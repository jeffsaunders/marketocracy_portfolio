<?php
/*
Marketocracy Inc. 
Admin Rankings Processing

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/admin-rankings-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/admin-rankings.js.php
	HTML		- includes/pages/admin-rankings.php
*/

//Start Session
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

// Load System Wide Functions
require_once("../../includes/system-functions.php");

set_time_limit(600);

$aMemberTypes				= array(1,2,3,4,5,10,11); // This controls which members to include based on productID (ie: 10 = managers| 1,2,3,4,5,10,11 for everyone)

//Get Process from URL
$process = $_REQUEST['process'];

$aProcessDebug	= array();

$rankingNavTable = 'rankings_navs'; /*rankings_fund_navs*/

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
		
		if(date('Ymd',$nextQuarter) >= date('Ymd',$periodEnd)){
			$loop = false;	
		}else{
			#add to array
			$aQuarters[] = $nextQuarter;
			
			$currentQuarter = $nextQuarter;
		}	
	
	}
	
	foreach($aQuarters as $key=>$quarter){
		$aQuarters[$key] = "'".date('Ymd', $quarter)."'";	
	}
	
	return $aQuarters;
		
}
//set_time_limit(3600);

function checkCompliance($mLink, $aQuarters, $compliantPercentage, $fundID){
	$query = "
		SELECT secCompliance, date, timestamp
		FROM ".$_SESSION['fund_pricing_table']."
		WHERE date IN (".implode(',',$aQuarters).") AND fund_id=:fund_id
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
	
	$numQuarters 	= count($aQuarters);
	$cntCompliant	= 0;
	foreach($aSecCompliant as $date=>$complaint){
		if($complaint == 1){
			$cntCompliant++;	
		}
	}
	
	$percentCompliant = ($cntCompliant / $numQuarters);
	
	if($percentCompliant >= $compliantPercentage){
		return('1');	
	}else{
		return('0');	
	}
}

function processBadges($mLink){
	
}



