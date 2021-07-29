<?php
/*
FUND VOLATILITY - HTML FILE

SUPPORTING FILES:
_______________________________________________________________

AJAX 			- process/ajax/
PHP JAVASCRIPT	- includes/scripts/
HTML 			- THIS FILE includes/pages/fund-volatility.php
_______________________________________________________________

*/

//GET AGGREGATE DATA
$query = '
	SELECT 
		bestReturn3Months AS br3m, 
		bestReturn6Months AS br6m, 
		bestReturn9Months AS br9m, 
		bestReturn12Months AS br12m, 
		bestReturn24Months AS br24m, 
		worstReturn3Months AS wr3m, 
		worstReturn6Months AS wr6m, 
		worstReturn9Months AS wr9m, 
		worstReturn12Months AS wr12m, 
		worstReturn24Months AS wr24m, 
		averageReturn3Months AS ar3m, 
		averageReturn6Months AS ar6m, 
		averageReturn9Months AS ar9m, 
		averageReturn12Months AS ar12m, 
		averageReturn24Months AS ar24m, 
		volatility3Months AS v3m, 
		volatility6Months AS v6m, 
		volatility9Months AS v9m, 
		volatility12Months AS v12m, 
		volatility24Months AS v24m
	FROM '.$_SESSION['fund_aggregate_table'].' 
	WHERE fund_id=:fund_id	
