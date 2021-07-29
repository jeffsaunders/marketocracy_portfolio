<script>
<?php

if($pageID == "08-01-00-001"){ //OPEN NEW TICKET
?>
//+---------------------------------------------------------------------------------------------+
//|								OPEN NEW Ticket Page Scripts									|
//+---------------------------------------------------------------------------------------------+
//When Support Ticket Open Page loads, select General Help Lis
$(document).ready(getLists = function() {
	  if (window.XMLHttpRequest){ // Code for IE7+, Firefox, Opera, Webkit (Chrome, Safari, Rockmelt)
		  xmlhttp=new XMLHttpRequest();
	  }else{ // Code for IE6, IE5
		  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	  }
	  
	  //alert( this.value ); // or $(this).val()
	  var params = '3';//3 = list_id on table:system_lists
	  
	  xmlhttp.open("GET","/process/ajax/system-support-processes.php?process=1&listID="+params);
	  xmlhttp.send();
	  xmlhttp.onreadystatechange = function(){
		  if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
			  document.getElementById("ticket_list").innerHTML = xmlhttp.responseText;
			  //notifications();
		  }
	
	  }
});

//When user selects a different ticket type, get new system_list_options 
$('#ticket_type').on('change', function() {
  if (window.XMLHttpRequest){ // Code for IE7+, Firefox, Opera, Webkit (Chrome, Safari, Rockmelt)
	  xmlhttp=new XMLHttpRequest();
  }else{ // Code for IE6, IE5
	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
  
  //alert( this.value ); // or $(this).val()
  var params = this.value;
  
  xmlhttp.open("GET","/process/ajax/system-support-processes.php?process=1&listID="+params);
  xmlhttp.send();
  xmlhttp.onreadystatechange = function(){
	  if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
		  document.getElementById("ticket_list").innerHTML = xmlhttp.responseText;
		  //notifications();
	  }

  }
});

function validateForm(){
	var a=document.forms["support"]["subject"].value;
	var b=document.forms["support"]["description"].value;
	var c=document.forms["support"]["problem_type"].value;
	var d=document.forms["support"]["problem_type"].value;
	
	if (a==null || a==""){
  		$('#subject').css({
			"border-color": "#b94a48",
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
	if (c==null || c==""){
  		$('#problem_type').css({
			"border-color": "#b94a48",
			"border-width": "1px",
			"border-style": "solid"	
		});
  	}
	if (d==null || d==""){
  		$('#stock_ticker').css({
			"border-color": "#b94a48",
			"border-width": "1px",
			"border-style": "solid"	
		});
  	}
	
	if (a!=""){
  		$('#subject').css({
			"border-color": "#e5e5e5",
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
	if (c!=""){
  		$('#problem_type').css({
			"border-color": "#e5e5e5",
			"border-width": "1px",
			"border-style": "solid"	
		});
  	}
	if (d!=""){
  		$('#stock_ticker').css({
			"border-color": "#e5e5e5",
			"border-width": "1px",
			"border-style": "solid"	
		});
  	}
	
}

//Process Form via AJAX
$('form.ajax').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $(".ajax").serialize(),
        url: "process/ajax/system-support-processes.php?process=2",
        success: function(data)
        {
			if(data == ""){
				validateForm();
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.info('Support Ticket Successfully Submitted');
				
				window.location.href = "/?page=08-01-00-002";
			}else{
				validateForm();
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.error('Please fill in required fields.');
			};
            //alert(data);
            $('#userError').html(data);
           // $("#userError").html(userChar);
            //$("#userError").html(userTaken);
			


        }
    });
	
	return false;
});
<?php
}//END IF PAGE is 08-01-00-001 : Open New Ticket

if($pageID == "08-01-cc-003" || $pageID == "08-01-cc-903"){//TICKET PAGE || TICKET PAGE ADMIN
?>
//+---------------------------------------------------------------------------------------------+
//|								Individual Ticket Page Scripts									|
//+---------------------------------------------------------------------------------------------+
function validateFormReply(){
	var a=document.forms["support-reply"]["reply"].value;
		
	if (a==null || a==""){
  		$('#reply').css({
			"border-color": "#b94a48",
			"border-width": "1px",
			"border-style": "solid"	
		});
  	}
	
	
	if (a!=""){
  		$('#reply').css({
			"border-color": "#e5e5e5",
			"border-width": "1px",
			"border-style": "solid"	
		});
  	}
	
	
}


var replyRefresh = <?php echo $_SESSION['support_ticket_refresh']; ?>;

function refreshResponses(){
	responses();	
}

$(document).ready(responses = function() {
    //Updates the responses on a ticket when a member posts a reply or clicks on the refresh handle
	$.ajax({
      url: 'process/ajax/system-support-processes.php?process=4&ticket=<?php echo $ticketID;?>',
      success: function(data) {
        $('#response-ajax').html(data);
      }
    
    });
});
var refreshInterval = setInterval(responses, replyRefresh);

//Process Reply Form via AJAX
$('form.ajax-reply').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $(".ajax-reply").serialize(),
        url: "process/ajax/system-support-processes.php?process=3",
        success: function(data)
        {
			if(data == ""){
				validateFormReply();
				responses();
				
				var ticketID=document.forms["support-reply"]["ticket_id"].value;
				var ticketStatusVar=document.forms["support-reply"]["ticket_status"].value;

				//When a member re-opens a ticket from "View Ticket" page; change to open
				if(ticketStatusVar == "0"){
					ticketStatus(ticketID, "1", "reopen");
					//alert('Ticket Status is 0');
				}
				
				//When a member replys to a "awaiting reply" status; change to open
				if(ticketStatusVar == "4"){
					ticketStatus(ticketID, "1", "awaiting-reply");
					//alert('Ticket Status is 4');
				}
				
				$('#reply').val('');
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.info('Response Successfully Submitted');
			}else{
				validateFormReply();
				responses();
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.error('Please fill in required fields.');
			};
            //alert(data);
            $('#userError').html(data);
           // $("#userError").html(userChar);
            //$("#userError").html(userTaken);
			


        }
    });
	
	return false;
});

