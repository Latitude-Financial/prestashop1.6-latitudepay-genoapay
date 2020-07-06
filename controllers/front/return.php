<?php
class latitude_officialreturnModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;

    /**
     * @var integer - Order state
     */
    const PAYMENT_ACCEPECTED = 2;

    /**
     * [initContent description]
     * @return [type]
     */
    public function initContent()
    {
        parent::initContent();

        //  public function validateOrder($id_cart, $id_order_state, $amount_paid, $payment_method = 'Unknown', $message = null, $extra_vars = array(), $currency_special = null, $dont_touch_amount = false, $secure_key = false, Shop $shop = null)

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
        // echo "<pre>";
        // print_r(Tools::getValue('result'));
        // print_r(Tools::getValue('signature'));
        // print_r(Tools::getValue('token'));
        // print_r(Tools::getValue('reference'));

        if (Tools::getValue('result') === 'COMPLETED') {
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

        // echo "</pre>";
        // die('123123123');
    }
}