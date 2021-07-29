<?php
/*
Global Include file for portlets that will be used site wide
Supporting Files:
Scripts: includes/scripts/global-admin.js.php 
Process: process/ajax/global-admin-processes.php THIS FILE
*/

//START | Include dependents
#Start Session
session_start();
#Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");
#Connect to DB
require_once("../../../secure/dbconnect.php");
#Load System Wide Functions
require_once("../../includes/system-functions.php");

//END | Include dependents

//Set process level variables
$process 					= $_REQUEST['process'];
$api1						= rand(53000, 53019);
$api2						= rand(53100, 53119);	
$setPort					= $api1;

//Switch on the process variable to determin its process
switch($process){
	
	//+----------------------------------------------------------------------------+
	//|							Load Ticket Details
	//+----------------------------------------------------------------------------+
	case 'load-api-status':
		
		$query = "
			SELECT *
			FROM ".$_SESSION['api_queue_table']."
		";			
		try{
			$rsAPI = $mLink->prepare($query);
			$aValues = array();
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsAPI->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$aAPI	= array();
						
		while($api = $rsAPI->fetch(PDO::FETCH_ASSOC)){
			
			$apiServer		= $api['api'];
			$aFundPrice 	= explode('|', $api['fundprice_processing']);
			$aStockPrice	= explode('|', $api['stockprice_processing']);
			$aEcn			= explode('|', $api['ecn_processing']);
			$aManagerAdmin	= explode('|', $api['manageradmin_processing']);
			$aTrade			= explode('|', $api['trade_processing']);
			$timestamp		= $api['timestamp'];
			
			$aAPI[$apiServer]	= array(
				'Fund Price Processing'		=> $aFundPrice,
				'Stock Price Processing'	=> $aStockPrice,
				'ECN Processing'			=> $aEcn,
				'Manager Admin Processing'	=> $aManagerAdmin,
				'Trade Processing'			=> $aTrade,
				'timestamp'					=> $timestamp
			);
		}
		
		
		
		foreach($aAPI as $apiServer=>$aProcess){
			$apiProcessCnt = 0;
			?>
            <table class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                	<tr>
                    	<th colspan="4"><?php echo $apiServer;?> Updated: <?php echo date('m/d/Y h:i:s A T', $timestamp);?></th>
                    </tr>
                </thead>
                <tbody>
					<?php
					foreach($aProcess as $process=>$apiProcess){
						
						if($process != 'timestamp'){
							
							foreach($apiProcess as $key=>$numProcess){
								
								$apiProcessCnt = $apiProcessCnt + $numProcess;
								
								if($numProcess == 0){
									$apiProcess[$key] = '<span class="label label-success" title="Idle">'.$numProcess.'</span>';	
								}else{
									$apiProcess[$key] = '<span class="label label-warning" title="Processing">'.$numProcess.'</span>';	
								}
							}
							
							?>
							<tr>
								<td colspan="4"><strong><?php echo $process;?></strong></td>
							</tr>
							<tr>
								<td>Input: <?php echo $apiProcess[0];?></td>
								<td>Temp: <?php echo $apiProcess[1];?></td>
								<td>Output: <?php echo $apiProcess[2];?></td>
								<td>Processing: <?php echo $apiProcess[3];?></td>
							</tr>
							<?php
						}
						
						
					}
					$_SESSION['apiProcess'][$apiServer] = $apiProcessCnt;
					?>
                </tbody>
            </table>
            <?php
			
		}
		
		
		
	break;
	
	case 'load-api-title':
		
		$apiProcesses = $_SESSION['apiProcess'];
		
		$aShow = array();
		
		foreach($apiProcesses as $apiServer=>$numProcess){
			
			if($numProcess == 0){
				$numProcess = '<span class="label label-success" title="Idle">'.$numProcess.'</span>';	
			}else{
				$numProcess = '<span class="label label-warning" title="Processing">'.$numProcess.'</span>';	
			}
			
			$aShow[] = '<strong>'.$apiServer.'</strong>: '.$numProcess.'';
				
		}
		
		echo implode(' | ', $aShow);
		
		
		
	break;
}

?>