$(document).ready(function(){
	$('#reply').keyup(function() {
		var replyField = document.forms["support-reply"]["reply"].value;
		if(replyField != ""){
			$("#close-ticket").html('Post Reply &amp; Close Ticket');
		}else{
			$("#close-ticket").html('Close Ticket');
		}
	});
});

<?php 
} // END TICKET PAGE

if($pageID == "08-01-cc-903"){ //TICKET PAGE ADMIN
?>
function validateFormNotes(){
	var a=document.forms["support-notes"]["notes"].value;
		
	if (a==null || a==""){
  		$('#notes').css({
			"border-color": "#b94a48",
			"border-width": "1px",
			"border-style": "solid"	
		});
  	}
	
	
	if (a!=""){
  		$('#notes').css({
			"border-color": "#e5e5e5",
			"border-width": "1px",
			"border-style": "solid"	
		});
  	}
	
	
}

//Process Ticket Notes Form via AJAX
$('form.ajax-notes').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $(".ajax-notes").serialize(),
        url: "process/ajax/system-support-processes.php?process=6",
        success: function(data)
        {
			if(data == ""){
				validateFormNotes();
				
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.success('<i class="icon-pencil"></i> Notes Saved');
			}else{
				validateFormNotes();
				
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.error('Please fill in required fields.');
			};
            $('#userErrorNotes').html(data);
        }
    });
	
	return false;
});

<?php	
}// END TICKET PAGE ADMIN
?>


