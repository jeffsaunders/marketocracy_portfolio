<?php
/*
Marketocracy Inc. | Beta Development
User Profile/Settings - Personal Info - Include File

Supporting files:
	AJAX			- process/ajax/user-settings-processes.php
	JAVASCRIPT		- JAVASCRIPT includes/scripts/user-settings.js.php
	HTML			- includes/pages/user-profile.php
	PHP Includes	- THIS DOCUMENT includes/pages/includes/user-profile/personal-info.php
*/

?>
<h3 class="form-section">Profile Info <span><a href="/?page=04-00-00-001" class="btn btn-info btn-sm">Return To Public Profile</a></span></h3>
<form role="form" action="" method="post" name="save-profile" id="save-profile">
   
    <div class="form-group">
    	<?php
		$aInterests = explode("|", $member['interests']);
		?>
        <label class="control-label">Interests</label>
        <input type="text" placeholder="Marketing, Stocks" name="interests" id="interests" class="form-control" value="<?php echo implode(', ', $aInterests);?>" />
        <span class="help-block">Please seperate interests by comma.</span>
    </div>
        <div class="form-group">
        <label class="control-label">Occupation</label>
        <input type="text" name="occupation" id="occupation" class="form-control" value="<?php echo $member['occupation'];?>" />
    </div>
    
    <label class="control-label">Date of Birth (DD/MM/YYYY)</label>
    <div class="form-inline" role="form">
    	
        <?php 
		//echo $member['DOB'];
		$dob = date_create($member['DOB']);
		$month 	= date_format($dob, 'm');
		$day	= date_format($dob, 'd');
		$year	= date_format($dob, 'Y');
		if($member['DOB'] == '0000-00-00'){
			$month = '';
			$year = '';
			$day = '';
		}
		?>
        <div class="form-group">
            
            <select name="month" class="form-control input-small">
                <option value="">MM</option>
				<?php echo printList($mLink, '7', $month);	?>
            </select> /
       	</div>
       	<div class="form-group">
            <select name="day" class="form-control input-small">
            	<option value="">DD</option>
                <?php echo printList($mLink, '6', $day); ?>
            </select> /
        </div>
        <div class="form-group">
        	<input type="text" placeholder="YYYY" name="year" class="form-control input-small" value="<?php echo $year;?>" />
        </div>
    </div><!--form-inline-->
    <br />
    
    <div class="form-group" id="summary">
        <label class="control-label">Summary (About You)</label>
        <textarea class="form-control" rows="5" id="about_me" name="about_me" placeholder="This is my profile information!"><?php echo $member['about_me'];?></textarea>
    </div>
    <div class="form-group">
        <label class="control-label">Personal Website</label>
        <input type="text" name="personal_site" placeholder="http://www.mywebsite.com" class="form-control" value="<?php echo $member['personal_site'];?>" />
    </div>
    <div class="form-group">
        <label class="control-label">LinkedIn URL</label>
        <input type="text" name="linkedin_url" placeholder="" class="form-control" value="<?php echo $member['linkedin'];?>" />
    </div>
    <div class="margiv-top-10">
        <button type="submit" class="btn btn-info" id="submit-profile">Save Changes</button>
        <a href="?page=10-00-00-002" class="btn btn-default">Cancel</a>
    </div>
</form>

