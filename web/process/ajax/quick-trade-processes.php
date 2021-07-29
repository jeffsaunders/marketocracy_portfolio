<?php
/*
TRADE WIZARD - PROCESSES FILE

SUPPORTING FILES:
_______________________________________________________________

AJAX 			- THIS FILE process/ajax/trade-wizard-processes.php
JAVASCRIPT 		- js/trade-wizard.js
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


//+---------------------------------------------------------------------------------------------------------+
//|										Process Quick Trade - PROCESS 1										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "1"){
	
	$username 		= $_REQUEST['username'];
	$fundID 		= $_REQUEST['fund_id'];
	$fundSymbol 	= get_funds($mLink, $fundID, 'fundSymbol');
	$tradeType		= $_REQUEST['trade_type'];
	$orderType 		= $_REQUEST['order_type'];
	$stockSymbol 	= strtoupper($_REQUEST['stock_symbol']);
	$shares			= $_REQUEST['shares'];
	
	$error_list = array();
	//sleep(2);
	if(empty($stockSymbol)){
		$error_list[] = 'Please provide a Stock Symbol.';	
	}
	
	if(empty($shares)){
		$error_list[] = 'Please provide the ammount of shares you wish to trade.';	
	}
	
	if(empty($error_list)){
		
		$query = 'create||'.$username.'|'.$fundSymbol.'|'.$fundID.'|'.$tradeType.'|'.$orderType.'|'.$stockSymbol.'|'.$shares.'';
		include ('../../includes/data-query-ecn.php');
		
		sleep(1);
		//echo $query;
		
	}else{
		?>
		<div class="alert alert-block alert-danger fade in">
			<button type="button" class="close" data-dismiss="alert"></button>
			<h4><strong>Please fix the following errors.</strong></h4>
			<ul>
				<?php
				foreach($error_list as $error){
					echo '<li>'.$error.'</li>';	
				}
				?>
			</ul>
			
		</div>
		<?php
	}
	
		
}


//+---------------------------------------------------------------------------------------------------------+
//|										Load Trades - PROCESS 2												|
//+---------------------------------------------------------------------------------------------------------+
if($process == "2"){
	
	//GET symbols from URL
	$symbols = $_REQUEST['symbols'];
	
	//Remove possible spaces
	$symbols = str_replace(" ", "", $symbols);
	
	//Create array from comma delimited string
	$aSymbols = explode(",", $symbols);
	
	//Convert array characters to upper case
	$aSymbols = array_map('strtoupper', $aSymbols);
	
	//Start a counter to deal with multiple entries
	$cnt = 0;
	
	//Run calculations and grab information from DB tables related to each symbol
	foreach ($aSymbols as $symbol){
		
		//Increment Counter by one each time
		$cnt = $cnt + 1;
		
		//START STEP 1 | Get Members Fund Symbols
		$query = " 
			SELECT fund_symbol, fund_id
			FROM ".$_SESSION['fund_table']."
			WHERE member_id=:member_id
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
		//END STEP 1 | Get Members Fund Symbols
		
		//START STEP 2 | Get Company ID, Company Name, and Current Price of Symbol
		
		$query = "
			SELECT ss.company_id, ss.symbol, sc.company_name
			FROM stock_symbols AS ss
			INNER JOIN stock_companies AS sc ON sc.company_id=ss.company_id
			WHERE ss.symbol=:symbol
			LIMIT 1
		";
		
		try{
			$rsCompany = $mLink->prepare($query);
			$aValues = array(
				':symbol' 	=> $symbol
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsCompany->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$company = $rsCompany->fetch(PDO::FETCH_ASSOC);
		
		$companyID 		= $company['company_id'];
		$companyName 	= $company['company_name'];
		//END STEP 2 | Get Company ID, Company Name, and Current Price of Symbol
		
		//START STEP 3 | Get Current Shares
		
		$query = " 
			SELECT fund_symbol, fund_id
			FROM ".$_SESSION['fund_table']."
			WHERE member_id=:member_id
			ORDER BY seq_id ASC
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
		
		$fund2 = $rsGetFunds2->fetch(PDO::FETCH_ASSOC);
		
		$query = "
			SELECT shares, stockSymbol, unix_date
			FROM `members_fund_details`
			WHERE fund_id=:fund_id AND stockSymbol=:stock_symbol
			GROUP BY unix_date
			ORDER BY unix_date DESC
			LIMIT 1
		";
		
		try{
			$rsGetStock = $mLink->prepare($query);
			$aValues = array(
				':stock_symbol' 	=> $symbol,
				':fund_id'			=> $fund2['fund_id']
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
		//END STEP 3 | Get Current Shares
		
		//START STEP 4 | Get Current Price
		
		$now = time();
		
		$query = "
			SELECT currentPrice, timestamp
			FROM stock_prices
			WHERE company_id=:company_id
			ORDER BY timestamp DESC
			LIMIT 1
		";
		
		try{
			$rsCompany2 = $mLink->prepare($query);
			$aValues = array(
				':company_id' 	=> $companyID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsCompany2->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$company2 = $rsCompany2->fetch(PDO::FETCH_ASSOC);
		$currentPrice	= number_format($company2['currentPrice'], 2, '.', ',');
		//END STEP 4 | Get Current Price
		
		
		//START CALCULATIONS
		$currentValue = number_format(($shares * $company['currentPrice']), 2, '.', ',');
		//END CALCULATIONS
		?>
		
		<tr>
			<td>
				<div class="radio-list">
				   <label>
				   <input type="radio" name="trade-type-<?php echo $cnt;?>[]" value="buy" data-title="Buy" />
				   Buy
				   </label>
				   <label>
				   <input type="radio" name="trade-type-<?php echo $cnt;?>[]" value="sell" data-title="Sell"/>
				   Sell
				   </label>  
				</div>
			</td>
			<td>
				<select name="fund-<?php echo $cnt;?>" >
					<?php
					while($fund = $rsGetFunds->fetch(PDO::FETCH_ASSOC)) {
						echo '<option value="'.$fund['fund_symbol'].'" data-title="'.$fund['fund_symbol'].'" class="fundsymbol">'.$fund['fund_symbol'].'</option>';
					}
					?>
				</select>
			</td>
			<td>
				<input type="text" class="form-control" name="symbol-<?php echo $cnt;?>" value="<?php echo $symbol;?>" />
			</td>
			<td><?php echo $companyName;?></td>
			<td>$<?php echo $currentPrice;?><input type="hidden" value="<?php echo $currentPrice; ?>" id="share-price" /></td>
			<td><?php echo $shares;?></td>
			<td>N/A Current</td>
			<td><span class="label label-success">$<?php echo $currentValue; ?></span><input type="hidden" value="234947.70" id="current-value" /></td>
			<td><input type="text" class="form-control" id="share-qty" name="shares-<?php echo $cnt;?>" value="<?php echo $company2['timestamp'];?>" /></td>
			<td><input type="text" class="form-control" id="pos-size" name="pos-size" value="<?php echo $now;?>" /></td>
			<td><input type="text" class="form-control" id="share-total" name="buy-ammount" value="" /></td>
			<td><input type="text" class="form-control" name="limit-price" /></td>
		</tr>
		<?php
	}	
	
	echo '
		<tr>
			<td colspan="12"><button type="button" onClick="loadStock(\''.$_REQUEST['symbols'].'\')" class="btn btn-info">Recalculate</button></td>
		</tr>
	';
}


//+---------------------------------------------------------------------------------------------------------+
//|										YQL Process - PROCESS 3												|
//+---------------------------------------------------------------------------------------------------------+
if($process == "3"){
	
	//GET symbols from URL
	$symbols = $_REQUEST['symbols'];
	
	$sellType = $_REQUEST['type'];
	
	$fundFront = $_REQUEST['fund'];
	
	//Remove possible spaces
	$symbols = str_replace(" ", "", $symbols);
	
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
	
	$cnt = 0;
		
	//Loop through phpObj	
	if($cntArray != "1"){
		
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
					<th class="hidden-xs">Buy $</th>
				</tr>
			</thead>
			<tbody class="load-trades">
		';
		
		$symbolString = '';
		
		//Build trades for each symbol
		foreach($phpObj->query->results->quote as $quote){
			
			$cnt = $cnt + 1;
			
			//Set Variables from returned results
			$companyName 	= $quote->Name;
			$symbol			= $quote->Symbol;
			$currentPrice	= $quote->LastTradePriceOnly;
			$change			= $quote->Change;
			
			$symbolString .= ''.$cnt.'|';
			//START STEP 1 | Get Members Fund Symbols
			$query = " 
				SELECT fund_symbol, fund_id
				FROM ".$_SESSION['fund_table']."
				WHERE member_id=:member_id
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
			//END STEP 1 | Get Members Fund Symbols
			
			//START STEP 3 | Get Current Shares
			
			// Get Fund ID
			/*$query = " 
				SELECT fund_symbol, fund_id
				FROM ".$_SESSION['fund_table']."
				WHERE member_id=:member_id
				ORDER BY seq_id ASC
			";*/
			
			if($fundFront == "" || $fundFront == "all"){
			
				$query = "
					SELECT mf.fund_symbol, mf.fund_id, lp.cashValue, lp.totalValue
					FROM ".$_SESSION['fund_table']." AS mf
					INNER JOIN ".$_SESSION['fund_liveprice_table']." as lp ON lp.fund_id=mf.fund_id
					WHERE mf.member_id=:member_id
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
				
				$fund2 = $rsGetFunds2->fetch(PDO::FETCH_ASSOC);
				$totalValue = $fund2['totalValue'];
				$cashValue = $fund2['cashValue'];
				
				$fundID = $fund2['fund_id'];
				$fundSymbol = $fund2['fund_symbol'];
			
			}else{
				
				$query = "
					SELECT mf.fund_symbol, mf.fund_id, lp.cashValue, lp.totalValue
					FROM members_fund AS mf
					INNER JOIN members_fund_liveprice as lp ON lp.fund_id=mf.fund_id
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
				
			}
			
			/*// Get Fund Total Value
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
			
			$totalValue = $fundInfo['totalValue'];*/
			
			// Get Shares
			$query = "
				SELECT shares, stockSymbol, unix_date
				FROM `members_fund_details`
				WHERE fund_id=:fund_id AND stockSymbol=:stock_symbol
				GROUP BY unix_date
				ORDER BY unix_date DESC
				LIMIT 1
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
			//END STEP 3 | Get Current Shares
			
			//START CALCULATIONS
			$currentValue1 = ($shares * $currentPrice);
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
			
			
			
			?>
			
			<tr id="update-row-<?php echo $cnt;?>">
				<td><?php echo $cnt;?></td>
                <td>
					<div class="radio-list">
					   <label>
					   <input type="radio" name="trade-type-<?php echo $cnt;?>[]" value="buy" id="trade-type-buy-<?php echo $cnt;?>" data-title="Buy" <?php echo $setBuy;?> />
					   Buy
					   </label>
					   <label>
					   <input type="radio" name="trade-type-<?php echo $cnt;?>[]" value="sell" id="trade-type-sell-<?php echo $cnt;?>" data-title="Sell" <?php echo $setSell;?> />
					   Sell
					   </label>  
					</div>
				</td>
				<td>
					<select name="fund-<?php echo $cnt;?>" id="fund-symbol-<?php echo $cnt;?>" onchange="changeFund('<?php echo $cnt;?>');">
						<?php
						if($fundFront != "" && $fundFront != "all"){
							$fundSymbolFront = get_funds($mLink, $fundFront, 'fundSymbol');
							echo '<option value="'.$fundFront.'" data-title="'.$fundFront.'" class="fundsymbol">'.$fundSymbolFront.'</option>';	
						}
						
						while($fund = $rsGetFunds->fetch(PDO::FETCH_ASSOC)) {
							if($fund['fund_id'] != $fundFront){
								echo '<option value="'.$fund['fund_id'].'" data-title="'.$fund['fund_symbol'].'" class="fundsymbol">'.$fund['fund_symbol'].'</option>';
							}
						}
						?>
					</select>
				</td>
				<td>
					<input type="text" class="form-control" id="symbol-<?php echo $cnt;?>" name="symbol-<?php echo $cnt;?>" value="<?php echo $symbol;?>" />
				</td>
				<td><?php echo $companyName;?> <input type="hidden" value="<?php echo $companyName;?>" id="company-name-<?php echo $cnt;?>" /></td>
				<td>
                    $<?php echo $currentPrice;?> <input type="hidden" value="<?php echo $currentPrice;?>" id="current-price-<?php echo $cnt;?>" name="current-price-<?php echo $cnt;?>" /><br />
                    <span class="label label-info"><?php echo $change;?></span> <input type="hidden" value="<?php echo $change;?>" id="change-<?php echo $cnt;?>" name="change-<?php echo $cnt;?>" />
                </td>
				<td><?php echo $shares;?></td>
				<td><?php echo $currentPercent; ?>%</td>
				<td><span class="label label-success">$<?php echo $currentValue; ?></span><input type="hidden" value="234947.70" id="current-value" /></td>
				<td><input type="text" class="form-control" id="share-qty-<?php echo $cnt;?>" onblur="calculateTotal('<?php echo $cnt;?>','<?php echo $fundSymbol;?>');" name="shares-<?php echo $cnt;?>"  /></td>
				<td style="display:none;"><input type="text" class="form-control" id="pos-size" name="pos-size" value="<?php echo $now;?>" /></td>
				<td>
                	<div class="input-icon">
						<i class="icon-dollar"></i>
                		<input type="text" class="form-control" id="show-share-total-<?php echo $cnt;?>" data-currency="USD" name="show-buy-ammount-<?php echo $cnt; ?>" value="" />
                    </div>
                    <input type="hidden" class="form-control" id="share-total-<?php echo $cnt;?>" data-currency="USD" name="buy-ammount-<?php echo $cnt;?>" value="" />
                	<input type="hidden" id="<?php echo $fundSymbol; ?>-cash-change-<?php echo $cnt;?>" name="<?php echo $fundSymbol; ?>-cash-change-<?php echo $cnt;?>" value="0" />
                    <input type="hidden" id="<?php echo $fundSymbol; ?>-cash-previous-<?php echo $cnt;?>" name="<?php echo $fundSymbol; ?>-cash-previous-<?php echo $cnt;?>" value="0" />
                </td>
				<td style="display:none;"><input type="text" class="form-control" name="limit-price" value="<?php echo $cashValue;?>"/></td>
			</tr>
			<?php
		}
		
		echo '<tr style="display:none;"><td><input type="hidden" value="'.$symbolString.'" name="symbol_string" id="validator"/></td></tr>';
		echo '</tbody>';
		
		
		
	}else{
		//FOR SINGLE SYMBOL
		$name 	= $phpObj->query->results->quote->Name;
		$symbol	= $phpObj->query->results->quote->Symbol;
		$price	= $phpObj->query->results->quote->LastTradePriceOnly;	
	}
	
	
}

