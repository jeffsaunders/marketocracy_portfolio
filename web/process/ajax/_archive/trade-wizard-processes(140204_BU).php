<?php
/*
TRADE WIZARD - PROCESSES FILE

SUPPORTING FILES:
_______________________________________________________________

AJAX 			- THIS FILE process/ajax/trade-wizard-processes.php
PHP JAVASCRIPT	- includes/scripts/trade-wizard.js.php
HTML 			- includes/pages/trade-wizard.php
_______________________________________________________________

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


function printRow($aTrade, $mLink, $update){
	
		
		//Get vars from array
		$cnt 			= $aTrade['cnt'];
		$setBuy			= $aTrade['setBuy'];
		$setSell		= $aTrade['setSell'];
		$fundFront		= $aTrade['fundFront'];
		$aMemberFunds	= $aTrade['memberFunds'];
		$symbol			= $aTrade['symbol'];
		$companyName	= $aTrade['companyName'];
		$currentPrice	= $aTrade['currentPrice'];
		$totalShares	= $aTrade['totalShares'];
		$currentValue	= $aTrade['currentValue'];
		$currentPercent	= $aTrade['currentPercent'];
		$fixMessage		= $aTrade['fixMessage'];
		$totalValue		= $aTrade['totalValue'];
		$fundID			= $aTrade['fundID'];
		$newShares		= $aTrade['newShares'];
		$newPosition	= $aTrade['newPosition'];
		$calcPosition	= $aTrade['calcPosition'];
		$newAmmount		= $aTrade['newAmmount'];
		$cashPrevious	= $aTrade['cashPrevious'];
		$colorBG		= $aTrade['colorBG'];
		$errorColor		= $aTrade['errorColor'];
		$newCashAmmount	= $aTrade['newCashAmmount'];
		$fixCash		= $aTrade['fixCash'];
		
		$messageType	= $aTrade['messageType'];
		$messageTime	= $aTrade['messageTime'];
		$messagePos		= $aTrade['messagePos'];
		$customMessage	= $aTrade['customMessage'];
		$customTitle	= $aTrade['customTitle'];
		
		if(isset($fixMessage)){
			echo '
				<tr style="background:#F2DEDE !important;color:#B94A48 !important;">
					<td colspan="11">'.$fixMessage.'</td>
				</tr>
			';	
		}
		
		if($update == ''){?>
		<tr id="update-row-<?php echo $cnt;?>">
        <?php } ?>
        
			<!--Row-Column-->
			<td><?php if($errorColor != ''){?><span class="label label-danger" title="<?php echo $customTitle;?> | <?php echo $customMessage;?>"><i class="icon-warning-sign"></i> <?php echo $cnt;?></span> <?php }else{ echo $cnt;}?></td>
			<!--Trade-Type-Column-->
			<td>
				<div class="radio-list">
				   <label style="float:left;display:block;">
				   <input type="radio" name="trade-type-<?php echo $cnt;?>[]" value="buy" id="trade-type-buy-<?php echo $cnt;?>" data-title="Buy" <?php echo $setBuy;?> onclick="calculateTotal('<?php echo $cnt;?>','<?php echo $fundID;?>', 'radio');" />
				   Buy
				   </label>
				   <label style="float:left;display:block;margin-left:5px;">
				   <input type="radio" name="trade-type-<?php echo $cnt;?>[]" value="sell" id="trade-type-sell-<?php echo $cnt;?>" data-title="Sell" <?php echo $setSell;?> onclick="calculateTotal('<?php echo $cnt;?>','<?php echo $fundID;?>', 'radio');" />
				   Sell
				   </label>  
				   <div style="clear:both;"></div>
				</div>
			</td>
			<!--Fund-Column-->
			<td>
				<select name="fund-<?php echo $cnt;?>" id="fund-symbol-<?php echo $cnt;?>" onchange="changeFund('<?php echo $cnt;?>', '<?php echo $fundID;?>');">
					<?php
					if($fundFront != "" && $fundFront != "all"){
						$fundSymbolFront = get_funds($mLink, $fundFront, 'fundSymbol');
						echo '<option value="'.$fundFront.'" data-title="'.$fundFront.'" class="fundsymbol">'.$fundSymbolFront.'</option>';
						
						foreach($aMemberFunds as $memberFundID => $memberFundSymbol){
							echo '<option value="'.$memberFundID.'" data-title="'.$memberFundSymbol.'" class="fundsymbol">'.$memberFundSymbol.'</option>';
						}	
					}
					
					if($fundFront == 'all'){
						$fundSymbolID = get_funds($mLink, $fundID, 'fundSymbol');
						echo '<option value="'.$fundID.'" data-title="'.$fundSymbolID.'" class="fundsymbol">'.$fundSymbolID.'</option>';
					}
					?>
				</select>
			</td>
			<!--Symbol-Column-->
			<td><?php echo $symbol;?>
				<input type="hidden" class="form-control" id="symbol-<?php echo $cnt;?>" name="symbol-<?php echo $cnt;?>" value="<?php echo $symbol;?>" title="<?php echo $customTitle;?> | <?php echo $customMessage;?>" <?php echo $errorColor;?> readonly/>
			</td>
			<!--Company-Name-Column-->
			<td><?php echo $companyName;?> <input type="hidden" value="<?php echo $companyName;?>" id="company-name-<?php echo $cnt;?>" /></td>
			<!--Current-Price-Column-->
			<td>
				$<?php echo $currentPrice;?> <input type="hidden" value="<?php echo $currentPrice;?>" id="current-price-<?php echo $cnt;?>" name="current-price-<?php echo $cnt;?>" /><br />
				<span class="label label-info"><?php echo $change;?></span> <input type="hidden" value="<?php echo $change;?>" id="change-<?php echo $cnt;?>" name="change-<?php echo $cnt;?>" />
			</td>
			<!--Current-Shares-Column-->
			<td><?php if($totalShares < 0){echo '<span class="label label-danger">'.$totalShares.'</span>';}else{ echo $totalShares;}?> <input type="hidden" value="<?php echo $totalShares;?>" name="current-shares-<?php echo $cnt;?>" id="current-shares-<?php echo $cnt;?>"/></td>
			<!--Current-Percent-Column-->
			<td><?php echo $currentPercent; ?>% <input type="hidden" name="current-percent-<?php echo $cnt;?>" id="current-percent-<?php echo $cnt;?>" value="<?php echo $currentPercent; ?>" /> </td>
			<!--Current-Value-Column-->
			<td><span class="label label-success">$<?php echo $currentValue; ?></span><input type="hidden" value="<?php echo $currentValue; ?>" id="current-value-<?php echo $cnt;?>" /></td>
			<!--Shares-Column-->
			<td><input type="text" class="form-control" id="share-qty-<?php echo $cnt;?>" onchange="calculateTotal('<?php echo $cnt;?>','<?php echo $fundID;?>', 'shares');" name="shares-<?php echo $cnt;?>" value="<?php echo $newShares;?>" <?php echo $errorColor;?> title="<?php echo $customTitle;?> | <?php echo $customMessage;?>" /></td>
			<!--Position-Size-Column-->
			<td>
            	<div class="input-icon right" >
					<i class="icon-percent" style="font-size:12px;"></i>
                	<input type="text" class="form-control" id="pos-size-<?php echo $cnt;?>" name="pos-size-<?php echo $cnt;?>" value="<?php echo $newPosition;?>" onchange="calculateTotal('<?php echo $cnt;?>','<?php echo $fundID;?>', 'position');" style="text-align:right;" />
                </div>
            </td>
                
			<!--Buy-Ammount-Column-->
			<td>
				<div class="input-icon">
					<i class="icon-dollar" ></i>
					<input type="text" class="form-control" id="show-share-total-<?php echo $cnt;?>" onchange="calculateTotal('<?php echo $cnt;?>','<?php echo $fundID;?>', 'buy-sell');" data-currency="USD" name="show-buy-ammount-<?php echo $cnt; ?>" value="<?php echo $newAmmount;?>" <?php echo $colorBG;?>/>
				</div>
				<input type="hidden" id="<?php echo $fundID; ?>-cash-new-<?php echo $cnt;?>" name="<?php echo $fundID; ?>-cash-new-<?php echo $cnt;?>" value="<?php echo $newCashAmmount;?>" />
				<input type="hidden" id="<?php echo $fundID; ?>-cash-previous-<?php echo $cnt;?>" name="<?php echo $fundID; ?>-cash-previous-<?php echo $cnt;?>" value="<?php echo $cashPrevious;?>" />
                <input type="hidden" id="fix-cash-<?php echo $cnt;?>" value="<?php echo $fixCash;?>" />
                <input type="hidden" value="<?php echo $calcPosition;?>" />
			</td>
			<!--Limit-Price-Column-->
			<td>
				<div class="input-icon">
					<i class="icon-dollar" ></i>
                    <input type="text" class="form-control" name="limit-price-<?php echo $cnt;?>" id="limit-price-<?php echo $cnt;?>" onchange="formatNumber('limit-price-<?php echo $cnt;?>');" value=""/>
                </div>
            </td>
            <td style="display:none;">
            	<input type="hidden" value="<?php echo $messageType;?>" id="message-type-<?php echo $cnt;?>" />
                <input type="hidden" value="<?php echo $messagePos;?>" id="message-pos-<?php echo $cnt;?>" />
                <input type="hidden" value="<?php echo $messageTime;?>" id="message-time-<?php echo $cnt;?>" />
                
            	<input type="hidden" value="<?php echo $customMessage;?>" id="custom-message-<?php echo $cnt;?>" />
                <input type="hidden" value="<?php echo $customTitle;?>" id="custom-title-<?php echo $cnt;?>" />
                <input type="hidden" value="<?php echo $totalValue; ?>" name="total-value-<?php echo $cnt;?>" id="total-value-<?php echo $cnt;?>" />
            </td>
            
		
		<?php if($update == ''){?>
        </tr>
    	<?php
		}
	
}


//+---------------------------------------------------------------------------------------------------------+
//|									GET FUND SYMBOLS - PROCESS 1											|
//+---------------------------------------------------------------------------------------------------------+
if($process == "1"){
	
	$fundID = $_REQUEST['fund'];
	
	//echo $fundID;
	
	$query = "
		SELECT fund_id, stockSymbol 
		FROM ".$_SESSION['fund_stratification_basic_table']." 
		WHERE fund_id=:fund_id AND totalShares>'0'
	";
	
	try{
		$rsGetSymbols = $mLink->prepare($query);
		$aValues = array(
			':fund_id' 	=> $fundID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetSymbols->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$symbols = array();
	while($sym = $rsGetSymbols->fetch(PDO::FETCH_ASSOC)) {
		$symbols[] = $sym['stockSymbol'];
	}
	
	
	echo implode(',',$symbols);
}

//+---------------------------------------------------------------------------------------------------------+
//|									Process Symbols YQL - PROCESS 2											|
//+---------------------------------------------------------------------------------------------------------+
if($process == "2"){

	//GET variables from either url or posted values
	$symbols = $_REQUEST['symbols'];
	
	$sellType = $_REQUEST['sell-type-front'];
	
	$fundFront = $_REQUEST['funds_front'];
	
	$aFundsFront = $_REQUEST['include_funds'];
	
	//Check To see if a specific fundID is passed: $aTradeFunds
	if($fundFront == "" || $fundFront == "all"){
		
		//No Fund ID passed, go get all fund ids for member	
		$query = "
			SELECT mf.fund_symbol, mf.fund_id, lp.cashValue, lp.totalValue
			FROM ".$_SESSION['fund_table']." AS mf
			INNER JOIN ".$_SESSION['fund_liveprice_table']." as lp ON lp.fund_id=mf.fund_id
			WHERE mf.member_id=:member_id AND mf.active='1' AND short_fund='0'
			ORDER BY mf.seq_id ASC
		";
		
		try{
			$rsGetFunds2 = $mLink->prepare($query);
			$aValues = array(
				':member_id' 	=> $_SESSION['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetFunds2->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		//Loop through fund IDs and assign to array for processing
		while($fund2 = $rsGetFunds2->fetch(PDO::FETCH_ASSOC)){
			$totalValue = $fund2['totalValue'];
			$cashValue = $fund2['cashValue'];
			
			$fundID = $fund2['fund_id'];
			$fundSymbol = $fund2['fund_symbol'];
			
			if(in_array($fundID, $aFundsFront)){
				$aTradeFunds[$fundID] = array(
					'totalValue'	=> $totalValue,
					'cashValue'		=> $cashValue,
					'fund_symbol'	=> $fundSymbol,
					'fund_id'		=> $fundID
				);
			}
		}
	
	}else{
		
		//Fund ID passed, get info for Fund ID
		$query = "
			SELECT mf.fund_symbol, mf.fund_id, lp.cashValue, lp.totalValue
			FROM ".$_SESSION['fund_table']." AS mf
			INNER JOIN ".$_SESSION['fund_liveprice_table']." as lp ON lp.fund_id=mf.fund_id
			WHERE mf.member_id=:member_id AND mf.fund_id=:fund_id
			ORDER BY mf.seq_id ASC
		";
		
		try{
			$rsGetFunds2 = $mLink->prepare($query);
			$aValues = array(
				':member_id' 	=> $_SESSION['member_id'],
				':fund_id'		=> $fundFront
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetFunds2->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$fund2 = $rsGetFunds2->fetch(PDO::FETCH_ASSOC);
		$totalValue = $fund2['totalValue'];
		$cashValue = $fund2['cashValue'];
		
		$fundID = $fundFront;
		$fundSymbol = $fund2['fund_symbol'];
		
		$aTradeFunds[$fundID] = array(
			'totalValue'	=> $totalValue,
			'cashValue'		=> $cashValue,
			'fund_symbol'	=> $fundSymbol,
			'fund_id'		=> $fundID
		);
		
	}
	
	//Remove possible spaces
	$symbols = str_replace(" ", "", $symbols);
	
	//Replace . with -
	$symbols = str_replace(".", "-", $symbols);
	
	//Create array from comma delimited string
	$aSymbols = explode(",", $symbols);
	
	//Convert array characters to upper case
	$aSymbols = array_map('strtoupper', $aSymbols);
	
	$cntArray = count($aSymbols);
	
	//Make symbols into a comma seperated string surround by quotes
	$symbols = '"'.implode('","', $aSymbols).'"';
	
	//Build query
	$yqlQuery = "select * from yahoo.finance.quote where symbol in (".$symbols.")";
	
	//BUILD YQL URL
	$yqlURL = "https://query.yahooapis.com/v1/public/yql?q=".urlencode($yqlQuery)."&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
		
	// Make call with cURL  
	$session = curl_init($yqlURL);  
	curl_setopt($session, CURLOPT_RETURNTRANSFER,true);  
	$json = curl_exec($session); 
	
	// Convert JSON to PHP object   
	$phpObj =  json_decode($json);
	
	//start a counter to identify each individual row
	$cnt = 0;
	
	//Print table header
	echo '
		<thead>
			<tr>
				<th>Row</th>
				<th><label class="control-label col-md-3">Trade Type<span class="required">*</span></label></th>
				<th>Fund</th>
				<th class="hidden-xs">Symbol</th>
				<th class="hidden-xs">Name</th>
				<th class="hidden-xs">Current Price</th>
				<th class="hidden-xs">Current Shares</th>
				<th class="hidden-xs">Current %</th>
				<th class="hidden-xs">Current Value</th>
				<th class="hidden-xs">Shares</th>
				<th>New Position Size (%)</th>
				<th class="hidden-xs"><span class="label" style="background:#fcf8e3;border:1px solid #fcb322;color:#000000;">Buy</span> / <span class="label" style="background:#dff0d8;border:1px solid #3cc051;color:#000000;">Sell</span> ($)</th>
				<th>Limit Price ($)</th>
			</tr>
		</thead>
		<tbody class="load-trades">
	';
	
	//Set variable(COME BACK TO ME)
	$symbolString = '';
	
	
	//Check to see if there is more than one result, YQL handles objects defferently if there is only one result.	
	if($cntArray != "1"){
		
		//More than one result, loop through each result and assign values to array
		foreach($phpObj->query->results->quote as $quote){
			
			//Set Variables from returned results(YQL)
			$companyName 	= $quote->Name;
			$symbol			= $quote->Symbol;
			$currentPrice	= $quote->LastTradePriceOnly;
			$change			= $quote->Change;
			
			//Set values to array
			$aTrades[$symbol] = array(
				'companyName' 	=> $companyName,
				'symbol'		=> $symbol,
				'currentPrice'	=> $currentPrice,
				'change'		=> $change
			);
		}
	}else{
		
		//Only one result
		$companyName 	= $phpObj->query->results->quote->Name;
		$symbol			= $phpObj->query->results->quote->Symbol;
		$currentPrice	= $phpObj->query->results->quote->LastTradePriceOnly;
		$change			= $phpObj->query->results->quote->Change;
		
		//Add values to array
		$aTrades[$symbol] = array(
			'companyName' 	=> $companyName,
			'symbol'		=> $symbol,
			'currentPrice'	=> $currentPrice,
			'change'		=> $change
		);
	}
	
	$aPrintRows = array();
	
	//Loop through trades array
	foreach($aTrades as $key => $aTrade){
			
		$companyName 	= $aTrade['companyName'];
		$symbol			= $aTrade['symbol'];
		$currentPrice	= $aTrade['currentPrice'];
		$change			= $aTrade['change'];
		
		//Check to see if currentPrice returned is '0', we do not want to be able to trade with ZERO!
		if($currentPrice == "0.00" || $currentPrice == ""){
			
			//Print table row to screen to show no results for the symbol
			echo '
				<tr>
					<td>'.$cnt.'</td>
					<td colspan="10">Symbol: '.$symbol.' Does NOT exist or is NOT avaliable.</td>
				</tr>
			';	
		}else{
			
			//Loop through the TradeFund array to write a stock result for each fund in that array, this will allow members to trade the same stock for multiple funds.
			foreach($aTradeFunds as $aFundID => $aTradeFund){
				
				//increment counter by one each time
				$cnt++;
				
				//Set variables to use in the print trades include
				$totalValue = $aTradeFund['totalValue'];
				$cashValue = $aTradeFund['cashValue'];
				$fundID = $aTradeFund['fund_id'];
				$fundSymbol = $aTradeFund['fund_symbol'];
//				$fundFront = $fundID;
				
				$symbol = str_replace("-", ".", $symbol);
			
				$symbolString .= ''.$cnt.'|';
				//START STEP 1 | Get Members Fund Symbols
				$query = " 
					SELECT fund_symbol, fund_id
					FROM ".$_SESSION['fund_table']."
					WHERE member_id=:member_id AND active='1' AND short_fund='0'
					ORDER BY seq_id ASC
				";
				
				try{
					$rsGetFunds = $mLink->prepare($query);
					$aValues = array(
						':member_id' 	=> $_SESSION['member_id']
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsGetFunds->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				while($fund = $rsGetFunds->fetch(PDO::FETCH_ASSOC)) {
					if($fund['fund_id'] != $fundFront){
						//echo '<option value="'.$fund['fund_id'].'" data-title="'.$fund['fund_symbol'].'" class="fundsymbol">'.$fund['fund_symbol'].'</option>';
						
						$aMemberFunds[$fund['fund_id']] = $fund['fund_symbol'];
					}
				}
				//END STEP 1 | Get Members Fund Symbols
				
				//START STEP 3 | Get Current Shares
				
				// Get Shares
				$query = "
					SELECT totalShares AS shares, stockSymbol
					FROM ".$_SESSION['fund_stratification_basic_table']."
					WHERE fund_id=:fund_id AND stockSymbol=:stock_symbol
				";
				
				try{
					$rsGetStock = $mLink->prepare($query);
					$aValues = array(
						':stock_symbol' 	=> $symbol,
						':fund_id'			=> $fundID
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsGetStock->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				$stock = $rsGetStock->fetch(PDO::FETCH_ASSOC);
				
				$shares = $stock['shares'];
				if($shares == ''){
					$shares = 0;	
				}
				//END STEP 3 | Get Current Shares
				
				//START STEP 4 | Check Tickets table to see if there are any pending and closed symbols for today
				$query = "
					SELECT shares, status, openned, closed, action, symbol
					FROM members_fund_tickets
					WHERE fund_id=:fund_id AND symbol=:symbol AND action='sell' AND status='pending' OR fund_id=:fund_id AND symbol=:symbol AND action='sell' AND status='closed' AND closed>(SELECT process_timestamp FROM members_fund_maxdate)
					ORDER BY openned DESC
				";
				
				try{
					$rsTickets = $mLink->prepare($query);
					$aValues = array(
						':symbol' 	=> $symbol,
						':fund_id'	=> $fundID
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsTickets->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				$oldShares = 0;
				while($ticket = $rsTickets->fetch(PDO::FETCH_ASSOC)){
					$oldShares = $oldShares + $ticket['shares'];
				}
				//END STEP 4 | Check Tickets table to see if there are any pending and closed symbols for today
				
				//START CALCULATIONS
				$totalShares = $shares - $oldShares;
				$currentValue1 = ($totalShares * $currentPrice);
				$currentValue = number_format($currentValue1, 2, '.', ',');
				
				//Get Current Position %
				$currentPercent = round((($currentValue1/$totalValue)*100), 2);
				
				//add label to $change depending on what works? haha < - come back to me later
				if (strpos($change,'+') !== false) {
					$percentChange = '<span class="label label-success">'.$change.'</span>';
				}else{
					$percentChange = '<span class="label label-danger">'.$change.'</span>';
				}
				//END CALCULATIONS
				
				switch($sellType){
					case "both": $setBuy = ""; $setSell = "";break;
					case "buy": $setBuy = "checked"; $setSell = "";break;
					case "sell": $setBuy = ""; $setSell = "checked";break;
					default: $setBuy = ""; $setSell = "";break;
				}
				
				if($totalShares < 0){
					$setBuy = "checked";
					$setSell = "";
					//$fixShareCnt = abs($totalShares);
					
					$fixMessage = '<i class="icon-arrow-down"></i> <strong>This stock is shorted in your fund! Please fix immediately!</strong> <i class="icon-arrow-down"></i>';	
				}
				
				
				$aPrintRows[$cnt] = array(
					'cnt'				=> $cnt,
					'setBuy'			=> $setBuy,
					'setSell'			=> $setSell,
					'fundFront'			=> $fundFront,
					'memberFunds'		=> $aMemberFunds,
					'symbol'			=> $symbol,
					'companyName'		=> $companyName,
					'currentPrice'		=> $currentPrice,
					'totalShares'		=> $totalShares,
					'currentValue'		=> $currentValue,
					'currentPercent'	=> $currentPercent,
					'fixMessage'		=> $fixMessage,
					'totalValue'		=> $totalValue,
					'fundID'			=> $fundID,
					'newCashAmmount'	=> $_REQUEST[''.$fundID.'-calc-cash']
				);
				
			}
			
		} // End current price check
	}
	
	//Print Trade Rows
	foreach($aPrintRows as $key => $aTrade){
		echo printRow($aTrade, $mLink);
	}
	
	$symbolString = explode('|', $symbolString);
	$symbolString = array_filter($symbolString);
	$symbolString = implode('|', $symbolString);	
	//print a string of the symbols to use in processing later
	echo '<tr style="display:none;"><td><input type="hidden" value="'.$symbolString.'" name="symbol_string" id="validator"/></td></tr>';
	
	
	/*?>
    <tr><td colspan="13"><pre><?php print_r($aPrintRows);?></pre></td></tr>
    <?php*/
	//Close the table body tag
	echo '</tbody>';
	
}

