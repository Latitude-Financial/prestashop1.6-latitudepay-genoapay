<?php

class LatitudeRefundTransaction
{
    const TABLE_NAME = _DB_PREFIX_ . 'latitude_refund_transactions';
    public $id_refund;
    public $id_order;
    public $order;
    public $refund_date;
    public $refund_amount;
    public $reference;
    public $commission_amount;
    public $payment_gateway;

    public function __construct($refundId = null)
    {
        if (!is_null($refundId)) {
            $this->id_refund = $refundId;
            $this->__init();
        }
    }

    public static function fromRefundId($refundId)
    {
        return Db::getInstance()->getRow(
            "SELECT * FROM `" . self::TABLE_NAME . "`
			WHERE `id_refund` = '".$refundId."'", false
        );
    }

    public static function fromOrderId($orderId)
    {
        return self::getBy('id_order', (int)$orderId);
    }

    public static function getBy($field, $value)
    {
        return Db::getInstance()->executeS(
            'SELECT * FROM `' . self::TABLE_NAME . '`
			WHERE `' . $field . '` = ' . $value
        );
    }

    public function set($field, $value) {
        $this->{$field} = $value;
        return $this;
    }

    public function save()
    {
        if (self::fromRefundId($this->id_refund)) {
            // Update
            $sql = "UPDATE `" . self::TABLE_NAME . "` SET " .
                sprintf("`id_order` = %s, `refund_date` = '%s', `refund_amount` = %s, `reference` = '%s', `commission_amount` = %s, `payment_gateway` = '%s'", $this->id_order, $this->refund_date, $this->refund_amount, $this->reference, $this->commission_amount, $this->payment_gateway);

        } else {
            // Create
            $sql = "INSERT INTO `" . self::TABLE_NAME . "`(`id_refund`, `id_order`, `refund_date`, `refund_amount`, `reference`, `commission_amount`, `payment_gateway`) " .
                sprintf("VALUES ('%s', %s, '%s', %s, '%s', %s, '%s')", $this->id_refund, $this->id_order, $this->refund_date, $this->refund_amount, $this->reference, $this->commission_amount, $this->payment_gateway);
        }
        return Db::getInstance()->Execute($sql);
    }

    protected function __init()
    {
        $rTransaction = self::fromRefundId($this->id_refund);
        if ($rTransaction) {
            $this->id_order = $rTransaction['id_order'];
            $this->order = new Order((int)$this->id_order);
            $this->refund_date = $rTransaction['refund_date'];
            $this->refund_amount = $rTransaction['refund_amount'];
            $this->reference = $rTransaction['reference'];
            $this->commission_amount = $rTransaction['commission_amount'];
            $this->payment_gateway = $rTransaction['payment_gateway'];
        }
    }
}