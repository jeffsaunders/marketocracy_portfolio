<?php
/*
TRADE WIZARD - PHP JAVASCRIPT FILE

SUPPORTING FILES:
_______________________________________________________________

AJAX 			- process/ajax/trade-wizard-new-processes.php
PHP JAVASCRIPT	- THIS FILE includes/scripts/trade-wizard.js.php
HTML 			- includes/pages/trade-wizard.php
_______________________________________________________________

*/
$twProcess = 'trade-wizard-processes.php';
$twLocation = '02-00-00-001';
?>
<script>

<?php if($_REQUEST['symbols'] != ''){?>
$(window).load(function() { 
	$(".button-next").click();
});
<?php } ?>

function checkForChecked(){
	var unchecked = [];
		
		$('.col-select').each(function(i, obj) {
			var prop = $(this).prop('checked');
				val	= $(this).attr('title');
				//alert(val);
			if(prop == false){
				unchecked.push(val);
			}
		});
		
		return unchecked;
}

function showHideCol(element, targetID){
	
	var prop = $('#'+element).prop('checked');
	
	if(prop == true){
		//alert('checked');
		$('#'+element).val('hide');
		$('#'+targetID).css('display', '');
		$('.'+targetID).css('display', '');
	}else{
		//alert('unchecked');
			$('#'+element).val('show');
		$('#'+targetID).css('display', 'none');
		$('.'+targetID).css('display', 'none');	
	}
	
	
		
}

function toggleHide(element, targetID){
	
	var prop = $('#'+element).attr('title');
	
	//alert();
	
	if(prop == 'hide'){
		
		$('#'+targetID).css('display', 'none');
		$('#'+element).attr('title', 'show');
		$('#'+element).html('Show Cash Values');
		
		
	}else if(prop == 'show'){
		
		$('#'+targetID).css('display', '');
		$('#'+element).attr('title', 'hide');
		$('#'+element).html('Hide Cash Values');
		
	}
	
}

function showHideNext(){
	
	var rowCount = $('#validator').val();
		//alert(rowCount);
		aCnt = rowCount.split("|");
		
		//alert(aCnt); 
		cntFields = 0;
		
		for (index = 0; index < aCnt.length; index++){
			
			//alert(aCnt[index]);
			
			var sharesVal = $('#share-qty-'+aCnt[index]).val();
			
			//alert(sharesVal);
			
			if(sharesVal == ''){
				sharesVal = '0';	
			}
			
			if(sharesVal != '0'){
				cntFields++;	
			}
		}
		
		
		if(cntFields == 0){
			//no trades, hide continue button
			$('.button-next').css('display', 'none');
			
		}else{
			//trades present, show continue button
			$('.button-next').css('display', '');
			$('#button-exec').css('display', '');	
		}
		
		//alert(cntFields);
}

function formatNumber(id){
	
	var number = $('#'+id).val();
	
	$.ajax({
		url: 'process/ajax/<?php echo $twProcess;?>?process=5&value='+number,
	  	success: function(data) {
			$('#'+id).val(data);
	  	}
	
	});
	
}

//+------------------START GET STOCK SYMBOLS-----------------------
// this gets stock symbols for a selected fund when the "Trade Type" is set to: "Sell" 
function getSymbols(){
	
	var tradeType = $('#sell-type-front').val();
	var fund = $('#funds-front').val();
	
	if(fund != ''){
		if(tradeType == "sell" && fund != "all"){
			//alert(fund);
			$.ajax({
			  url: 'process/ajax/<?php echo $twProcess;?>?process=1&fund='+fund,
			  success: function(data) {
				//alert(data);
				
				symbols = data;
				
				//alert('wait');
				
				window.location.replace("?page=<?php echo $twLocation;?>&trade="+tradeType+"&fund="+fund+"&symbols="+symbols);
				
				//$('#symbols').val(data);
			  }
			
			});
		}
	}
}



<?php
/*$query = "SELECT symbol FROM `stock_valid_symbols` WHERE top_500='1'";

try{
	$rsGetTop = $mLink->prepare($query);
	$aValues = array(
		//':member_id' 	=> $_SESSION['member_id']
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsGetTop->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$aTopStocks = array();
while($top = $rsGetTop->fetch(PDO::FETCH_ASSOC)){
	$aTopStocks[] = "'".$top['symbol']."'";
}

$topStocks = implode(',', $aTopStocks);*/
?>

