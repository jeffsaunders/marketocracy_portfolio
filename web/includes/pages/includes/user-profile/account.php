<?php
/*
Marketocracy Inc. | Beta Development
User Profile/Settings - Account Settings - Include File

Supporting files:
	AJAX			- process/ajax/user-settings-processes.php
	JAVASCRIPT		- JAVASCRIPT includes/scripts/user-settings.js.php
	HTML			- includes/pages/user-profile.php
	PHP Includes	- THIS DOCUMENT includes/pages/includes/user-profile/account.php
*/
?>

    
<form action="" method="post" name="save-account-settings" id="save-account-settings">    
    
	<h3 class="form-section">Member Info</h3>
	
    <div id="show-errors"></div>
    
    
    
    <div class="form-group" style="display:none;">
    	<label class="control-label">Name Prefix</label>
    	<input type="text" placeholder="John" class="form-control" />
    </div>
    
    <div class="form-group">
    	<label class="control-label">First Name<span class="required">*</span></label>
    	<input type="text" placeholder="John" class="form-control" name="name_first" id="name_first" value="<?php echo $member['name_first']; ?>" />
        <span class="help-block" id="name_first-help"></span>
    </div>
    <div class="form-group">
    	<label class="control-label">Last Name<span class="required">*</span></label>
    	<input type="text" placeholder="Doe" class="form-control" name="name_last" id="name_last" value="<?php echo $member['name_last']; ?>" />
        <span class="help-block" id="name_last-help"></span>
    </div>
    <div class="form-group">
    	<label class="control-label">Username</label>
    	<input type="text" id="username" placeholder="<?php echo $member['username']; ?>" class="form-control" value="<?php echo $member['username']; ?>" disabled/>
    </div>
    
    <div class="form-group" style="display:none;">
    	<label class="control-label">Name Suffix</label>
    	<input type="text" placeholder="John" class="form-control" />
    </div>
    
    <div class="form-group">
    	<label class="control-label">Email</label>
        <span id="change-email-orig">
    		<input type="text" placeholder="email address" class="form-control" value="<?php echo $member['email']; ?>"  disabled />
            <input type="hidden" name="email_orig" value="<?php echo $member['email'];?>" />
        	<button type="button" class="btn btn-info btn-xs" style="margin-top:5px;" onclick="changeEmail('change');">Change Email</button>
        </span>
    </div>
    
    <div id="change-email" style="background:#EFEFEF;border:1px solid #e5e5e5;padding:10px;border-radius:5px;margin:0px 0px 10px 0px;display:none;">
    	<div class="form-group">
            <label class="control-label">New Email<span class="required">*</span></label>
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="icon-envelope"></i>
                </span>
                <input type="text" class="form-control" name="email" id="email" placeholder="Your new email address" />
            </div>
            <span class="help-block" id="email-help">Provide your new email address</span>
            <div id="error_email"></div>
        </div>
    
        <div class="form-group">
            <label class="control-label">Confirm Email<span class="required">*</span></label>
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="icon-envelope"></i>
                </span>
                <input type="text" class="form-control" name="email2" id="email2" placeholder="Confirm your email address"/>
            </div>
            <span class="help-block" id="email2">Confirm your email address</span>
            <div id="error_email2"></div>
        </div>
        <button type="button" class="btn btn-info btn-xs" onclick="changeEmail('cancel');">Cancel Change Email</button>
    </div><!--change-email-->
    
    
    <div class="form-group">
        <label class="control-label">Daytime Telephone<span class="required">*</span></label>
        <div class="input-group">
            <span class="input-group-addon">
                <i class="icon-phone"></i>
            </span>
            <input type="text" class="form-control" name="phone_day" id="phone_day" placeholder="Your daytime phone number" value="<?php echo $member['phone_day']; ?>" />
        </div>
        <span class="help-block" id="phone_day-help"></span>
        <div id="error_phone_day"></div>
    </div>

    <div class="form-group">
        <label class="control-label">Evening Telephone</label>
        <div class="input-group">
            <span class="input-group-addon">
                <i class="icon-phone"></i>
            </span>
            <input type="text" class="form-control" name="phone_evening" id="phone_evening" placeholder="Your evening phone number" value="<?php echo $member['phone_evening']; ?>" />
        </div>
        <!--<span class="help-block"></span>-->
        <div id="error_phone_evening"></div>
    </div>

    <div class="form-group">
        <label class="control-label">Mobile Telephone</label>
        <div class="input-group">
            <span class="input-group-addon">
                <i class="icon-phone"></i>
            </span>
            <input type="text" class="form-control" name="phone_mobile" id="phone_mobile" placeholder="Your mobile phone number" value="<?php echo $member['phone_mobile']; ?>" />
        </div>
        <!--<span class="help-block"></span>-->
        <div id="error_phone_mobile"></div>
    </div>
    
    <div class="margiv-top-10">
    	<span><span class="required">*</span> Required</span><br />
    	<button type="submit" class="btn btn-info" id="submit-account-settings">Save Settings</button>
    	<a href="?page=10-00-00-002" class="btn btn-default">Cancel</a>
    </div>
</form>


