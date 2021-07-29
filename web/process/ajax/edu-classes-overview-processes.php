<?php
/*
Marketocracy Inc. | Beta Development
Fund Trades

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/fund-trades-processes.php
	JAVASCRIPT	- includes/scripts/fund-trades.js.php
	HTML		- includes/pages/fund-trades.php
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



//+---------------------------------------------------------------------------------------------------------+
//|									 - PROCESS 1										|
//+---------------------------------------------------------------------------------------------------------+

if($process == "1"){
	
	
}
?>