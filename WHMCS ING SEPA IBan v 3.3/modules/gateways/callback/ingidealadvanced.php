<?php

//*************************************************************************
//*                                                                       *
//* WHMCS ING iDEAL advanced payment gateway                              *
//* Copyright (c) Connected Concepts All Rights Reserved,                 *
//* Release Date: 25-02-2014                                              *
//* Version 3.3.1                                                         *
//*                                                                       *
//*************************************************************************
//*                                                                       *
//* Email: info@connectedconcepts.nl                                      *
//* Website: http://www.connectedconcepts.nl                              *
//*                                                                       *
//*************************************************************************
//*                                                                       *
//* This software is furnished under a license and may be used and copied *
//* only  in  accordance  with  the  terms  of such  license and with the *
//* inclusion of the above copyright notice.  This software  or any other *
//* copies thereof may not be provided or otherwise made available to any *
//* other person.  No title to and  ownership of the  software is  hereby *
//* transferred.                                                          *
//*************************************************************************
# Required File Includes
include("../../../dbconnect.php");
include("../../../includes/functions.php");
include("../../../includes/gatewayfunctions.php");
include("../../../includes/invoicefunctions.php");

$gatewaymodule = "ingidealadvanced"; # Enter your gateway module name here
$GATEWAY = getGatewayVariables($gatewaymodule);

if (!$GATEWAY["type"]) {
    die("Module Not Activated");# Checks gateway module is active before accepting callback
}

use iDEALConnector\iDEALConnector;
use iDEALConnector\Exceptions\SerializationException;
use iDEALConnector\Configuration\DefaultConfiguration;
use iDEALConnector\Exceptions\SecurityException;
use iDEALConnector\Exceptions\ValidationException;
use iDEALConnector\Exceptions\iDEALException;
use iDEALConnector\Entities\AcquirerStatusResponse;
date_default_timezone_set('UTC');

require_once("../ingidealadvanced/iDEALConnector.php");

$errorCode = 0;
$errorMsg = "";
$consumerMessage = "";
$config = new DefaultConfiguration("../ingidealadvanced/config.conf");

$transactionId = '';
if (isset($_GET["trxid"])) {
	$transactionId = $_GET["trxid"];
} elseif (isset($_POST["transactionId"])) {
	$transactionId = $_POST["transactionId"];
}
$ec = '';
if (isset($_GET["ec"])) {
	$ec = $_GET["ec"];
} elseif (isset($_POST["ec"])) {
	$ec = $_POST["ec"];
}

$acquirerID = '';
$consumerName = '';
$consumerIBAN = '';
$consumerBIC = '';
$amount = '';
$currency = '';
$statusDateTime = '';
$status = '';

if ($transactionId != '') {
    $iDEALConnector = iDEALConnector::getDefaultInstance("../ingidealadvanced/config.conf");
    $response = $iDEALConnector->getTransactionStatus($transactionID);	
    
	$acquirerID = $response->getAcquirerID();
	$consumerName = $response->getConsumerName();
	$consumerIBAN = $response->getConsumerIBAN();
	$consumerBIC = $response->getConsumerBIC();
	$amount = $response->getAmount();
	$currency = $response->getCurrency();
	$statusDateTime = $response->getStatusDateTime();
	$transactionId = $response->getTransactionID();
	$status = $response->getStatus();    
}

$redirectURL = $GATEWAY['systemurl'] . '/viewinvoice.php?id=' . $ec;
if ($status != 1) {
    logTransaction($GATEWAY["name"], $_POST, "Unsuccesfull - Status: " . $status. " || Response: ".$consumerMessage);
    header("Location: ".$redirectURL);
}

$feePercentage = (float) $GATEWAY['feePercentage'];
$feeFixed = (float) $GATEWAY['feeFixed'];
if ($feeFixed != '' && $feeFixed != 0.00) {
    if($feeFixed > 0) {
        $fee = ($fee + $feeFixed);
        $amount = ($amount - $feeFixed);
    } else {
        $fee = ($fee - abs($feeFixed));
        $amount = ($amount + abs($feeFixed));
    }
}
if ($feePercentage != '' && $feePercentage != 0.00) {
    if ($feePercentage > 0) {
        $fee = $fee + ($amount - (($amount / (100 + $feePercentage)) * 100));
        $amount = ($amount / (100 + $feePercentage)) * 100;
    } else {
        $fee = $fee + ($amount - (($amount / (100 - abs($feePercentage))) * 100));
        $amount = ($amount / (100 - abs($feePercentage))) * 100;
    }
}

$amount = number_format($amount, 2, '.', '');
$fee = number_format($fee, 2, '.', '');

checkCbTransID($transactionId); # Checks transaction number isn't already in the database and ends processing if it does
if (isset($transactionId) && $status == 1) {
    addInvoicePayment($ec, $transactionId, $amount, $fee, $gatewaymodule); # Apply Payment to Invoice: invoiceid, transactionid, amount paid, fees, modulename
    logTransaction($GATEWAY["name"], $_POST, "Successful - Status: " . $status); 
} else {
    logTransaction($GATEWAY["name"], $_POST, "Unsuccesfull - Status: " . $status);            
}

header("location: " . $redirectURL);
exit;
?>