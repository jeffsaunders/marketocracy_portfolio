<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

// Parse passed arguments string to $_REQUEST array (i.e. "first=1&second=2&third=3" -> $_REQUEST['first'] = 1, etc.)
parse_str($argv[1], $_REQUEST);

// Help screen if they pass "help" as a parameter
if (isset($_REQUEST['help']) || empty($_REQUEST)){
	echo
'
Valid Parameters:

pricefunds:		Defaults to "yes".  Passing "no", "0", or "false" will skip fund pricing.
delay:			Defaults to 3600 seconds (1 hour).  Pass it a numeric value equal to the number of seconds to delay processing once maxDate has incremented.
pause:			Defaults to 0 seconds.  Pass it a numeric value equal to the number of seconds to pause between daemon requests.
duration:		Defaults to 182 days (6 months).  Pass it a numeric value equal to the number of days since last login to determine members to update, 0 = forever.
skip_maxDate:		Defaults to "no".  Passing "yes", "1", or "true" will skip the maxDate incrementation check.
skip_livePrice:		Defaults to "no".  Passing "yes", "1", or "true" will skip livePrice updating.
skip_allStats:		Defaults to "no".  Passing "yes", "1", or "true" will skip all statistical updating (aggregate, etc.).
start_id:		Defaults to "1".  Passing a member ID will skip everyone whose member ID is lower than the passed value. Useful for starting where you left off.
member_id:		Defaults to "".  Passing a member ID will force the system to only pull records for all the funds for that one member.
	  		Overrides start_id value. If blank it processes all members, respecting the start_id value.
//fund_id:		Defaults to "".  Passing a fund ID will force the system to only pull records for that particular fund. (DOES NOT WORK YET!)
//	  		Overrides start_id and member_id values. If blank it processes all funds for the specified member_id or all members, respecting the start_id value.
start_port:		Defaults to 52100 (API2).  Pass it a numeric value between 52000 and 52099 for API1 or between 52100 and 52499 for API2.
help:			Display this message.

example:		price-fund-history.php "pricefunds=no&delay=0&pause=0&duration=0&skip_maxDate=yes&skip_livePrice=yes&skip_allStats=yes&member_id=18&start_port=52100".
			Passing no specific value for any parameter, including not passing it at all, assumes its default.

';
	die(); // Just display message and quit
}

// Start the clock
$begin_time = time();

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
$pause = 0;
if (isset($_REQUEST['pause']) && is_numeric($_REQUEST['pause'])){
	$pause = $_REQUEST['pause'];
}
$duration = 182;
if (isset($_REQUEST['duration']) && is_numeric($_REQUEST['duration'])){
	$duration = $_REQUEST['duration'];
}
$skip_maxDate = false;
if (isset($_REQUEST['skip_maxDate']) && ($_REQUEST['skip_maxDate'] == 1 || strtoupper($_REQUEST['skip_maxDate']) == "YES" || $_REQUEST['skip_maxDate'] == true)){
	$skip_maxDate = true;
}
$skip_livePrice = false;
if (isset($_REQUEST['skip_livePrice']) && ($_REQUEST['skip_livePrice'] == 1 || strtoupper($_REQUEST['skip_livePrice']) == "YES" || $_REQUEST['skip_livePrice'] == true)){
	$skip_livePrice = true;
}
$skip_allStats = false;
if (isset($_REQUEST['skip_allStats']) && ($_REQUEST['skip_allStats'] == 1 || strtoupper($_REQUEST['skip_allStats']) == "YES" || $_REQUEST['skip_allStats'] == true)){
	$skip_allStats = true;
}
$start_id = 1;
if (isset($_REQUEST['start_id']) && is_numeric($_REQUEST['start_id'])){
	$start_id = $_REQUEST['start_id'];
}
$member_id = "";
if (isset($_REQUEST['member_id']) && is_numeric($_REQUEST['member_id'])){
	$member_id = $_REQUEST['member_id'];
	$start_id = 1;
}
//$fund_id = "";
//if (isset($_REQUEST['fund_id']) && is_numeric($_REQUEST['fund_id'])){
//	$fund_id = $_REQUEST['fund_id'];
//	$member_id = "";
//	$start_id = 1;
//}
// Grab a port to start on randomly
$start_port = rand(52100, 52499); // API2
if (isset($_REQUEST['start_port']) && is_numeric($_REQUEST['start_port']) && $_REQUEST['start_port'] >= 52000 && $_REQUEST['start_port'] <= 52499){
	$start_port = $_REQUEST['start_port'];
}
if ($start_port >= 52100){ // API2
	$stop_port = 52499;
}else{  // API
	$stop_port = 52099;
}
// Initialize current port
$port = $start_port;

