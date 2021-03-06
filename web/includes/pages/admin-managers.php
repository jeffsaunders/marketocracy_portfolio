<?php
/*
Marketocracy Inc. | Beta Development
Admin Manager Composite Data

Supporting files:
	AJAX		- process/ajax/admin-managers-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/admin-managers.js.php
	HTML		- THIS DOCUMENT includes/pages/admin-managers.php
*/



?>
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
          	<!-- START ARTICLES MODAL-->
            <div class="modal fade" id="articles" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-wide">
                 <div class="modal-content">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Add Article</span></h4>
                    </div>
                    
                    
                        <form action="" method="post" name="add-article-form" class="add-article-form">
                            <div class="modal-body">
                                    
                                    <div class="form-body">
                                        
                                        <h4>Article Info</h4>
                                        
                                        <div id="add-article-error"></div>
                                        
                                        <div class="form-group">
                                        	<label class="form-group">Manager*</label>
                                            <select name="manager" id="manager-select" class="form-control">
                                            	<option value="xx">Select A Manager</option>
                                                <option value="xx" disabled>__________________________</option>
                                                <option value="29">Ken Kam</option>
                                                <option value="xx" disabled>__________________________</option>
                                                <?php
												
												$query = "
													SELECT m.member_id, m.name_first, m.name_last, m.username
													FROM ".$_SESSION['members_table']." AS m
													INNER JOIN ".$_SESSION['members_flags_table']." AS mf ON mf.member_id=m.member_id
													WHERE mf.composite='1'
													ORDER BY name_last ASC
												";
												$query = "
													SELECT m.member_id, m.name_first, m.name_last, m.username
													FROM ".$_SESSION['members_table']." AS m, ".$_SESSION['subscriptions_table']." as st
													WHERE st.product_id='10' AND st.member_id=m.member_id
													ORDER BY m.name_last
												";
												try{
													$rsGetManagers = $mLink->prepare($query);
													$aValues = array();
													$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
													$rsGetManagers->execute($aValues);
												}
												catch(PDOException $error){
													// Log any error
													file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
												}
												while($managers = $rsGetManagers->fetch(PDO::FETCH_ASSOC)){
													
													
													?>
                                                    <option value="<?php echo $managers['member_id'];?>"><?php echo $managers['name_first'];?> <?php echo $managers['name_last'];?> (<?php echo $managers['username'];?>)</option>
                                                    <?php
													
												}
												?>
                                            </select>
                                        </div>
                                                             
                                        <div class="form-group">
                                            <label class="control-label">Article Type*</label>
                                            <select name="article_type" id="article_type" class="form-control">
                                                <?php echo printList($mLink, '12', 'xx');?>
                                            </select>
                                        </div>
                                    
                                        <div id="load-article-fields">
                                        
                                        </div>
                                        
                                        <?php if($_SESSION['admin'] == '1' && 2==1){ ?>
                                        <hr />
                                        
                                        <div class="form-group">
                                        	<label class="control-label"><input type="checkbox" class="form-control gen_camp_btn" name="gen_camp" value="1"  /> Generate Campaign for this Article</label><br /><p class="help-text">Tick the checkbox to automatically generate an email campaign.</p>
                                        </div>
                                        
                                        
                                        <div class="form-group show-camp-list"  style="display:none;">
                                            <label class="control-label">Send To:*</label>
                                            <select name="camp_list" class="form-control">
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
                                        <?php }//end if admin == 1 ?>
                                        
                                        
                                        
                                    </div><!--form-body-->
                                
                            </div><!--modal-body-->
                            
                            <div class="modal-footer">
                                <span id="article-processing-btn">
                                    <input type="submit" value="Add Article" id="submit-article" class="btn btn-info" />
                                </span>
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            </div>
                        
                        </form><!--send email-->
                    
                 </div>
                 <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>        
            <!-- END ARTICLES MODAL-->
            
            <!-- START EDIT ARTICLE MODAL-->
		<div class="modal fade" id="edit-article" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-wide">
             <div class="modal-content">
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Edit Article</span></h4>
                </div>
                
                
                	<form action="" method="post" name="edit-article-form" class="edit-article-form">
                        <div class="modal-body">
                                <div id="create-campaign-errors"></div>
                                <div class="form-body">
                                    
                                    <h4>Article Info</h4>
                                    
                                    <div id="edit-article-error"></div>
                                                         
                                    <div id="load-edit-article-fields">
                                    
                                    </div>
                                	
                                </div><!--form-body-->
                            
                        </div><!--modal-body-->
                        
                        <div class="modal-footer">
                            <span id="edit-article-processing-btn">
                                <input type="submit" value="Save Changes" id="submit-article" class="btn btn-info" />
                            </span>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    
                    </form><!--send email-->
                
             </div>
             <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>        
        <!-- END EDIT ARTICLE MODAL-->
            
            <!-- BEGIN RUN LIVE PRICE-->
            <div class="modal" id="composite-data" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-full">
                 <div class="modal-content">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Composite Data</h4>
                    </div>
                    
                    
                        <div class="modal-body" id="load-composite-data-form">
                                
                            
                        </div><!--modal-body-->
                        
                        <div class="modal-footer">
                            
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    
                    
                 </div>
                 <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- END RUN LIVE PRICE-->
            
            <!-- BEGIN RUN LIVE PRICE-->
            <div class="modal" id="email-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-wide">
                 <div class="modal-content">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Email Managers</h4>
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
                                            <option value="managers">All Managers</option>
                                        </select>
                                    </div>
                                
                                    <div class="form-group">
                                        <label class="control-label">Select Template:*</label>
                                        <select name="email-template" class="form-control" id="select-template">
                                            <option value="x">Choose Template</option>
                                            <?php
											$query = "
												SELECT temp_title, temp_id, email_id
												FROM ".$_SESSION['email_templates_table']."
												WHERE camp_type='manager_update'
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
												
												if($template['email_id'] == '5'){
													$selected = "selected";	
												}else{
													$selected = "";
												}
												
												echo '<option value="'.$template['temp_id'].'" '.$selected.'>'.$template['temp_title'].'</option>';	
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
                                        <label class="control-label">Headline*</label>
                                        <input type="text" class="form-control" name="email_headline" value="<?php echo $template['temp_headline'];?>" />
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Body*</label>
                                        <textarea type="text" class="form-control email-body" name="email_body" rows="10" ><?php echo $template['temp_body'];?></textarea>
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
                                <input type="submit" value="Send Email" id="submit-email" class="btn btn-info" />
                            </span>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    
                    </form><!--send email-->
                        
                        
                    
                    
                 </div>
                 <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- END RUN LIVE PRICE-->
            
         
            <!-- BEGIN PAGE CONTENT-->
            
                
            <div class="row">
                <div class="col-md-12">
                    
                    <div class="tabbable tabbable-custom">
                        
                        <?php include('includes/nav-admin-tabs.php');?>

                        <div class="tab-content">
                        
                            
                            
                            <div class="tab-pane active" id="tab_1">
                                
                                <div class="portlet">
                                    <div class="portlet-title">
                                    	<div class="caption"><i class="icon-reorder"></i>Actions</div>
                                      	<div class="tools" id="collapse-btn">
                                        	<a href="javascript:;" class="collapse"></a>
                                        	<!--<a href="javascript:;" class="reload"></a>-->
                                      	</div><!--tools-->
                                    </div><!--portlet-title-->
                                    <div class="portlet-body" id="collapse-portlet">
                                    
                                    	<a href="#email-modal" data-toggle="modal" class="btn btn-info">Email Managers</a>
                                    	
                                        <div class="row">
                                        	
                                            <div class="col-md-12">
                                            	
                                                <div class="table-responsive">
                                                    <table class="table table-striped table-bordered table-hover" id="campaign-table">
                                                        <thead>
                                                            <tr>
                                                            	<th>#</th>
                                                                <th>Campaign</th>
                                                                <th>Subject</th>
                                                                <th>Sent On</th>
                                                                <th>Actions</th>
                                                                
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                        	<?php
															$query = "
			SELECT *
			FROM ".$_SESSION['email_campaigns_table']."
			WHERE deleted='0' AND camp_type='manager_update'
		";
		try{
			$rsGetCamp = $mLink->prepare($query);
			$aValues = array();
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetCamp->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		
		echo $error;
		
		$cnt = 0;
		$aJson = array();
		while($camp = $rsGetCamp->fetch(PDO::FETCH_ASSOC)){
		
			$cnt++;	
			
			$campID			= $camp['camp_id'];
			$scheduled 		= $camp['schedule_timestamp'];
			$campStatus		= $camp['camp_status'];
			
			if($scheduled == NULL){
				$scheduled = 'N/A';
			}else{
				$scheduled = date('m/d/Y h:i a', $scheduled);
			}	
			
			$timestamp = $camp['timestamp'];
			
			switch($campStatus){
				
				case 'sent':
					$actionBtns = '
						
						<button type="button" data-toggle="modal" data-target="#report" onclick="loadCampaignReport(\''.$campID.'\');" class="btn btn-warning btn-sm"><i class="icon-file"></i>&nbsp;&nbsp;View Report</button>
					';
				break;
				
				case 'draft':
					$actionBtns = '
						<button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#preview-email" onclick="loadEmailPreview(\''.$campID.'\');"><i class="icon-eye-open"></i> Preview</button>
						<button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#edit-email-campaign" onclick="loadEditEmail(\''.$campID.'\');"><i class="icon-pencil"  ></i> Edit</button> 
						<button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#send-campaign" onclick="loadSendEmail(\''.$campID.'\');"><i class="icon-arrow-right"></i> Send</button>
					';
				break;
				
				case 'sending':
					$actionBtns = '
						<button type="button" class="btn btn-default btn-sm" disabled>Sending...</button>
					';
				break;
				
			}
			
			echo '
				<tr>
					<td>'.$cnt.'</td>
					<td>'.$camp['camp_name'].'</td>
					<td>'.$camp['email_subject'].'</td>
					<td>'.date('m/d/Y h:i a', $camp['sent_timestamp']).'</td>
					<td>'.$actionBtns.'</td>
				</tr>
			';
		}
															?>
                                                        </tbody>
                                            		</table>
                                            </div><!--col-md-12-->
                                            
                                        </div><!--row-->
                                        
                                    </div><!--portlet-body-->
                                </div><!--portlet-->
                                
                                <div class="portlet">
                                    <div class="portlet-title">
                                    	<div class="caption"><i class="icon-reorder"></i>Manager Composite Data</div>
                                      	<div class="tools" id="collapse-btn">
                                        	<a href="javascript:;" class="collapse"></a>
                                        	<!--<a href="javascript:;" class="reload"></a>-->
                                      	</div><!--tools-->
                                    </div><!--portlet-title-->
                                    <div class="portlet-body" id="collapse-portlet">
                                    	
                                        <div class="row">
                                        	<div class="col-md-12">
                                                
                                                <div class="table-responsive">
                                                    <table class="table table-striped table-bordered table-hover" id="manager-table">
                                                        <thead>
                                                            <tr>
                                                                <th>Manager</th>
                                                                <th>Username</th>
                                                                <th>Fund Symbol</th>
                                                                <th>Fund Name</th>
                                                                <th>Composite</th>
                                                                <th>AUM</th>
                                                                <th>Earnings</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            
                                                            $query = "
                                                                SELECT mf.*,m.*
                                                                FROM members_fund AS mf
                                                                INNER JOIN members AS m ON m.member_id=mf.member_id
                                                                WHERE mf.composite_fund='1'
                                                            ";
                                                            
															$query = "
																SELECT mf.*,m.*
																FROM composite_cassatt_list ccl
																LEFT JOIN members_fund mf ON mf.fund_id=ccl.fund_id
																LEFT JOIN members m ON mf.member_id=m.member_id
																WHERE ccl.active='1'
															";
                                                            try{
                                                                $rsGetFunds = $mLink->prepare($query);
                                                                $aValues = array();
                                                                $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                                                $rsGetFunds->execute($aValues);
                                                            }
                                                            catch(PDOException $error){
                                                                // Log any error
                                                                file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                                            }
                                                            
                                                            $aumTotal = 0;
															$feeTotal = 0;
															$managerCnt	= 0;
                                                            
                                                            while($funds = $rsGetFunds->fetch(PDO::FETCH_ASSOC)){
                                                                
																$managerCnt++;
																
                                                                $memberID				= $funds['member_id'];
                                                                $compositeDisclosure	= $funds['composite_disclosure'];
                                                                
                                                                if($compositeDisclosure != NULL){
                                                                    $showComposite = '<a class="btn btn-xs btn-default" href="'.$_SESSION['site_root'].'/files/disclosures/'.$compositeDisclosure.'" target="_blank">View Composite <i class="icon-external-link"></i></a>';	
                                                                }else{
                                                                    $showComposite = 'N/A';	
                                                                }
																
																$query = "
																	SELECT aum
																	FROM members_fund_composite
																	WHERE fund_id=:fund_id
																	ORDER BY unix_date DESC
																	LIMIT 1
																
																";
																try{
																	$rsAUM = $mLink->prepare($query);
																	$aValues = array(
																		':fund_id' => $funds['fund_id']
																	);
																	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
																	$rsAUM->execute($aValues);
																}
																catch(PDOException $error){
																	// Log any error
																	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
																}
																$aum = $rsAUM->fetch(PDO::FETCH_ASSOC);
																
																$AUM = $aum['aum'];
																
																if($AUM <= 250000){
																	#100k - 250k
																	$fee = .015;
																}elseif($AUM <= 500000){
																	#250k - 500k
																	$fee = .014;	
																}elseif($AUM <= 1000000){
																	#500k - 1m
																	$fee = .013;	
																}elseif($AUM <= 2500000){
																	#1m - 2.5m
																	$fee = .011;	
																}else{
																	# > 2.5m
																	$fee = .01;	
																}
																
                                                                $aumTotal	= $aumTotal + $AUM;
																$feeAUM		= ($AUM*$fee);
																$feeTotal	= $feeTotal + $feeAUM;
                                                                ?>
                                                                <tr>
                                                                    <td><a href="?page=20-00-00-003&member=<?php echo $memberID;?>" target="_blank"><?php echo $funds['name_first'];?> <?php echo $funds['name_last'];?></a></td>
                                                                    <td><?php echo $funds['username'];?></td>
                                                                    <td><?php echo $funds['fund_symbol'];?></td>
                                                                    <td><?php echo $funds['fund_name'];?></td>
                                                                    <td><?php echo $showComposite;?></td>
                                                                    <td>$<?php echo number_format($AUM,2,'.',',');?></td>
                                                                    <td>$<?php echo number_format($feeAUM,2,'.',',');?></td>
                                                                    <td>
                                                                        <a href="#composite-data" data-toggle="modal" class="btn btn-xs btn-info" onclick="loadCompositeData('<?php echo $funds['fund_id'];?>');">View Composite Data</a>
                                                                        <a href="https://portfolio.marketocracy.com/process/ajax/admin-switch-user.php?member=<?php echo $memberID;?>&admin=<?php echo $_SESSION['member_id'];?>&toggle=admin-to-user&gopage=01-00-00-003&gofund=<?php echo $funds['fund_id'];?>&returnPage=20-00-00-007" class="btn btn-xs btn-info">Action Center</a> 
                                                                        <a href="https://<?php echo $funds['username'];?>.mytrackrecord.com/" class="btn btn-xs btn-info" target="_blank">Public Page <i class="icon-external-link"></i></a>
                                                                        
                                                                    </td>
                                                                </tr>
                                                                <?php
                                                                
                                                            }
                                                            
                                                            ?>  
                                                            
                                                        </tbody>
                                                    </table>
                                                    
                                                    <?php
													
													$monthlyBurnRate 	= 70000;
													$annualBurnRate		= ($monthlyBurnRate * 12);
													
													$assetsToProfit		= $annualBurnRate - $feeTotal;
													
													?>
                                                    
                                                    <table class="table table-striped table-bordered table-hover">
                                                    	<thead>
                                                            <tr>
                                                                <th># Managers</th>
                                                                <th>Total AUM</th>
                                                                <th>Earned</th>
                                                                <th>Burn Rate</th>
                                                                <th>Assets to Profit</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                        	<tr>
                                                        		<td><?php echo $managerCnt;?></td>
                                                                <td>$<?php echo number_format($aumTotal,2,'.',',');?></td>
                                                                <td>$<?php echo number_format($feeTotal,2,'.',',');?></td>
                                                                <td>$<?php echo number_format($annualBurnRate,2,'.',',');?></td>
                                                                <td>$<?php echo number_format($assetsToProfit,2,'.',',');?></td>
                                                        	</tr>
                                                        </tbody>
                                                    </table>
                                                    <p><strong>Total AUM:</strong> </p>
                                                </div><!--table-responsive-->    
                                                
                                    		</div><!--col-->
                                        </div><!--row-->
                                        
                                        
                                        
                                    </div><!--END PORTLET BODY-->
                                </div><!--END PORTLET-->
                                
                                <div class="portlet">
                                    <div class="portlet-title">
                                        <div class="caption">Articles</div>
                                        <div class="actions">
                                            <a class="btn btn-info btn-xs" href="#articles" data-toggle="modal">New Article</a>
                                        </div><!--tools-->
                                    </div><!--portlet-title-->
                                    <div class="portlet-body" style="background: #FAFAFA;">
                                        
                                        
                                        
                                        <table class="table table-striped table-bordered table-hover table-full-width" id="articles_table">
                                            <thead>
                                                <tr>
                                                    <th width="1%">#</th>
                                                    <th>Manager</th>
                                                    <th>Article</th>
                                                    <th>Published</th>
                                                    <th>Type</th>
                                                    <th>Last Modified</th>
                                                    <th>Status</th>
                                                    
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="load-articles">
                                                <?php /*?><tr>
                                                    <td>1</td>
                                                    <td><a href="javascript:void(0);">Apple Takes A Dive</a></td>
                                                    <td>Link</td>
                                                    <td>9/14/15 3:15 PM</td>
                                                    <td><span class="label label-success">Published</span></td>
                                                    <td>14</td>
                                                    <td><a href="javascript:void(0);"><i class="icon-edit"></i> Edit</a></td>
                                                </tr><?php */?>
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
   
