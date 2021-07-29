<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

$list 	= $_POST['list'];
$fundID	= $_POST['fund'];

$output = array();
$list = parse_str($list, $output);

$column1 = implode('|', $output['col1']);
$column2 = implode('|', $output['col2']);

//echo $column1;
//echo "<br>";
//echo $column2;
//echo "<br>";
//echo $fundID;

// Make sure they are actually logged in
if (isset($_SESSION['member_id']) == true){

	// log the event
	$query = "
		UPDATE members_fund_settings 
		SET	overview_col1=:overview_col1,
			overview_col2=:overview_col2,
			timestamp=UNIX_TIMESTAMP()
		
		WHERE fund_id=:fund_id
	";
	try {
		$rsUpdate = $mLink->prepare($query);
		$aValues = array(
			':fund_id'			=> $fundID,
			':overview_col1'	=> $column1,
			':overview_col2'	=> $column2,
		);
		// Prepared query - for error logging and debugging
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsUpdate->execute($aValues);
	}
	catch(PDOException $error){
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
}
?>