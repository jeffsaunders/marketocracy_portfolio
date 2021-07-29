<?php
/*
Marketocracy Inc. | Beta Development
Fund Recent Returns Scripts

Supporting files:
	AJAX		- process/ajax/portlets/fund-returns-index-ajax.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/portlets/fund-returns-index.js.php
	HTML		- includes/portlets/fund-returns-index.php
*/
?>
<script>
function refreshReturns(fundID){
	
	$('.recent-returns-loading-'+ fundID).html('<img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" /> Refreshing Recent Returns...');
	
	//Updates the Topics on the forum topics page
	$.ajax({
      url: 'process/ajax/portlets/fund-recent-returns-ajax.php?process=load-returns&fund='+ fundID,
      success: function(data) {
        $('.load-fund-recent-returns-ajax-'+ fundID).html(data);
		//alert(data);
		
		//get new as of date
		$.ajax({
		  url: 'process/ajax/portlets/fund-recent-returns-ajax.php?process=2&fund='+ fundID,
		  success: function(data) {
			//alert(data);
			$('.recent-returns-loading-' + fundID).html(data);
		  }
		});
      }
    
    });	
}

$(document).ready(function() {
	
	refreshReturns('<?php echo $$recentFundID;?>');
	
});
<?php
/*foreach($recentArray as $recentFundID){
	$recentFundSym = get_funds($mLink, $recentFundID, 'fundSymbol');
	?>
	$('.recent-returns-loading-<?php echo $recentFundID;?>').html('<img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" />');
	
	$(document).ready(refreshLoadReturns = function() {
		loadReturns('<?php echo $recentFundID;?>', '<?php echo $recentFundSym; ?>');
	});
	<?php
}*/
?>


</script>