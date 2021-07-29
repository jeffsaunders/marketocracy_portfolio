var SetupWizard = function () {


    return {
        //main function to initiate the module
        init: function () {
            if (!jQuery().bootstrapWizard) {
                return;
            }

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


            var form = $('#submit_form');
            var error = $('.alert-danger', form);
            var success = $('.alert-success', form);

            /*form.validate({
                doNotHideMessage: true, //this option enables to show the error/success messages on tab switch.
                errorElement: 'span', //default input error message container
                errorClass: 'help-block', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                rules: {
                    //account setup
					// minlength: (number), maxlength: (number), required (boolean), email (boolean), digits (boolean), equalTo: "#some-id",
					name_first: {
                        required: true
                    },
					name_last: {
                        required: true
                    },
                    username: {
                        minlength: 5,
                        required: true
                    },
					email: {
                        required: true,
                        email: true
                    },
					email2: {
                        required: true,
                        email: true,
						equalTo: "#confirm-email"
                    },
					address1: {
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
                    },

					//Profile Setup
					age: {
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

                    //Membership/Payment/Billing Address
					membership: {
                        required: true
                    },
					//comment out until launch/beta testing
                    /*card_name: {
                        required: true
                    },
                    card_number: {
                        minlength: 16,
                        maxlength: 16,
                        required: true
                    },
                    card_cvc: {
                        digits: true,
                        required: true,
                        minlength: 3,
                        maxlength: 4
                    },
                    card_expiry_date: {
                        required: true
                    },
                    'payment[]': {
                        required: true,
                        minlength: 1
                    },
					bill_address1: {
                        required: true
                    },
					bill_zip: {
                        required: true
                    },
					bill_city: {
                        required: true
                    },
					bill_state: {
                        required: true
                    },
					bill_country: {
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
						minlength: 3
                    }


                },

                messages: { // custom messages for radio buttons and checkboxes
                    'payment[]': {
                        required: "Please select at least one option."
                    },
					'membership': {
						required: "Please select a membership level."
					},
					'fund_type': {
						required: "Please select a fund type."
					}
                },

                errorPlacement: function (error, element) { // render error placement for each input type
                    if (element.attr("name") == "gender") { // for uniform radio buttons, insert the after the given container
                        error.insertAfter("#form_gender_error");
                    } else if (element.attr("name") == "payment[]") { // for uniform radio buttons, insert the after the given container
                        error.insertAfter("#form_payment_error");
                    } else if (element.attr("name") == "username") {
						error.insertAfter("#error_username");
					} else if (element.attr("name") == "email") {
						error.insertAfter("#error_email");
					} else if (element.attr("name") == "email2") {
						error.insertAfter("#error_email2");
					} else if (element.attr("name") == "membership") {
						error.insertAfter("#error-membership");
					} else if (element.attr("name") == "fund_type") {
						error.insertAfter("#error_fund_type");
					} else {
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

            });*/

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