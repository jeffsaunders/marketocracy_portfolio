<?php	
//START build badge array
$query = "
	SELECT * 
	FROM ".$_SESSION['badges_table']."
	WHERE active='1'
";

try{
	$rsBadge = $mLink->prepare($query);
	$aValues = array();
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsBadge->execute($aValues);
}

catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$aBadges = array();
	
while($badge = $rsBadge->fetch(PDO::FETCH_ASSOC)){
	$aBadges[$badge['badge_id']] = array(
		'badge_id'		=> $badge['badge_id'],
		'badge_name'	=> $badge['badge_name'],
		'type'			=> $badge['badge_type'],
		'badge_img'		=> '/images/badges/'.$badge['badge'],
		'badge_desc'	=> $badge['badge_desc'],
		'badge_group'	=> $badge['group'],
		'badge_weight'	=> $badge['weight'],
		'sub_type'		=> $badge['sub_type']
	);
	
}
//END build badge array

//START PAGINATION CODE
$query = "
	SELECT p.*, t.topic_creator, t.topic_title, t.cat_id
	FROM ".$_SESSION['forum_posts_table']." as p
	INNER JOIN ".$_SESSION['forum_topics_table']." as t ON t.topic_id=p.topic_id
	WHERE p.topic_id=:topic_id AND p.level='1'
	ORDER BY p.timestamp ASC
";

