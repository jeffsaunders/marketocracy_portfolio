<?php
/*
TRADE OPEN ORDERS - JAVASCRIPT FILE

SUPPORTING FILES:
_______________________________________________________________

AJAX 			- 	process/ajax/rankings-processes.php
PHP JAVASCRIPT	- 	THIS FILE includes/scripts/rankings.js.php
HTML 			- 	includes/pages/rank-pro.php
					includes/pages/rank-community.php
_______________________________________________________________

*/
?>
<script>

$('#period-select').change(function (){
	
	var rankDate = $(this).val();
	window.location.href = '/?page=06-00-00-001&period=10&rankDate='+rankDate;
		
});

function toggleStage(showHide, rankType){
	
	if(showHide == 'show'){
		var sessionVal = '1';	
	}else if(showHide == 'hide'){
		var sessionVal = '0';	
	}
	
	$.ajax({ 
        type: "POST",
        data:{process: 'set-session', sessionVar: 'view-stage', sessionVal: sessionVal},
        url: "process/ajax/rankings-processes.php",
        success: function(data)
        {
			location.reload();
        }
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
			fiveYear();
			threeYear();
			oneYear();
        }

    };

}();
</script>