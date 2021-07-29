<?php 
session_start();

// Load debug & error logging functions
require_once("../includes/system-debug-functions.php");

//Connect to DB
require_once("../../secure/dbconnect.php");

require_once("../includes/system-functions.php");

$ticketID = $_REQUEST['ticket'];


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Export Tickets</title>
</head>

<body>

<div>
    <h2>Export Tickets</h2>
    <form action="process/export-tickets-process.php?process=1" method="post" class="export-tickets">
        <label>Ticket Number:</label>
        <input type="text" value="<?php echo $ticketID;?>" name="ticket"/><br />
        <small>Leave blank for all tickets</small><br /><br />
        
        
        <input type="submit" value="Process Tickets" /><!--&nbsp;&nbsp;<input id="active_only" name="active_only" type="checkbox" value="1" checked="checked" /> Active Funds Only<br />-->
        
    
    </form>
</div>

<div id="show-results">

</div>



<script src="../plugins/jquery-1.10.2.min.js" type="text/javascript"></script>
<script src="../plugins/jquery-migrate-1.2.1.min.js" type="text/javascript"></script>
<script>

function checkRadio(radioID){
	
	$('#'+radioID).attr('checked', true);
		
}
//Process Reply Form via AJAX
$('form.export-tickets').on('submit', function() {
	
	$('#show-results').html('<img src="<?php echo $_SESSION['site_root'];?>images/loading.gif" />');
	
	$.ajax({
        type: "POST",
        data: $(".export-tickets").serialize(),
        url: "process/export-tickets-process.php?process=1",
        success: function(data)
        {
			//alert(data);
			$('#show-results').html(data);
			
			//loadForm();
			
		}
    });
	
	return false;
});

/*function loadForm(){
	
	
	$('form.export-csv').on('submit', function() {
		alert($(".export-csv").serialize());
		
		$('#show-results').html('<img src="<?php echo $_SESSION['site_root'];?>images/loading.gif" />');
		
		$.ajax({
			type: "POST",
			data: $(".export-tickets").serialize(),
			url: "process/export-tickets-process.php?process=2",
			success: function(data)
			{
				//alert(data);
				$('#show-results').html(data);
				
				//loadForm();
				
			}
		});
		
		return false;
	});
}*/



</script>
</body>
</html>