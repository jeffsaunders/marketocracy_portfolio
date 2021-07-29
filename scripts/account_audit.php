<?php
/*
+----------------------------------------------------------------------------------------+
|						STRATIFICATION CALCULATION ROUTINE
+----------------------------------------------------------------------------------------+
*/
// Parse passed arguments string to $_REQUEST array (i.e. "first=1&second=2&third=3" -> $_REQUEST['first'] = 1, etc.)
parse_str($argv[1], $_REQUEST);

//get dependencies
require('/var/www/html/portfolio.marketocracy.com/web/includes/system-functions.php');
require('/var/www/html/portfolio.marketocracy.com/web/includes/system-debug-functions.php');
require('/var/www/html/portfolio.marketocracy.com/secure/dbconnect.php');



//tables
$fundsTable			= 'members_fund';
$tradesTable 		= 'members_fund_trades';
$fundPosTable		= 'members_fund_positions';
$posDetailsTable	= 'members_fund_positions_details';
$stratBasicTable	= 'members_fund_stratification_basic';
$stratSectorTable	= 'members_fund_stratification_sector';
$stratStyleTable	= 'members_fund_stratification_style';
$stratSecPosTable	= 'members_fund_stratification_sector_positions';
$stratStylePosTable	= 'members_fund_stratification_style_positions';

//+-------------------------------------------------------------------------------------------------
//|								Set main variables
//+-------------------------------------------------------------------------------------------------
$forceAudit		= $_REQUEST['force'];
$memberID 		= $_REQUEST['memberID'];
$username		= get_member($mLink, $memberID, 'username');
$setFundID		= $_REQUEST['fundID'];
$apiServer		= $_REQUEST['api'];
$defaultAPI		= '2';
if($apiServer 	== ''){$apiServer = $defaultAPI;}
$aFunds			= array();
$today			= time();
$yesterday		= strtotime('-1 day', $today);

switch($forceAudit){
	case '1': $forceAudit = true;break;
	case '0': $forceAudit = false;break;
	default	: $forceAudit = false;break;	
}

//+-------------------------------------------------------------------------------------------------
//|								Local Functions
//+-------------------------------------------------------------------------------------------------

//This function will mark the beginning of the audit process for the passed fund
function setAuditFlag($mLink, $fundID, $action){
	
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
function checkRecordDate($mLink, $fundID, $table, $column, $dateType, $fromDate, $orderBy='timestamp', $orderType='DESC'){
	
	//Lets assume the record passes
	$checkRecordDate = true;
	
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
	
	$checkDate 	= $dateCheck[$column];
	$timestamp	= $dateCheck['timestamp'];
	
	switch($dateType){
		case 'unix':
			
			if($checkDate <= $fromDate){
				$checkRecordDate = false;	
			}
			
		break;
		
		case 'yyyymmdd':
			
			if($checkDate != date('Ymd', $checkDate)){
				$checkRecordDate = false;
			}
			
		break;	
	}
	
	return($checkRecordDate);
		
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
			'symbol'	=> $fund['fund_symbol'],
			'trans_ids'	=> array()
		);
		
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
			':member_id'	=> $memberID
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
	
}


//+-------------------------------------------------------------------------------------------------
//|						Loop through Data Set And get latest postionDetails
//+-------------------------------------------------------------------------------------------------
/*
We do this first so that we can get the process 
*/

//Make sure our Transaction Array is empty
$aMethodVars[]	= array();

//loop through and get the api processes rolling
foreach($aFunds as $fundID => $fundInfo){
	
	$fundSymbol		= $fundInfo['fund_symbol'];
	$checkRecord 	= checkRecordDate($mLink, $fundID, $fundPosTable, 'unix_date', 'unix', $yesterday, 'unix_date', 'DESC'); 
	
	//Flag the fund as being under audit
	setAuditFlag($mLink, $fundID, 'start');
	
	if($checkRecord == false){
		
		$aMethodVars[] = array(
			'method'						=> 'positionDetail',
			'source'						=> 'Account Audit Script | '.__FILE__.' | '.__LINE__.'',
			'api'							=> $apiServer,
			'username'						=> $username,
			'fund_id'						=> $fundID,
			'fund_symbol'					=> $fundSymbol,
			'from_date'						=> date('Ymd', $yesterday),
			'group'							=> 'N/A' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
		);
		
		//Run method and extract transID for later processing
		$pdTransID							= legacy_api($mLink, $aMethodVars, true, 'transID');
		
		$aFundTransIDs						= $aFunds[$fundID]['trans_ids'];
		array_push($aFundTransIDs, $pdTransID);
		$aFunds[$fundID]['trans_ids'] 		= $aFundTransIDs;
		$aFunds[$fundID]['utd_positions'] 	= false;
		
	}elseif($checkRecord == true){
		
		$aFunds[$fundID]['utd_positions'] 	= true;	
		
	}
	
}//end Foreach aFunds

print_r($aFunds);

