<?php
/*
The purpose of this script is to relay member's login credentials to Moodle, log them in seamlessly, and send them to their Moodle dashboard
*/

// Tell me when things go sideways
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

//Start session
session_start();

// Pre-set this session variable for the following lines (catch-22 as session vars are loaded below here)
$_SESSION['base_url'] = "portfolio.marketocracy.com/";

// Load debug functions
require("/var/www/html/".$_SESSION['base_url']."web/includes/system-debug-functions.php");

// Load encryption functions
require("/var/www/html/".$_SESSION['base_url']."secure/crypto.php");

// Connect to database
require("/var/www/html/".$_SESSION['base_url']."scripts/dbConnect.php");

//Get site functions
require("/var/www/html/".$_SESSION['base_url']."scripts/system-functions.php");

//Get global settings
require("/var/www/html/".$_SESSION['base_url']."web/includes/system-global.php");


// Assign member's username
$username = $_SESSION['username'];

// Get member's password
$query = "
	SELECT password
	FROM system_authentication
	WHERE member_id = ".$_SESSION['member_id']."
	ORDER BY timestamp DESC
    LIMIT 1
";
try{
	$rsPassword = $mLink->prepare($query);
	$rsPassword->execute();
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

// Decrypt their password
$pass = $rsPassword->fetch(PDO::FETCH_ASSOC);
$encryptedPassword = $pass['password'];
$password = trim(decrypt($encryptedPassword));

//echo $username."/".$password;die();

// Stuff values into a hidden form and submit it...then we're done here
?>

<form action="https://roundtable.marketocracy.com/login/index.php" method="post" name="moodle_login" id="form">
	<input type="hidden" name="username" value="<?php echo $username ?>">
	<input type="hidden" name="password" value="<?php echo $password ?>">
</form>

<script language="JavaScript">
function logIn(){
	document.moodle_login.submit();
}

logIn();
</script>

