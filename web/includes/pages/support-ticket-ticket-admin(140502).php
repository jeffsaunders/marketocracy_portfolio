<?php
//GET ALL TICKET INFO
$query = "
	SELECT lo.label AS ticket_status_label, st.ticket_id, st.ticket_type, st.ticket_status, st.problem_type, st.ticket_subject, st.description, st.stock_ticker, st.fund_tickers, st.priority, st.timestamp, st.member_id
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
		':member_id' 	=> $ticket['member_id']
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsUsername->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$user = $rsUsername->fetch(PDO::FETCH_ASSOC);
?>
        <!-- BEGIN PAGE CONTENT-->          
        <div class="row">
            <div class="col-md-12">
            	<a href="?page=08-01-00-002" class="btn btn-info" style="margin-bottom:20px;"><i class="icon-arrow-left"></i> Back</a>

                <!-- BEGIN New Support Ticket-->
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption">
                        	<span class="label label-lg label-<?php echo $showLabel; ?>"><i class="icon-tag"></i> <?php echo $ticket['ticket_status_label']; ?></span> <?php echo $ticket['ticket_subject']; ?>
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
                                    
                                    <div class="ticket-info-body <?php echo $systemStatus; ?>">
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
                                            <p><small>Additional Information</small></p>
                                            <p style=""><strong>Stock Ticker:</strong> <?php echo $ticket['stock_ticker'];?></p>
                                        </div>
                                        <?php
									}
									?>
                                </div><!--response-body-->
                            
                            </div>
                            
                            <div id="response-ajax">
                            <?php
							$query = "
								SELECT m.username, sf.response, sf.timestamp
								FROM support_feedback AS sf
								INNER JOIN ".$_SESSION['members_table']." AS m ON m.member_id=sf.member_id
								WHERE sf.ticket_id=:ticket_id
								ORDER BY sf.timestamp
							";
							
							//Fund Symbols
							try{
								$rsResponse = $mLink->prepare($query);
								$aValues = array(
									':ticket_id' 	=> $ticketID
								);
								$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
								$rsResponse->execute($aValues);
							}
							catch(PDOException $error){
								// Log any error
								file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
							}
							
							while($reply = $rsResponse->fetch(PDO::FETCH_ASSOC)){
								?>
                                <div style="margin:10px 0px 10px 20px;">
								<p style="border:1px solid #CCC;padding:10px;border-radius:3px;margin-bottom:0px;background:#F5F5F5;"><?php echo $reply['response']; ?></p>
                            	<small>Response by <?php echo $reply['username']; ?> on <?php echo date('m/d/Y', $reply['timestamp']);?> at <?php echo date('h:i A', $reply['timestamp']);?></small>
                                </div>
                                <?php
							}
							
							?>
                            </div>
                            
                        </div>
                        
                        <!-- BEGIN FORM-->
                        <form action="process/ajax/system-support-processes.php?process=2" method="post" class="ajax-reply" name="support-reply">
                                <div class="form-body">
                                  	<div id="userError">
                                    </div>
                                        
                                        <div class="form-group">
                                        	<label class="control-label">Reply *</label>
                                            <textarea name="reply" id="reply" class="form-control" rows="6"></textarea>
                                        </div>
                                        
                                </div><!--form-body-->
                                
                                <div class="form-actions">
                                		<span style="display:block;float:left;">	
                                            <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id']; ?>"  />
                                            <input type="hidden" name="ticket_member_id" value="<?php echo $ticket['member_id']; ?>" />
                                        	<a href="?page=08-01-00-002" class="btn btn-info"><i class="icon-arrow-left"></i> Back</a>
                                        	<input type="submit" value="Post Reply" class="btn btn-success">
                                        </span>
                                        <span style="display:block;float:right;">
                                        	<a href="void.javascript(0);" onClick="closeTicket();" class="btn btn-danger">Close Ticket</a>
                                        </span>
                                        <?php //Form Processed by ajax | call file: includes/scripts/system-support-scripts.php | process file: process/ajax/system-support-processes.php ?>
                                </div>
                        </form>
                        <!-- END FORM--> 
                    </div>
                </div>
                <!-- END SAMPLE TABLE PORTLET-->
               
            </div><!--END COLUMN-->
        </div><!--END ROW-->
       <!-- END PAGE CONTENT-->
        