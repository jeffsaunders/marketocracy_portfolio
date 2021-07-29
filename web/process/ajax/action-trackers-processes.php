<?php
/*
Marketocracy Inc. |
Action Center

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/action-center-processes.php
	JAVASCRIPT	- includes/scripts/action-center.js.php
	HTML		- includes/pages/action-center.php
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
	
	
	case 'load-lists':
		echo '<tr>
				<td>1</td>
				<td>Brandon McCarthy</td>
				<td>d</td>
				<td>d</td>
				<td>d</td>
				<td>d</td>
				<td>d</td>
				<td>d</td>
			</tr>';
	break;
	
	case 'save-tracker-code':
		
		$trackerCode 	= strtoupper($_REQUEST['tracker_code']);
		$aErrors		= array();
		
		//$aErrors[] = $trackerCode;
		
		if(empty($trackerCode)){
			$aErrors[] = 'You must provide a tracker code.';	
		}
		
		
		if(empty($aErrors)){
			$query = "
				UPDATE ".$_SESSION['members_table']."
				SET tracker_code=:tracker_code
				WHERE member_id=:member_id
			";
			try{
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':member_id'	=> $_SESSION['member_id'],
					':tracker_code'	=> $trackerCode
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdate->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
		}else{
		
			echo '<div class="note note-danger"><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';	
			}
			echo '</ul></div>';
		}
		
	break;
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//													Load Trackers
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'load-trackers':
		
		
		$query = "
			SELECT *
			FROM members_fund_tracking
			WHERE member_id=:member_id AND unsubscribe='0'
		";
		try{
			$rsGetTrackers = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $_SESSION['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetTrackers->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$cnt = 0;
		while($tracker = $rsGetTrackers->fetch(PDO::FETCH_ASSOC)){
			
			$cnt++;
			
			if($tracker['tracker_code'] != NULL){
				$showList  	= 'Friends &amp; Family';
				$showEmail 	= $tracker['email'];	
				$actionBtns = '<button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#edit-article" onclick="editArticle(\'load\',\''.$article['article_id'].'\');"><i class="icon-pencil"></i> Edit</button> <button type="button" class="btn btn-danger btn-sm" onclick="editArticle(\'delete\',\''.$article['article_id'].'\');">Delete</button>';
			}else{
				$showList	= 'Trackers';
				$showEmail	= '';
				$actionBtns	= '';	
			}
			
			$aFunds 		= explode('|', $tracker['track_funds']);
			$aFundSymbols	= array();
			
			foreach($aFunds as $key=>$fundID){
				
				$aFundSymbols[] = get_funds($mLink, $fundID, 'fundSymbol');
					
			}
			
			$listFunds = implode('|',$aFundSymbols);
			
			switch($tracker['frequency']){
				case 'monthly'		: $frequency = 'Monthly';break;
				case 'quarterly'	: $frequency = "Quarterly";break;
			}
			
			$managerUpdates = ($tracker['manager_updates'] == 1 ? 'Yes' : 'No');
			$articleUpdates = ($tracker['manager_articles'] == 1 ? 'Yes' : 'No');
			
			?>
            <tr>
            	<td><?php echo $cnt;?></td>
                <td><?php echo $tracker['first_name'];?> <?php echo $tracker['last_name'];?></td>
                <td><?php echo $showList;?></td>
                <td><?php echo $listFunds;?></td>
                <td><?php echo $managerUpdates;?></td>
                <td><?php echo $articleUpdates;?></td>
                <td><?php echo $frequency;?></td>
                <td><?php echo date('m/d/Y', $tracker['timestamp']);?></td>
            </tr>
            <?php
			
		}
		
        
		
	break;
	
	

}
?>