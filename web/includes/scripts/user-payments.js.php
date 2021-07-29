<?php
/*
Marketocracy Inc. | Beta Development
User Profile/Settings Scripts

Supporting files:
	AJAX			- process/ajax/user-payments-processes.php
	JAVASCRIPT		- THIS DOCUMENT includes/scripts/user-payments.js.php
	HTML			- includes/pages/user-profile.php
	PHP Includes	- THIS DOCUMENT includes/pages/includes/user-profile/payment-info.php
*/

$processFile = 'process/ajax/user-payment-processes.php';

?>
<script type="text/javascript">

function loadStates(country){
	
	$.ajax({
        type: "POST", /*form method*/
        data: {process:'load-states',country:country}, 
        url: "<?php echo $processFile;?>", /*location of php processing code, Note: this uses a process variable to distinguish between different processes*/
        
		success: function(data)
        {
			//Change the submit button to display that the settings have been saved
			$('#load-states').html(data);			
			
        }
    });
		
}

//VALIDATION FUNCTIONS ARE LOCATED IN user-settings.js.php
function showSaveGIF2(element){
	//Create an overlay div to display processing GIF
	$('<div class="remove-process"></div>').css({
        position: "absolute",
        width: "100%",
        height: "100%",
        top: 0,
        left: 0,
        background: "#e2e2e2",
        opacity: 0.4
    }).appendTo($("#"+element).css("position", "relative"));
    
    $('<div class="remove-process"></div>').css({
        position: "absolute",
        width: "100%",
        height: "100%",
        top: 0,
        left: 0,
        background: "url(http://beta.marketocracy.com/images/ajax-loading.gif) no-repeat 50% 50%"
    }).appendTo($("#"+element).css("position", "relative"));	
}

//load cc check
function loadCCCheck(){
	
	
	
	$('#card-number').keyup(function() {
		
		var ccNumber = $('#card-number').val();
		
		var ccLength = ccNumber.length;
		
		if(ccLength < 9){
		
			$.ajax({
				type: "POST", 
				data: {process:'validate-card',ccNumber:ccNumber}, 
				url: "<?php echo $processFile;?>", 
				
				success: function(data){
					
					var info 		= data.split("|");
					
					
					
					var cardImg		= info[0];
					var cardType	= info[1];
					
					if(cardType != 'invalid'){
						$('#show-card-type').html('<h5><strong>Your Card</strong></h5><img width="51" style="margin-top:5px" title="'+cardType+'" alt="'+cardType+'" src="images/payments/'+cardImg+'" />');
					}else{
						$('#show-card-type').html('<h5>Invalid Card Number</h5>');	
					}
					
				}
			});
		}
	});
}

//load payment methods after they have been edited
function loadPaymentMethods(){
	$.ajax({
		data: {process:'load-payment-methods'},
        url: '<?php echo $processFile;?>',
        success: function(data) {
            $('#payment-options').html(data);
        }
    
    });
}

//toggles view of CCV edit field
function showHideCCV(action){
	if(action == 'show'){
		$('#ccv').removeClass('hide');	
		$('#ccv-link').addClass('hide');
	}else if(action == 'hide'){
		$('#ccv').addClass('hide');	
		$('#ccv-link').removeClass('hide');
		$('#new_ccv').val('');
	}
}

//populates the edit payment method modal with the coresponding UID 
function editPayment(uid){
	$.ajax({
		data: {uid:uid,process:'load-edit-payment-modal'},
        url: '<?php echo $processFile;?>',
        success: function(data) {
            $('#load-edit-method-area').html(data);
			loadEditForm();
        }
    
    });
}

function newPayment(target){
	
	if(target == 'payment2'){
		loadTarget 	= '#load-new-payment-method2';
		$('#load-new-payment-method').html('');
	}else{
		loadTarget = '#load-new-payment-method';
		$('#load-new-payment-method2').html('');	
	}
	
	$.ajax({
		data: {process:'load-new-payment-modal'},
        url: '<?php echo $processFile;?>',
        success: function(data) {
            $(loadTarget).html(data);
			loadNewForm(target);
			loadCCCheck(target);
			
			
        }
    
    });
}

function deletePayment(uid){
	$.ajax({
		data: {uid:uid,process:'load-delete-payment-modal'},
        url: '<?php echo $processFile;?>',
        success: function(data) {
            $('#load-delete-payment-method').html(data);
			loadDeleteForm();
        }
    	
    });
}

function loadInvoice(uid){
	$.ajax({
		data: {uid:uid,process:'load-invoice-modal'},
        url: '<?php echo $processFile;?>',
        success: function(data) {
            $('#load-invoice').html(data);
        }
    	
    });
}



//Prefill Billing Address function (will prefill the new address fields in the "edit payment method"
function useThisAddress(uid){
	$.ajax({
		data: {uid:uid,process:'use-this-address'},
        url: '<?php echo $processFile;?>',
        success: function(data) {
            $('#new-address-fields').html(data);
			changeAddress('new-address');
        }
    
    });
}


