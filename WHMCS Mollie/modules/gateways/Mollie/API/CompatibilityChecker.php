<?php 

class Mollie_API_CompatibilityChecker
{
    public static $MIN_PHP_VERSION = "5.2.0";
    public static $REQUIRED_CURL_FUNCTIONS = array( "curl_init", "curl_setopt", "curl_exec", "curl_error", "curl_errno", "curl_close", "curl_version" );

    public function checkCompatibility()
    {
        if( !$this->satisfiesPhpVersion() ) 
        {
            throw new Mollie_API_Exception_IncompatiblePlatform("The client requires PHP version >= " . self::$MIN_PHP_VERSION . ", you have " . PHP_VERSION . ".", Mollie_API_Exception_IncompatiblePlatform::INCOMPATIBLE_PHP_VERSION);
        }

        if( !$this->satisfiesJsonExtension() ) 
        {
            throw new Mollie_API_Exception_IncompatiblePlatform("PHP extension json is not enabled. Please make sure to enable 'json' in your PHP configuration.", Mollie_API_Exception_IncompatiblePlatform::INCOMPATIBLE_JSON_EXTENSION);
        }

        if( !$this->satisfiesCurlExtension() ) 
        {
            throw new Mollie_API_Exception_IncompatiblePlatform("PHP extension cURL is not enabled. Please make sure to enable 'curl' in your PHP configuration.", Mollie_API_Exception_IncompatiblePlatform::INCOMPATIBLE_CURL_EXTENSION);
        }

        if( !$this->satisfiesCurlFunctions() ) 
        {
            throw new Mollie_API_Exception_IncompatiblePlatform("This client requires the following cURL functions to be available: " . implode(", ", self::$REQUIRED_CURL_FUNCTIONS) . ". " . "Please check that none of these functions are disabled in your PHP configuration.", Mollie_API_Exception_IncompatiblePlatform::INCOMPATIBLE_CURL_FUNCTION);
        }

    }

    public function satisfiesPhpVersion()
    {
        return (bool) version_compare(PHP_VERSION, self::$MIN_PHP_VERSION, ">=");
    }

    public function satisfiesJsonExtension()
    {
        if( function_exists("extension_loaded") && extension_loaded("json") ) 
        {
            return true;
        }

        if( function_exists("json_encode") ) 
        {
            return true;
        }

        return false;
    }

    public function satisfiesCurlExtension()
    {
        if( function_exists("extension_loaded") && extension_loaded("curl") ) 
        {
            return true;
        }

        if( function_exists("curl_version") && curl_version() ) 
        {
            return true;
        }

        return false;
    }

    public function satisfiesCurlFunctions()
    {
        foreach( self::$REQUIRED_CURL_FUNCTIONS as $curl_function ) 
        {
            if( !function_exists($curl_function) ) 
            {
                return false;
            }

        }
        return true;
    }

}


