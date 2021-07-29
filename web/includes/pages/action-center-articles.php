<?php
/*
Marketocracy Inc. |
Action Center Articles

Supporting files:
	AJAX		- process/ajax/action-center-processes.php
	JAVASCRIPT	- includes/scripts/action-center.js.php
	HTML		- THIS DOCUMENT includes/pages/action-center.php
*/

$tab = $_REQUEST['tab'];


$aTrackInfo 	= memberTracker($mLink, $_SESSION['member_id']);

$aFundAssets	= fundAssets($mLink, $_SESSION['member_id']);

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
                                                            <th width="20%">Article</th>
                                                            <th>Published</th>
                                                            <th>Type</th>
                                                            <th>Last Modified</th>
                                                            <th>Status</th>
                                                            <th>Views</th>
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
                                        
                                        
                                        
                                        
                                        
                                        
                                        
                                    </div><!--col-md-8-->
                                    
                                    <div class="col-md-4">
                                    	
                                        
                                        
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
                                                    	<li>Unique: <span><?php echo pageViews($mtrLink, '04-00-00-001', $_SESSION['member_id'], 'unique-member');?></span></li>
                                                        <li>Total: <span><?php echo pageViews($mtrLink, '04-00-00-001', $_SESSION['member_id'], 'total-member');?></span></li>
                                                    </ul>
                                                    
                                                    <div class="clearfix"></div>
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
        
        <?php if($_SESSION['admin'] == 1){?>
        <pre>
        <?php echo print_r($member); ?>
        </pre>
        <?php } ?>
      