/*$("#symbols").select2({
	
	tags: [<?php echo $topStocks;?>]
});
*/
function checkFund(){
	fundType = $('#funds-front').val();
	
	//alert(fundType);
	
	if(fundType == 'all'){
		$('#show-funds').css('display', '');
	}else{
		$('#show-funds').css('display', 'none');
	}
	
	if(fundType != ''){
		$('.button-next').css('display', '');	
	}
}

//+------------------END GET STOCK SYMBOLS-----------------------



/*alert(wiz);
*/
/*$(window).load(function() { 
	var screenWidth = window.innerWidth;
	//alert(screenWidth);
	if(screenWidth <= 1024){
		$('#name-check').click();
		$('#cshares-check').click();
		$('#cpos-check').click();
		$('#value-check').click();
		$('#buy-check').click();
		//$('#name-check').click();					
	}
	
	var wiz = '<?php echo $wiz;?>';
	
	if(wiz == '1'){
		$('#type-check').click();
		$('#fund-check').click();
	}
});*/
//+------------------Add New Symbol------------------------------

function addNewSymbol(cnt, cntString, fundID){
	
	var symbol 		= $('#add-symbol').val();
	var aUnchecked	= checkForChecked();
		cash		= $('#'+fundID+'-calc-cash').val();
	
	$.ajax({
		type: "POST",
		data: {cnt: cnt, cntString: cntString, fund: fundID, symbol: symbol, cash: cash, unchecked: aUnchecked},
		url: "process/ajax/<?php echo $twProcess;?>?process=2-2",
		success: function(data){
			
			cnt = Number(cnt) + 1;
			
			$('#update-new-count').html(cnt);
			$('#add-symbol').val('');
			$('.alert').css('display', 'none');
			
			$(data).insertBefore("#add-new-row");
			
		}
	});
		
}


//+------------------START Process Symbols-----------------------
function tradeSymbols(index, wiz){
	
	//Check to see if the continue button was pressed on the first page of the wizard
	if(index == "0"){
		
		$('#change_button').css('display', 'none');
		
		//Set loader GIF to show that symbols are being processed
		$('.load-trades').html('<tr><td colspan="8"><img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" /> Loading Trades...</td></tr>');
		
		$.ajax({
			type: "POST",
			data: $(".submit-trade").serialize(),
			url: "process/ajax/<?php echo $twProcess;?>?process=2&wiz=<?php echo $wiz;?>",
			success: function(data){
				
				
				
				//Display trades to screen				
				$('.load-trades').html(data);
				
				var checkSymbols = $('#symbols').val();
				
				if(checkSymbols == ''){
					alert('no symbols');	
				}else{
					var screenWidth = window.innerWidth;
					//alert(screenWidth);
					
					if(wiz == '1'){
						$('#type-check').click();
						$('#fund-check').click();
					
						if(screenWidth <= 1400){
							$('#name-check').click();
						}
						
						if(screenWidth <= 1300){
							
							$('#value-check').click();
							//$('#name-check').click();					
						}
						
						if(screenWidth <= 1024){
							
							$('#cpos-check').click();
							
							$('#buy-check').click();
						}
						if(screenWidth <= 900){
							$('#cshares-check').click();	
						}
					}else{
						if(screenWidth <= 1440){
							$('#name-check').click();
						}
						
						if(screenWidth <= 1366){
							$('#value-check').click();
							$('#buy-check').click();
						}
						
						if(screenWidth <= 1024){
							
							$('#cshares-check').click();
							$('#cpos-check').click();
							
						}
						
						if(screenWidth <= 800){
							$('#npos-check').click();
						}
					}
					
					
					
					
					//var wiz = '<?php echo $wiz;?>';
					
					
				}
				
				
				
				
				showHideNext();
				$('#change_button').css('display', '');
				
				
			}
		});
		
		$('.trade-errors').html('');
		
		
		
	}//end if index == 0
	
}//END FUNCTION: tradeSymbols

