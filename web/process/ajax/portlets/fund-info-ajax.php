<?php
/*
Marketocracy Inc. | Beta Development
Fund Info Scripts

Supporting files:
	AJAX		- process/ajax/portlets/fund-info-ajax.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/portlets/fund-info.js.php
	HTML		- includes/portlets/fund-info.php
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
		SELECT mf.*, lp.*
		FROM `members_fund` AS mf
		INNER JOIN members_fund_liveprice AS lp ON mf.fund_id=lp.fund_id
		WHERE mf.fund_id=:fund_id
	';
	
	try{
		$rsGetFund = $mLink->prepare($query);
		$aValues = array(
			':fund_id' 	=> $$fundInfoFundID
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetFund->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$fund = $rsGetFund->fetch(PDO::FETCH_ASSOC);
	
	$totalValue = number_format($fund['totalValue'], 2, '.', ',');
	$inception = date('F d, Y', $fund['unix_date']);
	
	$query = '
		SELECT * 
		FROM `members_fund_details` 
		WHERE fund_id=:fund_id AND date=(SELECT MAX(date) FROM members_fund_details WHERE fund_id=:fund_id)
		GROUP BY stockSymbol
	';
	
	try{
		$rsGetPos = $mLink->prepare($query);
		$aValues = array(
			':fund_id' 	=> $$fundInfoFundID
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetPos->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$securities = $rsGetPos->rowCount();
	
	?> 
	<table class="table table-striped table-bordered table-hover">
	   <thead>
		  <tr>
			 <th>Fund Manager:</th>
			 <th>Total Net Assets:</th>
		  </tr>
	   </thead>
	   <tbody>
		  <tr>
			 <td class="success"><?php echo $_SESSION['first_name']; ?> <?php echo $_SESSION['last_name'];?></td>
			 <td class="success">$<?php echo $totalValue;?></td>
		  </tr>
	   </tbody>
	</table>
	
	<table class="table table-striped table-bordered table-hover">
		<thead>
		  <tr>
			 <th>Inception:</th>
			 <th>Ticker Symbol:</th>
			 <th># of Securities:</th>
		  </tr>
	   </thead>
	   <tbody>
		  <tr>
			 <td><?php echo $inception;?></td>
			 <td><?php echo $$fundInfoFundSym; ?></td>
			 <td><?php echo $securities;?></td>
		  </tr>
	   </tbody>
	</table>
	
	<table class="table table-bordered">
	   <thead>
		  <tr>
			 <th>Description:</th>
		  </tr>
	   </thead>
	   <tbody>
		  <tr>
			 <td><p><?php echo $fund['description'];?></p></td>
		  </tr>
	   </tbody>
	</table>
    <?php
}
