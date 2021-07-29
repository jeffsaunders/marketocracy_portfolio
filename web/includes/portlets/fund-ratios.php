<?php
//RATIO TABLE PORTLET

//portVar1 = Fund ID
//portVar2 = NOT SET
//portVar3 = NOT SET

$portletType = "fund-ratios";

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


$query = "
	SELECT totalShares, gains
	FROM ".$_SESSION['fund_stratification_basic_table']."
	WHERE fund_id=:fund_id
	ORDER BY totalShares ASC
";
try{
$rsStrat = $mLink->prepare($query);
	$aValues = array(
		':fund_id' 	=> $$recentFundID
		
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsStrat->execute($aValues);
}

catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$aGains = array();
$gainCnt = 0;
while($strat = $rsStrat->fetch(PDO::FETCH_ASSOC)){
	$gainCnt++;
	$aGains[$gainCnt] = $strat['gains'];
	
}
$aMembersWorking[$memberID]['funds'][$fundID]['positions'] = $gainCnt;		
$aMembersWorking[$memberID]['funds'][$fundID]['gains'] = $aGains;

foreach($aMembersWorking[$memberID]['funds'] as $fundID=> $aStuff){

	$posGainCnt = 0;
	$negGainCnt = 0;
	
	$posGainTotal = 0;
	$negGainTotal = 0;
	
	foreach($aStuff['gains'] as $key=>$gain){
		if($gain < 0){
			$negGainCnt++;	
			$negGainTotal = $negGainTotal + $gain;
		}elseif($gain > 0){
			$posGainCnt++;
			$posGainTotal = $posGainTotal + $gain;	
		}
	}
	
	
	$winningPercent = $posGainCnt / $aStuff['positions'];
	$gainLossRatio = $posGainTotal / abs($negGainTotal);
                                                       
	$nGain = $posGainTotal / $posGainCnt;
	$dLoss = $negGainTotal / $negGainCnt;
	
	$avgGainLoss = $nGain / abs($dLoss);

	
	//lets hide this for now, so we can study the output and take up less memmory
	unset($aMembersWorking[$memberID]['funds'][$fundID]['gains']);
}
?>

<!--START RATIOS-->	
<div class="portlet" id="<?php echo $portletID; ?>">
    <div class="portlet-title handle" <?php echo $addFundColor; ?>>
        <div class="caption">
        	<i class="icon-bell"></i>
			<?php if(!isset($fundID)){?><a href="?page=03-01-00-001&fund=<?php echo $ratiosFundID; ?>"><?php echo $ratiosFundSym; ?></a><?php }else{ echo $fundSymbol; } ?> |  Skill Metrics
        </div>
        <div class="tools" id="update-button-<?php echo $portletID; ?>">
        	<?php if($pageID != "04-00-00-001"/*community public profile*/){?>
            <a href="javascript:void(0);" 
				<?php if($disableFR == 0){?>onClick="addToDash('portlet_id=<?php echo $portletType;?>~<?php echo $fundID; ?>~0~0','<?php echo $portletID;?>');" title="Add To Dashboard" class="addtodashboard balloon"<?php }?>
                <?php if($disableFR == 1){?>title="Portlet On Dashboard" class="addtodashboard balloon disabled"<?php }?>
                <?php if($disableFR == 2){?>onClick="removeFromDash('portlet_id=<?php echo $portletID; ?>','<?php echo $undoColumns;?>');" title="Remove From Dashboard" class="remove balloon"<?php }?>>
            </a>
            <a href="" class="reload balloon" title="Refresh Portlet"></a>
            <?php } ?>
            <a href="" class="collapse balloon" title="Expand/Collapse Portlet"></a>
        </div>
    </div><!--portlet-title-->
    <div class="portlet-body">
           
        <div class="table-scrollable">
            <table class="table table-striped table-bordered table-hover">
               <thead>
                  <tr>
                     <th></th>
                     <th>All Positions</th>
                  </tr>
               </thead>
               <tbody>
                  <tr>
                     <td class="success"><strong>Winning %</strong></td>
                     <td class="success"><?php echo number_format($winningPercent * 100, 2, '.' ,'');?>%</td>
                  </tr>
                 
                  <tr>
                     <td><strong>Avg. Gain/Loss Ratio <a href="javascript:void(0);" title="Avg Gain/Loss ratio tells you whether the manager is good at letting winners run while also cutting losers off while losses are small. An avg G/L ratio of 2 means that when the manager is right about a stock, he makes twice as much money as he loses when he is wrong." class="balloon-right balloon-click"><i class="fa fa-question-circle"></i></a></strong></td>
                     <td><?php echo number_format($avgGainLoss, 2, '.','');?></td>
                  </tr>
               </tbody>
            </table>
        </div><!--table-scrollable-->
      
    </div><!--portlet-body-->
</div><!--portlet-->
<!--END RATIOS-->