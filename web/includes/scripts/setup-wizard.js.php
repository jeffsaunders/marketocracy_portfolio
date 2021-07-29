<script>

function showAll(option){
	
	$('.showHide'+option).removeClass('hide');
	$('#showBtn'+option).addClass('hide');
	$('#hideBtn'+option).removeClass('hide');	
}

function hideAll(option){
	$('.showHide'+option).addClass('hide');
	$('#hideBtn'+option).addClass('hide');
	$('#showBtn'+option).removeClass('hide');
}

function loadAddress(country){
	
	$.ajax({
        type: "POST", /*form method*/
        data: {process:'1',country:country}, 
        url: "process/ajax/setup-wizard-processes.php", /*location of php processing code, Note: this uses a process variable to distinguish between different processes*/
        
		success: function(data)
        {
			//Change the submit button to display that the settings have been saved
			$('#load-address-fields').html(data);			
			
        }
    });
		
}

function checkForEDU(toggle){
	
	if(toggle == true){
		
		$.ajax({
			type: "POST", /*form method*/
			data: {process:'3'}, 
			url: "process/ajax/setup-wizard-processes.php", /*location of php processing code, Note: this uses a process variable to distinguish between different processes*/
			
			success: function(data)
			{
				if((data.indexOf('no-valid-edu-email') > -1) == true){
					$('#edu-email-check').html(data);	
					$('#is-student').addClass('hide');
				}else{
					$('#edu-email-check').html(data);
				}
				
			}
		});
	}else if(toggle == false){
		$('#edu-email-check').html('');	
	}
		
}

function checkStudent(toggle){
	
	if(toggle == 'yes'){
		$('#is-student').removeClass('hide');
		
		checkForEDU(true);
	}else if(toggle == 'no'){
		$('#is-student').addClass('hide');
		
		checkForEDU(false);
	}
		
}

function checkClassCode(code){
	
	if(code == 'yes'){
		$('#class-code-area').removeClass('hide');
	}else if(code == 'no'){
		$('#class-code-area').addClass('hide');
	}
		
}

function validateEduEmail(element){
	
	var eduEmail = $('#'+element).val();
	
	$.ajax({
        type: "POST", /*form method*/
        data: {process:'4',email:eduEmail}, 
        url: "process/ajax/setup-wizard-processes.php", /*location of php processing code, Note: this uses a process variable to distinguish between different processes*/
        
		success: function(data)
        {
			//Change the submit button to display that the settings have been saved
			if((data.indexOf('show-code-box') > -1) == true){
				$('#edu-validate-field').addClass('hide');
				$('#edu-code-field').removeClass('hide');
			}else{
				$('#error_edu_email').html(data);
			}
			
        }
    });
	
}

function submitValidateCode(element){
	
	var emailCode = $('#'+element).val();
	
	$.ajax({
        type: "POST", /*form method*/
        data: {process:'5',code:emailCode}, 
        url: "process/ajax/setup-wizard-processes.php", /*location of php processing code, Note: this uses a process variable to distinguish between different processes*/
        
		success: function(data)
        {
			//Change the submit button to display that the settings have been saved
			if((data.indexOf('email-is-valid') > -1) == true){
				$('#edu-email-check').html(data);
				
				$('#is-student').removeClass('hide');
			}else{
				$('#error_email_code').html(data);
			}
			
        }
    });
	
}

$('#class_code').keyup(function () {
	
	var code = $(this).val();
	
	$.ajax({
        type: "POST", /*form method*/
        data: {process:'2',code:code}, 
        url: "process/ajax/setup-wizard-processes.php", /*location of php processing code, Note: this uses a process variable to distinguish between different processes*/
        
		success: function(data)
        {
			//Change the submit button to display that the settings have been saved
			$('#class_code_details').html(data);
			if((data.indexOf('alert-danger') > -1) == true){
				$('#class_code').css('border-color', '#B94A48');
				$('#class_code_label').css('color', '#B94A48');
			}else{
				$('#class_code').css('border-color', '#356635');
				$('#class_code_label').css('color', '#356635');
			}			
			
        }
    });
	
});

