<?php

?>
        <!-- BEGIN CREATE CATEGORY MODAL-->
        <div class="modal fade" id="create-forum" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog">
             <div class="modal-content">
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Create New Forum</span></h4>
                </div>
                
                <form action="process/ajax/community-forum-processes.php?process=11" method="post" name="create-forum" class="create-forum">
                    <div class="modal-body">
                        	<div id="createForumUserError"></div>
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="control-label">Forum Title</label>
                                    <input type="text" class="form-control" name="forum_title" id="forum_title" />
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Forum Description</label>
                                    <textarea class="form-control" name="forum_desc" id="forum_desc" row="5" style="resize:vertical;"></textarea>
                                </div>
                                
                            </div><!--form-body-->
                        
                    </div><!--modal-body-->
                    
                    <div class="modal-footer">
                    	<input type="submit" value="Create Forum" class="btn btn-info"/>
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
             
                <!-- BEGIN MY TOPICS TABLE-->
                <div class="portlet" id="ledger-entries">
                <div class="portlet-title">
                   	<div class="caption"><i class="icon-comments"></i>My Topics</div>
                	<div class="actions">
                   		
                   	</div>
                </div>
                <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover table-full-width" id="my-topics">
                        <thead>
                            <tr>
                                <th>Topics</th>
                                <th width="10%">Forum</th>
                                <th width="2%">Category</th>
                                <th width="10%">Last Post</th>
                                
                                
                                
                            </tr>
                        </thead>
                        <tbody class="load-my-topics">
                        	<?php 
							
							$catID = $_REQUEST['cat'];
							
							$query = "
								SELECT fp.post_creator, fp.post_id, fp.topic_id, fp.post_content, ft.topic_title, ft.forum_id, ft.topic_last_user, ft.last_post_id, ft.cat_id, ft.topic_creator
								FROM `community_forum_posts` AS fp
								INNER JOIN community_forum_topics AS ft ON ft.topic_id=fp.topic_id
								WHERE fp.post_creator=:member_id
								GROUP BY fp.topic_id
							";
							
							try{
								$rsTopic = $mLink->prepare($query);
								$aValues = array(
									':member_id'	=> $_SESSION['member_id']
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
								$topicCreationDate 	= $topic['timestamp'];
								$postID 			= $topic['last_post_id'];
								$unreadPosts		= get_unread_post($mLink, $topicID);
								
								/*if($topic['last_post_id'] == NULL){
									$topicDate	= $topic['topic_reply_date'];
								}
								
								if($postID == "" || $postID == NULL){
									$query = "
										SELECT post_id
										FROM `community_forum_posts` 
										WHERE topic_id=:topic_id
										ORDER BY timestamp DESC
										LIMIT 1
									";
									
									try{
										$rsGetPost = $mLink->prepare($query);
										$aValues = array(
											':topic_id'	=> $topicID
										);
										$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
										$rsGetPost->execute($aValues);
									}
									catch(PDOException $error){
										// Log any error
										file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
									}
									
									$post = $rsGetPost->fetch(PDO::FETCH_ASSOC);
									
									$postID = $post['post_id'];
									
								}
								
									
								$topicDate = get_post_info('timestamp', $mLink, $postID);
								$lastPosted = get_member($mLink, get_post_info('creator', $mLink, $postID), 'username');
								
								$test = $topic['topic_id'];
								
								if($topic['topic_last_user'] == NULL){
									$lastPosted = get_member($mLink, $topic['topic_creator'], 'full name');	
								}
								
								
								
								
								
								//Get Last Post Direct Link
								$lastPostURL = get_post_url($mLink, $postID);*/
								
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
																
								?>
								<tr>
									<td><a href="?page=04-01-00-008&topic=<?php echo $topicID;?>" style="display:block;"><strong><?php echo $topicTitle;?></strong><br /><small>Category: <?php echo  get_category($mLink, $catID, 'title');?></small></a></td>
									<td><?php echo get_forum($mLink, $forumID, 'title'); ?>
									<td><?php echo  get_category($mLink, $catID, 'title');?></td>
									<td><span style="display:none;"><?php echo $postDate; ?></span><?php echo $lastPost;?></td>
									
								</tr>
								<?php
							}
							?>
                            
                        </tbody>
                        </table>
                </div>
                </div>
                <!-- END MY TOPICS TABLE-->
            </div>
        </div><!--row-->
        