try{
	$rsCntPosts = $mLink->prepare($query);
	$aValues = array(
		':topic_id' 	=> $topicID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsCntPosts->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

// Here we have the total row count
$rows = $rsCntPosts->rowCount();

// This is the number of results we want displayed per page
$page_rows = $_SESSION['forum_posts_default'];
if(!isset($_SESSION['forum_posts_default'])){
	$page_rows = 10;	
}

//Check to see if user has defined number of rows
if(isset($_SESSION['forum_page_rows'])){
	if($_SESSION['forum_page_rows'] != NULL){
		$page_rows = $_SESSION['forum_page_rows'];	
	}
}

// This tells us the page number of our last page
$last = ceil($rows/$page_rows);

// This makes sure $last cannot be less than 1
if($last < 1){
	$last = 1;
}

// Establish the $pagenum variable
$pagenum = 1;


// Get pagenum from URL vars if it is present, else it is = 1
if(isset($_REQUEST['pn'])){
	$pagenum = preg_replace('#[^0-9]#', '', $_REQUEST['pn']);
}

// This makes sure the page number isn't below 1, or more than our $last page
if ($pagenum < 1) { 
    $pagenum = 1; 
} else if ($pagenum > $last) { 
    $pagenum = $last; 
}

// This sets the range of rows to query for the chosen $pagenum
$limit = 'LIMIT ' .($pagenum - 1) * $page_rows .',' .$page_rows;

//echo $limit;

// QUERY DATABASE TO GET COMMUNITY FORUM POSTS
$query = "
	SELECT p.*, t.topic_creator, t.topic_title, t.cat_id
	FROM ".$_SESSION['forum_posts_table']." as p
	INNER JOIN ".$_SESSION['forum_topics_table']." as t ON t.topic_id=p.topic_id
	WHERE p.topic_id=:topic_id AND p.level='1'
	ORDER BY p.timestamp ASC
	".$limit."
";

try{
	$rsPosts = $mLink->prepare($query);
	$aValues = array(
		':topic_id' 	=> $topicID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsPosts->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

// Establish the $paginationCtrls variable
$paginationCtrls = '<div style="text-align:center;">';


// If there is more than 1 page worth of results
if($last != 1){
	/* First we check if we are on page one. If we are then we don't need a link to 
	   the previous page or the first page so we do nothing. If we aren't then we
	   generate links to the first page, and to the previous page. */
	if ($pagenum > 1) {
        $previous = $pagenum - 1;
		$paginationCtrls .= '<a class="btn btn-sm btn-info" href="?page=04-01-00-004&forum='.$forumID.'&cat='.$catID.'&topic='.$topicID.'&pn='.$previous.'"><i class="icon-arrow-left"></i>&nbsp;&nbsp; Previous</a> &nbsp; &nbsp; ';
		// Render clickable number links that should appear on the left of the target page number
		for($i = $pagenum-4; $i < $pagenum; $i++){
			if($i > 0){
		        $paginationCtrls .= '<a class="btn btn-sm btn-info" href="?page=04-01-00-004&forum='.$forumID.'&cat='.$catID.'&topic='.$topicID.'&pn='.$i.'">'.$i.'</a> &nbsp; ';
			}
	    }
    }
	// Render the target page number, but without it being a link
	$paginationCtrls .= '<a href="javascript:void(0);" class="btn btn-sm btn-default">'.$pagenum.'</a> &nbsp; ';
	// Render clickable number links that should appear on the right of the target page number
	for($i = $pagenum+1; $i <= $last; $i++){
		$paginationCtrls .= '<a class="btn btn-sm btn-info" href="?page=04-01-00-004&forum='.$forumID.'&cat='.$catID.'&topic='.$topicID.'&pn='.$i.'">'.$i.'</a> &nbsp; ';
		if($i >= $pagenum+4){
			break;
		}
	}
	// This does the same as above, only checking if we are on the last page, and then generating the "Next"
    if ($pagenum != $last) {
        $next = $pagenum + 1;
        $paginationCtrls .= ' &nbsp; &nbsp; <a class="btn btn-sm btn-info" href="?page=04-01-00-004&forum='.$forumID.'&cat='.$catID.'&topic='.$topicID.'&pn='.$next.'">Next &nbsp;&nbsp;<i class="icon-arrow-right"></i></a> ';
    }
}

$paginationCtrls .= '</div>';

//+-------------------------------------------------------------------------------------------------------------+
//|										START PRINT RESPONSE FUNCTION											|
//+-------------------------------------------------------------------------------------------------------------+

function printNestedPosts($parent, $response_array, $mLink, $showConvo) {
	foreach ($response_array as $post) {
		if($parent == $post['parent_post_id']) {
			
			$fullName = get_member($mLink, $post['member_id'], 'full name');
			
			if(get_member($mLink, $post['member_id'], 'admin') == "1"){
				$isAdmin = "(Staff)";	
			}else{
				$isAdmin = "";	
			}
			
			if($post['parent_post_id'] != NULL){
				$replyWord = '&#8627; ';
			}else{
				$replyWord = "";
			}
			
			//Check to see if repsonse has been deleted
			if($post['deleted'] == '0'){
				
				//response is not deleted
				
				if($post['member_id'] == $post['topic_creator']){
					//orginal poster
					if($post['anonymous'] == "1"){
						if($_SESSION['admin'] == "1"){
							$title = "".$replyWord." Anonymous (".$fullName.") (Original Poster)";
							$titleStyle = "background:#414247;";
						}else{
							$title = "".$replyWord." Anonymous";
							$titleStyle = "background:#414247;";
						}
					}else{
						$title = "".$replyWord." ".$fullName." (Original Poster) ".$isAdmin."";
						$titleStyle = "background:#414247;";
					}
				}else{
					//not original poster
					if($post['anonymous'] == "1"){
						if($_SESSION['admin'] == "1"){
							$title = "".$replyWord." Anonymous (".$fullName.")";
							$titleStyle = "background:#414247;";
						}else{
							$title = "".$replyWord." Anonymous";
							$titleStyle = "background:#414247;";
						}
					}else{
						$title = "".$replyWord." ".$fullName." ".$isAdmin."";
						$titleStyle = "background:#414247;";
					}
				}
				$deleted = '';
				$hideButtons = '';
				$response = $post['response'];
				$hideUser = '';
			}else{
				//Response is deleted
				
				$title = "Response Deleted";
				$titleStyle = "background:#414247;";
				$deleted = 'opacity:.5 !important;';
				$hideButtons = 'style="display:none;"';
				$response = "This repsonse has been removed by ".get_user_title($post['deleted_by'], $mLink, $post['section_id']).".";
				$hideUser = "style='display:none;'";
			}	
			
			if($post['off_topic'] == '1'){
				
				$title = "Response Removed";
				$titleStyle = "background:#414247;";
				$deleted = 'opacity:.5 !important;';
				$hideButtons = 'style="display:none;"';
				$response = "This repsonse has been removed for being off topic. We encourage our members to be objective and stay on point with the current topic.";
				$hideUser = "style='display:none;'";
					
			}
			
			//Check to see if the post has an edit
			if(empty($post['edit_post'])){
				$response = $response;
				$editTime = '';	
				$showEditDetails = '';
			}else{
				$response = ''.$post['edit_post'].'';
				$editTime = '<br /><small>Edited: '.time_past($post['edit_time']).'</small>';	
				
				//Show edit details to admin
				if($_SESSION['admin'] == '1' || $_SESSION['moderator'] == '1'){
					$showEditDetails = '<a class="accordion-toggle" data-toggle="collapse" href="#collapse_1">Show/Hide Edit</a><div id="collapse_1" class="note note-info collapse"><strong>Post Edited By: '.get_member($mLink, $post['edit_by'], "full name").'</strong><br /><br /><p>Original Post:<br /> '.$post['response'].'</p></div>';	
				}else{
					$showEditDetails = '';
				}
			}
			
			
			
			//Check to see if the user is looking at his/her own response
			if($_SESSION['member_id'] == $post['member_id']){
				//Check to see if user is the original poster
				if($post['member_id'] == $post['topic_creator']){
					$title = "".$replyWord." Your Post (Original Poster) ".$isAdmin."";
					$titleStyle = "background:#ccc8c8;color:#000000;";
				}else{
					$title = "".$replyWord." Your Post ".$isAdmin."";
					$titleStyle = "background:#ccc8c8;color:#000000;";
				}
			}
			
			// If post is the original post
			if($post['orig_post'] == "1"){
				$titleStyle = "background:#5BC0DE;color:#ffffff;";
				$postHeader = '<p><strong>'.$post['topic_title'].'</strong></p><hr  style="margin:0px;" />';	
			}else{
				$postHeader = '';	
			}
			
			// Explode database columns into an array
			$aAgree 		= explode("|", $post['agree']);
			$aDisagree 	= explode("|", $post['disagree']);
			
			// Setup counter for items in AGREE array
			$cntAgree = 0;
			if($aAgree[0] != ""){
				$cntAgree = count($aAgree);
			}
			
			
			// Setup counter for items in DISAGREE array
			$cntDisagree = 0;
			if($aDisagree[0] != ""){
				$cntDisagree = count($aDisagree);
			}
			
			
			// START BUILDING AGREE MODALS
			if($cntAgree > 0){
				
				if($cntAgree <= 1){
					$popTitle = "Member agrees with this response.";	
				}else{
					$popTitle = "Members agree with this response.";	
				}
			?>
				<!-- BEGIN RESPONSE AGREE MODAL-->
				<div class="modal fade" id="agree-<?php echo $post['post_id'];?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				  <div class="modal-dialog">
					 <div class="modal-content">
						<div class="modal-header">
						   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
						   <h4 class="modal-title"><?php echo $cntAgree; echo " ".$popTitle."";?></h4>
						</div>
						<div class="modal-body">
							
							<ul class="agree-disagree">
								<?php
								// echo each list item in array
								foreach($aAgree as $data){
									
									// Expand Member Data into an array
									$user	= explode("-", $data);
									
									// Get member info
									$member = get_member($mLink, $user[0], 'full name');
									
									// Echo list item
									echo '<li><a href="?page=04-00-00-001&member='.$user[0].'">
											<div class="col1-a">
												<img src="'.get_member($mLink, $user[0], 'profileImageTb').'" width="40" height="40" border="0" />
											</div>
											
											<div class="col2-a">
												<p>
													<strong>'.$member.'</strong><br />
													<span>'.time_past($user[1]).'</span>
												</p>
											</div>
											<div class="clear"></div><!--clear-->
										</a></li>';
									
								}
								?>
							
							
							</ul>
				
						</div><!--modal-body-->
						
						<div class="modal-footer">
						   <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						</div>
					 </div>
					 <!-- /.modal-content -->
				  </div>
				  <!-- /.modal-dialog -->
				</div>
				<!-- END RESPONSE AGREE MODAL-->
			<?php	
			}
			// END BUILDING AGREE MODALS
			
			// START BUILDING DISAGREE MODALS
			if($cntDisagree > 0){
				
				if($cntDisgree <= 1){
					$popTitle = "Member disagrees with this response.";	
				}else{
					$popTitle = "Members agree with this response.";	
				}
			?>
				<!-- BEGIN RESPONSE DISAGREE MODAL-->
				<div class="modal fade" id="disagree-<?php echo $post['post_id'];?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				  <div class="modal-dialog">
					 <div class="modal-content">
						<div class="modal-header">
						   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
						   <h4 class="modal-title"><?php echo $cntDisagree;?> <?php echo $popTitle;?></h4>
						</div>
						<div class="modal-body">
							
							<ul class="agree-disagree">
								<?php
								// echo each list item in array
								foreach($aDisagree as $data){
									
									// Expand Member Data into an array
									$user	= explode("-", $data);
									
									// Get member info
									$member = get_member($mLink, $user[0], 'full name');
									
									// Echo list item
									echo '<li><a href="?page=04-00-00-001&member='.$user[0].'">
											<div class="col1-a">
												<img src="'.get_member($mLink, $user[0], 'profileImageTb').'" width="40" height="40" border="0" />
											</div>
											
											<div class="col2-a">
												<p>
													<strong>'.$member.'</strong><br />
													<span>'.time_past($user[1]).'</span>
												</p>
											</div>
											<div class="clear"></div><!--clear-->
										</a></li>';
									
								}
								?>
							
							
							</ul>
				
						</div><!--modal-body-->
						
						<div class="modal-footer">
						   <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						</div>
					 </div>
					 <!-- /.modal-content -->
				  </div>
				  <!-- /.modal-dialog -->
				</div>
				<!-- END RESPONSE DISAGREE MODAL-->
			<?php	
			}
			//END BUILDING DISAGREE MODALS
			
			
			// START AGREE BUTTON
			if($cntAgree != 0){
			
				// Search to see if current user has already pressed agree
				foreach($aAgree as $key=>$value){
					// Eplode array into multidimensional array
					$aAgreeMember[$key] = explode('-', $value);
				}
				
				$arrayKey = searchForId($_SESSION['member_id'], $aAgree);
	
				
				// START IF MEMBER = MEMBER
				if($aAgreeMember[$arrayKey][0] == $_SESSION['member_id']){
					//Found Member
					//echo "FOUND IT";
					$postStatusAgree = "you";
					$btnStatus = "default";
					$btnType = "You Agree";
					$btnTitle = "Click here to undo.";
					
					if($cntAgree == 1){
						$postStatusAgree = "single-you";
							
					}
							
				}else{
					//Did not find member
					//echo "NOT FOUND";
					$postStatusAgree = "others";
					$btnStatus = "success";
					$btnType = "Agree";
					$btnTitle = "Click here to agree.";
					
					if($cntAgree == 1){
						$postStatusAgree = "single";
					}
					
					if($cntAgree <= 2){
						$postStatusAgree = "2others";
						
						$mCnt = 0;
						$memberAgree = "";
						foreach($aAgree as $data){
							$mCnt = $mCnt + 1;
							// Expand Member Data into an array
							$userAgree	= explode("-", $data);
							//echo $mCnt;
							if($mCnt <= 2){
								
								$memberAgree .= get_member($mLink, $userAgree[0], 'full name');
								if($mCnt < 2 && $cntAgree != 1){
									$memberAgree .= " and ";
								}
							}
						}	
					}// end $cntAgree <= 2
					
					// START LOGIC BLOCK
					if($cntAgree > 2){
						$postStatusAgree = "others";
						
						$mCnt = 0;
						$memberAgree = "";
						foreach($aAgree as $data){
							$mCnt = $mCnt + 1;
							// Expand Member Data into an array
							$userAgree	= explode("-", $data);
							//echo $mCnt;
							if($mCnt < 3){
								//$memberAgree .= ", ";
								$memberAgree .= get_member($mLink, $userAgree[0], 'full name');
								if($mCnt < 2){
									$memberAgree .= ", ";
								}
							}
						}	
					}
					// END LOGIC BLOCK
						
				}
				// END IF MEMBER = MEMBER
				
				$agreeBTN = '<button type="button" class="btn btn-'.$btnStatus.' btn-xs" onClick="agreeDisagree(\'agree\',\''.$post['post_id'].'\', \''.$post['member_id'].'\',\''.$post['topic_id'].'\')" title="'.$btnTitle.'">'.$btnType.'</button>';
				
				$agreeLiBtn = '<li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-'.$btnStatus.' btn-xs btn-full" onClick="agreeDisagree(\'agree\',\''.$post['post_id'].'\', \''.$post['member_id'].'\',\''.$post['topic_id'].'\')" title="'.$btnTitle.'">'.$btnType.'</button></li>';
			}else{
				
				$agreeBTN = '<button type="button" class="btn btn-success btn-xs" onClick="agreeDisagree(\'agree\',\''.$post['post_id'].'\', \''.$post['member_id'].'\',\''.$post['topic_id'].'\')"><i class=" icon-arrow-up"></i> Agree</button>';
				
				$agreeLiBtn = '<li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-success btn-xs btn-full" onClick="agreeDisagree(\'agree\',\''.$post['post_id'].'\', \''.$post['member_id'].'\',\''.$post['topic_id'].'\')"><i class=" icon-arrow-up"></i> Agree</button></li>';
				
			}
			// END AGREE BUTTON
			
			//START DISAGREE BUTTON
			if($cntDisagree != 0){
				
				// Search to see if current user has already pressed agree
				foreach($aDisagree as $key=>$value){
					// Eplode array into multidimensional array
					$aDisagreeMember[$key] = explode('-', $value);
				}
				
				// Set var for array key if found
				$arrayKey = searchForId($_SESSION['member_id'], $aDisagree);
				
				if($aDisagreeMember[$arrayKey][0] == $_SESSION['member_id']){
					//Found Member
					$postStatusDisagree = "you";
					$btnStatus = "default";
					$btnType = "You Disagree";
					$btnTitle = "Click here to undo.";
					
					if($cntDisagree == 1){
						$postStatusDisagree = "single-you";
					}
							
				}else{
					//Did not find member
					$postStatusDisagree = "others";
					$btnStatus = "info";
					$btnType = "Disagree";
					$btnTitle = "Click here to disagree.";
					
					if($cntDisagree == 1){
						$postStatusDisagree = "single";
					}
					
					if($cntDisagree <= 2){
						$postStatusDisagree = "2others";
						
						$mCnt = 0;
						$memberDisagree = "";
						foreach($aDisagree as $data){
							$mCnt = $mCnt + 1;
							// Expand Member Data into an array
							$userDisagree = explode("-", $data);
							//echo $mCnt;
							
							if($mCnt <= 2){
								
								$memberDisagree .= get_member($mLink, $userDisagree[0], 'full name');
								if($mCnt < 2 && $cntDisagree != 1){
									$memberDisagree .= " and ";
								}
							}
						}	
					}// end $cntDisagree <= 2
					
					if($cntDisagree > 2){
						$postStatusDisagree = "others";
						
						$mCnt = 0;
						$memberDisagree = "";
						foreach($aDisagree as $data){
							$mCnt = $mCnt + 1;
							// Expand Member Data into an array
							$userDisagree	= explode("-", $data);
							//echo $mCnt;
							if($mCnt < 3){
								//$memberAgree .= ", ";
								$memberDisagree .= get_member($mLink, $userDisagree[0], 'full name');
								if($mCnt < 2){
									$memberDisagree .= ", ";
								}
							}
						}	
					}// END $CNTdISAGREE > 2
						
				}
				
				$disagreeBTN = '<button type="button" class="btn btn-'.$btnStatus.' btn-xs" onClick="agreeDisagree(\'disagree\',\''.$post['post_id'].'\', \''.$post['member_id'].'\',\''.$post['topic_id'].'\')" title="'.$btnTitle.'">'.$btnType.'</button>';
				
				$disagreeLiBtn = '<li class="btn-li"><button type="button" class="btn btn-'.$btnStatus.' btn-xs btn-full" onClick="agreeDisagree(\'disagree\',\''.$post['post_id'].'\', \''.$post['member_id'].'\',\''.$post['topic_id'].'\')" title="'.$btnTitle.'">'.$btnType.'</button></li>';
			}else{
				
				$disagreeBTN = '<button type="button" class="btn btn-info btn-xs" onClick="agreeDisagree(\'disagree\',\''.$post['post_id'].'\', \''.$post['member_id'].'\',\''.$post['topic_id'].'\')"><i class="icon-arrow-down"></i> Disagree</button>';
				
				$disagreeLiBtn = '<li class="btn-li"><button type="button" href="javascript:void(0);" class="btn btn-xs btn-info btn-full" onClick="agreeDisagree(\'disagree\',\''.$post['post_id'].'\', \''.$post['member_id'].'\',\''.$post['topic_id'].'\')"><i class="icon-arrow-down"></i> Disagree</button></li>';
			
			}
			//END DISAGREE BUTTON
			
			//START REPLY BUTTON
			if($post['level'] < $_SESSION['max_reply_level_ca']){
				if($post['orig_post'] == "1"){
					$postBTN = '';
					$postLiBtn = '';
				}else{
					$postBTN = '<button type="button" class="btn btn-warning btn-xs" onClick="showReply(\''.$post['post_id'].'\')" title="Click here to reply"><i class="icon-share-alt"></i> Reply</button>';
					$postLiBtn = '<li class="btn-li"><button type="button" href="javascript:void(0);" class="btn btn-xs btn-warning btn-full" onClick="showReply(\''.$post['post_id'].'\')" title="Click here to reply"><i class="icon-share-alt"></i> Reply</button></li><li class="divider"></li>';
				}
			}else{
				$postBTN = '';
				$postLiBtn = '';	
			}
			//END REPLY BUTTON
			
			//START PROMO AND DEMOTE AND NEW TOPIC BUTTONS
			
			// Check to see if user is an admin or administrator
			if($_SESSION['admin'] == "1" || $_SESSION['moderator'] == "1"){
				
				// Check to see if post is a main level post, not a reply
				if($post['level'] == '1'){
					
					// Check to see if the post IS NOT the original post
					if($post['orig_post'] != '1'){
						$promoteBTN = '<li class="divider"></li><li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-xs btn-success btn-full" onClick="" title="Click here to promote post."><i class="icon-star"></i> Promote Post</button></li>';
						$demoteBTN = '<li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-xs btn-warning btn-full" onClick="" title="Click here to demote post."><i class="icon-star-empty"></i> Demote Post</button></li>';	
						$newTopicBTN = '<li class="btn-li"><button type="button" class="btn btn-xs btn-info btn-full" onClick="" title="Click here to create new topic from post."><i class="icon-plus"></i> Create Topic</button></li>';
					}else{
						//Clear variables
						$promoteBTN = '';
						$demoteBTN = '';
						$newTopicBTN = '';
					}
				}
			}
			//END PROMO AND DEMOTE BUTTONS
			
			//START EDIT BUTTON
			if($_SESSION['admin'] == "1" || $post['member_id'] == $_SESSION['member_id']){
				$editBTN = '<button type="button" class="btn btn-edit btn-xs" onClick="showEdit(\''.$post['post_id'].'\')" title="Click here to edit"><i class="icon-pencil"></i> Edit</button>';	
				
				$editLiBtn = '<li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-edit btn-xs btn-full" onClick="showEdit(\''.$post['post_id'].'\')" title="Click here to edit"><i class="icon-pencil"></i> Edit</button></li>';
			}else{
				$editBTN = '';
				$editLiBtn = '';
			}
			//END EDIT BUTTON
			
			// START AGREE LOGIC SWITH
			if($cntAgree == 3){
				$otherText = "other";	
			}else{
				$otherText = "others";
			}
			
			if($cntAgree == 1){
				$agreeText = "agrees";	
			}else{
				$agreeText = "agree";	
			}
			
			switch($postStatusAgree){
				case "single":$statusMessageA = '<a href="#agree-'.$post['post_id'].'" data-toggle="modal">'.$memberAgree.'</a> agrees with this.';break;
				case "single-you":$statusMessageA = '<a href="#agree-'.$post['post_id'].'" data-toggle="modal">You</a> agree with this.';break;
				case "you":$statusMessageA = 'You, and <a href="#agree-'.$post['post_id'].'" data-toggle="modal">'.($cntAgree - 1).' others</a> agree with this.';break;
				case "others":$statusMessageA = ''.$memberAgree.' and <a href="#agree-'.$post['post_id'].'" data-toggle="modal">'.($cntAgree - 2).' '.$otherText.'</a> agree with this.';break;
				case "2others":$statusMessageA = '<a href="#agree-'.$post['post_id'].'" data-toggle="modal">'.$memberAgree.'</a> '.$agreeText.' with this.';break;
				default: $statusMessageA = '';
					
			}
			// END AGREE LOGIC SWITCH
			
			// START DISAGREE LOGIC SWITCH
			if($cntDisagree == 3){
				$otherText = "other";	
			}else{
				$otherText = "others";
			}
			
			if($cntDisagree == 1){
				$disagreeText = "disagrees";	
			}else{
				$disagreeText = "disagree";	
			}
			
			switch($postStatusDisagree){
				case "single":$statusMessageD = '<a href="#disagree-'.$post['post_id'].'" data-toggle="modal">'.$memberDisagree.'</a> disagrees with this.';break;
				case "single-you":$statusMessageD = '<a href="#disagree-'.$post['post_id'].'" data-toggle="modal">You</a> disagree with this.';break;
				case "you":$statusMessageD = 'You, and <a href="#disagree-'.$post['post_id'].'" data-toggle="modal">'.($cntDisagree - 1).' others</a> disagree with this.';break;
				case "others":$statusMessageD = ''.$memberDisagree.' and <a href="#disagree-'.$post['post_id'].'" data-toggle="modal">'.($cntDisagree - 2).' '.$otherText.'</a> disagree with this.';break;
				case "2others":$statusMessageD = '<a href="#disagree-'.$post['post_id'].'" data-toggle="modal">'.$memberDisagree.'</a> '.$disagreeText.' with this.';break;
				default: $statusMessageD = '';
					
			}
			//END DISAGREE LOGIC SWITCH
			
			// If there are replys with agrees or disagrees display them
			if($post['deleted'] == "0"){
				if($cntAgree >= 1 || $cntDisagree >= 1){
					$postStats = '
					<div class="agree-disagree-area">
						
						<p><small>'.$statusMessageA.' '.$statusMessageD.'</small></p>
					   
					</div>';
				}else{
					$postStats = '';	
				}
			}else{
				$postStats = '';	
			}
			
			// Reset Variables
			$postStatusDisagree = "";
			$postStatusAgree = "";
			$memberAgree = "";
			$memberDisagree = "";
			$mCnt = "";				
			
			// Reply indentation based on level.
			switch($post['level']){
				case "1": $movePx = "0";break;
				case "2": $movePx = "20";break;
				case "3": $movePx = "40";break;
				case "4": $movePx = "60";break;
				case "5": $movePx = "80";break;
				case "6": $movePx = "100";break;
			}
			
			//START ADMIN DELETE BUTTON
			if($_SESSION['admin'] == "1"){
				if($post['deleted'] == "0"){
					$deleteBTN = '<a href="javascript:void(0);" onClick="deleteReply(\''.$post['post_id'].'\',\'delete\')" class="btn btn-xs btn-danger">Delete</a>';
					$deleteLiBtn = '<li class="divider"></li><li class="btn-li"><button type="button" onClick="deleteReply(\''.$post['post_id'].'\',\'delete\')" class="btn btn-xs btn-danger btn-full">Delete</button></li>';
					$deletedResponse = '';
					$deletedTitle = '';
					$restoreBTN = '';
					$restoreLiBtn = '';
				}else{
					$deleteBTN = '';
					$deleteLiBtn = '';
					$deletedResponse = '<p class="last">('.$post['response'].')</p>';
					$deletedTitle = '('.$fullName.')';
					$restoreBTN = '<a href="javascript:void(0);" onClick="deleteReply(\''.$post['post_id'].'\',\'restore\')" class="btn btn-xs btn-danger">Restore</a>';	
					$restoreLiBtn = '<li class="btn-li"><button type="button" onClick="deleteReply(\''.$post['post_id'].'\',\'restore\')" class="btn btn-xs btn-danger btn-full">Restore</button></li>';
				}
			}else{
				$deleteBTN = '';
				$deletedResponse = '';
				$deletedTitle = '';
				$restoreBTN = '';
			}
			//END ADMIN DELETE BUTTON
			
			//START CHILD CHECK
			if(isset($_REQUEST['pn'])){
				$addPN = '&pn='.$_REQUEST['pn'].'';	
			}
			if($post['has_child'] == "1"){
				$childBlock = '
					<div class="response-container" style="margin-left:20px !important;">
						
						<div class="response-header" style="background:#5BC0DE;color:#ffffff;">
							<div class="responder" style="float:left;">
								<a href="'.$_SESSION['site_root'].'?page=04-01-00-004&forum='.$post['forum_id'].'&cat='.$post['cat_id'].'&topic='.$post['topic_id'].''.$addPN.'&child='.$post['post_id'].'" style="display:block;color:#ffffff;" onClick="">&#8627; Click Here To View Replies</a>
							</div><!--responder-->
							
							<div class="clear"></div>
						</div><!--response-header-->
						
					</div><!--response-container-->
				';	
			}else{
				if($post['getChild'] == $post['post_id']) {
					$childBlock = '<a href="'.$_SESSION['site_root'].'?page=04-01-00-004&forum='.$post['forum_id'].'&cat='.$post['cat_id'].'&topic='.$post['topic_id'].''.$addPN.'&post='.$post['post_id'].'">[-] Collapse Replies</a>';
					
				}else{
					$childBlock = '';
				}
			}
			//END CHILD CHECK
			
			if($post['goToPost'] != ''){
				if($post['post_id'] == $post['goToPost']){
					$titleStyle = 'background:#5cb85c;';	
				}
			}
			
			//START BADGES AREA
			$aBadges = $post['badges'];
			$aListBadges = $post['listBadges'];
			
			
			
			$showBadges2 = '';
			$aListFundBadges = array();
			foreach($aBadges as $fundID=>$aFundBadges){
				
				$fundSymbol2 	= get_funds($mLink, $fundID, 'fundSymbol');
				
				foreach($aFundBadges as $key=>$badgeID){
					
					$aListFundBadges[] = array(
						'fundSymbol'	=> $fundSymbol2,
						'badge_id'		=> $badgeID,
						'weight'		=> $aListBadges[$badgeID]['badge_weight'],
						'sub_type'		=> $aListBadges[$badgeID]['sub_type']
					);
					
					
				}
			}
			
			usort($aListFundBadges, function($a, $b) {
				return $a['weight'] - $b['weight'];
			});
			
			//$aShowBadges = array();
			unset($aShowBadges);
			unset($badge10);
			unset($badge5);
			unset($badge3);
			unset($badge1);
			
			foreach($aListFundBadges as $key=>$aBadges2){
				
				if(!isset($badge10)){
					if($aBadges2['sub_type'] == '10'){
						$badge10 = $key;
						
						$aShowBadges[$key] = $aListFundBadges[$key];
					}
				}
				
				if(!isset($badge5)){
					if($aBadges2['sub_type'] == '5'){
						$badge5 = $key;
						
						$aShowBadges[$key] = $aListFundBadges[$key];
					}	
				}
				
				if(!isset($badge3)){
					if($aBadges2['sub_type'] == '3'){
						$badge3 = $key;
						
						$aShowBadges[$key] = $aListFundBadges[$key];
					}	
				}
				
				if(!isset($badge1)){
					if($aBadges2['sub_type'] == '1'){
						$badge1 = $key;
						
						$aShowBadges[$key] = $aListFundBadges[$key];
					}	
				}
					
			}
			
			//create short list
			$shortCnt = 0;
			$splitCnt = 0;
			foreach($aShowBadges as $key=>$aBadgeInfo){
				
				$shortCnt++;
				$splitCnt++;
				
				
				
				if($shortCnt <= 4){
					
					$badgeImg 		= $aListBadges[$aBadgeInfo['badge_id']]['badge_img'];
					$badgeDesc		= $aListBadges[$aBadgeInfo['badge_id']]['badge_desc'];
					$fundSymbol3	= $aBadgeInfo['fundSymbol'];
							
					$showBadges2 .= '<a href="#show-badges" data-toggle="modal" onclick="loadBadges(\''.$post['member_id'].'\',\''.$fullName.'\')"><img src="'.$badgeImg.'" alt="'.$badgeDesc.'" title="'.strtoupper($fundSymbol3).': '.$badgeDesc.'" width="20" height="20" border="0" style="border:none !important;"/></a>&nbsp;'; 
					
					/*if($splitCnt == 2){
						$splitCnt = 0;
						$showBadges2 .= '<br />';	
					}*/
				}
					
			}
			
			/*if($_SESSION['admin'] == '1'){
				$testArea = '<pre>';
				//$showBadges .= print_r($aListBadges, true);
				$testArea .= print_r($aShowBadges, true);
				$testArea .= print_r($aListFundBadges, true);
				
				$testArea .='</pre>';
			}else{
				//$showBadges = '';	
				//$showBadges2 = '';
				$testArea = '';
			}*/
			//END BADGES AREA
			
			$subscription = get_subscription($mLink, $post['member_id']);
			
			if($subscription['product_id'] == '10'){
				$postUserTitle = 'Manager';
			}else{
				$postUserTitle = get_user_title($post['member_id'], $mLink, $post['section_id']);
			}
			
			
			//USER INFO
			if($post['convo_id'] == $post['post_id']){
				$userInfo = '
					<p style="text-align:center;">'.$postUserTitle.'<p>
								
					<p style="text-align:center;"><a href="?page=04-00-00-001&member='.$post['member_id'].'" target="_blank"><img src="'.get_member($mLink, $post['member_id'], 'profileImage').'" alt="'.$fullName.'" width="70" height="70"/></a></p>
					<!--<p style="text-align:center;"><span class="label label-info"><strong><i class="icon-star"></i> Rank:</strong> 38 </span></p>-->
					<p style="text-align:center;"><span class="label label-warning"><strong><i class="icon-comments"></i> Posts:</strong> '.get_forum_info('numPosts', $mLink, $post['member_id']).'</span></p>
					<p style="text-align:center;"><small><span class="label label-default">Member Since:</span><br> '.date("M. j, Y", get_member($mLink, $post['member_id'], 'joinDate')).'</small></p>
					<!--<p style="text-align:center;">'.$showBadges2.'</p>-->
					
				';
			}else{
				$userInfo ='
					<a href="?page=04-00-00-001&member='.$post['member_id'].'" target="_blank" class="btn btn-xs btn-info">View Info</a>
				';
			}
			
			if($post['off_topic'] == '1'){
				$userInfo = '';	
			}
			
			//START RESPONSE HTML BLOCK			  
			$html .= '
				<div class="response-container" style="margin-left:'.$movePx.'px !important; '.$deleted.'" id="post-id-'.$post['post_id'].'">
					<div class="response-header response-header-'.$post['convo_id'].'" style="'.$titleStyle.'" >
						<div class="responder" style="float:left;">
							'.$title.' '.$deletedTitle.' '.$showBadges2.'
						</div><!--responder-->
						<div class="response-date">
							'.date('D m/d/Y', $post['timestamp']).' at '.date('h:i A T', $post['timestamp']).' &nbsp;&nbsp;'.$restoreBTN.'
							<div class="btn-group" '.$hideButtons.'>
								<button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown"><i class="icon-cog"></i> Choose Action</button>
								<!--<button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown"><i class="icon-angle-down"></i></button>-->
								
								<ul class="dropdown-menu" role="menu">
								   '.$postLiBtn.'
								   
								   '.$agreeLiBtn.'
								   '.$disagreeLiBtn.'
								   <li class="divider"></li>
								   <li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-default btn-xs btn-full" onClick="copyToClipboard(\''.$_SESSION['site_root'].'?postid='.$post['post_id'].'\')"><i class="icon-link"></i> Link</button></li>
								   '.$editLiBtn.'
								   '.$promoteBTN.'
								   '.$demoteBTN.'
								   '.$newTopicBTN.'
								   '.$deleteLiBtn.'
								   '.$restoreLiBtn.'
								   
								</ul>
		
							 </div><!--button-group-->
							 
						</div><!--response-date-->
						<div class="clear"></div>
					</div><!--response-header-->
					
					<div class="response-body">
						<div class="row">
							<div class="col-md-12">
								
								<div class="table-responsive">
									<table>
										<tr>
											<td valign="top" class="member-info" style="padding-right:10px;">'.$userInfo.'</td>
												
											<td valign="top" style="padding-left:10px;>
												'.$postHeader.'
												<p class="last">'.$response.''.$editTime.'</p>
												'.$showEditDetails.'
												'.$deletedResponse.'
												'.$testArea.'
											</td>
										</tr>
									</table>
								</div>
								
							</div><!-col-md-12-->
							
						</div><!--row-->
						
						<div class="clear"></div><!--clear-->
						
						<div id="replyForm-'.$post['post_id'].'" style="display:none;">
							<!-- BEGIN FORM-->
							<form action="process/ajax/community-forum-processes.php?process=5" method="post" class="reply-'.$post['post_id'].'" name="reply-'.$post['post_id'].'">
								<div class="form-body">
									<div id="postUserError-'.$post['post_id'].'">
									</div>
										
									<div class="form-group">
										<textarea name="post-reply" id="reply-'.$post['post_id'].'" class="form-control reply-editor" rows="4" style="resize:vertical;"></textarea>
										<span class="help-block"><strong>Note:</strong> This is a reply to the corresponding post. If you wish to post a response to the topic, please click the "Post Reply" button at the top or bottom of the page.</span>
									</div>
									<input type="hidden" name="cat_id" value="'.$post['cat_id'].'"  />
									<input type="hidden" name="topic_id" value="'.$post['topic_id'].'"  />
									<input type="hidden" name="post_id" value="'.$post['post_id'].'" />
									<input type="hidden" name="level" value="'.$post['level'].'" />
									<input type="hidden" name="forum_id" value="'.$post['forum_id'].'" />
									<input type="hidden" name="convo_id" value="'.$post['convo_id'].'" />
									
									<div style="text-align:right">		
										<input type="submit" onClick="postReply(\''.$post['post_id'].'\', \''.$post['convo_id'].'\')" value="Reply" id="submit-reply-'.$post['post_id'].'" class="btn btn-warning btn-sm">
										<button type="button" onClick="hideReply(\''.$post['post_id'].'\')" class="btn btn-sm btn-info">Cancel</button>
									</div>
									
								</div><!--form-body-->
							</form>
							<!-- END FORM-->
						</div><!--reply-form-->
						
						<div id="editForm-'.$post['post_id'].'" style="display:none;">
							<!-- BEGIN FORM-->
							<form action="process/ajax/community-forum-processes.php?process=10" method="post" class="edit-'.$post['post_id'].'" name="edit-'.$post['post_id'].'">
								<div class="form-body">
									<div id="editUserError-'.$post['post_id'].'">
									</div>
										
									<div class="form-group">
										<label>Edit Post:</label>
										<textarea name="post-edit" id="post-edit-'.$post['post_id'].'" class="form-control" rows="4" style="resize:vertical;">'.$response.'</textarea>
									</div>
									<input type="hidden" name="post_id" value="'.$post['post_id'].'" />
									
									<div style="text-align:right">		
										<input type="submit" onClick="postEdit(\''.$post['post_id'].'\')" value="Post Edit" class="btn btn-edit btn-sm">
										<button type="button" onClick="hideEdit(\''.$post['post_id'].'\')" class="btn btn-sm btn-info">Cancel</button>
									</div>
									
								</div><!--form-body-->
							</form>
							<!-- END FORM-->
						</div><!--edit-form-->
						 
					</div><!--response-body-->
					
					'. $postStats.'
					
				</div><!--response-container-->
				
				'.$childBlock.'
			';
			
			
			
			//Start Nesting Loop
			$html .= printNestedPosts($post['post_id'], $response_array, $mLink);
			
		}
	}
	
	return $html;
}
//+-------------------------------------------------------------------------------------------------------------+
//|											END PRINT RESPONSE FUNCTION											|
//+-------------------------------------------------------------------------------------------------------------+

// START LOOP FOR EACH RESPONSE 
$posts_array = array();
$aQueries = array();

while($post = $rsPosts->fetch(PDO::FETCH_ASSOC)){
	
	//$aFundBadges = array();
	//Get badge info for post creator
	/*$query = "
		SELECT fund_id, badges
		FROM ".$_SESSION['fund_settings_table']."
		WHERE fund_id LIKE :member_id AND badges<>''
	";
	
	
	try{
		$rsBadges = $mLink->prepare($query);
		$aValues = array(
			':member_id' => $post['post_creator'].'-%'
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsBadges->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$aQueries[] = $preparedQuery;
	while($badges = $rsBadges->fetch(PDO::FETCH_ASSOC)){
	
		$aFundBadges[$post['post_creator']][$badges['fund_id']] = explode(',',$badges['badges']);
		//$aQueries[] = $query;
		
		//echo $query;
	}*/
	
	$aFundBadges = get_fund_badges($mLink, $post['post_creator'], 'forum-title-bar');
	
	//END get badge info for post creator
	
	
	//Check to see if Post has children
	$query = "
		SELECT post_id
		FROM ".$_SESSION['forum_posts_table']."
		WHERE parent_post_id=:post_id
		LIMIT 1
	";
	
	try{
		$rsChild = $mLink->prepare($query);
		$aValues = array(
			':post_id' 	=> $post['post_id']
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsChild->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$child = $rsChild->rowCount();
	
	//Check to see if child variable is set in URL and compare it to Post ID
	if($getChild == $post['post_id']){
		//getChild is set
		$child = '0';
		
		//Loop through Posts and find all posts with parent id equal to getChild
		$query = "
			SELECT p.*, t.topic_creator, t.topic_title, t.cat_id 
			FROM ".$_SESSION['forum_posts_table']." as p 
			INNER JOIN ".$_SESSION['forum_topics_table']." as t ON t.topic_id=p.topic_id 
			WHERE p.topic_id=:topic_id AND p.convo_id=:post_id 
			ORDER BY p.timestamp ASC
		";
		
		try{
			$rsAddPosts = $mLink->prepare($query);
			$aValues = array(
				':topic_id' 	=> $topicID,
				':post_id'		=> $getChild
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsAddPosts->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		//Loop through new result set
		while($addPost = $rsAddPosts->fetch(PDO::FETCH_ASSOC)){
			
			
			//LEVEL 1
			//For each new result add to array
			$posts_array[] = array(
				'post_id' 			=> $addPost['post_id'],
				'parent_post_id' 	=> $addPost['parent_post_id'],
				'topic_creator'		=> $addPost['topic_creator'],
				'member_id'			=> $addPost['post_creator'],
				'response' 			=> $addPost['post_content'],
				'agree'				=> $addPost['agree'],
				'disagree'			=> $addPost['disagree'],
				'anonymous'			=> $addPost['anonymous'],
				'topic_id'			=> $addPost['topic_id'],
				'level'				=> $addPost['level'],
				'timestamp'			=> $addPost['timestamp'],
				'deleted'			=> $addPost['deleted'],
				'deleted_by'		=> $addPost['deleted_by'],
				'orig_post'			=> $addPost['orig_post'],
				'topic_title'		=> $addPost['topic_title'],
				'cat_id'			=> $addPost['cat_id'],
				'section_id'		=> $sectionID,
				'edit_by'			=> $addPost['edit_by'],
				'edit_time'			=> $addPost['edit_time'],
				'edit_post'			=> $addPost['edit_post'],
				'forum_id'			=> $forumID,
				'convo_id'			=> $addPost['convo_id'],
				'goToPost'			=> $getPost,
				'getChild'			=> $getChild,
				'badges'			=> $aFundBadges,
				'listBadges'		=> $aBadges,
				'off_topic'			=> $addPost['off_topic']
			);
			
		}
		
	}else{
		$child = $child;	
	}
		
	//Build function array	
	$posts_array[] = array(
		'post_id' 			=> $post['post_id'],
		'parent_post_id' 	=> $post['parent_post_id'],
		'topic_creator'		=> $post['topic_creator'],
		'member_id'			=> $post['post_creator'],
		'response' 			=> $post['post_content'],
		'agree'				=> $post['agree'],
		'disagree'			=> $post['disagree'],
		'anonymous'			=> $post['anonymous'],
		'topic_id'			=> $post['topic_id'],
		'level'				=> $post['level'],
		'timestamp'			=> $post['timestamp'],
		'deleted'			=> $post['deleted'],
		'deleted_by'		=> $post['deleted_by'],
		'orig_post'			=> $post['orig_post'],
		'topic_title'		=> $post['topic_title'],
		'cat_id'			=> $post['cat_id'],
		'section_id'		=> $sectionID,
		'edit_by'			=> $post['edit_by'],
		'edit_time'			=> $post['edit_time'],
		'edit_post'			=> $post['edit_post'],
		'forum_id'			=> $forumID,
		'has_child'			=> $child,
		'convo_id'			=> $post['post_id'],
		'goToPost'			=> $getPost,
		'getChild'			=> $getChild,
		'badges'			=> $aFundBadges[$post['post_creator']],
		'listBadges'		=> $aBadges,
		'off_topic'			=> $post['off_topic']
	);
			
}
// END LOOP FOR EACH RESPONSE

/*if($_SESSION['admin'] == '1'){
	echo '<pre>';
	print_r($aQueries);
	print_r($aFundBadges);
	echo '</pre>';
}*/
/*//TEMP MANUALLY SET VAR
$showConvo = '0';

if($showConvo == '1'){
	
	$query = "
		SELECT p.*, t.topic_creator, t.topic_title, t.cat_id 
		FROM community_forum_posts as p 
		INNER JOIN community_forum_topics as t ON t.topic_id=p.topic_id 
		WHERE p.topic_id=:topic_id AND p.parent_post_id='1' 
		ORDER BY p.timestamp ASC
	";
	
	try{
		$rsAddPosts = $mLink->prepare($query);
		$aValues = array(
			':topic_id' 	=> $topicID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsAddPosts->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	while($addPost = $rsAddPosts->fetch(PDO::FETCH_ASSOC)){
		
		$posts_array[] = array(
			'post_id' 			=> $addPost['post_id'],
			'parent_post_id' 	=> $addPost['parent_post_id'],
			'topic_creator'		=> $addPost['topic_creator'],
			'member_id'			=> $addPost['post_creator'],
			'response' 			=> $addPost['post_content'],
			'agree'				=> $addPost['agree'],
			'disagree'			=> $addPost['disagree'],
			'anonymous'			=> $addPost['anonymous'],
			'topic_id'			=> $addPost['topic_id'],
			'level'				=> $addPost['level'],
			'timestamp'			=> $addPost['timestamp'],
			'deleted'			=> $addPost['deleted'],
			'deleted_by'		=> $addPost['deleted_by'],
			'orig_post'			=> $addPost['orig_post'],
			'topic_title'		=> $addPost['topic_title'],
			'cat_id'			=> $addPost['cat_id'],
			'section_id'		=> $sectionID,
			'edit_by'			=> $addPost['edit_by'],
			'edit_time'			=> $addPost['edit_time'],
			'edit_post'			=> $addPost['edit_post'],
			'forum_id'			=> $forumID
		);
	}
	
}*/


echo $paginationCtrls;

// Start with the comment that has no parent (NULL parent)
echo printNestedPosts(NULL, $posts_array, $mLink, $showConvo);

echo $paginationCtrls;
//print_r($posts_array);
?>