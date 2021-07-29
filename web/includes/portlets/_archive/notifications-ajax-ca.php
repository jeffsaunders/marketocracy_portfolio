<?php
session_start();

require("../system-functions.php");
require("../../../secure/dbconnect.php");

$query = "
	SELECT *
	FROM ".$_SESSION['members_notification_table']."
	WHERE member_id=:member_id AND deleted='0' AND type='corporate-action'
	ORDER BY timestamp DESC
";

//Fund Symbols
try{
	$rsSystemNoteCA = $mLink->prepare($query);
	$aValues = array(
		':member_id' 	=> $_SESSION['member_id']
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsSystemNoteCA->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

while($systemNote = $rsSystemNoteCA->fetch(PDO::FETCH_ASSOC)) {

?>
<li>
	<?php
	if(!empty($systemNote['link'])){?><a href="<?php echo $systemNote['link'];?>"><?php } ?>
	<div class="col1">
	  <div class="cont">
		 <div class="cont-col1">
			<div class="label label-sm label-<?php echo $systemNote['status'];?>">                        
			   <i class="icon-<?php echo $systemNote['icon'];?>"></i>
			</div>
		 </div>
		 <div class="cont-col2">
			<div class="desc">
			   <?php if(!empty($systemNote['title'])){ echo "<strong>".$systemNote['title']."</strong> - ";} echo $systemNote['notification'];?>
			   <?php if(!empty($systemNote['link'])){?>
			   <span class="label label-sm label-<?php echo $systemNote['status'];?>">
			   <i class="icon-share-alt"></i>
			   </span>
			   <?php } ?>   
			</div>
		 </div>
	  </div>
	</div>
	<?php if(!empty($systemNote['link'])){?></a><?php }?>
	<div class="col2">
	  <div class="date">
		 <?php
		 echo time_past($systemNote['timestamp']);
		 ?>
		 <a href="" class="note-actions"><i class="icon-eye-open"></i></a>
		 <a href="" class="note-actions"><i class="icon-remove"></i></a>
	  </div>
	</div>
	
</li>
<?php
}
if($systemNote['notification'] == ""){
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
			   No Corporate Action Notifications
			</div>
		 </div>
	  </div>
	</div></li>';
}
?>
             