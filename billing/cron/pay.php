<?php

/**
 * @package zpanelx
 * @subpackage modules->billing
 * @author Martin Kollerup
 * @copyright martinkole
 * @link http://www.kmweb.dk/
 * @license GPL (http://www.gnu.org/licenses/gpl.html)
 */

include('../../../cnf/db.php');
include('../../../dryden/db/driver.class.php');
include('../../../dryden/debug/logger.class.php');
include('../../../dryden/runtime/dataobject.class.php');
include('../../../dryden/sys/versions.class.php');
include('../../../dryden/ctrl/options.class.php');
include('../../../dryden/ctrl/auth.class.php');
include('../../../dryden/ctrl/users.class.php');
include('../../../dryden/fs/director.class.php');
include('../../../inc/dbc.inc.php');
require_once('../code/controller.ext.php'); 

class rb_cron {

     /**
     * If an account is going to expire, we need send a invoice to them. 
     */
     static function AccountExpire(){
          global $zdbh;

          //todays date and minus/plus with the settings expire days
          $date = date('Y-m-d');
          $duedate = strtotime ( module_controller::getConfig('user.expire_days')." days" , strtotime ( $date ) ) ;
          $duedate = date ( 'Y-m-d' , $duedate );
          echo $duedate;

          $stmt = $zdbh->prepare("SELECT * FROM x_rb_billing WHERE blg_duedate = ?");
          $stmt->execute(array($duedate));
          $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

          if(!empty($rows)){
               foreach($rows as $row){
                    $type     = "Automatical renew";
                    $token    = module_controller::generateToken();
                    $desc     = $row['blg_desc'];
                    $obj      = json_decode($desc);
                    $pk_id    = $obj->{"pk_id"};
                    $period   = $obj->{"period"};
                    $price    = module_controller::ApiPackage($pk_id);
                    $json     = json_decode($price['pkp_hosting'], true);
                    //print_r($json);
                    foreach($json['hosting'] as $host){
                         if($host['month'] == $period){
                              $price = $host['price'];
                              echo "price:".$price;
                         }
                    }

                    $newDesc  = array('pk_id' => $pk_id, 'period' => $period, 'price' => $price);

                    if(module_controller::ExecuteCreateInvoice($row['blg_user'], $price, $type, json_encode($newDesc), $token, false)){
                         echo "Invoice created for user ".$row['blg_user'];
                    }
               }
          }
     }

     /**
     * Disabling accounts if thay not have been paid.
     */

     static function AccountDisable(){
          global $zdbh;
          $date = date('Y-m-d');
          $disabledate = strtotime ( "-".module_controller::getConfig('user.disable_days')." days" , strtotime ( $date ) ) ;
          $disabledate = date ( 'Y-m-d' , $disabledate );
          echo $disabledate;

          $stmt = $zdbh->prepare("SELECT * FROM x_rb_billing WHERE blg_duedate = ?");
          $stmt->execute(array($disabledate));
          $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
          //print_r($rows);
          if(!empty($rows)){
               foreach($rows as $row){
                    $stmt = $zdbh->prepare("UPDATE x_accounts SET ac_enabled_in = '0' WHERE ac_id_pk= :user_id");
                    
                    if($stmt->execute(array(':user_id'=>$row['blg_user']))){
                         $profile   = module_controller::ApiProfile($user);
                         $email     = module_controller::getMail("user_disabled");
                         $emailtext = $email['message'];
                         $emailtext = str_replace('{{fullname}}',$profile['ud_fullname_vc'],$emailtext);
                         $emailtext = str_replace('{{contact_email}}',module_controller::getConfig('email.contact_email'),$emailtext);
                         $emailtext = str_replace('{{firm}}',module_controller::getConfig('system.firm'),$emailtext);
            
                         module_controller::sendemail(module_controller::getUserEmail($user), $email['subject'], $emailtext);

                         echo "Disabled user ".$row['blg_user']."<br />";
                    }
               }
          }

     }
     /**
     * Notification for non paid invoices
     */
     
     static function InvoiceRemind(){
          global $zdbh;
          $date = date('Y-m-d');
          $notify = strtotime (module_controller::getConfig('invoice.notify')." days" , strtotime ( $date ) ) ;
          $notify = date ( 'Y-m-d' , $notify );
          echo $notify;
     
          $stmt = $zdbh->prepare("SELECT * FROM x_rb_invoice WHERE inv_date = ?");
          $stmt->execute(array($notify));
          $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
          //print_r($rows);
          if(!empty($rows)){
               foreach($rows as $row){
                    $user = ctrl_users::GetUserDetail($row['inv_user']);                    
                   
                    if($user){
                         $email     = module_controller::getMail("invoice_notify");
                         $emailtext = $email['message'];
                         $emailtext = str_replace('{{fullname}}',$user['fullname'],$emailtext);
                         $link      = module_controller::getConfig('system.url_billing')."pay.php?id=".$row['inv_token'];
                         $emailtext = str_replace('{{link}}',$link,$emailtext);
                         $emailtext = str_replace('{{firm}}',module_controller::getConfig('system.firm'),$emailtext);
            
                         module_controller::sendemail($user['email'], $email['subject'], $emailtext);
     
                         echo "Disabled user ".$row['blg_user']."<br />";
                    }
               }
          }
     
     }

}

rb_cron::InvoiceRemind();
rb_cron::AccountExpire();
rb_cron::AccountDisable();