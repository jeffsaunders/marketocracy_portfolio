<?php

session_start();

//Connect to DB

require_once("../secure/dbconnect.php");

require_once("includes/system-functions.php");

$memberID = "2";

$ticketID = "1018";

$query = "
	SELECT *
	FROM ".$_SESSION['support_email_table']."
	WHERE email_id='1'
";

try{
	$rsEmailItems = $mLink->prepare($query);
	$aValues = array(
		//':list_id' 	=> $listID
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


$name_first = "Brandon";

$variables = array("name_first"=>$name_first);
$string = $email['email_headline'];

foreach($variables as $key => $value){
    $headline = str_replace('['.strtoupper($key).']', $value, $string);
}

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Windows-1252">
<title><?php echo $email['email_title'];?></title>

</head>

<body style="background-color:#4d4f4e; margin:0px; padding:0px;">

<table width="600" align="center" border="0" cellpadding="0" cellspacing="0" style="margin:0px auto;" bgcolor="#ffffff">
    <tr bgcolor="#00bdee">
		<td colspan="4" align="left" width="600" style="background-image:url('http://<?php echo $_SESSION['base_url'];?>includes/email/images/support-ticket-email/header-support.png'); background-repeat:no-repeat;">
		<!--[if gte mso 9]>
		<img src="http://<?php echo $_SESSION['base_url'];?>includes/email/images/support-ticket-email/header-support.png" width="600" height="45" border="0" alt="" />
		<![endif]-->
		<!--[if !mso]><!-- -->
		<img src="http://<?php echo $_SESSION['base_url'];?>includes/email/images/support-ticket-email/spacer.gif" width="600" height="45" border="0" alt="Marketocracy" />
		<!--<![endif]-->
		</td>
	</tr>
    <tr bgcolor="#00bdee">
    	<td width="15"><!--[if !mso]><!-- -->
		<img src="http://<?php echo $_SESSION['base_url'];?>includes/email/images/support-ticket-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
        
        <td><h2 style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; margin:0px 0px 3px 0px; padding:0px;">Portfolio Trading Platform</h2></td>
        
        <td align="right"><h2 style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; margin:0px 0px 3px 0px; padding:0px;"><?php echo date("F j, Y");?></h2></td>
        
        <td width="15"><!--[if !mso]><!-- -->
		<img src="http://<?php echo $_SESSION['base_url'];?>includes/email/images/support-ticket-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
    </tr>
    <tr>
    	<td colspan="4">
        <!--[if !mso]><!-- -->
		<img src="http://<?php echo $_SESSION['base_url'];?>includes/email/images/support-ticket-email/spacer.gif" width="600" height="20" border="0" alt="Marketocracy" />
		<!--<![endif]-->
        </td>
    </tr>
    <tr>
    	<td width="15"><!--[if !mso]><!-- -->
		<img src="http://<?php echo $_SESSION['base_url'];?>includes/email/images/support-ticket-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
        <td valign="top" colspan="2" bgcolor="#ffffff" style="font-family:Arial, Helvetica, sans-serif; font-size:16px; color:#666666; line-height:20px;">
            <h2 style="font-family:Arial, Helvetica, sans-serif; font-size:18px; color:#242527; margin:0px; padding:0px;"><strong><?php echo $headline;?></strong></h2>
            <?php echo $email['email_copy']; ?>
    		
            <table cellspacing="0" cellpadding="5" border="1" width="570" bordercolor="#8a8a8a">
            	<?php
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
					
					if($field['selector_value'] == "ticketID"){
						$selectorValue = $ticketID;	
					}elseif($field['selector_value'] == "memberID"){
						$selectorValue = $memberID;	
					}
					
					if($field['email_columns'] == "2"){
						?>
						<tr>
						<td bgcolor="#242527" width="200"><strong style="color:#ffffff; font-size:12px;"><?php echo $field['label'];?>:</strong></td>
						<?php			
						
							$query = "
								SELECT ".$field['column']."
								FROM ".$field['column_table']."
								WHERE ".$field['selector_column']."=:selector_value
							";
							
							try{
								$rsFieldValue = $mLink->prepare($query);
								$aValues = array(
									':selector_value' 	=> $selectorValue
								);
								$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
								$rsFieldValue->execute($aValues);
							}
							catch(PDOException $error){
								// Log any error
								file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
							}
							$value = $rsFieldValue->fetch(PDO::FETCH_ASSOC);
							
							echo "<td>".$value[''.$field['column'].'']."</td>";
							echo "</tr>";
					}elseif($field['email_columns'] == "1"){
						echo '<tr>
                				<td colspan="2" bgcolor="#242527"><strong style="color:#ffffff; font-size:12px;">'.$field['label'].':</strong></td>
               				 </tr>';
						$query = "
							SELECT ".$field['column']."
							FROM ".$field['column_table']."
							WHERE ".$field['selector_column']."=:selector_value
						";
						
						try{
							$rsFieldValue = $mLink->prepare($query);
							$aValues = array(
								':selector_value' 	=> $selectorValue
							);
							$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
							$rsFieldValue->execute($aValues);
						}
						catch(PDOException $error){
							// Log any error
							file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
						}
						$value = $rsFieldValue->fetch(PDO::FETCH_ASSOC);
						
						echo '<tr>
								<td colspan="2">'.$value[''.$field['column'].''].'</td>
							 </tr>';
					}
				}
				?>
                
            </table>
            
            <?php echo $email['email_closing'];?>
            
        </td>
        <td width="15"><!--[if !mso]><!-- -->
		<img src="http://<?php echo $_SESSION['base_url'];?>includes/email/images/support-ticket-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
    </tr>
    <tr bgcolor="#242527">
    	<td colspan="4">
        <!--[if !mso]><!-- -->
		<img src="http://<?php echo $_SESSION['base_url'];?>includes/email/images/support-ticket-email/spacer.gif" width="600" height="5" border="0" alt="Marketocracy" />
		<!--<![endif]-->
        </td>
    </tr>
    <tr bgcolor="#242527">
        <td width="15"><!--[if !mso]><!-- -->
            <img src="http://<?php echo $_SESSION['base_url'];?>includes/email/images/support-ticket-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
            <!--<![endif]--></td>
        <td width="570" colspan="2"  valign="top" colspan="3" style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; text-align:center;">
            &copy; 2001-<?php echo date("Y");?> <?php echo $email['email_footer'];?>
        </td>
        <td width="15"><!--[if !mso]><!-- -->
            <img src="http://<?php echo $_SESSION['base_url'];?>includes/email/images/support-ticket-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
            <!--<![endif]--></td>
    </tr>

</table><!--main-wrapper-->

</body>
</html>
