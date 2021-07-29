/*
Marketocracy Inc. | Beta Development
Fund Ledger Table Scripts

Supporting files:
	AJAX		- process/ajax/fund-ledger-processes.php
	JAVASCRIPT	- includes/scripts/portlets/fund-alpha-beta.js.php | included in DB
	Javascript  - THIS DOCUMENT js/fund-ledger.js <- table scripts | included in DB
	HTML		- includes/pages/fund-ledger.php
*/
var TableAdvanced = function () {
	
	var initLedgerTable = function() {
        var oTable = $('#ledger-table').dataTable( {           
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
            "iDisplayLength": 31,
        });

        jQuery('#ledger-table_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#ledger-table_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#ledger-table_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#ledger-table_column_toggler input[type="checkbox"]').change(function(){
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