<?php
/*
Marketocracy Inc. | Beta Development
Admin Managers

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/admin-managers-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/admin-managers.js.php
	HTML		- includes/pages/admin-managers.php
*/

//Start Session
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

// Load System Wide Functions
require_once("../../includes/system-functions.php");

//Get Process from URL
$process = $_REQUEST['process'];


$table = "members_subscriptions";

switch($process){
	
	case 'add-portlet':
		
		$addPortlet = 'fund-ratios~0~0~0';
		
		$query = "
			SELECT *
			FROM members_fund_settings_bu
			
			
		";
		try{
			$rsSettings = $mLink->prepare($query);
			$aValues = array();
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsSettings->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$hasPortletCnt 	= 0;
		$noPortletCnt	= 0;
		$fundCnt		= 0;
		$needsUpdateCnt	= 0;
		
		while($setting = $rsSettings->fetch(PDO::FETCH_ASSOC)){
			$fundCnt++;
			
			$hasPortlet 	= false;
			$needsUpdate	= false;
			
			$aCol1 			= explode('|',$setting['overview_col1']);
			$aCol2			= explode('|',$setting['overview_col2']);
			$aCol3			= explode('|',$setting['overview_col3']);
			$aCol4			= explode('|',$setting['overview_col4']);
			
			$preCol1Cnt 	= count($aCol1);
			$preCol2Cnt 	= count($aCol2);
			$preCol3Cnt 	= count($aCol3);
			$preCol4Cnt 	= count($aCol4);
			
			if(in_array($addPortlet,$aCol1)){
				$hasPortlet = true;	
				
				/*echo '<pre>';
				print_r($aCol1);
				echo '</pre>';*/
			}
			
			if(in_array($addPortlet,$aCol2)){
				$hasPortlet = true;	
				/*echo '<pre>';
				print_r($aCol2);
				echo '</pre>';*/
			}
			
			
			/*
			foreach($aCol2 as $key=>$portlet){
				
				if($portlet == $addPortlet){
					$hasPortlet = true;	
					echo '<pre>';
			print_r($aCol2);
			echo '</pre>';
				}
					
			}*/
			
			if(in_array($addPortlet,$aCol3)){
				$hasPortlet = true;	
				/*echo '<pre>';
				print_r($aCol3);
				echo '</pre>';*/
			}
			
			if(in_array($addPortlet,$aCol4)){
				$hasPortlet = true;	
				/*echo '<pre>';
				print_r($aCol4);
				echo '</pre>';*/
			}
			
			#clean up arrays
			$aCol1 = array_filter($aCol1);
			$aCol2 = array_filter($aCol2);
			$aCol3 = array_filter($aCol3);
			$aCol4 = array_filter($aCol4);
			
			if($hasPortlet == true){
				#don't add portlet
				$hasPortletCnt++;		
				
				
			}else{
				#add portlet
				$noPortletCnt++;
			
				#add new portlet to top of left column
				array_unshift($aCol2, $addPortlet);
				
				$needsUpdate = true;
				/*echo '<pre>';
				echo $setting['fund_id'];
				print_r($aCol1);
				print_r($aCol2);
				echo '</pre>';*/
			}
			
			#post fix counters
			$postCol1Cnt 	= count($aCol1);
			$postCol2Cnt 	= count($aCol2);
			$postCol3Cnt 	= count($aCol3);
			$postCol4Cnt 	= count($aCol4);
			
			if($postCol1Cnt != $preCol1Cnt){
				$needsUpdate = true;	
			}
			if($postCol2Cnt != $preCol2Cnt){
				$needsUpdate = true;	
			}
			if($postCol2Cnt != $preCol2Cnt){
				$needsUpdate = true;	
			}
			if($postCol2Cnt != $preCol2Cnt){
				$needsUpdate = true;	
			}
			
			if($needsUpdate == true){
				
				$needsUpdateCnt++;
				
				#convert to string
				$aCol1 = implode('|',$aCol1);
				$aCol2 = implode('|',$aCol2);
				$aCol3 = implode('|',$aCol3);
				$aCol4 = implode('|',$aCol4);
				
				/*echo '<pre>';
				echo $aCol2;
				echo '</pre>';*/
				
				$query = "
					UPDATE members_fund_settings_bu
					SET overview_col1=:col1, overview_col2=:col2, overview_col3=:col3, overview_col4=:col4
					WHERE uid=:uid
				";
				/*try{
					$rsUpdate = $mLink->prepare($query);
					$aValues = array(
						':uid'		=> $setting['uid'],
						':col1'		=> $aCol1,
						':col2'		=> $aCol2,
						':col3'		=> $aCol3,
						':col4'		=> $aCol4
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsUpdate->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}*/
				
				echo $error;
			}else{
				
			}
			
		}
		
		echo '<pre>';
		echo 'Porlet To Add: '.$addPortlet;
		echo '<br />Needs Update: '.$needsUpdateCnt;
		echo '<br />Total Funds: '.$fundCnt;
		echo '<br />Has Portlet: '.$hasPortletCnt;
		echo '<br />Does Not have Portlet: '.$noPortletCnt;
		echo '</pre>';
		
	break;
	
	case 'dedupe':
		
		$query = "
			SELECT *
			FROM ".$table."
			ORDER BY uid ASC
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
			
			$aMembers[$sub['member_id']][$sub['product_id']][] = $sub;
			
		}
		
		
		foreach($aMembers as $memberID=>$aProducts){
			
			$standardTrialCnt 	= count($aProducts['0']);
			$legacyTrialCnt		= count($aProducts['99']);
			$basicCnt			= count($aProducts['1']);
			$plusCnt			= count($aProducts['2']);
			$proCnt				= count($aProducts['3']);
			$legProCnt			= count($aProducts['4']);
			
			/*echo 'Member ID: '.$memberID.'<br />';
			echo 'Standard Trial: '.$standardTrialCnt.'<br />';
			echo 'Legacy Trial: '.$legacyTrialCnt.'<br />';
			echo 'Basic: '.$basicCnt.'<br />';
			echo 'Plus: '.$plusCnt.'<br />';
			echo 'Pro: '.$proCnt.'<br />';
			echo 'Legacy Pro: '.$legProCnt.'<br />';*/
			
			
			
			foreach($aProducts as $productID=>$aProdSubs){
				
				if($productID == '99' || $productID == '0'){
					#member is posibly in trial, check to see if they shbould still be in trial
					
					#there should only be one
					foreach($aProdSubs as $key=>$subRecord){
						
						if($subRecord['active'] == 0){
							$startTime 	= $subRecord['start_timestamp'];
							$trialExp	= strtotime('+30 day', $startTime);
							$subUID		= $subRecord['uid'];
							
							if($trialExp > time()){
								//echo 'should be in trial';	
								
								#re-activate trial record
								$query = "
									UPDATE ".$table."
									SET active='1', cancel_reason = NULL, cancel_timestamp = NULL 
									WHERE uid=:uid
								";
								try{
									$rsUpdate = $mLink->prepare($query);
									$aValues = array(
										':uid'	=> $subUID
									);
									$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
									$rsUpdate->execute($aValues);
								}
								
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}
								
								echo $preparedQuery;
								echo $error.'<br />';
								//echo $query.'<br />';
								
								#delete all other records perm
								$query2 = "
									DELETE FROM ".$table."
									WHERE member_id=:member_id AND uid<>:uid
								";
								try{
									$rsDelete = $mLink->prepare($query2);
									$aValues = array(
										':uid'			=> $subUID,
										':member_id'	=> $memberID
									);
									$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query2); //Debug
									$rsDelete->execute($aValues);
								}
								
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}
								echo $preparedQuery;
								echo $error.'<br /><br />';
								//echo $query2.'<br /><br />';
							
							
							}else{
								//echo 'should not be in trial';	
							}#end check  if expired
							
						}#check if record is active
					}#foreach $aProdSubs
					
					/*echo '<pre>';
					print_r($aProdSubs);
					echo '</pre>';*/
				}
					
			}#end for each aProducts
			
		}#end for each Members
		
		/*echo "<pre>";
		print_r($aMembers);
		echo "</pre>";*/
		
	break;
	
}

?>