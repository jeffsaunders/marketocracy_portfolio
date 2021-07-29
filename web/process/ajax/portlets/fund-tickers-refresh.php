<?php
/*
Marketocracy Inc. | Beta Development
Fund Indices(Tickers)

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/portlets/fund-tickers-ajax.php
	JAVASCRIPT	- includes/scripts/portlets/tickers.js.php
	HTML		- includes/portlets/tickers.php

*/

session_start();

// Load debug & error logging functions
require_once("../../../includes/system-debug-functions.php");
require("../../../../secure/dbconnect.php");
require("../../../includes/system-functions.php");

$process = $_REQUEST['process'];

switch($process){
	case 'runLivePrice':
		
		$memberID	= $_SESSION['member_id'];
		$username	= $_SESSION['username'];
		
		// Get the member's fund info
		$query = "
			SELECT fund_id, unix_date, fund_name, fund_symbol, description, short_fund
			FROM ".$_SESSION['fund_table']."
			WHERE member_id = :member_id
			AND active = '1'
			ORDRE BY weight ASC
		";
		try {
			$rsFunds = $mLink->prepare($query);
			$aValues = array(
				':member_id' => $_SESSION['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		//die($preparedQuery);
			$rsFunds->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$aMethodVars = array();
		
		while ($fund = $rsFunds->fetch(PDO::FETCH_ASSOC)){
			
			
			
			$fundID = $fund['fund_id'];
			$fundSymbol	= get_funds($mLink, $fundID, 'fundSymbol');
			
			$aMethodVars[] = array(
				'method'	=> 'livePrice',
				'source'	=> 'Dashboard: Fund Tickers Portlet | process/ajax/portlets/fund-tickers-refresh.php',
				'api'	=> '1',
				'username'	=> $username,
				'fund_id' => $fundID,
				'fund_symbol' => $fundSymbol,
				'group'	=> 'Fund Tickers' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
			);
			
			
		}
		
		$mlaResults = legacy_api($mLink, $aMethodVars, true, 'transIDs');
				
		$transIDs = implode('|',$mlaResults);
		
		echo $transIDs;
		
	break;
	
	case 'checkTransIDs':
	
		$aTransIDs		= explode('|', $_REQUEST['transIDs']);
		
		$loop 		= true;
		$timeout 	= 30;
		$loopCnt	= 0;
		
		while($loop){
			
			$loopCnt++;
			
			$aTransProcessing = array();
			
			foreach($aTransIDs as $key=>$transID){
				
				$query = "
					SELECT processing
					FROM ".$_SESSION['legacy_api_trans_table']."
					WHERE trans_id=:trans_id
				";
				try {
					$rsTrans = $mLink->prepare($query);
					$aValues = array(
						':trans_id' => $transID
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				//die($preparedQuery);
					$rsTrans->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				
				
				$trans = $rsTrans->fetch(PDO::FETCH_ASSOC);
				
				$processing = $trans['processing'];
				
				$aTransProcessing[] = $processing;
				
			}
			
			if(in_array('1', $aTransProcessing)){
				
				if($loopCnt <= $timeout){
					sleep(1);
				}else{
					$loop = false;	
				}
			}else{
				$loop = false;
				
				
			}
			
			print_r($aTransProcessing);
				
		}
	
	break;
	
}




?>