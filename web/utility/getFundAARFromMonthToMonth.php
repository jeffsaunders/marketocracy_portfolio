<?php
/*
The purpose of this script is to calculate the AAR for a given fund between two dates (switched to month-ends).
*Note - this must be run in a web browser.
// Written by: Jeff Saunders 6/21/19
// Modified by: Jeff Saunders 6/24/19 - Added Composite fund AAR and accompanying S&P AAR for the same period; switched date range to MM/YYYY and used month closing values (month-to-month)
*/

// Define some system settings
date_default_timezone_set('America/New_York');
setlocale(LC_MONETARY, 'en_US.UTF-8');

// Tell me when things go sideways
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Connect to MySQL
require("/var/www/html/portfolio.marketocracy.com/secure/dbconnect.php");

// Load debug functions
require("/var/www/html/portfolio.marketocracy.com/web/includes/system-debug-functions.php");

// Set up the page
echo '
<!DOCTYPE html>

<head>
	<title>Month to Month AAR Calculator</title>
</head>
<html>
<body style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">
';

// Get all the managers/funds with composites
$query = "
	SELECT m.username, m.name_first, m.name_last, mf.fund_symbol, mf.fund_id, mf.unix_date AS inception_date
	FROM members m, members_fund mf, members_subscriptions ms
	WHERE mf.folio_cutover IS NOT NULL
	AND ms.active = 1
	AND ms.folio = 1
	AND mf.composite_fund = 1
	AND mf.member_id = ms.member_id
	AND m.member_id = mf.member_id
	ORDER BY m.name_last ASC, m.name_first ASC, mf.fund_symbol ASC
