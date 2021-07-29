<?php

//FEEDBACK FORM NOTIFICATION
$feedbackStatus = $_GET['feedback'];

if($feedbackStatus == "success") {?>
<div class="alert alert-block alert-success fade in">
      <button type="button" class="close" data-dismiss="alert"></button>
      <h4><strong>Feedback Form Submit Successful!</strong></h4>
      
      <p>Thank you for taking the time to fill out our feedback form. Your feedback is important to the future progress of our Trading System.</p>
</div>		
<?php }


//DEMO NOTIFICATION

if($demoNote == "on") {?>
<div class="alert alert-block alert-info fade in">
      <button type="button" class="close" data-dismiss="alert"></button>
      <h4><strong>You are currently viewing our DEMO SITE!</strong></h4>
      
      <p>All data is placeholder information only. Please feel free to navigate throughout the pages!</p>
      
      <?php /*?><p><strong>Link Source: <?php echo $_SESSION['referral_source']; ?></strong></p><?php */?>
</div>
<?php }//end demoNote IF


//System Notification Switch
switch($status) {
	case "mysql-select-pages": $statusType = "danger"; $statusTitle = "Unable to select from Pages"; $statusBody = "".mysql_error()."";break;	
}

if(!empty($status)){?>
    <div class="alert alert-block alert-<?php echo $statusType; ?> fade in">
        <button type="button" class="close" data-dismiss="alert"></button>
        <h4><strong><?php echo $statusTitle; ?></strong></h4>
    
        <p><?php echo $statusBody; ?></p>
    </div>
<?php 
}//end if not empty $statusBod

$errorNote = $_REQUEST['note'];

if($errorNote == "08-01-00-002-1"){ ?>
<div class="alert alert-block alert-danger fade in">
      <button type="button" class="close" data-dismiss="alert"></button>
      <h4><strong>INVALID TICKET ID</strong></h4>
      
      <p>You have tried to access an invalid Ticket ID, please select a Ticket below to view.</p>
      
      <?php /*?><p><strong>Link Source: <?php echo $_SESSION['referral_source']; ?></strong></p><?php */?>
</div>
<?php	
}

?>