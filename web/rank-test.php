<?php
/*
+----------------------------------------------------------------------------------------+
|						STORE RANKING NAVS FROM CSV ROUTINE
+----------------------------------------------------------------------------------------+
*/
// Parse passed arguments string to $_REQUEST array (i.e. "first=1&second=2&third=3" -> $_REQUEST['first'] = 1, etc.)
//parse_str($argv[1], $_REQUEST);



#get dependencies
require('/var/www/html/portfolio.marketocracy.com/web/includes/system-functions.php');
require('/var/www/html/portfolio.marketocracy.com/web/includes/system-debug-functions.php');
require('/var/www/html/portfolio.marketocracy.com/secure/dbconnect.php');

#start timer
$startTime 			= time();
echo 'Start Time: '.date('Y-m-d H:i:s', $startTime).'<br />';

#target file
$targetDir			= '/var/www/html/portfolio.marketocracy.com/web/files/rankings/';
$rankFile 			= $_REQUEST['rankFile'];
$targetFile			= $targetDir.$rankFile;

#db tables
$fundTable			= 'members_fund';
$rankNavTable		= 'rankings_navs_test';
$rankLog			= 'log_rank_navs';

#initialize
$aActiveFunds		= array();
$aFundsProcessed	= array();
$aFundsNotProcessed	= array();
$loopCnt			= 0;

#START | Rank Process Log
$query = "
	INSERT INTO ".$rankLog." (
		status,
		start_date,
		unix_start,
		file,
		timestamp
	)VALUES(
		'processing',
		:start_date,
		:unix_start,
		:file,
		UNIX_TIMESTAMP()
	)
