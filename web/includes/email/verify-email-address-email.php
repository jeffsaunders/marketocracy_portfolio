<?php
// The following "dirty's up" the URL so email clients won't turn it into a clickable hyperlink by adding a zero-width non-breaking space in the protocol and around each dot.
$bad_url = $_SESSION['site_protocol'].'&#65279;://'.str_replace(".", "&#65279;.&#65279;", $_SESSION['base_url']);

// Create the token if it's not already set
if (!isset($token)){
	$auth_string = encrypt($_REQUEST['email']);
	$token = json_encode($auth_string);
}

// Change task value is this is a resend so the right values are embedded into the email
if ($task == "resend-welcome-email"){
	$task = "create_acct";
}

// Email address change verification email
$to = $_REQUEST['email'];
//$to = 'jeff@nr.net'; // Testing
$subject = "Marketocracy Email Address Verification";
$message = '
<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=Windows-1252">
<title></title>
<html>

<body style="background-color:#4d4f4e; margin:0px; padding:0px;">

<br>
<table width="620" align="center" border="0" cellpadding="0" cellspacing="0" style="margin:0px auto;" bgcolor="#ffffff">
<!-- Header -->
<tr>
	<td style="padding:10px;">
		<img src="'.$_SESSION['site_root'].'images/email/email-logo-small.png" width="300" height="75" border="0" alt="Marketocracy - Mastering The Art Of Investing" />
	</td>
</tr>
<!-- Header Bar -->
<tr>
	<td width="620" height="33" style="background-color:006ea5; background-image:url(\''.$_SESSION['site_root'].'images/email/email-header-bar-bg.png\'); background-repeat:repeat-x;">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<!--[if gte mso 9]>
			<td width="100%" height="33" bgcolor="#036EA5">&nbsp;</td>
			<![endif]-->
			<!--[if !mso]><!-- NOT Outlook -->
			<td>
				<img src="'.$_SESSION['site_root'].'images/email/email-header-bar-ko.png" width="6" height="33" border="0" />
			</td>
			<td align="right">
				<img src="'.$_SESSION['site_root'].'images/email/email-header-bar-ko.png" width="6" height="33" border="0" />
			</td>
			<!--<![endif]-->
		</tr>
		</table>
	</td>
</tr>
<!-- Body -->
<tr>
	<td valign="top" bgcolor="#ffffff" style="font-family:Arial, Helvetica, sans-serif; font-size:16px; color:#666666; line-height:20px; padding:20px;">

		<h2 style="font-family:Arial, Helvetica, sans-serif; font-size:18px; color:#432f87; margin:0px; padding:0px;"><strong>Hello '.$member['name_first'].',</strong></h2>

';
if ($task == "create_acct"){
$message .= '
		<p>Thank you for joining Marketocracy!</p>
';
}else{
$message = '
		<p>We see you\'ve changed your email address.</p>
';
}
$message = '

		<!--[if gte mso 9]>
		<p>To '.($task == "create_acct" ? "activate your new membership" : "complete your change request").' <a href=\''.$_SESSION['site_root'].'verify-email/'.$token.'\'>CLICK HERE</a> or enter (exactly, with no spaces):<br><br><span style="font-family:courier, serif; font-size:14px; color:#000000; line-height:16px;">'.$_SESSION['base_url'].'verify-email/'.$token.'</span><br><br>in your browser\'s address bar and your '.($task == "create_acct" ? "account will be activated" : "change will be completed").'.</p>
		<![endif]-->
		<!--[if !mso]><!-- NOT Outlook -->
		<p>To '.($task == "create_acct" ? "activate your new membership" : "complete your change request").' <a href=\''.$_SESSION['site_root'].'verify-email/'.$token.'\'>CLICK HERE</a> or enter (exactly, with no spaces):<br><br><span style="font-family:courier, serif; font-size:14px; color:#000000; line-height:16px;"><nolink>'.$bad_url.'verify-email/'.$token.'</nolink></span><br><br>in your browser\'s address bar and your '.($task == "create_acct" ? "account will be activated" : "change will be completed").'.</p>
		<!--<![endif]-->

		<p>Thank you.</p>

	</td>
</tr>
<!-- Divider Bar -->
<tr>
	<td width="620" height="18" style="background-color:FFFFFF; background-image:url(\''.$_SESSION['site_root'].'images/email/email-divider-bg.png\'); background-repeat:repeat-x;">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<!--[if gte mso 9]>
			<td width="100%" height="18"><hr style="width: 90%; height: 1px; background-color: #000000; border: none"><br></td>
			<![endif]-->
			<!--[if !mso]><!-- -->
			<td>
				<img src="'.$_SESSION['site_root'].'images/email/email-divider-ko.png" width="30" height="18" border="0" />
			</td>
			<td align="right">
				<img src="'.$_SESSION['site_root'].'images/email/email-divider-ko.png" width="30" height="18" border="0" />
			</td>
			<!--<![endif]-->
		</tr>
		</table>
	</td>
</tr>
<!-- Footer -->
<tr>
	<td width="560" bgcolor="#ffffff" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#666666; text-align:center;">
		&copy; 2001-'.date("Y").' <a href="http://marketocracy.com" target="_blank" style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#666666; text-decoration:none;">Marketocracy</a>, Inc. All rights reserved.<br>
		1208 West Magnolia Ave &middot; Suite 236 &middot; Fort Worth, TX 76104<br><br>
	</td>
</tr>
</table>
<br><br>

</body>
</html>
';
//die($message);
// To send HTML mail, the Content-type header must be set
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

// Additional headers
$headers .= 'From: Marketocracy Member Services<MemberServices@marketocracy.com>' . "\r\n";
$headers .= 'Bcc: jeff.saunders@marketocracy.com' . "\r\n";  //Testing
//$headers .= 'Bcc: jeff.saunders@marketocracy.com, brandon.mccarthy@marketocracy.com' . "\r\n";  //Testing
//$headers .= 'Cc: someone@marketocracy.com' . "\r\n";
//$headers .= 'Bcc: someone.else@marketocracy.com, someone.outside@otherdomain.com' . "\r\n";

// Mail it
mail($to, $subject, $message, $headers);

?>