<?php
// Developed by Tony Maclennan (tonymaclennan@hotmail.com)
// Developed for ZPanelCP8
// Not for resale
define('zxBilling', 1);

include ('db.php');
include 'db_connect.php';
//include 'config/config.php';
include 'config/functions.php';

$pid = $_GET['pid'];
//connect to the database
$db = db::getConnection();

//If form has been submitted, action it
if (isset($_POST['SubmitForm'])) {

	$error 		= array();
	$username 	= filter_var($_POST["username"],FILTER_SANITIZE_STRING);
	$email 		= filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
	$fullname 	= filter_var($_POST["fullname"],FILTER_SANITIZE_STRING);
	$adress 	= filter_var($_POST["address"], FILTER_SANITIZE_STRING);
	$postcode 	= ctype_digit($_POST["postcode"]);
	$telephone 	= ctype_digit($_POST["telephone"]);
	//TODO: IS THE INFORMATIONS RIGHT
	
	$payperiod 	= $_POST['payperiod'];
	$packageid 	= $_POST['packageid'];
	$token 		= zpanelx::generateToken();


	//start by checking for missing inputs
	if (empty($username)) {
		$error[] = "Username missing";
	}
	if (empty($email)) {
		$error[] = "Email address missing";
	}
	else{
		if(zpanelx::checkEmail($email) !== true){
			$error[] = "Email is not true";
		}
	}
	if (empty($fullname)) {
		$error[] = "Full name missing";
	}
	if (empty($adress)) {
		$error[] = "Address missing";
	}
	if (empty($postcode)) {
		$error[] = "Postcode missing";
	}
	if (empty($telephone)) {
		$error[] = "Telephone number missing";
	}

	$stmt = $db->prepare("SELECT * FROM x_accounts WHERE ac_user_vc= ?");
		
	if($stmt->execute(array($username))){
		$row = $stmt->fetch();
		$user_exits = $row['ac_email_vc'];
	}

	if (isset($user_exits)) {
		$error[] = "Username allready exits. Please choose a new!";
	}

	//If there not are any erorrs.. proceed.
	if(!$error){
		//add the new user
		zpanelx::newUser($payperiod, $packageid, $token, zpanelx::generatePassword(), $username, $email, $fullname, $adress, $postcode, $telephone);
		//the user will be redirected from the function
	}
	else{
		$error = implode("<br/>",$error);
	}
}

//Firstly read URL to get package and user type
if(!empty($pid)){

	//select package informations
	$stmt = $db->prepare("SELECT * FROM x_packages WHERE pk_id_pk= ?");
		
	if($stmt->execute(array($pid))){
		$row = $stmt->fetch();
		$selectedpackagename = $row['pk_name_vc'];
		$pricepm = $row['pk_price_pm'];
		$pricepq = $row['pk_price_pq'];
		$pricepy = $row['pk_price_py'];
	}

	//we need to ensure that the package exits.
	if(!empty($selectedpackagename)){
		//add payments options
		if (!$pricepm == "0") {
			$loadpayoptions = "<option selected value=1>Monthly @ " . $cs . $pricepm . "</option>";
		}
		if (!$pricepq == "0") {
			$loadpayoptions .= "<option value=2>Quarterly @ " . $cs . $pricepq . "</option>";
		}
		if (!$pricepy == "0") {
			$loadpayoptions .= "<option value=3>Yearly @ " . $cs . $pricepy . "</option>";
		}

		$template = file_get_contents('templates/billing.html');
		$template = str_replace(':action', htmlspecialchars($_SERVER['SCRIPT_NAME'] .'?'. $_SERVER['QUERY_STRING']), $template);
		$template = str_replace(':selectedpackagename', htmlentities($selectedpackagename, ENT_QUOTES), $template);
		$template = str_replace(':payoptions', $loadpayoptions, $template);
		$template = str_replace(':pid', $pid, $template);
		$template = str_replace(':title', "Buy hosting", $template);
		if($error){
			$template = str_replace(':error', $error, $template);
		}
		else{
			$template = str_replace(':error', "", $template);
		}


	}//end selectedpackagename
	else{
		$error = "Invalid Package selected";
		$template = file_get_contents('templates/billing_error.html');
		$template = str_replace(':title', "Errors", $template);
		$template = str_replace(':error', $error, $template);
	}
} //end pid is not empty
else{
		$error = "No package selected";
		$template = file_get_contents('templates/billing_error.html');
		$template = str_replace(':title', "Errors", $template);
		$template = str_replace(':error', $error, $template);
}
	echo $template;
?>
