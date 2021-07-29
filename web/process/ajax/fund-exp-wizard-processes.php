<?php
/*
SETUP WIZARD - PROCESSES FILE

SUPPORTING FILES:
_______________________________________________________________

AJAX 			- THIS FILE process/ajax/setup-wizard-processes.php
PHP JAVASCRIPT	- includes/scripts/setup-wizard.js.php
HTML 			- includes/pages/setup-wizard.php
_______________________________________________________________

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


switch($process){
	
	case 'load-confirm':
		
		$selectedFunds 	= $_REQUEST['funds'];
		$allFunds		= $_SESSION['subscription']['funds'];
		$maxFunds		= $_SESSION['subscription']['maxFunds'];
		
		$fundType		= $_REQUEST['fund_type'];
		$fundSector		= $_REQUEST['fund_sector'];
		$fundName		= $_REQUEST['fund_name'];
		$fundSymbol		= $_REQUEST['fund_symbol'];
		
		
		
		$_SESSION['setFundType'] = $fundType;
		$_SESSION['setFundSector']	= $fundSector;
		$_SESSION['setFundName']	= $fundName;
		$_SESSION['setFundSymbol']	= $fundSymbol;
		
		if($fundType = 'sector'){
			if($fundSector == 'xx'){
				$aErrors[] = 'You must go back and select a fund sector.';	
			}
		}
		
		if($fundColor == ''){
			$fundColor = '#39B3D7';
		}
	
		
	
		$aFundSymbols	= get_fund_symbols($mLink, $_SESSION['member_id'], 'symbols', false);
		//print_r($aFundSymbols);
		$aErrors = array();
		
		if(strlen($fundSymbol) > 6){
			$aErrors[] = 'Fund symbol can only be 5 characters long.';	
		}
		
		if($fundSymbol == ''){
			$aErrors[] = 'Please provide a fund symbol.';
		}
	
		if($fundName == ''){
			$aErrors[] = 'Please provide a fund name.';
		}
	
		if(in_array($fundSymbol, $aFundSymbols)){
			$aErrors[] = 'Fund Symbol is already in use or has been used in the past, please choose another Fund Symbol.';	
		}
		
		if (preg_match('/[^a-zA-Z\-.0-9]+/', $fundSymbol)){
		  $aErrors[] = 'Fund Symbol can only contain Letters and Numbers. The following symbols are allowed: -(dash), .(period)';
		}
		
		if(strlen($fundSymbol) > 8){
			$aErrors[] = 'Fund Symbol can not exceed 8 characters.';	
		}
		
		
		if(!empty($aErrors)){
			
			echo '<div class="alert alert-danger"><h4>Please Fix the Following Errors</h4><ul>';
			
			echo '<p><a href="?page=10-00-00-005">Click Here to make changes.</a></p>';
			
			$errorList = '<ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';	
				$errorList .= '<li>'.$error.'</li>';
				
			}
			echo '</ul>';
			$errorList .= '</ul>';
			
			echo'<h5>Having issues? Create a support ticket here: <a href="/?page=08-01-00-001&type=ga&subtype=membership&ticketSubject=Transition Wizard Error&ticketDesc='.$errorList.'" target="_blank">Create Support Ticket</a><h5></div>';
				
		}
		?>
        
        
        <h4><strong>New Fund Info</strong></h4>
        <ul>
            <li><strong>Fund Type:</strong> <?php echo ucfirst($fundType);?> Fund</li>
            <li><strong>Fund Name:</strong> <?php echo $fundName;?></li>
            <li><strong>Fund Symbol:</strong> <?php echo $fundSymbol;?></li>
        </ul>
        
        
        <h4><strong>Funds That Will Be Removed from your account</strong></h4>
        <ul>
            <?php
			foreach($allFunds as $fundID=>$fundSymbol){
				
				if(!in_array($fundID, $selectedFunds)){
					echo '<li><strong>'.$allFunds[$fundID].'</strong></li>';
					echo '<input type="checkbox" style="display:none;" name="deactivate-funds[]" value="'.$fundID.'" checked />';
				}
			}
			?>
        </ul>
        <?php
		
		
		/*echo '<pre>';
		print_r($_SESSION['subscription']['funds']);
		print_r($selectedFunds);
		echo '</pre>';*/
		
	break;
	
	case 'create-fund':
		
		$keepFunds			= $_REQUEST['funds'];
		$deactivateFunds 	= $_REQUEST['deactivate-funds'];
		$numDeactivate		= count($deactivateFunds);
		$numActiveFunds		= count($keepFunds);
		$maxFunds			= $_SESSION['subscription']['maxFunds'];
		$confirmLess		= $_REQUEST['confirm-less'];
		$aErrors			= array();
		
		
		$fundType		= $_REQUEST['fund_type'];
		$fundSector		= $_REQUEST['fund_sector'];
		$fundName		= $_REQUEST['fund_name'];
		$fundSymbol		= $_REQUEST['fund_symbol'];
		
		if($fundType = 'sector'){
			if($fundSector == 'xx'){
				$aErrors[] = 'You must go back and select a fund sector.';	
			}
		}
		
		if($fundColor == ''){
			$fundColor = '#39B3D7';
		}
	
		
	
		$aFundSymbols	= get_fund_symbols($mLink, $_SESSION['member_id'], 'symbols', false);
		//print_r($aFundSymbols);
		$aErrors = array();
		
		if(strlen($fundSymbol) > 6){
			$aErrors[] = 'Fund symbol can only be 5 characters long.';	
		}
		
		if($fundSymbol == ''){
			$aErrors[] = 'Please provide a fund symbol.';
		}
	
		if($fundName == ''){
			$aErrors[] = 'Please provide a fund name.';
		}
	
		if(in_array($fundSymbol, $aFundSymbols)){
			$aErrors[] = 'Fund Symbol is already in use or has been used in the past, please choose another Fund Symbol.';	
		}
		
		if (preg_match('/[^a-zA-Z\-.0-9]+/', $fundSymbol)){
		  $aErrors[] = 'Fund Symbol can only contain Letters and Numbers. The following symbols are allowed: -(dash), .(period)';
		}
		
		if(strlen($fundSymbol) > 8){
			$aErrors[] = 'Fund Symbol can not exceed 8 characters.';	
		}
		
		$fundID = get_new_fund_id($mLink, $_SESSION['member_id']);
		
		//echo $fundID;
		
		$aFundID = explode('-', $fundID);
		
		$seqID = $aFundID[1];
		
		$col1 = 'fund-price-history~0~0~0|fund-pos-sectors~0~0~0|fund-info~0~0~0|fund-turnover~0~0~0|fund-best-worst~0~0~0';
		$col2 = 'fund-returns-index~0~0~0|fund-ratios~0~0~0|fund-pos-style~0~0~0|fund-recent-returns~0~0~0|fund-alpha-beta~0~0~0|fund-profit~0~0~0';
		
		
		
		
		if(empty($aErrors)){
			
			if($fundSector == ''){
				$fundSector = NULL;	
			}
			
			//Create new fund record
			$query = "
				INSERT INTO ".$_SESSION['fund_table']." (
					fund_id,
					fund_type,
					fund_sector,
					seq_id,
					member_id,
					timestamp,
					inception_date,
					unix_date,
					fund_name,
					fund_symbol,
					description,
					short_fund,
					active,
					version,
					fund_color
				) VALUE (
					:fund_id,
					:fund_type,
					:fund_sector,
					:seq_id,
					:member_id,
					UNIX_TIMESTAMP(),
					:date,
					UNIX_TIMESTAMP(),
					:fund_name,
					:fund_symbol,
					:description,
					'0',
					'1',
					'1',
					:fund_color
				)
				
			";
			try{
				$rsUpdateFund = $mLink->prepare($query);
				$aValues = array(
					':fund_id'		=> $fundID,
					':fund_type'	=> $fundType,
					':fund_sector'	=> $fundSector,
					':seq_id'		=> $seqID,
					':member_id'	=> $_SESSION['member_id'],
					':date'			=> date('Ymd'),
					':fund_name'	=> $fundName,
					':fund_symbol'	=> $fundSymbol,
					':description'	=> $fundDesc,
					':fund_color'	=> $fundColor
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdateFund->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$aErrors['Insert-Fund-Error'] = $error;
			
			//Create LivePrice Record
			$query = "
				INSERT INTO ".$_SESSION['fund_liveprice_table']." (
					fund_id,
					timestamp,
					nav,
					stockValue,
					cashValue,
					totalValue,
					shares,
					legacy
				) VALUES (
					:fund_id,
					UNIX_TIMESTAMP(),
					:nav,
					:stockValue,
					:cashValue,
					:totalValue,
					:shares,
					:legacy		
				)
			";
			try{
				$rsAddLivePrice = $mLink->prepare($query);
				$aValues = array(
					':fund_id'		=> $fundID,
					':nav'			=> '10',
					':stockValue'	=> '0',
					':cashValue'	=> '1000000',
					':totalValue' 	=> '1000000',
					':shares'		=> '100000',
					':legacy'		=> '0'
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsAddLivePrice->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$aErrors['Insert-LivePrice-Error'] = $error;
			
			//Create Funds Settings entry
			$query = "
				INSERT INTO ".$_SESSION['fund_settings_table']." (
					fund_id,
					overview_col1,
					overview_col2,
					fund_color,
					timestamp
				) VALUE (
					:fund_id,
					:col1,
					:col2,
					:fund_color,
					UNIX_TIMESTAMP()
				)
			";
			try{
				$rsUpdateFund = $mLink->prepare($query);
				$aValues = array(
					':fund_id'		=> $fundID,
					':col1'			=> $col1,
					':col2'			=> $col2,
					':fund_color'	=> $fundColor
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdateFund->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
	
			$aErrors['Insert-Fund-Settings-Error'] = $error;
	
			/*$query = 'newFund|'.$_SESSION['username'].'|long|'.$fundName.'|'.$fundSymbol.'|'.$fundID.'';
			include('../../includes/data-query-legacy.php');*/
			
			$aMethodVars[] = array(
				'method'		=> 'newFund', #method name 
				'source'		=> 'User Settings | user-settings-processes.php | process: 5-2', #File and Code location of this instance 
				'api'			=> '1', #api switch, 1 = api1, 2 = api2 
				'username'		=> $_SESSION['username'], #username of member
				'fund_type'		=> 'long',
				'fund_name'		=> $fundName, 
				'fund_symbol'	=> $fundSymbol, #fund symbol 
				'fund_id' 		=> $fundID, #members fund ID 'group'	=> 'N/A' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
			); 
			$mlaResults = legacy_api($mLink, $aMethodVars, true);
			
			//echo $query;
			$aErrors['MLA-Results'] = $mlaResults;
			/*$event = 'Fund Creation';
			$detail = 'Member has created a new fund: '.$_SESSION['username'].':'.$fundSymbol.'.';
			addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);*/
			if($_SESSION['member_id'] == '1'){
				echo '<div class="note note-danger"><ul>';
				foreach($aErrors as $key=>$error){
					echo '<li>'.$error.'</li>';	
				}
				echo '</ul></div>';
			}
			echo 'FUND:'.$fundID;
			
			#remove old fund
			foreach($deactivateFunds as $key=>$fundID){
				
				$query = "
					UPDATE ".$_SESSION['fund_table']."
					SET active='0'
					WHERE fund_id=:fund_id
				";
				try{
					$rsUpdate = $mLink->prepare($query);
					$aValues = array(
						':fund_id'	=> $fundID
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsUpdate->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
						
			}
			//end remove old fund
			
			if($error != ''){
				echo '<div class="alert alert-danger"><h4>There was an error.</h4><ul>';
				echo '<li>'.$error.'</li>';	
        		echo '</ul>';
				
				echo'<h5>Please copy the error above and paste it into a support ticket here: <a href="/?page=08-01-00-001&type=ga&subtype=membership&ticketSubject=Transition Wizard Error&ticketDesc='.$error.'" target="_blank">Create Support Ticket</a><h5></div>';	
			}else{
				echo 'success';	
			}
			
		}else{
			
			echo '<div class="alert alert-danger"><h4>Please Fix the Following Errors</h4><ul>';
			
			echo '<p><a href="?page=10-00-00-005">Click Here to make changes.</a></p>';
			
			$errorList = '<ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';	
				$errorList .= '<li>'.$error.'</li>';
				
			}
			echo '</ul>';
			$errorList .= '</ul>';
			
			echo'<h5>Having issues? Create a support ticket here: <a href="/?page=08-01-00-001&type=ga&subtype=membership&ticketSubject=Transition Wizard Error&ticketDesc='.$errorList.'" target="_blank">Create Support Ticket</a><h5></div>';
				
		}
				
		echo '<pre>';
		print_r($keepFunds);
		print_r($aFundInfo);
		echo '</pre>';
		
		
		
        
        
		
	break;
		
}
