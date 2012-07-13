<?php
/**
 * Config file for zpanelx signup
*/
class zConfig {

	//Settings for the script
	public $test = true; //false for NOT
	public $DEBUG = true; //false disable
	public $billing_url = 'http://billing.yourdomain.dk';
	public $zpanel_url = 'http://zpanelx.yourdomain.dk';
	public $notify_url = 'http://billing.yourdomain.dk/ipn.php';
	public $api = '';// enter your api key! IMPORTANT!§!!! Use Gate Keeper to get it
	public $ns1 = "ns1.yourdns.dk";
	public $ns2 = "ns2.yourdns.dk";

	//Settings for general
	public $firm = 'Your firm.dk';
	public $fromEmailName = 'YOur firm.dk';
	public $fromemail = 'from@email.dk';
	public $contact_email = 'contact@email.dk';

	//Settings for the payment
	public $cs = 'DKK';
	public $country_code = "DK";
	public $logo ="http://yourdomain.dk/logo.png";
	public $invoicedays = '1';
	//the form to paypal will use this email
	public $email_paypal = 'business_email@paypal.com';
	public $email_paypal_error = 'error@yourdomain.dk';
	public $return_url = "http://yourdomain.dk/payment_accepted"; //payment have been received

	//settings for account creation
	public $reseller_id = "1";
	public $groupid = "3"; //the user group 3=users

	//settings for creation when expire
	public $expire_days = '29'; //Send new payment day :day: before expire
	public $disable_days = "4"; //How many days should there go before we disable the account..

	//database settings
	public $dbName = 'zpanel_core';
	public $dbUser = 'root';
	public $dbPass = 'password';
	public $dbHost = 'localhost';
}