<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<title>Untitled</title>
</head>

<body>

<?php
if ($_REQUEST['string']){
	// Create encrypt function for password comparison
	function encrypt($value, $key='<key>'){
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
	$output = encrypt($_REQUEST['string']);
	echo "Encrypted String for <em>&quot;" . $_REQUEST['string'] . "&quot;</em> -> <strong>" . $output ."</strong><br><br>";
}
?>
<form action="" name="encrypt" id="encrypt">
	String to Encrypt:
	<input type="text" name="string">
	<input type="submit" name="Encrypt">
</form>

</body>
</html>
