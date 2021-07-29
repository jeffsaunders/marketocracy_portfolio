<?php
/*
Marketocracy Inc. | Beta Development
User Profile/Settings - Notification Settings - Include File

Supporting files:
	AJAX			- process/ajax/user-settings-processes.php
	JAVASCRIPT		- JAVASCRIPT includes/scripts/user-settings.js.php
	HTML			- includes/pages/user-profile.php
	PHP Includes	- THIS DOCUMENT includes/pages/includes/user-profile/notification-settings.php
*/

?>


<form action="process/ajax/user-settings-processes.php?process=email-settings" method="post" class="email-process-ajax">
    <h3>Turn On or Off Email Notifications</h3>
    <table class="table table-bordered table-striped">
        
            <tr>
               <td colspan="2"><strong>Email Notifications</strong></td>
            </tr>
            <?php
            
            $query = "
                SELECT emails.*, settings.ignore_emails
                FROM email_types AS emails
                INNER JOIN members_settings AS settings ON member_id=:member_id
                WHERE emails.locked='0' AND emails.disabled='0'
                ORDER BY emails.email_id ASC
            ";
            
            try{
                $rsEmail = $mLink->prepare($query);
                $aValues = array(
                    ':member_id'	=> $_SESSION['member_id']
                );
                $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                $rsEmail->execute($aValues);
            }
            catch(PDOException $error){
                // Log any error
                file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
            }
            
			echo $error;
			
            while($emailType = $rsEmail->fetch(PDO::FETCH_ASSOC)) {
                $ignore = $emailType['ignore_emails'];
                $emailID	= $emailType['email_id'];
                $checked = "";
                
                if(strpos($ignore, $emailID) !== false){
                    $checked = "checked";
                }else{
                    $checked = "";	
                }
                
                if($emailType['admin'] == 1 && $_SESSION['admin'] != 1){
                    //dont print, the user is not an admin
                }else{
                ?>
                <tr>
                   <td><?php echo $emailType['label']; ?></td>
                   <td>
                      <div class="switch-checkbox">
                          <input onChange="updateSave2();" type="checkbox" name="checkbox[]" value="<?php echo $emailID;?>" style="display:none;" class="hide" <?php echo $checked; ?>>
                      </div>
                   </td>
                </tr>
                <?php
                }
            }//noteType
            
     
      ?> 

    </table>
    <!--end profile-settings-->
    <div class="margin-top-10" id="email-settings">
        <!--<input type="submit" value="Save Settings" class="btn btn-success">-->
        <input type="submit" value="Saved" class="btn btn-default">
    </div>
</form>