<?php
// This utility uses howsmyssl.com to test what version of TLS is implemented - JSS

$ch = curl_init('https://www.howsmyssl.com/a/check');

// The following forces TLS v1.2...comment out to check default
//curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$data = curl_exec($ch);
curl_close($ch);

$json = json_decode($data);
echo $json->tls_version;
echo "\n";

?>
