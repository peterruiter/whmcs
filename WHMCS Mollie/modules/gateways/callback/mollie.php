<?php 
include("../../../init.php");
include("../../../includes/functions.php");
include("../../../includes/gatewayfunctions.php");
include("../../../includes/invoicefunctions.php");
include("../Mollie/API/Autoloader.php");
try
{
    $gatewaymodule = "mollie";
    $GATEWAY = getGatewayVariables($gatewaymodule);
    if( !$GATEWAY["type"] ) 
    {
        exit( "Module Not Activated" );
    }

    $APIKey = ($GATEWAY["mtestmode"] == "on" ? $GATEWAY["mtestkey"] : $GATEWAY["mlivekey"]);
    $mollie = new Mollie_API_Client();
    $mollie->setApiKey($APIKey);
    $transid = $_POST["id"];
    $payment = $mollie->payments->get($_POST["id"]);
    $invoiceid = $payment->metadata->order_id;
    $trans_fee = $payment->metadata->total_fee;
    $amount = $payment->amount;
    $invoiceid = checkCbInvoiceID($invoiceid, $GATEWAY["name"]);
    checkCbTransID($transid);
    if( $payment->isPaid() ) 
    {
        addInvoicePayment($invoiceid, $transid, $amount, $trans_fee, $gatewaymodule);
        logTransaction($gatewaymodule, $_POST, "Successful");
    }
    else
    {
        if( $payment->isOpen() ) 
        {
            logTransaction($gatewaymodule, $_POST, "Unsuccessful");
        }

    }

}
catch( Mollie_API_Exception $e ) 
{
    echo $e->getMessage();
}

