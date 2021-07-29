<?php
/*
TRADE RECENT ORDERS - JAVASCRIPT FILE

SUPPORTING FILES:
_______________________________________________________________

AJAX 			- process/ajax/trade-recent-orders-processes.php
PHP JAVASCRIPT	- THIS FILE includes/scripts/trade-recent-orders.js.php
HTML 			- includes/pages/trade-recent-orders.php
_______________________________________________________________

*/
?>
<script>
var recentOrdersTable = function () {

     var recentOrders = function() {
        var oTable = $('#recent_orders_table').dataTable( {           
            "aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[2, 'desc']],
             "aLengthMenu": [
                [10, 15, 20, 25, 30, 35, -1],
                [10, 15, 20, 25, 30, 35, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 25,
        });

        jQuery('#recent_orders_table_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#recent_orders_table_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#recent_orders_table_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#recent_orders_table_column_toggler input[type="checkbox"]').change(function(){
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

            recentOrders();
        }

    };

}();
</script>