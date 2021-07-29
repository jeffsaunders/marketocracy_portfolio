<?php
//GET ALL TICKET INFO
$query = "
	SELECT lo.label AS ticket_status_label, st.ticket_id, st.ticket_type, st.ticket_status, st.problem_type, st.ticket_subject, st.description, st.stock_ticker, st.fund_tickers, st.priority, st.timestamp, st.member_id, st.anonymous, st.resolution, st.resolution_timestamp
	FROM ".$_SESSION['support_ticket_table']." AS st
	INNER JOIN ".$_SESSION['lists_options_table']." AS lo ON lo.list_id='5' AND lo.value=st.ticket_status
	WHERE st.ticket_id=:ticket_id
";

try{
	$rsTicket = $mLink->prepare($query);
	$aValues = array(
		':ticket_id'	=> $ticketID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsTicket->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$ticket = $rsTicket->fetch(PDO::FETCH_ASSOC);

switch($ticket['ticket_status']){
	case "0": $showLabel = "danger";$systemStatus = "status-danger";break; //Closed
	case "1": $showLabel = "success";$systemStatus = "status-success";break; //Open
	case "2": $showLabel = "warning";$systemStatus = "status-warning";break; //On Hold
	case "3": $showLabel = "warning";$systemStatus = "status-warning";break; //Under Review
	case "4": $showLabel = "success";$systemStatus = "status-success";break; //Awaiting Response
}

if($ticket['ticket_status'] == "0"){
	$statusLabel = "Closed";	
}else{
	$statusLabel = "Open";	
}

$closedTicket = $_REQUEST['closed'];

if($closedTicket == "1"){
	$backURL = "?page=04-02-00-004";
}else{
	$backURL = "?page=04-02-00-002";	
}
?>
        <!-- BEGIN PAGE CONTENT-->          
        <div class="row">
            <div class="col-md-12">
            	<a href="<?php echo $backURL; ?>" class="btn btn-info" style="margin-bottom:20px;"><i class="icon-arrow-left"></i> Back</a>

                <!-- BEGIN New Support Ticket-->
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption">
                        	<span id="update-status" class="label label-lg label-<?php echo $showLabel; ?>"><i class="icon-tag"></i> <?php echo $ticket['ticket_status_label']; ?></span> <?php echo $ticket['ticket_subject']; ?> | Posted By: <?php if($ticket['anonymous'] == "1"){echo "Anonymous";}else{ echo get_member($mLink, $ticket['member_id'], 'full name');}?>
                        </div>
                        <div class="tools">
                            <a href="" class="reload" onClick="refreshResponses()"></a>
                      	</div>
                        
                    </div>
                    <div class="portlet-body form">
                    
                    	<div class="row" style="text-align:center;">
                        	<div class="col-md-3">
                            	<div class="ticket-info">
                                	<div class="ticket-info-header">
                                		<h4>Type</h4>
                                    </div><!--ticket-info-header-->
                                    <div class="ticket-info-body">
                                    	<h5><strong><?php echo get_ticket($mLink, $ticket['ticket_id'], 'type');?></strong> | <?php echo get_ticket($mLink, $ticket['ticket_id'], 'problem'); ?></h5>
                                    </div><!--ticket-info-body-->
                                </div><!--ticket-info-->
                            </div><!--col-md-3-->
                            <div class="col-md-3">
                            	
                                <div class="ticket-info">
                                	<div class="ticket-info-header">
                                		<h4>Ticket</h4>
                                    </div><!--ticket-info-header-->
                                    <div class="ticket-info-body">
                                    	<h5><strong><?php echo $ticket['ticket_id'];?></strong></h5>
                                    </div><!--ticket-info-body-->
                                </div><!--ticket-info-->
                            </div><!--col-md-3-->
                            <div class="col-md-3">
                            	
                                <div class="ticket-info">
                                	<div class="ticket-info-header">
                                		<h4>Priority</h4>
                                    </div><!--ticket-info-header-->
                                    <div class="ticket-info-body">
                                    	<h5><strong><?php echo get_ticket($mLink, $ticket['ticket_id'], "priority");?></strong></h5>
                                    </div><!--ticket-info-body-->
                                </div><!--ticket-info-->
                            </div><!--col-md-3-->
                            <div class="col-md-3">
                            	
                                <div class="ticket-info">
                                	<div class="ticket-info-header">
                                		<h4>Status</h4>
                                    </div><!--ticket-info-header-->
                                    
                                    <div class="ticket-info-body <?php echo $systemStatus; ?>" id="update-status2" >
                                    	<h5><strong><?php echo $statusLabel; ?></strong></h5>
                                    </div><!--ticket-info-body-->
                                </div><!--ticket-info-->
                            </div><!--col-md-3-->
                        </div><!--row-->
                        
                    	<div style="margin:10px;">
                        	
                            <div style="margin:10px 0px 10px 0px;border:1px solid #CCC;border-radius:3px;">
                                <div class="response-header" style="background:#5BC0DE;">
                                    <div class="responder">
                                        Description / Resources:
                                    </div><!--responder-->
                                    <div class="response-date">
                                        <?php echo date('D m/d/Y', $ticket['timestamp']);?> at <?php echo date('h:i A', $ticket['timestamp']);?>
                                    </div><!--response-date-->
                                    <div class="clear"></div>
                                </div><!--response-header-->
                                
                                <div class="response-body">
                                	<p><?php echo $ticket['description']; ?></p>
                                    
                                    <?php
									if($ticket['stock_ticker'] != ""){
										?>
                                        <div style="border-top:1px solid #CCC;margin:0px 0px;padding:1px 0px 0px 0px;">
                                            <p><small><strong>Additional Information:</strong></small></p>
                                            <p style=""><strong>Stock Ticker:</strong> <?php echo $ticket['stock_ticker'];?></p>
                                           
                                        </div>
                                        <?php
									}
									?>
                                </div><!--response-body-->
                            
                            </div><!--Description /Resources-->
                            
                            <?php
							if($ticket['resolution'] != ""){
							?>
                                <div class="resolution" style="margin:10px 0px 10px 0px;border:1px solid #CCC;border-radius:3px;">
                                    <div class="response-header" style="background:#5CB85C;">
                                        <div class="responder">
                                            <i class="icon-check"></i> Resolution:
                                        </div><!--responder-->
                                        <div class="response-date">
                                            <?php echo date('D m/d/Y', $ticket['resolution_timestamp']);?> at <?php echo date('h:i A', $ticket['resolution_timestamp']);?>
                                        </div><!--response-date-->
                                        <div class="clear"></div>
                                    </div><!--response-header-->
                                    
                                    <div class="response-body">
                                        <p><?php echo $ticket['resolution']; ?></p>
                                        
                                    </div><!--response-body-->
                                
                                </div><!--resolution-->
                            <?php
							}
							?>
                            
                            <div id="community-response-ajax">
                          	<?php
							$query = "
								SELECT *
								FROM ".$_SESSION['community_feedback_table']." 
								WHERE ticket_id=:ticket_id
								ORDER BY timestamp
							";
							
							try{
								$rsResponse = $mLink->prepare($query);
								$aValues = array(
									':ticket_id' 	=> $ticket['ticket_id']
								);
								$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
								$rsResponse->execute($aValues);
							}
							catch(PDOException $error){
								// Log any error
								file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
							}
							
							while($reply = $rsResponse->fetch(PDO::FETCH_ASSOC)){
								$response = $reply['response'];
								$fullName = $reply['name_first'] . " " . $reply['name_last'];
								
								// Start Portlets
								
								
								
								// End Portlets
								
								
								if($reply['member_id'] == $reply['ticket_member_id']){
									//orginal poster
									if($reply['anonymous'] == "1"){
										if($_SESSION['admin'] == "1"){
											$title = "Response By Anonymous (".get_member($mLink, $reply['member_id'], 'full name').") (Original Poster)";
											$titleStyle = "background:#414247;";
										}else{
											$title = "Response By Anonymous (Original Poster)";
											$titleStyle = "background:#414247;";
										}
									}else{
										$title = "Response By ".get_member($mLink, $reply['member_id'], 'full name')." (Original Poster)";
										$titleStyle = "background:#414247;";
									}
								}else{
									//not original poster
									if($reply['anonymous'] == "1"){
										if($_SESSION['admin'] == "1"){
											$title = "Response By Anonymous (".get_member($mLink, $reply['member_id'], 'full name').")";
											$titleStyle = "background:#414247;";
										}else{
											$title = "Response By Anonymous";
											$titleStyle = "background:#414247;";
										}
									}else{
										$title = "Response By ".get_member($mLink, $reply['member_id'], 'full name')."";
										$titleStyle = "background:#414247;";
									}
								}
								
								//Check to see if the user is looking at his/her own response
								if($_SESSION['member_id'] == $reply['member_id']){
									//Check to see if user is the original poster
									if($reply['member_id'] == $reply['ticket_member_id']){
										$title = "Response by You (Original Poster)";
										$titleStyle = "background:#5BC0DE;";
									}else{
										$title = "Response by You";
										$titleStyle = "background:#5BC0DE;";
									}
								}
								
								/*if($_SESSION['admin'] == "1"){
									$title = "Response By ".$fullName."";
								}*/
								
								?>
								<div class="response-container">
									<div class="response-header" style="<?php echo $titleStyle; ?>">
										<div class="responder" style="float:left;">
											<?php echo $title; ?>
										</div><!--responder-->
										<div class="response-date">
											<?php echo date('D m/d/Y', $reply['timestamp']);?> at <?php echo date('h:i A', $reply['timestamp']);?>
										</div><!--response-date-->
										<div class="clear"></div>
									</div><!--response-header-->
									
									<div class="response-body">
										<p class="last"><?php echo $response; ?></p>
									</div><!--response-body-->
                                    
                                    <div class="response-buttons">
                                    	<a href="javascript:void(0);" class="btn btn-success">Agree</a> <a href="javascript:void(0);" class="btn btn-danger">Disagree</a>
                                    </div><!--response-buttons-->
                                    <div class="clear"></div><!--clear-->
								</div>
								<?php
							}
							
							?>
                            </div><!--response-ajax-->
                            
                        </div>
                        
                        <?php if(get_ticket($mLink, $ticketID, 'status') != "Closed"){?>
                            <!-- BEGIN FORM-->
                            <form action="process/ajax/support-processes.php?process=3&community=1" method="post" class="community-ajax-reply" name="community-support-reply">
                                    <div class="form-body">
                                        <div id="userError">
                                        </div>
                                            
                                            <div class="form-group">
                                                <label class="control-label">Reply * <span id="status-update6"></span><?php if($ticket['ticket_status'] == "0"){?><span class="label label-danger" id="status-update3">Ticket Is CLOSED. To re-open ticket, post a reply.</span><?php }?></label>
                                                <textarea name="community-reply" id="community-reply" class="form-control" rows="4" style="resize:vertical;"></textarea>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>
                                                <input type="checkbox" name="anonymous" class="form-control" /> Reply Anonymously
                                                </label>
                                                <span class="help-block">If checked your reply will not show your name.</span>
                                            </div>
                                            
                                    </div><!--form-body-->
                                    
                                    <div class="form-actions">
                                            <span style="display:block;float:left;">
                                                <input type="hidden" name="ticket_member_id" value="<?php echo $ticket['member_id']; ?>" />	
                                                <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id']; ?>"  />
                                                <input type="hidden" name="ticket_status" value="<?php echo $ticket['ticket_status']; ?>"  />
                                                <input type="hidden" name="response" value="1" />
                                                
                                                
                                                <a href="<?php echo $backURL; ?>" class="btn btn-info"><i class="icon-arrow-left"></i> Back</a>
                                                <input type="submit" value="Post Reply" class="btn btn-success">
                                            </span>
                                            <span style="display:block;float:right;" id="status-update5">
                                                
                                            </span>
                                            <?php //Form Processed by ajax | call file: includes/scripts/support-scripts.php | process file: process/ajax/support-processes.php ?>
                                    </div>
                            </form>
                            <!-- END FORM-->
                            
                            <?php
                       }
							if($_SESSION['admin'] == "1"){
								
								if($ticket['resolution'] == "" || $ticket['ticket_status'] != "0"){
									$postBTN = "Post Resolution And Close Ticket";
									$postBtnStatus = "danger";	
								}else{
									$postBTN = "Save Resolution";
									$postBtnStatus = "warning";		
								}
							?>
                            	
                            	<!-- BEGIN RESOLUTION FORM-->
                                <form action="process/ajax/support-processes.php?process=14" method="post" class="ajax-resolution" name="support-resolution">
                                        <div class="form-body">
                                            <div id="userErrorResolution">
                                            </div>
                                                
                                                <div class="form-group">
                                                    <label class="control-label">Resolution *<span id="status-update6"></span></label>
                                                    <textarea name="resolution" id="resolution" class="form-control" rows="4" style="resize:vertical;"><?php echo $ticket['resolution'];?></textarea>
                                                </div>
                                                
                                                
                                                
                                        </div><!--form-body-->
                                        
                                        <div class="form-actions">
                                                <span style="display:block;float:left;">
                                                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id']; ?>"  />
                                                    
                                                    <input type="submit" value="<?php echo $postBTN;?>" class="btn btn-<?php echo $postBtnStatus;?>">
                                                </span>
                                                <span style="display:block;float:right;" id="status-update5">
                                                    
                                                </span>
                                                <?php //Form Processed by ajax | call file: includes/scripts/support-scripts.php | process file: process/ajax/support-processes.php ?>
                                        </div>
                                </form>
                                <!-- END RESOLUTION FORM-->
                            
                            <?php	
							}
							?>
                    </div>
                </div>
                <!-- END TICKET PORTLET-->
                
                
               
            </div><!--END COLUMN-->
        </div><!--END ROW-->
       <!-- END PAGE CONTENT-->
        