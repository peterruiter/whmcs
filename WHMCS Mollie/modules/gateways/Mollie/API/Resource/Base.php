<?php 

abstract class Mollie_API_Resource_Base
{
    protected $api = NULL;

    const REST_CREATE = Mollie_API_Client::HTTP_POST;
    const REST_UPDATE = Mollie_API_Client::HTTP_POST;
    const REST_READ = Mollie_API_Client::HTTP_GET;
    const REST_LIST = Mollie_API_Client::HTTP_GET;
    const REST_DELETE = Mollie_API_Client::HTTP_DELETE;
    const DEFAULT_LIMIT = 50;

    public function __construct(Mollie_API_Client $api)
    {
        $this->api = $api;
    }

    protected function getResourceName()
    {
        $class_parts = explode("_", get_class($this));
        return strtolower(end($class_parts));
    }

    private function buildQueryString(array $filters)
    {
        if( empty($filters) ) 
        {
            return "";
        }

        return "?" . http_build_query($filters, "", "&");
    }

    private function rest_create($rest_resource, $body, array $filters)
    {
        $result = $this->performApiCall(self::REST_CREATE, $rest_resource . $this->buildQueryString($filters), $body);
        return $this->copy($result, $this->getResourceObject());
    }

    private function rest_read($rest_resource, $id, array $filters)
    {
        if( empty($id) ) 
        {
            throw new Mollie_API_Exception("Invalid resource id.");
        }

        $id = urlencode($id);
        $result = $this->performApiCall(self::REST_READ, (string) $rest_resource . "/" . $id . $this->buildQueryString($filters));
        return $this->copy($result, $this->getResourceObject());
    }

    private function rest_list($rest_resource, $offset = 0, $limit = self::DEFAULT_LIMIT, array $filters)
    {
        $filters = array_merge(array( "offset" => $offset, "count" => $limit ), $filters);
        $api_path = $rest_resource . $this->buildQueryString($filters);
        $result = $this->performApiCall(self::REST_LIST, $api_path);
        $collection = $this->copy($result, new Mollie_API_Object_List());
        foreach( $result->data as $data_result ) 
        {
            $collection[] = $this->copy($data_result, $this->getResourceObject());
        }
        return $collection;
    }

    protected function copy($api_result, $object)
    {
        foreach( $api_result as $property => $value ) 
        {
            $object->$property = $value;
        }
        return $object;
    }

    abstract protected function getResourceObject();

    public function create(array $data = array(), array $filters = array())
    {
        $encoded = json_encode($data);
        if( version_compare(phpversion(), "5.3.0", ">=") ) 
        {
            if( json_last_error() != JSON_ERROR_NONE ) 
            {
                throw new Mollie_API_Exception("Error encoding parameters into JSON: '" . json_last_error() . "'.");
            }

        }
        else
        {
            if( $encoded === false ) 
            {
                throw new Mollie_API_Exception("Error encoding parameters into JSON.");
            }

        }

        return $this->rest_create($this->getResourceName(), $encoded, $filters);
    }

    public function get($resource_id, array $filters = array())
    {
        return $this->rest_read($this->getResourceName(), $resource_id, $filters);
    }

    public function all($offset = 0, $limit = 0, array $filters = array())
    {
        return $this->rest_list($this->getResourceName(), $offset, $limit, $filters);
    }

    protected function performApiCall($http_method, $api_method, $http_body = NULL)
    {
        $body = $this->api->performHttpCall($http_method, $api_method, $http_body);
        if( !($object = @json_decode($body)) ) 
        {
            throw new Mollie_API_Exception("Unable to decode Mollie response: '" . $body . "'.");
        }

        if( !empty($object->error) ) 
        {
            $exception = new Mollie_API_Exception("Error executing API call (" . $object->error->type . "): " . $object->error->message . ".");
            if( !empty($object->error->field) ) 
            {
                $exception->setField($object->error->field);
            }

            throw $exception;
        }

        return $object;
    }

}


