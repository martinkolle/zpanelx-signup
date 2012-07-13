<?php

/**
 *  Functions for zpanelx Auto-sign-up
 *  
 *  @package    Zpanelx Auto-sign-up
 *  @author     Martin Kollerup
 *  @license    http://opensource.org/licenses/gpl-3.0.html
 */

define('zxBilling', 1);

include ('lib/functions.php');
include ('lib/db.php');
include ('lib/xmwsclient.class.php');

$pid = $_GET['pid'];
//connect to the database
$db = db::getConnection();

//If $form has been submitted, action it
if (isset($_POST['Submit$form'])) {

	$error 		= array();
	$username 	= filter_var($_POST["username"],FILTER_SANITIZE_STRING);
	$email 		= filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
	$fullname 	= filter_var($_POST["fullname"],FILTER_SANITIZE_STRING);
	$adress 	= filter_var($_POST["address"], FILTER_SANITIZE_STRING);
	$transfer	= filter_var($_POST["transfer_help"], FILTER_SANITIZE_STRING);
	$website	= filter_var($_POST["website"], FILTER_SANITIZE_STRING);

	$postcode 	= ($_POST["postcode"]);
	$telephone 	= ($_POST["telephone"]);
	//TODO: IS THE IN$formATIONS RIGHT
	
	$payperiod 	= ($_POST['payperiod']);
	$packageid 	= ($_POST['packageid']);
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
	} else {
		if(preg_match("/^([1]-)?[0-9]{3}-[0-9]{3}-[0-9]{4}$/i",$postcode)){
			$error[] = "Telephone is not valid";
		}
	}
	if (empty($telephone)) {
		$error[] = "Telephone number missing";
	} else {
		if(preg_match("/^([1]-)?[0-9]{3}-[0-9]{3}-[0-9]{4}$/i",$telephone)){
			$error[] = "Telephone is not valid";
		}
	}
	if (empty($payperiod)) {
		$error[] = "Payperiod is missing";
	} else {
		if(preg_match("/^([1]-)?[0-9]{3}-[0-9]{3}-[0-9]{4}$/i",$payperiod)){
			$error[] = "Telephone is not valid";
		}
	}

	$xmws = new xmwsclient();
	$xmws->InitRequest(zpanelx::getConfig('zpanel_url'), 'reseller_billing', 'UsernameExits', zpanelx::getConfig('api'));
	$xmws->SetRequestData('<username>'.$username.'</username>');
	$returnUsername = $xmws->XMLDataToArray($xmws->Request($xmws->BuildRequest()), 0);
	
	if ($returnUsername['xmws']['content']['code'] != 3) {
		$error[] = $returnUsername['xmws']['content']['human'];
	}

	//If there not are any erorrs.. proceed.
	//TODO: Add the password when the payment is accepted
	if(!$error){
		//add the new user
		//use "newUser" if you don't want to use the API!!!
		zpanelx::newUser2($payperiod, $packageid, $token, zpanelx::generatePassword(), $username, $email, $fullname, $adress, $postcode, $telephone);
		
		//Something went wrong.. The user will be in$formed - turn debug on to find the problem!
		if(isset(zpanelx::$newUserError)){
			$error = "It is currently not possible to crate your account. A notification have been sent to the provider.<br /><b>We are sorry!</b>";
			$template = file_get_contents('templates/billing_error.html');
			$template = str_replace('{{title}}', "Errors", $template);
			$template = str_replace('{{error}}', $error, $template);
		}
	}
	else{
		$error = implode("<br/>",$error);
	}
}

//Firstly read URL to get package and user type
if(!empty($pid)){


	$xmws = new xmwsclient();
	$xmws->InitRequest(zpanelx::getConfig('zpanel_url'), 'reseller_billing', 'Package', zpanelx::getConfig('api'));
	$xmws->SetRequestData('<pk_id>'.$pid.'</pk_id>');
	$return = $xmws->XMLDataToArray($xmws->Request($xmws->BuildRequest()), 0);
	
	if (!empty($return['xmws']['content']['package']['id'])) {
		$package_name 	= $return['xmws']['content']['package']['name'];
		$price_pm 		= $return['xmws']['content']['package']['pm'];
		$price_pq 		= $return['xmws']['content']['package']['pq'];
		$price_py 		= $return['xmws']['content']['package']['py'];
	} 
	else{
		if(zpanelx::getConfig('DEBUG')){
			$error[] = "Error getting the package in$formations";
		}
	}

	//we need to ensure that the package exits.
	if(!empty($package_name)){
		//add payments options
		if ($price_pm != "0") {
			$payoptions = '<input type="radio" name="payperiod" selected value="1">Monthly @ '.zpanelx::getConfig('cs')." ".$price_pm .'</input><br />';
		}
		if ($price_pq != "0") {
			$payoptions .= "<input type=\"radio\" name=\"payperiod\" value=\"2\">Quarterly @ ".zpanelx::getConfig('cs')." ".$price_pq."</input><br />";
		}
		if ($price_py != "0") {
			$payoptions .= "<input type=\"radio\" name=\"payperiod\" value=\"3\">Yearly @ ".zpanelx::getConfig('cs')." ".$price_py."</input><br />";
		}

		$template = file_get_contents('templates/billing.html');
		$template = str_replace(':action', htmlspecialchars($_SERVER['SCRIPT_NAME'] .'?'. $_SERVER['QUERY_STRING']), $template);
		$template = str_replace(':selectedpackagename', htmlentities($packagename, ENT_QUOTES), $template);
		$template = str_replace(':payoptions', $payoptions, $template);
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
