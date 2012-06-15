<?php

/*THIS FILE WILL BE REMOVED IN THE NEVER VERSIONS.. WE WILL ONLY SUPPORT PDO STATEMENTS.*/


$DBNAME = zpanelx::getConfig('dbName');
$DBUSER = zpanelx::getConfig('dbUser');
$DBPASSWORD = zpanelx::getConfig('dbPass');
$DBHOST = zpanelx::getConfig('dbHost');
$con = mysql_connect($DBHOST,$DBUSER,$DBPASSWORD);
?>