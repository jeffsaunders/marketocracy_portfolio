<?php 
session_start();

// Load debug & error logging functions
require_once("../includes/system-debug-functions.php");

//Connect to DB
require_once("../../secure/dbconnect.php");

require_once("../includes/system-functions.php");

$memberID = $_REQUEST['member'];


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Build Ratios</title>
</head>

<body>

<div>
    <h2>Build Winning Ratio Data.</h2>
    <form action="process/build-ratio-process.php?process=1" method="post" class="build-ratio">
        <label>Member ID:</label>
        <input type="text" value="<?php echo $memberID;?>" name="member"/><br />
        <small>Leave blank for all members</small><br /><br />
        
        
        <input type="submit" value="Build Ratio Data" /><!--&nbsp;&nbsp;<input id="active_only" name="active_only" type="checkbox" value="1" checked="checked" /> Active Funds Only<br />-->
        
    
    </form>
</div>

<div id="show-results">

</div>



<script src="../plugins/jquery-1.10.2.min.js" type="text/javascript"></script>
<script src="../plugins/jquery-migrate-1.2.1.min.js" type="text/javascript"></script>
<script>
//Process Reply Form via AJAX
$('form.build-ratio').on('submit', function() {
	
	$('#show-results').html('<img src="<?php echo $_SESSION['site_root'];?>images/loading.gif" />');
	
	$.ajax({
        type: "POST",
        data: $(".build-ratio").serialize(),
        url: "process/build-ratio-process.php?process=1",
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