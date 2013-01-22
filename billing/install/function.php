<?php

session_start();
if (!isset($_SESSION['zpuid'])){
	die("Please log in");
}

//Get the function name, and check it only contains letter
$function = (isset($_GET['function'])) ? $_GET['function'] : "";
if(!preg_match("/^[a-zA-Z -]+$/", $function)){
	die("Invalid function");
}

include('../../../cnf/db.php');
include('../../../dryden/db/driver.class.php');
include('../../../dryden/debug/logger.class.php');
include('../../../dryden/runtime/dataobject.class.php');
include('../../../dryden/sys/versions.class.php');
include('../../../dryden/ctrl/options.class.php');
include('../../../dryden/ctrl/auth.class.php');
include('../../../dryden/ctrl/users.class.php');
include('../../../dryden/fs/filehandler.class.php');
include('../../../dryden/fs/director.class.php');
include('../../../dryden/ui/module.class.php');
include('../../../dryden/xml/reader.class.php');
include('../code/controller.ext.php');
include('../../../inc/dbc.inc.php');

if(method_exists("ajax", $function)){
	ajax::$function();
} else{
	die("Method does not exits");
}

class ajax {
	
	/**
	* Restore MySQL dump using PHP
	* (c) 2006 Daniel15
	* Version: 0.2
	* @link http://dan.cx/blog/2006/12/restore-mysql-dump-using-php
	*/
	
	static function installFtp(){
		global $zdbh;
		
		$file = "zpanel_core.sql";	

		$update = (isset($_GET['update'])) ? $_GET['update'] : false;
		$version = self::findVersion(false);
		
		if($update){
			if(!$version['currently'] < $version['new']){
				die("Current version have the same or lower then the new version.");
			}
			if(file_exists($version['new'].".sql")){
				$file = $version['new'].".sql";
			} else {
				die("Update sql does not exits");
			}
		}

		$templine	= '';
		$lines 		= file($file);
		$log 		= array();
		$error 		= array();
	
		// Loop through each line
		foreach ($lines as $line)
		{
			// Skip it if it's a comment
			if (substr($line, 0, 2) == '--' || $line == '')
				continue;
	
			// Add this line to the current segment
			$templine .= $line;
			// If it has a semicolon at the end, it's the end of the query
			if (substr(trim($line), -1, 1) == ';')
			{
				// Perform the query
				$zdbh->query($templine) or array_push($error, 'Error performing query \'<strong>' . $templine . '\': ' . mysql_error() . '<br /><br />');
				$temp = explode("VALUES", $templine);
				array_push($log, $temp[0]);
				
				// Reset temp variable to empty
				$templine = '';
			}
		}
		$file_log = "ERROR_NOT_CREATED";//date("Y-m-d_H:i:s").".log";
		//$fp = fopen($file_log, 'w');
		//fwrite($fp, print_r(log, TRUE));
		//fclose($fp);
		//if(!$logReturn){$log = null;}
		echo json_encode(array("status" => "1", "error" => "$error", "log" => $file_log));
	}
	
	static function findVersion($url = true){
		global $zdbh;
		
		$logReturn = false;//($_GET['log']) ? $_GET['log'] : false;
		$error 		= array();
				
		// Get version From module.xml
		$mod_xml 		= "/etc/zpanel/panel/modules/billing/module.xml";
		$mod_config 	= new xml_reader(fs_filehandler::ReadFileContents($mod_xml));
		$mod_config->Parse();
		$version_new 	= $mod_config->document->version[0]->tagData;
		$version 		= module_controller::$version;
	
		if(!$logReturn){$log = null;}
		if($url){
			echo json_encode(array("version" => $version, "version_new" => $version_new, "error" => "$error"));
		} else {
			return array("current" => $version, "new" => $version_new);
		}
	}
	
	static function installModule(){
		
		$module = true; //ui_module::CheckModuleExists("billing");
		$repo = "";
		$repo1 = "";
		exec("zppy repo list", $repo, $repo1);
		if(strpos($repo1, "zpanel.kmweb.dk") === false){
			exec("zppy repo add zpanel.kmweb.dk");
			exec("zppy update");
		} else{
			exec("zppy update");
		}

		$outout = "";
		if($module){
			exec("zppy upgrade billing", $output);
		}else {
			exec("zppy install billing", $output);
		}
		print_r($output);
		$logReturn = false;//($_GET['log']) ? $_GET['log'] : false;
		$error 		= array();

		if(!$logReturn){$log = null;}
		//echo json_encode(array("version" => $version, "version_new" => $version_new, "error" => "$error"));
	}

}