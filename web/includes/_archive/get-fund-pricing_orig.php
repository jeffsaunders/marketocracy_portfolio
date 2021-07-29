<?php
						// Build array of missing dates
						$queryDates = createDateRangeArray($startDate, $endDate);  // Function found in includes/system-functions.php
//print_r($queryDates);die();
						// Break it up into 10 day chunks
						$dateChunks = array_chunk($queryDates, 10);

						if (!isset($pause)){
							$pause = 1;
						}

						// Step through the chunks
						for ($cnt = 0; $cnt < count($dateChunks); $cnt++){

							// Grab the first and last date of the chunk
							$startDate = $dateChunks[$cnt][0];
							$endDate = $dateChunks[$cnt][count($dateChunks[$cnt])-1];

							// Build the query for the API
							$query = "priceRun|".$_SESSION['username']."|".$fund_id."|".$fund_symbol."|".$startDate."|".$endDate;
echo $query."\n";

							// Set the port number for the API call
							if ($port == 22499){ // Last port #, roll over
								$port = 22000;
							}else{
								$port++;
							}

							// Include the API call
							include("/var/www/html/".$_SESSION['base_url']."web/includes/data-query-legacy.php");
							// Wait a tick
							sleep($pause);
						}
?>