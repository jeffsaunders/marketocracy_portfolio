<?php
//TURNOVER PORTLET
/*
Supporting files:
	AJAX		- process/ajax/portlets/fund-turnover-ajax.php
	JAVASCRIPT	- includes/scripts/portlets/fund-turnover.js.php
	HTML		- THIS DOCUMENT includes/portlets/fund-turnover.php
	
*/


//portVar1 = Fund ID
//portVar2 = NOT SET
//portVar3 = NOT SET

$portletType = "fund-turnover";

if(!isset($fundID)) {
	//If FUND ID is not set, portlet is on dashboard
	include('fund-symbol-query.php');
	
	$turnoverFundSym	= 'turnoverFundSym_'.$fundInfo['fund_symbol'].'';
	$$turnoverFundSym = $fundInfo['fund_symbol'];
	
	$turnoverFundID = 'turnoverFundID_'.$$fundSym.'';
	$$turnoverFundID = $fundInfo['fund_id'];
	
	//Hide The "Add To Dashboard symbol"
	$disableFT = 2;
}else{
	//If FUND ID is Set, portlet is on funds pages
	$disableFT = 0;
	
	//Query Dashboard columns to see if portlet already exists on dashboard
	include('check-dash-query.php');
	
	//Set New Variables so not to overwrite eachother
	if($cnt == 0){
		//Show add to dash
		$disableFT = 0;	
	}else{
		//Hide add to dash
		$disableFT = 1;	
	}
	
	$fundSym = get_funds($mLink, $fundID, 'fundSymbol');
	$turnoverFundSym = 'turnoverFundSym_'.$fundSym.'';
	$$turnoverFundSym = $fundSym;
	
	
	$turnoverFundID = 'turnoverFundID_'.$$fundSym.'';
	$$turnoverFundID = $fundID;

}

if(!isset($fundID)){
	$turnoverFundSymLink = '<a href="?page=03-01-00-001&fund='.$$turnoverFundID.'">'.$$turnoverFundSym.'</a>';
	//$returnsFundID = $portVar1;
}else{ 
	$turnoverFundSymLink = $$turnoverFundSym; 
}

//Set fund id as an array value
$turnoverArray[] = $$turnoverFundID;
?>
<!--START TURNOVER-->
<div class="portlet" id="<?php echo $portletID; ?>">
    <div class="portlet-title handle" <?php echo $addFundColor; ?>>
        <div class="caption">
        	<i class="icon-bell"></i>
			<?php echo $turnoverFundSymLink; ?> | Turnover
        </div>
        <div class="tools" id="update-button-<?php echo $portletID; ?>">
        	<?php if($pageID != "04-00-00-001"/*community public profile*/){?>
            <a href="javascript:void(0);" 
				<?php if($disableFT == 0){?>onClick="addToDash('portlet_id=<?php echo $portletType;?>~<?php echo $fundID; ?>~0~0','<?php echo $portletID;?>');" title="Add To Dashboard" class="addtodashboard balloon"<?php }?>
                <?php if($disableFT == 1){?>title="Portlet On Dashboard" class="addtodashboard balloon disabled"<?php }?>
                <?php if($disableFT == 2){?>onClick="removeFromDash('portlet_id=<?php echo $portletID; ?>','<?php echo $undoColumns;?>');" title="Remove From Dashboard" class="remove balloon"<?php }?>>
            </a>
            <a href="" class="reload balloon" title="Refresh Portlet"></a>
            <?php } ?>
            <a href="" class="collapse balloon" title="Expand/Collapse Portlet"></a>
        </div>
    </div><!--portlet-title-->
    <div class="portlet-body">
      
        <table class="table table-striped table-bordered table-hover">
           <tbody class="load-turnover-<?php echo $$turnoverFundID; ?>">
              <?php
			  $query = '
					SELECT turnoverLast30Days AS month, turnoverLast90Days AS 3months, turnoverLast180Days AS 6months, turnoverLastYear AS year
					FROM '.$_SESSION['fund_aggregate_table'].'
					WHERE fund_id=:fund_id
					ORDER BY timestamp DESC
					LIMIT 1
				';
				
				try{
					$rsGetFund = $mLink->prepare($query);
					$aValues = array(
						':fund_id' 	=> $fundID
						
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsGetFund->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				$fund = $rsGetFund->fetch(PDO::FETCH_ASSOC);
				
				$lastMonth = round($fund['month'], 2);
				if($lastMonth == "0"){
					$lastMonth = "N/A";
				}else{
					$lastMonth = ''.$lastMonth.'%';
				}
				
				$last3Months = round($fund['3months'], 2);
				if($last3Months == "0"){
					$last3Months = "N/A";
				}else{
					$last3Months = ''.$last3Months.'%';
				}
				
				$last6Months = round($fund['6months'], 2);
				if($last6Months == "0"){
					$last6Months = "N/A";
				}else{
					$last6Months = ''.$last6Months.'%';
				}
				
				$lastYear = round($fund['year'], 2);
				if($lastYear == "0"){
					$lastYear = "N/A";	
				}else{
					$lastYear = ''.$lastYear.'%';
				}
				
				?>
				<tr>
					<th>Last Month</th>
					<td><?php echo $lastMonth;?></td>
				</tr>
				<tr>
					<th>Last 3 Months</th>
					<td><?php echo $last3Months;?></td>
				</tr>
				<tr>
					<th>Last 6 Months</th>
					<td><?php echo $last6Months;?></td>
				</tr>
				<tr>
				<th>Last 12 Months</th>
				<td><?php echo $lastYear;?></td>
				</tr>
           </tbody>
        </table>
      	<div class="turnover-loading-<?php echo $$turnoverFundID; ?>"></div>
    </div><!--portlet-body-->
    
</div><!--portlet-->
<!--END TURNOVER-->