$('#fund_symbol').keyup(function () {
	
	var symbol = $(this).val();
	
	$.ajax({
        type: "POST", /*form method*/
        data: {process:'6',fundSymbol:symbol}, 
        url: "process/ajax/setup-wizard-processes.php", /*location of php processing code, Note: this uses a process variable to distinguish between different processes*/
        
		success: function(data)
        {
			//Change the submit button to display that the settings have been saved
			$('#error_fund_symbol').html(data);			
			if((data.indexOf('alert-danger') > -1) == true){
				$('#fund_symbol').css('border-color', '#B94A48');
				$('#fund_symbol_label').css('color', '#B94A48');
			}else{
				$('#fund_symbol').css('border-color', '#356635');
				$('#fund_symbol_label').css('color', '#356635');
			}
        }
    });	
});

function checkEmail(email){
	
	if(email != ''){
	
		$.ajax({
			type: "POST", /*form method*/
			data: {email:email}, 
			url: "process/ajax/check-email-dev.php", /*location of php processing code, Note: this uses a process variable to distinguish between different processes*/
			
			success: function(data)
			{
				//Change the submit button to display that the settings have been saved
				$('#error_email').html(data);
				
				//if email exists
				if((data.indexOf('already registered') > -1) == true){
					//$('#email-check').html('<i class="icon-warning-sign" style="color:#C09853 !important;"></i>');
					$('#alt_email').css('border-color', '#C09853');
					$('#error-fields').html('<input type="text" name="alt_email_check" />');
					$('#alt_email_error_fix').css({'border-color':'#C09853','background':'#eee3cf'});
				}
				
				//if email is good
				if((data.indexOf('value="passed"') > -1) == true){
					//$('#email-check').html('<i class="icon-ok" style="color:#468847 !important;"></i>');
					$('#alt_email').css('border-color', '#468847');
					$('#error-fields').html('');	
					$('#alt_email_error_fix').css({'border-color':'#468847','background':'#DFF0D8'});
				}
				
				//if email is invalid
				if((data.indexOf('valid email address') > -1) == true){
					//$('#email-check').html('<i class="icon-warning-sign" style="color:#B94A48 !important;"></i>');
					$('#alt_email').css('border-color', '#B94A48');
					$('#error-fields').html('<input type="text" name="alt_email_check" />');
					$('#alt_email_error_fix').css({'border-color':'#B94A48','background':'#F2DEDE'});
				}
				
				
			}
		});
	}else{
		$('#alt_email').css('border-color', '#E5E5E5');
		$('#error-fields').html('');
		$('#error_email').html('');
		$('#alt_email_error_fix').css({'border-color':'#E5E5E5','background':'#E5E5E5'});
	}
}


$('form#setup_wizard_form').on('submit', function() {
	
	$('#save-btn').html('<button class="btn btn-default button-submit"></button>');
	
	$.ajax({
        type: "POST",
        data: $("#setup_wizard_form").serialize(),
        url: "process/ajax/setup-wizard-processes.php?process=7",
        success: function(data)
        {
			
			
			
			if((data.indexOf('success') > -1) == true){
				$('#save-btn').html('');
				setTimeout(function() {
					window.location = "?page=02-00-00-001&fund=<?php echo $_SESSION['member_id'];?>-1&trade=buy&wiz=buy";	
				}, 2);
			}else{
				$('#setup-wizard-errors').html(data);
            	$('#save-btn').html('<input type="submit" value="Save & Start Trading" class="btn btn-success button-submit">');
			}
            
     
        }
    });
	
	return false;
});	

