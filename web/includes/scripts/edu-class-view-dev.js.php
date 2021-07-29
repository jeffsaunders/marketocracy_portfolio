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


$(document).ready(function () {
    var iTotal = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
    var rowsToUse = 0;
    var oTable1 = $('#example1').dataTable({
        fnPreDrawCallback: function (oSettings) {
            iTotal = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            rowsToUse = 0;
            for (var i = 0; i < oSettings.aoData.length; i++) {
                if (oSettings.aoData[i]._aData.use == 1) {
                    iTotal[0] += oSettings.aoData[i]._aData.c1;
                    iTotal[1] += oSettings.aoData[i]._aData.c2;
                    iTotal[2] += oSettings.aoData[i]._aData.c3;
					iTotal[3] += oSettings.aoData[i]._aData.c4;
					iTotal[4] += oSettings.aoData[i]._aData.c5;
					iTotal[5] += oSettings.aoData[i]._aData.c6;
					iTotal[6] += oSettings.aoData[i]._aData.c7;
					iTotal[7] += oSettings.aoData[i]._aData.c8;
					iTotal[8] += parseFloat(oSettings.aoData[i]._aData.c9.replace('%',''));
					iTotal[9] += parseFloat(oSettings.aoData[i]._aData.c10.replace('%',''));
                    rowsToUse++;
                }
            }
            //set percentage value
            for (i = 0; i < oSettings.aoData.length; i++) {
                oSettings.aoData[i]._aData.perc = (oSettings.aoData[i]._aData.c1 / iTotal[9] * 100).toFixed(2) + '%';
            }
        },
        fnFooterCallback: function (nRow, aaData, iStart, iEnd, aiDisplay) {
            //$('#sum .c1').html(iTotal[9]);
            $('#avg .c9').html((rowsToUse > 0 ? (iTotal[8] / rowsToUse).toFixed(2) : 0) + '%');
			$('#avg .c10').html((rowsToUse > 0 ? (iTotal[9] / rowsToUse).toFixed(2) : 0) + '%');
        },
        bPaginate: false,
        bLengthChange: false,
        bFilter: false,
        bSort: true,
        bInfo: false,
        bAutoWidth: false,
        aaSorting: [
            [10, "desc"]
        ],
		oLanguage: {
			"sEmptyTable": "There are no students for this class yet.",
			"sLengthMenu": "Display _MENU_ Students",
		},
		aoColumnDefs: [
			{ "aTargets": [ 0 ] }
		],
		aLengthMenu: [
			[15, 20, 25, 50 -1],
			[15, 20, 25, 50, "All"] // change per page values here
		],
		// set the initial value
		iDisplayLength: 20,

        aaData: <?php print_r($json);?>,
        aoColumns: [{
            mData: "name"
        }, {
            mData: "c1"
        }, {
            mData: "c2"
        }, {
            mData: "c3"
        }, {
            mData: "c4"
        }, {
            mData: "c5"
        }, {
            mData: "c6"
        }, {
            mData: "c7"
        }, {
            mData: "c8"
        }, {
            mData: "c9"
        }, {
            mData: "c10"
        },/* {
            mData: null,
            bVisible: false,
            mRender: function (data, type, full) {
                return (full.c1 / iTotal[0]);
            }
        },*/ {
            mData: 'use',
            mRender: function (data, type, full) {
                return '<input type="checkbox" class="updateFooter" name="d' + data + '" value="' + data + '" defaultChecked="' + (data == 1 ? 'checked' : '') + '"' + (data == 1 ? 'checked="checked"' : '') + ' />';
            }

        }/*, {
            mData: null,
            sClass: "center",
            mRender: function (data, type, full) {
                return '<img title="Remove"  class="deleteMe" src="http://openfire-websockets.googlecode.com/svn-history/r2/trunk/plugin/web/images/delete-16x16.gif" style="cursor: pointer">';
            }
        }*/]
    });
	
	jQuery('#example1_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
	jQuery('#example1_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
	jQuery('#example1_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

	$('#example1_column_toggler input[type="checkbox"]').change(function(){
		/* Get the DataTables object again - this is not a recreation, just a get of the object */
		var iCol = parseInt($(this).attr("data-column"));
		var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
		oTable.fnSetColumnVis(iCol, (bVis ? false : true));
	});
    

    

    $(document).on('click', "input.updateFooter", function () {
        var rowIndex = oTable1.fnGetPosition($(this).closest('tr')[0]);
        var ok = this.checked ? 1 : 0;
        //oTable1.fnUpdate(ok, rowIndex, 5, true, true);
        oTable1.fnSettings().aoData[rowIndex]._aData.use = ok;
        oTable1.fnDraw();
    });
});


function getCSV(){
	
	
	window.location.href = "process/ajax/edu-class-view-processes.php?process=1";
	
}



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
					
					tooltip: {
						pointFormat: '<span style="color:{series.color}">{point.fund}</span>: <b>{point.y}</b> ({point.change}%) {point.compliant}<br/>',
						valueDecimals: 2
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