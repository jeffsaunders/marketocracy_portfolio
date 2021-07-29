<?php
/*
Marketocracy Inc. | Beta Development
Admin Managers

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/admin-managers-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/admin-managers.js.php
	HTML		- includes/pages/admin-managers.php
*/

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


switch($process){
	
	case 'load-total-aum':
		
		
		
	break;
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//													View Campaign Report
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'view-campaign-report':
	
		$campID = $_REQUEST['campID'];
		
		$query = "
			SELECT et.*, m.name_first, m.name_last, m.username 
			FROM email_tracking AS et
			INNER JOIN members AS m ON m.member_id=et.member_id
			WHERE camp_id=:camp_id AND bounced IS NULL
		";
		try{
			$rsTracking = $mLink->prepare($query);
			$aValues = array(
				':camp_id'	=> $campID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsTracking->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		echo $error;
		
		$trackerCnt = 0;
		$numOpened	= 0;
		$numClicked	= 0;
		$aList		= array();
			
		while($tracker = $rsTracking->fetch(PDO::FETCH_ASSOC)){
			
			$aList[$tracker['member_id']] = array(
				'name_first'	=> $tracker['name_first'],
				'name_last'		=> $tracker['name_last'],
				'username'		=> $tracker['username'],
				'opened'		=> $tracker['opened'],
				'open_ip'		=> $tracker['opened_ip'],
				'clicked'		=> $tracker['clicked'],
				'clicked_ip'	=> $tracker['clicked_ip']
			);
			
			$trackerCnt++;
			
			if($tracker['opened'] != NULL){
				$numOpened++;	
			}
			
			if($tracker['clicked'] == '1'){
				$numClicked++;	
			}
			
		}
		
		$openRatio 		= (($numOpened/$trackerCnt)*100);
		$clickedRatio	= (($numClicked/$trackerCnt)*100);
		
		?>
        <div class="profile-section">
            <h4>Email Activity</h4>
            
            <ul class="stat-list">
                
                <li>Opened: <span><?php echo $numOpened;?> (of <?php echo $trackerCnt;?>)</span></li>
                <li>Clicked Through: <span><?php echo $numClicked;?> (of <?php echo $trackerCnt;?>)</span></li>
                <li>Open Rate: <span><?php echo number_format($openRatio,2);?>%</span></li>
                <li>Click-to-Open Rate: <span><?php echo number_format($clickedRatio,2);?>%</span></li>
            </ul>
            
            <div class="clearfix"></div>
            
           
            <table class="table table-striped table-bordered table-hover" id="campaign-table" style="margin-top:20px;">
                <thead>
                    <tr>
                        <th>Recipient</th>
                        <th>Opened On</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                	<?php
					foreach($aList as $memberID=>$memberInfo){
						
						if($memberInfo['opened'] != NULL){
							$dateOpen = date('m/d/Y h:i', $memberInfo['opened']);	
						}else{
							$dateOpen = "Not Opened";	
						}
						
						?>
                        <tr>
                        	<td><a href="/?page=20-00-00-003&member=<?php echo $memberID;?>" target="_blank"><?php echo $memberInfo['name_first'];?> <?php echo $memberInfo['name_last'];?></a></td>
                            <td><?php echo $dateOpen;?></td>
                            <td><?php echo $memberInfo['open_ip'];?></td>
                            
                        </tr>
                        <?php	
					}
					?>
                </tbody>
                
                </table>
            
        </div><!--profile-section-->
        <?php
		
	
	break;
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//													Create Campaign Emails
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'create-email-campaign':
	
		$post 			= $_POST;
		$memberID		= $_SESSION['member_id'];
		$campName 		= $_REQUEST['camp_name'];
		$sendTo			= $_REQUEST['sent-to'];
		$templateID		= $_REQUEST['email-template'];
		$campType		= 'manager_update';
		$campStatus		= 'draft';
		
		$emailTitle		= $_REQUEST['email_title'];
		$emailSubject	= $_REQUEST['email_subject'];
		$emailHeadline	= $_REQUEST['email_headline'];
		$emailBody		= $_REQUEST['email_body'];
		//$emailClosing	= $_REQUEST['email_closing'];
		$sendTest		= $_REQUEST['send_test'];
		$testEmail		= $_REQUEST['test_email'];
		
		
		
		//echo $sendTest;
		
		$aErrors		= array();
		
		if($sendTest == '1'){
			
			if(empty($testEmail)){
				$aErrors[] = 'Please provide a test name and email address in this format: Brandon:brandon.mccarthy@marketocracy.com';	
			}else{
				if (strpos($testEmail, ':') !== false) {
					//echo 'true';
				}else{
					$aErrors[] = 'Please properly format the test email field, example: Brandon:brandon.mccarthy@marketocracy.com';	
				}
			}
			
		}
		
        $emailBody = preg_replace_callback(
			'~\[([^\]]+)\]\(([^)]+)\)~',
			function ($m) {
				return '<a href="http://portfolio.marketocracy.com/includes/email/tracking.php?track=clicked~[TRACK_ID]~'.$m[2].'~[TRACKER_CODE]~[EMAIL_ADDRESS]">'.substr($m[1],0,80).'</a>';
			},
			$emailBody
		);
		
		if(empty($campName)){
			$aErrors[]	= 'Please provide a name for this email campaign.';
		}else{
			
			$query = "
				SELECT camp_name
				FROM ".$_SESSION['email_campaigns_table']."
				WHERE camp_name=:camp_name AND member_id=:member_id
			";
			try{
				$rsGetCamp = $mLink->prepare($query);
				$aValues = array(
					':camp_name'	=> strtolower($campName),
					':member_id'	=> $_SESSION['member_id']
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetCamp->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			//$aErrors[] = $error;
			
			$rowCount = $rsGetCamp->rowCount();
			
			if($rowCount > 0){
				$aErrors[] = 'Campaign Name already in use. Please choose a different campaign name.';	
			}
		}
		
		if($templateID == 'x'){
			$aErrors[]	= 'Please select an email template.';
		}	
		
		if(empty($emailTitle)){
			$aErrors[]	= 'Please provide a title for this email.';
		}
		
		if(empty($emailSubject)){
			$aErrors[]	= 'Please provide a subject for this email.';
		}
		
		
		
		if(empty($emailBody)){
			$aErrors[]	= 'Please provide content in the body for this email.';
		}
		
		/*if(empty($emailClosing)){
			$aErrors[]	= 'Please provide a closing for this email.';
		}*/
		
		//$aErrors[] = $aRecipients;
		
		//No errors start processing
		if(empty($aErrors)){
			
			
			
			
			if($sendTest == '1'){
				
				$aTestEmail = explode(':',$testEmail);
				
				$testEmailAddress 	= $aTestEmail[1];
				$testEmailName		= $aTestEmail[0];
				
				$aRecipients 	=	array(
					
					'0'	=> array(
						'email_address'		=> 'brandon.mccarthy@marketocracy.com',
						'recepient_vars'	=> array(
							'name_first'	=> 'Brandon'
						)
					)
				);
				
				$aRecipients[1] =	 array(
						'email_address'		=> $testEmailAddress,
						'recepient_vars'	=> array(
							'name_first'	=> $aTestEmail[0]
						)
					);
				
				$aEmail = array(
					'camp_type'			=> $campType,
					'camp_name'			=> $campName,
					'camp_status'		=> $campStatus,
					'temp_id'			=> $templateID,
					'recipient_list'	=> $sendTo,
					'email_title'		=> $emailTitle,
					'email_subject'		=> $emailSubject,
					'email_headline'	=> $emailHeadline,
					'email_body'		=> $emailBody,
					'email_closing'		=> NULL,
					'schedule_date'		=> NULL,
					'recipients'		=> $aRecipients
				);
				
				$campaignStatus = sendTestEmail($mLink, $aEmail, $aEmailVars);
				
				echo '<div class="alert alert-danger">
					<h3>Test Email Sent Successfully</h3>
					<p>You must unclick "Send Test" checkbox to send to list.</p>
				</div>';	
			}else{
				
				switch($sendTo){
					
					case 'managers':
						
						$query = "
							SELECT m.email, m.name_first, m.member_id
							FROM members_subscriptions AS s
							INNER JOIN members AS m ON m.member_id=s.member_id
							WHERE s.product_id=10
						";
						try{
							$rsGetManagers = $mLink->prepare($query);
							$aValues = array();
							$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
							$rsGetManagers->execute($aValues);
						}
						
						catch(PDOException $error){
							// Log any error
							file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
						}
						
						while($manager = $rsGetManagers->fetch(PDO::FETCH_ASSOC)){
							
							$aRecipients[$manager['member_id']] = array(
								'email_address'		=> $manager['email'],
								'recepient_vars'	=> array(
									'name_first'	=> $manager['name_first']
								)
							);
								
						}
					break;
						
				}
				
				$aRecipients['2'] = array(
					'email_address'		=> 'mccarthyhe@gmail.com',
					'recepient_vars'	=> array(
												'name_first'	=> 'Brandon'
											)
				);
				
				
				
				$aEmail = array(
					'camp_type'			=> $campType,
					'camp_name'			=> $campName,
					'camp_status'		=> $campStatus,
					'temp_id'			=> $templateID,
					'recipient_list'	=> $sendTo,
					'email_title'		=> $emailTitle,
					'email_subject'		=> $emailSubject,
					'email_headline'	=> $emailHeadline,
					'email_body'		=> $emailBody,
					'email_closing'		=> NULL,
					'schedule_date'		=> NULL,
					'recipients'		=> $aRecipients
				);
				
				$campaignStatus = sendEmail($mLink, $aEmail, $aEmailVars, true, true);
			}
			
			
			//$aErrors[] = $campaignStatus;
			
			
			if(!empty($aErrors)){
				echo '<div class="alert alert-danger"><ul>';
				foreach($aErrors as $key=>$error){
					echo '<li>'.$error.'</li>';	
				}
				echo '</ul></div>';	
			}
			
			
			
			
			/*echo '<div class="alert alert-danger"><ul>';echo '<pre>';
			echo $emailBody;
			print_r($campaignStatus);
			print_r($aRecipients);
			echo '</pre>';
			echo '</ul></div>';	*/
		}else{
			
			echo '<div class="alert alert-danger"><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';	
			}
			echo '</ul></div>';	
			//echo 'test';
		}
		
		/*echo '<pre>';
		echo 'note-danger';
		print_r($campaignStatus);
		print_r($post);
		echo '</pre>';*/
	break;
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//													Load Articles
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'load-articles':
		
		//echo '<tr><td colspan="7">Test</td></tr>';
		
		$query = "
			SELECT ma.*, m.*
			FROM ".$_SESSION['members_articles_table']." AS ma
			INNER JOIN ".$_SESSION['members_table']." AS m on m.member_id=ma.member_id
			WHERE ma.deleted='0' and ma.published='1'
			ORDER BY ma.publish_date DESC
		";
		try{
			$rsGetArticles = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $_SESSION['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetArticles->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$cnt = 0;
		while($article = $rsGetArticles->fetch(PDO::FETCH_ASSOC)){
			
			$cnt++;
			
			$published		= $article['published'];
			$articleType	= $article['article_type'];
			$articleStocks	= $article['article_stock'];
			// Added in order to display managers tagged in each article - JSS
                        $articleTags    = $article['article_tags'];
			
			switch($articleType){
				case 'article':$articleType = 'Marketocracy Article';break;
				case 'forbes':$articleType = "Forbes Article";break;
				case 'blog':$articleType = "Blog Article";break;
				case 'ext-article':$articleType = 'External Article';break;	
				case 'seekingalpha':$articleType = 'Seeking Alpha Article';break;
			}
			
			if($published == '1'){
				$actionBtns = '<button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#edit-article" onclick="editArticle(\'load\',\''.$article['article_id'].'\');"><i class="icon-pencil"></i> Edit</button> <button type="button" class="btn btn-danger btn-xs" onclick="editArticle(\'delete\',\''.$article['article_id'].'\');">Delete</button>';
				$status = "Published";	
			}else{
				$actionBtns	= '';
				$status = "Not Published";
			}
			
			$nameFirst	= $article['name_first'];
			$nameLast	= $article['name_last'];
			$fullName	= $nameFirst.' '.$nameLast;
			$memberID	= $article['member_id'];
			$managerLink	= '<a href="?page=20-00-00-003&member='.$memberID.'" target="_blank">'.$fullName.'</a>';
			
			if(!empty($articleStocks)){
				$showStocks = '<br /><small>Stock Tags: '.$articleStocks.'</small>';	
			}else{
				$showStocks = '';	
			}
                        // Added in order to display managers tagged in each article - JSS
                        if(!empty($articleTags)){
                                $showTags = '<br /><small>Article Tags: '.$articleTags.'</small>';
                        }else{
                                $showTags = '';
                        }
			
			?>
            <tr>
            	<td><?php echo $cnt;?></td>
                <td><?php echo $managerLink;?></td>
                <td><a href="<?php echo $article['article_link'];?>" target="_blank"><?php echo $article['article_title'];?></a><?php echo $showStocks;?><?php echo $showTags;?></td>
                <td><?php echo date('m/d/Y h:i a', $article['publish_date']);?></td>
                // Added in order to display managers tagged in each article - JSS
                <td><?php echo $articleType;?></td>
                <td><?php echo date('m/d/Y h:i a',$article['timestamp']);?></td>
                <td><?php echo $status;?></td>
                
                <td>
                <?php echo $actionBtns;?>
                <a href="https://portfolio.marketocracy.com/process/ajax/admin-switch-user.php?member=<?php echo $memberID;?>&admin=<?php echo $_SESSION['member_id'];?>&toggle=admin-to-user&gopage=01-00-00-003&returnPage=20-00-00-007" class="btn btn-xs btn-success"><i class="icon-external-link"></i> Action Center</a> </td>
            </tr>
            <?php
			
		}
		
	break;
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//														Add Article
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'add-article':
		
		$aErrors		= array();
		$memberID		= $_REQUEST['manager'];
		$articleType	= $_REQUEST['article_type'];
		$articleTitle	= $_REQUEST['article_title'];
		$articleLink	= $_REQUEST['article_link'];
		$articleText	= $_REQUEST['article_text'];
		$articleStocks	= $_REQUEST['article_stocks'];
		$articleTags	= $_REQUEST['article_tags'];
		$publishDay		= $_REQUEST['day'];
		$publishMonth	= $_REQUEST['month'];
		$publishYear	= $_REQUEST['year'];
		$genCampaign	= $_REQUEST['gen_camp'];
		$campList		= $_REQUEST['camp_list'];
		
		
		$publishDate	= mktime(5,0,0,$publishMonth,$publishDay,$publishYear);
		
		//$aErrors[]		= 'test';
		if($articleType == 'xx'){
			$aErrors[] 	= 'Please select an article type.';	
		}else{
		
			if(empty($articleTitle)){
				$aErrors[]	= 'Please provide an article title.';	
			}
			if(empty($articleLink)){
				$aErrors[]	= 'Please provide a article link.';	
			}else{
				
				
				if(strpos($articleLink, 'https') !== false){
					$https = true;	
				}else{
					$https = false;	
				}
				
				$articleLink	= filter_var($articleLink, FILTER_SANITIZE_URL);
				
				$articleLink = str_replace('https://', '', $articleLink);
				$articleLink = str_replace('http://', '', $articleLink);
				
				if($https == true){
					$articleLink = 'https://'.$articleLink;
				}else{
					$articleLink = 'http://'.$articleLink;
				}
				
				if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$articleLink)) {
					$aErrors[]	= 'The link you provided is invalid.';
				}
				
			}
			if(empty($articleText)){
				$aErrors[]	= 'Please provide preview text for the article.';	
			}else{
				
				//$articleText = tokenTruncate($articleText,200);
					
			}
			
			if($genCampaign == 1){
				if($campList == 'xx'){
					$aErrors[]	= 'Please select an email list.';	
				}
			}//end if gen button = 1
			
		}
		
		if(empty($aErrors)){
			
			echo '<pre>';
			echo $articleText;
			print_r($_REQUEST);
			echo '</pre>';
			
			$query = "
				INSERT INTO ".$_SESSION['members_articles_table']." (
					article_type,
					member_id,
					article_link,
					article_title,
					article_text,
					article_stock,
					article_tags,
					publish_date,
					timestamp
				) VALUES (
					:article_type,
					:member_id,
					:article_link,
					:article_title,
					:article_text,
					:article_stocks,
					:article_tags,
					:publish_date,
					UNIX_TIMESTAMP()
				)
			";
			try{
			$rsInsert = $mLink->prepare($query);
				$aValues = array(
					':member_id'		=> $memberID,
					':article_type'		=> $articleType,
					':article_title'	=> $articleTitle,
					':article_text'		=> $articleText,
					':article_stocks'	=> $articleStocks,
					':article_tags'		=> $articleTags,
					':article_link'		=> $articleLink,
					':publish_date'		=> $publishDate
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsInsert->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}	
			
			if($genCampaign == 1){
				
				$aCamp = array(
					'camp_list'		=> $campList,
					'camp_type'		=> 'standard_campaign',
					'temp_id'		=> '23',
					'camp_name'		=> $articleStocks,
					'article_title'	=> $articleTitle,
					'article_text'	=> $articleText,
					'aritcle_link'	=> $articleLink
				);
				
				generateArticleCampaign($mLink, $aCamp);	
			}
			
		}else{
			
			echo '<div class="note note-danger"><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';
			}
			echo '</ul></div>';
				
		}
		
	break;
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//													Load Article Fields
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'load-article-fields':
		
		$prefill 		= $_REQUEST['prefill'];
		
		//echo $prefill;
		
		if($prefill == '1'){
			
			$articleID		= $_REQUEST['articleID'];
				
			$query = "
				SELECT * 
				FROM ".$_SESSION['members_articles_table']."
				WHERE article_id=:article_id
			";
			try{
				$rsGetArticle = $mLink->prepare($query);
					$aValues = array(
						':article_id'	=> $articleID
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsGetArticle->execute($aValues);
			}
			catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			echo $error;
			
			$article = $rsGetArticle->fetch(PDO::FETCH_ASSOC);
			
			$articleTitle	= $article['article_title'];
			$articleLink	= $article['article_link'];
			$articleText	= $article['article_text'];
			$publishDate	= $article['publish_date'];
			
			if($_REQUEST['articleType'] == ''){
				$articleType	= $article['article_type'];
			}else{
				$articleType	= $_REQUEST['articleType'];	
			}
			//echo $articleType;
			
			if(strpos($articleLink, 'https') !== false){
				$https = true;	
			}else{
				$https = false;	
			}
			
			$articleLink = str_replace('https://', '', $articleLink);
			$articleLink = str_replace('http://', '', $articleLink);
			if($https == true){
				$articleLink = 'https://'.$articleLink;
			}else{
				$articleLink = 'http://'.$articleLink;
			}
			
			
			?>
            <div class="form-group">
                <label class="control-label">Article Type*</label>
                <select name="article_type" id="article_type2" class="form-control">
                    <?php echo printList($mLink, '12', $articleType);?>
                </select>
            </div>
            <input type="hidden" name="edit_article_id" id="edit_article_id" value="<?php echo $articleID;?>" />
            <?php
					
		}else{
			$articleType	= $_REQUEST['articleType'];
		}
		
		switch($articleType){
			
			case 'blog':
				?>
                <div class="form-group">
                    <label class="control-label">Article Title*</label>
                    <input type="text" class="form-control" name="article_title" value="<?php echo $articleTitle;?>" />
                </div>
                <div class="form-group">
                    <label class="control-label">Blog Link*</label>
                    <input type="text" class="form-control" name="article_link" value="<?php echo $articleLink;?>" />
                </div>
                <div class="form-group">
                    <label class="control-label">Preview Text*</label>
                    <textarea class="form-control" name="article_text" row="3"><?php echo $articleText;?></textarea>
                    <span class="help-block">Copy/Paste First Paragraph</span>
                </div>
                <?php
			break;
			
			case 'article':
			
			break;
			
			case 'ext-article':
				?>
                <div class="form-group">
                    <label class="control-label">Article Title*</label>
                    <input type="text" class="form-control" name="article_title" value="<?php echo $articleTitle;?>" />
                </div>
                <div class="form-group">
                    <label class="control-label">Article Link*</label>
                    <input type="text" class="form-control" name="article_link" value="<?php echo $articleLink;?>" />
                </div>
                <div class="form-group">
                    <label class="control-label">Preview Text*</label>
                    <textarea class="form-control" name="article_text" row="3"><?php echo $articleText;?></textarea>
                    <span class="help-block">Copy/Paste First Paragraph</span>
                </div>
                <?php
			break;
			
			case 'forbes':
				?>
                <div class="form-group">
                    <label class="control-label">Article Title*</label>
                    <input type="text" class="form-control" name="article_title" value="<?php echo $articleTitle;?>" />
                </div>
                <div class="form-group">
                    <label class="control-label">Forbes Link*</label>
                    <input type="text" class="form-control" name="article_link" value="<?php echo $articleLink;?>" />
                </div>
                <div class="form-group">
                    <label class="control-label">Preview Text*</label>
                    <textarea class="form-control" name="article_text" row="3"><?php echo $articleText;?></textarea>
                    <span class="help-block">Copy/Paste First Paragraph</span>
                </div>
                <?php
			break;
			
			case 'seekingalpha':
				?>
                <div class="form-group">
                    <label class="control-label">Article Title*</label>
                    <input type="text" class="form-control" name="article_title" value="<?php echo $articleTitle;?>" />
                </div>
                <div class="form-group">
                    <label class="control-label">Seeking Alpha Link*</label>
                    <input type="text" class="form-control" name="article_link" value="<?php echo $articleLink;?>" />
                </div>
                <div class="form-group">
                    <label class="control-label">Preview Text*</label>
                    <textarea class="form-control" name="article_text" row="3"><?php echo $articleText;?></textarea>
                    <span class="help-block">Copy/Paste First Paragraph</span>
                </div>
                <?php
			break;
				
		}
		
		?>
        <div class="form-group">
            <label class="control-label">Select Publish Date</label><br />
            
            <select name="year">
                <?php
                echo date_list($mLink, 'year', NULL, date('Y'), false);
                ?>
            </select>/
            <select  name="month">
                <?php
                echo date_list($mLink, 'month', NULL, date('m'));
                ?>
            </select>/
            <select name="day">
                <?php
				echo date_list($mLink, 'day', NULL, date('d'));
                ?>
            </select><br />
            <small>YYYY/MM/DD</small>
       	</div><!--form-group-->
        <?php
		
	break;
	
	case 'load-composite-data':
		
		$fundID 	= $_REQUEST['fundID'];
		$aFundID	= explode('-', $fundID);
		$memberID	= $aFundID[0];
		
		$fundName	= get_funds($mLink,$fundID,'fundName');
		$fundSymbol	= get_funds($mLink,$fundID,'fundSymbol');
		$aMember	= get_member($mLink,$memberID, 'all');
		$firstName	= $aMember['firstName'];
		$lastName	= $aMember['lastName'];
		$fullName	= $firstName.'-'.$lastName;	
		
		//print_r($aMember);
		
		$query = "
			SELECT mfc.*, mf.composite_disclosure, exclude
			FROM members_fund_composite AS mfc
			INNER JOIN members_fund AS mf ON mf.fund_id=mfc.fund_id
			WHERE mfc.fund_id=:fund_id
		";
		try{
			$rsComposites = $mLink->prepare($query);
			$aValues = array(
				':fund_id'	=> $fundID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsComposites->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$aComposites 	= array();
		
		
		$currentYear 	= date('Y');
		
		$aYears[$currentYear] = $currentYear;
		
		while($composite = $rsComposites->fetch(PDO::FETCH_ASSOC)){
			
			$compositeDisclosure	= $composite['composite_disclosure'];
			
			$unixDate		= $composite['unix_date'];
			$year			= date('Y', $unixDate);
			$month			= date('m', $unixDate);
			$exclude		= $composite['exclude'];
			$locked			= $composite['locked'];
			
			$aYears[$year] = $year;
			
			$aComposites[$year][$month] = array(
				'unixDate'		=> $unixDate,
				'composite'		=> $composite['composite'],
				'aum'			=> $composite['aum'],
				'exclude'		=> $exclude,
                                'locked'                => $locked
			);
			
		}
		
		
		#build years and months
        foreach ($aYears as $key => $year) {
            $aMTMCompositeData[$year] = array(
                '01' => array('composite' => NULL, 'aum' => NULL),
                '02' => array('composite' => NULL, 'aum' => NULL),
                '03' => array('composite' => NULL, 'aum' => NULL),
                '04' => array('composite' => NULL, 'aum' => NULL),
                '05' => array('composite' => NULL, 'aum' => NULL),
                '06' => array('composite' => NULL, 'aum' => NULL),
                '07' => array('composite' => NULL, 'aum' => NULL),
                '08' => array('composite' => NULL, 'aum' => NULL),
                '09' => array('composite' => NULL, 'aum' => NULL),
                '10' => array('composite' => NULL, 'aum' => NULL),
                '11' => array('composite' => NULL, 'aum' => NULL),
                '12' => array('composite' => NULL, 'aum' => NULL)
            );
        }
		
		foreach ($aComposites as $year => $months) {

            foreach ($months as $month => $fcd) {

                $aMTMCompositeData[$year][$month] = array(
                    
                    'composite' 	=> $fcd['composite'],
                    'aum' 			=> $fcd['aum'],
					'unixDate'		=> $fcd['unixDate'],
					'exclude'		=> $fcd['exclude'],
                                        'locked'                => $fcd['locked']
                );
            }

        }

        krsort($aMTMCompositeData);
		krsort($aYears);
		
		$yearString = implode('|',$aYears);
		
		?>
        <h3 style="margin:0px 0px 10px 0px;padding:0px;">(<?php echo $fundSymbol;?>) <?php echo $fundName;?></h3>
        
        <form action="" method="post" name="composite-data-form" class="composite-data-form">
        
        <input type="hidden" name="years" id="years" value="<?php echo $yearString;?>" />
        <input type="hidden" name="fund_id" id="fund_id" value="<?php echo $fundID;?>" />
        
        <div id="load-composite-errors"></div>
        
        <div class="row" style="margin-bottom:10px;">
        	<div class="col-md-9">
            	
            </div>
            <div class="top-form col-md-3">	
            	<select name="add-year" id="add-year" class="form-control" style="float:right;display:block;">
            		<?php echo date_list($mLink, 'year', NULL, $currentYear, false);?>
            	</select>
                <input type="hidden" name="fund_id" value="<?php echo $fundID;?>" />
    			<input type="hidden" name="full_name" value="<?php echo $fullName;?>" />
            	<input type="hidden" name="fund_symbol" value="<?php echo $fundSymbol;?>" />
                <input type="hidden" name="composite_disclosure" value="<?php echo $compositeDisclosure;?>" />
            	<button type="button" class="btn btn-info btn-sm" style="float:right;display:block;" onclick="addYear('<?php echo $fundID;?>');">Add Year</button>
            </div>
        </div>
        
        
        <table class="table table-bordered table-striped">
            <thead>
            <tr>
                <th style="border-right:2px solid #000000;">Year</th>
                <th style="border-right:2px solid #000000;">Jan</th>
                <th style="border-right:2px solid #000000;">Feb</th>
                <th style="border-right:2px solid #000000;">Mar</th>
                <th style="border-right:2px solid #000000;">Apr</th>
                <th style="border-right:2px solid #000000;">May</th>
                <th style="border-right:2px solid #000000;">Jun</th>
                <th style="border-right:2px solid #000000;">Jul</th>
                <th style="border-right:2px solid #000000;">Aug</th>
                <th style="border-right:2px solid #000000;">Sep</th>
                <th style="border-right:2px solid #000000;">Oct</th>
                <th style="border-right:2px solid #000000;">Nov</th>
                <th style="border-right:2px solid #000000;">Dec</th>
            </tr>
            

            </thead>
            <tbody>

                <?php foreach($aMTMCompositeData as $year=>$months){?>
                    <tr>
                        <td  style="border-right:2px solid #000000;"><?php echo $year;?></td>
                        <?php foreach($months as $month=>$fcd){
								
							if($fcd['composite'] > 0){
								$comStyle = 'style="background:#B5E4BC;border-color:#333;"';
							}elseif($fcd['composite'] < 0){
								$comStyle = 'style="background:#FCABAC;border-color:#333;"';
							}else{
								$comStyle = 'border-color:#333;';
							}
							
							if($fcd['aum'] > 0){
								$aumStyle = 'style="background:#B5E4BC;border-color:#333;"';
							}elseif($fcd['aum'] < 0){
								$aumStyle = 'style="background:#FCABAC;border-color:#333;"';
							}else{
								$aumStyle = 'border-color:#333;';
							}
							
							if($fcd['exclude'] == '1'){
								$excludeCheck = 'checked';
								$tdBg = 'background-color:#f0ad4e;';	
							}else{
								$excludeCheck = '';	
								$tdBg = '';
							}
							?>

								                            
                            	<td  style="border-right:2px solid #000000; <?php echo $tdBg;?>" id="<?php echo $year;?>-<?php echo $month;?>-td">
                                	<div class="input-icon right">
                                    	<i class="icon-percent" style="font-size:10px;color:#333;"></i>
                                		<input type="text" class="form-control"  <?php echo $comStyle;?> name="<?php echo $year;?>-<?php echo $month;?>-com" id="<?php echo $year;?>-<?php echo $month;?>-com" value="<?php echo number_format($fcd['composite'],5);?>" /><br />
<!--                                                <input type="text" class="form-control"  <?php echo $comStyle;?> name="<?php echo $year;?>-<?php echo $month;?>-com" id="<?php echo $year;?>-<?php echo $month;?>-com" value="<?php echo number_format($fcd['composite'],5);?>" <?php echo ($fcd['locked'] == '1' ? "disabled" : "")?> /><br />-->
                                    </div>
                                    <div class="input-icon left">
                                    	<i class="icon-dollar" style="font-size:10px;color:#333;"></i>
                                    	<input type="text"  class="form-control" <?php echo $aumStyle;?> name="<?php echo $year;?>-<?php echo $month;?>-aum" id="<?php echo $year;?>-<?php echo $month;?>-aum" value="<?php echo number_format($fcd['aum'],2);?>" />
<!--                                        <input type="text"  class="form-control" <?php echo $aumStyle;?> name="<?php echo $year;?>-<?php echo $month;?>-aum" id="<?php echo $year;?>-<?php echo $month;?>-aum" value="<?php echo number_format($fcd['aum'],2);?>" <?php echo ($fcd['locked'] == '1' ? "disabled" : "")?>/>-->

                                     </div>
                                    <label class="control-label">
                                    <input type="checkbox"  name="<?php echo $year;?>-<?php echo $month;?>-exclude" id="<?php echo $year;?>-<?php echo $month;?>-exclude" value="1" onClick="showExclude('<?php echo $year;?>-<?php echo $month;?>');" <?php echo $excludeCheck;?>/> Exclude</label>
                                </td>
                                
                            

                        <?php } ?>
                        
                    </tr>

                <?php } ?>





            </tbody>
        </table>
        <input type="submit" value="Save Changes" id="submit-composite-data" class="btn btn-info" />
        </form><!--composite-data-form-->
        
        <hr />
        <h3 syle="margin-top:20px;">Composite Upload</h3>
        
		
        <div id="composite-upload-response">
        
        	<div class="note note-success">
            	<h3>Current File</h3>
                <p><a href="<?php echo $_SESSION['site_root']; ?>/files/disclosures/<?php echo $compositeDisclosure; ?>" target="_blank"><?php echo $compositeDisclosure;?></a></p>
            </div>
        
        </div>
        
        <form enctype="multipart/form-data" action="/process/ajax/admin-managers-processes.php?process=upload-composite" method="post"  name="upload-disclosure-form" class="upload-disclosure-form">
    		<!--<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />-->
    		<div class="form-group">
            <label class="control-label">Select File</label>
            <input name="upfile" id="upfile" type="file"  class="btn btn-default btn-xs"  />
            </div>
            
            <input type="hidden" name="fund_id" value="<?php echo $fundID;?>" />
    		<input type="hidden" name="full_name" value="<?php echo $fullName;?>" />
            <input type="hidden" name="fund_symbol" value="<?php echo $fundSymbol;?>" />
            
            <input type="submit" value="Upload"  class="btn btn-info" />
            
  		</form> 
        <?php
		
		/*echo '<pre>';
		print_r($aMTMCompositeData);
		echo count($aComposites);
		echo '</pre>';*/
		
	break;
	
	case 'add-year':
		
		$aYears 				= explode('|', $_REQUEST['years']);
		$addYear				= $_REQUEST['add-year'];
		$fundID					= $_REQUEST['fund_id'];
		$fullName				= $_REQUEST['full_name'];
		$fundSymbol				= $_REQUEST['fund_symbol'];
		$compositeDisclosure	= $_REQUEST['composite_disclosure'];
		$aMonths				= array('01','02','03','04','05','06','07','08','09','10','11','12');
		
		array_push($aYears, $addYear);
		
		foreach($aYears as $key=>$year){
			$aNewYears[$year] = $year;
		}
		
		$aYears = $aNewYears;
		
		$yearString = implode('|',$aYears);
		?>
        <form action="" method="post" name="composite-data-form" class="composite-data-form">
        
        <input type="hidden" name="years" id="years" value="<?php echo $yearString;?>" />
        <input type="hidden" name="fund_id" id="fund_id" value="<?php echo $fundID;?>" />
        
        <div id="load-composite-errors"></div>
        
        <div class="row" style="margin-bottom:10px;">
        	<div class="col-md-9">
            	
            </div>
            <div class="top-form col-md-3">	
            	<select name="add-year" id="add-year" class="form-control" style="float:right;display:block;">
            		<?php echo date_list($mLink, 'year', NULL, $currentYear, false);?>
            	</select>
            	<button type="button" class="btn btn-info btn-sm" style="float:right;display:block;" onclick="addYear('<?php echo $fundID;?>');">Add Year</button>
            </div>
        </div>
        
        
        <table class="table table-bordered table-striped">
            <thead>
            <tr>
                <th style="border-right:2px solid #000000;">Year</th>
                <th style="border-right:2px solid #000000;">Jan</th>
                <th style="border-right:2px solid #000000;">Feb</th>
                <th style="border-right:2px solid #000000;">Mar</th>
                <th style="border-right:2px solid #000000;">Apr</th>
                <th style="border-right:2px solid #000000;">May</th>
                <th style="border-right:2px solid #000000;">Jun</th>
                <th style="border-right:2px solid #000000;">Jul</th>
                <th style="border-right:2px solid #000000;">Aug</th>
                <th style="border-right:2px solid #000000;">Sep</th>
                <th style="border-right:2px solid #000000;">Oct</th>
                <th style="border-right:2px solid #000000;">Nov</th>
                <th style="border-right:2px solid #000000;">Dec</th>
            </tr>
            

            </thead>
            <tbody>
        <?php
		
		foreach($aYears as $key=>$year){
			?>
            <tr>
            	<td  style="border-right:2px solid #000000;"><?php echo $year;?></td>
            <?php
			foreach($aMonths as $key=>$month){
				
				$composite = str_replace(',','',$_REQUEST[$year.'-'.$month.'-com']);
				$aum = str_replace(',','',$_REQUEST[$year.'-'.$month.'-aum']);
				
				if($composite > 0){
					$comStyle = 'style="background:#B5E4BC;border-color:#333;"';
				}elseif($composite < 0){
					$comStyle = 'style="background:#FCABAC;border-color:#333;"';
				}else{
					$comStyle = 'border-color:#333;';
				}
				
				if($aum > 0){
					$aumStyle = 'style="background:#B5E4BC;border-color:#333;"';
				}elseif($aum < 0){
					$aumStyle = 'style="background:#FCABAC;border-color:#333;"';
				}else{
					$aumStyle = 'border-color:#333;										';
				}
				
				?>
                <td  style="border-right:2px solid #000000;">
                    <div class="input-icon right">
                        <i class="icon-percent" style="font-size:10px; color:#333;"></i>
                        <input type="text" class="form-control" <?php echo $comStyle;?> name="<?php echo $year;?>-<?php echo $month;?>-com" id="<?php echo $year;?>-<?php echo $month;?>-com" value="<?php echo number_format($composite,2);?>" /><br />
                    </div>
                    <div class="input-icon left">
                        <i class="icon-dollar" style="font-size:10px; color:#333;"></i>
                        <input type="text"  class="form-control" <?php echo $aumStyle;?> name="<?php echo $year;?>-<?php echo $month;?>-aum" id="<?php echo $year;?>-<?php echo $month;?>-aum" value="<?php echo number_format($aum,2);?>" />
                    </div>
                </td>
                <?php
				
			}
			echo '</tr>';
			
		}
		?>
        </tbody>
        </table>
        <input type="submit" value="Save Changes" id="submit-composite-data" class="btn btn-info" />
        
        </form><!--composite-data-form-->
        
        <hr />
        <h3 syle="margin-top:20px;">Composite Upload</h3>
        
		
        <div id="composite-upload-response">
        
        	<div class="note note-success">
            	<h3>Current File</h3>
                <p><a href="<?php echo $_SESSION['site_root']; ?>/files/disclosures/<?php echo $compositeDisclosure; ?>" target="_blank"><?php echo $compositeDisclosure;?></a></p>
            </div>
        
        </div>
        
        <form enctype="multipart/form-data" action="/process/ajax/admin-managers-processes.php?process=upload-composite" method="post"  name="upload-disclosure-form" class="upload-disclosure-form">
    		<!--<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />-->
    		<div class="form-group">
            <label class="control-label">Select File</label>
            <input name="upfile" id="upfile" type="file" class="btn btn-default btn-xs" />
            </div>
            
            <input type="hidden" name="fund_id" value="<?php echo $fundID;?>" />
    		<input type="hidden" name="full_name" value="<?php echo $fullName;?>" />
            <input type="hidden" name="fund_symbol" value="<?php echo $fundSymbol;?>" />
            
            <input type="submit" value="Upload" class="btn btn-info" />
            
  		</form> 
        <?php
		
		/*echo '<pre>';
			print_r($aNewYears);
		echo '</pre>';*/
			
	break;

	
	case 'save-composite-data':
	
		$aYears 	= explode('|', $_REQUEST['years']);
		$addYear	= $_REQUEST['add-year'];
		$fundID		= $_REQUEST['fund_id'];
		$aMonths	= array('01','02','03','04','05','06','07','08','09','10','11','12');
		
		#delete existing records
		$query = "
			DELETE FROM members_fund_composite
			WHERE fund_id=:fund_id
		";
		try{
			$rsDELETE = $mLink->prepare($query);
			$aValues = array(
				':fund_id'			=> $fundID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsDELETE->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		echo $error;
		
		array_push($aYears, $addYear);
		
		foreach($aYears as $key=>$year){
			$aNewYears[$year] = $year;
		}
		
		$aYears = $aNewYears;
		
		foreach($aYears as $key=>$year){
			
			foreach($aMonths as $key=>$month){
				
				#dates
				$dateStr 		= $year.'-'.$month.'-15';
				$endOfMonth		= date("Y-m-t", strtotime($dateStr));
				$unixDate		= strtotime($endOfMonth);
				$date			= date("Ymd", $unixDate);
				$dateTime		= date("Y-m-d H:i:s",$unixDate);
				
				//echo $dateStr.' | '.$endOfMonth.' | '.$unixDate,' | '.$date.' | '.$dateTime.'<br />';
				
				$saveComposite 	= false;
				$saveAum		= false;
				
				$composite = str_replace(',','',$_REQUEST[$year.'-'.$month.'-com']);
				$aum = str_replace(',','',$_REQUEST[$year.'-'.$month.'-aum']);
				$exclude = str_replace(',','',$_REQUEST[$year.'-'.$month.'-exclude']);
				
				if($composite != '0.00' && $composite != '' && $composite != NULL){
					
							$saveComposite = true;
							$compositeCalc = ($composite / 100);
							$exclude = NULL;
					
				}elseif($exclude == 1){
					$saveComposite = true;
					$exclude = '1';
					$composite = NULL;
					$compositeCalc = NULL;
				}else{
					$composite = NULL;
				}
				
				
				if($aum != '0.00' && $aum != '' && $aum != NULL){
					$saveAum = true;	
				}else{
					$aum = NULL;	
				}
				
				if($saveAum == true || $saveComposite == true){
					
					$query = "
						INSERT INTO members_fund_composite (
							fund_id,
							date,
							unix_date,
							datetime,
							composite,
							composite_calc,
							aum,
							exclude,
							timestamp
						)VALUES(
							:fund_id,
							:date,
							:unix_date,
							:datetime,
							:composite,
							:composite_calc,
							:aum,
							:exclude,
							UNIX_TIMESTAMP()
						)
					";
					try{
						$rsComposites = $mLink->prepare($query);
						$aValues = array(
							':fund_id'			=> $fundID,
							':date'				=> $date,
							':unix_date'		=> $unixDate,
							':datetime'			=> $dateTime,
							':composite'		=> $composite,
							':composite_calc'	=> $compositeCalc,
							':aum'				=> $aum,
							':exclude'			=> $exclude
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsComposites->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
						
				}
				
			}
			
		}
		
		//echo 'alert-danger';
	break;
	
	case 'upload-composite':
		
		$fileLocation	= '../../files/disclosures/';
		$now			= time();
		$fundID			= $_REQUEST['fund_id'];
		$fundSymbol		= $_REQUEST['fund_symbol'];
		$username		= $_REQUEST['username'];
		$fullName		= $_REQUEST['full_name'];
		$filename		= $fundID.'_'.$fullName.'-'.$fundSymbol.'_composite-disclosure_'.$now.'.';
		$aErrors		= array();
		
		
		header('Content-Type: text/plain; charset=utf-8');
		
		
		try {
			
			// Undefined | Multiple Files | $_FILES Corruption Attack
			// If this request falls under any of them, treat it invalid.
			if (
				!isset($_FILES['upfile']['error']) ||
				is_array($_FILES['upfile']['error'])
			) {
				throw new RuntimeException('Invalid parameters1.');
				//var_dump($_FILES['upfile']('error');
				//$aErrors[] = 'Invalid Parameters.';
			}
		
			// Check $_FILES['upfile']['error'] value.
			switch ($_FILES['upfile']['error']) {
				case UPLOAD_ERR_OK:
					break;
				case UPLOAD_ERR_NO_FILE:
					throw new RuntimeException('No file sent.');
					//$aErrors[] = 'No file sent.';
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					throw new RuntimeException('Exceeded filesize limit.');
					//$aErrors[] = 'Exceeded filesize limit.';
				default:
					throw new RuntimeException('Unknown errors.');
					//$aErrors[] = 'Unknown errors.';
			}
		
			// You should also check filesize here. 
			if ($_FILES['upfile']['size'] > 1000000) {
				//throw new RuntimeException('Exceeded filesize limit.');
				$aErrors[] = 'Exceeded filesize limit.';
			}
		
			// DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
			// Check MIME Type by yourself.
			$finfo = new finfo(FILEINFO_MIME_TYPE);
			if (false === $ext = array_search(
				$finfo->file($_FILES['upfile']['tmp_name']),
				array(
					/*'jpg' => 'image/jpeg',
					'png' => 'image/png',
					'gif' => 'image/gif',*/
					'pdf'	=> 'application/pdf'
				),
				true
			)) {
				//throw new RuntimeException('You can only upload PDF files.');
				$aErrors[] = 'You can only upload PDF files.';
			}
			
			
			$newFileName = $filename.$ext;
			// You should name it uniquely.
			// DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
			// On this example, obtain safe unique name from its binary data.
			if (!move_uploaded_file(
				$_FILES['upfile']['tmp_name'],
				"../../files/disclosures/" . $newFileName
				
			)) {
				//throw new RuntimeException('Failed to move uploaded file.');
				$aErrors[] = 'Failed to move uploaded file.';
			}
		
			
			if(empty($aErrors)){
				#FILE UPLOADED SUCCESSFULLY
				echo '<div class="note note-success"><h3>File is uploaded successfully.</h3><p><a href="'.$_SESSION['site_root'].'/files/disclosures/'.$newFileName.'" target="_blank">'.$newFileName.'</a></p></div>';
				
				$query = "
					UPDATE members_fund
					SET composite_disclosure=:compositeFile
					WHERE fund_id=:fund_id
				";
				try{
					$rsUpdate = $mLink->prepare($query);
					$aValues = array(
						':fund_id'			=> $fundID,
						':compositeFile'	=> $newFileName
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsUpdate->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
			}else{
				
				echo '<div class="alert alert-danger"><ul>';
				foreach($aErrors as $key=>$error){
					echo '<li>'.$error.'</li>';	
				}
				echo '</ul></div>';
				
					
			}
			
		
		} catch (RuntimeException $e) {
		
			echo $e->getMessage();
		
		}
	
	break;
		
}

?>
