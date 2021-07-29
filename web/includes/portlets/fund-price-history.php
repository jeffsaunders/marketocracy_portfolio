<?php
/*
Marketocracy Inc. | Beta Development
Fund Price History

Supporting files:
	JSON		- process/json/fund-price-history-json.php
	JAVASCRIPT	- includes/scripts/portlets/fund-price-history.js.php
	HTML		- THIS DOCUMENT includes/portlets/fund-price-history.php
*/

//portVar1 = Fund ID
//portVar2 = NOT SET
//portVar3 = NOT SET

$portletType = "fund-price-history";

if(!isset($fundID)) {
	//If FUND ID is not set, portlet is on dashboard
	include('fund-symbol-query.php');
	
	$phFundSym	= 'phFundSym_'.$fundInfo['fund_symbol'].'';
	$$phFundSym = $fundInfo['fund_symbol'];
	
	$phFundID = 'phFundID_'.$$fundSym.'';
	$$phFundID = $fundInfo['fund_id'];
	
	//Hide The "Add To Dashboard symbol"
	$disablePH = 2;
	
	$phArray[] = $fundInfo['fund_id'];
	
	
}else{
	//If FUND ID is Set, portlet is on funds pages
	$disablePH = 0;
	
	//Query Dashboard columns to see if portlet already exists on dashboard
	include('check-dash-query.php');
	
	//Set New Variables so not to overwrite eachother
	if($cnt == 0){
		//Show add to dash
		$disablePH = 0;	
	}else{
		//Hide add to dash
		$disablePH = 1;	
	}
	
	$fundSym = get_funds($mLink, $fundID, 'fundSymbol');
	$phFundSym = 'phFundSym_'.$fundSym.'';
	$$phFundSym = $fundSym;
	
	$phFundID = 'phFundID_'.$phFundSym.'';
	$$phFundID = $fundID;
	
	$phArray[] = $fundID;
	
}
if(!isset($fundID)){
	$phFundSymLink = '<a href="?page=03-01-00-001&fund='.$$phFundID.'">'.$$phFundSym.'</a>';
	//$fundID = $portVar1;
}else{ 
	$phFundSymLink = $$phFundSym; 
}
?>
<!-- START PRICE HISTORY-->
<div class="portlet" id="<?php echo $portletID; ?>">
    <div class="portlet-title handle" <?php echo $addFundColor; ?>>
        <div class="caption">
        	<i class="icon-bar-chart"></i>
			<?php echo $phFundSymLink;?> | Price History <?php //echo $$phFundID;?>
        </div>
        <div class="tools" id="update-button-<?php echo $portletID; ?>">
            <?php if($pageID != "04-00-00-001"/*community public profile*/){?>
            <a href="javascript:void(0);" 
				<?php if($disablePH == 0){?>onClick="addToDash('portlet_id=<?php echo $portletType;?>~<?php echo $fundID; ?>~0~0','<?php echo $portletID;?>');" title="Add To Dashboard" class="addtodashboard balloon"<?php }?>
                <?php if($disablePH == 1){?>title="Portlet On Dashboard" class="addtodashboard balloon disabled"<?php }?>
                <?php if($disablePH == 2){?>onClick="removeFromDash('portlet_id=<?php echo $portletID; ?>','<?php echo $undoColumns;?>');" title="Remove From Dashboard" class="remove balloon"<?php }?>>
            </a>
            <a href="" class="reload balloon" title="Refresh Portlet"></a>
            <?php } ?>
            <a href="" class="collapse balloon" title="Expand/Collapse Portlet"></a>
        </div>
    </div><!--portlet-title-->
    
    <div class="portlet-body">
		<div class="container-<?php echo $$phFundID;//$$phFundSym;?>" style="min-height:407px;"></div>
    </div><!--portlet-body-->
    
</div><!--portlet-->
<!-- END PRICE HISTORY-->

