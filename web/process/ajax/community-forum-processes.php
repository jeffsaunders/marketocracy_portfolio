<?php
//Marketocracy Inc.
//Community Forum Processing scripts

//Start Session
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

// Load System Wide Functions
require_once("../../includes/system-functions.php");

//Get Process from URL
$process = $_REQUEST['process'];

$userDisplay = $_SESSION['user_ident'];

if($process == 'send-email'){
	
	$forumID		= $_REQUEST['forum_id'];
	$catID			= $_REQUEST['cat_id'];
	$topicID		= $_REQUEST['topic_id'];
	$tempID			= $_REQUEST['email-template'];
	$aErrors		= array();
	
	//$aErrors[] 		= 'test';
	
	if($tempID == '' || $tempID == 'x'){
		$aErrors[]	= 'Please select a template.';	
	}
	
	$query 			= "
		SELECT *
		FROM ".$_SESSION['email_templates_table']."
		WHERE temp_id=:temp_id
	";
	try{
		$rsGetTemp = $mLink->prepare($query);
		$aValues = array(
			':temp_id'	=> $tempID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetTemp->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$template 	= $rsGetTemp->fetch(PDO::FETCH_ASSOC);
	
	$campType	= $template['camp_type'];
	
	//$aErrors[] = $campType;
	
	
	switch($tempID){
		
		//Forum Email - Stock Research
		case '15':
			
			$stockSymbol	= strtoupper($_REQUEST['stock']);
			
			
			$recipientType	= 'stock-holders';
			
			if(empty($stockSymbol)){
				$aErrors[] = 'Please provide a stock symbol.';	
			}
			
			if($stockSymbol != 'XXXX'){//test stock
				$query = "
					SELECT Name
					FROM stock_feed
					WHERE Symbol=:symbol
				";
				try{
					$rsGetName = $fLink->prepare($query);
					$aValues = array(
						':symbol'	=> $stockSymbol
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsGetName->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				$getName = $rsGetName->fetch(PDO::FETCH_ASSOC);
				
				$stockName = $getName['Name'];
				
				if($stockName == ''){
					$aErrors[] = 'Stock Symbol not found or Stock Name not found.';	
				}
			}
			
		break;
			
	}
	
	$customEmail	= $_REQUEST['customEmail'];
	
	//$aErrors[] = $customEmail;
	
	if($customEmail == '1'){
		
		$emailHeadline	= $_REQUEST['email_headline'];
		$emailBody		= $_REQUEST['email_body'];
		$emailClosing	= $_REQUEST['email_closing'];
		
		if(empty($emailHeadline)){
			$aErrors[] 	= 'Headline can not be left blank.';	
		}
		if(empty($emailBody)){
			$aErrors[] 	= 'Body can not be left blank.';	
		}
		if(empty($emailClosing)){
			$aErrors[] 	= 'Closing can not be left blank.';	
		}
	}
	
	$testEmail		= $_REQUEST['testEmail'];
	
	//$aErrors[]		= $testEmail;
	
	if($testEmail == '1'){
		
		$testEmailAddress 	= $_REQUEST['test_email'];
		$testName			= $_REQUEST['test_name'];
		
		if(empty($testEmail)){
			$aErrors[] 	= 'Please provide test email address.';	
		}
		if(empty($testName)){
			$aErrors[] 	= 'Please provide test name.';	
		}
			
	}
	
	if(empty($aErrors)){
		
		//echo $testEmail;
		
		if($testEmail == '1'){
			
			$aRecipients 	= array();
			
			$aRecipients['test'] = array(
				'nameFirst'			=> $testName,
				'email_address'		=> $testEmailAddress,
				'recepient_vars'	=> array('name_first'=>$testName)
			);
			
			$aRecipients['2'] = array(
				'nameFirst'			=> 'Brandon',
				'email_address'		=> 'brandon.mccarthy@marketocracy.com',
				'recepient_vars'	=> array('name_first'=>'Brandon')
			);
			
		}else{
		
			switch($recipientType){
		
				case 'stock-holders':
					
					#find all members who have held stock
					$query = "
						SELECT DISTINCT(member.member_id), strat.stockSymbol, member.name_first, member.email
						FROM members_fund_stratification_basic AS strat
						INNER JOIN members AS member ON member.member_id=(SELECT member_id FROM members_fund AS mf WHERE mf.fund_id=strat.fund_id)
						WHERE strat.stockSymbol=:stockSymbol
					";
					try{
						$rsGetMembers = $mLink->prepare($query);
						$aValues = array(
							':stockSymbol'	=> $stockSymbol
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsGetMembers->execute($aValues);
					}
					
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
					while($member = $rsGetMembers->fetch(PDO::FETCH_ASSOC)){
						$aRecipients[$member['member_id']] = array(
							'nameFirst'			=> $member['name_first'],
							'email_address'		=> $member['email'],
							'recepient_vars'	=> array('name_first'=>$member['name_first'])
						);
					}
					
				break;
				
			}
			
		}
		
		/*$aRecipients['2'] = array(
			'nameFirst'			=> 'Brandon',
			'email_address'		=> 'mccarthyhe@gmail.com',
			'recepient_vars'	=> array('name_first'=>'Brandon')
		);
		
		$aRecipients['29'] = array(
			'nameFirst'			=> 'Ken',
			'email_address'		=> 'kkam@mac.com',
			'recepient_vars'	=> array('name_first'=>'Ken')
		);*/
		
		//SEND EMAIL
		$aEmailVars = array(
			'stock_symbol'		=> $stockSymbol,
			'stock_name'		=> $stockName,
			'settings_link'		=> 'https://portfolio.marketocracy.com/?page=10-00-00-002&account=1&tab=email',
			'from_name'			=> 'Ken Kam',
			'topic_link'		=> 'https://portfolio.marketocracy.com/?page=04-01-00-004&forum='.$forumID.'&cat='.$catID.'&topic='.$topicID
		);
		$aEmail = array(
			'temp_id'			=> $tempID,
			'camp_type'			=> $campType,
			'recipients'		=> $aRecipients,
			'email_headline'	=> $emailHeadline,
			'email_body'		=> $emailBody,
			'email_closing'		=> $emailClosing
		);
		
		$aErrors[] = sendEmail($mLink, $aEmail, $aEmailVars, true, true);
		
		/*echo '<pre>';
		print_r($aRecipients);
		echo '</pre>';
		*/
		
		/*echo '<div class="note note-danger"><ul>';
		
		print_r($aRecipients);
		
		
		foreach($aErrors as $key=>$error){
			echo '<li>'.$error.'</li>';	
		}
		echo '</ul></div>';*/
		
	}else{
		
		echo '<div class="note note-danger"><ul>';
		foreach($aErrors as $key=>$error){
			echo '<li>'.$error.'</li>';	
		}
		echo '</ul></div>';	
	}
	
}	

if($process == 'load-email-modal'){
	
	$forumSection 	= $_REQUEST['section'];
	$forumID		= $_REQUEST['forumID'];
	$catID			= $_REQUEST['catID'];
	$topicID		= $_REQUEST['topicID'];
		
	switch($forumSection){
		
		case 'topic':	
			?>
			<form action="" method="post" name="send-email-form" class="send-email-form">
				<div class="modal-body">
						<div id="sendEmail-errors"></div>
						<div class="form-body">
							
							<div class="form-group">
								<label class="control-label">Send To:*</label>
								<select name="sent-to" class="form-control">
									<option value="stock-holders">Stock Holders</option>
								</select>
							</div>
						
							<div class="form-group">
                            	<label class="control-label">Select Template:*</label>
                                <select name="email-template" class="form-control" id="select-template">
                                	<option value="x">Choose Template</option>
                                    <option value="15">Forum Email - Stock Research</option>
                                </select>
                            </div>
							
                            <div id="load-template-options"></div>
							
							<input type="hidden" name="topic_id" value="<?php echo $topicID;?>" />
							<input type="hidden" name="cat_id" value="<?php echo $catID; ?>" />
							<input type="hidden" name="forum_id" value="<?php echo $forumID; ?>" />
						</div><!--form-body-->
					
				</div><!--modal-body-->
				
				<div class="modal-footer">
                	<span id="email-processing-btn">
						<input type="submit" value="Send Email" id="submit-email" class="btn btn-info" />
                    </span>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				</div>
			
			</form><!--send email-->
			<?php
		break;
	}#end forum section switch
		
}

if($process == 'load-template-fields'){
	
	$tempID		= $_REQUEST['tempID'];
	
	switch($tempID){
		
		//Forum Email - Stock Research
		case '15':
		
			?>
            <div class="form-group">
                <label class="control-label">Stock Symbol (limit 1)*</label>
                <input type="text" class="form-control" name="stock" id="stock" />
            </div>
            
            <div class="form-group">
            	<label class="control-label"><input type="checkbox" name="customEmail" value="1" onChange="toggleID('show-template-fields');"> Customize Template</label>
            </div>
            
            
            
            <input type="hidden" name="temp_id" value="15" />
            <?php
		
		break;
			
	}
	
	$query = "
		SELECT *
		FROM ".$_SESSION['email_templates_table']."
		WHERE temp_id=:temp_id
	";
	try{
		$rsTemp = $mLink->prepare($query);
		$aValues = array(
			':temp_id'	=> $tempID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsTemp->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$template	= $rsTemp->fetch(PDO::FETCH_ASSOC);
	
	?>
    <div id="show-template-fields" style="display:none;">
        <div class="form-group">
            <label class="control-label">Headline*</label>
            <input type="text" class="form-control" name="email_headline" value="<?php echo $template['temp_headline'];?>" />
        </div>
        <div class="form-group">
            <label class="control-label">Body*</label>
            <textarea type="text" class="form-control" name="email_body" rows="10"><?php echo $template['temp_body'];?></textarea>
        </div>
        
        <div class="form-group">
            <label class="control-label">Closing*</label>
            <textarea class="form-control" name="email_closing"><?php echo $template['temp_closing'];?></textarea>
        </div>
        
        <a href="javascript:void(0);" onclick="toggleID('format-help');">Toggle Formatting Help</a>
        <div class="note note-info" id="format-help" style="display:none;">
        	<h3>Formatting Help</h3>
            <table width="100" cellpadding="5" cellspacing="5"  class="table">
            	<thead>
                	<tr>
                		<td><strong>Code</strong></td>
                    	<td><strong>Result</strong></td>
                	</tr>
            	</thead>
                <tbody>
                	<tr>
                    	<td>&lt;strong&gt;Bold Text&lt;/strong&gt;</td>
                        <td><strong>Bold Text</strong><td>
                    </tr>
                    <tr>
                    	<td>&lt;em&gt;Italic Text&lt;/em&gt;</td>
                        <td><em>Italic Text</em><td>
                    </tr>
                    <tr>
                    	<td>&lt;a href="FULL_URL" target="_blank"&gt;Link&lt;/a&gt;</td>
                        <td><a href="FULL_URL" target="_blank">Link</a><td>
                    </tr>
                </tbody>
            </table>
            
            
            <h3>Email Tags</h3>
            <table width="100" cellpadding="5" cellspacing="5"  class="table">
            	<thead>
                	<tr>
                		<td><strong>Tag</strong></td>
                    	<td><strong>Result</strong></td>
                	</tr>
            	</thead>
                <tbody>
                	<tr>
                    	<td>[NAME_FIRST]</td>
                        <td>Brandon<td>
                    </tr>
                    <tr>
                    	<td>[STOCK_NAME]</td>
                        <td>Apple Inc.<td>
                    </tr>
                    <tr>
                    	<td>[STOCK_SYMBOL]</td>
                        <td>AAPL<td>
                    </tr>
                    
                    <tr>
                    	<td>[TOPIC_LINK]</td>
                        <td>Inserts url path of topic link, must be used with an &lt;a&gt; tag.</td>
                    </tr>
                    <tr>
                    	<td>[CLICK_CODE]</td>
                        <td>Code to track links to marketocracy, must be put on end of URL with the use of an &lt;a&gt; tag.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div><!--show-template-fields-->
    
    <div class="form-group">
        <label class="control-label"><input type="checkbox" name="testEmail" value="1" onChange="toggleID('test-email-fields');"> Send Test Email</label>
        <br /><small>If checked, email will only be sent to the email address provided.</small>
    </div>
    
    <div id="test-email-fields" style="display:none;">
    	
        <div class="form-group">
        	<label class="control-label"> Test Email Address</label>
            <input type="text" name="test_email" class="form-control" />
        </div>
        
        <div class="form-group">
        	<label class="control-label"> Test Name</label>
            <input type="text" name="test_name" class="form-control" />
        </div>
        
    </div>
    <?php
		
}

//+---------------------------------------------------------------------------------------------------------+
//|									CREATE NEW TOPIC - PROCESS 1											|
//+---------------------------------------------------------------------------------------------------------+
if($process == "1"){
	
	//Set Vars
	$forumID		= $_REQUEST['forum_id'];
	$catID 			= $_REQUEST['cat_id'];
	$topicTitle		= $_REQUEST['topic_title'];
	$postContent	= $_REQUEST['topic_post'];
	
	//Error Check
	$error_list = array();
	if(empty($catID)){
		$error_list[] = 'No Category ID Found';
	}
	
	if(empty($catID)){
		$error_list[] = 'No Forum ID Found';	
	}
	
	if(empty($topicTitle)){
		$error_list[] = 'Please provide a Topic Title';	
	}
	
	if(empty($postContent)){
		$error_list[] = 'Please provide a Post';
	}
	// If no errors post to Database
	if(empty($error_list)) {
	// No Errors
		
		
		//Insert Topic in to topic table
		$query = "
			INSERT INTO ".$_SESSION['forum_topics_table']." (
				forum_id,
				cat_id,
				topic_title,
				topic_creator,
				topic_reply_date,
				timestamp
			) VALUE (
				:forum_id,
				:cat_id,
				:topic_title,
				:topic_creator,
				UNIX_TIMESTAMP(),
				UNIX_TIMESTAMP()
			)
		";
		
		try{
			$rsAddTopic = $mLink->prepare($query);
			$aValues = array(
				':forum_id'			=> $forumID,
				':cat_id'			=> $catID,
				':topic_title'		=> $topicTitle,
				':topic_creator'	=> $_SESSION['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsAddTopic->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		//END DB INSERT
		
		// Get Topic ID from inserted topic
		$topicID = $mLink->lastInsertId();
		
		// Insert Original Post in to Post table
		$query = "
			INSERT INTO ".$_SESSION['forum_posts_table']." (
				cat_id,
				topic_id,
				post_creator,
				post_content,
				orig_post,
				timestamp
			) VALUE (
				:cat_id,
				:topic_id,
				:post_creator,
				:post_content,
				'1',
				UNIX_TIMESTAMP()
			)
		";
		
		try{
			$rsAddPost = $mLink->prepare($query);
			$aValues = array(
				':cat_id'			=> $catID,
				':topic_id'			=> $topicID,
				':post_creator'		=> $_SESSION['member_id'],
				':post_content'		=> $postContent
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsAddPost->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		//END DB INSERT
		
		echo $error;
		
		$event = 'Forum Category Update';
		$detail = 'Member has created new Topic: '.$topicTitle.'.';
		addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
		
	}else{
	// Has Errors	
		?>
		<div class="alert alert-block alert-danger fade in">
			<button type="button" class="close" data-dismiss="alert"></button>
			<h4><strong>Please Provide Required Fields</strong></h4>
			<ul>
				<?php
				foreach($error_list as $error){
					echo '<li>'.$error.'</li>';	
				}
				?>
			</ul>
			
		</div>
		<?php
	}
		
}




//+---------------------------------------------------------------------------------------------------------+
//|										AJAX LOAD TOPICS - PROCESS 2										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "2"){
	
	$catID = $_REQUEST['cat'];
	$forumID = $_REQUEST['forum'];
	$adminAccess = $_REQUEST['adminAccess'];
	$active = $_REQUEST['active'];
	
	if($active == '1'){						
		$query = "
			SELECT *
			FROM ".$_SESSION['forum_topics_table']."
			WHERE forum_id=:forum_id AND cat_id=:cat_id AND active='1'
			ORDER BY topic_reply_date DESC
		";
	}elseif($active == '0'){
		$query = "
			SELECT *
			FROM ".$_SESSION['forum_topics_table']."
			WHERE forum_id=:forum_id AND cat_id=:cat_id AND active='0'
			ORDER BY topic_reply_date DESC
		";	
	}
	
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
		$lastPosted			= get_member($mLink, $topic['topic_last_user'], 'full name');
		$topicDate			= $topic['topic_reply_date'];
		$topicViews			= $topic['topic_views'];
		$topicCreationDate 	= $topic['timestamp'];
		$postID 			= $topic['last_post_id'];
		$topicCreator		= $topic['topic_creator'];
		$topicClosed		= $topic['closed'];
		$forbesUC			= $topic['forbes_uc'];
		$forbesLink			= $topic['forbes_article'];
		
		
		
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
		
		//START Lock Code
		if($topicClosed != '0'){
			$showLock 	= '<i class="icon-lock"></i>';
			$lockLink	= '<li class="btn-li"><button class="btn btn-warning btn-xs btn-full" type="button" onclick="lockLevel(\'topic\', \''.$topicID.'\',\'unlock\');"><i class="icon-unlock"></i> Unlock Topic</button></li>';	
		}else{
			$showLock 	= '';
			$lockLink 	= '<li class="btn-li"><button class="btn btn-warning btn-xs btn-full" type="button" onclick="lockLevel(\'topic\', \''.$topicID.'\',\'lock\');"><i class="icon-lock"></i> Lock Topic</button></li>';
		}
		//End Lock Code
		
		//START Forbes Link Code
		if($forbesLink != ''){
			//Show Forbes Link
			$showForbes = '<br /><small><a href="'.$forbesLink.'" target="_blank"><i class="icon-book"></i> Read On Forbes</a></small>';
			
			$adminForbesLink = '<li class="btn-li" style="margin-bottom:5px;"><button class="btn btn-warning btn-xs btn-full" type="button" onclick="forbesLink(\''.$topicID.'\',\''.$forbesUC.'\');"><i class="icon-book"></i> Edit Forbes Link</button></li>';
			
			$adminForbesUC = '';
		}else{
			if($forbesUC != '0'){
				//Show Under Consideration
				$showForbes = '<br /><small><span title="This topic is under consideration for Forbes Publishing." style="cursor: help;"><i class="icon-upload-alt"></i> Under Consideration for Forbes</span></small>';
				
				$adminForbesLink = '<li class="btn-li" style="margin-bottom:5px;"><button class="btn btn-success btn-xs btn-full" type="button" onclick="forbesLink(\''.$topicID.'\',\''.$forbesUC.'\');"><i class="icon-book"></i> Add Forbes Link</button></li>';
				
				$adminForbesUC = '<li class="btn-li" id="forbes-uc-'.$topicID.'"><button class="btn btn-warning btn-xs btn-full" type="button" onclick="forbesUC(\''.$topicID.'\',\'0\');"><i class="icon-upload-alt"></i> Remove Under Consideration</button></li>';
			}else{
				//Show nothing
				$showForbes = '';
				
				$adminForbesLink = '<li class="btn-li" style="margin-bottom:5px;"><button class="btn btn-success btn-xs btn-full" type="button" onclick="forbesLink(\''.$topicID.'\',\''.$forbesUC.'\');"><i class="icon-book"></i> Add Forbes Link</button></li>';
				
				$adminForbesUC = '<li class="btn-li" id="forbes-uc-'.$topicID.'"><button class="btn btn-success btn-xs btn-full" type="button" onclick="forbesUC(\''.$topicID.'\',\'1\');"><i class="icon-upload-alt"></i> Mark Under Consideration</button></li>';
			}
		}
		
		/*if($forbesUC != '0'){
			//Show Remove Under Consideration
			
			$adminForbesUC = '<li class="btn-li"><button class="btn btn-warning btn-xs btn-full" type="button" onclick="forbesUC(\''.$topicID.'\',\'0\');"><i class="icon-upload-alt"></i> Remove Under Consideration</button></li>';
		}else{
			//Show Mark Under Consideration
			
			$adminForbesUC = '<li class="btn-li"><button class="btn btn-success btn-xs btn-full" type="button" onclick="forbesUC(\''.$topicID.'\',\'1\');"><i class="icon-upload-alt"></i> Mark Under Consideration</button></li>';
		}*/
		
		
		//END Forbes Link Code
		
		//Create Admin actions
		if($adminAccess == 1){
			
			$adminForbesForm = '
				
				<span id="edit-forbes-link-'.$topicID.'" class="hide">
					<br /><br />
                	<span id="show-edit-forbes-error-'.$topicID.'"></span>
					<form action="process/ajax/community-forum-processes.php?process=2-3" method="post" class="edit-forbes-form" id="edit-forbes-form-'.$topicID.'">
					<input type="text" name="new-forbes-link" class="form-control" placeholder="Add Link" style="width:80% !important;" value="'.$forbesLink.'" />
					<input type="hidden" name="edit-topic-id" value="'.$topicID.'" />
					<input type="hidden" name="old-forbes-link" id="reset-old-link-'.$topicID.'" value="'.$forbesLink.'" />
					<input type="submit" value="update" class="btn btn-xs btn-default"/>
					&nbsp;<a class="btn btn-xs btn-warning" href="javascript:void(0);" onclick="cancelEditForbes(\''.$topicID.'\');">Cancel</a>
					</form>
                </span>
			';
			
			if($active == '1'){
				$adminButton = '
					<div class="btn-group" style="float:left;margin-right:10px;">
						<button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" ><i class="icon-cog"></i></button>
						
						<ul class="dropdown-menu" role="menu">
						   <li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-danger btn-xs btn-full" onclick="activate(\'false\',\'topic\',\''.$topicID.'\');"><i class="icon-remove"></i> Remove Topic</button></li>
						   <li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-info btn-xs btn-full" onclick="editTopicTitle(\''.$topicID.'\');"><i class="icon-pencil"></i> Edit Topic Title</button></li>
						   '.$lockLink.'
						   <li class="divider"></li>
						   <li class="btn-li" style="margin-bottom:5px;"><button onclick="sendEmail(\'topic\',\''.$forumID.'\',\''.$catID.'\',\''.$topicID.'\');" data-toggle="modal" data-target="#send-email" type="button" class="btn btn-info btn-xs btn-full"><i class="icon-envelope"></i> Send Email</button></li>
						   <li class="divider"></li>
						   '.$adminForbesLink.'
						   '.$adminForbesUC.'
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
		//End Create admin actions
		
		
		?>
		<tr>
			<td>
				<?php echo $adminButton;?>
                <span id="edit-topic-<?php echo $topicID;?>"></span><a href="?page=04-01-00-004&forum=<?php echo $forumID;?>&cat=<?php echo $catID; ?>&topic=<?php echo $topicID;?>" style="display:block;float:left;" id="edit-topic-link-<?php echo $topicID;?>"><strong id="edit-topic-title-<?php echo $topicID;?>"><?php echo $topicTitle;?></strong> <?php echo $showLock;?></a>
                <span id="forbes-area-<?php echo $topicID;?>"><?php echo $showForbes;?></span>
                <?php echo $adminForbesForm;?>
            </td>
            <td><p style="margin-bottom:0px;"><span style="padding-top:7px;"><a href="?page=04-00-00-001&member=<?php echo $topicCreator;?>" target="_blank"><strong><?php echo get_member($mLink, $topicCreator, $userDisplay);?></strong></a></span><br /><?php print_r(get_badges($mLink, $topicCreator, 'short_list', 'none', '20'));?></p>  </td>
			<td><?php echo $lastPost;?></td>
			<td><?php echo get_topic_replies($mLink, $topicID); ?></td>
			<td><?php echo $topicViews;?></td>
			<td style="display:none;"><?php echo $topicCreationDate; ?></td>
		</tr>
		<?php
	}
	
}

//+---------------------------------------------------------------------------------------------------------+
//|										Edit TOPIC TITLE - PROCESS 2-1										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "2-1"){
	
	$topicID	= $_REQUEST['topic_id'];
	$topicTitle	= $_REQUEST['topic_title'];
	
	?>
    <form action="process/ajax/community-forum-processes.php?process=2-2" method="post" class="change-topic-form" id="change-topic-form-<?php echo $topicID;?>">
    <input type="text" name="new-topic-title" class="form-control" style="width:80% !important;" value="<?php echo $topicTitle;?>" />
    <input type="hidden" name="edit-topic-id" value="<?php echo $topicID;?>" />
    <input type="hidden" name="old-topic-title" value="<?php echo $topicTitle;?>" />
    <input type="submit" value="update" class="btn btn-xs btn-default"/>
    &nbsp;<a class="btn btn-xs btn-warning" href="javascript:void(0);" onclick="cancelEditTopic('<?php echo $topicID;?>');">Cancel</a>
    </form>
    <?php

}

//+---------------------------------------------------------------------------------------------------------+
//|										Edit TOPIC TITLE - PROCESS 2-2										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "2-2"){
	
	$topicID 	= $_REQUEST['edit-topic-id'];
	$newTitle	= $_REQUEST['new-topic-title'];
	$oldTitle	= $_REQUEST['old-topic-title'];
	
	if($newTitle != $oldTitle){
		//echo 'not equal to eachother';
		if($newTitle != ''){
			
			$query = "
				UPDATE ".$_SESSION['forum_topics_table']."
				SET topic_title=:topic_title
				WHERE topic_id=:topic_id
			";
			
			try{
				$rsGetPosts = $mLink->prepare($query);
				$aValues = array(
					':topic_id' 	=> $topicID,
					':topic_title'	=> $newTitle
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetPosts->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
		}else{
			$newTitle = $oldTitle;	
		}
			
	}else{
		$newTitle = $oldTitle;
		//echo 'equal';	
	}
	
	echo $newTitle;
	
}

//+---------------------------------------------------------------------------------------------------------+
//|										Edit Forbes Link - PROCESS 2-3										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "2-3"){
	
	$newForbesLink 	= $_REQUEST['new-forbes-link'];
	$forbesUC		= $_REQUEST['forbesUC'];
	$oldForbesLink	= $_REQUEST['old-forbes-link'];
	$topicID		= $_REQUEST['edit-topic-id'];
	
	
		
	if($newForbesLink != ''){
		
		$aURL = parse_url($newForbesLink);
		
		if(in_array("http", $aURL)){
			$newForbesLink = $newForbesLink;	
		}else{
			$newForbesLink = 'http://'.$newForbesLink;	
		}
		
		$return = '.<br /><small><a href="'.$newForbesLink.'" target="_blank"><i class="icon-book"></i> Read On Forbes</a></small>';
		
		$detail = 'Member has removed Forbes Link from Topic ID: '.$topicID.'';	
	}else{
		if($forbesUC != '0'){
			$return = '<br /><small><span title="This topic is under consideration for Forbes Publishing."><i class="icon-upload-alt"></i> Under Considerationfor Forbes</span></small>';	
		}else{
			$return = '';	
		}
		
		$detail = 'Member has added Forbes Link to Topic ID: '.$topicID.'';
	}
	
	//Update DB
	if($oldForbesLink != $newForbesLink){
		$query = "
			UPDATE ".$_SESSION['forum_topics_table']."
			SET forbes_article=:forbesLink
			WHERE topic_id=:topic_id
		";
		try{
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':topic_id' 	=> $topicID,
				':forbesLink'	=> $newForbesLink
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$event = 'Forum Topic Update';
		addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
	}
	
	//echo $preparedQuery;
	echo $return;
	echo $error;
	
}

//+---------------------------------------------------------------------------------------------------------+
//|								Set/Unset "Under Consideration" - PROCESS 2-4								|
//+---------------------------------------------------------------------------------------------------------+
if($process == "2-4"){
	
	$set		= $_REQUEST['set'];
	$topicID	= $_REQUEST['topic_id'];
	
	if($set == '1'){
		$showForbes = '<br /><small><span title="This topic is under consideration for Forbes Publishing."><i class="icon-upload-alt"></i> Under Consideration for Forbes</span></small>';	
		$detail = 'Topic: '.$topicID.' has been marked "Under Consideration".';
	}elseif($set == '0'){
		$showForbes = '';
		$detail = 'Topic: '.$topicID.' has been unmarked as "Under Consideration".';
	}
	
	if($set != ''){
		//Update DB
		$query = "
			UPDATE ".$_SESSION['forum_topics_table']."
			SET forbes_uc=:forbesUC
			WHERE topic_id=:topic_id
		";
		try{
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':topic_id' 	=> $topicID,
				':forbesUC'		=> $set
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$event = 'Forum Topic Update';
		addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
	}
	
	echo $showForbes;
}
//+---------------------------------------------------------------------------------------------------------+
//|										AJAX LOAD POSTS - PROCESS 3											|
//+---------------------------------------------------------------------------------------------------------+
if($process == "3"){
	
	$topicID 	= $_REQUEST['topic'];
	$pageID 	= $_REQUEST['page'];
	$sectionID	= substr($pageID, 0, 5);
	$forumID 	= $_REQUEST['forum'];
	$getChild 	= $_REQUEST['child'];
	$getPost 	= $_REQUEST['post'];
	$catID 		= $_REQUEST['cat'];
	//$pn = $_REQUEST['pn'];
	
	
	
	include('../../includes/pages/includes/community-forum-view-posts.php'); //replace include with actual code upon completion
	
}

//+---------------------------------------------------------------------------------------------------------+
//|										CREATE NEW POST - PROCESS 4											|
//+---------------------------------------------------------------------------------------------------------+
if($process == "4"){
	
	//Set Vars
	$forumID		= $_REQUEST['forum_id'];
	$topicID		= $_REQUEST['topic_id'];
	$catID 			= $_REQUEST['cat_id'];
	$postContent	= $_REQUEST['post'];
	
	//Error Check
	$error_list = array();
	if(empty($catID)){
		$error_list[] = 'No Category ID Found';
	}
	
	if(empty($topicID)){
		$error_list[] = 'No Topic ID Found';	
	}
	
	if(empty($postContent)){
		$error_list[] = 'Please provide a Post';
	}
	
	// If no errors post to Database
	if(empty($error_list)) {
	// No Errors
		
		// Insert Post in to Forum Post table
		$query = "
			INSERT INTO ".$_SESSION['forum_posts_table']." (
				cat_id,
				topic_id,
				post_creator,
				post_content,
				timestamp
			) VALUE (
				:cat_id,
				:topic_id,
				:post_creator,
				:post_content,
				UNIX_TIMESTAMP()
			)
		";
		
		try{
			$rsAddPost = $mLink->prepare($query);
			$aValues = array(
				':cat_id'			=> $catID,
				':topic_id'			=> $topicID,
				':post_creator'		=> $_SESSION['member_id'],
				':post_content'		=> $postContent
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsAddPost->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		//END DB INSERT
		
		// Get Post ID from inserted post
		$postID = $mLink->lastInsertId();
		
		
		
		//START NOTIFICATION
		$query = "
			SELECT post_creator, post_id
			FROM ".$_SESSION['forum_posts_table']."
			WHERE topic_id=:topic_id
			GROUP BY post_creator
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
		
		while($post = $rsGetPosts->fetch(PDO::FETCH_ASSOC)) {
			
			
			$notificationID = "04-003";
			$postCreator	= $post['post_creator'];
			
			//Custom notification
			$notification	= "".get_member($mLink, $_SESSION['member_id'], "username")." has replied to a topic you posted on. Click Here to view.";
			$link			= get_post_url($mLink, $postID);
			
			if($_SESSION['member_id'] != $postCreator){
				add_notification($mLink, $notificationID, $postCreator, $notification, $link);
			}
		}
		//END NOTIFICATION
		
		$topicTitle = get_topic($mLink, $topicID, 'title');
		$event = 'Forum Topic Update';
		$detail = 'Member has replied to Topic: '.$topicTitle.'.';
		addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
		
		//echo $error;
		
		//Send Admin Notification Email
		if($catID == '62'){
			if($_SESSION['member_id'] != '29'){
				$aEmailVars = array(
					'first_name'		=> 'Ken',
					'change_link'		=> $_SESSION['site_root'].'?postid='.$postID,
					'member_first_name'	=> $_SESSION['member_id'],
					'post_link'			=> $_SESSION['site_root'].'?postid='.$postID
				);
				$aEmail = array(
					'email_id'		=> '8',
					'to'			=> 'ken@marketocracy.com',
					'vars'			=> $aEmailVars
				);
				include('../../includes/email/system-email.php');
			}
		}
		//END Send Admin Notification Email
		
	}else{
	// Has Errors	
		?>
		<div class="alert alert-block alert-danger fade in">
			<button type="button" class="close" data-dismiss="alert"></button>
			<h4><strong>Please Provide Required Fields</strong></h4>
			<ul>
				<?php
				foreach($error_list as $error){
					echo '<li>'.$error.'</li>';	
				}
				?>
			</ul>
			
		</div>
		<?php
	}
		
}


//+---------------------------------------------------------------------------------------------------------+
//|										FORUM POST REPLY - PROCESS 5										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "5"){

	//Set Vars
	$topicID 		= $_REQUEST['topic_id'];
	$catID 			= $_REQUEST['cat_id'];
	$postID 		= $_REQUEST['post_id'];
	$postContent	= $_REQUEST['post-reply'];
	$level 			= $_REQUEST['level'];
	$forumID 		= $_REQUEST['forum_id'];
	$convoID		= $_REQUEST['convo_id'];
	$firstPostID	= $_REQUEST['post_id'];
	
	//Increment Level by 1(one)
	$level = $level + 1;
	
	//START FROM VALIDATION
	$error_list = array();
	
	
	//echo ''.$topicID.'|'.$catID.'|'.$postID.'|'.$level.'|'.$_SESSION['member_id'].'|'.$postContent.'';
		
	if(empty($topicID)) {
		$error_list[] = "Unable to pass Topic ID, please contact administrator.";
	}
	
	if(empty($catID)) {
		$error_list[] = "Unable to pass Category ID, please contact administrator.";
	}
	
	
	if(empty($postID)) {
		$error_list[] = "Unable to pass Post ID, please contact administrator.";
	}
	
	
	if(empty($level)) {
		$error_list[] = "Unable to pass post level, please contact administrator.";
	}
	
	
	if(empty($postContent)) {
		$error_list[] = "Please provide post.";
	}
	
	if(empty($error_list)) {
		//If no errors, insert into database
		
		//ADD POST TO DB
		$query = "
			INSERT INTO ".$_SESSION['forum_posts_table']." (
				post_creator,
				topic_id,
				cat_id,
				post_content,
				parent_post_id,
				level,
				convo_id,
				timestamp
			) VALUE (
				:post_creator,
				:topic_id,
				:cat_id,
				:post_content,
				:parent_post_id,
				:level,
				:convo_id,
				UNIX_TIMESTAMP()
			)
		";
		
		try{
			$rsAddPost = $mLink->prepare($query);
			$aValues = array(
				':post_creator'		=> $_SESSION['member_id'],
				':topic_id'			=> $topicID,
				':cat_id'			=> $catID,
				':post_content'		=> $postContent,
				':parent_post_id'	=> $postID,
				':level'			=> $level,
				':convo_id'			=> $convoID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsAddPost->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		//END DB INSERT
		
		// Get Post ID from inserted post
		$postID = $mLink->lastInsertId();
		
		//START NOTIFICATION
		$query = "
			SELECT post_creator
			FROM ".$_SESSION['forum_posts_table']."
			WHERE post_id=:post_id
		";
		
		try{
			$rsGetPosts = $mLink->prepare($query);
			$aValues = array(
				':post_id' 	=> $firstPostID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetPosts->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$post = $rsGetPosts->fetch(PDO::FETCH_ASSOC);
			
		$notificationID = "04-004";
		$postCreator	= $post['post_creator'];
		
		//Custom notification
		$notification	= "".get_member($mLink, $_SESSION['member_id'], "username")." has replied to your post. Click Here to view.";
		$link			= get_post_url($mLink, $postID);
		
		add_notification($mLink, $notificationID, $postCreator, $notification, $link);			
		//END NOTIFICATION
		
		$topicTitle = get_topic($mLink, $topicID, 'title');
		$event = 'Forum Post Update';
		$detail = 'Member has replied to a post under the Topic: '.$topicTitle.'. Post ID: '.$postID.'';
		addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
		//echo $error;
		//echo ''.$topicID.'|'.$ticketMemberID.'|'.$postID.'|'.$reply.'';
		
	}else{
		?>
		<div class="alert alert-block alert-danger fade in">
			<button type="button" class="close" data-dismiss="alert"></button>
			<h4><strong>Please Provide Required Fields</strong></h4>
			<ul>
				<?php
                foreach($error_list as $error){
                    echo '<li>'.$error.'</li>';	
                }
                ?>
            </ul>
            
		</div>
        <?php
	}
}

//+---------------------------------------------------------------------------------------------------------+
//|									FORUM AGREE/DISAGREE - PROCESS 6										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "6"){
	//Get Vars from click function
	$postID		= $_REQUEST['uid'];
	$choice		= $_REQUEST['choice'];
	$member		= $_REQUEST['member'];
	$topicID	= $_REQUEST['topic'];
	
	// Grab existing data from DB
	$query = "
		SELECT agree, disagree
		FROM ".$_SESSION['forum_posts_table']." 
		WHERE post_id=:post_id
		ORDER BY timestamp
	";
	
	try{
		$rsResponse = $mLink->prepare($query);
		$aValues = array(
			':post_id' 	=> $postID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsResponse->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$reply = $rsResponse->fetch(PDO::FETCH_ASSOC);
		
	//Get Columns From DB
	$agreeCol = $reply['agree'];
	$disagreeCol = $reply['disagree'];
		
	// Branch based on passed choice
	if($choice == "agree"){
		
		$choiceOption = "Agreed";
		
		// Explode db string into single dimensional array
		$arrayA = explode('|', $agreeCol);
		$arrayD = explode('|', $disagreeCol);
		//print_r($arrayA);
		//print_r($arrayD);
		// Loop through each key and explode its value
		foreach($arrayA as $key=>$value){
			// Eplode array into multidimensional array
			$arrayA[$key] = explode('-', $value);
		}
		
		foreach($arrayD as $key=>$value){
			$arrayD[$key] = explode('-', $value);	
		}
		
		// Search array for users member id (if you print $found, it will display an array with the key and value that was found)
		$foundA = ($arrayA[searchForId($_SESSION['member_id'], $arrayA)]);
		$foundD = ($arrayD[searchForId($_SESSION['member_id'], $arrayD)]);
		
		if($foundA != ""){
			//Member Id in column Agree
			
			// Set var for the array key
			$arrayKey = searchForId($_SESSION['member_id'], $arrayA);
			
			// Remove the key from the existing array
			unset($arrayA[$arrayKey]);
			
			// Now step out and implode second level of multidimensional array
			foreach($arrayA as $key=>$value){
				$arrayA[$key] = implode('-', $value);	
			}
			
			// Now implode the array to be able to store in data base
			$newArrayA = implode('|', $arrayA);
			
			// Echo message for pop up notification: controled by javascript includes/scripts/support-scripts.php
			echo "You no longer agree with the response.";
			
		}else{
			//Member Id not in column Agree
			
			//Assign date to var for building timestamp
			$date = new DateTime();
			
			// Build array value to add
			$addArray = array($_SESSION['member_id'], $date->getTimestamp());
			
			// Check to see if array is empty
			if($arrayA[0][0] == ""){
				// Array is empty
				
				// If array is empty override existing array with new array value
				$arrayA = $addArray;
				
				// Implode array for DB storage
				$newArrayA = implode('-', $arrayA);	
				
			}else{
				// Array is not empty
				
				// If array is not empty add new array value to the end of the existing array
				array_push($arrayA, $addArray);
				
				// Now step out and implode second level of multidimensional array
				foreach($arrayA as $key=>$value){
					$arrayA[$key] = implode('-', $value);	
				}
				
				// Implode array for DB storage
				$newArrayA = implode('|', $arrayA);
				
			}
			
			// Echo message for popup notification
			echo "You agree to the response.";
			
			/*//START NOTIFICATION	
			$notificationID	= "04-001";
			$memberID2		= $member;
			//Custom notification
			$notification	= "".get_member($mLink, $_SESSION['member_id'], "full name")." has agreed to your corporate action response.";
			$link			= "?page=04-02-cc-003&ticket=".$topicID."";
			
			add_notification($mLink, $notificationID, $memberID2, $notification, $link);
			//END NOTIFICATION*/
			
		}
		
		if($foundD != ""){
			//Member Id in column Disagree
			
			// Set var for the array key
			$arrayKey = searchForId($_SESSION['member_id'], $arrayD);
			
			// Remove the key from the existing array
			unset($arrayD[$arrayKey]);
			
			// Now step out and implode second level of multidimensional array
			foreach($arrayD as $key=>$value){
				$arrayD[$key] = implode('-', $value);	
			}
			
			// Now implode the array to be able to store in data base
			$newArrayD = implode('|', $arrayD);
		}else{
			$newArrayD = $reply['disagree'];
		}
		
		//Add new array to Database
		$query = "
			UPDATE ".$_SESSION['forum_posts_table']."
			SET agree=:agree, disagree=:disagree
			WHERE post_id=:post_id
		";
		
		try{
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':post_id'	=> $postID,
				':agree'	=> $newArrayA,
				':disagree'	=> $newArrayD
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		//END DB Update
		
		//echo $newArray;
		
	}elseif($choice == "disagree"){
		
		$choiceOption = "Disagreed";
		
		// Explode db string into single dimensional array
		$arrayA = explode('|', $agreeCol);
		$arrayD = explode('|', $disagreeCol);
		
		// Loop through each key and explode its value
		foreach($arrayA as $key=>$value){
			// Eplode array into multidimensional array
			$arrayA[$key] = explode('-', $value);
		}
		
		foreach($arrayD as $key=>$value){
			$arrayD[$key] = explode('-', $value);	
		}
		
		// Search array for users member id (if you print $found, it will display an array with the key and value that was found)
		$foundA = ($arrayA[searchForId($_SESSION['member_id'], $arrayA)]);
		$foundD = ($arrayD[searchForId($_SESSION['member_id'], $arrayD)]);
		
		if($foundD != ""){
			//Member Id in column Disagree
			
			// Set var for the array key
			$arrayKey = searchForId($_SESSION['member_id'], $arrayD);
			
			// Remove the key from the existing array
			unset($arrayD[$arrayKey]);
			
			// Now step out and implode second level of multidimensional array
			foreach($arrayD as $key=>$value){
				$arrayD[$key] = implode('-', $value);	
			}
			
			// Now implode the array to be able to store in data base
			$newArrayD = implode('|', $arrayD);
			
			echo "You no longer disagree with the response.";
			
			
		}else{
			//Member Id not in column Disagree
			
			//Assign date to var for building timestamp
			$date = new DateTime();
			
			// Build array value to add
			$addArray = array($_SESSION['member_id'], $date->getTimestamp());
			
			// Check to see if array is empty
			if($arrayD[0][0] == ""){
				// Array is empty
				
				// If array is empty override existing array with new array value
				$arrayD = $addArray;
				
				// Implode array for DB storage
				$newArrayD = implode('-', $arrayD);	
				
				echo "You disagree with the response.";
				
			}else{
				// Array is not empty
				
				// If array is not empty add new array value to the end of the existing array
				array_push($arrayD, $addArray);
				
				// Now step out and implode second level of multidimensional array
				foreach($arrayD as $key=>$value){
					$arrayD[$key] = implode('-', $value);	
				}
				
				// Implode array for DB storage
				$newArrayD = implode('|', $arrayD);
				
				
			}
			
			// Echo message for popup notification
			echo "You disagree with the response.";
			
			/*//START NOTIFICATION	
			$notificationID	= "04-002";
			$memberID2		= $member;
			//Custom notification
			$notification	= "".get_member($mLink, $_SESSION['member_id'], "full name")." disagreed to your corporate action response.";
			$link			= "?page=04-02-cc-003&ticket=".$topicID."";
			
			add_notification($mLink, $notificationID, $memberID2, $notification, $link);
			//END NOTIFICATION*/
			
		}
		
		if($foundA != ""){
			//Member Id in column Agree
			
			// Set var for the array key
			$arrayKey = searchForId($_SESSION['member_id'], $arrayA);
			
			// Remove the key from the existing array
			unset($arrayA[$arrayKey]);
			
			// Now step out and implode second level of multidimensional array
			foreach($arrayA as $key=>$value){
				$arrayA[$key] = implode('-', $value);	
			}
			
			// Now implode the array to be able to store in data base
			$newArrayA = implode('|', $arrayA);
		}else{
			// Member Id not found in column Agree
			$newArrayA = $reply['agree'];
		}
		
		//Add new array to Database
		$query = "
			UPDATE ".$_SESSION['forum_posts_table']."
			SET agree=:agree, disagree=:disagree
			WHERE post_id=:post_id
		";
		
		try{
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':post_id'		=> $postID,
				':agree'	=> $newArrayA,
				':disagree'	=> $newArrayD
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		//END DB Update
		
		//echo $newArray;
	}
	
	$event = 'Forum Post Update';
	$detail = 'Member has '.$choiceOption.' with a post. Post ID: '.$postID.'';
	addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
}


//+---------------------------------------------------------------------------------------------------------+
//|										FORUM DELETE POST - PROCESS 7										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "7"){
	$postID = $_REQUEST['uid'];
	$action = $_REQUEST['action'];
	
	//Check to see if the action is delete or restore
	if($action == "delete"){
		//action is delete
		
		//Update DELETE field in Database
		$query = "
			UPDATE ".$_SESSION['forum_posts_table']."
			SET deleted=UNIX_TIMESTAMP(), deleted_by=:deleted_by
			WHERE post_id=:post_id
		";
		
		try{
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':post_id'		=> $postID,
				':deleted_by'	=> $_SESSION['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		//END DB Update	
		
		//echo response for notification
		$postStatus = 'deleted';
		
	}elseif($action == "restore"){
		//Action is restore
		
		//Update DELETE field in Database
		$query = "
			UPDATE ".$_SESSION['forum_posts_table']."
			SET deleted='0', deleted_by=:restored_by
			WHERE post_id=:post_id
		";
		
		try{
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':post_id'		=> $postID,
				':restored_by'  => $_SESSION['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		//END DB Update	
		
		//echo response for notification
		$postStatus = "restored";
		
	}
	
	echo $postStatus;
	
	$event = 'Forum Post Update';
	$detail = 'Forum post has been '.$postStatus.'. Post ID: '.$postID.'';
	addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
}


//+---------------------------------------------------------------------------------------------------------+
//|										CREATE NEW CATEGORY - PROCESS 8										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "8"){
	
	//Set Vars
	$forumID		= $_REQUEST['forum_id'];
	$catTitle		= $_REQUEST['cat_title'];
	$catDesc		= $_REQUEST['cat_desc'];
	$stockSymbol	= $_REQUEST['stock_symbol'];
	
	
	
	//Look for symbol between ()
	$regex = '#\((([^()]+|(?R))*)\)#';
	if (preg_match_all($regex, $stockSymbol ,$matches)) {
		$stockSymbol = implode(' ', $matches[1]);
	} else {
		//no parenthesis
		$stockSymbol = $stockSymbol;
	}
	
	if($stockSymbol == NULL){
		$stockSymbol = '';	
	}
	
	//Error Check
	$error_list = array();
	
	if(empty($forumID)){
		$error_list[] = 'No Forum ID Found';	
	}
	
	if(empty($catTitle)){
		$error_list[] = 'Please provide a Category Title';	
	}
	
	if(empty($catDesc)){
		$error_list[] = 'Please provide a Category Description';
	}
	// If no errors post to Database
	if(empty($error_list)) {
	// No Errors
		
		//Insert Topic in to topic table
		$query = "
			INSERT INTO ".$_SESSION['forum_categories_table']." (
				forum_id,
				cat_title,
				cat_description,
				sequence,
				stockSymbol,
				timestamp
			) VALUE (
				:forum_id,
				:cat_title,
				:cat_description,
				:sequence,
				:stock_symbol,
				UNIX_TIMESTAMP()
			)
		";
		
		try{
			$rsAddCat = $mLink->prepare($query);
			$aValues = array(
				':forum_id'			=> $forumID,
				':cat_title'		=> $catTitle,
				':cat_description'	=> $catDesc,
				':sequence'			=> '100',
				':stock_symbol'		=> $stockSymbol
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsAddCat->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		//END DB INSERT
		
		echo $error;
		
		$forumTitle = get_category($mLink, $forumID, 'title');
		$event = 'Forum Update';
		$detail = 'Category created for Forum: '.$forumTitle.'.';
		addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
		
	}else{
	// Has Errors	
		?>
		<div class="alert alert-block alert-danger fade in">
			<button type="button" class="close" data-dismiss="alert"></button>
			<h4><strong>Please Provide Required Fields</strong></h4>
			<ul>
				<?php
				foreach($error_list as $error){
					echo '<li>'.$error.'</li>';	
				}
				?>
			</ul>
			
		</div>
		<?php
	}
		
}

//+---------------------------------------------------------------------------------------------------------+
//|										AJAX LOAD CATEGORIES - PROCESS 9									|
//+---------------------------------------------------------------------------------------------------------+
if($process == "9"){
	
	$forumID = $_REQUEST['forum'];
	$adminAccess = $_REQUEST['adminAccess'];
	$active = $_REQUEST['active'];
	
	if($active == '1'){
		$query = "
			SELECT *
			FROM ".$_SESSION['forum_categories_table']."
			WHERE forum_id=:forum_id AND active='1'
			ORDER BY sequence ASC
		";
		
		
	}elseif($active == '0'){
		$query = "
			SELECT *
			FROM ".$_SESSION['forum_categories_table']."
			WHERE forum_id=:forum_id AND active='0'
			ORDER BY sequence ASC
		";
	}
	
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
			LIMIT 25
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
		$postID = '';
				
		while($post = $rsGetPosts->fetch(PDO::FETCH_ASSOC)){
			
			//Set Vars
			$postID 		= $post['post_id'];
			$postDate 		= $post['timestamp'];
			$postUser		= get_member($mLink, $post['post_creator'], $userDisplay);
			$lastPostURL 	= get_post_url($mLink, $postID);
			$topicID		= $post['topic_id'];
			
			$topicActive = get_topic($mLink, $topicID, 'active');
				
			//check to see if the topic of the post is active, if it is break out of the while loop
			if($topicActive == '1'){
				break;	
			}
				
		}
		
		if($postID != ""){
			//If postID is not empty/blank post link			
			$lastPost = '<a href="'.$lastPostURL.'" style="display:block;"><span style="display:none;">'.$postDate.'</span>'.time_past($postDate, 'day').'<br /><small>by '.$postUser.'</small>'.$test.'</a>';	
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
						   <li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-danger btn-xs btn-full" onclick="activate(\'false\',\'category\',\''.$catID.'\');"><i class="icon-remove"></i> Remove Category</button></li>
						   <li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-info btn-xs btn-full" onclick="window.location.href=\'?page=04-01-00-003&forum='.$forumID.'&cat='.$catID.'#cat-settings\';"><i class="icon-pencil"></i> Edit Category</button></li>
						   <li class="btn-li"><button class="btn btn-warning btn-xs btn-full" type="button" onclick="lockLevel(\'cat\', \''.$catID.'\');"><i class="icon-lock"></i> Lock Forum</button></li>
						</ul>
						
					 </div><!--button-group-->
				';
			}elseif($active == '0'){
				$adminButton = '
					<div class="btn-group" style="float:left;margin-right:10px;">
						<button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" ><i class="icon-cog"></i></button>
						
						<ul class="dropdown-menu" role="menu">
						   <li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-success btn-xs btn-full" onclick="activate(\'true\',\'category\',\''.$catID.'\');"><i class="icon-ok"></i> Activate Category</button></li>
						   <li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-info btn-xs btn-full" onclick="window.location.href=\'?page=04-01-00-003&forum='.$forumID.'&cat='.$catID.'#cat-settings\';"><i class="icon-pencil"></i> Edit Category</button></li>
						</ul>
						
					 </div><!--button-group-->
				';
			}
			
			
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
	
	
	
}

//+---------------------------------------------------------------------------------------------------------+
//|										SAVE POST EDIT - PROCESS 10											|
//+---------------------------------------------------------------------------------------------------------+
if($process == "10"){

	$postID 	= $_REQUEST['post_id'];
	$postEdit 	= $_REQUEST['post-edit'];
	
	//Error Check
	$error_list = array();
	
	if(empty($postID)){
		$error_list[] = 'No Post ID Found';	
	}
	
	if(empty($postEdit)){
		$error_list[] = 'Edit field can not be left blank.';	
	}
	
	
	// If no errors post to Database
	if(empty($error_list)) {
	// No Errors
	
		//Get existing Post for comparison. 
		$query = "
			SELECT post_content
			FROM ".$_SESSION['forum_posts_table']."
			WHERE post_id=:post_id
		";
		
		try{
			$rsGetPost = $mLink->prepare($query);
			$aValues = array(
				':post_id' 	=> $postID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetPost->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$post = $rsGetPost->fetch(PDO::FETCH_ASSOC);
		
		//Check to see if the edit is the same as the original post. 
		if($postEdit != $post['post_content']){
			//Not the same, post edit.
			
			//Update DB
			$query = "
				UPDATE ".$_SESSION['forum_posts_table']."
				SET edit_by=:edit_by, edit_post=:edit_post, edit_time=UNIX_TIMESTAMP() 
				WHERE post_id=:post_id
			";
			
			try{
				$rsGetPost = $mLink->prepare($query);
				$aValues = array(
					':post_id' 		=> $postID,
					':edit_by' 		=> $_SESSION['member_id'],
					':edit_post'	=> $postEdit
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetPost->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			//END DB UPDATE
			
			echo $error;
			
			$event = 'Forum Post Updated';
			$detail = 'Forum post was edited. Post ID: '.$postID.'';
			addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
			
		}else{
			
			//echo 'Same as post';
		}
		//END - Check to see if the edit has the same post
		
	}else{
		// Has Errors	
		?>
		<div class="alert alert-block alert-danger fade in">
			<button type="button" class="close" data-dismiss="alert"></button>
			<h4><strong>Please Provide Required Fields</strong></h4>
			<ul>
				<?php
				foreach($error_list as $error){
					echo '<li>'.$error.'</li>';	
				}
				?>
			</ul>
			
		</div>
		<?php
	}
}


//+---------------------------------------------------------------------------------------------------------+
//|										CREATE NEW FORUM - PROCESS 11										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "11"){
	
	//Set Vars
	$forumTitle		= $_REQUEST['forum_title'];
	$forumDesc		= $_REQUEST['forum_desc'];
	
	//Error Check
	$error_list = array();
	
	if(empty($forumTitle)){
		$error_list[] = 'Please provide a Forum Title';	
	}
	
	if(empty($forumDesc)){
		$error_list[] = 'Please provide a Forum Description';
	}
	// If no errors post to Database
	if(empty($error_list)) {
	// No Errors
		
		//Get Max Sequence
		$query = "
			SELECT MAX(sequence) AS sequence
			FROM ".$_SESSION['forums_table']."
		";
		
		try{
			$rsGetMax = $mLink->prepare($query);
			$aValues = array(
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetMax->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$seq = $rsGetMax->fetch(PDO::FETCH_ASSOC);
		
		$sequence = $seq['sequence'] + 1;
		
		//END GET MAX SEQUENCE
		
		//Insert Topic in to topic table
		$query = "
			INSERT INTO ".$_SESSION['forums_table']." (
				forum_title,
				forum_description,
				sequence,
				active,
				timestamp
			) VALUE (
				:forum_title,
				:forum_description,
				:sequence,
				'1',
				UNIX_TIMESTAMP()
			)
		";
		
		try{
			$rsAddCat = $mLink->prepare($query);
			$aValues = array(
				':sequence'				=> $sequence,
				':forum_title'			=> $forumTitle,
				':forum_description'	=> $forumDesc
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsAddCat->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		//END DB INSERT
		
		$event = 'Forum Created';
		$detail = ''.$forumTitle.' has been created.';
		addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
		
		echo $error;
		
	}else{
	// Has Errors	
		?>
		<div class="alert alert-block alert-danger fade in">
			<button type="button" class="close" data-dismiss="alert"></button>
			<h4><strong>Please Provide Required Fields</strong></h4>
			<ul>
				<?php
				foreach($error_list as $error){
					echo '<li>'.$error.'</li>';	
				}
				?>
			</ul>
			
		</div>
		<?php
	}
		
}

//+---------------------------------------------------------------------------------------------------------+
//|										AJAX LOAD FORUMS - PROCESS 12										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "12"){
	
	$active = $_REQUEST['active'];
	$adminAccess = $_REQUEST['adminAccess'];
	
	if($active == '1'){
		$query = "
			SELECT *
			FROM ".$_SESSION['forums_table']."
			WHERE active='1'
			ORDER BY sequence ASC
		";
	}elseif($active == '0'){
		$query = "
			SELECT *
			FROM ".$_SESSION['forums_table']."
			WHERE active='0'
			ORDER BY sequence ASC
		";	
	}
	
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
		
		$postID = $forum['last_post_id'];
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
			LIMIT 25
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
				
		while($post = $rsGetPosts->fetch(PDO::FETCH_ASSOC)){
			
			//Set Vars
			$postID 		= $post['post_id'];
			$postDate 		= $post['timestamp'];
			$topicID		= $post['topic_id'];
			$catID			= $post['cat_id'];
			$postUser		= get_member($mLink, $post['post_creator'], $userDisplay);
			$lastPostURL 	= get_post_url($mLink, $postID);
			
			$catActive = get_category($mLink, $catID, 'active');
			
			if($catActive == '1'){
				
				$topicActive = get_topic($mLink, $topicID, 'active');
				
				if($topicActive == '1'){
					break;	
				}
			}
		}
		
		if($postID != ""){
			//If postID is not empty/blank post link			
			$lastPost = '<a href="'.$lastPostURL.'" style="display:block;">'.get_topic($mLink, $topicID, 'title').'<hr style="margin:5px 0px;" />By: '.$postUser.', '.time_past($postDate).'</a>';	
		}else{
			//If postID is empty/blank, do not post link
			$lastPost = '<span class="label label-info">No Posts</span>';	
		}
		
		$whereCat = '';
		//END GET LAST POST
		
		if($adminAccess == 1){
			$adminActions = '<a class="btn btn-danger btn-xs" onclick="activate(\'false\',\'forum\',\''.$forumID.'\');"><i class="icon-remove"></i> Remove</a>';
			$adminButton = '
				<div class="btn-group" style="float:left;margin-right:10px;">
					<button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" ><i class="icon-cog"></i></button>
					
					<ul class="dropdown-menu" role="menu">
					   <li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-danger btn-xs btn-full" onclick="activate(\'false\',\'forum\',\''.$forumID.'\');"><i class="icon-remove"></i> Remove Forum</button></li>
					   <li class="btn-li" style="margin-bottom:5px;"><button type="button" class="btn btn-info btn-xs btn-full" onclick="window.location.href=\'?page=04-01-00-002&forum='.$forumID.'#forum-settings\';"><i class="icon-pencil"></i> Edit Forum</button></li>
					   
					</ul>
					
				 </div><!--button-group-->
			';
		}
		
		$allowAccess = forumAccess($mLink, $forumID, 'forum');
										
		if($allowAccess != 0){
			?>
			<tr>
				<td><?php echo $adminButton;?><a href="?page=04-01-00-002&forum=<?php echo $forum['forum_id'];?>" style="display:block;float:left;"><strong><?php echo $forum['forum_title'];?></strong><br /><small><?php echo substr($forum['forum_description'], 0, 250); ?></small></a></td>
				<td><?php echo $lastPost;?></td>
				<td><a href="?page=04-01-00-002&forum=<?php echo $forum['forum_id'];?>" style="display:block;"><?php echo $cntCat;?></a></td>
				<td style="display:none;"><?php echo $forum['sequence']; ?></td>
			</tr>
			<?php
		}
	}
	
}

//+---------------------------------------------------------------------------------------------------------+
//|										MARK POST VIEWED - PROCESS 13										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "13"){
	
	$postID = $_REQUEST['post'];
	$topicID = $_REQUEST['topic'];
	$form = $_REQUEST['form'];
	$aUnread = $_REQUEST['posts'];
	
	
	if($form == "1"){
				
		foreach($aUnread as $postID){
			
			$query = "
				SELECT viewed_post
				FROM ".$_SESSION['forum_viewed_table']."
				WHERE topic_id=:topic_id AND viewed_post=:viewed_post AND member_id=:member_id
			";
			
			try{
				$rsFindPost = $mLink->prepare($query);
				$aValues = array(
					':topic_id' 	=> $topicID,
					':viewed_post'	=> $postID,
					':member_id' 	=> $_SESSION['member_id']
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsFindPost->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$numPosts = $rsFindPost->rowCount();
			
			
			
			if($numPosts == "1"){
				$query = "
					UPDATE ".$_SESSION['forum_viewed_table']."
					SET timestamp=UNIX_TIMESTAMP()
					WHERE uid=:uid
				";
				
				try{
					$rsUpdateView = $mLink->prepare($query);
					$aValues = array(
						':uid' 	=> $post['uid']
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsUpdateView->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
			}else{
				$query = "
					INSERT INTO ".$_SESSION['forum_viewed_table']." (
						member_id,
						topic_id,
						viewed_post,
						timestamp
					) VALUE (
						:member_id,
						:topic_id,
						:viewed_post,
						UNIX_TIMESTAMP()
					)
				";
				
				try{
					$rsAddView = $mLink->prepare($query);
					$aValues = array(
						':member_id' 	=> $_SESSION['member_id'],
						':topic_id'		=> $topicID,
						':viewed_post'	=> $postID
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsAddView->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
			}
		}
	}else{
	
		$query = "
			SELECT viewed_post
			FROM ".$_SESSION['forum_viewed_table']."
			WHERE topic_id=:topic_id AND viewed_post=:viewed_post AND member_id=:member_id
		";
		
		try{
			$rsFindPost = $mLink->prepare($query);
			$aValues = array(
				':topic_id' 	=> $topicID,
				':viewed_post'	=> $postID,
				':member_id' 	=> $_SESSION['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsFindPost->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$numPosts = $rsFindPost->rowCount();
		
		
		if($numPosts == "1"){
			$query = "
				UPDATE ".$_SESSION['forum_viewed_table']."
				SET timestamp=UNIX_TIMESTAMP()
				WHERE uid=:uid
			";
			
			try{
				$rsUpdateView = $mLink->prepare($query);
				$aValues = array(
					':uid' 	=> $post['uid']
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdateView->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
		}else{
			$query = "
				INSERT INTO ".$_SESSION['forum_viewed_table']." (
					member_id,
					topic_id,
					viewed_post,
					timestamp
				) VALUE (
					:member_id,
					:topic_id,
					:viewed_post,
					UNIX_TIMESTAMP()
				)
			";
			
			try{
				$rsAddView = $mLink->prepare($query);
				$aValues = array(
					':member_id' 	=> $_SESSION['member_id'],
					':topic_id'		=> $topicID,
					':viewed_post'	=> $postID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsAddView->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
		}
	}
	
}

//+---------------------------------------------------------------------------------------------------------+
//|											HASHTAG - PROCESS 14											|
//+---------------------------------------------------------------------------------------------------------+
if($process == "14"){
	
	$string = $_REQUEST['post'];
	
	
	echo hashtag($string);
}


//+---------------------------------------------------------------------------------------------------------+
//|							CREATE NEW STOCK DISCUSSION - PROCESS 15										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "15"){
	
	//Set Vars
	$stockSymbol = $_REQUEST['symbol'];
	
	//Check to see if symbol is a valid symbol
	$regex = '#\((([^()]+|(?R))*)\)#';
	if (preg_match_all($regex, $stockSymbol ,$matches)) {
		$searchData = implode(' ', $matches[1]);
	} else {
		//no parenthesis
		$searchData = $stockSymbol;
	}
	
	//echo $searchData;
	$symbolUpper = strtoupper($searchData); //force uppercase for the search string
	
	//First check to see if Category for stock already exists
	$sQuery = "
		SELECT forum_id, cat_id, cat_title, cat_description, stockSymbol
		FROM ".$_SESSION['forum_categories_table']."
		WHERE forum_id='8' AND stockSymbol=:symbol AND active='1'
	";

	try{
		$rsSearch = $mLink->prepare($sQuery);
		$aValues = array(
			':symbol' => $symbolUpper
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $searchQuery); //Debug
		$rsSearch->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$search = $rsSearch->fetch(PDO::FETCH_ASSOC);
	
	//if result set is empty, a category for that stock was not found.
	if($search['cat_id'] != ""){
		//Forum Category Found
		
		$forumID	= $search['forum_id'];
		$catID 		= $search['cat_id'];	
				
	}else{
		//Forum Category Not Found
		
		//Get stock info to make category
		$query = "
			SELECT *
			FROM ".$_SESSION['stock_valid_symbols_table']."
			WHERE symbol=:symbol
		";
	
		try{
			$rsGetStock = $mLink->prepare($query);
			$aValues = array(
				':symbol' => $symbolUpper
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $searchQuery); //Debug
			$rsGetStock->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$stock = $rsGetStock->fetch(PDO::FETCH_ASSOC);
		
		$company 	= $stock['company'];
		$exchange 	= $stock['exchange'];
		
		$catTitle 	= $company;
		$catDesc	= $exchange.':'.$symbolUpper;
		$forumID 	= '8';
		
		
		//Insert Category into category table
		$query = "
			INSERT INTO ".$_SESSION['forum_categories_table']." (
				forum_id,
				cat_title,
				cat_description,
				stockSymbol,
				sequence,
				timestamp
			) VALUE (
				:forum_id,
				:cat_title,
				:cat_description,
				:stockSymbol,
				:sequence,
				UNIX_TIMESTAMP()
			)
		";
		
		try{
			$rsAddCat = $mLink->prepare($query);
			$aValues = array(
				':forum_id'			=> $forumID,
				':cat_title'		=> $catTitle,
				':cat_description'	=> $catDesc,
				':stockSymbol'		=> $symbolUpper,
				':sequence'			=> '100'
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsAddCat->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		//END DB INSERT
		
		$catID = $mLink->lastInsertId();
		
		if(empty($error)){
			echo 'Success : '.$catID;	
		}else{
			echo "Something went wrong";	
		}
		
	} //END check to see if Category Exisits
	
	
	$topicTitle = $_REQUEST['topic_title'];
	$postContent = $_REQUEST['topic_post'];
	
	//echo 'Topic Title: '.$topicTitle.' | Post: '.$postContent;
	
	//Now that we have everything we need to make a topic, insert new topic into db
	$query = "
		INSERT INTO ".$_SESSION['forum_topics_table']." (
			forum_id,
			cat_id,
			topic_title,
			topic_creator,
			topic_reply_date,
			timestamp
		) VALUE (
			:forum_id,
			:cat_id,
			:topic_title,
			:topic_creator,
			UNIX_TIMESTAMP(),
			UNIX_TIMESTAMP()
		)
	";
	
	try{
		$rsAddTopic = $mLink->prepare($query);
		$aValues = array(
			':forum_id'			=> $forumID,
			':cat_id'			=> $catID,
			':topic_title'		=> $topicTitle,
			':topic_creator'	=> $_SESSION['member_id']
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsAddTopic->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	//END DB INSERT
	
	// Get Topic ID from inserted topic
	$topicID = $mLink->lastInsertId();
	
	// Insert Original Post in to Post table
	$query = "
		INSERT INTO ".$_SESSION['forum_posts_table']." (
			cat_id,
			topic_id,
			post_creator,
			post_content,
			orig_post,
			timestamp
		) VALUE (
			:cat_id,
			:topic_id,
			:post_creator,
			:post_content,
			'1',
			UNIX_TIMESTAMP()
		)
	";
	
	try{
		$rsAddPost = $mLink->prepare($query);
		$aValues = array(
			':cat_id'			=> $catID,
			':topic_id'			=> $topicID,
			':post_creator'		=> $_SESSION['member_id'],
			':post_content'		=> $postContent
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsAddPost->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	//END DB INSERT
	
	//Get Post ID from inserted Post
	$postID = $mLink->lastInsertId();
	
	$postURL = get_post_url($mLink, $postID);
	
	echo $postURL;
	
	//Check to see if searchMembers is set to one, this tells us if we need to lookup members that have the stock
	if($_REQUEST['members'] == ''){
		//Query DB and find all funds that have owned or own the searched stock
		$query = "
			SELECT sb.stockSymbol, sb.totalShares, sb.currentValue, sb.fundRatio, sb.fund_id, f.member_id, f.fund_name, f.fund_symbol
			FROM ".$_SESSION['fund_stratification_basic_table']." AS sb
			INNER JOIN ".$_SESSION['fund_table']." AS f ON f.fund_id=sb.fund_id
			WHERE sb.stockSymbol=:stockSymbol
			ORDER BY f.member_id
		";
	
		try{
			$rsGetMemberData = $mLink->prepare($query);
			$aValues = array(
				':stockSymbol'	=> $symbolUpper
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $searchQuery); //Debug
			$rsGetMemberData->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		//Start some resutls counters
		$fundsCnt	= 0;
		$heldCnt 	= 0;
		
		//
		$aCurHolders['desc'] = 'Array: $aCurHolders; Members that have held the searched stock';
		
		while($memberData = $rsGetMemberData->fetch(PDO::FETCH_ASSOC)){
			
			if($memberData['totalShares'] > 0){
				
				//Get member data from function
				$fMember = get_member($mLink, $memberData['member_id'], 'all');
				
				$aHolders['members'][$memberData['fund_id']] = array(
					'stockSymbol'	=> $memberData['stockSymbol'],
					'totalShares'	=> $memberData['totalShares'],
					'fundRatio'		=> number_format(($memberData['fundRatio']*100), 2).'%',
					'fund_symbol'	=> $memberData['fund_symbol'],
					'member_id'		=> $memberData['member_id']
					
				);
				
				
				$fundsCnt++;
			}
			
		}
		
		//Set array variable to hold the amount of funds that contain the searched stock.
		$aCurHolders['fundsCount'] = $fundsCnt;
		
		
		//Create a new array to reorder the previous results to group by MemberID
		foreach($aHolders['members'] as $fundID => $fundInfo){
			$aCurHolders['members'][$fundInfo['member_id']][$fundID] = $fundInfo;
			
			//get just a list of member ids to use in notifications
			if($fundInfo['member_id'] != $_SESSION['member_id']){
				$aMemberIds[$fundInfo['member_id']] = $fundInfo['member_id'];
			}
		}	
	}else{
		$aMemberIds = explode(',',$_REQUEST['members']);	
	}
	//End serach for members that have stock
	
	print_r($aMemberIds);
	
	//START NOTIFICATION
	$notificationID = "04-005";
	$postCreator	= $_SESSION['member_id'];
	
	//Custom notification
	$notification	= "".get_member($mLink, $_SESSION['member_id'], "username")." is talking about ".$symbolUpper.", a stock you hold. Click Here to view.";
	$link			= get_post_url($mLink, $postID);
	
	foreach($aMemberIds as $key => $memberID){
		add_notification($mLink, $notificationID, $memberID, $notification, $link);
		//echo $memberID;
	}
	//END NOTIFICATION
	
	//Update Event Log
	$event = 'New Stock Discussion';
	$detail = 'A new stock discussion has been started for STOCK: '.$stockSymbol.'';
	addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
		
}


//+---------------------------------------------------------------------------------------------------------+
//|								VALIDATE STOCK SYMBOL - PROCESS 16											|
//+---------------------------------------------------------------------------------------------------------+
if($process == "16"){
	
	$stockSymbol = $_REQUEST['symbol'];
	
	//Check to see if symbol is a valid symbol
	$regex = '#\((([^()]+|(?R))*)\)#';
	if (preg_match_all($regex, $stockSymbol ,$matches)) {
		$searchData = implode(' ', $matches[1]);
	} else {
		//no parenthesis
		$searchData = $stockSymbol;
	}
	
	//echo $searchData;
	$symbolUpper = strtoupper($searchData); //force uppercase for the search string
	
	// Search Valid Stocks table for the search string
	$sQuery = "
		SELECT symbol, company
		FROM ".$_SESSION['stock_valid_symbols_table']."
		WHERE symbol='".$symbolUpper."'
	";
	
	try{
		$rsSearch = $mLink->prepare($sQuery);
		$aValues = array();
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $searchQuery); //Debug
		$rsSearch->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$search = $rsSearch->fetch(PDO::FETCH_ASSOC);
	
	if($search['symbol'] == ''){
		echo '0';
	}else{
		echo '1';	
	}
}

//+---------------------------------------------------------------------------------------------------------+
//|								SAVE FORUM ACCESS RIGHTS - PROCESS 17										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "17"){

	$forumID 			= $_REQUEST['forum'];
	$selectGroups		= implode('|',$_REQUEST['select_groups']);
	$selectMemberTypes	= implode('|',$_REQUEST['select_member_types']);
	$selectMembers		= implode('|',$_REQUEST['select_members']);
	$allowAll			= $_REQUEST['allow_all'];
		
	//if Grant access to all checkbox is checkbox
	if($allowAll == 'on'){
		//checkbox checked, wipe "allow" columns to NULL
		
		$query = "
			UPDATE ".$_SESSION['forums_table']."
			SET allow_members_flags=NULL, allow_forum_groups=NULL, allow_members=NULL
			WHERE forum_id=:forum_id
		";
		
		try{
			$rsUpdateForum = $mLink->prepare($query);
			$aValues = array(
				':forum_id' => $forumID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $searchQuery); //Debug
			$rsUpdateForum->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		echo $forumID.' | '.$allowAll.' | '.$error;
	}else{
		//checkbox NOT checked update "allow" columns with selections
		
		if($selectMemberTypes == ''){
			$selectMemberTypes = NULL;	
		}
		if($selectGroups == ''){
			$selectGroups = NULL;	
		}
		if($selectMembers == ''){
			$selectMembers = NULL;
		}
		
		$query = "
			UPDATE ".$_SESSION['forums_table']."
			SET allow_members_flags=:member_flags, allow_forum_groups=:forum_groups, allow_members=:members
			WHERE forum_id=:forum_id
		";
		
		try{
			$rsUpdateForum = $mLink->prepare($query);
			$aValues = array(
				':forum_id' 	=> $forumID,
				':member_flags'	=> $selectMemberTypes,
				':forum_groups'	=> $selectGroups,
				':members'		=> $selectMembers
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $searchQuery); //Debug
			$rsUpdateForum->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		echo $forumID.' | '.$selectGroups.' | '. $selectMemberTypes.' | '.$selectMembers.' | '.$error;
		
		//Add entry into event log
		$forumTitle = get_forum($mLink, $forumID, 'title');
		$event = 'Forum Updated';
		$detail = 'Access Rights updated for Forum: '.$forumTitle.'';
		addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
	}
	
}

//+---------------------------------------------------------------------------------------------------------+
//|								SAVE CATEGORY ACCESS RIGHTS - PROCESS 17-2										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "17-2"){

	$catID 			= $_REQUEST['cat'];
	$selectGroups		= implode('|',$_REQUEST['select_groups']);
	$selectMemberTypes	= implode('|',$_REQUEST['select_member_types']);
	$selectMembers		= implode('|',$_REQUEST['select_members']);
	$allowAll			= $_REQUEST['allow_all'];
		
	//if Grant access to all checkbox is checkbox
	if($allowAll == 'on'){
		//checkbox checked, wipe "allow" columns to NULL
		
		$query = "
			UPDATE ".$_SESSION['forum_categories_table']."
			SET allow_members_flags=NULL, allow_forum_groups=NULL, allow_members=NULL
			WHERE cat_id=:cat_id
		";
		
		try{
			$rsUpdateForum = $mLink->prepare($query);
			$aValues = array(
				':cat_id' => $catID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $searchQuery); //Debug
			$rsUpdateForum->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		echo $forumID.' | '.$allowAll.' | '.$error;
	}else{
		//checkbox NOT checked update "allow" columns with selections
		
		if($selectMemberTypes == ''){
			$selectMemberTypes = NULL;	
		}
		if($selectGroups == ''){
			$selectGroups = NULL;	
		}
		if($selectMembers == ''){
			$selectMembers = NULL;
		}
		
		$query = "
			UPDATE ".$_SESSION['forum_categories_table']."
			SET allow_members_flags=:member_flags, allow_forum_groups=:forum_groups, allow_members=:members
			WHERE cat_id=:cat_id
		";
		
		try{
			$rsUpdateForum = $mLink->prepare($query);
			$aValues = array(
				':cat_id' 		=> $catID,
				':member_flags'	=> $selectMemberTypes,
				':forum_groups'	=> $selectGroups,
				':members'		=> $selectMembers
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $searchQuery); //Debug
			$rsUpdateForum->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		//echo $forumID.' | '.$selectGroups.' | '. $selectMemberTypes.' | '.$selectMembers.' | '.$error;
		
		
		
		//Add entry into event log
		$catTitle = get_category($mLink, $forumID, 'title');
		$event = 'Category Updated';
		$detail = 'Access Rights updated for Category: '.$catTitle.'';
		addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
	}
	sleep(1);
	
}

//+---------------------------------------------------------------------------------------------------------+
//|							ACTIVATE/DEACTIVATE FORUM ELEMENT - PROCESS 18									|
//+---------------------------------------------------------------------------------------------------------+
if($process == "18"){
	$activate 	= $_REQUEST['activate'];
	$level		= $_REQUEST['level'];
	$levelID	= $_REQUEST['levelID'];
	
	switch($activate){
		case 'false': $active = '0';$eventType = 'Deactivated';break;
		case 'true'	: $active = '1';$eventType = 'Activated';break;
	}
	
	switch($level){
		case "forum": 
			$skip = 0;
			$db = $_SESSION['forums_table']; 
			$colID = 'forum_id'; 
			
			$forumTitle = get_forum($mLink, $levelID, 'title');
			$event = 'Forum '.$eventType.'';
			$detail = 'Forum: '.$forumTitle.' has been '.$eventType.'.';
		
		break;
		
		case "category"	: 
			$skip = 0;
			$db = $_SESSION['forum_categories_table']; 
			$colID = 'cat_id';  
			
			$catTitle = get_category($mLink, $levelID, 'title');
			$event = 'Forum Category '.$eventType.'';
			$detail = 'Forum Category: '.$catTitle.' has been '.$eventType.'.';
		
		break;
		
		case "topic": 
			$skip = 0;
			$db = $_SESSION['forum_topics_table']; 
			$colID = 'topic_id';  
			
			$topicTitle = get_topic($mLink, $levelID, 'title');
			$event = 'Forum Topic '.$eventType.'';
			$detail = 'Forum Topic: '.$topicTitle.' has been '.$eventType.'.';
		break;
		
		default:$skip = 1;
	}
	
	
	
	if($skip == 0){
		$query = "
			UPDATE ".$db."
			SET active=:active
			WHERE ".$colID."=:level_id
		";
		
		try{
			$rsUpdateElement = $mLink->prepare($query);
			$aValues = array(
				':active' 	=> $active,
				':level_id'	=> $levelID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $searchQuery); //Debug
			$rsUpdateElement->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		//Add entry into event log
		
		
		addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
		
	}
	
	//echo $activate.' | '.$level.' | '.$levelID.' | '.$query.' | '.$error;
}

//+---------------------------------------------------------------------------------------------------------+
//|									UPDATE FORUM INFO - PROCESS 19											|
//+---------------------------------------------------------------------------------------------------------+
if($process == "19"){
	
	$forumTitle = $_REQUEST['forum_title'];
	$forumDesc = $_REQUEST['forum_desc'];
	$forumID = $_REQUEST['forum_id'];
	$sequence = $_REQUEST['sequence'];
	
	$query = "
		UPDATE ".$_SESSION['forums_table']."
		SET forum_title=:forum_title, forum_description=:forum_desc, sequence=:sequence
		WHERE forum_id=:forum_id
	";
	
	try{
		$rsUpdateForum = $mLink->prepare($query);
		$aValues = array(
			':sequence'		=> $sequence,
			':forum_title'	=> $forumTitle,
			':forum_desc'	=> $forumDesc,
			':forum_id'		=> $forumID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsUpdateForum->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$event = 'Forum Updated';
	$detail = 'Forum: '.$forumTitle.' has been updated.';
	
	addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
	
	//echo $_SESSION['forum_table'].' | '.$forumTitle.' | '.$forumDesc.' | '.$forumID.' | '.$sequence.' | '.$error;
	
	?>
    <div class="form-group">
        <label class="control-label">Forum Title* <span id="forum_title-help"></span></label>
        <input type="text" class="form-control" name="forum_title" id="forum_title" value="<?php echo $forumTitle;?>" />
    </div>
    
    <div class="form-group">
        <label class="control-label">Forum Description* <span id="forum_desc-help"></span></label>
        <textarea class="form-control" name="forum_desc" id="forum_desc" row="5" style="resize:vertical;"><?php echo $forumDesc;?></textarea>
    </div>
    
    <div class="form-group">
        <label class="control-label">Forum Order/Sequence* (Numbers Only)<span id="sequence-help"></span></label>
        <input type="text" class="form-control" name="sequence" id="sequence" value="<?php echo $sequence;?>" style="width:60px !important;" />
    </div>
    <?php
	
}

//+---------------------------------------------------------------------------------------------------------+
//|									UPDATE Category INFO - PROCESS 19-2										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "19-2"){
	
	$catTitle = $_REQUEST['cat_title'];
	$catDesc = $_REQUEST['cat_desc'];
	$catID = $_REQUEST['cat_id'];
	$sequence = $_REQUEST['sequence'];
	
	$query = "
		UPDATE ".$_SESSION['forum_categories_table']."
		SET cat_title=:cat_title, cat_description=:cat_desc, sequence=:sequence
		WHERE cat_id=:cat_id
	";
	
	try{
		$rsUpdateCat = $mLink->prepare($query);
		$aValues = array(
			':sequence'		=> $sequence,
			':cat_title'	=> $catTitle,
			':cat_desc'		=> $catDesc,
			':cat_id'		=> $catID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsUpdateCat->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$event = 'Category Updated';
	$detail = 'Forum: '.$catTitle.' has been updated.';
	
	addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
	
	//echo $_SESSION['forum_table'].' | '.$forumTitle.' | '.$forumDesc.' | '.$forumID.' | '.$sequence.' | '.$error;
	
	?>
    <div class="form-group">
        <label class="control-label">Category Title* <span id="forum_title-help"></span></label>
        <input type="text" class="form-control" name="cat_title" id="cat_title" value="<?php echo $catTitle;?>" />
    </div>
    
    <div class="form-group">
        <label class="control-label">Category Description* <span id="forum_desc-help"></span></label>
        <textarea class="form-control" name="cat_desc" id="cat_desc" row="5" style="resize:vertical;"><?php echo $catDesc;?></textarea>
    </div>
    
    <div class="form-group">
        <label class="control-label">Category Order/Sequence* (Numbers Only)<span id="sequence-help"></span></label>
        <input type="text" class="form-control" name="sequence" id="sequence" value="<?php echo $sequence;?>" style="width:60px !important;" />
    </div>
    <?php
	sleep(1);
}



//+---------------------------------------------------------------------------------------------------------+
//|										LOAD BADGES - PROCESS 20											|
//+---------------------------------------------------------------------------------------------------------+
if($process == "20"){
	
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
		$aListBadges[$badge['badge_id']] = array(
			'badge_id'		=> $badge['badge_id'],
			'badge_name'	=> $badge['badge_name'],
			'type'			=> $badge['badge_type'],
			'badge_img'		=> '/images/badges/'.$badge['badge'],
			'badge_desc'	=> $badge['badge_desc'],
			'badge_group'	=> $badge['group'],
			'badge_weight'	=> $badge['weight']
		);
		
	}
	//END build badge array
	
	$memberID = $_REQUEST['member'];
	
	/*$query = "
		SELECT fund_id, badges
		FROM ".$_SESSION['fund_settings_table']."
		WHERE fund_id LIKE :member_id AND badges<>''
	";
	
	
	try{
		$rsBadges = $mLink->prepare($query);
		$aValues = array(
			':member_id' => $memberID.'-%'
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
	
		$aFundBadges[$badges['fund_id']] = explode(',',$badges['badges']);
		
	}*/
	
	$aFundBadges = get_fund_badges($mLink, $memberID, 'show-badges-modal');
	
	?>
    <div class="modal-header">
       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
       <h4 class="modal-title">Badges For <span class="label label-info"><?php echo $_REQUEST['name']; ?></span></h4>
    </div>
    
    <div class="modal-body">
        
        
        
        <?php
		foreach($aFundBadges as $fundID=>$aBadges){
			
			$fundSymbol2 	= get_funds($mLink, $fundID, 'fundSymbol');
													
			
				
			echo '<h4><strong><a href="?page=04-00-00-001&member='.$memberID.'&tab='.$fundID.'" target="_blank">'.strtoupper($fundSymbol2).'</a></strong></h4>';
			
			
			foreach($aBadges as $key=>$badgeID){
				 
				 if(array_key_exists($badgeID, $aListBadges)){
					$badgeImg 	= $aListBadges[$badgeID]['badge_img'];
					$badgeDesc	= $aListBadges[$badgeID]['badge_desc'];
					
					//echo $badgeImg;
					
					echo '<img src="'.$badgeImg.'" alt="'.$badgeDesc.'" title="'.$fundSymbol2.': '.$badgeDesc.'" width="40" height="40" />&nbsp;&nbsp;'; 
				 }
				 
			}
			
			echo '<hr />';
				
		}
		?>
            
       
    </div><!--modal-body-->
    
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
    </div>
                
    <?php
	
}


//+---------------------------------------------------------------------------------------------------------+
//|								Mark all posts within topics as read - PROCESS 21							|
//+---------------------------------------------------------------------------------------------------------+
if($process == "21"){

	$aTopics 	= $_REQUEST['checkTopic'];
	
	$aPosts		= array();
	
	
	
	foreach($aTopics as $key=>$topicID){
		
		$aGetPostIDs = $_REQUEST['post-ids-'.$topicID];
		
		$aRawIDs = explode('|', $aGetPostIDs);
		
		$aPosts[$topicID] = $aRawIDs;
			
	}
	
	if(!empty($aPosts)){	
		foreach($aPosts as $topicID=>$aListPosts){
			
			foreach($aListPosts as $key=>$postID){
				
				$query = "
						INSERT INTO ".$_SESSION['forum_viewed_table']." (
							member_id,
							topic_id,
							viewed_post,
							timestamp
						) VALUE (
							:member_id,
							:topic_id,
							:viewed_post,
							UNIX_TIMESTAMP()
						)
					";
					
					try{
						$rsAddView = $mLink->prepare($query);
						$aValues = array(
							':member_id' 	=> $_SESSION['member_id'],
							':topic_id'		=> $topicID,
							':viewed_post'	=> $postID
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsAddView->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
			}
				
		}
	}else{
		echo '<div class="alert alert-danger">Please select at least one topic by clicking the checkbox next to the topic title.</div>';	
	}
	/*echo '<pre>';
	print_r($aTopics);
	echo count($aPosts);
	print_r($aPosts);
	echo '</pre>';*/

}


//+---------------------------------------------------------------------------------------------------------+
//|								Lock Level (Forum, Category, Topic) - PROCESS 22							|
//+---------------------------------------------------------------------------------------------------------+
if($process == "22"){
	
	$elementID	= $_REQUEST['element'];
	$level		= $_REQUEST['level'];
	$type		= $_REQUEST['type'];
	
	if($type == 'lock'){
		switch($level){
			case 'cat'	: 
				$query = "
					UPDATE ".$_SESSION['forum_topics_table']."
					SET closed=UNIX_TIMESTAMP()
					WHERE cat_id=".$elementID."
				";
				$levelWord = "Category";
			break;
			case 'topic'	:
				$query = "
					UPDATE ".$_SESSION['forum_topics_table']."
					SET closed=UNIX_TIMESTAMP()
					WHERE topic_id=".$elementID."
				";
				$levelWord = "Topic";
			break;	
		}
				
		//echo $preparedQuery.' | '.$elementID.' | '.$type.' | '.$error;
		
		$message = $levelWord.' as been locked.';
		
	}elseif($type == 'unlock'){
		switch($level){
			case 'cat'	: 
				$query = "
					UPDATE ".$_SESSION['forum_topics_table']."
					SET closed='0'
					WHERE cat_id=".$elementID."
				";
				$levelWord = "Category";
			break;
			case 'topic'	:
				$query = "
					UPDATE ".$_SESSION['forum_topics_table']."
					SET closed='0'
					WHERE topic_id=".$elementID."
				";
				$levelWord = "Topic";
			break;	
		}
		
		$message = $levelWord.' as been unlocked.';
	}
	
	//execute query
	try{
		$rsUpdate = $mLink->prepare($query);
		$aValues = array();
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsUpdate->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	echo $message;
}





