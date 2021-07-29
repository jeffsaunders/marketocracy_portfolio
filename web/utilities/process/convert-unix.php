<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

require_once("../../includes/system-functions.php");

$query = "
	SELECT *
	FROM members_fund_composite
	
";
try{
	$rsConvert = $mLink->prepare($query);
	$aValues = array();
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsConvert->execute($aValues);
}

catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

while($convert = $rsConvert->fetch(PDO::FETCH_ASSOC)){
	
	$unixTime = $convert['unix_date'];
	
	$dateTime	= gmdate("Y-m-d 00:00:00", $unixTime);
	
	$query = "
		UPDATE members_fund_composite
		SET datetime=:datetime
		WHERE uid=:uid
	";
	try{
		$rsUpdate = $mLink->prepare($query);
		$aValues = array(
			':uid'		=> $convert['uid'],
			':datetime'	=> $dateTime
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsUpdate->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	echo 'Done';
}

?>