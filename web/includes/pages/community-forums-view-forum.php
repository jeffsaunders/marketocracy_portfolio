<?php


//Get info for current forum
$query = "
	SELECT *
	FROM ".$_SESSION['forums_table']."
	WHERE forum_id=:forum_id
";
try{
	$rsGetForum = $mLink->prepare($query);
	$aValues = array(
		':forum_id' => $forumID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsGetForum->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}
$forum = $rsGetForum->fetch(PDO::FETCH_ASSOC);

$forumActive = $forum['active'];

//Explode groupIDs into an array to process later
if($forum['allow_forum_groups'] != NULL){
	$aForumGroups	= explode("|",$forum['allow_forum_groups']);
}else{
	$aForumGroups 	= $forum['allow_forum_groups'];
}
//Explode member flags into an array to process later
if($forum['allow_members_flags'] != NULL){
	$aMemberFlags = explode("|",$forum['allow_members_flags']);
}else{
	$aMemberFlags = $forum['allow_members_flags'];	
}
//Explode member flags into an array to process later
if($forum['allow_members'] != NULL){
	$aMembers = explode("|",$forum['allow_members']);
}else{
	$aMembers = $forum['allow_members'];	
}

if($forum['allow_members_flags'] == NULL && $forum['allow_forum_groups'] == NULL && $forum['allow_members'] == NULL){
	$checkEveryone = 'checked';	
}else{
	$checkEveryone = '';	
}
?>

        <!-- BEGIN CREATE CATEGORY MODAL-->
        <div class="modal fade" id="create-category" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog">
             <div class="modal-content">
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Create New Category For <span class="label label-info"><?php echo $forumMainTitle; ?></span></h4>
                </div>
                
                <form action="process/ajax/community-forum-processes.php?process=8" method="post" name="create-cat" class="create-cat">
                    <div class="modal-body">
                        	<div id="createCatUserError"></div>
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="control-label">Category Title * <span id="cat_title-help"></span></label>
                                    <input type="text" class="form-control" name="cat_title" id="cat_title" />
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Description * <span id="cat_desc-help"></span></label>
                                    <textarea class="form-control" name="cat_desc" id="cat_desc" row="5" style="resize:vertical;"></textarea>
                                </div>
                                
                                <div class="form-group">
                                	<label class="control-label">Stock Symbol</label>
                                    <input type="text" class="form-control" name="search-symbol" id="search-symbol" />
                                    <span class="help-block">If this category is about a single stock, use this field.</span>
                                </div>
                                
                                <input type="hidden" name="forum_id" value="<?php echo $forumID; ?>" />
                            </div><!--form-body-->
                        
                    </div><!--modal-body-->
                    
                    <div class="modal-footer">
                    	<input type="submit" value="Create Category" id="submit-cat" class="btn btn-info" style="display:none;"/>
                       	<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    </div>
                
                </form><!--create-topic-->
             </div>
             <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- END CREATE CATEGORY MODAL-->
        
        <!-- BEGIN CREATE TOPIC MODAL-->
        <div class="modal fade" id="create-topic" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog">
             <div class="modal-content">
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Create New Stock Discussion</h4>
                </div>
                
                <form action="process/ajax/community-forum-processes.php?process=15" method="post" name="create-topic" class="create-topic">
                    <div class="modal-body">
                            <div id="createTopicUserError"></div>
                            <div class="form-body">
                            	<div class="form-group">
                                	<label class="control-label">Stock Symbol * <span id="symbol-help"></span> <span id="symbol-check"></span></label>
                                    <input type="text" class="form-control" name="symbol" id="symbol" /><input type="hidden" value="" id="symbol-extra" />
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Topic Title * <span id="topic_title-help"></span></label><span id="symbol-check"></span>
                                    <input type="text" class="form-control" name="topic_title" id="topic_title" />
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Post * <span id="topic_post-help"></span></label>
                                    <textarea class="form-control wysihtml5" name="topic_post" id="topic_post" row="5" style="resize:vertical;"></textarea>
                                </div>
                                <span>* Required Fields</span>
                                
                            </div><!--form-body-->
                        
                    </div><!--modal-body-->
                    
                    <div class="modal-footer">
                        <input type="submit" value="Create Topic" class="btn btn-info" id="submit-topic" style="display:none;"/>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    </div>
                
                </form><!--create-topic-->
             </div>
             <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- END CREATE TOPIC MODAL-->
        
        <!-- BEGIN PAGE CONTENT-->
        <div class="row">
            <div class="col-md-12">
            	
                <?php
				$forumError = $_REQUEST['forumError'];
				
				if($forumError != ''){
				
					switch($forumError){
						case '2': $noteType = 'warning';$noteTitle = 'Access Denied'; $noteMessage = 'You do not have access this category. Please select a Category from the list below.';break;
					}
					
					?>
                    <div class="note note-<?php echo $noteType;?>">
                    	<h4 class="block"><?php echo $noteTitle;?></h4>
                   		<p><?php echo $noteMessage;?></p>
                    </div><!--note-->
                    <?php
					
				}
				?>
                
                <?php
				if($adminAccess == 1){
					
					switch($forumActive){
						case '1': $noteType = 'success';$noteMsg = 'Forum Is Active';break;
						case '0': $noteType = 'danger';$noteMsg = 'Forum Is NOT Active';break;	
					}
					?>
                    <div class="note note-<?php echo $noteType;?>">
                    	<p><strong><?php echo $noteMsg;?></strong></p>
                    </div>
                    <?php					
				}
				?>
            
             	<a class="btn btn-info btn-sm bump-down" href="?page=04-01-00-001"><i class="icon-arrow-left"></i> Back to Forums</a>
                <!-- BEGIN RECENT ORDERS TABLE-->
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption"><i class="icon-comments"></i><?php echo $forumMainTitle; ?></div>
                        <div class="actions">
                            <?php
                            if($forumID == '8'){
                                echo '<a class="btn btn-info" href="#create-topic" data-toggle="modal">Add Stock Discussion</a>';	
                            }
                            
                            if($adminAccess == 1){
                            ?>
                                <a class="btn btn-info" href="#create-category" data-toggle="modal">Create Category</a>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                    <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover table-full-width forum-cat" id="forum-cat">
                            <thead>
                                <tr>
                                    
                                    <th>Categories</th>
                                    
                                    <th width="10%">Last Post</th>
                                    <th width="2%">Topics</th>
                                    <th style="display:none;"></th>
                                    
                                </tr>
                            </thead>
                            <tbody class="load-categories">
                                <tr>
                                	<td><img src="<?php echo $_SESSION['site_root'];?>images/loading.gif" /></td>
                                	<td></td>
                                	<td></td>
                                	<td style="display:none;">0</td>
                                </tr>
								<?php 
                                
                                /*$query = "
                                    SELECT *
                                    FROM ".$_SESSION['forum_categories_table']."
                                    WHERE forum_id=:forum_id AND active='1'
                                    ORDER BY sequence ASC
                                ";
                                
                                try{
									$rsCat = $mLink->prepare($query);
									$aValues = array(
										':forum_id' 	=> $forumID
									);
									$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
									$rsCat->execute($aValues);
								}
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}
								
								while($cat = $rsCat->fetch(PDO::FETCH_ASSOC)) {
									
									$catID = $cat['cat_id'];
									
									//START Get Number Of Topics
									$query = "
										SELECT cat_id
										FROM ".$_SESSION['forum_topics_table']."
										WHERE cat_id=:cat_id
									";
									
									try{
										$rsCntCat = $mLink->prepare($query);
										$aValues = array(
											':cat_id' 	=> $catID
										);
										$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
										$rsCntCat->execute($aValues);
									}
									catch(PDOException $error){
										// Log any error
										file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
									}
									
									$cntTopics = $rsCntCat->rowCount();
									//END Get Number Of Topics
										
									//START GET LAST POST	
									//Query Posts table and grab the most recent post of all provided categories
									$query = "
										SELECT *
										FROM ".$_SESSION['forum_posts_table']."
										WHERE cat_id=:cat_id
										ORDER BY timestamp DESC
										LIMIT 1
									";
									
									try{
										$rsGetPosts = $mLink->prepare($query);
										$aValues = array(
											':cat_id' 	=> $catID
										);
										$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
										$rsGetPosts->execute($aValues);
									}
									catch(PDOException $error){
										// Log any error
										file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
									}
											
									$post = $rsGetPosts->fetch(PDO::FETCH_ASSOC);
									
									//Set Vars
									$postID 		= $post['post_id'];
									$postDate 		= $post['timestamp'];
									$postUser		= get_member($mLink, $post['post_creator'], $_SESSION['user_ident']);
									$lastPostURL 	= get_post_url($mLink, $postID);
									
									
									if($postID != ""){
										//If postID is not empty/blank post link			
										$lastPost = '<a href="'.$lastPostURL.'" style="display:block;"><span style="display:none;">'.$postDate.'</span>'.time_past($postDate, 'day').'<br /><small>by '.$postUser.'</small></a>';	
									}else{
										//If postID is empty/blank, do not post link
										$lastPost = '<span class="label label-info">No Posts</span>';	
									}
									//END GET LAST POST
									
									if($adminAccess == 1){
										$adminButton = '
											<div class="btn-group" style="float:left;margin-right:10px;">
												<button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" ><i class="icon-cog"></i></button>
												
												<ul class="dropdown-menu" role="menu">
												   <li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-danger btn-xs btn-full" onclick="activate(\'false\',\'category\',\''.$catID.'\');"><i class="icon-remove"></i> Remove Category</button></li>
												   <!--<li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-info btn-xs btn-full" onclick="window.location.href=\'?page=04-01-00-002&forum='.$forumID.'#forum-settings\';"><i class="icon-pencil"></i> Edit Forum</button></li>-->
												</ul>
												
											 </div><!--button-group-->
										';
									}
									
									$allowAccess = forumAccess($mLink, $catID, 'category');
																	
									if($allowAccess != 0){							
										?>
										<tr>
											
											<td><?php echo $adminButton;?><a href="?page=04-01-00-003&forum=<?php echo $cat['forum_id'];?>&cat=<?php echo $cat['cat_id']; ?>" style="display:block;float:left;"><strong><?php echo $cat['cat_title'];?></strong><br /><small><?php echo substr($cat['cat_description'], 0, 100); ?></small></a></td>
											<td><span style="display:none;"><?php echo $postDate;?></span><?php echo $lastPost;?></td>
											<td><a href="?page=04-01-00-003&forum=<?php echo $cat['forum_id'];?>&cat=<?php echo $cat['cat_id']; ?>" style="display:block;"><?php echo $cntTopics;?></a></td>
											<td style="display:none;"><?php echo $cat['sequence']; ?></td>
										</tr>
										<?php
									}
								}*/
                                ?>
                                
                            </tbody>
                            </table>
                    </div>
                </div>
                <!-- END CATEGORIES TABLE-->
                <a class="btn btn-info btn-sm bump-down" href="?page=04-01-00-001" style="display:block;margin:0px 0px 20px 0px;width:121px;"><i class="icon-arrow-left"></i> Back to Forums</a>
                
                <?php if($adminAccess != 0){?>
                <!--START INACTIVE FORUM TABLE-->
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption"><i class="icon-comments"></i>Inactive Categories</div>
                        <div class="tools">
                            <a href="" class="collapse" title="Expand/Collapse Portlet"></a>
                        </div>
                    </div>
                    <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover table-full-width" id="forum-inactive-list">
                            <thead>
                                <tr>
                                    
                                    <th>Categroies</th>
                                    
                                    <th width="20%">Last Post</th>
                                    <th width="2%">Topics</th>
                                    <th style="display:none;"></th>
                                    
                                </tr>
                            </thead>
                            <tbody class="load-inactive-cats">
                                <?php 
                                
                                $query = "
									SELECT *
									FROM ".$_SESSION['forum_categories_table']."
									WHERE forum_id=:forum_id AND active='0'
									ORDER BY sequence ASC
								";
								
								try{
									$rsCat = $mLink->prepare($query);
									$aValues = array(
										':forum_id' 	=> $forumID
									);
									$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
									$rsCat->execute($aValues);
								}
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}
								
								while($cat = $rsCat->fetch(PDO::FETCH_ASSOC)) {
									
									$catID = $cat['cat_id'];
									
									//START Get Number Of Topics
									$query = "
										SELECT cat_id
										FROM ".$_SESSION['forum_topics_table']."
										WHERE cat_id=:cat_id
									";
									
									try{
										$rsCntCat = $mLink->prepare($query);
										$aValues = array(
											':cat_id' 	=> $catID
										);
										$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
										$rsCntCat->execute($aValues);
									}
									catch(PDOException $error){
										// Log any error
										file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
									}
									
									$cntTopics = $rsCntCat->rowCount();
									//END Get Number Of Topics
										
									//START GET LAST POST	
									//Query Posts table and grab the most recent post of all provided categories
									$query = "
										SELECT *
										FROM ".$_SESSION['forum_posts_table']."
										WHERE cat_id=:cat_id
										ORDER BY timestamp DESC
										LIMIT 1
									";
									
									try{
										$rsGetPosts = $mLink->prepare($query);
										$aValues = array(
											':cat_id' 	=> $catID
										);
										$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
										$rsGetPosts->execute($aValues);
									}
									catch(PDOException $error){
										// Log any error
										file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
									}
											
									$post = $rsGetPosts->fetch(PDO::FETCH_ASSOC);
									
									//Set Vars
									$postID 		= $post['post_id'];
									$postDate 		= $post['timestamp'];
									$postUser		= get_member($mLink, $post['post_creator'], $_SESSION['user_ident']);
									$lastPostURL 	= get_post_url($mLink, $postID);
									
									
									if($postID != ""){
										//If postID is not empty/blank post link			
										$lastPost = '<a href="'.$lastPostURL.'" style="display:block;"><span style="display:none;">'.$postDate.'</span>'.time_past($postDate, 'day').'<br /><small>by '.$postUser.'</small></a>';	
									}else{
										//If postID is empty/blank, do not post link
										$lastPost = '<span class="label label-info">No Posts</span>';	
									}
									//END GET LAST POST
									
									if($adminAccess == 1){
										$adminButton = '
											<div class="btn-group" style="float:left;margin-right:10px;">
												<button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" ><i class="icon-cog"></i></button>
												
												<ul class="dropdown-menu" role="menu">
												   <li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-success btn-xs btn-full" onclick="activate(\'true\',\'category\',\''.$catID.'\');"><i class="icon-remove"></i> Activate Category</button></li>
												   <!--<li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-info btn-xs btn-full" onclick="window.location.href=\'?page=04-01-00-002&forum='.$forumID.'#forum-settings\';"><i class="icon-pencil"></i> Edit Forum</button></li>-->
												</ul>
												
											 </div><!--button-group-->
										';
									}
									
									$allowAccess = forumAccess($mLink, $catID, 'category');
																	
									if($allowAccess != 0){							
										?>
										<tr>
											
											<td><?php echo $adminButton;?><a href="?page=04-01-00-003&forum=<?php echo $cat['forum_id'];?>&cat=<?php echo $cat['cat_id']; ?>" style="display:block;float:left;"><strong><?php echo $cat['cat_title'];?></strong><br /><small><?php echo substr($cat['cat_description'], 0, 100); ?></small></a></td>
											<td><span style="display:none;"><?php echo $postDate;?></span><?php echo $lastPost;?></td>
											<td><a href="?page=04-01-00-003&forum=<?php echo $cat['forum_id'];?>&cat=<?php echo $cat['cat_id']; ?>" style="display:block;"><?php echo $cntTopics;?></a></td>
											<td style="display:none;"><?php echo $cat['sequence']; ?></td>
										</tr>
										<?php
									}
								}
                                ?>
                                
                            </tbody>
                            </table>
                            
                            
                           
                    </div><!--portlet-body-->
                </div><!--end portlet-->
                <!-- END INACTIVE FORUM TABLE-->
                <?php } ?>
                
                
                <?php if($adminAccess == 1){?><br /><br />
                <!-- BEGIN FORUM SETTINGS TABLE-->
                <div class="portlet" id="forum-settings">
                    <div class="portlet-title">
                        <div class="caption"><i class="icon-gear"></i>Forum Settings</div>
                        <div class="tools">
                            <a href="" class="collapse" title="Expand/Collapse Portlet"></a>
                        </div>
                    </div><!--portlet-title-->
                    <div class="portlet-body">
                    
                    	<div class="row">
                        	
                            <div class="col-md-12">
                            	
                            	<div class="portlet">
                                    <div class="portlet-title">
                                        <div class="caption"><i class="icon-lock"></i>Allow Access</div>
                                        <div class="tools">
                                            <a href="" class="collapse balloon" title="Expand/Collapse Portlet"></a>
                                        </div>
                                    </div><!--portlet-title-->
                                    <div class="portlet-body" id="select_container">
                                    	
                                        <form action="" method="post" name="save_allow_access" id="save_allow_access">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <br />
                                                <label><input type="checkbox" name="allow_all" onclick="showHideAccess();" id="allow_all" <?php echo $checkEveryone;?> /> <strong>Grant Access to Everyone</strong></label>
                                                <hr />
                                            </div>
										</div><!--row-->
                                                                                
                                        <div class="row" id="access_types">
                                            
                                        	<div class="col-md-4">
                                            	
                                                <div class="form-group">
                                                    <label class="control-label"><strong>Groups</strong></label>
                                                        <select name="select_groups[]" class="multi-select" multiple="" id="select_groups" >
                                                            <?php
                                                            //Get all forum Groups
                                                            $query = "
                                                                SELECT *
                                                                FROM ".$_SESSION['forum_groups_table']."
                                                                WHERE active='1'
                                                                ORDER BY group_name ASC
                                                            ";
                                                            try{
                                                                $rsGetGroups = $mLink->prepare($query);
                                                                $aValues = array(
                                                                    //':group_id' 	=> $forumGroupID
                                                                );
                                                                $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                                                $rsGetGroups->execute($aValues);
                                                            }
                                                            catch(PDOException $error){
                                                                // Log any error
                                                                file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                                            }
                                                            while($groups = $rsGetGroups->fetch(PDO::FETCH_ASSOC)){
                                                                
                                                                $groupID = $groups['group_id'];
                                                                
                                                                if(in_array($groupID, $aForumGroups)){
                                                                    echo '<option value="'.$groupID.'" selected>'.$groups['group_name'].'</option>';
                                                                }else{
                                                                    echo '<option value="'.$groupID.'">'.$groups['group_name'].'</option>';
                                                                }
                                                                
                                                            }
                                                            ?>
                                                        </select>
                                               </div><!--form-group-->
                                               <button type="button" class="btn btn-info btn-xs" onclick="selectDeselect('select', 'select_groups');"><i class="icon-arrow-right"></i> Select All</button>&nbsp;
                                               <button type="button" class="btn btn-warning btn-xs" onclick="selectDeselect('deselect', 'select_groups');"><i class="icon-arrow-left"></i> Deselect All</button>
                                           </div><!--col-md-4-->
                                           
                                           <div class="col-md-4">
                                               <div class="form-group">
                                                  <label class="control-label"><strong>Member Types</strong></label>
                                                     <select name="select_member_types[]" class="multi-select" multiple="" id="select_member_types" >
                                                     	<?php
														//Get all forum Groups
														$query = "
															SELECT *
															FROM ".$_SESSION['members_flags_index_table']."
															WHERE active='1'
															ORDER BY flag_id ASC
														";
														try{
															$rsGetFlags = $mLink->prepare($query);
															$aValues = array(
																//':group_id' 	=> $forumGroupID
															);
															$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
															$rsGetFlags->execute($aValues);
														}
														catch(PDOException $error){
															// Log any error
															file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
														}
														while($flags = $rsGetFlags->fetch(PDO::FETCH_ASSOC)){
															
															$flagCol = $flags['flag_col_name'];
															
															if(in_array($flagCol, $aMemberFlags)){
																echo '<option value="'.$flagCol.'" selected>'.$flags['flag_name'].'</option>';
															}else{
																echo '<option value="'.$flagCol.'">'.$flags['flag_name'].'</option>';
															}
															
														}
														?>
                                                     	
                                                     </select>
                                               </div><!--form-group-->
                                               <button type="button" class="btn btn-info btn-xs" onclick="selectDeselect('select', 'select_member_types');"><i class="icon-arrow-right"></i> Select All</button>&nbsp;
                                               <button type="button" class="btn btn-warning btn-xs" onclick="selectDeselect('deselect', 'select_member_types');"><i class="icon-arrow-left"></i> Deselect All</button>
                                        	</div><!--col-md-4-->
                                            	
                                           <div class="col-md-4">
                                               <?php /*?><div class="form-group">
                                                  <label class="control-label"><strong>Members</strong></label>
                                                     <select name="select_members[]" class="multi-select" multiple="" id="select_members" >
                                                        <?php
                                                        $query = "
                                                            SELECT name_first, name_last, member_id, username
                                                            FROM ".$_SESSION['members_table']."
                                                            WHERE active='1'
                                                            ORDER BY name_last ASC, name_first ASC
                                                        ";
                                                        try{
                                                            $rsGetMembers = $mLink->prepare($query);
                                                            $aValues = array(
                                                                //':group_id' 	=> $forumGroupID
                                                            );
                                                            $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                                            $rsGetMembers->execute($aValues);
                                                        }
                                                        catch(PDOException $error){
                                                            // Log any error
                                                            file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                                        }
                                                        while($members = $rsGetMembers->fetch(PDO::FETCH_ASSOC)){
                                                            
                                                            $memberID = $members['member_id'];
                                                            
                                                            if($members['name_last'] == '' && $members['name_first'] == ''){
                                                                $displayName = $members['username'];	
                                                            }else{
                                                                $displayName = $members['name_first'].' '.$members['name_last'].' ('.$members['username'].')';
                                                            }
                                                            
                                                            if(in_array($memberID, $aMembers)){
                                                                echo '<option value="'.$memberID.'" selected>'.$displayName.'</option>';
                                                            }else{
                                                                echo '<option value="'.$memberID.'">'.$displayName.'</option>';
                                                            }
                                                            
                                                        }
                                                        ?>
                                                     </select>
                                               </div><!--form-group-->
                                               <button type="button" class="btn btn-info btn-xs" onclick="selectDeselect('select', 'select_members');"><i class="icon-arrow-right"></i> Select All</button>&nbsp;
                                               <button type="button" class="btn btn-warning btn-xs" onclick="selectDeselect('deselect', 'select_members');"><i class="icon-arrow-left"></i> Deselect All</button><?php */?>
                                        	</div><!--col-md-4-->
                                                   
                                    	</div><!--row-->
                                    	
                                        
                                        
                                    </div><!--portlet-body-->
                                    <div class="form-actions fluid">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="col-md-9">
                                                    <a class="btn btn-success" onclick="updateAccess('<?php echo $forumID;?>');"><i class="icon-ok"></i> Save</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!--form-actions-->
                                    
                                    </form>
                                </div><!--end portlet-->
                                
                                <div class="row forum-actions">
                                	<div class="col-md-6 forum-info">
                                
                                        <div class="portlet">
                                            <div class="portlet-title">
                                                <div class="caption"><i class="icon-info-sign"></i>Forum Info</div>
                                                <div class="tools">
                                                    <a href="" class="collapse balloon" title="Expand/Collapse Portlet"></a>
                                                </div>
                                            </div><!--portlet-title-->
                                            
                                            <form action="process/ajax/community-forum-processes.php?process=19" method="post" name="update-forum" class="update-forum">
                                            <div class="portlet-body">
                                            
                                                <div class="modal-body">
                                                        <div id="createForumUserError"></div>
                                                        <div class="form-body" id="update-forum-info-form">
                                                            <div class="form-group">
                                                                <label class="control-label">Forum Title* <span id="forum_title-help"></span></label>
                                                                <input type="text" class="form-control" name="forum_title" id="forum_title" value="<?php echo $forum['forum_title'];?>" />
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label class="control-label">Forum Description* <span id="forum_desc-help"></span></label>
                                                                <textarea class="form-control" name="forum_desc" id="forum_desc" row="5" style="resize:vertical;"><?php echo $forum['forum_description'];?></textarea>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label class="control-label">Forum Order/Sequence* (Numbers Only)<span id="sequence-help"></span></label>
                                                                <input type="text" class="form-control" name="sequence" id="sequence" value="<?php echo $forum['sequence'];?>" style="width:60px !important;" />
                                                            </div>
                                                        </div><!--form-body-->
                                                        
                                                        <input type="hidden" name="forum_id" value="<?php echo $forumID;?>" />
                                                    
                                                </div><!--modal-body-->
                                                
                                            </div><!--portlet-body-->
                                            <div class="form-actions fluid">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="col-md-9">
                                                            <input type="submit" value="Save Forum Info" class="btn btn-success" id="submit-update-forum" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div><!--form-actions-->
                                            </form><!--create-topic-->
                                            
                                        </div><!--end portlet-->
                                        
                                    </div><!--col-md-6 forum-info-->
                                    
                                    <div class="col-md-6 delete-forum">
                                		
                                        <?php if($forumActive == '1'){?>
                                        <div class="portlet">
                                            <div class="portlet-title">
                                                <div class="caption"><i class="icon-exclamation-sign"></i>Forum Control</div>
                                                <div class="tools">
                                                    <a href="" class="collapse balloon" title="Expand/Collapse Portlet"></a>
                                                </div>
                                            </div><!--portlet-title-->
                                            
                                            <div class="portlet-body">
                                            	
                                                <div class="note note-danger">
                                                	
                                                    <h3>Deactivate Forum</h3>
                                                    <p>By pressing the "Deactivate" button you will be removing this Forum from the system. Note this will not delete the forum from the database, but will make inaccessable to anyone.</p>
                                                </div><!--note-->
                                                
                                            </div><!--portlet-body-->
                                            <div class="form-actions fluid">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="col-md-9">
                                                            <a class="btn btn-danger" onclick="activate('false','forum','<?php echo $forumID;?>');"><i class="icon-exclamation-sign"></i> DELETE</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div><!--form-actions-->
                                        </div><!--end portlet-->
                                        <?php
										}elseif($forumActive == '0'){?>
                                        <div class="portlet">
                                            <div class="portlet-title">
                                                <div class="caption"><i class="icon-exclamation-sign"></i>Forum Control</div>
                                                <div class="tools">
                                                    <a href="" class="collapse balloon" title="Expand/Collapse Portlet"></a>
                                                </div>
                                            </div><!--portlet-title-->
                                            
                                            <div class="portlet-body">
                                            	
                                                <div class="note note-success">
                                                    <h3>Activate Forum</h3>
                                                    <p>By pressing the "Activate" button you will be adding this Forum from the system. Note this will make the forum accessable to members.</p>
                                                </div><!--note-->
                                                
                                            </div><!--portlet-body-->
                                            <div class="form-actions fluid">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="col-md-9">
                                                            <a class="btn btn-success" onclick="activate('true','forum','<?php echo $forumID;?>');"><i class="icon-ok"></i> Activate</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div><!--form-actions-->
                                        </div><!--end portlet-->
                                        <?php }?>
                                        
                                    </div><!--col-md-6 delete-forum-->         
                                    
                                </div><!--row forum-actions-->
                            	
                            </div><!--col-md-12-->
                            
                        </div><!--row-->
                    
                    </div><!--portlet-body-->
            	</div><!--end portlet-->
                <?php } ?>
                
            </div><!--end column-->
        </div><!--row-->
        