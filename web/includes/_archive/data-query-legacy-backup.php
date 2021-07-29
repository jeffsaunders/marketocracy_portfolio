<?php
// This PHP script is run conditionally to submit a query to the Legacy Data daemon(s) running on the process server, /var/www/html/daemons/legacyDataDaemon.php
// That daemon pushes the results into various database tables (See it's comments for details)
// It requires that the value of the variable $query be defined before it's included.
// The communications port used should also be defined, but this script will handle a missing $port value.
// $query must contain a validly formatted string as defined in the daemon comments (e.g. $query="priceRun|jeffsaunders|1-1|JMF|20140601")

// Make sure we got something to submit
if (!isset($query) || $query == "") die("Missing query string");

// Define a port at random is one is not already defined
if (!isset($port)){
	$port = rand(52000, 52099); // API1
//	$port = rand(52100, 52499); // API2
}

// Execute an EXPECT shell script that fires off the communication with the Legacy Data daemon.
// By routing the output to /dev/null and issuing the command with a "&", the script runs in the background and releases PHP to continue processing.
$cmd = '/var/www/html/'.$_SESSION['base_url'].'web/process/scripts/process-legacy-query.sh "'.$port.'" "'.$query.'" > /dev/null &';
//echo $cmd."<br>";//die();
exec($cmd);

// If the calling routine is counting requests, increment
if (isset($requests)){
	$requests++;
}

?>
