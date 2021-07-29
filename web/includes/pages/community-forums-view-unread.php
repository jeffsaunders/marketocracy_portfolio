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
             	
                <form action="process/ajax/community-forum-processes.php?process=21" method="post" class="mark-viewed-form">
                <!-- BEGIN RECENT ORDERS TABLE-->
                <div class="portlet" id="ledger-entries">
                <div class="portlet-title">
                   	<div class="caption"><i class="icon-comments"></i>Unread Topics</div>
                	<div class="actions">
                   		<?php
						if($_SESSION['admin'] == 1 || $_SESSION['super_admin'] == 1 || $_SESSION['moderator'] == 1 || $_SESSION['super_moderator'] == 1){
						?>
                    		<!--<a class="btn btn-info btn-sm" href="#create-forum" data-toggle="modal">Mark All Topics As Read</a>-->
                            <input type="submit" value="Mark Selected Topics As Read" class="btn btn-sm btn-info"/>
                        <?php
                        }
						?>
                   	</div>
                </div>
                <div class="portlet-body">
                	<div id="mark-read-error"></div>
                        <table class="table table-striped table-bordered table-hover table-full-width" id="unread-topics">
                        <thead>
                            <tr>
                            	<th width="1%"><?php /*?><input type="checkbox" name="checkall" class="checkall" value="ON" /><?php */?></th>
                                <th>Topics</th>
                                <th width="10%">Forum</th>
                                <th width="2%">Unread Posts</th>
                                <th width="10%">Last Post</th>
                            </tr>
                        </thead>
                        <tbody class="load-unread">
                        	<?php 
							
							$catID = $_REQUEST['cat'];
							
							$query = "
								SELECT *
								FROM ".$_SESSION['forum_topics_table']."
								WHERE active='1'
								ORDER BY topic_reply_date DESC
							";
							
							//Fund Symbols
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
								$topicCreationDate 	= $topic['timestamp'];
								$postID 			= $topic['last_post_id'];
								$unreadPosts		= get_unread_post($mLink, $topicID);
								
								$forumAccess = forumAccess($mLink, $forumID, 'forum');
								
								if($forumAccess != 0){
									if($unreadPosts != "0"){
										$topicDate = get_post_info('timestamp', $mLink, $postID);
										$lastPosted = get_member($mLink, get_post_info('creator', $mLink, $postID), 'username');
										
										if($topic['topic_last_user'] == NULL){
											$lastPosted = get_member($mLink, $topic['topic_creator'], 'full name');	
										}
										
										if($topic['last_post_id'] == NULL){
											$topicDate	= $topic['topic_reply_date'];
										}
										
										//Get Last Post Direct Link
										$lastPostURL = get_post_url($mLink, $postID);
										
										//Get POST IDS
										$aPosts = implode('|',get_unread_post($mLink, $topicID, 'ID'));								
										?>
										<tr>
                                        	
                                        	<td><input type="checkbox" class="selectAll" id="select-<?php echo $topicID;?>" name="checkTopic[]" value="<?php echo $topicID;?>" /></td>
											<td><a href="?page=04-01-00-006&topic=<?php echo $topicID;?>" style="display:block;"><strong><?php echo $topicTitle;?></strong><br /><small>Category: <?php echo  get_category($mLink, $catID, 'title');?></small></a> <input type="hidden" name="post-ids-<?php echo $topicID;?>" value="<?php echo $aPosts;?>"/></td>
											<td><?php echo get_forum($mLink, $forumID, 'title'); ?></td>
											<td style="text-align:center;"><span class="label label-info"><?php echo $unreadPosts;?></span></td>
											<td><a href="<?php echo $lastPostURL;?>" style="display:block;"><span style="display:none;"><?php echo $topicDate; ?></span><?php echo time_past($topicDate, 'day'); ?><br /><small>by <?php echo $lastPosted;?></small></a></td>
											
										</tr>
										<?php
									}
								}//end forum access
							}
							?>
                            
                        </tbody>
                        </table>
                </div>
                </div>
                <!-- END RECENT ORDER TABLE-->
                </form>
            </div>
        </div><!--row-->
        