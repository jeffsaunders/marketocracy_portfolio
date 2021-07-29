<?php
//INDEX FEED PORTLET: This portlet is called in by ajax file: includes/scripts/system-indices.php which is included on the index.php page
session_start();

// Load debug & error logging functions
require_once("../../system-debug-functions.php");

require("../../../../secure/dbconnect.php");

require("../../system-functions.php");
$query = "
	SELECT index_nasdaq
	FROM ".$_SESSION['system_feeds_table']."
";

//Fund Symbols
try{
	$rsNASDAQ = $mLink->prepare($query);
	$aValues = array(
		':member_id' => $_SESSION['member_id']
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsNASDAQ->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$NASDAQ = $rsNASDAQ->fetch(PDO::FETCH_ASSOC);

$indexArray = explode('|', $NASDAQ['index_nasdaq']);

$indexPrice		= $indexArray[0];
$indexChange	= $indexArray[1];
$percentChange	= $indexArray[2];
$statusColor	= $indexArray[3];
$statusBar		= $indexArray[4];
$timeUpdated 	= $indexArray[5];
?>
<div class="stats-overview stat-block">
    <div class="display stat huge">
       <div class="percent"></div>
    </div>
    <div class="details">
        <div class="title"><strong>NASDAQ</strong><!-- - <?php echo date('g:i:s');?> --><br /><small>.IXIC - <?php echo $timeUpdated ;?></small></div>
        <div class="numbers"><?php echo $indexPrice;?><br><small style="color:<?php echo $statusColor;?>"><?php echo $indexChange;?> (<?php echo $percentChange;?>)</small></div>
        <div class="progress">
          <span style="width: 100%;" class="progress-bar progress-bar-<?php echo $statusBar;?>" aria-valuenow="0.05" aria-valuemin="0" aria-valuemax="100">
          </span>
        </div>
    </div>
</div>
