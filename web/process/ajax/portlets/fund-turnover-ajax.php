<?php
/*
Marketocracy Inc. | Beta Development
Fund Turnover AJAX Processes

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/portlets/fund-turnover-ajax.php
	JAVASCRIPT	- includes/scripts/portlets/fund-turnover.js.php
	HTML		- includes/portlets/fund-turnover.php
	
*/

session_start();

// Load debug & error logging functions
require_once("../../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../../secure/dbconnect.php");

require_once("../../../includes/system-functions.php");

$process = $_REQUEST['process'];


//+---------------------------------------------------------------------------------------------+
//|									Load Returns | PROCESS 1									|
//+---------------------------------------------------------------------------------------------+
if($process == "1"){
	$fundID = $_REQUEST['fund'];
	
	$query = '
		SELECT turnoverLast30Days AS month, turnoverLast90Days AS 3months, turnoverLast180Days AS 6months, turnoverLastYear AS year
		FROM '.$_SESSION['fund_aggregate_table'].'
		WHERE fund_id=:fund_id
		ORDER BY timestamp DESC
		LIMIT 1
	';
	
	try{
		$rsGetFund = $mLink->prepare($query);
		$aValues = array(
			':fund_id' 	=> $fundID
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetFund->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$fund = $rsGetFund->fetch(PDO::FETCH_ASSOC);
	
	$lastMonth = round($fund['month'], 2);
	if($lastMonth == "0"){
		$lastMonth = "N/A";
	}else{
		$lastMonth = ''.$lastMonth.'%';
	}
	
	$last3Months = round($fund['3months'], 2);
	if($last3Months == "0"){
		$last3Months = "N/A";
	}else{
		$last3Months = ''.$last3Months.'%';
	}
	
	$last6Months = round($fund['6months'], 2);
	if($last6Months == "0"){
		$last6Months = "N/A";
	}else{
		$last6Months = ''.$last6Months.'%';
	}
	
	$lastYear = round($fund['year'], 2);
	if($lastYear == "0"){
		$lastYear = "N/A";	
	}else{
		$lastYear = ''.$lastYear.'%';
	}
	
	?>
    <tr>
        <th>Last Month</th>
        <td><?php echo $lastMonth;?></td>
    </tr>
    <tr>
        <th>Last 3 Months</th>
        <td><?php echo $last3Months;?></td>
    </tr>
    <tr>
        <th>Last 6 Months</th>
        <td><?php echo $last6Months;?></td>
    </tr>
    <tr>
    <th>Last 12 Months</th>
    <td><?php echo $lastYear;?></td>
    </tr>
    <?php
}
