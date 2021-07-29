<?php
/*
Marketocracy Inc. | Beta Development
Fund ADMIN JAVASCRIPT

Supporting files:
	AJAX		- process/ajax/fund-admin-processes.php
	JAVASCRIPT	- THIS DOCUMENT JAVASCRIPT includes/scripts/fund-admin.js.php
	HTML		- includes/pages/fund-admin.php
*/
?>
<script>

function validateAdminForum(){
	var a=document.forms["fund_details"]["fund_name"].value;
	var b=document.forms["fund_details"]["description"].value;
		
	if (a==null || a==""){
  		$('#fund_name').css({
			"border-color": "#b94a48",
			"border-width": "1px",
			"border-style": "solid"	
		});
  	}
	
	
	if (a!=""){
  		$('#fund_name').css({
			"border-color": "#e5e5e5",
			"border-width": "1px",
			"border-style": "solid"	
		});
  	}
	
	if (b==null || b==""){
  		$('#description').css({
			"border-color": "#b94a48",
			"border-width": "1px",
			"border-style": "solid"	
		});
  	}
	
	if (b!=""){
  		$('#description').css({
			"border-color": "#e5e5e5",
			"border-width": "1px",
			"border-style": "solid"	
		});
  	}
	
	
}

//Process Create Topic Form via AJAX
$('form.fund_details').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $(".fund_details").serialize(),
        url: "process/ajax/fund-admin-processes.php?process=1",
        success: function(data)
        {
			alert(data);
			if(data == ""){
				validateAdminForum();
				
				
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.info('You have successfully updated your fund settings.');
				
				 window.location = '/?page=03-01-00-002&fund=<?php echo $fundID;?>';
				
			}else{
				validateAdminForum();
				
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.error('Please fill in required fields.');
				
				//alert(data);
            	$('#adminFormError').html(data);
			};
			
            
     
        }
    });
	
	return false;
});

</script>