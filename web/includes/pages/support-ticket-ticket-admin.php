<?php
//GET ALL TICKET INFO
$query = "
	SELECT lo.label AS ticket_status_label, st.*
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

$notesFile	= $ticket['notes_file'];



$username	= get_member($mLink, $ticket['member_id'], 'username');
$memberName = get_member($mLink, $ticket['member_id'], 'full name');


$fundSymbols = $ticket['fund_tickers'];

$aFundSymbols = explode("|", $fundSymbols);

$aFundList = Array();

foreach($aFundSymbols as $fundID){
	$aFundList[] = get_global_funds($mLink, $fundID, 'fundSymbol');	
}

$fundList = implode('|', $aFundList);

if($fundList == ''){
	$fundList = 'N/A';	
}else{
	$fundList = $fundList;	
}

if($ticket['stock_ticker'] != ''){
	$stockTicker = $ticket['stock_ticker'];	
}else{
	$stockTicker = 'N/A';	
}

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
	WHERE list_id='3' AND value=:value OR list_id='2' AND value=:value
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

//echo $problem['label'];

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

/*//GET USERNAME
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

$user = $rsUsername->fetch(PDO::FETCH_ASSOC);*/

$closedTicket = $_REQUEST['closed'];

if($closedTicket == "1"){
	$backURL = "?page=08-01-00-904";
}else{
	$backURL = "?page=08-01-00-902";	
}



