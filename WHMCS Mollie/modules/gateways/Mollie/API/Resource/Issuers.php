<?php 

class Mollie_API_Resource_Issuers extends Mollie_API_Resource_Base
{
    protected function getResourceObject()
    {
        return new Mollie_API_Object_Issuer();
    }

}


