<?php
/*
Marketocracy Inc. | Beta Development
Fund Stratification Styles Scripts

Supporting files:
	AJAX		- process/ajax/fund-strat-style-processes.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/fund-strat-style.js.php 
	HTML		- includes/pages/fund-ledger.php
*/
?>
<script>
$(function () {
    var chart;
   
    $(document).ready(function () { 
    	
    	// Build the chart
        $('#pos-style-chart').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: 'Positions by Style'
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
            series: [{
                type: 'pie',
                name: 'Allocation',
                data: [
                    //['Firefox',   45.0],
                    <?php
					$cntArray = count($aStyles);
					$arrayCnt = 0;
					
					foreach($aStyles as $styleID => $style){
						
						//SET VARS
						$styleName 			= $style['styleName'];
						$styleAllocation	= $style['styleAllocation'];
						$styleTodayReturn	= $style['styleTodayReturn'];
						
						//Format Vars
						$styleTodayReturn	= ''.number_format($styleTodayReturn, 2, '.', '').'%';
						
						$arrayCnt = $arrayCnt + 1;
						
						if($arrayCnt < $cntArray){ 
							$comma = ","; 
						}else{
							$comma = '';
						}
						
						if($style['styleName'] == 'Unclassified Market Cap : Unclassified Style'){
							$posStyle = 'Unclassified Style';	
						}else{
							$posStyle = $style['styleName'];	
						}
						
						echo "{name: '".$posStyle."', y: ".$style['styleAllocation'].", today: '".$styleTodayReturn."', incepReturn: '".$style['styleTotalReturn']."'}".$comma."";
					}
					
					?>
                    
                    
                ]
            }]
        });
    });
    
});
</script>