<?php
/*
Marketocracy Inc. | Beta Development
User Profile/Settings Scripts

Supporting files:
	AJAX		- process/ajax/user-settings-processes.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/user-settings.js.php
	HTML		- includes/pages/user-profile.php
*/

?>
<script type="text/javascript">

$('#fund_type').change(function(){
	
	
	var fundType = $('#fund_type').find(":selected").val();
	
	if(fundType == 'sector'){
		$('#fund-sector').css('display','block');	
	}else{
		$('#fund-sector').css('display', 'none');	
	}
});

function deleteAccount(memberID){

	
	
	$.ajax({
        type: "POST", 
        data: $(".account-delete-form").serialize(), 
        url: "process/ajax/user-settings-processes.php?process=deleteAccount", 
		success: function(data)
        {
		
			
			if((data.indexOf("alert-danger") > -1) == true ){
				
				$('#deleteError').html(data);
				
			}else{
				//$('#email-warnings').html(data);
				//location.reload();
				//alert('Success');
				
				window.location.href = '/logout';
				
				//$('#deleteError').html(data);
				
			}
			
     
        }
    });
	
}

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
        background: "url(/images/ajax-loading.gif) no-repeat 50% 50%"
    }).appendTo($("#"+element).css("position", "relative"));	
}

//START Validation Script
function validateField(eID, eIDs, submitID, customMessage){
	
	if(customMessage == '' || customMessage == undefined){
		customMessage = 'Please provide field.';	
	}else{
		customMessage = customMessage;	
	}
	
	//alert(customMessage);
	
	//Check to see if the eID has the wysihtml5 editor tag
	var check = (eID.indexOf('-editor') > -1);
	
	if(check == false){
		var fieldVal = $('#'+eID).val();
	}else{
		//Get Value from editor
		var fieldVal = editor.getValue();
		
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
		
		$('#'+eID+'-help').html('<span style="color:#b94a48;">'+customMessage+'</span>');	
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
		
		//Get value of field
		if(check == false){
			var val = $('#'+eIDs[index]).val();
		}else{
			var val = editor.getValue();
		}
		
		//Check for extra tag
		var check2 = (eIDs[index].indexOf('-extra') > -1);
		
		if(check2 == false){
			//alert('false');
		}else{
			//remove tags
			eIDs[index] = eIDs[index].replace('-editor', '');
			//eIDs[index] = eIDs[index].replace('-extra', '');
			
			//get extra validation value
			var extraVal = $('#'+eIDs[index]).val();
			
			if(extraVal != '0'){
				cnt++;	
			}
		}
		
		//If value is blank increase the counter by 1
		if(val == ''){
			cnt++;	
		}
	}
	
	
	
	//alert(cnt);
	
	//If the counter is greater than zero, there are blank fields
	if(cnt == 0){
		//no blank fields show submit button
		//$('#'+submitID).attr('disabled', true);	
		//$('#'+submitID).css('display', '');
		
		$('#'+submitID).css('background', '#5CB85C');
		$('#'+submitID).css('border-color', '#398439');
		$('#'+submitID).css('color', '#FFFFFF');
		$('#'+submitID).attr('disabled', false);	
	}else{
		//blank fields, hide submit button
		//$('#'+submitID).attr('disabled', false);
		//$('#'+submitID).css('display', 'none');
		
		$('#'+submitID).css('border-color', '#666666');
		$('#'+submitID).css('color', '#000000');
		$('#'+submitID).attr('disabled', true);
		
		
	}
	
}// End Validate Field Function

//END Validation Script

//Process Noticiationcs Form

