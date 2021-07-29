<?php 
session_start();

// Load debug & error logging functions
require_once("../includes/system-debug-functions.php");

//Connect to DB
require_once("../../secure/dbconnect.php");

require_once("../includes/system-functions.php");

$memberID 	= $_REQUEST['member'];

$startDayVal	= date('d');
$startMonthVal	= date('m');
$startYearVal	= date('Y');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Build Month By Month</title>
</head>

<body>

<div>
    <h2>Build Month To Month Data</h2>
    <form action="process/build-mtm-process2.php?process=1" method="post" class="build-mtm">
        
        <label>From Date</label>
        <select name="month" id="month" class="custom-select">
            <option value="<?php echo $startMonthVal;?>"><?php echo $startMonthVal; ?></option>
            <option disabled>--</option>
            <?php
            echo date_list($mLink, 'month', $startMonth);
            ?>
        </select>
        /
        <select name="day" id="day" class="custom-select">
            <option value="<?php echo $startDayVal;?>"><?php echo $startDayVal; ?></option>
            <option disabled>--</option>
            <?php
            echo date_list($mLink, 'day', $startDay);
            ?>
        </select>
        /
        <select name="year" id="year" class="custom-select">
            <option value="<?php echo $startYearVal;?>"><?php echo $startYearVal; ?></option>
            <option disabled>----</option>
            <?php
            echo date_list($mLink, 'year', NULL, $startYear, false);
            ?>
        </select>
        <br /><br />
        <label>Member ID:</label>
        <input type="text" value="<?php echo $memberID;?>" name="member"/><br />
        <small>Leave blank for all members</small><br /><br />
        
        
        <input type="submit" value="Build Month To Month Data" /><!--&nbsp;&nbsp;<input id="active_only" name="active_only" type="checkbox" value="1" checked="checked" /> Active Funds Only<br />-->
        
    
    </form>
</div>

<div id="show-results">

</div>



<script src="../plugins/jquery-1.10.2.min.js" type="text/javascript"></script>
<script src="../plugins/jquery-migrate-1.2.1.min.js" type="text/javascript"></script>
<script>
//Process Reply Form via AJAX
$('form.build-mtm').on('submit', function() {
	
	$('#show-results').html('<img src="<?php echo $_SESSION['site_root'];?>images/loading.gif" />');
	
	$.ajax({
        type: "POST",
        data: $(".build-mtm").serialize(),
        url: "process/build-mtm-process2.php?process=1",
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