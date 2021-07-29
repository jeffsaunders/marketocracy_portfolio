<?php
$ch = curl_init();
curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; U; Linux i686; pt-BR; rv:1.9.2.18) Gecko/20110628 Ubuntu/10.04 (lucid) Firefox/3.6.18' );
curl_setopt( $ch, CURLOPT_URL, 'http://www.nasdaq.com/aspx/indexrowhtml.aspx' );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
$result = curl_exec ( $ch );
curl_close ( $ch );

$result = str_replace("document.write('", "", $result);
$result = str_replace("');", "", $result);

echo $result;
?>
