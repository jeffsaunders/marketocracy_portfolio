<?php

//Start User Session
session_start();

//echo 'start';

//Connect to DB
require_once("../../secure/dbconnect.php");

// Load any global functions
require_once("../includes/system-functions.php");

// Get global settings and functions
require_once("../includes/system-global.php");

// Load default functions
//require("../system-debug-functions.php");
//require_once("../system-functions.php");
//Load encryption functions
require_once("../../secure/crypto.php");
// get passed value
$id = $_REQUEST["cargo"];

//echo $id;
// Now put back any stripped out plus signs (RFC 2396)
$id = str_replace(" ", "+", $id);


// ...and convert from URL safe format
$id = json_decode($id);



// Decrypt passed string (No need to do this as $id is needed encrypted)
//$decryptedID = decrypt($id);

// Assign cleaned-up encrypted email address, for your code reading pleasure.
$encrypted_email = $id;



// Look up encrypted email address
$query = "
	SELECT *
	FROM ".$_SESSION['auth_table']."
	WHERE email = :encrypted_email
	ORDER BY timestamp DESC
	LIMIT 1
";
try {
	$rsAuth = $mLink->prepare($query);
	$aValues = array(
		':encrypted_email'	=> $encrypted_email
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
	$rsAuth->execute($aValues);
}
catch(PDOException $error){
	// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}



// Otherwise grab the matching record...
$auth = $rsAuth->fetch(PDO::FETCH_ASSOC);

#No record found
if($auth['email'] == ''){
	header('Location: /');	
}

#make sure they haven't already validated
if ($auth['email_validated_timestamp'] > 0){
	header('Location: /email-verified/'.rawurlencode(trim(decrypt($auth['email']))).'');
}

#Flag it as verified
$query = "
	UPDATE ".$_SESSION['auth_table']."
	SET email_validated_timestamp = UNIX_TIMESTAMP()
	WHERE uid = :uid
";
try {
	$rsUpdate = $mLink->prepare($query);
	$aValues = array(
		':uid'	=> $auth['uid']
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
	$rsUpdate->execute($aValues);
}
catch(PDOException $error){
	// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}


#set default flags
$query = "
	UPDATE ".$_SESSION['members_flags_table']."
	SET member = 1
	WHERE member_id = :member_id
";
try {
	$rsUpdate = $mLink->prepare($query);
	$aValues = array(
		':member_id'	=> $auth['member_id']
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
	$rsUpdate->execute($aValues);
}
catch(PDOException $error){
	// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

#update subscription start date
$query = "
	UPDATE members_subscriptions
	SET start_timestamp=UNIX_TIMESTAMP()
	WHERE member_id = :member_id
";
try {
	$rsUpdate = $mLink->prepare($query);
	$aValues = array(
		':member_id'	=> $auth['member_id']
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
	$rsUpdate->execute($aValues);
}
catch(PDOException $error){
	// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

// Create the new account on the old system
//$query = "newManager|".trim(decrypt($auth['username']))."|".trim(decrypt($auth['email']))."|".$auth['member_id']."\r\n";
//die($query);
//include('../includes/data-query-legacy.php');

//echo 'hello | Flag Update Successful<br />';

/*$aMethodVars[] = array(
	'method'	=> 'newManager', #method name
	'source'    => 'User Sign Up | system-email-validate.php', #File and Code location of this instance
	'api'       => '1', #api switch, 1 = api1, 2 = api2
	'username'  => trim(decrypt($auth['username'])), #username of member
	'email'     => trim(decrypt($auth['email'])), #email of member
	'member_id' => $auth['member_id'] #member id member
);
$mlaResults = legacy_api($mLink, $aMethodVars, true, 'results');*/

//echo '<pre>';
//echo 'hello | User signup successful';
//print_r($mlaResults);
//echo '</pre>';

//echo 'hello | Create member successful<br />';

// Write their default dashboard settings
/*$query = "
	INSERT INTO ".$_SESSION['members_settings_table']." (
		member_id,
		dash_col1,
		dash_col2,
		dash_4col1,
		dash_4col2,
		timestamp
	) VALUES (
		:member_id,
		:dash_col1,
		:dash_col2,
		:dash_4col1,
		:dash_4col2,
		UNIX_TIMESTAMP()
	)
";
try {
	$rsInsert = $mLink->prepare($query);
	$aValues = array(
		':member_id'	=> $auth['member_id'],
		':dash_col1'	=> "notifications~0~0~0",
		':dash_col2'    => "tickers~0~0~0",
		':dash_4col1'	=> "notifications~0~0~0",
		':dash_4col2'   => "tickers~0~0~0"
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
	$rsInsert->execute($aValues);
}
catch(PDOException $error){
	// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		echo $error;
}*/



// Send them to the login page
header('Location: /email-verified/'.rawurlencode(trim(decrypt($auth['email']))).'');

?>
