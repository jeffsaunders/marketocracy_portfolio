<?php
//Marketocracy Inc.
//Support System Processing scripts

//Start Session
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

// Connect to DB
require_once("../../../secure/dbconnect.php");

// Load System Wide Functions
require_once("../../includes/system-functions.php");

// Get Process from URL
$process = $_REQUEST['process'];


$caAssignDefault = '27'; //<-- Marty's member ID

if($process == 'update-ticket'){
	
	$ticketID 		= $_REQUEST['ticket_id'];
	$type			= $_REQUEST['ticket'];
	$subType		= $_REQUEST['problem_type'];
	$stockTicker	= strtoupper($_REQUEST['stock_ticker']);
	$status			= $_REQUEST['ticket_status'];
	$assigned		= $_REQUEST['assigned'];
	$priority		= $_REQUEST['priority'];
	$oldAssigned	= $_REQUEST['old_assigned'];
	$fundSymbols	= implode('|',$_REQUEST['fund_symbols']);
	
	$query = "
		UPDATE ".$_SESSION['support_ticket_table']."
		SET assigned_to=:assigned, ticket_type=:type, problem_type=:subType, stock_ticker=:stock_ticker, ticket_status=:status, priority=:priority, fund_tickers=:fund_symbols
		WHERE ticket_id=:ticket_id
	";
	
	try{
		$rsUpdateTicket = $mLink->prepare($query);
		$aValues = array(
			':assigned' 		=> $assigned,
			':type'				=> $type,
			':subType'			=> $subType,
			':stock_ticker'		=> $stockTicker,
			':status'			=> $status,
			':priority'			=> $priority,
			':ticket_id'		=> $ticketID,
			':fund_symbols'		=> $fundSymbols
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsUpdateTicket->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	//echo $preparedQuery;
	//echo $error;
	
	if($oldAssigned != $assigned){
		
		//START NOTIFICATION	
		$notificationID	= "08-003";
		$memberID2		= $assigned;
		//Custom notification
		$notification	= "".get_member($mLink, $_SESSION['member_id'], "full name")." has assigned you ticket: ".$ticketID.".";
		$link			= "?page=08-01-cc-903&ticket=".$ticketID."";
		
		add_notification($mLink, $notificationID, $memberID2, $notification, $link);
		//END NOTIFICATION
			
	}
	
	//echo $ticketID.' | '.$type.' | '.$subType.' | '.$stockTicker.' | '.$status.' | '.$assigned.' | '.$priority;
	
	header('Location: /?page=08-01-cc-903&ticket='.$ticketID);
	
	
		
}

//+---------------------------------------------------------------------------------------------------------+
//|								Build Lists for support ticket - PROCESS 1									|
//+---------------------------------------------------------------------------------------------------------+
if($process == "1"){
	$listID = $_REQUEST['listID'];
	
	$query = "
		SELECT list_name
		FROM ".$_SESSION['system_lists_table']."
		WHERE list_id=:list_id
	";
	
	try{
		$rsListOptions = $mLink->prepare($query);
		$aValues = array(
			':list_id' 	=> $listID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsListOptions->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$options = $rsListOptions->fetch(PDO::FETCH_ASSOC);
	?>
    <div class="form-group">
        <label  class="control-label"><?php echo $options['list_name']; ?> *</label>
        <select name="problem_type" id="problem_type" class="form-control">
            <?php
            
            $query = "
                SELECT value, label
                FROM ".$_SESSION['lists_options_table']." 
                WHERE list_id=:list_id
                ORDER BY sequence ASC 
            ";
            
            //Fund Symbols
            try{
                $rsListOptions = $mLink->prepare($query);
                $aValues = array(
                    ':list_id' 	=> $listID
                );
                $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                $rsListOptions->execute($aValues);
            }
            catch(PDOException $error){
                // Log any error
                file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
            }
            
            while($options = $rsListOptions->fetch(PDO::FETCH_ASSOC)) {
                if($options['disabled'] == "1"){
                    $disabled = "disabled";	
                }else{
                    $disabled = "";	
                }
				
				/*if($_REQUEST['subType'] == 'feedback'){
					$selected = 'selected';	
				}else{
					$selected = '';	
				}*/
				
				if($_REQUEST['subType'] == $options['value']){
					$selected = 'selected';	
				}else{
					$selected = '';	
				}
				//echo $options['value'];echo'|';echo $_REQUEST['subType'];
                echo '<option value="'.$options['value'].'" '.$disabled.' '.$selected.'>'.$options['label'].'</option>';
            
            }
            ?>
        </select>
    </div><!--form-group-->
	<?php
	if($listID == "2"/*Corporate Action*/){
		?>
    	<div class="form-group">
            <label  class="control-label">Stock Ticker *</label>
            <input type="text" name="stock_ticker" id="stock_ticker" class="form-control" placeholder="AAPL" value="<?php echo $_REQUEST['stock'];?>" style="width:100px;text-transform:uppercase;">
        </div>
    	<?php	
	}
	
}//end process 1: Select Type List

//+---------------------------------------------------------------------------------------------------------+
//|								CREATE NEW SUPPORT TICKET - PROCESS 2										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "2"){
	
	//START FROM VALIDATION
	$error_list = array();
	
	switch($_REQUEST['ticket']){
		case "2": $assignTo = $caAssignDefault;break;	
		default: $assignTo = "";break;
	}
	
	if($_REQUEST['ticket'] == "2"){ //CA
		if($_REQUEST['problem_type'] == ""){
			$error_list[] = "Please provide <strong>Corporate Action Type</strong>";	
		}
	}else{
		if($_REQUEST['problem_type'] == ""){
			$error_list[] = "Please provide <strong>Problem Type</strong>";	
		}
	}
	
	if($_REQUEST['ticket'] == "2"){ //CA
		if(empty($_REQUEST['stock_ticker'])){
			$error_list[] = "Please Provide <strong>Stock Ticker</strong>";	
		}
	}
	
	
	if(empty($_REQUEST['subject'])) {
		$error_list[] = "Please provide <strong>Ticket Subject</strong>";
		$fundSymbolsError = true;
	}
	
	if(empty($_REQUEST['description'])) {
		$error_list[] = "Please provide <strong>Description</strong>";
		$tickerSymbolError = true;
	}
	
	
	
	if(empty($error_list)) {
		$fundSymbols = "";
		//Turn fund symbols array into "|" delimited for storage in DB
		if($_REQUEST['fund_symbols'] != ""){
			$fundSymbols = implode("|", $_REQUEST['fund_symbols']);
		}
		
		if(empty($_REQUEST['stock_ticker'])){
			$stockTicker = "";	
		}else{
			$stockTicker = strtoupper($_REQUEST['stock_ticker']);	
		}
		
		//INSERT Support Ticket Into Database
			//(SELECT label FROM system_lists_options WHERE list_id='1' AND value=:ticket_type), <-- was used for inserting text value for ticket type
			
		//Check to see if user submitted Ticket Anonymously
		if($_REQUEST['anonymous'] == "on"){
			$column 		= "anonymous,";
			$columnValue 	= "'1',";	
		}else{
			$column 		= "";
			$columnValue 	= "";		
		}
			
		$query = "
			INSERT INTO ".$_SESSION['support_ticket_table']." (
				member_id,
				ticket_type,
				ticket_status,
				ticket_subject,
				problem_type,
				description,
				additional_comments,
				stock_ticker,
				fund_tickers,
				priority,
				opened,
				".$column."
				timestamp,
				assigned_to
			) VALUE (
				:member_id,
				:ticket_type,
				:ticket_status,
				:ticket_subject,
				:problem_type,
				:description,
				:comments,
				:stock_ticker,
				:fund_tickers,
				:priority,
				UNIX_TIMESTAMP(),
				".$columnValue."
				UNIX_TIMESTAMP(),
				:assigned_to
			)
		";
		
		try{
			$rsAddTicket = $mLink->prepare($query);
			$aValues = array(
				':member_id'		=> $_SESSION['member_id'],
				':ticket_type'		=> $_REQUEST['ticket'],
				':ticket_status'	=> "1",
				':ticket_subject'	=> $_REQUEST['subject'],
				':problem_type'		=> $_REQUEST['problem_type'],		
				':description'		=> $_REQUEST['description'],
				':comments'			=> $_REQUEST['comments'],
				':stock_ticker'		=> $stockTicker,
				':fund_tickers'		=> $fundSymbols,
				':priority'			=> $_REQUEST['priority'],
				':assigned_to'		=> $assignTo
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsAddTicket->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		echo $error;
		//END DB INSERT
		
		//GET CASE NUMBER
		$query = "
			SELECT ticket_id
			FROM ".$_SESSION['support_ticket_table']."
			WHERE member_id=:member_id
			ORDER BY timestamp DESC
			LIMIT 1
		";
		
		try{
			$rsCaseNumber = $mLink->prepare($query);
			$aValues = array(
				':member_id'		=> $_SESSION['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsCaseNumber->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$case = $rsCaseNumber->fetch(PDO::FETCH_ASSOC);
		//END GET CASE NUMBER
		
		//If no errors in PDO continue
		if($error == ""){
			//Switch on the list_id to SEND the appropriate Confirmation Email
			switch($_REQUEST['ticket']){
				case "2"://Corporate Action
					//Email 
					$emailID 		= "1";
					//Notification
					$notificationID = "12-002"; 
					$notification	= "You have successfully submitted a Corporate Action ticket. Ticket Number: ".$case['ticket_id']."";
					$link			= "?page=08-01-cc-003&ticket=".$case['ticket_id']."";
				break;
				case "3"://General Help	 
					//Email
					$emailID		= "2";
					//Notification
					$notificationID = "08-001";
					$notification	= "You have successfully submitted a new support ticket. Ticket Number: ".$case['ticket_id']."";
					$link			= "?page=08-01-cc-003&ticket=".$case['ticket_id']."";
				break;
				case "9"://General Help	 
					//Email
					$emailID		= "6";
					//Notification
					$notificationID = "08-001";
					$notification	= "You have successfully submitted a new support ticket. Ticket Number: ".$case['ticket_id']."";
					$link			= "?page=08-01-cc-003&ticket=".$case['ticket_id']."";
				break;
				case "13"://General Help	 
					//Email
					$emailID		= "6";
					//Notification
					$notificationID = "08-001";
					$notification	= "You have successfully submitted a new support ticket. Ticket Number: ".$case['ticket_id']."";
					$link			= "?page=08-01-cc-003&ticket=".$case['ticket_id']."";
				break;
			}
			
			//Start Email
			//if($_REQUEST['request_email'] == 'on'){
				$ticketID 		= $case['ticket_id'];
				$memberID 		= $_SESSION['member_id'];
				$subject		= $_REQUEST['subject'];
				$description	= $_REQUEST['description'];
				include("../../includes/email/support-email.php");
			//}
			//End Email
			
			//Start Notification
			$memberID		= $_SESSION['member_id'];
			
			add_notification($mLink, $notificationID, $memberID, $notification, $link);
			//End Send Member Notification
			
			//Update Event Log
			$event = 'New Support Ticket Created';
			$detail = 'Member has submitted a new support ticket.';
			addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
			
			//START: Send notifications to Admins
			//if($_REQUEST['admin'] == "0"){
				
				$query = "
					SELECT member_id
					FROM ".$_SESSION['members_flags_table']."
					WHERE admin='1' AND support_admin='1' OR super_admin='1'
				";
				
				//Fund Symbols
				try{
					$rsGetAdmins = $mLink->prepare($query);
					$aValues = array();
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsGetAdmins->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				while($admin = $rsGetAdmins->fetch(PDO::FETCH_ASSOC)) {
					
					$adminMemberID = $admin['member_id'];
					
					$memberName = get_member($mLink, $memberID, 'full name');
					
					switch($_REQUEST['ticket']){
						case "2"://Corporate Action
							//Notification
							$notificationID = "12-002"; 
							$notification	= "".$memberName." has successfully submitted a Corporate Action ticket. Ticket Number: ".$case['ticket_id']."";
							$link			= "?page=08-01-cc-903&ticket=".$case['ticket_id']."";
						break;
						case "3"://General Help	 
							//Notification
							$notificationID = "08-001";
							$notification	= "".$memberName." has successfully submitted a new support ticket. Ticket Number: ".$case['ticket_id']."";
							$link			= "?page=08-01-cc-903&ticket=".$case['ticket_id']."";
						break;
						case "9"://General Help	 
							//Notification
							$notificationID = "08-001";
							$notification	= "".$memberName." has successfully submitted a new support ticket. Ticket Number: ".$case['ticket_id']."";
							$link			= "?page=08-01-cc-903&ticket=".$case['ticket_id']."";
						break;
					}
					
					add_notification($mLink, $notificationID, $adminMemberID, $notification, $link);
				}
			//}
			
			//End Notification
			
		}
		
	}else{
		//print_r($error_list);
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
	}//END VALIDATION
}

//+---------------------------------------------------------------------------------------------------------+
//|								TICKET REPLY/RESPONSE FORM	- PROCESS 3										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "3"){
//echo "hello";
	//START FROM VALIDATION
	$error_list = array();
	
	if($_REQUEST['community'] == "1"){	
		if(empty($_REQUEST['community-reply'])) {
			$error_list[] = "Please provide <strong>Reply</strong>";
			$tickerSymbolError = true;
		}
	}else{
		if(empty($_REQUEST['reply'])) {
			$error_list[] = "Please provide <strong>Reply</strong>";
			$tickerSymbolError = true;
		}
	}
	
	if(empty($_REQUEST['ticket_id'])) {
		$error_list[] = "Unable to pass ticket ID, please contact administrator.";
	}
	
	
	
	if(empty($error_list)) {
		
		
		
		if($_REQUEST['close'] == "0"){
			//echo "closed!";	
		}
		
		//Get ticket Member ID to test against
		$ticketMemberID = $_REQUEST['ticket_member_id'];
		
		if($ticketMemberID == $_SESSION['member_id']){
			$viewField		= "viewed,";
			$viewFieldValue = "UNIX_TIMESTAMP(),";
		}else{
			$viewField		= "admin_viewed,";
			$viewFieldValue = "UNIX_TIMESTAMP(),";
		}
		
		//Check if reply is coming from community or not
		if($_REQUEST['community'] == "1"){
			$table = $_SESSION['community_feedback_table'];
			$response = $_REQUEST['community-reply'];	
		}else{
			$table = $_SESSION['support_feedback_table'];
			$response = $_REQUEST['reply'];
		}
		
		//Check to see if user wants to submit anonymously
		if($_REQUEST['anonymous'] == "on"){
			$column 		= "anonymous,";
			$columnValue 	= "'1',";	
		}else{
			$column 		= "";
			$columnValue 	= "";	
		}
		
		if(isset($_SESSION['admin_id'])){
			$responseMemberID = $_SESSION['admin_id'];	
		}else{
			$responseMemberID = $_SESSION['member_id'];	
		}
		
		//INSERT Support Ticket Into Database
		$query = "
			INSERT INTO ".$table." (
				member_id,
				ticket_id,
				response,
				ticket_member_id,
				".$viewField."
				".$column."
				timestamp
			) VALUE (
				:member_id,
				:ticket_id,
				:response,
				:ticket_member_id,
				".$viewFieldValue."
				".$columnValue."
				UNIX_TIMESTAMP()
			)
		";
		
		try{
			$rsAddTicket = $mLink->prepare($query);
			$aValues = array(
				':member_id'		=> $responseMemberID,
				':ticket_id'		=> $_REQUEST['ticket_id'],
				':response'			=> $response,
				':ticket_member_id'	=> $_REQUEST['ticket_member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsAddTicket->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		//END DB INSERT
		
		//START SYSTEM NOTIFICATION
		//Input Notification
		if($_REQUEST['community'] != "1"){
			
			//if response is not from commuity do this
			if($_SESSION['member_id'] != $_REQUEST['ticket_member_id']){
				
				//set ticket status as "awaiting response"
				$query = "
					UPDATE ".$_SESSION['support_ticket_table']."
					SET	ticket_status='4'
					WHERE ticket_id=:ticket_id
				";
				try {
					$rsUpdate = $mLink->prepare($query);
					$aValues = array(
						':ticket_id'		=> $_REQUEST['ticket_id']
					);
					// Prepared query - for error logging and debugging
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsUpdate->execute($aValues);
				}
				catch(PDOException $error){
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				
				
				//Add notification
				$ticketType = get_ticket($mLink, $_REQUEST['ticket_id'], 'typeID');
				
				switch($ticketType){
					case "2":$notificationID = "12-003";break;//Corporate Action
					case "3":$notificationID = "08-002";break;//General Help
					case "9":$notificationID = "08-002";break;//Alpha Help
				}	
				$memberID		= $_REQUEST['ticket_member_id'];
				
				//Custom notification
				$notification	= "".get_member($mLink, $_SESSION['member_id'], "full name")." has responded to your ticket: ".$_REQUEST['ticket_id']."";
				$link			= "?page=08-01-cc-003&ticket=".$_REQUEST['ticket_id']."";
				
				add_notification($mLink, $notificationID, $memberID, $notification, $link);
			}
		}
		
		//Check to see if user responded to ticket
		if($_REQUEST['admin'] == "0"){
			
			$query = "
                SELECT member_id
				FROM ".$_SESSION['members_flags_table']."
				WHERE admin='1' AND support_admin='1' OR super_admin='1'
            ";
            
            //Fund Symbols
            try{
                $rsGetAdmins = $mLink->prepare($query);
                $aValues = array(
                    ':list_id' 	=> $listID
                );
                $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                $rsGetAdmins->execute($aValues);
            }
            catch(PDOException $error){
                // Log any error
                file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
            }
            
            while($admin = $rsGetAdmins->fetch(PDO::FETCH_ASSOC)) {
				
				$adminMemberID = $admin['member_id'];
				
				//Add notification
				$ticketType = get_ticket($mLink, $_REQUEST['ticket_id'], 'typeID');
				
				switch($ticketType){
					case "2":$notificationID = "12-003";break;//Corporate Action
					case "3":$notificationID = "08-002";break;//General Help
					case "9":$notificationID = "08-002";break;//Alpha Help
				}	
				$memberID		= $_REQUEST['ticket_member_id'];
				
				//Custom notification
				$notification	= "".get_member($mLink, $_SESSION['member_id'], "full name")." has responded to ticket: ".$_REQUEST['ticket_id']."";
				$link			= "?page=08-01-cc-903&ticket=".$_REQUEST['ticket_id']."";
				
				/*echo $ticketType;echo "-"; 
				echo $notificationID; echo $memberID; echo $notification; echo $link;*/
				add_notification($mLink, $notificationID, $adminMemberID, $notification, $link);
			}
		}
		//END SYSTEM NOTIFICATION
		
		//START EMAIL NOTIFICATION
		if($_REQUEST['request_email'] == 'on'){
			if($_REQUEST['community'] != "1"){
				//if response is not from community do this		
				if($_SESSION['member_id'] != $_REQUEST['ticket_member_id']){
					//If ticket is changed to close, do not send notification, recap email will be sent instead
					if($_REQUEST['close'] != "0"){
						$responder = get_member($mLink, $_SESSION['member_id'], "full name");
						$emailID = "3";//Support Response Email
						$ticketID = $_REQUEST['ticket_id'];
						$emailReply = $_REQUEST['reply'];
						include("../../includes/email/support-email.php");
					}
				}
			}
		}
		//END EMAIL NOTIFICATION
		
	}else{
		//print_r($error_list);
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
	}//END VALIDATION
	
}

if($process == "update-feedback"){
	
	
	$uid 	= $_REQUEST['uid'];
	$action	= $_REQUEST['action'];
	
	echo $action;
	
	switch($action){
		
		case 'remove':
			$query = "
				UPDATE ".$_SESSION['support_feedback_table']."
				SET active='0'
				WHERE uid=:uid
			";
			try{
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':uid' 	=> $uid
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdate->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			echo $preparedQuery;
			echo $error;
		break;
		
		case 'restore':
			$query = "
				UPDATE ".$_SESSION['support_feedback_table']."
				SET active='1'
				WHERE uid=:uid
			";
			try{
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':uid' 	=> $uid
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdate->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
		break;
		
	}
				
}

//+---------------------------------------------------------------------------------------------------------+
//|									AJAX LOAD RESPONSES - PROCESS 4											|
//+---------------------------------------------------------------------------------------------------------+
if($process == "4"){
	
	$ticketID = $_REQUEST['ticket'];
	
	$query = "
		SELECT m.username, m.name_first, m.name_last, sf.*
		FROM ".$_SESSION['support_feedback_table']." AS sf
		INNER JOIN ".$_SESSION['members_table']." AS m ON m.member_id=sf.member_id
		WHERE sf.ticket_id=:ticket_id
		ORDER BY sf.timestamp
	";
	
	try{
		$rsResponse = $mLink->prepare($query);
		$aValues = array(
			':ticket_id' 	=> $ticketID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsResponse->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	while($reply = $rsResponse->fetch(PDO::FETCH_ASSOC)){
		$response = $reply['response'];
		$fullName = $reply['name_first'] . " " . $reply['name_last'];
		
		$title = "Response By ".$fullName."";
		$titleStyle = "background:#414247;";
		
		$active = $reply['active'];
		
		if($reply['member_id'] == $_SESSION['member_id']){
			$title = "Response by You";
			$titleStyle = "background:#5BC0DE;";	
		}
		
		/*if($_SESSION['admin'] == "1"){
			$title = "Response By ".$fullName."";
		}*/
		
		if($_SESSION['admin'] == '1'){
			if($active == '1'){
				$showBtn = '<button type="button" onclick="updateFeedback(\''.$reply['uid'].'\',\'remove\');" class="btn btn-xs btn-danger">Remove</button>';	
			}else{
				$showBtn = '<button type="button" onclick="updateFeedback(\''.$reply['uid'].'\',\'restore\');" class="btn btn-xs btn-warning">Restore</button>';
			}
		}else{
			$showBtn = '';
		}	
		
		$display = 1;
		
		if($_SESSION['admin'] == '1'){
			$display = 1;	
			
			if($active == '0'){
				$addCSS = 'style="opacity:.5;"';
				$message = '<p>This message has been removed from the feedback.</p>';	
			}else{
				$addCSS = '';	
				$message = '';
			}
			
		}else{
			$addCSS = '';
			$message = '';
			
			if($active != '1'){
				$display = 0;	
			}
				
		}
		
		if($display == 1){
		?>
		<div class="response-container" <?php echo $addCSS;?>>
        	<div class="response-header" style="<?php echo $titleStyle; ?>">
            	<div class="responder" style="float:left;">
            		<?php echo $title; ?>
            	</div><!--responder-->
                <div class="response-date">
                	
                	<?php echo date('D m/d/Y', $reply['timestamp']);?> at <?php echo date('h:i A', $reply['timestamp']);?> <?php echo $showBtn;?>
                </div><!--response-date-->
                <div class="clear"></div>
            </div><!--response-header-->
            
            <div class="response-body">
				<?php echo $message;?>
                <p class="last"><?php echo $response; ?></p>
            </div><!--response-body-->
		</div>
		<?php
		}
	}
	
}

//+---------------------------------------------------------------------------------------------------------+
//|								UPDATE TICKET STATUS - PROCESS 5											|
//+---------------------------------------------------------------------------------------------------------+
if($process == "5"){
	
	if($_REQUEST['status'] == "0"){
		$openClose = ", closed=UNIX_TIMESTAMP(), opened='0'";	
	}
	if($_REQUEST['status'] == "1"){
		$openClose = ", closed='0', opened=UNIX_TIMESTAMP()";	
	}
	
	$query = "
		UPDATE ".$_SESSION['support_ticket_table']."
		SET	ticket_status=:ticket_status".$openClose."
		WHERE ticket_id=:ticket_id
	";
	try {
		$rsUpdate = $mLink->prepare($query);
		$aValues = array(
			':ticket_id'		=> $_REQUEST['ticket'],
			':ticket_status'	=> $_REQUEST['status'],
		);
		// Prepared query - for error logging and debugging
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsUpdate->execute($aValues);
	}
	catch(PDOException $error){
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	if($_REQUEST['admin'] != "" OR $_REQUEST['admin'] == "undefined"){
		
		$query = "
			SELECT member_id, assigned_to
			FROM ".$_SESSION['support_ticket_table']."
			WHERE ticket_id=:ticket_id
		";
		
		try{
			$rsMember = $mLink->prepare($query);
			$aValues = array(
				':ticket_id' 	=> $_REQUEST['ticket']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsMember->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$member = $rsMember->fetch(PDO::FETCH_ASSOC);
		
		//START NOTIFICATION
		$ticketType = get_ticket($mLink, $_REQUEST['ticket'], 'typeID');
			
		switch($ticketType){
			case "2":$notificationID = "12-004";break;//Corporate Action
			case "3":$notificationID = "08-004";break;//General Help
			case "9":$notificationID = "08-004";break;//General Help
		}
		
		$memberID		= $member['member_id'];
		$assignedID		= $member['assigned_to'];
		//Custom notification
		$notification	= "".get_member($mLink, $_REQUEST['admin'], "full name")." has changed the status of your ticket: ".$_REQUEST['ticket'].".";
		$link			= "?page=08-01-cc-903&ticket=".$_REQUEST['ticket']."";
		
		add_notification($mLink, $notificationID, $memberID, $notification, $link);
		
		echo $assignedID.' | '.$_SESSION['member_id'].' | '.$notificationID.' | '.$ticketType;
		//send notification to assigned user
		if($_REQUEST['status'] == "1"){
			if($_SESSION['member_id'] != $assignedID){
				//ticket is being re-opened
				
				//send to assigned user
				$notification	= "".$_SESSION['member_id']." has re-opened a ticket you are assigned to: ".$_REQUEST['ticket'].".";
				$link			= "?page=08-01-cc-903&ticket=".$_REQUEST['ticket']."";
				add_notification($mLink, $notificationID, $assignedID, $notification, $link);
				
				//send to admin that chagned the status
				$notification	= "You have re-opened a ticket: ".$_REQUEST['ticket'].".";
				$link			= "?page=08-01-cc-903&ticket=".$_REQUEST['ticket']."";
				add_notification($mLink, $notificationID, $_SESSION['member_id'], $notification, $link);
				
			}else{
				
				//send to admin that chagned the status
				$notification	= "You have re-opened a ticket that has been assigned to you: ".$_REQUEST['ticket'].".";
				$link			= "?page=08-01-cc-903&ticket=".$_REQUEST['ticket']."";
				add_notification($mLink, $notificationID, $_SESSION['member_id'], $notification, $link);
				
			}
			
		}
		
		//END NOTIFICATION
		
		//START EMAIL NOTIFICATION	
		if($_REQUEST['status'] != '0'){
			if($_REQUEST['admin'] != 'undefined'){	
				$responder 	= get_member($mLink, $_REQUEST['admin'], "full name");
				$emailID 	= "4";//Support Ticket Status Update Email
				$ticketID 	= $_REQUEST['ticket'];
				$status 	= get_status($mLink, $_REQUEST['status'], "status");
				include("../../includes/email/support-email.php");
			}
		}
		//END EMAIL NOTIFICATION
		
	}
	
	//echo $_REQUEST['ticket'];
	//echo "|";
	//echo $_REQUEST['status'];
}


//+---------------------------------------------------------------------------------------------------------+
//|									SAVE TICKET NOTES - PROCESS 6											|
//+---------------------------------------------------------------------------------------------------------+
if($process == "6"){
	
	$aNotes = $_REQUEST['note_file'];
	
	$listNotes = implode('|', $aNotes);
	
	
	
	//START FROM VALIDATION
	$error_list = array();
	
	//$error_list[] = $listNotes;
		
	if(empty($_REQUEST['notes'])) {
		$error_list[] = "<strong>Notes</strong> filed is empty. Please provide data before you sumbit.";
		$tickerSymbolError = true;
	}
	
	if(empty($_REQUEST['ticket_id'])) {
		$error_list[] = "Unable to pass ticket ID, please contact administrator.";
	}
	if(empty($error_list)) {
	
		
		//INSERT Support Ticket Into Database
		$query = "
			UPDATE ".$_SESSION['support_ticket_table']."
			SET notes=:notes, notes_file=:notes_file
			WHERE ticket_id=:ticket_id
		";
		
		try{
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':notes'		=> addslashes($_REQUEST['notes']),
				':ticket_id'	=> $_REQUEST['ticket_id'],
				':notes_file'	=> $listNotes
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		//END DB INSERT
		
	}else{
		//print_r($error_list);
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
	}//END VALIDATION
}

//+---------------------------------------------------------------------------------------------------------+
//|									MARK TICKET AS VIEWED - PROCESS 7										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "7"){
	
	//CHECK TO SEE IF request came from a closed ticket
	$closedTicket = $_REQUEST['closed'];
	
	//Get vars from URL
	$ticketID = $_REQUEST['ticket'];
	$user = $_REQUEST['user'];
	
	if($user == "member"){
		$viewColumn = "viewed";	
	}
	
	if($user == "admin"){
		$viewColumn = "admin_viewed";	
	}
	
	//UPDATE Ticket Viewed
	$query = "
		UPDATE ".$_SESSION['support_feedback_table']."
		SET ".$viewColumn."=UNIX_TIMESTAMP()
		WHERE ticket_id=:ticket_id
	";
	
	try{
		$rsUpdate = $mLink->prepare($query);
		$aValues = array(
			':ticket_id'	=> $ticketID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsUpdate->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	//END DB INSERT
	
	if($user == "admin"){
		if($closedTicket == "1"){
			header('Location: /?page=08-01-cc-903&ticket='.$ticketID.'&closed=1');	
		}else{
			header('Location: /?page=08-01-cc-903&ticket='.$ticketID.'');	
		}
	}elseif($user == "member"){
		if($closedTicket == "1"){
			header('Location: /?page=08-01-cc-003&ticket='.$ticketID.'&closed=1');
		}else{
			header('Location: /?page=08-01-cc-003&ticket='.$ticketID.'');	
		}
	}

}

//+---------------------------------------------------------------------------------------------------------+
//|										ASSIGN TICKET - PROCESS 8											|
//+---------------------------------------------------------------------------------------------------------+
if($process == "8"){
	
	$ticketID = $_REQUEST['ticket'];
	$assignedID = $_REQUEST['assign'];
	$memberID = $_REQUEST['member'];
	
		
	
	//UPDATE Assigned To 
	$query = "
		UPDATE ".$_SESSION['support_ticket_table']."
		SET assigned_to=:assigned_to
		WHERE ticket_id=:ticket_id
	";
	
	try{
		$rsUpdate = $mLink->prepare($query);
		$aValues = array(
			':ticket_id'	=> $ticketID,
			':assigned_to'	=> $assignedID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsUpdate->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	//END DB INSERT
	
	//START NOTIFICATION	
	$notificationID	= "08-003";
	$memberID2		= $assignedID;
	//Custom notification
	$notification	= "".get_member($mLink, $memberID, "full name")." has assigned you ticket: ".$ticketID.".";
	$link			= "?page=08-01-cc-903&ticket=".$ticketID."";
	
	add_notification($mLink, $notificationID, $memberID2, $notification, $link);
	//END NOTIFICATION
	
	echo get_member($mLink, $assignedID, "full name");
	
	
}

//+---------------------------------------------------------------------------------------------------------+
//|									TICKET RECAP EMAIL - PROCESS 9											|
//+---------------------------------------------------------------------------------------------------------+
if($process == "9"){
	$ticketID = $_REQUEST['ticket'];	
	
	$query = "
		SELECT *
		FROM ".$_SESSION['support_ticket_table']."
		WHERE ticket_id=:ticket_id
	";
	
	try{
		$rsTicketInfo = $mLink->prepare($query);
		$aValues = array(
			':ticket_id' 	=> $_REQUEST['ticket']
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsTicketInfo->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$ticket = $rsTicketInfo->fetch(PDO::FETCH_ASSOC);
	
	//Get Ticket Responses
	$query = "
		SELECT *
		FROM ".$_SESSION['support_feedback_table']."
		WHERE ticket_id=:ticket_id
	";
	
	try{
		$rsResponses = $mLink->prepare($query);
		$aValues = array(
			':ticket_id' 	=> $_REQUEST['ticket']
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsResponses->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$convo = '<table width="550" cellspacing="5" cellpadding="5" border="1">';
	while($reply = $rsResponses->fetch(PDO::FETCH_ASSOC)){
		$convo .= '<tr><td>'.$reply['response'].'<br /><small>'.get_member($mLink, $reply['member_id'], "full name").' | '.date('m/d/Y h:i a', $reply['timestamp']).'</small></td></tr>';
	}
	$convo .= '</table>';
	
	
	//START EMAIL NOTIFICATION		
	$emailID 		= "5";//Support Ticket Status Update Email
	$memberID		= $ticket['member_id'];
	$description	= $ticket['description'];
	$subject		= $ticket['ticket_subject'];
	include("../../includes/email/support-email.php");
	//END EMAIL NOTIFICATION
	
}

//+---------------------------------------------------------------------------------------------------------+
//|								COMMUNITY AJAX LOAD RESPONSES - PROCESS 10									|
//+---------------------------------------------------------------------------------------------------------+
if($process == "10"){
	
	// Get ticket id from passed script
	$ticketID = $_REQUEST['ticket'];
	
	
	// QUERY DATABASE TO GET COMMUNITY FEEDBACK RESPONSES
	$query = "
		SELECT *
		FROM ".$_SESSION['community_feedback_table']." 
		WHERE ticket_id=:ticket_id
		ORDER BY timestamp
	";
	
	try{
		$rsResponse = $mLink->prepare($query);
		$aValues = array(
			':ticket_id' 	=> $ticketID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsResponse->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	//+-------------------------------------------------------------------------------------------------------------+
	//|										START PRINT RESPONSE FUNCTION											|
	//+-------------------------------------------------------------------------------------------------------------+
	
	function printNestedReply($parent, $response_array, $mLink) {
		foreach ($response_array as $reply) {
			if($parent == $reply['parent_uid']) {
			  	
		
				if(get_member($mLink, $reply['member_id'], 'admin') == "1"){
					$isAdmin = "(Staff)";	
				}else{
					$isAdmin = "";	
				}
				
				if($reply['parent_uid'] != NULL){
					$replyWord = '&#8627; ';
				}else{
					$replyWord = "Response By";
				}
				
				//Check to see if repsonse has been deleted
				if($reply['deleted'] == '0'){
					
					//response is not deleted
					
					if($reply['member_id'] == $reply['ticket_member_id']){
						//orginal poster
						if($reply['anonymous'] == "1"){
							if($_SESSION['admin'] == "1"){
								$title = "".$replyWord." Anonymous (".get_member($mLink, $reply['member_id'], 'full name').") (Original Poster)";
								$titleStyle = "background:#414247;";
							}else{
								$title = "".$replyWord." Anonymous";
								$titleStyle = "background:#414247;";
							}
						}else{
							$title = "".$replyWord." ".get_member($mLink, $reply['member_id'], 'full name')." (Original Poster) ".$isAdmin."";
							$titleStyle = "background:#414247;";
						}
					}else{
						//not original poster
						if($reply['anonymous'] == "1"){
							if($_SESSION['admin'] == "1"){
								$title = "".$replyWord." Anonymous (".get_member($mLink, $reply['member_id'], 'full name').")";
								$titleStyle = "background:#414247;";
							}else{
								$title = "".$replyWord." Anonymous";
								$titleStyle = "background:#414247;";
							}
						}else{
							$title = "".$replyWord." ".get_member($mLink, $reply['member_id'], 'full name')." ".$isAdmin."";
							$titleStyle = "background:#414247;";
						}
					}
					$deleted = '';
					$hideButtons = '';
					$response = $reply['response'];
				}else{
					//Response is deleted
					
					$title = "Response Deleted";
					$titleStyle = "background:#414247;";
					$deleted = 'opacity:.5 !important;';
					$hideButtons = 'style="display:none;"';
					$response = "This repsonse has been removed by Administrator.";
				}	
				
				//Check to see if the user is looking at his/her own response
				if($_SESSION['member_id'] == $reply['member_id']){
					//Check to see if user is the original poster
					if($reply['member_id'] == $reply['ticket_member_id']){
						$title = "".$replyWord." by You (Original Poster) ".$isAdmin."";
						$titleStyle = "background:#ccc8c8;color:#000000;";
					}else{
						$title = "".$replyWord." by You ".$isAdmin."";
						$titleStyle = "background:#ccc8c8;color:#000000;";
					}
				}
				
				// Explode database columns into an array
				$aAgree 		= explode("|", $reply['agree']);
				$aDisagree 	= explode("|", $reply['disagree']);
				
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
					<div class="modal fade" id="agree-<?php echo $reply['uid'];?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
					<div class="modal fade" id="disagree-<?php echo $reply['uid'];?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
						$replyStatusAgree = "you";
						$btnStatus = "default";
						$btnType = "You Agree";
						$btnTitle = "Click here to undo.";
						
						if($cntAgree == 1){
							$replyStatusAgree = "single-you";
								
						}
								
					}else{
						//Did not find member
						//echo "NOT FOUND";
						$replyStatusAgree = "others";
						$btnStatus = "success";
						$btnType = "Agree";
						$btnTitle = "Click here to agree.";
						
						if($cntAgree == 1){
							$replyStatusAgree = "single";
						}
						
						if($cntAgree <= 2){
							$replyStatusAgree = "2others";
							
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
							$replyStatusAgree = "others";
							
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
					
					$agreeBTN = '<button type="button" class="btn btn-'.$btnStatus.' btn-xs" onClick="agreeDisagree(\'agree\',\''.$reply['uid'].'\', \''.$reply['member_id'].'\',\''.$reply['ticket_id'].'\')" title="'.$btnTitle.'">'.$btnType.'</button>';
					
				}else{
					
					$agreeBTN = '<button type="button" class="btn btn-success btn-xs" onClick="agreeDisagree(\'agree\',\''.$reply['uid'].'\', \''.$reply['member_id'].'\',\''.$reply['ticket_id'].'\')">Agree</button>';
					
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
						$replyStatusDisagree = "you";
						$btnStatus = "default";
						$btnType = "You Disagree";
						$btnTitle = "Click here to undo.";
						
						if($cntDisagree == 1){
							$replyStatusDisagree = "single-you";
						}
								
					}else{
						//Did not find member
						$replyStatusDisagree = "others";
						$btnStatus = "info";
						$btnType = "Disagree";
						$btnTitle = "Click here to disagree.";
						
						if($cntDisagree == 1){
							$replyStatusDisagree = "single";
						}
						
						if($cntDisagree <= 2){
							$replyStatusDisagree = "2others";
							
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
							$replyStatusDisagree = "others";
							
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
					
					$disagreeBTN = '<button type="button" class="btn btn-'.$btnStatus.' btn-xs" onClick="agreeDisagree(\'disagree\',\''.$reply['uid'].'\', \''.$reply['member_id'].'\',\''.$reply['ticket_id'].'\')" title="'.$btnTitle.'">'.$btnType.'</button>';
					
				}else{
					
					$disagreeBTN = '<button type="button" class="btn btn-info btn-xs" onClick="agreeDisagree(\'disagree\',\''.$reply['uid'].'\', \''.$reply['member_id'].'\',\''.$reply['ticket_id'].'\')">Disagree</button>';
				
				}
				//END DISAGREE BUTTON
				
				//START REPLY BUTTON
				if($reply['level'] < $_SESSION['max_reply_level_ca']){
					$replyBTN = '<button type="button" class="btn btn-warning btn-xs" onClick="showReply(\''.$reply['uid'].'\')" title="Click here to reply">Reply</button>';	
				}else{
					$replyBTN = '';	
				}
				//END REPLY BUTTON
				
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
				
				switch($replyStatusAgree){
					case "single":$statusMessageA = '<a href="#agree-'.$reply['uid'].'" data-toggle="modal">'.$memberAgree.'</a> agrees with this.';break;
					case "single-you":$statusMessageA = '<a href="#agree-'.$reply['uid'].'" data-toggle="modal">You</a> agree with this.';break;
					case "you":$statusMessageA = 'You, and <a href="#agree-'.$reply['uid'].'" data-toggle="modal">'.($cntAgree - 1).' others</a> agree with this.';break;
					case "others":$statusMessageA = ''.$memberAgree.' and <a href="#agree-'.$reply['uid'].'" data-toggle="modal">'.($cntAgree - 2).' '.$otherText.'</a> agree with this.';break;
					case "2others":$statusMessageA = '<a href="#agree-'.$reply['uid'].'" data-toggle="modal">'.$memberAgree.'</a> '.$agreeText.' with this.';break;
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
				
				switch($replyStatusDisagree){
					case "single":$statusMessageD = '<a href="#disagree-'.$reply['uid'].'" data-toggle="modal">'.$memberDisagree.'</a> disagrees with this.';break;
					case "single-you":$statusMessageD = '<a href="#disagree-'.$reply['uid'].'" data-toggle="modal">You</a> disagree with this.';break;
					case "you":$statusMessageD = 'You, and <a href="#disagree-'.$reply['uid'].'" data-toggle="modal">'.($cntDisagree - 1).' others</a> disagree with this.';break;
					case "others":$statusMessageD = ''.$memberDisagree.' and <a href="#disagree-'.$reply['uid'].'" data-toggle="modal">'.($cntDisagree - 2).' '.$otherText.'</a> disagree with this.';break;
					case "2others":$statusMessageD = '<a href="#disagree-'.$reply['uid'].'" data-toggle="modal">'.$memberDisagree.'</a> '.$disagreeText.' with this.';break;
					default: $statusMessageD = '';
						
				}
				//END DISAGREE LOGIC SWITCH
				
				// If there are replys with agrees or disagrees display them
				if($reply['deleted'] == "0"){
					if($cntAgree >= 1 || $cntDisagree >= 1){
						$replyStats = '
						<div class="agree-disagree-area">
							
							<p><small>'.$statusMessageA.' '.$statusMessageD.'</small></p>
						   
						</div>';
					}else{
						$replyStats = '';	
					}
				}else{
					$replyStats = '';	
				}
				
				// Reset Variables
				$replyStatusDisagree = "";
				$replyStatusAgree = "";
				$memberAgree = "";
				$memberDisagree = "";
				$mCnt = "";				
				
				// Reply indentation based on level.
				switch($reply['level']){
					case "1": $movePx = "20";break;
					case "2": $movePx = "40";break;
					case "3": $movePx = "60";break;
					case "4": $movePx = "80";break;
					case "5": $movePx = "100";break;
					case "6": $movePx = "120";break;
				}
				
				//START ADMIN DELETE BUTTON
				if($_SESSION['admin'] == "1"){
					if($reply['deleted'] == "0"){
						$deleteBTN = '<a href="javascript:void(0);" onClick="deleteReply(\''.$reply['uid'].'\',\'delete\')" class="btn btn-xs btn-danger">Delete</a>';
						$deletedResponse = '';
						$deletedTitle = '';
						$restoreBTN = '';
					}else{
						$deleteBTN = '';
						$deletedResponse = '<p class="last">('.$reply['response'].')</p>';
						$deletedTitle = '('.get_member($mLink, $reply['member_id'], 'full name').')';
						$restoreBTN = '<a href="javascript:void(0);" onClick="deleteReply(\''.$reply['uid'].'\',\'restore\')" class="btn btn-xs btn-danger">Restore</a>';	
					}
				}else{
					$deleteBTN = '';
					$deletedResponse = '';
					$deletedTitle = '';
					$restoreBTN = '';
				}
				//END ADMIN DELETE BUTTON
				
				//START RESPONSE HTML BLOCK			  
				$html .= '
				
					<div class="response-container" style="margin-left:'.$movePx.'px !important; '.$deleted.'">
						<div class="response-header" style="'.$titleStyle.'">
							<div class="responder" style="float:left;">
								'.$title.' '.$deletedTitle.'
							</div><!--responder-->
							<div class="response-date">
								'.date('D m/d/Y', $reply['timestamp']).' at '.date('h:i A', $reply['timestamp']).' &nbsp;&nbsp;'.$deleteBTN.''.$restoreBTN.'
							</div><!--response-date-->
							<div class="clear"></div>
						</div><!--response-header-->
						
						<div class="response-body">
							<div class="response">
								<p class="last">'.$response.'</p>
								'.$deletedResponse.'
								
							</div><!--response-->
							
							<div class="response-actions" '.$hideButtons.'>
								'.$agreeBTN.' '.$disagreeBTN.' '.$replyBTN.'
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
										<input type="hidden" name="level" value="'.$reply['level'].'" />
												
										<input type="submit" onClick="postReply(\''.$reply['uid'].'\')" value="Reply" class="btn btn-warning btn-sm">
										
								   <button type="button" onClick="hideReply(\''.$reply['uid'].'\')" class="btn btn-sm btn-info">Cancel</button>
									</div><!--form-body-->
								</form>
								<!-- END FORM-->
							</div><!--reply-form-->
							 
						</div><!--response-body-->
						
						
						
						'. $replyStats.'
						
						
					</div><!--response-container-->
					
				
				';
				
				//Start Nesting Loop
				$html .= printNestedReply($reply['uid'], $response_array, $mLink);
			}
		}
		
		return $html;
	}
	//+-------------------------------------------------------------------------------------------------------------+
	//|											END PRINT RESPONSE FUNCTION											|
	//+-------------------------------------------------------------------------------------------------------------+
	
	// START LOOP FOR EACH RESPONSE 
	$response_array = Array();
	while($reply = $rsResponse->fetch(PDO::FETCH_ASSOC)){
		
		$response_array[] = array(
			'uid' 				=> $reply['uid'],
			'parent_uid' 		=> $reply['parent_uid'],
			'ticket_member_id'	=> $reply['ticket_member_id'],
			'member_id'			=> $reply['member_id'],
			'response' 			=> $reply['response'],
			'agree'				=> $reply['agree'],
			'disagree'			=> $reply['disagree'],
			'anonymous'			=> $reply['anonymous'],
			'ticket_id'			=> $reply['ticket_id'],
			'level'				=> $reply['level'],
			'timestamp'			=> $reply['timestamp'],
			'deleted'			=> $reply['deleted']
		);
				
	}
	// END LOOP FOR EACH RESPONSE
	
	
	// Start with the comment that has no parent (NULL parent)
    echo printNestedReply(NULL, $response_array, $mLink);
	
}

//+---------------------------------------------------------------------------------------------------------+
//|								COMMUNITY AGREE/DISAGREE - PROCESS 11										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "11"){
	//Get Vars from click function
	$uid 		= $_REQUEST['uid'];
	$choice		= $_REQUEST['choice'];
	$member		= $_REQUEST['member'];
	$ticketID	= $_REQUEST['ticket'];
	
	// Grab existing data from DB
	$query = "
		SELECT agree, disagree
		FROM ".$_SESSION['community_feedback_table']." 
		WHERE uid=:uid
		ORDER BY timestamp
	";
	
	try{
		$rsResponse = $mLink->prepare($query);
		$aValues = array(
			':uid' 	=> $uid
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
			
			//START NOTIFICATION	
			$notificationID	= "04-001";
			$memberID2		= $member;
			//Custom notification
			$notification	= "".get_member($mLink, $_SESSION['member_id'], "full name")." has agreed to your corporate action response.";
			$link			= "?page=04-02-cc-003&ticket=".$ticketID."";
			
			add_notification($mLink, $notificationID, $memberID2, $notification, $link);
			//END NOTIFICATION
			
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
			UPDATE ".$_SESSION['community_feedback_table']."
			SET agree=:agree, disagree=:disagree
			WHERE uid=:uid
		";
		
		try{
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':uid'		=> $uid,
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
			
			//START NOTIFICATION	
			$notificationID	= "04-002";
			$memberID2		= $member;
			//Custom notification
			$notification	= "".get_member($mLink, $_SESSION['member_id'], "full name")." disagreed to your corporate action response.";
			$link			= "?page=04-02-cc-003&ticket=".$ticketID."";
			
			add_notification($mLink, $notificationID, $memberID2, $notification, $link);
			//END NOTIFICATION
			
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
			UPDATE ".$_SESSION['community_feedback_table']."
			SET agree=:agree, disagree=:disagree
			WHERE uid=:uid
		";
		
		try{
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':uid'		=> $uid,
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

}

//+---------------------------------------------------------------------------------------------------------+
//|								COMMUNITY RESPONSE REPLY - PROCESS 12										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "12"){

	//Set Vars
	$ticketID = $_REQUEST['ticket_id'];
	$ticketMemberID = $_REQUEST['ticket_member_id'];
	$uid = $_REQUEST['uid'];
	$reply = $_REQUEST['reply'];
	$level = $_REQUEST['level'];
	
	//Increment Level by 1(one)
	$level = $level + 1;
	
	//START FROM VALIDATION
	$error_list = array();
		
	if(empty($reply)) {
		$error_list[] = "Please provide a <strong>Reply</strong>";
		$tickerSymbolError = true;
	}
	
	if(empty($_REQUEST['ticket_id'])) {
		$error_list[] = "Unable to pass ticket ID, please contact administrator.";
	}
	
	if(empty($error_list)) {
		//If no errors, insert into database
		
		$query = "
			INSERT INTO ".$_SESSION['community_feedback_table']." (
				member_id,
				ticket_id,
				response,
				ticket_member_id,
				parent_uid,
				level,
				timestamp
			) VALUE (
				:member_id,
				:ticket_id,
				:response,
				:ticket_member_id,
				:parent_uid,
				:level,
				UNIX_TIMESTAMP()
			)
		";
		
		try{
			$rsAddTicket = $mLink->prepare($query);
			$aValues = array(
				':member_id'		=> $_SESSION['member_id'],
				':ticket_id'		=> $ticketID,
				':response'			=> $reply,
				':ticket_member_id'	=> $ticketMemberID,
				':parent_uid'		=> $uid,
				':level'			=> $level
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsAddTicket->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		//END DB INSERT
		
		//echo ''.$ticketID.'|'.$ticketMemberID.'|'.$uid.'|'.$reply.'';
		
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
//|									COMMUNITY DELETE REPLY - PROCESS 13										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "13"){
	$uid = $_REQUEST['uid'];
	$action = $_REQUEST['action'];
	
	//Check to see if the action is delete or restore
	if($action == "delete"){
		//action is delete
		
		//Update DELETE field in Database
		$query = "
			UPDATE ".$_SESSION['community_feedback_table']."
			SET deleted=UNIX_TIMESTAMP()
			WHERE uid=:uid
		";
		
		try{
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':uid'		=> $uid
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
		echo "deleted";
		
	}elseif($action == "restore"){
		//Action is restore
		
		//Update DELETE field in Database
		$query = "
			UPDATE ".$_SESSION['community_feedback_table']."
			SET deleted='0'
			WHERE uid=:uid
		";
		
		try{
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':uid'		=> $uid
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
		echo "restored";

	}
}

//+---------------------------------------------------------------------------------------------------------+
//|									COMMUNITY POST RESOLUTION - PROCESS 14									|
//+---------------------------------------------------------------------------------------------------------+
if($process == "14"){
	$ticketID = $_REQUEST['ticket_id'];
	$resolution = $_REQUEST['resolution'];
	
	if(empty($resolution)) {
		$error_list[] = "Please provide a <strong>Resolution</strong>";
		$tickerSymbolError = true;
	}
	
	if(empty($ticketID)) {
		$error_list[] = "Unable to pass ticket ID, please contact administrator.";
	}
	
	if(empty($error_list)) {
		//If no errors, insert into database
		
		//echo $ticketID;
		//echo $resolution;
		
		//Update ticket to show resolution
		$query = "
			UPDATE ".$_SESSION['support_ticket_table']."
			SET resolution=:resolution, closed=UNIX_TIMESTAMP(), ticket_status='0'
			WHERE ticket_id=:ticket_id
		";
		
		try{
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':ticket_id'	=> $ticketID,
				':resolution'	=> $resolution
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		//END DB Update
		
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


switch($process){
	
	case "upload-file":
		
		//$aCurrentFiles	= explode('|',$_REQUEST['current_files']);
		$ticketID = $_REQUEST['ticket_id'];
		
		
		$target_dir = "../../files/support_uploads/";
		
		$file_prefix = $ticketID.'_'.date('Y-m-d--h-i').'_'.mktime().'_';
		
		$fileName = str_replace(' ', '-',$_FILES["fileUpload"]["name"]);
		
		//echo $fileName.'-';
		
		$target_file = $target_dir . basename($file_prefix.$fileName);
		
		
		
		$uploadOk = 1;
		
		if(isset($_POST) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
		
			// check $_FILES['ImageFile'] not empty
			if(!isset($_FILES['fileUpload']) || !is_uploaded_file($_FILES['fileUpload']['tmp_name'])){
					die('File is Missing2!'); // output error when above checks fail.
			}
			
		
			$path_parts = pathinfo($target_file);
			
			if($path_parts['extension'] != 'txt'){
				echo 'File is not a text file.';
				$uploadOk = 0;	
			}
			
			// Check if file already exists
			if (file_exists($target_file)) {
				echo "Sorry, file already exists.";
				$uploadOk = 0;
			}
			
			// Check file size
			if ($_FILES["fileUpload"]["size"] > 500000) {
				echo "Sorry, your file is too large.";
				$uploadOk = 0;
			}
			
			
			
			/*// Allow certain file formats
			if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
			&& $imageFileType != "gif" ) {
				echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
				$uploadOk = 0;
			}*/
			
			
			// Check if $uploadOk is set to 0 by an error
			if ($uploadOk == 0) {
				echo "Sorry, your file was not uploaded.";
			
			
			// if everything is ok, try to upload file
			} else {
				if (move_uploaded_file($_FILES["fileUpload"]["tmp_name"], $target_file)) {
					//echo "The file ". $fileName. " has been uploaded.";
					
					$aCurrentFiles[] = $file_prefix.$fileName;
					
					$return = '<ul>';
					foreach($aCurrentFiles as $key=>$listFile){
						
						$aListFile = explode('_', $listFile);
						
						$return .= '<li><a href="files/support_uploads/'.$listFile.'" target="_blank">'.$aListFile[3].' - ('.date('m/d/Y h:i a', $aListFile[2]).')</a><input type="hidden" name="note_file[]" value="'.$listFile.'" target="_blank" /></li>';
					}
					$return .= '</ul>';
					
					echo $return;
				} else {
					echo "Sorry, there was an error uploading your file.";
				}
			}
			
			
		}
	
		
	break;
		
}

//+---------------------------------------------------------------------------------------------------------+
//|										SAVE NOTES FILE - PROCESS 15										|
//+---------------------------------------------------------------------------------------------------------+
if($process == '15'){
	
	$aCurrentFiles	= explode('|',$_REQUEST['current_files']);
	$ticketID = $_REQUEST['ticket_id'];
	
	
	$target_dir = "../../files/support_documents/";
	
	$file_prefix = $ticketID.'_'.date('Y-m-d--h-i').'_'.mktime().'_';
	
	$fileName = str_replace(' ', '-',$_FILES["fileToUpload"]["name"]);
	
	//echo $fileName;
	
	$target_file = $target_dir . basename($file_prefix.$fileName);
	
	
	
	$uploadOk = 1;
	
	if(isset($_POST) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
	
		// check $_FILES['ImageFile'] not empty
		if(!isset($_FILES['fileToUpload']) || !is_uploaded_file($_FILES['fileToUpload']['tmp_name'])){
				die('File is Missing!'); // output error when above checks fail.
		}
		
	
		$path_parts = pathinfo($target_file);
		
		if($path_parts['extension'] != 'txt'){
			echo 'File is not a text file.';
			$uploadOk = 0;	
		}
		
		// Check if file already exists
		if (file_exists($target_file)) {
			echo "Sorry, file already exists.";
			$uploadOk = 0;
		}
		
		// Check file size
		if ($_FILES["fileToUpload"]["size"] > 500000) {
			echo "Sorry, your file is too large.";
			$uploadOk = 0;
		}
		
		
		
		/*// Allow certain file formats
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
		&& $imageFileType != "gif" ) {
			echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
			$uploadOk = 0;
		}*/
		
		
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
			echo "Sorry, your file was not uploaded.";
		
		
		// if everything is ok, try to upload file
		} else {
			if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
				//echo "The file ". $fileName. " has been uploaded.";
				
				$aCurrentFiles[] = $file_prefix.$fileName;
				
				$return = '<ul>';
				foreach($aCurrentFiles as $key=>$listFile){
					
					$aListFile = explode('_', $listFile);
					
					$return .= '<li><a href="files/support_documents/'.$listFile.'" target="_blank">'.$aListFile[3].' - ('.date('m/d/Y h:i a', $aListFile[2]).')</a><input type="hidden" name="note_file[]" value="'.$listFile.'" /></li>';
				}
				$return .= '</ul>';
				
				echo $return;
			} else {
				echo "Sorry, there was an error uploading your file.";
			}
		}
		
		
	}

}
?>