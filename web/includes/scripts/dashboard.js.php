<script>
//Dashboard Scripts
function clearNotifications(memberID){
	
	if (confirm('Are you sure you want to clear all unflagged notifications?')) {
		$.ajax({
			url: 'process/ajax/dashboard-processes.php?process=1&member='+memberID,
			success: function(data) {
				//$('.load-inactive-forums').html(data);
				//alert(data);
				notifications();
			}
		
		});
	} else {
		// Do nothing!
	}
	
	
	
}



</script>