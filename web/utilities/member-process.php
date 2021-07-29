<?php

session_start();

// Load debug & error logging functions
require_once("../includes/system-debug-functions.php");

//Connect to DB
require_once("../../secure/dbconnect.php");

require_once("../includes/system-functions.php");

$memberID 	= $_REQUEST['member_id'];
$email		= $_REQUEST['email'];
$password	= $_REQUEST['password'];
$username	= $_REQUEST['username'];

// Create encrypt function for password comparison
function encrypt($value, $key='SGFwcHkgQmlydGhkYXkgUmFjaGVsIQ=='){
	if(!$value || !$key){
		return false;
	}
	$td = mcrypt_module_open('rijndael-256', '', 'ecb', '');
	$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size( $td ), MCRYPT_RAND);
	mcrypt_generic_init($td, $key, $iv);
	$encryptedValue = mcrypt_generic($td, $value);
	mcrypt_generic_deinit($td);
	mcrypt_module_close($td);
	return base64_encode( $encryptedValue );
}
$encEmail = encrypt($email);
$encPW = encrypt($password);
$encUsername = encrypt($username);

//echo "Encrypted String for <em>&quot;" . $_REQUEST['string'] . "&quot;</em> -> <strong>" . $output ."</strong><br><br>";

//UPdate members table
$query = "
	UPDATE members
	SET last_login=UNIX_TIMESTAMP(), email=:email, username=:username
	WHERE member_id=:member_id
";

try{
	$rsAddTopic = $mLink->prepare($query);
	$aValues = array(
		':member_id' 	=> $memberID,
		':email'		=> $email,
		':username'		=> $username
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsAddTopic->execute($aValues);
}

catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

//Update members_flags table
$query = "
	UPDATE members_flags
	SET free='1', member='1'
	WHERE member_id=:member_id
";

try{
	$rsAddTopic = $mLink->prepare($query);
	$aValues = array(
		':member_id' 	=> $memberID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsAddTopic->execute($aValues);
}

catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}



//Create members_profile Record
$query = "
	INSERT INTO members_profile (
		member_id,
		version,
		timestamp
	) VALUE (
		:member_id,
		'1',
		UNIX_TIMESTAMP()
	)
";

try{
	$rsAddRecord = $mLink->prepare($query);
	$aValues = array(
		':member_id' 	=> $memberID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsAddRecord->execute($aValues);
}

catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

//Update members_flags table
$query = "
	UPDATE members_settings
	SET dash_col1='notifications~0~0~0', dash_col2='', dash_4col1='notifications~0~0~0', timestamp=UNIX_TIMESTAMP()
	WHERE member_id=:member_id
";

try{
	$rsAddTopic = $mLink->prepare($query);
	$aValues = array(
		':member_id' 	=> $memberID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsAddTopic->execute($aValues);
}

catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

//Update members_flags table
$query = "
	UPDATE system_authentication
	SET username=:username, password=:password, email=:email, email_validated_timestamp=UNIX_TIMESTAMP()
	WHERE member_id=:member_id
";

try{
	$rsAddTopic = $mLink->prepare($query);
	$aValues = array(
		':member_id' 	=> $memberID,
		':username'		=> $encUsername,
		':password'		=> $encPW,
		':email'		=> $encEmail
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsAddTopic->execute($aValues);
}

catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}



header("Location: move-member.php?member=".$memberID."");

?>