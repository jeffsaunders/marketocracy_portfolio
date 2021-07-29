<?php
/*
Marketocracy Inc. | Beta Development
Admin General HTML

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/admin-general-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/admin-general.js.php
	HTML		- includes/pages/admin-general.php
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


//+-------------------------------------------------------------------------------+
//|						Admin Switch
//+-------------------------------------------------------------------------------+
	//echo 'hello';
	//echo $_SESSION['admin'];
	
	function adjustUser($mLink, $memberID){
		//Look up their membership information
		$query = "
			SELECT name_first, name_last, joined_timestamp, last_login, username
			FROM ".$_SESSION['members_table']."
			WHERE member_id = :member_id
			ORDER BY timestamp DESC
			LIMIT 1
		";
		try {
			$rsMember = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $memberID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
			$rsMember->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$member = $rsMember->fetch(PDO::FETCH_ASSOC);

		//Assign more session varibles
		$_SESSION['first_name']			= $member['name_first'];
		$_SESSION['last_name']			= $member['name_last'];
		$_SESSION['joined_timestamp']	= $member['joined_timestamp'];
		$_SESSION['last_login']			= $member['last_login'];
		$_SESSION['username']			= $member['username'];

		//Look up their membership flags
		$query = "
			SELECT *
			FROM ".$_SESSION['members_flags_table']."
			WHERE member_id = :member_id
			ORDER BY uid DESC
			LIMIT 1
		";
		//echo $query;
		try {
			$rsFlags = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $memberID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
			$rsFlags->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$flags = $rsFlags->fetch(PDO::FETCH_ASSOC);

		// Save each flag value to a session variable based on it's column name
		foreach($flags as $key => $value){
			$_SESSION[$key] = $value; // Do this until all older references sans "flag_" are purged
			$_SESSION['flag_'.$key] = $value;
		}
	}
	
	if($_SESSION['admin'] == '1' || isset($_SESSION['admin_id'])){
		
		$adminID 	= $_REQUEST['admin'];
		$memberID	= $_REQUEST['member'];
		$toggle		= $_REQUEST['toggle'];
		$goPage		= $_REQUEST['gopage'];
		$goFund		= $_REQUEST['gofund'];
		$returnPage	= $_REQUEST['returnPage'];
		$cargo		= $_REQUEST['cargo'];
		
		if($goPage != ''){
			$goPage = '?page='.$goPage;	
		}
		
		if($goFund != ''){
			$goFund = '&fund='.$goFund;	
		}
		
		if($returnPage == ''){
			$returnPage = '?page=20-00-00-003&member='.$memberID;
		}else{
			$returnPage = '?page='.$returnPage.'&memberID='.$memberID;	
		}
				
		switch($toggle){
			case 'admin-to-user':
				
				$_SESSION['admin'] 		= 0;
				$_SESSION['member_id'] 	= $memberID;
				$_SESSION['admin_id']	= $adminID;
				$_SESSION['returnPage']	= $returnPage;
				
				$cargo					= str_replace('|','&',$cargo);
				$cargo					= str_replace('~','=',$cargo);
				
				
				adjustUser($mLink, $_SESSION['member_id']);
				
				$_SESSION['goToURL'] = $goPage.$goFund.$cargo;
				
				header('Location: /'.$goPage.$goFund.$cargo);
				
			break;
			
			case 'user-to-admin':
				
				$_SESSION['admin'] 		= 1;
				$_SESSION['member_id'] 	= $adminID;
				unset($_SESSION['admin_override']);
				unset($_SESSION['admin_id']);
				unset($_SESSION['goToURL']);
				$returnPage = $_SESSION['returnPage'];
				
				adjustUser($mLink, $_SESSION['member_id']);
				
				header('Location: /'.$returnPage);
				
			break;	
		}
		
		

	}
?>