<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

require_once("../../includes/system-functions.php");

$process = $_REQUEST['process'];

//set dates
$currentQuarter = '20150930';
$oneYearPast	= '20140930';
$threeYearPast	= '20120930';
$fiveYearPast	= '20100930';
$tenYearPast	= '20050930';

$aDates = array($currentQuarter,$oneYearPast,$threeYearPast,$fiveYearPast,$tenYearPast);



/*$year			= 2015;
$month			= '03';
$day			= '31';

$currentQuarter 	= $year.$month.$day;
$oneYearPast	= ($year - 1).$month.$day;
$threeYearPast	= ($year - 3).$month.$day;
$fiveYearPast	= ($year - 5).$month.$day;
$tenYearPast	= ($year - 10).$month.$day;*/

//echo $oneYearPast;

//print_r($aDates);

function get_quarter_dates($periodStart, $periodEnd){
	
	$loop = true;
	
	$aQuarters = array();
	
	$aQuarters[]	= $periodStart;
	
	while($loop){
		
		$currentYear = date('Y-m-d', $periodStart);
		
		if(!isset($currentQuarter)){
			$currentQuarter = $periodStart;	
		}
		
		$next90 	= strtotime('+90 days', $currentQuarter);
		$nextQuarter = mktime(6,0,0,date('m',$next90),date('t',$next90),date('Y', $next90));
		
		#add to array
		$aQuarters[] = $nextQuarter;		
		
		if($nextQuarter >= $periodEnd){
			$loop = false;	
		}
		
		$currentQuarter = $nextQuarter;
	
	}
	
	foreach($aQuarters as $key=>$quarter){
		$aQuarters[$key] = "'".date('Ymd', $quarter)."'";	
	}
	
	return $aQuarters;
		
}

set_time_limit(3600);

