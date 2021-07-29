//--------------------------------------------------------
//				QUICK TRADE SCRIPTS
//--------------------------------------------------------

function processQuickTrade(){
	//alert(index);
	$('.quick-trade-errors').html('<div class="alert alert-block alert-success fade in"><button type="button" class="close" data-dismiss="alert"></button><img src="/images/loading.gif" /> Processing Trade...</div>');
	
	$.ajax({
		type: "POST",
		data: $(".quick-trade-form").serialize(),
		url: "process/ajax/quick-trade-processes.php?process=1",
		success: function(data)
		{
			//alert(data);
			if(data == ""){
				$('.quick-trade-errors').html('<div class="alert alert-block alert-success fade in"><button type="button" class="close" data-dismiss="alert"></button><img src="/images/loading.gif" /> Processing Trade...</div>');
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.success('Trade Sumbmitted Successfully.');
				
				setTimeout(function() {
					 window.location = '/?page=02-00-00-002&tradeMessage=1';
				}, 4000);
			}else{
				
				//Notification
				toastr.options = {
					closeButton: true,
					debug: false,
					positionClass: 'toast-top-right',
					timeOut: '4000'
				};
				  
				toastr.error('Please fix errors.');
			};
			
			//alert(data);
			$('.quick-trade-errors').html(data);
			
	 
		}
	});	
}