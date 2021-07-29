<?php
//RATIO TABLE PORTLET
//portVar1 = Fund ID
//portVar2 = NOT SET
//portVar3 = NOT SET

$portletType = "fund-pos-sectors";

if(!isset($fundID)) {
	//If FUND ID is not set, portlet is on dashboard
	include('fund-symbol-query.php');
	
	$posSectorFundID 	= $fundInfo['fund_id'];
	$posSectorFundSym	= $fundInfo['fund_symbol'];
	
	$posSectorFundSym	= 'posSectorFundSym_'.$fundInfo['fund_symbol'].'';
	$$posSectorFundSym = $fundInfo['fund_symbol'];
	
	$posSectorFundID = 'posSectorFundID_'.$$fundSym.'';
	$$posSectorFundID = $fundInfo['fund_id'];
	
	//Hide The "Add To Dashboard symbol"
	$disableFPS = 2;
	
	$posSecArray[] = $$posSectorFundID;

}else{
	//If FUND ID is Set, portlet is on funds pages
	$disableFPS = 0;
	
	//Query Dashboard columns to see if portlet already exists on dashboard
	include('check-dash-query.php');
	
	//Set New Variables so not to overwrite eachother
	if($cnt == 0){
		//Show add to dash
		$disableFPS = 0;	
	}else{
		//Hide add to dash
		$disableFPS = 1;	
	}
	
	$fundSym = get_funds($mLink, $fundID, 'fundSymbol');
	$posSectorFundSym = 'posSectorFundSym_'.$fundSym.'';
	$$posSectorFundSym = $fundSym;
	
	
	$posSectorFundID = 'posSectorFundID_'.$$fundSym.'';
	$$posSectorFundID = $fundID;
	
	$posSecArray[] = $$posSectorFundID;

}

if(!isset($fundID)){
	$posSectorFundSymLink = '<a href="?page=03-01-00-001&fund='.$$posSectorFundID.'">'.$$posSectorFundSym.'</a>';
	//$returnsFundID = $portVar1;
}else{ 
	$posSectorFundSymLink = $$posSectorFundSym; 
}

//Pull all sector Stratification Data and place into an array
$query = '
	SELECT * 
	FROM '.$_SESSION['fund_stratification_sector_table'].' 
	WHERE fund_id=:fund_id
	ORDER BY sectorALlocation DESC
	
';
try{
	$rsSectors = $mLink->prepare($query);
	$aValues = array(
		':fund_id'	=> $$posSectorFundID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsSectors->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

//loop through result
while($sector = $rsSectors->fetch(PDO::FETCH_ASSOC)){
	
	if($sector['sectorAllocation'] > 0){
		$aSectors[$sector['sector_id']] = array(
			//'sector_id'			=> $sector['sector_id'],
			'sectorName'			=> $sector['sectorName'],
			'sectorValue'			=> $sector['sectorValue'],
			'sectorAllocation'		=> $sector['sectorAllocation'],
			'sectorTodayReturn'		=> $sector['sectorTodayReturn'],
			'sectorTotalReturn'		=> $sector['sectorTotalReturn']
			
		);
	}
	
}

//Colors array used for matching to pie chart, colors come from highcharts
$aColors = graphColors();
?>
<!--START FUND POSITIONS BY SECTORS-->
<div class="portlet" id="<?php echo $portletID; ?>">
    <div class="portlet-title handle"  <?php echo $addFundColor; ?>>
        <div class="caption">
          <i class="icon-bell"></i>
          <?php echo $posSectorFundSymLink; ?> | Fund Positions by Sectors and Industries
        </div><!--caption-->
        <div class="tools" id="update-button-<?php echo $portletID; ?>">
        	<?php if($pageID != "04-00-00-001"/*community public profile*/){?>
            <a href="javascript:void(0);" 
                <?php if($disableFPS == 0){?>onClick="addToDash('portlet_id=<?php echo $portletType;?>~<?php echo $$posSectorFundID; ?>~0~0','<?php echo $portletID;?>');" title="Add To Dashboard" class="addtodashboard balloon"<?php }?>
                <?php if($disableFPS == 1){?>title="Portlet On Dashboard" class="addtodashboard balloon disabled"<?php }?>
                <?php if($disableFPS == 2){?>onClick="removeFromDash('portlet_id=<?php echo $portletID; ?>','<?php echo $undoColumns;?>');" title="Remove From Dashboard" class="remove balloon"<?php }?>>
            </a>
            <a href="" class="reload balloon" title="Refresh Portlet"></a>
            <?php } ?>
            <a href="" class="collapse balloon" title="Expand/Collapse Portlet"></a>
        </div>
    </div><!--portlet-title-->
    <div class="portlet-body">
    
       
              <div id="pos-sector-<?php echo $$posSectorFundID;?>"></div>
              
              <div class="table-responsive2">
              <table class="table table-striped table-bordered table-hover">
                 <thead>
                    <tr>
                       <th>Color</th>
                       <th>Name</th>
                       <th>Allocation</th>
                       <th>Today</th>
                       <?php /*?><th>Inception Return</th><?php */?>
                    </tr>
                 </thead>
                 <tbody>
                 	<?php
					//Set multi-variable for array to build chart results
					$aPieSecValues = 'aPieSecValues'.$$posSectorFundID.'';
					$$aPieSecValues = array();
					
					$colorCnt = 0; // this is used to loop through the color array
					
					// Loop through array and print results
					foreach($aSectors as $sector){
						
						// Set vars
						$sectorName		= $sector['sectorName'];
						$allocation 	= ''.number_format($sector['sectorAllocation'], 2, '.','').'%';
						$todayReturn	= ''.number_format($sector['sectorTodayReturn'], 2, '.', '').'%';
						$totalReturn	= ''.number_format($sector['sectorTotalReturn'], 2, '.', '').'%';
						
						// Grab a color from the color array then increment by one until you get to 10 then start over at 0
						if($colorCnt >= 10){
							$colorCnt = 0;	
						}
						$color = $aColors[$colorCnt];
						$colorCnt = $colorCnt + 1;
						
						?>
						<tr>
                           <td style="background-color:<?php echo $color; ?>;min-height:35px !important;"></td>
                           <td><?php echo $sectorName; ?></td>
                           <td><?php echo $allocation; ?></td>
                           <td><?php echo $todayReturn; ?>%</td>
                           <?php /*?><td><?php echo $totalReturn; ?>%</td><?php */?>
                        </tr>
                        <?php
						
						array_push($$aPieSecValues, array(
							'sector'		=> $sectorName,
							'allocation'	=> number_format($sector['sectorAllocation']),
							'todayReturn'	=> $todayReturn,
							'totalReturn'	=> $totalReturn
						));	
					}
					//END: Loop through each sector array and print results
					
					$aSectors = array();	
					?>
                    
                    
                 </tbody>
              </table>
              </div><!--table-responsive-->
              <?php /*?><pre>
              <?php print_r($sectortors); print_r($aPosSec); ?>
              </pre><?php */?>
              
              <?php /*?><pre>
              hello
              <?php print_r($$aPieSecValues);?>
              </pre><?php */?>
        
    </div><!--end portlet body-->
</div><!--end portlet-->
<!--END FUND POSITIONS BY SECTOR-->