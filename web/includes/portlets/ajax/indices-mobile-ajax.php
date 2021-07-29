<?php
//INDEX FEED PORTLET: This portlet is called in by ajax file: includes/scripts/system-indices.php which is included on the index.php page
session_start();

// Load debug & error logging functions
require_once("../../system-debug-functions.php");

require("../../../../secure/dbconnect.php");

require("../../system-functions.php");
$query = "
	SELECT *
	FROM ".$_SESSION['system_feeds_table']."
";

//Fund Symbols
try{
	$rsIndices = $mLink->prepare($query);
	$rsIndices->execute();
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$indices = $rsIndices->fetch(PDO::FETCH_ASSOC);

// Define the index column names & labels
$aIndex = array();

// S&P500
$aIndex[0][0] = "index_sp500";
$aIndex[0][1] = "S&amp;P 500";
$aIndex[0][2] = "INX";

// NASDAQ
$aIndex[1][0] = "index_nasdaq";
$aIndex[1][1] = "NASDAQ";
$aIndex[1][2] = "IXIC";

// Dow Jones
$aIndex[2][0] = "index_djia";
$aIndex[2][1] = "DOW";
$aIndex[2][2] = "DJI";

echo '
<div class="stats-overview stat-block" style="height:85px;">
';

for ($indexCnt = 0; $indexCnt <= 2; $indexCnt++){

	$indexArray = explode('|', $indices[$aIndex[$indexCnt][0]]);

	$indexPrice		= $indexArray[0];
	$indexChange	= $indexArray[1];
	$percentChange	= $indexArray[2];
	$statusColor	= $indexArray[3];
	$statusBar		= $indexArray[4];
	$timeUpdated 	= $indexArray[5];
?>
    <div class="details" style="width:33%; float:left; padding:0 0 0 5px;<?php echo ($indexCnt < 2 ? " border-right:solid 1px #CCCCCC;" : "") ?>">
        <div class="title">
			<strong><?php echo $aIndex[$indexCnt][1];?></strong> <small style="font-size:5pt;">(.<?php echo $aIndex[$indexCnt][2];?>)</small>
		</div>
        <div class="numbers" style="line-height:100%;">
			<?php echo $indexPrice;?><br>
			<span style="color:<?php echo $statusColor;?>;"><?php echo $percentChange;?></span>
		</div>
    </div>
<?php
}
?>
</div>