//+---------------------------------------------------------------------------------------------------------+
//|										CHANGE FUND - PROCESS 4												|
//+---------------------------------------------------------------------------------------------------------+
if($process == "4"){
	
	$fundID = $_REQUEST['fund'];
	$symbol = $_REQUEST['symbol'];
	$currentPrice = $_REQUEST['price'];
	$cnt = $_REQUEST['cnt'];
	$companyName = $_REQUEST['name'];
	$change = $_REQUEST['change'];
	//echo $symbol;
	
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
		SELECT shares, stockSymbol, unix_date
		FROM `members_fund_details`
		WHERE fund_id=:fund_id AND stockSymbol=:stock_symbol
		GROUP BY unix_date
		ORDER BY unix_date DESC
		LIMIT 1
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
	//END STEP 3 | Get Current Shares
	
	//START CALCULATIONS
	$currentValue1 = ($shares * $currentPrice);
	$currentValue = number_format($currentValue1, 2, '.', ',');
	
	//Get Current Position %
	$currentPercent = round((($currentValue1/$totalValue)*100), 2);
	
	//END CALCULATIONS
	
	$fundSymbol = get_funds($mLink, $fundID, 'fundSymbol');
	
	//echo 'Shares:'.$shares.'|Current Value:'.$currentValue.'|Current %'.$currentPercent.'|Total Value:'.$totalValue.'|Current Price: '.$currentPrice.'';
	
	?>
    <td><?php echo $cnt;?></td>
    <td>
        <div class="radio-list">
           <label>
           <input type="radio" name="trade-type-<?php echo $cnt;?>[]" value="buy" id="trade-type-buy-<?php echo $cnt;?>" data-title="Buy" />
           Buy
           </label>
           <label>
           <input type="radio" name="trade-type-<?php echo $cnt;?>[]" value="sell" id="trade-type-sell-<?php echo $cnt;?>" data-title="Sell"/>
           Sell
           </label>  
        </div>
    </td>
    <td>
        <select name="fund-<?php echo $cnt;?>" id="fund-symbol-<?php echo $cnt;?>" onchange="changeFund('<?php echo $cnt;?>');">
            <?php
			if($fundID != "all"){?>
            	<option value="<?php echo $fundID;?>" data-title="<?php echo $fundSymbol;?>" class="fundsymbol"><?php echo $fundSymbol;?></option>
            <?php
			}
			
            $query = " 
                SELECT fund_symbol, fund_id
                FROM ".$_SESSION['fund_table']."
                WHERE member_id=:member_id
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
                if($fundID != $fund['fund_id']){
                    echo '<option value="'.$fund['fund_id'].'" data-title="'.$fund['fund_symbol'].'" class="fundsymbol">'.$fund['fund_symbol'].'</option>';
                }
            }
            ?>
        </select>
    </td>
    <td>
        <input type="text" class="form-control" id="symbol-<?php echo $cnt;?>" name="symbol-<?php echo $cnt;?>" value="<?php echo $symbol;?>" data-tittle="<?php echo $symbol;?>"/>
    </td>
    <td><?php echo $companyName;?> <input type="hidden" value="<?php echo $companyName;?>" id="company-name-<?php echo $cnt;?>" /></td>
    <td>
    	$<?php echo $currentPrice;?> <input type="hidden" value="<?php echo $currentPrice;?>" id="current-price-<?php echo $cnt;?>" name="current-price-<?php echo $cnt;?>" /><br />
        <span class="label label-info"><?php echo $change;?></span> <input type="hidden" value="<?php echo $change;?>" id="change-<?php echo $cnt;?>" name="change-<?php echo $cnt;?>" />
    </td>
    <td><?php echo $shares;?></td>
    <td><?php echo $currentPercent; ?>%</td>
    <td><span class="label label-success">$<?php echo $currentValue; ?></span><input type="hidden" value="234947.70" id="current-value" /></td>
    <td><input type="text" class="form-control"  id="share-qty-<?php echo $cnt;?>" onblur="calculateTotal('<?php echo $cnt;?>','<?php echo $fundSymbol;?>');" name="shares-<?php echo $cnt;?>"  /></td>
    <td style="display:none;"><input type="text" class="form-control" id="pos-size" name="pos-size" value="<?php echo $now;?>" /></td>
    <td>
        <div class="input-icon">
            <i class="icon-dollar"></i>
            <input type="text" class="form-control" id="show-share-total-<?php echo $cnt;?>" data-currency="USD" name="show-buy-ammount-<?php echo $cnt;?>" />
        </div>
        <input type="hidden" class="form-control" id="share-total-<?php echo $cnt;?>" data-currency="USD" name="buy-ammount-<?php echo $cnt;?>" value="" />
        <input type="hidden" id="<?php echo $fundSymbol; ?>-cash-change-<?php echo $cnt;?>" name="<?php echo $fundSymbol; ?>-cash-change-<?php echo $cnt;?>" value="0" />
        <input type="hidden" id="<?php echo $fundSymbol; ?>-cash-previous-<?php echo $cnt;?>" name="<?php echo $fundSymbol; ?>-cash-previous-<?php echo $cnt;?>" value="0" />
    </td>
    <td style="display:none;"><input type="text" class="form-control" name="limit-price" /></td>
    <?php
}