var SetupWizard = function () {


    return {
        //main function to initiate the module
        init: function () {
            if (!jQuery().bootstrapWizard) {
                return;
            }
			
			function format(country) {
				if (!country.id) return country.text; // optgroup
	//            return "<img class='flag' src='images/flags/" + state.id.toLowerCase() + ".png'/>&nbsp;&nbsp;" + state.text;
				return "<img class='flag' src='images/flags/countries/" + country.id.toLowerCase().substring(0,2).trim() + ".png'/>&nbsp;&nbsp;" + country.text;
			}
	
	
			$("#select2_sample4").select2({
				placeholder: '<i class="icon-map-marker"></i>&nbsp;Select a Country',
				allowClear: true,
				formatResult: format,
				formatSelection: format,
				escapeMarkup: function (m) {
					return m;
				}
			});


			/*$('#select2_sample4').change(function () {
                $('.register-form').validate().element($(this)); //revalidate the chosen dropdown value and show error or success message for the input
            });*/

			
            /*function formatC(country) {
                if (!country.id) return country.text; // optgroup
//                return "<img class='flag' src='images/flags/" + state.id.toLowerCase() + ".png'/>&nbsp;&nbsp;" + state.text;
		   		return "<img class='flag' src='images/flags/countries/" + country.id.toLowerCase().substring(0,2).trim() + ".png'/>&nbsp;&nbsp;" + country.text;
           }

			$("#country").select2({
			  	placeholder: 'Select a Country',
	            allowClear: true,
	            formatResult: formatC,
	            formatSelection: formatC,
	            escapeMarkup: function (m) {
	                return m;
	            }
	        });
			
			//START | THIS IS FOR AN ON CHANGE EVENT FOR COUNTRIES
			$select = $("#country").off("change");
			$select.on("change", function(e) {           
           		showStates('country='+$(this).val());
       		});
			//END | THIS IS FOR AN ON CHANGE EVENT FOR COUNTRIES
			
			$('#country').change(function () {
                $('.register-form').validate().element($(this)); //revalidate the chosen dropdown value and show error or success message for the input
				
            });*/

           /* $("#country_list").select2({
                placeholder: "Select",
                allowClear: true,
                formatResult: formatC,
                formatSelection: formatC,
                escapeMarkup: function (m) {
                    return m;
                }
            });*/


            /*function formatS(state) {
                if (!state.id) return state.text; // optgroup
//                return "<img class='flag' src='images/flags/" + state.id.toLowerCase() + ".png'/>&nbsp;&nbsp;" + state.text;
		   		return "<img class='flag' src='images/flags/states/" + state.id.toLowerCase().substring(0,2).trim() + ".png'/>&nbsp;&nbsp;" + state.text;
           }

			$("#state-field").select2({
				selectedIndex: 0,
			  	placeholder: 'Select a State/Province',
	            allowClear: true,
	            formatResult: formatS,
	            formatSelection: formatS,
	            escapeMarkup: function (m) {
	                return m;
	            }
	        });

			$('#state-field').change(function () {
                $('.register-form').validate().element($(this)); //revalidate the chosen dropdown value and show error or success message for the input
            });

            $("#state_list").select2({
                placeholder: "Select",
                allowClear: true,
                formatResult: formatS,
                formatSelection: formatS,
                escapeMarkup: function (m) {
                    return m;
                }
            });*/


            var form = $('#setup_wizard_form');
            var error = $('.alert-danger', form);
            var success = $('.alert-success', form);

            form.validate({
                doNotHideMessage: true, //this option enables to show the error/success messages on tab switch.
                errorElement: 'span', //default input error message container
                errorClass: 'help-block', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                rules: {
                    //account setup
					// minlength: (number), maxlength: (number), required (boolean), email (boolean), digits (boolean), equalTo: "#some-id",
					first_name: {
                        required: true
                    },
					last_name: {
                        required: true
                    },
					alt_email: {
                        email: true
                    },
					alt_email_check: {
						required: true
					},
					/*phone_day: {
						required: true
					},*/
					/*address1: {
                        required: true
                    },
					zip: {
                        required: true
                    },
					city: {
                        required: true
                    },
					state: {
                        required: true
                    },
					country: {
                        required: true
                    },*/

					//Profile Setup
					year: {
                        digits: true,
						maxlength: 4,
						minlength: 4
                    },
					linkedin: {
                        url: true
                    },
					twitter: {
                        url: true
                    },
					google: {
                        url: true
                    },
					web_site: {
                        url: true
                    },
					facebook: {
                        url: true
                    },
					
					edu_email: {
						email: true,
						required: true
					},
					
					validate_code: {
						required: true
					},
                   
					class_code_check: {
						required: true
					},
					
					class_code_check2: {
						required: true
					},
					
					class_code: {
						required: true
					},
					//Fund Setup
					fund_type: {
                        required: true
                    },
					fund_name: {
                        required: true,
						minlength: 5
                    },
					fund_symbol: {
                        required: true,
						minlength: 3,
						maxlength: 8
                    },
					fund_symbol_check: {
						required: true
					}


                },

                messages: { // custom messages for radio buttons and checkboxes
                    /*'payment[]': {
                        required: "Please select at least one option."
                    },
					'membership': {
						required: "Please select a membership level."
					},
					'fund_type': {
						required: "Please select a fund type."
					}*/
                },

                errorPlacement: function (error, element) { // render error placement for each input type
                    if (element.attr("name") == "alt_email") { // for uniform radio buttons, insert the after the given container
                        $("#error_email").html(error);
                    } else if (element.attr("name") == "first_name") {
						$("#error_first_name").html(error);
					} else if (element.attr("name") == "last_name") {
						$("#error_last_name").html(error);
					} else if (element.attr("name") == "phone_day") {
						$("#error_phone_day").html(error);
					} else if (element.attr("name") == "address1") {
						$("#error_address1").html(error);
					} else if (element.attr("name") == "city") {
						$("#error_city").html(error);
					} else if (element.attr("name") == "state") {
						$("#error_state").html(error);
					} else if (element.attr("name") == "zip") {
						$("#error_zip").html(error);
					} else if (element.attr("name") == "class_code_check") {
						$("#error_class_code_check").html(error);
					} else if (element.attr("name") == "edu_email") {
						$("#error_edu_email").html(error);
					} else if (element.attr("name") == "fund_type") {
						$("#error_fund_type").html(error);
					} else if (element.attr("name") == "validate_code"){
						$("#error_email_code2").html(error);	
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
                    if (label.attr("for") == "gender" || label.attr("for") == "payment[]") { // for checkboxes and radio buttons, no need to show OK icon
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
                var total = navigation.find('li').length;
                var current = index + 1;
                // set wizard title
                $('.step-title', $('#setup_wizard_1')).text('Step ' + (index + 1) + ' of ' + total);
                // set done steps
                jQuery('li', $('#setup_wizard_1')).removeClass("done");
                var li_list = navigation.find('li');
                for (var i = 0; i < index; i++) {
                    jQuery(li_list[i]).addClass("done");
                }

                if (current == 1) {
                    $('#setup_wizard_1').find('.button-previous').hide();
                } else {
                    $('#setup_wizard_1').find('.button-previous').show();
                }

                if (current >= total) {
                    $('#setup_wizard_1').find('.button-next').hide();
                    $('#setup_wizard_1').find('.button-submit').show();
                    displayConfirm();
                } else {
                    $('#setup_wizard_1').find('.button-next').show();
                    $('#setup_wizard_1').find('.button-submit').hide();
                }
                App.scrollTo($('.page-title'));
            }

            // default form wizard
            $('#setup_wizard_1').bootstrapWizard({
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
                    $('#setup_wizard_1').find('.progress-bar').css({
                        width: $percent + '%'
                    });
                }
            });

            $('#setup_wizard_1').find('.button-previous').hide();
            $('#setup_wizard_1 .button-submit').click(function () {
                /*alert('Finished! Hope you like it :)');*/
            }).hide();
        }

    };

}();
</script>
