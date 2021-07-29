

<script type="text/javascript">
		$(function() {
			var seriesOptions = [],
				yAxisOptions = [],
				seriesCounter = 0,
				//names = ['MSFT', 'AAPL', 'GOOG'],
				names = ['<?php echo $phFundID; ?>', 'NASDAQ', 'SP500'],
				colors = Highcharts.getOptions().colors;
				
			$.each(names, function(i, name) {
		
				//$.getJSON('http://www.highcharts.com/samples/data/jsonp.php?filename='+ name.toLowerCase() +'-c.json&callback=?',	function(data) {
				//$.getJSON('http://alpha.marketocracy.com/process/json/fund-price-history-json.php?fund='+ name + '&date=<?php echo date('Y-m-d', get_member($mLink, $_SESSION['member_id'], 'joinDate'));?>',	function(data) {
				$.getJSON('http://beta.marketocracy.com/process/json/graph.php?fund='+ name,	function(data) {
					//alert(name);
					
					if(name == "NASDAQ" || name == "SP500" || name == "DJI"){
						name = name;	
					}else{
						name = '<?php echo get_funds($mLink, $phFundID, 'fundSymbol');?>';	
					}
					
					seriesOptions[i] = {
						name: name,
						data: data
					};
		
					// As we're loading the data asynchronously, we don't know what order it will arrive. So
					// we keep a counter and create the chart when all the data is loaded.
					seriesCounter++;
		
					if (seriesCounter == names.length) {
						createChart();
					}
				});
				
				
			});
		
			
		
			// create the chart when all data is loaded
			function createChart() {
		
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
					
					series: seriesOptions
				});
                                
                                
			}

                        
		
		});
		</script>