<?php
/*
Marketocracy Inc. | Beta Development
Admin General HTML

Supporting files:
	AJAX		- process/ajax/admin-general-processes.php
	JAVASCRIPT	- THIS DOCUMENT JAVASCRIPT includes/scripts/admin-general.js.php
	HTML		- includes/pages/admin-general.php
*/
?>

<script type="text/javascript">


	
$('.goTo').on('click',function(ev){
	
	
	ev.preventDefault();
	
	var targetElement = $(this).attr('href');
	
	//alert(targetElement);
	
	$('html, body').animate(
		{ scrollTop: $('#'+targetElement).offset().top -80 }, 
	500);  
});
	



function setModalHeight(){
	
	var height = $(window).height() - 200;
	
	//alert(height);
	
	$('.set-modal-height').css('height', height);
		
}

function loadPromoteManagerForm(){
	
	//alert('loading-form');
	
	$('form.promote-manager').on('submit', function() {
	
		$('#promote-errors').html('<img src="/images/ajax-loading.gif" alt="Loading" />');
		
		$.ajax({ 
			type: "POST",
			data: $(".promote-manager").serialize(),
			url: "process/ajax/admin-member-lookup-processes.php?process=promote-manager",
			success: function(data)
			{
				$('#promote-errors').html(data);
				location.reload();
			}
		});
		
		return false;
	});
	
}


function promoteManager(memberID){
	
	$.ajax({
		type: "POST", 
		data: {process:'load-promote-manager',memberID:memberID},
		url: "process/ajax/admin-member-lookup-processes.php",
			
		success: function(data)
		{
			//alert(data);
			$('#load-promote-info').html(data);	
			loadPromoteManagerForm();
		}
	});
	
}



function loadMemberInfo(memberID){
	
	$.ajax({
		type: "POST", 
		data: {process:'load-member-info',memberID:memberID},
		url: "process/ajax/admin-member-lookup-processes.php",
			
		success: function(data)
		{
			//alert(data);
			$('#load-member-data').html(data);	
			setModalHeight();		
	 
		}
	});
	
}

function loadPwCheck(){
	
	$('#new-pw').keyup(function() {
	
		var newPW 		= $('#new-pw').val();
			username	= $('#username').val();
			
		$.ajax({
			type: "POST", 
			url: "process/ajax/score-password.php?username="+username+"&password="+newPW,
			
			success: function(data)
			{
				//alert(data);
				$('#password-score').html(data);			
		 
			}
		});
		
	});
		
}

function loadRepriceForm(){

	$('form.reprice-fund').on('submit', function() {
	
		$('#display-reprice-info').html('<img src="/images/ajax-loading.gif" alt="Loading" />');
		
		$.ajax({ 
			type: "POST",
			data: $(".reprice-fund").serialize(),
			url: "process/ajax/admin-member-lookup-processes.php?process=reprice-fund",
			success: function(data)
			{
				$('#display-reprice-info').html(data);
				
			}
		});
		
		return false;
	});
	
}

function loadEmailForm(){
	
	$('form.email-form').on('submit', function() {
	
		$('#email-warnings').html('<img src="/images/ajax-loading.gif" alt="Loading" />');
		
		$.ajax({ 
			type: "POST",
			data: $(".email-form").serialize(),
			url: "process/ajax/admin-member-lookup-processes.php?process=change-email",
			success: function(data)
			{
				
				
				if((data.indexOf("alert-danger") > -1) == true ){
					
					$('#email-warnings').html(data);
					
				}else{
					//$('#email-warnings').html(data);
					location.reload();
					
				}
				
			}
		});
		
		return false;
	});
			
}

function loadUsernameForm(){
	
	$('form.username-form').on('submit', function() {
	
		$('#email-warnings').html('<img src="/images/ajax-loading.gif" alt="Loading" />');
		
		$.ajax({ 
			type: "POST",
			data: $(".username-form").serialize(),
			url: "process/ajax/admin-member-lookup-processes.php?process=change-username",
			success: function(data)
			{
				
				
				if((data.indexOf("alert-danger") > -1) == true ){
					
					$('#username-warnings').html(data);
					
				}else{
					//$('#email-warnings').html(data);
					location.reload();
					
				}
				
			}
		});
		
		return false;
	});
			
}

function loadPasswrodForm(){
	
	$('form.password-form').on('submit', function() {
	
		$('#password-warnings').html('<img src="/images/ajax-loading.gif" alt="Loading" />');
		
		$.ajax({ 
			type: "POST",
			data: $(".password-form").serialize(),
			url: "process/ajax/admin-member-lookup-processes.php?process=change-password",
			success: function(data)
			{
				
				
				if((data.indexOf("alert-danger") > -1) == true ){
					
					$('#password-warnings').html(data);
					
				}else{
					//$('#email-warnings').html(data);
					location.reload();
					
				}
				
			}
		});
		
		return false;
	});
			
}

function loadTradeHistoryForm(){
	
	$('form.trade-history-form').on('submit', function() {
	
		$('#trade-history-warnings').html('<img src="/images/ajax-loading.gif" alt="Loading" />');
		
		$.ajax({ 
			type: "POST",
			data: $(".trade-history-form").serialize(),
			url: "process/ajax/admin-member-lookup-processes.php?process=trade-history",
			success: function(data)
			{
				
				$('#trade-history-warnings').html(data);
				
			}
		});
		
		return false;
	});
			
}

function loadModal(modalType,memberID,fundID){
	
	$('#load-modal-content').html('<img src="/images/ajax-loading.gif" alt="Loading" />');
	
	$.ajax({ 
        type: "POST",
        data: {process:'load-modal',memberID:memberID,modalType:modalType,fundID:fundID},
        url: "process/ajax/admin-member-lookup-processes.php",
        success: function(data)
        {
			$('#load-modal-content').html(data);
			
			loadRepriceForm();
			loadEmailForm();
			loadPwCheck();
			loadPasswrodForm();
			loadTradeHistoryForm();
			//loadUsernameForm();
        }
    });	
}



$('#new-lookup').on('click', function(){
	
	$.ajax({ 
        type: "POST",
        data: $(".member_lookup").serialize(),
        url: "process/ajax/admin-member-lookup-processes.php?process=member-lookup",
        success: function(data)
        {
			$('#load-member-list').html(data);
			//$('#load-member-info').html(data);
			//$('.clear-input').val('');
			
			//$('#collapse-btn').html('<a class="expand" href="javascript:;"> </a>');
     		//$('#collapse-portlet').css("display", "none");
        }
    });
	
	return false;
	
});

//Process Create Forum Form via AJAX
$('form.member_lookup').on('submit', function() {
	
	$('#load-member-info').html('<img src="/images/ajax-loading.gif" alt="Loading" />');
	
	$.ajax({ 
        type: "POST",
        data: $(".member_lookup").serialize(),
        url: "process/ajax/admin-member-lookup-processes.php?process=member-lookup-new",
        success: function(data)
        {
			//$('#load-member-info').html(data);
			$('#load-member-list').html(data);
			//$('.clear-input').val('');
			
			//$('#collapse-btn').html('<a class="expand" href="javascript:;"> </a>');
     		//$('#collapse-portlet').css("display", "none");
        }
    });
	
	return false;
});




<?php if(!empty($lookupMemberID) || !empty($lookupUsername) || !empty($lookupEmail)){
	?>
	$(document).ready(function() {
		$('#submit-lookup').click();
	});
	<?php
}
?>


</script>