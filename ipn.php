<?php
/**
 *  @package    Zpanelx Auto-sign-up
 *  @author     Tony Maclennan & Martin Kollerup
 *  @license    http://opensource.org/licenses/gpl-3.0.html
 */

/**
 *
 * This a simple ipn for paypal
 * @link build on http://www.micahcarrick.com/paypal-ipn-with-php.html
*/
 
error_reporting(E_ALL);
ini_set('log_errors', true);
ini_set('error_log', dirname(__FILE__).'/ipn_errors.log');


include('db.php');
include('config/functions.php');

// instantiate the IpnListener class
include('ipnlistener.php');
$listener = new IpnListener();

//testing = true
$listener->use_sandbox = zpanelx::getConfig('test');

/*
To post over standard HTTP connection, use:
$listener->use_ssl = false;

To post using the fsockopen() function rather than cURL, use:
$listener->use_curl = false;
*/

//send request to paypal and will verify
try {
    $listener->requirePostMethod();
    $verified = $listener->processIpn();
} catch (Exception $e) {
    error_log($e->getMessage());
    exit(0);
}


/*
The processIpn() method returned true if the IPN was "VERIFIED" and false if it
was "INVALID".
*/
if ($verified) {

    $error = array();
    //connect to the databse
    $pdo = db::getConnection();

    $item_name = $_POST['item_name'];
    $invoice = $_POST['invoice'];
    $payment_status = $_POST['payment_status'];
    $payment_amount = $_POST['mc_gross'];
    $payment_currency = $_POST['mc_currency'];
    $txn_id = $_POST['txn_id'];
    $receiver_email = $_POST['receiver_email'];
    $payer_email = $_POST['payer_email'];

    //Check that the invoice is here.
    $stmt = $pdo->prepare("SELECT * FROM x_invoice WHERE token = ?");
    if($stmt->execute(array($invoice)))
    {
        $row = $stmt->fetch();   
        $invoiceamount = $row['inv_amount'];
        $invoiceaction = $row['inv_act'];
        $invoiceuserid = $row['inv_user'];
    }
    else{
        //we need both to add it to the log and report to the seller
        error_log($stmt->errorInfo());
        $error[] .= print_r($stmt->errorInfo());
    }

    if(empty($error)){
        //Do the user have paid the same as we want!?
        //TODO: Add something with tax
        if ($invoiceamount != $_POST['mc_gross']) {
            $error[] .= "INVALID PAYMENT: ".$invoice." (invoice number) - ".$payment_amount." (payment received) - ".$invoiceamount." (invoice amount)";  
        }

        // Set that we have received the payment from paypal
        $sql = "UPDATE x_invoice SET inv_payment_method = 'PayPal', inv_payment_id = :txn_id WHERE token = :invoice";
        $query = $pdo->prepare($sql);
        
        if(!$query->execute(array(':txn_id'=>$txn_id, ':invoice'=>$invoice)))
        {
            //we need both to add it to the log and report to the seller
            error_log($query->errorInfo());
            $error[] = $query->errorInfo();
        }
            //if the amount not is correct, the account will not be disabled. 
           if(empty($error)){ 

                //Update the hosting time - when should the user expire
                $stmt = $pdo->prepare("SELECT * FROM x_accounts WHERE ?");
                if($stmt->execute(array($invoiceuserid))){
                    $row = $stmt->fetch();   

                    switch($row['ac_invoice_period']){
                        case '1':
                            $hostingTime = "3"; //month
                        break;
                        case '2' :
                            $hostingTime = "6"; //month
                        break;
                        case'3':
                            $hostingTime = "12"; //month
                        break;
                    }
                else{
                    error_log($stmt->errorInfo());
                    $error[] .= print_r($stmt->errorInfo());
                }

                $date = date('Y-m-d');
                $nextdue = strtotime ( $hostingTime." month" , strtotime ( $date ) ) ;
                $nextdue = date ( 'Y-m-d' , $nextdue );

                //activate the account
                $sql = "UPDATE x_accounts SET ac_enabled_in = '1', ac_invoice_nextdue = :nextdue WHERE ac_id_pk= :user_id";
                $query = $pdo->prepare($sql);
                if(!$query->execute(array(':nextdue'=>$nextdue,':user_id'=>$invoiceuserid)))
                {
                    error_log($query->errorInfo());
                    $error[] = $query->errorInfo();
                }
            }
    }
    else{
        $error[] .= "A invoice, which allready exits have been tried to be paid. The invoice is: ". $invoice;
    }

    if(!empty($error)){
        zpanelx::sendemail(zpanelx::getConfig('email_paypal_error'),"Invalid payment received", 
            implode('<br />',$error)."<br />".$listener->getTextReport());
    }
    $db = null;//close pdo connection

} else {
    //there have been some problem with the Payment.. There have been sent a report to the admin.
    zpanelx::sendmail(zpanelx::getConfig('email_paypal_error'), 'Invalid IPN', $listener->getTextReport());
}

?>
