<script>
//+---------------------------------------------------------------------------------------------------+
//						FORM FIELD VALIDATION FUNCTION :  *Field Required 

	//	eID = input element HTML ID, 
	//	eIDs = javascript array of input elementIDs(for hiding and displaying the submit button), 
	//	submitID = HTML ID of submit button
//+----------------------------------------------------------------------------------------------------+
function toggleID(id){
	$('#'+id).toggle();	
}

function loadOptions(){
	
	 
	$('#select-template').on('change', function() {
	  
	  	//alert( this.value );
		
		var tempID = this.value;
		 
		//alert(tempID); 
		 
		$.ajax({
			type: "POST", 
			data: {process: 'load-template-fields', tempID: tempID},
			url: "process/ajax/community-forum-processes.php",
			
			success: function(data)
			{
				
				$('#load-template-options').html(data);
				
			}
			
		});
	  
	});
	
	
		
}

function loadSendEmail(){
	$('form.send-email-form').on('submit', function() {
		
		$('#email-processing-btn').html('<button type="button" class="btn btn-default">Processing...</button>');
		
		//process php
		$.ajax({
			type: "POST", 
			data: $(".send-email-form").serialize(),
			url: "process/ajax/community-forum-processes.php?process=send-email",
			
			success: function(data)
			{
				$('#email-processing-btn').html('<input type="submit" value="Send Email" id="submit-email" class="btn btn-info" />');
				//alert(data);
				//$('#sendEmail-errors').html(data);
				if (data.indexOf("note-danger") !=-1) {
					$('#sendEmail-errors').html(data);
					
					//alert(data);
				}else{
					
					//alert(data);
					$('#send-email').modal('toggle');
				
					//Notification
					toastr.options = {
						closeButton: true, 
						debug: false,
						positionClass: 'toast-top-right', 
						timeOut: '3000'
					};
					
					//Display notifiaction to browser  
					toastr.success('Email Sent!');
				}
				
			}
		});
		
		return false;
	});
}

function sendEmail(section, forumID, catID, topicID){
	
	$('#load-email-modal').html('Loading...');
	
	$.ajax({
        type: "POST", 
        data: {process: 'load-email-modal',section: section, forumID: forumID, catID: catID, topicID: topicID},
        url: "process/ajax/community-forum-processes.php",
        
		success: function(data){
		
			$('#load-email-modal').html(data);	
     		loadOptions();
			loadSendEmail();
        }
    });
		
}

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
		$('#'+submitID).css('display', '');	
	}else{
		//blank fields, hide submit button
		$('#'+submitID).css('display', 'none');
		
		
	}
	
}// End Validate Field Function


$(function() {
 
    $("form").bind("keypress", function(e) {
            if (e.keyCode == 13) return false;
      });
 
});
<?php
//+---------------------------------------------------------------------------------------------+
//|										VIEW FORUMS PAGE										|
//+---------------------------------------------------------------------------------------------+ 
if($pageID == "04-01-00-001"){
?>

//+---------------------------------------------------------------------+
//|     				AJAX LOAD FORUMS - PROCESS 12					|
//+---------------------------------------------------------------------+


//START TABLE CODE
var forumTable = function () {
		
	var initForumTable = function() {
        var oTable = $('#forum-list').dataTable( {           
            "oLanguage": {
				"sEmptyTable": "There are no forums yet."
			},
			"aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[3, 'asc']], // set default column order 
             "aLengthMenu": [
                [15, 20, 25, 50 -1],
                [15, 20, 25, 50, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 20,
        });

        jQuery('#forum-list_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#forum-list_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#forum-list_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#forum-list_column_toggler input[type="checkbox"]').change(function(){
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
			
			initForumTable();
			
        }

    };

}();

//END TABLE CODE

var forumRefresh = <?php echo $_SESSION['support_ticket_refresh']; ?>;

$(document).ready(getForums = function() {
   
   	
   
    //Updates the forums on the forum  page
	$.ajax({
      url: 'process/ajax/community-forum-processes.php?process=12&active=1&adminAccess=<?php echo $adminAccess;?>',
      success: function(data) {
        $('.load-forums').html(data);
		
		forumTable.init();
      }
    
    });
});
//var refreshInterval = setInterval(getForums, forumRefresh);

$(document).ready(getInactiveForums = function() {
    //Updates the Topics on the forum topics page
	$.ajax({
      url: 'process/ajax/community-forum-processes.php?process=12&active=0&adminAccess=<?php echo $adminAccess;?>',
      success: function(data) {
        $('.load-inactive-forums').html(data);
      }
    
    });
});
var refreshInterval2 = setInterval(getInactiveForums, forumRefresh);


function refreshForums(){
	getForums();
	getInactiveForums();	
}



//+---------------------------------------------------------------------+
//|     				CREATE NEW FORUM - PROCESS 11					|
//+---------------------------------------------------------------------+

var eIDs = ["forum_title", "forum_desc"];

$('#forum_title').focusout(function(){
	validateField('forum_title', eIDs, 'submit-forum');
});

$('#forum_desc').focusout(function(){
	validateField('forum_desc', eIDs, 'submit-forum');
});


//Process Create Forum Form via AJAX
$('form.create-forum').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $(".create-forum").serialize(),
        url: "process/ajax/community-forum-processes.php?process=11",
        success: function(data)
        {
			if(data == ""){
				getForums();
				
				$('#create-forum').modal('toggle');
				
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.info('New Category Successfully Created');
				
				$('#forum_title').val('');
				$('#forum_desc').val('');
				
			}else{
				getForums();
				
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
            $('#createForumUserError').html(data);
     
        }
    });
	
	return false;
});

<?php
}

