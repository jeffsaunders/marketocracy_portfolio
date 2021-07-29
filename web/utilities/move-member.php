<?php 
session_start();

// Load debug & error logging functions
require_once("../includes/system-debug-functions.php");

//Connect to DB
require_once("../../secure/dbconnect.php");

require_once("../includes/system-functions.php");

$memberID = $_REQUEST['member'];

$query = "
	SELECT *
	FROM members
	WHERE member_id=:member_id
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
$member = $rsGetFunds->fetch(PDO::FETCH_ASSOC);



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Update Member</title>
</head>

<body>
<h1>Update User and Auto Fill Database</h1>

<form method="post" action="member-process.php">
MemberID:<br />
<input type="text" name="member_id" value="<?php echo $memberID;?>" /><br />

Email:<br />
<input type="text" name="email" id="email" value="<?php echo $member['email'];?>"/><br />

Password:<br />
<input type="text" name="password" /><br />

Username:<br />
<input type="text" name="username" value="<?php echo $member['username'];?>"/><br />


<input type="submit" name="submit" value="Auto Fill DB Fields" />

</form>



</body>
</html>