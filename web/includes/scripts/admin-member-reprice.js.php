<?php
/*
Marketocracy Inc. | Beta Development
Admin Member Reprice Scripts

Supporting files:
	AJAX		- process/ajax/admin-member-reprice-processes.php
	JAVASCRIPT	- THIS DOCUMENT JAVASCRIPT includes/scripts/admin-member-reprice.js.php
	HTML		- includes/pages/admin-member-reprice.php
*/
$processFile = 'process/ajax/admin-member-reprice-processes.php';

?>

<script type="text/javascript">

function loadDateRangeForm(){
	
	$('form.dateRangeForm').on('submit', function() {
		
		$('#reload-reprice-history').html('loading...');
		
		//var dateRange = $('#dateRange').val();
		
		//alert(dateRange);
		
		$.ajax({
			type: "POST",
			data: $(".dateRangeForm").serialize(),
			url: "<?php echo $processFile;?>?process=load-reprice-history",
			success: function(data)
			{
				//alert(data);
				$('#load-reprice-history').html(data);
				
				loadDateRangeForm();
				
			}
		});	
		
	return false;
	});
		
}


function loadRepriceHistory(){
	$('#load-reprice-history').html('loading...');
	
	
	
	$.ajax({ 
        type: "POST",
        data: {process: 'load-reprice-history'},
        url: "<?php echo $processFile;?>",
        success: function(data)
        {
			$('#load-reprice-history').html(data);
			loadDateRangeForm();
        }
    });
}

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
		
		loadRepriceHistory();
		
	}
	function beforeSubmit(){
		$('#load-files').html('<img src="<?php echo $siteRoot;?>/images/ajax-loading.gif" />');
	}
}

function loadField(){
	
	$.ajax({
        type: "POST",
        data: $(".reprice").serialize(),
        url: "<?php echo $processFile;?>?process=get-field",
        success: function(data)
        {
			//alert(data);
			$('#load-field').html(data);
			
			uploadForm();
			
		}
    });	
}

function runStrat(uid,funds){
	
	$('#process-btn-'+uid).html('<a href="javascript:void(0):" class="btn btn-info btn-sm" style="margin-bottom:5px;">Running <img src="/images/loading.gif" /></a>');
	
	//var funds = $('fund_ids_'+uid).val();
	
	//alert(uid);
	
	$.ajax({ 
        type: "POST",
        data: {process: 'run-strat',funds:funds, uid:uid, section:'member-reprice'},
        url: "process/ajax/admin-ca-processes.php",
        success: function(data)
        {
			//$('#load-processed-table').html(data);
			$('#process-btn-'+uid).html('<a href="javascript:void(0):" class="btn btn-info btn-sm" style="margin-bottom:5px;" onClick="runStrat(\''+uid+'\',\''+funds+'\');">Run Stratification Rebuild</a><br />'+data);
			//alert(data);
        }
    });	
}

$(document).ready(function(){
	loadRepriceHistory();	
});

</script>