//This function grabs the form by its class. <form action="" method="POST" class="note-process-agax">
$('form.note-process-ajax').on('submit', function() {
	//Make an ajax call
	$.ajax({
        type: "POST", /*form method*/
        data: $(".note-process-ajax").serialize(), /*This grabs all elements: inputs, checkbox arrays, textareas, etc, and allows them to be called by their 'name' variables with php $_REQUEST (acts like a post)*/
        url: "process/ajax/user-settings-processes.php?process=1", /*location of php processing code, Note: this uses a process variable to distinguish between different processes*/
        
		//after the ajax processes its code successfully, this success function will allow you to get the results from that page if need be. That information is stored in the "data" javascript variable
		success: function(data)
        {
			//Change the submit button to display that the settings have been saved
			$('#note-settings').html('<input type="submit" value="Saved" class="btn btn-default">');
			
			//Uncomment the line below to see the processed data alerted to the screen, Debug purpouses usually
			//alert(data);
			
			//Notification
			toastr.options = {
				closeButton: true, /*shows a close button on the popup*/
				debug: false,
				positionClass: 'toast-top-right', /*this is the position of the popup*/
				timeOut: '1000' /*miliseconds of how long to display popup*/
			};
			
			//Display notifiaction to browser  
			toastr.success('Notification Settings Saved'); /*Notification text in between quotes*/
     
        }
    });
	
	//This prevents the form from submiting like a normal forum, IE: keeps you from leaving the page
	return false;
});

//Change saved button back into a submit button once a user makes a change
function updateSave(){
	$('#note-settings').html('<input type="submit" value="Save Settings" class="btn btn-success">');
}

$('form.email-process-ajax').on('submit', function() {
	//Make an ajax call
	$.ajax({
        type: "POST", /*form method*/
        data: $(".email-process-ajax").serialize(), /*This grabs all elements: inputs, checkbox arrays, textareas, etc, and allows them to be called by their 'name' variables with php $_REQUEST (acts like a post)*/
        url: "process/ajax/user-settings-processes.php?process=email-settings", /*location of php processing code, Note: this uses a process variable to distinguish between different processes*/
        
		//after the ajax processes its code successfully, this success function will allow you to get the results from that page if need be. That information is stored in the "data" javascript variable
		success: function(data)
        {
			//Change the submit button to display that the settings have been saved
			$('#email-settings').html('<input type="submit" value="Saved" class="btn btn-default">');
			
			//Uncomment the line below to see the processed data alerted to the screen, Debug purpouses usually
			//alert(data);
			
			//Notification
			toastr.options = {
				closeButton: true, /*shows a close button on the popup*/
				debug: false,
				positionClass: 'toast-top-right', /*this is the position of the popup*/
				timeOut: '1000' /*miliseconds of how long to display popup*/
			};
			
			//Display notifiaction to browser  
			toastr.success('Email Settings Saved'); /*Notification text in between quotes*/
     
        }
    });
	
	//This prevents the form from submiting like a normal forum, IE: keeps you from leaving the page
	return false;
});

function updateSave2(){
	$('#email-settings').html('<input type="submit" value="Save Settings" class="btn btn-success">');
}

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


