<?php
/*
Marketocracy Inc. | Beta Development
Fund Trades

Supporting files:
	AJAX		- process/ajax/fund-trades-processes.php
	JAVASCRIPT	- includes/scripts/fund-trades.js.php
	HTML		- THIS DOCUMENT includes/pages/fund-trades.php
*/

$aDebugLog = array();

//Get Date Range from URL
if(!isset($startDay)){
	$startDate = $_REQUEST['startDate'];
}
if(!isset($endDate)){
	$endDate = $_REQUEST['endDate'];
}

//START | Get Inception Date /OR earliest Record
/*$query = "
	SELECT date
	FROM ".$_SESSION['stratification_basic_table']."
	WHERE fund_id=:fund_id 
";

try{
	$rsInception = $mLink->prepare($query);
	$aValues = array(
		':fund_id'	=> $fundID,
		':date'		=> $date
		
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsInception->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$aDebugLog['strat_info']['prepared_query'] = $preparedQuery;
$aDebugLog['strat_info']['query_error'][] = $error;

$inception = $rsInception->fetch(PDO::FETCH_ASSOC);*/

$query = "
	SELECT *
	FROM ".$_SESSION['fund_trades_table']."
	WHERE fund_id=:fund_id
	ORDER BY unix_closed DESC
	
";

