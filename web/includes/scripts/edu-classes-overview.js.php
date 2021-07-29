<?php
/*
Marketocracy Inc. | Beta Development
My Classes Overview Scripts

Supporting files:
	AJAX		- process/ajax/edu-classes-overview-porcesses.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/edu-classes-overview.js.php
	HTML		- includes/pages/edu-classes-overview.php
*/
?>
<!--EDU CLASSES-->


<script>
//init table
var ClassTable = function () {
		
	var initClassTable = function() {
        var oTable = $('#class-list').dataTable( {           
            "oLanguage": {
				"sEmptyTable": "There are no students for this class yet.",
				"sLengthMenu": "Display _MENU_ Classes",
			},
			"aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[4, 'asc']], // set default column order 
             "aLengthMenu": [
                [15, 20, 25, 50 -1],
                [15, 20, 25, 50, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 20,
        });

        jQuery('#class-list_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#class-list_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#class-list_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('.class-list_column_toggler input[type="checkbox"]').change(function(){
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
			
			initClassTable();
			
        }

    };

}();

</script>