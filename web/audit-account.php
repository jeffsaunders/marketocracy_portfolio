<?php
/*
+----------------------------------------------------------------------------------------+
|						STRATIFICATION CALCULATION ROUTINE
+----------------------------------------------------------------------------------------+
*/
// Parse passed arguments string to $_REQUEST array (i.e. "first=1&second=2&third=3" -> $_REQUEST['first'] = 1, etc.)
//parse_str($argv[1], $_REQUEST);
error_reporting(E_ALL);
ini_set('display_errors', 1);
//get dependencies
/*require('/var/www/html/portfolio.marketocracy.com/web/includes/system-functions.php');
require('/var/www/html/portfolio.marketocracy.com/web/includes/system-debug-functions.php');
require('/var/www/html/portfolio.marketocracy.com/secure/dbconnect.php');*/
session_start();
require('includes/system-functions.php');
require('includes/system-debug-functions.php');
require('../secure/dbconnect.php');



//tables
$fundsTable				= 'members_fund';
$tradesTable 			= 'members_fund_trades';
$fundPosTable			= 'members_fund_positions';
$posDetailsTable		= 'members_fund_positions_details';
$stratBasicTable		= 'members_fund_stratification_basic';
$stratSectorTable		= 'members_fund_stratification_sector';
$stratStyleTable		= 'members_fund_stratification_style';
$stratSecPosTable		= 'members_fund_stratification_sector_positions';
$stratStylePosTable		= 'members_fund_stratification_style_positions';
$apiTransactionTable	= 'log_transactions_api_dev';

//+-------------------------------------------------------------------------------------------------
//|								Set main variables
//+-------------------------------------------------------------------------------------------------
$forceAudit				= $_REQUEST['force'];
$memberID 				= $_REQUEST['memberID'];
$processTrans			= $_REQUEST['process'];
if($processTrans		== ''){$processTrans = 1;}
$username				= get_member($mLink, $memberID, 'username');
$setFundID				= $_REQUEST['fundID'];
$apiServer				= $_REQUEST['api'];
$defaultAPI				= '2';
if($apiServer 			== ''){$apiServer = $defaultAPI;}
$aFunds					= array();
$fromDate				= $_REQUEST['fromDate'];
$today					= time();
$yesterday				= strtotime('-1 day', $today);
if($fromDate			== ''){$fromDate = $yesterday;}
switch($processTrans){	case '1': $processTrans = true;break;case '0': $processTrans = false;break;}
$aErrors				== array();
$loopTimeout			= 60; //Loop timeout in seconds

//echo $username;echo 'hello';echo $memberID;
switch($forceAudit){
	case '1': $forceAudit = true;break;
	case '0': $forceAudit = false;break;
	default	: $forceAudit = false;break;	
}

//+-------------------------------------------------------------------------------------------------
//|								Local Functions
//+-------------------------------------------------------------------------------------------------

