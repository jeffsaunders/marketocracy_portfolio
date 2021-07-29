<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

require_once("../../../secure/dbconnect.php");

require_once("../../includes/system-functions.php");


//print_r($_POST);

$settings = $_POST['checkbox'];

$settings = implode('|', $settings);

if (isset($_SESSION['member_id']) == true){
	
	$query = "
		UPDATE ".$_SESSION['members_settings_table']." 
		SET	ignore_notifications=:ignore_notifications,
			timestamp=UNIX_TIMESTAMP()
		WHERE member_id=:member_id
	";
	try {
		$rsUpdate = $mLink->prepare($query);
		$aValues = array(
			':member_id'	=> $_SESSION['member_id'],
			':ignore_notifications'	=> $settings
		);
		// Prepared query - for error logging and debugging
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsUpdate->execute($aValues);
	}
	catch(PDOException $error){
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
}

echo $settings;

//Start Notification
$notificationID	= "11-002";
$memberID		= $_SESSION['member_id'];
//Custom notification
$notification	= "";
$link			= "";

add_notification($mLink, $notificationID, $memberID, $notification, $link);
//End Notification
?>