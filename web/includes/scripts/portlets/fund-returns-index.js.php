<?php
/*
Marketocracy Inc. | Beta Development
Fund Recent Returns vs Major Indexes Scripts

Supporting files:
	AJAX		- process/ajax/portlets/fund-returns-index-ajax.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/portlets/fund-returns-index.js.php
	HTML		- includes/portlets/fund-returns-index.php
*/
?>
<script>

<?php
foreach($riArray as $riFundID){
	$riFundSym = get_funds($mLink, $riFundID, 'fundSymbol');
	/*?>

	$(document).ready(loadReturns = function(){
		getMTD('<?php echo $riFundID;?>', '<?php echo $riFundSym;?>');
	});
	<?php*/
}
?>

function loadValues(fundID, sym) {
	$.ajax({
      url: 'process/ajax/portlets/fund-returns-index-ajax.php?process=2&fund=' + fundID,
      success: function(data) {
        $('.load-fund-returns-index-ajax-' + fundID).html(data);
		//alert(data);
      }
    
    });
}

function getMTD(fundID, sym){
	//Updates the DB aggregates table and updates the last entry with Today % Return and MTD
	$.ajax({
      url: 'process/ajax/portlets/fund-returns-index-ajax.php?process=1&fund=' + fundID,
      success: function(data) {
        //$('.load-forums').html(data);
		//alert(data);
		
		loadValues(fundID, sym);
      }
    
    });
}

/*$(document).ready(getMTD = function(fundID, sym) {
    //Updates the DB aggregates table and updates the last entry with Today % Return and MTD
	$.ajax({
      url: 'process/ajax/portlets/fund-returns-index-ajax.php?process=1&fund=' + fundID,
      success: function(data) {
        //$('.load-forums').html(data);
		//alert(data);
		
		loadValues(fundID, sym);
      }
    
    });
});*/


</script>