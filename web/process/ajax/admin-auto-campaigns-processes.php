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


switch($process){
	
	
	case 'test-auto-email':
		
		
		$memberID 	= $_REQUEST['memberID'];
		$campID		= $_REQUEST['campID'];
		
		$results 	= sendAutoEmail($mLink, $memberID, $campID);
		
		echo '<h4>Test Results</h4><pre>';
		
		print_r($results);
		
		echo '</pre>';
	break;
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//													Load Emails
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'load-emails':
		
		$memberID 		= $_REQUEST['memberID'];
		$aEmailLists	= getEmailLists($mLink);
		
		$query = "
			SELECT *
			FROM ".$_SESSION['email_auto_campaigns_table']."
			WHERE active='1'
		";
		try{
			$rsGetCamp = $mLink->prepare($query);
			$aValues = array();
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetCamp->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$cnt = 0;
		$aJson = array();
		while($camp = $rsGetCamp->fetch(PDO::FETCH_ASSOC)){
		
			$cnt++;	
			
			$campID			= $camp['camp_id'];
			$scheduled 		= $camp['schedule_timestamp'];
			$campStatus		= $camp['camp_status'];
			
			$listID			= $camp['recipient_list'];
			$numRecipients	= $camp['recipients'];
			
			if($scheduled == NULL){
				$scheduled = 'N/A';
			}else{
				$scheduled = date('m/d/Y h:i a', $scheduled);
			}	
			
			$timestamp = $camp['timestamp'];
			
			switch($campStatus){
				
				case 'complete':
					$actionBtns = '
						
						<button type="button" data-toggle="modal" data-target="#report" onclick="loadCampaignReport(\''.$campID.'\');" class="btn btn-warning btn-sm"><i class="icon-file"></i>&nbsp;&nbsp;View Report</button>
					';
				break;
				
				case 'sent':
					$actionBtns = '
						
						<button type="button" data-toggle="modal" data-target="#report" onclick="loadCampaignReport(\''.$campID.'\');" class="btn btn-warning btn-sm"><i class="icon-file"></i>&nbsp;&nbsp;View Report</button>
						<button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#preview-email" onclick="loadEmailPreview(\''.$campID.'\');"><i class="icon-eye-open"></i> View Email</button>
						<button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#edit-email-campaign" onclick="loadEditEmail(\''.$campID.'\');"><i class="icon-pencil"  ></i> Edit</button>
						<button type="button" class="btn btn-warning btn-sm" onclick="testEmail(\'2\',\''.$campID.'\');">Test</button> 
					';
				break;
				
				case 'draft':
					$actionBtns = '
						<button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#preview-email" onclick="loadEmailPreview(\''.$campID.'\');"><i class="icon-eye-open"></i> Preview</button>
						<button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#edit-email-campaign" onclick="loadEditEmail(\''.$campID.'\');"><i class="icon-pencil"  ></i> Edit</button> 
						<button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#send-campaign" onclick="loadSendEmail(\''.$campID.'\');"><i class="icon-arrow-right"></i> Send</button>
					';
				break;
				
				case 'sending':
					$actionBtns = '
						<button type="button" class="btn btn-default btn-sm" disabled>Sending...</button>
					';
				break;
				
			}
			
			echo '
				<tr>
					<td>'.$camp['camp_id'].'</td>
					<td>'.$camp['camp_name'].'</td>
					<td>'.$camp['email_subject'].'</td>
					<td>'.$aEmailLists[$listID]['listName'].'</td>
					<td>'.date('m/d/Y h:i a', $timestamp).'</td>
					<td>'.$numRecipients.'</td>
					<td>'.$actionBtns.'</td>
				</tr>
			';
			
			//$aJson[] = array($cnt, $camp['camp_name'], $camp['email_subject'], $scheduled, date('m/d/Y h:i a', $timestamp), ucfirst($camp['camp_status']), $actionBtns);
			
		}
		
		/*echo '{ "data": ';
		echo json_encode($aJson);
		echo '}';*/
		
		
		
		
		/*echo '
			<tr>
				<td colspan="7">'.$error.'</td>
			</tr>
		';*/
	break;
	
	case 'load-total-aum':
		
		
		
	break;
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//													View Campaign Report
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'view-campaign-report':
	
		$campID = $_REQUEST['campID'];
		
		$query = "
			SELECT et.*, m.name_first, m.name_last, m.username 
			FROM email_auto_tracking AS et
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
			
			$aList[] = array(
				'member_id'		=> $tracker['member_id'],
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
					foreach($aList as $key=>$memberInfo){
						
						$memberID = $memberInfo['member_id'];
						
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
	//													Load SEND Email 
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'load-send-email':
		
		$campID = $_REQUEST['campID'];
		
		?>
        <form action="" method="post" name="send-campaign-form" class="send-campaign-form">
            <div class="modal-body">
                
                <div id="send-campaign-errors"></div>
                
                <div class="form-group">
                    <label class="control-label">When to send:</label>
                    <select class="form-control">
                        <option value="now">Now</option>
                        <!--<option value="schedule">Schedule For Date/Time</option>-->
                    </select>
                </div>
                <input type="hidden" name="camp_id" value="<?php echo $campID;?>" />
            
            </div><!--modal-body-->
                
            <div class="modal-footer">
                <span id="send-campaign-processing-btn">
                    <input type="submit" value="Send Campaign" id="send-campaign-btn" class="btn btn-info" />
                </span>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </form>
        <?php
		
	break;
	
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//													Send Campaign
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'send-campaign':
		
		
		
		//echo 'alert-danger';
		
		$campID = $_REQUEST['camp_id'];
		
		$query = "
			SELECT ct.*, el.*
			FROM ".$_SESSION['email_auto_campaigns_table']." ct, email_lists el
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
		
		if($listType == 'query'){
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
			SELECT * FROM ".$_SESSION['email_auto_campaigns_table']."
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
			FROM ".$_SESSION['email_auto_campaigns_table']." as ect
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
				FROM ".$_SESSION['email_auto_campaigns_table']."
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
					UPDATE ".$_SESSION['email_auto_campaigns_table']."
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
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//													Create Campaign Emails
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'create-email-campaign':
	
		$post 			= $_POST;
		$memberID		= $_SESSION['member_id'];
		$campName 		= $_REQUEST['camp_name'];
		$sendTo			= $_REQUEST['sent-to'];
		$templateID		= $_REQUEST['email-template'];
		$campType		= 'auto_campaign';
		$campStatus		= 'draft';
		
		$emailTitle		= $_REQUEST['email_title'];
		$emailSubject	= $_REQUEST['email_subject'];
		$emailHeadline	= $_REQUEST['email_headline'];
		$emailBody		= $_REQUEST['email_body'];
		//$emailClosing	= $_REQUEST['email_closing'];
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
				FROM ".$_SESSION['email_auto_campaigns_table']."
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
		
		/*if(empty($emailHeadline)){
			$aErrors[]	= 'Please provide a headline for this email.';
		}*/
		
		if(empty($emailBody)){
			$aErrors[]	= 'Please provide content in the body for this email.';
		}
		
		/*if(empty($emailClosing)){
			$aErrors[]	= 'Please provide a closing for this email.';
		}*/
		
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
				);
				
				$query = "
					INSERT INTO ".$_SESSION['email_auto_campaigns_table']." (
						active,
						recipient_list,
						camp_type,
						camp_name,
						camp_status,
						temp_id,
						email_title,
						email_subject,
						email_headline,
						email_body,
						timestamp
						
					)VALUES(
						'1',
						:list,
						:camp_type,
						:camp_name,
						'sent',
						:temp_id,
						:email_title,
						:email_subject,
						:email_headline,
						:email_body,
						UNIX_TIMESTAMP()
					)
				";
				try{
					$rsGetCamp = $mLink->prepare($query);
					$aValues = array(
						':list'				=> $sendTo,
						':camp_type'		=> $campType,
						':camp_name'		=> $campName,
						':temp_id'			=> $tempID,
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
	//													Create Campaign Emails
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'create-email-campaign2':
	
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
				FROM ".$_SESSION['email_auto_campaigns_table']."
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
	
	
		
}

?>