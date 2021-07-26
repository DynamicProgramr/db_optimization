<?php
	/**
     * this file is to backup a database, then check, repair and optimize each table 
	 * 
     * version 0.4 - 07/25/2021   This is a new file - for the mysql_connect version, see dbMaintV0-3.php                                                  
     *    Convert all the db stuff to PDO to make it PHP 7.4+ compatible.
	 * 
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
	
	/* for testing -> 
	print "	Database Maintenance v0.4 - Execution time: ".date("d-M-Y H:i:s T")."<hr />
			Backing up tables.<br />
			working...<br />";
	 end testing */
	
	$returnThis .= "	Database Maintenance v0.4 - Execution time: ".date("d-M-Y H:i:s T")."<hr />
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
		
		$showTablesSql = "SHOW TABLES";
		$showTablesStmt = $linkID->query($showTablesSql);
		$allTables = $showTablesStmt->fetchAll();
		
		// first check and repair if needed
		foreach($allTables AS $table)
		{
			foreach ($table AS $db => $tableName)
			{
				$query1 = "CHECK TABLE ".$tableName." FAST"; // QUICK";
				$query2 = "REPAIR TABLE ".$tableName;
				
				$checkTableStmt = $linkID->query($query1);
				$check_result = $checkTableStmt->fetchAll();
				$checkRowCount = $checkTableStmt->rowCount();
				
				if ($check_result === FALSE || $checkRowCount <= 0)
                {
                    // print "	Could not get status of table '".$tableName."' Error: ".mysql_error()."<br />";
                    
                    $returnThis .= "	Could not get status of table '".$tableName."' Error: ".$checkTableStmt->errorInfo()[2]."<br />";
                    
                    sleep(2);
                }
                else
                {
                    foreach($check_result AS $the_check_result)
                    {
                        // for testing -> print "Check message: ".$the_check_result['Msg_text']."<br />";
                        
                        if (strtolower($the_check_result['Msg_text']) == "table is already up to date" || strtolower($the_check_result['Msg_text']) == "ok")
                        {
                            // for testing -> print "	CHECK -- ".$tableName." -- success: ".$the_check_result['Msg_text']."<br />";
                            
                            $returnThis .= "	CHECK -- ".$tableName." -- success: ".$the_check_result['Msg_text']."<br />";
                            
                            sleep(2);
                        }
                        else
                        {
                            // for Testing -> print "	CHECK -- ".$tableName." -- needs repair<br />";
                            
                            $returnThis .= "	CHECK -- ".$tableName." -- needs repair<br />";
                            
                            $repairTableStmt = $linkID->query($query2);
                            $repair_result = $repairTableStmt->fetchAll();
                            $repairRowCount = $repairTableStmt->rowCount();
                            
                            if ($repair_result === FALSE || $repairRowCount <= 0)
                            {
                                // for testing -> print "	Could not repair table '".$tableName."' Error: ".$repairTableStmt->errorInfo()[2]."<br />";
                                
                                $returnThis .= "	Could not repair table '".$tableName."' Error: ".$repairTableStmt->errorInfo()[2]."<br />";
                            }
                            else
                            {
                                foreach($repair_result AS $the_repair_result)
                                {
                                    // for testing -> print "Repair message: ".$the_repair_result['Msg_text']."<br />";
                                    
                                    if (strtolower($the_repair_result['Msg_text']) == "ok" || strtolower($the_repair_result['Msg_text']) == "table is already up to date")
                                    {
                                        // for testing -> print "	REPAIR -- ".$tableName." -- success: ".$the_repair_result['Msg_text']."<br />";
                                        
                                        $returnThis .= "	REPAIR -- ".$tableName." -- success: ".$the_repair_result['Msg_text']."<br />";
                                    }
                                    else
                                    {
                                        // for testing -> print "	REPAIR -- ".$tableName." -- <span style=\"color: #ff0000;\">fail: ".$the_repair_result['Msg_text']."</span><br />";
                                        
                                        $returnThis .= "	REPAIR -- ".$tableName." -- <span style=\"color: #ff0000;\">fail: ".$the_repair_result['Msg_text']."</span><br />";
                                    }
                                } // end repair foreach
                            }
                            
                            sleep(3);
                        }
                    } // end chech foreach
                    
                    // do optimization				
                    $query3 = "OPTIMIZE TABLE ".$tableName;
                    
                    $optimizeStmt = $linkID->query($query3);
                    $optimize_result = $optimizeStmt->fetchAll();
                    $optimizeRowCount = $optimizeStmt->rowCount();
                    
                    if ($optimize_result === FALSE || $optimizeRowCount <= 0)
                    {
                        // for testing -> print "	Cannot optimize table '".$tableName."' Error: ".$optimizeStmt->errorInfo()[2]."<br />";
                        
                        $returnThis .= "	Cannot optimize table '".$tableName."' Error: ".$optimizeStmt->errorInfo()[2]."<br />";
                        
                        sleep(2);
                    }
                    else
                    {
                        foreach($optimize_result AS $the_optimize_result)
                        {
                            // for testing -> print "Optimize message: ".$the_optimize_result['Msg_text']."<br />";
                            
                            if (strtolower($the_optimize_result['Msg_text']) == "ok")
                            {
                                // for testing -> print "	OPTIMIZE -- ".$tableName." -- success: ".$the_optimize_result['Msg_text']."<br /><br />";
                                
                                $returnThis .= "	OPTIMIZE -- ".$tableName." -- success: ".$the_optimize_result['Msg_text']."<br /><br />";
                            }
                            else
                            {
                                // for testing -> print "	OPTIMIZE -- ".$tableName." -- <span style=\"color: #ff0000;\">fail: ".$the_optimize_result['Msg_text']."</span><br /><br />";
                                
                                $returnThis .= "	OPTIMIZE -- ".$tableName." -- <span style=\"color: #ff0000;\">fail: ".$the_optimize_result['Msg_text']."</span><br /><br />";
                            }
                            
                            sleep(3);
                        } // end optimize foreach
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
	
	$from = "service@freelanceitsolutions.com";
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
	
	unset($linkID);
?>