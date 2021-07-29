<?php
/*
Marketocracy Inc. | Beta Development
Admin Member Reprice Processess

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/admin-member-reprice-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/admin-member-reprice.js.php
	HTML		- includes/pages/admin-member-reprice.php
*/


// Stop repricing!
//echo "<strong>Fund Repricing is Disabled.</strong>";die();



//Start Session
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

// Load System Wide Functions
require_once("../../includes/system-functions.php");

//Get Process from URL
$process = $_REQUEST['process'];


set_time_limit(3600);

// Set memory limit to "unlimited"...hopefully that's enough to complete some of the really big ones. -JSS
ini_set('memory_limit', '-1');

if($process == "get-field"){
	
	$dataType = $_REQUEST['data-type'];
	
	//echo $dataType;
	
	
	
	if(strpos($dataType, 'csv') !== false){
		
		?>
        <br />
        <form action="process/ajax/admin-member-reprice-processes.php?process=file-upload" method="post" class="upload-text-file">
            <div class="form-body">
                <div class="form-group">
                	<label class="control-label">Select Price From Date</label><br />
                    
                    <select name="year">
                    	<?php
						echo date_list($mLink, 'year', NULL, date('Y'), false);
						?>
                    </select>/
					<select  name="month">
                    	<?php
						echo date_list($mLink, 'month', NULL, date('m'), true);
						?>
                    </select>/
                    <select name="day">
                    	<?php
						echo date_list($mLink, 'day', NULL, date('d'), true);
						?>
                    </select><br />
                    <small>YYYY/MM/DD</small>
                    <br />
                    <label class="control-label"><input type="checkbox" name="from-incpet" value="1" /> Reprice from inception</label>
                </div>
                <div class="form-group">
                	<label class="control-label">Notes</label><br />
                    <textarea name="notes" class="form-control"></textarea>
                </div>
                <div class="form-group">
                	<label class="control-label">Select API</label><br />
                    <select name="api-server">
                    	<option value="api1">API1</option>
                        <option value="api2" selected>API2</option>
                        <option value="api3">API3</option>
                    </select>
                </div>
                <div class="form-group">                        <option value="api2">API2</option>
                	<label><input type="checkbox" value="1" name="process-queries" /> Show queries</label>
                </div>
                <div class="form-group">
                    <div id="upload-errors"></div>
                    <label class="control-label">Upload CSV or Comma Delimited Text File</label><br />
                    <input name="fileToUpload" id="textFileName" type="file" class="btn btn-default" style="float:left;"/><br /><br />
                    <?php /*?><input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id'];?>" /><?php */?>
                    <input type="hidden" name="data-type" value="<?php echo $dataType;?>" />
                    <input type="submit"  id="submit-btn" value="Upload File" class="btn btn-info" style="float:left;margin-left:10px;" /><div class="clearfix"></div>
                </div><!--form-group-->
            </div><!--form-body-->
        </form>
        
        <div id="load-files"></div>
        <?php
		
	}elseif(strpos($dataType, 'field') !== false){
		
		?>
        
        <form action="process/ajax/admin-member-reprice-processes.php?process=field-submit" method="post" class="upload-text-file">
            <div class="form-body">
                <div class="form-group">
                	<label class="control-label">Select Price From Date</label><br />
                    
                    
                    <select name="year">
                    	<?php
						echo date_list($mLink, 'year', NULL, date('Y'), false);
						?>
                    </select>/
                    <select  name="month">
                    	<?php
						echo date_list($mLink, 'month', NULL, date('m'), true);
						?>
                    </select>/
                    <select name="day">
                    	<?php
						echo date_list($mLink, 'day', NULL, date('d'), true);
						?>
                    </select>/<br />
                    <small>YYYY/MM/DD</small>
                    <br />
                    <label class="control-label"><input type="checkbox" name="from-incpet" value="1" /> Reprice from inception</label>
                </div>
                <div class="form-group">
                	<label class="control-label">Notes</label><br />
                    <textarea name="notes" class="form-control"></textarea>
                </div>
                <div class="form-group">
                	<label class="control-label">Select API</label><br />
                    <select name="api-server">
                    	<option value="api1">API1</option>
                        <option value="api2" selected>API2</option>
                        <option value="api3">API3</option>
                    </select>
                </div>
                <div class="form-group">
                	<label><input type="checkbox" value="1" name="process-queries" /> Show queries</label>
                </div>
                <div class="form-group">
                    <label class="control-label">Input Values</label><br />
                    <textarea name="values" class="form-control" /></textarea><br /><br />
                </div>
                <input type="hidden" name="data-type" value="<?php echo $dataType;?>" />
                <div class="form-group">
                    <input type="submit" name="submit" value="Submit" class="btn btn-info"/>
                </div>
            </div>
        </form>
        <div id="load-files"></div>
        <?php
	}
	
	
	
}




