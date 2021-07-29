<?php
/*
Marketocracy Inc. | Beta Development
Admin Campaigns

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/admin-campaigns-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/admin-campaigns.js.php
	HTML		- includes/pages/admin-campaigns.php
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

$allowedMembers = '10,11'; //'3,4,10,11

switch($process){
	
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//													Create Campaign Emails
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'create-email-campaign':
		
		$peformanceMonth	= mktime(8,0,0,$_REQUEST['month'],1,$_REQUEST['year']);
		$managers			= $_REQUEST['managers'];
		
		#build member array
		if($managers == 'all'){
			
			$query = "
				SELECT ms.member_id, m.name_first, m.name_last, m.username, ms.product_id
				FROM `members_subscriptions` as ms
				JOIN members as m ON m.member_id=ms.member_id
				WHERE ms.product_id IN (".$allowedMembers.") and ms.active='1'
			";
				
		}else{
			
			$query = "
				SELECT ms.member_id, m.name_first, m.name_last, m.username, ms.product_id
				FROM `members_subscriptions` as ms
				JOIN members as m ON m.member_id=ms.member_id
				WHERE ms.product_id IN (".$allowedMembers.") and ms.active='1' AND ms.member_id=".$managers."
			";
			
		}
		
		try{
			$rsMembers = $mLink->prepare($query);
			$aValues = array(	
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsMembers->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
					
		echo $preparedQuery;
						
		while($memberInfo = $rsMembers->fetch(PDO::FETCH_ASSOC)){	
		
			$aMembers[$memberInfo['member_id']] = array(
				'name_first'	=> $memberInfo['name_first'],
				'name_last'		=> $memberInfo['name_last'],
				'username'		=> $memberInfo['username'],
				'product_id'	=> $memberInfo['product_id']
			);		
			
		}//end While
		//end build member array
		
		
		
		echo '<div class="alert alert-danger"><ul>';
		
		echo '<pre>';
		print_r($aMembers);
		echo '</pre>';
		
		foreach($aErrors as $key=>$error){
			echo '<li>'.$error.'</li>';	
		}
		echo '</ul></div>';	
		
		
		
		
		//No errors start processing
		if(empty($aErrors)){
			
			
			foreach($aMembers as $memberID=>$memberData){
				
				$query = "
					INSERT INTO email_campaigns (
						member_id,
						camp_type,
						camp_status,
						camp_name,
						temp_id,
						email_title,
						email_subject,
						asOfDate,
						timestamp
					)VALUES(
						:member_id,
						:camp_type,
						:camp_status,
						:camp_name,
						:temp_id,
						:email_title,
						:email_subject,
						:asOfDate,
						unix_timestamp()
					)
				";	
				try{
					$rsCampaign = $mLink->prepare($query);
					$aValues = array(
						':member_id'		=> $memberID,
						':camp_type'		=> 'monthly_perf',
						':camp_status'		=> 'draft',
						':camp_name'		=> $memberData['name_first'].' '.$memberData['name_last'].'\'s '.date('F Y', $peformanceMonth).' Performance Report - ',
						':temp_id'			=> '22',
						':email_title'		=> $memberData['name_first'].' '.$memberData['name_last'].'\'s '.date('F Y', $peformanceMonth).' Performance Report - ',
						':email_subject' 	=> $memberData['name_first'].' '.$memberData['name_last'].'\'s '.date('F Y', $peformanceMonth).' Performance Report - ',
						':asOfDate'			=> date('Ymt', $peformanceMonth)
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsCampaign->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}	
			}
				
			
		}else{
			
			echo '<div class="alert alert-danger"><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';	
			}
			echo '</ul></div>';	
		}
		
		/*echo '<pre>';
		echo 'note-danger';
		print_r($campaignStatus);
		print_r($post);
		echo '</pre>';*/
	break;
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//													Send Campaign
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'send-campaign':
		
		
		
		//echo 'alert-danger';
		
		$campID = $_REQUEST['camp_id'];
		
		$query = "
			SELECT ct.*, el.*
			FROM ".$_SESSION['email_campaigns_table']." ct, email_lists el
			WHERE ct.camp_id=:camp_id AND ct.recipient_list=el.list_id
		";
		
		try{
			$rsCampaign = $mLink->prepare($query);
			$aValues = array(
				':camp_id' 	=> $campID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsCampaign->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		//$aErrors[] = 'test';
		//$aErrors[] = $error;
		//$aErrors[] = $preparedQuery;
		
		$camp = $rsCampaign->fetch(PDO::FETCH_ASSOC);
		
		$campName		= $camp['camp_name'];
		$memberID		= $camp['member_id'];
		$list			= $camp['recipient_list'];
		$emailTitle		= $camp['email_title'];
		$emailSubject	= $camp['email_subject'];
		$emailHeadline	= $camp['email_headline'];
		$emailBody		= $camp['email_body'];
		$emailClosing	= $camp['email_closing'];
		$scheduleDate	= $camp['schedule_timestamp'];
		$tempID			= $camp['temp_id'];	
		$listType		= $camp['list_type'];
		$listQuery		= $camp['list_query'];
		$listMemIds		= $camp['list_member_ids'];
		$getListID		= $camp['list_id'];
		
		//$aErrors[] = $listType.' | '.$listQuery;
		
		//GET THE USERNAME OF THE MEMBER
		$query = "
			SELECT username
			FROM members
			WHERE member_id=:member_id
		";
		try{
			$rsMember = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $memberID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsMember->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$member = $rsMember->fetch(PDO::FETCH_ASSOC);
		$username	= $member['username'];
		
		//$aErrors[] = $tempID;
		
		//GET TEMPLATE INFO
		$query = "
			SELECT *
			FROM email_templates
			WHERE temp_id=:temp_id
		";
		try{
			$rsTemplate = $mLink->prepare($query);
			$aValues = array(
				':temp_id'	=> $tempID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsTemplate->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$template = $rsTemplate->fetch(PDO::FETCH_ASSOC);
		
		$templateHTML	= $template['html_source'];
		$templateFrom	= $template['headers_from'];
		
		
		/*switch($list){
		
			case 'f-f':
				
				$query = "
					SELECT *
					FROM members_fund_tracking
					WHERE member_id=:member_id AND tracker_code IS NOT NULL AND unsubscribe='0' AND manager_updates='1'
				";
				
			break;
			
			case 'trackers':
				$query = "
					SELECT *
					FROM members_fund_tracking
					WHERE member_id=:member_id AND unsubscribe='0' AND manager_updates='1'
				";
			break;
		
		}*/
		
		switch($listType){
			case "query":
				$query = $listQuery;
			
			
			
			try{
				$rsGetList = $mLink->prepare($query);
				$aValues = array();
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetList->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			//$aErrors[] = $error;
			$aRecipients = array();
			while($list = $rsGetList->fetch(PDO::FETCH_ASSOC)){
				
				/*$aRecipients[$list['email']] = array(
					'name_first'		=> $list['first_name'],
					'name_last'			=> $list['last_name'],
					'email_code'		=> $list['code']
				);*/
				
				$aRecipients[$list['member_id']] = array(
					'email_address'		=> $list['email'],
					'recepient_vars'	=> array(
						'name_first'	=> $list['name_first']
					)
				);
				
				
				
			}
			
			//$aErrors[] = count($aRecipients);
			//$aErrors[] = $aRecipients[2]['email_address'];
			//$aErrors[] = $aRecipients[2]['recepient_vars']['name_first'];
			break;
			
			case "contacts":
				
				$query = "
					SELECT *
					FROM email_list_contacts
					WHERE list_id=:list_id AND active='1' AND unsub_list='0' AND unsub_all='0'
				";
				try{
					$rsGetList = $mLink->prepare($query);
					$aValues = array(
						':list_id'	=> $getListID
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsGetList->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				//$aErrors[] = $error;
				$aRecipients = array();
				while($list = $rsGetList->fetch(PDO::FETCH_ASSOC)){
					
					/*$aRecipients[$list['email']] = array(
						'name_first'		=> $list['first_name'],
						'name_last'			=> $list['last_name'],
						'email_code'		=> $list['code']
					);*/
					
					$aRecipients[$list['email']] = array(
						'email_address'		=> $list['email'],
						'recepient_vars'	=> array(
							'name_first'	=> $list['name_first'],
							'name_last'		=> $list['name_last']
						)
					);
					
					
					
				}
				
			break;	
		}
		
		
		
		
		//UPDATE CAMPAIGN
		$query = "
			UPDATE email_campaigns
			SET camp_status='sending'
			WHERE camp_id=:camp_id
		";
		try{
			$rsUpdateCampaign = $mLink->prepare($query);
			$aValues = array(
				':camp_id' 	=> $campID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdateCampaign->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		//$aErrors[] = $error;
		//END UPDATE CAMPAIGN
		
		$numberRecipients = count($aRecipients);
		
		/*$aRecipients['ken@marketocracy.com'] = array(
			'name_first'		=> 'Ken',
			'name_last'			=> 'Kam',
			'email_code'		=> 'X'
		);
		$aRecipients['mccarthyhe@gmail.com'] = array(
			'name_first'		=> 'Brandon',
			'name_last'			=> 'McCarthy',
			'email_code'		=> 'X'
		);*/
		
		$aEmail = array(
			'camp_id'			=> $campID,
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
		
		$campaignStatus = sendEmail($mLink, $aEmail, $aEmailVars, false, true);
		
		
		
		//UPDATE CAMPAIGN INFO
		$query = "
			UPDATE email_campaigns
			SET camp_status='sent', sent_timestamp=UNIX_TIMESTAMP(), modified=UNIX_TIMESTAMP(), recipients=:recipients
			WHERE camp_id=:camp_id
		";
		try{
			$rsUpdateCampaign = $mLink->prepare($query);
			$aValues = array(
				':camp_id' 		=> $campID,
				':recipients'	=> $numberRecipients
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdateCampaign->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		//$aErrors[] = $error;
		//END UPDATE CAMPAIGN
		
		
		if(!empty($aErrors)){
			echo '<div class="alert alert-danger"><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';	
			}
			echo '</ul></div>';	
		}
		
	break;
	
	case 'load-edit-email':
		
		$campID = $_REQUEST['campID'];
		
		$query = "
			SELECT * FROM ".$_SESSION['email_campaigns_table']."
			WHERE camp_id=:camp_id
		";
		try{
			$rsGetCamp = $mLink->prepare($query);
			$aValues = array(
				':camp_id'	=> $campID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetCamp->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$camp = $rsGetCamp->fetch(PDO::FETCH_ASSOC);
		
		$sendList = $camp['recipient_list'];
		
		?>
        <form action="" method="post" name="edit-campaign-form" class="edit-campaign-form">
            <div class="modal-body">
                    <div id="save-campaign-errors"></div>
                    <div class="form-body">
                        
                        <h4>Campaign Info</h4>
                        <div class="form-group">
                            <label class="control-label">Campaign Name:*</label>
                            <input type="text" class="form-control" name="camp_name" value="<?php echo $camp['camp_name'];?>" />
                        </div><!--form-group-->
                        
                        <div class="form-group">
                            <label class="control-label">Send To:*</label>
                            <select name="sent-to" class="form-control">
                                <option value='xx'>Select An Email List</option>
                                <?php
                                $query = "
                                    SELECT *
                                    FROM email_lists
                                ";
                                try{
                                    $rsEmailList = $mLink->prepare($query);
                                    $aValues = array();
                                    $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                    $rsEmailList->execute($aValues);
                                }
                                
                                catch(PDOException $error){
                                    // Log any error
                                    file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                }
                                while($list = $rsEmailList->fetch(PDO::FETCH_ASSOC)){
                                    
									if($sendList == $list['list_id']){
										$selectList = 'selected';	
									}else{
										$selectList = '';		
									}
									
									echo '<option value="'.$list['list_id'].'" '.$selectList.'>'.$list['list_name'].'</option>';	
                                }
                                ?>
                            </select>
                        </div>
                    
                        <div class="form-group">
                            <label class="control-label">Select Template:*</label>
                            <select name="email-template" class="form-control" id="select-template">
                                <option value="x">Choose Template</option>
                                <?php
                                $query = "
                                    SELECT temp_title, temp_id
                                    FROM ".$_SESSION['email_templates_table']."
                                    WHERE camp_type='standard_campaign'
                                ";
                                try{
                                    $rsTemplate = $mLink->prepare($query);
                                    $aValues = array();
                                    $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                    $rsTemplate->execute($aValues);
                                }
                                
                                catch(PDOException $error){
                                    // Log any error
                                    file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                }
                                while($template = $rsTemplate->fetch(PDO::FETCH_ASSOC)){
									
									if($camp['temp_id'] == $template['temp_id']){
										$showSelected = 'selected';	
									}else{
										$showSelected = '';
									}
									
                                    echo '<option value="'.$template['temp_id'].'" '.$showSelected.'>'.$template['temp_title'].' </option>';	
                                }
                                ?>
                            </select>
                        </div>
                        <hr />
                        <h4>Email Info</h4>
                        <div id="load-template-options"></div>
                        
                        <div class="form-group">
                            <label class="control-label">Email Title*</label>
                            <input type="text" class="form-control" name="email_title" value="<?php echo $camp['email_title'];?>" />
                        </div>
                        <div class="form-group">
                            <label class="control-label">Subject Line*</label>
                            <input type="text" class="form-control" name="email_subject" value="<?php echo $camp['email_subject'];?>" />
                        </div>
                        <div class="form-group">
                            <label class="control-label">Headline</label>
                            <input type="text" class="form-control" name="email_headline" value="<?php echo $camp['email_headline'];?>" />
                        </div>
                        <div class="form-group">
                            <label class="control-label">Body*</label>
                            <textarea type="text" class="form-control email-body" name="email_body" rows="10" id="email-body-<?php echo $campID;?>"><?php echo $camp['email_body'];?></textarea>
                        </div>
                        
                       	<input type="hidden" name="camp_id" value="<?php echo $campID;?>" />
                        
                        <a href="javascript:void(0);" onclick="toggleID('format-help-<?php echo $campID;?>');">Toggle Formatting Help</a>
                        <div class="note note-info" id="format-help-<?php echo $campID;?>" style="display:none;">
                            <h3>Formatting Help</h3>
                            <table width="100" cellpadding="5" cellspacing="5"  class="table">
                                            <thead>
                                                <tr>
                                                    <td><strong>Code</strong></td>
                                                    <td><strong>Result</strong></td>
                                                    <td><strong>Description</strong></td>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                <tr>
                                                    <td>[Link Text](http://example.com)</td>
                                                    <td><a href="http://example.com" target="_blank">Link Text</a></td>
                                                    <td>Trackable Link</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        
                                        
                                        <h3>Email Tags</h3>
                                        <table width="100" cellpadding="5" cellspacing="5"  class="table">
                                            <thead>
                                                <tr>
                                                    <th><strong>Tag</strong></th>
                                                    <th><strong>Result</strong></th>
                                                    <th><strong>Description</strong></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>[NAME_FIRST]</td>
                                                    <td>Brandon</td>
                                                    <td align="left">Adds recipients first name. Brackets must be in all caps.</td>
                                                </tr>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                        </div>
                    
                        
                        <hr />
                                    <h4>Testing</h4>
                                    
                                	<div class="form-group">
                                        <label class="control-label">Send Test
                                        <input id="send-test-check" type="checkbox" class="form-control" name="send_test" value="1" /></label>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Test Email Address</label>
                                        <input type="text" class="form-control" name="test_email" value="Ken:kkam@mac.com" />
                                    </div>
                        
                    </div><!--form-body-->
                
            </div><!--modal-body-->
            
            <div class="modal-footer">
                <span id="email-processing-btn">
                    <input type="submit" value="Save Changes" id="save-email-changes-btn" class="btn btn-info" />
                </span>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
        
        </form><!--send email-->
        <?php
		
	break;
	
	case 'load-email-preview':
		
		$campID = $_REQUEST['campID'];
		
		
		$query = "
			SELECT ect.*, ett.html_source
			FROM ".$_SESSION['email_campaigns_table']." as ect
			INNER JOIN ".$_SESSION['email_templates_table']." as ett ON ett.temp_id=ect.temp_id
			WHERE ect.camp_id=:camp_id
		";
		try{
			$rsGetCamp = $mLink->prepare($query);
			$aValues = array(
				':camp_id'	=> $campID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetCamp->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		
		$camp = $rsGetCamp->fetch(PDO::FETCH_ASSOC);
		
		
		$htmlSource 	= $camp['html_source'];
		$emailTitle		= $camp['email_title'];
		$emailHeadline	= $camp['email_headline'];
		$emailBody		= $camp['email_body'];
		$emailClosing	= NULL/*$camp['email_closing']*/;
		
		$preview	= true;
		
		include('../../includes/email/templates/'.$htmlSource);
		
		echo $message;
		
	break;
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//													Save Email Campaign Changes
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'save-email-campaign':
		
		$campID			= $_REQUEST['camp_id'];
		$campName 		= $_REQUEST['camp_name'];
		$sendTo			= $_REQUEST['sent-to'];
		$templateID		= $_REQUEST['email-template'];
		$campType		= 'standard_campaign';
		$campStatus		= 'draft';
		
		$emailTitle		= $_REQUEST['email_title'];
		$emailSubject	= $_REQUEST['email_subject'];
		$emailHeadline	= $_REQUEST['email_headline'];
		$emailBody		= $_REQUEST['email_body'];
		
		$sendTest		= $_REQUEST['send_test'];
		$testEmail		= $_REQUEST['test_email'];
		
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

		/*$emailBody = preg_replace_callback(
			'~\[([^\]]+)\]\(([^)]+)\)~',
			function ($m) {
				return '<a href="'.$_SESSION['base_url'].'includes/email/tracking.php?track=subscription~[TRACK_ID]~'.$m[2].'~[TRACKER_CODE]~[EMAIL_ADDRESS]" target="_blank">'.substr($m[1],0,80).'</a>';
			},
			$emailBody
		);*/

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
				WHERE camp_name=:camp_name AND member_id=:member_id AND camp_id<>:camp_id
			";
			try{
				$rsGetCamp = $mLink->prepare($query);
				$aValues = array(
					':camp_name'	=> strtolower($campName),
					':member_id'	=> $_SESSION['member_id'],
					':camp_id'		=> $campID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetCamp->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$rowCount = $rsGetCamp->rowCount();
			
			if($rowCount > 0){
				$aErrors[] = 'Campaign Name already in use. Please choose a different campaign name.';	
			}
		}
		
	//	$aErrors[] = $sendTo;
		
		if($sendTo == 'xx'){
			$aErrors[]	= 'Please select an email list.';
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
		
		/*if(empty($emailHeadline)){
			$aErrors[]	= 'Please provide a headline for this email.';
		}*/
		
		if(empty($emailBody)){
			$aErrors[]	= 'Please provide content in the body for this email.';
		}
		
		
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
					<p>You must unclick "Send Test" checkbox to save the campaign.</p>
				</div>';
			}else{
			
				$query = "
					UPDATE ".$_SESSION['email_campaigns_table']."
					SET camp_name=:camp_name, 
						recipient_list=:recipient_list, 
						temp_id=:email_template,
						email_title=:email_title,
						email_subject=:email_subject,
						email_headline=:email_headline,
						email_body=:email_body,
						modified=UNIX_TIMESTAMP()
					WHERE camp_id=:camp_id
				";
				try{
					$rsGetCamp = $mLink->prepare($query);
					$aValues = array(
						':camp_id'			=> $campID,
						':camp_name'		=> $campName,
						':recipient_list'	=> $sendTo,
						':email_template'	=> $templateID,
						':email_title'		=> $emailTitle,
						':email_subject'	=> $emailSubject,
						':email_headline'	=> $emailHeadline,
						':email_body'		=> $emailBody
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsGetCamp->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
			}
			
			
			
			/*echo '<div class="alert alert-danger">';
			echo $error;
			echo $preparedQuery;
			echo '</div>';*/
			
		}else{
			
			echo '<div class="alert alert-danger"><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';	
			}
			echo '</ul></div>';	
		}
		
		
	break;
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
		
}

?>