<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

require_once("../../includes/system-functions.php");

$process = $_REQUEST['process'];


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
                echo '<option value="'.$options['value'].'" '.$disabled.'>'.$options['label'].'</option>';
            
            }
            ?>
        </select>
    </div><!--form-group-->
	<?php
	if($listID == "2"/*Corporate Action*/){
		?>
    	<div class="form-group">
            <label  class="control-label">Stock Ticker *</label>
            <input type="text" name="stock_ticker" id="stock_ticker" class="form-control" placeholder="AAPL" style="width:100px;">
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
				timestamp
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
				UNIX_TIMESTAMP()
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
				':priority'			=> $_REQUEST['priority']
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
			}
			
			//Start Email
			$ticketID 		= $case['ticket_id'];
			$memberID 		= $_SESSION['member_id'];
			$subject		= $_REQUEST['subject'];
			$description	= $_REQUEST['description'];
			include("../../includes/email/support-email.php");
			//End Email
			
			//Start Notification
				//$notificationID	= "08-001";
			$memberID		= $_SESSION['member_id'];
			//Custom notification
				//$notification	= "You have successfully submitted a new support ticket. Case Number: ".$case['ticket_id']."";
				//$link			= "?page=08-01-cc-003&ticket=".$case['ticket_id']."";
			
			add_notification($mLink, $notificationID, $memberID, $notification, $link);
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

	//START FROM VALIDATION
	$error_list = array();
		
	if(empty($_REQUEST['reply'])) {
		$error_list[] = "Please provide <strong>Reply</strong>";
		$tickerSymbolError = true;
	}
	
	if(empty($_REQUEST['ticket_id'])) {
		$error_list[] = "Unable to pass ticket ID, please contact administrator.";
	}
	
	
	
	if(empty($error_list)) {
		
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
		}else{
			$table = $_SESSION['support_feedback_table'];
		}
		
		//Check to see if user wants to submit anonymously
		if($_REQUEST['anonymous'] == "on"){
			$column 		= "anonymous,";
			$columnValue 	= "'1',";	
		}else{
			$column 		= "";
			$columnValue 	= "";	
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
				':member_id'		=> $_SESSION['member_id'],
				':ticket_id'		=> $_REQUEST['ticket_id'],
				':response'			=> $_REQUEST['reply'],
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
				$ticketType = get_ticket($mLink, $_REQUEST['ticket_id'], 'type');
				
				switch($ticketType){
					case "2":$notificationID = "12-003";break;//Corporate Action
					case "3":$notificationID = "08-002";break;//General Help
				}	
				$memberID		= $_REQUEST['ticket_member_id'];
				
				//Custom notification
				$notification	= "".get_member($mLink, $_SESSION['member_id'], "full name")." has responded to your ticket: ".$_REQUEST['ticket_id']."";
				$link			= "?page=08-01-cc-003&ticket=".$_REQUEST['ticket_id']."";
				
				add_notification($mLink, $notificationID, $memberID, $notification, $link);
			}
		}
		//END SYSTEM NOTIFICATION
		
		//START EMAIL NOTIFICATION
		if($_REQUEST['community'] != "1"){
			//if response is not from community do this		
			if($_SESSION['member_id'] != $_REQUEST['ticket_member_id']){
				//If ticket is changed to close, do not send notification, recap email will be sent instead
				if($_REQUEST['close'] != "0"){
					$responder = $fullName;
					$emailID = "3";//Support Response Email
					$ticketID = $_REQUEST['ticket_id'];
					$emailReply = $_REQUEST['reply'];
					include("../../includes/email/support-email.php");
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

//+---------------------------------------------------------------------------------------------------------+
//|									AJAX LOAD RESPONSES - PROCESS 4											|
//+---------------------------------------------------------------------------------------------------------+
if($process == "4"){
	
	$ticketID = $_REQUEST['ticket'];
	
	$query = "
		SELECT m.username, m.name_first, m.name_last, sf.response, sf.timestamp, sf.member_id, sf.ticket_member_id
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
		
		if($reply['ticket_member_id'] == $reply['member_id']){
			$title = "Response by You";
			$titleStyle = "background:#5BC0DE;";	
		}
		
		/*if($_SESSION['admin'] == "1"){
			$title = "Response By ".$fullName."";
		}*/
		
		?>
		<div class="response-container">
        	<div class="response-header" style="<?php echo $titleStyle; ?>">
            	<div class="responder" style="float:left;">
            		<?php echo $title; ?>
            	</div><!--responder-->
                <div class="response-date">
                	<?php echo date('D m/d/Y', $reply['timestamp']);?> at <?php echo date('h:i A', $reply['timestamp']);?>
                </div><!--response-date-->
                <div class="clear"></div>
            </div><!--response-header-->
            
            <div class="response-body">
				<p class="last"><?php echo $response; ?></p>
            </div><!--response-body-->
		</div>
		<?php
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
			SELECT member_id
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
		$ticketType = get_ticket($mLink, $_REQUEST['ticket'], 'type');
			
		switch($ticketType){
			case "2":$notificationID = "12-004";break;//Corporate Action
			case "3":$notificationID = "08-004";break;//General Help
		}
		
		$memberID		= $member['member_id'];
		//Custom notification
		$notification	= "".get_member($mLink, $_REQUEST['admin'], "full name")." has changed the status of your ticket: ".$_REQUEST['ticket'].".";
		$link			= "?page=08-01-cc-903&ticket=".$_REQUEST['ticket']."";
		
		add_notification($mLink, $notificationID, $memberID, $notification, $link);
		//END NOTIFICATION
		
		//START EMAIL NOTIFICATION	
		if($_REQUEST['status'] != '0'){	
			$responder 	= get_member($mLink, $_REQUEST['admin'], "full name");
			$emailID 	= "4";//Support Ticket Status Update Email
			$ticketID 	= $_REQUEST['ticket'];
			$status 	= get_status($mLink, $_REQUEST['status'], "status");
			include("../../includes/email/support-email.php");
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
	
	//START FROM VALIDATION
	$error_list = array();
		
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
			SET notes=:notes
			WHERE ticket_id=:ticket_id
		";
		
		try{
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':notes'		=> addslashes($_REQUEST['notes']),
				':ticket_id'	=> $_REQUEST['ticket_id']
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
	
	$ticketID = $_REQUEST['ticket'];
	
	
	
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
	
	while($reply = $rsResponse->fetch(PDO::FETCH_ASSOC)){
		$response = $reply['response'];
		
		if(get_member($mLink, $reply['member_id'], 'admin') == "1"){
			$isAdmin = "(Staff)";	
		}else{
			$isAdmin = "";	
		}
		
		if($reply['member_id'] == $reply['ticket_member_id']){
			//orginal poster
			if($reply['anonymous'] == "1"){
				if($_SESSION['admin'] == "1"){
					$title = "Response By Anonymous (".get_member($mLink, $reply['member_id'], 'full name').") (Original Poster)";
					$titleStyle = "background:#414247;";
				}else{
					$title = "Response By Anonymous (Original Poster)";
					$titleStyle = "background:#414247;";
				}
			}else{
				$title = "Response By ".get_member($mLink, $reply['member_id'], 'full name')." (Original Poster) ".$isAdmin."";
				$titleStyle = "background:#414247;";
			}
		}else{
			//not original poster
			if($reply['anonymous'] == "1"){
				if($_SESSION['admin'] == "1"){
					$title = "Response By Anonymous (".get_member($mLink, $reply['member_id'], 'full name').")";
					$titleStyle = "background:#414247;";
				}else{
					$title = "Response By Anonymous";
					$titleStyle = "background:#414247;";
				}
			}else{
				$title = "Response By ".get_member($mLink, $reply['member_id'], 'full name')." ".$isAdmin."";
				$titleStyle = "background:#414247;";
			}
		}
		
		//Check to see if the user is looking at his/her own response
		if($_SESSION['member_id'] == $reply['member_id']){
			//Check to see if user is the original poster
			if($reply['member_id'] == $reply['ticket_member_id']){
				$title = "Response by You (Original Poster) ".$isAdmin."";
				$titleStyle = "background:#ccc8c8;color:#000000;";
			}else{
				$title = "Response by You ".$isAdmin."";
				$titleStyle = "background:#ccc8c8;color:#000000;";
			}
		}
		
		$aAgree 		= explode("|", $reply['agree']);
		$aDisagree 	= explode("|", $reply['disagree']);
		
		$cntAgree = 0;
		if($aAgree[0] != ""){
			$cntAgree = count($aAgree);
		}
		
		$cntDisagree = 0;
		if($aDisagree[0] != ""){
			$cntDisagree = count($aDisagree);
		}
		
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
								echo '<li><a href="javascript:void(0);">
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
		
		if($cntDisagree > 0){
			
			if($cntDisgree <= 1){
				$popTitle = "Member disagrees with this reply.";	
			}else{
				$popTitle = "Members agree with this reply.";	
			}
		?>
			<!-- BEGIN RESPONSE DISAGREE MODAL-->
            <div class="modal fade" id="disagree-<?php echo $reply['uid'];?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                 <div class="modal-content">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title"><?php echo $cntDisagree;?> Members disagree to this reply.</h4>
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
								echo '<li><a href="javascript:void(0);">
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
		
		/*if($_SESSION['admin'] == "1"){
			$title = "Response By ".$fullName."";
		}*/
		
		?>
		<div class="response-container">
        	<div class="response-header" style="<?php echo $titleStyle; ?>">
            	<div class="responder" style="float:left;">
            		<?php echo $title; ?>
            	</div><!--responder-->
                <div class="response-date">
                	<?php echo date('D m/d/Y', $reply['timestamp']);?> at <?php echo date('h:i A', $reply['timestamp']);?>
                </div><!--response-date-->
                <div class="clear"></div>
            </div><!--response-header-->
            
            <div class="response-body">
            	<div class="response">
					<p class="last"><?php echo $response; ?></p>
                    
                </div><!--response-->
                
                <div class="response-actions">
                	
                    <?php 
					if($cntAgree != 0){
					
						// Search to see if current user has already pressed agree
						foreach($aAgree as $key=>$value){
							// Eplode array into multidimensional array
							$aAgreeMember[$key] = explode('-', $value);
						}
						
						$arrayKey = searchForId($_SESSION['member_id'], $aAgree);
						
						//print_r($arrayKey);
						
						//print_r($aAgreeMember[$arrayKey]);
						
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
								
						}
					
					?>
                    	<button type="button" class="btn btn-<?php echo $btnStatus;?> btn-xs" onClick="agreeDisagree('agree','<?php echo $reply['uid'];?>')" title="<?php echo $btnTitle;?>"><?php echo $btnType; ?></button>
                    <?php /*?><div class="btn-group dropup">
                        <button type="button" class="btn btn-<?php echo $btnStatus;?> btn-xs" onClick="agreeDisagree('agree','<?php echo $reply['uid'];?>')" title="<?php echo $btnTitle;?>"><?php echo $btnType; ?></button>
                        <button type="button" class="btn btn-<?php echo $btnStatus;?> btn-xs dropdown-toggle" data-toggle="dropdown"><i class="icon-angle-up"></i></button>
                        <ul class="dropdown-menu" role="menu">
                        	<li class="drop-header"><?php echo $cntAgree;?> Members Agree</li>
							<?php
							// Set counter to zero
							$cnt = 0;
							// echo each list item in array
							foreach($aAgree as $data){
								// Increment counter by 1 each time
								$cnt = $cnt + 1;
								
								// limit the number of loops to 5
								if($cnt <= 5){
									// Expand Member Data into an array
									$userAgree	= explode("-", $data);
									
									// Get member info
									$memberAgree = get_member($mLink, $userAgree[0], 'full name');
									
									// Get Time past from timestamp
									$timestampAgree = time_past($userAgree[1]);
									
									// Echo list item
									echo '<li><a href="javascript:void(0);">'.$memberAgree.'</a></li>';
								}
								
							}
							
							if($cnt > 5){
							?>
                            	<li class="divider"></li>
                            	<li><a href="#agree-<?php echo $reply['uid'];?>" data-toggle="modal"><small>View All Who Agree</small></a></li>
                            <?php
							}
							?>
                        </ul>
                    </div><?php */?>
                    <?php
					}else{?>
						<button type="button" class="btn btn-success btn-xs" onClick="agreeDisagree('agree','<?php echo $reply['uid'];?>')">Agree</button>
					<?php
                    }
					
					if($cntDisagree != 0){
						
						// Search to see if current user has already pressed agree
						foreach($aDisagree as $key=>$value){
							// Eplode array into multidimensional array
							$aDisagreeMember[$key] = explode('-', $value);
						}
						
						$arrayKey = searchForId($_SESSION['member_id'], $aDisagree);
						
						//print_r($arrayKey);
						
						//print_r($aAgreeMember[$arrayKey]);
						
						if($aDisagreeMember[$arrayKey][0] == $_SESSION['member_id']){
							//Found Member
							//echo "FOUND IT";
							$replyStatusAgree = "you";
							$btnStatus = "default";
							$btnType = "You Agree";
							$btnTitle = "Click here to undo.";
							
							if($cntDisagree == 1){
								$replyStatusDisagree = "single-you";
									
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
								
						}
						
					?>
                    <div class="btn-group dropup">
                        <button type="button" class="btn btn-info btn-xs" onClick="agreeDisagree('disagree','<?php echo $reply['uid'];?>')">(<?php echo $cntDisagree;?>) Disagree</button>
                        <?php /*?><button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown"><i class="icon-angle-up"></i></button>
                        <ul class="dropdown-menu" role="menu">
                            <li class="drop-header"><?php echo $cntDisagree;?> Members Disgree</li>
							<?php
							// Set counter to zero
							$cnt = 0;
							// echo each list item in array
							foreach($aDisagree as $data){
								// Increment counter by 1 each time
								$cnt = $cnt + 1;
								
								// limit the number of loops to 5
								if($cnt <= 5){
									// Expand Member Data into an array
									$user	= explode("-", $data);
									
									// Get member info
									$member = get_member($mLink, $user[0], 'full name');
									
									// Get Time past from timestamp
									$timestampAgree = time_past($user[1]);
									
									// Echo list item
									echo '<li><a href="javascript:void(0);">'.$member.'</a></li>';
								}
								
							}
							
							if($cnt > 5){
							?>
                            	<li class="divider"></li>
                            	<li><a href="#disagree-<?php echo $reply['uid'];?>" data-toggle="modal"><small>View All Who Disagree</small></a></li>
                            <?php
							}
							?>
                        </ul>
                    </div><?php */?>
                    <?php
					}else{?>
						<button type="button" class="btn btn-info btn-xs" onClick="agreeDisagree('disagree','<?php echo $reply['uid'];?>')"><?php if($cntDisagree != 0){echo "(".$cntDisagree.") ";}?> Disagree</button>
					<?php
                    }
					
					?>
                    
                    
                </div><!--response-actions-->
                <div class="clear"></div><!--clear-->
            </div><!--response-body-->
            
            <?php 
			
			switch($replyStatusAgree){
				case "single":$statusMessage = '<a href="#agree-'.$reply['uid'].'" data-toggle="modal">'.$memberAgree.'</a> agrees with this.';break;
				case "single-you":$statusMessage = '<a href="#agree-'.$reply['uid'].'" data-toggle="modal">You</a> agree with this.';break;
				case "you":$statusMessage = 'You, and <a href="#agree-'.$reply['uid'].'" data-toggle="modal">'.($cntAgree - 1).' others</a> agree with this.';break;
				case "others":$statusMessage = '<a href="#agree-'.$reply['uid'].'" data-toggle="modal">'.$cntAgree.' others</a> agree with this.';break;
				default: $statusMessage = '';
					
			}
			
			if($cntAgree > 0){
			?>
                <div class="agree-disagree-area">
                    
                    <p><small><?php echo $statusMessage;?></small></p>
                   
                </div>
            <?php
			}
			?>
            
            
		</div>
		<?php
	}
	
}

//+---------------------------------------------------------------------------------------------------------+
//|								COMMUNITY AGREE/DISAGREE - PROCESS 11										|
//+---------------------------------------------------------------------------------------------------------+
if($process == "11"){
	//Get Vars from click function
	$uid 		= $_REQUEST['uid'];
	$choice		= $_REQUEST['choice'];
	
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
	
	// Build function for searching Arrays
	/*function searchForId($id, $array) {
	   foreach ($array as $key => $val) {
		   if ($val[0] === $id) {
			   return $key;
		   }
	   }
	   return null;
	}*/
	
	// Branch based on passed choice
	if($choice == "agree"){
		
		// Explode db string into single dimensional array
		$array = explode('|', $agreeCol);
		
		// Loop through each key and explode its value
		foreach($array as $key=>$value){
			// Eplode array into multidimensional array
			$array[$key] = explode('-', $value);
		}
		
		// Search array for users member id (if you print $found, it will display an array with the key and value that was found)
		$found = ($array[searchForId($_SESSION['member_id'], $array)]);
		
		if($found != ""){
			//Member Id in column 
			//echo "Member Id Found";
			
			// Set var for the array key
			$arrayKey = searchForId($_SESSION['member_id'], $array);
			
			// Remove the key from the existing array
			unset($array[$arrayKey]);
			
			// Now step out and implode second level of multidimensional array
			foreach($array as $key=>$value){
				$array[$key] = implode('-', $value);	
			}
			
			// Now implode the array to be able to store in data base
			$newArray = implode('|', $array);
			
			print_r($newArray);
			
		}else{
			//Member Id not in column
			echo "Member ID Not Found";
			
			$date = new DateTime();
			
			$addArray = array($_SESSION['member_id'], $date->getTimestamp());
			
			print_r($addArray);	
			
			array_push($array, $addArray);
			
			print_r($array);
		}
	
	}elseif($choice == "disagree"){
		
		$array = explode('|', $disagreeCol);
		foreach($array as $key=>$value){
			$array[$key] = explode('-', $value);
		}
				
		print_r($array);
	}

}

?>