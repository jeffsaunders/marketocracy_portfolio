<?php
// This PHP script is run conditionally to submit a query to the ECN Trade daemon running on the Process server, /var/www/html/daemons/ecnTradeDaemon.php
// That daemon pushes the results into various database tables (See its comments for details)
// It requires that the value of the variable $query be defined before it's included
// The communications port used should also be defined, but this script will handle a missing $port value.
// $query must contain a validly formatted string as defined in the daemon comments (i.e. $query="create||jeffsaunders|JMF|1-1|sell|Day|AAPL|50")

// Execute an EXPECT shell script that fires off the communication with the daemon.
// By routing the output to /dev/null and issuing the command with a "&", the script runs in the background and releases PHP to continue processing.
if (!isset($port)){
	$port = rand(53001, 53019);
}

// For some reason 53000 stopped working.  This is a little workaround... JS
if ($port == 53000){
        $port = rand(53001, 53019);
}

exec('/var/www/html/'.$_SESSION['base_url'].'web/process/scripts/process-ecn-query.sh "'.$port.'" "'.$query.'" > /dev/null &');



#log query
$dbQuery = "
	INSERT INTO log_ecn_api (
		query,
		location,
		username,
		fund_id,
		fund_symbol,
		action,
		type,
		symbol,
		shares,
		limit_price,
		reason,
		description,
		timestamp
	)VALUES(
		:query,
		:stack,
		:username,
		:fund_id,
		:fund_symbol,
		:action,
		:type,
		:symbol,
		:shares,
		:limit_price,
		:reason,
		:description,
		UNIX_TIMESTAMP()
	)
";

try{
	$rsInsert = $mLink->prepare($dbQuery);
	$aValues = array(
		':query' 		=> $query,
		':stack'		=> $location,
		':username'		=> $username,
		':fund_id'		=> $fundID,
		':fund_symbol'	=> $fundSymbol,
		':action'		=> $tradeType,
		':type'			=> $orderType,
		':symbol'		=> $symbol,
		':shares'		=> $shares,
		':limit_price'	=> $limitPrice,
		':reason'		=> $reason,
		':description'	=> $desc
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsInsert->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

// Not sure what this is for, added by Brandon??
$identify = 'data-query-ecn';
?>
