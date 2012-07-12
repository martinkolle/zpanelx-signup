<?php

/**
 *  Pay-page for zpanelx Auto-sign-up
 *  
 *  @package    Zpanelx Auto-sign-up
 *  @author     Tony Maclennan & Martin Kollerup
 *  @license    http://opensource.org/licenses/gpl-3.0.html
 */

include ('lib/functions.php');
include ('lib/xmwsclient.class.php');

$token = $_GET['id'];
$error = array();
$html = array();
$html['title'] = "Pay for hosting";

//is the token in the url
if(!isset($token)){
     $error[] = "No invoice id have been set";
}

if(preg_match("/^[a-zA-Z0-9]+$/", $token) == TRUE){

     $xmws = new xmwsclient();
     $xmws->InitRequest(zpanelx::getConfig('zpanel_url'), 'reseller_billing', 'Invoice', zpanelx::getConfig('api'));
     $xmws->SetRequestData('<token>'.$token.'</token>');
     $return = $xmws->XMLDataToArray($xmws->Request($xmws->BuildRequest()), 0);
     
     if (!empty($return['xmws']['content']['invoice']['id'])) {
          $inv_user      = $return['xmws']['content']['invoice']['user'];
          $inv_amount    = $return['xmws']['content']['invoice']['amount'];
          $inv_paid      = $return['xmws']['content']['invoice']['payment_id'];
          $inv_id        = $return['xmws']['content']['invoice']['id'];
     } 
     else{
          if(zpanelx::getConfig('DEBUG')){
               $error[] = "Error getting invoice data";
          }
     }
} else {
     $error[] = "Invoice token is invalid";
}


if(empty($error)){
     if(!$inv_user){
          //we don't need to load the page right now
          $error[] = "Invoice id was not found in the system."; 
     } 
     elseif($inv_paid != 'no'){
          $error[] = "This invoice has already been paid.";
     }
}

if(empty($error)){

     $xmws = new xmwsclient();
     $xmws->InitRequest(zpanelx::getConfig('zpanel_url'), 'reseller_billing', 'Pay', zpanelx::getConfig('api'));
     $xmws->SetRequestData('<profile_id>'.$inv_user.'</profile_id><account_id>'.$inv_user.'</account_id><payment>1</payment>');
     $return = $xmws->XMLDataToArray($xmws->Request($xmws->BuildRequest()), 0);
     
     if (!empty($return['xmws']['content'])) {
          $user_alias      = $return['xmws']['content']['account']['alias'];
          $user_id         = $return['xmws']['content']['account']['id'];
          $user_email      = $return['xmws']['content']['account']['email'];
          $user_payperiod  = $return['xmws']['content']['account']['payperiod'];

          $payments        = $return['xmws']['content']['payments']['payment'];

          $profile_fullname= $return['xmws']['content']['profile']['fullname'];
     } 
     else{
          if(zpanelx::getConfig('DEBUG')){
               $error[] = "Error getting account data";
          }
     }

     //Generate the payment page
     //TODO: new design - please!!
     $template = file_get_contents('templates/paymethod.html');
     foreach($payments as $row){
          $pmtemp = $template;
          $pmtemp = str_replace('{{paycode}}',$row['data'],$pmtemp);
          $pmtemp = str_replace('{{payname}}',$row['name'],$pmtemp);
          $form .= $pmtemp;
     }
 
     $action = (zpanelx::getConfig('test')) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
     $form = str_replace('{{action}}', $action, $form);
     $form = str_replace('{{user_firstname}}', $user['ud_fullname_vc'], $form);
     $form = str_replace('{{invoice}}', $token, $form);
     $form = str_replace('{{email}}', $user_email, $form);
     $form = str_replace('{{return_url}}', zpanelx::getConfig('return_url'), $form);
     $form = str_replace('{{business}}', zpanelx::getConfig('email_paypal'), $form);
     $form = str_replace('{{item_name}}', "Webhosting", $form);//will have the oackage name and period in next release
     $form = str_replace('{{country}}', zpanelx::getConfig('country_code'), $form);
     $form = str_replace('{{amount}}', $inv_amount, $form);
     $form = str_replace('{{logo}}', zpanelx::getConfig('logo'), $form);
     $form = str_replace('{{notify_url}}', zpanelx::getConfig('notify_url'), $form);
     $form = str_replace('{{cs}}', zpanelx::getConfig('cs'), $form);

     $html['pay'] .= "Paying today: " . zpanelx::getConfig('cs') ." ". $inv_amount;
     $html['body'] .= $form; 
     $html['title'] = "Pay for hosting";
}//end if no error

     $html['body'] .= implode("<br/>", $error);
     $template = file_get_contents('templates/pay.html');
     $template = str_replace('{{title}}', $html['title'], $template);
     $template = str_replace('{{body}}', $html['body'], $template);
     $template = str_replace('{{pay}}', $html['pay'], $template);

     echo $template;
?>