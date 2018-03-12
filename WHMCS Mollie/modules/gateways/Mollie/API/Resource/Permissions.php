<?php 

class Mollie_API_Resource_Permissions extends Mollie_API_Resource_Base
{
    protected function getResourceObject()
    {
        return new Mollie_API_Object_Permission();
    }

    public function isGranted($permission_id)
    {
        $permission = $this->get($permission_id);
        if( $permission && $permission->granted ) 
        {
            return true;
        }

        return false;
    }

}


