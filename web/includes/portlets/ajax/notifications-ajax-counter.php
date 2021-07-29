<?php
//NOTIFICATION PORTLET: This portlet is called in by ajax file: includes/scripts/system-ajax-portlets.php which is included on the index.php page
session_start();

// Load debug & error logging functions
require_once("../../system-debug-functions.php");

require("../../../../secure/dbconnect.php");

require("../../system-functions.php");

$showZero = $_REQUEST['showZero'];

$noteSection = $_REQUEST['section'];
/*if($noteSection != ""){
	$noteSection = "AND notification_id='".$noteSection."'";	
}*/
if($noteSection != ""){
	$noteSection = "AND SUBSTRING(notification_id, 1, 2)='".substr($noteSection, 0, 2)."'";
}

$time = time();

$oneWeek = strtotime('-2 week', $time);

$query = "
	SELECT *
	FROM ".$_SESSION['members_notification_table']."
	WHERE member_id=:member_id AND deleted='0' ".$noteSection." AND notes.timestamp>:oneWeek OR member_id=:member_id AND deleted='0' AND flagged='1'
	ORDER BY timestamp DESC
";

//Fund Symbols
try{
	$rsSystemNoteSys = $mLink->prepare($query);
	$aValues = array(
		':member_id' 	=> $_SESSION['member_id'],
		':oneWeek'		=> $oneWeek
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsSystemNoteSys->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$cnt = 0;
while($systemNote = $rsSystemNoteSys->fetch(PDO::FETCH_ASSOC)){
	if($systemNote['viewed'] == "0"){
		$cnt = $cnt + 1;	
	}
}

if($cnt != "0"){
	echo $cnt;
}elseif($showZero == 1){
	echo $cnt;	
}
?>
