<?php

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

$problemType = $problem['label'];

//GET EMAIL FROM DB
$query = "
	SELECT *
	FROM ".$_SESSION['support_email_table']."
	WHERE email_id=:email_id
";

try{
	$rsEmailItems = $mLink->prepare($query);
	$aValues = array(
		':email_id' 	=> $emailID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsEmailItems->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}
$email = $rsEmailItems->fetch(PDO::FETCH_ASSOC);

$emailFields = explode("|", $email['fields']);


$emailVars = array(
	"name_first"		=> get_member($mLink, $memberID, "first name"),
	"ticket_id"			=> $ticketID,
	"username"			=> get_member($mLink, $memberID, "username"),
	"stock_ticker"		=> $stockTicker,
	"problem_type"		=> $problemType,
	"ticket_subject"	=> $subject,
	"description"		=> $description,
	"fund_tickers"		=> $fundSymbols,
	"responder"			=> $responder,
	"reply"				=> $emailReply,
	"status"			=> $status,
	"site_root"			=> $_SESSION['site_root'],
	"convo"				=> $convo
);
			
//START EMAIL

//Where the email goes (user/member)
if($adminMemberID == ""){
	$to = get_member($mLink, $memberID, "email");
}else{
	$to = get_member($mLink, $adminMemberID, "email");
}

//Email Subject
$subject = $email['email_subject'];

$message = '
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Windows-1252">
<title>'.$email['email_title'].'</title>

</head>

<body style="background-color:#4d4f4e; margin:0px; padding:0px;">

<table width="600" align="center" border="0" cellpadding="0" cellspacing="0" style="margin:0px auto;" bgcolor="#ffffff">
    <tr bgcolor="#00bdee">
		<td colspan="4" align="left" width="600" style="background-image:url(\'http://'.$_SESSION['base_url'].'includes/email/images/support-ticket-email/header-support.png\'); background-repeat:no-repeat;">
		<!--[if gte mso 9]>
		<img src="http://'. $_SESSION['base_url'].'includes/email/images/support-ticket-email/header-support.png" width="600" height="45" border="0" alt="" />
		<![endif]-->
		<!--[if !mso]><!-- -->
		<img src="http://'.$_SESSION['base_url'].'includes/email/images/support-ticket-email/spacer.gif" width="600" height="45" border="0" alt="Marketocracy" />
		<!--<![endif]-->
		</td>
	</tr>
    <tr bgcolor="#00bdee">
    	<td width="15"><!--[if !mso]><!-- -->
		<img src="http://'.$_SESSION['base_url'].'includes/email/images/support-ticket-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
        
        <td><h2 style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; margin:0px 0px 3px 0px; padding:0px;">Portfolio Trading Platform</h2></td>
        
        <td align="right"><h2 style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; margin:0px 0px 3px 0px; padding:0px;"><?php echo date("F j, Y");?></h2></td>
        
        <td width="15"><!--[if !mso]><!-- -->
		<img src="http://'.$_SESSION['base_url'].'includes/email/images/support-ticket-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
    </tr>
    <tr>
    	<td colspan="4">
        <!--[if !mso]><!-- -->
		<img src="http://'.$_SESSION['base_url'].'includes/email/images/support-ticket-email/spacer.gif" width="600" height="20" border="0" alt="Marketocracy" />
		<!--<![endif]-->
        </td>
    </tr>
    <tr>
    	<td width="15"><!--[if !mso]><!-- -->
		<img src="http://'.$_SESSION['base_url'].'includes/email/images/support-ticket-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
        <td valign="top" colspan="2" bgcolor="#ffffff" style="font-family:Arial, Helvetica, sans-serif; font-size:16px; color:#666666; line-height:20px;">
            <h2 style="font-family:Arial, Helvetica, sans-serif; font-size:18px; color:#242527; margin:0px; padding:0px;"><strong>'.$email['email_headline'].'</strong></h2>
            '.$email['email_copy'].'
    		
            <table cellspacing="0" cellpadding="5" border="1" width="570" bordercolor="#8a8a8a">';
				foreach($emailFields as $fields) {
					//echo $fields;
										
					//Get Field Values and labels
					$query = "
						SELECT *
						FROM ".$_SESSION['email_fields_table']."
						WHERE field_id=:field_id
					";
					
					try{
						$rsEmailFields = $mLink->prepare($query);
						$aValues = array(
							':field_id' 	=> $fields
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsEmailFields->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					$field = $rsEmailFields->fetch(PDO::FETCH_ASSOC);
					
					
										
					//Display as two columns or one column
					if($field['email_columns'] == "2"){
						$message .= '
						<tr>
							<td bgcolor="#242527" width="200"><strong style="color:#ffffff; font-size:12px;">'.$field['label'].':</strong></td>
						';
						
						$message .= '<td>'.$field['variable'].'</td>';
						$message .= "</tr>";
							
					}elseif($field['email_columns'] == "1"){
						$message .= '
							<tr>
                				<td colspan="2" bgcolor="#242527"><strong style="color:#ffffff; font-size:12px;">'.$field['label'].':</strong></td>
               				</tr>';
						
						$message .= '
							<tr>
								<td colspan="2">'.$field['variable'].'</td>
							</tr>';
					}
				}
				
				$message .= '
                
            </table>
            
            '.$email['email_closing'].'
            
        </td>
        <td width="15"><!--[if !mso]><!-- -->
		<img src="http://'.$_SESSION['base_url'].'includes/email/images/support-ticket-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
    </tr>
    <tr bgcolor="#242527">
    	<td colspan="4">
        <!--[if !mso]><!-- -->
		<img src="http://'.$_SESSION['base_url'].'includes/email/images/support-ticket-email/spacer.gif" width="600" height="5" border="0" alt="Marketocracy" />
		<!--<![endif]-->
        </td>
    </tr>
    <tr bgcolor="#242527">
        <td width="15"><!--[if !mso]><!-- -->
            <img src="http://'.$_SESSION['base_url'].'includes/email/images/support-ticket-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
            <!--<![endif]--></td>
        <td width="570" colspan="2"  valign="top" colspan="3" style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; text-align:center;">
            &copy; 2001-'.date("Y").' '.$email['email_footer'].'
        </td>
        <td width="15"><!--[if !mso]><!-- -->
            <img src="http://'.$_SESSION['base_url'].'includes/email/images/support-ticket-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
            <!--<![endif]--></td>
    </tr>

</table><!--main-wrapper-->

</body>
</html>
';

foreach($emailVars as $key => $value){
    $message = str_replace('['.strtoupper($key).']', $value, $message);
}

foreach($emailVars as $key => $value){
    $subject = str_replace('['.strtoupper($key).']', $value, $subject);
}

//die($message);
// To send HTML mail, the Content-type header must be set
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

// Additional headers
$headers .= 'From: '.$_SESSION['system_email_from'].'' . "\r\n";

//This is for testing
if($_SESSION['system_email_bcc'] != ''){
	$headers .= 'Bcc: '.$_SESSION['system_email_bcc'].'' . "\r\n";  //Testing
}

//$headers .= 'Bcc: jeff.saunders@marketocracy.com, brandon.mccarthy@marketocracy.com' . "\r\n";  //Testing
//$headers .= 'Cc: someone@marketocracy.com' . "\r\n";
//$headers .= 'Bcc: someone.else@marketocracy.com, someone.outside@otherdomain.com' . "\r\n";

// Mail it
mail($to, $subject, $message, $headers);

?>