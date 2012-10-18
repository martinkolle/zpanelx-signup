<?php
/**
 * Config file for zpanelx signup
 * @author Martinkolle
*/
class zConfig {

	/**
	* Welcome to the config file for Reseller billing API connection
	* Please set this:
	*
	* $error_email
	* $zpanel_api
	* $zpanel_url
	*/

	public $test = true; //false for NOT
	public $DEBUG = true; //false disable

	//Enter your api key! Find with gatekeeper, or in the zpanel_core.x_settings
	public $zpanel_api = 'ee8795c8c53bfdb3b2cc595186b68912';

	//enter url to your zpanel panel
	public $zpanel_url = 'http://panel.local';

	//Email settings
	public $error_email = '';
	public $error_emailName = '';

	/**
	* Using this will override user.reseller_id in reseller_billing. 
	* Only set this variable if you are having multiple sign-up sites 
	* and the users should be assigned to different resellers accounts.
	* Default is 0
	*/
	public $reseller_id = '0';

	/**
	* Using this will override user.groupid in reseller_billing. 
	* Only set this variable if you are having multiple sign-up sites 
	* and the users should be assigned to different groups
	* Default is 0
	*/
	public $group_id = '0';
}