function useThisAddress2(uid){
	
	//show loader
	$('#new-address-fields2').html('<img src="/images/ajax-loading.gif" alt="loading..." />');
	
	$.ajax({
		data: {uid:uid,process:'use-this-address'},
        url: '<?php echo $processFile;?>',
        success: function(data) {
            $('#new-address-fields2').html(data);
			changeAddress2('new-address2');
        }
    
    });
}

//toggle views of address fields and buttons in "Edit Payment Method"
function changeAddress(action){
		
	if(action == 'change'){
		$('#current-billing-address').addClass('hide');
		$('#billing-address-area').removeClass('hide');
	}else if(action == 'cancel'){
		$('#current-billing-address').removeClass('hide');
		$('#billing-address-area').addClass('hide');
		$('#new-address-fields').addClass('hide');
		
		$('#new-address-fields').find('input:text').val('');
		$('#save_new_address').val('0');
	}else if(action = 'new-address'){
		$('#billing-address-area').addClass('hide');
		$('#new-address-fields').removeClass('hide');
		$('#save_new_address').val('1');
	}
	
	
}

//Toggle view of address fields in "add Card"
function changeAddress2(action){
	
	if(action = 'new-address2'){
		$('#billing-address-area2').addClass('hide');
		$('#new-address-fields2').removeClass('hide');
	}

}

//initialize edit form after its loaded into the page
function loadEditForm(){
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




function loadNewForm(target){
	
	if(target == 'payment2'){
		loadTarget 	= '#new-card2';
	}else{
		loadTarget = '#new-card';	
	}
	
	$('form.new-payment-form').on('submit', function() {
		//Make an ajax call
		$.ajax({
			type: "POST", /*form method*/
			data: $(".new-payment-form").serialize(), /*This grabs all elements: inputs, checkbox arrays, textareas, etc, and allows them to be called by their 'name' variables with php $_REQUEST (acts like a post)*/
			url: "<?php echo $processFile;?>?process=new-payment-method", /*location of php processing code, Note: this uses a process variable to distinguish between different processes*/
			
			//after the ajax processes its code successfully, this success function will allow you to get the results from that page if need be. That information is stored in the "data" javascript variable
			success: function(data)
			{
				//alert(data);
				//$('#new-payment-method-error').html(data);
				//$('#new-card').animate({ scrollTop: 0 }, 'slow');
				
				//this checks to see if data contains "danger" if so it outputs the return to a div
				if((data.indexOf("alert-danger") > -1) == true ){
					
					$('#new-payment-method-error').html(data);
					$(loadTarget).animate({ scrollTop: 0 }, 'slow');
				}else{
					
					if(target == 'payment2'){
						checkoutLoadPayments('new');	
						loadPaymentMethods();
					}else{
						loadPaymentMethods();
					}
					
				
					//Notification
					toastr.options = {
						closeButton: true, //shows a close button on the popup
						debug: false,
						positionClass: 'toast-top-right', //this is the position of the popup
						timeOut: '3000' //miliseconds of how long to display popup
					};
					
					//Display notifiaction to browser  
					toastr.success('Payment Method Updated!'); //Notification text in between quotes
					
					//close the modal
					$(loadTarget).modal('toggle');
					
				}
		 
			}
		});
		
		//This prevents the form from submiting like a normal forum, IE: keeps you from leaving the page
		return false;
	});
}

function loadDeleteForm(){
	$('form.delete-method-form').on('submit', function() {
		//Make an ajax call
		$.ajax({
			type: "POST", /*form method*/
			data: $(".delete-method-form").serialize(), /*This grabs all elements: inputs, checkbox arrays, textareas, etc, and allows them to be called by their 'name' variables with php $_REQUEST (acts like a post)*/
			url: "<?php echo $processFile;?>?process=delete-payment-method", /*location of php processing code, Note: this uses a process variable to distinguish between different processes*/
			
			//after the ajax processes its code successfully, this success function will allow you to get the results from that page if need be. That information is stored in the "data" javascript variable
			success: function(data)
			{
				
				//this checks to see if data contains "danger" if so it outputs the return to a div
				if((data.indexOf("alert-danger") > -1) == true ){
					
					$('#delete-method-error').html(data);
					$('#delete-method').animate({ scrollTop: 0 }, 'slow');
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
					toastr.success('Payment Method Deleted Successfully'); //Notification text in between quotes
					
					//close the modal
					$('#delete-method').modal('toggle');
					
				}
		 
			}
		});
		
		//This prevents the form from submiting like a normal forum, IE: keeps you from leaving the page
		return false;
	});
}

//Billing history table
var billingHistory = function () {

     var initBillingHistory = function() {
        var oTable = $('#billing_history_table').dataTable( {           
            "aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[0, 'desc']],
             "aLengthMenu": [
                [25, 50, 75, -1],
                [25, 50, 75, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 25,
        });

        jQuery('#billing_history_table_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#billing_history_table_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#billing_history_table_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#billing_history_table_column_toggler input[type="checkbox"]').change(function(){
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

            initBillingHistory();
        }

    };

}();

$(document).ready(function() {
	billingHistory.init();
});
</script>