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

$displayQuery = '1'; //1 = show query, 0 = hide query


$api1 		= rand(52000,52099);
$api2		= rand(52100, 52499);
$apiPort	= $api2;
$timeout	= 30; //timeout loop in seconds

//+---------------------------------------------------------------------------------------------------------+
//|								Load Ledger Details - NEW METHOD											|
//+---------------------------------------------------------------------------------------------------------+
if($process == 'load-details'){
	
	$fundID		= $_REQUEST['fund'];
	$unixDate	= $_REQUEST['date'];
	$fundSymbol	= get_funds($mLink, $fundID, 'fundSymbol');
	
	//START | check so see if we have an update price record
	$query = "
		SELECT *
		FROM ".$_SESSION['fund_pricing_table']."
		WHERE fund_id=:fund_id AND date=:date
	";
	try{
		$rsPricing = $mLink->prepare($query);
		$aValues = array(
			':fund_id'	=> $fundID,
			':date'		=> date('Ymd',$unixDate)
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsPricing->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$pricing = $rsPricing->fetch(PDO::FETCH_ASSOC);
	
	$priceRecordDate 	= date('Ymd',$pricing['timestamp']);
	$today			= date('Ymd');
	$date			= date('Ymd', $unixDate);
	$reloadRow		= false;
	
	if($priceRecordDate != $today){
		#record date does not match today, run new price run query for date in question		
		
		#submit priceRun API call 
		/*$query = 'priceRun|'.$_SESSION['username'].'|'.$fundID.'|'.$fundSymbol.'|'.$date;
		
		include ('../../includes/data-query-legacy.php');*/
		
		$aMethodVars[] = array(
			'method'		=> 'priceRun',
			'source'		=> 'Fund Ledger | '.__FILE__.' | process: load-details | line: '.__LINE__,
			'api'			=> '1',
			'username'		=> $_SESSION['username'],
			'fund_id'		=> $fundID,
			'fund_symbol'	=> $fundSymbol,
			'start_date'	=> $date,
			'end_date'		=> $date
		);
		
		$mlaResults = legacy_api($mLink, $aMethodVars, true);
	}
	//END | check so see if we have an update price record
	
	//START | Check to see if we have up to date details records
	$query = '
		SELECT *
		FROM '.$_SESSION['fund_positions_table'].'
		WHERE fund_id=:fund_id AND date=:date
		ORDER BY value DESC
	';
	
	try{
		$rsGetPos = $mLink->prepare($query);
		$aValues = array(
			':fund_id' 		=> $fundID,
			':date'			=> date('Ymd',$unixDate)
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetPos->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aPos	= array();
	
	while($pos = $rsGetPos->fetch(PDO::FETCH_ASSOC)){
		
		$posRecordDate = $pos['timestamp'];
		
		$aPos[$pos['stockSymbol']] = array(
			'name'		=> $pos['name'],
			'shares'	=> $pos['shares'],
			'dividends'	=> $pos['dividends'],
			'value'		=> $pos['value'],
			'ratio'		=> ($pos['ratio'] * 100),
			'price'		=> $pos['price']
		);
		
	}
	
	#check to see if the position record date matches today
	if($posRecordDate != $today){
		//doesnt match today, run positions query
		//$query = 'positionDetail|'.$_SESSION['username'].'|'.$fundID.'|'.$fundSymbol.'|'.date('Ymd',$unixDate);
		
		//include ('../../includes/data-query-legacy.php');
		
		$aMethodVars[] = array(
			'method'		=> 'positionDetail',
			'source'		=> 'Load Ledger Details | fund-ledger-processes.php | process: load-details',
			'api'			=> '1',
			'username'		=> $username,
			'fund_id'		=> $fundID,
			'fund_symbol'	=> $fundSymbol,
			'from_date'		=> date('Ymd',$unixDate)
		);
		
		$mlaResults = legacy_api($mLink, $aMethodVars, true);	
	}
	//END | Check to see if we have up to date details records
	
	
	//START | check to see if we need to loop check for the new record
	if($priceRecordDate != $today){
		#record date does not match today, run new price run query for date in question		
		
		$timeoutLoop 	= true;
		$timeoutCnt		= 0;
		
		
		//loop through unitl the new record comes in or it timesout for the set timeout period
		while($timeoutLoop){
			//sleep 1 second
			sleep(1);
			
			//echo 'timeout count: '.$timeoutCnt.'<br />';
			
			$query = "
				SELECT *
				FROM ".$_SESSION['fund_pricing_table']."
				WHERE fund_id=:fund_id AND unix_date=:date
			";
			try{
				$rsPricing = $mLink->prepare($query);
				$aValues = array(
					':fund_id'	=> $fundID,
					':date'		=> $unixDate
					
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsPricing->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$pricing = $rsPricing->fetch(PDO::FETCH_ASSOC);
			
			$priceRecordDate = date('Ymd',$pricing['timestamp']);
			
			if($priceRecordDate == $today || $timeoutCnt >= $timeout){
				$timeoutLoop 	= false;
				$reloadRow 		= true;
			}else{
				$timeoutCnt++;	
			}
			
		}#end while loop
		
		//echo $priceRecordDate;
		
		
	}
	//END | check to see if we need to loop check for the new record
	
	//START | loop check for new position records
	if($posRecordDate != $today){
		
		$posTimeout 	= true;
		$posTimeoutCnt	= 0;
		
		while($posTimeout){
			
			
			$query = '
				SELECT *
				FROM '.$_SESSION['fund_positions_table'].'
				WHERE fund_id=:fund_id AND date=:date
				ORDER BY value DESC
			';
			
			try{
				$rsGetPos = $mLink->prepare($query);
				$aValues = array(
					':fund_id' 		=> $fundID,
					':date'			=> date('Ymd',$unixDate)
					
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetPos->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$aPos	= array();
			
			while($pos = $rsGetPos->fetch(PDO::FETCH_ASSOC)){
				
				$posRecordDate = $pos['timestamp'];
				
				$aPos[$pos['stockSymbol']] = array(
					'name'		=> $pos['name'],
					'shares'	=> $pos['shares'],
					'dividends'	=> $pos['dividends'],
					'value'		=> $pos['value'],
					'ratio'		=> ($pos['ratio'] * 100),
					'price'		=> $pos['price']
				);
				
			}
			
			if($posRecordDate == $today || $posTimeoutCnt >= $timeout){
				$posTimeout 	= false;
			}else{
				$posTimeoutCnt++;	
			}
				
		}#end posTimeout loop
		
	}
	//END | loop check for new position records
	
	//START | Get trades for date
	$query = "
		SELECT *
		FROM ".$_SESSION['fund_trades_table']."
		WHERE fund_id=:fund_id AND closed=:closed
	";
		
	try{
		$rsTrades = $mLink->prepare($query);
		$aValues = array(
			':fund_id' 		=> $fundID,
			':closed'		=> date('Ymd',$unixDate)
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsTrades->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	//echo $preparedQuery;
	$aTrades	= array();
	
	
	while($trade = $rsTrades->fetch(PDO::FETCH_ASSOC)){
		
		$aTrades[$trade['uid']] = array(
			'tradeType'		=> $trade['buyOrSell'],
			'stockSymbol'	=> $trade['stockSymbol'],
			'sharesFilled'	=> $trade['sharesFilled'],
			'price'			=> $trade['price'],
			'net'			=> $trade['net'],
			'commission'	=> $trade['commission'],
			'secFee'		=> $trade['secFee']
		);
		
	}
	//END | Get Trades for date
	
	
	$query = '
		SELECT *
		FROM '.$_SESSION['fund_positions_table'].'
		WHERE fund_id=:fund_id AND date=:date
		ORDER BY value DESC
	';
	
	try{
		$rsGetPos = $mLink->prepare($query);
		$aValues = array(
			':fund_id' 		=> $fundID,
			':date'			=> date('Ymd',$unixDate)
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetPos->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aPos	= array();
	
	while($pos = $rsGetPos->fetch(PDO::FETCH_ASSOC)){
		
		$posRecordDate = $pos['timestamp'];
		
		$aPos[$pos['stockSymbol']] = array(
			'name'		=> $pos['name'],
			'shares'	=> $pos['shares'],
			'dividends'	=> $pos['dividends'],
			'value'		=> $pos['value'],
			'ratio'		=> ($pos['ratio'] * 100),
			'price'		=> $pos['price']
		);
		
	}
	
	//START | Display Data
	?>
    <div class="note note-warning">
        <p><strong>NOTE:</strong> These details pertain to <?php echo date('F d, Y', $unixDate);?></p>
    </div><!--note-warning-->
    
    <table id="fund-zone-table" class="table table-bordered table-striped table-condensed flip-content load-ledger-info">
        <thead class="flip-content">
            <tr>
                <th><strong>Cash:</strong> <span class="label label-info">$<?php echo number_format($pricing['cashValue'], 2, '.', ',');?></span></th>
                <th><strong>Stock:</strong> <span class="label label-default">$<?php echo number_format($pricing['positionsValue'], 2, '.', ',');?></span></th>
                <th><strong>Total:</strong> <span class="label label-warning">$<?php echo number_format($pricing['totalValue'], 2, '.', ',');?></span></th>
                <th><strong>Shares:</strong> $<?php echo number_format($pricing['shares'], 0, '.', ',');?></th>
                <th><strong>Price:</strong> <span class="label label-success">$<?php echo number_format($pricing['price'], 2, '.', ',');?></span></th>
            </tr>
        </thead>            
    </table>
    
    <table id="fund-zone-table" class="table table-bordered table-striped table-condensed flip-content">
        <thead class="flip-content">
            <tr>
                <th class="fzt-organge" colspan="8">POSITIONS ON <?php echo date('F d, Y', $unixDate);?></th>
            </tr>
            <tr class="fzt-blue">
                <th class="fzt-aleft">SYMBOL</th>
                <th class="hidden-xs">NAME</th>
                <th>PRICE</th>
                <th>PORTION OF FUND</th>
                <th>SHARES HELD</th>
                <th>DIVIDENDS PAID</th>
                <th>VALUE</th>
                <th>ACTION</th>
            </tr>
        </thead>
        <tbody>
            <?php
			
			$posTotalValue	= 0;
			$ratioTotal		= 0;
			$sharesTotal 	= 0;
			$dividendTotal	= 0;
			
			foreach($aPos as $stockSymbol=>$aStockInfo){
				
			
				if($stockSymbol != ''){
					
					$posTotalValue 	= $posTotalValue + $aStockInfo['value'];
					$ratioTotal		= $ratioTotal + $aStockInfo['ratio'];
					$sharesTotal	= $sharesTotal + $aStockInfo['shares'];
					$dividendTotal	= $dividendTotal + $aStockInfo['dividends'];
					
					?>
					<tr>
						<td><a href="#global-stock-info" data-toggle="modal" onclick="loadGlobalStockInfo('<?php echo $stockSymbol;?>');"><?php echo $stockSymbol;?></a></td>
						<td class="hidden-xs"><?php echo $aStockInfo['name'];?></td>
						<td style="text-align:right;"><span class="label label-success">$<?php echo $aStockInfo['price'];?></span></td>
						<td style="text-align:right;"><?php echo number_format($aStockInfo['ratio'], 2, '.',',');?>%</td>
						<td style="text-align:right;"><?php echo $aStockInfo['shares'];?></td>
						<td style="text-align:right;">$<?php echo number_format($aStockInfo['dividends'], 2, '.', ',');?></td>
						<td style="text-align:right;">$<?php echo number_format($aStockInfo['value'], 2, '.', ',');?></td>
						<td style="text-align:center;">
                        	<a href="#global-pos-details" data-toggle="modal" class="btn btn-info btn-xs" onclick="loadPosDetails('<?php echo $fundID;?>','<?php echo $stockSymbol;?>');">Details</a>
                        </td>
					</tr>
					<?php
					
				}
			}
			?>
            <tr style="border-top:5px solid #006DA4;">
				<td colspan="2"></td>
				<td style="text-align:right;"><strong>Totals:</strong></td>
				<td style="text-align:right;"><strong><?php echo number_format($ratioTotal, 2, '.',',');?>%</strong></td>
                <td style="text-align:right;"><strong><?php echo number_format($sharesTotal, 0, '.',',');?></strong></td>
                <td style="text-align:right;"><strong>$<?php echo number_format($dividendtotal, 2, '.',',');?></strong></td>
                <td style="text-align:right;"><strong>$<?php echo number_format($posTotalValue, 2, '.',',');?></strong></td>
                <td></td>
			</tr>
        </tbody>
    </table>
    <br /><br >
    <table id="fund-zone-table" class="table table-bordered table-striped table-condensed flip-content">
    	<thead class="flip-content">
			<tr>
				<th class="fzt-organge" colspan="7">TRADES ON <?php echo date('F d, Y', $unixDate);?></th>
			</tr>
			<tr class="fzt-blue">
				<th>TYPE</th>
				<th class="fzt-aleft">SYMBOL</th>
				<th>QUANTITY</th>
				<th>PRICE</th>
				<th>NET</th>
				<th>COMMISSION</th>
				<th>SEC FEE</th>
			</tr>
		</thead>
		<tbody>
       		<?php
			
			
			foreach($aTrades as $uid=>$aTrade){
				
				?>
                <tr>
                    <th><a href="javascript:void();"><?php echo $aTrade['tradeType'];?></a></th>
                    <th><?php echo $aTrade['stockSymbol'];?></th>
                    <th><?php echo $aTrade['sharesFilled'];?></th>
                    <th>$<?php echo number_format($aTrade['price'],2,'.',',');?></th>
                    <th>$<?php echo number_format($aTrade['net'],2,'.',',');?></th>
                    <th>$<?php echo number_format($aTrade['commission'],2,'.',',');?></th>
                    <th>$<?php echo number_format($aTrade['secFee'],2,'.',',');?></th>
                </tr>
                <?php	
			}
			
			if(empty($aTrades)){
				?>
                <tr><td colspan="7">No trades for this date.</td></tr>
                <?php	
			}
			?>
        </tbody>
    </table>
    <?php
	
	
	//END | Display Data
	
	/*echo '<pre>';
	print_r($aPos);
	echo '</pre>';*/
	
	if($reloadRow == true){
		echo '<input type="hidden" id="reloadRow" value="1" />';	
	}
		
}

//+---------------------------------------------------------------------------------------------------------+
//|								Load Ledger Details - NEW METHOD											|
//+---------------------------------------------------------------------------------------------------------+
if($process == 'update-row'){
	
	$uid		= $_REQUEST['uid'];
	$date 		= date('Ymd',$_REQUEST['date']);
	$fundID		= $_REQUEST['fund'];
	
	$query = '
		SELECT *
		FROM '.$_SESSION['fund_pricing_table'].'
		WHERE fund_id=:fund_id AND date=:date
	';
	
	try{
		$rsLedger = $mLink->prepare($query);
		$aValues = array(
			':fund_id'		=> $fundID,
			':date'			=> $date
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsLedger->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	
	
	$ledgerCnt = 0;
	
	$ledger = $rsLedger->fetch(PDO::FETCH_ASSOC);
		
		
	if(number_format($ledger['dividends'], 2, '.', ',') != "0.00"){
		$dividends = '<span class="label label-success">$'.number_format($ledger['dividends'], 2, '.', ',').'</span>';	
	}else{
		$dividends = '$'.number_format($ledger['dividends'], 2, '.', ',').'';	
	}
	
	if(number_format($ledger['tradeValue'], 2, '.',',') != "0.00"){
		$trades = '<span class="label label-info">$'.number_format($ledger['tradeValue'], 2, '.', ',').'</span>';	
	}else{
		$trades = '$'.number_format($ledger['tradeValue'], 2, '.', ',').'';	
	}
	
	$ledgerCnt++;
	
	
	
	$loadLedgerCall 	= "loadDetails('".$fundID."','".$ledger['unix_date']."','".$uid."');";	
	$loadLedgerModal	= '#ledger-details2';
	?>
		
	<td><a href="<?php echo $loadLedgerModal;?>" onClick="<?php echo $loadLedgerCall;?>" data-toggle="modal"><?php echo date('m/d/y',$ledger['unix_date']);?></a></td>
	<td><span class="label label-info">$<?php echo number_format($ledger['startCash'], 2, '.', ',');?></span></td>
	<td>$<?php echo number_format($ledger['netFlow'], 2, '.', ',');?></td>
	<td>$<?php echo number_format($ledger['interest'], 2, '.', ',');?></td>
	<td><?php echo $dividends;?></td>
	<td><span class="label label-danger">$<?php echo number_format($ledger['fees'], 2, '.', ',');?></span></td>
	<td><?php echo $trades;?></td>
	<td>$<?php echo number_format($ledger['cashValue'], 2, '.', ',');?></td>
	<td><span class="label label-default">$<?php echo number_format($ledger['positionsValue'], 2, '.', ',');?></span></td>
	<td><span class="label label-warning">$<?php echo number_format($ledger['totalValue'], 2, '.', ',');?></span></td>
	<td><?php echo number_format($ledger['shares'], 0, '.', ',');?></td>
	<td><span class="label label-success">$<?php echo number_format($ledger['price'], 2, '.', ',');?></span></td>
	<td><?php echo  ($ledger['secCompliance'] == '1' ? "Yes" : "No");?></td>
	<td><a href="<?php echo $loadLedgerModal;?>" class="btn btn-info btn-xs" onClick="<?php echo $loadLedgerCall;?>" data-toggle="modal">Details</a></td>
	<td style="display:none;"><?php echo $ledger['unix_date'];?></td>
	<?php
	
}

//+---------------------------------------------------------------------------------------------------------+
//|								Load Ledger Details Positions - PROCESS 1									|
//+---------------------------------------------------------------------------------------------------------+
if($process == "1"){
	
	//Set Vars from URL
	$fundID		= $_REQUEST['fund'];
	$unixDate	= $_REQUEST['date'];
	
	//Set a queryDate variable for querying OLD SYSTEM API
	$queryDate = date('Ymd', $unixDate);
	
	//Convert Unixdate into human readable format
	$date = strtoupper(date('F d, Y', $unixDate));
	
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
		SELECT *
		FROM '.$_SESSION['fund_positions_table'].'
		WHERE fund_id=:fund_id AND unix_date=:unix_date
		ORDER BY value DESC
	';
	
	try{
		$rsGetPos = $mLink->prepare($query);
		$aValues = array(
			':fund_id' 		=> $fundID,
			':unix_date'	=> $unixDate
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetPos->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	
	$rowCount = $rsGetPos->rowCount();
	$showQuery = 'positionDetail|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$queryDate.'';
	//if rowCount is equal to zero, run fetch query OLD SYSTEM API
	if($rowCount == "0"){
		
		// Build Query to pass to EXTERNAL API
		//$query = 'positionDetail|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$queryDate.'';
		
		
		
		//Execute Query
		//include ('../../includes/data-query-legacy.php');
		
		$aMethodVars[] = array(
			'method'		=> 'positionDetail',
			'source'		=> 'Load Ledger Details Positions | fund-ledger-processes.php | process: 1',
			'api'			=> '1',
			'username'		=> $username,
			'fund_id'		=> $fundID,
			'fund_symbol'	=> $fundSymbol,
			'from_date'		=> $queryDate
		);
		
		$mlaResults = legacy_api($mLink, $aMethodVars, true);	
	}
	
	$showQuery = '<tr><td colspan="8">'.$showQuery.' - '.$error.' - '.$_SESSION['fund_positions_table'].' - '.$rowCount.'</th></tr>';
	
	if($displayQuery == "1"){
		$displayQuery1 = $showQuery;
	}else{
		$displayQuery1 = '';	
	}
	
	//Display Table header info
	echo '
		<thead class="flip-content">
			<tr>
				<th class="fzt-organge" colspan="8">POSITIONS ON '.$date.' - <a href="javascript:void(0);" onClick="reloadDetails(\''.$fundID.'\',\''.$unixDate.'\')" class="btn btn-default btn-xs">Refresh</a></th>
			</tr>
			
			<tr class="fzt-blue">
				<th class="fzt-aleft">SYMBOL</th>
				<th class="hidden-xs">NAME</th>
				<th>PRICE</th>
				<th>PORTION OF FUND</th>
				<th>SHARES HELD</th>
				<th>DIVIDENDS PAID</th>
				<th>VALUE</th>
				<!--<th>ACTION</th>-->
			</tr>
		</thead>
		<tbody>
	';
	
	//Start function to check to see if EXTERNAL API has finished
	function get_results($mLink, $fundID, $unixDate, $timeout){
		
		//Increment timeout counter by one
		$timeout = $timeout + 1;
		
		//Query DB to see if there are results for the provided date
		$query = '
			SELECT *
			FROM '.$_SESSION['fund_positions_table'].'
			WHERE fund_id=:fund_id AND unix_date=:unix_date
			ORDER BY value DESC
		';
		
		try{
			$rsGetPos = $mLink->prepare($query);
			$aValues = array(
				':fund_id' 		=> $fundID,
				':unix_date'	=> $unixDate
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetPos->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$rowCount = $rsGetPos->rowCount();
		
		
		
		//If the rowCount is zero, loop through the function until it gets a result or the timeout is reached.		
		if($rowCount == "0" && $timeout < 10){
			sleep(1);
			$results .= get_results($mLink, $fundID, $unixDate, $timeout);
		}else{
			$results .= $rowCount;
		}
		
	}
	
	//Set starting time out to zero
	$timeout = 0;
	
	// Run Loop function to see if Details have been written to Database
	$results = get_results($mLink, $fundID, $unixDate, $timeout);	
	
	// If results are in DB Query and print results
	if($results != "0"){
	
		$query1 = '
			SELECT *
			FROM '.$_SESSION['fund_positions_table'].'
			WHERE fund_id=:fund_id AND date=:date
			GROUP BY stockSymbol
			ORDER BY value DESC
		';
		
		try{
			$rsGetPos = $mLink->prepare($query1);
			$aValues = array(
				':fund_id' 		=> $fundID,
				':date'			=> $queryDate
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetPos->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$posTotalValue = 0;
			
		while($pos = $rsGetPos->fetch(PDO::FETCH_ASSOC)){
			
			$portionFund = round((($pos['value'] / $totalValue)*100), 2);
			
			$posTotalValue = $posTotalValue + $pos['value'];
			?>
			<tr>
				<td><a href="#global-stock-info" data-toggle="modal" onClick="loadGlobalStockInfo('<?php echo $pos['stockSymbol'];?>');"><?php echo $pos['stockSymbol'];?></a></td>
				<td class="hidden-xs"><?php echo $pos['name'];?></td>
				<td style="text-align:right;"><span class="label label-success">$<?php echo $pos['price'];?></span></td>
				<td style="text-align:right;"><?php echo $portionFund;?>%</td>
				<td style="text-align:right;"><?php echo $pos['shares'];?></td>
				<td style="text-align:right;">$<?php echo number_format($pos['dividends'], 2, '.', ',');?></td>
				<td style="text-align:right;">$<?php echo number_format($pos['value'], 2, '.', ',');?></td>
				<!--<td style="text-align:center;"><a href="#pos-details" data-toggle="modal" class="btn btn-info btn-xs">Details</a></td>-->
			</tr>
			<?php
		}
		
		echo '
			<tr>
				<td colspan="5"></td>
				<td>Stock Total:</td>
				<td>$'.number_format($posTotalValue, 2, '.',',').'</td>
			</tr>
		';
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



//+---------------------------------------------------------------------------------------------------------+
//|								Load Ledger Details Trades - PROCESS 2										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "2"){
	
	//Set Vars
	$fundID		= $_REQUEST['fund'];
	$unixDate	= $_REQUEST['date'];
	
	$date = strtoupper(date('F d, Y', $unixDate));
	
	echo '
		<thead class="flip-content">
			<tr>
				<th class="fzt-organge" colspan="7">TRADES ON '.$date.'</th>
			</tr>
			<tr class="fzt-blue">
				<th>TYPE</th>
				<th class="fzt-aleft">SYMBOL</th>
				<th>QUANTITY</th>
				<th>PRICE</th>
				<th>NET</th>
				<th>COMMISSION</th>
				<th>SEC FEE</th>
			</tr>
		</thead>
		<tbody>
	';
	
	$query = "
		SELECT *
		FROM ".$_SESSION['fund_trades_table']."
		WHERE fund_id=:fund_id and closed=:date
		ORDER BY unix_closed ASC
	";
	try{
		$rsGetTrades = $mLink->prepare($query);
		$aValues = array(
			':fund_id' 		=> $fundID,
			':date'			=> date('Ymd',$unixDate)
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetTrades->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$numTrades = $rsGetTrades->rowCount();
	while($trades = $rsGetTrades->fetch(PDO::FETCH_ASSOC)){
		
		?>
        <tr>
            <th><a href="javascript:void();"><?php echo $trades['buyOrSell'];?></a></th>
            <th><?php echo $trades['stockSymbol'];?></th>
            <th><?php echo $trades['sharesFilled'];?></th>
            <th><?php echo number_format($trades['price'],2,'.',',');?></th>
            <th><?php echo number_format($trades['net'],2,'.',',');?></th>
            <th><?php echo number_format($trades['commission'],2,'.',',');?></th>
            <th><?php echo number_format($trades['secFee'],2,'.',',');?></th>
        </tr>
        <?php
			
	}
	
	if($numTrades <= 0){
		
		echo '
			<tr>
				<td colspan="7">'.$error.'</td>
			</tr>
		';
	}
	
	echo '</tbody>';
}

//+---------------------------------------------------------------------------------------------------------+
//|								Get DAILY Ledger Details - PROCESS 3										|
//+---------------------------------------------------------------------------------------------------------+

if($process == "3"){
	
	//Set Vars
	$fundID		= $_REQUEST['fund'];
	$unixDate	= $_REQUEST['date'];
	
	$queryDate = date('Ymd', $unixDate);
	$date = strtoupper(date('F d, Y', $unixDate));
	
	//START GET LEDGER INFO
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
	
	echo '
		<div class="note note-warning">
			<p><strong>NOTE:</strong> These details pertain to '.$date.'</p>
		</div><!--note-warning-->
		
		<table id="fund-zone-table" class="table table-bordered table-striped table-condensed flip-content load-ledger-info">
			<thead class="flip-content">
				<tr>
					<th><strong>Cash:</strong> <span class="label label-info">$'.number_format($ledger['cashValue'], 2, '.', ',').'</span></th>
					<th><strong>Stock:</strong> <span class="label label-default">$'.number_format($ledger['positionsValue'], 2, '.', ',').'</span></th>
					<th><strong>Total:</strong> <span class="label label-warning">$'.number_format($ledger['totalValue'], 2, '.', ',').'</span></th>
					<th><strong>Shares:</strong> $'.number_format($ledger['shares'], 0, '.', ',').'</th>
					<th><strong>Price:</strong> <span class="label label-success">$'.number_format($ledger['price'], 2, '.', ',').'</span></th>
				</tr>
			</thead>            
        </table>
	';
	//END GET LEDGER INFO	
}


//+---------------------------------------------------------------------------------------------------------+
//|									Reload Ledger Details - PROCESS 4										|
//+---------------------------------------------------------------------------------------------------------+

if($process == "4"){
	
	//Set Vars from URL
	$fundID		= $_REQUEST['fund'];
	$unixDate	= $_REQUEST['date'];
	
	$date = strtoupper(date('F d, Y', $unixDate));
	
	//Set a queryDate variable for querying OLD SYSTEM API
	$queryDate = date('Ymd', $unixDate);
	
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
	
	$query1 = '
		SELECT *
		FROM '.$_SESSION['fund_positions_table'].'
		WHERE fund_id=:fund_id AND date=:date
		GROUP BY stockSymbol
		ORDER BY value DESC
	';
	
	try{
		$rsGetPos = $mLink->prepare($query1);
		$aValues = array(
			':fund_id' 		=> $fundID,
			':date'			=> $queryDate
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetPos->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$showQuery = '<tr><td colspan="8">'.$showQuery.'</th></tr>';
	
	if($displayQuery == "1"){
		$displayQuery1 = $showQuery;
	}else{
		$displayQuery1 = '';	
	}
	
	echo '
		<thead class="flip-content">
			<tr>
				<th class="fzt-organge" colspan="8">POSITIONS ON '.$date.' - <a href="javascript:void(0);" onClick="reloadDetails(\''.$fundID.'\',\''.$unixDate.'\')" class="btn btn-default btn-xs">Refresh</a></th>
			</tr>
			'.$displayQuery1.'
			<tr class="fzt-blue">
				<th class="fzt-aleft">SYMBOL</th>
				<th class="hidden-xs">NAME</th>
				<th>PRICE</th>
				<th>PORTION OF FUND</th>
				<th>SHARES HELD</th>
				<th>DIVIDENDS PAID</th>
				<th>VALUE</th>
				<!--<th>ACTION</th>-->
			</tr>
		</thead>
		<tbody>
	';
		
	while($pos = $rsGetPos->fetch(PDO::FETCH_ASSOC)){
		
		$portionFund = round((($pos['value'] / $totalValue)*100), 2);
		
		?>
		<tr>
			<td><a href="#global-stock-info" data-toggle="modal" onClick="loadGlobalStockInfo('<?php echo $pos['stockSymbol'];?>');"><?php echo $pos['stockSymbol'];?></a></td>
			<td class="hidden-xs"><?php echo $pos['name'];?></td>
			<td style="text-align:right;"><span class="label label-success">$<?php echo $pos['price'];?></span></td>
			<td style="text-align:right;"><?php echo $portionFund;?>%</td>
			<td style="text-align:right;"><?php echo $pos['shares'];?></td>
			<td style="text-align:right;">$<?php echo number_format($pos['dividends'], 2, '.', ',');?></td>
			<td style="text-align:right;">$<?php echo number_format($pos['value'], 2, '.', ',');?></td>
			<!--<td style="text-align:center;"><a href="#pos-details" data-toggle="modal" class="btn btn-info btn-xs">Details</a></td>-->
		</tr>
		<?php
	}
	
	echo '</tbody>';
	
}

//+--------------------------------------------------------------------------------------------------------+
//|												CSV Process
//+--------------------------------------------------------------------------------------------------------+

if($process == "csv"){
	
	$fundID = $_REQUEST['fund'];
	
	$fundSymbol = get_funds($mLink,$fundID,'fundSymbol');
	
	$query = '
		SELECT *
		FROM '.$_SESSION['fund_pricing_table'].'
		WHERE fund_id=:fund_id
		ORDER BY unix_date ASC
	';
	
	try{
		$rsLedger = $mLink->prepare($query);
		$aValues = array(
			':fund_id'		=> $fundID
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsLedger->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	//create CSV header
	$aLedgerCSV[] = array(
		'index','Date','In/Out Flow','Interest','Dividends','Fees','Buys','Sells','Cash Value','Stock Value','Total Value','Shares','Price','Compliance'
	);
	
	$ledgerCnt = 0;
	
	while($ledger = $rsLedger->fetch(PDO::FETCH_ASSOC)){
		
		if(number_format($ledger['dividends'], 2, '.', ',') != "0.00"){
			$dividends = '<span class="label label-success">$'.number_format($ledger['dividends'], 2, '.', ',').'</span>';	
		}else{
			$dividends = '$'.number_format($ledger['dividends'], 2, '.', ',').'';	
		}
		
		if(number_format($ledger['tradeValue'], 2, '.',',') != "0.00"){
			$trades = '<span class="label label-info">$'.number_format($ledger['tradeValue'], 2, '.', ',').'</span>';	
		}else{
			$trades = '$'.number_format($ledger['tradeValue'], 2, '.', ',').'';	
		}
		
		$ledgerCnt++;
		
		$aLedgerCSV[] = array(
			$ledgerCnt,
			date('m/d/y',$ledger['unix_date']),
			'$'.number_format($ledger['netFlow'], 2, '.', ','),
			'$'.number_format($ledger['interest'], 2, '.', ','),
			'$'.number_format($ledger['dividends'], 2, '.', ','),
			'$'.number_format($ledger['fees'], 2, '.', ','),
			'$'.number_format($ledger['tradeBuys'], 2,'.',','),
			'$'.number_format($ledger['tradeSells'], 2,'.',','),
			'$'.number_format($ledger['cashValue'], 2, '.', ','),
			'$'.number_format($ledger['positionsValue'], 2, '.', ','),
			'$'.number_format($ledger['totalValue'], 2, '.', ','),
			number_format($ledger['shares'], 0, '.', ','),
			'$'.number_format($ledger['price'], 2, '.', ','),
			($ledger['secCompliance'] == '1' ? "Yes" : "No")
		);
		
		
	}
	


	

    // output as an attachment
    query_to_csv($aLedgerCSV, $_SESSION['username'].'-'.$fundSymbol."_".date('Y.m.d-h.i').".csv", true);
	
	/*echo '<pre>';
	print_r($aCSV);
	echo '</pre>';*/
}
