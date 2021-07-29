<?php
//RECENT RETURNS VS MAJOR INDEXES PORTLET
/*
Supporting files:
	AJAX		- process/ajax/portlets/fund-returns-index-ajax.php
	JAVASCRIPT	- includes/scripts/portlets/fund-returns-index.js.php
	HTML		- THIS DOCUMENT includes/portlets/fund-returns-index.php
	
*/


//portVar1 = Fund ID
//portVar2 = NOT SET
//portVar3 = NOT SET



$portletType = "fund-returns-index";

if(!isset($fundID)) {
	//If FUND ID is not set, portlet is on dashboard
	include('fund-symbol-query.php');
	
	$returnsFundSym	= 'returnsFundSym_'.$fundInfo['fund_symbol'].'';
	$$returnsFundSym = $fundInfo['fund_symbol'];
	
	$returnsFundID = 'returnsFundID_'.$$fundSym.'';
	$$returnsFundID = $fundInfo['fund_id'];
	
	//Hide The "Add To Dashboard symbol"
	$disableFRMI = 2;
	
	$riArray[] = $$returnsFundID;
}else{
	//If FUND ID is Set, portlet is on funds pages
	$disableFRMI = 0;
	
	//Query Dashboard columns to see if portlet already exists on dashboard
	include('check-dash-query.php');
	
	//Set New Variables so not to overwrite eachother
	if($cnt == 0){
		//Show add to dash
		$disableFRMI = 0;	
	}else{
		//Hide add to dash
		$disableFRMI = 1;	
	}
	$fundSym = get_funds($mLink, $fundID, 'fundSymbol');
	$returnsFundSym = 'returnsFundSym_'.$fundSym.'';
	$$returnsFundSym = $fundSym;
	
	
	$returnsFundID = 'returnsFundID_'.$$fundSym.'';
	$$returnsFundID = $fundID;
	
	$riArray[] = $$returnsFundID;
}