function addslashes( str ) {
    return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
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
			
			/*if(data == ''){
				var data = 'https://portfolio.marketocracy.com/images/profile/default-profile.png';	
			}*/
			
			//var data = addslashes(data);
			
			$('.replace-current-image').html('<img src="'+data+'" class="img-responsive" alt="<?php echo addslashes($memberName);?>" />');
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


//+-------------------------------------------------------------------------------------------------
//								BASIC AND ADVANCED FUND SETTINGS
//+-------------------------------------------------------------------------------------------------

//color picker
$('.colorpicker-default').colorpicker({
	format: 'hex'
});

function updateColor(){
	var color = $('#fund_color').val();
	
	//alert(color);
	
	$('#update_color_btn').css('background-color', color);	
}

function changeColor(color){
	$('#fund_color').val(color);
	$('#update_color_btn').css('background-color', color);	
}

//multi select
$('#select_groups').multiSelect({
	selectableHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search unselected...'>",
	selectableFooter: "<h5>Not Allowed</h5>",
	selectionHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search selected...'>",
	selectionFooter: "<h5>Allowed</h5>",
	afterInit: function (ms) {
		var that = this,
			$selectableSearch = that.$selectableUl.prev(),
			$selectionSearch = that.$selectionUl.prev(),
			selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
			selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';

		that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
			.on('keydown', function (e) {
				if (e.which === 40) {
					that.$selectableUl.focus();
					return false;
				}
			});

		that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
			.on('keydown', function (e) {
				if (e.which == 40) {
					that.$selectionUl.focus();
					return false;
				}
			});
	},
	afterSelect: function () {
		this.qs1.cache();
		this.qs2.cache();
	},
	afterDeselect: function () {
		this.qs1.cache();
		this.qs2.cache();
	}
});

$('#select_connects').multiSelect({
	selectableHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search unselected...'>",
	selectableFooter: "<h5>Not Allowed</h5>",
	selectionHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search selected...'>",
	selectionFooter: "<h5>Allowed</h5>",
	afterInit: function (ms) {
		var that = this,
			$selectableSearch = that.$selectableUl.prev(),
			$selectionSearch = that.$selectionUl.prev(),
			selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
			selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';

		that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
			.on('keydown', function (e) {
				if (e.which === 40) {
					that.$selectableUl.focus();
					return false;
				}
			});

		that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
			.on('keydown', function (e) {
				if (e.which == 40) {
					that.$selectionUl.focus();
					return false;
				}
			});
	},
	afterSelect: function () {
		this.qs1.cache();
		this.qs2.cache();
	},
	afterDeselect: function () {
		this.qs1.cache();
		this.qs2.cache();
	}
});





$('.make-switch').on('switch-change', function () {
    checked = $('#allow_all').attr('checked');
	
	//alert(checked);
	
	if(checked == 'checked'){
		$('.access_types').collapse('toggle');
		
	}else{
		$('.access_types').collapse('toggle');
	}
});

var eIDs = ["fund_name", "fund_symbol"/*, "fund_color"*/];

$('#fund_name').focusout(function(){
	validateField('fund_name', eIDs, 'submit-basic', 'Please provide a Fund Name.');
});

$('#fund_symbol').focusout(function(){
	validateField('fund_symbol', eIDs, 'submit-basic', 'Please provide a Fund Symbol.');
});

/*$('#fund_color').focusout(function(){
	validateField('fund_color', eIDs, 'submit-basic', 'Please pick a Fund Color.');
});*/

//SAVE Basic Settings
$('form#fund_basic').on('submit', function() {
	
	//Create an overlay div to display processing GIF
	$('<div class="remove-process"></div>').css({
        position: "absolute",
        width: "100%",
        height: "100%",
        top: 0,
        left: 0,
        background: "#e2e2e2",
        opacity: 0.4
    }).appendTo($("#basic-settings").css("position", "relative"));
    
    $('<div class="remove-process"></div>').css({
        position: "absolute",
        width: "100%",
        height: "100%",
        top: 0,
        left: 0,
        background: "url(/images/ajax-loading.gif) no-repeat 50% 50%"
    }).appendTo($("#basic-settings").css("position", "relative"));
	
	$.ajax({
        type: "POST", /*form method*/
        data: $("#fund_basic").serialize(),
        url: "process/ajax/user-settings-processes.php?process=5",
        
		success: function(data)
        {
			//alert(data);
			//Remove processing GIF
			$('.remove-process').remove();
			
			$('#basic-settings-error').html(data);
			
			if(data == ''){
				//Notification
				toastr.options = {
					closeButton: true, 
					debug: false,
					positionClass: 'toast-top-right', 
					timeOut: '3000'
				};
				
				//Display notifiaction to browser  
				toastr.success('Basic Fund Settings Saved!');
			}
     
        }
    });
	
	return false;
});

//Create New Fund
$('form#fund_new').on('submit', function() {
	
	//Create an overlay div to display processing GIF
	$('<div class="remove-process"></div>').css({
        position: "absolute",
        width: "100%",
        height: "100%",
        top: 0,
        left: 0,
        background: "#e2e2e2",
        opacity: 0.4
    }).appendTo($("#basic-settings").css("position", "relative"));
    
    $('<div class="remove-process"></div>').css({
        position: "absolute",
        width: "100%",
        height: "100%",
        top: 0,
        left: 0,
        background: "url(/images/ajax-loading.gif) no-repeat 50% 50%"
    }).appendTo($("#basic-settings").css("position", "relative"));
	
	$.ajax({
        type: "POST", /*form method*/
        data: $("#fund_new").serialize(),
        url: "process/ajax/user-settings-processes.php?process=5-2",
        
		success: function(data)
        {
			//alert(data);
			//Remove processing GIF
			$('.remove-process').remove();

            //alert(data);

            var checkData = data;
			
			//$('#basic-settings-error').html(data);
            if((checkData.indexOf("FUND:") > -1) == false ){
                $('#basic-settings-error').html(data);
                //alert('Not Found');
            }else{
                
                var fundID = data.replace("FUND:", "");

                //alert(fundID);

                window.location.href = '/?page=02-00-00-001&fund='+fundID+'&trade=buy&wiz=buy&buySym=';
            }


     
        }
    });
	
	return false;
});

//DELETE FUND
function deleteFund(fundID){

    var checked = $('#check-delete-fund').prop('checked');

    if(checked == true){
        //alert('delete');

        $.ajax({
            type: "POST", /*form method*/
            data: {fund: fundID},
            url: "process/ajax/user-settings-processes.php?process=5-3",

            success: function (data) {

                if (data == '') {
                    window.location.href = '<?php echo $siteRoot;?>?page=10-00-00-002&account=2';
                } else {
                    toastr.options = {
                        closeButton: true,
                        debug: false,
                        positionClass: 'toast-top-full-width',
                        timeOut: '6000'
                    };

                    //Display notifiaction to browser
                    toastr.error(data);
                }

            }
        });
    }else{
        //alert('You must first ACCEPT that you will no longer be able to access this fund.');
        toastr.options = {
            closeButton: true,
            debug: false,
            positionClass: 'toast-top-full-width',
            timeOut: '5000'
        };

        //Display notifiaction to browser
        toastr.error('You must first ACCEPT that you will no longer be able to access this fund!');
    }

}

//Remove group select boxes when a user hits the cancel button
function closeGroup(){
	
	$('#load-group-settings').html('');
	$('#submit-group').css('display', 'none');
	
}

//Load Group Settings
function loadGroup(groupID){	
	$.ajax({
        type: "POST", 
        url: "process/ajax/user-settings-processes.php?process=6&group="+groupID,
		success: function(data)
        {
			
			$('#load-group-settings').html(data);
			
			//start multiselect
			$('#select_connections').multiSelect({
				selectableHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search connections...'>",
				selectableFooter: "<h5>Not In Group</h5>",
				selectionHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search group...'>",
				selectionFooter: "<h5>In Group</h5>",
				afterInit: function (ms) {
					var that = this,
						$selectableSearch = that.$selectableUl.prev(),
						$selectionSearch = that.$selectionUl.prev(),
						selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
						selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';
			
					that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
						.on('keydown', function (e) {
							if (e.which === 40) {
								that.$selectableUl.focus();
								return false;
							}
						});
			
					that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
						.on('keydown', function (e) {
							if (e.which == 40) {
								that.$selectionUl.focus();
								return false;
							}
						});
				},
				afterSelect: function () {
					this.qs1.cache();
					this.qs2.cache();
				},
				afterDeselect: function () {
					this.qs1.cache();
					this.qs2.cache();
				}
			});
			//end multiselect
			
			$('#submit-group').css('display', '');
     
        }
    });
}

//Save Group Settings
function saveGroupSettings(){
	$.ajax({
        type: "POST",
        data: $("#update-group").serialize(),
        url: "process/ajax/user-settings-processes.php?process=7",
        
		success: function(data)
        {
			//alert(data);
			
			$('#exit-btn').html('close');
			
			//Notification
			toastr.options = {
				closeButton: true, 
				debug: false,
				positionClass: 'toast-top-right', 
				timeOut: '3000'
			};
			
			//Display notifiaction to browser  
			toastr.success('Group Saved!');
     
        }
    });
}

function reloadGroups(){
	$.ajax({
        type: "POST",
        data: $("#new-group").serialize(),
        url: "process/ajax/user-settings-processes.php?process=9",
        success: function(data)
        {
			//alert(data);
			
			$('#reload-groups').html(data);
			
			//multiselect
			$('#select_groups').multiSelect({
				selectableHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search unselected...'>",
				selectableFooter: "<h5>Not Allowed</h5>",
				selectionHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search selected...'>",
				selectionFooter: "<h5>Allowed</h5>",
				afterInit: function (ms) {
					var that = this,
						$selectableSearch = that.$selectableUl.prev(),
						$selectionSearch = that.$selectionUl.prev(),
						selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
						selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';
			
					that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
						.on('keydown', function (e) {
							if (e.which === 40) {
								that.$selectableUl.focus();
								return false;
							}
						});
			
					that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
						.on('keydown', function (e) {
							if (e.which == 40) {
								that.$selectionUl.focus();
								return false;
							}
						});
				},
				afterSelect: function () {
					this.qs1.cache();
					this.qs2.cache();
				},
				afterDeselect: function () {
					this.qs1.cache();
					this.qs2.cache();
				}
			});
			//multiselect
		}
		
	});
}

//Create Group
$('form#new-group').on('submit', function() {
	
	$('#update-new-group').fadeTo( "fast", 0);
	
	$.ajax({
        type: "POST",
        data: $("#new-group").serialize(),
        url: "process/ajax/user-settings-processes.php?process=8",
        success: function(data)
        {
			//alert(data);
			
			$('#update-new-group').html(data);
			$('#update-new-group').fadeTo( "fast", 1);
			reloadGroups();
			
		}
		
	});
	
	return false;
});

//Delete Group (Actually we are just making it inactive for now)
function deleteGroup(groupID, groupName){
	
	var check = confirm("Are you sure you wish to delete the group: "+groupName+"?");
	
	if(check == true){
		$.ajax({
			type: "POST",
			url: "process/ajax/user-settings-processes.php?process=10&group="+groupID,
			success: function(data)
			{
				//alert(data);
				$('#load-group-settings').html('');
				$('#update-new-group').html(data);
				$('#update-new-group').fadeTo( "fast", 1);
				reloadGroups();
				$('#exit-btn').html('close');
			}
			
		});
	}
}

//Save Advanced Settings
$('form#fund_advanced').on('submit', function() {
	
	//Create an overlay div to display processing GIF
	$('<div class="remove-process"></div>').css({
        position: "absolute",
        width: "100%",
        height: "100%",
        top: 0,
        left: 0,
        background: "#e2e2e2",
        opacity: 0.4
    }).appendTo($("#advanced-settings").css("position", "relative"));
    
    $('<div class="remove-process"></div>').css({
        position: "absolute",
        width: "100%",
        height: "100%",
        top: 0,
        left: 0,
        background: "url(/images/ajax-loading.gif) no-repeat 50% 50%"
    }).appendTo($("#advanced-settings").css("position", "relative"));
	
	//process php
	$.ajax({
        type: "POST", 
        data: $("#fund_advanced").serialize(),
        url: "process/ajax/user-settings-processes.php?process=11",
        
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
			toastr.success('Advanced Fund Settings Saved!');
     
        }
    });
	
	return false;
});


