<?php 
include_once("mollie_settings.php");
if( $_SERVER["REQUEST_METHOD"] == "POST" ) 
{
    global $_LANG;
    global $db_host;
    global $db_username;
    global $db_password;
    global $db_name;
    $link = mysqli_connect($db_host, $db_username, $db_password, $db_name);
    $invID = mysqli_real_escape_string($link, $_GET["id"]);
    list($description, $mlivekey, $mtestkey, $mtestmode) = mollie_gateway_settings();

	$invoice = mysqli_fetch_assoc(mysqli_query($link, "SELECT `id`,`total`,`invoicenum` FROM tblinvoices WHERE id = '" . $invID . "'"));
	$checkPy = mysqli_query($link, "SELECT amountin FROM tblaccounts WHERE invoiceid = '" . $invID . "'");
	list($SystemURL, $CompanyName) = whmcs_config_settings();
	$description = str_replace("{invoice_id}", $invoice["id"], $description);
	$description = str_replace("{invoice_num}", $invoice["invoicenum"], $description);
	$descri = (empty($description) ? $CompanyName . " - Invoice #" . $invoice["id"] : $description);
	$APIKey = ($mtestmode == "on" ? $mtestkey : $mlivekey);
	$mollie = new Mollie_API_Client();
	$mollie->setApiKey($APIKey);
	try
	{
		list($fixed, $variable, $typeName) = mollie_transaction_fee($_POST["method"]);
		if( 0 < mysqli_num_rows($checkPy) ) 
		{
			$amountin = mysqli_fetch_assoc($checkPy);
			$invoice["total"] = $invoice["total"] - $amountin["amountin"];
		}
		else
		{
			$invoice["total"] = $invoice["total"];
		}

		$total = $invoice["total"];
		$percentage = ($total * $variable) / 100;
		$total_fee = $fixed + $percentage;
		$fixed_total = $total + $total_fee;
		$payment = $mollie->payments->create(array( "amount" => $invoice["total"], "method" => $_POST["method"], "description" => $descri, "redirectUrl" => $SystemURL . "viewinvoice.php?id=" . $invID, "webhookUrl" => $SystemURL . "modules/gateways/callback/mollie.php", "metadata" => array( "order_id" => $invID, "total_fee" => $total_fee ) ));
		header("Location: " . $payment->getPaymentUrl());
	}
	catch( Mollie_API_Exception $e ) 
	{
		echo htmlspecialchars($e->getMessage());
	}
   

}


