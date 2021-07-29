<?php
// Quick report to dump all members to a spreadsheet - JS 10/08/13

// Make sure the user is authorized to run this report
session_start();
// Is anyone even logged in?
if (!isset($_SESSION['USER_ID'])){
	// Bounce over to the login screen
	header( 'Location: http://new.ewomennetwork.com/signin.php' );
}

// Ok, see if they are worthy

// Connect to database
if (!($ewnLink = mysql_connect("192.168.15.100","eWNWebSites","zNyU59LFry5E9ajf"))){
    die("<h3>Error - Could Not Connect to the eWomenNetwork Database</h3>".mysql_error()."\n");
}
mysql_select_db("ewomen", $ewnLink);

if (!($ewsnLink = mysql_connect("192.168.15.100","eWNWebSites","zNyU59LFry5E9ajf",true))){
    die("<h3>Error - Could Not Connect to the eWomenSpeakersNetwork Database</h3>".mysql_error()."\n");
}
mysql_select_db("ewsn", $ewsnLink);

$query = sprintf("
	SELECT *
	FROM u_sec_access
	WHERE user_id = '".$_SESSION['USER_ID']."'
	AND group_id = '1030'
");
//echo $query."<br>";
$rs_authenticate = mysql_query($query, $ewnLink);
// Denied!
if (mysql_num_rows($rs_authenticate) == 0){
	echo '
		<div align="center">
			<br>
			<font color="#FF0000" style="font-family: Arial,Helvetica,sans-serif; font-size:14px;"><strong>You are not authorized to access this information.</strong></font>
			<br><br>
			<a href="https://www.ewomennetwork.com/admin/index.php" target="_self" style="font-family: Arial,Helvetica,sans-serif; font-size:11px;">Return to Admin Menu</a>
		</div>
	';
	exit;
}
// You're in!

// Go get 'em
// eWN - MEMBERS
//$query = "
//	SELECT up.company_name, up.city, up.state_province
//	FROM u_flag uf, u_personal up
//	WHERE up.user_id = uf.user_id
//";
// eWN OTHERS
$query = "
	SELECT company_name, city, state_province
	FROM u_personal up
	WHERE user_id NOT IN (
    	SELECT user_id
    	FROM u_flag
    )
";
$rs_eWN = mysql_query($query, $ewnLink);

// eWSN
$query = "
	SELECT company_name, city, state
	FROM users
	WHERE city <> 'Test'
";
$rs_eWSN = mysql_query($query, $ewsnLink);

// Excel functions
function xlsBOF($Filename){
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Type: application/force-download");
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/download");;
	header("Content-Disposition: attachment;filename=".$Filename."");
	header("Content-Transfer-Encoding: binary ");
//	echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
	return;
}

function xlsEOF(){
	echo pack("ss", 0x0A, 0x00);
	return;
}

// Initialize the spreadsheet file
xlsBOF("eWNAllMembersReport.csv");

// Build the header row --
// Grab the first row
$row = mysql_fetch_assoc($rs_eWN);
$sHeader = "";
// Grab just the column names
foreach ($row as $key => $value){
	$sHeader .= $key.",";
}
// Chop off the trailing comma
$sHeader = substr($sHeader, 0, -1);
// Write the header row
echo $sHeader;
// Must send this seperately - it is ignored by Excel if appended to the header string
echo "\n";

// Build the data rows --
// Start from the top
mysql_data_seek($rs_eWN, 0);

// Loop through eWN
while ($row = mysql_fetch_assoc($rs_eWN)){
	// Initialize a clean row
	$sRow = "";
	foreach ($row as $key => $value){
		$sRow .= '"'.str_replace("\"", "\"\"", $value).'",';
	}
	// Chop off the trailing comma
	$sRow = substr($sRow, 0, -1);
	// Write data row
	echo $sRow;
	echo "\n";
}

// Loop through eWSN
while ($row = mysql_fetch_assoc($rs_eWSN)){
	// Initialize a clean row
	$sRow = "";
	foreach ($row as $key => $value){
		$sRow .= '"'.str_replace("\"", "\"\"", $value).'",';
	}
	// Chop off the trailing comma
	$sRow = substr($sRow, 0, -1);
	// Write data row
	echo $sRow;
	echo "\n";
}

// Close 'er up
xlsEOF();

?>