//+------------------START Process Change Fund-----------------------
function changeFund(cnt, fundID){
	var fund 		= $('#fund-symbol-' + cnt).val();
		symbol 		= $('#symbol-' + cnt).val();
		price 		= $('#current-price-' + cnt).val();
		name 		= $('#company-name-' + cnt).val();
		name 		= encodeURIComponent(name);
		change 		= $('#change-' + cnt).val();
		change 		= encodeURIComponent(change);
		
		cash		= $('#'+fundID+'-calc-cash').val();
		prevCash 	= $('#'+fundID+'-cash-previous-'+cnt).val();
		
		$('#update-row-'+cnt).fadeTo( "fast", 0.33 );
		
		if($('#trade-type-sell-' + cnt).is(':checked')) {
			tradeType = 'sell';
		}
		if($('#trade-type-buy-' + cnt).is(':checked')) {
			tradeType = 'buy';	
		}
		
		//get all unchecked columns
		var aUnchecked = checkForChecked();
		
	$.ajax({
		url: 'process/ajax/<?php echo $twProcess;?>?process=3&symbol=' + symbol + '&fund=' + fund + '&price=' + price + '&cnt=' + cnt + '&name=' + name + '&change=' + change + '&tradeType=' + tradeType + '&prevCash='+prevCash + '&cash='+cash,
		data: {unchecked : aUnchecked},
	  	success: function(data) {

		$('#update-row-' + cnt).html(data);
		
//		//start adjust cash
		cashAdjust = $('#fix-cash-'+cnt).val();
				
		$('#'+fundID+'-calc-cash').val(cashAdjust);
		
		//number format
		$.ajax({
			url: 'process/ajax/<?php echo $twProcess;?>?process=5&value=' + cashAdjust,
			success: function(data) {
			
				$('#'+fundID+'-cash').val(data);
				
				$('#update-row-'+cnt).fadeTo( "fast", 1 );
				
				//Get variables for notification
				messageType 	= $('#message-type-'+cnt).val();
				messagePos		= $('#message-pos-'+cnt).val();
				messageTimeOut	= $('#message-time-'+cnt).val();
				
				customMessage	= $('#custom-message-'+cnt).val();
				customTitle		= $('#custom-title-'+cnt).val();
				
				//Set defaults
				if(messagePos == ''){
					messagePos = 'toast-top-right';
				}
				if(messageTimeOut == ''){
					messageTimeOut == '10000';	
				}
				
				//Print message if there is one
				if(messageType != ''){
					var shortCutFunction = 'success';
					var msg = 'Please click here to view your post.';
					var title = 'Discussion Started!';
					
					//Notification
					toastr.options = {
						closeButton: true,
						debug: false,
						positionClass: messagePos,
						timeOut: messageTimeOut
					};
					
					/*toastr.options.onclick = function () {
						//alert('When notifiaction is clicked');
						window.location.href=data;
					};*/
					
					toastr[messageType](customMessage, customTitle);
				}
				
				showHideNext();
			}
		
		});
//		//end adjust cash		
		/*toastr.options = {
			closeButton: true,
			debug: false,
			positionClass: 'toast-top-right',
			timeOut: '2000'
		};
		  
		toastr.info('Fund changed for Row: ' + cnt);*/
		
		//showHideNext();
	  }
	
	});	
}

