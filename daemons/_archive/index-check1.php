<?php
//Start session
session_start();
///var/www/html/alpha.marketocracy.com/
// Connect to database
require("../../../secure/dbconnect.php");
//Get site functions
require("../../includes/system-functions.php");
//Get global settings
require("../../includes/system-global.php");


/*
loop X times then start over
assign url X
set a counter
	execute it
	check for $indexPrice = 0.00 or no reply (timeout)
		increment counter and loop to try again (up to 10 times)
	else
		Write values to table

wait 10 seconds

*/

// Define the index URLs
$aIndexURL = array();

// Dow Jones
$aIndexURL[0][0] = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20*%20FROM%20yahoo.finance.quote%20WHERE%20symbol%3D'INDU'&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
$aIndexURL[0][1] = "index_djia";

// Nasdaq
$aIndexURL[1][0] = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20*%20FROM%20yahoo.finance.quote%20WHERE%20symbol%3D'%5Eixic'&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
$aIndexURL[1][1] = "index_nasdaq";

// S&P500
$aIndexURL[2][0] = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20*%20FROM%20yahoo.finance.quote%20WHERE%20symbol%3D'%5Egspc'&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
$aIndexURL[2][1] = "index_sp500";

// The following would get them all at once, but if any one fails it's harder to retry, etc., so do them one at a time instead
//"https://query.yahooapis.com/v1/public/yql?q=SELECT%20*%20FROM%20yahoo.finance.quote%20WHERE%20symbol%20in%20('INDU','%5Eixic','%5Egspc')&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";

// Let's get started (forever)
$indexCounter = 0;
while (true){ // Run forever
	// Run only if the market is open
	if (isMarketOpen(time())){  // in system-functions.php
		for ($try = 0; $try < 10; $try++){
//			$yqlURL = $aIndexURL[$indexCounter];
			// Make Yahoo Finance YQL call with cURL
			$session = curl_init($aIndexURL[$indexCounter][0]);
			curl_setopt($session, CURLOPT_RETURNTRANSFER,true);
			$json = curl_exec($session);
			// Convert JSON to PHP object
			$phpObj =  json_decode($json);

			// Set variables from returned results
			foreach($phpObj->query->results as $index){
				$indexName		= $index->Name;
				$indexChange	= $index->Change;
				$indexPrice		= $index->LastTradePriceOnly;
			}

			// If the query succeeded (got a price other then "0.00")
			if ($indexPrice != "0.00") {

				// Check to see if $indexChange is positive or negative (for styling)
				if(strpos(substr($indexChange, 0, 1), '+') !== FALSE){
					$statusColor 	= "#3cc051";
					$statusBar 		= "success";
					$operator		= "+";
				}else{
					$statusColor 	= "#ed4e2a";
					$statusBar 		= "danger";
					$operator		= "-";
				}

				// Remove the operator from the front of the string so we can perform some math
				$indexChangeNoOp = substr($indexChange, 1);

				// Calculate the percent change and round it off to the hundreths place
				$percentChange = round(($indexChangeNoOp/$indexPrice)*100, 2);

				// Round the Index change to the hundreths place
				$indexChange = round($indexChange, 2);

				// Add operator back to the front of the string (Rounding removes this)
				if ($operator != "-"){
					$indexChange = "".$operator."".$indexChange."";
				}

				// Format the Index Price to the hundreths place and add commas
				$indexPrice = number_format($indexPrice, 2, '.', ',');

				// String together all variables to store in DB as an array seperated by "|"
				$updateIndex = "".$indexPrice."|".$indexChange."|".$percentChange."|".$statusColor."|".$statusBar."|".date('M j g:i:s A T');

				// Update the database
				$query = "
					UPDATE ".$_SESSION['system_feeds_table']."
					SET	".$aIndexURL[$indexCounter][0]." = :index,
						timestamp = UNIX_TIMESTAMP()
				";
				try {
					$rsUpdate = $mLink->prepare($query);
					$aValues = array(
						':index'	=> $updateIndex,
					);
					// Prepared query - for error logging and debugging
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
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
	$indexCounter = ($indexCounter == 2 ? 0 : $indexCounter++);

	// Wait 10 seconds then do the next one
	sleep(10);
}

?>