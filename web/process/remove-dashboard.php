<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

$portletID = $_REQUEST['portlet_id'];


$query = "
	SELECT dash_col1, dash_col2 
	FROM members_settings
	WHERE member_id=:member_id 
";

//Fund Symbols
try{
	$rsGetColumns = $mLink->prepare($query);
	$aValues = array(
		':member_id' 	=> $_SESSION['member_id']
		
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsGetColumns->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$columns = $rsGetColumns->fetch(PDO::FETCH_ASSOC);

$col1 = explode("|", $columns['dash_col1']);
$col2 = explode("|", $columns['dash_col2']);

$cnt=0;


foreach($col1 as $key => $value) {
	if($value == $portletID){
		$cnt = $cnt + 1;	
	}
}

foreach($col2 as $key => $value) {
	if($value == $portletID){
		$cnt = $cnt + 1;	
	}
}

if($cnt == 0){
	$column1 = $columns['dash_col1'];
	$column1 .= "|";
	$column1 .= $portletID;
	
	if (isset($_SESSION['member_id']) == true){
		// log the event
		$query = "
			UPDATE members_settings 
			SET	dash_col1=:dash_col1,
				timestamp=UNIX_TIMESTAMP()
			
			WHERE member_id=:member_id
		";
		try {
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $_SESSION['member_id'],
				':dash_col1'	=> $column1
			);
			// Prepared query - for error logging and debugging
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		catch(PDOException $error){
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}
	
	
}
echo $cnt;
echo $_SESSION['member_id'];
echo $portletID;
echo "| ";
echo $column1;
?>