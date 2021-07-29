<?php
/*
The purpose of this script is to provide a mechanism for updating positions data for all tracked funds in FOLIOfn.
The data to be entered would be a copy/paste of the "Holdings" table displayed on the "View Holdings" page for each fund on FOLIOfn.
The data needed is everything inside the white area, starting with, and including "Holdings:".  The totals row is not needed but including it will do no harm, so just grab everything in the white.
This script is designed to handle any excess information that may be (inadvertently, or not) pasted into the textarea, up to and including the entire screen's content = if you choose to just do "CTRL-A", then "CTRL-C".
*Note - this must be run in a web browser.
*/

// Define some system settings
date_default_timezone_set('America/New_York');

// Tell me when things go sideways
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Load debug functions
//require("/var/www/html/portfolio.marketocracy.com/web/includes/systemDebugFunctions.php");

// Connect to MySQL
require("/var/www/html/portfolio.marketocracy.com/secure/dbconnect.php");

// Start the page & build the form
echo '
<!DOCTYPE HTML>

<html>
<head>
	<title>Top 5 Holdings Tool</title>
</head>

<body>

<h1>Fund Positions Data Upload</h1>

<h3>The purpose of this facility is to provide a mechanism for updating positions data for all tracked funds in FOLIOfn.</h3>
<h3>The data to be entered would be a copy/paste of the "Holdings" table displayed on the "View Holdings" page for each fund on FOLIOfn.</h3>
<h3>The data needed is everything inside the white area, starting with and including "Holdings:".  The totals row is not needed but including it will do no harm, so just grab everything in the white box.</h3>
<h3>This form is designed to handle any excess information that may be (inadvertently, or not) pasted into the textarea, up to and including the entire screen\'s content, if you choose to just do "<em>CTRL-A</em>", then "<em>CTRL-C</em>" to grab the entire page (Recommended).</h3>


<form action="" name="top5" id="top5" method="POST">
	<h2>Paste Data:</h2>
	<textarea id="csv" name="csv" rows="10" cols="150" autofocus="autofocus"></textarea><br>
	<input type="submit" name="submit">
</form>

<br>
';

/////Need to add a DDLB of all the tracked funds (Managers at top, paid members at bottom) to select from
/////MUST select one or the form won't submit

//////////////BETTER IDEA - make sure the entire holdings content is copied and pasted, starting with the large, bold "Holdings:" in the upper-left, then glean the fund name form the value to it's right and query it against the folio_folio_names table to get the fund_id, just like I do in the script to read the downloaded performance data.



