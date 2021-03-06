<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

//Start session
session_start();

//Get site functions
require("/var/www/html/portfolio.marketocracy.com/web/includes/system-debug-functions.php");

// Connect to database
require("/var/www/html/portfolio.marketocracy.com/secure/dbconnect.php");

//Get site functions
require("/var/www/html/portfolio.marketocracy.com/web/includes/system-functions.php");

//Get global settings
require("/var/www/html/portfolio.marketocracy.com/web/includes/system-global.php");

// Define the index URLs & column names
$aIndex = array();

// Dow Jones
$aIndex[0][0] = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20*%20FROM%20yahoo.finance.quote%20WHERE%20symbol%3D'INDU'&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
$aIndex[0][1] = "index_djia";
$aIndex[0][2] = "DJIA";

// NASDAQ
$aIndex[1][0] = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20*%20FROM%20yahoo.finance.quote%20WHERE%20symbol%3D'%5Eixic'&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
$aIndex[1][1] = "index_nasdaq";
$aIndex[1][2] = "NASDAQ";

// S&P500
$aIndex[2][0] = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20*%20FROM%20yahoo.finance.quote%20WHERE%20symbol%3D'%5Egspc'&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
$aIndex[2][1] = "index_sp500";
$aIndex[2][2] = "S&amp;P";

// NYSE
$aIndex[3][0] = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20*%20FROM%20yahoo.finance.quote%20WHERE%20symbol%3D'%5Enya'&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
$aIndex[3][1] = "index_nyse";
$aIndex[3][2] = "NYSE";

// Russell 2000
$aIndex[4][0] = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20*%20FROM%20yahoo.finance.quote%20WHERE%20symbol%3D'%5Erut'&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
$aIndex[4][1] = "index_rut";
$aIndex[4][2] = "RUT";

// Wilshire 5000
$aIndex[5][0] = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20*%20FROM%20yahoo.finance.quote%20WHERE%20symbol%3D'%5Ew5000'&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
$aIndex[5][1] = "index_w5000";
$aIndex[5][2] = "W5000";

// S&P500TR
$aIndex[6][0] = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20*%20FROM%20yahoo.finance.quote%20WHERE%20symbol%3D'%5ESP500TR'&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
$aIndex[6][1] = "index_sp500TR";
$aIndex[6][2] = "S&amp;P";

// The following would get them all at once, but if any one fails it's harder to retry, etc., so do them one at a time instead
//"https://query.yahooapis.com/v1/public/yql?q=SELECT%20*%20FROM%20yahoo.finance.quote%20WHERE%20symbol%20in%20('INDU','%5Eixic','%5Egspc','^nya')&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";

