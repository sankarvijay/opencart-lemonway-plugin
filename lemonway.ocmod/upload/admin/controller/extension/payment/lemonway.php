<?php
class ControllerExtensionPaymentLemonway extends Controller
{
    const LEMONWAY_ENVIRONMENT_DEFAULT = 'lwecommerce';

    const LEMONWAY_WEBKIT_FORMAT_URL_PROD = 'https://webkit.lemonway.fr/mb/%s/prod/';
    const LEMONWAY_WEBKIT_FORMAT_URL_TEST = 'https://sandbox-webkit.lemonway.fr/%s/dev/';
    const LEMONWAY_DIRECTKIT_FORMAT_URL_PROD = 'https://ws.lemonway.fr/mb/%s/prod/directkitjson2/service.asmx';
    const LEMONWAY_DIRECTKIT_FORMAT_URL_TEST = 'https://sandbox-api.lemonway.fr/mb/%s/dev/directkitjson2/service.asmx';

    const CSS_URL_DEFAULT = 'https://webkit.lemonway.fr/css/mercanet/mercanet_lw_custom.css';

    private $error = array();

    public function index()
    {
        // Load language
        $this->load->language('extension/payment/lemonway');

        // Load language variables
        $data['heading_title'] = $this->language->get('heading_title');
        $data['button_save']   = $this->language->get('button_save');
        $data['button_cancel']   = $this->language->get('button_cancel');

        $data['text_payment'] = $this->language->get('text_payment');
        $data['text_breadcrumbs'] = $this->language->get('text_breadcrumbs');
        $data['text_success'] = $this->language->get('text_success');
        $data['text_lemonway'] = $this->language->get('text_lemonway');
        $data['text_test'] = $this->language->get('text_test');
        $data['text_live'] = $this->language->get('text_live');
        $data['text_authorization'] = $this->language->get('text_authorization');
        $data['text_charge'] = $this->language->get('text_charge');
        $data['text_help'] = $this->language->get('text_help');

        $data['text_edit_config'] = $this->language->get('text_edit_config');

        // Tab titles
        $data['text_about_us'] = $this->language->get('text_about_us');
        $data['text_configuration'] = $this->language->get('text_configuration');
        $data['text_cc'] = $this->language->get('text_cc');

        $data['text_advanced_configuration'] = $this->language->get('text_advanced_configuration');
        $data['text_custom_environment'] = $this->language->get('text_custom_environment');

        //About us
        $data['text_sign_in'] = $this->language->get('text_sign_in');
        $data['text_sign_up'] = $this->language->get('text_sign_up');
        $data['text_create_account_title'] = $this->language->get('text_create_account_title');
        $data['text_create_account_step_1'] = $this->language->get('text_create_account_step_1');
        $data['text_create_account_step_2'] = $this->language->get('text_create_account_step_2');
        $data['text_create_account_step_3'] = $this->language->get('text_create_account_step_3');
        $data['text_help_desk'] = $this->language->get('text_help_desk');
        $data['text_turnkey_solution'] = $this->language->get('text_turnkey_solution');
        $data['text_secured_solution'] = $this->language->get('text_secured_solution');
        $data['text_commission'] = $this->language->get('text_commission');
        $data['text_secured_payments_title'] = $this->language->get('text_secured_payments_title');
        $data['text_secured_payments_content'] = $this->language->get('text_secured_payments_content');
        $data['text_manage_transaction_title'] = $this->language->get('text_manage_transaction_title');
        $data['text_manage_transaction_content'] = $this->language->get('text_manage_transaction_content');
        $data['text_more_information'] = $this->language->get('text_more_information');
        $data['text_link_support'] = $this->language->get('text_link_support');
        $data['text_or'] = $this->language->get('text_or');
        $data['text_save'] = $this->language->get('text_save');

        $data['text_method_configuration'] = $this->language->get('text_method_configuration');

        //HELP
        $data['help_login_prod'] = $this->language->get('help_login_prod');
        $data['help_wallet'] = $this->language->get('help_wallet');
        $data['help_test'] = $this->language->get('help_test');
        $data['help_css'] = $this->language->get('help_css');
        $data['help_leave_empty'] = $this->language->get('help_leave_empty');
        $data['help_oneclick'] = $this->language->get('help_oneclick');

        // Entry
        $data['entry_login'] = $this->language->get('entry_login');
        $data['entry_password'] = $this->language->get('entry_password');
        $data['entry_wallet'] = $this->language->get('entry_wallet');
        $data['entry_test'] = $this->language->get('entry_test');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_debug'] = $this->language->get('entry_debug');
        $data['entry_yes'] = $this->language->get('entry_yes');
        $data['entry_no'] = $this->language->get('entry_no');
        $data['entry_oneclick'] = $this->language->get('entry_oneclick');
        $data['entry_css'] = $this->language->get('entry_css');

        $data['entry_environment_name']=$this->language->get('entry_environment_name');

        // Warning
        $data['warning_status'] = $this->language->get('warning_status');

        $this->load->model('setting/setting');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['lemonway_api_login'] = $this->model_setting_setting->getSettingValue('lemonway_api_login');
        $data['lemonway_api_password'] = $this->model_setting_setting->getSettingValue('lemonway_api_password');
        $data['lemonway_custom_wallet'] = $this->model_setting_setting->getSettingValue('lemonway_custom_wallet');
        $data['lemonway_is_test_mode'] = $this->model_setting_setting->getSettingValue('lemonway_is_test_mode');
        $data['lemonway_oneclick_enabled'] = $this->model_setting_setting->getSettingValue('lemonway_oneclick_enabled');
        $data['lemonway_debug'] = $this->model_setting_setting->getSettingValue('lemonway_debug');
        $data['lemonway_css_url'] = $this->model_setting_setting->getSettingValue('lemonway_css_url');
        $data['lemonway_environment_name'] = $this->model_setting_setting->getSettingValue('lemonway_environment_name');
        $data['lemonway_status'] = $this->model_setting_setting->getSettingValue('lemonway_status');
        
        //UNSET THE ERROR
        unset($data['error_permission'], $data['error_login'], $data['error_password'], $data['error_curl'], $data['error_wallet'], $data['error_testConfig']);

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        // Load default layout
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');

        $data['footer'] = $this->load->controller('common/footer');

        // About us
        $data['about_us'] = $this->load->view('extension/payment/lemonway_aboutus.tpl', $data);

        // Configuration
        $data['configure'] = $this->load->view('extension/payment/lemonway_configure.tpl', $data);

        // Credit Card
        $data['cc'] = $this->load->view('extension/payment/lemonway_cc.tpl', $data);

        // Load breadcrumbs
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_breadcrumbs'),
            'href' => $this->url->link('extension/payment/lemonway', 'token=' . $this->session->data['token'], true)
        );

