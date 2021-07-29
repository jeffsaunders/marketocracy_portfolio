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
<title>Get Strat Data</title>
</head>

<body>

<div>
    <h2>Get Strat Data</h2>
    <form action="process/build-mtm-process.php?process=1" method="post" class="strat-info">
        
        <label><input type="checkbox" value="process" checked name="api" /> Process through API</label><br />
        <small>If unchecked, this process will not submit to the data legacy deamons</small><br /><br />
        
        <label>Select API</label><br />
        <select name="api-type">
        	<option value="1">API 1</option>
            <option value="2">API 2</option>
        </select><br /><br />
        
        <label>Member ID:</label>
        <input type="text" value="<?php echo $memberID;?>" name="member"/><br /><br />
        <label>Fund ID:</label>
        <input type="text" value="" name="fund"/><br />
        <small>Leave blank for all funds</small><br /><br />
        
        <label>Select Processes</label>
        <select name="query">
        	<option value="all">All</option>
            <option value="allPositionInfo">allPositionInfo</option>
            <option value="tradesForFund">tradesForFund</option>
        </select><br /><br />
        
        <label>Trade Pull Start Date</label><br />
        <input type="text" name="tradeStartDate" /><br />
        <small>Format: YYYYMMDD</small><br /><br />
       	
        
        
        <input type="submit" value="Get Strat Data" /><!--&nbsp;&nbsp;<input id="active_only" name="active_only" type="checkbox" value="1" checked="checked" /> Active Funds Only<br />-->
        
    
    </form>
</div>

<div id="show-results">

</div>



<script src="../plugins/jquery-1.10.2.min.js" type="text/javascript"></script>
<script src="../plugins/jquery-migrate-1.2.1.min.js" type="text/javascript"></script>
<script>
//Process Reply Form via AJAX
$('form.strat-info').on('submit', function() {
	
	$('#show-results').html('<img src="<?php echo $_SESSION['site_root'];?>images/loading.gif" />');
	
	$.ajax({
        type: "POST",
        data: $(".strat-info").serialize(),
        url: "process/strat-info-process.php?process=1",
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