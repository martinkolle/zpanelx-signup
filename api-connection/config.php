<?php

class zConfig {

	
    public $test = false; //true will enable Paypal Sandbox
    public $DEBUG = false; //false disable
	
   	static $zpanel_api = ''; // API Key can be found in your zpanel database
   	static $zpanel_url = ''; //or IP/URL of zpanel server
   	public $use_ssl = 'false';
	
    public $theme = 'default'; //themes are in a subdirectory in the themes folder
    public $control_panel = ''; //external url to your Zpanel installation you have to include 'http://' or 'https://'
    public $webmail_url = ''; //external url to your Webmail you have to include 'http://' or 'https://'
    public $company = ''; //Your webhosting Comany Name
	
    //Recaptcha keys
    public $rc_public_key = ''; //recaptcha public key
    public $rc_private_key = ''; //recaptcha private key

    //Config
   	public $server_cfg;

    //Email settings
    public $error_email = '';
    public $error_emailName = ''; // Displayed Name on the error email

    //Locale Settings
    public $currency_symbol = '$'; //for Pounds use '&pound;' , for Yen use '&yen;' , for Euro use '&euro;'
    
    /**
    * Using this will override user.reseller_id in reseller_billing. 
    * Only set this variable if you are having multiple sign-up sites 
    * and the users should be assigned to different resellers accounts.
    * Disabled is 0
    */
    public $reseller_id = '0';

    /**
    * Using this will override user.groupid in reseller_billing. 
    * Only set this variable if you are having multiple sign-up sites 
    * and the users should be assigned to different groups
    * Disabled is 0
    */
    public $group_id = '0';

}