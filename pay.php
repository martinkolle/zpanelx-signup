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

//connect to the databse
$db = db::getConnection();
$stmt = $db->prepare("SELECT * FROM x_invoice WHERE token= ?");
    
$stmt->execute(array($token));
$row = $stmt->fetch();
$invpaid = $row['inv_payment_method'];
$amount = $row['inv_amount'];
$invoiceuser = $row['inv_user'];


if(!isset($token)){
     $error[] = "No payment id have been set";
}

//if the invoice have been paid - return this.
if (!$invpaid == 'no') {
     $error[] = "This invoice has already been paid.";
}

if(empty($error)){

     $stmt = $db->prepare("SELECT * FROM x_accounts WHERE ac_id_pk= ?");
    
     if($stmt->execute(array($invoiceuser))){
          $row = $stmt->fetch();
          $useralias = $row['ac_user_vc'];
          $userpayperiod = $row['ac_invoice_period'];
     }    

     //Load payment methods
     $pmtemplate = file_get_contents('templates/pay_paymentmethod_template.html');

     $stmt = $db->prepare("SELECT * FROM x_payment_methods WHERE pm_active='1'");
         
     if($stmt->execute()){
          foreach($stmt->fetchAll() as $row){
               $pmtemp = $pmtemplate;
               $pmtemp = str_replace('$paycode',$row['pm_data'],$pmtemp);
               $pmtemp = str_replace('$payname',$row['pm_name'],$pmtemp);
               
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

     $accdat = str_replace('$userid', $useralias, $accdat);
     $accdat = str_replace('$itmvalue', $amount, $accdat);
     $accdat = str_replace('$invid', $token, $accdat);
     $accdat = str_replace('$cs', zpanelx::getConfig('cs'), $accdat);

     $html['pay'] .= "Paying today: " . $cs . $amount;
     $html['body'] .= $accdat; 
     $html['title'] = "Pay for hosting";
}//end if no error

     if ($accstatus =="0") {
          include 'templates/pay_acc_inactive.html';
     }
     $html['body'] = implode("<br/>", $error);
     $template = file_get_contents('templates/pay.html');
     $template = str_replace(':title', $html['title'], $template);
     $template = str_replace(':pay', $html['pay'], $template);
     $template = str_replace(':body', $accdat, $template);

     echo $template;
?>