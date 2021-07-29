<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

// Parse passed arguments string to $_REQUEST array (i.e. "first=1&second=2&third=3" -> $_REQUEST['first'] = 1, etc.)
parse_str($argv[1], $_REQUEST);

// Help screen if they pass "help" as a parameter
if (isset($_REQUEST['help'])){
	echo
'
Valid Parameters:

pricefunds:	Defaults to "yes".  Passing "no", "0", or "false" will skip fund pricing.
delay:		Defaults to 3600 seconds (1 hour).  Pass it a numeric value equal to the number of seconds to delay processing once maxDate has incremented.
pause:		Defaults to 1 second.  Pass it a numeric value equal to the number of seconds to pause between daemon requests.
help:		Display this message.

example:	price-funds.php "pricefunds=no&delay=3600&pause=2".  Passing no value(s) assumes defaults.

';
	die(); // Just display message and quit
}

// Determine whether to price the funds or not
$priceFunds = true;
if (isset($_REQUEST['pricefunds']) && ($_REQUEST['pricefunds'] == 0 || strtoupper($_REQUEST['pricefunds']) == "NO" || $_REQUEST['pricefunds'] == false)){
	$priceFunds = false;
}

// Set the delay and pause values
$delay = 3600;
if (isset($_REQUEST['delay']) && is_numeric($_REQUEST['delay'])){
	$delay = $_REQUEST['delay'];
}
$pause = 1;
if (isset($_REQUEST['pause']) && is_numeric($_REQUEST['pause'])){
	$pause = $_REQUEST['pause'];
}

// Grab a port to start on randomly
$port = rand(22200, 22249);

//Start session
session_start();
$_SESSION['base_url'] = "alpha.marketocracy.com/";

//Get site functions
require("/var/www/html/".$_SESSION['base_url']."web/includes/system-debug-functions.php");

// Connect to database
require("/var/www/html/".$_SESSION['base_url']."secure/dbconnect.php");

//Get site functions
require("/var/www/html/".$_SESSION['base_url']."web/includes/system-functions.php");

//Get global settings
require("/var/www/html/".$_SESSION['base_url']."web/includes/system-global.php");

//print_r($_SESSION);

// Try for up to 12 hours, then give up
for ($try = 1; ; $try++){

	// Get the maxDate and the date of the last processing
	$query = "
		SELECT maxdate, process_date
		FROM ".$_SESSION['fund_maxdate_table']."
	";
	$rsMaxDate = $mLink->prepare($query);
	$rsMaxDate->execute();
	$maxDate = $rsMaxDate->fetch(PDO::FETCH_ASSOC);

	// If the maxDate has changed, break out of the loop and start the processing, otherwise wait 15 minutes and check again
	if ($maxDate['maxdate'] > $maxDate['process_date']){
		break;
	}else{
		// If we've been trying for 12 hours, give up.
		if ($try == 51){
			/////////// Fire off an email here!  Need a generic email function that we just pass the content to.


$to = 'jeff.saunders@marketocracy.com';
$subject = "Price Funds Failed!";
$message = 'Price Funds process failed to complete in 12 hours.';
// To send HTML mail, the Content-type header must be set
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

// Additional headers
$headers .= 'From: Portfolio CRON Processes <root@portfolio.marketocracy.com>' . "\r\n";
//$headers .= 'Bcc: jeff.saunders@marketocracy.com' . "\r\n";  //Testing
//$headers .= 'Bcc: jeff.saunders@marketocracy.com, brandon.mccarthy@marketocracy.com' . "\r\n";  //Testing
//$headers .= 'Cc: someone@marketocracy.com' . "\r\n";
//$headers .= 'Bcc: someone.else@marketocracy.com, someone.outside@otherdomain.com' . "\r\n";

// Mail it
mail($to, $subject, $message, $headers);





			die();
		}
		sleep(900);  // 15 minutes
	}
}

// Wait an hour to let straggling processes, especially on other machines, to catch up
sleep($delay);

// Assign maxDate to session vars, clear and unixdate converted
// NOT SURE IF WE NEED BOTH, DELETE WHAT WE DON'T NEED
$_SESSION['max_date'] = $maxDate['maxdate'];
//$_SESSION['unix_max_date'] = trim(mktime(0, 0, 0, substr($maxDate['maxdate'], 4, 2), substr($maxDate['maxdate'], 6, 2), substr($maxDate['maxdate'], 0, 4)));

// Get the memberID and last login for everyone who has logged in within the past 6 months (182 days)
$query = "
	SELECT member_id, username, last_login
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

// loop through each member and price all their funds first, unless we say not to
// This way we'll know all their funds are fully priced by the time we go for their data
if ($priceFunds){
	$startTime = time();
	while ($members = $rsMembers->fetch(PDO::FETCH_ASSOC)){
	//echo $members['member_id']."\n";
		$_SESSION['username'] = $members['username'];  // Use session var for compatibility with some includes

		// Price their fund(s)
		$query = "priceManager|".$_SESSION['username'];

		// Include the API call
		include("/var/www/html/".$_SESSION['base_url']."web/includes/data-query-legacy.php");

		// Wait half a tick
		usleep(500000); // 1/2 second
	}
	// Test to see if it's been 5 minutes since the first "priceManager" was executed
	// Do this to give it enough time to finish pricing before going for the details
	$elapsedTime = time() - $startTime;
	// If not...
	if ($elapsedTime < 300){ // 5 minutes
		/// wait until it's been at least 5 minutes, then move on
		sleep(300 - $elapsedTime);
	}
}

