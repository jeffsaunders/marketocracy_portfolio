<?php

session_start();

// Load debug & error logging functions
require_once("../includes/system-debug-functions.php");

//Connect to DB
require_once("../../secure/dbconnect.php");

require_once("../includes/system-functions.php");

$memberID 	= $_REQUEST['member_id'];
$fundSymbol = $_REQUEST['fund_symbol'];
$fundName 	= $_REQUEST['fund_name'];
$fundID 	= $_REQUEST['fund_id'];
$seqID 		= $_REQUEST['seq_id'];
$desc		= $_REQUEST['desc'];
$fundColor	= $_REQUEST['fund_color'];
$date		= $_REQUEST['date'];
$short 		= $_REQUEST['short'];

$year = substr($date, 0, 4);
	
$month = substr($date, 4, 2);

$day = substr($date, 6, 2);

$unixtime = mktime('0','0','0',$month,$day,$year);


/*echo $memberID;
echo "|";
echo $fundSymbol;
echo "|";
echo $fundName;
echo "|";
echo $fundID;*/

$query = "
	INSERT INTO members_fund (
		fund_id,
		seq_id,
		member_id,
		fund_name,
		fund_symbol,
		description,
		active,
		version,
		inception_date,
		unix_date,
		short_fund,
		timestamp
	) VALUE (
		:fund_id,
		:seq_id,
		:member_id,
		:fund_name,
		:fund_symbol,
		:desc,
		1,
		1,
		:inception_date,
		:unix_date,
		:short_fund,
		UNIX_TIMESTAMP()
	)
";

try{
	$rsAddTopic = $mLink->prepare($query);
	$aValues = array(
		':fund_id'			=> $fundID,
		':seq_id'			=> $seqID,
		':member_id'		=> $memberID,
		':fund_name'		=> $fundName,
		':fund_symbol'		=> $fundSymbol,
		':desc'				=> $desc,
		':unix_date'		=> $unixtime,
		':inception_date'	=> $date,
		':short_fund'		=> $short
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsAddTopic->execute($aValues);
}

catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

echo $error;

$query = "
	INSERT INTO members_fund_settings (
		fund_id,
		overview_col1,
		overview_col2,
		fund_color,
		timestamp
	) VALUE (
		:fund_id,
		:overview_col1,
		:overview_col2,
		:fund_color,
		UNIX_TIMESTAMP()
	)
";

try{
	$rsAddTopic = $mLink->prepare($query);
	$aValues = array(
		':fund_id'			=> $fundID,
		':fund_color'		=> $fundColor,
		':overview_col1'	=> 'fund-price-history~0~0~0|fund-info~0~0~0|fund-turnover~0~0~0',
		':overview_col2'	=> 'fund-returns-index~0~0~0|fund-recent-returns~0~0~0|fund-alpha-beta~0~0~0'
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsAddTopic->execute($aValues);
}

catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

echo $error;

header("Location: create-fund.php?member=".$memberID."");

?>