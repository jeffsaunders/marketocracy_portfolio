<?php
/*
Marketocracy Inc. 
Admin Rankings Processing

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/admin-rankings-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/admin-rankings.js.php
	HTML		- includes/pages/admin-rankings.php
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

switch($process){
	
	
	case 'set-session':
	
		$sessionVar	= $_REQUEST['sessionVar'];
		$sessionVal	= $_REQUEST['sessionVal'];
		
		if($_SESSION['admin'] == '1'){
			$_SESSION[$sessionVar] = $sessionVal;
		}
	
	break;
		
}