<?php
//INDEX FEED PORTLET: This portlet is called in by ajax file: includes/scripts/system-heartbeat.php which is included on the index.php page

// Tell me when things go sideways
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

require("../../../secure/dbconnect.php");

require("../../includes/system-functions.php");

// Update their heartbeat timestamp
$query = "
	UPDATE ".$_SESSION['logged_in_table']."
	SET heartbeat_timestamp = UNIX_TIMESTAMP()
	WHERE member_id = :member_id
	AND session_id = :session_id
";
try {
	$rsUpdate = $mLink->prepare($query);
	$aValues = array(
		':member_id'	=> $_SESSION['member_id'],
		':session_id'	=> $_SESSION['session_id']
	);
	// Prepared query - for error logging and debugging
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
	$rsUpdate->execute($aValues);
}
catch(PDOException $error){
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

?>