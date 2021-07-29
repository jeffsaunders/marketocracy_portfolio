<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

require_once("../../includes/system-functions.php");

$process = $_REQUEST['process'];

//set dates
$currentQuater 	= '20150331';
$oneYearPast	= '20140331';
$threeYearPast	= '20120331';
$fiveYearPast	= '20100331';
$tenYearPast	= '20050331';

$aDates = array($currentQuater,$oneYearPast,$threeYearPast,$fiveYearPast,$tenYearPast);

/*$year			= 2015;
$month			= '03';
$day			= '31';

$currentQuater 	= $year.$month.$day;
$oneYearPast	= ($year - 1).$month.$day;
$threeYearPast	= ($year - 3).$month.$day;
$fiveYearPast	= ($year - 5).$month.$day;
$tenYearPast	= ($year - 10).$month.$day;*/

//echo $oneYearPast;

set_time_limit(3600);

if($process == "get-field"){
	
	$dataType = $_REQUEST['data-type'];
	
	//echo $dataType;
	
	
	
	if(strpos($dataType, 'csv') !== false){
		
		?>
        <br />
        <form action="process/reprice-members-process.php?process=file-upload" method="post" class="upload-text-file">
            <div class="form-body">
                <div class="form-group">
                	<label class="control-label">Select Price From Date</label>
                    <select class="form-control" name="day">
                    	<?php
						$query = "
							SELECT *
							FROM system_lists_options
							WHERE list_id='6'
							ORDER BY sequence ASC
						";
						try{
							$rsList = $mLink->prepare($query);
							$aValues = array(
								':fundkey' 	=> "X'".$fundKey."'"
							);
							$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
							$rsList->execute($aValues);
						}
						catch(PDOException $error){
							// Log any error
							file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
						}
						while($list = $rsList->fetch(PDO::FETCH_ASSOC)){
							echo '<option value="'.$list['value'].'">'.$list['label'].'</option>';
						}
						?>
                    </select>/
                    <select class="form-control" name="month">
                    	<?php
						$query = "
							SELECT *
							FROM system_lists_options
							WHERE list_id='7'
							ORDER BY sequence ASC
						";
						try{
							$rsList = $mLink->prepare($query);
							$aValues = array(
								':fundkey' 	=> "X'".$fundKey."'"
							);
							$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
							$rsList->execute($aValues);
						}
						catch(PDOException $error){
							// Log any error
							file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
						}
						while($list = $rsList->fetch(PDO::FETCH_ASSOC)){
							echo '<option value="'.$list['value'].'">'.$list['label'].'</option>';
						}
						?>
                    </select>/
                    <select class="form-control" name="year">
                    	<?php
						$query = "
							SELECT *
							FROM system_lists_options
							WHERE list_id='8'
							ORDER BY sequence ASC
						";
						try{
							$rsList = $mLink->prepare($query);
							$aValues = array(
								':fundkey' 	=> "X'".$fundKey."'"
							);
							$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
							$rsList->execute($aValues);
						}
						catch(PDOException $error){
							// Log any error
							file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
						}
						while($list = $rsList->fetch(PDO::FETCH_ASSOC)){
							echo '<option value="'.$list['value'].'">'.$list['label'].'</option>';
						}
						?>
                    </select>
                    <small>DD/MM/YYYY
                </div>
                <div class="form-group">
                	<label class="control-label">Select API</label>
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
		
		?><br />
        <label>Input Values</label><br />
        <textarea name="values" col="10" row="10" /></textarea><br /><br />
        <input type="submit" name="submit" value="Submit" />
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
	$fromDate		= mktime(6,0,0,$month,$day,$year);
	$fromDateStr	= $year.$month.$day;
	$today			= time();
	$newDate		= 0;
	$aPriceDates 	= array();
	
	switch($apiServer){
		case 'api1': $port = rand(52000, 52099);break;
		case 'api2': $port = rand(52100, 52199);break;
                case 'api3': $port = rand(52500, 52699);break;
	}
	
	$key = true;
	
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
	} 
	
	
	
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
							foreach($aFundKey as $key=>$fundKey){
								
								$query = "
									SELECT mf.fund_id, mf.fund_symbol, m.username
									FROM members_fund as mf
									INNER JOIN members as m ON m.member_id=mf.member_id
									WHERE mf.fb_primarykey=:fundkey AND mf.active='1'
								";
								try{
									$rsFundID = $mLink->prepare($query);
									$aValues = array(
										':fundkey' 	=> "X'".$fundKey."'"
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
										'username'		=> $getFund['username']
									);
								}
							}
						}
						
						$aQueries = array();
						
						
						$fundCnt = 0;
						//Loop through and create data legacy queries
						foreach($aFunds as $fundID=>$aFundInfo){
							
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
							}
							
							$fundCnt++;
							
							$username 	= $aFundInfo['username'];
							$fundSymbol	= $aFundInfo['fundSymbol'];
							$aQueries[] = 'aggregateStatistics|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.date('Ymd');
							$aQueries[] = 'alphaBetaStatistics|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.date('Ymd');
							$aQueries[] = 'livePrice|'.$username.'|'.$fundID.'|'.$fundSymbol;
							
							$aQueries[] = 'tradesForFund|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$fromDateStr;
							$aQueries[] = 'allPositionInfo|'.$username.'|'.$fundID.'|'.$fundSymbol;
							
							foreach($aPriceDates as $key=>$aDate){
								
								$start 	= date('Ymd', $aDate['start']);
								$end	= date('Ymd', $aDate['end']);
								
								$aQueries[] = 'priceRun|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$start.'|'.$end;
									
							}
								
						}
						
						if($showQueries == '1'){
							echo 'Done!<br />'.$fundCnt.' Funds Queued<br />'.count($aQueries).' Queries Queued<br />';
							echo '<pre>';
							echo 'Port:'.$port.'<br />';
							print_r($aQueries);
							print_r($aPriceDates);
							print_r($aFundKeys);
							echo '</pre>';
						}else{
							
							$queryCnt = 0;
							
							switch($apiServer){
								case 'api1': $portStart = 52000;$portStop = 52099;break;
								case 'api2': $portStart = 52100;$portStop = 52199;break;
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
							}
							
							echo 'Done!<br />'.$fundCnt.' Funds Processed<br />'.$queryCnt.' Queries Processed';
							
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
