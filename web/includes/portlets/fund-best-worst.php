<?php
//BEST WORST RETURNS PORTLET
/*
Supporting files:
	AJAX		- process/ajax/portlets/fund-best-worst-ajax.php
	JAVASCRIPT	- includes/scripts/portlets/fund-best-worst.js.php
	HTML		- THIS DOCUMENT includes/portlets/fund-best-worst.php
	
*/

//portVar1 = Fund ID
//portVar2 = NOT SET
//portVar3 = NOT SET

$portletType = "fund-best-worst";

if(!isset($fundID)) {
	//If FUND ID is not set, portlet is on dashboard
	include('fund-symbol-query.php');
	
	$bestWorstFundSym	= 'bestWorstFundSym_'.$fundInfo['fund_symbol'].'';
	$$bestWorstFundSym = $fundInfo['fund_symbol'];
	
	$bestWorstFundID = 'bestWorstFundID_'.$$fundSym.'';
	$$bestWorstFundID = $fundInfo['fund_id'];
	
	//Hide The "Add To Dashboard symbol"
	$disableFBW = 2;
	
	$bwArray[] = $$bestWorstFundID;
}else{
	//If FUND ID is Set, portlet is on funds pages
	$disableFBW = 0;
	
	//Query Dashboard columns to see if portlet already exists on dashboard
	include('check-dash-query.php');
	
	//Set New Variables so not to overwrite eachother
	if($cnt == 0){
		//Show add to dash
		$disableFBW = 0;	
	}else{
		//Hide add to dash
		$disableFBW = 1;	
	}
	
	$fundSym = get_funds($mLink, $fundID, 'fundSymbol');
	$bestWorstFundSym = 'bestWorstFundSym_'.$fundSym.'';
	$$bestWorstFundSym = $fundSym;
	
	
	$bestWorstFundID = 'bestWorstFundID_'.$$fundSym.'';
	$$bestWorstFundID = $fundID;
	
	$bwArray[] = $$bestWorstFundID;

}

if(!isset($fundID)){
	$bestWorstFundSymLink = '<a href="?page=03-01-00-001&fund='.$$bestWorstFundID.'">'.$$bestWorstFundSym.'</a>';
	//$returnsFundID = $portVar1;
}else{ 
	$bestWorstFundSymLink = $$bestWorstFundSym; 
}
?>
<!--START 5 BEST AND WORST RETUNRS-->
<div class="portlet" id="<?php echo $portletID; ?>">
    <div class="portlet-title handle" <?php echo $addFundColor; ?>>
    	<div class="caption">
        	<i class="icon-arrow-up"></i><i class="icon-arrow-down"></i>
			<?php echo $bestWorstFundSymLink;?> | 5 Best and Worst Returns
        </div>
        <div class="tools" id="update-button-<?php echo $portletID; ?>">
        	<?php if($pageID != "04-00-00-001"/*community public profile*/){?>
            <a href="javascript:void(0);" 
				<?php if($disableFBW == 0){?>onClick="addToDash('portlet_id=<?php echo $portletType;?>~<?php echo $$bestWorstFundID; ?>~0~0','<?php echo $portletID;?>');" title="Add To Dashboard" class="addtodashboard balloon"<?php }?>
                <?php if($disableFBW == 1){?>title="Portlet On Dashboard" class="addtodashboard balloon disabled"<?php }?>
                <?php if($disableFBW == 2){?>onClick="removeFromDash('portlet_id=<?php echo $portletID; ?>','<?php echo $undoColumns;?>');" title="Remove From Dashboard" class="remove balloon"<?php }?>>
            </a>
            <a href="" class="reload balloon" title="Refresh Portlet"></a>
            <?php } ?>
            <a href="" class="collapse balloon" title="Expand/Collapse Portlet"></a>
        </div>
    </div><!--portlet-title-->
    <div class="portlet-body">
      
        <table class="table table-striped table-bordered table-hover">
           <thead>
              <th class="success">5 BEST</th>
              <th class="danger">5 WORST</th>
           </thead>
           <tbody>
              <tr>
                 <td>
                 
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Position</th>
                                <th>Symbol</th>
                                <th>Inception Return</th>
                            </tr>
                        </thead>
                        <tbody>
                        	<?php
							/*$query = '
								SELECT *
								FROM '.$_SESSION['fund_stratification_basic_table'].'
								WHERE fund_id=:fund_id AND totalShares > 0
								ORDER BY totalReturn DESC
							';*/
							
							$query = '
								SELECT *
								FROM '.$_SESSION['fund_stratification_basic_table'].'
								WHERE fund_id=:fund_id
								ORDER BY totalReturn DESC
							';
							
							try{
								$rsStrat = $mLink->prepare($query);
								$aValues = array(
									':fund_id' 	=> $$bestWorstFundID
									
								);
								$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
								$rsStrat->execute($aValues);
							}
							catch(PDOException $error){
								// Log any error
								file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
							}
							
							
							$aBest = array();
							while($strat = $rsStrat->fetch(PDO::FETCH_ASSOC)){
								
								$bwStockSymbol 	= $strat['stockSymbol'];
								$bwReturn		= number_format($strat['totalReturn'], 2, '.', '');
								
								$aBest[$bwStockSymbol] = $bwReturn;
							
								
							}
							$bwCnt = 0;
							foreach($aBest as $bwStockSymbol=>$bwReturn){
								$bwCnt++;
								if($bwCnt <= 5){
								?>
                                <tr>
									<td><?php echo $bwCnt;?></td>
									<td><a href="javascript:void(0);" data-toggle="modal"><?php echo $bwStockSymbol;?></a></td>
									<td><?php echo $bwReturn;?>%</td>
								</tr>
                                <?php
								}
							}
							?>
                        </tbody>
                    </table>
                 
                 </td>
                 <td>
                 
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Position</th>
                                <th>Symbol</th>
                                <th>Inception Return</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
							/*$query = '
								SELECT *
								FROM '.$_SESSION['fund_stratification_basic_table'].'
								WHERE fund_id=:fund_id AND totalShares > 0
								ORDER BY recentReturn ASC
							';*/
							
							$query = "
								SELECT *
								FROM ".$_SESSION['fund_stratification_basic_table']."
								WHERE fund_id=:fund_id AND totalReturn<>'-100.000000000000'
								ORDER BY totalReturn ASC
							";
							
							try{
								$rsStrat = $mLink->prepare($query);
								$aValues = array(
									':fund_id' 	=> $$bestWorstFundID
									
								);
								$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
								$rsStrat->execute($aValues);
							}
							catch(PDOException $error){
								// Log any error
								file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
							}
							$aWorst = array();
							while($strat = $rsStrat->fetch(PDO::FETCH_ASSOC)){
								
								$bwStockSymbol 	= $strat['stockSymbol'];
								$bwReturn		= number_format($strat['totalReturn'], 2, '.', '');
								
								$aWorst[$bwStockSymbol] = $bwReturn;
								
								
							}
							$bwCnt = 0;
							foreach($aWorst as $bwStockSymbol=>$bwReturn){
								$bwCnt++;
								if($bwCnt <= 5){
								?>
                                <tr>
									<td><?php echo $bwCnt;?></td>
									<td><a href="javascript:void(0);" data-toggle="modal"><?php echo $bwStockSymbol;?></a></td>
									<td><?php echo $bwReturn;?>%</td>
								</tr>
                                <?php
								}
							}
							?>
                        </tbody>
                    </table>
                 
                 </td>
              </tr>
           </tbody>
        </table>
      
    </div><!--portlet-body-->
</div><!--portlet-->
<!--END 5 BEST AND WORST RETURNS-->