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

// Define the index values
$aIndex = array();

// Dow Jones
$aIndex[0][0] = "index_djia";
$aIndex[0][1] = "DJIA";

// NASDAQ
$aIndex[1][0] = "index_nasdaq";
$aIndex[1][1] = "NASDAQ";

// S&P500
$aIndex[2][0] = "index_sp500";
$aIndex[2][1] = "S&amp;P";

// Get the closing numbers from NASDAQ (more reliable than YQL)
$curl	= curl_init("http://www.nasdaq.com/aspx/indexrowhtml.aspx");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$feed	= curl_exec($curl);

// Parse the values and loop through each
for ($index = 0; $index < sizeof($aIndex); $index++){

	// Pull out just the closing price from the string for each index
	$subString		= str_replace('&nbsp;', ' ', $feed);
	$subString		= strstr($subString, $aIndex[$index][1]);
	$subString		= strstr($subString, ' ');
	$subString		= substr($subString, 1);

	$indexPrice		= strstr($subString, ' ', true);

	// Now grab the change value
	$subString		= strstr($subString, '</span> ', true);
	$subString		= strstr($subString, '>');

	$indexChange	= substr($subString, 1);

	// If query succeeded (got a price greater than $100)
	if ($indexPrice > 100){ // Just skip if it's under $100

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
//			$operator		= "&nabla; ";
			$direction		= "&#9660;";
			$indexChangeNoOp = substr($indexChange, 1);
		}else{
			$statusColor 	= "#3cc051";
			$statusBar 		= "success";
			$operator		= "+";
//			$operator		= "&Delta; ";
			$direction		= "&#9650;";
			if (substr($indexChange, 0, 1) == '+'){
				$indexChangeNoOp = substr($indexChange, 1);
			}else{
				$indexChangeNoOp = $indexChange;
			}
		}

		// Remove the operator from the front of the string so we can perform some math

// 		$indexChangeNoOp = substr($indexChange, 1);

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
				SELECT ".$aIndex[$index][0]."
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
			$indexArray = explode('|', $Status[$aIndex[$index][0]]);
			$indexTime = $indexArray[5];
		}

		// String together all variables to store in DB as an array seperated by "|"
		$updateIndex = "".$indexPrice."|".$indexChange."|".$percentChange."|".$statusColor."|".$statusBar."|".$indexTime;

		// Update the database and bail
		$query = "
			UPDATE ".$_SESSION['system_feeds_table']."
			SET	".$aIndex[$index][0]." = :index,
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
	}
}

?>