//+------------------START Calculate Trade-----------------------
function calculateTotal(cnt, fundID, calcType){
	//alert(cnt);
	var tradeType 		= $("input:radio[name='trade-type-"+cnt+"[]']:checked").val();
		stockSymbol 	= $('#symbol-'+cnt).val();
		price 			= $('#current-price-' + cnt).val();
		
		if($('#share-qty-'+cnt).val() == ''){
			shares 		= 0;
		}else{
			shares 		= parseInt($('#share-qty-' + cnt).val());
		}
		
		cashPrevious 	= $('#'+fundID+'-cash-previous-'+cnt).val();
		cashAdjust 		= $('#'+fundID+'-cash-new-'+cnt).val();
		cash 			= $('#'+fundID+'-calc-cash').val();
		totalValue 		= $('#total-value-'+cnt).val();
		currentShares 	= $('#current-shares-'+cnt).val();
		
		company 		= $('#company-name-'+cnt).val();
		company 		= encodeURIComponent(company);
		
		currentPercent 	= $('#current-percent-'+cnt).val();
		newPercent		= $('#pos-size-'+cnt).val();
		
		newAmmount		= $('#show-share-total-'+cnt).val();
		
		currentValue 	= $('#current-value-'+cnt).val();
		
		limitPrice		= $('#limit-price-'+cnt).val();
		
		$('#update-row-'+cnt).fadeTo( "fast", 0.33 );
		
		//get all unchecked columns
		var aUnchecked = checkForChecked();
		
		$.ajax({
			
			url: 'process/ajax/<?php echo $twProcess;?>',
		  	data: {
				process			: 'calculate-line-total',
				unchecked 		: aUnchecked,
				price			: price,
				cash			: cash,
				tradeType		: tradeType,
				stock			: stockSymbol,
				shares			: shares,
				totalValue		: totalValue,
				currentShares	: currentShares,
				fund			: fundID,
				cnt				: cnt,
				company			: company,
				currentPercent	: currentPercent,
				currentValue	: currentValue,
				calcType		: calcType,
				cashPrevious	: cashPrevious,
				cashAdjust		: cashAdjust,
				newPercent		: newPercent,
				newAmmount		: newAmmount,
				limitPrice		: limitPrice
			},
			success: function(newRow) {
				//alert(newRow);
			
				$('#update-row-'+cnt).html(newRow);
				
				cashAdjust = $('#'+fundID+'-cash-new-'+cnt).val();
				
				$('#'+fundID+'-calc-cash').val(cashAdjust);
				
				//number format
				$.ajax({
					url: 'process/ajax/<?php echo $twProcess;?>?process=5&value=' + cashAdjust,
					success: function(data) {
					
						$('#'+fundID+'-cash').val(data);
						
						$('#update-row-'+cnt).fadeTo( "fast", 1 );
						
						//Get variables for notification
						messageType 	= $('#message-type-'+cnt).val();
						messagePos		= $('#message-pos-'+cnt).val();
						messageTimeOut	= $('#message-time-'+cnt).val();
						
						customMessage	= $('#custom-message-'+cnt).val();
						customTitle		= $('#custom-title-'+cnt).val();
						
						//Set defaults
						if(messagePos == ''){
							messagePos = 'toast-top-right';
						}
						if(messageTimeOut == ''){
							messageTimeOut == '10000';	
						}
						
						//Print message if there is one
						if(messageType != ''){
							var shortCutFunction = 'success';
							var msg = 'Please click here to view your post.';
							var title = 'Discussion Started!';
							
							//Notification
							toastr.options = {
								closeButton: true,
								debug: false,
								positionClass: messagePos,
								timeOut: messageTimeOut
							};
							
							/*toastr.options.onclick = function () {
								//alert('When notifiaction is clicked');
								window.location.href=data;
							};*/
							
							toastr[messageType](customMessage, customTitle);
						}
						
						showHideNext();
				  	}
				
				});
			
		  	}
		
		});
}



function printTradeConf(index){
		
	if(index == "2"){
	
		//alert(index);
		$.ajax({
			type: "POST",
			data: $(".submit-trade").serialize(),
			url: "process/ajax/<?php echo $twProcess;?>?process=6&index=" + index,
			success: function(data)
			{
				//alert(data);
				
				//alert(data);
				$('.load-confirm').html(data);
				
		 		
				
			}
		});
			
	}//end if index == 2
}

function processTrades(index){
	
	if(index == "3"){
		
		$('#submit-trade-btn').html('<button type="button" class="btn btn-default">Processing...</button>');
		
		tradeCnt = parseInt($('#row-cnt').val());
		//alert(tradeCnt);
		timeSec = tradeCnt * 1;
		//alert(timeSec);
		timeMin = timeSec / 60;
		//alert(timeMin);
		if(tradeCnt > "30"){
			$('#submit-trades').html('<img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" /> Submitting '+tradeCnt+' Trades... Estimated Time: '+timeMin+' Minutes');
		}else{
			$('#submit-trades').html('<img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" /> Submitting '+tradeCnt+' Trades... Estimated Time: '+timeSec+' Seconds');
		}
		
		$('#submitting-trade').modal('toggle');
		
		//alert(index);
		$.ajax({
			type: "POST",
			data: $(".submit-trade").serialize(),
			url: "process/ajax/<?php echo $twProcess;?>?process=7&index=" + index,
			success: function(data)
			{	
				$('#submit-trades').html(data);
				//alert(data);
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.success('Trades Submitted Successfully!');
				
				setTimeout(function() {
					 window.location = '/?page=02-00-00-002&tradeMessage=1';
				}, 4000);
				
		 
			}
		});
			
	}//end if index == 3
	
}
			
