<?php
require_once DIR_SYSTEM . '/library/lemonway/LemonWayService.php'; // SEND REQUEST

class ControllerExtensionPaymentLemonWay extends Controller
{
    private $money_in_trans_details = null;

    private function isGet()
    {
        return (strtoupper($this->request->server['REQUEST_METHOD']) == 'GET');
    }

    private function getValue($key)
    {
        return (isset($this->request->get[$key]) ? $this->request->get[$key] : null);
    }

    private function isPost()
    {
        return (strtoupper($this->request->server['REQUEST_METHOD']) == 'POST');
    }

    private function postValue($key)
    {
        return (isset($this->request->post[$key]) ? $this->request->post[$key] : null);
    }

    private function getLemonWayConfig()
    {
        $config = array();
        
        if ($this->config->get('lemonway_is_test_mode') == '1') {
            // TEST
            //DIRECT KIT URL TEST
            if (!empty($this->config->get('lemonway_directkit_url_test'))) {
                $config['dkURL'] = $this->config->get('lemonway_directkit_url_test');
            }

            //WEB KIT URL TEST
            if (!empty($this->config->get('lemonway_webkit_url_test'))) {
                $config['wkURL'] = $this->config->get('lemonway_webkit_url_test');
            }

            $config['test'] = '1';
        } else {
            // PROD
            // DIRECT KIT URL PROD
            if (!empty($this->config->get('lemonway_directkit_url'))) {
                $config['dkURL'] = $this->config->get('lemonway_directkit_url');
            }

            // WEBKIT URL PROD
            if (!empty($this->config->get('lemonway_webkit_url'))) {
                $config['wkURL'] = $this->config->get('lemonway_webkit_url');
            }

            $config['test'] = '0';
        }

        $config['login'] = $this->config->get('lemonway_api_login');
        $config['pass'] = $this->config->get('lemonway_api_password');
        $config['wallet'] = empty($this->config->get('lemonway_environment_name')) ? $this->config->get('lemonway_wallet') : $this->config->get('lemonway_custom_wallet');
        $config['cssURL'] = $this->config->get('lemonway_css_url');
        $config['autoCommission'] = (int)!empty($this->config->get('lemonway_environment_name')); // Autocom = 0 if lwecommerce, 1 if custom environment

        return $config;
    }

    private function getMoneyInTransDetails($wkToken)
    {
        if (is_null($this->money_in_trans_details)) {
            // Call API to get transaction detail for this wkToken
            $lang = substr($this->language->get('code'), 0, 2);
            $config = $this->getLemonWayConfig();

            $lemonwayService = new LemonWayService(
                $config['dkURL'],
                $config['wkURL'],
                $config['login'],
                $config['pass'],
                $config['test'] != 1,
                $lang,
                (bool)$this->config->get('lemonway_debug')
            );

            $params = array(
                'transactionMerchantToken' => $wkToken
            );
            $res = $lemonwayService->getMoneyInTransDetails($params);

            $this->money_in_trans_details = $res;
        }

        return $this->money_in_trans_details;
    }

    // Check the real paid amount
    private function checkAmount($amount, $realAmount) {
        return ($amount == $realAmount);
    }

    // Double check
    private function doublecheckAmount($amount, $wkToken) {
        $details = $this->getMoneyInTransDetails($wkToken);
        // CREDIT + COMMISSION
        $realAmountDoublecheck = $details->TRANS->HPAY->CRED + $details->TRANS->HPAY->COM;

        // Status 3 means success
        return (($details->TRANS->HPAY->STATUS == '3') && ($amount == $realAmountDoublecheck));
    }

    // Whether the client use a saved card
    private function useCard()
    {
        return $this->postValue('lemonway_oneclic') === 'use_card' && $this->config->get('lemonway_oneclick_enabled') == '1' && !empty($this->customer->getId());
    }

