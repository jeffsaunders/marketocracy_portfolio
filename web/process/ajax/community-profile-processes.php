<?php
//Marketocracy Inc.
//Community Forum Processing scripts

//Start Session
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

// Load System Wide Functions
require_once("../../includes/system-functions.php");

//Get Process from URL
$process = $_REQUEST['process'];


//+---------------------------------------------------------------------------------------------------------+
//|									Send Connect Request - PROCESS 1										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "1"){
	
	$connection	= $_REQUEST['connection'];
	$requestMsg	= $_REQUEST['request_msg'];
	
		
	echo $connection.' | '.$requestMsg;
	
	$query = "
		INSERT INTO ".$_SESSION['connections_table']." (
			member_id,
			connection,
			status,
			request_message,
			timestamp
		) VALUE (
			:member_id,
			:connection,
			'pending',
			:request_msg,
			UNIX_TIMESTAMP()
		)
	";
	try{
		$rsCreateGroup = $mLink->prepare($query);
		$aValues = array(
			':member_id'	=> $_SESSION['member_id'],
			':connection'	=> $connection,
			':request_msg'	=> $requestMsg
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsCreateGroup->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	echo $error;
	
	//START NOTIFICATION
	$notificationID = "04-006";
	
	//Custom notification
	$notification	= "".get_member($mLink, $_SESSION['member_id'], "full name")." is trying to connect with you! Click here to review request.";
	
	$report = add_notification($mLink, $notificationID, $connection, $notification);
	
	echo $report;
	//END NOTIFICATION
	
	//Update Event Log
	$event = 'Connection Request';
	$detail = 'Member has submitted a connection request';
	addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
		
}

//+---------------------------------------------------------------------------------------------------------+
//|									Remove Connection - PROCESS 2											|
//+---------------------------------------------------------------------------------------------------------+
if($process == "2"){
	
	$connection	= $_REQUEST['connection'];
	
		
	echo $connection;
	
	/*$query = "
		UPDATE ".$_SESSION['connections_table']."
		SET status='inactive'
		WHERE member_id=:member_id AND connection=:connection OR member_id=:connection AND connection=:member_id
	";*/
	
	$query = "
		DELETE FROM ".$_SESSION['connections_table']."
		WHERE member_id=:member_id AND connection=:connection OR member_id=:connection AND connection=:member_id
	";
	try{
		$rsCreateGroup = $mLink->prepare($query);
		$aValues = array(
			':member_id'	=> $_SESSION['member_id'],
			':connection'	=> $connection
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsCreateGroup->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	echo $error;
	
	//Update Event Log
	$event = 'Connection Removed';
	$detail = 'Member has removed a connection.';
	addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
		
}

?>



















