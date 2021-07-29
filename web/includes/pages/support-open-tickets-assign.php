        <!-- BEGIN PAGE CONTENT-->
        <div class="row">
            <div class="col-md-12">
             
                <!-- BEGIN RECENT ORDERS TABLE-->
                <div class="portlet" id="ledger-entries">
                <div class="portlet-title">
                   <div class="caption"><i class="icon-globe"></i>Support Tickets</div>
                   <div class="actions">
                      <div class="btn-group" id="column-view">
                         <a class="btn btn-info dropdown-toggle" href="#" data-toggle="dropdown">
                         Columns
                         <i class="icon-angle-down"></i>
                         </a>
                         <div id="open_tickets_admin_column_toggler" class="dropdown-menu hold-on-click dropdown-checkboxes pull-right">
                            <label><input type="checkbox" checked data-column="1" />Date</label>
                            <label><input type="checkbox" checked data-column="2" />Case Number</label>
                            <label><input type="checkbox" checked data-column="2" />Ticket Type</label>
                            <label><input type="checkbox" checked data-column="3" />Subject</label>
                            <label><input type="checkbox" checked data-column="4" />Replies</label>
                            
                         </div>
                      </div>
                   </div>
                </div>
                <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover table-full-width" id="open_tickets_admin">
                        <thead>
                            <tr>
                                <th class="fzt-aleft" width="5%">DATE</th>
                                <th width="5%">Case Number</th>
                                <th width="5%">Member</th>
                                <th width="10%">Ticket Type</th>
                                <th>Subject/Description</th>
                                <th width="2%">Replies</th>
                                <th width="13%">Status</th>
                                <th width="13%">Assigned To</th>
                                <th width="2%" style="display:none;">Feedback</th>
                            </tr>
                        </thead>
                        <tbody>
                        	<?php 
							
							 /*$query = "
								SELECT lo.label, st.ticket_status, st.ticket_id, st.ticket_type, st.ticket_subject, st.problem_type, st.description, st.timestamp, st.member_id
								FROM support_tickets AS st
								INNER JOIN system_lists_options AS lo ON lo.list_id='5' AND lo.value=st.ticket_status
								WHERE st.ticket_status<>'0'
								ORDER BY st.ticket_id ASC
							";*/
							
							$query = "
								SELECT lo.label, st.ticket_status, st.ticket_id, st.ticket_type, st.ticket_subject, st.problem_type, st.description, st.timestamp, st.member_id, st.assigned_to, st.stock_ticker,
								(SELECT MAX( sf.timestamp) AS feedback_timestamp FROM ".$_SESSION['support_feedback_table']." AS sf WHERE sf.ticket_id=st.ticket_id AND admin_viewed='0') AS feedback
								FROM ".$_SESSION['support_ticket_table']." AS st
								INNER JOIN ".$_SESSION['lists_options_table']." AS lo ON lo.list_id =  '5'
								AND lo.value = st.ticket_status
								WHERE st.ticket_status <> '0' AND st.assigned_to=:assigned
								ORDER BY feedback ASC
							";
							
							try{
								$rsTickets = $mLink->prepare($query);
								$aValues = array(
									':assigned' 	=> $_SESSION['member_id']
								);
								$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
								$rsTickets->execute($aValues);
							}
							catch(PDOException $error){
								// Log any error
								file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
							}
							
							$cnt = 0;
							$cntOrder = 9000;
							while($ticket = $rsTickets->fetch(PDO::FETCH_ASSOC)) {
								$cnt = $cnt + 1;
								
								
								if($ticket['ticket_status'] == "0"){
									$showLabel = "danger";	
								}elseif($ticket['ticket_status'] == "1"){
									$showLabel = "success";	
								}elseif($ticket['ticket_status'] == '5'){
									$showLabel = "default";	
								}else{
									$showLabel = "warning";	
								}
								
								//START REPLY COUTNER
								$query = "
									SELECT ticket_id, COUNT(*) AS replys, viewed
									FROM ".$_SESSION['support_feedback_table']."
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
									FROM ".$_SESSION['support_feedback_table']."
									WHERE ticket_id=:ticket_id AND admin_viewed='0'
								";
								
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
								//END REPLY COUNTER
								
								//Member Details Query
								$query = "
									SELECT username, name_first, name_last
									FROM ".$_SESSION['members_table']."
									WHERE member_id=:member_id
								";
								
								try{
									$rsMember = $mLink->prepare($query);
									$aValues = array(
										':member_id' 	=> $ticket['member_id']
									);
									$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
									$rsMember->execute($aValues);
								}
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}
								
								$member = $rsMember->fetch(PDO::FETCH_ASSOC);
								$fullName = $member['name_first'] . " " . $member['name_last'];
								
								if($unread['replys'] != "0"){
									$ticketURL = "process/ajax/support-processes.php?process=7&ticket=".$ticket['ticket_id']."&user=admin";	
								}else{
									$ticketURL = "?page=08-01-cc-903&ticket=".$ticket['ticket_id']."";	
								}
															
								?>
                                <tr>
                                	<td><?php echo date('m/d/Y h:i a', $ticket['timestamp']); ?></td>
                                	
                                    <td align="center">
                                    <a href="<?php echo $ticketURL;?>" class="btn btn-<?php if($unread['replys'] != "0" OR $reply['replys'] == "0"){?>warning<?php }else{?>info<?php }?>">
										<?php echo $ticket['ticket_id']; ?>
                                    </a></td>
                                    
                                    <td><?php echo $fullName; ?></td>
                                	
                                    <td><?php echo get_ticket_type($mLink, $ticket['ticket_type']);?></td>
                                	
                                    <td>
                                    <a href="<?php echo $ticketURL;?>">
                                       	<strong><?php if($ticket['stock_ticker'] != ""){echo "".$ticket['stock_ticker']." | ";}?><?php echo $ticket['ticket_subject'];?></strong>
                                       	<br /><small><?php echo substr($ticket['description'], 0, 100); ?></small>
                                    </a>
                                    </td>
                                    
                                    <td>
                                    <span class="label label-info"><?php echo $reply['replys'];?></span>
									<?php if($unread['replys'] != "0"){?> <span class="label label-warning"><?php echo $unread['replys']; ?> Unread</span><?php } ?>
                                    </td>
                                    
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" id="us-btn1-<?php echo $ticket['ticket_id'];?>" class="btn btn-sm btn-<?php echo $showLabel; ?>"><?php echo $ticket['label']; ?></button>
                                            <button type="button" id="us-btn2-<?php echo $ticket['ticket_id'];?>" class="btn btn-sm btn-<?php echo $showLabel; ?> dropdown-toggle" data-toggle="dropdown"><i class="icon-angle-down"></i></button>
                                            <ul class="dropdown-menu" id="update-status-<?php echo $ticket['ticket_id'];?>" role="menu">
                                            	<?php
												//Member Details Query
												$query = "
													SELECT label, value
													FROM ".$_SESSION['lists_options_table']."
													WHERE list_id='5'
													ORDER BY sequence ASC
												";
												
												try{
													$rsStatus = $mLink->prepare($query);
													$aValues = array(
														':member_id' 	=> $ticket['member_id']
													);
													$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
													$rsStatus->execute($aValues);
												}
												catch(PDOException $error){
													// Log any error
													file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
												}
												
												while($status = $rsStatus->fetch(PDO::FETCH_ASSOC)){
													echo '<li><a href="javascript:void(0)" onClick="ticketStatus('.$ticket['ticket_id'].', '.$status['value'].', \'open-tickets\', '.$_SESSION['member_id'].')">'.$status['label'].'</a></li>';
												}
												?>
                                                
                                                
                                            </ul>
                                        </div><!--btn-group-->
                                        
                                	</td>
                                    <td>
                                    	<div class="btn-group">
                                    		<button class="btn btn-sm btn-info" id="assign<?php echo $ticket['ticket_id'];?>">
												<?php if($ticket['assigned_to'] == ""){echo "Unassigned";}else{ echo get_member($mLink, $ticket['assigned_to'], "full name");}?>
                                            </button>
                                    		<button type="button" class="btn btn-sm btn-info dropdown-toggle" data-toggle="dropdown"><i class="icon-angle-down"></i></button>
                                            	<ul class="dropdown-menu" role="menu">
                                            		<?php
													$query = "
														SELECT m.member_id, m.name_first, m.name_last
														FROM ".$_SESSION['members_table']." as m
														INNER JOIN ".$_SESSION['members_flags_table']." as flags ON m.member_id=flags.member_id
														WHERE flags.admin='1'
													";
													
													try{
														$rsAdmin = $mLink->prepare($query);
														$aValues = array(
															//':member_id' 	=> $ticket['member_id']
														);
														$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
														$rsAdmin->execute($aValues);
													}
													catch(PDOException $error){
														// Log any error
														file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
													}
													
													while($admin = $rsAdmin->fetch(PDO::FETCH_ASSOC)){
														if($admin['member_id'] != $ticket['assigned_to']){
															echo '<li><a href="javascript:void(0)" onClick="ticketAdmin('.$ticket['ticket_id'].', '.$admin['member_id'].', '.$_SESSION['member_id'].')">'.$admin['name_first'].' '.$admin['name_last'].'</a></li>';
														}
													}
													
													?>
                                                </ul>
                                        </div><!--btn-group-->
                                    </td>
                                    <td style="display:none;">
									<?php 
									if(is_null($ticket['feedback'])){
										if($unread['replys'] != "0" OR $reply['replys'] == "0"){
											echo $ticket['ticket_id'];
										}else{ 
											$cntOrder = $cntOrder +1; echo $cntOrder;
										}
									}else{ 
										echo date('d/m/Y h:i a', $ticket['feedback']);
									}?>
                                    </td>
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
        