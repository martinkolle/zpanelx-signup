<?php
/**
 * @link http://weebtutorials.com/2012/03/pdo-connection-class-using-singleton-pattern/  
*/
class db{

//variable to hold connection object.
protected static $db;

	private function __construct() {

	/*Connect to database*/
	$dbHost		= zpanelx::getConfig('dbHost');
	$dbName 	= zpanelx::getConfig('dbName');
	$dbUser		= zpanelx::getConfig('dbUser');
	$dbPass		= zpanelx::getConfig('dbPass');

	try {
		// assign PDO object to db variable
		self::$db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser,$dbPass);
		self::$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		}
		catch (PDOException $e) {

		//Output error - would normally log this to error file rather than output to user.
		echo "Connection Error: " . $e->getMessage();
		}
	}

	// get connection function. Static method - accessible without instantiation
	public static function getConnection() {

		if (!self::$db) {
			//new connection object.
			new db();
		}
		return self::$db;
	}
}//end class


?>