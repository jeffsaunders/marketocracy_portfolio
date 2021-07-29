<?php
// This PHP script is run via an AJAX call to validate a desired username during account creation
// Results of any failed check are returned as HTML to be displayed on the form, after which the script is discontinued - no need to proceed

// Start me up...
session_start();

// Load default functions
require("../../includes/system-debug-functions.php");
require("../../includes/system-functions.php");
//echo $_SERVER['QUERY_STRING'];
//echo "<br>";

$username = urldecode($_REQUEST['username']);
//echo $username;

// Make sure the username isn't blank
if ($username == ""){
	echo '
		<span style="color:#b94a48; font-size:13px; padding:5px 0 0 0;">Username cannot be blank.</span>
		<input type="hidden" id="username_test" name="username_test" value="">
	';
	die();
}

// Make sure it doesn't contain any spaces
if (stripos($username, " ") !== false){
	echo '
		<span style="color:#b94a48; font-size:13px; padding:5px 0 0 0;">Username cannot contain spaces - Please try another.</span>
		<input type="hidden" id="username_test" name="username_test" value="">
	';
	die();
}

// Make sure it doesn't contain fewer than the minimum defined number of characters
if (strlen($username) < $_SESSION['username_minimum_length']){
	echo '
		<span style="color:#b94a48; font-size:13px; padding:5px 0 0 0;">Username must be at least '.$_SESSION['username_minimum_length'].' characters - Please try another.</span>
		<input type="hidden" id="username_test" name="username_test" value="">
	';
	die();
}

// Make sure it contains only letters and numbers (no punctuation, etc.)
if (!ctype_alnum($username)){
	echo '
		<span style="color:#b94a48; font-size:13px; padding:5px 0 0 0;">Username must contain only letters and numbers - Please try another.</span>
		<input type="hidden" id="username_test" name="username_test" value="">
	';
	die();
}

/////// Add any other format restrictions here.....

//Connect to DB
require_once("../../../secure/dbconnect.php");

//Load encryption functions
require_once("../../../secure/crypto.php");

// Encrypt the passed username for DB lookup
$encrypted_username = encrypt($username);

//Look up the encrypted username
$query = "
	SELECT *
	FROM ".$_SESSION['auth_table']."
	WHERE username = '$encrypted_username'
";
try {
	$rsUsername = $mLink->prepare($query);
	$aValues = array(
		':encrypted_username'	=> $encrypted_username
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
	$rsUsername->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

// Username already taken
if ($rsUsername->rowCount() > 0){
	echo '
		<span style="color:#b94a48; font-size:13px; padding:5px 0 0 0;">Username unavailable - Please try another.</span>
		<input type="hidden" id="username_test" name="username_test" value="">
	';
	die();
}

$query = "
	SELECT *
	FROM ".$_SESSION['mtr_subdomain_table']."
	WHERE subdomain=:username
";
try {
	$rsUsername2 = $mLink->prepare($query);
	$aValues = array(
		':username'	=> $username
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
	$rsUsername2->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

// Username already taken
if ($rsUsername2->rowCount() > 0){
	echo '
		<span style="color:#b94a48; font-size:13px; padding:5px 0 0 0;">Username unavailable - Please try another.</span>
		<input type="hidden" id="username_test" name="username_test" value="">
	';
	die();
}



if ($_SESSION['username_obscenity_checking'] == "on"){
	// Get list of restricted phrases (obscenities)
	// *** Expand language support later ***
	$query = "
		SELECT *
		FROM ".$_SESSION['obscenities_table']."
		WHERE language = :language
	";
	//echo $query;
	try {
		$rsObscenities = $mLink->prepare($query);
		$aValues = array(
			':language'	=> "en"
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
		$rsObscenities->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
 		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	while ($obscenity = $rsObscenities->fetch(PDO::FETCH_ASSOC)){
		$sBad = $obscenity['obscenity'];
		if (stripos($username, $sBad) !== false){
			echo '
				<span style="color:#b94a48; font-size:13px; padding:5px 0 0 0;">Username contains an obscene phrase - Please try another.</span>
				<input type="hidden" id="username_test" name="username_test" value="">
			';
			die();
		}
		if (stripos($username, str_replace(' ', '', $sBad)) !== false){
			echo '
				<span style="color:#b94a48; font-size:13px; padding:5px 0 0 0;">Username contains an obscene phrase - Please try another.</span>
				<input type="hidden" id="username_test" name="username_test" value="">
			';
			die();
		}
	}
}

// All's well
echo '
	<span style="color:#468847; font-size:13px; padding:5px 0 0 0;">Username available!</span>
	<input type="hidden" id="username_test" name="username_test" value="passed">
';
?>
