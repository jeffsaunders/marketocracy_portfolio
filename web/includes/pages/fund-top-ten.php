<?php
/*
FUND VOLATILITY - HTML FILE

SUPPORTING FILES:
_______________________________________________________________

AJAX 			- process/ajax/
PHP JAVASCRIPT	- includes/scripts/
HTML 			- THIS FILE includes/pages/fund-top-ten.php
_______________________________________________________________

*/

$aHoldings = array();

$query = "
	SELECT *
	FROM ".$_SESSION['fund_stratification_basic_table']." 
	WHERE fund_id=:fund_id AND totalShares>'0'	
	ORDER BY fundRatio DESC
";
try{
	$rsPositions = $mLink->prepare($query);
	$aValues = array(
		':fund_id'	=> $fundID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsPositions->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$cnt = 0;

while($pos = $rsPositions->fetch(PDO::FETCH_ASSOC)){
	
	$cnt = $cnt + 1;
	
	$gains = $pos['gains'];
	if($gains < 0){
		$gains = '<span class="label label-danger">-$'.number_format(str_replace('-', '', $gains), 2, '.', ',').'</span>';	
	}else{
		$gains = '$'.number_format($gains, 2, '.', ',');	
	}
	
	$value = $pos['currentValue'];
	
	
        //replace cnt with symbol to prevent dupes
        $cnt = $pos['stockSymbol'];
    
	$query = "
		SELECT Name AS companyName, Symbol AS symbol, Last AS CurrentPrice, PercentChangeFromPreviousClose AS pChange
		FROM `stock_feed`
		WHERE `Symbol`=:symbol
	";
	try{
		$rsStock = $fLink->prepare($query);
		$aValues = array(
			':symbol'	=> $pos['stockSymbol']
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsStock->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$stockInfo = $rsStock->fetch(PDO::FETCH_ASSOC);
	    
	$aHoldings[$cnt]['symbol'] 		= $pos['stockSymbol'];
	$aHoldings[$cnt]['value'] 		= $value;
	$aHoldings[$cnt]['ratio']		= number_format(($pos['fundRatio']*100), 2, '.', '').'%';
	$aHoldings[$cnt]['ratioSort']	= $pos['fundRatio'];
	$aHoldings[$cnt]['today']		= $stockInfo['pChange'];
	$aHoldings[$cnt]['todaySort']	= $stockInfo['pChange'];
	$aHoldings[$cnt]['return']		= number_format($pos['totalReturn'], 2, '.', '').'%';
	$aHoldings[$cnt]['gains']		= $gains;
	$aHoldings[$cnt]['gainsSort']	= $pos['gains'];
	$aHoldings[$cnt]['price']		= $pos['currentPrice'];
}




?>
        
        <!-- BEGIN PAGE CONTENT-->
        <div class="row">
            <div class="col-md-12">

            	<?php /*?><div class="note note-warning">
                	<h4 class="block">PAGE AVAILABLE SOON - UNDER CONSTRUCTION</h4>
                	<p>The information reflected on this page is not accurate. Please check back later.</p>
                </div><!--note--><?php */?>
               	
                <!-- BEGIN FUND INFO -->
                <?php include('includes/portlets/fund-live-info.php');?>
                <!-- END FUND INFO -->
                
                <!-- BEGIN VOLATILITY ANALYSIS-->
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption"><i class="icon-arrow-up"></i>Top Ten</div>
                        <div class="tools">
                            <a href="" class="collapse"></a>
                            <a href="" class="reload"></a>
                        </div>
                    </div><!--portlet-title-->
                    <div class="portlet-body">
                    	
                    	<div class="row">
                        	<div class="col-md-6">
                            	<table class="table table-bordered table-striped table-condensed flip-content volatility-table">
                                    <thead class="flip-content">
                                        <tr>
                                            <th colspan="7" class="strat-table-header">TOP 10 HOLDINGS</th>
                                        </tr>
                                        <tr>
                                        	<th></th>
                                        	<th>Symbol</th>
                                            <th>Value</th>
                                            <th>Portion of Fund</th>
                                            <th>Today</th>
                                            <th>Inception Return</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
                                        <?php
										$stopCnt = 0;
										
										foreach($aHoldings as $key=>$aPos){
											
											$stopCnt = $stopCnt +1;
											
											if($stopCnt <= 10){
												
												$value = $aPos['value'];
												
												if($value < 0){
													$value = '-$'.number_format(str_replace('-', '', $value), 2, '.', ',');	
												}else{
													$value = '$'.number_format($value, 2, '.', ',');	
												}
												?>
												<tr>
													<td width="3%"><?php echo $stopCnt;?></td>
													<td><span class="label label-info"><?php echo $aPos['symbol'];?></span></td>
													<td><?php echo $value;?></td>
													<td style="text-align:center;"><?php echo $aPos['ratio'];?></td>
													<td style="text-align:center;"><?php echo number_format($aPos['today'],2,'.',',');?>%</td>
													<td style="text-align:center;"><?php echo $aPos['return'];?></td>
													<td><a href="javascript:void(0);" class="btn btn-xs btn-info">Details</a></td>
												</tr>
												<?php	
											}
										}
										?>
                                    
                                    </tbody>
                                </table>
                            </div><!--col-md-6-->
                            
                            <div class="col-md-6">
                            	
                                <div class="row">
                                	<div class="col-md-6">
                                        <table class="table table-bordered table-striped table-condensed flip-content strat-style-table">
                                            <thead class="flip-content">
                                                <tr>
                                                    <th colspan="3" class="strat-table-header">10 MOST PROFITABLE</th>
                                                </tr>
                                                <tr>
                                                    <th></th>
                                                    <th>Symbol</th>
                                                    <th>Gains</th>
                                                </tr>
                                            </thead>
                                            
                                            <tbody>
                                                <?php
                                                usort($aHoldings, 'order_by_gainsSort');
        
                                                function order_by_gainsSort($a, $b) {
                                                    return $b['gainsSort'] > $a['gainsSort'] ? 1 : -1;
                                                }
                                                
                                                $stopCnt = 0;
                                                
                                                foreach($aHoldings as $key=>$aPos){
                                                    
													
													
                                                    $stopCnt = $stopCnt +1;
                                                    
                                                    if($stopCnt <= 10){
                                                    
													
													?>
                                                    <tr>
                                                        <td width="3%"><?php echo $stopCnt;?></td>
                                                        <td><span class="btn btn-xs btn-info"><?php echo $aPos['symbol'];?></span></td>
                                                        <td><?php echo $aPos['gains'];?></td>
                                                    </tr>
                                                    <?php	
                                                    }
                                                }
                                                
                                                ?>
                                                
                                            </tbody>
                                        </table>
                                	</div><!--col-md-6-->
                                    
                                    <div class="col-md-6">
                                        <table class="table table-bordered table-striped table-condensed flip-content strat-style-table">
                                            <thead class="flip-content">
                                                <tr>
                                                    <th colspan="3" class="strat-table-header">10 LEAST PROFITABLE</th>
                                                </tr>
                                                <tr>
                                                    <th></th>
                                                    <th>Symbol</th>
                                                    <th>Gains</th>
                                                </tr>
                                            </thead>
                                            
                                            <tbody>
                                                <?php
                                                usort($aHoldings, 'order_by_gainsSortDown');
        
                                                function order_by_gainsSortDown($a, $b) {
                                                    return $b['gainsSort'] > $a['gainsSort'] ? -1 : 1;
                                                }
                                                
                                                $stopCnt = 0;
                                                
                                                foreach($aHoldings as $key=>$aPos){
                                                    
                                                    $stopCnt = $stopCnt +1;
                                                    
                                                    if($stopCnt <= 10){
                                                    ?>
                                                    <tr>
                                                        <td width="3%"><?php echo $stopCnt;?></td>
                                                        <td><span class="btn btn-xs btn-info"><?php echo $aPos['symbol'];?></span></td>
                                                        <td><?php echo $aPos['gains'];?></td>
                                                    </tr>
                                                    <?php	
                                                    }
                                                }
                                                
                                                ?>
                                                
                                            </tbody>
                                        </table>
                                	</div><!--col-md-6-->
                                    
                                </div><!--row-->
                                
                            </div><!--col-md-6-->
                        </div><!--row-->
                        
                        <div class="row">
                        	<div class="col-md-6">
                            	<table class="table table-bordered table-striped table-condensed flip-content strat-style-table">
                                    <thead class="flip-content">
                                        <tr>
                                            <th colspan="6" class="strat-table-header">TOP 10 WINNERS</th>
                                        </tr>
                                        <tr>
                                        	<th></th>
                                        	<th>Symbol</th>
                                            <th>Value</th>
                                            <th>Portion of Fund</th>
                                            <th>Today</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
                                        <?php
										usort($aHoldings, 'order_by_todaySort');

										function order_by_todaySort($a, $b) {
											return $b['todaySort'] > $a['todaySort'] ? 1 : -1;
										}
										
										$stopCnt = 0;
										
										foreach($aHoldings as $key=>$aPos){
											
											$stopCnt = $stopCnt +1;
											
											if($stopCnt <= 10){
												
												$value = $aPos['value'];
												
												if($value < 0){
													$value = '-$'.number_format(str_replace('-', '', $value), 2, '.', ',');	
												}else{
													$value = '$'.number_format($value, 2, '.', ',');	
												}
												?>
												<tr>
													<td width="3%"><?php echo $stopCnt;?></td>
													<td><span class="label label-info"><?php echo $aPos['symbol'];?></span></td>
													<td><?php echo $value;?></td>
													<td style="text-align:center;"><?php echo $aPos['ratio'];?></td>
													<td style="text-align:center;"><?php echo number_format($aPos['today'],2,'.',',');?>%</td>
													<td><a href="javascript:void(0);" class="btn btn-xs btn-info">Details</a></td>
												</tr>
												<?php	
											}
										}

										?>
                                    
                                    </tbody>
                                </table>
                            </div><!--col-md-6-->
                            
                            <div class="col-md-6">
                            	<table class="table table-bordered table-striped table-condensed flip-content strat-style-table">
                                    <thead class="flip-content">
                                        <tr>
                                            <th colspan="6" class="strat-table-header">TOP 10 LOSERS</th>
                                        </tr>
                                        <tr>
                                        	<th></th>
                                        	<th>Symbol</th>
                                            <th>Value</th>
                                            <th>Portion of Fund</th>
                                            <th>Today</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
                                        <?php
										usort($aHoldings, 'order_by_todaySortDown');

										function order_by_todaySortDown($a, $b) {
											return $b['todaySort'] > $a['todaySort'] ? -1 : 1;
										}
										
										$stopCnt = 0;
										
										foreach($aHoldings as $key=>$aPos){
											if($aPos['value'] != 0){
												$stopCnt = $stopCnt +1;
												
												if($stopCnt <= 10){
													$value = $aPos['value'];
												
													if($value < 0){
														$value = '-$'.number_format(str_replace('-', '', $value), 2, '.', ',');	
													}else{
														$value = '$'.number_format($value, 2, '.', ',');	
													}
													?>
													<tr>
														<td width="3%"><?php echo $stopCnt;?></td>
														<td><span class="label label-info"><?php echo $aPos['symbol'];?></span></td>
														<td><?php echo $value;?></td>
														<td style="text-align:center;"><?php echo $aPos['ratio'];?></td>
														<td style="text-align:center;"><?php echo number_format($aPos['today'],2,'.',',');?>%</td>
														<td><a href="javascript:void(0);" class="btn btn-xs btn-info">Details</a></td>
													</tr>
													<?php	
												}
											}
										}

										?>
                                    
                                    </tbody>
                                </table>
                            </div><!--col-md-6-->
                        </div><!--row-->
                   
                    </div><!--portlet-body-->
                </div><!--portlet-->
                <!--END VOLATILITY ANALYSIS-->
                
                
                
            </div>
        </div>
        <!-- END PAGE CONTENT-->