';
try{
	$rsAgg = $mLink->prepare($query);
	$aValues = array(
		':fund_id'	=> $fundID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsAgg->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$agg = $rsAgg->fetch(PDO::FETCH_ASSOC);

//GET ALPHA BETA DATA
$query = '
	SELECT 
		bestDailyAlpha AS b1a,
		bestWeeklyAlpha AS b7a,
		bestMonthlyAlpha AS b30a,
		best90Alpha AS b90a,
		best180Alpha AS b180a,
		best365Alpha AS b365a,
		worstDailyAlpha AS w1a,
		worstWeeklyAlpha AS w7a,
		worstMonthlyAlpha AS w30a,
		worst90Alpha AS w90a,
		worst180Alpha AS w180a,
		worst365Alpha AS w365a,
		avgDailyAlpha AS a1a,
		avgWeeklyAlpha AS a7a,
		avgMonthlyAlpha AS a30a,
		avg90Alpha AS a90a,
		avg180Alpha AS a180a,
		avg365Alpha AS a365a,
		batAvgDaily AS ba1,
		batAvgWeekly AS ba7,
		batAvgMonthly AS ba30,
		batAvg90 AS ba90,
		batAvg180 AS ba180,
		batAvg365 AS ba365
	FROM '.$_SESSION['fund_alphabeta_table'].' 
	WHERE fund_id=:fund_id	
';
try{
	$rsAlpha = $mLink->prepare($query);
	$aValues = array(
		':fund_id'	=> $fundID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsAlpha->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$alpha = $rsAlpha->fetch(PDO::FETCH_ASSOC);

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
                        <div class="caption"><i class="icon-arrow-up"></i><i class="icon-arrow-down"></i>Volatility Analysis</div>
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
                                            <th colspan="2" class="strat-table-header">BEST REUTRN</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
                                        <tr>
                                            <td><strong>3 Month Best Return</strong></td>
                                            <td class="a-right"><?php echo number_format($agg['br3m'], 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										if($agg['br6m'] != NULL){
										?>
                                        <tr>
                                            <td><strong>6 Month Best Return</strong></td>
                                            <td class="a-right"><?php echo number_format($agg['br6m'], 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										}
										
										if($agg['br9m'] != NULL){
										?>
                                        <tr>
                                            <td><strong>9 Month Best Return</strong></td>
                                            <td class="a-right"><?php echo number_format($agg['br9m'], 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										}
										
										if($agg['br12m'] != NULL){
										?>
                                        <tr>
                                            <td><strong>12 Month Best Return</strong></td>
                                            <td class="a-right"><?php echo number_format($agg['br12m'], 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										}
										
										if($agg['br24m'] != NULL){
										?>
                                        <tr>
                                            <td><strong>24 Month Best Return</strong></td>
                                            <td class="a-right"><?php echo number_format($agg['br24m'], 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										}
										?>
                                    
                                    </tbody>
                                </table>
                            </div><!--col-md-6-->
                            
                            <div class="col-md-6">
                            	<table class="table table-bordered table-striped table-condensed flip-content strat-style-table">
                                    <thead class="flip-content">
                                        <tr>
                                            <th colspan="2" class="strat-table-header">WORST REUTRN</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
                                        <tr>
                                            <td><strong>3 Month Worst Return</strong></td>
                                            <td class="a-right"><?php echo number_format($agg['wr3m'], 2, '.','');?>%</td>
                                        </tr>
                                         <?php
										if($agg['wr6m'] != NULL){
										?>
                                        <tr>
                                            <td><strong>6 Month Worst Return</strong></td>
                                            <td class="a-right"><?php echo number_format($agg['wr6m'], 2, '.','');?>%</td>
                                        </tr>
                                         <?php
										}
										
										if($agg['wr9m'] != NULL){
										?>
                                        <tr>
                                            <td><strong>9 Month Worst Return</strong></td>
                                            <td class="a-right"><?php echo number_format($agg['wr9m'], 2, '.','');?>%</td>
                                        </tr>
                                         <?php
										}
										
										if($agg['wr12m'] != NULL){
										?>
                                        <tr>
                                            <td><strong>12 Month Worst Return</strong></td>
                                            <td class="a-right"><?php echo number_format($agg['wr12m'], 2, '.','');?>%</td>
                                        </tr>
                                         <?php
										}
										
										if($agg['wr24m'] != NULL){
										?>
                                        <tr>
                                            <td><strong>24 Month Worst Return</strong></td>
                                            <td class="a-right"><?php echo number_format($agg['wr24m'], 2, '.','');?>%</td>
                                        </tr>
                                         <?php
										}
										?>
                                    </tbody>
                                </table>
                            </div><!--col-md-6-->
                        </div><!--row-->
                        
                        <div class="row">
                        	<div class="col-md-6">
                            	<table class="table table-bordered table-striped table-condensed flip-content strat-style-table">
                                    <thead class="flip-content">
                                        <tr>
                                            <th colspan="2" class="strat-table-header">OVERALL VOLATILITY</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
                                        <tr>
                                            <td><strong>3 Month Volatility</strong></td>
                                            <td class="a-right"><?php echo number_format($agg['v3m'], 2, '.','');?>%</td>
                                        </tr>
                                         <?php
										if($agg['v6m'] != NULL){
										?>
                                        <tr>
                                            <td><strong>6 Month Volatility</strong></td>
                                            <td class="a-right"><?php echo number_format($agg['v6m'], 2, '.','');?>%</td>
                                        </tr>
                                         <?php
										}
										
										if($agg['v9m'] != NULL){
										?>
                                        <tr>
                                            <td><strong>9 Month Volatility</strong></td>
                                            <td class="a-right"><?php echo number_format($agg['v9m'], 2, '.','');?>%</td>
                                        </tr>
                                         <?php
										}
										
										if($agg['v12m'] != NULL){
										?>
                                        <tr>
                                            <td><strong>12 Month Volatility</strong></td>
                                            <td class="a-right"><?php echo number_format($agg['v12m'], 2, '.','');?>%</td>
                                        </tr>
                                         <?php
										}
										
										if($agg['v24m'] != NULL){
										?>
                                        <tr>
                                            <td><strong>24 Month Volatility</strong></td>
                                            <td class="a-right"><?php echo number_format($agg['v24m'], 2, '.','');?>%</td>
                                        </tr>
                                         <?php
										}
										?>
                                    
                                    </tbody>
                                </table>
                            </div><!--col-md-6-->
                            
                            <div class="col-md-6">
                            	<table class="table table-bordered table-striped table-condensed flip-content strat-style-table">
                                    <thead class="flip-content">
                                        <tr>
                                            <th colspan="2" class="strat-table-header">AVERAGE RETURNS</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
                                        <tr>
                                            <td><strong>3 Month Average Return</strong></td>
                                            <td class="a-right"><?php echo number_format($agg['ar3m'], 2, '.','');?>%</td>
                                        </tr>
                                         <?php										
										if($agg['ar6m'] != NULL){
										?>
                                        <tr>
                                            <td><strong>6 Month Average Return</strong></td>
                                            <td class="a-right"><?php echo number_format($agg['ar6m'], 2, '.','');?>%</td>
                                        </tr>
                                         <?php
										}
										
										if($agg['ar9m'] != NULL){
										?>
                                        <tr>
                                            <td><strong>9 Month Average Return</strong></td>
                                            <td class="a-right"><?php echo number_format($agg['ar9m'], 2, '.','');?>%</td>
                                        </tr>
                                         <?php
										}
										
										if($agg['ar12m'] != NULL){
										?>
                                        <tr>
                                            <td><strong>12 Month Average Return</strong></td>
                                            <td class="a-right"><?php echo number_format($agg['ar12m'], 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										}
										
										if($agg['ar24m'] != NULL){
										?>
                                        <tr>
                                            <td><strong>24 Month Average Return</strong></td>
                                            <td class="a-right"><?php echo number_format($agg['ar24m'], 2, '.','');?>%</td>
                                        </tr>
                                         <?php
										}
										?>
                                    
                                    </tbody>
                                </table>
                            </div><!--col-md-6-->
                        </div><!--row-->
                   
                    </div><!--portlet-body-->
                </div><!--portlet-->
                <!--END VOLATILITY ANALYSIS-->
                
                <!--START VOLATILITY VS SP500-->
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption"><i class="icon-arrow-up"></i><i class="icon-arrow-down"></i>Volatility VS S&amp;P500</div>
                        <div class="tools">
                            <a href="" class="collapse"></a>
                            <a href="" class="reload"></a>
                        </div>
                    </div><!--portlet-title-->
                    <div class="portlet-body">
                    	
                        <div class="note note-info">
                        	<p>This section is similar to the above volatility measurements for your fund, except it removes the volatility of the overall stock market. As the time periods get larger and larger, the performance vs. the market will rise for a top investor.</p>
                        </div>
                        
                    	<div class="row">
                        	<div class="col-md-4">
                            	<table class="table table-bordered table-striped table-condensed flip-content strat-style-table">
                                    <thead class="flip-content">
                                        <tr>
                                            <th colspan="2" class="strat-table-header">LARGEST GAINS VS. S&ampP 500</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
                                        <tr>
                                            <td><strong>Best Over 1 Day</strong></td>
                                            <td class="a-right"><?php echo number_format(($alpha['b1a'] * 100), 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										if($alpha['b7a'] != NULL){
										?>
                                        <tr>
                                            <td><strong>Best Over 1 Week</strong></td>
                                            <td class="a-right"><?php echo number_format(($alpha['b7a'] * 100), 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										}
										if($alpha['b30a'] != NULL){
										?>
                                        <tr>
                                            <td><strong>Best Over 1 Month</strong></td>
                                            <td class="a-right"><?php echo number_format(($alpha['b30a'] * 100), 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										}
										if($alpha['b90a'] != NULL){
										?>
                                        <tr>
                                            <td><strong>Best Over 3 Months</strong></td>
                                            <td class="a-right"><?php echo number_format(($alpha['b90a'] * 100), 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										}
										if($alpha['b180a'] != NULL){
										?>
                                        <tr>
                                            <td><strong>Best Over 6 Months</strong></td>
                                            <td class="a-right"><?php echo number_format(($alpha['b180a'] * 100), 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										}
										if($alpha['b365a'] != NULL){
										?>
                                        <tr>
                                            <td><strong>Best Over 12 Months</strong></td>
                                            <td class="a-right"><?php echo number_format(($alpha['b365a'] * 100), 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										}
										?>
                                    
                                    </tbody>
                                </table>
                            </div><!--col-md-4-->
                            
                            <div class="col-md-4">
                            	<table class="table table-bordered table-striped table-condensed flip-content strat-style-table">
                                    <thead class="flip-content">
                                        <tr>
                                            <th colspan="2" class="strat-table-header">LARGEST LOSSES VS. S&amp;P 500</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
                                        <tr>
                                            <td><strong>Worst Over 1 Day</strong></td>
                                            <td class="a-right"><?php echo number_format(($alpha['w1a'] * 100), 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										if($alpha['w7a'] != NULL){
										?>
                                        <tr>
                                            <td><strong>Worst Over 1 Week</strong></td>
                                            <td class="a-right"><?php echo number_format(($alpha['w7a'] * 100), 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										}
										if($alpha['w30a'] != NULL){
										?>
                                        <tr>
                                            <td><strong>Worst Over 1 Month</strong></td>
                                            <td class="a-right"><?php echo number_format(($alpha['w30a'] * 100), 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										}
										if($alpha['w90a'] != NULL){
										?>
                                        <tr>
                                            <td><strong>Worst Over 3 Months</strong></td>
                                            <td class="a-right"><?php echo number_format(($alpha['w90a'] * 100), 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										}
										if($alpha['w180a'] != NULL){
										?>
                                        <tr>
                                            <td><strong>Worst Over 6 Months</strong></td>
                                            <td class="a-right"><?php echo number_format(($alpha['w180a'] * 100), 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										}
										if($alpha['w365a'] != NULL){
										?>
                                        <tr>
                                            <td><strong>Worst Over 12 Months</strong></td>
                                            <td class="a-right"><?php echo number_format(($alpha['w365a'] * 100), 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										}
										?>
                                    
                                    </tbody>
                                </table>
                            </div><!--col-md-4-->
                            
                            <div class="col-md-4">
                            	<table class="table table-bordered table-striped table-condensed flip-content strat-style-table">
                                    <thead class="flip-content">
                                        <tr>
                                            <th colspan="2" class="strat-table-header">AVERAGE RETURNS VS. S&amp;P 500</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
                                        <tr>
                                            <td><strong>Avg. Daily Gain</strong></td>
                                            <td class="a-right"><?php echo number_format(($alpha['a1a'] * 100), 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										if($alpha['a7a'] != NULL){
										?>
                                        <tr>
                                            <td><strong>Avg. Weekly Gain</strong></td>
                                            <td class="a-right"><?php echo number_format(($alpha['a7a'] * 100), 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										}
										if($alpha['a30a'] != NULL){
										?>
                                        <tr>
                                            <td><strong>Avg. Monthly Gain</strong></td>
                                            <td class="a-right"><?php echo number_format(($alpha['a30a'] * 100), 2, '.','');?>%</td>
                                        </tr>
                                         <?php
										}
										if($alpha['a90a'] != NULL){
										?>
                                        <tr>
                                            <td><strong>Avg Gain Over 3 Months</strong></td>
                                            <td class="a-right"><?php echo number_format(($alpha['a90a'] * 100), 2, '.','');?>%</td>
                                        </tr>
                                         <?php
										}
										if($alpha['a180a'] != NULL){
										?>
                                        <tr>
                                            <td><strong>Avg Gain Over 6 Months</strong></td>
                                            <td class="a-right"><?php echo number_format(($alpha['a180a'] * 100), 2, '.','');?>%</td>
                                        </tr>
                                         <?php
										}
										if($alpha['a365a'] != NULL){
										?>
                                        <tr>
                                            <td><strong>AvgGain Over 12 Months</strong></td>
                                            <td class="a-right"><?php echo number_format(($alpha['a365a'] * 100), 2, '.','');?>%</td>
                                        </tr>
                                         <?php
										}
										?>
                                    
                                    </tbody>
                                </table>
                            </div><!--col-md-4-->
                        </div><!--row-->
                        
                        <div class="row">
                        	<div class="col-md-6">
                            	<table class="table table-bordered table-striped table-condensed flip-content strat-style-table">
                                    <thead class="flip-content">
                                        <tr>
                                            <th colspan="2" class="strat-table-header">BATTING AVERAGES</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
                                        <tr>
                                            <td><strong>Daily Bat Average</strong></td>
                                            <td class="a-right"><?php echo number_format(($alpha['ba1'] * 100), 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										if($alpha['ba7'] != NULL){
										?>
                                        <tr>
                                            <td><strong>Bat Avg. for 1 Week</strong></td>
                                            <td class="a-right"><?php echo number_format(($alpha['ba7'] * 100), 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										}
										if($alpha['ba30'] != NULL){
										?>
                                        <tr>
                                            <td><strong>Bat Avg. for 1 Month</strong></td>
                                            <td class="a-right"><?php echo number_format(($alpha['ba30'] * 100), 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										}
										if($alpha['ba90'] != NULL){
										?>
                                        <tr>
                                            <td><strong>Bat Avg. for 3 Months</strong></td>
                                            <td class="a-right"><?php echo number_format(($alpha['ba90'] * 100), 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										}
										if($alpha['ba180'] != NULL){
										?>
                                        <tr>
                                            <td><strong>Bat Avg. for 6 Months</strong></td>
                                            <td class="a-right"><?php echo number_format(($alpha['ba180'] * 100), 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										}
										if($alpha['ba365'] != NULL){
										?>
                                        <tr>
                                            <td><strong>Bat Avg. for 12 Months</strong></td>
                                            <td class="a-right"><?php echo number_format(($alpha['ba365'] * 100), 2, '.','');?>%</td>
                                        </tr>
                                        <?php
										}
										?>
                                    
                                    </tbody>
                                </table>
                            </div><!--col-md-6-->
                            
                            <div class="col-md-6">
                            	
                            </div><!--col-md-6-->
                        </div><!--row-->
                   
                    </div><!--portlet-body-->
                </div><!--portlet-->
                <!--END VOLATILITY VS S&P500-->
                
                <!--START TRADE SCHOOL-->
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption"><i class="icon-book"></i>Trade School</div>
                        <div class="tools">
                        <a href="javascript:;" class="collapse"></a>
                        </div><!--tools-->
                    </div><!--portlet-title-->
                    
                    <div class="portlet-body">
                    	
                        <h3>How do I read this report?</h3>
                        <p>This report really tells a very simple story. It measures the best & worst performance your fund or portfolio experienced for any given time period. Depending on how old your fund is, we can measure this up to two years.</p>
                        <div class="divider"></div>
                        <h3>How does this make me a better investor?</h3>
                        
                        
                        <p>A sound investment strategy will still show volatility, but over time returns will average out. While there may have been spikes in individual stocks the fund volatility will flatten out as time goes on. Your good choices will out weigh the bad and over longer time periods, your fund will not only become less volatile, but also achieve higher average returns. If we compare a stock portfolio to a bond portfolio over the same period , we'll see a smaller volatility for the bonds, but if we factor in inflation a larger portion of the bonds volatility would be below break-even. As far as volatility is concerned it's the downward variety that is least appealing!</p>
                         
                         <p>Volatility may be large over short time periods, but over a longer investment horizon you should find your volatility levels out.</p>
                    </div><!--END PORTLET BODY-->
                </div><!--END PORTLET-->
                <!--END TRADE SCHOOL-->
                
            </div>
        </div>
        <!-- END PAGE CONTENT-->