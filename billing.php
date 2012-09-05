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

$id = (isset($_GET['id'])) ? $_GET['id'] : "";
$head = null;

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


$username 	= (isset($_POST["username"])) ? filter_var($_POST["username"],FILTER_SANITIZE_STRING) : "";
$email	 	= (isset($_POST["email"])) ? filter_var($_POST["email"], FILTER_SANITIZE_EMAIL) : "";
$fullname 	= (isset($_POST["fullname"])) ? filter_var($_POST["fullname"],FILTER_SANITIZE_STRING) : "";
$adress 	= (isset($_POST["address"])) ? filter_var($_POST["address"], FILTER_SANITIZE_STRING) : "";
$transfer_help 	= (isset($_POST["transfer_help"])) ? filter_var($_POST["transfer_help"], FILTER_SANITIZE_STRING) : "";
$website 	= (isset($_POST["website"])) ? filter_var($_POST["website"], FILTER_SANITIZE_STRING) : "";

$postcode 	= (isset($_POST['postcode'])) ? $_POST["postcode"] : "";
$telephone 	= (isset($_POST['telephone'])) ? $_POST["telephone"] : "";
$payperiod 	= (isset($_POST['payperiod'])) ? $_POST["payperiod"] : "";
$packageid 	= (isset($_POST['packageid'])) ? $_POST["packageid"] : "";

if (isset($_POST['submit'])) {	
	$username 	= (isset($_POST["username"])) ? filter_var($_POST["username"],FILTER_SANITIZE_STRING) : "";
	$email	 	= (isset($_POST["email"])) ? filter_var($_POST["email"], FILTER_SANITIZE_EMAIL) : "";
	$fullname 	= (isset($_POST["fullname"])) ? filter_var($_POST["fullname"],FILTER_SANITIZE_STRING) : "";
	$address 	= (isset($_POST["address"])) ? filter_var($_POST["address"], FILTER_SANITIZE_STRING) : "";
	$website_help= (isset($_POST["website_help"])) ? filter_var($_POST["website_help"], FILTER_SANITIZE_STRING) : "";
	$website 	= (isset($_POST["website"])) ? filter_var($_POST["website"], FILTER_SANITIZE_STRING) : "";
	$postcode 	= (isset($_POST['postcode'])) ? $_POST["postcode"] : "";
	$telephone 	= (isset($_POST['telephone'])) ? $_POST["telephone"] : "";

	$payperiod 	= (isset($_POST['payperiod'])) ? $_POST["payperiod"] : "";
	$packageid 	= (isset($_POST['packageid'])) ? $_POST["packageid"] : "";

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
			zpanelx::error("Payperiod is not valid");
		}
	}

	//is the username already used?
	$data = "<username>".$username."</username>";
	$usernameExits = zpanelx::api("reseller_billing", "UsernameExits", $data, zpanelx::getConfig('zpanel_url'), zpanelx::getConfig('api'));

	if($usernameExits['xmws']['content']['code'] != "3"){
		zpanelx::error($usernameExits['xmws']['content']['human']);
	}
	if(empty(zpanelx::$zerror)){
		zpanelx::addUser($payperiod, $packageid, zpanelx::generateToken(), zpanelx::generatePassword(), $username, $email, $fullname, $adress, $postcode, $telephone, $website, $website_help);
	}
}//end submit

//Request for the package prices
$data = "<pk_id>".$id."</pk_id>";
$package = zpanelx::api("reseller_billing", "Package", $data, zpanelx::getConfig('zpanel_url'), zpanelx::getConfig('api'));
if (!empty($package['xmws']['content']['package']['id'])) {
	$package_name 	= $package['xmws']['content']['package']['name'];
	$hosting 		= $package['xmws']['content']['package']['hosting'];
	$domain 		= $package['xmws']['content']['package']['domain'];
}
else {
	zpanelx::error("Error getting package data", true);
}

//Adding the price from xmws to input fields
if(!empty($package_name)){

	$payoptions = json_decode($hosting, true);

	foreach($payoptions['hosting'] as $option){
		$payoption .= "<input type=\"radio\" name=\"payperiod\" value=\"".$option['month']."\">".$option['month']." month @ ".zpanelx::getConfig('cs')." ".$option['price']."</input><br />";
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
	$template = ($address ? str_replace('{{address}}', $address, $template) : str_replace('{{address}}', "Address", $template));
	$template = ($postcode ? str_replace('{{postcode}}', $postcode, $template) : str_replace('{{postcode}}', "Post code", $template));
	$template = ($telephone ? str_replace('{{telephone}}', $telephone, $template) : str_replace('{{telephone}}', "Telephone", $template));
	$template = ($telephone ? str_replace('{{transfer_website}}', $website, $template) : str_replace('{{transfer_website}}', "Website", $template));

}
else{
	zpanelx::error("Invalid package selected");
}
	//Echo the template
	echo zpanelx::template($title, $head, $template);
	//print_r(zpanelx::$zerror);
?>