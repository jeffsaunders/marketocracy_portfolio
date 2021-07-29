<?php
$query = "
	SELECT fund_symbol, fund_id 
	FROM ".$_SESSION['fund_table']."
	WHERE member_id=:member_id AND fund_id=:fund_id AND active=1
";

//Fund Symbols
try{
	$rsGetFund = $mLink->prepare($query);
	$aValues = array(
		':member_id' 	=> $_SESSION['member_id'],
		':fund_id'		=> $portVar1
		
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsGetFund->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$fundInfo = $rsGetFund->fetch(PDO::FETCH_ASSOC);

$query = "
	SELECT * 
	FROM ".$_SESSION['fund_settings_table']."
	WHERE fund_id=:fund_id
";

//Fund Symbols
try{
	$rsFundSettings = $mLink->prepare($query);
	$aValues = array(
		':fund_id'	=> $portVar1
		
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsFundSettings->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$fundSettings = $rsFundSettings->fetch(PDO::FETCH_ASSOC);

$addFundColor = 'style="border-bottom: 5px solid '.$fundSettings['fund_color'].' !important;"';
?>