try{
	$rsTrades = $mLink->prepare($query);
	$aValues = array(
		':fund_id'		=> $fundID
		
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsTrades->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}


$aDebugLog['trade_info']['prepared_query'] = $preparedQuery;
$aDebugLog['trade_info']['query_error'][] = $error;

$cnt = 0;
$aTrades = array();
$aStocks = array();

while($trades = $rsTrades->fetch(PDO::FETCH_ASSOC)){
	
	$cnt++;
	
	/*$query = "
			SELECT Name AS companyName
			FROM `stock_feed`
			WHERE `Symbol`=:stockSymbol
	";
	try{
			$rsSymbols = $fLink->prepare($query);
			$aValues = array(':stockSymbol'=>$trades['stockSymbol']);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsSymbols->execute($aValues);
	}
	catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$foo = $rsSymbols->fetch(PDO::FETCH_ASSOC);*/
	
	$companyName = $foo['companyName'];
	
	$aStocks[$trades['stockSymbol']] = $trades['stockSymbol'];
	
	$aTrades[] = array(
		'stockSymbol'		=> $trades['stockSymbol'],
		'opened'			=> $trades['unix_opened'],
		'closed'			=> $trades['unix_closed'],
		'sharesOrdered'		=> $trades['sharesOrdered'],
		'sharesFilled'		=> $trades['sharesFilled'],
		'price'				=> $trades['price'],
		'limit'				=> $trades['limit'],
		'dayOrGTC'			=> $trades['dayOrGTC'],
		'ticketKey'			=> $trades['ticketKey'],
		'createdByCA'		=> $trades['createdbyCA'],
		'net'				=> $trades['net'],
		'secFee'			=> $trades['secFee'],
		'commission'		=> $trades['commission'],
		'buyOrSell'			=> $trades['buyOrSell'],
		'companyName'		=> $companyName,
		'comment'			=> $trades['comment']
	);
}

//Get company names
$query = "
	SELECT Name AS companyName, Symbol
	FROM `stock_feed`
	WHERE `Symbol` IN (\"".implode('","',$aStocks)."\")
";
try{
	$rsSymbols = $fLink->prepare($query);
	$aValues = array(':stockSymbol'=>$trades['stockSymbol']);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsSymbols->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}
$aStocks = array();

while($foo = $rsSymbols->fetch(PDO::FETCH_ASSOC)){
	$aStocks[$foo['Symbol']] = $foo['companyName'];	
}

$aDebugLog['stock_info']['prepared_query'] = $preparedQuery;
$aDebugLog['stock_info']['query_error'][] = $error;

$_SESSION['trades-history'] = $aTrades;
?>
         <!-- BEGIN TRADE DETAILS MODAL-->         
        <div class="modal fade" id="trade-details" tabindex="-1" role="basic" aria-hidden="true">
            <div class="modal-dialog modal-wide">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title">Trade Info</h4>
                    </div>
                    <div class="modal-body">
                    	
                        <div class="load-ledger-info"></div>
                    	
                        
                        <table id="trades-table" class="table table-bordered table-striped table-condensed flip-content load-pos-trades">
                            <thead class="flip-content">
                                <tr>
                                	<th class="fzt-organge" colspan="8">POSITIONS ON JANUARAY 06, 2014</th>
                                </tr>
                                <tr class="fzt-blue">
                                    <th class="fzt-aleft">Type</th>
                                    <th class="hidden-xs">Close Date</th>
                                    <th>Symbol</th>
                                    <th>Name</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Net</th>
                                    <th>Commission</th>
                                    <th>SEC Fee</th>
                                </tr>
                            </thead>
                            <tbody>
                            	
                              
                                
                            </tbody>
                        </table>
                            
                        
                    </div>
                    <div class="modal-footer">
                     <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div><!-- .modal-content -->
            </div><!-- .modal-dialog -->
        </div> <!-- .modal -->
        <!--END TRADE DETAIL MODAL-->
        
        <!-- BEGIN STOCK DETAILS PORTLET-->         
        <div class="modal fade in" id="ticket-details" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content" style="background: #FAFAFA;">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title"><button title="Go Back To Previous Screen" type="button" class="btn btn-warning btn-sm" data-dismiss="modal"><i class="icon-arrow-left"></i></button> Ticket Details</h4>
                    </div><!--modal-header-->
                    <div class="modal-body">
                        
                        <div id="ticket-detail-load"></div>
                        
                    </div><!--modal-body-->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div><!--modal-footer-->	
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- STOCK DETAILS PORTLET-->
        
        <!-- BEGIN PAGE CONTENT-->
        <div class="row">
            <div class="col-md-12">
            	
               <?php /*?> <div class="note note-warning">
                	<h4 class="block">PAGE AVAILABLE SOON - UNDER CONSTRUCTION</h4>
                	<p>The information reflected on this page is not accurate. Please check back later.</p>
                </div><!--note--><?php */?>
                
                <!-- BEGIN FUND INFO -->
                <?php include('includes/portlets/fund-live-info.php');?>
                <!-- END FUND INFO -->
                
                <!-- BEGIN Trade History TABLE-->
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption">
                        	<i class="icon-random"></i>Trades
                        </div>
                        
                        <div class="actions">
                            
                            
                            <a href="process/ajax/fund-trades-processes.php?process=csv&fund=<?php echo $fundID;?>" class="btn btn-success"><i class="icon-download"></i> Download CSV</a>
                        </div><!--actions-->
                        
					</div><!--portlet-title-->
                    <div class="portlet-body">
                    	
                        <table class="table table-striped table-bordered table-hover table-full-width" id="trades-history-table">
                            <thead>
                                <tr>
                                	<th>TYPE</th>
                                	<th>CLOSE DATE</th>
                                    <th>SYMBOL</th>
                                    <th>NAME</th>
                                    <th>QUANTITY</th>
                                    <th>PRICE</th>
                                    <th>NET</th>
                                    <th>COMMISSION</th>
                                    <th>SEC FEE</th>
                                </tr>
                            </thead>
                            <tbody>
                            	<?php
								
								foreach($aTrades as $key=>$aTrade){
									
									$ticketKey	= $aTrade['ticketKey'];
									$hasComment	= false;
									
									
									if($aTrade['comment'] == ''){
										
										$query = "
											SELECT reasons, description
											FROM ".$_SESSION['fund_tickets_table']."
											WHERE ticket_key=:ticketKey											
										";
										try{
											$rsComments = $mLink->prepare($query);
											$aValues = array(
												':ticketKey' => $ticketKey
											);
											$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
											$rsComments->execute($aValues);
										}
										catch(PDOException $error){
												// Log any error
												file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
										}
										$comment = $rsComments->fetch(PDO::FETCH_ASSOC);
										
										$reasons		= $comment['reasons'];
										$description	= $comment['description'];
										
										if($reasons != ''){
											$hasComment = true;	
										}
										if($description != ''){
											$hasComment = true;	
										}
											
									}else{
										$hasComment = true;	
										
										$aComment = explode('|',$aTrade['comment']);
										
										$aComment = array_reverse($aComment);
										
										$description = $aComment[0];
										
										unset($aComment[0]);
										
										$reasons = implode('|',$aComment);
									}
									
									$companyName = $aStocks[$aTrade['stockSymbol']];
									
									$aTrades[$key] = array(
										'stockSymbol'		=> $aTrade['stockSymbol'],
										'opened'			=> $aTrade['opened'],
										'closed'			=> $aTrade['closed'],
										'sharesOrdered'		=> $aTrade['sharesOrdered'],
										'sharesFilled'		=> $aTrade['sharesFilled'],
										'price'				=> $aTrade['price'],
										'limit'				=> $aTrade['limit'],
										'dayOrGTC'			=> $aTrade['dayOrGTC'],
										'ticketKey'			=> $aTrade['ticketKey'],
										'createdByCA'		=> $aTrade['createdbyCA'],
										'net'				=> $aTrade['net'],
										'secFee'			=> $aTrade['secFee'],
										'commission'		=> $aTrade['commission'],
										'buyOrSell'			=> $aTrade['buyOrSell'],
										'companyName'		=> $companyName,
										'reasons'			=> $aTrade['comment'],
										'reasons2'			=> $reasons,
										'description'		=> $description,
										
									);
									
									?>
                                    <tr>
                                    	<td>
                                        	<a href="#global-ticket-details" data-toggle="modal" style="text-decoration:none;" onclick="loadGlobalTradeDetails('<?php echo $aTrade['ticketKey'];?>');">
												<?php echo ucfirst($aTrade['buyOrSell']);?>&nbsp;
                                                <?php if($hasComment == true){ echo '<span class="label label-warning" title="Has comment"><i class="icon-comments"></i></span>'; }?>
                                            </a>
                                        </td>
                                        <td><?php echo date('M d, Y',$aTrade['closed']);?></td>
                                        <td><?php echo $aTrade['stockSymbol'];?></td>
                                        <td><?php echo $companyName;?></td>
                                        <td><?php echo number_format($aTrade['sharesFilled'],0,'.',',');?></td>
                                        <td>$<?php echo number_format($aTrade['price'],2,'.',',');?></td>
                                        <td>$<?php echo number_format($aTrade['net'],2,'.',',');?></td>
                                        <td>$<?php echo number_format($aTrade['commission'],2,'.',',');?></td>
                                        <td>$<?php echo number_format($aTrade['secFee'],2,'.',',');?></td>
                                    </tr>
                                    <?php										
								}	
								
								$_SESSION['csv_trades'] = $aTrades;
								$_SESSION['csv_fund']	= $fundID;							
								?>                              
                            </tbody>
                        </table>
						
                        
                        <?php if($_REQUEST['debug'] == '1'){?>
                        	<pre>
                            	<?php
								print_r($aDebugLog);
								?>
                            </pre>
                        <?php } ?>
                      
                    </div><!--portlet-boody-->
                </div><!--end portlet-->
               	<!-- END Trade History TABLE-->
                
                
                
                <!-- BEGIN Currnet Holdings Trade TABLE-->
                <div class="portlet" id="ledger-entries">
                    <div class="portlet-title">
                        <div class="caption">
                        	<i class="icon-globe"></i>Current Holdings:</div>
                        <?php /*?><div class="actions">
                            <div class="btn-group" id="column-view">
                                <a class="btn btn-info dropdown-toggle" href="#" data-toggle="dropdown">
                                Columns
                                <i class="icon-angle-down"></i>
                                </a>
                                <div id="ledger-table_column_toggler" class="dropdown-menu hold-on-click dropdown-checkboxes pull-right">
                                    <label><input type="checkbox" checked data-column="2">Start Cash</label>
                                    <label><input type="checkbox" checked data-column="3">In/Out Flow</label>
                                    <label><input type="checkbox" checked data-column="4">Interest</label>
                                    <label><input type="checkbox" checked data-column="5">Dividends</label>
                                    <label><input type="checkbox" checked data-column="6">Management Fees</label>
                                    <label><input type="checkbox" checked data-column="7">Trade Balance</label>
                                    <label><input type="checkbox" checked data-column="8">End Cash</label>
                                    <label><input type="checkbox" checked data-column="9">Stock Value</label>
                                    <label><input type="checkbox" checked data-column="10">Total Value</label>
                                    <label><input type="checkbox" checked data-column="11">Shares</label>
                                    <label><input type="checkbox" checked data-column="12">Price</label>
                                    <label><input type="checkbox" checked data-column="13">Compliance</label>
                                </div><!--ledger-table_colum_toggler-->
                       		</div><!--button-group-->
                        </div><!--actions--><?php */?>
					</div><!--portlet-title-->
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover table-full-width" id="current-trades-table">
                            <thead>
                                <tr>
                                	<th style="display:none;"></th>
                                	<th style="width:20px;"></th>
                                    <th>STOCK</th>
                                    <th>SHARES</th>
                                    <th>TOTAL VALUE</th>
                                    <th width="5%">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                            	<?php
								$query = "
									SELECT *
									FROM ".$_SESSION['fund_stratification_basic_table']."
									WHERE fund_id=:fund_id AND totalShares>'0'
									ORDER BY currentValue DESC
								";
								
								try{
									$rsStocks = $mLink->prepare($query);
									$aValues = array(
										':fund_id'		=> $fundID
										
									);
									$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
									$rsStocks->execute($aValues);
								}
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}
								
								$cnt = 0;
								while($stocks = $rsStocks->fetch(PDO::FETCH_ASSOC)){
									
									$cnt = $cnt + 1;
									
									$symbol = $stocks['stockSymbol'];
									?>
									<tr>
                                    	<td style="display:none;"><?php echo $stocks['currentValue'];?></td>
                                        <td><?php echo $cnt;?></td>
										<td><span class="label label-info"><?php echo $symbol;?></span></td>
										<td><?php echo $stocks['totalShares'];?></td>
                                        <td>$<?php echo number_format($stocks['currentValue'], 2, '.', ',');?></td>
										<td><a href="#trade-details" class="btn btn-info btn-xs" onClick="loadPosTrades('<?php echo $fundID;?>','<?php echo $symbol;?>');" data-toggle="modal">View Trades</a></td>
									</tr>
									<?php
								}
								
								
								?>
								
                               
                            </tbody>
                        </table>
						
                      
                    </div><!--portlet-boody-->
                </div><!--end portlet-->
               <!-- END Current Holdings Trades TABLE-->
               
               <!-- BEGIN Past Holdings Trade TABLE-->
                <div class="portlet" id="ledger-entries">
                    <div class="portlet-title">
                        <div class="caption">
                        	<i class="icon-globe"></i>Past Holdings:</div>
                        <?php /*?><div class="actions">
                            <div class="btn-group" id="column-view">
                                <a class="btn btn-info dropdown-toggle" href="#" data-toggle="dropdown">
                                Columns
                                <i class="icon-angle-down"></i>
                                </a>
                                <div id="ledger-table_column_toggler" class="dropdown-menu hold-on-click dropdown-checkboxes pull-right">
                                    <label><input type="checkbox" checked data-column="2">Start Cash</label>
                                    <label><input type="checkbox" checked data-column="3">In/Out Flow</label>
                                    <label><input type="checkbox" checked data-column="4">Interest</label>
                                    <label><input type="checkbox" checked data-column="5">Dividends</label>
                                    <label><input type="checkbox" checked data-column="6">Management Fees</label>
                                    <label><input type="checkbox" checked data-column="7">Trade Balance</label>
                                    <label><input type="checkbox" checked data-column="8">End Cash</label>
                                    <label><input type="checkbox" checked data-column="9">Stock Value</label>
                                    <label><input type="checkbox" checked data-column="10">Total Value</label>
                                    <label><input type="checkbox" checked data-column="11">Shares</label>
                                    <label><input type="checkbox" checked data-column="12">Price</label>
                                    <label><input type="checkbox" checked data-column="13">Compliance</label>
                                </div><!--ledger-table_colum_toggler-->
                       		</div><!--button-group-->
                        </div><!--actions--><?php */?>
					</div><!--portlet-title-->
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover table-full-width" id="past-trades-table">
                            <thead>
                                <tr>
                                	<th style="display:none;"></th>
                                	<th style="width:20px;"></th>
                                    <th>STOCK</th>
                                    <th>SHARES</th>
                                    <th>TOTAL VALUE</th>
                                    <th width="5%">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                            	<?php
								$query = "
									SELECT *
									FROM ".$_SESSION['fund_stratification_basic_table']."
									WHERE fund_id=:fund_id AND totalShares='0'
									ORDER BY currentValue DESC
								";
								
								try{
									$rsStocks = $mLink->prepare($query);
									$aValues = array(
										':fund_id'		=> $fundID
										
									);
									$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
									$rsStocks->execute($aValues);
								}
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}
								
								$cnt = 0;
								while($stocks = $rsStocks->fetch(PDO::FETCH_ASSOC)){
									
									$cnt = $cnt + 1;
									$symbol = $stocks['stockSymbol'];
									?>
									<tr>
                                    	<td style="display:none;"><?php echo $stocks['currentValue'];?></td>
                                        <td><?php echo $cnt;?></td>
										<td><span class="label label-info"><?php echo $symbol;?></span></td>
										<td><?php echo $stocks['totalShares'];?></td>
                                        <td>$<?php echo number_format($stocks['currentValue'], 2, '.', ',');?></td>
										<td><a href="#trade-details" class="btn btn-info btn-xs" onClick="loadPosTrades('<?php echo $fundID;?>','<?php echo $symbol;?>');" data-toggle="modal">View Trades</a></td>
									</tr>
									<?php
								}
								
								
								?>
								
                               
                            </tbody>
                        </table>
						
                      
                    </div><!--portlet-boody-->
                </div><!--end portlet-->
               <!-- END Past Holdings Trades TABLE-->
               
            </div><!--col-->
        </div><!--row-->
        