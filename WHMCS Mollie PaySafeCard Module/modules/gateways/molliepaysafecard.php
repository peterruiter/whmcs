<?php

function molliepaysafecard_config() {
    $configarray = array(
        "FriendlyName" => array("Type" => "System", "Value" => "PaySafeCard via Mollie"),
        "partnerid" => array("FriendlyName" => "Mollie Partner ID", "Type" => "text", "Size" => "10", "Description" => "Deze kunt u vinden op <a href=\"http://www.mollie.nl/beheer/betaaldiensten/instellingen/\" target=\"_blank\">www.mollie.nl/beheer/betaaldiensten/instellingen</a>"),
        "profilekey" => array("FriendlyName" => "Mollie Profile Key", "Type" => "text", "Size" => "10", "Description" => "Deze kunt u vinden op <a href=\"https://www.mollie.nl/beheer/betaaldiensten/profielen/\" target=\"_blank\">www.mollie.nl/beheer/betaaldiensten/profielen</a>"),
        "feeFixed" => array("FriendlyName" => "Transactiekosten (&euro;)", "Type" => "text", "Size" => "15", "Description" => "Opslag in &euro;. <i>Bijvoorbeeld: 4.50</i>",),       
    );
    return $configarray;
}

function molliepaysafecard_link($params) {

    # Gateway Specific Variables
    $gatewaypartnerid = $params['partnerid'];
    $gatewayprofilekey = $params['profilekey'];
    $gatewaydescription = $params['description'];
    $return_url = $params['returnurl']; // URL waarnaar de consument teruggestuurd wordt na de betaling
    $report_url = $params['systemurl'] . '/modules/gateways/callback/molliepaysafecard.php?invoiceid=' . urlencode($params['invoiceid']); // URL die Mollie aanvraagt (op de achtergrond) na de betaling om de status naar op te sturen
    # Invoice Variables
    $invoiceid = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount']; # Format: ##.##
    $currency = $params['currency']; # Currency Code
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
    $currency = $params['currency'];

    # Enter your code submit to the gateway...

    if (!in_array('ssl', stream_get_transports())) {
        $code = "<h1>Foutmelding</h1>";
        $code .= "<p>Uw PHP installatie heeft geen SSL ondersteuning. SSL is nodig voor de communicatie met de Mollie PaySafeCard API.</p>";
        return $code;
    }


	// Need to set a session var here to make sure that the visitor gets redirected in the callback
	$_SESSION['molliepaysafecard_visitor'] = 1;

	// Everything is good... 
	$feeFixed = (float) $params['feeFixed'];
	if ($feeFixed != '' && $feeFixed != 0.00) {
		if ($feeFixed > 0) {
			$amount = $amount + $feeFixed;
		} else {
			$amount = $amount - $feeFixed;
		}
	}

	$amount = number_format($amount, 2, '.', '');
	$amount = $amount * 100;    // Het af te rekenen bedrag in centen (!!!)

	$paysafecard = new PaySafeCard_Payment($gatewaypartnerid);

	if (isset($_POST['PaySafeCardPayment']) && !empty($_POST['PaySafeCardPayment'])) {
		if ($paysafecard->createPayment($amount, $gatewayprofilekey, $invoiceid, $return_url, $report_url)) {
			header("Location: " . $paysafecard->getPaymentURL());
			exit;
		} else {
			$code = '<p>De betaling kon niet aangemaakt worden.</p>';
			$code .= '<p><strong>Foutmelding:</strong> ' . $paysafecard->getErrorMessage() . '</p>';
			return $code;
		}
	}

	$code = '<form method="post"><input type="submit" name="PaySafeCardPayment" value="Betaal via PaySafeCard" /></form>';
	return $code;
      
}

/**
 * PaySafeCard Payment class written by Mollie
 *
 * @author Mollie BV <info@mollie.nl>
 * @copyright Copyright (c) November, 2011
 * @version 1.00
 */
class PaySafeCard_Payment {

    const MIN_TRANS_AMOUNT = 100;

    protected $partner_id = null;
    protected $profile_key = null;
    protected $customer_ref = null;
    protected $amount = 0;
    protected $return_url = null;
    protected $report_url = null;
    protected $payment_url = null;
    protected $transaction_id = null;
    protected $paid_status = false;
    protected $error_message = '';
    protected $error_code = 0;
    protected $api_host = 'ssl://secure.mollie.nl';
    protected $api_port = 443;

    public function __construct($partner_id, $api_host = 'ssl://secure.mollie.nl', $api_port = 443) {
        $this->partner_id = $partner_id;
        $this->api_host = $api_host;
        $this->api_port = $api_port;
    }

    // Zet een betaling klaar bij de bank en maak de betalings URL beschikbaar
    public function createPayment($amount, $profile_key, $customer_ref, $return_url, $report_url) {
        if (!$this->setAmount($amount) or
                !$this->setProfileKey($profile_key) or
                !$this->setCustomerRef($customer_ref) or
                !$this->setReturnUrl($return_url) or
                !$this->setReportUrl($report_url)) {
            $this->error_message = "De opgegeven betalings gegevens zijn onjuist of incompleet.";
            return false;
        }

        $query_variables = array(
            'partnerid' => $this->getPartnerId(),
            'amount' => $this->getAmount(),
            'profile_key' => $this->getProfileKey(),
            'customer_ref' => $this->getCustomerRef(),
            'reporturl' => $this->getReportURL(),
            'returnurl' => $this->getReturnURL(),
        );

        $create_xml = $this->_sendRequest(
                $this->api_host, $this->api_port, '/xml/paysafecard/prepare/', http_build_query($query_variables, '', '&')
        );

        if (empty($create_xml)) {
            return false;
        }

        $create_object = $this->_XMLtoObject($create_xml);
        if (!$create_object or $this->_XMLisError($create_object)) {
            return false;
        }

        $this->transaction_id = (string) $create_object->order->transaction_id;
        $this->payment_url = (string) $create_object->order->url;

        return true;
    }

