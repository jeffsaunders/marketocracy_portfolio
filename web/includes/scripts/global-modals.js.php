<?php
/*
Global Include file for portlets that will be used site wide
Supporting Files:
HTML: includes/global-modals.php
Scripts: includes/scripts/global-modals.js.php THIS FILE
Process: process/ajax/global-modals-processes.php
*/

$processFile = 'process/ajax/global-modals-processes.php';
?>
<script type="text/javascript">

function loadGlobalQuoteForm() {
    $('form.global-quote-form').on('submit', function () {

        var symbol = $('#global-new-quote').val();

        loadGlobalStockInfo(symbol);

        return false;
    });
}

$('#system-alert-link').on("click", function(e){
	//alert('hello');
	e.preventDefault();
});

function loadGlobalStockInfo(symbol){
	$('#global-stock-info-load').html('<img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" /> Loading Stock Information');
	var capSymbol = symbol.toUpperCase();
	$('#global-stock-info-symbol').html(capSymbol);
	
	$.ajax({
		data: {process: 'load-stock-info', symbol: symbol},
      	url: '<?php echo $processFile;?>',
      	success: function(data) {

            

			
        	$('#global-stock-info-load').html(data);
		
			loadGlobalPriceChart(symbol,'stockPriceChart');

            loadGlobalQuoteForm();
      }
    
    });
	
}

function loadLabelForm(){
	
	$('form.edit-label-form').on('submit', function() {
		$.ajax({
			type: "POST",
			data: $(".edit-label-form").serialize(),
			url: "<?php echo $processFile;?>?process=save-global-label",
			success: function(data)
			{	
			
				
				if(data == ""){
					
					$('#global-label').modal('toggle');
					location.reload();
					
				}else{
			
					$('#label-form-errors').html(data);
				
				}
		 
			}
		});
		
		return false;
	});
		
}

function getLabel(fundID,stockSymbol){
	$.ajax({
		data: {process: 'load-label-modal', fund: fundID, stockSymbol: stockSymbol},
      	url: '<?php echo $processFile;?>',
      	success: function(data) {
       		$('#load-global-label').html(data);
			
			loadLabelForm();
		}
    
    });
}

function updateLabel(fundID){
	
	var symbol = $('#stock_symbol').val();
	
	getLabel(fundID,symbol);
		
}

function loadGlobalTradeDetails(ticketKey){
	
	$('#load-global-trade-details').html('<img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" /> Loading Trades');
	
	$.ajax({
		data: {process: 'load-ticket-details', ticketKey: ticketKey},
      	url: '<?php echo $processFile;?>',
      	success: function(data) {
       		$('#load-global-trade-details').html(data);
		}
    
    });
	
}

function loadGlobalPriceChart(symbol, target){
	$('#'+target).html('<img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" /> Loading Chart Info');
	$(function () {
		
		$.getJSON('<?php echo $processFile;?>?process=load-price-chart&symbol='+symbol, function (data) {
	
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

function loadPosDetailsTrades(fundID, stockSymbol){
	
	$('#load-global-pos-details-trades').html('<img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" /> Loading Trades');
	
	$.ajax({
		data: {process: 'load-position-details-trades', fund:fundID, stockSymbol:stockSymbol},
      	url: '<?php echo $processFile;?>',
      	success: function(data) {
       		$('#load-global-pos-details-trades').html(data);
		}
    
    });
		
}



function loadPosDetails(fundID,stockSymbol){
	
	
	$('#load-global-pos-details').html('<img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" /> Position Details for: '+stockSymbol);
	
	$.ajax({
		data: {process: 'load-position-details-content', fund:fundID, symbol:stockSymbol},
      	url: '<?php echo $processFile;?>',
      	success: function(data) {
       		$('#load-global-pos-details').html(data);
		
			loadGlobalPriceChart(stockSymbol,'global-price-chart');
			loadPosDetailsTrades(fundID, stockSymbol);
			
			/*if((data.indexOf('reloadTrades') > -1) == true){
				
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
			}*/
		
		
      	}
    
    });
}

</script>