<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<title>Decrypt</title>
</head>

<body>

<?php
if ($_REQUEST['string']){
	// Create decrypt function for password comparison
	function decrypt($value, $key='SGFwcHkgQmlydGhkYXkgUmFjaGVsIQ=='){
		if(!$value || !$key){
			return false;
		}
		$td = mcrypt_module_open('rijndael-256', '', 'ecb', '');
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size( $td ), MCRYPT_RAND);
		mcrypt_generic_init($td, $key, $iv);
		$decryptedValue = mdecrypt_generic($td, base64_decode($value));
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return $decryptedValue;
	}
	$output = decrypt($_REQUEST['string']);
	echo "Decrypted String for <em>&quot;" . urldecode($_REQUEST['string']) . "&quot;</em> -> <strong>" . $output ."</strong><br><br>";
}
?>
<form action="" name="decrypt" id="decrypt">
	String to Decrypt:
	<input type="text" name="string">
	<input type="submit" name="Decrypt">
</form>

</body>
</html>
