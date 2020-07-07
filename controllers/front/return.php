<?php
class latitude_officialreturnModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;

    /**
     * @var integer - Order state
     */
    const PAYMENT_ACCEPECTED = 2;
    const PAYMENT_ERROR = 8;

    const PAYMENT_SUCCESS_STATES = [
        'COMPLETED'
    ];

    const PAYMENT_FAILED_STATES = [
        'UNKNOWN',
        'FAILED'
    ];

    /**
     * [initContent description]
     * @return [type]
     */
    public function initContent()
    {
        parent::initContent();
        // Add the validation
        $reference = Tools::getValue('reference');
        if (!$this->context->cookie->reference || $this->context->cookie->reference !== $reference) {
            Tools::redirect(Context::getContext()->shop->getBaseURL(true));
        }

        // validate the request and place the order or return to shopping cart page
        // base on the response
        // Array
        // (
        //     [token] => ed354b1f-dad0-40e0-b70b-45792440cfc7
        //     [reference] => 100012
        //     [message] => The payment was approved.
        //     [result] => COMPLETED
        //     [signature] => d8045ef4061dca7dbe986d30df1fedd9a59fb90a5c3711cb898e8e4246f8b104
        //     [module] => latitude_official
        //     [controller] => return
        //     [fc] => module
        // )
        // print_r(Tools::getValue('result'));
        $responseState = Tools::getValue('result');
        // success
        if (in_array($responseState, self::PAYMENT_SUCCESS_STATES)) {
            $this->module->validateOrder(
                $this->context->cart->id,
                self::PAYMENT_ACCEPECTED,
                $this->context->cart->getOrderTotal(),
                'Latitude Finance',
                '',
                array(
                    'transaction_id' => Tools::getValue('token')
                )
            );
        } elseif (in_array($responseState, self::PAYMENT_FAILED_STATES)) {
            $this->module->validateOrder(
                $this->context->cart->id,
                self::PAYMENT_ERROR,
                $this->context->cart->getOrderTotal(),
                'Latitude Finance',
                '',
                array(
                    'transaction_id' => Tools::getValue('token')
                )
            );
        } else {
            // For cancel
            Tool::redirect('order');
        }

        $id_order = Order::getOrderByCartId($this->context->cart->id);
        $url = Context::getContext()->link->getPageLink(
            'order-confirmation',
            true,
            null,
            array(
                'id_cart' => (int)$this->context->cart->id,
                'id_module' => (int)$this->module->id,
                'id_order' => (int)$id_order,
                'key' => $returnValues['secure_key']
            )
        );

        Tools::redirect($url);
    }
}