function ticketStatus(ticketID, status, updateSource){
	//Process ajax with passed paramiters
	var replyField = document.forms["support-reply"]["reply"].value;
	
	if(status == "0" && updateSource == "view-ticket-close" && replyField != ""){
		$.ajax({
			type: "POST",
			data: $(".ajax-reply").serialize(),
			url: "process/ajax/system-support-processes.php?process=3",
			success: function(data)
			{
				if(data == ""){
					validateFormReply();
					responses();
					
					var ticketID=document.forms["support-reply"]["ticket_id"].value;
					var ticketStatusVar=document.forms["support-reply"]["ticket_status"].value;
	
					//When a member re-opens a ticket from "View Ticket" page; change to open
					if(ticketStatusVar == "0"){
						ticketStatus(ticketID, "1", "reopen");
						//alert('Ticket Status is 0');
					}
					
					//When a member replys to a "awaiting reply" status; change to open
					if(ticketStatusVar == "4"){
						ticketStatus(ticketID, "1", "awaiting-reply");
						//alert('Ticket Status is 4');
					}
					
					$('#reply').val('');
					//Notification
					toastr.options = {
						closeButton: true,
						debug: false,
						positionClass: 'toast-top-right',
						timeOut: '4000'
					};
					  
					toastr.info('Response Successfully Submitted');
				}else{
					validateFormReply();
					responses();
					//Notification
					toastr.options = {
						closeButton: true,
						debug: false,
						positionClass: 'toast-top-right',
						timeOut: '4000'
					};
					  
					toastr.error('Please fill in required fields.');
				};
				//alert(data);
				$('#userError').html(data);
			   // $("#userError").html(userChar);
				//$("#userError").html(userTaken);
				
	
	
			}
		});
	}
	
	$.ajax({
		url: 'process/ajax/system-support-processes.php?process=5&ticket=' + ticketID + '&status=' + status,
		success: function(data) {
			//empty var
			var clear = "";
			
			//When member/user clicks "Close Ticket" button on the "View Ticket" page
			if(status == "0" && updateSource == "view-ticket-close"){
				//Without refreshing page update the page to show that the ticket status is CLOSED
				$("#update-status").removeAttr('class').addClass('label label-lg label-danger');
				$("#update-status2").removeAttr('class').addClass('ticket-info-body status-danger');
				$("#update-status2").html('<h5><strong>Closed</strong></h5>');
				$("#update-status").html('<i class="icon-tag"></i> Closed');
				$("#status-update6").html('<span class="label label-danger" id="status-update3">Ticket Is CLOSED. To re-open ticket, post a reply.</span>');
				$("#status-update5").html(clear);
				$("input[name='ticket_status']").val('0');
				
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.info('Ticket Successfully Closed');
			}
		
			//When member/user clicks Submits a reply when ticket is closed
			if(status == "1" && updateSource == "reopen"){
				//Without refreshing page update the page to show that the ticket status is now OPEN
				$("#update-status").removeAttr('class').addClass('label label-lg label-success');
				$("#update-status2").removeAttr('class').addClass('ticket-info-body status-success');
				$("#update-status2").html('<h5><strong>Open</strong></h5>');
				$("#update-status").html('<i class="icon-tag"></i> Open');
				$("#status-update3").html('');
				$("#status-update5").html('<a href="javascript:void(0);" onClick="ticketStatus('+ticketID+', 0, \'view-ticket-close\');" class="btn btn-danger">Close Ticket</a>');
				$('#reply').val('');
				
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.success('Ticket Successfully Re-opened');
			}
		
			//When a member/sure submits a reply when the ticket is "awaiting reply"
			if(updateSource == "awaiting-reply"){
				//Without refreshing page update the page to show that the ticket status is OPEN
				$("#update-status").removeAttr('class').addClass('label label-lg label-success');
				$("#update-status2").removeAttr('class').addClass('ticket-info-body status-success');
				$("#update-status2").html('<h5><strong>Open</strong></h5>');
				$("#update-status").html('<i class="icon-tag"></i> Open');
				$('#reply').val('');
			}
			
			//When admin updates ticket status to close
			if(status == "0" && updateSource == "open-tickets"){
				$("#us-btn1-" + ticketID).removeAttr('class').addClass('btn btn-danger');
				$("#us-btn1-" + ticketID).html('Closed');
				$("#us-btn2-" + ticketID).removeAttr('class').addClass('btn btn-danger dropdown-toggle');
				
				
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.success('Ticket Status Changed Successfully');
			}
			
			//When admin updates ticket status to Open
			if(status == "1" && updateSource == "open-tickets"){
				$("#us-btn1-" + ticketID).removeAttr('class').addClass('btn btn-success');
				$("#us-btn1-" + ticketID).html('Open');
				$("#us-btn2-" + ticketID).removeAttr('class').addClass('btn btn-success dropdown-toggle');
				
				
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.success('Ticket Status Changed Successfully');
			}
			
			//When admin updates ticket status to On Hold
			if(status == "2" && updateSource == "open-tickets"){
				$("#us-btn1-" + ticketID).removeAttr('class').addClass('btn btn-warning');
				$("#us-btn1-" + ticketID).html('On Hold');
				$("#us-btn2-" + ticketID).removeAttr('class').addClass('btn btn-warning dropdown-toggle');
				
				
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.success('Ticket Status Changed Successfully');
			}
			
			//When admin updates ticket status to Under Review
			if(status == "3" && updateSource == "open-tickets"){
				$("#us-btn1-" + ticketID).removeAttr('class').addClass('btn btn-warning');
				$("#us-btn1-" + ticketID).html('Under Review');
				$("#us-btn2-" + ticketID).removeAttr('class').addClass('btn btn-warning dropdown-toggle');
				
				
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.success('Ticket Status Changed Successfully');
			}
			
			//When admin updates ticket status to Awaiting Response
			if(status == "4" && updateSource == "open-tickets"){
				$("#us-btn1-" + ticketID).removeAttr('class').addClass('btn btn-warning');
				$("#us-btn1-" + ticketID).html('Awaiting Response');
				$("#us-btn2-" + ticketID).removeAttr('class').addClass('btn btn-warning dropdown-toggle');
				
				
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.success('Ticket Status Changed Successfully');
			}
			
			/*//GENERIC UPDATE TICKET STATUS TO OPEN
			if(status == "1"){
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.success('Ticket Successfully Re-opened');
			}
			
			//GENERIC UPDATE TICKET STATUS TO CLOSED
			if(status == "0" && updateSource == ""){
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.info('Ticket Successfully Closed');
			}*/
		
		}//end success ajax
    
    });//end ajax call
}//end function ticketStatus()

function ticketView(ticketID, user){
	//Process ajax with passed paramiters
	
	$.ajax({
		url: 'process/ajax/system-support-processes.php?process=7&ticket=' + ticketID + '&user=' + user,
		success: function(data) {
		//alert(ticketID);
		alert(ticketID + '|' + user);
		}//end success ajax
    
    });//end ajax call
}//end function ticketStatus()

</script>