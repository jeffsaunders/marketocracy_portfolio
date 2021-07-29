<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

$key = '8;ibch24.99a';
$value = 'F1E7DDEA34336FE988A2FC01D5809301';
//echo hex2bin($value)."<br><br>";
//echo hexdec($value)."<br><br>";
//$value = pack('H*', 'F1E7DDEA34336FE988A2FC01D5809301'); // same exact output as hex2bin()
//echo $value."<br><br>";

//$key='SGFwcHkgQmlydGhkYXkgUmFjaGVsIQ==';
//$value = 'a1iTYAo2HGX2VMZKRzQ8h/+8lTxpOvcLOe12V6DognU=';
//$td = mcrypt_module_open('rijndael-256', '', 'ecb', '');

//"ecb", "cbc", "cfb", "ofb", "nofb" or "stream"
$aCiphers = mcrypt_list_algorithms();
$aModes = array("ecb", "cbc", "cfb", "ofb", "nofb", "stream");
for ($cnt = 0; $cnt < count($aCiphers); $cnt++){
	for ($mode = 0; $mode < count($aModes); $mode++){
		$td = mcrypt_module_open($aCiphers[$cnt], '', $aModes[$mode], '');
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size( $td ), MCRYPT_RAND);
		mcrypt_generic_init($td, $key, $iv);
		$decryptedValue = mdecrypt_generic($td, base64_decode($value));
//		$decryptedValue = mdecrypt_generic($td, $value);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		echo $aCiphers[$cnt].", ".$aModes[$mode]." => ".$decryptedValue." <br>";
	}
}

echo "<br><br>-------------------------------------------------------------------------------------------<br><br>";

$key = '8;ibch24.99a';
$value = 'n0thing!';

//$key='SGFwcHkgQmlydGhkYXkgUmFjaGVsIQ==';
//$value = 'a1iTYAo2HGX2VMZKRzQ8h/+8lTxpOvcLOe12V6DognU=';
//$td = mcrypt_module_open('rijndael-256', '', 'ecb', '');

//"ecb", "cbc", "cfb", "ofb", "nofb" or "stream"
$aCiphers = mcrypt_list_algorithms();
$aModes = array("ecb", "cbc", "cfb", "ofb", "nofb", "stream");
for ($cnt = 0; $cnt < count($aCiphers); $cnt++){
	for ($mode = 0; $mode < count($aModes); $mode++){
		$td = mcrypt_module_open($aCiphers[$cnt], '', $aModes[$mode], '');
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size( $td ), MCRYPT_RAND);
		mcrypt_generic_init($td, $key, $iv);
		$encryptedValue = mcrypt_generic($td, $value);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
//		echo $aCiphers[$cnt].", ".$aModes[$mode]." => ".base64_encode($encryptedValue)." <br>";
//		echo $aCiphers[$cnt].", ".$aModes[$mode]." => ".bin2hex(base64_encode($encryptedValue))." <br>";
		echo $aCiphers[$cnt].", ".$aModes[$mode]." => ".array_shift( unpack('H*', $encryptedValue))." <br>";
	}
}

echo "<br><br>-------------------------------------------------------------------------------------------<br><br>";

/*
 * Convert a hex string to ASCII with hexstr()
 * Convert a ASCII string to a hex string with strhex()
 *
 *
 * Paul Gregg <pgregg@pgregg.com>
 * 3 October 2003
 *
 * Open Source Code:   If you use this code on your site for public
 * access (i.e. on the Internet) then you must attribute the author and
 * source web site: http://www.pgregg.com/projects/php/code/hexstr.phps
 *
 */
/*
Function hexstr($hexstr) {
  $hexstr = str_replace(' ', '', $hexstr);
  $hexstr = str_replace('\x', '', $hexstr);
  $retstr = pack('H*', $hexstr);
  return $retstr;
}

Function strhex($string) {
  $hexstr = unpack('H*', $string);
  return array_shift($hexstr);
}

$teststr = "64 65 74 61 69 6c 73";
#$teststr = "01 02 63 00 39 00 45 00 36 00 43 00 32 00 30 00 41 00 30 00 00 00";

ini_set('display_errors',1);
error_reporting(E_ALL);

$ascii_inputs = array("details", "abcde");
$hex_inputs = array("64 65 74 61 69 6c 73", "64657461696c73", '\x64\x65\x74\x61\x69\x6c\x73');

print "<pre>";

foreach ($ascii_inputs as $str) {
  $str2 = strhex($str);
  printf("strhex('%s') = %s  [%s]\n", $str, var_export($str2, true), implode(" ", str_split($str2, 2)));
}

foreach ($hex_inputs as $str) {
  $str2 = hexstr($str);
  printf("hexstr('%s') = %s\n", $str, var_export($str2, true));
}


print "</pre><hr>\n";
show_source(__FILE__);
*/
?>