//This function will mark the beginning of the audit process for the passed fund
function setAuditFlag($mLink, $fundsTable, $fundID, $action){
	
	switch($action){
		case 'start'	: $auditCheck = 1;break;
		case 'stop'		: $auditCheck = 0;break;	
	}
	$query = "
		UPDATE ".$fundsTable."
		SET audit_check=:audit_check 
		WHERE fund_id=:fund_id
	";
	try{
		$rsAudit = $mLink->prepare($query);
		$aValues = array(
			':fund_id'	=> $fundID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsAudit->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
	}
		
}

//This function checks the date of a particular column to tell us if the record is stale or not
function checkRecordDate($mLink, $fundID, $table, $column, $fromDate, $orderBy='timestamp', $orderType='DESC'){
	
	$debug = array();
	$debug['vars'] = array(
		'fundID'		=> $fundID,
		'table'			=> $table,
		'column'		=> $column,
		'fromDate'		=> $fromDate,
		'fromDateRead'	=> date('Ymd',$fromDate),
		'orderBy'		=> $orderBy,
		'orderType'		=> $orderType
	);
	
	//Lets assume the record passes
	$checkRecordDate = 1;
	
	//If no fromDate is passed lets default it to yesterday
	if($fromDate == NULL){
		$today			= time();
		$fromDate		= strtotime('-1 day', $today);
	}
	
	//Grab the record in question
	$query = "
		SELECT ".$column.", timestamp
		FROM ".$table."
		WHERE fund_id=:fund_id
		ORDER BY ".$orderBy." ".$orderType."
	";
	try{
		$rsAudit = $mLink->prepare($query);
		$aValues = array(
			':fund_id'	=> $fundID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsAudit->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
	}
	$dateCheck 	= $rsAudit->fetch(PDO::FETCH_ASSOC);
	
	$debug['query_error'] = $error;
	
	$checkDate 	= $dateCheck[$column];
	$timestamp	= $dateCheck['timestamp'];
	
	$debug['recordDate']		= $checkDate;
	$debug['recordDateRead']	= date('Ymd', $checkDate);
	
	if(date('Ymd',$checkDate) != date('Ymd', $fromDate)){
		$checkRecordDate = 0;
	}else{
		$checkRecordDate = 1;	
	}
	
	//return($debug);
	return($checkRecordDate);
		
}

//Checks to see if the transID pass is still processing, if processing is finished return the error if there is one
function transIDcheck($mLink, $apiTransactionTable, $transID){
	
	$query = "
		SELECT processing, error, submission_timestamp, completion_timestamp
		FROM ".$apiTransactionTable."
		WHERE trans_id=:trans_id
	";
	try{
		$rsTransInfo = $mLink->prepare($query);
		$aValues = array(
			':trans_id'	=> $transID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsTransInfo->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
	}
	$transInfo 	= $rsTransInfo->fetch(PDO::FETCH_ASSOC);
	
	//return($preparedQuery);
	
	$transProcessing	= $transInfo['processing'];
	$transStart			= $transInfo['submission_timestamp'];
	$transStop			= $transInfo['completion_timestamp'];
	$transError			= $transInfo['error'];
	
	//return($transProcessing);
	
	switch($transProcessing){
		
		//Transaction is still processing
		case '1':
			
			return($transProcessing);
			
		break;
		
		//Transaction is done processing
		case '0':
			
			return($transError);
			
		break;	
	}
		
}

function processTime(){
	
	$t = microtime(true);
	$micro = sprintf("%06d",($t - floor($t)) * 1000000);
	$d = new DateTime( date('Y-m-d H:i:s.'.$micro, $t) );
	
	$timestamp = $d->format("Y-m-d H:i:s.u");
	
	return($timestamp);
		
}

//+-------------------------------------------------------------------------------------------------
//|								Build Data to work with 
//+-------------------------------------------------------------------------------------------------
//Make sure memberID isn't empty
if($memberID != '' && $setFundID == ''){
	
	//Select all ACTIVE funds for a member.
	$query = "
		SELECT fund_id, fund_symbol
		FROM ".$fundsTable."
		WHERE member_id=:member_id AND active='1'
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
		
		$aFunds[$fund['fund_id']]  = array(
			'fund_symbol'	=> $fund['fund_symbol'],
			'trans_ids'	=> array()
		);
		
		$aFunds[$fund['fund_id']]['process_log'][] = 'Fund ID loaded into array. | Line:'.__LINE__.' | Time: '. processTime();
	}	
	
}elseif($memberID != '' && $setFundID != ''){
	
	$query = "
		SELECT fund_id, fund_symbol
		FROM ".$fundsTable."
		WHERE fund_id=:fund_id
	";
	try{
		$rsFund = $mLink->prepare($query);
		$aValues = array(
			':fund_id'	=> $setFundID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsFund->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
	}
	
	$fund = $rsFund->fetch(PDO::FETCH_ASSOC);
	
	$aFunds[$fund['fund_id']] = array(
		'fund_symbol'	=> $fund['fund_symbol'],
		'trans_ids'		=> array()
	);
	
	$aFunds[$fund['fund_id']]['process_log'][] = 'Fund ID loaded into array. | Line:'.__LINE__.' | Time: '. processTime();
	
}


//+-------------------------------------------------------------------------------------------------
//|						Loop through Data Set And get latest postionDetails
//+-------------------------------------------------------------------------------------------------
/*
We do this first so that we can get the process 
*/

//Lets check to see how many funds we'll be working with
$numFunds = count($aFunds);

//loop through and get the api processes rolling
foreach($aFunds as $fundID => $fundInfo){
	
	//Set Vars
	$aMethodVars[]	= array();
	$fundSymbol		= $fundInfo['fund_symbol'];
	$checkRecord 	= checkRecordDate($mLink, $fundID, $fundPosTable, 'unix_date', $fromDate, 'unix_date', 'DESC'); 
	
	
	
	//Flag the fund as being under audit
	setAuditFlag($mLink, $fundID, 'start');
	
	if($checkRecord == 0 || $forceAudit == true){
		
		if($forcedAudit == true && $checkRecord == 1){
			
			$aFunds[$fundID]['process_log'][] = 'Up to date record exists but audit was forced. |  Line:'.__LINE__.' | Time: '. processTime();
			
		}elseif($forceAudit == true && $checkRecord == 0){
			
			$aFunds[$fundID]['process_log'][] = 'Up to date record does not exist and audit was forced. |  Line:'.__LINE__.' | Time: '. processTime();
			
		}else{
			
			$aFunds[$fundID]['process_log'][] = 'Up to date record does not exist. |  Line:'.__LINE__.' | Time: '. processTime();
			
		}
		
		$aMethodVars[] = array(
			'method'						=> 'positionDetail',
			'source'						=> 'Account Audit Script | '.__FILE__.' | '.__LINE__.' | Time: '. processTime(),
			'api'							=> $apiServer,
			'username'						=> $username,
			'fund_id'						=> $fundID,
			'fund_symbol'					=> $fundSymbol,
			'from_date'						=> date('Ymd', $fromDate)
		);
		
		//Run method and extract transID for later processing
		$pdTransID							= legacy_api($mLink, $aMethodVars, $processTrans, 'transID');
		$aFundTransIDs						= $aFunds[$fundID]['trans_ids'];
		array_push($aFundTransIDs, $pdTransID);
		$aFunds[$fundID]['trans_ids'] 		= $aFundTransIDs;
		$aFunds[$fundID]['up2date_positions'] 	= false;
		
		$aFunds[$fundID]['process_log'][] = 'API call made: TransID: '.$pdTransID.' | Line:'.__LINE__.' | Time: '. processTime();
		
	}elseif($checkRecord == 1){
		
		$aFunds[$fundID]['process_log'][] = 'Up to date record found | Line:'.__LINE__.' | Time: '. processTime();
		
		$aFunds[$fundID]['up2date_positions'] 	= true;	
	}
	
}//end Foreach aFunds


//+-------------------------------------------------------------------------------------------------
//|						Go through each fund and build an array of share quantities
//+-------------------------------------------------------------------------------------------------
$loopCnt = 0;

$loopFunds = true;

//create a repeating loop until all conditions are met
while($loopFunds){
	
	$fundCompleteCnt = 0;
	
	//loop through each fund and make sure that all transIDs have been processed without errors
	foreach($aFunds as $fundID=>$aFundInfo){
		
		#set fund vars
		$getShares = false;
		$up2datePositions = $aFundInfo['up2date_positions'];
		
		//Check whether or not the position records are up to date
		if($up2datePositions == false){
			
			//positions for this fund were not up to date, so we need to check the transIDs to see if the api processes finished
			$aTransIDs = $aFundInfo['trans_ids'];
			
			//If the transID array is not empty, then we need to loop through the transIDs and see if they have completed processing.
			if(!empty($aTransIDs)){
				
				//Loop through each transID
				foreach($aTransIDs as $key=>$transID){
					
					//Get the result of the transID
					$transProcess = transIDcheck($mLink, $apiTransactionTable, $transID);
					
					//switch on the result
					if($transProcess == 1){
						
						#transaction is still procesing continue on to next
						$aFunds[$fundID]['process_log'][] = 'FundID: '.$fundID.' is still processing. | Line:'.__LINE__.' | Time: '. processTime();
						
						continue;
							
					}elseif($transProcess == NULL){
						
						#transaction has finished processing, update positions variable and continue on to get shares
						$aFunds[$fundID]['process_log'][] = 'FundID: '.$fundID.' Finished processing with no errors. | Line:'.__LINE__.' | Time: '. processTime();
						
						$aFunds[$fundID]['up2date_positions'] = true;
						$getShares = true;
					}else{
						#transaction has finished but there is an error, skip this fund and log the error
						$aFunds[$fundID]['process_log'][] = 'FundID: '.$fundID.' finished processing but had the following error: '.$transProcess.' | Line:'.__LINE__.' | Time: '. processTime();
					}	
				}
				
			}//end if !empty $aTransIDs
			
		}else{
			
			//Positions for this fund are up to date, now we need to get the shares of each position
			if(empty($aFunds[$fundID]['positions'])){
			
				$aFunds[$fundID]['process_log'][] = 'No need to check transIDs. Moving on to getting shares.';
			}
			$getShares = true;
				
		}//end check for up to date positions
		
		if($getShares == true && empty($aFunds[$fundID]['positions'])){
			
			//Get positions and shares from fund_positions table
			$query = "
				SELECT shares, stockSymbol, unix_date
				FROM ".$fundPosTable."
				WHERE fund_id=:fund_id AND date=:date
				ORDER BY shares
			";
			try{
				$rsPositions = $mLink->prepare($query);
				$aValues = array(
					':fund_id'	=> $fundID,
					':date'		=> date('Ymd',$fromDate)
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsPositions->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
			}
			
			$aFunds[$fundID]['process_log'][] = 'Getting shares from: '.$fundPosTable.' | Querey: '.$preparedQuery.' | Error: '.$error.' | Line:'.__LINE__.' | Time: '. processTime();
			
			$positionCnt = 0;
			while($position = $rsPositions->fetch(PDO::FETCH_ASSOC)){
				
				$positionCnt++;
				$shareQty		= $position['shares'];
				$stockSymbol	= $position['stockSymbol'];
				
				$aFunds[$fundID]['positionsDate'] = date('m/d/Y', $position['unix_date']);
				$aFunds[$fundID]['positions'][$stockSymbol] = $shareQty;
					
			}
			
			$aFunds[$fundID]['process_log'][] = 'Sucessfully pulled share quatities for '.$positionCnt.' Position(s) from table: '.$fundPosTable.'. | Line:'.__LINE__.' | Time: '. processTime();
			
			//Get positions and shares from stratification basic table
			$query = "
				SELECT totalShares, stockSymbol
				FROM ".$stratBasicTable."
				WHERE fund_id=:fund_id AND totalShares>'0'
				ORDER BY totalShares
			";
			try{
				$rsPositions = $mLink->prepare($query);
				$aValues = array(
					':fund_id'	=> $fundID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsPositions->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
			}
			
			$aFunds[$fundID]['process_log'][] = 'Getting shares from: '.$stratBasicTable.' | Querey: '.$preparedQuery.' | Error: '.$error.' | Line:'.__LINE__.' | Time: '. processTime();
			
			$positionCnt = 0;
			while($position = $rsPositions->fetch(PDO::FETCH_ASSOC)){
				
				$positionCnt++;
				$shareQty		= $position['totalShares'];
				$stockSymbol	= $position['stockSymbol'];
				
				$aFunds[$fundID]['strat_positions'][$stockSymbol] = $shareQty;
					
			}
			
			$aFunds[$fundID]['process_log'][] = 'Sucessfully pulled share quatities for '.$positionCnt.' Position(s) from table: '.$stratBasicTable.'. | Line:'.__LINE__.' | Time: '. processTime();
			
			
			//Compare arrays to find all positions that do not match up, we'll use this to process tradesForPosition on the inaccurate positions.
			foreach($aFunds[$fundID]['positions'] as $stockSymbol=>$shares){
				
				if($aFunds[$fundID]['strat_positions'][$stockSymbol] != $shares){
					$aFunds[$fundID]['getTrades'][] = $stockSymbol;	
				}
					
			}
			
			//Log fund as completed
			$aFunds[$fundID]['process_log'][] = 'End of loop: '.$loopCnt.' | Line:'.__LINE__.' | Time: '. processTime();
			$fundCompleteCnt++;
		}
		
		
		
		
		if(empty($aFunds[$fundID]['positions'])){
			$aFunds[$fundID]['process_log'][] = 'End of loop: '.$loopCnt.' | Line:'.__LINE__.' | Time: '. processTime();
		}
			
	}//end foreach $aFunds
	
	if($fundCompleteCnt == $numFunds){
		$loopFunds = false;	
	}else{
		$loopCnt++;
		sleep(1);
	}
	
	
	
	//make sure we dont get stuck in a never ending loop
	if($loopCnt >= $loopTimeout){
		$aErrors[] = 'Loop has exceeded max number of loops. | Line:'.__LINE__;
		$loopFunds = false;	
		
	}
	
}//end while $loopFunds

//+-------------------------------------------------------------------------------------------------
//|					Loop through positions that do not match and pull thier trades
//+-------------------------------------------------------------------------------------------------


echo '<pre>';





echo '<br /><br />';
print_r($aErrors);
print_r($aFunds);
echo '<pre>';