//+---------------------------------------------------------------------------------------------------------+
//|										Do some math - PROCESS 5											|
//+---------------------------------------------------------------------------------------------------------+
if($process == "5"){
	
	//Get vars
	$price 			= $_REQUEST['price'];
	$shares 		= $_REQUEST['shares'];
	$cashPrevious	= $_REQUEST['cashPrevious'];
	$cash 			= $_REQUEST['cash'];
	
	//start calculations
	$calc = ($price * $shares);
	
	$adjusted = $cashPrevious - $calc;
	
	$newCash = $adjusted + $cash;
	
	echo $newCash;
}

//+---------------------------------------------------------------------------------------------------------+
//|										Format Numbers - PROCESS 6											|
//+---------------------------------------------------------------------------------------------------------+
if($process == "6"){
	
	$value = $_REQUEST['value'];
	
	$value = number_format(round($value, 2, PHP_ROUND_HALF_DOWN),2,'.',',');
	
	echo $value;
}

//+---------------------------------------------------------------------------------------------------------+
//|								Calculate new share qty - PROCESS 7											|
//+---------------------------------------------------------------------------------------------------------+
if($process == "7"){
	
	$price = $_REQUEST['price'];
	$cash = $_REQUEST['cash'];
	$calc = $_REQUEST['calc'];
	$adjustedCash = $_REQUEST['adjustedCash'];
	
	$newShares = floor((($calc + $cash + $adjustedCash) / $price));
	
	//echo 'Price:'.$price.'|Cash:'.$cash.'|Calc:'.$calc.'|adjusted:'.$adjustedCash.'|New Shares:';
	
	echo $newShares;
}

