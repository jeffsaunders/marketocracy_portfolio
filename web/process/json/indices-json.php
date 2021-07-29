<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

require_once("../../includes/system-functions.php");

$index = $_REQUEST['index']; 
$inception = $_REQUEST['date'];

if(!isset($inception)){
	$date = '';	
}else{
	$date = 'AND one.date > \''.$inception.'\'';
}

$query = "
	SELECT unix_date, close
	FROM stock_index_history as one
	WHERE `index`=:index ".$date." AND  timestamp=(
		SELECT MAX(two.timestamp)
		FROM stock_index_history AS two
		WHERE two.index=:index AND two.date=one.date)
	ORDER BY one.date
";

try{
	$rsPrice = $mLink->prepare($query);
	$aValues = array(
		':index'	=> $index,
		':date'		=> $date
		
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsPrice->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

while($price = $rsPrice->fetch(PDO::FETCH_ASSOC)){
	
	$date =($price['unix_date']* 1000);
	$price = round($price['close'],2);
	
	$array[] = array($date, $price);
	
}

echo json_encode($array);
?>