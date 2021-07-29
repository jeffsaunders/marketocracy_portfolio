<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

require_once("../../includes/system-functions.php");

$process = $_REQUEST['process'];

set_time_limit(3600);



$getDay		= $_REQUEST['day'];
$getMonth	= $_REQUEST['month'];
$getYear	= $_REQUEST['year'];

$startDate = '946702800'; //2000 - 01 - 01
//$startDate = '971377200';
$endDate = mktime(10,0,0,$getMonth,$getDay,$getYear);

//START GET DATES FOR RETURN CALCULATION
function get_months($time1, $time2) {
	
    $my = date('mY', $time2);
	
	//echo $my;
    $months = array(/*date('Y-m-t', $time1)*/);
    $f = '';

    while($time1 < $time2) {
        $time1 = strtotime((date('Y-m-d', $time1).' +15days'));

        if(date('F', $time1) != $f) {
            $f = date('F', $time1);

            if(date('mY', $time1) != $my && ($time1 < $time2))
                $months[] = date('Y-m-t', $time1);
				//$months[] = $time1;
        }

    }

    $months[] = date('Y-m-d', $time2);
	//$months[] = $time2;
    return $months;
}

$aDates = get_months($startDate, $endDate);

echo '<pre>';
echo $startDate.' | '.$endDate;

//print_r($aDates);
echo '</pre>';

//print_r($aDates);

foreach($aDates as $key=>$timestamp){
	
	$aTimestamp = explode('-', $timestamp);
	
	$timestamp = mktime(0, 0, 0, $aTimestamp[1], $aTimestamp[2], $aTimestamp[0]);
	
	//check to see if its a weekend, if it is get the timestamp of the friday before it
	if(date('N', $timestamp) == '6'){
		$timestamp = strtotime('-1 day', $timestamp);
	}elseif(date('N', $timestamp) == '7'){
		$timestamp = strtotime('-2 day', $timestamp);	
	}
	
	if(date('N', $timestamp) == '1' && isMarketHoliday($timestamp, $mLink, true) == 'Y'){
		$timestamp = strtotime('-3 day', $timestamp);	
	}
	
	//Check to see if the timestamp is a Market Holiday
	if(isMarketHoliday($timestamp, $mLink, true) == 'Y'){
		
		$timestamp = strtotime('-1 day', $timestamp);
		
	}
	
	//$aDates[$key] = date('Y-m-d', $timestamp);
	$aDates[$key] = date('Ymd',$timestamp);	
}


//print_r($aDates);
/*echo '<pre>';

print_r($aDates);

echo '</pre>';*/



$queryDates = implode(',', $aDates);
//END GET DATES FOR RETURN CALCULATION


//echo $queryDates;

//GET S&P INFO
$query = "
	SELECT *
	FROM stock_index_history
	WHERE `index`='^SP500TR'
	ORDER BY unix_date ASC
";
try{
$rsGetSP = $mLink->prepare($query);
	$aValues = array();
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsGetSP->execute($aValues);
}

catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

while($sp = $rsGetSP->fetch(PDO::FETCH_ASSOC)){
	
	$aSP500[$sp['date']] = $sp['close'];
	
}

//print_r($aSP500);

