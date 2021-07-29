<?php
/*
Marketocracy Inc. | Beta Development
Fund ADMIN Scripts

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/fund-admin-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/fund-admin.js.php
	HTML		- includes/pages/fund-admin.php
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


//+---------------------------------------------------------------------------------------------------------+
//|								Load Ledger Details Positions - PROCESS 1									|
//+---------------------------------------------------------------------------------------------------------+
if($process == "1"){
	
	$fundName = $_REQUEST['fund_name'];
	$description = $_REQUEST['description'];
	$fundSymbol = $_REQUEST['fund_symbol'];
	$fundID = $_REQUEST['fund_id'];
	
	//echo $fundName;
	
	
	$error_list = array();
	
	if(empty($fundName)){
		$error_list[] = "Please provide a Fund Name.";	
	}
	
	if(empty($error_list)){
		
		$query = '
			UPDATE '.$_SESSION['fund_table'].'
			SET fund_name=:fund_name, description=:description
			WHERE fund_id=:fund_id
		';
		
		try{
			$rsUpdateFund = $mLink->prepare($query);
			$aValues = array(
				':fund_name' 	=> $fundName,
				':description'	=> $description,
				':fund_id'		=> $fundID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdateFund->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		
		
	}else{
		
		?>
		<div class="alert alert-block alert-danger fade in">
			<button type="button" class="close" data-dismiss="alert"></button>
			<h4><strong>Please fix the following errors.</strong></h4>
			<ul>
				<?php
				foreach($error_list as $error){
					echo '<li>'.$error.'</li>';	
				}
				?>
			</ul>
			
		</div>
		<?php
			
	}
}