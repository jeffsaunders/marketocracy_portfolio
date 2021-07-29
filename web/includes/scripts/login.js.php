<?php
/*
Marketocracy Inc. | Beta Development
My Classes Overview Scripts

Supporting files:
	AJAX		- process/ajax/edu-classes-overview-porcesses.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/edu-classes-overview.js.php
	HTML		- includes/pages/edu-classes-overview.php
*/

$processAccountCreation = 'process-acct.php'; /* dev file is: process-acct-dev.php */
?>
<!--EDU CLASSES-->


<script>
function showSignUpCode(action) {
	
	if(action == 'show'){
		$('#show-hide-signup-code').removeClass('hide');
		$('#show-hide-signup-code2').addClass('hide');	
	}else if(action == 'hide'){
		$('#show-hide-signup-code2').removeClass('hide');
		$('#show-hide-signup-code').addClass('hide');	
	}
}

function checkCode(){
	var code = $('#signup_code').val();
	var email = $('#email-field').val();
	
	$.ajax({
        type: "POST", /*form method*/
        data: {code:code,email:email}, 
        url: "process/ajax/check-signup-code.php", /*location of php processing code, Note: this uses a process variable to distinguish between different processes*/
        
		success: function(data)
        {
			//Change the submit button to display that the settings have been saved
			$('#code-check-result').html(data);
			
			
			if((data.indexOf('name="code_success"') > -1) == true){
				$('#code-check').html('<i class="icon-ok" style="color:#468847 !important;"></i>');
				$('#signup_code').css('border-color', '#468847');	
			}else{
				$('#code-check').html('<i class="icon-warning-sign" style="color:#B94A48 !important;"></i>');
				$('#signup_code').css('border-color', '#B94A48');
			}
			
			if(code == ''){
				$('#code-check-result').html('');
				$('#code-check').html('');
				$('#signup_code').css('border-color', '#e5e5e5');
			}
			
        }
    });
}

function checkEmail2(email){
	
	$.ajax({
        type: "POST", /*form method*/
        data: {email:email}, 
        url: "process/ajax/check-email-dev.php", /*location of php processing code, Note: this uses a process variable to distinguish between different processes*/
        
		success: function(data)
        {
			//Change the submit button to display that the settings have been saved
			$('#email-check-result2').html(data);
			
			var code = $('#signup_code').val();
			
			if((data.indexOf('already registered') > -1) == true){
				$('#email-check').html('<i class="icon-warning-sign" style="color:#C09853 !important;"></i>');
				$('#email-field').css('border-color', '#C09853');
			}
			
			if((data.indexOf('value="passed"') > -1) == true){
				$('#email-check').html('<i class="icon-ok" style="color:#468847 !important;"></i>');
				$('#email-field').css('border-color', '#468847');	
				
				if(code != ''){
					checkCode();
				}
			}
			
			if((data.indexOf('valid email address') > -1) == true){
				$('#email-check').html('<i class="icon-warning-sign" style="color:#B94A48 !important;"></i>');
				$('#email-field').css('border-color', '#B94A48');
			}
			
			
			
			
        }
    });
}

$('#signup_code').keyup(function (){
	
	checkCode();
	
});

function checkUsername2(username){

	$.ajax({
        type: "POST", /*form method*/
        data: {username:username}, 
        url: "process/ajax/check-username-dev.php", /*location of php processing code, Note: this uses a process variable to distinguish between different processes*/
        
		success: function(data)
        {
			//Change the submit button to display that the settings have been saved
			$('#username-check-result2').html(data);
			
			
			if((data.indexOf('value="passed"') > -1) == true){
				$('#username-check').html('<i class="icon-ok" style="color:#468847 !important;"></i>');
				$('#username2').css('border-color', '#468847');	
			}else{
				$('#username-check').html('<i class="icon-warning-sign" style="color:#B94A48 !important;"></i>');
				$('#username2').css('border-color', '#B94A48');
			}
			
			
        }
    });

}



