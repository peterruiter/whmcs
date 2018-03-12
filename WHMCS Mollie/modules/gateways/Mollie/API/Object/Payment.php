<?php 

class Mollie_API_Object_Payment
{
    public $id = NULL;
    public $mode = NULL;
    public $amount = NULL;
    public $amountRefunded = NULL;
    public $amountRemaining = NULL;
    public $description = NULL;
    public $method = NULL;
    public $status = self::STATUS_OPEN;
    public $expiryPeriod = NULL;
    public $createdDatetime = NULL;
    public $paidDatetime = NULL;
    public $cancelledDatetime = NULL;
    public $expiredDatetime = NULL;
    public $profileId = NULL;
    public $locale = NULL;
    public $metadata = NULL;
    public $details = NULL;
    public $links = NULL;

    const STATUS_OPEN = "open";
    const STATUS_PENDING = "pending";
    const STATUS_CANCELLED = "cancelled";
    const STATUS_EXPIRED = "expired";
    const STATUS_PAID = "paid";
    const STATUS_PAIDOUT = "paidout";
    const STATUS_REFUNDED = "refunded";
    const STATUS_CHARGED_BACK = "charged_back";

    public function isCancelled()
    {
        return $this->status == self::STATUS_CANCELLED;
    }

    public function isExpired()
    {
        return $this->status == self::STATUS_EXPIRED;
    }

    public function isOpen()
    {
        return $this->status == self::STATUS_OPEN;
    }

    public function isPending()
    {
        return $this->status == self::STATUS_PENDING;
    }

    public function isPaid()
    {
        return !empty($this->paidDatetime);
    }

    public function isRefunded()
    {
        return $this->status == self::STATUS_REFUNDED;
    }

    public function isChargedBack()
    {
        return $this->status == self::STATUS_CHARGED_BACK;
    }

    public function getPaymentUrl()
    {
        if( empty($this->links->paymentUrl) ) 
        {
            return NULL;
        }

        return $this->links->paymentUrl;
    }

    public function canBeRefunded()
    {
        return $this->amountRemaining !== NULL;
    }

    public function canBePartiallyRefunded()
    {
        return $this->canBeRefunded();
    }

    public function getAmountRefunded()
    {
        if( $this->amountRefunded ) 
        {
            return floatval($this->amountRefunded);
        }

        return 0;
    }

    public function getAmountRemaining()
    {
        if( $this->amountRemaining ) 
        {
            return floatval($this->amountRemaining);
        }

        return 0;
    }

}


