<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

require_once("../../includes/system-functions.php");

$process = $_REQUEST['process'];


switch($process){
	
	case 'convert':
		
		$query = "
			SELECT *
			FROM edu_class
		";
		try{
			$rsGetClasses = $mLink->prepare($query);
			$aValues = array();
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetClasses->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$aClasses = array();
		
		while($class = $rsGetClasses->fetch(PDO::FETCH_ASSOC)){
			
			$classID	= $class['class_id'];
			$aStudents	= explode('|', $class['student_ids']);
			
			foreach($aStudents as $key=>$studentFundID){
				
				$aSFID = explode('-', $studentFundID);
				
				$sID	= $aSFID[0];
				
				$aClasses[$classID][$sID] = $studentFundID;
				
				$query = "
					INSERT INTO edu_students(
						student_id,
						class_id,
						fund_id,
						active,
						timestamp
					)VALUES(
						:student_id,
						:class_id,
						:fund_id,
						'1',
						UNIX_TIMESTAMP()
					)
				";
				try{
					$rsInsert = $mLink->prepare($query);
					$aValues = array(
						':student_id'	=> $sID,
						':fund_id'		=> $studentFundID,
						':class_id'		=> $classID
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsInsert->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				echo $error;	
			}
				
		}
		
		echo '<pre>';
		print_r($aClasses);
		echo '</pre>';	
		
	break;
		
}

?>