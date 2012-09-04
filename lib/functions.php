<?php

/**
 *  Functions for zpanelx Auto-sign-up
 *  
 *  @package    Zpanelx Auto-sign-up
 *  @author     Tony Maclennan & Martin Kollerup
 *  @license    http://opensource.org/licenses/gpl-3.0.html
 */

class zpanelx{
	static $newUserError;
	static $token;
	static $zerror;

	/**
	* PHP mail function to send mail in UTF-8.
	* @return true on success and false on fail
	* @author Martinkolle
	*/
	function sendemail($emailto, $emailsubject, $emailbody) {

		$fromEmail = self::getConfig('fromemail');
		$fromEmailName = self::getConfig('fromEmailName');
		$message = $emailbody;
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
		$headers .= "From: ". $fromEmailName ." <". $fromEmail .">\r\n";

		$send = mail($emailto,$emailsubject,$emailbody,$headers);

		return ($send) ? true : false;
	} 

	/**
		* Send email when we have accepted the payment.
		* TODO: add it to the code (ipn)
		* @author Tony
		* @return true or false 
	*/

	function sendWelcomeMail($id){

		$db = $db->getConnection();
		$stmt = $db->prepare("SELECT * FROM x_accounts WHERE ac_id_pk=?");
		$stmt->execute(array($id));
		$row = $stmt->fetch();

		$body = file_get_contents('../templates/emails/user_welcome-email.html');
		$body = str_replace('$username',$row['ac_user_vc'],$body);
		$body = str_replace('$cpurl',self::getCongfig('zpanel_url'),$body);
		$body = str_replace('$ns1',self::getConfig('ns1'),$body);				
		$body = str_replace('$ns2',self::getConfig('ns2'),$body);
		$toemail = $row['ac_email_vc'];
		
		if(self::sendemail($toemail,"Welcome",$body)){
			return true;
		}
		else{
			return false;
		}
	}

	/**
	    * Generate a password for the payer.
	    * There is fallback to mt:rand() if openssl not is supported
	    * @link http://www.php.net/manual/en/function.openssl-random-pseudo-bytes.php#96812
	    * @return password
	*/
	function generatePassword($length = 8) {
	        if(function_exists('openssl_random_pseudo_bytes')) {
	            $password = base64_encode(openssl_random_pseudo_bytes($length, $strong));
	            if($strong == TRUE)
	                return substr($password, 0, $length); //base64 is about 33% longer, so we need to truncate the result
	        }
	        //fall back to mt_rand
	        $characters = '0123456789';
	        $characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz/&'; 
	        $charactersLength = strlen($characters)-1;
	        $password = '';

	        //select some random characters
	        for ($i = 0; $i < $length; $i++) {
	            $password .= $characters[mt_rand(0, $charactersLength)];
	        }        
	        return $password;
	}

	/**
	    * Generate the token the payment should be specified with.
	    * @link http://www.php.net/manual/en/function.openssl-random-pseudo-bytes.php#96812
	    * @return token
	*/
	function generateToken($length = 24) {
		$characters = '0123456789';
		$characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; 
		$charactersLength = strlen($characters)-1;
		$token = '';
		//select some random characters
		for ($i = 0; $i < $length; $i++) {
			$token .= $characters[mt_rand(0, $charactersLength)];
		}        
		return $token;
	}

	function packagePrice($period){
		switch($period){
				case '1':
					$time 	= "3";
				break;
				case '2':
					$time 	= "6";
				break;
				case '3';
					$time 	= "12";
				break;
			}
		return $time;
	}

