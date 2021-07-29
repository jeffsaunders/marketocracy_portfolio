<?php
/*
Marketocracy Inc. | Beta Development
Fund Stratification Sectors Scripts

Supporting files:
	AJAX		- process/ajax/fund-strat-sector-processes.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/fund-strat-sector.js.php 
	HTML		- includes/pages/fund-strat-sector.php
*/
?>
<script>
$(window).load(function() { 
	$(function () {
		var chart;
	   
		$(document).ready(function () { 
			
			// Build the chart
			$('#pos-sector-chart').highcharts({
				chart: {
					plotBackgroundColor: null,
					plotBorderWidth: null,
					plotShadow: false
				},
				title: {
					text: 'Positions by Sectors and Industries'
				},
				tooltip: {
					//pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b> <br>Today Return: <b>{point.today}</b> <br>Inception Return: <b>{point.incepReturn}</b>'
					pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b> <br>Today Return: <b>{point.today}</b>'
				},
				
				plotOptions: {
					pie: {
						allowPointSelect: true,
						cursor: 'pointer',
						dataLabels: {
							enabled: false
						},
						showInLegend: true
					},
					series: {
						dataLabels: {
							enabled: true,
							format: '{point.name}: {point.y:.1f}%'
						}
					}
				},
				legend: {
					enabled: false,
					layout: 'vertical',
					align: 'right',
					width: 300,
					verticalAlign: 'middle',
					useHTML: true,
					labelFormatter: function() {
						//return '<div style="text-align: left; width:130px;float:left;">' + this.name + '</div><div style="width:40px; float:left;text-align:right;">' + this.y + '%</div>';
						return '' + this.name + ': <b>' + this.y + '%</b>';
					}
				},
				credits: {
					enabled: false	
				},
				series: [{
					type: 'pie',
					name: 'Allocation',
					data: [
						<?php
						$cntArray = count($aSectors);
						$arrayCnt = 0;
						
						foreach($aSectors as $sectorID => $sector){
							
							//SET VARS
							$sectorName 		= $sector['sectorName'];
							$sectorAllocation	= $sector['sectorAllocation'];
							$sectorTodayReturn	= $sector['sectorTodayReturn'];
							
							//Format Vars
							$sectorTodayReturn	= ''.number_format($sectorTodayReturn, 2, '.', '').'%';
							
							$arrayCnt = $arrayCnt + 1;
							
							if($arrayCnt < $cntArray){ 
								$comma = ","; 
							}else{
								$comma = '';
							}
							
							if($sector['sectorName'] == 'Unclassified Market Cap : Unclassified Sector'){
								$posSector = 'Unclassified Sector';	
							}else{
								$posSector = $sector['sectorName'];	
							}
							
							//echo "{name: '".$posSector."', y: ".number_format($sector['sectorAllocation'],2,'.','').", today: '".$sectorTodayReturn."', incepReturn: '".$sector['sectorTotalReturn']."'}".$comma."";
							echo "{name: '".$posSector."', y: ".$sector['sectorAllocation'].", today: '".$sectorTodayReturn."'}".$comma."";
						}
						?>
						
					]
				}]
			});
		});
		
	});
});
</script>