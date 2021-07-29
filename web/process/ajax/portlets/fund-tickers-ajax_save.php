<?php
/*
Marketocracy Inc. | Beta Development
Fund Indices(Tickers)

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/portlets/fund-tickers-ajax.php
	JAVASCRIPT	- includes/scripts/portlets/tickers.js.php
	HTML		- includes/portlets/tickers.php

*/

session_start();

// Load debug & error logging functions
require_once("../../../includes/system-debug-functions.php");
require("../../../../secure/dbconnect.php");
require("../../../includes/system-functions.php");

?>

<!-- Start the table, build the header -->
<div class="portlet-body">
    <table class="table table-striped table-bordered table-hover table-full-width" id="member_fund_ticker_table" style="margin:0;">
        <thead>
            <tr>
            	<th style="text-align:center;">Fund</th>
                <th class="hidden-xs" style="text-align:center;">Stock</th>
                <th class="hidden-xs" style="text-align:center;">Cash</th>
                <th class="hidden-xs" style="text-align:center;">Total</th>
                <th class="visible-xs" style="text-align:center;">Value</th>
                <th style="text-align:center;">NAV <a href="#nav-help" data-toggle="modal" style="text-decoration:none;"><i class="icon-question-sign"></i></a></th>
                <th style="text-align:center;">Change  <a href="#change-help" data-toggle="modal" style="text-decoration:none;"><i class="icon-question-sign"></i></a></th>
            </tr>
        </thead>
	    <tbody>

<?php

// Get the member's fund info
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

/* Moved to heartbeat on the Process server
if (isMarketOpen(time(), $mLink, "after")){ // Do this only if the markets are open
	// Get a current livePrice for each fund
	// This ultimately shouldn't be here.  While it would work for this facility, it's dual purpose was to perform a livePrice for everyone who is logged in every few minutes, for other charts and tables as well.  Problem is, it will only execute here if the portlet that calls this script is in focus - livePrice needs to run regardless.  Need to add it to the heartbeat daemon instead, after which it is no longer needed here since that will run it every few minutes instead.
	while ($fund = $rsFunds->fetch(PDO::FETCH_ASSOC)){

		$query = "livePrice|".$_SESSION['username']."|".$fund['fund_id']."|".$fund['fund_symbol'];
	//echo $query."<br>";
	//die();
		// Set the port number for the API call
		$port = rand(52000, 52499);

		// Include the API call IF the markets are open
		include("../../../includes/data-query-legacy.php");

		// Wait a tick
		sleep(1);
	}
	// Give the livePrice request(s) a li'l more time to finish
	sleep(2);
}

// Now get the member's fund IDs again (PDO on MySQL won't allow the reuse of the first result set (FIX THIS ORACLE!))
$rsFunds->execute($aValues);
*/

