<?php
include '../db_connect.php';
include '../config/config.php';
include '../config/functions.php';

mysql_select_db($DBNAME, $con);

if ($_GET['action'] == "invoicechanges") {
	$sqlquery = "UPDATE x_invoice SET inv_amount='" . $_POST['invvalue'] . "',inv_description='" . $_POST['invdesc'] . "',inv_payment_method='" . $_POST['invpaymethod'] . "',inv_payment_id='" . $_POST['invpayid'] . "' WHERE inv_id='" . $_GET['id'] . "'";
	mysql_query($sqlquery);
	$statusreport= "Update-complete.";
} elseif ($_GET['action'] == "packagepricechanges") {
	$sqlquery = "UPDATE x_packages SET pk_price_pm='" . $_POST['pckpricepm'] . "', pk_price_pq='" . $_POST['pckpricepq'] . "', pk_price_py='" . $_POST['pckpricepy'] . "' WHERE pk_id_pk='" . $_GET['id'] . "'";
	mysql_query($sqlquery);
	$statusreport= "Prices-updated";
} else if ($_GET['action'] == "resendwelcomeemail") {
	$emailb = file_get_contents('../templates/emails/user_welcome-email.html');
	$sqlquery = "SELECT * FROM x_accounts WHERE ac_id_pk='" . $_GET['id'] . "'";
	$result = mysql_query($sqlquery);
	while($row = mysql_fetch_array($result)) {
		$emailb = str_replace('$username',$row['ac_user_vc'],$emailb);
		$emailb = str_replace('$cpurl',$PATHTOCP,$emailb);
		$emailb = str_replace('$ns1',$ns1,$emailb);				$emailb = str_replace('$ns2',$ns2,$emailb);
		$toemail = $row['ac_email_vc'];
	}
	sendemail($toemail,"Welcome",$emailb,$fromemail);
	$statusreport= "Email-sent-to-" . $toemail;
} elseif ($_GET['action'] == "paymentchanges") {
	$sqlquery = "UPDATE x_payment_methods SET pm_data='" . $_POST['pmdata'] . "',pm_active='" . $_POST['pmactive'] . "',pm_name='" . $_POST['pmname'] . "' WHERE pm_id='" . $_GET['id'] . "'";
	mysql_query($sqlquery);
	$statusreport= "Changes-made.";
} else if ($_GET['action'] == "addpaymentmethod") {
	$sqlquery = "INSERT INTO x_payment_methods (pm_data,pm_active,pm_name) VALUES('" . $_POST['pmdata'] . "','" . $_POST['pmactive'] . "','" . $_POST['pmname'] . "')";
	mysql_query($sqlquery);
echo $sqlquery;
exit;
	$statusreport= "Method-added.";
}

//SEE IF WE SHOULD REDIRECT THE USER BACK
if ($_GET['view'] == "") {
 echo $finalmessage;
} else {
 header('Location: index.php?view=' . $_GET['view'] . '&mode=' . $_GET['mode'] . "&status=" . $statusreport);
}

?>