";
try{
	$rsLog = $mLink->prepare($query);
	$aValues = array(
		':start_date'	=> date('Y-m-d h:i:s',$startTime),
		':unix_start'	=> $startTime,
		':file'			=> $targetFile
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsLog->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$logUid = $mLink->lastInsertId();
#END | Rank Process Log

#START | build array of active funds to quickly check against
$query = "
	SELECT fund_id, member_id, fb_primarykey
	FROM members_fund
	WHERE short_fund='0' AND active='1'
";
try{
	$rsActiveFunds = $mLink->prepare($query);
	$aValues = array();
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsActiveFunds->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}
while($funds = $rsActiveFunds->fetch(PDO::FETCH_ASSOC)){
	
	$aActiveFunds[strtoupper($funds['fb_primarykey'])] = array(
		'fund_id'		=> $funds['fund_id'],
		'member_id'		=> $funds['member_id']
	);	
		
}
#END | build array of active funds to quickly check against

#START | loop through csv file line by line
if(($handle = fopen($targetFile, 'r')) !== false)
{
    #get the first row, which contains the column-titles (if necessary)
    $header = fgetcsv($handle);

    #loop through the file line-by-line
    while(($data = fgetcsv($handle)) !== false)
    {
		#step the counter
        $loopCnt++;
		
		#set vars		
		$fundKey		= strtoupper($data[0]);
		$fbPrimaryKey	= "X'".$fundKey."'";
		$managerKey		= $data[1];
		$asOfDate		= $data[2];
		$currentNav		= $data[3];
		$nav1			= $data[4];
		$nav3			= $data[5];
		$nav5			= $data[6];
		$nav10			= $data[7];
		$nav15			= $data[8];
		$navME			= $data[9];
		$navQE			= $data[10];
		$navYE			= $data[11];
		
		#get date values
		$aDate			= explode('/', $asOfDate);
		$month			= $aDate[0];
		$day			= $aDate[1];
		$year			= $aDate[2];
		$unixDate		= mktime(8,0,0,$month,$day,$year);
		$asOfDate		= date('Ymd', $unixDate);
		
		#check to see if fund exists in active funds array
		if(array_key_exists($fbPrimaryKey, $aActiveFunds)){
			
			#fund is active, grab IDs
			$fundID 	= $aActiveFunds[$fbPrimaryKey]['fund_id'];
			$memberID 	= $aActiveFunds[$fbPrimaryKey]['member_id'];
			
			#add to processed array
			$aFundsProcessed[$fbPrimaryKey] = $fundID;
			
			#write record into database
			$query = "
				INSERT INTO ".$rankNavTable." (
					asOfDate,
					unix_period,
					fund_id,
					member_id,
					fb_primarykey,
					fund_key,
					manager_key,
					current_nav,
					nav_1,
					nav_3,
					nav_5,
					nav_10,
					nav_15,
					nav_me,
					nav_qe,
					nav_ye
				)VALUES(
					:asOfDate,
					:unix_period,
					:fund_id,
					:member_id,
					:fb_primarykey,
					:fund_key,
					:manager_key,
					:current_nav,
					:nav_1,
					:nav_3,
					:nav_5,
					:nav_10,
					:nav_15,
					:nav_me,
					:nav_qe,
					:nav_ye
				)
			";
			try{
				$rsInsert = $mLink->prepare($query);
				$aValues = array(
					':asOfDate'			=> $asOfDate,
					':unix_period'		=> $unixDate,
					':fund_id'			=> $fundID,
					':member_id'		=> $memberID,
					':fb_primarykey'	=> $fbPrimaryKey,
					':fund_key'			=> $fundKey,
					':manager_key'		=> $managerKey,
					':current_nav'		=> $currentNav,
					':nav_1'			=> $nav1,
					':nav_3'			=> $nav3,
					':nav_5'			=> $nav5,
					':nav_10'			=> $nav10,
					':nav_15'			=> $nav15,
					':nav_me'			=> $navME,
					':nav_qe'			=> $navQE,
					':nav_ye'			=> $navYE
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsInsert->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
		}else{
			#add to NOT processed array
			$aFundsNotProcessed[$fbPrimaryKey] = $fundID;
		}
		
		#update the log every 100 records
		if($loopCnt %50 != 0 || $loopCnt == 1){
			
			$query = "
				UPDATE ".$rankLog."
				SET funds_processed=:funds_processed, funds_not_processed=:funds_not_processed, timestamp=UNIX_TIMESTAMP()
				WHERE uid=:log_uid
			";
			try{
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':funds_processed'		=> count($aFundsProcessed),
					':funds_not_processed'	=> count($aFundsNotProcessed),
					':log_uid'				=> $logUid
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdate->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
				
		}
		
		/*echo '<pre>';
		
		print_r($data);
		
		echo '</pre>';*/
        // I don't know if this is really necessary, but it couldn't harm;
        // see also: http://php.net/manual/en/features.gc.php
        unset($data);
    }
	
    fclose($handle);
	
	$stopTime	= time();
	
	#update final log entry
	$query = "
		UPDATE ".$rankLog."
		SET funds_processed=:funds_processed, funds_not_processed=:funds_not_processed, status=:status, end_date=:end_date, unix_end=:unix_end, timestamp=UNIX_TIMESTAMP()
		WHERE uid=:log_uid
	";
	try{
		$rsUpdate = $mLink->prepare($query);
		$aValues = array(
			':status'				=> 'complete',
			':funds_processed'		=> count($aFundsProcessed),
			':funds_not_processed'	=> count($aFundsNotProcessed),
			':log_uid'				=> $logUid,
			':end_date'				=> date('Y-m-d h:i:s', $stopTime),
			':unix_end'				=> $stopTime
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsUpdate->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	
	echo 'Stop Time: '.date('Y-m-d H:i:s', $stopTime).'<br />';
	
	$duration = ($stopTime - $startTime);
	
	function sec2hms ($sec, $padHours = false) 
	{
	
	  // do the hours first: there are 3600 seconds in an hour, so if we divide
	  // the total number of seconds by 3600 and throw away the remainder, we're
	  // left with the number of hours in those seconds
	  $hours = intval(intval($sec) / 3600); 
	
	  // start our return string with the hours (with a leading 0 if asked for)
	  if ($padHours) {
		$hms = str_pad($hours, 2, "0", STR_PAD_LEFT). ":";
	  } else {
		$hms = $hours. ":";
	  }
	
	  // dividing the total seconds by 60 will give us the number of minutes
	  // in total, but we're interested in *minutes past the hour* and to get
	  // this, we have to divide by 60 again and then use the remainder
	  $minutes = intval(($sec / 60) % 60); 
	
	  // add minutes to $hms (with a leading 0 if needed)
	  $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";
	
	  // seconds past the minute are found by dividing the total number of seconds
	  // by 60 and using the remainder
	  $seconds = intval($sec % 60); 
	
	  // add seconds to $hms (with a leading 0 if needed)
	  $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);
	
	  // done!
	  return $hms;
	
	}
	
	$timeElapse = sec2hms($duration);
	
	echo 'Duration: '.$timeElapse;
	
	
	
	/*echo '<pre>';
		
		
	print_r($aActiveFunds);
	echo '</pre>';*/
}
#END | loop through csv file line by line