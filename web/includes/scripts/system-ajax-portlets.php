<!--START AJAX PORTLETS-->
<script>
//START NOTIFICATION PORTLET SCRIPT: 
//JS FILE: SYSTEM-AJAX-PORTLETS.PHP | LOCATION: INCLUDES/SCRIPTS

//Refresh Notifications button | Portlet Tool
function refreshNote(){
	notifications();
}

//Notification Refresh rate
var notificationRefresh = 30000;


$(document).ready(notifications = function() {
    //Included in the index.php, this function updates the toolbar for notifications
	$.ajax({
      url: '/includes/portlets/ajax/notifications-ajax-toolbar.php',
      success: function(data) {
        $('.notification-ajax-toolbar').html(data);
      }
    
    });
	$.ajax({
      url: '/includes/portlets/ajax/notifications-ajax.php',
      success: function(data) {
        $('.notification-ajax-all').html(data);
      }
    
    });
	$.ajax({
      url: '/includes/portlets/ajax/notifications-ajax.php?section=12-001',
      success: function(data) {
        $('#notification-ajax-ca').html(data);
      }
    
    });
	$.ajax({
      url: '/includes/portlets/ajax/notifications-ajax.php?section=11-001',
      success: function(data) {
        $('#notification-ajax-system').html(data);
      }
    
    });
	$.ajax({
      url: '/includes/portlets/ajax/notifications-ajax-counter.php',
      success: function(data) {
        $('.note-counter').html(data);
		if(data != ''){
			document.title = "(" + data + ") <?php echo $_SESSION['site_name'];?> | <?php echo $pageTitle; ?>";
		}
      }
    
    });
	
	$.ajax({
      url: '/includes/portlets/ajax/notifications-ajax-counter.php?showZero=1',
      success: function(data) {
        $('.note-counter2').html('You have ' + data + ' new notifications');
		$('.note-counter3').html('<span class="badge badge-danger">' + data + '</span>');
		$('.note-counter4').html(data);
		
      }
	  
    
    });
	$.ajax({
      url: '/includes/portlets/ajax/notifications-ajax-counter.php?section=12-001',
      success: function(data) {
        $('.note-counter-ca').html(data);
      }
    
    });
	$.ajax({
      url: '/includes/portlets/ajax/notifications-ajax-counter.php?section=11-001',
      success: function(data) {
        $('.note-counter-sys').html(data);
      }
    
    });
	
	$.ajax({
      url: '/includes/portlets/ajax/notifications-ajax-flag-counter.php',
      success: function(data) {
		//var response = html(data);
		
        $('.flag-counter').html(data);
      }
    
    });
	$.ajax({
      url: '/includes/portlets/ajax/notifications-ajax-flag-counter.php?section=12-001',
      success: function(data) {
		//var response = html(data);
		
        $('.flag-counter-ca').html(data);
      }
    
    });
	
	$.ajax({
      url: '/includes/portlets/ajax/notifications-ajax-flag-counter.php?section=11-001',
      success: function(data) {
		//var response = html(data);
		
        $('.flag-counter-sys').html(data);
      }
    
    });
	
	/*$.ajax({
      url: 'test.php',
      success: function(data) {
        $('#nasdaq-feeds').html(data);
      }
    
    });*/
	
});
var refreshInterval = setInterval(notifications, notificationRefresh);

function removeNote(params, noteID){
	if (window.XMLHttpRequest){ // Code for IE7+, Firefox, Opera, Webkit (Chrome, Safari, Rockmelt)
		xmlhttp=new XMLHttpRequest();
	}else{ // Code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	//alert(noteID);	
	xmlhttp.open("GET","/includes/portlets/ajax/notifications-remove.php?"+params);
	xmlhttp.send();
	xmlhttp.onreadystatechange = function(){
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
			//document.getElementById(noteID).innerHTML = xmlhttp.responseText;
			notifications();
		}

	}
}

function viewNote(params, noteID, noteID2){
	if (window.XMLHttpRequest){ // Code for IE7+, Firefox, Opera, Webkit (Chrome, Safari, Rockmelt)
		xmlhttp=new XMLHttpRequest();
	}else{ // Code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	//alert(noteID2);	
	xmlhttp.open("GET","/includes/portlets/ajax/notifications-view.php?"+params);
	xmlhttp.send();
	xmlhttp.onreadystatechange = function(){
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
			//document.getElementById(noteID).innerHTML = xmlhttp.responseText;
			notifications();
		}
		$.ajax({
		  url: '/includes/portlets/ajax/notifications-ajax.php',
		  success: function(data) {
			$('.notification-ajax-all').html(data);
		  }
		
		});
	}
}
//END NOTIFICATION PORTLET SCRIPT

</script>

<!--END AJAX PORTLETS-->