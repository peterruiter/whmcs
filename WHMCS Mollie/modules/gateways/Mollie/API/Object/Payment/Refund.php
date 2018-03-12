<?php 

class Mollie_API_Object_Payment_Refund
{
    public $id = NULL;
    public $amount = NULL;
    public $payment = NULL;
    public $refundedDatetime = NULL;
    public $status = NULL;

    const STATUS_PENDING = "pending";
    const STATUS_PROCESSING = "processing";
    const STATUS_REFUNDED = "refunded";

    public function isPending()
    {
        return $this->status == self::STATUS_PENDING;
    }

    public function isProcessing()
    {
        return $this->status == self::STATUS_PROCESSING;
    }

    public function isTransferred()
    {
        return $this->status == self::STATUS_REFUNDED;
    }

}


