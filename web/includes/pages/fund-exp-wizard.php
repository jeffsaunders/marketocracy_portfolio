<?php

//$maxFunds
$aBadges = get_badge_info($mLink);

//$aDebug['badges'] = $aBadges;

?>		

        <!--START PAGE MODALS-->
         <!-- BEGIN Free Membership MODAL-->
         <div class="modal fade" id="membership-free" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
               <div class="modal-content">
                  <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                     <h4 class="modal-title">Free Membership</h4>
                  </div>
                  <div class="modal-body">
                    <p>Details about Free Membership.</p>
                      
                      
                  </div><!--modal-body-->
                  <div class="modal-footer">
                     <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  </div>
               </div>
               <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
         </div>
         <!-- END Free Membership MODAL-->
         
         <!-- BEGIN Student Membership MODAL-->
         <div class="modal fade" id="membership-student" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
               <div class="modal-content">
                  <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                     <h4 class="modal-title">Student Membership</h4>
                  </div>
                  <div class="modal-body">
                    <p>Details about Student Membership.</p>
                      
                      
                  </div><!--modal-body-->
                  <div class="modal-footer">
                     <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  </div>
               </div>
               <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
         </div>
         <!-- END Student Membership MODAL-->
         
         <!-- BEGIN Basic Membership MODAL-->
         <div class="modal fade" id="membership-basic" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
               <div class="modal-content">
                  <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                     <h4 class="modal-title">Basic Membership</h4>
                  </div>
                  <div class="modal-body">
                    <p>Details about Basic Membership.</p>
                      
                      
                  </div><!--modal-body-->
                  <div class="modal-footer">
                     <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  </div>
               </div>
               <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
         </div>
         <!-- END Basic Membership MODAL-->
         
         <!-- BEGIN Premium Membership MODAL-->
         <div class="modal fade" id="membership-premium" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
               <div class="modal-content">
                  <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                     <h4 class="modal-title">Premium Membership</h4>
                  </div>
                  <div class="modal-body">
                    <p>Details about Premium Membership.</p>
                      
                      
                  </div><!--modal-body-->
                  <div class="modal-footer">
                     <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  </div>
               </div>
               <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
         </div>
         <!-- END Premium Membership MODAL-->         
         <!-- END PAGE MODALS-->
         
         
         
         
         <!-- BEGIN PAGE CONTENT-->
         <div class="row">
            <div class="col-md-12">
            
            	<?php
				/*echo '<pre>';
				print_r($_SESSION['subscription']);
				echo '</pre>';*/
				?>
            	
            	
               <div class="portlet" id="trans_wizard_1">
                  <div class="portlet-title">
                     <div class="caption">
                        <i class="icon-reorder"></i> Fund Experation - <span class="step-title">Step 1 of 2</span>
                     </div>
                     <div class="tools hidden-xs">
                        
                     </div>
                  </div>
                  
                  <div class="portlet-body form">
                     <form action="" class="form-horizontal" id="trans_wizard_form">
                        <div class="form-wizard">
                           <div class="form-body">
                           		<div id="trans-wizard-errors">
                                </div>
                              <ul class="nav nav-pills nav-justified steps">
                                 <li>
                                    <a href="#tab1" data-toggle="tab" class="step">
                                    <span class="number">1</span>
                                    <span class="desc"><i class="icon-ok"></i> New Fund</span>   
                                    </a>
                                 </li>
                                 
                                 <li>
                                    <a href="#tab2" data-toggle="tab" class="step">
                                    <span class="number">3</span>
                                    <span class="desc"><i class="icon-ok"></i> Confirmation</span>   
                                    </a>
                                 </li>
                                 
                              </ul>
                              <div id="bar" class="progress progress-striped" role="progressbar">
                                 <div class="progress-bar progress-bar-success"></div>
                              </div>
                              <div class="tab-content">
                                 <?php /*?><div class="alert alert-danger display-none">
                                    <button class="close" data-dismiss="alert"></button>
                                    You have some form errors. Please check below.
                                 </div>
                                 <div class="alert alert-success display-none">
                                    <button class="close" data-dismiss="alert"></button>
                                    Your form validation is successful!
                                 </div><?php */?>

								<!--START ACCOUNT SETUP-->
								<div class="tab-pane active" id="tab1">
									<div class="form-body">
										<h3 class="form-section">The following fund has expired!</h3>
                                        <p>Your <strong><?php echo $_SESSION['subscription']['subData']['product_name'];?> Membership</strong> only allows you to have <strong>(<?php echo $_SESSION['subscription']['maxFunds'];?>)</strong> fund. Also with a <strong><?php echo $_SESSION['subscription']['subData']['product_name'];?> Membership</strong>, you can only maintain a fund for 1 year. After that year has expired you must either upgrade your membership or start a new fund.</p>
										<div class="trans-wizard-errors"></div>
                                        <div class="row">
                                        	<div class="col-md-12">
                                            
                                            	<div class="form-group" style="margin: 0 0 10px 0 !important;">
                                                    
													<table class="table  table-bordered table-hover">
                                                    	<thead>
                                                        	<tr>
                                                            	<th>Fund</th>
                                                                <th>Inception</th>
                                                                <th>10 Year AAR</th>
                                                                <th>5 Year AAR</th>
                                                                <th>3 Year AAR</th>
                                                                <th>1 Year AAR</th>
                                                                <th>Achievements</th>
                                                            </tr>
                                                    	</thead>  
                                                        <tbody>
                                                        	<?php
                                                            $aFunds 	= $_SESSION['subscription']['funds'];
															$limitFund	= false;
                                                            
															if($_SESSION['subscription']['membershipLevelID'] == '1' && $_SESSION['subscription']['maxFunds'] == '1'){
																#member is basic with no add-ons
																
																$aDebug['limitFund'] = 'hello';
																
																$limitFund = true;																
																	
															}
															
															$selectCnt = 0;
															$showWarningMessage	= false;
                                                            foreach($aFunds as $fundID=>$fundSymbol){
																
																$selectCnt++;
																if($selectCnt <= $_SESSION['subscription']['maxFunds']){
																	$preSelect = 'checked';	
																}else{
																	$preSelect = '';
																}
																
																
																
																$aFundInfo 			= get_funds($mLink, $fundID, 'fundInfo');
																
																$inceptionDate		= $aFundInfo['unix_date'];
					
																$timeDiff			= time() - $inceptionDate;
																
																
																
																if($limitFund == true){
																	
																	$aDebug['limitFund'] = $limitFund;
																	
																	if($timeDiff > 31536000){
																		#fund is over a year old
																		
																		$showFundWarning 	= true;
																		$showWarningMessage	= true;
																			
																	}else{
																		#fund is not a year old
																		
																		$showFundWarning = false;
																	}
																	
																}
																	
																#grab most recent ranking_process_results for this fundID, use AAR calculations
																$query = "
																	SELECT rank_unix_date, badge_ids, tenYearAAR, fiveYearAAR, threeYearAAR, oneYearAAR
																	FROM rankings_process_results
																	WHERE fund_id=:fund_id
																	ORDER BY rank_unix_date DESC
																	LIMIT 1
																";
																try {
																	$rsRank = $mLink->prepare($query);
																	$aValues = array(
																		':fund_id'	=> $fundID
																	);
																	// Prepared query - for error logging and debugging
																	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
																	$rsRank->execute($aValues);
																}
																catch(PDOException $error){
																	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
																}
																
																$rankData = $rsRank->fetch(PDO::FETCH_ASSOC);
																
																$tenYearAAR = $rankData['tenYearAAR'];
																$fiveYearAAR = $rankData['fiveYearAAR'];
																$threeYearAAR = $rankData['threeYearAAR'];
																$oneYearAAR = $rankData['oneYearAAR'];
																
																if($tenYearAAR == 0){
																	$tenYearAAR = 'N/A';	
																}else{
																	$tenYearAAR = number_format($tenYearAAR,2,'.',',').'%';
																}
																
																if($fiveYearAAR == 0){
																	$fiveYearAAR = 'N/A';	
																}else{
																	$fiveYearAAR = number_format($fiveYearAAR,2,'.',',').'%';
																}
																
																if($threeYearAAR == 0){
																	$threeYearAAR = 'N/A';	
																}else{
																	$threeYearAAR = number_format($threeYearAAR,2,'.',',').'%';
																}
																
																if($oneYearAAR == 0){
																	$oneYearAAR = 'N/A';	
																}else{
																	$oneYearAAR = number_format($oneYearAAR,2,'.',',').'%';
																}
																
																$aFundBadges 	= explode(',',$rankData['badge_ids']);
																$showBadge		= '';
																
																foreach($aFundBadges as $key=>$badgeID){
																	
																	if($badgeID != ''){
																	
																		$showBadge .= '<img src="'.$aBadges[$badgeID]['badge_img'].'" title="'.$aBadges[$badgeID]['badge_desc'].'" width="60" height="60"> ';	
																	}
																}
																
                                                                ?>
                                                                
                                                                <tr>
                                                                	<td>
                                                                    	<label class="control-label">
                                                                        	<strong><?php echo $fundSymbol;?></strong>
                                                                        </label>
                                                                        <?php if($showFundWarning == true){?>
                                                                        	<span class="label label-warning"><i class="icon-warning-sign" title="Fund is over 1 Year old."></i></span>
																		<?php }?>
                                                                        <?php //echo time();?>
                                                                    </td>
                                                                    <td><?php echo date('m/d/Y', $inceptionDate);?></td>
                                                                    <td><?php echo $tenYearAAR;?></td>
                                                                    <td><?php echo $fiveYearAAR;?></td>
                                                                    <td><?php echo $threeYearAAR;?></td>
                                                                    <td><?php echo $oneYearAAR;?></td>
                                                                    <td><?php echo $showBadge;?></td>
                                                                </tr>
                                                                 
                                                                
                                                                    
                                                                <?php
                                                            }
                                                            ?>
                                                        </tbody>  
                                                            
                                                    </table>
															
                                                    
                                                    <?php 
													if($showWarningMessage == true){
														echo '<div class="note note-warning"><p><span class="label label-warning"><i class="icon-warning-sign"></i></span> Fund is over one year old. This fund will be removed from your account after this wizard has been completed.</p></div>';	
													}
													?>
                                                    
                                                    
                                                    
                                                    
                                                    <div id="error_first_name"></div>
                                                   
                                                    
                                                    <?php
													if(isset($_SESSION['setFundType'])){
														
														if($_SESSION['setFundType'] == 'sector'){
															$showSector = '';
														}else{
															$showSector = 'style="display:none;"';
														}
													}else{
														$showSector		= 'style="display:none;"';
													}
													?>
                                                    
                                                    <h3  class="form-section">Setup your New Fund</h3>
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label">Fund Type <span class="required">*</span></label>
                                                        <div class="col-md-4">
                                                            <select class="form-control" name="fund_type" id="fund_type">
                                                                
                                                                <?php echo $optionList->getList('Fund Type', $_SESSION['setFundType']);?>
                                                            </select>
                                                            <span class="help-block">Select a Fund Type.</span>
                                                            <span class="help-block" id="fund_symbol-help"></span>
                                                        </div>
                                                    </div>
                                                    
                                                   
                                                    <div class="form-group" id="fund-sector" <?php echo $showSector;?>>
                                                        <label class="col-md-3 control-label">Fund Sector <span class="required">*</span></label>
                                                        <div class="col-md-4">
                                                            <select class="form-control" name="fund_sector" id="fund_sector">
                                                                <option value='xx'>Select a Sector</option>
                                                                <?php echo $optionList->getList('Stock Sectors', $_SESSION['setFundSector']);?>
                                                            </select>
                                                            <span class="help-block">Select the Sector you want your fund to be apart of.</span>
                                                            <span class="help-block" id="fund_symbol-help"></span>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                       <label class="control-label col-md-3">Fund Name<span class="required">*</span></label>
                                                       <div class="col-md-4">
                                                          <input type="text" class="form-control" name="fund_name" value="<?php echo $_SESSION['setFundName'];?>" placeholder="Example: jdoe's Mutual Fund"/>
                                                          <span class="help-block"></span>
                                                       </div>
                                                    </div>
                                                    <div class="form-group">
                                                       <label class="control-label col-md-3" id="fund_symbol_label">Fund Ticker Symbol<span class="required">*</span></label>
                                                       <div class="col-md-4">
                                                          <input type="text" class="form-control" name="fund_symbol" value="<?php echo $_SESSION['setFundSymbol'];?>" id="fund_symbol" placeholder="Example: JDF" style="text-transform:uppercase;"/>
                                                          <span class="help-block"></span>
                                                          <div id="error_fund_symbol"></div>
                                                       </div>
                                                       
                                                    </div>
                                                    <div class="form-group">
                                                       <label class="control-label col-md-3">Description</label>
                                                       <div class="col-md-4">
                                                          <textarea class="form-control" name="fund_desc" rows="5"></textarea>
                                                          <span class="help-block">
                                                          - What is your investment objective (aggressive growth, income, long term gain, short term gains, stable growth, etc.)?<br>
                                                          - Do you focus on any one particular sector of the economy?<br>
                                                          - Is the outlook favorable for that sector going forward?<br>
                                                          - Do you have expertise in this area?<br>
                                                          - How do you select companies to add to your fund?<br>
                                                          - How do you select companies to sell from your fund?<br>
                                                          - What are some of the more interesting companies in your portfolio?<br>
                                                          - What firms are you adding now?
                                                          </span>
                                                       </div>
                                                    </div>
                                                    
                                                </div>
                                                
                                            </div><!--col-md-12-->
                                            
                                        </div><!--row-->
										
                                        <hr />
                                        
                                       <div class="row">
                                        	<div class="col-md-6">
                                            	<h3 class="form-section">Upgrade Membership</h3>
                                        		<p><strong>Want to keep your fund?</strong></p>
                                        		<p><a href="/?page=10-00-00-002&account=1&tab=membership" class="btn btn-info">Click here to view Membership Upgrade Options</a></p>
                                            </div><!--col-md-6-->
                                            
                                            <div class="col-md-6">
                                            	<h3 class="form-section">Current Membership Subscriptions</h3>
                                                
                                                <h4><strong>Membership Level:</strong></h4>
                                                <h5><span class="label label-info"><?php echo $_SESSION['subscription']['membershipLevel'];?></span></h5>
                                                
                                                <?php
												#Check to see if they are a basic member if so, check for product subscriptions
												if($_SESSION['subscription']['membershipLevelID'] == '1'){
													
													$productSubs = $_SESSION['subscription']['productSubs'];
													
													echo '<h4><strong>Product Subscriptions:</strong></h4>';
													echo '<ul>';
													
													foreach($productSubs as $productID=>$productSubInfo){
														
														echo '<li>'.$productSubInfo['product_name'].'</li>';
															
													}
													echo '</ul>';
													
													?>
                                                    
                                                    <?php	
												}
												?>
                                                <h4><strong>Total Number of Funds Allowed:</strong></h4>
                                                <h5><span class="label label-info"><?php echo $_SESSION['subscription']['maxFunds'];?></span></h5>
                                                
                                            </div><!--col-md-6-->
                                        </div><!--row-->
										
                                        <div class="note note-info">
                                        	<p>Something wrong with your Membership Level? <a href="/?page=08-01-00-001&type=ga&subtype=membership&ticketSubject=Membership Issue" target="_blank">Click Here</a> to submit a support ticket.</p>
                                        </div>
                                        
									</div><!--END form-body-->

								</div><!--END tabf1-->
								<!--END ACCOUNT SETUP-->

                                 <!--START ACCOUNT SETUP-->
                                 <div class="tab-pane" id="tab2">
                                 	<div class="form-body">
                                        <h3 class="form-section">Confirmation</h3>
										<div class="note note-info">
                                        	<p>Note: Please review the information below. You must click "Create New Fund" to complete the wizard.<p>
                                        </div>
                                        <div class="row">
                                            
                                            <div class="trans-wizard-errors"></div>
                                            <div id="confirm-sec" class="col-md-6 form-control-static" style="padding: 0px 30px;">
                                            	
                                                
                                                
                                            </div><!--END col-md-6-->
                                            
                                        </div><!--row-->    
                                        
                                        <hr />
                                        
                                        <div class="row">
                                        	<div class="col-md-6">
                                            	<h3 class="form-section">Upgrade Membership</h3>
                                        		<p><strong>Want to keep all of your funds?</strong></p>
                                        		<p><a href="/?page=10-00-00-002&account=1&tab=membership" class="btn btn-info">Click here to view Membership Upgrade Options</a></p>
                                            </div><!--col-md-6-->
                                            
                                            <div class="col-md-6">
                                            	<h3 class="form-section">Current Membership Subscriptions</h3>
                                                
                                                <h4><strong>Membership Level:</strong></h4>
                                                <h5><span class="label label-info"><?php echo $_SESSION['subscription']['membershipLevel'];?></span></h5>
                                                
                                                <?php
												#Check to see if they are a basic member if so, check for product subscriptions
												if($_SESSION['subscription']['membershipLevelID'] == '1'){
													
													$productSubs = $_SESSION['subscription']['productSubs'];
													
													echo '<h4><strong>Product Subscriptions:</strong></h4>';
													echo '<ul>';
													
													foreach($productSubs as $productID=>$productSubInfo){
														
														echo '<li>'.$productSubInfo['product_name'].'</li>';
															
													}
													echo '</ul>';
													
													?>
                                                    
                                                    <?php	
												}
												?>
                                                <h4><strong>Total Number of Funds Allowed:</strong></h4>
                                                <h5><span class="label label-info"><?php echo $_SESSION['subscription']['maxFunds'];?></span></h5>
                                                
                                            </div><!--col-md-6-->
                                        </div><!--row-->
                                        
                                            
                                            
                                    </div><!--END form-body-->
                                    
                                 </div><!--END tabf1-->
                                 <!--END ACCOUNT SETUP-->
                                 
                               
                                 
                                 
                              </div>
                           </div><!--END form-body-->
                           
                           <div class="form-actions fluid">
                              <div class="row">
                                 <div class="col-md-12">
                                    <div class="col-md-offset-3 col-md-9">
                                       <a href="?page=10-00-00-005"  class="btn btn-default button-previous">
                                       <i class="m-icon-swapleft"></i> Back 
                                       </a>
                                       <a href="javascript:;" class="btn btn-info button-next" onclick="loadConfirm();">
                                       Continue to Confirmation<i class="m-icon-swapright m-icon-white"></i>
                                       </a>
                                       <input type="submit" value="Create New Fund" class="btn btn-success button-submit">
                                                                  
                                    </div>
                                 </div>
                              </div>
                           </div><!--END form-actions-->
                           
                        </div><!--END form-wizard-->
                     </form>
                  </div><!--END portlet-body-->
               </div>
            </div>
         </div>
         <!-- END PAGE CONTENT-->

		<!-- END INCLUDE SETUP-WIZARD -->
