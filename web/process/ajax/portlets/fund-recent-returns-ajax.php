<?php
/*
Marketocracy Inc. | Beta Development
Fund Recent Returns AJAX Processes

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/portlets/fund-recent-returns-ajax.php
	JAVASCRIPT	- includes/scripts/portlets/fund-recent-returns.js.php
	HTML		- includes/portlets/fund-recent-returns.php
	
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
switch($process){
	
	case 'load-returns':
		
		$fundID = $_REQUEST['fund'];
		
		$query = "
			SELECT unix_date, price, date
			FROM ".$_SESSION['fund_pricing_table']."
			WHERE fund_id=:fund_id
			ORDER BY unix_date DESC
			LIMIT 1
		";
		try{
			$rsGetStartDate = $mLink->prepare($query);
			$aValues = array(
				':fund_id' 	=> $fundID
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetStartDate->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$fundPrice			= $rsGetStartDate->fetch(PDO::FETCH_ASSOC);
		
		$currentPrice		= $fundPrice['price'];
		$getDate 			= $fundPrice['date'];
		
		//$lastDay 			= mktime(11, 0, 0, substr($getDate, 4, 2), substr($getDate, 6, 2), substr($getDate, 0, 4));
		$lastDay			= $fundPrice['unix_date'];
		$lastWeek			= checkForMarketDate(strtotime('-7 day', $lastDay), $mLink);
		$lastMonth			= checkForMarketDate(strtotime('-30 day', $lastDay), $mLink);
		$lastThreeMonths	= checkForMarketDate(strtotime('-90 day', $lastDay), $mLink);
		$lastSixMonths		= checkForMarketDate(strtotime('-180 day', $lastDay), $mLink);
		$lastYear			= checkForMarketDate(strtotime('-365 day', $lastDay), $mLink);
		$lastTwoYears		= checkForMarketDate(strtotime('-730 day', $lastDay), $mLink);
		$lastThreeYears		= checkForMarketDate(strtotime('-1095 day', $lastDay), $mLink);
		$lastFiveYears		= checkForMarketDate(strtotime('-1825 day', $lastDay), $mLink);
		$lastTenYears		= checkForMarketDate(strtotime('-3650 day', $lastDay), $mLink);
		
		$AAR				= get_fund_AAR($mLink, $fundID);
		$spAAR				= get_fund_AAR($mLink, $fundID, 'SP');
		
		$inceptDate			= date('Ymd',get_funds($mLink, $fundID, 'trueInceptDate'));
		$unixInceptDate		= mktime(11, 0, 0, substr($inceptDate, 4, 2), substr($inceptDate, 6, 2), substr($inceptDate, 0, 4));
		$inception			= checkForMarketDate($unixInceptDate, $mLink, true);
		
		
		//echo date('Ymd',$inception);
		$fundYears			= (((($lastDay - $inception) / 60) / 60) / 24) / 365;
		$aDatePrices		= array(
			'lastDay'			=> array('date'=> date('Ymd', $lastDay), 'unix'=>$lastDay, 'currentPrice'=> $currentPrice, 'price'=>$currentPrice, 'fundLength'=>$fundYears),
			'Last 7 Days'		=> array('date'=> date('Ymd', $lastWeek), 'unix'=>$lastWeek, 'currentPrice'=> $currentPrice, 'price'=>''),
			'Last 30 Days'		=> array('date'=> date('Ymd', $lastMonth), 'unix'=>$lastMonth,'currentPrice'=> $currentPrice, 'price'=>''),
			'Last 3 Months'		=> array('date'=> date('Ymd', $lastThreeMonths), 'unix'=>$lastThreeMonths, 'currentPrice'=> $currentPrice, 'price'=>''),
			'Last 6 Months'		=> array('date'=> date('Ymd', $lastSixMonths), 'unix'=>$lastSixMonths, 'currentPrice'=> $currentPrice, 'price'=>'', 'unix'=>$lastSixMonths),
			'Last 12 Months'	=> array('date'=> date('Ymd', $lastYear), 'unix'=>$lastYear, 'currentPrice'=> $currentPrice, 'price'=>''),
			'Last 2 Years'		=> array('date'=> date('Ymd', $lastTwoYears), 'unix'=>$lastTwoYears, 'currentPrice'=> $currentPrice, 'price'=>''),
			'Last 3 Years'		=> array('date'=> date('Ymd', $lastThreeYears), 'unix'=>$lastThreeYears, 'currentPrice'=> $currentPrice, 'price'=>''),
			'Last 5 Years'		=> array('date'=> date('Ymd', $lastFiveYears), 'unix'=>$lastFiveYears, 'currentPrice'=> $currentPrice, 'price'=>''),
			'Last 10 Years'		=> array('date'=> date('Ymd', $lastTenYears), 'unix'=>$lastTenYears, 'currentPrice'=> $currentPrice, 'price'=>''),
			'Since Inception'	=> array('date'=> date('Ymd', $inception), 'unix'=>$inception, 'currentPrice'=> $currentPrice, 'price'=>''),
			'(Annualized)'		=> array('date'=> '', 'price'=>'','currentPrice'=> $currentPrice)
		);			
		
		/*if($_REQUEST['debug'] == '1'){
		echo '<pre>';
		print_r(isMarketHoliday('1401116400', $mLink, true, true));
		if(isMarketOpen('1401116400', $mLink) == true){
			echo 'true';
		}else{
			echo 'false';
		}
		
		if(isWeekend('1401116400') == false){
			echo 'false';		
		}else{
			echo 'true';	
		}
		

		print_r(checkForMarketDate($unixInceptDate, $mLink, true, true));
		print_r($aDatePrices);
		echo '</pre>';
		}*/
		
		$aErrors 	= array();
		
		foreach($aDatePrices as $period=>$aDetails){
			
			$aDatePrices[$period]['date-read'] = date('Y-m-d h:i:s a', $aDetails['unix']);
			$dateDebug = checkForMarketDate($aDetails['unix'], $mLink, false, true);
			
			$aDatePrices[$period]['debug'] = $dateDebug;
			
			if($aDetails['price'] == ''){
				//Get fund data
				$query = "
					SELECT price, date
					FROM ".$_SESSION['fund_pricing_table']."
					WHERE fund_id=:fund_id AND date=:date
					ORDER BY unix_date DESC
					LIMIT 1
				";
				try{
					$rsGetPrice = $mLink->prepare($query);
					$aValues = array(
						':fund_id' 	=> $fundID,
						':date'		=> $aDetails['date']
						
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsGetPrice->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				$fundPrice		= $rsGetPrice->fetch(PDO::FETCH_ASSOC);
				
				$navPrice	= $fundPrice['price'];
				
				if($inceptDate == $fundPrice['date']){
					 //echo 'hello';
					$inceptPrice = $navPrice;
					//$aDataPrices[$period]['inceptPrice'] = $inceptPrice;	
				}else{
					$inceptPrice = 10;	
				}
				
			}else{
				$navPrice 	= $aDetails['price'];	
			}
			
			$aDatePrices[$period]['price'] = $navPrice;
			
			$return = (($currentPrice - $navPrice) / $navPrice) * 100;
			
			if($aDetails['return'] == ''){
				$aDatePrices[$period]['return'] = number_format($return, 2, '.',',');
			}
			
			//Get S&P Data
			$index = '^SP500TR';
			//$index = '^GSPC';
			
			//$getSpDate = checkForMarketDate($aDetails['date'], $mLink);
			
			$spDate = substr($aDetails['date'],0,4).'-'.substr($aDetails['date'],4,2).'-'.substr($aDetails['date'],6,2);
			
			$aDatePrices['debug'][$period]['sp-date'] = $spDate;
			
			
			$spDateTimestamp = mktime(8,0,0,substr($aDetails['date'],4,2),substr($aDetails['date'],6,2),substr($aDetails['date'],0,4));
			
			$aDatePrices['debug'][$period]['sp-date-timestamp'] = $spDateTimestamp;
			$getSpDate = checkForMarketDate($spDateTimestamp, $mLink);
			$aDatePrices['debug'][$period]['sp-date-adj'] = $getSpDate;
			$spDateAdj = date('Y-m-d', $getSpDate);
			
			//echo $spDate;
			$query = "
				SELECT close
				FROM ".$_SESSION['index_history_table']."
				WHERE `index`=:index AND `date`=:date
				ORDER BY unix_date DESC
				LIMIT 1
			";
			try{
				$rsGetSP = $mLink->prepare($query);
				$aValues = array(
					':date'		=> $spDateAdj,
					':index'	=> $index
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetSP->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$aErrors[] = $preparedQuery;
			
			$spPrice		= $rsGetSP->fetch(PDO::FETCH_ASSOC);
			
			$spClose	= $spPrice['close'];
			
			if(!isset($spCurrentPrice)){
				$spCurrentPrice = $spClose;
			}
			
			/*echo '<pre>';
			echo $spClose;
			echo '</pre>';*/
			
			$spReturn = (($spCurrentPrice - $spClose) / $spClose) * 100;
			//$aDatePrices[$period]['spQuery'] 	= $preparedQuery;
			//$aDatePrices[$period]['spError'] 	= $error;
			$aDatePrices[$period]['spDate'] 	= $spDate;
			$aDatePrices[$period]['spCurrentPrice'] 	= $spCurrentPrice;	
			$aDatePrices[$period]['spPrice'] 	= $spClose;	
			$aDatePrices[$period]['spReturn'] 	= number_format($spReturn, 2, '.',',');
			
			
			//Calculate Difference
			$difference = (round($return,2) - round($spReturn,2));
			$aDatePrices[$period]['difference']	= number_format($difference, 2, '.', ',');
			
			//inception period
			if($period == 'Since Inception'){
				
				//$inceptPrice = 10;
				$spInceptPrice = $spClose;
					
			}
			
			//annualized period
			if($period == '(Annualized)'){
				$base = $spCurrentPrice / $spInceptPrice;
				$exp = 1 / $fundYears;
		
				$spAAR = ((pow($base, $exp))-1)*100;
				
				$aDatePrices[$period]['spAAR'] = number_format($spAAR, 2, '.', ',');
				
				$base = $currentPrice / $inceptPrice;
				$exp = 1 / $fundYears;
		
				$AAR = ((pow($base, $exp))-1)*100;
				
				$aDatePrices[$period]['AAR'] = number_format($AAR, 2, '.', ',');
				$aDatePrices[$period]['inceptPrice'] = number_format($inceptPrice, 2, '.', ',');
				$aDatePrices[$period]['currentPrice'] = number_format($currentPrice, 2, '.', ',');
				$aDatePrices[$period]['fundLength'] = number_format($fundYears, 2, '.', ',');
				
				$aarDifference = (round($AAR,2) - round($spAAR,2));
				
				$aDatePrices[$period]['difference']	= number_format($aarDifference, 2, '.', ',');
			}
		}
		
		if($_REQUEST['debug'] == '1'){
			echo '<pre>';
			//print_r($aErrors);
			echo 'hello';
			echo $currentPrice.'|'.$inceptPrice;
			//print_r($aDatePrices);
			
			echo '</pre>';	
		}
		
		foreach($aDatePrices as $period=>$aDetails){
			
			$lineTitle = 	"How this is calculated. \n\n".
							"Past NAV:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$".number_format($aDetails['price'],2,'.',',')." \n".
							"Current NAV:&nbsp;&nbsp;&nbsp;&nbsp;$".number_format($aDetails['currentPrice'],2,'.',',')." \n".
							"Formula:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Return = (((Current NAV - Past NAV) / Past NAV) X 100)";
			
			switch($period){
				default: 
				
					//echo $period.'<br />Date: '.$aDetails['date'].'<br />Return: '.$aDetails['return'].'<br />S&P Return: '.$aDetails['spReturn'].'<br />Difference: '.$aDetails['difference'].'<br /><br />';
					if($aDetails['return'] != 0){
					?>
					<tr title="<?php echo $lineTitle;?>">
						<th><?php echo $period;?></th>
						<td><?php echo $aDetails['return'];?>%</td>
						<td><?php echo $aDetails['spReturn'];?>%</td>
						<td><?php echo $aDetails['difference'];?>%</td>
					</tr>
					<?php
					}
				break;
				
				case '(Annualized)': 
					
					$annualizedTitle = 	"How this is calculated. \n\n".
										"Inception NAV: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$aDetails['inceptPrice']."\n".
										"Current NAV: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$aDetails['currentPrice']."\n".
										"Length of Fund: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$aDetails['fundLength']."\n".
										"Formula:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Annualized Return = ((((Current NAV / Inception NAV) ^ (1 / Length of fund in years)) - 1) * 100) \n\n".
										"^ = to the power of, exponent \n".
										"* = multiply \n".
										"/ = divide";
					
					//echo $period.'<br />AAR: '.$aDetails['AAR'].'<br />S&P AAR: '.$aDetails['spAAR'].'<br />Difference: '.$aDetails['difference'].'<br /><br />';
					?>
					<tr title="<?php echo $annualizedTitle;?>">
						<th><?php echo $period;?></th>
						<td><?php echo $aDetails['AAR'];?>%</td>
						<td><?php echo $aDetails['spAAR'];?>%</td>
						<td><?php echo $aDetails['difference'];?>%</td>
					</tr>
					<?php	
				break;
				
				case 'lastDay':
					$asOfDate = date('m/d/Y',$aDetails['unix']);
				break;
			}
		}
		
	break;
		
}

if($process == "1"){
	$fundID = $_REQUEST['fund'];
	
	//Get vars for API query
	/*$queryDate = date('Ymd', strtotime('-1 day'));*/
	$username = $_SESSION['username'];
	$fundSymbol = get_funds($mLink, $fundID, 'fundSymbol');
	
	
	
	
	$query1 = '
		SELECT returnSinceInception, returnLastWeek, returnLast30Days, returnLast90Days, returnLast180Days, returnLastYear, returnLast2Years, returnLast3Years, returnLast5Years, unix_date
		FROM '.$_SESSION['fund_aggregate_table'].'
		WHERE fund_id=:fund_id
		ORDER BY timestamp DESC
		LIMIT 1
	';
	
	try{
		$rsGetFund = $mLink->prepare($query1);
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
	//END: Get S&P Data
	
	/*$oldDate = date('Ymd', $fund['unix_date']);
	
	if($oldDate != $queryDate){
	
		// QUERY API for AggregateStatistics
		$query = 'aggregateStatistics|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$queryDate.'';
		
		//Execute Query
		include ('../../../includes/data-query-legacy.php');
		sleep(1);
		
		// QUERY API for AlphaBetaeStatistics
		$query = 'alphaBetaStatistics|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$queryDate.'';
		
		//Execute Query
		include ('../../../includes/data-query-legacy.php');
		sleep(1);
		
		//Lets give the API some time to work
		sleep(2);
		
		//Rerun Query
		try{
			$rsGetFund = $mLink->prepare($query1);
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
		
	}*/
	sleep(1);
	
	
	//START: Get S&P Data
	$query = '
		SELECT sp500SinceInception, sp500SinceInceptionAAR, sp500LastWeek, sp500LastMonth, sp500Last90, sp500Last180, sp500Last270, sp500Last365, sp500Last730, sp500Last1095, sp500Last1825
		FROM '.$_SESSION['fund_alphabeta_table'].'
		WHERE fund_id=:fund_id
		ORDER BY timestamp DESC
		LIMIT 1
	';
	
	try{
		$rsGetSP = $mLink->prepare($query);
		$aValues = array(
			':fund_id' 	=> $fundID
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetSP->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$sp = $rsGetSP->fetch(PDO::FETCH_ASSOC);
	//END: Get S&P Data
	
	//Create Vars
	$fundLastWeek 		= number_format($fund['returnLastWeek'], 2, '.', '');
	$spLastWeek			= number_format(($sp['sp500LastWeek'] * 100), 2, '.', '');
	$vsLastWeek			= $fundLastWeek - $spLastWeek;
	
	$fundLastMonth		= number_format($fund['returnLast30Days'], 2, '.', '');
	$spLastMonth		= number_format(($sp['sp500LastMonth'] * 100), 2, '.', '');
	$vsLastMonth		= $fundLastMonth - $spLastMonth;
	
	$fundLast3Months	= number_format($fund['returnLast90Days'], 2, '.', '');
	$spLast3Months		= number_format(($sp['sp500Last90'] * 100), 2, '.', '');
	$vsLast3Months		= $fundLast3Months - $spLast3Months;
	
	$fundLast6Months	= number_format($fund['returnLast180Days'], 2, '.', '');
	$spLast6Months		= number_format(($sp['sp500Last180'] * 100), 2, '.', '');
	$vsLast6Months		= $fundLast6Months - $spLast6Months;
	
	$fundLastYear		= number_format($fund['returnLastYear'], 2, '.', '');
	$spLastYear			= number_format(($sp['sp500Last365'] * 100), 2, '.', '');
	$vsLastYear			= $fundLastYear - $spLastYear;
	
	$fundLast2Years		= number_format($fund['returnLast2Years'], 2, '.', '');
	$spLast2Years		= number_format(($sp['sp500Last730'] * 100), 2, '.', '');
	$vsLast2Years		= $fundLast2Years - $spLast2Years;
	
	$fundLast3Years		= number_format($fund['returnLast3Years'], 2, '.', '');
	$spLast3Years		= number_format(($sp['sp500Last1095'] * 100), 2, '.', '');
	$vsLast3Years		= $fundLast3Years - $spLast3Years;
	
	$fundLast5Years		= number_format($fund['returnLast5Years'], 2, '.', '');
	$spLast5Years		= number_format(($sp['sp500Last1825'] * 100), 2, '.', '');
	$vsLast5Years		= $fundLast5Years - $spLast5Years;
	
	$fundInception		= number_format($fund['returnSinceInception'], 2, '.', '');
	$spInception		= number_format(($sp['sp500SinceInception'] * 100), 2, '.', '');
	$vsInception		= $fundInception - $spInception;
	
	if($fundLastWeek !== "0.00"){
	?>
	<tr>
		<th>Last Week</th>
		<td><?php echo $fundLastWeek;?>%</td>
		<td><?php echo $spLastWeek;?>%</td>
		<td><?php echo $vsLastWeek;?>%</td>
	</tr>
	<?php
	}
	
	if($fundLastMonth !== "0.00"){
	?>
	<tr>
		<th>Last Month</th>
		<td><?php echo $fundLastMonth;?>%</td>
		<td><?php echo $spLastMonth;?>%</td>
		<td><?php echo $vsLastMonth;?>%</td>
	</tr>
	<?php
	}
	
	if($fundLast3Months !== "0.00"){
	?>
	<tr>
		<th>Last 3 Months</th>
		<td><?php echo $fundLast3Months;?>%</td>
		<td><?php echo $spLast3Months;?>%</td>
		<td><?php echo $vsLast3Months;?>%</td>
	</tr>
	<?php
	}
	
	if($fundLast6Months !== "0.00"){
	?>
	<tr>
		<th>Last 6 Months</th>
		<td><?php echo $fundLast6Months;?>%</td>
		<td><?php echo $spLast6Months;?>%</td>
		<td><?php echo $vsLast6Months;?>%</td>
	</tr>
	<?php
	}
	
	if($fundLastYear !== "0.00"){
	?>
	<tr>
		<th>Last 12 Months</th>
		<td><?php echo $fundLastYear;?>%</td>
		<td><?php echo $spLastYear;?>%</td>
		<td><?php echo $vsLastYear;?>%</td>
	</tr>
	<?php
	}
	
	if($fundLast2Years !== "0.00"){
	?>
	<tr>
		<th>Last 2 Years</th>
		<td><?php echo $fundLast2Years;?>%</td>
		<td><?php echo $spLast2Years;?>%</td>
		<td><?php echo $vsLast2Years;?>%</td>
	</tr>
	<?php
	}
	
	if($fundLast3Years !== "0.00"){
	?>
	<tr>
		<th>Last 3 Years</th>
		<td><?php echo $fundLast3Years;?>%</td>
		<td><?php echo $spLast3Years;?>%</td>
		<td><?php echo $vsLast3Years;?>%</td>
	</tr>
	<?php
	}
	
	if($fundLast5Years !== "0.00"){
	?>
	<tr>
		<th>Last 5 Years <?php echo $oldDate;?></th>
		<td><?php echo $fundLast5Years;?>%</td>
		<td><?php echo $spLast5Years;?>%</td>
		<td><?php echo $vsLast5Years;?>%</td>
	</tr>
	<?php
	}
	?>
	<tr>
		<th>Since Inception <?php echo $queryDate;?></th>
		<td><?php echo $fundInception;?>%</td>
		<td><?php echo $spInception;?>%</td>
		<td><?php echo $vsInception;?>%</td>
	</tr>
	<?php
	
}

// Process 2 - Get Date
if($process = '2'){
	
	$fundID = $_REQUEST['fund'];
	
	$query = '
		SELECT unix_date
		FROM '.$_SESSION['fund_aggregate_table'].'
		WHERE fund_id=:fund_id
		ORDER BY timestamp DESC
		LIMIT 1
	';
	
	try{
		$rsGetDate = $mLink->prepare($query);
		$aValues = array(
			':fund_id' 	=> $fundID
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetDate->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$date = $rsGetDate->fetch(PDO::FETCH_ASSOC);
	//echo $date['unix_date'];
	
	echo 'Data as of: '.date('m/d/Y', $date['unix_date']).'';	
}

