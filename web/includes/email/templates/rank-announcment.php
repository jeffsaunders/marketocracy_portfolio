<?php
//EMAIL TEMPLATE FOR SYSTEM EMAILS


if($preview == true){
	$trackLink = '';
}else{
	$trackLink = '<img src="https://'.$_SESSION['base_url'].'includes/email/tracking.php?[OPEN_LINK]" alt="" /><!--[TRACK_ID]-->';
	$showTrackLink	= '[OPEN_LINK]';
}

if($emailHeadline != ''){
	
	$headline = '<h2 style="font-family:Arial, Helvetica, sans-serif; font-size:18px; color:#242527; margin:0px 0px 10px 0px; padding:0px;"><strong>'.$emailHeadline.'</strong></h2>';
	
}else{
	
	$headline = '';
}
			
//START EMAIL
$message = '
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Windows-1252">
<title>'.$emailTitle.'</title>

</head>

<body style="background-color:#4d4f4e; margin:0px; padding:0px;">

<table width="600" align="center" border="0" cellpadding="0" cellspacing="0" style="margin:0px auto;" bgcolor="#ffffff">
    <tr bgcolor="#00bdee">
		<td colspan="4" align="left" width="600" style="background-image:url(\'https://'.$_SESSION['base_url'].'includes/email/images/system-email/header-system.png\'); background-repeat:no-repeat;">
		<!--[if gte mso 9]>
		<img src="https://'. $_SESSION['base_url'].'includes/email/images/system-email/header-system.png" width="600" height="45" border="0" alt="" />
		<![endif]-->
		<!--[if !mso]><!-- -->
		<img src="https://'.$_SESSION['base_url'].'includes/email/images/system-email/spacer.gif" width="600" height="45" border="0" alt="Marketocracy" />
		<!--<![endif]-->
		</td>
	</tr>
    <tr bgcolor="#00bdee">
    	<td width="15"><!--[if !mso]><!-- -->
		<img src="https://'.$_SESSION['base_url'].'includes/email/images/system-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
        
        <td><h2 style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; margin:0px 0px 3px 0px; padding:0px;">Portfolio Trading Platform</h2></td>
        
        <td align="right"><h2 style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; margin:0px 0px 3px 0px; padding:0px;">'.date("F j, Y").'</h2></td>
        
        <td width="15"><!--[if !mso]><!-- -->
		<img src="https://'.$_SESSION['base_url'].'includes/email/images/system-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
    </tr>
    <tr>
    	<td colspan="4">
        <!--[if !mso]><!-- -->
		<img src="https://'.$_SESSION['base_url'].'includes/email/images/system-email/spacer.gif" width="600" height="20" border="0" alt="Marketocracy" />
		<!--<![endif]-->
        </td>
    </tr>
    <tr>
    	<td width="15"><!--[if !mso]><!-- -->
		<img src="https://'.$_SESSION['base_url'].'includes/email/images/system-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
        <td style="font-family: Arial, Helvetica, sans-serif; font-size: 16px; color: #555555; line-height: 20px;" colspan="2" valign="top">
            '.$headline.'
			
			<p>Here is how your funds performed for the Q4 2017 period.</p>
			
			[RANK_TABLE]
			
			
			<br /><br />
			
            '.$emailBody.'
    		';
            
			//reopen message
            $message .= '
			
			'.$emailClosing.'
            <br />
			'.$trackLink.'
        </td>
        <td width="15"><!--[if !mso]><!-- -->
		<img src="https://'.$_SESSION['base_url'].'includes/email/images/system-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
    </tr>
    <tr bgcolor="#242527">
    	<td colspan="4">
        <!--[if !mso]><!-- -->
		<img src="https://'.$_SESSION['base_url'].'includes/email/images/system-email/spacer.gif" width="600" height="5" border="0" alt="Marketocracy" />
		<!--<![endif]-->
        </td>
    </tr>
    <tr bgcolor="#242527">
        <td width="15"><!--[if !mso]><!-- -->
            <img src="https://'.$_SESSION['base_url'].'includes/email/images/system-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
            <!--<![endif]--></td>
        <td width="570" colspan="2"  valign="top" colspan="3" style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; text-align:center;">
            &copy; 2001-'.date("Y").' '.$emailFooter.'
        </td>
        <td width="15"><!--[if !mso]><!-- -->
            <img src="https://'.$_SESSION['base_url'].'includes/email/images/system-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
            <!--<![endif]--></td>
    </tr>

</table><!--main-wrapper-->

</body>
</html>
';

?>