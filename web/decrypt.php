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
	echo $output;
}
?>
