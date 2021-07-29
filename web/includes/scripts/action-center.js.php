<?php
/*
Marketocracy Inc. |
Action Center

Supporting files:
	AJAX		- process/ajax/action-center-processes.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/action-center.js.php
	HTML		- includes/pages/action-center.php
*/
?>
<script type="text/javascript">

function loadTestBtn(){
	$('.send-test-check').on('click', function(){
		
		if($(this).is(':checked')){
			$('.email-processing-btn').html('<input type="submit" value="Send Test Email" id="submit-email" class="btn btn-success" />');
		}else{
			$('.email-processing-btn').html('<input type="submit" value="Save Campaign" id="submit-email" class="btn btn-info" />');
		}
		//alert(sendTest);
		
	});
}

$('.email-body').wysihtml5({
	events: {
		load: function() {
			var some_wysi = $('.email-body').data('wysihtml5').editor;
			/*$(some_wysi.composer.element).bind('keyup', function(){ 
				//validateField('topic_post-editor', eIDs, 'submit-topic', 'Please provide a post for your topic.');
			});*/
		}
	}
});

$('.wysiwyg-add').wysihtml5({
	events: {
		load: function() {
			var some_wysi = $('.wysiwyg-add').data('wysihtml5').editor;
			//console.log("Loaded!");
      		var $iframe = $(this.composer.iframe);
      		var $body = $(this.composer.element);
			/*$body.css({
				'min-height': '120px',
				'line-height': '20px',
				'overflow': 'hidden',
			});*/
			/*var scrollHeightInit = $body[0].scrollHeight;
			console.log("scrollHeightInit", scrollHeightInit);
			var bodyHeightInit = $body.height();
			console.log("bodyHeightInit", bodyHeightInit);
			var heightInit = Math.min(scrollHeightInit, bodyHeightInit);*/
			$iframe.height(200);
			/*$(some_wysi.composer.element).bind('keyup', function(){ 
				//validateField('topic_post-editor', eIDs, 'submit-topic', 'Please provide a post for your topic.');
			});*/
			
		}
	}
});

$('#about_me').wysihtml5({
	events: {
		load: function() {
			var some_wysi = $('#about_me').data('wysihtml5').editor;
			/*$(some_wysi.composer.element).bind('keyup', function(){ 
				//validateField('topic_post-editor', eIDs, 'submit-topic', 'Please provide a post for your topic.');
			});*/
		}
	}
});


