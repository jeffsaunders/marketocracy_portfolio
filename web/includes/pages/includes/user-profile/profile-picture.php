<?php
/*
Marketocracy Inc. | Beta Development
User Profile/Settings - Change Profile Picture - Include File

Supporting files:
	AJAX			- process/ajax/user-settings-processes.php
	JAVASCRIPT		- JAVASCRIPT includes/scripts/user-settings.js.php
	HTML			- includes/pages/user-profile.php
	PHP Includes	- THIS DOCUMENT includes/pages/includes/user-profile/profile-picture.php
*/

?>

<h3 class="form-section">Change Your Profile Picture <span style="float:right;margin-bottom:10px;display:block;"><a href="/?page=04-00-00-001" class="btn btn-info btn-sm">Return To Public Profile</a></span></h3>
<div id="output"></div>

<div style="float:left;margin-right:10px;">
    <h5>Current Image</h5>
    <span class="replace-current-image">
    <img src="<?php echo $memberImgURL;?>" class="img-responsive" alt="<?php echo $memberName;?>" /> 
    </span>
</div>
<form action="process/ajax/user-settings-processes.php?process=2" method="post" enctype="multipart/form-data" id="MyImageForm">
    <div id="new-image" style="float:left;"></div>
    <div style="clear:both;"></div>
    
    <input type="hidden" id="crop_x" name="x" />
    <input type="hidden" id="crop_y" name="y" />
    <input type="hidden" id="crop_w" name="w" />
    <input type="hidden" id="crop_h" name="h" />
    
    <div style="border-top:1px solid #CCC;margin-top:10px;padding-top:10px;">
        
        <div class="btn-group">
        <input name="image_file" id="imageInput" type="file" class="btn btn-default" style="float:left;"/>
        <input type="submit"  id="submit-btn" value="Upload Image" class="btn btn-info" style="float:left;" />
        </div>
        <img src="images/ajax-loading.gif" id="loading-img" style="display:none;" alt="Please Wait"/>
        <br /><br /><p>Note: Uploading images that contain obscene gestures, nudity, or offensive content is prohibited. Treat this like you would a professional profile.</p>
    </div>
</form>
<br />
<br />

<button type="button" class="btn btn-success" onclick="saveProfilePic();">Save Picture</button>
                                    
                                    