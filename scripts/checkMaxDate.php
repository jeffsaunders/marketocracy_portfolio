<?php
// This commandline batch script checks for the value of "maxdate" to be greater than the value of "process_date" in the members_fund_maxdate table.
// If it is, then it kicks off the nightly "price-funds" process.
// By running it as a CRON you can set the checking and updating interval
// Example:
//  # Run every 30 minutes between 1:00am and 12:00pm (noon), Tuesday (2) - Saturday (6)
//	*/30 1-12 * * 2-6 /usr/bin/php /var/www/html/protfolio.marketocracy.com/scripts/checkMaxDate.php > /dev/null 2>&1
// OR
//  # Run every 15 minutes, on the 5, 20, 35, and 50, 24/7
//  5,20,35,5 * * * * /usr/bin/php /var/www/html/portfolio.marketocracy.com/scripts/checkMaxDate.php > /dev/null 2>&1
// *Note - this will not run within a web browser.

// Define some global system settings
date_default_timezone_set('America/New_York');

// Tell me when things go sideways
error_reporting(E_ALL);
ini_set('display_errors', '1');

// See if we are in the DB backup period
$wait = false;
$now = time();
$backup_start = strtotime('today 4:00 AM');
$backup_end = strtotime('today 6:00 AM');

if ($now >= $backup_start && $now <= $backup_end){
	$wait = true;
}

if (!$wait){

	// Make sure the price-funds isn't already running
	exec("ps -ef | grep p[r]ice-funds", $output);
	if (empty($output)) {  // NOT already running

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

		// Wait a minute to let the getMaxDate process finish (it also runs every half hour and it might not have finished before we get here)
		// getmaxDate pulls the maxDate value from the old system via the API and is subject to potential delays/lag
	//	sleep(60);  // Run with a 15 minute offset now so no delay needed.

		// Get the maxDate and the date of the last processing
		$query = "
			SELECT maxdate, process_date
			FROM ".$_SESSION['fund_maxdate_table']."
		";
		$rsMaxDate = $mLink->prepare($query);
		$rsMaxDate->execute();
		$maxDate = $rsMaxDate->fetch(PDO::FETCH_ASSOC);

		// If the maxDate has changed, execute the price-funds script, otherwise die (and wait 15 minutes and check again via cron)
		if ($maxDate['maxdate'] > $maxDate['process_date']){
			$cmd = "/usr/bin/php /var/www/html/portfolio.marketocracy.com/scripts/price-funds.php > /dev/null 2>&1";
			exec($cmd);
		}

	}

}
// Die and try again later

?>
