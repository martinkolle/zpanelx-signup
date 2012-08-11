<?php

/**
 *  Main page zpanelx Auto-sign-up
 *  
 *  @package    Zpanelx Auto-sign-up
 *  @author     Martin Kollerup
 *  @license    http://opensource.org/licenses/gpl-3.0.html
 */

include ('lib/functions.php');
include ('lib/xmwsclient.class.php');

if(isset($_GET['id'])){
	$id 	= $_GET['id'];	
} else{
	$id = "";
}

//Are there a id?
if(empty($id)){
	zpanelx::error('No package selected');
	echo zpanelx::template("Error","","");
	die();
} 
//And is it in digits only?
else if(!preg_match('/^\d+$/', $id)){
	zpanelx::error('Package id invalid');
	echo zpanelx::template("Error","","");
	die();
}

if (isset($_POST['submit'])) {
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

		//start by checking for missing inputs
	if (empty($username)) {
		zpanelx::error("Username missing");
	}
	if (empty($email)) {
		zpanelx::error("Email address missing");
	}
	else{
		if(!preg_match('/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i', $email)){
			zpanelx::error("Email is not true");
		}
	}
	if (empty($fullname)) {
		zpanelx::error("Full name missing");
	}
	if (empty($adress)) {
		zpanelx::error("Address missing");
	}
	if (empty($postcode)) {
		zpanelx::error("Postcode missing");
	} else {
		if(preg_match("/^([1]-)?[0-9]{3}-[0-9]{3}-[0-9]{4}$/i",$postcode)){
			zpanelx::error("Telephone is not valid");
		}
	}
	if (empty($telephone)) {
		zpanelx::error("Telephone number missing");
	} else {
		if(preg_match("/^([1]-)?[0-9]{3}-[0-9]{3}-[0-9]{4}$/i",$telephone)){
			zpanelx::error("Telephone is not valid");
		}
	}
	if (empty($payperiod)) {
		zpanelx::error("Payperiod is missing");
	} else {
		if(preg_match("/^([1]-)?[0-9]{3}-[0-9]{3}-[0-9]{4}$/i",$payperiod)){
			zpanelx::error("Telephone is not valid");
		}
	}

	//is the username already used?
	$data = "<username>".$username."</username>";
	$usernameExits = zpanelx::api("reseller_billing", "UsernameExits", $data, zpanelx::getConfig('zpanel_url'), zpanelx::getConfig('api'));

	if($usernameExits['xmws']['content']['code'] != "3"){
		zpanelx::error($usernameExits['xmws']['content']['human']);
	}
	if(empty(zpanelx::$zerror)){
		zpanelx::addUser($payperiod, $packageid, zpanelx::generateToken(), zpanelx::generatePassword(), $username, $email, $fullname, $adress, $postcode, $telephone);
	}
}//end submit

//Request for the package prices
$data = "<pk_id>".$id."</pk_id>";
$package = zpanelx::api("reseller_billing", "Package", $data, zpanelx::getConfig('zpanel_url'), zpanelx::getConfig('api'));
if (!empty($package['xmws']['content']['package']['id'])) {
	$package_name 	= $package['xmws']['content']['package']['name'];
	$price_pm 		= $package['xmws']['content']['package']['pm'];
	$price_pq 		= $package['xmws']['content']['package']['pq'];
	$price_py 		= $package['xmws']['content']['package']['py'];
}
else {
	zpanelx::error("Error getting package data:", true);
}

//Adding the price from xmws to input fields
if(!empty($package_name)){
	$payoptions = array();
	
	if($price_pm != "0"){
		array_push($payoptions, '<input type="radio" name="payperiod" selected value="1">Monthly @ '.zpanelx::getConfig('cs')." ".$price_pm .'</input><br />');
	}
	if($price_pq != "0"){
		array_push($payoptions, "<input type=\"radio\" name=\"payperiod\" value=\"2\">Quarterly @ ".zpanelx::getConfig('cs')." ".$price_pq."</input><br />");
	}
	if($price_py != "0"){
		array_push($payoptions, "<input type=\"radio\" name=\"payperiod\" value=\"3\">Yearly @ ".zpanelx::getConfig('cs')." ".$price_py."</input><br />");
	}
	//need to have them as string
	foreach($payoptions as $key => $option){
		$payoption .= $option; 
	}
	//inserting the values to a template
	$template = file_get_contents('templates/billing.html');
	$template = str_replace('{{action}}', htmlspecialchars($_SERVER['SCRIPT_NAME'] .'?'. $_SERVER['QUERY_STRING']), $template);
	$template = str_replace('{{packagename}}', htmlentities($packagename, ENT_QUOTES), $template);
	$template = str_replace('{{payoptions}}', $payoption, $template);
	$template = str_replace('{{pid}}', $id, $template);
	$title 	  = "Buy hosting";

	//if post use the entered value, else enter the field name
	$template = ($username ? str_replace('{{username}}', $username, $template) : str_replace('{{username}}', "Username", $template));
	$template = ($email ? str_replace('{{email}}', $email, $template) : str_replace('{{email}}', "Email", $template));
	$template = ($fullname ? str_replace('{{fullname}}', $fullname, $template) : str_replace('{{fullname}}', "Full name", $template));
	$template = ($adress ? str_replace('{{adress}}', $adress, $template) : str_replace('{{adress}}', "Adress", $template));
	$template = ($postcode ? str_replace('{{postcode}}', $postcode, $template) : str_replace('{{postcode}}', "Post code", $template));
	$template = ($telephone ? str_replace('{{telephone}}', $telephone, $template) : str_replace('{{telephone}}', "Telephone", $template));
}
else{
	zpanelx::error("Invalid package selected");
}
	//Echo the template
	echo zpanelx::template($title, $head, $template);
	print_r(zpanelx::$zerror);
?>