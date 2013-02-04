<?php

/**
 * Reseller billing api integration
 *
 * @package Reseller billing -> api
 * @author Martin Kollerup
 * @copyright martinkole
 * @link http://www.kmweb.dk/
 * @license GPL (http://www.gnu.org/licenses/gpl.html)
 */

//error_reporting(E_ERROR);

class zpanelx {
	static $newUserError;
	static $token;
	static $zerror;

	/**
	 * PHP mail function to send mail in UTF-8.
	 *
	 * @author Martinkolle
	 * @return bool true on succes | false on failure
	 */
	static function sendemail($emailto, $emailsubject, $emailbody) {

		$fromEmail = self::getConfig('error_email');
		$fromEmailName = self::getConfig('error_emailName');
		$message = $emailbody;
		$headers = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
		$headers .= "From: " . $fromEmailName . " <" . $fromEmail . ">\r\n";

		$send = mail($emailto, $emailsubject, $emailbody, $headers);

		return ($send) ? true : false;
	}

	/**
	 * Generate a password for the payer.
	 * There is fallback to mt:rand() if openssl not is supported
	 * @link http://www.php.net/manual/en/function.openssl-random-pseudo-bytes.php#96812
	 * @return string password
	 */
	static function generatePassword($length = 9) {
		if (function_exists('openssl_random_pseudo_bytes')) {
			$password = base64_encode(openssl_random_pseudo_bytes($length, $strong));
			if ($strong == TRUE)
				return substr($password, 0, $length);
			//base64 is about 33% longer, so we need to truncate the result
		}
		//fall back to mt_rand
		$characters = '0123456789';
		$characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz/&';
		$charactersLength = strlen($characters) - 1;
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
	 * @return string token
	 */

	static function generateToken($length = 24) {
		$characters = '0123456789';
		$characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		$charactersLength = strlen($characters) - 1;
		$token = '';
		//select some random characters
		for ($i = 0; $i < $length; $i++) {
			$token .= $characters[mt_rand(0, $charactersLength)];
		}
		return $token;
	}

	/**
	 * Get the config.php values
	 * @copyright Copyright (c)2009-2012 Nicholas K. Dionysopoulos
	 */
	public static function getConfig($key, $default = null) {
		if (!class_exists('zConfig')) {
			require_once ('config.php');
		}
		$config = new zConfig;
		$class_vars = get_class_vars('zConfig');
		if (array_key_exists($key, $class_vars)) {
			return $class_vars[$key];
		} else {
			return $default;
		}
	}

	/**
	 * Get the main template and insert variables
	 * @author Martinkolle
	 * @return string template
	 */
	static function template($title, $head, $body) {
		$company = zpanelx::getConfig('company');
		$theme = zpanelx::getConfig('theme');
		$template = file_get_contents('./themes/' . $theme . '/includes/header.tpl');
		$template .= file_get_contents('./themes/' . $theme . '/includes/body.tpl');
		$template .= file_get_contents('./themes/' . $theme . '/includes/footer.tpl');

		if (!is_array(zpanelx::$zerror)) {
			$template = str_replace('{{error}}', "", $template);
		} else {
			$errors = "";
			foreach (zpanelx::$zerror as $key => $error) {
				$errors .= $error . "<br />";
			}
			$template = str_replace('{{error}}', $errors, $template);
			$head .= '<style type="text/css">#error{display:block !important;}</style>';
		}

		$template = ($title) ? str_replace('{{title}}', $title, $template) : str_replace('{{title}}', "", $template);
		$template = ($head) ? str_replace('{{head}}', $head, $template) : str_replace('{{head}}', "", $template);
		$template = ($body) ? str_replace('{{body}}', $body, $template) : str_replace('{{body}}', "", $template);
		$template = str_replace('{{theme}}', $theme, $template);
		$template = str_replace('{{company}}', $company, $template);
		return $template;
	}

	/**
	 * Connection to the API using xmws
	 * @author Martinkolle
	 * @return array
	 */
	static function api($module, $function, $data, $url = "", $api = "", $user = "", $pass = "") {
		//Find the url and api from the config.php.
		//This can be used if you are having different API connections to different servers on ZPX
		if (empty($url)) {
			$url = self::getConfig("zpanel_url");
		}
		if (empty($api)) {
			$api = self::getConfig("zpanel_api");
		}

		//Do the url have the scheme
		$parsed = parse_url($url);
		if (empty($parsed['scheme'])) {
			$url = "http://$url";
		}

		if (!class_exists('xmwsclient')) {
			require_once ('xmwsclient.class.php');
		}
		$xmws = new xmwsclient();
		$xmws -> InitRequest($url, $module, $function, $api, $user, $pass);
		$xmws -> SetRequestData($data);
		$xml = $xmws -> XMLDataToArray($xmws -> Request($xmws -> BuildRequest()), 0);
		//return error when wrong response code
		if ($xml['xmws']['response'] != "1101") {
			self::error("Wrong response code: " . $xml['xmws']['content'] . " (" . $xml['xmws']['response'] . ")", false, true);
		}
		return $xml['xmws']['content'];
	}

	/**
	 * Error function
	 * Errors will be returned to the user when the template is printed.
	 * @param string The error description
	 * @param string Will only show the error when debug is turned on.
	 * @param string Will force the error to show
	 */

	static function error($error, $debug = false, $force = false) {
		if (!is_array(zpanelx::$zerror)) {
			zpanelx::$zerror = array();
		}
		array_push(zpanelx::$zerror, $error);

		if ($force) {
			echo self::template("Error", "", "");
			die();
		}
	}

}
?>