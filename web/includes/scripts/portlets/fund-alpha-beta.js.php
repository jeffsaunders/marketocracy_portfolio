<?php
/*
Marketocracy Inc. | Beta Development
Fund Alpha Beta Scripts

Supporting files:
	AJAX		- process/ajax/portlets/fund-alpha-beta-ajax.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/portlets/fund-alpha-beta.js.php
	HTML		- includes/portlets/fund-alpha-beta.php
*/
?>
<!--Fund Alpha Beta-->
<script>
<?php
foreach($alphaBetaArray as $alphaBetaFundID){
	$alphaBetaFundSym = get_funds($mLink, $alphaBetaFundID, 'fundSymbol');
	?>
	$('.alpha-beta-loading-<?php echo $alphaBetaFundID;?>').html('<img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" />');
	
	$(document).ready(refreshLoadAlphaBeta = function() {
		loadAlphaBeta('<?php echo $alphaBetaFundID;?>', '<?php echo $alphaBetaFundSym; ?>');
	});
	<?php
}
?>

function loadAlphaBeta(fundID, fundSym){
	//Updates the Topics on the forum topics page
	$.ajax({
      url: 'process/ajax/portlets/fund-alpha-beta-ajax.php?process=1&fund='+ fundID,
      success: function(data) {
        $('.load-alpha-beta-'+ fundID).html(data);
		//alert(data);
		$('.alpha-beta-loading-' + fundID).html('');
      }
    
    });	
}
</script>