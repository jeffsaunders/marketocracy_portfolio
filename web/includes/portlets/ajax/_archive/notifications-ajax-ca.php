<?php
//NOTIFICATION PORTLET: This portlet is called in by ajax file: includes/scripts/system-ajax-portlets.php which is included on the index.php page
session_start();

require("../../system-functions.php");
require("../../../../secure/dbconnect.php");

/*$query = "
	SELECT *
	FROM ".$_SESSION['members_notification_table']."
	WHERE member_id=:member_id AND deleted='0' AND type='corporate-action'
	ORDER BY timestamp DESC
";*/

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
	WHERE member_id=:member_id AND deleted='0' AND notes.notification_id='12-001'
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

//Set counter at zero for row counting
$cnt = 0;

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
	
	
	//Run counter for every row returned from DB (use this to determine if there are any notifications)
	if($systemNote['member_id'] == $_SESSION['member_id']) {
		$cnt = $cnt + 1;	
	}
	
	//
	if($systemNote['viewed'] != "0"){
		$viewBTN = '<a href="javascript:void(0);" onClick="viewNote(\'uid='.$systemNote['uid'].'&mark=1\');" title="Flag Notification"class="note-actions"><i class="icon-flag"></i></a>';
		$flagStyle = "";
	}else{
		$viewBTN = '<a href="javascript:void(0);" onClick="viewNote(\'uid='.$systemNote['uid'].'\');" title="Mark As Viewed" class="note-actions"><i class="icon-eye-open"></i></a>';
		$flagStyle = "";
		$viewJava = 'onClick="viewNote(\'uid='.$systemNote['uid'].'\');"';
	}
	
	if($systemNote['flagged'] != "0"){
		$viewBTN = '<a href="javascript:void(0);" onClick="viewNote(\'uid='.$systemNote['uid'].'&mark=2\');" title="Unflag Notification" class="note-actions"><i class="icon-flag" style="color:#ed4e2a;"></i></a>';
		$flagStyle = 'style="border-left: 5px solid #ed4e2a !important;"';	
	}

?>
<li id="note-<?php echo $noteUID;?>" <?php echo $flagStyle; ?>>
	<?php
	if($link != ""){?><a href="<?php echo $link;?>" <?php echo $viewJava;?>><?php } ?>
	<div class="col1">
	  <div class="cont">
		 <div class="cont-col1">
			<div class="label label-sm label-<?php echo $noteStatus;?>" style="margin-top:10px;margin-left:10px;">                        
			   <i class="icon-<?php echo $noteIcon;?>"></i>
			</div>
		 </div>
		 <div class="cont-col2">
			<div class="desc">
			   <?php echo "<strong>".$noteTitle."</strong>"; 
			   if($noteView == "0") {?>&nbsp;<span class="label label-sm label-info">New</span><?php } 
			   if($noteFlag != "0") {?>&nbsp;<span class="label label-sm label-danger"><i class="icon icon-flag" style="color:#ffffff;"></i> Flagged</span><?php }
			   ?> <?php //echo $systemNote['uid']; ?>
               <br /><?php echo $notification;?>
			   <?php if(!empty($link)){?>
			   <span class="label label-sm label-<?php echo $noteStatus;?>">
			   <i class="icon-share-alt"></i>
			   </span>
			   <?php } ?>
			     
			</div>
		 </div>
	  </div>
	</div>
	<?php if($link != ""){?></a><?php }?>
	<div class="col2">
	  <div class="date">
		 <?php
		 echo time_past($timestamp);?>
	  </div>
	</div>
    
    <div class="col3">
    	<div class="notification-buttons">
        <a href="javascript:void(0);" onClick="removeNote('uid=<?php echo $noteUID;?>','note-<?php echo $noteUID;?>');" title="Remove Notification" class="note-actions remove"><i class="icon-remove"></i></a>
		<?php echo $viewBTN;?>
        </div><!--notification-buttons-->
    </div><!--col3-->
	
</li>
<?php
}
if($cnt == 0){
	echo '<li>
	<div class="col1">
	  <div class="cont">
		 <div class="cont-col1">
			<div class="label label-sm label-default">                        
			   <i class="icon-bell"></i>
			</div>
		 </div>
		 <div class="cont-col2">
			<div class="desc">
			   No Notifications
			</div>
		 </div>
	  </div>
	</div></li>';
}
?>
             