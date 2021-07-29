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

function array_to_csv_download($array, $filename = "export.csv", $delimiter=";") {
    header('Content-Type: application/csv');
    header('Content-Disposition: attachement; filename="'.$filename.'";');

    // open the "output" stream
    // see http://www.php.net/manual/en/wrappers.php.php#refsect2-wrappers.php-unknown-unknown-unknown-descriptioq
    $f = fopen('php://output', 'w');

    foreach ($array as $line) {
        fputcsv($f, $line, $delimiter);
    }
}  

//+---------------------------------------------------------------------------------------------------------+
//|									CSV Download - PROCESS 1												|
//+---------------------------------------------------------------------------------------------------------+

if($process == "1"){
	
	$csvData = $_SESSION['csvData'];
	
	$aCSV = array();
	
	$aCSV[] = array(
		'First Name', 'Last Name', 'Full Name', 'Username', 'Fund Symbol', 'Fund Name', 'Compliant', '% Cash', 'Cash Value', 'NAV', 'Win Loss Ratio', 'Avg Gain Loss', 'Return Last Week', 'Current Return'
	);
	
	foreach($csvData as $studentID=>$aStudent){
		
		if($aStudent['compliant'] == '<span class="label label-success">Yes</span>'){
			$compliant = 'Yes';
		}elseif($aStudent['compliant'] == '<span class="label label-danger">No</span>'){
			$compliant = 'No';
		}else{
			$compliant = $aStudent['compliant'];	
		}
		
		$className = str_replace(' ', '-', $aStudent['className']);
		$professor = str_replace(' ', '-', $aStudent['professor']);
		
		$aCSV[] = array(
			'first_name'		=> $aStudent['firstName'],
			'last_name'			=> $aStudent['lastName'],
			'full_name'			=> $aStudent['fullName'],
			'username'			=> $aStudent['username'],
			'fund_symbol'		=> $aStudent['fundSymbol'],
			'fund_name'			=> $aStudent['fundName'],
			'compliant'			=> $compliant,
			'percentCash'		=> $aStudent['percentCash'],
			'cashValue'			=> $aStudent['cashValue'],
			'nav'				=> $aStudent['nav'],
			'winLossRatio'		=> $aStudent['winLossRatio'],
			'avgGainLoss'		=> $aStudent['avgGainLoss'],
			'returnLastWeek'	=> $aStudent['returnLastWeek'],
			'currentReturn'		=> $aStudent['currentReturn']
		);
			
	}
	


	

    // output as an attachment
    query_to_csv($aCSV, $professor.'_'.$className."_".date('Y.m.d-h.i').".csv", true);
	
	/*echo '<pre>';
	print_r($aCSV);
	echo '</pre>';*/
}

if($process == '2'){
	
	$fundID	= $_REQUEST['fund'];
	
	$aFundID	= explode('-',$fundID);
	$memberID 	= $aFundID[0];
	
	$username 	= get_member($mLink, $memberID, 'username');
	$fundSymbol	= get_funds($mLink, $fundID, 'fundSymbol');
	
	$label = $username.':'.strtoupper($fundSymbol);
	
	echo $label;
	
	$label = '';	
}
?>