<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

require_once("../../includes/system-functions.php");

$fundID = $_REQUEST['fund']; 
$inception = $_REQUEST['date'];


$query = "
	SELECT *
	FROM ".$_SESSION['fund_mtm_table']."
	WHERE fund_id=:fund_id
	ORDER BY unix_date ASC
";

try{
	$rsValues = $mLink->prepare($query);
	$aValues = array(
		':fund_id' 	=> $fundID
		
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsValues->execute($aValues);
}

catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$aMTM = array();

while($months = $rsValues->fetch(PDO::FETCH_ASSOC)){
	
	$date = $months['unix_date'];
	
	$aChartFund['['.($date*1000)] = number_format($months['value'], 2, '.', ',').']';
	
	
	
	
}


//echo '<pre>';
//print_r($aMTM);
$json = json_encode($aChartFund);

$json = str_replace(array('{','}','"',':'),array('[',']','',','), $json);

echo $json;
//echo '</pre>';


?>

