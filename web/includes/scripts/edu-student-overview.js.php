<?php
/*
Marketocracy Inc. | 
My Classes Overview Scripts

Supporting files:
	AJAX		- process/ajax/edu-student-overview-porcesses.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/edu-student-overview.js.php
	HTML		- includes/pages/edu-student-overview.php
*/
?>
<!--EDU CLASSES-->


<script>


$('#class_code').keyup(function () {
	
	var code = $(this).val();
	
	
	
	$.ajax({
        type: "POST", /*form method*/
        data: {process:'code-check',code:code}, 
        url: "process/ajax/edu-student-overview-processes.php", /*location of php processing code, Note: this uses a process variable to distinguish between different processes*/
        
		success: function(data)
        {
			//Change the submit button to display that the settings have been saved
			$('#class_code_details').html(data);
			if((data.indexOf('alert-danger') > -1) == true){
				$('#class_code').css('border-color', '#B94A48');
				$('#class_code_label').css('color', '#B94A48');
			}else{
				$('#class_code').css('border-color', '#356635');
				$('#class_code_label').css('color', '#356635');
			}			
			
        }
    });
	
});

$('form.join-class').on('submit', function() {
	
	
	
	$.ajax({
        type: "POST",
        data: $(".join-class").serialize(),
        url: "process/ajax/edu-student-overview-processes.php?process=submit-join-class",
        success: function(data)
        {
			
			if((data.indexOf('danger') > -1) == true){
				$('#joinClassError').html(data);
			}else{
				location.reload();
			}
			
			
            
			
            
     
        }
    });
	
	return false;
});	

//init table
var ClassTable = function () {
		
	var initClassTable = function() {
        var oTable = $('#class-list').dataTable( {           
            "oLanguage": {
				"sEmptyTable": "You are not enrolled into a class.",
				"sLengthMenu": "Display _MENU_ Classes",
			},
			"aoColumnDefs": [
                { "aTargets": [ 0 ] }
            ],
            "aaSorting": [[4, 'asc']], // set default column order 
             "aLengthMenu": [
                [15, 20, 25, 50 -1],
                [15, 20, 25, 50, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 20,
        });

        jQuery('#class-list_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        jQuery('#class-list_wrapper .dataTables_length select').addClass("form-control input-small"); // modify table per page dropdown
        jQuery('#class-list_wrapper .dataTables_length select').select2(); // initialize select2 dropdown

        $('.class-list_column_toggler input[type="checkbox"]').change(function(){
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
			
			initClassTable();
			
        }

    };

}();

</script>