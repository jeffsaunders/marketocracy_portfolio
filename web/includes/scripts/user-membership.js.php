<?php
/*
Marketocracy Inc. | Beta Development
User Profile/Settings Scripts

Supporting files:
	AJAX			- process/ajax/user-membership-processes.php
	JAVASCRIPT		- THIS DOCUMENT includes/scripts/user-membership.js.php
	HTML			- includes/pages/user-profile.php
	PHP Includes	- THIS DOCUMENT includes/pages/includes/user-profile/membership.php
*/

$processFile = 'process/ajax/user-membership-processes.php';

?>
<script type="text/javascript">

//VALIDATION FUNCTIONS ARE LOCATED IN user-settings.js.php
$('#edu_email').keyup(function (){
	
	var emailAddress = $('#edu_email').val();
	
	$.ajax({
		data: {process:'validate-email', email: emailAddress},
        url: '<?php echo $processFile;?>',
        success: function(data) {
            
			if((data.indexOf('alert-danger') > -1) == true){
				$('#validate-email-btn').html('');
			}else{
				$('#validate-email-btn').html('<button type="button" class="btn btn-sm btn-info" style="margin-top:5px;" onclick="validateEduEmail(\'edu_email\');">Validate Email</button>');
				
			}
			
			$('#error_edu_email').html(data);
			
        }
    
    });
	
});

function validateEduEmail(element){
	
	var eduEmail = $('#'+element).val();
	
	$.ajax({
        type: "POST", /*form method*/
        data: {process:'send-edu-validation-email',email:eduEmail}, 
        url: "<?php echo $processFile;?>", /*location of php processing code, Note: this uses a process variable to distinguish between different processes*/
        
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
        data: {process:'edu-verify-code',code:emailCode}, 
        url: "<?php echo $processFile;?>", /*location of php processing code, Note: this uses a process variable to distinguish between different processes*/
        
		success: function(data)
        {
			//Change the submit button to display that the settings have been saved
			if((data.indexOf('email-is-valid') > -1) == true){
				$('#edu-email-check').html(data);
				
				window.location.href = '/?page=10-00-00-002&account=1&tab=membership&message=edu-success';
				
			}else{
				$('#error_email_code').html(data);
			}
			
        }
    });
	
}

function cartItems(){
	

	$.ajax({
		data: {process:'cart-items'},
        url: '<?php echo $processFile;?>',
        success: function(data) {
            
			var aCart 		= data.split("|");
			var subTotal	= aCart[1];
			var numItems	= aCart[0];
			
			if(numItems == 0){
				$('#checkout-btn').addClass('hide');	
			}else{
				$('#checkout-btn').removeClass('hide');	
			}
			
			$('.cart-items').html(numItems);
        }
    
    });
		
}



/*$(document).ready(function() {
	cartItems();
});*/

function updateCartTotal(){
	/*$.ajax({
		data: {uid:uid,process:'load-edit-payment-modal'},
        url: '<?php echo $processFile;?>',
        success: function(data) {
            $('#load-edit-method-area').html(data);
			loadEditForm();
        }
    
    });*/
}

//populates the edit payment method modal with the coresponding UID 
function cart(action,productID,term,price){
	
	var funds = [];
	$('#upgradeFunds input:checked').each(function() {
		funds.push($(this).attr('name'));
	});
	
	//Display Cart Loading
	$('#load-cart').html('Loading Cart <img src="images/loading.gif" alt="Loading Cart..." />');
	
	$.ajax({
		data: {action:action,productID:productID,term:term,price:price,process:'cart',funds:funds},
        url: '<?php echo $processFile;?>',
        success: function(data) {
            
			checkoutStep(0);
			
			$('#load-cart').html(data);
			updateCartTotal();
			
			$('#cart').animate({ scrollTop: 0 }, 'slow');
			
			cartItems();
        }
    
    });
}

function checkoutLoadPayments(selectCard){
	$.ajax({
		data: {process:'load-checkout-payment-methods',selectCard:selectCard},
        url: '<?php echo $processFile;?>',
        success: function(data) {
            $('#load-checkout-payments').html(data);
        }
    
    });	
}

function checkoutStep(setStep){
	
	if(setStep == '4'){
		var payMethod = ($("input[name=payment-method]:checked").val());
		
		if(payMethod === undefined){
			var payMethod = 'xx';	
		}
		
		$.ajax({
			data: {process:'load-review-order',payMethodID:payMethod},
			url: '<?php echo $processFile;?>',
			success: function(data) {
								
				$('#load-review-order').html(data);
				
			}
		
		});	
	}
	
	$.ajax({
		data: {process:'checkout-step',setStep:setStep},
        url: '<?php echo $processFile;?>',
        success: function(data) {
            
			var aStepBtn = data.split("|");
			
			//$('#prevStepBtn').html(aStepBtn[0]);
			//$('#nextStepBtn').html(aStepBtn[1]);
			
			$('#load-cart-steps').html(data);
			
        }
    
    });	
}

