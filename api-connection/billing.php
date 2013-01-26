<?php
/**
 * Create user page for ZPX billing API integration 
 *
 * @author Martin Kollerup
 * @copyright martinkole
 * @link http://www.kmweb.dk/
 * @license GPL (http://www.gnu.org/licenses/gpl.html)
 */
error_reporting(E_ERROR);
include ('lib/functions.php');

$id = (isset($_GET['id'])) ? $_GET['id'] : "";
$head = null;
$title = null;
$template = null;
$package_name = null;

//Are there a id?
if(empty($id)){
	zpanelx::error('No package selected');
	echo zpanelx::template("Error","","");
	die();
} 
//Only digits?
else if(!preg_match('/^\d+$/', $id)){
	zpanelx::error('Invalid package id');
	echo zpanelx::template("Error","","");
	die();
}

require_once('lib/recaptchalib.php');

$username 		= (isset($_POST["username"])) ? filter_var($_POST["username"],FILTER_SANITIZE_STRING) : "";
$email	 		= (isset($_POST["email"])) ? $_POST["email"] : "";
$companyname 		= (isset($_POST["companyname"])) ? filter_var($_POST["companyname"],FILTER_SANITIZE_STRING) : "";
$fullname 		= (isset($_POST["fullname"])) ? filter_var($_POST["fullname"],FILTER_SANITIZE_STRING) : "";
$address 		= (isset($_POST["address"])) ? ($_POST["address"]) : "";
$transfer_help 	= (isset($_POST["transfer_help"])) ? filter_var($_POST["transfer_help"], FILTER_SANITIZE_STRING) : "";
$ssl_support 	= (isset($_POST["ssl_support"])) ? filter_var($_POST["ssl_support"], FILTER_SANITIZE_STRING) : "";
$website 		= (isset($_POST["website"])) ? filter_var($_POST["website"], FILTER_SANITIZE_STRING) : "";

$postcode 		= (isset($_POST['postcode'])) ? $_POST["postcode"] : "";
$telephone 		= (isset($_POST['telephone'])) ? $_POST["telephone"] : "";
$payperiod 		= (isset($_POST['payperiod'])) ? $_POST["payperiod"] : "";
$packageid 		= (isset($_POST['packageid'])) ? $_POST["packageid"] : "";
$captcha_response 	= (isset($_POST['recaptcha_response_field'])) ? $_POST["recaptcha_response_field"] : "";
$captcha_challenge 	= (isset($_POST['recaptcha_challenge_field'])) ? $_POST["recaptcha_challenge_field"] : "";

if (isset($_POST['submit'])) {	

	//start by checking for missing inputs and check if they are lega!
	if (empty($username) || $username == "Username") {
		zpanelx::error("Username missing");
	}
	if (empty($email) || $email == "Email") {
		zpanelx::error("Email address missing");
	}
	else{
		if(!preg_match('/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i', $email)){
			zpanelx::error("Email is not true");
		}
	}
	if (empty($fullname) || $fullname == "Full name") {
		zpanelx::error("Full name missing");
	}
	if (empty($address) || $address == "Address") {
		zpanelx::error("Address missing");
	}
	if (empty($postcode) || $postcode == "Post code") {
		zpanelx::error("Postcode missing");
	} else {
		if(preg_match("/^([1]-)?[0-9]{3}-[0-9]{3}-[0-9]{4}$/i",$postcode)){
			zpanelx::error("Telephone is not valid");
		}
	}
	if (empty($telephone) || $telephone == "Telephone") {
		zpanelx::error("Telephone number missing");
	} else {
		if(preg_match("/^([1]-)?[0-9]{3}-[0-9]{3}-[0-9]{4}$/i",$telephone)){
			zpanelx::error("Telephone is not valid");
		}
	}
	if (empty($payperiod)) {
		zpanelx::error("Payment Period is missing");
	} else {
		if(preg_match("/^([1]-)?[0-9]{3}-[0-9]{3}-[0-9]{4}$/i",$payperiod)){
			zpanelx::error("Payment Period is not valid");
		}
	}

    //TODO: re-enable Captcha
	//disabling captcha for dev purpose
	/*if (empty($captcha_response) || empty($captcha_challenge)) {
		zpanelx::error("Captcha Challenge is missing");
	} else {
          //$privatekey = getConfig('rc_private_key');
          $resp = recaptcha_check_answer(zpanelx::getConfig('rc_private_key'),$_SERVER["REMOTE_ADDR"],$captcha_challenge,$captcha_response);

          if (!$resp->is_valid) {
            zpanelx::error("Captcha Challenge is not valid");
          }
	}*/

	//is the username already used?
	$data = "<username>".$username."</username>";
	$usernameExits = zpanelx::api("billing", "UsernameExits", $data);

	if($usernameExits['code'] != "3"){
		zpanelx::error($usernameExits['human']);
	}
	//If no error have been added create the user
	if(empty(zpanelx::$zerror)){
		$token = zpanelx::generateToken();
		$pwd = zpanelx::generatePassword();
		$data = '<resellerid>'.zpanelx::getConfig('reseller_id').'</resellerid>
		<groupid>'.zpanelx::getConfig('group_id').'</groupid>
		<username>'.$username.'</username>
		<companyname>'.$companyname.'</companyname>
		<fullname>'.$fullname.'</fullname>
		<email>'.$email.'</email>
		<postcode>'.$postcode.'</postcode>
		<address>'.$address.' </address>
		<phone>'.$telephone.'</phone>
		<packageid>'.$packageid.'</packageid>
		<period>'.$payperiod.'</period>
		<type>Initial Signup</type>		
		<domain>'.$website.'</domain>
		<password>'.$pwd.'</password>
		<web_help>'.$transfer_help.'</web_help>
		<web_help>'.$ssl_support.'</web_help>
		<token>'.$token.'</token>
		';
		
		$createBilling = zpanelx::api("billing", "CreateClient", $data);
        
        //create invoice
        if($createBilling['code'] == "1"){
            //Request for the package prices
            $data = "<pk_id>".$id."</pk_id>";
            $package = zpanelx::api("billing", "Package", $data);
            if (!empty($package['package']['id'])) {
                $hosting_options = json_decode($package['package']['hosting'], true);
                
                if(is_array($hosting_options['hosting'])){
                    foreach($hosting_options['hosting'] as $option){
                        if($payperiod == $option['month']){
                            $period_amt = $option['price'];
                            break;
                        }
                    }
                                                           
                    if($period_amt){
                        $payment_desc = '"pk_id":'.$id.',"price":"'.$period_amt.'","period":"'.$payperiod.'"';
		                $token = zpanelx::generateToken();
		                $data = '<user_id>'.$createBilling['uid'].'</user_id>
		                <amount>'.$period_amt.'</amount>
		                <desc>{'.$payment_desc.'}</desc>
		                <type>Initial Signup</type>
		                <token>'.$token.'</token>
		                ';
                    
                		$createInvoice = zpanelx::api("billing", "CreateInvoice", $data);                    
                    }
                }
	            
            }        
        }
        
		if($createBilling['code'] == "1" && $createInvoice['code'] == "1"){
			header('Location: pay.php?id='.$token);

		} else{
			
			zpanelx::error("Error creating billing");
			zpanelx::sendemail(zpanelx::getConfig('error_email'), "Error creating billing", "The invoice have not been created for user: ".$username."(".$email.") Error code:". $createBilling['create_invoice'] );
		}

	}
}//end submit

