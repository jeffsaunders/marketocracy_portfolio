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
			fiveYear();
			threeYear();
			oneYear();
        }

    };

}();
</script>