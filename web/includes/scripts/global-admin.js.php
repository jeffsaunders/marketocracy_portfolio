<?php
/*
Global Include file for portlets that will be used site wide
Supporting Files:
Scripts: includes/scripts/global-admin.js.php THIS FILE
Process: process/ajax/global-admin-processes.php
*/

$processFile = 'process/ajax/global-admin-processes.php';
?>
<script type="text/javascript">


//GLOBAL API SCRIPTS
var apiRefresh = 3000;

$(document).ready(apiStatus = function() {
	
	//$('#load-api-info').html('<img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" /> Loading Trades');
	
	$.ajax({
		data: {process: 'load-api-status'},
      	url: '<?php echo $processFile;?>',
      	success: function(data) {
        	$('#load-api-info').html(data);
		
      	}
    
    });
	
	$.ajax({
		data: {process: 'load-api-title'},
      	url: '<?php echo $processFile;?>',
      	success: function(data) {
        	$('#load-api-title').html(data);
		
      	}
    
    });
	
});

var refreshInterval = setInterval(apiStatus,apiRefresh);

</script>