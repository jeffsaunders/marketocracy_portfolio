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
<title>Reprice Member's Funds</title>
</head>

<body>

<div>
    <h2>Select Data Type</h2>
    <form action="process/calc-badges-process.php?process=5" method="post" class="reprice">
        <label>Data Type:</label>
        <select name="data-type" onchange="loadField();" id="data-type">
        	<option>Select Type</option>
            <option value="csv-member-id">Member ID (CSV)</option>
            <option value="csv-fund-key">Fund Key (CSV)</option>
            <option value="csv-username">Username (CSV)</option>
            <option value="field-member-id">Member ID (Field Comma Seperated)</option>
            <option value="field-fund-key">Fund Key (Field Comma Seperated)</option>
            <option value="field-username">Username (Field Comma Seperated)</option>
        </select>
        
        
    
    </form>
</div>
<div id="load-field">
        <?php /*?><input type="submit" value="Get Members" /><?php */?>
</div><!--load-field-->
<div id="show-results"></div>



<script src="../plugins/jquery-1.10.2.min.js" type="text/javascript"></script>
<script src="../plugins/jquery-migrate-1.2.1.min.js" type="text/javascript"></script>

<script type="text/javascript" src="../plugins/bootstrap-fileupload/bootstrap-fileupload.js"></script>
<script type="text/javascript" src="../utilities/uploads/js/jquery.form.min.js"></script>
<script>

function uploadForm(){
	var options = { 
			target: '#load-files',   // target element(s) to be updated with server response 
			beforeSubmit: beforeSubmit,  // pre-submit callback 
			success: afterSuccess,  // post-submit callback 
			resetForm: false        // reset the form after successful submit 
		}; 
		
	 $('.upload-text-file').submit(function() { 
		$(this).ajaxSubmit(options);  			
		// always return false to prevent standard browser submit and page navigation 
		return false; 
	}); 
	
	function afterSuccess(){
		
	}
	function beforeSubmit(){
		$('#load-files').html('<img src="<?php echo $siteRoot;?>/images/ajax-loading.gif" />');
	}
}

function loadField(){
	
	$.ajax({
        type: "POST",
        data: $(".reprice").serialize(),
        url: "process/reprice-members-process.php?process=get-field",
        success: function(data)
        {
			//alert(data);
			$('#load-field').html(data);
			
			uploadForm();
			
		}
    });	
}

//Process Reply Form via AJAX
$('form.reprice').on('submit', function() {
	
	$('#show-results3').html('<img src="<?php echo $_SESSION['site_root'];?>images/loading.gif" />');
	
	$.ajax({
        type: "POST",
        data: $(".reprice").serialize(),
        url: "process/calc-badges-process.php?process=5",
        success: function(data)
        {
			//alert(data);
			$('#show-results3').html(data);
			
		}
    });
	
	return false;
});






</script>
</body>
</html>