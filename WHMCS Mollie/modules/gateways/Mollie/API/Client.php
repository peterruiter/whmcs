<?php 

class Mollie_API_Client
{
    protected $api_endpoint = self::API_ENDPOINT;
    public $payments = NULL;
    public $payments_refunds = NULL;
    public $issuers = NULL;
    public $methods = NULL;
    public $permissions = NULL;
    public $organizations = NULL;
    public $profiles = NULL;
    public $settlements = NULL;
    protected $api_key = NULL;
    protected $oauth_access = NULL;
    protected $version_strings = array();
    protected $ch = NULL;

    const CLIENT_VERSION = "1.3.3";
    const API_ENDPOINT = "https://api.mollie.nl";
    const API_VERSION = "v1";
    const HTTP_GET = "GET";
    const HTTP_POST = "POST";
    const HTTP_DELETE = "DELETE";

    public function __construct()
    {
        $this->getCompatibilityChecker()->checkCompatibility();
        $this->payments = new Mollie_API_Resource_Payments($this);
        $this->payments_refunds = new Mollie_API_Resource_Payments_Refunds($this);
        $this->issuers = new Mollie_API_Resource_Issuers($this);
        $this->methods = new Mollie_API_Resource_Methods($this);
        $this->permissions = new Mollie_API_Resource_Permissions($this);
        $this->organizations = new Mollie_API_Resource_Organizations($this);
        $this->profiles = new Mollie_API_Resource_Profiles($this);
        $this->settlements = new Mollie_API_Resource_Settlements($this);
        $curl_version = curl_version();
        $this->addVersionString("Mollie/" . self::CLIENT_VERSION);
        $this->addVersionString("PHP/" . phpversion());
        $this->addVersionString("cURL/" . $curl_version["version"]);
        $this->addVersionString($curl_version["ssl_version"]);
    }

    public function __get($resource_name)
    {
        $undefined_resource = new Mollie_API_Resource_Undefined($this);
        $undefined_resource->setResourceName($resource_name);
        return $undefined_resource;
    }

    public function setApiEndpoint($url)
    {
        $this->api_endpoint = rtrim(trim($url), "/");
    }

    public function getApiEndpoint()
    {
        return $this->api_endpoint;
    }

    public function setApiKey($api_key)
    {
        $api_key = trim($api_key);
        if( !preg_match("/^(live|test)_\\w+\$/", $api_key) ) 
        {
            throw new Mollie_API_Exception("Invalid API key: '" . $api_key . "'. An API key must start with 'test_' or 'live_'.");
        }

        $this->api_key = $api_key;
        $this->oauth_access = false;
    }

    public function setAccessToken($access_token)
    {
        $access_token = trim($access_token);
        if( !preg_match("/^access_\\w+\$/", $access_token) ) 
        {
            throw new Mollie_API_Exception("Invalid OAuth access token: '" . $access_token . "'. An access token must start with 'access_'.");
        }

        $this->api_key = $access_token;
        $this->oauth_access = true;
    }

    public function usesOAuth()
    {
        return $this->oauth_access;
    }

    public function addVersionString($version_string)
    {
        $this->version_strings[] = str_replace(array( " ", "\t", "\n", "\r" ), "-", $version_string);
    }

    public function performHttpCall($http_method, $api_method, $http_body = NULL)
    {
        if( empty($this->api_key) ) 
        {
            throw new Mollie_API_Exception("You have not set an API key. Please use setApiKey() to set the API key.");
        }

        if( empty($this->ch) || !function_exists("curl_reset") ) 
        {
            $this->ch = curl_init();
        }
        else
        {
            curl_reset($this->ch);
        }

        $url = $this->api_endpoint . "/" . self::API_VERSION . "/" . $api_method;
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 10);
        $user_agent = join(" ", $this->version_strings);
        if( $this->usesOAuth() ) 
        {
            $user_agent .= " OAuth/2.0";
        }

        $request_headers = array( "Accept: application/json", "Authorization: Bearer " . $this->api_key, "User-Agent: " . $user_agent, "X-Mollie-Client-Info: " . php_uname() );
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $http_method);
        if( $http_body !== NULL ) 
        {
            $request_headers[] = "Content-Type: application/json";
            curl_setopt($this->ch, CURLOPT_POST, 1);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $http_body);
        }

        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($this->ch, CURLOPT_CAINFO, realpath(dirname(__FILE__) . "/cacert.pem"));
        $body = curl_exec($this->ch);
        if( strpos(curl_error($this->ch), "certificate subject name 'mollie.nl' does not match target host") !== false ) 
        {
            $request_headers[] = "X-Mollie-Debug: old OpenSSL found";
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $request_headers);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
            $body = curl_exec($this->ch);
        }

        if( curl_errno($this->ch) ) 
        {
            $message = "Unable to communicate with Mollie (" . curl_errno($this->ch) . "): " . curl_error($this->ch) . ".";
            curl_close($this->ch);
            $this->ch = NULL;
            throw new Mollie_API_Exception($message);
        }

        if( !function_exists("curl_reset") ) 
        {
            curl_close($this->ch);
            $this->ch = NULL;
        }

        return $body;
    }

    public function __destruct()
    {
        if( is_resource($this->ch) ) 
        {
            curl_close($this->ch);
        }

    }

    protected function getCompatibilityChecker()
    {
        static $checker = NULL;
        if( !$checker ) 
        {
            $checker = new Mollie_API_CompatibilityChecker();
        }

        return $checker;
    }

}


