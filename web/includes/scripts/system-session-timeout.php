<script type="text/javascript">

// Set timer to configuration value
var idleSeconds = <?php echo (!$_SESSION['flag_admin'] || isset($_SESSION['admin_id']) ? $_SESSION['inactivity_timeout'] : $_SESSION['admin_inactivity_timeout']); ?>;

// Activity monitor
$(document).ready(function(){
	var idleTimer;
	// Function to (re)start the timer
	function resetTimer(){
		clearTimeout(idleTimer);
		idleTimer = setTimeout(popNotification, idleSeconds * 1000);
	}
	// If the timer is not 0 watch for keystrokes and mouse movement
	if (idleSeconds > 0){ // A setting of zero (or less) disables it
		$(this).mousemove(function(e){resetTimer();});  // Reset timer on mouse movement
		$(this).keypress(function(e){resetTimer();});  // Reset timer on keystroke
		resetTimer(); // Start the timer when the page loads
	}
});

// Pop up a notification that, if clicked, cancels the automatic logout, otherwise they are outta here.
function popNotification() {

	// Start a timer for cancellation period (time to allow them to cancel)
	var timerStart = new Date().getTime();

	//START NOTIFICATION
	var i = -1,
	toastCount = 0,
	$toastlast
	var shortCutFunction = 'warning';
	var msg = '<span style="font-size:16px; font-weight:bold">Your session is about to expire. <u>CLICK HERE</u> to stay logged in.</span>';
	var title = '<span style="font-size:20px; font-weight:bold">Session Expiration Warning</span>';
	var $showDuration = '1000';
	var $hideDuration = '1000';
	var $timeOut = '<?php echo ($_SESSION['logout_warning_timeout'] * 1000); ?>';
	var $extendedTimeOut = '0';
	var $showEasing = 'swing';
	var $hideEasing = 'linear';
	var $showMethod = 'fadein';
	var $hideMethod = 'fadeOut';
	var toastIndex = toastCount++;

	toastr.options = {
		closeButton: true,
		debug: false,
		positionClass: 'toast-top-full-width',
		showDuration: $showDuration,
		hideDuration: $hideDuration,
		timeOut: $timeOut,
		extendedTimeOut: $extendedTimeOut
	};

	// What to do if they click to cancel
	toastr.options.onClick = function () {
		timerStart = new Date().getTime();  // Start the timer over, that's it.
	};

	// What to do if the notification closes/is closed
	toastr.options.onHidden = function (){
		var timerNow = new Date().getTime();  // What time is it now?
		// If now minus the overall timeout is past the time we started, it timed out so log them out
		if ((timerNow - $timeOut) >= timerStart){
			// Log them out!
	        window.location = '/session-timeout';
		}
		// Otherwise, assume they clicked it so do nothing and just let the timer keep running
    }

	var $toast = toastr[shortCutFunction](msg, title);

	$toastlast = $toast;

	$('#clearlasttoast').click(function () {
		toastr.clear($toastlast);
	});
	//END NOTIFICATION

}

</script>
