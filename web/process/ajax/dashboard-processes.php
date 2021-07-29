<?php
//Marketocracy Inc.
//Dashboard Processing scripts

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

if($process == '1'){
	
	$memberID = $_REQUEST['member'];
	
	$query = "
		UPDATE ".$_SESSION['members_notification_table']."
		SET deleted=UNIX_TIMESTAMP()
		WHERE member_id=:member_id AND flagged='0'
	";
	try{
		$rsUpdateNotes = $mLink->prepare($query);
		$aValues = array(
			':member_id' 	=> $memberID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsUpdateNotes->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	echo $error;
	
}

?>