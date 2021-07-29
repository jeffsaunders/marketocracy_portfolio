<?php
/*
SEARCH - Process

SUPPORTING FILES:
_______________________________________________________________

AJAX 			- THIS FILE process/ajax/search-processes.php
PHP JAVASCRIPT	- includes/scripts/search.js.php
HTML 			- includes/pages/search.php
_______________________________________________________________

*/


if(!isset($process)){
	//Start Session
	session_start();
	
	// Load debug & error logging functions
	require_once("../../includes/system-debug-functions.php");
	
	//Connect to DB
	require_once("../../../secure/dbconnect.php");
	
	// Load System Wide Functions
	require_once("../../includes/system-functions.php");
	
	$process = $_GET['process'];	
}

$stockTable = $_SESSION['stock_valid_symbols_table'];//$_SESSION['stock_symbols_table'];'stock_valid_symbols2_save';//

//+---------------------------------------------------------------+
//|					PROCESS 1 : SEARCH						 	  |
//+---------------------------------------------------------------+
if($process == '1'){
	
	$startTime = round(microtime(true) * 1000);
	$resultsCnt = 0;
	
	//echo $_REQUEST['search'];
	
	$searchString = preg_replace('#[^a-z 0-9?!$()]#i', '', $_REQUEST['search']);
	$filter = $_REQUEST['filter'];
	
	//echo $searchString;
	
	switch($filter){
		case "all"			: $searchQuery = "all";break;
		case "stock"		: $searchQuery = "2";break;
		case "community"	: $searchQuery = "3";break;
		case "funds"		: $searchQuery = "4";break;
		case "rank"			: $searchQuery = "5";break;
		case "support"		: $searchQuery = "6";break;
		default				: $searchQuery = "all";break;	
	}
	
	
	//00-01: START SEARCH QUERY 1 : STOCKS
	if($searchQuery == "2" || $searchQuery == "all"){
		
		
		//01-01: START: Search Valid Stocks - (This checks to see if the search result is an acutal stock, if it is, then get data for that stock)
		
		$cnt = 0; //Start counter for stock lookup results
		
		
		$regex = '#\((([^()]+|(?R))*)\)#';
		if (preg_match_all($regex, $_REQUEST['search'] ,$matches)) {
			$searchData = implode(' ', $matches[1]);
		} else {
			//no parenthesis
			$searchData = $searchString;
		}
		$symbolUpper = strtoupper($searchData); //force uppercase for the search string
		
		
		
		// Search Valid Stocks table for the search string
		$sQuery = "
			SELECT symbol, company
			FROM ".$stockTable."
			WHERE symbol='".$symbolUpper."'
		";

		try{
			$rsSearch = $mLink->prepare($sQuery);
			$aValues = array();
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $searchQuery); //Debug
			$rsSearch->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		// Loop through results, although there should only be ONE match.
		while($search = $rsSearch->fetch(PDO::FETCH_ASSOC)){
			
			// Assign symbol to stock array for use later in getting stock quotes from YQL/New Data Feed
			$aStocks[$cnt] = $search['symbol'];
			
			
			//02-01: START: Check to see if member has this stock
			
			//Query tund database and grab all funds for member
			$query = "
				SELECT fund_id
				FROM ".$_SESSION['fund_table']."
				WHERE member_id=:member_id AND active=1
			";
	
			try{
				$rsGetFunds = $mLink->prepare($query);
				$aValues = array(
					':member_id' => $_SESSION['member_id']
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $searchQuery); //Debug
				$rsGetFunds->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$aFunds = array();
			
			//Stuff those members funds into an array
			while($funds = $rsGetFunds->fetch(PDO::FETCH_ASSOC)){
				
				$aFunds[] = $funds['fund_id'];
					
			}
			
			//Loop through the funds array and check to see if that members fund hold the stock that is being searched
			foreach($aFunds as $key => $fundID){
				
				//Check DB to see if fund holds the searched stock
				$query = "
					SELECT stockSymbol, totalShares, currentValue, fundRatio, fund_id
					FROM ".$_SESSION['fund_stratification_basic_table']."
					WHERE fund_id=:fund_id AND stockSymbol=:stockSymbol
				";
		
				try{
					$rsGetData = $mLink->prepare($query);
					$aValues = array(
						':fund_id' => $fundID,
						':stockSymbol'	=> $search['symbol']
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $searchQuery); //Debug
					$rsGetData->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				$fundsCnt = 0;
				
				//Loop through results and get stratification data for the stock
				while($data = $rsGetData->fetch(PDO::FETCH_ASSOC)){
					$fundsCnt++;
					$aStockInfo[$data['fund_id']] = array(
						'stockSymbol' 	=> $data['stockSymbol'],
						'totalShares'	=> $data['totalShares'],
						'currentValue'	=> '$'.number_format($data['currentValue'], 2, '.', ','),
						'fundRatio'		=> number_format(($data['fundRatio']*100), 2, '.', '').'%',
						'fund_id'		=> $data['fund_id'],
						'fund_symbol'	=> get_funds($mLink, $data['fund_id'], 'fundSymbol'),
						'held_position'	=> true
					);	
				} // End $data While Loop
				
				if($fundsCnt == 0){
					$aStockInfo[$data['fund_id']] = array(
						'stockSymbol' 	=> $search['symbol'],
						'totalShares'	=> 0,
						'currentValue'	=> '$0.00',
						'fundRatio'		=> '0.0%',
						'fund_id'		=> $fundID,
						'fund_symbol'	=> get_funds($mLink, $fundID, 'fundSymbol'),
						'held_position'	=> false
					);	
				}
				
				//Sort the stock info array witht the fund that has the highest share count on top
				usort($aStockInfo, function($a, $b) {
					return $b['totalShares'] - $a['totalShares'];
				});
				
			} //02-01: END Check to see if member has this stock
			
			$cnt++; //Increment result counter by 1
			
		} // END $search While Loop
		
		
		// If there was no direct match on the original lookup, change the search paramiter to find similar stocks relating to the search.
		if($cnt != 1){
		
			$sQuery = "
				SELECT symbol, company
				FROM ".$stockTable."
				WHERE symbol LIKE '%".$searchString."%' OR company LIKE '%".$searchString."%'
			";
	
			try{
				$rsSearch = $mLink->prepare($sQuery);
				$aValues = array();
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $searchQuery); //Debug
				$rsSearch->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			//Loop through search results, if match found add symbol to aStocks array
			while($search = $rsSearch->fetch(PDO::FETCH_ASSOC)){
					
					$aStocks[$cnt] = $search['symbol'];
					$aCompany[$cnt] = $search['company'];
					
					$cnt++;					
			}
		} // END if cnt != 1
		
		//01-01: END: Search Valid Stocks
		
		
		//01-02: START: get data for stocks 
		
		//Check to see if stock array is empty, if not keep processing
		if(!empty($aStocks)){
			//Create function calculating and displaying the Percent Change on a stock
			function calcPercentChange($percentChange, $currentPrice, $prevClose){
				
				$difference = $currentPrice - $prevClose;
				
				if($difference < 0){
					$change = '<span style="color:#ED4E2A;"><i class="icon-arrow-down"></i> '.number_format($difference, 2).' ('.number_format($percentChange,2,'.',',').'%)</span>';
				}else{
					$change = '<span style="color:#3D9400;"><i class="icon-arrow-up"></i> '.number_format($difference, 2).' ('.number_format($percentChange,2,'.',',').'%)</span>';
				}
				
				/*if(strpos($change, '+') !== false){
					$change	= (str_replace('+', '', $change)*1);
					
					$percentChange = number_format((($change / ($currentPrice - $change))*100), 2);
					
					$change = '<span style="color:#3D9400;"><i class="icon-arrow-up"></i> '.number_format($change, 2).' ('.$percentChange.'%)</span>';
				}elseif(strpos($change, '-') !== false){
					$change = (str_replace('-', '', $change)*1);
					
					$percentChange = number_format((($change / ($currentPrice - $change))*100), 2);
					
					$change = '<span style="color:#ED4E2A;"><i class="icon-arrow-down"></i> '.number_format($change, 2).' ('.$percentChange.'%)</span>';
				}*/
			
				return $change;	
			} //End FUNCTION: calcPercentChange()
			
			//Implode stock results in to a comma delimeted string for use in YQL query
			$symbols = '"'.implode('","', $aStocks).'"';
						
			//Build query
			$yqlQuery = "select * from yahoo.finance.quote where symbol in (".$symbols.")";
				
			//BUILD YQL URL
			/*$yqlURL = "https://query.yahooapis.com/v1/public/yql?q=".urlencode($yqlQuery)."&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
			
			// Make call with cURL  
			$session = curl_init($yqlURL);  
			curl_setopt($session, CURLOPT_RETURNTRANSFER,true);  
			$json = curl_exec($session);
			
			// Convert JSON to PHP object   
			$phpObj =  json_decode($json);*/
			
			//Count array to determin if there is more than one result. YQL treats 1 result and multiple results differently
			$numResults = count($aStocks);
			
			
			
			/*
			
			if($numResults > 1){
				// If there are more than 1 result run foreach loop
				
				$count = 0; //initialize couter for loop
				
				//Loop through each result that came back from the YQL query
				foreach($phpObj->query->results->quote as $quote){
				
					//Set Variables from returned results
					$companyName 	= $quote->Name;
					$symbol			= $quote->Symbol;
					$currentPrice	= ($quote->LastTradePriceOnly * 1);
					$change			= $quote->Change;
					$exchange		= $quote->StockExchange;
					
					//Convert date from feed
					$date	= $phpObj->query->created;
					$year	= substr($date, 0, 4);
					$month	= substr($date, 5, 2);
					$day	= substr($date, 8, 2);
					$hour	= (substr($date, 11, 2)-5);
					$min	= (substr($date, 14, 2)-15);
					
					$unixdate = mktime($hour, $min, 0, $month, $day, $year);
					
					//If the stock prices is less than 1 dollar, do not show
					if($currentPrice > 1){
						
						$change = calcPercentChange($change, $currentPrice);
						
						$subText = '<strong>'.$currentPrice.'</strong> '.$change.'';
						
						//Add each result to main search array for displaying results on the search page
						$aSearch['search']['Stocks']['break-'.$count] = array(
							'title'		=> '('.$symbol.') '.$companyName,
							'subtext'	=> $subText,
							'url'		=> '?page=01-00-00-002&search='.$symbol.'',
							'special'	=> '<br />'.$exchange.' - '.date('M d g:i A', $unixdate).''
						);
						
						$count++; //increment loop counter
						
						$resultsCnt++; //Increment the search results counter
					}
					
					//Set a var for the first stock symbol the loop encounters
					if(!isset($stockSymbol)){
						$stockSymbol = $symbol;
						$stockCompany = $companyName;
					}
					
				}// END foreach $phpObj	
				
			}elseif($numResults == 1){
				//If there is only one result
				
				//Set Variables from returned results
				$companyName 	= $phpObj->query->results->quote->Name;
				$symbol			= $phpObj->query->results->quote->Symbol;
				$currentPrice	= ($phpObj->query->results->quote->LastTradePriceOnly * 1);
				$change			= $phpObj->query->results->quote->Change;
				$exchange		= $phpObj->query->results->quote->StockExchange;
				
				//Convert date from feed
				$date	= $phpObj->query->created;
				$year	= substr($date, 0, 4);
				$month	= substr($date, 5, 2);
				$day	= substr($date, 8, 2);
				$hour	= (substr($date, 11, 2)-5);
				$min	= (substr($date, 14, 2)-15);
				
				$unixdate = mktime($hour, $min, 0, $month, $day, $year);	
				
				
				$change = calcPercentChange($change, $currentPrice);
				
				$subText = '<strong>'.$currentPrice.'</strong> '.$change.'';
			
				$aSearch['search']['Stocks']['break-'.$count] = array(
					'title'		=> '('.$symbol.') '.$companyName,
					'subtext'	=> $subText,
					'url'		=> 'javascript:void(0);',
					'special'	=> '<br />'.$exchange.' - '.date('M d g:i A', $unixdate).' '
				);
				
				$resultsCnt++;
				
				if(!isset($stockSymbol)){
					$stockSymbol = $symbol;
					$stockCompany = $companyName;
				}
				
			}*/
			
			//REFACTOR STOCK LOOKUP
			
			#Remove possible spaces
			/*$symbols = str_replace(" ", "", $symbols);
			
			#Replace . with -
			$symbols = str_replace(".", "/", $symbols);
			
			#Create array from comma delimited string
			$aSymbols = explode(",", $symbols);
			
			#Convert array characters to upper case
			$aSymbols = array_map('strtoupper', $aSymbols);
			
			$_SESSION['checkSymbols'] = $aSymbols;
			
			$cntArray = count($aSymbols);*/
			
			#Make symbols into a comma seperated string surround by quotes
			//$symbols = '"'.implode('","', $aSymbols).'"';
			
			foreach($aStocks as $key=>$symbol){

				$query = "
					SELECT Name AS companyName, Symbol AS symbol, Last AS CurrentPrice, ChangeFromPreviousClose AS chang, Market, mTimeStamp, PreviousClose, PercentChangeFromPreviousClose as percentChange
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
				$count = '0';
				$foo = $rsSymbols->fetch(PDO::FETCH_ASSOC);
				
				
				
				
				//Set Variables from returned results
				$companyName 	= $foo['companyName'];
				$symbol			= $foo['symbol'];
				$currentPrice	= $foo['CurrentPrice'];
				$change			= $foo['chang'];
				$exchange		= $foo['Market'];
				$prevClosed		= $foo['PreviousClose'];
				$percentChange	= $foo['percentChange'];
				
				//Convert date from feed
				
				$unixdate = $foo['mTimeStamp'];	
				
				
				$change = calcPercentChange($percentChange, $currentPrice, $prevClosed);
				
				$subText = '<strong>'.number_format(round($currentPrice, 2), 2, '.', '').'</strong> '.$change.'';
			
				$aSearch['search']['Stocks']['break-'.$count] = array(
					'title'		=> '('.$symbol.') '.$companyName,
					'subtext'	=> $subText,
					'url'		=> 'javascript:void(0);',
					'special'	=> '<br />'.$exchange.' - '.date('M d g:i A', $unixdate).' '
				);
				
				/*$aTrades[$symbol] = array(
					'companyName' 	=> $foo['companyName'],
					'symbol'		=> $foo['symbol'],
					'currentPrice'	=> number_format(round($foo['CurrentPrice'], 2), 2, '.', ''),
					'change'		=> $foo['chang']
				);*/
				
				if(!isset($stockSymbol)){
					$stockSymbol = $symbol;
					$stockCompany = $companyName;
				}
			}
			//END REFACTOR STOCK LOOKUP
			
		} //End if empty $aStocks
		
		//01-02: END: get data for stocks
		
		//01-03: START: Find members that own searched stock
		
		//Query DB and find all funds that have owned or own the searched stock
		$query = "
			SELECT sb.stockSymbol, sb.totalShares, sb.currentValue, sb.fundRatio, sb.fund_id, f.member_id, f.fund_name, f.fund_symbol
			FROM ".$_SESSION['fund_stratification_basic_table']." AS sb
			INNER JOIN ".$_SESSION['fund_table']." AS f ON f.fund_id=sb.fund_id
			WHERE sb.stockSymbol=:stockSymbol
			ORDER BY sb.totalShares DESC
			LIMIT 100
		";

		try{
			$rsGetMemberData = $mLink->prepare($query);
			$aValues = array(
				':stockSymbol'	=> $symbolUpper
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $searchQuery); //Debug
			$rsGetMemberData->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		//Start some resutls counters
		$fundsCnt	= 0;
		$heldCnt 	= 0;
		
		//
		$aCurHolders['desc'] = 'Array: $aCurHolders; Members that have held the searched stock';
		
		while($memberData = $rsGetMemberData->fetch(PDO::FETCH_ASSOC)){
			
			if($memberData['totalShares'] > 0){
				
				
				
				//Get member data from function
				$fMember = get_member($mLink, $memberData['member_id'], 'all');
				
				
				$aHolders['members'][$memberData['fund_id']] = array(
					'stockSymbol'	=> $memberData['stockSymbol'],
					'totalShares'	=> $memberData['totalShares'],
					'fundRatio'		=> number_format(($memberData['fundRatio']*100), 2).'%',
					'fund_symbol'	=> $memberData['fund_symbol'],
					'member_id'		=> $memberData['member_id']
					
				);
				
				
				$fundsCnt++;
			}
			
		}
		
		//Set array variable to hold the amount of funds that contain the searched stock.
		$aCurHolders['fundsCount'] = $fundsCnt;
		
		
		//Create a new array to reorder the previous results to group by MemberID
		foreach($aHolders['members'] as $fundID => $fundInfo){
			$aCurHolders['members'][$fundInfo['member_id']][$fundID] = $fundInfo;
			
			//get just a list of member ids to use in notifications
			if($fundInfo['member_id'] != $_SESSION['member_id']){
				$aMemberIds[$fundInfo['member_id']] = $fundInfo['member_id'];
			}
		}
		
		//01-03: END: Find members that own searched stock 
		
	}
	//00-01: END SEARCH QUERY 1 : STOCKS
	
	
	//START SEARCH QUERY 2
	
	//END SEARCH QUERY 2
	
	
	//START SEARCH QUERY 3 : COMMUNITY
	if($searchQuery == "3" || $searchQuery == "all"){
		
		//START: Search Forums Categories
		$sQuery = "
			SELECT cat_id, forum_id, cat_title, `cat_description`
			FROM ".$_SESSION['forum_categories_table']."
			WHERE MATCH(cat_title,cat_description) AGAINST('".$searchString."')
			
		";

		try{
			$rsSearch = $mLink->prepare($sQuery);
			$aValues = array();
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $searchQuery); //Debug
			$rsSearch->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$cnt = 0;
		
		while($search = $rsSearch->fetch(PDO::FETCH_ASSOC)){
				
				$aSearch['search']['Community']['Forum']['Categories'][$cnt] = array(
					'title'		=> $search['cat_title'],
					'subtext'	=> '<small>'.$search['cat_description'].'</small>',
					'url'		=> '?page=04-01-00-003&forum='.$search['forum_id'].'&cat='.$search['cat_id'].'',
					'special'	=> ''
				);
				
				$cnt++;
				$resultsCnt++;
		}
		//END: Search Forums Categories
		
		
		//START: Search Forums TOPICS
		$sQuery1 = "
			SELECT cat_id, topic_id, topic_title, topic_views , forum_id
			FROM ".$_SESSION['forum_topics_table']."
			WHERE MATCH(topic_title) AGAINST('".$searchString."')
			
		";

		try{
			$rsSearch = $mLink->prepare($sQuery1);
			$aValues = array();
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $searchQuery); //Debug
			$rsSearch->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$cnt = 0;
		
		while($search = $rsSearch->fetch(PDO::FETCH_ASSOC)){
				
				$aSearch['search']['Community']['Forum']['Topics'][$cnt] = array(
					'title'		=> $search['topic_title'],
					'subtext'	=> '<small>Click to view topic.</small>',
					'url'		=> '?page=04-01-00-004&forum='.$search['forum_id'].'&cat='.$serach['cat_id'].'&topic='.$serach['topic_id'].'',
					'special'	=> ''
				);
				
				$cnt++;
				$resultsCnt++;
		}
		//END: Search Forums TOPICS
		
		
		//START: Search Forums POSTS
		$sQuery2 = "
			SELECT p.post_id, p.cat_id, p.topic_id, p.post_content, t.topic_title, t.forum_id
			FROM ".$_SESSION['forum_posts_table']." AS p
			INNER JOIN ".$_SESSION['forum_topics_table']." AS t ON t.topic_id=p.topic_id
			WHERE MATCH(post_content) AGAINST('".$searchString."')
		";

		try{
			$rsSearch = $mLink->prepare($sQuery2);
			$aValues = array();
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $searchQuery); //Debug
			$rsSearch->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$cnt = 0;
		
		while($search = $rsSearch->fetch(PDO::FETCH_ASSOC)){
				
				$aSearch['search']['Community']['Forum']['Posts'][$cnt] = array(
					'title'		=> $search['topic_title'],
					'subtext'	=> '<small>'.substr($search['post_content'], 0, 100).'...</small>',
					'url'		=> get_post_url($mLink, $search['post_id']),
					'special'	=> ''
				);
				
				$cnt++;
				$resultsCnt++;
		}
		//END: Search Forums POSTS
		
		
	}
	//END SEARCH QUERY 3 :COMMUNITY
	
	
	//START SEARCH QUERY 4
	if($searchQuery == "4" || $searchQuery == "all"){
		
	}
	//END SEARCH QUERY 4
	
	
	//START SEARCH QUERY 5
	if($searchQuery == "5" || $searchQuery == "all"){
		
	}
	//END SEARCH QUERY 5
	
	//START SEARCH QUERY 6
	if($searchQuery == "6" || $searchQuery == "all" && $_SESSION['admin'] == '1'){
		//echo 'hello';
		/*$sQuery = "
			SELECT ticket_id, member_id, assigned_to, ticket_type, ticket_status, ticket_subject
			FROM ".$_SESSION['support_ticket_table']."
			WHERE MATCH(ticket_id, stock_ticker, fund_tickers) AGAINST('".$searchString."')
			
		";*/
		
                $regex = '#\((([^()]+|(?R))*)\)#';
		if (preg_match_all($regex, $searchString ,$matches)) {
			$searchData = implode(' ', $matches[1]);
		} else {
			//no parenthesis
			$searchData = $searchString;
		}
		$symbolUpper = strtoupper($searchData);
            
		$sQuery = "
			SELECT ticket_id, member_id, assigned_to, ticket_type, ticket_status, ticket_subject, description
			FROM ".$_SESSION['support_ticket_table']."
			WHERE ticket_id LIKE '".$searchString."' OR ticket_subject LIKE '%".$searchString."%' OR stock_ticker LIKE '".$symbolUpper."' OR description LIKE '%".$searchString."%' OR description LIKE '%".$symbolUpper."%'
			
		";

		try{
			$rsSearch = $mLink->prepare($sQuery);
			$aValues = array();
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $searchQuery); //Debug
			$rsSearch->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$cnt = 0;
		
		while($search = $rsSearch->fetch(PDO::FETCH_ASSOC)){
			
			$tQuery = "
				SELECT *
				FROM ".$_SESSION['lists_options_table']."
				WHERE list_id='5' AND value=:ticket_status
			";
			try{
				$rsStatus = $mLink->prepare($tQuery);
				$aValues = array(':ticket_status' => $search['ticket_status']);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $searchQuery); //Debug
				$rsStatus->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$status = $rsStatus->fetch(PDO::FETCH_ASSOC);
			
			$ticketStatus = $status['label'];
			
			switch($ticketStatus){
				case 'Closed'	: $ticketStatus = '<span class="label label-danger">'.$ticketStatus.'</span>';break;	
				case 'Open'		: $ticketStatus = '<span class="label label-success">'.$ticketStatus.'</span>';break;
				default			: $ticketStatus = '<span class="label label-info">'.$ticketStatus.'</span>';break;
			}
			
			$aSearch['search']['Support']['Temp']['Corporate Actions'][$cnt] = array(
				'title'		=> 'Ticket: '.$search['ticket_id'].' | '.$ticketStatus,
				'subtext'	=> $search['ticket_subject'].'<br /><small>'.substr($search['description'],0, 60).'</small>',
				'url'		=> '?page=08-01-cc-903&ticket='.$search['ticket_id'],
				'special'	=> ''
			);
			
			$cnt++;
			$resultsCnt++;
		
		}
		
		
		/*$aSearch['search']['Support']['Support Tickets']['General Help'][1] = array(
			'title'		=> 'Ticket 1050',
			'subtext'	=> '<small>Some Sub Text</small>',
			'url'		=> 'link',
			'special'	=> 'special'
		);
		
		$aSearch['search']['Support']['Support Tickets']['BETA System'][1] = array(
			'title'		=> 'Ticket 1050',
			'subtext'	=> '<small>Some Sub Text</small>',
			'url'		=> 'link',
			'special'	=> 'special'
		);*/
		
	}
	//END SEARCH QUERY 6
	
	//Figure out time
	$endTime = round(microtime(true) * 1000);
		
	$totalTime = (($endTime - $startTime) / 1000);
	
	$aSearch['time'] = array(
		'start_time'	=> $startTime,
		'end_time'		=> $endTime,
		'total_time'	=> $totalTime
	);
	
	$aSearch['results'] = number_format($resultsCnt, 0, ',', '');
			
}

//+---------------------------------------------------------------+
//|			PROCESS 2-2 : GET HISTORICAL DATA FOR STOCK(YQL) 	  |
//+---------------------------------------------------------------+

if($process == '2-2'){
	
	$symbol = $_REQUEST['symbol'];
	
	$startDate = '2009-09-11';
	$endDate = '2010-03-10';
	$symbol = strtoupper($symbol);
	
	//BUILD YQL URL
	$yqlURL = "http://query.yahooapis.com/v1/public/yql?q=select%20%2a%20from%20yahoo.finance.historicaldata%20where%20symbol%20in%20%28%27".$symbol."%27%29%20and%20startDate%20=%20%27".$startDate."%27%20and%20endDate%20=%20%27".$endDate."%27&env=store://datatables.org/alltableswithkeys&format=json";
		
	// Make call with cURL  
	$session = curl_init($yqlURL);  
	curl_setopt($session, CURLOPT_RETURNTRANSFER,true);  
	$json = curl_exec($session); 
	
	// Convert JSON to PHP object   
	$phpObj =  json_decode($json); 
	
	//print_r($phpObj);
	
	$cnt = 0;
	foreach($phpObj->query->results->quote as $quote){
			
		
		//Set Variables from returned results
		$symbol	= $quote->Symbol;
		$date	= $quote->Date;
		$open	= $quote->Open;
		$high	= $quote->High;
		$low	= $quote->Low;
		$close	= $quote->Close;
		$volume	= $quote->Volume;
		
		$year = substr($date, 0, 4);
		$month = substr($date, 5, 2);
		$day	= substr($date, 8, 2);
		
		$unixdate = mktime(0, 0, 0, $month, $day, $year);
		
		$aHistory[$cnt] = array(($unixdate*1000),$open,$high,$low,$close,$volume);/*,date('Y-m-d',$unixdate),$date*/
		
		$cnt++;
		
		$reverse = array_reverse($aHistory);
	}
	
	
	/*echo '<pre>';
	print_r($aHistory);
	echo '</pre>';*/
	
	$json = str_replace('"', '', json_encode($reverse));
	
	print_r($json);
}

//+---------------------------------------------------------------+
//|			PROCESS 2: GET HISTORICAL DATA FOR STOCK(XIGNITE) 	  |
//+---------------------------------------------------------------+

if($process == '2'){
	
	$symbol = $_REQUEST['symbol'];
	
	$startDate = date('m/d/Y', strtotime('-2 year'));
	//echo $startDate;
	$endDate = date('m/d/Y');
	$symbol = strtoupper($symbol);
	
	//BUILD URL
	$yqlURL = "http://192.168.111.213/feed/historicalStockRangeLookup.php?symbol=".$symbol."&StartDate=".$startDate."&EndDate=".$endDate."";
		
	// Make call with cURL  
	$session = curl_init($yqlURL);  
	curl_setopt($session, CURLOPT_RETURNTRANSFER,true);  
	$json = curl_exec($session); 
	
	// Convert JSON to PHP object   
	$phpObj =  json_decode($json); 
	
	$aQuotes = $phpObj->GlobalQuotes;
	
	foreach($aQuotes as $key=>$aObj){
		$aQuotes[$key] = array(
			'Date'		=> $aObj->Date,
			'Open'		=> $aObj->Open,
			'High' 		=> $aObj->High,
			'Low'		=> $aObj->Low,
			'Close'		=> $aObj->LastClose,
			'Volume'	=> $aObj->Volume
		);	
	}

	$cnt = 0;
	foreach($aQuotes as $key=>$aQuote){
			
		
		//Set Variables from returned results
		$date	= $aQuote['Date'];
		$open	= $aQuote['Open'];
		$high	= $aQuote['High'];
		$low	= $aQuote['Low'];
		$close	= $aQuote['Close'];
		$volume	= $aQuote['Volume'];
		
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


//+---------------------------------------------------------------+
//|					PROCESS 3 : AUTOCOMPLETE				 	  |
//+---------------------------------------------------------------+

if($process == "3"){
	
	$searchString = preg_replace('#[^a-z 0-9?!$]#i', '', $_REQUEST['search']);
	$symbolUpper = strtoupper($_REQUEST['search_term']);
	
	$sQuery = "
		SELECT symbol, company
		FROM ".$stockTable."
		WHERE symbol LIKE '".$symbolUpper."%' AND traded='1'
	";

	try{
		$rsSearch = $mLink->prepare($sQuery);
		$aValues = array();
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $searchQuery); //Debug
		$rsSearch->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$data = array();
	
	// Loop through results, although there should only be ONE match.
	while($search = $rsSearch->fetch(PDO::FETCH_ASSOC)){
		array_push($data, '('.$search['symbol'].') '.$search['company']);
	}
	
	//echo $error;
	echo json_encode($data);
	
}

?>