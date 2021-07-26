<?php

	// copyright (c) 2007 - 2021, Russ Thompson and Freelance IT Solutions
	// You may copy and reuse this code in any way you would like.

/* ======================  /
/  Database Function Page  /
/  ====================== */

function db_connect()
{

	global $dbservername,$dbusername,$dbpassword,$dbname;

	/* if using php 5.x and dbMaintV0-3.php uncomment this code and comment out the PDO code below
	$db = @mysql_pconnect($dbservername,$dbusername,$dbpassword);
	@mysql_select_db($dbname,$db);
	return $db;
	*/

	/* if using php 7.x and dbMaintV0-4.php, use this code */
	/* for PDO connection */
	try
	{
		$db = new PDO("mysql:host=".$dbservername.";dbname=".$dbname, $dbusername, $dbpassword);
	}
	catch (Exception $e)
	{
		die("ERROR: Could not connect to MySQL database: ".$e);
	}

	return $db;
}

?>
