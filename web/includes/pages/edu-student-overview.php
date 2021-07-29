         <!-- BEGIN CREATE CATEGORY MODAL-->
        <div class="modal fade" id="join-class" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog">
             <div class="modal-content">
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Join Class</span></h4>
                </div>
                
                <form action="" method="post" name="join-class" class="join-class">
                    <div class="modal-body">
                        	<div id="joinClassError"></div>
                            <div class="form-body">
                                <div id="class-code-area">
                                    <div class="form-group">
                                        <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
                                        <label class="control-label" id="class_code_label">Please Enter Your Class Code<span class="required">*</span></label>
                                        
                                        <input class="form-control placeholder-no-fix" type="text" autocomplete="off" value="<?php echo $classCode;?>" placeholder="Class Code" id="class_code" name="class_code" style="text-transform:uppercase;"/>
                                    </div>
                                    
                                    <div id="class_code_details"></div>
                                </div><!--class-code-area-->
                                
                            </div><!--form-body-->
                        
                    </div><!--modal-body-->
                    
                    <div class="modal-footer">
                    	
                       	<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    </div>
                
                </form><!--create-topic-->
             </div>
             <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- END CREATE CATEGORY MODAL-->
        
        <!-- BEGIN PAGE CONTENT-->
        <div class="row">
            <div class="col-md-12">
             	
                <div class="note note-info">
                	<h3>Student BETA</h3>
                    <p>This is an area for students to join a class setup by their professor. If you are not a student please disregard this section.</p>
                </div>
                
                <!-- BEGIN FORUMS TABLE-->
                <div class="portlet" id="ledger-entries">
                    <div class="portlet-title">
                        <div class="caption"><i class="icon-comments"></i>All Classes</div>
                        <div class="actions">
                            <?php
                            //if($_SESSION['admin'] == 1 || $_SESSION['super_admin'] == 1 /*|| $_SESSION['moderator'] == 1*/ || $_SESSION['super_moderator'] == 1){
                            ?>
                                <a class="btn btn-info" href="#join-class" data-toggle="modal">Join Class</a>
                            <?php
                            //}
                            ?>
                        </div>
                    </div>
                    <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover table-full-width" id="class-list">
                            <thead>
                                <tr>
                                    
                                    <th>Class Name</th>
                                    <th>Fund</th>
                                    <th width="2%">Start Date</th>
                                    <th width="2%">End Date</th>
                                    
                                    <th style="display:none;"></th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                            	
                                <?php 
                                
                               $query = "
                                    SELECT c.*, sc.*, s.fund_id
                                    FROM ".$_SESSION['students_table']." AS s
									INNER JOIN ".$_SESSION['class_table']." AS c ON c.class_id=s.class_id
									INNER JOIN ".$_SESSION['teacher_table']." as t ON t.teacher_id=c.teacher_id
									INNER JOIN ".$_SESSION['school_table']." as sc ON sc.school_id=t.school_id
                                    WHERE s.student_id=:student_id
                                    ORDER BY c.start_date DESC
                                ";
                                
                                try{
                                    $rsClasses = $mLink->prepare($query);
                                    $aValues = array(
                                    	':student_id' 	=> $_SESSION['member_id']
                                    );
                                    $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                    $rsClasses->execute($aValues);
                                }
                                catch(PDOException $error){
                                    // Log any error
                                    file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                }
                                
								//echo '<tr><td colspan="3">'.$preparedQuery.' | '.$error.'</td></tr>';
								
								function order_by_return($a, $b) {
									return $b['currentReturn'] > $a['currentReturn'] ? 1 : -1;
								}
								
                                while($class = $rsClasses->fetch(PDO::FETCH_ASSOC)) {
                                    
									$className			= $class['class_name'];
									$startDate			= $class['start_date'];
									$endDate			= $class['end_date'];
									$classDesc			= $class['class_desc'];
									$classID			= $class['class_id'];
									$aListStudents		= explode('|', $class['student_ids']);
									$teacherName		= $class['name_first'].' '.$class['name_last'];
									$universityName		= $class['university_name'];
									$schoolName			= $class['name'];
									$studentFundSymbol	= get_funds($mLink, $class['fund_id'], 'fundSymbol');
									
									//Get top performing student
								
									$studentFundIDs = implode(',',$aFundIDs);
									$professor		= get_member($mLink, $class['teacher_id'], 'full name');
									$aStudents = array();
									foreach($aListStudents as $key=>$sFundID){
										
										$aFundID = explode('-',$sFundID);
										$studentID = $aFundID[0];
										
										//set vars
										$aMember 		= get_member($mLink, $studentID, 'all');
										$aAgg			= get_funds($mLink, $sFundID, 'aggAll', 'agg');
										
										$aStudents[$studentID] = array(
											'memberID'			=> $studentID,
											'fullName'			=> $aMember['fullName'],
											'username'			=> $aMember['username'],
											'fundID'			=> $sFundID,
											'returnLastWeek'	=> $aAgg['returnLastWeek'],
											'currentReturn'		=> number_format($aAgg['currentReturn'],2,'.',',')
										);
									}
									
									
									usort($aStudents, 'order_by_return');
									
									?>
                                    <tr>
                                    	<?php /*?><td>
                                        	<a href="?page=13-01-00-002&class=<?php echo $classID;?>" style="display:block;">
												<?php echo $className;?><br />
                                        		<small><?php echo $universityName; ?> | <?php echo $schoolName; ?> | <?php echo $professor;?></small></a>
                                        </td><?php */?>
                                        
                                        <td>
                                        	
												<?php echo $className;?><br />
                                        		<small><?php echo $universityName; ?> | <?php echo $schoolName; ?> | <?php echo $professor;?></small>
                                        </td>
                                        <td><?php echo $studentFundSymbol;?></td>
                                        <td><?php echo date('m/d/Y', $startDate);?></td>
                                        <td><?php echo date('m/d/Y', $endDate);?></td>
                                       
                                        <td style="display:none;"><?php echo $startDate;?></td>
                                    </tr>
                                    <?php
									
								}
                                ?>
                                
                            </tbody>
                            </table>
                            
                            <?php /*?><pre>
                            <?php
							
							

							
							print_r($aStudents);
							?>
                            </pre><?php */?>
                            
                           
                    </div><!--portlet-body-->
                </div><!--end portlet-->
                <!-- END FORUM TABLE-->
                
               
                
            </div><!--end column-->
        </div><!--row-->
        