function checkPassword2(password, username){
	//check2
	$.ajax({
        type: "POST", /*form method*/
        data: {password:password,username:username}, 
        url: "process/ajax/score-password-dev.php", /*location of php processing code, Note: this uses a process variable to distinguish between different processes*/
        
		success: function(data)
        {
			//Change the submit button to display that the settings have been saved
			$('#password-strength-result').html(data);
			
			var pwScore = $('#password_score').val();
			
			
					
			if(pwScore > 30){
				$('#password-check').html('<i class="icon-ok" style="color:#468847 !important;"></i>');
				$('#password-new').css('border-color', '#468847');
				
			}else{
				$('#password-check').html('<i class="icon-warning-sign" style="color:#B94A48 !important;"></i>');
				$('#password-new').css('border-color', '#B94A48');
			}
			var pw = $('#password-new').val();
			/*var timer = null;
			
			$('#view-password').hover(function() {
				var $el = $('#center-side');
				clearTimeout(timer);
				timer = setTimeout(function() {
					$('#show-hide-pw').html(pw);
					$('#show-hide-pw').show();
					
				}, 500);
			}, function() {
				clearTimeout(timer);
				timer = setTimeout(function() {
					$('#show-hide-pw').html('');
					$('#show-hide-pw').hide();
					//$('#password-new').prop('type', 'password');
				}, 10);

			});*/
			
			$('#view-password').click(function(){
				
				var passwordProp = $('#password-new').prop('type');
				
				if(passwordProp == 'password'){
					$('#password-new').prop('type', 'text');
					$('#view-password').html('<i class="icon-eye-close"></i>  Hide Password');
				}else{
					$('#password-new').prop('type', 'password');
					$('#view-password').html('<i class="icon-eye-open"></i>  Show Password');
				}
			});
			
			$('#password-new').focusout(function(){
				$('#password-new').prop('type', 'password');
				$('#view-password').html('<i class="icon-eye-open"></i>  Show Password');
			});
			
			
			if(pw == ''){
				$('#view-password').hide();
			}
			
			//$('#show-hide-pw').html(pw);
			
			
			
			
        }
    });

}


// Function to verify the two passwords match
		function validatePasswords(){
			// If password is blank
			if (document.getElementById('register-form').elements['password'].value == ""){
				// Turn it red
				document.getElementById("password-div").className += " has-error";
				//Put the cursor back in the password field
				document.forms["register-form"].elements["password"].focus();
			// If the score is less than 25 (too weak)
			}else if (document.forms["register-form"].elements["password_score"] && (document.forms["register-form"].elements["password_score"].value < 25 || document.forms["register-form"].elements["password_score"].value == "X")){
				// Turn it red
				document.getElementById("password-div").className += " has-error";
				//Put the cursor back in the password field
				document.forms["register-form"].elements["password"].focus();
			// If they DON'T match
			}else if (document.getElementById('register-form').elements['password'].value !== document.getElementById('register-form').elements['password2'].value){
				// Turn them red
				document.getElementById("password-div").className += " has-error";
				document.getElementById("password2-div").className += " has-error";
				// Tell 'em what's wrong
				document.getElementById("password-check-result").innerHTML = '<span style="color:#b94a48; font-size:13px; padding:5px 0 0 0;">Passwords do not match.</span>';
				//Put the cursor back in the password2 field
				document.getElementById("register-form").elements["password2"].focus();
			// All's well
			}else{
				// Turn them back to gray if they are red (does nothing if they aren't)
				document.getElementById("password-div").classList.remove("has-error");
				document.getElementById("password2-div").classList.remove("has-error");
				// clear any left-over error message
				document.getElementById("password-check-result").innerHTML = '';
			}
			// If the score is between 25 and 40 (very weak or weak) force them to acknowledge it (CYA)
			if (document.forms["register-form"].elements["password_score"].value > 24 && document.forms["register-form"].elements["password_score"].value < 41){
				document.getElementById("weak-pw-div").style.visibility = "visible";
				document.getElementById("weak-pw-div").style.display = "block";
			// Hide it, in case it was visible and they made it stronger (does noting if it was already hidden)
			}else{
				document.getElementById("weak-pw-div").style.visibility = "hidden";
				document.getElementById("weak-pw-div").style.display = "none";
			}
		}

		// Final check before allowing form submission
		// Most is handled by jQuery but this points out any errors involving unusable usernames or email addresses
		// List them in reverse order so last match is highlighted
		function highlightErrors(){
			// Username already taken
			if (document.forms["register-form"].elements['username_test'].value == ""){
				document.getElementById("register-form").elements["username"].focus();
			}
			// Email already used
			if (document.forms["register-form"].elements['email_test'].value == ""){
				document.getElementById("register-form").elements["email"].focus();
			}
		}

//Process Create Forum Form via AJAX
$('form#register-form2').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $("#register-form2").serialize(),
        url: "process/<?php echo $processAccountCreation;?>",
        success: function(data)
        {
			
			if((data.indexOf('show-error') > -1) == true){
				$('#server-side-errors').html(data);
			}else{
				$('#register-form2').html(data);
				
				$('#register-back-btn2').click(function () {
					$('.login-form').show();
					$('.register-form').hide();
				});
			}
            //alert(data);
            
     
        }
    });
	
	return false;
});		
		
