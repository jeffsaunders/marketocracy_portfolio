<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<title>Unixtime</title>
</head>

<body>

<form action="" name="unixtime" id="unixtime">
	Enter UnixTime <span style="font-size:9pt">(leave blank for "NOW")</span>:
	<input type="text" name="unixtime" size='15'>
	<input type="submit" name="Convert">
</form>
<?php
/*
select upper(substring(m.fb_primarykey, 3, 24)) as managerkey, m.username as loginname, m.email as email, c.password as password
from members m, clear_passwords c
where c.username = m.username

select username as loginname, password
from clear_passwords
*/

// testing (DST) 1435708800

// Set the time to convert
$unixtime = $_REQUEST['unixtime'];
if ($unixtime == ''){
	$unixtime = time();
}

// Step through the timezones and convert to "local" time
date_default_timezone_set('UTC');
$UTC = date("m/d/Y @ h:i:s a (P)", $unixtime);
date_default_timezone_set('America/New_York');
$ET = date("m/d/Y @ h:i:s a (P)", $unixtime);
date_default_timezone_set('America/Chicago');
$CT = date("m/d/Y @ h:i:s a (P)", $unixtime);
date_default_timezone_set('America/Denver');
$MT = date("m/d/Y @ h:i:s a (P)", $unixtime);
date_default_timezone_set('America/Los_Angeles');
$PT = date("m/d/Y @ h:i:s a (P)", $unixtime);

// Display it
echo "
	<table border='1'>
	<tr style='font-weight:bold'>
		<td>Unixtime:</td>
		<td>".$unixtime."</td>
	</tr>
	<tr>
		<td>Greenwich Mean Time (UTC):</td>
		<td>".$UTC."</td>
	</tr>
	<tr>
		<td>Eastern ".(date('I', $unixtime) == 1 ? "Daylight" : "Standard")." Time (E".(date('I', $unixtime) == 1 ? "D" : "S")."T):</td>
		<td>".$ET."</td>
	</tr>
	<tr>
		<td>Central ".(date('I', $unixtime) == 1 ? "Daylight" : "Standard")." Time (C".(date('I', $unixtime) == 1 ? "D" : "S")."T):</td>
		<td>".$CT."</td>
	</tr>
	<tr>
		<td>Mountain ".(date('I', $unixtime) == 1 ? "Daylight" : "Standard")." Time (M".(date('I', $unixtime) == 1 ? "D" : "S")."T):</td>
		<td>".$MT."</td>
	</tr>
	<tr>
		<td>Pacific ".(date('I', $unixtime) == 1 ? "Daylight" : "Standard")." Time (P".(date('I', $unixtime) == 1 ? "D" : "S")."T):</td>
		<td>".$PT."</td>
	</tr>
	<table>
	<br>
";

?>

</body>
</html>
