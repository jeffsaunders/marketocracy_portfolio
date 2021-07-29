<?php

session_start();

// Load debug & error logging functions
require_once("../../system-debug-functions.php");

require("../../../../secure/dbconnect.php");

require("../../system-functions.php");

?>

<div class="portlet-body">
    <table class="table table-striped table-bordered table-hover table-full-width" id="member_fund_ticker_table" style="margin:0;">
        <thead>
            <tr>
            	<th>Fund</th>
                <th>Stock Value</th>
                <th>Cash Value</th>
                <th>Total Value</th>
                <th>NAV</th>
                <th>Change</th>
            </tr>
        </thead>
	    <tbody>

<?php

// Get the member's fund ids
$query = "
	SELECT fund_id, unix_date, fund_name, fund_symbol, description, short_fund
	FROM ".$_SESSION['fund_table']."
	WHERE member_id = :member_id
	AND active = '1'
";
try {
	$rsFunds = $mLink->prepare($query);
	$aValues = array(
		':member_id' => $_SESSION['member_id']
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
	$rsFunds->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

// Get a current livePrice for each fund
// This won't work here.  While it would work for this facility, it's dual purpose was to perform a livePrice for everyone who is logged in every few minutes, for other charts and tables as well.  problem is, it will only execute here if the portlet that calls this script is in focus - livePrice needs to run regardless.  Need to add it to the heartbeat daemon instead, after whcih it is no longer needed here since that will run it every few minutes instead.
while ($fund = $rsFunds->fetch(PDO::FETCH_ASSOC)){

	$query = "livePrice|".$_SESSION['username']."|".$fund['fund_id']."|".$fund['fund_symbol'];
//echo $query."<br>";
//die();
	// Set the port number for the API call
	$port = rand(52000, 52499);

	// Include the API call
	include("../../includes/data-query-legacy.php");

	// Wait a tick
	sleep(1);
}

// Give the livePrice request a li'l time to finish
sleep(5);

// Now get the member's fund ids again (PDO on MySQL won't allow the reuse of the first result set (FIX THIS ORACLE!)
$rsFunds->execute($aValues);

// Step through them again and update the table
while ($fund = $rsFunds->fetch(PDO::FETCH_ASSOC)){

	$query = "
		SELECT totalValue
		FROM ".$_SESSION['fund_pricing_table']."
		WHERE fund_id = :fund_id
		ORDER By timestamp DESC
		LIMIT 1
	";
	try {
		$rsClosePrice = $mLink->prepare($query);
		$aValues = array(
			':fund_id' => $fund['fund_id']
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	//die($preparedQuery);
		$rsClosePrice->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$closePrice = $rsClosePrice->fetch(PDO::FETCH_ASSOC);








	$query = "
		SELECT nav, stockValue, cashValue, totalValue
		FROM ".$_SESSION['fund_liveprice_table']."
		WHERE fund_id = :fund_id
		ORDER By timestamp DESC
		LIMIT 1
	";
	try {
		$rsLivePrice = $mLink->prepare($query);
		$aValues = array(
			':fund_id' => $fund['fund_id']
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	//die($preparedQuery);
		$rsLivePrice->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$livePrice = $rsLivePrice->fetch(PDO::FETCH_ASSOC);

/*
print_r($fund);
echo "<br>";
print_r($closePrice);
echo "<br>";
print_r($livePrice);
echo "<br>";
$row = date("m/d/Y", $fund['unix_date'])."|";
$row .= $fund['fund_name']."|";
$row .= $fund['fund_symbol']."|";
$row .= $fund['description']."|";
$row .= ($fund['short_fund'] == "1" ? "Short Fund" : "Long Fund")."|";
$row .= round($livePrice['nav'], 2)."|";
$row .= round($livePrice['stockValue'], 2)."|";
$row .= round($livePrice['cashValue'], 2)."|";
$row .= round($livePrice['stockValue'], 2) + round($livePrice['cashValue'], 2)."|";
$row .= round($closePrice['totalValue'], 2)."|";
$row .= round($livePrice['totalValue'] - $closePrice['totalValue'], 2)."|";
$row .= number_format(round((($livePrice['totalValue'] - $closePrice['totalValue'])/$livePrice['totalValue'])*100, 2), 2);

echo $row;

echo "<br><br>";
*/

$bubble = ""; // bubble data (inception, fund type, nav, description)

?>
			<tr>
				<td><?php echo $fund['fund_name']; ?> (<?php echo $fund['fund_symbol']; ?>)</td>
				<td><?php echo number_format(round($livePrice['stockValue'], 2), 2); ?></td>
				<td><?php echo number_format(round($livePrice['cashValue'], 2), 2); ?></td>
				<td><?php echo number_format(round($livePrice['stockValue'], 2) + round($livePrice['cashValue'], 2), 2); ?></td>
				<td><?php echo number_format(round($livePrice['nav'], 2), 2); ?> (<?php echo (round(((round($livePrice['nav'], 2) - 10)/10)*100, 2) >= 0 ? "+" : "").number_format(round(((round($livePrice['nav'], 2) - 10)/10)*100, 2), 2)."%"; ?>)</td>
				<td><?php echo (round($livePrice['totalValue'] - $closePrice['totalValue'], 2) >= 0 ? "+" : "").number_format(round($livePrice['totalValue'] - $closePrice['totalValue'], 2), 2); ?> (<?php echo (round($livePrice['totalValue'] - $closePrice['totalValue'], 2) >= 0 && round(((round($livePrice['totalValue'], 2) - round($closePrice['totalValue'], 2))/round($closePrice['totalValue'], 2))*100, 2) >= 0 ? "+" : "").number_format(round(((round($livePrice['totalValue'], 2) - round($closePrice['totalValue'], 2))/round($closePrice['totalValue'], 2))*100, 2), 2)."%"; ?>)</td>
			</tr>
<?php
}
?>
	    </tbody>
    </table>
</div><!--portlet-body-->
<?php
/*
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
$aIndex[2][1] = "Dow";
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
			<strong><?php echo $aIndex[$indexCnt][1];?></strong> <small style="font-size:10px;">(.<?php echo $aIndex[$indexCnt][2];?>)</small>
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
*/
?>
