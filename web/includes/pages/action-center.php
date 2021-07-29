<?php
/*
Marketocracy Inc. |
Action Center

Supporting files:
	AJAX		- process/ajax/action-center-processes.php
	JAVASCRIPT	- includes/scripts/action-center.js.php
	HTML		- THIS DOCUMENT includes/pages/action-center.php
*/

$tab = $_REQUEST['tab'];

$aTrackInfo 	= memberTracker($mLink, $_SESSION['member_id']);

$aViewData		= pageViews($mtrLink, '04-00-00-001', $_SESSION['member_id'], 'all');

$aFundAssets	= fundAssets($mLink, $_SESSION['member_id']);

$query = "
	SELECT *
	FROM ".$_SESSION['mtr_subdomain_table']."
	WHERE member_id=:member_id
";
try {
	$rsSubdomain = $mLink->prepare($query);
	$aValues = array(
		':member_id'	=> $_SESSION['member_id']
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
	$rsSubdomain->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}
echo $error;
$getSubdomain = $rsSubdomain->fetch(PDO::FETCH_ASSOC);

$subdomain = $getSubdomain['subdomain'];

if($subdomain == ''){
	$subdomain = $_SESSION['username'];	
}

$_SESSION['sub_domain'] = $subdomain;

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
                                            <option value="trackers">Trackers List</option>
                                            <option value="family">Family List</option>
                                            <option value="friends">Friends List</option>
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
												WHERE camp_type='manager_campaign'
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
                                        <label class="control-label">Headline*</label>
                                        <input type="text" class="form-control" name="email_headline" value="<?php echo $template['temp_headline'];?>" />
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Body*</label>
                                        <textarea type="text" class="form-control" name="email_body" rows="10"><?php echo $template['temp_body'];?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="control-label">Closing*</label>
                                        <textarea class="form-control" name="email_closing"><?php echo $template['temp_closing'];?></textarea>
                                    </div>
                                    
                                    <a href="javascript:void(0);" onclick="toggleID('format-help');">Toggle Formatting Help</a>
                                    <div class="note note-info" id="format-help" style="display:none;">
                                        <h3>Formatting Help</h3>
                                        <table width="100" cellpadding="5" cellspacing="5"  class="table">
                                            <thead>
                                                <tr>
                                                    <td><strong>Code</strong></td>
                                                    <td><strong>Result</strong></td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>&lt;strong&gt;Bold Text&lt;/strong&gt;</td>
                                                    <td><strong>Bold Text</strong><td>
                                                </tr>
                                                <tr>
                                                    <td>&lt;em&gt;Italic Text&lt;/em&gt;</td>
                                                    <td><em>Italic Text</em><td>
                                                </tr>
                                                <tr>
                                                    <td>&lt;a href="FULL_URL" target="_blank"&gt;Link&lt;/a&gt;</td>
                                                    <td><a href="FULL_URL" target="_blank">Link</a><td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        
                                        
                                        <h3>Email Tags</h3>
                                        <table width="100" cellpadding="5" cellspacing="5"  class="table">
                                            <thead>
                                                <tr>
                                                    <td><strong>Tag</strong></td>
                                                    <td><strong>Result</strong></td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>[NAME_FIRST]</td>
                                                    <td>Brandon<td>
                                                </tr>
                                                <tr>
                                                    <td>[STOCK_NAME]</td>
                                                    <td>Apple Inc.<td>
                                                </tr>
                                                <tr>
                                                    <td>[STOCK_SYMBOL]</td>
                                                    <td>AAPL<td>
                                                </tr>
                                                
                                                <tr>
                                                    <td>[TOPIC_LINK]</td>
                                                    <td>Inserts url path of topic link, must be used with an &lt;a&gt; tag.</td>
                                                </tr>
                                                <tr>
                                                    <td>[CLICK_CODE]</td>
                                                    <td>Code to track links to marketocracy, must be put on end of URL with the use of an &lt;a&gt; tag.</td>
                                                </tr>
                                            </tbody>
                                        </table>
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
                                <div id="create-campaign-errors"></div>
                                <div class="form-body">
                                    
                                    <h4>Article Info</h4>
                                    
                                    <div id="add-article-error"></div>
                                                         
                                    <div class="form-group">
                                        <label class="control-label">Article Type*</label>
                                        <select name="article_type" id="article_type" class="form-control">
                                            <?php echo printList($mLink, '12', 'xx');?>
                                        </select>
                                    </div>
                                
                                    <div id="load-article-fields">
                                    
                                    </div>
                                	
                                    
                                    
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
        
        
        <!-- START PAGE VIEWS MODAL-->
		<div class="modal fade" id="page-view-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-wide">
             <div class="modal-content">
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Page Views</span></h4>
                </div>
                
                <div class="modal-body">
                <div id="page-view-modal-container">Loading</div>
                </div><!--modal-body-->
                
             </div>
             <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>        
        <!-- END PAGE VIEWS MODAL-->
        
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
        
        <!-- START EMAIL CAMPAIGN MODAL-->
		<div class="modal fade" id="report" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-wide">
             <div class="modal-content">
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Campaign Report</span></h4>
                </div>
                
                <div class="modal-body">
                	
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
                    
                    <div class="profile-section">
                        
                        <h4>Email Delivery</h4>
                        
                        <ul class="stat-list">
                            <li>Sent: <span>45</span></li>
                            <li>Delivered: <span>38</span></li>
                            <li>Bounced: <span>7</span></li>
                            <li>Unsubscribed: <span>1</span></li>
                            
                        </ul>
                        
                        <div class="clearfix"></div>
                        
                        
                    </div><!--profile-section-->
                    
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
        
        <!-- START EMAIL CAMPAIGN MODAL-->
		<div class="modal fade" id="send-campaign" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-wide">
             <div class="modal-content">
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Send/Schedule Campaign</span></h4>
                </div>
                
                <div class="modal-body">
                	
                    <h4>Send Now / Schedule</h4>
                    
                    
                    
                    
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
        <div class="row profile">
            <div class="col-md-12">
            	
                <?php /*?><div class="alert alert-warning">
                    <h4>Page Under Construction</h4>
                    <p>Note: This page is still under contruction, please check back later.</p>
                </div><?php */?>
                
                <?php 
				/*if($_SESSION['admin'] == '1'){ 
					echo '<pre>';
					print_r($aFundAssets);
					echo '</pre>';
				}*/
				?>
            
                <!--BEGIN TABS-->
                <div class="tabbable tabbable-custom">
                    <?php include('includes/nav-action-center.php');?>
                    <div class="tab-content">
                      	
                        <?php if($tab == ''){?>
                    		
                            <div class="tab-pane <?php if($tab == ""){?>active<?php } ?>" id="<?php echo $tab;?>" >
                            	<div class="row">
                                	
                                    <div class="col-md-8">
                                    	
                                        
                                        
                                        <div class="portlet tabbable">
                                            <div class="portlet-title">
                                            	<div class="caption"><i class="icon-reorder"></i>MyTrackRecord Setup</div>
                                            </div><!--portlet-title-->
                                            <div class="portlet-body">
                                                <div class="tabbable portlet-tabs">
                                                    
                                                    <ul class="nav nav-tabs">
                                                    	<li id="fund-strat-tab"><a href="#fund-strat_tab3" data-toggle="tab"><span class="badge badge-info">3</span> Fund Strategy</a></li>
                                                    	<li id="invest-strat-tab"><a href="#invest-strat_tab2" data-toggle="tab"><span class="badge badge-info">2</span> Investment Strategy</a></li>
                                                    	<li id="headshot-tab" class="active"><a href="#headshot_tab1" data-toggle="tab"><span class="badge badge-info"><strong>1</strong></span> Headshot</a></li>
                                                    </ul>
                                                    
                                                    <div class="tab-content">
                                                        <div class="tab-pane active" id="headshot_tab1">
                                                        
                                                        	<div class="note note-info">
                                                            	<p>It is important that you represent yourself in a professional manner.</p>
                                                            </div>
                                                            
                                                            <h3 class="form-section">Upload Your Headshot</h3>
                                                            <div id="output"></div>
                                                            
                                                            <div style="float:left;margin-right:10px;">
                                                                <h5>Current Image</h5>
                                                                <span class="replace-current-image">
                                                                <img src="<?php echo get_member($mLink, $_SESSION['member_id'], 'profileImage');?>" class="img-responsive" alt="<?php echo $_SESSION['fullname'];?>" /> 
                                                                </span>
                                                            </div>
                                                            <form action="process/ajax/user-settings-processes.php?process=2" method="post" enctype="multipart/form-data" id="MyImageForm">
                                                                <div id="new-image" style="float:left;"></div>
                                                                <div style="clear:both;"></div>
                                                                
                                                                <input type="hidden" id="crop_x" name="x" />
                                                                <input type="hidden" id="crop_y" name="y" />
                                                                <input type="hidden" id="crop_w" name="w" />
                                                                <input type="hidden" id="crop_h" name="h" />
                                                                
                                                                <div style="border-top:1px solid #CCC;margin-top:10px;padding-top:10px;">
                                                                    
                                                                    <div class="btn-group">
                                                                    <input name="image_file" id="imageInput" type="file" class="btn btn-default" style="float:left;"/>
                                                                    <input type="submit"  id="submit-btn" value="Upload Image" class="btn btn-info" style="float:left;" />
                                                                    </div>
                                                                    <img src="images/ajax-loading.gif" id="loading-img" style="display:none;" alt="Please Wait"/>
                                                                    <br /><br /><p>Note: Uploading images that contain obscene gestures, nudity, or offensive content is prohibited. Treat this like you would a professional profile.</p>
                                                                </div>
                                                            </form>
                                                            <br />
                                                            <br />
                                                            
                                                            <button type="button" class="btn btn-success" onclick="saveProfilePic();">Save Picture</button>
                                                            
                                                            <div class="mtr-setup-nav">
                                                            	<a href="#invest-strat_tab2" data-toggle="tab" onClick="changeTab('headshot-tab','invest-strat-tab');" class="btn btn-info mtr-nav-right"> Investment Strategy <i class="icon-arrow-right"></i></a>	
                                                                <div class="clearfix"></div>
                                                            </div>
                                                        </div><!--tab-pane - profile headshot-->
                                                        
                                                        
                                                        <div class="tab-pane" id="invest-strat_tab2">
                                                            
                                                            <?php
															$query = "
																SELECT m.*, p.*
																FROM ".$_SESSION['members_table']." as m
																INNER JOIN ".$_SESSION['members_profile_table']." as p ON p.member_id=m.member_id 
																WHERE m.member_id=:member_id
															";
															
															try{
																$rsMember = $mLink->prepare($query);
																$aValues = array(
																	':member_id' 	=> $_SESSION['member_id']
																);
																$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
																$rsMember->execute($aValues);
															}
															catch(PDOException $error){
																// Log any error
																file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
															}
															
															$member = $rsMember->fetch(PDO::FETCH_ASSOC);
															?>
                                                            
                                                            <div class="note note-info">
                                                            	<p>Please provide your investment style. As well as any other information you feel is relevant to share. LinkedIn Website, personal blog, etc.</p>
                                                            </div>
                                                            
                                                            <form role="form" action="" method="post" name="save-profile" id="save-profile">
   																
                                                                <div class="form-group" id="summary">
                                                                    <label class="control-label">Investment Strategy</label>
                                                                    <textarea class="form-control" rows="5" id="about_me" name="about_me" placeholder="Please write about your investment strategy."><?php echo $member['about_me'];?></textarea>
                                                                </div>
                                                                
                                                                
                                                                <div class="form-group">
                                                                    <label class="control-label">Occupation</label>
                                                                    <input type="text" name="occupation" id="occupation" class="form-control" value="<?php echo $member['occupation'];?>" />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label class="control-label">Personal Website</label>
                                                                    <input type="text" name="personal_site" placeholder="http://www.mywebsite.com" class="form-control" value="<?php echo $member['personal_site'];?>" />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label class="control-label">LinkedIn URL</label>
                                                                    <input type="text" name="linkedin_url" placeholder="" class="form-control" value="<?php echo $member['linkedin'];?>" />
                                                                </div>
                                                                
                                                                <div class="margin-top-10">
                                                                    <button type="submit" class="btn btn-success" id="submit-profile">Save Changes</button>
                                                                </div>
                                                            </form>
                                                            
                                                            <div class="mtr-setup-nav">
                                                            	<a href="#headshot_tab1" data-toggle="tab" onClick="changeTab('invest-strat-tab','headshot-tab');saveProfileInfo();" class="btn btn-info mtr-nav-left"><i class="icon-arrow-left"></i> Update Headshot</a>
                                                                <a href="#fund-strat_tab3" data-toggle="tab" onClick="changeTab('invest-strat-tab','fund-strat-tab');saveProfileInfo();" class="btn btn-info mtr-nav-right"> Fund Strategy <i class="icon-arrow-right"></i></a>	
                                                                <div class="clearfix"></div>
                                                            </div>
                                                            
                                                        </div><!--tab-pane-->
                                                        <div class="tab-pane" id="fund-strat_tab3">
                                                            
                                                            <div class="note note-info">
                                                            	<p>Please provide your fund strategy for each fund you wish to display on mytrackrecord.com. If you do not want a particular fund to be displayed, simply toggle the display to "No".</p>
                                                            </div>
															
															<?php
															$query = "
																SELECT * 
																FROM ".$_SESSION['fund_table']."
																WHERE member_id=:member_id AND active='1'
															";
															try{
																$rsGetFunds = $mLink->prepare($query);
																$aValues = array(
																	':member_id'	=> $_SESSION['member_id']
																);
																$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
																$rsGetFunds->execute($aValues);
															}
															catch(PDOException $error){
																// Log any error
																file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
															}
															//echo $preparedQuery;
															while($fund = $rsGetFunds->fetch(PDO::FETCH_ASSOC)){
																
																?>
																<div class="portlet">
																	<div class="portlet-title">
																		<div class="caption"><?php echo $fund['fund_symbol'];?></div>
																		<div class="actions">
																			<?php /*?><a class="btn btn-info btn-xs" href="javascript:;">New Article</a><?php */?>
																		</div><!--tools-->
																	</div><!--portlet-title-->
																	<div class="portlet-body" style="padding:10px;">
																		
																		<form action="" method="post" name="update-fund-<?php echo $fund['fund_id'];?>" class="update-fund-<?php echo $fund['fund_id'];?>">
																			
																			<div id="fund-strat-msg-<?php echo $fund['fund_id'];?>"></div>
																			
																			<div class="form-group">
																				<label  class="col-md-2 control-label" style="margin-top:0px !important">Display Fund</label>
																				<div class="col-md-10">
																					<div class="make-switch" data-on-label="Yes" data-on="success" data-off-label="No" data-off="danger">
																						<input type="checkbox"  <?php if($fund['public'] == '1'){ echo 'checked';}?> id="allow_all_<?php echo $fund['fund_id'];?>" class="toggle" name="public" onChange="updateDisplay('<?php echo $fund['fund_id'];?>');"/>
																					</div>
																					<span class="help-block">If set to "Yes", this fund will be visible on MyTrackRecord.com</span>
																				</div><!--col-md-10-->
																			</div><!--form-group-->
																			<div class="clearfix"></div>
																			
																			<div class="form-group" style="border-top:1px solid #EEEEEE;padding-top:10px;">
																				<label class="col-md-2 control-label">Fund Strategy</label>
																				<div class="col-md-10">
																					<textarea class="form-control wysiwyg-add" name="fund_strategy" id="fund_strategy_<?php echo $fund['fund_id'];?>"><?php echo $fund['description'];?></textarea><br />
																					<button type="button" class="btn btn-info btn-sm" onclick="saveFundStrat('<?php echo $fund['fund_id'];?>');">Save Changes</button>
																				</div><!--col-md-10-->
																			
																			</div>
																			<div class="clearfix"></div>
																			<input type="hidden" name="fundID" value="<?php echo $fund['fund_id'];?>" />
																			
																			
																		</form>
																	</div><!--portlet-body-->
																</div><!--portlet-->
																<?php
																	
															}
															?>
                                                            
                                                            <div class="mtr-setup-nav">
                                                            	<a href="#invest-strat_tab2" data-toggle="tab" onClick="changeTab('fund-strat-tab','invest-strat-tab');" class="btn btn-info mtr-nav-left"><i class="icon-arrow-left"></i> Investment Strategy</a>
                                                                <a href="https://marketocracy.setmore.com/resourcebookingpage/r2f301448389596528" target="_blank" class="btn btn-success mtr-nav-right"> Request Feedback</a>
                                                 
                                                                <div class="clearfix"></div>
                                                            </div>
                                                            
                                                        </div><!--tab-pane-->
                                                        
                                                        
                                                    </div><!--tab-content-->
                                                    
                                                </div><!--portlet-tabs-->
                                                
                                            </div><!--portlet-body-->
                                        </div><!--portlet-tabbable-->
                                        
                                        
                                        
                                        <?php /*?><div class="portlet">
                                            <div class="portlet-title">
                                                <div class="caption">Fund Management</div>
                                                <div class="actions">
                                                   
                                                </div><!--tools-->
                                            </div><!--portlet-title-->
                                            <div class="portlet-body" style="background: #FAFAFA;">
                                            	
                                                
                                                
                                                <p>Here you will be able to toggle which funds you wish to display on MyTrackRecord.com, as well as change your fund strategy/description.</p>
                                               	
												<?php
												$query = "
													SELECT * 
													FROM ".$_SESSION['fund_table']."
													WHERE member_id=:member_id AND active='1'
												";
												try{
													$rsGetFunds = $mLink->prepare($query);
													$aValues = array(
														':member_id'	=> $_SESSION['member_id']
													);
													$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
													$rsGetFunds->execute($aValues);
												}
												catch(PDOException $error){
													// Log any error
													file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
												}
												//echo $preparedQuery;
												while($fund = $rsGetFunds->fetch(PDO::FETCH_ASSOC)){
													
													?>
                                                    <div class="portlet">
                                                        <div class="portlet-title">
                                                            <div class="caption"><?php echo $fund['fund_symbol'];?></div>
                                                            <div class="actions">
                                                            </div><!--tools-->
                                                        </div><!--portlet-title-->
                                                        <div class="portlet-body">
                                                        	
                                                            <form action="" method="post" name="update-fund-<?php echo $fund['fund_id'];?>" class="update-fund-<?php echo $fund['fund_id'];?>">
                                                        		
                                                                <div id="fund-strat-msg-<?php echo $fund['fund_id'];?>"></div>
                                                                
                                                            	<div class="form-group">
                                                                    <label  class="col-md-2 control-label" style="margin-top:0px !important">Display Fund</label>
                                                                    <div class="col-md-10">
                                                                        <div class="make-switch" data-on-label="Yes" data-on="success" data-off-label="No" data-off="danger">
                                                                            <input type="checkbox"  <?php if($fund['public'] == '1'){ echo 'checked';}?> id="allow_all_<?php echo $fund['fund_id'];?>" class="toggle" name="public" onChange="updateDisplay('<?php echo $fund['fund_id'];?>');"/>
                                                                        </div>
                                                                        <span class="help-block">If set to "Yes", this fund will be visible on MyTrackRecord.com</span>
                                                                    </div><!--col-md-10-->
                                                                </div><!--form-group-->
                                                                <div class="clearfix"></div>
                                                                
                                                                <div class="form-group" style="border-top:1px solid #EEEEEE;padding-top:10px;">
                                                                	<label class="col-md-2 control-label">Fund Strategy</label>
                                                                    <div class="col-md-10">
                                                                    	<textarea class="form-control" name="fund_strategy" id="fund_strategy_<?php echo $fund['fund_id'];?>" row="5"><?php echo $fund['description'];?></textarea><br />
                                                                        <button type="button" class="btn btn-info btn-sm" onclick="saveFundStrat('<?php echo $fund['fund_id'];?>');">Save Changes</button>
                                                                    </div><!--col-md-10-->
                                                                
                                                                </div>
                                                            	<div class="clearfix"></div>
                                                                <input type="hidden" name="fundID" value="<?php echo $fund['fund_id'];?>" />
                                                                
                                                                
                                                            </form>
                                                        </div><!--portlet-body-->
                                                    </div><!--portlet-->
                                                    <?php
														
												}
												?>
                                               	
                                                
                                            </div><!--portlet-body-->
                                        </div><!--end-portlet--><?php */?>
                                        
                                        
                                        
                                        
                                        
                                    </div><!--col-md-8-->
                                    
                                    <div class="col-md-4">
                                    	
                                        <div class="portlet">
                                            <div class="portlet-title">
                                                <div class="caption">Domain Name</div>
                                                
                                            </div><!--portlet-title-->
                                            <div class="portlet-body" style="background: #FAFAFA;">
                                            	<?php
												//$subdomain = mtr_subdomain($mLink, $_SESSION['member_id'], NULL, 'get-subdomain');
												
												
												
												//echo $subdomain;
												?>
                                            	<form action="" method="post" name="edit-article-form" class="edit-domain-form">
                                                	
                                                    <div id="subdomain-error"></div>
                                                    <div class="form-group">
                                                    	<label class="control-label">Desired Subdomain</label><br />
                                                        <input type="text" class="form-control" name="domain" id="subdomain-check" style="width:300px;display:inline;" value="<?php echo $_SESSION['sub_domain'];?>" /><h4 style="display:inline;"><strong>.mytrackrecord.com</strong></h4>
                                                    </div>
                                                    <div  id="show-subdomain-check" style="margin-top:5px !important;"></div>    
                                                      
                                                    <div id="save-domain-btn">    
                                                    <input type="submit" value="Save Changes" id="submit-domain" class="btn btn-info btn-sm" />
                                                    </div>
                                                    
                                                </form><!--send email-->   
                                                
                                                
                                                <a href="javascript:void(0);" style="margin-top:20px;display:block;text-decoration:none;" onclick="toggleID('show-mtr-links');"><i class="icon-arrow-down"></i> View Your MyTrackRecord Links</a>
                                                
                                                <div id="show-mtr-links" style="display:none;background:#ffffff;padding:10px 5px 5px 5px;">
                                                
                                                
                                                	<ul>
                                                    	<li><a href="http://<?php echo $_SESSION['sub_domain'];?>.mytrackrecord.com"><?php echo $_SESSION['sub_domain'];?>.mytrackrecord.com</a></li>
                                                    	<?php 
														foreach(get_fund_symbols($mLink, $_SESSION['member_id']) as $key=>$fundSymbol){
															
															echo '<li><a href="http://'.$_SESSION['sub_domain'].'.mytrackrecord.com/'.$fundSymbol.'" target="_blank">'.$_SESSION['sub_domain'].'.mytrackrecord.com/'.$fundSymbol.'</a></li>';
																
														}
														
														?>
                                                    </ul>
                                                </div>
                                            
                                            </div><!--portlet-body-->
                                            
											
                                            
                                            
                                            
                                        </div><!--end-portlet-->
                                        
                                        <div class="portlet">
                                            <div class="portlet-title">
                                                <div class="caption">At A Glance</div>
                                                <div class="tools">
                                                    <a class="reload" href="javascript:;"></a>
                                                </div><!--tools-->
                                            </div><!--portlet-title-->
                                            <div class="portlet-body" style="background: #FAFAFA;">
                                            	
                                                
                                                
                                            	<div class="profile-section">
                                            		<h4>Profile Page Views</h4>
                                                    
                                                    <ul class="stat-list">
                                                    	<li>Unique: <span><?php echo $aViewData['uniqueViews'];?></span></li>
                                                        <li>Total: <span><?php echo $aViewData['totalViews'];?></span></li>
                                                        
                                                    </ul>
                                                    <div class="clearfix"></div>
                                                    <h4>Unique Views Breakdown</h4>
                                                    
                                                    <ul class="stat-list">
                                                    	
                                                        <li>Today: <span><?php echo $aViewData['todayViews'];?></span></li>
                                                        <li>Last Week: <span><?php echo $aViewData['lastWeekViews'];?></span></li>
                                                        <li>Last Month: <span><?php echo $aViewData['lastMonthViews'];?></span></li>
                                                        <li>Last 3 Months: <span><?php echo $aViewData['lastThreeMonthViews'];?></span></li>
                                                    </ul>
                                                    
                                                    <div class="clearfix"></div>
                                                    
														<a style="margin:10px;" class="btn btn-info btn-xs" id="load-view-chart-btn" data-toggle="modal" href="#page-view-modal" onclick="loadAnayliticsChart('04-00-00-001',<?php echo $_SESSION['member_id'];?>);">Page View Chart</a>
                                                    
                                                </div><!--profile-section-->
                                                
                                                <div class="profile-section">
                                            		<h4>People Tracking You</h4>
                                                    
                                                    <ul class="stat-list">
                                                    	<li>Total: <span><?php echo $aTrackInfo['totalTrackers'];?></span></li>
                                                    	<li>Today: <span><?php echo $aTrackInfo['subsToday'];?></span></li>
                                                        <li>Last Week: <span><?php echo $aTrackInfo['subsLastWeek'];?></span></li>
                                                        <li>Last Month: <span><?php echo $aTrackInfo['subsLastMonth'];?></span></li>
                                                        
                                                        <li>Unsubscribed: <span><?php echo $aTrackInfo['unsubscribes'];?></span></li>
                                                        <li>Spam: <span><?php echo $aTrackInfo['spam'];?></span></li>
                                                    </ul>
                                                    
                                                    <div class="clearfix"></div>
                                              		
                                                    <h4>Trackers Per Fund</h4>
                                                    
                                                    <ul class="stat-list">
                                                    	<?php
														foreach($aTrackInfo['trackedFunds'] as $trackFundID=>$trackers){
															
															$fundSymbol = get_funds($mLink, $trackFundID, 'fundSymbol');
															
															echo '<li>'.$fundSymbol.': <span>'.$trackers.'</span></li>';
																
														}
														
														?>
                                                    </ul>
                                                    
                                                    <div class="clearfix"></div>
                                                    
                                                    <h4>Your Prospects</h4>
                                                    
                                                    <ul class="stat-list">
                                                    	<li>Total: <span><?php echo memberProspects($mincLink, $_SESSION['member_id']);?></span></li>
                                                        
                                                    </ul>
                                                    
                                                    <div class="clearfix"></div>
                                                </div><!--profile-section-->
                                                
                                                
                                                
                                                <?php if($aFundAssets != false){?>
                                                
                                                <div class="profile-section">
                                            		<h4>Assets Under Management</h4>
                                                    
                                                    <ul class="stat-list">
                                                    	<li>Total: <span>$ <?php echo number_format($aFundAssets[$_SESSION['member_id']]['assetsTotal'], 2, '.',',');?></span></li>
                                                        <?php
														foreach($aFundAssets[$_SESSION['member_id']]['funds'] as $assetFundID=>$assetsUM){
															
															$fundSymbol = get_funds($mLink, $assetFundID, 'fundSymbol');
															
															echo '<li>'.$fundSymbol.': <span>$ '.number_format($assetsUM, 2, '.', ',').'</span></li>';
														}
														?>
                                                        
                                                    </ul>
                                                    
                                                    <div class="clearfix"></div>
                                                    
                                                    
                                                </div><!--profile-section-->
                                                <?php } ?>
                                                
                                                
                                                <div class="profile-section">
                                            		<h4>Your Email Campaigns</h4>
                                                    
                                                    <ul class="stat-list">
                                                    	<li>Sent: <span>0</span></li>
                                                        <li>Clicks: <span>0</span></li>
                                                    </ul>
                                                    
                                                    <div class="clearfix"></div>
                                                    
                                                    
                                                </div><!--profile-section-->
                                                
                                                <div class="profile-section">
                                            		<h4>Your Articles</h4>
                                                    
                                                    <ul class="stat-list">
                                                    	<li>Published: <span>0</span></li>
                                                        <li>Unpublished: <span>0</span></li>
                                                        <li>Views: <span>0</span></li>
                                                    </ul>
                                                    
                                                    <div class="clearfix"></div>
                                                    
                                                    
                                                </div><!--profile-section-->
                                                
                                            
                                            </div><!--portlet-body-->
                                        </div><!--end-portlet-->
                                        
                                        <div class="portlet">
                                            <div class="portlet-title">
                                                <div class="caption">Resources</div>
                                                
                                            </div><!--portlet-title-->
                                            <div class="portlet-body" style="background: #FAFAFA;">
                                            
                                            
                                            <div class="profile-section">
                                                <h4>Videos</h4>
                                                
                                                <ul class="stat-list">
                                                    <li><a href="https://portfolio.marketocracy.com/files/resources/action-center/20160303_MTR_webinar.mp4" target="_blank">MyTrackRecord Webinar<br /><small>02/26/2016</small></a></li>
                                                </ul>
                                                
                                                <div class="clearfix"></div>
                                            </div><!--profile-section-->
                                            
                                            <div class="profile-section">
                                                <h4>Documents</h4>
                                                
                                                <ul class="stat-list">
                                                    <li><a href="https://portfolio.marketocracy.com/files/resources/action-center/20160303_MTR_webinar_slides.pdf" target="_blank">MyTrackRecord Webinar Slides<br /><small>02/26/2016</small></a></li>
                                                </ul>
                                                
                                                <div class="clearfix"></div>
                                            </div><!--profile-section-->
                                            	
                                            
                                            
                                            
                                            </div><!--portlet-body-->
                                        </div><!--end-portlet-->
                                        
                                        
                                            
                                    </div><!--col-md-4-->
                                    
                                </div><!--row-->
                            </div><!--tab-pane-->
                            
             			<?php } //end if tab = dashboard?>
                    </div><!--tab-content-->
                </div><!--tabbable
                <!--END TABS-->
                
                
            
            </div><!--col-md-12-->
        </div><!--row profile-->
        <!-- END PAGE CONTENT-->
        
        <?php /*if($_SESSION['admin'] == 1){?>
        <pre>
        <?php echo print_r($member); ?>
        </pre>
        <?php }*/ ?>
      