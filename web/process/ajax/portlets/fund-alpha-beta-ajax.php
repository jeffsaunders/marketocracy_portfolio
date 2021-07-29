<?php
/*
Marketocracy Inc. | Beta Development
Fund Alpha Beta AJAX Processes

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/portlets/fund-alpha-beta-ajax.php
	JAVASCRIPT	- includes/scripts/portlets/fund-alpha-beta.js.php
	HTML		- includes/portlets/fund-alpha-beta.php
	
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
		SELECT thirtyDayAlphaSkipAAR AS alpha, thirtyDayBetaSkip AS beta, thirtyDayRSquaredSkip AS squared
		FROM '.$_SESSION['fund_alphabeta_table'].'
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
	
	$alpha = ''.round(($fund['alpha']*100), 2).'%';
	
	$beta = round($fund['beta'], 2);
	
	$rSquared = round($fund['squared'],2 );
	
	?>
    <tr>
        <th>Alpha</th>
        <td><?php echo $alpha; ?></td>
    </tr>
    <tr>
        <th>Beta</th>
        <td><?php echo $beta; ?></td>
    </tr>
    <tr>
        <th>R-Squared</th>
        <td><?php echo $rSquared; ?></td>
    </tr>
    <?php
}
