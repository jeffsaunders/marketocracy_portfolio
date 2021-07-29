<?php
/*
Marketocracy Inc. 
Admin Rankings HTML

Supporting files:
	AJAX		- process/ajax/admin-rankings-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/admin-rankings.js.php
	HTML		- THIS DOCUMENT includes/pages/admin-rankings.php
*/
?>
         
          
         
            <!-- BEGIN PAGE CONTENT-->
            
            
            
            
                
            <div class="row">
                <div class="col-md-12">
                    
                    <div class="tabbable tabbable-custom">
                        
                        <?php include('includes/nav-admin-tabs.php');?>

                        <div class="tab-content">
                        
                            <?php /*?><pre>
                            Function Testing<br />
                            
                            
							<?php
							echo date('Y - m - d h:i:s', 1443816000);
							
							$fundID = '2-1'; 
							
							print_r(checkRecords($mLink, $fundID));
							?>
                            </pre><?php */?>
                            
                            <div class="tab-pane active" id="tab_1">
                                <div class="portlet">
                                    <div class="portlet-title">
                                    	<div class="caption"><i class="icon-reorder"></i>Run Rankings Process</div>
                                      	<div class="tools">
                                        	<a href="javascript:;" class="collapse"></a>
                                        	<!--<a href="javascript:;" class="reload"></a>-->
                                      	</div><!--tools-->
                                    </div><!--portlet-title-->
                                    <div class="portlet-body form">
                                            
                                            <form action="" method="post" class="view-period-form">
                                                <div class="form-body">
                                                    
                                                     <div class="form-group">
                                                        <label class="control-label">Select Ranking Period</label><br />
                                                        
                                                        
                                                        <select name="year">
                                                            <?php
                                                            echo date_list($mLink, 'year', NULL, NULL, false);
                                                            ?>
                                                        </select>/
                                                        <select  name="month">
                                                            <?php
                                                            echo date_list($mLink, 'month', NULL, NULL, true);
                                                            ?>
                                                        </select>
                                                        
                                                        <small>YYYY/MM</small><br /><br />
                                                	</div><!--form-group-->
                                                    
                                                    <div id="period-submit-btn"><input type="submit" name="submit-btn" id="view-period-btn" value="View Period Data" class="btn btn-success" /></div>
                                                    
                                            	</div><!--form-body-->
                                            </form>
                                            
                                            <div id="rank-period-data"></div>
                                            
                                            
                                        
                                        
                                        
                                        <div id="load-results">
                                        
                                        </div>
                                        
                                        
                                    </div><!--END PORTLET BODY-->
                                </div><!--END PORTLET-->
                                
                                <?php /*?><div class="portlet">
                                    <div class="portlet-title">
                                    	<div class="caption"><i class="icon-reorder"></i>Gap Check</div>
                                      	<div class="tools">
                                        	<a href="javascript:;" class="collapse"></a>
                                        	<!--<a href="javascript:;" class="reload"></a>-->
                                      	</div><!--tools-->
                                    </div><!--portlet-title-->
                                    <div class="portlet-body form">
                                            
                                            <form action="" method="post" class="gap-check">
                                                <div class="form-body">
                                                    
                                                     
                                                    
                                                    <div class="form-group">
                                                        <label class="control-label">Member ID</label>
                                                        <input type="text" name="member_id" class="form-control" /><br />
                                                        <small>Leave blank for all members</small>
                                                    </div><!--form-group-->
                                                    
                                                    
                                                    
                                                    
                                                    
                                                    <div id="gap-submit-btn"><input type="submit" name="gap-check" value="Check For Gaps" class="btn btn-success" /></div>
                                            	</div><!--form-body-->
                                                
                                            </form>
                                        
                                        
                                        
                                        <div id="gap-results">
                                        
                                        </div>
                                        
                                        
                                    </div><!--END PORTLET BODY-->
                                </div><!--END PORTLET-->
                                
                                <div class="portlet">
                                    <div class="portlet-title">
                                    	<div class="caption"><i class="icon-reorder"></i>Rankings Upload</div>
                                      	<div class="tools">
                                        	<a href="javascript:;" class="collapse"></a>
                                        	<!--<a href="javascript:;" class="reload"></a>-->
                                      	</div><!--tools-->
                                    </div><!--portlet-title-->
                                    <div class="portlet-body form">
                                            
                                            <?php
											$dir = 'files/rankings';
											$aFiles = scandir($dir,1);
											
											//print_r($aFiles);
											?>
                                            
                                            <form action="" method="post" class="rankings-upload">
                                                <div class="form-body">
                                                    
                                                    <div class="form-group">
                                                    	 <label class="control-label">Ranking File</label>
                                                         <select name="rank_file" class="form-control">
                                                         	<?php 
															foreach($aFiles as $key=>$value){
																if($value != '.' && $value != '..' && $value != 'archive'){
																	echo '<option value="'.$value.'">'.$value.'</option>';
																}
															}
															?>
                                                         </select>
                                                    </div>
                                                    
                                                    
                                                    
                                                    
                                                    
                                                    
                                                    
                                                    <div id="upload-submit-btn"><input type="submit" name="rankings_upload" value="Save Rankings" class="btn btn-success" /></div>
                                            	</div><!--form-body-->
                                                
                                            </form>
                                        
                                        
                                        
                                        <div id="upload-results">
                                        
                                        </div>
                                        
                                        
                                    </div><!--END PORTLET BODY-->
                                </div><!--END PORTLET--><?php */?>
                                
                                
                            
                            </div><!--END TAB 2-->
                            
                            
                        
                        
                        </div><!--tab-content-->
                    </div><!--tabbable tabbable-custom-->
                    
                </div><!--col-md-12-->
            </div><!--row-->
            
            <?php include('includes/pages/includes/admin-api-que.php');?>
            <!-- END PAGE CONTENT-->    
   