if(!isset($fundID)){
	$returnsFundSymLink = '<a href="?page=03-01-00-001&fund='.$$returnsFundID.'">'.$$returnsFundSym.'</a>';
	//$returnsFundID = $portVar1;
}else{ 
	$returnsFundSymLink = $$returnsFundSym; 
}
?>
<!--START RECENT RETURNS VS MAJOR INDEXES-->	
<div class="portlet" id="<?php echo $portletID; ?>">
    <div class="portlet-title handle" <?php echo $addFundColor; ?>>
        <div class="caption">
            <i class="icon-bell"></i>
            <?php echo $returnsFundSymLink;?> | Recent Returns vs Major Indexes
        </div>
        <div class="tools" id="update-button-<?php echo $portletID; ?>">
        	<?php if($pageID != "04-00-00-001"/*community public profile*/){?>
            <a href="javascript:void(0);" 
                <?php if($disableFRMI == 0){?>onClick="addToDash('portlet_id=<?php echo $portletType;?>~<?php echo $$returnsFundID; ?>~0~0','<?php echo $portletID;?>');" title="Add To Dashboard" class="addtodashboard balloon"<?php }?>
                <?php if($disableFRMI == 1){?>title="Portlet On Dashboard" class="addtodashboard balloon disabled"<?php }?>
                <?php if($disableFRMI == 2){?>onClick="removeFromDash('portlet_id=<?php echo $portletID; ?>','<?php echo $undoColumns;?>');" title="Remove From Dashboard" class="remove balloon"<?php }?>>
            </a>
            <a href="" class="reload balloon" onClick="getMTD('<?php echo $$returnsFundID;?>','<?php echo $$returnsFundSym; ?>')" title="Refresh Portlet"></a>
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
                    <th>Beating</th>
                    <th>Today</th>
                    <th>MTD</th>
                    <th>QTD</th>
                    <th>YTD</th>
                </tr>
            </thead>
            <tbody class="load-fund-returns-index-ajax-<?php echo $$returnsFundID;?>">
            	<?php
				
				
				//echo time();				
				
				//Get aggregate statistics
				$query = '
					SELECT MTDReturn, QTDReturn, YTDReturn, sp500MTDReturn, sp500QTDReturn, sp500YTDReturn, nasdaqMTDReturn, nasdaqQTDReturn, nasdaqYTDReturn
					FROM '.$_SESSION['fund_aggregate_table'].'
					WHERE fund_id=:fund_id
					ORDER BY timestamp DESC
					LIMIT 1
				';
				
				try{
					$rsGetFund = $mLink->prepare($query);
					$aValues = array(
						':fund_id' 	=> $$returnsFundID
						
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsGetFund->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				$fund = $rsGetFund->fetch(PDO::FETCH_ASSOC);
				
				//Get Live Price Info
				$query = '
					SELECT *
					FROM '.$_SESSION['fund_liveprice_table'].'
					WHERE fund_id=:fund_id
				';
				
				try{
					$rsGetLive = $mLink->prepare($query);
					$aValues = array(
						':fund_id' 	=> $$returnsFundID
						
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsGetLive->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				$live = $rsGetLive->fetch(PDO::FETCH_ASSOC);
				
				$todayReturn = number_format($live['todayReturn'], 2, '.', '');
				$spTodayReturn = number_format($live['sp500TodayReturn'], 2, '.', '');
				$nasdaqTodayReturn = number_format($live['nasdaqTodayReturn'], 2, '.', '');
				
				if($todayReturn >= $spTodayReturn){
					$beatSP = '<span class="label label-success"><i class="icon-asterisk"></i></span>';	
				}else{
					$beatSP = '';	
				}
				
				if($todayReturn >= $nasdaqTodayReturn){
					$beatNasdaq = '<span class="label label-success"><i class="icon-asterisk"></i></span>';	
				}else{
					$beatNasdaq = '';
				}
				
				?>
                <tr>
                    <td style="border-left:5px solid #57b5e3;"><strong><?php echo $$returnsFundSym;?></strong></td>
                    <td></td>
                    <td><?php echo $todayReturn; ?>%</td>
                    <td><?php echo number_format($fund['MTDReturn'], 2, '.', '');?>%</td><!--MTD-->
                    <td><?php echo number_format($fund['QTDReturn'], 2, '.', '');?>%</td><!--QTD-->
                    <td><?php echo number_format($fund['YTDReturn'], 2, '.', '');?>%</td><!--YTD-->
                </tr>
                <tr>
                    <td><strong>S&amp;P 500</strong></td>
                    <td><?php echo $beatSP;?></td>
                    <td><?php echo $spTodayReturn; ?>%</td>
                    <td><?php echo number_format($fund['sp500MTDReturn'], 2, '.', '');?>%</td><!--MTD-->
                    <td><?php echo number_format($fund['sp500QTDReturn'], 2, '.', '');?>%</td><!--QTD-->
                    <td><?php echo number_format($fund['sp500YTDReturn'], 2, '.', '');?>%</td><!--YTD-->
                </tr>
                <tr>
                    <td><strong>NASDAQ</strong></td>
                    <td><?php echo $beatNasdaq;?></td>
                    <td><?php echo $nasdaqTodayReturn; ?>%</td>
                    <td><?php echo number_format($fund['nasdaqMTDReturn'], 2, '.', '');?>%</td><!--MTD-->
                    <td><?php echo number_format($fund['nasdaqQTDReturn'], 2, '.', '');?>%</td><!--QTD-->
                    <td><?php echo number_format($fund['nasdaqYTDReturn'], 2, '.', '');?>%</td><!--YTD-->
                </tr>
            </tbody>
        </table>
    </div><!--table-scrollable-->
    
    </div><!--portlet-body-->
</div><!--portlet-->
<!--END RECENT RETURNS VS MAJOR INDEXES-->
