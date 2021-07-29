<?php
//NOTIFICATION PORTLET: This portlet is called in by ajax file: includes/scripts/system-ajax-portlets.php which is included on the index.php page
session_start();

// Load debug & error logging functions
require_once("../../system-debug-functions.php");

require("../../../../secure/dbconnect.php");

require("../../system-functions.php");


$query = "
	SELECT 
	types.notification_id, 
	types.status, 
	types.icon, 
	types.title, 
	types.default_notification, 
	types.default_link, 
	
	notes.uid, 
	notes.notification, 
	notes.link,
	notes.flagged,
	notes.viewed,
	notes.timestamp,
	notes.member_id
	
	FROM ".$_SESSION['members_notification_types_table']." AS types 
	INNER JOIN ".$_SESSION['members_notification_table']." AS notes ON types.notification_id = notes.notification_id
	WHERE member_id=:member_id AND deleted='0'
	ORDER BY notes.timestamp DESC
";

//Fund Symbols
try{
	$rsSystemNote = $mLink->prepare($query);
	$aValues = array(
		':member_id' 	=> $_SESSION['member_id']
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsSystemNote->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

while($systemNote = $rsSystemNote->fetch(PDO::FETCH_ASSOC)) {
	//Set Default Variables
	$noteStatus	 	= $systemNote['status'];
	$noteIcon 		= $systemNote['icon'];
	$noteTitle 		= $systemNote['title'];
	$defaultNote	= $systemNote['default_notification'];
	$defaultLink 	= $systemNote['default_link'];
	
	$noteUID 		= $systemNote['uid'];
	$noteFlag 		= $systemNote['flagged'];
	$noteView 		= $systemNote['viewed'];
	$timestamp 		= $systemNote['timestamp'];
	
	//Set Custom Variables
	$customNote 	= $systemNote['notification'];
	$customLink 	= $systemNote['link'];
	
	//Choose Defaults or Customs
	if($customNote == ""){
		$notification = $defaultNote;	
	}else{
		$notification = $customNote;	
	}
	
	if($customLink == ""){
		$link = $defaultLink;	
	}else{
		$link = $customLink;
	}
	
	if($link == ""){
		$link = "javascript:void(0);";	
	}else{
		$link = $link;	
	}

	if($systemNote['viewed'] != "0"){
		$viewBTN = '<a href="javascript:void(0);" onClick="viewNote(\'uid='.$noteUID.'&mark=1\');" title="Flag Notification" class="note-actions" style="border:none !important;padding-bottom:10px !important;"><i class="icon-flag"></i></a>';
		$flagStyle = "";
	}else{
		$viewBTN = '<a href="javascript:void(0);" onClick="viewNote(\'uid='.$noteUID.'\');" title="Mark As Viewed" class="note-actions" style="border:none !important;padding-bottom:10px !important;"><i class="icon-eye-open"></i></a>';
		$flagStyle = "";
	}
	
	if($noteFlag != "0"){
		$viewBTN = '<a href="javascript:void(0);" onClick="viewNote(\'uid='.$noteUID.'&mark=2\');" title="Unflag Notification" class="note-actions" style="border:none !important;padding-bottom:10px !important;"><i class="icon-flag" style="color:#ed4e2a;"></i></a>';	
		$flagStyle = 'border-left: 5px solid #ed4e2a !important;';
	}

	?>
	<li>  
        <a href="<?php echo $link;?>" onClick="viewNote('uid=<?php echo $noteUID;?>');"  style="<?php echo $flagStyle;?>padding-right:45px !important;">
        <span class="label label-sm label-icon label-<?php echo $noteStatus;?>"><i class="icon-<?php echo $noteIcon?>"></i></span>
        <?php echo "<strong>".$noteTitle."</strong>";?>&nbsp;<?php if($noteView == "0") {?>&nbsp;<span class="label label-sm label-info">New</span><?php } ?>
        <br /><?php echo $notification;?>
        <br /><span class="time"><?php echo time_past($timestamp);?></span>
        </a>
        <span class="note-buttons">
        	
        	<a href="javascript:void(0);" onClick="removeNote('uid=<?php echo $noteUID;?>','note-<?php echo $noteUID;?>');" title="Remove Notification" class="note-actions" style="border:none !important;"><i class="icon-remove"></i></a>
            <?php echo $viewBTN; ?>
        </span>
	</li>
	<?php
}
?>         