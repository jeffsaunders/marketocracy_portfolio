<?php
/*
Marketocracy Data Services | Content Management
Admin General HTML

Supporting files:
	AJAX		- process/ajax/mds/mds-path-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/mds/mds-path-scripts.js.php
	HTML		- THIS DOCUMENT includes/pages/admin-sites-mds.php
*/

$tab = $_REQUEST['tab'];
if($tab == ''){
	$tab = 'paths';	
}
?>
         
          
         
            <!-- BEGIN EDIT PATH MODAL-->
            <div class="modal fade" id="edit-path" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-full">
                 <div class="modal-content" id="load-path-area">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Edit Path</span></h4>
                    </div>
                    
                    <form action="process/ajax/sites/mds-path-processes.php" method="post" name="edit-path-form" class="edit-path-form">
                        <div class="modal-body">
                                
                            <img src="<?php echo $siteRoot;?>/images/ajax-modal-loading.gif" alt="" />
                            
                        </div><!--modal-body-->
                        
                        <div class="modal-footer">
                            <input type="submit" value="Save Path" id="submit-path" class="btn btn-info"/>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    
                    </form><!--create-topic-->
                 </div>
                 <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- END EDIT PATH MODAL-->
            
            <!-- BEGIN EDIT PATH MODAL-->
            <div class="modal fade" id="edit-page" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-full">
                 <div class="modal-content" id="load-page-area">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Edit Page</span></h4>
                    </div>
                    
                    <form action="process/ajax/sites/mds-path-processes.php" method="post" name="edit-path-form" class="edit-page-form">
                        <div class="modal-body">
                                
                            <img src="<?php echo $siteRoot;?>/images/ajax-modal-loading.gif" alt="" />
                            
                        </div><!--modal-body-->
                        
                        <div class="modal-footer">
                            <input type="submit" value="Save Page" id="submit-page" class="btn btn-info"/>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    
                    </form><!--create-topic-->
                 </div>
                 <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- END EDIT PATH MODAL-->
                
            <div class="row">
                <div class="col-md-12">
                    
                    <div class="tabbable tabbable-custom">
                        
                        <ul class="nav nav-tabs">
                       		<li <?php if($tab == 'paths'){echo 'class="active"';}?>><a href="#tab_0" data-toggle="tab">Paths</a></li>
                        	<li <?php if($tab == 'pages'){echo 'class="active"';}?>><a href="#tab_1" data-toggle="tab">Pages</a></li>
                        	
                        </ul>

                        <div class="tab-content">
                        
                            <div class="tab-pane <?php if($tab == 'paths'){echo 'active';}?>" id="tab_0">
                                <div class="portlet">
                                    <div class="portlet-title">
                                    	<div class="caption"><i class="icon-reorder"></i>MDS Paths</div>
                                      	<div class="tools">
                                        	<a href="javascript:;" class="collapse"></a>
                                         	<a href="javascript:;" class="reload"></a>
                                      	</div>
                                    </div><!--portlet-title-->
                                    <div class="portlet-body form">
                                    
                                    	<div class="table-responsive">
                                            <table class="table table-striped table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                    	<th>#</th>
                                                        <th>Path Name</th>
                                                        <?php /*?><th>Path Desc</th><?php */?>
                                                        <th>Last Edited</th>
                                                        <th>Status</th>
                                                        <th>Order</th>
                                                        <th>Action</th>
                                                        
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                	<?php
													$query = "
														SELECT path_name, path_id, path_desc, timestamp, active, sequence
														FROM paths
														ORDER BY sequence ASC
													";
													try{
														$rsGetPaths = $mdsLink->prepare($query);
														$aValues = array(
															//':fund_id' 	=> $fundID
														);
														$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
														$rsGetPaths->execute($aValues);
													}
													catch(PDOException $error){
														// Log any error
														file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
													}
													
													$cnt = 0;
													
													while($paths = $rsGetPaths->fetch(PDO::FETCH_ASSOC)) {
														
														$cnt++;
														
														$pathID			= $paths['path_id'];
														$active 		= $paths['active'];
														$pathName		= $paths['path_name'];
														$pathDesc		= $paths['path_desc'];
														$timestamp		= $paths['timestamp'];
														$order			= $paths['sequence'];
														
														if($active == '1'){
															$status = '<span class="label label-success">Published</span>';	
														}else{
															$status = '<span class="label label-warning">Not Published</span>';	
														}
														
														?>
														<tr>
                                                        	<td><?php echo $cnt;?></td>
                                                            <td><a href="#edit-path" data-toggle="modal" onclick="editPath('<?php echo $pathID;?>');"><?php echo $pathName;?></a></td>
                                                            <?php /*?><td><?php echo $pathDesc;?></td><?php */?>
                                                            <td><?php echo date('Y/m/d h:i', $timestamp);?></td>
                                                            <td><?php echo $status;?></td>
                                                            <td><input type="text" name="order-<?php echo $pathID;?>" value="<?php echo $order;?>"  /></td>
                                                            <td><a href="#edit-path" data-toggle="modal" class="btn btn-info" onclick="editPath('<?php echo $pathID;?>');">Edit</a></td>
                                                        </tr>
                                                        <?php														
													}
													?>
                                                    
                                                </tbody>
                                            </table>
										</div><!--table-responsive-->    
                                        
                                        <?php /*?><pre>
                                       	<?php 
										echo $error;
										?>
                                        </pre><?php */?>
                                                  
                                    </div><!--END PORTLET BODY-->
                                </div><!--END PORTLET-->
                            
                            </div><!--END TAB 1-->
                            
                            <div class="tab-pane <?php if($tab == 'pages'){echo 'active';}?>" id="tab_1">
                                <div class="portlet">
                                    <div class="portlet-title">
                                    	<div class="caption"><i class="icon-reorder"></i>MDS Pages</div>
                                      	<div class="tools">
                                        	<a href="#edit-page" data-toggle="modal" class="btn btn-xs btn-info" style="margin-top:-10px;height:25px;" onclick="createPage();">New Page</button>
                                        	<a href="javascript:;" class="collapse"></a>
                                         	<a href="javascript:;" class="reload"></a>
                                      	</div>
                                    </div><!--portlet-title-->
                                    <div class="portlet-body form">
                                    
                                    	<div class="table-responsive">
                                            <table class="table table-striped table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                    	<th>#</th>
                                                        <th>Page Title</th>
                                                        <th>Page URL</th>
                                                        <th>Last Edited</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                        
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                	<?php
													$query = "
														SELECT *
														FROM site_pages_templates
														ORDER BY subpage_id ASC
													";
													try{
														$rsGetPages = $mdsLink->prepare($query);
														$aValues = array(
															//':fund_id' 	=> $fundID
														);
														$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
														$rsGetPages->execute($aValues);
													}
													catch(PDOException $error){
														// Log any error
														file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
													}
													
													$cnt = 0;
													
													while($pages = $rsGetPages->fetch(PDO::FETCH_ASSOC)) {
														
														$cnt++;
														
														$subpageID		= $pages['subpage_id'];
														$active 		= $pages['active'];
														$title			= $pages['title'];
														$url			= $pages['custom_url'];
														$timestamp		= $pages['timestamp'];
														
														if($active == '1'){
															$status = '<span class="label label-success">Published</span>';	
														}else{
															$status = '<span class="label label-warning">Not Published</span>';	
														}
														
														?>
														<tr>
                                                        	<td><?php echo $cnt;?></td>
                                                            <td><a href="#edit-page" data-toggle="modal" onclick="editPage('<?php echo $subpageID;?>');"><?php echo $title;?></a></td>
                                                            <td>/page/<?php echo $url;?></td>
                                                            <td><?php echo date('Y/m/d h:i', $timestamp);?></td>
                                                            <td><?php echo $status;?></td>
                                                            <td><a href="#edit-page" data-toggle="modal" class="btn btn-info" onclick="editPage('<?php echo $subpageID;?>');">Edit</a></td>
                                                        </tr>
                                                        <?php														
													}
													?>
                                                    
                                                </tbody>
                                            </table>
										</div><!--table-responsive-->    
                                        
                                        <?php /*?><pre>
                                       	<?php 
										echo $error;
										?>
                                        </pre><?php */?>
                                                  
                                    </div><!--END PORTLET BODY-->
                                </div><!--END PORTLET-->
                            
                            </div><!--END TAB 2-->
                            
                            
                        
                        
                        </div><!--tab-content-->
                    </div><!--tabbable tabbable-custom-->
                    
                </div><!--col-md-12-->
            </div><!--row-->
            <!-- END PAGE CONTENT-->    
   