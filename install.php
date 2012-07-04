<?php

echo "<h1>Install zpanel x SignUp</h1>";
echo "Please follow these guides.:";
echo "<ul><li>Edit your database settings in the config/config.php</li>";
echo "</ul>";
echo "<font color=red>This script is offered without liability, use at own risk.</font>";
echo "Refresh the page if you have edited the settings...";

include 'db.php';
include 'config/functions.php';

//connect to the databse
$db = db::getConnection();

$someinfo = "This is the log:<br \><br />";

$sqlquery = "ALTER TABLE x_packages ADD pk_price_pm double, ADD pk_price_pq double,ADD pk_price_py double";
$stmt = $db->prepare($sqlquery);

if($stmt->execute()){
	$someinfo .= " Modified x_packages, successful<br>";
}
else{
	$someinfo .= " Could not modify x_packages<br>";
}

$sqlquery = "ALTER TABLE x_accounts ADD ac_price_pm double,ADD ac_invoice_nextdue date, ADD ac_invoice_period varchar(5)";
$stmt = $db->prepare($sqlquery);
if($stmt->execute()){
	$someinfo .= " Modified x_accounts, successful<br>";
}
else{
	$someinfo .= " Could not modify x_accounts<br>";
}


$sqlquery = "UPDATE x_packages SET pk_price_pm='0'";
$stmt = $db->prepare($sqlquery);
if($stmt->execute()){
	$someinfo .= "Package prices set to 0<br>";
}
else{
	$someinfo .= "Package prices could not be set to 0<br>";
}

$sqlquery = "UPDATE x_accounts SET ac_price_pm='0'";
$stmt = $db->prepare($sqlquery);
if($stmt->execute()){
	$someinfo .= "All existing accounts set to 0 monthly<br>";
}
else{
	$someinfo .= "All existing accounts could not be set to 0 monthly<br>";
}

$sqlquery = "CREATE TABLE x_payment_methods (pm_id int NOT NULL AUTO_INCREMENT PRIMARY KEY,pm_name varchar(50),pm_data text,pm_active int(3))";
$stmt = $db->prepare($sqlquery);
if($stmt->execute()){
	$someinfo .= "x_payment_methods table created<br>";
}
else{
	$someinfo .= "x_payment_methods table could not be created<br>";
}

$sqlquery = "CREATE TABLE x_invoice (inv_id int NOT NULL AUTO_INCREMENT PRIMARY KEY,inv_user varchar(50),inv_amount double,inv_description varchar(500),inv_duedate date,inv_createddate date,inv_payment_method varchar(10), token varchar(255) DEFAULT 'no',inv_payment_id varchar(150) DEFAULT 'no',inv_act varchar(5) DEFAULT '0')";
$stmt = $db->prepare($sqlquery);
if($stmt->execute()){
	$someinfo .= "x_invoice table created<br>";
}
else{
	$someinfo .= "x_invoice table could not be created<br>";
}


$someinfo .= "<p><br /><b>Next steps:</b><br>- Add a cron job scheduled to run daily: [WEB-URL-TO-SCRIPT]/cron/invoices.php";
$someinfo .= "<br>- Delete file /adm/install.php<br>- Personalise emails in /templates/emails<br>(emails are automatically sent on sign up and when clicking 'welcome email' in the admin area).";
$someinfo .="<br /><b>ZPANELX SIGNUO HAVE BEEN INSTALLED";

echo $someinfo;
