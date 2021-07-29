        <!-- BEGIN PAGE CONTENT-->
        <div class="row">
            <div class="col-md-12">
             
                <!-- BEGIN RECENT ORDERS TABLE-->
                <div class="portlet" id="ledger-entries">
                <div class="portlet-title">
                   <div class="caption"><i class="icon-tag"></i>Active Corporate Actions</div>
                   <div class="actions">
                      <div class="btn-group" id="column-view">
                         <a class="btn btn-info dropdown-toggle" href="#" data-toggle="dropdown">
                         Columns
                         <i class="icon-angle-down"></i>
                         </a>
                         <div id="open_tickets_column_toggler" class="dropdown-menu hold-on-click dropdown-checkboxes pull-right">
                            <label><input type="checkbox" checked data-column="0" />Date</label>
                            <label><input type="checkbox" checked data-column="2" />Submitted By</label>
                            <label><input type="checkbox" checked data-column="3" />Subject</label>
                            <label><input type="checkbox" checked data-column="4" />Replies</label>
                            
                         </div>
                      </div>
                   </div>
                </div>
                <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover table-full-width" id="open_tickets">
                        <thead>
                            <tr>
                                <th class="fzt-aleft" width="5%">DATE</th>
                                <th width="5%">Ticket Number</th>
                                <th width="10%">Submitted By</th>
                                <th>Stock Ticker</th>
                                <th>Subject/Description</th>
                                <th width="5%">Status</th>
                                <th width="2%">Replies</th>
                            </tr>
                        </thead>
                        <tbody>
                        	<?php 
							
							 $query = "
								SELECT lo.label, st.ticket_status, st.ticket_id, st.ticket_type, st.ticket_subject, st.problem_type, st.description, st.timestamp, st.member_id, st.stock_ticker
								FROM ".$_SESSION['support_ticket_table']." AS st
								INNER JOIN ".$_SESSION['lists_options_table']." AS lo ON lo.list_id='5' AND lo.value=st.ticket_status
								WHERE st.ticket_status<>'0' AND ticket_type='2'
								ORDER BY st.ticket_id
							";
							
							//Fund Symbols
							try{
								$rsTickets = $mLink->prepare($query);
								$aValues = array(
									':member_id' 	=> $_SESSION['member_id']
								);
								$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
								$rsTickets->execute($aValues);
							}
							catch(PDOException $error){
								// Log any error
								file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
							}
							
							$cnt = 0;
							while($ticket = $rsTickets->fetch(PDO::FETCH_ASSOC)) {
								$cnt = $cnt + 1;
								
								if($ticket['ticket_status'] == "0"){
									$showLabel = "danger";	
								}elseif($ticket['ticket_status'] == "1"){
									$showLabel = "success";	
								}else{
									$showLabel = "warning";	
								}
								
								$query = "
									SELECT ticket_id, COUNT(*) AS replys, viewed
									FROM ".$_SESSION['community_feedback_table']."
									WHERE ticket_id=:ticket_id
								";
								
								try{
									$rsReplys = $mLink->prepare($query);
									$aValues = array(
										':ticket_id' 	=> $ticket['ticket_id']
									);
									$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
									$rsReplys->execute($aValues);
								}
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}
								
								$reply = $rsReplys->fetch(PDO::FETCH_ASSOC);
								
								$query = "
									SELECT ticket_id, COUNT(*) AS replys, viewed
									FROM ".$_SESSION['community_feedback_table']."
									WHERE ticket_id=:ticket_id AND viewed='0'
								";
								
								//Fund Symbols
								try{
									$rsUnread = $mLink->prepare($query);
									$aValues = array(
										':ticket_id' 	=> $ticket['ticket_id']
									);
									$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
									$rsUnread->execute($aValues);
								}
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}
								
								$unread = $rsUnread->fetch(PDO::FETCH_ASSOC);
								
								/*if($unread['replys'] != "0"){
									$ticketURL = "process/ajax/support-processes.php?process=7&ticket=".$ticket['ticket_id']."&user=member&ca=1";	
								}else{
									$ticketURL = "?page=04-01-cc-003&ticket=".$ticket['ticket_id']."&ca=1";	
								}*/
								$ticketURL = "?page=04-02-cc-003&ticket=".$ticket['ticket_id']."";
																
								?>
                                <tr>
                                	<td><?php echo date('m/d/Y', $ticket['timestamp']); ?></td>
                                	<td align="center"><a href="<?php echo $ticketURL; ?>" class="btn btn-<?php if($unread['replys'] != "0"){?>warning<?php }else{?>info<?php }?>"><?php echo $ticket['ticket_id']; ?></a></td>
                                	<td><?php echo get_member($mLink, $ticket['member_id'], 'full name');?></td>
                                	<td><?php echo $ticket['stock_ticker']; ?></td>
                                    <td><a href="<?php echo $ticketURL; ?>"><strong><?php echo $ticket['ticket_subject'];?></strong><br /><small><?php echo substr($ticket['description'], 0, 100); ?></small></a></td>
                                	<td><span class="label label-lg label-<?php echo $showLabel; ?>"><?php echo $ticket['label']; ?></span></td>
                                    <td><span class="label label-info"><?php echo $reply['replys'];?></span></td>
                            	</tr>
								<?php
							}
							?>
                            
                        </tbody>
                        </table>
                </div>
                </div>
                <!-- END RECENT ORDER TABLE-->
            </div>
        </div><!--row-->
        