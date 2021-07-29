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
$testDate = $_REQUEST['test'];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<link href="<?php echo $_SESSION['site_root'];?>css/style-minc.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $_SESSION['site_root'];?>css/style.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $_SESSION['site_root'];?>css/style-responsive.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $_SESSION['site_root'];?>css/plugins.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $_SESSION['site_root'];?>css/themes/default.css" rel="stylesheet" type="text/css" id="style_color"/>
<link href="<?php echo $_SESSION['site_root'];?>css/custom.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $_SESSION['site_root'];?>plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $_SESSION['site_root'];?>plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $_SESSION['site_root'];?>plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $_SESSION['site_root'];?>plugins/bootstrap-toastr/toastr.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="<?php echo $_SESSION['site_root'];?>plugins/select2/select2_mds.css" />
<link rel="stylesheet" href="<?php echo $_SESSION['site_root'];?>plugins/data-tables/DT_bootstrap.css" />
<link href="<?php echo $_SESSION['site_root'];?>plugins/bootstrap-tour-master/assets/css/bootstrap-tour.css" rel="stylesheet">	<!-- END PAGE LEVEL PLUGIN STYLES -->

<title>Find Missing Data</title>
</head>

<body>
<div style="background:#fff;padding:20px;">
<h1>Missing Data</h1>

<form action="process/find-data-process.php?process=1" method="post" class="find-data">
<label>Only active Funds <input type="checkbox" name="active_only" /></label><br />
<label>Member ID:</label>
<input type="text" value="<?php echo $memberID;?>" name="member"/>
<input type="hidden" value="<?php echo $testDate;?>" name="test" />
<input type="submit" value="Run Report" /><!--&nbsp;&nbsp;<input id="active_only" name="active_only" type="checkbox" value="1" checked="checked" /> Active Funds Only<br />-->
<small>Leave blank for all funds</small>

</form>
<br />
<br />
<div id="showQueryBtn"></div>

<table class="table table-striped table-bordered table-hover table-full-width" style="margin-top:10px;" id="missing-data">
	<?php /*?><thead>
    	<tr>
        	<th>Fund</th>
        	<th>Aggregate</th>
            <th>Alpha/Beta</th>
            <th>Live Price</th>
            <th>Price Run</th>
            <th>Strat. Basic</th>
        </tr>
    </thead><?php */?>
    
    <tbody id="report-data">
    	
    </tbody>
</table>

<div ></div>
</div><!--background-->


<script src="../plugins/jquery-1.10.2.min.js" type="text/javascript"></script>
<script src="../plugins/jquery-migrate-1.2.1.min.js" type="text/javascript"></script>
<script>
//Process Reply Form via AJAX
$('form.find-data').on('submit', function() {
	
	$('#report-data').html('<img src="<?php echo $_SESSION['site_root'];?>images/loading.gif" />');
	$.ajax({
        type: "POST",
        data: $(".find-data").serialize(),
        url: "process/find-data-process.php?process=1",
        success: function(data)
        {
			//alert(data);
			$('#report-data').html(data);
			$('#showQueryBtn').html('<a href="javascript:void(0);" class="btn btn-info" onclick="getAllRecords();">Get All Missing Records</a>');
			if(data == ""){
				
			}
		}
    });
	
	return false;
});

function runQuery(query, elementID){
	
	$('#'+elementID).html('<img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" /> Fetching Data...');
	
	$.ajax({
        type: "POST",
        data: $(".find-data").serialize(),
        url: "process/find-data-process.php?process=2&query=" + query,
        success: function(data)
        {
			//alert(data);
			$('#'+elementID).html(data);
			if(data == ""){
				
			}
		}
    });
}

function getAllRecords(){
	
	var seconds = $('#query-count').val();
		minutes	= (seconds / 60);
	
	alert('Estimated Time: '+minutes+' min.');
	
	$.ajax({
        type: "POST",
        data: $(".run-queries").serialize(),
        url: "process/find-data-process.php?process=3",
        success: function(data)
        {
			//alert(data);
			$('#report-data').html(data);
			$('#showQueryBtn').html('<a href="javascript:void(0);" class="btn btn-info" onclick="getAllRecords();">Get All Missing Records</a>');
			if(data == ""){
				
			}
		}
    });
}

</script>
</body>
</html>