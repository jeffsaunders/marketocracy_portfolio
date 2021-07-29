<?php
/*
Marketocracy Inc. | Beta Development
Fund Postion Styles Scripts

Supporting files:
	AJAX		- process/ajax/portlets/fund-pos-style-ajax.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/portlets/fund-pos-style.js.php
	HTML		- includes/portlets/fund-pos-style.php
*/
?>
<!--Fund Position Styles-->
<script type="text/javascript">
<?php

foreach($posStyleArray as $posStyleFundID){

$posStyleFundSym = get_funds($mLink, $posStyleFundID, 'fundSymbol');
$posStylePieValueArray = 'aPieStyleValues'.$posStyleFundID.'';


?>

$(function () {
    var chart;
    /*<?php //echo $posStylePieValueArray; print_r($$posStylePieValueArray);?>*/
    $(document).ready(function () {
    	
    	// Build the chart
        $('#pos-style-<?php echo $posStyleFundID;?>').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: 'Positions by Style'
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
                    //['Firefox',   45.0],
                    <?php
					$cntArray = count($$posStylePieValueArray);
					$arrayCnt = 0;
					
					foreach($$posStylePieValueArray as $style){
						
						$arrayCnt = $arrayCnt + 1;
						
						if($arrayCnt < $cntArray){ 
							$comma = ","; 
						}else{
							$comma = '';
						}
						
						if($style['style'] == 'Unclassified Market Cap : Unclassified Style'){
							$posStyle = 'Unclassified Style';	
						}else{
							$posStyle = $style['style'];	
						}
						
						echo "{name: '".$posStyle."', y: ".$style['allocation'].", today: '".$style['todayReturn']."', incepReturn: '".$style['totalReturn']."'}".$comma."";
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