//+---------------------------------------------------------------------------------------------------------+
//|									GET FUND SYMBOLS - PROCESS 8											|
//+---------------------------------------------------------------------------------------------------------+
if($process == "8"){
	
	$fundID = $_REQUEST['fund'];
	
	$query = "
		SELECT fund_id, date, stockSymbol 
		FROM `members_fund_details` 
		WHERE fund_id=:fund_id
		GROUP BY stockSymbol
		ORDER BY date DESC, value DESC
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
	while($sym = $rsGetSymbols->fetch(PDO::FETCH_ASSOC)) {
		$symbols .= ''.$sym['stockSymbol'].', ';
	}
	
	
	echo $symbols;
}

//+---------------------------------------------------------------------------------------------------------+
//|									Validate/Process Form - PROCESS 9										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "9"){
	
	$index = $_REQUEST['index'];
	
	$rowCount = rtrim($_REQUEST['symbol_string'], "|");
	$aRowCnt = explode("|", $rowCount);
	
	$aTrades = Array();
	
	$error_list = array();
	
	foreach($aRowCnt as $cnt){
		
		//Symbol
		$symbol = $_REQUEST['symbol-'.$cnt.''];
		
		if(empty($symbol)){
			$error_list[] = 'No symbol has been submitted on row '.$cnt.'';
		}
		
		//Shares
		
		$shares = $_REQUEST['shares-'.$cnt.''];
		
		if(empty($shares)){
			//$error_list[] = 'Please provide the ammount of shares for Stock:'.$symbol.' on row '.$cnt.'';
		}else{
		
			//Trade Type
			$tradeType = $_REQUEST['trade-type-'.$cnt.''];
			$tradeType = $tradeType[0];
			
			if(empty($tradeType)){
				$error_list[] = 'Please select a Trade Type for Stock:'.$symbol.' on row '.$cnt.'';
			}
		}
		
		
		
		//Fund ID
		$fundID = $_REQUEST['fund-'.$cnt.''];
		
		if(empty($fundID)){
			$error_list[] = 'Please select a Fund for Stock:'.$symbol.' on row '.$cnt.'';
		}
				
		//Price		
		$price = $_REQUEST['current-price-'.$cnt.''];
		
		//username
		$username = $_REQUEST['username'];
		
		//Order Type
		$orderType = $_REQUEST['order_type'];
		$orderType = $orderType[0];
		
		if(!empty($shares)){
			$aTrades[] = array($tradeType, $fundID, $symbol, $price, $shares, $orderType, $username);
		}
		//print_r($aTrade);	
	}
	//print_r($aRowCnt);
	
	if(empty($error_list)) {
		
		if($index == "2"){
			foreach($aTrades as $aTrade){
				
				//print_r($aTrade);
				
				$username = $aTrade[6];
				$fundSymbol = get_funds($mLink, $aTrade[1], 'fundSymbol');
				$fundID = $aTrade[1];
				$tradeType = $aTrade[0];
				$orderType = $aTrade[5];
				$symbol = $aTrade[2];
				$shares = $aTrade[4];
				
				
				//Create query
				$query = 'create||'.$username.'|'.$fundSymbol.'|'.$fundID.'|'.$tradeType.'|'.$orderType.'|'.$symbol.'|'.$shares.'';
				
				
				
				
			}
		}
		
		//print_r($aTrades);
		if($index == "3"){
		
			foreach($aTrades as $aTrade){
				
				//print_r($aTrade);
				
				$username = $aTrade[6];
				$fundSymbol = get_funds($mLink, $aTrade[1], 'fundSymbol');
				$fundID = $aTrade[1];
				$tradeType = $aTrade[0];
				$orderType = $aTrade[5];
				$symbol = $aTrade[2];
				$shares = $aTrade[4];
				
				
				//Create query
				$query = 'create||'.$username.'|'.$fundSymbol.'|'.$fundID.'|'.$tradeType.'|'.$orderType.'|'.$symbol.'|'.$shares.'';
				
				
				
				//Execute Query
				include ('../../includes/data-query-ecn.php');
				
				//echo $query; echo "~~";
				sleep(1);
			}
			
			//header('Location: /?page=02-00-00-002');
		}
		
		
	}else{
		
		?>
		<div class="alert alert-block alert-danger fade in">
			<button type="button" class="close" data-dismiss="alert"></button>
			<h4><strong>Please fix the following errors.</strong></h4>
			<ul>
				<?php
				foreach($error_list as $error){
					echo '<li>'.$error.'</li>';	
				}
				?>
			</ul>
			
		</div>
		<?php
			
	}
	
}

