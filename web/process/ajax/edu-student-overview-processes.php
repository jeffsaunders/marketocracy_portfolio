<?php
/*
Marketocracy Inc. | Beta Development
Fund Trades

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/fund-trades-processes.php
	JAVASCRIPT	- includes/scripts/fund-trades.js.php
	HTML		- includes/pages/fund-trades.php
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
//|									 - PROCESS 1										|
//+---------------------------------------------------------------------------------------------------------+

switch($process){
	case 'code-check':
		
		$code		= trim($_REQUEST['code']);
	
		if($code != ''){
		
			//Check to see if code is valide
			$query = "
				SELECT *
				FROM `edu_class`
				WHERE class_code=:code AND active='1'
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
			
				$query = "
					SELECT ec.*, es.name, es.university_name, es.university_symbol, m.name_first, m.name_last, m.name_title, m.name_suffix
					FROM `edu_class` AS ec 
					INNER JOIN `edu_teacher` AS et ON ec.teacher_id=et.teacher_id
					INNER JOIN `edu_schools` as es ON es.school_id=et.school_id
					INNER JOIN `members` AS m ON m.member_id=et.teacher_id
					WHERE ec.class_code=:code
				";
				try {
					$rsGetClass = $mLink->prepare($query);
					$aValues = array(
						':code'	=> $code
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
					
					$aStudentIDs		= explode('|', $class['student_ids']);
					
					$query = "
						SELECT * 
						FROM ".$_SESSION['students_table']."
						WHERE class_id=:class_id AND student_id=:student_id
					";
					try {
						$rsGetStudent = $mLink->prepare($query);
						$aValues = array(
							':class_id'		=> $classID,
							':student_id'	=> $_SESSION['member_id']
						);
						// Prepared query - for error logging and debugging
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsGetStudent->execute($aValues);
					}
					catch(PDOException $error){
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					$rowCount = $rsGetStudent->rowCount();
					
					if($rowCount > 0){
						//Student is already enrolled in class
						
						?>
						<div class="alert alert-success">
							<h4>Class Found!</h4>
							<p>You are already enrolled into this class. If the code you provided is not the right code, please try another one now. If not, please close this box.</p>
						</div>
                        
                        <input type="hidden" value='true' name="enrolled" />
                        
                        <?php
						
					}else{
					
						?>
						<div class="alert alert-success">
							<h4>Class Found!</h4>
							<p>Please review the class info below, if correct please select one of your funds to be added to the class and click "Join Class".</p>
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
							<h5><strong>University:</strong> <?php echo $universitySymbol;?> <?php if($universitySymbol != ''){echo'|';}?> <?php echo $universityName;?></h5>
						</div>
						
						<div class="form-group">
							<!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
							<label class="control-label" id="class_code_label">Select Fund<span class="required">*</span></label>
							
							<select name="student_fund_id" class="form-control">
								<option value='xx'>Please Select A Fund</option>
								<?php
									$aFunds = get_fund_symbols($mLink, $_SESSION['member_id'], 'funds');
									
									foreach($aFunds as $studentFundID=>$studentFundSymbol){
										
										echo '<option value="'.$studentFundID.'">'.$studentFundSymbol.'</option>';
											
									}
								?>	
							</select>
						</div>
						
						<input type="hidden" value="<?php echo $code;?>" name="good_class_code" />
						<input type="hidden" value="<?php echo $classID;?>" name="class_id" />
						<input type="hidden" value="<?php echo $endDate;?>"	name="class_end_date" />
						
						
						<input type="submit" value="Join Class" class="btn btn-info" id="submit-forum" />
						<?php
						
					}
					
				}//end in class check
				
				
			}else{
				echo '<div class="alert alert-danger"><p><small>Code Not Found! If you do not have a class code at this time, select "No, I do not have a class code" to continue setup. You can join a class later.</small></p></div>';
				echo '<div style="overflow:hidden;position:relative;"><div style="position:absolute;height:10px;width:10px;right:-500px;top:50px;"><input type="text" name="class_code_check2" /></div></div>';	
			}
		}else{
			echo '<div class="alert alert-danger"><p><small>Code Not Found! If you do not have a class code at this time, select "No, I do not have a class code" to continue setup. You can join a class later.</small></p></div>';
			echo '<div style="overflow:hidden;position:relative;"><div style="position:absolute;height:10px;width:10px;right:-500px;top:50px;"><input type="text" name="class_code_check2" /></div></div>';	
		}
		
	break;
	
	case 'submit-join-class':
		
		$enrolled			= $_REQUEST['enrolled'];
		
		
		
		$classID 			= $_REQUEST['class_id'];
		$classCode			= $_REQUEST['good_class_code'];
		$studentFundID		= $_REQUEST['student_fund_id'];
		$aErrors			= array();
		
		if(empty($studentFundID)){
			$aErrors[] 		= 'You must select a fund to join the class.'; 	
		}
		
		if($studentFundID	== 'xx'){
			$aErrors[] 		= 'You must select a fund to join the class.'; 	
		}
		
		if(empty($classID)){
			$aErrors[]		= 'Warning! Class ID not found. Please submit a <a href="https://portfolio.marketocracy.com/?page=08-01-00-001" target="_blank">support ticket</a> and add the following to the description:<br /><br /><strong>Class Code:</strong> '.$classCode.'<br /><strong>Class ID:</strong> '.$classID.'';	
		}
		
		if($enrolled == 'true'){
			$aErrors 		= array();
			$aErrors[]		= 'You are already enrolled in this class.';
		}
		
		if(empty($aErrors)){
			$query = "
				SELECT * 
				FROM ".$_SESSION['class_table']."
				WHERE class_id=:class_id
			";
			try{
				$rsGetClass = $mLink->prepare($query);
				$aValues = array(
					':class_id'			=> $classID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetClass->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$class = $rsGetClass->fetch(PDO::FETCH_ASSOC);
			
			$aStudentIDs = explode('|', $class['student_ids']);
			
			if(!in_array($studentFundID, $aStudentIDs)){
				array_push($aStudentIDs, $studentFundID);
			}
			
			$studentIDs	= implode('|', $aStudentIDs);
			
			$query = "
				UPDATE ".$_SESSION['class_table']."
				SET student_ids=:student_ids
				WHERE class_id=:class_id
			";
			try{
				$rsUpdateClass = $mLink->prepare($query);
				$aValues = array(
					':class_id'			=> $classID,
					':student_ids'		=> $studentIDs
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdateClass->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$query = "
				INSERT INTO ".$_SESSION['students_table']." (
					student_id,
					class_id,
					fund_id,
					active,
					timestamp
				)VALUE(
					:member_id,
					:class_id,
					:fund_id,
					'1',
					UNIX_TIMESTAMP()
				)
			";
			try{
				$rsInsertStudent = $mLink->prepare($query);
				$aValues = array(
					':member_id' 		=> $_SESSION['member_id'],
					':class_id'			=> $classID,
					':fund_id'			=> $studentFundID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsInsertStudent->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			echo $error;
			
		}else{
			
			echo '<div class="note note-danger"><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';
			}
			echo '</ul></div>';
				
		}
		
		/*echo '<pre>';
		print_r($_POST);
		echo '</pre>';*/
	
	break;
	
}
?>