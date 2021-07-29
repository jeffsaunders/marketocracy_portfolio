<?php
// This PHP script is run conditionally to submit a query to the Legacy Data daemon running on the process server, /var/www/html/daemons/legacyDataDaemon.php
// That daemon pushes the results into various database tables (See it's comments for details)
// It requires that the value of the variable $query be defined before it's included.
// The communications port used should also be defined, but this script will handle a missing $port value.
// $query must contain a validly formatted string as defined in the daemon comments, *including a trailing "\r\n"* (i.e. $query="priceRun|jeffsaunders|1-1|JMF|20140601\r\n")

// Set some ground rules
//ob_implicit_flush();

// Make sure we got something to submit
//if (!isset($query) || $query == "" || (!strpos($query, "\r\n") && !strpos($query, "\n\r"))) die("Bad query string");
if (!isset($query) || $query == "") die("Missing query string");

// Hail the daemon
//$socket = fsockopen($_SESSION['fetch_server'], $_SESSION['query_legacy_socket']) or die("Cannot create a socket at ".$_SESSION['fetch_server'].":".$_SESSION['query_legacy_socket']);

// Take a breather
//usleep(500000); // Half a second

// Post the passed query to the daemon
//fwrite($socket, $query);

// Gather what the daemon says, one character at a time
//$reply = "";
//while (false !== ($char = fgetc($socket))){
//	// Concatonate the characters
//	$reply .= $char;
//	// If we hit a Linefeed (chr(10)) throw it away - we want only the last line
//	if (ord($char) == 10){
//		$reply = "";
//	}
//}

// Tell 'em what the fetcher said
//echo $reply;

// Close hailing frequencies
//fclose($socket);


// Execute an EXPECT shell script that fires off the communication with the Legacy Data daemon.  MUCH faster than the pure PHP method (above).
// By routing the output to /dev/null and issuing the command with a "&", the script runs in the background and releases PHP to continue processing.
// *Note, the trailing "\r\n" in the $query is no longer needed.
if (!isset($port)){
	$port = rand(52000, 52099);
	//$port = rand(52100, 52499);
}
$cmd = '/var/www/html/'.$_SESSION['base_url'].'web/process/scripts/process-legacy-query.sh "'.$port.'" "'.$query.'" > /dev/null &';
//echo $cmd."<br>";
//echo $cmd."\n";
//die();
exec($cmd);

if (isset($requests)){
	$requests++;
}

// This now works both from within a browser and from the command line...

// is this running from a command shell or a browser?
//if (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])){  // Command shell
//	exec('/var/www/html/'.$_SESSION['base_url'].'web/process/scripts/process-legacy-query.sh "'.$port.'" "'.$query.'" > /dev/null &');
//}else{  // Browser
//	exec('/var/www/html/'.$_SESSION['base_url'].'web/process/scripts/process-legacy-query.sh "'.$port.'" "'.$query.'"', $out);
//var_dump($out);
//}
?>
