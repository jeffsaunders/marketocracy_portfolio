<?php
/*
Marketocracy Inc. | Beta Development
Admin Member Reprice Processess

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/admin-member-reprice-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/admin-member-reprice.js.php
	HTML		- includes/pages/admin-member-reprice.php
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


set_time_limit(3600);

if($process == "get-field"){
	
	
}
?>