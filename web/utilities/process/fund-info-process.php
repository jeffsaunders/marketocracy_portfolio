<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

require_once("../../includes/system-functions.php");

$process = $_REQUEST['process'];


if($process == "1"){
	
	$memberID = $_REQUEST['member'];
	
	if($memberID != ''){
		$whereClause = "WHERE mp.member_id='".$memberID."'";	
	}else{
		$whereClause = '';		
	}
	
	$query = "
		SELECT  mp.profile, mp.member_id
		FROM temp_member_profile AS mp
		".$whereClause."
	";
	
	try{
		$rsGetOldProfile = $mLink->prepare($query);
		$aValues = array(
		);
			//':asOfDate' 	=> $yesterday
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetOldProfile->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aProfile = array();
	
	while($profile = $rsGetOldProfile->fetch(PDO::FETCH_ASSOC)){
		
		$profileInfo 	= $profile['profile'];
		$memberID		= $profile['member_id'];
		
		//remove the brackets
		$profileInfo = trim(str_replace(array('{', '}'),array('',''), $profileInfo));
		
		//explode the deferent items
		$aItems = explode(';', $profileInfo);
		
		
		//loop through items and remove white space, if value is empty unset it from the array
		foreach($aItems as $key=>$value){
			
			$aItems[$key] = trim($value);
			
			//unset empties
			if($aItems[$key] == '' || $aItems == ' '){
				unset($aItems[$key]);	
			}
			
			//split the question and answer up
			if($aItems[$key] != ''){
				$aQA = explode('=', $aItems[$key]);
				
				$aItems[trim($aQA[0])] = str_replace(array('"', '\n'), array('', '<br />'), trim($aQA[1]));
				
				unset($aItems[$key]);
			}
		}
		
		//$aItems['joined'] = date('Y', $profile['joined_timestamp']);
		$aProfile[$memberID] = $aItems;
		
	}
	
	//clear out the empty array elements
	foreach($aProfile as $key=>$value){
		if(empty($aProfile[$key])){
			unset($aProfile[$key]);	
		}
	}
	
	foreach($aProfile as $memberID=>$aQAs){
		
		foreach($aQAs as $question=>$answer){
			
			$mainQuery = "
				INSERT INTO ".$_SESSION['profile_answers_table']." (
					qid,
					member_id,
					answer,
					timestamp
				) VALUES (
					:qid,
					:member_id,
					:answer,
					UNIX_TIMESTAMP()
				)
			";
			
			switch($question){
				//Whats your age
				case 'age':
					
					$yearOfBirth = (date('Y') - $answer).'-01-01';
					
					$aProfile[$memberID][$question] = $yearOfBirth;
					
					//update DOB
					$query = "
						UPDATE ".$_SESSION['members_profile_table']."
						SET DOB=:dob
						WHERE member_id=:member_id
					";
					try{
						$rsUpdate = $mLink->prepare($query);
						$aValues = array(
							':dob' 			=> $yearOfBirth,
							':member_id'	=> $memberID
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsUpdate->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
				break;
				
				//Interests
				case 'hobbies':
					
					$query = "
						UPDATE ".$_SESSION['members_profile_table']."
						SET interests=:interests
						WHERE member_id=:member_id
					";
					try{
						$rsUpdate = $mLink->prepare($query);
						$aValues = array(
							':interests'	=> str_replace(',', '|', $answer),
							':member_id'	=> $memberID
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsUpdate->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
				break;
				
				//Occupation
				case 'occupation':
					
					$query = "
						UPDATE ".$_SESSION['members_profile_table']."
						SET occupation=:occupation
						WHERE member_id=:member_id
					";
					try{
						$rsUpdate = $mLink->prepare($query);
						$aValues = array(
							':occupation'	=> $answer,
							':member_id'	=> $memberID
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsUpdate->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
				break;
				
				//What is your investing strategy?
				case 'strategy':
					
					try{
						$rsUpdate = $mLink->prepare($mainQuery);
						$aValues = array(
							':qid'			=> '1',
							':member_id'	=> $memberID,
							':answer'		=> $answer
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsUpdate->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
				break;
				
				//Do you have expertise, from your job or personal experience?
				case 'expertise':
					
					try{
						$rsUpdate = $mLink->prepare($mainQuery);
						$aValues = array(
							':qid'			=> '2',
							':member_id'	=> $memberID,
							':answer'		=> $answer
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsUpdate->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
				break;
				
				//How long have you been an investor?
				case 'howLong':
					
					try{
						$rsUpdate = $mLink->prepare($mainQuery);
						$aValues = array(
							':qid'			=> '3',
							':member_id'	=> $memberID,
							':answer'		=> $answer
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsUpdate->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
				break;	
				
				//What attracted youto Marketocracy?
				case 'whyMarketocracy':
					
					try{
						$rsUpdate = $mLink->prepare($mainQuery);
						$aValues = array(
							':qid'			=> '4',
							':member_id'	=> $memberID,
							':answer'		=> $answer
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsUpdate->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
				break;
				
				//Do you want to become a fund manager?
				case 'fundManager':
					
					switch($answer){
						case 'NO': 		$answer = '0';break;
						case 'MAYBE': 	$answer = '2';break;
						case 'YES':		$answer = '1';break;	
					}
					
					try{
						$rsUpdate = $mLink->prepare($mainQuery);
						$aValues = array(
							':qid'			=> '5',
							':member_id'	=> $memberID,
							':answer'		=> $answer
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsUpdate->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
				break;
				
				//How do you select new companies for your funds?
				case 'howSelect':
					
					try{
						$rsUpdate = $mLink->prepare($mainQuery);
						$aValues = array(
							':qid'			=> '6',
							':member_id'	=> $memberID,
							':answer'		=> $answer
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsUpdate->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
				break;
				
				//How do you decide to remvoe a stock from your fund?
				case 'howRemove':
					
					try{
						$rsUpdate = $mLink->prepare($mainQuery);
						$aValues = array(
							':qid'			=> '7',
							':member_id'	=> $memberID,
							':answer'		=> $answer
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
				
		}
		
	}
	
	echo '<pre>';
	print_r($aProfile);
	echo '</pre>';
	
}

?>