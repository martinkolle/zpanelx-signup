<?php
/**
 * Config file for zpanelx signup
*/
class zConfig {
	public $billing_url = 'http://billing.yourdomain.dk';
	public $zpanel_url = 'http://zpanel.yourdomain.dk';
	
	//currency
	public $cs = 'DKK';
	public $invoicedays = '1';

	//The name which the email is from
	public $fromEmailName = 'Yourdomain or name.dk';
	public $fromemail = 'info@yourdomain.dk';

	//seller email - which email is your business account created on.
	public $email_paypal = 'paypal_email@yourdomain.dk';
	//if there is any errors with the payment, will it be send to:
	public $email_paypal_error = 'info@yourdomain.dk';

	//DNS servers
	public $ns1 = "ns1.webglobe.dk";
	public $ns2 = "ns2.webglobe.dk";

	//database settings
	public $dbName = 'zpanel_core';
	public $dbUser = 'root';
	public $dbPass = 'password';
	public $dbHost = 'localhost';
}