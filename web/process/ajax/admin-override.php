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

if($_SESSION['admin'] == '1' || isset($_SESSION['admin_id'])){

	switch($process){
		
		case 'override':
		
			$_SESSION['admin_override'] = $_SESSION['admin_id'];
			
			$goToURL = $_SESSION['goToURL'];
			
			//echo $goToURL;
			
			header('Location: /'.$goToURL);
		
		break;
		
		case 'rtn-user-mode':
			
			unset($_SESSION['admin_override']);
			header('Location: /');
		
		break;
			
	}
	
}else{
	//echo 'failed auth';
	header('Location: /');	
}

?>