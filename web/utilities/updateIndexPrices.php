<?php
/*
The purpose of this facility is to provide a mechanism for updating index closing prices as they are no longer available via AlphaVantage or YQL.
*Note - this must be run in a web browser.
Written by: Jeff Saunders 7/7/20
Modified by: Jeff Saunders - 7/10/20 - Added listing of missing S&P 500 TR closing price records
*/

// Define some system settings
date_default_timezone_set('America/New_York');

// Tell me when things go sideways
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Connect to MySQL
require("/var/www/html/portfolio.marketocracy.com/secure/dbconnect.php");

// Load debug functions
//require("/var/www/html/portfolio.marketocracy.com/web/includes/systemDebugFunctions.php");

// Load some useful functions
//require("/var/www/html/portfolio.marketocracy.com/web/includes/system-functions.php");

// Instead of all that bloat, just put the one needed function directly in the code
// Determine if passed date falls on a market holiday
function isMarketHoliday($timestamp, $mLink) {


        // See if it's a holiday
        $nRows = $mLink->query("SELECT count(*) FROM system_holidays WHERE date = '".date("Y-m-d", $timestamp)."'")->fetchColumn();

        if ($nRows < 1){
                return false;  // It's not, bail!
        }

        // It is a holiday, see when the market closes
        $query = "
                SELECT *
                FROM system_holidays
                WHERE date = :date
        ";
        try {
                $rsHoliday = $mLink->prepare($query);
                $aValues = array(
                        ':date'         => date('Y-m-d', $timestamp)
                );
                $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                //return $preparedQuery;
                $rsHoliday->execute($aValues);
        }
        catch(PDOException $error){
                // Log any error
                file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
        }

        $holiday = $rsHoliday->fetch(PDO::FETCH_ASSOC);
        return $holiday['closed']; // "Y" if it is a holiday, "E" if it closes early

}

// See what dates are missing - just for SP500TR, for now
// Get the dates of last years' worth of SP500TR records
$query = "
	SELECT date
	FROM stock_index_history
	WHERE `index` = '^SP500TR'
	ORDER BY date DESC
	LIMIT 365
