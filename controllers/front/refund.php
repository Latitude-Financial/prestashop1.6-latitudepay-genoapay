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
     * @var string
     */
    protected $amount;

    /**
     * The ajax controller for clicking the frontend GenoaPay refund or LatitudePay refund button
     * @return json
     */
    public function initContent() {
        $json = '';
        parent::initContent();

        if (Tools::getValue('ajax')) {
            parse_str(Tools::getValue('query_data'), $queryData);
            $this->orderId = $queryData['order_id'];
            $this->amount = $this->module->getAvailableRefundAmount($this->orderId);
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
        $order = new Order($this->orderId);
        try {
            $refundRequest = $this->module->_makeRefund($order, $this->amount);
            if ($refundRequest['success']) {
                $response = $refundRequest['response'];
                if (isset($response['reference'])) {
                    $this->setReference($response['reference']);
                }

                if (isset($response['refundId'])) {
                    $this->setTransactionId($response['refundId']);
                }

                // refund successfully
                if (isset($response['refundId'])) {
                    if ($this->module->getAvailableRefundAmount($this->orderId) === $order->getTotalPaid()) {
                        $qtyList = [];
                        $productList = $this->module->getProductList($order);
                        foreach ($productList as $idOrderDetail) {
                            $orderDetail = new OrderDetail((int)$idOrderDetail);
                            $orderQty = $orderDetail->product_quantity;
                            $qtyList[(int)$idOrderDetail] = $orderQty;
                        }
                        // Create creditslip
                        OrderSlip::createOrderSlip($order, $productList, $qtyList, true);
                        $this->createNewOrderHistory($order);
                    }
                    return true;
                }
            }
            return false;
        } catch (Exception $e) {
            PrestaShopLogger::addLog($e->getMessage(), 1, null, 'PaymentModule', (int)$order->id, true);
            BinaryPay::log($e->getMessage(), true, 'prestashop-latitude-finance.log');
            return false;
        }
    }

    public function createNewOrderHistory($order)
    {
        // Create new OrderHistory
        $history = new OrderHistory();
        $history->id_order = $order->id;
        $history->id_employee = Context::getContext()->cookie->id_employee;

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