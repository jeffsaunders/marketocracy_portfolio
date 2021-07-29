<?php
// This PHP script is run via an AJAX call to validate a entered email address during account creation
// Results of any failed check are returned as HTML to be displayed on the form, after which the script is discontinued - no need to proceed

// Start me up...
session_start();

// Load default functions
require("../../includes/system-debug-functions.php");
require("../../includes/system-functions.php");
//echo $_SERVER['QUERY_STRING'];
//echo "<br>";

$email = urldecode($_REQUEST['email']);
//echo $username;

// Let the jQuery validation handle the format validity - this facility is mainly concerned it's not already used

//Connect to DB
require_once("../../../secure/dbconnect.php");

//Load encryption functions
require_once("../../../secure/crypto.php");

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	echo '<span style="color:#B94A48; font-size:13px; padding:5px 0 0 0;">Please enter a valid email address.</span>';
  	echo '<input type="hidden" id="email_test" name="email_test" value="Please enter a valid email address.">';
  
  die();
}


// Encrypt the passed email address for DB lookup
$encrypted_email = encrypt($email);

//Look up the encrypted email
$query = "
	SELECT *
	FROM ".$_SESSION['auth_table']."
	WHERE email = :encrypted_email
";
try {
	$rsEmail = $mLink->prepare($query);
	$aValues = array(
		':encrypted_email'	=> $encrypted_email
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
	$rsEmail->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo-log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

// Email already taken
if ($rsEmail->rowCount() > 0){
	echo '
		<span style="color:#C09853; font-size:13px; padding:5px 0 0 0;">Email address already registered - Please try another.</span>
		<input type="hidden" id="email_test" name="email_test" value="Email address already registered - Please try another.">
	';
	die();
}



// All's well
//echo '<span style="color:#468847; font-size:13px; padding:5px 0 0 0;">Email available!</span>';
echo '<input type="hidden" id="email_test" name="email_test" value="passed">';
?>