    // Kijk of er daadwerkelijk betaald is
    public function checkPayment($transaction_id) {
        if (!$this->setTransactionId($transaction_id)) {
            $this->error_message = "Er is een onjuist transactie ID opgegeven";
            return false;
        }

        $query_variables = array(
            'partnerid' => $this->partner_id,
            'transaction_id' => $this->getTransactionId(),
        );

        $check_xml = $this->_sendRequest(
                $this->api_host, $this->api_port, '/xml/paysafecard/check-status/', http_build_query($query_variables, '', '&')
        );

        if (empty($check_xml))
            return false;

        $check_object = $this->_XMLtoObject($check_xml);

        if (!$check_object or $this->_XMLisError($check_object)) {
            return false;
        }

        $this->paid_status = (bool) ($check_object->order->paid == 'true');
        $this->status = (string) $check_object->order->status;
        $this->amount = (int) $check_object->order->amount;
        return true;
    }

    /*
      PROTECTED FUNCTIONS
     */

    protected function _sendRequest($host, $port, $path, $data) {
        if (function_exists('curl_init')) {
            return $this->_sendRequestCurl($host, $port, $path, $data);
        } else {
            return $this->_sendRequestFsock($host, $port, $path, $data);
        }
    }

    protected function _sendRequestFsock($host, $port, $path, $data) {
        $hostname = str_replace('ssl://', '', $host);
        $fp = @fsockopen($host, $port, $errno, $errstr);
        $buf = '';

        if (!$fp) {
            $this->error_message = 'Kon geen verbinding maken met server: ' . $errstr;
            $this->error_code = 0;

            return false;
        }

        @fputs($fp, "POST $path HTTP/1.0\n");
        @fputs($fp, "Host: $hostname\n");
        @fputs($fp, "Content-type: application/x-www-form-urlencoded\n");
        @fputs($fp, "Content-length: " . strlen($data) . "\n");
        @fputs($fp, "Connection: close\n\n");
        @fputs($fp, $data);

        while (!feof($fp)) {
            $buf .= fgets($fp, 128);
        }

        fclose($fp);

        if (empty($buf)) {
            $this->error_message = 'Zero-sized reply';
            return false;
        } else {
            list($headers, $body) = preg_split("/(\r?\n){2}/", $buf, 2);
        }

        return $body;
    }

    protected function _sendRequestCurl($host, $port, $path, $data) {
        $host = str_replace('ssl://', 'https://', $host);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $host . $path . '?' . $data);
        curl_setopt($ch, CURLOPT_PORT, $port);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $body = curl_exec($ch);

        curl_close($ch);

        return $body;
    }

    protected function _XMLtoObject($xml) {
        try {
            $xml_object = new SimpleXMLElement($xml);
            if ($xml_object == false) {
                $this->error_message = "Kon XML resultaat niet verwerken";
                return false;
            }
        } catch (Exception $e) {
            return false;
        }

        return $xml_object;
    }

    protected function _XMLisError($xml) {
        if (isset($xml->item)) {
            $attributes = $xml->item->attributes();
            if ($attributes['type'] == 'error') {
                $this->error_message = (string) $xml->item->message;
                $this->error_code = (string) $xml->item->errorcode;

                return true;
            }
        }

        return false;
    }

    /* Getters en setters */

    public function setProfileKey($profile_key) {
        if (is_null($profile_key))
            return false;

        return ($this->profile_key = $profile_key);
    }

    public function getProfileKey() {
        return $this->profile_key;
    }

    public function setCustomerRef($customer_ref) {
        if (is_null($customer_ref))
            return false;

        return ($this->customer_ref = $customer_ref);
    }

    public function getCustomerRef() {
        return $this->customer_ref;
    }

    public function setPartnerId($partner_id) {
        if (!is_numeric($partner_id)) {
            return false;
        }

        return ($this->partner_id = $partner_id);
    }

    public function getPartnerId() {
        return $this->partner_id;
    }

    public function setAmount($amount) {
        if (!preg_match('~^[0-9]+$~', $amount)) {
            return false;
        }

        if (self::MIN_TRANS_AMOUNT > $amount) {
            return false;
        }

        return ($this->amount = $amount);
    }

    public function getAmount() {
        return $this->amount;
    }

    public function setReturnURL($return_url) {
        if (!preg_match('|(\w+)://([^/:]+)(:\d+)?(.*)|', $return_url))
            return false;

        return ($this->return_url = $return_url);
    }

    public function getReturnURL() {
        return $this->return_url;
    }

    public function setReportURL($report_url) {
        if (!preg_match('|(\w+)://([^/:]+)(:\d+)?(.*)|', $report_url)) {
            return false;
        }

        return ($this->report_url = $report_url);
    }

    public function getReportURL() {
        return $this->report_url;
    }

    public function setTransactionId($transaction_id) {
        if (empty($transaction_id))
            return false;

        return ($this->transaction_id = $transaction_id);
    }

    public function getTransactionId() {
        return $this->transaction_id;
    }

    public function getPaymentURL() {
        return $this->payment_url;
    }

    public function getPaidStatus() {
        return $this->paid_status;
    }

    public function getBankStatus() {
        return $this->status;
    }

    public function getErrorMessage() {
        return $this->error_message;
    }

    public function getErrorCode() {
        return $this->error_code;
    }

}


?>