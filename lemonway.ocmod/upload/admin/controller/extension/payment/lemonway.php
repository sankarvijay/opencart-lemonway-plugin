<?php
define('LEMONWAY_VERSION', '1.0.0');

class ControllerExtensionPaymentLemonway extends Controller
{
    const LEMONWAY_ENVIRONMENT_DEFAULT = 'lwecommerce';

    const LEMONWAY_WEBKIT_FORMAT_URL_PROD = 'https://webkit.lemonway.fr/mb/%s/prod/';
    const LEMONWAY_WEBKIT_FORMAT_URL_TEST = 'https://sandbox-webkit.lemonway.fr/%s/dev/';
    const LEMONWAY_DIRECTKIT_FORMAT_URL_PROD = 'https://ws.lemonway.fr/mb/%s/prod/directkitjson2/service.asmx';
    const LEMONWAY_DIRECTKIT_FORMAT_URL_TEST = 'https://sandbox-api.lemonway.fr/mb/%s/dev/directkitjson2/service.asmx';

    const CSS_URL_DEFAULT = 'https://webkit.lemonway.fr/css/mercanet/mercanet_lw_custom.css';

    private $variables = array();

    public function index()
    {   
        // Load settings
        $this->load->model('setting/setting');

        // Load language
        $this->load->language('extension/payment/lemonway');

        // If POST request => validate the data before saving
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
            // Edit settings
            $this->model_setting_setting->editSetting('lemonway', $this->request->post);
        }

        // Load language variables
        $this->variables['heading_title'] = $this->language->get('heading_title');
        $this->variables['button_save']   = $this->language->get('button_save');
        $this->variables['button_cancel']   = $this->language->get('button_cancel');

        // Text
        // ABOUT US
        $this->variables['text_secured_solution'] = $this->language->get('text_secured_solution');
        $this->variables['text_commission'] = $this->language->get('text_commission');
        $this->variables['text_sign_in'] = $this->language->get('text_sign_in');
        $this->variables['text_create_account_title'] = $this->language->get('text_create_account_title');
        $this->variables['text_create_account_step_1'] = $this->language->get('text_create_account_step_1');
        $this->variables['text_sign_up'] = $this->language->get('text_sign_up');
        $this->variables['text_create_account_step_2'] = $this->language->get('text_create_account_step_2');
        $this->variables['text_create_account_step_3'] = $this->language->get('text_create_account_step_3');
        $this->variables['text_help_desk'] = $this->language->get('text_help_desk');
        $this->variables['text_turnkey_solution'] = $this->language->get('text_turnkey_solution');
        $this->variables['text_secured_payments_title'] = $this->language->get('text_secured_payments_title');
        $this->variables['text_secured_payments_content'] = $this->language->get('text_secured_payments_content');
        $this->variables['text_manage_transaction_title'] = $this->language->get('text_manage_transaction_title');
        $this->variables['text_manage_transaction_content'] = $this->language->get('text_manage_transaction_content');
        $this->variables['text_more_information'] = $this->language->get('text_more_information');
        $this->variables['text_or'] = $this->language->get('text_or');
        $this->variables['text_support_link'] = $this->language->get('text_support_link');  

        // CONFIGURATION
        $this->variables['text_account_configuration'] = $this->language->get('text_account_configuration');
        $this->variables['text_login'] = $this->language->get('text_login');
        $this->variables['text_help_login'] = $this->language->get('text_help_login');
        $this->variables['text_password'] = $this->language->get('text_password');
        $this->variables['text_masked'] = $this->language->get('text_masked');
        $this->variables['text_help_password'] = $this->language->get('text_help_password');
        $this->variables['text_test_mode'] = $this->language->get('text_test_mode');
        $this->variables['text_help_test_mode'] = $this->language->get('text_help_test_mode');
        $this->variables['text_advanced_configuration'] = $this->language->get('text_advanced_configuration');
        $this->variables['text_css'] = $this->language->get('text_css');
        $this->variables['text_help_css'] = $this->language->get('text_help_css');
        $this->variables['text_debug_mode'] = $this->language->get('text_debug_mode');
        $this->variables['text_custom_environment'] = $this->language->get('text_custom_environment');
        $this->variables['text_environment_name'] = $this->language->get('text_environment_name');
        $this->variables['text_wallet'] = $this->language->get('text_wallet');

        // CREDIT CARD
        $this->variables['text_method_configuration'] = $this->language->get('text_method_configuration');
        $this->variables['text_enabled'] = $this->language->get('text_enabled');
        $this->variables['text_oneclick'] = $this->language->get('text_oneclick');
        $this->variables['text_help_oneclick'] = $this->language->get('text_help_oneclick');

        // Tab
        $this->variables['tab_about_us'] = $this->language->get('tab_about_us');
        $this->variables['tab_configuration'] = $this->language->get('tab_configuration');
        $this->variables['tab_cc'] = $this->language->get('tab_cc');

