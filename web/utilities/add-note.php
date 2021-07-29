<?php 
session_start();

// Load debug & error logging functions
require_once("../includes/system-debug-functions.php");

//Connect to DB
require_once("../../secure/dbconnect.php");

require_once("../includes/system-functions.php");

$memberID = $_REQUEST['member'];

$query = '
	SELECT *
	FROM '.$_SESSION['members_notification_types_table'].'
';

try{
	$rsGetTypes = $mLink->prepare($query);
	$aValues = array(
		//':asOfDate' 	=> $yesterday
		
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsGetTypes->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

while($note = $rsGetTypes->fetch(PDO::FETCH_ASSOC)){
	$aNoteTypes[$note['notification_id']] = array(
		'label'		=> $note['label'],
		'title'		=> $note['title'],
		'note'		=> $note['default_notification'],
		'link'		=> $note['default_link']
	);	
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Add Notification</title>
</head>

<body>

<div>
    <h2>Add notification to members.</h2>
    <form action="process/find-data-process.php?process=1" method="post" class="add-note">
        <label>Member ID:</label>
        <input type="text" value="<?php echo $memberID;?>" name="member"/><br />
        <small>Leave blank for all members</small><br /><br />
        
        <label>Notification Type:</label><br />
        <select name="note_id">
        	<?php
			foreach($aNoteTypes as $noteID => $aNote){
				echo '<option value="'.$noteID.'">'.$aNote['label'].'</option>';
			}
			?>
        </select><br /><br />
        
     
        
        <label>Custom Notification</label><br />
        <textarea type="text" name="custom_note"></textarea><br /><br />
        
        <label>Custom Link</label><br />
        <input type="text" name="custom_link" /><br /><br />
        
        
        
        <input type="hidden" value="<?php echo $testDate;?>" name="test" />
        <input type="submit" value="Add Note" /><!--&nbsp;&nbsp;<input id="active_only" name="active_only" type="checkbox" value="1" checked="checked" /> Active Funds Only<br />-->
        
    
    </form>
</div>

<div id="show-results">

</div>



<script src="../plugins/jquery-1.10.2.min.js" type="text/javascript"></script>
<script src="../plugins/jquery-migrate-1.2.1.min.js" type="text/javascript"></script>
<script>
//Process Reply Form via AJAX
$('form.add-note').on('submit', function() {
	
	$('#show-results').html('<img src="<?php echo $_SESSION['site_root'];?>images/loading.gif" />');
	
	$.ajax({
        type: "POST",
        data: $(".add-note").serialize(),
        url: "process/add-note-process.php?process=1",
        success: function(data)
        {
			//alert(data);
			$('#show-results').html(data);
			
		}
    });
	
	return false;
});



</script>
</body>
</html>