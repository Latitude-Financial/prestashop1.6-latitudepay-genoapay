<?php
/**
 * This checks for the existence of an always-existing PrestaShop constant (its version number),
 * and if it does not exist, it stops the module from loading.
 * The sole purpose of this is to prevent malicious visitors to load this file directly.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Latitude_Official extends PaymentModule
{
    protected $_html = '';

    /**
     * @var string
     */
    const ENVIRONMENT_SANDBOX = 'sandbox';

    /**
     * @var string
     */
    const ENVIRONMENT_PRODUCTION = 'production';

    /**
     * @var string
     */
    const ENVIRONMENT_DEVELOPMENT = 'development';

    public function __construct()
    {
        /**
         * The value MUST be the name of the module's folder.
         * @var string
         */
        $this->name = 'latitude_official';

        /**
         * The title for the section that shall contain this module in PrestaShop's back office modules list.
         * payments_gateways => Payments & Gateways
         * @var string
         */
        $this->tab = 'payments_gateways';

        $this->version = '1.0';
        $this->author = 'MageBinary';

        /**
         * Indicates whether to load the module's class when displaying the "Modules" page in the back office.
         * If set at 0, the module will not be loaded, and therefore will spend less resources to generate the "Modules" page.
         * If your module needs to display a warning message in the "Modules" page, then you must set this attribute to 1.
         * @var integer
         */
        $this->need_instance = 0;

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6.99.99');

        /**
         * Indicates that the module's template files have been built with PrestaShop 1.6's bootstrap tools in mind
         * PrestaShop should not try to wrap the template code for the configuration screen
         * (if there is one) with helper tags.
         * @var boolean
         */
        $this->bootstrap = true;

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        // Calling the parent constuctor method must be done after the creation of the $this->name variable and before any use of the $this->l() translation method.
        parent::__construct();

        $this->displayName = $this->l('Latitude Finance Payment Module');
        $this->description = $this->l('Available to NZ residents who are 18 years old and over and have a valid debit or credit card.');
        $this->confirmUninstall = $this->l('Are you sure you to uninstall the module?');

        /**
         * Check cURL extension
         */
        if (is_callable('curl_init') === false) {
            $this->errors[] = $this->l('To be able to use this module, please activate cURL (PHP extension).');
        }

        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency has been set for this module.');
        }
    }

    /**
     * Install this module and register the following Hooks:
     *
     * @return bool
     */
    public function install()
    {
        if (!parent::install() || !$this->registerHook('payment') || !$this->registerHook('paymentOptions') || !$this->registerHook('paymentReturn')) {
             return false;
         }
         return true;
    }

    /**
     * Uninstall this module and remove it from all hooks
     *
     * @return bool
     */
    public function uninstall()
    {
        // @todo: remove the configurations
        return parent::uninstall();
    }

    // @todo: finish the implementation
    public function checkApiConnection($key, $secret)
    {
        return true;
    }

    public function hookPayment($params)
    {
        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        $this->smarty->assign(array(
            'this_path' => $this->_path,
            'logo' => _PS_BASE_URL_ . $this->_path . 'logos/genoapay.svg',
            'this_path_ssl' => Tools::getShopDomain(true, true) . __PS_BASE_URI__ . 'modules/'.$this->name.'/',
        ));

        return $this->display(__FILE__, 'payment.tpl');
    }

    /**
     * Display a message in the paymentReturn hook
     *
     * @param array $params
     * @return string
     */
    public function hookPaymentReturn($params)
    {
        /**
         * Verify if this module is enabled
         */
        if (!$this->active) {
            return;
        }

        // return $this->fetch('module:latitude_official/views/templates/hook/payment_return.tpl');
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Returns a string containing the HTML necessary to
     * generate a configuration screen on the admin
     *
     * @return string
     */
    public function getContent()
    {
        /* Check if SSL is enabled */
        if (!Configuration::get('PS_SSL_ENABLED')) {
            $this->warning[] = $this->l(
                'You must enable SSL on the store if you want to use this module in production.',
                $this->name
            );
        }

        $output = '';
        $output .= $this->postProcess();
        $output .= $this->renderSettingsForm();
        return $output;
    }

    public function renderSettingsForm()
    {
        // echo "<pre>";
        // var_dump(Country::getCountries($this->context->language->id, false));
        // echo "</pre>";
         $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                // 'description' => $this->l('Add payment methods on') . ' <a href="?tab=AdminUniPaySystem&token=' . Tools::getAdminToken('AdminUniPaySystem' .
                //         Tab::getIdFromClassName('AdminUniPaySystem') . $this->context->cookie->id_employee) .
                //     '" class="link">' . $this->l('Modules > Pay Systems tab') . '</a>',
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Title'),
                        'desc'  => $this->l('This controls the title which the user sees during checkout.'),
                        'name' => 'LATITUDE_FINANCE_TITLE',
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Description'),
                        'desc' => $this->l('This option can be set from your account portal. When the Save Changes button is clicked, this option will update automatically.'),
                        'name' => 'LATITUDE_FINANCE_DESCRIPTION',
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Debug Mode'),
                        'hint' => $this->l('Show Detailed Error Messages and API requests/responses in the log file.'),
                        'name' => 'LATITUDE_FINANCE_DEBUG_MODE',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'LATITUDE_FINANCE_DEBUG_MODE_ON',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'LATITUDE_FINANCE_DEBUG_MODE_OFF',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        )
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Minimum Order Total'),
                        'desc'  => $this->l('This option can be set from your account portal. When the Save Changes button is clicked, this option will update automatically.'),
                        'name' => 'LATITUDE_FINANCE_MIN_ORDER_TOTAL',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Maximum Order Total'),
                        'desc'  => $this->l('This option can be set from your account portal. When the Save Changes button is clicked, this option will update automatically.'),
                        'name' => 'LATITUDE_FINANCE_MAX_ORDER_TOTAL',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('API Key'),
                        'desc'  => $this->l('The Public Key for your GenoaPay account.'),
                        'name' => 'LATITUDE_FINANCE_PUBLIC_KEY',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('API Secret'),
                        'desc'  => $this->l('The Private Key for your GenoaPay account.'),
                        'name' => 'LATITUDE_FINANCE_PRIVATE_KEY',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Sandbox API Key'),
                        'desc'  => $this->l('The Public Key for your GenoaPay sandbox account.'),
                        'name' => 'LATITUDE_FINANCE_SANDBOX_PUBLIC_KEY',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Sandbox API Secret'),
                        'desc'  => $this->l('The Private Key for your GenoaPay sandbox account.'),
                        'name' => 'LATITUDE_FINANCE_SANDBOX_PRIVATE_KEY',
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Environment'),
                        'name' => 'LATITUDE_FINANCE_ENVIRONMENT',
                        'col' => 4,
                        'options' => array(
                            'query' => array(
                                array('id' => self::ENVIRONMENT_DEVELOPMENT, 'name' => $this->l('Development')),
                                array('id' => self::ENVIRONMENT_SANDBOX, 'name' => $this->l('Sandbox')),
                                array('id' => self::ENVIRONMENT_PRODUCTION, 'name' => $this->l('Production')),
                            ),
                            'id' => 'latitude_official_environment',
                            'name' => 'environment',
                            'default' => array(
                                'label' => $this->l('Sandbox'),
                                'value' => self::ENVIRONMENT_SANDBOX
                            )
                        )
                    ),
                    // array(
                    //     'type' => 'select',
                    //     'label' => $this->l('Environment'),
                    //     'options' => array(
                    //         array(
                    //             'value' => self::ENVIRONMENT_DEVELOPMENT,
                    //             'label' => $this->l('Development')
                    //         ),
                    //         array(
                    //             'value' => self::ENVIRONMENT_SANDBOX,
                    //             'label' => $this->l('Sandbox')
                    //         ),
                    //         array(
                    //             'value' => self::ENVIRONMENT_PRODUCTION,
                    //             'label' => $this->l('Production')
                    //         )
                    //     )
                    // )
                ),
                'submit' => array(
                    'name' => 'submitSave',
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSave';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }

    /**
     * @todo: Read the data from the API
     */
    public function getConfigFieldsValues()
    {
        return array(
            'LATITUDE_FINANCE_TITLE' => Tools::getValue('LATITUDE_FINANCE_TITLE', Configuration::get('LATITUDE_FINANCE_TITLE')),
            'LATITUDE_FINANCE_DESCRIPTION' => Tools::getValue('LATITUDE_FINANCE_DESCRIPTION', Configuration::get('LATITUDE_FINANCE_DESCRIPTION')),
            'LATITUDE_FINANCE_DEBUG_MODE' => Tools::getValue('LATITUDE_FINANCE_DEBUG_MODE', Configuration::get('LATITUDE_FINANCE_DEBUG_MODE')),
            'LATITUDE_FINANCE_MIN_ORDER_TOTAL' => Tools::getValue('LATITUDE_FINANCE_MIN_ORDER_TOTAL', Configuration::get('LATITUDE_FINANCE_MIN_ORDER_TOTAL')),
            'LATITUDE_FINANCE_MAX_ORDER_TOTAL' => Tools::getValue('LATITUDE_FINANCE_MAX_ORDER_TOTAL', Configuration::get('LATITUDE_FINANCE_MAX_ORDER_TOTAL')),
        );
    }

    protected function postProcess()
    {
        if (Tools::isSubmit('submitSave')) {
            if (Configuration::updateValue('latitude_offical', (int)Tools::getValue('latitude_offical'))) {
                return $this->displayConfirmation($this->l('Settings updated'));
            } else {
                return $this->displayError($this->l('Confirmation button') . ': ' . $this->l('Invaild choice'));
            }
        }
    }

    // public function getOfflinePaymentOption()
    // {
    //     $offlineOption = new PaymentOption();
    //     $offlineOption->setCallToActionText($this->l('Pay offline'))
    //                   ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
    //                   ->setAdditionalInformation($this->context->smarty->fetch('module:paymentexample/views/templates/front/payment_infos.tpl'))
    //                   ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/payment.jpg'));

    //     return $offlineOption;
    // }

    // public function getExternalPaymentOption()
    // {
    //     $externalOption = new PaymentOption();
    //     $externalOption->setCallToActionText($this->l('Pay external'))
    //                    ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
    //                    ->setInputs([
    //                         'token' => [
    //                             'name' =>'token',
    //                             'type' =>'hidden',
    //                             'value' =>'12345689',
    //                         ],
    //                     ])
    //                    ->setAdditionalInformation($this->context->smarty->fetch('module:paymentexample/views/templates/front/payment_infos.tpl'))
    //                    ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/payment.jpg'));

    //     return $externalOption;
    // }

    // public function getEmbeddedPaymentOption()
    // {
    //     $embeddedOption = new PaymentOption();
    //     $embeddedOption->setCallToActionText($this->l('Pay embedded'))
    //                    ->setForm($this->generateForm())
    //                    ->setAdditionalInformation($this->context->smarty->fetch('module:paymentexample/views/templates/front/payment_infos.tpl'))
    //                    ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/payment.jpg'));

    //     return $embeddedOption;
    // }

    // public function getIframePaymentOption()
    // {
    //     $iframeOption = new PaymentOption();
    //     $iframeOption->setCallToActionText($this->l('Pay iframe'))
    //                  ->setAction($this->context->link->getModuleLink($this->name, 'iframe', array(), true))
    //                  ->setAdditionalInformation($this->context->smarty->fetch('module:paymentexample/views/templates/front/payment_infos.tpl'))
    //                  ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/payment.jpg'));

    //     return $iframeOption;
    // }
}