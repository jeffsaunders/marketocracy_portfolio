<?php
/*
SEARCH - Javascript

SUPPORTING FILES:
_______________________________________________________________

AJAX 			- process/ajax/search-processes.php
PHP JAVASCRIPT	- THIS FILE includes/scripts/search.js.php
HTML 			- includes/pages/search.php
_______________________________________________________________

*/
?>

<script>

//var value = editor.getValue();

/*$('#topic_post').wysihtml5({
	
	"stylesheets": ["plugins/bootstrap-wysihtml5/wysiwyg-color.css"],
	
	"events": {
		"load": function() { 
			console.log("Loaded!");
		},
		"blur": function() { 
			console.log("Blured");
			
			var title = $('#topic_title').val();
			var post = editor.getValue();
			
			if(post == ''){
				$('#post-help').html('<span style="color:#b94a48;">Please provide a post.</span>');
			}else{
				$('#post-help').html('');
			}
			
			if(title != '' && post != ''){
				$('#submit-topic').css('display', '');	
			}else{
				$('#submit-topic').css('display', 'none');	
			}	
			
		}
	}
});*/

//+-------------------------------------------------------------------------------------+
//|							START: WYSIHTML5 Validation									|
//+-------------------------------------------------------------------------------------+
$('#topic_post').wysihtml5({
	events: {
		load: function() {
			var some_wysi = $('#topic_post').data('wysihtml5').editor;
			$(some_wysi.composer.element).bind('keyup', function(){ 
				var title = $('#topic_title').val();
				var post = editor.getValue();
				
				if(post == ''){
					$('#post-help').html('<span style="color:#b94a48;">Please provide a post.</span>');
				}else{
					$('#post-help').html('');
				}
				
				if(title != '' && post != ''){
					$('#submit-topic').css('display', '');	
				}else{
					$('#submit-topic').css('display', 'none');	
				}	
			});
		}
	}
});


$('#topic_title').focusout(function(){
	
	var title = $('#topic_title').val();
	var post = editor.getValue();
	
	if(title == ''){
		$('#topic_title').css({
			"border-color": "#b94a48",
			"border-width": "1px",
			"border-style": "solid"	
		});
		
		$('#topic-help').html('<span style="color:#b94a48;">Please provide a title.</span>');	
	}else{
		$('#topic_title').css({
			"border-color": "#e5e5e5",
			"border-width": "1px",
			"border-style": "solid"	
		});
		$('#topic-help').html('');
		
	}
	
	if(title != '' && post != ''){
		$('#submit-topic').css('display', '');	
	}else{
		$('#submit-topic').css('display', 'none');	
	}
	
});
//+-------------------------------------------------------------------------------------+
//|							END: WYSIHTML5 jquery code									|
//+-------------------------------------------------------------------------------------+


$('form.create-topic').on('submit', function() {

	$.ajax({
        type: "POST",
        data: $(".create-topic").serialize(),
        url: "process/ajax/community-forum-processes.php?process=15",
        success: function(data)
        {
			//alert(data);
			var shortCutFunction = 'success';
			var msg = 'Please click here to view your post.';
			var title = 'Discussion Started!';
			
			$('#create-topic').modal('toggle');
			
			//Notification
			toastr.options = {
				closeButton: true,
				debug: false,
				positionClass: 'toast-top-right',
				timeOut: '4000'
			};
			
			toastr.options.onclick = function () {
				//alert('When notifiaction is clicked');
				window.location.href=data;
			};
			
			toastr[shortCutFunction](msg, title); 
			
			$('#topic_title').val('');
			$('#topic_post').data("wysihtml5").editor.clear();
     
        }
    });
	
	return false;
});

function showResults(element){
	$('.'+element).css('display', '');
	$('#btn-'+element).css('display', 'none');
}

$(function () {
	var symbol = '<?php echo $stockSymbol;?>';
	//alert(symbol);
    //$.getJSON('http://www.highcharts.com/samples/data/jsonp.php?filename=aapl-ohlcv.json&callback=?', function (data) {
	$.getJSON('process/ajax/search-processes.php?process=2&symbol='+symbol, function (data) {

        // split the data set into ohlc and volume
        var ohlc = [],
            volume = [],
            dataLength = data.length,
            // set the allowed units for data grouping
            groupingUnits = [[
                'week',                         // unit name
                [1]                             // allowed multiples
            ], [
                'month',
                [1, 2, 3, 4, 6]
            ]],

            i = 0;

        for (i; i < dataLength; i += 1) {
            ohlc.push([
                data[i][0], // the date
                data[i][1], // open
                data[i][2], // high
                data[i][3], // low
                data[i][4] // close
            ]);

            volume.push([
                data[i][0], // the date
                data[i][5] // the volume
            ]);
        }


        // create the chart
        $('#container').highcharts('StockChart', {
			
			plotOptions: {
				candlestick: {
					color: 'red',
					upColor: '#5CB85C'
				}
			},
			
            rangeSelector: {
                inputEnabled: $('#container').width() > 480,
                selected: 1
            },

            title: {
                text: symbol+' Historical'
            },

            yAxis: [{
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: 'OHLC'
                },
                height: '60%',
                lineWidth: 2
            }, {
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: 'Volume'
                },
                top: '65%',
                height: '35%',
                offset: 0,
                lineWidth: 2
            }],
			credits: {
				enabled: false	
			},

            series: [{
                type: 'candlestick',
                name: symbol,
                data: ohlc,
                dataGrouping: {
                    units: groupingUnits
                }
            }, {
                type: 'column',
                name: 'Volume',
                data: volume,
                yAxis: 1,
                dataGrouping: {
                    units: groupingUnits
                }
            }]
        });
    });
});

/*if ($('.wysihtml5').size() > 0) {
	$('.wysihtml5').wysihtml5({
		"stylesheets": ["plugins/bootstrap-wysihtml5/wysiwyg-color.css"]
	});
}*/

</script>