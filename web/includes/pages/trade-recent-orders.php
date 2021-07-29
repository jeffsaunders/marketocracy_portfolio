<?php
/*
TRADE RECENT ORDERS - HTML FILE

SUPPORTING FILES:
_______________________________________________________________

AJAX 			- process/ajax/trade-recent-orders-processes.php
PHP JAVASCRIPT	- includes/scripts/trade-recent-orders.js.php
HTML 			- THIS FILE includes/pages/trade-recent-orders.php
_______________________________________________________________

*/
?>
            <!--START MODALS-->
            <!-- BEGIN DETAILS MODAL-->         
            <div class="modal fade" id="fund-details" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                            <h4 class="modal-title">Ticket Details</h4>
                            <h5>Concurrent Computer (NASDAQ:CCUR) | Status: <span class="label label-warning">CLOSED</span></h5>
                        </div>
                        <div class="modal-body">
                            <h5><strong>Ticket Detail for: CCUR</strong></h5>
                            <div class="scrollable">
                            <table class="table table-bordered table-hover ">
                                <thead>
                                    <tr>
                                        <th>Opened</th>
                                        <th class="hidden-xs">Action</th>
                                        <th>Shares Ordered</th>
                                        <th class="hidden-xs">Limit</th>
                                        <th>Shares Filled</th>
                                        <th>Expires</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="highlight">13:12:40 on Jan 06, 2014</td>
                                        <td class="hidden-xs">Buy</td>
                                        <td>6,000</td>
                                        <td class="hidden-xs"></td>
                                        <td>4,551</td>
                                        <td>Day</td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>
                            <h5><strong>Completion</strong></h5>
                            <p>This ticket was opened at 13:12:40 on Jan 06, 2014 and completed at 15:59:00 on Jan 06, 2014</p>
                            
                            <h5><strong>Fees</strong></h5>
                            <table class="table table-bordered table-hover">
                                <tbody>
                                    <tr>
                                        <td>Shares</td>
                                        <td>4,551</td>
                                    </tr>
                                    <tr>
                                        <td>SEC Fee</td>
                                        <td>$0.00</td>
                                    </tr>
                                    <tr>
                                        <td>Commission</td>
                                        <td>$227.55</td>
                                    </tr>
                                    <tr>
                                        <td>Net</td>
                                        <td>$37,739.63</td>
                                    </tr>
                                    <tr>
                                        <td>Net Avg. Price</td>
                                        <td>$8.2926</td>
                                    </tr>
                                </tbody>
                            </table>
                            <p><small>Note: Per standard industry practice, price above is the Net Average Price, which includes the stated fees and commissions. The price can be a few cents above or below any limit price, or the high/low for the day.</small></p>
                            
                            <h5><strong>Fund Trades</strong></h5>
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Symbol</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="highlight">jdoe's Mutual Fund</td>
                                        <td>JDM</td>
                                        <td>4,551</td>
                                    </tr>
                                </tbody>
                            </table>
                        
                        
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
            <!--END MODALS-->
            
            <!-- BEGIN PAGE CONTENT-->
            <div class="row">
            <div class="col-md-12">
               
               <?php /*?><div class="alert alert-block alert-warning fade in">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <h4><strong>TRADE CANCELLATION ISSUE - UPDATED</strong></h4>
                    <p>Members, some trades are being marked as Cancelled, but in fact they are actually filling. Please refrain from placing duplicate orders until we resolve this issue. We apologize for this inconvenience.</p>
                    
                </div><?php */?>
               
            	<!-- BEGIN RECENT ORDERS TABLE-->
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption"><i class="icon-globe"></i>Recently Closed Tickets</div>
                        <div class="actions">
                            <div class="btn-group">
                                <a class="btn btn-info dropdown-toggle" href="#" data-toggle="dropdown">
                                Columns
                                <i class="icon-angle-down"></i>
                                </a>
                                <div id="sample_2_column_toggler" class="dropdown-menu hold-on-click dropdown-checkboxes pull-right">
                                  <label><input type="checkbox" checked data-column="3">Shares</label>
                                  <label><input type="checkbox" checked data-column="4">Net Avg. Price</label>
                                </div>
                            </div><!--btn-group-->
                        </div>
                    </div><!--portlet-title-->
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover table-full-width" id="recent_orders_table">
                            <thead>
                                <tr>
                                	<th>Fund</th>
                                    <th>Open Date</th>
                                    <th>Close Date</th>
                                    <th>Type</th>
                                    <th>Symbol</th>
                                    <th>Shares Ordered</th>
                                    <th class="hidden-xs">Shares Filled</th>
                                    <th>Limit</th>
                                    <th class="hidden-xs">Net Avg. Price</th>
                                    <th>Net</th>
                                    <th>Status</th>
                                    <?php /*?><th>Actions</th><?php */?>
                                </tr>
                            </thead>
                        <tbody>
                        	<?php


							//echo $sqlMemberID;
							$past = date('Ymd', strtotime('-60 days'));
							$year = substr($past, 0, 4);
							$month = substr($past, 4, 2);
							$day = substr($past, 6, 2);
							$unixtime = mktime('0','0','0',$month,$day,$year);

							//echo $unixtime;

							$query = "
								SELECT *
								FROM members_fund_tickets
								WHERE member_id=:member_id AND status='cancelled' AND closed>:closed OR status='closed' AND member_id=:member_id AND closed>:closed
								ORDER BY closed DESC
							";

							try{
								$rsGetOrders = $mLink->prepare($query);
								$aValues = array(
									':member_id' 	=> $_SESSION['member_id'],
									':closed'		=> $unixtime
								);
								$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
								$rsGetOrders->execute($aValues);
							}
							catch(PDOException $error){
								// Log any error
								file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
							}
							
							if($_REQUEST['debug'] == 'ticket-query'){
								?>
                                <tr>
                                	<td colspan="11"><?php echo $preparedQuery;?></td>
                                </tr>
                                <?php	
							}
							
							while($order = $rsGetOrders->fetch(PDO::FETCH_ASSOC)){

								$tradeType = $order['action'];
								switch($tradeType){
									case "buy": $tradeType = "Buy";break;
									case "sell": $tradeType = "Sell";break;
								}

								$symbol = $order['symbol'];
								$date = $order['closed'];
								$openDate	= $order['openned'];
								
								$price = number_format($order['price'], 2, '.', ',');
								$sharesOrdered	= $order['shares'];
								$shares = $order['sharesFilled'];
								$orderType = $order['type'];
								switch($orderType){
									case "Day": $orderType = 'Day Order';break;
									case "GTCI": $orderType = "Good Until Canceled";break;
								}
								$net = number_format($order['net'], 2 , '.',',');

								$netAvg = number_format(($order['net'] / $order['shares']), 2, '.', ',');

								$fundSymbol = get_funds($mLink, $order['fund_id'], 'fundSymbol');

								$ticketStatus = $order['status'];

								switch($ticketStatus){
									case "cancelled": $ticketStatus = '<span class="label label-danger">'.$ticketStatus.'</span>';break;
									case "closed": $ticketStatus = '<span class="label label-info">'.$ticketStatus.'</span>';break;
								}
								
								?>
                                <tr>
                                	<td><a href="#global-ticket-details" data-toggle="modal" onclick="loadGlobalTradeDetails('<?php echo $order['ticket_key'];?>');"><?php echo $fundSymbol;?> <?php if($_REQUEST['debug'] == '1'){echo $order['fund_id'];}?></a></td>
                                    <td><?php echo date('M d, Y', $date);?></td>
                                    <td><?php echo date('M d, Y', $date);?></td>
                                    <td><a href="#global-ticket-details" data-toggle="modal" onclick="loadGlobalTradeDetails('<?php echo $order['ticket_key'];?>');"><?php echo $tradeType;?></a></td>
                                    <td><a href="#global-ticket-details" data-toggle="modal" onclick="loadGlobalTradeDetails('<?php echo $order['ticket_key'];?>');"><?php echo $symbol;?></a></td>
                                    <td><?php echo number_format($sharesOrdered, 0,'.',',');?></td>
                                    <td><?php echo number_format($shares, 0,'.',',');?></td>
                                    <td><?php echo number_format($order['limit'],2,'.',',');?></td>
                                    <td>$<?php echo $netAvg;?></td>
                                    <td>$<?php echo $net;?></td>
                                    <td><?php echo $ticketStatus;?></td>
                                    <?php /*?><td><a href="#fund-details" data-toggle="modal" class="btn btn-info">Details</a></td><?php */?>
                                </tr>
                                <?php
							}
							?>


                        </tbody>
                        </table>
                    </div><!--portlet-body-->
                </div>
                <!-- END RECENT ORDER TABLE-->
            </div>
            </div>
            <!-- END PAGE CONTENT-->
