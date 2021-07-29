<?php
/*
Marketocracy Inc. | Beta Development
Fund Ledger Scripts

Supporting files:
	AJAX		- process/ajax/fund-ledger-processes.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/fund-ledger.js.php | included in DB
	Javascript  - js/fund-ledger.js <- table scripts | included in DB
	HTML		- includes/pages/fund-ledger.php
*/
?>
<script>
//Fund Ledger Scripts

function updateRow(fundID, unixDate, uid){
	
	$.ajax({
		data: {process: 'update-row',fund: fundID, date: unixDate},
      	url: 'process/ajax/fund-ledger-processes.php',
      	success: function(data) {
		  	//alert(data);
        	$('#'+uid).html(data);
		
      }
    
    });
		
}

function loadDetails(fundID, unixDate, uid){
	
	$('#load-ledger-details2').html('<img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" />');
	
	$.ajax({
		data: {process: 'load-details',fund: fundID, date: unixDate, uid: uid},
      	url: 'process/ajax/fund-ledger-processes.php',
      	success: function(data) {
		  	//alert(data);
        	$('#load-ledger-details2').html(data);
			
			if (data.indexOf("reloadRow") !=-1) {
				updateRow(fundID, unixDate, uid);
			}
      }
    
    });
		
}

function loadLedgerDetails(fundID, unixDate){
	//alert(fundID + '|' + unixDate);
	
	//Display loading bars
	$('.load-ledger-positions').html('<tr><td colspan="8"><img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" /></td></tr>');
	$('.load-ledger-trades').html('<tr><td colspan="8"><img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" /></td></tr>');
	
	//Load Ledger Info
	$.ajax({
      url: 'process/ajax/fund-ledger-processes.php?process=3&fund='+ fundID + '&date=' + unixDate,
      success: function(data) {
		  //alert(data);
        $('.load-ledger-info').html(data);
		
      }
    
    });
	
	//Load Ledger Positions
	$.ajax({
      url: 'process/ajax/fund-ledger-processes.php?process=1&fund='+ fundID + '&date=' + unixDate,
      success: function(data) {
        $('.load-ledger-positions').html(data);
		
      }
    
    });
	
	//Load Ledger Trades
	$.ajax({
      url: 'process/ajax/fund-ledger-processes.php?process=2&fund='+ fundID + '&date=' + unixDate,
      success: function(data) {
		  //alert(data);
        $('.load-ledger-trades').html(data);
		
      }
    
    });
}

function reloadDetails(fundID, unixDate){
	//Load Ledger Positions
	$.ajax({
      url: 'process/ajax/fund-ledger-processes.php?process=4&fund='+ fundID + '&date=' + unixDate,
      success: function(data) {
        $('.load-ledger-positions').html(data);
		
      }
    
    });
}


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
            "aoColumns": [
				{"iDataSort": 14},
				{"bSortable": true},
				{"bSortable": true},
				{"bSortable": true},
				{"bSortable": true},
				{"bSortable": true},
				{"bSortable": true},
				{"bSortable": true},
				{"bSortable": true},
				{"bSortable": true},
				{"bSortable": true},
				{"bSortable": true},
				{"bSortable": true},
				{"bSortable": true},
				{"bSortable": false},
			],
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



</script>