<?php
/*
Marketocracy Inc. | Beta Development
Fund Info Scripts

Supporting files:
	AJAX		- process/ajax/portlets/fund-info-ajax.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/portlets/fund-info.js.php
	HTML		- includes/portlets/fund-info.php
*/

//portVar1 = Fund ID
//portVar2 = NOT SET
//portVar3 = NOT SET

$portletType = "fund-info";

if(!isset($fundID)) {
	//If FUND ID is not set, portlet is on dashboard
	include('fund-symbol-query.php');
	
	
	$fundInfoFundSym	= 'fundInfoFundSym_'.$fundInfo['fund_symbol'].'';
	$$fundInfoFundSym 	= $fundInfo['fund_symbol'];
	
	$fundInfoFundID  	= 'fundInfoFundID _'.$$fundSym.'';
	$$fundInfoFundID  	= $fundInfo['fund_id'];
	
	//Hide The "Add To Dashboard symbol"
	$disableFI = 2;
}else{
	//If FUND ID is Set, portlet is on funds pages
	$disableFI = 0;
	
	//Query Dashboard columns to see if portlet already exists on dashboard
	include('check-dash-query.php');
	
	//Set New Variables so not to overwrite eachother
	if($cnt == 0){
		//Show add to dash
		$disableFI = 0;	
	}else{
		//Hide add to dash
		$disableFI = 1;	
	}
	
	$fundSym = get_funds($mLink, $fundID, 'fundSymbol');
	$fundInfoFundSym = 'alphaBetaFundSym_'.$fundSym.'';
	$$fundInfoFundSym = $fundSym;
	
	
	$fundInfoFundID = 'fundInfoFundID_'.$$fundSym.'';
	$$fundInfoFundID = $fundID;

}


if(!isset($fundID)){
	$fundInfoFundSymLink = '<a href="?page=03-01-00-001&fund='.$$fundInfoFundID.'">'.$$fundInfoFundSym.'</a>';
	//$returnsFundID = $portVar1;
}else{ 
	$fundInfoFundSymLink = $$fundInfoFundSym; 
}

//Set fund id as an array value
$fundInfoArray[] = $$fundInfoFundID;
?>
<!--START FUND INFORMATION-->
<div class="portlet" id="<?php echo $portletID; ?>">
    <div class="portlet-title handle"  <?php echo $addFundColor; ?>>
        <div class="caption">
            <i class="icon-bell"></i>
            <?php echo $fundInfoFundSymLink ?> | Fund Information
        </div><!--caption-->
        <div class="tools" id="update-button-<?php echo $portletID; ?>">
        	<?php if($pageID != "04-00-00-001"/*community public profile*/){?>
            <a href="javascript:void(0);" 
                <?php if($disableFI == 0){?>onClick="addToDash('portlet_id=<?php echo $portletType;?>~<?php echo $fundID; ?>~0~0','<?php echo $portletID;?>');" title="Add To Dashboard" class="addtodashboard balloon"<?php }?>
                <?php if($disableFI == 1){?>title="Portlet On Dashboard" class="addtodashboard balloon disabled"<?php }?>
                <?php if($disableFI == 2){?>onClick="removeFromDash('portlet_id=<?php echo $portletID; ?>','<?php echo $undoColumns;?>');" title="Remove From Dashboard" class="remove balloon"<?php }?>>
            </a>
            <a href="" class="reload balloon" title="Refresh Portlet" onclick="loadfundInfo('<?php echo $$fundInfoFundID; ?>', '<?php echo $$fundInfoFundSym; ?>')"></a>
            <?php } ?>
            <a href="" class="collapse balloon" title="Expand/Collapse Portlet"></a>
        </div>
    </div><!--portlet-title-->
    <div class="portlet-body" id="<?php echo $$fundInfoFundSym;?>-fund-info">
    	<?php
		
		//Get member ID from fund id
		$aaFundID = explode('-', $$fundInfoFundID);
		
		$activeMemberID = $aaFundID[0];
		
		$query = '
			SELECT mf.*, lp.*
			FROM '.$_SESSION['fund_table'].' AS mf
			INNER JOIN '.$_SESSION['fund_liveprice_table'].' AS lp ON mf.fund_id=lp.fund_id
			WHERE mf.fund_id=:fund_id
		';
		
		try{
			$rsGetFund = $mLink->prepare($query);
			$aValues = array(
				':fund_id' 	=> $$fundInfoFundID
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetFund->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$fund = $rsGetFund->fetch(PDO::FETCH_ASSOC);
		
		$totalValue = number_format($fund['totalValue'], 2, '.', ',');
		$inception = date('F d, Y', $fund['unix_date']);
		
		$query = '
			SELECT * 
			FROM '.$_SESSION['fund_stratification_basic_table'].'
			WHERE fund_id=:fund_id AND totalShares<>"0"
			GROUP BY stockSymbol
		';
		
		try{
			$rsGetPos = $mLink->prepare($query);
			$aValues = array(
				':fund_id' 	=> $$fundInfoFundID
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetPos->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$securities = $rsGetPos->rowCount();
		
		?> 
        <table class="table table-striped table-bordered table-hover">
           <thead>
              <tr>
                 <th>Fund Manager:</th>
                 <th>Total Net Assets:</th>
              </tr>
           </thead>
           <tbody>
              <tr>
                 <td class="success"><?php echo get_member($mLink, $activeMemberID, 'full name'); ?></td>
                 <td class="success">$<?php echo $totalValue;?></td>
              </tr>
           </tbody>
        </table>
        
        <table class="table table-striped table-bordered table-hover">
            <thead>
              <tr>
                 <th>Inception:</th>
                 <th>Ticker Symbol:</th>
                 <th># of Securities:</th>
              </tr>
           </thead>
           <tbody>
              <tr>
                 <td><?php echo $inception;?></td>
                 <td><?php echo $$fundInfoFundSym; ?></td>
                 <td><?php echo $securities;?></td>
              </tr>
           </tbody>
        </table>
        
        <table class="table table-bordered">
           <thead>
              <tr>
                 <th>Description:</th>
              </tr>
           </thead>
           <tbody>
              <tr>
                 <td><p><?php echo $fund['description'];?></p></td>
              </tr>
           </tbody>
        </table>
      
    </div><!--portlet-body-->
</div><!--portlet-->
<!--END FUND INFORMATION-->