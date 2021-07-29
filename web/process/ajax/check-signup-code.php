<?php
// This PHP script is run via an AJAX call to validate a entered email address during account creation
// Results of any failed check are returned as HTML to be displayed on the form, after which the script is discontinued - no need to proceed

// Start me up...
session_start();

// Load default functions
require("../../includes/system-debug-functions.php");
require("../../includes/system-functions.php");
//echo $_SERVER['QUERY_STRING'];
//echo "<br>";

$email = urldecode($_REQUEST['email']);
//echo $username;

// Let the jQuery validation handle the format validity - this facility is mainly concerned it's not already used

//Connect to DB
require_once("../../../secure/dbconnect.php");

//Load encryption functions
require_once("../../../secure/crypto.php");

$code = $_REQUEST['code'];

if($code != ''){
	$query = "
		SELECT *
		FROM system_signup_codes
		WHERE code=:code
	";
	try {
		$rsCheckCode = $mLink->prepare($query);
		$aValues = array(
			':code'	=> $code
		);
		// Prepared query - for error logging and debugging
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsCheckCode->execute($aValues);
	}
	catch(PDOException $error){
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$result = $rsCheckCode->rowCount();
	
	if($result > 0){
		
		$codes = $rsCheckCode->fetch(PDO::FETCH_ASSOC);
		
		$codeType 	= $codes['code_type'];
		$codeDesc	= $codes['code_desc'];
		$codeID		= $codes['code_id'];
		
		
		switch($codeType){
			
			case 'class':
				
				$forceEDU	= ($codes['force_edu'] == 1 ? true:false);
				$classID	= $codes['class_id'];
				$email 		= $_REQUEST['email'];
				
				$emailDomain = substr(strrchr($email, "@"), 1);
				
				if($forceEDU == true){
					if(strpos($emailDomain, '.edu') !== false){
						$eduValid = true;	
					}else{
						$eduValid = false;	
					}
				}else{
					$eduValid = true;	
				}
				
				if($eduValid == true){
				
					$query = "
						SELECT ec.*, es.name, es.university_name, es.university_symbol, m.name_first, m.name_last, m.name_title, m.name_suffix
						FROM `edu_class` AS ec 
						INNER JOIN `edu_teacher` AS et ON ec.teacher_id=et.teacher_id
						INNER JOIN `edu_schools` as es ON es.school_id=et.school_id
						INNER JOIN `members` AS m ON m.member_id=et.teacher_id
						WHERE ec.class_id=:class_id
					";
					try {
						$rsGetClass = $mLink->prepare($query);
						$aValues = array(
							':class_id'	=> $classID
						);
						// Prepared query - for error logging and debugging
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsGetClass->execute($aValues);
					}
					catch(PDOException $error){
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
					while($class = $rsGetClass->fetch(PDO::FETCH_ASSOC)){
						
						$teacherName 		= $class['name_title'].' '.$class['name_first'].' '.$class['name_last'].' '.$class['name_suffix'];
						$className			= $class['class_name'];
						$classDesc			= $class['class_desc'];
						$classTitle			= $class['title'];
						$classID			= $class['class_id'];
						
						$schoolName			= $class['name'];
						$universityName		= $class['university_name'];
						$universitySymbol	= $class['university_symbol'];
						
						$aStudentFunds		= explode('|', $class['student_ids']);
						$startDate			= $class['start_date'];
						$endDate			= $class['end_date'];
						
						if($universitySymbol != ''){
							$universityText = $universitySymbol.' | '.$universityName;
						}else{
							$universityText = $universityName;	
						}
						
						?>
						<div class="alert alert-success">
							<h4>Code Found!</h4>
							<p><?php echo $codeDesc;?></p>
						</div>
						
						<div class="note note-success">
							
							<h4>Class Info</h4>
							<h5><strong>Professor:</strong> <?php echo $teacherName;?></h5>
							<h5><strong>Class Name:</strong> <?php echo $className;?></h5>
							<h5><strong>Start Date:</strong> <?php echo date('m/d/Y', $startDate);?></h5>
							<h5><strong>End Date:</strong> <?php echo date('m/d/Y', $endDate);?></h5>
							<h5><strong>Class Description:</strong></h5>
							<p><?php echo $classDesc;?></p>
						</div>
						
						<br />
						
						<div class="note note-info">
							<h4>School Info:</h4>
							<h5><strong>School:</strong> <?php echo $schoolName;?></h5>
							<h5><strong>University:</strong> <?php echo $universityText;?></h5>
						</div>
						
						<input type="hidden" value="<?php echo $codeID;?>" name="signup_code_id" />
						<input type="hidden" value="1" name="is_class_code" />
						
						<input type="hidden" name="code_success"  />
						
						
						<?php
					}
				}else{
					echo '
						<div class="alert alert-danger">
							<h4>Code Error!</h4>
							<p>You must have a valid .EDU email address in order to use this code.</p>
						</div>
					';	
				}
			break;
			//END Class
			
			case 'discount':
				echo '<input type="hidden" name="code_success"  />';
			break;
			//END discount
			
			
		}//end cases
		
		
	}else{
		echo '<div class="alert alert-danger"><p><small>Code Not Found!</small></p></div>';
	}
}else{
	echo '<div class="alert alert-danger"><p><small>Code Not Found!</small></p></div>';
}
?>
