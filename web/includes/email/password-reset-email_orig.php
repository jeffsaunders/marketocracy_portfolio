<?php
// More info request email
//$to = $_REQUEST['email'];
$to = 'jeff@nr.net'; // Testing
$subject = "Marketocracy Password Reset Request";
$message = '
<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=Windows-1252">
<title></title>
<html>

<body style="background-color:#4d4f4e; margin:0px; padding:0px;">

<br>
<table width="620" align="center" border="0" cellpadding="0" cellspacing="0" style="margin:0px auto;" bgcolor="#ffffff">
<tr>
	<td colspan="3" align="left" width="620" style="background-image:url(\''.$_SESSION['site_root'].'images/email/email-header-logo.png\'); background-repeat:no-repeat;">
		<!--[if gte mso 9]>
		<img src="'.$_SESSION['site_root'].'images/email/email-header-logo.png" width="650" border="0" alt="Marketocracy - Mastering The Art Of Investing" />
		<![endif]-->
		<!--[if !mso]><!-- -->
		<img src="'.$_SESSION['site_root'].'images/email/spacer.gif" width="620" height="162" border="0" alt="Marketocracy - Mastering The Art Of Investing" />
		<!--<![endif]-->
	</td>
</tr>
<tr>
	<td valign="top" width="30" style="background-image:url(\''.$_SESSION['site_root'].'images/email/email-side-left.png\'); background-repeat:repeat-y; margin:0px auto;">
		<!--[if gte mso 9]>
		<img src="'.$_SESSION['site_root'].'images/email/email-side-left.png" width="34" height="600" border="0" alt="" />
		<![endif]-->
		<!--[if !mso]><!-- -->
		<img src="'.$_SESSION['site_root'].'images/email/spacer.gif" width="30" height="1" border="0" alt="" />
		<!--<![endif]-->
	</td>
	<td valign="top" bgcolor="#ffffff" style="font-family:Arial, Helvetica, sans-serif; font-size:16px; color:#666666; line-height:20px;">
		<h2 style="font-family:Arial, Helvetica, sans-serif; font-size:18px; color:#432f87; margin:0px; padding:0px;"><strong>Hello '.$member['name_first'].',</strong></h2>

		<p>You, or someone else at IP Address '.$_SERVER['REMOTE_ADDR'].', just now asked to have your Marketocracy password reset.</p>

		<p>To complete your request <a href=\''.$_SESSION['site_root'].'reset-password/'.$token.'\'>CLICK HERE</a> or enter (exactly, with no spaces):<br><br><span style="font-family:courier, serif; font-size:14px; color:#000000; line-height:16px;">'.$_SESSION['site_root'].'reset-password/'.$token.'</span><br><br>in your browser\'s address bar and you will be taken to the secure password change form.</p>

		<p>If you did not request this password reset, simply do nothing and your password will not be changed.  If more than an hour has passed since your request you will need to request it again, as it has now expired.</p>

		<p>Thank you.</p>

<!--		<p style="margin:0px; padding:0px;"><img src="'.$_SESSION['site_root'].'images/email/email-pull-quote.png" alt="At Marketocracy, top-flight investment talent is cultivated through a meritocracy where verified track records mean more than resumes and pedigrees and proven abilities are a prerequisite to handling real money." width="365" height="98" border="0" /></p>-->
	</td>
	<td valign="top" width="30" style="background-image:url(\''.$_SESSION['site_root'].'images/email/email-side-right-full.png\'); background-repeat:no-repeat; margin:0px auto;">
		<!--[if gte mso 9]>
		<img src="'.$_SESSION['site_root'].'images/email/email-side-right-full.png" width="34" height="600" border="0" alt="" />
		<![endif]-->
		<!--[if !mso]><!-- -->
		<img src="'.$_SESSION['site_root'].'images/email/spacer.gif" width="30" height="1" border="0" alt="" />
		<!--<![endif]-->
	</td>
</tr>
<tr>
	<td colspan="3" align="left" style="background-image:url(\''.$_SESSION['site_root'].'images/email/email-divider.png\'); background-repeat:no-repeat; margin:0px auto;">
		<!--[if gte mso 9]>
		<img src="'.$_SESSION['site_root'].'images/email/email-divider.png" width="650" border="0" alt="" />
		<![endif]-->
		<!--[if !mso]><!-- -->
		<img src="'.$_SESSION['site_root'].'images/email/spacer.gif" width="620" height="35" border="0" alt="" />
		<!--<![endif]-->
	</td>
</tr>
<tr>
	<td valign="top" style="background-image:url(\''.$_SESSION['site_root'].'images/email/email-side-left.png\'); background-repeat:repeat-y; margin:0px auto;">
		<!--[if gte mso 9]>
		<img src="'.$_SESSION['site_root'].'images/email/email-side-left.png" width="34" height="65" border="0" alt="" />
		<![endif]-->
		<!--[if !mso]><!-- -->
		<img src="'.$_SESSION['site_root'].'images/email/spacer.gif" width="30" height="1" border="0" alt="" />
		<!--<![endif]-->
	</td>
	<td width="560" bgcolor="#ffffff" valign="top" style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#666666; text-align:center;">
		&copy; 2001-'.date("Y").' <a href="http://marketocracy.com" target="_blank" style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#666666; text-decoration:none;">Marketocracy</a>, Inc. All rights reserved.<br>
		1208 West Magnolia Ave &middot; Suite 236 &middot; Fort Worth, TX 76104<br><br>
	</td>
	<td valign="top" style="background-image:url(\''.$_SESSION['site_root'].'images/email/email-side-right-full.png\'); background-repeat:repeat-y; margin:0px auto;">
		<!--[if gte mso 9]>
		<img src="\''.$_SESSION['site_root'].'images/email/email-side-right-full.png\'" width="34" height="65" border="0" alt="" />
		<![endif]-->
		<!--[if !mso]><!-- -->
		<img src="'.$_SESSION['site_root'].'images/email/spacer.gif" width="30" height="1" border="0" alt="" />
		<!--<![endif]-->
	</td>
</tr>
</table>
<br>

</body>
</html>
';
die($message);
// To send HTML mail, the Content-type header must be set
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

// Additional headers
$headers .= 'From: Marketocracy <rachel.oneal@marketocracy.com>' . "\r\n";
$headers .= 'Bcc: jeff.saunders@marketocracy.com' . "\r\n";  //Testing
//$headers .= 'Bcc: jeff.saunders@marketocracy.com, brandon.mccarthy@marketocracy.com' . "\r\n";  //Testing
//$headers .= 'Cc: someone@marketocracy.com' . "\r\n";
//$headers .= 'Bcc: someone.else@marketocracy.com, someone.outside@otherdomain.com' . "\r\n";

// Mail it
mail($to, $subject, $message, $headers);

?>