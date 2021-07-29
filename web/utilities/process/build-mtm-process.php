<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

require_once("../../includes/system-functions.php");

$process = $_REQUEST['process'];

set_time_limit(3600);

if($process == "2"){
	
	$member 	= $_REQUEST['member'];
	
	
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
		
		//Get the passed member ID
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
	
	//get pricing and add to array
	foreach($aMembers as $memberID=>$aMember){
		
		foreach($aMember['funds'] as $key=>$fundID){
			unset($aMembers[$memberID]['funds'][$key]);
			
			$query = "
				SELECT price, date
				FROM ".$_SESSION['fund_pricing_table']."
				WHERE fund_id=:fund_id
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
			
			$aFundPrice = array();
			while($price = $rsPrice->fetch(PDO::FETCH_ASSOC)){
				$aFundPrice[$price['date']] = $price['price'];
			}
			
			//$aMembers[$memberID]['pricing'][$fundID] = $aFundPrice;
			
			$aMembers[$memberID]['funds'][$fundID]['pricing'] = $aFundPrice;
			
				
		}
		
	}
	
	
	//Get Years and add to array
	foreach($aMembers[$memberID]['funds'] as $fundID=>$aFundInfo){
		
		
		$aYears = array();
		
		foreach($aFundInfo['pricing'] as $date=>$price){
			if(in_array(substr($date, 0, 4), $aYears)){
				
			}else{
				$aYears[] = substr($date, 0, 4);
			}
		}
		
		//$aMembers[$memberID]['years'][$fundID] = $aYears;
		$aMembers[$memberID]['funds'][$fundID]['years'] = $aYears;
		
		foreach($aYears as $key=>$year){
			$aProcess[$memberID][$fundID][$year] = array(
				'01' => NULL,
				'02' => NULL,
				'03' => NULL,
				'04' => NULL,
				'05' => NULL,
				'06' => NULL,
				'07' => NULL,
				'08' => NULL,
				'09' => NULL,
				'10' => NULL,
				'11' => NULL,
				'12' => NULL
			);
		}
			
	}
	
	//Get price data for each month
	foreach($aMembers[$memberID]['funds'] as $fundID=>$aFundInfo){
		$aStartDays = array('01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31');
		
		$aMonths = array('01','02','03','04','05','06','07','08','09','10','11','12');
		
		foreach($aFundInfo['pricing'] as $date=>$price){
			
			
			$month = substr($date, 4, 2);
			$day 	= substr($date, 6, 2);
			$year	= substr($date,0, 4);
			
			//echo $month;
			
			//foreach 1
			foreach($aMonths as $key=>$theMonth){
				
				if($month != $setMonth){
					if($month == $theMonth){
						
						$setDay = '';
						
						foreach($aStartDays as $key=>$theDay){
							
							if($day != $setDay){
								if($day == $theDay){
									//echo $fundID.' | '.$month.'-'.$day.' | '. $date .' ~~ ';
									
									$aProcess[$memberID][$fundID][$year][$month]['start'] = $price;
									$setDay = $day;
								}
								
							}
								
						}
							
						//echo $fundID.' | '.$month.' '. $date .' ~~ ';
						$setMonth = $month;
					}else{
						
					}
				}
			}//foreach 1
			
			
		}//for each pricing record
	}
	
	
	//Get price data for each month
	foreach($aMembers[$memberID]['funds'] as $fundID=>$aFundInfo){
		$aEndDays = array('31','30','29','28','27','26','25','24','23','22','21','20','19','18','17','16','15','14','13','12','11','10','09','08','07','06','05','04','03','02','01');
		$aMonths = array('01','02','03','04','05','06','07','08','09','10','11','12');
		
		$aPricing = $aFundInfo['pricing'];
		
		$aPricing = array_reverse($aPricing, true);
		
		$setMonth = '';
		
		foreach($aPricing as $date=>$price){
			
			//echo $date.' | '.$price;
			
			$month = substr($date, 4, 2);
			$day 	= substr($date, 6, 2);
			$year	= substr($date,0, 4);
			
			
			
			//foreach 1
			foreach($aMonths as $key=>$theMonth){
				
				
				if($month != $setMonth){
					
					if($month == $theMonth){
						
						$setDay = '';
						foreach($aEndDays as $key=>$theDay){
							
							if($day != $setDay){
								
								
								if($day == $theDay){
									
									$aProcess[$memberID][$fundID][$year][$month]['end'] = $price;
									$setDay = $day;
									
								}
								
							}
								
						}
							
						$setMonth = $month;
					}else{
						
					}
				}
			}//foreach 1
			
			
		}//for each pricing record
	}
	
	
	
	//START THE CALCULATIONS
	foreach($aProcess as $memberID=>$aFunds){
		
		
		foreach($aFunds as $fundID=>$aYears){
			
			
			
			
			foreach($aYears as $year=>$aMonths){
				
				
				foreach($aMonths as $month=>$aStartEnd){
					
					if($aStartEnd != NULL){
						$start 	= $aStartEnd['start'];
						$end	= $aStartEnd['end'];
						
						
						
						$monthReturn = ((($end - $start)/$start) * 100);
						$monthReturnFormat = number_format($monthReturn, 2, '.', ',').'%';
						
						$aProcess[$memberID][$fundID][$year][$month]['monthlyReturn'] = $monthReturn;
						$aProcess[$memberID][$fundID][$year][$month]['monthlyReturnFormat'] = $monthReturnFormat;
						
						$query = "
							INSERT INTO ".$_SESSION['fund_mtm_table']." (
								fund_id,
								member_id,
								unix_date,
								value,
								timestamp
							) VALUES (
								:fund_id,
								:member_id,
								:unix_date,
								:value,
								UNIX_TIMESTAMP()
							)
						";
						try{
						$rsInsertMTM = $mLink->prepare($query);
							$aValues = array(
								':fund_id' 		=> $fundID,
								':member_id'	=> $memberID,
								':unix_date'	=> mktime(10, 0, 0, $month, 1, $year),
								':value'		=> $monthReturn
							);
							$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
							$rsInsertMTM->execute($aValues);
						}
						
						catch(PDOException $error){
							// Log any error
							file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
						}
						
					}
					
					
					
				}
				
				
					
			}
			
			
				
		}
		
		
	}
	
	echo 'done';
	
	/*foreach($aProcess as $memberID=>$aFunds){
		
		echo '<h1>'.$memberID.'</h1>';
		
		foreach($aFunds as $fundID=>$aYears){
			
			echo '<table width="100%" border="1">';
			echo '
				<tr>
					<th>'.$fundID.'</th>
					<th>January</th>
					<th>February</th>
					<th>March</th>
					<th>April</th>
					<th>May</th>
					<th>June</th>
					<th>July</th>
					<th>August</th>
					<th>September</th>
					<th>October</th>
					<th>November</th>
					<th>December</th>
				</tr>
			';
			$aYears = array_reverse($aYears, true);
			
			foreach($aYears as $year=>$aMonths){
				
				echo '<tr><td>'.$year.'</td>';
				foreach($aMonths as $month=>$aStartEnd){
					
					
						
						$start 	= $aStartEnd['start'];
						$end	= $aStartEnd['end'];
						
						
						$monthReturn = ((($end - $start)/$start) * 100);
						$monthReturnFormat = number_format($monthReturn, 2, '.', ',').'%';
						
						if($monthReturnFormat == '0.00%'){
							$show = '-';	
						}else{
							$show = $monthReturnFormat;	
						}
						
						echo '<td>'.$show.'</td>';
					
					
				}
				
				echo '</tr>';
					
			}
			
			echo '</table><br /><br />';	
				
		}
		
		?>
        <hr /><br />
        <?php	
	}*/
	
	
	/*echo '<pre>';
	print_r($aProcess);
	echo $error;
	echo $cnt;
	print_r($aMembers);
	echo '</pre>';*/
}