//+---------------------------------------------------------------------------------------------------------+
//|										CHANGE FUND - PROCESS 3												|
//+---------------------------------------------------------------------------------------------------------+
if($process == "3"){
	
	$fundID 		= $_REQUEST['fund'];
	$symbol 		= $_REQUEST['symbol'];
	$currentPrice	= $_REQUEST['price'];
	$cnt 			= $_REQUEST['cnt'];
	$companyName 	= $_REQUEST['name'];
	$change 		= $_REQUEST['change'];
	$tradeType 		= $_REQUEST['tradeType'];
	$cash			= $_REQUEST['cash'];
	$prevCash		= $_REQUEST['prevCash'];
	
	//Fix cash issue
	$fixCash = $cash + $prevCash;
	
	switch($tradeType){
		case "buy":$tradeBuy = 'checked';$tradeSell = '';break;
		case "sell":$tradeBuy = '';$tradeSell = 'checked';break;	
	}
	
	// Get Fund Total Value
	$query = "
		SELECT totalValue 
		FROM ".$_SESSION['fund_pricing_table']."
		WHERE fund_id=:fund_id 
		ORDER BY date DESC
		LIMIT 1
	";
	
	try{
		$rsGetFund = $mLink->prepare($query);
		$aValues = array(
			':fund_id'		=> $fundID
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetFund->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$fundInfo = $rsGetFund->fetch(PDO::FETCH_ASSOC);
	
	$totalValue = $fundInfo['totalValue'];
	
	// Get Shares
	$query = "
		SELECT totalShares AS shares, stockSymbol
		FROM ".$_SESSION['fund_stratification_basic_table']."
		WHERE fund_id=:fund_id AND stockSymbol=:stock_symbol
	";
	
	try{
		$rsGetStock = $mLink->prepare($query);
		$aValues = array(
			':stock_symbol' 	=> $symbol,
			':fund_id'			=> $fundID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetStock->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$stock = $rsGetStock->fetch(PDO::FETCH_ASSOC);
	
	$shares = $stock['shares'];
	if($shares == ''){
		$shares = 0;	
	}
	//END STEP 3 | Get Current Shares
	
	//START STEP 4 | Check Tickets table to see if there are any pending and closed symbols for today
	$query = "
		SELECT shares, status, openned, closed, action, symbol
		FROM members_fund_tickets
		WHERE fund_id=:fund_id AND symbol=:symbol AND action='sell' AND status='pending' OR fund_id=:fund_id AND symbol=:symbol AND action='sell' AND status='closed' AND closed>(SELECT process_timestamp FROM members_fund_maxdate)
		ORDER BY openned DESC
	";
	
	try{
		$rsTickets = $mLink->prepare($query);
		$aValues = array(
			':symbol' 	=> $symbol,
			':fund_id'	=> $fundID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsTickets->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$oldShares = 0;
	while($ticket = $rsTickets->fetch(PDO::FETCH_ASSOC)){
		$oldShares = $oldShares + $ticket['shares'];
	}
	//END STEP 4 | Check Tickets table to see if there are any pending and closed symbols for today
	
	//START CALCULATIONS
	$totalShares = $shares - $oldShares;
	$currentValue1 = ($totalShares * $currentPrice);
	$currentValue = number_format($currentValue1, 2, '.', ',');
	
	//Get Current Position %
	$currentPercent = round((($currentValue1/$totalValue)*100), 2);
	
	//END CALCULATIONS
	
	$fundSymbol = get_funds($mLink, $fundID, 'fundSymbol');
	
	//echo 'Shares:'.$shares.'|Current Value:'.$currentValue.'|Current %'.$currentPercent.'|Total Value:'.$totalValue.'|Current Price: '.$currentPrice.'';
	
	$query = " 
		SELECT fund_symbol, fund_id
		FROM ".$_SESSION['fund_table']."
		WHERE member_id=:member_id AND active='1' AND short_fund='0'
		ORDER BY seq_id ASC
	";
	
	try{
		$rsGetFunds = $mLink->prepare($query);
		$aValues = array(
			':member_id' 	=> $_SESSION['member_id']
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetFunds->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	while($fund = $rsGetFunds->fetch(PDO::FETCH_ASSOC)) {
		if($fund['fund_id'] != $fundID){
			$aMemberFunds[$fund['fund_id']] = $fund['fund_symbol'];
		}
	}
	
	$aPrintRows[$cnt] = array(
		'cnt'				=> $cnt,
		'setBuy'			=> $tradeBuy,
		'setSell'			=> $tradeSell,
		'fundFront'			=> $fundID,
		'memberFunds'		=> $aMemberFunds,
		'symbol'			=> $symbol,
		'companyName'		=> $companyName,
		'currentPrice'		=> $currentPrice,
		'totalShares'		=> $totalShares,
		'currentValue'		=> $currentValue,
		'currentPercent'	=> $currentPercent,
		'fixMessage'		=> $fixMessage,
		'totalValue'		=> $totalValue,
		'fundID'			=> $fundID,
		'fixCash'			=> $fixCash
	);
	
	//Print Trade Rows
	foreach($aPrintRows as $key => $aTrade){
		echo printRow($aTrade, $mLink, 'update');
	}
	
}

//+---------------------------------------------------------------------------------------------------------+
//|										Calculate Trade - PROCESS 4											|
//+---------------------------------------------------------------------------------------------------------+
if($process == "4"){
	//?process=4&price=' + price + '&cash=' + cash + '&tradeType=' + tradeType + '&stock=' + stockSymbol + '&shares=' + shares + '&totalValue=' + totalValue + '&currentShares=' + currentShares
	
	$cnt			= $_REQUEST['cnt'];
	$tradeType 		= $_REQUEST['tradeType'];
	$fundID			= $_REQUEST['fund'];
	$stockSymbol	= $_REQUEST['stock'];
	$companyName	= $_REQUEST['company'];
	$price			= $_REQUEST['price'];
	$currentShares	= $_REQUEST['currentShares'];
	
	$currentPercent	= $_REQUEST['currentPercent'];
	$newPercent		= floatval($_REQUEST['newPercent']);
	
	$currentValue	= $_REQUEST['currentValue'];
	$shares 		= $_REQUEST['shares'];
	$totalValue		= $_REQUEST['totalValue'];
	$cash			= $_REQUEST['cash'];
	$calcType		= $_REQUEST['calcType'];
	$cashPrevious 	= $_REQUEST['cashPrevious'];
	$cashAdjust		= $_REQUEST['cashAdjust'];
	
	$newAmmount		= floatval($_REQUEST['newAmmount']);
	
	//Get Fund IDs and Symbols for dropdown list
	$query = " 
		SELECT fund_symbol, fund_id
		FROM ".$_SESSION['fund_table']."
		WHERE member_id=:member_id AND active='1' AND short_fund='0'
		ORDER BY seq_id ASC
	";
	
	try{
		$rsGetFunds = $mLink->prepare($query);
		$aValues = array(
			':member_id' 	=> $_SESSION['member_id']
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetFunds->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	while($fund = $rsGetFunds->fetch(PDO::FETCH_ASSOC)) {
		if($fund['fund_id'] != $fundID){
			$aMemberFunds[$fund['fund_id']] = $fund['fund_symbol'];
		}
	}
	//End get fund IDs and Symbols
	
	//Calculate for New Ammount
	if($calcType == 'buy-sell'){
		
		$shares = floor(($newAmmount / $price));
		
		$test = $shares;
		
		$calcType = 'shares';
		
	}//end calc type
	
	//Calculate for New Position Size
	if($calcType == 'position'){
		
		//If trade type is "sell" make sure they cant put a percentage that is higher than their current percentage BOO YA
		if($tradeType == 'buy'){
			
			if($newPercent < $currentPercent){
				//$tradeType = 'sell';
				$newPercent = $currentPercent;
			}else{
				$newPercent = $newPercent;
			}
			
			$shares = (floor((($totalValue * ($newPercent/100)) / $price))) - $currentShares;
		}elseif($tradeType == 'sell'){
			
			
			
			if($newPercent > $currentPercent){
				$newPercent = $currentPercent;	
				//$tradeType = 'buy';
			}else{
				$newPercent = $newPercent;	
			}
			//$test = $newPercent.'|'.$currentPercent;
			
			$shares = $currentShares - (floor((($totalValue * ($newPercent/100)) / $price)));
			
			if($shares < 0){
				$shares = 0;	
			}
		}
		
		$calcType = 'shares';
	}//end calc type
	
	
	
	//Calculate for shares and radio/tradetype
	if($calcType == "shares" || $calcType == "radio"){
		
		//check trade type
		if($tradeType == "buy"){
			
			//Check for negative cash
			if($cash > 0){
				$tradeBuy = 'checked';
				$tradeSell = '';
				$colorBG = 'style="background:#fcf8e3;border:1px solid #fcb322;"';
			
				$newPosition = ((($shares + $currentShares) * $price) / $totalValue)*100;
				$newAmmount = ($shares * $price);
				
				if($newAmmount <= $cash){
					$newShares = $shares;
					
					echo 'less than '.$cash.'|';
				}else{
					echo 'more than|';
					$calcShares = floor($cash / $price);
					
					$newPosition = (($calcShares * $price) / $totalValue)*100;
					$newAmmount = ($calcShares * $price);
					
					$newShares = $calcShares;
					
					$messagePos = 'toast-top-right';
					$messageTime = '10000';
					$messageType = 'warning';
					$customMessage	= 'You do not have enough avaliable cash to purchase the requested ammount of shares of '.$stockSymbol.'. Shares total has been adjusted automatically.';
					$customTitle = 'Cash Warning';
				}
				
				
				
				
				if($newAmmount > $cashPrevious){
					
					$newCalcAmmount = $newAmmount - $cashPrevious;	
					
					$newCashAmmount = $cash - $newCalcAmmount;
					
					$newCashPrevious = $newAmmount;
					
				}elseif($newAmmount < $cashPrevious){
					
					$newCalcAmmount = $cashPrevious - $newAmmount;	
					
					$newCashAmmount = $cash + $newCalcAmmount;
					
					$newCashPrevious = $newAmmount;
				}
				
				//Check to see if cash is empty if so get cash 
				if($cashAdjust == '0' || $cashAdjust == ''){
					$newCashAmmount = $cash;	
				}
				if($newShares == '0' && $cashPrevious == '' || $newShares == '0' && $cashPrevious == '0'){
						$newCashAmmount = $cash;	
					
				}
			}else{
				$tradeBuy = 'checked';
				$tradeSell = '';
				
				$newShares = 0;
				$newCashAmmount = $cash;
				$newPosition = 0;
				
				$messagePos = 'toast-top-right';
				$messageTime = '10000';
				$messageType = 'warning';
				$customMessage	= 'You do not have enough avaliable cash to purchase this stock.';
				$customTitle = 'Cash Warning';
			}
			
		}elseif($tradeType == "sell"){
			
			//Check to see if there is previous cash, this is the case when a member switches from buy to sell
			if($cashPrevious != '0' || $cashPrevious != ''){
				$newCashAmmount = $cash + $cashPrevious;
			}
			
			$tradeBuy 	= '';
			$tradeSell 	= 'checked';
			$colorBG	= 'style="background:#dff0d8;border:1px solid #3cc051;"';
			
			if($shares > $currentShares){
				$shares = $currentShares;
				
				$messagePos = 'toast-top-right';
				$messageTime = '10000';
				$messageType = 'warning';
				$customMessage	= 'You can not sell more shares than you own.';
				$customTitle = 'Share ammount exceeded!';
			}
			
			$newPosition = ((($currentShares - $shares) * $price) / $totalValue)*100;
			$newAmmount = ($shares * $price);
			
			$newShares = $shares;
			
			$newCalcAmmount = $cashPrevious;
			
			
			//Check to see if cash is empty if so get cash 
			if($cashAdjust == '0' || $cashAdjust == ''){
				$newCashAmmount = $cash;	
			}
		}
	}//end if calc type is radio or shares
	
	
	//START Fund Validation, check to see if fund will become non compliant because of this trade.
	
	//check to see if trade will break complaince.
	$checkStock = isStock50($mLink, $stockSymbol, $fundID);
	
	$roundNewPosition = round($newPosition, 2);
	
	if($checkStock == '1'){
		//Stock is top 50%
		
		if($newPosition > '25'){
		
			$messageType 	= 'error';
			$messagePos		= 'toast-top-full-width';
			$messageTime 	= '30000';
				
			$customMessage	= 'This trade of '.$stockSymbol.' would take your position to over 25% of your fund, which would disqualify your fund from ranking until such time as your fund becomes rules compliant again, and any gains made by your fund before that date will not count.';
			$customTitle 	= 'Warning: Rule 2 violation! First half of your portfolio can not exceed 25%.';
			
			$errorColor = 'style="background:#FAEAE6;border:1px solid #ED4E2A;"';
		}
	}else{
		//Stock is bottom 50%
		
		if($roundNewPosition > 10){
			
			$messageType 	= 'error';
			$messagePos		= 'toast-top-full-width';
			$messageTime 	= '30000';
			$customMessage	= 'This trade of '.$stockSymbol.' would take your position to over 10% of your fund, which would disqualify your fund from ranking until such time as your fund becomes rules compliant again, and any gains made by your fund before that date will not count.';
			$customTitle 	= 'Warning: Rule 3 violation! Second half of your portfolio can not exceed 10%.';
			
			$errorColor = 'style="background:#FAEAE6;border:1px solid #ED4E2A;"';
		}
		
		//$messageType = 'success';
		//$customTitle = $newPosition;
		
	}
	
	
	$newPositionDisplay = number_format($newPosition, 2, '.', '');
	$newAmmount = number_format($newAmmount, 2, '.', ',');
	
	//echo $cnt.'|'.$tradeType.'|'.$fundID.'|'.$stockSymbol.'|'.$companyName.'|'.$price.'|'.$currentShares.'|'.$currentPercent.'|'.$currentValue.'|'.$shares.'|'.$totalValue.'|'.$cash;
	if($newShares == '0'){
		$colorBG = '';
			
	}
	
	$aPrintRows[$cnt] = array(
		'cnt'				=> $cnt,
		'setBuy'			=> $tradeBuy,
		'setSell'			=> $tradeSell,
		'fundFront'			=> $fundID,//
		'memberFunds'		=> $aMemberFunds,//
		'symbol'			=> $stockSymbol,
		'companyName'		=> $companyName,
		'currentPrice'		=> $price,
		'totalShares'		=> $currentShares,
		'currentPercent'	=> $currentPercent,
		'currentValue'		=> $currentValue,
		'fixMessage'		=> $fixMessage,//
		'totalValue'		=> $totalValue,
		'fundID'			=> $fundID,
		'newShares'			=> $newShares,
		'newPosition'		=> $newPositionDisplay,
		'calcPosition'		=> $test,
		'newAmmount'		=> $newAmmount,
		'cashPrevious'		=> $newCashPrevious,
		'colorBG'			=> $colorBG,
		'errorColor'		=> $errorColor,
		'newCashAmmount'	=> $newCashAmmount,
		
		//Make notification
		'messageType'		=> $messageType,
		'messageTime'		=> $messageTime,
		'messagePos'		=> $messagePos,
		'customMessage'		=> $customMessage,
		'customTitle'		=> $customTitle
	);
	
	//Print Trade Rows
	foreach($aPrintRows as $key => $aTrade){
		echo printRow($aTrade, $mLink, 'update');
	}
		
}

//+---------------------------------------------------------------------------------------------------------+
//|										Format Numbers - PROCESS 5											|
//+---------------------------------------------------------------------------------------------------------+
if($process == "5"){
	
	$value = $_REQUEST['value'];
	
	$value = number_format(round($value, 2, PHP_ROUND_HALF_DOWN),2,'.',',');
	
	echo $value;
}


//+---------------------------------------------------------------------------------------------------------+
//|									Print Trade Confirmation - PROCESS 6									|
//+---------------------------------------------------------------------------------------------------------+
if($process == "6"){
	$index = $_REQUEST['index'];
	
	$rowCount = rtrim($_REQUEST['symbol_string'], "|");
	$aRowCnt = explode("|", $rowCount);
	
	$aTrades = array();
	
	$error_list = array();

	foreach($aRowCnt as $cnt){
		
		//Shares
		$shares = "";
		$shares = $_REQUEST['shares-'.$cnt.''];
		
		
		if($shares != ""){
		
			//Symbol
			$symbol = $_REQUEST['symbol-'.$cnt.''];
			
			
			//Trade Type
			$tradeType = $_REQUEST['trade-type-'.$cnt.''];
			$tradeType = $tradeType[0];
			
			
			//Fund ID
			$fundID = $_REQUEST['fund-'.$cnt.''];
			
					
			//Price		
			$price = $_REQUEST['current-price-'.$cnt.''];
			
			//username
			$username = $_REQUEST['username'];
			
			//Order Type
			$orderType = $_REQUEST['order_type'];
			$orderType = $orderType[0];
			//Analize
			$aReasons = $_REQUEST['reasons'];
			$reasons = implode("~", $aReasons);
			$tradeDesc = $_REQUEST['trade-desc'];
			
			$limitPrice = $_REQUEST['limit-price-'.$cnt.''];	
			$buyAmmount = $_REQUEST['show-buy-ammount-'.$cnt.''];

			if(!empty($shares)){
							 
				$aTrades[] = array($tradeType, $fundID, $symbol, $price, $shares, $orderType, $username, $buyAmmount, $reasons, $tradeDesc, $limitPrice);
			}
			
			
			$aFunds = Array();
			foreach($aTrades as $aFund){
				$aFunds[] = $aFund[1];	
			}
		}
		
	}
	$aFunds = array_unique($aFunds);
			
	if($index == "2"){
		
		$tradeCnt = 0;
		
		foreach($aFunds as $orderFundID){
			
			$orderFundSymbol = get_funds($mLink, $orderFundID, 'fundSymbol');
			echo '<h4 class="form-section">'.$orderFundSymbol.' Trades</h4>';
			
			echo '
				<table  class="table table-striped table-bordered table-hover table-full-width">
					<thead>
						<tr>
							<th>Symbol</th>
							<th>Trade Type</th>
							<th>Shares</th>
							<th>Total</th>
							<th>Limit Price</a>
						</tr>
					</thead>
					<tbody>
			';	
			
			$buyTotal = 0;
			
			foreach($aTrades as $aTrade){
				
				$tradeCnt = $tradeCnt + 1;
				
				$username = $aTrade[6];
				$fundSymbol = get_funds($mLink, $aTrade[1], 'fundSymbol');
				$fundID = $aTrade[1];
				$tradeType = $aTrade[0];
				
				switch($tradeType){
					case "buy":$tradeType = "Buy";break;
					case "sell":$tradeType = "Sell";break;	
				}
				$orderType = $aTrade[5];
				$symbol = $aTrade[2];
				$shares = $aTrade[4];
				$reason = $aTrade[8];
				
				$reasons = str_replace('~', ' | ', $reason);
				
				$desc 	= $aTrade[9];
				
				$buyAmmount = number_format($aTrade[7], 2, '.', ',');
				$buyAmmount = $aTrade[7];
				
				
				if($orderFundID == $fundID){
					//Create query
					$query = 'create||'.$username.'|'.$fundSymbol.'|'.$fundID.'|'.$tradeType.'|'.$orderType.'|'.$symbol.'|'.$shares.'';
					
					?>
                    <tr>
                        <td><?php echo $symbol;?></td>
                        <td><?php echo $tradeType; ?></td>
                        <td><?php echo $shares; ?></td>
                        <td>$<?php echo $buyAmmount;?></td>	
                        <td><?php echo $aTrade[10];?></td>
                    </tr>
                    <?php
					
					$buyTotal .= $buyTotal + $buyAmmount;
				}
				
				
			}//end foreach aTrades
			
			
				
				
			echo '
				<tr style="display:none">
					<td>Number of Trades</td>
					<td colspan="3">'.$tradeCnt.' <input type="hidden" value="'.$tradeCnt.'" name="row-cnt" id="row-cnt"/></td>
				</tr>
			';
			echo '
					</tbody>
				</table>
			';
					
			
		}//end foreach aFunds
		
		//Display Analyse fields if there is any.	
		if($reasons != '' || $desc != ''){
			
			echo '<h4 class="form-section">Analyze</h4>';
			
			if($reasons == ''){
				$reasons = 'No reasons provided.';	
			}
			if($desc == ''){
				$desc = 'No detailed reason provided.';	
			}
			echo '
				<table  class="table table-striped table-bordered table-hover table-full-width">
					<thead>
						<tr>
							<th>Reasons</th>
							<th>Detail Reason</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>'.$reasons.'</td>
							<td>'.$desc.'</td>
						</tr>
					</tbody>
				</table>
			';
		}
		
	}//end if index == 2
}

//+---------------------------------------------------------------------------------------------------------+
//|										Process Trades - PROCESS 7											|
//+---------------------------------------------------------------------------------------------------------+
if($process == "7"){
	
	$index 		= $_REQUEST['index'];
	$rowCount	= rtrim($_REQUEST['symbol_string'], "|");
	$aRowCnt 	= explode("|", $rowCount);
	
	$aTrades 	= array();
	
	foreach($aRowCnt as $cnt){
		
		$aTradeType		= $_REQUEST['trade-type-'.$cnt.''];
		$aOrderType 	= $_REQUEST['order_type'];
		$aReasons 		= $_REQUEST['reasons'];
		
		$username		= $_REQUEST['username'];
		$fundID 		= $_REQUEST['fund-'.$cnt.''];
		$fundSymbol 	= get_funds($mLink, $fundID, 'fundSymbol');
		$tradeType 		= $aTradeType[0];
		$orderType 		= $aOrderType[0];
		$stockSymbol	= $_REQUEST['symbol-'.$cnt.''];
		$shares 		= $_REQUEST['shares-'.$cnt.''];
		$reasons 		= implode("~", $aReasons);
		$tradeDesc 		= $_REQUEST['trade-desc'];
		$limitPrice		= $_REQUEST['limit-price-'.$cnt.''];
		
		
		if(!empty($shares)){
			$aTrades[] = array($username, $fundSymbol, $fundID, $tradeType, $orderType, $stockSymbol, $shares, $reasons, $tradeDesc, $limitPrice);
			
		}
	}
	
	if($index == "3"){
		
		$showQueries = array();
		
		foreach($aTrades as $aTrade){
			
			$username 		= $aTrade[0];
			$fundSymbol		= $aTrade[1];
			$fundID 		= $aTrade[2];
			$tradeType 		= $aTrade[3];
			$orderType		= $aTrade[4];
			$symbol			= $aTrade[5];
			$shares 		= $aTrade[6];
			$reason 		= $aTrade[7];
			$desc			= $aTrade[8];
			$limitPrice		= $aTrade[9];
			
			//Create query
			$query = 'create||'.$username.'|'.$fundSymbol.'|'.$fundID.'|'.$tradeType.'|'.$orderType.'|'.$symbol.'|'.$shares.'|'.$reason.'|'.$desc.'|'.$limitPrice.'';
			
			//Execute Query
			include ('../../includes/data-query-ecn.php');
			
			$showQueries[] = $query;
			
		}
		
		echo '<h4>Trades submitted successful, please wait while we redirect you to your open orders page.</h4>';
		/*echo '<pre>';
		echo $identify;
		print_r($showQueries);
		echo '</pre>';*/
		
	}
}
?>