<h3 class="form-section">Profile Questions</h3>
<form role="form" action="#" method="post" name="profile-questions" id="profile-questions" class="form-row-seperated">

	<?php
	$query = "
		SELECT *
		FROM ".$_SESSION['profile_questions_table']."
		WHERE active='1'
		ORDER BY sequence ASC
	";
	try{
		$rsGetQs = $mLink->prepare($query);
		$aValues = array(
			//':list_id' 	=> $listID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetQs->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aQuestionIDs = array();
	
	while($question = $rsGetQs->fetch(PDO::FETCH_ASSOC)){
		
		$QID 		= $question['qid'];
		$type		= $question['type'];
		
		$aQuestionIDs[] = $QID;
		
		if($question['options'] != ''){
			$aOptions	= explode('~',$question['options']);
		}
		
		$query = "
			SELECT *
			FROM ".$_SESSION['profile_answers_table']."
			WHERE member_id=:member_id AND qid=:qid
			ORDER BY timestamp DESC
			LIMIT 1
		";
		try{
			$rsGetAnswer = $mLink->prepare($query);
			$aValues = array(
				':member_id' 	=> $_SESSION['member_id'],
				':qid'			=> $QID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetAnswer->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	
		$answer = $rsGetAnswer->fetch(PDO::FETCH_ASSOC);
		
		//swith on question type
		switch($type){
			case 'textarea' :
				?>
                <div class="form-group">
                    <label class="control-label"><strong><?php echo $question['question'];?> <?php if($question['note'] != ''){ echo '('.$question['note'].')'; }?></strong></label>
                    <textarea class="form-control wysihtml5" rows="4" id="<?php echo $QID;?>" name="<?php echo $QID;?>"><?php echo $answer['answer'];?></textarea>
                </div>
                <?php
			break;
			
			case 'radio' :
				?>
                <div class="form-group">
                	<label class="control-label"><strong><?php echo $question['question'];?> <?php if($question['note'] != ''){ echo '('.$question['note'].')'; }?></strong></label>
                    <div class="radio-list">
                    	<?php
						$cnt = 0;
						
						foreach($aOptions as $key=>$option){
								
							$cnt++;
							
							$aOption = explode('|', $option);
							
							if($answer['answer'] == $aOption[1]){
								echo '<label class="radio-inline" style="border:none;"><input type="radio" name="'.$QID.'[]" id="'.$QID.'-option-'.$cnt.'" value="'.$aOption[1].'" checked> '.$aOption[0].'</label>';	
							}else{
								echo '<label class="radio-inline" style="border:none;"><input type="radio" name="'.$QID.'[]" id="'.$QID.'-option-'.$cnt.'" value="'.$aOption[1].'"> '.$aOption[0].'</label>';
							}
						}
						?>
                        
                    </div>
                </div>
                <?php
			break;	
			case 'text' :
				?>
                <div class="form-group">
                    <label class="control-label"><strong><?php echo $question['question'];?> <?php if($question['note'] != ''){ echo '('.$question['note'].')'; }?></strong></label>
                    <input type="text" name="<?php echo $QID;?>" id="<?php echo $QID;?>" class="form-control" value="<?php echo $answer['answer'];?>" />
                </div>
                <?php
			break;
			case 'select' :
				?>
                <div class="form-group">
               		<label class="control-label"><strong><?php echo $question['question'];?> <?php if($question['note'] != ''){ echo '('.$question['note'].')'; }?></strong></label>
                    <select name="<?php echo $QID;?>" id="<?php echo $QID;?>" class="form-control">
                    	<?php
						$cnt = 0;
						
						foreach($aOptions as $key=>$option){
								
							$cnt++;
							
							$aOption = explode('|', $option);
							
							if($answer['answer'] == $aOption[1]){
								echo '<option value="'.$aOption[1].'" selected>'.$aOption[0].'</option>';	
							}else{
								echo '<option value="'.$aOption[1].'">'.$aOption[0].'</option>';
							}
						}
						?>
                    </select>
                </div>
                <?php
			break;
			case 'checkbox' :
				?>
                <div class="form-group">
                    <label class="control-label"><strong><?php echo $question['question'];?> <?php if($question['note'] != ''){ echo '('.$question['note'].')'; }?></strong></label>
                    <div class="checkbox-list">
                    	<?php
						$cnt = 0;
						
						foreach($aOptions as $key=>$option){
								
							$cnt++;
							
							$aOption = explode('|', $option);
							
							$aAnswers = explode('|', $answer['answer']);
							
							if(in_array($aOption[1], $aAnswers)){
								echo '<label class="checkbox-inline"><input type="checkbox" name="'.$QID.'[]" id="'.$QID.'-'.$cnt.'" value="'.$aOption[1].'" checked> '.$aOption[0].'</label>';	
							}else{
								echo '<label class="checkbox-inline"><input type="checkbox" name="'.$QID.'[]" id="'.$QID.'-'.$cnt.'" value="'.$aOption[1].'"> '.$aOption[0].'</label>';
							}
						}
						?>
                    </div>
                </div>
                <?php
			break;
		}//end switch
		
		
	}
	?>
    
    <input type="hidden" name="questions" value="<?php echo implode('|', $aQuestionIDs);?>" />
    
    <div class="margiv-top-10" style="margin-top:20px;">
        <button type="submit" class="btn btn-info" id="submit-answers">Save Answers</button>
        <a href="?page=10-00-00-002" class="btn btn-default">Cancel</a>
    </div>

</form>