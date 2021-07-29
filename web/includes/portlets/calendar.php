<?php
//EVENT CALENDAR PORTLET

//portVar1 = NOT SET
//portVar2 = NOT SET
//portVar3 = NOT SET

/*$query = "
	SELECT fund_symbol, fund_id 
	FROM :table
	WHERE member_id=:member_id AND fund_id=:fund_id AND active=1
";

//Fund Symbols
try{
	$rsStocksFund = $mLink->prepare($query);
	$aValues = array(
		':member_id' 	=> $_SESSION['member_id'],
		':fund_id'		=> $portVar1,
		':table'		=> $_SESSION['fund_table']
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsStocksFund->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$stockFund = $rsStocksFund->fetch(PDO::FETCH_ASSOC)
*/

?>
<!-- BEGIN PORTLET CALENDAR-->
<div class="portlet calendar" id="<?php echo $portletID; ?>">
  <div class="portlet-title handle">
     <div class="caption"><i class="icon-calendar"></i>Event Calendar</div>
     
  </div>
  <div class="portlet-body">
     <div id="calendar"></div>
  </div>
</div>
<!-- END PORTLET ALENDAR-->