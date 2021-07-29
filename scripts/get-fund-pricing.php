<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

//Start session
session_start();

//Get site functions
require("../web/includes/system-debug-functions.php");

// Connect to database
require("../secure/dbconnect.php");

//Get site functions
require("../web/includes/system-functions.php");

//Get global settings
require("../web/includes/system-global.php");

//print_r($_SESSION);

// Run for up to 5 hours, then give up
for ($try = 1; ; $try++){

	// Get the maxDate and the date of the last processing
	$query = "
		SELECT maxdate, process_date
		FROM ".$_SESSION['fund_maxdate_table']."
	";
	$rsMaxDate = $mLink->prepare($query);
	$rsMaxDate->execute();
	$maxDate = $rsMaxDate->fetch(PDO::FETCH_ASSOC);

	// If the maxDate has changed, break out of the loop and start the processing, otherwise wait 30 minutes and check again
	if ($maxDate['maxdate'] > $maxDate['process_date']){
		break;
	}else{
		// If we've been trying for 5 hours, give up.
		if ($try == 10){
			/////////// Fire off an email here!  Need a generic email function that we just pass the content to.
			die();
		}
		sleep(1800);  // 30 minutes
	}
}

// Assign maxDate to session vars, clear and unixdate converted
// NOT SURE IF WE NEED BOTH, DELETE WHAT WE DON'T NEED
$_SESSION['max_date'] = $maxDate['maxdate'];
$_SESSION['unix_max_date'] = trim(mktime(0, 0, 0, substr($maxDate['maxdate'], 4, 2), substr($maxDate['maxdate'], 6, 2), substr($maxDate['maxdate'], 0, 4)));

// Get the memberID and last login for everyone who has logged in within the past 6 months (182 days)
$query = "
	SELECT member_id, last_login
	FROM ".$_SESSION['members_table']."
	WHERE last_login > :cutoff_date
	GROUP BY member_id
";
try {
	$rsMembers = $mLink->prepare($query);
	$aValues = array(
		':cutoff_date'	=> time() - (86400 * 182)
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
	$rsMembers->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

// loop through each member and price all their funds first
// This way we'll know all their funds are fully priced by the time we go for their data
$startTime = time();
while ($members = $rsMembers->fetch(PDO::FETCH_ASSOC)){
//echo $members['member_id']."\n";
	// Price their fund(s)
	$query = "priceManager|".$_SESSION['username'];

	// Grab a port to start on randomly
	$port = rand(22200, 22249);

	// Include the API call
	include("../web/includes/data-query-legacy.php");

	// Wait a tick
	sleep(1);
}
// Test to see if it's been 5 minutes since the first "priceManager" was executed
// Do this to give it enough time to finish pricing before going for the details
$elapsedTime = time() - $startTime;
// If not...
if ($elapsedTime < 3){ // 5 minutes
	/// wait until it's been at least 5 minutes, then move on
	sleep(3 - $elapsedTime);
}
//echo time() - $startTime."\n";

// Now get the memberIDs again (PDO on MySQL won't allow the reuse of the first result set (FIX THIS ORACLE!)
$rsMembers->execute($aValues);
while ($members = $rsMembers->fetch(PDO::FETCH_ASSOC)){
//echo $members['member_id']."\n";
// Get their funds

	$query = "
		SELECT fund_id, fund_symbol, inception_date
		FROM ".$_SESSION['fund_table']."
		WHERE member_id = :member_id
	";
	try {
		$rsFunds = $mLink->prepare($query);
		$aValues = array(
			':member_id' => $members['member_id']
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
		$rsFunds->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	// Step through them and assign them to an array ($key=fundID, $value=fundSymbol)
	$aFunds = array();
	while ($fund = $rsFunds->fetch(PDO::FETCH_ASSOC)){
		$aFunds[$fund['fund_id']] = $fund['fund_symbol'];
		// Store their inception date
		$aFundDates[$fund['fund_id']] = $fund['inception_date'];
	}
//print_r($aFunds);//die();

	// Step through the funds array and pull the data for each
	foreach ($aFunds as $fund_id => $fund_symbol){

		// check for the oldest date we have and see if there is a gap from the inception date to it
		$query = "
			SELECT date, unix_date
			FROM ".$_SESSION['fund_pricing_table']."
			WHERE fund_id = :fundID
			ORDER BY unix_date ASC
			LIMIT 1
		";
		try {
			$rsFirstDate = $mLink->prepare($query);
			$aValues = array(
				':fundID'	=> $fund_id
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
			$rsFirstDate->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}

		// If result set is empty we have no history at all for this fund, so grab it all
		if ($rsFirstDate->rowCount() < 1){


//// go get it all


//			$firstDate = $aFundDates[$fund_id];
		}else{
			$date = $rsDate->fetch(PDO::FETCH_ASSOC);
			$firstDate = $date['date'];

/// get inception date, then get the range from the API

		}






	// Find any gaps in the middle
		$query = "
			SELECT a, b
			FROM (
				SELECT a1.unix_date AS a, MIN(a2.unix_date) AS b
				FROM ".$_SESSION['fund_pricing_table']." AS a1
				LEFT JOIN members_fund_pricing AS a2 ON a2.unix_date > a1.unix_date
				WHERE a1.fund_id = :fundID
				AND a1.fund_id = a2.fund_id
				GROUP BY a1.unix_date
			) AS foo
			WHERE b > a + 86400
		";
		try {
			$rsGaps = $mLink->prepare($query);
			$aValues = array(
				':fundID'	=> $fund_id
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	//die($preparedQuery);
			$rsGaps->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
echo $fund_id."---------\n";
		while ($gaps = $rsGaps->fetch(PDO::FETCH_ASSOC)){
print_r($gaps);
			// Get the date range for each gap and issue a priceRun
		}
// look for any gaps between the newest date and maxDate

	}
}




die("Done\n");


























// get their funds with inception dates


		// Get all the fund IDs for this member
		$query = "
			SELECT fund_id, fund_symbol, inception_date
			FROM ".$_SESSION['fund_table']."
			WHERE member_id = :member_id
		";
		try {
			$rsFunds = $mLink->prepare($query);
			$aValues = array(
				':member_id' => $_SESSION['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
			$rsFunds->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		// Step through them and assign them to an array ($key=fundID, $value=fundSymbol)
		while ($fund = $rsFunds->fetch(PDO::FETCH_ASSOC)){
			$aFunds[$fund['fund_id']] = $fund['fund_symbol'];
			// Store their inception date, JIC we need it
			$aFundDates[$fund['fund_id']] = $fund['inception_date'];
		}
//print_r($aFunds);die();


// determine which dates we need to get here.......









		$query = "
			SELECT name_first, name_last, joined_timestamp, last_login
			FROM ".$_SESSION['members_table']."
			WHERE member_id = :member_id
			ORDER BY timestamp DESC
			LIMIT 1
		";
		try {
			$rsMember = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $_SESSION['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
			$rsMember->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$member = $rsMember->fetch(PDO::FETCH_ASSOC);


?>