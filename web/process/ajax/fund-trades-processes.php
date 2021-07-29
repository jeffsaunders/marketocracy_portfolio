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

$displayQuery = '0'; //1 = show query, 0 = hide query

//+---------------------------------------------------------------------------------------------------------+
//|									Reload Ledger Details - PROCESS 1										|
//+---------------------------------------------------------------------------------------------------------+

if($process == "1"){
	
	//Set Vars from URL
	$fundID		= $_REQUEST['fund'];
	$symbol		= $_REQUEST['symbol'];
		
	//Break down fund ID to get member ID
	$fundArray = explode('-', $fundID);
	$memberID = $fundArray[0];
	
	//Get Username for use in OLd SYSTEM API
	//$username = get_member($mLink, $memberID, 'username');
	
	$username = $_SESSION['username'];
	
	//GET Fund ID for use in OLD SYSTEM API
	$fundSymbol = get_funds($mLink, $fundID, 'fundSymbol');
	
	//STARET GET LEDGER INFO
	$query = "
		SELECT *
		FROM ".$_SESSION['fund_pricing_table']."
		WHERE fund_id=:fund_id AND date=:date
		ORDER BY date DESC, timestamp DESC
		LIMIT 1
	";
	
	try{
		$rsLedger = $mLink->prepare($query);
		$aValues = array(
			':fund_id'	=> $fundID,
			':date'		=> $queryDate
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsLedger->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$ledger = $rsLedger->fetch(PDO::FETCH_ASSOC);
	
	$totalValue = $ledger['totalValue'];
	//END GET LEDGER INFO
	
	//START GET POSITIONS
	
	// Start by getting a row count to determin if details exist for the selected date
	$query = '
		SELECT trades.*, company.company_name
		FROM '.$_SESSION['fund_trades_table'].' AS trades
		INNER JOIN '.$_SESSION['stock_companies_table'].' AS company ON trades.company_id=company.company_id
		WHERE trades.fund_id=:fund_id AND trades.stockSymbol=:symbol
		ORDER BY trades.closed DESC
	';
	
	try{
		$rsGetTrades = $mLink->prepare($query);
		$aValues = array(
			':fund_id' 		=> $fundID,
			':symbol'		=> $symbol
			
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
		
		$company = $trades['company_name'];
	}
	$showQuery = 'tradesForPosition|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$symbol.'';
	
	//if rowCount is equal to zero, or record is old, run fetch query OLD SYSTEM API
	if($rowCount == "0" || $timestamp < $morning){
		
		// Build Query to pass to EXTERNAL API
		$aMethodVars[] = array(
			'method'		=> 'tradesForPosition',
			'source'		=> 'Reload Ledger Details | fund-trades-processes.php | process: 1',
			'api'			=> '1',
			'username'		=> $username,
			'fund_id'		=> $fundID,
			'fund_symbol'	=> $fundSymbol,
			'stock_symbol'  => $symbol
		);
		$mlaResults = legacy_api($mLink, $aMethodVars, true);
		
		/*$query = 'tradesForPosition|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$symbol.'';
		
		//Execute Query
		include ('../../includes/data-query-legacy.php');*/
	}
	
	$showQuery = '<tr><td colspan="9">'.$showQuery.' - '.$error.' - '.$_SESSION['fund_trades_table'].' - '.$rowCount.'</th></tr>';
	
	if($displayQuery == "1"){
		$displayQuery1 = $showQuery;
	}else{
		$displayQuery1 = '';	
	}
	
	
	
	//Display Table header info
	echo '
			<thead class="flip-content">
			<tr>
				<th class="fzt-organge" colspan="8">Trades for <span class="btn btn-default btn-xs">'.$symbol.' ('.$company.')</span></th>
			</tr>
			'.$displayQuery1.'
			<tr class="fzt-blue">
				<th>&nbsp;</th>
				<th class="fzt-aleft">Type</th>
				<th class="hidden-xs">Close Date</th>
				<th>Quantity</th>
				<th>Price</th>
				<th>Net</th>
				<th>Commission</th>
				<th>SEC Fee</th>
			</tr>
		</thead>
		<tbody>
	';
	
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
	
	//echo '<tr><td>'.$results.'</td></tr>';
		
	
	//If results are in DB Query and print results
	if($results != "0"){
	
		$query1 = '
			SELECT *
			FROM '.$_SESSION['fund_trades_table'].'
			WHERE fund_id=:fund_id AND stockSymbol=:stockSymbol
			ORDER BY closed DESC
		';
		
		try{
			$rsGetTrades = $mLink->prepare($query1);
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
		
		$cnt = 0;			
		while($trade = $rsGetTrades->fetch(PDO::FETCH_ASSOC)){
			
			$tradeType 		= $trade['buyOrSell'];
			switch($tradeType){
				case 'Sell'	: $tradeType = '<span class="label label-danger"><i class="icon-arrow-down"></i> '.$tradeType.'</span>';break;
				case 'Buy'	: $tradeType = '<span class="label label-success"><i class="icon-arrow-up"></i> '.$tradeType.'</span>';break;	
			}
			
			if($trade['sharesFilled'] == '0'){
				$quantity 	= $trade['shares'];	
			}else{
				$quantity	= $trade['sharesFilled'];	
			}
			
			$closeDate		= date('m/d/Y', $trade['unix_closed']);
			//$quantity		= $trade['shares'];
			$price			= '$'.number_format($trade['price'], 2, '.', ',');
			$net			= '$'.number_format($trade['net'], 2, '.', ',');
			$commission		= '$'.number_format($trade['commission'], 2, '.', ',');
			$secFee			= '$'.number_format($trade['secFee'], 2, '.', ',');
			
			
			$cnt = $cnt + 1;
			?>
			<tr>
            	<td><?php echo $cnt;?></td>
				<td><?php echo $tradeType;?></td>
				<td><?php echo $closeDate;?></td>
				<td><?php echo $quantity;?></td>
				<td><?php echo $price;?></td>
				<td><?php echo $net;?></td>
                <td><?php echo $commission;?></td>
                <td><?php echo $secFee;?></td>
                <td><a href="#global-ticket-details" data-toggle="modal" onclick="loadGlobalTradeDetails('<?php echo $trade['ticketKey'];?>')" class="btn btn-xs btn-info">Details</a></td>
			</tr>
			<?php
		}
		
		
	}else{
		//If no results found print message
		echo '
			<tr>
				<td colspan="8">No Data Avaliable | '.$rowCount.' | '.$query.'</td>
			</tr>
		';			
	}
	
	//Close table body
	echo '</tbody>';
	//END GET POSITIONS

	
}


if($process == 'show-trade-info'){
	
	$key 	= $_REQUEST['key'];
	
	$trades = $_SESSION['trades-history'][$key];
	
	$fundID				= $_REQUEST['fund'];
	
	$stockSymbol		= $trades['stockSymbol'];
	$companyName		= $trades['companyName'];
	$opened				= $trades['opened'];
	$close				= $trades['closed'];
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
    <h4><span style="float:left;">Ticket Details for <?php echo $companyName;?></span> <span style="float:right;"><small>Status: <span class="success"><?php echo $status;?></span></small></span> </h4>
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

//+--------------------------------------------------------------------------------------------------------+
//|												CSV Process
//+--------------------------------------------------------------------------------------------------------+

if($process == "csv"){
	
	$fundID 	= $_SESSION['csv_fund'];
	$aTrades	= $_SESSION['csv_trades'];
	
	$fundSymbol = get_funds($mLink,$fundID,'fundSymbol');
	
	
	
	//create CSV header
	$aTradesCSV[] = array(
		'index', 'Symbol', 'Company Name', 'Open Date','Close Date','Shares Ordered','Shares Filled','Price','Limit','Day Or GTC','Created By CA','NET','SEC Fee','Commission','Buy or Sell', 'Reasons', 'Description'
	);
	
	$tradeCnt = 0;
	
	foreach($aTrades as $key=>$aTrade){
		
		$tradeCnt++;
		
		$aTradesCSV[] = array(
			$tradeCnt,
			$aTrade['stockSymbol'],
			$aTrade['companyName'],
			date('m/d/y',$aTrade['opened']),
			date('m/d/y',$aTrade['closed']),
			$aTrade['sharesOrdered'],
			$aTrade['sharesFilled'],
			$aTrade['price'],
			$aTrade['limit'],
			$aTrade['dayOrGTC'],
			($aTrade['createdByCA'] == '1' ? "Yes" : "No"),
			$aTrade['net'],
			$aTrade['secFee'],
			$aTrade['commission'],
			$aTrade['buyOrSell'],
			$aTrade['reasons2'],
			$aTrade['description']			
		);
		
	}
	
    // output as an attachment
    query_to_csv($aTradesCSV, $_SESSION['username'].'-'.$fundSymbol."_".date('Y.m.d-h.i').".csv", true);
	
	/*echo '<pre>';
	print_r($aCSV);
	echo '</pre>';*/
}

?>

