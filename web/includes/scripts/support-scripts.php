<script>
//+---------------------------------------------------------------------------------------------------+
//						FORM FIELD VALIDATION FUNCTION :  *Field Required 

	//	eID = input element HTML ID, 
	//	eIDs = javascript array of input elementIDs(for hiding and displaying the submit button), 
	//	submitID = HTML ID of submit button
//+----------------------------------------------------------------------------------------------------+
function validateField(eID, eIDs, submitID){
	
	
	//Check to see if the eID has the wysihtml5 editor tag
	var check = (eID.indexOf('-editor') > -1);
	
	if(check == false){
		var fieldVal = $('#'+eID).val();
	}else{
		//Get Value from editor
		var fieldVal = $('#'+eID).data("wysihtml5").editor.getValue();
		//alert(fieldVal);
		//remove Editor Identifier from element id
		eID = eID.replace('-editor', '');
	}
	
	//Remove all spaces to make sure field is not empty
	fieldVal = fieldVal.replace(/ /g,'');
	
	if(fieldVal == ''){
		//If filed is empty show error
		$('#'+eID).css({
			"border-color": "#b94a48",
			"border-width": "1px",
			"border-style": "solid"	
		});
		
		$('#'+eID+'-help').html('<span style="color:#b94a48;">Please provide field.</span>');	
	}else{
		//If field is not empty remove error
		$('#'+eID).css({
			"border-color": "#e5e5e5",
			"border-width": "1px",
			"border-style": "solid"	
		});
		$('#'+eID+'-help').html('');
		
	}
	
	var index;
	var cnt = 0;
	
	//Check the eIDs array as a whole for an empty value to show/hide the submit button
	for (index = 0; index < eIDs.length; index++){
		
		//Check for editor tag
		var check = (eIDs[index].indexOf('-editor') > -1);
		//alert(check);
		//Get value of field
		if(check == false){
			var val = $('#'+eIDs[index]).val();
			
			
		}else{
			//var val = editor.getValue();
			var val = $('#'+eIDs[index]).data("wysihtml5").editor.getValue();
			//alert(val);
		}
		
			
		//If value is blank increase the counter by 1
		if(val == ''){
			cnt++;	
		}
	}
	
	//If the counter is greater than zero, there are blank fields
	if(cnt == 0){
		//no blank fields show submit button
		//$('#'+submitID).css('display', '');	
		$('#'+submitID).css('background', '#5CB85C');
		$('#'+submitID).css('border-color', '#398439');
		$('#'+submitID).css('color', '#FFFFFF');
		$('#'+submitID).attr('disabled', false);
		//alert(cnt);
		
	}else{
		
		//blank fields, hide submit button
		//alert(cnt);
		//$('#'+submitID).css('display', 'none');
		$('#'+submitID).css('border-color', '#666666');
		$('#'+submitID).css('color', '#000000');
		$('#'+submitID).attr('disabled', true);
	}
	
}// End Validate Field Function

function loadProblemList(subType, stockTicker){
	
	var listID = $('#ticket_type2').val();
	
	$.ajax({ 
        type: "POST",
        data: {process: '1', listID: listID, subType: subType, stock: stockTicker},
        url: "process/ajax/support-processes.php",
        success: function(data)
        {
			$('#ticket_list2').html(data);
			
        }
    });
		
}

$(document).ready(function() {
		loadProblemList('<?php echo $ticket['problem_type'];?>','<?php echo $ticket['stock_ticker'];?>');
});

