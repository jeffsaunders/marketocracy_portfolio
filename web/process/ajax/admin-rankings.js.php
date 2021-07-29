<?php
/*
Marketocracy Inc. 
Admin Rankings Scripts

Supporting files:
	AJAX		- process/ajax/admin-rankings-processes.php
	JAVASCRIPT	- THIS DOCUMENT JAVASCRIPT includes/scripts/admin-rankings.js.php
	HTML		- includes/pages/admin-rankings.php
*/
?>

<script type="text/javascript">



$('form.view-period-form').on('submit', function() {
	
	$('#period-submit-btn').html('<button type="button" class="btn btn-defualt">Processing...</button>');
	$('#rank-period-data').html('<img src="/images/ajax-loading.gif" alt="Loading" />');
	
	$.ajax({ 
        type: "POST",
        data: $(".view-period-form").serialize(),
        url: "process/ajax/admin-rankings-processes.php?process=view-rank-period",
        success: function(data)
        {
			$('#rank-period-data').html(data);
			$('#period-submit-btn').html('<input type="submit" name="submit-btn" value="View Period Data" class="btn btn-success" />');
			
			loadBenchForm();
			loadAarForm();
			loadOutlierForm();
			loadStageForm();
			loadPubForm();
        }
    });
	
	return false;
});

function loadBenchForm(){
	$('form.benchmark-form').on('submit', function() {
		
		$('#bench-form-submit').html('<button type="button" class="btn btn-defualt">Processing...</button>');
		$('#bench-form-errors').html('<img src="/images/ajax-loading.gif" alt="Loading" />');
		
		$.ajax({ 
			type: "POST",
			data: $(".benchmark-form").serialize(),
			url: "process/ajax/admin-rankings-processes.php?process=save-benchmarks",
			success: function(data)
			{
				$('#bench-form-errors').html(data);
				$('#bench-form-submit').html('<input type="submit" value="Save Benchmarks" class="btn btn-success" />');
				
				$('#upload-bench').modal('hide');
				$('#view-period-btn').click();
			}
		});
		
		return false;
	});	
}

function loadAarForm(){
	
	$('.aar-form').on('submit', function() {
		
		//alert('hello');
		
		$('#aar-form-submit').html('<button type="button" class="btn btn-defualt">Processing...</button>');
		$('#aar-form-errors').html('<img src="/images/ajax-loading.gif" alt="Loading" />');
		
		$.ajax({ 
			type: "POST",
			data: $(".aar-form").serialize(),
			url: "process/ajax/admin-rankings-processes.php?process=process-aar",
			success: function(data)
			{
				$('#aar-form-errors').html(data);
				$('#aar-form-submit').html('<input type="submit" value="Build AAR Table" class="btn btn-success" />');
				
				//$('#upload-bench').modal('hide');
				//$('#view-period-btn').click();
			}
		});
		
		return false;
	});	
}

function loadOutlierForm(){
	
	$('.outlier-form').on('submit', function() {
		
		//alert('hello');
		
		$('#outlier-form-submit').html('<button type="button" class="btn btn-defualt">Processing...</button>');
		$('#outlier-form-errors').html('<img src="/images/ajax-loading.gif" alt="Loading" />');
		
		$.ajax({ 
			type: "POST",
			data: $(".outlier-form").serialize(),
			url: "process/ajax/admin-rankings-processes.php?process=calc-outliers",
			success: function(data)
			{
				$('#outlier-form-errors').html(data);
				$('#outlier-form-submit').html('<input type="submit" value="Calculate Outliers" class="btn btn-success" />');
				
				//$('#upload-bench').modal('hide');
				//$('#view-period-btn').click();
			}
		});
		
		return false;
	});	
}

function loadStageForm(){
	
	$('.stage-form').on('submit', function() {
		
		//alert('hello');
		
		$('#stage-form-submit').html('<button type="button" class="btn btn-defualt">Processing...</button>');
		$('#stage-form-errors').html('<img src="/images/ajax-loading.gif" alt="Loading" />');
		
		$.ajax({ 
			type: "POST",
			data: $(".stage-form").serialize(),
			url: "process/ajax/admin-rankings-processes.php?process=build-stage-tables",
			success: function(data)
			{
				$('#stage-form-errors').html(data);
				$('#stage-form-submit').html('<input type="submit" value="Build Stage Tables" class="btn btn-success" />');
				
				//$('#upload-bench').modal('hide');
				//$('#view-period-btn').click();
			}
		});
		
		return false;
	});	
}

function loadPubForm(){
	
	$('.publish-form').on('submit', function() {
		
		//alert('hello');
		
		$('#publish-form-submit').html('<button type="button" class="btn btn-defualt">Processing...</button>');
		$('#publish-form-errors').html('<img src="/images/ajax-loading.gif" alt="Loading" />');
		
		$.ajax({ 
			type: "POST",
			data: $(".publish-form").serialize(),
			url: "process/ajax/admin-rankings-processes.php?process=publish-rankings",
			success: function(data)
			{
				$('#publish-form-errors').html(data);
				$('#publish-form-submit').html('<input type="submit" value="Publish Rankings" class="btn btn-success" />');
				
				//$('#upload-bench').modal('hide');
				//$('#view-period-btn').click();
			}
		});
		
		return false;
	});	
}

$('form.process-rankings').on('submit', function() {
	
	$('#submit-btn').html('<button type="button" class="btn btn-defualt">Processing...</button>');
	$('#load-results').html('<img src="/images/ajax-loading.gif" alt="Loading" />');
	
	$.ajax({ 
        type: "POST",
        data: $(".process-rankings").serialize(),
        url: "process/ajax/admin-rankings-processes.php?process=build-rankings-new",
        success: function(data)
        {
			$('#load-results').html(data);
			$('#submit-btn').html('<input type="submit" name="ca-symbol-change" value="Run Rankings" class="btn btn-success" />');
        }
    });
	
	return false;
});

$('form.gap-check').on('submit', function() {
	
	$('#gap-submit-btn').html('<button type="button" class="btn btn-defualt">Processing...</button>');
	$('#gap-results').html('<img src="/images/ajax-loading.gif" alt="Loading" />');
	
	$.ajax({ 
        type: "POST",
        data: $(".gap-check").serialize(),
        url: "process/ajax/admin-rankings-processes.php?process=gap-check",
        success: function(data)
        {
			$('#gap-results').html(data);
			$('#gap-submit-btn').html('<input type="submit" name="gap-check" value="Check For Gaps" class="btn btn-success" />');
        }
    });
	
	return false;
});

$('form.rankings-upload').on('submit', function() {
	
	$('#upload-submit-btn').html('<button type="button" class="btn btn-defualt">Processing...</button>');
	$('#upload-results').html('<img src="/images/ajax-loading.gif" alt="Loading" />');
	
	$.ajax({ 
        type: "POST",
        data: $(".rankings-upload").serialize(),
        url: "process/ajax/admin-rankings-processes.php?process=csv-upload",
        success: function(data)
        {
			$('#upload-results').html(data);
			$('#upload-submit-btn').html('<input type="submit" name="rankings_upload" value="Save Rankings" class="btn btn-success" />');
        }
    });
	
	return false;
});




$(document).ready(function(){
});

</script>