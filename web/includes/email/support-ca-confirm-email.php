<?php
//Get Members username and first name
$query = "
	SELECT username, name_first, email
	FROM ".$_SESSION['members_table']."
	WHERE member_id=:member_id
";

try{
	$rsUserInfo = $mLink->prepare($query);
	$aValues = array(
		':member_id'		=> $_SESSION['member_id']
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsUserInfo->execute($aValues);
}

catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$user = $rsUserInfo->fetch(PDO::FETCH_ASSOC);

//Get label from list table for Problem Type
$query = "
	SELECT label
	FROM ".$_SESSION['lists_options_table']."
	WHERE value=:value
";

try{
	$rsProblem = $mLink->prepare($query);
	$aValues = array(
		':value'		=> $_REQUEST['problem_type']
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsProblem->execute($aValues);
}

catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$problem = $rsProblem->fetch(PDO::FETCH_ASSOC);
			
// CA Confirm Email
$to = $user['email'];
//$to = 'jeff@nr.net'; // Testing
$subject = $user['name_first'].', Corporate Action Change Request | Ticket: '.$case['ticket_id'].' | Ticker: '.$_REQUEST['stock_ticker'].'';
$message = '
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Windows-1252">
<title>Marketocracy Corporate Action</title>

</head>

<body style="background-color:#4d4f4e; margin:0px; padding:0px;">

<table width="600" align="center" border="0" cellpadding="0" cellspacing="0" style="margin:0px auto;" bgcolor="#ffffff">
    <tr bgcolor="#00bdee">
		<td colspan="4" align="left" width="600" style="background-image:url(\'http://inside.marketocracy.com/email/2014/ca-system-email/images/header.png\'); background-repeat:no-repeat;">
		<!--[if gte mso 9]>
		<img src="http://inside.marketocracy.com/email/2014/ca-system-email/images/header.png" width="600" height="45" border="0" alt="" />
		<![endif]-->
		<!--[if !mso]><!-- -->
		<img src="http://marketocracy.com/email/140123_MarketocracyTemplate/images/spacer.gif" width="600" height="45" border="0" alt="Marketocracy" />
		<!--<![endif]-->
		</td>
	</tr>
    <tr bgcolor="#00bdee">
    	<td width="15"><!--[if !mso]><!-- -->
		<img src="http://marketocracy.com/email/140123_MarketocracyTemplate/images/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
        
        <td><h2 style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; margin:0px 0px 3px 0px; padding:0px;">Portfolio Trading Platform</h2></td>
        
        <td align="right"><h2 style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; margin:0px 0px 3px 0px; padding:0px;">'.date("F j, Y").'</h2></td>
        
        <td width="15"><!--[if !mso]><!-- -->
		<img src="http://marketocracy.com/email/140123_MarketocracyTemplate/images/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
    </tr>
    <tr>
    	<td colspan="4">
        <!--[if !mso]><!-- -->
		<img src="http://marketocracy.com/email/140123_MarketocracyTemplate/images/spacer.gif" width="600" height="20" border="0" alt="Marketocracy" />
		<!--<![endif]-->
        </td>
    </tr>
    <tr>
    	<td width="15"><!--[if !mso]><!-- -->
		<img src="http://marketocracy.com/email/140123_MarketocracyTemplate/images/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
        <td valign="top" colspan="2" bgcolor="#ffffff" style="font-family:Arial, Helvetica, sans-serif; font-size:16px; color:#666666; line-height:20px;">
            <h2 style="font-family:Arial, Helvetica, sans-serif; font-size:18px; color:#242527; margin:0px; padding:0px;"><strong>'.$user['name_first'].', thank you for reporting a Corporate Action!</strong></h2>
            <p>We understand the urgency of Corporate Action changes, and we will do everything in our ability to resolve this quickly.</p>
    
            <p>Please keep this email for your records:</p>
    		
            <table cellspacing="0" cellpadding="5" border="1" width="570" bordercolor="#8a8a8a">
            	<tr>
                	<td bgcolor="#242527" width="200"><strong style="color:#ffffff; font-size:14px;">Ticket Number:</strong></td>
                    <td>'.$case['ticket_id'].'</td>
                </tr>
                
                <tr>
                	<td bgcolor="#242527"><strong style="color:#ffffff; font-size:14px;">Portfolio Username:</strong></td>
                    <td>'.$user['username'].'</td>
                </tr>
                <tr>
                	<td bgcolor="#242527"><strong style="color:#ffffff; font-size:14px;">Ticker Symbol:</strong></td>
                    <td>'.$_REQUEST['stock_ticker'].'</td>
                </tr>
                <tr>
                	<td colspan="2" bgcolor="#242527"><strong style="color:#ffffff; font-size:14px;">Corporate Action Type:</strong></td>
                </tr>
                <tr>
                	<td colspan="2">'.$problem['label'].'</td>
                </tr>
				<tr>
                	<td colspan="2" bgcolor="#242527"><strong style="color:#ffffff; font-size:12px;">Subject:</strong></td>
                </tr>
                <tr>
                	<td colspan="2">'.$_REQUEST['subject'].'</td>
                </tr>
                <tr>
                	<td colspan="2" bgcolor="#242527"><strong style="color:#ffffff; font-size:12px;">Description:</strong></td>
                </tr>
                <tr>
                	<td colspan="2">'.$_REQUEST['description'].'</td>
                </tr>
                
            </table>
            
            <p>Thank you.</p>
    
            
        </td>
        <td width="15"><!--[if !mso]><!-- -->
		<img src="http://marketocracy.com/email/140123_MarketocracyTemplate/images/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
    </tr>
    <tr bgcolor="#242527">
    	<td colspan="4">
        <!--[if !mso]><!-- -->
		<img src="http://marketocracy.com/email/140123_MarketocracyTemplate/images/spacer.gif" width="600" height="5" border="0" alt="Marketocracy" />
		<!--<![endif]-->
        </td>
    </tr>
    <tr bgcolor="#242527">
        <td width="15"><!--[if !mso]><!-- -->
            <img src="http://marketocracy.com/email/140123_MarketocracyTemplate/images/spacer.gif" width="15" border="0" alt="Marketocracy" />
            <!--<![endif]--></td>
        <td width="570" colspan="2"  valign="top" colspan="3" style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; text-align:center;">
            &copy; 2001-'.date("Y").' <a href="http://marketocracy.com" target="_blank" style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; text-decoration:none;">Marketocracy</a>, Inc. All rights reserved.<br>
            1208 West Magnolia Ave &middot; Suite 236 &middot; Fort Worth, TX 76104<br><br>
        </td>
        <td width="15"><!--[if !mso]><!-- -->
            <img src="http://marketocracy.com/email/140123_MarketocracyTemplate/images/spacer.gif" width="15" border="0" alt="Marketocracy" />
            <!--<![endif]--></td>
    </tr>

</table><!--main-wrapper-->

</body>
</html>
';
//die($message);
// To send HTML mail, the Content-type header must be set
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

// Additional headers
$headers .= 'From: Alpha - Marketocracy <rachel.oneal@marketocracy.com>' . "\r\n";
$headers .= 'Bcc: brandon.mccarthy@marketocracy.com' . "\r\n";  //Testing
//$headers .= 'Bcc: jeff.saunders@marketocracy.com, brandon.mccarthy@marketocracy.com' . "\r\n";  //Testing
//$headers .= 'Cc: someone@marketocracy.com' . "\r\n";
//$headers .= 'Bcc: someone.else@marketocracy.com, someone.outside@otherdomain.com' . "\r\n";

// Mail it
mail($to, $subject, $message, $headers);

?>