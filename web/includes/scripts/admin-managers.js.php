<?php
/*
Marketocracy Inc. | Beta Development
Admin Managers

Supporting files:
	AJAX		- process/ajax/admin-managers-processes.php
	JAVASCRIPT	- THIS DOCUMENT JAVASCRIPT includes/scripts/admin-general.js.php
	HTML		- includes/pages/admin-general.php
*/
$processLink	= 'admin-managers-processes.php';

?>

<script type="text/javascript">

function loadToggle(){
	$(".gen_camp_btn").click(function (){
		$('.show-camp-list').toggle();
	});
}


function toggleID(id){
	$('#'+id).toggle();	
}

function showExclude(tdID){
	
	var excludeValue = $('#'+tdID+'-exclude').val();
	
	if(excludeValue == '1'){
		$('#'+tdID+'-td').css("background-color", "#f0ad4e");
	}else{
		$('#'+tdID+'-td').css("background-color", "#f9f9f9");
	}

}

function loadArticleType(){
	$('#article_type2').change(function getArticleType() {
	
	var articleType = $(this).val();
		articleID	= $('#edit_article_id').val();
		
		
	$.ajax({
        type: "POST", 
        data: {process: 'load-article-fields',articleType:articleType,articleID:articleID,prefill:'1'},
        url: "process/ajax/action-center-processes.php",
		success: function(data){
			
			$('#load-edit-article-fields').html(data);
			loadArticleType();
			loadToggle();
				
		}
	});
	
});	
}


function loadEditForm(){
	$('form.edit-article-form').on('submit', function(e) {
		
		e.preventDefault();
		
		$('#edit-article-processing-btn').html('<button type="button" class="btn btn-default">Processing...</button>');
	
		//process php
		$.ajax({
			type: "POST", 
			data: $(".edit-article-form").serialize(),
			url: "process/ajax/action-center-processes.php?process=edit-article",
			
			success: function(data)
			{
				if (data.indexOf("note-danger") !=-1) {
					$('#edit-article-error').html(data);
					$('#edit-article-processing-btn').html('<input type="submit" value="Save Changes" id="submit-article" class="btn btn-info" />');
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
}

function editArticle(action,articleID){
	
	
	if(action == 'load'){
		$.ajax({
			type: "POST", 
			data: {action:action,articleID:articleID,process:'load-article-fields',prefill:'1'},
			url: "process/ajax/action-center-processes.php",
			
			success: function(data)
			{
				$('#load-edit-article-fields').html(data);
				loadArticleType();
				loadEditForm();	
				/*if (data.indexOf("note-danger") !=-1) {
					$('#add-article-error').html(data);
					
					//alert(data);
				}else{
					//$('#add-article-error').html(data);
					//alert(data);
					location.reload();
				}*/
			
			}
		});				
	}
	
	if(action == 'delete'){
		
		if (confirm('Are you sure you want to delete this article?')) {
			$.ajax({
				type: "POST", 
				data: {action:action,articleID:articleID,process:'delete-article'},
				url: "process/ajax/action-center-processes.php",
				
				success: function(data)
				{
					location.reload();
				
				}
			});		
		} else {
			// Do nothing!
		}
			
	}
	
	
}

$('#send-test-check').on('click', function(){
	
	if($(this).is(':checked')){
        $('#email-processing-btn').html('<input type="submit" value="Send Test Email" id="submit-email" class="btn btn-success" />');
	}else{
        $('#email-processing-btn').html('<input type="submit" value="Send Email" id="submit-email" class="btn btn-info" />');
	}
	//alert(sendTest);
	
});

$('.email-body').wysihtml5({
	"html": true,
	"link": true
	/*events: {
		
		load: function() {
			var some_wysi = $('.email-body').data('wysihtml5').editor;
			
			
			
		}
	}*/
});

$('form.create-campaign-form').on('submit', function() {
	
	$('#campaign-processing-btn').html('<button type="button" class="btn btn-default">Processing...</button>');
	
	//alert('hello');
	
	//process php
	$.ajax({
		type: "POST", 
		data: $(".create-campaign-form").serialize(),
		url: "process/ajax/admin-managers-processes.php?process=create-email-campaign",
		
		success: function(data)
		{
			$('#campaign-processing-btn').html('<input type="submit" value="Create Campaign" id="submit-campaign" class="btn btn-info" />');
			//alert(data);
			//$('#sendEmail-errors').html(data);
			if (data.indexOf("alert-danger") !=-1) {
				$('#create-campaign-errors').html(data);
				
				//alert(data);
			}else{
				
				location.reload();
			}
			
		}
	});
	
	return false;
});

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

function loadCampaignReport(campID){
	$.ajax({
		type: "POST", 
		data: {campID:campID, process: 'view-campaign-report'},
		url: "process/ajax/<?php echo $processLink;?>",
		
		success: function(data)
		{
			
			$('#load-campaign-report').html(data);
			
			/*$('campaign-processing-btn').html('<input type="submit" value="Create Campaign" id="submit-campaign" class="btn btn-info" />');
			//alert(data);
			//$('#sendEmail-errors').html(data);
			if (data.indexOf("note-danger") !=-1) {
				$('#create-campaign-errors').html(data);
				
				//alert(data);
			}else{
				
				//location.reload();
			}*/
			
		}
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
	
	var initCampTable = function() {
        var oTable = $('#campaign-table').dataTable( {           
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

        jQuery('#campaign-table_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#campaign-table_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#campaign-table_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#campaign-table_column_toggler input[type="checkbox"]').change(function(){
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
			initCampTable();
            
        }

    };

}();

$(document).ready(function() {
	

	loadArticles();
	loadToggle();

});

</script>