// Only do this if "submit" was clicked
if ($_REQUEST && $_REQUEST["submit"]){





// Define some vars
$date = date("Ymd");
$count = 0;
$aCSV = array();

// Create an array containing each line (one per element) of the pasted data
$aLines = explode("\n", $_REQUEST["csv"]);

//////////////
//echo "<pre>";
//echo "<h3>Debug*<br></h3>";
//echo "Pasted Data ---<br><br>";
//print_r($aLines);
//echo "</pre>";
//////////////

// Step through each line, one at a time
foreach ($aLines as $line) {

	// Assign each pasted cell to an array element
	$aCSV[$count][] = str_getcsv($line, "\t");  // Tab delimited

	// Look for the line starting with "Holdings:" and store off the FOLIOfn fund name
	if (substr($aCSV[$count][0][0], 0, 9) == "Holdings:"){
		$folio_name = substr($aCSV[$count][0][0], 10); // Everything from position 10 to the end
	}

	// Make sure new array has exactly 10 elements and does not start with "Symbol" - those are not valid position rows
	if (sizeof($aCSV[$count][0]) != 10 || $aCSV[$count][0][0] == "Symbol "){
		array_pop($aCSV);  // 86 it
	}else{
		$count++;
	}

}

// If we got nuttin', hara-kiri
if (sizeof($aCSV) < 1){
	die("No Valid Data Pasted - Aborting!");
}

//////////////
echo "<pre>";
echo "Extracted Data ---<br><br>";
echo "Folio Name: ".$folio_name."<br>";
echo "</pre>";
//////////////

// Start processing...

// Get Fund ID
$query = "
	SELECT fund_id
	FROM folio_folio_names
	WHERE folio_name = :folio
";
try{
	$rsFundID = $tLink->prepare($query);
	$aValues = array(
		':folio'	=> $folio_name
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	//echo $preparedQuery."\n";//die();
	$rsFundID->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}
$fundID = $rsFundID->fetchColumn();

//////////////
echo "<pre>";
echo "Fund ID: ".$fundID."<br>";
echo "</pre>";
//////////////

// If we don't get a fund ID there's no reason to continue
if ($fundID == ""){
	die("Unable to Acquire the FundID for '".$folio_name."' - Aborting!");
}

// Delete all the rows for this fund having this date, so we only have one (most recent) set of positions on any given date
$query = "
	DELETE FROM members_fund_positions
	WHERE fund_id = :fund_id
	AND date = :date
";
try{
	$rsDelete = $tLink->prepare($query);
	$aValues = array(
		':fund_id'	=> $fundID,
		':date'		=> $date
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	//echo $preparedQuery;//die();
	$rsDelete->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($pdo_log, "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

//////////////
//echo "<pre>";
//print_r($aCSV);
//echo "</pre>";
//////////////

// Step through each array...
foreach ($aCSV as $keyPrime=>$aPosition){

	// then sub-array
	foreach ($aPosition as $key=>$aValues){

//////////////
echo "<pre>";
echo "Row ".$keyPrime.":<br>";
print_r($aValues);
echo "</pre>";
//////////////

		// Assign raw values to vars
		$symbol				= $aValues[0];
		$company			= $aValues[1];
		$shares				= $aValues[2];
		$lastTradePrice		= $aValues[3];
		$totalValue			= $aValues[4];
		$weight				= $aValues[5];
		$percentChange		= $aValues[6];
		$perShareChange		= $aValues[7];
		$gainLoss			= $aValues[8];
		$percentGainLoss	= $aValues[9];

		// Change any negative values, wrapped in (), to be -{value} instead, e.g. (1.23%) -> -1.23%
		if (strstr($percentChange, '(') != false){
			$percentChange = "-".str_replace('(', '', str_replace(')', '', $percentChange));
		}

		if (strstr($perShareChange, '(') != false){
			$perShareChange = "-".str_replace('(', '', str_replace(')', '', $perShareChange));
		}

		if (strstr($gainLoss, '(') != false){
			$gainLoss = "-".str_replace('(', '', str_replace(')', '', $gainLoss));
		}

		if (strstr($percentGainLoss, '(') != false){
			$percentGainLoss = "-".str_replace('(', '', str_replace(')', '', $percentGainLoss));
		}

		// Insert the data
		$query = "
			INSERT INTO members_fund_positions (
				fund_id,
				member_id,
				date,
				unix_date,
				symbol,
				company,
				shares,
				last_trade_price,
				total_value,
				weight,
				percent_change,
				per_share_change,
				gain_loss,
				percent_gain_loss
			) VALUES (
				:fund_id,
				:member_id,
				:date,
				UNIX_TIMESTAMP(),
				:symbol,
				:company,
				:shares,
				:last_trade_price,
				:total_value,
				:weight,
				:percent_change,
				:per_share_change,
				:gain_loss,
				:percent_gain_loss
			)
		";
		try{
			$rsInsert = $tLink->prepare($query);
			// Sanitize data during assignment (drop $, #, etc.)
			$aValues = array(
				':fund_id'			 	=> $fundID,
				':member_id'			=> explode("-", $fundID)[0],
				':date'				 	=> $date,
				':symbol'			 	=> $symbol,
				':company'			 	=> $company,
				':shares'			 	=> str_replace(',', '', $shares) + 0, // +0 to cast as a number
				':last_trade_price' 	=> str_replace('$', '', str_replace(',', '', $lastTradePrice)) + 0,
				':total_value'		 	=> str_replace('$', '', str_replace(',', '', $totalValue)) + 0,
				':weight'			 	=> $weight,
				':percent_change'	 	=> str_replace('%', '', $percentChange) + 0,
				':per_share_change' 	=> str_replace('$', '', str_replace(',', '', $perShareChange)) + 0,
				':gain_loss'			=> str_replace('$', '', str_replace(',', '', $gainLoss)) + 0,
				':percent_gain_loss'	=> str_replace('%', '', $percentGainLoss) + 0
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			//echo $preparedQuery;//die();
			$rsInsert->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($pdo_log, "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			$aErrors[] = $error;
		}

	}

}

echo "
<h3>Done!</h3>
";


} // END - if submit clicked



// Close 'er up
echo "
</body>
</html>
";

?>
