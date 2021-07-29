		<!-- BEGIN INCLUDE SETUP-WIZARD -->

		<?php
		//Look up their membership information
		$query = "
			SELECT *
			FROM ".$_SESSION['members_table']."
			WHERE member_id = :member_id
			ORDER BY timestamp DESC
			LIMIT 1
		";
		try {
			$rsMember = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $_SESSION['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
			$rsMember->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}

		$member = $rsMember->fetch(PDO::FETCH_ASSOC);
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
         
         <!-- BEGIN Fund Type MODAL-->
         <div class="modal fade" id="fund-type-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
               <div class="modal-content">
                  <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                     <h4 class="modal-title">Fund Type</h4>
                  </div>
                  <div class="modal-body">
                    <p>In order to accurately track all components of your investing performance we separate your long and short positions into different funds. Please select which type of fund you wish to make.</p>
                    <p>If you're unsure what this means, choose "long-only". You can also <a href="#">click here</a> to read a library article about shorting.</p>
                      
                      
                  </div><!--modal-body-->
                  <div class="modal-footer">
                     <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  </div>
               </div>
               <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
         </div>
         <!-- END Fund Type MODAL-->
         <!-- END PAGE MODALS-->
         
         <!-- BEGIN PAGE CONTENT-->
         <div class="row">
            <div class="col-md-12">
               <div class="portlet" id="setup_wizard_1">
                  <div class="portlet-title">
                     <div class="caption">
                        <i class="icon-reorder"></i> Membership Setup Wizard - <span class="step-title">Step 1 of 5</span>
                     </div>
                     <div class="tools hidden-xs">
                        <a href="#portlet-config" data-toggle="modal" class="config"></a>
                        <a href="javascript:;" class="reload"></a>
                     </div>
                  </div>
                  
                  <div class="portlet-body form">
                     <form action="process/process-acct.php" class="form-horizontal" id="submit_form">
                        <div class="form-wizard">
                           <div class="form-body">
                              <ul class="nav nav-pills nav-justified steps">
                                 <li>
                                    <a href="#tab1" data-toggle="tab" class="step">
                                    <span class="number">1</span>
                                    <span class="desc"><i class="icon-ok"></i> Account Setup</span>   
                                    </a>
                                 </li>
                                 <li>
                                    <a href="#tab2" data-toggle="tab" class="step">
                                    <span class="number">2</span>
                                    <span class="desc"><i class="icon-ok"></i> Profile Setup</span>   
                                    </a>
                                 </li>
                                 <li>
                                    <a href="#tab3" data-toggle="tab" class="step active">
                                    <span class="number">3</span>
                                    <span class="desc"><i class="icon-ok"></i> Membership Level</span>   
                                    </a>
                                 </li>
                                 <li>
                                    <a href="#tab4" data-toggle="tab" class="step">
                                    <span class="number">4</span>
                                    <span class="desc"><i class="icon-ok"></i> Fund Setup</span>   
                                    </a> 
                                 </li>
                                 <li>
                                    <a href="#tab5" data-toggle="tab" class="step">
                                    <span class="number">5</span>
                                    <span class="desc"><i class="icon-ok"></i> Start Trading</span>   
                                    </a> 
                                 </li>
                              </ul>
                              <div id="bar" class="progress progress-striped" role="progressbar">
                                 <div class="progress-bar progress-bar-success"></div>
                              </div>
                              <div class="tab-content">
                                 <div class="alert alert-danger display-none">
                                    <button class="close" data-dismiss="alert"></button>
                                    You have some form errors. Please check below.
                                 </div>
                                 <div class="alert alert-success display-none">
                                    <button class="close" data-dismiss="alert"></button>
                                    Your form validation is successful!
                                 </div>

								<!--START ACCOUNT SETUP-->
								<div class="tab-pane active" id="tab1">
									<div class="form-body">
										<h3 class="form-section">Contact</h3>

										<div class="row">

											<div class="col-md-6" style="padding: 0px 30px;">

												<div class="form-group">
													<label class="control-label">Username</label>
													<div class="input-group">
														<span class="input-group-addon">
                                                       		<i class="icon-user"></i>
														</span>
														<input type="text" class="form-control" name="username" value="<?php echo $_SESSION['username']; ?>" disabled/>
													</div>
													<span class="help-block"></span>
													<div id="error_username"></div>
												</div>

												<div class="form-group">
													<label class="control-label">Title</label>
													<div class="input-group">
														<span class="input-group-addon">
															<i class="icon-font"></i>
														</span>
														<input type="text" class="form-control" name="name_title" placeholder="Mr., Mrs., Ms., Dr., etc." />
													</div>
													<!--<span class="help-block"></span>-->
												</div>

												<div class="form-group">
													<label class="control-label">First Name<span class="required">*</span></label>
													<div class="input-group">
														<span class="input-group-addon">
															<i class="icon-font"></i>
														</span>
														<input type="text" class="form-control" name="name_first" placeholder="Your first name" value="<?php echo $member['name_first']; ?>" />
													</div>
													<!--<span class="help-block"></span>-->
												</div>

												<div class="form-group">
													<label class="control-label">Middle Initial/Name</label>
													<div class="input-group">
														<span class="input-group-addon">
															<i class="icon-font"></i>
														</span>
														<input type="text" class="form-control" name="name_middle" placeholder="Your middle initial/name" />
													</div>
													<!--<span class="help-block"></span>-->
												</div>

												<div class="form-group">
													<label class="control-label">Last Name<span class="required">*</span></label>
													<div class="input-group">
														<span class="input-group-addon">
															<i class="icon-font"></i>
														</span>
														<input type="text" class="form-control" name="name_last" placeholder="Your last name" value="<?php echo $member['name_last']; ?>" />
													</div>
													<!--<span class="help-block"></span>-->
												</div>

												<div class="form-group">
													<label class="control-label">Suffix</label>
													<div class="input-group">
														<span class="input-group-addon">
															<i class="icon-font"></i>
														</span>
														<input type="text" class="form-control" name="name_suffix" placeholder="Jr, III, MD, PhD, etc." />
													</div>
													<!--<span class="help-block"></span>-->
												</div>

											</div><!--END col-md-6-->

                                            <div class="col-md-6" style="padding: 0px 30px;">

												<div class="form-group" style="height:65px;"><!-- spacer --></div>

												<div class="form-group">
													<label class="control-label">Email<span class="required">*</span></label>
													<div class="input-group">
														<span class="input-group-addon">
															<i class="icon-envelope"></i>
														</span>
														<input type="text" class="form-control" name="email" id="email" placeholder="Your email address" value="<?php echo $member['email']; ?>" />
													</div>
													<!--<span class="help-block">Provide your email address</span>-->
													<div id="error_email"></div>
												</div>

												<div class="form-group">
													<label class="control-label">Confirm Email<span class="required">*</span></label>
												   	<div class="input-group">
												   		<span class="input-group-addon">
												   			<i class="icon-envelope"></i>
														</span>
														<input type="text" class="form-control" name="email2" placeholder="Confirm your email address" />
													</div>
													<!--<span class="help-block">Confirm your email address</span>-->
													<div id="error_email2"></div>
												</div>

												<div class="form-group">
													<label class="control-label">Daytime Telephone<span class="required">*</span></label>
													<div class="input-group">
														<span class="input-group-addon">
															<i class="icon-phone"></i>
														</span>
														<input type="text" class="form-control" name="phone_day" id="phone_day" placeholder="Your daytime phone number" value="<?php echo $member['phone_day']; ?>" />
													</div>
													<!--<span class="help-block"></span>-->
													<div id="error_phone_day"></div>
												</div>

												<div class="form-group">
													<label class="control-label">Evening Telephone</label>
													<div class="input-group">
														<span class="input-group-addon">
															<i class="icon-phone"></i>
														</span>
														<input type="text" class="form-control" name="phone_evening" id="phone_evening" placeholder="Your evening phone number" value="<?php echo $member['phone_evening']; ?>" />
													</div>
													<!--<span class="help-block"></span>-->
													<div id="error_phone_evening"></div>
												</div>

												<div class="form-group">
													<label class="control-label">Mobile Telephone</label>
													<div class="input-group">
														<span class="input-group-addon">
															<i class="icon-phone"></i>
														</span>
														<input type="text" class="form-control" name="phone_mobile" id="phone_mobile" placeholder="Your mobile phone number" value="<?php echo $member['phone_mobile']; ?>" />
													</div>
													<!--<span class="help-block"></span>-->
													<div id="error_phone_mobile"></div>
												</div>

											</div><!--END col-md-6-->

										</div><!--END row-->

										<h3 class="form-section">Address</h3>

										<div class="row">

											<div class="col-md-6" style="padding: 0px 30px;">

												<?php
												//Get countries (except US)
												$query = "
													SELECT *
													FROM ".$_SESSION['countries_table']."
													WHERE country_valid = 1
													AND country_name <> 'United States'
													AND country_name <> 'Canada'
													ORDER BY country_name
												";
												try {
													$rsCountries = $mLink->prepare($query);
													$rsCountries->execute();
												}
												catch(PDOException $error){
													// Log any error
													file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
												}
												?>



												<div class="form-group">
													<label class="control-label">Country<span class="required">*</span></label>
													<div class="input-group">
														<span class="input-group-addon">
															<i class="icon-flag"></i>
														</span>
<!--														<input type="text" class="form-control" name="country" value="<?php echo $member['country']; ?>" />-->
														<select name="country" id="select2_sample4" class="select2 form-control">
															<option value=""></option>
															<optgroup>
															<option value="US|United States" <?php echo (trim($member['country']) == "United States" ? " selected" : ""); ?>>United States</option>
															<option value="CA|Canada" <?php echo (trim($member['country']) == "Canada" ? " selected" : ""); ?>>Canada</option>
															<?php
															// Loop through countries, building option list
															while ($countries = $rsCountries->fetch(PDO::FETCH_ASSOC)){
																echo "<option value=\"".$countries['country_code_isa']."|".$countries['country_name']."\"".(trim($member['country']) == trim($countries['country_name']) ? ' selected' : '').">".$countries['country_name']."</option>\r";
															}
															?>
															</optgroup>
														</select>
													</div>
													<!--<span class="help-block"></span>-->
                                                </div>

												<div class="form-group">
													<label class="control-label">Address<span class="required">*</span></label>
												   	<div class="input-group">
												   		<span class="input-group-addon">
												   			<i class="icon-home"></i>
														</span>
														<input type="text" class="form-control" name="address1"/>
													</div>
													<!--<span class="help-block"></span>-->
												</div>
												<div class="form-group" style="margin-top:-10px;">
												   	<div class="input-group">
												   		<span class="input-group-addon">
												   			<i class="icon-plus" style="width:13px;"></i>
														</span>
														<input type="text" class="form-control" name="address2"/>
													</div>
													<!--<span class="help-block"></span>-->
												</div>

											</div><!--END col-md-6-->

											<div class="col-md-6" style="padding: 0px 30px;">

												<div class="form-group">
													<label class="control-label">City<span class="required">*</span></label>
												   	<div class="input-group">
												   		<span class="input-group-addon">
												   			<i class="icon-font"></i>
														</span>
														<input type="text" class="form-control" name="city"/>
													</div>
													<!--<span class="help-block"></span>-->
                                                </div>
<!-- Test for country here and adjust State/Province and Postal Code accordingly -->
												<?php
												//Get States/Provinces
												if ($member['country'] == "United States" || $member['country'] == "Canada"){
													if ($member['country'] == "United States"){
														$query = "
															SELECT *
															FROM ".$_SESSION['states_table']."
															WHERE state_entity = 'State'
															OR state_entity = 'Territory'
															ORDER BY state_entity ASC, state_name ASC
														";
													}else{
														$query = "
															SELECT *
															FROM ".$_SESSION['states_table']."
															WHERE state_entity = 'Province'
															ORDER BY state_name
														";
													}
													try {
														$rsStates = $mLink->prepare($query);
														$rsStates->execute();
													}
													catch(PDOException $error){
														// Log any error
														file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
													}
												?>
												<div class="form-group">
													<label class="control-label">State/Province<span class="required">*</span></label>
												   	<div class="input-group">
												   		<span class="input-group-addon">
												   			<i class="icon-caret-down"></i>
														</span>
														<input type="text" class="form-control" name="state"/>
														<select name="country" id="select2_sample5" class="select2 form-control">
															<option value=""></option>

															<?php
  															if ($member['country'] == "United States"){
																echo '<optgroup label="States">';
															}else{
																echo '<optgroup label="Provinces">';
															}
															$states = $rsStates->fetch(PDO::FETCH_ASSOC);
															$entity = $states['state_entity'];
//															mysql_data_seek($rsStates, 0);
//															$rsStates->rewind();
															// PDO for MySQL does not have a way to reset the pointer in the result set
															// Therefore we must run the query again to get a fresh set with the pointer at the top
															// Commonly mentioned workarounds do NOT work.
															// Please fix this Zend or Oracle!
															$rsStates->execute();
															// Loop through states, building option list
															while ($states = $rsStates->fetch(PDO::FETCH_ASSOC)){
																if ($states['state_entity'] != $entity){
																	echo '
															</optgroup>
															<optgroup label = "Territories">
																	';
																	$entity = $states['state_entity'];
																}
																echo "<option value=\"".$states['state_abbrev']."|".$states['state_name']."\"".(trim($member['state']) == trim($states['state_name']) ? ' selected' : '').">".$states['state_name']."</option>\r";
															}
															?>
															</optgroup>
														</select>
													</div>
													<!--<span class="help-block"></span>-->
												</div>

												<div class="form-group">
													<label class="control-label">ZIP/Postal Code<span class="required">*</span></label>
												   	<div class="input-group">
												   		<span class="input-group-addon">
												   			<i class="icon-envelope"></i>
														</span>
														<input type="text" class="form-control" name="zip" id="submit_form_password"/>
													</div>
													<!--<span class="help-block"></span>-->
												</div>
												<?php
												}
												?>

											</div><!--END col-md-6-->

										</div><!--END row-->

									</div><!--END form-body-->

								</div><!--END tabf1-->
								<!--END ACCOUNT SETUP-->

                                 <!--START ACCOUNT SETUP-->
                                 <div class="tab-pane" id="tab2">
                                 	<div class="form-body">
                                        <h3 class="form-section">Provide your profile details</h3>

                                        <div class="row">
                                            
                                            <div class="col-md-6" style="padding: 0px 30px;">
                                            
                                                <div class="form-group">
                                                   <label class="control-label" tabindex=1>Occupation</label>
                                                      <input type="text" class="form-control" name="occupation"/>
                                                      <span class="help-block"></span>
                                                </div>
                                                
                                                <div class="form-group">
                                                   <label class="control-label" tabindex=2>Industry</label>
                                                      <input type="text" class="form-control" name="industry"/>
                                                      <span class="help-block"></span>
                                                </div>
                                                
                                                <div class="form-group">
                                                   <label class="control-label" tabindex=5>Other Interest</label>
                                                      <input type="text" class="form-control" name="interest"/>
                                                      <span class="help-block"></span>
                                                </div>
                                                
                                            </div><!--END col-md-6-->
                                            
                                            <div class="col-md-6" style="padding: 0px 30px;">
                                            
                                                <div class="form-group">
                                                   <label class="control-label" tabindex=3>Year of Birth</label>
                                                      <input type="text" class="form-control" name="age" placeholder="YYYY"/>
                                                      <span class="help-block">Format: YYYY</span>
                                                </div>
                                                
                                               <div class="form-group">
                                                 <label class="control-label">Gender</label>
                                                    <div class="radio-list">
                                                       <label><input type="radio" name="gender" value="M" data-title="Male" />Male</label>
                                                       <label><input type="radio" name="gender" value="F" data-title="Female"/>Female</label>  
                                                    </div>
                                                    <div id="form_gender_error"></div>
                                              </div>
                                            
                                            </div><!--END col-md-6-->
                                                
                                        </div><!--END row-->
                                        
                                        <h3 class="form-section">Social Media and Web</h3>
                                        
                                        <div class="row">
                                            
                                            <div class="col-md-6" style="padding: 0px 30px;">
                                            
                                                <div class="form-group">
                                                   <label class="control-label" tabindex=1>LinkedIn</label>
                                                      <input type="text" class="form-control" name="linkedin"/>
                                                      <span class="help-block"></span>
                                                </div>
                                                
                                                <div class="form-group">
                                                   <label class="control-label" tabindex=2>Google+</label>
                                                      <input type="text" class="form-control" name="google" />
                                                      <span class="help-block"></span>
                                                </div>
                                                
                                                <div class="form-group">
                                                   <label class="control-label" tabindex=3>Web Site</label>
                                                      <input type="text" class="form-control" name="web_site"/>
                                                      <span class="help-block"></span>
                                                </div>
                                                
                                            </div><!--END col-md-6-->
                                            
                                            <div class="col-md-6" style="padding: 0px 30px;">
                                            
                                                <div class="form-group">
                                                   <label class="control-label" tabindex=4>Twitter</label>
                                                      <input type="text" class="form-control" name="twitter"/>
                                                      <span class="help-block"></span>
                                                </div>
                                                
                                                <div class="form-group">
                                                   <label class="control-label" tabindex=4>Facebook</label>
                                                      <input type="text" class="form-control" name="facebook"/>
                                                      <span class="help-block"></span>
                                                </div>
                                            
                                            </div><!--END col-md-6-->
                                                
                                        </div><!--END row-->
                                        
                                        <h3 class="form-section">About You</h3>
                                        
                                        <div class="row">
                                            
                                            <div class="col-md-12" style="padding: 0px 30px;">
                                            
                                                <div class="form-group">
                                                   <label class="control-label">Bio</label>
                                                      <textarea class="form-control" name="bio" rows="6"></textarea>
                                                      <span class="help-block">Give a brief description about yourself</span>
                                                </div>
                                                
                                            </div><!--END col-md-12-->
                                                
                                        </div><!--END row-->
                                        
                                        <h3 class="form-section">How You Invest</h3>
                                        
                                        <div class="row">
                                            
                                            <div class="col-md-12" style="padding: 0px 30px;">
                                            
                                                <div class="form-group">
                                                   <label class="control-label">What is your investing strategy? (Growth, Value, Technical analysis, etc.)</label>
                                                      <textarea class="form-control" name="question1" rows="6"></textarea>
                                                      <span class="help-block"></span>
                                                </div>
                                                
                                                <div class="form-group">
                                                   <label class="control-label">Do you have expertise, from your job or personal life, in the area you invest in?</label>
                                                      <textarea class="form-control" name="question2" rows="6"></textarea>
                                                      <span class="help-block"></span>
                                                </div>
                                                
                                                <div class="form-group">
                                                   <label class="control-label">How long have you been an investor? How did you get started?</label>
                                                      <textarea class="form-control" name="question3" rows="6"></textarea>
                                                      <span class="help-block"></span>
                                                </div>
                                                
                                                <div class="form-group">
                                                   <label class="control-label">What attracted you to Marketocracy?</label>
                                                      <textarea class="form-control" name="question4" rows="6"></textarea>
                                                      <span class="help-block"></span>
                                                </div>
                                                
                                                <div class="form-group">
                                                   <label class="control-label">How do you select new companies for your Funds?</label>
                                                      <textarea class="form-control" name="question5" rows="6"></textarea>
                                                      <span class="help-block"></span>
                                                </div>
                                                
                                                <div class="form-group">
                                                   <label class="control-label">How do you decide to remove a stock from your Funds?</label>
                                                      <textarea class="form-control" name="question6" rows="6"></textarea>
                                                      <span class="help-block"></span>
                                                </div>
                                                
                                            </div><!--END col-md-12-->
                                                
                                        </div><!--END row-->
                                    </div><!--END form-body-->
                                    
                                 </div><!--END tabf1-->
                                 <!--END ACCOUNT SETUP-->
                                 
                                 
                                 <div class="tab-pane" id="tab3">
                                    <h3 class="form-section">Select a membership level</h3>
                                    <div class="form-group">
                                    	<label class="control-label col-md-3">Membership Level</label>
                                        <div class="col-md-4">
                                        	<style type="text/css">
												.radio-list label {border:1px solid #CCC;padding:10px;}
												
												span.push-right {float:right;}
											</style>
                                        	
                                            
                                        	<div class="radio-list">
                                               <label style="border:1px solid #CCC;padding:10px;">
                                               <input type="radio" name="membership" value="F" data-title="Free" checked />Free <span class="push-right"><a href="#membership-free" data-toggle="modal">Details</a></span>
                                               </label>
                                               <?php /*?>
											   // Comment out until launch/beta testing
                                               <label>
                                               <input type="radio" name="membership" value="S" data-title="Student"/>Student | Cost: $299.99 <span class="push-right"><a href="#membership-student" data-toggle="modal">Details</a></span>
                                               </label> 
                                               <label>
                                               <input type="radio" name="membership" value="B" data-title="Basic"/>Basic | Cost: $399.99 <span class="push-right"><a href="#membership-basic" data-toggle="modal">Details</a></span>
                                               </label>
                                               <label>
                                               <input type="radio" name="membership" value="P" data-title="Premium"/>Premium | Cost: $699.99 <span class="push-right"><a href="#membership-premium" data-toggle="modal">Details</a></span>
                                               </label> <?php */?>
                                            </div>
                                            <span class="help-block">This membership platform is currently in "Alpha" Testing. No payment is required.</span>
                                            <div id="error-membership"></div>
                                        </div>
                                    </div>
                                    
                                    
                                    
                                    <?php /*?>
									COMMENT OUT UNTIL LAUNCH/BETA
									<h3 class="form-section">Provide your billing and credit card details</h3>
                                        <div class="row">
                                        	<div class="col-md-6">
                                            	<h3 class="form-section" style="padding-left: -30px !important;">Credit Card</h3>
                                            </div>
                                            
                                            <div class="col-md-6">
                                            	<h3 class="form-section" style="padding-left: -30px !important;">Billing Address | 
                                                	<span style="font-size:12px;"><input type="checkbox" name="same_personal"> Same as personal address</span>
                                                </h3>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            
                                            <div class="col-md-6" style="padding: 0px 30px;">
                                            	
                                                
                                                <div class="form-group">
                                                   <label class="control-label">Card Holder Name<span class="required">*</span></label>
                                                      <input type="text" class="form-control" name="card_name" />
                                                      <span class="help-block"></span>
                                                </div>
                                                
                                                <div class="form-group">
                                                   <label class="control-label">Card Number<span class="required">*</span></label>
                                                      <input type="text" class="form-control" name="card_number"/>
                                                      <span class="help-block"></span>
                                                </div>
                                                
                                                <div class="form-group">
                                                   <label class="control-label">CVC<span class="required">*</span></label>
                                                      <input type="text" placeholder="" class="form-control" name="card_cvc"/>
                                                      <span class="help-block"></span>
                                                </div>
                                                
                                                <div class="form-group">
                                                   <label class="control-labe">Expiration(MM/YY)<span class="required">*</span></label>
                                                      <input type="text" placeholder="MM/YY" maxlength="7" class="form-control" name="card_expiry_date"/>
                                                      <span class="help-block"></span>
                                                </div>
                                                
                                               <div class="form-group">
                                                 <label class="control-label">Payment Options<span class="required">*</span></label>
                                                    <div class="checkbox-list">
                                                       <label>
                                                       <input type="checkbox" name="payment[]" value="1" data-title="Auto-Pay with this Credit Card." /> Auto-Pay with this Credit Card
                                                       </label>
                                                       <label>
                                                       <input type="checkbox" name="payment[]" value="2" data-title=""/> Something eles
                                                       </label>
                                                     </div>
                                                     <div id="form_payment_error"></div>
                                               </div>
                                               
                                            </div><!--END col-md-6-->
                                            
                                            <div class="col-md-6" style="padding: 0px 30px;">
                                                
                                                <div class="form-group">
                                                   <label class="control-label">Address<span class="required">*</span></label>
                                                      <input type="text" class="form-control" name="bill_address1"/>
                                                      <input type="text" class="form-control" name="bill_address2" style="margin-top:10px;"/>
                                                      <span class="help-block"></span>
                                                </div>
                                                
                                                <div class="form-group">
                                                   <label class="control-label">City<span class="required">*</span></label>
                                                      <input type="text" class="form-control" name="bill_city"/>
                                                      <span class="help-block"></span>
                                                </div>
                                                
                                                <div class="form-group">
                                                   <label class="control-label">State/Province<span class="required">*</span></label>
                                                      <input type="text" class="form-control" name="bill_state"/>
                                                      <span class="help-block"></span>
                                                </div>
                                                
                                                <div class="form-group">
                                                   <label class="control-label">ZIP/PostCode<span class="required">*</span></label>
                                                      <input type="text" class="form-control" name="bill_zip"/>
                                                      <span class="help-block"></span>
                                                </div>
                                                
                                                <div class="form-group">
                                                   <label class="control-label">Country<span class="required">*</span></label>
                                                      <input type="text" class="form-control" name="bill_country"/>
                                                      <span class="help-block"></span>
                                                </div>
                                            
                                            </div><!--END col-md-6-->
                                                
                                        </div><!--END row--><?php */?>
                                 </div><!--END TAB 3-->
                                 
                                 <div class="tab-pane" id="tab4">
                                    <h3 class="block">Setup your first Fund!</h3>
                                    <div class="form-group">
                                       <label class="control-label col-md-3">Type of Fund <span class="required">*</span> <br><a href="#fund-type-modal" data-toggle="modal">What's This?</a></label>
                                       <div class="col-md-4">
                                          <div class="radio-list">
                                             <label>
                                             <input type="radio" name="fund_type" value="L" />
                                             Long Only
                                             </label>
                                             <label>
                                             <input type="radio" name="fund_type" value="S" >
                                             Short Only
                                             </label>  
                                          </div>
                                          <div id="error_fund_type"></div>
                                       </div>
                                    </div>
                                    
                                    <div class="form-group">
                                       <label class="control-label col-md-3">Fund Name<span class="required">*</span></label>
                                       <div class="col-md-4">
                                          <input type="text" class="form-control" name="fund_name" placeholder="jdoe's Mutual Fund"/>
                                          <span class="help-block"></span>
                                       </div>
                                    </div>
                                    <div class="form-group">
                                       <label class="control-label col-md-3">Fund Ticker Symbol<span class="required">*</span></label>
                                       <div class="col-md-4">
                                          <input type="text" class="form-control" name="fund_symbol" placeholder="JDF"/>
                                          <span class="help-block"></span>
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
                                 </div><!--end tab 4-->
                                 
                                 <div class="tab-pane" id="tab5">
                                    <h3 class="block">All done!</h3>
                                    
                                    <h4>You are now ready to start trading! Click Save to finalize your settings and go to the trade wizard!</h4>
                                 </div><!--end tab 4-->
                                 
                              </div>
                           </div><!--END form-body-->
                           
                           <div class="form-actions fluid">
                              <div class="row">
                                 <div class="col-md-12">
                                    <div class="col-md-offset-3 col-md-9">
                                       <a href="javascript:;" class="btn btn-default button-previous">
                                       <i class="m-icon-swapleft"></i> Back 
                                       </a>
                                       <a href="javascript:;" class="btn btn-info button-next">
                                       Continue <i class="m-icon-swapright m-icon-white"></i>
                                       </a>
                                       <input type="submit" value="Save & Start Trading" class="btn btn-success button-submit">
                                                                  
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
