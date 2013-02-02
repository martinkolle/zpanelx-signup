<?php

class zConfig {

	
	public $test = true; //false for NOT
	public $DEBUG = true; //false disable
	
   	static $zpanel_api = 'API_KEY_HERE'; // API Key can be found in your zpanel database
   	static $zpanel_url = 'localhost'; //or IP/URL of zpanel server
   	public $use_ssl = 'false';
	
    //Database connection
    /* The database connection is only needed if the API key is not set
	 * For security reason the less place you have to have your database credentials in plain text the better
	 * if you do need to use the database connection you will have to uncomment the function at the bottom of this file
	 */ 
   	static $mysql_host = 'localhost';
   	static $mysql_user = 'root';
   	static $mysql_pass = '';
	
	//Recaptcha keys
	public $rc_public_key = ''; //recaptcha public key
	public $rc_private_key = ''; //recaptcha private key

    //Config
   	public $server_cfg;

	//Email settings
	public $error_email = '';
	public $error_emailName = '';


	//Locale Settings
	public $currency_symbol = '$';
	
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

	/*****************************************
	    @Author: Aderemi Adewale (modpluz @ ZPanel Forums)
	    Fetch API Key and Control Panel URL the Old Fashioned way
	 *****************************************/
	 
	/* Function Only needed if API key is not set
	function __construct(){
		if(!class_exists('zServer') && is_file('zserver.php')) {
			require_once('zserver.php');
		}

	    $zServer = new zServer();
	    $server_cfg = $zServer->_getConfig(self::$mysql_host,self::$mysql_user,self::$mysql_pass);
	    if(is_array($server_cfg)){
	        self::$zpanel_api = $server_cfg['api_key'];
	         self::$zpanel_url = $server_cfg['panel_url'];
	    }
	}
	 */


}
