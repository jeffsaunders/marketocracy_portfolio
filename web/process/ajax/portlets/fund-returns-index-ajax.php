<?php
/*
Marketocracy Inc. | Beta Development
Fund Recent Returns vs Major Indexes AJAX Processes

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/portlets/fund-returns-index-ajax.php
	JAVASCRIPT	- includes/scripts/portlets/fund-returns-index.js.php
	HTML		- includes/portlets/fund-returns-index.php
	
*/

session_start();

// Load debug & error logging functions
require_once("../../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../../secure/dbconnect.php");

require_once("../../../includes/system-functions.php");

$process = $_REQUEST['process'];


//+---------------------------------------------------------------------------------------------+
//|								GET MONTH TO DATE | PROCESS 1									|
//+---------------------------------------------------------------------------------------------+
if($process == "1-stop"){
	
	$fundID = $_REQUEST['fund'];
	
	//Get the date range to determine MTD
	$lastDay =  date('Y-m-d', strtotime('last day of previous month'));
	
	$yesterday =  date('Y-m-d', strtotime('yesterday'));
	$queryDate =  date('Ymd', strtotime('yesterday'));
		
	//Build array of date range
	$dateRange = (createDateRangeArray($lastDay,$yesterday));
	
	print_r($dateRange);
	
	//Get variables for priceRun Query	
	$username 	= $_SESSION['username'];
	$fundSymbol = get_funds($mLink, $fundID, 'fundSymbol');
		
	//Start a counter
	$cnt = 0;
	
	//CHECK TO SEE IF MTD EXISTS OR TODAY EXISTS
	$query = '
		SELECT returnMTD, returnToday
		FROM '.$_SESSION['fund_aggregate_table'].'
		WHERE fund_id=:fund_id and asOfDate=:date
		ORDER BY timestamp DESC
		LIMIT 1
	';
	
	try{
		$rsRow = $mLink->prepare($query);
		$aValues = array(
			':fund_id' 	=> $fundID,
			':date'		=> $queryDate
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsRow->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$agg = $rsRow->fetch(PDO::FETCH_ASSOC);
		
	$returnToday = $agg['returnToday'];
	$returnMTD = $agg['returnMTD'];	
	
	if($returnMTD == '0.000000000000' || $returnMTD == '-100'){
	
		//Loop through date array and check to see if price data is avaliable for that range
		foreach($dateRange as $date){
			$query = '
				SELECT date
				FROM '.$_SESSION['fund_pricing_table'].'
				WHERE fund_id=:fund_id and date=:date
				ORDER BY timestamp DESC
				LIMIT 1
			';
			
			try{
				$rsRow = $mLink->prepare($query);
				$aValues = array(
					':fund_id' 	=> $fundID,
					':date'		=> $date
					
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsRow->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			//Get row count
			$rowCnt = $rsRow->rowCount();					
			
			//If row count is zero, query external db 
			if($rowCnt == "0"){
				
				$cnt = $cnt + 1;
				
				//Set a start date if one has not yet been set
				if(isset($startDate)){
					$endDate = $date;	
				}else{
					$startDate = $date;	
				}
	
			}
			
			//Set a split date if one has not yet been set
			if(!isset($splitDate)){
				if($cnt > 20){
					$splitDate = $date;
				}
			}
		}
		
		
		$startCnt = date("z", mktime(0, 0, 0, substr($startDate, 4, 2), substr($startDate, 6, 2), substr($startDate, 0, 4)));
		$endCnt = date("z", mktime(0, 0, 0, substr($endDate, 4, 2), substr($endDate, 6, 2), substr($endDate, 0, 4)));
		
		$numDays =  $endCnt - $startCnt + 1;
		
		//echo $cnt;echo '|';
		if($cnt != "0"){
			if($numDays > 20){
				
				if($cnt == "1"){
					/*$query = 'priceRun|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$startDate.'/r/n';
					
					//echo $query;
					
					include ('../../../includes/data-query-legacy.php');*/
					
					$aMethodVars[] = array(
						'method'		=> 'priceRun',
						'source'		=> 'Portlet Scripts | process/ajax/portlets/'.__FILE__.' | process: 1-stop | Line: '.__LINE__,
						'api'			=> '2',
						'username'		=> $username,
						'fund_id'		=> $fundID,
						'fund_symbol'	=> $fundSymbol,
						'start_date'	=> $startDate,
						'end_date'		=> $startDate,
					);
				}else{
				
					/*$query = 'priceRun|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$startDate.'|'.$splitDate.'/r/n';
					
					//echo $query;
					
					include ('../../../includes/data-query-legacy.php');*/
					
					$aMethodVars[] = array(
						'method'		=> 'priceRun',
						'source'		=> 'Portlet Scripts | process/ajax/portlets/'.__FILE__.' | process: 1-stop | Line: '.__LINE__,
						'api'			=> '2',
						'username'		=> $username,
						'fund_id'		=> $fundID,
						'fund_symbol'	=> $fundSymbol,
						'start_date'	=> $startDate,
						'end_date'		=> $splitDate,
					);
					
					/*$query = 'priceRun|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$splitDate.'|'.$endDate.'/r/n';
					
					include ('../../../includes/data-query-legacy.php');*/
					
					$aMethodVars[] = array(
						'method'		=> 'priceRun',
						'source'		=> 'Portlet Scripts | process/ajax/portlets/'.__FILE__.' | process: 1-stop | Line: '.__LINE__,
						'api'			=> '2',
						'username'		=> $username,
						'fund_id'		=> $fundID,
						'fund_symbol'	=> $fundSymbol,
						'start_date'	=> $splitDate,
						'end_date'		=> $endDate,
					);
				}
				
			}else{
				/*$query = 'priceRun|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$startDate.'|'.$endDate.'/r/n';
				
				include ('../../../includes/data-query-legacy.php');*/
				
				$aMethodVars[] = array(
					'method'		=> 'priceRun',
					'source'		=> 'Portlet Scripts | process/ajax/portlets/'.__FILE__.' | process: 1-stop | Line: '.__LINE__,
					'api'			=> '2',
					'username'		=> $username,
					'fund_id'		=> $fundID,
					'fund_symbol'	=> $fundSymbol,
					'start_date'	=> $startDate,
					'end_date'		=> $endDate,
				);
					
			}
			
			$mlaResults = legacy_api($mLink, $aMethodVars, true);
		}
		//echo $query;
		
		//print_r($dateRange);
		
		//Start Calculating MTD
		
		//Create array variable
		$aCalc[] = array();
		
		//loop through date range array and add db values to new array
		foreach($dateRange as $date){
			//Query Database to get total value
			$query = '
				SELECT totalValue
				FROM '.$_SESSION['fund_pricing_table'].'
				WHERE fund_id=:fund_id AND date=:date
				ORDER BY timestamp DESC
				LIMIT 1
			';
			
			try{
				$rsGetValue = $mLink->prepare($query);
				$aValues = array(
					':fund_id' 	=> $fundID,
					':date'		=> $date
					
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetValue->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$value = $rsGetValue->fetch(PDO::FETCH_ASSOC);
			
			$aCalc[] = round($value['totalValue'], 2);
		}
		
		//remove the first row
		unset($aCalc[0]);
		
		//print_r($aCalc);
		
		$newArray = Array();
		foreach($aCalc as $key=>$value){
			
			$newArray[] = round(($aCalc[$key] - $aCalc[$key -1])/$aCalc[$key - 1], 9) + 1;
		}
		
		unset($newArray[0]);
		//print_r($newArray);
		
		$MTD = ((array_product($newArray)) - 1)*100;
		
		if($MTD != "0"){
			$yesterday =  date('Ymd', strtotime('yesterday'));
			
			//echo ''.$fundID.'|'.$yesterday.'|'.$MTD.'|'.$_SESSION['fund_aggregate_table'].'';
			
			$query = "
				UPDATE ".$_SESSION['fund_aggregate_table']."
				SET returnMTD=:mtd
				WHERE fund_id=:fund_id AND asOfDate=:date
			";
			
			try{
				$rsUpdateAgg = $mLink->prepare($query);
				$aValues = array(
					':fund_id' 	=> $fundID,
					':date'		=> $yesterday,
					':mtd'		=> $MTD
					
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdateAgg->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
		}
	}//END IF RETURN MTD IS EQUAL TO NOTHING
	
	//if($returnToday == '0.000000000000' || $returnToday == NULL){
		
		$yesterdayDate =  date('Ymd', strtotime('yesterday'));
		$query = '
			SELECT lp.totalValue as today, fp.totalValue as yesterday
			FROM '.$_SESSION['fund_liveprice_table'].' AS lp 
			INNER JOIN '.$_SESSION['fund_pricing_table'].' AS fp ON lp.fund_id=fp.fund_id AND fp.date=:date
			WHERE lp.fund_id=:fund_id
		';
		try{
			$rsGetToday = $mLink->prepare($query);
			$aValues = array(
				':fund_id' 	=> $fundID,
				':date'		=> $yesterdayDate
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetToday->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$price = $rsGetToday->fetch(PDO::FETCH_ASSOC);
		
		$today = $price['today'];
		$yesterday = $price['yesterday'];
		
		$returnToday = ((($today - $yesterday)/$yesterday)*100);
		
		echo $returnToday;
		
		if($retrunToday != '-1'){
			$query = "
				UPDATE members_fund_aggregate
				SET returnToday=:today
				WHERE fund_id=:fund_id AND asOfDate=:date
			";
			
			//$query = "UPDATE members_fund_aggregate SET returnToday='878' WHERE fund_id=:fund_id AND asOfDate=:date";
			
			try{
				$rsUpdateAgg = $mLink->prepare($query);
				$aValues = array(
					':fund_id' 	=> $fundID,
					':date'		=> $yesterdayDate,
					':today'	=> $returnToday
					
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdateAgg->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			echo $error;
		}
	//}
}

if($process == "2-stop"){
	
	$fundID = $_REQUEST['fund'];
	
	$query = '
		SELECT returnCurrentQuarter AS QTD, returnPrevious2Quarters AS YTD, returnMTD AS MTD, returnToday AS today
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
	?>
	<tr>
		<td class="success" style="border-left:5px solid #57b5e3;"><strong><?php echo get_funds($mLink, $fundID, 'fundSymbol');?></strong></td>
		<td class="success"></td>
		<td class="success"><?php echo round($fund['today'], 2);?>%</td>
		<td class="success"><?php echo round($fund['MTD'], 2);?>%</td>
		<td class="success"><?php echo round($fund['QTD'], 2);?>%</td>
		<td class="success"><?php echo round($fund['YTD'], 2);?>%</td>
	</tr>	
    <?php
}