<?php
//PROFITABLE PORTLET

//portVar1 = Fund ID
//portVar2 = NOT SET
//portVar3 = NOT SET
$portletType = "fund-profit";

if(!isset($fundID)) {
	//If FUND ID is not set, portlet is on dashboard
	include('fund-symbol-query.php');
	
	$profitFundID 	= $fundInfo['fund_id'];
	$profitFundSym	= $fundInfo['fund_symbol'];
	
	$profitFundSym	= 'profitFundSym_'.$fundInfo['fund_symbol'].'';
	$$profitFundSym = $fundInfo['fund_symbol'];
	
	$profitFundID = 'profitFundID_'.$$fundSym.'';
	$$profitFundID = $fundInfo['fund_id'];
	
	//Hide The "Add To Dashboard symbol"
	$disableFP = 2;
	
	$profitArray[] = $$profitFundID;
}else{
	//If FUND ID is Set, portlet is on funds pages
	$disableFP = 0;
	
	//Query Dashboard columns to see if portlet already exists on dashboard
	include('check-dash-query.php');
	
	//Set New Variables so not to overwrite eachother
	if($cnt == 0){
		//Show add to dash
		$disableFP = 0;	
	}else{
		//Hide add to dash
		$disableFP = 1;	
	}
	
	$fundSym = get_funds($mLink, $fundID, 'fundSymbol');
	$profitFundSym = 'profitFundSym_'.$fundSym.'';
	$$profitFundSym = $fundSym;
	
	
	$profitFundID = 'profitFundID_'.$$fundSym.'';
	$$profitFundID = $fundID;
	
	$profitArray[] = $$profitFundID;

}

if(!isset($fundID)){
	$profitFundSymLink = '<a href="?page=03-01-00-001&fund='.$$profitFundID.'">'.$$profitFundSym.'</a>';
	//$returnsFundID = $portVar1;
}else{ 
	$profitFundSymLink = $$profitFundSym; 
}

?>
<!--START 5 MOST AND LEAST PROFITABLE-->
<div class="portlet" id="<?php echo $portletID; ?>">
    <div class="portlet-title handle"  <?php echo $addFundColor; ?>>
    	<div class="caption">
        	<i class="icon-dollar"></i>
			<?php echo $profitFundSymLink; ?> | 5 Most and Least Profitable
        </div>
        <div class="tools" id="update-button-<?php echo $portletID; ?>">
        	<?php if($pageID != "04-00-00-001"/*community public profile*/){?>
            <a href="javascript:void(0);" 
				<?php if($disableFP == 0){?>onClick="addToDash('portlet_id=<?php echo $portletType;?>~<?php echo $$profitFundID; ?>~0~0','<?php echo $portletID;?>');" title="Add To Dashboard" class="addtodashboard balloon"<?php }?>
                <?php if($disableFP == 1){?>title="Portlet On Dashboard" class="addtodashboard balloon disabled"<?php }?>
                <?php if($disableFP == 2){?>onClick="removeFromDash('portlet_id=<?php echo $portletID; ?>','<?php echo $undoColumns;?>');" title="Remove From Dashboard" class="remove balloon"<?php }?>>
            </a>
            <a href="" class="reload balloon" title="Refresh Portlet"></a>
            <?php } ?>
            <a href="" class="collapse balloon" title="Expand/Collapse Portlet"></a>
        </div>
    </div><!--portlet-title-->
    <div class="portlet-body">
      
        <table class="table table-striped table-bordered table-hover">
           <thead>
              <th class="success">5 MOST PROFITABLE</th>
              <th class="danger">5 LEAST PROFITABLE</th>
           </thead>
           <tbody>
              <tr>
                 <td>
                 
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Position</th>
                                <th>Symbol</th>
                                <th>Gains</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
							$query = '
								SELECT *
								FROM '.$_SESSION['fund_stratification_basic_table'].'
								WHERE fund_id=:fund_id
								ORDER BY gains DESC
							';
							
							try{
								$rsStrat = $mLink->prepare($query);
								$aValues = array(
									':fund_id' 	=> $$profitFundID
									
								);
								$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
								$rsStrat->execute($aValues);
							}
							catch(PDOException $error){
								// Log any error
								file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
							}
							
							
							
							$aMost = array();
							while($strat = $rsStrat->fetch(PDO::FETCH_ASSOC)){
								
								$profitStockSymbol 	= $strat['stockSymbol'];
								$profitReturn		= number_format($strat['gains'], 2, '.', ',');
								
								if($profitReturn < 0){
									$profitReturn = '-$'.str_replace('-', '', $profitReturn).'';	
								}else{
									$profitReturn = '$'.$profitReturn.'';	
								}
								
								
								$aMost[$profitStockSymbol] = $profitReturn;
								
								
								
							}
							
							$profitCnt = 0;
							foreach($aMost as $profitStockSymbol=>$profitReturn){
								$profitCnt++;
								if($profitCnt <= 5){
								?>
								<tr>
									<td><?php echo $profitCnt;?></td>
									<td><a href="javascript:void(0);" data-toggle="modal"><?php echo $profitStockSymbol;?></a></td>
									<td><?php echo $profitReturn;?></td>
								</tr>
								<?php
								}else{break;}
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
                                <th>Gains</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
							$query = '
								SELECT *
								FROM '.$_SESSION['fund_stratification_basic_table'].'
								WHERE fund_id=:fund_id
								ORDER BY gains ASC
							';
							
							try{
								$rsStrat = $mLink->prepare($query);
								$aValues = array(
									':fund_id' 	=> $$profitFundID
									
								);
								$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
								$rsStrat->execute($aValues);
							}
							catch(PDOException $error){
								// Log any error
								file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
							}
							$aLeast = array();
							while($strat = $rsStrat->fetch(PDO::FETCH_ASSOC)){
								
								$profitStockSymbol 	= $strat['stockSymbol'];
								$profitReturn		= number_format($strat['gains'], 2, '.', ',');
								
								if($profitReturn < 0){
									$profitReturn = '-$'.str_replace('-', '', $profitReturn).'';	
								}else{
									$profitReturn = '$'.$profitReturn.'';	
								}
								$aLeast[$profitStockSymbol] = $profitReturn;
								
							}
							
							$profitCnt = 0;
							foreach($aLeast as $profitStockSymbol=>$profitReturn){
								$profitCnt++;
								if($profitCnt <= 5){
								?>
								<tr>
									<td><?php echo $profitCnt;?></td>
									<td><a href="javascript:void(0);" data-toggle="modal"><?php echo $profitStockSymbol;?></a></td>
									<td><?php echo $profitReturn;?></td>
								</tr>
								<?php
								}else{break;}
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
<!--END 5 MOST AND LEAST PROFITABLE-->

