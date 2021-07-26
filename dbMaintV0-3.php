<?php
	/**
	 * this file is to backup a database, the check, repair and optimize each table
	 * version 0.1 - 09/28/2013
	 * 		The version tests concept and writes to broswer window.
	 * version 0.2 - 09/29/2013
	 * 		This version adds email functionality.
	 * version 0.3 - 09/30/2013
	 * 		This version becomes a cron job version, all prints are commented out.
	 * 		But they are left in the code in case they're needed for testing later.
	 * 
	 * 		copyright (c) 2021, Russ Thompson and Freelance IT Solutions
	 * 		You may copy and reuse this code in any way you would like.
	 */
	
	
	// for connection to db
	include("includes/config.php");
	include("includes/mysql_functions.php");
	
	$linkID = db_connect();
	
	//global vars
	$returnThis = "";
	$backupPath = "/dbBackups/";
	
	/*
	print "	Database Maintenance v0.1 - Execution time: ".date("d-M-Y H:i:s T")."<hr />
			Backing up tables.<br />
			working...<br />";
	*/
	
	$returnThis .= "	Database Maintenance v0.3 - Execution time: ".date("d-M-Y H:i:s T")."<hr />
					Backing up tables.<br />
					working...<br />";
	
	// first backup all tables
	//$datestamp = date("Y-m-d");     // Current date to append to filename of backup file in format of YYYY-MM-DD
	// for testing, using the unix timestamp because I will be running more than one time a day
	$datestamp = date("U");

	/* CONFIGURE THE FOLLOWING FOUR VARIABLES TO MATCH YOUR SETUP */
	$filename = "dbBackups/$dbname-backup-$datestamp.sql.gz";     // The name (and optionally path) of the dump file
	
	$command = "mysqldump -u $dbusername --password=$dbpassword $dbname | gzip > $filename";
	$result = passthru($command, $return_code);
	
	if ($result_code == 0)
	{
		/*
		print "	The database has been backed up successfully.  File name: ".$filename."<br /><br />
				Beginning optimization process...<br />";
		*/
		
		$returnThis .= "	The database has been backed up successfully.  File name: ".$filename."<br /><br />
						Beginning optimization process...<br />";
		
		$allTables = mysql_query("SHOW TABLES");
		
		// first check and repair if needed
		while ($table = mysql_fetch_assoc($allTables))
		{
			foreach ($table AS $db => $tableName)
			{
				$query1 = "CHECK TABLE ".$tableName." FAST"; // QUICK";
				$query2 = "REPAIR TABLE ".$tableName;
				
				$check_result = mysql_query($query1, $linkID);
				
				if (!$check_result || mysql_num_rows($check_result) <= 0)
				{
					// print "	Could not get status of table '".$tableName."' Error: ".mysql_error()."<br />";
					
					$returnThis .= "	Could not get status of table '".$tableName."' Error: ".mysql_error()."<br />";
					
					sleep(2);
				}
				else
				{
					$checkArray = mysql_fetch_assoc($check_result);
					
					if (strtolower($checkArray['Msg_text']) == "table is already up to date" || strtolower($checkArray['Msg_text']) == "ok")
					{
						// print "	CHECK -- ".$tableName." -- success: ".$checkArray['Msg_text']."<br />";
						
						$returnThis .= "	CHECK -- ".$tableName." -- success: ".$checkArray['Msg_text']."<br />";
						
						sleep(2);
					}
					else
					{
						// print "	CHECK -- ".$tableName." -- needs repair<br />";
						
						$returnThis .= "	CHECK -- ".$tableName." -- needs repair<br />";
						
						$repair_result = mysql_query($query2, $linkID);
						
						if (!$repair_result || mysql_num_rows($repair_result) <= 0)
						{
							// print "	Could not repair table '".$tableName."' Error: ".mysql_error()."<br />";
							
							$returnThis .= "	Could not repair table '".$tableName."' Error: ".mysql_error()."<br />";
						}
						else
						{
							$repairArray = mysql_fetch_assoc($repair_result);
							
							if (strtolower($repairArray['Msg_text']) == "ok" || strtolower($repairArray['Msg_text']) == "table is already up to date")
							{
								// print "	REPAIR -- ".$tableName." -- success: ".$repairArray['Msg_text']."<br />";
								
								$returnThis .= "	REPAIR -- ".$tableName." -- success: ".$repairArray['Msg_text']."<br />";
							}
							else
							{
								// print "	REPAIR -- ".$tableName." -- <span style=\"color: #ff0000;\">fail: ".$repairArray['Msg_text']."</span><br />";
								
								$returnThis .= "	REPAIR -- ".$tableName." -- <span style=\"color: #ff0000;\">fail: ".$repairArray['Msg_text']."</span><br />";
							}
						}
						
						sleep(3);
					}
					
					// do optimization				
					$query3 = "OPTIMIZE TABLE ".$tableName;
					
					$optimize_result = mysql_query($query3, $linkID);
					
					if (!$optimize_result || mysql_num_rows($optimize_result) <= 0)
					{
						// print "	Cannot optimize table '".$tableName."' Error: ".mysql_error()."<br />";
						
						$returnThis .= "	Cannot optimize table '".$tableName."' Error: ".mysql_error()."<br />";
						
						sleep(2);
					}
					else
					{
						$optimizeArray = mysql_fetch_assoc($optimize_result);
						
						if (strtolower($optimizeArray['Msg_text']) == "ok")
						{
							// print "	OPTIMIZE -- ".$tableName." -- success: ".$optimizeArray['Msg_text']."<br />";
							
							$returnThis .= "	OPTIMIZE -- ".$tableName." -- success: ".$optimizeArray['Msg_text']."<br />";
						}
						else
						{
							// print "	OPTIMIZE -- ".$tableName." -- <span style=\"color: #ff0000;\">fail: ".$optimizeArray['Msg_text']."</span><br />";
							
							$returnThis .= "	OPTIMIZE -- ".$tableName." -- <span style=\"color: #ff0000;\">fail: ".$optimizeArray['Msg_text']."</span><br />";
						}
						
						sleep(3);
					}
				}
			}
		}
	}
	else
	{
		/*
		print "	<span style=\"color: #ff0000;\">There has been an error while backing up the database. Error: ".$return_code."</span><br />
				The process has been stopped.";
		*/
		
		$returnThis .= "	<span style=\"color: #ff0000;\">There has been an error while backing up the database. Error: ".$return_code."</span><br />
						The process has been stopped.";
	}
	
	$emailBody = "	<html>
					<head>
						<title>Database Maintenance v0.3</title>
					</head>
					<body>
						".$returnThis."
					</body>
				</html>";
	
	$from = $fromemail; // change this in the config.php file or just add an email in this line, ex: "service@youremaildomain.com"
	$headers = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers  .= "From: $from\r\n";
	$subject = "Freelance db Report - ".date("d-M-Y");
	$toArray = array("russ@freelanceitsolutions.com", "questions@freelanceitsolutions.com");
	
	foreach($toArray AS $value)
	{
		$success = mail($value, $subject, $emailBody, $headers);
		
		if($success)
		{
			$successCount++;
		}
		else
		{
			array_push($failArray, $value);
		}
	}
	
	/* not reporting to screen at all. this is not needed for cron version
	if($successCount == count($toArray))
	{
		print "	<br /><br />Notification Sent Successfully."; // in cron job version, this will all be commented out
	}
	elseif(in_array("russ@freelanceitsolutions.com", $failArray) && in_array("emergency@freelanceitsolutions.com", $failArray))
	{
		print "	<br /><br /><span style=\"color: #ff0000;\">There has been an error. The db information has not been sent to any email address.</span>";
	}
	*/
	
	mysql_close($linkID);
?>