//+---------------------------------------------------------------------------------------------------------+
//|									Print Trade Confirmation - PROCESS 10									|
//+---------------------------------------------------------------------------------------------------------+
if($process == "10"){
	
	$index = $_REQUEST['index'];
	
	$rowCount = rtrim($_REQUEST['symbol_string'], "|");
	$aRowCnt = explode("|", $rowCount);
	
	$aTrades = Array();
	
	$error_list = array();
	//print_r($aRowCnt);
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
			
			$buyAmmount = $_REQUEST['show-buy-ammount-'.$cnt.''];
			//echo $buyAmmount; echo "here";
			if(!empty($shares)){
				$aTrades[] = array($tradeType, $fundID, $symbol, $price, $shares, $orderType, $username, $buyAmmount);
			}
			
			
			$aFunds = Array();
			foreach($aTrades as $aFund){
				$aFunds[] = $aFund[1];	
			}
		}
		
		//print_r($aFunds);
	}
	//print_r($aRowCnt);
	$aFunds = array_unique($aFunds);
	
	//print_r($aFunds);
	//print_r($aTrades);
		
	if($index == "2"){
		
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
						</tr>
					</thead>
					<tbody>
			';	
			
			$buyTotal = 0;
			foreach($aTrades as $aTrade){
				
				//print_r($aTrade);
				
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
				
				//echo $aTrade[6];
				$buyAmmount = number_format($aTrade[7], 2, '.', ',');
				$buyAmmount = $aTrade[7];
				
				
				if($orderFundID == $fundID){
					//Create query
					$query = 'create||'.$username.'|'.$fundSymbol.'|'.$fundID.'|'.$tradeType.'|'.$orderType.'|'.$symbol.'|'.$shares.'';
					//echo $query; echo '~~';
					//echo 'here';echo $aTrade[7];
					?>
                    <tr>
                        <td><?php echo $symbol;?></td>
                        <td><?php echo $tradeType; ?></td>
                        <td><?php echo $shares; ?></td>
                        <td>$<?php echo $buyAmmount;?></td>	
                    </tr>
                    <?php
					
					$buyTotal .= $buyTotal + $buyAmmount;
				}
				
				
			}//end foreach aTrades
			
			/*echo '
				<tr>
					<td></td>
					<td></td>
					<td></td>
					<td>Total: $'.$buyTotal.'</td>
				</tr>
			';*/
			
			echo '
					</tbody>
                </table>
			';
			
		}//end foreach aFunds
		
	}//end if index == 2

	
}//end if process == 10
?>