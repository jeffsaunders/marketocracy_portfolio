<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<link href="css/style.css" rel="stylesheet" type="text/css"/>

</head>

<body>
<?php

    /* 
    Prints the comments recursively. The only down side is that the array is
    potentially being traversed through each recursive call.
    */

    function printNestedComment($parent, $comments_array) {
      foreach ($comments_array as $comment) {
        if($parent == $comment['parent_uid']) {
          /*$html .= '<ul>' . "\n";
          $html .= '<li>' . $comment['message'] . '</li>' . "\n" ;
          $html .= printNestedComment($comment['uid'], $comments_array);
          $html .= '</ul>' . "\n";*/
		  
		  if($parent != NULL){
			$pushRight = 'style="margin-left:80px;"';  
		  }else{
			$pushRight = '';  
		  }
		  
		  $html .= '
          <div class="response-container" '.$pushRight.'>
        	<div class="response-header" style="<?php echo $titleStyle; ?>">
            	<div class="responder" style="float:left;">
            		<?php echo $title; ?>
            	</div><!--responder-->
                <div class="response-date">
                	'.date('D m/d/Y', $reply['timestamp']).' at '.date('h:i A', $reply['timestamp']).'
                </div><!--response-date-->
                <div class="clear"></div>
            </div><!--response-header-->
            
            <div class="response-body">
            	<div class="response">
					<p class="last">'.$comment['message'].'</p>
                    
                </div><!--response-->
                
                <div class="response-actions">
                	
               
					
						<button type="button" class="btn btn-success btn-xs" onClick="agreeDisagree(\'agree\',\''.$reply['uid'].'\', \''.$reply['member_id'].'\',\''.$reply['ticket_id'].'\')">Agree</button>
					
				
						<button type="button" class="btn btn-info btn-xs" onClick="agreeDisagree(\'disagree\',\''.$reply['uid'].'\', \''.$reply['member_id'].'\',\''.$reply['ticket_id'].'\')">
						 Disagree</button>
					
					
                    <button type="button" class="btn btn-warning btn-xs" onClick="showReply(\''.$reply['uid'].'\')" title="Click here to reply to soandso\'s response">Reply</button>
                </div><!--response-actions-->
                
                <div class="clear"></div><!--clear-->
                
                <div id="replyForm-'.$reply['uid'].'" style="display:none;">
                    <!-- BEGIN FORM-->
                    <form action="process/ajax/support-processes.php?process=12" method="post" class="reply-'.$reply['uid'].'" name="reply-'.$reply['uid'].'">
                        <div class="form-body">
                            <div id="replyUserError-'.$reply['uid'].'">
                            </div>
                                
                            <div class="form-group">
                                <textarea name="reply" id="reply-'.$reply['uid'].'" class="form-control" rows="4" style="resize:vertical;"></textarea>
                            </div>
                            <input type="hidden" name="ticket_member_id" value="'.$reply['ticket_member_id'].'" />	
                            <input type="hidden" name="ticket_id" value="'.$reply['ticket_id'].'"  />
                            <input type="hidden" name="uid" value="'.$reply['uid'].'" />
                                    
                            <input type="submit" onClick="postReply(\''.$reply['uid'].'\')" value="Reply" class="btn btn-warning btn-sm">
                            
                       <button type="button" onClick="hideReply(\''.$reply['uid'].'\')" class="btn btn-sm btn-info">Cancel</button>
                        </div><!--form-body-->
                    </form>
                    <!-- END FORM-->
                </div><!--reply-form-->
                 
            </div><!--response-body-->
            
            
            <div class="agree-disagree-area">
                
                <p><small><?php echo $statusMessageA;?> <?php echo $statusMessageD;?></small></p>
               
            </div>
           
            
		</div><!--response-container-->';
		$html .= printNestedComment($comment['uid'], $comments_array);
        }
      }

      return $html;
    }

    $host = '<hostname> or <ip>';
    $user = '<databaseUser>';
    $password = '<databasePassword>';

    $database = '<databaseName>';

    $link = mysql_connect($host, $user, $password)
            or die ('Could not connect: ' . mysql_error());

    mysql_select_db($database) or die ('Could not select database');
	
    // You should make use of prepared statements as here:
    // http://www.databasejournal.com/features/mysql/article.php/3599166 . 
    // I am not using it because I do not have PHP 5 installed 

    // This is a convoluted example to show how to prevent SQL injections
    // Get the parameter passed in through the URL
    $query = sprintf("SELECT * FROM support_community_feedback WHERE ticket_id='1014'");
	
    $raw_result = mysql_query($query);
	
    // Stick this in an array of associative arrays (hash)
    $comments_array = Array();
    while ($row = mysql_fetch_array($raw_result, MYSQL_ASSOC)) {
      $comments_array[] = array(
		  'uid' => $row['uid'],
		  'parent_uid' => $row['parent_uid'],
		  'message' => $row['response']);
    }
	
	print_r($comments_array);
	
	

    // Start with the comment that has no parent (NULL parent)
    echo printNestedComment(NULL, $comments_array);

    // Free the results

    mysql_free_result($raw_result);

    mysql_close($link);

?>
</body>
</html>
