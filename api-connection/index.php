<?php

/**
 * List all packages
 *
 * @author MaouKami
 * @copyright Martinkolle
 * @link http://www.kmweb.dk/
 * @license GPL (http://www.gnu.org/licenses/gpl.html)
 */
error_reporting(E_ERROR);
require_once('lib/functions.php');

$head = null;
$id = (isset($_GET['id'])) ? $_GET['id'] : "";
$packetlist = null;
//Get packages from zpanelx
$listPackages = zpanelx::api("billing", "PackageList", "");

if (empty($listPackages)) {
    zpanelx::error("No packages where found", false, true);
}

//Check how the array is build. xmws.class is gennerating different arrays based on the number of packages. 
$listPackages = (isset($listPackages['package'][0])) ? $listPackages['package'] : $listPackages;
$list = null;

foreach ($listPackages as $row) {

    //List the prices for the package and find the cheapest
    $json = json_decode($row['hosting'], true);
    $price = array();
    foreach ($json['hosting'] as $host) {
        array_push($price, $host['price']);
    }
    $price = min($price);
    $price = zpanelx::getConfig('currency_symbol') . $price;
    $packetlist = file_get_contents('themes/packagelist.tpl');

    //If a id have been added to the url it will be checked.
    if (preg_match('/^\d+$/', $id) && $id == $row['id']) {
        $packetlist = str_replace('{{selectedpackage}}', " checked", $packetlist);
    } else {
        $packetlist = str_replace('{{selectedpackage}}', "", $packetlist);
    }

    $packetlist = str_replace('{{packagename}}', $row['name'], $packetlist);
    $packetlist = str_replace('{{packageid}}', $row['id'], $packetlist);
    $packetlist = str_replace('{{price}}', "Prices beginning from " . $price, $packetlist);
    $list .= $packetlist;
}

$template = file_get_contents('themes/index.tpl');
$template = str_replace('{{packageList}}', $list, $template);
$template = str_replace('{{action}}', "./billing.php", $template);
$title = "Buy Hosting";

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
print_r(zpanelx::$zerror);
?>
