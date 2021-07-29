<?php
//RECENT RETURNS PORTLET
/*
Supporting files:
	AJAX		- process/ajax/portlets/fund-recent-returns-ajax.php
	JAVASCRIPT	- includes/scripts/portlets/fund-recent-returns.js.php
	HTML		- THIS DOCUMENT includes/portlets/fund-recent-returns.php
	
*/

//portVar1 = Fund ID
//portVar2 = NOT SET
//portVar3 = NOT SET

$portletType = "fund-recent-returns";

if(!isset($fundID)) {
	//If FUND ID is not set, portlet is on dashboard
	include('fund-symbol-query.php');
	
	$recentFundSym	= 'recentFundSym_'.$fundInfo['fund_symbol'].'';
	$$recentFundSym = $fundInfo['fund_symbol'];
	
	$recentFundID = 'recentFundID_'.$$fundSym.'';
	$$recentFundID = $fundInfo['fund_id'];
	
	//Hide The "Add To Dashboard symbol"
	$disableFRR = 2;
	
	
}else{
	//If FUND ID is Set, portlet is on funds pages
	$disableFRR = 0;
	
	//Query Dashboard columns to see if portlet already exists on dashboard
	include('check-dash-query.php');
	
	//Set New Variables so not to overwrite eachother
	if($cnt == 0){
		//Show add to dash
		$disableFRR = 0;	
	}else{
		//Hide add to dash
		$disableFRR = 1;	
	}
	
	$fundSym = get_funds($mLink, $fundID, 'fundSymbol');
	$recentFundSym = 'recentFundSym_'.$fundSym.'';
	$$recentFundSym = $fundSym;
	
	
	$recentFundID = 'recentFundID_'.$$fundSym.'';
	$$recentFundID = $fundID;
}

if(!isset($fundID)){
	$recentFundSymLink = '<a href="?page=03-01-00-001&fund='.$$recentFundID.'">'.$$recentFundSym.'</a>';
	//$returnsFundID = $portVar1;
}else{ 
	$recentFundSymLink = $$recentFundSym; 
}

//Set fund id as an array value
$recentArray[] = $$recentFundID;

