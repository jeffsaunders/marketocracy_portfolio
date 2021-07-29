var supportTicket = function () {

	var handleSubmit = function() {
		$('.ajax').validate({
	            errorElement: 'span', //default input error message container
	            errorClass: 'help-block', // default input error message class
	            focusInvalid: false, // do not focus the last invalid input
	            rules: {
	                stock_ticker: {
	                    required: true
	                },
	                subject: {
	                    required: true
	                },
	                description: {
	                    required: false
	                }
	            },

	            messages: {
	                stock_ticker: {
	                    required: "Please provide a Stock Ticker related to the Corporate Action."
	                },
	                subject: {
	                    required: "Please provide a subject line."
	                },
					description: {
						required: "Please provide details to your support ticket."	
					}
	            },

	            invalidHandler: function (event, validator) { //display error alert on form submit
	                $('.alert-error', $('.ajax')).show();
	            },

	            highlight: function (element) { // hightlight error inputs
	                $(element)
	                    .closest('.form-group').addClass('has-error'); // set error class to the control group
	            },

	            success: function (label) {
	                label.closest('.form-group').removeClass('has-error');
	                label.remove();
	            },

	            errorPlacement: function (error, element) {
	                error.insertAfter(element.closest('.input-icon'));
	            },

	            /*submitHandler: function (form) {
	                form.submit();
	            }*/
	        });

	       /* $('.ajax input').keypress(function (e) {
	            if (e.which == 13) {
	                if ($('.ajax').validate().form()) {
	                    //$('.ajax').submit();
	                }
	                return false;
	            }
	        });*/
	}

	
    
    return {
        //main function to initiate the module
        init: function () {
        	
            handleSubmit();
            
	       
        }

    };

}();