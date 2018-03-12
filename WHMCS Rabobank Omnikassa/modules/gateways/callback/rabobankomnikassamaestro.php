<?php

//*************************************************************************
//*                                                                       *
//* WHMCS Rabobank Omnikassa payment gateway                              *
//* Copyright (c) Connected Concepts All Rights Reserved,                 *
//* Release Date: 19-09-2015                 				  			  *
//* Version 3.0                                                           *
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
require( '../../../init.php' );
$whmcs->load_function( 'gateway' );
$whmcs->load_function( 'invoice' );

$gatewaymodule = "rabobankomnikassamaestro"; # Enter your gateway module name here
$GATEWAY = getGatewayVariables($gatewaymodule);

if (!$GATEWAY["type"])
    die("Module Not Activated");# Checks gateway module is active before accepting callback

$aRawRaboData = explode('|',$_POST['Data']);
$aRaboData = array();
foreach ($aRawRaboData as $k=>$v) {
    list ($sRaboKey, $sRaboValue) = explode('=', $v);
    $aRaboData[$sRaboKey] = $sRaboValue;
}

$amount = $aRaboData['amount'];
$captureDay = $aRaboData['captureDay'];
$captureMode = $aRaboData['captureMode'];
$currencyCode = $aRaboData['currencyCode'];
$merchantId = $aRaboData['merchantId'];
$orderId = $aRaboData['orderId'];
$transactionDateTime = $aRaboData['transactionDateTime'];
$transactionReference = $aRaboData['transactionReference'];
$keyVersion = $aRaboData['keyVersion'];
$authorisationId = $aRaboData['authorisationId'];
$paymentMeanBrand = $aRaboData['paymentMeanBrand'];
$paymentMeanType = $aRaboData['paymentMeanType'];
$gatewayLanguage = $aRaboData['customerLanguage'];
$responseCode = $aRaboData['responseCode'];
$fee = 0;

$customer_return_url = $GATEWAY['systemurl'] . '/modules/gateways/callback/rabobankomnikassamaestro.php';

// Extra security check - check if the data returned is the same
if ($GATEWAY['testmodus'] == 'on') {
    $keyVersion = '1';
    $merchantId = '002020000000001';
    $GATEWAY['codeerSleutel'] = '002020000000001_KEY1';
}

$returnedHash = $_POST['Seal'];

$controleData = '';
$controleData .= 'currencyCode=' . $currencyCode . '|';
$controleData .= 'merchantId=' . $merchantId . '|';
$controleData .= 'normalReturnUrl=' . $customer_return_url . '|';
$controleData .= 'automaticResponseUrl=' . $customer_return_url . '|';
$controleData .= 'amount=' . $amount . '|';
$controleData .= 'transactionReference=' . $transactionReference . '|';
$controleData .= 'keyVersion=' . $keyVersion . '|';
$controleData .= 'customerLanguage=' . $gatewayLanguage . '|';
$controleData .= 'orderId=' . $orderId;

$controleHash = hash('sha256', $_POST['Data'] . $GATEWAY['codeerSleutel']);
if ($controleHash != $returnedHash) {
    $responseCode = '63';
}

$amount = $amount / 100;

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

$redirectURL = $GATEWAY['systemurl'] . '/viewinvoice.php?id=' . $orderId;

// If this is the visitor that is returning via the callback then just send them along.
// We don't want to check the transaction again since the Rabo allready called this script
if ($_SESSION['rabobankomnikassa_visitor'] == 1) {
    header("location: " . $redirectURL);
    exit;
}
checkCbTransID($transactionReference); # Checks transaction number isn't already in the database and ends processing if it does

