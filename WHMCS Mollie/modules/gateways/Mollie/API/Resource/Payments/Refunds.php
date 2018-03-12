<?php 

class Mollie_API_Resource_Payments_Refunds extends Mollie_API_Resource_Base
{
    private $payment_id = NULL;

    protected function getResourceObject()
    {
        return new Mollie_API_Object_Payment_Refund();
    }

    protected function getResourceName()
    {
        return "payments/" . urlencode($this->payment_id) . "/refunds";
    }

    public function with(Mollie_API_Object_Payment $payment)
    {
        $this->payment_id = $payment->id;
        return $this;
    }

}


