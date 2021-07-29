<?php
/*
Marketocracy Inc. | Beta Development
Admin campaigns

Supporting files:
	AJAX		- process/ajax/admin-auto-campaigns-processes.php
	JAVASCRIPT	- THIS DOCUMENT JAVASCRIPT includes/scripts/admin-auto-campaigns.js.php
	HTML		- includes/pages/admin-auto-campaigns.php
*/
$processLink	= 'admin-auto-campaigns-processes.php';

?>

<script type="text/javascript">

function toggleID(id){
	$('#'+id).toggle();	
}
$('#send-test-check').on('click', function(){
	
	if($(this).is(':checked')){
        $('#email-processing-btn').html('<input type="submit" value="Send Test Email" id="submit-email" class="btn btn-success" />');
	}else{
        $('#email-processing-btn').html('<input type="submit" value="Save Campaign" id="submit-email" class="btn btn-info" />');
	}
	//alert(sendTest);
	
});

/*$('.email-body').wysihtml5({
	"html": true,
	"link": true
	
});*/

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

function testEmail(memberID, campID){
	$.ajax({
		type: "POST", 
		data: {memberID:memberID, campID:campID, process:'test-auto-email'},
		url: "process/ajax/<?php echo $processLink;?>",
		
		success: function(data)
		{
			
			$('#email-test-errors').html(data);
			
			
		}
	});
}

$('form.create-campaign-form').on('submit', function() {
	
	$('#campaign-processing-btn').html('<button type="button" class="btn btn-default">Processing...</button>');
	
	//process php
	$.ajax({
		type: "POST", 
		data: $(".create-campaign-form").serialize(),
		url: "process/ajax/<?php echo $processLink;?>?process=create-email-campaign",
		
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
			url: "process/ajax/<?php echo $processLink;?>?process=send-campaign",
			
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
        url: "process/ajax/<?php echo $processLink;?>",
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
			url: "process/ajax/<?php echo $processLink;?>?process=save-email-campaign",
			
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


function loadSendEmail(campID){
	$.ajax({
		type: "POST", 
		data: {campID:campID, process: 'load-send-email'},
		url: "process/ajax/<?php echo $processLink;?>",
		
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
		url: "process/ajax/<?php echo $processLink;?>",
		
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
		url: "process/ajax/<?php echo $processLink;?>",
		
		success: function(data)
		{
			
			$('#load-edit-email').html(data);
			
			/*$('#email-body-'+campID).wysihtml5({
				events: {
					load: function() {
						var some_wysi = $('#email-body-'+campID).data('wysihtml5').editor;
						
					}
				}
			});*/
			
			loadEditEmailForm();
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
		url: "process/ajax/<?php echo $processLink;?>",
		
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


$(document).ready(function() {
	
	//loadTrackedManagers();
	//loadTopTenStocks();
	//loadArticles();
	loadEmails(0);
	//loadTrackers();
	//loadArticleForm();
	
});


</script>