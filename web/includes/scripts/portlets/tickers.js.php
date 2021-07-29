<?php
/*
Marketocracy Inc. | Beta Development
Fund Indices(Tickers)

Supporting files:
	AJAX		- process/ajax/portlets/fund-tickers-ajax.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/portlets/tickers.js.php
	HTML		- includes/portlets/tickers.php
	
*/
?>
<!--START MEMBER FUND FEEDS-->

<script>
function fundTickers(status, message){
	
	$('#details-funds').html('<img src="<?php echo $_SESSION['site_root']; ?>/images/loading.gif">');
	
	$.ajax({
		url: '/process/ajax/portlets/fund-tickers-ajax.php',
		success: function(data) {
			$('#details-funds').html(data);
		}
	});
}

function checkTickerProcessing(transIDs){
	$.ajax({
		data: {process: 'checkTransIDs', transIDs: transIDs},
		url: '/process/ajax/portlets/fund-tickers-refresh.php',
		success: function(data) {
			
			//alert(data);
			
			fundTickers('refresh', '');
		}
	});
}

$(document).ready(fundTickersLoad = function() {
	var message = '';
	fundTickers('load',message);
});
var refreshInterval = setInterval(fundTickersLoad, <?php echo $_SESSION['funds_refresh'];?>);

function refreshTickers(){
	$.ajax({
		data: {process: 'runLivePrice'},
		url: '/process/ajax/portlets/fund-tickers-refresh.php',
		success: function(data) {
			
			//alert(data);
			checkTickerProcessing(data);
			//fundTickers2();
		}
	});
}

</script>

<!--END MEMBER FUND FEEDS-->
