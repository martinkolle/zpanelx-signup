<?php
/*
ipn.php - example code used for the tutorial:

PayPal IPN with PHP
How To Implement an Instant Payment Notification listener script in PHP
http://www.micahcarrick.com/paypal-ipn-with-php.html

(c) 2011 - Micah Carrick
*/

// tell PHP to log errors to ipn_errors.log in this directory
ini_set('log_errors', true);
ini_set('error_log', dirname(__FILE__).'/ipn_errors.log');

// intantiate the IPN listener
include('ipnlistener.php');
$listener = new IpnListener();

// tell the IPN listener to use the PayPal test sandbox
$listener->use_sandbox = true;


//variables
$seller_email = 'martin.kollerup@gmail.com';
$seller_paypal_email = 'martin.kollerup@gmail.com';

// try to process the IPN POST
try {
    $listener->requirePostMethod();
    $verified = $listener->processIpn();
} catch (Exception $e) {
    error_log($e->getMessage());
    exit(0);
}

if ($verified) {

    $errmsg = '';   // stores errors from fraud checks
    
    // 1. Make sure the payment status is "Completed" 
    if ($_POST['payment_status'] != 'Completed') { 
        // simply ignore any IPN that is not completed
        exit(0); 
    }

    // 2. Make sure seller email matches your primary account email.
    if ($_POST['receiver_email'] != $seller_paypal_email) {
        $errmsg .= "'receiver_email' does not match: ";
        $errmsg .= $_POST['receiver_email']."\n";
    }
    /*
        we can 
    
    */
    
    // 4. Make sure the currency code matches
    if ($_POST['mc_currency'] != 'DKK') {
        $errmsg .= "'mc_currency' does not match: ";
        $errmsg .= $_POST['mc_currency']."\n";
    }

    // 5. Ensure the transaction is not a duplicate.

mysql_connect("kmweb.dk","billing2","my4epamuz") or die(0);
mysql_select_db('zadmin_billing') or die(0);

    $txn_id = mysql_real_escape_string($_POST['txn_id']);
    $sql = "SELECT COUNT(*) FROM user WHERE txn_id = '$txn_id'";
    $r = mysql_query($sql);
    
    if (!$r) {
        error_log(mysql_error());
        exit(0);
    }
    
    $exists = mysql_result($r, 0);
    mysql_free_result($r);
    
    if ($exists) {
        $errmsg .= "'txn_id' has already been processed: ".$_POST['txn_id']."\n";
    }
    
    if (!empty($errmsg)) {
    
        // manually investigate errors from the fraud checking
        $body = "IPN failed fraud checks: \n$errmsg\n\n";
        $body .= $listener->getTextReport();
        mail($seller_email, 'IPN Fraud Warning', $body);
       
        $to = filter_var($_POST['payer_email'], FILTER_SANITIZE_EMAIL);
        $body = "Vi har modtaget din ordre, men der er blevet fundet en fejl i forhold til vores oplysninger og dem som vi har modtaget fra paypal. <br />
                Din bestilling vil derfor blive manuelt undersøgt og du vil blive oprettet indenfor 24 timer. ";
        mail($to, "Thank you for your order", "Download URL: ...");

        
    } else {
    
        // add this order to a table of completed orders
        $payer_email = mysql_real_escape_string($_POST['payer_email']);
        $mc_gross = mysql_real_escape_string($_POST['mc_gross']);
        $payment_id = '0'/*get unique id*/;

        $sql = "UPDATE user SET txn_id = '$txn_id', mc_gross = '$mc_gross' WHERE id = '5' ";
        
        if (!mysql_query($sql)) {
            error_log(mysql_error());
            exit(0);
        }
        
        // send user an email with a link to their digital download
        $to = filter_var($_POST['payer_email'], FILTER_SANITIZE_EMAIL);
        $subject = "Your digital download is ready";
        mail($to, "Thank you for your order", "Download URL: ...");
    }
    
} else {
    // manually investigate the invalid IPN
    mail($seller_email, 'Invalid IPN', $listener->getTextReport());
}

mail($seller_email, 'post', print_r($_POST));

?>
