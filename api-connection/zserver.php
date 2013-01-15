<?php
/**
 * ServerConfig file for zpanelx signup
 * @author Aderemi Adewale (modpluz @ ZPanel Forums)
 * @desc Fetch API Key and Control Panel URL the Old Fashioned way
*/
//error_reporting(E_ALL);
class zServer {

   	static $zpanel_cfg;
   	//static $zpanel_url;

	static function _getConfig($host,$user,$pass){
        $db_conn = new mysqli($host, $user, $pass, 'zpanel_core');
	    if($db_conn->connect_errno > 0){
	        die("Cannot connect to database, Please check your connection settings!");
	    }

        //API Key
        if(!self::$zpanel_cfg['api_key']){
	        $sql = "SELECT so_value_tx FROM x_settings WHERE so_name_vc='apikey'";
	        $res = $db_conn->query($sql);
	        $rows = $res->num_rows;
	        if($rows > 0){
	            $row = $res->fetch_assoc();
            	self::$zpanel_cfg['api_key'] = $row['so_value_tx'];
            }
            $res->free();
        }	 

        //Panel URL
        if(!self::$zpanel_cfg['panel_url']){
	        $sql = "SELECT so_value_tx FROM x_settings WHERE so_name_vc='zpanel_domain' LIMIT 1";
	        $res = $db_conn->query($sql);
	        $rows = $res->num_rows;
	        if($rows > 0){
	            $row = $res->fetch_assoc();
            	self::$zpanel_cfg['panel_url'] = $row['so_value_tx'];
            }
            $res->free();
        }
        $db_conn->close();
        
        return self::$zpanel_cfg;
	}
	 


}
