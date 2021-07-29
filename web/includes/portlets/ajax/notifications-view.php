<?php
//AJAX SCRIPT CALLED BY: /js/system-add-remove-portlets.js

session_start();

// Load debug & error logging functions
require_once("../../system-debug-functions.php");

//Connect to DB
require_once("../../../../secure/dbconnect.php");

$markRead = $_REQUEST['mark'];

$noteID = $_REQUEST['uid'];

if (isset($_SESSION['member_id']) == true){
	
	if($markRead == "1"){
		$flagged 	= "UNIX_TIMESTAMP()";
		$viewed		= "UNIX_TIMESTAMP()";
	}else{
		$flagged	= "0";
		$viewed 	= "UNIX_TIMESTAMP()";
	}
	
	
	
	$query = "
		UPDATE ".$_SESSION['members_notification_table']."
		SET	viewed=".$viewed.", flagged=".$flagged."
		WHERE member_id=:member_id AND uid=:uid
	";
	try {
		$rsUpdate = $mLink->prepare($query);
		$aValues = array(
			':member_id'	=> $_SESSION['member_id'],
			':uid'			=> $noteID,
		);
		// Prepared query - for error logging and debugging
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsUpdate->execute($aValues);
	}
	catch(PDOException $error){
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	echo $viewed;
	echo $flagged;
	//echo "reload";
	
	/*echo "It worked! Refresh the page!";
	echo $col1;
	echo $col2;*/
}
?>