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

function rabobankomnikassavisa_config() {
    $configarray = array(
        "FriendlyName" => array("Type" => "System", "Value" => "Rabobank Omnikassa visa"),
        "partnerId" => array("FriendlyName" => "Winkel ID", "Type" => "text", "Size" => "15",),
        "gatewayVersion" => array("FriendlyName" => "Interface versie", "Type" => "text", "Size" => "15", "Description" => "Dit is standaard HP_1.0",),
        "codeerSleutel" => array("FriendlyName" => "Sleutel", "Type" => "text", "Size" => "50",),
        "sleutelVersie" => array("FriendlyName" => "Sleutel versie", "Type" => "text", "Size" => "10",),
        "feePercentage" => array("FriendlyName" => "Transactiekosten (%)", "Type" => "text", "Size" => "15", "Description" => "Opslag in %. <i>Bijvoorbeeld: 3.50</i><br><strong>(Let op: Opslag in % wordt afgerond op 2 decimalen door de bank en is derhalve niet altijd even nauwkeurig bij de verwerking. Gebruik indien u transactiekosten wilt berekenen bij voorkeur altijd een vaste transactiefee)</strong>",),
        "feeFixed" => array("FriendlyName" => "Transactiekosten (&euro;)", "Type" => "text", "Size" => "15", "Description" => "Opslag in &euro;. <i>Bijvoorbeeld: 4.50</i><br><strong>(Let op: Opslag in % wordt eerst berekend en vervolgens pas opslag in &euro;)</strong>",),
        "testmodus" => array("FriendlyName" => "Testmodus", "Type" => "yesno", "Description" => "Aanvinken om in testmodus te zetten",),
        "licenseKey" => array("FriendlyName" => "License Key", "Type" => "text", "Size" => "15", "Description" => "Licentiesleutel voor WHMCS Payment Gateway"),
        "localKey" => array("FriendlyName" => "Local Key", "Type" => "textarea", "Rows" => "10", "Description" => "<strong>Wordt automatisch gegenereerd. Niet aanpassen tenzij door de supportafdeling aangegeven.</strong>"),
    );
    return $configarray;
}

function rabobankomnikassavisa_link($params) {
# Gateway Specific Variables
    $gatewaypartnerId = $params['partnerId'];
    $gatewaycodeersleutel = $params['codeerSleutel'];
    $gatewayversie = $params['gatewayVersion'];
    $keyVersion = $params['sleutelVersie'];
    $connectorURL = 'https://payment-webinit.omnikassa.rabobank.nl/paymentServlet';
    $testmodus = ($params['testmodus'] == 'on') ? '1' : '0';

    # Invoice Variables
    $invoiceid = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount']; # Format: ##.##

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

    $currency = '978'; # Euro
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

    # Enter your code submit to the gateway...    
    $amount = $amount * 100;    // Het af te rekenen bedrag in centen (!!!)
    $transref = $invoiceid . rand(0, 100000);

    $customer_return_url = $systemurl . '/modules/gateways/callback/rabobankomnikassavisa.php';

    if ($testmodus) {
        $connectorURL = 'https://payment-webinit.simu.omnikassa.rabobank.nl/paymentServlet';
        $keyVersion = '1';
        $gatewayversie = 'HP_1.0';
        $gatewaypartnerId = '002020000000001';
        $gatewaycodeersleutel = '002020000000001_KEY1';
        $transref = $invoiceid . rand(0, 100000);
    }

    // Verplichte velden
    $data = '';
    $data .= 'currencyCode=' . $currency . '|';
    $data .= 'merchantId=' . $gatewaypartnerId . '|';
    $data .= 'normalReturnUrl=' . $customer_return_url . '|';
    $data .= 'automaticResponseUrl=' . $customer_return_url . '|';
    $data .= 'amount=' . $amount . '|';
    $data .= 'transactionReference=' . $transref . '|';
    $data .= 'keyVersion=' . $keyVersion . '|';

    // Optionele velden
    $data .= 'orderId=' . $invoiceid;
    $data .= '|paymentMeanBrandList=VISA';

    $hash = hash('sha256', $data . $gatewaycodeersleutel);

    if (!in_array('ssl', stream_get_transports())) {
        echo "<h1>Foutmelding</h1>";
        echo "<p>Uw PHP installatie heeft geen SSL ondersteuning. SSL is nodig voor de communicatie met de Rabobank Omnikassa gateway.</p>";
        exit;
    }

    $licenskeySet = true;
    $licenskeyValid = true;

    if ($licenskeySet && $licenskeyValid) {
        // Need to set a session var here to make sure that the visitor gets redirected in the callback
        $_SESSION['rabobankomnikassa_visitor'] = 1;

        // Everything is good... 
        $code = '<form action="' . $connectorURL . '" method="POST" name="process">
                    <input type="hidden" name="Data" value="' . $data . '" />            
                    <input type="hidden" name="InterfaceVersion" value="' . $gatewayversie . '" />  
                    <input type="hidden" name="Seal" value="' . $hash . '">
                    <input type="submit" name="submit" value="Betalen" />            
                    </form>';
        return $code;
    }
}
