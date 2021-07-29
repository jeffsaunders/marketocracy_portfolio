<?php
//Start User Session
session_start();



// Load debug & error logging functions
require("../includes/system-debug-functions.php");

// Connect to DB
require("../../secure/dbconnect.php");

// Load any global functions
require("../includes/system-functions.php");

// Get global settings and functions
require("../includes/system-global.php");

$memberID = $_REQUEST['member'];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Old Profile Info</title>
</head>

<body>



<h1>Get Old Member Profile Info</h1>

<form action="process/fund-info-process.php?process=1" method="post" class="fund-info">
<label>Member ID:</label>
<input type="text" value="<?php echo $memberID;?>" name="member"/>
<input type="submit" value="Get Data" /><!--&nbsp;&nbsp;<input id="active_only" name="active_only" type="checkbox" value="1" checked="checked" /> Active Funds Only<br />-->
<small>Leave blank for all funds</small>

</form>

<div id="results">


</div>


<script src="../plugins/jquery-1.10.2.min.js" type="text/javascript"></script>
<script src="../plugins/jquery-migrate-1.2.1.min.js" type="text/javascript"></script>
<script>
//Process Reply Form via AJAX
$('form.fund-info').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $(".fund-info").serialize(),
        url: "process/fund-info-process.php?process=1",
        success: function(data)
        {
			//alert(data);
			$('#results').html(data);
			
		}
    });
	
	return false;
});


</script>
</body>
</html>