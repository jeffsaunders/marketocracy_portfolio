<?php
/*
TRADE OPEN ORDERS - HTML FILE

SUPPORTING FILES:
_______________________________________________________________

AJAX 			- process/ajax/trade-open-orders-processes.php
PHP JAVASCRIPT	- includes/scripts/trade-open-orders.js.php
HTML 			- THIS FILE includes/pages/trade-open-orders.php
_______________________________________________________________

*/

$tradeMessage = $_REQUEST['tradeMessage'];

$fiveMinAgo			= (time()) - (60*5);
?>
        <!--START MODALS-->
        
        
        <!-- BEGIN DETAILS MODAL-->         
        <div class="modal fade" id="fund-details" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title">Ticket Details</h4>
                        <h5>Weatherford International Ltd (NYSE:WFT) | Status: <span class="label label-success">OPEN</span></h5>
                    </div>
                    <div class="modal-body">
                        <h5>Ticket Detail for: <strong>WFT</strong></h5>
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Opened</th>
                                    <th class="hidden-xs">Action</th>
                                    <th>Shares Ordered</th>
                                    <th>Limit</th>
                                    <th>Expires</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="highlight">11:40:34 AM on Jan 20, 2014</td>
                                    <td class="hidden-xs">Buy</td>
                                    <td>4,460</td>
                                    <td></td>
                                    <td>Day</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <h5>ECN Status</h5>
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Last Fill</th>
                                    <th>Shares Ordered</th>
                                    <th class="hidden-xs">Limit</th>
                                    <th>Shares Filled</th>
                                    <th class="hidden-xs">Average Price</th>
                                    <th>Expires</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td></td>
                                    <td>4,460</td>
                                    <td class="hidden-xs"></td>
                                    <td>0</td>
                                    <td class="hidden-xs">$0.00</td>
                                    <td>Day</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <h5>Funds Allocation</h5>
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th class="hidden-xs">Symbol</th>
                                    <th>Shares</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="highlight">jdoe's Mutual Fund</td>
                                    <td>JDM</td>
                                    <td>4,460</td>
                                </tr>
                            </tbody>
                        </table>
                    </div><!--modal-body-->
                    <div class="modal-footer">
                     <a href="#fund-cancel" data-toggle="modal" class="btn btn-danger" data-dismiss="modal">Cancel</a>
                     <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- END DETAILS MODAL-->
        
       <!-- BEGIN DETAILS MODAL-->         
        <div class="modal fade" id="order-processing" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title">Order Processing</h4>
                    </div>
                    <div class="modal-body order-processing">
                        
                        
                        
                    </div><!--modal-body-->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- END DETAILS MODAL-->
        
        <!-- BEGIN DETAILS MODAL-->         
        <div class="modal fade" id="fund-cancel" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content cancel-fund">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title">Confirm Cancel Order</h4>
                        <h5><?php echo $name;?> (<?php echo $symbol;?>) | Status: <span class="label label-success"><?php echo $order['status'];?></span></h5>
                    </div>
                    <div class="modal-body cancel-fund">
                        
                        <h4>Please Confirm Your Cancel Order</h4>
                        <p>Clicking the button below will send a cancel request to the exchange and take you back to the Open Orders page. The order will still appear in Open Orders for a few minutes until the exchange processes your request. After the exchange has canceled trading for that order, the order will move to the Recent Orders page. Any shares already traded will then be credited to your funds.</p>
                        
                    </div><!--modal-body-->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel the unfilled portion of my order</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- END DETAILS MODAL-->
        <!--END MODALS-->
        
        <!-- BEGIN PAGE CONTENT-->
        <div class="row">
            <div class="col-md-12">
               
               <?php if($tradeMessage == "1"){
			       ?>
                   <div class="alert alert-block alert-info fade in">
                        <button type="button" class="close" data-dismiss="alert"></button>
                        <h4><strong>Recent Trades.</strong></h4>
                        <p>Please note it may take a few minutes for your recently submitted trades to load. Please refresh the page. <?php echo $fiveMinAgo;?></p>
                        
                    </div>
                    <?php  
			   }
			   ?>
               
               <?php /*?><div class="alert alert-block alert-warning fade in">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <h4><strong>TRADE STATUS UPDATE ISSUE. - UPDATED</strong></h4>
                    <p>Members, some trades are showing up with no fills, when they have infact been filled. This is an issue with the ticket status update. Please allow us the time for the ticket to show as closed. We appreciate your patience.</p>
                    
                </div><?php */?>
               
                <!-- BEGIN RECENT ORDERS TABLE-->
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption"><i class="icon-globe"></i>All Open Orders</div>
                        <div class="actions">
                            <div class="btn-group">
                                <a class="btn btn-info dropdown-toggle" href="#" data-toggle="dropdown">
                                Columns
                                <i class="icon-angle-down"></i>
                                </a>
                                <div id="sample_2_column_toggler" class="dropdown-menu hold-on-click dropdown-checkboxes pull-right">
                                    <label><input type="checkbox" checked data-column="2">Open Date</label>
                                    <label><input type="checkbox" checked data-column="3">Limit</label>
                                    <label><input type="checkbox" checked data-column="4">Current</label>
                                    <label><input type="checkbox" checked data-column="6">Fills</label>
                                    <label><input type="checkbox" checked data-column="7">Avg. Paid</label>
                                    <label><input type="checkbox" checked data-column="8">Order Type</label>
                                    <label><input type="checkbox" checked data-column="9">Last Filled</label>
                                </div>
                            </div><!--btn-group-->
                        </div>
                        <div class="tools">
                        	<a href="" class="reload" title="Refresh Tickets" onclick="location.reload();"></a>
                        </div>
                    </div><!--portlet-title-->
                    <div class="portlet-body">
                    <table class="table table-striped table-bordered table-hover table-full-width" id="open_orders_table">
                        <thead>
                            <tr>
                            	<th>Fund</th>
                                <th>Type</th>
                                <th>Symbol</th>
                                <th>Open Date</th>
                               	<th>Limit</th>
                                <th>Current</th>
                                <th>Shares</th>
                                <th>Fills</th>
                                <?php /*<th class="hidden-xs">Avg. Paid</th><?php */?>
                                <th>Order Type</th>
                                <?php /*?><th class="hidden-xs">Last Filled</th><?php */?>
                                <th>Status</th>
                                <th>Actions</th>
                                <th style="display:none;">Unix Time</th>
                            </tr>
                        </thead>
                        <tbody>
                        	<?php
							
							$query = "
								SELECT *
								FROM members_fund_tickets
								WHERE member_id=:member_id AND status='pending'
								ORDER BY openned DESC
							";
							
							try{
								$rsGetOrders = $mLink->prepare($query);
								$aValues = array(
									':member_id' 	=> $_SESSION['member_id']
								);
								$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
								$rsGetOrders->execute($aValues);
							}
							catch(PDOException $error){
								// Log any error
								file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
							}
							
							while($order = $rsGetOrders->fetch(PDO::FETCH_ASSOC)){
								
								$tradeType 			= $order['action'];
								$symbol 			= $order['symbol'];
								$date 				= $order['openned'];
								$ticketKey			= $order['ticket_key'];
								$price 				= number_format($order['quote_price'], 2, '.', ',');
								$shares 			= $order['shares'];
								$orderType 			= $order['type'];
								$orderStatus		= $order['status'];
								$ticketFundID 		= $order['fund_id'];
								$ticketFundSymbol	= get_funds($mLink, $ticketFundID, 'fundSymbol');
								$cancelStatus = $order['cancel_status'];
								
								//get current stock price
								$query = "
									SELECT *
									FROM stock_feed
									WHERE symbol=:symbol
								";
								try{
									$rsGetPrice = $fLink->prepare($query);
									$aValues = array(
										':symbol' 	=> $symbol
									);
									$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
									$rsGetPrice->execute($aValues);
								}
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}
								$priceFeed = $rsGetPrice->fetch(PDO::FETCH_ASSOC);
								
								//echo $error; echo'here';
								//echo $priceFeed
								$price = number_format($priceFeed['Last'], 2, '.', ',');