        // Load default layout
        $this->variables['header'] = $this->load->controller('common/header');
        $this->variables['column_left'] = $this->load->controller('common/column_left');
        $this->variables['footer'] = $this->load->controller('common/footer');
        $this->variables['cancel_link'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'], true);

        $this->document->setTitle($this->language->get('heading_title'));

        // Load setting values
        $this->variables['lemonway_api_login'] = $this->model_setting_setting->getSettingValue('lemonway_api_login');
        $this->variables['lemonway_api_password'] = $this->model_setting_setting->getSettingValue('lemonway_api_password');
        $this->variables['lemonway_is_test_mode'] = $this->model_setting_setting->getSettingValue('lemonway_is_test_mode');
        $this->variables['lemonway_css_url'] = $this->model_setting_setting->getSettingValue('lemonway_css_url');
        $this->variables['lemonway_debug'] = $this->model_setting_setting->getSettingValue('lemonway_debug');
        $this->variables['lemonway_environment_name'] = $this->model_setting_setting->getSettingValue('lemonway_environment_name');
        $this->variables['lemonway_custom_wallet'] = $this->model_setting_setting->getSettingValue('lemonway_custom_wallet');
        $this->variables['lemonway_status'] = $this->model_setting_setting->getSettingValue('lemonway_status');
        $this->variables['lemonway_oneclick_enabled'] = $this->model_setting_setting->getSettingValue('lemonway_oneclick_enabled');

        // Alerts
        if ($this->variables['lemonway_status']) { // If enabled
            // If Test mode
            if ($this->variables['lemonway_is_test_mode']) {
                $this->variables['error_test_mode'] = $this->language->get('error_test_mode');
            }

            // Test the configuration
            if ($this->testConfig()) {
                $this->variables['error_success'] = $this->language->get('error_success');
            }
        } else { // If no method enabled
            $this->variables['error_no_method'] = $this->language->get('error_no_method');
        }

        $this->variables['error_custom_env'] = $this->language->get('error_custom_env');

        // Load tabs
        // About us
        $this->variables['about_us'] = $this->load->view('extension/payment/lemonway_aboutus.tpl', $this->variables);
        // Configuration
        $this->variables['configure'] = $this->load->view('extension/payment/lemonway_configure.tpl', $this->variables);
        // Credit Card
        $this->variables['cc'] = $this->load->view('extension/payment/lemonway_cc.tpl', $this->variables);

        $this->response->setOutput($this->load->view('extension/payment/lemonway.tpl', $this->variables));
    }

    private function validate()
    {
        $error = false;

        if (!$this->user->hasPermission('modify', 'extension/payment/lemonway')) {
            $this->variables['error_permission'] = $this->language->get('error_permission');
            $error = true;
        }

        //  Test mode
        if (!isset($this->request->post['lemonway_is_test_mode'])) {
            $this->request->post['lemonway_is_test_mode'] = 0;
        }

        // Default value if lemonway_css_url is empty
        if (empty($this->request->post['lemonway_css_url'])) {
            $this->request->post['lemonway_css_url'] = self::CSS_URL_DEFAULT;
        }

        // Debug mode
        if (!isset($this->request->post['lemonway_debug'])) {
            $this->request->post['lemonway_debug'] = 0;
        }

        // Environment name
        if (empty($this->request->post['lemonway_environment_name'])) {
            // If no custom environment => lwecommerce
            $env_name = self::LEMONWAY_ENVIRONMENT_DEFAULT;
        } else {
            // If custom environment
            $env_name = $this->request->post['lemonway_environment_name'];
        }
        
        // Generate API links
        $this->request->post['lemonway_directkit_url'] = sprintf(self::LEMONWAY_DIRECTKIT_FORMAT_URL_PROD, $env_name);
        $this->request->post['lemonway_webkit_url'] = sprintf(self::LEMONWAY_WEBKIT_FORMAT_URL_PROD, $env_name);
        $this->request->post['lemonway_directkit_url_test'] = sprintf(self::LEMONWAY_DIRECTKIT_FORMAT_URL_TEST, $env_name);
        $this->request->post['lemonway_webkit_url_test'] = sprintf(self::LEMONWAY_WEBKIT_FORMAT_URL_TEST, $env_name);

        //Credit Card status
        if (!isset($this->request->post['lemonway_status'])) {
            $this->request->post['lemonway_status'] = 0;
        }
        
        // One-click
        if (!isset($this->request->post['lemonway_oneclick_enabled'])) {
            $this->request->post['lemonway_oneclick_enabled'] = 0;
        }

        return !$error; // If no error => validated
    }

    // Test if the configuration is OK
    private function testConfig()
    {
        // Load settings
        $this->load->model('setting/setting');

        // TEST
        if ($this->variables['lemonway_is_test_mode']) {
            // DIRECTKIT TEST URL
            $dkUrl = $this->model_setting_setting->getSettingValue('lemonway_directkit_url_test');
        } //PROD
        else {
            // DIRECTKIT URL
            $dkUrl = $this->model_setting_setting->getSettingValue('lemonway_directkit_url');
        }

        require_once DIR_SYSTEM . '/library/lemonway/LemonWayService.php';

        // API connection
        $lemonwayService = new LemonWayService(
            $dkUrl,
            $this->variables['lemonway_api_login'],
            $this->variables['lemonway_api_password'],
            $this->variables['lemonway_is_test_mode'],
            substr($this->language->get('code'), 0, 2),
            $this->variables['lemonway_debug']
        );

        if (empty($this->variables['lemonway_environment_name'])) {
            // If lwecommerce, get wallet by email
            $params = array('email' => $this->variables['lemonway_api_login']);
        } else {
            // If custom env, get custom wallet
            $params = array('wallet' => $this->variables['lemonway_custom_wallet']);
        }

        $res = $lemonwayService->getWalletDetails($params);
        if (empty($this->variables['lemonway_environment_name'])) {
            // If lwecommerce, get wallet
            if (isset($res->WALLET->ID)) {
                $this->model_setting_setting->editSettingValue('lemonway', 'lemonway_wallet', $res->WALLET->ID);
            }
        }

        if (isset($res->E)) {
            $this->variables['error_api'] = $this->language->get('error_api') . " - " . $lemonwayService->printError($res->E);
            return false;
        } else {
            return true;
        }
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