//GET S&P INFO
$query = "
	SELECT *
	FROM stock_index_history
	WHERE `index`='^GSPC'
	ORDER BY unix_date ASC
";
try{
$rsGetSP = $mLink->prepare($query);
	$aValues = array(
		':date' 	=> $year.'-'.$month.'-%'
		
	);
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

if($process == "1"){
	
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
	
	//get pricing and add to array
	foreach($aMembers as $memberID=>$aMember){
		
		$aMembersWorking 	= array();
		
		foreach($aMember['funds'] as $key=>$fundID){
			
			unset($aMembers[$memberID]['funds'][$key]);
			
			$query = "
				SELECT price, date
				FROM ".$_SESSION['fund_pricing_table']."
				WHERE fund_id=:fund_id
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
			
			$aFundPrice = array();
			while($price = $rsPrice->fetch(PDO::FETCH_ASSOC)){
				$aFundPrice[$price['date']] = $price['price'];
			}
			
			
			$aMembersWorking[$memberID]['funds'][$fundID]['pricing'] = $aFundPrice;
			
				
		}
		
		//Initialize array, helps prevent memory run off
		$aProcess = array();
		
		//Find the years in which the fund has been active and add to array
		foreach($aMembersWorking[$memberID]['funds'] as $fundID=>$aFundInfo){
			
			
			$aYears = array();
			
			foreach($aFundInfo['pricing'] as $date=>$price){
				if(in_array(substr($date, 0, 4), $aYears)){
					
				}else{
					$aYears[] = substr($date, 0, 4);
				}
			}
			
			//$aMembers[$memberID]['years'][$fundID] = $aYears;
			$aMembersWorking[$memberID]['funds'][$fundID]['years'] = $aYears;
			
			foreach($aYears as $key=>$year){
				$aProcess[$memberID][$fundID][$year] = array(
					'01' => NULL,
					'02' => NULL,
					'03' => NULL,
					'04' => NULL,
					'05' => NULL,
					'06' => NULL,
					'07' => NULL,
					'08' => NULL,
					'09' => NULL,
					'10' => NULL,
					'11' => NULL,
					'12' => NULL
				);
			}
				
		}
		
		//Get price data for each month
		foreach($aMembersWorking[$memberID]['funds'] as $fundID=>$aFundInfo){
			$aStartDays = array('01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31');
			
			$aMonths = array('01','02','03','04','05','06','07','08','09','10','11','12');
			
			foreach($aFundInfo['pricing'] as $date=>$price){
				
				
				$month = substr($date, 4, 2);
				$day 	= substr($date, 6, 2);
				$year	= substr($date,0, 4);
				
				//echo $month;
				
				//GET S&P INFO
				/*$query = "
					SELECT *
					FROM stock_index_history
					WHERE `index`='^GSPC' AND `date` like :date
					ORDER BY unix_date ASC
					LIMIT 1
				";
				try{
				$rsGetSP = $mLink->prepare($query);
					$aValues = array(
						':date' 	=> $year.'-'.$month.'-%'
						
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsGetSP->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				while($sp = $rsGetSP->fetch(PDO::FETCH_ASSOC)){
					
					$startSP = $sp['close'];
					
				}*/
				
				//foreach 1
				foreach($aMonths as $key=>$theMonth){
					
					if($month != $setMonth){
						if($month == $theMonth){
							
							$setDay = '';
							
							foreach($aStartDays as $key=>$theDay){
								
								if($day != $setDay){
									if($day == $theDay){
										//echo $fundID.' | '.$month.'-'.$day.' | '. $date .' ~~ ';
										
										$aProcess[$memberID][$fundID][$year][$month]['start'] = $price;
										//$aProcess[$memberID][$fundID][$year][$month]['startSP'] = $startSP;
										
										
										//START | Get S&P500 -----------------------------------------------------+
										if($aSP500[$year.'-'.$month.'-'.$day] == ''){
											$startSP = $aSP500[$year.'-'.$month.'-0'.($day + 1)];
											$dateTest = $year.'-'.$month.'-0'.($day + 1);
											
											if($aSP500[$year.'-'.$month.'-0'.($day + 1)] == ''){
												$startSP = $aSP500[$year.'-'.$month.'-0'.($day + 2)];
												$dateTest = $year.'-'.$month.'-0'.($day + 2);
												
												if($aSP500[$year.'-'.$month.'-0'.($day + 2)] == ''){
													$startSP = $aSP500[$year.'-'.$month.'-0'.($day + 3)];
													$dateTest = $year.'-'.$month.'-0'.($day + 3);
												}
											}
												
										}else{
											$startSP = $aSP500[$year.'-'.$month.'-'.$day];	
											$dateTest = $year.'-'.$month.'-'.$day;
										}
										
										$aProcess[$memberID][$fundID][$year][$month]['startSP'] = $startSP;
										//$aProcess[$memberID][$fundID][$year][$month]['SPDate'] = $dateTest;
										
										//END | Get S&P500 -----------------------------------------------------+
										
										$setDay = $day;
									}
									
								}
									
							}
								
							//echo $fundID.' | '.$month.' '. $date .' ~~ ';
							$setMonth = $month;
						}else{
							
						}
					}
				}//foreach 1
				
				
			}//for each pricing record
		}
		
		
		//Get price data for each month
		foreach($aMembersWorking[$memberID]['funds'] as $fundID=>$aFundInfo){
			$aEndDays = array('31','30','29','28','27','26','25','24','23','22','21','20','19','18','17','16','15','14','13','12','11','10','09','08','07','06','05','04','03','02','01');
			$aMonths = array('01','02','03','04','05','06','07','08','09','10','11','12');
			
			$aPricing = $aFundInfo['pricing'];
			
			$aPricing = array_reverse($aPricing, true);
			
			$setMonth = '';
			
			foreach($aPricing as $date=>$price){
				
				//echo $date.' | '.$price;
				
				$month = substr($date, 4, 2);
				$day 	= substr($date, 6, 2);
				$year	= substr($date,0, 4);
				
				//GET S&P INFO
				/*$query = "
					SELECT *
					FROM stock_index_history
					WHERE `index`='^GSPC' AND `date` like :date
					ORDER BY unix_date DESC
					LIMIT 1
				";
				try{
				$rsGetSP = $mLink->prepare($query);
					$aValues = array(
						':date' 	=> $year.'-'.$month.'-%'
						
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsGetSP->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				while($sp = $rsGetSP->fetch(PDO::FETCH_ASSOC)){
					
					$endSP = $sp['close'];
					
				}*/
				
				//foreach 1
				foreach($aMonths as $key=>$theMonth){
					
					
					if($month != $setMonth){
						
						if($month == $theMonth){
							
							$setDay = '';
							foreach($aEndDays as $key=>$theDay){
								
								if($day != $setDay){
									
									
									if($day == $theDay){
										
										$aProcess[$memberID][$fundID][$year][$month]['end'] = $price;
										//$aProcess[$memberID][$fundID][$year][$month]['endSP'] = $endSP;
										
										//START | Get S&P500 -----------------------------------------------------+
										if($aSP500[$year.'-'.$month.'-'.$day] == ''){
											$endSP = $aSP500[$year.'-'.$month.'-'.($day - 1)];
											//$dateTest = $year.'-'.$month.'-'.($day - 1);
											
											if($aSP500[$year.'-'.$month.'-'.($day - 1)] == ''){
												$endSP = $aSP500[$year.'-'.$month.'-0'.($day - 2)];
												//$dateTest = $year.'-'.$month.'-'.($day - 2);
												
												if($aSP500[$year.'-'.$month.'-'.($day - 2)] == ''){
													$endSP = $aSP500[$year.'-'.$month.'-'.($day - 3)];
													//$dateTest = $year.'-'.$month.'-'.($day - 3);
												}
											}
												
										}else{
											$endSP = $aSP500[$year.'-'.$month.'-'.$day];	
											//$dateTest = $year.'-'.$month.'-'.$day;
										}
										
										
										$aProcess[$memberID][$fundID][$year][$month]['endSP'] = $endSP;
										//$aProcess[$memberID][$fundID][$year][$month]['SPDate'] = $dateTest;
											
										//END | Get S&P500 ------------------------------------------------------+
										
										$setDay = $day;
										
									}
									
								}
									
							}
								
							$setMonth = $month;
						}else{
							
						}
					}
				}//foreach 1
				
				
			}//for each pricing record
		}
		
		//START THE CALCULATIONS
		foreach($aProcess as $memberID=>$aFunds){
			
			
			foreach($aFunds as $fundID=>$aYears){
				
				
				
				
				foreach($aYears as $year=>$aMonths){
					
					$setYTDmonth = '';
					$setYTDsp = '';
					
					foreach($aMonths as $month=>$aStartEnd){
						
						if($aStartEnd != NULL){
							
							
							$startSP = $aStartEnd['startSP'];
							$endSP 	= $aStartEnd['endSP'];
							
							$start 	= $aStartEnd['start'];
							$end	= $aStartEnd['end'];
							
							if($setYTDmonth == ''){
								$setYTDmonth = $month;
								
								$startYTD = $start;
								
								$startYTDsp = $startSP;
							}
							
							
							
							
							$monthReturn = ((($end - $start)/$start) * 100);
							$monthReturnFormat = number_format($monthReturn, 2, '.', ',').'%';
							
							$YTD = (($end - $startYTD)/$startYTD);
							$spYTD = (($endSP - $startYTDsp)/$startYTDsp);
							
							$spMonthReturn = ((($endSP - $startSP)/$startSP) * 100);
							
							
							//$aProcess[$memberID][$fundID][$year][$month]['startYTDsp'] = $startYTDsp;
							//$aProcess[$memberID][$fundID][$year][$month]['endYTDsp'] = $endSP;
							
							//$aProcess[$memberID][$fundID][$year][$month]['startYTD'] = $startYTD;
							//$aProcess[$memberID][$fundID][$year][$month]['endYTD'] = $end;
							
							//$aProcess[$memberID][$fundID][$year][$month]['setYTDmonth'] = $setYTDmonth;
							
							
							
							$aProcess[$memberID][$fundID][$year][$month]['YTD'] = $YTD;
							$aProcess[$memberID][$fundID][$year][$month]['spYTD'] = $spYTD;
							
							$aProcess[$memberID][$fundID][$year][$month]['monthlyReturn'] = $monthReturn;
							$aProcess[$memberID][$fundID][$year][$month]['monthlyReturnFormat'] = $monthReturnFormat;
							
							$query = "
								INSERT INTO ".$_SESSION['fund_mtm_table']." (
									fund_id,
									member_id,
									unix_date,
									value,
									YTD,
									sp_value,
									spYTD,
									timestamp
								) VALUES (
									:fund_id,
									:member_id,
									:unix_date,
									:value,
									:YTD,
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
									':unix_date'		=> mktime(10, 0, 0, $month, 1, $year),
									':value'			=> $monthReturn,
									':YTD'				=> $YTD,
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
							
						}
						
						
						
					}
					
					
						
				}
				
				
					
			}
			
			
		}
		
		$aMembers = array();
	}//end foreach member loop

	
	
	/*echo '<pre>';
	//print_r($aSP500);
	print_r($aProcess);
	echo $error;
	echo $cnt;
	print_r($aMembers);
	echo '</pre>';*/
	
	echo 'done';
	
}

?>