<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

require_once("../../includes/system-functions.php");

$process = $_REQUEST['process'];

set_time_limit(3600);

if($process == "1"){
	
	$member 	= $_REQUEST['member'];
	$fundID		= $_REQUEST['fund'];
	
	$runQuery	= $_REQUEST['query'];
	
	$api 		= $_REQUEST['api'];
	$apiType	= $_REQUEST['api-type'];
	
	$tradeStart	= $_REQUEST['tradeStartDate'];
	
	if($tradeStart == ''){
		$tradeDate = '';	
	}else{
		$tradeDate = '|'.$tradeStart;	
	}
	
	switch($apiType){
		case '1': $startPort = '52001';$endPort = '52050';break;
		case '2': $startPort = '52101';$endPort = '52150';break;	
	}
	
	//Get the passed member ID
	$query = '
		SELECT *
		FROM '.$_SESSION['members_table'].'
		WHERE active=:active AND member_id=:member_id
	';
	
	try{
		$rsMembers = $mLink->prepare($query);
		$aValues = array(
			':active' 		=> '1',
			':member_id'	=> $member
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsMembers->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aMembers = array();
	$cnt = 0;
	
	//loop through results and assign values to array
	while($member = $rsMembers->fetch(PDO::FETCH_ASSOC)){
		$memberID 	= $member['member_id'];
		$username 	= $member['username'];
		$firstName	= $member['name_first'];
		$lastName	= $member['name_last'];
		
		if($fundID == ''){
			$query = "
				SELECT fund_id, fund_symbol
				FROM ".$_SESSION['fund_table']."
				WHERE member_id=:member_id AND active=:active
			";
			try{
				$rsFunds = $mLink->prepare($query);
				$aValues = array(
					':member_id' 	=> $memberID,
					':active'		=> '1'
					
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsFunds->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
		}else{
			$query = "
				SELECT fund_id, fund_symbol
				FROM ".$_SESSION['fund_table']."
				WHERE fund_id=:fund_id
			";
			try{
				$rsFunds = $mLink->prepare($query);
				$aValues = array(
					':fund_id' 	=> $fundID
					
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsFunds->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
		}
		
		$aFunds = array();
		
		while($funds = $rsFunds->fetch(PDO::FETCH_ASSOC)){
			$aFunds[$funds['fund_id']] = $funds['fund_symbol'];
			
		}
		
		$aMembers[$memberID] = array(
			'member_id'	=> $memberID,
			'username'	=> $username,
			'firstName'	=> $firstName,
			'lastName'	=> $lastName,
			'funds'		=> $aFunds
		);
		
		$cnt++;
		
	}
	
	$aQueries = array();
	
	//get pricing and add to array
	foreach($aMembers as $memberID=>$aMember){
		
		$username = $aMember['username'];
		
		foreach($aMember['funds'] as $fundID=>$fundSymbol){		
			
			if($runQuery == 'all' || $runQuery == 'tradesForFund'){
				//generate trades query
				$port = rand($startPort, $endPort);
				$query = 'tradesForFund|'.$username.'|'.$fundID.'|'.$fundSymbol.$tradeDate;
				
				if($api == 'process'){
					include('../../includes/data-query-legacy.php');
				}
				
				$aQueries[] = $query;
			}
			
			
			if($runQuery == 'all' || $runQuery == 'allPositionInfo'){
				//generate allPositionInfo query
				$port = rand($startPort, $endPort);
				$query	= 'allPositionInfo|'.$username.'|'.$fundID.'|'.$fundSymbol.'';
				
				if($api == 'process'){
					include('../../includes/data-query-legacy.php');
				}
				
				$aQueries[] = $query;	
			}
			
		}
		
	}
	
	echo '<ol>';
	foreach($aQueries as $key=>$query){
		echo '<li>'.$query.'</li>';
	}
	echo '</ol.';
	
	echo '<pre>';
	

	//print_r($aQueries);
	
	//print_r($aMembers);
	
	echo '</pre>';
	
	
}

?>