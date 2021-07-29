<?php
//Start session
session_start();

//Get Site Functions
require("../web/includes/system-functions.php");
require("../secure/dbconnect.php");

//Set YAHOO YQL(Yahoo Query Language) JSON URL
//DJIA
//$yqlURL = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20*%20FROM%20yahoo.finance.quote%20WHERE%20symbol%3D'INDU'&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";

//NASDAQ
//$yqlURL = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20*%20FROM%20yahoo.finance.quote%20WHERE%20symbol%3D'%5Eixic'&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";

//S&P
$yqlURL = "https://query.yahooapis.com/v1/public/yql?q=SELECT%20*%20FROM%20yahoo.finance.quote%20WHERE%20symbol%3D'%5Egspc'&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";

// Make call with cURL  
$session = curl_init($yqlURL);  
curl_setopt($session, CURLOPT_RETURNTRANSFER,true);  
$json = curl_exec($session); 
// Convert JSON to PHP object   
$phpObj =  json_decode($json); 

//Set Variables from returned  results
foreach($phpObj->query->results as $index){
	$indexName 		= $index->Name;
	$indexChange	= $index->Change;
	$indexPrice		= $index->LastTradePriceOnly;
}

//Check to see if $indexChange is positive or negative (for styling)
if(strpos(substr($indexChange, 0, 1), '+') !== FALSE){
	$statusColor 	= "#3cc051";
	$statusBar 		= "success";
	$operator		= "+";	
}else{
	$statusColor 	= "#ed4e2a";	
	$statusBar 		= "danger";
	$operator		= "-";
}

//Remove the operator from the front of the string so we can perform some math
$indexChangeNoOp = substr($indexChange, 1);

//Calculate the percent change and round it off to the hundreths place
$percentChange = round(($indexChangeNoOp/$indexPrice)*100, 2);

//Round the Index change to the hundreths place
$indexChange = round($indexChange, 2);
//Add operator back to the front of the string (Rounding removes this)
if($operator != "-"){
	$indexChange = "".$operator."".$indexChange."";
}

//Format the Index Price to the hundreths place and add commas
$indexPrice = number_format($indexPrice, 2, '.', ',');

//String together all variables to store in DB as an array seperated by "|"
$updateIndex = "".$indexPrice."|".$indexChange."|".$percentChange."|".$statusColor."|".$statusBar."|".date('M j g:i:s A')." EST";

//Check to see if the Yahoo JSON failed. If it is successful, update database
if($indexPrice != "0.00") {
	$query = "
		UPDATE ".$_SESSION['system_feeds_table']." 
		SET	index_sp500=:index_sp500,
			timestamp=UNIX_TIMESTAMP()
	";
	try {
		$rsUpdate = $mLink->prepare($query);
		$aValues = array(
			':index_sp500'	=> $updateIndex,
		);
		// Prepared query - for error logging and debugging
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsUpdate->execute($aValues);
	}
	catch(PDOException $error){
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	echo "JSON Successful - Database was updated: ".$updateIndex."";
	
}else{
	echo "JSON Failed - Datebase was not updated.";	
}
?>
<div class="title"><strong>S&amp;P 500</strong> <br/><small>.INX - <?php echo date('M j g:i:s A');?> EST</small></div>
<div class="numbers"><?php echo $indexPrice;?> <small style="color:<?php echo $statusColor;?>"><?php echo $indexChange;?> (<?php echo $percentChange;?>%)</small></div>
<div class="progress">
  <span style="width: 100%;" class="progress-bar progress-bar-<?php echo $statusBar;?>" aria-valuenow="0.05" aria-valuemin="0" aria-valuemax="100">
  </span>
</div>