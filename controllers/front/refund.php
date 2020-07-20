<?php

class latitude_officialrefundModuleFrontController extends ModuleFrontController
{
    protected $orderId;

    public function initContent() {
        $row = array();
        $json = '';
        parent::initContent();

        if (Tools::getValue('ajax')) {
            parse_str(Tools::getValue('query_data'), $queryData);
            $this->orderId = $queryData['order_id'];
            /**
             * @todo: validate the request by using _id and _secret to avoid direct access
             */
            // if refund successed
            if ($this->refund()) {
                $json = array(
                    'status' => 'success',
                    'message' => 'hahahaha'
                );
            } else {
                $json = array(
                    'status' => 'error',
                    'message' => $this->l('Error when getting product informations.')
                );
            }
        }
        header('Content-Type: application/json');
        echo Tools::jsonEncode($json);
    }

    protected function refund()
    {
        $currencyCode = $this->context->currency->iso_code;
        $gateway = $this->module->getGateway();
        $order = new Order($this->orderId);
        $payments = $order->getOrderPayments();
        $credentials = $this->module->getCredentials();

        /**
         * @todo: check refund has been done
         */
        if (count($payments) >= 2 || count($payments) < 1) {
            $this->errors = "The order has been refunded already.";
            return;
        }

        $payment = reset($payments);
        $transactionId = $payment->transaction_id;
        $amount = $payment->amount;
        $reference = $payment->order_reference;

        $refund = array(
            BinaryPay_Variable::PURCHASE_TOKEN  => $transactionId,
            BinaryPay_Variable::CURRENCY        => $currencyCode,
            BinaryPay_Variable::AMOUNT          => $amount,
            BinaryPay_Variable::REFERENCE       => $reference,
            BinaryPay_Variable::REASON          => '',
            BinaryPay_Variable::PASSWORD        => $credentials['password']
        );

        try {
            if (empty($transactionId)) {
                throw new InvalidArgumentException($this->l(sprintf('The transaction ID for order %1$s is blank. A refund cannot be processed unless there is a valid transaction associated with the order.', $orderId)));
            }
            /**
             * Add payment transaction to the order
             */
            $qtyList = [];
            $productList = $order->getOrderDetailList();
            $product = new Product(reset($productList)['product_id']);

            foreach ($productList as $idOrderDetail) {
                $orderDetail = new OrderDetail((int)$idOrderDetail);
                $orderQty = $orderDetail->product_quantity;
                $qtyList[(int)$idOrderDetail] = $orderQty;
            }

            // { "refundId":"488c4942-b937-4f7a-812e-ad388473143c","refundDate":"2020-07-16T14:15:48+12:00","reference":"G111-706133-UGQ","commissionAmount":0 }
            $response = $gateway->refund($refund);

            // Log the refund response
            BinaryPay::log(json_encode($response), true, 'prestashop-latitude-finance.log');

            // refund successfully
            if (isset($response['refundId'])) {
                // Create creditslip
                OrderSlip::createOrderSlip($order, $productList, $qtyList, true);

                $history = new OrderHistory();
                $history->changeIdOrderState(_PS_OS_REFUND_, $this->orderId);
            } else {
                // add note to the order
                // Message: The refund amount cannot be greater than the original payment amount
            }
        } catch (Exception $e) {
            BinaryPay::log($e->getMessage(), true, 'prestashop-latitude-finance.log');
        }
        return true;
    }

    /**
     * process_refund
     * @todo : BinaryPay::log($e->getMessage(), true, 'woocommerce-genoapay.log'); extension wide.
     */
    // public function process_refund($order_id, $amount = null, $reason = '')
    // {
    //     $gateway = $this->get_gateway();
    //     $order = wc_get_order($order_id);
    //     $transaction_id = $order->get_transaction_id();

    //     /**
    //      * @todo support to add refund reason via wordpress backend
    //      */
    //     $refund = array(
    //          BinaryPay_Variable::PURCHASE_TOKEN  => $transaction_id,
    //          BinaryPay_Variable::CURRENCY        => $this->currency_code,
    //          BinaryPay_Variable::AMOUNT          => $amount,
    //          BinaryPay_Variable::REFERENCE       => $order->get_id(),
    //          BinaryPay_Variable::REASON          => '',
    //          BinaryPay_Variable::PASSWORD        => $this->credentials['password']
    //     );

    //     try {
    //         if (empty($transaction_id)) {
    //             throw new InvalidArgumentException(sprintf(__ ('The transaction ID for order %1$s is blank. A refund cannot be processed unless there is a valid transaction associated with the order.', 'woocommerce-payment-gateway-latitudefinance' ), $order_id ));
    //         }
    //         $response = $gateway->refund($refund);
    //         $order->update_meta_data('_transaction_status', $response['status']);
    //         $order->add_order_note (
    //             sprintf(__('Refund successful. Amount: %1$s. Refund ID: %2$s', 'woocommerce-payment-gateway-latitudefinance'),
    //             wc_price($amount, array(
    //                 'currency' => $order->get_currency()
    //             )
    //         ), $response['refundId']));
    //         $order->save();
    //     } catch (Exception $e) {
    //         BinaryPay::log($e->getMessage(), true, 'woocommerce-genoapay.log');
    //         return new WP_Error('refund-error', sprintf(__('Exception thrown while issuing refund. Reason: %1$s Exception class: %2$s', 'woocommerce-payment-gateway-latitudefinance'), $e->getMessage(), get_class($e)));
    //     }
    //     return true;
    // }
}