	/**
	* Create the client through XMWS
	* @author Martinkolle
	* @return redirect on success else add error
	*/
	function addUser($payPeriod, $packageId, $token, $password, $username, $email, $fullname, $address, $postcode, $telephone, $website, $website_help){

		$data = "<pk_id>".$packageId."</pk_id>";
		$package = self::api("reseller_billing", "Package", $data, self::getConfig('zpanel_url'), self::getConfig('api'));

		if (!empty($package['xmws']['content']['package']['id'])) {	

			$package_name 	= $package['xmws']['content']['package']['name'];
			$hosting 		= $package['xmws']['content']['package']['hosting'];
			$domain 		= $package['xmws']['content']['package']['domain'];

			$json = json_decode($hosting, true);
			$packagePrice = null;
			foreach($json['hosting'] as $key=>$host){
				if($host['month'] == $payPeriod){
					$packagePrice = $host['price'];
				}
			}
		}
		else {
			self::error("Error getting package data: function addUser", true);
			return false;
		}

		$data = '
			<resellerid>'.self::getConfig('reseller_id').'</resellerid>
	        <username>'.$username.'</username>
	        <packageid>'.$packageId.'</packageid>
	        <groupid>'.self::getConfig('groupid').'</groupid>
	        <fullname>'.$fullname.'</fullname>
	        <email>'.$email.'</email>
	        <postcode>'.$postcode.'</postcode>
	        <address>'.$address.'</address>
	        <phone>'.$telephone.'</phone>
	        <password>'.$password.'</password>';

		$addUser 		= self::api("reseller_billing", "CreateClient", $data, self::getConfig('zpanel_url'), self::getConfig('api'));

		if($addUser['xmws']['content']['code'] == "1"){
			
			$userId 	= $addUser['xmws']['content']['uid'];
			$todaydate 	= date("Y-m-d");// current date
			$newdate 	= strtotime(date("Y-m-d", strtotime($todaydate)) . $hostingTime." month");
			$newdate 	= date('Y-m-d', $newdate);
			
			$desc = array('pk_name'=>$package_name, 'price'=>$packagePrice, 'period'=>$payPeriod, 'domain'=>$website, 'web_help'=>$website_help);
			$desc = json_decode($desc);
			$data = "<user_id>".$userId."</user_id>
					<amount>".$packagePrice."</amount>
					<type>Initial Signup</type>
					<desc>".$desc."</desc>
					<token>".$token."</token>";
			$addInvoice = self::api("reseller_billing", "CreateInvoice", $data, self::getConfig('zpanel_url'), self::getConfig('api'));

			if($addInvoice['xmws']['content']['code'] == "1"){
				$emailtext = file_get_contents("templates/emails/user_reg.html");
				$emailtext = str_replace('$fullname',$fullname,$emailtext);
				$emailtext = str_replace('$pathto',self::getConfig('billing_url'),$emailtext);
				$emailtext = str_replace('$invid',$token,$emailtext);
				$emailtext = str_replace('$userid',$username,$emailtext);
				$emailtext = str_replace('$password',$password,$emailtext);
				
				//send a email to the user that they have been created.
				self::sendemail($email, "New Account Created", $emailtext);

				header('Location: pay.php?id='.$token);
			} else{
				zpanelx::error("Error creating invoice");
				self::sendemail(self::getConfig('email_paypal_error'), "Error creating invoice", "The invoice have not been created for user: ".$username );
			}
		} else{
			zpanelx::error("Error creating account");
			self::sendemail(self::getConfig('email_paypal_error'), "Error creating account", "A new account have tried to be created, but failed");	
		}
	}

	/**
	 * Get the config values
	 * @copyright Copyright (c)2009-2012 Nicholas K. Dionysopoulos
	*/
	public static function getConfig( $key, $default = null )
	{
		if( !class_exists('zConfig') )
		{
			require_once('config.php');
		}
		$config = new zConfig;
		$class_vars = get_class_vars('zConfig');
		if( array_key_exists($key, $class_vars) ){
			return $class_vars[$key];
		}
		else{
			return $default;
		}
	}

	/**
	 * Get the main template and insert variables
	 * @author Martinkolle
	 * @return template
	*/
	function template($title,$head,$body){
		
		$template = file_get_contents('templates/default.html');

		if(!is_array(zpanelx::$zerror)){
			$template = str_replace('{{error}}', "", $template);
		} else{
			$errors = "";
			foreach(zpanelx::$zerror as $key => $error){
				$errors .= $error."<br />"; 
			}
			$template = str_replace('{{error}}', $errors, $template);
			$head 	 .= '<style type="text/css">#error{display:block !important;}</style>';
		}

		$template = ($title) ? str_replace('{{title}}', $title, $template) : str_replace('{{title}}', "", $template);
		$template = ($head) ? str_replace('{{head}}', $head, $template) : str_replace('{{head}}', "", $template);
		$template = ($body) ? str_replace('{{body}}', $body, $template) : str_replace('{{body}}', "", $template);

		return $template;
	}

	/**
	* Connection to the API. Simple function for minimising the code.
	* @author Martinkolle
	* @return array
	*/
	function api($module, $function, $data, $url, $api, $user = "", $pass =""){

		$xmws = new xmwsclient();
		$xmws->InitRequest($url, $module, $function, $api, $user, $pass);
		$xmws->SetRequestData($data);
		return $xmws->XMLDataToArray($xmws->Request($xmws->BuildRequest()), 0);
	}

	/**
	* Error function
	* Errors will be returned to the user when the template is printed.
	* @param string The error description
	* @param string Will only show the error when debug is turned on.
	* @param string Will force the error to show
	*/

	function error($error, $debug = false, $force = false){
		if(!is_array(zpanelx::$zerror)){
			zpanelx::$zerror = array();
		}
		array_push(zpanelx::$zerror,$error);

		if($force){
			echo self::template("Error","","");
			die();
		}
	}
}

?>