?>
<!-- START RECENT RETURNS-->
<div class="portlet" id="<?php echo $portletID; ?>">
    <div class="portlet-title handle" <?php echo $addFundColor; ?>>
    	<div class="caption">
        	<i class="icon-bell"></i>
			<?php echo $recentFundSymLink; ?> | Recent Returns
        </div>
        <div class="tools" id="update-button-<?php echo $portletID; ?>">
        	<?php if($pageID != "04-00-00-001"/*community public profile*/){?>
            <a href="javascript:void(0);" 
				<?php if($disableFRR == 0){?>onClick="addToDash('portlet_id=<?php echo $portletType;?>~<?php echo $fundID; ?>~0~0','<?php echo $portletID;?>');" title="Add To Dashboard" class="addtodashboard balloon"<?php }?>
                <?php if($disableFRR == 1){?>title="Portlet On Dashboard" class="addtodashboard balloon disabled"<?php }?>
                <?php if($disableFRR == 2){?>onClick="removeFromDash('portlet_id=<?php echo $portletID; ?>','<?php echo $undoColumns;?>');" title="Remove From Dashboard" class="remove balloon"<?php }?>>
            </a>
            <a href="" class="reload balloon" onCLick="refreshReturns('<?php echo $$recentFundID;?>')" title="Refresh Portlet"></a>
            <?php } ?>
            <a href="" class="collapse balloon" title="Expand/Collapse Portlet"></a>
        </div>
    </div>
    <div class="portlet-body" id="chats">
    	
        <?php
			
		$query = "
			SELECT unix_date, price, date
			FROM ".$_SESSION['fund_pricing_table']."
			WHERE fund_id=:fund_id
			ORDER BY unix_date DESC
			LIMIT 1
		";
		try{
			$rsGetStartDate = $mLink->prepare($query);
			$aValues = array(
				':fund_id' 	=> $$recentFundID
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetStartDate->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$fundPrice			= $rsGetStartDate->fetch(PDO::FETCH_ASSOC);
		
		$currentPrice		= $fundPrice['price'];
		$getDate 			= $fundPrice['date'];
		
		$lastDay 			= mktime(11, 0, 0, substr($getDate, 4, 2), substr($getDate, 6, 2), substr($getDate, 0, 4));
		$lastWeek			= checkForMarketDate(strtotime('-7 day', $lastDay), $mLink);
		$lastMonth			= checkForMarketDate(strtotime('-30 day', $lastDay), $mLink);
		$lastThreeMonths	= checkForMarketDate(strtotime('-90 day', $lastDay), $mLink);
		$lastSixMonths		= checkForMarketDate(strtotime('-180 day', $lastDay), $mLink);
		$lastYear			= checkForMarketDate(strtotime('-365 day', $lastDay), $mLink);
		$lastTwoYears		= checkForMarketDate(strtotime('-730 day', $lastDay), $mLink);
		$lastThreeYears		= checkForMarketDate(strtotime('-1095 day', $lastDay), $mLink);
		$lastFiveYears		= checkForMarketDate(strtotime('-1825 day', $lastDay), $mLink);
		$lastTenYears		= checkForMarketDate(strtotime('-3650 day', $lastDay), $mLink);
		
		$AAR				= get_fund_AAR($mLink, $$recentFundID);
		$spAAR				= get_fund_AAR($mLink, $$recentFundID, 'SP');
		
		$inceptDate			= date('Ymd',get_funds($mLink, $fundID, 'trueInceptDate'));
		$unixInceptDate		= mktime(11, 0, 0, substr($inceptDate, 4, 2), substr($inceptDate, 6, 2), substr($inceptDate, 0, 4));
		$inception			= checkForMarketDate($unixInceptDate, $mLink, true);
		
		
		//echo date('Ymd',$inception);
		$fundYears			= (((($lastDay - $inception) / 60) / 60) / 24) / 365;
		$aDatePrices		= array(
			'lastDay'			=> array('date'=> date('Ymd', $lastDay), 'unix'=>$lastDay, 'currentPrice'=> $currentPrice, 'price'=>$currentPrice, 'fundLength'=>$fundYears),
			'Last 7 Days'		=> array('date'=> date('Ymd', $lastWeek), 'unix'=>$lastWeek, 'currentPrice'=> $currentPrice, 'price'=>''),
			'Last 30 Days'		=> array('date'=> date('Ymd', $lastMonth), 'unix'=>$lastMonth,'currentPrice'=> $currentPrice, 'price'=>''),
			'Last 3 Months'		=> array('date'=> date('Ymd', $lastThreeMonths), 'unix'=>$lastThreeMonths, 'currentPrice'=> $currentPrice, 'price'=>''),
			'Last 6 Months'		=> array('date'=> date('Ymd', $lastSixMonths), 'unix'=>$lastSixMonths, 'currentPrice'=> $currentPrice, 'price'=>'', 'unix'=>$lastSixMonths),
			'Last 12 Months'	=> array('date'=> date('Ymd', $lastYear), 'unix'=>$lastYear, 'currentPrice'=> $currentPrice, 'price'=>''),
			'Last 2 Years'		=> array('date'=> date('Ymd', $lastTwoYears), 'unix'=>$lastTwoYears, 'currentPrice'=> $currentPrice, 'price'=>''),
			'Last 3 Years'		=> array('date'=> date('Ymd', $lastThreeYears), 'unix'=>$lastThreeYears, 'currentPrice'=> $currentPrice, 'price'=>''),
			'Last 5 Years'		=> array('date'=> date('Ymd', $lastFiveYears), 'unix'=>$lastFiveYears, 'currentPrice'=> $currentPrice, 'price'=>''),
			'Last 10 Years'		=> array('date'=> date('Ymd', $lastTenYears), 'unix'=>$lastTenYears, 'currentPrice'=> $currentPrice, 'price'=>''),
			'Since Inception'	=> array('date'=> date('Ymd', $inception), 'unix'=>$inception, 'currentPrice'=> $currentPrice, 'price'=>''),
			'(Annualized)'		=> array('date'=> '', 'price'=>'')
		);			
		
		/*if($_REQUEST['debug'] == '1'){
		echo '<pre>';
		print_r(isMarketHoliday('1401116400', $mLink, true, true));
		if(isMarketOpen('1401116400', $mLink) == true){
			echo 'true';
		}else{
			echo 'false';
		}
		
		if(isWeekend('1401116400') == false){
			echo 'false';		
		}else{
			echo 'true';	
		}
		
		print_r(checkForMarketDate($unixInceptDate, $mLink, true, true));
		print_r($aDatePrices);
		echo '</pre>';
		}*/
		
		$aErrors 	= array();
		
		foreach($aDatePrices as $period=>$aDetails){
			
			$aDatePrices[$period]['date-read'] = date('Y-m-d h:i:s a', $aDetails['unix']);
			$dateDebug = checkForMarketDate($aDetails['unix'], $mLink, false, true);
			
			$aDatePrices[$period]['debug'] = $dateDebug;
			
			if($aDetails['price'] == ''){
				//Get fund data
				$query = "
					SELECT price, date
					FROM ".$_SESSION['fund_pricing_table']."
					WHERE fund_id=:fund_id AND date=:date
					ORDER BY unix_date DESC
					LIMIT 1
				";
				try{
					$rsGetPrice = $mLink->prepare($query);
					$aValues = array(
						':fund_id' 	=> $$recentFundID,
						':date'		=> $aDetails['date']
						
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsGetPrice->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				$fundPrice		= $rsGetPrice->fetch(PDO::FETCH_ASSOC);
				
				$navPrice	= $fundPrice['price'];
				
				if($inceptDate == $fundPrice['date']){
					 //echo 'hello';
					$inceptPrice = $navPrice;	
					//$aDatePrices[$period]['inceptPrice'] = $inceptPrice;
				}
				
				
			}else{
				$navPrice 	= $aDetails['price'];	
			}
			
			$aDatePrices[$period]['price'] = $navPrice;
			
			$return = (($currentPrice - $navPrice) / $navPrice) * 100;
			
			if($aDetails['return'] == ''){
				$aDatePrices[$period]['return'] = number_format($return, 2, '.',',');
			}
			
			//Get S&P Data
			$index = '^SP500TR';
			//$index = '^GSPC';
			
			
			
			$spDate = substr($aDetails['date'],0,4).'-'.substr($aDetails['date'],4,2).'-'.substr($aDetails['date'],6,2);
			
			$aDatePrices['debug'][$period]['sp-date'] = $spDate;
			
			
			$spDateTimestamp = mktime(8,0,0,substr($aDetails['date'],4,2),substr($aDetails['date'],6,2),substr($aDetails['date'],0,4));
			
			$aDatePrices['debug'][$period]['sp-date-timestamp'] = $spDateTimestamp;
			$getSpDate = checkForMarketDate($spDateTimestamp, $mLink);
			$aDatePrices['debug'][$period]['sp-date-adj'] = $getSpDate;
			$spDateAdj = date('Y-m-d', $getSpDate);
			
			//echo $spDate;
			$query = "
				SELECT close
				FROM ".$_SESSION['index_history_table']."
				WHERE `index`=:index AND `date`=:date
				ORDER BY unix_date DESC
				LIMIT 1
			";
			try{
				$rsGetSP = $mLink->prepare($query);
				$aValues = array(
					':date'		=> $spDateAdj,
					':index'	=> $index
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetSP->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$aErrors[] = $preparedQuery;
			
			$spPrice		= $rsGetSP->fetch(PDO::FETCH_ASSOC);
			
			$spClose	= $spPrice['close'];
			
			if(!isset($spCurrentPrice)){
				$spCurrentPrice = $spClose;
			}
			
			/*echo '<pre>';
			echo $spClose;
			echo '</pre>';*/
			
			$spReturn = (($spCurrentPrice - $spClose) / $spClose) * 100;
			//$aDatePrices[$period]['spQuery'] 	= $preparedQuery;
			//$aDatePrices[$period]['spError'] 	= $error;
			$aDatePrices[$period]['spDate'] 	= $spDate;
			$aDatePrices[$period]['spCurrentPrice'] 	= $spCurrentPrice;	
			$aDatePrices[$period]['spPrice'] 	= $spClose;	
			$aDatePrices[$period]['spReturn'] 	= number_format($spReturn, 2, '.',',');
			
			
			//Calculate Difference
			$difference = (round($return,2) - round($spReturn,2));
			$aDatePrices[$period]['difference']	= number_format($difference, 2, '.', ',');
			
			//inception period
			if($period == 'Since Inception'){
				
				//$inceptPrice = 10;
				$spInceptPrice = $spClose;
					
			}
			
			//annualized period
			if($period == '(Annualized)'){
				$base = $spCurrentPrice / $spInceptPrice;
				$exp = 1 / $fundYears;
		
				$spAAR = ((pow($base, $exp))-1)*100;
				
				$aDatePrices[$period]['spAAR'] = number_format($spAAR, 2, '.', ',');
				
				$base = $currentPrice / $inceptPrice;
				$exp = 1 / $fundYears;
		
				$AAR = ((pow($base, $exp))-1)*100;
				
				$aDatePrices[$period]['AAR'] = number_format($AAR, 2, '.', ',');
				$aDatePrices[$period]['inceptPrice'] = number_format($inceptPrice, 2, '.', ',');
				$aDatePrices[$period]['currentPrice'] = number_format($currentPrice, 2, '.', ',');
				
				$aarDifference = (round($AAR,2) - round($spAAR,2));
				
				$aDatePrices[$period]['difference']	= number_format($aarDifference, 2, '.', ',');
			}
		}
		
		if($_REQUEST['debug'] == '1'){
			echo '<pre>';
			//print_r($aErrors);
			
			echo $currentPrice.'|'.$inceptPrice.'|'.$inceptDate;
			
			print_r($aDatePrices);
			
			echo '</pre>';	
		}
			
		?>
    	
        <table class="table table-striped table-bordered table-hover">
         <thead>
            <tr>
               <th>Period</th>
               <th class="success">Returns</th>
               <th>S&amp;P 500 Returns</th>
               <th class="warning">Returns VS S&amp;P 500</th>
            </tr>
         </thead>
         <tbody class="load-fund-recent-returns-ajax-<?php echo $$recentFundID; ?>">
            <?php
			/*foreach($aDatePrices as $period=>$aDetails){
				
				switch($period){
					default: 
					
						//echo $period.'<br />Date: '.$aDetails['date'].'<br />Return: '.$aDetails['return'].'<br />S&P Return: '.$aDetails['spReturn'].'<br />Difference: '.$aDetails['difference'].'<br /><br />';
						if($aDetails['return'] != 0){
						?>
                        <tr>
                            <th><?php echo $period;?></th>
                            <td><?php echo $aDetails['return'];?>%</td>
                            <td><?php echo $aDetails['spReturn'];?>%</td>
                            <td><?php echo $aDetails['difference'];?>%</td>
                        </tr>
                        <?php
						}
					break;
					
					case '(Annualized)': 
						//echo $period.'<br />AAR: '.$aDetails['AAR'].'<br />S&P AAR: '.$aDetails['spAAR'].'<br />Difference: '.$aDetails['difference'].'<br /><br />';
						?>
                        <tr>
                            <th><?php echo $period;?></th>
                            <td><?php echo $aDetails['AAR'];?>%</td>
                            <td><?php echo $aDetails['spAAR'];?>%</td>
                            <td><?php echo $aDetails['difference'];?>%</td>
                        </tr>
                        <?php	
					break;
					
					case 'lastDay':
						$asOfDate = date('m/d/Y',$aDetails['unix']);
					break;
				}
			}*/
			?>
			
         </tbody>
         
        </table>
        <div class="recent-returns-loading-<?php echo $$recentFundID; ?>"><small>Data as of: <?php echo $asOfDate;?></small></div>
    </div><!--portlet-body-->
</div><!--portlet-->
<!-- END RECENT RETURNS-->