<?php
if($pageID == "08-01-00-001" || $pageID == "08-02-00-001"){ //OPEN NEW TICKET

if($_REQUEST['subtype'] != ''){
	$filter = '&subType='.$_REQUEST['subtype'];	
}
?>
//+---------------------------------------------------------------------------------------------+
//|								OPEN NEW Ticket Page Scripts									|
//+---------------------------------------------------------------------------------------------+
function checkTicketType(){
	
	ticketType = $('#ticket_type').val();
	
	//alert(ticketType);
	
	if(ticketType == '2' /*corporate Action*/){
		$('#ticket-type-alert').html('<div class="note note-warning"><p><strong>Attention:</strong> If you are reporting on a corporate action that is supposed to be effective as of today. Please wait untill tomorrow before submitting a ticket.</p></div>');	
	}else{
		$('#ticket-type-alert').html('');	
	}
		
}

<?php if($pageID == "08-01-00-001"){?>
//When Support Ticket Open Page loads, select General Help Lis
$(document).ready(getLists = function() {
	  if (window.XMLHttpRequest){ // Code for IE7+, Firefox, Opera, Webkit (Chrome, Safari, Rockmelt)
		  xmlhttp=new XMLHttpRequest();
	  }else{ // Code for IE6, IE5
		  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	  }
	  
	  //alert( this.value ); // or $(this).val()
	  var params = '<?php echo $listType;?>';//3 = list_id on table:system_lists
	  
	  xmlhttp.open("GET","/process/ajax/support-processes.php?process=1&listID="+params+"<?php echo $filter;?>");
	  xmlhttp.send();
	  xmlhttp.onreadystatechange = function(){
		  if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
			  document.getElementById("ticket_list").innerHTML = xmlhttp.responseText;
			  checkTicketType();
		  }
	
	  }
	  
});

<?php } ?>

