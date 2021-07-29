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


<form action="process/ajax/user-settings-processes.php?process=1" method="post" class="note-process-ajax">
    <h3>Turn On or Off notifications</h3>
    <table class="table table-bordered table-striped">
         <?php
      
      $query = "
          SELECT DISTINCT sec.section_id, sec.section_name
          FROM site_sections as sec
          INNER JOIN members_notifications_types AS types
          WHERE sec.section_id = SUBSTR(types.notification_id,1,2)
      ";
      
      //Fund Symbols
      try{
          $rsSections = $mLink->prepare($query);
          $aValues = array(
              //':member_id' 	=> $_SESSION['member_id']
          );
          $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
          $rsSections->execute($aValues);
      }
      catch(PDOException $error){
          // Log any error
          file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
      }
      
      while($section = $rsSections->fetch(PDO::FETCH_ASSOC)) {
            
            //echo $noteID['section_id'];
            ?>
            <tr>
               <td colspan="2"><strong><?php echo $section['section_name']; ?> Notifications</strong></td>
            </tr>
            <?php
            
            $query = "
                SELECT types.notification_id, types.label, types.uid, types.admin, settings.ignore_notifications, types.disable
                FROM members_notifications_types AS types
                INNER JOIN members_settings AS settings ON member_id=:member_id
                WHERE types.locked='0' AND SUBSTR(types.notification_id,1,2)=:section_id AND types.disable='0'
                ORDER BY types.notification_id ASC
            ";
            
            try{
                $rsNotification = $mLink->prepare($query);
                $aValues = array(
                    ':section_id' 	=> $section['section_id'],
                    ':member_id'	=> $_SESSION['member_id']
                );
                $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                $rsNotification->execute($aValues);
            }
            catch(PDOException $error){
                // Log any error
                file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
            }
            
            while($noteType = $rsNotification->fetch(PDO::FETCH_ASSOC)) {
                $ignore = $noteType['ignore_notifications'];
                $nid	= $noteType['notification_id'];
                $checked = "";
                
                if(strpos($ignore, $nid) !== false){
                    $checked = "checked";
                }else{
                    $checked = "";	
                }
                
                if($noteType['admin'] == 1 && $_SESSION['admin'] != 1){
                    //dont print, the user is not an admin
                }else{
                ?>
                <tr>
                   <td><?php echo $noteType['label']; ?></td>
                   <td>
                      <div class="switch-checkbox">
                          <input onChange="updateSave();" type="checkbox" name="checkbox[]" value="<?php echo $nid;?>" style="display:none;" class="hide" <?php echo $checked; ?>>
                      </div>
                   </td>
                </tr>
                <?php
                }
            }//noteType
            
      }//noteID
      ?> 

    </table>
    <!--end profile-settings-->
    <div class="margin-top-10" id="note-settings">
        <!--<input type="submit" value="Save Settings" class="btn btn-success">-->
        <input type="submit" value="Saved" class="btn btn-default">
    </div>
</form>