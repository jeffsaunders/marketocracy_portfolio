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
function toggleID(id){
	$('#'+id).toggle();	
}






var initTrackersTable = function() {
	var oTable = $('#trackers_table').dataTable( {           
		oLanguage: {
			"sEmptyTable": "You currently do not have any trackers."
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
	
	

	

	jQuery('#trackers_table_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
	jQuery('#trackers_table_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
	jQuery('#trackers_table_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

	$('#trackers_table_column_toggler input[type="checkbox"]').change(function(){
		/* Get the DataTables object again - this is not a recreation, just a get of the object */
		var iCol = parseInt($(this).attr("data-column"));
		var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
		oTable.fnSetColumnVis(iCol, (bVis ? false : true));
	});
	
	
}

function loadTrackers(){
	
	//init:initTrackingTable();
	
	$.ajax({
        type: "POST", 
        data: {process: 'load-trackers', memberID: '<?php echo $_SESSION['member_id'];?>'},
        url: "process/ajax/action-trackers-processes.php",
		success: function(data){
			
			$('#load-trackers').html(data);
			
			init:initTrackersTable();
			
				
		}
	});
}
	
$('form.edit-tracker-code-form').on('submit', function(e) {
		
		e.preventDefault();
		
		$('#save-tracker-btn').html('<button type="button" class="btn btn-default">Processing...</button>');
	
		//process php
		$.ajax({
			type: "POST", 
			data: $(".edit-tracker-code-form").serialize(),
			url: "process/ajax/action-trackers-processes.php?process=save-tracker-code",
			
			success: function(data)
			{
				if (data.indexOf("note-danger") !=-1) {
					$('#save-tracker-code-error').html(data);
					$('#save-tracker-btn').html('<input type="submit" value="Save Code" id="submit-tracker-code" class="btn btn-info btn-sm" />');
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
		

$(document).ready(function() {
	
	loadTrackers();
	//loadTrackers();
	//loadListss();
	//loadArticleForm();
	
	
});








</script>