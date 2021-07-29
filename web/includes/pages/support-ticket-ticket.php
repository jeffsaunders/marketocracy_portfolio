<?php
if($_SESSION['admin'] == "1"){
	$checkMemberID = "";
	$checkArray = array(':ticket_id' => $ticketID);	
}else{
	$checkMemberID = "st.member_id=:member_id AND";
	$checkArray = array(':member_id' => $_SESSION['member_id'], ':ticket_id' => $ticketID);
}

//GET ALL TICKET INFO
$query = "
	SELECT lo.label AS ticket_status_label, st.ticket_id, st.ticket_type, st.ticket_status, st.problem_type, st.ticket_subject, st.description, st.stock_ticker, st.fund_tickers, st.priority, st.timestamp, st.member_id, st.additional_comments, st.request_emails
	FROM ".$_SESSION['support_ticket_table']." AS st
	INNER JOIN ".$_SESSION['lists_options_table']." AS lo ON lo.list_id='5' AND lo.value=st.ticket_status
	WHERE ".$checkMemberID." st.ticket_id=:ticket_id
";

try{
	$rsTicket = $mLink->prepare($query);
	$aValues = $checkArray;
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
	case "4": $showLabel = "warning";$systemStatus = "status-warning";break; //Awaiting Response
}

//GET LABEL FOR PROBLEM TYPES
$query = "
	SELECT label
	FROM ".$_SESSION['lists_options_table']."
	WHERE list_id='3' OR list_id='2' AND value=:value
";

