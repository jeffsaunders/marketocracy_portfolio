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
		
		//Check for signup code
		$codeID = $member['signup_code_id'];
		
		//echo $codeID;
		
		if($codeID != ''){
			
			$query = "
				SELECT *
				FROM system_signup_codes
				WHERE code_id=:code_id
			";
			try {
				$rsCheckCode = $mLink->prepare($query);
				$aValues = array(
					':code_id'	=> $codeID
				);
				// Prepared query - for error logging and debugging
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsCheckCode->execute($aValues);
			}
			catch(PDOException $error){
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$codeInfo = $rsCheckCode->fetch(PDO::FETCH_ASSOC);
			
			
			
			$today 			= time();
			
			$code			= $codeInfo['code'];
			$codeType		= $codeInfo['code_type'];
			$activationDate	= $codeInfo['activation_date'];
			$experationDate	= $codeInfo['experation_date'];
			
			
			
			if($codeType == 'class'){
				
				if($today > $activationDate && $today < $experationDate){
					
					$query = "
						SELECT ec.*, es.name, es.university_name, es.university_symbol, m.name_first, m.name_last, m.name_title, m.name_suffix
						FROM `edu_class` AS ec 
						INNER JOIN `edu_teacher` AS et ON ec.teacher_id=et.teacher_id
						INNER JOIN `edu_schools` as es ON es.school_id=et.school_id
						INNER JOIN `members` AS m ON m.member_id=et.teacher_id
						WHERE ec.class_code=:code
					";
					try {
						$rsGetClass = $mLink->prepare($query);
						$aValues = array(
							':code'	=> $code
						);
						// Prepared query - for error logging and debugging
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsGetClass->execute($aValues);
					}
					catch(PDOException $error){
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
					while($class = $rsGetClass->fetch(PDO::FETCH_ASSOC)){
						$classID			= $class['class_id'];
						$endDate			= $class['end_date'];
					}
					
					$classCode = $code;
					
				}else{
					$classCode = '';	
				}
			}
				
		}
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
         
         <!-- BEGIN Class Code MODAL-->
         <div class="modal fade" id="about-class-code" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
               <div class="modal-content">
                  <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                     <h4 class="modal-title">Class Code</h4>
                  </div>
                  <div class="modal-body">
                    <p>If you are joining Marketocracy to participate in a class assignment, you will need your class code from your professor. This code will link your account to the correct class.</p>
                    
                    <p>If you are a college student signing up without joining a class, simply select "No, I do not have a class code." and click the "Continue" button.<p>
                      
                      
                  </div><!--modal-body-->
                  <div class="modal-footer">
                     <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  </div>
               </div>
               <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
         </div>
         <!-- END Class Code MODAL-->
         <!-- END PAGE MODALS-->
         
         <!-- BEGIN PAGE CONTENT-->
         <div class="row">
            <div class="col-md-12">
               <div class="portlet" id="setup_wizard_1">
                  <div class="portlet-title">
                     <div class="caption">
                        <i class="icon-reorder"></i> Account Setup - <span class="step-title">Step 1 of 5</span>
                     </div>
                     <div class="tools hidden-xs">
                        <a href="#portlet-config" data-toggle="modal" class="config"></a>
                        <a href="javascript:;" class="reload"></a>
                     </div>
                  </div>
                  
                  <div class="portlet-body form">
                     <form action="" class="form-horizontal" id="setup_wizard_form">
                        <div class="form-wizard">
                           <div class="form-body">
                              <ul class="nav nav-pills nav-justified steps">
                                 <li>
                                    <a href="#tab1" data-toggle="tab" class="step">
                                    <span class="number">1</span>
                                    <span class="desc"><i class="icon-ok"></i> Basic Info</span>   
                                    </a>
                                 </li>
                                 <li>
                                    <a href="#tab2" data-toggle="tab" class="step">
                                    <span class="number">2</span>
                                    <span class="desc"><i class="icon-ok"></i> Profile Setup</span>   
                                    </a>
                                 </li>
                                 <?php 
								 //if class code is set, there is no need to go through this step
								 if($classCode == ''){ ?>
                                 <li>
                                    <a href="#tab3" data-toggle="tab" class="step active">
                                    <span class="number">3</span>
                                    <span class="desc"><i class="icon-ok"></i> Student Access</span>   
                                    </a>
                                 </li>
                                 <?php } ?>
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
										<h3 class="form-section">First Things First!</h3>
										
                                        <div class="row">
                                        	<div class="col-md-3 xs-hidden sm-hidden">
                                            </div><!--col-md-3-->
                                            
                                        	<div class="col-md-6">
                                            	<h4>What is your name?</h4>
                                                
                                                <div class="form-group showHideName hide" style="margin: 0 0 10px 0 !important; width:30%;">
                                                    <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
                                                    <label class="control-label visible-ie8 visible-ie9 hide">Title</label>
                                                    
                                                    <input class="form-control placeholder-no-fix" type="text" autocomplete="off" placeholder="Title: Mr. Mrs. Dr." name="title" value="<?php echo $member['name_title']; ?>"/>
                                                    
                                                    
                                                </div>
                                                
                                                <div class="form-group" style="margin: 0 0 10px 0 !important;">
                                                    <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
                                                    <label class="control-label visible-ie8 visible-ie9">First Name<span class="required">*</span></label>
                                                    
                                                    <input class="form-control placeholder-no-fix" type="text" autocomplete="off" placeholder="First Name*" name="first_name" value="<?php echo $member['name_first']; ?>" />
                                                    
                                                    <div id="error_first_name"></div>
                                                </div>
                                                
                                                <div class="form-group showHideName hide" style="margin:0 0 10px 0 !important;">
                                                    <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
                                                    <label class="control-label visible-ie8 visible-ie9">Middle Name</label>
                                                    
                                                    <input class="form-control placeholder-no-fix" type="text" autocomplete="off" placeholder="Middle Name" name="middle_name" value="<?php echo $member['name_middle']; ?>"/>
                                                </div>
                                                
												<div class="form-group" style="margin:0 0 10px 0 !important;">
                                                    <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
                                                    <label class="control-label visible-ie8 visible-ie9">Last Name<span class="required">*</span></label>
                                                    
                                                    <input class="form-control placeholder-no-fix" type="text" autocomplete="off" placeholder="Last Name*" name="last_name" value="<?php echo $member['name_last']; ?>" />
                                                    
                                                    <div id="error_last_name"></div>
                                                </div>
                                                
                                                <div class="form-group showHideName hide" style="margin: 0 0 10px 0 !important; width:30%;">
                                                    <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
                                                    <label class="control-label visible-ie8 visible-ie9">Suffix</label>
                                                    
                                                    <input class="form-control placeholder-no-fix" type="text" autocomplete="off" placeholder="Suffix: Jr, Sr, III, MD, PhD" name="suffix" value="<?php echo $member['name_suffix']; ?>"/>
                                                </div>
                                                
                                                <button type="button" class="btn btn-info btn-xs" id="showBtnName" onclick="showAll('Name');">View All Options</button>
                                                <button type="button" class="btn btn-info btn-xs hide" id="hideBtnName" onclick="hideAll('Name');">Hide Non Required Fields</button>
                                            
                                            	
                                            
                                            
                                            
                                            </div><!--col-md-12-->
                                            
                                           
                                            
                                            
                                        </div><!--row-->
                                        
                                        <hr />
                                        
                                        <div class="row">
                                        	<div class="col-md-3 xs-hidden sm-hidden">
                                            </div><!--col-md-3-->
                                            
                                        	<div class="col-md-6">
                                            	<h4>How can we get in touch? <span class="label label-warning">Optional</span></h4>
                                                
                                                <div class="form-group showHideContact"  style="margin:0 0 10px 0 !important;">
													<label class="control-label visible-ie8 visible-ie9">Alternate Email<span class="required">*</span></label>
													<div class="input-group">
														<span class="input-group-addon" id="alt_email_error_fix">
															<i class="icon-envelope"></i>
														</span>
														<input type="text" class="form-control" name="alt_email" id="alt_email" placeholder="Alternate Email Address" onBlur="checkEmail(encodeURIComponent(this.value));" value="<?php echo $member['alt_email']; ?>"/>
													</div>
													<!--<span class="help-block">Provide your email address</span>-->
													<div id="error_email"></div>
												</div>

												<div style="overflow:hidden;position:relative;">
                                                	<div style="position:absolute;height:10px;width:10px;right:-500px;top:50px;" id="error-fields">
                                                    	
                                                    </div>
                                                </div>

												<div class="form-group" style="margin:0 0 10px 0 !important;">
													<label class="control-label visible-ie8 visible-ie9">Daytime Telephone</label>
													<div class="input-group">
														<span class="input-group-addon">
															<i class="icon-phone"></i>
														</span>
														<input type="text" class="form-control" name="phone_day" id="phone_day" placeholder="Daytime Telephone" value="<?php echo $member['phone_day']; ?>" />
													</div>
													<!--<span class="help-block"></span>-->
													<div id="error_phone_day"></div>
												</div>

												<div class="form-group showHideContact hide" style="margin:0 0 10px 0 !important;">
													<label class="control-label visible-ie8 visible-ie9">Evening Telephone</label>
													<div class="input-group">
														<span class="input-group-addon">
															<i class="icon-phone"></i>
														</span>
														<input type="text" class="form-control" name="phone_evening" id="phone_evening" placeholder="Evening Telephone" value="<?php echo $member['phone_evening']; ?>" />
													</div>
													<!--<span class="help-block"></span>-->
													<div id="error_phone_evening"></div>
												</div>

												<div class="form-group showHideContact hide" style="margin:0 0 10px 0 !important;">
													<label class="control-label visible-ie8 visible-ie9">Mobile Telephone</label>
													<div class="input-group">
														<span class="input-group-addon">
															<i class="icon-phone"></i>
														</span>
														<input type="text" class="form-control" name="phone_mobile" id="phone_mobile" placeholder="Mobile Telephone" value="<?php echo $member['phone_mobile']; ?>" />
													</div>
													<!--<span class="help-block"></span>-->
													<div id="error_phone_mobile"></div>
												</div>
                                                
                                                <button type="button" class="btn btn-info btn-xs" id="showBtnContact" onclick="showAll('Contact');">View All Options</button>
                                                <button type="button" class="btn btn-info btn-xs hide" id="hideBtnContact" onclick="hideAll('Contact');">Hide Non Required Fields</button>
                                            	
                                            </div><!--col-md-6-->
                                            
                                        </div><!--row-->
                                        
                                        <hr />
                                        
                                        <div class="row">
                                        
                                        	<div class="col-md-3 xs-hidden sm-hidden">
                                            </div><!--col-md-3-->
                                            
                                        	<div class="col-md-6">
                                            	<h4>Where do you live? <span class="label label-warning">Optional</span></h4>
                                                
                                                
                                                <div id="load-address-fields"></div>
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
												<div class="form-group"  style="margin:0 0 10px 0 !important;">
													<label class="control-label visible-ie8 visible-ie9">Country</label>
													<select name="country" id="select2_sample4" class="select2 form-control"  onChange="loadAddress(this.value);">
														<option value=""></option>
														<option value="US|United States">United States</option>
														<option value="CA|Canada">Canada</option>
														<?php
														// Loop through countries, building option list
														while ($countries = $rsCountries->fetch(PDO::FETCH_ASSOC)){
															echo "<option value=\"".$countries['country_code_isa']."|".$countries['country_name']."\">".$countries['country_name']."</option>\r";
														}
														?>
													</select>
												</div>
                                                
                                                
                                            </div><!--col-md-6-->
                                            
                                        </div><!--row-->
										

									</div><!--END form-body-->

								</div><!--END tabf1-->
								<!--END ACCOUNT SETUP-->

                                 <!--START ACCOUNT SETUP-->
                                 <div class="tab-pane" id="tab2">
                                 	<div class="form-body">
                                        <h3 class="form-section">Tell us a little about yourself.</h3>
										<div class="note note-info">
                                        	<p>Note: This section is not required, however we encourage our members to give us a little insight into their inventment strategy. To skip this step, simply click "Continue" at the bottom.<p>
                                        </div>
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
                                                     <div class="form-inline" role="form">
    	
														<?php 
                                                        //echo $member['DOB'];
                                                        /*$dob 	= date_create($member['DOB']);
                                                        $month 	= date_format($dob, 'm');
                                                        $day	= date_format($dob, 'd');
                                                        $year	= date_format($dob, 'Y');
                                                        
														if($member['DOB'] == '0000-00-00'){
                                                            $month = '';
                                                            $year = '';
                                                            $day = '';
                                                        }*/
                                                        ?>
                                                        <div class="form-group" style="margin:0px;">
                                                            
                                                            <select name="month" class="form-control input-small">
                                                                <option value="">MM</option>
                                                                <?php echo printList($mLink, '7', $month);	?>
                                                            </select> /
                                                        </div>
                                                        <div class="form-group" style="margin:0px;">
                                                            <select name="day" class="form-control input-small">
                                                                <option value="">DD</option>
                                                                <?php echo printList($mLink, '6', $day); ?>
                                                            </select> /
                                                        </div>
                                                        <div class="form-group" style="margin:0px;">
                                                            <input type="text" placeholder="YYYY" name="year" class="form-control input-small" value="<?php echo $year;?>" />
                                                        </div>
                                                    </div><!--form-inline-->
                                                </div>
                                                
                                               <div class="form-group">
                                                 <label class="control-label">Gender/Sex</label>
                                                    <div class="radio-list">
                                                       <label><input type="radio" name="gender" value="M" data-title="Male" />Male</label>
                                                       <label><input type="radio" name="gender" value="F" data-title="Female"/>Female</label>
                                                       <label><input type="radio" name="gender" value="U" data-title="Undefined"/>Undefined</label>  
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
                                            	<?php
												$query = "
													SELECT *
													FROM ".$_SESSION['profile_questions_table']."
													WHERE active='1'
													ORDER BY sequence ASC
												";
												try{
													$rsGetQs = $mLink->prepare($query);
													$aValues = array(
														//':list_id' 	=> $listID
													);
													$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
													$rsGetQs->execute($aValues);
												}
												catch(PDOException $error){
													// Log any error
													file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
												}
												
												$aQuestionIDs = array();
												
												while($question = $rsGetQs->fetch(PDO::FETCH_ASSOC)){
													
													$QID 		= $question['qid'];
													$type		= $question['type'];
													
													$aQuestionIDs[] = $QID;
													
													if($question['options'] != ''){
														$aOptions	= explode('~',$question['options']);
													}
													
													$query = "
														SELECT *
														FROM ".$_SESSION['profile_answers_table']."
														WHERE member_id=:member_id AND qid=:qid
														ORDER BY timestamp DESC
														LIMIT 1
													";
													try{
														$rsGetAnswer = $mLink->prepare($query);
														$aValues = array(
															':member_id' 	=> $_SESSION['member_id'],
															':qid'			=> $QID
														);
														$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
														$rsGetAnswer->execute($aValues);
													}
													catch(PDOException $error){
														// Log any error
														file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
													}
												
													$answer = $rsGetAnswer->fetch(PDO::FETCH_ASSOC);
													
													//swith on question type
													switch($type){
														case 'textarea' :
															?>
															<div class="form-group">
																<label class="control-label"><strong><?php echo $question['question'];?> <?php if($question['note'] != ''){ echo '('.$question['note'].')'; }?></strong></label>
																<textarea class="form-control wysihtml5" rows="4" id="<?php echo $QID;?>" name="pro_question_<?php echo $QID;?>"><?php echo $answer['answer'];?></textarea>
															</div>
															<?php
														break;
														
														case 'radio' :
															?>
															<div class="form-group">
																<label class="control-label"><strong><?php echo $question['question'];?> <?php if($question['note'] != ''){ echo '('.$question['note'].')'; }?></strong></label>
																<div class="radio-list">
																	<?php
																	$cnt = 0;
																	
																	foreach($aOptions as $key=>$option){
																			
																		$cnt++;
																		
																		$aOption = explode('|', $option);
																		
																		if($answer['answer'] == $aOption[1]){
																			echo '<label class="radio-inline" style="border:none;"><input type="radio" name="pro_question_'.$QID.'[]" id="'.$QID.'-option-'.$cnt.'" value="'.$aOption[1].'" checked> '.$aOption[0].'</label>';	
																		}else{
																			echo '<label class="radio-inline" style="border:none;"><input type="radio" name="pro_question_'.$QID.'[]" id="'.$QID.'-option-'.$cnt.'" value="'.$aOption[1].'"> '.$aOption[0].'</label>';
																		}
																	}
																	?>
																	
																</div>
															</div>
															<?php
														break;	
														case 'text' :
															?>
															<div class="form-group">
																<label class="control-label"><strong><?php echo $question['question'];?> <?php if($question['note'] != ''){ echo '('.$question['note'].')'; }?></strong></label>
																<input type="text" name="pro_question_<?php echo $QID;?>" id="<?php echo $QID;?>" class="form-control" value="<?php echo $answer['answer'];?>" />
															</div>
															<?php
														break;
														case 'select' :
															?>
															<div class="form-group">
																<label class="control-label"><strong><?php echo $question['question'];?> <?php if($question['note'] != ''){ echo '('.$question['note'].')'; }?></strong></label>
																<select name="pro_question_<?php echo $QID;?>" id="<?php echo $QID;?>" class="form-control">
																	<?php
																	$cnt = 0;
																	
																	foreach($aOptions as $key=>$option){
																			
																		$cnt++;
																		
																		$aOption = explode('|', $option);
																		
																		if($answer['answer'] == $aOption[1]){
																			echo '<option value="'.$aOption[1].'" selected>'.$aOption[0].'</option>';	
																		}else{
																			echo '<option value="'.$aOption[1].'">'.$aOption[0].'</option>';
																		}
																	}
																	?>
																</select>
															</div>
															<?php
														break;
														case 'checkbox' :
															?>
															<div class="form-group">
																<label class="control-label"><strong><?php echo $question['question'];?> <?php if($question['note'] != ''){ echo '('.$question['note'].')'; }?></strong></label>
																<div class="checkbox-list">
																	<?php
																	$cnt = 0;
																	
																	foreach($aOptions as $key=>$option){
																			
																		$cnt++;
																		
																		$aOption = explode('|', $option);
																		
																		$aAnswers = explode('|', $answer['answer']);
																		
																		if(in_array($aOption[1], $aAnswers)){
																			echo '<label class="checkbox-inline"><input type="checkbox" name="pro_question_'.$QID.'[]" id="'.$QID.'-'.$cnt.'" value="'.$aOption[1].'" checked> '.$aOption[0].'</label>';	
																		}else{
																			echo '<label class="checkbox-inline"><input type="checkbox" name="pro_question_'.$QID.'[]" id="'.$QID.'-'.$cnt.'" value="'.$aOption[1].'"> '.$aOption[0].'</label>';
																		}
																	}
																	?>
																</div>
															</div>
															<?php
														break;
													}//end switch
													
													
												}
												?>
												
												<input type="hidden" name="questions" value="<?php echo implode('|', $aQuestionIDs);?>" />
                                                
                                            </div><!--END col-md-12-->
                                                
                                        </div><!--END row-->
                                    </div><!--END form-body-->
                                    
                                 </div><!--END tabf1-->
                                 <!--END ACCOUNT SETUP-->
                                 
                                 
                                 <div class="tab-pane" id="tab3">
                                    <h3 class="form-section">Student Info</h3>
                                    
                                    <div class="row">
                                        <div class="col-md-3 xs-hidden sm-hidden">
                                        </div><!--col-md-3-->
                                        
                                        <div class="col-md-6">
                                            <h4>Are you a student?</h4>
                                            
                                            <?php
											if($classCode != ''){
												$noStudent 	= '';
												$yesStudent	= 'checked';	
												$yesClassCode = 'checked';
												
												?>
                                                <input type="hidden" value="<?php echo $classCode;?>" name="good_class_code" />
                								<input type="hidden" value="<?php echo $classID;?>" name="class_id" />
                								<input type="hidden" value="<?php echo $endDate;?>"	name="class_end_date" />
                                                <input type="hidden" value="passed" name="edu_email_passed" />
                                                <?php
											}else{
												$noStudent 	= 'checked';
												$yesStudent	= '';
												$yesClassCode = '';
											}
											?>
                                            
                                            <div class="form-group" style="margin:0 0 10px 0 !important;">
                                                <div class="radio-list">
                                                    <label><input type="radio" name="student" value="false"  <?php echo $noStudent;?> onchange="checkStudent('no');"/>No, I am NOT a student</label>
                                                    <label><input type="radio" name="student" value="true" <?php echo $yesStudent;?> onchange="checkStudent('yes');"/>Yes, I am a student</label>
                                                </div>
                                            </div>
                                        	
                                            <div id="edu-email-check">
                                            	
                                            </div><!--edu-email-check-->
                                            
                                            
                                        </div><!--col-md-6-->
                                        
                                    </div><!--row-->
                                    
                                    <hr />
                                    
                                    <div id="is-student" class="hide">
                                        <div class="row">
                                            <div class="col-md-3 xs-hidden sm-hidden"></div><!--col-md-3-->
                                            
                                            <div class="col-md-6">
                                                <h4>Do you have a class code? <a href="#about-class-code" data-toggle="modal"><i class="icon-info-sign"></i></a></h4>
                                                
                                                <div class="form-group" style="margin:0 0 10px 0 !important;">
                                                    <div class="radio-list">
                                                        <label><input type="radio" name="class_code_check" value="true"  <?php echo $yesClassCode;?> onchange="checkClassCode('yes');" />Yes, I have a class code.</label>
                                                        <label><input type="radio" name="class_code_check" value="false" onchange="checkClassCode('no');"/>No, I do not have a class code.</label>
                                                    </div>
                                                    
                                                    <div id="error_class_code_check"></div>
                                                </div>
                                                
                                                
                                                
                                                <div id="class-code-area" class="hide">
                                                    <div class="form-group"  style="margin: 0 0 10px 0 !important; width:30%;">
                                                        <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
                                                        <label class="control-label" id="class_code_label">Please Enter Your Class Code<span class="required">*</span></label>
                                                        
                                                        <input class="form-control placeholder-no-fix" type="text" autocomplete="off" value="<?php echo $classCode;?>" placeholder="Class Code" id="class_code" name="class_code" style="text-transform:uppercase;"/>
                                                    </div>
                                                    
                                                    <div id="class_code_details"></div>
                                                </div><!--class-code-area-->
                                                
                                                <div class="alert alert-info hide">
                                                	<p>You do not need a class code to continue, you can sign up for a class later.</p>
                                                </div>
                                                
                                            </div><!--col-md-6-->
                                        </div><!--row-->
                                 </div><!--is-student-->
                                    
                                 </div><!--END TAB 3-->
                                 
                                 <div class="tab-pane" id="tab4">
                                    <h3 class="block">Setup your first Fund!</h3>
                                    <div class="form-group">
                                       <label class="control-label col-md-3">Type of Fund <span class="required">*</span> <?php /*?><br><a href="#fund-type-modal" data-toggle="modal">What's This?</a><?php */?></label>
                                       <div class="col-md-4">
                                          <div class="radio-list">
                                             <label>
                                             <input type="radio" name="fund_type" value="L" checked />
                                             Long Only
                                             </label>
                                             <?php /*?><label>
                                             <input type="radio" name="fund_type" value="S" >
                                             Short Only
                                             </label> <?php */?> 
                                          </div>
                                          <div id="error_fund_type"></div>
                                       </div>
                                    </div>
                                    
                                    <div class="form-group">
                                       <label class="control-label col-md-3">Fund Name<span class="required">*</span></label>
                                       <div class="col-md-4">
                                          <input type="text" class="form-control" name="fund_name" placeholder="Example: jdoe's Mutual Fund"/>
                                          <span class="help-block"></span>
                                       </div>
                                    </div>
                                    <div class="form-group">
                                       <label class="control-label col-md-3" id="fund_symbol_label">Fund Ticker Symbol<span class="required">*</span></label>
                                       <div class="col-md-4">
                                          <input type="text" class="form-control" name="fund_symbol" id="fund_symbol" placeholder="Example: JDF" style="text-transform:uppercase;"/>
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
                                 </div><!--end tab 4-->
                                 
                                 <div class="tab-pane" id="tab5">
                                    <h3 class="block">All done!</h3>
                                    
                                    <h4>You are now ready to start trading! Click Save to finalize your settings and go to the trade wizard!</h4>
                                    
                                    <div id="setup-wizard-errors"></div>
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
                                       <span id="save-btn"><input type="submit" value="Save & Start Trading" class="btn btn-success button-submit"></span>
                                                                  
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