//CONNECTIONS PAGE

function reloadConnections(){
	$.ajax({
        type: "POST", 
        url: "process/ajax/user-settings-processes.php?process=13",
        
		success: function(data)
        {
			//alert(data);
			
			$('#my-connections').html(data);
     		
        }
    });	
}

function reloadInvites(){
	$.ajax({
        type: "POST", 
        url: "process/ajax/user-settings-processes.php?process=14",
        
		success: function(data)
        {
			//alert(data);			
			$('#invites-list').html(data);
			
     
        }
    });	
}

//accept invite
function acceptInvite(uid, connection, name){
	
	$('#invites-list').fadeTo( "fast", 0.3);
	$('#my-connections').fadeTo( "fast", 0.3);
	
	//process php
	$.ajax({
        type: "POST", 
        url: "process/ajax/user-settings-processes.php?process=12&uid="+uid+"&connect="+connection,
        
		success: function(data)
        {
			//alert(data);
			
			reloadConnections();
			reloadInvites();
			
			//Notification
			toastr.options = {
				closeButton: true, 
				debug: false,
				positionClass: 'toast-top-right', 
				timeOut: '3000'
			};
			
			//Display notifiaction to browser  
			toastr.success('You have accepted '+name+'\'s invite!');
     		
			
			$('#invites-list').fadeTo( "fast", 1);
			$('#my-connections').fadeTo( "fast", 1);
        }
    });	
}


