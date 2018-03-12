<?php 
Mollie_API_Autoloader::register();

class Mollie_API_Autoloader
{
    public static function autoload($class_name)
    {
        if( strpos($class_name, "Mollie_") === 0 ) 
        {
            $file_name = str_replace("_", "/", $class_name);
            $file_name = realpath(dirname(__FILE__) . "/../../" . $file_name . ".php");
            if( $file_name !== false ) 
            {
                require($file_name);
            }

        }

    }

    public static function register()
    {
        return spl_autoload_register(array( "Mollie_API_Autoloader", "autoload" ));
    }

    public static function unregister()
    {
        return spl_autoload_unregister(array( "Mollie_API_Autoloader", "autoload" ));
    }

}