//When user selects a different ticket type, get new system_list_options 
$('#ticket_type').on('change', function() {
  if (window.XMLHttpRequest){ // Code for IE7+, Firefox, Opera, Webkit (Chrome, Safari, Rockmelt)
	  xmlhttp=new XMLHttpRequest();
  }else{ // Code for IE6, IE5
	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
  
  //alert( this.value ); // or $(this).val()
  var params = this.value;
  
  xmlhttp.open("GET","/process/ajax/support-processes.php?process=1&listID="+params);
  xmlhttp.send();
  xmlhttp.onreadystatechange = function(){
	  if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
		  document.getElementById("ticket_list").innerHTML = xmlhttp.responseText;
		  //notifications();
		  checkTicketType();
		  if(params == "2"){
			 $('#desc-help').html('Please provide as much detail as possible relating to your Corporate Action request. Please note that information you provide here will be visible to the community.'); 
			 $('#comment-help').html('If you have additional comments you would like to keep private, please provide them here.');
			 $('#addAnonymous').attr('style', '');
		  }else{
			 $('#desc-help').html(''); 
			 $('#comment-help').html('');
			 $('#addAnonymous').css('display', 'none');
		  }
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
        url: "process/ajax/support-processes.php?process=2",
        success: function(data)
        {
			if(data == "" || data == "ca" ){
				validateForm();
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.info('Support Ticket Successfully Submitted');
				
				if(data == "ca"){
					//if ticket is ca, go to ca open tickets
					window.location.href = "/?page=08-02-00-002";
				}else{
					//if ticket is not ca, go to all open tickets
					window.location.href  = "/?page=08-01-00-002";	
				}
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




var replyRefresh = <?php echo $_SESSION['support_ticket_refresh']; ?>;

$(document).ready(responses = function() {
    //Updates the responses on a ticket when a member posts a reply or clicks on the refresh handle
	$.ajax({
      url: 'process/ajax/support-processes.php?process=4&ticket=<?php echo $ticketID;?>',
      success: function(data) {
        $('#response-ajax').html(data);
      }
    
    });
});
var refreshInterval = setInterval(responses, replyRefresh);


function refreshResponses(){
	responses();	
}


function updateFeedback(uid,action){
	
	$.ajax({
		data: {process:'update-feedback',uid:uid,action:action},
      	url: 'process/ajax/support-processes.php',
      	success: function(data) {
        	responses();
      	}
    
    });
		
}
/*function validateFormReply(){
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
	
	
}*/

<?php 
if($_SESSION['admin'] == "1"){
	$admin = "1";	
}else{
	$admin = "0";	
}
?>

var eIDs2 = ["reply-editor"];
//alert(eIDs2);
$('#reply-editor').wysihtml5({
	events: {
		load: function() {
			var some_wysi = $('#reply-editor').data('wysihtml5').editor;
			$(some_wysi.composer.element).bind('keyup', function(){ 
				validateField('reply-editor', eIDs2, 'submit-reply');
			});
		}
	}
});

//Process Reply Form via AJAX
$('form.ajax-reply').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $(".ajax-reply").serialize(),
        url: "process/ajax/support-processes.php?process=3&admin=<?php echo $admin;?>",
        success: function(data)
        {
			if(data == ""){
				//validateFormReply();
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
				
				$('#reply-editor').data("wysihtml5").editor.clear();
			}else{
				//validateFormReply();
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
     
        }
    });
	
	return false;
});

$(document).ready(function(){
	$('#reply-editor').keyup(function() {
		var replyField = document.forms["support-reply"]["reply-editor"].value;
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
$(document).ready(function() {
	var options = { 
			target: '#load-files',   // target element(s) to be updated with server response 
			beforeSubmit: beforeSubmit,  // pre-submit callback 
			success: afterSuccess,  // post-submit callback 
			resetForm: true        // reset the form after successful submit 
		}; 
		
	 $('.upload-text-file').submit(function() { 
		$(this).ajaxSubmit(options);  			
		// always return false to prevent standard browser submit and page navigation 
		return false; 
	}); 
	
	function afterSuccess(){
		
	}
	function beforeSubmit(){
		
	}
});

$(document).ready(function() {
	var options = { 
			target: '#load-files2',   // target element(s) to be updated with server response 
			beforeSubmit: beforeSubmit,  // pre-submit callback 
			success: afterSuccess,  // post-submit callback 
			resetForm: true        // reset the form after successful submit 
		}; 
		
	 $('.upload-file').submit(function() { 
		$(this).ajaxSubmit(options);  			
		// always return false to prevent standard browser submit and page navigation 
		return false; 
	}); 
	
	function afterSuccess(){
		
	}
	function beforeSubmit(){
		
	}
});
/*$('form.upload-file').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $(".upload-text-file").serialize(),
        url: "process/ajax/support-processes.php?process=upload-file",
        success: function(data)
        {
			$('#upload-errors').html(data);
     
        }
    });
	
	return false;
});*/

/*function validateFormNotes(){
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
	
	
}*/

var eIDs3 = ["notes-editor"];

$('#notes-editor').wysihtml5({
	events: {
		load: function() {
			var some_wysi = $('#notes-editor').data('wysihtml5').editor;
			$(some_wysi.composer.element).bind('keyup', function(){ 
				validateField('notes-editor', eIDs3, 'submit-notes');
			});
		}
	}
});

//Process Ticket Notes Form via AJAX
$('form.ajax-notes').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $(".ajax-notes").serialize(),
        url: "process/ajax/support-processes.php?process=6",
        success: function(data)
        {
			if(data == ""){
				//validateFormNotes();
				
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.success('<i class="icon-pencil"></i> Notes Saved');
			}else{
				//validateFormNotes();
				
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


//START COMMUNITY TICKET PAGE
if($pageID == "04-02-cc-003" || $pageID == "08-01-cc-903"){//COMMUNITY TICKET PAGE
?>
//+-------------------------------------------------------------------------------------------------------------+
//|											Community Ticket Page Scripts										|
//+-------------------------------------------------------------------------------------------------------------+

<?php if($ticket['ticket_type'] == "2"){ //CA's?>
//Get refresh variable from system config settings. | This is set in the database: table: nova > system_config
var replyRefresh = <?php echo $_SESSION['support_ticket_refresh']; ?>;

//When document loads load CA Responses via ajax
$(document).ready(communityResponses = function() {
    //Updates the responses on a ticket when a member posts a reply or clicks on the refresh handle
	$.ajax({
      url: 'process/ajax/support-processes.php?process=10&ticket=<?php echo $ticketID;?>',
      success: function(data) {
        $('#community-response-ajax').html(data);
      }
    
    });
});

//Refresh the communityResponses by the interval determinded by the config setting
var refreshInterval = setInterval(communityResponses, replyRefresh);
<?php } ?>


//+---------------------------
//| START Page wide functions
//+---------------------------

//Refresh button | refreshes communityResponses
function refreshResponses(){
	communityResponses();	
}

//Show Reply Field Button | When Reply button on response is clicked, the reply field will be shown.
function showReply(uid){
	//alert(uid);
	$('#replyForm-' + uid).attr('style', '');
}

//When the cancel button is pressed on the reply field, the reply field will be hidden
function hideReply(uid){
	$('#replyForm-' + uid).attr('style', 'display:none;');	
}

//Function to control the Agree/Disagree Buttons
function agreeDisagree(choice, uid, member, ticket){
	$.ajax({
      url: 'process/ajax/support-processes.php?process=11&uid=' + uid + '&choice=' + choice + '&member=' + member + '&ticket=' + ticket,
      success: function(data) {
        //alert(data);
		//$('#response-ajax').html(data);
		communityResponses();
		
		if(data != ""){
			//Notification
			toastr.options = {
				closeButton: true,
				debug: false,
				positionClass: 'toast-top-right',
				timeOut: '1500'
			};
			  
			toastr.info(data);
		}
      }
    
    });
}

//This function deletes or restores a reply
function deleteReply(uid, action){
	
	if(action == 'delete'){
		var check=confirm("Are you sure you wish to remove this reply?");
	}else if(action == 'restore'){
		check=confirm("Are you sure you wish to restore this reply?");
	}
	
	if(check == true){
		//DEBUG
		//alert("Deleted");
		$.ajax({
			url: 'process/ajax/support-processes.php?process=13&uid=' + uid + '&action=' + action,
			success: function(data) {
				communityResponses();
				
				//DEBUG
				//alert(data);
				
				if(data == "deleted"){
					//Notification
					toastr.options = {
						closeButton: true,
						debug: false,
						positionClass: 'toast-top-right',
						timeOut: '2500'
					};
					  
					toastr.warning("Reply was successfully deleted.");
				}
				if(data == "restored"){
					//Notification
					toastr.options = {
						closeButton: true,
						debug: false,
						positionClass: 'toast-top-right',
						timeOut: '2500'
					};
					  
					toastr.success("Reply was successfully restored.");
				}
			}
		
		});
	}else{
		//DEBUG
		//alert("Not Deleted"); 
	}
	
}
//+-------------------------
//| END Page wide functions
//+-------------------------


//+-----------------------------------------------
//| START Corporate Action Community Reply Form 
//| Located at the bottom of the page
//+-----------------------------------------------

/*function validateCommunityReply(){
	var a=document.forms["community-support-reply"]["community-reply"].value;
		
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
	
	
}*/

var eIDs = ["community-reply-editor"];

$('#community-reply-editor').wysihtml5({
	events: {
		load: function() {
			var some_wysi = $('#community-reply-editor').data('wysihtml5').editor;
			$(some_wysi.composer.element).bind('keyup', function(){ 
				validateField('community-reply-editor', eIDs, 'submit-community-reply');
			});
		}
	}
});

//Process Reply Form via AJAX
$('form.community-ajax-reply').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $(".community-ajax-reply").serialize(),
        url: "process/ajax/support-processes.php?process=3&community=1",
        success: function(data)
        {
			if(data == ""){
				validateCommunityReply();
				communityResponses();
				
				/*var ticketID=document.forms["community-support-reply"]["ticket_id"].value;
				var ticketStatusVar=document.forms["community-support-reply"]["ticket_status"].value;*/

				$('#community-reply-editor').val('');
				
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.info('Response Successfully Submitted');
			}else{
				validateCommunityReply();
				communityResponses();
				
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
           
        }
    });
	
	return false;
});

//+-----------------------------------------------------
//| END Corporate Action Community Reply Form 
//+-----------------------------------------------------

//+-----------------------------------------------------
//| START Reply To Reply form (each reply has this form)
//+-----------------------------------------------------
function validateResponseReply(id){
	var a=document.forms["reply-"+id]["reply"].value;
		
	if (a==null || a==""){
		$('#reply-'+id).css({
			"border-color": "#b94a48",
			"border-width": "1px",
			"border-style": "solid"	
		});
	}
	
	
	if (a!=""){
		$('#reply-'+id).css({
			"border-color": "#e5e5e5",
			"border-width": "1px",
			"border-style": "solid"	
		});
	}
	
	
}

//Process Response Reply Form via AJAX
function postReply(uid){
	
	$('form.reply-'+uid).on('submit', function() {
		
		//alert(uid);
		$.ajax({
			type: "POST",
			data: $(".reply-"+uid).serialize(),
			url: "process/ajax/support-processes.php?process=12",
			success: function(data)
			{
				//alert(data);
				if(data == ""){
					validateResponseReply(uid);
					communityResponses();
					
					//Notification
					toastr.options = {
						closeButton: true,
						debug: false,
						positionClass: 'toast-top-right',
						timeOut: '4000'
					};
					  
					toastr.info('Reply Successfully Submitted');
				}else{
					validateResponseReply(uid);
					//Notification
					toastr.options = {
						closeButton: true,
						debug: false,
						positionClass: 'toast-top-right',
						timeOut: '4000'
					};
					  
					toastr.error('Please fill in required fields.');
					
					$('#replyUserError-'+uid).append(data);
				};
				
			}
		});
		
		return false;
	});
}

//+-----------------------------------------------------
//| END Reply To Reply form (each reply has this form)
//+-----------------------------------------------------



//+-----------------------------------------------------
//| START Resolution Form | only visable to ADMINS
//+-----------------------------------------------------
//Jquery Validation for form | This form is also validated via php: process/ajax/support-processes.php | Process 14
function validateResolution(){
	var a=document.forms["support-resolution"]["resolution"].value;
		
	if (a==null || a==""){
		$('#resolution').css({
			"border-color": "#b94a48",
			"border-width": "1px",
			"border-style": "solid"	
		});
	}
	
	
	if (a!=""){
		$('resolution').css({
			"border-color": "#e5e5e5",
			"border-width": "1px",
			"border-style": "solid"	
		});
	}
	
	
}

//Process Reply Form via AJAX and submit to support-processes.php
$('form.ajax-resolution').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $(".ajax-resolution").serialize(),
        url: "process/ajax/support-processes.php?process=14",
        success: function(data)
        {
			if(data == ""){
				
				validateResolution();
				
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				
				  
				toastr.info('Response Successfully Submitted');
				
				location.reload();
			}else{
				//alert(data);
				validateResolution();
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.error('Please fill in required fields.');
				
				$('#userErrorResolution').append(data);
			};
         
        }
    });
	
	return false;
});

//+-----------------------------------------------------
//| END Resolution Form | only visable to ADMINS
//+-----------------------------------------------------

<?php 
} // END COMMUNITY TICKET PAGE


?>


function ticketStatus(ticketID, status, updateSource, admin){
	//Process ajax with passed paramiters
	<?php if($pageID == "08-01-cc-003" || $pageID == "08-01-cc-903"){?>
	
	
	
	//SEND TICKET RECAP EMAIL
	if(status == "0" && updateSource == "view-ticket-close"){
		$.ajax({
			type: "POST",
			data: '',
			url: "process/ajax/support-processes.php?process=9&ticket=" + ticketID,
			success: function(data){
				//alert(data);	
			}
		});	
	}
	
	var replyField = document.forms["support-reply"]["reply"].value;
		
	if(status == "0" && updateSource == "view-ticket-close" && replyField != ""){
		
		//alert('hello');
		
		$.ajax({
			type: "POST",
			data: $(".ajax-reply").serialize(),
			url: "process/ajax/support-processes.php?process=3&close=0",
			success: function(data)
			{
				if(data == ""){
					//alert(data);
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
					//alert(data);
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
	<?php } ?>
	
	$.ajax({
		url: 'process/ajax/support-processes.php?process=5&ticket=' + ticketID + '&status=' + status + '&admin=' + admin,
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
		url: 'process/ajax/support-processes.php?process=7&ticket=' + ticketID + '&user=' + user,
		success: function(data) {
		//alert(ticketID);
		alert(ticketID + '|' + user);
		}//end success ajax
    
    });//end ajax call
}//end function ticketStatus()


function ticketAdmin(ticketID, assignedID, memberID){
	$.ajax({
		url: 'process/ajax/support-processes.php?process=8&ticket=' + ticketID + '&assign=' + assignedID + '&member=' + memberID,
		success: function(data) {
		$("#assign" + ticketID).html(data);
		//alert(data);
		
		//Notification
		toastr.options = {
			closeButton: true,
			debug: false,
			positionClass: 'toast-top-right',
			timeOut: '4000'
		};
		  
		toastr.success('Ticket successfully assigned to ' + data);
		}//end success ajax
    
    });//end ajax call
}//end function ticketAdmin

</script>