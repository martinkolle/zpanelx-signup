<?php

require_once '/etc/zpanel/panel/dryden/loader.inc.php';
require_once 'cnf/db.php';
require_once 'inc/dbc.inc.php';
require_once 'dryden/ctrl/options.class.php'; 

$db = db::getConnection();

/**
 * if the users hosting period is nearly ranout of time, 
 * 2 we will add a new payment for him/her and send a payment link.
*/
$date = date('Y-m-d');
$duedate = strtotime ( zpanelx::getConfig('expire_days')." days" , strtotime ( $date ) ) ;
$duedate = date ( 'Y-m-d' , $duedate );

echo $duedate;


$stmt = $db->prepare("SELECT * FROM x_accounts WHERE ac_invoice_nextdue= ?");
$stmt->execute(array($duedate));
$rows = $stmt->fetchAll();

if(!empty($rows)){
     foreach($rows as $row){
          $userId = $row['ac_id_pk'];
          $username = $row['ac_user_vc'];
          $period = $row['ac_invoice_period'];
          $package = $row['ac_package_fk'];
          //$fullname = $row['ud_fullname_vc']; LEFT JOIN to sql - i'm tired
          $email = $row['ac_email_vc'];
          $billing_url = zpanelx::getConfig('billing_url');
          define('TOKEN', zpanelx::generateToken());

          //get the mounth
          $stmt = $db->prepare("SELECT * FROM x_packages WHERE pk_id_pk= ?");
               
          $stmt->execute(array($package));
          $row = $stmt->fetch();

          switch($period){
               case '1':
                    $price = $row['pk_price_pm'];
                    $mounth = "3";
               break;
               case '2' :
                    $price = $row['pk_price_pq'];
                    $mounth = "6"; 
               break;
               case'3':
                    $price = $row['pk_price_py'];
                    $mounth = "12";
               break;
          }

          //add to invoice
          $stmt = $db->prepare("INSERT INTO x_invoice(inv_user, inv_amount, inv_description, inv_duedate, inv_createddate, inv_act, token) VALUES (:user_id,:price,'Renew',:todaydate,:todaydate,'1',:token)");
          
          $query = array(':user_id'=>$userId, ':price'=>$price,':todaydate'=>$date, ':todaydate'=>$date, ':token'=>TOKEN);
          
          if(!$stmt->execute($query)){
               echo $stmt->errorInfo();
          }
          $link = zpanelx::getConfig('billing_url')."/pay.php?id=".TOKEN;
          $emailtext = file_get_contents("../templates/emails/user_exp.html");
          $emailtext = str_replace(':fullname',$username,$emailtext);
          $emailtext = str_replace(':firm',zpanelx::getConfig('firm'),$emailtext);
          $emailtext = str_replace(':mounth',$mounth,$emailtext);
		  $emailtext = str_replace(':days',zpanelx::getConfig('expire_days'),$emailtext);
          $emailtext = str_replace(':link',$link,$emailtext);
          $emailtext = str_replace(':firm',zpanelx::getConfig('firm'),$emailtext);

          zpanelx::sendemail($email, "Account will expire", $emailtext);
          echo $emailtext."<br />";
     }
}
else{
     echo "kagmand";
}

/**
 * Disable accounts which not have been payed.
 * Will send a mail that they have been disable and link to invoice if exits.
*/

$date = date('Y-m-d');
$disabledate = strtotime ( "-".zpanelx::getConfig('disable_days')." days" , strtotime ( $date ) ) ;
$disabledate = date ( 'Y-m-d' , $disabledate );
echo $disabledate;

$stmt = $db->prepare("SELECT * FROM x_accounts WHERE ac_invoice_nextdue= ?");
$stmt->execute(array($disabledate));
$rows = $stmt->fetchAll();

if(!empty($rows)){
	foreach($rows as $row){
		$sql = "UPDATE x_accounts SET ac_enabled_in = '0' WHERE ac_id_pk= :user_id";
		$query = $db->prepare($sql);
		
		if(!$query->execute(array(':user_id'=>$row['ac_id_pk'])))
		{
			echo $query->errorInfo();
		}

		$stmt = $db->prepare("SELECT * FROM x_invoice WHERE inv_user= :user_id AND inv_description = :desc");
		$stmt->execute(array(':user_id'=>$row['ac_id_pk'], ':desc'=>'Renew'));
		$inv = $stmt->fetch();

		//If there are a invoice in the database, make a link to this
		if(!empty($inv)){
			$link = zpanelx::getConfig('billing_url')."/pay.php?id=".$inv['token'];
			$emailtext = file_get_contents("../templates/emails/user_disabled.html");
			$emailtext = str_replace(':fullname',$row['ac_user_vc'],$emailtext);
			$emailtext = str_replace(':firm',zpanelx::getConfig('firm'),$emailtext);
			$emailtext = str_replace(':pay',$link,$emailtext);
			$emailtext = str_replace(':regards',zpanelx::getConfig('firm'),$emailtext);

			zpanelx::sendemail($row['ac_email_vc'], "Account have been disabled", $emailtext);
			echo $emailtext."<br />";
			echo $row['ac_email_vc'];
		}
		else{
			//there have not been made a invoice.. just say they can contact us..
			//TODO: Maybe add a invoice instead of say that they chould contact.. 
			$emailtext = file_get_contents("../templates/emails/user_disabled_no_invoice.html");
			$emailtext = str_replace(':fullname',$row['ac_user_vc'],$emailtext);
			$emailtext = str_replace(':firm',zpanelx::getConfig('firm'),$emailtext);
			$emailtext = str_replace(':contact_mail',zpanelx::getConfig('contact_email'),$emailtext);
			$emailtext = str_replace(':regards',zpanelx::getConfig('firm'),$emailtext);

			zpanelx::sendemail($row['ac_email_vc'], "Account have been disabled", $emailtext);
		}
	}
}

$db = null;