    public function index()
    {
        // Load language
        $this->load->language('extension/payment/lemonway');

        // Load Model
        $this->load->model('extension/payment/lemonway');

        $data['button_continue'] = $this->language->get('button_continue');
        $data['text_loading'] = $this->language->get('text_loading');

        $data['continue'] = $this->url->link('extension/payment/lemonway/checkout', '', true);
        $data['text_card'] = $this->language->get('text_card');

        $data['customerId'] = empty($this->customer->getId()) ? 0 : $this->customer->getId(); // A guest customer has no Id, we consider it 0

        // If card saved
        $data['entry_save_card'] = $this->language->get('entry_save_card');
        $data['entry_use_card'] = $this->language->get('entry_use_card');
        $data['entry_actual_card'] = $this->language->get('entry_actual_card');
        $data['entry_save_new_card'] = $this->language->get('entry_save_new_card');
        $data['entry_not_use_card'] = $this->language->get('entry_not_use_card');
        $data['entry_expiration_date'] = $this->language->get('entry_expiration_date');

        $data['lemonway_oneclick_enabled'] = $this->config->get('lemonway_oneclick_enabled');

        $data['card'] = $this->model_extension_payment_lemonway->getCustomerCard($this->customer->getId());

        return $this->load->view('extension/payment/lemonway', $data);
    }

