<?php
/*
TRADE OPEN ORDERS - JAVASCRIPT FILE

SUPPORTING FILES:
_______________________________________________________________

AJAX 			- process/ajax/trade-open-orders-processes.php
PHP JAVASCRIPT	- THIS FILE includes/scripts/trade-open-orders.js.php
HTML 			- includes/pages/trade-open-orders.php
_______________________________________________________________

*/
?>
<script>


function updateStatus(uid,status){
	
	$.ajax({ 
        type: "POST",
        data: {uid:uid, status:status, process:'update-anom-status'},
        url: "process/ajax/admin-rankings-processes.php",
        success: function(data)
        {
			$('#update-anom-'+uid).html(data);
			
        }
    });
	
}

$('#period-select').change(function() {
	
	var getDate	=	$('#period-select').val();
	//alert(getDate);
	
	window.location.href = "?page=06-00-00-011&date="+getDate;
	
});

function excludeFund(fundID, fundSymbol){
	
	$('#exclude-fund-symbol').html(fundSymbol);
	$('#setFundID').val(fundID);
	loadExcludeForm(fundID, fundSymbol);
}

function includeFund(fundID, fundSymbol){
	
	$('#include-fund-symbol').html(fundSymbol);
	$('#setIncludeFundID').val(fundID);
	loadIncludeForm(fundID, fundSymbol);
}

function loadExcludeForm(fundID, fundSymbol){
	
	$('.exclude-form').on('submit', function() {
		
		//alert('hello');
		
		$('#exclude-form-submit').html('<button type="button" class="btn btn-defualt">Processing...</button>');
		$('#exclude-form-errors').html('<img src="/images/ajax-loading.gif" alt="Loading" />');
		
		$.ajax({ 
			type: "POST",
			data: $(".exclude-form").serialize(),
			url: "process/ajax/admin-rankings-processes.php?process=exclude-fund",
			success: function(data)
			{
				$('#exclude-form-errors').html(data);
				$('#exclude-form-submit').html('<input type="submit" value="Exclude Fund" class="btn btn-success" />');
				
				
				$('#rankAction-'+fundID).html('<a href="#include" class="btn btn-xs btn-info" data-toggle="modal" onClick="includeFund(\''+fundID+'\', \''+fundSymbol+'\');">Include In Rankings</a>');
				$('#exclude').modal('hide');
				//$('#view-period-btn').click();
			}
		});
		
		return false;
	});	
}

function loadNotes(fundID, fundSymbol, username, asOfTimestamp, asOfDate){
	
	$.ajax({ 
		type: "POST",
		data: {process:'load-note-form',fundID:fundID,fundSymbol:fundSymbol,username:username,asOfTimestamp:asOfTimestamp,asOfDate:asOfDate},
		url: "process/ajax/admin-rankings-processes.php",
		success: function(data)
		{
			$('#load-note-form').html(data);
			
			
			loadNotesForm();
			loadSupportForm();
			
			//$('#view-period-btn').click();
		}
	});
		
}

$('#notes-modal').on('hidden.bs.modal', function(){
	//alert('hi');
	location.reload();	
});

function loadNotesForm(fundID, fundSymbol){
	
	$('.note-form').on('submit', function() {
		
		//alert('hello');
		
		$('#note-form-submit').html('<button type="button" class="btn btn-defualt">Processing...</button>');
		$('#load-all-notes').html('<img src="/images/ajax-loading.gif" alt="Loading" />');
		
		$.ajax({ 
			type: "POST",
			data: $(".note-form").serialize(),
			url: "process/ajax/admin-rankings-processes.php?process=save-note",
			success: function(data)
			{
				$('#load-all-notes').html(data);
				$('#note-form-submit').html('<input type="submit" value="Save Note" class="btn btn-success" />');
				
				
			}
		});
		
		return false;
	});	
}

