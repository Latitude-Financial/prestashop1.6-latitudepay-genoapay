<?php

class latitude_officialrefundModuleFrontController extends ModuleFrontController
{
    /**
     * @var integer
     */
    protected $orderId;

    /**
     * @var string
     */
    protected $reference;

    /**
     * @var string
     */
    protected $transactionId;

    /**
     * The ajax controller for clicking the frontend GenoaPay refund or LatitudePay refund button
     * @return json
     */
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
            if ($this->refund()) {
                $message = sprintf('The refund with the transaction id: %s has been successfully performed. The reference of the order is: %s', $this->getTransactionId(), $this->getReference());
                $json = array(
                    'status' => 'success',
                    'message' => $message
                );

                PrestaShopLogger::addLog($message, 1, null, 'PaymentModule', (int)$this->orderId, true);
            } else {
                $json = array(
                    'status' => 'error',
                    'message' => 'Error occured when refund the order.'
                );
            }
        }

        header('Content-Type: application/json');
        echo Tools::jsonEncode($json);
    }

    protected function refund()
    {
        $response = [];
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
                throw new InvalidArgumentException(sprintf('The transaction ID for order %1$s is blank. A refund cannot be processed unless there is a valid transaction associated with the order.', $orderId));
            }
            /**
             * Add payment transaction to the order
             */
            $qtyList = [];
            $productList = $this->getProductList($order);
            $product = new Product(reset($productList)['product_id']);

            foreach ($productList as $idOrderDetail) {
                $orderDetail = new OrderDetail((int)$idOrderDetail);
                $orderQty = $orderDetail->product_quantity;
                $qtyList[(int)$idOrderDetail] = $orderQty;
            }

            // { "refundId":"488c4942-b937-4f7a-812e-ad388473143c","refundDate":"2020-07-16T14:15:48+12:00","reference":"G111-706133-UGQ","commissionAmount":0 }
            $response = $gateway->refund($refund);

            if (isset($response['reference'])) {
                $this->setReference($response['reference']);
            }

            if (isset($response['refundId'])) {
                $this->setTransactionId($response['refundId']);
            }

            // Log the refund response
            BinaryPay::log(json_encode($response), true, 'prestashop-latitude-finance.log');

            // refund successfully
            if (isset($response['refundId'])) {
                // Create creditslip
                OrderSlip::createOrderSlip($order, $productList, $qtyList, true);
                $this->createNewOrderHistory($order);

                return true;
            } else {
                // add note to the order
                // Message: The refund amount cannot be greater than the original payment amount
                // Display an error message
                return false;
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog($e->getMessage(), 1, null, 'PaymentModule', (int)$order->id_order, true);
            BinaryPay::log($e->getMessage(), true, 'prestashop-latitude-finance.log');
            return false;
        }
    }

    public function createNewOrderHistory($order)
    {
        // Create new OrderHistory
        $history = new OrderHistory();
        $history->id_order = $order->id;
        $history->id_employee = (int)$this->context->employee->id;

        $use_existings_payment = false;
        if (!$order->hasInvoice()) {
            $use_existings_payment = true;
        }
        $history->changeIdOrderState(_PS_OS_REFUND_, $order, $use_existings_payment);

        $carrier = new Carrier($order->id_carrier, $order->id_lang);
        $templateVars = array();
        if ($history->id_order_state == Configuration::get('PS_OS_SHIPPING') && $order->shipping_number) {
            $templateVars = array('{followup}' => str_replace('@', $order->shipping_number, $carrier->url));
        }

        // Save all changes
        if ($history->addWithemail(true, $templateVars)) {
            // synchronizes quantities if needed..
            if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                foreach ($order->getProducts() as $product) {
                    if (StockAvailable::dependsOnStock($product['product_id'])) {
                        StockAvailable::synchronize($product['product_id'], (int)$product['id_shop']);
                    }
                }
            }
        }
    }

    /**
     * Build the correct structure of the product list
     * @param  OrderCore $order
     * @return array
     */
    public function getProductList($order)
    {
        $productList = [];
        $orderDetailList = $order->getOrderDetailList();
        foreach ($orderDetailList as $productDetail) {
            $productList[] = $productDetail['id_order_detail'];
        }
        return $productList;
    }

    public function setReference($reference)
    {
        $this->reference = $reference;
        return $this;
    }

    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }
}