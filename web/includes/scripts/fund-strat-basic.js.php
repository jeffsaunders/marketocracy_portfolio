<?php
/*
Marketocracy Inc. | Beta Development
Fund Stratification Basic Scripts

Supporting files:
	AJAX		- process/ajax/fund-strat-basic-processes.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/fund-strat-basic.js.php 
	HTML		- includes/pages/fund-strat-basic.php
*/



?>
<script>

<?php
//if stratification is still processing check to see when it is finished. 
if($processStatus == '1'){?>
var checkRate = 5000;

$( document ).ready(checkStratProcess = function() {
	$.ajax({
		data: {fund:'<?php echo $fundID;?>'},
		url: 'process/ajax/fund-strat-basic-processes.php?process=check-strat-process',
		success: function(data) {
			
			if(data.indexOf('strat-success') >= 0){
				$('#strat-process-finished').modal('show');
				$('#load-process-note').html(data);
			}else{
				$('#load-process-note').html(data);
			}
			
			if(data != ''){
			
				
				
			}else{
				
				
					
			}
			
		}
	
	});
});
var refreshCheck = setInterval(checkStratProcess, checkRate);

function stopChecking(refreshPage){
	clearInterval(refreshCheck);
	
	if(refreshPage == '1'){
		location.reload();	
	}
	
	
	
}
<?php }?>

function stopChecking2(refreshPage){
	if(refreshPage == '2'){
		location.reload();
	}
}

function loadPriceChart(symbol, target){
	$(function () {
		
		$.getJSON('process/ajax/fund-strat-basic-processes.php?process=2&symbol='+symbol, function (data) {
	
			// split the data set into ohlc and volume
			var ohlc = [],
				volume = [],
				dataLength = data.length,
				// set the allowed units for data grouping
				groupingUnits = [[
					'week',                         // unit name
					[1]                             // allowed multiples
				], [
					'month',
					[1, 2, 3, 4, 6]
				]],
	
				i = 0;
	
			for (i; i < dataLength; i += 1) {
				ohlc.push([
					data[i][0], // the date
					data[i][1], // open
					data[i][2], // high
					data[i][3], // low
					data[i][4] // close
				]);
	
				volume.push([
					data[i][0], // the date
					data[i][5] // the volume
				]);
			}
	
	
			// create the chart
			$('#'+target).highcharts('StockChart', {
	
				rangeSelector: {
					inputEnabled: $('#container').width() > 480,
					selected: 1
				},
	
				title: {
					text: symbol+' Historical'
				},
	
				yAxis: [{
					labels: {
						align: 'right',
						x: -3
					},
					title: {
						text: 'OHLC'
					},
					height: '60%',
					lineWidth: 2
				}, {
					labels: {
						align: 'right',
						x: -3
					},
					title: {
						text: 'Volume'
					},
					top: '65%',
					height: '35%',
					offset: 0,
					lineWidth: 2
				}],
				credits: {
					enabled: false	
				},
	
				series: [{
					type: 'candlestick',
					name: symbol,
					data: ohlc,
					dataGrouping: {
						units: groupingUnits
					}
				}, {
					type: 'column',
					name: 'Volume',
					data: volume,
					yAxis: 1,
					dataGrouping: {
						units: groupingUnits
					}
				}]
			});
		});
	});	
}

function loadTickets(fundID,symbol){
	$.ajax({
		data: {fund:fundID,symbol:symbol},
        url: 'process/ajax/fund-strat-basic-processes.php?process=3-1',
        success: function(data) {
            
			if(data.indexOf('get_message') > -1){
				//alert("abc");
				var symbol = $('#getSymbol').val();
				var message = '<p>We noticed a discrepancy in your trades for: <strong>'+symbol+'</strong>. Therefore, we are refreshing your stratification for this symbol. Please click the refresh button to reload the page. Note, for funds with long track records, it may take longer to fully reload the stratification.</p>';
				
				$('#refresh-symbol').html(message);
				$('#strat-process-reload').modal('toggle');
					
			}
			$('#load-trade-info').html(data);
		
        }
    
    });
}

function stockInfoTrade(type) {



    $.ajax({
        type: "POST",
        data: $(".stock-info-trade-form").serialize(),
        url: "process/ajax/fund-strat-basic-processes.php?process=6&type="+type,
        success: function(data)
        {

            if(data != '') {
                $('.stock-info-trade-errors').html(data);
                var container = $('#stock-info-trade'),
                    scrollTo = $('.trade-scroll-to');

                container.scrollTop(
                    scrollTo.offset().top - container.offset().top + container.scrollTop()
                );
            }else{

                $("#stock-info-trade").modal("hide");
                $("#stock-info").modal("hide");

                //START NOTIFICATION
                var i = -1,
                    toastCount = 0,
                    $toastlast
                var shortCutFunction = 'success';
                var msg = 'Click Here to view open orders.';
                var title = 'Trade Submitted Successfully';
                var toastIndex = toastCount++;

                toastr.options = {
                    closeButton: true,
                    debug: false,
                    positionClass: 'toast-top-right',
                    timeOut: '3500'
                };

                toastr.options.onclick = function () {
                    //alert('When notifiaction is clicked');
                    window.location.href="/?page=02-00-00-002";
                };

                var $toast = toastr[shortCutFunction](msg, title);

                $toastlast = $toast;

                $('#clearlasttoast').click(function () {
                    toastr.clear($toastlast);
                });
                //END NOTIFICATION
            }


        }
    });


}

