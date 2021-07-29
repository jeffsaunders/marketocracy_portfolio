<?php 
session_start();

// Load debug & error logging functions
require_once("../includes/system-debug-functions.php");

//Connect to DB
require_once("../../secure/dbconnect.php");

require_once("../includes/system-functions.php");



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Add new Fund Portlet.</title>
</head>

<body>

<div>
<h2>Add new fund Portlet</h2>
<form method="post" action="add-portlet-process.php?process=1" class="add-fund-portlet">
New Fund Portlet ID:<br />
<input type="text" name="portlet_id" value="<?php echo $memberID;?>" /><br />


<input type="submit" name="submit" value="Add New Fund Portlet" />

</form>
<div id="add-fund-portlet"></div>
</div>

<div>
<h2>Add new Dasboard Portlet</h2>
<form method="post" action="add-portlet-process.php?process=2" class="add-dash-portlet">
New Dashboard Portlet ID:<br />
<input type="text" name="portlet_id" value="<?php echo $memberID;?>" /><br />


<input type="submit" name="submit" value="Add New Dashboard Portlet" />

</form>
<div id="add-dash-portlet"></div>
</div>

<script src="../plugins/jquery-1.10.2.min.js" type="text/javascript"></script>
<script src="../plugins/jquery-migrate-1.2.1.min.js" type="text/javascript"></script>
<script>
//Process Reply Form via AJAX
$('form.add-fund-portlet').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $(".add-fund-portlet").serialize(),
        url: "add-portlet-process.php?process=1",
        success: function(data)
        {
			//alert(data);
			$('#add-fund-portlet').html(data);
			if(data == ""){
				
			}
		}
    });
	
	return false;
});

$('form.add-dash-portlet').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $(".add-dash-portlet").serialize(),
        url: "add-portlet-process.php?process=2",
        success: function(data)
        {
			//alert(data);
			$('#add-dash-portlet').html(data);
			if(data == ""){
				
			}
		}
    });
	
	return false;
});

</script>
</body>
</html>