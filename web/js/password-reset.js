var PWReset = function () {

	var handleResetPassword = function () {
		$('.reset-form').validate({
	            errorElement: 'span', //default input error message container
	            errorClass: 'help-block', // default input error message class
	            focusInvalid: true, // focus the last invalid input
	            ignore: "",
	            rules: {
	                password: {
	                    required: true,
	                },
	                password2: {
	                    equalTo: "#password"
	                },
	                password_score: {
	                    required: true,
						min: 25
	                },
	                weak_pw: {
	                    required: true
	                }
	            },

	            messages: {
	                password: {
	                    required: "Password is required."
	                },
	                password_score: {
	                    required: "Your password is too weak to be acceptible."
	                },
	                weak_pw: {
	                    required: "You must acknowledge that you are informed that your password is considered weak."
	                }
	            },

	            invalidHandler: function (event, validator) { //display error alert on form submit
	                $('.alert-error', $('.reset-form')).show();
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
	                if (element.attr("name") == "password_score") {
	                    error.insertAfter($('#password_score_error'));
	                } else if (element.attr("name") == "weak_pw") {
	                    error.insertAfter($('#weak_pw_error'));
	                } else if (element.closest('.input-icon').size() === 1) {
	                    error.insertAfter(element.closest('.input-icon'));
	                } else {
	                	error.insertAfter(element);
	                }
	            },
	            submitHandler: function (form) {
	                form.submit();
	            }
	        });

	        $('.reset-form input').keypress(function (e) {

	            if (e.which == 13) {
	                if ($('.reset-form').validate().form()) {
	                    $('.reset-form').submit();
	                }
	                return false;
	            }
	        });

	}


    return {
        //main function to initiate the module
        init: function () {

            handleResetPassword();

        }

    };

}();