<?php
// The following "dirty's up" the URL so email clients won't turn it into a clickable hyperlink by adding a zero-width non-breaking space in the protocol and around each dot.
$bad_url = $_SESSION['site_protocol'].'&#65279;://'.str_replace(".", "&#65279;.&#65279;", $_SESSION['base_url']);

// Password reset request email
//$to = $_REQUEST['email'];
$to = decrypt($auth['email']);
//$to = 'jeff@nr.net'; // Testing
$subject = "Marketocracy Password Reset Request";
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

		<p>You, or someone else at IP Address '.$_SERVER['REMOTE_ADDR'].', just now asked to have your Marketocracy password reset.</p>

		<!--[if gte mso 9]>
<!--		<p>To complete your request <a href=\''.$_SESSION['site_root'].'reset-password/'.$token.'\'>CLICK HERE</a> or enter (exactly, with no spaces):<br><br><span style="font-family:courier, serif; font-size:14px; color:#000000; line-height:16px;">'.$_SESSION['base_url'].'reset-password/'.$token.'</span><br><br>in your browser\'s address bar and you will be taken to the secure password change form.</p>-->
		<![endif]-->
		<!--[if !mso]><!-- NOT Outlook -->
<!--		<p>To complete your request <a href=\''.$_SESSION['site_root'].'reset-password/'.$token.'\'>CLICK HERE</a> or enter (exactly, with no spaces):<br><br><span style="font-family:courier, serif; font-size:14px; color:#000000; line-height:16px;"><nolink>'.$bad_url.'reset-password/'.$token.'</nolink></span><br><br>in your browser\'s address bar and you will be taken to the secure password change form.</p>-->
		<!--<![endif]-->
		<p>To complete your request <a href=\''.$_SESSION['site_root'].'reset-password/'.$token.'\'>CLICK HERE</a> or enter (exactly, with no spaces):<br><br><span style="font-family:courier, serif; font-size:14px; color:#000000; line-height:16px;"><nolink>'.$bad_url.'reset-password/'.$token.'</nolink></span><br><br>in your browser\'s address bar and you will be taken to the secure password change form.</p>

		<p>If you did not request this password reset, simply do nothing and your password will not be changed.  If more than an hour has passed since your request you will need to request it again, as it has now expired.</p>

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
$headers .= 'From: Marketocracy Member Services <MemberServices@marketocracy.net>' . "\r\n";
$headers .= 'Bcc: jeff.saunders@marketocracy.com' . "\r\n";  //Testing
//$headers .= 'Bcc: jeff.saunders@marketocracy.com, brandon.mccarthy@marketocracy.com' . "\r\n";  //Testing
//$headers .= 'Cc: someone@marketocracy.com' . "\r\n";
//$headers .= 'Bcc: someone.else@marketocracy.com, someone.outside@otherdomain.com' . "\r\n";

// Mail it
mail($to, $subject, $message, $headers);

?>