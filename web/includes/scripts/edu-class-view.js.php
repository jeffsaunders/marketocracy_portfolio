<?php
/*
Marketocracy Inc. | Beta Development
My Classes Overview Scripts

Supporting files:
	AJAX		- process/ajax/edu-classes-overview-porcesses.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/edu-classes-overview.js.php
	HTML		- includes/pages/edu-classes-overview.php
*/
?>
<!--EDU CLASSES-->


<script>
function getCSV(){
	
	window.location.href = "process/ajax/edu-class-view-processes.php?process=1";
	
}

//init table
var StudentTable = function () {
		
	var initStudentTable = function() {
        var oTable = $('#student-list').dataTable( {           
            "oLanguage": {
				"sEmptyTable": "There are no students for this class yet.",
				"sLengthMenu": "Display _MENU_ Students"
			},
			"aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[13, 'desc']], // set default column order 
             "aLengthMenu": [
                [15, 20, 25, 50 -1],
                [15, 20, 25, 50, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 25
        });
		
		oTable.fnSetColumnVis(10, false);
		oTable.fnSetColumnVis(11, false);
		oTable.fnSetColumnVis(12, false);
		
        jQuery('#student-list_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#student-list_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#student-list_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#student-list_column_toggler input[type="checkbox"]').change(function(){
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
            oTable.fnSetColumnVis(iCol, (bVis ? false : true));
			//oTable.fnSetColumnVis(10, false);
        });
    }

    return {

        //main function to initiate the module
        init: function () {
            
            if (!jQuery().dataTable) {
                return;
            }
			
			initStudentTable();
			
        }

    };

}();

$(function() {
	var sym = '';
	colors = Highcharts.getOptions().colors;
	
	//alert(colors);
		
	//sym = '<?php echo $phFundSym; ?>';
	sym = 'class';
	window['seriesOptions' + sym] = [],
	window['yAxisOptions' + sym] = [],
	window['seriesCounter' + sym] = 0,
	window['names' + sym] = [<?php echo $studentFundIDs;?>,'SP500'],
	
	$('.load-class-data').html('<img src="<?php echo $_SESSION['site_root'];?>images/loading.gif" />');
	
	//alert(window['names' + sym]);
	
	$.each(window['names' + sym], function(i, name) {
		
		/*$.ajax({
			data: {process: '2', fund: name},
			url: 'process/ajax/edu-class-view-processes.php',
			success: function(data) {
				name2 = data;
			}
		
		});*/
		
		$.getJSON('<?php echo $_SESSION['site_root'];?>process/json/fund-price-history-multi-json.php?fund='+ name + '&date=<?php echo $graphStartDate;?>',	function(data) {
			
			if(name == "NASDAQ" || name == "SP500" || name == "DJI"){
				name = name;	
			}else{
				name = name;
			}
			
			window['seriesOptions' + sym][i] = {
				name: name,
				data: data
			};
			
			
			//alert(data);
			// As we're loading the data asynchronously, we don't know what order it will arrive. So
			// we keep a counter and create the chart when all the data is loaded.
			window['seriesCounter' + sym]++;
			
			
			if (window['seriesCounter' + sym] == window['names' + sym].length) {
				$('.load-class-data').highcharts('StockChart', {
					chart: {
						//margin: [50, 50, 100, 80]
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
					
					/*tooltip: {
						//pointFormat: '<span style="color:{series.color}">{point.fund}</span>: <b>{point.y}</b> ({point.change}%) {point.compliant}<br/>',
						valueDecimals: 2,
                        formatter: '<span style="color:{series.color}">{this.point.options.fund}</span>: <b>{this.y}</b> ({point.change}%) {this.point.options.compliant}<br/>'
					},*/
                    /*tooltip: {
                        formatter: function () {
                            return '<span style="color:' + this.series.color + '">'+ this.point.fund + '</span>: <b>' + this.y + '</b> ('+ this.change + ') '+this.point.compliant+'<br />';
                        }
                    },*/
                    tooltip: {
                        useHTML: true,
                        formatter: function () {
                            var s = '<b>' + Highcharts.dateFormat('%b %e, %Y',this.x) + '</b>';
                                s += "<table>";
                                s += '<tr><th style="text-align:center;">Fund</th><th>NAV/Change</th><th>Compliance</th>';

                            $.each(this.points, function () {

                               

                                s += '<tr><td style="text-align:right;"><span style="color:' + this.series.color + '">' + this.point.options.fund + '</span>:</td><td> <b>' + Highcharts.numberFormat(this.y, 2) + '</b> ('+Highcharts.numberFormat(this.point.change, 2)+'%)</td><td> ' + this.point.options.compliant + '</td></tr>';
                            });

                            s += "</table>";

                            return s;
                        },
                        //valueDecimals: 2,
                        shared: true
                    },

					credits: {
						enabled: false	
					},
					
					
					
					
					series: window['seriesOptions' + sym]
					
				});
			}
			
		});
		
		
	});
	
});

</script>