<?php
ini_set('memory_limit', '1024M');
/*
+----------------------------------------------------------------------------------------+
|						STRATIFICATION CALCULATION ROUTINE
+----------------------------------------------------------------------------------------+
*/
// Parse passed arguments string to $_REQUEST array (i.e. "first=1&second=2&third=3" -> $_REQUEST['first'] = 1, etc.)
//parse_str($argv[1], $_REQUEST);

//get dependencies
require('/var/www/html/portfolio.marketocracy.com/secure/dbconnect.php');
require('/var/www/html/portfolio.marketocracy.com/web/includes/system-functions.php');
require('/var/www/html/portfolio.marketocracy.com/web/includes/system-debug-functions.php');


echo 'start\n';

$now		= time();
$yesterday	= strtotime('-2 day', $now);

echo 'now:'.date('Y-m-d h:i:s',$now).'\r\n';
echo 'yesterdate:'.date('Y-m-d h:i:s', $yesterday).'\r\n';

//tables
$caHistoryTable	= 'log_ca_affected_funds';

$time	= mktime(0,0,0,date('m',$yesterday),date('d', $yesterday),date('Y',$yesterday));

echo 'yesterdate:'.date('Y-m-d h:i:s', $time).'\r\n';

$query = "
	SELECT *
	FROM ".$caHistoryTable."
	WHERE timestamp>:time AND strat_run IS NULL
";
try{
	$rsGet = $mLink->prepare($query);
	$aValues = array(':time'=>$time);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsGet->execute($aValues);
}
catch(PDOException $error){
	// Log any error
}

echo $preparedQuery.'\r\n';
while($log = $rsGet->fetch(PDO::FETCH_ASSOC)){
	
	$aFundIDs 	= explode('|',$log['fund_ids']);
	$uid		= $log['uid'];
	
	echo '<----------------'.$uid.'------------------>'.'\r\n';
	
	foreach($aFundIDs as $key=>$fundID){
		exec('/usr/bin/php /var/www/html/portfolio.marketocracy.com/scripts/strat-build.php "fundID='.$fundID.'" > /dev/null &');
		
		echo $fundID.'\n';
			
		usleep(50000);
	}
	
	$query = "
		UPDATE ".$caHistoryTable."
		SET strat_run=UNIX_TIMESTAMP()
		WHERE uid=:uid
	";
	try{
		$rsUpdateHistory = $mLink->prepare($query);
		$aValues = array(':uid'=>$uid);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsUpdateHistory->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
}	
?>