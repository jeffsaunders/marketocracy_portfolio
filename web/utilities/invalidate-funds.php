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
<title>Invalidate Funds</title>
</head>

<body>

<div>
    <h2>Invalidate Funds</h2>
    <form action="" method="post" class="reprice">
        <input type="submit" value="Process Invalidated Funds" name="submit"/>
        
        
    
    </form>
</div>

<div id="show-results"></div>



<script src="../plugins/jquery-1.10.2.min.js" type="text/javascript"></script>
<script src="../plugins/jquery-migrate-1.2.1.min.js" type="text/javascript"></script>

<script type="text/javascript" src="../plugins/bootstrap-fileupload/bootstrap-fileupload.js"></script>
<script type="text/javascript" src="../utilities/uploads/js/jquery.form.min.js"></script>
<script>



//Process Reply Form via AJAX
$('form.reprice').on('submit', function() {
	
	$('#show-results').html('<img src="<?php echo $_SESSION['site_root'];?>images/loading.gif" />');
	
	$.ajax({
        type: "POST",
        data: $(".reprice").serialize(),
        url: "process/invalidate-funds-process.php?process=invalidate-funds",
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