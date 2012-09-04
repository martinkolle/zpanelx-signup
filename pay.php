<?php

/**
 *  Pay-page for zpanelx Auto-sign-up
 *  
 *  @package    Zpanelx Auto-sign-up
 *  @author     Martin Kollerup
 *  @license    http://opensource.org/licenses/gpl-3.0.html
 */

include ('lib/functions.php');
include ('lib/xmwsclient.class.php');

$head = null;
$token = isset($_GET['id']) ? $_GET['id'] : "";

//Are there a id
if(empty($token)){
     zpanelx::error("No package selected",false,true);
} 
//And is it in digits only?
else if(!preg_match("/^[a-zA-Z0-9]+$/", $token)){
     zpanelx::error("Payment token invalid",false,true);
}

$data     = "<token>".$token."</token>";
$invoice  = zpanelx::api("reseller_billing", "Invoice", $data, zpanelx::getConfig('zpanel_url'), zpanelx::getConfig('api'));

if($invoice['xmws']['content']['code'] == "0"){
     zpanelx::error("Invoice id was not found",false,true);
} 

elseif($invoice['xmws']['content']['code'] == "1"){
     $inv_user      = $invoice['xmws']['content']['invoice']['user'];
     $desc          = $invoice['xmws']['content']['invoice']['desc'];
     $obj           = json_decode($desc);
     $inv_amount    = $obj->{'price'};
     $user_payperiod= $obj->{'period'};
     $package_id    = $obj->{'pk_id'};
     $inv_status    = $invoice['xmws']['content']['invoice']['status'];
     $inv_id        = $invoice['xmws']['content']['invoice']['id'];
}
else{
     zpanelx::error('Invoice data could not be loaded',false,true);
}
if(!$inv_user){
     zpanelx::error("Invoice id was not found in the system",false,true);
} 
elseif($inv_status == "1"){
     zpanelx::error("This invoice has already been paid.",false,true);
}

$data = "<profile_id>".$inv_user."</profile_id><account_id>".$inv_user."</account_id><payment>1</payment>";
$account = zpanelx::api("reseller_billing", "Pay", $data, zpanelx::getConfig("zpanel_url"), zpanelx::getConfig("api"));
     
     if (!empty($account['xmws']['content']['account']['id'])) {
          $user_alias      = $account['xmws']['content']['account']['alias'];
          $user_id         = $account['xmws']['content']['account']['id'];
          $user_email      = $account['xmws']['content']['account']['email'];
          $payments        = $account['xmws']['content']['payments'];
          $profile_fullname= $account['xmws']['content']['profile']['fullname'];
     } 
     else{
          zpanelx::error("Error getting account data",false,true);
     }

     foreach($payments as $row){
          $paymethod = file_get_contents('templates/paymethod.html');

          $paymethod = str_replace('{{paycode}}',$row['data'],$paymethod);
          $paymethod = str_replace('{{payname}}',$row['name'],$paymethod);
          $paymethods .= $paymethod;
     }
//get the package name
$data = "<pk_id>".$package_id."</pk_id>";
$package = zpanelx::api("reseller_billing", "Package", $data, zpanelx::getConfig("zpanel_url"), zpanelx::getConfig("api"));

if (!empty($package['xmws']['content']['package']['name'])) {
     $package_name    = $package['xmws']['content']['package']['name'];
} 
else{
     zpanelx::error("Error getting package data".$data,false,true);
}

$form = file_get_contents('templates/pay.html');
    
//Add the paymethods, price and title
$form = str_replace('{{payment}}',$paymethods,$form);
$form = str_replace('{{pay}}', zpanelx::getConfig('cs') ." ". $inv_amount, $form);
$form = str_replace('{{package_name}}', $package_name , $form);
$form = str_replace('{{period}}', $user_payperiod , $form);

$action = (zpanelx::getConfig('test')) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
$form = str_replace('{{action}}', $action, $form);
$form = str_replace('{{user_firstname}}', $user['ud_fullname_vc'], $form);
$form = str_replace('{{invoice}}', $token, $form);
$form = str_replace('{{email}}', $user_email, $form);
$form = str_replace('{{return_url}}', zpanelx::getConfig('return_url'), $form);
$form = str_replace('{{business}}', zpanelx::getConfig('email_paypal'), $form);
$form = str_replace('{{item_name}}', $package_name." - ".$user_payperiod." month", $form);//will have the oackage name and period in next release
$form = str_replace('{{country}}', zpanelx::getConfig('country_code'), $form);
$form = str_replace('{{amount}}', $inv_amount, $form);
$form = str_replace('{{logo}}', zpanelx::getConfig('logo'), $form);
$form = str_replace('{{notify_url}}', zpanelx::getConfig('notify_url'), $form);
$form = str_replace('{{cs}}', zpanelx::getConfig('cs'), $form);

$title = "Pay for hosting";
echo zpanelx::template($title, $head, $form);
?>