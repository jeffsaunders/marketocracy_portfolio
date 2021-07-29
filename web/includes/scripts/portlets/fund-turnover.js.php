<?php
/*
Marketocracy Inc. | Beta Development
Fund Turnover Scripts

Supporting files:
	AJAX		- process/ajax/portlets/fund-turnover-ajax.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/portlets/fund-turnover.js.php
	HTML		- includes/portlets/fund-turnover.php
*/
?>
<!--Fund Turnover-->
<script>
<?php
foreach($turnoverArray as $turnoverFundID){
	$turnoverFundSym = get_funds($mLink, $turnoverFundID, 'fundSymbol');
	?>
	$('.turnover-loading-<?php echo $turnoverFundID;//$turnoverFundSym;?>').html('<img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" />');
	
	$(document).ready(refreshLoadTurnover = function() {
		loadTurnover('<?php echo $turnoverFundID;?>', '<?php echo $turnoverFundSym; ?>');
	});
	<?php
}
?>

function loadTurnover(fundID, fundSym){
	//Updates the Topics on the forum topics page
	$.ajax({
      url: 'process/ajax/portlets/fund-turnover-ajax.php?process=1&fund='+ fundID,
      success: function(data) {
        $('.load-turnover-'+ fundID).html(data);
		//alert(data);
		$('.turnover-loading-' + fundID).html('');
      }
    
    });	
}
</script>