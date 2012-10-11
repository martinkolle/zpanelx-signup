<?php

/**
 * List all packages
 *
 * @author MaouKami
 * @copyright MaouKami
 * @link http://www.kmweb.dk/
 * @license GPL (http://www.gnu.org/licenses/gpl.html)
 */
include ('lib/functions.php');

$head = null;

    //Get packages from zpanelx
	$listPackages = zpanelx::api("reseller_billing", "PackageList", "");
	//print_r($listPackages);

    if(empty($listPackages['xmws']['content'])){
        zpanelx::error("No packages where found", false, true);
    }
    //We need to check that the array is the right. xmws.class is gennerating different arrays based on the number of packages. 
    $listPackages = (is_array($listPackages['xmws']['content']['package'][0])) ? $listPackages['xmws']['content']['package'] : $listPackages['xmws']['content'];
    foreach($listPackages as $row){

        //List the prices for the package and find the cheapest
        $json = json_decode($row['hosting'], true);
        $price = array();
        foreach($json['hosting'] as $host){
            array_push($price, $host['price']);
        }
        $price = min($price);
        $packetlist = file_get_contents('templates/packagelist.html');
        
        //If a id have been added to the url it will be checked.
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

	//return template
	echo zpanelx::template($title, $head, $template);
	//print_r(zpanelx::$zerror);
?>