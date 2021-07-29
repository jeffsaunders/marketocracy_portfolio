<?php
//EMAIL TEMPLATE FOR SYSTEM EMAILS

if($_REQUEST['show-email'] == 1){
	session_start();
}

		
if($preview == true){
	$trackLink = '';
}else{
	$trackLink = '<img src="https://portfolio.marketocracy.com/includes/email/tracking.php?[OPEN_LINK]" alt=""  /><!--[TRACK_ID]-->';
}		

if($emailHeadline != ''){
	
	$headline = '<h2 style="font-family:Arial, Helvetica, sans-serif; font-size:18px; color:#242527; margin:10px 0px 10px 0px; padding:0px;"><strong>'.$emailHeadline.'</strong></h2>';
	
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
		<td colspan="4" align="left" width="600" style="background-image:url(\'https://portfolio.marketocracy.com/includes/email/images/mytrackrecord/mytrackrecord-header.png\'); background-repeat:no-repeat;">
		<!--[if gte mso 9]>
		<img src="https://portfolio.marketocracy.com/includes/email/images/mytrackrecord/mytrackrecord-header.png" width="600" height="45" border="0" alt="" />
		<![endif]-->
		<!--[if !mso]><!-- -->
		<img src="https://portfolio.marketocracy.com/includes/email/images/system-email/spacer.gif" width="600" height="45" border="0" alt="Marketocracy" />
		<!--<![endif]-->
		</td>
	</tr>
    <tr bgcolor="#00bdee">
    	<td width="15"><!--[if !mso]><!-- -->
		<img src="https://portfolio.marketocracy.com/includes/email/images/system-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
        
        <td><h2 style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; margin:0px 0px 3px 0px; padding-top:10px; padding-bottom:5px;">Monthly Performance Update</h2></td>
        
        <td align="right"><h2 style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; margin:0px 0px 3px 0px; padding:0px;"><?php echo date("F j, Y");?></h2></td>
        
        <td width="15"><!--[if !mso]><!-- -->
		<img src="https://portfolio.marketocracy.com/includes/email/images/system-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
    </tr>
    <tr>
    	<td colspan="4">
        <!--[if !mso]><!-- -->
		<img src="https://portfolio.marketocracy.com/includes/email/images/system-email/spacer.gif" width="600" height="20" border="0" alt="Marketocracy" />
		<!--<![endif]-->
        </td>
    </tr>
    <tr>
    	<td width="15"><!--[if !mso]><!-- -->
		<img src="https://portfolio.marketocracy.com/includes/email/images/system-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
        <td valign="top" colspan="2" bgcolor="#ffffff" style="font-family:Arial, Helvetica, sans-serif; font-size:16px; color:#666666; line-height:20px;">
            '.$headline.'
			<h3>Fund Performance</h3>
            <p>'.$emailBody.'</p>
    		<p>'.$fundPerfTable.'</p>
			
			
			'.$showArticle.'
			';
            $message .= '';
			$message .= '
				<hr style="width:560px; height:1px; background-color:#EEEEEE; border:none">
				<h3>Start Investing</h3>
				<p>Our model portfolios are available to Marketocracy Capital Management clients in our Separately Managed Account (SMA) program. For more information on our SMA program, please click the "Learn More" link below. If you would rather schedule a time to discuse opportunities over the phone, click the "Schedule A Call" link below to schedule a One on One with Ken Kam.</p>
				<br />
				<p style="text-align:center;"><a href="https://marketocracy.com/?pmessage=I would like to open an account.&open=1#contact" target="_blank" style="background:#00bdee;padding:10px;color:#ffffff;border-radius:3px;text-decoration:none;"><strong>Learn More</strong></a> <a href="https://marketocracy.setmore.com" target="_blank" style="background:#00bdee;padding:10px;color:#ffffff;border-radius:3px;text-decoration:none;"><strong>Schedule A Call</strong></a></p>
				
				<br />
				
				<hr style="width:560px; height:1px; background-color:#EEEEEE; border:none">
				';
			
			//reopen message
            $message .= '
			
			<p>'.$emailClosing.'</p>
            <br />
			'.$trackLink.'
			
        </td>
        <td width="15"><!--[if !mso]><!-- -->
		<img src="https://portfolio.marketocracy.com/includes/email/images/system-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
    </tr>
    <tr bgcolor="#242527">
    	<td colspan="4">
        <!--[if !mso]><!-- -->
		<img src="https://portfolio.marketocracy.com/includes/email/images/system-email/spacer.gif" width="600" height="5" border="0" alt="Marketocracy" />
		<!--<![endif]-->
        </td>
    </tr>
    <tr bgcolor="#242527">
        <td width="15"><!--[if !mso]><!-- -->
            <img src="https://portfolio.marketocracy.com/includes/email/images/system-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
            <!--<![endif]--></td>
        <td width="570" colspan="2"  valign="top">
		<p style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; text-align:center;padding:0px;">&copy; 2001-'.date("Y").' MyTrackRecord.com - A Product of Marketocracy Inc. | <a href="https://portfolio.marketocracy.com/includes/email/tracking.php?[SUB_LINK]" style="color:#ffffff;text-decoration:underline;" target="_blank">Change Subscription Settings</a></p>
        </td>
        <td width="15"><!--[if !mso]><!-- -->
            <img src="https://portfolio.marketocracy.com/includes/email/images/system-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
            <!--<![endif]--></td>
    </tr>

</table><!--main-wrapper-->

</body>
</html>
';

if($_REQUEST['show-email'] == 1){
	
	print_r($message);
		
}

?>