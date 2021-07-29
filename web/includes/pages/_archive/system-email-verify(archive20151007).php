<?php

//Start User Session
session_start();

//Connect to DB
require_once("../../../secure/dbconnect.php");

//Load encryption functions
require_once("../../../secure/crypto.php");

// Load default functions
//require("../system-debug-functions.php");
//require("../system-functions.php");

// get passed value
$id = $_REQUEST["cargo"];

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

// Authentication failed - no match for email address
if ($rsAuth->rowCount() < 1){
	header('Location: /');
}

// Otherwise grab the matching record...
$auth = $rsAuth->fetch(PDO::FETCH_ASSOC);

// ...make sure they haven't already validated...
if ($auth['email_validated_timestamp'] > 0){
	header('Location: /');
}

// ...and flag it as verified, then...
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

// ...set the default flags
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

// Create the new account on the old system
$query = "newManager|".trim(decrypt($auth['username']))."|".trim(decrypt($auth['email']))."|".$auth['member_id']."\r\n";
//die($query);

ob_implicit_flush();

// Hail the daemon on the fetch server
$socket = fsockopen($_SESSION['fetch_server'], $_SESSION['query_legacy_socket']) or die("Cannot create a socket at ".$_SESSION['fetch_server'].":".$_SESSION['query_legacy_socket']);

// Take a breather
usleep(500000); // Half a second

// Post the passed query to the daemon
fwrite($socket, $query);

// Gather what the daemon says, one character at a time (effectively, wait until we get the end of the reply)
$reply = "";
while (false !== ($char = fgetc($socket))){
	// Concatonate the characters
	$reply .= $char;
// If we hit a Linefeed (chr(10)) throw it away - we want only the last line
	if (ord($char) == 10){
		$reply = "";
	}
}

//echo dump_array(get_defined_vars());die();

// Close hailing frequencies
//fclose($socket); // Let it close automatically when the script ends

// Write their default dashboard settings
$query = "
	INSERT INTO ".$_SESSION['members_settings_table']." (
		member_id,
		dash_col1,
		dash_col2,
		timestamp
	) VALUES (
		:member_id,
		:dash_col1,
		:dash_col2,
		UNIX_TIMESTAMP()
	)
";
try {
	$rsInsert = $mLink->prepare($query);
	$aValues = array(
		':member_id'	=> $auth['member_id'],
		':dash_col1'	=> "notifications~0~0~0|calendar~0~0~0",
		':dash_col2'	=> "tasks~0~0~0|chat~0~0~0"
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
	$rsInsert->execute($aValues);
}
catch(PDOException $error){
	// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

// Finally, log the event
$query = "
	INSERT INTO ".$_SESSION['eventslog_table']." (
		member_id,
		timestamp,
		event,
		detail
	) VALUES (
		:member_id,
		UNIX_TIMESTAMP(),
		:event,
		:detail
	)
";
try {
	$rsInsert = $mLink->prepare($query);
	$aValues = array(
		':member_id'	=> $auth['member_id'],
		':event'		=> "Verify Email",
		':detail'		=> ""
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
	$rsInsert->execute($aValues);
}
catch(PDOException $error){
	// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

// Send them to the login page
header('Location: /login');

?>
