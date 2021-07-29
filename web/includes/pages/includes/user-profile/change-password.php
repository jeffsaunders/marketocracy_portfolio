<?php
/*
Marketocracy Inc. | Beta Development
User Profile/Settings - Change Password - Include File

Supporting files:
	AJAX			- process/ajax/user-settings-processes.php
	JAVASCRIPT		- JAVASCRIPT includes/scripts/user-settings.js.php
	HTML			- includes/pages/user-profile.php
	PHP Includes	- THIS DOCUMENT includes/pages/includes/user-profile/change-password.php
*/

?>

<form action="process/ajax/user-settings-processes.php?process=19" method="post" name="change-password" id="change-password">
    <div id="pw-error"></div>
    
    <div class="form-group">
        <label class="control-label">Current Password <span class="required">*</span></label>
        <input type="password" class="form-control" name="current_pw" id="current-pw"/>
        <span id="current-pw-help"></span>
    </div>
    <div class="form-group">
    	
        <label class="control-label">New Password <span class="required">*</span></label>
        <input type="password" class="form-control" name="new_pw" id="new-pw" />
        <div id="password-score"></div>
        <span id="new-pw-help"></span>
    </div>
    <div class="form-group">
        <label class="control-label">Re-type New Password <span class="required">*</span></label>
        <input type="password" class="form-control" name="new_pw2" id="new-pw2" />
        <div id="compare-pw"></div>
        <span id="new-pw2-help"></span>
    </div>
    <div class="margin-top-10">
        <input type="submit" class="btn btn-success" value="Change Password" id="submit-change-pw" />
        <a href="#" class="btn btn-default">Cancel</a>
    </div>
</form>