try{
	$rsListLabel = $mLink->prepare($query);
	$aValues = array(
		':value' 	=> $ticket['problem_type']
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsListLabel->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$problem = $rsListLabel->fetch(PDO::FETCH_ASSOC);

//GET LABEL FOR PRIORITY
$query = "
	SELECT label
	FROM ".$_SESSION['lists_options_table']."
	WHERE list_id='4' AND value=:value
";

try{
	$rsPriorityLabel = $mLink->prepare($query);
	$aValues = array(
		':value' 	=> $ticket['priority']
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsPriorityLabel->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$priority = $rsPriorityLabel->fetch(PDO::FETCH_ASSOC);

//GET USERNAME
$query = "
	SELECT username
	FROM ".$_SESSION['members_table']."
	WHERE member_id=:member_id
";

try{
	$rsUsername = $mLink->prepare($query);
	$aValues = array(
		':member_id' 	=> $_SESSION['member_id']
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsUsername->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$user = $rsUsername->fetch(PDO::FETCH_ASSOC);

$closedTicket = $_REQUEST['closed'];

if($closedTicket == "1"){
	$backURL = "?page=08-01-00-004";
}else{
	$backURL = "?page=08-01-00-002";	
}

if($_REQUEST['ca'] == "1"){
	$backURL = "?page=08-02-00-002";	
}

if($_REQUEST['ca'] == "1" && $closedTicket == "1"){
	$backURL = "?page=08-02-00-004";	
}
?>
        <!-- BEGIN PAGE CONTENT-->          
        <div class="row">
            <div class="col-md-12">
            	<a href="<?php echo $backURL;?>" class="btn btn-info" style="margin-bottom:20px;"><i class="icon-arrow-left"></i> Back</a>

                <!-- BEGIN New Support Ticket-->
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption">
                        	<span id="update-status" class="label label-lg label-<?php echo $showLabel; ?>"><i class="icon-tag"></i> <?php echo $ticket['ticket_status_label']; ?></span> <?php echo $ticket['ticket_subject']; ?>
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
                                    	<h5><strong><?php echo get_ticket_type($mLink, $ticket['ticket_type']);?></strong> | <?php echo $problem['label']; ?></h5>
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
                                    	<h5><strong><?php echo $priority['label'];?></strong></h5>
                                    </div><!--ticket-info-body-->
                                </div><!--ticket-info-->
                            </div><!--col-md-3-->
                            <div class="col-md-3">
                            	
                                <div class="ticket-info">
                                	<div class="ticket-info-header">
                                		<h4>Status</h4>
                                    </div><!--ticket-info-header-->
                                    
                                    <div class="ticket-info-body <?php echo $systemStatus; ?>" id="update-status2" >
                                    	<h5><strong><?php echo $ticket['ticket_status_label']; ?></strong></h5>
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
									if($ticket['additional_comments'] != ""){?>
                                    	<div style="border-top:1px solid #CCC;margin:0px 0px;padding:1px 0px 0px 0px;">
                                        	<p><small><strong>Additional Comments:</strong></small></p>
                                            <p><?php echo $ticket['additional_comments'];?></p>
                                    	</div>
									<?php } ?>
                                </div><!--response-body-->
                            
                            </div>
                            
                            <div id="response-ajax">
                            <?php
							$query = "
								SELECT m.username, m.name_first, m.name_last, sf.response, sf.timestamp, sf.member_id, sf.ticket_member_id
								FROM ".$_SESSION['support_feedback_table']." AS sf
								INNER JOIN ".$_SESSION['members_table']." AS m ON m.member_id=sf.member_id
								WHERE sf.ticket_id=:ticket_id
								ORDER BY sf.timestamp
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
								
								$title = "Response By ".$fullName."";
								$titleStyle = "background:#414247;";
								
								if($reply['ticket_member_id'] == $reply['member_id']){
									$title = "Response by You";
									$titleStyle = "background:#5BC0DE;";	
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
								</div>
								<?php
							}
							
							?>
                            </div><!--response-ajax-->
                            
                        </div>
                        
                        <!-- BEGIN FORM-->
                        <form action="process/ajax/support-processes.php?process=2" method="post" class="ajax-reply" name="support-reply">
                                <div class="form-body">
                                  	<div id="userError">
                                    </div>
                                        
                                        <div class="form-group">
                                        	<label class="control-label">Reply * <span id="reply-help"></span><span id="status-update6"></span><?php if($ticket['ticket_status'] == "0"){?><span class="label label-danger" id="status-update3">Ticket Is CLOSED. To re-open ticket, post a reply.</span><?php }?></label>
                                            <textarea name="reply" id="reply-editor" class="form-control" rows="6"></textarea>
                                        </div>
                                        
                                        <div class="form-group">
                                        	<label>
                                        	<input type="checkbox" name="request_email" class="form-control" <?php if($ticket['request_emails']=='1'){echo 'checked';}?>/> Request Email Updates
                                            </label>
                                            <span class="help-block">If checked, you will recieve email updates for this ticket.</span>
                                        </div>
                                </div><!--form-body-->
                                
                                <div class="form-actions">
                                		<span style="display:block;float:left;">
                                        	<input type="hidden" name="ticket_member_id" value="<?php echo $ticket['member_id']; ?>" />	
                                            <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id']; ?>"  />
                                            <input type="hidden" name="ticket_status" value="<?php echo $ticket['ticket_status']; ?>"  />
                                            
                                            
                                        	<a href="<?php echo $backURL;?>" class="btn btn-info"><i class="icon-arrow-left"></i> Back</a>
                                        	<input type="submit" value="Post Reply" class="btn btn-success" id="submit-reply" disabled="disabled" style="border-color:#666666 !important;color:#000000;">
                                        </span>
                                        <span style="display:block;float:right;" id="status-update5">
                                        	<?php
											if($ticket['ticket_status'] != "0"){
												?>
                                        		<a href="javascript:void(0);" onClick="ticketStatus(<?php echo $ticket['ticket_id'];?>, 0, 'view-ticket-close');" class="btn btn-danger" id="close-ticket">Close Ticket</a>
                                                <?php
											}
											?>
                                        </span>
                                        <?php //Form Processed by ajax | call file: includes/scripts/support-scripts.php | process file: process/ajax/support-processes.php ?>
                                </div>
                        </form>
                        <!-- END FORM--> 
                        
                    </div>
                </div>
                <!-- END SAMPLE TABLE PORTLET-->
               
            </div><!--END COLUMN-->
        </div><!--END ROW-->
       <!-- END PAGE CONTENT-->
        