if($process == "1"){
	
	if($_SESSION['members_table'] == ''){
		die('No sessions variables are set');	
	}
	
	$member = $_REQUEST['member'];
	
	
	
	
	if($member != ''){
		
		//Remove all records with member id
		$query = "
			DELETE
			FROM ".$_SESSION['fund_mtm_table']."
			WHERE member_id=:member_id
		";
		try{
			$rsClearTable = $mLink->prepare($query);
			$aValues = array(
				':member_id' 	=> $member
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsClearTable->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
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
		
		//Clear out table
		$queryT = "
			TRUNCATE ".$_SESSION['fund_mtm_table']."
		";
		
		try{
			$rsClearTable = $mLink->prepare($queryT);
			$aValues = array(
				//':active' 	=> '1'
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsClearTable->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
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
			SELECT fund_id, unix_date as incept
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
			$aFunds[$funds['fund_id']] = $funds['incept'];
			
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
	
	/*echo '<pre>';
	print_r($aMembers);
	echo '<pre>';*/
	
	//get pricing and add to array
	foreach($aMembers as $memberID=>$aMember){
		
		$aMembersWorking 	= array();
		
		//START loop through funds and get calculated data
		foreach($aMember['funds'] as $fundID=>$fundIncept){
			
			//unset($aMembers[$memberID]['funds'][$key]);
			
			//initialize array
			$aFundPrice = array();
			
			//Get fund inception date
			//$fundIncept = get_funds($mLink, $fundID, 'unixIncept', 'agg');
			
			//echo $fundIncept;
			
			//create first record
			$aFundPrice[0] = '10';
			
			//loop through and get all pricing records that match the dates
			$query = "
				SELECT price, date, unix_date
				FROM ".$_SESSION['fund_pricing_table']."
				WHERE date IN (20000101,".$queryDates.") AND fund_id=:fund_id
				ORDER BY unix_date ASC
			";
			
			try{
			$rsPrice = $mLink->prepare($query);
				$aValues = array(
					':fund_id' 	=> $fundID
					
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsPrice->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			
			//echo $preparedQuery;
			
			while($price = $rsPrice->fetch(PDO::FETCH_ASSOC)){
				$aFundPrice[$price['unix_date']] = $price['price'];
			}
			
			
			
			//get most current record
			/*$query2 = "
				SELECT price, date, unix_date
				FROM ".$_SESSION['fund_pricing_table']."
				WHERE fund_id=:fund_id
				ORDER BY unix_date DESC
				LIMIT 1
			";
			try{
			$rsPrice2 = $mLink->prepare($query2);
				$aValues = array(
					':fund_id' 	=> $fundID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsPrice2->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$price = $rsPrice2->fetch(PDO::FETCH_ASSOC);
			
			$aFundPrice[$price['unix_date']] = $price['price'];*/
			//end get current pricing record
			
			$aMembersWorking[$memberID][$fundID]['pricing'] = $aFundPrice;
			
			//START LOOP THROUGH PRICING RECORDS
			$loopCnt = 0;
			
			foreach($aFundPrice as $timestamp=>$price){
				
				//Set the start of the YTD
				if(date('n',$timestamp) == '1' && $setYTDyear != date('Y', $timestamp) || $timestamp == '0'){
					
					
					
					if($loopCnt == '0'){
						$setYTDstart = $price;
						$setYTDyear = date('Y', $timestamp);
						
						$setSpYTDstart = $aSP500[date('Y-m-d',checkForMarketDate($fundIncept, $mLink))];
						
						$prevSPprice = $setSpYTDstart;
						//echo $setSpYTDstart;		
					}else{
						
						$setYTDstart = $prevMonthPrice;
						$setYTDyear = date('Y', $timestamp);
						
						$setSpYTDstart = $prevSPprice;
					}
					
				}
				
				
				//end set the start of the YTD
				
				if($loopCnt != 0){
					
					//set calc vars
					$currentMonth = $timestamp;
					
					$endMonthPrice = $price;
					$endSpMonthPrice = $aSP500[date('Y-m-d', $timestamp)];
					
					$aEndMonth[date('Y-m-d', $currentMonth)] = $endMonthPrice; 
					
					//Month Return
					$monthReturn = (($endMonthPrice - $prevMonthPrice) / $prevMonthPrice)*100;
					
					//YTD
					$YTD = (($endMonthPrice - $setYTDstart ) / $setYTDstart);
					
					$aYTD[date('Y-m-d', $currentMonth)] = $YTD;
					
					//SP Month Return
					$spMonthReturn = (($endSpMonthPrice - $prevSPprice) / $prevSPprice)*100;
					
					//SP YTD
					$spYTD = (($endSpMonthPrice - $setSpYTDstart ) / $setSpYTDstart)*100;
					
					
					$aSpYTD[date('Y-m-d', $currentMonth)] = array(
						'spYTD'		=> $spYTD,
						'spYTDr'	=> number_format($spYTD,2),
						'spStart'	=> $setSpYTDstart,
						'spEnd'		=> $endSpMonthPrice
					);
					
					//add values to array for printing details
					$aMembersWorking[$memberID][$fundID][date('m/d/Y',$currentMonth)] = array(
						'dateRead'		=> date('Y/m/d', $timestamp),
						'price'			=> $price,
						'unix_time'		=> $timestamp,
						'return'		=> $monthReturn,
						'YTD'			=> $YTD,
						'spReturn'		=> $spMonthReturn,
						'spYTD'			=> $spYTD
					);
					
					//START DB insert
					$query = "
						INSERT INTO ".$_SESSION['fund_mtm_table']." (
							fund_id,
							member_id,
							unix_date,
							value,
							YTD,
							sp_price,
							sp_value,
							spYTD,
							timestamp
						) VALUES (
							:fund_id,
							:member_id,
							:unix_date,
							:value,
							:YTD,
							:sp_price,
							:sp_month_return,
							:spYTD,
							UNIX_TIMESTAMP()
						)
					";
					try{
					$rsInsertMTM = $mLink->prepare($query);
						$aValues = array(
							':fund_id' 			=> $fundID,
							':member_id'		=> $memberID,
							':unix_date'		=> $timestamp,
							':value'			=> $monthReturn,
							':YTD'				=> $YTD,
							':sp_price'			=> $endSpMonthPrice,
							':sp_month_return'	=> $spMonthReturn,
							':spYTD'			=> $spYTD
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsInsertMTM->execute($aValues);
					}
					
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					//END DB insert
					
					
					//set previous value for S&P 
					$prevSPprice = $aSP500[date('Y-m-d',$timestamp)];
				}
				
				//set previous value for fund price
				$prevMonthPrice = $price;

				//increment counter
				$loopCnt++;
				
				
					
			}
			//END LOOP THROUGH PRICING RECORDS
			
			//unset pricing array
			unset($aMembersWorking[$memberID][$fundID]['pricing']);
			
			/*echo '<pre>'.$fundID.'<br />';
			
			//print_r($aSP500);
			
			print_r($aSpYTD);
			
			foreach($aFundPrice as $date=>$price){
	
				echo $date.' | '.date('Y-m-d', $date).' | '.$price.'<br />';
					
			}
			echo '</pre>';	*/		
				
		}//END loop through funds and get calculated data
		
	}//end for each aMembers

	/*echo '<pre>';
	foreach($aFundPrice as $date=>$price){
		
		echo $date.' | '.date('Y-m-d', $date).' | '.$price.'<br />';
			
	}
	echo '</pre>';*/
	echo 'done';
	
	/*
	echo '<pre>';
	//echo $query2;
	print_r($aDates);
	print_r($aMembers);
	print_r($aMembersWorking);
	//print_r($aSP500);
	echo '</pre>';
*/
}
?>
