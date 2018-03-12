<?php
if( !defined("DS") ) {
    define("DS", DIRECTORY_SEPARATOR, true);
}

include(dirname(__DIR__) . "/../../configuration.php");
include_once("API" . DS . "Autoloader.php");
if( !function_exists("whmcs_config_settings") ) 
{
function whmcs_config_settings()
{
    global $db_host;
    global $db_username;
    global $db_password;
    global $db_name;
	
	
	
    $link = mysqli_connect($db_host, $db_username, $db_password, $db_name);
    $query = mysqli_query($link, "SELECT * FROM tblconfiguration");
    if( !$query ) 
    {
        exit( "Could not connect: " . mysqli_error($query) );
    }

    while( $results = mysqli_fetch_assoc($query) ) 
    {
        switch( $results["setting"] ) 
        {
            case "SystemURL":
                $SystemURL = $results["value"];
                break;
            case "CompanyName":
                $CompanyName = $results["value"];
                break;
        }
    }
    return array( $SystemURL, $CompanyName );
}

}

if( !function_exists("mollie_gateway_settings") ) 
{
function mollie_gateway_settings()
{
    global $db_host;
    global $db_username;
    global $db_password;
    global $db_name;
    $link = mysqli_connect($db_host, $db_username, $db_password, $db_name);
    $query = mysqli_query($link, "SELECT * FROM `tblpaymentgateways` WHERE `gateway` = 'mollie'");
    if( !$query ) 
    {
        exit( "Error: " . mysqli_error($link) );
    }

    while( $results = mysqli_fetch_assoc($query) ) 
    {
        switch( $results["setting"] ) 
        {           
            case "mdescription":
                $description = $results["value"];
                break;
            case "mlivekey":
                $mlivekey = $results["value"];
                break;
            case "mtestkey":
                $mtestkey = $results["value"];
                break;
            case "mtestmode":
                $mtestmode = $results["value"];
                break;
            case "mtaxsetting":
                $mtaxsetting = $results["value"];
                break;
        }
    }
    return array($description, $mlivekey, $mtestkey, $mtestmode, $mtaxsetting );
}

}

if( !function_exists("mollie_transaction_fee") ) 
{
function mollie_transaction_fee($method)
{
    switch( $method ) 
    {
        case "ideal":
            $fixed = 0.45;
            $variable = 0;
            $typeName = "iDEAL";
            break;
        case "creditcard":
            $fixed = 0.25;
            $variable = 2.8;
            $typeName = "CreditCard";
            break;
        case "mistercash":
            $fixed = 0.25;
            $variable = 1.8;
            $typeName = "Bancontact/Mister Cash";
            break;
        case "sofort":
            $fixed = 0.25;
            $variable = 0.9;
            $typeName = "SOFORT Banking";
            break;
        case "belfius":
            $fixed = 0.25;
            $variable = 0.9;
            $typeName = "Belfius";
            break;
        case "banktransfer":
            $fixed = 0.25;
            $variable = 0;
            $typeName = "Overboeking";
            break;
        case "directdebit":
            $fixed = 0.45;
            $variable = 0;
            $typeName = "SEPA-incasso";
            break;
        case "bitcoin":
            $fixed = 0.25;
            $variable = 0;
            $typeName = "Bitcoin";
            break;
        case "paypal":
            $fixed = 0.45;
            $variable = 3.4;
            $typeName = "PayPal";
            break;
        case "podiumcadeaukaart":
            $fixed = 0.65;
            $variable = 0;
            $typeName = "Podium Cadeaukaart";
            break;
        case "paysafecard":
            $fixed = 0;
            $variable = 15;
            $typeName = "Paysafecard";
            break;
        case "acceptmail":
            $fixed = 0.99;
            $variable = 0;
            $typeName = "AcceptEmail";
            break;
        default:
            $fixed = 0;
            $variable = 0;
            $typeName = "";
    }
    return array( $fixed, $variable, $typeName );
}

}


