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
	
	$subpageID 	= $_REQUEST['page'];
	
	$query = "
		SELECT * 
		FROM site_pages_templates
		WHERE subpage_id=:subpage_id
	";
	try{
		$rsGetPage = $mdsLink->prepare($query);
		$aValues = array(
			':subpage_id' 	=> $subpageID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetPage->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
		
	$page = $rsGetPage->fetch(PDO::FETCH_ASSOC);
	
	?>
    
    <div class="modal-header">
       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
       <h4 class="modal-title">Edit Page: <?php echo $page['title'];?></span></h4>
    </div>
    
    <form action="process/ajax/sites/mds-page-processes.php" method="post" name="edit-page-form" class="edit-page-form">
        <div class="modal-body">
            <div style="float:right;margin-bottom:10px;" id="collapse-btn"><button type="button" class="btn btn-xs btn-warning" onclick="collapseShow('collapse');">Collapse Portlets</button></div>
            <div class="clearfix"></div>
            <div class="portlet">
                <div class="portlet-title">
                    <div class="caption"><i class="icon-reorder"></i><?php echo $page['title'];?></div>
                    <div class="tools">
                        <a href="javascript:;" class="collapse"></a>
                    </div>
                </div><!--portlet-title-->
                <div class="portlet-body path-portlet-body path-portlet-body">
                
                    <div class="form-group">
                        <label class="control-label">Page Title</label>
                        <input type="text" class="form-control" name="title" value="<?php echo $page['title'];?>"/>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Custom URL</label>
                        <input type="text" class="form-control" name="url" value="<?php echo $page['custom_url'];?>"/>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Header</label>
                        <textarea class="form-control" rows="3" name="header"><?php echo $page['header'];?></textarea>
                        
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Body</label>
                        <textarea class="form-control" rows="40" name="body"><?php echo $page['body'];?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label"><input type="checkbox" name="active" value="1" <?php if($page['active'] == '1'){echo'checked';}?> /> Publish</label>
                        <span class="help-block">If checked, page will be accessible.</span>
                    </div>
                    
                </div><!--portlet-body-->
            </div><!--portlet-->
           
           	
            
            
        
        </div><!--modal-body-->
        <div class="modal-footer">
            <input type="hidden" value="<?php echo $subpageID;?>" name="subpage_id" />
        
            <input type="submit" value="Save Page" id="submit-page" class="btn btn-info"/>
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        </div>
    
    </form><!--create-topic-->
    
    <div id="save-page-errors"></div>
    
    <?php
		
}



//+-----------------------------------------------------------------------------------------+
//|								Save Page | Process 2
//+-----------------------------------------------------------------------------------------+

if($process == '5'){
	
	$newPage		= $_REQUEST['new-page'];
	
	//Get the variables!
	$subpageID		= $_REQUEST['subpage_id'];
	$title			= $_REQUEST['title'];
	$url			= $_REQUEST['url'];
	$header			= $_REQUEST['header'];
	$body			= $_REQUEST['body'];
	$active			= $_REQUEST['active'];
	
	$aErrors		= array();
	if($active == ''){
		$active = '0';	
	}
	
	
	//update the page record
	if($newPage == '1'){
		
		if(empty($title)){
			$aErrors[] = 'Please provide a title.';	
		}
		
		if(empty($url)){
			$aErrors[] = 'Please provide a URL.';	
		}
		
		if(empty($header)){
			$aErrors[] = 'Please provide a header.';
		}
		
		if(empty($body)){
			$aErrors[] = 'Please provide body content';	
		}
		
		if(empty($aErrors)){
				
			//new page
			$query = "
				INSERT INTO site_pages_templates (
					page_id,
					custom_url,
					title,
					header,
					body,
					active,
					timestamp
				) VALUE (
					'10-00-00-001',
					:url,
					:title,
					:header,
					:body,
					:active,
					UNIX_TIMESTAMP()
				)
			";
			try{
				$rsUpdatePage = $mdsLink->prepare($query);
				$aValues = array(
					':url'			=> $url,
					':title'		=> $title,
					':header'		=> $header,
					':body'			=> $body,
					':active'		=> $active,
					
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdatePage->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			//echo $error;
			
		}else{
			
			echo '<div class="alert alert-danger"><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';	
			}
			echo '</ul></div>';
		}
	}else{
		//edit page
		$query = "
			UPDATE site_pages_templates
			SET	custom_url=:url,
				title=:title,
				header=:header,
				body=:body,
				active=:active,
				timestamp=UNIX_TIMESTAMP()
			WHERE subpage_id=:subpage_id
		";
		try{
			$rsUpdatePage = $mdsLink->prepare($query);
			$aValues = array(
				':subpage_id'	=> $subpageID,
				':url'			=> $url,
				':title'		=> $title,
				':header'		=> $header,
				':body'			=> $body,
				':active'		=> $active,
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdatePage->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}
		
	/*echo '<pre>';
	echo $error;
	print_r($_POST);
	echo '</pre>';*/
	
}

//+-----------------------------------------------------------------------------------------+
//|								New Page | Process 3
//+-----------------------------------------------------------------------------------------+
if($process == '3'){
	
	?>
    
    <div class="modal-header">
       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
       <h4 class="modal-title">New Page</span></h4>
    </div>
    
    <form action="process/ajax/sites/mds-page-processes.php" method="post" name="edit-page-form" class="edit-page-form">
        <div class="modal-body">
            
            <div class="portlet">
                <div class="portlet-title">
                    <div class="caption"><i class="icon-reorder"></i>Create New Page</div>
                    <div class="tools">
                        <a href="javascript:;" class="collapse"></a>
                    </div>
                </div><!--portlet-title-->
                <div class="portlet-body path-portlet-body path-portlet-body">
                
                    <div class="form-group">
                        <label class="control-label">Path Title</label>
                        <input type="text" class="form-control" name="title" value=""/>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Custom URL</label>
                        <input type="text" class="form-control" name="url" value=""/>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Header</label>
                        <textarea class="form-control" rows="3" name="header"></textarea>
                        
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Body</label>
                        <textarea class="form-control" rows="40" name="body"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label"><input type="checkbox" name="active" value="1" checked /> Publish</label>
                        <span class="help-block">If checked, page will be accessible.</span>
                    </div>
                    
                </div><!--portlet-body-->
            </div><!--portlet-->
           
           	
            
            
        
        </div><!--modal-body-->
        <div class="modal-footer">
        	<input type="hidden" value="1" name="new-page" />
            
            <input type="submit" value="Save Page" id="submit-page" class="btn btn-info"/>
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        </div>
    
    </form><!--create-topic-->
    
    <div id="save-page-errors"></div>
    
    <?php
		
}
?>