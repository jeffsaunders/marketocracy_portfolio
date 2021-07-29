<?php
//AJAX SCRIPT CALLED BY: /js/system-add-remove-portlets.js

session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

$portletID = $_REQUEST['portlet_id'];
$undo = $_REQUEST['undo'];

if($undo == "1"){
	
		//2 Column code
		$col1 = $_REQUEST['col1'];
		$col2 = $_REQUEST['col2'];
		$col4_1 = $_REQUEST['4col1'];
		$col4_2 = $_REQUEST['4col2'];
		$col4_3 = $_REQUEST['4col3'];
		$col4_4 = $_REQUEST['4col4'];
		
		if (isset($_SESSION['member_id']) == true){
			// log the event
			$query = "
				UPDATE ".$_SESSION['members_settings_table']."
				SET	dash_col1=:dash_col1, 
					dash_col2=:dash_col2,
					dash_4col1=:dash_4col1, 
					dash_4col2=:dash_4col2,
					dash_4col3=:dash_4col3, 
					dash_4col4=:dash_4col4,	
					timestamp=UNIX_TIMESTAMP()
				
				WHERE member_id=:member_id
			";
			try {
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':member_id'	=> $_SESSION['member_id'],
					':dash_col1'	=> $col1,
					':dash_col2'	=> $col2,
					':dash_4col1'	=> $col4_1,
					':dash_4col2'	=> $col4_2,
					':dash_4col3'	=> $col4_3,
					':dash_4col4'	=> $col4_4
				);
				// Prepared query - for error logging and debugging
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdate->execute($aValues);
			}
			catch(PDOException $error){
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			echo "reload";
			
			/*echo "It worked! Refresh the page!";
			echo $col1;
			echo $col2;*/
		}
	
	
}else{

	$query = "
		SELECT dash_col1, dash_col2, dash_4col1, dash_4col2, dash_4col3, dash_4col4
		FROM ".$_SESSION['members_settings_table']."
		WHERE member_id=:member_id 
	";
	
	//Fund Symbols
	try{
		$rsGetColumns = $mLink->prepare($query);
		$aValues = array(
			':member_id' 	=> $_SESSION['member_id']
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetColumns->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$columns = $rsGetColumns->fetch(PDO::FETCH_ASSOC);
	
	$col1 	= explode("|", $columns['dash_col1']);
	$col2 	= explode("|", $columns['dash_col2']);
	$col4_1 = explode("|", $columns['dash_4col1']);
	$col4_2 = explode("|", $columns['dash_4col2']);
	$col4_3 = explode("|", $columns['dash_4col3']);
	$col4_4 = explode("|", $columns['dash_4col4']);
	
	
	$cnt=0;
	$cntCol1 	= 0;
	$cntCol2 	= 0;
	$cntCol4_1	= 0;
	$cntCol4_2	= 0;
	$cntCol4_3	= 0;
	$cntCol4_4	= 0;
	
	foreach($col1 as $key => $value) {
		$cntCol1 = $cntCol1 + 1;
		if($value == $portletID){
			$cnt = $cnt + 1;	
		}
	}
	
	foreach($col2 as $key => $value) {
		$cntCol2 = $cntCol2 + 1;
		if($value == $portletID){
			$cnt = $cnt + 1;	
		}
	}
	
	foreach($col4_1 as $key => $value) {
		$cntCol4_1 = $cntCol4_1 + 1;
		if($value == $portletID){
			$cnt = $cnt + 1;	
		}
	}
	
	foreach($col4_2 as $key => $value) {
		$cntCol4_2 = $cntCol4_2 + 1;
		if($value == $portletID){
			$cnt = $cnt + 1;	
		}
	}
	
	foreach($col4_3 as $key => $value) {
		$cntCol4_3 = $cntCol4_3 + 1;
		if($value == $portletID){
			$cnt = $cnt + 1;	
		}
	}
	
	foreach($col4_4 as $key => $value) {
		$cntCol4_4 = $cntCol4_4 + 1;
		if($value == $portletID){
			$cnt = $cnt + 1;	
		}
	}
	
	if($cnt == 0){
		
		if($cntCol1 <= $cntCol2) {
			//Update Column 1
			$updateColumn = "dash_col1";
			
			$updateString = $columns['dash_col1'];
			$updateString .= "|";
			$updateString .= $portletID;
		}else{
			//Update Column 2
			$updateColumn = "dash_col2";
			
			$updateString = $columns['dash_col2'];
			$updateString .= "|";
			$updateString .= $portletID; 
		}
		
		$fourColumns = array(
			"dash_4col1" => $cntCol4_1,
			"dash_4col2" => $cntCol4_2,
			"dash_4col3" => $cntCol4_3,
			"dash_4col4" => $cntCol4_4
		);
	
		$set = array_keys($fourColumns, min($fourColumns));
		
		$updateColumn2 = $set[0];
		$updateString2 = $columns[''.$updateColumn2.''];
		$updateString2 .= "|";
		$updateString2 .= $portletID;
		
		if (isset($_SESSION['member_id']) == true){
			// log the event
			
			
			$query = "
				UPDATE ".$_SESSION['members_settings_table']." 
				SET	".$updateColumn."=:update_string,
					".$updateColumn2."=:update_string2,
					timestamp=UNIX_TIMESTAMP()
				
				WHERE member_id=:member_id
			";
			try {
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':member_id'		=> $_SESSION['member_id'],
					':update_string'	=> $updateString,
					':update_string2'	=> $updateString2
				);
				// Prepared query - for error logging and debugging
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdate->execute($aValues);
			}
			catch(PDOException $error){
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
		}
		
		echo '<a href="javascript:void(0);" title="Portlet On Dashboard" class="addtodashboard balloon disabled"></a>
			  <a href="" class="reload balloon" title="Refresh Portlet"></a>
			  <a href="" class="collapse balloon" title="Expand/Collapse Portlet"></a>
		';
		
		
	}
	
}
/*echo $cnt;
echo $_SESSION['member_id'];
echo $portletID;
echo "| ";
echo $column1;*/
?>