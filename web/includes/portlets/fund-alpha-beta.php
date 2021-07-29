<?php
//ALPHA/BETA VS S&P PORTLET
/*
Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/portlets/fund-alpha-beta-ajax.php
	JAVASCRIPT	- includes/scripts/portlets/fund-alpha-beta.js.php
	HTML		- includes/portlets/fund-alpha-beta.php
	
*/

//portVar1 = Fund ID
//portVar2 = NOT SET
//portVar3 = NOT SET

$portletType = "fund-alpha-beta";

if(!isset($fundID)) {
	//If FUND ID is not set, portlet is on dashboard
	include('fund-symbol-query.php');

	
	$alphaBetaFundSym	= 'alphaBetaFundSym_'.$fundInfo['fund_symbol'].'';
	$$alphaBetaFundSym = $fundInfo['fund_symbol'];
	
	$alphaBetaFundID = 'alphaBetaFundID_'.$$fundSym.'';
	$$alphaBetaFundID = $fundInfo['fund_id'];
	
	//Hide The "Add To Dashboard symbol"
	$disableFAB = 2;
}else{
	//If FUND ID is Set, portlet is on funds pages
	$disableFAB = 0;
	
	//Query Dashboard columns to see if portlet already exists on dashboard
	include('check-dash-query.php');
	
	//Set New Variables so not to overwrite eachother
	if($cnt == 0){
		//Show add to dash
		$disableFAB = 0;	
	}else{
		//Hide add to dash
		$disableFAB = 1;	
	}
	
	$fundSym = get_funds($mLink, $fundID, 'fundSymbol');
	$alphaBetaFundSym = 'alphaBetaFundSym_'.$fundSym.'';
	$$alphaBetaFundSym = $fundSym;
	
	
	$alphaBetaFundID = 'alphaBetaFundID_'.$$fundSym.'';
	$$alphaBetaFundID = $fundID;

}

if(!isset($fundID)){
	$alphaBetaFundSymLink = '<a href="?page=03-01-00-001&fund='.$$alphaBetaFundID.'">'.$$alphaBetaFundSym.'</a>';
	//$returnsFundID = $portVar1;
}else{ 
	$alphaBetaFundSymLink = $$alphaBetaFundSym; 
}

//Set fund id as an array value
$alphaBetaArray[] = $$alphaBetaFundID;
?>
<!--START ALPHA BETA VS SP500-->
<div class="portlet" id="<?php echo $portletID; ?>">
    <div class="portlet-title handle" <?php echo $addFundColor; ?>>
    	<div class="caption">
        	<i class="icon-bell"></i>
			<?php echo $alphaBetaFundSymLink ?> | Alpha/Beta VS. S&amp;P 500
        </div>
        <div class="tools" id="update-button-<?php echo $portletID; ?>">
        	<?php if($pageID != "04-00-00-001"/*community public profile*/){?>
            <a href="javascript:void(0);" 
				<?php if($disableFAB == 0){?>onClick="addToDash('portlet_id=<?php echo $portletType;?>~<?php echo $fundID; ?>~0~0','<?php echo $portletID;?>');" title="Add To Dashboard" class="addtodashboard balloon"<?php }?>
                <?php if($disableFAB == 1){?>title="Portlet On Dashboard" class="addtodashboard balloon disabled"<?php }?>
                <?php if($disableFAB == 2){?>onClick="removeFromDash('portlet_id=<?php echo $portletID; ?>','<?php echo $undoColumns;?>');" title="Remove From Dashboard" class="remove balloon"<?php }?>>
            </a>
            <a href="" class="reload balloon" onClick="loadAlphaBeta('<?php echo $$alphaBetaFundID;?>', '<?php echo $$alphaBetaFundSym; ?>')" title="Refresh Portlet"></a>
            <?php } ?>
            <a href="" class="collapse balloon" title="Expand/Collapse Portlet"></a>
        </div>
    </div><!--portlet-title-->
    <div class="portlet-body">
      
        <table class="table table-striped table-bordered table-hover">
            <tbody class="load-alpha-beta-<?php echo $$alphaBetaFundID; ?>">
                <?php
                $query = '
                    SELECT thirtyDayAlphaSkipAAR AS alpha, thirtyDayBetaSkip AS beta, thirtyDayRSquaredSkip AS squared
                    FROM '.$_SESSION['fund_alphabeta_table'].'
                    WHERE fund_id=:fund_id
                    ORDER BY timestamp DESC
                    LIMIT 1
                ';
                
                try{
                    $rsGetFund = $mLink->prepare($query);
                    $aValues = array(
                        ':fund_id' 	=> $$alphaBetaFundID
                        
                    );
                    $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                    $rsGetFund->execute($aValues);
                }
                catch(PDOException $error){
                    // Log any error
                    file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                }
                
                $fund = $rsGetFund->fetch(PDO::FETCH_ASSOC);
                
                $alpha = ''.round(($fund['alpha']*100), 2).'%';
                
                $beta = round($fund['beta'], 2);
                
                $rSquared = round($fund['squared'],2 );
                
                ?>
                <tr>
                    <th>Alpha</th>
                    <td><?php echo $alpha; ?></td>
                </tr>
                <tr>
                    <th>Beta</th>
                    <td><?php echo $beta; ?></td>
                </tr>
                <tr>
                    <th>R-Squared</th>
                    <td><?php echo $rSquared; ?></td>
                </tr>
            </tbody>
        </table>
      	<div class="alpha-beta-loading-<?php echo $$alphaBetaFundID; ?>"></div>
    </div><!--portlet-body-->
</div><!--portlet-->
<!--END ALPHA BETA VS SP500-->