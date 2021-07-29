<?php
/*
Marketocracy Inc. | Beta Development
Fund Price History

Supporting files:
	JSON		- process/json/fund-price-history-json.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/portlets/fund-price-history.js.php
	HTML		- includes/portlets/fund-price-history.php
*/
?>


<script type="text/javascript">
<?php

foreach($phArray as $phFundID){

$phFundSym = get_funds($mLink, $phFundID, 'fundSymbol');

?>

$(function() {
	var sym = '';
	colors = Highcharts.getOptions().colors;
		
	sym = '<?php echo $phFundSym; ?>';
	window['seriesOptions' + sym] = [],
	window['yAxisOptions' + sym] = [],
	window['seriesCounter' + sym] = 0,
	window['names' + sym] = ['<?php echo $phFundID;?>', 'NASDAQ', 'SP500'],
	
	$('.container-<?php echo $phFundSym;?>').html('<img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" />');
	
	//alert(window['names' + sym]);
	
	$.each(window['names' + sym], function(i, name) {
		
		
		$.getJSON('http://beta.marketocracy.com/process/json/fund-price-history-json.php?fund='+ name + '&date=<?php echo get_funds($mLink, $phFundID, 'inceptDate', 'agg');?>',	function(data) {

			if(name == "NASDAQ" || name == "SP500" || name == "DJI"){
				name = name;
			}else{
				name = '<?php echo $phFundSym;?>';
			}
			window['seriesOptions' + sym][i] = {
				name: name,
				data: data
			};

			// As we're loading the data asynchronously, we don't know what order it will arrive. So
			// we keep a counter and create the chart when all the data is loaded.
			window['seriesCounter' + sym]++;


			if (window['seriesCounter' + sym] == window['names' + sym].length) {
				$('.container-<?php echo $phFundSym;?>').highcharts('StockChart', {
					chart: {
					},

					rangeSelector: {
						selected: 4
					},

					yAxis: {
						labels: {
							formatter: function() {
								return (this.value > 0 ? '+' : '') + this.value + '%';
							}
						},
						plotLines: [{
							value: 0,
							width: 2,
							color: 'silver'
						}]
					},

					plotOptions: {
						series: {
							compare: 'percent'
						}
					},

					tooltip: {
						pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.change}%)<br/>',
						valueDecimals: 2
					},

					series: window['seriesOptions' + sym]
				});
			}

		});


	});

});

<?php
}
?>
</script>