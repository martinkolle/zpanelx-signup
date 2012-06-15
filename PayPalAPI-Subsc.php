<?php
include 'db_connect.php';
include 'config/config.php';
include 'config/functions.php';

$req = 'cmd=_notify-validate';

foreach ($_POST as $key => $value) {
$value = urlencode(stripslashes($value));
$req .= "&$key=$value";
}

// post back to PayPal system to validate
$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
$fp = fsockopen ('ssl://sandbox.paypal.com', 443, $errno, $errstr, 30);

// assign posted variables to local variables
$item_name = $_POST['item_name'];
$item_number = $_POST['item_number'];
$payment_status = $_POST['payment_status'];
$payment_amount = $_POST['mc_gross'];
$payment_currency = $_POST['mc_currency'];
$txn_id = $_POST['txn_id'];
$receiver_email = $_POST['receiver_email'];
$payer_email = $_POST['payer_email'];


if (!$fp) {
// HTTP ERROR
} else {
fputs ($fp, $header . $req);

        while (!feof($fp)) {
            $res = fgets ($fp, 1024);
            if (strcmp ($res, "VERIFIED") == 0) {
               if ($txn_id == "") {
      exit;
       }
mysql_select_db($DBNAME, $con);
$sqlquery = "SELECT * FROM x_accounts WHERE ac_user_vc='" . $item_number ."'";
$result = mysql_query($sqlquery);

while($row = mysql_fetch_array($result))
  {
    $userid = $row['ac_id_pk'];
    $useremail = $row['ac_email_vc'];
  }

$sqlquery = "SELECT * FROM x_invoice WHERE inv_user='" . $userid ."' AND inv_payment_method='no' AND inv_amount='" . $payment_amount . "' ORDER BY inv_duedate DESC";
$result = mysql_query($sqlquery);

while($row = mysql_fetch_array($result))
  {
    $duedate = $row['inv_duedate'];
    $invoiceid = $row['inv_id'];
    $invoiceaction = $row['inv_act'];
  }

if ($invoiceid == "") {
// no invoice in DB
sendemail($email_paypal_error, "No invoice found (Paypal_subsc)", $invoiceid . " | " . $sqlquery . " || " . $txn_id . " | " . $item_number, $fromemail);
} else {
// invoice found, update records
$result = mysql_query("UPDATE x_invoice SET inv_payment_method = 'PayPal Subscription', inv_payment_id = '" . $txn_id . "' WHERE inv_id=" . $invoiceid );
if ($invoiceaction == "1") {
$result = mysql_query("UPDATE x_accounts SET ac_enabled_in = '1' WHERE ac_id_pk='" . $userid ."'");
}


sendemail($useremail, "Invoice Paid, thank you", $invoiceid . " | " . $sqlquery . " || " . $txn_id . " | " . $item_number, $fromemail);
}

            }
            else if (strcmp ($res, "INVALID") == 0) {
                // log for manual investigation
            }

        }
}
echo "Words";
?>