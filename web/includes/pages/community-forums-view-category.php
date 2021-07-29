<?php
$catID = $_REQUEST['cat'];
//Get info for current category
$query = "
	SELECT *
	FROM ".$_SESSION['forum_categories_table']."
	WHERE cat_id=:cat_id
";
try{
	$rsGetCat = $mLink->prepare($query);
	$aValues = array(
		':cat_id' => $catID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsGetCat->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}
$cat = $rsGetCat->fetch(PDO::FETCH_ASSOC);

$catActive = $cat['active'];

//Explode groupIDs into an array to process later
if($cat['allow_forum_groups'] != NULL){
	$aForumGroups	= explode("|",$cat['allow_forum_groups']);
}else{
	$aForumGroups 	= $cat['allow_forum_groups'];
}
//Explode member flags into an array to process later
if($cat['allow_members_flags'] != NULL){
	$aMemberFlags = explode("|",$cat['allow_members_flags']);
}else{
	$aMemberFlags = $cat['allow_members_flags'];	
}
//Explode member flags into an array to process later
if($cat['allow_members'] != NULL){
	$aMembers = explode("|",$cat['allow_members']);
}else{
	$aMembers = $cat['allow_members'];	
}

if($cat['allow_members_flags'] == NULL && $cat['allow_forum_groups'] == NULL && $cat['allow_members'] == NULL){
	$checkEveryone = 'checked';	
}else{
	$checkEveryone = '';	
}
?>		
		
        <!-- BEGIN SEND EMAIL MODAL-->
        <div class="modal fade" id="send-email" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-wide">
             <div class="modal-content">
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Send Email To Members</span></h4>
                </div>
                
                <div id="load-email-modal">
                
                
                
                </div><!--load-email-modal-->
             </div>
             <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- END SEND EMAIL MODAL-->
		
        <!-- BEGIN CREATE TOPIC MODAL-->
        <div class="modal fade" id="create-topic" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog">
             <div class="modal-content">
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Create New Topic For <span class="label label-info"><?php echo $forumCatTitle; ?></span></h4>
                </div>
                
                <form action="process/ajax/community-forum-processes.php?process=1" method="post" name="create-topic" class="create-topic">
                    <div class="modal-body">
                        	<div id="createTopicUserError"></div>
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="control-label">Topic Title* <span id="topic_title-help"></span></label>
                                    <input type="text" class="form-control" name="topic_title" id="topic_title" />
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Post* <span id="topic_post-help"></span></label>
                                    <textarea class="form-control" name="topic_post" id="topic_post" row="5" style="resize:vertical;"></textarea>
                                </div>
                                
                                <input type="hidden" name="cat_id" value="<?php echo $catID; ?>" />
                                <input type="hidden" name="forum_id" value="<?php echo $forumID; ?>" />
                            </div><!--form-body-->
                        
                    </div><!--modal-body-->
                    
                    <div class="modal-footer">
                    	<input type="submit" value="Create Topic" id="submit-topic" class="btn btn-info" style="dispaly:none;"/>
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
             	<a class="btn btn-info btn-sm bump-down" href="?page=04-01-00-002&forum=<?php echo $forumID; ?>"><i class="icon-arrow-left"></i> Back to <?php echo $forumMainTitle; ?></a>
                <!-- BEGIN RECENT ORDERS TABLE-->
                <div class="portlet" id="ledger-entries">
                    <div class="portlet-title">
                       <div class="caption"><i class="icon-comments"></i><?php echo $forumCatTitle; ?></div>
                       <div class="actions">
                          <a class="btn btn-info" href="#create-topic" data-toggle="modal">Create Topic</a>
                       </div>
                    </div>
                    <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover table-full-width" id="forum-topics">
                            <thead>
                                <tr>
                                    
                                    <th>Topics</th>
                                    <th>Created By</th>
                                    <th width="10%">Last Post</th>
                                    <th width="2%">Replies</th>
                                    <th width="2%">Views</th>
                                    <th style="display:none;"></th>
                                    
                                </tr>
                            </thead>
                            <tbody class="load-topics">
                                <tr>
                                	<td><img src="<?php echo $_SESSION['site_root'];?>images/loading.gif" /></td>
                                	<td></td>
                                	<td></td>
                                    <td></td>
                                    <td></td>
                                	<td style="display:none;">0</td>
                                </tr>
								<?php 
                                
                                /*$catID = $_REQUEST['cat'];
                                                        
                                $query = "
									SELECT *
									FROM ".$_SESSION['forum_topics_table']."
									WHERE forum_id=:forum_id AND cat_id=:cat_id AND active='1'
									ORDER BY topic_reply_date DESC
								";	
								
								try{
									$rsTopic = $mLink->prepare($query);
									$aValues = array(
										':forum_id' 	=> $forumID,
										':cat_id'		=> $catID
									);
									$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
									$rsTopic->execute($aValues);
								}
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}
								
								while($topic = $rsTopic->fetch(PDO::FETCH_ASSOC)) {
									$topicID 			= $topic['topic_id'];
									$catID 				= $topic['cat_id'];
									$forumID			= $topic['forum_id'];
									$topicTitle			= $topic['topic_title'];
									$lastPosted			= get_member($mLink, $topic['topic_last_user'], 'full name');
									$topicDate			= $topic['topic_reply_date'];
									$topicViews			= $topic['topic_views'];
									$topicCreationDate 	= $topic['timestamp'];
									$postID 			= $topic['last_post_id'];
									
									
									
									//START GET LAST POST	
									//Query Posts table and grab the most recent post of all provided categories
									$query = "
										SELECT *
										FROM ".$_SESSION['forum_posts_table']."
										WHERE topic_id=:topic_id
										ORDER BY timestamp DESC
										LIMIT 1
									";
									
									try{
										$rsGetPosts = $mLink->prepare($query);
										$aValues = array(
											':topic_id' 	=> $topicID
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
									$postUser		= get_member($mLink, $post['post_creator'], $userDisplay);
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
										
										if($active == '1'){
											$adminButton = '
												<div class="btn-group" style="float:left;margin-right:10px;">
													<button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" ><i class="icon-cog"></i></button>
													
													<ul class="dropdown-menu" role="menu">
													   <li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-danger btn-xs btn-full" onclick="activate(\'false\',\'topic\',\''.$topicID.'\');"><i class="icon-remove"></i> Remove Topic</button></li>
													   <!--<li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-info btn-xs btn-full" onclick="window.location.href=\'?page=04-01-00-002&forum='.$forumID.'#forum-settings\';"><i class="icon-pencil"></i> Edit Forum</button></li>-->
													</ul>
													
												 </div><!--button-group-->
											';
										}elseif($active == '0'){
											$adminButton = '
												<div class="btn-group" style="float:left;margin-right:10px;">
													<button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" ><i class="icon-cog"></i></button>
													
													<ul class="dropdown-menu" role="menu">
													   <li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-success btn-xs btn-full" onclick="activate(\'true\',\'topic\',\''.$topicID.'\');"><i class="icon-ok"></i> Activate Topic</button></li>
													   <!--<li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-info btn-xs btn-full" onclick="window.location.href=\'?page=04-01-00-002&forum='.$forumID.'#forum-settings\';"><i class="icon-pencil"></i> Edit Forum</button></li>-->
													</ul>
													
												 </div><!--button-group-->
											';
										}
									}
									
									
									?>
									<tr>
										<td><?php echo $adminButton;?><a href="?page=04-01-00-004&forum=<?php echo $forumID;?>&cat=<?php echo $catID; ?>&topic=<?php echo $topicID;?>" style="display:block;float:left;"><strong><?php echo $topicTitle;?></strong></a></td>
										<td><?php echo $lastPost;?></td>
										<td><?php echo get_topic_replies($mLink, $topicID); ?></td>
										<td><?php echo $topicViews;?></td>
										<td style="display:none;"><?php echo $topicCreationDate; ?></td>
									</tr>
									<?php
								}*/
                                ?>
                            </tbody>
                            </table>
                    </div><!--portlet-body-->
                </div><!--portlet-->
                <!-- END VIEW Categories TABLE-->
                
                <a class="btn btn-info btn-sm bump-down" href="?page=04-01-00-002&forum=<?php echo $forumID; ?>"><i class="icon-arrow-left"></i> Back to <?php echo $forumMainTitle; ?></a>
                
                
                <?php if($adminAccess != 0){?>
                <!--START INACTIVE TOPICS TABLE-->
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption"><i class="icon-comments"></i>Inactive Topics</div>
                        <div class="tools">
                            <a href="" class="collapse" title="Expand/Collapse Portlet"></a>
                        </div>
                    </div>
                    <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover table-full-width" id="forum-inactive-list">
                            <thead>
                                <tr>
                                    
                                    <th>Topics</th>
                                    
                                    <th width="20%">Last Post</th>
                                    <th width="2%">Replies</th>
                                    <th width="2%">Views</th>
                                    <th style="display:none;"></th>
                                    
                                </tr>
                            </thead>
                            <tbody class="load-inactive-topics">
                                <?php 
                                
                                $query = "
									SELECT *
									FROM ".$_SESSION['forum_topics_table']."
									WHERE forum_id=:forum_id AND cat_id=:cat_id AND active='0'
									ORDER BY topic_reply_date DESC
								";	
								
								try{
									$rsTopic = $mLink->prepare($query);
									$aValues = array(
										':forum_id' 	=> $forumID,
										':cat_id'		=> $catID
									);
									$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
									$rsTopic->execute($aValues);
								}
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}
								
								while($topic = $rsTopic->fetch(PDO::FETCH_ASSOC)) {
									$topicID 			= $topic['topic_id'];
									$catID 				= $topic['cat_id'];
									$forumID			= $topic['forum_id'];
									$topicTitle			= $topic['topic_title'];
									$lastPosted			= get_member($mLink, $topic['topic_last_user'], 'full name');
									$topicDate			= $topic['topic_reply_date'];
									$topicViews			= $topic['topic_views'];
									$topicCreationDate 	= $topic['timestamp'];
									$postID 			= $topic['last_post_id'];
									
									
									
									//START GET LAST POST	
									//Query Posts table and grab the most recent post of all provided categories
									$query = "
										SELECT *
										FROM ".$_SESSION['forum_posts_table']."
										WHERE topic_id=:topic_id
										ORDER BY timestamp DESC
										LIMIT 1
									";
									
									try{
										$rsGetPosts = $mLink->prepare($query);
										$aValues = array(
											':topic_id' 	=> $topicID
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
									$postUser		= get_member($mLink, $post['post_creator'], $userDisplay);
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
										
										if($active == '1'){
											$adminButton = '
												<div class="btn-group" style="float:left;margin-right:10px;">
													<button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" ><i class="icon-cog"></i></button>
													
													<ul class="dropdown-menu" role="menu">
													   <li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-danger btn-xs btn-full" onclick="activate(\'false\',\'topic\',\''.$topicID.'\');"><i class="icon-remove"></i> Remove Topic</button></li>
													   <!--<li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-info btn-xs btn-full" onclick="window.location.href=\'?page=04-01-00-002&forum='.$forumID.'#forum-settings\';"><i class="icon-pencil"></i> Edit Forum</button></li>-->
													</ul>
													
												 </div><!--button-group-->
											';
										}elseif($active == '0'){
											$adminButton = '
												<div class="btn-group" style="float:left;margin-right:10px;">
													<button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" ><i class="icon-cog"></i></button>
													
													<ul class="dropdown-menu" role="menu">
													   <li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-success btn-xs btn-full" onclick="activate(\'true\',\'topic\',\''.$topicID.'\');"><i class="icon-ok"></i> Activate Topic</button></li>
													   <!--<li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-info btn-xs btn-full" onclick="window.location.href=\'?page=04-01-00-002&forum='.$forumID.'#forum-settings\';"><i class="icon-pencil"></i> Edit Forum</button></li>-->
													</ul>
													
												 </div><!--button-group-->
											';
										}
									}
									
									
									?>
									<tr>
										<td><?php echo $adminButton;?><a href="?page=04-01-00-004&forum=<?php echo $forumID;?>&cat=<?php echo $catID; ?>&topic=<?php echo $topicID;?>" style="display:block;float:left;"><strong><?php echo $topicTitle;?></strong></a></td>
										<td><?php echo $lastPost;?></td>
										<td><?php echo get_topic_replies($mLink, $topicID); ?></td>
										<td><?php echo $topicViews;?></td>
										<td style="display:none;"><?php echo $topicCreationDate; ?></td>
									</tr>
									<?php
								}
                                ?>
                                
                            </tbody>
                            </table>
                            
                            
                           
                    </div><!--portlet-body-->
                </div><!--end portlet-->
                <!-- END INACTIVE TOPICS TABLE-->
                <?php } ?>
                
                
                
                <?php if($adminAccess == 1){?><br /><br />
                <!-- BEGIN CATEGORY SETTINGS TABLE-->
                <div class="portlet" id="cat-settings">
                    <div class="portlet-title">
                        <div class="caption"><i class="icon-gear"></i>Category Settings</div>
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
                                            	
                                           <div class="col-md-4" id="div-select_members">
                                               <div class="form-group">
                                                  <label class="control-label"><strong>Members</strong></label>
                                                     <select name="select_members[]" class="multi-select" multiple="" id="select_members" >
                                                        <?php
                                                        $query = "
                                                            SELECT name_first, name_last, member_id, username
                                                            FROM ".$_SESSION['members_table']."
                                                            WHERE active='1'
                                                            ORDER BY name_last ASC, name_first ASC
															LIMIT 100
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
                                               <button type="button" class="btn btn-warning btn-xs" onclick="selectDeselect('deselect', 'select_members');"><i class="icon-arrow-left"></i> Deselect All</button>
                                        	</div><!--col-md-4-->
                                                   
                                    	</div><!--row-->
                                    	
                                        
                                        
                                    </div><!--portlet-body-->
                                    <div class="form-actions fluid">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="col-md-9">
                                                    <a class="btn btn-success" onclick="updateAccess('<?php echo $catID;?>');"><i class="icon-ok"></i> Save</a>
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
                                                <div class="caption"><i class="icon-info-sign"></i>Category Info</div>
                                                <div class="tools">
                                                    <a href="" class="collapse balloon" title="Expand/Collapse Portlet"></a>
                                                </div>
                                            </div><!--portlet-title-->
                                            
                                            <form action="process/ajax/community-forum-processes.php?process=19-2" method="post" name="update-cat" class="update-cat">
                                            <div class="portlet-body" id="update-cat-area">
                                            
                                                <div class="form-body" id="update-cat-info-form">
                                                    <div class="form-group">
                                                        <label class="control-label">Category Title* <span id="cat_title-help"></span></label>
                                                        <input type="text" class="form-control" name="cat_title" id="cat_title" value="<?php echo $cat['cat_title'];?>" />
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label class="control-label">Category Description* <span id="cat_desc-help"></span></label>
                                                        <textarea class="form-control" name="cat_desc" id="cat_desc" row="5" style="resize:vertical;"><?php echo $cat['cat_description'];?></textarea>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label class="control-label">Category Order/Sequence* (Numbers Only)<span id="sequence-help"></span></label>
                                                        <input type="text" class="form-control" name="sequence" id="sequence" value="<?php echo $cat['sequence'];?>" style="width:60px !important;" />
                                                    </div>
                                                </div><!--form-body-->
                                                
                                                <input type="hidden" name="cat_id" value="<?php echo $catID;?>" />
                                                    
                                                
                                            </div><!--portlet-body-->
                                            <div class="form-actions fluid">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="col-md-9">
                                                            <input type="submit" value="Save Category Info" class="btn btn-success" id="submit-update-cat" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div><!--form-actions-->
                                            </form><!--create-topic-->
                                            
                                        </div><!--end portlet-->
                                        
                                    </div><!--col-md-6 forum-info-->
                                    
                                    <div class="col-md-6 delete-forum">
                                		
                                        <?php if($catActive == '1'){?>
                                        <div class="portlet">
                                            <div class="portlet-title">
                                                <div class="caption"><i class="icon-exclamation-sign"></i>Category Control</div>
                                                <div class="tools">
                                                    <a href="" class="collapse balloon" title="Expand/Collapse Portlet"></a>
                                                </div>
                                            </div><!--portlet-title-->
                                            
                                            <div class="portlet-body">
                                            	
                                                <div class="note note-danger">
                                                	
                                                    <h3>Deactivate Category</h3>
                                                    <p>By pressing the "Deactivate" button you will be removing this Category from the system. Note this will not delete the Category from the database, but will make inaccessable to anyone except admins.</p>
                                                </div><!--note-->
                                                
                                            </div><!--portlet-body-->
                                            <div class="form-actions fluid">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="col-md-9">
                                                            <a class="btn btn-danger" onclick="activate('false','category','<?php echo $catID;?>');"><i class="icon-exclamation-sign"></i> DELETE</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div><!--form-actions-->
                                        </div><!--end portlet-->
                                        <?php
										}elseif($catActive == '0'){?>
                                        <div class="portlet">
                                            <div class="portlet-title">
                                                <div class="caption"><i class="icon-exclamation-sign"></i>Category Control</div>
                                                <div class="tools">
                                                    <a href="" class="collapse balloon" title="Expand/Collapse Portlet"></a>
                                                </div>
                                            </div><!--portlet-title-->
                                            
                                            <div class="portlet-body">
                                            	
                                                <div class="note note-success">
                                                    <h3>Activate Category</h3>
                                                    <p>By pressing the "Activate" button you will be adding this Category to the system. Note this will make the category accessable to members.</p>
                                                </div><!--note-->
                                                
                                            </div><!--portlet-body-->
                                            <div class="form-actions fluid">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="col-md-9">
                                                            <a class="btn btn-success" onclick="activate('true','category','<?php echo $catID;?>');"><i class="icon-ok"></i> Activate</a>
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
                
            </div>
        </div><!--row-->
        