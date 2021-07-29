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

function showCancellOrder(uid){
	
	//alert("here");
	
	$('.cancel-fund').html('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button><h4 class="modal-title">Loading Data</h4></div><div class="modal-body cancel-fund"><img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" /> Loading Data...</div><!--modal-body--><div class="modal-footer"><button type="button" class="btn btn-danger" data-dismiss="modal">Cancel the unfilled portion of my order</button><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>');
	
	$.ajax({
	  url: 'process/ajax/trade-open-orders-processes.php?process=1&uid=' + uid,
	  success: function(data) {
		//alert(data);
		//alert(data);
		$('.cancel-fund').html(data);
		
			  
	  }
	
	});	
}

function cancelOrder(uid){
	
	//alert("here");
	
	$('.cancel-order').html('<img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" /> Cancelling Order...');
	
	$.ajax({
	  url: 'process/ajax/trade-open-orders-processes.php?process=2&uid=' + uid,
	  success: function(data) {
		//alert(data);
		//alert(data);
		//$('.cancel-order').html(data);
		
		setTimeout(function() {
			window.location = '/?page=02-00-00-002';
		}, 2000);	  
	  }
	
	});	
}

function deleteOrder(uid){
	
	//alert("here");
	
	$('.order-processing').html('<img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" /> Cancelling Order...');
	
	$.ajax({
		data:{process:'deleteOrder', uid:uid},
	  	url: 'process/ajax/trade-open-orders-processes.php',
	  	success: function(data) {
			//alert(data);
			//alert(data);
			//$('.cancel-order').html(data);
			
			setTimeout(function() {
				window.location = '/?page=02-00-00-002';
			}, 2000);	  
	  	}
	
	});	
}

function resubmitOrder(uid){
	
	//alert("here");
	
	$('.order-processing').html('<img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" /> Resubmitting Order...');
	
	$.ajax({
		data:{process:'resubmitOrder', uid:uid},
	  	url: 'process/ajax/trade-open-orders-processes.php',
	  	success: function(data) {
			//alert(data);
			//alert(data);
			//$('.cancel-order').html(data);
			
			setTimeout(function() {
				window.location = '/?page=02-00-00-002';
			}, 2000);	  
	  	}
	
	});	
}

var openOrdersTable = function () {

     var openOrders = function() {
        var oTable = $('#open_orders_table').dataTable( {           
            "aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[9, 'desc']],
             "aLengthMenu": [
                [10, 15, 20, 25, 30, 35, -1],
                [10, 15, 20, 25, 30, 35, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 10,
        });

        jQuery('#open_orders_table_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#open_orders_table_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#open_orders_table_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#open_orders_table_column_toggler input[type="checkbox"]').change(function(){
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

            openOrders();
        }

    };

}();
</script>