";
//echo $query;
try{
	$rsDates = $mLink->prepare($query);
	$rsDates->execute();
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

// Create and array to hold the dates we do have
$aDates = array();

// Loop through the results
while ($dates = $rsDates->fetch(PDO::FETCH_ASSOC)){

	// Add date to array
	array_push($aDates, strtotime($dates["date"]) + 43200);  // Add 43200 to make timestamp NOON on that date instead of midnight

}

//echo "<pre>";
//print_r($aDates);
//echo "</pre>";

// Now step through the last years' dates and see if the market was open on that date

// Set seed time to noon today
$seed = strtotime(date('Ymd')) + 43200;

// Create and array to hold the dates we are missing
$aMissing = array();

// Loop 365 times
for ($d = 1; $d <= 365; $d++){

	// Build step string to pass to the strtotiime function ("-x days")
	$step = "-".$d." days";

	// Check if the date is on a weekend or falls on a market holiday - bail if it does
	if (date('w', strtotime($step, $seed)) == 6 || date('w', strtotime($step, $seed)) == 0) {
//		echo "Weekend!<br>";
		continue;
        }elseif (isMarketHoliday(strtotime($step, $seed), $mLink)){
//		echo "Holiday!<br>";
		continue;
	}

//	echo "Market Day!<br>";

	// search $aDates for the date to see if we already have that day's SP500TR closing value
	$found = in_array(strtotime($step, $seed), $aDates);
	if (!$found){
//		echo "Missing!<br>";
	        // Add date to array of missing dates
		array_push($aMissing, strtotime($step, $seed));
	} 

}

//print_r($aMissing);

// Start the page & build the form
echo '
<!DOCTYPE HTML>

<html>
<head>
        <title>Index Price Update Tool</title>
</head>

<body>

<h1>Index Price Update Tool</h1>

<h3>The purpose of this facility is to provide a mechanism for updating index closing prices as they are no longer available via AlphaVantage or Yahoo YQL.</h3>
<h3>Any closing price left blank will simply not be saved - as opposed to being saved as 0.00 - so you only have to enter the closing price(s) required.</h3>

';
if (count($aMissing) > 0){
	echo '<h3> *NOTE - S&P 500 TR closing price missing for the following dates:</h3>';
	echo '<table border="0" cellspacing="0" cellpadding="0">';
        foreach($aMissing as $key=>$value){
		echo '<tr><td style="padding: 0px 0px 0px 50px;"><strong>';
		echo date('Y-m-d', $value).'<br>'; 
		echo '</td></tr>';
	}
	echo '</table><br>';
}

echo '
<form action="" name="indices" id="indicies" method="POST">
	<table border="0" cellspacing="0" cellpadding="0">
	<tr>
	        <td colspan="2"><h2>Enter Index Closing Prices:<hr width="100%" size="1" noshade></td>
	</tr>
        <tr>
                <td><strong>Date:</strong></td>
                <td><input type="text" name="date" id="date" value="'.date("Y-m-d").'" size="10" maxlength="10" style="width:100px;" autofocus="autofocus"></td>
        </tr>
        <tr>
                <td colspan="2">&nbsp;</td>
        </tr>
	<tr>
	        <td><strong>S&P 500 TR:</strong></td>
		<td><input type="text" name="SP500TR" id="SP500TR" value="" size="10" maxlength="10" style="width:100px;" oninput="this.value=this.value.replace(/(?![0-9]|\.)./gmi,\'\')"></td>
	</tr>
        <tr>
                <td><strong>S&P 500:</strong></td>
                <td><input type="text" name="SP500" id="SP500" value="" size="10" maxlength="10" style="width:100px;" oninput="this.value=this.value.replace(/(?![0-9]|\.)./gmi,\'\')"></td>
        </tr>
        <tr>
                <td><strong>Nasdaq:</strong></td>
                <td><input type="text" name="IXIC" id="IXIC" value="" size="10" maxlength="10" style="width:100px;" oninput="this.value=this.value.replace(/(?![0-9]|\.)./gmi,\'\')"></td>
        </tr>
        <tr>
                <td><strong>Dow Jones:</strong></td>
                <td><input type="text" name="DJIA" id="DJIA" value="" size="10" maxlength="10" style="width:100px;" oninput="this.value=this.value.replace(/(?![0-9]|\.)./gmi,\'\')"></td>
        </tr>
	
        <tr>
                <td><strong>NYSE Composite:</strong></td>
                <td><input type="text" name="NYA" id="NYA" value="" size="10" maxlength="10" style="width:100px;" oninput="this.value=this.value.replace(/(?![0-9]|\.)./gmi,\'\')"></td>
        </tr>
        <tr>
                <td><strong>Russell 2000:</strong></td>
                <td><input type="text" name="RUT" id="RUT" value="" size="10" maxlength="10" style="width:100px;" oninput="this.value=this.value.replace(/(?![0-9]|\.)./gmi,\'\')"></td>
        </tr>
        <tr>
                <td><strong>Russell 3000:</strong></td>
                <td><input type="text" name="RUA" id="RUA" value="" size="10" maxlength="10" style="width:100px;" oninput="this.value=this.value.replace(/(?![0-9]|\.)./gmi,\'\')"></td>
        </tr>
        <tr>
                <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
                <input type="hidden" name="source" value="Manual Form">
                <td colspan="2" align="center"><input type="submit" name="submit"></td>
        </tr>
	</table>
</form>

<br>
';

// Process submitted form with minimal error checking - assume user is expert
if ($_REQUEST && $_REQUEST["submit"]){

//print_r($_REQUEST);

	// Copy posted results for array manipulation
	$aPost = $_REQUEST;

	// Assign fixed values
	$date	= $aPost["date"];
	$source	= $aPost["source"];

	// Calculate unix_date
	$unix_date = strtotime($date);

	// Now get rid of everything except the indices
	unset($aPost["date"]);
        unset($aPost["source"]);
        unset($aPost["submit"]);

//print_r($aPost);
//echo $unix_date;

        foreach($aPost as $key=>$value){

		if ($value == ""){
			continue;
		}

		$index = "^".$key;
		$close = $value;

//echo $index." -> ".$value."\n";

	        // Insert index closing price record
	        $query = "
			INSERT INTO stock_index_history (
                                `index`,
                                date,
                                unix_date,
                                close,
                                timestamp,
                                source
                        ) VALUES (
                                :index,
                                :date,
                                :unix_date,
				:close,
                                UNIX_TIMESTAMP(),
                                :source
                        )
	                ON DUPLICATE KEY UPDATE
        	                `index` = :index,
	               	        date = :date,
	               	        unix_date = :unix_date,
	                        close = :close,
	                        timestamp = UNIX_TIMESTAMP(),
	                        source = :source
                ";
                try{
                        $rsInsert = $mLink->prepare($query);
                        $aValues = array(
                                ':index'	=> $index,
                                ':date'		=> $date,
                                ':unix_date'	=> $unix_date,
                                ':close'	=> $close,
                                ':source'	=> $source
                        );
                        //$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                        //echo $preparedQuery;//die();
                        $rsInsert->execute($aValues);
                }
                catch(PDOException $error){
                        // Log any error
                        file_put_contents($pdo_log, "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                        $aErrors[] = $error;
                }


	}
	echo "Closing Index Prices Saved for ".$date.".\n";

	// Javascript refresh to force update of the missing dates list...after a 3 second pause
	echo'
	<script type="text/javascript">
		function reloadAfterSleepingFor() {
//			location.reload(true);
			window.location.href = window.location.href;
		}
		setTimeout(reloadAfterSleepingFor, 3000);
	</script>
	';
}

?>