var Login = function () {

	var handleLogin = function() {
		$('.login-form').validate({
	            errorElement: 'span', //default input error message container
	            errorClass: 'help-block', // default input error message class
	            focusInvalid: true, // focus the last invalid input
	            ignore: "",
	            rules: {
	                username: {
	                    required: true
	                },
	                password: {
	                    required: true
	                },
	                remember: {
	                    required: false
	                }
	            },

	            messages: {
	                username: {
	                    required: "Username or Email is required."
	                },
	                password: {
	                    required: "Password is required."
	                }
	            },

	            invalidHandler: function (event, validator) { //display error alert on form submit
	                $('.alert-error', $('.login-form')).show();
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

	            submitHandler: function (form) {
	                form.submit();
	            }
	        });

	        $('.login-form input').keypress(function (e) {
	            if (e.which == 13) {
	                if ($('.login-form').validate().form()) {
	                    $('.login-form').submit();
	                }
	                return false;
	            }
	        });
	}

	var handleForgetPassword = function () {
		$('.forget-form').validate({
	            errorElement: 'span', //default input error message container
	            errorClass: 'help-block', // default input error message class
	            focusInvalid: true, // focus the last invalid input
	            ignore: "",
	            rules: {
	                username: {
	                    required: true
	                },
	                email: {
	                    required: true,
	                    email: true
	                }
	            },

	            messages: {
	                username: {
	                    required: "Username is required."
	                },
	                email: {
	                    required: "Email is required."
	                }
	            },

	            invalidHandler: function (event, validator) { //display error alert on form submit   

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

	            submitHandler: function (form) {
	                form.submit();
	            }
	        });

	        $('.forget-form input').keypress(function (e) {
	            if (e.which == 13) {
	                if ($('.forget-form').validate().form()) {
	                    $('.forget-form').submit();
	                }
	                return false;
	            }
	        });

	        jQuery('#forget-password').click(function () {
	            jQuery('.login-form').hide();
	            jQuery('.forget-form').show();
	        });

	        jQuery('#back-btn').click(function () {
	            jQuery('.login-form').show();
	            jQuery('.forget-form').hide();
	        });

	}

	var handleRegister = function () {

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


			$('#select2_sample4').change(function () {
                $('.register-form').validate().element($(this)); //revalidate the chosen dropdown value and show error or success message for the input
            });



         $('.register-validate').validate({
	            errorElement: 'span', //default input error message container
	            errorClass: 'help-block', // default input error message class
	            focusInvalid: true, // focus the last invalid input
	            ignore: "",
	            rules: {

	                first_name: {
	                    required: true
	                },
	                last_name: {
	                    required: true
	                },/*
	                email: {
	                    required: true,
	                    email: true
	                },*/
	                address: {
	                    required: true
	                },
	                city: {
	                    required: true
	                },
	                country: {
	                    required: true
	                },
	                username: {
	                    required: true,
						minlength: 4
	                },
	                password: {
	                    required: true
	                },
	                password2: {
	                    equalTo: "#password"
	                },
	                password_score: {
	                    required: true,
						min: 25
	                },
	                tnc: {
	                    required: true
	                },
	                weak_pw: {
	                    required: false
	                },
	                email_test: {
	                    required: true
	                },
	                username_test: {
	                    required: true
	                }
	            },

	            messages: { // custom messages for radio buttons and checkboxes
	                tnc: {
	                    required: "You must accept our Terms of Service and Privacy Policy."
	                },
	                password_score: {
	                    required: "Your password is too weak to be acceptible."
	                },
	                weak_pw: {
	                    required: "You must acknowledge that you are informed that your password is considered weak."
	                },
	                email_test: {
	                    required: ""
	                },
	                username_test: {
	                    required: ""
	                }
	            },

	            invalidHandler: function (event, validator) { //display error alert on form submit

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
	                if (element.attr("name") == "tnc") { // insert checkbox errors after the container
	                    error.insertAfter($('#register_tnc_error'));
	                } else if (element.attr("name") == "password_score") {
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

			$('.register-form input').keypress(function (e) {
	            if (e.which == 13) {
	                if ($('.register-form').validate().form()) {
	                    $('.register-form').submit();
	                }
	                return false;
	            }
	        });

	        jQuery('#register-btn').click(function () {
	            jQuery('.login-form').hide();
	            jQuery('.register-form').show();
	        });
			
			 jQuery('#register-btn2').click(function () {
	            jQuery('.login-form').hide();
	            jQuery('#register-form2').show();
	        });

	        jQuery('#register-back-btn').click(function () {
	            jQuery('.login-form').show();
	            jQuery('.register-form').hide();
	        });
	}

    return {
        //main function to initiate the module
        init: function () {

            handleLogin();
            handleForgetPassword();
            handleRegister();

        }

    };

}();
</script>