<form action="" method="post" name="save-address-settings" id="save-address-settings">   
    <h3 class="form-section">Home Address</h3>
    
    <div id="show-address-errors"></div>
    
    <?php
	//Get countries (except US)
	$query = "
		SELECT *
		FROM ".$_SESSION['countries_table']."
		WHERE country_valid = 1
		AND country_name <> 'United States'
		AND country_name <> 'Canada'
		ORDER BY country_name
	";
	try {
		$rsCountries = $mLink->prepare($query);
		$rsCountries->execute();
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	?>

	<div class="form-group">
		<label class="control-label">Country<span class="required">*</span></label>
		<div class="input-group">
			<span class="input-group-addon">
				<i class="icon-flag"></i>
			</span>
<!--														<input type="text" class="form-control" name="country" value="<?php echo $member['country']; ?>" />-->
			<select name="country" id="country" class="select2 form-control" onChange="showStates('country='+this.value);">
				<option value="<?php echo $member['country_code'];?>|<?php echo $member['country'];?>"><?php echo $member['country'];?></option>
                <option value=""></option>
				<optgroup>
				<option value="US|United States" <?php echo (trim($member['country']) == "United States" ? " selected" : ""); ?>>United States</option>
				<option value="CA|Canada" <?php echo (trim($member['country']) == "Canada" ? " selected" : ""); ?>>Canada</option>
				<?php
				// Loop through countries, building option list
				while ($countries = $rsCountries->fetch(PDO::FETCH_ASSOC)){
					echo "<option value=\"".$countries['country_code_isa']."|".$countries['country_name']."\"".(trim($member['country']) == trim($countries['country_name']) ? ' selected' : '').">".$countries['country_name']."</option>\r";
				}
				?>
				</optgroup>
			</select>
		</div>
		<span class="help-block" id="country-help"></span>
	</div>

	<div class="form-group">
		<label class="control-label">Address<span class="required">*</span></label>
		<div class="input-group">
			<span class="input-group-addon">
				<i class="icon-home"></i>
			</span>
			<input type="text" class="form-control" id="address1" name="address1" value="<?php echo $member['address'];?>"/>
		</div>
		<span class="help-block" id="address1-help"></span>
	</div>
	<div class="form-group" style="margin-top:-10px;">
		<div class="input-group">
			<span class="input-group-addon">
				<i class="icon-plus" style="width:13px;"></i>
			</span>
			<input type="text" class="form-control" name="address2" value="<?php echo $member['address2'];?>"/>
		</div>
		<!--<span class="help-block"></span>-->
	</div>



	<div class="form-group">
		<label class="control-label">City<span class="required">*</span></label>
		<div class="input-group">
			<span class="input-group-addon">
				<i class="icon-font"></i>
			</span>
			<input type="text" class="form-control" name="city" id="city" value="<?php echo $member['city'];?>"/>
		</div>
		<span class="help-block" id="city-help"></span>
	</div>
<!-- Test for country here and adjust State/Province and Postal Code accordingly -->
	<?php
	//Get States/Provinces
	
		if ($member['country'] == "United States"){
			$query = "
				SELECT *
				FROM ".$_SESSION['states_table']."
				WHERE state_entity = 'State'
				OR state_entity = 'Territory'
				ORDER BY state_entity ASC, state_name ASC
			";
		}else{
			$query = "
				SELECT *
				FROM ".$_SESSION['states_table']."
				WHERE state_entity = 'Province'
				ORDER BY state_name
			";
		}
		try {
			$rsStates = $mLink->prepare($query);
			$rsStates->execute();
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	?>




<script>
// Ajax script to build states list
function showStates(params){
	
	var country = $('#country').val();
	
	//alert(country);
		
	if(country == 'US|United States' || country == 'CA|Canada'){
		
		$('#change-state-field').html('<select name="state" id="state-field" class="select2 form-control"></select>');
		
		if (window.XMLHttpRequest){ // Code for IE7+, Firefox, Opera, Webkit (Chrome, Safari, Rockmelt)
			xmlhttp=new XMLHttpRequest();
		}else{ // Code for IE6, IE5
			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		
		xmlhttp.open("GET","/process/ajax/setup-states.php?"+params);
		xmlhttp.send();
		
		xmlhttp.onreadystatechange = function(){
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
			
			
			document.getElementById("state-field").innerHTML = xmlhttp.responseText;
			}
		
		}
	}else{
		
		$('#change-state-field').html('<input type="text" id="state-field" name="state" class="form-control" />');
		
	}
	
	
}

function showStatesOnLoad(params){
	if (window.XMLHttpRequest){ // Code for IE7+, Firefox, Opera, Webkit (Chrome, Safari, Rockmelt)
		xmlhttp=new XMLHttpRequest();
	}else{ // Code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	xmlhttp.open("GET","/process/ajax/setup-states.php?"+params);
	xmlhttp.send();
	
	xmlhttp.onreadystatechange = function(){
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
		
		
		document.getElementById("state-field").innerHTML = xmlhttp.responseText;
		}
	
	}	
}
</script>

	<div class="form-group">
		<label class="control-label">State/Province<span class="required">*</span></label>
		<div class="input-group">
			<span class="input-group-addon">
				<i class="icon-star"></i>
			</span>
			<input type="hidden" class="form-control" id="state" name="state"/>
<!--<div id="state-field"></div>-->
			<span id="change-state-field">
			<select name="state" id="state-field" class="select2 form-control"></select>
            </span>
		</div>
		<span class="help-block"></span>
	</div>

				<!--<div id="state-field123"></div>-->

			<script>showStatesOnLoad('<?php echo "country=XX|".trim($member['country']); ?>&setState=<?php echo $member['state'];?>');</script>


	<div class="form-group">
		<label class="control-label">ZIP/Postal Code<span class="required">*</span></label>
		<div class="input-group">
			<span class="input-group-addon">
				<i class="icon-envelope"></i>
			</span>
			<input type="text" class="form-control" name="zip" value="<?php echo $member['zip_code'];?>"/>
		</div>
		<!--<span class="help-block"></span>-->
	</div>
	<?php
	
	?>
    
    <div class="margiv-top-10">
    	<button type="submit" class="btn btn-info" id="submit-address-settings">Save Address</button>
    	<a href="?page=10-00-00-002" class="btn btn-default">Cancel</a>
    </div>
    
</form>