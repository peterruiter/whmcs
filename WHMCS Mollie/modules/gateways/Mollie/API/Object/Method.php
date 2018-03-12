<?php 

class Mollie_API_Object_Method
{
    public $id = NULL;
    public $description = NULL;
    public $amount = NULL;
    public $image = NULL;

    const IDEAL = "ideal";
    const PAYSAFECARD = "paysafecard";
    const CREDITCARD = "creditcard";
    const MISTERCASH = "mistercash";
    const SOFORT = "sofort";
    const BANKTRANSFER = "banktransfer";
    const DIRECTDEBIT = "directdebit";
    const PAYPAL = "paypal";
    const BITCOIN = "bitcoin";
    const BELFIUS = "belfius";
    const PODIUMCADEAUKAART = "podiumcadeaukaart";

    public function getMinimumAmount()
    {
        if( empty($this->amount) ) 
        {
            return NULL;
        }

        return (double) $this->amount->minimum;
    }

    public function getMaximumAmount()
    {
        if( empty($this->amount) ) 
        {
            return NULL;
        }

        return (double) $this->amount->maximum;
    }

}


