<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

require_once("../../includes/system-functions.php");

$fundID = $_REQUEST['fund']; 
$inception = $_REQUEST['date'];

if($fundID == "NASDAQ" || $fundID == "SP500" || $fundID == "DJI"){
	$index = $fundID;	
}else{
	$index = NULL;	
}

if($index != NULL){
	if($index == "NASDAQ"){
		$index = "^IXIC";
	}elseif($index == "SP500"){
		$index = "^GSPC";	
	}elseif($index == "DJI"){
		$index = "^DJI";	
	}
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
}else{

	$query = "
		SELECT unix_date, price
		FROM members_fund_pricing as one
		WHERE fund_id=:fund_id AND timestamp=(
			SELECT MAX(two.timestamp)
			FROM members_fund_pricing AS two
			WHERE two.fund_id=:fund_id AND two.date=one.date)
		ORDER BY one.date
	";
	
	try{
		$rsPrice = $mLink->prepare($query);
		$aValues = array(
			':fund_id'	=> $fundID,
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
		$price = round($price['price'],2);
		
		$array[] = array($date, $price);
		
	}
}



echo json_encode($array);
?>