<?php 

class Mollie_API_Exception extends Exception
{
    protected $_field = NULL;

    public function getField()
    {
        return $this->_field;
    }

    public function setField($field)
    {
        $this->_field = (string) $field;
    }

}


