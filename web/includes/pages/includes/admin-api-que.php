			<!-- BEGIN API QUEUE -->
            <div class="row">
                <div class="col-md-12 col-sm-6">
                
                    <!--START FUND INFORMATION-->
                    <div class="portlet">
                        <div class="portlet-title">
                            <div class="caption"><i class="icon-cogs"></i>API QUEUE: <div id="load-api-title" style="display:inline;"></div></div>
                            <div class="tools">
                            	<a href="" class="expand"></a>
                            </div>
                        </div><!--portlet-title-->
                        <div class="portlet-body" style="display:none;">
                        
                        	<div id="load-api-info"></div>
                        	
                        </div><!--portlet-body-->
                    </div><!--portlet-->
                    <!--END FUND INFORMATION-->
                    
                    <!--START FUND INFORMATION-->
                    <div class="portlet">
                        <div class="portlet-title">
                            <div class="caption"><i class="icon-cogs"></i>API Query Examples:</div>
                            <div class="tools">
                            	<a href="" class="expand"></a>
                            </div>
                        </div><!--portlet-title-->
                        <div class="portlet-body" style="display:none;">
                        
                        	<?php
							$query = "
								SELECT *
								FROM ".$_SESSION['api_methods_table']."
							    WHERE active='1'
							";
							try{
								$rsMethods = $mLink->prepare($query);
								$aValues = array();
								$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
								$rsMethods->execute($aValues);
							}
							catch(PDOException $error){
								// Log any error
								file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
							}
							
							$aMethods = array();
							
							while($method = $rsMethods->fetch(PDO::FETCH_ASSOC)){
								
								$aMethods[$method['method_group']][$method['method']] = array(
									'process'		=> $method['method_process'],
									'query_old'		=> $method['query_old'],
									'query_new'		=> $method['query_new'],
									'query_switch'	=> $method['query_switch'],
									'example'		=> $method['example'],
									'notes'			=> $method['notes']
								);	
									
							}
							
							
							?>
                            
                            
                        	<table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Method</th>
                                        <th>Process</th>
                                        <th>Query String</th>
                                        <th>Example</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    
									foreach($aMethods as $groupName=>$apiMethods){
										
										echo '
										<tr>
											<td colspan="5" style="background:#39B3D7 !important;color:#ffffff;">'.$groupName.'</td>
										</tr>
										';
										
										foreach($apiMethods as $method=>$aMethodInfo){
											
											$queryOld = $aMethodInfo['query_old'];
											$queryNew = $aMethodInfo['query_new'];
											$querySwitch	= $aMethodInfo['query_switch'];
											
											switch($querySwitch){
												case 'old': $apiQuery = $queryOld;break;
												case 'new': $apiQuery = $queryNew;break;	
											}
											
											switch($method){
												
												case 'newManager':
													
													$example = "
													  	
													";
													
												break;
													
											}
											
											?>
                                            <tr>
                                            	<td><?php echo $method;?></td>
                                                <td><?php echo $aMethodInfo['process'];?></td>
                                                <td><?php echo $apiQuery;?></td>
												<td width="40%">
<?php 
switch($method){

case 'newManager':
?>
<pre><code>$aMethodVars[] = array(
	'method'	=> 'newManager', #method name
	'source'    => 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund', #File and Code location of this instance
	'api'       => '1', #api switch, 1 = api1, 2 = api2, 3 = api3
	'username'  => 'bmccarthy', #username of member
	'email'     => 'brandon.mccarthy@marketocracy.com', #email of member
	'member_id' => '2', #member id member
	'group'     => 'N/A', #use this field if there are multiple queries being placed at the same time (IE Fund Price)
);
$mlaResults = legacy_api($mLink, $aMethodVars, true);
</code></pre>
<?php
break;

case 'newFund':
?>
<pre><code>$aMethodVars[] = array(
	'method'		=> 'newFund', #method name 
	'source'		=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund', #File and Code location of this instance 
	'api'			=> '1', #api switch, 1 = api1, 2 = api2, 3 = api3 
    'fund_type'		=> 'long',
	'username'		=> 'bmccarthy', #username of member
    'fund_name'		=> 'BMF fund', 
	'fund_symbol'	=> 'BMF', #fund symbol 
	'fund_id' 		=> '2-8', #members fund ID 'group'	=> 'N/A' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
); 
$mlaResults = legacy_api($mLink, $aMethodVars, true);
</code></pre>
<?php
break;

case 'deactivateFund':
?>
<pre><code>$aMethodVars[] = array(
    'method'		=> 'deactivateFund', #method name 
    'source'		=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund', #File and Code location of this instance 
    'api'			=> '1', #api switch, 1 = api1, 2 = api2, 3 = api3 
    'username'		=> 'bmccarthy', #username of member 
    'fund_symbol'	=> 'BMF', #fund symbol 
    'fund_id' 		=> '2-8', #members fund ID 
    'group'			=> 'N/A' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
); 
$mlaResults = legacy_api($mLink, $aMethodVars, true);
</code></pre>
<?php
break;

case 'updateSymbol':
?>
<pre><code>$aMethodVars[] = array(
    'method'			=> 'updateSymbol', #method name	
    'source'			=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund', #File and Code location of this instance	
    'api'				=> '1', #api switch, 1 = api1, 2 = api2, 3 = api3
    'username'			=> 'bmccarthy', #username of member 
    'fund_symbol'		=> 'BMF', #old fund symbol 
    'fund_id' 			=> '2-8', #members fund ID 
    'new_fund_symbol'	=> 'BMF2', #new fund symbol	
    'group'				=> 'N/A' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
);
$mlaResults = legacy_api($mLink, $aMethodVars, true);
</code></pre>
<?php
break;

case 'updateName':
?>
<pre><code>$aMethodVars[] = array(
    'method'		=> 'updateName', #method name
    'source'		=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund', #File and Code location of this instance
    'api'			=> '1', #api switch, 1 = api1, 2 = api2, 3 = api3
    'fund_id'		=> '2-1',
    'username' 		=> 'bmccarthy', #username of member 'fund_id' => '2-8', #members fund ID
    'fund_symbol'	=> 'BMF', #old fund symbol
    'new_fund_name'	=> 'My Tech Fund', #new fund name
    'group'			=> 'N/A' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
);
$mlaResults = legacy_api($mLink, $aMethodVars, true);
</code></pre>
<?php
break;

case 'managerPassword':
?>
<pre><code>$aMethodVars[] = array(
    'method'	=> 'managerPassword', #method name
    'source'	=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund', #File and Code location of this instance
    'api'		=> '1', #api switch, 1 = api1, 2 = api2, 3 = api3
    'username'	=> 'bmccarthy', #username of member
    'group'		=> 'N/A' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
);
$mlaResults = legacy_api($mLink, $aMethodVars, true);
</code></pre>
<?php
break;

case 'importPassword':
?>
<pre><code>$aMethodVars[] = array(
    'method'	=> 'importPassword', #method name
    'source'	=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund', #File and Code location of this instance
    'api'		=> '1', #api switch, 1 = api1, 2 = api2, 3 = api3
    'username' 	=> 'bmccarthy', #username of member
    'password'	=> 'asdfasdf',
    'email' 	=> 'brandon.mccarthy@marketocracy.com',
    'member_id' => '2',
    'group'		=> 'N/A' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
);
$mlaResults = legacy_api($mLink, $aMethodVars, true);
</code></pre>
<?php
break;

case 'maxDate':
?>
<pre><code>$aMethodVars[] = array(
    'method'	=> 'maxDate', #method name
    'source'	=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund', #File and Code location of this instance
    'api'		=> '1', #api switch, 1 = api1, 2 = api2, 3 = api3
    'group'		=> 'N/A' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
);
$mlaResults = legacy_api($mLink, $aMethodVars, true);
</code></pre>
<?php
break;

case 'priceManager':
?>
<pre><code>$aMethodVars[] = array(
    'method'	=> 'priceManager',
    'source'	=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund',
    'api'		=> '1',
    'username'	=> 'bmccarthy',
    'group'		=> 'N/A' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
);
$mlaResults = legacy_api($mLink, $aMethodVars, true);
</code></pre>
<?php
break;

case 'livePrice':
?>
<pre><code>$aMethodVars[] = array(
    'method'	=> 'livePrice',
    'source'	=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund',
    'api'	=> '1',
    'username'	=> 'bmccarthy',
    'fund_id' => '2-1',
    'fund_symbol' => 'BMF',
    'group'	=> 'N/A' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
);
$mlaResults = legacy_api($mLink, $aMethodVars, true);
</code></pre>
<?php
break;

case 'priceRun':
?>
<pre><code>$aMethodVars[] = array(
    'method'	=> 'priceRun',
    'source'	=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund',
    'api'	=> '1',
    'username'	=> 'bmccarthy',
    'fund_id' => '2-1',
    'fund_symbol' => 'BMF',
    'start_date' => '20150913', #start date of period to grab records
    'end_date' => '20150923', #end date of period to grab records ( do no exceed 15 days)
    'group'	=> 'N/A' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
);
$mlaResults = legacy_api($mLink, $aMethodVars, true);
</code></pre>
<?php
break;

case 'aggregateStatistics':
?>
<pre><code>$aMethodVars[] = array(
    'method'	=> 'aggregateStatistics',
    'source'	=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund',
    'api'	=> '1',
    'username'	=> 'bmccarthy',
    'fund_id' => '2-1',
    'fund_symbol' => 'BMF',
    'from_date' => '20150913', #date to pull record for
    'group'	=> 'N/A' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
);
$mlaResults = legacy_api($mLink, $aMethodVars, true);
</code></pre>
<?php
break;

case 'alphaBetaStatistics':
?>
<pre><code>$aMethodVars[] = array(
    'method'	=> 'alphaBetaStatistics',
    'source'	=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund',
    'api'		=> '1',
    'username'	=> 'bmccarthy',
    'fund_id' => '2-1',
    'fund_symbol' => 'BMF',
    'from_date' => '20150913', #date to pull record for
    'group'	=> 'N/A' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
);
$mlaResults = legacy_api($mLink, $aMethodVars, true);
</code></pre>
<?php
break;

case 'positionInfo':
?>
<pre><code>$aMethodVars[] = array(
    'method'	=> 'positionInfo',
    'source'	=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund',
    'api'	=> '1',
    'username'	=> 'bmccarthy',
    'fund_id' => '2-1',
    'fund_symbol' => 'BMF',
    'stock_symbol' => 'AAPL', #symbol to pull position info for
    'group'	=> 'N/A' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
);
$mlaResults = legacy_api($mLink, $aMethodVars, true);
</code></pre>
<?php
break;

case 'allPositionInfo':
?>
<pre><code>$aMethodVars[] = array(
    'method'		=> 'allPositionInfo',
    'source'		=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund',
    'api'			=> '1',
    'username'		=> 'bmccarthy',
    'fund_id'		=> '2-1',
    'fund_symbol'	=> 'BMF',
    'active_only'	=> '1', #grabs info for only active stocks, 0 grabs info for all stocks
    'group'			=> 'N/A' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
);
$mlaResults = legacy_api($mLink, $aMethodVars, true);
</code></pre>
<?php
break;

case 'stockActions':
?>
<pre><code>$aMethodVars[] = array(
    'method'		=> 'stockActions',
    'source'		=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund',
    'api'			=> '1',
    'stock_symbol'	=> 'AAPL',
    'group'			=> 'N/A' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
);
$mlaResults = legacy_api($mLink, $aMethodVars, true);
</code></pre>
<?php
break;

case 'stockInfo':
?>
<pre><code>$aMethodVars[] = array(
    'method'	=> 'stockInfo',
    'source'	=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund',
    'api'		=> '1', 'stock_symbol' => 'AAPL',
    'group'		=> 'N/A' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
);
$mlaResults = legacy_api($mLink, $aMethodVars, true);
</code></pre>
<?php
break;

case 'tradesForPosition':
?>
<pre><code>$aMethodVars[] = array(
    'method'		=> 'tradesForPosition',
    'source'		=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund',
    'api'			=> '1',
    'username'		=> 'bmccarthy',
    'fund_id'		=> '2-1',
    'fund_symbol'	=> 'BMF',
    'stock_symbol'	=> 'AAPL',
    'from_date'		=> '20150923', #gets all trades from the date past, forward
    'group'			=> 'N/A' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
);
$mlaResults = legacy_api($mLink, $aMethodVars, true);
</code></pre>
<?php
break;

case 'tradesForFund':
?>
<pre><code>$aMethodVars[] = array(
    'method'		=> 'tradesForPosition',
    'source'		=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund',
    'api'			=> '1',
    'username'		=> 'bmccarthy',
    'fund_id'		=> '2-1',
    'fund_symbol'	=> 'BMF',
    'from_date'		=> '20150923', #gets all trades from the date past, forward
    'group'			=> 'N/A' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
);
$mlaResults = legacy_api($mLink, $aMethodVars, true);
</code></pre>
<?php
break;

case 'positionDetail':
?>
<pre><code>$aMethodVars[] = array(
    'method'		=> 'positionDetail',
    'source'		=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund',
    'api'			=> '1',
    'username'		=> 'bmccarthy',
    'fund_id'		=> '2-1',
    'fund_symbol'	=> 'BMF',
    'from_date'		=> '20150923',
    'group'			=> 'N/A' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
);
$mlaResults = legacy_api($mLink, $aMethodVars, true);
</code></pre>
<?php
break;

case 'untrade':
?>
<pre><code>$aMethodVars[] = array(
    'method'		=> 'untrade',
    'source'		=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund',
    'api'			=> '1',
    'ticket_key'	=> 'X'DLKFJDLKFJLKJLDKFJD'',
    'group'			=> 'N/A' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
);
$mlaResults = legacy_api($mLink, $aMethodVars, true);
</code></pre>
<?php
break;


}
?>
</td>
                                                <td><?php echo $aMethodInfo['notes'];?></td>
                                            </tr>
                                            <?php
												
										}
											
									}
									
                                   
                                    ?>
                                </tbody>
                            </table>
                        </div><!--portlet-body-->
                    </div><!--portlet-->
                    <!--END FUND INFORMATION-->
                	
                </div><!--col-md-6-->
			</div><!--row-->
            <!-- BEGIN API QUEUE -->
