<?php 

class Mollie_API_Resource_Undefined extends Mollie_API_Resource_Base
{
    protected $resource_name = NULL;
    protected $parent_id = NULL;

    protected function getResourceObject()
    {
        return new stdClass();
    }

    public function setResourceName($resource_name)
    {
        $this->resource_name = strtolower($resource_name);
    }

    public function withParentId($parent_id)
    {
        $this->parent_id = $parent_id;
        return $this;
    }

    public function with($parent)
    {
        $this->parent_id = $parent->id;
        return $this;
    }

    public function getResourceName()
    {
        if( strpos($this->resource_name, "_") !== false ) 
        {
            list($parent_resource, $child_resource) = explode("_", $this->resource_name, 2);
            if( !strlen($this->parent_id) ) 
            {
                throw new Mollie_API_Exception("Subresource '" . $this->resource_name . "' used without parent '" . $parent_resource . "' ID.");
            }

            return (string) $parent_resource . "/" . $this->parent_id . "/" . $child_resource;
        }

        return $this->resource_name;
    }

}


