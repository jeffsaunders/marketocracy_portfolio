<?php
//NOTIFICATION PORTLET: This portlet is called in by ajax file: includes/scripts/system-ajax-portlets.php which is included on the index.php page
session_start();

// Load debug & error logging functions
require_once("../../system-debug-functions.php");

//DB
require("../../../../secure/dbconnect.php");

//FUNCTIONS
require("../../system-functions.php");

$noteSection = $_REQUEST['section'];

/*if($noteSection != ""){
	$noteSection = "AND notes.notification_id='".$noteSection."'";	
}*/
if($noteSection != ""){
	$noteSection = "AND SUBSTRING(notes.notification_id, 1, 2)='".substr($noteSection, 0, 2)."'";
}

$time = time();

$oneWeek = strtotime('-4 week', $time);

//Query Database | NOTE: when adding new notification IDs they must first exist in the members_notifications_types table
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
	WHERE notes.member_id=:member_id AND notes.deleted='0' ".$noteSection." AND notes.timestamp>:oneWeek OR notes.member_id=:member_id AND notes.deleted='0' AND notes.flagged<>'0'
	ORDER BY notes.timestamp DESC
";

//Fund Symbols
try{
	$rsSystemNote = $mLink->prepare($query);
	$aValues = array(
		':member_id' 	=> $_SESSION['member_id'],
		':oneWeek'		=> $oneWeek
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsSystemNote->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$_SESSION['debug']['notifications'] = $preparedQuery;

//Set counter at zero for row counting
$cnt = 0;

//lets try and catch some dupes
$aNotes = array();
$deleteNotes = array();

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
	
	$noteID 		= $systemNote['notification_id'];
	
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
	
	if(in_array($notification, $aNotes)){
		//do nothing
		$deleteNotes[] = $noteUID;
	}else{
	
		//track the dupes
		if($noteID == '02-001'){
			$aNotes[] = $notification;	
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
		
		if($noteID == '08-001' || $noteID == '08-002' || $noteID == '12-002'){
			
			$aLink 		= explode('&', $link);
			$aTicket 	= explode('=', $aLink[1]);
			$ticketID 	= $aTicket[1];
			
			$ticketStatus	= get_ticket($mLink, $ticketID, 'status');
			
			switch($ticketStatus){
				
				case 'Closed': $ticketStatus = '<span class="label label-danger label-sm">'.$ticketStatus.'</span>';break;
				case 'Awaiting Response': $ticketStatus = '<span class="label label-warning label-sm">'.$ticketStatus.'</span>';break;
				default: $ticketStatus = '';	
			}
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
					   echo ' '.$ticketStatus;
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
	}//end check for dupes
}

//Remove dup records
/*foreach($deleteNotes as $key=>$UID){
	
	$query = "
		UPDATE ".$_SESSION['members_notification_table']."
		SET deleted=UNIX_TIMESTAMP()
		WHERE uid=:uid
	";
	try{
		$rsDeleteDupes = $mLink->prepare($query);
		$aValues = array(
			':uid' 	=> $UID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsDeleteDupes->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
}
*/

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
         