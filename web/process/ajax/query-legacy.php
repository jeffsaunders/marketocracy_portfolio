<?php
// This PHP script is run via an AJAX call to submit a query to the Legacy Data daemon running on the fetch server
// That daemon pushes the results into various database tables (See it's comments for details)

// Set some ground rules
error_reporting(E_ALL);
ini_set('display_errors', '1');
ob_implicit_flush();

// Start me up...
session_start();
$_SESSION['base_url'] = "portfolio.marketocracy.com/";

// Make sure we got something to submit
if (!isset($_REQUEST['query']) || $_REQUEST['query'] == "")	die("No query passed");

// Assign the passed query
//$query = $_REQUEST['query']."\r\n";
$query = $_REQUEST['query'];

// Assign a port
if (!isset($port)){
	$port = rand(52000, 52249);
}
//$cmd = '/var/www/html/'.$_SESSION['base_url'].'web/process/scripts/process-legacy-query.sh "'.$port.'" "'.$query.'" > /dev/null &';
//echo $cmd."<br>";
//die();
//exec($cmd);

// Call the EXPECT script to send the query to the daemon
exec('/var/www/html/'.$_SESSION['base_url'].'web/process/scripts/process-legacy-query.sh "'.$port.'" "'.$query.'" > /dev/null &');



/*

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
echo $reply;

// Close hailing frequencies
fclose($socket);
*/
?>
