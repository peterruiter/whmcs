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

define("SECURE_ING_IDEAL_PATH", getcwd()."/modules/gateways");	

use iDEALConnector\iDEALConnector;
use iDEALConnector\Exceptions\ValidationException;
use iDEALConnector\Exceptions\SecurityException;
use iDEALConnector\Exceptions\SerializationException;
use iDEALConnector\Configuration\DefaultConfiguration;
use iDEALConnector\Exceptions\iDEALException;
use iDEALConnector\Entities\DirectoryResponse;
use iDEALConnector\Entities\Transaction;
use iDEALConnector\Entities\AcquirerTransactionResponse;
        
function ingidealadvanced_config() {
    $configarray = array(
        "FriendlyName" => array("Type" => "System", "Value" => "ING iDEAL advanced"),
        "feePercentage" => array("FriendlyName" => "Transactiekosten (%)", "Type" => "text", "Size" => "15", "Description" => "Opslag in %. <i>Bijvoorbeeld: 3.50</i><br><strong>(Let op: Opslag in % wordt afgerond op 2 decimalen door de bank en is derhalve niet altijd even nauwkeurig bij de verwerking. Gebruik indien u transactiekosten wilt berekenen bij voorkeur altijd een vaste transactiefee)</strong>",),
        "feeFixed" => array("FriendlyName" => "Transactiekosten (&euro;)", "Type" => "text", "Size" => "15", "Description" => "Opslag in &euro;. <i>Bijvoorbeeld: 4.50</i><br><strong>(Let op: Opslag in % wordt eerst berekend en vervolgens pas opslag in &euro;)</strong>",),       
        "invoiceDescription" => array("FriendlyName" => "Factuuromschrijving", "Type" => "text", "Size" => "30", "Description" => "Bijvoorbeeld: <i>Factuur [[invoicenum]]</i><br><strong>(Let op: [[invoiceid]] wordt automatisch vervangen door het respectievelijke factuurid.)</strong><br><strong>(Let op: [[invoicenum]] wordt automatisch vervangen door het respectievelijke factuurnummer.)</strong>",),
    );
    return $configarray;
}

function ingidealadvanced_link($params) {
# Gateway Specific Variables    
            
    # Invoice Variables
    $invoiceid = $params['invoiceid'];
    $description = $params["description"];
	$invoiceDescription = $params["invoiceDescription"];
    $amount = $params['amount']; # Format: ##.##    
    
	$invoiceNum = '';
	$query = "SELECT tblinvoices.id, tblinvoices.invoicenum FROM tblinvoices WHERE tblinvoices.id = '".$invoiceid."';";
	$result=mysql_query($query);

	if (mysql_num_rows($result) != 0) {			
		while ($data = mysql_fetch_array($result)) {
			$invoiceNum = $data['invoicenum'];
		}
	} else {
		$invoiceNum = $invoiceid;
	}
	
    # Client Variables
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    # System Variables
    $companyname = $params['companyname'];
    $systemurl = $params['systemurl'];  
	
    $feePercentage = (float) $params['feePercentage'];
    $feeFixed = (float) $params['feeFixed'];
    if ($feePercentage != '' && $feePercentage != 0.00) {
        if ($feePercentage > 0) {
            $amount = ((($amount / 100) * abs($feePercentage)) + $amount);
        } else {
            $amount = ($amount - (($amount / 100) * abs($feePercentage)));
        }
    }
    if ($feeFixed != '' && $feeFixed != 0.00) {
        if ($feeFixed > 0) {
            $amount = $amount + $feeFixed;
        } else {
            $amount = $amount - $feeFixed;
        }
    }

    $amount = number_format($amount, 2, '.', '');           
    $transref = $invoiceid . rand(0, 100000);    

    $customer_return_url = $systemurl . '/modules/gateways/callback/ingidealadvanced.php';
   
    if (!in_array('ssl', stream_get_transports())) {
        echo "<h1>Foutmelding</h1>";
        echo "<p>Uw PHP installatie heeft geen SSL ondersteuning. SSL is nodig voor de communicatie met de ING iDEAL advanced gateway.</p>";
        exit;
    }


	
    ///////////// Start IDEAL Code	
    require_once("ingidealadvanced/iDEALConnector.php");
    
    $issuerList = "";
    $errorCode = 0;
    $errorMsg = "";
    $consumerMessage = "";
    $config = new DefaultConfiguration(SECURE_ING_IDEAL_PATH."/ingidealadvanced/config.conf");

    $merchantId = $config->getMerchantID();
    $subId = $config->getSubID();
    $acquirerUrl = $config->getAcquirerDirectoryURL(); 

    $responseDatetime = '';    
    $iDEALConnector = iDEALConnector::getDefaultInstance(SECURE_ING_IDEAL_PATH."/ingidealadvanced/config.conf");
    $response = $iDEALConnector->getIssuers();

    if (!isset($_POST["submitted"])) {
        if ($response->IsResponseError()) {
            $errorCode = $response->getErrorCode();
            $errorMsg = $response->getErrorMessage();
            $consumerMessage = $response->getConsumerMessage();
            echo "$errorCode - $errorMsg";
        } else {
            foreach ($response->getCountries() as $country) { 
				$issuerList .= "<optgroup label=\"" . $country->getCountryNames() . "\">";

				foreach ($country->getIssuers() as $issuer) {
					$issuerList .= "<option value=\"" . $issuer->getId() . "\">"
						. $issuer->getName() . "</option>";
				}
				$issuerList .= "</optgroup>";

				$acquirerID = $response->getAcquirerID();
				$responseDatetime = $response->getDirectoryDate();
			}
        }
    } else {
        $actionType = '';
        if (isset($_POST["submitted"])) {
                $actionType = $_POST["submitted"];
        } 

        $issuerId = '';
        if (isset($_POST["IssuerIDs"])) {
                $issuerId = $_POST["IssuerIDs"];
        } 
        
        // Set specific vars for ing transaction request
        $purchaseId = $invoiceid;
		
		if ($invoiceDescription == '') {
			$description = $invoiceid;
		} else {
			$description = $invoiceDescription;
			$description = str_replace("[[invoiceid]]", $invoiceid, $description);
			$description = str_replace("[[invoicenum]]", $invoiceNum, $description);
		}               		

		date_default_timezone_set('UTC');

		require_once(SECURE_ING_IDEAL_PATH."/ingidealadvanced/iDEALConnector.php");
		$config = new DefaultConfiguration(SECURE_ING_IDEAL_PATH."/ingidealadvanced/config.conf");
		
		$entranceCode = $invoiceid;
        $merchantReturnUrl = $customer_return_url;
        $expirationPeriod = $config->getExpirationPeriod();
        $acquirerID = '';
        $issuerAuthenticationURL = '';
        $transactionID = '';

		
        if ($actionType == "Request Transaction") {
            $iDEALConnector = iDEALConnector::getDefaultInstance(SECURE_ING_IDEAL_PATH."/ingidealadvanced/config.conf");
			$response = $iDEALConnector->startTransaction(
				$issuerId,
				new Transaction(
					$amount,
					$description,
					$entranceCode,
					$expirationPeriod,
					$purchaseId,
					'EUR',
					'nl'
				),
				$merchantReturnUrl
			);            
                        
			$acquirerID = $response->getAcquirerID();
			$issuerAuthenticationURL = $response->getIssuerAuthenticationURL();
			$transactionID = $response->getTransactionID();			    
        }
    }
    
    ///////////// End IDEAL Code
    header ("Location: ".$issuerAuthenticationURL);          
        
	
}

?>