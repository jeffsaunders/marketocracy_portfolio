<?php
// This PHP script is run conditionally to submit a query to the Legacy Data daemon running on the fetch server, /var/www/html/daemons/legacyDataDaemon.php
// That daemon pushes the results into various database tables (See it's comments for details)
// It requires that the value of the variable $query be defined before it's included
// $query must contain a validly formatted string as defined in the daemon comments, *including a trailing "\r\n"* (i.e. $query="priceRun|jeffsaunders|1-1|JMF|20140601\r\n")

// Set some ground rules
ob_implicit_flush();

// Make sure we got something to submit
//if (!isset($query) || $query == "" || (!strpos($query, "\r\n") && !strpos($query, "\n\r"))) die("Bad query string");

// Hail the daemon
$socket = fsockopen($_SESSION['fetch_server'], $_SESSION['query_legacy_socket']) or die("Cannot create a socket at ".$_SESSION['fetch_server'].":".$_SESSION['query_legacy_socket']);

// Take a breather
usleep(500000); // Half a second

// Post the passed query to the daemon
fwrite($socket, $query);

// Gather what the daemon says, one character at a time
$reply = "";
while (false !== ($char = fgetc($socket))){
	// Concatonate the characters
	$reply .= $char;
	// If we hit a Linefeed (chr(10)) throw it away - we want only the last line
	if (ord($char) == 10){
		$reply = "";
	}
}

// Tell 'em what the fetcher said
//echo $reply;

// Close hailing frequencies
//fclose($socket);

?>
