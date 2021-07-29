<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<title>Encrypt</title>
</head>

<body>

<?php
// Encrypt functions
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

function double_encrypt($value, $key='fhPXaYlnraw4aN6mOVOfOXPdEtVQGZml'){
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

if ($_REQUEST['string']){
	if ($_REQUEST['double'] != true){
		$output = encrypt($_REQUEST['string']);
	}else{
		$output = double_encrypt($_REQUEST['string']);
	}
	echo "Encrypted String for <em>&quot;" . $_REQUEST['string'] . "&quot;</em> -> <strong>" . $output ."</strong><br><br>";
}

?>
<form action="" name="encrypt" id="encrypt">
	String to Encrypt:
	<input type="text" name="string">
	<input type="submit" name="Encrypt">
	<input type="checkbox" name="double">Double?
</form>

<a href="/utility/decrypt.php">Switch to Decrypt</a>

</body>
</html>