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
$my_email   = "martin.kollerup@gmail.com";

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
        die('does not exirts'); 
    }

    // 2. Make sure seller email matches your primary account email.
    if ($_POST['receiver_email'] != $my_email) {
        $errmsg .= "'receiver_email' does not match: ";
        $errmsg .= $_POST['receiver_email']."\n";
    }

mysql_connect("217.79.179.185","billing2","my4epamuz") or error_log(mysq_error()) die(0);
mysql_select_db('zadmin_billing') or error_log(mysq_error()) die(0);
    $price      = mysql_query("SELECT price FROM order WHERE email = $_POST['receiver_email']") or error_log(mysql_error());  ;

    // 3. Make sure the amount(s) paid match
    if ($_POST['mc_gross'] != $price) {
        $errmsg .= "'mc_gross' does not match: ";
        $errmsg .= $_POST['mc_gross']."\n";
    }
    
    // 4. Make sure the currency code matches
    if ($_POST['mc_currency'] != 'DKK') {
        $errmsg .= "'mc_currency' does not match: ";
        $errmsg .= $_POST['mc_currency']."\n";
    }

    $txn_id = mysql_real_escape_string($_POST['txn_id']);
    $sql = "SELECT COUNT(*) FROM orders WHERE txn_id = '$txn_id'";
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
        mail($my_email, 'IPN Fraud Warning', $body);
        
    } else {
    
        // add this order to a table of completed orders
        $payer_email = mysql_real_escape_string($_POST['payer_email']);
        $mc_gross = mysql_real_escape_string($_POST['mc_gross']);
        $sql = "UPDATE order WHERE email = $payer_email SET payment='1', txn_id=$txn_id, mc_gross=$mc_gross";  
        
        if (!mysql_query($sql)) {
            error_log(mysql_error());
            exit(0);
        }
        
        // send user an email with a link to their digital download
        $to         = filter_var($_POST['payer_email'], FILTER_SANITIZE_EMAIL);
        $subject    = "Webhotel registreret";
        $body       = "Hej $navn <br/>
                        Vi har modtaget din registrering af webhotel, og du vil indenfor 24 timer være klar til at gøre nytte af det.
                        Ønsker du at have hjælp til overflytning af domæner, skriv venligst en mail til info@kmweb.dk, og vi vil hjælpe dig på vej. 

                        Hvis håber vi får et godt samarbejde.

                        Mvh. 
                        KMweb.dk"
        mail($to, $body);
    }
    
} else {
    // manually investigate the invalid IPN
    mail($my_email, 'Invalid IPN', $listener->getTextReport());
}

?>
