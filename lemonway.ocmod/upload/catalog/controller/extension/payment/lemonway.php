<?php
require_once DIR_SYSTEM . '/library/lemonway/LemonWayService.php'; // SEND REQUEST

class ControllerExtensionPaymentLemonWay extends Controller
{
    // Constants
    const SUPPORTED_LANGS = array(
        'da' => 'da', // Danish
        'de' => 'ge', // German
        'en' => 'en', // English
        'es' => 'sp', // Spanish
        'fi' => 'fi', // Finnish
        'fr' => 'fr', // French
        'it' => 'it', // Italian
        'ko' => 'ko', // Korean
        'no' => 'no', // Norwegian
        'pt' => 'po', // Portuguese
        'sv' => 'sw' // Swedish
    );
    const DEFAULT_LANG = 'en';

    private $money_in_trans_details;

    /*
    Check if there are GET params
    */
    private function isGet()
    {
        return (strtoupper($this->request->server['REQUEST_METHOD']) == 'GET');
    }

    /*
    Get GET param with $key
    */
    private function getValue($key)
    {
        return (isset($this->request->get[$key]) ? $this->request->get[$key] : null);
    }

    /*
    Check if there are POST params
    */
    private function isPost()
    {
        return (strtoupper($this->request->server['REQUEST_METHOD']) == 'POST');
    }

    /*
    Get POST param with $key
    */
    private function postValue($key)
    {
        return (isset($this->request->post[$key]) ? $this->request->post[$key] : null);
    }

    /*
    Get the config
    */
    private function getLemonWayConfig()
    {
        $config = array();
        
        if ($this->config->get('lemonway_is_test_mode')) {
            // TEST
            $config['dkURL'] = $this->config->get('lemonway_directkit_url_test'); //DIRECT KIT URL TEST
            $config['wkURL'] = $this->config->get('lemonway_webkit_url_test'); //WEB KIT URL TEST
        } else {
            // PROD
            $config['dkURL'] = $this->config->get('lemonway_directkit_url'); // DIRECT KIT URL PROD
            $config['wkURL'] = $this->config->get('lemonway_webkit_url'); // WEBKIT URL PROD
        }

        $config['login'] = $this->config->get('lemonway_api_login');
        $config['pass'] = $this->config->get('lemonway_api_password');
        $config['wallet'] = empty($this->config->get('lemonway_environment_name')) ?
            $this->config->get('lemonway_wallet') : $this->config->get('lemonway_custom_wallet');
        $config['cssURL'] = $this->config->get('lemonway_css_url');
        $config['autoCommission'] = (int)!empty($this->config->get('lemonway_environment_name'));
        // Autocom = 0 if lwecommerce, 1 if custom environment

        return $config;
    }

    /*
    Call the API with a wkToken to check the details of a payment
    */
    private function getMoneyInTransDetails($wkToken)
    {
        if (is_null($this->money_in_trans_details)) {
            // Call API to get transaction detail for this wkToken
            $config = $this->getLemonWayConfig();

            $lemonwayService = new LemonWayService(
                $config['dkURL'],
                $config['login'],
                $config['pass'],
                substr($this->language->get('code'), 0, 2),
                $this->config->get('lemonway_debug')
            );

            $params = array(
                'transactionMerchantToken' => $wkToken
            );
            $res = $lemonwayService->getMoneyInTransDetails($params);

            $this->money_in_trans_details = $res;
        }

        return $this->money_in_trans_details;
    }

    /*
    Check the real paid amount
    */
    private function checkAmount($amount, $realAmount) {
        return ($amount == $realAmount);
    }

    /*
    Double check
    */
    private function doublecheckAmount($amount, $wkToken) {
        $details = $this->getMoneyInTransDetails($wkToken);

        // CREDIT + COMMISSION
        $realAmountDoublecheck = $details->TRANS->HPAY[0]->CRED + $details->TRANS->HPAY[0]->COM;
        
        // Status 3 means success
        return (($details->TRANS->HPAY[0]->STATUS == '3') && ($amount == $realAmountDoublecheck));
    }


    /*
     Retrieve and update saved card info
    */
    private function updateSavedCardInfo($customerId, $wkToken)
    {
        $card = $this->model_extension_payment_lemonway->getCustomerCard($customerId);
        if (!$card) {
            $card = array();
        }

        $card['customer_id'] = $customerId;
        $details = $this->getMoneyInTransDetails($wkToken);
        $card['card_num'] = $details->TRANS->HPAY[0]->EXTRA->NUM;
        $card['card_type'] = $details->TRANS->HPAY[0]->EXTRA->TYP;
        $card['card_exp'] = $details->TRANS->HPAY[0]->EXTRA->EXP;

        $this->model_extension_payment_lemonway->insertOrUpdateCard($card);
    }