// Now get the memberIDs again (PDO on MySQL won't allow the reuse of the first result set (FIX THIS ORACLE!)
$rsMembers->execute($aValues);
while ($members = $rsMembers->fetch(PDO::FETCH_ASSOC)){
//echo $members['member_id']."\n";
	$_SESSION['username'] = $members['username'];  // Use session var for compatibility with some includes

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
//print_r($aFunds);print_r($aFundDates);//die();

	// Step through the funds array and pull the data for yesterday for each
	foreach ($aFunds as $fund_id => $fund_symbol){
		// Check for the newest date we have and see if there is a gap from it to maxDate
		$query = "
			SELECT date, unix_date
			FROM ".$_SESSION['fund_pricing_table']."
			WHERE fund_id = :fundID
			ORDER BY unix_date DESC
			LIMIT 1
		";
		try {
			$rsLastDate = $mLink->prepare($query);
			$aValues = array(
				':fundID'	=> $fund_id
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
			$rsLastDate->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}

		$date = $rsLastDate->fetch(PDO::FETCH_ASSOC);
		// figure out the date for the first day we are missing and convert it to the format the query wants
		if ($rsLastDate->rowCount() < 1){
			$firstDate = $aFundDates[$fund_id]; // Inception Date
		}else{
			$firstDate = date('Ymd', strtotime(date('j-n-Y', $date['unix_date']).' +1 day'));
		}

		// If that date is before the maxDate, then we need to grab everything from that date to maxDate
		if ($firstDate <= $_SESSION['max_date']){

			// Assign dates for priceRun query
			$startDate = substr($firstDate, 0, 4)."-".substr($firstDate, 4, 2)."-".substr($firstDate, 6, 2);
			$endDate = substr($_SESSION['max_date'], 0, 4)."-".substr($_SESSION['max_date'], 4, 2)."-".substr($_SESSION['max_date'], 6, 2);

 			// Go get 'em
 			include("/var/www/html/".$_SESSION['base_url']."web/includes/get-fund-pricing.php");

 		}
		// Get aggregate stats for maxDate
		$query = "aggregateStatistics|".$_SESSION['username']."|".$fund_id."|".$fund_symbol."|".$_SESSION['max_date'];
echo $query."\n";

		// Set the port number for the API call
		if ($port == 22499){
			$port = 22000;
		}else{
			$port++;
		}

		// Include the API call
		include("/var/www/html/".$_SESSION['base_url']."web/includes/data-query-legacy.php");

		// Wait a tick
		sleep($pause);

		// Get alphabeta stats for maxDate
		$query = "alphaBetaStatistics|".$_SESSION['username']."|".$fund_id."|".$fund_symbol."|".$_SESSION['max_date'];
echo $query."\n";

		// Set the port number for the API call
		if ($port == 22499){
			$port = 22000;
		}else{
			$port++;
		}

		// Include the API call
		include("/var/www/html/".$_SESSION['base_url']."web/includes/data-query-legacy.php");

		// Wait a tick
		sleep($pause);

		// Get fund content details for maxDate
///////////////// Move this to it's own process once Kim has the maxStockDate query working!!!!!!!!!!

		$query = "positionDetail|".$_SESSION['username']."|".$fund_id."|".$fund_symbol."|".$_SESSION['max_date'];
echo $query."\n";

		// Set the port number for the API call
		if ($port == 22499){
			$port = 22000;
		}else{
			$port++;
		}

		// Include the API call
		include("/var/www/html/".$_SESSION['base_url']."web/includes/data-query-legacy.php");

		// Wait a tick
		sleep($pause);

		// Get the latest basic position stratification stats
		$query = "positionStratification|".$_SESSION['username']."|".$fund_id."|".$fund_symbol;
echo $query."\n";

		// Set the port number for the API call
		if ($port == 22499){
			$port = 22000;
		}else{
			$port++;
		}

		// Include the API call
		include("/var/www/html/".$_SESSION['base_url']."web/includes/data-query-legacy.php");

		// Wait a tick
		sleep($pause);

		// Get the latest style position stratification stats
		$query = "stylePositionStratification|".$_SESSION['username']."|".$fund_id."|".$fund_symbol;
echo $query."\n";

		// Set the port number for the API call
		if ($port == 22499){
			$port = 22000;
		}else{
			$port++;
		}

		// Include the API call
		include("/var/www/html/".$_SESSION['base_url']."web/includes/data-query-legacy.php");

		// Wait a tick
		sleep($pause);

		// Get the latest sector position stratification stats
		$query = "sectorPositionStratification|".$_SESSION['username']."|".$fund_id."|".$fund_symbol;
echo $query."\n\n";

		// Set the port number for the API call
		if ($port == 22499){
			$port = 22000;
		}else{
			$port++;
		}

		// Include the API call
		include("/var/www/html/".$_SESSION['base_url']."web/includes/data-query-legacy.php");

		// Wait a tick
		sleep($pause);
	}
}

// Update the process date in the maxdate table
$query = "
	UPDATE ".$_SESSION['fund_maxdate_table']."
	SET process_timestamp = UNIX_TIMESTAMP(),
		process_date = :process_date
	WHERE 1
";
try {
	$rsUpdate = $mLink->prepare($query);
	$aValues = array(
		':process_date'	=> $_SESSION['max_date']
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
	$rsUpdate->execute($aValues);
}
catch(PDOException $error){
	// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

//die("Done\n");

?>