function processRankings($mLink, $unixRankDate, $period='all', $rankClass='all', $compliantPercentage='.8'){
	
	$runCommunity	= false;
	$runPro			= false;
	
	$rankDate		= date('Ymd',$unixRankDate);
	$aRankResults	= array(); 
	
        $fifteenYearQuery = "
                SELECT *
                FROM rankings_process_results
                WHERE fifteenYearAAR<>0 AND rank_date=:rankDate
                ORDER BY fifteenYearAAR DESC
        ";
	$tenYearQuery = "
		SELECT *
		FROM rankings_process_results
		WHERE tenYearAAR<>0 AND rank_date=:rankDate
		ORDER BY tenYearAAR DESC
	";
	$fiveYearQuery 	= "
		SELECT *
		FROM rankings_process_results
		WHERE fiveYearAAR<>0 AND rank_date=:rankDate
		ORDER BY fiveYearAAR DESC
	";
	$threeYearQuery 	= "
		SELECT *
		FROM rankings_process_results
		WHERE threeYearAAR<>0 AND rank_date=:rankDate
		ORDER BY threeYearAAR DESC
	";
	$oneYearQuery 	= "
		SELECT *
		FROM rankings_process_results
		WHERE oneYearAAR<>0 AND rank_date=:rankDate
		ORDER BY oneYearAAR DESC
	";
	switch($period){
		
		case 'all':
			
                        $aRankQueries['15']	= $fifteenYearQuery;
			$aRankQueries['10']	= $tenYearQuery;
			$aRankQueries['5'] 	= $fiveYearQuery;
			$aRankQueries['3'] 	= $threeYearQuery;
			$aRankQueries['1'] 	= $oneYearQuery;
			
		break;
		
                case '15'       : $aRankQueries['15']   = $fifteenYearQuery;break;

		case '10'	: $aRankQueries['10'] 	= $tenYearQuery;break;
		
		case '5'	: $aRankQueries['5'] 	= $fiveYearQuery;break;
		
		case '3'	: $aRankQueries['3'] 	= $threeYearQuery;break;
		
		case '1'	: $aRankQueries['1'] 	= $oneYearQuery;break;
			
	}
	
	$aRankResults['queries']['rank queries'] = $aRankQueries;
	
	switch($rankClass){
		
		case 'all'			: $runCommunity = true;$runPro = true;break;
		
		case 'community'	: $runCommunity = true; break;
		
		case 'pro'			: $runPro = true;break;
			
	}
	
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
	
	$aRankResults['errors']['invalid funds query'] = $error;
	
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
	
	$aRankResults['errors']['benchmarks query'] = $error;
	
	$aBenchmarks = array();
	while($bench = $rsBench->fetch(PDO::FETCH_ASSOC)){
		
		$aType = explode('-',$bench['type']);
		
		$periodYear = $aType[0];
		$periodLevel = $aType[2];
		
		$aBenchmarks[$periodYear][$periodLevel] = $bench['value'];
	}
	
	
	
	//Run Pro Rankings
	if($runPro == true){
		
		$rankTable	= 'rankings_stage_pro';
		
		
		
		//$aRankResults['rankQuery']
		
		foreach($aRankQueries as $rankPeriod=>$rankQuery){
			
			
			
			
			//remove old stage results
			$query = "
				DELETE FROM ".$rankTable."
				WHERE period=:period and rank_date=:rankDate
			";
			try{
				$rsDelete = $mLink->prepare($query);
				$aValues = array(
					':period'	=> $rankPeriod,
					':rankDate'	=> $rankDate
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsDelete->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			
			$aRankResults['rankQuery'][$rankPeriod][] = $preparedQuery;
						
			try{
				$rsResults = $mLink->prepare($rankQuery);
				$aValues = array(
					':rankDate'	=> $rankDate
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $rankQuery); //Debug
				$rsResults->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$aRankResults['prepared queries']['get results query'][] = $preparedQuery;
			$aRankResults['errors']['get results query'][] = $error;
			
			$loopCnt = 0;
			$rankCnt		= 0;
		
			while($results = $rsResults->fetch(PDO::FETCH_ASSOC)){
				
				$loopCnt++;
				
				$memberID 			= $results['member_id'];
				$fundID				= $results['fund_id'];
                                $fifteenYearAAR                     = $results['fifteenYearAAR'];
				$tenYearAAR			= $results['tenYearAAR'];
				$fiveYearAAR		= $results['fiveYearAAR'];
				$threeYearAAR		= $results['threeYearAAR'];
				$oneYearAAR			= $results['oneYearAAR'];
                                $fifteenYearCompliant       = $results['fifteenYearCompliant'];
				$tenYearCompliant	= $results['tenYearCompliant'];
				$fiveYearCompliant	= $results['fiveYearCompliant'];
				$threeYearCompliant	= $results['threeYearCompliant'];
				$oneYearCompliant	= $results['oneYearCompliant'];
				
				$fundQualify		= true;
				
				switch($rankPeriod){
                                        case '15':
                                                $periodAAR                      = $fifteenYearAAR;
                                                $compliant                      = $fifteenYearCompliant;
                                        break;

					case '10': 
						$periodAAR 			= $tenYearAAR;
						$compliant			= $tenYearCompliant;
					break;
					
					case '5':
						$periodAAR 			= $fiveYearAAR;
						$compliant			= $fiveYearCompliant;
					break;
						
					case '3':
						$periodAAR 			= $threeYearAAR;
						$compliant			= $threeYearCompliant;
					break;	
					
					case '1':
						$periodAAR 			= $oneYearAAR;
						$compliant			= $oneYearCompliant;
					break;
				}
				
				//Check to see if fund is disqualified
				/*if(array_key_exists($fundID, $aInvalidFunds)){
					#Fund was not found in the invalid funds table
					
					$fundQualify = false;
				}#end fund qualify check	*/
				
				//check to see if fund is compliant
				if($compliant == '0' || $compliant == NULL){
					$fundQualify = false;	
				}
				
				
				if($fundQualify == true){
					
					$gold	= $aBenchmarks[$rankPeriod]['gold'];
					$silver	= $aBenchmarks[$rankPeriod]['silver'];
					$bronze	= $aBenchmarks[$rankPeriod]['bronze'];
					
					if($periodAAR >= $gold){
						$beat = 'gold';	
					}elseif($periodAAR >= $silver && $periodAAR < $gold ){
						$beat = 'silver';	
					}elseif($periodAAR >= $bronze && $periodAAR < $silver ){
						$beat = 'bronze';
					}
					
					$rankCnt++;
					
					$aRanks[$rankPeriod][$rankCnt] = array(
						'fund_id'		=> $fundID,
						'compliant'		=> 'yes',
						'rank'			=> $rankCnt,
						'date_start'	=> $fifteenYearPast,
						'date_end'		=> $currentQuarter,
						'AAR'			=> $periodAAR,
						'period'		=> '15',
						'beat'			=> $beat
					);	
					
					
					//INsert into DB
					$query = "
						INSERT INTO ".$rankTable." (
							member_id,
							fund_id,
							rank,
							rank_unix_date,
							rank_date,
							AAR,
							period,
							beat,
							timestamp
						) VALUES (
							:member_id,
							:fund_id,
							:rank,
							:rank_unix_date,
							:rank_date,
							:AAR,
							:period,
							:beat,
							UNIX_TIMESTAMP()
						)
					";
					try{
						$rsCompliant = $mLink->prepare($query);
						$aValues = array(
							':member_id'		=> $memberID,
							':fund_id'			=> $fundID,
							':rank'				=> $rankCnt,
							':rank_unix_date'	=> $unixRankDate,
							':rank_date'		=> $rankDate,
							':AAR' 				=> $periodAAR,
							':period'			=> $rankPeriod,
							':beat'				=> $beat
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsCompliant->execute($aValues);
					}
					
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
					$aRankResults['errors']['insert rank query'] = $error;
					
					
					//END TEN YEAR RANKINGS
					
					/*if($loopCnt == 100){
						break;	
					}*/
					
				}#end fund qualify check
					
				
					
			}#end while loop
				
		}
			
	}
	
	//Run Community Rankings
	if($runCommunity == true){
		
		$rankTable	= 'rankings_stage_community';
		
		//$aRankResults['rankQuery']
		
		foreach($aRankQueries as $rankPeriod=>$rankQuery){
			
			//remove old stage results
			$query = "
				DELETE FROM ".$rankTable."
				WHERE period=:period and rank_date=:rankDate
			";
			try{
				$rsDelete = $mLink->prepare($query);
				$aValues = array(
					':period'	=> $rankPeriod,
					':rankDate'	=> $rankDate
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsDelete->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			
			$aRankResults['rankQuery'][$rankPeriod] = $rankQuery;
						
			try{
				$rsResults = $mLink->prepare($rankQuery);
				$aValues = array(
					':rankDate'	=> $rankDate
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $rankQuery); //Debug
				$rsResults->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$aRankResults['prepared queries']['get results query'][] = $preparedQuery;
			$aRankResults['errors']['get results query'][] = $error;
			
			$loopCnt = 0;
			$rankCnt		= 0;
		
			while($results = $rsResults->fetch(PDO::FETCH_ASSOC)){
				
				$loopCnt++;
				
				$memberID 			= $results['member_id'];
				$fundID				= $results['fund_id'];
                                $fifteenYearAAR                     = $results['fifteenYearAAR'];
				$tenYearAAR			= $results['tenYearAAR'];
				$fiveYearAAR		= $results['fiveYearAAR'];
				$threeYearAAR		= $results['threeYearAAR'];
				$oneYearAAR			= $results['oneYearAAR'];
				
				
				$fundQualify		= true;
				
				
				
				switch($rankPeriod){
                                        case '15':
                                                $periodAAR                      = $fifteenYearAAR;
                                        break;

					case '10': 
						$periodAAR 			= $tenYearAAR;
					break;
					
					case '5':
						$periodAAR 			= $fiveYearAAR;
					break;
						
					case '3':
						$periodAAR 			= $threeYearAAR;
					break;	
					
					case '1':
						$periodAAR 			= $oneYearAAR;
					break;
				}
				
				
				
				if($fundQualify = true){
					
					$gold	= $aBenchmarks[$rankPeriod]['gold'];
					$silver	= $aBenchmarks[$rankPeriod]['silver'];
					$bronze	= $aBenchmarks[$rankPeriod]['bronze'];
					
					if($periodAAR >= $gold){
						$beat = 'gold';	
					}elseif($periodAAR >= $silver && $periodAAR < $gold ){
						$beat = 'silver';	
					}elseif($periodAAR >= $bronze && $periodAAR < $silver ){
						$beat = 'bronze';
					}
					
					$rankCnt++;
					
					$aRanks[$rankPeriod][$rankCnt] = array(
						'fund_id'		=> $fundID,
						'compliant'		=> 'yes',
						'rank'			=> $rankCnt,
						'date_start'	=> $fifteenYearPast,
						'date_end'		=> $currentQuarter,
						'AAR'			=> $periodAAR,
						'period'		=> '15',
						'beat'			=> $beat
					);	
					
					
					//INsert into DB
					$query = "
						INSERT INTO ".$rankTable." (
							member_id,
							fund_id,
							rank,
							rank_unix_date,
							rank_date,
							AAR,
							period,
							beat,
							timestamp
						) VALUES (
							:member_id,
							:fund_id,
							:rank,
							:rank_unix_date,
							:rank_date,
							:AAR,
							:period,
							:beat,
							UNIX_TIMESTAMP()
						)
					";
					try{
						$rsCompliant = $mLink->prepare($query);
						$aValues = array(
							':member_id'		=> $memberID,
							':fund_id'			=> $fundID,
							':rank'				=> $rankCnt,
							':rank_unix_date'	=> $unixRankDate,
							':rank_date'		=> $rankDate,
							':AAR' 				=> $periodAAR,
							':period'			=> $rankPeriod,
							':beat'				=> $beat
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsCompliant->execute($aValues);
					}
					
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
					$aRankResults['errors']['insert rank query'] = $error;
					
					//END TEN YEAR RANKINGS
					
				}#end fund qualify check
				
					
			}#end while loop
				
		}
		
	}
	
	return $aRankResults;
	
}


//+----------------------------------------------------------+
//|					Get Betchmarks for Period
//|	$period format: YYYYMMDD
//+----------------------------------------------------------+
function getBenchmarks($mLink, $asOfDate){
	
	$funcTime = date('Y/m/d-h:i:s');
	
	$query = "
		SELECT * 
		FROM ".$_SESSION['system_benchmarks_table']."
		WHERE sub_type='badge' AND period=:period
	";
	
	try{
		$rsBench = $mLink->prepare($query);
		$aValues = array(
			':period' => $asOfDate
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsBench->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aProcessDebug[$funcTime]['Benchmark Function']['query error'] = $error;//DEBUG
	
	$aBench = array();
		
	while($bench = $rsBench->fetch(PDO::FETCH_ASSOC)){
		$aBench[$bench['type']] = array(
			'bench_id'	=> $bench['bench_id'],
			'type'		=> $bench['type'],
			'value'		=> $bench['value']	
		);
		
	}
	
	return($aBench);		
}

function getBadgeInfo($mLink, $type=NULL){
	
	$query = "
		SELECT * 
		FROM ".$_SESSION['badges_table']."
		WHERE active='1'
		ORDER BY weight ASC
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
		
		if($type == NULL){
			$aBadge[$badge['badge_id']] = array(
				'group'			=> $badge['group'],
				'badge_id'		=> $badge['badge_id'],
				'badge_name'	=> $badge['badge_name'],
				'type'			=> $badge['type'],
				'image'			=> $badge['badge'],
				'desc'			=> $badge['badge_desc']
			);
		}else{
			$aBadge[$badge['sub_type']][$badge['metal']] = array(
				'badge_id'		=> $badge['badge_id'],
				'badge_name'	=> $badge['badge_name']
			);
		}
		
		
		
		
	}
	
	
	
	
	return($aBadge);
		
	
}

function processAARs($mLink, $asOfDate){
	
	$aBench 	= getBenchmarks($mLink, $asOfDate);
	
}

switch($process){

	case 'view-rank-period':
		
		$year 					= $_REQUEST['year'];
		$month					= $_REQUEST['month'];
		$day					= date('t',mktime(6,0,0,$month,1,$year));
		
		//echo $day;
		//$day					= $_REQUEST['day'];
		$asOfDate				= $year.$month.$day;
		//$asOfDate				= $month.'/'.$day.'/'.$year;
		$currentQuarter			= mktime(6,0,0,$month,$day,$year);
		$currentQuarterStr		= date('Ymd',$currentQuarter); 
		$oneYearPast			= strtotime(''.$year.'-'.$month.'-'.$day.' -1 years');
		$oneYearPastStr			= date('Ymd',$oneYearPast); 
		$threeYearPast			= strtotime(''.$year.'-'.$month.'-'.$day.' -3 years');
		$threeYearPastStr		= date('Ymd',$threeYearPast);  
		$fiveYearPast			= strtotime(''.$year.'-'.$month.'-'.$day.' -5 years'); 
		$fiveYearPastStr		= date('Ymd',$fiveYearPast); 
		$tenYearPast			= strtotime(''.$year.'-'.$month.'-'.$day.' -10 years'); 
		$tenYearPastStr			= date('Ymd',$tenYearPast); 
                $fifteenYearPast                = strtotime(''.$year.'-'.$month.'-'.$day.' -15 years');
                $fifteenYearPastStr             = date('Ymd',$fifteenYearPast);
		$aBadge					= getBadgeInfo($mLink, NULL);
		
		
		
		#check Raw Nav Table for records
		$query = "
			SELECT COUNT(*) as count
			FROM rank_raw_nav
			WHERE as_of_date=:asOfDate
		";
		try{
			$rsRaw = $mLink->prepare($query);
			$aValues = array(
				':asOfDate'		=> $asOfDate
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsRaw->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$numRaw = $rsRaw->fetch(PDO::FETCH_ASSOC);
		$numRaw = $numRaw['count'];
		
		
		
		if($numRaw > 0){ 
			$rawNavStatus 	= '<span class="label label-success">Complete | Records: '.number_format($numRaw,0,'.',',').'</span>';
			$rawAction		= '';
		}else{ 
			$rawNavStatus 	= '<span class="label label-warning">Incomplete | No Records</span>';
			$rawAction		= '<button type="button" class="btn btn-warning btn-sm"><i class="icon-warning-sign"></i> Run Legacy Scripts</button>';
		}
		
		#check benchmark table for records
		$aBench = getBenchmarks($mLink, $asOfDate);
		
		$benchCount = count($aBench);
		
		if($benchCount > 0){ 
		
			$benchStatus 	= '<span class="label label-success">Complete | Records: '.number_format($benchCount,0,'.',',').'</span>'; 
			$benchAction	= '<a href="#upload-bench" class="btn btn-info btn-xs" data-toggle="modal"><i class="icon-pencil"></i> Edit/View Benchmarks</a>';
		}else{ 
			
			$benchStatus 	= '<span class="label label-warning">Incomplete | No Records</span>';
			$benchAction	= '<a href="#upload-bench" class="btn btn-success btn-sm" data-toggle="modal"><i class="icon-upload"></i> Upload Benchmarks</a>';
		}
		
		#check rank_aar table for records
		$query = "
			SELECT COUNT(*) as count
			FROM rank_aar
			WHERE as_of_date=:asOfDate
		";
		try{
			$rsAAR = $mLink->prepare($query);
			$aValues = array(
				':asOfDate'		=> $asOfDate
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsAAR->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$numAAR = $rsAAR->fetch(PDO::FETCH_ASSOC);
		$numAAR = $numAAR['count'];
		
		
		if($numRaw <= 0){
			$aarStatus 	= '<span class="label label-warning">Incomplete | No Records</span>';
			$aarAction	= '<span class="label label-default">Waiting on Raw NAV Data</span>';
		}elseif($benchCount <= 0){
			$aarStatus 	= '<span class="label label-warning">Incomplete | No Records</span>';
			$aarAction	= '<span class="label label-default">Waiting on Benchmarks</span>';
		}elseif($numAAR <= 0){
			$aarStatus 	= '<span class="label label-warning">Incomplete | No Records</span>';
			$aarAction	= '<a href="#aar-table-build" data-toggle="modal" class="btn btn-success btn-sm"><i class="icon-tasks"></i> Build AAR Table</a>';
		}elseif($numAAR > 0){
			$aarStatus 	= '<span class="label label-success">Complete | Records: '.number_format($numAAR,0,'.',',').'</span>';
			$aarAction	= '<a href="#aar-table-build" data-toggle="modal" class="btn btn-info btn-xs"><i class="icon-repeat"></i> Re-Build AAR Table</a> <a href="#outlier" data-toggle="modal" class="btn btn-success btn-xs"><i class="icon-tasks"></i> Calculate Anomalies</a>';
		}
		
		#check stage tables for records
		$query = "
			SELECT COUNT(*) as count
			FROM rank_stage_community
			WHERE as_of_date=:asOfDate
		";
		try{
			$rsStageCom = $mLink->prepare($query);
			$aValues = array(
				':asOfDate'		=> $asOfDate
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsStageCom->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$numStageCom = $rsStageCom->fetch(PDO::FETCH_ASSOC);
		$numStageCom = $numStageCom['count'];
		
		$query = "
			SELECT COUNT(*) as count
			FROM rank_stage_paid_pro
			WHERE as_of_date=:asOfDate
		";
		try{
			$rsStagePro = $mLink->prepare($query);
			$aValues = array(
				':asOfDate'		=> $asOfDate
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsStagePro->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$numStagePro = $rsStagePro->fetch(PDO::FETCH_ASSOC);
		$numStagePro = $numStagePro['count'];
		
		echo $numStagePro;
		
		if($numAAR == 0){
			$stageStatus 	= '<span class="label label-warning">Incomplete | No Records</span>';
			$stageAction	= '<span class="label label-default">Waiting on AAR Data</span>';
		}elseif($numStagePro <= 0){
			$stageStatus 	= '<span class="label label-warning">Incomplete | No Records</span>';
			$stageAction	= '<a href="#stage-table" data-toggle="modal" class="btn btn-success btn-sm"><i class="icon-tasks"></i> Build Stage Tables</a>';
		}elseif($numStagePro > 0){
			$stageStatus 	= '<span class="label label-success">Complete | Community Records: '.number_format($numStagePro,0,'.',',').' a| Paid Pro Records: '.number_format($numStagePro,0,'.',',').'</span>';
			$stageAction	= '<a href="#stage-table" data-toggle="modal" class="btn btn-info btn-xs"><i class="icon-repeat"></i> Re-Build Stage Tables</a> <a href="?page=06-00-00-011&date='.$asOfDate.'" class="btn btn-xs btn-success" target="_blank"><i class="icon-eye-open"></i> View Stage Ranks</a>';
		}
		
		#check rank tables for records
		$query = "
			SELECT COUNT(*) as count
			FROM rank_pub_pro
			WHERE as_of_date=:asOfDate
		";
		try{
			$rsPubPro = $mLink->prepare($query);
			$aValues = array(
				':asOfDate'		=> $asOfDate
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsPubPro->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$numPubPro = $rsPubPro->fetch(PDO::FETCH_ASSOC);
		$numPubPro = $numPubPro['count'];
		
		if($numStagePro == 0){
			$pubStatus 	= '<span class="label label-warning">Incomplete | No Records</span>';
			$pubAction	= '<span class="label label-default">Waiting on Stage Data</span>';
		}elseif($numPubPro <= 0){
			$pubStatus 	= '<span class="label label-warning">Incomplete | No Records</span>';
			$pubAction	= '<a href="#publish" data-toggle="modal" class="btn btn-success btn-sm"><i class="icon-tasks"></i> Publish Rankings</a>';
		}elseif($numPubPro > 0){
			$pubStatus 	= '<span class="label label-success">Complete | Community Records: '.number_format($numPubCom,0,'.',',').' | Pro Records: '.number_format($numPubPro,0,'.',',').'</span>';
			$pubAction	= '<a href="#publish" data-toggle="modal" class="btn btn-info btn-xs"><i class="icon-repeat"></i> Re-Publish Rankings</a>';
		}
		
		?>
        <!-- START Benchmark MODAL-->
        <div class="modal fade" id="upload-bench" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        	<div class="modal-dialog modal-wide">
            	<div class="modal-content">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Upload Benchmarks</span></h4>
                    </div>
                    <div class="modal-body">
                    
                    	<form method="post" class="benchmark-form">
                        	
                            
                            <?php
							$aProcessNames = array();
							
							foreach($aBadge as $badgeID=>$aBadgeInfo){
								
								if($aBadgeInfo['group'] == 'returns'){
									
								$aProcessNames[] = $aBadgeInfo['type'];
									
								echo '<div class="form-group"><label class="control-label">'.$aBadgeInfo['badge_name'].'</label><br /><input placeholder="Example: 12.25" class="form-control" type="text" name="'.$aBadgeInfo['type'].'" value="'.$aBench[$aBadgeInfo['type']]['value'].'" /></div>';
								}
							}
							
							$processNames = implode(',',$aProcessNames);	
							?>
                            
                            <div class="form-group">
                            <label class="control-label">As Of Date</label>
                            <input type="text" class="form-control" value="<?php echo $asOfDate;?>" name="asOfDate" />
                            </div>
                            
                            <input type="hidden" name="process-names" value="<?php echo $processNames;?>" />
                            
                            <div id="bench-form-submit"><input type="submit" value="Save Benchmarks" class="btn btn-success" /></div>
                            
                            <div id="bench-form-errors"></div>
                            
                        </form>
                    
                    </div><!--modal-body-->
          		</div><!--modal-content -->
        	</div><!--modal-dialog -->
        </div><!--modal-->      
        
        <!-- START AAR MODAL-->
        <div class="modal fade" id="aar-table-build" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        	<div class="modal-dialog modal-wide">
            	<div class="modal-content">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Build AAR Table</span></h4>
                    </div>
                    <div class="modal-body">
                    
                    	<form method="post" class="aar-form">
                            
                            <div class="form-group">
                            <label class="control-label">As Of Date</label>
                            <input type="text" class="form-control" value="<?php echo $asOfDate;?>" name="asOfDate" />
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Compliance Tolerance</label><br />
                                <select name="tolerance">
                                    <option value=".80">80%</option>
                                    <option value=".100">100%</option>
                                    <option value=".95">95%</option>
                                    <option value=".90">90%</option>
                                    <option value=".85">85%</option>
                                    
                                    <option value=".75">75%</option>
                                    <option value=".70">70%</option>
                                </select>
                            </div>
                            
                            <input type="hidden" name="process-names" value="<?php echo $processNames;?>" />
                            
                            <div id="aar-form-submit"><input type="submit" value="Build AAR Table" class="btn btn-success" /></div>
                            
                            <div id="aar-form-errors"></div>
                            
                        </form>
                    
                    </div><!--modal-body-->
          		</div><!--modal-content -->
        	</div><!--modal-dialog -->
        </div><!--modal-->      
        
        <!-- START OUTLIER MODAL-->
        <div class="modal fade" id="outlier" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        	<div class="modal-dialog modal-wide">
            	<div class="modal-content">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Calculate Anomalies</span></h4>
                    </div>
                    <div class="modal-body">
                    
                    	<form method="post" class="outlier-form">
                            
                            <div class="form-group">
                            <label class="control-label">As Of Date</label>
                            <input type="text" class="form-control" value="<?php echo $asOfDate;?>" name="asOfDate" />
                            </div>
                            
                            <hr />
                            
                            
                            <div class="form-group">
                            	<label class="control-label"><input type="checkbox" value="1" name="reprice" /> Reprice Anomalies</label><br />
                        		<small>Check this box after you calculate anomalies.</small><br />
                            
                                <label class="control-label">Select API</label><br />
                                <select name="api-server">
                                    <option value="api1">API1</option>
                                    <option value="api2" selected>API2</option>
                                    <option value="api3">API3</option>
                                </select>
                            </div>
                            
                            <input type="hidden" name="as_of_timestamp" value="<?php echo $currentQuarter;?>" /> 
                            <input type="hidden" name="process-names" value="<?php echo $processNames;?>" />
                            
                            <div id="outlier-form-submit"><input type="submit" value="Calculate Anomalies" class="btn btn-success" /></div>
                            
                            <div id="outlier-form-errors"></div>
                            
                        </form>
                    
                    </div><!--modal-body-->
          		</div><!--modal-content -->
        	</div><!--modal-dialog -->
        </div><!--modal-->      
        
        <!-- START STAGE MODAL-->
        <div class="modal fade" id="stage-table" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        	<div class="modal-dialog modal-wide">
            	<div class="modal-content">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Build Stage Tables</span></h4>
                    </div>
                    <div class="modal-body">
                    
                    	<form method="post" class="stage-form">
                            
                            <div class="form-group">
                            <label class="control-label">As Of Date</label>
                            <input type="text" class="form-control" value="<?php echo $asOfDate;?>" name="asOfDate" />
                            </div>
                            
                            <div class="form-group">
                            	<div>
                                    <input type="checkbox"  value='1' name="run_pro" checked /> Pro Rankings
                                    
                                </div>
                                
                                <div >
                                    <input type="checkbox"  value='1' name="run_paid_pro" checked /> Paid-Pro Rankings
                                    
                                </div>
                                
                                <div>
                                    <input type="checkbox"  value='1' name="run_community" checked /> Community Rankings
                                    
                                </div>
                                
                                
                            </div>
                            
                            
                            
                            
                            
                            <input type="hidden" name="as_of_timestamp" value="<?php echo $currentQuarter;?>" />                            
                            <div id="stage-form-submit"><input type="submit" value="Build Stage Tables" class="btn btn-success" /></div>
                            
                            <div id="stage-form-errors"></div>
                            
                        </form>
                    
                    </div><!--modal-body-->
          		</div><!--modal-content -->
        	</div><!--modal-dialog -->
        </div><!--modal-->
        
        <!-- START STAGE MODAL-->
        <div class="modal fade" id="publish" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        	<div class="modal-dialog modal-wide">
            	<div class="modal-content">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Publish Rankings</span></h4>
                    </div>
                    <div class="modal-body">
                    
                    	<form method="post" class="publish-form">
                            
                            <div class="form-group">
                            <label class="control-label">As Of Date</label>
                            <input type="text" class="form-control" value="<?php echo $asOfDate;?>" name="asOfDate" />
                            </div>
                            
                            <div class="form-group">
                            	<div>
                                    <input type="checkbox"  value='1' name="run_pro" checked /> Pro Rankings
                                    
                                </div>
                                
                                <div >
                                    <input type="checkbox"  value='1' name="run_paid_pro" checked /> Paid-Pro Rankings
                                    
                                </div>
                                
                                <div>
                                    <input type="checkbox"  value='1' name="run_community" checked /> Community Rankings
                                    
                                </div>
                                
                                
                            </div>
                            
                            <div class="form-group">
                            	<div>
                                    <input type="checkbox"  value='1' name="run_report" checked /> Build Report
                                    
                                </div>
                            </div>
                            
                            <input type="hidden" name="as_of_timestamp" value="<?php echo $currentQuarter;?>" />                            
                            <div id="publish-form-submit"><input type="submit" value="Publish Rankings" class="btn btn-success" /></div>
                            
                            <div id="publish-form-errors"></div>
                            
                        </form>
                    
                    </div><!--modal-body-->
          		</div><!--modal-content -->
        	</div><!--modal-dialog -->
        </div><!--modal-->
        
        <table class="table table-bordered table-striped table-hover-alt">
        	<thead>
            	<tr>
                	<th colspan="4">As Of Date: <?php echo date('m/d/Y',$currentQuarter);?></th>
                </tr>
            	<tr>
                	<th>Step</th>
                	<th>Process</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            	<tr>
                	<th>1</th>
                	<th>Raw NAV</th>
                    <td><?php echo $rawNavStatus;?></span></td>
                    <td><?php echo $rawAction;?></td>
                </tr>
                <tr>
                	<th>2</th>
                	<th>Benchmarks</th>
                    <td><?php echo $benchStatus;?></td>
                    <td><?php echo $benchAction;?></td>
                </tr>
                <tr>
                	<th>3</th>
                	<th>Calculate AARs</th>
                    <td><?php echo $aarStatus;?></td>
                    <td><?php echo $aarAction;?></td>
                </tr>
                <tr>
                	<th>4</th>
                	<th>Build Stage Tables</th>
                    <td><?php echo $stageStatus;?></td>
                    <td><?php echo $stageAction;?></td>
                </tr>
                <tr>
                	<th>5</th>
                	<th>Publish Rankings</th>
                    <td><?php echo $pubStatus;?></td>
                    <td><?php echo $pubAction;?></td>
                </tr>
                
            </tbody>
        </table>
        <?php
		
	break;
	
	#--------------------------------------------------------------------------------------------------------------------#
	#											Exclude Fund
	#--------------------------------------------------------------------------------------------------------------------#
	case 'exclude-fund':
		
		$fundID		= $_REQUEST['fund_id'];
		$asOfDate	= $_REQUEST['asOfDate'];
		$rankTable	= $_REQUEST['rank_table'];
		
		$query = "
			UPDATE ".$rankTable."
			SET exclude='1'
			WHERE fund_id=:fund_id AND as_of_date=:asOfDate
		";
		try{
			$rsAAR = $mLink->prepare($query);
			$aValues = array(
				':asOfDate'	=> $asOfDate,
				':fund_id'	=> $fundID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsAAR->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		
		
	break;
	
	#--------------------------------------------------------------------------------------------------------------------#
	#											Include Fund
	#--------------------------------------------------------------------------------------------------------------------#
	case 'include-fund':
		
		$fundID		= $_REQUEST['fund_id'];
		$asOfDate	= $_REQUEST['asOfDate'];
		$rankTable	= $_REQUEST['rank_table'];
		
		$query = "
			UPDATE ".$rankTable."
			SET exclude=NULL
			WHERE fund_id=:fund_id AND as_of_date=:asOfDate
		";
		try{
			$rsAAR = $mLink->prepare($query);
			$aValues = array(
				':asOfDate'	=> $asOfDate,
				':fund_id'	=> $fundID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsAAR->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		
		
	break;
	
	#--------------------------------------------------------------------------------------------------------------------#
	#											PUBLISH RANKINGS
	#--------------------------------------------------------------------------------------------------------------------#
	case 'publish-rankings':
	
		$asOfDate					= $_REQUEST['asOfDate'];
		$asOfTimestamp				= $_REQUEST['as_of_timestamp'];
		$aRun['pro']				= $_REQUEST['run_pro'] == 1 ? true : false;
		$aRun['paid-pro']			= $_REQUEST['run_paid_pro'] == 1 ? true : false;
		$aRun['community']			= $_REQUEST['run_community'] == 1 ? true : false;
		$rankReport					= $_REQUEST['run_report'] == 1 ? true : false;
		
		$aRank = array(
                        '15'    => array(0=>'delete'),
			'10' 	=> array(0=>'delete'),
			'5'		=> array(0=>'delete'),
			'3'		=> array(0=>'delete'),
			'1'		=> array(0=>'delete')
		);
		
		
		
		if($aRun['pro'] == true){
			$query = "
				SELECT * 
				FROM rank_stage_pro
				WHERE as_of_date=:as_of_date AND exclude IS NULL
				ORDER BY AAR DESC 
			";
			try{
				$rsStage = $mLink->prepare($query);
				$aValues = array(
					':as_of_date'	=> $asOfDate
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsStage->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			echo $error;
			
			while($stage = $rsStage->fetch(PDO::FETCH_ASSOC)){
				
				$aRank[$stage['period']][] = $stage;
				
			}//end while
			
			#delete previous period rankings
			$query = "
				DELETE FROM rank_pub_pro WHERE as_of_date=:asOfDate
			";
			try{
				$rsDelete = $mLink->prepare($query);
				$aValues = array(
					':asOfDate'	=> $asOfDate
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsDelete->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			echo $preparedQuery;
			
			#loop through each rank and publish to the rank table
			
			foreach($aRank as $period=>$aRanks){
				foreach($aRanks as $rank=>$fundData){
					
					
						
					if($rank == 0){
						unset($aRank[$period][$rank]);	
					}
					
					if($rank != 0){
						$query = "
							INSERT INTO rank_pub_pro (
								member_id,
								fund_id,
								rank,
								as_of_timestamp,
								as_of_date,
								AAR,
								period,
								composite,
								timestamp
							)VALUES(
								:member_id,
								:fund_id,
								:rank,
								:as_of_timestamp,
								:as_of_date,
								:AAR,
								:period,
								:composite,
								unix_timestamp()
							)
							
						";
						try{
							$rsInsert = $mLink->prepare($query);
							$aValues = array(
								':member_id'		=> $fundData['member_id'],
								':fund_id'			=> $fundData['fund_id'],
								':rank'				=> $rank,
								':as_of_timestamp'	=> $fundData['as_of_timestamp'],
								':as_of_date'		=> $fundData['as_of_date'],
								':AAR'				=> $fundData['AAR'],
								':period'			=> $fundData['period'],
								':composite'		=> $fundData['composite']
							);
							$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
							$rsInsert->execute($aValues);
						}
						
						catch(PDOException $error){
							// Log any error
							file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
						}
						//echo $preparedQuery.' <br />';
						echo $error;	
					}
				}//end foreach aRank
			}//end foreach rank insert
			
			echo '<pre>';
			print_r($aRank);
			echo '</pre>';
			
		}//end if pro = true
		
		
		if($rankReport == true){
			
			$aBench 				= getBenchmarks($mLink, $asOfDate);	
			
			function checkBench($aBench,$value,$period){
				
				$gold 	= $aBench[$period.'-year-gold']['value'];
				$silver	= $aBench[$period.'-year-silver']['value'];
				$bronze	= $aBench[$period.'-year-bronze']['value'];
				
				
				if($value>$gold){
					$return = 'gold-'.$gold;	
				}elseif($value < $gold && $value > $silver){
					$return = 'silver-'.$silver;
				}elseif($value < $silver && $value > $bronze){
					$return	= 'bronze-'.$bronze;
				}else{
					$return = $period;
				}
				
				return($return);
				
			}
			
			//echo 'start';
			//echo checkBench($aBench, 10, '10');
			
			$query = "
				SELECT m.name_first, m.name_last, m.username, f.fund_symbol, f.fund_name, rpp.*
				FROM rank_pub_pro rpp, members m, members_fund f
				WHERE rpp.member_id=m.member_id AND rpp.fund_id=f.fund_id AND rpp.as_of_date=:as_of_date
			";
			try{
				$rsGet = $mLink->prepare($query);
				$aValues = array(
					':as_of_date'		=> $asOfDate
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGet->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			//echo $preparedQuery;
			$aReport = array();
			
			while($rank = $rsGet->fetch(PDO::FETCH_ASSOC)){
				
				$aReport[$rank['fund_id']]['member_id']			= $rank['member_id'];
				$aReport[$rank['fund_id']]['username']			= $rank['username'];
				$aReport[$rank['fund_id']]['name_first']		= $rank['name_first'];
				$aReport[$rank['fund_id']]['name_last']			= $rank['name_last'];
				$aReport[$rank['fund_id']]['fund_symbol']		= $rank['fund_symbol'];
				$aReport[$rank['fund_id']]['fund_name']			= $rank['fund_name'];
				$aReport[$rank['fund_id']]['composite']			= $rank['composite'];
				
				
				$period = $rank['period'];
				
				switch($period){
                                        case '15':
                                                $aReport[$rank['fund_id']]['aar_15'] = $rank['AAR'];
                                                $aReport[$rank['fund_id']]['rank_15'] = $rank['rank'];
                                                $aReport[$rank['fund_id']]['bench_15'] = checkBench($aBench,$rank['AAR'],'15');
                                        break;

					case '10':
						$aReport[$rank['fund_id']]['aar_10'] = $rank['AAR'];
						$aReport[$rank['fund_id']]['rank_10'] = $rank['rank'];
						$aReport[$rank['fund_id']]['bench_10'] = checkBench($aBench,$rank['AAR'],'10');
					break;
					
					case '5':
						$aReport[$rank['fund_id']]['aar_5'] = $rank['AAR'];
						$aReport[$rank['fund_id']]['rank_5'] = $rank['rank'];
						$aReport[$rank['fund_id']]['bench_5'] = checkBench($aBench,$rank['AAR'],'5');
					break;
					
					case '3':
						$aReport[$rank['fund_id']]['aar_3'] = $rank['AAR'];
						$aReport[$rank['fund_id']]['rank_3'] = $rank['rank'];
						$aReport[$rank['fund_id']]['bench_3'] = checkBench($aBench,$rank['AAR'],'3');
					break;
					
					case '1':
						$aReport[$rank['fund_id']]['aar_1'] = $rank['AAR'];
						$aReport[$rank['fund_id']]['rank_1'] = $rank['rank'];
						$aReport[$rank['fund_id']]['bench_1'] = checkBench($aBench,$rank['AAR'],'1');
					break;	
				}//end switch
					
			}//end while
			
			#delete rank report
			$query = "
				DELETE FROM rank_report_pro WHERE as_of_date=:as_of_date
			";
			try{
				$rsDelete = $mLink->prepare($query);
				$aValues = array(
					':as_of_date'		=> $asOfDate,
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsDelete->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			//echo $preparedQuery;
			//echo $error;
			
			foreach($aReport as $fundID => $report){
				
				$query = "
					INSERT INTO rank_report_pro (
						member_id,
						fund_id,
						as_of_date,
						composite,
						username,
						name_first,
						name_last,
						fund_symbol,
						fund_name,
						aar_15,
						rank_15,
						bench_15,
						aar_10,
						rank_10,
						bench_10,
						aar_5,
						rank_5,
						bench_5,
						aar_3,
						rank_3,
						bench_3,
						aar_1,
						rank_1,
						bench_1
					)VALUES(
						:member_id,
						:fund_id,
						:as_of_date,
						:composite,
						:username,
						:name_first,
						:name_last,
						:fund_symbol,
						:fund_name,
                                                :aar_15,
                                                :rank_15,
                                                :bench_15,
						:aar_10,
						:rank_10,
						:bench_10,
						:aar_5,
						:rank_5,
						:bench_5,
						:aar_3,
						:rank_3,
						:bench_3,
						:aar_1,
						:rank_1,
						:bench_1
					)
				";
				try{
					$rsInsert = $mLink->prepare($query);
					$aValues = array(
						':member_id'		=> $report['member_id'],
						':fund_id'			=> $fundID,
						':as_of_date'		=> $asOfDate,
						':composite'		=> $report['composite'],
						':username'			=> $report['username'],
						':name_first'		=> $report['name_first'],
						':name_last'		=> $report['name_last'],
						':fund_symbol'		=> $report['fund_symbol'],
						':fund_name'		=> $report['fund_name'],
                                                ':aar_15'                       => $report['aar_15'],
                                                ':rank_15'                      => $report['rank_15'],
                                                ':bench_15'                     => $report['bench_15'],
						':aar_10'			=> $report['aar_10'],
						':rank_10'			=> $report['rank_10'],
						':bench_10'			=> $report['bench_10'],
						':aar_5'			=> $report['aar_5'],
						':rank_5'			=> $report['rank_5'],
						':bench_5'			=> $report['bench_5'],
						':aar_3'			=> $report['aar_3'],
						':rank_3'			=> $report['rank_3'],
						':bench_3'			=> $report['bench_3'],
						':aar_1'			=> $report['aar_1'],
						':rank_1'			=> $report['rank_1'],
						':bench_1'			=> $report['bench_1']
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsInsert->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				//echo '<pre>';
				//print_r($aReport);
				//echo '</pre>';
				//echo $preparedQuery.'<br />';	
				
				
			}
			
		}//end if rank report

		
		echo $asOfDate;
	
	break;

#--------------------------------------------------------------------------------------------------------------------#
	#											BUILD CSV
	#--------------------------------------------------------------------------------------------------------------------#
	case 'rank-csv':	
		
		$query = "
			SELECT m.name_first, m.name_last, m.username, f.fund_symbol, f.fund_name, rpp.*
			FROM rank_pub_pro rpp, members m, members_fund f
			WHERE rpp.member_id=m.member_id AND rpp.fund_id=f.fund_id AND rpp.as_of_date
		";
		
	break;
	#--------------------------------------------------------------------------------------------------------------------#
	#											BUILD STAGE TABLES
	#--------------------------------------------------------------------------------------------------------------------#
	case 'build-stage-tables':
		
		//$aMemberTypes				= array(10);//,2,3,4,11
		
		
		$asOfDate					= $_REQUEST['asOfDate'];
		$asOfTimestamp				= $_REQUEST['as_of_timestamp'];
		$aRun['pro']				= $_REQUEST['run_pro'] == 1 ? true : false;
		$aRun['paid-pro']			= $_REQUEST['run_paid_pro'] == 1 ? true : false;
		$aRun['community']			= $_REQUEST['run_community'] == 1 ? true : false;
		
		if($aRun['pro'] == true){
			$aRankings['pro'] 			= array(10=>array('xx'=>array('aar'=>1000)),5=>array('xx'=>array('aar'=>1000)),3=>array('xx'=>array('aar'=>1000)),1=>array('xx'=>array('aar'=>1000)));
		}
		if($aRun['paid-pro'] == true){
			$aRankings['paid-pro'] 		= array(10=>array('xx'=>array('aar'=>1000)),5=>array('xx'=>array('aar'=>1000)),3=>array('xx'=>array('aar'=>1000)),1=>array('xx'=>array('aar'=>1000)));;
		}
		if($aRun['community'] == true){
			$aRankings['community'] 	= array(10=>array('xx'=>array('aar'=>1000)),5=>array('xx'=>array('aar'=>1000)),3=>array('xx'=>array('aar'=>1000)),1=>array('xx'=>array('aar'=>1000)));;
		}
		
		$aPeriods = array(15,10,5,3,1);
		
		#select all eligible funds
//		$query = "
//			SELECT ra.*, ms.product_id
//			FROM rank_aar ra, members_subscriptions ms
//			WHERE ra.member_id=ms.member_id AND ra.eligible='1' AND ra.as_of_date=:asOfDate AND ms.product_type='membership' AND ms.active='1'
//			
//		";
                $query = "
                        SELECT ra.*, ms.product_id
                        FROM rank_aar ra, members_subscriptions ms
                        WHERE ra.member_id=ms.member_id AND ra.eligible='1' AND ra.as_of_date=:asOfDate AND ms.folio='1'

                ";
		try{
			$rsAAR = $mLink->prepare($query);
			$aValues = array(
				':asOfDate'	=> $asOfDate
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsAAR->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		echo $preparedQuery;
		while($record = $rsAAR->fetch(PDO::FETCH_ASSOC)){
			
			#set vars
			$memberID 			= $record['member_id'];
			$fundID				= $record['fund_id'];
			$productID			= $record['product_id'];
			$compositeFund		= $record['composite'];
			$isPaid				= false;
			
                        $aData['comp'][15]      = $record['fifteenYearCompliant'];
			$aData['comp'][10]	= $record['tenYearCompliant'];
			$aData['comp'][5]	= $record['fiveYearCompliant'];
			$aData['comp'][3]	= $record['threeYearCompliant'];
			$aData['comp'][1]	= $record['oneYearCompliant'];
			
                        $aData['aar'][15]       = $record['fifteenYearAAR'];
			$aData['aar'][10]	= $record['tenYearAAR'];
			$aData['aar'][5]	= $record['fiveYearAAR'];
			$aData['aar'][3]	= $record['threeYearAAR'];
			$aData['aar'][1]	= $record['oneYearAAR'];
			
			if($productID == 10){
				$manager = 'yes';
				if($compositeFund == 'no'){
					continue;	
				}
				
			}elseif($productID == 1 || $productID == 0){
				continue;
			}elseif(in_array($productID,$aMemberTypes)){
				$manager = 'no';
			}else{
				$manager = 'no';
				continue;
			}
			
			#check if paid member
			if($productID >= 2 && $productID <= 11){
				$isPaid = true;
				
				$isPaidCnt++;
			}else{
				$isPaid = false;
			}//end if paid member
			
			#loop through each period
			foreach($aPeriods as $key=>$period){
				
				if($aData['aar'][$period] != NULL){
				
					if($aData['comp'][$period] == 1 || $manager == 'yes'){
						
						#paid pro rankings
						if($aRun['paid-pro'] == true){
							if($isPaid == true){
								$aRankings['paid-pro'][$period][$fundID] = array(
									'aar'				=> $aData['aar'][$period],
									'member_id'			=> $memberID,
									'fund_id'			=> $fundID,
									'composite_fund'	=> $compositeFund
								);
							}
						}
						
						#pro rankings
						if($aRun['pro'] == true){
							$aRankings['pro'][$period][$fundID] = array(
								'aar'				=> $aData['aar'][$period],
								'member_id'			=> $memberID,
								'fund_id'			=> $fundID,
								'composite_fund'	=> $compositeFund
							);
						}
						
					}//end compliant
					
					#community rankings
					if($aRun['community'] == true){
						$aRankings['community'][$period][$fundID] = array(
							'aar'				=> $aData['aar'][$period],
							'member_id'			=> $memberID,
							'fund_id'			=> $fundID,
							'composite_fund'	=> $compositeFund
						);
					}
					
				}//end period
					
			}//end foreach period
	
		}//end query
		
		#sort ranks by aar desc		
		function sortMe($a, $b) {
			if ($a['aar'] == $b['aar']) {
				return 0;
			}
			return ($a['aar'] < $b['aar']) ? 1 : -1;
		} 
		
		#pro
		if($aRun['pro'] == true){
                        usort($aRankings['pro'][15], 'sortMe');
                        unset($aRankings['pro'][15][0]);
			usort($aRankings['pro'][10], 'sortMe');
			unset($aRankings['pro'][10][0]);
			usort($aRankings['pro'][5], 'sortMe');
			unset($aRankings['pro'][5][0]);
			usort($aRankings['pro'][3], 'sortMe');
			unset($aRankings['pro'][3][0]);
			usort($aRankings['pro'][1], 'sortMe');
			unset($aRankings['pro'][1][0]);
		}
		
		#paid-pro
		if($aRun['paid-pro'] == true){
                        usort($aRankings['paid-pro'][15], 'sortMe');
                        unset($aRankings['paid-pro'][15][0]);
			usort($aRankings['paid-pro'][10], 'sortMe');
			unset($aRankings['paid-pro'][10][0]);
			usort($aRankings['paid-pro'][5], 'sortMe');
			unset($aRankings['paid-pro'][5][0]);
			usort($aRankings['paid-pro'][3], 'sortMe');
			unset($aRankings['paid-pro'][3][0]);
			usort($aRankings['paid-pro'][1], 'sortMe');
			unset($aRankings['paid-pro'][1][0]);
		}
		
		#community
		if($aRun['community'] == true){
                        usort($aRankings['community'][15], 'sortMe');
                        unset($aRankings['community'][15][0]);
			usort($aRankings['community'][10], 'sortMe');
			unset($aRankings['community'][10][0]);
			usort($aRankings['community'][5], 'sortMe');
			unset($aRankings['community'][5][0]);
			usort($aRankings['community'][3], 'sortMe');
			unset($aRankings['community'][3][0]);
			usort($aRankings['community'][1], 'sortMe');
			unset($aRankings['community'][1][0]);
		}
		
		foreach($aRankings as $rankGroup=>$aPeriods){
			
			switch($rankGroup){
				case 'pro'			: $stageTable = 'rank_stage_pro';break;
				case 'paid-pro'		: $stageTable = 'rank_stage_paid_pro';break;
				case 'community'	: $stageTable = 'rank_stage_community';break;
			}
			
			if($aRun[$rankGroup] == true){
				
				#delete old data
				$query = "
					DELETE FROM ".$stageTable."
					WHERE as_of_date=:as_of_date
				";
				try{
					$rsDelete = $mLink->prepare($query);
					$aValues = array(
						':as_of_date'		=> $asOfDate
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsDelete->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				
				
				foreach($aPeriods as $period=>$aRanks){
					
					
					
					foreach($aRanks as $rank=>$fundInfo){
						
						
						$query = "
							INSERT INTO ".$stageTable." (
								member_id,
								fund_id,
								rank,
								as_of_timestamp,
								as_of_date,
								AAR,
								period,
								composite,
								timestamp
							)VALUES(
								:member_id,
								:fund_id,
								:rank,
								:as_of_timestamp,
								:as_of_date,
								:AAR,
								:period,
								:composite,
								UNIX_TIMESTAMP()
							)
						";
						try{
							$rsInsertRank = $mLink->prepare($query);
							$aValues = array(
								':member_id'		=> $fundInfo['member_id'],
								':fund_id'			=> $fundInfo['fund_id'],
								':rank'				=> $rank,
								':as_of_timestamp'	=> $asOfTimestamp,
								':as_of_date'		=> $asOfDate,
								':AAR'				=> $fundInfo['aar'],
								':period'			=> $period,
								':composite'		=> $fundInfo['composite_fund']
								
							);
							$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
							$rsInsertRank->execute($aValues);
						}
						
						catch(PDOException $error){
							// Log any error
							file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
						}
				
						
							$aDebug['insert'][] = array(
								'error' => $error,
								'query'	=> $preparedQuery
							);
						
					}//end foreach rank
					
				}//end foreach period
				
			}//end if run rank group
				
		}//end foreach rankings
		
		echo '<pre>';
                echo 'Paid Pro 15: '.count($aRankings['paid-pro'][15]).'<br />';
		echo 'Paid Pro 10: '.count($aRankings['paid-pro'][10]).'<br />'; 
		echo 'Paid Pro 5: '.count($aRankings['paid-pro'][5]).'<br />'; 
		echo 'Paid Pro 3: '.count($aRankings['paid-pro'][3]).'<br />'; 
		echo 'Paid Pro 1: '.count($aRankings['paid-pro'][1]).'<br />'; 
                echo 'Pro 15: '.count($aRankings['pro'][15]).'<br />';
		echo 'Pro 10: '.count($aRankings['pro'][10]).'<br />'; 
		echo 'Pro 5: '.count($aRankings['pro'][5]).'<br />'; 
		echo 'Pro 3: '.count($aRankings['pro'][3]).'<br />'; 
		echo 'Pro 1: '.count($aRankings['pro'][1]).'<br />'; 
                echo 'Community 15: '.count($aRankings['community'][15]).'<br />';
		echo 'Community 10: '.count($aRankings['community'][10]).'<br />'; 
		echo 'Community 5: '.count($aRankings['community'][5]).'<br />'; 
		echo 'Community 3: '.count($aRankings['community'][3]).'<br />'; 
		echo 'Community 1: '.count($aRankings['community'][1]).'<br />'; 
		print_r($aDebug);
		echo '</pre>';
	
	break;
	
	case 'calc-outliers':
		
// Stop this immediately!
break;
		$startTime = microtime();
		
		$asOfDate 	= $_REQUEST['asOfDate'];
		$threshold	= 20;
		
		$reprice 	= $_REQUEST['reprice'];
		$apiServer	= $_REQUEST['api-server'];
		
		$start = strtotime('2000-05-01');
		$end = strtotime('2017-03-31');
		$month = $start;
		$months[] = date('Ymt', $start);
		while($month <= $end) {
		  $month = strtotime("+1 month", $month);
		  $months[] = date('Ymt', $month);
		}
		
		
		switch($apiServer){
			case 'api1': $port = rand(52000, 52099);$apiType = '1';break;
			case 'api2': $port = rand(52100, 52199);$apiType = '2';break;
                        case 'api3': $port = rand(52500, 52699);$apiType = '3';break;
		}
	
		
		if($reprice == '1'){
			#reprice anomalies
			
			
			$query = "
				SELECT ra.*, m.username, mf.fund_symbol
				FROM (SELECT ra2.* FROM rank_anomalies as ra2 ORDER BY ra2.anomaly_timestamp ASC) AS ra , members m, members_fund mf
				WHERE ra.as_of_date=:as_of_date AND m.member_id=ra.member_id AND mf.fund_id=ra.fund_id AND eligible='1'
				GROUP BY ra.fund_id
				ORDER BY uid ASC
			";
			try{
				$rsGet = $mLink->prepare($query);
				$aValues = array(
					':as_of_date'	=> $asOfDate
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGet->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			echo $preparedQuery;
			
			echo $error;
			
			
			while($anom = $rsGet->fetch(PDO::FETCH_ASSOC)){
				$aMethodVars = array();
				//echo 'here';
				
				$fromDate 		= strtotime('-5 day', $anom['anomaly_timestamp']);
				$username		= $anom['username'];
				$fundID			= $anom['fund_id'];
				$fundSymbol		= $anom['fund_symbol'];
				
				//echo $fromDate.' - '.date('Y-m-d', $fromDate).' - '.$anom['anomaly_timestamp'].' - '.date('Y-m-d', $anom['anomaly_timestamp']).'<br />';
				
				#delete old pricing records
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
				
				#Remove existing trade data
				$deleteTradesQuery = "
					DELETE FROM members_fund_trades
					WHERE fund_id=:fund_id AND unix_close>=:target_date
				";
				try{
					$rsDelete2 = $mLink->prepare($deleteTradesQuery);
					$aValues = array(
						':fund_id' 		=> $fundID,
						':target_date'	=> $fromDate
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsDelete2->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				
				
				$aMethodVars[] = array(
					'method'		=> 'tradesForFund',
					'source'		=> 'Admin Ranking Script | admin-rankings-processes.php | process: calculate-anomalies',
					'api'			=> $apiType,
					'port'			=> '',
					'username'		=> $username,
					'fund_id'		=> $fundID,
					'fund_symbol'	=> $fundSymbol,
					'from_date'		=> $repriceFrom,
					'start_date'	=> '',
					'end_date'		=> '',
					'group'			=> date('Ymd-h.i', time()).'-reprice-fund'
				);
				
				#build reprice requests from anomaly date
				
				
				$aPriceDates = priceRunDateArray(0, time(), $fromDate, 0);
				
				/*echo '<pre>';
				print_r($aPriceDates);
				echo '</pre>';*/
				
				foreach($aPriceDates as $key=>$aDate){
			
					$start 	= date('Ymd', $aDate['start']);
					$end	= date('Ymd', $aDate['end']);
					
					$aMethodVars[] = array(
						'method'		=> 'priceRun',
						'source'		=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund',
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
		
						
				}//end foreach aPriceDates
				
				$mlaResults = legacy_api($mLink, $aMethodVars, true);
				
				echo '<pre>';
				print_r($mlaResults);
				echo '</pre';
				
				unset($aMethodVars);	
			}//end while loop
			
		}else{
			#calculate anomalies
			$query = "
				DELETE FROM rank_anomalies
				WHERE as_of_date=:asOfDate
			";
			try{
				$rsDelete = $mLink->prepare($query);
				$aValues = array(
					':asOfDate'		=> $asOfDate
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsDelete->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$aDebug[0]['preparedQuery'] = $preparedQuery;
			$aDebug[0]['error']			= $error;
		
			
			$query = "
				SELECT fund_id, member_id, as_of_date, eligible
				FROM rank_aar
				WHERE as_of_date=:asOfDate 
			";
			
			/*$query = "
				SELECT fund_id, `date`, unix_date, price
				FROM members_fund_pricing
				WHERE `date` IN (".implode(',',$months).")
				ORDER BY unix_date ASC
			";*/
			try{
				$rsGetData = $mLink->prepare($query);
				$aValues = array(
					':asOfDate'		=> $asOfDate
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetData->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$aDebug[1]['preparedQuery'] = $preparedQuery;
			$aDebug[1]['error']			= $error;
			while($data = $rsGetData->fetch(PDO::FETCH_ASSOC)){
				
				$fundID 		= $data['fund_id'];
				$memberID		= $data['member_id'];
				$eligible		= $data['eligible'];
				
				$query = "
					SELECT fund_id, `date`, unix_date, price
					FROM members_fund_pricing
					WHERE `date` IN (".implode(',',$months).") AND fund_id=:fund_id
					ORDER BY unix_date ASC
				";
				try{
					$rsGetPricing = $mLink->prepare($query);
					$aValues = array(
						':fund_id'		=> $fundID
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsGetPricing->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				$aDebug[2]['preparedQuery'] = $preparedQuery;
				$aDebug[2]['error']			= $error;
				
				$loopCnt = 0;
				unset($lastPrice);
				
				while($pricing = $rsGetPricing->fetch(PDO::FETCH_ASSOC)){
					
					$loopCnt++;
					
					$price 		= $pricing['price'];
					$date		= $pricing['date'];
					$unixDate	= $pricing['unix_date'];
					
					#skip check for first loop
					if($loopCnt > 1){
						
						$percentChange = (($price - $lastPrice)/$lastPrice)*100;
						
						#get month data if month end return is higher than threshold
						if($percentChange > $threshold){
							
							$lookupMonth = date('Ym', $unixDate).'%';
							
							$query = "
								SELECT fund_id, `date`, unix_date, price
								FROM members_fund_pricing
								WHERE `date` LIKE '".$lookupMonth."' AND fund_id=:fund_id
								ORDER BY unix_date ASC
							";
							try{
								$rsSubPrice = $mLink->prepare($query);
								$aValues = array(
									':fund_id'		=> $fundID
								);
								$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
								$rsSubPrice->execute($aValues);
							}
							
							catch(PDOException $error){
								// Log any error
								file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
							}
							$aDebug[3]['preparedQuery'] = $preparedQuery;
							$aDebug[3]['error']			= $error;
							
							$subLoopCnt = 0;
							unset($subLastPrice);
							
							while($subPricing = $rsSubPrice->fetch(PDO::FETCH_ASSOC)){
								
								$subLoopCnt++;
								
								$subPrice 		= $subPricing['price'];
								$subDate		= $subPricing['date'];
								$subUnixDate	= $subPricing['unix_date'];
								
								#skip check for first loop
								if($subLoopCnt > 1){
									
									$subPercentChange = (($subPrice - $subLastPrice)/$subLastPrice)*100;
									
									#get month data if month end return is higher than threshold
									if($subPercentChange > $threshold){
									
										$aAnomalies[$memberID][$fundID][$subDate] = array(
											'percentChange'		=> $subPercentChange,
											'price'				=> $subPrice,
											'prevPrice'			=> $subLastPrice,
											'eligible'			=> $eligible
										);
										
										
									}//end subPercentChange
									
								}//end subLoopCnt
								
								$subLastPrice = $subPrice;
								
							}//end sub while loop
							
						}//end > threshold
						
						
					}//end if loop count > 1
					
					#set last price for comparison
					$lastPrice = $price;
					
				}//end while loop
				
				//$aPriceData[$data['fund_id']][$data['date']] = $data['price'];
				
			}
			
			foreach($aAnomalies as $memberID=>$aFundIDs){
				
				foreach($aFundIDs as $fundID=>$aAnomaly){
					
					foreach($aAnomaly as $anomalyDate=>$anomalyInfo){
						
							$percentChange 	= $anomalyInfo['percentChange'];
							$price 			= $anomalyInfo['price'];
							$prevPrice 		= $anomalyInfo['prevPrice'];
							$eligible		= $anomalyInfo['eligible'];
						
							$anomalyTimestmap = mktime(8,0,0,substr($anomalyDate,4,2),substr($anomalyDate,6,2),substr($anomalyDate,0,4));
										
							$query = "
								INSERT INTO rank_anomalies(
									member_id,
									fund_id,
									eligible,
									as_of_date,
									as_of_timestamp,
									anomaly_date,
									anomaly_timestamp,
									percent_change,
									price,
									prev_price
								)VALUES(
									:member_id,
									:fund_id,
									:elibible,
									:as_of_date,
									:as_of_timestamp,
									:anomaly_date,
									:anomaly_timestamp,
									:percent_change,
									:price,
									:prev_price
								)
							";
							try{
								$rsInsert = $mLink->prepare($query);
								$aValues = array(
									':member_id'			=> $memberID,
									':fund_id'				=> $fundID,
									':elibible'				=> $eligible,
									':as_of_date'			=> $asOfDate,
									':as_of_timestamp'		=> $asOfTimestamp,
									':anomaly_date'			=> $anomalyDate,
									':anomaly_timestamp'	=> $anomalyTimestmap,
									':percent_change'		=> $percentChange,
									':price'				=> $price,
									':prev_price'			=> $prevPrice
								);
								$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
								$rsInsert->execute($aValues);
							}
							
							catch(PDOException $error){
								// Log any error
								file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
							}
							$aDebug[4]['preparedQuery'] = $preparedQuery;
							$aDebug[4]['error']			= $error;
							
					}
					
				}
					
			}
			
			$endTime = microtime();
			
			$timeDiff = $endTime - $startTime;
			
			echo '<p>Process Time: '.$timeDiff.' ms</p>';
			
			echo '<pre>';
			print_r($aAnomalies);
			print_r($aDebug);
			echo '</pre>';	
		}//end calculate anomalies
		
//	break;
	
	#--------------------------------------------------------------------------------------------------------------------#
	#											CALCULATE OUTLIERS
	#--------------------------------------------------------------------------------------------------------------------#
	case 'anom-reprice':
		
		$asOfDate = $_REQUEST['as_of_date'];
		
		
	
	break;
	
	#--------------------------------------------------------------------------------------------------------------------#
	#											CALCULATE OUTLIERS
	#--------------------------------------------------------------------------------------------------------------------#
	case 'calc-outliers2':
		
		
		$start = strtotime('2000-05-01');
		$end = strtotime('2017-03-31');
		$month = $start;
		$months[] = date('Ymt', $start);
		while($month <= $end) {
		  $month = strtotime("+1 month", $month);
		  $months[] = date('Ymt', $month);
		}
		
		function Median($Array) {
			return Quartile_50($Array);
		}
		 
		function Quartile_25($Array) {
			return Quartile($Array, 0.25);
		}
		 
		function Quartile_50($Array) {
			return Quartile($Array, 0.5);
		}
		 
		function Quartile_75($Array) {
			return Quartile($Array, 0.75);
		}
		 
		function Quartile($Array, $Quartile) {
			
			
			$pos = (count($Array) - 1) * $Quartile;
		 
			$base = floor($pos);
			$rest = $pos - $base;
			
			if( isset($Array[$base+1]) ) {
				return $Array[$base] + $rest * ($Array[$base+1] - $Array[$base]);
			} else {
				return $Array[$base];
			}
		}
		 
		function Average($Array) {
			
		  	return array_sum($Array) / count($Array);
		}
		 
		function StdDev($Array) {
			
			if( count($Array) < 2 ) {
				return;
			}
			
			$avg = Average($Array);
			
			$sum = 0;
			foreach($Array as $value) {
				$sum += pow($value - $avg, 2);
			}
			
			return sqrt((1 / (count($Array) - 1)) * $sum);
		}
		
		#vars
		$asOfDate						= $_REQUEST['asOfDate'];
		$aPeriods 						= array(15,10,5,3,1);
		$aTen = array();
		
		$countRecords = 0;
		
		$query = "
			SELECT *
			FROM rank_aar
			WHERE as_of_date=:asOfDate AND eligible='1' AND tenYearAAR IS NOT NULL AND tenYearCompliant='1'
		";
		try{
			$rsRawNAV = $mLink->prepare($query);
			$aValues = array(
				':asOfDate'	=> $asOfDate
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsRawNAV->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		echo $preparedQuery;
		while($record = $rsRawNAV->fetch(PDO::FETCH_ASSOC)){
			
			
			#set vars
			$memberID 			= $record['member_id'];
			$fundID				= $record['fund_id'];
			$productID			= $record['product_id'];
			$isPaid				= false;
			
                        $aData['comp'][15]      = $record['fifteenYearCompliant'];
			$aData['comp'][10]	= $record['tenYearCompliant'];
			$aData['comp'][5]	= $record['fiveYearCompliant'];
			$aData['comp'][3]	= $record['threeYearCompliant'];
			$aData['comp'][1]	= $record['oneYearCompliant'];
			
                        $aData['aar'][15]       = $record['fifteenYearAAR'];
			$aData['aar'][10]	= $record['tenYearAAR'];
			$aData['aar'][5]	= $record['fiveYearAAR'];
			$aData['aar'][3]	= $record['threeYearAAR'];
			$aData['aar'][1]	= $record['oneYearAAR'];
			
			#check if paid member
			if($productID >= 2 && $productID <= 11){
				$isPaid = true;
				
				$isPaidCnt++;
			}else{
				$isPaid = false;
			}//end if paid member
			
			
			
			#loop through each period
			foreach($aPeriods as $key=>$period){
				
				if($aData['aar'][$period] != NULL){
				
					$aRankings['all'][$period][$fundID] = array(
						'aar'			=> $aData['aar'][$period],
						'member_id'		=> $memberID,
						'fund_id'		=> $fundID
					);
					
					if($aData['comp'][$period] == '1'){
						//echo $aData['aar'][$period];
						
						$aTen[$period][] = $aData['aar'][$period];
							
					}
					/*if($aData['comp'][$period] == 1){
						
						
						
						#paid pro rankings
						if($aRun['paid-pro'] == true){
							if($isPaid == true){
								$aRankings['paid-pro'][$period][$fundID] = array(
									'aar'			=> $aData['aar'][$period],
									'member_id'		=> $memberID,
									'fund_id'		=> $fundID
								);
							}
						}
						
						#pro rankings
						if($aRun['pro'] == true){
							$aRankings['pro'][$period][$fundID] = array(
								'aar'			=> $aData['aar'][$period],
								'member_id'		=> $memberID,
								'fund_id'		=> $fundID
							);
						}
						
					}//end compliant
					
					#community rankings
					if($aRun['community'] == true){
						$aRankings['community'][$period][$fundID] = array(
							'aar'			=> $aData['aar'][$period],
							'member_id'		=> $memberID,
							'fund_id'		=> $fundID
						);
					}*/
					
				}//end period
					
			}//end foreach period
			
		}//end while loop
		
		
		$aTen = $aTen[10];
		
		sort($aTen);
		
		$oneQR 		= Quartile($aTen, .25);
		$twoQR 		= Quartile($aTen, .50);
		$threeQR	= Quartile($aTen, .75);
		
		echo '<pre>';
		echo 'Count: '.count($aTen).'<br />';
		echo '1QR: '.Quartile($aTen, .25).'<br />';
		echo '2QR: '.Quartile($aTen, .50).'<br />';
		echo '3QR: '.Quartile($aTen, .75).'<br />';
		
		echo 'Average: '.Average($aTen).'<br />';
		echo 'Standard Deviation: '.StdDev($aTen).'<br />';
		
		print_r(implode(',',$months));
		
		print_r($aTen);
		//print_r($aRankings);
		
		echo '</pre>';
		
	break;
	
	#--------------------------------------------------------------------------------------------------------------------#
	#											SAVE BENCHMARKS
	#--------------------------------------------------------------------------------------------------------------------#
	case 'save-benchmarks':
		
		$asOfDate			= $_REQUEST['asOfDate'];
		$aProcessNames		= explode(',',$_REQUEST['process-names']);
		$aBadge				= getBadgeInfo($mLink, NULL);
		
		foreach($aBadge as $badgeID=>$badgeInfo){
			$aNewBadge[$badgeInfo['type']] = $badgeInfo['desc'];	
		}
		
		$query = "
			DELETE FROM system_benchmarks WHERE period=:period
		";
		try{
			$rsDelete = $mLink->prepare($query);
			$aValues = array(
				':period'	=> $asOfDate
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsDelete->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		foreach($aProcessNames as $key=>$benchmark){
			
			$benchValue = $_REQUEST[$benchmark];
			$note		= $aNewBadge[$benchmark];
			
			
			
			$query = "
				INSERT INTO system_benchmarks (
					type,
					sub_type,
					value,
					note,
					period,
					timestamp
				)VALUES(
					:type,
					'badge',
					:value,
					:note,
					:period,
					UNIX_TIMESTAMP()
				)
			";
			try{
				$rsInsert = $mLink->prepare($query);
				$aValues = array(
					':type'		=> $benchmark,
					':value'	=> $benchValue,
					':note'		=> $note,
					':period'	=> $asOfDate
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsInsert->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			//echo $preparedQuery;
		}
		
	break;
	
	#--------------------------------------------------------------------------------------------------------------------#
	#											SAVE NOTE
	#--------------------------------------------------------------------------------------------------------------------#
	case 'save-note':
		
		$asOfDate		= $_REQUEST['asOfDate'];
		$note 			= $_REQUEST['note'];
		$asOfTimestamp	= $_REQUEST['as_of_timestamp'];
		$fundID			= $_REQUEST['fund_id'];
		
		$query = "
			INSERT INTO rank_notes (
				fund_id,
				created_by,
				as_of_date,
				as_of_timestamp,
				note,
				note_date,
				note_timestamp
			)VALUES(
				:fund_id,
				:created_by,
				:as_of_date,
				:as_of_timestamp,
				:note,
				:note_date,
				UNIX_TIMESTAMP()
			)
		";
		try{
			$rsInsert = $mLink->prepare($query);
			$aValues = array(
				':fund_id'			=> $fundID,
				':created_by'		=> $_SESSION['member_id'],
				':as_of_date'		=> $asOfDate,
				':as_of_timestamp'	=> $asOfTimestamp,
				':note'				=> $note,
				':note_date'		=> date('Ymd')
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsInsert->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		echo $error;
		
		
		echo "<h4>All Notes</h4>";
		$query = "
			SELECT rn.*, m.name_first, m.name_last
			FROM rank_notes rn
			LEFT JOIN members m ON m.member_id=rn.created_by
			WHERE rn.fund_id=:fund_id AND rn.deleted IS NULL
		";
		try{
			$rsGetNotes = $mLink->prepare($query);
			$aValues = array(
				':fund_id'			=> $fundID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetNotes->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		while($allNotes = $rsGetNotes->fetch(PDO::FETCH_ASSOC)){
			
			$createdBy 	= $allNotes['name_first'].' '.$allNotes['name_last'];
			$note		= $allNotes['note'];
			
			
			?>
            <div class="response-container" <?php echo $addCSS;?>>
                <div class="response-header" style="background:#414247;">
                    <div class="responder" style="float:left;">
                        Note by: <?php echo $createdBy; ?>
                    </div><!--responder-->
                    <div class="response-date">
                        
                        <?php echo date('D m/d/Y', $reply['note_timestamp']);?> at <?php echo date('h:i A', $reply['note_timestamp']);?> <?php echo $showBtn;?>
                    </div><!--response-date-->
                    <div class="clear"></div>
                </div><!--response-header-->
                
                <div class="response-body">
                    <?php echo $note;?>
                    <p class="last"><?php echo $response; ?></p>
                </div><!--response-body-->
                
                <div class="response-header" style="background:#414247;">
                    <div class="responder" style="float:left;">
                    </div><!--responder-->
                    <div class="response-date">
                        
                        Rank Period: <?php echo date('m/d/Y', $allNotes['as_of_timestamp']);?>
                    </div><!--response-date-->
                    <div class="clear"></div>
                </div><!--response-header-->
            </div>
            <?php
				
		}
		
	break;
	
	case 'load-note-form':
		
		$fundID 		= $_REQUEST['fundID'];
		$fundSymbol		= $_REQUEST['fundSymbol'];
		$manager		= $_REQUEST['manager'];
		$asOfDate		= $_REQUEST['asOfDate'];
		$asOfTimestamp	= $_REQUEST['asOfTimestamp'];
		
		?>
        <h3>Notes for FUND: <?php echo $fundSymbol;?></h3>
        
        <h4>Add Note</h4>
        <hr />
        <form method="post" class="note-form">
                            
            
            
            <div class="form-group">
            <label class="control-label">As Of Date</label>
            <input type="text" class="form-control" value="<?php echo $asOfDate;?>" name="asOfDate" />
            </div>
            
            
            <div class="form-group">
            	<label class="control-label">Note:</label>
                <textarea class="form-control" name="note"></textarea>
            </div>
            
            
            <input type="hidden" name="as_of_timestamp" value="<?php echo $asOfTimestamp;?>" />
            
            <input type="hidden" name="fund_id" value="<?php echo $fundID;?>" />
            
                                        
            <div id="note-form-submit"><input type="submit" value="Save Note" class="btn btn-success" /></div>
            
            <div id="note-form-errors"></div>
            
        </form>
        
        
        <div id="load-all-notes">
        <h4  style="margin-top:30px;">All Notes</h4>
        <hr />
        
        
        <?php
		
		$query = "
			SELECT rn.*, m.name_first, m.name_last, m.member_id
			FROM rank_notes rn
			LEFT JOIN members m ON m.member_id=rn.created_by
			WHERE rn.fund_id=:fund_id AND rn.deleted IS NULL
		";
		try{
			$rsGetNotes = $mLink->prepare($query);
			$aValues = array(
				':fund_id'			=> $fundID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetNotes->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		while($allNotes = $rsGetNotes->fetch(PDO::FETCH_ASSOC)){
			
			$createdBy 	= $allNotes['name_first'].' '.$allNotes['name_last'];
			$note		= $allNotes['note'];
			$memberID	= $allNotes['member_id'];
			
			?>
            <div class="response-container" <?php echo $addCSS;?>>
                <div class="response-header" style="background:#414247;">
                    <div class="responder" style="float:left;">
                        Note by: <?php echo $createdBy; ?>
                    </div><!--responder-->
                    <div class="response-date">
                        
                        <?php echo date('D m/d/Y', $allNotes['note_timestamp']);?> at <?php echo date('h:i A', $allNotes['note_timestamp']);?> <?php echo $showBtn;?>
                    </div><!--response-date-->
                    <div class="clear"></div>
                </div><!--response-header-->
                
                <div class="response-body">
                    <?php echo $note;?>
                    <p class="last"><?php echo $response; ?></p>
                </div><!--response-body-->
                
                <div class="response-header" style="background:#414247;">
                    <div class="responder" style="float:left;">
                    </div><!--responder-->
                    <div class="response-date">
                        
                        Rank Period: <?php echo date('m/d/Y', $allNotes['as_of_timestamp']);?>
                    </div><!--response-date-->
                    <div class="clear"></div>
                </div><!--response-header-->
            </div>
            <?php
				
		}
		
		?>
        </div>
        
       	<h4 style="margin-top:30px;">Anomalies</h4>
        <hr />
        
        <table class="table table-striped table-bordered table-hover table-full-width" id="ten_year_table2">
            <thead>
                <tr>
                    <th>Anomaly Date</th>
                    <th>Status</th>
                    <th>Prev Price</th>
                    <th>Price</th>
                    <th>% Change</th>
                    <th>Action</th>
                    <th>Go To</th>
                </tr>
            </thead>
            <tbody>
            	<?php
				$query = "
					SELECT *
					FROM rank_anomalies
					WHERE fund_id=:fund_id and as_of_date=:asOfDate
				";
				try{
					$rsAnom = $mLink->prepare($query);
					$aValues = array(
						':fund_id'			=> $fundID,
						':asOfDate'			=> $asOfDate
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsAnom->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				while($anoms = $rsAnom->fetch(PDO::FETCH_ASSOC)){
					
					$aStatus		= explode('|',$anoms['status']);
					$memberID		= $anoms['member_id'];
					$fundID			= $anoms['fund_id'];
					$status			= $aStatus[0];
					$statusDate		= $aStatus[1];
					
					switch($status){
						default:
							
							$currentStatus	= '<span class="label label-warning">Waiting Action</span>';
							$showActions	= '
												<a href="javascript:void(0)" onclick="updateStatus(\''.$anoms['uid'].'\',\'valid\');" class="btn btn-xs btn-success">Flag Valid</a> 
                        						<a href="javascript:void(0)" onclick="updateStatus(\''.$anoms['uid'].'\',\'unfixable\');" class="btn btn-xs btn-danger">Flag Unfixable</a> 
                            					<a href="javascript:void(0)" onclick="updateStatus(\''.$anoms['uid'].'\',\'fixed\');" class="btn btn-xs btn-info">Flag Fixed</a>
											';
						break;	
						
						case 'valid':
							$currentStatus	= '<span class="label label-success">Valid</span>';
							$showActions	= '
												<a href="javascript:void(0)" onclick="updateStatus(\''.$anoms['uid'].'\',\'clear\');" class="btn btn-xs btn-warning">Clear Valid Flag</a> 
												<a href="javascript:void(0)" onclick="updateStatus(\''.$anoms['uid'].'\',\'unfixable\');" class="btn btn-xs btn-danger">Flag Unfixable</a> 
												<a href="javascript:void(0)" onclick="updateStatus(\''.$anoms['uid'].'\',\'fixed\');" class="btn btn-xs btn-info">Flag Fixed</a>
											';
						break;
						
						case 'fixed':
							$currentStatus	= '<span class="label label-info">Fixed</span>';
							$showActions	= '
												<a href="javascript:void(0)" onclick="updateStatus(\''.$anoms['uid'].'\',\'valid\');" class="btn btn-xs btn-success">Flag Valid</a> 
												<a href="javascript:void(0)" onclick="updateStatus(\''.$anoms['uid'].'\',\'unfixable\');" class="btn btn-xs btn-danger">Flag Unfixable</a> 
												<a href="javascript:void(0)" onclick="updateStatus(\''.$anoms['uid'].'\',\'clear\');" class="btn btn-xs btn-warning">Clear Fixed Flag</a>
											';
						break;
						
						case 'unfixable':
							$currentStatus	= '<span class="label label-danger">Unfixable</span>';
							$showActions	= '
												<a href="javascript:void(0)" onclick="updateStatus(\''.$anoms['uid'].'\',\'valid\');" class="btn btn-xs btn-success">Flag Valid</a> 
												<a href="javascript:void(0)" onclick="updateStatus(\''.$anoms['uid'].'\',\'clear\');" class="btn btn-xs btn-warning">Clear Unfixable Flag</a> 
												<a href="javascript:void(0)" onclick="updateStatus(\''.$anoms['uid'].'\',\'fixed\');" class="btn btn-xs btn-info">Flag Fixed</a>
											';
			break;	
					}
					
					$ledgerStartDate 	= date('Y-m-d',strtotime('-5 day', $anoms['anomaly_timestamp']));
					$ledgerEndDate		= date('Y-m-d',strtotime('+25 day', $anoms['anomaly_timestamp']));
					
					$ledgerDate			= '|startDate~'.$ledgerStartDate.'|endDate~'.$ledgerEndDate;
					
					?>
                    <tr id="update-anom-<?php echo $anoms['uid'];?>">
                    	<td><?php echo date('m/d/Y', $anoms['anomaly_timestamp']);?></td>
                        <td><?php echo $currentStatus;?></td>
                    	<td><?php echo number_format($anoms['prev_price'],2,'.',',');?></td>
                    	<td><?php echo number_format($anoms['price'],2,'.',',');?></td>
                    	<td><?php echo number_format($anoms['percent_change'],2,'.',',');?>%</td>
                    	<td><?php echo $showActions;?></td>
                        <td><a href="/process/ajax/admin-switch-user.php?member=<?php echo $memberID;?>&admin=<?php echo $_SESSION['member_id'];?>&toggle=admin-to-user&gopage=03-01-00-004&gofund=<?php echo $fundID;?>&returnPage=06-00-00-011&cargo=<?php echo $ledgerDate;?>" class="btn btn-info btn-xs">Ledger</a></td>
                    </tr>
                    <?php
					
				}
				?>
            
			</tbody>
        </table>          
        
        <h4 style="margin-top:30px;">Submit Support Ticket</h4>
        <hr />
        
        <!-- BEGIN New Support Ticket-->
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption"><i class="icon-reorder"></i>Open Ticket</div>
                        <?php /*?><div class="tools">
                           <a href="javascript:;" class="collapse"></a>
                           <a href="#portlet-config" data-toggle="modal" class="config"></a>
                           <a href="javascript:;" class="reload"></a>
                           <a href="javascript:;" class="remove"></a>
                        </div><?php */?>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <form  class="support">
                                <div class="form-body">
                                  	<div id="userError">
                                    </div>
                                        <div class="form-group">
                                            <label  class="control-label">Ticket Type</label>
                                            <select name="ticket" id="ticket_type" class="form-control" >
                                            	<option value="13">Rankings</option>
                                            </select>
                                            <?php /*?><span class="help-block">A block of help text.</span><?php */?>
                                        </div>
                                        
                                        <div id="ticket_list">
                                        	<div class="form-group">
                                                <label  class="control-label">Ticket Type</label>
                                                <select name="problem_type" id="problem_type" class="form-control" >
                                                    <option value="fund_anomaly">Fund Anomaly</option>
                                                </select>
                                        </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label  class="control-label">Ticket Subject *</label>
                                            <input type="text" class="form-control" id="subject" name="subject" value=""> 
                                        </div>
                                        
                                        <div class="form-group">
                                        	<label class="control-label">Description / Resources *</label>
                                            <textarea name="description" id="description" class="form-control" rows="6"></textarea>
                                            <span id="desc-help" class="help-block"></span>
                                        </div>
                                        
                                        <div class="form-group">
                                        	<label class="control-label">Affected Funds</label><br  />
                                            <?php
											$aFundID = explode('-', $fundID);
											$memberID = $aFundID[0];
											
											$query = " 
												SELECT fund_symbol, fund_id
												FROM ".$_SESSION['fund_table']."
												WHERE member_id=:member_id AND active='1'
												ORDER BY seq_id ASC
											";
											
											//Fund Symbols
											try{
												$rsGetFunds = $mLink->prepare($query);
												$aValues = array(
													':member_id' 	=> $memberID
												);
												$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
												$rsGetFunds->execute($aValues);
											}
											catch(PDOException $error){
												// Log any error
												file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
											}
											
											while($fund = $rsGetFunds->fetch(PDO::FETCH_ASSOC)) {
												if($fundID == $fund['fund_id']){
													$selectedFund = 'checked';	
												}else{
													$selectedFund = '';	
												}
												
												echo '<input type="checkbox" name="fund_symbols[]"  value="'.$fund['fund_id'].'" '.$selectedFund.'/> '.$fund['fund_symbol'].'&nbsp;';
											
											}
											//echo $memberID;
											?>
                                          
                                        </div>
                                        
                                        
                                        <input type="hidden" name="member_id" value="<?php echo $memberID;?>" />
                                      
                                        
                                        
                                </div><!--form-body-->
                                
                                <div class="form-actions">
                                        <input type="submit" value="Submit Ticket" class="btn btn-success">
                                </div>
                        </form>
                        <!-- END FORM--> 
                        <div id="support-errors">
                        	
                            <table class="table table-striped table-bordered">
                            	<thead>	
                                	<tr>
                                    	<td>Date</td>
                                    	<th>Case #</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                	<?php
									$query = "
										SELECT st.*, lo.label AS ticket_status_label
										FROM support_tickets as st
										INNER JOIN ".$_SESSION['lists_options_table']." AS lo ON lo.list_id='5' AND lo.value=st.ticket_status
										WHERE st.member_id=:member_id AND st.ticket_type='13' AND st.problem_type='fund_anomaly'
									";
									try{
										$rsTickets = $mLink->prepare($query);
										$aValues = array(
											':member_id' 	=> $memberID
										);
										$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
										$rsTickets->execute($aValues);
									}
									catch(PDOException $error){
										// Log any error
										file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
									}
									
									while($ticket = $rsTickets->fetch(PDO::FETCH_ASSOC)) {
										
										$ticketStatus = $ticket['ticket_status_label'];
										
										?>
                                        <tr>
                                        	<td><?php echo date('m/d/Y', $ticket['opened']);?></td>
                                            <td><?php echo $ticket['ticket_id'];?></td>
                                            <td><?php echo $ticket['ticket_subject'];?></td>
                                            <td><?php echo $ticketStatus;?></td>
                                            <td><a href="/?page=08-01-cc-903&ticket=<?php echo $ticket['ticket_id'];?>" class="btn btn-sm btn-info" target="_blank">Go To Case</a></td>
                                        </tr>
                                        <?php
									}
									?>
                                </tbody>
                            
                            </table>
                        </div>
                        
                    </div>
                </div>
                <!-- END SAMPLE TABLE PORTLET-->
        
        <?php
	break;
	
	case 'update-anom-status':
		
		$uid	= $_REQUEST['uid'];
		$status	= $_REQUEST['status'];
		
		echo $status;
		
		if($status == 'clear'){
			$status = NULL;
		}
		
		$query = "
				UPDATE rank_anomalies
				SET status=:status
				WHERE uid=:uid
			";
		try{
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':status'		=> $status,
				':uid'			=> $uid
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		echo $error;
		$query = "
			SELECT *
			FROM rank_anomalies
			WHERE uid=:uid
		";
		try{
			$rsAnom = $mLink->prepare($query);
			$aValues = array(
				':uid'			=> $uid
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsAnom->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$anoms = $rsAnom->fetch(PDO::FETCH_ASSOC);
			
		$aStatus		= explode('|',$anoms['status']);
		$memberID		= $anoms['member_id'];
		$fundID			= $anoms['fund_id'];
		$status			= $aStatus[0];
		$statusDate		= $aStatus[1];
		
		switch($status){
			default:
				
				$currentStatus	= '<span class="label label-warning">Waiting Action</span>';
				$showActions	= '
									<a href="javascript:void(0)" onclick="updateStatus(\''.$anoms['uid'].'\',\'valid\');" class="btn btn-xs btn-success">Flag Valid</a> 
									<a href="javascript:void(0)" onclick="updateStatus(\''.$anoms['uid'].'\',\'unfixable\');" class="btn btn-xs btn-danger">Flag Unfixable</a> 
									<a href="javascript:void(0)" onclick="updateStatus(\''.$anoms['uid'].'\',\'fixed\');" class="btn btn-xs btn-info">Flag Fixed</a>
								';
			break;
			
			case 'valid':
				$currentStatus	= '<span class="label label-success">Valid</span>';
				$showActions	= '
									<a href="javascript:void(0)" onclick="updateStatus(\''.$anoms['uid'].'\',\'clear\');" class="btn btn-xs btn-warning">Clear Valid Flag</a> 
									<a href="javascript:void(0)" onclick="updateStatus(\''.$anoms['uid'].'\',\'unfixable\');" class="btn btn-xs btn-danger">Flag Unfixable</a> 
									<a href="javascript:void(0)" onclick="updateStatus(\''.$anoms['uid'].'\',\'fixed\');" class="btn btn-xs btn-info">Flag Fixed</a>
								';
			break;
			
			case 'fixed':
				$currentStatus	= '<span class="label label-info">Fixed</span>';
				$showActions	= '
									<a href="javascript:void(0)" onclick="updateStatus(\''.$anoms['uid'].'\',\'valid\');" class="btn btn-xs btn-success">Flag Valid</a> 
									<a href="javascript:void(0)" onclick="updateStatus(\''.$anoms['uid'].'\',\'unfixable\');" class="btn btn-xs btn-danger">Flag Unfixable</a> 
									<a href="javascript:void(0)" onclick="updateStatus(\''.$anoms['uid'].'\',\'clear\');" class="btn btn-xs btn-warning">Clear Fixed Flag</a>
								';
			break;
			
			case 'unfixable':
				$currentStatus	= '<span class="label label-danger">Unfixable</span>';
				$showActions	= '
									<a href="javascript:void(0)" onclick="updateStatus(\''.$anoms['uid'].'\',\'valid\');" class="btn btn-xs btn-success">Flag Valid</a> 
									<a href="javascript:void(0)" onclick="updateStatus(\''.$anoms['uid'].'\',\'clear\');" class="btn btn-xs btn-warning">Clear Unfixable Flag</a> 
									<a href="javascript:void(0)" onclick="updateStatus(\''.$anoms['uid'].'\',\'fixed\');" class="btn btn-xs btn-info">Flag Fixed</a>
								';
			break;	
		}
		
		?>
			<td><?php echo date('m/d/Y', $anoms['anomaly_timestamp']);?></td>
			<td><?php echo $currentStatus;?></td>
			<td><?php echo number_format($anoms['prev_price'],2,'.',',');?></td>
			<td><?php echo number_format($anoms['price'],2,'.',',');?></td>
			<td><?php echo number_format($anoms['percent_change'],2,'.',',');?>%</td>
			<td><?php echo $showActions;?></td>
			<td><a href="/process/ajax/admin-switch-user.php?member=<?php echo $memberID;?>&admin=<?php echo $_SESSION['member_id'];?>&toggle=admin-to-user&gopage=03-01-00-004&gofund=<?php echo $fundID;?>&returnPage=06-00-00-011" class="btn btn-info btn-xs">Ledger</a></td>
		<?php
			
		
		
	break;
	
	
	case 'support-ticket':
		
		
		if($_REQUEST['fund_symbols'] != ""){
			$fundSymbols = implode("|", $_REQUEST['fund_symbols']);
		}
		
		$query = "
			INSERT INTO ".$_SESSION['support_ticket_table']." (
				member_id,
				ticket_type,
				ticket_status,
				ticket_subject,
				problem_type,
				description,
				fund_tickers,
				priority,
				opened,
				timestamp,
				assigned_to
			) VALUE (
				:member_id,
				:ticket_type,
				:ticket_status,
				:ticket_subject,
				:problem_type,
				:description,
				:fund_tickers,
				:priority,
				UNIX_TIMESTAMP(),
				UNIX_TIMESTAMP(),
				:assigned_to
			)
		";
		
		try{
			$rsAddTicket = $mLink->prepare($query);
			$aValues = array(
				':member_id'		=> $_REQUEST['member_id'],
				':ticket_type'		=> $_REQUEST['ticket'],
				':ticket_status'	=> "1",
				':ticket_subject'	=> $_REQUEST['subject'],
				':problem_type'		=> $_REQUEST['problem_type'],		
				':description'		=> $_REQUEST['description'],
				':fund_tickers'		=> $fundSymbols,
				':priority'			=> 'high',
				':assigned_to'		=> '27'
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsAddTicket->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		//GET CASE NUMBER
		$query = "
			SELECT ticket_id
			FROM ".$_SESSION['support_ticket_table']."
			WHERE member_id=:member_id
			ORDER BY timestamp DESC
			LIMIT 1
		";
		
		try{
			$rsCaseNumber = $mLink->prepare($query);
			$aValues = array(
				':member_id'		=> $_REQUEST['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsCaseNumber->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$case = $rsCaseNumber->fetch(PDO::FETCH_ASSOC);
		
		$notificationID = "08-001";
		$notification	= "Rank Support Ticket Submitted. Ticket Number: ".$case['ticket_id']."";
		$link			= "?page=08-01-cc-003&ticket=".$case['ticket_id']."";
		add_notification($mLink, $notificationID, $_REQUEST['member_id'], $notification, $link);
		
		?>
        <table class="table table-striped table-bordered">
            <thead>	
                <tr>
                    <td>Date</td>
                    <th>Case #</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "
                    SELECT st.*, lo.label AS ticket_status_label
                    FROM support_tickets as st
                    INNER JOIN ".$_SESSION['lists_options_table']." AS lo ON lo.list_id='5' AND lo.value=st.ticket_status
                    WHERE st.member_id=:member_id AND st.ticket_type='13' AND st.problem_type='fund_anomaly'
                ";
                try{
                    $rsTickets = $mLink->prepare($query);
                    $aValues = array(
                        ':member_id' 	=> $_REQUEST['member_id']
                    );
                    $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                    $rsTickets->execute($aValues);
                }
                catch(PDOException $error){
                    // Log any error
                    file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                }
                
                while($ticket = $rsTickets->fetch(PDO::FETCH_ASSOC)) {
                    
                    $ticketStatus = $ticket['ticket_status_label'];
                    
                    ?>
                    <tr>
                        <td><?php echo date('m/d/Y', $ticket['opened']);?></td>
                        <td><?php echo $ticket['ticket_id'];?></td>
                        <td><?php echo $ticket['ticket_subject'];?></td>
                        <td><?php echo $ticketStatus;?></td>
                        <td><a href="/?page=08-01-cc-903&ticket=<?php echo $ticket['ticket_id'];?>" class="btn btn-sm btn-info" target="_blank">Go To Case</a></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        
        </table>
        <?php
		
	break;
	
	#--------------------------------------------------------------------------------------------------------------------#
	#											INACTIVE FUND CHECK
	#--------------------------------------------------------------------------------------------------------------------#
	case 'check-inactive-funds':
		
		$query = "
			SELECT *
			FROM members_subscriptions
			WHERE active='1' AND product_type='membership'
		";
		try{
			$rsGet = $mLink->prepare($query);
			$aValues = array();
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGet->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		while($sub = $rsGet->fetch(PDO::FETCH_ASSOC)){
			
			$inactive = false;
			
			$memberID = $sub['member_id'];
			
			$subData = subscription($mLink, $memberID, true, true);
			
			if($subData['maxFundValid'] == false){
				$inactive = true;	
			}
			
			if($subData['subStartDate'] == NULL){
				$inactive = true;	
			}
			
			
			if($inactive == true){
				
				$query = "
					UPDATE members_subscriptions
					SET inactive_timestamp=UNIX_TIMESTAMP()
					WHERE member_id=:member_id
				";
				try{
					$rsUpdate = $mLink->prepare($query);
					$aValues = array(':member_id'=>$memberID);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsUpdate->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
					
			}
			
		}
		
		
		
	break;
	
	#--------------------------------------------------------------------------------------------------------------------#
	#											BUILD AAR TABLE
	#--------------------------------------------------------------------------------------------------------------------#
	case 'process-aar':
		
		$time_start 			= microtime(true);
		
		$compliantPercentage	= $_REQUEST['tolerance'];
		$period					= $_REQUEST['period'];
		$asOfDate				= $_REQUEST['asOfDate'];
		$year 					= substr($asOfDate,0,4);
		$month					= substr($asOfDate,4,2);
		$day					= date('t',mktime(6,0,0,$month,1,$year));
		//$day					= substr($asOfDate,6,2);
		
		/*echo 'asOfDate: '.$asOfDate;
		echo 'sub: '.$year.'-'.$month.'-'.$day;*/
		
		//$asOfDate				= $month.'/'.$day.'/'.$year;
		$currentQuarter			= mktime(6,0,0,$month,$day,$year);
		$currentQuarterStr		= date('Ymd',$currentQuarter); 
		$oneYearPast			= strtotime(''.$year.'-'.$month.'-'.$day.' -1 years');
		$oneYearPastStr			= date('Ymd',$oneYearPast); 
		$threeYearPast			= strtotime(''.$year.'-'.$month.'-'.$day.' -3 years');
		$threeYearPastStr		= date('Ymd',$threeYearPast);  
		$fiveYearPast			= strtotime(''.$year.'-'.$month.'-'.$day.' -5 years'); 
		$fiveYearPastStr		= date('Ymd',$fiveYearPast); 
		$tenYearPast			= strtotime(''.$year.'-'.$month.'-'.$day.' -10 years'); 
		$tenYearPastStr			= date('Ymd',$tenYearPast); 
                $fifteenYearPast                = strtotime(''.$year.'-'.$month.'-'.$day.' -15 years');
                $fifteenYearPastSt              = date('Ymd',$fifteenYearPast);
		
                $aQuarters[15]                  = get_quarter_dates($fifteenYearPast, $currentQuarter);
		$aQuarters[10]			= get_quarter_dates($tenYearPast, $currentQuarter);
		$aQuarters[5]			= get_quarter_dates($fiveYearPast, $currentQuarter);
		$aQuarters[3]			= get_quarter_dates($threeYearPast, $currentQuarter);
		$aQuarters[1]			= get_quarter_dates($oneYearPast, $currentQuarter);
				
		$aBench 				= getBenchmarks($mLink, $asOfDate);			
		$aBadge					= getBadgeInfo($mLink, 1);
		
		#calculate SP Data
		
		$aSpDates['current'] = date('Y-m-t',$currentQuarter);
		$aSpDates[1] = date('Y-m-t',$oneYearPast);
		$aSpDates[3] = date('Y-m-t',$threeYearPast);
		$aSpDates[5] = date('Y-m-t',$fiveYearPast);
		$aSpDates[10] = date('Y-m-t',$tenYearPast);
                $aSpDates[15] = date('Y-m-t',$fifteenYearPast);
		
		$spDates = implode("','",$aSpDates);
		//print_r($spDates);
		
		$query = "
			SELECT *
			FROM stock_index_history
			WHERE date IN ('".$spDates."') AND `index`='^SP500TR'
			ORDER BY unix_date DESC
		";
		try{
			$rsSPdata = $mLink->prepare($query);
			$aValues = array(
				':asOfDate'	=> $asOfDate
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsSPdata->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		//echo $preparedQuery;
		
		$loopCnt = 0;
		while($spData = $rsSPdata->fetch(PDO::FETCH_ASSOC)){
			
			$loopCnt++;
			
			//echo $loopCnt;
			
			$spCloseAdj	= $spData['adj_close'];
			$spClose	= $spData['close'];
			
			if($spCloseAdj == NULL){
				$spPrice = $spClose;		
			}else{
				$spPrice = $spCloseAdj;
			}
			
			//echo $spPrice.' | ';
			
			switch($loopCnt){
				case 1: $_SESSION['sp-current-price'] = $spPrice;break;	
				
				case 2:
					
					$aSpData[1] = array(
						'price'	=> $spPrice,
						'aar'	=> calc_AAR($_SESSION['sp-current-price'], $spPrice, 1)
					); 
					
					
				break;
				
				case 3:
					
					$aSpData[3] = array(
						'price'	=> $spPrice,
						'aar'	=> calc_AAR($_SESSION['sp-current-price'], $spPrice, 3)
					); 
					
				break;
				
				case 4:
					
					$aSpData[5] = array(
						'price'	=> $spPrice,
						'aar'	=> calc_AAR($_SESSION['sp-current-price'], $spPrice, 5)
					); 
					
				break;
				
				case 5:
					
					$aSpData[10] = array(
						'price'	=> $spPrice,
						'aar'	=> calc_AAR($_SESSION['sp-current-price'], $spPrice, 10)
					); 
					
				break;

                                case 6:

                                        $aSpData[15] = array(
                                                'price' => $spPrice,
                                                'aar'   => calc_AAR($_SESSION['sp-current-price'], $spPrice, 15)
                                        );

                                break;
			}
				
		}
		
		
		#clear out old AAR data
		$query = "
				DELETE FROM `rank_aar`
				WHERE as_of_date=:asOfDate
		";
		try{
			$rsDelete = $mLink->prepare($query);
			$aValues = array(
				':asOfDate'	=> $asOfDate
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsDelete->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		#clear out old achievement data
		$query = "
				DELETE FROM `rank_achievements`
				WHERE as_of_date=:asOfDate
		";
		try{
			$rsDelete = $mLink->prepare($query);
			$aValues = array(
				':asOfDate'	=> $asOfDate
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsDelete->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		
		#Build List of know invalid funds
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
		
		while($invalidFunds = $rsInvalidFunds->fetch(PDO::FETCH_ASSOC)){
			
			$aInvalidFunds[$invalidFunds['fund_id']] = $invalidFunds['fund_key'];
			
		}
		
		#get all composite funds
		$aCompositeFunds = array();
		$query 	= "
			SELECT *
			FROM composite_cassatt_list
			WHERE active='1'
		";
		try{
			$rsCompFunds = $mLink->prepare($query);
			$aValues = array();
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsCompFunds->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		while($compFunds = $rsCompFunds->fetch(PDO::FETCH_ASSOC)){
			
			$aCompositeFunds[$compFunds['fund_id']] = $compFunds['fund_id'];
			
		}
		
		$aRankPeriods = array(1,3,5,10,15);
		
		#Get Raw NAV Data loop through results
		$query = "
			SELECT rrn.*, mf.active, ms.product_id, ms.start_timestamp
			FROM rank_raw_nav rrn, members_fund mf, members_subscriptions ms
			WHERE rrn.as_of_date=:asOfDate AND mf.fund_id=rrn.fund_id AND ms.member_id=rrn.member_id AND ms.product_type='membership' AND ms.start_timestamp IS NOT NULL AND ms.inactive_timestamp IS NULL AND rrn.nav_1 IS NOT NULL
		";
		try{
			$rsRawNAV = $mLink->prepare($query);
			$aValues = array(
				':asOfDate'	=> $asOfDate
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsRawNAV->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		echo $preparedQuery;
		$aProcessed = array();
		while($rawNAV = $rsRawNAV->fetch(PDO::FETCH_ASSOC)){
			
			#set vars
			$fundID 				= $rawNAV['fund_id'];
			$memberID				= $rawNAV['member_id'];
			$currentNAV				= $rawNAV['nav'];
			$aNAV[1]				= $rawNAV['nav_1'];
			$aNAV[3]				= $rawNAV['nav_3'];
			$aNAV[5]				= $rawNAV['nav_5'];
			$aNAV[10]				= $rawNAV['nav_10'];
                        $aNAV[15]                               = $rawNAV['nav_15'];
			$aSet					= array();
			$aSet['nav']['current']	= $currentNAV;
			$aSet['composite']		= 'no';
			
			if(in_array($fundID, $aProcessed)){
				continue;	
			}
			
			$aSet['elegible'] = 1;
			
			#check if fund is invalid
			/*if(array_key_exists($fundID, $aInvalidFunds)){
				$aSet['elegible'] = 0;
			}else{
				$aSet['elegible'] = 1;
			}*/
			
			#check if fund is active
			if($rawNAV['active'] == 0){
				$aSet['elegible'] = 0;	
			}
			
			#if composite make eligible
			if(in_array($fundID, $aCompositeFunds)){
				$aSet['elegible'] = 1;
				$aSet['composite'] = 'yes';
			}
						
			foreach($aRankPeriods as $key=>$period){
				
				$gold 	= $aBench[$period.'-year-gold']['value'];
				$silver	= $aBench[$period.'-year-silver']['value'];
				$bronze	= $aBench[$period.'-year-bronze']['value'];
				
				if($aNAV[$period] != NULL){
					
					//Calculate AAR
					$periodAAR = calc_AAR($currentNAV, $aNAV[$period], $period);
					
			
					$aSet['nav'][$period] = $aNAV[$period];
					$aSet['aar'][$period] = $periodAAR;
					
					//Compare to market
					if($periodAAR > $gold){
						
						$aSet['award']['gold_'.$period] = 1;
						$aSet['badges'][$aBadge[$period]['gold']['badge_id']] = array(
							'type'	=>'gold_'.$period,
							'name'	=> $aBadge[$period]['gold']['badge_name']
						);
						
					}elseif($periodAAR > $silver && $periodAAR < $gold){
						
						$aSet['award']['silver_'.$period] = 1;
						$aSet['badges'][$aBadge[$period]['silver']['badge_id']] = array(
							'type'	=>'silver_'.$period,
							'name'	=> $aBadge[$period]['silver']['badge_name']
						);
						
						
					}elseif($periodAAR > $bronze && $periodAAR < $silver){
						
						$aSet['award']['bronze_'.$period] = 1;
						$aSet['badges'][$aBadge[$period]['bronze']['badge_id']] = array(
							'type'	=>'bronze_'.$period,
							'name'	=> $aBadge[$period]['bronze']['badge_name']
						);
						
						
					}else{
						$aSet['award']['year_'.$period] = 1;
						$aSet['badges'][$aBadge[$period]['standard']['badge_id']] = array(
							'type'	=>'standard_'.$period,
							'name'	=> $aBadge[$period]['standard']['badge_name']
						);
					}
					
					//Check compliance
					$compliant	= checkCompliance($mLink, $aQuarters[$period], $compliantPercentage, $fundID);
					$aSet['compliance'][$period]= $compliant;
					
				}//END PERIOD NULL
				
					
			}//END FOREACH PERIOD
			
			#insert into AAR Table
			$query ="
				INSERT INTO rank_aar (
					member_id,
					fund_id,
					eligible,
					composite,
					as_of_timestamp,
					as_of_date,
					fifteenYearAAR,
					tenYearAAR,
					fiveYearAAR,
					threeYearAAR,
					oneYearAAR,
					fifteenYearNAV,
					tenYearNAV,
					fiveYearNAV,
					threeYearNAV,
					oneYearNAV,
					currentNAV,
					fifteenYearCompliant,
					tenYearCompliant,
					fiveYearCompliant,
					threeYearCompliant,
					oneYearCompliant,
					fifteenYearAARsp,
					tenYearAARsp,
					fiveYearAARsp,
					threeYearAARsp,
					oneYearAARsp,
					timestamp
				)VALUES(
					:member_id,
					:fund_id,
					:eligible,
					:composite,
					:as_of_timestamp,
					:as_of_date,
					:fifteenYearAAR,
					:tenYearAAR,
					:fiveYearAAR,
					:threeYearAAR,
					:oneYearAAR,
					:fifteenYearNAV,
					:tenYearNAV,
					:fiveYearNAV,
					:threeYearNAV,
					:oneYearNAV,
					:currentNAV,
					:fifteenYearCompliant,
					:tenYearCompliant,
					:fiveYearCompliant,
					:threeYearCompliant,
					:oneYearCompliant,
					:fifteenYearAARsp,
					:tenYearAARsp,
					:fiveYearAARsp,
					:threeYearAARsp,
					:oneYearAARsp,
					unix_timestamp()
				)
			";
			try{
				$rsInsert = $mLink->prepare($query);
				$aValues = array(
					':member_id'				=> $memberID,
					':fund_id'					=> $fundID,
					':eligible'					=> $aSet['elegible'],
					':composite'				=> $aSet['composite'],
					':as_of_timestamp'			=> $currentQuarter,
					':as_of_date'				=> $asOfDate,
                                        ':fifteenYearAAR'                       => $aSet['aar'][15],
					':tenYearAAR'				=> $aSet['aar'][10],
					':fiveYearAAR'				=> $aSet['aar'][5],
					':threeYearAAR'				=> $aSet['aar'][3],
					':oneYearAAR'				=> $aSet['aar'][1],
                                        ':fifteenYearNAV'                       => $aSet['nav'][15],
					':tenYearNAV'				=> $aSet['nav'][10],
					':fiveYearNAV'				=> $aSet['nav'][5],
					':threeYearNAV'				=> $aSet['nav'][3],
					':oneYearNAV'				=> $aSet['nav'][1],
					':currentNAV'				=> $aSet['nav']['current'],
                                        ':fifteenYearCompliant'                 => $aSet['compliance'][15],
 					':tenYearCompliant'			=> $aSet['compliance'][10],
					':fiveYearCompliant'		=> $aSet['compliance'][5],
					':threeYearCompliant'		=> $aSet['compliance'][3],
					':oneYearCompliant'			=> $aSet['compliance'][1],
                                        ':fifteenYearAARsp'                     => $aSpData[15]['aar'],
					':tenYearAARsp'				=> $aSpData[10]['aar'],
					':fiveYearAARsp'			=> $aSpData[5]['aar'],
					':threeYearAARsp'			=> $aSpData[3]['aar'],
					':oneYearAARsp'				=> $aSpData[1]['aar']
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//echo $preparedQuery; die();
				$rsInsert->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$aProcessed[] = $fundID;
			
			foreach($aSet['badges'] as $badgeID=>$aBadgeInfo){
				
				if($aSet['elegible'] == 1){
				
					$query = "
						INSERT INTO rank_achievements (
							member_id,
							fund_id,
							as_of_date,
							as_of_timestamp,
							achievement,
							badge_id,
							badge,
							timestamp
						)VALUES(
							:member_id,
							:fund_id,
							:as_of_date,
							:as_of_timestamp,
							:achievement,
							:badge_id,
							:badge,
							UNIX_TIMESTAMP()
						)
					";
					try{
						$rsInsertAch = $mLink->prepare($query);
						$aValues = array(
							':member_id'				=> $memberID,
							':fund_id'					=> $fundID,
							':as_of_timestamp'			=> $currentQuarter,
							':as_of_date'				=> $asOfDate,
							':achievement'				=> $aBadgeInfo['name'],
							':badge_id'					=> $badgeID,
							':badge'					=> $aBadgeInfo['type']
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsInsertAch->execute($aValues);
					}
					
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
				}
			}
				
		}//END RAW NAV LOOP
		
		$time_end 		= microtime(true);
		
		$processTime	=	$time_end - $time_start;
		
		echo "Process Time: ".$processTime." ms";
		
		echo '<pre>';
		print_r($aSpData);
		print_r($aSet);
		echo '</pre>';
	
	break;
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	case 'build-rankings-new':
		
		$debug 					= true;
		$saveDB					= true;
		$debugMemberID			= '2';
		
		$time_start 			= microtime(true);
		
		$runPriceRun			= ($_REQUEST['update-records'] 		== '1' ? true : false);
		$processResults			= ($_REQUEST['process-results'] 	== '1' ? true : false);
		$processBadges			= ($_REQUEST['process-badges'] 		== '1' ? true : false);
		$processRankings		= ($_REQUEST['process-rankings'] 	== '1' ? true : false);	
		
		$compliantPercentage	= $_REQUEST['tolerance'];
		$period					= $_REQUEST['period'];
		$year 					= $_REQUEST['year'];
		$month					= $_REQUEST['month'];
		$day					= $_REQUEST['day'];
		$rankDate				= $year.$month.$day;
		$asOfDate				= $month.'/'.$day.'/'.$year;
		$currentQuarter			= mktime(6,0,0,$month,$day,$year);
		$currentQuarterStr		= date('Ymd',$currentQuarter); 
		$oneYearPast			= strtotime(''.$year.'-'.$month.'-'.$day.' -1 years');
		$oneYearPastStr			= date('Ymd',$oneYearPast); 
		$threeYearPast			= strtotime(''.$year.'-'.$month.'-'.$day.' -3 years');
		$threeYearPastStr		= date('Ymd',$threeYearPast);  
		$fiveYearPast			= strtotime(''.$year.'-'.$month.'-'.$day.' -5 years'); 
		$fiveYearPastStr		= date('Ymd',$fiveYearPast); 
		$tenYearPast			= strtotime(''.$year.'-'.$month.'-'.$day.' -10 years'); 
		$tenYearPastStr			= date('Ymd',$tenYearPast); 
                $fifteenYearPast                    = strtotime(''.$year.'-'.$month.'-'.$day.' -15 years');
                $fifteenYearPastStr                 = date('Ymd',$fifteenYearPast);
		$rankClass				= $_REQUEST['rank_class'];
		$api					= $_REQUEST['api-server'];
		
		//$aPeriodDates			= array($currentQuarterStr, $oneYearPastStr, $threeYearPastStr, $fiveYearPastStr, $tenYearPastStr);
		
                $aFifteenYearQuarters               = get_quarter_dates($fifteenYearPast, $currentQuarter);
		$aTenYearQuarters		= get_quarter_dates($tenYearPast, $currentQuarter);
		$aFiveYearQuarters		= get_quarter_dates($fiveYearPast, $currentQuarter);
		$aThreeYearQuarters		= get_quarter_dates($threeYearPast, $currentQuarter);
		$aOneYearQuarters		= get_quarter_dates($oneYearPast, $currentQuarter);
		
		echo $rankDate;
		
		$aRankPeriods			= array();
		$aInvalidFunds			= array();
		
		//determine which class of rank to run for
		switch($rankClass){
		
			case 'pro':
				$rankTable	= 'rankings_stage_pro';
			break;
			
			case 'community':
				$rankTable 	= 'rankings_stage_community';
			break;	
		}
		
		//determine which periods to run for
		switch($period){
			
			case 'all':
                                $aRankPeriods[15]       = $aFifteenYearQuarters;
				$aRankPeriods[10] 	= $aTenYearQuarters;
				$aRankPeriods[5] 	= $aFiveYearQuarters;
				$aRankPeriods[3] 	= $aThreeYearQuarters;
				$aRankPeriods[1] 	= $aOneYearQuarters;
			break;
			
                        case '15':
                                $aRankPeriods[15]       = $aFifteenYearQuarters;
                        break;

			case '10':
				$aRankPeriods[10] 	= $aTenYearQuarters;
			break;
			
			case '5':
				$aRankPeriods[5] 	= $aFiveYearQuarters;
			break;
			
			case '3':
				$aRankPeriods[3] 	= $aThreeYearQuarters;
			break;
			
			case '1':
				$aRankPeriods[1] 	= $aOneYearQuarters;
			break;				
		}
		
		//Build an array of previously know invalid funds
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
		
		while($invalidFunds = $rsInvalidFunds->fetch(PDO::FETCH_ASSOC)){
			
			$aInvalidFunds[$invalidFunds['fund_id']] = $invalidFunds['fund_key'];
			
		}
		if(!array_key_exists($fundID, $aInvalidFunds)){
			
		}
		
		if($debug == true){
			$testQuery = "AND f.member_id='".$debugMemberID."'";
		}else{
			$testQuery = "";	
		}
		
		
		//Get all active members who have a verified email address
		$query = "
			SELECT f.fund_id, f.fund_symbol, f.member_id, f.unix_date, f.rank_eligible, m.username
			FROM `members_fund` as f 
			INNER JOIN `members` as m ON m.member_id=f.member_id
			WHERE f.active='1' AND m.active='1' AND f.short_fund='0' AND f.private='0' ".$testQuery."
		";
		try{
			$rsFunds = $mLink->prepare($query);
			$aValues = array();
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsFunds->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$getFundQuery = $preparedQuery;
		
		$aMembers	= array();
		$fundCnt	= 0;
		
		while($funds = $rsFunds->fetch(PDO::FETCH_ASSOC)){
			
			$memberID	= $funds['member_id'];
			$fundID		= $funds['fund_id'];
			
			
			$aMembers[$memberID][] = array(
				'fund_id'		=> $fundID,
				'incept'		=> $funds['unix_date'],
				'username'		=> $funds['username'],
				'fund_symbol'	=> $funds['fund_symbol'],
				'rank_eligible'	=> $funds['rank_eligible']
			);
		}	
		
		
		
		if($runPriceRun == false){
			
			
			//Start build benchmark array
			$query = "
				SELECT * 
				FROM ".$_SESSION['system_benchmarks_table']."
				WHERE sub_type='badge' AND period=:period
			";
			
			try{
				$rsBench = $mLink->prepare($query);
				$aValues = array(
					':period' => $rankDate
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
					'type'			=> $badge['type'],
					'image'			=> $badge['badge']
				);
			}
			//END build badge array
			
			
			//START | Calculate AARs Process
			if($processResults == true){
				
				if($debug == true){
					$query = "
						DELETE FROM `rankings_process_results`
						WHERE asOfDate=:asOfDate AND member_id='".$debugMemberID."'
					";
				}else{
				//Clear out old results
					$query = "
						DELETE FROM `rankings_process_results`
						WHERE asOfDate=:asOfDate
					";
				}
				try{
					$rsDelete = $mLink->prepare($query);
					$aValues = array(
						':asOfDate'	=> $asOfDate
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsDelete->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				//get pricing and add to array
				foreach($aMembers as $memberID=>$aFunds){
					
					//$aFunds = $aMember['funds'];
					
					foreach($aFunds as $key=>$aFundInfo){
					
						$fundID			= $aFundInfo['fund_id'];
						$rankEligible	= $aFundInfo['rank_eligible'];
						$incept			= $aFundInfo['incept'];
						
						
						
						//START ONE YEAR
						$oneYearGold 	= $aBench['1-year-gold']['value'];
						$oneYearSilver	= $aBench['1-year-silver']['value'];
						$oneYearBronze	= $aBench['1-year-bronze']['value'];
						
						//Get price data
						$query = "
							SELECT current_nav, nav_1, nav_3, nav_5, nav_10, nav_15,
							FROM ".$rankingNavTable."
							WHERE fund_id=:fund_id AND asOfDate=:rankDate
						";
						
						try{
							$rsStats = $mLink->prepare($query);
							$aValues = array(
								':fund_id'	=> $fundID,
								':rankDate'	=> $rankDate,
							);
							$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
							$rsStats->execute($aValues);
						}
						
						catch(PDOException $error){
							// Log any error
							file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
						}
						
						$loopCnt 		= 0;
						$oneYearLoop 	= false;
						$threeYearLoop	= false;
						$fiveYearLoop	= false;
						$tenYearLoop	= false;
                                                $fifteenYearLoop    = false;
						
						echo $error;
						
						while($stats = $rsStats->fetch(PDO::FETCH_ASSOC)){
							
							$loopCnt++;
							
							$currentPrice 	= round($stats['current_nav'],4);
							$oneYearNAV		= round($stats['nav_1'],4);
							$threeYearNAV	= round($stats['nav_3'],4);
							$fiveYearNAV	= round($stats['nav_5'],4);
							$tenYearNAV		= round($stats['nav_10'],4);
                                                        $fifteenYearNAV             = round($stats['nav_15'],4);
							
							if($stats['nav_1'] != '0.000000000000'){
								$oneYearLoop = true;	
							}
							if($stats['nav_3'] != '0.000000000000'){
								$threeYearLoop = true;	
							}
							if($stats['nav_5'] != '0.000000000000'){
								$fiveYearLoop = true;	
							}
							if($stats['nav_10'] != '0.000000000000'){
								$tenYearLoop = true;	
							}
                                                        if($stats['nav_15'] != '0.000000000000'){
                                                                $fifteenYearLoop = true;
                                                        }
							
						}
						//End get price data
						
						if($oneYearLoop == true){
							//START Calculate AAR
							$oneYear = calc_AAR($currentPrice, $oneYearNAV, '1');
							
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
						
						
						if($threeYearLoop == true){
							//START Calculate AAR
							$threeYear = calc_AAR($currentPrice, $threeYearNAV, '3');
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
						
						
						if($fiveYearLoop == true){
							//START Calculate AAR
							$fiveYear = calc_AAR($currentPrice, $fiveYearNAV, '5');
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
                                                $tenYearGold    = $aBench['10-year-gold']['value'];
                                                $tenYearSilver  = $aBench['10-year-silver']['value'];
                                                $tenYearBronze  = $aBench['10-year-bronze']['value'];


                                                if($tenYearLoop == true){


                                                        //START Calculate AAR
                                                        $tenYear = calc_AAR($currentPrice, $tenYearNAV, '10');
                                                        //$aMembers[$memberID]['price'][$fundID]['10year']['AAR'] = $tenYear; //

                                                        $aMembers[$memberID]['prices'][$fundID] = array('currentPrice'=>$currentPrice,'pastPrice'=>$tenYearNAV,'aar'=>$tenYear);

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

						
						//START FIFTEEN YEAR
						$fifteenYearGold 	= $aBench['15-year-gold']['value'];
						$fifteenYearSilver	= $aBench['15-year-silver']['value'];
						$fifteenYearBronze	= $aBench['15-year-bronze']['value'];
						
						
						if($fifteenYearLoop == true){
							
							
							//START Calculate AAR
							$fifteenYear = calc_AAR($currentPrice, $fifteenYearNAV, '15');
							//$aMembers[$memberID]['price'][$fundID]['15year']['AAR'] = $fifteenYear; //
							
							$aMembers[$memberID]['prices'][$fundID] = array('currentPrice'=>$currentPrice,'pastPrice'=>$fifteenYearNAV,'aar'=>$fifteenYear);
							
							if(is_nan($fifteenYear) == TRUE){
								$setFifteenYear = '';
								$fifteenYear = '';	
							}elseif($fifteenYear > $fifteenYearGold){
								
								$setFifteenYear = $aBench['15-year-gold']['type'];
								$aMembers[$memberID]['badges'][$fundID][$aBadge[17]['badge_name']] = $aBadge[17]['badge_id'];
									
							}elseif($fifteenYear > $fifteenYearSilver && $fifteenYear < $fifteenYearGold){
								
								$setFifteenYear = $aBench['15-year-silver']['type'];
								$aMembers[$memberID]['badges'][$fundID][$aBadge[18]['badge_name']] = $aBadge[18]['badge_id'];
								
							}elseif($fifteenYear > $fifteenYearBronze && $fifteenYear < $fifteenYearSilver){
								
								$setFifteenYear = $aBench['15-year-bronze']['type'];
								$aMembers[$memberID]['badges'][$fundID][$aBadge[20]['badge_name']] = $aBadge[20]['badge_id'];
									
							}elseif($fifteenYear <= 0){
								$setfifteenYear = '';
								$fifteenYear = '';	
							}else{
								$setfifteenYear = '';
								$aMembers[$memberID]['badges'][$fundID][$aBadge[19]['badge_name']] = $aBadge[19]['badge_id'];
							}
								
							
						}else{
							$setFifteenYear = '';
							$fifteenYear = '';	
						}
						//END FIFTEEN YEAR

						
						$aMembers[$memberID]['AAR'][$fundID] = array(
							'1year'		=> $oneYear,
							'3year'		=> $threeYear,
							'5year'		=> $fiveYear,
							'10year'	=> $tenYear,
                                                        '15year'        => $fifteenYear,
						);
						
						$aMembers[$memberID]['fundBadge'][$fundID] = array(
							'1year'		=> $setOneYear,
							'3year'		=> $setThreeYear,
							'5year'		=> $setFiveYear,
							'10year'	=> $setTenYear,
                                                        '15year'        => $setFifteenYear,
						);
						
						
						$aBadgeNamesResults = array();
						foreach($aMembers[$memberID]['badges'][$fundID] as $badgeName=>$badgeID){
							$aBadgeNamesResults[] = $badgeName;
						}
						
						
						//CHECK FUND COMPLIANCE
                                                if($incept <= $fifteenYearPast){

                                                        $fifteenYearCompliant       = checkCompliance($mLink, $aFifteenYearQuarters, $compliantPercentage, $fundID);
                                                        $tenYearCompliant       = checkCompliance($mLink, $aTenYearQuarters, $compliantPercentage, $fundID);
                                                        $fiveYearCompliant      = checkCompliance($mLink, $aFiveYearQuarters, $compliantPercentage, $fundID);
                                                        $threeYearCompliant     = checkCompliance($mLink, $aThreeYearQuarters, $compliantPercentage, $fundID);
                                                        $oneYearCompliant       = checkCompliance($mLink, $aOneYearQuarters, $compliantPercentage, $fundID);

						}elseif($incept <= $tenYearPast){
							
                                                        $fifteenYearCompliant       = NULL;
							$tenYearCompliant	= checkCompliance($mLink, $aTenYearQuarters, $compliantPercentage, $fundID);
							$fiveYearCompliant	= checkCompliance($mLink, $aFiveYearQuarters, $compliantPercentage, $fundID);
							$threeYearCompliant	= checkCompliance($mLink, $aThreeYearQuarters, $compliantPercentage, $fundID);
							$oneYearCompliant	= checkCompliance($mLink, $aOneYearQuarters, $compliantPercentage, $fundID);
							
							
						}elseif($incept <= $fiveYearPast){
							
                                                        $fifteenYearCompliant       = NULL;
							$tenYearCompliant	= NULL;
							$fiveYearCompliant	= checkCompliance($mLink, $aFiveYearQuarters, $compliantPercentage, $fundID);
							$threeYearCompliant	= checkCompliance($mLink, $aThreeYearQuarters, $compliantPercentage, $fundID);
							$oneYearCompliant	= checkCompliance($mLink, $aOneYearQuarters, $compliantPercentage, $fundID);
								
						}elseif($incept	<= $threeYearPast){

                                                        $fifteenYearCompliant       = NULL;
							$tenYearCompliant	= NULL;
							$fiveYearCompliant	= NULL;
							$threeYearCompliant	= checkCompliance($mLink, $aThreeYearQuarters, $compliantPercentage, $fundID);
							$oneYearCompliant	= checkCompliance($mLink, $aOneYearQuarters, $compliantPercentage, $fundID);
								
						}elseif($incept <= $oneYearPast){
							
                                                        $fifteenYearCompliant       = NULL;
							$tenYearCompliant	= NULL;
							$fiveYearCompliant	= NULL;
							$threeYearCompliant	= NULL;
							$oneYearCompliant	= checkCompliance($mLink, $aOneYearQuarters, $compliantPercentage, $fundID);
								
						}
						
						if($saveDB == true){
							$query = "
								INSERT INTO `rankings_process_results` (
									member_id,
									fund_id,
									eligible,
									rank_unix_date,
									rank_date,
									badge_ids,
									badges,
                                                                        fifteenYearAAR,
									tenYearAAR,
									fiveYearAAR,
									threeYearAAR,
									oneYearAAR,
                                                                        fifteenYearNAV,
									tenYearNAV,
									fiveYearNAV,
									threeYearNAV,
									oneYearNAV,
									currentNAV,
                                                                        fifteenYearCompliant,
									tenYearCompliant,
									fiveYearCompliant,
									threeYearCompliant,
									oneYearCompliant,
									asOfDate,
									timestamp
								) VALUES (
									:member_id,
									:fund_id,
									:eligible,
									:rank_unix_date,
									:rank_date,
									:badge_ids,
									:badges,
                                                                        :fifteenYearAAR,
 									:tenYearAAR,
									:fiveYearAAR,
									:threeYearAAR,
									:oneYearAAR,
                                                                        :fifteenYearNAV,
									:tenYearNAV,
									:fiveYearNAV,
									:threeYearNAV,
									:oneYearNAV,
									:currentNAV,
                                                                        :fifteenYearCompliant,
									:tenYearCompliant,
									:fiveYearCompliant,
									:threeYearCompliant,
									:oneYearCompliant,
									:asOfDate,
									UNIX_TIMESTAMP()
								)
							";
							
							//echo $fiveYear;
							
							try{
								$rsUpdate = $mLink->prepare($query);
								$aValues = array(
									':member_id'			=> $memberID,
									':fund_id'				=> $fundID,
									':eligible'				=> $rankEligible,
									':rank_unix_date'		=> $currentQuarter,
									':rank_date'			=> $currentQuarterStr,
									':badge_ids'			=> implode(',', $aMembers[$memberID]['badges'][$fundID]),
									':badges'				=> implode(',', $aBadgeNamesResults),
                                                                        ':fifteenYearAAR'                   => $fifteenYear,
									':tenYearAAR'			=> $tenYear,
									':fiveYearAAR'			=> $fiveYear,
									':threeYearAAR'			=> $threeYear,
									':oneYearAAR'			=> $oneYear,
                                                                        ':fifteenYearNAV'                   => $fifteenYearNAV,
									':tenYearNAV'			=> $tenYearNAV,
									':fiveYearNAV'			=> $fiveYearNAV,
									':threeYearNAV'			=> $threeYearNAV,
									':oneYearNAV'			=> $oneYearNAV,
									':currentNAV'			=> $currentNAV,
                                                                        ':fifteenYearCompliant'             => $fifteenYearCompliant,
									':tenYearCompliant'		=> $tenYearCompliant,
									':fiveYearCompliant'	=> $fiveYearCompliant,
									':threeYearCompliant'	=> $threeYearCompliant,
									':oneYearCompliant'		=> $oneYearCompliant,
									':asOfDate'				=> $asOfDate
									
								);
								$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
								$rsUpdate->execute($aValues);
							}
							
							catch(PDOException $error){
								// Log any error
								file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
							}
							echo $error;
						}
						
                                                $fifteenYear = '';
						$tenYear = '';
						$fiveYear = '';
						$threeYear = '';
						$oneYear = '';
                                                $fifteenYearNAV = '';
						$tenYearNAV = '';
						$fiveYearNAV = '';
						$threeYearNAV = '';
						$oneYearNAV = '';
						$currentNAV = '';
					}
					
					
					
						
				}//end foreach member loop
				
			}#if $processResults == true
			//END | Calculate $processResults
			
			
			if($processBadges == true){
				
			}#end $processBadges == true
			
			echo 'Period:'.$period;
			if($processRankings == true){
				
				
				$aRankResults = processRankings($mLink, $currentQuarter, $period, $rankClass, $compliantPercentage);
			}#end $processRankings == true
			
				
		}//end if runPriceRun == false
		
		
		echo '<pre>';
		
		//print_r($aRankResults);
		echo $getFundQuery;
		echo $runPriceRun.' | '.$asOfDate.' | '.$rankDate.' | '.$currentQuarterStr.' | '.$oneYearPastStr.' | '.$threeYearPastStr.' | '.$fiveYearPastStr.' | '.$tenYearPastStr.' | '.$rankClass.' | '.$api;
		
		//print_r(count($aMethodVars));
		
		//print_r($aBadge);
		
		//print_r('<br />'.count($aMembers));
		
		/*foreach($aMembers as $memberID=>$aFunds){
			
			foreach($aFunds as $key=>$fundID){
				
				$fundCnt++;
					
			}
				
		}*/
		
		print_r('<br />'.$fundCnt);
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		
		print_r('<br />Processing Time:'.number_format($time, 4, '.', ',').' Seconds');
		
		echo '<br />'.number_format(($time / 60), 4, '.', ',').' Minutes';
		
		print_r($aMembers);
		
		echo '</pre>';
		
	break;
	
	
	case 'gap-check':
		
		
		
		$memberID 	= $_REQUEST['member_id'];
		$aFunds		= array();
		
		if($memberID == ''){
			
			
			
		}else{
			
			$query = "
				SELECT f.fund_id, f.fund_symbol, f.unix_date, m.username
				FROM members_fund AS f
				INNER JOIN members AS m ON m.member_id=f.member_id
				WHERE f.member_id=:member_id AND f.active='1'
			";
			try{
				$rsGetFunds = $mLink->prepare($query);
				$aValues = array(
					':member_id'	=> $memberID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetFunds->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			while($funds = $rsGetFunds->fetch(PDO::FETCH_ASSOC)){
				
				$aFunds[$funds['fund_id']] = array(
					'fund_symbol'	=> $funds['fund_symbol'],
					'username'		=> $funds['username'],
					'inception'		=> $funds['unix_date']
				);
					
			}
				
		}
		
		foreach($aFunds as $fundID=>$aFundInfo){
			
			$aMissingDates = array();
			
			$query = "
				SELECT d.*
				FROM system_dates_since_inception as d
				WHERE d.unix_date>:incept AND d.date NOT IN (select `date` FROM members_fund_pricing WHERE fund_id=':fund_id')
			";
			try{
				$rsGetDates = $mLink->prepare($query);
				$aValues = array(
					':fund_id'		=> $fundID,
					':incept'		=> $aFundInfo['inception']
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetDates->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			while($date = $rsGetDates->fetch(PDO::FETCH_ASSOC)){
				
				$aMissingDates[] = $date['date'];
				echo 'hello';
				echo $date['date'];
					
			}
			$aFunds[$fundID]['query'] = $preparedQuery;
			$aFunds[$fundID]['missing'] = $aMissingDates;	
		}
		
		
		echo '<pre>';
		print_r($aFunds);
		echo '</pre>';
		
	break;
		
}

?>
