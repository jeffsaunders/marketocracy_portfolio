<?php
/*
Marketocracy Inc. | Beta Development
Admin Member Reprice Scripts

Supporting files:
	AJAX		- process/ajax/admin-ca-processes.php
	JAVASCRIPT	- THIS DOCUMENT JAVASCRIPT includes/scripts/admin-ca.js.php
	HTML		- includes/pages/admin-ca.php
*/
?>

<script type="text/javascript">

$('form.ca-symbol-change').on('submit', function() {
	
	$('#symbol-change-results').html('<img src="/images/ajax-loading.gif" alt="Loading" />');
	
	$.ajax({ 
        type: "POST",
        data: $(".ca-symbol-change").serialize(),
        url: "process/ajax/admin-ca-processes.php?process=symbol-change",
        success: function(data)
        {
			$('#symbol-change-results').html(data);
			loadProcessTable();
        }
    });
	
	return false;
});

/*function loadProcessTable(){
	$.ajax({ 
        type: "POST",
        data: {process: 'load-processed-funds'},
        url: "process/ajax/admin-ca-processes.php",
        success: function(data)
        {
			$('#load-processed-table').html(data);
			
        }
   });
} */

function runStrat(uid,funds){
	
	$('#process-btn-'+uid).html('<a href="javascript:void(0):" class="btn btn-info btn-sm" style="margin-bottom:5px;">Running <img src="/images/loading.gif" /></a>');
	
	//var funds = $('fund_ids_'+uid).val();
	
	//alert(uid);
	
	$.ajax({ 
        type: "POST",
        data: {process: 'run-strat',funds:funds,uid:uid},
        url: "process/ajax/admin-ca-processes.php",
        success: function(data)
        {
			//$('#load-processed-table').html(data);
			$('#process-btn-'+uid).html('<a href="javascript:void(0):" class="btn btn-warning btn-sm" style="margin-bottom:5px;" onClick="runStrat(\''+uid+'\',\''+funds+'\');">Re-Run Stratification Rebuild</a>');
        }
    });	
}

function rerunChange(uid){
	
	$('#rerun-btn-'+uid).html('<a href="javascript:void(0);" onclick="rerunChange(\''+uid+'\');" class="btn btn-default btn-sm">Running <img src="/images/loading.gif" /></a>');
	
	//var funds = $('fund_ids_'+uid).val();
	
	//alert(uid);
	
	$.ajax({ 
        type: "POST",
        data: {process: 're-run-change',uid:uid,api: 'api2'},
        url: "process/ajax/admin-ca-processes.php",
        success: function(data)
        {
			//$('#load-processed-table').html(data);
			$('#rerun-btn-'+uid).html('<a href="javascript:void(0);" onclick="rerunChange(\''+uid+'\');" class="btn btn-default btn-sm">Re-Run Ticker Change</a>');
        }
    });	
}

$(document).ready(function(){
	//loadProcessTable();	
});

</script>