//+------------------------------------------------------------------------------------------------------+
//								ACCOUNT SETTINGS - Account Settings Scripts
//+------------------------------------------------------------------------------------------------------+
function changeEmail(type){
	
	if(type == 'change'){
		$('#change-email-orig').css('display', 'none');
		$('#change-email').css('display', '');
	}
	
	if(type == 'cancel'){
		$('#change-email-orig').css('display', '');
		$('#change-email').css('display', 'none');
	}		
}	

//Process Account Settings form
//Save Advanced Settings
var eIDacs = ["name_first", "name_last", "phone_day"];

$('#name_first').focusout(function(){
	validateField('name_first', eIDacs, 'submit-account-settings', 'Please provide your first name.');
});

$('#name_last').focusout(function(){
	validateField('name_last', eIDacs, 'submit-account-settings', 'Please provide your last name.');
});

$('#phone_day').focusout(function(){
	validateField('phone_day', eIDacs, 'submit-account-settings', 'Please provide a phone number.');
});


$('form#save-account-settings').on('submit', function() {
	
	//alert('hello');
	
	//Create an overlay div to display processing GIF
	$('<div class="remove-process"></div>').css({
        position: "absolute",
        width: "100%",
        height: "100%",
        top: 0,
        left: 0,
        background: "#e2e2e2",
        opacity: 0.4
    }).appendTo($("#save-account-settings").css("position", "relative"));
    
    $('<div class="remove-process"></div>').css({
        position: "absolute",
        width: "100%",
        height: "100%",
        top: 0,
        left: 0,
        background: "url(/images/ajax-loading.gif) no-repeat 50% 50%"
    }).appendTo($("#save-account-settings").css("position", "relative"));
	
	//process php
	$.ajax({
        type: "POST", 
        data: $("#save-account-settings").serialize(),
        url: "process/ajax/user-settings-processes.php?process=15",
        
		success: function(data)
        {
			//alert(data);
			//Remove processing GIF
			$('.remove-process').remove();
			
			
			if(data == ''){
				
				$('#show-errors').html('');
				
				//Notification
				toastr.options = {
					closeButton: true, 
					debug: false,
					positionClass: 'toast-top-right', 
					timeOut: '3000'
				};
				
				//Display notifiaction to browser  
				toastr.success('Account Settings Saved!');
			}else{
				//Notification
				toastr.options = {
					closeButton: true, 
					debug: false,
					positionClass: 'toast-top-right', 
					timeOut: '5000'
				};
				
				//Display notifiaction to browser  
				toastr.error(data);
				
				$('#show-errors').html('<div class="note note-danger"><h4>Please fix the following issues.</h4><p>'+data+'</p></div>');
			}
     
        }
    });
	
	return false;
});


