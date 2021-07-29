var TableAdvanced = function () {

     var initOpenTicketsTable = function() {
        var oTable = $('#open_tickets').dataTable( {           
            "oLanguage": {
				"sEmptyTable": "You do not have any tickets."
			},
			"aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[1, 'desc']], // set default column order 
             "aLengthMenu": [
                [10, 15, 20, -1],
                [10, 15, 20, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 10,
        });

        jQuery('#open_tickets_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#open_tickets_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#open_tickets_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#open_tickets_column_toggler input[type="checkbox"]').change(function(){
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
            oTable.fnSetColumnVis(iCol, (bVis ? false : true));
        });
    }
	
	var initOpenTicketsTableAdmin = function() {
        var oTable = $('#open_tickets_admin').dataTable( {           
            "oLanguage": {
				"sEmptyTable": "You do not have any tickets."
			},
			
            "aaSorting": [[1, 'desc']], // set default column order 
             "aLengthMenu": [
                [25, 50, 100, 200,  -1],
                [25, 50, 100, 200, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 25,
			//"order": [[3, "desc"]]
			"columns": [
				null,
				{ "width": "1%"},
				null,
				null,
				{ "width": "5%" },
				{ "width": "1%" },
				null,
				null
			]
        });

        jQuery('#open_tickets_admin_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#open_tickets_admin_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#open_tickets_admin_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#open_tickets_admin_column_toggler input[type="checkbox"]').change(function(){
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

            initOpenTicketsTable();
			initOpenTicketsTableAdmin();
        }

    };

}();