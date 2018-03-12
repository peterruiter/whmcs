<?php 

class Mollie_API_Resource_Organizations extends Mollie_API_Resource_Base
{
    protected function getResourceObject()
    {
        return new Mollie_API_Object_Organization();
    }

    public function me()
    {
        return $this->get("me");
    }

}


