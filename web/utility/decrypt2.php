<?php
/*
The purpose of this script is to encrypt a passed string using the same encryption routine we use for passwords, etc.
*Note - this must be run in a web browser.
*/

// Define some system settings
date_default_timezone_set('America/New_York');

// Tell me when things go sideways
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Load debug functions
require("/var/www/html/portfolio.marketocracy.com/web/includes/system-debug-functions.php");

// Decrypt functions
function decrypt2($value, $key='SGFwcHkgQmlydGhkYXkgUmFjaGVsIQ=='){
	if(!$value || !$key){
		return false;
	}
    $decodedValue = base64_decode($value);
    if ($decodedValue === false) {
        throw new Exception('Scream bloody murder, the encoding failed');
    }
    if (mb_strlen($decodedValue, '8bit') < (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES)) {
        throw new Exception('Scream bloody murder, the message was truncated');
    }
    $nonce = mb_substr($decodedValue, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
    $cipherText = mb_substr($decodedValue, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
    $decryptedValue = sodium_crypto_secretbox_open($cipherText, $nonce, $key);
    if ($plain === false) {
         throw new Exception('The message was tampered with in transit');
    }
    sodium_memzero($cipherText);
    sodium_memzero($key);
    return $decryptedValue;
}

function double_decrypt2($value, $key='fhPXaYlnraw4aN6mOVOfOXPdEtVQGZml'){
	if(!$value || !$key){
		return false;
	}
    $decodedValue = base64_decode($value);
    if ($decodedValue === false) {
        throw new Exception('Scream bloody murder, the encoding failed');
    }
    if (mb_strlen($decodedValue, '8bit') < (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES)) {
        throw new Exception('Scream bloody murder, the message was truncated');
    }
    $nonce = mb_substr($decodedValue, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
    $cipherText = mb_substr($decodedValue, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
    $decryptedValue = sodium_crypto_secretbox_open($cipherText, $nonce, $key);
    if ($plain === false) {
         throw new Exception('The message was tampered with in transit');
    }
    sodium_memzero($cipherText);
    sodium_memzero($key);
    return $decryptedValue;
}

echo '
<!DOCTYPE HTML>

<html>
<head>
	<title>Decrypter</title>
</head>

<style>
	a:link {color:#000000; text-decoration:none;}
	a:visited {color:#000000; text-decoration:none;}
	a:hover {color:#FF0000; text-decoration:underline;}
	a:active {color:#000000; text-decoration:none;}
</style>

<body>

<form action="" name="decrypt" id="decrypt">
	String to Decrypt:
	<input type="text" name="string">
	<input type="submit">
	<input type="checkbox" name="double">Double Decrypt
</form>
';

// Display results if string is passed
if ($_REQUEST['string']){
	if ($_REQUEST['double'] != true){
		$output = decrypt($_REQUEST['string']);
	}else{
		$output = double_decrypt($_REQUEST['string']);
	}
	echo '
<h3 style="font-weight:normal;">'.($_REQUEST['double'] == true ? 'Double ': '').'Decrypted String for <em>&quot;'.$_REQUEST['string'].'&quot;</em> &ndash;&raquo; <strong>'.$output.'</strong></h3>
	';
}else{
	echo '
<br>
	';
}

echo '
<a href="/utility/encrypt2.php">Switch to Encrypt</a>

</body>
</html>
';

?>
