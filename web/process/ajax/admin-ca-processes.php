<?php
/*
Marketocracy Inc. | Beta Development
Admin Corporate ACtions

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/admin-ca-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/admin-ca.js.php
	HTML		- includes/pages/admin-ca.php
*/

//Start Session
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

// Load System Wide Functions
require_once("../../includes/system-functions.php");

//Get Process from URL
$process = $_REQUEST['process'];


set_time_limit(3600);

if($process == "symbol-change"){
	
	$oldSymbol		= $_REQUEST['old_symbol'];
	$newSymbol		= $_REQUEST['new_symbol'];
	$day			= $_REQUEST['day'];
	$month			= $_REQUEST['month'];
	$year			= $_REQUEST['year'];
	$caDate			= mktime(6,0,0,$month,$day,$year);
	$caDateStr		= $year.$month.$day;
	
	$allMembers		= $_REQUEST['all-members'] == true ? '1' : '0';
	
	
	$apiServer		= $_REQUEST['api-server'];
	switch($apiServer){
		case 'api1': $apiType = '1';$startPort = rand(52000, 52099);$portCeil = 52099;$portFloor = 52000;break;
		case 'api2': $apiType = '2';$startPort = rand(52100, 52199);$portCeil = 52199;$portFloor = 52100;break;
	}
	
	
	$query = "
		SELECT fund_id
		FROM members_fund_stratification_basic
		WHERE stockSymbol=:stockSymbol
	";
	try{
		$rsFunds = $mLink->prepare($query);
		$aValues = array(
			':stockSymbol' 	=> $oldSymbol
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsFunds->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	echo $error;
	$aFundIds = array();
	while($funds = $rsFunds->fetch(PDO::FETCH_ASSOC)){
		
		
		
		
		
		$fundID 	= $funds['fund_id'];
		$fundSymbol	= get_funds($mLink, $fundID, 'fundSymbol');
		$aFundID 	= explode('-',$fundID);
		$memberID	= $aFundID[0];
		$username	= get_member($mLink, $memberID, 'username');
		
		if($fundSymbol != ""){
			
			$aFundIds[] = $funds['fund_id'];
		
			#run api queries
			$aMethodVars[] = array(
				'method'		=> 'positionInfo',
				'source'		=> 'Admin CA Symbol Change | admin-ca-processes.php | process: symbol-change',
				'api'			=> $apiType,
				'port'			=> '',
				'username'		=> $username,
				'fund_id'		=> $fundID,
				'fund_symbol'	=> $fundSymbol,
				'stock_symbol'  => $newSymbol,
				'from_date'		=> '',
				'start_date'	=> '',
				'end_date'		=> '',
				'group'			=> 'symbol-change'
			);
			
			
			//NEW LEGACY API FUNCTION
			$aMethodVars[] = array(
				'method'		=> 'tradesForPosition',
				'source'		=> 'Admin CA Tools | admin-ca-processes.php | process: symbol-change',
				'api'			=> $apiType,
				'port'			=> '',
				'username'		=> $username,
				'fund_id'		=> $fundID,
				'fund_symbol'	=> $fundSymbol,
				'from_date'		=> '',
				'stock_symbol'	=> $newSymbol,
				'group'			=> 'symbol-change'
			);
		}
		
		
		
		
	}
		
	//store results in a table
	$query = "
		INSERT INTO ".$_SESSION['ca_affected_funds_table']." (
			ca_type,
			fund_ids,
			old_stock_symbol,
			stock_symbol,
			timestamp
		)VALUES(
			'ticker_change',
			:fund_ids,
			:old_stock_symbol,
			:stock_symbol,
			UNIX_TIMESTAMP()
		)
	";
	try{
		$rsInsert = $mLink->prepare($query);
		$aValues = array(
			':old_stock_symbol' 	=> $oldSymbol,
			':stock_symbol'			=> $newSymbol,
			':fund_ids'				=> implode('|',$aFundIds)
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsInsert->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$query="
		UPDATE members_fund_positions_labels
		SET stock_symbol=:new_symbol
		WHERE stock_symbol=:old_symbol
	";
	try{
		$rsUpdate = $mLink->prepare($query);
		$aValues = array(
			':new_symbol'	=> $newSymbol,
			':old_symbol' 	=> $oldSymbol
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsUpdate->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	//garbage clean up the trades table
	$query = "
		DELETE FROM ".$_SESSION['fund_trades_table']."
		WHERE stockSymbol=:stockSymbol
	";
	try{
		$rsDelete = $mLink->prepare($query);
		$aValues = array(
			':stockSymbol' 	=> $oldSymbol
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsDelete->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	//garbage clean up the stratification table
	$query = "
		DELETE FROM ".$_SESSION['fund_stratification_basic_table']."
		WHERE stockSymbol=:stockSymbol
	";
	try{
		$rsDelete = $mLink->prepare($query);
		$aValues = array(
			':stockSymbol' 	=> $oldSymbol
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsDelete->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	//garbage cleanup the position details table	
	$query = "
		DELETE FROM ".$_SESSION['fund_positions_details_table']."
		WHERE stockSymbol=:stockSymbol
	";
	try{
		$rsDelete = $mLink->prepare($query);
		$aValues = array(
			':stockSymbol' 	=> $oldSymbol
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsDelete->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	
	//Process queries
	$mlaResults = legacy_api($mLink, $aMethodVars, true, 'results');
	
	/*echo '<pre>';
	print_r($aFundIds);
	count($aFundIds);
	echo '</pre>';*/
	
	//load list of processes
	
}

if($process == 'load-processed-funds'){

	?>
	<div class="portlet">
		<div class="portlet-title">
			<div class="caption"><i class="icon-reorder"></i>Processed</div>
			<div class="tools">
				<a href="javascript:;" class="collapse"></a>
				<!--<a href="javascript:;" class="reload"></a>-->
			</div><!--tools-->
		</div><!--portlet-title-->
		<div class="portlet-body form">
				
			<table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>PID</th>
                        <th>Date</th>
                        <th>Old Ticker</th>
                        <th>New Ticker</th>
                        <th>Funds</th>
                        <th>Actions</th>
                        
                    </tr>
                </thead>
                <tbody>
                    
                	<?php
					
					$query = "
						SELECT *
						FROM ".$_SESSION['ca_affected_funds_table']."
						WHERE ca_type='ticker_change'
						ORDER BY timestamp DESC
						LIMIT 50
					";
					try{
						$rsGetProcess = $mLink->prepare($query);
						$aValues = array();
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsGetProcess->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					while($process = $rsGetProcess->fetch(PDO::FETCH_ASSOC)){
						
						$uid		= $process['uid'];
						$aFundIDs	= explode('|', $process['fund_ids']);
						$oldSymbol 	= $process['old_stock_symbol'];
						$newSymbol	= $process['stock_symbol'];
						
						$date		= date('m/d/Y h:i a', $process['timestamp']);
						$stratRun	= $process['strat_run'];
						
						$aListFunds	= array();
						
						$cnt = 0;
						foreach($aFundIDs as $key=>$fundID){
							
							$fundSymbol = get_funds($mLink, $fundID, 'fundSymbol');
							//$fundSymbol = $fundID;
							$aFundID	= explode('-', $fundID);
							$memberID	= $aFundID[0];
							
							$cnt++;
							
							if($cnt <= 200){
								$aListFunds[] = '<a href="process/ajax/admin-switch-user.php?member='.$memberID.'&admin='.$_SESSION['member_id'].'&toggle=admin-to-user&gopage=03-01-03-001&gofund='.$fundID.'" onclick="switchUser(\''.$memberID.'\',\''.$_SESSION['member_id'].'\');">'.$fundSymbol.'</a>';	
							}
						}
						
						$listFunds = implode(', ',$aListFunds);
						
						$listFundIDs = implode('|', $aFundIDs);
						
						if($stratRun == NULL){
							$btnType 	= 'info';
							$btnText	= 'Run Stratification Rebuild';
						}else{
							$btnType 	= 'warning';
							$btnText	= 'Re-Run Stratification Rebuild';	
						}
						
						?>
                        <tr>
                        	<td><?php echo $uid;?></td>
                            <td><?php echo $date;?></td>
                            <td><?php echo $oldSymbol;?></td>
                            <td><?php echo $newSymbol;?></td>
                            <td><?php echo $listFunds;?></td>
                            <td>
                            	<span id="process-btn-<?php echo $uid;?>"><a href="javascript:void(0):" class="btn btn-<?php echo $btnType;?> btn-sm" style="margin-bottom:5px;" onClick="runStrat('<?php echo $uid;?>','<?php echo $listFundIDs; ?>');"><?php echo $btnText;?></a></span>
                            	<span id="rerun-btn-<?php echo $uid;?>"><a href="javascript:void(0);" onClick="rerunChange('<?php echo $uid;?>');" class="btn btn-default btn-sm">Re-Run Ticker Change</a></span>
                            </td>
                        </tr>
                        <?php
							
					}
					?>
                        
                </tbody>
            </table>	
			
			
			
		</div><!--END PORTLET BODY-->
	</div><!--END PORTLET-->
	<?php	
	
}

if($process == 'run-strat'){
	
	$aFundIds 	= explode('|', $_REQUEST['funds']);
	$uid		= $_REQUEST['uid'];
	$section	= $_REQUEST['section'];
	
	foreach($aFundIds as $key=>$fundID){
		
		exec('/usr/bin/php /var/www/html/portfolio.marketocracy.com/scripts/strat-build.php "fundID='.$fundID.'" > /dev/null &');
		
		usleep(50000);
		
	}
	
	switch($section){
		case 'member-reprice':
			$query = "
				UPDATE log_reprice_history
				SET strat_rebuilt=UNIX_TIMESTAMP()
				WHERE uid=:uid
			";
			try{
				$rsUpdateLog = $mLink->prepare($query);
				$aValues = array(':uid'=>$uid);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdateLog->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}	
		break;
		default:
			$query = "
				UPDATE log_ca_affected_funds
				SET strat_run=UNIX_TIMESTAMP()
				WHERE uid=:uid
			";
			try{
				$rsUpdateHistory = $mLink->prepare($query);
				$aValues = array(':uid'=>$uid);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdateHistory->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
		break;	
	}
	
	
	
	echo $error;
	echo 'Strat Processed: '.date('m/d/Y h:i a');
}

if($proces == 're-run-change'){
	
	$apiServer		= $_REQUEST['api'];
	switch($apiServer){
		case 'api1': $apiType = '1';$startPort = rand(52000, 52099);$portCeil = 52099;$portFloor = 52000;break;
		case 'api2': $apiType = '2';$startPort = rand(52100, 52199);$portCeil = 52199;$portFloor = 52100;break;
	}
	
	$uid 	= $_REQUEST['uid'];
	
	$query = "
		SELECT *
		FROM ".$_SESSION['ca_affected_funds_table']."
		WHERE uid=:uid
	";
	try{
		$rsGetProcess = $mLink->prepare($query);
		$aValues = array();
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetProcess->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$process = $rsGetProcess->fetch(PDO::FETCH_ASSOC);
	
	$aFundIDs = explode('|',$process['fund_ids']);
	$stockSymbol = $process['stock_symbol'];
	
	foreach($aFundIDs as $key=>$fundID){
		
		$fundSymbol	= get_funds($mLink, $fundID, 'fundSymbol');
		$aFundID 	= explode('-',$fundID);
		$memberID	= $aFundID[0];
		$username	= get_member($mLink, $memberID, 'username');
		
		#run api queries
		/*if($startPort >= $portCeil){
			$startPort = $portFloor;	
		}else{
			$startPort++;	
		}*/
		
		//get position details
		$aMethodVars[] = array(
			'method'		=> 'positionInfo',
			'source'		=> 'Admin CA Symbol Change | admin-ca-processes.php | process: re-run-change',
			'api'			=> $apiType,
			'port'			=> '',
			'username'		=> $username,
			'fund_id'		=> $fundID,
			'fund_symbol'	=> $fundSymbol,
			'stock_symbol'  => $stockSymbol,
			'from_date'		=> '',
			'start_date'	=> '',
			'end_date'		=> '',
			'group'			=> 're-run-change'
		);
	
		/*$port = $startPort;
		$query = 'positionInfo|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$stockSymbol;
		include ('../../includes/data-query-legacy.php');
		
		if($startPort >= $portCeil){
			$startPort = $portFloor;	
		}else{
			$startPort++;	
		}*/
		//get trade history
		//NEW LEGACY API FUNCTION
		$aMethodVars[] = array(
			'method'		=> 'tradesForPosition',
			'source'		=> 'Admin CA Tools | admin-ca-processes.php | process: re-run-change',
			'api'			=> $apiType,
			'port'			=> '',
			'username'		=> $username,
			'fund_id'		=> $fundID,
			'fund_symbol'	=> $fundSymbol,
			'from_date'		=> '',
			'stock_symbol'	=> $stockSymbol,
			'group'			=> 're-run-change'
		);
		//$query = 'tradesForPosition|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$stockSymbol;
		//include ('../../includes/data-query-legacy.php');
		
	}
	
	//Process queries
	$mlaResults = legacy_api($mLink, $aMethodVars, true);
}
?>