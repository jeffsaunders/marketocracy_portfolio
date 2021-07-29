<?php
// Build array of missing dates
$queryDates = createDateRangeArray($startDate, $endDate);  // Function found in includes/system-functions.php
//print_r($queryDates);die();

// Break it up into 15 day chunks
$dateChunks = array_chunk($queryDates, 15);

// Step through the chunks
for ($cnt = 0; $cnt < count($dateChunks); $cnt++){

	// Grab the first and last date of the chunk
	$startDate = $dateChunks[$cnt][0];
	$endDate = $dateChunks[$cnt][count($dateChunks[$cnt])-1];

	// Build the query for the API
	$query = "priceRun|0|".$_SESSION['username']."|".$fund_id."|".$fund_symbol."|".$startDate."|".$endDate;
echo $query."\n";

	// Set the port number for the API call
	if ($port == 52499){ // Last port #, roll over
		$port = 52100;
		sleep(1);
	}else{
		$port++;
	}

	// Include the API call
	include("/var/www/html/".$_SESSION['base_url']."web/includes/data-query-legacy-batch.php");
	if (isset($requests)){
		$requests++;
	}
	// Wait a tick
	sleep($pause);
}
?>

