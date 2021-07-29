<?php
//GET ALL Topic Info

/*if($_SESSION['admin'] == "1"){
	$showWysihtml5 = "wysihtml5";	
}else{
	$showWysihtml5 = "";
}*/


$query = "
	SELECT *
	FROM ".$_SESSION['forum_topics_table']."
	WHERE topic_id=:topic_id
";

try{
	$rsTopic = $mLink->prepare($query);
	$aValues = array(
		':topic_id'	=> $topicID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsTopic->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$topic = $rsTopic->fetch(PDO::FETCH_ASSOC);

$lastPosted	= get_member($mLink, $topic['topic_last_user'], 'full name');
$lastPostedDate = time_past($topic['topic_reply_date']);

if($topic['topic_last_user'] == NULL){
	$lastPosted = get_member($mLink, $topic['topic_creator'], 'full name');	
}

//Update View Count

//Check to see if session for topic ID as been set
if(!isset($_SESSION['topic_'.$topicID.''])){
	
	//No Session for Topic ID set, set it here
	$_SESSION['topic_'.$topicID.''] = $_SESSION['member_id'];
	
	$oldViews = $topic['topic_views'];
	$newViews = $oldViews + 1;
	
	$query = "
		UPDATE ".$_SESSION['forum_topics_table']."
		SET topic_views=:topic_views
		WHERE topic_id=:topic_id
	";
	
	try{
		$rsTopic = $mLink->prepare($query);
		$aValues = array(
			':topic_id'		=> $topicID,
			':topic_views'	=> $newViews
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsTopic->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
}




?>		
		<!-- BEGIN CREATE POSTS MODAL-->
        <div class="modal fade" id="create-post" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog">
             <div class="modal-content">
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Create New Post For <span class="label label-info"><?php echo $topic['topic_title']; ?></span></h4>
                </div>
                
                <form action="process/ajax/community-forum-processes.php?process=4" method="post" name="create-post" class="create-post">
                    <div class="modal-body">
                        	<div id="createPostUserError"></div>
                            <div class="form-body">
                                
                                <div class="form-group">
                                    <label class="control-label">Post* <span id="post-help"></span></label>
                                    
                                    <textarea class="form-control" name="post" id="post" rows="10" style="resize:vertical;"></textarea>
                                </div>
                                <input type="hidden" name="cat_id" value="<?php echo $catID; ?>" />
                                <input type="hidden" name="topic_id" value="<?php echo $topicID; ?>" />
                                <input type="hidden" name="forum_id" value="<?php echo $forumID; ?>" />
                            </div><!--form-body-->
                        
                    </div><!--modal-body-->
                    
                    <div class="modal-footer">
                    	<input type="submit" value="Post Reply" class="btn btn-info" id="submit-post" style="display:none;"/>
                       	<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    </div>
                
                </form><!--create-topic-->
             </div>
             <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- END CREATE POSTS MODAL-->
        
        <!-- BEGIN SHOW MODAL-->
        <div class="modal fade" id="show-badges" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog">
             <div class="modal-content" id="load-badges">
             
             </div>
             <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- END CREATE POSTS MODAL-->
        
        <!-- BEGIN PAGE CONTENT-->          
        <div class="row">
            <div class="col-md-12">
            	<a href="?page=04-01-00-003&forum=<?php echo $forumID;?>&cat=<?php echo $catID; ?>" class="btn btn-info btn-sm" style="margin-bottom:20px;"><i class="icon-arrow-left"></i> Back to Topics</a>

                <!-- BEGIN New Support Ticket-->
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption">
                        	<i class="icon-comments"></i> <?php echo $topic['topic_title']; ?> 
                        </div>
                        <div class="actions">
                        	<?php if($topic['closed'] == '0'){?>
                        	<a href="#create-post" data-toggle="modal" class="btn btn-info">Post Reply</a>
                            <?php } ?>
                        </div>
                        <div class="tools" style="margin-right:10px;">
                        	
                            <a href="" class="reload" onClick="refreshPosts()"></a>
                      	</div>
                        
                    </div>
                    <div class="portlet-body form">
                    
                    	<div class="row" style="text-align:center;">
                        	<div class="col-md-3">
                            	<div class="ticket-info">
                                	<div class="ticket-info-header">
                                		<h4>Views</h4>
                                    </div><!--ticket-info-header-->
                                    <div class="ticket-info-body">
                                    	<h5><strong><?php echo $topic['topic_views']; ?></strong></h5>
                                    </div><!--ticket-info-body-->
                                </div><!--ticket-info-->
                            </div><!--col-md-3-->
                            <div class="col-md-3">
                            	
                                <div class="ticket-info">
                                	<div class="ticket-info-header">
                                		<h4>Replies</h4>
                                    </div><!--ticket-info-header-->
                                    <div class="ticket-info-body">
                                    	<h5><strong><?php echo get_topic_replies($mLink, $topicID);?></strong></h5>
                                    </div><!--ticket-info-body-->
                                </div><!--ticket-info-->
                            </div><!--col-md-3-->
                            <div class="col-md-3">
                            	
                                <div class="ticket-info">
                                	<div class="ticket-info-header">
                                		<h4>Last Responder</h4>
                                    </div><!--ticket-info-header-->
                                    <div class="ticket-info-body">
                                    	<h5><strong><?php echo $lastPosted;?></strong></h5>
                                    </div><!--ticket-info-body-->
                                </div><!--ticket-info-->
                            </div><!--col-md-3-->
                            <div class="col-md-3">
                            	
                                <div class="ticket-info">
                                	<div class="ticket-info-header">
                                		<h4>Last Reply</h4>
                                    </div><!--ticket-info-header-->
                                    
                                    <div class="ticket-info-body">
                                    	<h5><strong><?php echo $lastPostedDate; ?></strong></h5>
                                    </div><!--ticket-info-body-->
                                </div><!--ticket-info-->
                            </div><!--col-md-3-->
                        </div><!--row-->
                        
                    	<div style="margin:10px;">
                            
                            <div id="community-forum-posts-ajax">
                          	<?php //include('includes/community-forum-view-posts.php');?>
                            </div><!--response-ajax-->
                            
                        </div>
                        
                        <div class="forum-post-actions">
                        <?php if($topic['closed'] == '0'){?>
                            <a href="#create-post" data-toggle="modal" class="btn btn-info btn-md">Post Reply</a>
                            
                        <?php
                        }
					    ?>
                        </div><!--forum-actions-->
                    </div>
                </div>
                <!-- END TICKET PORTLET-->
                <a href="?page=04-01-00-003&forum=<?php echo $forumID;?>&cat=<?php echo $catID; ?>" class="btn btn-info btn-sm" style="margin-bottom:20px;"><i class="icon-arrow-left"></i> Back to Topics</a>
                
                
               
            </div><!--END COLUMN-->
        </div><!--END ROW-->
       <!-- END PAGE CONTENT-->
        