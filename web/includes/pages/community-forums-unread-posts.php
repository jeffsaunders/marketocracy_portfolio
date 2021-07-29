<?php
$query = "
	SELECT p.* 
	FROM community_forum_posts AS p
	WHERE p.topic_id=:topic_id 
	AND NOT EXISTS(
		SELECT *
		FROM members_forum_viewed AS v
		WHERE v.viewed_post=p.post_id AND v.member_id=:member_id
	)
	ORDER BY p.timestamp DESC
";

//Fund Symbols
try{
	$rsPosts = $mLink->prepare($query);
	$aValues = array(
		':topic_id' 	=> $topicID,
		':member_id'	=> $_SESSION['member_id']
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsPosts->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$numPosts = $rsPosts->rowCount();
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
             	<a class="btn btn-info btn-sm bump-down" href="?page=04-01-00-005"><i class="icon-arrow-left"></i> Back to Unread Topics</a>
                <!-- BEGIN RECENT ORDERS TABLE-->
                <div class="portlet" id="ledger-entries">
                    <div class="portlet-title">
                        <div class="caption"><i class="icon-comments"></i><?php echo $numPosts;?> Unread Posts</div>
                        <div class="actions">
                        	<a class="btn btn-info btn-sm" href="javascript:void(0);" onClick="markRead();">Mark All Posts As Read</a>
                        </div>
                    </div>
                    <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover table-full-width" id="unread-posts">
                            <thead>
                                <tr>
                                    <th width="1%"></th>
                                    <th>Post</th>
                                    
                                    <th width="10%">Post Creator</th>
                                    
                                    <th width="10%">Posted On</th>
                                    <th width="2%">Post Replies</th>
                                </tr>
                            </thead>
                            <tbody class="load-unread-posts">
                                <?php 
                                
                                $catID = $_REQUEST['topic'];
                                $aUnread = Array();
								
                                while($post = $rsPosts->fetch(PDO::FETCH_ASSOC)) {
									
									$aUnread[] = $post['post_id'];
									
                                    $postID				= $post['post_id'];
                                    $topicID 			= $post['topic_id'];
                                    $catID 				= $post['cat_id'];
                                    //$forumID			= $post['forum_id'];
                                    $postContent		= $post['post_content'];
                                    $postDate			= $post['timestamp'];
                                    $postReplys			= get_post_replies($mLink, $postID);
                                    $usernameLink		= get_member($mLink, $post['post_creator'], 'usernameLink');
                                    $convoID			= $post['convo_id'];
                                    
                                    if($postReplys != "0"){
                                        $postReplys = '<span class="label label-info">'.$postReplys.'</span>';
                                    }else{
                                        $postReplys = '<span class="label label-warning">'.$postReplys.'</span>';	
                                    }
                                    
                                    if($convoID != NULL){
                                        $showConvo = '<span style="display:none;">1</span><span class="label label-success"><i class="icon-comments"></i></span>&nbsp;';	
                                    }else{
                                        $showConvo = '<span style="display:none;">0</span><span class="label label-info"><i class="icon-comment"></i></span>&nbsp;';	
                                    }
                                    
                                    //Get Last Post Direct Link
                                    $postURL = get_post_url($mLink, $postID);
                                    
                                    ?>
                                    <tr>
                                        <td><?php echo $showConvo;?></td>
                                        <td><?php //echo $postID; ?><a href="<?php echo $postURL;?>" style="display:block;"><?php echo substr($postContent, 0, 100);?></a></td>
                                        <td><?php echo $usernameLink; ?></td>
                                        
                                        <td><span style="display:none;"><?php echo $postDate; ?></span><?php echo time_past($postDate); ?></td>
                                        <td style="text-align:center;"><?php echo $postReplys; ?></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                
                            </tbody>
                            </table>
                            
                            <form method="post" action="process/ajax/community-forum-processes.php?process=13&form=1" id="mark-read">
                            	<?php
								foreach($aUnread as $value){
									echo '<input type="hidden" value="'.$value.'" name="posts[]" />';
								}
								?>                                
                                <input type="hidden" value="<?php echo $topicID; ?>" name="topic" />
                            </form> 
                            
                    </div>
                    <div class="portlet-key">
                    	<ul>
                        	<li><span class="label label-success"><i class="icon-comments"></i></span> Post is part of a converstaion.</li>
                            <li><span class="label label-info"><i class="icon-comment"></i></span> Post is a direct reply to the topic.</li>
                        </ul>
                    </div>
                </div>
                <!-- END RECENT ORDER TABLE-->
                <a class="btn btn-info btn-sm bump-down" href="?page=04-01-00-005"><i class="icon-arrow-left"></i> Back to Unread Topics</a>
            </div>
        </div><!--row-->
        