function loadSupportForm(){
	
	//alert('loaded');
	$('.support').on('submit', function() {
		
		//alert('submit');
		//alert('hello');
		
		$('#support-errors').html('<img src="/images/ajax-loading.gif" alt="Loading" />');
		
		$.ajax({ 
			type: "POST",
			data: $(".support").serialize(),
			url: "process/ajax/admin-rankings-processes.php?process=support-ticket",
			success: function(data)
			{
				$('#support-errors').html(data);
				
				
			}
		});
		
		return false;
	});			
}

function loadIncludeForm(fundID, fundSymbol){
	
	$('.include-form').on('submit', function() {
		
		//alert('hello');
		
		$('#include-form-submit').html('<button type="button" class="btn btn-defualt">Processing...</button>');
		$('#include-form-errors').html('<img src="/images/ajax-loading.gif" alt="Loading" />');
		
		$.ajax({ 
			type: "POST",
			data: $(".include-form").serialize(),
			url: "process/ajax/admin-rankings-processes.php?process=include-fund",
			success: function(data)
			{
				$('#include-form-errors').html(data);
				$('#include-form-submit').html('<input type="submit" value="Include Fund" class="btn btn-success" />');
				
				
				$('#rankAction-'+fundID).html('<a href="#exclude" class="btn btn-xs btn-danger" data-toggle="modal" onClick="excludeFund(\''+fundID+'\', \''+fundSymbol+'\');">Exclude From Rankings</a>');
				$('#include').modal('hide');
				//$('#view-period-btn').click();
			}
		});
		
		return false;
	});	
}

var RankTables = function () {

     var tenYear = function() {
        var tenYearTable = $('#ten_year_table').dataTable( {           
            "aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[4, 'desc']],
             "aLengthMenu": [
                [100, 75, 50, 25, -1],
                [100, 75, 50, 25, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 102,
        });

        jQuery('#ten_year_table_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#ten_year_table_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#ten_year_table_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#ten_year_table_column_toggler input[type="checkbox"]').change(function(){
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            var bVis = tenYearTable.fnSettings().aoColumns[iCol].bVisible;
            tenYearTable.fnSetColumnVis(iCol, (bVis ? false : true));
        });
    }
	
	var fiveYear = function() {
        var fiveYearTable = $('#five_year_table').dataTable( {           
            "aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[0, 'asc']],
             "aLengthMenu": [
                [100, 75, 50, 25, -1],
                [100, 75, 50, 25, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 100,
        });

        jQuery('#five_year_table_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#five_year_table_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#five_year_table_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#five_year_table_column_toggler input[type="checkbox"]').change(function(){
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            var bVis = fiveYearTable.fnSettings().aoColumns[iCol].bVisible;
            fiveYearTable.fnSetColumnVis(iCol, (bVis ? false : true));
        });
    }
	
	var threeYear = function() {
        var threeYearTable = $('#three_year_table').dataTable( {           
            "aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[0, 'asc']],
             "aLengthMenu": [
                [100, 75, 50, 25, -1],
                [100, 75, 50, 25, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 100,
        });

        jQuery('#three_year_table_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#three_year_table_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#three_year_table_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#three_year_table_column_toggler input[type="checkbox"]').change(function(){
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            var bVis = threeYearTable.fnSettings().aoColumns[iCol].bVisible;
            threeYearTable.fnSetColumnVis(iCol, (bVis ? false : true));
        });
    }
	
	var oneYear = function() {
        var oneYearTable = $('#one_year_table').dataTable( {           
            "aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[4, 'asc']],
             "aLengthMenu": [
                [100, 75, 50, 25, -1],
                [100, 75, 50, 25, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 100,
        });

        jQuery('#one_year_table_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#one_year_table_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#one_year_table_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#one_year_table_column_toggler input[type="checkbox"]').change(function(){
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            var bVis = oneYearTable.fnSettings().aoColumns[iCol].bVisible;
            oneYearTable.fnSetColumnVis(iCol, (bVis ? false : true));
        });
    }

    return {

        //main function to initiate the module
        init: function () {
            
            if (!jQuery().dataTable) {
                return;
            }

            tenYear();
			//fiveYear();
			//threeYear();
			//oneYear();
        }

    };

}();
</script>