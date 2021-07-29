<?php
/*
AJAX to kick off the heartbeat to determine who's logged in
Included (conditionally) at the bottom of index.php
*/
?>
<!-- Start heartbeat -->
<script>
$(document).ready(heartbeat = function() {
	$.ajax({
		url: '/process/ajax/heartbeat.php'
	});
});
var refreshInterval = setInterval(heartbeat, <?php echo $_SESSION['heartbeat_interval'];?>);
</script>
