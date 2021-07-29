<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

$portletID = $_REQUEST['portlet_id'];

if(!isset($_SESSION['layout']) or $_SESSION['layout'] == "2" or $_SESSION['layout'] == "4"){
	//Start 2 column
	$query = "
		SELECT dash_col1, dash_col2 
		FROM ".$_SESSION['members_settings_table']."
		WHERE member_id=:member_id 
	";
	
	//Fund Symbols
	try{
		$rsGetColumns = $mLink->prepare($query);
		$aValues = array(
			':member_id' 	=> $_SESSION['member_id']
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetColumns->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$columns = $rsGetColumns->fetch(PDO::FETCH_ASSOC);
	
	$col1 = $columns['dash_col1'];
	$col2 = $columns['dash_col2'];
	
	
	//First Check to See which column the Portlet ID is in
	if(strpos($col1, $portletID) !== false) {
		//Its in COLUMN 1
		$replaceColumn = "dash_col1";
		
		//Check to see if Portlet ID is anywhere but the front
		if(strpos($col1, '|'.$portletID.'') !== false){
			//Remove Portlet ID from string
			$replaceString = str_replace('|'.$portletID.'', '', $col1);
			
		//Check to see if Portlet ID is anywhere but the back
		}elseif(strpos($col1, ''.$portletID.'|') !== false){
			//Remove Poretlet ID from string
			$replaceString = str_replace(''.$portletID.'|', '', $col1);
		}
	}elseif(strpos($col2, $portletID) !== false) {
		//Its in COLUMN 2
		$replaceColumn = "dash_col2";
		//Check to see if Portlet ID is anywhere but the front
		
		if(strpos($col2, '|'.$portletID.'') !== false){
			//Remove Portlet ID from string
			$replaceString = str_replace('|'.$portletID.'', '', $col2);
			
		//Check to see if Portlet ID is anywhere but the back
		}elseif(strpos($col2, ''.$portletID.'|') !== false){
			//Remove Portlet ID from string
			$replaceString = str_replace(''.$portletID.'|', '', $col2);
		}
	}

	//echo $replaceColumn;
	//echo "<br>";
	//echo $replaceString;
	
	
	if (isset($_SESSION['member_id']) == true){
		// log the event
		
		$query = "
			UPDATE ".$_SESSION['members_settings_table']."
			SET	".$replaceColumn."=:replace_string,
				timestamp=UNIX_TIMESTAMP()
			
			WHERE member_id=:member_id
		";
		try {
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':member_id'		=> $_SESSION['member_id'],
				':replace_string'	=> $replaceString
			);
			// Prepared query - for error logging and debugging
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		catch(PDOException $error){
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}
	
}
if($_SESSION['layout'] == "4" or !isset($_SESSION['layout']) or $_SESSION['layout'] == "2"){
	
	//Start 4 column
	$query = "
		SELECT dash_4col1, dash_4col2, dash_4col3, dash_4col4 
		FROM ".$_SESSION['members_settings_table']."
		WHERE member_id=:member_id 
	";
	
	//Fund Symbols
	try{
		$rsGetColumns = $mLink->prepare($query);
		$aValues = array(
			':member_id' 	=> $_SESSION['member_id']
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetColumns->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$columns = $rsGetColumns->fetch(PDO::FETCH_ASSOC);
	
	$col1 = $columns['dash_4col1'];
	$col2 = $columns['dash_4col2'];
	$col3 = $columns['dash_4col3'];
	$col4 = $columns['dash_4col4'];
	
	
	//First Check to See which column the Portlet ID is in
	if(strpos($col1, $portletID) !== false) {
		//Its in COLUMN 1
		$replaceColumn = "dash_4col1";
		
		//Check to see if Portlet ID is anywhere but the front
		if(strpos($col1, '|'.$portletID.'') !== false){
			//Remove Portlet ID from string
			$replaceString = str_replace('|'.$portletID.'', '', $col1);
			
		//Check to see if Portlet ID is anywhere but the back
		}elseif(strpos($col1, ''.$portletID.'|') !== false){
			//Remove Poretlet ID from string
			$replaceString = str_replace(''.$portletID.'|', '', $col1);
		}
	}elseif(strpos($col2, $portletID) !== false) {
		//Its in COLUMN 2
		$replaceColumn = "dash_4col2";
		//Check to see if Portlet ID is anywhere but the front
		
		if(strpos($col2, '|'.$portletID.'') !== false){
			//Remove Portlet ID from string
			$replaceString = str_replace('|'.$portletID.'', '', $col2);
			
		//Check to see if Portlet ID is anywhere but the back
		}elseif(strpos($col2, ''.$portletID.'|') !== false){
			//Remove Portlet ID from string
			$replaceString = str_replace(''.$portletID.'|', '', $col2);
		}
	}elseif(strpos($col3, $portletID) !== false) {
		//Its in COLUMN 3
		$replaceColumn = "dash_4col3";
		//Check to see if Portlet ID is anywhere but the front
		
		if(strpos($col3, '|'.$portletID.'') !== false){
			//Remove Portlet ID from string
			$replaceString = str_replace('|'.$portletID.'', '', $col3);
			
		//Check to see if Portlet ID is anywhere but the back
		}elseif(strpos($col3, ''.$portletID.'|') !== false){
			//Remove Portlet ID from string
			$replaceString = str_replace(''.$portletID.'|', '', $col3);
		}
	}elseif(strpos($col4, $portletID) !== false) {
		//Its in COLUMN 4
		$replaceColumn = "dash_4col4";
		//Check to see if Portlet ID is anywhere but the front
		
		if(strpos($col4, '|'.$portletID.'') !== false){
			//Remove Portlet ID from string
			$replaceString = str_replace('|'.$portletID.'', '', $col4);
			
		//Check to see if Portlet ID is anywhere but the back
		}elseif(strpos($col4, ''.$portletID.'|') !== false){
			//Remove Portlet ID from string
			$replaceString = str_replace(''.$portletID.'|', '', $col4);
		}
	}

	//echo $replaceColumn;
	//echo "<br>";
	//echo $replaceString;
	
	
	if (isset($_SESSION['member_id']) == true){
		// log the event
		
		$query = "
			UPDATE ".$_SESSION['members_settings_table']." 
			SET	".$replaceColumn."=:replace_string,
				timestamp=UNIX_TIMESTAMP()
			
			WHERE member_id=:member_id
		";
		try {
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':member_id'		=> $_SESSION['member_id'],
				':replace_string'	=> $replaceString
			);
			// Prepared query - for error logging and debugging
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		catch(PDOException $error){
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}
}


?>