//								echo $preparedQuery;
								//print_r($rsGetPrice);
								
								switch($tradeType){
									case "buy": $tradeType = "Buy";break;
									case "sell": $tradeType = "Sell";break;	
								}
								
								switch($orderType){
									case "Day": $orderType = 'Day Order';break;
									case "GTCI": $orderType = "Good Until Canceled";break;	
								}
								
								switch($orderStatus){
									case "pending": $orderStatus = '<span class="btn btn-sm btn-info">Pending</span>';break;
								}
								
								if(!empty($cancelStatus)){
									$orderStatus = '<span class="btn btn-sm btn-warning">Cancel Request Sent: <br /> '.date('M d, Y H:m', $cancelStatus).'</span>';
									$showCancelBtn = '';	
								}else{
									$showCancelBtn = '<a href="#fund-cancel" onclick="showCancellOrder(\''.$order['uid'].'\');" data-toggle="modal" class="btn btn-danger btn-sm">Cancel</a>';	
								}
								
								if($ticketKey == '' && $date<$fiveMinAgo){
									$orderStatus = '<span class="btn btn-sm btn-danger">Ticket Submission FAILED! <br /> Please cancel or resubmit ticket.</span>';
									$showCancelBtn = '<a href="#order-processing" onclick="resubmitOrder(\''.$order['uid'].'\');" data-toggle="modal" class="btn btn-info btn-sm">Resubmit</a> <a href="#order-processing" onclick="deleteOrder(\''.$order['uid'].'\');" data-toggle="modal" class="btn btn-danger btn-sm">Cancel</a>';
								}	
								
								if(isMarketOpen($date, $mLink) == false){
									
									if($order['type'] == 'Day'){
										$orderType = "Next Day Order";	
									}
										
								}
								
								?>
                                <tr>
                                	<td><?php echo $ticketFundSymbol;?></td>
                                    <td><?php echo $tradeType;?></td>
                                    <td><a href="#global-stock-info" class="btn btn-default btn-xs" data-toggle="modal" onclick="loadGlobalStockInfo('<?php echo $symbol;?>');"><?php echo $symbol;?></a></td>
                                    <td><?php echo date('M d, Y g:i A', $date);?> </td>
                                    <td>$<?php echo number_format($order['limit'],2,'.',',');?></td>
                                    <td>$<?php echo $price;?></td>
                                    <td><?php echo $shares;?></td>
                                    <td><?php echo $order['sharesFilled'];?></td>
                                    <?php /*?><td>$0.00</td><?php */?>
                                    <td><?php echo $orderType;?></td>
                                    <?php /*?><td></td><?php */?>
                                    <td><?php echo $orderStatus; ?></td>
                                    <td><?php /*?><a href="#fund-details" data-toggle="modal" class="btn btn-info">Details</a> <?php */?><?php echo $showCancelBtn;?></td>
                                    <td style="display:none;"><?php echo $date;?> <?php echo $preparedQuery;?></td>
                                </tr>
                                <?php
							}
							?>
                            
                           
                        </tbody>
                    </table>
                   	Note: Orders are processed 15 minutes after they are placed in order to close at real-time prices.
                    </div><!--portlet-body-->
                </div>
                <!-- END RECENT ORDER TABLE-->
            </div>
        </div>
        <!-- END PAGE CONTENT-->