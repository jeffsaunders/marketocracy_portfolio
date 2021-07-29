<?php
/*
Marketocracy Data Services | Content Management
Admin General HTML

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/mds/mds-path-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/sites-mds-path-scripts.js.php
	HTML		- includes/pages/admin-sites-mds.php
*/

//Start Session
session_start();

// Load debug & error logging functions
require_once("../../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../../secure/dbconnect.php");

// Load System Wide Functions
require_once("../../../includes/system-functions.php");

//Get Process from URL
$process = $_REQUEST['process'];


//+-----------------------------------------------------------------------------------------+
//|								Edit Path | Process 1
//+-----------------------------------------------------------------------------------------+
if($process == '1'){
	
	$pathID 	= $_REQUEST['path'];
	
	$query = "
		SELECT * 
		FROM paths
		WHERE path_id=:path_id
	";
	try{
		$rsGetPaths = $mdsLink->prepare($query);
		$aValues = array(
			':path_id' 	=> $pathID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetPaths->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
		
	$path = $rsGetPaths->fetch(PDO::FETCH_ASSOC);
	
	//Create an array of sub_content_ids
	$aSubContentIDs = array();
	?>
    
    <div class="modal-header">
       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
       <h4 class="modal-title">Edit <?php echo $path['path_name'];?> Path</span></h4>
    </div>
    
    <form action="process/ajax/sites/mds-path-processes.php" method="post" name="edit-path-form" class="edit-path-form">
        <div class="modal-body">
            <div style="float:right;margin-bottom:10px;" id="collapse-btn"><button type="button" class="btn btn-xs btn-warning" onclick="collapseShow('collapse');">Collapse Portlets</button></div>
            <div class="clearfix"></div>
            <div class="portlet">
                <div class="portlet-title">
                    <div class="caption"><i class="icon-reorder"></i>Level 1</div>
                    <div class="tools">
                        <a href="javascript:;" class="collapse"></a>
                    </div>
                </div><!--portlet-title-->
                <div class="portlet-body path-portlet-body path-portlet-body">
                
                    <div class="form-group">
                        <label class="control-label">Path Name</label>
                        <input type="text" class="form-control" name="path_name" value="<?php echo $path['path_name'];?>"/>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Slider Image</label>
                        <input type="text" class="form-control" name="slider_img" value="<?php echo $path['slider_img'];?>"/>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Path Description</label>
                        <textarea class="form-control" rows="7" name="path_desc"><?php echo $path['path_desc'];?></textarea>
                        <span class="help-block">This will show up on the first level of the paths as well as in the slider.</span>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Path Sequence</label>
                        <input type="text" class="form-control" name="path_sequence" value="<?php echo $path['sequence'];?>"/>
                    </div>
                    
                </div><!--portlet-body-->
            </div><!--portlet-->
           
           	<div class="portlet">
                <div class="portlet-title">
                    <div class="caption"><i class="icon-reorder"></i>Level 2</div>
                    <div class="tools">
                        <a href="javascript:;" class="collapse"></a>
                    </div>
                </div><!--portlet-title-->
                <div class="portlet-body path-portlet-body path-portlet-body">
             		
                    <div class="portlet">
                        <div class="portlet-title">
                            <div class="caption"><i class="icon-reorder"></i>Description</div>
                            <div class="tools">
                                <a href="javascript:;" class="expand"></a>
                            </div>
                        </div><!--portlet-title-->
                        <div class="portlet-body path-portlet-body path-portlet-body">
                            <div class="form-group">
                                <label class="control-label">Text</label>
                                <textarea class="form-control" rows="5" name="sub_desc"><?php echo $path['sub_desc'];?></textarea>
                            </div>
                            
                            <div class="portlet">
                                <div class="portlet-title">
                                    <div class="caption"><i class="icon-reorder"></i>Read More Area</div>
                                    <div class="tools">
                                        <a href="javascript:;" class="expand"></a>
                                    </div>
                                </div><!--portlet-title-->
                                <div class="portlet-body display-hide">
                                
                                	<?php
                        
									$pathLink = $pathID.'-header';
									
									echo '<div id="load-extra-rows-'.$pathLink.'">';
									
									$query = "
										SELECT *
										FROM paths_sub_content
										WHERE path_link=:path_link AND active='1'
										ORDER BY sequence
									";
									try{
										$rsGetPaths2 = $mdsLink->prepare($query);
										$aValues = array(
											':path_link' 	=> $pathLink
										);
										$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
										$rsGetPaths2->execute($aValues);
									}
									catch(PDOException $error){
										// Log any error
										file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
									}
									$seq = 0;	
									while($path2 = $rsGetPaths2->fetch(PDO::FETCH_ASSOC)){
										$seq++;
										?>
										<div class="portlet">
                                            <div class="portlet-title">
                                                <div class="caption"><i class="icon-reorder"></i><?php echo $path2['psc_title'];?></div>
                                                <div class="tools">
                                                	<button type="button" class="btn btn-xs btn-danger" onclick="deleteRow('<?php echo $path2['psc_id'];?>','<?php echo $pathLink;?>','<?php echo $path2['psc_title'];?>');" style="margin-top:-10px;">Delete</button>
                                                    <a href="javascript:;" class="expand"></a>
                                                </div>
                                            </div><!--portlet-title-->
                                            <div class="portlet-body display-hide">
                                                <div class="form-group">
                                                    <label class="control-label">Row Title</label>
                                                    <input type="text" class="form-control" name="psc_title_<?php echo $pathLink;?>_<?php echo $path2['psc_id'];?>" value="<?php echo $path2['psc_title'];?>"/>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label class="control-label">Row Column Text</label>
                                                    <textarea class="form-control" rows="5" name="psc_column_text_<?php echo $pathLink;?>_<?php echo $path2['psc_id'];?>"><?php echo $path2['psc_column_text'];?></textarea>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label class="control-label">Row Content</label>
                                                    <textarea class="form-control" rows="10" name="psc_content_<?php echo $pathLink;?>_<?php echo $path2['psc_id'];?>"><?php echo $path2['psc_content'];?></textarea>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label class="control-label">Row Sequence</label>
                                                    <input type="text" class="form-control" name="psc_sequence_<?php echo $pathLink;?>_<?php echo $path2['psc_id'];?>" value="<?php echo $path2['sequence'];?>"/>
                                                </div>
											</div><!--portlet-body-->
                                        </div><!--portlet-->
										<?php	
										
										$aSubContentIDs[] =	$pathLink.'_'.$path2['psc_id'];			
									}
									$seq++
									?>
                                    </div><!--load-extra-rows-->
                                    
                                    <div id="show-add-row-error-<?php echo $pathLink;?>"></div>
                                                
                                    <div class="portlet">
                                        <div class="portlet-title">
                                            <div class="caption"><i class="icon-plus"></i> Add Row</div>
                                            <div class="tools">
                                                <a href="javascript:;" class="expand"></a>
                                            </div>
                                        </div><!--portlet-title-->
                                        <div class="portlet-body display-hide">
                                                
                                                <div class="form-group">
                                                    <label class="control-label">Row Title</label>
                                                    <input type="text" class="form-control" name="new_psc_title_<?php echo $pathLink;?>" id="new_psc_title_<?php echo $pathLink;?>" />
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label class="control-label">Row Column Text</label>
                                                    <textarea class="form-control" rows="5" name="new_psc_column_text_<?php echo $pathLink;?>" id="new_psc_column_text_<?php echo $pathLink;?>"></textarea>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label class="control-label">Row Content</label>
                                                    <textarea class="form-control" rows="10" name="new_psc_content_<?php echo $pathLink;?>" id="new_psc_content_<?php echo $pathLink;?>"></textarea>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label class="control-label">Row Sequence</label>
                                                    <input type="text" class="form-control" value="<?php echo $seq;?>" name="new_psc_sequence_<?php echo $pathLink;?>" id="new_psc_sequence_<?php echo $pathLink;?>" />
                                                </div>
                                                
                                                
                                                <input type="hidden" value="<?php echo $pathLink;?>" id="new-path-link" />
                                                
                                                <button type="button" class="btn btn-info btn-sm" onclick="addNewRow('<?php echo $pathLink;?>');">Add Row</button>
                                        </div><!--portlet-body-->
                                        
                                    </div><!--portlet-->
                                
                            	</div><!--portlet-body-->
                            </div><!--portlet-->
                            
                            
                        </div><!--portlet-body-->
                    </div><!--portlet-->     
                    
                    <?php
					for($i = 1; $i <= 3; $i++){
						?>
                        <div class="portlet">
                            <div class="portlet-title">
                                <div class="caption"><i class="icon-reorder"></i>Point <?php echo $i;?> - <?php echo $path['point_'.$i.'_title'];?></div>
                                <div class="tools">
                                    <a href="javascript:;" class="expand"></a>
                                </div>
                            </div><!--portlet-title-->
                            <div class="portlet-body display-hide">
                            	<div class="form-group">
                                    <label class="control-label">Title</label>
                                    <input type="text" class="form-control" name="point_<?php echo $i;?>_title" value="<?php echo $path['point_'.$i.'_title'];?>"/>
                                </div>
                                
                                <div class="form-group">
                                    <label class="control-label">Text</label>
                                    <textarea class="form-control" rows="5" name="point_<?php echo $i;?>_text"><?php echo $path['point_'.$i.'_text'];?></textarea>
                                </div>
                                
                                <?php //START LEVEL 3?>
                                <div class="portlet">
                                    <div class="portlet-title">
                                        <div class="caption"><i class="icon-reorder"></i>Learn More Area</div>
                                        <div class="tools">
                                            <a href="javascript:;" class="expand"></a>
                                        </div>
                                    </div><!--portlet-title-->
                                    <div class="portlet-body display-hide">
                                    
                                    	<div class="form-group">
                                            <label class="control-label">Title</label>
                                            <input type="text" class="form-control" name="sub_point_<?php echo $i;?>_title" value="<?php echo $path['sub_point_'.$i.'_title'];?>"/>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="control-label">Text (HTML Allowed)</label>
                                            <textarea class="form-control" rows="10" name="sub_point_<?php echo $i;?>_text"><?php echo $path['sub_point_'.$i.'_text'];?></textarea>
                                        </div>
                                        
                                        <div class="portlet">
                                            <div class="portlet-title">
                                                <div class="caption"><i class="icon-reorder"></i>Extra Rows</div>
                                                <div class="tools">
                                                    <a href="javascript:;" class="expand"></a>
                                                </div>
                                            </div><!--portlet-title-->
                                            <div class="portlet-body display-hide">
                                            	
                                                
                                            	<?php
                        
												$pathLink = $pathID.'-'.$i;
												
												echo '<div id="load-extra-rows-'.$pathLink.'">';
												
												$query = "
													SELECT *
													FROM paths_sub_content
													WHERE path_link=:path_link AND active='1'
													ORDER BY sequence
												";
												try{
													$rsGetPaths3 = $mdsLink->prepare($query);
													$aValues = array(
														':path_link' 	=> $pathLink
													);
													$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
													$rsGetPaths3->execute($aValues);
												}
												catch(PDOException $error){
													// Log any error
													file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
												}
												
												$seq = 0;	
												while($path3 = $rsGetPaths3->fetch(PDO::FETCH_ASSOC)){
													$seq++;
													?>
													<div class="portlet">
                                                        <div class="portlet-title">
                                                            <div class="caption"><i class="icon-reorder"></i><?php echo $path3['psc_title'];?></div>
                                                            <div class="tools">
                                                            	<button type="button" class="btn btn-xs btn-danger" onclick="deleteRow('<?php echo $path3['psc_id'];?>','<?php echo $pathLink;?>','<?php echo $path3['psc_title'];?>');" style="margin-top:-10px;">Delete</button>
                                                                <a href="javascript:;" class="expand"></a>
                                                            </div>
                                                        </div><!--portlet-title-->
                                                        <div class="portlet-body display-hide">
                                                            <div class="form-group">
                                                                <label class="control-label">Row Title</label>
                                                                <input type="text" class="form-control" name="psc_title_<?php echo $pathLink;?>_<?php echo $path3['psc_id'];?>" value="<?php echo $path3['psc_title'];?>"/>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label class="control-label">Row Column Text</label>
                                                                <textarea class="form-control" rows="5" name="psc_column_text_<?php echo $pathLink;?>_<?php echo $path3['psc_id'];?>"><?php echo $path3['psc_column_text'];?></textarea>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label class="control-label">Row Content</label>
                                                                <textarea class="form-control" rows="10" name="psc_content_<?php echo $pathLink;?>_<?php echo $path3['psc_id'];?>"><?php echo $path3['psc_content'];?></textarea>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label class="control-label">Row Sequence</label>
                                                                <input type="text" class="form-control" name="psc_sequence_<?php echo $pathLink;?>_<?php echo $path3['psc_id'];?>" value="<?php echo $path3['sequence'];?>"/>
                                                            </div>
                                                    	</div><!--portlet-body-->
													</div><!--portlet-->
													<?php
													$aSubContentIDs[] =	$pathLink.'_'.$path3['psc_id'];					
												}
												
												$seq++
												?>
                                                </div><!--load-extra-rows-->
                                                
                                                <div id="show-add-row-error-<?php echo $pathLink;?>"></div>
                                                
                                                <div class="portlet">
                                                    <div class="portlet-title">
                                                        <div class="caption"><i class="icon-plus"></i> Add Row</div>
                                                        <div class="tools">
                                                            <a href="javascript:;" class="expand"></a>
                                                        </div>
                                                    </div><!--portlet-title-->
                                                    <div class="portlet-body display-hide">
                                                    		
                                                    		<div class="form-group">
                                                                <label class="control-label">Row Title</label>
                                                                <input type="text" class="form-control" name="new_psc_title_<?php echo $pathLink;?>" id="new_psc_title_<?php echo $pathLink;?>" />
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label class="control-label">Row Column Text</label>
                                                                <textarea class="form-control" rows="5" name="new_psc_column_text_<?php echo $pathLink;?>" id="new_psc_column_text_<?php echo $pathLink;?>"></textarea>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label class="control-label">Row Content</label>
                                                                <textarea class="form-control" rows="10" name="new_psc_content_<?php echo $pathLink;?>" id="new_psc_content_<?php echo $pathLink;?>"></textarea>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label class="control-label">Row Sequence</label>
                                                                <input type="text" class="form-control" value="<?php echo $seq;?>" name="new_psc_sequence_<?php echo $pathLink;?>" id="new_psc_sequence_<?php echo $pathLink;?>" />
                                                            </div>
                                                    		
                                                            
                                                            <input type="hidden" value="<?php echo $pathLink;?>" id="new-path-link" />
                                                            
                                                            <button type="button" class="btn btn-info btn-sm" onclick="addNewRow('<?php echo $pathLink;?>');">Add Row</button>
                                                    </div><!--portlet-body-->
                                                    
                                            	</div><!--portlet-->
                                            
                                            </div><!--portlet-body-->
                                        </div><!--portlet-->
                                    
                                	</div><!--portlet-body-->
            					</div><!--portlet-->
                                
                                
                            </div><!--portlet-body-->
            			</div><!--portlet-->
                        <?php	
					}
					?>
                    
                    
            	</div><!--portlet-body-->
            </div><!--portlet-->
            
            
        
        </div><!--modal-body-->
        <?php $subContentIDs = implode('|',$aSubContentIDs);?>
        <div class="modal-footer">
        	<input type="hidden" value="<?php echo $subContentIDs;?>" name="sub_content_ids" />
            <input type="hidden" value="<?php echo $pathID;?>" name="path_id" />
        
            <input type="submit" value="Save Path" id="submit-path" class="btn btn-info"/>
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        </div>
    
    </form><!--create-topic-->
    
    <div id="save-path-errors"></div>
    
    <?php
		
}

//+-----------------------------------------------------------------------------------------+
//|								Add Row For Points | Process 2
//+-----------------------------------------------------------------------------------------+
if($process == '2'){
	
	$pathLink 		= $_REQUEST['pathLink'];
	$rowTitle		= $_REQUEST['new_psc_title_'.$pathLink];
	$columnText		= $_REQUEST['new_psc_column_text_'.$pathLink];
	$rowContent		= $_REQUEST['new_psc_content_'.$pathLink];
	$rowSeq			= $_REQUEST['new_psc_sequence_'.$pathLink];
	
	$aErrors		= array();
	
	if(empty($rowTitle)){
		$aErrors[] = 'Please provide a Row Title';	
	}
	
	if(empty($rowContent)){
		$aErrors[] = 'Please provide Row Content';	
	}
	
	if(empty($rowSeq)){
		$aErrors[] = 'Please provide a sequence number';	
	}else{
		if(!is_numeric($rowSeq)){
			$aErrors[] = 'Row Sequence can only be a number';	
		}
	}
	
	if(empty($aErrors)){
		//echo $pathLink.'|'.$rowTitle.'|'.$columnText.'|'.$rowContent.'|'.$rowSeq;
		
		$query = "
			INSERT INTO paths_sub_content (
				path_link,
				psc_title,
				psc_column_text,
				psc_content,
				sequence,
				active,
				timestamp
			) VALUE (
				:path_link,
				:title,
				:column_text,
				:content,
				:sequence,
				'1',
				UNIX_TIMESTAMP()
			)
		";
		try{
			$rsAddRow = $mdsLink->prepare($query);
			$aValues = array(
				':path_link' 	=> $pathLink,
				':title'		=> $rowTitle,
				':column_text'	=> $columnText,
				':content'		=> $rowContent,
				':sequence'		=> $rowSeq
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsAddRow->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
	}else{
		
		echo '<div class="alert alert-danger"><ul>';
		foreach($aErrors as $key=>$error){
			echo '<li>'.$error.'</li>';	
		}
		echo '</ul></div>';
			
	}
	
	
}


//+-----------------------------------------------------------------------------------------+
//|								Load Rows | Process 3
//+-----------------------------------------------------------------------------------------+

if($process == '3'){
                        
	$pathLink = $_REQUEST['pathLink'];
	
	$query = "
		SELECT *
		FROM paths_sub_content
		WHERE path_link=:path_link AND active='1'
		ORDER BY sequence
	";
	try{
		$rsGetPaths3 = $mdsLink->prepare($query);
		$aValues = array(
			':path_link' 	=> $pathLink
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetPaths3->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$seq = 0;	
	while($path3 = $rsGetPaths3->fetch(PDO::FETCH_ASSOC)){
		$seq++;
		?>
		<div class="portlet">
			<div class="portlet-title">
				<div class="caption"><i class="icon-reorder"></i><?php echo $path3['psc_title'];?> </div>
				<div class="tools">
                	<button type="button" class="btn btn-xs btn-danger" onclick="deleteRow('<?php echo $path3['psc_id'];?>','<?php echo $pathLink;?>','<?php echo $path3['psc_title'];?>');" style="margin-top:-10px;">Delete</button>
					<a href="javascript:;" class="expand"></a>
				</div>
			</div><!--portlet-title-->
			<div class="portlet-body display-hide">
				<div class="form-group">
					<label class="control-label">Row Title</label>
					<input type="text" class="form-control" name="psc_title_<?php echo $pathLink;?>_<?php echo $path3['psc_id'];?>" value="<?php echo $path3['psc_title'];?>"/>
				</div>
				
				<div class="form-group">
					<label class="control-label">Row Column Text</label>
					<textarea class="form-control" rows="5" name="psc_column_text_<?php echo $pathLink;?>_<?php echo $path3['psc_id'];?>"><?php echo $path3['psc_column_text'];?></textarea>
				</div>
				
				<div class="form-group">
					<label class="control-label">Row Content</label>
					<textarea class="form-control" rows="10" name="psc_content_<?php echo $pathLink;?>_<?php echo $path3['psc_id'];?>"><?php echo $path3['psc_content'];?></textarea>
				</div>
				
				<div class="form-group">
					<label class="control-label">Row Sequence</label>
					<input type="text" class="form-control" name="psc_sequence_<?php echo $pathLink;?>_<?php echo $path3['psc_id'];?>" value="<?php echo $path3['sequence'];?>"/>
				</div>
			</div><!--portlet-body-->
		</div><!--portlet-->
		<?php					
	}	
}

//+-----------------------------------------------------------------------------------------+
//|								Delete Row | Process 4
//+-----------------------------------------------------------------------------------------+

if($process == '4'){
	
	$pathLink	= $_REQUEST['pathLink'];
	$pscID		= $_REQUEST['pscID'];
	
	$error = '';
	
	$query 		= "
		UPDATE paths_sub_content
		SET active='0'
		WHERE psc_id=:psc_id
	";
	try{
		$rsUpdateRow = $mdsLink->prepare($query);
		$aValues = array(
			':psc_id' 	=> $pscID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsUpdateRow->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$error = trim($error);
	
	/*if($error == '' || $error == NULL){
		?>
        <div class="alert alert-danger">
        	<p><?php echo $error;?></p>
            <p><?php echo $preparedQuery;?></p>
        </div>
        <?php	
	}*/
	//echo $pathLink.'|'.$pscID;
	
	
}

//+-----------------------------------------------------------------------------------------+
//|								Save Path | Process 5
//+-----------------------------------------------------------------------------------------+

if($process == '5'){
	
	//Get the variables!
	$pathID				= $_REQUEST['path_id'];
	
	$pathName			= $_REQUEST['path_name'];
	$sliderImg			= $_REQUEST['slider_img'];
	$pathDesc			= $_REQUEST['path_desc'];
	$subDesc			= $_REQUEST['sub_desc'];
	
	$point1Title		= $_REQUEST['point_1_title'];
	$point1Text			= $_REQUEST['point_1_text'];
	$subPoint1Title		= $_REQUEST['sub_point_1_title'];
	$subPoint1Text		= $_REQUEST['sub_point_1_text'];
	
	$point2Title		= $_REQUEST['point_2_title'];
	$point2Text			= $_REQUEST['point_2_text'];
	$subPoint2Title		= $_REQUEST['sub_point_2_title'];
	$subPoint2Text		= $_REQUEST['sub_point_2_text'];
	
	$point3Title		= $_REQUEST['point_3_title'];
	$point3Text			= $_REQUEST['point_3_text'];
	$subPoint3Title		= $_REQUEST['sub_point_3_title'];
	$subPoint3Text		= $_REQUEST['sub_point_3_text'];
	
	$pathSequence		= $_REQUEST['path_sequence'];
	
	$aSubContentIDs		= explode('|',$_REQUEST['sub_content_ids']);
	
	//Update the Sub content rows first
	foreach($aSubContentIDs as $key=>$subContentID){
		
		$aSubContentID = explode('_', $subContentID);
		
		$pathLink 	= $aSubContentID[0];
		$pscID		= $aSubContentID[1];
		
		$query = "
			UPDATE paths_sub_content
			SET psc_title=:psc_title, psc_column_text=:psc_column_text, psc_content=:psc_content, sequence=:sequence, timestamp=UNIX_TIMESTAMP()
			WHERE psc_id=:psc_id
		";
		try{
			$rsSubContent = $mdsLink->prepare($query);
			$aValues = array(
				':psc_id' 			=> $pscID,
				':psc_title'		=> $_REQUEST['psc_title_'.$subContentID],
				':psc_column_text'	=> $_REQUEST['psc_column_text_'.$subContentID],
				':psc_content'		=> $_REQUEST['psc_content_'.$subContentID],
				':sequence'			=> $_REQUEST['psc_sequence_'.$subContentID],
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsSubContent->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
			
	}
	
	//update the path record
	$query = "
		UPDATE paths
		SET	path_name=:path_name,
			path_desc=:path_desc,
			slider_img=:slider_img,
			sub_desc=:sub_desc,
			point_1_title=:point_1_title,
			point_1_text=:point_1_text,
			sub_point_1_title=:sub_point_1_title,
			sub_point_1_text=:sub_point_1_text,
			point_2_title=:point_2_title,
			point_2_text=:point_2_text,
			sub_point_2_title=:sub_point_2_title,
			sub_point_2_text=:sub_point_2_text,
			point_3_title=:point_3_title,
			point_3_text=:point_3_text,
			sub_point_3_title=:sub_point_3_title,
			sub_point_3_text=:sub_point_3_text,
			sequence=:sequence,
			timestamp=UNIX_TIMESTAMP()
		WHERE path_id=:path_id
	";
	try{
		$rsUpdatePath = $mdsLink->prepare($query);
		$aValues = array(
			':path_id' 				=> $pathID,
			':path_name'			=> $pathName,
			':path_desc'			=> $pathDesc,
			':slider_img'			=> $sliderImg,
			':sub_desc'				=> $subDesc,
			':point_1_title'		=> $point1Title,
			':point_1_text'			=> $point1Text,
			':sub_point_1_title'	=> $subPoint1Title,
			':sub_point_1_text'		=> $subPoint1Text,
			':point_2_title'		=> $point2Title,
			':point_2_text'			=> $point2Text,
			':sub_point_2_title'	=> $subPoint2Title,
			':sub_point_2_text'		=> $subPoint2Text,
			':point_3_title'		=> $point3Title,
			':point_3_text'			=> $point3Text,
			':sub_point_3_title'	=> $subPoint3Title,
			':sub_point_3_text'		=> $subPoint3Text,
			':sequence'				=> $pathSequence
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsUpdatePath->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	/*echo '<pre>';
	print_r($_POST);
	echo '</pre>';*/
	
}
?>