<?php
//INDEX FEED PORTLET: This portlet is called in by ajax file: includes/scripts/system-indices.php which is included on the index.php page
session_start();

// load required includes

// Load debug & error logging functions
require_once("../../system-debug-functions.php");

require("../../../../secure/dbconnect.php");

require("../../system-functions.php");

// Assign passed values
$indexName = htmlspecialchars($_REQUEST['index']);
$indexLabel = urlencode(htmlspecialchars($_REQUEST['label']));
$indexSymbol = urlencode(htmlspecialchars($_REQUEST['symbol']));

// Get the most recent values
$query = "
	SELECT index_".$indexName.", timestamp
	FROM ".$_SESSION['system_feeds_table']."
	LIMIT 1
";

try{
	$rsStatus = $mLink->prepare($query);
	$rsStatus->execute();
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$status = $rsStatus->fetch(PDO::FETCH_ASSOC);

// Blow it apart and assign values
$indexArray = explode('|', $status['index_'.$indexName]);
$indexPrice		= $indexArray[0];
$indexChange	= $indexArray[1];
$percentChange	= $indexArray[2];
$statusColor	= $indexArray[3];
$statusBar		= $indexArray[4];
$timeUpdated 	= $indexArray[5];

// Determine if the values are relatively current or pretty old (more than 30 minutes)
// Usually delayed due to failing YQL feed for a particular index (DJIA, I'm looking at you!)
$delayed = false;
// If the markets are open, check based on now
if (isMarketOpen(time(), $mLink, "none")){
	if (time() - strtotime($timeUpdated) > 1800){ //30 minutes old
		$delayed = true;
	}
}else{  // If the closing time (accounting for early closing) on the date of the last index update minus the last good time is more than 30 minutes, it closed delayed
	if (strtotime(date('j-n-Y', $status['timestamp']).(isMarketHoliday($timestamp, $mLink) == "E" ? " 1:02" : " 4:02").' PM America/New_York') - strtotime($timeUpdated) > 1800){
		if (time() - strtotime(date('j-n-Y', time())." 9:30 AM America/New_York") > 120){ // Give it a couple minutes to get the first update
			$delayed = true;
		}
	}
}
// Return the portlet content

?>
<div class="stats-overview stat-block">
    <div class="details">
        <div class="title">
			<strong><?php echo urldecode($indexLabel);?></strong> <small style="font-size:10px;">(.<?php echo $indexSymbol;?>)</small><?php echo ($delayed ? "&nbsp;&nbsp;<strong style='font-size:11px; color:#FF0000;'>*STALLED</strong>" : "");?><br>
			<small><?php echo ($delayed ? " <span style='color:#FF0000;'>".$timeUpdated."</span>" : $timeUpdated);?></small>
		</div>
        <div class="numbers">
			<?php echo $indexPrice;?><br>
			<small style="color:<?php echo $statusColor;?>"><?php echo $indexChange;?> (<?php echo $percentChange;?>)</small>
		</div>
        <div class="progress">
        	<span style="width: 100%;" class="progress-bar progress-bar-<?php echo $statusBar;?>" aria-valuenow="0.05" aria-valuemin="0" aria-valuemax="100"></span>
        </div>
    </div>
</div>
