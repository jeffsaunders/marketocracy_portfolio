<?php
/*
+----------------------------------------------------------------------------------------+
|						STRATIFICATION CALCULATION ROUTINE
+----------------------------------------------------------------------------------------+
*/
// Parse passed arguments string to $_REQUEST array (i.e. "first=1&second=2&third=3" -> $_REQUEST['first'] = 1, etc.)
parse_str($argv[1], $_REQUEST);

//get dependencies
require('/var/www/html/portfolio.marketocracy.com/secure/dbconnect.php');
require('/var/www/html/portfolio.marketocracy.com/web/includes/system-functions.php');
require('/var/www/html/portfolio.marketocracy.com/web/includes/system-debug-functions.php');


echo 'start\n';

$targetMemberID = $_REQUEST['memberID'];

//tables
$emailCampaigns			= 'email_campaigns';
$memberTracking			= 'members_fund_tracking';

$aCampaigns				= array();

#build array of campaigns
if($targetMemberID != ''){
	#get specific member campaigns
	$query = "
		SELECT *
		FROM ".$emailCampaigns."
		WHERE member_id=".$targetMemberID." AND camp_type='monthly_perf' AND camp_status='draft'
	";
}else{
	#get all member campaigns
	$query = "
		SELECT *
		FROM ".$emailCampaigns."
		WHERE camp_type='monthly_perf' AND camp_status='draft'
	";
}
try{
	$rsCamps = $mLink->prepare($query);
	$aValues = array();
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsCamps->execute($aValues);
}

catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}
			
				
while($campInfo = $rsCamps->fetch(PDO::FETCH_ASSOC)){	
	
	$aCampaigns[$campInfo['member_id']][$campInfo['camp_id']] = array(
		'camp_name'		=> $campInfo['camp_name'],
		'temp_id'		=> $campInfo['temp_id'],
		'email_title'	=> $campInfo['email_title'],
		'email_subject'	=> $campInfo['email_subject']
	);
	
}
//END build array of campaigns


echo '<pre>';
print_r($aCampaigns);
echo '</pre>';

#START | Loop through each campaign
foreach($aCampaigns as $memberID=>$campaign){
	
}

?>