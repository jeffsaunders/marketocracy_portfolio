<?php 
session_start();

// Load debug & error logging functions
require_once("../includes/system-debug-functions.php");

//Connect to DB
require_once("../../secure/dbconnect.php");

require_once("../includes/system-functions.php");

$memberID = $_REQUEST['member'];

$query = "
	SELECT fund_id, fund_symbol, fund_name, seq_id
	FROM members_fund
	WHERE member_id=:member_id
	ORDER BY seq_id DESC
	LIMIT 1
";

try{
	$rsGetFunds = $mLink->prepare($query);
	$aValues = array(
		':member_id' 	=> $memberID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsGetFunds->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}
$fund = $rsGetFunds->fetch(PDO::FETCH_ASSOC);

$seqID = $fund['seq_id'];

$newSeqID = $seqID + 1;

$newFundID = ''.$memberID.'-'.$newSeqID.'';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CREATE FUND</title>
</head>

<body>

<form method="post" action="fund-process.php">
MemberID:<br />
<input type="text" name="member_id" value="<?php echo $memberID;?>" /><br />

Fund ID:<br />
<input type="text" name="fund_id" value="<?php echo $newFundID; ?>" /><br />

Sequence ID:<br />
<input type="text" name="seq_id" value="<?php echo $newSeqID; ?>" /><br />

Fund Symbol:<br />
<input type="text" name="fund_symbol"  /><br />

Fund Name:<br />
<input type="text" name="fund_name" /><br />

Fund Inception Date (YYYYMMDD):<br />
<input type="text" name="date" /><br />

Description:<br />
<textarea name="desc"></textarea><br />

Fund Color:<br />
<select name="fund_color">
	<option value="#57b5e3">BLUE</option>
    <option value="#ed4e2a">RED</option>
    <option value="3cc05a">GREEN</option>
</select>
<br />

Short Fund<br />
<select name="short">
	<option value="0">No</option>
    <option value="1">Yes</option>
</select><br /><br />

<input type="submit" name="submit" value="Create New Fund" />

</form>


<h3>Current Funds</h3>

<table border="1" cellpadding="5">
<tr>
	<th>Fund ID</th>
    <th>Fund Symbol</th>
    <th>Fund Name</th>
    <th>Fund Inception</th>
    <th>Description</th>
    <th>SEQ ID</th>
    <th>Fund Color</th>
</tr>

<?php
$query = "
	SELECT mf.*, fs.fund_color
	FROM members_fund as mf
	INNER JOIN members_fund_settings as fs ON mf.fund_id=fs.fund_id
	WHERE mf.member_id=:member_id
	ORDER BY mf.seq_id ASC
";

try{
	$rsGetFunds = $mLink->prepare($query);
	$aValues = array(
		':member_id' 	=> $memberID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsGetFunds->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}
while($fund = $rsGetFunds->fetch(PDO::FETCH_ASSOC)) {
	
	?>
    <tr>
    	<td><?php echo $fund['fund_id'];?></td>
        <td><?php echo $fund['fund_symbol'];?></td>
        <td><?php echo $fund['fund_name'];?></td>
        <td><?php echo date('m/d/Y', $fund['unix_date']);?></td>
        <td><?php echo substr($fund['description'], 0, 200);?></td>
        <td><?php echo $fund['seq_id'];?></td>
        <td bgcolor="<?php echo $fund['fund_color'];?>"><?php echo $fund['fund_color'];?></td>
    </tr>
    <?php
}
?>

</table>

</body>
</html>