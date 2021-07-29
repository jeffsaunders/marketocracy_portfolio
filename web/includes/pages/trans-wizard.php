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
                        <i class="icon-reorder"></i> Account Transition - <span class="step-title">Step 1 of 2</span>
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
                                    <span class="desc"><i class="icon-ok"></i> Select Funds</span>   
                                    </a>
                                 </li>
                                 <li>
                                    <a href="#tab2" data-toggle="tab" class="step">
                                    <span class="number">2</span>
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
										<h3 class="form-section">Select Funds to Continue. Number of funds allowed: <strong><?php echo $_SESSION['subscription']['maxFunds'];?></strong></h3>
                                        <p>Your <strong><?php echo $_SESSION['subscription']['subData']['product_name'];?> Membership</strong> only allows you to have <strong>(<?php echo $_SESSION['subscription']['maxFunds'];?>)</strong> fund(s). Please select the fund(s) you wish to keep. You may also choose to upgrade your membership to allow for more funds.</p>
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
                                                                        	<input class="form-control fund-checkbox" type="checkbox" name="funds[]" value="<?php echo $fundID;?>"  /> <strong><?php echo $fundSymbol;?></strong>
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
														echo '<div class="note note-warning"><p><span class="label label-warning"><i class="icon-warning-sign"></i></span> Fund is over one year old. If you select this fund, you will have 30 days before the fund is removed, at which time you will be able to start a new fund.</p></div>';	
													}
													?>
                                                    
                                                    
                                                    <div id="error_first_name"></div>
                                                </div>
                                                
                                            </div><!--col-md-12-->
                                            
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
                                        	<p>Note: Please review the information below as this will be final. After you complete the wizard all unselected funds will be removed from your account permenantly.<p>
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
                                       <a href="?page=10-00-00-004"  class="btn btn-default button-previous">
                                       <i class="m-icon-swapleft"></i> Back 
                                       </a>
                                       <a href="javascript:;" class="btn btn-info button-next" onclick="loadConfirm();">
                                       Continue <i class="m-icon-swapright m-icon-white"></i>
                                       </a>
                                       <input type="submit" value="Complete Transition Wizard" class="btn btn-success button-submit">
                                                                  
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
