<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

require_once("../../includes/system-functions.php");

$process = $_REQUEST['process'];

set_time_limit(3600);


switch($process){
	
	case 'fix-sub-records':
		
		$aProducts			= get_products($mLink);
		$aMembers			= array();
		$aMissingMem		= array();
		
		$query = "
			SELECT ms.*
			FROM members_subscriptions AS ms
			WHERE active='1'
		";
		try{
			$rsSubs = $mLink->prepare($query);
			$aValues = array();
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsSubs->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		while($sub = $rsSubs->fetch(PDO::FETCH_ASSOC)){
			
			if($sub['product_id'] < 100){
				$aMembers[$sub['member_id']]['membership'][$sub['product_id']] = $aProducts[$sub['product_id']]['product_name'];
			}else{
				$aMembers[$sub['member_id']]['products'][$sub['product_id']] = $aProducts[$sub['product_id']]['product_name'];	
			}
			
		}
		
		foreach($aMembers as $memberID=>$subInfo){
			
			$aMemberships 	= $subInfo['membership'];
			$aProducts		= $subInfo['products'];
			
			if(empty($aMemberships)){
				$aMissingMem[$memberID]	= $aMembers[$memberID];
			}
				
		}
		
		
		
		echo '<pre>';
		print_r($aMissingMem);
		echo '</pre>';
		
	break;
	
	case 'fix-fund-exp':
		
		$query = "
			SELECT fund_id, fund_experation, member_id
			FROM ".$_SESSION['fund_table']."
			WHERE fund_experation IS NOT NULL
		";
		try{
		$rsFunds = $mLink->prepare($query);
			$aValues = array();
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsFunds->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		while($fund = $rsFunds->fetch(PDO::FETCH_ASSOC)){
			
			$fundID 	= $fund['fund_id'];
			$fundExp	= $fund['fund_experation'];
			
			$query = "
				UPDATE members_subscriptions
				SET fund_id=:fund_id
				WHERE member_id=:member_id AND product_id='1' AND active='1'
			";
			try{
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':member_id'	=> $fund['member_id'],
					':fund_id'		=> $fundID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdate->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$aFunds['sub'][$fundID] = array(
				'member_id'	=> $fund['member_id'],
				'query'		=> $preparedQuery
			);
			
			if(date('Y',$fundExp) == '2018'){
				
				$newExp		= strtotime('-365 day', $fundExp);
				
				$query = "
					UPDATE members_fund
					SET fund_experation=:fundEXP
					WHERE fund_id=:fund_id
				";
				try{
					$rsUpdate = $mLink->prepare($query);
					$aValues = array(
						':fundEXP'		=> $newExp,
						':fund_id'		=> $fundID
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsUpdate->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				$aFunds[$fundID] = array(
					'new_timestamp'		=> $newExp,
					'new_exp'			=> date('m/d/Y',$newExp),
					'old_timestamp'		=> $fundExp,
					'old_exp'			=> date('m/d/Y', $fundExp),
					'query'				=> $preparedQuery
				);
				
			}
			
			
			
			
			
			
		}
		
		echo '<pre>';
		print_r($aFunds);
		echo '</pre>';
		
		
	break;
	
	case 'fix-trans-date':
		
		$cnt=0;
		
		$query = "
			SELECT fund_id, fund_experation, member_id
			FROM ".$_SESSION['fund_table']."
			WHERE fund_experation IS NOT NULL
		";
		try{
		$rsFunds = $mLink->prepare($query);
			$aValues = array();
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsFunds->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		while($fund = $rsFunds->fetch(PDO::FETCH_ASSOC)){
			
			$fundEXP = $fund['fund_experation'];
			
			if($fundEXP < '1486249200'){
				
				$cnt++;
				
				$transDate = strtotime('-30 day', $fundEXP);
				
				$query = "
					UPDATE members_subscriptions 
					SET trans_wiz=:transDate
					WHERE member_id=:member_id AND active='1' AND product_id<'100'
				";
				try{
					$rsUpdate = $mLink->prepare($query);
					$aValues = array(
						':member_id'		=> $fund['member_id'],
						':transDate'	=> $transDate
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsUpdate->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				echo $cnt.' | '.$preparedQuery.' | '.$error.'<br />';
				
			}
			
			
			
		}
		
	break;
		
}


?>