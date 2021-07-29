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
	
	$aFundID	= explode('-',$fundID);
	$memberID 	= $aFundID[0];
	
	$username 	= get_member($mLink, $memberID, 'username');
	$fundSymbol	= get_funds($mLink, $fundID, 'fundSymbol');
	
	$fundLabel = $username.':'.strtoupper($fundSymbol);
		
}

if($index != NULL){
	if($index == "NASDAQ"){
		$index = "^IXIC";
	}elseif($index == "SP500"){
		//$index = "^GSPC";	
		$index = "^SP500TR";
	}elseif($index == "DJI"){
		$index = "^DJI";	
	}
	if($inception == ''){
		$date = '';	
	}else{
		$date = 'AND date > \''.$inception.'\'';
	}
		
	$query = "
		SELECT unix_date, close
		FROM stock_index_history
		WHERE `index`=:index ".$date."
		GROUP BY unix_date
		ORDER BY unix_date
	";
	
	try{
		$rsPrice = $mLink->prepare($query);
		$aValues = array(
			':index'	=> $index
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsPrice->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$cnt = 0;
	while($price = $rsPrice->fetch(PDO::FETCH_ASSOC)){
		
		$cnt = $cnt + 1;
		
		//unify timestamp
		$priceDate = $price['unix_date'];
		
		$unifiedTimestamp = mktime(5,0,0,date('m',$priceDate),date('d',$priceDate),date('Y',$priceDate));
		
		$date =($unifiedTimestamp* 1000);
		$price = round($price['close'],2);
				
		$array[] = array($date, $price);
		//$array[] = array(x=>$date, y=>$price, fund=>$index);
		
	}
}else{
	
	//Get Market Holidays from Database
	$query = "
		SELECT *
		FROM ".$_SESSION['system_holidays_table']."
		WHERE closed='Y'
	";
	
	try{
		$rsHolidays = $mLink->prepare($query);
		$aValues = array(
			//':fund_id'	=> $fundID
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsHolidays->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$aHolidays = array();
	while($holiday = $rsHolidays->fetch(PDO::FETCH_ASSOC)){
		$date = $holiday['date'];
		$year = substr($date, 0, 4);
		$month = substr($date, 5, 2);
		$day	= substr($date, 8, 2);
		
		$theDate = mktime(0, 0, 0, $month, $day, $year);
		
		$aHolidays[] = $theDate;
	}
	
	if(!isset($inception)){
		$date = '';	
	}else{
		$date = 'AND date > \''.$inception.'\'';
	}
	
	//Get Fund Pricing Data
	$query = "
		SELECT unix_date, price, secCompliance
		FROM ".$_SESSION['fund_pricing_table']."
		WHERE fund_id=:fund_id ".$date."
		GROUP BY unix_date
		ORDER BY unix_date
	";
	
	try{
		$rsPrice = $mLink->prepare($query);
		$aValues = array(
			':fund_id'	=> $fundID
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsPrice->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$cnt = 0;
	
	while($price = $rsPrice->fetch(PDO::FETCH_ASSOC)){
		$cnt + 1;
		
		$compliant = $price['secCompliance'];
		
		//unify timestmap
		$priceDate = $price['unix_date'];
		$unifiedTimestamp = mktime(5,0,0,date('m',$priceDate),date('d',$priceDate),date('Y',$priceDate));
		
		$date =($unifiedTimestamp * 1000);
		$price = round($price['price'],2);
		
		$dayOfWeek = date('l', ($date / 1000));
		
		//exclude weekends from graph
		if($dayOfWeek == 'Saturday'){
			//its saturday, do nothing
		}elseif($dayOfWeek == 'Sunday'){
			//its sunday, do nothing
		}else{
			//Its a week day, add to array
			
			if(in_array(($date / 1000), $aHolidays)){
				//date is a holiday do nothing	
			}else{
				if($compliant == "1"){
					$compliant = '<span style="color:#3cc051;">(Compliant)</span>';	
				}else{
					$compliant = '<span style="color:#ed4e2a;">(Not Compliant)</span>';
				}
				
				$array[] = array(x=>$date, y=>$price, compliant=>$compliant, fund=>$fundLabel);
					
			} //end if holiday
			
		} //end if weekend
		
	} //end while loop
}


//echo $cnt;
echo json_encode($array);
?>