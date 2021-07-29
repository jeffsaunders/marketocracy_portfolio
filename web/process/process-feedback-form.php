<?php
// Display errors - debugging
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");


// Connect to the database(s)
require("dbconnect.php");

// Let's see if the submission came from a human or a 'bot
$fails = 0; // $fails > 0 = 'bot!
if (isset($_REQUEST['middle_name'])){ $fails++; } // middle_name value sent - honeypot trapped!
if ($_REQUEST['check'] != ""){  // We got a "check" value
	if (!is_numeric($_REQUEST['check'])){  // Is it a number?
		$fails++;  // Honeypot trapped!
	}else if ($_REQUEST['check'] < 1 || $_REQUEST['check'] > 9){
		$fails++;  // Honeypot trapped!
	}
}

// Track 'em
if ($fails > 0){
	$query = "
		UPDATE info_stats
		SET honeypot_traps = honeypot_traps + 1
	";
	$rsUpdate = $mLink->prepare($query);
	$rsUpdate->execute();
}

// Now let's see if they defeated the validation and submitted a blank form instead
if (strpos($_REQUEST['email'], '@') == false){ $fails++; } // email cannot be blank - likely 'bot direct submit!

// Track those too
if ($fails > 0){
	$query = "
		UPDATE info_stats
		SET blank_form_traps = blank_form_traps + 1
	";
	$rsUpdate = $mLink->prepare($query);
	$rsUpdate->execute();
}

if ($fails == 0){  // Submitted by a human, process it!
//	session_start();

	// Set the timezone
	date_default_timezone_set('America/Chicago');

//print "<pre>";
//print_r($_REQUEST);
//print "</pre>";
//die();

	

	// Insert form data
	$query = "
		INSERT INTO demo_feedback (
			first_name,
			last_name,
			email,
			phone,
			feedback,
			feedback_topic,
			page_title,
			page_source,
			referral_source,
			browser,
			timestamp
		) VALUE (
			:first_name,
			:last_name,
			:email,
			:phone,
			:feedback,
			:feedback_topic,
			:page_title,
			:page_source,
			:referral_source,
			:browser,
			UNIX_TIMESTAMP()
		)
	";
	$rsInfo = $mLink->prepare($query);
	$rsInfo->execute(array(
		':first_name'		=> addslashes($_REQUEST['first_name']),
		':last_name'		=> addslashes($_REQUEST['last_name']),
		':email'			=> $_REQUEST['email'],
		':phone'			=> $_REQUEST['phone'],
		':feedback'	    	=> addslashes($_REQUEST['feedback']),
		':feedback_topic'	=> addslashes($_REQUEST['feedback_topic']),
		':page_title'		=> addslashes($_REQUEST['page_title']),
		':page_source'		=> addslashes($_REQUEST['redirect']),
		':referral_source'  => addslashes($_REQUEST['referral_source']),
		':browser'			=> addslashes ($_REQUEST['browser'])
	));

	// Build the notification email
	//$to = 'MemberServices@marketocracy.com';
	$to = 'brandon.mccarthy@marketocracy.com'; // Testing
	$subject = 'Marketocracy Feedback Form Submission';
	$message = '
	<meta http-equiv="Content-Type" content="text/html; charset=Windows-1252">
	<title></title>
	<html>
	<body>
	<table width="760" cellspacing="0" cellpadding="3" style="font-family:Arial, Helvetica, sans-serif; font-size:12px;">
	<tr>
		<td colspan="2">
			<font size="+2"><strong>Marketocracy DEMO Feedback Form Submission</strong></font><br>
			Submitted: '.date('m/d/Y @ g:i A T').'
			<br><hr width="100%" size="2" noshade>
		</td>
	</tr>
	<tr>
		<td width="125" valign="top">Name:</td>
		<td width="635">
			<strong>'.trim($_REQUEST['first_name']).'&nbsp;'.trim($_REQUEST['last_name']).'</strong>
		</td>
	</tr>
	<tr>
		<td>Email:</td>
		<td><strong>'.$_REQUEST['email'].'</strong></td>
	</tr>
	<tr>
		<td>Phone:</td>
		<td><strong>'.$_REQUEST['phone'].'</strong></td>
	</tr>
	<br><br>
	<tr>
		<td valign="top">Feedback Topic:</td>
		<td>'.$_REQUEST['feedback_topic'].'</td>
	</tr>
	';
	
	if ($_REQUEST['feedback'] != ""){
		$message .= '
	<tr>
		<td valign="top">Feedback:</td>
		<td><strong>'.$_REQUEST['feedback'].'</strong></td>
	</tr>
		';
	}
	$message .= '
	<br><br>
	<tr>
		<td valign="top">Page Title:</td>
		<td>'.$_REQUEST['page_title'].'</td>
	</tr>
	<tr>
		<td valign="top">Page URL:</td>
		<td>'.$_REQUEST['redirect'].'</td>
	</tr>
	<tr>
		<td valign="top">Referral Source:</td>
		<td>'.$_REQUEST['referral_source'].'</td>
	</tr>
	<br><br>
	<tr>
		<td valign="top">Browser Info:</td>
		<td>'.$_REQUEST['browser'].'</td>
	</tr>
	</table>
	</body>
	</html>
	';
	//die($message);
	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

	// Additional headers
	$headers .= 'From: Marketocracy Feedback Form <MemberServices@marketocracy.com>' . "\r\n";
	$headers .= 'Bcc: jeff.saunders@marketocracy.com, rachel.oneal@marketocracy.com' . "\r\n";  //Testing
//	$headers .= 'Cc: someone@marketocracy.com' . "\r\n";
//	$headers .= 'Bcc: someone.else@marketocracy.com, someone.outside@otherdomain.com' . "\r\n";

	// Mail it
	mail($to, $subject, $message, $headers);

	// Now determine which follow-up email to send
	/*$boxes_checked = 0;
	$aBoxNames = array("more_info", "track_performance", "call_me", "open_acct");
	for ($checkCount = 1; $checkCount <= sizeof($aBoxNames); $checkCount++){
		if (isset($_REQUEST[$aBoxNames[$checkCount-1]]) && $_REQUEST[$aBoxNames[$checkCount-1]] == "Y"){
			$boxes_checked++;
		}
	}

	// Include the right one
	if (($_REQUEST['more_info'] == "Y" && $boxes_checked == 1) || $boxes_checked == 0 || $boxes_checked > 1){
		include("more-info-email.php");
	}else if ($_REQUEST['track_performance'] == "Y"){
		include("track-email.php");
	}else if ($_REQUEST['call_me'] == "Y"){
		include("contact-email.php");
	}else if ($_REQUEST['open_acct'] == "Y"){
		include("open-acct-email.php");
	}*/
	
	
	
/*die();*/
}



// Go where I told you to
$stringtocheck = $_REQUEST['redirect'];

$checkForSymbol = '?';

if(strpos($stringtocheck, $checkForSymbol) !== false)
{
	$messageString = "&feedback=success";
}else{
	$messageString = "?feedback=success";	
}

header('Location: ' . $_REQUEST['redirect'].$messageString);

?>
