$('form.ajax').on('submit', function() {
		
	var data = { 'checkbox[]' : []};
	$("form.ajax :checked").each(function() {
		data['checkbox[]'].push($(this).val());
	});
	//alert(data);
	console.log(data);
	
	$.post("process/ajax/system-notification-settings.php", data, function(response){
		
		$('#note-settings').html('<input type="submit" value="Saved" class="btn btn-default">');
		
		
		//Notification
		toastr.options = {
			closeButton: true,
			debug: false,
			positionClass: 'toast-top-right',
			timeOut: '1000'
		};
		  
		var $timeOut = '100';
		toastr.success('Notification Settings Saved')			
	});
	
	return false;
});

function updateSave(){
	$('#note-settings').html('<input type="submit" value="Save Settings" class="btn btn-success">');
}