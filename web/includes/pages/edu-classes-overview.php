         <!-- BEGIN CREATE CATEGORY MODAL-->
        <div class="modal fade" id="create-class" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog">
             <div class="modal-content">
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Create New Class</span></h4>
                </div>
                
                <form action="" method="post" name="create-forum" class="create-forum">
                    <div class="modal-body">
                        	<div id="createForumUserError"></div>
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="control-label">Class Name* <span id="forum_title-help"></span></label>
                                    <input type="text" class="form-control" name="forum_title" id="forum_title" />
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Display Navigation* <span id="forum_title-help"></span></label>
                                    <input type="text" class="form-control" name="forum_title" id="forum_title" />
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Class Description* <span id="forum_desc-help"></span></label>
                                    <textarea class="form-control" name="forum_desc" id="forum_desc" row="5" style="resize:vertical;"></textarea>
                                </div>
                                
                            </div><!--form-body-->
                        
                    </div><!--modal-body-->
                    
                    <div class="modal-footer">
                    	<input type="submit" value="Create Forum" class="btn btn-info" id="submit-forum" style="display:none;"/>
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
             	
                
                
                <!-- BEGIN FORUMS TABLE-->
                <div class="portlet" id="ledger-entries">
                    <div class="portlet-title">
                        <div class="caption"><i class="icon-comments"></i>All Classes</div>
                        <div class="actions">
                            <?php
                            if($_SESSION['admin'] == 1 || $_SESSION['super_admin'] == 1 /*|| $_SESSION['moderator'] == 1*/ || $_SESSION['super_moderator'] == 1){
                            ?>
                                <a class="btn btn-info" href="#create-class" data-toggle="modal">New Class</a>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                    <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover table-full-width" id="class-list">
                            <thead>
                                <tr>
                                    
                                    <th>Class Name</th>
                                    
                                    <th width="2%">Start Date</th>
                                    <th width="2%">End Date</th>
                                    <th width="20%">Top Student</th>
                                    <th style="display:none;"></th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                            	
                                <?php 
                                
                               $query = "
                                    SELECT *
                                    FROM ".$_SESSION['class_table']."
                                    WHERE active='1' AND teacher_id=:member_id
                                    ORDER BY start_date ASC
                                ";
                                
                                try{
                                    $rsClasses = $mLink->prepare($query);
                                    $aValues = array(
                                    	':member_id' 	=> $_SESSION['member_id']
                                    );
                                    $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                    $rsClasses->execute($aValues);
                                }
                                catch(PDOException $error){
                                    // Log any error
                                    file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                }
                                
								function order_by_return($a, $b) {
									return $b['currentReturn'] > $a['currentReturn'] ? 1 : -1;
								}
								
                                while($class = $rsClasses->fetch(PDO::FETCH_ASSOC)) {
                                    
									$className		= $class['class_name'];
									$startDate		= $class['start_date'];
									$endDate		= $class['end_date'];
									$classDesc		= $class['class_desc'];
									$classID		= $class['class_id'];
									$aListStudents	= explode('|', $class['student_ids']);
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
                                    	<td><a href="?page=13-01-00-002&class=<?php echo $classID;?>" style="display:block;"><?php echo $className;?><br /><small><?php echo substr($classDesc,0, 100);?>...</small></a></td>
                                        <td><?php echo date('m/d/Y', $startDate);?></td>
                                        <td><?php echo date('m/d/Y', $endDate);?></td>
                                        <td><a href="?page=04-00-00-001&member=<?php echo $aStudents[0]['memberID'];?>&tab=<?php echo $aStudents[0]['fundID'];?>" target="_blank" style="display:block;"><?php echo $aStudents[0]['fullName'];?> : <?php echo $aStudents[0]['currentReturn'];?>% Return</a></td>
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
        