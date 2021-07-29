<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

require_once("../../includes/system-functions.php");

$process = $_REQUEST['process'];

//set dates
$currentQuater 	= '20150331';
$oneYearPast	= '20140331';
$threeYearPast	= '20120331';
$fiveYearPast	= '20100331';
$tenYearPast	= '20050331';

$aDates = array($currentQuater,$oneYearPast,$threeYearPast,$fiveYearPast,$tenYearPast);

/*$year			= 2015;
$month			= '03';
$day			= '31';

$currentQuater 	= $year.$month.$day;
$oneYearPast	= ($year - 1).$month.$day;
$threeYearPast	= ($year - 3).$month.$day;
$fiveYearPast	= ($year - 5).$month.$day;
$tenYearPast	= ($year - 10).$month.$day;*/

//echo $oneYearPast;

set_time_limit(3600);

if($process == "1"){
	
	$member = $_REQUEST['member'];
	$maxMemberID	= $_REQUEST['max_member'];
	$minMemberID	= $_REQUEST['min_member'];
	
	if($maxMemberID == ''){
		$maxMemberID = 100000000;	
	}
	
	if($minMemberID == ''){
		$minMemberID = 1;	
	}
	
	//build member array
	if($member != ''){
		
		//Get only master member
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
	}else{
		
		//Get all active member IDs
		$query = '
			SELECT *
			FROM '.$_SESSION['members_table'].'
			WHERE active=:active
			ORDER BY member_id ASC
		';
		
		try{
			$rsMembers = $mLink->prepare($query);
			$aValues = array(
				':active' 	=> '1'
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsMembers->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}
	$aMembers = array();
	$cnt = 0;
	
	//loop through results and assign values to array
	while($member = $rsMembers->fetch(PDO::FETCH_ASSOC)){
		$memberID 	= $member['member_id'];
		$username 	= $member['username'];
		$firstName	= $member['name_first'];
		$lastName	= $member['name_last'];
		
		if($memberID <= $maxMemberID && $memberID >= $minMemberID){
		
			$query = "
				SELECT fund_id
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
			
			$aFunds = array();
			
			while($funds = $rsFunds->fetch(PDO::FETCH_ASSOC)){
				$aFunds[] = $funds['fund_id'];
				
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
		
	}
	//end build member array
	
	
	
	
	
	//get pricing and add to array
	foreach($aMembers as $memberID=>$aMember){
		
		$aFunds = $aMember['funds'];
		
		foreach($aFunds as $key=>$fundID){
		
			
			exec('/usr/bin/php /var/www/html/portfolio.marketocracy.com/scripts/strat-build.php "fundID='.$fundID.'" > /dev/null &');
			
			
		}
		
		
			
	}//end foreach member loop

	
	
	echo '<pre>';
	//echo $query2;
	//print_r($aSP500);
	//print_r($aProcess);
	//echo $error;
	//echo $cnt;
	
	//print_r($aMembers);
	//print_r($aBadge);
	//print_r($aBench);
	echo '</pre>';
	
	echo 'done';
	
}


