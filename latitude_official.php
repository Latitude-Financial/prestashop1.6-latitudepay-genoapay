<?php
/**
 * This checks for the existence of an always-existing PrestaShop constant (its version number),
 * and if it does not exist, it stops the module from loading.
 * The sole purpose of this is to prevent malicious visitors to load this file directly.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(__DIR__ . '/includes/autoload.php');

class Latitude_Official extends PaymentModule
{
    protected $_html = '';

    /**
     * @var string
     */
    const ENVIRONMENT_DEVELOPMENT = 'development';

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
    const ENVIRONMENT = 'LATITUDE_FINANCE_ENVIRONMENT';

    /**
     * @var string - The data would be fetch from the API
     */
    const LATITUDE_FINANCE_TITLE = 'LATITUDE_FINANCE_TITLE';
    const LATITUDE_FINANCE_DESCRIPTION = 'LATITUDE_FINANCE_DESCRIPTION';
    const LATITUDE_FINANCE_MIN_ORDER_TOTAL = 'LATITUDE_FINANCE_MIN_ORDER_TOTAL';
    const LATITUDE_FINANCE_MAX_ORDER_TOTAL = 'LATITUDE_FINANCE_MAX_ORDER_TOTAL';

    /**
     * @var boolean
     */
    const LATITUDE_FINANCE_DEBUG_MODE = 'LATITUDE_FINANCE_DEBUG_MODE';

    /**
     * @var string
     */
    const LATITUDE_FINANCE_PUBLIC_KEY = 'LATITUDE_FINANCE_PUBLIC_KEY';

    /**
     * @var string
     */
    const LATITUDE_FINANCE_PRIVATE_KEY = 'LATITUDE_FINANCE_PRIVATE_KEY';

    /**
     * @var string
     */
    const LATITUDE_FINANCE_SANDBOX_PUBLIC_KEY = 'LATITUDE_FINANCE_SANDBOX_PUBLIC_KEY';

    /**
     * @var string
     */
    const LATITUDE_FINANCE_SANDBOX_PRIVATE_KEY = 'LATITUDE_FINANCE_SANDBOX_PRIVATE_KEY';


    public function __construct()
    {
        /**
         * The value MUST be the name of the module's folder.
         * @var string
         */
        $this->name = 'latitude_official';
        $this->gatewayName = 'genoapay';

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

        $this->configuration = [];
        $this->gateway = $this->getGateway();

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
    public function checkApiConnection($publicKey = null, $privateKey = null)
    {
        try {
            $configuration = $this->getConfiguration();
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }

        if (empty($configuration)) {
            return false;
        }

        return true;
    }

    public function getConfiguration()
    {
        // initialize payment gateway
        $this->gateway = $gateway = $this->getGateway();

        if (!$gateway) {
            throw new Exception('The payment gateway cannot been initialized.');
        }

        if (empty($this->configuration)) {
            $this->configuration = $gateway->configuration();
        }

        return $this->configuration;
    }

    /**
     * retrieve PostPassword from database
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getCredentials()
    {
        $environment = Configuration::get(self::ENVIRONMENT);
        $publicKey = $privateKey = '';
        switch ($environment) {
            case self::ENVIRONMENT_SANDBOX:
            case self::ENVIRONMENT_DEVELOPMENT:
                $publicKey = Configuration::get(self::LATITUDE_FINANCE_SANDBOX_PUBLIC_KEY);
                $privateKey = Configuration::get(self::LATITUDE_FINANCE_SANDBOX_PRIVATE_KEY);
                break;
            case self::ENVIRONMENT_PRODUCTION:
                $publicKey = Configuration::get(self::LATITUDE_FINANCE_PUBLIC_KEY);
                $privateKey = Configuration::get(self::LATITUDE_FINANCE_PRIVATE_KEY);
                break;
            default:
                throw new Exception('Failed to get credentials because the environment value is not correct.');
                break;
        }

        $credentials = array(
            'username'      => $publicKey,
            'password'      => $privateKey,
            'environment'   => $environment,
            'accountId'     => ''
        );
        return $credentials;
    }

    public function getGateway()
    {
        if (isset($this->gateway)) {
            return $this->gateway;
        }

        try {
            $className = (isset(explode('_', $this->gatewayName)[1])) ? ucfirst(explode('_', $this->gatewayName)[1]) : ucfirst($this->gatewayName);
            // @todo: validate credentials coming back from the account
            $this->gateway = BinaryPay::getGateway($className, $this->getCredentials());
        } catch (BinaryPay_Exception $e) {
            $this->errors[] =  $this->l($className .': '. $e->getMessage());
            BinaryPay::log($e->getMessage(), true, 'prestashop-latitude-finance.log');
        } catch (Exception $e) {
            $this->errors[] = $this->l($className . ': ' . $e->getMessage());
            BinaryPay::log($e->getMessage(), true, 'prestashop-latitude-finance.log');
        }

        if (!isset($this->gateway)) {
            throw new Exception('The gateway object did not initialized correctly.');
        }

        // log everything
        if (Configuration::get(self::LATITUDE_FINANCE_DEBUG_MODE)) {
            $this->gateway->setConfig(['debug' => true]);
        }

        return $this->gateway;
    }

    public function hookPayment($params)
    {
        $cartAmount = $params['cart']->getOrderTotal();

        if (!$this->active || !$this->isOrderAmountAvailable($cartAmount)) {
            return;
        }

        if (!$this->checkApiConnection()) {
            $this->context->smarty->assign(array(
                'latitudeError' => $this->l(
                    'No credentials have been provided for Latitude Finance. Please contact the owner of the website.',
                    $this->name
                )
            ));
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
                        'name' => self::LATITUDE_FINANCE_DEBUG_MODE,
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
                        'name' => self::LATITUDE_FINANCE_MIN_ORDER_TOTAL,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Maximum Order Total'),
                        'desc'  => $this->l('This option can be set from your account portal. When the Save Changes button is clicked, this option will update automatically.'),
                        'name' => self::LATITUDE_FINANCE_MAX_ORDER_TOTAL,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('API Key'),
                        'desc'  => $this->l('The Public Key for your GenoaPay account.'),
                        'name' => self::LATITUDE_FINANCE_PUBLIC_KEY,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('API Secret'),
                        'desc'  => $this->l('The Private Key for your GenoaPay account.'),
                        'name' => self::LATITUDE_FINANCE_PRIVATE_KEY,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Sandbox API Key'),
                        'desc'  => $this->l('The Public Key for your GenoaPay sandbox account.'),
                        'name' => self::LATITUDE_FINANCE_SANDBOX_PUBLIC_KEY,
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Sandbox API Secret'),
                        'desc'  => $this->l('The Private Key for your GenoaPay sandbox account.'),
                        'name' => self::LATITUDE_FINANCE_SANDBOX_PRIVATE_KEY,
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Environment'),
                        'name' => self::ENVIRONMENT,
                        'col' => 4,
                        'options' => array(
                            'query' => $this->getEnvironments(),
                            'id' => 'id_option',
                            'name' => 'environment',
                        )
                    ),
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

    public function getEnvironments()
    {
        return array(
            array(
                'id_option' => self::ENVIRONMENT_DEVELOPMENT,
                'environment' => 'Development'
            ),
            array(
                'id_option' => self::ENVIRONMENT_SANDBOX,
                'environment' => 'Sandbox'
            ),
            array(
                'id_option' => self::ENVIRONMENT_PRODUCTION,
                'environment' => 'Production'
            ),
        );
    }

    public function getConfigFieldsValues()
    {
        return array(
            self::LATITUDE_FINANCE_TITLE => Tools::getValue(self::LATITUDE_FINANCE_TITLE, Configuration::get(self::LATITUDE_FINANCE_TITLE)),
            self::LATITUDE_FINANCE_DESCRIPTION => Tools::getValue(self::LATITUDE_FINANCE_DESCRIPTION, Configuration::get(self::LATITUDE_FINANCE_DESCRIPTION)),
            self::LATITUDE_FINANCE_DEBUG_MODE => Tools::getValue(self::LATITUDE_FINANCE_DEBUG_MODE, Configuration::get(self::LATITUDE_FINANCE_DEBUG_MODE)),
            self::ENVIRONMENT => Tools::getValue(self::ENVIRONMENT, Configuration::get(self::ENVIRONMENT)),
            self::LATITUDE_FINANCE_MIN_ORDER_TOTAL => Tools::getValue(self::LATITUDE_FINANCE_MIN_ORDER_TOTAL, Configuration::get(self::LATITUDE_FINANCE_MIN_ORDER_TOTAL)),
            self::LATITUDE_FINANCE_MAX_ORDER_TOTAL => Tools::getValue(self::LATITUDE_FINANCE_MAX_ORDER_TOTAL, Configuration::get(self::LATITUDE_FINANCE_MAX_ORDER_TOTAL)),
            self::LATITUDE_FINANCE_PUBLIC_KEY => Tools::getValue(self::LATITUDE_FINANCE_PUBLIC_KEY, Configuration::get(self::LATITUDE_FINANCE_PUBLIC_KEY)),
            self::LATITUDE_FINANCE_PRIVATE_KEY => Tools::getValue(self::LATITUDE_FINANCE_PRIVATE_KEY, Configuration::get(self::LATITUDE_FINANCE_PRIVATE_KEY)),
            self::LATITUDE_FINANCE_SANDBOX_PUBLIC_KEY => Tools::getValue(self::LATITUDE_FINANCE_SANDBOX_PUBLIC_KEY, Configuration::get(self::LATITUDE_FINANCE_SANDBOX_PUBLIC_KEY)),
            self::LATITUDE_FINANCE_SANDBOX_PRIVATE_KEY => Tools::getValue(self::LATITUDE_FINANCE_SANDBOX_PRIVATE_KEY, Configuration::get(self::LATITUDE_FINANCE_SANDBOX_PRIVATE_KEY)),
        );
    }

    /**
     * @todo: Dynamic payment gateway by store currency
     */
    protected function postProcess()
    {
        try {
            $configuration = $this->getConfiguration();
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }

        if (Tools::isSubmit('submitSave')) {
            // The data fetched from Latitude Finance API
            Configuration::updateValue(self::LATITUDE_FINANCE_TITLE, $this->getConfigData('name', $configuration, 'LatitudePay'));
            Configuration::updateValue(self::LATITUDE_FINANCE_DESCRIPTION, $this->getConfigData('description', $configuration, 'LatitudePay'));
            Configuration::updateValue(self::LATITUDE_FINANCE_MIN_ORDER_TOTAL, $this->getConfigData('minimumAmount', $configuration, 'LatitudePay'));
            Configuration::updateValue(self::LATITUDE_FINANCE_MAX_ORDER_TOTAL, $this->getConfigData('maximumAmount', $configuration, 'LatitudePay'));

            // The values set by the shop owner
            Configuration::updateValue(self::LATITUDE_FINANCE_DEBUG_MODE, Tools::getValue(self::LATITUDE_FINANCE_DEBUG_MODE));
            Configuration::updateValue(self::ENVIRONMENT, Tools::getValue(self::ENVIRONMENT));
            Configuration::updateValue(self::LATITUDE_FINANCE_PUBLIC_KEY, Tools::getValue(self::LATITUDE_FINANCE_PUBLIC_KEY));
            Configuration::updateValue(self::LATITUDE_FINANCE_PRIVATE_KEY, Tools::getValue(self::LATITUDE_FINANCE_PRIVATE_KEY));
            Configuration::updateValue(self::LATITUDE_FINANCE_SANDBOX_PUBLIC_KEY, Tools::getValue(self::LATITUDE_FINANCE_SANDBOX_PUBLIC_KEY));
            Configuration::updateValue(self::LATITUDE_FINANCE_SANDBOX_PRIVATE_KEY, Tools::getValue(self::LATITUDE_FINANCE_SANDBOX_PRIVATE_KEY));

            if (Configuration::updateValue('latitude_offical', (int)Tools::getValue('latitude_offical'))) {
                return $this->displayConfirmation($this->l('Settings updated'));
            } else {
                return $this->displayError($this->l('Confirmation button') . ': ' . $this->l('Invaild choice'));
            }
        }
    }

    protected function getMinOrderTotal()
    {
        if (!$this->configuration) {
            $this->getConfiguration();
        }
        return $this->getConfigData('minimumAmount', $this->configuration);
    }

    protected function getMaxOrderTotal()
    {
        if (!$this->configuration) {
            $this->getConfiguration();
        }
        return $this->getConfigData('maximumAmount', $this->configuration);
    }

    protected function isOrderAmountAvailable($amount)
    {
        $minOrderTotal = $this->getMinOrderTotal();
        $maxOrderTotal = $this->getMaxOrderTotal();

        if ($amount > $maxOrderTotal || $amount < $minOrderTotal) {
            return false;
        }
        return true;
    }

    protected function getConfigData($key, $array, $default = '')
    {
        $value = isset($array[$key]) ? $array[$key] : $default;
        return $value;
    }
}