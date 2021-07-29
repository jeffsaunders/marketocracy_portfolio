<?php
/*
Marketocracy Inc. | Beta Development
Fund Postion Styles Scripts

Supporting files:
	AJAX		- process/ajax/portlets/fund-pos-style-ajax.php
	JAVASCRIPT	- includes/scripts/portlets/fund-pos-style.js.php
	HTML		- THIS DOCUMENT includes/portlets/fund-pos-style.php
*/

//portVar1 = Fund ID
//portVar2 = NOT SET
//portVar3 = NOT SET

$portletType = "fund-pos-styles";

if(!isset($fundID)) {
	//If FUND ID is not set, portlet is on dashboard
	include('fund-symbol-query.php');
	
	$posStyleFundSym	= 'posStyleFundSym_'.$fundInfo['fund_symbol'].'';
	$$posStyleFundSym = $fundInfo['fund_symbol'];
	
	$posStyleFundID = 'posStyleFundID_'.$$fundSym.'';
	$$posStyleFundID = $fundInfo['fund_id'];
	
	//Hide The "Add To Dashboard symbol"
	$disableFPS = 2;
	
	$posStyleArray[] = $$posStyleFundID;

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
	$posStyleFundSym = 'posStyleFundSym_'.$fundSym.'';
	$$posStyleFundSym = $fundSym;
	
	
	$posStyleFundID = 'posStyleFundID_'.$$fundSym.'';
	$$posStyleFundID = $fundID;
	
	$posStyleArray[] = $$posStyleFundID;

}