        // Load action buttons urls
        $data['action'] = $this->url->link('extension/payment/lemonway', 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], true);

        // If isset request to change settings
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
            // Edit settings
            $this->model_setting_setting->editSetting('lemonway', $this->request->post);

            // Set success message
            if ($this->testConfig()) {
                $this->session->data['success'] = $this->language->get('text_success');

                // Return to extensions page
                $this->response->redirect($this->url->link('extension/payment/lemonway', 'token=' . $this->session->data['token'], true));
            } else {
                //Display Error
                $data['error_testConfig'] = "LemonWay: " . $this->error['testConfig'];

                //ACCOUNT INFORMATION
                $data['lemonway_api_login'] = $this->model_setting_setting->getSettingValue('lemonway_api_login');
                $data['lemonway_api_password'] = $this->model_setting_setting->getSettingValue('lemonway_api_password');
                $data['lemonway_custom_wallet'] = $this->model_setting_setting->getSettingValue('lemonway_custom_wallet');

                $data['lemonway_is_test_mode'] = $this->model_setting_setting->getSettingValue('lemonway_is_test_mode');
                $data['lemonway_oneclick_enabled'] = $this->model_setting_setting->getSettingValue('lemonway_oneclick_enabled');

                $data['lemonway_debug'] = $this->model_setting_setting->getSettingValue('lemonway_debug');

                $data['error_testConfig'] .= 'TEST: ' . $data['lemonway_is_test_mode']; // Debug 

                //ADVANCED ACCOUNT CONFIGURATION
                $data['lemonway_environment_name'] = $this->model_setting_setting->getSettingValue('lemonway_environment_name');

                // Credit card
                $data['lemonway_status'] = $this->model_setting_setting->getSettingValue('lemonway_status');

                $data['lemonway_css_url'] = $this->model_setting_setting->getSettingValue('lemonway_css_url');

                $this->response->setOutput($this->load->view('extension/payment/lemonway.tpl', $data));
            }
        }

        $data = $this->loadAllErrors($data);

        $this->response->setOutput($this->load->view('extension/payment/lemonway.tpl', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/payment/lemonway')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if (!extension_loaded('curl')) {
            $this->error['curl_not_installed'] = $this->langage->get('error_curl');
        }
        if (empty($this->request->post['lemonway_api_login']) && empty($this->model_setting_setting->getSettingValue('lemonway_api_login'))) {
            $this->error['login_not_set'] = $this->language->get('error_login');
        }
        if (empty($this->request->post['lemonway_api_login'])  && !empty($this->model_setting_setting->getSettingValue('lemonway_api_login')))  {
            $this->request->post['lemonway_api_login'] = $this->model_setting_setting->getSettingValue('lemonway_api_login');
        }

        if ( empty($this->request->post['lemonway_api_password']) && !empty($this->model_setting_setting->getSettingValue('lemonway_api_password'))) {
            $this->request->post['lemonway_api_password'] = $this->model_setting_setting->getSettingValue('lemonway_api_password');
        }
        if ( empty($this->request->post['lemonway_api_password']) && empty($this->model_setting_setting->getSettingValue('lemonway_api_password'))) {
            $this->error['password_not_set'] = $this->language->get('error_password');
        }

        // Debug
        if (!isset($this->request->post['lemonway_debug'])) {
            $this->request->post['lemonway_oneclick_enabled'] = '0';
        }
        //  Test Mode
        if (!isset($this->request->post['lemonway_is_test_mode'])) {
            $this->request->post['lemonway_is_test_mode'] = '0';
        }

        // Default value if lemonway_css_url is empty
        if (empty($this->request->post['lemonway_css_url'])) {
            $this->request->post['lemonway_css_url'] = self::CSS_URL_DEFAULT;
        }

        // Environment name
        if (empty($this->request->post['lemonway_environment_name'])) {
            // If no custom environment => lwecommerce
            $env_name = self::LEMONWAY_ENVIRONMENT_DEFAULT;
        } else {
            // If custom environment
            $env_name = $this->request->post['lemonway_environment_name'];
        }
        
        $this->request->post['lemonway_directkit_url'] = sprintf(self::LEMONWAY_DIRECTKIT_FORMAT_URL_PROD, $env_name);
        $this->request->post['lemonway_webkit_url'] = sprintf(self::LEMONWAY_WEBKIT_FORMAT_URL_PROD, $env_name);
        $this->request->post['lemonway_directkit_url_test'] = sprintf(self::LEMONWAY_DIRECTKIT_FORMAT_URL_TEST, $env_name);
        $this->request->post['lemonway_webkit_url_test'] = sprintf(self::LEMONWAY_WEBKIT_FORMAT_URL_TEST, $env_name);

        //Credit Card status
        if (!isset($this->request->post['lemonway_status'])) {
            $this->request->post['lemonway_status'] = '0';
        }
        
        // One-click
        if (!isset($this->request->post['lemonway_oneclick_enabled'])) {
            $this->request->post['lemonway_oneclick_enabled'] = '0';
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    // Test if the configuration is OK
    private function testConfig()
    {
        // Load settings
        $this->load->model('setting/setting');

        // TEST
        if ($this->config->get('lemonway_is_test_mode') == '1') {
            // DIRECTKIT TEST URL
            $dkUrl = $this->config->get('lemonway_directkit_url_test');
            // WEBKIT TEST URL
            $wkUrl = $this->config->get('lemonway_webkit_url_test');
        } //PROD
        else {
            // DIRECTKIT URL
            $dkUrl = $this->config->get('lemonway_directkit_url');
            // WEBKIT URL
            $wkUrl = $this->config->get('lemonway_webkit_url');
        }

        require_once DIR_SYSTEM . '/library/lemonway/LemonWayService.php';

        $lang = substr($this->language->get('code'), 0, 2);

        // API connection
        $lemonwayService = new LemonWayService(
            $dkUrl,
            $wkUrl,
            $this->config->get('lemonway_api_login'),
            $this->config->get('lemonway_api_password'),
            $this->config->get('lemonway_is_test_mode') != '1',
            $lang,
            (bool)$this->config->get('lemonway_debug')
        );

        if (empty($this->config->get('lemonway_environment_name'))) {
            // If lwecommerce, get wallet by email
            $params = array('email' => $this->config->get('lemonway_api_login'));
        } else {
            // If custom env, get custom wallet
            $params = array('wallet' => $this->config->get('lemonway_custom_wallet'));
        }

        $res = $lemonwayService->getWalletDetails($params);
        if (empty($this->config->get('lemonway_environment_name'))) {
            // If lwecommerce, get wallet
            if (isset($res->WALLET->ID)) {
                $this->model_setting_setting->editSettingValue('lemonway', 'lemonway_wallet', $res->WALLET->ID);
            }
        }

        if (isset($res->E)) {
            $this->error['testConfig'] = $res->E->Msg;
            return false;
        } else {
            return true;
        }
    }

    private function loadAllErrors($data)
    {
        foreach ($this->error as $key => $value) {
            $data['error_' . $key] = $value;
        }
        return $data;
    }

    public function install()
    {
        $this->load->model('extension/payment/lemonway');
        $this->model_extension_payment_lemonway->install();

        // Default settings
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('lemonway', [
            'lemonway_status' => 1,
            'lemonway_css_url' => self::CSS_URL_DEFAULT,
            'lemonway_directkit_url' => sprintf(self::LEMONWAY_DIRECTKIT_FORMAT_URL_PROD, self::LEMONWAY_ENVIRONMENT_DEFAULT),
            'lemonway_webkit_url' => sprintf(self::LEMONWAY_WEBKIT_FORMAT_URL_PROD, self::LEMONWAY_ENVIRONMENT_DEFAULT),
            'lemonway_directkit_url_test' => sprintf(self::LEMONWAY_DIRECTKIT_FORMAT_URL_TEST, self::LEMONWAY_ENVIRONMENT_DEFAULT),
            'lemonway_webkit_url_test' => sprintf(self::LEMONWAY_WEBKIT_FORMAT_URL_TEST, self::LEMONWAY_ENVIRONMENT_DEFAULT)
        ]);
    }

    public function uninstall()
    {
        $this->load->model('extension/payment/lemonway');
        $this->model_extension_payment_lemonway->uninstall();
    }
}
?>