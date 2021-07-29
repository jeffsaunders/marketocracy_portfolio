function addToDash(params, pid){
	if (window.XMLHttpRequest){ // Code for IE7+, Firefox, Opera, Webkit (Chrome, Safari, Rockmelt)
		xmlhttp=new XMLHttpRequest();
	}else{ // Code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	//START NOTIFICATION
	var i = -1,
	toastCount = 0,
	$toastlast
	var shortCutFunction = 'success';
	var msg = 'Click Here to view your Dashboard.';
	var title = 'Portlet Added To Dashboard';
	var toastIndex = toastCount++;
	
	toastr.options = {
		closeButton: true,
		debug: false,
		positionClass: 'toast-top-right',
		timeOut: '2500'
	};
	
	toastr.options.onclick = function () {
		//alert('When notifiaction is clicked');
		window.location.href="/";
	};
	
	var $toast = toastr[shortCutFunction](msg, title);
	
	$toastlast = $toast;
	
	$('#clearlasttoast').click(function () {
		toastr.clear($toastlast);
	});
	//END NOTIFICATION
	
	xmlhttp.open("GET","/process/ajax/portlet-add-dashboard.php?"+params);
	xmlhttp.send();
	xmlhttp.onreadystatechange = function(){
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
			document.getElementById("update-button-"+pid).innerHTML = xmlhttp.responseText;
		}

	}
}
function removeFromDash(params,updateCol){
	if (window.XMLHttpRequest){ // Code for IE7+, Firefox, Opera, Webkit (Chrome, Safari, Rockmelt)
		xmlhttp=new XMLHttpRequest();
	}else{ // Code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	//START NOTIFICATION
	var i = -1,
	toastCount = 0,
	$toastlast
	var setColumns = updateCol;
	var shortCutFunction = 'warning';
	var msg = 'If this was an error Click Here to UNDO.';
	var title = 'Portlet Removed From Dashboard';
	var $showDuration = '1000';
	var $hideDuration = '1000';
	var $timeOut = '10000';
	var $extendedTimeOut = '1000';
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

	toastr.options.onclick = function () {
		//alert(updateCol);
		xmlhttp.open("GET","/process/ajax/portlet-add-dashboard.php?"+setColumns+"&undo=1");
		xmlhttp.send();
		xmlhttp.onreadystatechange = function(){
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
				//document.getElementById("status-update").innerHTML = xmlhttp.responseText;
				var response = xmlhttp.responseText;
				if (response == 'reload'){
					location.reload();
				}
			}

		}
	};

	var $toast = toastr[shortCutFunction](msg, title);

	$toastlast = $toast;

	$('#clearlasttoast').click(function () {
		toastr.clear($toastlast);
	});
	//END NOTIFICATION

	xmlhttp.open("GET","/process/ajax/portlet-remove-dashboard.php?"+params);
	xmlhttp.send();
	xmlhttp.onreadystatechange = function(){
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
			//document.getElementById("status-update").innerHTML = xmlhttp.responseText;
		}

	}
}