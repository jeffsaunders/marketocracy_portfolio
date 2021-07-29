<?php
//Set Database Variables
$dbHost = "192.168.111.211";
$dbName = "portfolio";
$dbUser = "marketocracy";
$dbPass = "KfabyZcbE3";

//Connect to portfolio DB / MySQL with PDO_MYSQL
try{
	$mLink = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
	$mLink->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}
catch(PDOException $error){
	// Log any error to /var/log/httpd/beta-pdo_log
	file_put_contents("/var/log/httpd/beta-pdo_log", "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	die('We are currently experiencing technical difficulties. Please check back later. <br /><br /><br />'.$error->getMessage());
}

$dbName = "feed2";

//Connect to feed_data DB / MySQL with PDO_MYSQL
try{
        $fLink = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
        $fLink->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}
catch(PDOException $error){
        // Log any error to /var/log/httpd/beta-pdo_log
        file_put_contents("/var/log/httpd/beta-pdo_log", "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
        die($error->getMessage());
}


//Connect to MDS DB
$dbName = 'sites_mds';

try{
	$mdsLink = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
	$mdsLink->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}
catch(PDOException $error){
	// Log any error to /var/log/httpd/beta-pdo_log
	file_put_contents("/var/log/httpd/beta-pdo_log", "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	die($error->getMessage());
}


//Connect to myTrackRecord DB
$dbName = 'my_track_record';

try{
	$mtrLink = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
	$mtrLink->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}
catch(PDOException $error){
	// Log any error to /var/log/httpd/beta-pdo_log
	file_put_contents("/var/log/httpd/beta-pdo_log", "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	die($error->getMessage());
}

//Connect to myTrackRecord DB
$dbName = 'sites_minc';

try{
	$mincLink = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
	$mincLink->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}
catch(PDOException $error){
	// Log any error to /var/log/httpd/beta-pdo_log
	file_put_contents("/var/log/httpd/beta-pdo_log", "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	die($error->getMessage());
}

?>
