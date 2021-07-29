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
$process 	= $_REQUEST['process'];
$api1		= rand(53000, 53019);
$api2		= rand(53100, 53119);	
$setPort	= $api1;
$apiType 	= '1'; //tells the new api function which api to send the request to, ports are decided in function
//Process Functions


function stockActionsCheck($mLink, $fundID, $symbol){
	//This function checks to see if trade information for today is avaliable in the Database for a particular fund and symbol. Returns true or false, if false, it will run the data legacy query, if true, it will return the array of trades.
	$query = "
		SELECT *
            FROM ".$_SESSION['changeactions_table']."
            WHERE symbol=:symbol AND effectiveDate_timestamp>:inception
            ORDER BY effectiveDate_timestamp ASC
	";
	try{
		$rsActions = $mLink->prepare($query);
		$aValues = array(
			':inception'	=> get_funds($mLink, $fundID, 'unixIncept', 'agg'),
			':symbol'		=> $symbol
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsActions->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aStockActions = array();
	$checkLoop = 0;	
	while($actions = $rsActions->fetch(PDO::FETCH_ASSOC)){
		
		$checkLoop = 1;
		
		if(date('Ymd',$actions['timestamp']) == date('Ymd')){
			
			$action		= $actions['action'];
			
			$aStockActions[$action][] = array(
				'company_id'		=> $actions['company_id'],
				'symbol'			=> $actions['symbol'],
				'action'			=> $action,
				'effectiveDate'		=> $actions['effectiveDate_timestamp'],
				'recordDate'		=> $actions['recordDate_timestamp'],
				'payDate'			=> $actions['payDate_timestamp'],
				'terms'				=> $actions['terms'],
				'frequency'			=> $actions['frequency'],
				'gross'				=> $actions['gross'],
				'oldExchangeName'	=> $actions['oldExchangeName'],
				'newExchangeName'	=> $actions['newExchangeName'],
				'oldSymbol'			=> $actions['oldSymbol'],
				'newSymbol'			=> $actions['newSymbol'],
				'oldCUSIP'			=> $actions['oldCUSIP'],
				'newCUSIP'			=> $actions['newCUSIP'],
				'spinoffStock'		=> $actions['spinoffStock'],
				'description'		=> $actions['description']
			);
			
		}else{
			
			
			$checkLoop = 0;
			
			break;
				
		}
	}
	
	//if no results return run data legacy query.
	if($checkLoop == 0){
		
		
		
		// Build Query to pass to EXTERNAL API
		$aMethodVars[] = array(
			'method'		=> 'stockActions',
			'source'		=> 'Fund Stratification Basic | fund-strat-basic-processes.php | function: stockActionsCheck',
			'api'			=> '1',
			'stock_symbol'  => $symbol,
		);
		//$query = 'stockActions|'.$symbol.'';
		
		//Execute Query
		$mlaResults = legacy_api($mLink, $aMethodVars, true);
		
		/*include ('../../includes/data-query-legacy.php');
		
		$event = 'Data Legacy : stockActions';
		$detail = $_SESSION['username'].':'.$query;
		addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
		*/
		//echo '<span style="display:none;">reloadTrades</span>';	
		
		return false;
		
	}else{
		
		return $aStockActions;
			
	}
}


if($process == 'refresh-strat'){
	
	$fundID = $_REQUEST['fund'];
	
	/*$username = $_SESSION['username'];
	$fundSymbol = get_funds($mLink, $fundID, 'fundSymbol');
	
	$query = 'allPositionInfo|'.$username.'|'.$fundID.'|'.$fundSymbol.'|1';
	include ('../../includes/data-query-legacy.php');
	
	$event = 'Data Legacy : allPositionInfo';
	$detail = $_SESSION['username'].':'.$query;
	addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
	
	sleep(2);*/
	
	exec('/usr/bin/php /var/www/html/portfolio.marketocracy.com/scripts/strat-build.php "fundID='.$fundID.'" > /dev/null &');
	
	
	
	header('Location: '.$siteRoot.'/?page=03-01-03-001&fund='.$fundID);
		
}

//+---------------------------------------------------------------------------------------------------------+
//|									Check Stratification Process											|
//+---------------------------------------------------------------------------------------------------------+
if($process == 'check-strat-process'){
	
	$fundID = $_REQUEST['fund'];
	
	$query = "
		SELECT processing
		FROM ".$_SESSION['fund_table']."
		WHERE fund_id=:fund_id
	";
	try{
		$rsProcess = $mLink->prepare($query);
		$aValues = array(
			':fund_id'	=> $fundID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsProcess->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$process = $rsProcess->fetch(PDO::FETCH_ASSOC);
	$processStatus = $process['processing'];
	
	if($processStatus == '1'){
	
		?>
		<div class="note note-info">
			<p><strong><img src="images/ajax-refresh.gif" /> Stratification is currently being updated. Please feel free to use the page, we will notify you when the process has completed.</strong></p>
			
		</div>
		<?php
	}else{
		?>
		<div class="note note-info">
			<p class="strat-success"><strong>Stratification updated. Please refresh the page to see the new results.</strong></p>
			
		</div>
		<?php
	}
	
}


//+---------------------------------------------------------------------------------------------------------+
//|									Load Position Details - PROCESS 1										|
//+---------------------------------------------------------------------------------------------------------+

if($process == "1"){
	
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
    <div id="priceChart" style="border:1px solid #CCCCCC;min-height:400px;"><img src="/images/ajax-loading.gif" style="display: block;margin-left: auto;margin-right: auto;padding-top:180px;"/></div>
    
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
    <?php
	
	
	
	$aTrades = tradesCheck($mLink, $fundID, $symbol, $fundSymbol, true);
	
	//print_r($aTrades);
	
	if($aTrades == false){
		echo '<span style="display:none;">reloadTrades</span>';	
	}
	
	//
	//+-----------------------------------------------------------------------------------------------------+
	//										START CREATE LEDGER ARRAY AND BUY/SELL ARRAYS
	//+-----------------------------------------------------------------------------------------------------+
	
	//init ledger, buy, and sell arrays
	$aLedger 	= array();
	$aBuys		= array();
	$aSells		= array();
	
	//init "start" variable
	$start = '';
	
	/*echo '<pre>';
	print_r($aTrades);
	echo '</pre>';
	*/
	if(!empty($aTrades)){
	
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
		
		//Check against existing strat data, if $end does not = current shares, run the reload startification data routine. 
		$query = "
			SELECT totalShares
			FROM ".$_SESSION['fund_stratification_basic_table']."
			WHERE fund_id=:fund_id AND stockSymbol=:symbol
		";
		try{
			$rsShares = $mLink->prepare($query);
			$aValues = array(
				':fund_id'	=> $fundID,
				':symbol'	=> $symbol
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsShares->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$getShares = $rsShares->fetch(PDO::FETCH_ASSOC);
		
		$currentShares = $getShares['totalShares'];
		
		//if current shares are not equal to the ledger end shares, reload the stratification
		if($currentShares != $end){
			exec('/usr/bin/php /var/www/html/portfolio.marketocracy.com/scripts/strat-build.php "fundID='.$fundID.'" > /dev/null &');
			
			//header('Location: '.$siteRoot.'/?page=03-01-03-001&fund='.$fundID.'&message=reload&symbol='.$symbol);
		}
	//+-----------------------------------------------------------------------------------------------------+
	//										END CREATE LEDGER ARRAY
	//+-----------------------------------------------------------------------------------------------------+
	
	
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
			$aBuys = array_reverse($aBuys);
			
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
                	<td style="text-align:left;"><a href="#global-ticket-details" data-toggle="modal" onclick="loadGlobalTradeDetails('<?php echo $ticketKey;?>');"><?php echo $date;?></a></td>
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
	}else{
		echo '<div id="load-trade-info"></div>';	
	}//end check for empty a trades
	
	
	$aStockActions = stockActionsCheck($mLink, $fundID, $symbol);
		
	if($aStockActions != false){
		
		$posStart = get_pos_start($mLink, $fundID, $symbol);
		
		if(!empty($aStockActions['SPLIT'])){
			
			
			?>
	
			<h4 style="margin-top:20px;">Corporate Actions</h4>
			<hr style="margin:0px 0px 10px 0px;" />
			
			<table class="table table-bordered table-striped table-condensed flip-content volatility-table">
				<thead class="flip-content">
					<tr>
						<th colspan="7" class="strat-table-header">CHANGE ACTIONS</th>
					</tr>
					<tr>
						<th>Effective Date</th>
						<th>Description</th>
					</tr>
				</thead>
				
				<tbody style="text-align:right;">
				<?php
				
				foreach($aStockActions['SPLIT'] as $key=>$aActions){           
					
					if($aActions['effectiveDate'] >= $posStart){
						?>
						<tr>
							<td style="text-align:left"><?php echo date('M d, Y', $aActions['effectiveDate']);?></td>
							<td><?php echo $aActions['description'];?></td>
						</tr>
						<?php
					}
							
				}
				
				?>
				</tbody>
			</table>
			<?php
			
		}//end if empty splits
		
		if(!empty($aStockActions['CASHDIVIDEND'])){
			
			
			?>
		
			<table class="table table-bordered table-striped table-condensed flip-content volatility-table">
				<thead class="flip-content">
					<tr>
						<th colspan="7" class="strat-table-header">DIVIDENDS</th>
					</tr>
					<tr>
						<th>Effective Date</th>
						<th>Record Date</th>
						<th>Pay Date</th>
						<th>Description</th>
					</tr>
				</thead>
				
				<tbody style="text-align:right;">
				<?php
				
					foreach($aStockActions['CASHDIVIDEND'] as $key=>$aActions){ 
						
						if($aActions['effectiveDate'] > $posStart){
							?>
							<tr>
								<td style="text-align:left"><?php echo date('M d, Y', $aActions['effectiveDate']);?></td>
								<td><?php echo date('M d, Y', $aActions['recordDate']);?></td>
								<td><?php echo date('M d, Y', $aActions['payDate']);?></td>
								<td><?php echo $aActions['description'];?></td>
							</tr>
							<?php
						}
					}
						
				?>
				</tbody>
			</table>
			  
			<?php
			
		}//end if empty dividends
		
	}else{
		echo '<div id="load-changeActions"></div>';	
	}
	?>    
	
	
    
      
    <?php
	
	/*echo '<pre>';
	echo $posStart.' |';
	print_r($aStockActions);
	echo '</pre>';*/
}

//+---------------------------------------------------------------------------------------------------------+
//|									GET Stock Chart Date - PROCESS 2										|
//+---------------------------------------------------------------------------------------------------------+

if($process == '2'){
	
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
		
}

//+---------------------------------------------------------------------------------------------------------+
//|										API TICKETS - PROCESS 3-1											|
//+---------------------------------------------------------------------------------------------------------+

if($process == '3-1'){
	
	$symbol 	= $_REQUEST['symbol'];
	$fundID 	= $_REQUEST['fund'];
	
	
	
	//Start function to check to see if EXTERNAL API has finished
	function get_results($mLink, $fundID, $symbol, $timeout){
		
		//Increment timeout counter by one
		$timeout = $timeout + 1;
		
		//Query DB to see if there are results for the provided date
		$query = '
			SELECT *
			FROM '.$_SESSION['fund_trades_table'].'
			WHERE fund_id=:fund_id AND stockSymbol=:stockSymbol
			ORDER BY closed DESC
		';
		
		try{
			$rsGetTrades = $mLink->prepare($query);
			$aValues = array(
				':fund_id' 		=> $fundID,
				':stockSymbol'	=> $symbol
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetTrades->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$rowCount = 0;
		while($trades = $rsGetTrades->fetch(PDO::FETCH_ASSOC)){
			$rowCount = $rowCount + 1;
			
			$timestamp = $trades['timestamp'];
			$yesterday = date('m/d/Y', time());
			$morning = mktime(6, 0, 0, date('m'), date('d'), date('Y'));
		}
		$results = '';
		//If the rowCount is zero, loop through the function until it gets a result or the timeout is reached.		
		if($rowCount == "0" && $timeout < $_SESSION['trade_popup_timeout'] || $timestamp < $morning && $timeout < $_SESSION['trade_popup_timeout']){
			sleep(1);
			$results .= get_results($mLink, $fundID, $symbol, $timeout);
		}else{
			$results .= $rowCount;
		}
		
		return $results;
	}
	
	//Set starting time out to zero
	$timeout = 0;
	
	// Run Loop function to see if Details have been written to Database
	$results = get_results($mLink, $fundID, $symbol, $timeout);	
	
	//If results are in DB Query and print results
	if($results != "0"){
	
		/*$query = "
			SELECT ft.*, sc.company_name
			FROM ".$_SESSION['fund_trades_table']." AS ft
			INNER JOIN ".$_SESSION['stock_companies_table']." as sc ON ft.company_id=sc.company_id
			WHERE fund_id=:fund_id AND stockSymbol=:symbol
			ORDER BY unix_closed ASC
		";
		*/
		$query = "
			SELECT *
			FROM ".$_SESSION['fund_trades_table']."
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
		
		$aTrades = array();
		while($trades = $rsTrades->fetch(PDO::FETCH_ASSOC)){
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
		}
		
		//+-----------------------------------------------------------------------------------------------------+
		//										START CREATE LEDGER ARRAY AND BUY/SELL ARRAYS
		//+-----------------------------------------------------------------------------------------------------+
		
		//init ledger, buy, and sell arrays
		$aLedger 	= array();
		$aBuys		= array();
		$aSells		= array();
		
		//init "start" variable
		$start = '';
		
		if(!empty($aTrades)){
		
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
			
			//Check against existing strat data, if $end does not = current shares, run the reload startification data routine. 
			$query = "
				SELECT totalShares
				FROM ".$_SESSION['fund_stratification_basic_table']."
				WHERE fund_id=:fund_id AND stockSymbol=:symbol
			";
			try{
				$rsShares = $mLink->prepare($query);
				$aValues = array(
					':fund_id'	=> $fundID,
					':symbol'	=> $symbol
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsShares->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$getShares = $rsShares->fetch(PDO::FETCH_ASSOC);
			
			$currentShares = $getShares['totalShares'];
			
			//if current shares are not equal to the ledger end shares, reload the stratification
			if($currentShares != $end){
				exec('/usr/bin/php /var/www/html/portfolio.marketocracy.com/scripts/strat-build.php "fundID='.$fundID.'" > /dev/null &');
				
				echo '<div id="get_message"></div>';
				//header('Location: '.$siteRoot.'/?page=03-01-03-001&fund='.$fundID.'&message=reload&symbol='.$symbol);
			}
		//+-----------------------------------------------------------------------------------------------------+
		//										END CREATE LEDGER ARRAY
		//+-----------------------------------------------------------------------------------------------------+
		
		
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
						<td style="text-align:left;"><a href="#global-ticket-details" data-toggle="modal" onclick="loadGlobalTradeDetails('<?php echo $ticketKey;?>');"><?php echo $date;?></a></td>
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
		}else{
			echo '<div id="load-trade-info"></div>';	
		}//end check for empty a trades
	
	}
}


//+---------------------------------------------------------------------------------------------------------+
//|									Load Ticket Details - PROCESS 3											|
//+---------------------------------------------------------------------------------------------------------+

if($process == '3'){

	$uid 	= $_REQUEST['uid'];
	$fundID	= $_REQUEST['fund'];
		
	/*$query = "
		SELECT ft.*, sc.company_name
		FROM ".$_SESSION['fund_trades_table']." AS ft
		INNER JOIN ".$_SESSION['stock_companies_table']." as sc ON ft.company_id=sc.company_id
		WHERE ft.uid=:uid
	";*/
	$query = "
		SELECT ft.*
		FROM ".$_SESSION['fund_trades_table']." AS ft
		WHERE ft.uid=:uid
	";
	
	try{
		$rsTrades = $mLink->prepare($query);
		$aValues = array(
			':uid'	=> $uid
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsTrades->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
			
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
    
    <table class="table table-bordered table-striped table-condensed flip-content volatility-table">
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
    
    <table class="table table-bordered table-striped table-condensed flip-content volatility-table">
        <thead class="flip-content">
            <tr>
            	<th class="strat-table-header">Completion</th>
            </tr>
        </thead>
        
        <tbody>
			<tr>
            	<td>This ticket was opened on <?php echo date('M d, Y', $opened);?> and completed on <?php echo date('M d, Y', $close);?>.</td>
            </tr>
        </tbody>
    </table>
    
    <table class="table table-bordered table-striped table-condensed flip-content volatility-table">
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
    <table class="table table-bordered table-striped table-condensed flip-content volatility-table">
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
    
    <?php
}

//+---------------------------------------------------------------------------------------------------------+
//|										Load Stock Info - PROCESS 4 										|
//+---------------------------------------------------------------------------------------------------------+

if($process == '4'){
	
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
<!--        	<a href="#stock-info-trade" data-toggle="modal" class="btn btn-info" onclick="loadStockTrade();"><i class="icon-random"></i> Trade Stock</a>--> <a href="#stock-info-tickets" onclick="loadStockTickets();" data-toggle="modal" class="btn btn-info"><i class="icon-"></i> Tickets</a>
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
	
	/*echo '<pre>';
	print_r($_SESSION['fundsStockTrade']);
	print_r($aStock);
	print_r($phpObj);
	echo '</pre>';	*/
}

//+---------------------------------------------------------------------------------------------------------+
//|										Load Stock Tickets - PROCESS 4-1									|
//+---------------------------------------------------------------------------------------------------------+
if($process == '4-1'){
	$aFunds 	= $_SESSION['fundsStockTrade'];
	$symbol		= $aFunds['stockInfo']['symbol'];
	$aTrades[] 	= array();
	
	//set var to determin if we need to wait for trades to load
	$checkTrades = 0;
	
	foreach($aFunds as $fundID=>$aFundInfo){
		
		if($fundID != 'stockInfo'){
			
			if($aFundInfo['trades'] != 'getTrades'){
			
				foreach($aFundInfo['trades'] as $key=>$aTrade){
				
					$aTrade['fundSymbol'] = $aFundInfo['fundSymbol'];
					
					$aTrades[] = $aTrade;
				}
			}else{
				$checkTrades = 1;
				$fundID = $fundID;		
				break;
			}
				
		}
		
	}
	
	
	
	function order_by_date($a, $b) {
	  return $b["unixdate"] - $a["unixdate"];
	}
	
	usort($aTrades, "order_by_date");
	?>
    <h4>Active Tickets for  <?php echo $aFunds['stockInfo']['symbol'];?></h4>
    <hr style="margin:0px 0px 10px 0px;" />
    <table class="table table-striped table-bordered table-hover table-full-width">
        <thead>
            <tr>
                <th>Fund</th>
                <th>Type</th>
                <th>Symbol</th>
                <th class="hidden-xs">Open Date</th>
               <?php /*?> <th class="hidden-xs">Limit</th><?php */?>
                <th class="hidden-xs">Current</th>
                <th>Shares</th>
                <?php /*?><th class="hidden-xs">Fills</th>
                <th class="hidden-xs">Avg. Paid</th><?php */?>
                <th class="hidden-xs">Order Type</th>
                <?php /*?><th class="hidden-xs">Last Filled</th><?php */?>
                <th>Status</th>
                <th>Actions</th>
                <th style="display:none;">Unix Time</th>
            </tr>
        </thead>
        <tbody>
            <?php
            
            $query = "
                SELECT *
                FROM members_fund_tickets
                WHERE member_id=:member_id AND status='pending' AND symbol=:symbol
                ORDER BY openned DESC
            ";
            
            try{
                $rsGetOrders = $mLink->prepare($query);
                $aValues = array(
                    ':member_id' 	=> $_SESSION['member_id'],
					':symbol'		=> $symbol
                );
                $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                $rsGetOrders->execute($aValues);
            }
            catch(PDOException $error){
                // Log any error
                file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
            }
            
			$resultCheck = 0;
            while($order = $rsGetOrders->fetch(PDO::FETCH_ASSOC)){
                
				$resultCheck = 1;
				
                $tradeType = $order['action'];
                switch($tradeType){
                    case "buy": $tradeType = "Buy";break;
                    case "sell": $tradeType = "Sell";break;	
                }
                
                $symbol = $order['symbol'];
                $date = $order['openned'];
                
                $price = number_format($order['price'], 2, '.', ',');
                
                $shares = $order['shares'];
                $orderType = $order['type'];
                switch($orderType){
                    case "Day": $orderType = 'Day Order';break;
                    case "GTCI": $orderType = "Good Until Canceled";break;	
                }
                
                $ticketFundID = $order['fund_id'];
                $ticketFundSymbol = get_funds($mLink, $ticketFundID, 'fundSymbol');
                
                $orderStatus = $order['status'];
                switch($orderStatus){
                    case "pending": $orderStatus = '<span class="btn btn-sm btn-info">Pending</span>';break;
                }
                
                $cancelStatus = $order['cancel_status'];
                
                if(!empty($cancelStatus)){
                    $orderStatus = '<span class="btn btn-sm btn-warning">Cancel Request Sent: <br /> '.date('M d, Y H:m', $cancelStatus).'</span>';
                    $showCancelBtn = '';	
                }else{
                    $showCancelBtn = '<a href="#fund-cancel" onclick="showCancellOrder(\''.$order['uid'].'\');" data-toggle="modal" class="btn btn-danger btn-sm">Cancel</a>';	
                }
                
                ?>
                <tr>
                    <td><?php echo $ticketFundSymbol;?></td>
                    <td><?php echo $tradeType;?></td>
                    <td><?php echo $symbol;?></td>
                    <td><?php if(isMarketOpen($date, $mLink) == false){echo '<span class="label label-warning">A.H.T.</span>';}?> <?php echo date('M d, Y g:i A', $date);?> </td>
                    <?php /*?><td></td><?php */?>
                    <td>$<?php echo $price;?></td>
                    <td><?php echo $shares;?></td>
                    <?php /*?><td>0</td>
                    <td>$0.00</td><?php */?>
                    <td><?php echo $orderType;?></td>
                    <?php /*?><td></td><?php */?>
                    <td><?php echo $orderStatus; ?></td>
                    <td><?php /*?><a href="#fund-details" data-toggle="modal" class="btn btn-info">Details</a> <?php */?><?php echo $showCancelBtn;?></td>
                    <td style="display:none;"><?php echo $date;?></td>
                </tr>
                <?php
            }
			
			if($resultCheck == 0){
				echo '
					<tr>
						<td colspan="9">No active tickets.</td>
					</tr>
				';	
			}
            ?>
            
           
        </tbody>
    </table>
    <h4 style="margin-top:20px !important;">All Tickets for <?php echo $aFunds['stockInfo']['symbol'];?></h4>
    <hr style="margin:0px 0px 10px 0px;" />
	<table class="table table-bordered table-striped table-condensed flip-content volatility-table" id="stock-info-tickets-table">
        <thead class="flip-content">
            <?php /*?><tr>
                <th colspan="9" class="strat-table-header">All Tickets for <?php echo $aFunds['stockInfo']['symbol'];?></th>
            </tr><?php */?>
            <tr>
            	<th>Date</th>
                <th>Fund</th>
            	<th>Type</th>
                
                <th>Shares</th>
                <th>Net Avg. Price</th>
                <th>Commission</th>
                <th>SEC Fee</th>
                <th>Net</th>
                <th>Details</th>
            </tr>
        </thead>
        
        <tbody style="text-align:right;">
            <?php
			//$aTrades = array_shift($aTrades);
            foreach($aTrades as $key=>$aTrade){
                $fundSymbol	= $aTrade['fundSymbol'];
				$type		= $aTrade['type'];
                $date		= date('m/d/Y', $aTrade['unixdate']);
                $shares		= number_format($aTrade['shares'], 0, '.',',');
                $commission	= '$'.number_format($aTrade['commission'], 2, '.',',');
                $secFee		= '$'.number_format($aTrade['secFee'], 2, '.', ',');
                $net 		= '$'.number_format($aTrade['net'], 2, '.',',');
                $price		= '$'.number_format($aTrade['price'], 2, '.',',');
                $uid		= $aTrade['uid'];
                $ticketKey	= $aTrade['ticketKey'];
				
				if($shares != '0'){
					if($aTrade['CA'] != '1'){
					?>
					<tr>
						<td><span style="display:none;"><?php echo $aTrade['unixdate'];?></span><?php echo $date;?></td>
						<td><?php echo $fundSymbol;?></td>
						<td><?php echo $type;?></td>
						
						<td><?php echo $shares;?></td>
						<td><?php echo $price;?></td>
						<td><?php echo $commission;?></td>
						<td><?php echo $secFee;?></td>
						<td><?php echo $net;?></td>
						
						<td><a href="#global-ticket-details" data-toggle="modal" class="btn btn-xs btn-default" onclick="loadGlobalTradeDetails('<?php echo $ticketKey;?>');">Details</a></td>
					</tr>
					<?php
					}
				}
            }
            ?>
        </tbody>
    </table>
    <?php
	
	/*echo '<pre>';
	print_r($aFunds);
	print_r($aTrades);
	echo '</pre>';	*/
}


//+---------------------------------------------------------------------------------------------------------+
//|										Load Stock Trade - PROCESS 5										|
//+---------------------------------------------------------------------------------------------------------+

if($process == '5'){
	
	$aFunds = $_SESSION['fundsStockTrade'];

    $aStock = $aFunds['stockInfo'];
	?>
    <h4>Your Holdings</h4>
    <hr style="margin:0px 0px 10px 0px;" />
    <table class="table table-bordered table-striped table-condensed flip-content volatility-table">
        <thead class="flip-content">
            <tr class="strat-table-header">
            	<th colspan="3">Your Holdings</th>
                <th colspan="2">Diversification &amp; Compliance</th>
            </tr>
            <tr>
                <th>Fund</th>
                <th>Shares</th>
                <th>Portion of Fund</th>
                <th>Shares to 10%</th>
                <th>Shares to 25%</th>
            </tr>
        </thead>
        
        <tbody style="text-align:right;">
			<?php
			foreach($aFunds as $fundID=>$aFund){
				if($fundID != 'stockInfo'){
				?>
				<tr>
					<td style="text-align:center;"><a class="btn btn-xs btn-default" title="<?php echo $aFund['fundName'];?>" style="display:block;" href="?page=03-01-03-001&fund=<?php echo $fundID;?>"><?php echo $aFund['fundSymbol'];?></a></td>
					<td><?php echo number_format($aFund['totalShares'],0,'.',',');?></td>
					<td><?php echo number_format($aFund['fundRatio'],2,'.',',');?>%</td>
                    <td><?php echo number_format($aFund['sharesTo10'],0,'.',',');?></td>
                    <td><?php echo number_format($aFund['sharesTo25'],0,'.',',');?></td>
				</tr>
                <?php
				}
			}
		?>
        </tbody>
    </table>

    <h4>Trade This Stock</h4>
    <form method="post" class="stock-info-trade-form">
    <hr style="margin:0px 0px 10px 0px;" />
    <div class="stock-info-trade-errors" id="trade-error"></div>
    
    <?php if($_SESSION['admin'] == '1'){
		echo '<pre>';
		print_r($aFunds);
		echo '</pre>';
	}?>
    <table class="table table-bordered table-striped table-condensed flip-content volatility-table">
        <thead class="flip-content">
        <tr class="strat-table-header">
            <th></th>
            <th><small>Selling Limits</small></th>
            <th colspan="2"><small>Buying Limits</small></th>
            <th></th>
        </tr>
        <tr>
            <th>Fund</th>
            <?php /* <th>Trade Type</th> */?>
            <th>Available<br />Shares</th>
            <th>Available<br />Cash</th>
            <th>Max<br />Shares</th>
            <th>Trade Shares</th>
        </tr>
        </thead>

        <tbody style="text-align:right;">
        <?php

        $cnt = 0;
        $aProcessFunds = array();

        foreach($aFunds as $fundID=>$aFund){

            $cnt++;

            if($fundID != 'stockInfo'){

                $aProcessFunds[] = $fundID;

                if($aFund['cash'] != $aFund['adjustedCash']){
                    $showAdjustCash = ' <i class="icon-exclamation-sign" style="cursor:help;color:#ECBC29;" title="Adjusted Cash: $'.number_format($aFund['adjustedCash'], 2, '.',',').' - This is your adjusted cash value based on trades that are currently pending."></i><br /><span class="label label-warning" title="This is your adjusted cash value based on trades that are currently pending.">$'.number_format($aFund['adjustedCash'], 2, '.',',').'</span>';
                    $showAdjustShares = '<i class="icon-exclamation-sign" style="cursor:help;color:#ECBC29;" title="Adjusted Max Shares: '.number_format($aFund['adjustBuyMaxShares'], 0, '.',',').' - This is your adjusted Max Share value based on trades that are currently pending."></i><br /><span class="label label-warning" title="This is your adjusted Max Share value based on trades that are currently pending.">'.number_format($aFund['adjustBuyMaxShares'], 0, '.',',').'</span>';
                }else {
                    $showAdjustCash = '';
                    $showAdjustShares = '';
                }
				
				if($aFund['sellAdjustedShares'] != $aFund['totalShares']){
					$showSellAdjustShares = '<i class="icon-exclamation-sign" style="cursor:help;color:#ECBC29;" title="Adjusted Shares: '.number_format($aFund['sellAdjustedShares'], 0, '.',',').' - This is your adjusted shares available to sell based on trades that are currently pending."></i><br /><span class="label label-warning" title="This is your adjusted shares available to sell based on trades that are currently pending.">'.number_format($aFund['sellAdjustedShares'], 0, '.',',').'</span>';	
				}else{
					$showSellAdjustShares = '';	
				}
                ?>
                <tr>
                    <td style="text-align:center;"><a class="btn btn-xs btn-default" title="<?php echo $aFund['fundName'];?>" style="display:block;" href="?page=03-01-03-001&fund=<?php echo $fundID;?>"><?php echo $aFund['fundSymbol'];?></a></td>

                    <td>
                        <?php echo number_format($aFund['totalShares'],0,'.',',');?> <?php echo $showSellAdjustShares;?>
                        <input type="hidden" name="sell-limit-shares-<?php echo $fundID;?>" value="<?php echo $aFund['sellAdjustedShares'];?>"/>
                        <input type="hidden" name="adjust-sell-limit-shares-<?php echo $fundID;?>" value="<?php echo $aFund['sellAdjustedShares'];?>" />
                    </td>
                    <td>
                        $<?php echo number_format($aFund['cash'],2,'.',',');?> <?php echo $showAdjustCash;?>
                        <input type="hidden" name="buy-limit-cash-<?php echo $fundID;?>" value="<?php echo $aFund['cash'];?>"/>
                        <input type="hidden" name="adjust-buy-limit-cash-<?php echo $fundID;?>" value="<?php echo $aFund['adjustedCash'];?>"/>
                    </td>
                    <td>
                        <?php echo number_format($aFund['buyMaxShares'],0,'.',',');?> <?php echo $showAdjustShares;?>
                        <input type="hidden" name="buy-limit-shares-<?php echo $fundID;?>" value="<?php echo $aFund['buyMaxShares'];?>"/>
                        <input type="hidden" name="adjust-buy-limit-shares-<?php echo $fundID;?>" value="<?php echo $aFund['adjustBuyMaxShares'];?>"/>
                    </td>
                    <td>
                        <input type="text" class="form-control" name="shares-total-<?php echo $fundID;?>" id="shares-total-<?php echo $fundID;?>" />
                        <input type="hidden" name="fund-symbol-<?php echo $fundID;?>" value="<?php echo $aFund['fundSymbol'];?>"/>
                        <input type="hidden" name="shares-to-25-<?php echo $fundID;?>" value="<?php echo number_format($aFund['sharesTo25'],0,'.',',');?>" />
                    </td>
                </tr>
            <?php
            }
        }
        ?>
        <tr>
            <td  colspan="3">
                <div class="form-group" style="text-align: left;">
                    <label><strong>Limit Price</strong></label>
                    <input type="text" name="limit-price" class="form-control" id="limit-price-field" />
                </div>
            </td>
            <td colspan="2">
                <div class="form-group" style="text-align: left;">
                    <label><strong>Order Type</strong></label><br />
                    <label><input type="radio" value="Day" name="order_type" checked /> Day Order</label>
                    <label><input type="radio" value="GTC" name="order_type" /> Good Until Canceled</label>
                </div>
            </td>
        </tr>
        <tr style="text-align: left;border-top:5px solid #5BC0DE;">
            <td colspan="5">
                Analyze your buy/sell decisions by selecting any &amp; all reasons that support this trade.<br /><small><span class="label label-warning">Please note: these fields are NOT required!</span></small>
            </td>
        </tr>
        <tr style="text-align: left;">
            <td colspan="3">
                <div class="checkbox-list">
                    <label>
                        <input type="checkbox" name="reasons[]" value="Good News"> Good News
                    </label>
                    <label>
                        <input type="checkbox" name="reasons[]" value="Bad News"> Bad News
                    </label>
                    <label>
                        <input type="checkbox" name="reasons[]" value="Good Price"> Good Price
                    </label>
                    <label>
                        <input type="checkbox" name="reasons[]" value="Shift in Company Focus"> Shift in Company Focus
                    </label>
                    <label>
                        <input type="checkbox" name="reasons[]" value="New Product"> New Product
                    </label>
                    <label>
                        <input type="checkbox" name="reasons[]" value="Asset Allocation"> Asset Allocation
                    </label>
                    <label>
                        <input type="checkbox" name="reasons[]" value="Rule Compliance"> Rule Compliance
                    </label>
                    <label>
                        <input type="checkbox" name="reasons[]" value="Tax Planning"> Tax Planning
                    </label>
                </div><!--checkbox-list-->
            </td>
            <td colspan="2">
                <div class="checkbox-list">
                    <label>
                        <input type="checkbox" name="reasons[]" value="Earnings Announcement"> Earnings Announcement
                    </label>
                    <label>
                        <input type="checkbox" name="reasons[]" value="Management Change"> Management Change
                    </label>
                    <label>
                        <input type="checkbox" name="reasons[]" value="Merger/Acquistion"> Merger/Acquistion
                    </label>
                    <label>
                        <input type="checkbox" name="reasons[]" value="Analyst Recommendation"> Analyst Recommendation
                    </label>
                    <label>
                        <input type="checkbox" name="reasons[]" value="Sector Looks Better"> Sector Looks Better
                    </label>
                    <label>
                        <input type="checkbox" name="reasons[]" value="Sector Looks Worse"> Sector Looks Worse
                    </label>
                    <label>
                        <input type="checkbox" name="reasons[]" value="Market Indicators"> Market Indicators
                    </label>
                    <label>
                        <input type="checkbox" name="reasons[]" value="Technical Analysis"> Technical Analysis
                    </label>
                </div><!--checkbox-list-->
            </td>
        </tr>
        <tr style="text-align: left;">
            <td colspan="5">
                <label >Detailed Reason</label>
                <textarea class="form-control" name="trade-desc" rows="3"></textarea>
            </td>
        </tr>
        <tr style="text-align:center;">
            <td colspan="5">
<!--                <button type="button" onclick="stockInfoTrade('buy');" class="btn btn-success"><i class="icon-arrow-up"></i> Buy</button>
                <button type="button" onclick="stockInfoTrade('sell');" class="btn btn-info"><i class="icon-arrow-down"></i> Sell</button>-->
            </td>
        </tr>
        </tbody>
    </table>
        <input type="hidden" name="trade-funds" value="<?php echo implode('|',$aProcessFunds);?>"/>
        <input type="hidden" name="symbol" value="<?php echo $aStock['symbol'];?>"/>
    </form>

    <?php
	/*echo '<pre>';
	print_r($aFunds);
	echo '</pre>';*/
}

//+---------------------------------------------------------------------------------------------------------+
//|								Process Stock Info Trades - PROCESS 6										|
//+---------------------------------------------------------------------------------------------------------+

if($process == '6') {



    $type           = $_REQUEST['type'];
    $username       = $_SESSION['username'];
    $aProcessFunds  = explode('|',$_REQUEST['trade-funds']);
    $limitPrice     = $_REQUEST['limit-price'];
    $orderType      = $_REQUEST['order_type'];
    $reasons        = $_REQUEST['reasons'];
    $tradeDesc      = $_REQUEST['trade-desc'];
    $symbol         = $_REQUEST['symbol'];
    $acceptCash     = $_REQUEST['acceptCash'];
    $accept25Error  = $_REQUEST['accept25'];

    if($_SESSION['acceptCash'] == '' && $acceptCash == '1'){
        $_SESSION['acceptCash'] = '1';
    }

    if($_SESSION['accept25'] == '' && $accept25Error == '1') {
        $_SESSION['accept25'] = '1';
    }

    $aTrades        = array();
    $aErrors        = array();

    if ($limitPrice != '') {
        if (!preg_match('/^[0-9].+$/', $limitPrice)) {
            $aErrors[] = 'You can only enter numbers in the limit price field.';
        }
    }

    foreach($aProcessFunds as $key=>$fundID) {

        $shares             = $_REQUEST['shares-total-' . $fundID];
		$adjustShares		= $_REQUEST['adjust-sell-limit-shares-' . $fundID];
        $fundSymbol         = $_REQUEST['fund-symbol-' . $fundID];
        $sellLimitShares    = $_REQUEST['sell-limit-shares-' . $fundID];
        $buyLimitCash       = $_REQUEST['buy-limit-cash-' . $fundID];
        $buyLimitShares     = $_REQUEST['buy-limit-shares-' . $fundID];
        $adjustCash         = $_REQUEST['adjust-buy-limit-cash-' . $fundID];
        $adjustShares       = $_REQUEST['adjust-buy-limit-shares-' . $fundID];
        $sharesTo25         = $_REQUEST['shares-to-25-'.$fundID];



        if ($shares != '') {
            if (!preg_match('/^[0-9]+$/', $shares)) {
                $aErrors[] = 'Fund: ' . $fundSymbol . ' - You can only enter numbers in the shares field.';
            }
        }

        if($shares > 0) {
            $aTrades[$fundID] = array(
                'username'          => $username,
                'fundSymbol'        => $fundSymbol,
                'fundID'            => $fundID,
                'tradeType'         => $type,
                'orderType'         => $orderType,
                'symbol'            => $symbol,
                'shares'            => $shares,
                'reasons'           => $reasons,
                'tradeDesc'         => $tradeDesc,
                'limitPrice'        => $limitPrice,
                'sellLimitShares'   => $sellLimitShares,
                'buyLimitCash'      => $buyLimitCash,
                'buyLimitShares'    => $buyLimitShares,
                'adjustCash'        => $adjustCash,
                'adjustShares'      => $adjustShares,
                'sharesTo25'        => $sharesTo25
            );
        }

    }



    switch($type){

        case 'buy':

            foreach($aTrades as $fundID=>$aTrade) {

                $buyLimit       = $aTrade['buyLimitShares'];
                $shares         = strToInt($aTrade['shares']);
                $cash           = $aTrade['buyLimitCash'];
                $adjustCash     = $aTrade['adjustCash'];
                $adjustShares   = $aTrade['adjustShares'];
                $sharesTo25     = strToInt($aTrade['sharesTo25']);


                //check to see if they previously accepted the warning
                if($_SESSION['acceptCash'] != '1') {
                    //make sure shares are less than or equal to the buy limit of shares
                    if ($shares <= $buyLimit) {
                        //check to see if shares are great than the adjusted amount of shares, if so display warning
                        if ($shares > $adjustShares) {
                            //only show warning if they havnt accepted the warning
                            if ($acceptCash != '1') {
                                $aErrors[] = 'Fund: ' . $aTrade['fundSymbol'] . ' - You are about to make a trade for which the cash value is more than your adjusted cash value! Check the box below to accept and continue this trade.<br /><br />Your adjusted cash value is your Avaliable Cash minus the cash on hold for trades that are currently pending. Current Adjusted Cash: <strong>$' . number_format($adjustCash, 2, '.', ',') . '</strong><br /><br /> <label><input type="checkbox" name="acceptCash" value="1" /> <strong>I accept that this trade could take my fund to a negative cash position, and I will be responsible for bringing my fund to a positive balance in the event that my fund goes negative, as a result of this trade.</strong>';
                            }
                        }
                    }
                }

                if($shares > $buyLimit){
                    $aErrors[] = $aTrade['fundSymbol'].': You do not have enough available cash to purchase more than '.$buyLimit.' shares.';
                }else{

                    if ($_SESSION['accept25'] != '1'){
                        if ($shares > $sharesTo25) {
                            if ($accept25Error != '1') {
                                $aErrors[] = 'Fund: ' . $aTrade['fundSymbol'] . ' - This trade will make this position exceed 25% of your overall fund, breaking fund compliance, resulting in disqualification for rankings! Check the box below to accept and continue this trade, or make sure the ammount of shares is below: <strong>' . $sharesTo25 . ' shares</strong>'.$shares.'.<br /><br /><label><input type="checkbox" name="accept25" value="1" /> <strong>I accept that this trade will make this position exceed the 25% rule. (This is NOT recommended)</strong>';
                            }
                        }
                    }

                }

            }

        break;

        case 'sell':

            foreach($aTrades as $fundID=>$aTrade){

                $sellLimit  = $aTrade['sellLimitShares'];
                $shares     = $aTrade['shares'];

                if($shares > $sellLimit){
                    $aErrors[] = $aTrade['fundSymbol'].': You can not sell more shares than you own. Note, you may have sells that are currently pending which will affect the number of shares you can sell at this time.';
                }

            }

        break;
    }

    if(empty($aErrors)){

        foreach($aTrades as $fundID=>$aTrade) {

            $username       = $aTrade['username'];
            $fundSymbol     = $aTrade['fundSymbol'];
            $tradeType      = $aTrade['tradeType'];
            $orderType      = $aTrade['orderType'];
            $symbol         = $aTrade['symbol'];
            $shares         = $aTrade['shares'];
            $reasons        = implode('~',$aTrade['reasons']);
            $tradeDesc      = $aTrade['tradeDesc'];
            $limitPrice     = $aTrade['limitPrice'];

            //Create query
            $query = 'create||' . $username . '|' . $fundSymbol . '|' . $fundID . '|' . $tradeType . '|' . $orderType . '|' . $symbol . '|' . $shares . '|' . $limitPrice . '|' . $reasons . '|' . $tradeDesc . '|0';
			
			$port = $setPort;
			
            //Execute Query
            include ('../../includes/data-query-ecn.php');

            $event = 'STOCK ORDER : create';
            $detail = $_SESSION['username'].':'.$query;
            addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
            //echo $query;

            //clear session vars
            $_SESSION['accept25'] = '';
            $_SESSION['acceptCash'] = '';
        }

    }else{

        echo '<div class="note note-danger"><ul>';
        foreach($aErrors as $key=>$error){
            echo '<li>'.$error.'</li>';
        }
        echo '</ul></div>';

    }

    /*echo '<pre>';
    print_r($aTrades);
    print_r($aProcessFunds);
    echo '</pre>';*/
}

if($process == 'csv'){
	
	$fundID 		= $_REQUEST['fund'];
	$allPositions	= $_REQUEST['pos'];
	$sortOrder		= $_REQUEST['sort'];
	
	$fundSymbol = get_funds($mLink,$fundID,'fundSymbol');
	
	if($allPositions == '0'){
		$sort = "AND totalShares>'0'";
		
		//echo $sort;	
	}
	
	switch($sortOrder){
		case 'symbol'	: $orderBy = 'stockSymbol ASC';break;
		case 'price'	: $orderBy = 'currentPrice DESC';break;
		case 'shares'	: $orderBy = 'totalShares DESC';break;
		case 'value'	: $orderBy = 'currentValue DESC';break;
		case 'percent'	: $orderBy = 'fundRatio DESC';break;
		case 'gains'	: $orderBy = 'gains DESC';break;
		case 'today'	: $orderBy = 'todayReturn DESC';break;
		case 'incept'	: $orderBy = 'totalReturn DESC';break;
		case 'current'	: $orderBy = 'recentReturn DESC';break;
		default			: $orderBy = 'totalReturn DESC';break;
	}
	
	$query = "
		SELECT * 
		FROM ".$_SESSION['fund_stratification_basic_table']." 
		WHERE fund_id='".$fundID."' ".$sort."
		ORDER BY ".$orderBy."
	";
	try{
		$rsPos = $mLink->prepare($query);
		$aValues = array(
			//':fund_id'	=> $fundID
			//':sort'		=> $setSort
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsPos->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	//echo $preparedQuery;
	
	//Reset variables
	$cnt = 0;
	
	//Initialize Arrays
	$aStrat = array();
		
	//loop through result
	while($position = $rsPos->fetch(PDO::FETCH_ASSOC)){

		//Set Vars
		$symbol	= $position['stockSymbol'];
		$price 	= $position['currentPrice'];
		$shares = $position['totalShares'];
		$value 	= $position['currentValue'];
		$ratio 	= $position['fundRatio'];
		$gains	= $position['gains'];
		$today	= $position['todayReturn'];
		$return = $position['totalReturn'];
		
		
		//get all
		//Increase counter
		$cnt = $cnt + 1;
	
		if($shares == '0'){
			$currentReturn = '0.00';	
		}else{
			$currentReturn = $return;	
		}
		
		//Create Array to work with so we dont have to keep going back to the database
		$aStrat[$symbol] = array(
			'symbol'		=> $symbol,
			'price'			=> $price,
			'shares'		=> $shares,
			'value'			=> $value,
			'ratio'			=> $ratio,
			'gains'			=> $gains,
			'today'			=> $today,
			'return'		=> $return,
			'currentReturn'	=> $currentReturn,
			'cnt'			=> $cnt,
		);	
		
	
	$asOfDate = date('m/d/Y', $position['timestamp']);
	
	} //end while loop
	
	
	//Get array of current holdings price and today values
	$aStockList = array();
	foreach($aStrat as $key=>$stock){
			$aStockList[] = "'".$stock['symbol']."'";	
	}
	$stockList = implode(',',$aStockList);

	$query = "
			SELECT Name AS companyName, Symbol AS symbol, Last AS CurrentPrice, PercentChangeFromPreviousClose AS chang
			FROM `stock_feed`
			WHERE `Symbol` IN (".$stockList.")
	";
	try{
			$rsSymbols = $fLink->prepare($query);
			$aValues = array();
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsSymbols->execute($aValues);
	}
	catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$aStockInfo = array();
	while($foo = $rsSymbols->fetch(PDO::FETCH_ASSOC)){
			$aStockInfo[$foo['symbol']] = array(
					'stockPrice'	=> $foo['CurrentPrice'],
					'today'			=> $foo['chang']
			);
	}
	
	$stratCSV[] = array(
		'Index',
		'Symbol',
		//'Label',
		'Price',
		'Shares',
		'Value',
		'Gains',
		'Today',
		'Inception',
		'Current Return',
		'First Trade',
		'Last Trade',
		'Total Buys',
		'Total Sells',
		'P/E',
		'P/S',
		'PSG',
		//'PEG',
		'EPS',
		'Style',
		'Sector',
		'Sub Sector',
		//'Breakeven',
		//'Days Held',
		'MTD Return',
		'QTD Return',
		'YTD Return',
		//'Notes',
		'Yield',
		//'Selection',
		//'Activity'
	);
	
	$stratCnt = 0;
	foreach($aStrat as $stockSymbol=>$stock){
		
		$query = "
				SELECT *
				FROM ".$_SESSION['fund_positions_details_table']."
				WHERE fund_id=:fund_id AND stockSymbol=:stockSymbol
				ORDER BY timestamp DESC
				LIMIT 1
		";
		try{
				$rsPosInfo = $mLink->prepare($query);
				$aValues = array(
						':fund_id'	=> $fundID,
						':stockSymbol'	=> $stockSymbol
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsPosInfo->execute($aValues);
		}
		catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$posInfo = $rsPosInfo->fetch(PDO::FETCH_ASSOC);

		//echo $error;

		#CA info
		$caYield 		= $posInfo['caYield'];
		$caCost			= $posInfo['caCost'];
		$recentCAYield	= $posInfo['recentCAYield'];
		$recentCACost	= $posInfo['recentCACost'];

		#return Info
		$recentReturn	= $posInfo['recentReturn'];
		$totalReturn	= $posInfo['totalReturn'];

		#sector style info
		$sector         = $posInfo['sectorBaseName'];
		$style          = $posInfo['style'];
		$subIndustry    = $posInfo['subIndustryBaseName'];
		
		
		
		$stratCnt++;
		
		if($stock['shares'] > 0){ 
			$currentReturn = $stock['currentReturn'];
		}else{
			$currentReturn = '0.00';	
		}
		
		$stratCSV[] = array(
			$stratCnt,
			$stockSymbol,
			//'', //Label
			$aStockInfo[$stockSymbol]['stockPrice'],
			$stock['shares'],
			($stock['shares'] * $aStockInfo[$stockSymbol]['stockPrice']),
			$stock['gains'],
			$aStockInfo[$stockSymbol]['today'],
			$stock['currentReturn'],
			$currentReturn,
			date('m/d/Y', $posInfo['first_trade_unix_date']),
			date('m/d/Y', $posInfo['last_trade_unix_date']),
			$posInfo['totalUserBuys'],
			$posInfo['totalUserSells'],
			$posInfo['signedPERatio'],
			$posInfo['psRatio'],
			$posInfo['salesGrowthRatio'],
			//'', //PEG
			$posInfo['eps'],
			$posInfo['style'],
			$posInfo['sectorBaseName'],
			$posInfo['subIndustryBaseName'],
			//'', //Breakeven
			//'', //Days Held
			$posInfo['mtdReturn'],
			$posInfo['qtdReturn'],
			$posInfo['ytdReturn'],
			//'', //Notes
			$posInfo['caReturn'],
			//'', //Selection,
			//'' //Activity
		);
	
	}
	
	/*echo '<pre>';							
	print_r($stratCSV);
	echo '</pre>';*/
	query_to_csv($stratCSV, $_SESSION['username'].'-'.$fundSymbol."_Stratification_".date('Y.m.d-h.i').".csv", true);
	
	
	
}
?>
