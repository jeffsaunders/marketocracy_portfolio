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

$portletType = "fund-pos-style";

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


//Pull all Style Stratification Data and place into an array
$query = '
	SELECT * 
	FROM '.$_SESSION['fund_stratification_style_table'].' 
	WHERE fund_id=:fund_id
	ORDER BY styleALlocation DESC
	
';
try{
	$rsStyles = $mLink->prepare($query);
	$aValues = array(
		':fund_id'	=> $$posStyleFundID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsStyles->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

//loop through result
while($style = $rsStyles->fetch(PDO::FETCH_ASSOC)){
	
	if($style['styleAllocation'] > 0){
		$aStyles[$style['style_id']] = array(
			//'style_id'			=> $style['style_id'],
			'styleName'			=> $style['styleName'],
			'styleValue'		=> $style['styleValue'],
			'styleAllocation'	=> $style['styleAllocation'],
			'styleTodayReturn'	=> $style['styleTodayReturn'],
			'styleTotalReturn'	=> $style['styleTotalReturn']
		);
	}
	
}

//Clean up array (get rid of empties)
foreach($aStyles as $key => $style){
	if($key == ''){
		unset($aStyles[$key]);	
	}
}

//Colors array used for matching to pie chart, colors come from highcharts
$aColors = graphColors();

//END: loop through each style and get the positions relating to that style and stuff in the array

/*echo '<pre>';
print_r($aStyles);
echo '</pre>';*/
?>
<!--START FUND POSITIONS BY SECTORS-->
<div class="portlet" id="<?php echo $portletID; ?>">
    <div class="portlet-title handle"  <?php echo $addFundColor; ?>>
        <div class="caption">
          <i class="icon-bell"></i>
          <?php echo $posStyleFundSymLink; ?> | Fund Positions by Styles
        </div><!--caption-->
        <div class="tools" id="update-button-<?php echo $portletID; ?>">
        	<?php if($pageID != "04-00-00-001"/*community public profile*/){?>
            <a href="javascript:void(0);" 
                <?php if($disableFPS == 0){?>onClick="addToDash('portlet_id=<?php echo $portletType;?>~<?php echo $$posStyleFundID; ?>~0~0','<?php echo $portletID;?>');" title="Add To Dashboard" class="addtodashboard balloon"<?php }?>
                <?php if($disableFPS == 1){?>title="Portlet On Dashboard" class="addtodashboard balloon disabled"<?php }?>
                <?php if($disableFPS == 2){?>onClick="removeFromDash('portlet_id=<?php echo $portletID; ?>','<?php echo $undoColumns;?>');" title="Remove From Dashboard" class="remove balloon"<?php }?>>
            </a>
            <a href="" class="reload balloon" title="Refresh Portlet"></a>
            <?php } ?>
            <a href="" class="collapse balloon" title="Expand/Collapse Portlet"></a>
        </div>
    </div><!--portlet-title-->
    <div class="portlet-body flip-scroll">
    
              <div id="pos-style-<?php echo $$posStyleFundID;?>"></div>
              
              <div class="table-responsive2">
              <table class="table table-striped table-bordered flip-content">
                 <thead class="flip-content">
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
					$aPieValues = 'aPieStyleValues'.$$posStyleFundID.'';
					${$aPieValues} = array();
					
					$colorCnt = 0; // this is used to loop through the color array
					
					// Loop through array and print results
					foreach($aStyles as $style){
						
						// Calculate Allocation
						$allocation = $style['styleAllocation'];
						
						// Grab a color from the color array then increment by one until you get to 10 then start over at 0
						if($colorCnt >= 10){
							$colorCnt = 0;	
						}
						$color = $aColors[$colorCnt];
						$colorCnt = $colorCnt + 1;
						
						?>
						<tr>
                           <td style="background-color:<?php echo $color; ?>;min-height:35px !important;"></td>
                           <td><?php echo $style['styleName']; ?></td>
                           <td><?php echo number_format($allocation, 2, '.','');?>%</td>
                           <td><?php echo number_format($style['styleTodayReturn'], 2, '.', ''); ?>%</td>
                           <?php /*?><td><?php echo number_format($style['styleTotalReturn'], 2, '.', ''); ?>%</td><?php */?>
                        </tr>
                        <?php
						
						//Push results into the array for chart
						array_push(${$aPieValues}, array(
							'style'			=> $style['styleName'],
							'allocation'	=> ''.number_format($allocation, 2, '.', '').'',
							'todayReturn'	=> ''.number_format($style['styleTodayReturn'], 2, '.', '').'%',
							'totalReturn'	=> ''.number_format($style['styleTotalReturn'], 2, '.', '').'%',
							'fundID'		=> $$posStyleFundID
						));	
						
					}
										
					$aStyles = array();
					//END: Loop through each style array and print results
					?>
                   
                 </tbody>
              </table>
              </div>
              
              <?php /*?><pre>
              hello
              <?php print_r($$aPieValues);?>
              </pre><?php */?>
        
    </div><!--end portlet body-->
</div><!--end portlet-->
<!--END FUND POSITIONS BY SECTOR-->