if (isset($transactionReference) && $responseCode == '00') {
    addInvoicePayment($orderId, $transactionReference, $amount, $fee, $gatewaymodule); # Apply Payment to Invoice: invoiceid, transactionid, amount paid, fees, modulename
    logTransaction($GATEWAY["name"], $_POST, "Successful - Status: " . $responseCode); # Save to Gateway Log: name, data array, status
} else {
    switch ($responseCode) {
        case '02':
            logTransaction($GATEWAY["name"], $_POST, "Status: " . $responseCode . " - Please call the bank because the authorization limit on the card has been exceeded (neem contact op met de bank; de autorisatielimiet op de kaart is overschreden)");
            break;
        case '03':
            logTransaction($GATEWAY["name"], $_POST, "Status: " . $responseCode . " - Invalid merchant contract (ongeldig contract webwinkel)");
            break;
        case '05':
            logTransaction($GATEWAY["name"], $_POST, "Status: " . $responseCode . " - Do not honor, authorization refused (niet inwilligen, autorisatie geweigerd");
            break;
        case '12':
            logTransaction($GATEWAY["name"], $_POST, "Status: " . $responseCode . " - Invalid transaction, check the parameters sent in the request (ongeldige transactie, controleer de in het verzoek verzonden parameters).");
            break;
        case '14':
            logTransaction($GATEWAY["name"], $_POST, "Status: " . $responseCode . " - Invalid card number or invalid Card Security Code or Card (for MasterCard) or invalid Card Verification Value (for Visa/Maestro) (ongeldig kaartnummer of ongeldige beveiligingscode of kaart (voor MasterCard) of ongeldige waarde kaartverificatie (voor Visa/Maestro))");
            break;
        case '17':
            logTransaction($GATEWAY["name"], $_POST, "Status: " . $responseCode . " - Cancellation of payment by the end user (betaling geannuleerd door eindgebruiker/klant)");
            break;
        case '24':
        default:
            logTransaction($GATEWAY["name"], $_POST, "Status: " . $responseCode . " - Invalid status (ongeldige status).");
            break;
        case '25':
            logTransaction($GATEWAY["name"], $_POST, "Status: " . $responseCode . " - Transaction not found in database (transactie niet gevonden in database)");
            break;
        case '30':
            logTransaction($GATEWAY["name"], $_POST, "Status: " . $responseCode . " - Invalid format (ongeldig formaat)");
            break;
        case '34':
            logTransaction($GATEWAY["name"], $_POST, "Status: " . $responseCode . " - Fraud suspicion (vermoeden van fraude)");
            break;
        case '40':
            logTransaction($GATEWAY["name"], $_POST, "Status: " . $responseCode . " - Operation not allowed to this Merchant (handeling niet toegestaan voor deze webwinkel)");
            break;
        case '60':
            logTransaction($GATEWAY["name"], $_POST, "Status: " . $responseCode . " - Pending transaction (transactie in behandeling)");
            break;
        case '63':
            logTransaction($GATEWAY["name"], $_POST, "Status: " . $responseCode . " - Security breach detected, transaction stopped (beveiligingsprobleem gedetecteerd, transactie gestopt).");
            break;
        case '75':
            logTransaction($GATEWAY["name"], $_POST, "Status: " . $responseCode . " - The number of attempts to enter the card number has been exceeded (three tries exhausted) (het aantal beschikbare pogingen om het card-nummer in te geven is overschreden (max. drie))");
            break;
        case '90':
            logTransaction($GATEWAY["name"], $_POST, "Status: " . $responseCode . " - Acquirer server temporarily unavailable (server acquirer tijdelijk onbeschikbaar)");
            break;
        case '94':
            logTransaction($GATEWAY["name"], $_POST, "Status: " . $responseCode . " - Duplicate transaction (duplicaattransactie). (transactiereferentie al gereserveerd)");
            break;
        case '97':
            logTransaction($GATEWAY["name"], $_POST, "Status: " . $responseCode . " - Request time-out; transaction refused (time-out voor verzoek; transactie geweigerd)");
            break;
        case '99':
            logTransaction($GATEWAY["name"], $_POST, "Status: " . $responseCode . " - Payment page temporarily unavailable (betaalpagina tijdelijk niet beschikbaar)");
            break;
    }
}