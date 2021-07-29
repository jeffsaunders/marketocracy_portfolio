<?php
/*
Marketocracy Inc. | Beta Development
Fund Month To Month Scripts

Supporting files:
	AJAX		- process/ajax/portlets/fund-month-ajax.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/portlets/fund-month.js.php
	HTML		- includes/portlets/fund-month.php
*/
?>
<!--Fund Turnover-->

<?php
$jsonFund = json_encode($aChartFund);

$jsonFund = str_replace(array('{','}','"',':'),array('[',']','',','), $jsonFund);

$jsonSP = json_encode($aChartSP);

$jsonSP = str_replace(array('{','}','"',':'),array('[',']','',','), $jsonSP);

?>
<script>
$(function () {
    $.getJSON('<?php echo $_SESSION['site_root'];?>process/json/fund-month-to-month-json.php?fund=<?php echo $fundID;?>', function (data) {

        // create the chart
        $('#monthToMonth').highcharts('StockChart', {
            chart: {
                alignTicks: false
            },

            rangeSelector: {
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
					count: 2,
					text: '2y'
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
                
            },

            title: {
                text: '<?php echo $fundSymbol;?> Month By Month Returns'
            },
			credits: {
				enabled: false	
			},

            series: [{
                type: 'column',
                name: '<?php echo $fundSymbol;?> Returns',
                data: data,
                dataGrouping: {
                    units: [[
                        'week', // unit name
                        [1] // allowed multiples
                    ], [
                        'month',
                        [1, 2, 3, 4, 6]
                    ]]
                }
            }/*,{
                type: 'column',
                name: 'S&P Returns',
                data: <?php echo $jsonSP;?>,
                dataGrouping: {
                    units: [[
                        'week', // unit name
                        [1] // allowed multiples
                    ], [
                        'month',
                        [1, 2, 3, 4, 6]
                    ]]
                }
            }*/]
        });
    });
});

</script>