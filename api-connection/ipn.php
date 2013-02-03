<?php

/**
 * PAYPAL IPN Gateway
 *
 * @author Rob Brown
 * @copyright Brownweb
 * @link http://www.www.brownweb.com.au
 * @license GPL (http://www.gnu.org/licenses/gpl.html)
 */
error_reporting(E_ALL);
ini_set('log_errors', true);
ini_set('error_log', dirname(__FILE__) . '/log/ipn_errors.log');

require_once('lib/functions.php');

$paypalurl = (zpanelx::getConfig('test')) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';

// STEP 1: Read POST data from PAYPAL

$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
    $keyval = explode('=', $keyval);
    if (count($keyval) == 2)
        $myPost[$keyval[0]] = urldecode($keyval[1]);
}
// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';
if (function_exists('get_magic_quotes_gpc')) {
    $get_magic_quotes_exists = true;
}
foreach ($myPost as $key => $value) {
    if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
        $value = urlencode(stripslashes($value));
    } else {
        $value = urlencode($value);
    }
    $req .= "&$key=$value";
}

// STEP 2: Post IPN data back to paypal to validate

$ch = curl_init($paypalurl);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

// In wamp like environments that do not come bundled with root authority certificates,
// please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set the directory path 
// of the certificate as shown below.
// curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
if (!($res = curl_exec($ch))) {
    curl_close($ch);
    exit;
}
curl_close($ch);

// STEP 3: Inspect IPN validation result and act accordingly

if (strcmp($res, "VERIFIED") == 0) {

    $data = "<settings><setting>system.test</setting><setting>payment.email_paypal</setting><setting>payment.cs</setting><setting>payment.email_error</setting></settings>";
    $setting = zpanelx::api("billing", "setting", $data);
    $setting = $setting['settings'];

    $item_name = $_POST['item_name'];
    $ipninvoice = $_POST['invoice'];
    $payment_status = $_POST['payment_status'];
    $payment_amount = $_POST['mc_gross'];
    $payment_currency = $_POST['mc_currency'];
    $txn_id = $_POST['txn_id'];
    $receiver_email = $_POST['receiver_email'];
    $business = $_POST['business'];
    $payer_email = $_POST['payer_email'];

    if ($business != $setting['payment.email_paypal']) {
        zpanelx::error("INVALID PAYMENT: A wrong paypal email have been used: " . $business . " and invoice id: " . $ipninvoice);
    }
    if ($payment_currency != $setting['payment.cs']) {
        zpanelx::error("INVALID PAYMENT: Paypal returned a wrong currency(" . $payment_currency . ") relative to the settings. Invice id: " . $ipninvoice);
    }

    //Check if the invoice id exits or have been paid
    $data = "<token>" . $ipninvoice . "</token>";
    $invoice = zpanelx::api("billing", "Invoice", $data);

    if ($invoice['code'] == "0") {
        zpanelx::error("Invoice id was not found");
    } elseif ($invoice['code'] == "1") {
        $inv_user = $invoice['invoice']['user'];
        $inv_desc = $invoice['invoice']['desc'];
        $inv_amount = $invoice['invoice']['amount'];
        $inv_id = $invoice['invoice']['id'];
        $inv_status = $invoice['invoice']['status'];
    } else {
        zpanelx::error("Invoice data could not be loaded");
    }

    if (!$inv_user) {
        //Forcing to show the error
        zpanelx::error("Invoice id was not found in the system");
    } elseif ($inv_status == "1") {
        //FOrcing to show the error
        zpanelx::error("This invoice has already been paid.");
    }

    //Do the user have paid the same as we want!?
    //TODO: Add something with tax
    if ($inv_amount != $_POST['mc_gross']) {
        zpanelx::error("INVALID PAYMENT: " . $ipninvoice . " (invoice number) - " . $payment_amount . " (payment received) - " . $inv_amount . " (invoice amount)");
    }

    $data = "<method>Paypal</method><user_id>" . $inv_user . "</user_id><txn_id>" . $txn_id . "</txn_id><token>" . $ipninvoice . "</token>";


    $invoice = zpanelx::api("billing", "Payment", $data);

    switch ($invoice['code']) {
        case "1":
            //Really going to do nothing!
            break;
        case "2":
            zpanelx::error("PAYMENT ERROR: Could not create invoice");
            break;
        case "3":
            zpanelx::error("PAYMENT ERROR: Could not select inv_desc");
            break;
        case "4":
            zpanelx::error("PAYMENT ERROR: Could not activate user");
            break;
        case "5":
            zpanelx::error("PAYMENT ERROR: Could not add to x_rb_billing");
            break;
    }
} else if (strcmp($res, "INVALID") == 0) {
    // log for manual investigation
    zpanelx::sendmail($setting['payment.email_error'], 'Invalid IPN', 'error ' . $ipninvoice);
}
?>