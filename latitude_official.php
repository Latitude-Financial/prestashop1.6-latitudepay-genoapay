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

        /**
         * @todo: Need to find out what versions will be compatible with
         */
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6');

        /**
         * Indicates that the module's template files have been built with PrestaShop 1.6's bootstrap tools in mind
         * PrestaShop should not try to wrap the template code for the configuration screen
         * (if there is one) with helper tags.
         * @var boolean
         */
        $this->bootstrap = true;

        // Calling the parent constuctor method must be done after the creation of the $this->name variable and before any use of the $this->l() translation method.
        parent::__construct();

        $this->displayName = $this->l('Latitude Finance');
        $this->description = $this->l('Available to NZ residents who are 18 years old and over and have a valid debit or credit card.');

        /**
         * Check cURL extension
         */
        if (is_callable('curl_init') === false) {
            $this->errors[] = $this->l('To be able to use this module, please activate cURL (PHP extension).');
        }
    }
}