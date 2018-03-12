<?php 

class Mollie_API_Resource_Payments extends Mollie_API_Resource_Base
{
    const RESOURCE_ID_PREFIX = "tr_";

    protected function getResourceObject()
    {
        return new Mollie_API_Object_Payment();
    }

    public function get($payment_id, array $filters = array())
    {
        if( empty($payment_id) || strpos($payment_id, self::RESOURCE_ID_PREFIX) !== 0 ) 
        {
            throw new Mollie_API_Exception("Invalid payment ID: '" . $payment_id . "'. A payment ID should start with '" . self::RESOURCE_ID_PREFIX . "'.");
        }

        return parent::get($payment_id, $filters);
    }

    public function refund(Mollie_API_Object_Payment $payment, $amount = NULL)
    {
        $resource = (string) $this->getResourceName() . "/" . urlencode($payment->id) . "/refunds";
        $body = NULL;
        if( $amount ) 
        {
            $body = json_encode(array( "amount" => $amount ));
        }

        $result = $this->performApiCall(self::REST_CREATE, $resource, $body);
        if( !empty($result->payment) ) 
        {
            foreach( $result->payment as $payment_key => $payment_value ) 
            {
                $payment->$payment_key = $payment_value;
            }
        }

        return $this->copy($result, new Mollie_API_Object_Payment_Refund());
    }

}


