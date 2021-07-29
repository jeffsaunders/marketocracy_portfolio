var PortletDraggable = function () {
  
	return {
		//main function to initiate the module
		init: function () {

			if (!jQuery().sortable) {
				return;
			}

			$("div.sortable").sortable({
			  connectWith: '.sortable',
			  items: ".portlet",
			  opacity: 0.8,
			  coneHelperSize: true,
			  placeholder: 'sortable-box-placeholder round-all',
			  forcePlaceholderSize: true,
			  tolerance: "pointer",
			  handle: '.handle',
			  
			  update: function(event, ui) {
				  var postData = $('.sortable').serial();
				  /*alert( postData );*/
				  
				  //document.getElementById("status-update").innerHTML = postData;
				  console.log(postData);
				  $.post('/process/ajax/portlet-save-fund-overview.php', {list: postData}, function(o) {
						console.log(o);	
					}, 'json');
			  }
			});


			$.fn.serial = function() {
			 var array = [];
			 var $elem = $(this); 
				$elem.each(function(i) {
				  var menu = this.id;
					$('div.portlet', this).each(function(e) {
							array.push( menu + '['+e+']=' + this.id  );
					});
				});
				return array.join('&');      
				}
			 

			$(".column").disableSelection();

		}

	};

}();