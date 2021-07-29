<?php
/*
Marketocracy Inc. | Beta Development
Admin Managers

Supporting files:
	AJAX		- process/ajax/admin-managers-processes.php
	JAVASCRIPT	- THIS DOCUMENT JAVASCRIPT includes/scripts/admin-general.js.php
	HTML		- includes/pages/admin-general.php
*/
$processLink	= 'admin-membership-processes.php';

?>

<script type="text/javascript">

function loadSubList(ids,list){
	
	$('#load-sub-list').html('<img src="/images/ajax-loading.gif" /> Loading, this may take a minute.');
	$('#update-title').html(list);
	
	$.ajax({
        type: "POST", 
        data: {process: 'load-sub-list', ids:ids, 'list-name':list},
        url: "process/ajax/<?php echo $processLink;?>",
		success: function(data){
			
			$('#load-sub-list').html(data);
			
			//init:initArticleTable();
			
				
		}
	});
	
}


$('form.add-article-form').on('submit', function(e) {
		
	e.preventDefault();
	
	$('#article-processing-btn').html('<button type="button" class="btn btn-default">Processing...</button>');

	//process php
	$.ajax({
		type: "POST", 
		data: $(".add-article-form").serialize(),
		url: "process/ajax/<?php echo $processLink;?>?process=add-article",
		
		success: function(data)
		{
			if (data.indexOf("note-danger") !=-1) {
				$('#add-article-error').html(data);
				$('#article-processing-btn').html('<input type="submit" value="Add Article" id="submit-article" class="btn btn-info" />');
				//alert(data);
			}else{
				//$('#add-article-error').html(data);
				//alert(data);
				location.reload();
			}
			
		}
	});
	
	return false;
});

function loadArticles(){

	$.ajax({
        type: "POST", 
        data: {process: 'load-articles'},
        url: "process/ajax/<?php echo $processLink;?>",
		success: function(data){
			
			$('#load-articles').html(data);
			
			init:initArticleTable();
			
				
		}
	});
}

function loadCompositeData(fundID){
	
		$('#load-composite-data-form').html('<img src="/images/ajax-loading.gif" alt="Loading" />');	
	
		$.ajax({
			type: "POST", 
			data: {process:'load-composite-data',fundID:fundID},
			url: "process/ajax/<?php echo $processLink;?>",
			
			success: function(data)
			{
				//alert(data);
				$('#load-composite-data-form').html(data);			
		 		loadCompositeDataFrom(fundID);
				loadCompositeUploadForm();
			}
		});		
}

function addYear(fundID){
		
		$.ajax({ 
			type: "POST",
			data: $(".composite-data-form").serialize(),
			url: "process/ajax/<?php echo $processLink;?>?process=add-year",
			success: function(data)
			{
				
				
				$('#load-composite-data-form').html(data);			
		 		loadCompositeDataFrom(fundID);
				loadCompositeUploadForm();
				
			}
		});
}

function loadCompositeDataFrom(fundID){
	
	$('form.composite-data-form').submit(function() {
	
		//$('#submit-composite-data').html('<img src="/images/ajax-loading.gif" alt="Loading" />');
		
		$.ajax({ 
			type: "POST",
			data: $(".composite-data-form").serialize(),
			url: "process/ajax/<?php echo $processLink;?>?process=save-composite-data",
			success: function(data)
			{
				
				
				if((data.indexOf("alert-danger") > -1) == true ){
					
					$('#load-composite-errors').html(data);
					
					
				}else{
					//$('#email-warnings').html(data);
					loadCompositeData(fundID);
					
				}
				
			}
		});
		
		return false;
	});
		
}

function loadCompositeUploadForm(){
	
	$('form.upload-disclosure-form').submit(function() {
		
		$('#composite-upload-response').html('<div class="note note-warning"><h3>Uploading...</h3></div>');
		
		var formData = new FormData($(this)[0]);
		
		$.ajax({ 
			url: "process/ajax/<?php echo $processLink;?>?process=upload-composite",
			type: "POST",
			data: formData,
			async: false,
			success: function(data)
			{
				
				$('#composite-upload-response').html(data);
								
			},
			cache: false,
			contentType: false,
			processData: false,
		});
		
		return false;
	});
	
		
}

$('#article_type').change(function getArticleType() {
	
	var articleType = $(this).val();
	
	$.ajax({
        type: "POST", 
        data: {process: 'load-article-fields',articleType:articleType},
        url: "process/ajax/action-center-processes.php",
		success: function(data){
			
			$('#load-article-fields').html(data);
			//loadArticleForm();
			
				
		}
	});
	
});

var initArticleTable = function() {
	var oTable = $('#articles_table').dataTable( {           
		"oLanguage": {
			"sEmptyTable": "You do not have any articles."
		},
		"aoColumnDefs": [
			{ "aTargets": [ 0 ] },
			{ "width": "20%", "targets": 2 },
			{ "width": "1%", "targets": 1 }
		],
		"aaSorting": [[3, 'desc']], // set default column order 
		 "aLengthMenu": [
			[30, 60, 90 -1],
			[30, 60, 90, "All"] // change per page values here
		],
		// set the initial value
		"iDisplayLength": 25
	});
	
	/*new $.fn.dataTable.FixedColumns( oTable, {
		leftColumns: 1,
		rightColumns: 0	
	});*/
	
	/*new $.fn.dataTable.FixedHeader( oTable, {
		"offsetTop": 45,
		left: true
	} );*/

	jQuery('#articles_table_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
	jQuery('#articles_table_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
	jQuery('#articles_table_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

	$('#articles_table_column_toggler input[type="checkbox"]').change(function(){
		/* Get the DataTables object again - this is not a recreation, just a get of the object */
		var iCol = parseInt($(this).attr("data-column"));
		var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
		oTable.fnSetColumnVis(iCol, (bVis ? false : true));
	});
}

var TableAdvanced = function () {
	
	var initLedgerTable = function() {
        var oTable = $('#manager-table').dataTable( {           
            "oLanguage": {
				"sEmptyTable": "There are no ledger entries yet."
			},
			"aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[0, 'desc']], // set default column order 
            
			"aLengthMenu": [
                [30, 60, 90 -1],
                [30, 60, 90, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 35
        });
		
		/*new $.fn.dataTable.FixedColumns( oTable, {
			leftColumns: 1,
			rightColumns: 0	
		});*/
		
		/*new $.fn.dataTable.FixedHeader( oTable, {
			"offsetTop": 45,
			left: true
		} );*/

        jQuery('#manager-table_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#manager-table_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#manager-table_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#manager-table_column_toggler input[type="checkbox"]').change(function(){
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
            oTable.fnSetColumnVis(iCol, (bVis ? false : true));
        });
    }
	
	
    return {

        //main function to initiate the module
        init: function () {
            
            if (!jQuery().dataTable) {
                return;
            }
			
			initLedgerTable();
            
        }

    };

}();

$(document).ready(function() {
	
	$('.tree').treegrid({
		expanderExpandedClass: 'glyphicon glyphicon-minus',
		expanderCollapsedClass: 'glyphicon glyphicon-plus',
		initialState: 'collapsed'
	});
	loadArticles();

});

</script>