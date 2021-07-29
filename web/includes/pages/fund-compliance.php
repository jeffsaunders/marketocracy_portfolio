<?php
//Start by getting aggregate statistics for fund
$query = '
	SELECT * 
	FROM '.$_SESSION['fund_aggregate_table'].'
	WHERE fund_id=:fund_id
	ORDER BY asOfDate DESC
	LIMIT 1
';

try{
	$rsStats = $mLink->prepare($query);
	$aValues = array(
		':fund_id'	=> $fundID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsStats->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$fund = $rsStats->fetch(PDO::FETCH_ASSOC);

$sectorIncep = $fund['sectorCompliancePercentageSinceInception'];
if($sectorIncep == NULL){
	$sectorIncep = 'N/A';	
}else{
	$sectorIncep = ''.number_format($sectorIncep, 0).'%';	
}

$sectorCurrentQtr = $fund['sectorCompliancePercentageCurrentQuarter'];
if($sectorCurrentQtr == NULL){
	$sectorCurrentQtr = 'N/A';	
}else{
	$sectorCurrentQtr = ''.number_format($sectorCurrentQtr, 0).'%';	
}

$sectorPrevQtr = $fund['sectorCompliancePercentagePreviousQuarter'];
if($sectorPrevQtr == NULL){
	$sectorPrevQtr = 'N/A';	
}else{
	$sectorPrevQtr = ''.number_format($sectorPrevQtr, 0).'%';	
}

$sector90Days = $fund['sectorCompliancePercentageLast90Days'];
if($sector90Days == NULL){
	$sector90Days = 'N/A';	
}else{
	$sector90Days = ''.number_format($sector90Days, 0).'%';	
}

//Get Live Price Data
$query = '
	SELECT * 
	FROM '.$_SESSION['fund_liveprice_table'].'
	WHERE fund_id=:fund_id
';

try{
	$rsLivePrice = $mLink->prepare($query);
	$aValues = array(
		':fund_id'	=> $fundID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsLivePrice->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$livePrice = $rsLivePrice->fetch(PDO::FETCH_ASSOC);

//get Total Value
$totalValue = $livePrice['totalValue'];
$showTotalValue = number_format($totalValue, 2, '.', ',');

//Get Cash Value
$cashValue = $livePrice['cashValue'];
$showCashValue = number_format($cashValue, 2, '.', ',');

if($showCashValue < 0){
	$showCashValue = '-$'.str_replace('-','',$showCashValue).'';	
}else{
	$showCashValue = '$'.$showCashValue.'';	
}

//Get Rule Violations
$vCash35 	= $livePrice['violatesCash35'];
$vDiverse25	= $livePrice['violatesDiversification25'];
$vDiverse10 = $livePrice['violatesDiversification10'];
$isInMargin	= $livePrice['isInMargin'];

if($vCash35 == "0"){
	$vCash35 = '<span class="label label-success">Pass</span>';	
}else{
	$vCash35 = '<span class="label label-danger">Fail</span>';
}

if($vDiverse25 == "0"){
	$vDiverse25 = '<span class="label label-success">Pass</span>';	
}else{
	$vDiverse25 = '<span class="label label-danger">Fail</span>';
}

if($vDiverse10 == "0"){
	$vDiverse10 = '<span class="label label-success">Pass</span>';	
}else{
	$vDiverse10 = '<span class="label label-danger">Fail</span>';
}

if($isInMargin == "0"){
	$isInMargin = '<span class="label label-success">Pass</span>';	
}else{
	$isInMargin = '<span class="label label-danger">Fail</span>';
}

//Get Cash percentage

$cashPercent = number_format((($cashValue / $totalValue)*100), 0);


//Check to see percentage of positions under 10%
$query = '
	SELECT *
	FROM `members_fund_positions`
	WHERE fund_id=:fund_id and date=(
		SELECT MAX(date)
		FROM `members_fund_positions` 
		WHERE fund_id=:fund_id)
	GROUP BY stockSymbol
	ORDER BY ratio DESC
';

try{
	$rsPos = $mLink->prepare($query);
	$aValues = array(
		':fund_id'	=> $fundID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsPos->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

//Set vars to zero for calculations
$under10 = 0;
$over10 = 0;
$cnt = 0;

while($position = $rsPos->fetch(PDO::FETCH_ASSOC)){
	//Get ratio from db
	$ratio = $position['ratio'];
	
	$cnt = $cnt + 1;
	
	if($ratio < .1){
		$under10 = $under10 + 1;
	}elseif($ratio >= .1){
		$over10 = $over10 + 1;	
	}
		
}

$under10 = round((($under10 / $cnt)*100),2);
$over10 = round((($over10 / $cnt)*100),2);

		
	$getFund = new fund($dbPortfolio, $dbFeed, $fundID);
	
	$showSectorComp	= ($getFund->fundType == "sector" ? true : false);
	
	
	
	if($showSectorComp == true){
		$isCompliant 	= $getFund->getTodayCompliance();
		
		$sector80 = ($isCompliant == true ? '<span class="label label-success">Pass</span>' : '<span class="label label-danger">Fail</span>');
		
	}
	
	


?>
         
        <!-- BEGIN PAGE CONTENT-->
        
        <div class="row">
            <div class="col-md-12">
                <?php /*?><div class="note note-warning">
                    <h4 class="block">PAGE UNDER CONSTRUCTION</h4>
                    <p>The information reflected on this page is not accurate. Please check back later.</p>
                </div><!--note--><?php */?>
                
                <!-- BEGIN FUND INFO -->
                <?php include('includes/portlets/fund-live-info.php');?>
                <!-- END FUND INFO -->
                
                <div class="tabbable tabbable-custom">
                    <ul class="nav nav-tabs">
                        <li><a href="#tab_0" data-toggle="tab">Compliance Overview</a></li>
                        <!--<li><a href="#tab_1" data-toggle="tab">Report Card</a></li>-->
                        <li class="active"><a href="#tab_2" data-toggle="tab">Current Compliance</a></li>
                        <li><a href="#tab_3" data-toggle="tab">Historical Compliance</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane" id="tab_0">
                        	
                            <div class="portlet">
                                <div class="portlet-title">
                                    <div class="caption"><i class="icon-reorder"></i>Compliance Overview - Current</div>
                                    <div class="tools">
                                        <a href="javascript:;" class="collapse"></a>
                                        <a href="javascript:;" class="reload"></a>
                                    </div>
                                </div><!--portlet-title-->
                                <div class="portlet-body">
                                
                                    <p>In order to be eligible for Marketocracy rankings, you must follow diversification rules that are similar to those that real-world fund managers must face. We require that you meet these rules for a majority (51%) of a given quarter in order to be compliant — so we‘ve designed this report to help you keep track of your current compliance statistics, as well as your historical compliance and ranking eligibility.</p>
                                    <p>There are a few simple rules to remaining compliant:</p>
                                    
                                    <ol>
                                        <li>You cannot purchase a stock so that it will increase your position to over 25% of your portfolio‘s value. If you violate this rule, your fund's effective inception date will be reset to the date that you bring the find back into compliance with all compliance rules, not just the 25% rule</li>
                                        <li>If a sub-25% position rises in value above the 25% threshold (without additional purchases), your fund will be out of compliance until you sell of enough to bring it below the threshold. (However, your inception date will not be reset.)</li>
                                        <li>You may only hold up to 35% if your portfolio‘s value in cash. The other 65% (or more) must be invested in stocks. Real fund managers are paid to invest, not hold cash, and so the SEC requires that they meet this cash rule — hence we also require you to follow this rule a majority (51% or more) of the time.</li>
                                        <li>Half of your long portfolio may be in positions that may not exceed 25% of the value your total portfolio value. That means that you can have two 25% positions taking up that entire half of the portfolio, or, you can have five 10% positions (since none exceed 25%).</li>
                                        <li>The other half of your long portfolio may be in positions that may not exceed 10% of the value your total portfolio value. At the least, this means that you would need to hold 5 long positions worth 10% each. However, you can (and likely will) hold a few more positions to make sure you have some breathing room below the 10% threshold.</li>
                                        <li>Negative cash balances may not exceed 5% of total portfolio value. If this happens for more than 7 days per quarter, you are out of compliance. That means that you may not spend more cash than you have to buy stock — or in other words, you may not use margin.</li>
                                    </ol>
                                    
                                    <p>Note: Mirror funds, or funds within your account with a 50% or greater overlap in holdings with another fund, are disqualified from ranking consideration.</p>
                                
                                </div><!--END PORTLET BODY-->
                        	</div><!--END PORTLET-->
                        
                        </div><!--END TAB 1-->
                        
                        <div class="tab-pane" id="tab_1">
                        
                            
                        
                        </div><!--END TAB 2-->
                        
                        <div class="tab-pane active" id="tab_2">
                        	
                            <div class="portlet">
                                <div class="portlet-title">
                                    <div class="caption"><i class="icon-reorder"></i>Your Current SEC Compliance Report Card</div>
                                    <div class="tools">
                                        <a href="javascript:;" class="collapse"></a>
                                        <a href="javascript:;" class="reload"></a>
                                    </div>
                                </div><!--portlet-title-->
                                
                                <div class="portlet-body">
                                    
                                    <table id="fund-zone-table" class="table table-bordered table-striped table-condensed flip-content">
                                        <thead class="flip-content">
                                            <tr>
                                            	<th class="fzt-blue">#</th>
                                                <th class="fzt-aleft fzt-blue">RULE</th>
                                                <th class="fzt-blue">PERIOD</th>
                                                <th class="fzt-blue">TODAY</th>
                                            </tr>
                                        </thead>
                                        
                                        <tbody>
                                            <tr>
                                            	<td align="center">1</td>
                                                <td>You cannot <strong>purchase</strong> stock such that it will increase a position more that 25% of your portfolio.</td>
                                                <td>Always</td>
                                            	<td><span class="label label-success">Pass</span></td>
                                            </tr>
                                            <tr>
                                            	<td align="center">2</td>
                                                <td>You must be at least 65% invested.</td>
                                                <td>Majority of Quarter</td>
                                                <td><?php echo $vCash35;?></td>
                                            </tr>
                                            <tr>
                                            	<td align="center">3</td>
                                                <td>No one stock can exceed 25% of your portfolio assets.</td>
                                                <td>Majority of Quarter</td>
                                                <td><?php echo $vDiverse25;?></td>
                                            </tr>
                                            
                                            <tr>
                                            	<td align="center">4</td>
                                                <td>Half your portfolio must be made up of stocks of 10% (or less) of your portfolio assets.</td>
                                                <td>Majority of Quarter</td>
                                                <td><?php echo $vDiverse10;?></td>
                                            </tr>
                                            <tr>
                                            	<td align="center">5</td>
                                                <td>Excessive margin.</td>
                                                <td>7 days/Quarter</td>
                                                <td><?php echo $isInMargin;?></td>
                                            </tr>
                                        
                                        </tbody>
                                    </table>
                                  
                                </div><!--END PORTLET BODY-->
                            </div><!--END PORTLET-->
                            
                            <?php if($showSectorComp == true){ ?>
                            <div class="portlet">
                                <div class="portlet-title">
                                    <div class="caption"><i class="icon-reorder"></i>Your Current SECTOR Compliance Report Card</div>
                                    <div class="tools">
                                        <a href="javascript:;" class="collapse"></a>
                                        <a href="javascript:;" class="reload"></a>
                                    </div>
                                </div><!--portlet-title-->
                                
                                <div class="portlet-body">
                                    
                                    <table id="fund-zone-table" class="table table-bordered table-striped table-condensed flip-content">
                                        <thead class="flip-content">
                                            <tr>
                                            	<th class="fzt-blue">#</th>
                                                <th class="fzt-aleft fzt-blue">RULE</th>
                                                <th class="fzt-blue">PERIOD</th>
                                                <th class="fzt-blue">TODAY</th>
                                            </tr>
                                        </thead>
                                        
                                        <tbody>
                                            <tr>
                                            	<td align="center">6</td>
                                                <td>You must be at least 80% invested in the <?php echo $getFund->fundSectorName;?> Sector.</td>
                                                <td>Majority of Quarter</td>
                                            	<td><?php echo $sector80; ?></td>
                                            </tr>
                                            
                                        
                                        </tbody>
                                    </table>
                                  
                                </div><!--END PORTLET BODY-->
                            </div><!--END PORTLET-->
							<?php }	?>
                            
                            <div class="portlet">
                                <div class="portlet-title">
                                    <div class="caption"><i class="icon-reorder"></i>Current Compliance - Details</div>
                                    <div class="tools">
                                        <a href="javascript:;" class="collapse"></a>
                                        <a href="javascript:;" class="reload"></a>
                                    </div>
                                </div><!--portlet-title-->
                                <div class="portlet-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p>To understand why you are or are not compliant currently, review each of the rules in more detail below</p>
                                            <p>Rule #1 says that you must be 65% invested — or that you may only hold up to 35% of your portfolio‘s value in cash. The table below shows you your status with regards to this 35% cash rule:</p>
                                            <table id="fund-zone-table" class="table table-bordered table-striped table-condensed flip-content">
                                                <thead class="flip-content">
                                                    <tr>
                                                    	<th class="fzt-organge" colspan="2">CASH COMPLIANCE - RULE #2</th>
                                                    </tr>
                                                    <tr>
                                                        <th class="fzt-aleft fzt-blue"></th>
                                                        <th class="fzt-blue">Value/%</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><strong>Your Portfolio Value</strong></td>
                                                        <td>$<?php echo $showTotalValue;?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Your Current Cash Value</strong></td>
                                                        <td><?php echo $showCashValue;?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Your Cash Percentage</strong></td>
                                                        <td><?php echo $cashPercent;?>%</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            
                                            <p>Rules 3 &amp; 4 deal with keeping your portfolio diversified. The first half of your portfolio may not have positions larger than 25%, and the second half may not have positions larger than 10%. Check the table to the right, which separates your portfolio into 2 halves, to see that your positions are under those thresholds:</p>
                                            
                                            <table id="fund-zone-table" class="table table-bordered table-striped table-condensed flip-content">
                                                <thead class="flip-content">
                                                    <tr>
                                                    	<th class="fzt-organge" colspan="2">DIVERSIFICATION SUMMARY - RULE #3 &amp; #4</th>
                                                    </tr>
                                                    <tr>
                                                        <th class="fzt-aleft fzt-blue"></th>
                                                        <th class="fzt-blue">%</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><strong>Positions under 10%</strong></td>
                                                        <td><?php echo $under10;?>%</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Positions over 10%</strong></td>
                                                        <td><?php echo $over10;?>%</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            
                                            <p>Finally, Rule #5 says that you may not have more than 7 days per quarter with negative cash balances exceeding 5%. The table below tells you your status with regards to that rule:</p>
                                            
                                            <table id="fund-zone-table" class="table table-bordered table-striped table-condensed flip-content">
                                                <thead class="flip-content">
                                                    <tr>
                                                    	<th class="fzt-organge" colspan="2">MARGIN DETAILS - RULE #5</th>
                                                    </tr>
                                                    <tr>
                                                        <th class="fzt-aleft fzt-blue"></th>
                                                        <th class="fzt-blue"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><strong>Margin Since Inception</strong></td>
                                                        <td><?php echo number_format($fund['marginSinceInception'], 0);?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Margin Previous Quarter</strong></td>
                                                        <td><?php echo number_format($fund['marginPreviousQuarter'], 0);?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Margin This Quarter</strong></td>
                                                        <td><?php echo number_format($fund['marginCurrentQuarter'], 0);?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Margin in Last 90 Days</strong></td>
                                                        <td><?php echo number_format($fund['marginLast90Days'], 0);?></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                           
                                        </div><!--col-md-6-->
                                        
                                        <div class="col-md-6">
                                            <table id="fund-zone-table" class="table table-bordered table-striped table-condensed flip-content">
                                                <thead class="flip-content">
                                                    <tr>
                                                    	<th class="fzt-organge" colspan="8">DIVERSIFICATION - RULE #3 &amp; #4</th>
                                                    </tr>
                                                    <tr class="fzt-blue">
                                                        <th class="fzt-aleft">SYMBOL</th>
                                                        <th>PRICE</th>
                                                        <th>SHARES</th>
                                                        <th>VALUE</th>
                                                        <th>CURRENT RETURN</th>
                                                        <th>PORTION OF FUND</th>
                                                        <th>ACTION</th>
                                                        <?php /*?><th>ACTION</th><?php */?>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                	<?php
													//Build query to use multiple times 
													/*$query = '
														SELECT *
														FROM `members_fund_positions`
														WHERE fund_id=:fund_id and date=(
															SELECT MAX(date)
															FROM `members_fund_positions` 
															WHERE fund_id=:fund_id)
														GROUP BY stockSymbol
														ORDER BY ratio DESC
													';*/
													$query = '
														SELECT * 
														FROM '.$_SESSION['fund_stratification_basic_table'].' 
														WHERE fund_id=:fund_id 
														GROUP BY stockSymbol 
														ORDER BY fundRatio DESC
													';
													
													//START: Query for counting the amount of rows for rowspan
													try{
														$rsPos = $mLink->prepare($query);
														$aValues = array(
															':fund_id'	=> $fundID
														);
														$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
														$rsPos->execute($aValues);
													}
													catch(PDOException $error){
														// Log any error
														file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
													}
													
													//Set vars to zero for calculations
													$first50 = 0;
													$rowCnt = 0;
													
													while($position = $rsPos->fetch(PDO::FETCH_ASSOC)){
														//Get ratio from db
														$ratio = $position['fundRatio'];
														
														//add ratio to ratio prior
														$first50 = $first50 + $ratio;
														
														//if the combined ratio is great than 50 or 50% stop the loop
														if($first50 > .5){
															break;	
														}
														
														//generate variable for number of rows for row span
														$rowCnt = $rowCnt + 1;
													}
													//END: Query for counting the amount of rows for rowspan
													
													//START: Query for displaying First 50% positions to the screen
													try{
														$rsPos = $mLink->prepare($query);
														$aValues = array(
															':fund_id'	=> $fundID
														);
														$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
														$rsPos->execute($aValues);
													}
													catch(PDOException $error){
														// Log any error
														file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
													}
													
													//Reset variables
													$first50 = 0;
													$cnt = 0;
													
													//loop through result
													while($position = $rsPos->fetch(PDO::FETCH_ASSOC)){
	
														$stockSymbol = $position['stockSymbol'];
														
														$query2 = "
															SELECT Name AS companyName, Symbol AS symbol, Last AS CurrentPrice, PercentChangeFromPreviousClose AS chang
															FROM `stock_feed`
															WHERE `Symbol`=:stockSymbol
														";
														try{
															$rsSymbols = $fLink->prepare($query2);
															$aValues = array(':stockSymbol'=>$stockSymbol);
															$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
															$rsSymbols->execute($aValues);
														}
														catch(PDOException $error){
															// Log any error
															file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
														}
														//$aStockInfo = array();
														$foo = $rsSymbols->fetch(PDO::FETCH_ASSOC);
														$price = $foo['CurrentPrice'];
														
														
														
														//$price = $position['currentPrice'];
														$shares = $position['totalShares'];
														
														
														$value = ($shares * $price);
														//$value = $position['currentValue'];
														$ratio = $position['fundRatio'];
														$return = $position['recentReturn'];
														
														$first50 = $first50 + $ratio;
														
														if($first50 > .5){
															break;	
														}
														
														$cnt = $cnt + 1;
														
														if($cnt == 1){
															$addRowSpan = '<td rowspan="'.$rowCnt.'" align="middle" valign="middle" style="vertical-align: middle;"><strong>First 50%</strong></td>';	
														}else{
															$addRowSpan = '';	
														}
														
														$ratio = ($ratio * 100);
														
														if($ratio > 25){
															$ratio = '<span class="label label-danger">'.number_format($ratio, 2, '.', '').'%</span>';	
														}else{
															$ratio = '<span class="label label-success">'.number_format($ratio, 2, '.', '').'%</span>';
														}
														
														if($return < 0){
															$return = '<span class="label label-warning">'.number_format($return, 2, '.', '').'%</span>';
														}else{
															$return = ''.number_format($return, 2, '.', '').'%';
														}
														
														$buyLink = '<a href="?page=02-00-00-001&symbols='.$stockSymbol.'&fund='.$fundID.'&trade=buy&wiz=buy" class="btn btn-success btn-xs" target="_blank" title="Buy '.$stockSymbol.'"><i class="icon-arrow-up"></i> Buy</a>';
														$sellLink = '<a href="?page=02-00-00-001&symbols='.$stockSymbol.'&fund='.$fundID.'&trade=sell&wiz=sell" class="btn btn-danger btn-xs" target="_blank" title="Sell '.$stockSymbol.'"><i class="icon-arrow-down"></i> Sell</a>';
														
														if($shares != "0"){
															?>
															<tr>
																<td><?php echo $stockSymbol;?></td>
																<td>$<?php echo number_format($price, 2, '.',',');?></td>
																<td><?php echo $shares;?></td>
																<td>$<?php echo number_format($value, 2, '.',',');?></td>
																<td><?php echo $return;?></td>
																<td><?php echo $ratio;?></td>
                                                                <td><?php echo $buyLink;?> <?php echo $sellLink;?></td>
																<?php /*?><td><a href="#" class="btn btn-default btn-xs">Details</a></td><?php */?>
																<?php echo $addRowSpan;?>
															</tr>
															<?php
														}
													}
													//END: Query for displaying First 50% positions to the screen
													
													
													//START: Query for counting the number of rows for the second 50%
													try{
														$rsPos = $mLink->prepare($query);
														$aValues = array(
															':fund_id'	=> $fundID
														);
														$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
														$rsPos->execute($aValues);
													}
													catch(PDOException $error){
														// Log any error
														file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
													}
													
													$second50 = 0;
													$rowCnt = 0;
													
													while($position = $rsPos->fetch(PDO::FETCH_ASSOC)){
														$ratio = $position['fundRatio'];
														
														$second50 = $second50 + $ratio;
														
														if($second50 < .5){
															continue;	
														}
														
														$rowCnt = $rowCnt + 1;
													}
													//END: Query for counting the number of rows for the second 50%
													
													//START: Query for displaying Second 50% positions to the screen
													try{
														$rsPos = $mLink->prepare($query);
														$aValues = array(
															':fund_id'	=> $fundID
														);
														$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
														$rsPos->execute($aValues);
													}
													catch(PDOException $error){
														// Log any error
														file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
													}
													
                                                    $second50 = 0;
                                                    $cnt = 0;
													
													while($position = $rsPos->fetch(PDO::FETCH_ASSOC)){
	
														$stockSymbol = $position['stockSymbol'];
														
														$query2 = "
															SELECT Name AS companyName, Symbol AS symbol, Last AS CurrentPrice, PercentChangeFromPreviousClose AS chang
															FROM `stock_feed`
															WHERE `Symbol`=:stockSymbol
														";
														try{
															$rsSymbols = $fLink->prepare($query2);
															$aValues = array(':stockSymbol'=>$stockSymbol);
															$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
															$rsSymbols->execute($aValues);
														}
														catch(PDOException $error){
															// Log any error
															file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
														}
														//$aStockInfo = array();
														$foo = $rsSymbols->fetch(PDO::FETCH_ASSOC);
														$price = $foo['CurrentPrice'];
														
														//$price = $position['currentPrice'];
														$shares = $position['totalShares'];
														
														$value = ($shares * $price);
														//$value = $position['currentValue'];
														$ratio = $position['fundRatio'];
														$return = $position['recentReturn'];
														
														$second50 = $second50 + $ratio;
														
														if($second50 < .5){
															continue;	
														}
														
														$cnt = $cnt + 1;
														
														if($cnt == 1){
															$addRowSpan = '<td rowspan="'.$rowCnt.'" align="middle" valign="middle" style="background-color:#eed97e !important;vertical-align: middle;"><strong>Second 50%</strong></td>';	
															$addBorder = 'style="border-top:5px solid #006da4;"';
														}else{
															$addRowSpan = '';	
															$addBorder = '';
														}
														
														$ratio = ($ratio * 100);
														
														if($ratio > 10){
															$ratio = '<span class="label label-danger">'.number_format($ratio, 2, '.', '').'%</span>';	
														}else{
															$ratio = '<span class="label label-success">'.number_format($ratio, 2, '.', '').'%</span>';
														}
														
														if($return < 0){
															$return = '<span class="label label-warning">'.number_format($return, 2, '.', '').'%</span>';
														}else{
															$return = ''.number_format($return, 2, '.', '').'%';
														}
														
														$buyLink = '<a href="?page=02-00-00-001&symbols='.$stockSymbol.'&fund='.$fundID.'&trade=buy&wiz=buy" class="btn btn-success btn-xs" target="_blank" title="Buy '.$stockSymbol.'"><i class="icon-arrow-up"></i> Buy</a>';
														$sellLink = '<a href="?page=02-00-00-001&symbols='.$stockSymbol.'&fund='.$fundID.'&trade=sell&wiz=sell" class="btn btn-danger btn-xs" target="_blank" title="Sell '.$stockSymbol.'"><i class="icon-arrow-down"></i> Sell</a>';
														if($shares != '0'){
															?>
															<tr <?php echo $addBorder;?>>
																<td><?php echo $stockSymbol;?></td>
																<td>$<?php echo number_format($price, 2, '.',',');?></td>
																<td><?php echo $shares;?></td>
																<td>$<?php echo number_format($value, 2, '.',',');?></td>
																<td><?php echo $return;?></td>
																<td><?php echo $ratio;?></td>
                                                                <td><?php echo $buyLink;?> <?php echo $sellLink;?></td>
																<?php /*?><td><a href="#" class="btn btn-default btn-xs">Details</a></td><?php */?>
																<?php echo $addRowSpan;?>
															</tr>
															<?php
														}
													}
													//END: Query for displaying Second 50% positions to the screen
													?>
                                                   
                                                </tbody>
                                            </table>
                                            <span class="label label-success">0.00%</span> Position follows compliance<br /><br />
                                            <span class="label label-danger">0.00%</span> Position breaks compliance<br /><br />
                                            <span class="label label-warning">-0.00%</span> Position with negative return
                                        </div><!--col-md-6-->
                                    </div><!--row-->
                                  
                                </div><!--END PORTLET BODY-->
                            </div><!--END PORTLET-->
                        
                        </div><!--END TAB 3-->
                        
                        <div class="tab-pane" id="tab_3">
                        
                            <div class="portlet">
                                <div class="portlet-title">
                                    <div class="caption"><i class="icon-reorder"></i>Historical Compliance - Details</div>
                                    <div class="tools">
                                        <a href="javascript:;" class="collapse"></a>
                                        <a href="javascript:;" class="reload"></a>
                                    </div>
                                </div>
                                <div class="portlet-body">
                                    <p>Because you must follow these rules over the course of time, we‘ve created the following Historical Compliance Details table — it tells you how often you meet all 5 of the compliance rules over the given time periods:</p>
                                    
                                    <table id="fund-zone-table" class="table table-bordered table-striped table-condensed flip-content">
                                        <thead class="flip-content">
                                            <tr>
                                            	<th class="fzt-organge" colspan="2">COMPLIANCE DETAILS</th>
                                            </tr>
                                            <tr>
                                                <th class="fzt-aleft fzt-blue"></th>
                                                <th class="fzt-blue"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong>Compliance Since Inception</strong></td>
                                                <td><?php echo number_format($fund['compliancePercentageSinceInception'], 2);?>%</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Compliance Previous Quarter</strong></td>
                                                <td><?php echo number_format($fund['compliancePercentagePreviousQuarter'], 2);?>%</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Compliance This Quarter</strong></td>
                                                <td><?php echo number_format($fund['compliancePercentageCurrentQuarter'], 2);?>%</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Compliance Last 90 Days</strong></td>
                                                <td><?php echo number_format($fund['compliancePercentageLast90Days'], 2);?>%</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    
                                    <p>In order to also be eligible for sector rankings, your fund must follow all of the rules mentioned above, plus one more: You must be at least 65% invested in the sector for a majority of the quarter to be ranked within that sector. So if Health Care is the sector you‘d like to compete in, you must have 65% of your portfolio‘s total value invested in stocks within the Health Care sector. The table below gives you your historical sector compliance percentages to help you keep track of your eligibility to be ranked in a particular sector:</p>
                                    
                                    <table id="fund-zone-table" class="table table-bordered table-striped table-condensed flip-content">
                                        <thead class="flip-content">
                                            <tr>
                                            	<th class="fzt-organge" colspan="3">SECTOR COMPLIANCE</th>
                                            </tr>
                                            <tr class="fzt-blue">
                                                <th class="fzt-aleft">PERIOD</th>
                                                <th>SECTOR</th>
                                                <th>COMPLIANCE</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong>Sector Compliance Since Inception</strong></td>
                                                <td>(N/A)</td>
                                                <td><?php echo $sectorIncep; ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Sector Compliance Previous Quarter</strong></td>
                                                <td>(N/A)</td>
                                                <td><?php echo $sectorPrevQtr;?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Sector Compliace Current Quarter</strong></td>
                                                <td>(N/A)</td>
                                                <td><?php echo $sectorCurrentQtr;?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Sector Compliance Last 90 Days</strong></td>
                                                <td>(N/A)</td>
                                                <td><?php echo $sector90Days; ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                  
                                </div><!--END PORTLET BODY-->
                            </div><!--END PORTLET-->
                        
                        </div><!--END TAB 4-->
                    
                        <div class="note note-info">
                        	<p>The Marketocracy diversification rules are based on the SEC rules for diversification in a mutual fund. You can read the complete rules <a href="#">here</a>. This report calculates the most complicated part of the compliance test. If this is your first fund, try spending $80,000 (8%) on each of 10 stocks. This will provide enough diversification to ensure initial compliance.</p>
                        </div><!--note-->
                     
                    </div><!--tab content-->
                </div><!--tab container-->
            </div><!--col-->
        </div><!--row-->
        <!-- END PAGE CONTENT-->
