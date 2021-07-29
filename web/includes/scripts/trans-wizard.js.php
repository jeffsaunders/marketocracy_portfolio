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


function loadConfirm(){
	
	//alert('test');
	$('.trans-wizard-errors').html();
	$.ajax({
        type: "POST",
        data: $("#trans_wizard_form").serialize(),
        url: "process/ajax/trans-wizard-processes.php?process=load-confirm",
        success: function(data)
        {
			
			$('#confirm-sec').html(data);
            
     		
        }
    });
	
	return false;
		
}



/*$(document).ready(function () {

	
	$('input:checkbox').click(function(){
		
		var len = $("input[name='funds']:checked").length;
		//alert (len);
		var maxLen = 2;
		
		var $inputs = $('input:checkbox')
		
		if(maxLen == len){
			if($('.fund-checkbox').prop('checked') == false){
					
			}
		}
		
		if(len < maxLen){
			alert('check');
		}else{
			alert('dont check');
			$(this).prop('disabled',true);		
		}
			
			
			if($(this).is(':checked')){
				
				
					//$(this).addClass('checked');
					//$(this).removeClass('dechecked');
				
				
				//$inputs.not(this).prop('disabled',true); 
				//alert('checked');
			}else{
				
				$(this).addClass('dechecked');
				$(this).removeClass('checked');
				//alert('dechecked');
				//$inputs.prop('disabled',false);
			}
		
	})
})*/






$(document).ready(function() {
	$("form").on("click", ":checkbox", function(event){
	$(":checkbox:not(:checked)", this.form).prop("disabled", function(){
    return $(this.form).find(":checkbox:checked").length == <?php echo $_SESSION['subscription']['maxFunds'];?>;
  });
});
});



$('form#trans_wizard_form').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $("#trans_wizard_form").serialize(),
        url: "process/ajax/trans-wizard-processes.php?process=deactivate-funds",
        success: function(data)
        {
			
			$('.trans-wizard-errors').html(data);
            
			if((data.indexOf('success') > -1) == true){
				setTimeout(function() {
					window.location = "/";	
				}, 2000);
			}
            
     
        }
    });
	
	return false;
});	

var TransWizard = function () {


    return {
        //main function to initiate the module
        init: function () {
            if (!jQuery().bootstrapWizard) {
                return;
            }
			
			




            var form = $('#trans_wizard_form');
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
					/*first_name: {
                        required: true
                    },*/
					/*last_name: {
                        required: true
                    },
					alt_email: {
                        email: true
                    },
					alt_email_check: {
						required: true
					},
					phone_day: {
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
                $('#tab2 .form-control-static', form).each(function(){
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
                $('.step-title', $('#trans_wizard_1')).text('Step ' + (index + 1) + ' of ' + total);
                // set done steps
                jQuery('li', $('#trans_wizard_1')).removeClass("done");
                var li_list = navigation.find('li');
                for (var i = 0; i < index; i++) {
                    jQuery(li_list[i]).addClass("done");
                }

                if (current == 1) {
                    $('#trans_wizard_1').find('.button-previous').hide();
                } else {
                    $('#trans_wizard_1').find('.button-previous').show();
                }

                if (current >= total) {
                    $('#trans_wizard_1').find('.button-next').hide();
                    $('#trans_wizard_1').find('.button-submit').show();
                    displayConfirm();
                } else {
                    $('#trans_wizard_1').find('.button-next').show();
                    $('#trans_wizard_1').find('.button-submit').hide();
                }
                App.scrollTo($('.page-title'));
            }

            // default form wizard
            $('#trans_wizard_1').bootstrapWizard({
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
                    $('#trans_wizard_1').find('.progress-bar').css({
                        width: $percent + '%'
                    });
                }
            });

            $('#trans_wizard_1').find('.button-previous').hide();
            $('#trans_wizard_1 .button-submit').click(function () {
                /*alert('Finished! Hope you like it :)');*/
            }).hide();
        }

    };

}();
</script>
