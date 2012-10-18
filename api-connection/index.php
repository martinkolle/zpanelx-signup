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
$id = (isset($_GET['id'])) ? $_GET['id'] : "";
$packetlist = null;

//Get packages from zpanelx
$listPackages = zpanelx::api("reseller_billing", "PackageList", "");
//print_r($listPackages);

if(empty($listPackages)){
    zpanelx::error("No packages where found", false, true);
}

//Check how the array is build. xmws.class is gennerating different arrays based on the number of packages. 
$listPackages = (is_array(isset($listPackages['package'][0]))) ? $listPackages['package'] : $listPackages;
//print_r($listPackages);
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
    if(preg_match('/^\d+$/', $id) && $id == $row['id']){
        $packetlist = str_replace('{{selectedpackage}}'," checked",$packetlist);
    } else {
        $packetlist = str_replace('{{selectedpackage}}',"",$packetlist);
    }
    
    $packetlist = str_replace('{{packagename}}',$row['name'],$packetlist);
    $packetlist = str_replace('{{packageid}}',$row['id'],$packetlist);
    $packetlist = str_replace('{{desc}}',"Prices beginning from " . $price,$packetlist);
}

	$template = file_get_contents('templates/index.html');
    $template = str_replace('{{packageList}}', $packetlist, $template);
	$template = str_replace('{{action}}', "./billing.php", $template);
	$title 	  = "Buy hosting";
	
	$head = '<script type="text/javascript">
window.onload=function(){
    if(window.location.hash) { 
        var pack = document.location.hash.replace("#","");
        if(document.getElementById(pack) != null){
            document.getElementById(pack).checked=true;
        }
    }
}</script>';

//return template
echo zpanelx::template($title, $head, $template);
//print_r(zpanelx::$zerror);
?>