<?php if($_REQUEST['toggle'] == 1){?>
$(document).ready(function(){
	cart('view','','','');
	$('#cart').modal('toggle');	
});
<?php } ?>



//initialize edit form after its loaded into the page
function loadEditForm2(){
	$('form.edit-method-form').on('submit', function() {
		//Make an ajax call
		$.ajax({
			type: "POST", /*form method*/
			data: $(".edit-method-form").serialize(), /*This grabs all elements: inputs, checkbox arrays, textareas, etc, and allows them to be called by their 'name' variables with php $_REQUEST (acts like a post)*/
			url: "<?php echo $processFile;?>?process=edit-payment-method", /*location of php processing code, Note: this uses a process variable to distinguish between different processes*/
			
			//after the ajax processes its code successfully, this success function will allow you to get the results from that page if need be. That information is stored in the "data" javascript variable
			success: function(data)
			{
				
				//this checks to see if data contains "danger" if so it outputs the return to a div
				if((data.indexOf("alert-danger") > -1) == true ){
					
					$('#edit-payment-method-error').html(data);
					$('#edit-method').animate({ scrollTop: 0 }, 'slow');
				}else{
					
					loadPaymentMethods();
					
					//Notification
					toastr.options = {
						closeButton: true, /*shows a close button on the popup*/
						debug: false,
						positionClass: 'toast-top-right', //this is the position of the popup
						timeOut: '3000' //miliseconds of how long to display popup
					};
					
					//Display notifiaction to browser  
					toastr.success('Payment Method Updated!'); //Notification text in between quotes
					
					//close the modal
					$('#edit-method').modal('toggle');
					
				}
		 
			}
		});
		
		//This prevents the form from submiting like a normal forum, IE: keeps you from leaving the page
		return false;
	});
}

function loadDiscountForm(){
	$('form.discount-form').on('submit', function() {
		//Make an ajax call
		$.ajax({
			type: "POST", /*form method*/
			data: $(".discount-form").serialize(), 
			url: "<?php echo $processFile;?>?process=apply-discount", 
			
			success: function(data)
			{
				$('#discount-area').html(data);
				$('#discount-code').val('');
			}
		});
		
		return false;
	});
}

function loadDiscounts(){
	$.ajax({
		type: "POST", /*form method*/
		data: {loadDiscounts:1}, 
		url: "<?php echo $processFile;?>?process=apply-discount", 
		
		success: function(data)
		{
			$('#discount-area').html(data);
		}
	});	
}

function clearDiscounts(){
	$.ajax({
		type: "POST", /*form method*/
		data: {process:'clear-discounts'}, 
		url: "<?php echo $processFile;?>", 
		
		success: function(data)
		{
			$('#discount-area').html(data);
		}
	});		
}

function checkout(){
	
	$('#load-cart').html('Proceeding to checkout <img src="images/loading.gif" alt="Proceeding to checkout..." />');
	
	$.ajax({
		data: {process:'checkout'},
        url: '<?php echo $processFile;?>',
        success: function(data) {
            $('#cart-header').html('Checkout');
			$('#load-cart').html(data);
			
			$('#clear-cart').addClass('hide');
			
			loadDiscountForm();
			loadDiscounts();
			checkoutLoadPayments('default');
			checkoutStep('2');
			
			$('#cart').animate({ scrollTop: 0 }, 'slow');
			
        }
    
    });
		
}

function refreshPage(){
	//alert('hello');
	window.location.href = "<?php echo $_SESSION['site_root'];?>?page=10-00-00-002&account=1&tab=membership";
}

function placeOrder(){
	$.ajax({
		type: "POST", /*form method*/
		data: $("#submit-cart-order").serialize(), /*This grabs all elements: inputs, checkbox arrays, textareas, etc, and allows them to be called by their 'name' variables with php $_REQUEST (acts like a post)*/
		url: "<?php echo $processFile;?>?process=submit-cart-order", /*location of php processing code, Note: this uses a process variable to distinguish between different processes*/
		
		//after the ajax processes its code successfully, this success function will allow you to get the results from that page if need be. That information is stored in the "data" javascript variable
		success: function(data)
		{
			
			//this checks to see if data contains "danger" if so it outputs the return to a div
			if((data.indexOf("alert-danger") > -1) == true ){
				
				$('#load-order-processing-errors').html(data);
				//$('#edit-method').animate({ scrollTop: 0 }, 'slow');
			}else{
				
				$('#cart-header').html('Confirmation');
				
				$('#load-cart').html(data);
				
				$('#load-cart-steps').html('<button type="button" class="btn btn-default" onClick="refreshPage();">Close</button>');
				
			}
	 
		}
	});
}



//Billing history table

</script>