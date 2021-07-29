<?php
//EMAIL TEMPLATE FOR SYSTEM EMAILS

	
			
//START EMAIL
$adminMessage = '
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Windows-1252">
<title>Marketocracy Email Campaign Info</title>

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
        
        <td><h2 style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; margin:0px 0px 3px 0px; padding:0px;">Portfolio Email Campaign System</h2></td>
        
        <td align="right"><h2 style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; margin:0px 0px 3px 0px; padding:0px;"><?php echo date("F j, Y");?></h2></td>
        
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
        <td valign="top" colspan="2" bgcolor="#ffffff" style="font-family:Arial, Helvetica, sans-serif; font-size:16px; color:#666666; line-height:20px;">
            <h2 style="font-family:Arial, Helvetica, sans-serif; font-size:18px; color:#242527; margin:0px; padding:0px;"><strong>Email Campaign Info</strong></h2>
            
			<table width="550" align="center" border="1" cellpadding="10" cellspacing="0" style="margin:0px auto;" bgcolor="#ffffff">
				<tr>
					<td width="50%" valign="top">Campaign Name:</td>
					<td width="50%">'.$emailTitle.'</td>
				</tr>
				<tr>
					<td width="50%" valign="top">Campaign Status:</td>
					<td width="50%">Sent</td>
				</tr>
				<tr>
					<td width="50%" valign="top">Email Subject:</td>
					<td width="50%">'.$emailSubject.'</td>
				</tr>
				<tr>
					<td width="50%" valign="top">Recipients:</td>
					<td width="50%">'.count($aRecipients).'</td>
				</tr>
				<tr>
					<td width="50%" valign="top">Unsubscribes:</td>
					<td width="50%">'.count($aUnsubscribed).'</td>
				</tr>
			</table>
    		';
            
			//reopen message
            $adminMessage .= '
			
			<hr />
			<h2>Email</h2>
			<br />
			<div style="border:1px solid #000000;">
			'.$message.'
			</div>
            <br />
			<img src="'.$_SESSION['base_url'].'includes/email/tracking.php?track=true[OPEN_CODE]" alt="" /><!--[TRACK_ID]-->
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