//Start session
session_start();
$_SESSION['base_url'] = "portfolio.marketocracy.com/";

//Get site functions
require("/var/www/html/".$_SESSION['base_url']."web/includes/system-debug-functions.php");

// Connect to database
require("/var/www/html/".$_SESSION['base_url']."secure/dbconnect.php");

//Get site functions
require("/var/www/html/".$_SESSION['base_url']."web/includes/system-functions.php");

//Get global settings
require("/var/www/html/".$_SESSION['base_url']."web/includes/system-global.php");

//print_r($_SESSION);

// Try for up to 7 hours, then give up
for ($try = 1; ; $try++){

	// Get the maxDate and the date of the last processing
	$query = "
		SELECT maxdate, process_date
		FROM ".$_SESSION['fund_maxdate_table']."
	";
	$rsMaxDate = $mLink->prepare($query);
	$rsMaxDate->execute();
	$maxDate = $rsMaxDate->fetch(PDO::FETCH_ASSOC);

	// Bail now if we said to skip it
	if ($skip_maxDate){
		break;
	}

	// If the maxDate has changed, break out of the loop and start the processing, otherwise wait 15 minutes and check again
	if ($maxDate['maxdate'] > $maxDate['process_date']){
		break;
	}else{
		// If we've been trying for 12 hours, give up.
		if ($try == 53){
			/////////// Fire off an email here!  Need a generic email function that we just pass the content to.


$to = 'jeff.saunders@marketocracy.com';
$subject = "Price Funds History Failed!";
$message = 'Price Funds History process failed to complete in 12 hours.';
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

// Wait an hour, or however long specified, to let straggling processes, especially on other machines, catch up
sleep($delay);

// Assign maxDate to session vars, clear and unixdate converted
// NOT SURE IF WE NEED BOTH, DELETE WHAT WE DON'T NEED
$_SESSION['max_date'] = $maxDate['maxdate'];
//$_SESSION['unix_max_date'] = trim(mktime(0, 0, 0, substr($maxDate['maxdate'], 4, 2), substr($maxDate['maxdate'], 6, 2), substr($maxDate['maxdate'], 0, 4)));

// Get the memberID and last login for everyone who has logged in within the past X days (value passed and defined above)
if ($duration > 0){
	$query = "
		SELECT m.member_id, m.username, m.last_login, f.promote
		FROM ".$_SESSION['members_table']." m, ".$_SESSION['members_flags_table']." f
		WHERE m.member_id = f.member_id
		AND (last_login > :cutoff_date OR f.promote = 1)
		GROUP BY member_id
	";
}else{
	$query = "
		SELECT member_id, username, last_login
		FROM ".$_SESSION['members_table']."
		GROUP BY member_id
	";
}
try {
	$rsMembers = $mLink->prepare($query);
	$aValues = array(
		':cutoff_date'	=> time() - (86400 * $duration)
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

		// Set the port number for the API call
		if ($port == $stop_port){
			$port = $start_port;
		}else{
			$port++;
		}

		// Include the API call
		include("/var/www/html/".$_SESSION['base_url']."web/includes/data-query-legacy.php");

		// Wait half a tick
//		usleep(500000); // 1/2 second
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

$aMissingFirst = array();
$aMissingGaps = array();

//$requests = 0;
while ($members = $rsMembers->fetch(PDO::FETCH_ASSOC)){
//echo $members['member_id']."\n";
//if ($members['member_id'] > 2){break;}

//-----------------------------------------------------------------------
//Skip ones we've already done (remove this after initial imports)
//if ($members['member_id'] != 17){
//if ($members['member_id'] < 3895){
//	continue;
//}
//if ($members['member_id'] == 3900){
//	die();
//}
//-----------------------------------------------------------------------

	// Skip any memberIDs lower than the specified starting number
	if ($members['member_id'] < $start_id){
		continue;
	}

	// Grab ONLY this one member's funds if specified
	if ($member_id > 0 && $members['member_id'] != $member_id){
		continue;
	}

	// Quit once we pass the specified member
	if ($member_id > 0 && $members['member_id'] > $member_id){
		break;
	}

//	// Grab ONLY this one specific fund if specified
//	if ($fund_id > 0 && $members['fooooo'] != $fund_id){
//		continue;
//	}

	echo "\nProcessing Member ".$members['member_id']." (".$members['username'].")\n";
//if ($requests > 1000){
//	sleep(180);
//	$requests = 0;
//}

	$_SESSION['username'] = $members['username'];  // Use session var for compatibility with some includes

	// Get their funds
//	if ($fund_id > 0){
//		$query = "
//			SELECT fund_id, fund_symbol, inception_date
//			FROM ".$_SESSION['fund_table']."
//			WHERE fund_id = :fund_id
//			AND short_fund = 0
//			AND active = 1
//		";
//	}else{
		$query = "
			SELECT fund_id, fund_symbol, inception_date
			FROM ".$_SESSION['fund_table']."
			WHERE member_id = :member_id
			AND short_fund = 0
			AND active = 1
		";
//	}
	try {
		$rsFunds = $mLink->prepare($query);
//		$aValues = array(
//			':member_id'	=> $members['member_id'],
//			':fund_id'		=> $fund_id
//		);
		$aValues = array(
			':member_id'	=> $members['member_id']
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

	// Step through the funds array and pull the data for each
	// Price history must be done in three steps:
	//		one for between inception and the first day we have;
	//		one for between the last day we have and maxDate;
	//		and one for the gaps in between
	// Then we grab the Aggregate and AlphaBeta stats for maxDate
	foreach ($aFunds as $fund_id => $fund_symbol){
//echo $fund_id."---------\n";
		// First check for the oldest date we have and see if there is a gap from the inception date to it
		$query = "
			SELECT date, unix_date
			FROM ".$_SESSION['fund_pricing_table']."
			WHERE fund_id = :fundID
			ORDER BY unix_date ASC
		";
//			LIMIT 1  <- adding this hoses MySQL for some bizarre reason

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
		$gotAll = false;
//echo $rsFirstDate->rowCount();
		if ($rsFirstDate->rowCount() < 1){
			$firstDate = $aFundDates[$fund_id]; // Inception Date

			// Assign dates for priceRun query
			$startDate = substr($firstDate, 0, 4)."-".substr($firstDate, 4, 2)."-".substr($firstDate, 6, 2);
			$endDate = substr($_SESSION['max_date'], 0, 4)."-".substr($_SESSION['max_date'], 4, 2)."-".substr($_SESSION['max_date'], 6, 2);

//echo $startDate." - ".$endDate."\n";
			// Set the port number for the API call
			if ($port == $stop_port){
				$port = $start_port;
			}else{
				$port++;
			}
//			// Go get 'em
//			if (!isset($port)){
//				$port = rand(52100, 52499);
//			}
//echo $port;die();
			include("/var/www/html/".$_SESSION['base_url']."web/includes/get-fund-pricing.php");

			// Bail on this fund, we just got it all
			$gotAll = true;
//			continue;

		}else{
			$date = $rsFirstDate->fetch(PDO::FETCH_ASSOC);
			// figure out the date for the first day we have data for, go back 1 day and convert that to the format the query wants
			$lastDate = date('Ymd', strtotime(date('j-n-Y', $date['unix_date']).' -1 day'));
//echo $aFundDates[$fund_id]." - ".$lastDate."\n\n";die();

			// If that first date is after the inception date, then we need to grab everything from inception up to that date
			if ($lastDate >= $aFundDates[$fund_id]){

				// Assign dates for priceRun query
				$startDate = substr($aFundDates[$fund_id], 0, 4)."-".substr($aFundDates[$fund_id], 4, 2)."-".substr($aFundDates[$fund_id], 6, 2);
				$endDate = substr($lastDate, 0, 4)."-".substr($lastDate, 4, 2)."-".substr($lastDate, 6, 2);

				// Set the port number for the API call
				if ($port == $stop_port){
					$port = $start_port;
				}else{
					$port++;
				}
//				// Go get 'em
//			if (!isset($port)){
//				$port = rand(52100, 52499);
//			}
//echo $port;die();
				include("/var/www/html/".$_SESSION['base_url']."web/includes/get-fund-pricing.php");

				// add the fund_id to the missingFirst array
				array_push($aMissingFirst, $fund_id);

			}
		}

		// Now let's find any gaps in the middle (this only looks BETWEEN the first and last dates we have)
		// compare the fund pricing table to itself, looking for gaps greater than a day (86,400 seconds) between records
		if (!$gotAll){
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
				WHERE b > a + 90000
			"; // 90,000 seconds (25 hours) - accommodates the Fall DST change, without which caused every Fall timechange date to be flagged as missing each time.  The following day's timestamp is 1 hour later than the day of the change
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

			// If we find any gaps, fill them
			while ($gaps = $rsGaps->fetch(PDO::FETCH_ASSOC)){
	//print_r($gaps);die();
				// Assign dates for priceRun query
				$startDate = date('Y-m-d', strtotime(date('m/d/Y', $gaps[a]).' +1 day'));
				$endDate = date('Y-m-d', strtotime(date('m/d/Y', $gaps[b]).' -1 day'));
	//echo $startDate." - ".$endDate."\n\n";die();

				// Set the port number for the API call
				if ($port == $stop_port){
					$port = $start_port;
				}else{
					$port++;
				}
//				// Go get 'em
//			if (!isset($port)){
//				$port = rand(52100, 52499);
//			}
//echo $port;die();
				include("/var/www/html/".$_SESSION['base_url']."web/includes/get-fund-pricing.php");

				// add the fund_id to the missingGaps array
				array_push($aMissingGaps, $fund_id);

			}

			// Finally, check for the newest date we have and see if there is a gap from it to maxDate
			$query = "
				SELECT date, unix_date
				FROM ".$_SESSION['fund_pricing_table']."
				WHERE fund_id = :fundID
				ORDER BY unix_date DESC
			";
//				LIMIT 1  <- adding this hoses MySQL for some bizarre reason
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
			$firstDate = date('Ymd', strtotime(date('j-n-Y', $date['unix_date']).' +1 day'));

			// If that last date is before the maxDate, then we need to grab everything from that date to maxDate
			if ($firstDate <= $_SESSION['max_date']){

				// Assign dates for priceRun query
				$startDate = substr($firstDate, 0, 4)."-".substr($firstDate, 4, 2)."-".substr($firstDate, 6, 2);
				$endDate = substr($_SESSION['max_date'], 0, 4)."-".substr($_SESSION['max_date'], 4, 2)."-".substr($_SESSION['max_date'], 6, 2);

				// Set the port number for the API call
				if ($port == $stop_port){
					$port = $start_port;
				}else{
					$port++;
				}

	 			// Go get 'em
	 			include("/var/www/html/".$_SESSION['base_url']."web/includes/get-fund-pricing.php");

	 		}
		}

unset($rsFunds);
unset($rsFirstDate);
unset($rsGaps);
unset($rsLastDate);


		if ($skip_allStats == false){
			// Get aggregate stats for maxDate
			$query = "aggregateStatistics|".$_SESSION['username']."|".$fund_id."|".$fund_symbol."|".$_SESSION['max_date'];
echo $query."\n";

			// Set the port number for the API call
			if ($port == $stop_port){
				$port = $start_port;
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
			if ($port == $stop_port){
				$port = $start_port;
			}else{
				$port++;
			}

			// Include the API call
			include("/var/www/html/".$_SESSION['base_url']."web/includes/data-query-legacy.php");

			// Wait a tick
			sleep($pause);

			// Get fund content details for maxDate
	///////////////// Move this to it's own process once Kim has the maxStockDate query working!!!!!!!!!!
/*
			$query = "positionDetail|".$_SESSION['username']."|".$fund_id."|".$fund_symbol."|".$_SESSION['max_date'];
echo $query."\n";

			// Set the port number for the API call
			if ($port == $stop_port){
				$port = $start_port;
			}else{
				$port++;
			}

			// Include the API call
			include("/var/www/html/".$_SESSION['base_url']."web/includes/data-query-legacy.php");

			// Wait a tick
			sleep($pause);

			// Get the latest basic position stratification stats
			$query = "positionStratification|".$_SESSION['username']."|".$fund_id."|".$fund_symbol."|0";
echo $query."\n";

			// Set the port number for the API call
			if ($port == $stop_port){
				$port = $start_port;
			}else{
				$port++;
			}

			// Include the API call
			include("/var/www/html/".$_SESSION['base_url']."web/includes/data-query-legacy.php");

			// Wait a tick
			sleep($pause);

			// Get the latest style position stratification stats
			$query = "stylePositionStratification|".$_SESSION['username']."|".$fund_id."|".$fund_symbol."|0";;
echo $query."\n";

			// Set the port number for the API call
			if ($port == $stop_port){
				$port = $start_port;
			}else{
				$port++;
			}

			// Include the API call
			include("/var/www/html/".$_SESSION['base_url']."web/includes/data-query-legacy.php");

			// Wait a tick
			sleep($pause);

			// Get the latest sector position stratification stats
			$query = "sectorPositionStratification|".$_SESSION['username']."|".$fund_id."|".$fund_symbol."|0";;
echo $query."\n";

			// Set the port number for the API call
			if ($port == $stop_port){
				$port = $start_port;
			}else{
				$port++;
			}

			// Include the API call
			include("/var/www/html/".$_SESSION['base_url']."web/includes/data-query-legacy.php");

			// Wait a tick
			sleep($pause);
*/
			// Update the stock info for all positions in the fund
			$query = "allPositionInfo|".$_SESSION['username']."|".$fund_id."|".$fund_symbol."|0";;
echo $query."\n";

			// Set the port number for the API call
			if ($port >= $stop_port){
				$port = $start_port;
			}else{
				$port++;
			}

			// Include the API call
			include("/var/www/html/".$_SESSION['base_url']."web/includes/data-query-legacy.php");

			// Wait a tick
			sleep($pause);

			// Update the trade history for all positions in the fund
			$sinceDate = date('Ymd', strtotime("-8 days"));
			$query = "tradesForFund|".$_SESSION['username']."|".$fund_id."|".$fund_symbol."|".$sinceDate;
//			$query = "tradesForFund|".$_SESSION['username']."|".$fund_id."|".$fund_symbol."|".$_SESSION['max_date'];
echo $query."\n";

			// Set the port number for the API call
			if ($port >= $stop_port){
				$port = $start_port;
			}else{
				$port++;
			}

			// Include the API call
			include("/var/www/html/".$_SESSION['base_url']."web/includes/data-query-legacy.php");

			// Wait a tick
			sleep($pause);

		}

		// Finally, get a livePrice record to speed up login time (unless told not to)
		if ($skip_livePrice == false){
			$query = "livePrice|".$_SESSION['username']."|".$fund_id."|".$fund_symbol;
echo $query."\n";

			// Set the port number for the API call
			if ($port == $stop_port){
				$port = $start_port;
			}else{
				$port++;
			}

			// Include the API call
			include("/var/www/html/".$_SESSION['base_url']."web/includes/data-query-legacy.php");

			// Wait a tick
			sleep($pause);

		}
echo "\n";
	}
}
/*
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
*/

// Calculate the run time
$end_time = time();
$diff = ($end_time - $begin_time);
$hours = str_pad(floor($diff / 3600), 2, '0', STR_PAD_LEFT);
$mins  = str_pad(floor(($diff - $hours * 3600) / 60), 2, '0', STR_PAD_LEFT);
$secs = str_pad(($diff - $hours * 3600 - $mins * 60), 2, '0', STR_PAD_LEFT);

// Email the results
$to = 'jeff.saunders@marketocracy.com';
$subject = "Price Funds History Completed";

$message = 'Price Funds History process began at '.date("m/d/y h:i:s a T", $begin_time).' and finished at '.date("m/d/y h:i:s a T", $end_time).' (Run time: '.$hours.':'.$mins.':'.$secs.').<br><br>';
$message .= 'It found '.count($aMissingFirst).' funds missing history from the front and '.count($aMissingGaps).' funds missing history somewhere in the middle.<br><br>';
if (count($aMissingFirst) > 0){
	$message .= 'Fund IDs missing records from the front: ';
	for ($fcnt = 0; $fcnt < count($aMissingFirst); $fcnt++){
		$message .=  $aMissingFirst[$fcnt].', ';
	}
	// Pop the trailing ", " off
	$message = substr($message, 0, -2);

	$message .= '.<br><br>';
}
if (count($aMissingGaps) > 0){
	$message .= 'Fund IDs missing records from the middle (gaps): ';
	for ($fcnt = 0; $fcnt < count($aMissingGaps); $fcnt++){
		$message .=  $aMissingGaps[$fcnt].', ';
	}
	// Pop the trailing ", " off
	$message = substr($message, 0, -2);

	$message .= '.<br><br>';
}

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

//die("Done\n");

?>