function showSaveGIF(element){
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

function saveProfileInfo(){
	$('#submit-profile').click();	
}

$('form#save-profile').on('submit', function () {
	
	//show animated GIF
	showSaveGIF('save-profile');
	
	//process php
	$.ajax({
        type: "POST", 
        data: $("#save-profile").serialize(),
        url: "process/ajax/user-settings-processes.php?process=18",
        
		success: function(data)
        {
			//alert(data);
			//Remove processing GIF
			$('.remove-process').remove();
				
			//Notification
			toastr.options = {
				closeButton: true, 
				debug: false,
				positionClass: 'toast-top-right', 
				timeOut: '3000'
			};
			
			//Display notifiaction to browser  
			toastr.success('Profile Settings Saved!');
			
     
        }
    });
	
	return false;
});


//START IMAGE UPLOAD JQUERY
$(document).ready(function() { 
	var options = { 
			target: '#new-image',   // target element(s) to be updated with server response 
			beforeSubmit: beforeSubmit,  // pre-submit callback 
			success: afterSuccess,  // post-submit callback 
			resetForm: true        // reset the form after successful submit 
		}; 
		
	 $('#MyImageForm').submit(function() { 
			$(this).ajaxSubmit(options);  			
			// always return false to prevent standard browser submit and page navigation 
			return false; 
		}); 
}); 

function afterSuccess()
{
	$('#submit-btn').show(); //hide submit button
	$('#loading-img').hide(); //hide submit button
	
	$('#demo8').Jcrop({
		aspectRatio: 1,
		onSelect: updateCoords
	});
	
	function updateCoords(c){
		$('#crop_x').val(c.x);
		$('#crop_y').val(c.y);
		$('#crop_w').val(c.w);
		$('#crop_h').val(c.h);
	};
	
	$('#demo8_form').submit(function(){
		if (parseInt($('#crop_w').val())) return true;
		alert('Please select a crop region then press submit.');
		return false;
	});

}

//function to check file size before uploading.
function beforeSubmit(){
    //check whether browser fully supports all File API
   if (window.File && window.FileReader && window.FileList && window.Blob)
	{
		
		if( !$('#imageInput').val()) //check empty input filed
		{
			$("#output").html('<div class="note note-warning"><h4>No File Selected</h4><p>Please select a file to upload.</p></div>');
			return false
		}
		
		var fsize = $('#imageInput')[0].files[0].size; //get file size
		var ftype = $('#imageInput')[0].files[0].type; // get file type
		

		//allow only valid image file types 
		switch(ftype)
        {
            case 'image/png': case 'image/gif': case 'image/jpeg': case 'image/pjpeg':
                break;
            default:
                $("#output").html('<div class="note note-danger"><h4>Invalid File Type!</h4><p>Unsupported file type! Please upload one of the following: <br /><strong>.jpg, .png, .gif</strong></p></div>');
				return false
        }
		
		//Allowed file size is less than 1 MB (1048576)
		if(fsize>3048576) 
		{
			$("#output").html('<div class="note note-danger"><h4>File Size Exceeded!</h4><p>Please reduce the size of your photo using an image editor, or choose a smaller image.<br /><b>'+bytesToSize(fsize) +'</b></p></div>');
			return false
		}
				
		$('#submit-btn').hide(); //hide submit button
		$('#loading-img').show(); //hide submit button
		$("#output").html("");  
	}
	else
	{
		//Output error to older browsers that do not support HTML5 File API
		$("#output").html('<div class="note note-warning"><h4>Browser Out Of Date</h4><p>Please upgrade your browser, your current browser lacks some new features we need!</p>');
		return false;
	}
}

//function to format bites bit.ly/19yoIPO
function bytesToSize(bytes) {
   var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
   if (bytes == 0) return '0 Bytes';
   var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
   return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
}




//SAVE PICTURE
function saveProfilePic(){	
	$.ajax({
        type: "POST", /*form method*/
        data: $("#MyImageForm").serialize(),
        url: "process/ajax/user-settings-processes.php?process=3",
        
		success: function(data)
        {
			//alert(data);
			
			$('.replace-current-image').html('<img src="'+data+'" class="img-responsive" alt="<?php echo $memberName;?>" />');
			$('#new-image').html('');
			
			
			//Notification
			toastr.options = {
				closeButton: true, /*shows a close button on the popup*/
				debug: false,
				positionClass: 'toast-top-right', /*this is the position of the popup*/
				timeOut: '3000' /*miliseconds of how long to display popup*/
			};
			
			//Display notifiaction to browser  
			toastr.success('New Picture Saved!'); /*Notification text in between quotes*/
     
        }
    });
}

//process crop image function
function cropImage(){	
	
	$('#crop-btn').html('<img src="images/ajax-loading.gif" alt="Please Wait"/>');
	
	$.ajax({
        type: "POST", /*form method*/
        data: $("#MyImageForm").serialize(),
        url: "process/ajax/user-settings-processes.php?process=4",
        
		success: function(data)
        {
			//alert(data);
			
			$('#new-image').html(data);
			
			//Notification
			toastr.options = {
				closeButton: true, /*shows a close button on the popup*/
				debug: false,
				positionClass: 'toast-top-right', /*this is the position of the popup*/
				timeOut: '3000' /*miliseconds of how long to display popup*/
			};
			
			//Display notifiaction to browser  
			toastr.success('Image successfully croped!'); /*Notification text in between quotes*/
     
        }
    });
}

//Redo image crop function
function redoCropImage(){
	
	var userImage 			= $('#user_image').val();
		imageFileName		= $('#image_file_name').val();
		imageType 			= $('#image_type').val();
		saveUserImage		= $('#save_user_image').val();
		saveUserImageThumb	= $('#save_user_image_thumb').val();
	
	$('#new-image').html('<h5>Crop Image</h5><div style="float:left;"><img src="'+userImage+'" alt="Resized Image" id="crop-function"></div><div style="float:left;margin-left:10px;" id="crop-btn"><button type="button" class="btn btn-info" onclick="cropImage();">Crop Image</button></div><input type="hidden" value="'+saveUserImage+'" name="save_user_image" /><input type="hidden" value='+saveUserImageThumb+'" name="save_user_image_thumb" /><input type="hidden" value="'+userImage+'" name="user_image" /><input type="hidden" value="'+imageFileName+'" name="image_file_name" /><input type="hidden" value="'+imageType+'" name="image_type" />');
	
	$('#crop-function').Jcrop({
		aspectRatio: 1,
		onSelect: updateCoords
	});
	
	function updateCoords(c){
		$('#crop_x').val(c.x);
		$('#crop_y').val(c.y);
		$('#crop_w').val(c.w);
		$('#crop_h').val(c.h);
	};
	
}


function toggleID(id){
	$('#'+id).toggle();	
}


function changeTab(current, active){
	
	
	
	$('#'+current).removeClass('active');
	$('#'+active).addClass('active');
	
}

$('#subdomain-check').keyup(function(){
	
	var subdomain = $(this).val();
	
	
	$.ajax({
		type: "POST",
		data: {subdomain:subdomain, process:'check-subdomain'},
		url: "process/ajax/action-center-processes.php",
		
		success: function(data){
			
			$('#show-subdomain-check').html(data);
			
			if (data.indexOf("alert-danger") !=-1) {
				$('#save-domain-btn').html('');
			}else{
				$('#save-domain-btn').html('<input type="submit" value="Save Changes" id="submit-domain" class="btn btn-info btn-sm" />');
			}
			/*if(data == ''){
				$('#subdomain-check').val('bmccarthy');	
			}*/
				
		}
	});
	
});

$('#subdomain-check').focusout(function() {
    
	if($(this).val() == ''){
		$('#subdomain-check').val('bmccarthy');		
	}
	
});

$('form.edit-domain-form').on('submit', function(e) {
		
		e.preventDefault();
		
		$('#save-domain-btn').html('<button type="button" class="btn btn-default btn-sm">Processing...</button>');
	
		//process php
		$.ajax({
			type: "POST", 
			data: $(".edit-domain-form").serialize(),
			url: "process/ajax/action-center-processes.php?process=save-domain",
			
			success: function(data){
				
				
				
				if (data.indexOf("note-danger") !=-1) {
					$('#subdomain-error').html(data);
					$('#save-domain-btn').html('<input type="submit" value="Save Changes" id="submit-domain" class="btn btn-info btn-sm" />');
					//alert(data);
				}else{
					$('#subdomain-error').html(data);
					$('#save-domain-btn').html('<input type="submit" value="Save Changes" id="submit-domain" class="btn btn-info btn-sm" />');
					$('#show-subdomain-check').html('');
					//alert(data);
					//location.reload();
				}
				
			}
		});
		
		return false;
	});

function deleteArticle(action,articleID){
	
	
		
	var conf = confirm("Are you sure you wish to delete this article?");
				
	
	if(conf == true){
		$.ajax({
				type: "POST", 
				data: {action:action,articleID:articleID,process:'delete-article'},
				url: "process/ajax/action-center-processes.php",
				
				success: function(data)
				{
					/*if (data.indexOf("note-danger") !=-1) {
						$('#add-article-error').html(data);
						
						//alert(data);
					}else{
						//$('#add-article-error').html(data);
						//alert(data);
						location.reload();
					}*/
					
					
					
					if(action == 'delete'){
						location.reload();	
					}
					
				}
			});
	}
		
}

function loadArticleType(){
	$('#article_type2').change(function getArticleType() {
	
	var articleType = $(this).val();
		articleID	= $('#edit_article_id').val();
		
		
	$.ajax({
        type: "POST", 
        data: {process: 'load-article-fields',articleType:articleType,articleID:articleID,prefill:'1'},
        url: "process/ajax/action-center-processes.php",
		success: function(data){
			
			$('#load-edit-article-fields').html(data);
			loadArticleType();
			
				
		}
	});
	
});	
}

function editArticle(action,articleID){
	
	
	if(action == 'load'){
		$.ajax({
			type: "POST", 
			data: {action:action,articleID:articleID,process:'load-article-fields',prefill:'1'},
			url: "process/ajax/action-center-processes.php",
			
			success: function(data)
			{
				$('#load-edit-article-fields').html(data);
				loadArticleType();
				loadEditForm();	
				/*if (data.indexOf("note-danger") !=-1) {
					$('#add-article-error').html(data);
					
					//alert(data);
				}else{
					//$('#add-article-error').html(data);
					//alert(data);
					location.reload();
				}*/
			
			}
		});				
	}
	
	if(action == 'delete'){
		
		if (confirm('Are you sure you want to delete this article?')) {
			$.ajax({
				type: "POST", 
				data: {action:action,articleID:articleID,process:'delete-article'},
				url: "process/ajax/action-center-processes.php",
				
				success: function(data)
				{
					location.reload();
				
				}
			});		
		} else {
			// Do nothing!
		}
			
	}
	
	
}

function loadEditForm(){
	$('form.edit-article-form').on('submit', function(e) {
		
		e.preventDefault();
		
		$('#edit-article-processing-btn').html('<button type="button" class="btn btn-default">Processing...</button>');
	
		//process php
		$.ajax({
			type: "POST", 
			data: $(".edit-article-form").serialize(),
			url: "process/ajax/action-center-processes.php?process=edit-article",
			
			success: function(data)
			{
				if (data.indexOf("note-danger") !=-1) {
					$('#edit-article-error').html(data);
					$('#edit-article-processing-btn').html('<input type="submit" value="Save Changes" id="submit-article" class="btn btn-info" />');
					//alert(data);
				}else{
					//$('#add-article-error').html(data);
					//alert(data);
					location.reload();
				}
				
			}
		});
		
		return false;
	});
}

//function loadArticleForm(){
	
	$('form.add-article-form').on('submit', function(e) {
		
		e.preventDefault();
		
		$('#article-processing-btn').html('<button type="button" class="btn btn-default">Processing...</button>');
	
		//process php
		$.ajax({
			type: "POST", 
			data: $(".add-article-form").serialize(),
			url: "process/ajax/action-center-processes.php?process=add-article",
			
			success: function(data)
			{
				if (data.indexOf("note-danger") !=-1) {
					$('#add-article-error').html(data);
					$('#article-processing-btn').html('<input type="submit" value="Add Article" id="submit-article" class="btn btn-info" />');
					//alert(data);
				}else{
					//$('#add-article-error').html(data);
					//alert(data);
					location.reload();
				}
				
			}
		});
		
		return false;
	});
	
	
	
//}

function updateDisplay(fundID){
	
	
	
	$.ajax({
        type: "POST", 
        data: $(".update-fund-"+fundID).serialize(),
        url: "process/ajax/action-center-processes.php?process=updateDisplay",
		success: function(data){
			
			$('#fund-strat-msg-'+fundID).html(data);
			//loadArticleForm();
			
				
		}
	});
	
	alert(toggle);
		
}

function saveFundStrat(fundID){
	
	var fundStrat = $('#fund_strategy_'+fundID).val();
	//alert(fundStrat);
	
	$.ajax({
        type: "POST", 
        data: {process: 'save-fund-strat',fundStrat: fundStrat, fundID: fundID},
        url: "process/ajax/action-center-processes.php",
		success: function(data){
			
			$('#fund-strat-msg-'+fundID).html(data);
			//loadArticleForm();
			
				
		}
	});
		
}

$('#article_type').change(function getArticleType() {
	
	var articleType = $(this).val();
	
	$.ajax({
        type: "POST", 
        data: {process: 'load-article-fields',articleType:articleType},
        url: "process/ajax/action-center-processes.php",
		success: function(data){
			
			$('#load-article-fields').html(data);
			//loadArticleForm();
			
				
		}
	});
	
});

//+-------------------------------------------------------------------------------------------+
//|									EMAIL Campaign Scripts
//+-------------------------------------------------------------------------------------------+

//+-------------------------------------------------------------------------------------------+
//|									EMAIL Campaign Scripts
//+-------------------------------------------------------------------------------------------+
var initEmailTable = function() {
	var oTable = $('#campaigns_table').dataTable( {           
		"oLanguage": {
			"sEmptyTable": "You do not have any campaigns."
		},
		"aoColumnDefs": [
			{ "aTargets": [ 0 ] }
		],
		"aaSorting": [[7, 'desc']], // set default column order 
		 "aLengthMenu": [
			[30, 60, 90 -1],
			[30, 60, 90, "All"] // change per page values here
		],
		// set the initial value
		"iDisplayLength": 30
		
	});
	
	/*new $.fn.dataTable.FixedColumns( oTable, {
		leftColumns: 1,
		rightColumns: 0	
	});*/
	
	/*new $.fn.dataTable.FixedHeader( oTable, {
		"offsetTop": 45,
		left: true
	} );*/

	jQuery('#campaigns_table_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
	jQuery('#campaigns_table_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
	jQuery('#campaigns_table_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

	$('#campaigns_table_column_toggler input[type="checkbox"]').change(function(){
		/* Get the DataTables object again - this is not a recreation, just a get of the object */
		var iCol = parseInt($(this).attr("data-column"));
		var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
		oTable.fnSetColumnVis(iCol, (bVis ? false : true));
	});
}

$('form.create-campaign-form').on('submit', function() {
	
	$('#campaign-processing-btn').html('<button type="button" class="btn btn-default">Processing...</button>');
	
	//process php
	$.ajax({
		type: "POST", 
		data: $(".create-campaign-form").serialize(),
		url: "process/ajax/action-center-processes.php?process=create-email-campaign",
		
		success: function(data)
		{
			$('#campaign-processing-btn').html('<input type="submit" value="Create Campaign" id="submit-campaign" class="btn btn-info" />');
			//alert(data);
			//$('#sendEmail-errors').html(data);
			if (data.indexOf("alert-danger") !=-1) {
				$('#create-campaign-errors').html(data);
				
				//alert(data);
			}else{
				
				location.reload();
			}
			
		}
	});
	
	return false;
});

function loadSendCampaignForm(){
	$('form.send-campaign-form').on('submit', function() {
		
		$('#send-campaign-processing-btn').html('<button type="button" class="btn btn-default">Processing...</button>');
		
		//process php
		$.ajax({
			type: "POST", 
			data: $(".send-campaign-form").serialize(),
			url: "process/ajax/action-center-processes.php?process=send-campaign",
			
			success: function(data)
			{
				$('#send-campaign-processing-btn').html('<input type="submit" value="Send Campaign" id="send-campaign-btn" class="btn btn-info" />');
				//alert(data);
				//$('#sendEmail-errors').html(data);
				if (data.indexOf("alert-danger") !=-1) {
					$('#send-campaign-errors').html(data);
					
					//alert(data);
				}else{
					
					location.reload();
				}
				
			}
		});
		
		return false;
	});
}

function loadEmails(skipTableInit){
	
	//init:initEmailTable();
	
	$.ajax({
        type: "POST", 
        data: {process: 'load-emails', memberID: '<?php echo $_SESSION['member_id'];?>'},
        url: "process/ajax/action-center-processes.php",
		success: function(data){
			
			$('#load-emails').html(data);
			
			if(skipTableInit == 0){
				init:initEmailTable();
			}else{
				$('#campaigns_table').DataTable().reload();	
			}
			
				
		}
	});
}

function loadEditEmailForm(){
	
	
	
	$('form.edit-campaign-form').on('submit', function() {
	
		$('#save-email-changes-btn').html('<button type="button" class="btn btn-default">Processing...</button>');
		
		//process php
		$.ajax({
			type: "POST", 
			data: $(".edit-campaign-form").serialize(),
			url: "process/ajax/action-center-processes.php?process=save-email-campaign",
			
			success: function(data)
			{
				$('save-email-changes-btn').html('<input type="submit" value="Save Changes" id="save-email-changes-btn" class="btn btn-info" />');
				//alert(data);
				//$('#sendEmail-errors').html(data);
				if (data.indexOf("alert-danger") !=-1) {
					$('#save-campaign-errors').html(data);
					$("#edit-email-campaign").scrollTop(0);
					//alert(data);
				}else{
					
					//loadEmails(1);
					//$('#edit-email-campaign').modal('hide');
					location.reload();
				}
				
			}
		});
		
		return false;
	});

		
}

function loadConfirmEmail(campID){
	$.ajax({
		type: "POST", 
		data: {campID:campID, process: 'load-confirm-email'},
		url: "process/ajax/action-center-processes.php",
		
		success: function(data)
		{
			
			$('#load-confirm-email').html(data);
			
			loadConfirmForm();
			
			
		}
	});
}

function loadConfirmForm(){
	
	$('form.confirm-form').on('submit', function() {
	
		$('#confirm-processing-btn').html('<button type="button" class="btn btn-default">Processing...</button>');
		
		//process php
		$.ajax({
			type: "POST", 
			data: $(".confirm-form").serialize(),
			url: "process/ajax/action-center-processes.php?process=save-email-campaign",
			
			success: function(data)
			{
				$('#confirm-processing-btn').html('<input type="submit" value="Save Status" id="confirm-btn" class="btn btn-info" />');
				//alert(data);
				//$('#sendEmail-errors').html(data);
				if (data.indexOf("alert-danger") !=-1) {
					$('#confirm-errors').html(data);
					$("#confirm-errors").scrollTop(0);
					//alert(data);
				}else{
					
					//loadEmails(1);
					//$('#edit-email-campaign').modal('hide');
					location.reload();
				}
				
			}
		});
		
		return false;
	});
		
};

function loadSendEmail(campID){
	$.ajax({
		type: "POST", 
		data: {campID:campID, process: 'load-send-email'},
		url: "process/ajax/action-center-processes.php",
		
		success: function(data)
		{
			
			$('#load-send-email').html(data);
			
			loadSendCampaignForm();
			/*$('campaign-processing-btn').html('<input type="submit" value="Create Campaign" id="submit-campaign" class="btn btn-info" />');
			//alert(data);
			//$('#sendEmail-errors').html(data);
			if (data.indexOf("note-danger") !=-1) {
				$('#create-campaign-errors').html(data);
				
				//alert(data);
			}else{
				
				//location.reload();
			}*/
			
		}
	});
}

function loadCampaignReport(campID){
	$.ajax({
		type: "POST", 
		data: {campID:campID, process: 'view-campaign-report'},
		url: "process/ajax/action-center-processes.php",
		
		success: function(data)
		{
			
			$('#load-campaign-report').html(data);
			
			/*$('campaign-processing-btn').html('<input type="submit" value="Create Campaign" id="submit-campaign" class="btn btn-info" />');
			//alert(data);
			//$('#sendEmail-errors').html(data);
			if (data.indexOf("note-danger") !=-1) {
				$('#create-campaign-errors').html(data);
				
				//alert(data);
			}else{
				
				//location.reload();
			}*/
			
		}
	});
}

function loadEditEmail(campID){
	
	$('#load-edit-email').html('Loading...');
	
	$.ajax({
		type: "POST", 
		data: {campID:campID, process: 'load-edit-email'},
		url: "process/ajax/action-center-processes.php",
		
		success: function(data)
		{
			
			$('#load-edit-email').html(data);
			
			$('#email-body-'+campID).wysihtml5({
				events: {
					load: function() {
						var some_wysi = $('#email-body-'+campID).data('wysihtml5').editor;
						/*$(some_wysi.composer.element).bind('keyup', function(){ 
							//validateField('topic_post-editor', eIDs, 'submit-topic', 'Please provide a post for your topic.');
						});*/
					}
				}
			});
			
			loadEditEmailForm();
			loadTestBtn();
			/*$('campaign-processing-btn').html('<input type="submit" value="Create Campaign" id="submit-campaign" class="btn btn-info" />');
			//alert(data);
			//$('#sendEmail-errors').html(data);
			if (data.indexOf("note-danger") !=-1) {
				$('#create-campaign-errors').html(data);
				
				//alert(data);
			}else{
				
				//location.reload();
			}*/
			
		}
	});
}
			
function loadEmailPreview(campID){
	
	$.ajax({
		type: "POST", 
		data: {campID:campID, process: 'load-email-preview'},
		url: "process/ajax/action-center-processes.php",
		
		success: function(data)
		{
			
			$('#load-email-preview').html(data);
			/*$('campaign-processing-btn').html('<input type="submit" value="Create Campaign" id="submit-campaign" class="btn btn-info" />');
			//alert(data);
			//$('#sendEmail-errors').html(data);
			if (data.indexOf("note-danger") !=-1) {
				$('#create-campaign-errors').html(data);
				
				//alert(data);
			}else{
				
				//location.reload();
			}*/
			
		}
	});
	
}

var initArticleTable = function() {
	var oTable = $('#articles_table').dataTable( {           
		"oLanguage": {
			"sEmptyTable": "You do not have any articles."
		},
		"aoColumnDefs": [
			{ "aTargets": [ 0 ] },
			{ "width": "20%", "targets": 2 },
			{ "width": "1%", "targets": 1 }
		],
		"aaSorting": [[0, 'desc']], // set default column order 
		 "aLengthMenu": [
			[30, 60, 90 -1],
			[30, 60, 90, "All"] // change per page values here
		],
		// set the initial value
		"iDisplayLength": 31
	});
	
	/*new $.fn.dataTable.FixedColumns( oTable, {
		leftColumns: 1,
		rightColumns: 0	
	});*/
	
	/*new $.fn.dataTable.FixedHeader( oTable, {
		"offsetTop": 45,
		left: true
	} );*/

	jQuery('#articles_table_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
	jQuery('#articles_table_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
	jQuery('#articles_table_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

	$('#articles_table_column_toggler input[type="checkbox"]').change(function(){
		/* Get the DataTables object again - this is not a recreation, just a get of the object */
		var iCol = parseInt($(this).attr("data-column"));
		var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
		oTable.fnSetColumnVis(iCol, (bVis ? false : true));
	});
}

var initTrackingTable = function() {
	var oTable = $('#tracking_table').dataTable( {           
		oLanguage: {
			"sEmptyTable": "You are not tracking any managers."
		},
		aoColumnDefs: [
			{ "aTargets": [ 0 ] }
		],
		aaSorting: [[0, 'desc']], // set default column order 
		aLengthMenu: [
			[30, 60, 90 -1],
			[30, 60, 90, "All"] // change per page values here
		],
		// set the initial value
		iDisplayLength: 31,
		//ajax: "process/ajax/action-center-processes.php?process=load-tracked-managers&memberID=<?php echo $_SESSION['member_id'];?>"
	});
	
	//oTable.api().ajax.load();
	/*new $.fn.dataTable.FixedColumns( oTable, {
		leftColumns: 1,
		rightColumns: 0	
	});*/
	
	/*new $.fn.dataTable.FixedHeader( oTable, {
		"offsetTop": 45,
		left: true
	} );*/

	

	jQuery('#tracking_table_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
	jQuery('#tracking_table_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
	jQuery('#tracking_table_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

	$('#tracking_table_column_toggler input[type="checkbox"]').change(function(){
		/* Get the DataTables object again - this is not a recreation, just a get of the object */
		var iCol = parseInt($(this).attr("data-column"));
		var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
		oTable.fnSetColumnVis(iCol, (bVis ? false : true));
	});
	
	
}



function loadTrackedManagers(){
	
	//init:initTrackingTable();
	
	$.ajax({
        type: "POST", 
        data: {process: 'load-tracked-managers', memberID: '<?php echo $_SESSION['member_id'];?>'},
        url: "process/ajax/action-center-processes.php",
		success: function(data){
			
			$('#load-tracked-managers').html(data);
			
			init:initTrackingTable();
			
				
		}
	});
}

function loadArticles(){

	$.ajax({
        type: "POST", 
        data: {process: 'load-articles'},
        url: "process/ajax/action-center-processes.php",
		success: function(data){
			
			$('#load-articles').html(data);
			
			init:initArticleTable();
			
				
		}
	});
}



function loadTopTenStocks(){

	$.ajax({
        type: "POST", 
        data: {process: 'load-top-ten-stocks'},
        url: "process/ajax/action-center-processes.php",
		success: function(data){
			
			$('#load-top-ten-stocks').html(data);
				
		}
	});
}

function removeTracking(fundID, userID, fundSymbol){
	
	if(confirm('Are you sure you wish to stop tracking: '+fundSymbol+'?')){
		
		$.ajax({
			type: "POST", 
			data: {process: 'stop-track-fund',fundID: fundID},
			url: "process/ajax/track-manager-processes.php",
			success: function(data){
				
				//alert(data);
				location.reload();
				//$('#tracking_table').dataTable()._fnAjaxUpdate();
					
			}
		});
			
	}
		
}


function loadAnayliticsChart(pageID, memberID){
	
	$('#load-view-chart-btn').html('Loading...');
	$('#page-view-modal-container').html('<img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" /> Loading Chart Info');
	
	var names = ['Unique Views','Trackers'],
		seriesCounter = 0,
		seriesOptions = [],
		colors = Highcharts.getOptions().colors,
		flags = true;
		
	//console.log('starting to retrive data');
	
	var start = + new Date();
	
	$.each(names, function(i, name) {
		
		//get line data
		$.ajax({
		    url:'process/json/page-views-json.php?page='+pageID+'&member='+memberID+'&type=views&viewType='+name, 
		    dataType:'json',
		    async:false,
		    success:function(data) {
				// do stuff.
				//console.log( "success with grabing json data for " + name.toLowerCase());
				seriesOptions[i] = {
					name: name,
					data: data,
					color: colors[i],
					id: name,
					type: 'line',
					/*marker : {
                   		enabled : true,
                    	radius : 3
                	},*/
					tooltip: {
						pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br/>',
						valueDecimals: 0
					}
				}; //end seriesOptions
				
			} //end success
		}) //end ajax
		
		seriesCounter++;
		
		if (seriesCounter == names.length) {
			
			//we have finished loading the temperature data, so lets check if any flags that need adding.
			// this is getting JSON data like this, [{"x":1405584416000,"title":"1","text":"matthew enter home"}]
		 		//console.log('Loading Flags');
					i++; //need to increment the counter again
		  		$.ajax({
				    url:'process/json/page-views-json.php?page='+pageID+'&member='+memberID+'&type=flags', 
				    dataType:'json',
				    async:false,
				    success:function(data) {
						console.log( "success with grabing json data for flags");
						//flags or events
						seriesOptions[i] = ({
								name: 'Forbes Article',
								color: colors[i],
								type: 'flags',
								shape: 'circlepin',
								onSeries: 'Unique Views',
								data: data
						});
					} //end success
					
				}) //end ajax
				
			
		}; //end if seriesCounter
		
	}); //end foreach name
	
	//console.log(seriesOptions);
	//all done, lets create that chart now.
	
	
	createChart();
	
	$('#load-view-chart-btn').html('View Page View Chart');
	
	function createChart() {
		
		$('#page-view-modal-container').highcharts('StockChart', {
			
			
			
            rangeSelector : {
                selected : 1
            },
			
			 yAxis: {
				
				min: 0 // this sets minimum values of y to 0
			},
			
			legend: {
				enabled: true
			},

            title : {
                text : 'Unique Page Views'
            },

            series : seriesOptions,
			
			credits: {
				enabled: false	
			}
        });
		
		
			
	};
	
	
	
}



$(document).ready(function() {
	
	loadTrackedManagers();
	loadTopTenStocks();
	loadArticles();
	loadEmails(0);
	loadTestBtn();
	//loadTrackers();
	//loadArticleForm();
	
});








</script>