if($process == 'file-upload'){
	
	$dataType		= $_REQUEST['data-type'];
	$target_dir 	= "../../files/csv_uploads/";
	
	$showQueries	= $_REQUEST['process-queries'];
	$apiServer		= $_REQUEST['api-server'];
	$day			= $_REQUEST['day'];
	$month			= $_REQUEST['month'];
	$year			= $_REQUEST['year'];
	$repriceIncept	= $_REQUEST['from-incpet'];
	
	if($repriceIncept == '1'){
		$day 	= '01';
		$month	= '01';
		$year	= '2000';
	}
	
	
	$notes			= $_REQUEST['notes'];
	$fromDate		= mktime(4,0,0,$month,$day,$year);

	// Reset minimum date to 3/27/18 - JS
	if ($fromDate < 1522108800){
                $day    = '27';
                $month  = '03';
                $year   = '2018';
	        $fromDate = mktime(4,0,0,$month,$day,$year);

	}

	$fromDateStr	= $year.$month.$day;
	$today			= time();
	$newDate		= 0;
	$aPriceDates 	= array();
	
	
	
	switch($apiServer){
		case 'api1': $port = rand(52000, 52099);$apiType = '1';break;
		case 'api2': $port = rand(52100, 52199);$apiType = '2';break;
                case 'api3': $port = rand(52500, 52699);$apiType = '3';break;
	}
	
	/*$key = true;
	
	while($key){
		//echo "The number is: $x <br>";
		
		if($newDate < $today){
			
			//echo 'hello';
			
			if($newDate < $fromDate){
				$start = $fromDate;	
			}else{
				$start = strtotime('+1 days',$newDate);	
			}
			
			$end = strtotime('+15 days',$start);
			
			if($end > $today){
				$aPriceDates[] = array(
					'start'	=> $start,
					'end'	=> $today
				);	
			}else{
				$aPriceDates[] = array(
					'start'	=> $start,
					'end'	=> $end
				);	
			}
		}
		
		$newDate = $end;
		
		if($newDate > $today){
			//stop loop
			$key = false;	
		}
	} */
		
	$file_prefix	= date('Y-m-d--h-i').'_'.mktime().'_';
	$fileName 		= str_replace(' ', '-',$_FILES["fileToUpload"]["name"]);
	$target_file 	= $target_dir . basename($file_prefix.$fileName);
		
	$uploadOk 		= 1;
	
	if(isset($_POST) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
		
		
		// check $_FILES['fileToUpload'] not empty
		if(!isset($_FILES['fileToUpload']) || !is_uploaded_file($_FILES['fileToUpload']['tmp_name'])){
				die('File is Missing!'); // output error when above checks fail.
		}
		
		$path_parts = pathinfo($target_file);		
		
		if($path_parts['extension'] != 'csv'){
			echo 'File is not a text file.';
			$uploadOk = 0;	
		}
		
		// Check if file already exists
		if (file_exists($target_file)) {
			echo "Sorry, file already exists.";
			$uploadOk = 0;
		}
		
		// Check file size
		if ($_FILES["fileToUpload"]["size"] > 500000) {
			echo "Sorry, your file is too large.";
			$uploadOk = 0;
		}
				
		/*// Allow certain file formats
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
		&& $imageFileType != "gif" ) {
			echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
			$uploadOk = 0;
		}*/
		
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
			echo "Sorry, your file was not uploaded.";
		
		// if everything is ok, try to upload file
		} else {
			
			//echo 'hello';
			//echo $target_file;
			if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
				
				//echo $dataType;
				
				//echo "The file ". $fileName. " has been uploaded.";
				
				//echo 'hello';
				//+---------------------------------------------------------------------+
				//|						Start file processing
				//+---------------------------------------------------------------------+
				switch($dataType){
		
					//CSV MemberID
					case 'csv-member-id':
					
					break;
					
					//CSV FundKey
					case 'csv-fund-key':
					
						function readCSV($csvFile){
							$file_handle = fopen($csvFile, 'r');
							while (!feof($file_handle) ) {
								$line_of_text[] = fgetcsv($file_handle, 1024);
							}
							fclose($file_handle);
							return $line_of_text;
						}
						
						
						// Set path to CSV file
						$csvFile = $target_file;
						
						$csv = readCSV($csvFile);
						
						$aFundKeys = array();
						
						
						foreach($csv as $key=>$aFundKey){
							
							$aInactiveFunds = array();
							
							foreach($aFundKey as $key=>$fundKey){
								
								if(strpos($fundKey, "X'") !== false){
									$fundKey = $fundKey;
								}else{
									$fundKey = "X'".$fundKey."'";	
								}
								
								$query = "
									SELECT mf.fund_id, mf.fund_symbol, m.username, mf.unix_date as incept
									FROM members_fund as mf
									INNER JOIN members as m ON m.member_id=mf.member_id
									WHERE mf.fb_primarykey=:fundkey AND mf.active='1'
								";
								try{
									$rsFundID = $mLink->prepare($query);
									$aValues = array(
										':fundkey' 	=> $fundKey
									);
									$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
									$rsFundID->execute($aValues);
								}
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}
								$getFund = $rsFundID->fetch(PDO::FETCH_ASSOC);
								
								$fundID = $getFund['fund_id'];
								
								
								
								if($fundID != ''){
									$aFunds[$fundID] = array(
										'fundKey'		=> $fundKey,
										'fundSymbol'	=> $getFund['fund_symbol'],
										'username'		=> $getFund['username'],
										'inception'		=> $getFund['incept']
									);
								}else{
								
									$aInactiveFunds[] = $fundKey;
								
								}
							}
						}
						
						$aQueries = array();
						
						$aFundsLog = array();
						$fundCnt = 0;
						//Loop through and create data legacy queries
						foreach($aFunds as $fundID=>$aFundInfo){
							
							$aFundsLog[] = $fundID;
							
							if($showQueries != '1'){
								//Remove Price Data
								/*$query = "
									DELETE FROM members_fund_pricing
									WHERE fund_id=:fund_id AND unix_date>=:target_date
								";
								try{
									$rsDelete = $mLink->prepare($query);
									$aValues = array(
										':fund_id' 		=> $fundID,
										':target_date'	=> $fromDate
									);
									$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
									$rsDelete->execute($aValues);
								}
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}
								
								//Remove existing trade data
								$query = "
									DELETE FROM members_fund_trades
									WHERE fund_id=:fund_id AND unix_closed>=:target_date
								";
								try{
									$rsDelete = $mLink->prepare($query);
									$aValues = array(
										':fund_id' 		=> $fundID,
										':target_date'	=> $fromDate
									);
									$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
									$rsDelete->execute($aValues);
								}
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}*/
							}
							
							$fundCnt++;
							
							$username 	= $aFundInfo['username'];
							$fundSymbol	= $aFundInfo['fundSymbol'];
							/*$aQueries[] = 'aggregateStatistics|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.date('Ymd', strtotime('yesterday'));
							$aQueries[] = 'alphaBetaStatistics|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.date('Ymd', strtotime('yesterday'));
							$aQueries[] = 'livePrice|'.$username.'|'.$fundID.'|'.$fundSymbol;
							
							$aQueries[] = 'tradesForFund|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$fromDateStr;
							$aQueries[] = 'allPositionInfo|'.$username.'|'.$fundID.'|'.$fundSymbol;*/
							
							$aMethodVars[] = array(
								'method'		=> 'aggregateStatistics',
								'source'		=> 'Admin Reprice Script | admin-member-reprice-processes.php | process: file-upload | case: csv-fund-key',
								'api'			=> $apiType,
								'port'			=> '',
								'username'		=> $username,
								'fund_id'		=> $fundID,
								'fund_symbol'	=> $fundSymbol,
								'from_date'		=> date('Ymd', strtotime('yesterday')),
								'start_date'	=> '',
								'end_date'		=> '',
								'group'			=> date('Ymd', time()).'-reprice-fund'
							);
							
							$aMethodVars[] = array(
								'method'		=> 'alphaBetaStatistics',
								'source'		=> 'Admin Reprice Script | admin-member-reprice-processes.php | process: file-upload | case: csv-fund-key',
								'api'			=> $apiType,
								'port'			=> '',
								'username'		=> $username,
								'fund_id'		=> $fundID,
								'fund_symbol'	=> $fundSymbol,
								'from_date'		=> date('Ymd', strtotime('yesterday')),
								'start_date'	=> '',
								'end_date'		=> '',
								'group'			=> date('Ymd', time()).'-reprice-fund'
							);
							
							$aMethodVars[] = array(
								'method'		=> 'livePrice',
								'source'		=> 'Admin Reprice Script | admin-member-reprice-processes.php | process: file-upload | case: csv-fund-key',
								'api'			=> $apiType,
								'port'			=> '',
								'username'		=> $username,
								'fund_id'		=> $fundID,
								'fund_symbol'	=> $fundSymbol,
								'from_date'		=> '',
								'start_date'	=> '',
								'end_date'		=> '',
								'group'			=> date('Ymd', time()).'-reprice-fund'
							);
							
							$aMethodVars[] = array(
								'method'		=> 'allPositionInfo',
								'source'		=> 'Admin Reprice Script | admin-member-reprice-processes.php | process: file-upload | case: csv-fund-key',
								'api'			=> $apiType,
								'port'			=> '',
								'username'		=> $username,
								'fund_id'		=> $fundID,
								'fund_symbol'	=> $fundSymbol,
								'from_date'		=> '',
								'start_date'	=> '',
								'end_date'		=> '',
								'active_only'	=> '1',
								'group'			=> date('Ymd', time()).'-reprice-fund'
							);
							
							$aMethodVars[] = array(
								'method'		=> 'tradesForFund',
								'source'		=> 'Admin Reprice Script | admin-member-reprice-processes.php | process: file-upload | case: csv-fund-key',
								'api'			=> $apiType,
								'port'			=> '',
								'username'		=> $username,
								'fund_id'		=> $fundID,
								'fund_symbol'	=> $fundSymbol,
								'from_date'		=> $fromDateStr,
								'start_date'	=> '',
								'end_date'		=> '',
								'group'			=> date('Ymd', time()).'-reprice-fund'
							);
							
							$aPriceDates = priceRunDateArray($newDate, $today, $fromDate, $inception);
							
							foreach($aPriceDates as $key=>$aDate){
								
								$start 	= date('Ymd', $aDate['start']);
								$end	= date('Ymd', $aDate['end']);
								
								//$aQueries[] = 'priceRun|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$start.'|'.$end;
								
								$aMethodVars[] = array(
									'method'		=> 'priceRun',
									'source'		=> 'Admin Reprice Script | admin-member-reprice-processes.php | process: file-upload | case: csv-fund-key',
									'api'			=> $apiType,
									'port'			=> '',
									'username'		=> $username,
									'fund_id'		=> $fundID,
									'fund_symbol'	=> $fundSymbol,
									'from_date'		=> '',
									'start_date'	=> $start,
									'end_date'		=> $end,
									'group'			=> date('Ymd', time()).'-reprice-fund'
								);	
							}
							
							//delete position detail datea (data that shows in the ledger
							$query = "
								DELETE FROM members_fund_positions WHERE fund_id=:fund_id AND `date`>:reprice_date
							";
							try{
								$rsDeletePD = $mLink->prepare($query);
								$aValues = array(
									':fund_id' 			=> $fundID,
									':reprice_date'		=> $fromDateStr
								);
								$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
								$rsDeletePD->execute($aValues);
							}
							catch(PDOException $error){
								// Log any error
								file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
							}
								
						}
						
						
						
						//insert into log table
						$query = "
							INSERT INTO ".$_SESSION['reprice_history_table']."(
								fund_ids,
								reprice_date,
								notes,
								invalid_funds,
								timestamp
							)VALUES(
								:fund_ids,
								:reprice_date,
								:notes,
								:invalid_funds,
								UNIX_TIMESTAMP()
							)
						";
						try{
							$rsInsert = $mLink->prepare($query);
							$aValues = array(
								':fund_ids' 		=> implode('|', $aFundsLog),
								':reprice_date'		=> $fromDate,
								':notes'			=> $notes,
								':invalid_funds'	=> implode('|', $aInactiveFunds)
							);
							$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
							$rsInsert->execute($aValues);
						}
						catch(PDOException $error){
							// Log any error
							file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
						}
						
						if($showQueries == '1'){
							echo 'Done!<br />'.$fundCnt.' Funds Queued<br />'.count($aMethodVars).' Queries Queued<br />';
							echo '<pre>';
							echo 'Port:'.$port.'<br />';
							print_r($aMethodVars);
							print_r($aPriceDates);
							print_r($aFunds);
							echo '</pre>';
						}else{
							
							$queryCnt = 0;
							
							/*switch($apiServer){
								case 'api1': $portStart = 52000;$portStop = 52099;break;
								case 'api2': $portStart = 52100;$portStop = 52499;break;
                                                                case 'api3': $portStart = 52500;$portStop = 52699;break;
							}
							
							$port = $portStart;
							
							//Process All Queries
							foreach($aQueries as $key=>$query){
								
								if($port >= $portStop){
									$port = $portStart;	
								}
								
								$port++;
								
								$queryCnt++;
								include ('../../includes/data-query-legacy.php');
								usleep(500000);
							}*/
							$mlaResults = legacy_api($mLink, $aMethodVars, true);
							
							echo '
								Done!<br /><br />
								'.$fundCnt.' Funds Processed<br />
								'.count($aMethodVars).' Queries Processed<br />
								'.count($aInactiveFunds).' Inactive Funds<br />
								'.strtotime('yesterday').'|'.date('Ymd', strtotime('yesterday')).'
							';
							
						}
						
					
					break;
					
					//CSV Username
					case 'csv-username':
					
					break;					
				}
				
				
				
			} else {
				echo "Sorry, there was an error uploading your file.";
			}
		}
		
		
	}
}

