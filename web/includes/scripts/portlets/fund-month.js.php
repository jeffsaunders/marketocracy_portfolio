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
<script>
$(function () {
    $.getJSON('http://www.highcharts.com/samples/data/jsonp.php?filename=aapl-v.json&callback=?', function (data) {

        // create the chart
        $('#monthToMonth').highcharts('StockChart', {
            chart: {
                alignTicks: false
            },

            rangeSelector: {
                selected: 1
            },

            title: {
                text: 'AAPL Stock Volume'
            },
			credits: {
				enabled: false	
			},

            series: [{
                type: 'column',
                name: 'AAPL Stock Volume',
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
            }]
        });
    });
});

</script>