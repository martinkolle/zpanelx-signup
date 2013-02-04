<?php

/**
 * Pay page for ZPX billing API integration 
 *
 * @author Martin Kollerup
 * @copyright martinkole
 * @link http://www.kmweb.dk/
 * @license GPL (http://www.gnu.org/licenses/gpl.html)
 */
require_once ('lib/functions.php');

$head = null;
$token = isset($_GET['id']) ? $_GET['id'] : "";

$theme = zpanelx::getConfig('theme');
//Are there a id
if (empty($token)) {
    zpanelx::error("No package selected", false, true);
}
//And is it in digits only?
else if (!preg_match("/^[a-zA-Z0-9]+$/", $token)) {
    zpanelx::error("Payment token invalid", false, true);
}

$data = "<token>" . $token . "</token>";
$invoice = zpanelx::api("billing", "Invoice", $data);

if ($invoice['code'] == "0") {
    zpanelx::error("Invoice id was not found", false, true);
} elseif ($invoice['code'] == "1") {
    $inv_user = $invoice['invoice']['user'];
    $desc = $invoice['invoice']['desc'];
    $obj = json_decode($desc);
    $inv_amount = $obj->{'price'};
    $user_payperiod = $obj->{'period'};
    $package_id = $obj->{'pk_id'};
    $inv_status = $invoice['invoice']['status'];
    $inv_id = $invoice['invoice']['id'];
} else {
    zpanelx::error('Invoice data could not be loaded', false, true);
}
if (!$inv_user) {
    zpanelx::error("Invoice id was not found in the system", false, true);
} elseif ($inv_status == "1") {
    zpanelx::error("This invoice has already been paid.", false, true);
}

$data = "<profile_id>" . $inv_user . "</profile_id><account_id>" . $inv_user . "</account_id><payment>1</payment>";
$account = zpanelx::api("billing", "Pay", $data);

if (!empty($account['account']['id'])) {
    $user_alias = $account['account']['alias'];
    $user_id = $account['account']['id'];
    $user_email = $account['account']['email'];
    $payments = $account['payments'];
    $profile_fullname = $account['profile']['fullname'];
} else {
    zpanelx::error("Error getting account data", false, true);
}

//Check if we have more than one payment method we have different arrays
$payments = (is_array(isset($payments['payment'][0]))) ? $payments['payment'] : $payments;
$paymethods = null;

foreach ($payments as $row) {
    $paymethod = file_get_contents('themes/' . $theme . 'paymethod.tpl');

    $paymethod = str_replace('{{paycode}}', $row['data'], $paymethod);
    $paymethod = str_replace('{{payname}}', $row['name'], $paymethod);
    $paymethods .= $paymethod;
}

//get the package name
$data = "<pk_id>" . $package_id . "</pk_id>";
$package = zpanelx::api("billing", "Package", $data);

if (!empty($package['package']['name'])) {
    $package_name = $package['package']['name'];
} else {
    zpanelx::error("Error getting package data" . $data, false, true);
}

$form = file_get_contents('themes/' . $theme . 'pay.tpl');

//Add the paymethods, price and title
$form = str_replace('{{payment}}', $paymethods, $form);
$form = str_replace('{{pay}}', zpanelx::getConfig('currency_symbol') . $inv_amount, $form);
$form = str_replace('{{package_name}}', $package_name, $form);
$form = str_replace('{{period}}', $user_payperiod, $form);

$action = (zpanelx::getConfig('test')) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
$form = str_replace('{{action}}', $action, $form);
$form = str_replace('{{user_firstname}}', $profile_fullname, $form);
$form = str_replace('{{invoice}}', $token, $form);
$form = str_replace('{{email}}', $user_email, $form);
$form = str_replace('{{item_name}}', $package_name . " - " . $user_payperiod . " month", $form);
$form = str_replace('{{amount}}', $inv_amount, $form);

$title = "Pay for hosting";
echo zpanelx::template($title, $head, $form);
?>