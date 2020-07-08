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

        $cart = $this->context->cart;

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
                $cart->id,
                self::PAYMENT_ACCEPECTED,
                $cart->getOrderTotal(),
                'Latitude Finance',
                '',
                array(
                    'transaction_id' => Tools::getValue('token')
                )
            );
        } elseif (in_array($responseState, self::PAYMENT_FAILED_STATES)) {
            $this->module->validateOrder(
                $cart->id,
                self::PAYMENT_ERROR,
                $cart->getOrderTotal(),
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

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer))
            Tools::redirect('index.php?controller=order&step=1');

        Tools::redirect('index.php?controller=order-confirmation&id_cart='. (int)$cart->id. '&id_module=' . (int)$this->module->id . '&id_order=' . $this->module->currentOrder. '&key=' . $customer->secure_key);
    }
}