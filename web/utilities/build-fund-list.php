<?php

session_start();

// Load debug & error logging functions
//require_once("../includes/system-debug-functions.php");

//Connect to DB
require_once("../../secure/dbconnect.php");

require_once("../includes/system-functions.php");

$rankedFunds 	= 0;
$nonRankedFunds	= 0;
$noFunds		= 0;

$sp10			= 7.24;
$sp5			= 16.37;
$sp3			= 11.16;
$sp1			= 15.43;

$query = "
	SELECT ManagerKey
	FROM temp_member
	
";
try{
	$rsManagers = $mLink->prepare($query);
	$aValues = array(
		
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsManagers->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}
$aManagers = array();
while($manager=$rsManagers->fetch(PDO::FETCH_ASSOC)){
	
	$aManagers[] = $manager['ManagerKey'];
	
}


function rankOrder10($a, $b) {
  return $b["rtn10"] - $a["rtn10"];
}

foreach($aManagers as $key=>$managerKey){
	
	$aFunds = array();
	
	$query = "
		SELECT *
		FROM temp_nav
		WHERE ManagerKey=:managerKey
	";
	try{
		$rsNavs = $mLink->prepare($query);
		$aValues = array(
			'managerKey' => $managerKey
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsNavs->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	while($nav=$rsNavs->fetch(PDO::FETCH_ASSOC)){
		
		$nameFirst		= $nav['FirstName'];
		$nameLast		= $nav['LastName'];
		$nameLogin		= $nav['Login'];
		
		$group			= 0;
		$rank			= $nav['Rank'];
		
		if($rank == '0'){
			$level = 'noRank';	
		}else{
			$level = 'ranked';	
		}
		
		$return10	= $nav['Rtn_10'];
		$return5	= $nav['Rtn_5'];
		$return3	= $nav['Rtn_3'];
		$return1	= $nav['Rtn_1'];
		
		
		
		if($level == "ranked"){
			if($rank > 80){
				$group = 10;
			}elseif($rank > 60 && $rank < 90){
				$group = 5;
			}elseif($rank > 40 && $rank < 70){
				$group = 3;
			}else{
				$group = 1;
			}
		}else{
			
			
			if($return10 != 0){
				$group	= 10;
			}elseif($return5 != 0){
				$group = 5;
			}elseif($return3 != 0){
				$group = 3;
			}elseif($return1 != 0){
				$group = 1;
			}
		}
		
		$aFunds[$level][$group][$nav['Rank']][$nav['FundKey']]= array(
			'rank'			=> $nav['Rank'],
			'fundSymbol'	=> $nav['FundSymbol'],
			'fundName'		=> $nav['FundName'],
			'campaign'		=> $nav['Campaign'],
			'rtn1'			=> $nav['Rtn_1'],
			'rtn3'			=> $nav['Rtn_3'],
			'rtn5'			=> $nav['Rtn_5'],
			'rtn10'			=> $nav['Rtn_10'],
		);
			
	}
	
	
	if(!empty($aFunds['ranked'])){
		#member has ranked funds, use higest ranked
		
		#sort rank periods highest to lowest
		krsort($aFunds['ranked']);
		
		#loop through each period
		foreach($aFunds['ranked'] as $period=>$rankLevels){
			
			#sort ranks highest to lowest
			krsort($rankLevels);
			
						
			#sort each fund based on return for period
			switch($period){
				case '10':
					foreach($rankLevels as $rankLevel=>$funds){
						
						uasort(
							$funds, 
							function($a, $b) {
								$result = 0;
								if ($a["rtn10"] < $b["rtn10"]) {
									$result = 1;
								} else if ($a["rtn10"] > $b["rtn10"]) {
									$result = -1;
								}
								return $result; 
							}
						);
						
						$rankLevels[$rankLevel] = $funds;
						
					}
				break;
				
				case '5':
					foreach($rankLevels as $rankLevel=>$funds){
						
						uasort(
							$funds, 
							function($a, $b) {
								$result = 0;
								if ($a["rtn5"] < $b["rtn5"]) {
									$result = 1;
								} else if ($a["rtn5"] > $b["rtn5"]) {
									$result = -1;
								}
								return $result; 
							}
						);
						
						$rankLevels[$rankLevel] = $funds;
						
					}
				break;
				
				case '3':
					foreach($rankLevels as $rankLevel=>$funds){
						
						uasort(
							$funds, 
							function($a, $b) {
								$result = 0;
								if ($a["rtn3"] < $b["rtn3"]) {
									$result = 1;
								} else if ($a["rtn3"] > $b["rtn3"]) {
									$result = -1;
								}
								return $result; 
							}
						);
						
						$rankLevels[$rankLevel] = $funds;
						
					}
				break;
				
				case '1':
					foreach($rankLevels as $rankLevel=>$funds){
						
						uasort(
							$funds, 
							function($a, $b) {
								$result = 0;
								if ($a["rtn1"] < $b["rtn1"]) {
									$result = 1;
								} else if ($a["rtn1"] > $b["rtn1"]) {
									$result = -1;
								}
								return $result; 
							}
						);
						
						$rankLevels[$rankLevel] = $funds;
						
					}
				break;
				
			}
			
			$aFunds['ranked'][$period] = $rankLevels;
			
			
			
		}
		
		/*echo '<pre>';
		print_r($aFunds['ranked']);
		echo '</pre>';*/
		
		$rankedFunds++;
		
	}elseif(!empty($aFunds['noRank'])){
		
		#member does not have ranked funds, get highest AAR 
		
		#sort rank periods highest to lowest
		krsort($aFunds['noRank']);
		
		#loop through each period
		foreach($aFunds['noRank'] as $period=>$rankLevels){
			
			#sort ranks highest to lowest
			krsort($rankLevels);
			
			
			
						
			#sort each fund based on return for period
			switch($period){
				case '10':
					foreach($rankLevels as $rankLevel=>$funds){
						
						foreach($funds as $fundKey=>$fundInfo){
					
							$rtn10Alpha = $fundInfo['rtn10'] - $sp10;
							$rtn5Alpha	= $fundInfo['rtn5'] - $sp5;
							$rtn3Alpha	= $fundInfo['rtn3'] - $sp3;
							$rtn1Alpha	= $fundInfo['rtn1'] - $sp1;
							
							$rtnAverageAlpha = (($rtn10Alpha + $rtn5Alpha + $rtn3Alpha + $rtn10Alpha) / 4);
							
							$funds[$fundKey]['AverageRtnAlpha'] = $rtnAverageAlpha;
						}
						
						uasort(
							$funds, 
							function($a, $b) {
								$result = 0;
								if ($a["rtn10"] < $b["rtn10"]) {
									$result = 1;
								} else if ($a["rtn10"] > $b["rtn10"]) {
									$result = -1;
								}
								return $result; 
							}
						);
						
						$rankLevels[$rankLevel] = $funds;
						
						
					}
				break;
				
				case '5':
					foreach($rankLevels as $rankLevel=>$funds){
						
						foreach($funds as $fundKey=>$fundInfo){
					
							$rtn5Alpha	= $fundInfo['rtn5'] - $sp5;
							$rtn3Alpha	= $fundInfo['rtn3'] - $sp3;
							$rtn1Alpha	= $fundInfo['rtn1'] - $sp1;
							
							$rtnAverageAlpha = (($rtn5Alpha + $rtn3Alpha + $rtn10Alpha) / 3);
							
							$funds[$fundKey]['AverageRtnAlpha'] = $rtnAverageAlpha;
						}
						
						uasort(
							$funds, 
							function($a, $b) {
								$result = 0;
								if ($a["rtn5"] < $b["rtn5"]) {
									$result = 1;
								} else if ($a["rtn5"] > $b["rtn5"]) {
									$result = -1;
								}
								return $result; 
							}
						);
						
						$rankLevels[$rankLevel] = $funds;
						
					}
				break;
				
				case '3':
					foreach($rankLevels as $rankLevel=>$funds){
						
						foreach($funds as $fundKey=>$fundInfo){
					
							
							$rtn3Alpha	= $fundInfo['rtn3'] - $sp3;
							$rtn1Alpha	= $fundInfo['rtn1'] - $sp1;
							
							$rtnAverageAlpha = (($rtn3Alpha + $rtn10Alpha) / 2);
							
							$funds[$fundKey]['AverageRtnAlpha'] = $rtnAverageAlpha;
						}
						
						uasort(
							$funds, 
							function($a, $b) {
								$result = 0;
								if ($a["rtn3"] < $b["rtn3"]) {
									$result = 1;
								} else if ($a["rtn3"] > $b["rtn3"]) {
									$result = -1;
								}
								return $result; 
							}
						);
						
						$rankLevels[$rankLevel] = $funds;
						
					}
				break;
				
				case '1':
					foreach($rankLevels as $rankLevel=>$funds){
						
						foreach($funds as $fundKey=>$fundInfo){
					
							
							
							$rtn1Alpha	= $fundInfo['rtn1'] - $sp1;
													
							$funds[$fundKey]['AverageRtnAlpha'] = $rtn1Alpha;
							$funds[$fundKey]['SP'] = $sp1;
						}
						
						uasort(
							$funds, 
							function($a, $b) {
								$result = 0;
								if ($a["rtn1"] < $b["rtn1"]) {
									$result = 1;
								} else if ($a["rtn1"] > $b["rtn1"]) {
									$result = -1;
								}
								return $result; 
							}
						);
						
						$rankLevels[$rankLevel] = $funds;
						
					}
				break;
				
			}
			
			$aFunds['noRank'][$period] = $rankLevels;
			
			
		}
		
		/*echo '<pre>';
		print_r($aFunds['noRank']);
		echo '</pre>';*/
		
		$nonRankedFunds++;
	}else{
		#member does not have funds do nothing	
		
		$noFundManagers[] = $managerKey;
		
		$noFunds++;
	}
	
	
	#store off fund
	$fundSet = false;
	
	foreach($aFunds as $rankType=>$periods){
		
		if($fundSet == true){
			break;	
		}
		
		foreach($periods as $period=>$rankLevels){
			
			if($fundSet == true){
				break;	
			}
			
			foreach($rankLevels as $rankLevel=>$funds){
				
				if($fundSet == true){
					break;	
				}
				
				foreach($funds as $fundKey=>$aFundInfo){
					
					if($fundSet == true){
						break;	
					}
					
					$query = "
						UPDATE temp_member
						SET 
							FundKey=:fundKey, 
							RankedFund=:rankedFund, 
							Campaign=:campaign, 
							FundSymbol=:fundSymbol, 
							FundName=:fundName, 
							Rtn_10=:rtn10, 
							Rtn_5=:rtn5, 
							Rtn_3=:rtn3, 
							Rtn_1=:rtn1, 
							NameFirst=:nameFirst, 
							NameLast=:nameLast
						WHERE ManagerKey=:managerKey
					";
					try{
						$rsUpdate = $mLink->prepare($query);
						$aValues = array(
							':managerKey' 	=> $managerKey,
							':fundKey'		=> $fundKey,
							':rankedFund'	=> $aFundInfo['rank'],
							':campaign'		=> $aFundInfo['campaign'],
							':fundSymbol'	=> $aFundInfo['fundSymbol'],
							':fundName'		=> $aFundInfo['fundName'],
							':rtn10'			=> $aFundInfo['rtn10'],
							':rtn5'			=> $aFundInfo['rtn5'],
							':rtn3'			=> $aFundInfo['rtn3'],
							':rtn1'			=> $aFundInfo['rtn1'],
							':nameFirst'		=> $nameFirst,
							':nameLast'		=> $nameLast
							
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsUpdate->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
					//echo $preparedQuery;
					
					$fundSet = true;
					
					
						
				}#end foreach funds
									
			}#end foreach rankLevels
				
		}#end foreach periods
			
	}#end foreach aFunds
	
	echo '<pre>';
		//print_r($aFunds);
		
		echo $managerKey;
		echo '<br />end <br />';
	echo '</pre>';
		
}


echo 'Hello: This is a Manager Process<br /><br />';

echo '<pre>';
echo 'Ranked Funds: '.$rankedFunds.' | Non-Ranked Funds: '.$nonRankedFunds.' | No Funds: '.$noFunds.'<br />';

echo count($aManagers);
//print_r($noFundManagers);
echo '</pre>';

?>