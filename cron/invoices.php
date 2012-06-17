<?php

include '../config/functions.php';
include '../db_connect.php';

$todaydate = date("Y-m-d");// current date
$newdate = strtotime(date("Y-m-d", strtotime($todaydate)) . "+" . $invoicedays . " day");

echo $newdate;

mysql_select_db($DBNAME, $con);
$sqlquery = "SELECT * FROM x_accounts WHERE ac_invoice_nextdue='" . $newdate . "'";
$result = mysql_query($sqlquery);

if (!mysql_select_db($DBNAME)) {
    die('Could not select database: ' . mysql_error());
}

while($row = mysql_fetch_array($result))
  {
    $accountslist .= $row['ac_id_pk'] . " | " . $row['ac_user_vc'] . " | Due: " . $row['ac_invoice_nextdue'] . "<br>";

    if ($_GET['run'] == "invoices") {
	//Start - determine how many months to add based on x_accounts.ac_invoice_period
	$accperiod = $row['ac_invoice_period'];
		if ($accperiod == 1) {
			$monthstoadd  =  "1";
		} elseif ($accperiod == 2) {
    			$monthstoadd  = "3";
		} elseif ($accperiod == 3) {
   			$monthstoadd  = "12";
		} else {
   			$accountslist  .= "No valid period!!";
		}
	//0 - create next due date
		$todaydate = date("Y-m-d");
		$currduedate = $row['ac_invoice_nextdue'];
		$nextduedate = strtotime(date("Y-m-d", strtotime($currduedate )) . "+" . $monthstoadd  . " month");
		$nextduedate = date('Y-m-d', $nextduedate );
	//1 - create invoice
		$sqlquery = "INSERT INTO x_invoice(inv_user, inv_amount, inv_description, inv_duedate, inv_createddate, inv_act) VALUES ('" . $row['ac_id_pk'] . "','" . $row['ac_price_pm'] . "','Monthly Invoice','" . $row['ac_invoice_nextdue'] . "','" . $todaydate . "','0')";
		mysql_query($sqlquery);

	//2 - update next invoice due date
		$sqlquery = "UPDATE x_accounts SET ac_invoice_nextdue = '" . $nextduedate . "' WHERE ac_id_pk = '" . $row['ac_id_pk'] . "'";
		mysql_query($sqlquery);
	$accountslist .= "     run | Months added: " . 	$monthstoadd  . " | Next due: " . $nextduedate . "<p>";
    }

  }

echo "<b>Accounts due in " . $invoicedays . " days:</b><br>";
echo $accountslist;
?>