//+---------------------------------------------------------------------------------------------+
//|									VIEW FORUM CATEGORIES PAGE									|
//+---------------------------------------------------------------------------------------------+ 
if($pageID == "04-01-00-002"){
?>




$('#select_groups').multiSelect({
	selectableHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search...'>",
	selectionHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search...'>",
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

$('#select_members').multiSelect({
	selectableHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search...'>",
	selectionHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search...'>",
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


$('#select_member_types').multiSelect({
	selectableHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search...'>",
	selectionHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search...'>",
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

<?php
if($checkEveryone == 'checked'){?>
$("#access_types *").prop('disabled',true);
$('#access_types').fadeTo( "fast", 0.33 );
<?php } ?>

function showHideAccess(){
	checked = $('#allow_all').attr('checked');
	
	var index;
	var lists = ["select_groups", "select_member_types", "select_members"];
	
	if(checked == 'checked'){
		
		for (index = 0; index < lists.length; ++index) {
			$('#'+lists[index]+' option').prop('selected', true);
			$('#ms-'+lists[index]+' .ms-elem-selectable').css('display', 'none');
			$('#ms-'+lists[index]+' .ms-elem-selection').css('display', '');
			$('#ms-'+lists[index]+' .ms-elem-selection').addClass('ms-selected');
		}
		
		$("#access_types *").prop('disabled',true);
		$('#access_types').fadeTo( "fast", 0.33 );
		
		
	}else{
		
		for (index = 0; index < lists.length; ++index) {
			$('#'+lists[index]+' option').prop('selected', false);
			$('#ms-'+lists[index]+' .ms-elem-selectable').css('display', '');
			$('#ms-'+lists[index]+' .ms-elem-selection').css('display', 'none');
			$('#ms-'+lists[index]+' .ms-elem-selection').removeClass('ms-selected');
		}
		
		$('#access_types').fadeTo( "fast", 1 );
		//$("#access_types *").attr("disabled", "").off('click');
		$("#access_types *").prop('disabled',false);
		//alert('not checked');
	}
}

function updateAccess(forumID){
	
	$('<div class="remove-process"></div>').css({
        position: "absolute",
        width: "100%",
        height: "100%",
        top: 0,
        left: 0,
        background: "#e2e2e2",
        opacity: 0.4
    }).appendTo($("#select_container").css("position", "relative"));
    
    $('<div class="remove-process"></div>').css({
        position: "absolute",
        width: "100%",
        height: "100%",
        top: 0,
        left: 0,
        background: "url(http://beta.marketocracy.com/images/ajax-loading.gif) no-repeat 50% 50%"
    }).appendTo($("#select_container").css("position", "relative"));
	
	$.ajax({
		type: "POST",
		data: $("#save_allow_access").serialize(),
		url: "process/ajax/community-forum-processes.php?process=17&forum="+forumID,
		success: function(data){
			
			//alert(data);
			$('.remove-process').remove();
			
		}
	});	
}

function selectDeselect(type, list){
	
	if(type == 'select'){
		$('#'+list+' option').prop('selected', true);
		$('#ms-'+list+' .ms-elem-selectable').css('display', 'none');
		$('#ms-'+list+' .ms-elem-selection').css('display', '');
		$('#ms-'+list+' .ms-elem-selection').addClass('ms-selected');
	}
	if(type == 'deselect'){
		$('#'+list+' option').prop('selected', false);
		$('#ms-'+list+' .ms-elem-selectable').css('display', '');
		$('#ms-'+list+' .ms-elem-selection').css('display', 'none');
		$('#ms-'+list+' .ms-elem-selection').removeClass('ms-selected');
	}
}

$('#search-symbol').autocomplete({
	source: function( request, response ) {
		$.ajax({
			url : 'http://beta.marketocracy.com/process/ajax/search-processes.php?process=3',
			dataType: "json",
			data: {
			   search_term: request.term,
			   type: 'country'
			},
			 success: function( data ) {
				 response( $.map( data, function( item ) {
					return {
						label: item,
						value: item
					}
				}));
			}
		});
	},
	autoFocus: true,
	minLength: 0      	
});

$('#symbol').autocomplete({
	source: function( request, response ) {
		$.ajax({
			url : 'http://beta.marketocracy.com/process/ajax/search-processes.php?process=3',
			dataType: "json",
			data: {
			   search_term: request.term,
			   type: 'country'
			},
			 success: function( data ) {
				 response( $.map( data, function( item ) {
					return {
						label: item,
						value: item
					}
				}));
			}
		});
	},
	autoFocus: true,
	minLength: 0      	
});

//+---------------------------------------------------------------------+
//|     			AJAX LOAD CATEGORIES - PROCESS 9					|
//+---------------------------------------------------------------------+

//START TABLE CODE
var catTable = function () {
		
	var initCatTable = function() {
        var oTable = $('.forum-cat').dataTable( {           
            "oLanguage": {
				"sEmptyTable": "There are no categories for this forum yet."
			},
			"aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[3, 'asc']], // set default column order 
             "aLengthMenu": [
                [15, 20, 25, 50 -1],
                [15, 20, 25, 50, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 20,
        });

        jQuery('.forum-cat_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('.forum-cat_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('.forum-cat_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('.forum-cat_column_toggler input[type="checkbox"]').change(function(){
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
			
			initCatTable();
			
        }

    };

}();

//END TABLE CODE


var catRefresh = <?php echo $_SESSION['support_ticket_refresh']; ?>;

$(document).ready(getCategories = function(skip) {
    //Updates the Topics on the forum topics page
	$.ajax({
		url: 'process/ajax/community-forum-processes.php?process=9&forum=<?php echo $forumID;?>&adminAccess=<?php echo $adminAccess;?>&active=1',
		success: function(data) {
			$('.load-categories').html(data);
			
			if(skip == 'skip'){
				catTable.init();
			}
		}
      
    });
});
//var refreshInterval = setInterval(getCategories, catRefresh);

$(document).ready(getInactiveCategories = function() {
    //Updates the Topics on the forum topics page
	$.ajax({
      url: 'process/ajax/community-forum-processes.php?process=9&forum=<?php echo $forumID;?>&adminAccess=<?php echo $adminAccess;?>&active=0',
      success: function(data) {
        $('.load-inactive-cats').html(data);
      }
    
    });
});
var refreshInterval2 = setInterval(getInactiveCategories, catRefresh);


function refreshCategories(){
	getCategories();
	getInactiveCategories();	
}

//+-------------------------------------------------------------------------------------+
//|							START: WYSIHTML5 Validation									|
//+-------------------------------------------------------------------------------------+


var eIDs = ["symbol-extra", "topic_title", "topic_post-editor"];

$('#symbol').bind('keyup', function(){
	$.ajax({
        type: "POST",
        data: $(".create-topic").serialize(),
        url: "process/ajax/community-forum-processes.php?process=16",
        success: function(data)
        {	
			//alert(data);
			if(data == '0'){
				$('#symbol-check').html('<span class="label label-danger"><i class="icon-remove"></i> Invalid Stock Symbol</span>');
				$('#symbol-extra').val('1');
			}
			
			if(data == '1'){
				$('#symbol-check').html('<span class="label label-success"><i class="icon-ok"></i> Valid Stock Symbol</span>');
				$('#symbol-extra').val('0');
			}
		}
		
	});
	
	
	validateField('symbol', eIDs, 'submit-topic', 'Please provide a VALID stock symbol.');
});


$('#topic_title').focusout(function(){
	validateField('topic_title', eIDs, 'submit-topic', 'Please provide a TITLE for your topic.');
});

$('#topic_post').wysihtml5({
	events: {
		load: function() {
			var some_wysi = $('#topic_post').data('wysihtml5').editor;
			$(some_wysi.composer.element).bind('keyup', function(){ 
				validateField('topic_post-editor', eIDs, 'submit-topic', 'Please provide a post for your topic.');
			});
		}
	}
});
//+-------------------------------------------------------------------------------------+
//|							END: WYSIHTML5 jquery code									|
//+-------------------------------------------------------------------------------------+


$('form.create-topic').on('submit', function() {

	$.ajax({
        type: "POST",
        data: $(".create-topic").serialize(),
        url: "process/ajax/community-forum-processes.php?process=15",
        success: function(data)
        {
			//alert(data);
			getCategories('skip');
			
			$('#create-topic').modal('toggle');
			
			
			var shortCutFunction = 'success';
			var msg = 'Please click here to view your post.';
			var title = 'Discussion Started!';
			
			//Notification
			toastr.options = {
				closeButton: true,
				debug: false,
				positionClass: 'toast-top-right',
				timeOut: '4000'
			};
			
			toastr.options.onclick = function () {
				//alert('When notifiaction is clicked');
				window.location.href=data;
			};
			
			toastr[shortCutFunction](msg, title);
			
			
			//clear fields
			$('#symbol').val('');
			$('#topic_title').val('');
			$('#topic_post').data("wysihtml5").editor.clear();
     
        }
    });
	
	return false;
});

//+---------------------------------------------------------------------+
//|     			CREATE NEW CATEGORY - PROCESS 8						|
//+---------------------------------------------------------------------+

//Validate New Category Form
var eIDs2 = ["cat_title", "cat_desc"];

$('#cat_title').focusout(function(){
	validateField('cat_title', eIDs2, 'submit-cat');
});

$('#cat_desc').focusout(function(){
	validateField('cat_desc', eIDs2, 'submit-cat');
});



//Process Create Topic Form via AJAX
$('form.create-cat').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $(".create-cat").serialize(),
        url: "process/ajax/community-forum-processes.php?process=8",
        success: function(data)
        {
			if(data == ""){
				//validateCreateCat();
				getCategories();
				
				$('#create-category').modal('toggle');
				
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.info('New Category Successfully Created');
				
				$('#cat_title').val('');
				$('#cat_desc').val('');
			}else{
				//validateCreateCat();
				getCategories();
				
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
            $('#createCatUserError').html(data);
     
        }
    });
	
	return false;
});

//+---------------------------------------------------------------------+
//|     				UPDATE FORUM - PROCESS 19						|
//+---------------------------------------------------------------------+

var eIDs = ["forum_title", "forum_desc", "sequence"];

$('#forum_title').focusout(function(){
	validateField('forum_title', eIDs, 'submit-update-forum', 'Forum Title can not be left blank.');
});

$('#forum_desc').focusout(function(){
	validateField('forum_desc', eIDs, 'submit-update-forum', 'Forum Description can not be left blank.');
});

$('#sequence').focusout(function(){
	validateField('sequence', eIDs, 'submit-update-forum', 'You must provide a sequence number.');
});

     
//Process Create Forum Form via AJAX
$('form.update-forum').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $(".update-forum").serialize(),
        url: "process/ajax/community-forum-processes.php?process=19",
        success: function(data)
        {
			//alert(data);
			$('#update-forum-info-form').html(data);
			
			//Notification
			toastr.options = {
				closeButton: true,
				debug: false,
				positionClass: 'toast-top-right',
				timeOut: '4000'
			};
			  
			toastr.info('Forum Info Updated');
			
			
     
        }
    });
	
	return false;
});

	
<?php
}


//+---------------------------------------------------------------------------------------------+
//|									VIEW FORUM TOPICS PAGE										|
//+---------------------------------------------------------------------------------------------+ 
if($pageID == "04-01-00-003"){
?>

function loadTopicEditForm(topicID){
	$('form.change-topic-form').on('submit', function() {
		$.ajax({
			type: "POST",
			data: $(".change-topic-form").serialize(),
			url: "process/ajax/community-forum-processes.php?process=2-2",
			success: function(data)
			{	
				$('#edit-topic-'+topicID).addClass('hide');
				$('#edit-topic-link-'+topicID).removeClass('hide');
				$('#edit-topic-title-'+topicID).html(data);
				$('#edit-topic-'+topicID).html('');
				//Notification
				/*toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.info('New Topic Successfully Created');*/
				
		 
			}
		});
		
		return false;
	});
}

function editTopicTitle(topicID){
	
	var topicTitle = $('#edit-topic-title-'+topicID).html();
	
	$.ajax({
      url: 'process/ajax/community-forum-processes.php?process=2-1&topic_id='+topicID+'&topic_title='+topicTitle,
      success: function(data) {
        $('#edit-topic-'+topicID).html(data);
		$('#edit-topic-'+topicID).removeClass('hide');
		$('#edit-topic-link-'+topicID).addClass('hide');
		
		loadTopicEditForm(topicID);
      }
    
    });
}

function cancelEditTopic(topicID){
	$('#edit-topic-'+topicID).addClass('hide');
	$('#edit-topic-link-'+topicID).removeClass('hide');
	$('#edit-topic-'+topicID).html('');
}


//Edit forbes link form
function loadForbesEditForm(topicID,forbesUC){
	$('form#edit-forbes-form-'+topicID).on('submit', function() {
		$.ajax({
			type: "POST",
			data: $("#edit-forbes-form-"+topicID).serialize(),
			url: "process/ajax/community-forum-processes.php?process=2-3&forbesUC="+forbesUC,
			success: function(data)
			{	
				//$('#show-edit-forbes-error-'+topicID).html(data);
				$('#forbes-area-'+topicID).html(data);
				$('#reset-old-link-'+topicID).val(data);
				$('#edit-forbes-link-'+topicID).addClass('hide');
				
				//Notification
				/*toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.info('New Topic Successfully Created');*/
				
		 
			}
		});
		
		return false;
	});
}

function forbesLink(topicID,forbesUC){
	
	$('#edit-forbes-link-'+topicID).removeClass('hide');
	
	loadForbesEditForm(topicID,forbesUC);
	
	
}

function cancelEditForbes(topicID){
	$('#edit-forbes-link-'+topicID).addClass('hide');
}


function forbesUC(topicID,set){
	$.ajax({
      url: 'process/ajax/community-forum-processes.php?process=2-4&topic_id='+topicID+'&set='+set,
      success: function(data) {
        $('#forbes-area-'+topicID).html(data);
		
		if(set == '1'){
			$('#forbes-uc-'+topicID).html('<button class="btn btn-warning btn-xs btn-full" type="button" onclick="forbesUC(\''+topicID+'\',\'0\');"><i class="icon-upload-alt"></i> Remove Under Consideration</button>');	
		}
		if(set == '0'){
			$('#forbes-uc-'+topicID).html('<button class="btn btn-success btn-xs btn-full" type="button" onclick="forbesUC(\''+topicID+'\',\'1\');"><i class="icon-upload-alt"></i> Mark Under Consideration</button>');
		}
      }
    
    });	
}
//+---------------------------------------------------------------------+
//|     				AJAX LOAD TOPICS - PROCESS 2					|
//+---------------------------------------------------------------------+

var topicRefresh = <?php echo $_SESSION['support_ticket_refresh']; ?>;

var TableAdvanced2 = function () {
		
	var initTopicsTable = function() {
        var oTable = $('#forum-topics').dataTable( {           
            "oLanguage": {
				"sEmptyTable": "There are no topics in this category yet."
			},
			"aoColumnDefs": [
                //{ "aTargets": [ 0 ] },
				//{ "aDataSort": [ 5 ], "aTargets": [ 2 ] }
            ],
			"columnDefs":[
				{"targets": 2, "aDataSort": [ 5 ]}
			],
            "aaSorting": [[5, 'desc']], // set default column order 
             "aLengthMenu": [
                [15, 20, 25, 50 -1],
                [15, 20, 25, 50, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 20,
        });

        jQuery('#forum-topics_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#forum-topics_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#forum-topics_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#forum-topics_column_toggler input[type="checkbox"]').change(function(){
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
			
			
			initTopicsTable();
			
        }

    };

}();

$(document).ready(getTopics = function() {
    //Updates the Topics on the forum topics page
	$.ajax({
      url: 'process/ajax/community-forum-processes.php?process=2&forum=<?php echo $forumID;?>&cat=<?php echo $catID;?>&adminAccess=<?php echo $adminAccess;?>&active=1',
      success: function(data) {
        $('.load-topics').html(data);
		
		TableAdvanced2.init();
      }
    
    });
});

function getTopics2(){
	$.ajax({
      url: 'process/ajax/community-forum-processes.php?process=2&forum=<?php echo $forumID;?>&cat=<?php echo $catID;?>&adminAccess=<?php echo $adminAccess;?>&active=1',
      success: function(data) {
        $('.load-topics').html(data);
		
      }
    
    });
}

//var refreshInterval = setInterval(getTopics, topicRefresh);

$(document).ready(getInactiveTopics = function() {
    //Updates the Topics on the forum topics page
	$.ajax({
      url: 'process/ajax/community-forum-processes.php?process=2&forum=<?php echo $forumID;?>&cat=<?php echo $catID;?>&adminAccess=<?php echo $adminAccess;?>&active=0',
      success: function(data) {
        $('.load-inactive-topics').html(data);
      }
    
    });
});
var refreshInterval2 = setInterval(getInactiveTopics, topicRefresh);


function refreshTopics(){
	getTopics();
	getInactiveTopics();	
}



//+---------------------------------------------------------------------+
//|     						ADMIN SETTIINGS							|
//+---------------------------------------------------------------------+

$('#select_groups').multiSelect({
	selectableHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search...'>",
	selectionHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search...'>",
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

$('#select_members').multiSelect({
	selectableHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search...'>",
	selectionHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search...'>",
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


$('#select_member_types').multiSelect({
	selectableHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search...'>",
	selectionHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search...'>",
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

<?php
if($checkEveryone == 'checked'){?>
$("#access_types *").prop('disabled',true);
$('#access_types').fadeTo( "fast", 0.33 );
<?php } ?>

function showHideAccess(){
	checked = $('#allow_all').attr('checked');
	
	var index;
	var lists = ["select_groups", "select_member_types", "select_members"];
	
	if(checked == 'checked'){
		
		for (index = 0; index < lists.length; ++index) {
			$('#'+lists[index]+' option').prop('selected', true);
			$('#ms-'+lists[index]+' .ms-elem-selectable').css('display', 'none');
			$('#ms-'+lists[index]+' .ms-elem-selection').css('display', '');
			$('#ms-'+lists[index]+' .ms-elem-selection').addClass('ms-selected');
		}
		
		$("#access_types *").prop('disabled',true);
		$('#access_types').fadeTo( "fast", 0.33 );
		
		
	}else{
		
		for (index = 0; index < lists.length; ++index) {
			$('#'+lists[index]+' option').prop('selected', false);
			$('#ms-'+lists[index]+' .ms-elem-selectable').css('display', '');
			$('#ms-'+lists[index]+' .ms-elem-selection').css('display', 'none');
			$('#ms-'+lists[index]+' .ms-elem-selection').removeClass('ms-selected');
		}
		
		$('#access_types').fadeTo( "fast", 1 );
		//$("#access_types *").attr("disabled", "").off('click');
		$("#access_types *").prop('disabled',false);
		//alert('not checked');
	}
}

function updateAccess(catID){
	
	$('<div class="remove-process"></div>').css({
        position: "absolute",
        width: "100%",
        height: "100%",
        top: 0,
        left: 0,
        background: "#e2e2e2",
        opacity: 0.4
    }).appendTo($("#select_container").css("position", "relative"));
    
    $('<div class="remove-process"></div>').css({
        position: "absolute",
        width: "100%",
        height: "100%",
        top: 0,
        left: 0,
        background: "url(http://beta.marketocracy.com/images/ajax-loading.gif) no-repeat 50% 50%"
    }).appendTo($("#select_container").css("position", "relative"));
	
	$.ajax({
		type: "POST",
		data: $("#save_allow_access").serialize(),
		url: "process/ajax/community-forum-processes.php?process=17-2&cat="+catID,
		success: function(data){
			
			//alert(data);
			$('.remove-process').remove();
			
		}
	});	
}

function selectDeselect(type, list){
	
	if(type == 'select'){
		
		$('#'+list+' option').prop('selected', true);
		$('#ms-'+list+' .ms-elem-selectable').css('display', 'none');
		$('#ms-'+list+' .ms-elem-selection').css('display', '');
		$('#ms-'+list+' .ms-elem-selection').addClass('ms-selected');
		
		
	}
	if(type == 'deselect'){
		$('#'+list+' option').prop('selected', false);
		$('#ms-'+list+' .ms-elem-selectable').css('display', '');
		$('#ms-'+list+' .ms-elem-selection').css('display', 'none');
		$('#ms-'+list+' .ms-elem-selection').removeClass('ms-selected');
	}
	
}

//+---------------------------------------------------------------------+
//|     				CREATE NEW TOPIC - PROCESS 1					|
//+---------------------------------------------------------------------+

//Validate New Category Form
var eIDs = ["topic_title", "topic_post-editor"];

$('#topic_title').focusout(function(){
	validateField('topic_title', eIDs, 'submit-topic');
});

$('#topic_post').wysihtml5({
	events: {
		load: function() {
			var some_wysi = $('#topic_post').data('wysihtml5').editor;
			$(some_wysi.composer.element).bind('keyup', function(){ 
				validateField('topic_post-editor', eIDs, 'submit-topic');
			});
		}
	}
});

//Process Create Topic Form via AJAX
$('form.create-topic').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $(".create-topic").serialize(),
        url: "process/ajax/community-forum-processes.php?process=1",
        success: function(data)
        {
			if(data == ""){
				//validateCreateTopic();
				getTopics();
				
				$('#create-topic').modal('toggle');
				
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.info('New Topic Successfully Created');
				
				$('#topic_title').val('');
				$('#topic_post').data("wysihtml5").editor.clear();
			}else{
				//validateCreateTopic();
				getTopics();
				
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
            $('#createTopicUserError').html(data);
     
        }
    });
	
	return false;
});


//UPDATE CATEGORY
var eIDs = ["cat_title", "cat_desc", "sequence"];

$('#cat_title').focusout(function(){
	validateField('cat_title', eIDs, 'submit-update-cat', 'Category Title can not be left blank.');
});

$('#cat_desc').focusout(function(){
	validateField('cat_desc', eIDs, 'submit-update-cat', 'Category Description can not be left blank.');
});

$('#sequence').focusout(function(){
	validateField('sequence', eIDs, 'submit-update-cat', 'You must provide a sequence number.');
});

     
//Process Create Forum Form via AJAX
$('form.update-cat').on('submit', function() {
	
	$('<div class="remove-process"></div>').css({
        position: "absolute",
        width: "100%",
        height: "100%",
        top: 0,
        left: 0,
        background: "#e2e2e2",
        opacity: 0.4
    }).appendTo($("#update-cat-area").css("position", "relative"));
    
    $('<div class="remove-process"></div>').css({
        position: "absolute",
        width: "100%",
        height: "100%",
        top: 0,
        left: 0,
        background: "url(http://beta.marketocracy.com/images/ajax-loading.gif) no-repeat 50% 50%"
    }).appendTo($("#update-cat-area").css("position", "relative"));
	
	$.ajax({
        type: "POST",
        data: $(".update-cat").serialize(),
        url: "process/ajax/community-forum-processes.php?process=19-2",
        success: function(data)
        {
			//alert(data);
			$('#update-cat-info-form').html(data);
			
			//Notification
			toastr.options = {
				closeButton: true,
				debug: false,
				positionClass: 'toast-top-right',
				timeOut: '4000'
			};
			  
			toastr.info('Category Info Updated');
			
			$('.remove-process').remove();
			
			
     
        }
    });
	
	return false;
});
	
<?php
}


//+---------------------------------------------------------------------------------------------+
//|									VIEW FORUM POSTS PAGE										|
//+---------------------------------------------------------------------------------------------+ 
if($pageID == "04-01-00-004"){
	
$child = $_REQUEST['child'];
$pn = $_REQUEST['pn'];
$catID = $_REQUEST['cat'];
$post = $_REQUEST['post'];

if($pn != ''){
	$pn = '&pn='.$pn.'';
}else{
	$pn = '';	
}
?>


function loadBadges(memberID, name){
	$.ajax({
		url: "process/ajax/community-forum-processes.php?process=20&member="+memberID+'&name='+name,
		success: function(data)
		{
			$('#load-badges').html(data);
		}
	});
}


function copyToClipboard(text) {
  window.prompt("Copy to clipboard: Ctrl+C, Enter", text);
}


//Mark post as viewed
$(document).ready(viewPost = function() {
	$.ajax({
		url: "process/ajax/community-forum-processes.php?process=13&post=<?php echo $_REQUEST['post']; ?>&topic=<?php echo $topicID; ?>",
		success: function(data)
		{
			//alert(data);
			if(data == ""){
								
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.info('Post Viewed');
			}else{
				
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.error('Post not viewed.');
				
			};
			
		}
	});
});
//+---------------------------------------------------------------------+
//|     				AJAX LOAD POSTS - PROCESS 3						|
//+---------------------------------------------------------------------+
var postsRefresh = <?php echo $_SESSION['support_ticket_refresh']; ?>;


$(document).ready(getPosts = function(stay) {
    //Updates the Topics on the forum topics page
	
	$.fn.scrollView = function () {
	  return this.each(function () {
		$('html, body').animate({
		  scrollTop: $(this).offset().top
		}, 500);
		;
	  });
	}
	$.ajax({
      url: 'process/ajax/community-forum-processes.php?process=3&topic=<?php echo $topicID;?>&page=<?php echo $pageID; ?>&forum=<?php echo $forumID; ?>&cat=<?php echo $catID;?>&child=<?php echo $child; echo $pn;?>&post=<?php echo $post;?>',
      success: function(data) {
        $('#community-forum-posts-ajax').html(data);
		
		<?php 
		
		if($child != ''){
			if($post != ''){
				?>
				if(stay != '1'){
					$('#post-id-<?php echo $post;?>').scrollView();
					
					$('html, body').animate({scrollTop: '-=45px'}, 500);
					
				}
				$('.response-header-<?php echo $child; ?>').css({"border-bottom-color": "#ED9C28", "border-bottom-width": "3px", "border-bottom-style": "solid"});
				<?php 
			}else{
				?>
				if(stay != '1'){
					$('#post-id-<?php echo $child;?>').scrollView();
					$('html, body').animate({scrollTop: '-=45px'}, 500);
					
				}
				$('.response-header-<?php echo $child; ?>').css({"border-bottom-color": "#ED9C28", "border-bottom-width": "3px", "border-bottom-style": "solid"});
				<?php 
			}
		}else{
			if($post != ''){
				?>
				if(stay != '1'){
					$('#post-id-<?php echo $post;?>').scrollView();
					$('html, body').animate({scrollTop: '-=45px'}, 500);
					
				}
				<?php 
			}
		}?>
      }
    
    });
});
var refreshInterval = setInterval(getPosts, postsRefresh);


function refreshPosts(){
		
	getPosts();
		
}


//+---------------------------------------------------------------------+
//|     				CREATE NEW POST - PROCESS 4						|
//+---------------------------------------------------------------------+

var eIDs = ["post"];

$('#post').wysihtml5({
	events: {
		load: function() {
			var some_wysi = $('#post').data('wysihtml5').editor;
			$(some_wysi.composer.element).bind('keyup', function(){ 
				validateField('post', eIDs, 'submit-post');
			});
		}
	}
});

//Process Create Topic Form via AJAX
$('form.create-post').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $(".create-post").serialize(),
        url: "process/ajax/community-forum-processes.php?process=4",
        success: function(data)
        {
			if(data == ""){
				//validateCreatePost();
				getPosts();
				
				$('#create-post').modal('toggle');
				$('#post').data("wysihtml5").editor.clear();
				
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.info('New Post Successfully Created');
			}else{
				//validateCreatePost();
				getPosts();
				
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
            $('#createPostUserError').html(data);
     
        }
    });
	
	return false;
});

//+---------------------------------------------------------------------+
//|     			FORUM POST REPLY - PROCESS 5						|
//+---------------------------------------------------------------------+


function validateResponseReply(uid){
	var a=document.forms["reply-"+uid]["post-reply"].value;
		
	if (a==null || a==""){
		$('#reply-'+uid).css({
			"border-color": "#b94a48",
			"border-width": "1px",
			"border-style": "solid"	
		});
	}
	
	
	if (a!=""){
		$('#reply-'+uid).css({
			"border-color": "#e5e5e5",
			"border-width": "1px",
			"border-style": "solid"	
		});
	}
	
	
}

//Process Response Reply Form via AJAX
function postReply(uid, convoID){
	
	$('form.reply-'+uid).on('submit', function() {
		
		//alert(uid);
		$.ajax({
			type: "POST",
			data: $(".reply-"+uid).serialize(),
			url: "process/ajax/community-forum-processes.php?process=5",
			success: function(data)
			{
				//alert(data);
				if(data == ""){
					validateResponseReply(uid);
					getPosts('1');
					
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
					//getPosts();
					//Notification
					toastr.options = {
						closeButton: true,
						debug: false,
						positionClass: 'toast-top-right',
						timeOut: '4000'
					};
					  
					toastr.error('Please fill in required fields.');
					
					$('#postUserError-'+uid).append(data);
				};
				
			}
		});
		
		return false;
	});
}

//+---------------------------------------------------------------------+
//|     			FORUM AGREE/DISAGREE - PROCESS 6					|
//+---------------------------------------------------------------------+

//Function to control the Agree/Disagree Buttons
function agreeDisagree(choice, uid, member, topic){
	$.ajax({
      url: 'process/ajax/community-forum-processes.php?process=6&uid=' + uid + '&choice=' + choice + '&member=' + member + '&topic=' + topic,
      success: function(data) {
        //alert(data);
		//$('#response-ajax').html(data);
		getPosts('1');
		
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

//+---------------------------------------------------------------------+
//|     			FORUM DELETE POST - PROCESS 7						|
//+---------------------------------------------------------------------+

//This function deletes or restores a POST
function deleteReply(uid, action){
	
	if(action == 'delete'){
		var check=confirm("Are you sure you wish to remove this reply?");
	}else if(action == 'restore'){
		check=confirm("Are you sure you wish to restore this reply?");
	}
	
	//alert(uid + '|' + action);
	
	if(check == true){
		//DEBUG
		//alert("Deleted");
		$.ajax({
			url: 'process/ajax/community-forum-processes.php?process=7&uid=' + uid + '&action=' + action,
			success: function(data) {
				getPosts('1');
				
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

//+---------------------------------------------------------------------+
//|     			FORUM EDIT POST - PROCESS 10						|
//+---------------------------------------------------------------------+

function validatePostEdit(uid){
	var a=document.forms["edit-"+uid]["post-edit"].value;
		
	if (a==null || a==""){
		$('#post-edit-'+uid).css({
			"border-color": "#b94a48",
			"border-width": "1px",
			"border-style": "solid"	
		});
	}
	
	
	if (a!=""){
		$('#post-edit-'+uid).css({
			"border-color": "#e5e5e5",
			"border-width": "1px",
			"border-style": "solid"	
		});
	}
	
	
}

//Process Response Reply Form via AJAX
function postEdit(uid){
	
	$('form.edit-'+uid).on('submit', function() {
		
		//alert(uid);
		$.ajax({
			type: "POST",
			data: $(".edit-"+uid).serialize(),
			url: "process/ajax/community-forum-processes.php?process=10",
			success: function(data)
			{
				//alert(data);
				if(data == ""){
					validatePostEdit(uid);
					getPosts('1');
					
					//Notification
					toastr.options = {
						closeButton: true,
						debug: false,
						positionClass: 'toast-top-right',
						timeOut: '4000'
					};
					  
					toastr.info('Edit Successfully Submitted');
				}else{
					validatePostEdit(uid);
					
					//Notification
					toastr.options = {
						closeButton: true,
						debug: false,
						positionClass: 'toast-top-right',
						timeOut: '4000'
					};
					  
					toastr.error('Please fill in required fields.');
					
					$('#editUserError-'+uid).append(data);
				};
				
			}
		});
		
		return false;
	});
}


//+---------------------------------------------------------------------+
//|     					Misc. Functions								|
//+---------------------------------------------------------------------+

//Show Reply Field Button | When Reply button on response is clicked, the reply field will be shown.
function showReply(postID){
	//alert(uid);
	$('#replyForm-' + postID).attr('style', '');
}

//When the cancel button is pressed on the reply field, the reply field will be hidden
function hideReply(postID){
	$('#replyForm-' + postID).attr('style', 'display:none;');	
}

//Show Edit Field Button | When Edit button on response is clicked, the edit field will be shown.
function showEdit(postID){
	//alert(uid);
	$('#editForm-' + postID).attr('style', '');
}

//When the cancel button is pressed on the edit field, the edit field will be hidden
function hideEdit(postID){
	$('#editForm-' + postID).attr('style', 'display:none;');	
}

//HashTag Checking
/*$(document).ready(function() {
	$(document).keydown(function(e) {
		if (e.keyCode == '32') {
			
			//alert('space');
			
			$.ajax({
				type: "POST",
				data: $(".create-post").serialize(),
				url: 'process/ajax/community-forum-processes.php?process=14',
				success: function(data) {
					alert(data);
					
					//$('#post').html(data);
				}
			
			});
		}
	});
});*/



<?php
}

//+---------------------------------------------------------------------------------------------+
//|									VIEW UNREAD POSTS PAGE										|
//+---------------------------------------------------------------------------------------------+ 
if($pageID == "04-01-00-006"){
?>

//Process Mark Posts as Read
function markRead(){
	
	
	var check=confirm("Are you sure you wish to mark all of these post as read?");
	
	if(check == true){
		$("#mark-read").submit(function(e){
			
			//alert(uid);
			$.ajax({
				type: "POST",
				data: $("#mark-read").serialize(),
				url: "process/ajax/community-forum-processes.php?process=13&form=1",
				success: function(data)
				{
					//alert(data);
					if(data == ""){
						
						//Notification
						toastr.options = {
							closeButton: true,
							debug: false,
							positionClass: 'toast-top-right',
							timeOut: '2000'
						};
						  
						toastr.info('Post have been marked as read.');
						
						setTimeout(function(){location.reload();}, 2000);
						
					}else{
						
						//Notification
						toastr.options = {
							closeButton: true,
							debug: false,
							positionClass: 'toast-top-right',
							timeOut: '4000'
						};
						  
						toastr.error('There was an error, please try again.');
						
					};
					
				}
			});
			
			return false;
		});
		
		$('#mark-read').submit();
		
		
	}
}
	
<?php
}
?>



<?php
//+---------------------------------------------------------------------------------------------+
//|									VIEW UNREAD TOPICS PAGE										|
//+---------------------------------------------------------------------------------------------+ 
if($pageID == "04-01-00-005"){?>
var unreadTable = function () {
		
	var initUnreadTable = function() {
        var oTable = $('#unread-topics').dataTable( {           
            "oLanguage": {
				"sEmptyTable": "You have no unread topics."
			},
			"aoColumnDefs": [
                { 
					"aTargets": [ 0 ]/*,
					"fnRender": function (o, v){
						return '<input type="checkbox" class="selectAll" name="someCheckbox[]" />';	
					}*/
				}
            ],
            "aaSorting": [[3, 'desc']], // set default column order 
             "aLengthMenu": [
                [15, 20, 25, 50 -1],
                [15, 20, 25, 50, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 20,
        });
		
		

        jQuery('#unread-topics_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#unread-topics_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#unread-topics_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('#unread-topics_column_toggler input[type="checkbox"]').change(function(){
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
            oTable.fnSetColumnVis(iCol, (bVis ? false : true));
        });
		
		/*$('.checkall', oTable.fnGetNodes()).click(function () {
			 var checkall =$(this).parents('.box:eq(0)').find(':checkbox').attr('checked', this.checked);
			$.uniform.update(checkall);
		});*/
    }

    return {

        //main function to initiate the module
        init: function () {
            
            if (!jQuery().dataTable) {
                return;
            }
			
			initUnreadTable();
			
        }

    };

}();

/*$(document).ready(loadUnreadTable = function() {
	unreadTable.init();
	
	var datatable = $('#unread-topics').dataTable();
	
	$(".checkall").click(function(event) {
        event.preventDefault();
        var nodes = datatable.fnGetNodes( );
        $('.selectAll', nodes).prop("checked", true);
    });
	
});*/

/*function checkAll(){
	
	//alert('clicked');
	
	var checkAll = $('#checkall').val();
	
	alert(checkAll);
	
	if(checkAll == 'yes'){
		$('.select-all').prop('checked', true);	
		alert('checked');
	}else{
		alert('not checked');
		$('.select-all').prop('checked', false);	
	}
}*/

$('form.mark-viewed-form').on('submit', function() {
	
	//alert(uid);
	$.ajax({
		type: "POST",
		data: $(".mark-viewed-form").serialize(),
		url: "process/ajax/community-forum-processes.php?process=21",
		success: function(data)
		{
			$('#mark-read-error').html(data);
			
			if(data == ''){
				location.reload();	
			}
			
		}
	});
	
	return false;
});

<?php	
}

?>

function lockLevel(level,elementID,type){

	$.ajax({
		type: "POST",
		url: "process/ajax/community-forum-processes.php?process=22&element="+elementID+"&type="+type+'&level='+level,
		success: function(data)
		{
			
			//Notification
			toastr.options = {
				closeButton: true,
				debug: false,
				positionClass: 'toast-top-right',
				timeOut: '2000'
			};
			  
			toastr.info(data);
			
			if(level == 'topic'){
				getTopics2();	
				//window.setTimeout(function(){location.reload()},2000);
			}
			
		}
	});
	
}

function activate(activate, level, levelID){
	
	if(activate == 'false'){
		var check=confirm("Are you sure you wish to remove this "+level+"?");
	}else{
		var check=confirm("Are you sure you wish to activate this "+level+"?");
	}
	
	if(check == true){
		$.ajax({
			type: "POST",
			data: $("#save_allow_access").serialize(),
			url: "process/ajax/community-forum-processes.php?process=18&activate="+activate+"&level="+level+"&levelID="+levelID,
			success: function(data){
				
				location.reload();
				
				//$('.remove-process').remove();
				
			}
		});	
	}
}
</script>