    public function checkout()
    {
        $available_card = array('CB', 'VISA', 'MASTERCARD');

        if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))  || !isset($this->request->post['cc_type']) || !in_array($this->request->post['cc_type'], $available_card)) {
            $this->response->redirect($this->url->link('checkout/cart'));
        }

        //Load Language
        $this->load->language('extension/payment/lemonway');

        // Load Model
        $this->load->model('extension/payment/lemonway');
        $this->load->model('checkout/order');

        // Order info
        $order_id = $this->session->data['order_id'];
        $order_info = $this->model_checkout_order->getOrder($order_id);
        
        // Lemon Way config
        $config = $this->getLemonWayConfig();

        $lang = $this->language->get('code');
        $lang = substr($lang, 0, 2);

        $lemonwayService = new LemonWayService(
            $config['dkURL'],
            $config['wkURL'],
            $config['login'],
            $config['pass'],
            $config['test'] != 1,
            $lang,
            (bool)$this->config->get('lemonway_debug')
        );

        $params = array();
        
        $params['wallet'] = $config['wallet'];
        $total = number_format((float)$order_info['total'], 2, '.', '');
        $params['amountTot'] = $total;
        $params['comment'] = $this->config->get('config_name') . " - " . $order_id . " - " .
            $this->customer->getLastName() . " " . $this->customer->getFirstName() . " - " .
            $this->customer->getEmail();
        $params['autoCommission'] = $config['autoCommission'];

        $customerId = empty($this->customer->getId()) ? 0 : $this->customer->getId(); // A guest customer has no Id, we consider it 0

        $useCard = (
            $this->config->get('lemonway_oneclick_enabled') == '1' && 
            $customerId &&
            $this->postValue('lemonway_oneclic') === 'use_card'
        );

        if (!$useCard) { // If the client use a new card => MoneyInWebInit
            // Whether the client save a card
            $registerCard = (int)(
                $this->config->get('lemonway_oneclick_enabled') == '1' &&
                $customerId &&
                $this->postValue('lemonway_oneclic') === 'register_card'
            );
            $params['registerCard'] = $registerCard;

            // Associate order id with a wkToken
            $wkToken = $this->model_extension_payment_lemonway->saveWkToken($order_id, $registerCard);
            $params['wkToken'] = $wkToken;

            // GET Params
            // returnUrl
            $returnParams = array(
                'action' => 'return'
            );
            $returnParams = http_build_query($returnParams);

            // cancelUrl
            $cancelParams = array(
                'action' => 'cancel'
            );
            $cancelParams = http_build_query($cancelParams);

            // errorUrl
            $errorParams = array(
                'action' => 'error'
            );
            $errorParams = http_build_query($errorParams);

            $params['returnUrl'] = $this->url->link('extension/payment/lemonway/checkoutReturn&' . $returnParams, '', true);
            $params['cancelUrl'] = $this->url->link('extension/payment/lemonway/checkoutReturn&' . $cancelParams, '', true);
            $params['errorUrl'] = $this->url->link('extension/payment/lemonway/checkoutReturn&' . $errorParams, '', true);

            // Money In
            $res = $lemonwayService->moneyInWebInit($params);

            // Error
            if (isset($res->E)) {
                // Redirect to the cart and display error
                $this->session->data['error'] = $res->E->Msg;
                $this->response->redirect($this->url->link('checkout/cart'));
            }

            if ($customerId && isset($res->MONEYINWEB->CARD) && $registerCard) {
                $card = $this->model_extension_payment_lemonway->getCustomerCard($customerId);
                if (!$card) {
                    $card = array();
                }

                $card['customer_id'] = $customerId;
                $card['card_id'] = (string)$res->MONEYINWEB->CARD->ID;

                $this->model_extension_payment_lemonway->insertOrUpdateCard($card);
            }

            $moneyInToken = (string)$res->MONEYINWEB->TOKEN;

            $lwUrl = $config['wkURL'] . '?moneyintoken=' . $moneyInToken . '&p=' . urlencode($config['cssURL']) . '&lang=' . $lang;

            $cc_type = $this->request->post['cc_type'];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $lwUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $config['test'] != 1);//False if  test enabled

            $response = curl_exec($ch);

            $matches = array();

            $patternFormActionAndData = '/(action="|name=data value=")([^"]*)"/i';
            if (preg_match_all($patternFormActionAndData, $response, $matches)) {
                if (isset($matches[2])) {
                    list($actionUrl, $data) = $matches[2];
                    $postFields = array(
                        'DATA' => $data,
                        $cc_type => 1
                    );

                    $text_redirect = $this->language->get('text_redirect');

                    $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
                    $html .= '<html>' . "\n";
                    $html .= '    <head>' . "\n";
                    $html .= '    </head>';
                    $html .= '    <body>';
                    $html .= '        <div align="center"><br />' . $text_redirect . '</div>' . "\n";
                    $html .= '        <div id="buttons" style="display: none;">' . "\n";
                    $html .= '            <form id="lemonway_payment_redirect" action="' . $actionUrl . '" method="post">' . "\n";
                    $html .= '                <input type="hidden" name="' . $cc_type . '_x" value="1" />' . "\n";
                    $html .= '                <input type="hidden" name="' . $cc_type . '_y" value="1" />' . "\n";
                    $html .= '                <input type="hidden" name="DATA" value="' . $data . '" />' . "\n";
                    $html .= '            </form>' . "\n";
                    $html .= '        </div>' . "\n";
                    $html .= '        <script type="text/javascript">document.getElementById("lemonway_payment_redirect").submit();</script>' . "\n";
                    $html .= '    </body>' . "\n";
                    $html .= '</html>';

                    die($html);
                }
            }
        } else { // If the client use a saved card => MoneyInWithCardId
            if (($card = $this->model_extension_payment_lemonway->getCustomerCard($this->customer->getId())) && $this->customer->isLogged()) {
                //Call API for MoneyInWithCardId
                $params['comment'] = $params['comment'] . " (Money In with Card Id)";
                $params['cardId'] = $card['card_id'];

                // Money In with saved card
                $res = $lemonwayService->moneyInWithCardId($params);

                // Error
                if (isset($res->E)) {
                    // Redirect to the cart and display error
                    $this->session->data['error'] = $res->E->Msg;
                    $this->response->redirect($this->url->link('checkout/cart'));
                }

                // Credit + Commission
                $realAmount = $res->TRANS->HPAY->CRED + $res->TRANS->HPAY->COM;

                if ($res->TRANS->HPAY->STATUS == '3' && $this->checkAmount($total, $realAmount) && $this->doublecheckAmount($total, $wkToken)) {
                    // Success => Order status 5 : Complete
                    $this->model_checkout_order->addOrderHistory($order_id, 5);
                    $this->response->redirect($this->url->link('checkout/success'));
                } else {
                    // Error => Order status 10 : Failed
                    $this->model_checkout_order->addOrderHistory($order_id, 10);
                    $this->response->redirect($this->url->link('checkout/failure'));
                }
            } else {
                // Redirect to the cart and display error
                $this->session->data['error'] = 'Customer not logged or card not found!';
                $this->response->redirect($this->url->link('checkout/cart'));
            }
        }
    }

    // Retrieve and update saved card info
    private function updateSavedCardInfo($customerId, $wkToken)
    {
        $card = $this->model_extension_payment_lemonway->getCustomerCard($customerId);
        if (count($card) == 0) {
            $card = array();
        }

        $card['customer_id'] = $customerId;
        $details = $this->getMoneyInTransDetails($wkToken);
        $card['card_num'] = $details->TRANS->HPAY[0]->EXTRA->NUM;
        $card['card_type'] = $details->TRANS->HPAY[0]->EXTRA->TYP;
        $card['card_exp'] = $details->TRANS->HPAY[0]->EXTRA->EXP;

        $this->model_extension_payment_lemonway->insertOrUpdateCard($card);
    }

    // checkoutReturn page controller (returnUrl, cancelUrl, errorUrl)
    public function checkoutReturn()
    {
        //Load Language
        $this->load->language('extension/payment/lemonway');

        //Load Model
        $this->load->model('extension/payment/lemonway');
        $this->load->model('checkout/order');

        if ($this->isGet()) { // If redirection
            // GET params
            if ($this->config->get('lemonway_debug')) {
               $debug_log = new Log('lemonway_debug.log');
               $debug_log->write('GET params: ' . print_r($this->request->get, true));
            }

            $wkToken = $this->getValue('response_wkToken');
            $action = $this->getValue('action');

            if (!isset($wkToken) || !isset($action)) {
                // Redirect to the cart and display error
                $this->session->data['error'] = $this->language->get('error_param');
                $this->response->redirect($this->url->link('checkout/cart'));
            }

            // Get order info
            $order_id = $this->model_extension_payment_lemonway->getOrderIdFromToken($wkToken);
            $order_info = $this->model_checkout_order->getOrder($order_id);
            $total = number_format((float)$order_info['total'], 2, '.', '');

            switch ($action) {
                case 'return':
                    // Success => Order status 5 : Complete
                    $this->model_checkout_order->addOrderHistory($order_id, 5);

                    $customerId = $order_info['customer_id'];
                    $registerCard = (bool)$this->model_extension_payment_lemonway->getRegisterCardFromToken($wkToken);
                    if ($customerId && $registerCard) {
                        $this->updateSavedCardInfo($customerId, $wkToken);
                    }

                    $this->response->redirect($this->url->link('checkout/success'));

                case 'error':
                    // Error => Order status 10 : Failed
                    $this->model_checkout_order->addOrderHistory($order_id, 10);
                    $this->response->redirect($this->url->link('checkout/failure'));

                case 'cancel':
                    // Success => Order status 7 : Canceled
                    $this->model_checkout_order->addOrderHistory($order_id, 7);
                    $this->response->redirect($this->url->link('checkout/cart'));

                default:
                    $this->session->data['error'] = $this->language->get('error_param');
                    $this->response->redirect($this->url->link('checkout/cart'));
            }
        } elseif ($this->isPost()) { // If IPN
            // Get response by IPN
            if ($this->config->get('lemonway_debug')) {
               $debug_log = new Log('lemonway_debug.log');
               $debug_log->write('IPN: ' . print_r($this->request->post, true));
            }

            $response_code = $this->postValue('response_code');
            $wkToken = $this->postValue('response_wkToken');
            $realAmount = $this->postValue('response_transactionAmount');

            // Get order info
            $order_id = $this->model_extension_payment_lemonway->getOrderIdFromToken($wkToken);
            $order_info = $this->model_checkout_order->getOrder($order_id);
            $total = number_format((float)$order_info['total'], 2, '.', '');

            if (($response_code == "0000") && $this->checkAmount($total, $realAmount) && $this->doublecheckAmount($total, $wkToken)) {
                // Success => Order status 5 : Complete
                $this->model_checkout_order->addOrderHistory($order_id, 5);

                $customerId = $order_info['customer_id'];
                $registerCard = (bool)$this->model_extension_payment_lemonway->getRegisterCardFromToken($wkToken);
                if ($customerId && $registerCard) {
                    $this->updateSavedCardInfo($customerId, $wkToken);
                }
            } else {
                // Error => Order status 10 : Failed
                $this->model_checkout_order->addOrderHistory($order_id, 10);
            }
        } else {
            // Redirect to the cart and display error
            $this->session->data['error'] = $this->language->get('error_param');
            $this->response->redirect($this->url->link('checkout/cart'));
        }
    }
}
