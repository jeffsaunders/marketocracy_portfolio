<?php
			$session = curl_init("http://www.nasdaq.com/aspx/indexrowhtml.aspx");
			curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
			$feed = curl_exec($session);
			// Convert JSON to PHP object
//			$phpObj =  json_decode($json);

//echo $feed;

/*
document.write('<table class="indextext" cellpadding="0" cellspacing="0" border="0" ><tr><td NOWRAP class="indexmktstatus" valign="bottom">US&nbsp;Market&nbsp;Closed</td><td nowrap valign="bottom">&nbsp;NASDAQ&nbsp;4075.56 &nbsp;<span class="red">-72.78</span>&nbsp;<img src="http://content.nasdaq.com/images/redarrowsmall.gif" border="0" height="7" width="7" alt="">&nbsp;<span class="red">-1.75%</span>&nbsp;|&nbsp;DJIA&nbsp;16361.46 &nbsp;<span class="red">-140.19</span>&nbsp;<img src="http://content.nasdaq.com/images/redarrowsmall.gif" border="0" height="7" width="7" alt="">&nbsp;<span class="red">-0.85%</span>&nbsp;|&nbsp;S&amp;P&nbsp;1863.4 &nbsp;<span class="red">-15.21</span>&nbsp;<img src="http://content.nasdaq.com/images/redarrowsmall.gif" border="0" height="7" width="7" alt="">&nbsp;<span class="red">-0.81%</span> </td></tr></table>');
*/

$subString = str_replace('&nbsp;', ' ', $feed);
$subString = strstr($subString, 'S&amp;P');
$subString = strstr($subString, ' ');
$subString = substr($subString, 1);

$indexPrice = strstr($subString, ' ', true);

$subString = strstr($subString, '</span> ', true);
$subString = strstr($subString, '>');
$indexChange = substr($subString, 1);


//				$indexChange	= $index->Change;

echo $indexPrice;
echo "\n";
echo $indexChange;

?>