if($process == 'build-ranks'){
	
	$compliantPercentage = $_REQUEST['tolerance'];
	$period				= $_REQUEST['period'];
	$year 				= $_REQUEST['year'];
	$month				= $_REQUEST['month'];
	$day				= $_REQUEST['day'];
	$rankDate			= $year.$month.$day;
	$asOfDate			= $month.'/'.$day.'/'.$year;
	$currentQuarter		= mktime(6,0,0,$month,$day,$year);
	$oneYearPast		= strtotime(''.$year.'-'.$month.'-'.$day.' -1 years'); 
	$threeYearPast		= strtotime(''.$year.'-'.$month.'-'.$day.' -3 years'); 
	$fiveYearPast		= strtotime(''.$year.'-'.$month.'-'.$day.' -5 years'); 
	$tenYearPast		= strtotime(''.$year.'-'.$month.'-'.$day.' -10 years'); 
	$rankClass			= $_REQUEST['rank_class'];
	
	$aTenYearQuarters	= get_quarter_dates($tenYearPast, $currentQuarter);
	$aFiveYearQuarters	= get_quarter_dates($fiveYearPast, $currentQuarter);
	$aThreeYearQuarters	= get_quarter_dates($threeYearPast, $currentQuarter);
	$aOneYearQuarters	= get_quarter_dates($oneYearPast, $currentQuarter);
	
	//echo $currentQuarter;echo '<br />';
	//echo $rankClass;
	//exit();
	//print_r($aTenYearQuarters);
	//echo $currentQuarter;
	//echo $tenYearPast;
	
	switch($rankClass){
		
		case 'pro':
			
			$rankTable	= $_SESSION['pro_rankings_table'];
			
		break;
		
		case 'community':
		
			$rankTable 	= $_SESSION['community_rankings_table'];
			
		break;	
	}
	
	
	//echo $rankTable;
	//exit();
	//START | Calculate quarter dates
	#ten year quarter dates
	
	//Get list of invalid funds
	$query 	= "
		SELECT *
		FROM ".$_SESSION['invalid_funds_table']."
	";
	try{
		$rsInvalidFunds = $mLink->prepare($query);
		$aValues = array();
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsInvalidFunds->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aInvalidFunds = array();
	while($invalidFunds = $rsInvalidFunds->fetch(PDO::FETCH_ASSOC)){
		
		$aInvalidFunds[$invalidFunds['fund_id']] = $invalidFunds['fund_key'];
		
	}
	
	#Get benchmark values
	$query = "
		SELECT * 
		FROM ".$_SESSION['system_benchmarks_table']."
		WHERE period=:period
	";
	try{
		$rsBench = $mLink->prepare($query);
		$aValues = array('period'=>$rankDate);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsBench->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aBenchmarks = array();
	while($bench = $rsBench->fetch(PDO::FETCH_ASSOC)){
		
		$aType = explode('-',$bench['type']);
		
		$periodYear = $aType[0];
		$periodLevel = $aType[2];
		
		$aBenchmarks[$periodYear][$periodLevel] = $bench['value'];
	}
	
	/*echo '<pre>';
	print_r($aBenchmarks);
	echo '</pre>';*/
	
	//echo 'rankTable'; echo $currentQuarter;
	
	//Delete Rankings for existing period
	$query = "
		DELETE FROM ".$rankTable."
		WHERE period=:period AND date_end=:date_end
	";
	try{
		$rsDELETE = $mLink->prepare($query);
		$aValues = array(
			':date_end'		=> $currentQuarter,
			':period'		=> $period,
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsDELETE->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	echo $error;
	
	switch($period){
		case '10':
			$rankQuery 	= "
				SELECT *
				FROM ".$_SESSION['badge_results_table']."
				WHERE tenYearAAR<>0 AND asOfDate=:asOfDate
				ORDER BY tenYearAAR DESC
			";
			$period = 10;
			
			
		break;
		
		case '5':
			$rankQuery 	= "
				SELECT *
				FROM ".$_SESSION['badge_results_table']."
				WHERE fiveYearAAR<>0 AND asOfDate=:asOfDate
				ORDER BY fiveYearAAR DESC
			";
			$period = 5;
		break;
		
		case '3':
			$rankQuery 	= "
				SELECT *
				FROM ".$_SESSION['badge_results_table']."
				WHERE threeYearAAR<>0 AND asOfDate=:asOfDate
				ORDER BY threeYearAAR DESC
			";
			$period = 3;
		break;
		
		case '1':
			$rankQuery 	= "
				SELECT *
				FROM ".$_SESSION['badge_results_table']."
				WHERE oneYearAAR<>0 AND asOfDate=:asOfDate
				ORDER BY oneYearAAR DESC
			";
			$period = 1;
		break;	
	}
	try{
		$rsResults = $mLink->prepare($rankQuery);
		$aValues = array(
			':asOfDate'	=> $asOfDate
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsResults->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
		
	$loopCnt = 0;
	$rankCnt		= 0;

	while($results = $rsResults->fetch(PDO::FETCH_ASSOC)){
		
		$loopCnt++;
		
		$memberID 			= $results['member_id'];
		$fundID				= $results['fund_id'];
		$tenYearAAR			= $results['tenYearAAR'];
		$fiveYearAAR		= $results['fiveYearAAR'];
		$threeYearAAR		= $results['threeYearAAR'];
		$oneYearAAR			= $results['oneYearAAR'];
		
		$fundQualify		= false;
		
		switch($period){
			case '10': 
				$periodAAR 			= $tenYearAAR;
				$aPeriodQuarters	= $aTenYearQuarters;
			break;
			
			case '5':
				$periodAAR 			= $fiveYearAAR;
				$aPeriodQuarters	= $aFiveYearQuarters;
			break;
				
			case '3':
				$periodAAR 			= $threeYearAAR;
				$aPeriodQuarters	= $aThreeYearQuarters;
			break;	
			
			case '1':
				$periodAAR 			= $oneYearAAR;
				$aPeriodQuarters	= $aOneYearQuarters;
			break;
		}
		
		//Check to see if fund is disqualified
		if(!array_key_exists($fundID, $aInvalidFunds)){
			#Fund was not found in the invalid funds table
			
			$fundQualify = true;
		}#end fund qualify check	
		
		
		if($fundQualify = true){
			
			#determin if fund meets SEC Compliance
			$query = "
				SELECT secCompliance, date, timestamp
				FROM ".$_SESSION['fund_pricing_table']."
				WHERE date IN (".implode(',',$aPeriodQuarters).") AND fund_id=:fund_id
				ORDER BY unix_date ASC
			";	
					
			try{
				$rsCompliant = $mLink->prepare($query);
				$aValues = array(
					':fund_id'	=> $fundID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsCompliant->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
						
			$aSecCompliant = array();
							
			while($compliant = $rsCompliant->fetch(PDO::FETCH_ASSOC)){
			
				$aSecCompliant[$compliant['date']][] = array(
					'compliant' => $compliant['secCompliance'],
					'date'		=> $compliant['date'],
					'timestamp'	=> $compliant['timestamp'],
					'fund_id'	=> $fundID
				);
			
			}
			
			#sort the SEC compliant array and reassign the array with just the compliant data
			foreach($aSecCompliant as $date=>$aValues){
				
				usort($aValues, function($a, $b) {
					return $b['timestamp'] - $a['timestamp'];
				});
				
				$aSecCompliant[$date] = $aValues[0]['compliant'];
					
			}
			
			$numQuarters 	= count($aPeriodQuarters);
			$cntCompliant	= 0;
			foreach($aSecCompliant as $date=>$complaint){
				if($complaint == 1){
					$cntCompliant++;	
				}
			}
			
			$percentCompliant = ($cntCompliant / $numQuarters);
			
			if($percentCompliant > $compliantPercentage || $rankClass == 'community'){
				
				$gold	= $aBenchmarks[$period]['gold'];
				$silver	= $aBenchmarks[$period]['silver'];
				$bronze	= $aBenchmarks[$period]['bronze'];
				
				if($periodAAR >= $gold){
					$beat = 'gold';	
				}elseif($periodAAR >= $silver && $periodAAR < $gold ){
					$beat = 'silver';	
				}elseif($periodAAR >= $bronze && $periodAAR < $silver ){
					$beat = 'bronze';
				}
				
				$rankCnt++;
				
				$aTenYearRanks[$rankCnt] = array(
					'fund_id'		=> $fundID,
					'compliant'		=> 'yes',
					'rank'			=> $rankCnt,
					'date_start'	=> $tenYearPast,
					'date_end'		=> $currentQuarter,
					'AAR'			=> $periodAAR,
					'period'		=> '10',
					'beat'			=> $beat
				);	
				
				
				//INsert into DB
				$query = "
					INSERT INTO ".$rankTable." (
						member_id,
						fund_id,
						rank,
						date_start,
						date_end,
						AAR,
						period,
						beat,
						timestamp
					) VALUES (
						:member_id,
						:fund_id,
						:rank,
						:date_start,
						:date_end,
						:AAR,
						:period,
						:beat,
						UNIX_TIMESTAMP()
					)
				";
				try{
					$rsCompliant = $mLink->prepare($query);
					$aValues = array(
						':member_id'	=> $memberID,
						':fund_id'		=> $fundID,
						':rank'			=> $rankCnt,
						':date_start'	=> $tenYearPast,
						':date_end'		=> $currentQuarter,
						':AAR' 			=> $periodAAR,
						':period'		=> $period,
						':beat'			=> $beat
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsCompliant->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				echo $error;
			}
			
			//END TEN YEAR RANKINGS
			
			/*if($loopCnt == 100){
				break;	
			}*/
			
		}#end fund qualify check
			
		
			
	}#end while loop
	
	echo 'done';
	/*echo '<pre>';
	echo $error;
	//print_r($aBenchmarks);
	//echo $tenYearCompliant;
	//print_r($aTenYearRanks);
	
	
	echo '</pre>';	*/
}


if($process == "1"){
	
	$member = $_REQUEST['member'];
	
	//build member array
	if($member != ''){
		
		//Get only master member
		$query = '
			SELECT *
			FROM '.$_SESSION['members_table'].'
			WHERE active=:active AND member_id=:member_id
		';
		
		try{
			$rsMembers = $mLink->prepare($query);
			$aValues = array(
				':active' 		=> '1',
				':member_id'	=> $member
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsMembers->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}else{
		
		//Get all active member IDs
		$query = '
			SELECT *
			FROM '.$_SESSION['members_table'].'
			WHERE active=:active
			ORDER BY member_id ASC
		';
		
		try{
			$rsMembers = $mLink->prepare($query);
			$aValues = array(
				':active' 	=> '1'
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsMembers->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}
	$aMembers = array();
	$cnt = 0;
	
	//loop through results and assign values to array
	while($member = $rsMembers->fetch(PDO::FETCH_ASSOC)){
		$memberID 	= $member['member_id'];
		$username 	= $member['username'];
		$firstName	= $member['name_first'];
		$lastName	= $member['name_last'];
		
		$query = "
			SELECT fund_id
			FROM ".$_SESSION['fund_table']."
			WHERE member_id=:member_id AND active=:active
		";
		try{
			$rsFunds = $mLink->prepare($query);
			$aValues = array(
				':member_id' 	=> $memberID,
				':active'		=> '1'
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsFunds->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$aFunds = array();
		
		while($funds = $rsFunds->fetch(PDO::FETCH_ASSOC)){
			$aFunds[] = $funds['fund_id'];
			
		}
		
		$aMembers[$memberID] = array(
			'member_id'	=> $memberID,
			'username'	=> $username,
			'firstName'	=> $firstName,
			'lastName'	=> $lastName,
			'funds'		=> $aFunds
		);
		
		$cnt++;
		
	}
	//end build member array
	
	//Start build benchmark array
	$query = "
		SELECT * 
		FROM ".$_SESSION['system_benchmarks_table']."
		WHERE sub_type='badge' AND period=:period
	";
	
	try{
		$rsBench = $mLink->prepare($query);
		$aValues = array(
			':period' => $currentQuarter
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsBench->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aBench = array();
		
	while($bench = $rsBench->fetch(PDO::FETCH_ASSOC)){
		$aBench[$bench['type']] = array(
			'bench_id'	=> $bench['bench_id'],
			'type'		=> $bench['type'],
			'value'		=> $bench['value']	
		);
		
	}
	/*echo '<pre>';
	print_r($preparedQuery);
	print_r($aBench);
	echo '</pre>';*/
	
	//End build benchmark array
	
	//START build badge array
	$query = "
		SELECT * 
		FROM ".$_SESSION['badges_table']."
		WHERE active='1'
	";
	
	try{
		$rsBadge = $mLink->prepare($query);
		$aValues = array();
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsBadge->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aBadge = array();
		
	while($badge = $rsBadge->fetch(PDO::FETCH_ASSOC)){
		$aBadge[$badge['badge_id']] = array(
			'badge_id'		=> $badge['badge_id'],
			'badge_name'	=> $badge['badge_name'],
			'type'			=> $badge['badge_type'],
			'image'			=> $badge['badge']
		);
		
	}
	//END build badge array
	
	
	//get pricing and add to array
	foreach($aMembers as $memberID=>$aMember){
		
		$aFunds = $aMember['funds'];
		
		foreach($aFunds as $key=>$fundID){
		
			
			
			
			//START ONE YEAR
			$oneYearGold 	= $aBench['1-year-gold']['value'];
			$oneYearSilver	= $aBench['1-year-silver']['value'];
			$oneYearBronze	= $aBench['1-year-bronze']['value'];
			
			//Get price data
			$query = "
				SELECT price, unix_date
				FROM ".$_SESSION['fund_pricing_table']."
				WHERE fund_id=:fund_id AND date=:current OR fund_id=:fund_id AND date=:past
				ORDER BY unix_date DESC
			";
			
			try{
				$rsStats = $mLink->prepare($query);
				$aValues = array(
					':fund_id'	=> $fundID,
					':current'	=> $currentQuarter,
					':past'		=> $oneYearPast
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsStats->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$loopCnt = 0;
			while($stats = $rsStats->fetch(PDO::FETCH_ASSOC)){
				
				$loopCnt++;
				
				if($loopCnt == 1){
					$currentPrice 	= round($stats['price'],4);
					
					//set var for results table
					$currentNAV		= $currentPrice;
					
				}elseif($loopCnt > 1){
					$pastPrice		= round($stats['price'],4);
					
					$oneYearNAV		= $pastPrice;
				}
			}
			//End get price data
			
			if($loopCnt > 1){
				//START Calculate AAR
				$oneYear = calc_AAR($currentPrice, $pastPrice, '1');
				
				//echo '|'.$oneYear;echo '|';
				//$aMembers[$memberID]['price'][$fundID]['1year']['AAR'] = $oneYear;//	
				
				
				if(is_nan($oneYear) == TRUE){
					$setOneYear = '';
					$oneYear = '';	
				}elseif($oneYear > $oneYearGold){
					
					$setOneYear = $aBench['1-year-gold']['type'];
					$aMembers[$memberID]['badges'][$fundID][$aBadge[4]['badge_name']] = $aBadge[4]['badge_id'];
						
				}elseif($oneYear > $oneYearSilver && $oneYear < $oneYearGold){
					
					$setOneYear = $aBench['1-year-silver']['type'];	
					$aMembers[$memberID]['badges'][$fundID][$aBadge[8]['badge_name']] = $aBadge[8]['badge_id'];
					
				}elseif($oneYear > $oneYearBronze && $oneYear < $oneYearSilver){
					
					$setOneYear = $aBench['1-year-bronze']['type'];
					$aMembers[$memberID]['badges'][$fundID][$aBadge[12]['badge_name']] = $aBadge[12]['badge_id'];
					
				}elseif($oneYear == 0){
					$setOneYear = '';
					$oneYear = '';	
				}else{
					$setOneYear = '';
					//$setOneYearBadge = '';
					
					$aMembers[$memberID]['badges'][$fundID][$aBadge[16]['badge_name']] = $aBadge[16]['badge_id'];	
				}
			}else{
				$setOneYear = '';
				$oneYear = '';	
			}
			//END ONE YEAR
			
			
			//START THREE YEAR
			$threeYearGold 		= $aBench['3-year-gold']['value'];
			$threeYearSilver	= $aBench['3-year-silver']['value'];
			$threeYearBronze	= $aBench['3-year-bronze']['value'];
			
			//Get price data
			$query = "
				SELECT price, unix_date
				FROM ".$_SESSION['fund_pricing_table']."
				WHERE fund_id=:fund_id AND date=:current OR fund_id=:fund_id AND date=:past
				ORDER BY unix_date DESC
			";
			
			try{
				$rsStats = $mLink->prepare($query);
				$aValues = array(
					':fund_id'	=> $fundID,
					':current'	=> $currentQuarter,
					':past'		=> $threeYearPast
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsStats->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$loopCnt = 0;
			while($stats = $rsStats->fetch(PDO::FETCH_ASSOC)){
				
				$loopCnt++;
				
				if($loopCnt == 1){
					$currentPrice 	= round($stats['price'],4);	
					
					
				}elseif($loopCnt > 1){
					$pastPrice		= round($stats['price'],4);	
					
					$threeYearNAV	= $pastPrice;
				}
			}
			//End get price data
			
			if($loopCnt > 1){
				//START Calculate AAR
				$threeYear = calc_AAR($currentPrice, $pastPrice, '3');
				//echo '3yr AAR-'.$fundID.': '.$threeYear.'<br /><br />';
				//$aMembers[$memberID]['price'][$fundID]['3year']['AAR'] = $threeYear;//
				
				if(is_nan($threeYear) == TRUE){
					$setThreeYear = '';
					$threeYear = '';	
				}elseif($threeYear > $threeYearGold){
					
					$setThreeYear = $aBench[3]['type'];
					$aMembers[$memberID]['badges'][$fundID][$aBadge[3]['badge_name']] = $aBadge[3]['badge_id'];
						
				}elseif($threeYear > $threeYearSilver && $threeYear < $threeYearGold){
					
					$setThreeYear = $aBench[7]['type'];	
					$aMembers[$memberID]['badges'][$fundID][$aBadge[7]['badge_name']] = $aBadge[7]['badge_id'];
					
				}elseif($threeYear > $threeYearBronze && $threeYear < $threeYearSilver){
					
					$setThreeYear = $aBench[11]['type'];
					$aMembers[$memberID]['badges'][$fundID][$aBadge[11]['badge_name']] = $aBadge[11]['badge_id'];
					
				}elseif($threeYear == 0){
					$setThreeYear = '';
					$threeYear = '';	
				}else{
					$setThreeYear = '';
					//$setThreeYearBadge = '';
					
					$aMembers[$memberID]['badges'][$fundID][$aBadge[15]['badge_name']] = $aBadge[15]['badge_id'];	
				}
			}else{
				$setThreeYear = '';
				$threeYear = '';	
			}
			//END THREE YEAR
			
			
			//START FIVE YEAR
			$fiveYearGold 	= $aBench['5-year-gold']['value'];
			$fiveYearSilver	= $aBench['5-year-silver']['value'];
			$fiveYearBronze	= $aBench['5-year-bronze']['value'];
			
			//Get price data
			$query = "
				SELECT price, unix_date
				FROM ".$_SESSION['fund_pricing_table']."
				WHERE fund_id=:fund_id AND date=:current OR fund_id=:fund_id AND date=:past
				ORDER BY unix_date DESC
			";
			
			try{
				$rsStats = $mLink->prepare($query);
				$aValues = array(
					':fund_id'	=> $fundID,
					':current'	=> $currentQuarter,
					':past'		=> $fiveYearPast
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsStats->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$loopCnt = 0;
			while($stats = $rsStats->fetch(PDO::FETCH_ASSOC)){
				
				$loopCnt++;
				
				if($loopCnt == 1){
					$currentPrice 	= round($stats['price'],4);	
					
					
				}elseif($loopCnt > 1){
					$pastPrice		= round($stats['price'],4);	
					
					$fiveYearNAV	= $pastPrice;
				}
			}
			
			//End get price data
			
			
			if($loopCnt > 1){
				//START Calculate AAR
				$fiveYear = calc_AAR($currentPrice, $pastPrice, '5');
				//echo '5yr AAR-'.$fundID.': '.$fiveYear.'<br /><br />';
				//$aMembers[$memberID]['price'][$fundID]['5year']['AAR'] = $fiveYear;
				
				if(is_nan($fiveYear) == TRUE){
					$setFiveYear = '';
					$fiveYear = '';	
				}elseif($fiveYear > $fiveYearGold){
					
					$setFiveYear = $aBench[2]['type'];
					$aMembers[$memberID]['badges'][$fundID][$aBadge[2]['badge_name']] = $aBadge[2]['badge_id'];
						
				}elseif($fiveYear > $fiveYearSilver && $fiveYear < $fiveYearGold){
					
					$setFiveYear = $aBench[6]['type'];
					$aMembers[$memberID]['badges'][$fundID][$aBadge[6]['badge_name']] = $aBadge[6]['badge_id'];
					
				}elseif($fiveYear > $fiveYearBronze && $fiveYear < $fiveYearSilver){
					
					$setFiveYear = $aBench[10]['type'];
					$aMembers[$memberID]['badges'][$fundID][$aBadge[10]['badge_name']] = $aBadge[10]['badge_id'];
						
				}elseif($fiveYear == 0){
					$setFiveYear = '';
					$fiveYear = '';	
				}else{
					$setFiveYear = '';
					$aMembers[$memberID]['badges'][$fundID][$aBadge[14]['badge_name']] = $aBadge[14]['badge_id'];
				}
			}else{
				$setFiveYear = '';
				$fiveYear = '';	
			}
			//END FIVE YEAR
			
			
			//START TEN YEAR
			$tenYearGold 	= $aBench['10-year-gold']['value'];
			$tenYearSilver	= $aBench['10-year-silver']['value'];
			$tenYearBronze	= $aBench['10-year-bronze']['value'];
			
			//Get price data
			$query = "
				SELECT price, unix_date
				FROM ".$_SESSION['fund_pricing_table']."
				WHERE fund_id=:fund_id AND date=:current OR fund_id=:fund_id AND date=:past
				ORDER BY unix_date DESC
			";
			
			try{
				$rsStats = $mLink->prepare($query);
				$aValues = array(
					':fund_id'	=> $fundID,
					':current'	=> $currentQuarter,
					':past'		=> $tenYearPast
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsStats->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$loopCnt = 0;
			while($stats = $rsStats->fetch(PDO::FETCH_ASSOC)){
				
				$loopCnt++;
				
				if($loopCnt == 1){
					$currentPrice 	= round($stats['price'],4);
						
				}elseif($loopCnt > 1){
					$pastPrice		= round($stats['price'],4);
					
					$tenYearNAV		= $pastPrice;
				}
			}
			//End get price data
			
			if($loopCnt > 1){
				
				
				
				//START Calculate AAR
				$tenYear = calc_AAR($currentPrice, $pastPrice, '10');
				//$aMembers[$memberID]['price'][$fundID]['10year']['AAR'] = $tenYear; //
				
				$aMembers[$memberID]['prices'][$fundID] = array('currentPrice'=>$currentPrice,'pastPrice'=>$pastPrice,'aar'=>$tenYear);
				
				if(is_nan($tenYear) == TRUE){
					$setTenYear = '';
					$tenYear = '';	
				}elseif($tenYear > $tenYearGold){
					
					$setTenYear = $aBench[1]['type'];
					$aMembers[$memberID]['badges'][$fundID][$aBadge[1]['badge_name']] = $aBadge[1]['badge_id'];
						
				}elseif($tenYear > $tenYearSilver && $tenYear < $tenYearGold){
					
					$setTenYear = $aBench[5]['type'];
					$aMembers[$memberID]['badges'][$fundID][$aBadge[5]['badge_name']] = $aBadge[5]['badge_id'];
					
				}elseif($tenYear > $tenYearBronze && $tenYear < $tenYearSilver){
					
					$setTenYear = $aBench[9]['type'];
					$aMembers[$memberID]['badges'][$fundID][$aBadge[9]['badge_name']] = $aBadge[9]['badge_id'];
						
				}elseif($tenYear <= 0){
					$setTenYear = '';
					$tenYear = '';	
				}else{
					$setTenYear = '';
					$aMembers[$memberID]['badges'][$fundID][$aBadge[13]['badge_name']] = $aBadge[13]['badge_id'];
				}
					
				
			}else{
				$setTenYear = '';
				$tenYear = '';	
			}
			//END TEN YEAR
			
			$aMembers[$memberID]['AAR'][$fundID] = array(
				'1year'		=> $oneYear,
				'3year'		=> $threeYear,
				'5year'		=> $fiveYear,
				'10year'	=> $tenYear,
			);
			
			$aMembers[$memberID]['fundBadge'][$fundID] = array(
				'1year'		=> $setOneYear,
				'3year'		=> $setThreeYear,
				'5year'		=> $setFiveYear,
				'10year'	=> $setTenYear,
			);
			
			
			$aBadgeNamesResults = array();
			foreach($aMembers[$memberID]['badges'][$fundID] as $badgeName=>$badgeID){
				$aBadgeNamesResults[] = $badgeName;
			}
			
			//Update DB
			$query = "
				UPDATE ".$_SESSION['fund_settings_table']."
				SET badges=:badges
				WHERE fund_id=:fund_id
			";
			
			try{
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':fund_id'	=> $fundID,
					':badges'	=> implode(',', $aMembers[$memberID]['badges'][$fundID])
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdate->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$query = "
				UPDATE ".$_SESSION['fund_table']."
				SET tenYearAAR=:tenYearAAR, fiveYearAAR=:fiveYearAAR, threeYearAAR=:threeYearAAR, oneYearAAR=:oneYearAAR
				WHERE fund_id=:fund_id
			";
			
			$asOfDate = substr($currentQuarter,4,2).'/'.substr($currentQuarter,6,2).'/'.substr($currentQuarter,0,4);
			
			$query = "
				DELETE FROM `members_badge_results`
				WHERE fund_id=:fund_id AND asOfDate=:asOfDate
			";
			try{
				$rsDelete = $mLink->prepare($query);
				$aValues = array(
					':fund_id'	=> $fundID,
					':asOfDate'	=> $asOfDate
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsDelete->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			
			$query = "
				INSERT INTO `members_badge_results` (
					member_id,
					fund_id,
					badge_ids,
					badges,
					tenYearAAR,
					fiveYearAAR,
					threeYearAAR,
					oneYearAAR,
					tenYearNAV,
					fiveYearNAV,
					threeYearNAV,
					oneYearNAV,
					currentNAV,
					asOfDate,
					timestamp
				) VALUES (
					:member_id,
					:fund_id,
					:badge_ids,
					:badges,
					:tenYearAAR,
					:fiveYearAAR,
					:threeYearAAR,
					:oneYearAAR,
					:tenYearNAV,
					:fiveYearNAV,
					:threeYearNAV,
					:oneYearNAV,
					:currentNAV,
					:asOfDate,
					UNIX_TIMESTAMP()
				)
			";
			
			//echo $fiveYear;
			
			try{
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':member_id'	=> $memberID,
					':fund_id'		=> $fundID,
					':badge_ids'	=> implode(',', $aMembers[$memberID]['badges'][$fundID]),
					':badges'		=> implode(',', $aBadgeNamesResults),
					':tenYearAAR'	=> $tenYear,
					':fiveYearAAR'	=> $fiveYear,
					':threeYearAAR'	=> $threeYear,
					':oneYearAAR'	=> $oneYear,
					':tenYearNAV'	=> $tenYearNAV,
					':fiveYearNAV'	=> $fiveYearNAV,
					':threeYearNAV'	=> $threeYearNAV,
					':oneYearNAV'	=> $oneYearNAV,
					':currentNAV'	=> $currentNAV,
					':asOfDate'		=> $asOfDate
					
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdate->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			echo $error;
			
			$tenYear = '';
			$fiveYear = '';
			$threeYear = '';
			$oneYear = '';
			$tenYearNAV = '';
			$fiveYearNAV = '';
			$threeYearNAV = '';
			$oneYearNAV = '';
			$currentNAV = '';
		}
		
		
		
			
	}//end foreach member loop

	
	
	echo '<pre>';
	//echo $query2;
	//print_r($aSP500);
	//print_r($aProcess);
	//echo $error;
	//echo $cnt;
	//print_r($aBadgeNamesResults);
	//print_r($aMembers);
	//print_r($aBadge);
	//print_r($aBench);
	echo '</pre>';
	
	echo 'done';
	
}


if($process == "2"){
	
	$member 	= $_REQUEST['member'];
	$setBadge	= $_REQUEST['badge'];
	
	//START build badge array
	if($setBadge == 'all'){
		$query = "
			SELECT * 
			FROM ".$_SESSION['badges_table']."
			WHERE active='1'
		";
	}else{
		$query = "
			SELECT * 
			FROM ".$_SESSION['badges_table']."
			WHERE active='1' AND badge_id='".$setBadge."'
		";
	}
	
	try{
		$rsBadge = $mLink->prepare($query);
		$aValues = array();
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsBadge->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aBadge = array();
		
	while($badge = $rsBadge->fetch(PDO::FETCH_ASSOC)){
		$aBadges[$badge['badge_id']] = array(
			'badge_id'		=> $badge['badge_id'],
			'badge_name'	=> $badge['badge_name'],
			'type'			=> $badge['badge_type'],
			'image'			=> $badge['badge']
		);
		
	}
	//END build badge array
	
	//build member array
	if($member != ''){
		
		//Get only master member
		$query = '
			SELECT *
			FROM '.$_SESSION['members_table'].'
			WHERE active=:active AND member_id=:member_id
		';
		
		try{
			$rsMembers = $mLink->prepare($query);
			$aValues = array(
				':active' 		=> '1',
				':member_id'	=> $member
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsMembers->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}else{
		
		//Get all active member IDs
		$query = '
			SELECT *
			FROM '.$_SESSION['members_table'].'
			WHERE active=:active
			ORDER BY member_id ASC
		';
		
		try{
			$rsMembers = $mLink->prepare($query);
			$aValues = array(
				':active' 	=> '1'
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsMembers->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}
	$aMembers = array();
	$cnt = 0;
	
	//loop through results and assign values to array
	while($member = $rsMembers->fetch(PDO::FETCH_ASSOC)){
		$memberID 	= $member['member_id'];
		$username 	= $member['username'];
		$firstName	= $member['name_first'];
		$lastName	= $member['name_last'];
		$email		= $member['email'];
		
		$query = "
			SELECT fund_id, fb_primarykey, tenYearAAR, fiveYearAAR, threeYearAAR, oneYearAAR
			FROM ".$_SESSION['fund_table']."
			WHERE member_id=:member_id AND active=:active
		";
		try{
			$rsFunds = $mLink->prepare($query);
			$aValues = array(
				':member_id' 	=> $memberID,
				':active'		=> '1'
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsFunds->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$aFunds 	= array();
		$aFunds2	= array();
		
		while($funds = $rsFunds->fetch(PDO::FETCH_ASSOC)){
			$aFunds[] = $funds['fund_id'];
			
			$aFunds2[$funds['fund_id']] = array(
				'fundKey'		=> substr($funds['fb_primarykey'],2,24),
				'tenYearAAR'	=> $funds['tenYearAAR'],
				'fiveYearAAR'	=> $funds['fiveYearAAR'],
				'threeYearAAR'	=> $funds['threeYearAAR'],
				'oneYearAAR'	=> $funds['oneYearAAR']
			);
			
		}
		
		$aMembers[$memberID] = array(
			'member_id'	=> $memberID,
			'username'	=> $username,
			'firstName'	=> $firstName,
			'lastName'	=> $lastName,
			'funds'		=> $aFunds,
			'funds2'	=> $aFunds2,
			'email'		=> $email
		);
		
		$cnt++;
		
	}
	//end build member array
	
	//START GET FUNDS WITH BADGES
	
	$query = "
		SELECT badges, fund_id
		FROM ".$_SESSION['fund_settings_table']."
		WHERE badges<>''
	";
	
	try{
		$rsFunds = $mLink->prepare($query);
		$aValues = array(
			':member_id' 	=> $memberID,
			':active'		=> '1'
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsFunds->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aFunds = array();
	
	while($funds = $rsFunds->fetch(PDO::FETCH_ASSOC)){
		
		$fundID 		= $funds['fund_id'];
		$aFundBadges	= explode(',', $funds['badges']);
		$fundKey		= $aMembers[$memberID]['funds2'][$fundID]['fundKey'];
		
		foreach($aFundBadges as $key=>$badgeID){
			
			if($setBadge == 'all'){
				if(array_key_exists($badgeID, $aBadges)){
					$aFundBadges[$key] = $aBadges[$badgeID]['badge_name'].'|'.$badgeID.'|'.$aBadges[$badgeID]['image'];	
				}
			}else{
				if($setBadge == $badgeID){
					if(array_key_exists($badgeID, $aBadges)){
						$aFundBadges[$key] = $aBadges[$badgeID]['badge_name'].'|'.$badgeID.'|'.$aBadges[$badgeID]['image'];	
					}
				}else{
					$aFundBadges = '';
				}
			}
		}
		
		$getID = explode('-', $fundID);
		$memberID = $getID[0];
		
		if($aFundBadges != ''){
		
			if(array_key_exists($memberID, $aMembers)){
				$aMembers[$memberID]['badges'][$fundID] = $aFundBadges;
			}
		}
		
		
		
	}
	
	
	foreach($aMembers as $memberID=>$aMember){
		if($aMember['badges'] == ''){
			unset($aMembers[$memberID]);
		}
	}
	//END GET FUNDS WITH BADGES
	
	
	?>
    
    <form action="process/calc-badges-process.php?process=4" method="post" class="export-csv">
   	<br /><br />
    <input type="submit" name="submit-csv" value="Download CSV" />
    <table cellpadding="10" cellspacing="0" border="1">
    	<thead>
        	<tr>
            	<th>Row</th>
            	<th>Member</th>
                <th>Username / ID</th>
                <th>Email</th>
                <th>Badges</th>
                <th>Show Badges</th>
                <th>AAR</th>
            </tr>
        </thead>
        <tbody>
        
		<?php
		
		$rowCnt = 0;
		$aNumRows = array();
		
		$aRecords = array();
		
        foreach($aMembers as $memberID=>$aMember){
        	
			$rowCnt++;
			
			$aNumRows[] = $rowCnt;
			
          	$username = $aMember['username'];
			$firstName	= $aMember['firstName'];
			$lastName	= $aMember['lastName'];
			$fullName	= $firstName.' '.$lastName;
			$aFunds 	= $aMember['badges'];
			$email		= $aMember['email'];
			
			
			$aListBadges		= array();
			$aListBadgeImages	= array();
			
			foreach($aFunds as $fundID=>$aBadges){
				
				$fundSymbol 	= get_funds($mLink, $fundID, 'fundSymbol');
				$fundKey 		= $aMembers[$memberID]['funds2'][$fundID]['fundKey'];
				$tenYearAAR		= $aMembers[$memberID]['funds2'][$fundID]['tenYearAAR'];
				$fiveYearAAR	= $aMembers[$memberID]['funds2'][$fundID]['fiveYearAAR'];
				$threeYearAAR	= $aMembers[$memberID]['funds2'][$fundID]['threeYearAAR'];
				$oneYearAAR		= $aMembers[$memberID]['funds2'][$fundID]['oneYearAAR'];
				
				$aBadgeNames	= array();
				$aBadgeImages	= array();
								
				foreach($aBadges as $key=>$badge){
					$aBadge = explode('|', $badge);
					
					$aBadgeNames[] 	= $aBadge[0];
					$aBadgeImages[] = '<img src="../images/badges/'.$aBadge[2].'" width="40" height="40" />';
				}
				
				$badgeNames 		= implode(', ',$aBadgeNames);
				$badgeImages		= implode(' ', $aBadgeImages);
				
				$aListBadges[] 		= $fundSymbol.' | '.$fundID.': '.$badgeNames;
				$aListBadgeImages[]	= $fundSymbol.' | '.$fundID.': '.$badgeImages;
				
				$aAARs[] = $fundID.' : Ten Year: '.$tenYearAAR.' <br /> '.$fundID.' : Five Year: '.$fiveYearAAR.' <br /> '.$fundID.' : Three Year: '.$threeYearAAR.' <br /> '.$fundID.' : One Year: '.$oneYearAAR;
				
				$aRecords[$fundID] = array(
					'firstName'		=> $firstName,
					'lastName'		=> $lastName,
					'username'		=> $username,
					'fundSymbol'	=> $fundSymbol,
					'memberID'		=> $memberID,
					'fundID'		=> $fundID,
					'fundKey'		=> $fundKey,
					'email'			=> $email,
					'badges'		=> $badgeNames,
					'tenYearAAR'	=> $tenYearAAR,
					'fiveYearAAR'	=> $fiveYearAAR,
					'threeYearAAR'	=> $threeYearAAR,
					'oneYearAAR'	=> $oneYearAAR,
				);
					
			}
			
			$listBadges 			= implode('<br />', $aListBadges);
			$listBadgeImages		= implode('<hr /><br />', $aListBadgeImages);
			
			$formBadges 			= implode('|', $aListBadges);
			
			$showAAR				= implode('<br />', $aAARs);
			
			$_SESSION['badgeRecords'] = $aRecords;
			
			?>
            	<tr>
                	<td><?php echo $rowCnt;?></td>
                    <td><?php echo $fullName;?><input type="hidden" name="first_name-<?php echo $rowCnt;?>" value="<?php echo $firstName;?>" /><input type="hidden" name="last_name-<?php echo $rowCnt;?>" value="<?php echo $lastName;?>" /></td>
                    <td><?php echo $username.' / '.$memberID;?><input type="hidden" name="username-<?php echo $rowCnt;?>" value="<?php echo $username;?>" /><input type="hidden" name="member_id-<?php echo $rowCnt;?>" value="<?php echo $memberID;?>" /></td>
                    <td><?php echo $email;?><input type="hidden" name="email-<?php echo $rowCnt;?>" value="<?php echo $email;?>" /></td>
                    <td><?php echo $listBadges;?><input type="hidden" name="badges-<?php echo $rowCnt;?>" value="<?php echo $formBadges;?>" /></td>
                    <td><?php echo $listBadgeImages;?> </td>
                    <td><?php echo $showAAR;?></td>
                </tr>
            <?php
            //unset($aListBadgeImages);
			//unset($listBadgeImages);
        }
		?>
		</tbody>
    </table>
    <input type="hidden" name="rows" value="<?php echo implode('|',$aNumRows);?>" />
    </form>
	<?php
	
	
	
	echo '<pre>';
	//print_r($aBadgeNames['18-1']);
	//print_r($aMembers);
	//print_r($aBadges);
	echo '</pre>';
	
}

if($process == 'badge-report'){

	$member 	= $_REQUEST['member'];
	$setBadge	= $_REQUEST['badge'];
	
	//build member array
	if($member != ''){
		
		//Get only master member
		/*$query = '
			SELECT *
			FROM members_badge_results
			WHERE member_id=:member_id AND badge_ids IS NOT NULL
		';*/
		$query = '
			SELECT *
			FROM members_badge_results
			WHERE member_id=:member_id
		';
		
		try{
			$rsResults = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $member				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsResults->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}else{
		
		//Get all active member IDs
		/*$query = '
			SELECT *
			FROM members_badge_results
			WHERE badge_ids IS NOT NULL
			ORDER BY member_id ASC
		';*/
		$query = '
			SELECT *
			FROM members_badge_results
			ORDER BY member_id ASC
		';
		
		try{
			$rsResults = $mLink->prepare($query);
			$aValues = array();
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsResults->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}
	$aMembers = array();
	$cnt = 0;
	
	$aCSV = array();
	
	$aCSV[] = array(
		'Count', 'FirstName', 'LastName', 'Email', 'Username', 'MemberID', 'Badges', 'FundSymbol', 'FundID','FundKey', 'Return10', 'Return5', 'Return3', 'Return1', 'NAV10', 'NAV5', 'NAV3', 'NAV1', 'NAV', 'asOfDate'
	);
	
	//loop through results and assign values to array
	while($result = $rsResults->fetch(PDO::FETCH_ASSOC)){
		$memberID 		= $result['member_id'];
		$fundID			= $result['fund_id'];
		$aBadgeIDs		= explode(',',$result['badge_ids']);
		$aBadges		= explode(',',$result['badges']);
		$tenYearNAV		= $result['tenYearNAV'];
		$fiveYearNAV	= $result['fiveYearNAV'];
		$threeYearNAV	= $result['threeYearNAV'];
		$oneYearNAV		= $result['oneYearNAV'];
		$currentNAV		= $result['currentNAV'];
		$tenYearAAR		= $result['tenYearAAR'];
		$fiveYearAAR	= $result['fiveYearAAR'];
		$threeYearAAR	= $result['threeYearAAR'];
		$oneYearAAR		= $result['oneYearAAR'];
		$asOfDate		= $result['asOfDate'];
						
		$query = "
			SELECT fb_primarykey, fund_symbol
			FROM ".$_SESSION['fund_table']."
			WHERE fund_id=:fund_id
		";
		try{
			$rsFunds = $mLink->prepare($query);
			$aValues = array(
				':fund_id'	=> $fundID
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsFunds->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$fund = $rsFunds->fetch(PDO::FETCH_ASSOC);
		
		$fundSymbol		= $fund['fund_symbol'];
		$fundKey		= strtoupper(substr($fund['fb_primarykey'],2,24));
		
		
		
		$query = "
			SELECT name_first, name_last, username, email
			FROM ".$_SESSION['members_table']."
			WHERE member_id=:member_id
		";
		try{
			$rsMember = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $memberID
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsMember->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$member = $rsMember->fetch(PDO::FETCH_ASSOC);
		
		$firstName	= $member['name_first'];
		$lastName	= $member['name_last'];
		$username	= $member['username'];
		$email		= $member['email'];
		
		
		
		
		if($setBadge != 'all'){
			if(in_array($setBadge, $aBadgeIDs)){
			
				$aCSV[] = array(
					'count'			=> $cnt,
					'firstName'		=> $firstName,
					'lastName'		=> $lastName,
					'email'			=> $email,
					'username'		=> $username,
					'member_id'		=> $memberID,
					'badges'		=> implode(',', $aBadges),
					'fundSymbol'	=> $fundSymbol,
					'fundID'		=> $fundID,
					'fundKey'		=> $fundKey,
					'tenAAR'		=> $tenYearAAR,
					'fiveAAR'		=> $fiveYearAAR,
					'threeAAR'		=> $threeYearAAR,
					'oneAAR'		=> $oneYearAAR,
					'tenNAV'		=> $tenYearNAV,
					'fiveNAV'		=> $fiveYearNAV,
					'threeNAV'		=> $threeYearNAV,
					'oneNAV'		=> $oneYearNAV,
					'currentNAV'	=> $currentNAV,
					'asOfDate'		=> $asOfDate
				);
				
				$cnt++;
			}
			
			
		}else{
			$aCSV[] = array(
				'count'			=> $cnt,
				'firstName'		=> $firstName,
				'lastName'		=> $lastName,
				'email'			=> $email,
				'username'		=> $username,
				'member_id'		=> $memberID,
				'badges'		=> implode(',', $aBadges),
				'fundSymbol'	=> $fundSymbol,
				'fundID'		=> $fundID,
				'fundKey'		=> $fundKey,
				'tenAAR'		=> $tenYearAAR,
				'fiveAAR'		=> $fiveYearAAR,
				'threeAAR'		=> $threeYearAAR,
				'oneAAR'		=> $oneYearAAR,
				'tenNAV'		=> $tenYearNAV,
				'fiveNAV'		=> $fiveYearNAV,
				'threeNAV'		=> $threeYearNAV,
				'oneNAV'		=> $oneYearNAV,
				'currentNAV'	=> $currentNAV,
				'asOfDate'		=> $asOfDate
			);
			
			$cnt++;
			
		}
		
	}
	//end build member array
	
	/*echo '<pre>';
	print_r($aCSV);
	echo '</pre>';*/
	
	query_to_csv($aCSV, "badge-export-".date('Y.m.d-h.i').".csv", true);
	
	//echo 'done';
}

if($process == '3'){
	$aRows = explode('|',$_REQUEST['rows']);
	
	//print_r($aRows);

	$aCSV = array();
	
	$aCSV[] = array(
		'Row', 'First Name', 'Last Name', 'Email', 'Username', 'Member ID', 'Badges'
	);
	
	foreach($aRows as $key=>$row){
		
		$firstName 	= $_REQUEST['first_name-'.$row];
		$lastName	= $_REQUEST['last_name-'.$row];
		$email		= $_REQUEST['email-'.$row];
		$username	= $_REQUEST['username-'.$row];
		$memberID	= $_REQUEST['member_id-'.$row];
		$aBadges 	= explode('|',$_REQUEST['badges-'.$row]);
		 
		$listBadges = implode(', ', $aBadges);
		
		//if($process == "Yes"){
			$aCSV[] = array(
				'row'				=> $row,
				'first_name'		=> $firstName,
				'last_name'			=> $lastName,
				'email'				=> $email,
				'username'			=> $username,
				'MemberID'			=> $memberID,
				'Badges'			=> $listBadges
			);
		//}
			
	}
	


	/*function query_to_csv($array, $filename, $attachment = false, $headers = true) {
        
        if($attachment) {
            // send response headers to the browser
            header( 'Content-Type: text/csv' );
            header( 'Content-Disposition: attachment;filename='.$filename);
            $fp = fopen('php://output', 'w');
        } else {
            $fp = fopen($filename, 'w');
        }
        
        		
		foreach($array as $key => $row){
			fputcsv($fp, $row, ',', '"');
		}
		
		
        
        fclose($fp);
    }*/

    // Using the function
    //$sql = "SELECT * FROM table";
    // $db_conn should be a valid db handle

    // output as an attachment
    query_to_csv($aCSV, "badge-export-".date('Y.m.d-h.i').".csv", true);

    
	
	/*echo '<pre>';
	print_r($aCSV);
	echo '</pre>';*/
	
}

if($process == '4'){
	$aRows = $_SESSION['badgeRecords'];
	
	//print_r($aRows);

	$aCSV = array();
	
	$aCSV[] = array(
		'Count', 'FirstName', 'LastName', 'Email', 'Username', 'FundSymbol', 'MemberID', 'Badges', 'FundKey', 'FundID', 'Return10', 'Return5', 'Return3', 'Return1', 'NAV10', 'NAV5', 'NAV3', 'NAV1', 'currentNAV'
	);
	
	$rowCnt = 0;
	
	foreach($aRows as $key=>$row){
		
		$rowCnt++;
		
		$firstName 	= $row['firstName'];
		$lastName	= $row['lastName'];
		$email		= $row['email'];
		$username	= $row['username'];
		$fundSymbol	= $row['fundSymbol'];
		$memberID	= $row['memberID'];
		$badges 	= $row['badges'];
		$fundKey	= $row['fundKey'];
		$fundID		= $row['fundID'];
		$tenYear	= $row['tenYearAAR']; 
		$fiveYear	= $row['fiveYearAAR']; 
		$threeYear	= $row['threeYearAAR']; 
		$oneYear	= $row['oneYearAAR']; 
		
		//if($process == "Yes"){
			$aCSV[] = array(
				'row'				=> $rowCnt,
				'first_name'		=> $firstName,
				'last_name'			=> $lastName,
				'email'				=> $email,
				'username'			=> $username,
				'fund symbol'		=> $fundSymbol,
				'MemberID'			=> $memberID,
				'Badges'			=> $badges,
				'Fund_key'			=> strtoupper($fundKey),
				'Fund ID'			=> $fundID,
				'tenYear'			=> $tenYear,
				'fiveYear'			=> $fiveYear,
				'threeYear'			=> $threeYear,
				'oneYear'			=> $oneYear
			);
		//}
			
	}
	


	/*function query_to_csv($array, $filename, $attachment = false, $headers = true) {
        
        if($attachment) {
            // send response headers to the browser
            header( 'Content-Type: text/csv' );
            header( 'Content-Disposition: attachment;filename='.$filename);
            $fp = fopen('php://output', 'w');
        } else {
            $fp = fopen($filename, 'w');
        }
        
        		
		foreach($array as $key => $row){
			fputcsv($fp, $row, ',', '"');
		}
		
		
        
        fclose($fp);
    }*/

    // Using the function
    //$sql = "SELECT * FROM table";
    // $db_conn should be a valid db handle

    // output as an attachment
    query_to_csv($aCSV, "badge-export-".date('Y.m.d-h.i').".csv", true);

    
	
	/*echo '<pre>';
	print_r($aCSV);
	echo '</pre>';*/
	
}


if($process == '5'){
	
	$member = $_REQUEST['member'];
	
	//build member array
	if($member != ''){
		
		//Get only master member
		$query = '
			SELECT *
			FROM '.$_SESSION['members_table'].'
			WHERE active=:active AND member_id=:member_id
		';
		
		try{
			$rsMembers = $mLink->prepare($query);
			$aValues = array(
				':active' 		=> '1',
				':member_id'	=> $member
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsMembers->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}else{
		
		//Get all active member IDs
		$query = '
			SELECT *
			FROM '.$_SESSION['members_table'].'
			WHERE active=:active
			ORDER BY member_id ASC
		';
		
		try{
			$rsMembers = $mLink->prepare($query);
			$aValues = array(
				':active' 	=> '1'
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsMembers->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}
	$aMembers = array();
	$cnt = 0;
	 //echo 'hello';
	//loop through results and assign values to array
	while($member = $rsMembers->fetch(PDO::FETCH_ASSOC)){
		$memberID 	= $member['member_id'];
		$username 	= $member['username'];
		$firstName	= $member['name_first'];
		$lastName	= $member['name_last'];
		
		$query = "
			SELECT fund_id, fund_symbol
			FROM ".$_SESSION['fund_table']."
			WHERE member_id=:member_id AND active=:active
		";
		try{
			$rsFunds = $mLink->prepare($query);
			$aValues = array(
				':member_id' 	=> $memberID,
				':active'		=> '1'
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsFunds->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$aFunds = array();
		
		while($funds = $rsFunds->fetch(PDO::FETCH_ASSOC)){
			$aFunds[$funds['fund_id']] = $funds['fund_symbol'];
			
		}
		
		$aMembers[$memberID] = array(
			'member_id'	=> $memberID,
			'username'	=> $username,
			'firstName'	=> $firstName,
			'lastName'	=> $lastName,
			'funds'		=> $aFunds
		);
		
		$cnt++;
		
	}
	//end build member array
	$port = rand(52101, 52499);
	
	foreach($aMembers as $memberID=>$aMember){
		
		foreach($aMember['funds'] as $fundID=>$fundSymbol){
			
			foreach($aDates as $key=>$date){
				
				$query = "
					DELETE
					FROM ".$_SESSION['fund_pricing_table']."
					WHERE fund_id=:fund_id AND date=:date
				";
				
				try{
					$rsDelete = $mLink->prepare($query);
					$aValues = array(
						':fund_id'	=> $fundID,
						':date'		=> $date
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsDelete->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				// Build Query to pass to EXTERNAL API
				/*$query = 'priceRun|'.$aMember['username'].'|'.$fundID.'|'.$fundSymbol.'|'.$date.'';
				
				$port++;
				if($port >= 52499){
					$port = 52101;	
				}
				//Execute Query
				include ('../../includes/data-query-legacy.php');
				*/
				$aMethodVars[] = array(
					'method'		=> 'priceRun',
					'source'		=> 'Calc Badges Script | utilities/process/'.__FILE__.' | process: 5 | line: '.__LINE__,
					'api'			=> '2',
					'username'		=> $aMember['username'],
					'fund_id'		=> $fundID,
					'fund_symbol'	=> $fundSymbol,
					'start_date'	=> $date,
					'end_date'		=> $date,
					'group'			=> date('Ymd', time()).'-reprice-fund'
				);
				
				//echo $query.'~'.$port.'<br />';
					
			}
			
			$mlaResults = legacy_api($mLink, $aMethodVars, true);
				
		}
			
	}
	
}

?>