//Request for the package prices
$data = "<pk_id>".$id."</pk_id>";
$package = zpanelx::api("billing", "Package", $data);

if (!empty($package['package']['id'])) {
	$package_name 	= $package['package']['name'];
	$hosting 		= $package['package']['hosting'];
	$domain 		= $package['package']['domain'];
}
else {
	zpanelx::error("Error getting package data", true, false);
}

//Adding the price from xmws to input fields
if(!empty($package_name)){

	//Get the setting
	$data     	= "<settings><setting>payment.cs</setting></settings>";
	$setting 	= zpanelx::api("billing", "Setting", $data);
	$cs  		= $setting['settings']['payment.cs'];

	$payoptions = json_decode($hosting, true);
	$payoption 	= null;

	foreach($payoptions['hosting'] as $option){
		$payoption .= "<input type=\"radio\" name=\"payperiod\" value=\"".$option['month']."\">".$option['month']." month @ ".$cs." ".$option['price']."</input><br />";
	}
	//Insert values to template
	$template = file_get_contents('themes/billing.tpl');
	$template = str_replace('{{action}}', htmlspecialchars($_SERVER['SCRIPT_NAME'] .'?'. $_SERVER['QUERY_STRING']), $template);
	$template = str_replace('{{packagename}}', htmlentities($package_name, ENT_QUOTES), $template);
	$template = str_replace('{{payoptions}}', $payoption, $template);
	$template = str_replace('{{pid}}', $id, $template);
	$template = str_replace('{{captcha_pub_key}}', zpanelx::getConfig('rc_public_key'), $template);
	$title 	  = "Buy hosting";

	//if post use the entered value, else enter the default values
	$template = ($username ? str_replace('{{username}}', $username, $template) : str_replace('{{username}}', "Username", $template));
	$template = ($email ? str_replace('{{email}}', $email, $template) : str_replace('{{email}}', "Email", $template));
	$template = ($fullname ? str_replace('{{fullname}}', $fullname, $template) : str_replace('{{fullname}}', "Full name", $template));
	$template = ($address ? str_replace('{{address}}', $address, $template) : str_replace('{{address}}', "Address", $template));
	$template = ($postcode ? str_replace('{{postcode}}', $postcode, $template) : str_replace('{{postcode}}', "Post code", $template));
	$template = ($telephone ? str_replace('{{telephone}}', $telephone, $template) : str_replace('{{telephone}}', "Telephone", $template));
	$template = ($telephone ? str_replace('{{transfer_website}}', $website, $template) : str_replace('{{transfer_website}}', "Website", $template));

} else{
	zpanelx::error("Invalid package selected", false, true);
}

echo zpanelx::template($title, $head, $template);
?>