    /*
    The view in the checkout page with the card choosing.
    By clicking on "Continue" a payment will be created in checkout()
    */
    public function index()
    {
        // Load language
        $this->load->language('extension/payment/lemonway');

        // Load Model
        $this->load->model('extension/payment/lemonway');

        $data['text_card'] = $this->language->get('text_card');
        $data['link_checkout'] = $this->url->link('extension/payment/lemonway/checkout', '', true);
        $data['lemonway_oneclick_enabled'] = $this->config->get('lemonway_oneclick_enabled');
        $data['customerId'] = empty($this->customer->getId()) ? 0 : $this->customer->getId();
        // A guest customer has no Id, we consider it 0
        
        $data['card'] = $this->model_extension_payment_lemonway->getCustomerCard($this->customer->getId());
        // If card saved
        $data['text_use_card'] = $this->language->get('text_use_card');
        $data['text_save_new_card'] = $this->language->get('text_save_new_card');
        $data['text_not_use_card'] = $this->language->get('text_not_use_card');
        // If no card saved
        $data['text_save_card'] = $this->language->get('text_save_card');

        $data['button_continue'] = $this->language->get('button_continue');
        $data['text_loading'] = $this->language->get('text_loading');

        return $this->load->view('extension/payment/lemonway', $data);
    }

    /*
    A payment is created here, the user is redirected to the payment page.
    After putting the card information, the user will be redirected to checkoutReturn()
    */
    public function checkout()
    {
        $available_card = array('CB', 'VISA', 'MASTERCARD');

        if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
            // Redirect to the cart
            $this->response->redirect($this->url->link('checkout/cart'));
        }

        if (!isset($this->request->post['cc_type']) || !in_array($this->request->post['cc_type'], $available_card)) {
            // Redirect to the cart and display error
            $this->session->data['error'] = $this->language->get('error_card_type');
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

        if (!$order_info) {
            // Redirect to the cart and display error
            $this->session->data['error'] = $this->language->get('error_order_not_found');
            $this->response->redirect($this->url->link('checkout/cart'));
        }
        
        // Lemon Way config
        $config = $this->getLemonWayConfig();

        $lemonwayService = new LemonWayService(
            $config['dkURL'],
            $config['login'],
            $config['pass'],
            substr($this->language->get('code'), 0, 2),
            $this->config->get('lemonway_debug')
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
            $this->config->get('lemonway_oneclick_enabled') && 
            $customerId &&
            $this->postValue('lemonway_oneclick') === 'use_card'
        );

        if (!$useCard) { // If the client use a new card => MoneyInWebInit
            // Whether the client save a card
            $registerCard = (int)(
                $this->config->get('lemonway_oneclick_enabled') &&
                $customerId &&
                $this->postValue('lemonway_oneclick') === 'register_card'
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
                $this->session->data['error'] = $lemonwayService->printError($res->E);
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
            $lang = substr($this->language->get('code'), 0, 2);
            $lang = array_key_exists($lang, self::SUPPORTED_LANGS) ? self::SUPPORTED_LANGS[$lang] : self::DEFAULT_LANG;

            $lwUrl = $config['wkURL'] . '?moneyintoken=' . $moneyInToken . '&p=' . urlencode($config['cssURL']) . '&lang=' . $lang;

            $cc_type = $this->request->post['cc_type'];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $lwUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CAINFO, DIR_SYSTEM . "/library/lemonway/cacert.pem");

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
                    $this->session->data['error'] = $lemonwayService->printError($res->E);
                    $this->response->redirect($this->url->link('checkout/cart'));
                }

                // Credit + Commission
                $realAmount = $res->TRANS->HPAY->CRED + $res->TRANS->HPAY->COM;

                if ($res->TRANS->HPAY->STATUS == '3' && $this->checkAmount($total, $realAmount)) {
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
                $this->session->data['error'] = $this->language->get('error_card_not_found');
                $this->response->redirect($this->url->link('checkout/cart'));
            }
        }
    }

    /*
    This page receive the response of Lemon Way about the payment then redirect the user to the appropriate order page
    (success, failure or back to checkout)
    */
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

            if (!isset($wkToken)) {
                // Redirect to the cart and display error
                $this->session->data['error'] = $this->language->get('error_get');
                $this->response->redirect($this->url->link('checkout/cart'));
            }

            // Get order info
            $order_id = $this->model_extension_payment_lemonway->getOrderIdFromToken($wkToken);
            $order_info = $this->model_checkout_order->getOrder($order_id);

            if (!$order_info) {
                // Redirect to the cart and display error
                $this->session->data['error'] = $this->language->get('error_order_not_found');
                $this->response->redirect($this->url->link('checkout/cart'));
            }

            $total = number_format((float)$order_info['total'], 2, '.', '');

            switch ($action) {
                case 'return':
                    // Success => Order status 5 : Complete
                    $this->model_checkout_order->addOrderHistory($order_id, 5);

                    $customerId = $order_info['customer_id'];
                    $registerCard = $this->model_extension_payment_lemonway->getRegisterCardFromToken($wkToken);
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
                    $this->session->data['error'] = $this->language->get('error_action');
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
                $registerCard = $this->model_extension_payment_lemonway->getRegisterCardFromToken($wkToken);
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
