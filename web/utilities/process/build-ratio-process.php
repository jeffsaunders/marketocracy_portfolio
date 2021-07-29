<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

require_once("../../includes/system-functions.php");

$process = $_REQUEST['process'];

set_time_limit(3600);

function gcd($a,$b) {
	$a = abs($a); $b = abs($b);
	if( $a < $b) list($b,$a) = Array($a,$b);
	if( $b == 0) return $a;
	$r = $a % $b;
	while($r > 0) {
		$a = $b;
		$b = $r;
		$r = $a % $b;
	}
	return $b;
}

function simplify($num,$den) {
	$g = gcd($num,$den);
	return Array($num/$g,$den/$g);
}

if($process == "1"){
	
	$member = $_REQUEST['member'];
	
	
	if($member != ''){
		
		/*//Remove all records with member id
		$query = "
			DELETE
			FROM ".$_SESSION['fund_mtm_table']."
			WHERE member_id=:member_id
		";
		try{
			$rsClearTable = $mLink->prepare($query);
			$aValues = array(
				':member_id' 	=> $member
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsClearTable->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		*/
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
		
		//Clear out table
		/*$queryT = "
			TRUNCATE ".$_SESSION['fund_mtm_table']."
		";
		
		try{
			$rsClearTable = $mLink->prepare($queryT);
			$aValues = array(
				//':active' 	=> '1'
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsClearTable->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}*/
		
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
	
	//get pricing and add to array
	foreach($aMembers as $memberID=>$aMember){
		
		$aMembersWorking 	= array();
		
		foreach($aMember['funds'] as $key=>$fundID){
			
			unset($aMembers[$memberID]['funds'][$key]);
			
			$query = "
				SELECT totalShares, gains
				FROM ".$_SESSION['fund_stratification_basic_table']."
				WHERE fund_id=:fund_id
				ORDER BY totalShares ASC
			";
			try{
			$rsStrat = $mLink->prepare($query);
				$aValues = array(
					':fund_id' 	=> $fundID
					
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsStrat->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$aGains = array();
			$gainCnt = 0;
			while($strat = $rsStrat->fetch(PDO::FETCH_ASSOC)){
				$gainCnt++;
				$aGains[$gainCnt] = $strat['gains'];
				
			}
			$aMembersWorking[$memberID]['funds'][$fundID]['positions'] = $gainCnt;		
			$aMembersWorking[$memberID]['funds'][$fundID]['gains'] = $aGains;
		}
		
		foreach($aMembersWorking[$memberID]['funds'] as $fundID=> $aStuff){
			
			$posGainCnt = 0;
			$negGainCnt = 0;
			
			$posGainTotal = 0;
			$negGainTotal = 0;
			
			foreach($aStuff['gains'] as $key=>$gain){
				if($gain < 0){
					$negGainCnt++;	
					$negGainTotal = $negGainTotal + $gain;
				}elseif($gain > 0){
					$posGainCnt++;
					$posGainTotal = $posGainTotal + $gain;	
				}
			}
			
			$aMembersWorking[$memberID]['funds'][$fundID]['posCount'] = $posGainCnt;
			$aMembersWorking[$memberID]['funds'][$fundID]['posTotal'] = number_format($posGainTotal, 2, '.', ',');
			$aMembersWorking[$memberID]['funds'][$fundID]['negCount'] = $negGainCnt;
			$aMembersWorking[$memberID]['funds'][$fundID]['negTotal'] = number_format($negGainTotal, 2, '.', ',');
			
			$winningPercent = $posGainCnt / $aStuff['positions'];
			$gainLossRatio = $posGainTotal / abs($negGainTotal);
			
			$nGain = $posGainTotal / $posGainCnt;
			$dLoss = $negGainTotal / $negGainCnt;
			
			$avgGainLoss = $nGain / abs($dLoss);
			
							
			$aMembersWorking[$memberID]['funds'][$fundID]['winningPercent'] = $winningPercent;	
			$aMembersWorking[$memberID]['funds'][$fundID]['gainLossRatio'] = $gainLossRatio;
			$aMembersWorking[$memberID]['funds'][$fundID]['avgGainLossRatio'] = $avgGainLoss;	
			
			//lets hide this for now, so we can study the output and take up less memmory
			unset($aMembersWorking[$memberID]['funds'][$fundID]['gains']);
		}
		
		//$aMembers = array();
	}//end foreach member loop

	
	
	echo '<pre>';
	echo $error;
	print_r($aMembersWorking);
	echo '</pre>';
	
	echo 'done';
	
}

?>