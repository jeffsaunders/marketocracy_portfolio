<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

require_once("../../includes/system-functions.php");


$list = $_POST['list'];

$output = array();
$list = parse_str($list, $output);

$column1 = implode('|', $output['col1']);
$column2 = implode('|', $output['col2']);
$column3 = implode('|', $output['col3']);
$column4 = implode('|', $output['col4']);

/*echo $col1;
echo $col2;
echo $col3;
echo $col4;*/

// Make sure they are actually logged in
if (isset($_SESSION['member_id']) == true){
	
	if(!isset($_SESSION['layout']) or $_SESSION['layout'] == "2"){
		// log the event
		$query = "
			UPDATE ".$_SESSION['members_settings_table']." 
			SET	dash_col1=:dash_col1,
				dash_col2=:dash_col2,
				timestamp=UNIX_TIMESTAMP()
			
			WHERE member_id=:member_id
		";
		try {
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $_SESSION['member_id'],
				':dash_col1'	=> $column1,
				':dash_col2'	=> $column2,
			);
			// Prepared query - for error logging and debugging
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		catch(PDOException $error){
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}elseif($_SESSION['layout'] == "4"){
		// log the event
		$query = "
			UPDATE ".$_SESSION['members_settings_table']." 
			SET	dash_4col1=:dash_4col1,
				dash_4col2=:dash_4col2,
				dash_4col3=:dash_4col3,
				dash_4col4=:dash_4col4,
				timestamp=UNIX_TIMESTAMP()
			
			WHERE member_id=:member_id
		";
		try {
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $_SESSION['member_id'],
				':dash_4col1'	=> $column1,
				':dash_4col2'	=> $column2,
				':dash_4col3'	=> $column3,
				':dash_4col4'	=> $column4
			);
			// Prepared query - for error logging and debugging
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		catch(PDOException $error){
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}
	
	
	
	//Start Notification
	$notificationID	= "11-001";
	$memberID		= $_SESSION['member_id'];
	//Custom notification
	$notification	= "";
	$link			= "";
	
	add_notification($mLink, $notificationID, $memberID, $notification, $link);
	//End Notification
}



?>