<?php


include '../config/functions.php';
include '../db_connect.php';

mysql_select_db($DBNAME, $con);

if ($_GET['view'] == "invoices") {

	$sqlquery = "SELECT * FROM x_invoice";
	if ($_GET['mode'] == "2") {
		$sqlquery .= " WHERE inv_payment_method='no'";
	}
	if ($_GET['uid'] == "") {
	} else {
		if ($_GET['mode'] == "") {
			$sqlquery .= " WHERE inv_user='" . $_GET['uid'] . "'";
		} else {
			$sqlquery .= " AND inv_user='" . $_GET['uid'] . "'";
		}
	}

	$result = mysql_query($sqlquery);

	if (!mysql_select_db($DBNAME)) {
    		die('Could not select database: ' . mysql_error());
	}
	$someinfo = "<table border=0 width=100% id=table1><tr><td><b>ID:</b></td><td><b>User ID:</b></td><td><b>Price:</b></td><td><b>Description:</b></td><td><b>Due Date:</b></td><td><b>Created on:</b></td><td><b>Paid:</b></td><td><b>Payment ID:</b></td><td><b>Action_ID:</b></td><td>What shall we do?</td><td></td></tr>";
	while($row = mysql_fetch_array($result)) {
		$currentid = $row['inv_id'];
		$currentuserid = $row['inv_user'];

				$sqlquery = "SELECT * FROM x_accounts WHERE ac_id_pk='" . $currentuserid . "'";
		$result2 = mysql_query($sqlquery);
		while($row2 = mysql_fetch_array($result2)) {
			$currentuseralias = $row2['ac_user_vc'];
		}

		$someinfo .= "<tr><form method=POST action=action.php?action=invoicechanges&id=" . $currentid . "&view=invoices&mode=" . $_GET['mode'] . ">";
		$someinfo .= "<td>" . $currentid . "</td>";
		$someinfo .= "<td><a href=index.php?view=invoices&mode=" . $_GET['mode'] . "&uid=" . $currentuserid . ">" . $currentuserid . " | " . $currentuseralias . "</a></td>";
	
		$someinfo .= "<td><input type=text name=invvalue value=" . $row['inv_amount'] . "></td>";
		$someinfo .= "<td><input type=text name=invdesc value=" . $row['inv_description'] . "></td>";
		$someinfo .= "<td>" . $row['inv_duedate'] . "</td>";
		$someinfo .= "<td>" . $row['inv_createddate'] . "</td>";
		$someinfo .= "<td><input type=text name=invpaymethod value=" . $row['inv_payment_method'] . "></td>";
		$someinfo .= "<td><input type=text name=invpayid value=" . $row['inv_payment_id'] . "></td>";
		$someinfo .= "<td>" . $row['inv_act'] . "</td>";
    		$someinfo .= "<td><input type=submit value=Save Row></td>";
		$someinfo .= "</form></td><td><form method=POST action=action.php?action=resendwelcomeemail&id=" . $currentuserid . "&view=invoices&mode=" . $_GET['mode'] . "><input type=submit value=Send-Welcome-Email></form></td></tr>";

	}
	$someinfo .= "</table>";

} elseif ($_GET['view'] == "packages") {
	$sqlquery = "SELECT * FROM x_packages";
	$result = mysql_query($sqlquery);
	if (!mysql_select_db($DBNAME)) {
    		die('Could not select database: ' . mysql_error());
	}
		$someinfo = "<table border=0 width=100% id=table1><tr><td><b>ID:</b></td><td><b>Package Name:</b></td><td><b>Price (pm):</b></td><td><b>Price(pq):</b></td><td><b>Price(py):</b></td><td>What shall we do?</td></tr>";
	while($row = mysql_fetch_array($result)) {
		$currentpackageid = $row['pk_id_pk'];
		$someinfo .= "<tr><form method=POST action=action.php?action=packagepricechanges&id=" . $currentpackageid . "&view=packages>";
		$someinfo .= "<td>" . $currentpackageid . "</td>";
		$someinfo .= "<td>" . $row['pk_name_vc'] . "</td>";
		$someinfo .= "<td><input type=text name=pckpricepm value=" . $row['pk_price_pm'] . "></td>";
		$someinfo .= "<td><input type=text name=pckpricepq value=" . $row['pk_price_pq'] . "></td>";
		$someinfo .= "<td><input type=text name=pckpricepy value=" . $row['pk_price_py'] . "></td>";
    		$someinfo .= "<td><input type=submit value=Save-Prices></td>";
		$someinfo .= "</form></tr>";
		$someinfo .= "<tr><td></td><td colspan=4>Signup URL: " . $PATHWEBURL . "/billing.php?pid=" . $currentpackageid . "</td></tr>";
	}
		$someinfo .= "</table>";
} elseif ($_GET['view'] == "payment_methods") {

	$sqlquery = "SELECT * FROM x_payment_methods";
	$result = mysql_query($sqlquery);

	if (!mysql_select_db($DBNAME)) {
    		die('Could not select database: ' . mysql_error());
	}
	$someinfo = "<table border=0 width=100%><tr><td><b>ID:</b></td><td><b>Data:</b></td><td><b>Active:</b><br>0=disabled;1=active</td><td><b>Name:</b></td><td></td></tr>";
	while($row = mysql_fetch_array($result)) {
		$currentid = $row['pm_id'];

		$someinfo .= "<tr><form method=POST action=action.php?action=paymentchanges&id=" . $currentid . "&view=payment_methods>";
		$someinfo .= "<td>" . $currentid . "</td>";
		$someinfo .= "<td><textarea rows=5 name=pmdata cols=70>" . $row['pm_data'] . "</textarea></td>";
		$someinfo .= "<td><input type=text name=pmactive value=" . $row['pm_active'] . "></td>";
		$someinfo .= "<td><input type=text name=pmname value=" . $row['pm_name'] . "></td>";
		$someinfo .= "<td><input type=submit value=Save Row></td>";
		$someinfo .= "</form></tr>";

	}

		$someinfo .= "<tr><form method=POST action=action.php?action=addpaymentmethod&view=payment_methods>";
		$someinfo .= "<td>" . $currentid . "</td>";
		$someinfo .= "<td><textarea rows=5 name=pmdata cols=70></textarea></td>";
		$someinfo .= "<td><input type=text name=pmactive></td>";
		$someinfo .= "<td><input type=text name=pmname></td>";
		$someinfo .= "<td><input type=submit value=Add Row></form></td>";
	$someinfo .= "</table>";

}
else {
	$someinfo = "<h1>Welcome to zpanelx auto sign-up";
}

?>

<?php 
include '../templates/admin_head.html';
echo $_GET['status'] . "<p>";
echo $someinfo;

//We need to check if it is set not set = frontpage
$include_url = '../templates/admin_top_' . $_GET['view'] . '.html'; 

//check if the file exits
if(file_exists($include_file)){
    include $include_url; 
}
?>