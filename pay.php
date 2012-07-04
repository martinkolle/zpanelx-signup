<?php

/**
 *  Functions for zpanelx Auto-sign-up
 *  
 *  @package    Zpanelx Auto-sign-up
 *  @author     Tony Maclennan & Martin Kollerup
 *  @license    http://opensource.org/licenses/gpl-3.0.html
 */

include ('db.php');
include ('config/functions.php');

$token = $_GET['id'];
$error = array();
$html = array();
$html['title'] = "Pay for hosting";

//is the token in the url
if(!isset($token)){
     $error[] = "No invoice id have been set";
}

//connect to the databse
$db = db::getConnection();
$stmt = $db->prepare("SELECT * FROM x_invoice WHERE token= ?");
    
$stmt->execute(array($token));
$row = $stmt->fetch();
$invpaid = $row['inv_payment_id'];
$amount = $row['inv_amount'];
$invoiceuser = $row['inv_user'];

if(!$row['token']){
     //we don't need to load the page right now
     die("Invoice id was not found in the system."); 
}
//if the invoice have been paid - return this.
if ($invpaid != 'no') {
     $error[] = "This invoice has already been paid.";
}

if(empty($error)){

     $stmt = $db->prepare("SELECT * FROM x_accounts WHERE ac_id_pk= ?");
    
     $stmt->execute(array($invoiceuser));
     $row = $stmt->fetch();
     
     $useralias = $row['ac_user_vc'];
     $user_id = $row['ac_id_pk'];
     $user_email = $row['ac_email_vc'];
     $userpayperiod = $row['ac_invoice_period'];     

     //Load payment methods
     $pmtemplate = file_get_contents('templates/paymethod.html');
     $stmt = $db->prepare("SELECT * FROM x_payment_methods WHERE pm_active='1'");
              
     if($stmt->execute()){
          foreach($stmt->fetchAll() as $row){
               $pmtemp = $pmtemplate;
               $pmtemp = str_replace('{{paycode}}',$row['pm_data'],$pmtemp);
               $pmtemp = str_replace('{{payname}}',$row['pm_name'],$pmtemp);
                    
                    //incase it's a subscription, enter subsequent payment periods
               if ($userpayperiod == "1") {
                    $pmtemp = str_replace('$subscrper','1',$pmtemp);
               } elseif ($userpayperiod == "2") {
                    $pmtemp = str_replace('$subscrper','3',$pmtemp);
               } elseif ($userpayperiod == "3") {
                    $pmtemp = str_replace('$subscrper','12',$pmtemp);
               }
               $accdat .= $pmtemp;
          }
     }
     $stmt = $db->prepare("SELECT * FROM x_profiles WHERE ud_user_fk= ?");
     $stmt->execute(array($user_id));
     $user = $stmt->fetch();


     $action = (zpanelx::getConfig('test')) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
     $accdat = str_replace('{{action}}', $action, $accdat);
     $accdat = str_replace('{{user_firstname}}', $user['ud_fullname_vc'], $accdat);
     $accdat = str_replace('{{invoice}}', $token, $accdat);
     $accdat = str_replace('{{email}}', $user_email, $accdat);
     $accdat = str_replace('{{return_url}}', zpanelx::getConfig('return_url'), $accdat);
     $accdat = str_replace('{{business}}', zpanelx::getConfig('email_paypal'), $accdat);
     $accdat = str_replace('{{item_name}}', "Webhosting", $accdat);//will have the oackage name and period in next release
     $accdat = str_replace('{{country}}', zpanelx::getConfig('country_code'), $accdat);
     $accdat = str_replace('{{amount}}', $amount, $accdat);
     $accdat = str_replace('{{logo}}', zpanelx::getConfig('logo'), $accdat);
     $accdat = str_replace('{{notify_url}}', zpanelx::getConfig('notify_url'), $accdat);
     $accdat = str_replace('{{cs}}', zpanelx::getConfig('cs'), $accdat);

          $html['pay'] .= "Paying today: " . zpanelx::getConfig('cs') ." ". $amount;
          $html['body'] .= $accdat; 
          $html['title'] = "Pay for hosting";
}//end if no error

          if ($accstatus =="0") {
               include 'templates/pay_acc_inactive.html';
          }
          $html['body'] .= implode("<br/>", $error);
          $template = file_get_contents('templates/pay.html');
          $template = str_replace('{{title}}', $html['title'], $template);
          $template = str_replace('{{body}}', $html['body'], $template);
          $template = str_replace('{{pay}}', $html['pay'], $template);


          echo $template;

?>