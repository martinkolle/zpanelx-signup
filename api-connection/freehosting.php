<?php

/**
 * Free Hosting Gateway
 *
 * @author Rob Brown
 * @copyright Brownweb
 * @link http://www.www.brownweb.com.au
 * @license GPL (http://www.gnu.org/licenses/gpl.html)
 */
require_once('lib/functions.php');

// STEP 1: Check for invoice number

if (isset($_POST['invoice'])) {

    $freeinvoice = $_POST['invoice'];

    //Check if the invoice id exits or have been paid
    $data = "<token>" . $freeinvoice . "</token>";
    $invoice = zpanelx::api("billing", "Invoice", $data);

    if ($invoice['code'] == "0") {
        zpanelx::error("Invoice id was not found");
    } elseif ($invoice['code'] == "1") {
        $inv_user = $invoice['invoice']['user'];
        $inv_desc = $invoice['invoice']['desc'];
        $inv_amount = $invoice['invoice']['amount'];
        $inv_id = $invoice['invoice']['id'];
        $inv_status = $invoice['invoice']['status'];
    } else {
        zpanelx::error("Invoice data could not be loaded");
    }

    if (!$inv_user) {
        //Forcing to show the error
        zpanelx::error("Invoice id was not found in the system");
    } elseif ($inv_status == "1") {
        //FOrcing to show the error
        zpanelx::error("This invoice has already been processed.");
    }

    $data = "<method>Free Hosting</method><user_id>" . $inv_user . "</user_id><txn_id>Free Hosting</txn_id><token>" . $freeinvoice . "</token>";

    $invoice = zpanelx::api("billing", "Payment", $data);

    switch ($invoice['code']) {
        case "1":
            //Really going to do nothing!
            break;
        case "2":
            zpanelx::error("PAYMENT ERROR: Could not create invoice");
            break;
        case "3":
            zpanelx::error("PAYMENT ERROR: Could not select inv_desc");
            break;
        case "4":
            zpanelx::error("PAYMENT ERROR: Could not activate user");
            break;
        case "5":
            zpanelx::error("PAYMENT ERROR: Could not add to x_rb_billing");
            break;
    }
	
$form = file_get_contents('themes/' . $theme . '/free.tpl');
$form = str_replace('{{user_firstname}}', $_POST['first_name'], $form);

$title = "Thankyou for signing up";
echo zpanelx::template($title, $head, $form);

} else {
    // log for manual investigation
    zpanelx::sendmail($setting['payment.email_error'], 'Invalid Invoice', 'error ' . $freeinvoice);
}

?>