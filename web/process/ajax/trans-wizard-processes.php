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
		
		
		?>
        
        
        <h5><strong>Funds That Will Be Kept</strong></h5>
        <ul>
            <?php
			foreach($selectedFunds as $key=>$fundID){
				
				echo '<li><strong>'.$allFunds[$fundID].'</strong></li>';
					
			}
			?>
        </ul>
        
        
        <h5><strong>Funds That Will Be Removed</strong></h5>
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
		
		$numSelected = count($selectedFunds);
		if($numSelected < $maxFunds){
			
			$difference = ($maxFunds - $numSelected);
			
			?>
            <div class="alert alert-warning">
            	<h4>You can maintain <?php echo $difference;?> more fund(s).</h4>
                
                <p>You have selected fewer funds than what your membership level allows you to maintain.</p>
                
                <p><input type="checkbox" name="confirm-less" value='1' /> <strong>I agree and understand that I can choose (<?php echo $difference;?>) more Fund(s), but choose to only maintain the funds currently selected.</strong></p>
            </div>
            <?php
				
		}
		
		/*echo '<pre>';
		print_r($_SESSION['subscription']['funds']);
		print_r($selectedFunds);
		echo '</pre>';*/
		
	break;
	
	case 'deactivate-funds':
		
		$keepFunds			= $_REQUEST['funds'];
		$deactivateFunds 	= $_REQUEST['deactivate-funds'];
		$numDeactivate		= count($deactivateFunds);
		$numActiveFunds		= count($keepFunds);
		$maxFunds			= $_SESSION['subscription']['maxFunds'];
		$confirmLess		= $_REQUEST['confirm-less'];
		$aErrors			= array();
		
		
		#start error checking
		if($numActiveFunds > $maxFunds){
			
			$difference = $numActiveFunds - $maxFunds;
			
			$aErrors[] = 'You have selected to keep more funds than you are allowed to maintain. Please go back and deselect ('.$difference.') fund(s).';	
		}
		
		if($numActiveFunds < $maxFunds){
			
			if($confirmLess != '1'){
				$aErrors[] = 'You must agree that you understand that you can choose more funds to maintain.';
			}
		}
		
		
		$query = "
			UPDATE members_subscriptions
			SET trans_wiz=UNIX_TIMESTAMP()
			WHERE member_id=:member_id AND active='1' AND product_id<'100'
		";
		try{
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $_SESSION['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		
		if(empty($aErrors)){
			
			if($_SESSION['subscription']['membershipLevelID'] == '1' && $_SESSION['subscription']['maxFunds'] == '1'){
				
				foreach($keepFunds as $key=>$fundID){
					
					$aFundInfo = get_funds($mLink, $fundID, 'fundInfo');
					
					$inceptionDate	= $aFundInfo['unix_date'];
					
					$timeDiff		= time() - $inceptionDate;
										
					#check to see if fund is over one year old
					if($timeDiff > 31536000){
						$experationDate	= strtotime('+30 day', time());
					}else{
						$experationDate	= strtotime('+395 day', $inceptionDate);
					}
					
					$query = "
						UPDATE ".$_SESSION['fund_table']."
						SET fund_experation=:fund_exp
						WHERE fund_id=:fund_id
					";
					try{
						$rsUpdate = $mLink->prepare($query);
						$aValues = array(
							':fund_id'	=> $fundID,
							':fund_exp'	=> $experationDate
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsUpdate->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
					$query = "
						UPDATE members_subscriptions
						SET fund_id=:fund_id
						WHERE member_id=:member_id AND product_id='1' AND active='1'
					";
					try{
						$rsUpdate = $mLink->prepare($query);
						$aValues = array(
							':member_id'	=> $_SESSION['member_id'],
							':fund_id'		=> $fundID
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsUpdate->execute($aValues);
					}
					
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
						
				}
					
			}
			
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