// Let's get started (forever)
$indexCounter = 0;
while (1){ // Run forever
//for ($x = 0; $x < 4; $x++){  // Uncomment to run once and quit

	// Run only if the market is open
	if (isMarketOpen(time(), $mLink, "indices")){  // "indices" tells it to run 2 hours longer
//if (1==1){  // Uncomment to run anytime
		for ($try = 0; $try < 10; $try++){
			// Make Yahoo Finance YQL call with cURL
			$curl	= curl_init($aIndex[$indexCounter][0]);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$json	= curl_exec($curl);
			// Convert JSON to PHP object
			$phpObj	= json_decode($json);

			// Set variables from returned results
			foreach($phpObj->query->results as $index){
				//$indexName		= $index->Name;
				$indexChange	= $index->Change;
				$indexPrice		= $index->LastTradePriceOnly;
			}

			// DJIA sometimes returns 0.00, 10.00, or 11.00 (10.00 especially right after closing) and NYSE sometimes returns 1.00
			// If we get one of those then go grab the values from NASDAQ (works for DJIA, NASDAQ, and S&P - no NYSE, RUT, or W5000)
			if ($indexPrice <= 11) {
				$curl	= curl_init("http://www.nasdaq.com/aspx/indexrowhtml.aspx");
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				$feed	= curl_exec($curl);

				// Pull out just the current price from the string
				$subString		= str_replace('&nbsp;', ' ', $feed);
				$subString		= strstr($subString, $aIndex[$indexCounter][2]);
				$subString		= strstr($subString, ' ');
				$subString		= substr($subString, 1);

				$indexPrice		= strstr($subString, ' ', true);

				// Now grab the change value
				$subString		= strstr($subString, '</span> ', true);
				$subString		= strstr($subString, '>');

				$indexChange	= substr($subString, 1);
//echo "NASDAQ\n";
			}

			// If either query succeeded (got a price other than "0.00", "1.00", "10.00", or "11.00")
			if ($indexPrice > 100){ // Just skip if it's under $100
//echo "YQL\n";
				// Check to see if $indexChange is positive or negative (for styling)
				if ($indexChange == 0){
					$statusColor 	= "#57b5e3";
					$statusBar 		= "info";
					$operator		= "";
					$direction		= "";
				}else if(substr($indexChange, 0, 1) == '-'){
					$statusColor 	= "#ed4e2a";
					$statusBar 		= "danger";
					$operator		= "-";
//					$operator		= "&nabla; ";
					$direction		= "&#9660;";
					$indexChangeNoOp = substr($indexChange, 1);
				}else{
					$statusColor 	= "#3cc051";
					$statusBar 		= "success";
					$operator		= "+";
//					$operator		= "&Delta; ";
					$direction		= "&#9650;";
					if (substr($indexChange, 0, 1) == '+'){
						$indexChangeNoOp = substr($indexChange, 1);
					}else{
						$indexChangeNoOp = $indexChange;
					}
				}

				// Remove the operator from the front of the string so we can perform some math

//				$indexChangeNoOp = substr($indexChange, 1);

				// Calculate the percent change and round it off to the hundreths place, and pad trailing zeros if needed
				$percentChange = number_format(round(($indexChangeNoOp/$indexPrice)*100, 2), 2);
				if ($indexChange == 0){
					$percentChange = "Unchanged";
				}else{
					$percentChange = $direction.$percentChange."%";
				}

				// Round the Index change to the hundreths place and pad trailing zeros if needed
				$indexChange = number_format(round($indexChange, 2), 2, '.', ',');

				// Add operator back to the front of the string (Rounding removes this)
				if ($operator != "-"){
					$indexChange = "".$operator."".$indexChange."";
				}

				// Format the Index Price to the hundreths place and add commas
				$indexPrice = number_format($indexPrice, 2, '.', ',');

				// Set the timestamp
				if (isMarketOpen(time(), $mLink, "none")){
					$indexTime = date('M j g:i:s A T');
				}else{
					$query = "
						SELECT ".$aIndex[$indexCounter][1]."
						FROM ".$_SESSION['system_feeds_table']."
						LIMIT 1
					";
					try{
						$rsStatus = $mLink->prepare($query);
						$rsStatus->execute();
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}

					$Status = $rsStatus->fetch(PDO::FETCH_ASSOC);

					// Blow it apart and get the last update date
					$indexArray = explode('|', $Status[$aIndex[$indexCounter][1]]);
					$indexTime = $indexArray[5];
				}

				// String together all variables to store in DB as an array seperated by "|"
//				$updateIndex = "".$indexPrice."|".$indexChange."|".$percentChange."|".$statusColor."|".$statusBar."|".date('M j g:i:s A T');
				$updateIndex = "".$indexPrice."|".$indexChange."|".$percentChange."|".$statusColor."|".$statusBar."|".$indexTime;

				// Update the database
				$query = "
					UPDATE ".$_SESSION['system_feeds_table']."
					SET	".$aIndex[$indexCounter][1]." = :index,
						timestamp = UNIX_TIMESTAMP()
				";
				try {
					$rsUpdate = $mLink->prepare($query);
					$aValues = array(
						':index'	=> $updateIndex,
					);
					// Prepared query - for error logging and debugging
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					//echo $preparedQuery;die();
					$rsUpdate->execute($aValues);
				}
				catch(PDOException $error){
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}

				break;
	 		}
		}
	}

	// Move on to the next index, start over if at the end
	if ($indexCounter == sizeof($aIndex) - 1){
		$indexCounter = 0;
	}else{
		$indexCounter++;
	}

	// Wait 10 seconds then do the next one
	sleep(10);
}

?>
