<?php
//Add new portlet process

session_start();

// Load debug & error logging functions
require_once("../includes/system-debug-functions.php");

//Connect to DB
require_once("../../secure/dbconnect.php");

require_once("../includes/system-functions.php");

$process = $_REQUEST['process'];

if($process == "1"){

	$portletID = $_REQUEST['portlet_id'];
	
	//echo $portletID;
	
	//Get Funds
	$query = '
		SELECT fund_id
		FROM '.$_SESSION['fund_table'].'
		ORDER BY fund_id ASC
	';
	
	try{
		$rsFunds = $mLink->prepare($query);
		$aValues = array(
			//':fund_id' 	=> $$profitFundID
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsFunds->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aFunds = array();
	
	while($fund = $rsFunds->fetch(PDO::FETCH_ASSOC)){
		//echo $fund['fund_id'];
		$aFunds[] = $fund['fund_id'];
	
	}
	
	$counter = 0;
	$aProcesses = array();
	//Get Fund Settings
	foreach($aFunds as $fundID){
		
		$counter = $counter + 1;
		
		$query = '
			SELECT overview_col1, overview_col2
			FROM '.$_SESSION['fund_settings_table'].'
			WHERE fund_id=:fund_id
		';
		
		try{
			$rsFundSettings = $mLink->prepare($query);
			$aValues = array(
				':fund_id' 	=> $fundID
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsFundSettings->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$settings = $rsFundSettings->fetch(PDO::FETCH_ASSOC);
		
		$col1 = explode('|', $settings['overview_col1']);
		$col2 = explode('|', $settings['overview_col2']);
		
		
		
		//print_r($col1);
		
		
		
		if(in_array($portletID, $col1)){
			//is in column 1
			$aProcesses[] = ''.$counter.'). Portlet found in column 1 for fund: '.$fundID.'. Portlet not added.';	
		}elseif(in_array($portletID, $col2)){
			//is in column 2
			$aProcesses[] = ''.$counter.'). Portlet found in column 2 for fund: '.$fundID.'. Portlet not added.';		
		}else{
			
			// not in either column
			array_filter($col1);
			array_filter($col2);
			
			
			/*print_r($col1);
			echo '<br />';
			print_r($col2);
			echo '<br /><br />';*/
			
			//print_r($col1);
			
			$col1Cnt = count($col1);
			$col2Cnt = count($col2);
			
			if($col1Cnt < $col2Cnt){
				//array_push($col1, $portletID);
				array_unshift($col1, $portletID);
			}else{
				//array_push($col2, $portletID);
				array_unshift($col2, $portletID);
			}
			
			$newCol1 = implode('|', $col1);
			$newCol2 = implode('|', $col2);
			
			
			/*echo $newCol1;
			echo '<br />';
			echo $newCol2;
			echo '<br />';*/
			
			$query = '
				UPDATE '.$_SESSION['fund_settings_table'].'
				SET overview_col1=:col1, overview_col2=:col2, timestamp=UNIX_TIMESTAMP()
				WHERE fund_id=:fund_id
			';
			
			try{
				$rsFundSettings = $mLink->prepare($query);
				$aValues = array(
					':fund_id' 	=> $fundID,
					':col1'		=> $newCol1,
					':col2'		=> $newCol2
					
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsFundSettings->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			if(empty($error)){
				$aProcesses[] = ''.$counter.'). Portlet NOT found for fund: '.$fundID.'. Portlet added successfully.<br>Column 1: '.$newCol1.'<br>Column 2: '.$newCol2.'';	
			}else{
				$aProcesses[] = $error;	
			}
			
		}
	}
	
	echo '<br /><br />Portlet ID: '.$portletID.'<br /><br />';
	echo '<h2>Results</h2>';
	echo '<ul>';
	
	foreach($aProcesses as $result){
		echo '<li>'.$result.'</li>';	
	}
	
	echo '</ul>';
	
	
	/*echo '<pre>';
	print_r($aProcesses);
	echo '</pre>';*/
		
}

if($process == "2"){

	$portletID = $_REQUEST['portlet_id'];
	
	//echo $portletID;
	
	//Get Funds
	$query = '
		SELECT member_id
		FROM '.$_SESSION['members_table'].'
		ORDER BY member_id ASC
	';
	
	try{
		$rsMembers = $mLink->prepare($query);
		$aValues = array(
			//':fund_id' 	=> $$profitFundID
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsMembers->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aMembers = array();
	
	while($member = $rsMembers->fetch(PDO::FETCH_ASSOC)){
		//echo $fund['fund_id'];
		$aMembers[] = $member['member_id'];
	
	}
	
	$counter = 0;
	$aProcesses = array();
	//Get Fund Settings
	foreach($aMembers as $memberID){
		
		$counter = $counter + 1;
		
		$query = '
			SELECT dash_col1, dash_col2, dash_4col1
			FROM '.$_SESSION['members_settings_table'].'
			WHERE member_id=:member_id
		';
		
		try{
			$rsFundSettings = $mLink->prepare($query);
			$aValues = array(
				':member_id' 	=> $memberID
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsFundSettings->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$settings = $rsFundSettings->fetch(PDO::FETCH_ASSOC);
		
		$col1 		= explode('|', $settings['dash_col1']);
		$col2 		= explode('|', $settings['dash_col2']);
		$dash4col1 	= explode('|', $settings['dash_4col1']);
		
		
		
		//print_r($col1);
		
		
		
		if(in_array($portletID, $col1)){
			//is in column 1
			$aProcesses[] = ''.$counter.'). Portlet found in column 1 for member: '.$memberID.'. Portlet not added.';	
		}elseif(in_array($portletID, $col2)){
			//is in column 2
			$aProcesses[] = ''.$counter.'). Portlet found in column 2 for member: '.$memberID.'. Portlet not added.';		
		}else{
			
			// not in either column
			array_filter($col1);
			array_filter($col2);
			
			
			/*print_r($col1);
			echo '<br />';
			print_r($col2);
			echo '<br /><br />';*/
			
			//print_r($col1);
			
			$col1Cnt = count($col1);
			$col2Cnt = count($col2);
			
			if($col1Cnt < $col2Cnt){
				//array_push($col1, $portletID);
				array_unshift($col1, $portletID);
				array_unshift($dash4col1, $portletID);
			}else{
				//array_push($col2, $portletID);
				array_unshift($col2, $portletID);
				array_unshift($dash4col1, $portletID);
			}
			
			$newCol1 = implode('|', $col1);
			$newCol2 = implode('|', $col2);
			$newDash4Col1 = implode('|', $dash4col1);
			
			
			/*echo $newCol1;
			echo '<br />';
			echo $newCol2;
			echo '<br />';*/
			
			$query = '
				UPDATE '.$_SESSION['members_settings_table'].'
				SET dash_col1=:col1, dash_col2=:col2, dash_4col1=:dash4col1, timestamp=UNIX_TIMESTAMP()
				WHERE member_id=:member_id
			';
			
			try{
				$rsMemberSettings = $mLink->prepare($query);
				$aValues = array(
					':member_id' 		=> $memberID,
					':col1'			=> $newCol1,
					':col2'			=> $newCol2,
					':dash4col1'	=> $newDash4Col1
					
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsMemberSettings->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			if(empty($error)){
				$aProcesses[] = ''.$counter.'). Portlet NOT found for member: '.$memberID.'. Portlet added successfully.<br>Column 1: '.$newCol1.'<br>Column 2: '.$newCol2.'<br>4 Column 1:'.$newDash4Col1.'';	
			}else{
				$aProcesses[] = $error;	
			}
			
		}
	}
	
	echo '<br /><br />Portlet ID: '.$portletID.'<br /><br />';
	echo '<h2>Results</h2>';
	echo '<ul>';
	
	foreach($aProcesses as $result){
		echo '<li>'.$result.'</li>';	
	}
	
	echo '</ul>';
	
	
	/*echo '<pre>';
	print_r($aProcesses);
	echo '</pre>';*/
		
}

?>