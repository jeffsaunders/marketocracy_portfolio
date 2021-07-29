<?php

//Start Session
session_start();

// Load debug & error logging functions
//require_once("../system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

// Load System Wide Functions
require_once("../system-functions.php");



$track		= $_REQUEST['track'];
$aTrackID	= explode('~',$track);

$trackType	= $aTrackID[0];
$trackID	= $aTrackID[1];

$ipAddress	= get_ip();


switch($trackType){
	
	case 'open':
		$query = "
			UPDATE email_tracking
			SET opened=UNIX_TIMESTAMP(), opened_ip=:ip
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
		
		$file = '../../images/1x1.png';
		$type = 'image/png';
		header('Content-Type:'.$type);
		header('Content-Length: ' . filesize($file));
		readfile($file);
		
	break;
	
	case 'auto-open':
		$query = "
			UPDATE email_auto_tracking
			SET opened=UNIX_TIMESTAMP(), opened_ip=:ip
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
		
		$file = '../../images/1x1.png';
		$type = 'image/png';
		header('Content-Type:'.$type);
		header('Content-Length: ' . filesize($file));
		readfile($file);
		
	break;
	
	case 'clicked':
		
		$query = "
			UPDATE email_tracking
			SET clicked=UNIX_TIMESTAMP(), clicked_ip=:ip
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
		//echo $error;
		header('Location: '.$aTrackID[2]);
	break;
	
	case 'auto-clicked':
		
		$query = "
			UPDATE email_auto_tracking
			SET clicked='1', clicked_ip=:ip
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
		//echo $error;
		header('Location: '.$aTrackID[2]);
	break;
	
	case 'subscription':
		
		$query = "
			UPDATE email_tracking
			SET clicked=UNIX_TIMESTAMP(), clicked_ip=:ip
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
		//echo $error;
		header('Location: https://'.$aTrackID[2].'.mytrackrecord.com/?page=19-00-00-001&code='.$aTrackID[3].'&email='.$aTrackID[4]);
	break;
	
	
	default:
	
	
	break;	
	
	
}
?>