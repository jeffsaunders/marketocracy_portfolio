<?php
/*
Marketocracy Inc. | Beta Development
Fund Trades

Supporting files:
	AJAX		- process/ajax/fund-trades-processes.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/fund-trades.js.php
	HTML		- includes/pages/fund-trades.php
*/

?>
<script>
//Get Trades
function loadPosTrades(fundID, stockSymbol){
	//alert(fundID + '|' + unixDate);
	
	//Display loading bars
	$('.load-pos-trades').html('<tr><td colspan="8"><img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" /></td></tr>');
		
	//Load Ledger Positions
	$.ajax({
      url: 'process/ajax/fund-trades-processes.php?process=1&fund='+ fundID + '&symbol=' + stockSymbol,
      success: function(data) {
        $('.load-pos-trades').html(data);
		
      }
    
    });
	
}

var TradeTables = function () {
	
	var initTrades1Table = function() {
        var oTable = $('#current-trades-table').dataTable( {           
            "oLanguage": {
				"sEmptyTable": "There are no trade entries yet."
			},
			"aoColumnDefs": [
                { "aTargets": [ 1 ] }
            ],
			
            "aaSorting": [[0, 'desc']], // set default column order 
             "aLengthMenu": [
                [30, 60, 90 -1],
                [30, 60, 90, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 31,
        });

        jQuery('#current-trades-table_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#current-trades-table_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#current-trades-table_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#current-trades-table_column_toggler input[type="checkbox"]').change(function(){
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
            oTable.fnSetColumnVis(iCol, (bVis ? false : true));
        });
    }
	
	var initTrades2Table = function() {
        var oTable = $('#past-trades-table').dataTable( {           
            "oLanguage": {
				"sEmptyTable": "There are no trade entries yet."
			},
			"aoColumnDefs": [
                { "aTargets": [ 1 ] }
            ],
            "aaSorting": [[0, 'desc']], // set default column order 
             "aLengthMenu": [
                [30, 60, 90 -1],
                [30, 60, 90, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 31,
        });

        jQuery('#past-trades-table_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#past-trades-table_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#past-trades-table_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#past-trades-table_column_toggler input[type="checkbox"]').change(function(){
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
            oTable.fnSetColumnVis(iCol, (bVis ? false : true));
        });
    }
	
	var initTradesHistoryTable = function() {
        var oTable = $('#trades-history-table').dataTable( {           
            "oLanguage": {
				"sEmptyTable": "There are no trade entries yet."
			},
			"aoColumnDefs": [
                { "aTargets": [ 1 ] },
				{ "width": "55px", "targets": 0},
				{ "width": "125px", "targets": 1},
				{ "width": "120px", "targets": 2},
				{ "width": "3%", "targets": 3 }
            ],
            "aaSorting": [[1, 'desc']], // set default column order 
             "aLengthMenu": [
                [30, 60, 90 -1],
                [30, 60, 90, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 31,
        });

        jQuery('#trades-history-table_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#trades-history-table_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#trades-history-table_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#trades-history-table_column_toggler input[type="checkbox"]').change(function(){
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
			
			initTrades1Table();
			initTrades2Table();
			initTradesHistoryTable();
            
        }

    };

}();

function loadTicketDetail(key, fundID){
	$.ajax({
		data: {key: key, fund: fundID},
      	url: 'process/ajax/fund-trades-processes.php?process=show-trade-info',
      	success: function(data) {
        	$('#ticket-detail-load').html(data);
		
      	}
    
    });
}
</script>