// Step through them again and update the table
$nonCompliant = 0;
while ($fund = $rsFunds->fetch(PDO::FETCH_ASSOC)){
/*
	// Get previous day's closing price
	if (!isset($_SESSION['close_price_'.$fund['fund_id']])){ // Not already in a session var
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
		// Save it to a session var so we don't have to keep looking it up
		$_SESSION['close_price_'.$fund['fund_id']] = $closePrice['totalValue'];
	}
	$closePricePrevious = $_SESSION['close_price_'.$fund['fund_id']];
*/

	// Switch to checking the last closing price every time rather than using a session var as it may change if nightly processing finishes while the member is logged in.
	$query = "
		SELECT totalValue
		FROM ".$_SESSION['fund_pricing_table']."
		WHERE fund_id = :fund_id
		ORDER By unix_date DESC
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
	$closePricePrevious = $closePrice['totalValue'];

	// Get the livePrice data
	$query = "
		SELECT *
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

	// Determine compliance.  If any one of the values is greater than 0 (not false) then they are non-compliant
	$compliant = true;
	if ($livePrice['violatesCash35'] + $livePrice['violatesDiversification25'] + $livePrice['violatesDiversification10'] + $livePrice['isInMargin'] > 0){ // They should all be 0 (false)
		$compliant = false;
		$nonCompliant++;
	}
	$change = $livePrice['totalValue'] - $closePricePrevious;

	// Build pretty output strings
//	$fundName = $fund['fund_name'];
//	if (strlen($fundName) > 20){
//		$fundName = substr($fund['fund_name'], 0, 19)."&#8230;"; //Ellipsis
//	}
//	$sName =		(!$compliant ? "<strong style='color:#ed4e2a'>" : "").
//					$fund['fund_symbol'].
//					" (".$fundName.")".
//					(!$compliant ? "<sup> &ddagger;</sup></strong>" : "")
//	;
//	$sSmallName =	(!$compliant ? "<strong style='color:#ed4e2a'>" : "").
	$sName =		(!$compliant ? "<strong style='color:#ed4e2a;'>" : "").
					'<span style="text-decoration:underline;">'.$fund['fund_symbol'].'</span>'.
					(!$compliant ? "<sup> &ddagger;</sup></strong>" : "")
	;
	$sNAV =			"<strong style='color:".($livePrice['nav'] >= 10 ? '#3cc051' : '#ed4e2a')."';>".
					number_format(round($livePrice['nav'], 2), 2).
					" (".(round(((round($livePrice['nav'], 2) - 10)/10)*100, 2) >= 0 ? "&#9650;" : "&#9660;").
					number_format(abs(round(((round($livePrice['nav'], 2) - 10)/10)*100, 2)), 2)."%)</strong>"
	;
	$sSmallNAV =	"<strong style='color:".($livePrice['nav'] >= 10 ? '#3cc051' : '#ed4e2a')."';>".
					number_format(round($livePrice['nav'], 2), 2)."</strong>"
	;
	$sChange =		"<strong style='color:".($change >= 0 ? '#3cc051' : '#ed4e2a')."';>".
					number_format(round($change, 2), 2).
					" (".(round($change, 2) >= 0 && round(((round($livePrice['totalValue'], 2) - round($closePricePrevious, 2))/round($closePricePrevious, 2))*100, 2) >= 0 ? "&#9650;" : "&#9660;").
					number_format(abs(round(((round($livePrice['totalValue'], 2) - round($closePricePrevious, 2))/round($closePricePrevious, 2))*100, 2)), 2)."%)</strong>"
	;
	$sBubble =		"Name:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$fund['fund_name']."\n".
					"Type:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".($fund['short_fund'] ? "Short" : "Long")." Fund (".(!$compliant ? "NOT " : "")."Compliant)\n".
					"Inception:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".date("m-d-Y", $fund['unix_date']).
					($fund['description'] != "" ? "\nDescription:&nbsp;&nbsp;".$fund['description'] : "")
	; // Bubble data (fund type, compliance, inception, description)
?>
			<!-- Build the fund's table row -->
			<tr title="<?php echo $sBubble; ?>" onClick="location.href='?page=03-01-03-001&fund=<?php echo $fund['fund_id']; ?>';" style="cursor:pointer;">
<!--				<td class="hidden-xs"><?php echo $fund['fund_name']; ?> (<?php echo $fund['fund_symbol']; ?>)</td>-->
<!--				<td class="visible-lg"><?php echo $sName; ?></td>-->
<!--				<td class="visible-xs"><?php echo $sSmallName; ?></td>-->
<!--				<td class="hidden-lg"><?php echo $sSmallName; ?></td>-->
				<td><span style=""><?php echo $sName; ?></span></td>
				<td class="hidden-xs" style="text-align:right"><?php echo number_format(round($livePrice['stockValue'], 2), 2); ?></td>
				<td class="hidden-xs" style="text-align:right"><?php echo number_format(round($livePrice['cashValue'], 2), 2); ?></td>
				<td style="text-align:right"><?php echo number_format(round($livePrice['stockValue'], 2) + round($livePrice['cashValue'], 2), 2); ?></td>
				<td class="hidden-xs" style="text-align:right"><?php echo $sNAV; ?></td>
				<td class="visible-xs" style="text-align:right"><?php echo $sSmallNAV; ?></td>
				<td style="text-align:right"><?php echo $sChange; ?></td>
			</tr>
<?php
}
?>
		<!-- Finish the table -->
	    </tbody>
    </table>
<?php
// Add a footnote if any are non-compliant
if ($nonCompliant > 0){
?>
	<div style="padding:5px 0 0 0;font-size:10px;">
		<sup>&ddagger;</sup> Non-Compliant Fund
	</div>
<?php
}
?>
</div><!--portlet-body-->

<!-- That's all folks! -->