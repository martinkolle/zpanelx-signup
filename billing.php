<?php
// Developed by Tony Maclennan (tonymaclennan@hotmail.com)
// Developed for ZPanelCP8
// Not for resale
define('zxBilling', 1);

include 'config/functions.php';
include ('db.php');
include 'db_connect.php';
//include 'config/config.php';

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
	$transfer	= filter_var($_POST["transfer_help"], FILTER_SANITIZE_STRING);
	$website	= filter_var($_POST["website"], FILTER_SANITIZE_STRING);

	$postcode 	= ($_POST["postcode"]);
	$telephone 	= ($_POST["telephone"]);
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
	if (empty($payperiod)) {
		$error[] = "Payperiod is missing";
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
	//TODO: Add the påassword when the payment is accepted
	if(!$error){
		//add the new user
		//use "newUser" if you don't want to use the API!!!
		zpanelx::newUser2($payperiod, $packageid, $token, zpanelx::generatePassword(), $username, $email, $fullname, $adress, $postcode, $telephone);
		
		if(isset(zpanelx::$newUserError)){
			$error = "It is currently not possible to crate your account. A notification have been sent to the provider.<br /><b>We are sorry!</b>";
			$template = file_get_contents('templates/billing_error.html');
			$template = str_replace('{{title}}', "Errors", $template);
			$template = str_replace('{{error}}', $error, $template);
		}
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
			$loadpayoptions = '<input type="radio" name="payperiod" selected value="1">Monthly @ ' . $cs . $pricepm . '</input><br />';
		}
		if (!$pricepq == "0") {
			$loadpayoptions .= "<input type=\"radio\" name=\"payperiod\" value=\"2\">Quarterly @ " . $cs . $pricepq . "</input><br />";
		}
		if (!$pricepy == "0") {
			$loadpayoptions .= "<input type=\"radio\" name=\"payperiod\" value=\"3\">Yearly @ " . $cs . $pricepy . "</input><br />";
		}

		$template = file_get_contents('templates/billing.html');
		$template = str_replace(':action', htmlspecialchars($_SERVER['SCRIPT_NAME'] .'?'. $_SERVER['QUERY_STRING']), $template);
		$template = str_replace(':selectedpackagename', htmlentities($selectedpackagename, ENT_QUOTES), $template);
		$template = str_replace(':payoptions', $loadpayoptions, $template);
		$template = str_replace(':pid', $pid, $template);
		$template = str_replace(':title', "Buy hosting", $template);

		//if post use the entered value, else enter the field name
		$template = ($username ? str_replace(':username', $username, $template) : str_replace(':username', "Username", $template));
		$template = ($email ? str_replace(':email', $email, $template) : str_replace(':email', "Email", $template));
		$template = ($fullname ? str_replace(':fullname', $fullname, $template) : str_replace(':fullname', "Full name", $template));
		$template = ($adress ? str_replace(':adress', $adress, $template) : str_replace(':adress', "Adress", $template));
		$template = ($postcode ? str_replace(':postcode', $postcode, $template) : str_replace(':postcode', "Post code", $template));
		$template = ($telephone ? str_replace(':telephone', $telephone, $template) : str_replace(':telephone', "Telephone", $template));

		if($error){
			$template = str_replace(':error', $error, $template);
			$template = str_replace(':head', '<style type="text/css">#error{display:block !important;}</style>', $template);
		}
		else{
			$template = str_replace(':error', "", $template);
			$template = str_replace(':head', "", $template);

		}


	}//end selectedpackagename
	else{
		$error = "Invalid Package selected";
		$template = file_get_contents('templates/billing_error.html');
		$template = str_replace('{{title}}', "Errors", $template);
		$template = str_replace('{{error}}', $error, $template);
	}
} //end pid is not empty
else{
		$error = "No package selected";
		$template = file_get_contents('templates/billing_error.html');
		$template = str_replace('{{title}}', "Errors", $template);
		$template = str_replace('{{error}}', $error, $template);
}
	echo $template;
?>
