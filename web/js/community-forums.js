var TableAdvanced = function () {
	
	var initForumTable = function() {
        var oTable = $('#forum-list').dataTable( {           
            "oLanguage": {
				"sEmptyTable": "There are no forums yet."
			},
			"aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[3, 'asc']], // set default column order 
             "aLengthMenu": [
                [15, 20, 25, 50 -1],
                [15, 20, 25, 50, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 20,
        });

        jQuery('#forum-list_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#forum-list_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#forum-list_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#forum-list_column_toggler input[type="checkbox"]').change(function(){
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
            oTable.fnSetColumnVis(iCol, (bVis ? false : true));
        });
    }
	
	var initForumInactiveTable = function() {
        var oTable = $('#forum-inactive-list').dataTable( {           
            "oLanguage": {
				"sEmptyTable": "There are no forums yet."
			},
			"aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[3, 'asc']], // set default column order 
             "aLengthMenu": [
                [15, 20, 25, 50 -1],
                [15, 20, 25, 50, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 20,
        });

        jQuery('#forum-inactive-list_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#forum-inactive-list_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#forum-inactive-list_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#forum-inactive-list_column_toggler input[type="checkbox"]').change(function(){
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
            oTable.fnSetColumnVis(iCol, (bVis ? false : true));
        });
    }
	
     var initCatTable = function() {
        var oTable = $('.forum-cat').dataTable( {           
            "oLanguage": {
				"sEmptyTable": "There are no categories for this forum yet."
			},
			"aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[3, 'asc']], // set default column order 
             "aLengthMenu": [
                [15, 20, 25, 50 -1],
                [15, 20, 25, 50, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 20,
        });

        jQuery('.forum-cat_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('.forum-cat_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('.forum-cat_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('.forum-cat_column_toggler input[type="checkbox"]').change(function(){
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
            oTable.fnSetColumnVis(iCol, (bVis ? false : true));
        });
    }
	
	var initTopicsTable = function() {
        var oTable = $('#forum-topics').dataTable( {           
            "oLanguage": {
				"sEmptyTable": "There are no topics in this category yet."
			},
			"aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[4, 'desc']], // set default column order 
             "aLengthMenu": [
                [15, 20, 25, 50 -1],
                [15, 20, 25, 50, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 20,
        });

        jQuery('#forum-topics_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#forum-topics_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#forum-topics_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#forum-topics_column_toggler input[type="checkbox"]').change(function(){
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
            oTable.fnSetColumnVis(iCol, (bVis ? false : true));
        });
    }
	
	var initUnreadTable = function() {
        var oTable = $('#unread-topics').dataTable( {           
            "oLanguage": {
				"sEmptyTable": "You have no unread topics."
			},
			"aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[3, 'desc']], // set default column order 
             "aLengthMenu": [
                [15, 20, 25, 50 -1],
                [15, 20, 25, 50, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 20,
        });
		
		

        jQuery('#unread-topics_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#unread-topics_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#unread-topics_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#unread-topics_column_toggler input[type="checkbox"]').change(function(){
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
            oTable.fnSetColumnVis(iCol, (bVis ? false : true));
        });
		
		$('.checkall', oTable.fnGetNodes()).click(function () {
			 var checkall =$(this).parents('.box:eq(0)').find(':checkbox').attr('checked', this.checked);
        	$.uniform.update(checkall);
		});
    }
	
	var initUnreadPostsTable = function() {
        var oTable = $('#unread-posts').dataTable( {           
            "oLanguage": {
				"sEmptyTable": "You have no unread posts."
			},
			"aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[3, 'desc']], // set default column order 
             "aLengthMenu": [
                [15, 20, 25, 50 -1],
                [15, 20, 25, 50, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 20,
        });

        jQuery('#unread-posts_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#unread-posts_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#unread-posts_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#unread-posts_column_toggler input[type="checkbox"]').change(function(){
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
            oTable.fnSetColumnVis(iCol, (bVis ? false : true));
        });
    }
	
	var initMyTopicsTable = function() {
        var oTable = $('#my-topics').dataTable( {           
            "oLanguage": {
				"sEmptyTable": "You have no unread topics."
			},
			"aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[3, 'desc']], // set default column order 
             "aLengthMenu": [
                [15, 20, 25, 50 -1],
                [15, 20, 25, 50, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 20,
        });

        jQuery('#my-topics_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#my-topics_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#my-topics_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#my-topics_column_toggler input[type="checkbox"]').change(function(){
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
            oTable.fnSetColumnVis(iCol, (bVis ? false : true));
        });
    }
	
	var initMyPostsTable = function() {
        var oTable = $('#my-posts').dataTable( {           
            "oLanguage": {
				"sEmptyTable": "You have no unread posts."
			},
			"aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[3, 'desc']], // set default column order 
             "aLengthMenu": [
                [15, 20, 25, 50 -1],
                [15, 20, 25, 50, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 20,
        });
		
		

        jQuery('#my-posts_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#my-posts_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#my-posts_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#my-posts_column_toggler input[type="checkbox"]').change(function(){
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
			
			//initForumTable();
			initForumInactiveTable();
            //initCatTable();
			//initTopicsTable();
			//initUnreadTable();
			initUnreadPostsTable();
			initMyTopicsTable();
			initMyPostsTable();
        }

    };

}();