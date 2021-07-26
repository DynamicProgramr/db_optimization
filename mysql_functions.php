<?php

	// copyright (c) 2007 - 2021, Russ Thompson and Freelance IT Solutions
	// You may copy and reuse this code in any way you would like.

/* ======================  /
/  Database Function Page  /
/  ====================== */

function db_connect() // connect with "mysql_connect"
{
	global $dbservername,$dbusername,$dbpassword,$dbname;
	
	$db = @mysql_pconnect($dbservername,$dbusername,$dbpassword);
	@mysql_select_db($dbname,$db);
	return $db;
	
}

function db_pdo() // connect with pdo
{
	global $dbservername,$dbusername,$dbpassword,$dbname;

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
