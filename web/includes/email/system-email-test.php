<?php
//EMAIL TEMPLATE FOR SYSTEM EMAILS
/*
Email Array example:

$aEmailVars = array(
	'first_name'	=> 'Brandon',
	'last_name'		=> 'McCarthy',
	'user_name'		=> 'bmccarthy',
	'change_link'	=> 'http://beta.marketocracy.com/?page=10-00-00-002&account=1&tab=password',
	
);
$aEmail = array(
	'email_id'		=> '7',
	'to'			=> 'brandon.mccarthy@marketocracy.com',
	'vars'			=> $aEmailVars
);
include('#siteRoot#/includes/emails/system-email.php');

*/



$aEmailVars = array(
	'verify_link'	=> $tokenLink,
	'username'		=> 'bmccarthy4'
);
$aEmail = array(
	'email_id'		=> '9',
	'to'			=> 'media.mccarthy@gmail.com',
	'vars'			=> $aEmailVars
);

//GET EMAIL FROM DB
$query = "
	SELECT *
	FROM ".$_SESSION['system_email_table']."
	WHERE email_id=:email_id
";

try{
	$rsEmailItems = $mLink->prepare($query);
	$aValues = array(
		':email_id' 	=> $aEmail['email_id']
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsEmailItems->execute($aValues);
}

catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}
$email = $rsEmailItems->fetch(PDO::FETCH_ASSOC);


//$emailFields = explode("|", $email['fields']);
	
echo $error;			
//START EMAIL
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
		<td colspan="4" align="left" width="600" style="background-image:url(\'http://'.$_SESSION['base_url'].'includes/email/images/system-email/header-system.png\'); background-repeat:no-repeat;">
		<!--[if gte mso 9]>
		<img src="http://'. $_SESSION['base_url'].'includes/email/images/system-email/header-system.png" width="600" height="45" border="0" alt="" />
		<![endif]-->
		<!--[if !mso]><!-- -->
		<img src="http://'.$_SESSION['base_url'].'includes/email/images/system-email/spacer.gif" width="600" height="45" border="0" alt="Marketocracy" />
		<!--<![endif]-->
		</td>
	</tr>
    <tr bgcolor="#00bdee">
    	<td width="15"><!--[if !mso]><!-- -->
		<img src="http://'.$_SESSION['base_url'].'includes/email/images/system-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
        
        <td><h2 style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; margin:0px 0px 3px 0px; padding:0px;">Portfolio Trading Platform</h2></td>
        
        <td align="right"><h2 style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; margin:0px 0px 3px 0px; padding:0px;"><?php echo date("F j, Y");?></h2></td>
        
        <td width="15"><!--[if !mso]><!-- -->
		<img src="http://'.$_SESSION['base_url'].'includes/email/images/system-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
    </tr>
    <tr>
    	<td colspan="4">
        <!--[if !mso]><!-- -->
		<img src="http://'.$_SESSION['base_url'].'includes/email/images/system-email/spacer.gif" width="600" height="20" border="0" alt="Marketocracy" />
		<!--<![endif]-->
        </td>
    </tr>
    <tr>
    	<td width="15"><!--[if !mso]><!-- -->
		<img src="http://'.$_SESSION['base_url'].'includes/email/images/system-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
        <td valign="top" colspan="2" bgcolor="#ffffff" style="font-family:Arial, Helvetica, sans-serif; font-size:16px; color:#666666; line-height:20px;">
            <h2 style="font-family:Arial, Helvetica, sans-serif; font-size:18px; color:#242527; margin:0px; padding:0px;"><strong>'.$email['email_headline'].'</strong></h2>
            '.$email['email_copy'].'
    		';
			
			//check to see if there are any email fields that need to be brought in
			/*if($email['fields'] != ''){
				
				$message .= '
				<table cellspacing="0" cellpadding="5" border="1" width="570" bordercolor="#8a8a8a">
				';
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
					
				</table>';
			}//end check for fields*/
            
			//reopen message
            $message .= '
			
			'.$email['email_closing'].'
            <br />
        </td>
        <td width="15"><!--[if !mso]><!-- -->
		<img src="http://'.$_SESSION['base_url'].'includes/email/images/system-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
    </tr>
    <tr bgcolor="#242527">
    	<td colspan="4">
        <!--[if !mso]><!-- -->
		<img src="http://'.$_SESSION['base_url'].'includes/email/images/system-email/spacer.gif" width="600" height="5" border="0" alt="Marketocracy" />
		<!--<![endif]-->
        </td>
    </tr>
    <tr bgcolor="#242527">
        <td width="15"><!--[if !mso]><!-- -->
            <img src="http://'.$_SESSION['base_url'].'includes/email/images/system-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
            <!--<![endif]--></td>
        <td width="570" colspan="2"  valign="top" colspan="3" style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; text-align:center;">
            &copy; 2001-'.date("Y").' '.$email['email_footer'].'
        </td>
        <td width="15"><!--[if !mso]><!-- -->
            <img src="http://'.$_SESSION['base_url'].'includes/email/images/system-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
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
if($email['headers_from'] == '' || $email['headers_from'] == NULL){
	$headers .= 'From: '.$_SESSION['system_email_from'].'' . "\r\n";
}else{
	$headers .= 'From: '.$email['headers_from'].'' . "\r\n";	
}

//This is for testing
if($email['headers_bcc'] == '' || $email['headers_from'] == NULL){
	if($_SESSION['system_email_bcc'] != ''){
		$headers .= 'Bcc: '.$_SESSION['system_email_bcc'].'' . "\r\n";  //Testing
	}
}else{
	$headers .= 'Bcc: '.$email['headers_bcc'].'' . "\r\n";
}

//Where the email goes (user/member)
$to = $aEmail['to'];

//Email Subject
$subject = $email['email_subject'];

//Fill in email message variables
foreach($aEmail['vars'] as $key => $value){
    $message = str_replace('['.strtoupper($key).']', $value, $message);
}

//Fill in email subject variables
foreach($aEmail['vars'] as $key => $value){
    $subject = str_replace('['.strtoupper($key).']', $value, $subject);
}

// Mail it
mail($to, $subject, $message, $headers);

//echo $message;
?>