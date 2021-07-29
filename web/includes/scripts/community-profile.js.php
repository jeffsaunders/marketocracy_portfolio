<?php
/*
Marketocracy Inc. | Beta Development
Community Public Profile Scripts

Supporting files:
	AJAX		- process/ajax/community-profile-processes.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/community-profile.js.php
	HTML		- includes/pages/community-public-profile.php
*/

?>
<script type="text/javascript">

function clearForm(formID){
	
	
	$('#form-errors').html('');
	$('#'+formID).trigger("reset");
}

function selectFund(fundID){
	
	
	
	$('#fund-'+fundID).prop('checked', true);
	$('#fund-boxes').css('display','none');
	
}

//Save Advanced Settings
$('form#track-manager').on('submit', function() {

	//process php
	$.ajax({
        type: "POST", 
        data: $("#track-manager").serialize(),
        url: "process/ajax/track-manager-processes.php?process=track-manager-submit",
        
		success: function(data)
        {	
		
			if (data.indexOf("note-danger") !=-1) {
				$('#form-errors').html(data);
			}else{
				$('#track-modal').modal('toggle');
				
				$('#subscription-confirm').html(data);
				
				$('#sub-conf').modal('toggle');
			}
			//alert(data);
			
			//$('#connect').modal('toggle');
			
			//Notification
			/*toastr.options = {
				closeButton: true, 
				debug: false,
				positionClass: 'toast-top-right', 
				timeOut: '3000'
			};
			
			//Display notifiaction to browser  
			toastr.success('Connection Request Sent!');*/
			
			//$('#form-errors').html(data);
     
        }
    });
	
	return false;
});

//Save Advanced Settings
$('form#send-request').on('submit', function() {

	//process php
	$.ajax({
        type: "POST", 
        data: $("#send-request").serialize(),
        url: "process/ajax/community-profile-processes.php?process=1",
        
		success: function(data)
        {
			//alert(data);
			
			$('#connect').modal('toggle');
			
			//Notification
			toastr.options = {
				closeButton: true, 
				debug: false,
				positionClass: 'toast-top-right', 
				timeOut: '3000'
			};
			
			//Display notifiaction to browser  
			toastr.success('Connection Request Sent!');
			
			$('#connect-btn').html('<button type="button" class="btn btn-warning">Request Pending</button>');
     
        }
    });
	
	return false;
});

//Save Advanced Settings
$('form#remove-connection').on('submit', function() {

	//process php
	$.ajax({
        type: "POST", 
        data: $("#remove-connection").serialize(),
        url: "process/ajax/community-profile-processes.php?process=2",
        
		success: function(data)
        {
			//alert(data);
			
			$('#remove-connect').modal('toggle');
			
			//Notification
			toastr.options = {
				closeButton: true, 
				debug: false,
				positionClass: 'toast-top-right', 
				timeOut: '3000'
			};
			
			//Display notifiaction to browser  
			toastr.success('You have successfully removed this connection.');
			
			//Update connection button
			$('#connect-btn').html('<a class="btn btn-info" href="#connect" data-toggle="modal">Connect</a>');
     
        }
    });
	
	return false;
});



var TableAdvanced = function () {
	
	var initArticleTable = function() {
        var oTable = $('#articles_table').dataTable( {           
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
            "iDisplayLength": 31
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
	
	var initEmailTable = function() {
        var oTable = $('#campaigns_table').dataTable( {           
            "oLanguage": {
				"sEmptyTable": "There are no ledger entries yet."
			},
			"aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[7, 'desc']], // set default column order 
             "aLengthMenu": [
                [30, 60, 90 -1],
                [30, 60, 90, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 31
        });
		
		/*new $.fn.dataTable.FixedColumns( oTable, {
			leftColumns: 1,
			rightColumns: 0	
		});*/
		
		/*new $.fn.dataTable.FixedHeader( oTable, {
			"offsetTop": 45,
			left: true
		} );*/

        jQuery('#campaigns_table_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#campaigns_table_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#campaigns_table_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#campaigns_table_column_toggler input[type="checkbox"]').change(function(){
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
            oTable.fnSetColumnVis(iCol, (bVis ? false : true));
        });
    }
	
	var initTrackingTable = function() {
        var oTable = $('#tracking_table').dataTable( {           
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
            "iDisplayLength": 31
        });
		
		/*new $.fn.dataTable.FixedColumns( oTable, {
			leftColumns: 1,
			rightColumns: 0	
		});*/
		
		/*new $.fn.dataTable.FixedHeader( oTable, {
			"offsetTop": 45,
			left: true
		} );*/

        jQuery('#tracking_table_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#tracking_table_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#tracking_table_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#tracking_table_column_toggler input[type="checkbox"]').change(function(){
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
			
			initArticleTable();
			initEmailTable();
			initTrackingTable();
            
        }

    };

}();

</script>