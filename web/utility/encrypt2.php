<?php
/*
The purpose of this script is to encrypt a passed string using the new Sodium encryption routine in PHP7
*Note - this must be run in a web browser.
*/

// Define some system settings
date_default_timezone_set('America/New_York');

// Tell me when things go sideways
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Load debug functions
require("/var/www/html/portfolio.marketocracy.com/web/includes/system-debug-functions.php");

// Encryption functions
function encrypt2($value, $key='SGFwcHkgQmlydGhkYXkgUmFjaGVsIQ=='){
	if(!$value || !$key){
		return false;
	}
	$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
	$encryptedValue = base64_encode($nonce.sodium_crypto_secretbox($value, $nonce, $key));
	sodium_memzero($value);
	sodium_memzero($key);
	return $encryptedValue;
}

function double_encrypt2($value, $key='fhPXaYlnraw4aN6mOVOfOXPdEtVQGZml'){
	if(!$value || !$key){
		return false;
	}
	$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
	$encryptedValue = base64_encode($nonce.sodium_crypto_secretbox($value, $nonce, $key));
	sodium_memzero($value);
	sodium_memzero($key);
	return $encryptedValue;
}

echo '
<!DOCTYPE HTML>

<html>
<head>
	<title>Encrypter</title>
</head>

<style>
	a:link {color:#000000; text-decoration:none;}
	a:visited {color:#000000; text-decoration:none;}
	a:hover {color:#FF0000; text-decoration:underline;}
	a:active {color:#000000; text-decoration:none;}
</style>

<body>

<form action="" name="encrypt" id="encrypt">
	String to Encrypt:
	<input type="text" name="string">
	<input type="submit">
	<input type="checkbox" name="double">Double Encrypt
</form>
';

// Display results if string is passed
if ($_REQUEST['string']){
	if ($_REQUEST['double'] != true){
		$output = encrypt($_REQUEST['string']);
	}else{
		$output = double_encrypt($_REQUEST['string']);
	}
	echo '
<h3 style="font-weight:normal;">'.($_REQUEST['double'] == true ? 'Double ': '').'Encrypted String for <em>&quot;'.$_REQUEST['string'].'&quot;</em> &ndash;&raquo; <strong>'.$output.'</strong></h3>
	';
}else{
	echo '
<br>
	';
}

echo '
<a href="/utility/decrypt2.php">Switch to Decrypt</a>

</body>
</html>
';

?>