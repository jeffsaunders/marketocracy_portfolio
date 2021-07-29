<?php
/*
Marketocracy Inc. | Beta Development
Fund Ledger Scripts

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/fund-ledger-processes.php
	JAVASCRIPT	- JAVASCRIPT	- THIS DOCUMENT includes/scripts/fund-ledger.js.php
	Javascript  - js/fund-ledger.js <- table scripts
	HTML		- includes/pages/fund-ledger.php
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
//|									CSV Download - PROCESS csv												|
//+---------------------------------------------------------------------------------------------------------+

if($process == "csv"){
	
	$aCSV = $_SESSION['csvData'];
	
    // output as an attachment
    query_to_csv($aCSV, $professor.'_'.$className."_".date('Y.m.d-h.i').".csv", true);
	
	/*echo '<pre>';
	print_r($aCSV);
	echo '</pre>';*/
}
