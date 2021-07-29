<!--START AJAX Counters-->
<!--includes/scripts/system-ajax-counters-->
<script>

var notificationRefresh = 30000;

$(document).ready(notifications = function() {
    $.ajax({
      url: '/includes/portlets/ajax/notifications-ajax-toolbar.php',
      success: function(data) {
        $('.notification-ajax-toolbar').html(data);
		
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

	}
}


</script>

<!--END AJAX PORTLETS-->