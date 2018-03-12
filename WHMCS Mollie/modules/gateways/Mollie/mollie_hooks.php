<?php 
header("Access-Control-Allow-Origin: *");
if( $_SERVER["REQUEST_METHOD"] == "POST" ) 
{
    global $_LANG;
    global $db_host;
    global $db_username;
    global $db_password;
    global $db_name;
    include_once("mollie_settings.php");
    try
    {
        $dbtype = "MollieTransactionFee";
        $link = mysqli_connect($db_host, $db_username, $db_password, $db_name);
        $sesuid = mysqli_real_escape_string($link, $_POST["transid"]);
        list($fixed, $variable, $typeName) = mollie_transaction_fee($_POST["method"]);
        list($license, $mlivekey, $mdescription, $mtestkey, $mtestmode, $mtaxsetting) = mollie_gateway_settings();
        $invID = mysqli_real_escape_string($link, $_POST["invoiceid"]);
        $query = mysqli_query($link, "SELECT * FROM tblinvoices WHERE id = '" . $invID . "'");
        $result = mysqli_fetch_assoc($query);
        $btwtax = $result["taxrate"];
        if( isset($_POST["method"]) && $_POST["method"] == "other" ) 
        {
            $invoiceid = mysqli_real_escape_string($link, $_POST["invoiceid"]);
            $items = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM tblinvoiceitems WHERE invoiceid = '" . $invoiceid . "' AND type = '" . $dbtype . "'"));
            $invoice = mysqli_fetch_assoc(mysqli_query($link, "SELECT subtotal FROM tblinvoices WHERE id = '" . $invoiceid . "'"));
            $subtotal = $invoice["subtotal"] - $items["amount"];
            $cleantax = ($subtotal * $btwtax) / 100;
            $cleantotal = $subtotal + $cleantax;
            $vat_query = ($btwtax != "0.00" ? "tax = '" . $cleantax . "', total = '" . $cleantotal . "'" : "total = '" . $subtotal . "'");
            mysqli_query($link, "UPDATE tblinvoices SET subtotal = '" . $subtotal . "', " . $vat_query . " WHERE id = '" . $invoiceid . "'");
            mysqli_query($link, "DELETE FROM tblinvoiceitems WHERE invoiceid='" . $invoiceid . "' AND type='" . $dbtype . "' AND userid = '" . $sesuid . "'");
        }
        else
        {
            $total = $result["total"];
            $showVar = ($variable != 0 ? " + " . $variable . "%" : NULL);
            $trans = (isset($typeName) ? "Transaction fee (" . $typeName . " - &euro;" . $fixed . $showVar . ")" : NULL);
            $percentage = ($total * $variable) / 100;
            $total_fee = $fixed + $percentage;
            $fixed_total = $total + $total_fee;
            $invoiceid = mysqli_real_escape_string($link, $_POST["invoiceid"]);
            $checkinv = mysqli_query($link, "SELECT * FROM tblinvoiceitems WHERE invoiceid = '" . $invoiceid . "' AND type = '" . $dbtype . "'");
            $invoice = mysqli_fetch_assoc(mysqli_query($link, "SELECT subtotal FROM tblinvoices WHERE id = '" . $invoiceid . "'"));
            if( empty($typeName) ) 
            {
                $items = mysqli_fetch_assoc($checkinv);
                $invoiceID = (int) $items["invoiceid"];
                $subtotal = $invoice["subtotal"] - $items["amount"];
                $cleantax = ($subtotal * $btwtax) / 100;
                $cleantotal = $subtotal + $cleantax;
                $taxing = ($btwtax != "0.00" ? "tax = '" . $cleantax . "', total = '" . $cleantotal . "'" : "total = '" . $subtotal . "'");
                mysqli_query($link, "UPDATE tblinvoices SET subtotal = '" . $subtotal . "', " . $taxing . " WHERE id = '" . $invoiceID . "'");
                mysqli_query($link, "DELETE FROM tblinvoiceitems WHERE invoiceid='" . $invoiceid . "' AND type='" . $dbtype . "'");
                echo json_encode(array( "request" => "empty" ));
            }
            else
            {
                if( 0 < mysqli_num_rows($checkinv) ) 
                {
                    $items = mysqli_fetch_assoc($checkinv);
                    $invID = (int) $items["invoiceid"];
                    $empty = $invoice["subtotal"] - $items["amount"];
                    $taxdd = ($empty * $btwtax) / 100;
                    $totll = $empty + $taxdd;
                    $taxQ = ($btwtax != "0.00" ? "tax = '" . $taxdd . "', total = '" . $totll . "'" : "total = '" . $empty . "'");
                    $updQ = mysqli_query($link, "UPDATE tblinvoices SET subtotal = '" . $empty . "', " . $taxQ . " WHERE id = '" . $invID . "'");
                    if( $query = $updQ ) 
                    {
                        $invoice_tax = ($btwtax != "0.00" ? "taxed = '1'" : "taxed = '0'");
                        $invoice_vat = "UPDATE tblinvoiceitems SET userid = '" . $sesuid . "', description = '" . $trans . "', " . $invoice_tax . ", amount = '" . $total_fee . "' WHERE invoiceid = '" . $invID . "' AND type = '" . $dbtype . "'";
                        if( $result = mysqli_query($link, $invoice_vat) ) 
                        {
                            $subt = ($invoice["subtotal"] + $total_fee) - $items["amount"];
                            $taxet = ($subt * $btwtax) / 100;
                            $total = $subt + $taxet;
                            $vats = ($btwtax != "0.00" ? "tax = '" . $taxet . "', total = '" . $total . "'" : "total = '" . $subt . "'");
                            $update = mysqli_query($link, "UPDATE tblinvoices SET subtotal = '" . $subt . "', " . $vats . " WHERE id = '" . $invID . "'");
                            if( $update ) 
                            {
                                echo json_encode(array( "method" => $_POST["method"], "request" => "update" ));
                            }
                            else
                            {
                                $_LANG["thereisaproblem"];
                            }

                        }
                        else
                        {
                            echo $_LANG["thereisaproblem"];
                        }

                    }

                }
                else
                {
                    $taxing = ($btwtax != "0.00" ? "taxed = '1', " : "taxed = '0', ");
                    $query = "INSERT INTO tblinvoiceitems SET invoiceid = '" . $invoiceid . "', userid = '" . $sesuid . "', " . $taxing . " type = '" . $dbtype . "', description = '" . $trans . "', amount = '" . $total_fee . "'";
                    if( $result = mysqli_query($link, $query) ) 
                    {
                        $subtotal = $invoice["subtotal"] + $total_fee;
                        $taxed = ($subtotal * $btwtax) / 100;
                        $total = $subtotal + $taxed;
                        $updater = ($btwtax != "0.00" ? "tax = '" . $taxed . "', total = '" . $total . "'" : "total = '" . $subtotal . "'");
                        $update = mysqli_query($link, "UPDATE tblinvoices SET subtotal = " . $subtotal . ", " . $updater . " WHERE id = '" . $invoiceid . "'");
                        if( $update ) 
                        {
                            echo json_encode(array( "method" => $_POST["method"], "request" => "insert" ));
                        }
                        else
                        {
                            echo json_encode(array( "error" => "Could not run updating query on invoices.", "code" => "0x0002" ));
                        }

                    }
                    else
                    {
                        echo json_encode(array( "error" => "Could not run inserting query on invoice items.", "code" => "0x0001" ));
                    }

                }

            }

            mysqli_close($link);
        }

    }
    catch( Exception $e ) 
    {
        echo $_LANG["thereisaproblem"] . ": " . $e->getMessage();
    }
}
else
{
    exit( "This file cannot be accessed directly" );
}


