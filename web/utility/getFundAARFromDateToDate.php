<?php
/*
The purpose of this script is to calculate the AAR for a given fund between two dates.
*Note - this must be run in a web browser.
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
	<title>Date to Date AAR Calculator</title>
</head>
<html>
<body style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">
';

// Get all the managers/funds with composites
$query = "
	SELECT m.username, m.name_first, m.name_last, mf.fund_symbol, mf.fund_id, mf.inception_date
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
			<td colspan = "2"><h1>Date to Date AAR Calculator</h1></td>
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
						inceptions.push("'.$fund["inception_date"].'");
					</script>
					<option value="'.$fund["fund_id"].'" '.($fund["fund_id"] == $_REQUEST['fund_id'] ? "selected" : "").'>'.$fund["name_first"].' '.$fund["name_last"].' ('.$fund["username"].':'.$fund["fund_symbol"].')</option>
	';
}

echo '
				</select>
			<td>
		</tr>
		<tr>
			<td>Start Date:</td>
			<td><input type="text" name="start_date" id="start_date" size="10" value="'.($_REQUEST['start_date'] != "" ? $_REQUEST['start_date'] : "").'"> YYYYMMDD <span style="font-size: 8pt">(Defaults to Inception)</span></td>
		</tr>
		<tr>
			<td>End Date:</td>
			<td><input type="text" name="end_date" id="end_date" size="10" value="'.($_REQUEST['end_date'] != "" ? $_REQUEST['end_date'] : "").'"> YYYYMMDD <span style="font-size: 8pt">(Defaults to Today)</span></td>
		</tr>
		<tr>
			<td colspan = "2"><p style="text-align: center"><input type="submit"></p></td>
		</tr>
	</table>

</form>
';

// Objects to hold/display output values built in javascript
//echo '
//<p id="ids"></p>
//<p id="dates"></p>
//';

echo '
<script>

<!-- Function to seek and return the index value for the selected fund_id -->
function seekIndex(id) {
	return id == document.getElementById("fund_id").value;
}

<!-- Function to grab the inception date stored at the same index as the fund_id in the parallel (inception dates) array -->
function setDates() {
	document.getElementById("start_date").value = inceptions[fund_ids.findIndex(seekIndex)];
	document.getElementById("end_date").value = "'.date('Ymd').'";

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

if (isset($_REQUEST['fund_id']) || $_REQUEST['fund_id'] != ""){

	// Assign passed values
	$fund_id = $_REQUEST['fund_id'];
	$start_date = $_REQUEST['start_date'];
	$end_date = $_REQUEST['end_date'];

	// Verify that the passed date values are valid dates
	$start_year = substr($start_date, 0, 4);
	$start_month = substr($start_date, 4, 2);
	$start_day = substr($start_date, 6, 2);

	$end_year = substr($end_date, 0, 4);
	$end_month = substr($end_date, 4, 2);
	$end_day = substr($end_date, 6, 2);

	// Make sure it's 8 characters, all numbers, and is a valid date
	if (strlen($start_date) != 8 || !is_numeric($start_date) || !checkdate($start_month, $start_day, $start_year)){

		echo "Start Date is not valid (Must be YYMMDD) - Aborted!";
		die();

	}
	if (strlen($end_date) != 8 || !is_numeric($end_date) || !checkdate($end_month, $end_day, $end_year)){

		echo "End Date is not valid (Must be YYMMDD) - Aborted!";
		die();

	}

	// I must get the most recent values to the dates passed, which means I want the NAV for the date passed or the most recent date before.
	// As such I cannot use an IN clause as that will only find exact matches, so I need to query each passed date individually.
	// And given that the data may be found in either the marketaco database or the portfolio database, I must query for each up to 4 times.

	// Get the NAV for the passed start date from marketaco
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

		// Get the NAV for the passed start date from portfolio
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

	// Now get the NAV for the passed end date from marketaco
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

		// Get the NAV for the passed start date from portfolio
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
	$startValue = ($start_nav * 100000); // NAV * 100K (shares)
	$endValue = ($end_nav * 100000);

	// Calculate the number of days in the period
	$startDate = new DateTime($start_date);
	$endDate = new DateTime($end_date);
	$days = $endDate->diff($startDate)->format("%a");

	// Divide 365 by the number of days to get the exponent
	$exp = (365/$days);

	// Divide the ending value by the starting value
	$base = ($endValue/$startValue);

	// Multiply that by the exponent and subtract 1
	$rawAAR = ((pow($base, $exp))-1);

	// Multiply that by 100 to get the AAR percentage
	$AAR = ($rawAAR*100);

//echo $AAR."<br>";

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
	ORDER BY unix_date ASC";
	try{
		$rsSP = $mLink->prepare($query);
		$aValues = array(
			':start_date' => $start_year.'-'.$start_month.'-'.$start_day,
			':end_date' => $end_year.'-'.$end_month.'-'.$end_day
		);
//			':date'         => date('Y-m-', $fund['unix_date']).'%'
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
			$start_sp = $sp['close'];
		}else{
			$end_sp = $sp['close'];
		}

		$cnt++;

	}

//echo $start_sp."<br>";
//echo $end_sp."<br>";

	// Now calculate S&P AAR same period
//	$startValue = ($start_sp * 100000); // NAV * 100K (shares)
//	$endValue = ($end_sp * 100000);

	// Calculate the number of days in the period
	$startDate = new DateTime($start_date);
	$endDate = new DateTime($end_date);
	$days = $endDate->diff($startDate)->format("%a");

	// Divide 365 by the number of days to get the exponent
	$exp = (365/$days);

	// Divide the ending value by the starting value
	$base = ($end_sp/$start_sp);

	// Multiply that by the exponent and subtract 1
	$rawAAR = ((pow($base, $exp))-1);

	// Multiply that by 100 to get the AAR percentage
	$spAAR = ($rawAAR*100);

//echo $spAAR."<br>";

	// Output the results
	echo '
	<table>
		<tr>
			<td>Fund AAR Between '.$startDate->format('m/d/Y').' and '.$endDate->format('m/d/Y').':</td>
			<td>&nbsp;&nbsp;</td>
			<td>'.$AAR.' <strong>('.round($AAR, 2).'%)</strong></td>
		</tr>
		<tr>
			<td>S&P500TR AAR Between '.$startDate->format('m/d/Y').' and '.$endDate->format('m/d/Y').':</td>
			<td>&nbsp;&nbsp;</td>
			<td>'.$spAAR.' <strong>('.round($spAAR, 2).'%)</strong></td>
		</tr>
	</table>
	';

}

echo '
</body>
</html>
';

?>