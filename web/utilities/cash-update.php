<?php
/*session start and requires are used for testing, can be removed*/
session_start();
require_once("../includes/system-debug-functions.php");
require_once("../../secure/dbconnect.php");
require_once("../includes/system-functions.php");



//set these variables as you see fit or exchange the variables that are already in use with your code, all of these are required in order for the below code to execute properly 
$fundID	= $_REQUEST['fund_id']; /*used to determine which fund to adjust*/
$shares = $_REQUEST['sharesFilled']; /*used to calculate new stock value, this is the amount of shares that were FILLED not the amount that was ordered, very important*/
$price	= $_REQUEST['price']; /*used to calculate new stock value, this is the closing price of the ticket*/
$net	= $_REQUEST['net']; /*used to calculate new cashValue, this is the total net value*/
$type	= $_REQUEST['buyOrSell']; /*used to determine wether to add or subtract*/

//Get current LivePrice Values
$query = "
	SELECT *
	FROM members_fund_liveprice
	WHERE fund_id=:fund_id
";
try{
	$rsGetLP = $mLink->prepare($query);
	$aValues = array(
		':fund_id'	=> $fundID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsGetLP->execute($aValues);
}
catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}
$lp = $rsGetLP->fetch(PDO::FETCH_ASSOC);

$currentNav			= $lp['nav']; /*not required, used for testing, can be removed*/
$currentStockValue	= $lp['stockValue'];
$currentCashValue 	= $lp['cashValue'];
$fundShares			= $lp['shares'];

echo 'NAV: '.$currentNav.' | Current Cash: '.$currentCashValue.' | Current Stock'.$currentStockValue.' | Shares'.$fundShares.' <br /><br />'; /*used for testing, can be removed*/

//switch on the type to determine the proper opperators 
switch($type){
	
	case 'buy':
		
		#calculate new stock value
		$positionValue 	= ($price * $shares);
		$stockValue 	= ($currentStockValue + $positionValue);
		
		#calculate new cash value
		$cashValue		= ($currentCashValue - $net);
		
	break;
	
	case 'sell':
		
		#calculate new stock value
		$positionValue 	= ($price * $shares);
		$stockValue 	= ($currentStockValue - $positionValue);
		
		#calculate new cash value
		$cashValue		= ($currentCashValue + $net);
		
	break;	
}

//echo $positionValue;

//calculate total value and NAV
$totalValue = ($stockValue + $cashValue);
$nav		= ($totalValue / $fundShares);

//UPdate LivePrice table
$query = "
	UPDATE members_fund_liveprice
	SET nav=:nav, stockValue=:stockValue, cashValue=:cashValue, totalValue=:totalValue, legacy='0'
	WHERE fund_id=:fund_id
";
try{
	$rsUpdate = $mLink->prepare($query);
	$aValues = array(
		':nav'			=> $nav,
		':stockValue'	=> $stockValue,
		':cashValue'	=> $cashValue,
		':totalValue'	=> $totalValue,
		':fund_id'		=> $fundID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsUpdate->execute($aValues);
}


catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}
echo $preparedQuery; /*used for testing, can be removed*/
?>