<?php

mysql_connect("localhost", "user", "password") or die(mysql_error());
mysql_select_db("DBName") or die(mysql_error());


// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';
foreach ($_POST as $key => $value) {
$value = urlencode(stripslashes($value));
$req .= "&$key=$value";
}
// post back to PayPal system to validate
$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

$fp = fsockopen ('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);


if (!$fp) {
// HTTP ERROR
} else {
fputs ($fp, $header . $req);
while (!feof($fp)) {
$res = fgets ($fp, 1024);
if (strcmp ($res, "VERIFIED") == 0) {

// PAYMENT VALIDATED & VERIFIED!

$email = $_POST['payer_email'];
$password = mt_rand(1000, 9999);

mysql_query("INSERT INTO users (email, password) VALUES('". mysql_escape_string($email) ."', '".md5($password)."' ) ") or die(mysql_error()); 

$to      = $email;
$subject = 'Download Area | Login credentials';
$message = '

Thank you for your purchase

Your account information
-------------------------
Email: '.$email.'
Password: '.$password.'
-------------------------
            
You can now login at http://yourwebsite.com/PayPal/';
$headers = 'From:noreply@downloadarea.com' . "\r\n";

mail($to, $subject, $message, $headers);



}

else if (strcmp ($res, "INVALID") == 0) {

// PAYMENT INVALID & INVESTIGATE MANUALY!

$to      = 'admin@yourwebsite.com';
$subject = 'Download Area | Invalid Payment';
$message = '

Dear Administrator,

A payment has been made but is flagged as INVALID.
Please verify the payment manualy and contact the buyer.

Buyer Email: '.$email.'
';
$headers = 'From:noreply@yourwebsite.com' . "\r\n";

mail($to, $subject, $message, $headers);

}
}
fclose ($fp);
}
?>
