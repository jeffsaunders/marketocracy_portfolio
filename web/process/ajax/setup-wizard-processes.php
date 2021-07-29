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


//+-------------------------------------------------------------------------------------+
//|							Load Address Fields | Process 1
//+-------------------------------------------------------------------------------------+
if($process == '1'){

	$country 		= $_REQUEST['country'];
	$aCountry 		= explode('|', $country);
	
	$country 		= $aCountry[1];
	$countryCode	= $aCountry[0];
	
	switch($countryCode){
		case 'US'	:
			
			$stateLookup	= true;
			$stateQuery		= "
				SELECT *
				FROM ".$_SESSION['states_table']."
				WHERE state_entity = 'State'
				OR state_entity = 'Territory'
				ORDER BY state_entity ASC, state_name ASC
			";	
			
			$stateType		= 'dropdown';
			$stateLabel		= 'State<span class="required">*</span>';
			$stateDropDown	= 'Select your State*';
			$stateTextField	= '';
			
			$optGroupStart	= '<optgroup label="States">';
			$optGroupEnd	= '</optgroup>';
			
			$zipLabel		= 'ZIP Code<span class="required">*</span>';
			$zipFieldLabel	= 'ZIP Code*';
			
			
		break;
		
		case 'CA'	:
			
			$stateLookup	= true;
			$stateQuery		= "
				SELECT *
				FROM ".$_SESSION['states_table']."
				WHERE state_entity = 'Province'
				ORDER BY state_name
			";
			
			$stateType		= 'dropdown';
			$stateLabel		= 'Province<span class="required">*</span>';
			$stateDropDown	= 'Select your Province*';
			$stateTextField	= '';
			
			$optGroupStart	= '<optgroup label="Provinces">';
			$optGroupEnd	= '</optgroup>';
			
			$zipLabel		= 'Postal Code<span class="required">*</span>';
			$zipFieldLabel	= 'Postal Code*';
			
		break;
			
		default:
			
			$stateLookup	= false;
			$stateQuery 	= "";
			
			$stateType		= 'text-field';
			$stateLabel		= 'State/Province/Region<span class="required">*</span>';
			$stateDropDown	= '';
			$stateTextField = 'State/Province/Region*';
			
			$optGroupStart	= '<optgroup label="Provinces">';
			$optGroupEnd	= '</optgroup>';
			
			$zipLabel		= 'ZIP/Postal Code<span class="required">*</span>';
			$zipFieldLabel	= 'ZIP/Postal Code*';
		
		break;
	}
	
	if($stateLookup == true){
		try {
			$rsStates = $mLink->prepare($stateQuery);
			$rsStates->execute();
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}
	
	?>
    <div class="form-group" style="margin:0 0 10px 0 !important;">
        <label class="control-label visible-ie8 visible-ie9">Address<span class="required">*</span></label>
        <div class="input-group"  style="width:100%;">
            <span class="input-group-addon" style="width:40px;">
                <i class="icon-home"></i>
            </span>
            <input type="text" class="form-control" name="address1" placeholder="Street Address*"/>
        </div>
       
       	<div id="error_address1"></div>
    </div>
    <div class="form-group"  style="margin:0 0 10px 0 !important;">
        <div class="input-group"  style="width:100%;">
            <span class="input-group-addon" style="width:40px;">
                <i class="icon-plus" style="width:13px;"></i>
            </span>
            <input type="text" class="form-control" name="address2" placeholder="Address Line 2"/>
        </div>
        <!--<span class="help-block"></span>-->
    </div>
    
    <div class="form-group" style="margin:0 0 10px 0 !important;">
        <label class="control-label visible-ie8 visible-ie9">City<span class="required">*</span></label>
        <div class="input-group"  style="width:100%;">
            <span class="input-group-addon" style="width:40px;">
                <i class="icon-map-marker"></i>
            </span>
            <input type="text" class="form-control" name="city" placeholder="City*"/>
        </div>
        <div id="error_city"></div>
    </div>
    
    
    <div class="form-group"  style="margin:0 0 10px 0 !important;">
        <label class="control-label visible-ie8 visible-ie9" style="margin:0 0 10px 0 !important;"><?php echo $stateLabel;?><span class="required">*</span></label>
        <input type="hidden" name="state-field-type" value="<?php echo $stateType;?>" />
        <div class="input-group"  style="width:100%;">
            <span class="input-group-addon" style="width:40px;">
                <i class="icon-star"></i>
            </span>
            <?php
			if($stateType == 'dropdown'){?>
                <select name="state" id="state-field" class="select2 form-control">
                    <option value=""><?php echo $stateDropDown;?></option>
                    <?php
                    //Get States/Provinces
                    
					echo $optGroupStart;
					                    
                    $states = $rsStates->fetch(PDO::FETCH_ASSOC);
                    $entity = $states['state_entity'];
                    // PDO for MySQL does not have a way to reset the pointer in the result set
                    // Therefore we must run the query again to get a fresh set with the pointer at the top
                    // Commonly mentioned workarounds do NOT work.
                    // Please fix this Zend or Oracle!
                    $rsStates->execute();
                    // Loop through states, building option list
                    while ($states = $rsStates->fetch(PDO::FETCH_ASSOC)){
                        if ($states['state_entity'] != $entity){
                            echo '
                                </optgroup>
                                <optgroup label = "Territories">
                            ';
                            $entity = $states['state_entity'];
                        }
                        
                        echo "<option value=\"".$states['state_abbrev']."|".$states['state_name']."\"".(trim($member['state']) == trim($states['state_name']) ? ' selected' : '')." ".$selected.">".$states['state_name']."</option>\r";
                    }
                    echo $optGroupEnd;
                    ?>
                </select>
            <?php
			}elseif($stateType == 'text-field'){
				?>
                <input type="text" name="state" class="form-control" placeholder="<?php echo $stateTextField;?>" />
                <?php
			}
			?>
        </div>
        <div id="error_state"></div>
        
    </div>


    <div class="form-group"  style="margin:0 0 10px 0 !important;">
        <label class="control-label visible-ie8 visible-ie9"><?php echo $zipLabel;?></label>
        <div class="input-group"  style="width:100%;">
            <span class="input-group-addon" style="width:40px;">
                <i class="icon-envelope"></i>
            </span>
            <input type="text" class="form-control" name="zip" id="submit_form_password" placeholder="<?php echo $zipFieldLabel;?>"/>
        </div>
        <div id="error_zip"></div>
    </div>
    <?php
	
}

//+-------------------------------------------------------------------------------------+
//|							Check Class Code | Process 2
//+-------------------------------------------------------------------------------------+
if($process == '2'){
	
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
				
				
				?>
                <div class="alert alert-success">
                	<h4>Class Found!</h4>
                    <p>Please review the class info below, if correct continue on to the next step.</p>
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
                    <h5><strong>University:</strong> <?php echo $universitySymbol;?> | <?php echo $universityName;?></h5>
                </div>
                
                <input type="hidden" value="<?php echo $code;?>" name="good_class_code" />
                <input type="hidden" value="<?php echo $classID;?>" name="class_id" />
                <input type="hidden" value="<?php echo $endDate;?>"	name="class_end_date" />
				
				
				<?php
				
			}
			
			
		}else{
			echo '<div class="alert alert-danger"><p><small>Code Not Found! If you do not have a class code at this time, select "No, I do not have a class code" to continue setup. You can join a class later.</small></p></div>';
			echo '<div style="overflow:hidden;position:relative;"><div style="position:absolute;height:10px;width:10px;right:-500px;top:50px;"><input type="text" name="class_code_check2" /></div></div>';	
		}
	}else{
		echo '<div class="alert alert-danger"><p><small>Code Not Found! If you do not have a class code at this time, select "No, I do not have a class code" to continue setup. You can join a class later.</small></p></div>';
		echo '<div style="overflow:hidden;position:relative;"><div style="position:absolute;height:10px;width:10px;right:-500px;top:50px;"><input type="text" name="class_code_check2" /></div></div>';	
	}
	
}

