<?php

class latitude_officialpaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = false;
    public $display_column_left = false;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $cart = $this->context->cart;
        if (!$this->module->checkCurrency($cart))
            Tools::redirect('index.php?controller=order');

        $this->context->smarty->assign(array(
            'nbProducts' => $cart->nbProducts(),
            'cust_currency' => $cart->id_currency,
            'currencies' => $this->module->getCurrency((int)$cart->id_currency),
            'total' => $cart->getOrderTotal(true, Cart::BOTH),
            'isoCode' => $this->context->language->iso_code,
            // 'apikey' => $this->module->chequeName,
            // 'apiPassword' => Tools::nl2br($this->module->address),
            'this_path' => $this->module->getPathUri(),
            'this_path_cheque' => $this->module->getPathUri(),
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/',
            'purchase_url' => $this->getPurchaseUrl(),
        ));

        // echo "<pre>";
        // var_dump(array(
        //     'nbProducts' => $cart->nbProducts(),
        //     'cust_currency' => $cart->id_currency,
        //     'currencies' => $this->module->getCurrency((int)$cart->id_currency),
        //     'total' => $cart->getOrderTotal(true, Cart::BOTH),
        //     'isoCode' => $this->context->language->iso_code,
        //     // 'apikey' => $this->module->chequeName,
        //     // 'apiPassword' => Tools::nl2br($this->module->address),
        //     'this_path' => $this->module->getPathUri(),
        //     'this_path_cheque' => $this->module->getPathUri(),
        //     'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/',
        //     'purchase_url' => $this->getPurchaseUrl(),
        // ));
        // echo "</pre>";

        $this->setTemplate('payment_execution.tpl');
    }

    // @todo: implment the actual logic
    public function getPurchaseUrl()
    {
        return 'http://www.google.co.nz/';
    }
}