if(!isset($fundID)){
	$posStyleFundSymLink = '<a href="?page=03-01-00-001&fund='.$$posStyleFundID.'">'.$$posStyleFundSym.'</a>';
	//$returnsFundID = $portVar1;
}else{
	$posStyleFundSymLink = $$posStyleFundSym; 
}
?>
<!--START FUND POSITIONS BY SECTORS-->
<div class="portlet" id="<?php echo $portletID; ?>">
    <div class="portlet-title handle"  <?php echo $addFundColor; ?>>
        <div class="caption">
          <i class="icon-bell"></i>
          <?php echo $posStyleFundSymLink; ?> | Fund Positions by Styles and Industries
        </div><!--caption-->
        <div class="tools" id="update-button-<?php echo $portletID; ?>">
            <a href="javascript:void(0);" 
                <?php if($disableFPS == 0){?>onClick="addToDash('portlet_id=<?php echo $portletType;?>~<?php echo $$posStyleFundID; ?>~0~0','<?php echo $portletID;?>');" title="Add To Dashboard" class="addtodashboard balloon"<?php }?>
                <?php if($disableFPS == 1){?>title="Portlet On Dashboard" class="addtodashboard balloon disabled"<?php }?>
                <?php if($disableFPS == 2){?>onClick="removeFromDash('portlet_id=<?php echo $portletID; ?>','<?php echo $undoColumns;?>');" title="Remove From Dashboard" class="remove balloon"<?php }?>>
            </a>
            <a href="" class="reload balloon" title="Refresh Portlet"></a>
            <a href="" class="collapse balloon" title="Expand/Collapse Portlet"></a>
        </div>
    </div><!--portlet-title-->
    <div class="portlet-body">
    
       
              <div id="pos-style-<?php echo $$posStyleFundID;?>"></div>
              
              
              <table class="table table-striped table-bordered table-hover">
                 <thead>
                    <tr>
                       <th>Color</th>
                       <th>Name</th>
                       <th>Allocation</th>
                       <th>Today</th>
                       <th>Inception Return</th>
                    </tr>
                 </thead>
                 <tbody>
                 	<?php
					//START: Get All Styles for fund
					
					//Colors array used for matching to pie chart, colors come from highcharts
					$aColors = array('#7cb5ec','#434348','#90ed7d','#f7a35c','#8085e9','#f15c80','#e4d354','#8085e8','#8d4653','#91e8e1');
					
					//query db and get all styles for the specific fund
					$query = '
						SELECT style
						FROM '.$_SESSION['fund_stratification_basic_table'].'
						WHERE fund_id=:fund_id
						GROUP BY style
					';
					
					try{
						$rsStyles = $mLink->prepare($query);
						$aValues = array(
							':fund_id' 	=> $$posStyleFundID
							
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsStyles->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
					// Create an array to store styles in to be used later
					$aStyles = array();
					
					// Loop through Query results and assign the styles to the array
					while($style = $rsStyles->fetch(PDO::FETCH_ASSOC)){
						$aStyles[] = $style['style'];
					}
					
					//END: Get All Styles for fund
					
					
					
					//START: Get Totals for each Style
					
					// Create an array to store style totals in
					$aPosSec = array();
					
					// Create misc counters
					$stockTotal = 0; // this is used for calculating the total value of all stocks in all styles
					
					
					// loop through each style and make calculations
					foreach($aStyles as $style){
												
						
						
						// Query DB to get position info for each style
						$query = '
							SELECT *
							FROM '.$_SESSION['fund_stratification_basic_table'].'
							WHERE fund_id=:fund_id AND style=:style
						';
						
						try{
							$rsStyle = $mLink->prepare($query);
							$aValues = array(
								':fund_id' 	=> $$posStyleFundID,
								':style'	=> $style
								
							);
							$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
							$rsStyle->execute($aValues);
						}
						catch(PDOException $error){
							// Log any error
							file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
						}
						
						//Calculators
						$currentValueTotal = 0; // used for determining allocation size, not including cash
						$todayReturnTotal = 0; // used for getting Today's return for each style
						$totalReturnTotal = 0; // used for getting Inception Return for each style
						
						$aStocks = array();
						
						// Loop through each position and make calcualtions
						while($sty = $rsStyle->fetch(PDO::FETCH_ASSOC)){
							
							// create vars from DB
							$currentValue 	= $sty['currentValue'];
							$todayReturn	= $sty['todayReturn'];
							$totalReturn	= $sty['totalReturn'];
							
							$aStocks[]		= $sty['stockSymbol'];
							
							// add up all the vars to create totals
							$currentValueTotal	= $currentValueTotal + $currentValue;
							$todayReturnTotal 	= $todayReturnTotal + $todayReturn;
							$totalReturnTotal	= $totalReturnTotal + $totalReturn;
							
						}
						
						// add each style's totals to the array
						$aPosSec[] = array(
							'style'			=> $style,
							'currentValue'	=> $currentValueTotal,
							'todayReturn'	=> $todayReturnTotal,
							'totalReturn'	=> $totalReturnTotal,
							'stocks'		=> $aStocks
						);
						
						// Create stock total to use in allocation calculation
						$stockTotal = $stockTotal + $currentValueTotal;
						
						
					}//end foreach aStyles
					//END: Get Totals for each Style
					
					//START: Loop through each style array and print results
					// Sort the array by currentValue DESC
					usort($aPosSec, function ($a, $b) { 
						return $b['currentValue'] - $a['currentValue']; 
					});
					
					$aPieValues = 'aPieValues'.$$posStyleFundID.'';
					$$aPieValues = array();
					
					$colorCnt = 0; // this is used to loop through the color array
					// Loop through array and print results
					foreach($aPosSec as $aSec){
						
						// Calculate Allocation
						$allocation = $aSec['currentValue'] / $stockTotal;
						
						// Grab a color from the color array then increment by one until you get to 10 then start over at 0
						if($colorCnt >= 10){
							$colorCnt = 0;	
						}
						$color = $aColors[$colorCnt];
						$colorCnt = $colorCnt + 1;
						
						?>
						<tr>
                           <td style="background-color:<?php echo $color; ?>;"></td>
                           <td><?php echo $aSec['style']; ?></td>
                           <td><?php echo number_format(($allocation * 100), 2, '.','');?>%</td>
                           <td><?php echo number_format($aSec['todayReturn'], 2, '.', ''); ?>%</td>
                           <td><?php echo number_format($aSec['totalReturn'], 2, '.', ''); ?>%</td>
                        </tr>
                        <?php
						
						array_push($$aPieValues, array(
							'style'			=> $aSec['style'],
							'allocation'	=> ''.number_format(($allocation * 100), 2, '.', '').'',
							'todayReturn'	=> ''.number_format($aSec['todayReturn'], 2, '.', '').'%',
							'totalReturn'	=> ''.number_format($aSec['totalReturn'], 2, '.', '').'%'
						));	
					}
					//END: Loop through each style array and print results
						
					?>
                    
                    
                 </tbody>
              </table>
              <?php /*?><pre>
              <?php print_r($aStyles); print_r($aPosSec); ?>
              </pre><?php */?>
        
    </div><!--end portlet body-->
</div><!--end portlet-->
<!--END FUND POSITIONS BY SECTOR-->