if($process == 'field-submit'){
	
	$dataType		= $_REQUEST['data-type'];
	$aFundsValues	= explode(',',str_replace(' ','',$_REQUEST['values']));
	
	//print_r($aFundsValues);
	//echo $dataType;
		
	$showQueries	= $_REQUEST['process-queries'];
	$apiServer		= $_REQUEST['api-server'];
	$day			= $_REQUEST['day'];
	$month			= $_REQUEST['month'];
	$year			= $_REQUEST['year'];
	
	$repriceIncept	= $_REQUEST['from-incpet'];
	
	if($repriceIncept == '1'){
		$day 	= '01';
		$month	= '01';
		$year	= '2000';
	}
	
	$notes			= $_REQUEST['notes'];
	$fromDate		= mktime(4,0,0,$month,$day,$year);

        // Reset minimum date to 3/27/18 - JS
        if ($fromDate < 1522108800){
                $day    = '27';
                $month  = '03';
                $year   = '2018';
                $fromDate = mktime(4,0,0,$month,$day,$year);

        }

	$fromDateStr	= $year.$month.$day;
	$today			= time();
	$newDate		= 0;
	$aPriceDates 	= array();
	
	switch($apiServer){
		case 'api1': $port = rand(52000, 52099);$apiType = '1';break;
		case 'api2': $port = rand(52100, 52499);$apiType = '2';break;
                case 'api3': $port = rand(52500, 52699);$apiType = '3';break;
	}
	
	/*$key = true;
	
	while($key){
		//echo "The number is: $x <br>";
		
		if($newDate < $today){
			
			//echo 'hello';
			
			if($newDate < $fromDate){
				$start = $fromDate;	
			}else{
				$start = strtotime('+1 days',$newDate);	
			}
			
			$end = strtotime('+15 days',$start);
			
			if($end > $today){
				$aPriceDates[] = array(
					'start'	=> $start,
					'end'	=> $today
				);	
			}else{
				$aPriceDates[] = array(
					'start'	=> $start,
					'end'	=> $end
				);	
			}
		}
		
		$newDate = $end;
		
		if($newDate > $today){
			//stop loop
			$key = false;	
		}		
	} */
	
	switch($dataType){
	
		case 'field-fund-key':
			
			$aInactiveFunds = array();
			
			foreach($aFundsValues as $key=>$fundKey){
				
				if(strpos($fundKey, "X'") !== false){
					$fundKey = $fundKey;
				}else{
					$fundKey = "X'".$fundKey."'";	
				}
										
				$query = "
					SELECT mf.fund_id, mf.fund_symbol, m.username, mf.fb_primarykey, mf.unix_date as incept
					FROM members_fund as mf
					INNER JOIN members as m ON m.member_id=mf.member_id
					WHERE mf.fb_primarykey=:fundkey AND mf.active='1'
				";
				try{
					$rsFundID = $mLink->prepare($query);
					$aValues = array(
						':fundkey' 	=> $fundKey
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsFundID->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				$getFund = $rsFundID->fetch(PDO::FETCH_ASSOC);
				
				$fundID = $getFund['fund_id'];
				
				
				if($fundID != ''){
					$aFunds[$fundID] = array(
						'fundKey'		=> $getFund['fb_primarykey'],
						'fundSymbol'	=> $getFund['fund_symbol'],
						'username'		=> $getFund['username'],
						'inception'		=> $getFund['incept']
					);
				}else{
					$aInactiveFunds[] = $fundKey;	
				}
			}
			
		break;
		case 'field-fund-id':
			
			$aInactiveFunds = array();
			
			foreach($aFundsValues as $key=>$fundID){
										
				$query = "
					SELECT mf.fund_id, mf.fund_symbol, m.username, mf.unix_date as incept
					FROM members_fund as mf
					INNER JOIN members as m ON m.member_id=mf.member_id
					WHERE mf.fund_id=:fund_id AND mf.active='1'
				";
				try{
					$rsFundID = $mLink->prepare($query);
					$aValues = array(
						':fund_id' 	=> $fundID
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsFundID->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				$getFund = $rsFundID->fetch(PDO::FETCH_ASSOC);
				
				$fundID = $getFund['fund_id'];
				
				
				if($fundID != ''){
					$aFunds[$fundID] = array(
						'fundKey'		=> $fundKey,
						'fundSymbol'	=> $getFund['fund_symbol'],
						'username'		=> $getFund['username'],
						'inception'		=> $getFund['incept']
					);
				}else{
					$aInactiveFunds[] = $fundID;	
				}
			}
			
		break;
	
	}
	
	$aQueries 	= array();
						
	$aFundsLog 	= array();				
	$fundCnt 	= 0;
	//Loop through and create data legacy queries
	foreach($aFunds as $fundID=>$aFundInfo){
		
		$aFundsLog[] = $fundID;
		
		if($showQueries != '1'){
			//Remove Price Data
			$query = "
				DELETE FROM members_fund_pricing
				WHERE fund_id=:fund_id AND unix_date>=:target_date
			";
			try{
				$rsDelete = $mLink->prepare($query);
				$aValues = array(
					':fund_id' 		=> $fundID,
					':target_date'	=> $fromDate
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsDelete->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			//Remove existing trade data
			$query = "
				DELETE FROM members_fund_trades
				WHERE fund_id=:fund_id AND unix_closed>=:target_date
			";
			try{
				$rsDelete = $mLink->prepare($query);
				$aValues = array(
					':fund_id' 		=> $fundID,
					':target_date'	=> $fromDate
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsDelete->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			//delete position detail datea (data that shows in the ledger
			$query = "
				DELETE FROM members_fund_positions WHERE fund_id=:fund_id AND `date`>:reprice_date
			";
			try{
				$rsDeletePD = $mLink->prepare($query);
				$aValues = array(
					':fund_id' 			=> $fundID,
					':reprice_date'		=> $fromDateStr
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsDeletePD->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
		}
		
		$fundCnt++;
		
		$username 	= $aFundInfo['username'];
		$fundSymbol	= $aFundInfo['fundSymbol'];
		/*$aQueries[] = 'aggregateStatistics|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.date('Ymd', strtotime('yesterday'));
		$aQueries[] = 'alphaBetaStatistics|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.date('Ymd', strtotime('yesterday'));
		$aQueries[] = 'livePrice|'.$username.'|'.$fundID.'|'.$fundSymbol;
		
		$aQueries[] = 'tradesForFund|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$fromDateStr;
		$aQueries[] = 'allPositionInfo|'.$username.'|'.$fundID.'|'.$fundSymbol;*/
		
		$aMethodVars[] = array(
			'method'		=> 'aggregateStatistics',
			'source'		=> 'Admin Reprice Script | admin-member-reprice-processes.php | process: field-submit',
			'api'			=> $apiType,
			'port'			=> '',
			'username'		=> $username,
			'fund_id'		=> $fundID,
			'fund_symbol'	=> $fundSymbol,
			'from_date'		=> date('Ymd', strtotime('yesterday')),
			'start_date'	=> '',
			'end_date'		=> '',
			'group'			=> date('Ymd', time()).'-reprice-fund'
		);
		
		$aMethodVars[] = array(
			'method'		=> 'alphaBetaStatistics',
			'source'		=> 'Admin Reprice Script | admin-member-reprice-processes.php | process: field-submit',
			'api'			=> $apiType,
			'port'			=> '',
			'username'		=> $username,
			'fund_id'		=> $fundID,
			'fund_symbol'	=> $fundSymbol,
			'from_date'		=> date('Ymd', strtotime('yesterday')),
			'start_date'	=> '',
			'end_date'		=> '',
			'group'			=> date('Ymd', time()).'-reprice-fund'
		);
		
		$aMethodVars[] = array(
			'method'		=> 'livePrice',
			'source'		=> 'Admin Reprice Script | admin-member-reprice-processes.php | process: field-submit',
			'api'			=> $apiType,
			'port'			=> '',
			'username'		=> $username,
			'fund_id'		=> $fundID,
			'fund_symbol'	=> $fundSymbol,
			'from_date'		=> '',
			'start_date'	=> '',
			'end_date'		=> '',
			'group'			=> date('Ymd', time()).'-reprice-fund'
		);
		
		$aMethodVars[] = array(
			'method'		=> 'allPositionInfo',
			'source'		=> 'Admin Reprice Script | admin-member-reprice-processes.php | process: field-submit',
			'api'			=> $apiType,
			'port'			=> '',
			'username'		=> $username,
			'fund_id'		=> $fundID,
			'fund_symbol'	=> $fundSymbol,
			'from_date'		=> '',
			'start_date'	=> '',
			'end_date'		=> '',
			'active_only'	=> '1',
			'group'			=> date('Ymd', time()).'-reprice-fund'
		);
		
		$aMethodVars[] = array(
			'method'		=> 'tradesForFund',
			'source'		=> 'Admin Reprice Script | admin-member-reprice-processes.php | process: field-submit',
			'api'			=> $apiType,
			'port'			=> '',
			'username'		=> $username,
			'fund_id'		=> $fundID,
			'fund_symbol'	=> $fundSymbol,
			'from_date'		=> $fromDateStr,
			'start_date'	=> '',
			'end_date'		=> '',
			'group'			=> date('Ymd', time()).'-reprice-fund'
		);
		
		$aPriceDates = priceRunDateArray($newDate, $today, $fromDate, $aFundInfo['inception']);
		
		foreach($aPriceDates as $key=>$aDate){
			
			$start 	= date('Ymd', $aDate['start']);
			$end	= date('Ymd', $aDate['end']);
			
			$aMethodVars[] = array(
				'method'		=> 'priceRun',
				'source'		=> 'Admin Reprice Script | admin-member-reprice-processes.php | process: file-upload | case: csv-fund-key',
				'api'			=> $apiType,
				'port'			=> '',
				'username'		=> $username,
				'fund_id'		=> $fundID,
				'fund_symbol'	=> $fundSymbol,
				'from_date'		=> '',
				'start_date'	=> $start,
				'end_date'		=> $end,
				'group'			=> date('Ymd', time()).'-reprice-fund'
			);	
			//$aQueries[] = 'priceRun|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$start.'|'.$end;
				
		}
			
	}
	
	
	
	if($showQueries == '1'){
		echo 'Done!<br />'.$fundCnt.' Funds Queued<br />'.count($aMethodVars).' Queries Queued<br />';
		echo '<pre>';
		echo 'Port:'.$port.'<br />';
		print_r($aMethodVars);
		print_r($aPriceDates);
		print_r($aFundsValues);
		echo '</pre>';
	}else{
		
		$queryCnt = 0;
		
		/*switch($apiServer){
			case 'api1': $portStart = 52000;$portStop = 52099;break;
			case 'api2': $portStart = 52100;$portStop = 52499;break;
                        case 'api3': $portStart = 52500;$portStop = 52699;break;
		}
		
		$port = $portStart;
		
		//Process All Queries
		foreach($aQueries as $key=>$query){
			
			if($port >= $portStop){
				$port = $portStart;	
			}
			
			$port++;
			
			$queryCnt++;
			include ('../../includes/data-query-legacy.php');
			usleep(500000);
		}*/
		//Process queries
		$mlaResults = legacy_api($mLink, $aMethodVars, true);
		
		//insert into log table
		$query = "
			INSERT INTO ".$_SESSION['reprice_history_table']."(
				fund_ids,
				reprice_date,
				notes,
				invalid_funds,
				timestamp
			)VALUES(
				:fund_ids,
				:reprice_date,
				:notes,
				:invalid_funds,
				UNIX_TIMESTAMP()
			)
		";
		try{
			$rsInsert = $mLink->prepare($query);
			$aValues = array(
				':fund_ids' 		=> implode('|', $aFundsLog),
				':reprice_date'		=> $fromDate,
				':notes'			=> $notes,
				':invalid_funds'	=> implode('|', $aInactiveFunds)
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsInsert->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		echo $preparedQuery;
		echo $error;
		
		//echo 'Done!<br />'.$fundCnt.' Funds Processed<br />'.$queryCnt.' Queries Processed';
		echo '
			Done!<br /><br />
			'.$fundCnt.' Funds Processed<br />
			'.count($aMethodVars).' Queries Processed<br />
			'.count($aInactiveFunds).' Inactive Funds<br />
			'.strtotime('yesterday').'|'.date('Ymd', strtotime('yesterday')).'
		';
	}
	
}

if($process == 'load-reprice-history'){
	
	$today = time();
					
	$weekAgo = strtotime('-7 day', $today);
	
	$dateRange 		= $_REQUEST['dateRange'];
	
	//echo $dateRanage;
	
	if($dateRange == '1'){
		
		$startMonth		= $_REQUEST['start_month'];
		$startDay		= $_REQUEST['start_day'];
		$startYear		= $_REQUEST['start_year'];
		$endMonth		= $_REQUEST['end_month'];
		$endDay			= $_REQUEST['end_day'];
		$endYear		= $_REQUEST['end_year'];
		
		$startMonthVal	= $startMonth;
		$startDayVal	= $startDay;
		$startYearVal	= $startYear;
		$endMonthVal	= $endMonth;
		$endDayVal		= $endDay;
		$endYearVal		= $endYear;
		
		//echo $startMonth;echo 'hello';
		
		$startDate		= mktime(5,0,0,$startMonth,$startDay,$startYear);
		$endDate		= mktime(5,0,0,$endMonth,$endDay,$endYear);
		
		$query = "
			SELECT *
			FROM ".$_SESSION['reprice_history_table']."
			WHERE timestamp>:startDate AND timestamp<:endDate
			ORDER BY timestamp DESC
		";
		try{
			$rsGetHistory = $mLink->prepare($query);
			$aValues = array(
				':startDate'		=> $startDate,
				':endDate'			=> $endDate
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetHistory->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
				
	}else{
		
		$startMonthVal	= date('m',$weekAgo);
		$startDayVal	= date('d',$weekAgo);
		$startYearVal	= date('Y',$weekAgo);
		$endMonthVal	= date('m',$today);
		$endDayVal		= date('d',$today);
		$endYearVal		= date('Y',$today);
		
		$query = "
			SELECT *
			FROM ".$_SESSION['reprice_history_table']."
			WHERE timestamp>:week_ago
			ORDER BY timestamp DESC
		";
		try{
			$rsGetHistory = $mLink->prepare($query);
			$aValues = array(
				':week_ago'		=> $weekAgo
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetHistory->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
			
	}
	
	
	
	
	?>
	<div class="portlet">
		<div class="portlet-title">
			<div class="caption"><i class="icon-reorder"></i>Reprice History</div>
			<div class="tools">
				<a href="javascript:;" class="collapse"></a>
				<!--<a href="javascript:;" class="reload"></a>-->
			</div><!--tools-->
		</div><!--portlet-title-->
		<div class="portlet-body form">
        	<div id="reload-reprice-history"></div>
			<div style="margin:20px;">
            <form method="post" action="process/ajax/admin-member-reprice-processes.php" name="dateRangeForm" class="dateRangeForm">
                            	
                <select name="start_month" id="start_month" class="custom-select">
                    <option value="<?php echo $startMonthVal;?>"><?php echo $startMonthVal; ?></option>
                    <option disabled>--</option>
                    <?php
                    echo date_list($mLink, 'month', $startMonth);
                    ?>
                </select>
                /
                <select name="start_day" id="start_day" class="custom-select">
                    <option value="<?php echo $startDayVal;?>"><?php echo $startDayVal; ?></option>
                    <option disabled>--</option>
                    <?php
                    echo date_list($mLink, 'day', $startDay);
                    ?>
                </select>
                /
                <select name="start_year" id="start_year" class="custom-select">
                    <option value="<?php echo $startYearVal;?>"><?php echo $startYearVal; ?></option>
                    <option disabled>----</option>
                    <?php
                    echo date_list($mLink, 'year', $startYear);
                    ?>
                </select>
                &nbsp;-&nbsp;
                <select name="end_month"  id="end_month" class="custom-select">
                    <option value="<?php echo $endMonthVal;?>"><?php echo $endMonthVal; ?></option>
                    <option disabled>--</option>
                    <?php
                    echo date_list($mLink, 'month', $endMonth);
                    ?>
                </select>
                /
                <select name="end_day"  id="end_day" class="custom-select">
                    <option value="<?php echo $endDayVal;?>"><?php echo $endDayVal; ?></option>
                    <option disabled>--</option>
                    <?php
                    echo date_list($mLink, 'day', $endDay);
                    ?>
                </select>
                /
                <select name="end_year" id="end_year" class="custom-select">
                    <option value="<?php echo $endYearVal;?>"><?php echo $endYearVal; ?></option>
                    <option disabled>----</option>
                    <?php
                    echo date_list($mLink, 'year', $endYear);
                    ?>
                </select>
                <input type="hidden" name="dateRange"  value='1' id="dateRange" />
                <input type="submit" value="Change Date Range" name="submit" class="btn btn-xs btn-info" style="margin-top:-4px;"/>
            </form>
            </div>
            	
			<table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>UID</th>
                        <th>Date</th>
                        <th>Funds</th>
                        <th>Repriced From</th>
                        <th>Notes</th>
                        <th>Actions</th>
                        
                    </tr>
                </thead>
                <tbody>
                    
                	<?php
					
					
					//echo '<tr><td colspan="6">'.$today.'</td></tr>';
					
					while($history = $rsGetHistory->fetch(PDO::FETCH_ASSOC)){
						
						$uid			= $history['uid'];
						$aFundIDs		= explode('|', $history['fund_ids']);
						$notes			= $history['notes'];
						$date			= date('m/d/Y h:i a', $history['timestamp']);
						$repriceDate	= date('m/d/Y',$history['reprice_date']);
						$stratRebuild	= $history['strat_rebuilt'];
						
						if($stratRebuild != '0'){
							$showRebuild = 'Strat Processed: '.date('m/d/Y h:i a',$stratRebuild);
						}else{
							$showRebuild = '';
						}	
						
						$aListFunds	= array();
						
						$cnt = 0;
						foreach($aFundIDs as $key=>$fundID){
							
							$fundSymbol = get_funds($mLink, $fundID, 'fundSymbol');
							$aFundID	= explode('-', $fundID);
							$memberID	= $aFundID[0];
							
							$cnt++;
							
							if($cnt <= 200){
								$aListFunds[] = '<a href="process/ajax/admin-switch-user.php?member='.$memberID.'&admin='.$_SESSION['member_id'].'&toggle=admin-to-user&gopage=03-01-03-001&gofund='.$fundID.'" onclick="switchUser(\''.$memberID.'\',\''.$_SESSION['member_id'].'\');">'.$fundSymbol.'</a>';	
							}
						}
						
						$listFunds = implode(', ',$aListFunds);
						
						$listFundIDs = implode('|', $aFundIDs);
						?>
                        <tr>
                        	<td><?php echo $uid;?></td>
                            <td><?php echo $date;?></td>
                            <td><?php echo $listFunds;?></td>
                            <td><?php echo $repriceDate;?></td>
                            <td><?php echo $notes;?></td>
                            <td>
                            	<span id="process-btn-<?php echo $uid;?>"><a href="javascript:void(0):" class="btn btn-info btn-sm" style="margin-bottom:5px;" onClick="runStrat('<?php echo $uid;?>','<?php echo $listFundIDs; ?>');">Run Stratification Rebuild</a></span>
                                <?php echo $showRebuild;?>
                            	<?php /*?><a href="javascript:void(0);" class="btn btn-default btn-sm">Re-Run Reprice</a><?php */?>
                            </td>
                        </tr>
                        <?php
							
					}
					?>
                        
                </tbody>
            </table>	
			
			
			
		</div><!--END PORTLET BODY-->
	</div><!--END PORTLET-->
	<?php	
	
}
?>
