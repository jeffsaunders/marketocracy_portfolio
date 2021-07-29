<?php
/*
+----------------------------------------------------------------------------------------+
|						STRATIFICATION CALCULATION ROUTINE
+----------------------------------------------------------------------------------------+
*/
// Parse passed arguments string to $_REQUEST array (i.e. "first=1&second=2&third=3" -> $_REQUEST['first'] = 1, etc.)
parse_str($argv[1], $_REQUEST);

//get dependencies
require('/var/www/html/portfolio.marketocracy.com/secure/dbconnect.php');
require('/var/www/html/portfolio.marketocracy.com/web/includes/system-functions.php');
require('/var/www/html/portfolio.marketocracy.com/web/includes/system-debug-functions.php');


//First lets see if we need to run this for a single fund or for all funds for a member
$fundID = $_REQUEST['fundID'];
$aFunds	= array();

//check to see if fundID is empty
if($fundID == ''){
	//If empty check for memberID
	$memberID = $_REQUEST['memberID'];
	
	if($memberID == ''){
		#if there is no memberID or fund ID exit script
		exit();	
	}else{
		#if there is a memberID, get all active fund ids
		$query = "
			SELECT fund_id
			FROM members_funds
			WHERE fund_type='sector' AND active='1'
		";	
		
		try{
			$rsFund = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $memberID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsFund->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
		}
		
		while($fund = $rsFund->fetch(PDO::FETCH_ASSOC)){
			$aFunds[]	= $fund['fund_id'];	
		}	
	}
}else{
	$aFunds[] = $fundID;	
}

foreach($aFunds as $key=>$fundID){
	
	$fund = new fund($dbPortfolio, $dbFeed, $fundID);
	
	$isCompliant = ($fund->getTodayCompliance() == true ? 1 : 0);
	
	$today = date('Ymd');
	
	$unixToday	= mktime(5,0,0,date('m'),date('d'),date('Y'));
	
	$query = "
		INSERT INTO members_fund_compliance (
			fund_id,
			timestamp,
			date,
			unix_date,
			sectorCompliance
		)VALUES(
			:fund_id,
			unix_timestamp(),
			:date,
			:unix_date,
			:sectorCompliance
		)
	";
	try{
		$rsInsert = $mLink->prepare($query);
		$aValues = array(
			':fund_id'			=> $fundID,
			':date'				=> $today,
			':unix_date'		=> $unixToday,
			':sectorCompliance'	=> $isCompliant
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsInsert->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
	}
	
	echo $fundID.'|';
	
}

?>