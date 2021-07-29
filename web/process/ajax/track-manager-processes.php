<?php
//Marketocracy Inc.
//Community Forum Processing scripts

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


switch($process){
	
	//+---------------------------------------------------------------------------------------------------------+
	//|										Track Manager Request										
	//+---------------------------------------------------------------------------------------------------------+
	case 'track-manager-submit':

		$memberID		= $_REQUEST['member_id'];
		$userID			= $_REQUEST['user_id'];
		$nameFirst		= $_REQUEST['name_first'];
		$nameLast		= $_REQUEST['name_last'];
		$email			= $_REQUEST['email'];
		$frequency		= $_REQUEST['frequency'];
		$aFunds			= $_REQUEST['funds'];
		$username		= $_REQUEST['username'];
		$managerUpdate	= $_REQUEST['manager-updates'];
		$managerArticle	= $_REQUEST['manager-articles'];
		$aErrors		= array();
		
		
		if($managerArticle != '1'){
			$managerArticle = '0';	
		}
		if($managerUpdate != '1'){
			$managerUpdate = '0';	
		}
		
		//print_r($aFunds);
		
		if(empty($nameFirst)){
			$aErrors[] = 'Please provide your first name.';	
		}
		
		if(empty($email)){
			$aErrors[] = 'Please provide a valid email address';	
		}else{
			if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$aErrors[] =  "The email address you provided is not valid.";
			}	
		}
		
		if(empty($aFunds)){
			$aErrors[] = "Please select at least one(1) fund to track.";	
		}
		
		if(empty($memberID)){
			$aErrors[] = 'We are experiencing technical difficulties. Please send and email to help@marketocracy.com if you continue to experience this issue. Error: Manager ID not found.';	
		}
		
		#check to see if email is already subscribed for this member
		$query = "
			SELECT email, track_funds, code
			FROM ".$_SESSION['fund_tracking_table']."
			WHERE email=:email AND member_id=:member_id
			LIMIT 1
		";
		try{
			$rsCheck = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $memberID,
				':email'		=> $email
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsCheck->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$checkSub = $rsCheck->fetch(PDO::FETCH_ASSOC);
		
		$checkEmail				= $checkSub['email'];
		$aCheckFunds 			= explode('|', $checkSub['track_funds']);
		$code					= $checkSub['code'];
		$aFundIDs				= array();	
		
		$recordExist			= false;
		
		foreach($aFunds as $key=>$fundInfo){
			$aFundInfo 			= explode('|', $fundInfo);
			$submitFundID 		= $aFundInfo[0];
			$submitFundSymbol	= $aFundInfo[1];
			$aFundIDs[]			= $submitFundID;
			
			if(in_array($submitFundID, $aCheckFunds)){
				$aErrors[] 		= 'This email is already subscribed for fund: '.$submitFundSymbol.'.';
					
			}else{
				$recordExist	= true;
				$aCheckFunds[] 	= $submitFundID;	
				
			}
		}
		
		if($checkEmail == ''){
			$recordExist = false;	
		}
		
		//$aErrors[] = 'test';
		
		if(empty($aErrors)){
			
			
			$spam 				= 0;
			$unsubscribeLink 	= 'https://'.$username.'.mytrackrecord.com?page=19-00-00-001&tab=unsubscribe&code='.$code;
			$subscriptionLink	= 'https://'.$username.'.mytrackrecord.com?page=19-00-00-001&tab=info&code='.$code;
			$trackFunds			= implode('|', array_filter($aCheckFunds));
			
			$member 			= get_member($mLink, $memberID, 'all');
			$memberName 		= $member['fullName'];

			
			
			if($recordExist != true){
				$code 	= substr(strtoupper(md5(uniqid($email, true))), 0, 10);
				
				#insert tracker info into db
				$query = "
					INSERT INTO ".$_SESSION['fund_tracking_table']."(
						member_id,
						user_id,
						code,
						first_name,
						last_name,
						email,
						spam,
						frequency,
						track_funds,
						manager_updates,
						manager_articles,
						timestamp
					)VALUES(
						:member_id,
						:user_id,
						:code,
						:first_name,
						:last_name,
						:email,
						:spam,
						:frequency,
						:track_funds,
						:manager_updates,
						:manager_articles,
						UNIX_TIMESTAMP()
					)
				";
				try{
					$rsInsert = $mLink->prepare($query);
					$aValues = array(
						':member_id'		=> $memberID,
						':user_id'			=> $userID,
						':code'				=> $code,
						':first_name'		=> $nameFirst,
						':last_name'		=> $nameLast,
						':email'			=> $email,
						':spam'				=> $spam,
						':frequency'		=> $frequency,
						':track_funds'		=> $trackFunds,
						':manager_updates'	=> $managerUpdate,
						':manager_articles'	=> $managerArticle
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsInsert->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				echo $error;
			}else{
				
				$query = "
					UPDATE ".$_SESSION['fund_tracking_table']."
					SET track_funds=:track_funds, manager_updates=:manager_updates, manager_articles=:manager_articles, last_name=:last_name
					WHERE email=:email AND member_id=:member_id
				";
				try{
					$rsUpdate = $mLink->prepare($query);
					$aValues = array(
						':member_id'		=> $memberID,
						':email'			=> $email,
						':track_funds'		=> $trackFunds,
						':manager_updates'	=> $managerUpdate,
						':manager_articles'	=> $managerArticle,
						':last_name'		=> $nameLast
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsUpdate->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}	
				echo $error;
			}
			
			#Send confirmation Email
			/*$aEmailVars = array(
				'name_first'		=> $nameFirst,
				'frequency'			=> $frequency,
				'unsubscribe_link'	=> $unsubscribeLink,
				'subscription_link'	=> $subscriptionLink,
				'manager'			=> $memberName,
				'subsc_code'		=> $code
			);
			$aEmail = array(
				'email_id'		=> '14',
				'to'			=> $email,
				'vars'			=> $aEmailVars
			);
			include('../../includes/email/tracker-emails.php');*/
			
			echo '
				<p>You have successfully subscribed to the manager. To view the tracking information of this manager, please go to your Action Center.</p>
				
			';
			
		}else{
			echo '
				<div class="note note-danger">
				<h4>Please correct the following errors.</h4>
				<ul>	
			';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';	
			}
			echo '</ul></div>';
				
		}
	
	break;
	
	//+---------------------------------------------------------------------------------------------------------+
	//|										STOP Track Manager								
	//+---------------------------------------------------------------------------------------------------------+
	case 'stop-track-fund':
		
		$fundID		= $_REQUEST['fundID'];
		
		
		
		$aFundID	= explode('-', $fundID);
		
		$memberID	= $aFundID[0];
		
		echo $fundID.' | '.$memberID;
		
		$query = "
			SELECT *
			FROM ".$_SESSION['fund_tracking_table']."
			WHERE member_id=:member_id AND user_id=:user_id
		";
		try{
			$rsGetTrack = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $memberID,
				':user_id'		=> $_SESSION['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetTrack->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$trackInfo = $rsGetTrack->fetch(PDO::FETCH_ASSOC);
		
		$aTrackFunds = explode('|',$trackInfo['track_funds']);
		
		echo $fundID;
		print_r($aTrackFunds);
		
		foreach($aTrackFunds as $key=>$trackFundID){
			
			if($trackFundID == $fundID){
				unset($aTrackFunds[$key]);
			}
				
		}
		
		print_r($aTrackFunds);
		
		if(count($aTrackFunds) == 0){
			
			$query = "
				DELETE 
				FROM ".$_SESSION['fund_tracking_table']."
				WHERE member_id=:member_id AND user_id=:user_id			
			";
				
		}else{
			
			$query = "
				UPDATE ".$_SESSION['fund_tracking_table']."
				SET track_funds=:track_funds
				WHERE member_id=:member_id AND user_id=:user_id
			";
							
		}
		
		try{
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $memberID,
				':user_id'		=> $_SESSION['member_id'],
				':track_funds'	=> implode('|',$aTrackFunds)
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
	break;	
}
