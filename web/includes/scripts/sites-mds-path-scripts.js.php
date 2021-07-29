<?php
/*
Marketocracy Data Services | Content Management
Admin General HTML

Supporting files:
	AJAX		- process/ajax/mds/mds-path-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/sites-mds-path-scripts.js.php
	HTML		- THIS DOCUMENT includes/pages/admin-sites-mds.php
*/


?>

<script type="text/javascript">

function collapseShow(toggle){
	
	if(toggle == 'collapse'){
		$('.path-portlet-body').addClass('display-hide');	
		$('#collapse-btn').html('<button type="button" class="btn btn-xs btn-info" onclick="collapseShow(\'show\');">Expand Portlets</button>');
	}else if(toggle == 'show'){
		$('.path-portlet-body').removeClass('display-hide');
		$('#collapse-btn').html('<button type="button" class="btn btn-xs btn-warning" onclick="collapseShow(\'collapse\');">Collapse Portlets</button>');
	}
	
}

function loadRows(pathLink){
	
	//alert(pathLink);
	
	$.ajax({
		type: "POST",
		data: {process:'3',pathLink:pathLink},
		url: "process/ajax/mds/mds-path-processes.php",
		success: function(data){
			$('#load-extra-rows-'+pathLink).html(data);
		}
	});
}

function addNewRow(pathLink){
	
	var seq = parseInt($('#new_psc_sequence_'+pathLink).val());
	
	seq = seq + 1;
	
	//alert(pathLink);
	
	$.ajax({
		type: "POST",
		data: $(".edit-path-form").serialize(),
		url: "process/ajax/mds/mds-path-processes.php?process=2&pathLink="+pathLink,
		success: function(data)
		{	
			//alert(data);
			loadRows(pathLink);			
			
			if((data.indexOf('danger') > -1) == false){
				
				$('#new_psc_title_'+pathLink).val('');
				$('#new_psc_column_text_'+pathLink).val('');
				$('#new_psc_content_'+pathLink).val('');
				$('#new_psc_sequence_'+pathLink).val(seq);
				$('#show-add-row-error-'+pathLink).html('');
								
				
			}else{
				$('#show-add-row-error-'+pathLink).html(data);
			}
	 
		}
	});
		
}

function deleteRow(pscID, pathLink, title){
	
	if (confirm('Are you sure you want to delete row: '+title)) {
    	$.ajax({
			type: "POST",
			data: {process:'4',pscID:pscID,pathLink:pathLink},
			url: "process/ajax/mds/mds-path-processes.php",
			success: function(data){
				
				if((data.indexOf('danger') > -1) == false){
					loadRows(pathLink);
					$('#show-add-row-error-'+pathLink).html('');
				}else{
					$('#show-add-row-error-'+pathLink).html(data);
				}
				
			}
		});
	} else {
		// Do nothing!
	}
		
}

function editPath(pathID){
	$.ajax({
        type: "POST",
        data: {process:'1',path:pathID},
        url: "process/ajax/mds/mds-path-processes.php",
        success: function(data)
        {
					
            //alert(data);
            $('#load-path-area').html(data);
     		
			loadForm();
        }
    });
}



function loadForm(){
	$('form.edit-path-form').on('submit', function() {
		$.ajax({
			type: "POST",
			data: $(".edit-path-form").serialize(),
			url: "process/ajax/mds/mds-path-processes.php?process=5",
			success: function(data)
			{
						
				//alert(data);
				$('#save-path-errors').html(data);
		 		
				if(data == ''){
					window.location.href = "?page=20-00-01-001&tab=paths";
				}
			}
		});
		
		return false;
	});
}

function editPage(pageID){
	$.ajax({
        type: "POST",
        data: {process:'1',page:pageID},
        url: "process/ajax/mds/mds-page-processes.php",
        success: function(data)
        {
					
            //alert(data);
            $('#load-page-area').html(data);
     		
			loadPageForm();
        }
    });
}

function loadPageForm(){
	$('form.edit-page-form').on('submit', function() {
		$.ajax({
			type: "POST",
			data: $(".edit-page-form").serialize(),
			url: "process/ajax/mds/mds-page-processes.php?process=5",
			success: function(data)
			{
						
				//alert(data);
				$('#save-page-errors').html(data);
				
				if(data == ''){
					window.location.href = "?page=20-00-01-001&tab=pages";
				}
		 
			}
		});
		
		return false;
	});
}

function createPage(){
	
	$.ajax({
        type: "POST",
        data: {process:'3'},
        url: "process/ajax/mds/mds-page-processes.php",
        success: function(data)
        {
					
            //alert(data);
            $('#load-page-area').html(data);
     		
			loadPageForm();
        }
    });
		
}
</script>