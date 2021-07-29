<?php
$query = "
	SELECT * 
	FROM members_fund_liveprice
	WHERE fund_id=:fund_id
";

try{
	$rsLive = $mLink->prepare($query);
	$aValues = array(
		':fund_id'		=> $fundID
		
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsLive->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$livePrice = $rsLive->fetch(PDO::FETCH_ASSOC);

$nav 			= number_format($livePrice['nav'], 2, '.', ',');
$stockValue 	= number_format($livePrice['stockValue'], 2, '.', ',');
$cashValue 		= number_format($livePrice['cashValue'], 2, '.', ',');
$shares 		= number_format($livePrice['shares'], 0, '.', ',');
$totalValue 	= number_format(($livePrice['stockValue'] + $livePrice['cashValue']), 2, '.', ',');
?>
<div class="portlet">
    <div class="portlet-title">
        <div class="caption">
        	
            <?php if($pageID == "03-01-00-001"){?>
            <div style="float:left;"><?php echo get_funds($mLink, $fundID, 'fund');?></div>
            <?php }else{ ?>
            <div style="float:left;"><?php echo get_funds($mLink, $fundID, 'fundSymbol');?></div>
            <?php } ?>
            
            <div class="" style="float:right;margin-left:40px;margin-top:-6px;">
              <table class="fund-info-bar" cellpadding="0" cellspacing="0">
                  <tr>
                      <td class="fund-info-bar-hl"><strong>Value:</strong></td><td>$<?php echo $totalValue;?></td>
                      <td class="fund-info-bar-hl"><strong>Cash:</strong></td><td>$<?php echo $cashValue;?></td>
                      <td class="fund-info-bar-hl"><strong>Stock Value:</strong></td><td>$<?php echo $stockValue;?></td>
                      <td class="fund-info-bar-hl"><strong>Total Shares:</strong></td><td><?php echo $shares;?></td>
                      <td class="fund-info-bar-hl"><strong>NAV:</strong></td><td>$<?php echo $nav;?></td>
                  </tr>
              </table>
            </div>
            
            <div class="clearfix"></div>
        </div><!--caption-->
    </div><!--portlet-title-->
  
</div>
<!-- END FUND INFO -->
         
