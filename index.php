<?php

/**
 *  Main page zpanelx Auto-sign-up
 *  
 *  @package    Zpanelx Auto-sign-up
 *  @author     MaouKami
 *  @license    http://opensource.org/licenses/gpl-3.0.html
 */

include ('lib/functions.php');
include ('lib/xmwsclient.class.php');

$head = null;

	$listPackages = zpanelx::api("reseller_billing", "PackageList", "", zpanelx::getConfig('zpanel_url'), zpanelx::getConfig('api'));
    print_r($listPackages);
    $listPackages = (is_array($listPackages['xmws']['content']['package'][0])) ? $listPackages['xmws']['content']['package'] : $listPackages['xmws']['content'];
    foreach($listPackages as $row){

        $json = json_decode($row['hosting'], true);
        $price = array();
        foreach($json['hosting'] as $host){
            array_push($price, $host['price']);
        }
        $price = min($price);

          $packetlist = file_get_contents('templates/packagelist.html');
          if(preg_match('/^\d+$/', $_GET['id']) && $_GET['id'] == $row['id']){
            $packetlist = str_replace('{{selectedpackage}}'," checked",$packetlist);
          } else {
             $packetlist = str_replace('{{selectedpackage}}',"",$packetlist);
          }
          $packetlist = str_replace('{{packagename}}',$row['name'],$packetlist);
          $packetlist = str_replace('{{packageid}}',$row['id'],$packetlist);
          $packetlist = str_replace('{{desc}}',"Prices beginning from " . $price,$packetlist);
          $listPackage .= $packetlist;
     }

	$template = file_get_contents('templates/index.html');
    $template = str_replace('{{packageList}}', $listPackage, $template);
	$template = str_replace('{{action}}', "./billing.php", $template);
	$title 	  = "Buy hosting";

	//Echo the template
	echo zpanelx::template($title, $head, $template);
	//print_r(zpanelx::$zerror);
?>