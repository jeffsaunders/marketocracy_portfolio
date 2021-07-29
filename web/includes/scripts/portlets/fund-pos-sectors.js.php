<?php
/*
Marketocracy Inc. | Beta Development
Fund Position Sectors Scripts

Supporting files:
	AJAX		- process/ajax/portlets/fund-pos-sectors-ajax.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/portlets/fund-pos-sectors.js.php
	HTML		- includes/portlets/fund-pos-sectors.php
*/
?>
<!--Fund Position Sectors-->
<script type="text/javascript">
<?php

foreach($posSecArray as $posSecFundID){

$posSecFundSym = get_funds($mLink, $posSecFundID, 'fundSymbol');
$posSecPieValueArray = 'aPieSecValues'.$posSecFundID.'';

?>

$(function () {
    var chart;
    /*<?php print_r($$posSecPieValueArray);?>*/
    $(document).ready(function () {
    	
    	// Build the chart
        $('#pos-sector-<?php echo $posSecFundID;?>').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: 'Positions by Sectors and Industries'
            },
            tooltip: {
        	    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b> <br>Today Return: <b>{point.today}</b> <br>Inception Return: <b>{point.incepReturn}</b>'
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
					$cntArray = count($$posSecPieValueArray);
					$arrayCnt = 0;
					
					foreach($$posSecPieValueArray as $sector){
						$arrayCnt = $arrayCnt + 1;
						if($arrayCnt < $cntArray){ 
							$comma = ","; 
						}else{
							$comma = '';
						}
						
						echo "{name: '".$sector['sector']."', y: ".$sector['allocation'].", today: '".$sector['todayReturn']."', incepReturn: '".$sector['totalReturn']."'}".$comma."";
					}
					?>
                    
                    
                ]
            }]
        });
    });
    
});

<?php 
}
?>
</script>