if($notesFile != ''){
	$aNotesFiles = explode('|', $notesFile);	
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
                        
                        <div class="row" style="text-align:center;">
                        	<div class="col-md-3">
                            	<div class="ticket-info">
                                	<div class="ticket-info-header">
                                		<h4>Member Name</h4>
                                    </div><!--ticket-info-header-->
                                    <div class="ticket-info-body">
                                    	<h5><strong><a href="?page=04-00-00-001&member=<?php echo $ticket['member_id'];?>" target="_blank"><?php echo $memberName; ?></a></strong></h5>
                                    </div><!--ticket-info-body-->
                                </div><!--ticket-info-->
                            </div><!--col-md-3-->
                            <div class="col-md-3">
                            	
                                <div class="ticket-info">
                                	<div class="ticket-info-header">
                                		<h4>Username</h4>
                                    </div><!--ticket-info-header-->
                                    <div class="ticket-info-body">
                                    	<h5><strong><a href="?page=20-00-00-003&member=<?php echo $ticket['member_id'];?>" target="_blank"><?php echo $username;?></a></strong></h5>
                                    </div><!--ticket-info-body-->
                                </div><!--ticket-info-->
                            </div><!--col-md-3-->
                            <div class="col-md-3">
                            	
                                <div class="ticket-info">
                                	<div class="ticket-info-header">
                                		<h4>Fund Symbols</h4>
                                    </div><!--ticket-info-header-->
                                    <div class="ticket-info-body">
                                    	<h5><strong><?php echo $fundList;?></strong></h5>
                                    </div><!--ticket-info-body-->
                                </div><!--ticket-info-->
                            </div><!--col-md-3-->
                            <div class="col-md-3">
                            	
                                <div class="ticket-info">
                                	<div class="ticket-info-header">
                                		<h4>Stock Tickers</h4>
                                    </div><!--ticket-info-header-->
                                    
                                    <div class="ticket-info-body">
                                    	<h5><strong><?php echo $stockTicker; ?></strong></h5>
                                    </div><!--ticket-info-body-->
                                </div><!--ticket-info-->
                            </div><!--col-md-3-->
                        </div><!--row-->
                        
                    	<div style="margin:10px;">
                        	
                            <div class="description" style="margin:10px 0px 10px 0px;border:1px solid #CCC;border-radius:3px;">
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
                            
                            </div><!--description-->
                            
                            <div class="tabbable tabbable-custom">
                                <ul class="nav nav-tabs">
                                    <li class="active"><a href="#tab_personal" data-toggle="tab">Personal</a></li>
                                    <?php if($ticket['ticket_type'] == "2"){?>
                                    <li><a href="#tab_community" data-toggle="tab">Community</a></li>
                                    <?php } ?>
                                </ul>
                                <div class="tab-content" style="padding:0px !important;">
                                    <div class="tab-pane active" id="tab_personal">
                                    
                                    	<div id="response-ajax" style="padding-right:10px !important;">
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
                                                    <p class="last"><?php echo $reply['response']; ?></p>
                                                </div><!--response-body-->
                                            </div>
                                            <?php
                                        }
                                        
                                        ?>
                            			</div><!--response-ajax-->
                                        
                                        <!-- BEGIN FORM-->
                                        <form action="process/ajax/support-processes.php?process=3" method="post" class="ajax-reply" name="support-reply">
                                                <div class="form-body">
                                                    <div id="userError">
                                                    </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="control-label">Reply * <span id="reply-help"></span><span id="status-update6"></span><?php if($ticket['ticket_status'] == "0"){?><span class="label label-danger" id="status-update3">Ticket Is CLOSED. To re-open ticket, post a reply.</span><?php }?></label>
                                                            <textarea name="reply" id="reply-editor" class="form-control" rows="6"></textarea>
                                                        </div>
                                                        
                                                </div><!--form-body-->
                                                
                                                <div class="form-actions">
                                                        <span style="display:block;float:left;">
                                                            <input type="hidden" name="ticket_member_id" value="<?php echo $ticket['member_id']; ?>" />	
                                                            <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id']; ?>"  />
                                                            <input type="hidden" name="ticket_status" value="<?php echo $ticket['ticket_status']; ?>"  />
                                                            <input type="hidden" name="response" value="1" />
                                                            
                                                            
                                                            <a href="<?php echo $backURL; ?>" class="btn btn-info"><i class="icon-arrow-left"></i> Back</a>
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
                                    
                                    </div><!--tab-pane-->
                                    
                                    
                                    <?php if($ticket['ticket_type'] == "2"){?>
                                    <div class="tab-pane" id="tab_community">
                                    
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
                                        </div><!--community-response-ajax-->
                                        
                                        <!-- BEGIN FORM-->
                                        <form action="process/ajax/support-processes.php?process=3&community=1" method="post" class="community-ajax-reply" name="community-support-reply">
                                                <div class="form-body">
                                                    <div id="userError">
                                                    </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="control-label">Reply * <span id="community-reply-help"></span><span id="status-update6"></span><?php if($ticket['ticket_status'] == "0"){?><span class="label label-danger" id="status-update3">Ticket Is CLOSED. To re-open ticket, post a reply.</span><?php }?></label>
                                                            <textarea name="community-reply" id="community-reply-editor" class="form-control" rows="4" style="resize:vertical;"></textarea>
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
                                                            <input type="submit" value="Post Reply" class="btn btn-success" id="submit-community-reply">
                                                        </span>
                                                        <span style="display:block;float:right;" id="status-update5">
                                                            
                                                        </span>
                                                        <?php //Form Processed by ajax | call file: includes/scripts/support-scripts.php | process file: process/ajax/support-processes.php ?>
                                                </div>
                                        </form>
                                        <!-- END FORM-->
                                        
                                    </div><!--tab-pane-community-response-->
                                    <?php } ?>
                                    
                                </div><!--tab-content-->
                                
                            </div><!--tabbable tabbable-custom-->
                            
                        </div><!--margin-->
                        
                    </div>
                </div>
                <!-- END CA PORTLET-->
                
                <!-- BEGIN File Upload-->
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption">
                        	<i class="icon-pencil"></i> Support Ticket Files
                        </div>
                        
                    </div>
                    <div class="portlet-body form">
                        <form action="process/ajax/support-processes.php?process=upload-file" method="post" class="upload-file">
                        	<div class="form-body">
                                <div class="form-group">
                                	<div id="upload-errors"></div>
                                    <label class="control-label">File Upload</label><br />
                                    <input type="file"  name="fileUpload" id="fileName" class="btn btn-default" style="float:left;"/>
                                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id'];?>" />
                                    <input type="hidden" name="current_files" value="<?php echo $notesFile;?>" />
                                    <input type="submit"  id="submit-btn" value="Upload File" class="btn btn-info" style="float:left;margin-left:10px;" /><div class="clearfix"></div>
                                </div><!--form-group-->
                            </div><!--form-body-->
                        </form>
                        <div class="clearfix"></div>
                        
                        <div id="load-files2">
                        
                        </div>
                        
                    </div>
                </div>
                <!-- END Internal Notes-->
                
                <!-- BEGIN Internal Notes-->
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption">
                        	<i class="icon-pencil"></i> Internal Notes and Documentation
                        </div>
                        
                    </div>
                    <div class="portlet-body form">
                        <form action="process/ajax/support-processes.php?process=15" method="post" class="upload-text-file">
                        	<div class="form-body">
                                <div class="form-group">
                                	<div id="upload-errors"></div>
                                    <label class="control-label">Notes File Upload</label><br />
                                    <input name="fileToUpload" id="textFileName" type="file" class="btn btn-default" style="float:left;"/>
                                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id'];?>" />
                                    <input type="hidden" name="current_files" value="<?php echo $notesFile;?>" />
                                    <input type="submit"  id="submit-btn" value="Upload File" class="btn btn-info" style="float:left;margin-left:10px;" /><div class="clearfix"></div>
                                    <small>You must click the "Save Notes" button after you upload your file.</small>
                                </div><!--form-group-->
                            </div><!--form-body-->
                        </form>
                        <div class="clearfix"></div>
                        
                        <!-- BEGIN FORM-->
                        <form action="process/ajax/support-processes.php?process=6" method="post" class="ajax-notes" name="support-notes">
                                <div class="form-body">
                                  	<div id="userErrorNotes">
                                    </div>
                                    	<div class="form-group" id="load-files">
                                        	<?php
											if(!empty($aNotesFiles)){
												echo '<ul>';
												foreach($aNotesFiles as $key=>$listFile){
													
													$aListFile = explode('_', $listFile);
													
													echo '<li><a href="files/support_documents/'.$listFile.'" target="_blank">'.$aListFile[3].' - ('.date('m/d/Y h:i a', $aListFile[2]).')</a><input type="hidden" name="note_file[]" value="'.$listFile.'" /></li>';
												}
												echo '</ul>';	
											}
											?>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="control-label">Internal Notes <span id="notes-help"></span></label> 
                                            <textarea name="notes" id="notes-editor" class="form-control" rows="12" style="resize:vertical;"><?php echo $ticket['notes'];?></textarea>
                                            <span class="help-block">Not Visible To Members</span>
                                        </div>
                                        
                                </div><!--form-body-->
                                
                                <div class="form-actions">
                                		<span style="display:block;float:left;">
                                            <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id']; ?>"  />
                                            
                                        	<input type="submit" value="Save Notes" id="submit-notes" class="btn btn-success" <?php //if($ticket['notes'] == ""){ echo 'style="display:none;"';}?>>
                                        </span>
                                        
                                        <?php //Form Processed by ajax | call file: includes/scripts/support-scripts.php | process file: process/ajax/support-processes.php ?>
                                </div>
                        </form>
                        <!-- END FORM--> 
                    </div>
                </div>
                <!-- END Internal Notes-->
                
                <!-- BEGIN Admin Controls-->
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption">
                        	<i class="icon-pencil"></i> Admin Controls
                        </div>
                        
                    </div>
                    <div class="portlet-body form">
                        
                        
                        
                        <!-- BEGIN FORM-->
                        <form action="process/ajax/support-processes.php?process=update-ticket" method="post" class="update-ticket" name="update-ticket">
                                <div class="form-body">
                                  	<div id="updateTicketError">
                                    </div>
                                    	
                                        <div class="form-group">
                                            <label  class="control-label">Ticket Type</label>
                                            <select name="ticket" onchange="loadProblemList('<?php echo $ticket['problem_type'];?>','<?php echo $ticket['stock_ticker'];?>');" id="ticket_type2" class="form-control" >
                                            	<?php
												$query = "
													SELECT *
													FROM ".$_SESSION['lists_options_table'] ."
													WHERE list_id=:list_id
													ORDER BY sequence ASC
												";
												
												//Fund Symbols
												try{
													$rsListOptions = $mLink->prepare($query);
													$aValues = array(
														':list_id' 	=> "1"
													);
													$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
													$rsListOptions->execute($aValues);
												}
												catch(PDOException $error){
													// Log any error
													file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
												}
												$listType = $ticket['ticket_type'];
												/*switch($ticketType){
													case "gh"	: $listType = '3';break;
													case 'ca'	: $listType = '2';break;
													case 'pp'	: $listType = '9';break;
													default		: $listType = '3';break;	
												}*/
												while($options = $rsListOptions->fetch(PDO::FETCH_ASSOC)) {
													if($options['disabled'] == "1"){
														$disabled = "disabled";	
													}else{
														$disabled = "";	
													}
													if($listType == $options['value']){
														$select = 'selected';
													}else{
														$select = '';	
													}
													
													echo '<option value="'.$options['value'].'" '.$disabled.' '.$select.'>'.$options['label'].'</option>';
												
												}
												?>
                                            </select>
                                            <?php /*?><span class="help-block">A block of help text.</span><?php */?>
                                        </div>
                                        
                                        <div id="ticket_list2">
                                        	<?php //loaded by ajax | call file: includes/scripts/support-scripts.php | process file: process/ajax/support-processes.php ?>
                                        </div>
                                        
                                        
                                        
                                        <div class="form-group">
                                        	<label class="control-label">Affected Funds</label><br  />
                                            <?php
											$query = " 
												SELECT fund_symbol, fund_id
												FROM ".$_SESSION['fund_table']."
												WHERE member_id=:member_id AND active='1'
												ORDER BY seq_id ASC
											";
											
											//Fund Symbols
											try{
												$rsGetFunds = $mLink->prepare($query);
												$aValues = array(
													':member_id' 	=> $ticket['member_id']
												);
												$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
												$rsGetFunds->execute($aValues);
											}
											catch(PDOException $error){
												// Log any error
												file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
											}
																						
											while($fund = $rsGetFunds->fetch(PDO::FETCH_ASSOC)) {
												
												if(in_array($fund['fund_id'], $aFundSymbols)){
													$checkedFund = 'checked';	
												}else{
													$checkedFund = '';	
												}
												
												echo '<input type="checkbox" name="fund_symbols[]" class="form-control" value="'.$fund['fund_id'].'" '.$checkedFund.'/> '.$fund['fund_symbol'].'&nbsp;';
											
											}
											?>
                                          
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="control-label">Ticket Status <span id="notes-help"></span></label> 
                                            <select name="ticket_status" class="form-control">
                                                <?php
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
													
													if($ticket['ticket_status'] == $status['value']){
														$selectStatus = 'selected';	
													}else{
														$selectStatus = '';	
													}
													
													echo '<option value="'.$status['value'].'" '.$selectStatus.'>'.$status['label'].'</option>';
												}
												?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="control-label">Assigned To <span id="notes-help"></span></label> 
                                            <select name="assigned" class="form-control">
                                                <option value="">Unassigned</option>
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
													if($admin['member_id'] == $ticket['assigned_to']){
														$selectAdmin = 'selected';	
													}else{
														$selectAdmin = '';	
													}
												
													echo '<option value="'.$admin['member_id'].'" '.$selectAdmin.'>'.$admin['name_first'].' '.$admin['name_last'].'</option>';
																									}
												?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="control-label">Priority<span id="notes-help"></span></label> 
                                            <select name="priority" class="form-control">
                                                <?php
												$query = "
													SELECT label, value
													FROM ".$_SESSION['lists_options_table']."
													WHERE list_id='4'
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
													
													if($ticket['priority'] == $status['value']){
														$selectPriority = 'selected';	
													}else{
														$selectPriority = '';	
													}
													
													echo '<option value="'.$status['value'].'" '.$selectPriority.'>'.$status['label'].'</option>';
												}
												?>
                                            </select>
                                        </div>
                                        
                                </div><!--form-body-->
                                
                                <div class="form-actions">
                                		<span style="display:block;float:left;">
                                            <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id']; ?>"  />
                                            <input type="hidden" name="old_assigned" value="<?php echo $ticket['assigned_to'];?>" />
                                            
                                        	<input type="submit" value="Update Ticket" id="submit-ticket-update" class="btn btn-success" <?php //if($ticket['notes'] == ""){ echo 'style="display:none;"';}?>>
                                        </span>
                                        
                                        <?php //Form Processed by ajax | call file: includes/scripts/support-scripts.php | process file: process/ajax/support-processes.php ?>
                                </div>
                        </form>
                        <!-- END FORM--> 
                    </div>
                </div>
                <!-- END Admin controls PORTLET-->
               
			   <?php
              	if($_SESSION['admin'] == 1){
					?>
                    <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption">
                        	<i class="icon-pencil"></i> Debug
                        </div>
                        
                    </div>
                    <div class="portlet-body">
                    <?php
					
					echo '<pre>';
					print_r($ticket);
					echo '</pre>';	
					
					?>
                    </div></div>
                    <?php
				}
				?>
               
            </div><!--END COLUMN-->
        </div><!--END ROW-->
       <!-- END PAGE CONTENT-->
        