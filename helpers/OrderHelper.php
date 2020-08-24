<?php
class OrderHelper
{
    public static function getTotalRefundedAmount($orderId) {
        $result = 0;
        $rTrans = LatitudeRefundTransaction::fromOrderId((int) $orderId);
        if (!$rTrans) {
            return $result;
        }
        foreach ($rTrans as $tran) {
            $result += floatval($tran['refund_amount']);
        }
        return $result;
    }

    public function createRefundTransaction($data) {
        $rTransaction = new LatitudeRefundTransaction();
        foreach ($data as $field => $value) {
            $rTransaction->set($field, $value);
        }
        if ($rTransaction->save()) {
            return $rTransaction;
        }
        return false;
    }
}