//+-------------------------------------------------------------------------------------+
//|								Check Email | Process 3
//+-------------------------------------------------------------------------------------+
if($process == '3'){
	
	$memberID		= $_SESSION['member_id'];
	
	//get decrypt functions
	require_once("../../../secure/crypto.php");
	
	$query = "
		SELECT *
		FROM ".$_SESSION['auth_table']."
		WHERE member_id=:member_id
		ORDER BY uid DESC
		LIMIT 1
	";
	try {
		$rsGetAuth = $mLink->prepare($query);
		$aValues = array(
			':member_id'	=> $memberID
		);
		// Prepared query - for error logging and debugging
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetAuth->execute($aValues);
	}
	catch(PDOException $error){
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$auth = $rsGetAuth->fetch(PDO::FETCH_ASSOC);
	
	$email = decrypt($auth['email']);
	
	$emailDomain = substr(strrchr($email, "@"), 1);
	
	if(strpos($emailDomain, '.edu') !== false){
		$eduValid = true;	
	}else{
		$eduValid = false;	
	}
	
	if($eduValid == false){
		if($auth['edu_email_validated_timestamp'] > 0){
			$eduValid = true;	
		}
	}
	
	if($eduValid == true){
		echo '
			<div class="alert alert-success">
        		<p>Your email has been sucessfully verified as a valid .edu address!<p>
				<input type="hidden" name="edu_email_passed" value="passed" />
        	</div>
		';
		
	}else{
		//echo 'You do not have a valid .edu email address.';	
		
		?>
        <div class="no-valid-edu-email"></div>
         
        <div class="form-group" id="edu-validate-field"  style="margin:0 0 10px 0 !important;">
            <label class="control-label">Please enter a valid .EDU email address</label>
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="icon-envelope"></i>
                </span>
                <input type="text" class="form-control" name="edu_email" id="edu_email" placeholder=".EDU Email Address" />
            </div>
            <!--<span class="help-block">Provide your email address</span>-->
            <div id="error_edu_email"></div>
            
            <button type="button" class="btn btn-sm btn-info" style="margin-top:5px;" onclick="validateEduEmail('edu_email');">Validate Email</button>
        </div>
        
        <div class="form-group hide" id="edu-code-field"  style="margin:0 0 10px 0 !important;">
            
            <div class="alert alert-info">
            	<p>An email with your Validation Code, has been sent to the email address you provided. Copy and paste the Validation Code into the field below, and click "Submit Code".<p>
                <p>Note: You may need to check your SPAM folder. If your verification email is in your SPAM folder, mark the email as "Not Spam" or move it to your inbox.</p>
            </div>
            
            <label class="control-label">Enter Validation Code</label>
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="icon-lock"></i>
                </span>
                <input type="text" class="form-control" name="validate_code" id="validate_code" placeholder="Validation Code" style="text-transform:uppercase;"/>
            </div>
            <div id="error_email_code2"></div>
            <button type="button" class="btn btn-sm btn-info" style="margin-top:5px;" onclick="submitValidateCode('validate_code');">Submit Code</button>
            <!--<span class="help-block">Provide your email address</span>-->
            <div id="error_email_code"></div>
        </div>
        
        <?php	
	}
	
}

//+-------------------------------------------------------------------------------------+
//|							SEND EDU VALIDATION CODE | Process 4
//+-------------------------------------------------------------------------------------+
if($process == '4'){
	$email			= $_REQUEST['email'];
	
	$emailDomain 	= substr(strrchr($email, "@"), 1);
	
	$aErrors		= array();
	
	if(strpos($emailDomain, '.edu') !== false){
		$eduValid = true;	
	}else{
		$eduValid = false;
		
		$aErrors[] = 'You must provide a .EDU email address to continue. Otherwise select "No, I am NOT a student".';	
	}
	
	if(empty($aErrors)){
		
		//Generate code to use in validation
		$code 	= gen_randomString(10,'numUpperSymbol');
		
		$query 	= "
			UPDATE ".$_SESSION['members_table']."
			SET edu_email=:edu_email, edu_verified_code=:code
			WHERE member_id=:member_id
		";
		try {
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $_SESSION['member_id'],
				':edu_email'	=> $email,
				':code'			=> $code
			);
			// Prepared query - for error logging and debugging
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		catch(PDOException $error){
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		echo $error;
		
		$aEmailVars = array(
			'edu_code'		=> $code,
			'support_email'	=> 'help@marketocracy.com'
		);
		$aEmail = array(
			'email_id'		=> '10',
			'to'			=> $email,
			'vars'			=> $aEmailVars,
			'headers_bcc'	=> 'brandon.mccarthy@marketocracy.com'
		);
		include('../../includes/email/system-email.php');
		?>
        
        
        <div class="show-code-box"></div>
        <?php
		
	}else{
		
		echo '<div class="alert alert-danger" style="margin:5px 0 0 0;"><ul>';
		foreach($aErrors as $key=>$error){
			echo '<li>'.$error.'</li>';
		}
		echo '</ul></div>';
	}
}


//+-------------------------------------------------------------------------------------+
//|								VERIFIY EDU EMAIL CODE | Process 5
//+-------------------------------------------------------------------------------------+
if($process == '5'){
	
	$code = $_REQUEST['code'];
	
	$query = "
		SELECT *
		FROM ".$_SESSION['members_table']."
		WHERE member_id=:member_id AND edu_verified_code=:code
	";
	try {
		$rsCheckCode = $mLink->prepare($query);
		$aValues = array(
			':member_id'	=> $_SESSION['member_id'],
			':code'			=> $code
		);
		// Prepared query - for error logging and debugging
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsCheckCode->execute($aValues);
	}
	catch(PDOException $error){
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$rowCount = $rsCheckCode->rowCount();
	
	if($rowCount > 0){
		//email is valid
		
		//update most recent Auth record
		$query = "
			UPDATE ".$_SESSION['auth_table']."
			SET edu_email_validated_timestamp=UNIX_TIMESTAMP()
			WHERE member_id=:member_id
		";
		try {
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $_SESSION['member_id']
			);
			// Prepared query - for error logging and debugging
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		catch(PDOException $error){
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		?>
        <div class="alert alert-success">
        	<p>Your email has been successfully verified as a valid .edu address!<p>
            <input type="hidden" name="edu_email_passed" value="passed" />
        </div>
        <?php
		
		echo '<div class="email-is-valid"></div>';
	}else{
		
		echo '<div class="alert alert-danger" style="margin-top:5px;"><p>Invalid Code!</p></div>';
	}
	
}

//+-------------------------------------------------------------------------------------+
//|							VALIDATE FUND SYMBOL | Process 6
//+-------------------------------------------------------------------------------------+
if($process == '6'){
	
	$fundSymbol		= $_REQUEST['fundSymbol'];
	
	$aErrors 		= array();
	
	if (preg_match('/[^a-zA-Z\-.0-9]+/', $fundSymbol)){
	  $aErrors[] = 'Fund Symbol can only contain Letters and Numbers. The following symbols are allowed: -(dash), .(period)';
	}
	
	if(strlen($fundSymbol) > 8){
		$aErrors[] = 'Fund Symbol can not exceed 8 characters.';	
	}
	
	if(empty($aErrors)){
		
	}else{
		//create a div that appears off screen so that our javascript can validate the field without us seeing it. 
		echo '<div style="overflow:hidden;position:relative;"><div style="position:absolute;height:10px;width:10px;right:-500px;top:50px;"><input type="text" name="fund_symbol_check" /></div></div>';
		
		//show errors
		echo '<div class="alert alert-danger"><ul>';
		foreach($aErrors as $key=>$error){
			echo '<li>'.$error.'</li>';	
		}
		echo '</ul></div>';
	}
}

//+-------------------------------------------------------------------------------------+
//|							Process setup wizard | Process 7
//+-------------------------------------------------------------------------------------+
if($process == '7'){
	
	
	if($debug == '1'){
		echo '<pre>';
		print_r($_POST);
		echo '</pre>';
	}
	
	//Set variables
	$aErrors		= array();
	//$aErrors[] 		= 'test-error';
	
	//Basic Info
	$nameTitle		= $_REQUEST['title']; 		//NR
	$nameMiddle		= $_REQUEST['middle_name'];	//NR
	$nameSuffix		= $_REQUEST['suffix']; 		//NR
	$nameFirst		= $_REQUEST['first_name']; 	//R
	$nameLast		= $_REQUEST['last_name']; 	//R
	
	$altEmail		= $_REQUEST['alt_email'];	//NR
	$phoneDay		= $_REQUEST['phone_day']; 	//R
	$phoneEvening	= $_REQUEST['phone_evening'];	//NR
	$phoneMobile	= $_REQUEST['phone_mobile'];	//NR
	
	$country		= $_REQUEST['country'];		//R
	$aCountry 		= explode('|', $country);
	
	$country 		= $aCountry[1];
	$countryCode	= $aCountry[0];
	$address1		= $_REQUEST['address1'];	//R
	$address2		= $_REQUEST['address2'];	//NR
	$city			= $_REQUEST['city'];		//R
	$state			= $_REQUEST['state'];		//R
	$aState			= explode('|',$state);
	
	$state			= $aState[0];
	$zip			= $_REQUEST['zip'];			//R
	
	
	
	//Profile Info
	$occupation		= $_REQUEST['occupation'];
	$industry		= $_REQUEST['industry'];
	$interest		= $_REQUEST['interest'];
	$day			= $_REQUEST['day'];
	$month			= $_REQUEST['month'];
	$year			= $_REQUEST['year'];
	$dob			= $year.'-'.$month.'-'.$day;
	$gender			= $_REQUEST['gender'];
	$linkedin		= $_REQUEST['linkedin'];
	$google			= $_REQUEST['google'];
	$webSite		= $_REQUEST['web_site'];
	$twitter		= $_REQUEST['twitter'];
	$facebook		= $_REQUEST['facebook'];
	$bio			= $_REQUEST['bio'];
	$aQuestionIDs 	= explode('|',$_REQUEST['questions']);
	
	//Student Info
	$isStudent		= $_REQUEST['student']; //true or false
	
	if($isStudent == 'true'){
		$eduEmailPassed	= $_REQUEST['edu_email_passed']; //must be equal to "passed"
		
		
		
		if($eduEmailPassed != 'passed'){
			$makeStudent = false;
			$aErrors[] = 'You must have a valid .edu email address to signup as a student.';	
		}else{
			$makeStudent = true;
		}
		
		$classCodeCheck	= $_REQUEST['class_code_check']; //true or false
		
		if($classCodeCheck == 'true'){
			$classCode	= $_REQUEST['good_class_code'];
			$classID	= $_REQUEST['class_id'];
			$classEndDate	= $_REQUEST['class_end_date'];
			
			if(!isset($classCode)){
				$aErrors[] = 'Class Code is not valid.';
			}
		}
	}//end student check
	
	//New Fund
	$fundType		= $_REQUEST['fund_type']; 	//R
	$fundName		= $_REQUEST['fund_name']; 	//R
	$fundSymbol		= $_REQUEST['fund_symbol'];	//R
	$fundDesc		= $_REQUEST['fund_desc'];	//NR
	
	//Error Check for Basic Info
	if(empty($nameFirst)){
		$aErrors[] 	= 'You must provide your First Name.';	
	}
	if(empty($nameLast)){
		$aErrors[] 	= 'You must provide your Last Name.';	
	}
	/*if(empty($phoneDay)){
		$aErrors[] 	= 'You must provide a day time reachable phone number.';	
	}
	
	if(empty($country)){
		$aErrors[] 	= 'You must provide your country of residence.';	
	}
	if(empty($address1)){
		$aErrors[] 	= 'You must provide your address.';	
	}
	if(empty($city)){
		$aErrors[] 	= 'You must provide your city of residence.';	
	}
	if(empty($state)){
		$aErrors[] 	= 'You must provide your state of residence.';	
	}
	if(empty($zip)){
		$aErrors[] 	= 'You must provide your zip/postal code.';	
	}*/
	
	//Error check for Fund Info
	if(empty($fundName)){
		$aErrors[] 	= 'You must provide a Fund Name.';	
	}
	
	if(empty($fundSymbol)){
		$aErrors[]	= 'You must provide a Fund Symbol.';	
	}
		
	if (preg_match('/[^a-zA-Z\-.0-9]+/', $fundSymbol)){
	  	$aErrors[] = 'Fund Symbol can only contain Letters and Numbers. The following symbols are allowed: -(dash), .(period)';
	}
	
	if(strlen($fundSymbol) > 8){
		$aErrors[] = 'Fund Symbol can not exceed 8 characters.';	
	}
	
	//If no errors continue
	if(empty($aErrors)){
		
		//START | Create new members_profile table entry
		$query = "
			INSERT INTO ".$_SESSION['members_profile_table']." (
				member_id,
				about_me,
				occupation,
				industry,
				interests,
				DOB,
				gender,
				personal_site,
				linkedin,
				facebook,
				twitter,
				google,
				version,
				timestamp
			)VALUE(
				:member_id,
				:bio,
				:occupation,
				:industry,
				:interest,
				:DOB,
				:gender,
				:personal_site,
				:linkedin,
				:facebook,
				:twitter,
				:google,
				'1',
				UNIX_TIMESTAMP()
			)
		";
		try{
			$rsAddProfile = $mLink->prepare($query);
			$aValues = array(
				':member_id'		=> $_SESSION['member_id'],
				':bio'				=> $bio,
				':occupation'		=> $occupation,
				':industry'			=> $industry,
				':interest'			=> $interest,
				':DOB'				=> $dob,
				':gender'			=> $gender,
				':personal_site'	=> $webSite,
				':linkedin'			=> $linkedin,
				':facebook'			=> $facebook,
				':twitter'			=> $twitter,
				':google'			=> $google
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsAddProfile->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		//END INSERT NEW RECORD INTO PROFILE TABLE
		
		if($debug == '1'){
			echo 'Step 1 - Insert Profile Table<br />';
			echo $error;
			echo '<br /><br />';
		}
		
		//START | CREATE NEW FUND RECORD
		$fundID = get_new_fund_id($mLink, $_SESSION['member_id']);
		$aFundID = explode('-', $fundID);
		$seqID = $aFundID[1];
		
		
        $fundColor = '#39B3D7';
		
		//set default columns
		$col1 = 'fund-price-history~0~0~0|fund-pos-sectors~0~0~0|fund-info~0~0~0|fund-turnover~0~0~0|fund-best-worst~0~0~0';
		$col2 = 'fund-returns-index~0~0~0|fund-ratios~0~0~0|fund-pos-style~0~0~0|fund-recent-returns~0~0~0|fund-alpha-beta~0~0~0|fund-profit~0~0~0';
		
		$query = "
			INSERT INTO ".$_SESSION['fund_table']." (
				fund_id,
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
		
		/*echo 'Step 2 - Create new fund insert<br />';
		echo $error;
		echo '<br /><br />';*/
		
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
		
		if($debug == '1'){
			echo 'Step 3 - Create Fund Settings record<br />';
			echo $error;
			echo '<br /><br />';
		}

        /*$query = 'newFund|'.$_SESSION['username'].'|long|'.$fundName.'|'.$fundSymbol.'|'.$fundID.'';
        include('../../includes/data-query-legacy.php');*/
		
		$aMethodVars[] = array(
			'method'		=> 'newFund', #method name 
			'source'		=> 'Setup Wizard | setup-wizard-processes.php | process: 7 | line: '.__LINE__, #File and Code location of this instance 
			'api'			=> '1', #api switch, 1 = api1, 2 = api2 
			'username'		=> $_SESSION['username'], #username of member
			'fund_type'		=> 'long',
			'fund_name'		=> $fundName, 
			'fund_symbol'	=> $fundSymbol, #fund symbol 
			'fund_id' 		=> $fundID, #members fund ID 'group'	=> 'N/A' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
		); 
		$mlaResults = legacy_api($mLink, $aMethodVars, true);
		
		if($debug == '1'){
			echo 'Step 4 - Send Data Legacy Query<br />';
			echo $query;
			echo '<br /><br />';
		}
		
        //echo $query;

        /*$event = 'Fund Creation';
        $detail = 'Member has created a new fund: '.$_SESSION['username'].':'.$fundSymbol.'.';
        addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);*/
		
		//END | CREATE NEW FUND RECORD
		
		//START | UPDATE MEMBER RECORD
		$query = "
			UPDATE ".$_SESSION['members_table']."
			SET name_title=:name_title, name_first=:name_first, name_middle=:name_middle, name_last=:name_last,	name_suffix=:name_suffix, alt_email=:alt_email,	phone_day=:phone_day, phone_evening=:phone_evening, phone_mobile=:phone_mobile,	country=:country, country_code=:country_code, address=:address1, address2=:address2, city=:city, state=:state, zip_code=:zip, timestamp=UNIX_TIMESTAMP()
			WHERE member_id=:member_id
		";
		try{
            $rsUpdateMember = $mLink->prepare($query);
            $aValues = array(
                ':member_id'		=> $_SESSION['member_id'],
				':name_title'		=> $nameTitle,
				':name_first'		=> $nameFirst,
				':name_middle'		=> $nameMiddle,
				':name_last'		=> $nameLast,
				':name_suffix'		=> $nameSuffix,
				':alt_email'		=> $altEmail,
				':phone_day'		=> $phoneDay,
				':phone_evening'	=> $phoneEvening,
				':phone_mobile'		=> $phoneMobile,
				':country'			=> $country,
				':country_code'		=> $countryCode,
				':address1'			=> $address1,
				':address2'			=> $address2,
				':city'				=> $city,
				':state'			=> $state,
				':zip'				=> $zip				
            );
            $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
            $rsUpdateMember->execute($aValues);
        }
        catch(PDOException $error){
            // Log any error
            file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
        }
		//END | UPDATE MEMBER RECORD
		if($debug == '1'){
			echo 'Step 5 - update members table<br />';
			echo $error;
			echo '<br /><br />';
		}
		
		//START | write profile questions to DB
		foreach($aQuestionIDs as $key=>$QID){
		
			$answer = $_REQUEST['pro_question_'.$QID.''];
		
				//check to see if answer is an array
				if(is_array($answer) == true){
					//answer is an array
					$answer = implode('|', $answer);
				}else{
					//answer is not an array
					$answer = $answer;
				}
				
				if($answer != ''){
					$query = "
						INSERT INTO ".$_SESSION['profile_answers_table']." (
							qid,
							member_id,
							answer,
							timestamp
						) VALUE (
							:qid,
							:member_id,
							:answer,
							UNIX_TIMESTAMP()
						)
					";
					
					try{
						$rsInsertAnswer = $mLink->prepare($query);
						$aValues = array(
							':qid'				=> $QID,
							':member_id' 		=> $_SESSION['member_id'],
							':answer'			=> $answer,
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsInsertAnswer->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
				}
	
		}//end for each question
		//END | write profile questions to db
		
		//START | CREATE STUDENT RECORDS
		if($classID != ''){
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
				array_push($aStudentIDs, $fundID);
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
				INSERT INTO edu_students (
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
					':fund_id'			=> $fundID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsInsertStudent->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			
			
			
			if($debug == '1'){
				echo 'Step 6 - Create student record<br />';
				echo $error;
				echo '<br />'.$_SESSION['member_id'].'|'.$classID.'|'.$fundID.'<br /><br />';
			}
		}
		//END | CREATE STUDENT RECORDS
		
		//Determin Trial period
		$trialStart = time();
		
		if($makeStudent == true){
		
			if($classEndDate != ''){
				$trialEnd = strtotime("+7 days", $classEndDate);
			}else{
				$trialEnd = strtotime("+30 days");	
			}
			
		}else{
			$trialEnd = strtotime("+30 days");
		}		
		
		//Update Auth Table
		$query = "
			UPDATE ".$_SESSION['auth_table']."
			SET trial_start=:trial_start, trial_end=:trial_end
			WHERE member_id=:member_id
		";
		try{
			$rsUpdateAuth = $mLink->prepare($query);
			$aValues = array(
				':member_id' 		=> $_SESSION['member_id'],
				':trial_start'		=> $trialStart,
				':trial_end'		=> $trialEnd
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdateAuth->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		if($debug == '1'){	
			echo 'Step 7 - update auth table<br />';
			echo $error;
			echo '<br /><br />';
		}
		//END | DETERMIN TRIAL PERIOD
		
		//START UPDATE FLAGS
		if($makeStudent == true){
			$_SESSION['flag_premium'] = 1;
			$_SESSION['trial'] = 1;
			$_SESSION['student'] = 1;
			
			$query = "
				UPDATE ".$_SESSION['members_flags_table']."
				SET trial='1', premium='1', student='1'
				WHERE member_id=:member_id
			";	
		}else{
			$_SESSION['flag_premium'] = 1;
			$_SESSION['trial'] = 1;
			$query = "
				UPDATE ".$_SESSION['members_flags_table']."
				SET trial='1', premium='1'
				WHERE member_id=:member_id
			";	
		}
		try{
			$rsUpdateFlags = $mLink->prepare($query);
			$aValues = array(
				':member_id' 		=> $_SESSION['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdateFlags->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		if($debug == '1'){
			echo 'Step 8 - set flags<br />';
			echo $error;
			echo '<br /><br />';
		}
		//END UPDATE FLAGS
		
		
		//All went well, not lets set their seesion variables!
		$_SESSION['first_name'] = $nameFirst;
		$_SESSION['last_name']	= $nameLast;
		
		
		echo 'success';
		
	}else{
		echo '<div class="alert alert-danger">';
		
		print_r($_POST);
		
		echo '<ul>';
		foreach($aErrors as $key=>$error){
			echo '<li>'.$error.'</li>';
		}
		echo '</ul></div>';
	}
	
	
	
	
}




