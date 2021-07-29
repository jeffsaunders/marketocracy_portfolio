<?php
/*
Marketocracy Inc. | Beta Development
Admin campaigns

Supporting files:
	AJAX		- process/ajax/admin-campaigns-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/admin-campaigns.js.php
	HTML		- THIS DOCUMENT includes/pages/admin-campaigns.php
*/



?>
        <!-- START EMAIL CAMPAIGN MODAL-->
		<div class="modal fade" id="email-campaign" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-wide">
             <div class="modal-content">
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Create Email Campaign</span></h4>
                </div>
                
                
                	<form action="" method="post" name="create-campaign-form" class="create-campaign-form">
                        <div class="modal-body">
                                <div id="create-campaign-errors"></div>
                                <div class="form-body">
                                    
                                    <h4>Campaign Info</h4>
                                    <div class="form-group">
                                    	<label class="control-label">Campaign Name:*</label>
                                        <input type="text" class="form-control" name="camp_name" />
                                    </div><!--form-group-->
                                    
                                    <div class="form-group">
                                        <label class="control-label">Send To:*</label>
                                        <select name="sent-to" class="form-control">
                                            <option value='xx'>Select An Email List</option>
											<?php
											$query = "
												SELECT *
												FROM email_lists
											";
											try{
												$rsEmailList = $mLink->prepare($query);
												$aValues = array();
												$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
												$rsEmailList->execute($aValues);
											}
											
											catch(PDOException $error){
												// Log any error
												file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
											}
											while($list = $rsEmailList->fetch(PDO::FETCH_ASSOC)){
												echo '<option value="'.$list['list_id'].'">'.$list['list_name'].'</option>';	
											}
											?>
                                        </select>
                                    </div>
                                
                                    <div class="form-group">
                                        <label class="control-label">Select Template:*</label>
                                        <select name="email-template" class="form-control" id="select-template">
                                            <option value="x">Choose Template</option>
                                            <?php
											$query = "
												SELECT temp_title, temp_id
												FROM ".$_SESSION['email_templates_table']."
												WHERE camp_type='standard_campaign'
											";
											try{
												$rsTemplate = $mLink->prepare($query);
												$aValues = array();
												$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
												$rsTemplate->execute($aValues);
											}
											
											catch(PDOException $error){
												// Log any error
												file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
											}
											while($template = $rsTemplate->fetch(PDO::FETCH_ASSOC)){
												echo '<option value="'.$template['temp_id'].'">'.$template['temp_title'].'</option>';	
											}
											?>
                                        </select>
                                    </div>
                                    <hr />
                                    <h4>Email Info</h4>
                                    <div id="load-template-options"></div>
                                    
                                    <div class="form-group">
                                        <label class="control-label">Email Title*</label>
                                        <input type="text" class="form-control" name="email_title" value="<?php echo $template['temp_headline'];?>" />
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Subject Line*</label>
                                        <input type="text" class="form-control" name="email_subject" value="<?php echo $template['temp_headline'];?>" />
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Headline</label>
                                        <input type="text" class="form-control" name="email_headline" value="<?php echo $template['temp_headline'];?>" />
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Body*</label>
                                        <textarea type="text" class="form-control email-body" name="email_body" rows="20" ><?php echo $template['temp_body'];?></textarea>
                                    </div>
                                    
                                    
                                    <a href="javascript:void(0);" onclick="toggleID('format-help');">Toggle Formatting Help</a>
                                    <div class="note note-info" id="format-help" style="display:none;">
                                        <h3>Formatting Help</h3>
                                        <table width="100" cellpadding="5" cellspacing="5"  class="table">
                                            <thead>
                                                <tr>
                                                    <td><strong>Code</strong></td>
                                                    <td><strong>Result</strong></td>
                                                    <td><strong>Description</strong></td>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                <tr>
                                                    <td>[Link Text](http://example.com)</td>
                                                    <td><a href="http://example.com" target="_blank">Link Text</a><td>
                                                    <td>Trackable Link</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        
                                        
                                        <h3>Email Tags</h3>
                                        <table width="100" cellpadding="5" cellspacing="5"  class="table">
                                            <thead>
                                                <tr>
                                                    <td><strong>Tag</strong></td>
                                                    <td><strong>Result</strong></td>
                                                    <td><strong>Description</strong></td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>[NAME_FIRST]</td>
                                                    <td>Brandon<td>
                                                    <td>Adds recipients first name. Brackets must be in all caps.</td>
                                                </tr>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                                	
                                    <hr />
                                    <h4>Testing</h4>
                                    
                                	<div class="form-group">
                                        <label class="control-label">Send Test
                                        <input id="send-test-check" type="checkbox" class="form-control" name="send_test" value="1" /></label>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Test Email Address</label>
                                        <input type="text" class="form-control" name="test_email" value="" />
                                    </div>
                                	
                                    
                                    
                                </div><!--form-body-->
                            
                        </div><!--modal-body-->
                        
                        <div class="modal-footer">
                            <span id="email-processing-btn">
                                <input type="submit" value="Save Campaign" id="submit-email" class="btn btn-info" />
                            </span>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    
                    </form><!--send email-->
                
             </div>
             <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>        
        <!-- END EMAIL CAMPAIGN MODAL-->
        
        <!-- START EDIT EMAIL CAMPAIGN MODAL-->
		<div class="modal fade" id="edit-email-campaign" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-wide">
             <div class="modal-content">
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Edit Email Campaign</span></h4>
                </div>
                
                
                	<div id="load-edit-email"></div>
                
             </div>
             <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>        
        <!-- END EDIT EMAIL CAMPAIGN MODAL-->
        
        
        
        
        
         <!-- START EMAIL REPORT MODAL-->
		<div class="modal fade" id="report" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-wide">
             <div class="modal-content">
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Campaign Report</span></h4>
                </div>
                
                <div class="modal-body" id="load-campaign-report">
                	
                    <div class="profile-section">
                        <h4>Email Activity</h4>
                        
                        <ul class="stat-list">
                            
                            <li>Opened: <span>20</span></li>
                            <li>Clicked Through: <span>15</span></li>
                            <li>Open Rate: <span>48%</span></li>
                            <li>Click-to-Open Rate: <span>75%</span></li>
                        </ul>
                        
                        <div class="clearfix"></div>
                        
                       
                        
                        
                    </div><!--profile-section-->
                    
                    <?php /*?><div class="profile-section">
                        
                        <h4>Email Delivery</h4>
                        
                        <ul class="stat-list">
                            <li>Sent: <span>45</span></li>
                            <li>Delivered: <span>38</span></li>
                            <li>Bounced: <span>7</span></li>
                            <li>Unsubscribed: <span>1</span></li>
                            
                        </ul>
                        
                        <div class="clearfix"></div>
                        
                        
                    </div><!--profile-section--><?php */?>
                    
                </div><!--modal-body-->
                
                <div class="modal-footer">
                    
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
                	
                
             </div>
             <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>        
        <!-- END EMAIL CAMPAIGN MODAL-->
        
        <!-- START SCHEDULE EMAIL CAMPAIGN MODAL-->
		<div class="modal fade" id="send-campaign" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-wide">
             <div class="modal-content">
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Send/Schedule Campaign</span></h4>
                </div>
                
                
                	
                    <div id="load-send-email"></div>
                    
                    
                    
                    
                
                	
                
             </div>
             <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>        
        <!-- END EMAIL CAMPAIGN MODAL-->
        
        <!-- START SCHEDULE EMAIL CAMPAIGN MODAL-->
		<div class="modal fade" id="preview-email" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-wide">
             <div class="modal-content">
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Email Campaign Preview</span></h4>
                </div>
                
                <div class="modal-body">
                	
                    <div id="load-email-preview"></div>
                    
                    
                    
                    
                </div><!--modal-body-->
                
                <div class="modal-footer">
                    
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
                	
                
             </div>
             <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>        
        <!-- END EMAIL CAMPAIGN MODAL-->
            
         
            <!-- BEGIN PAGE CONTENT-->
            
                
            <div class="row">
                <div class="col-md-12">
                    
                    <div class="tabbable tabbable-custom">
                        
                        <?php include('includes/nav-admin-tabs.php');?>

                        <div class="tab-content">
                        
                            
                            
                            <div class="tab-pane active" id="tab_1">
                                
                                <div class="portlet">
                                    <div class="portlet-title">
                                        <div class="caption">Email Campaigns</div>
                                        <div class="actions">
                                            <a class="btn btn-info btn-xs" href="#email-campaign" data-toggle="modal">New Campaign</a>
                                        </div><!--tools-->
                                    </div><!--portlet-title-->
                                    <div class="portlet-body" style="background: #FAFAFA;">
                                        
                                        
                                        <table class="table table-striped table-bordered table-hover table-full-width" id="campaigns_table">
                                            <thead>
                                                <tr>
                                                    <th width="1%">#</th>
                                                    <th>Name</th>
                                                    <th>Subject</th>
                                                    <th>List</th>
                                                    <th>Last Modified</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="load-emails">
                                                <?php //content loaded by process/ajax/action-center-processes.php ?>
                                            </tbody>
                                        </table>
                                    
                                    </div><!--portlet-body-->
                                </div><!--end-portlet-->
                                
                                
                            </div><!--END TAB 2-->
                            
                            
                        
                        
                        </div><!--tab-content-->
                    </div><!--tabbable tabbable-custom-->
                    
                </div><!--col-md-12-->
            </div><!--row-->
            <!-- END PAGE CONTENT-->    
   