<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");
require_once("../../../secure/crypto.php");

require_once("../../includes/system-functions.php");

$process = $_REQUEST['process'];

set_time_limit(3600);
/*$encrypted_username = encrypt(strtolower($_REQUEST['username']));
		$encrypted_password = encrypt($_REQUEST['password']);
		$encrypted_email = encrypt($_REQUEST['email']);*/


$query = "TRUNCATE TABLE `system_authentication_temp`";
try{
	$rsEmpty = $mLink->prepare($query);
	$aValues = array();
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsEmpty->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}		

echo $error;
		
$query = "
	SELECT * FROM system_authentication GROUP BY member_id
";
try{
	$rsAuth = $mLink->prepare($query);
	$aValues = array();
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsAuth->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

echo $error;
while($auth = $rsAuth->fetch(PDO::FETCH_ASSOC)){

	$query = "
		INSERT INTO system_authentication_temp (
			member_id,
			timestamp,
			username,
			email,
			email_validated_timestamp,
			imported
		)VALUES(
			:member_id,
			:timestamp,
			:username,
			:email,
			:email_validated_timestamp,
			:imported
		)
	";
	try{
		$rsInsertAuth = $mLink->prepare($query);
		$aValues = array(
			':member_id'					=> $auth['member_id'],
			':timestamp'					=> date('Y/m/d h:i a', $auth['timestamp']),
			':username'						=> decrypt($auth['username']),
			':email'						=> decrypt($auth['email']),
			':email_validated_timestamp'	=> date('Y/m/d h:i a', $auth['email_validated_timestamp']),
			':imported'						=> $auth['member_id']
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsInsertAuth->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	echo $error;
	
}

?>