function loadStockTrade() {
	$.ajax({
		//data: {fund:fundID,symbol:symbol},
      	url: 'process/ajax/fund-strat-basic-processes.php?process=5',
      	success: function(data) {
        	$('#trade-detail-load').html(data);
            //loadStockInfoTradeForm();
      	}
    
    });
}

var ticketsTable = function () {
		
	var initTicketsTable = function() {
        var oTable = $('#stock-info-tickets-table').dataTable( {           
            "oLanguage": {
				"sEmptyTable": "There are no trades."
			},
			"aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[0, 'desc']], // set default column order 
             "aLengthMenu": [
                [15, 20, 25, 50 -1],
                [15, 20, 25, 50, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 20,
        });

        jQuery('#stock-info-tickets-table_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#stock-info-tickets-table_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#stock-info-tickets-table_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#stock-info-tickets-table_column_toggler input[type="checkbox"]').change(function(){
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
			
			initTicketsTable();
			
        }

    };

}();

function loadStockTickets(){
	
	$('#ticket-detail-load2').html('<img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" /> Loading Tickets...');
	
	$.ajax({
		//data: {fund:fundID,symbol:symbol},
      	url: 'process/ajax/fund-strat-basic-processes.php?process=4-1',
      	success: function(data) {
        	$('#ticket-detail-load2').html(data);
			ticketsTable.init();
      	}
    
    });
}

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
		
		$('#fund-cancel').modal('hide');
		loadStockTickets();
		/*setTimeout(function() {
			window.location = '/?page=02-00-00-002';
		}, 2000);	  */
	  }
	
	});	
}


/*function loadPD(fundID,getSymbol){
	
	$.ajax({
		data: {fund:fundID, symbol:getSymbol},
      url: 'process/ajax/fund-strat-basic-processes.php?process=1',
      success: function(data) {
        $('#position-detail-load').html(data);
		
		loadPriceChart(getSymbol,'priceChart');
		
		if((data.indexOf('reloadTrades') > -1) == true){
			//alert('reload scripts');
			$('#load-trade-info').html('<img src="<?php echo $siteRoot;?>/images/ajax-loading.gif" />');
			loadTickets(fundID,getSymbol);
		}
		
		
      }
    
    });
	
}*/



function loadPD(fundID,getSymbol){
	
	$.ajax({
		data: {fund:fundID, symbol:getSymbol},
      url: 'process/ajax/fund-strat-basic-processes.php?process=1',
      success: function(data) {
        $('#position-detail-load').html(data);
		
		
		
		if((data.indexOf('reloadTrades') > -1) == true){
			
			var stepOne = function() {
			
				var r = $.Deferred();
				$('#load-trade-info').html('<img src="<?php echo $siteRoot;?>/images/ajax-loading.gif" />');
				loadPriceChart(getSymbol,'priceChart');
				
				setTimeout(function (){
					r.resolve();
				}, 1000);
				
				return r;
			
			};
			
			var stepTwo = function() {
				//alert('reload scripts');
				
				loadTickets(fundID,getSymbol);
			};
			
			stepOne().done(stepTwo);
			
			
		}else{
			loadPriceChart(getSymbol,'priceChart');	
		}
		
		
      }
    
    });
	
}

function loadQuoteForm() {
    $('form.new-quote-form').on('submit', function () {

        var symbol = $('#new-quote').val();

        loadStockInfo(symbol);

        return false;
    });
}

function loadStockInfo(symbol){
	
	$.ajax({
		data: {symbol: symbol},
      	url: 'process/ajax/fund-strat-basic-processes.php?process=4',
      	success: function(data) {

            var capSymbol = symbol.toUpperCase();

			$('#stock-info-symbol').html(capSymbol);
        	$('#stock-info-load').html(data);
		
			loadPriceChart(symbol,'stockPriceChart');

            loadQuoteForm();
      }
    
    });
	
}

function loadTicketDetail(uid, fundID){
	$.ajax({
		data: {uid: uid, fund: fundID},
      	url: 'process/ajax/fund-strat-basic-processes.php?process=3',
      	success: function(data) {
        	$('#ticket-detail-load').html(data);
		
      	}
    
    });
}



</script>