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
$unixIncept = get_funds($mLink, $phFundID, 'trueInceptDate');



$tenYearsAgo 	= strtotime('-10 year');
$fiveYearsAgo	= strtotime('-5 year');
$threeYearsAgo	= strtotime('-3 year');
$oneYearAgo		= strtotime('-1 year');

echo '//findme'.$unixIncept.'|'.$tenYearsAgo.'|'.$fiveYearsAgo.'|'.$threeYearsAgo.'|'.$oneYearAgo;

if($unixIncept < $tenYearsAgo){
	//track record longer than 10 years	
	
	$rangeBtns 	= "
		selected: 4,
		buttons: [{
			type: 'month',
			count: 5,
			text: '6m'
		},{
			type: 'year',
			count: 1,
			text: '1y'
		},{
			type: 'year',
			count: 3,
			text: '3y'
		},{
			type: 'year',
			count: 5,
			text: '5y'
		},{
			type: 'year',
			count: 10,
			text: '10y'
		},{
			type: 'ytd',
			text: 'YTD'
		},{
			type: 'all',
			text: 'All'
		}]
	";
}elseif($unixIncept < $fiveYearsAgo){
	//track record longer than 5 years	
	
	$rangeBtns 	= "
		selected: 4,
		buttons: [{
			type: 'month',
			count: 2,
			text: '3m'
		},{
			type: 'month',
			count: 5,
			text: '6m'
		},{
			type: 'year',
			count: 1,
			text: '1y'
		},{
			type: 'year',
			count: 3,
			text: '3y'
		},{
			type: 'year',
			count: 5,
			text: '5y'
		},{
			type: 'ytd',
			text: 'YTD'
		},{
			type: 'all',
			text: 'All'
		}]
	";
}elseif($unixIncept < $threeYearsAgo){
	//track record longer than 3 years	
	
	$rangeBtns 	= "
		selected: 4,
		buttons: [{
			type: 'month',
			count: 0,
			text: '1m'
		},{
			type: 'month',
			count: 2,
			text: '3m'
		},{
			type: 'month',
			count: 5,
			text: '6m'
		},{
			type: 'year',
			count: 1,
			text: '1y'
		},{
			type: 'year',
			count: 3,
			text: '3y'
		},{
			type: 'ytd',
			text: 'YTD'
		},{
			type: 'all',
			text: 'All'
		}]
	";
}elseif($unixIncept < $oneYearAgo){
	//track record longer than 1 year	
	
	$rangeBtns 	= "
		selected: 3,
		buttons: [{
			type: 'month',
			count: 0,
			text: '1m'
		},{
			type: 'month',
			count: 2,
			text: '3m'
		},{
			type: 'month',
			count: 5,
			text: '6m'
		},{
			type: 'year',
			count: 1,
			text: '1y'
		},{
			type: 'ytd',
			text: 'YTD'
		},{
			type: 'all',
			text: 'All'
		}]
	";
}else{
	//track record under 1 year	
	
	$rangeBtns 	= "
		buttons: [{
			type: 'month',
			count: 0,
			text: '1m'
		},{
			type: 'month',
			count: 2,
			text: '3m'
		},{
			type: 'month',
			count: 5,
			text: '6m'
		},{
			type: 'ytd',
			text: 'YTD'
		},{
			type: 'all',
			text: 'All'
		}]
	";
}

?>

$(function() {
	var sym = '';
	colors = Highcharts.getOptions().colors;
	
	//alert(colors);
		
	//sym = '<?php echo $phFundSym; ?>';
	sym = '<?php echo $phFundID;?>';
	window['seriesOptions' + sym] = [],
	window['yAxisOptions' + sym] = [],
	window['seriesCounter' + sym] = 0,
	window['names' + sym] = ['<?php echo $phFundID;?>', 'NASDAQ', 'SP500'],
	
	$('.container-<?php echo $phFundID;//$phFundSym;?>').html('<img src="<?php echo $_SESSION['site_root'];?>images/loading.gif" />');
	
	//alert(window['names' + sym]);
	
	$.each(window['names' + sym], function(i, name) {
		
		
		$.getJSON('<?php echo $_SESSION['site_root'];?>process/json/fund-price-history-json.php?fund='+ name + '&date=<?php echo /*get_funds($mLink,$phFundID, 'fundIncept')*/get_funds($mLink, $phFundID, 'trueInceptDateStr');?>',	function(data) {
			
			if(name == "NASDAQ" || name == "SP500" || name == "DJI"){
				name = name;	
			}else{
				name = '<?php echo $phFundSym;?>';	
			}
			
			if(name == "NASDAQ"){
				setColor = '#7CB5EC';	
			}
			
			if(name == "SP500"){
				setColor = '#90ED7D';	
			}
			
			if(name != "NASDAQ" && name != "SP500"){
				setColor = '#434348';	
			}
			
			window['seriesOptions' + sym][i] = {
				name: name,
				data: data,
				color: setColor
			};
			
			
			//alert(data);
			// As we're loading the data asynchronously, we don't know what order it will arrive. So
			// we keep a counter and create the chart when all the data is loaded.
			window['seriesCounter' + sym]++;
			
			
			if (window['seriesCounter' + sym] == window['names' + sym].length) {
				$('.container-<?php echo $phFundID;//$phFundSym;?>').highcharts('StockChart', {
					chart: {
						//margin: [50, 50, 100, 80]
					},
					legend: {
						enabled: true
					},
					rangeSelector: {
						//selected: 4
						<?php echo $rangeBtns;?>
					},
		
					yAxis: {
						//opposite: false,
						labels: {
							//align: "right",
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
							compare: 'percent',
							turboThreshold: 0
						}
					},
					
					tooltip: {
						pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.change}%) {point.compliant}<br/>',
						valueDecimals: 2
					},
					credits: {
						enabled: false	
					},
					
					
					/*tooltip: {
						pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.change}%) {point.compliant}<br/>',
						valueDecimals: 2,
						formatter: function() {
							return this.name + ': ' + this.y + '('+this.compliant+')';
						}
					},*/
					
					series: window['seriesOptions' + sym]
					/*series: [{
						name: name,
						data: data
					}, {
						type: 'flags',
						name: 'Flags on axis',
						data: [{
							x: Date.UTC(2014, 2, 1),
							title: 'On axis'
						}, {
							x: Date.UTC(2014, 3, 1),
							title: 'On axis'
						}],
						shape: 'squarepin'
					}]*/
				});
			}
			
		});
		
		
	});
	
});

<?php 
}
?>
</script>