<?php
/*
Marketocracy Inc. | Beta Development
Fund Info Scripts

Supporting files:
	AJAX		- process/ajax/portlets/fund-info-ajax.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/portlets/fund-info.js.php
	HTML		- includes/portlets/fund-info.php
*/
?>
<!--Fund Info-->
<script>


function loadfundInfo(fundID, fundSym){
	//Updates the Topics on the forum topics page
	$.ajax({
      url: 'process/ajax/portlets/fund-alpha-beta-ajax.php?process=1&fund='+ fundID,
      success: function(data) {
        $('.'+ fundSym + '-fund-info').html(data);
		//alert(data);
      }
    
    });	
}
</script>