<?php
#include classes
//include('system-classes.php');

function trace() {
  $fileinfo = 'no_file_info';
  $backtrace = debug_backtrace();
  if (!empty($backtrace[0]) && is_array($backtrace[0])) {
    $fileinfo = $backtrace[0]['file'] . ":" . $backtrace[0]['line'];
  }
  return "Location: $fileinfo\n";
}

//+----------------------------------------------------------------------------------------+
//|						Marketocracy Legacy API Transaction Log Function
//+----------------------------------------------------------------------------------------+
function legacy_api($mLink, $aMethodVars, $exec=true, $option='results'){
	
	$queryLegacyPath 	= 'data-query-legacy.php';
	$pauseCnt 			= 0;
	$methodCnt			= 0;
	$aResults			= array();
	
	$aTransIDs			= array();
	
	#loop through each posible query
	foreach($aMethodVars as $queryCnt=>$aVars){
		
		$pauseCnt++;
		$methodCnt++;
		$addToEventLog = true;
		
		
		if($pauseCnt >= 50){
			sleep(1);
			$pauseCnt = 0;	
		}
		
		$method 		= $aVars['method'];
		
		$memberID		= $aVars['member_id'];
		
		if($memberID == ''){
			$memberID = $_SESSION['member_id'];	
		}
		
		if($memberID == ''){
			$addToEventLog = false;	
		}
		
		//Get query strings from Db
		$query = "
			SELECT *
			FROM ".$_SESSION['api_methods_table']."
			WHERE method=:method
		";
		try{
			$rsMethods = $mLink->prepare($query);
			$aValues = array(
				':method'	=> $method
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsMethods->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$getMethod = $rsMethods->fetch(PDO::FETCH_ASSOC);
		
		$aResults['errors']['get_method_queries'][] = $error;
		
		//Set query vars
		$oldQuery 		= $getMethod['query_old'];
		$newQuery		= $getMethod['query_new'];
		$querySwitch	= $getMethod['query_switch'];
		
		//Set common vars for use in transaction table
		$api			= $aVars['api'];
		if($api 		== ''){$api = '2';}
		$port			= $aVars['port'];
		$source			= $aVars['source'];
		$group			= $aVars['group'];
		if($group		== ''){ $group = NULL;}
		$fundID			= $aVars['fund_id'];
		if($fundID 		== ''){ $fundID = NULL;}
		
		//assign port value
		if($port == ''){
			
			if(!isset($startPort)){
				
				switch($api){
					case '1':
						$startPort 	= rand(52000, 52099);
						$endPort	= 52099;
					break;
					
					case '2':
						$startPort 	= rand(52100, 52499);
						$endPort	= 52499;
					break;
					
					case '3':
						$startPort 	= rand(52500,52699);
						$endPort	= 52699;
					break;
					
					default:
						$startPort 	= rand(52000, 52099);
						$endPort	= 52099;
					break;	
				}
				
				$nextPort = $startPort;
			}else{
				if($nextPort < $endPort){
					$nextPort = $nextPort++;
				}else{
					sleep(1);
					$nextPort = $startPort;	
				}
			}
			
			$port = $nextPort;
		}
		
		//Deterine which process string to use		
		if($querySwitch == 'new'){
			$processing = '1';
			$queryStr	= $newQuery;	
		}elseif($querySwitch == 'old'){
			$processing	= '0';
			$queryStr	= $oldQuery;	
		}
		
		foreach($aVars as $var=>$value){
			$queryStr	= str_replace($var, $value, $queryStr);
		}
		
		$apiQuery = $queryStr;
		
		//Insert transaction into transaction table
		$query = "
			INSERT INTO ".$_SESSION['legacy_api_trans_table']." (
				api,
				port,
				groups,
				timestamp,
				fund_id,
				method,
				query,
				submission_timestamp,
				processing,
				source
			)VALUES(
				:api,
				:port,
				:groups,
				UNIX_TIMESTAMP(),
				:fund_id,
				:method,
				:query,
				UNIX_TIMESTAMP(),
				:processing,
				:source
			)
		";
		try{
			$rsInsertTrans = $mLink->prepare($query);
			$aValues = array(
				//':trans_id'		=> $transID,
				':api'			=> $api,
				':port'			=> $port,
				':groups'		=> $group,
				':fund_id'		=> $fundID,
				':method'		=> $method,
				':query'		=> $apiQuery,
				':source'		=> $source,
				':processing'	=> $processing
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsInsertTrans->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$aResults['errors']['insert_trans_errors'][] = $error;
		
		$transID = $mLink->lastInsertId();
		
		$aTransIDs[] = $transID;
		
		//Execute query to daemon
		$apiQuery 	= str_replace('trans-id', $transID, $apiQuery);
		$query 		= $apiQuery;
		
		if($exec == true){
			include('data-query-legacy.php');
			
			if($addToEventLog == true){
				$event = 'MLA : '.$method;
				$detail = $source.' : '.$query;
				addEventRecord($mLink, $memberID, $event, $detail);
			}
			
			$aResults['submissions'][$methodCnt]['query'] = $query;
			$aResults['submissions'][$methodCnt]['port'] = $port;
			$aResults['submissions'][$methodCnt]['api'] = $api;
		}
		
	}//end foreach query
	
	switch($option){
		case 'results': return $aResults;break;	
		
		case 'transID': return $transID;
		
		case 'transIDs': return $aTransIDs;
		
		default: return $aResults;break;
	}
	
}

function getCA($mLink, $symbol){
	
	$query = "
		SELECT *
		FROM stock_corporate_actions
		WHERE symbol=':symbol' OR newSymbol=':symbol'
	";
	try{
			$rsCA = $mLink->prepare($query);
			$aValues = array(
				':symbol'		=> $symbol
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsCA->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	
	return($preparedQuery);
	
	while($ca = $rsCA->fetch(PDO::FETCH_ASSOC)){
		
		$method = $ca['method'];
		
		switch($method){
			
			case 'splitsOnDate':
				
				$aCA['stockSplits'][] = array(
					'effectiveDate'		=> $ca['unix_date'],
					'terms'				=> $ca['terms'],
					'desc'				=> 'Split terms: '.$ca['terms']
				);
				
			break;
			
			case 'cashDividendsOnDate':
				
				$aCA['cashDiv'][] = array(
					'effectiveDate'		=> $ca['unix_date'],
					'recordDate'		=> $ca['record_unix_date'],
					'payDate'			=> $ca['pay_unix_date'],
					'frequency'			=> $ca['frequency'],
					'gross'				=> $ca['gross'],
					'desc'				=> 'Dividend amount: $'.number_format($ca['gross'],2,'.',',').' per share.'
				);
				
			break;
			
			case 'stockDividendsOnDate':
			
			break;
			
			case 'symbolChangesOnDate':
				
				$companyID = $ca['company_id'];
				
				$query = "
					SELECT *
					FROM stock_corporate_actions
					WHERE company_id=:company_id AND method='symbolChangesOnDate'
					ORDER BY unix_date ASC
				";
				try{
					$rsSC = $mLink->prepare($query);
					$aValues = array(
						':symbol'		=> $symbol
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsSC->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				//return($preparedQuery);
				
				while($sc = $rsSC->fetch(PDO::FETCH_ASSOC)){
					
					$aCa['symbolChange'][] = array(
						'effectiveDate'		=> $sc['unix_date'],
						'oldSymbol'			=> $sc['symbol'],
						'newSymbol'			=> $sc['newSymbol'],
						'desc'				=> 'Change in ticker (trading) symbol from '.$sc['symbol'].' to '.$sc['newSymbol'].'.'
					);
				}
				
			break;
			
			case 'acquisitionsOnDate':
			
			break;
			
			case 'cusipChangesOnDate':
			
			break;
			
			case 'bankruptciesOnDate':
			
			break;
			
			case 'spinoffsOnDate':
			
			break;
			
			case 'listingsOnDate':
			
			break;
			
			case 'delistingsOnDate':
			
			break;
			
			case 'nameChangesOnDate':
				$aCA['nameChange'][] = array(
					'effectiveDate'		=> $ca['unix_date'],
					'oldName'			=> $ca['old_company_name'],
					'newName'			=> $ca['company_name'],
					'desc'				=> 'Change in the name of issuer from '.$ca['old_company_name'].' to '.$ca['company_name'].'.'
				);
			break;
			
			case 'listingChangesOnDate':
				$aCA['listingChange'][] = array(
					'effectiveDate'		=> $ca['unix_date'],
					'oldExchange'		=> $ca['old_exchange'],
					'oldCode'			=> $ca['old_code'],
					'newExchange'		=> $ca['new_exchange'],
					'newCode'			=> $ca['new_code'],
					'desc'				=> 'Change in the exchange that '.strtoupper($symbol).' trades on from '.$ca['old_exchange'].' to '.$ca['new_exchange'].'.'
				);
			break;
			
		}//END SWITCH
	
	}//END WHILE LOOP
	
	return($aCA);
		
}

function memberData($mLink, $memberID){
	
	#members
	$query = "
		SELECT m.*, ms.*, mp.*
		FROM `members` m,  members_settings ms, members_profile mp
		WHERE m.member_id=:member_id  AND m.member_id=ms.member_id AND m.member_id=mp.member_id
	";
	try{
		$rsLookup 		= $mLink->prepare($query);
		$aValues 		= array(
			':member_id'	=> $memberID
		);
		$preparedQuery 	= str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsLookup->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$memberInfo['data'] = $rsLookup->fetch(PDO::FETCH_ASSOC);
	
	#flags
	$query = "
		SELECT mf.*
		FROM members_flags mf
		WHERE mf.member_id=:member_id
	";
	try{
		$rsLookup 		= $mLink->prepare($query);
		$aValues 		= array(
			':member_id'	=> $memberID
		);
		$preparedQuery 	= str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsLookup->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$memberInfo['flags'] = $rsLookup->fetch(PDO::FETCH_ASSOC);
	
	$query = "
		SELECT *
		FROM ".$_SESSION['auth_table']."
		WHERE member_id=:member_id AND active='1'
		ORDER BY uid DESC
		LIMIT 1
		"; 
	
	try{
		$rsLookup 		= $mLink->prepare($query);
		$aValues 		= array(
			':member_id'	=> $memberID
		);
		$preparedQuery 	= str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsLookup->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$memberInfo['auth'] = $rsLookup->fetch(PDO::FETCH_ASSOC);
	
	
	return($memberInfo);
	
}



function sendAutoEmail($mLink, $memberID, $campID, $sendEmail=false){
	
	if($sendEmail == false){
		$aFuncDebug['sendEmailStatus'] = 'false';
	}elseif($sendEmail == true){
		$aFuncDebug['sendEmailStatus'] = 'true';
	}
	
	
	if($memberID == NULL || $campID == NULL || $memberID == '' || $campID == ''){
		return('error');
		exit;	
	}
	
	#GET MEMBER DATA
	
	$aMember 					= get_member($mLink, $memberID, 'all');
	$nameFirst					= $aMember['firstName'];
	$emailAddress				= $aMember['email'];
	
	if($nameFirst == NULL || $nameFirst == ''){
		$nameFirst = "Valued Member";	
	}
	
	$aEmailVars['NAME_FIRST'] 	= $nameFirst;
	
	#GET CAMPAIGN DATA
	$query = "
		SELECT *
		FROM ".$_SESSION['email_auto_campaigns_table']."
		WHERE camp_id=:camp_id
	";
	
	try{
		$rsCampaign = $mLink->prepare($query);
		$aValues = array(
			':camp_id' 	=> $campID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsCampaign->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aErrors[] = $error;
	
	$camp = $rsCampaign->fetch(PDO::FETCH_ASSOC);
	
	$emailTitle		= $camp['email_title'];
	$emailSubject	= $camp['email_subject'];
	$emailHeadline	= $camp['email_headline'];
	$emailBody		= $camp['email_body'];
	$emailClosing	= $camp['email_closing'];
	$tempID			= $camp['temp_id'];	
	$numRecipients	= $camp['recipients'];
	
	if($numRecipients == NULL){
		$numRecipients = 0;	
	}
	
	
	
	#get template data
	$query = "
		SELECT *
		FROM ".$_SESSION['email_templates_table']."
		WHERE temp_id=:temp_id
	";
	
	try{
		$rsEmailTemp = $mLink->prepare($query);
		$aValues = array(
			':temp_id' 	=> $tempID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsEmailTemp->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$emailTemp = $rsEmailTemp->fetch(PDO::FETCH_ASSOC);
	
	$aErrors[] = $preparedQuery;
	
	//return($emailTemp);
	
	$emailID		= $emailTemp['email_id'];
	$htmlSource		= $emailTemp['html_source'];
	$textSource		= $emailTemp['text_source'];
	$headersFrom	= $emailTemp['headers_from'];
	$headersBCC		= $emailTemp['headers_bcc'];
	$emailFooter	= $emailTemp['temp_footer'];
	
	//return($headersBCC);
	
	//If email content vars are not set, set them with the template defaults
	if($emailTitle == ''){
		$emailTitle		= $emailTemp['temp_title'];	
	}
	
	if($emailSubject == ''){
		$emailSubject = $emailTemp['temp_subject'];	
	}
	
	if($emailHeadline == ''){
		$emailHeadline = $emailTemp['temp_headline'];	
	}
	
	if($emailBody == ''){
		$emailBody = $emailTemp['temp_body'];
	}
	
	if($emailClosing == ''){
		$emailClosing = $emailTemp['temp_closing'];	
	}
	
	if($scheduleDate == ''){
		$scheduleDate = NULL;	
	}
	
	
	//+-----------------------------------------------------------------------------------------------------------+
	//|									START | Build Email Section
	//+-----------------------------------------------------------------------------------------------------------+
	
	
	
	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	
	
	// Additional headers
	if($headersFrom == '' || $headersFrom == NULL){
		$headers .= 'From: '.$_SESSION['system_email_from'].'' . "\r\n";
	}else{
		$headers .= 'From: '.$headersFrom.'' . "\r\n";	
	}
	
	//This is for testing
	/*if($headersBCC == '' || $headersBCC == NULL){
		if($_SESSION['system_email_bcc'] != ''){
			$headers .= 'Bcc: '.$_SESSION['system_email_bcc'].'' . "\r\n";  //Testing
		}
	}else{
		$headers .= 'Bcc: '.$headersBCC.'' . "\r\n";
	}*/
	
	
	#Include Email Source
	include('email/templates/'.$htmlSource);	
	
	
	//Fill in email message variables
	foreach($aEmailVars as $key => $value){
		$message = str_replace('['.strtoupper($key).']', $value, $message);
	}
	
	//Fill in email subject variables
	foreach($aEmailVars as $key => $value){
		$emailSubject = str_replace('['.strtoupper($key).']', $value, $emailSubject);
	}
	
	//Check to see if member has opted out of email
	$query = "
		SELECT ignore_emails
		FROM ".$_SESSION['members_settings_table']."
		WHERE member_id=:member_id
	";
	try{
		$rsGetSettings = $mLink->prepare($query);
		$aValues = array(
			':member_id'		=> $memberID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetSettings->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$settings = $rsGetSettings->fetch(PDO::FETCH_ASSOC);
	
	$aIgnoreEmails = explode('|', $settings['ignore_emails']);
	
	
						
	if(!in_array($emailID, $aIgnoreEmails)){
		
		//Insert Tracking Record
		$query = "
			INSERT INTO ".$_SESSION['email_auto_tracking_table']." (
				member_id,
				email,
				camp_id,
				timestamp
			)VALUES(
				:member_id,
				:email,
				:camp_id,
				UNIX_TIMESTAMP()
			)
		";
		try{
			$rsInsertTrack = $mLink->prepare($query);
			$aValues = array(
				':member_id'		=> $memberID,
				':email'			=> $emailAddress,
				':camp_id'			=> $campID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsInsertTrack->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$trackID	= $mLink->lastInsertId();
		
		//Generate Tracking Codes
		$openLink	= 'track=auto-open~'.$trackID;
		$clickLink	= 'track=auto-clicked~'.$trackID;
		$clickCode 	= 'click-'.$trackID;
		$openCode 	= 'open-'.$trackID;
		$bounceCode	= '<track_id>'.$trackID.'</track_id>';
		
		$sendMessage	= str_replace('[OPEN_LINK]', $openLink, $message);
		$sendMessage	= str_replace('[CLICK_LINK]', $clickLink, $sendMessage);
		$sendMessage 	= str_replace('[CLICK_CODE]', '&trackID='.$clickCode, $sendMessage);
		$sendMessage 	= str_replace('[OPEN_CODE]', '&trackID='.$openCode, $sendMessage);
		$sendMessage	= str_replace('[TRACK_ID]', $bounceCode, $sendMessage);
		
		//Go through the message and replace recipient based variables
		foreach($aEmailVars as $key => $value){
			$sendMessage = str_replace('['.strtoupper($key).']', $value, $sendMessage);
		}
		
		//Fill in email subject variables
		if(!empty($aEmailVars)){
			foreach($aEmailVars as $key => $value){
				$sendEmailSubject = str_replace('['.strtoupper($key).']', $value, $emailSubject);
			}
		}else{
			$sendEmailSubject = $emailSubject;
		}
		
		//return($headers);
		
		// Mail it
		if($sendEmail == true){
			if($emailAddress != '' && $emailAddress != 'NULL'){
				$aFuncDebug['emailStatus'] = 'Made it to email function';
				mail($emailAddress, $sendEmailSubject, $sendMessage, $headers);
			}
		}
		
		$aFuncDebug['sendEmail'] 		= ($sendEmail == true ? 'Send Email' : "Did not send email");
		$aFuncDebug['emailAddress'] 	= $emailAddress;
		$aFuncDebug['subject']			= $sendEmailSubject;
		//$aFuncDebug['message']			= $sendMessage;
		$aFuncDebug['headers']			= $headers;
		
		
		$numRecipients++;
		
		//update reportin tables
		$query = "
			UPDATE ".$_SESSION['email_auto_tracking_table']."
			SET sent=UNIX_TIMESTAMP(), html_message=:sendMessage
			WHERE track_id=:trackID
		";
		try{
			$rsInsertTrack = $mLink->prepare($query);
			$aValues = array(
				':sendMessage'		=> $sendMessage,
				':trackID'			=> $trackID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsInsertTrack->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		
		
	}else{
		//they are on the do not email database
		
			
	}
	
	
	//Update campaign
	$query = "
		UPDATE ".$_SESSION['email_auto_campaigns_table']."
		SET timestamp=UNIX_TIMESTAMP(), sent_timestamp=UNIX_TIMESTAMP(), recipients=:recipients
		WHERE camp_id=:camp_id
	";
	try{
		$rsUpdateCamp = $mLink->prepare($query);
		$aValues = array(
			':camp_id'			=> $campID,
			':recipients'		=> $numRecipients
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsUpdateCamp->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}

	return($aFuncDebug);
}

//+----------------------------------------------------------------------------------------+
//|								EMAIL Function
//+----------------------------------------------------------------------------------------+
function sendTestEmail($mLink, $aEmail, $aEmailVars){
	
	$campType		= $aEmail['camp_type'];
	$campStatus		= $aEmail['camp_status'];
	$campName		= $aEmail['camp_name'];
	$emailTitle		= $aEmail['email_title'];
	$emailSubject	= $aEmail['email_subject'];
	$emailHeadline	= $aEmail['email_headline'];
	$emailBody		= $aEmail['email_body'];
	$emailClosing	= $aEmail['email_closing'];
	$scheduleDate	= $aEmail['schedule_date'];
	$recipientList	= $aEmail['recipient_list'];
	$tempID			= $aEmail['temp_id'];
	$aRecipients	= $aEmail['recipients'];
	
	$query = "
		SELECT *
		FROM ".$_SESSION['email_templates_table']."
		WHERE temp_id=:temp_id
	";
	
	try{
		$rsEmailTemp = $mLink->prepare($query);
		$aValues = array(
			':temp_id' 	=> $tempID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsEmailTemp->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$emailTemp = $rsEmailTemp->fetch(PDO::FETCH_ASSOC);
	
	$aFuncDebug['temp_query'] = $preparedQuery;
	
	//return($emailTemp);
	
	$emailID		= $emailTemp['email_id'];
	$htmlSource		= $emailTemp['html_source'];
	$textSource		= $emailTemp['text_source'];
	$headersFrom	= $emailTemp['headers_from'];
	$headersBCC		= $emailTemp['headers_bcc'];
	$emailFooter	= $emailTemp['temp_footer'];
	
	//return($headersBCC);
	
	//If email content vars are not set, set them with the template defaults
	if($emailTitle == ''){
		$emailTitle		= $emailTemp['temp_title'];	
	}
	
	if($emailSubject == ''){
		$emailSubject = $emailTemp['temp_subject'];	
	}
	
	if($emailHeadline == ''){
		$emailHeadline = $emailTemp['temp_headline'];	
	}
	
	if($emailBody == ''){
		$emailBody = $emailTemp['temp_body'];
	}
	
	if($emailClosing == ''){
		$emailClosing = $emailTemp['temp_closing'];	
	}
	
	if($scheduleDate == ''){
		$scheduleDate = NULL;	
	}
	
	//+-----------------------------------------------------------------------------------------------------------+
	//|									START | Build Email Section
	//+-----------------------------------------------------------------------------------------------------------+
	
	
	
	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	
	
	// Additional headers
	if($headersFrom == '' || $headersFrom == NULL){
		$headers .= 'From: '.$_SESSION['system_email_from'].'' . "\r\n";
	}else{
		$headers .= 'From: '.$headersFrom.'' . "\r\n";	
	}
	
	//This is for testing
	/*if($headersBCC == '' || $headersBCC == NULL){
		if($_SESSION['system_email_bcc'] != ''){
			$headers .= 'Bcc: '.$_SESSION['system_email_bcc'].'' . "\r\n";  //Testing
		}
	}else{
		$headers .= 'Bcc: '.$headersBCC.'' . "\r\n";
	}*/
	$aFuncDebug['htmlSource']=$htmlSource;
	
	#Include Email Source
	include('email/templates/'.$htmlSource);	
	
	
	//Fill in email message variables
	foreach($aEmailVars as $key => $value){
		$message = str_replace('['.strtoupper($key).']', $value, $message);
	}
	
	//Fill in email subject variables
	foreach($aEmailVars as $key => $value){
		$emailSubject = str_replace('['.strtoupper($key).']', $value, $emailSubject);
	}

	foreach($aRecipients as $memberID=>$aMemberInfo){
						
		//return($headers);
		
		$aRecipientVars	= $aMemberInfo['recepient_vars'];
		$to				= $aMemberInfo['email_address'];
		
		$aFuncDebug['recipientVars']	= $aRecipientVars;
			
		//Go through the message and replace recipient based variables
		foreach($aRecipientVars as $key => $value){
			$sendMessage = str_replace('['.strtoupper($key).']', $value, $message);
		}
		
		//Fill in email subject variables
		if(!empty($aRecipientVars)){
			foreach($aRecipientVars as $key => $value){
				$sendEmailSubject = str_replace('['.strtoupper($key).']', $value, $emailSubject);
			}
		}else{
			$sendEmailSubject = $emailSubject;
		}
		
		
		$sendEmailSubject = 'TEST EMAIL - '.$sendEmailSubject;
		
		$aFuncDebug['to'] = $to;
		
		// Mail it
		$result = mail($to, $sendEmailSubject, $sendMessage, $headers);
		
		if(!$result){
			$aFuncDebug['mailErrors'][] = 'Did not send to '.$to;	
		}else{
			$aFuncDebug['mailErrors'][] = 'Success to '.$to;
		}
		
	}
	
	$aFuncDebug['recip'] = $aRecipients;
	
	return ($aFuncDebug);
	
}

function getEmailLists($mLink){
	
	$query = "
		SELECT *
		FROM email_lists
	";
	try{
		$rsLists = $mLink->prepare($query);
		$aValues = array();
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsLists->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aLists = array();
	
	while($list = $rsLists->fetch(PDO::FETCH_ASSOC)){
		
		$aLists[$list['list_id']] = array(
			'listName'		=> $list['list_name']
		);	
			
	}
	
	return($aLists);
	
}

function sendEmail($mLink, $aEmail, $aEmailVars, $createCampaign=true, $sendEmail=true){
	
	/*
	Example:
	
	$aEmail = array(
		'temp_id'			=> '15',
		'camp_id'			=> '0',
		'recipients'		=> $aRecipients,
		'email_subject'		=> $emailSubject,
		'email_headline'	=> $emailHeadline,
		'email_body'		=> $emailBody,
		'email_closing'		=> $emailClosing,
		'schedule_date'		=> $scheduleDate,
	);*/
	
	
	
	$aFuncDebug 		= array();
	
	$tempID				= $aEmail['temp_id'];
	$aRecipients		= $aEmail['recipients'];
	$aErrors			= array();
	$skipActiveCheck	= false; //this is needed for manager campaings
	$activeCheck		= $aEmail['active_check'];
	
	if($activeCheck == true){
		$skipActiveCheck = true;	
	}
	
	$aErrors[] 			= $tempID;
	
	
	//check to see if we need to create a campaign
	if($createCampaign == true){
		
		$campType		= $aEmail['camp_type'];
		$campStatus		= $aEmail['camp_status'];
		$campName		= $aEmail['camp_name'];
		$emailTitle		= $aEmail['email_title'];
		$emailSubject	= $aEmail['email_subject'];
		$emailHeadline	= $aEmail['email_headline'];
		$emailBody		= $aEmail['email_body'];
		$emailClosing	= $aEmail['email_closing'];
		$scheduleDate	= $aEmail['schedule_date'];
		$recipientList	= $aEmail['recipient_list'];
		
		
	}else{
		#Do not create a campaign
		
		$campID			= $aEmail['camp_id'];
		
		$query = "
			SELECT *
			FROM ".$_SESSION['email_campaigns_table']."
			WHERE camp_id=:camp_id
		";
		
		try{
			$rsCampaign = $mLink->prepare($query);
			$aValues = array(
				':camp_id' 	=> $campID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsCampaign->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$aErrors[] = $error;
		
		$camp = $rsCampaign->fetch(PDO::FETCH_ASSOC);
		
		$emailTitle		= $camp['email_title'];
		$emailSubject	= $camp['email_subject'];
		$emailHeadline	= $camp['email_headline'];
		$emailBody		= $camp['email_body'];
		$emailClosing	= $camp['email_closing'];
		$scheduleDate	= $camp['schedule_timestamp'];
		$tempID			= $camp['temp_id'];	
		
	}#end campaign check
	
	$query = "
		SELECT *
		FROM ".$_SESSION['email_templates_table']."
		WHERE temp_id=:temp_id
	";
	
	try{
		$rsEmailTemp = $mLink->prepare($query);
		$aValues = array(
			':temp_id' 	=> $tempID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsEmailTemp->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$emailTemp = $rsEmailTemp->fetch(PDO::FETCH_ASSOC);
	
	$aErrors[] = $preparedQuery;
	
	//return($emailTemp);
	
	$emailID		= $emailTemp['email_id'];
	$htmlSource		= $emailTemp['html_source'];
	$textSource		= $emailTemp['text_source'];
	$headersFrom	= $emailTemp['headers_from'];
	$headersBCC		= $emailTemp['headers_bcc'];
	$emailFooter	= $emailTemp['temp_footer'];
	
	//return($headersBCC);
	
	//If email content vars are not set, set them with the template defaults
	if($emailTitle == ''){
		$emailTitle		= $emailTemp['temp_title'];	
	}
	
	if($emailSubject == ''){
		$emailSubject = $emailTemp['temp_subject'];	
	}
	
	if($emailHeadline == ''){
		$emailHeadline = $emailTemp['temp_headline'];	
	}
	
	if($emailBody == ''){
		$emailBody = $emailTemp['temp_body'];
	}
	
	if($emailClosing == ''){
		$emailClosing = $emailTemp['temp_closing'];	
	}
	
	if($scheduleDate == ''){
		$scheduleDate = NULL;	
	}
	
	//+-----------------------------------------------------------------------------------------------------------+
	//|									START | CREATE CAMPAIGN
	//+-----------------------------------------------------------------------------------------------------------+
	if($createCampaign == true){
		
		$query = "
			INSERT INTO ".$_SESSION['email_campaigns_table']." (
				member_id,
				camp_type,
				camp_name,
				camp_status,
				temp_id,
				email_title,
				email_subject,
				email_headline,
				email_body,
				email_closing,
				recipient_list,
				recipients,
				timestamp
			)VALUES(
				:member_id,
				:camp_type,
				:camp_name,
				:camp_status,
				:temp_id,
				:email_title,
				:email_subject,
				:email_headline,
				:email_body,
				:email_closing,
				:recipient_list,
				:recipients,
				UNIX_TIMESTAMP()
			)
		";
		try{
			$rsInsertCamp = $mLink->prepare($query);
			$aValues = array(
				':member_id'		=> $_SESSION['member_id'],
				':camp_type'		=> $campType,
				':camp_name'		=> $campName,
				':camp_status'		=> $campStatus,
				':temp_id'			=> $tempID,
				':email_title'		=> $emailTitle,
				':email_subject'	=> $emailSubject,
				':email_headline'	=> $emailHeadline,
				':email_body'		=> $emailBody,
				':email_closing'	=> $emailClosing,
				':recipient_list'	=> $recipientList,
				':recipients'		=> count($aRecipients)
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsInsertCamp->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
				
		$campID = $mLink->lastInsertId();
		
		$aErrors[] = $error;
		
	}
	
	//+-----------------------------------------------------------------------------------------------------------+
	//|									START | Build Email Section
	//+-----------------------------------------------------------------------------------------------------------+
	
	
	
	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	
	
	// Additional headers
	if($headersFrom == '' || $headersFrom == NULL){
		$headers .= 'From: '.$_SESSION['system_email_from'].'' . "\r\n";
	}else{
		$headers .= 'From: '.$headersFrom.'' . "\r\n";	
	}
	
	//This is for testing
	/*if($headersBCC == '' || $headersBCC == NULL){
		if($_SESSION['system_email_bcc'] != ''){
			$headers .= 'Bcc: '.$_SESSION['system_email_bcc'].'' . "\r\n";  //Testing
		}
	}else{
		$headers .= 'Bcc: '.$headersBCC.'' . "\r\n";
	}*/
	
	
	#Include Email Source
	include('email/templates/'.$htmlSource);	
	
	
	//Fill in email message variables
	foreach($aEmailVars as $key => $value){
		$message = str_replace('['.strtoupper($key).']', $value, $message);
	}
	
	//Fill in email subject variables
	foreach($aEmailVars as $key => $value){
		$emailSubject = str_replace('['.strtoupper($key).']', $value, $emailSubject);
	}
	
	
	if($sendEmail == false){
		//Update campaign
		$query = "
			UPDATE ".$_SESSION['email_campaigns_table']."
			SET html_sent_message=:html_message, timestamp=UNIX_TIMESTAMP()
			WHERE camp_id=:camp_id
		";
		try{
			$rsUpdateCamp = $mLink->prepare($query);
			$aValues = array(
				':camp_id'			=> $campID,
				':html_message'		=> $message,
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdateCamp->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}	
	}
	
	//+-----------------------------------------------------------------------------------------------------------+
	//|									START | SEND Email Section
	//+-----------------------------------------------------------------------------------------------------------+
	#check to see if there is a campaign id or a template id
	
	$aUnsubscribed = array();
	
	if($sendEmail == true){
		if($tempID != ''){
			if($campID != ''){
				
				
				
				#check to see if the scheduleDate has been exceeeded
				if($scheduleDate < time() || $scheduleDate == NULL || $scheduleDate == ''){
					
					
					
					foreach($aRecipients as $memberID=>$aMemberInfo){
						
						//return($headers);
						
						$aRecipientVars	= $aMemberInfo['recepient_vars'];
						$to				= $aMemberInfo['email_address'];
						
						$aFuncDebug['recipientVars']	= $aRecipientVars;
						
						$memberActive 	= get_member($mLink, $memberID, 'active');
						
						//Check to see if recipient is active
						if($memberActive == '1' || $memberID == 'test' || $skipActiveCheck == true){
						
							//Check to see if member has opted out of email
							$query = "
								SELECT ignore_emails
								FROM ".$_SESSION['members_settings_table']."
								WHERE member_id=:member_id
							";
							try{
								$rsGetSettings = $mLink->prepare($query);
								$aValues = array(
									':member_id'		=> $memberID
								);
								$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
								$rsGetSettings->execute($aValues);
							}
							
							catch(PDOException $error){
								// Log any error
								file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
							}
							$settings = $rsGetSettings->fetch(PDO::FETCH_ASSOC);
							
							$aIgnoreEmails = explode('|', $settings['ignore_emails']);
							
							
												
							if(!in_array($emailID, $aIgnoreEmails)){
								
								//Insert Tracking Record
								$query = "
									INSERT INTO ".$_SESSION['email_tracking_table']." (
										member_id,
										manager_id,
										email,
										camp_id,
										sent,
										timestamp
									)VALUES(
										:member_id,
										:manager_id,
										:email,
										:camp_id,
										UNIX_TIMESTAMP(),
										UNIX_TIMESTAMP()
									)
								";
								try{
									$rsInsertTrack = $mLink->prepare($query);
									$aValues = array(
										':member_id'		=> $memberID,
										':manager_id'		=> $_SESSION['member_id'],
										':email'			=> $to,
										':camp_id'			=> $campID
									);
									$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
									$rsInsertTrack->execute($aValues);
								}
								
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}
								
								$trackID	= $mLink->lastInsertId();
								
								//Generate Tracking Codes
								$openLink	= 'track=open~'.$trackID;
								$clickCode 	= 'click-'.$trackID;
								$openCode 	= 'open-'.$trackID;
								$bounceCode	= '<track_id>'.$trackID.'</track_id>';
								$subscriptionLink = 'track=subscription~'.$trackID.'~'.$aRecipientVars['manager_username'].'~'.$aRecipientVars['email_code'].'~'.$to;
								
								$sendMessage	= str_replace('[OPEN_LINK]', $openLink, $message);
								$sendMessage 	= str_replace('[CLICK_CODE]', '&trackID='.$clickCode, $sendMessage);
								$sendMessage 	= str_replace('[OPEN_CODE]', '&trackID='.$openCode, $sendMessage);
								$sendMessage	= str_replace('[TRACK_ID]', $bounceCode, $sendMessage);
								$sendMessage 	= str_replace('[SUB_LINK]', $subscriptionLink, $sendMessage);
								
								//Go through the message and replace recipient based variables
								foreach($aRecipientVars as $key => $value){
									$sendMessage = str_replace('['.strtoupper($key).']', $value, $sendMessage);
								}
								
								//Fill in email subject variables
								if(!empty($aRecipientVars)){
									foreach($aRecipientVars as $key => $value){
										$sendEmailSubject = str_replace('['.strtoupper($key).']', $value, $emailSubject);
									}
								}else{
									$sendEmailSubject = $emailSubject;
								}
								
								//return($headers);
								
								// Mail it
								if($sendEmail == true){
									mail($to, $sendEmailSubject, $sendMessage, $headers);
								}
							}else{
								$aUnsubscribed[] = $to;
								unset($aRecipients[$memberID]);	
							}
						
						}else{
							$aUnsubscribed[] = $to;
							unset($aRecipients[$memberID]);
						}#end check if member has excluded themselves
						
						
					}
					
					//Send email to BCC with statistics
					$headers2  	= 'MIME-Version: 1.0' . "\r\n";
					$headers2 	.= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
					$headers2 	.= 'From: '.$headersFrom.'' . "\r\n";
					//$headers2 .= 'Bcc: ken@marketocracy.com, jeff.saunders@marketocracy.com' . "\r\n";
					
					$subject2 	= 'Marketocracy Campaign Notification | '.$campID;
					
					include('email/templates/bcc-campaign-info.php');
					
					if($sendEmail == true){
						mail($headersBCC, $subject2, $adminMessage, $headers2);
					}
					
					//Update campaign
					$query = "
						UPDATE ".$_SESSION['email_campaigns_table']."
						SET camp_status='sent', html_sent_message=:html_message, timestamp=UNIX_TIMESTAMP(), sent_timestamp=UNIX_TIMESTAMP(), unsubscribes=:unsubscribes, recipients=:recipients
						WHERE camp_id=:camp_id
					";
					try{
						$rsUpdateCamp = $mLink->prepare($query);
						$aValues = array(
							':camp_id'			=> $campID,
							':html_message'		=> $sendMessage,
							':unsubscribes'		=> count($aUnsubscribed),
							':recipients'		=> count($aRecipients)
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsUpdateCamp->execute($aValues);
					}
					
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
					return($aFuncDebug);
				}
				
			}else{
				$aErrors[] = 'No Campaign ID';		
			}#campaign id check
		}else{
			$aErrors[] = 'No Template ID';
		}#template id check
	}
	return $aErrors;
}

//+----------------------------------------------------------------------------------------+
//|								Tracking Function
//+----------------------------------------------------------------------------------------+
function trackLink($mLink, $trackID, $url){
	
	if($trackID != ''){
		$aTrackID	= explode('-',$trackID);
	
		$trackType	= $aTrackID[0];
		$trackID	= $aTrackID[1];
		
		$ipAddress	= get_ip();
		
		
		switch($trackType){
			
			case 'click':
				
				$query = "
					UPDATE email_tracking
					SET clicked='1', clicked_ip=:ip, opened='1', opened_ip=:ip, timestamp=UNIX_TIMESTAMP()
					WHERE track_id=:track_id
				";
				try{
					$rsUpdateTrack = $mLink->prepare($query);
					$aValues = array(
						':track_id'		=> $trackID,
						':ip'			=> $ipAddress
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsUpdateTrack->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
			break;
		}
	}
		
}

//+----------------------------------------------------------------------------------------+
//|								Action Center Functions
//+----------------------------------------------------------------------------------------+
function mtr_subdomain($mLink, $memberID, $subdomain=NULL, $action){
	
	//be sure we have access to the cryptology functions
	require_once("../../../secure/crypto.php");
	
	//Reaasons to fail
	/*
	1 = username exists
	2 = invalid characters
	3 = obscenities
	4 = users current subdomain/username
	*/
	
	switch($action){
		
		case 'get-subdomain':
			
			$query = "
				SELECT *
				FROM ".$_SESSION['mtr_subdomain_table']."
				WHERE member_id=:member_id
			";
			try {
				$rsSubdomain = $mLink->prepare($query);
				$aValues = array(
					':member_id'	=> $_SESSION['member_id']
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			//die($preparedQuery);
				$rsSubdomain->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$getSubdomain = $rsSubdomain->fetch(PDO::FETCH_ASSOC);
			
			$subdomain = $getSubdomain['subdomain'];
			
			if($subdomain == ''){
				$subdomain = $_SESSION['username'];	
			}
			
			return $error;
			
		break;
		
		case 'check-subdomain':
			
			
			
			$username 			= trim(strtolower($subdomain));
			$username			= str_replace(' ','',$username);
			
			$subdomainPass		= true;
			
			//Check for invalid characters
			/*function isValid($str) {
				return preg_match('/[a-z]/', $str);
			}
			
			
			
			if(isValid($username) == true){
				$subdomainPass 	= false;
				$failReason		= 2;	
			}*/
			
			if (!preg_match('/^[A-Za-z0-9]*$/', $username)) {
    			#your string is good
				$subdomainPass 	= false;
				$failReason		= 2;
			}
			
			//check to see if it is the existing subdomain
			if($subdomainPass == true){
				
				$query = "
					SELECT *
					FROM ".$_SESSION['mtr_subdomain_table']."
					WHERE member_id=:member_id
				";
				try {
					$rsSubdomain = $mLink->prepare($query);
					$aValues = array(
						':member_id'	=> $_SESSION['member_id']
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				//die($preparedQuery);
					$rsSubdomain->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				$subdomain = $rsSubdomain->fetch(PDO::FETCH_ASSOC);
				
				$checkSubdomain = $subdomain['subdomain'];
				
				if($checkSubdomain == $username){
					
					$subdomainPass 	= false;
					$failReason 	= 4;
						
				}elseif($username == $_SESSION['username']){
					
					$subdomainPass 	= false;
					$failReason		= 4;
					
				}
				
					
			}
			
			if($subdomainPass == true){
				//Check the clear_passwords table to check against old usernames, this will prevent duplicates if old members get brought over later
				$query = "
					SELECT *
					FROM clear_passwords
					WHERE username=:username
				";
				try {
					$rsOldUsername = $mLink->prepare($query);
					$aValues = array(
						':username'	=> $username
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				//die($preparedQuery);
					$rsOldUsername->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				// Username already taken
				if ($rsOldUsername->rowCount() > 0){
					
					$subdomainPass 	= false;
					$failReason		= 1;
				}
			}
			
			//if check passed first checkpoint continue
			if($subdomainPass == true){
				
				// Encrypt the passed username for DB lookup
				$encrypted_username = encrypt($username);
				
				//Look up the encrypted username
				$query = "
					SELECT *
					FROM ".$_SESSION['auth_table']."
					WHERE username = '$encrypted_username'
				";
				try {
					$rsUsername = $mLink->prepare($query);
					$aValues = array(
						':encrypted_username'	=> $encrypted_username
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				//die($preparedQuery);
					$rsUsername->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				// Username already taken
				if ($rsUsername->rowCount() > 0){
					$subdomainPass = false;
					$failReason		= 1;
				}
				
			}
			
			//If seccond checkpoing passed, continue
			if($subdomainPass == true){
			
				if ($_SESSION['username_obscenity_checking'] == "on"){
					// Get list of restricted phrases (obscenities)
					// *** Expand language support later ***
					$query = "
						SELECT *
						FROM ".$_SESSION['obscenities_table']."
						WHERE language = :language
					";
					//echo $query;
					try {
						$rsObscenities = $mLink->prepare($query);
						$aValues = array(
							':language'	=> "en"
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				//die($preparedQuery);
						$rsObscenities->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					while ($obscenity = $rsObscenities->fetch(PDO::FETCH_ASSOC)){
						$sBad = $obscenity['obscenity'];
						if (stripos($username, $sBad) !== false){
							
							$subdomainPass	= false;
							$failReason		= 3;
							
						}
						if (stripos($username, str_replace(' ', '', $sBad)) !== false){
							
							$subdomainPass	= false;
							$failReason		= 3;
							
						}
					}
				}
				
			}//end subdomainExists check
			
			if($subdomainPass == true){
				return('<div class="alert alert-success"><strong><i class="icon-ok"></i> '.$username.'</strong>.mytrackrecord.com - Available!</div>');	
			}else{
				
				switch($failReason){
				
					case 1: return('<div class="alert alert-danger"><strong><i class="icon-remove"></i> '.$username.'</strong>.mytrackrecord.com | Subdomain is Unavailable!</div>'); break;
					
					case 2: return('<div class="alert alert-danger"><strong><i class="icon-remove"></i> '.$username.'</strong>.mytrackrecord.com | Invalid Characters in subdomain, only letters are allowed.</div>'); break;
					
					case 3: return('<div class="alert alert-danger"><strong><i class="icon-remove"></i> '.$username.'</strong>.mytrackrecord.com | Subdomain contains obscenity.</div>'); break;

					case 4: return('');break;
				}
				
			}
			
		break;
			
	}
		
}

//+----------------------------------------------------------------------------------------+
//|								Fund Pricing Function
//+----------------------------------------------------------------------------------------+
function fundPrice($mLink, $fundID, $process){
	
	$today = date('Ymd', time());
	
	#check to see if there is a current record for this fund
	$query = "
		SELECT *
		FROM ".$_SESSION['fund_price_table']."
		WHERE fund_id=:fund_id
		ORDER BY timestamp DESC
		LIMIT 1
	";
	try{
		$rsFundPrice = $mLink->prepare($query);
		$aValues = array(
			':fund_id'		=> $fundID		
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsFundPrice->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$price = $rsFundPrice->fetch(PDO::FETCH_ASSOC);
	
	$recordDate		= date('Ymd',$price['timestamp']);
	
	if($recordDate != $today){
		
		$query = "
			INSERT INTO ".$_SESSION['fund_price_table']." (
				fund_id,
				date,
				timestamp,
				cash
			)VALUES(
				:fund_id,
				:date,
				UNIX_TIMESTAMP(),
				:cash
			)
		";
		
	}else{
		
		$cash			= $price['cash'];
		$date			= $price['date'];
		$availableCash	= $price['available_cash'];
		$stockValue		= $price['stock_value'];
		$totalValue		= $price['total_value'];
		$nav			= $price['nav'];
			
	}
}

function checkStratBuildStatus($mLink, $fundID){
	
	$query = "
		SELECT processing
		FROM members_fund
		WHERE fund_id=:fund_id
	";
	try{
		$rsProcessing = $mLink->prepare($query);
		$aValues = array(
			':fund_id'		=> $fundID		
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsProcessing->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$processing = $rsProcessing->fetch(PDO::FETCH_ASSOC);
	
	$process = $processing['processing'];
	
	if($process == 1){
		return true;	
	}else{
		return false;	
	}
	
}

//+----------------------------------------------------------------------------------------+
//|								Member Record Check Function
//+----------------------------------------------------------------------------------------+
function checkRecords($mLink, $memberID, $method='all'){
	
	//$aTransID[] = 'test';
	
	$queryInt	= 0;
	
	$today 		= date('Ymd',time());
	$yesterday	= date('Ymd', strtotime('-1 day', time()));
	$aFunds		= get_fund_symbols($mLink, $memberID, $item='funds', '1');
	$username	= get_member($mLink, $memberID, 'username');
	
	
	
	$aTransID['dates']['today'] = $today;
	$aTransID['dates']['yesterday'] = $yesterday;
	//$aTransID[] = $aFunds;
	
	$aPositions 	= array();
	$aStratPositions	= array();
	
	foreach($aFunds as $fundID=>$fundSymbol){	
		
		$fundIdent = $fundID.'|'.$fundSymbol;
		
		#check to see if strat build is running
		$stratProcess = checkStratBuildStatus($mLink, $fundID);
		
		if($stratProcess == true){
			$pause = true;
			$loopCnt = 0;
			
			while($pause){
				
				sleep(1);
				
				$loopCnt++;
				
				$pause = checkStratBuildStatus($mLink, $fundID);
				
				#timeout the loop
				if($loopCnt >= 60){
					$pause = false;	
				}
					
			}
		}
		#end check for strat build processing
		
		#check price history record
		if($method == 'all' || $method = 'priceRun'){
			
			$query = "
				SELECT timestamp
				FROM ".$_SESSION['fund_pricing_table']."
				WHERE fund_id=:fund_id
				ORDER BY timestamp DESC
				LIMIT 1
			";
			try{
				$rsPriceHistory = $mLink->prepare($query);
				$aValues = array(
					':fund_id'		=> $fundID		
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsPriceHistory->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$priceRecord = $rsPriceHistory->fetch(PDO::FETCH_ASSOC);
			
			$priceRecordDate	= date('Ymd',$priceRecord['timestamp']);
			
			$aTransID['record_dates'][] = $priceRecordDate;
			
			if($priceRecordDate != $today){
				
				$yesterday = date('Ymd',strtotime('-1 days', time()));
				
				$port 	= rand(52100, 52499);
				$query 	= "priceRun|".$username."|".$fundID."|".$fundSymbol."|".$yesterday; 
				
				$aMethodVars[] = array(
					'method'	=> 'priceRun',
					'source'	=> 'Login Member Check |system-functions.php process-auth.php | function: checkRecords',
					'api'	=> '3',
					'username'	=> $username,
					'fund_id' => $fundID,
					'fund_symbol' => $fundSymbol,
					'start_date' => $yesterday, #start date of period to grab records
					'end_date' => $yesterday, #end date of period to grab records ( do no exceed 15 days)
					'group'	=> date('Y-m-d-h-i').'_login-check' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
				);
				$aTransID['transIDs'][] = legacy_api($mLink, $aMethodVars, true, 'transID');
				//$aTransID[] = $aMethodVars;
				
				//Run check to see if we got the new record
				
				//End run check for new record	
			}
			
		}
		//End PriceRun Check
		
		#check price history record
		if($method == 'all' || $method = 'fixShares'){
			
			#GET LEDGER POSITION INFO
			$aPositions[$fundIdent]['ledgerPos'] = array('no-record');
			
			$query = "
				SELECT * 
				FROM members_fund_positions
				WHERE fund_id=:fund_id AND date=:date
			";
			
			$queryInt++;
			$queryID = date('Y-m-d-h-m-i',time()).'|'.$queryInt;
			try{
				$rsGetPos = $mLink->prepare($query);
				$aValues = array(
					':fund_id'		=> $fundID,	
					':date'			=> $yesterday
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetPos->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$aTransID['queries']['fund-positions'][$queryID]['query'] = $preparedQuery;
			$aTransID['queries']['fund-positions'][$queryID]['error'] = $error;
			
			while($ledgerPos = $rsGetPos->fetch(PDO::FETCH_ASSOC)){
				
				unset($aPositions[$fundIdent]['ledgerPos'][0]);
				$aPositions[$fundIdent]['ledgerPos'][$ledgerPos['stockSymbol']] = $ledgerPos['shares'];
				
			}
			#END GET LEDGER POSITION INFO
			
			
			
			#START GET STRAT POS INFO
			$aPositions[$fundIdent]['stratPos'] = array('no-records');
			
			$query = "
				SELECT * 
				FROM members_fund_stratification_basic
				WHERE fund_id=:fund_id
			";
			
			$queryInt++;
			$queryID = date('Y-m-d-h-m-i',time()).'|'.$queryInt;
			try{
				$rsGetPos = $mLink->prepare($query);
				$aValues = array(
					':fund_id'		=> $fundID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetPos->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$aTransID['queries']['stratification'][$queryID]['query'] = $preparedQuery;
			$aTransID['queries']['stratification'][$queryID]['error'] = $error;
			
			while($stratPos = $rsGetPos->fetch(PDO::FETCH_ASSOC)){
				
				unset($aPositions[$fundIdent]['stratPos'][0]);
				$aPositions[$fundIdent]['stratPos'][$stratPos['stockSymbol']] = $stratPos['totalShares'];
				
			}
			#END GET STRAT POS INFO	
			
			
			#START GET RECENTLY CLOSED TICKETS FOR TODAY
			$aPositions[$fundIdent]['tradePos'] = array('no-records');
			
			$query = "
				SELECT * 
				FROM members_fund_trades
				WHERE fund_id=:fund_id AND closed=:date
			";
			$queryInt++;
			$queryID = date('Y-m-d-h-m-i',time()).'|'.$queryInt;
			try{
				$rsGetPos = $mLink->prepare($query);
				$aValues = array(
					':fund_id'		=> $fundID,
					':date'			=> $today
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetPos->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
				
			$aTransID['queries']['fund-trades'][$queryID]['query'] = $preparedQuery;
			$aTransID['queries']['fund-trades'][$queryID]['error'] = $error;
			
			while($tradePos = $rsGetPos->fetch(PDO::FETCH_ASSOC)){
				
				unset($aPositions[$fundIdent]['tradePos'][0]);
				
				if($tradePos['buyOrSell'] == 'Buy' || $tradePos['buyOrSell'] == 'buy'){
					$shareQTY = 0 + $tradePos['sharesFilled'];
				}else{
					$shareQTY = 0 - $tradePos['sharesFilled'];
				}
				
				$aPositions[$fundIdent]['tradePos'][$tradePos['stockSymbol']][] = $shareQTY;
				
			}
			#END GET RECENTLY CLOSED TICKETS FOR TODAY
			
			
			
		}
				
	}//end foreach fundID
	
	
	
	$aFixPos = array();
	
	#loop through current strat and fix for each fund
	foreach($aPositions as $fundInfo=>$aPos){
		
		$aCheckDiff = array_diff_assoc($aPos['ledgerPos'],$aPos['stratPos']);
		
		$aPositions[$fundInfo]['difference'] = $aCheckDiff;
		
		$aFixPos[$fundInfo] = $aCheckDiff;
		/*foreach($aPos['stratPos'] as $stockSymbol=>$shares){
			
			#check for shorts
			if($shares < 0){
				$aFixPos[$fundID][] = $stockSymbol;
			}
			#end check for shorts
			
			#check for qty errors
			if($shares != $aPos['ledgerPos'][$stockSymbol] && $shares != 0 && $shares > 0){
				
				#check todays trades
				$tradeShareQty = 0;
				
				foreach($aPos['tradePos'][$stockSymbol] as $key=>$tradeShares){
					
					$tradeShareQty = $tradeShareQty + $tradeShares;
						
				}
				
				$newLedgerShares = $aPos['ledgerPos'][$stockSymbol] + $tradeShareQty;
				
				if($newLedgerShares != $shares){
					$aFixPos[$fundID][] = $stockSymbol;
				}
				#end check todays trades
				
			}
			#end check for qty errors
				
		}#end foreach $aPos['stratPos']*/
			
	}#end foreach $aPositions
	#end loop through each funds strat
	
	#fix incorect positions
	foreach($aFixPos as $fundInfo=>$aPos2){
		
		$aFundInfo = explode('|', $fundInfo);
		
		$fundID = $aFundInfo[0];
		$fundSymbol	= $aFundInfo[1];
		
		$inceptionDate = get_funds($mLink, $fundID, 'trueInceptDate', false);
		$inceptDate		= date('Ymd', $inceptionDate);
		
		
		
		foreach($aPos2 as $stockSymbol=>$shareQTY){
			
			unset($aMethodVars);
			
			#delete existing trade records
			$query = "
				DELETE
				FROM members_fund_trades
				WHERE fund_id=:fund_id AND stockSymbol=:stockSymbol
			";
			
			$queryInt++;
			$queryID = date('Y-m-d-h-m-i',time()).'|'.$queryInt;
			try{
				$rsDelete = $mLink->prepare($query);
				$aValues = array(
					':fund_id'		=> $fundID,
					':stockSymbol'	=> $stockSymbol
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsDelete->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			#end delete existing trade records
			
			$aTransID['queries']['delete-trades'][$queryID]['query'] = $preparedQuery;
			$aTransID['queries']['delete-trades'][$queryID]['error'] = $error;
			
			#get new trades for postion
			$aMethodVars[] = array(
				'method'		=> 'tradesForPosition',
				'source'		=> 'Login Audit |system-functions.php process-auth.php | function: checkRecords',
				'api'			=> '3',
				'username'		=> $username,
				'fund_id' 		=> $fundID,
				'fund_symbol' 	=> $fundSymbol,
				'stock_symbol'	=> $stockSymbol,
				'from_date'		=> $inceptDate,
				'group'			=> date('Y-m-d-h-i').'_login-check' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
			);
			$aTransID['transIDs'][] = legacy_api($mLink, $aMethodVars, true, 'transID');
			#end get trades for postion
			
		}
			
	}
	#end fix incorrect positions
	$aTransID['positions'] = $aPositions;
	$aTransID['fixPos'] = $aFixPos;
	
	
	return($aTransID);
			
}


//+----------------------------------------------------------------------------------------+
//|								COMMUNITY PROFILE FUNCTIONS
//+----------------------------------------------------------------------------------------+
function memberTracker($dbLink, $memberID, $switch='mytrackrecord'){
	
	$now		= time();
	$today		= mktime(0,0,0,date('m',$now),date('d',$now),date('Y',$now));
	$lastWeek	= strtotime('-7 day', $now);
	$lastMonth	= strtotime('-30 day', $now);
	
	
	$query = "
		SELECT *
		FROM ".$_SESSION['fund_tracking_table']."
		WHERE member_id=:member_id
	";
	try{
		$rsGetTracking = $dbLink->prepare($query);
		$aValues = array(
			':member_id'	=> $memberID			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetTracking->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aTackers 		= array();
	$aUpdates		= array();
	$aArticles		= array();
	$aUnsubs		= array();
	$aSpam			= array();
	$aTrackedFunds	= array();
	$aToday			= array();
	$aLastWeek		= array();
	$aLastMonth		= array();
	
	
	
	while($tracking = $rsGetTracking->fetch(PDO::FETCH_ASSOC)){
		$validTracker	= true;
		
		$aTrackFunds	= explode('|',$tracking['track_funds']);
		$updates		= $tracking['manager_updates'];
		$articles		= $tracking['manager_articles'];
		$spam			= $tracking['spam'];
		$unsub			= $tracking['unsubscribe'];
		$subscribed		= $tracking['timestamp'];
		$email			= $tracking['email'];
		
		if($spam != '0' || $unsub != '0'){
			$validTracker = false;	
		}
		
		//$aTest[$email] = date('Y-m-d h:i',$subscribed);
		
		if($spam == '1'){
			$aSpam[$email] = 1;	
		}
		
		if($unsub == '1'){
			$aUnsubs[$email] = 1;	
		}
		
		if($validTracker == true){
			
			#build subscribed time arrays
			if($subscribed >= $today){
				$aToday[$email] = 1;	
			}
			if($subscribed >= $lastWeek && $subscribed < $today){
				$aLastWeek[$email] = 1;	
			}
			if($subscribed >= $lastMonth && $subscribed < $today){
				$aLastMonth[$email] = 1;	
			}
			
			#build array to count trackers
			$aTrackers[$email] = 1;
			
			#build array to count how many subscribers for each fund
			foreach($aTrackFunds as $key=>$fundID){
				$aTrackedFunds[$fundID][] = $email;
			}
			
			#build array to count how many subscribers want email updates
			if($updates == '1'){
				$aUpdates[$email] = 1;	
			}
			
			#build array to count how many subscribers want article notifications
			if($articles == '1'){
				$aArticles[$email] = 1;	
			}
			
		}
		
	}
	
	foreach($aTrackedFunds as $fundID=>$aEmails){
		
		$numTrack = count($aEmails);
		
		$aTrackedFunds[$fundID] = $numTrack;
			
	}
	
	$aTrackingInfo = array(
		'trackedFunds'	=> $aTrackedFunds,
		'totalTrackers'	=> count($aTrackers),
		'receiveUpdates'	=> count($aUpdates),
		'receiveArticles'	=> count($aArticles),
		'subsToday'			=> count($aToday),
		'subsLastWeek'		=> count($aLastWeek),
		'subsLastMonth'		=> count($aLastMonth),
		'unsubscribes'		=> count($aUnsubs),
		'spam'				=> count($aSpam)/*,
		'now'				=> $now.' | '.date('Y-m-d h:i',$now),
		'today'				=> $today.' | '.date('Y-m-d h:i',$today),
		'lastWeek'			=> $lastWeek.' | '.date('Y-m-d h:i',$lastWeek),
		'lastMonth'			=> $lastMonth.' | '.date('Y-m-d h:i',$lastMonth),
		'test'				=> $aTest*/
	);
	
	
	return($aTrackingInfo);	
}

function memberProspects($dbLink, $memberID){
	
	$query = "
		SELECT track_id
		FROM info_request
		WHERE track_id LIKE :memberID
	";
	
	try{
		$rsTrack = $dbLink->prepare($query);
		$aValues = array(':memberID'=>'%'.$memberID.'%');
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsTrack->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aTrackList = array();
	
	while($track = $rsTrack->fetch(PDO::FETCH_ASSOC)){
		
			$aTrackList[] = explode(',',$track['track_id']);
		
	}
	
	$trackCounter = 0;
	
	foreach($aTrackList as $key=>$aTracked){
		if(in_array($memberID, $aTracked)){
			$trackCounter++;	
		}
	}
	
	return($trackCounter);
}


function formatPhone($phoneNumber){
	
	$phoneNumber = trim(preg_replace("/[^0-9]/", "", $phoneNumber));
	
	if(strlen($phoneNumber) > 10) {
        $countryCode = substr($phoneNumber, 0, strlen($phoneNumber)-10);
        $areaCode = substr($phoneNumber, -10, 3);
        $nextThree = substr($phoneNumber, -7, 3);
        $lastFour = substr($phoneNumber, -4, 4);

        $phoneNumber = '+'.$countryCode.' ('.$areaCode.') '.$nextThree.'-'.$lastFour;
    }
    else if(strlen($phoneNumber) == 10) {
        $areaCode = substr($phoneNumber, 0, 3);
        $nextThree = substr($phoneNumber, 3, 3);
        $lastFour = substr($phoneNumber, 6, 4);

        $phoneNumber = '('.$areaCode.') '.$nextThree.'-'.$lastFour;
    }
    else if(strlen($phoneNumber) == 7) {
        $nextThree = substr($phoneNumber, 0, 3);
        $lastFour = substr($phoneNumber, 3, 4);

        $phoneNumber = $nextThree.'-'.$lastFour;
    }

    return $phoneNumber;
	
}

function fundAssets($dbLink, $memberID){
	
	/*$query = "
		SELECT *
		FROM ".$_SESSION['fund_table']."
		WHERE member_id=:member_id
	";*/
	$query = "
		SELECT mf.*, mfc.aum
		FROM members_fund AS mf
		INNER JOIN members_fund_composite AS mfc ON mfc.fund_id=mf.fund_id
		WHERE mf.member_id=:member_id AND mfc.unix_date=(SELECT MAX(unix_date) FROM members_fund_composite as mfc2 WHERE mfc2.fund_id=mf.fund_id)
	";
	try{
		$rsGetAssets = $dbLink->prepare($query);
		$aValues = array(
			':member_id'	=> $memberID			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetAssets->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aFundAssets 	= array();
	$assetsTotal	= 0;
	
	$hasAssets = false;
	while($assets = $rsGetAssets->fetch(PDO::FETCH_ASSOC)){
		
		$fundID		= $assets['fund_id'];
		$assetsUM	= $assets['aum'];
		
		if($assetsUM != NULL){
			$aFundAssets[$memberID]['funds'][$fundID] = $assetsUM;
			
			$assetsTotal = ($assetsTotal + $assetsUM);
			
			$hasAssets = true;
		}
		
	}
	
	$aFundAssets[$memberID]['assetsTotal'] = $assetsTotal;
	
	if($hasAssets == true){
		return($aFundAssets);
	}else{
		return(false);	
	}
};

function pageViews($dbLink, $pageID, $identifier, $switch='unique-member'){
	
	
	switch($switch){
		
		case 'all':
			
			$now			= time();
			$today			= mktime(0,0,0,date('m',$now),date('d',$now),date('Y',$now));
			$lastWeek		= strtotime('-7 day', $now);
			$lastMonth		= strtotime('-30 day', $now);
			$lastThreeMonth	= strtotime('-90 day', $now);
			$aViewData		= array();
			
			//Unique Views
			$query = "
				SELECT *
				FROM ".$_SESSION['page_tracking_table']."
				WHERE page_id=:page_id AND member_id=:member_id
				ORDER BY timestamp DESC
			";
			try{
				$rsGetViews = $dbLink->prepare($query);
				$aValues = array(
					':page_id'		=> $pageID,
					':member_id'	=> $identifier			
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetViews->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$aViews 		= array();
			$cntTotalViews 	= 0;
			
			while($views = $rsGetViews->fetch(PDO::FETCH_ASSOC)){
				
				$cntTotalViews++;
				
				$aViews[$views['ip_address']] = array(
					'pageID'		=> $views['page_id'],
					'fundID'		=> $views['fund_id'],
					'memberID'		=> $views['member_id'],
					'agent'			=> $views['agent'],
					'timestamp'		=> $views['timestamp']
				);
						
			}
			
			foreach($aViews as $ipAddress=>$viewInfo){
				
				#build subscribed time arrays
				if($viewInfo['timestamp'] >= $today){
					$aToday[$ipAddress] = 1;	
				}
				if($viewInfo['timestamp'] >= $lastWeek && $viewInfo['timestamp'] < $today){
					$aLastWeek[$ipAddress] = 1;	
				}
				if($viewInfo['timestamp'] >= $lastMonth && $viewInfo['timestamp'] < $lastWeek){
					$aLastMonth[$ipAddress] = 1;	
				}
				if($viewInfo['timestamp'] >= $lastThreeMonth && $viewInfo['timestamp'] < $lastMonth){
					$aLastThreeMonth[$ipAddress] = 1;	
				}
				
			}	
			
			$aViewData = array(
				'uniqueViews' 			=> count($aViews),
				'todayViews'			=> count($aToday),
				'lastWeekViews'			=> count($aLastWeek),
				'lastMonthViews'		=> count($aLastMonth),
				'lastThreeMonthViews'	=> count($aLastThreeMonth),
				'totalViews'			=> $cntTotalViews
			);
			
			return($aViewData);
			
		break;	
		
		case 'unique-member': 
			
			$query = "
				SELECT *
				FROM ".$_SESSION['page_tracking_table']."
				WHERE page_id=:page_id AND member_id=:member_id
			";
			try{
				$rsGetViews = $dbLink->prepare($query);
				$aValues = array(
					':page_id'		=> $pageID,
					':member_id'	=> $identifier			
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetViews->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$aViews = array();
			
			while($views = $rsGetViews->fetch(PDO::FETCH_ASSOC)){
				
				$aViews[$views['ip_address']] = array(
					'pageID'		=> $views['page_id'],
					'fundID'		=> $views['fund_id'],
					'memberID'		=> $views['member_id'],
					'agent'			=> $views['agent'],
					'timestamp'		=> $views['timestamp']
				);
						
			}
			
			$uniqueViews = count($aViews);
			
			return $uniqueViews;
			
		break;
		
		case 'total-member': 
			
			$query = "
				SELECT *
				FROM ".$_SESSION['page_tracking_table']."
				WHERE page_id=:page_id AND member_id=:member_id
			";
			try{
				$rsGetViews = $dbLink->prepare($query);
				$aValues = array(
					':page_id'		=> $pageID,
					':member_id'	=> $identifier			
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetViews->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$aViews = array();
			
			while($views = $rsGetViews->fetch(PDO::FETCH_ASSOC)){
				$aViews[] = array(
					'pageID'		=> $views['page_id'],
					'fundID'		=> $views['fund_id'],
					'memberID'		=> $views['member_id'],
					'agent'			=> $views['agent'],
					'ipAddress'		=> $views['ip_address'],
					'timestamp'		=> $views['timestamp']
				);	
			}
			
			$totalViews = count($aViews);
			
			return $totalViews;
			
		break;
			
	}
		
}


//+----------------------------------------------------------------------------------------+
//|this function grabs the label and rationale for a particular fund for a particular symbol
//+----------------------------------------------------------------------------------------+
function getPosLabel($mLink, $fundID, $stockSymbol){
	
	$query = "
		SELECT *
		FROM ".$_SESSION['positions_labels_table']."
		WHERE fund_id=:fund_id AND stock_symbol=:stock_symbol
		ORDER BY timestamp DESC
		LIMIT 1
	";
	
	try{
		$rsGetLabel = $mLink->prepare($query);
		$aValues = array(
			':fund_id'		=> $fundID,
			':stock_symbol'	=> $stockSymbol			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetLabel->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$labelInfo = $rsGetLabel->fetch(PDO::FETCH_ASSOC);
	
	$label 		= $labelInfo['label'];
	$rationale	= $labelInfo['rationale'];
	
	//Check to see if an rows were returned at all, if not check the position details table for legacy data
	$rowCnt = $rsGetLabel->rowCount();
	if($rowCnt == 0){
		
		$query = "
			SELECT label, rationale
			FROM ".$_SESSION['fund_positions_details_table']."
			WHERE fund_id=:fund_id AND stockSymbol=:stock_symbol
		";
		
		try{
			$rsPosDetails = $mLink->prepare($query);
			$aValues = array(
				':fund_id'		=> $fundID,
				':stock_symbol'	=> $stockSymbol			
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsPosDetails->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$posInfo 	= $rsPosDetails->fetch(PDO::FETCH_ASSOC);
		
		$label 		= $posInfo['label'];
		$rationale	= $posInfo['rationale'];
		
		if($label != '' || $rationale != ''){
			//create a new record in the positions labels table so that we dont have to rely on legacy data
			$query = "
				INSERT INTO ".$_SESSION['positions_labels_table']."(
					fund_id,
					stock_symbol,
					label,
					rationale,
					timestamp
				)VALUES(
					:fund_id,
					:stock_symbol,
					:label,
					:rationale,
					UNIX_TIMESTAMP()
				)
			";
			try{
				$rsInsert = $mLink->prepare($query);
				$aValues = array(
					':fund_id'		=> $fundID,
					':stock_symbol'	=> $stockSymbol,
					':label'		=> $label,
					':rationale'	=> $rationale			
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsInsert->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
		}
	}
	
	$aLabelInfo = array(
		'label'		=> $label,
		'rationale'	=> $rationale
	);
	
	return($aLabelInfo);
}


function priceRunDateArray($newDate, $today, $fromDate, $inception){
	$key = true;
	
	while($key){
		//echo "The number is: $x <br>";
		
		if($inception > $fromDate){
			$fromDate = $inception;	
		}
		
		if($newDate < $today){
			
			//echo 'hello';
			
			if($newDate < $fromDate){
				$start = $fromDate;	
			}else{
				$start = strtotime('+1 days',$newDate);	
			}
			
			$end = strtotime('+15 days',$start);
			
			if($end > $today){
				$aPriceDates[] = array(
					'start'	=> $start,
					'end'	=> $today
				);	
			}else{
				$aPriceDates[] = array(
					'start'	=> $start,
					'end'	=> $end
				);	
			}
		}
		
		$newDate = $end;
		
		if($newDate > $today){
			//stop loop
			$key = false;	
		}
	} 	
	
	return $aPriceDates;
}

//Get USER IP function
function get_ip() {

	//Just get the headers if we can or else use the SERVER global
	if ( function_exists( 'apache_request_headers' ) ) {

		$headers = apache_request_headers();

	} else {

		$headers = $_SERVER;

	}

	//Get the forwarded IP if it exists
	if ( array_key_exists( 'X-Forwarded-For', $headers ) && filter_var( $headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {

		$the_ip = $headers['X-Forwarded-For'];

	} elseif ( array_key_exists( 'HTTP_X_FORWARDED_FOR', $headers ) && filter_var( $headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 )
	) {

		$the_ip = $headers['HTTP_X_FORWARDED_FOR'];

	} else {
		
		$the_ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 );

	}

	return $the_ip;

}

//Credit Card Checksum
function cc_checksum($number){
	
	// Strip any non-digits (useful for credit card numbers with spaces and hyphens)
  	$number=preg_replace('/\D/', '', $number);

  	// Set the string length and parity
  	$number_length=strlen($number);
  	$parity=$number_length % 2;

  	// Loop through each digit and do the maths
  	$total=0;
  	for ($i=0; $i<$number_length; $i++) {
    	$digit=$number[$i];
    	// Multiply alternate digits by two
		if ($i % 2 == $parity) {
			$digit*=2;
		  	// If the sum is two digits, add them together (in effect)
		  	if ($digit > 9) {
				$digit-=9;
		  	}
		}
		// Total up the digits
		$total+=$digit;
  	}

	// If the total mod 10 equals 0, the number is valid
  	return ($total % 10 == 0) ? TRUE : FALSE;
}

//Download To CSV
function query_to_csv($array, $filename, $attachment = false, $headers = true) {
        
	if($attachment) {
		// send response headers to the browser
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment;filename='.$filename);
		$fp = fopen('php://output', 'w');
	} else {
		$fp = fopen($filename, 'w');
	}
	
			
	foreach($array as $key => $row){
		fputcsv($fp, $row, ',', '"');
	}
	
	
	
	fclose($fp);
}


//Data Legacy Query Functions
function newTrade($mLink, $aTrade, $action='process'){
	
	$api 		= "ECN";
	$method 	= 'create';
	
	switch($action){
		case 'process':
			//Create query
			$query = 'create||'.$username.'|'.$fundSymbol.'|'.$fundID.'|'.$tradeType.'|'.$orderType.'|'.$symbol.'|'.$shares.'|'.$limitPrice.'|'.$reason.'|'.$desc.'';
			
			//Execute Query
			include ('data-query-ecn.php');
			
			$event = 'STOCK ORDER : create';
            $detail = $_SESSION['username'].':'.$query;
            addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
			
			$message = "Data legacy query logged.";
		break;
		
		case 'debug':
			$query 			= 'create||'.$username.'|'.$fundSymbol.'|'.$fundID.'|'.$tradeType.'|'.$orderType.'|'.$symbol.'|'.$shares.'|'.$limitPrice.'|'.$reason.'|'.$desc.'';
			$queryStructure	= 'create|key|username|fundSymbol|fundID|tradeType(buy/sell)|orderType(Day/GTC)|stockSymbol|shares|limitPrice|reason|description';
			
			$dbQuery = "
				INSERT INTO debug_legacy_queries (
					api,
					method,
					query,
					query_structure,
					time_readable,
					timestamp
				) VALUE (
					:api,
					:method,
					:query,
					:query_structure,
					:time,
					UNIX_TIMESTAMP()
				)
			";
			try{
				$rsInsertDebug = $mLink->prepare($dbQuery);
				$aValues = array(
					':api'				=> $api,
					':method'			=> $method,
					':query'			=> $query,
					':query_structure'	=> $queryStructure,
					':time'				=> date('Y/m/d h:i a')
					
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsInsertDebug->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$message = "Debug entry logged.";
		break;	
		
		case 'help':
			$message = "The array passed to the function should look like the following: array=([username]=>'',[fundSymbol]=>'',[fundID]=>'',[tradeType]=>'',[orderType]=>'',[symbol]=>'',[shares]=>'',[limitPrice]=>'',[reason]=>'',[desc]=>'')";
		break;
	}
	 
	return $message;
}


function gen_randomString($length = 10, $chars= 'all') {
    
	switch($chars){
		case 'all'				: 	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';break;
		case 'numUpperSymbol'	: 	$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';break;
	}
	
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function strToInt($string){
    return (int)preg_replace("/([^0-9\\.])/i", "", $string);
}

function gen_timestamp($date, $format='yyyy-mm-dd hh:mm:ss'){
	
	switch($format){
		case 'yyyy-mm-dd hh:mm:ss' :
			$timestamp = mktime(/*hour*/(substr($date, 11, 2)), /*min*/substr($date, 14, 2), /*second*/substr($date, 17, 2), /*month*/substr($date, 5, 2), /*day*/substr($date, 8, 2),/*year*/substr($date, 0, 4));	
		break;
	}
	
	return $timestamp;
}

//math functions
function calc_AAR($currentPrice, $pastPrice, $years){
	
	/*if($currentPrice < $pastPrice){
		$base =  $pastPrice / $currentPrice;
		$exp = 1 / $years;
					
		$AAR = ((pow($base, $exp))-1)*100;
		
		$AAR = $AAR * (-1);
			
	}else{
		
		$base = $currentPrice / $pastPrice;
		$exp = 1 / $years;
					
		$AAR = ((pow($base, $exp))-1)*100;
		
	}*/
	
	$base = $currentPrice / $pastPrice;
	$exp = 1 / $years;
				
	$AAR = ((pow($base, $exp))-1)*100;
	
	/*if($currentPrice > 0){
		$base = $currentPrice / $pastPrice;
		$exp = 1 / $years;
					
		$AAR = ((pow($base, $exp))-1)*100;
	}else{
		$AAR = 0;	
	}*/
	
	return $AAR;	
}

function get_fund_AAR($mLink, $fundID, $switch){
	
	$inceptionStart = 1000000;
	
	$query = "
		SELECT unix_date
		FROM ".$_SESSION['fund_table']."
		WHERE fund_id=:fund_id
	";
	
	try{
		$rsFundInfo = $mLink->prepare($query);
		$aValues = array(
			':fund_id'		=> $fundID
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsFundInfo->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$fund = $rsFundInfo->fetch(PDO::FETCH_ASSOC);
		
	//Getting the time
	$hour = 12;
	$today              = strtotime("$hour:00:00");
	$yesterday          = strtotime("-1 day", $today);
	//Get seconds since inception, then years since inception
	$seconds = $yesterday - $fund['unix_date'];
	$years = $seconds / 31536000;
	$yearsRound = floor($years);
	
	//calaculate Annualized Return	
	$aLP = get_funds($mLink, $fundID, 'lp', 'livePrice');
	
	$currentValue = $aLP['totalValue'];
	
	
	$AAR = calc_AAR($currentValue, $inceptionStart, $years);
	
	if($switch == "SP"){
		$query = "
			SELECT * 
			FROM `stock_index_history`
			WHERE `index`='^GSPC' 
			AND date=(SELECT date FROM `stock_index_history` WHERE `index`='^SP500TR' AND date LIKE :date ORDER BY unix_date DESC LIMIT 1) 
			OR `index`='^SP500TR' 
			AND date=(SELECT date FROM `stock_index_history` WHERE `index`='^SP500TR' ORDER BY unix_date DESC LIMIT 1)
			ORDER BY unix_date ASC
		";
		try{
			$rsSP = $mLink->prepare($query);
			$aValues = array(
				':date' 	=> date('Y-m-', $fund['unix_date']).'%'
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsSP->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$cnt = 0;
		while($sp = $rsSP->fetch(PDO::FETCH_ASSOC)){
			
			$aSP[$cnt] = $sp['close'];
			
			$cnt++;
			
		}
		
		$base = $aSP[1] / $aSP[0];
		$exp = 1 / $years;
		
		$spAAR = ((pow($base, $exp))-1)*100;
		
		return($spAAR);	
	}else{
		return($AAR);
	}
		
}

// Some convenient functions

function graphColors(){
	$aColors = array('#7cb5ec','#434348','#90ed7d','#f7a35c','#8085e9','#f15c80','#e4d354','#8085e8','#8d4653','#91e8e1');
	return $aColors;	
}

//-----
// Is passed value odd?
function isOdd($num){
	return (is_numeric($num)&($num&1));
}

// Is passed value even?
function isEven($num){
	return (is_numeric($num)&(!($num&1)));
}

//-----
// Determine if day is a weekday
// Pass time
function isWeekday($timestamp) {
	return (date('N', $timestamp) < 6); // ISO DoW (7 = Sunday)
}

function isWeekend($timestamp){
	
	$dayOfWeek = date('N', $timestamp);
	
	if($dayOfWeek == '6' || $dayOfWeek == '7'){
		return true;	
	}else{
		return false;	
	}
			
}

//-----
// Determine if day is a market holiday
// Pass time & DB link
// Returns false if not, "Y" if it is, "E" if it's an early closing day
function isMarketHoliday($timestamp, $mLink, $skipSession=false) {
	
	
	if($skipSession == false){
		if (isset($_SESSION['market_holiday'])){
			return $_SESSION['market_holiday'];
		}
	}
	
	
	
	$query = "
		SELECT *
		FROM ".$_SESSION['system_holidays_table']."
		WHERE `date`=:date
	";
	try {
		$rsHoliday = $mLink->prepare($query);
		$aValues = array(
			':date'		=> date('Y-m-d', $timestamp)
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		//return $preparedQuery;
		$rsHoliday->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	

	if ($rsHoliday->rowCount() > 0){  // It's a holiday
		$holiday = $rsHoliday->fetch(PDO::FETCH_ASSOC);
		$_SESSION['market_holiday'] = $holiday['closed'];
		$_SESSION['market_holiday_occasion'] = $holiday['occasion'];
		return $holiday['closed']; // "Y" if it is a holiday, "E" if it closes early
	}
	$_SESSION['market_holiday'] = false;
	return false;
		
	
}

function isMarketHoliday2($timestamp, $mLink, $skipSession=false, $debug=false) {
	
	$functionLog = array();
	
	if($skipSession == false){
		if (isset($_SESSION['market_holiday'])){
			return $_SESSION['market_holiday'];
		}
	}
	
	
	
	$query = "
		SELECT *
		FROM ".$_SESSION['system_holidays_table']."
		WHERE `date`=:date
	";
	try {
		$rsHoliday = $mLink->prepare($query);
		$aValues = array(
			':date'		=> date('Y-m-d', $timestamp)
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		//return $preparedQuery;
		$rsHoliday->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$functionLog['preparedQuery'] 	= $preparedQuery;
	$functionLog['queryError']		= $error;
	
	if($debug == false){
	
		if ($rsHoliday->rowCount() > 0){  // It's a holiday
			$holiday = $rsHoliday->fetch(PDO::FETCH_ASSOC);
			$_SESSION['market_holiday'] = $holiday['closed'];
			$_SESSION['market_holiday_occasion'] = $holiday['occasion'];
			return $holiday['closed']; // "Y" if it is a holiday, "E" if it closes early
		}
		$_SESSION['market_holiday'] = false;
		return false;
		
		/*$holiday = $rsHoliday->fetch(PDO::FETCH_ASSOC);
			
		if($holiday['closed'] == 'Y' || $holiday['closed'] == 'E'){	
			$_SESSION['market_holiday'] = $holiday['closed'];
			$_SESSION['market_holiday_occasion'] = $holiday['occasion'];
			return $holiday['closed']; // "Y" if it is a holiday, "E" if it closes early
		}else{
			$_SESSION['market_holiday'] = false;
			return false;
		}*/
	
	}else{
		$holiday = $rsHoliday->fetch(PDO::FETCH_ASSOC);
		$functionLog['market_status'] = $holiday['closed'];
		return $functionLog;	
	}
}



//-----
// Determine if market is open
// Pass time, DB link (for holiday lookup), and whether to pad start and end times
// Returns true or false
function isMarketOpen($timestamp, $mLink, $fudge='none') {
	// Is it a weekday?
	if (isWeekday($timestamp)){
		switch($fudge){
			case 'none': // ACTUAL market hours (9:30 to 4:00 ET, 1:00 if it's an early close day)
				$begin = "9:30 AM";
				$end = (isMarketHoliday($timestamp, $mLink) == "E" ? "1:01 PM" : "4:01 PM"); // Add 1 minute to cover last loop running overtime
				break;

			case 'before':  // Start 30 minutes early, end on time
				$begin = "9:00 AM";
				$end = (isMarketHoliday($timestamp, $mLink) == "E" ? "1:01 PM" : "4:01 PM");
				break;

			case 'after': // Start on time, end 30 minutes late
				$begin = "9:30 AM";
				$end = (isMarketHoliday($timestamp, $mLink) == "E" ? "1:31 PM" : "4:31 PM");
				break;

			case 'both':  // Start 30 minutes early, end 30 minutes late
				$begin = "9:00 AM";
				$end = (isMarketHoliday($timestamp, $mLink) == "E" ? "1:31 PM" : "4:31 PM");
				break;

			case 'indices':  // Start on time, end 2 hours late (after final closing prices are set)
				$begin = "9:30 AM";
				$end = (isMarketHoliday($timestamp, $mLink) == "E" ? "3:01 PM" : "6:01 PM");
				break;

			default: // Use actual market hours if not properly specified
				$begin = "9:30 AM";
				$end = (isMarketHoliday($timestamp, $mLink) == "E" ? "1:01 PM" : "4:01 PM");
		}
		if (isMarketHoliday($timestamp, $mLink) == "Y"){  // Closed all day
			return false;
		}else{ // Open today
			if ($timestamp > strtotime(date('j-n-Y', $timestamp).' '.$begin.' America/New_York') && $timestamp < strtotime(date('j-n-Y', $timestamp).' '.$end.' America/New_York')) {
				return true;
			}
		}
	}
	return false;
}


/*
function to get the previous market day date.
build list of dates to check (unixtime), in reverse order starting with yesterday strtotime("-1 day 10:00 AM")...use for/next to count back up to 7 days
us isMarketOpen function, passing unixdate, to determine if the market was open.
Keep doing tha tuntil you get a "true", then you have the last market day.

Use this to set the background color if the change value is stale (last closing price not from the last actual market day)
<span class="label label-warning" style="color:#000000;">VALUE HERE</span>

if (isMarketOpen(time(), $linkID, "none")){ // Do this only if the markets are open
*/


/*
This function will return the nearest open market unix timestamp from the timestamp passed to it. If the timestamp passed is an open market timestamp, the function will return it. 

if $future = true is passed, the fuction will increment 1 day forward from the timestamp passed instead of one day backwards, this is nessisary for inception dates

if $debug = true is passed, the function will return text message as well as the last timestamp the function has processed.

NOTE: THE TIMESTAMP MUST FALL IN BETWEEN MARKET OPEN HOURS FOR THIS FUNCTION TO WORK, SET TIMESTAMP TO 12:00pm NOON FOR BEST RESULTS
*/
function checkForMarketDate($timestamp, $mLink, $future=false, $debug=false){
	
	//Set variables
	$returnTimestamp 	= false;
	$maxLoop 			= 7; //in days
	$loopCnt			= 0;
	$msg				= '';
	
	$functionLog		= array();
	
	//Loop until an open market timestamp is found or loop has exceeded its max number of loops
	while($returnTimestamp == false){
		
		//Check the timestamp to see if the market is open
		//if(isMarketOpen($timestamp, $mLink) == false){
		if(isWeekday($timestamp) == false){
			$functionLog[] = array('status'=>'its the weekend','timestamp'=>$timestamp, 'date'=>date('Ymd',$timestamp));
			
			#Check to see if the future switch has been passed
			if($future == false){
				#no future switch, go backwards in time
				$timestamp = strtotime('-1 day', $timestamp);
			}else{
				#future switched, go forwards in time
				$timestamp = strtotime('+1 day', $timestamp);
			}
			
			#increment loop by 1(one)
			$loopCnt++;
			
			#check to see if loop has exceeded the set var
			if($loopCnt >= $maxLoop){
				#loop exceeded drop out of loop and complete function
				$returnTimestamp = true;
				$msg = 'Max Loop Exceeded '.$maxLoop;	
			}
			
		}elseif(isMarketHoliday2($timestamp, $mLink, true, false) == 'Y'){
			
			$functionLog[] = array('status'=>'market is a holiday','timestamp'=>$timestamp, 'date'=>date('Ymd',$timestamp));
			
			#Check to see if the future switch has been passed
			if($future == false){
				#no future switch, go backwards in time
				$timestamp = strtotime('-1 day', $timestamp);
			}else{
				#future switched, go forwards in time
				$timestamp = strtotime('+1 day', $timestamp);
			}
			
			#increment loop by 1(one)
			$loopCnt++;
			
			#check to see if loop has exceeded the set var
			if($loopCnt >= $maxLoop){
				#loop exceeded drop out of loop and complete function
				$returnTimestamp = true;
				$msg = 'Max Loop Exceeded '.$maxLoop;	
			}
			
		}else{
			#timestamp is a market open day, break loop and continue
			
			$functionLog[] = array('status'=>'market is open','timestamp'=>$timestamp, 'date'=>date('Ymd',$timestamp));
			$returnTimestamp = true;
			$msg = "Market is open";	
		}
		
	}
	
	#check to see if function can continue
	if($returnTimestamp == true){
		#check to see if debug switch has been used
		if($debug == false){
			#output the timestamp
			return $timestamp;	
		}else{
			//return $msg.' | '.$timestamp.' | '.date('Ymd',$timestamp);
			
			return $functionLog;	
		}
	}
	
}

function todayReturn($mLink, $fundID){
	
	$today = time();
	$yesterday = strtotime('-1 day', $today);
	
	$query = "
		SELECT nav
		FROM ".$_SESSION['fund_liveprice_table']."
		WHERE fund_id=:fund_id
	";
	
}


//-----
// Calculate time passed since timestamp
function get_day_name($timestamp) {
    $date = date('d/m/Y', $timestamp);
    if($date == date('d/m/Y')) {
      $day_name = '<strong>Today</strong>,';
    }else{
		$day_name = '';
	}
    return $day_name;
}

function time_past($timestamp, $type){
	$seconds	= time() - $timestamp;
	$minutes	= $seconds / 60;
	$hours		= $minutes / 60;
	$days		= $hours / 24;

	if($seconds <= 60) {
		$timePast = "".round($seconds, 0)." seconds ago";
	}elseif($minutes <= 60 ){
		$timePast = "".round($minutes, 0)." minutes ago";
	}elseif($hours <= 24 ){
		$timePast = "".round($hours, 0)." hours ago";
	}/*elseif($days < 2 ){
		$timePast = "Yesterday at ".date('g:ia', $timestamp)."";
	}*/elseif($days >= 2 && date('Y', $timestamp) == date('Y')){
		$timePast = "".date('F j', $timestamp)." at ".date('g:ia', $timestamp)."";
	}else{
		$timePast = "".date('n/j/y', $timestamp)." at ".date('g:ia', $timestamp)."";
	}

	if($type == "time"){
		$timePast = date('g:ia');
		$output = $timePast;
	}else{
		$output = $timePast;
	}
	
	if($type == "day"){
		$output = ''.get_day_name($timestamp).' '.$timePast.'';
		
	}
	
	return $output;
}

//-----
// Add Notification to Database
function add_notification($mLink, $notificationID, $memberID, $notification, $link){
	
	
	
	//Check to see if notifcation is LOCKED (If it is locked the user settings are ignored)
	$query = "
		SELECT *
		FROM ".$_SESSION['members_notification_types_table']."
		WHERE notification_id=:notification_id
	";
	try{
		$rsLocked = $mLink->prepare($query);
		$aValues = array(
			':notification_id'	=> $notificationID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsLocked->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}

	$locked = $rsLocked->fetch(PDO::FETCH_ASSOC);
	
	if($link == ''){
		$link = $locked['default_link'];
		
	}
	
	if($notification == ''){
		$notification == $locked['default_notification'];
	}
	
	//If the notification is Unlocked query member settings to check to see if it is ignored
	if($locked['locked'] == "0"){
		
		if($locked['disable'] == '0'){
			$query = "
				SELECT ignore_notifications
				FROM ".$_SESSION['members_settings_table']."
				WHERE member_id=:member_id
			";
			try{
				$rsSettings = $mLink->prepare($query);
				$aValues = array(
					':member_id'	=> $memberID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsSettings->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
	
			$exclude = $rsSettings->fetch(PDO::FETCH_ASSOC);
			
			if(strpos($exclude['ignore_notifications'], $notificationID) !== false){
				//Do nothing Notification Type Ignored	
			}else{
				//Add notification
			
				$query = "
					INSERT INTO ".$_SESSION['members_notification_table']." (
						notification_id,
						member_id,
						notification,
						link,
						timestamp
					) VALUE (
						:notification_id,
						:member_id,
						:notification,
						:link,
						UNIX_TIMESTAMP()
					)
				";
				
				try{
					$rsNote = $mLink->prepare($query);
					$aValues = array(
						':notification_id'	=> $notificationID,
						':member_id'		=> $memberID,
						':notification'		=> $notification,
						':link'				=> $link
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsNote->execute($aValues);
				}
			
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
			}//end exclude statement
		}
		
	}else{
		
		if($locked['disable'] == '0'){
			//If notification is LOCKED, add notification
			$query = "
				INSERT INTO ".$_SESSION['members_notification_table']." (
					notification_id,
					member_id,
					notification,
					link,
					timestamp
				) VALUE (
					:notification_id,
					:member_id,
					:notification,
					:link,
					UNIX_TIMESTAMP()
				)
			";
			
			try{
				$rsNote = $mLink->prepare($query);
				$aValues = array(
					':notification_id'	=> $notificationID,
					':member_id'		=> $memberID,
					':notification'		=> $notification,
					':link'				=> $link			
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsNote->execute($aValues);
			}
		
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
		}
	}//end locked statement
	
	return $error;
	
}//END FUNCTION

//USAGE
/*

	$notificationID	= "11-001"; <- SET the type of notifcation. These types can be found in the "members_notifications_types" table column: notification_id
	$memberID		= $_SESSION['member_id']; <- Pass the member ID
	//Custom notification
	$notification	= ""; <- If the notification requires custom text for the notification, put it here
	$link			= ""; <- If the notification requires a custom link, put it here
	
	//Run function
	add_notification($mLink, $notificationID, $memberID, $notification, $link);
	
	NOTE: YOU MUST PASS THE PDO OBJECT "$mLink"  IN ORDER FOR THIS FUNCTION TO WORK

*/
//END NOTIFICATION FUNCTION

//START GET NEW FUND ID
function get_new_fund_id($mLink, $memberID){
	$query = "
		SELECT fund_id
		FROM ".$_SESSION['fund_table']."
		WHERE member_id=:member_id
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
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aFundSymbols = array();
	
	while($fund = $rsFund->fetch(PDO::FETCH_ASSOC)){
		
		$aFundID = explode('-',$fund['fund_id']);
		
		$aFundIDs[] = $aFundID[1];
	}
	
	$max = max($aFundIDs);
	
	$newFundID = $memberID.'-'.($max + 1);
	
	return $newFundID;
}
//END GET NEW FUND ID

//START GET MEMBERS FUND SYMBOLS
function get_fund_symbols($mLink, $memberID, $item='symbols', $active=true){
	
	if($active == false){
		$showActive = '';
	}elseif($active == true){
		$showActive = "AND active='1'";
	}
	
	$query = "
		SELECT fund_symbol, fund_id
		FROM ".$_SESSION['fund_table']."
		WHERE member_id=:member_id ".$showActive."
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
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aFundSymbols 	= array();
	$aFundIDs		= array();
	$aFunds 		= array();
	
	while($fund = $rsFund->fetch(PDO::FETCH_ASSOC)){
		$aFundSymbols[] 			= $fund['fund_symbol'];
		$aFundIDs[]					= $fund['fund_id'];	
		$aFunds[$fund['fund_id']]	= $fund['fund_symbol'];
	}
	
	switch($item){
		case 'symbols'	: $return = $aFundSymbols;break;
		case 'fundIDs'	: $return = $aFundIDs;break;
		case 'funds'	: $return = $aFunds;break;
	}
	return $return;
}
//END GET MEMBERS FUND SYMBOLS

//Get Transaction Details
function transactionDetails($mLink,$id,$idType,$output='raw'){
	
	$aTransactions = array();
	
	#switch on the idType to determine how to search for the transaction data
	switch($idType){
		#get data for specific transaction
		case 'invoiceNumber':
			
			
			$query = "
				SELECT *
				FROM merchant_transaction_history
				WHERE order_invoiceNumber=:invoiceNumber
			";
			try {
				$rsTransData			= $mLink->prepare($query);
				$aValues 				= array(
					':invoiceNumber'	=> $id
				);
				// Prepared query - for error logging and debugging
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsTransData->execute($aValues);
			}
			catch(PDOException $error){
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$transData	= $rsTransData->fetch(PDO::FETCH_ASSOC);
			
			$aTransactions[$id]['transactionData'] = $transData;
			
			#get the cart data for the transaction
			$query = "
				SELECT *
				FROM merchant_cart
				WHERE cart_id=:cart_id
			";
			try {
				$rsCartData			= $mLink->prepare($query);
				$aValues 				= array(
					':cart_id'	=> $id
				);
				// Prepared query - for error logging and debugging
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsCartData->execute($aValues);
			}
			catch(PDOException $error){
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			//$aTransactions[$id]['error'][] = $error;
			
			while($cartData	= $rsCartData->fetch(PDO::FETCH_ASSOC)){
				
				$aTransactions[$id]['cartItems'][] = $cartData;
					
			}
			
			#get payment info
			$query = "
				SELECT *
				FROM merchant_payment_profiles
				WHERE uid=:paymentProfileID
			";
			try {
				$rsPaymentInfo			= $mLink->prepare($query);
				$aValues 				= array(
					':paymentProfileID'	=> $transData['payment_profile_uid']
				);
				// Prepared query - for error logging and debugging
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsPaymentInfo->execute($aValues);
			}
			catch(PDOException $error){
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$paymentInfo	= $rsPaymentInfo->fetch(PDO::FETCH_ASSOC);
			
			$aTransactions[$id]['paymentInfo'] = $paymentInfo;
					
		break;	
	}#end switch $idType
	
	switch($output){
		
		case 'raw':
			return($aTransactions);
		break;
		
		case 'html':
			
			$transInfo 		= $aTransactions[$id]['transactionData'];
			$paymentInfo	= $aTransactions[$id]['paymentInfo'];
			$subTotal		= $transInfo['unitPrice'];
			$total			= $transInfo['transaction_amount'];
			$taxable		= $transInfo['taxable'];
			
			if($taxable 	== true){
				$tax		= number_format(($subTotal * .0825),2,'.',',');
			}else{
				$tax		= 0; 
			}
			
			
			$htmlData = '
				
				<h5><strong>Billed Date:</strong> '.date('M d, Y',$transInfo['bill_timestamp']).'</h5>
				<h5><strong>Invoice Number:</strong> '.$id.'</h5>
				<h5><strong>Bill Total:</strong> $'.$transInfo['transaction_amount'].'</h5>
		
				<table class="table table-bordered">
					<tr>
						<td colspan="2"><strong>Order Information</strong></td>
					</tr>
			';
			
			
			foreach($aTransactions[$id]['cartItems'] as $key=>$productInfo){
				
				$productName 	= $productInfo['product_name'];
				$salePrice		= $productInfo['sale_price'];
				
				$htmlData .= '
				
				
                <tr>
                    <td>
                        <p style="color:#222222;">'.$productName.'</p>
    
                    </td>
                    <td style="text-align:right;">
                        <h5><strong>Price:</strong></h5>
                        <p style="color:#222222;">'.$salePrice.'<p>
                    </td>
                </tr>';
   			}
			
			$htmlData .= '		
				</table>
		
				<table class="table table-bordered">
					<tr>
						<td colspan="2"><strong>Payment Information</strong></td>
					</tr>
					<tr>
						<td>
							<h5><strong>Payment Method:</strong></h5>
							<p>'.$paymentInfo['cardType'].' | Last digits: '.$paymentInfo['cardLastFour'].'</p>
		
							<h5><strong>Billing Address:</strong></h5>
							<p>'.$paymentInfo['billTo_firstName'].' '.$paymentInfo['billTo_lastName'].'<br />'.$paymentInfo['billTo_address'].'<br />'.$paymentInfo['billTo_city'].', '.$paymentInfo['billTo_state'].' '.$paymentInfo['billTo_zip'].'<br />'.$paymentInfo['billTo_country'].'</p>
						</td>
						<td style="text-align:right;">
							<p style="color:#222222;">
							SubTotal: $'.$subTotal.'<br />
							';
							
			if($taxable == true){
				$htmlData .=	'
							-----<br />
							Tax: $'.$tax.'<br />';	
			}
							
			$htmlData .= '
							-----<br />
							<strong>Total: $'.$total.'</strong>
							<p>
						</td>
					</tr>
				</table>
				
			';
			
			return $htmlData;
			
		break;	
	}
	
}

function revenue($mLink, $revType='all'){
	
	$results = array();
	
	switch(true){
		
		case ($revType == 'GIPS' || $revType == 'all'):
			
			$query = "
				SELECT mf.*,m.*
				FROM members_fund AS mf
				INNER JOIN members AS m ON m.member_id=mf.member_id
				WHERE mf.composite_fund='1'
			";
			
			try{
				$rsGetFunds = $mLink->prepare($query);
				$aValues = array();
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetFunds->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$aumTotal = 0;
			$feeTotal = 0;
			$managerCnt	= 0;
			
			$results['error'] = $error;
			
			while($funds = $rsGetFunds->fetch(PDO::FETCH_ASSOC)){
				
				$managerCnt++;
		
				$memberID				= $funds['member_id'];
				$compositeDisclosure	= $funds['composite_disclosure'];
				
				if($compositeDisclosure != NULL){
					$showComposite = '<a class="btn btn-xs btn-default" href="'.$_SESSION['site_root'].'/files/disclosures/'.$compositeDisclosure.'" target="_blank">View Composite <i class="icon-external-link"></i></a>';	
				}else{
					$showComposite = 'N/A';	
				}
				
				$query = "
					SELECT aum
					FROM members_fund_composite
					WHERE fund_id=:fund_id
					ORDER BY unix_date DESC
					LIMIT 1
				
				";
				try{
					$rsAUM = $mLink->prepare($query);
					$aValues = array(
						':fund_id' => $funds['fund_id']
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsAUM->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				$aum = $rsAUM->fetch(PDO::FETCH_ASSOC);
				
				$AUM = $aum['aum'];
				
				if($AUM <= 250000){
					#100k - 250k
					$fee = .015;
				}elseif($AUM <= 500000){
					#250k - 500k
					$fee = .014;	
				}elseif($AUM <= 1000000){
					#500k - 1m
					$fee = .013;	
				}elseif($AUM <= 2500000){
					#1m - 2.5m
					$fee = .011;	
				}else{
					# > 2.5m
					$fee = .01;	
				}
				
				$aumTotal	= $aumTotal + $AUM;
				$feeAUM		= ($AUM*$fee);
				$feeTotal	= $feeTotal + $feeAUM;
				
			}
			
			$results['aum-total'] 	= $aumTotal;
			$results['aum-fee'] 	= $feeAUM;
			$results['gips-earned']	= $feeTotal;
			
		break;
			
			
		
	}
	
	return($results);		
		
		
}



//MAX FUND CHECK
function subscription($mLink, $memberID, $force=false, $queryOnly=false){
	
	$legacyEndDate			= '1480654800';
	$subscriptionTimeout	= 15; #how often to force check of subscription record in minutes
	$subscriptionTimeout	= ($subscriptionTimeout * 60);
	$timePast				= (time() - $subscriptionTimeout);
	$joinDate				= get_member($mLink, $memberID, 'joinDate');
	$mtrAccess				= 0;
	
	#update subscription settings if the subscription session is not set, or the session is older than the timeout period, or if we force it
	if(!isset($_SESSION['subscription']) || $_SESSION['subscription']['timestamp'] < $timePast || $force==true){
	
		$maxFundValid = false;
		
		#get sub data
		$query = "
			SELECT s.*, p.*
			FROM ".$_SESSION['subscriptions_table']." AS s
			INNER JOIN ".$_SESSION['products_table']." as p ON p.product_id=s.product_id
			WHERE s.member_id=:member_id AND s.active='1' AND p.product_type='membership'
			ORDER BY s.uid DESC
			LIMIT 1
		";
		try {
			$rsSubData 			= $mLink->prepare($query);
			$aValues 			= array(
				':member_id'	=> $memberID
			);
			// Prepared query - for error logging and debugging
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsSubData->execute($aValues);
		}
		catch(PDOException $error){
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$subData 				= $rsSubData->fetch(PDO::FETCH_ASSOC);
		
		#validate the max allowed funds
		$aFunds					= get_fund_symbols($mLink, $memberID, 'funds');
		$fundCount				= count($aFunds);
		$mtrLevel				= $subData['mtr_access'];
		$membershipLevelID		= $subData['product_id'];
		$membershipLevel		= $subData['alt_product_name'].' Membership';
		$maxFunds				= $subData['track_records'];
		
		if($mtrLevel != NULL){
			$mtrAccess			= $mtrLevel;
		}
		
		
		
		
		#calculate subscription duration
		$subStart	 			= $subData['start_timestamp'];
		$duration				= $subData['sub_duration'];
		$endOfSub				= strtotime('+'.$duration.' day', $subStart);
		$daysLeft				= (($endOfSub - time())/86400);
		$daysLeftRound			= floor($daysLeft);
		$term					= $subData['new_bill_frequency'];
		
		#check to see if member is in trial
		if($subData['product_id'] == '0' || $subData['product_id'] == '99'){
			$inTrial = '1';	
			
			if($daysLeft > 15){
				$trial30 = 1;	
			}else{
				$trial15 = 1;
			}
			
		}else{
			$inTrial = '0';	
		}
		
		
		
		#check to see if member is a legacy member
		if($joinDate < $legacyEndDate){
			$isLegacy = '1';	
		}else{
			$isLegacy = '0';		
		}
		
		if($inTrial == '1'){
			
			$annualRenewalDate 		= strtotime('+13 month',$subStart);
			$monthlyRenewalDate		= strtotime('+2 month',$subStart);
			
				
		}else{
			
			$annualRenewalDate 		= strtotime('+12 month',$subStart);
			$monthlyRenewalDate		= strtotime('+1 month',$subStart);
			
		}
		
		if($term == 'Monthly'){
			$nextBillDate 		= $monthlyRenewalDate;	
		}else{
			$nextBillDate		= $annualRenewalDate;
		}
		
		
		//check for product subscriptions
		$query = "
			SELECT s.*, p.*
			FROM ".$_SESSION['subscriptions_table']." AS s
			INNER JOIN ".$_SESSION['products_table']." as p ON p.product_id=s.product_id
			WHERE s.member_id=:member_id AND s.active='1' AND p.product_type='product'
		";
		try {
			$rsProductSubs 			= $mLink->prepare($query);
			$aValues 			= array(
				':member_id'	=> $memberID
			);
			// Prepared query - for error logging and debugging
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsProductSubs->execute($aValues);
		}
		catch(PDOException $error){
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		while($productSubs 				= $rsProductSubs->fetch(PDO::FETCH_ASSOC)){
			
			$mtrLevel		= $productSubs['mtr_access'];
			$maxFunds		= ($maxFunds + $productSubs['add_track_records']);
			
			
			
			if($mtrAccess == 0 && $mtrLevel != NULL){
				$mtrAccess = $mtrLevel;	
			}
			
			$prodSubs[$productSubs['product_id']] = $productSubs;
		}
		
		if($fundCount <= $maxFunds){
			$maxFundValid = true;
		}
		
		#make sure admins don't fall into the transition wizard
		if($maxFunds == '' && $_SESSION['admin'] == '1'){
			$maxFundValid = true;	
		}
		
		$subscription = array(
				
			//'query'				=> $preparedQuery,
			'membershipLevelID'		=> $membershipLevelID,
			'membershipLevel'		=> $membershipLevel,
			'timestamp'				=> time(),
			'subStartDate'			=> $subStart,
			'maxFundValid'			=> $maxFundValid,
			'maxFunds'				=> $maxFunds,
			'subEndDate'			=> $endOfSub,
			'daysLeft'				=> $daysLeft,
			'daysLeftRound'			=> $daysLeftRound,
			'funds'					=> $aFunds,
			'newProductID'			=> $subData['new_product_id'],
			'inTrial'				=> $inTrial,
			'isLegacy'				=> $isLegacy,
			'term'					=> $term,
			'nextBillDate'			=> $nextBillDate,
			'renewalDate'			=> $annualRenewalDate,
			'annualRenewalDate'		=> $annualRenewalDate,
			'monthlyRenewalDate'	=> $monthlyRenewalDate,
			'trial30'				=> $trial30,
			'trial15'				=> $trial15,
			'mtrAccess'				=> $mtrAccess,
			'subData'				=> $subData,
			'productSubs'			=> $prodSubs
		);
		
		if($queryOnly == false){
			$_SESSION['subscription'] = $subscription;
			return($_SESSION['subscription']);
		}else{
			return($subscription);
		}
		
				
		
			
		
	}
	
	
	
}

function get_products($mLink, $active=true){
	
	$aProducts = array();
	
	$query = "
		SELECT *
		FROM ".$_SESSION['products_table']."
		WHERE active='1'
		ORDER BY `order` ASC
	";	
	try{
		$rsProducts = $mLink->prepare($query);
		$aValues = array(
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsProducts->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	while($product = $rsProducts->fetch(PDO::FETCH_ASSOC)){
		
		$aProducts[$product['product_id']] = $product;
			
	}
	
	//return ($error);
	return $aProducts;
			
}

//START GET PRODUCT INFO
function get_product($mLink, $productID){
	$query = "
		SELECT *
		FROM ".$_SESSION['products_table']."
		WHERE product_id=:product_id
	";	
	try{
		$rsProduct = $mLink->prepare($query);
		$aValues = array(
			':product_id'	=> $productID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsProduct->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$product = $rsProduct->fetch(PDO::FETCH_ASSOC);
	
	return $product;
}
//END GET PRODUCT INFO

//START GET SUBSCRIPTION RECORD
function get_subscription($mLink, $memberID){
	
	$query = "
		SELECT *
		FROM members_subscriptions
		WHERE member_id=:member_id AND active='1' AND product_id<'101'
	";
	try{
		$rsSub = $mLink->prepare($query);
		$aValues = array(
			':member_id'	=> $memberID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsSub->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$sub = $rsSub->fetch(PDO::FETCH_ASSOC);
	
	return($sub);
	
};

//START GET MEMBER INFO
function get_member($mLink, $memberID, $info){
	/*$query = "
		SELECT *
		FROM ".$_SESSION['members_table']."
		WHERE member_id=:member_id
	";*/
	
	$query = "
		SELECT * 
		FROM ".$_SESSION['members_table']." AS m 
		INNER JOIN ".$_SESSION['members_profile_table']." AS mp ON m.member_id=mp.member_id 
		WHERE m.member_id=:member_id 
		ORDER BY version DESC LIMIT 1
	";
	try{
		$rsUser = $mLink->prepare($query);
		$aValues = array(
			':member_id'	=> $memberID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsUser->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$user = $rsUser->fetch(PDO::FETCH_ASSOC);
	//return($error);
	if($user['profile_image'] == ""){
		$profile = "images/profile/default-profile.png";	
	}else{
		$profile = $user['profile_image'];	
	}
	
	if($user['profile_image_tb'] == ""){
		$profileTB = "images/profile/default-profile-tb.png";	
	}else{
		$profileTB = $user['profile_image_tb'];	
	}
	
	switch($info){
		//Members Table
		case 'lastLogin'			: $info = $user['last_login'];break;
		case "active"				: $info = $user['active'];break;
		case "query"				: $info = $preparedQuery;break;
		case "username"				: $info = $user['username'];break;
		case "first name"			: $info = $user['name_first'];break;
		case "last name"			: $info = $user['name_last'];break;
		case "full name"			: $info = "".$user['name_first']." ".$user['name_last']."";break;
		case "admin"				: $info = $user['admin'];break;
		case "email"				: $info = $user['email'];break;
		case "joinDate"				: $info = $user['joined_timestamp'];break;
		case "city"					: $info = $user['city'];break;
		case "state"				: $info = $user['state'];break;
		case "lastLogin"			: $info = $user['last_login'];break;
		case "welcome"				: $info = $user['welcome_message'];break;
		case "maxFunds"				: $info = $user['max_funds'];break;
		
		//keep consistant
		case "firstName"			: $info = $user['name_first'];break;
		case "lastName"				: $info = $user['name_last'];break;
		case "fullName"				: $info = "".$user['name_first']." ".$user['name_last']."";break;
		
		//Members Profile Table
		case "profileImage"			: $info = $profile;break;
		case "profileImageTb"		: $info = $profileTB;break;
		case "img-profileImage"		: $info = '<img src="'.$_SESSION['site_root'].''.$profile.'" border="0" />';break;
		case "img-profileImageTb"	: $info = '<img src="'.$_SESSION['site_root'].''.$profile.'" width="40" height="40" border="0" />';break;
		case "profile_desc"			: $info = $user['about_me'];break;
		case "occupation"			: $info = $user['occupation'];break;
		
		//Customs
		case "profileLink"			: $info = '?page=04-00-00-001&member='.$memberID.'';break;
		case "trackerCode"			: $info = $user['tracker_code'];break;
		case "usernameLink"			: $info = '<a href="?page=04-00-00-001&member='.$memberID.'">'.$user['username'].'</a>';break;
		case "all"					: $info = array(
										//Member Info
										'username'			=> $user['username'],
										'firstName'			=> $user['name_first'],
										'lastName'			=> $user['name_last'],
										'fullName'			=> "".$user['name_first']." ".$user['name_last']."",
										'admin'				=> $user['admin'],
										'email'				=> $user['email'],
										'joinDate'			=> $user['joined_timestamp'],
										'city'				=> $user['city'],
										'state'				=> $user['state'],
										//Profile
										'profileImageURL'	=> $profile,
										'profileImageTbURL'	=> $profileTB,
										'profileImage'		=> '<img src="'.$_SESSION['site_root'].''.$profile.'" border="0" />',
										'profileImageTb'	=> '<img src="'.$_SESSION['site_root'].''.$profile.'" width="40" height="40" border="0" />',
										'profile_desc'		=> $user['about_me'],
										'occupation'		=> $user['occupation'],
										//Custom
										'profileLink'		=> '?page=04-00-00-001&member='.$memberID.'',
										'usernameLink'		=> '<a href="?page=04-00-00-001&member='.$memberID.'">'.$user['username'].'</a>',
										
									);
		break;
		case "help": $info = "<ul>
								<li>username: displays passed member_id's username</li>
								<li>firstName: displays passed member_id's first name</li>
								<li>lastName: displays passed member_id's last name</li>
								<li>fullName: displays passed member_id's first and last name</li>
								<li>admin: displays if user is an admin or not 1 = yes; 0 - no;</li>
								<li>email: displays users email address</li>
								<li>profileImage: displays url path to profile image</li>
								<li>profileImageTb: displays url path to profile image thumbnail</li>
								<li>img-profileImage: displays profile image</li>
								<li>img-profileImageTb: displays profile image thumbnail</li>
								<li>joinDate: Displays timestamp when user joined</li>
								<li>profileLink: Displays the users public profile url</li>
							  <ul>";break;
		
		
		
		default: $info = "Pass \"help\" as the last variable to see options";break;
	}
	
	return $info;
}
//END GET MEMBER INFO


//START GET TICKET INFO
function get_ticket($mLink, $ticketID, $info){
	$query = "
		SELECT *
		FROM ".$_SESSION['support_ticket_table']."
		WHERE ticket_id=:ticket_id
	";
	try{
		$rsTicket = $mLink->prepare($query);
		$aValues = array(
			':ticket_id'	=> $ticketID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsTicket->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$ticket = $rsTicket->fetch(PDO::FETCH_ASSOC);
	
	//GET LABEL FOR PRIORITY
	if($info == "priority"){
		$query = "
			SELECT label
			FROM ".$_SESSION['lists_options_table']."
			WHERE list_id='4' AND value=:value
		";
		
		try{
			$rsPriorityLabel = $mLink->prepare($query);
			$aValues = array(
				':value' 	=> $ticket['priority']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsPriorityLabel->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$priority = $rsPriorityLabel->fetch(PDO::FETCH_ASSOC);
	}
	
	//GET LABEL BOR TICKET STATUS
	if($info == "status"){
		$query = "
			SELECT *
			FROM ".$_SESSION['lists_options_table']."
			WHERE list_id='5' AND value=:value
		";
		try{
			$rsStatus = $mLink->prepare($query);
			$aValues = array(
				':value'	=> $ticket['ticket_status']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsStatus->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$status = $rsStatus->fetch(PDO::FETCH_ASSOC);
	}
	//END GET LABEL FOR TICKET STATUS
	
	//GET LABEL FOR TICKET TYPE
	if($info == "type"){
		$query = "
			SELECT *
			FROM ".$_SESSION['lists_options_table']."
			WHERE list_id='1' AND value=:value
		";
		try{
			$rsType = $mLink->prepare($query);
			$aValues = array(
				':value'	=> $ticket['ticket_type']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsType->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$type = $rsType->fetch(PDO::FETCH_ASSOC);
	}
	//END GET LABEL FOR TICKET TYPE
	
	//GET LABEL FOR Problem Type
	if($info == "problem"){
		$query = "
			SELECT *
			FROM ".$_SESSION['lists_options_table']."
			WHERE list_id=:list_id AND value=:value
		";
		try{
			$rsProblem = $mLink->prepare($query);
			$aValues = array(
				':list_id'	=> $ticket['ticket_type'],
				':value'	=> $ticket['problem_type']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsProblem->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$problem = $rsProblem->fetch(PDO::FETCH_ASSOC);
	}
	//END GET LABEL FOR TICKET TYPE
	
	//START GET FUNDS
	if($info == "funds"){
		$funds = explode("|", $ticket['fund_tickers']);
		$affectedFunds = "";
		foreach($funds as $fund){
			$member = explode("-", $fund);
			
			//echo '<li>'.$fund.'</li>';
			
			$query = "
				SELECT mf.fund_symbol, m.username
				FROM members_fund as mf
				INNER JOIN members as m ON m.member_id=:member_id
				WHERE mf.fund_id=:fund_id
			";
			try{
				$rsFund = $mLink->prepare($query);
				$aValues = array(
					':member_id'	=> $member[0],
					':fund_id'	=> $fund
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsFund->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$fundName = $rsFund->fetch(PDO::FETCH_ASSOC);
			
			
			$affectedFunds .= ''.$fundName['username'].':'.$fundName['fund_symbol'].'|';
				
		}
	}
	//END GET FUNDS
	
	//swithc info
	switch($info){
		case "member": $info = get_member($mLink, $ticket['member_id'], 'full name');break;
		case "assigned": $info = get_member($mLink, $ticket['assigned_to'], 'full name');break;
		case "type": $info = $type['label'];break;
		case "typeID": $info = $ticket['ticket_type'];break;
		case "status": $info = $status['label'];break;
		case "subject": $info = $ticket['ticket_subject'];break;
		case "problem": $info = $problem['label'];break;
		case "description": $info = $ticket['description'];break;
		case "ticker": $info = $ticket['stock_ticker'];break;
		case "funds": $info = $affectedFunds;break;
		case "priority": $info = $priority['label'];break;
		case "help": $info = "<ul>
								<li>member: name of who created the ticket</li>
								<li>assigned: name of who the ticket is assigned to</li>
								<li>type: Ticket Type</li>
								<li>status: Ticket Status</li>
								<li>subject: Ticket Subject</li>
								<li>problem: Problem Type</li>
								<li>description: Ticket Description</li>
								<li>ticker: Stock ticker</li>
								<li>funds: Affected funds</li>
								<li>priority: Ticket's priority</li>
							  <ul>";break;
		default: $info = "type \"help\" to view options";break;
	}
	
	return $info;
}
//END GET TICKET INFO


//START GET TICKET STATUS
function get_status($mLink, $status, $item){
	$query = "
		SELECT *
		FROM ".$_SESSION['lists_options_table']."
		WHERE list_id='5' AND value=:value
	";
	try{
		$rsStatus = $mLink->prepare($query);
		$aValues = array(
			':value'	=> $status
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsStatus->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$status = $rsStatus->fetch(PDO::FETCH_ASSOC);
	
	switch($item){
		case "status": $item = $status['label'];break;
		case "color": $item = $status['special'];break;
	}
	
	return $item;
}
//END GET TICKET STATUS

//START GET TICKET TYPE
function get_ticket_type($mLink, $ticketID){
	$query = "
		SELECT label
		FROM ".$_SESSION['lists_options_table']."
		WHERE list_id='1' AND value=:value
	";
	try{
		$rsType = $mLink->prepare($query);
		$aValues = array(
			':value'	=> $ticketID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsType->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$type = $rsType->fetch(PDO::FETCH_ASSOC);
	
	$ticketType = $type['label'];
	
	return $ticketType;
}
//END GET TICKET TYPE

//START ARRAY SERACH FUNCTION FOR COMMUNITY CA'S
function searchForId($id, $array) {
   foreach ($array as $key => $val) {
	   if ($val[0] === $id) {
		   return $key;
	   }
   }
   return null;
}
//END ARRAY SERACH FUNCTION FOR COMMUNITY CA'S

//START GET FORUM INFO
function get_forum($mLink, $forumID, $item){
	$query = "
		SELECT *
		FROM ".$_SESSION['forums_table']."
		WHERE forum_id=:forum_id
	";
	try{
		$rsForum = $mLink->prepare($query);
		$aValues = array(
			':forum_id'	=> $forumID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsForum->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$forum = $rsForum->fetch(PDO::FETCH_ASSOC);
	
	switch($item){
		case "title": $item = $forum['forum_title'];break;
		case "desc": $item = $forum['forum_description'];break;
		case "active": $item = $forum['active'];
	}
	
	return $item;
}
//END GET FORUM INFO

//START GET FORUM CATEGORY INFO
function get_category($mLink, $catID, $item){
	$query = "
		SELECT *
		FROM ".$_SESSION['forum_categories_table']."
		WHERE cat_id=:cat_id
	";
	try{
		$rsForumCat = $mLink->prepare($query);
		$aValues = array(
			':cat_id'	=> $catID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsForumCat->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$cat = $rsForumCat->fetch(PDO::FETCH_ASSOC);
	
	switch($item){
		case "title": $item = $cat['cat_title'];break;
		case "desc": $item = $cat['cat_description'];break;
		case "active": $item = $cat['active'];break;
	}
	
	return $item;
}
//END GET FORUM INFO

//START GET FORUM CATEGORY INFO
function get_topic($mLink, $topicID, $item){
	$query = "
		SELECT *
		FROM ".$_SESSION['forum_topics_table']."
		WHERE topic_id=:topic_id
	";
	try{
		$rsTopic = $mLink->prepare($query);
		$aValues = array(
			':topic_id'	=> $topicID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsTopic->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$topic = $rsTopic->fetch(PDO::FETCH_ASSOC);
	
	switch($item){
		case "title": $item = $topic['topic_title'];break;
		case "creator": $item = get_member($mLink, $topic['topic_creator'], 'full name');break;
		case "lastUser": $item = get_member($mLink, $topic['topic_last_user'], 'full name');break;
		case "active": $item = $topic['active'];break;
	}
	
	return $item;
}
//END GET FORUM INFO

//START GET FORUM TOPIC REPLIES
function get_topic_replies($mLink, $topicID){
	$query = "
	SELECT post_id
	FROM ".$_SESSION['forum_posts_table']."
	WHERE topic_id=:topic_id
	";
	
	try{
		$rsCntPosts = $mLink->prepare($query);
		$aValues = array(
			':topic_id'	=> $topicID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsCntPosts->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$cntPosts = -1;
	while($posts = $rsCntPosts->fetch(PDO::FETCH_ASSOC)){
		$cntPosts = $cntPosts + 1;	
	}
	
	return $cntPosts;
}
//END GET FORUM TOPIC REPLIES

//START GET FORUM POST REPLIES
function get_post_replies($mLink, $postID){
	
	$query = "
	SELECT post_id
	FROM ".$_SESSION['forum_posts_table']."
	WHERE parent_post_id=:post_id
	";
	
	try{
		$rsCntPosts = $mLink->prepare($query);
		$aValues = array(
			':post_id'	=> $postID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsCntPosts->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$cntPosts = 0;
	while($posts = $rsCntPosts->fetch(PDO::FETCH_ASSOC)){
		$cntPosts = $cntPosts + 1;	
	}
	
	return $cntPosts;
}
//END GET FORUM POST REPLIES



//START GET FORUM TOPIC REPLIES
function get_post_topic_replies($mLink, $topicID, $timestamp){
	
	$query = "
		SELECT post_id
		FROM community_forum_posts
		WHERE topic_id=:topic_id AND timestamp>:timestamp
	";
	
	try{
		$rsCntPosts = $mLink->prepare($query);
		$aValues = array(
			':topic_id'		=> $topicID,
			':timestamp'	=> $timestamp
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsCntPosts->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$cntPosts = 0;
	while($posts = $rsCntPosts->fetch(PDO::FETCH_ASSOC)){
		$cntPosts = $cntPosts + 1;	
		$postID = $posts['post_id'];
	}
	
	//return $cntPosts;
	return $cntPosts;
}
//END GET FORUM TOPIC REPLIES

//START UNREAD POSTS
function get_unread_post($mLink, $topicID, $switch='numPosts'){
	$query = "
		SELECT p.* 
		FROM community_forum_posts AS p
		WHERE p.topic_id=:topic_id 
		AND NOT EXISTS(
			SELECT *
			FROM members_forum_viewed AS v
			WHERE v.viewed_post=p.post_id AND v.member_id=:member_id
		)
		ORDER BY p.timestamp DESC
	";
	
	//Fund Symbols
	try{
		$rsPosts = $mLink->prepare($query);
		$aValues = array(
			':topic_id' 	=> $topicID,
			':member_id'	=> $_SESSION['member_id']
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsPosts->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	if($switch == 'numPosts'){
		$numPosts = $rsPosts->rowCount();
		
		return $numPosts;
	}elseif($switch == 'ID'){
		
		$aPosts = array();
		
		while($post = $rsPosts->fetch(PDO::FETCH_ASSOC)){
			$aPosts[] = $post['post_id'];
		}
		
		return $aPosts;
			
	}
}

//END UNREAD POSTS

//START GET USER TITLE
function get_user_title($memberID, $mLink, $sectionID){
	
	if($memberID != ""){
		$query = "
		SELECT *
		FROM ".$_SESSION['members_flags_table']."
		WHERE member_id=:member_id
		";
		
		try{
			$rsGetFlags = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $memberID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetFlags->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$flags = $rsGetFlags->fetch(PDO::FETCH_ASSOC);
		
		$free			= $flags['free'];
		$basic 			= $flags['basic'];
		$student		= $flags['student'];
		$premium 		= $flags['premium'];
		$master 		= $flags['master'];
		$staff 			= $flags['staff'];
		$moderator 		= $flags['moderator'];
		$superModerator	= $flags['super_moderator'];
		$editor 		= $flags['editor'];
		$superEditor 	= $flags['super_editor'];
		$admin 			= $flags['admin'];
		$superAdmin 	= $flags['super_admin'];
		$membershipLevel= "";  // Need to query for this too

	}else{
		$free			= $_SESSION['free'];
		$basic 			= $_SESSION['basic'];
		$student		= $_SESSION['student'];
		$premium 		= $_SESSION['premium'];
		$master 		= $_SESSION['master'];
		$staff 			= $_SESSION['staff'];
		$moderator 		= $_SESSION['moderator'];
		$superModerator	= $_SESSION['super_moderator'];
		$editor 		= $_SESSION['editor'];
		$superEditor 	= $_SESSION['super_editor'];
		$admin 			= $_SESSION['admin'];
		$superAdmin 	= $_SESSION['super_admin'];
//		$membershipLevel= $_SESSION['subscription']['membershipLevel'];
		$membershipLevel= $_SESSION['subscription']['subData']['alt_product_name']." Member";
	}
/*
	if($free == "1"){
		$userTitle = "Basic (Free) Member";
	}
	if($basic == "1"){
		$userTitle = "Plus Member";
	}
	if($student == "1"){
		$userTitle = "Student Member";
	}
	if($premium == "1"){
		$userTitle = "Pro Member";
	}
*/
	if($staff == "1"){
		$userTitle = "Staff";
	}
	if($moderator == "1"){
		$userTitle = "Moderator";
	}
	if($superModerator == "1"){
		$userTitle = "Moderator";
	}
	if($editor == "1"){
		$userTitle = "Editor";
	}
	if($superEditor == "1"){
		$userTitle = "Editor";
	}
	if($master == "1"){
		$userTitle = "Master";
	}
	if($admin == "1"){
		$userTitle = "Administrator";
	}
	if($superAdmin == "1"){
		$userTitle = "Administrator";
	}

	//Check to see if section is Forums
	if($sectionID != "04-01"/*forums*/){
/*
		if($basic == "1"){
			$userTitle = "Plus Member";
		}
		if($student == "1"){
			$userTitle = "Student Member";
		}
		if($premium == "1"){
			$userTitle = "Pro Member";
		}
		if($master == "1"){
			$userTitle = "Marketocracy Master";
		}



		// MEMBER + MODERATOR
		if($basic == "1" && $moderator == "1"){
			$userTitle = "Plus Member | Moderator";
		}
		if($student == "1" && $moderator == "1"){
			$userTitle = "Student Member | Moderator";
		}
		if($premium == "1" && $moderator == "1"){
			$userTitle = "Pro Member | Moderator";
		}
		if($master == "1" && $moderator == "1"){
			$userTitle = "Master | Moderator";
		}

		if($basic == "1" && $superModerator == "1"){
			$userTitle = "Plus Member | Moderator";
		}
		if($student == "1" && $superModerator == "1"){
			$userTitle = "Student Member | Moderator";
		}
		if($premium == "1" && $superModerator == "1"){
			$userTitle = "Pro Member | Moderator";
		}
		if($master == "1" && $superModerator == "1"){
			$userTitle = "Master | Moderator";
		}

		// MEMBER + EDITOR
		if($basic == "1" && $editor == "1"){
			$userTitle = "Plus Member | Editor";
		}
		if($student == "1" && $editor == "1"){
			$userTitle = "Student Member | Editor";
		}
		if($premium == "1" && $editor == "1"){
			$userTitle = "Pro Member | Editor";
		}
		if($master == "1" && $editor == "1"){
			$userTitle = "Master | Editor";
		}

		if($basic == "1" && $superEditor == "1"){
			$userTitle = "Plus Member | Editor";
		}
		if($student == "1" && $superEditor == "1"){
			$userTitle = "Student Member | Editor";
		}
		if($premium == "1" && $superEditor == "1"){
			$userTitle = "Pro Member | Editor";
		}
		if($master == "1" && $superEditor == "1"){
			$userTitle = "Master | Editor";
		}
*/
		// High Level Overrides
		if($staff == "1"){
			$userTitle = "Staff";
		}
		if($admin == "1"){
			$userTitle = "Administrator";
		}
		if($superAdmin == "1"){
			$userTitle = "Super Administrator";
		}

	}
	//END - Check to see if section is Forums

	/*$query = "
		SELECT *
		FROM members_subscriptions
		WHERE member_id=:member_id AND active='1' AND product_id<'101'
	";
	try{
		$rsSub = $mLink->prepare($query);
		$aValues = array(
			':member_id'	=> $memberID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsSub->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}

	$sub = $rsSub->fetch(PDO::FETCH_ASSOC);*/
	/*
	if($sub['product_id'] == '10'){
		$userTitle = "Manager";
	}*/

	if ($membershipLevel != ""){
		$userTitle = $membershipLevel."<br>".$userTitle;
	}

	return $userTitle;
}
//END GET USER TITLE

//START FORUM DETAILS
function get_forum_info($item, $mLink, $memberID, $forumID, $topicID){

	if($item == 'numPosts'){
		//Query DB for number of posts
		$query = "
			SELECT post_id
			FROM ".$_SESSION['forum_posts_table']."
			WHERE post_creator=:member_id
		";

		try{
			$rsGetPosts = $mLink->prepare($query);
			$aValues = array(
				':member_id' 	=> $memberID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetPosts->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$numPosts = $rsGetPosts->rowCount();	
	}
	
	
	//BEGIN DATA SWITCH
	switch($item){
		case "numPosts": $item = $numPosts;break;	
	}
	
	return $item;
	
}

//END FORUM DETAILS

//START GET POST DETAILS
function get_post_info($item, $mLink, $postID){
	
	$query = "
		SELECT *
		FROM ".$_SESSION['forum_posts_table']."
		WHERE post_id=:post_id
	";
	
	try{
		$rsGetPost = $mLink->prepare($query);
		$aValues = array(
			':post_id' 	=> $postID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetPost->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$post = $rsGetPost->fetch(PDO::FETCH_ASSOC);
	
	switch($item){
		case "convoID"	: $item = $post['convo_id'];break;
		case "catID"	: $item = $post['cat_id'];break;
		case "parentID"	: $item = $post['parent_post_id'];break;
		case "level"	: $item = $post['level'];break;
		case "topicID"	: $item = $post['topic_id'];break;
		case "creator"	: $item = $post['post_creator'];break;
		case "timestamp": $item = $post['timestamp'];break;	
	}
	
	return $item;
}
//END GET POST DETAILS

//START FORUM POST URL

function get_post_url($mLink, $postID){
									
	//Query DB to get Post Data	
	$query = "
		SELECT p.*, t.forum_id
		FROM ".$_SESSION['forum_posts_table']." AS p
		INNER JOIN ".$_SESSION['forum_topics_table']." AS t ON p.topic_id=t.topic_id
		WHERE p.post_id=:post_id
	";
	
	try{
		$rsGetPost = $mLink->prepare($query);
		$aValues = array(
			':post_id' 	=> $postID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetPost->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$post = $rsGetPost->fetch(PDO::FETCH_ASSOC);
	
	//Get static Vars
	$forumID	= $post['forum_id'];
	$catID 		= $post['cat_id'];
	$topicID 	= $post['topic_id'];
	$convoID	= $post['convo_id'];
	
	//We need to figure out what page number the postID will be in: Note that this position will changed based on new posts being added and user's preference on how many posts per page
									
	// Get the default number of posts per page from the session var (setting stored in Database)
	$page_rows = $_SESSION['forum_posts_default'];
	
	//If for some reason the session is not set, set the default to 10 posts per page
	if(!isset($_SESSION['forum_posts_default'])){
		$page_rows = 10;	
	}
	
	//Check to see if user has defined number of rows
	if(isset($_SESSION['forum_page_rows'])){
		//If the user has set their own setting use it
		if($_SESSION['forum_page_rows'] != NULL){
			$page_rows = $_SESSION['forum_page_rows'];	
		}
	}
	
	// Query database the same way as you would on the view topic page
	$query = "
		SELECT post_id
		FROM ".$_SESSION['forum_posts_table']." 
		WHERE topic_id=:topic_id AND level='1'
		ORDER BY timestamp ASC
	";
	
	try{
		$rsPosts = $mLink->prepare($query);
		$aValues = array(
			':topic_id' 	=> $topicID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsPosts->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	//Set counter at zero, this will be used to determin the postion of the post or parent post
	$cnt = 0;
	
	//Loop through results
	while($findPost = $rsPosts->fetch(PDO::FETCH_ASSOC)){
		
		//For each result increase the counter by 1(one)
		$cnt = $cnt + 1;
		
		//When the postID is equal to the convoID/Child break out of the loop so we will have our postion
		if($findPost['post_id'] == $convoID){
			break;
		}
		
		//If the item is a LEVEL 1, test for post id
		if($findPost['post_id'] == $postID){
			break;	
		}
	}
	
	
	//To get the page number, devide the new count by the number of posts per page, then round up. This result in the proper page number for the pagination.
	$pn = ceil($cnt/$page_rows);
	
	//Combine all the variables to generate forum post link
	if($post['level'] != "1"){
		$lastPostURL = '?page=04-01-00-004&forum='.$forumID.'&cat='.$catID.'&topic='.$topicID.'&pn='.$pn.'&child='.$convoID.'&post='.$postID.'';
	}else{
		//$lastPostURL = '?page=04-01-00-004&forum='.$forumID.'&cat='.$catID.'&topic='.$topicID.'&pn='.$pn.'&post='.$postID.'&child='.$postID.'';
		$lastPostURL = '?page=04-01-00-004&forum='.$forumID.'&cat='.$catID.'&topic='.$topicID.'&pn='.$pn.'&post='.$postID.'';
	}
	
	//return $lastPostURL;
	return $lastPostURL;
}
//END FORUM POST URL

//START GET POSITION START DATE
function get_pos_start($mLink, $fundID, $symbol){
	
	$query = "
		SELECT unix_closed
		FROM ".$_SESSION['fund_trades_table']."
		WHERE fund_id=:fund_id AND stockSymbol=:symbol
		ORDER BY unix_closed ASC
		LIMIT 1
	";
	
	try{
		$rsTrade = $mLink->prepare($query);
		$aValues = array(
			':fund_id' 	=> $fundID,
			':symbol'	=> $symbol
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsTrade->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$stockSymbols = array();
	$trade = $rsTrade->fetch(PDO::FETCH_ASSOC);
	
	$posStart = $trade['unix_closed'];
	
	return $posStart;
		
}
//END GET POSITION START DATE

function fund_status($mLink, $fundID){
	$query = "
		SELECT fund_id, active
		FROM ".$_SESSION['fund_table']."
		WHERE fund_id=:fund_id
	";
	
	try{
		$rsGetFund = $mLink->prepare($query);
		$aValues = array(
			':fund_id'		=> $fundID
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetFund->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$fundInfo = $rsGetFund->fetch(PDO::FETCH_ASSOC);
	
	return($fundInfo['active']);	
}

//START GET FUND INFO
function get_funds($mLink, $fundID, $item, $switch=NULL){

	/*$query = "
		SELECT * 
		FROM ".$_SESSION['fund_table']."
		WHERE member_id=:member_id AND fund_id=:fund_id AND active=1
	";*/
	
	$query = "
		SELECT * 
		FROM ".$_SESSION['fund_table']."
		WHERE fund_id=:fund_id AND active=1
	";
	
	try{
		$rsGetFund = $mLink->prepare($query);
		$aValues = array(
			//':member_id' 	=> $_SESSION['member_id'],
			':fund_id'		=> $fundID
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetFund->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$fundInfo = $rsGetFund->fetch(PDO::FETCH_ASSOC);
	
	
	
	switch($switch){
		case 'fundSettings':
			$query = "
				SELECT *
				FROM ".$_SESSION['fund_settings_table']."
				WHERE fund_id=:fund_id
				ORDER BY timestamp DESC
				LIMIT 1
			";
			
			try{
				$rsSettings = $mLink->prepare($query);
				$aValues = array(
					':fund_id'		=> $fundID
					
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsSettings->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$fundSettings = $rsSettings->fetch(PDO::FETCH_ASSOC);
		break;
		
		
		case 'agg':
			$query = "
				SELECT *
				FROM ".$_SESSION['fund_aggregate_table']."
				WHERE fund_id=:fund_id
				ORDER BY timestamp
				LIMIT 1
			";
			
			try{
				$rsAgg = $mLink->prepare($query);
				$aValues = array(
					':fund_id'		=> $fundID
					
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsAgg->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$fundAgg = $rsAgg->fetch(PDO::FETCH_ASSOC);
			
			$aAggAll	= array(
				'returnLastWeek'	=> $fundAgg['returnLastWeek'],
				'currentReturn'		=> $fundAgg['returnSinceInception']
			);
			
			$effectiveDate2 = $fundAgg['effectiveInceptionDate'];
			
			$inceptDate2 = mktime(0,0,0,substr($effectiveDate2, 4,2), substr($effectiveDate2, 6,2), substr($effectiveDate2, 4,4) );
			
			$inceptDate = $fundAgg['effectiveInceptionDate'];
		break;
		
		case 'stocks':
			$query = "
				SELECT fund_id, stockSymbol 
				FROM ".$_SESSION['fund_stratification_basic_table']." 
				WHERE fund_id=:fund_id AND totalShares>'0'
				ORDER BY currentValue DESC
			";
			
			try{
				$rsGetSymbols = $mLink->prepare($query);
				$aValues = array(
					':fund_id' 	=> $fundID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetSymbols->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$stockSymbols = array();
			while($sym = $rsGetSymbols->fetch(PDO::FETCH_ASSOC)) {
				$aStockSymbols[] = $sym['stockSymbol'];
			}
		break;
		
		case 'livePrice':
			$query = "
				SELECT *
				FROM members_fund_liveprice
				WHERE fund_id=:fund_id
			";	
			
			try{
				$rsGetLivePrice = $mLink->prepare($query);
				$aValues = array(
					':fund_id' 	=> $fundID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetLivePrice->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$lp= $rsGetLivePrice->fetch(PDO::FETCH_ASSOC);
			
			$compliant1 = $lp['violatesCash35'];
			$compliant2 = $lp['violatesDiversification25'];
			$compliant3	= $lp['violatesDiversification10'];
			$compliant4	= $lp['isInMargin'];
	
			//Get open tickets for this fund and calculate the BUY POWER(adjusted cash value) for the fund.
			$query = "
				SELECT shares, quote_price
				FROM members_fund_tickets
				WHERE fund_id=:fund_id AND status='pending'
			";
			try{
				$rsGetTickets = $mLink->prepare($query);
				$aValues = array(
					':fund_id' 	=> $fundID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetTickets->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
	
			$pendingCash = 0;
	
			while($tickets= $rsGetTickets->fetch(PDO::FETCH_ASSOC)){
	
				$shares     = $tickets['shares'];
				$quotePrice = $tickets['quote_price'];
				if($quotePrice == NULL){
					$quotePrice = 0;
				}
	
				$pendingCash = $pendingCash + ($shares * $quotePrice);
	
			}
	
			$adjustedCash = $lp['cashValue'] - $pendingCash;
	
			$aLP = array(
				'nav'			=> $lp['nav'],
				'stockValue'	=> $lp['stockValue'],
				'cashValue'		=> $lp['cashValue'],
				'totalValue'	=> $lp['totalValue'],
				'compCash'		=> $compliant1,
				'compDiv25'		=> $compliant2,
				'compDiv10'		=> $compliant3,
				'compMargin'	=> $compliant4,
				'fundName'		=> $fundInfo['fund_name'],
				'adjustedCash'  => $adjustedCash
			);
		break;
			
	}
	
	$trueInceptDate		= $fundInfo['unix_date'];
	
	switch($item){
		case "processing"		: $item = $fundInfo['processing'];break;
		case "trueInceptDate"	: $item = $trueInceptDate;break;
		case "trueInceptDateStr": $item = date('Y-m-d',$trueInceptDate);break;
		case "fundID"			: $item = $fundInfo['fund_id'];break;
		case "fundSymbol"		: $item = $fundInfo['fund_symbol'];break;
		case "fundName"			: $item = $fundInfo['fund_name'];break;	
		case "fund"				: $item = ''.$fundInfo['fund_symbol'].' - '.$fundInfo['fund_name'].'';break;
		case "inceptDate"		: $item = ''.substr($inceptDate, 0, 4).'-'.substr($inceptDate, 4, 2).'-'.substr($inceptDate, 6, 2).'';break;
		case "unixIncept"		: $item = mktime(5, 0, 0, substr($inceptDate, 4, 2), substr($inceptDate, 6, 2), substr($inceptDate, 0, 4));break;
		case "stockSymbols"		: $item = $aStockSymbols;break;
		case "lp"				: $item = $aLP;break;
		case "ident"			: $item = array('symbol'=>$fundInfo['fund_symbol'], 'name'=>$fundInfo['fund_name']);break;
		case "aggAll"			: $item = $aAggAll;break;
		case "fundColor"		: $item = $fundSettings['fund_color'];break;
		case "fundIncept"		: $item = $fundInfo['unix_date'];break;
		case "fundBasic"		: $item = array('fundSymbol'=>$fundInfo['fund_symbol'],'fundName'=>$fundInfo['fund_name']);
		case "fundInfo"			: $item = $fundInfo;
		//case "rawIncept"		: $item = $inceptDate;
	}
	
	return $item;
}


//start win/loos ratio
function win_loss($mLink, $fundID){
	
}

//start avg gain loss
function avg_gain_loss($mLink, $fundID){
	
}

function get_global_funds($mLink, $fundID, $item, $switch){

	$query = "
		SELECT * 
		FROM ".$_SESSION['fund_table']."
		WHERE fund_id=:fund_id AND active=1
	";
	
	try{
		$rsGetFund = $mLink->prepare($query);
		$aValues = array(
			':fund_id'		=> $fundID
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetFund->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$fundInfo = $rsGetFund->fetch(PDO::FETCH_ASSOC);
	
	if($switch == "agg"){
		$query = "
			SELECT *
			FROM ".$_SESSION['fund_aggregate_table']."
			WHERE fund_id=:fund_id
			ORDER BY timestamp
			LIMIT 1
		";
		
		try{
			$rsAgg = $mLink->prepare($query);
			$aValues = array(
				':fund_id'		=> $fundID
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsAgg->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$fundAgg = $rsAgg->fetch(PDO::FETCH_ASSOC);
		
		$inceptDate = $fundAgg['effectiveInceptionDate'];
	}
	
	
	switch($item){
		case "fundID": 		$item = $fundInfo['fund_id'];break;
		case "fundSymbol":	$item = $fundInfo['fund_symbol'];break;
		case "fundName": 	$item = $fundInfo['fund_name'];break;	
		case "fund":		$item = ''.$fundInfo['fund_symbol'].' - '.$fundInfo['fund_name'].'';break;
		case "inceptDate": 	$item = ''.substr($inceptDate, 0, 4).'-'.substr($inceptDate, 4, 2).'-'.substr($inceptDate, 6, 2).'';break;
	}
	
	return $item;
}
//END GET FUND INFO

//START CREATE DATE RANGE FORUMLA

function createDateRangeArray($strDateFrom,$strDateTo,$special)
{
    // takes two dates formatted as YYYY-MM-DD and creates an
    // inclusive array of the dates between the from and to dates.

    // could test validity of dates here but I'm already doing
    // that in the main script

    $aryRange=array();

    $iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2),     substr($strDateFrom,8,2),substr($strDateFrom,0,4));
    $iDateTo=mktime(1,0,0,substr($strDateTo,5,2),     substr($strDateTo,8,2),substr($strDateTo,0,4));
	
	
	if($special == "dash"){
		if ($iDateTo>=$iDateFrom)
		{
			array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry
			while ($iDateFrom<$iDateTo)
			{
				$iDateFrom+=86400; // add 24 hours
				array_push($aryRange,date('Y-m-d',$iDateFrom));
			}
		}
	}else{
		if ($iDateTo>=$iDateFrom)
		{
			array_push($aryRange,date('Ymd',$iDateFrom)); // first entry
			while ($iDateFrom<$iDateTo)
			{
				$iDateFrom+=86400; // add 24 hours
				array_push($aryRange,date('Ymd',$iDateFrom));
			}
		}
	}
    return $aryRange;
}

//END CREATE DATE RANGE FORUMLA

//START DATE LIST
function date_list($mLink, $MDY, $exclude=NULL, $select=NULL, $dbYear=true){
	if($MDY == "day"){
		$listID = '6';
	}elseif($MDY == "month"){
		$listID = '7';
	}elseif($MDY == "year"){
		$listID = '8';
	}
	
	if($exclude != NULL){
		$where = 'AND value<>'.$exclude.'';	
	}
	
	if($dbYear == true){
		$query = "
			SELECT label, value 
			FROM ".$_SESSION['lists_options_table']."
			WHERE list_id=:list_id ".$where."
		";
		
		try{
			$rsList = $mLink->prepare($query);
			$aValues = array(
				':list_id' => $listID
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsList->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$html = '';
		
		while($list = $rsList->fetch(PDO::FETCH_ASSOC)){
			
			if($select != NULL){
				if($select == $list['value']){
					$checked = 'selected';	
				}else{
					$checked = '';	
				}
			}
			$html .= '<option value="'.$list['value'].'" '.$checked.'>'.$list['label'].'</option>';
				
		}
	}else{
		//get years from inception, and make ongoing
		$startYear		= 2000;
		$currentYear	= date('Y');
		
		//echo $currentYear;
		
		$yearLoop 		= true;
		$loopYear 		= $startYear;
		$html			= '';
		
		while($yearLoop){
			
			$html .= '<option value="'.$loopYear.'"'.($loopYear == date("Y") ? " selected" : "").'>'.$loopYear.'</option>';
			
			if($loopYear <= $currentYear){
				$loopYear++;
			}else{
				$yearLoop = false;	
			}
			
				
		}
	}
	
	return $html;
	
}
//END DATE LIST

//START GET PAGE TITLE
//END GET PAGE TITLE

//START HASHTAG FUNCTION
/*function hashtag($string) {
	
	//Set Vars
	$htag = '#';
	$aString = explode(" ", $string);
	$aStringCnt = count($aString);
	$i = 0;
	
	while($i < $aStringCnt) {
		
		if(substr($aString[$i], 0, 1) === $htag){
			$aString[$i] = '<a href="javascript:void(0);">'.$aString[$i].'</a>';	
		}
		
		$i++;
			
	}
	
	$string = implode(" ", $aString);
	
	return $string;
		
}*/

function hashtag($str){
	$regex = "/#+([a-zA-Z0-9_]+)/";
	$str = preg_replace($regex, '<a href="hashtag.php?tag=$1">$0</a>', $str);	
	return($str);
}
//END HASHTAG FUNCTION


//START Is stock symbol top 50%
function isStock50($mLink, $stockSymbol, $fundID){
	$query = "
		SELECT fundRatio, stockSymbol
		FROM ".$_SESSION['fund_stratification_basic_table']." 
		WHERE fund_id=:fund_id AND currentValue > '0'
		ORDER BY fundRatio DESC
	";
	
	//START: Query for counting the amount of rows for rowspan
	try{
		$rsPos = $mLink->prepare($query);
		$aValues = array(
			':fund_id'	=> $fundID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsPos->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	//Set vars to zero for calculations
	$first50 = 0;
	$aCheckSymbol = array();
	
	while($position = $rsPos->fetch(PDO::FETCH_ASSOC)){
		//Get ratio from db
		$ratio = $position['fundRatio'];
		
		//add ratio to ratio prior
		$first50 = $first50 + $ratio;
		
		$aCheckSymbol[] = $position['stockSymbol'];
		
		//if the combined ratio is great than 50 or 50% stop the loop
		if($first50 > .5){
			break;	
		}
	}
	
	if(in_array($stockSymbol, $aCheckSymbol)){
		$found = 1;	
	}else{
		$found = 0;	
	}
	
	return $found;
}
//END is stock symbol top 50%

//START Forum Access
function forumAccess($mLink, $levelID, $level){
	
	switch($level){
		case 'forum':
			$query = "
				SELECT *
				FROM ".$_SESSION['forums_table']."
				WHERE forum_id=:forum_id
			";
			try{
				$rsGetForum = $mLink->prepare($query);
				$aValues = array(
					':forum_id' => $levelID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetForum->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$forum = $rsGetForum->fetch(PDO::FETCH_ASSOC);
			
			//Master variable to allow access, anything other than a zero will result in access
			$allowAccess = 0;
			
			if($forum['active'] == '1'){
				//Forum is active
				
				if($forum['allow_members_flags'] == NULL && $forum['allow_forum_groups'] == NULL && $forum['allow_members'] == NULL){
					$allowAccess = 1;
				}else{
						
							
					if($forum['allow_members_flags'] != NULL){
						$aMemberFlags = explode("|",$forum['allow_members_flags']);
					}else{
						$aMemberFlags = $forum['allow_members_flags'];	
					}
					
					if($forum['allow_forum_groups'] != NULL){
						$aForumGroups	= explode("|",$forum['allow_forum_groups']);
					}else{
						$aForumGroups 	= $forum['allow_forum_groups'];
					}
					
					if($forum['allow_members'] != NULL){
						$aMembers	= explode("|",$forum['allow_members']);
					}else{
						$aMembers 	= $forum['allow_members'];
					}
					
					//START FORUM GROUPS | Loop through all allowed forum groups for this forum
					foreach($aForumGroups as $key => $forumGroupID){
				
						//Query each forum group to see which members are apart of the group
						$query = "
							SELECT *
							FROM ".$_SESSION['forum_groups_table']."
							WHERE group_id=:group_id
						";
						try{
							$rsGetGroup = $mLink->prepare($query);
							$aValues = array(
								':group_id' 	=> $forumGroupID
							);
							$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
							$rsGetGroup->execute($aValues);
						}
						catch(PDOException $error){
							// Log any error
							file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
						}
						$group = $rsGetGroup->fetch(PDO::FETCH_ASSOC);
						
						$aGroupMembers = explode('|',$group['members']);
						
						//Loop through the allowed members, if you find a match increment the allowAccess by one
						if(in_array($_SESSION['member_id'], $aGroupMembers)){
							$allowAccess++;	
						}
						/*foreach($aGroupMembers as $key => $groupMember){
							if($groupMember == $_SESSION['member_id']){
								$allowAccess++;	
							}
						}*/
					}
					//END FORUM GROUPS
					
					//START MEMBERS FLAGS
					foreach($aMemberFlags as $key => $flag){
						if($_SESSION[''.$flag.''] == 1){
							$allowAccess++;
						}
					}
					//END MEMBERS FLAGS
					
					//START ALLOW MEMBERS
					if(in_array($_SESSION['member_id'], $aMembers)){
						$allowAccess++;	
					}
					//END ALLOW MEMBERS 
					
					if($aMemberFlags == NULL && $aForumGroups == NULL && $aMembers == NULL){
						$allowAccess = 1;	
					}
				}
				
				if($_SESSION['admin'] == '1' || $_SESSION['super_moderator'] == '1'){
					$allowAccess = 1;	
				}
				
			}else{
				//forum not active, only allow admins and super moderators
				if($_SESSION['admin'] == '1' || $_SESSION['super_moderator'] == '1'){
					$allowAccess = 1;	
				}else{
					$allowAccess = 0;	
				}
			}
		break;
		
		case 'category':
		
			$query = "
				SELECT *
				FROM ".$_SESSION['forum_categories_table']."
				WHERE cat_id=:cat_id
			";
			try{
				$rsGetCat = $mLink->prepare($query);
				$aValues = array(
					':cat_id' => $levelID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetCat->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$cat = $rsGetCat->fetch(PDO::FETCH_ASSOC);
			
			//Master variable to allow access, anything other than a zero will result in access
			$allowAccess = 0;
			
			if($cat['active'] == '1'){
				//Forum is active
			
				if($cat['allow_members_flags'] != NULL){
					$aMemberFlags = explode("|",$cat['allow_members_flags']);
				}else{
					$aMemberFlags = $cat['allow_members_flags'];	
				}
				
				if($cat['allow_forum_groups'] != NULL){
					$aForumGroups	= explode("|",$cat['allow_forum_groups']);
				}else{
					$aForumGroups 	= $cat['allow_forum_groups'];
				}
				
				if($cat['allow_members'] != NULL){
					$aMembers	= explode("|",$cat['allow_members']);
				}else{
					$aMembers 	= $cat['allow_members'];
				}
				
				//START FORUM GROUPS | Loop through all allowed forum groups for this forum
				foreach($aForumGroups as $key => $forumGroupID){
			
					//Query each forum group to see which members are apart of the group
					$query = "
						SELECT *
						FROM ".$_SESSION['forum_groups_table']."
						WHERE group_id=:group_id
					";
					try{
						$rsGetGroup = $mLink->prepare($query);
						$aValues = array(
							':group_id' 	=> $forumGroupID
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsGetGroup->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					$group = $rsGetGroup->fetch(PDO::FETCH_ASSOC);
					
					$aGroupMembers = explode('|',$group['members']);
					
					//Loop through the allowed members, if you find a match increment the allowAccess by one
					if(in_array($_SESSION['member_id'], $aGroupMembers)){
						$allowAccess++;	
					}
					/*foreach($aGroupMembers as $key => $groupMember){
						if($groupMember == $_SESSION['member_id']){
							$allowAccess++;	
						}
					}*/
				}
				//END FORUM GROUPS
				
				//START MEMBERS FLAGS
				foreach($aMemberFlags as $key => $flag){
					if($_SESSION[''.$flag.''] == 1){
						$allowAccess++;
					}
				}
				//END MEMBERS FLAGS
				
				//START ALLOW MEMBERS
				if(in_array($_SESSION['member_id'], $aMembers)){
					$allowAccess++;	
				}
				//END ALLOW MEMBERS 
				
				if($aMemberFlags == NULL && $aForumGroups == NULL && $aMembers == NULL){
					$allowAccess = 1;	
				}
				
				if($_SESSION['admin'] == '1' || $_SESSION['super_moderator'] == '1'){
					$allowAccess = 1;	
				}
				
			}else{
				//forum not active, only allow admins and super moderators
				if($_SESSION['admin'] == '1' || $_SESSION['super_moderator'] == '1'){
					$allowAccess = 1;	
				}else{
					$allowAccess = 0;	
				}
			}
		
		break;	
	}
	
	
	
	return $allowAccess;
}
//END FOrum Access


//START Forum Admin Access
function forumAdminAccess($mLink, $levelID, $level){ //level ID is ForumID or CategoryID
	
	if($level == 'forum'){
		$query = "
			SELECT *
			FROM ".$_SESSION['forums_table']."
			WHERE forum_id=:forum_id
		";
		try{
			$rsGetForum = $mLink->prepare($query);
			$aValues = array(
				':forum_id' => $levelID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetForum->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$forum = $rsGetForum->fetch(PDO::FETCH_ASSOC);
		
		//Master variable to allow access, anything other than a zero will result in access
		$allowAccess = 0;
		
		if($forum['active'] == '1'){
			//Forum is active
		
			if($forum['forum_admin_flags'] != NULL){
				$aMemberAdminFlags = explode("|",$forum['forum_admin_flags']);
				
				//START MEMBERS FLAGS
				foreach($aMemberAdminFlags as $key => $flag){
					if($_SESSION[''.$flag.''] == 1){
						$adminAccess++;
					}
				}
				//END MEMBERS FLAGS
			}else{
				$aMemberAdminFlags = $forum['forum_admin_flags'];	
			}
			
			if($forum['forum_admin_groups'] != NULL){
				$aForumAdminGroups	= explode("|",$forum['forum_admin_groups']);
				
				//START FORUM GROUPS | Loop through all allowed forum groups for this forum
				foreach($aForumAdminGroups as $key => $forumGroupID){
			
					//Query each forum group to see which members are apart of the group
					$query = "
						SELECT *
						FROM ".$_SESSION['forum_groups_table']."
						WHERE group_id=:group_id
					";
					try{
						$rsGetGroup = $mLink->prepare($query);
						$aValues = array(
							':group_id' 	=> $forumGroupID
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsGetGroup->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					$group = $rsGetGroup->fetch(PDO::FETCH_ASSOC);
					
					$aGroupMembers = explode('|',$group['members']);
					
					//Loop through the allowed members, if you find a match increment the allowAccess by one
					if(in_array($_SESSION['member_id'], $aGroupMembers)){
						$adminAccess++;	
					}
				}
				//END FORUM GROUPS
			}else{
				$aForumAdminGroups 	= $forum['forum_admin_groups'];
			}
			
			if($forum['forum_admin_members'] != NULL){
				$aAdminMembers	= explode("|",$forum['forum_admin_members']);
				
				//START ALLOW MEMBERS
				if(in_array($_SESSION['member_id'], $aAdminMembers)){
					$adminAccess++;	
				}
				//END ALLOW MEMBERS 
			}else{
				$aAdminMembers 	= $forum['forum_admin_members'];
			}
			
			if($_SESSION['admin'] == '1' || $_SESSION['super_moderator'] == '1'){
				$adminAccess = 1;	
			}
			
		}else{
			//forum not active, only allow admins and super moderators
			if($_SESSION['admin'] == '1' || $_SESSION['super_moderator'] == '1'){
				$adminAccess = 1;	
			}	
		}
	}//end level is FORUM
	
	if($level == 'cat'){
		if($_SESSION['admin'] == '1' || $_SESSION['super_moderator'] == '1'){
			$adminAccess = 1;	
		}	
	}
	
	
	return $adminAccess;
	
}
//END Forum Admin Access


//START Add Event Record
function addEventRecord($mLink, $memberID, $event, $detail){
	
	$query = "
		INSERT INTO ".$_SESSION['eventslog_table']." (
			member_id,
			timestamp,
			event,
			detail
		) VALUE (
			:member_id,
			UNIX_TIMESTAMP(),
			:event,
			:detail
		)
	";
	
	try{
		$rsAddEvent = $mLink->prepare($query);
		$aValues = array(
			':member_id'	=> $memberID,
			':event'		=> $event,
			':detail'		=> $detail
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsAddEvent->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	return $error;
	
}
//END Add Event Record


//START GET GROUP MEMBERS
function get_group($mLink, $groupID){
	
	$query = "
		SELECT *
		FROM ".$_SESSION['connections_group_table']."
		WHERE group_id=:group_id AND active='1'
	";
	
	try{
		$rsGetGroup = $mLink->prepare($query);
		$aValues = array(
			':group_id' 	=> $groupID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetGroup->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$group = $rsGetGroup->fetch(PDO::FETCH_ASSOC);
	
	$aGroupMembers = explode('|', $group['members']);
	
	return $aGroupMembers;
}
//END GET GROUP MEMBERS

//START GET MEMBER CONNECTIONS
function get_member_connections($mLink, $memberID){
	$query = "
		SELECT *
		FROM ".$_SESSION['connections_table']."
		WHERE member_id=:member_id AND status='active'
	";
	
	try{
		$rsGetConnects = $mLink->prepare($query);
		$aValues = array(
			':member_id' 	=> $memberID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetConnects->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aConnections = array();
	while($connects = $rsGetConnects->fetch(PDO::FETCH_ASSOC)){
		
		$aConnections[] = $connects['connection'];
		
	}
	
	return $aConnections;
}
//END GET MEMBER CONNECTIONS

function tokenTruncate($string, $your_desired_width) {
  $parts = preg_split('/([\s\n\r]+)/', $string, null, PREG_SPLIT_DELIM_CAPTURE);
  $parts_count = count($parts);

  $length = 0;
  $last_part = 0;
  for (; $last_part < $parts_count; ++$last_part) {
    $length += strlen($parts[$last_part]);
    if ($length > $your_desired_width) { break; }
  }

  return implode(array_slice($parts, 0, $last_part));
}

//START PRINT SYSTEM LIST
function printList($mLink, $listID, $value){
	
	$query = "
		SELECT *
		FROM ".$_SESSION['lists_options_table']."
		WHERE list_id=:list_id AND disabled='0'
		ORDER BY sequence ASC
	";
	
	try{
		$rsGetLists = $mLink->prepare($query);
		$aValues = array(
			':list_id' 	=> $listID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetLists->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	while($list = $rsGetLists->fetch(PDO::FETCH_ASSOC)){
		
		if($value == $list['value']){
			$showList .= '<option value="'.$list['value'].'" selected>'.$list['label'].'</option>';
		}else{
			$showList .= '<option value="'.$list['value'].'">'.$list['label'].'</option>';	
		}
	}
	
	
	return $showList;
}
//END PRINT SYSTEM LIST

//START GET MEMBER BADGES
function get_fund_badges($mLink, $memberID, $switch, $aVars=NULL){
	
	//Get Badges
	$query = "
		SELECT * 
		FROM `rankings_process_results` 
		WHERE member_id=:member_id AND rank_unix_date=(SELECT MAX(rank_unix_date) FROM `rankings_process_results` WHERE 1 LIMIT 1)
	";
	
	$query = "
		SELECT *
		FROM rank_achievements
		WHERE member_id=:member_id AND as_of_date=(SELECT MAX(as_of_date) FROM rank_achievements)
	";
	
	try{
		$rsBadges = $mLink->prepare($query);
		$aValues = array(
			':member_id' 	=> $memberID
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsBadges->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aFundBadges = array();
	
	while($badge = $rsBadges->fetch(PDO::FETCH_ASSOC)){
		
		$rankDate		= $badge['as_of_date'];
		$rankUnixDate	= $badge['as_of_timestamp'];
		
		$aFundBadges[]	= $badge['badge_id'];
		
		$aFundBadges[$badge['fund_id']] = array(
			'rank_unix_date'		=> $rankDate,
			'rank_date'				=> $rankUnixDate,
			'badgeIds'				=> $aFundBadges,
		);
		
	}
	
	
	
	$listBadges = $getBadges['badge_ids'];
	
	switch($switch){
		case 'forum-title-bar':
			
			foreach($aFundBadges as $fundID=>$aData){
				
				$aBadges[$memberID][$fundID] = $aData['badgeIds'];
					
			}
			
			return($aBadges);
			
		break;
		
		case 'show-badges-modal':
			
			foreach($aFundBadges as $fundID=>$aData){
				$aBadges[$fundID] = $aData['badgeIds'];
			}
			
			return($aBadges);
			
		break;
	}
		
}

function get_badges($mLink, $memberID, $switch, $split, $size='40'){
	
	//START build badge array
	$query = "
		SELECT * 
		FROM ".$_SESSION['badges_table']."
		WHERE active='1'
	";
	
	try{
		$rsBadge = $mLink->prepare($query);
		$aValues = array();
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsBadge->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aListBadges = array();
		
	while($badge = $rsBadge->fetch(PDO::FETCH_ASSOC)){
		$aListBadges[$badge['badge_id']] = array(
			'badge_id'		=> $badge['badge_id'],
			'badge_name'	=> $badge['badge_name'],
			'type'			=> $badge['badge_type'],
			'badge_img'		=> '/images/badges/'.$badge['badge'],
			'badge_desc'	=> $badge['badge_desc'],
			'badge_group'	=> $badge['group'],
			'badge_weight'	=> $badge['weight'],
			'sub_type'		=> $badge['sub_type']
		);
		
	}
	//END build badge array
	
	
	
	//Get badges for member
	$query = "
		SELECT fund_id, badges
		FROM ".$_SESSION['fund_settings_table']."
		WHERE fund_id LIKE :member_id AND badges<>''
	";
	
	$query = "
		SELECT fund_id, badge_ids as badges
		FROM rankings_process_results
		WHERE member_id=:member_id AND rank_date=:period
		
	";
	
	$query = "
		SELECT badge_id, fund_id, as_of_date
		FROM rank_achievements
		WHERE member_id=:member_id AND as_of_date=(SELECT MAX(as_of_date) FROM rank_achievements)
	";
	
	try{
		$rsBadges = $mLink->prepare($query);
		$aValues = array(
			//':member_id' => $memberID.'-%'
			':member_id' => $memberID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsBadges->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}

	while($badges = $rsBadges->fetch(PDO::FETCH_ASSOC)){
	
		$aFundBadges[$badges['fund_id']][] = $badges['badge_id'];
	}
	//END get badges for member	
	
	$aListFundBadges = array();
	
	//START Create array for member badges
	foreach($aFundBadges as $fundID=>$aBadges){
				
		$fundSymbol2 	= get_funds($mLink, $fundID, 'fundSymbol');
		//return($aBadges);
		foreach($aBadges as $key=>$badgeID){
			
			
			
			$aListFundBadges[] = array(
				'fundSymbol'	=> $fundSymbol2,
				'badge_id'		=> $badgeID,
				'weight'		=> $aListBadges[$badgeID]['badge_weight'],
				'sub_type'		=> $aListBadges[$badgeID]['sub_type']
			);
			
			
		}
	}
	
	//return($aListFundBadges);
	
	//order badge array by badge weight
	usort($aListFundBadges, function($a, $b) {
		return $a['weight'] - $b['weight'];
	});
	//END Create array for member badges
	
	if($switch == 'short_list'){
		//Clear vars
		unset($aShowBadges);
		unset($badge10);
		unset($badge5);
		unset($badge3);
		unset($badge1);
		
		foreach($aListFundBadges as $key=>$aBadges2){
			
			if(!isset($badge10)){
				if($aBadges2['sub_type'] == '10'){
					$badge10 = $key;
					
					$aShowBadges[$key] = $aListFundBadges[$key];
				}
			}
			
			if(!isset($badge5)){
				if($aBadges2['sub_type'] == '5'){
					$badge5 = $key;
					
					$aShowBadges[$key] = $aListFundBadges[$key];
				}	
			}
			
			if(!isset($badge3)){
				if($aBadges2['sub_type'] == '3'){
					$badge3 = $key;
					
					$aShowBadges[$key] = $aListFundBadges[$key];
				}	
			}
			
			if(!isset($badge1)){
				if($aBadges2['sub_type'] == '1'){
					$badge1 = $key;
					
					$aShowBadges[$key] = $aListFundBadges[$key];
				}	
			}
				
		}
		
		//create short list
		$shortCnt = 0;
		
		//check for split
		if(isset($split) || $split == 'none'){
			$splitCnt = 0;
		}
		
		foreach($aShowBadges as $key=>$aBadgeInfo){
			
			$shortCnt++;

			
			if(isset($split) || $split == 'none'){$splitCnt++;}
						
			if($shortCnt <= 4){
				
				$badgeImg 		= $aListBadges[$aBadgeInfo['badge_id']]['badge_img'];
				$badgeDesc		= $aListBadges[$aBadgeInfo['badge_id']]['badge_desc'];
				$fundSymbol3	= $aBadgeInfo['fundSymbol'];
						
				$showBadges2 .= '<a href="#show-badges" data-toggle="modal" onclick="loadBadges(\''.$post['member_id'].'\',\''.$fullName.'\')"><img src="'.$badgeImg.'" alt="'.$badgeDesc.'" title="'.strtoupper($fundSymbol3).': '.$badgeDesc.'" width="'.$size.'" height="'.$size.'" border="0" style="border:none !important;"/></a>&nbsp;'; 
				
				//check for row split
				if(isset($split) || $split == 'none'){
					if($splitCnt == $split){
						$splitCnt = 0;
						$showBadges2 .= '<br />';	
					}
				}
				//end check for row split
				
			}
				
		}
		
		return($showBadges2);
	}//end short list
	
	
}
//END GET MEMBER BADGES

function get_last_rank_note($mLink, $fundID){
	
		$query = "
			SELECT note_timestamp
			FROM rank_notes
			WHERE fund_id=:fund_id AND deleted IS NULL
			ORDER BY note_timestamp DESC
			LIMIT 1
		";
		try{
			$rsNote = $mLink->prepare($query);
			$aValues = array(':fund_id'=>$fundID);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsNote->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$note = $rsNote->fetch(PDO::FETCH_ASSOC);
		
		//return('hi');
		return($note['note_timestamp']);
	
}

function get_badge_info($mLink, $aBadges=NULL, $switch='all', $size='40'){
	
	//START build badge array
	$query = "
		SELECT * 
		FROM ".$_SESSION['badges_table']."
		WHERE active='1'
	";
	
	try{
		$rsBadge = $mLink->prepare($query);
		$aValues = array();
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsBadge->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aListBadges = array();
		
	while($badge = $rsBadge->fetch(PDO::FETCH_ASSOC)){
		$aListBadges[$badge['badge_id']] = array(
			'badge_id'		=> $badge['badge_id'],
			'badge_name'	=> $badge['badge_name'],
			'type'			=> $badge['badge_type'],
			'badge_img'		=> '/images/badges/'.$badge['badge'],
			'badge_desc'	=> $badge['badge_desc'],
			'badge_group'	=> $badge['group'],
			'badge_weight'	=> $badge['weight'],
			'sub_type'		=> $badge['sub_type']
		);
		
	}
	//END build badge array
	
	
	switch($switch){
		case 'all':
			return($aListBadges);
		break;
	}
	
}

?>