";
// That will pull in all members who are active on FOLIOfn
// Add "AND ms.product_id IN (comma delimited levels)" to specify specific membership levels
// ...or just = for one, e.g. "AND ms.product_id = 10"
try {
	$rsFunds = $mLink->prepare($query);
	$rsFunds->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($pdo_log, "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}
//dump_rs($rsFunds, 1); // 1 indicates html output

echo'
<!-- Set up arrays to hold fund_ids and inception dates -->
<script>
	var fund_ids = [];
	var inceptions = [];
</script>
';

// Display the form
echo '
<form action="" name="calcAAR" id="calcAAR" method="POST">
	<table>
		<tr>
			<td colspan = "2"><h1>Month to Month AAR Calculator</h1></td>
		</tr>
		<tr>
			<td>Select Fund:</td>
			<td>
				<select name="fund_id" id="fund_id" onChange="setDates();">
					<option value="">Select a Fund</option>
';

// Loop through the funds results to build the options
while ($fund = $rsFunds->fetch(PDO::FETCH_ASSOC)){
	echo '
					<!-- Push the values onto their respective arrays -->
					<script>
						fund_ids.push("'.$fund["fund_id"].'");
						inceptions.push("'.date('m/Y', $fund["inception_date"]).'"); <!-- Inception month -->
					</script>
					<option value="'.$fund["fund_id"].'" '.($fund["fund_id"] == $_REQUEST['fund_id'] ? "selected" : "").'>'.$fund["name_first"].' '.$fund["name_last"].' ('.$fund["username"].':'.$fund["fund_symbol"].')</option>
	';
}

echo '
				</select>
			<td>
		</tr>
		<tr>
			<td>Start Month:</td>
			<td><input type="text" name="start_date" id="start_date" size="10" value="'.($_REQUEST['start_date'] != "" ? $_REQUEST['start_date'] : "").'"> MM/YYYY <span style="font-size: 8pt">(Defaults to Model Inception Month)</span></td>
		</tr>
		<tr>
			<td>End Month:</td>
			<td><input type="text" name="end_date" id="end_date" size="10" value="'.($_REQUEST['end_date'] != "" ? $_REQUEST['end_date'] : "").'"> MM/YYYY <span style="font-size: 8pt">(Defaults to Model Last Month)</span></td>
		</tr>
		<tr>
			<td>Mutual Funds:</td>
			<td><input type="text" name="mutual_funds" id="mutual_funds" size="38" value="'.$_REQUEST['mutual_funds'].'"> Comma delimited list of real funds to compare to <span style="font-size: 8pt">(Leave empty for none)</span></td>
		</tr>
		<tr>
			<td colspan = "2"><p style="font-size: 8pt"><em>*Note - Fetching mutual fund data from remote source may take time, please be patient</em></p></td>
		</tr>
		<tr>
			<td colspan = "2"><p style="text-align: left"><input type="submit" value="Calculate AARs"></p></td>
		</tr>
	</table>

</form>
';

echo '
<script>

<!-- Function to seek and return the index value for the selected fund_id -->
function seekIndex(id) {
	return id == document.getElementById("fund_id").value;
}

<!-- Function to grab the inception date stored at the same index as the fund_id in the parallel (inception dates) array -->
function setDates() {
	document.getElementById("start_date").value = inceptions[fund_ids.findIndex(seekIndex)];
	document.getElementById("end_date").value = "'.date('m/Y', strtotime('last month')).'";

<!-- Test code to display array values -->
//	var x = document.getElementById("fund_id");
//	var y = document.getElementByID("start_date");
//	y.value = x.value.toUpperCase();

//	text = "<ul>";
//		for (i = 0; i < fund_ids.length; i++) {
//		text += "<li>" + i + " - " + fund_ids[i] + "</li>";
//	}
//	text += "</ul>";
//	document.getElementById("ids").innerHTML = text;

//	text = "<ul>";
//		for (i = 0; i < inceptions.length; i++) {
//		text += "<li>" + i + " - " + inceptions[i] + "</li>";
//	}
//	text += "</ul>";
//	document.getElementById("dates").innerHTML = text;

}

</script>
';

// --- Process Form Data

// If form was submitted start calc'in!
if (isset($_REQUEST['fund_id']) && $_REQUEST['fund_id'] != ""){

	// Assign passed values
	$fund_id	= $_REQUEST['fund_id'];
	$start_date	= date('Ymt', strtotime(substr($_REQUEST['start_date'], 0, 3).'01/'.substr($_REQUEST['start_date'], 3, 4)));
	$end_date	= date('Ymt', strtotime(substr($_REQUEST['end_date'], 0, 3).'01/'.substr($_REQUEST['end_date'], 3, 4)));

//echo $_REQUEST['start_date']."<br>";
//echo $start_date."<br>";
//echo $_REQUEST['end_date']."<br>";
//echo $end_date."<br>";

	// Verify that the passed date values are valid dates
	$start_year		= substr($start_date, 0, 4);
	$start_month	= substr($start_date, 4, 2);
	$start_day		= substr($start_date, 6, 2);

	$end_year		= substr($end_date, 0, 4);
	$end_month		= substr($end_date, 4, 2);
	$end_day		= substr($end_date, 6, 2);

	// Make sure each are 8 characters, all numbers, and is a valid date
	if (strlen($start_date) != 8 || !is_numeric($start_date) || !checkdate($start_month, $start_day, $start_year)){

		echo "Start Date is not valid (Must be MM/YYYY) - Aborted!";
		die();

	}
	if (strlen($end_date) != 8 || !is_numeric($end_date) || !checkdate($end_month, $end_day, $end_year)){

		echo "End Date is not valid (Must be MM/YYYY) - Aborted!";
		die();

	}

	// Also make sure the dates are not the same
	if ($start_date == $end_date){

		echo "Dates cannot be the same - Aborted!";
		die();

	}

	// Finally, make sure the end date is after the start date
	if ($end_date < $start_date){

		echo "End Month cannot be before Start Month - Aborted!";
		die();

	}


//--- Model

	// Calculate the model AAR ---
	// I must get the most recent values to the dates passed, which means I want the NAV for the date passed or the most recent date before.
	// As such I cannot use an IN clause as that will only find exact matches, so I need to query each passed date individually.
	// And given that the data may be found in either the marketaco database or the portfolio database, I must query for each up to 4 times.

	// Get the model NAV for the passed start date from marketaco
	$query = "
		SELECT (totalValue/shares) AS nav
		FROM folio_fund_pricing
		WHERE fund_id = :fund_id
		AND date <= :date
		ORDER BY date DESC
		LIMIT 1
	";
	try {
		$rsNAV = $tLink->prepare($query);
		$aValues = array(
			':fund_id' 	=> $fund_id,
			':date' 	=> $start_date
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		//echo $preparedQuery;//die();
		$rsNAV->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($pdo_log, "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}

	$start_nav = $rsNAV->fetchColumn();

	if ($start_nav == ""){ // Wasn't found in marketaco

		// Get the model NAV for the passed start date from portfolio
		$query = "
			SELECT (totalValue/shares) AS nav
			FROM members_fund_pricing
			WHERE fund_id = :fund_id
			AND date <= :date
			ORDER BY date DESC
			LIMIT 1
		";
		try {
			$rsNAV = $mLink->prepare($query);
			$aValues = array(
				':fund_id' 	=> $fund_id,
				':date' 	=> $start_date
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			//echo $preparedQuery;//die();
			$rsNAV->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($pdo_log, "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}

		$start_nav = $rsNAV->fetchColumn();

	}

	if ($start_nav == ""){ // Wasn't found in marketaco

		echo "Start Date out of range (before inception or after today) - Aborted!";
		die();

	}

//echo $start_nav."<br>";

	// Now get the model NAV for the passed end date from marketaco
	$query = "
		SELECT (totalValue/shares) AS nav
		FROM folio_fund_pricing
		WHERE fund_id = :fund_id
		AND date <= :date
		ORDER BY date DESC
		LIMIT 1
	";
	try {
		$rsNAV = $tLink->prepare($query);
		$aValues = array(
			':fund_id' 	=> $fund_id,
			':date' 	=> $end_date
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		//echo $preparedQuery;//die();
		$rsNAV->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($pdo_log, "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}

	$end_nav = $rsNAV->fetchColumn();

	if ($end_nav == ""){ // Wasn't found in marketaco

		// Get the model NAV for the passed start date from portfolio
		$query = "
			SELECT (totalValue/shares) AS nav
			FROM members_fund_pricing
			WHERE fund_id = :fund_id
			AND date <= :date
			ORDER BY date DESC
			LIMIT 1
		";
		try {
			$rsNAV = $mLink->prepare($query);
			$aValues = array(
				':fund_id' 	=> $fund_id,
				':date' 	=> $end_date
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			//echo $preparedQuery;//die();
			$rsNAV->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($pdo_log, "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}

		$end_nav = $rsNAV->fetchColumn();

	}

	if ($start_nav == ""){ // Wasn't found in marketaco

		echo "End Date out of range (before inception or after today) - Aborted!";
		die();

	}

//echo $end_nav."<br>";

	// Start calculating the AAR
	$startValue	= ($start_nav * 100000); // NAV * 100K (increase precision)
	$endValue	= ($end_nav * 100000);

	// Calculate the number of days in the period
	$startDate	= new DateTime($start_date);
	$endDate	= new DateTime($end_date);
	$days		= $endDate->diff($startDate)->format("%a");

	// Divide 365 by the number of days to get the exponent
	$exp		= (365 / $days);

	// Divide the ending value by the starting value
	$base		= ($endValue / $startValue);

	// Multiply that by the exponent and subtract 1
//	$rawAAR		= ((pow($base, $exp)) - 1);
	$rawAAR		= (($base ** $exp) - 1);

	// Multiply that by 100 to get the AAR percentage
	$mAAR		= ($rawAAR * 100);

//echo $mAAR."<br>";

	// Define_result output strings
	$model_label	= '<strong>Model</strong> Fund AAR Between '.$startDate->format('m/d/Y').' and '.$endDate->format('m/d/Y').':';
	$model_result	= $mAAR.' <strong>('.round($mAAR, 2).'%)</strong>';


//--- S&P over Model Date Range

	// Now get S&P data for the same period
	$stockIndex = '^SP500TR';

	// Start date AND end date in one query (woot!)
	$query = "
		SELECT close
		FROM `stock_index_history`
		WHERE `index`='^SP500TR'
		AND date=(SELECT date FROM `stock_index_history` WHERE `index`='^SP500TR' AND date <= :start_date ORDER BY unix_date DESC LIMIT 1)
		OR `index`='^SP500TR'
		AND date=(SELECT date FROM `stock_index_history` WHERE `index`='^SP500TR' AND date <= :end_date ORDER BY unix_date DESC LIMIT 1)
		ORDER BY unix_date ASC
	";
	try{
		$rsSP = $mLink->prepare($query);
		$aValues = array(
			':start_date'	=> $start_year.'-'.$start_month.'-'.$start_day,
			':end_date'		=> $end_year.'-'.$end_month.'-'.$end_day
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		//echo $preparedQuery;//die();
		$rsSP->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}

	$cnt = 0;
	while($sp = $rsSP->fetch(PDO::FETCH_ASSOC)){

		if ($cnt == 0){
			$start_sp	= $sp['close'];
		}else{
			$end_sp		= $sp['close'];
		}

		$cnt++;

	}

//echo $start_sp."<br>";
//echo $end_sp."<br>";

	// Now calculate S&P AAR same period
	$startValue	= ($start_sp * 10000); // Close * 10K to increase precision (carry all stored places over to the left of the decimal)
	$endValue	= ($end_sp * 10000);

	// Calculate the number of days in the period
	$startDate	= new DateTime($start_date);
	$endDate	= new DateTime($end_date);
	$days		= $endDate->diff($startDate)->format("%a");

	// Divide 365 by the number of days to get the exponent
	$exp		= (365 / $days);

	// Divide the ending value by the starting value
	$base		= ($endValue / $startValue);

	// Multiply that by the exponent and subtract 1
//	$rawAAR		= ((pow($base, $exp)) - 1);
	$rawAAR		= (($base ** $exp) - 1);

	// Multiply that by 100 to get the AAR percentage
	$spAAR		= ($rawAAR * 100);

//echo $spAAR."<br>";

	// Define_more result output strings
	$sp_label	= 'S&P500 TR Between '.$startDate->format('m/d/Y').' and '.$endDate->format('m/d/Y').':';
	$sp_result	= $spAAR.' <strong>('.round($spAAR, 2).'%)</strong>';

//--- Composite

	// Calculate the composite AAR ---
	// Need to get the composite start date and set start_date to the last day of that month if the passed start date is before inception.
	// Same for end date if composite ended before passed end date

	// Get the composite start and end (most recent) dates
	$query = "
		SELECT date, composite_calc
		FROM members_fund_composite
		WHERE fund_id = :fund_id
		AND (exclude <> 1 OR exclude IS NULL)
		ORDER BY unix_date ASC
	";
	try{
		$rsCompositeDates = $mLink->prepare($query);
		$aValues = array(
			':fund_id' 	=> $fund_id
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		//echo $preparedQuery;//die();
		$rsCompositeDates->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}

	// Step through the dates and assign values
	$first		= true;
	$aCompCalc	= array(); // Array to hold result set rows
	while($row = $rsCompositeDates->fetch(PDO::FETCH_ASSOC)){

		if ($first){
			$composite_start_date	= $row['date'];
			$first					= false;
		}
		$composite_end_date	= $row['date'];

		// Push result set row onto array
		$aCompCalc[]		= $row;

	}

//echo $composite_start_date."<br>";
//echo $composite_end_date."<br>";

//echo '<pre>';
//print_r($aCompCalc);
//echo '</pre>';

	// Calculate pseudo "NAVs" for the composites
	$cNAV = 10000000; // Seed a value high enough (10M) to increase precision (carry 8 decimal places over to the left of the decimal)
	foreach($aCompCalc as $key=>$aValue){
		$cNAV = (($aValue['composite_calc'] + 1) * $cNAV);  // Calculate the pseudo NAV
		$aCompCalc[$key]['NAV']	= $cNAV;  // Save it to the array
	}

//echo '<pre>';
//print_r($aCompCalc);
//echo '</pre>';

	// Figure out the valid composite start and end dates as related to the requested dates
	if ($start_date < $composite_start_date){
		$comp_start_date = $composite_start_date; // Set composite start to actual composite start date
	}else{
		$comp_start_date = $start_date; // Set composite start to requested start date
	}

	if ($end_date >= $composite_end_date){
		$comp_end_date = $composite_end_date; // Set composite end to actual composite end date
	}else{
		$comp_end_date = $end_date; // Set composite end to requested end date
	}

	if ($comp_end_date >= $comp_start_date){ // We have a valid date range

//echo $comp_start_date."<br>";
//echo $comp_end_date."<br>";

		// Get the starting and ending NAVs
		$start_nav	= $aCompCalc[array_search($comp_start_date, array_column($aCompCalc, 'date'))]['NAV'];
		$end_nav	= $aCompCalc[array_search($comp_end_date, array_column($aCompCalc, 'date'))]['NAV'];

//echo $start_nav."<br>";
//echo $end_nav."<br><br>";

		// Calculate the number of days in the period
		$compStartDate	= new DateTime($comp_start_date);
		$compEndDate	= new DateTime($comp_end_date);
		$days			= $compEndDate->diff($compStartDate)->format("%a");

		// Divide 365 by the number of days to get the exponent
		$exp			= (365 / $days);

		// Divide the ending value by the starting value
		$base			= ($end_nav / $start_nav);

		// Multiply that by the exponent and subtract 1
	//	$rawAAR			= ((pow($base, $exp)) - 1);
		$rawAAR			= (($base ** $exp) - 1);

		// Multiply that by 100 to get the AAR percentage
		$cAAR			= ($rawAAR * 100);
//echo $base."<br>";
//echo $exp."<br>";
//echo $rawAAR."<br>";
//echo $cAAR."<br>";

		// Define_result output strings
		$composite_label	= '<strong>Composite</strong> Fund AAR Between '.$compStartDate->format('m/d/Y').' and '.$compEndDate->format('m/d/Y').':';
		$composite_result	= $cAAR.' <strong>('.round($cAAR, 2).'%)</strong>';

//--- S&P over Composite Date Range

		// Now get S&P data for the same period
		$stockIndex		= '^SP500TR';

		// Split dates for index query (date formatted differently in that table for some inexplicable reason)
		$start_year		= substr($comp_start_date, 0, 4);
		$start_month	= substr($comp_start_date, 4, 2);
		$start_day		= substr($comp_start_date, 6, 2);

		$end_year		= substr($comp_end_date, 0, 4);
		$end_month		= substr($comp_end_date, 4, 2);
		$end_day		= substr($comp_end_date, 6, 2);

		// Start date AND end date in one query (woot!)
		$query = "
			SELECT close
			FROM `stock_index_history`
			WHERE `index`='^SP500TR'
			AND date=(SELECT date FROM `stock_index_history` WHERE `index`='^SP500TR' AND date <= :start_date ORDER BY unix_date DESC LIMIT 1)
			OR `index`='^SP500TR'
			AND date=(SELECT date FROM `stock_index_history` WHERE `index`='^SP500TR' AND date <= :end_date ORDER BY unix_date DESC LIMIT 1)
			ORDER BY unix_date ASC
		";
		try{
			$rsSP = $mLink->prepare($query);
			$aValues = array(
				':start_date'	=> $start_year.'-'.$start_month.'-'.$start_day,
				':end_date'		=> $end_year.'-'.$end_month.'-'.$end_day
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			//echo $preparedQuery;//die();
			$rsSP->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}

		$cnt = 0;
		while($sp = $rsSP->fetch(PDO::FETCH_ASSOC)){

			if ($cnt == 0){
				$start_sp	= $sp['close'];
			}else{
				$end_sp		= $sp['close'];
			}

			$cnt++;

		}

//echo $start_sp."<br>";
//echo $end_sp."<br>";

		// Now calculate S&P AAR same period
		$startValue	= ($start_sp * 10000); // Close * 10K to increase precision (carry all stored places over to the left of the decimal)
		$endValue	= ($end_sp * 10000);

		// Calculate the number of days in the period
		$startDate	= new DateTime($comp_start_date);
		$endDate	= new DateTime($comp_end_date);
		$days		= $endDate->diff($startDate)->format("%a");

		// Divide 365 by the number of days to get the exponent
		$exp		= (365 / $days);

		// Divide the ending value by the starting value
		$base		= ($endValue / $startValue);

		// Multiply that by the exponent and subtract 1
//		$rawAAR		= ((pow($base, $exp)) - 1);
		$rawAAR		= (($base ** $exp) - 1);

		// Multiply that by 100 to get the AAR percentage
		$spcAAR		= ($rawAAR * 100);

//echo $spcAAR."<br>";

		// Define_more result output strings
		$sp_comp_label	= 'S&P500 TR Between '.$startDate->format('m/d/Y').' and '.$endDate->format('m/d/Y').':';
		$sp_comp_result	= $spcAAR.' <strong>('.round($spcAAR, 2).'%)</strong>';

	}else{ // Composite not within date range

		// Define_result output strings for invalid date range
		$composite_label	= 'Composite Fund AAR Between '.$startDate->format('m/d/Y').' and '.$endDate->format('m/d/Y').':';
		$composite_result	= "No Composite data found within defined date range.";
		$sp_comp_label		= NULL; // Results in a blank line
		$sp_comp_result		= NULL;

	}


//--- Defined Mutual Funds

	// Get data for any mutual funds passed for comparison
	if (isset($_REQUEST['mutual_funds']) && $_REQUEST['mutual_funds'] != ""){

		// Define our AlphaVantage API Key
		$apikey = "N61VMD5KOL6ZZAB"; // Premium Key (smallest level - 30 calls per minute, Unlimited per day)

		// Explode the passed CSV string
		$aMFunds = explode(',', strtoupper($_REQUEST['mutual_funds']));

		// Initialize output arrays
		$aModelLabels	= array();
		$aModelResults	= array();
		$aCompLabels	= array();
		$aCompResults	= array();

		// Loop though them and request the data from AlphaVantage
		foreach($aMFunds as $key=>$symbol){

			// Wait 2 seconds for pacing
			sleep(2);

			// Get the requested data from AlphaVantage (JSON)
			$url = "https://www.alphavantage.co/query?function=TIME_SERIES_MONTHLY_ADJUSTED&symbol=".$symbol."&apikey=".$apikey;
			$json = file_get_contents($url);
			$aFundData = json_decode($json, true);
//echo "<pre>";print_r($aFundData);echo "</pre><br>";

/*
Returned data, after decoding, will look like the following:

Array
(
	[Meta Data] => Array
		(
			[1. Information] => Monthly Adjusted Prices and Volumes
			[2. Symbol] => MXXVX
			[3. Last Refreshed] => 2019-08-13
			[4. Time Zone] => US/Eastern
		)

	[Monthly Adjusted Time Series] => Array
		(
			[2019-08-13] => Array  //NOTE - First record is always the most recent to the date of the request, the rest are for the last trading day of the month
				(
					[1. open] => 29.2600
					[2. high] => 29.2600
					[3. low] => 27.9700
					[4. close] => 28.5600
					[5. adjusted close] => 28.5600
					[6. volume] => 0
					[7. dividend amount] => 0.0000
				)

			[2019-07-31] => Array
				(
					[1. open] => 30.2300
					[2. high] => 30.3200
					[3. low] => 28.7900
					[4. close] => 29.8600
					[5. adjusted close] => 29.8600
					[6. volume] => 0
					[7. dividend amount] => 0.0000
				)

...

			[1999-09-30] => Array
				(
					[1. open] => 10.7500
					[2. high] => 10.9400
					[3. low] => 10.0200
					[4. close] => 10.0600
					[5. adjusted close] => 5.0789
					[6. volume] => 0
					[7. dividend amount] => 0.0000
				)

		)

)

*/

//--- Model Period

			// Determine the start date for the mutual fund data for model comparison
			$AVtargetDate = strtotime($start_date);

			// Step through the array evaluating the date value
			foreach($aFundData['Monthly Adjusted Time Series'] as $date=>$aData){
//echo "<pre>".$date."<br>";print_r($aData);echo "</pre><br>";

				// If the date is <= to the target date we have a winner!
				if (strtotime($date) <= $AVtargetDate){
					$AVstart_date = $date;
					break;
				}
			}
//echo $AVstart_date."<br>";

			// Store the start value from the JSON/ARRAY
			$startValue	= ($aFundData['Monthly Adjusted Time Series'][$AVstart_date]['5. adjusted close']);
//echo $startValue."<br>";

			// Determine the end date for the mutual fund data model comparison
			$AVtargetDate = strtotime($end_date);
			foreach($aFundData['Monthly Adjusted Time Series'] as $date=>$aData){
//echo "<pre>".$date."<br>";print_r($aData);echo "</pre><br>";
				if (strtotime($date) <= $AVtargetDate){
					$AVend_date = $date;
					break;
				}
			}
//echo $AVend_date."<br>";

			$endValue	= ($aFundData['Monthly Adjusted Time Series'][$AVend_date]['5. adjusted close']);
//echo $endValue."<br>";

			// Calculate the number of days in the period
			$startDate	= new DateTime($AVstart_date);
			$endDate	= new DateTime($AVend_date);
			$days		= $endDate->diff($startDate)->format("%a");

			// Divide 365 by the number of days to get the exponent
			$exp		= (365 / $days);

			// Divide the ending value by the starting value
			$base		= ($endValue / $startValue);

			// Multiply that by the exponent and subtract 1
	//		$rawAAR		= ((pow($base, $exp)) - 1);
			$rawAAR		= (($base ** $exp) - 1);

			// Multiply that by 100 to get the AAR percentage
			$mfAAR		= ($rawAAR * 100);

//echo $mfAAR."<br>";

			// Define_more result output strings
			if ($startValue != ""){
				$modelLabel = '<a href="https://www.alphavantage.co/query?function=TIME_SERIES_MONTHLY_ADJUSTED&symbol='.$symbol.'&apikey='.$apikey.'" target="_blank" title="Click for RAW Data">'.$symbol.'</a> Between '.date('m/d/Y', strtotime($start_date)).' and '.date('m/d/Y', strtotime($end_date)).':';
//				array_push($aModelLabels, $symbol.' Between '.date('m/d/Y', strtotime($start_date)).' and '.date('m/d/Y', strtotime($end_date)).':');
				array_push($aModelLabels, $modelLabel);
				array_push($aModelResults, $mfAAR.' <strong>('.round($mfAAR, 2).'%)</strong>');
			}else{
				array_push($aModelLabels, $symbol.' Not Found/Invalid');
				array_push($aModelResults, NULL);
			}

//--- Composite period

			// Determine the start date for the mutual fund data for composite comparison
			$AVtargetDate = strtotime($comp_start_date);

			// Step through the array evaluating the date value
			foreach($aFundData['Monthly Adjusted Time Series'] as $date=>$aData){
//echo "<pre>".$date."<br>";print_r($aData);echo "</pre><br>";

				// If the date is <= to the target date we have a winner!
				if (strtotime($date) <= $AVtargetDate){
					$AVstart_date = $date;
					break;
				}
			}
//echo $AVstart_date."<br>";

			// Store the start value from the JSON/ARRAY
			$startValue	= ($aFundData['Monthly Adjusted Time Series'][$AVstart_date]['5. adjusted close']);
//echo $startValue."<br>";

			// Determine the end date for the mutual fund data model comparison
			$AVtargetDate = strtotime($comp_end_date);
			foreach($aFundData['Monthly Adjusted Time Series'] as $date=>$aData){
//echo "<pre>".$date."<br>";print_r($aData);echo "</pre><br>";
				if (strtotime($date) <= $AVtargetDate){
					$AVend_date = $date;
					break;
				}
			}
//echo $AVend_date."<br>";

			$endValue	= ($aFundData['Monthly Adjusted Time Series'][$AVend_date]['5. adjusted close']);
//echo $endValue."<br>";

			// Calculate the number of days in the period
			$startDate	= new DateTime($AVstart_date);
			$endDate	= new DateTime($AVend_date);
			$days		= $endDate->diff($startDate)->format("%a");

			// Divide 365 by the number of days to get the exponent
			$exp		= (365 / $days);

			// Divide the ending value by the starting value
			$base		= ($endValue / $startValue);

			// Multiply that by the exponent and subtract 1
	//		$rawAAR		= ((pow($base, $exp)) - 1);
			$rawAAR		= (($base ** $exp) - 1);

			// Multiply that by 100 to get the AAR percentage
			$mfcAAR		= ($rawAAR * 100);

//echo $mfcAAR."<br>";

			// Define_more result output strings
			if ($startValue != ""){
				array_push($aCompLabels, $symbol.' Between '.date('m/d/Y', strtotime($comp_start_date)).' and '.date('m/d/Y', strtotime($comp_end_date)).':');
				array_push($aCompResults, $mfcAAR.' <strong>('.round($mfcAAR, 2).'%)</strong>');
			}else{
				array_push($aCompLabels, $symbol.' Not Found/Invalid');
				array_push($aCompResults, NULL);
			}

		}
//print_r($aModelLabels);
//print_r($aModelResults);
//print_r($aCompLabels);
//print_r($aCompResults);

	}

//--- Output

	// Output the results
	echo '
	<br>
	<table>
		<tr>
			<td>'.$model_label.'</td>
			<td rowspan = "50">&nbsp</td>
			<td>'.$model_result.'</td>
		</tr>
		<tr>
			<td>'.$sp_label.'</td>
			<td>'.$sp_result.'</td>
		</tr>
	';
	for ($i = 0; $i < count($aModelLabels); $i++) {
		echo '
		<tr>
			<td>'.$aModelLabels[$i].'</td>
			<td>'.$aModelResults[$i].'</td>
		</tr>
		';
	}
	echo '
		<tr>
			<td colspan = "3"><hr></td>
		</tr>
		<tr>
			<td>'.$composite_label.'</td>
			<td>'.$composite_result.'</td>
		</tr>
		<tr>
			<td>'.$sp_comp_label.'</td>
			<td>'.$sp_comp_result.'</td>
		</tr>
	';
	for ($i = 0; $i < count($aCompLabels); $i++) {
		echo '
		<tr>
			<td>'.$aCompLabels[$i].'</td>
			<td>'.$aCompResults[$i].'</td>
		</tr>
		';
	}
	echo '
		<tr>
			<td colspan = "3"><h6><em>* Composite date range may be indicative of the fund\'s boundries (inception, etc.) within the requested date range</em></h6></td>
		</tr>
	</table>
	';
}

echo '
</body>
</html>
';

?>