function fastExec(){
	$('#quick-submit').html('<button type="button" class="btn btn-dafault">Processing...</button>');
	processTrades(3);
}

//+-------------------------------------------------------------------------------
//|						WIZARD BUTTON CONTROL
//+-------------------------------------------------------------------------------

function nextButton(index){
	
	if(index == "0"){
		
		tradeSymbols(index,'<?php if($wiz != ''){echo '1';}?>');
	}
	
	if(index == "2"){
		printTradeConf(index);	
	}
}



//+-------------------------------------------------------------------------------
//|						WIZARD VALIDATION
//+-------------------------------------------------------------------------------

var TradeWizard = function () {


    return {
        //main function to initiate the module
        init: function () {
            if (!jQuery().bootstrapWizard) {
                return;
            }

            var form = $('#submit_form');
            var error = $('.alert-danger', form);
            var success = $('.alert-success', form);

            form.validate({
                doNotHideMessage: true, //this option enables to show the error/success messages on tab switch.
                errorElement: 'span', //default input error message container
                errorClass: 'help-block', // default input error message class
                focusInvalid: false, // do not focus the last invalid input

				
				rules: {
					symbols: {
						required: true	
					},
					order_type: {
						required: true	
					},
					funds_front: {
						required: true	
					},
					'include_funds[]': {
						required: true	
					}
                },

                messages: { // custom messages for radio buttons and checkboxes
                    'funds_front': {
                        required: "Please select a fund."
                    },
					'order_type': {
						required: "Please select an Order Type."	
					},
					'validate': {
						required: "Please fix the errors above."	
					},
					'include_funds[]': {
						required: "Please check at least 1 fund."	
					}
                },

                errorPlacement: function (error, element) { // render error placement for each input type
                    if (element.attr("name") == "order_type") { // for uniform radio buttons, insert the after the given container
                        error.insertAfter("#form_order_type_error");
                    } else if (element.attr("name") == "payment[]") { // for uniform radio buttons, insert the after the given container
                        error.insertAfter("#form_payment_error");
                    } else if (element.attr("name") == "include_funds[]"){
						error.insertAfter("#form_select_funds_error");
					}else {
                        error.insertAfter(element); // for other inputs, just perform default behavior
                    }
                },

                invalidHandler: function (event, validator) { //display error alert on form submit   
                    success.hide();
                    error.show();
                    App.scrollTo(error, -200);
                },

                highlight: function (element) { // hightlight error inputs
                    $(element)
                        .closest('.form-group').removeClass('has-success').addClass('has-error'); // set error class to the control group
                },

                unhighlight: function (element) { // revert the change done by hightlight
                    $(element)
                        .closest('.form-group').removeClass('has-error'); // set error class to the control group
                },

                success: function (label) {
                    if (label.attr("for") == "order-type" /*|| label.attr("for") == "payment[]"*/) { // for checkboxes and radio buttons, no need to show OK icon
                        label
                            .closest('.form-group').removeClass('has-error').addClass('has-success');
                        label.remove(); // remove error label here
                    } else { // display success icon for other inputs
                        label
                            .addClass('valid') // mark the current input as valid and display OK icon
                        .closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
                    }
                },

                submitHandler: function (form) {
                    success.show();
                    error.hide();
                    //add here some ajax code to submit your form or just call form.submit() if you want to submit the form without ajax
                }

            });

            var displayConfirm = function() {
                $('#tab4 .form-control-static', form).each(function(){
                    var input = $('[name="'+$(this).attr("data-display")+'"]', form);
                    if (input.is(":text") || input.is("textarea")) {
                        $(this).html(input.val());
                    } else if (input.is("select")) {
                        $(this).html(input.find('option:selected').text());
                    } else if (input.is(":radio") && input.is(":checked")) {
                        $(this).html(input.attr("data-title"));
                    } else if ($(this).attr("data-display") == 'payment') {
                        var payment = [];
                        $('[name="payment[]"]').each(function(){
                            payment.push($(this).attr('data-title'));
                        });
                        $(this).html(payment.join("<br>"));
                    }
                });
            }
			
			
            var handleTitle = function(tab, navigation, index) {
				//alert(tab);
                var total = navigation.find('li').length;
                var current = index + 1;
				
				//alert(index);
				$('#change_button').html('<a onclick="previousButton(\''+index+'\');" href="javascript:;" class="btn btn-default button-previous" ><i class="m-icon-swapleft"></i> Back</a> <a onclick="nextButton(\''+index+'\');" href="javascript:;" class="btn btn-info button-next">Continue <i class="m-icon-swapright m-icon-white"></i></a> <span id="submit-trade-btn"><a href="javascript:;" onclick="processTrades(\''+index+'\');" class="btn btn-success button-submit">Submit Trade<i class="m-icon-swapright m-icon-white"></i></a></span> <span id="quick-submit"><a href="javascript:void(0);" class="btn btn-warning" id="button-exec" onclick="fastExec();" style="display:none;">Skip Analysis &amp; Execute Now</a></span>');
				
                // set wizard title
                $('.step-title', $('#trade_wizard')).text('Step ' + (index + 1) + ' of ' + total);
                // set done steps
                jQuery('li', $('#trade_wizard')).removeClass("done");
                var li_list = navigation.find('li');
                for (var i = 0; i < index; i++) {
                    jQuery(li_list[i]).addClass("done");
                }
				
                if (current == 1) {
                    $('#trade_wizard').find('.button-previous').hide();
                } else {
                    $('#trade_wizard').find('.button-previous').show();
					
                }

                if (current >= total) {
                    $('#trade_wizard').find('.button-next').hide();
                    $('#trade_wizard').find('.button-submit').show();
                    displayConfirm();
                } else {
                    $('#trade_wizard').find('.button-next').show();
                    $('#trade_wizard').find('.button-submit').hide();
                }
                App.scrollTo($('.page-title'));
            }

            // default form wizard
            $('#trade_wizard').bootstrapWizard({
                'nextSelector': '.button-next',
                'previousSelector': '.button-previous',
                onTabClick: function (tab, navigation, index, clickedIndex) {
                    success.hide();
                    error.hide();
                    if (form.valid() == false) {
                        return false;
                    }
                    handleTitle(tab, navigation, clickedIndex);
                },
                onNext: function (tab, navigation, index) {
                    success.hide();
                    error.hide();

                    if (form.valid() == false) {
                        return false;
                    }
					
                    handleTitle(tab, navigation, index);
                },
                onPrevious: function (tab, navigation, index) {
                    success.hide();
                    error.hide();

                    handleTitle(tab, navigation, index);
                },
                onTabShow: function (tab, navigation, index) {
                    var total = navigation.find('li').length;
                    var current = index + 1;
                    var $percent = (current / total) * 100;
                    $('#trade_wizard').find('.progress-bar').css({
                        width: $percent + '%'
                    });
					
                }
            });

            $('#trade_wizard').find('.button-previous').hide();
            $('#trade_wizard .button-submit').click(function () {
                alert('Finished! Hope you like it :)');
            }).hide();
        }

    };

}();


var TradeTable1 = function () {

     var initTable1 = function() {
        var oTable = $('#trade_table_1').dataTable( {           
            "aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[1, 'asc']],
             "aLengthMenu": [
                [5, 15, 20, -1],
                [5, 15, 20, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 10,
        });

        //jQuery('#trade_table_1_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        //jQuery('#trade_table_1_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        //jQuery('#trade_table_1_wrapper .dataTables_length select').select2(); // initialize select2 dropdown
		jQuery('#trade_table_1_wrapper .dataTables_filter').css('display', 'none');
		jQuery('#trade_table_1_wrapper .dataTables_length').css('display', 'none');
		
        $('#trade_table_1_column_toggler input[type="checkbox"]').change(function(){
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
            oTable.fnSetColumnVis(iCol, (bVis ? false : true));
        });
    }

    return {

        //main function to initiate the module
        init: function () {
            
            if (!jQuery().dataTable) {
                return;
            }

            initTable1();
        }

    };

}();

</script>