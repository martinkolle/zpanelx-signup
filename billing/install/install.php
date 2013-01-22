<?php

die();
/**
 * @package zpanelx
 * @subpackage modules->reseller_billing->install
 * @author Martin Kollerup
 * @copyright martinkole
 * @link http://www.kmweb.dk/
 * @license GPL (http://www.gnu.org/licenses/gpl.html)
 */

include('../../../cnf/db.php');
include('../../../dryden/db/driver.class.php');
include('../../../dryden/debug/logger.class.php');
include('../../../dryden/runtime/dataobject.class.php');
include('../../../dryden/sys/versions.class.php');
include('../../../dryden/ctrl/options.class.php');
include('../../../dryden/ctrl/auth.class.php');
include('../../../dryden/ctrl/users.class.php');
include('../../../dryden/fs/director.class.php');
include('../../../inc/dbc.inc.php');

/**
 * Restore MySQL dump using PHP
 * (c) 2006 Daniel15
 * Version: 0.2
 * @link http://dan.cx/blog/2006/12/restore-mysql-dump-using-php
 */
 
global $zdbh;

// Name of the file
$filename = 'zpanel_core.sql';

// Temporary variable, used to store current query
$templine = '';
// Read in entire file
$lines = file($filename);

$log = array();
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
		$zdbh->query($templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysql_error() . '<br /><br />');
		array_push($log, $templine);
		// Reset temp variable to empty
		$templine = '';
	}
}
?>
<h1>It looks like the database have been installed. But who knows?</h1>
<p>If the database not have been installed, please find the dump in /etc/zpanel/panel/modules/billing/install/zpanel_core.sql
<p>Please delete the folder /install/ with succesfull install.</p>
