<?php
//Marketocracy Inc. | Alpha Development
//Fund Ledger AJAX Processes

session_start();

// Load debug & error logging functions
require_once("../../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../../secure/dbconnect.php");

require_once("../../../includes/system-functions.php");








?>
   
   
   
   
   
    <tr>
    	<td><?php echo $key;?></td>
        <td><?php //echo date('m/d/Y', $date);?><?php echo $value['date'];?></td>
        <td>$<?php echo number_format($array[$key-1]['cashValue'], 2, '.', ',');?></td>
        <td>$<?php echo number_format($value['netFlow'],2, '.', ',');?></td>
        <td>$<?php echo number_format($array[$key-1]['interest'],2, '.', ',');?></td>
        <td>$<?php echo number_format($value['dividends'],2, '.', ',');?></td>
        <td>$<?php echo number_format($array[$key-1]['fees'],2);?></td>
        <?php /*?><td>$<?php echo number_format($value['fees'],2);?></td><?php */?>
        <td>$<?php echo number_format($tb, 2, '.', ',');?></td>
        <td>$<?php echo number_format($value['cashValue'], 2, '.', ',');?></td>
        <td>$<?php echo number_format($value['positionsValue'],2, '.', ',');?></td>
        <td>$<?php echo number_format($value['totalValue'],2, '.', ',');?></td>
        <td><?php echo round($value['shares'],0);?></td>
        <td>$<?php echo number_format($value['price'],2, '.', ',');?></td>
        <td><?php echo $compliance;?></td>
    </tr>
