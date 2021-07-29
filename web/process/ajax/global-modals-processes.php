<?php
/*
Global Include file for portlets that will be used site wide
Supporting Files:
HTML: includes/global-modals.php
Scripts: includes/scripts/global-modals.js.php 
Process: process/ajax/global-modals-processes.php THIS FILE
*/

//START | Include dependents
#Start Session
session_start();
#Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");
#Connect to DB
require_once("../../../secure/dbconnect.php");
#Load System Wide Functions
require_once("../../includes/system-functions.php");

//END | Include dependents

//Set process level variables
$process 					= $_REQUEST['process'];
$api1						= rand(53000, 53019);
$api2						= rand(53100, 53119);	
$setPort					= $api1;
$_SESSION['tradeTimeout']	= 30;

//START | Process Page Functions
function loadTrades($mLink, $fundID, $symbol, $fundSymbol=false, $split=false){
	
	$tradeTimeout = $_SESSION['tradeTimeout'];
	
	#first check to see if the trade data is stale
	$tradeQuery = "
		SELECT ft.*
		FROM ".$_SESSION['fund_trades_table']." AS ft
		WHERE fund_id=:fund_id AND stockSymbol=:symbol
		ORDER BY unix_closed ASC
		LIMIT 1
	";
	try{
		$rsTrades = $mLink->prepare($query);
		$aValues = array(
			':fund_id'	=> $fundID,
			':symbol'	=> $symbol
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsTrades->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$trades 		= $rsTrades->fetch(PDO::FETCH_ASSOC);
	$aTrades 		= array();
	$refreshTrades	= false;
	$today			= date('Ymd');
	$recordDate 	= $trades['timestamp'];
	
	//check to see if record is stale
	if($today != date('Ymd',$recordDate)){
		//Record is stale, get new data
		$refreshTrades = true;	
	}
				
	
	if($refreshTrades == true){
		
		if($fundSymbol == false){
			$fundSymbol = get_funds($mLink, $fundID, 'fundSymbol');	
		}
		
		// Build Query to pass to EXTERNAL API
		$aMethodVars[] = array(
			'method'		=> 'tradesForPosition',
			'source'		=> 'Global Trades | global-modals-processes.php | function: loadTrades',
			'api'			=> '1',
			'username'		=> $_SESSION['username'],
			'fund_id'		=> $fundID,
			'fund_symbol'	=> $fundSymbol,
			'from_date'		=> '',
			'stock_symbol'  => $symbol
		);
		$mlaResults = legacy_api($mLink, $aMethodVars, true);
		//$query 			= 'tradesForPosition|'.$_SESSION['username'].'|'.$fundID.'|'.$fundSymbol.'|'.$symbol.'';
		
		//Execute Query
		//include ('../../includes/data-query-legacy.php');
		
		/*$event 			= 'Data Legacy : tradesForPosition';
		$detail 		= $_SESSION['username'].':'.$query;
		addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);*/
		
		$tradeLoop 		= true;
		$tradeLoopCnt	= 0;
		
		while($tradeLoop){
			
			$query = "
				SELECT ft.*
				FROM ".$_SESSION['fund_trades_table']." AS ft
				WHERE fund_id=:fund_id AND stockSymbol=:symbol
				ORDER BY unix_closed ASC
				LIMIT 1
			";
			try{
				$rsTrades2 = $mLink->prepare($query);
				$aValues = array(
					':fund_id'	=> $fundID,
					':symbol'	=> $symbol
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsTrades2->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$trades	= $rsTrades2->fetch(PDO::FETCH_ASSOC);
			
			$tradeRecordDate = date('Ymd', $trades['timestamp']);
			
			if($tradeRecordDate == $today || $tradeLoopCnt >= $tradeTimeout){
				$tradeLoop 	= false;
				
			}else{
				$tradeLoopCnt++;	
			}
				
		}#end trade while loop
		
	}#end trade refresh statement
	
	//Load Trades Into Array
	$query = "
		SELECT ft.*
		FROM ".$_SESSION['fund_trades_table']." AS ft
		WHERE fund_id=:fund_id AND stockSymbol=:symbol
		ORDER BY unix_closed ASC
	";
	try{
		$rsTrades = $mLink->prepare($query);
		$aValues = array(
			':fund_id'	=> $fundID,
			':symbol'	=> $symbol
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsTrades->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	while($trades = $rsTrades->fetch(PDO::FETCH_ASSOC)){
		#check if split buy or sell
		if($split == false){
			#no split needed, load normal array
			$aTrades[] = array(
				'unixdate'		=> $trades['unix_closed'],
				'date'			=> date('Y-m-d',$trades['unix_closed']),
				'symbol'		=> $trades['stockSymbol'],
				'company'		=> $trades['company_name'],
				'opened'		=> $trades['unix_opened'],
				'closed'		=> $trades['unix_closed'],
				'shares'		=> $trades['sharesFilled'],
				'price'			=> $trades['price'],
				'CA'			=> $trades['createdByCA'],
				'net'			=> $trades['net'],
				'secFee'		=> $trades['secFee'],
				'commission'	=> $trades['commission'],
				'type'			=> $trades['buyOrSell'],
				'uid'			=> $trades['uid'],
				'fund_id'		=> $fundID,
				'ticketKey'		=> $trades['ticketKey']
			);
			
		}else{
			#split needed, split they trades into buys and sells.
			if($trades['buyOrSell'] == "Sell"){
				
				$aTrades[$trades['unix_closed']]['sell'][] = array(
					'date'			=> date('Y-m-d',$trades['unix_closed']),
					'symbol'		=> $trades['stockSymbol'],
					'company'		=> $trades['company_name'],
					'opened'		=> $trades['unix_opened'],
					'closed'		=> $trades['unix_closed'],
					'shares'		=> $trades['sharesFilled'],
					'price'			=> $trades['price'],
					'CA'			=> $trades['createdByCA'],
					'net'			=> $trades['net'],
					'secFee'		=> $trades['secFee'],
					'commission'	=> $trades['commission'],
					'type'			=> $trades['buyOrSell'],
					'uid'			=> $trades['uid'],
					'ticketKey'		=> $trades['ticketKey']
				);
				
			}elseif($trades['buyOrSell'] == "Buy"){
				
				$aTrades[$trades['unix_closed']]['buy'][] = array(
					'date'			=> date('Y-m-d',$trades['unix_closed']),
					'symbol'		=> $trades['stockSymbol'],
					'company'		=> $trades['company_name'],
					'opened'		=> $trades['unix_opened'],
					'closed'		=> $trades['unix_closed'],
					'shares'		=> $trades['sharesFilled'], 
					'price'			=> $trades['price'],
					'CA'			=> $trades['createdByCA'],
					'net'			=> $trades['net'],
					'secFee'		=> $trades['secFee'],
					'commission'	=> $trades['commission'],
					'type'			=> $trades['buyOrSell'],
					'uid'			=> $trades['uid'],
					'ticketKey'		=> $trades['ticketKey']
				);	
			}
		}//end split buy and sell	
	}
	
	return($aTrades);
	
}#end load trades function

//END | Process Page Functions

//Switch on the process variable to determin its process
switch($process){
	
	//+----------------------------------------------------------------------------+
	//|							Load Global Stock Info
	//+----------------------------------------------------------------------------+
	case 'load-stock-info':
		
		$symbol		= $_REQUEST['symbol'];
	
		//Run stockInfo Query
		$symbol = strtoupper($symbol);
		
		//BUILD URL
		$yqlURL = "http://192.168.111.213/feed/currentStockLookup.php?string=".$symbol."";
			
		// Make call with cURL  
		$session = curl_init($yqlURL);  
		curl_setopt($session, CURLOPT_RETURNTRANSFER,true);  
		$json = curl_exec($session); 
		
		// Convert JSON to PHP object   
		$phpObj =  json_decode($json); 
		
		foreach($phpObj as $key=>$obj){
			
			$companyObj = $obj->Security;
			
			$aStock = array(
				'volume'		=> $obj->Volume,
				'high'			=> $obj->High,
				'low'			=> $obj->Low,
				'change'		=> $obj->ChangeFromPreviousClose,
				'price'			=> $obj->Last,
				'name'			=> $companyObj->Name,
				'market'		=> $companyObj->Market,
				'yearHigh'		=> $obj->High52Weeks,
				'yearLow'		=> $obj->Low52Weeks,
				'symbol'        => $symbol
				
				
			);
		}
		//end stockInfo Query
		
		/*$query = "
			SELECT Name AS companyName, Symbol AS symbol, Last AS CurrentPrice, ChangeFromPreviousClose AS chang
			FROM `stock_feed`
			WHERE `Symbol`=:symbol
		";
		try{
			$rsSymbols = $fLink->prepare($query);
			$aValues = array(
				':symbol' 	=> $symbol
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsSymbols->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$feedInfo = $rsSymbols->fetch(PDO::FETCH_ASSOC);*/
		
		
		
		$query = "
			SELECT *
			FROM ".$_SESSION['stock_prices_table']."
			WHERE symbol=:symbol
		";
		try{
			$rsStockInfo = $mLink->prepare($query);
			$aValues = array(
				':symbol'	=> $symbol
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsStockInfo->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$stockInfo = $rsStockInfo->fetch(PDO::FETCH_ASSOC);
		
		
		//Get funds that hold this stock
		$aFundIDs = get_fund_symbols($mLink, $_SESSION['member_id'], 'funds');
		
		//print_r($aFundIDs);
		
		$aFunds = array();
		
		
		foreach($aFundIDs as $fundID=>$fundSymbol){
			
			$aLP = get_funds($mLink, $fundID, 'lp', 'livePrice');
			
			
			//Get strat data
			$query = "
				SELECT *
				FROM ".$_SESSION['fund_stratification_basic_table']." 
				WHERE fund_id=:fund_id AND stockSymbol=:symbol AND totalShares>0
			";
			try{
				$rsStrat = $mLink->prepare($query);
				$aValues = array(
					':fund_id'	=> $fundID,
					':symbol'	=> $symbol
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsStrat->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$strat = $rsStrat->fetch(PDO::FETCH_ASSOC);
			
			$fundTV		            = $aLP['totalValue'];
			$fundName	            = $aLP['fundName'];
			$cash		            = $aLP['cashValue'];
			$adjustedCash           = $aLP['adjustedCash'];
			$shares 	            = $strat['totalShares'];
			
			$shares10	            = floor(((.1 * $fundTV)/$aStock['price']));
			$shares25	            = floor(((.25 * $fundTV)/$aStock['price']));
			
			$sharesTo10             = $shares10 - $shares;
			$sharesTo25             = $shares25 - $shares;
	
			//calculate the max number of shares the member can buy
			$buyMaxShares           = floor(($cash / $aStock['price']));
			$adjustBuyMaxShares     = floor(($adjustedCash / $aStock['price']));
			
			//calculate the max number of shares the member can sell based on tickets already in the que
			$query = "
				SELECT *
				FROM ".$_SESSION['fund_tickets_table']."
				WHERE fund_id=:fund_id AND symbol=:symbol AND status='pending'
			";
			try{
				$rsTickets = $mLink->prepare($query);
				$aValues = array(
					':fund_id'	=> $fundID,
					':symbol'	=> $symbol
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsTickets->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$openShares = 0;
					
			while($ticket = $rsTickets->fetch(PDO::FETCH_ASSOC)){
				
				if($ticket['action'] == 'sell'){
					$openShares = ($openShares + $ticket['shares']);	
				}
			}
			
			$sellAdjustedShares = ($shares - $openShares);
			
			$aFunds['stockInfo']    = $aStock;
			
			$aTrades				= $aTrades = tradesCheck($mLink, $fundID, $symbol, $fundSymbol);
			
			if($aTrades == false){
				$aTrades = 'getTrades';	
			}
			
			$aFunds[$fundID] = array(
				'fundTV'                => $fundTV,
				'fundID'		        => $fundID,
				'fundSymbol'	        => $fundSymbol,
				'fundName'		        => $fundName,
				'currentPrice'	        => $strat['currentPrice'],
				'totalShares'	        => $shares,
				'sellAdjustedShares'	=> $sellAdjustedShares,
				'openShares'			=> $openShares,
				'currentValue'	        => $strat['currentValue'],
				'fundRatio'		        => $strat['fundRatio']*100,
				'gains'			        => $strat['gains'],
				'todayReturn'	        => $strat['todayReturn'],
				'totalReturn'	        => $strat['totalReturn'],
				'sector'		        => $strat['sector'],
				'style'			        => $strat['style'],
				'cash'			        => $cash,
				'adjustedCash'          => $adjustedCash,
				'adjustBuyMaxShares'    => $adjustBuyMaxShares,
				'buyMaxShares'	        => $buyMaxShares,
				'sharesTo10'	        => $sharesTo10,
				'sharesTo25'	        => $sharesTo25,
				'trades'				=> $aTrades
				
			);
			
			$style = $strat['style'];
			$sector = $strat['sector'];
		}
		
		
		$_SESSION['fundsStockTrade'] = $aFunds;
		
		?>
		
		
		
		<br /><br />
		<table class="table table-bordered table-striped table-condensed flip-content volatility-table">
			<thead class="flip-content">
				<tr>
					<th colspan="7" class="strat-table-header">STOCK INFORMATION</th>
				</tr>
				
			</thead>
			
			<tbody style="font-size:16px;">
				<tr>
					<td><strong>Symbol:</strong> <?php echo $symbol;?></td>
					<td style="text-align:center;"><strong>Name:</strong> <?php echo $aStock['name'];?></td>
					<td style="text-align:right;"><strong>Price:</strong> $<?php echo number_format($aStock['price'],2,'.',',');?></td>
				</tr>
				<tr>
					<td><strong>Volume:</strong> <?php echo number_format($aStock['volume'], 0, '.',',');?></td>
					<td style="text-align:center;"><strong>Sector:</strong> <?php echo $sector;?></td>
					<td style="text-align:right;"><strong>Change:</strong> $<?php echo number_format($aStock['change'],2,'.',',');?></td>
				</tr>
				<tr>
					<td><strong>Short Volume:</strong> <?php echo number_format($stockInfo['shortVolume'], 0, '.',',');?></td>
					<td style="text-align:center;"><strong>Style:</strong> <?php echo $style;?></td>
					<td style="text-align:right;"><strong>Market Cap:</strong> $<?php echo number_format($stockInfo['marketcap'],2 , '.',',');?></td>
				</tr>
				<tr>
					<td><strong>Avg Volume:</strong> <?php echo number_format($aStock['volume'], 0, '.',',');?></td>
					<td style="text-align:center;"><strong>Exchange:</strong> <?php echo $aStock['market'];?></td>
					<td style="text-align:right;"><strong>Today:</strong> <?php echo number_format($stockInfo['todayReturn'],2, '.',',');?>%</td>
				</tr>
				<tr>
					<td><strong>50 Day Avg:</strong> $<?php echo number_format($stockInfo['moving50DayAvgClosed'], 2, '.',',');?></td>
					<td style="text-align:center;"><strong>Year High:</strong> <?php echo $aStock['yearHigh'];?></td>
					<td style="text-align:right;"><strong>High:</strong> $<?php echo $aStock['high'];?></td>
				</tr>
				<tr>
					<td><strong>200 Day Avg:</strong> $<?php echo number_format($stockInfo['moving200DayAvgClosed'], 2, '.',',');?></td>
					<td style="text-align:center;"><strong>Year Low:</strong> <?php echo $aStock['yearLow'];?></td>
					<td style="text-align:right;"><strong>Low:</strong> $<?php echo $aStock['low'];?></td>
				</tr>
			</tbody>
		</table>
		
		<div style="width:500px;margin:0px auto 10px auto;">
			<div style="float:left;width:150px;">
				<form method="post" class="new-quote-form">
				<div class="input-group">
					<input type="text" class="form-control" name="new-quote" id="new-quote" value="<?php echo $symbol;?>" style="font-weight:bold;text-transform: uppercase;"/>
					<span class="input-group-btn">
					<input type="submit"  class="btn btn-success" value="Quote" style="text-transform:uppercase;" />
					</span>
				</div>
				</form>
			</div>
			
			<div style="float:left;margin-left:5px;">
				<a href="#stock-info-trade" data-toggle="modal" class="btn btn-info" onclick="loadStockTrade();"><i class="icon-random"></i> Trade Stock</a> <a href="#stock-info-tickets" onclick="loadStockTickets();" data-toggle="modal" class="btn btn-info"><i class="icon-"></i> Tickets</a>
			</div>
			<div class="clearfix"></div>
		</div>
		
		<h4>Recent Prices for <?php echo $symbol;?></h4>
		<hr style="margin:0px 0px 10px 0px;" />
		<div id="stockPriceChart" style="border:1px solid #CCCCCC;min-height:400px;"><img src="/images/ajax-loading.gif" style="display: block;margin-left: auto;margin-right: auto;padding-top:180px;"/></div>
		<?php
		
		
		
		?>
		<h4>Your Holdings of <?php echo $symbol;?> <a href="#stock-info-trade" class="btn btn-info btn-xs" data-toggle="modal"><i class="icon-random"></i> Trade</a></h4>
		<hr style="margin:0px 0px 10px 0px;" />
		<table class="table table-bordered table-striped table-condensed flip-content volatility-table">
			<thead class="flip-content">
				
				<tr class="strat-table-header">
					<th>Fund</th>
					<th>Shares</th>
					<th>Value</th>
					<th>Portion of Fund</th>
				</tr>
			</thead>
			
			<tbody style="text-align:right;">
				<?php
				foreach($aFunds as $fundID=>$aFund){
					if($fundID != 'stockInfo'){
					?>
					<tr>
						<td style="text-align:center;"><a class="btn btn-xs btn-default" style="display:block;" href="?page=03-01-03-001&fund=<?php echo $fundID;?>"><?php echo $aFund['fundSymbol'];?></a></td>
						<td><?php echo number_format($aFund['totalShares'],0,'.',',');?></td>
						<td>$<?php echo number_format($aFund['currentValue'],2,'.',',');?></td>
						<td><?php echo number_format($aFund['fundRatio'],2,'.',',');?>%</td>
					</tr>
					<?php
					}
				}
			?>
			</tbody>
		</table>
		<?php
		
	break;
	case 'load-stock-info-old':
	
		$stockSymbol 	= $_REQUEST['symbol'];
		$today			= date('Ymd');
		$timeout		= 30; //timeout loop in seconds
		
		
		
		//START | check to see if the current stock info is upto date
		$query = "
			SELECT timestamp 
			FROM ".$_SESSION['stock_prices_table']."
			WHERE symbol=:symbol
		";
		try{
			$rsGetTimestamp = $mLink->prepare($query);
			$aValues = array(
				':symbol'	=> $stockSymbol			
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetTimestamp->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$timestamp = $rsGetTimestamp->fetch(PDO::FETCH_ASSOC);
		
		$stockRecordDate	= date('Ymd',$timestamp['timestamp']);
		
		
		if($stockRecordDate != $today){
			#old record, run data legacy query
			$aMethodVars[] = array(
				'method'		=> 'stockInfo',
				'source'		=> 'Global Stock Info | global-modals-processes.php | process: load-stock-info',
				'api'			=> '1',
				'stock_symbol'  => $stockSymbol,
				'group'			=> date('Ymd', time()).'-load-stock-info'
			);
			
			/*$query = 'stockInfo|'.$stockSymbol;
			
			include ('../../includes/data-query-legacy.php');
			
			$event = 'Stock Info: lookup';
            $detail = $_SESSION['username'].':'.$query;
            addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);*/
			//echo 'Old Record '.date('Ymd', $timestamp);
		}
		//END | check to see if the current stock info is upto date
		
		
		
		//START | check to see if stockactions is up to date
		$query = "
			SELECT timestamp 
			FROM ".$_SESSION['stock_changeactions_table']."
			WHERE symbol=:symbol
			LIMIT 1
		";
		try{
			$rsGetTimestamp = $mLink->prepare($query);
			$aValues = array(
				':symbol'	=> $stockSymbol			
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetTimestamp->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$timestamp = $rsGetTimestamp->fetch(PDO::FETCH_ASSOC); 
		
		$caRecordDate = date('Ymd',$timestamp['timestamp']);
		
		if($caRecordDate != $today){
			
			$aMethodVars[] = array(
				'method'		=> 'stockActions',
				'source'		=> 'Global Stock Actions | global-modals-processes.php | process: load-stock-info',
				'api'			=> '1',
				'stock_symbol'  => $stockSymbol,
				'group'			=> date('Ymd', time()).'-load-stock-info'
			);
			
			
			/*$query = "stockActions|".$stockSymbol;
			include ('../../includes/data-query-legacy.php');
			
			$event = 'Stock Actions: CA Lookup';
            $detail = $_SESSION['username'].':'.$query;
            addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);	*/
		}
		
		$mlaResults = legacy_api($mLink, $aMethodVars, true);
		
		//END | check to see if stockactions is up to date
		
		//START | check to see if we need to loop check for the new record
		if($caRecordDate != $today){
			#record date does not match today, run new price run query for date in question		
			
			$timeoutLoop 	= true;
			$timeoutCnt		= 0;
			
			
			//loop through unitl the new record comes in or it timesout for the set timeout period
			while($timeoutLoop){
				//sleep 1 second
				sleep(1);
				
				$query = "
					SELECT timestamp 
					FROM ".$_SESSION['stock_changeactions_table']."
					WHERE symbol=:symbol
					LIMIT 1
				";
				try{
					$rsGetTimestamp = $mLink->prepare($query);
					$aValues = array(
						':symbol'	=> $stockSymbol			
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsGetTimestamp->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				$timestamp = $rsGetTimestamp->fetch(PDO::FETCH_ASSOC); 
				
				$caRecordDate = date('Ymd',$timestamp['timestamp']);
				
				
				if($caRecordDate == $today || $timeoutCnt >= $timeout){
					$timeoutLoop 	= false;
				}else{
					$timeoutCnt++;	
				}
				
			}#end while loop
			
		}#end if check
		//END | check to see if we need to loop check for the new record
		
		
		//START | check to see if we need to loop check for the new record
		if($stockRecordDate != $today){
			#record date does not match today, run new price run query for date in question		
			
			$timeoutLoop 	= true;
			$timeoutCnt		= 0;
			
			
			//loop through unitl the new record comes in or it timesout for the set timeout period
			while($timeoutLoop){
				//sleep 1 second
				sleep(1);
				
				$query = "
					SELECT timestamp 
					FROM ".$_SESSION['stock_prices_table']."
					WHERE symbol=:symbol
				";
				try{
					$rsGetTimestamp = $mLink->prepare($query);
					$aValues = array(
						':symbol'	=> $stockSymbol			
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsGetTimestamp->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				$timestamp = $rsGetTimestamp->fetch(PDO::FETCH_ASSOC); 
				
				$stockRecordDate = date('Ymd',$timestamp['timestamp']);
				
				
				if($stockRecordDate == $today || $timeoutCnt >= $timeout){
					$timeoutLoop 	= false;
				}else{
					$timeoutCnt++;	
				}
				
			}#end while loop
			
		}#end if check
		//END | check to see if we need to loop check for the new record
		
		
		
		
		//START | Pull Stock Info
		$query = "
			SELECT *
			FROM `stock_feed`
			WHERE `Symbol`=:symbol
		";
		try{
			$rsSymbols = $fLink->prepare($query);
			$aValues = array(
				':symbol' 	=> $stockSymbol
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsSymbols->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$count = '0';
		$foo = $rsSymbols->fetch(PDO::FETCH_ASSOC);
		
		$aStock = array(
			'volume'		=> $foo['Volume'],
			'high'			=> $foo['High'],
			'low'			=> $foo['Low'],
			'change'		=> $foo['ChangeFromPreviousClose'],
			'price'			=> $foo['Last'],
			'name'			=> $foo['Name'],
			'market'		=> $foo['Market'],
			'yearHigh'		=> $foo['High52Weeks'],
			'yearLow'		=> $foo['Low52Weeks'],
            'symbol'        => $stockSymbol
			
			
		);
		$query = "
			SELECT *
			FROM ".$_SESSION['stock_prices_table']."
			WHERE symbol=:symbol
		";
		try{
			$rsStockInfo = $mLink->prepare($query);
			$aValues = array(
				':symbol'	=> $stockSymbol
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsStockInfo->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$stockInfo = $rsStockInfo->fetch(PDO::FETCH_ASSOC);
		
		
		//Get funds that hold this stock
		$aFundIDs = get_fund_symbols($mLink, $_SESSION['member_id'], 'funds');
		
		//print_r($aFundIDs);
		
		$aFunds = array();
		
		
		foreach($aFundIDs as $fundID=>$fundSymbol){
			
			$aLP = get_funds($mLink, $fundID, 'lp', 'livePrice');
			
			
			//Get strat data
			$query = "
				SELECT *
				FROM ".$_SESSION['fund_stratification_basic_table']." 
				WHERE fund_id=:fund_id AND stockSymbol=:symbol AND totalShares>0
			";
			try{
				$rsStrat = $mLink->prepare($query);
				$aValues = array(
					':fund_id'	=> $fundID,
					':symbol'	=> $stockSymbol
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsStrat->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$strat = $rsStrat->fetch(PDO::FETCH_ASSOC);
			
			$fundTV		            = $aLP['totalValue'];
			$fundName	            = $aLP['fundName'];
			$cash		            = $aLP['cashValue'];
			$adjustedCash           = $aLP['adjustedCash'];
			$shares 	            = $strat['totalShares'];
			
			$shares10	            = floor(((.1 * $fundTV)/$aStock['price']));
			$shares25	            = floor(((.25 * $fundTV)/$aStock['price']));
			
			$sharesTo10             = $shares10 - $shares;
			$sharesTo25             = $shares25 - $shares;
	
			//calculate the max number of shares the member can buy
			$buyMaxShares           = floor(($cash / $aStock['price']));
			$adjustBuyMaxShares     = floor(($adjustedCash / $aStock['price']));
			
			//calculate the max number of shares the member can sell based on tickets already in the que
			$query = "
				SELECT *
				FROM ".$_SESSION['fund_tickets_table']."
				WHERE fund_id=:fund_id AND symbol=:symbol AND status='pending'
			";
			try{
				$rsTickets = $mLink->prepare($query);
				$aValues = array(
					':fund_id'	=> $fundID,
					':symbol'	=> $stockSymbol
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsTickets->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$openShares = 0;
					
			while($ticket = $rsTickets->fetch(PDO::FETCH_ASSOC)){
				
				if($ticket['action'] == 'sell'){
					$openShares = ($openShares + $ticket['shares']);	
				}
			}
			
			$sellAdjustedShares = ($shares - $openShares);
			
			$aFunds['stockInfo']    = $aStock;
			
			//$aTrades				= $aTrades = tradesCheck($mLink, $fundID, $symbol, $fundSymbol);
			
			if($aTrades == false){
				$aTrades = 'getTrades';	
			}
			
			$aFunds[$fundID] = array(
				'fundTV'                => $fundTV,
				'fundID'		        => $fundID,
				'fundSymbol'	        => $fundSymbol,
				'fundName'		        => $fundName,
				'currentPrice'	        => $strat['currentPrice'],
				'totalShares'	        => $shares,
				'sellAdjustedShares'	=> $sellAdjustedShares,
				'openShares'			=> $openShares,
				'currentValue'	        => $strat['currentValue'],
				'fundRatio'		        => $strat['fundRatio']*100,
				'gains'			        => $strat['gains'],
				'todayReturn'	        => $strat['todayReturn'],
				'totalReturn'	        => $strat['totalReturn'],
				'sector'		        => $strat['sector'],
				'style'			        => $strat['style'],
				'cash'			        => $cash,
				'adjustedCash'          => $adjustedCash,
				'adjustBuyMaxShares'    => $adjustBuyMaxShares,
				'buyMaxShares'	        => $buyMaxShares,
				'sharesTo10'	        => $sharesTo10,
				'sharesTo25'	        => $sharesTo25,
				'trades'				=> $aTrades
				
			);
			
			$style = $strat['style'];
			$sector = $strat['sector'];
		}
		
		
		$_SESSION['fundsStockTrade'] = $aFunds;
		//END | Pull Stock Info
		
		?>
        <table class="table table-bordered table-striped table-condensed flip-content volatility-table">
            <thead class="flip-content">
                <tr>
                    <th colspan="7" class="strat-table-header">STOCK INFORMATION</th>
                </tr>
                
            </thead>
            
            <tbody style="font-size:16px;">
                <tr>
                    <td><strong>Symbol:</strong> <?php echo $stockSymbol;?></td>
                    <td style="text-align:center;"><strong>Name:</strong> <?php echo $aStock['name'];?></td>
                    <td style="text-align:right;"><strong>Price:</strong> $<?php echo number_format($aStock['price'],2,'.',',');?></td>
                </tr>
                <tr>
                    <td><strong>Volume:</strong> <?php echo number_format($aStock['volume'], 0, '.',',');?></td>
                    <td style="text-align:center;"><strong>Sector:</strong> <?php echo $sector;?></td>
                    <td style="text-align:right;"><strong>Change:</strong> $<?php echo number_format($aStock['change'],2,'.',',');?></td>
                </tr>
                <tr>
                    <td><strong>Short Volume:</strong> <?php echo number_format($stockInfo['shortVolume'], 0, '.',',');?></td>
                    <td style="text-align:center;"><strong>Style:</strong> <?php echo $style;?></td>
                    <td style="text-align:right;"><strong>Market Cap:</strong> $<?php echo number_format($stockInfo['marketcap'],2 , '.',',');?></td>
                </tr>
                <tr>
                    <td><strong>Avg Volume:</strong> <?php echo number_format($aStock['volume'], 0, '.',',');?></td>
                    <td style="text-align:center;"><strong>Exchange:</strong> <?php echo $aStock['market'];?></td>
                    <td style="text-align:right;"><strong>Today:</strong> <?php echo number_format($stockInfo['todayReturn'],2, '.',',');?>%</td>
                </tr>
                <tr>
                    <td><strong>50 Day Avg:</strong> $<?php echo number_format($stockInfo['moving50DayAvgClosed'], 2, '.',',');?></td>
                    <td style="text-align:center;"><strong>Year High:</strong> <?php echo $aStock['yearHigh'];?></td>
                    <td style="text-align:right;"><strong>High:</strong> $<?php echo $aStock['high'];?></td>
                </tr>
                <tr>
                    <td><strong>200 Day Avg:</strong> $<?php echo number_format($stockInfo['moving200DayAvgClosed'], 2, '.',',');?></td>
                    <td style="text-align:center;"><strong>Year Low:</strong> <?php echo $aStock['yearLow'];?></td>
                    <td style="text-align:right;"><strong>Low:</strong> $<?php echo $aStock['low'];?></td>
                </tr>
            </tbody>
        </table>
        
        <div style="width:500px;margin:0px auto 10px auto;">
            <div style="float:left;width:150px;">
                <form method="post" class="global-quote-form">
                <div class="input-group">
                    <input type="text" class="form-control" name="global-new-quote" id="global-new-quote" value="<?php echo $stockSymbol;?>" style="font-weight:bold;text-transform: uppercase;"/>
                    <span class="input-group-btn">
                    <input type="submit"  class="btn btn-success" value="Quote" style="text-transform:uppercase;" />
                    </span>
                </div>
                </form>
            </div>
            
            <div style="float:left;margin-left:5px;">
                <a href="#stock-info-trade" data-toggle="modal" class="btn btn-info" onclick="loadStockTrade();"><i class="icon-random"></i> Trade Stock</a> <a href="#stock-info-tickets" onclick="loadStockTickets();" data-toggle="modal" class="btn btn-info"><i class="icon-"></i> Tickets</a>
            </div>
            <div class="clearfix"></div>
        </div>
        
        <h4>Recent Prices for <?php echo $stockSymbol;?></h4>
        	<hr style="margin:0px 0px 10px 0px;" />
        <div id="stockPriceChart" style="border:1px solid #CCCCCC;min-height:400px;"><img src="/images/ajax-loading.gif" style="display: block;margin-left: auto;margin-right: auto;padding-top:180px;"/></div>
        
		<h4>Your Holdings of <?php echo $symbol;?> <a href="#stock-info-trade" class="btn btn-info btn-xs" data-toggle="modal"><i class="icon-random"></i> Trade</a></h4>
        <hr style="margin:0px 0px 10px 0px;" />
        <table class="table table-bordered table-striped table-condensed flip-content volatility-table">
            <thead class="flip-content">
                
                <tr class="strat-table-header">
                    <th>Fund</th>
                    <th>Shares</th>
                    <th>Value</th>
                    <th>Portion of Fund</th>
                </tr>
            </thead>
            
            <tbody style="text-align:right;">
                <?php
                foreach($aFunds as $fundID=>$aFund){
                    if($fundID != 'stockInfo'){
                    ?>
                    <tr>
                        <td style="text-align:center;"><a class="btn btn-xs btn-default" style="display:block;" href="?page=03-01-03-001&fund=<?php echo $fundID;?>"><?php echo $aFund['fundSymbol'];?></a></td>
                        <td><?php echo number_format($aFund['totalShares'],0,'.',',');?></td>
                        <td>$<?php echo number_format($aFund['currentValue'],2,'.',',');?></td>
                        <td><?php echo number_format($aFund['fundRatio'],2,'.',',');?>%</td>
                    </tr>
                    <?php
                    }
                }
            ?>
            </tbody>
        </table>
		<?php
		
		echo $stockSymbol;
	
	break;
	
	//+----------------------------------------------------------------------------+
	//|							Save Stock Label Info
	//+----------------------------------------------------------------------------+
	case 'save-global-label':
		
		$fundID 		= $_REQUEST['fund_id'];
		$label			= $_REQUEST['label'];
		$rationale		= $_REQUEST['rationale'];
		$stockSymbol	= $_REQUEST['stock_symbol'];
		$labelCnt		= strlen($label);
		$rationaleCnt	= strlen($rationale);
		$aErrors		= array();
		
		//$aErrors[] = 'test';
		
		if($fundID == ''){
			$aErrors[] 	= 'No Fund ID passed.';	
		}
		
		/*if($label == ''){
			$aErrors[] = 'Please provide a label. If you do not wish to use a label, please click close.';	
		}*/
		
		if($labelCnt > 255){
			$aErrors[] = 'Label is to long, please make it smaller. You may use a max of 255 characters.';
		}
		
		if($rationaleCnt > 2048){
			$aErrors[] = 'Rationale is to long, please make it smaller. You may use a max of 2048 characters.';
		}	
		
		if(empty($aErrors)){
			
			$query = "
				DELETE FROM ".$_SESSION['positions_labels_table']."
				WHERE fund_id=:fund_id AND stock_symbol=:stock_symbol
			";
			try{
				$rsDELETE = $mLink->prepare($query);
				$aValues = array(
					':fund_id'		=> $fundID,
					':stock_symbol'	=> $stockSymbol			
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsDELETE->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$query = "
				INSERT INTO ".$_SESSION['positions_labels_table']."(
					fund_id,
					stock_symbol,
					label,
					rationale,
					timestamp
				)VALUES(
					:fund_id,
					:stock_symbol,
					:label,
					:rationale,
					UNIX_TIMESTAMP()
				)
			";
			try{
				$rsInsert = $mLink->prepare($query);
				$aValues = array(
					':fund_id'		=> $fundID,
					':stock_symbol'	=> $stockSymbol,
					':label'		=> $label,
					':rationale'	=> $rationale			
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsInsert->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
				
		}else{
			
			echo '<div class="alert alert-danger"><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';	
			}
			echo '</ul></div>';
		}
		
	break;
	
	
	//+----------------------------------------------------------------------------+
	//|							Load LABEL Modal
	//+----------------------------------------------------------------------------+
	case 'load-label-modal':
		
		$fundID			= $_REQUEST['fund'];
		$stockSymbol	= $_REQUEST['stockSymbol'];
		
		$aLabelInfo		= getPosLabel($mLink, $fundID, $stockSymbol);
		
		$label 			= $aLabelInfo['label'];
		$rationale		= $aLabelInfo['rationale'];
		
		#get all current positions
		$query = "
			SELECT stockSymbol
			FROM ".$_SESSION['fund_stratification_basic_table']."
			WHERE fund_id=:fund_id and totalShares>'0'
			ORDER BY stockSymbol ASC
		";
		try{
			$rsGetPos = $mLink->prepare($query);
			$aValues = array(
				':fund_id'	=> $fundID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetPos->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		//echo $preparedQuery;
		$aPositions = array();		
		while($pos = $rsGetPos->fetch(PDO::FETCH_ASSOC)){
			
			$aPositions[] = $pos['stockSymbol'];
				
		}
		
		?>
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
            <h4 class="modal-title">Set Label</h4>
        </div><!--modal-header-->
        <form role="form" action="/process/ajax/global-modals-processes.php?process=save-position-label" method="post" class="edit-label-form">
            <div class="modal-body">
            	<div id="label-form-errors"></div>
                
                <p>You can use label to enter a very small reminder about this position for use other pages. How you use this is up to you, but one suggested use is a short reminder for why you chose this position for use with the strategy report. By comparing this label to the performance of a position, you can see which strategies work best for you.</p>
                <p>You can use the notes field for more extensive notes on a position.</p>
                
                    
                    <div class="form-group">
                        <label>Position</label>
                        <select class="form-control" id="stock_symbol" name="stock_symbol" onchange="updateLabel('<?php echo $fundID;?>');">
                            <?php
                            foreach($aPositions as $key=>$symbol){
                                
                                if($symbol == $stockSymbol){
                                    $selected = 'selected';	
                                }else{
                                    $selected = '';	
                                }
                                echo '<option value="'.$symbol.'" '.$selected.'>'.$symbol.'</option>';	
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Label</label>
                        <input type="text" class="form-control" name="label" placeholder="Set Label" value="<?php echo $label;?>">
                        <small class="help-text">Max Character: 255</small>
                    </div>
                    <div class="form-group">
                        <label>Rationale</label>
                        <textarea class="form-control" name="rationale" row="5"><?php echo $rationale;?></textarea>
                        <small class="help-text">Max Character: 2048</small>
                    </div>
                	<input type="hidden" value="<?php echo $fundID;?>" name="fund_id" />
            </div><!--modal-body-->
            <div class="modal-footer">
                <input type="submit" class="btn btn-success" value="Save" />
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div><!--modal-footer-->
        </form>	
        <?php
		
	break;
	
	//+----------------------------------------------------------------------------+
	//|							Load Ticket Details
	//+----------------------------------------------------------------------------+
	case 'load-ticket-details':
		
		$ticketKey 	= $_REQUEST['ticketKey'];
			
		$query = "
			SELECT ft.*, ticket.reasons, ticket.description
			FROM ".$_SESSION['fund_trades_table']." AS ft
			INNER JOIN ".$_SESSION['fund_tickets_table']." AS ticket ON ticket.ticket_key=ft.ticketKey
			WHERE ft.ticketKey=:ticketKey
		";
		$query = "
			SELECT ft.*
			FROM ".$_SESSION['fund_trades_table']." AS ft
			WHERE ft.ticketKey=:ticketKey
		";
		
		try{
			$rsTrades = $mLink->prepare($query);
			$aValues = array(
				':ticketKey'	=> trim($ticketKey)
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsTrades->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		//echo $preparedQuery;
				
		$trades = $rsTrades->fetch(PDO::FETCH_ASSOC);
		
		$stockSymbol		= $trades['stockSymbol'];
		$companyName		= $trades['company_name'];
		$opened				= $trades['unix_opened'];
		$close				= $trades['unix_closed'];
		$sharesOrdered		= $trades['sharesOrdered'];
		$sharesFilled		= $trades['sharesFilled'];
		$price				= $trades['price'];
		$limit				= $trades['limit'];
		$expires			= $trades['dayOrGTC'];
		$net				= '$'.number_format($trades['net'],2,'.',',');
		$secFee				= '$'.number_format($trades['secFee'],2,'.',',');
		$commission			= '$'.number_format($trades['commission'],2,'.',',');
		$action				= $trades['buyOrSell'];
		$status				= $trades['ticketStatus'];
		$comment			= $trades['comment'];
		$CA					= $trades['createdByCA'];
		
		if($comment == ''){
			//Get ticket description and reasons
			$query = "
				SELECT *
				FROM ".$_SESSION['fund_tickets_table']." 
				WHERE ticket_key=:ticketKey
			";
			
			try{
				$rsTicket = $mLink->prepare($query);
				$aValues = array(
					':ticketKey'	=> trim($ticketKey)
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsTicket->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$ticket = $rsTicket->fetch(PDO::FETCH_ASSOC);
			
			//echo $preparedQuery;
			
			$desc			= urldecode($ticket['description']);
			$reasons		= explode('|',$ticket['reasons']);
			$reasons		= implode(', ',$reasons);
			
		}else{
			
			$aComments 		= explode('|', $comment);
			$aComments		= array_reverse($aComments);
			
			$desc 			= $aComments[0];
			$aReasons		= array();
			
			foreach($aComments as $key=>$reason){
				
				if($key != 0){
					$aReasons[] = $reason;	
				}
					
			}
			
			$reasons	= implode(', ',$aReasons);
				
		}
		
		//echo $desc;
		
		$fundID				= $trades['fund_id'];
		$aFundInfo			= get_funds($mLink, $fundID, 'ident');
		$fundName			= $aFundInfo['name'];
		$fundSymbol			= $aFundInfo['symbol'];
		
		$netAvgPrice		= '$'.number_format(($trades['net'] / $sharesFilled),4,'.',',');	
		
		if($limit == '0.00'){
			$limit = '';	
		}
		//echo $trades['company_name'].' | '.$trades['stockSymbol'].' | '.date('M d, Y',$trades['unix_closed']);
		
		?>
		<p><p>
		<h4><span style="float:left;">Ticket Details for <?php echo $stockSymbol;?> <?php echo $companyName;?></span> <span style="float:right;"><small>Status: <span class="success"><?php echo $status;?></span></small></span> </h4>
		<div class="clearfix"></div>
		<hr style="margin:5px 0px 10px 0px;" />
		
		<table class="table table-bordered table-striped table-condensed flip-content">
			<thead class="flip-content">
				<tr>
					<th colspan="7" class="strat-table-header">Ticket Detail for <?php echo $stockSymbol;?></th>
				</tr>
				<tr>
					<th>Opened</th>
					<th>Action</th>
					<th>Shares Ordered</th>
					<th>Limit</th>
					<th>Shares Filled</th>
					<th>Expires</th>
				</tr>
			</thead>
			
			<tbody style="text-align:right;">
				<tr>
					<td><?php echo date('M d, Y', $opened);?></td>
					<td><?php echo $action;?></td>
					<td><?php echo $sharesOrdered;?></td>
					<td><?php echo $limit;?></td>
					<td><?php echo $sharesFilled;?></td>
					<td><?php echo $expires;?></td>
				</tr>
			</tbody>
		</table>
		
		<table class="table table-bordered table-striped table-condensed flip-content">
			<thead class="flip-content">
				<tr>
					<th class="strat-table-header">Completion</th>
				</tr>
			</thead>
			
			<tbody>
				<tr>
					<td><p>This ticket was opened on <?php echo date('M d, Y', $opened);?> and completed on <?php echo date('M d, Y', $close);?>.</p>
                    <?php if($CA == '1'){?>
                    <div class="alert alert-warning">This transaction was created by our system as the result of a corporate action.
(Split, Stock Dividend, Merger, Acquisition).</div>
					<?php }?>
					</td>
				</tr>
                
			</tbody>
		</table>
		
		<table class="table table-bordered table-striped table-condensed flip-content">
			<thead class="flip-content">
				<tr>
					<th colspan="7" class="strat-table-header">Fees</th>
				</tr>
			</thead>
			
			<tbody>
				<tr>
					<td><strong>Shares</strong></td>
					<td style="text-align:right;"><?php echo $sharesFilled;?></td>
				</tr>
				<tr>
					<td><strong>SEC Fee</strong></td>
					<td style="text-align:right;"><?php echo $secFee;?></td>
				</tr>
				<tr>
					<td><strong>Commission</strong></td>
					<td style="text-align:right;"><?php echo $commission;?></td>
				</tr>
				<tr>
					<td><strong>Net</strong></td>
					<td style="text-align:right;"><?php echo $net;?></td>
				</tr>
				<tr>
					<td><strong>Net Avg Price</strong></td>
					<td style="text-align:right;"><?php echo $netAvgPrice;?></td>
				</tr>
			</tbody>
		</table>
		<p>Note: Per standard industry practice, price above is the Net Average Price, which includes the stated fees and commissions. The price can be a few cents above or below any limit price, or the high/low for the day.</p>
		<table class="table table-bordered table-striped table-condensed flip-content">
			<thead class="flip-content">
				<tr>
					<th colspan="7" class="strat-table-header">Fund Trades</th>
				</tr>
				<tr>
					<th>Name</th>
					<th>Symbol</th>
					<th>Quantity</th>
				</tr>
			</thead>
			
			<tbody>
				<tr>
					<td><?php echo $fundName;?></td>
					<td><?php echo $fundSymbol;?></td>
					<td style="text-align:right;"><?php echo $sharesFilled;?></td>
				</tr>
			</tbody>
		</table>
        
        <?php //if($reasons != '' || $desc != ''){?>
        <table class="table table-bordered table-striped table-condensed flip-content">
			<thead class="flip-content">
				<tr>
					<th colspan="7" class="strat-table-header">Trade Reasons/Description</th>
				</tr>
				<tr>
					<th>Reasons</th>
					<th>Description</th>
				</tr>
			</thead>
			
			<tbody>
				<tr>
					<td><?php echo $reasons;?></td>
					<td><?php echo $desc;?></td>
				</tr>
			</tbody>
		</table>
		
        <p>Ticket Key: <?php echo $ticketKey;?></p>
		<?php
		//}
		
	break;
	
	//+----------------------------------------------------------------------------+
	//|						Load Position Details TRADES
	//+----------------------------------------------------------------------------+
	case 'load-position-details-trades':
		
		$fundID 		= $_REQUEST['fund'];
		$stockSymbol	= $_REQUEST['stockSymbol'];
		
		$aTrades = loadTrades($mLink, $fundID, $stockSymbol, false, true);
		
		//init ledger, buy, and sell arrays
		$aLedger 	= array();
		$aBuys		= array();
		$aSells		= array();
		
		//init "start" variable
		$start = '';

		//for each date
		foreach($aTrades as $date=>$aTradeType){
			
			//set some vars
			$buySharesTotal 	= 0;
			$sellSharesTotal	= 0;
			$buyCnt				= 0;
			$sellCnt			= 0;
			$caType				= 0;
			
			//loop through the two different types of trades
			foreach($aTradeType as $type=>$aTrade){
				
				//run sell logic
				if($type == 'sell'){
					
					//loop through each trade under type "sell"
					foreach($aTrade as $key=>$aTradeInfo){
						
						//total up all the shares for the buy column
						$sellSharesTotal = $sellSharesTotal + $aTradeInfo['shares'];
						
						//increment sell counter
						$sellCnt++;
						
						//check for ca type
						if($aTradeInfo['CA'] == '1'){
							$caType = '1';	
						}else{
							//set element in sells array
							$aSells[] = array(
								'date'			=> $date,
								'quantity'		=> $aTradeInfo['shares'],
								'commission'	=> $aTradeInfo['commission'],
								'secFee'		=> $aTradeInfo['secFee'],
								'net'			=> $aTradeInfo['net'],
								'price'			=> $aTradeInfo['price'],
								'uid'			=> $aTradeInfo['uid'],
								'ticketKey'		=> $aTradeInfo['ticketKey']
							);
							
						}//end CA check
						
					}//end each trade loop
					
				}//end sell loop
				
				//run "buy" logic
				if($type == 'buy'){
					
					//loop through each trade under type "buy"
					foreach($aTrade as $key=>$aTradeInfo){
						
						//total up all the shares for the buy column
						$buySharesTotal = $buySharesTotal + $aTradeInfo['shares'];
						
						//increment buy counter
						$buyCnt++;
						
						//check for ca type
						if($aTradeInfo['CA'] == '1'){
							$caType = '1';	
						}else{
							//set element in sells array
							$aBuys[] = array(
								'date'			=> $date,
								'quantity'		=> $aTradeInfo['shares'],
								'commission'	=> $aTradeInfo['commission'],
								'net'			=> $aTradeInfo['net'],
								'price'			=> $aTradeInfo['price'],
								'uid'			=> $aTradeInfo['uid'],
								'ticketKey'		=> $aTradeInfo['ticketKey']
							);
							
						}//end CA check
					
					}//end each trade loop
					
				}//end buy loop
				
			}//end tradeType loop
			
			//treat sells like negative numbers
			$sellSharesTotal = $sellSharesTotal*(-1);
			
			//calculate change
			$change = $buySharesTotal + $sellSharesTotal;
			
			//calculate end
			$end 	= $start + $change;
			
			//figure out type
			if($buyCnt > 0){
				$type = 'Buy';	
			}
			
			if($sellCnt > 0){
				$type = 'Sell';	
			}
			
			if($buyCnt > 0 && $sellCnt > 0){
				$type = 'Multiple';	
			}
			
			if($caType == '1'){
				$type = 'Corporate Action';	
			}
			
			//assign to ledger array
			$aLedger[$date] = array(
				'readDate'	=> date('m/d/Y', $date),
				'start'		=> $start,
				'change'	=> $change,
				'end'		=> $end,
				'type'		=> $type			
			);
			
			//set start value to current end value for next loop
			$start = $end;
			
		}
		
		?>
    	
        
        
        <table class="table table-bordered table-striped table-condensed flip-content volatility-table">
            <thead class="flip-content">
                <tr>
                    <th colspan="5" class="strat-table-header">LEDGER</th>
                </tr>
                <tr>
                    <th>Date</th>
                    <th>Start</th>
                    <th>Change</th>
                    <th>End</th>
                    <th>Type</th>
                </tr>
            </thead>
            
            <tbody style="text-align:right;" >
                <?php
                foreach($aLedger as $date=>$aLedgerInfo){
                    
                    $start	= number_format($aLedgerInfo['start'], 0, '.',',');
                    $change	= number_format($aLedgerInfo['change'], 0, '.',',');
                    $end	= number_format($aLedgerInfo['end'], 0, '.',',');
                    $type 	= $aLedgerInfo['type'];
                    
                    ?>
                    <tr>
                        <td style="text-align:left;"><?php echo date('M d, Y', $date);?></td>
                        <td><?php echo $start;?></td>
                        <td><?php echo $change;?></td>
                        <td><?php echo $end;?></td>
                        <td><?php echo $type;?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        
        
        
		
		
        
        <table class="table table-bordered table-striped table-condensed flip-content volatility-table">
            <thead class="flip-content">
                <tr>
                    <th colspan="5" class="strat-table-header">BUYS</th>
                </tr>
                <tr>
                    <th>Close Date</th>
                    <th>Quantity</th>
                    <th>Commission</th>
                    <th>Net</th>
                    <th>Price</th>
                </tr>
            </thead>
            
            <tbody style="text-align:right;">
                <?php
                //Reverse array to show descending order
                
                
                foreach($aBuys as $key=>$aBuyTrades){
                    
                    $date		= date('M d, Y', $aBuyTrades['date']);
                    $quantity	= number_format($aBuyTrades['quantity'], 0, '.',',');
                    $commission	= '$'.number_format($aBuyTrades['commission'], 2, '.',',');
                    $net 		= '$'.number_format($aBuyTrades['net'], 2, '.',',');
                    $price		= '$'.number_format($aBuyTrades['price'], 2, '.',',');
                    $uid		= $aBuyTrades['uid'];
					$ticketKey	= $aBuyTrades['ticketKey'];
                    
                    ?>
                    <tr>
                        <td style="text-align:left;"><a href="#global-ticket-details" data-toggle="modal" onclick="loadGlobalTradeDetails('<?php echo $ticketKey;?>');"><?php echo $date;?></a> </td>
                        <td><?php echo $quantity;?></td>
                        <td><?php echo $commission;?></td>
                        <td><?php echo $net;?></td>
                        <td><?php echo $price;?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        
        <table class="table table-bordered table-striped table-condensed flip-content volatility-table">
            <thead class="flip-content">
                <tr>
                    <th colspan="7" class="strat-table-header">SELLS</th>
                </tr>
                <tr>
                    <th>Close Date</th>
                    <th>Quantity</th>
                    <th>Commission</th>
                    <th>SEC Fee</th>
                    <th>Net</th>
                    <th>Price</th>
                </tr>
            </thead>
            
            <tbody style="text-align:right;">
                <?php
                //Reverse array for descending order
                $aSells = array_reverse($aSells);
                
                foreach($aSells as $key=>$aSellTrades){
                    
                    $date		= date('M d, Y', $aSellTrades['date']);
                    $quantity	= number_format($aSellTrades['quantity'], 0, '.',',');
                    $commission	= '$'.number_format($aSellTrades['commission'], 2, '.',',');
                    $secFee		= '$'.number_format($aSellTrades['secFee'], 2, '.', ',');
                    $net 		= '$'.number_format($aSellTrades['net'], 2, '.',',');
                    $price		= '$'.number_format($aSellTrades['price'], 2, '.',',');
                    $uid		= $aSellTrades['uid'];
					$ticketKey	= $aSellTrades['ticketKey'];
                    ?>
                    <tr>
                        <td style="text-align:left;"><a href="#global-ticket-details" data-toggle="modal" onclick="loadGlobalTradeDetails('<?php echo $ticketKey;?>');"><?php echo $date;?></a></td>
                        <td><?php echo $quantity;?></td>
                        <td><?php echo $commission;?></td>
                        <td><?php echo $secFee;?></td>
                        <td><?php echo $net;?></td>
                        <td><?php echo $price;?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <?php
		
	break;
	
	//+----------------------------------------------------------------------------+
	//|						Load Position Details Content
	//+----------------------------------------------------------------------------+
	case 'load-position-details-content':
		
		//set vars
		$fundID 	= $_REQUEST['fund'];
		$symbol		= $_REQUEST['symbol'];
		
		$fundSymbol	= get_funds($mLink, $fundID, 'fundSymbol');
			
		$query = "
			SELECT *
			FROM ".$_SESSION['fund_positions_details_table']."
			WHERE fund_id=:fund_id AND stockSymbol=:symbol
			ORDER BY timestamp DESC
			LIMIT 1
		";
		try{
			$rsPD = $mLink->prepare($query);
			$aValues = array(
				':fund_id'	=> $fundID,
				':symbol'	=> $symbol
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsPD->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$pd = $rsPD->fetch(PDO::FETCH_ASSOC);
				
		//START RECENT RETURNS TABLE	
		$mtdReturn 		= number_format($pd['mtdReturn'], 2, '.',',');
		$qtdReturn		= number_format($pd['qtdReturn'], 2, '.',',');
		$ytdReturn		= number_format($pd['ytdReturn'], 2, '.',',');
		$currentReturn	= number_format($pd['recentReturn'], 2, '.',',');
		$inceptReturn	= number_format($pd['totalReturn'], 2, '.',',');
		
		//START ATTRIBUTION TABLE
		$yeild 			= number_format($pd['caReturn'], 2, '.',',');
		$selectReturn	= number_format($pd['stockSelectionReturn'], 2, '.',',');
		$activity		= number_format($pd['activityReturn'], 2, '.',',');
		
		//START FUNDAMENTALS TABLE
		$PE			= number_format($pd['signedPERatio'], 2, '.', ',');
		$PS			= number_format($pd['psRatio'], 1, '.', ',');
		$PEG		= 'N/A';
		$PSG		= number_format($pd['salesGrowthRatio'], 1, '.',',');
		$EPS		= number_format($pd['eps'], 2, '.',',');
		
		//START M100 TRADING TABLE
		$TQT			= number_format($pd['q1HoldingsPercentTradeChange'], 0).'%';
		$TQH			= $pd['q1HoldingSize'];
		$m100Trading	= number_format($pd['m100HoldingsPercentTradeChange'], 0).'%';
		$m100Holding	= $pd['m100HoldingSize'];
		$style			= $pd['style'];
		$sector			= $pd['sectorBaseName'];
		$industry		= $pd['subIndustryBaseName'];	
		
		//START CORPORATE ACTIONS TABLE
		$totalCApayout	= '$'.number_format($pd['caYield'], 2, '.', ',');
		$totalCAcost	= '$'.number_format($pd['caCost'], 2, '.', ',');
		$recentCAPayout	= '$'.number_format($pd['recentCAYield'], 2, '.', ',');
		$recentCAcost	= '$'.number_format($pd['recentCACost'], 2, '.', ',');
		
		//START TRADING SUMMARY TABLE
		$firstTrade		= date('M j, Y h:i A',gen_timestamp($pd['firstTradeTimestamp']));
		$lastTrade		= date('M j, Y h:i A',gen_timestamp($pd['lastTradeTimestamp']));
		$totalSells		= '$'.number_format($pd['totalUserSells'], 2, '.', ',');
		$totalBuys		= '$'.number_format($pd['totalUserBuys'], 2, '.', ',');
		$recentSells	= '$'.number_format($pd['recentUserSells'], 2, '.', ',');
		$recentBuys		= '$'.number_format($pd['recentUserBuys'], 2, '.', ',');
		
		?>
		<input type="hidden" id="getSymbol" value="<?php echo $symbol;?>" />
		<div id="global-price-chart" style="border:1px solid #CCCCCC;min-height:400px;"><img src="/images/ajax-loading.gif" style="display: block;margin-left: auto;margin-right: auto;padding-top:180px;"/></div>
		
		<h4 style="float:left;">Position Statistics</h4>
		<p style="float:right;margin-top:10px;"><?php echo date('F d, Y');?></p>
		<div class="clearfix"></div>
		<hr style="margin:0px 0px 10px 0px;" />
		
		<div class="row">
			<div class="col-sm-6">
			
				
				<table class="table table-bordered table-striped table-condensed flip-content volatility-table">
					<thead class="flip-content">
						<tr>
							<th colspan="7" class="strat-table-header">RECENT RETURNS</th>
						</tr>
					</thead>
					
					<tbody>
						<tr>
							<td><strong>MTD Return</strong></td>
							<td width="20%" style="text-align:right;"><?php echo $mtdReturn;?>%</td>
						</tr>
						<tr>
							<td><strong>QTD Return</strong></td>
							<td width="20%" style="text-align:right;"><?php echo $qtdReturn;?>%</td>
						</tr>
						<tr>
							<td><strong>YTD Return</strong></td>
							<td width="20%" style="text-align:right;"><?php echo $ytdReturn;?>%</td>
						</tr>
						<tr>
							<td><strong>Current Return</strong></td>
							<td width="20%" style="text-align:right;"><?php echo $currentReturn;?>%</td>
						</tr>
						<tr>
							<td><strong>Inception Return</strong></td>
							<td width="20%" style="text-align:right;"><?php echo $inceptReturn;?>%</td>
						</tr>
					
					</tbody>
				</table>
				
				<table class="table table-bordered table-striped table-condensed flip-content volatility-table">
					<thead class="flip-content">
						<tr>
							<th colspan="7" class="strat-table-header">ATTRIBUTION</th>
						</tr>
					</thead>
					
					<tbody>
						<tr>
							<td><strong>Yield</strong></td>
							<td width="20%" style="text-align:right;"><?php echo $yeild;?>%</td>
						</tr>
						<tr>
							<td><strong>Selection Return</strong></td>
							<td width="20%" style="text-align:right;"><?php echo $selectReturn;?>%</td>
						</tr>
						<tr>
							<td><strong>Activity</strong></td>
							<td width="20%" style="text-align:right;"><?php echo $activity;?>%</td>
						</tr>
						<tr>
							<td><strong>Inception Return</strong></td>
							<td width="20%" style="text-align:right;"><?php echo $inceptReturn;?>%</td>
						</tr>
					</tbody>
				</table>
				
				<table class="table table-bordered table-striped table-condensed flip-content volatility-table">
					<thead class="flip-content">
						<tr>
							<th colspan="7" class="strat-table-header">FUNDAMENTALS</th>
						</tr>
					</thead>
					
					<tbody>
						<tr>
							<td><strong>P/E</strong></td>
							<td width="20%" style="text-align:right;"><?php echo $PE;?>%</td>
						</tr>
						<tr>
							<td><strong>P/S</strong></td>
							<td width="20%" style="text-align:right;"><?php echo $PS;?>%</td>
						</tr>
						<tr>
							<td><strong>PEG</strong></td>
							<td width="20%" style="text-align:right;"><?php echo $PEG;?></td>
						</tr>
						<tr>
							<td><strong>PSG</strong></td>
							<td width="20%" style="text-align:right;"><?php echo $PSG;?>%</td>
						</tr>
						<tr>
							<td><strong>EPS</strong></td>
							<td width="20%" style="text-align:right;">$<?php echo $EPS;?></td>
						</tr>
					</tbody>
				</table>
			</div><!--col-md-6-->
				
			<div class="col-sm-6">
				<table class="table table-bordered table-striped table-condensed flip-content volatility-table">
					<thead class="flip-content">
						<tr>
							<th colspan="7" class="strat-table-header">m100 TRADING</th>
						</tr>
					</thead>
					
					<tbody>
						<tr>
							<td><strong>Top Quartile Trading</strong></td>
							<td width="40%" style="text-align:right;"><?php echo $TQT;?></td>
						</tr>
						<tr>
							<td><strong>Top Quartile Holdings</strong></td>
							<td width="40%" style="text-align:right;"><?php echo $TQH;?></td>
						</tr>
						<tr>
							<td><strong>m100 Trading</strong></td>
							<td width="40%" style="text-align:right;"><?php echo $m100Trading;?></td>
						</tr>
						<tr>
							<td><strong>m100 Holdings</strong></td>
							<td width="40%" style="text-align:right;"><?php echo $m100Holding;?></td>
						</tr>
						<tr>
							<td><strong>Style</strong></td>
							<td width="40%" style="text-align:right;"><?php echo $style;?></td>
						</tr>
						<tr>
							<td><strong>Sector</strong></td>
							<td width="40%" style="text-align:right;"><?php echo $sector;?></td>
						</tr>
						<tr>
							<td><strong>Industry</strong></td>
							<td width="40%" style="text-align:right;"><?php echo $industry;?></td>
						</tr>
					</tbody>
				</table>
				
				<table class="table table-bordered table-striped table-condensed flip-content volatility-table">
					<thead class="flip-content">
						<tr>
							<th colspan="7" class="strat-table-header">CORPORATE ACTIONS</th>
						</tr>
					</thead>
					
					<tbody>
						<tr>
							<td><strong>Total CA Payouts</strong></td>
							<td width="40%" style="text-align:right;"><?php echo $totalCApayout;?></td>
						</tr>
						<tr>
							<td><strong>Total CA Cost</strong></td>
							<td width="40%" style="text-align:right;"><?php echo $totalCAcost;?></td>
						</tr>
						<tr>
							<td><strong>Recent CA Payouts</strong></td>
							<td width="40%" style="text-align:right;"><?php echo $recentCAPayout;?></td>
						</tr>
						<tr>
							<td><strong>Recent CA Cost</strong></td>
							<td width="40%" style="text-align:right;"><?php echo $recentCAcost;?>%</td>
						</tr>
					</tbody>
				</table>
				
				<table class="table table-bordered table-striped table-condensed flip-content volatility-table">
					<thead class="flip-content">
						<tr>
							<th colspan="7" class="strat-table-header">m100 TRADING</th>
						</tr>
					</thead>
					
					<tbody>
						<tr>
							<td><strong>First Trade</strong></td>
							<td width="40%" style="text-align:right;"><?php echo $firstTrade;?></td>
						</tr>
						<tr>
							<td><strong>Last Trade</strong></td>
							<td width="40%" style="text-align:right;"><?php echo $lastTrade;?></td>
						</tr>
						<tr>
							<td><strong>Total Sells</strong></td>
							<td width="40%" style="text-align:right;"><?php echo $totalSells;?></td>
						</tr>
						<tr>
							<td><strong>Total Buys</strong></td>
							<td width="40%" style="text-align:right;"><?php echo $totalBuys;?></td>
						</tr>
						<tr>
							<td><strong>Recent Sells</strong></td>
							<td width="40%" style="text-align:right;"><?php echo $recentSells;?></td>
						</tr>
						<tr>
							<td><strong>Recent Buys</strong></td>
							<td width="40%" style="text-align:right;"><?php echo $recentBuys;?></td>
						</tr>
					</tbody>
				</table>
			</div><!--col-md-6-->
		</div><!--row-->
		
		<h4>Trades</h4>
		<hr style="margin:0px 0px 10px 0px;" />
        <div id="load-global-pos-details-trades"></div><!--load-trades-div-->
		<?php
		
	break;
	//END LOAD POSITION DETAILS CONENT
	
	//+----------------------------------------------------------------------------+
	//|						Load Price Chart
	//+----------------------------------------------------------------------------+
	case 'load-price-chart':
		$symbol = $_REQUEST['symbol'];
	
		if($_REQUEST['start'] == ''){
			$startDate = date('m/d/Y', strtotime('-2 year'));
		}else{
			$startDate = $_REQUEST['start'];	
		}
		
		//echo $startDate;
		if($_REQUEST['end'] == ''){
			$endDate = date('m/d/Y');
		}else{
			$endDate = $_REQUEST['end'];	
		}
		$symbol = strtoupper($symbol);
		
		//BUILD URL
		$yqlURL = "http://192.168.111.213/feed/historicalStockRangeLookup.php?symbol=".$symbol."&StartDate=".$startDate."&EndDate=".$endDate."";
			
		// Make call with cURL  
		$session = curl_init($yqlURL);  
		curl_setopt($session, CURLOPT_RETURNTRANSFER,true);  
		$json = curl_exec($session); 
		
		// Convert JSON to PHP object   
		$phpObj =  json_decode($json); 
		
		/*echo '<pre>';
		print_r($phpObj);
		echo '</pre>';*/
		
		$aQuotes = $phpObj->GlobalQuotes;
		
		
		foreach($aQuotes as $key=>$aObj){
			$aQuotes[$key] = array(
				'Date'			=> $aObj->Date,
				'Open'			=> $aObj->Open,
				'High' 			=> $aObj->High,
				'Low'			=> $aObj->Low,
				'Close'			=> $aObj->Last,
				'Volume'		=> $aObj->Volume,
				'SplitRatio'	=> $aObj->SplitRatio
			);	
		}
		
		/*echo '<pre>';
		print_r($aQuotes);
		echo '</pre>';*/
	
		$cnt = 0;
		foreach($aQuotes as $key=>$aQuote){
				
			
			//Set Variables from returned results
			$date		= $aQuote['Date'];
			$open		= $aQuote['Open'];
			$high		= $aQuote['High'];
			$low		= $aQuote['Low'];
			$close		= $aQuote['Close'];
			$volume		= $aQuote['Volume'];
			$splitRatio	= $aQuote['SplitRatio'];
			
			//adjust for splits
			$open	= ($open / $splitRatio);
			$high	= ($high / $splitRatio);
			$low	= ($low / $splitRatio);
			$close 	= ($close / $splitRatio);
					
					
					
			//$volume	= ($volume * $splitRatio);
			
			
			
			$aDate = explode('/', $date);
			
			$unixdate = mktime(0, 0, 0, $aDate[0], $aDate[1], $aDate[2]);
			
			$aHistory[$cnt] = array(($unixdate*1000),$open,$high,$low,$close,$volume);//,date('Y-m-d',$unixdate),$date
			
			$cnt++;
			
			
		}
		$aHistory = array_reverse($aHistory);
		//$json = json_encode($aHistory);
		
		$json = json_encode($aHistory);
		
		print_r($json);
	break;
	//END LOAD PRICE CHART
	
	
}

?>