//Process Save Address

var eIDsTwo = ["country", "address1", "city"];

$('#country').focusout(function(){
	validateField('country', eIDsTwo, 'submit-address-settings', 'Please provide your country.');
});

$('#address1').focusout(function(){
	validateField('address1', eIDsTwo, 'submit-address-settings', 'Please provide your address.');
});

$('#city').focusout(function(){
	validateField('city', eIDsTwo, 'submit-address-settings', 'Please provide your city.');
});

$('form#save-address-settings').on('submit', function() {
	
	//Create an overlay div to display processing GIF
	$('<div class="remove-process"></div>').css({
        position: "absolute",
        width: "100%",
        height: "100%",
        top: 0,
        left: 0,
        background: "#e2e2e2",
        opacity: 0.4
    }).appendTo($("#save-address-settings").css("position", "relative"));
    
    $('<div class="remove-process"></div>').css({
        position: "absolute",
        width: "100%",
        height: "100%",
        top: 0,
        left: 0,
        background: "url(/images/ajax-loading.gif) no-repeat 50% 50%"
    }).appendTo($("#save-address-settings").css("position", "relative"));
	
	//process php
	$.ajax({
        type: "POST", 
        data: $("#save-address-settings").serialize(),
        url: "process/ajax/user-settings-processes.php?process=16",
        
		success: function(data)
        {
			//alert(data);
			//Remove processing GIF
			$('.remove-process').remove();
			
			
			if(data == ''){
				
				$('#show-address-errors').html('');
				
				//Notification
				toastr.options = {
					closeButton: true, 
					debug: false,
					positionClass: 'toast-top-right', 
					timeOut: '3000'
				};
				
				//Display notifiaction to browser  
				toastr.success('Address Settings Saved!');
			}else{
				//Notification
				toastr.options = {
					closeButton: true, 
					debug: false,
					positionClass: 'toast-top-right', 
					timeOut: '5000'
				};
				
				//Display notifiaction to browser  
				toastr.error(data);
				
				$('#show-address-errors').html('<div class="note note-danger"><h4>Please fix the following issues.</h4><p>'+data+'</p></div>');
			}
     
        }
    });
	
	return false;
});
//+------------------------------------------------------------------------------------------------------+
//								PROFILE SETTINGS - Profile Settings Scripts
//+------------------------------------------------------------------------------------------------------+
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

