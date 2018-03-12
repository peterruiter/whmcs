<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include(dirname(__DIR__) . "/../configuration.php");
if (!function_exists("mollie_config")) {
    function mollie_config()
    {
        global $_LANG;
        include_once("Mollie/mollie_settings.php");
        list($SystemURL, $CompanyName) = whmcs_config_settings();
        $mConfig = array("FriendlyName" => array("Type" => "System", "Value" => "Mollie Custom Module"),
            "mdescription" => array("FriendlyName" => "Transaction description", "Type" => "text", "Description" => "If you leave this blank, your customers will see: " . $CompanyName . " - " . $_LANG["invoicenumber"] . "{invoiceID}", "Size" => 50),
            "mlivekey" => array("FriendlyName" => "Mollie Live API Key", "Type" => "text", "Size" => 50),
            "mtestkey" => array("FriendlyName" => "Mollie Test API Key", "Type" => "text", "Size" => 50),
            "mtestmode" => array("FriendlyName" => "Mollie Test Mode", "Description" => "Tick this to test the module.", "Type" => "yesno"),
            "mtransactionfee" => array("FriendlyName" => "Customers transaction fee", "Description" => "Let your customers pay the transaction fee, tick to activate. Activating this will add a extra line to your invoice with the transaction fee.<br><small>* The transaction fee is provided by <a href=\"https://www.mollie.com\" target=\"_blank\">Mollie BV</a> and cannot be changed.</small>", "Type" => "yesno"));
        return $mConfig;
    }

}

if (!function_exists("mollie_link")) {
    function mollie_link($params)
    {
        global $_LANG;
        global $db_host;
        global $db_username;
        global $db_password;
        global $db_name;
        include_once("Mollie/mollie_settings.php");
        list($mlivekey, $mtestkey, $mtestmode, $mtaxsetting) = mollie_gateway_settings();

            $APIKey = ($params["mtestmode"] == "on" ? $params["mtestkey"] : $params["mlivekey"]);
            $mollie = new Mollie_API_Client();
            $mollie->setApiKey($APIKey);
            try {
                $form_html = "<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js\"></script>\r\n\t\t\t\t<form method=\"post\" id=\"mollie_method\" action=\"" . $params["systemurl"] . "/modules/gateways/Mollie/create_invoice.php?id=" . $params["invoiceid"] . "\">\r\n\t\t\t\t\t<input type=\"hidden\" name=\"transid\" value=\"" . $_SESSION["uid"] . "\">\r\n\t\t\t\t\t<input type=\"hidden\" name=\"invoiceid\" value=\"" . $params["invoiceid"] . "\">\r\n\t\t\t\t\t<select name=\"method\" id=\"method\">\r\n\t\t\t\t\t\t<option value=\"0\">" . $_LANG["paymentmethod"] . "</option>";
                foreach ($mollie->methods->all() as $method) {
                    $amount = $params["amount"];
                    $minimum = $method->amount->minimum;
                    $maximum = $method->amount->maximum;
                    if ($minimum < $amount && $amount < $maximum) {
                        if (isset($_GET["method"]) && $_GET["method"] == $method->id) {
                            $form_html .= "<option value=\"" . htmlspecialchars($method->id) . "\" selected=\"selected\">" . htmlspecialchars($method->description) . "</option>" . "\n\t\t\t\t\t\t\t";
                        } else {
                            $form_html .= "<option value=\"" . htmlspecialchars($method->id) . "\">" . htmlspecialchars($method->description) . "</option>" . "\n\t\t\t\t\t\t\t";
                        }

                    }

                }
                $form_html .= "</select>&nbsp;\r\n\t\t\t\t<div style=\"float: right\" id=\"paynow\"></div>\r\n\t\t\t\t</form>";
                if ($params["mtransactionfee"] != "on") {
                    $form_html .= "
                        <script type=\"text/javascript\">
                            $(document).ready(function() {
                                $('#method').change(function() {
                                    if(typeof $('#method').val() !== 'undefined' && $('#method').val() != 0) {
                                        $('#paynow').html('<input type=\"submit\" name=\"submit\" id=\"mollie_payment\" value=\"" . $_LANG["invoicespaynow"] . "\">');
                                    } else {
                                        $('#paynow').html();
                                    }
                                });
                            });
                        </script>";
                }

                if ($params["mtransactionfee"] == "on") {
                    $form_html .= "
                        <script type=\"text/javascript\">
                            $(document).ready(function() {
                                var method_form = $('#mollie_method');
                                if(typeof $('#method').val() !== 'undefined' && $('#method').val() != 0) {
                                    $('#paynow').html('<input type=\"submit\" name=\"submit\" id=\"mollie_payment\" value=\"" . $_LANG["invoicespaynow"] . "\">');
                                } else {
                                    $('#paynow').html();
                                    $('#method').change(function() {
                                        $.ajax({
                                            type: \"POST\",
                                            url: '" . $params["systemurl"] . "/modules/gateways/Mollie/mollie_hooks.php',
                                            data: method_form.serialize(),
                                            success: function(resp) {
                                                if (resp) {
                                                    var obj = JSON.parse(resp);
                                                    var url = window.location.href; url = url.split('&')[0];
                                                    window.location.href = url + '&method=' + obj.method;
                                                }
                                            }
                                        });
                                    });
                                    $('select[name=gateway]').change(function() {
                                        $.ajax({
                                            type: \"POST\",
                                            url: '" . $params["systemurl"] . "/modules/gateways/Mollie/mollie_hooks.php',
                                            data: {
                                                method: 'other',
                                                transid: '" . uniqid(). "',
                                                invoiceid: '" . $params["invoiceid"] . "',
                                            }
                                        });
                                    });
                                }
                            });
                        </script>";
                }
            } catch (Mollie_API_Exception $e) {
                return "Mollie API Key Failed: " . htmlspecialchars($e->getMessage());
            }
            return $form_html;
        
    }

}

