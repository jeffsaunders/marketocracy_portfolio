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
                                    <label class="control-label">Forum Title* <span id="forum_title-help"></span></label>
                                    <input type="text" class="form-control" name="forum_title" id="forum_title" />
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Forum Description* <span id="forum_desc-help"></span></label>
                                    <textarea class="form-control" name="forum_desc" id="forum_desc" row="5" style="resize:vertical;"></textarea>
                                </div>
                                
                            </div><!--form-body-->
                        
                    </div><!--modal-body-->
                    
                    <div class="modal-footer">
                    	<input type="submit" value="Create Forum" class="btn btn-info" id="submit-forum" style="display:none;"/>
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
             	
                <?php
				$forumError = $_REQUEST['forumError'];
				
				if($forumError != ''){
				
					switch($forumError){
						case '1': $noteType = 'warning';$noteTitle = 'Access Denied'; $noteMessage = 'You do not have access that forum. Please select a Forum from the list below.';break;
					}
					
					?>
                    <div class="note note-<?php echo $noteType;?>">
                    	<h4 class="block"><?php echo $noteTitle;?></h4>
                   		<p><?php echo $noteMessage;?></p>
                    </div><!--note-->
                    <?php
					
				}
				?>
                
                <!-- BEGIN FORUMS TABLE-->
                <div class="portlet" id="ledger-entries">
                    <div class="portlet-title">
                        <div class="caption"><i class="icon-comments"></i>All Forums</div>
                        <div class="actions">
                            <?php
                            if($_SESSION['admin'] == 1 || $_SESSION['super_admin'] == 1 /*|| $_SESSION['moderator'] == 1*/ || $_SESSION['super_moderator'] == 1){
                            ?>
                                <a class="btn btn-info" href="#create-forum" data-toggle="modal">Create Forum</a>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                    <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover table-full-width" id="forum-list">
                            <thead>
                                <tr>
                                    
                                    <th>Forums</th>
                                    
                                    <th width="20%">Last Post</th>
                                    <th width="2%">Categories</th>
                                    <th style="display:none;"></th>
                                    
                                </tr>
                            </thead>
                            <tbody class="load-forums">
                            	<tr>
                                	<td><img src="<?php echo $_SESSION['site_root'];?>images/loading.gif" /></td>
                                	<td></td>
                                	<td></td>
                                	<td style="display:none;">0</td>
                                </tr>
                                <?php 
                                
                                /*$query = "
                                    SELECT *
                                    FROM ".$_SESSION['forums_table']."
                                    WHERE active='1'
                                    ORDER BY sequence ASC
                                ";
                                
                                try{
                                    $rsForum = $mLink->prepare($query);
                                    $aValues = array(
                                        //':forum_id' 	=> $forumID
                                    );
                                    $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                    $rsForum->execute($aValues);
                                }
                                catch(PDOException $error){
                                    // Log any error
                                    file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                }
                                
                                while($forum = $rsForum->fetch(PDO::FETCH_ASSOC)) {
                                    
                                    $forumID = $forum['forum_id'];
                                    //START Get Number Of Categories
                                    $query = "
                                        SELECT cat_id
                                        FROM ".$_SESSION['forum_categories_table']."
                                        WHERE forum_id=:forum_id
                                    ";
                                    
                                    try{
                                        $rsGetCat = $mLink->prepare($query);
                                        $aValues = array(
                                            ':forum_id' 	=> $forumID
                                        );
                                        $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                        $rsGetCat->execute($aValues);
                                    }
                                    catch(PDOException $error){
                                        // Log any error
                                        file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                    }
                                    $cnt = 0;
                                    
                                    //loop through forum ids and get their category IDs, and use counter to count categories
                                    while($cat = $rsGetCat->fetch(PDO::FETCH_ASSOC)) {
                                        
                                        $aCat[$forumID][$cnt] = $cat['cat_id'];
                                        
                                        $cnt++;
                                    }
                                    
                                    $cntCat = $cnt;
                                    //END Get Number Of Topics
                                    
                                    
                                    //START GET LAST POST	
            
                                    //Start counter for looping
                                    $newCnt = 0;
                                    
                                    //build WHERE clause from each category ID
                                    foreach($aCat[$forumID] as $key=>$catID){
                                        
                                        //Increment Counter
                                        $newCnt++;
                                        
                                        
                                        if($newCnt < $cntCat){
                                            //If counter is less than total number of categories
                                            $whereCat .= "cat_id='".$catID."' OR ";
                                        }else{
                                            //If counter is equal than total number of categories
                                            $whereCat .= "cat_id='".$catID."'";
                                        }
                                    }
                                    
                                    //Query Posts table and grab the most recent post of all provided categories
                                    $query = "
                                        SELECT *
                                        FROM ".$_SESSION['forum_posts_table']."
                                        WHERE ".$whereCat."
                                        ORDER BY timestamp DESC
                                        LIMIT 1
                                    ";
                                    
                                    try{
                                        $rsGetPosts = $mLink->prepare($query);
                                        $aValues = array(
                                            //':forum_id' 	=> $catID
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
                                    $topicID		= $post['topic_id'];
                                    $postUser		= get_member($mLink, $post['post_creator'], 'full name');
                                    $lastPostURL 	= get_post_url($mLink, $postID);
                                    
                                    
                                    if($postID != ""){
                                        //If postID is not empty/blank post link			
                                        $lastPost = '<a href="'.$lastPostURL.'" style="display:block;">'.get_topic($mLink, $topicID, 'title').'<hr style="margin:5px 0px;" />By: '.$postUser.', '.time_past($postDate).'</a>';	
                                    }else{
                                        //If postID is empty/blank, do not post link
                                        $lastPost = '<span class="label label-info">No Posts</span>';	
                                    }
                                    
                                    $whereCat = '';
                                    //END GET LAST POST
                                    
                                    $allowAccess = forumAccess($mLink, $forumID);
                                    
                                    if($allowAccess != 0){								
                                    ?>
                                    <tr>
                                        
                                        <td><a href="?page=04-01-00-002&forum=<?php echo $forum['forum_id'];?>" style="display:block;"><strong><?php echo $forum['forum_title'];?></strong><br /><small><?php echo substr($forum['forum_description'], 0, 250); ?></small></a></td>
                                        <td><?php echo $lastPost;?></td>
                                        <td><?php echo $cntCat;?></td>
                                        <td style="display:none;"><?php echo $forum['sequence']; ?></td>
                                    </tr>
                                    <?php
                                    }
                                }*/
                                ?>
                                
                            </tbody>
                            </table>
                            
                            
                           
                    </div><!--portlet-body-->
                </div><!--end portlet-->
                <!-- END FORUM TABLE-->
                
                <?php if($adminAccess != 0){?>
                <!--START INACTIVE FORUM TABLE-->
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption"><i class="icon-comments"></i>Inactive Forums</div>
                        <div class="tools">
                            <a href="" class="collapse" title="Expand/Collapse Portlet"></a>
                        </div>
                    </div>
                    <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover table-full-width" id="forum-inactive-list">
                            <thead>
                                <tr>
                                    
                                    <th>Forums</th>
                                    
                                    <th width="20%">Last Post</th>
                                    <th width="2%">Categories</th>
                                    <th style="display:none;"></th>
                                    
                                </tr>
                            </thead>
                            <tbody class="load-inactive-forums">
                                <?php 
                                
                                $query = "
                                    SELECT *
                                    FROM ".$_SESSION['forums_table']."
                                    WHERE active='0'
                                    ORDER BY sequence ASC
                                ";
                                
                                try{
                                    $rsForum = $mLink->prepare($query);
                                    $aValues = array(
                                        //':forum_id' 	=> $forumID
                                    );
                                    $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                    $rsForum->execute($aValues);
                                }
                                catch(PDOException $error){
                                    // Log any error
                                    file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                }
                                
                                while($forum = $rsForum->fetch(PDO::FETCH_ASSOC)) {
                                    
                                    $forumID = $forum['forum_id'];
                                    //START Get Number Of Categories
                                    $query = "
                                        SELECT cat_id
                                        FROM ".$_SESSION['forum_categories_table']."
                                        WHERE forum_id=:forum_id
                                    ";
                                    
                                    try{
                                        $rsGetCat = $mLink->prepare($query);
                                        $aValues = array(
                                            ':forum_id' 	=> $forumID
                                        );
                                        $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                        $rsGetCat->execute($aValues);
                                    }
                                    catch(PDOException $error){
                                        // Log any error
                                        file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                    }
                                    $cnt = 0;
                                    
                                    //loop through forum ids and get their category IDs, and use counter to count categories
                                    while($cat = $rsGetCat->fetch(PDO::FETCH_ASSOC)) {
                                        
                                        $aCat[$forumID][$cnt] = $cat['cat_id'];
                                        
                                        $cnt++;
                                    }
                                    
                                    $cntCat = $cnt;
                                    //END Get Number Of Topics
                                    
                                    
                                    //START GET LAST POST	
            
                                    //Start counter for looping
                                    $newCnt = 0;
                                    
                                    //build WHERE clause from each category ID
                                    foreach($aCat[$forumID] as $key=>$catID){
                                        
                                        //Increment Counter
                                        $newCnt++;
                                        
                                        
                                        if($newCnt < $cntCat){
                                            //If counter is less than total number of categories
                                            $whereCat .= "cat_id='".$catID."' OR ";
                                        }else{
                                            //If counter is equal than total number of categories
                                            $whereCat .= "cat_id='".$catID."'";
                                        }
                                    }
                                    
                                    //Query Posts table and grab the most recent post of all provided categories
                                    $query = "
                                        SELECT *
                                        FROM ".$_SESSION['forum_posts_table']."
                                        WHERE ".$whereCat."
                                        ORDER BY timestamp DESC
                                        LIMIT 1
                                    ";
                                    
                                    try{
                                        $rsGetPosts = $mLink->prepare($query);
                                        $aValues = array(
                                            //':forum_id' 	=> $catID
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
                                    $topicID		= $post['topic_id'];
                                    $postUser		= get_member($mLink, $post['post_creator'], 'full name');
                                    $lastPostURL 	= get_post_url($mLink, $postID);
                                    
                                    
                                    if($postID != ""){
                                        //If postID is not empty/blank post link			
                                        $lastPost = '<a href="'.$lastPostURL.'" style="display:block;">'.get_topic($mLink, $topicID, 'title').'<hr style="margin:5px 0px;" />By: '.$postUser.', '.time_past($postDate).'</a>';	
                                    }else{
                                        //If postID is empty/blank, do not post link
                                        $lastPost = '<span class="label label-info">No Posts</span>';	
                                    }
                                    
                                    $whereCat = '';
                                    //END GET LAST POST
                                    
                                    $allowAccess = forumAccess($mLink, $forumID);
                                    
                                    if($allowAccess != 0){								
                                    ?>
                                    <tr>
                                        
                                        <td><a href="?page=04-01-00-002&forum=<?php echo $forum['forum_id'];?>" style="display:block;"><strong><?php echo $forum['forum_title'];?></strong><br /><small><?php echo substr($forum['forum_description'], 0, 250); ?></small></a></td>
                                        <td><?php echo $lastPost;?></td>
                                        <td><?php echo $cntCat;?></td>
                                        <td style="display:none;"><?php echo $forum['sequence']; ?></td>
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
                
                
                
            </div><!--end column-->
        </div><!--row-->
        