$('form#profile-questions').on('submit', function() {
	
	//Create an overlay div to display processing GIF
	$('<div class="remove-process"></div>').css({
        position: "absolute",
        width: "100%",
        height: "100%",
        top: 0,
        left: 0,
        background: "#e2e2e2",
        opacity: 0.4
    }).appendTo($("#profile-questions").css("position", "relative"));
    
    $('<div class="remove-process"></div>').css({
        position: "absolute",
        width: "100%",
        height: "100%",
        top: 0,
        left: 0,
        background: "url(/images/ajax-loading.gif) no-repeat 50% 50%"
    }).appendTo($("#profile-questions").css("position", "relative"));
	
	//process php
	$.ajax({
        type: "POST", 
        data: $("#profile-questions").serialize(),
        url: "process/ajax/user-settings-processes.php?process=17",
        
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
			toastr.success('Profile Questions Saved!');
			
     
        }
    });
	
	return false;
});

$('form#save-profile').on('submit', function() {
	
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

//+------------------------------------------------------------------------------------------------------+
//								ACCOUNT SETTINGS - Change Password Scripts
//+------------------------------------------------------------------------------------------------------+


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

$('#new-pw2').keyup(function() {
	
	var newPW 		= $('#new-pw').val();
		newPW2		= $('#new-pw2').val();
		
	$.ajax({
        type: "POST", 
        url: "process/ajax/compare-password.php?newPW2="+newPW2+"&newPW="+newPW,
        
		success: function(data)
        {
			//alert(data);
			$('#compare-pw').html(data);			
     
        }
    });
	
});

var eIDsThree = ["current-pw", "new-pw", "new-pw2"];

$('#current-pw').focusout(function(){
	validateField('current-pw', eIDsThree, 'submit-change-pw', 'Please provide a Current Password.');
});

$('#new-pw').focusout(function(){
	validateField('new-pw', eIDsThree, 'submit-change-pw', 'Please provide a New Password.');
});

$('#new-pw2').focusout(function(){
	validateField('new-pw2', eIDsThree, 'submit-change-pw', 'Please Re-type new Password.');
});

$('form#change-password').on('submit', function() {
	
	//show animated GIF
	showSaveGIF('change-password');
	
	//process php
	$.ajax({
        type: "POST", 
        data: $("#change-password").serialize(),
        url: "process/ajax/user-settings-processes.php?process=19",
        
		success: function(data)
        {
			//alert(data);
			
			//Remove processing GIF
			$('.remove-process').remove();
			
			if(data == 'success'){	
				//reset form
				$('#current-pw').val('');
				$('#new-pw').val('');
				$('#new-pw2').val('');
				$('#password-score').html('');
				$('#compare-pw').html('');
				$('#new-pw-help').html('');
				$('#new-pw2-help').html('');
				$('#pw-error').html('');
				
				
				//Notification
				toastr.options = {
					closeButton: true, 
					debug: false,
					positionClass: 'toast-top-right', 
					timeOut: '4000'
				};
				
				//Display notifiaction to browser  
				toastr.success('Password Changed Successfully!');
			}else{
				$('#pw-error').html(data);	
			}
     
        }
    });
	
	return false;
});


$(document).ready(function () {
    $('#sort-funds').sortable({
        axis: 'y',
        stop: function (event, ui) {
	        var data = $(this).sortable('serialize');
            $('#show-sort').html('Saving...');
            $.ajax({
                data: data,
                type: 'POST',
                url: 'process/ajax/user-settings-processes.php?process=sort-funds',
				success: function(returnData){
					
					$('#show-sort').html(returnData);
						
				}
            });
	}
    });
});
</script>