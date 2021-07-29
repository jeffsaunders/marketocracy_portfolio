<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/*
Marketocracy Inc. | Beta Development
User Profile/Settings Processes

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/user-settings-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/user-settings.js.php
	HTML		- includes/pages/user-profile.php
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


switch($process){
	
	case 'deleteAccount':
		
		
		$reason		= $_REQUEST['delete-reason'];
		$confirm	= $_REQUEST['check-delete-account'];
		$memberID	= $_REQUEST['member_id'];
		$aErrors	= array();
		
		if($confirm != true){
			$aErrors[] = 'You must ACCEPT that you understand that you will no longer be able to access this account.';
		}
		if(empty($reason)){
			$aErrors[] = 'You must provide a resason for deleting your account.';
		}
		if($memberID == ''){
			$aErros[] = 'There was an error processing your memberID';	
		}
		
		/*$aErrors['reason'] 		= $reason;
		$aErrors['confirm']		= $confirm;
		$aErrors['memberID']	= $memberID;*/
		
		if(empty($aErrors)){
			
			#Update members table
			$query = "
				UPDATE ".$_SESSION['members_table']."
				SET active='0'
				WHERE member_id=:member_id
			";
			try {
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':member_id'	=> $memberID
				);
				// Prepared query - for error logging and debugging
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdate->execute($aValues);
			}
			catch(PDOException $error){
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			echo '<pre>'.$error;
			
			#update flag
			$query = "
				UPDATE ".$_SESSION['members_flags_table']."
				SET member='0'
				WHERE member_id=:member_id
			";
			try {
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':member_id'	=> $memberID
				);
				// Prepared query - for error logging and debugging
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdate->execute($aValues);
			}
			catch(PDOException $error){
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			#Update subscription table
			$query = "
				UPDATE ".$_SESSION['subscriptions_table']."
				SET active='0', cancel_timestamp=UNIX_TIMESTAMP(), cancel_reason=:reason
				WHERE member_id=:member_id AND active='1'
			";
			try {
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':member_id'	=> $memberID,
					':reason'		=> 'Account Deleted - '.$reason
				);
				// Prepared query - for error logging and debugging
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdate->execute($aValues);
			}
			catch(PDOException $error){
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			echo $error;
			
			#Update auth table table
			$query = "
				UPDATE ".$_SESSION['auth_table']."
				SET active='0', password='x'
				WHERE member_id=:member_id AND active='1'
			";
			try {
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':member_id'	=> $memberID
				);
				// Prepared query - for error logging and debugging
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdate->execute($aValues);
			}
			catch(PDOException $error){
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			echo $error.'</pre>';
			
		}else{
		
			echo '<div class="alert alert-danger"><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';	
			}
			echo '</ul></div>';
		}
		
	break;
	
	case 'sort-funds':
		
		$aOrders = $_POST;
		
		foreach($aOrders as $memberID=>$aOrder){
			
			foreach($aOrder as $key=>$fundSeq){
				
				$fundID = $memberID.'-'.$fundSeq;
				
				$query = "
					UPDATE ".$_SESSION['fund_table']."
					SET weight=:weight
					WHERE fund_id=:fund_id
				";
				try {
					$rsUpdate = $mLink->prepare($query);
					$aValues = array(
						':fund_id'	=> $fundID,
						':weight'	=> $key
					);
					// Prepared query - for error logging and debugging
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsUpdate->execute($aValues);
				}
				catch(PDOException $error){
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				//echo $preparedQuery;
					
			}
			
		}
		
		echo '<div class="alert alert-success"><h4>Fund Order Updated</h4><p>You must refresh the page in order to see changes in the navigation.</p></div>';
		
	break;
		
}

//+---------------------------------------------------------------------------------------------------------+
//|								SAVE EMAIL SETTINGS 														|
//+---------------------------------------------------------------------------------------------------------+
if($process == 'email-settings'){
	
	$settings = $_POST['checkbox'];

	$settings = implode('|', $settings);
	
	if (isset($_SESSION['member_id']) == true){
		
		$query = "
			UPDATE ".$_SESSION['members_settings_table']." 
			SET	ignore_emails=:ignore_emails,
				timestamp=UNIX_TIMESTAMP()
			WHERE member_id=:member_id
		";
		try {
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $_SESSION['member_id'],
				':ignore_emails'	=> $settings
			);
			// Prepared query - for error logging and debugging
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		catch(PDOException $error){
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}
	
	echo $settings;
}

//+---------------------------------------------------------------------------------------------------------+
//|								SAVE NOTIFICATION SETTINGS - PROCESS 1										|
//+---------------------------------------------------------------------------------------------------------+
if($process == '1'){
	
	$settings = $_POST['checkbox'];

	$settings = implode('|', $settings);
	
	if (isset($_SESSION['member_id']) == true){
		
		$query = "
			UPDATE ".$_SESSION['members_settings_table']." 
			SET	ignore_notifications=:ignore_notifications,
				timestamp=UNIX_TIMESTAMP()
			WHERE member_id=:member_id
		";
		try {
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $_SESSION['member_id'],
				':ignore_notifications'	=> $settings
			);
			// Prepared query - for error logging and debugging
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		catch(PDOException $error){
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}
	
	echo $settings;
}

//+---------------------------------------------------------------------------------------------------------+
//|										IMAGE UPLOAD - PROCESS 2											|
//+---------------------------------------------------------------------------------------------------------+
if($process == '2'){
	
	$userPrefix = $_SESSION['member_id'];
	
	############ Configuration ##############
	$thumb_square_size 		= 40; //Thumbnails will be cropped to 200x200 pixels
	$max_image_size 		= 700; //Maximum image size (height and width)
	$thumb_prefix			= "thumb_"; //Normal thumb Prefix
	$destination_folder		= '../../images/profile/'; //upload directory ends with / (slash)
	$image_foler_path		= 'images/profile/';
	$orig_img_folder		= '../../images/profile/orig/';
	$jpeg_quality 			= 90; //jpeg quality
	##########################################
	
	#####  This function will proportionally resize image ##### 
	function normal_resize_image($source, $destination, $image_type, $max_size, $image_width, $image_height, $quality){
		
		if($image_width <= 0 || $image_height <= 0){return false;} //return false if nothing to resize
		
		//do not resize if image is smaller than max size
		if($image_width <= $max_size && $image_height <= $max_size){
			if(save_image($source, $destination, $image_type, $quality)){
				return true;
			}
		}
		
		//Construct a proportional size of new image
		$image_scale	= min($max_size/$image_width, $max_size/$image_height);
		$new_width		= ceil($image_scale * $image_width);
		$new_height		= ceil($image_scale * $image_height);
		
		$new_canvas		= imagecreatetruecolor( $new_width, $new_height ); //Create a new true color image
		
		//Copy and resize part of an image with resampling
		if(imagecopyresampled($new_canvas, $source, 0, 0, 0, 0, $new_width, $new_height, $image_width, $image_height)){
			save_image($new_canvas, $destination, $image_type, $quality); //save resized image
		}
	
		return true;
	}
	
	##### This function corps image to create exact square, no matter what its original size! ######
	function crop_image_square($source, $destination, $image_type, $square_size, $image_width, $image_height, $quality){
		if($image_width <= 0 || $image_height <= 0){return false;} //return false if nothing to resize
		
		if( $image_width > $image_height )
		{
			$y_offset = 0;
			$x_offset = ($image_width - $image_height) / 2;
			$s_size 	= $image_width - ($x_offset * 2);
		}else{
			$x_offset = 0;
			$y_offset = ($image_height - $image_width) / 2;
			$s_size = $image_height - ($y_offset * 2);
		}
		$new_canvas	= imagecreatetruecolor( $square_size, $square_size); //Create a new true color image
		
		//Copy and resize part of an image with resampling
		if(imagecopyresampled($new_canvas, $source, 0, 0, $x_offset, $y_offset, $square_size, $square_size, $s_size, $s_size)){
			save_image($new_canvas, $destination, $image_type, $quality);
		}
	
		return true;
	}
	
	##### Saves image resource to file ##### 
	function save_image($source, $destination, $image_type, $quality){
		switch(strtolower($image_type)){//determine mime type
			case 'image/png': 
				imagepng($source, $destination); return true; //save png file
				break;
			case 'image/gif': 
				imagegif($source, $destination); return true; //save gif file
				break;          
			case 'image/jpeg': case 'image/pjpeg': 
				imagejpeg($source, $destination, $quality); return true; //save jpeg file
				break;
			default: return false;
		}
	}
	
	//continue only if $_POST is set and it is a Ajax request
	if(isset($_POST) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
	
		// check $_FILES['ImageFile'] not empty
		if(!isset($_FILES['image_file']) || !is_uploaded_file($_FILES['image_file']['tmp_name'])){
				die('Image file is Missing!'); // output error when above checks fail.
		}
		
		//uploaded file info we need to proceed
		$image_name = $_FILES['image_file']['name']; //file name
		$image_size = $_FILES['image_file']['size']; //file size
		$image_temp = $_FILES['image_file']['tmp_name']; //file temp
	
		$image_size_info 	= getimagesize($image_temp); //get image size
		
		if($image_size_info){
			$image_width 		= $image_size_info[0]; //image width
			$image_height 		= $image_size_info[1]; //image height
			$image_type 		= $image_size_info['mime']; //image type
		}else{
			die("Make sure image file is valid!");
		}
	
		//switch statement below checks allowed image type 
		//as well as creates new image from given file 
		switch($image_type){
			case 'image/png':
				$image_res =  imagecreatefrompng($image_temp); break;
			case 'image/gif':
				$image_res =  imagecreatefromgif($image_temp); break;			
			case 'image/jpeg': case 'image/pjpeg':
				$image_res = imagecreatefromjpeg($image_temp); break;
			default:
				$image_res = false;
		}
	
		if($image_res){
			//Get file extension and name to construct new file name 
			$image_info = pathinfo($image_name);
			$image_extension = strtolower($image_info["extension"]); //image extension
			$image_name_only = strtolower($image_info["filename"]);//file name only, no extension
			
			//create a random name for new image (Eg: fileName_293749.jpg) ;
			//$new_file_name = $image_name_only. '_' .  rand(0, 9999999999) . '.' . $image_extension;
			$new_file_name = $userPrefix. '_' .  mktime() . '.' . $image_extension;
			
			//folder path to save resized images and thumbnails
			$thumb_save_folder 		= $destination_folder . $thumb_prefix . $new_file_name; 
			$image_save_folder 		= $destination_folder . $new_file_name;
			$image_save_crop 		= $destination_folder . 'crop_'.$new_file_name;
			$orig_image_save_folder	= $orig_img_folder . $new_file_name;
			
			//save original photo
			save_image($image_res, $orig_image_save_folder, $image_type, 100);
			
			//call normal_resize_image() function to proportionally resize image
			if(normal_resize_image($image_res, $image_save_crop, $image_type, $max_image_size, $image_width, $image_height, $jpeg_quality))
			{
				//call crop_image_square() function to create square thumbnails
				if(!crop_image_square($image_res, $thumb_save_folder, $image_type, $thumb_square_size, $image_width, $image_height, $jpeg_quality))
				{
					die('Error Creating thumbnail');
				}
				
				if(!crop_image_square($image_res, $image_save_folder, $image_type, 300, $image_width, $image_height, $jpeg_quality))
				{
					die('Error Creating thumbnail');
				}
				
				/* We have succesfully resized and created thumbnail image
				We can now output image to user's browser or store information in the database*/
				
				?>
				
				<h5>Crop Image</h5>
				<div style="float:left;">
					<img src="<?php echo $image_foler_path.'crop_'. $new_file_name;?>?<?php echo mktime();?>" alt="Resized Image" id="demo8">
				</div>
                
                <div style="float:left;margin-left:10px;" id="crop-btn">
					<button type="button" class="btn btn-info" onclick="cropImage();">Crop Image</button>
                </div>
                
                <input type="hidden" value="<?php echo $image_foler_path . $new_file_name;?>" name="save_user_image" />
				<input type="hidden" value="<?php echo $image_foler_path . $thumb_prefix .$new_file_name;?>" name="save_user_image_thumb" />
				
				<input type="hidden" value="<?php echo $image_foler_path . 'crop_'.$new_file_name;?>" name="user_image" />
				<input type="hidden" value="<?php echo $new_file_name;?>" name="image_file_name" />
				<input type="hidden" value="<?php echo $image_type;?>" name="image_type" />
                <?php
			}
			
			imagedestroy($image_res); //freeup memory
			
			$event = 'Profile Update - Photo';
			$detail = 'Member has uploaded a photo.';
			addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
		}
	}
}

//+---------------------------------------------------------------------------------------------------------+
//|									SAVE PROFILE PICTURE - PROCESS 3										|
//+---------------------------------------------------------------------------------------------------------+
if($process == '3'){
	
	$imagePath = $_REQUEST['save_user_image'];
	$imagePathThumb = $_REQUEST['save_user_image_thumb'];
	$removeImagePath = $_REQUEST['user_image'];
	
	if(!empty($imagePath) && !empty($imagePathThumb)){
	
		$query = "
			UPDATE ".$_SESSION['members_profile_table']." 
			SET	profile_image=:profile_image, profile_image_tb=:profile_image_tb, timestamp=UNIX_TIMESTAMP()
			WHERE member_id=:member_id
		";
		try {
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':member_id'		=> $_SESSION['member_id'],
				':profile_image'	=> $imagePath,
				':profile_image_tb'	=> $imagePathThumb
			);
			// Prepared query - for error logging and debugging
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		catch(PDOException $error){
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		unlink('../../'.$removeImagePath);
		
		$event = 'Profile Update - Photo';
		$detail = 'Member has successfully changed their profile picture.';
		addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
	}
	
	echo $imagePath;
}

//+---------------------------------------------------------------------------------------------------------+
//|										CROP IMAGE - PROCESS 4												|
//+---------------------------------------------------------------------------------------------------------+
if($process == '4'){
	
	$username = $_SESSION['username'];
	
	############ Configuration ##############
	$thumb_square_size 		= 40; //Thumbnails will be cropped to 200x200 pixels
	$max_image_size 		= 300; //Maximum image size (height and width)
	$thumb_prefix			= "thumb_"; //Normal thumb Prefix
	$destination_folder		= '../../images/profile/'; //upload directory ends with / (slash)
	$image_foler_path		= 'images/profile/';
	$jpeg_quality 			= 90; //jpeg quality
	
	$image_type 			= $_REQUEST['image_type'];
	$fileName				= $_REQUEST['image_file_name'];
	$image_width			= intval($_POST['w']);
	$image_height			= intval($_POST['h']);
	$image 					= 'http://'.$_SESSION['base_url'] . $_REQUEST['user_image'];
	##########################################
	
	
	##### This function corps image to create exact square, no matter what its original size! ######
	function crop_image_square($source, $destination, $image_type, $square_size, $image_width, $image_height, $quality){
		if($image_width <= 0 || $image_height <= 0){return false;} //return false if nothing to resize
		
		$new_canvas	= imagecreatetruecolor( $square_size, $square_size); //Create a new true color image
		
		//Copy and resize part of an image with resampling
		if(imagecopyresampled($new_canvas, $source, 0, 0, intval($_POST['x']), intval($_POST['y']), $square_size, $square_size, intval($_POST['w']), intval($_POST['h']))){
			save_image($new_canvas, $destination, $image_type, $quality);
		}
	
		return true;
	}
	
	##### Saves image resource to file ##### 
	function save_image($source, $destination, $image_type, $quality){
		switch(strtolower($image_type)){//determine mime type
			case 'image/png': 
				imagepng($source, $destination); return true; //save png file
				break;
			case 'image/gif': 
				imagegif($source, $destination); return true; //save gif file
				break;          
			case 'image/jpeg': case 'image/pjpeg': 
				imagejpeg($source, $destination, $quality); return true; //save jpeg file
				break;
			default: return false;
		}
	}
	
	//continue only if $_POST is set and it is a Ajax request
	if(isset($_POST) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
	
		//switch statement below checks allowed image type 
		//as well as creates new image from given file 
		switch($image_type){
			case 'image/png':
				$image_res =  imagecreatefrompng($image); 
				$image_extension = 'png';break;
			case 'image/gif':
				$image_res =  imagecreatefromgif($image);
				$image_extension = 'gif'; break;			
			case 'image/jpeg': case 'image/pjpeg':
				$image_res = imagecreatefromjpeg($image); 
				$image_extension = 'jpg';break;
			default:
				$image_res = false;
		}
	
		if($image_res){
			
			//create a random name for new image (Eg: fileName_293749.jpg) ;
			//$new_file_name = $image_name_only. '_' .  rand(0, 9999999999) . '.' . $image_extension;
			//$new_file_name = $username. '_' .  rand(0, 9999999999) . '_crop.' . $image_extension;
			$new_file_name = $fileName;
			
			//folder path to save resized images and thumbnails
			$thumb_save_folder 	= $destination_folder . $thumb_prefix . $new_file_name; 
			$image_save_folder 	= $destination_folder . $new_file_name;
			
			//call normal_resize_image() function to proportionally resize image
			if(crop_image_square($image_res, $image_save_folder, $image_type, $max_image_size, $image_width, $image_height, $jpeg_quality))
			{
				//call crop_image_square() function to create square thumbnails
				if(!crop_image_square($image_res, $thumb_save_folder, $image_type, $thumb_square_size, $image_width, $image_height, $jpeg_quality))
				{
					die('Error Creating thumbnail');
				}
				
				/* We have succesfully resized and created thumbnail image
				We can now output image to user's browser or store information in the database*/
				?>
				<h5>New Image</h5>
				<div style="float:left;margin:0px 10px 0px 0px;">
                    <img src="<?php echo $image_foler_path . $new_file_name;?>?<?php echo mktime();?>" alt="Resized Image"><br />
                    <small>250x250</small>
                </div>
                <div style="float:left;margin:0px 10px 0px 0px;">
					<img src="<?php echo $image_foler_path . $thumb_prefix . $new_file_name;?>?<?php echo mktime();?>" alt="Thumbnail"><br />
                    <small>40x40</small>
                </div>
				
				<input type="hidden" value="<?php echo $image_foler_path . $new_file_name;?>" name="save_user_image" id="save_user_image" />
				<input type="hidden" value="<?php echo $image_foler_path . $thumb_prefix . $new_file_name;?>" name="save_user_image_thumb" id="save_user_image_thumb" />
                
                <input type="hidden" value="<?php echo $_REQUEST['user_image'];?>" name="user_image" id="user_image" />
                <input type="hidden" value="<?php echo $new_file_name;?>" name="image_file_name" id="image_file_name" />
				<input type="hidden" value="<?php echo $image_type;?>" name="image_type" id="image_type" />
				
				<button type="button" class="btn btn-info" onclick="redoCropImage();" style="margin:0px 0px 0px 0px;">Redo Crop</button>
                
                <?php
			}
			
			imagedestroy($image_res); //freeup memory
		}
	}
}

//+---------------------------------------------------------------------------------------------------------+
//|									Save Basic Settings - PROCESS 5											|
//+---------------------------------------------------------------------------------------------------------+
if($process == '5'){
	
	$fundID 		= $_REQUEST['fund_id'];
	$fundType		= $_REQUEST['fund_type'];
	$fundSector		= $_REQUEST['fund_sector'];
	$fundSymbol		= $_REQUEST['fund_symbol'];
	$oldFundSymbol	= $_REQUEST['old_fund_symbol'];
	$fundDesc		= $_REQUEST['fund_desc'];
	$fundColor		= $_REQUEST['fund_color'];
	$fundName		= $_REQUEST['fund_name'];
	$oldFundName	= $_REQUEST['old_fund_name'];

    if($fundColor == ''){
        $fundColor = '#39B3D7';
    }
	
	
	
	$aFundSymbols	= get_fund_symbols($mLink, $_SESSION['member_id']);
	
	$aErrors = array();
	
	switch($fundType){
		
		case 'standard':
			$fundSector = NULL;
		break;
		
		case 'sector':
			if($fundSector == 'xx'){
				$aErrors[] = 'Please select a Sector for your fund.';	
			}
		break;
			
	}
	
	if(empty($fundName)){
		$aErrors[] = 'Please provide a fund name. This field can not be blank.';	
	}
	
	/*if($fundSymbol != $oldFundSymbol){
		$aErrors[] = 'You can not change your fund symbol at this time.';
		
		if(in_array($fundSymbol, $aFundSymbols)){
			$aErrors[] = 'Fund Symbol already in use, please choose another Fund Symbol.';	
		}
		
		if (preg_match('/[^a-zA-Z\-.0-9]+/', $fundSymbol)){
		  $aErrors[] = 'Fund Symbol can only contain Letters and Numbers. The following symbols are allowed: -(dash), .(period)';
		}
		
		if(strlen($fundSymbol) > 8){
			$aErrors[] = 'Fund Symbol can not exceed 8 characters.';	
		}
	}*/
	
	if(empty($aErrors)){
		//echo $fundID.' | '.$fundSymbol.' | '.$fundDesc.' | '.$fundColor.' | '.$fundName.' | '.$oldFundSymbol;
		/*$query = '
			UPDATE '.$_SESSION['fund_table'].'
			SET fund_name=:fund_name, description=:description, fund_color=:fund_color, fund_symbol=:fund_symbol
			WHERE fund_id=:fund_id
		';*/
		$query = '
			UPDATE '.$_SESSION['fund_table'].'
			SET fund_name=:fund_name, description=:description, fund_color=:fund_color, fund_type=:fund_type, fund_sector=:fund_sector
			WHERE fund_id=:fund_id
		';
		
		try{
			$rsUpdateFund = $mLink->prepare($query);
			$aValues = array(
				':fund_name' 	=> $fundName,
				':fund_type'	=> $fundType,
				':fund_sector'	=> $fundSector,
				':description'	=> $fundDesc,
				':fund_id'		=> $fundID,
				':fund_color'	=> $fundColor
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdateFund->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$query = '
			UPDATE '.$_SESSION['fund_settings_table'].'
			SET fund_color=:fund_color
			WHERE fund_id=:fund_id
		';
		try{
			$rsUpdateFund = $mLink->prepare($query);
			$aValues = array(
				':fund_id'		=> $fundID,
				':fund_color'	=> $fundColor
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdateFund->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		
		if($fundSymbol != $oldFundSymbol){
			
			/*$query = 'updateSymbol|'.$_SESSION['username'].'|'.$fundID.'|'.$oldFundSymbol.'|'.$fundSymbol.'';
			include('../../includes/data-query-legacy.php');
			
			$event = 'Legacy Update';
			$detail = 'Member has updated their fund symbol.';
			addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);*/
			
			/*$aMethodVars[] = array(
				'method'			=> 'updateSymbol', #method name	
				'source'			=> 'User Settings Script | user-settings-processes.php | process: 5 | Line: '.__LINE__, #File and Code location of this instance	
				'api'				=> '1', #api switch, 1 = api1, 2 = api2
				'username'			=> $_SESSION['username'], #username of member 
				'fund_symbol'		=> $oldFundSymbol, #old fund symbol 
				'fund_id' 			=> $fundID, #members fund ID 
				'new_fund_symbol'	=> $fundSymbol #new fund symbol	
			);
			$mlaResults = legacy_api($mLink, $aMethodVars, true);*/
			
			//echo '~'.$query;
		}
		if($fundName != $oldFundName){
			
			/*$query = 'updateName|'.$_SESSION['username'].'|'.$fundID.'|'.$oldFundSymbol.'|'.$fundName.'';
			//echo '~'.$query;
			
			$event = 'Legacy Update';
			$detail = 'Member has updated their fund name.';
			addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);*/
			
			$aMethodVars[] = array(
				'method'		=> 'updateName', #method name
				'source'		=> 'User Settings Script | user-settings-processes.php | process: 5 | Line: '.__LINE__, #File and Code location of this instance
				'api'			=> '1', #api switch, 1 = api1, 2 = api2
				'fund_id'		=> $fundID,
				'username' 		=> $_SESSION['username'], #username of member 'fund_id' => '2-8', #members fund ID
				'fund_symbol'	=> $oldFundSymbol, #old fund symbol
				'new_fund_name'	=> $fundName #new fund name
			);
			$mlaResults = legacy_api($mLink, $aMethodVars, true);
		}
		
		//echo '~~~~~~'.$error;
		sleep(1);
	}else{
		
		echo '<div class="note note-danger"><ul>';
		foreach($aErrors as $key=>$error){
			echo '<li>'.$error.'</li>';
		}
		echo '</ul></div>';
		
	}
}

//+---------------------------------------------------------------------------------------------------------+
//|									Create New Fund - PROCESS 5-2												|
//+---------------------------------------------------------------------------------------------------------+
if($process == '5-2'){
	
	
	
	$fundSymbol		= strtoupper($_REQUEST['fund_symbol']);
	$fundType		= $_REQUEST['fund_type'];
	$fundDesc		= $_REQUEST['fund_desc'];
	$fundColor		= $_REQUEST['fund_color'];
	$fundName		= $_REQUEST['fund_name'];
	$memberObj		=  new member($dbPortfolio, $dbFeed, $_SESSION['member_id']);

    if($fundColor == ''){
        $fundColor = '#39B3D7';
    }

	//$aFundSymbols	= get_fund_symbols($mLink, $_SESSION['member_id'], 'symbols', false);
	$aFundSymbols = $memberObj->getFundSymbols();
	//print_r($aFundSymbols);
	$aErrors = array();
	
	//$aErrors[] = 'test';
	
	if(strlen($fundSymbol) > 6){
		$aErrors[] = 'Fund symbol can only be 5 characters long.';	
	}
	
    if($fundSymbol == ''){
        $aErrors[] = 'Please provide a fund symbol.';
    }

    if($fundName == ''){
        $aErrors[] = 'Please provide a fund name.';
    }

	if(in_array($fundSymbol, $aFundSymbols)){
		$aErrors[] = 'Fund Symbol is already in use or has been used in the past, please choose another Fund Symbol.';	
	}
	
	if (preg_match('/[^a-zA-Z\-.0-9]+/', $fundSymbol)){
	  $aErrors[] = 'Fund Symbol can only contain Letters and Numbers. The following symbols are allowed: -(dash), .(period)';
	}
	
	if(strlen($fundSymbol) > 8){
		$aErrors[] = 'Fund Symbol can not exceed 8 characters.';	
	}
	
	$fundID = get_new_fund_id($mLink, $_SESSION['member_id']);
	
	//echo $fundID;
	
	$aFundID = explode('-', $fundID);
	
	$seqID = $aFundID[1];
	
	$col1 = 'fund-price-history~0~0~0|fund-pos-sectors~0~0~0|fund-info~0~0~0|fund-turnover~0~0~0|fund-best-worst~0~0~0';
	$col2 = 'fund-returns-index~0~0~0|fund-ratios~0~0~0|fund-pos-style~0~0~0|fund-recent-returns~0~0~0|fund-alpha-beta~0~0~0|fund-profit~0~0~0';
	
	//$aErrors[] = $fundID;
	
	if(empty($aErrors)){
		
		switch($fundType){
			case 'standard':
				$active 			= 1;
				$createLivePrice	= true;
				$createFundSettings	= true;
				$createLegacy		= true;
				$createFoF			= false;
			break;
			
			case 'sector':
				$active 			= 1;
				$createLivePrice	= true;
				$createFundSettings	= true;
				$createLegacy		= true;
				$createFoF			= false;
			break;
			
			case 'fof':
				$active 			= 0;
				$createLivePrice	= false;
				$createFundSettings	= false;
				$createLegacy		= false;
				$createFoF			= true;
			break;
		}
		
		//Create new fund record
		$query = "
			INSERT INTO ".$_SESSION['fund_table']." (
				fund_id,
				fund_type,
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
				:fund_type,
				:seq_id,
				:member_id,
				UNIX_TIMESTAMP(),
				:date,
				UNIX_TIMESTAMP(),
				:fund_name,
				:fund_symbol,
				:description,
				'0',
				:active,
				'1',
				:fund_color
			)
			
		";
		try{
			$rsUpdateFund = $mLink->prepare($query);
			$aValues = array(
				':fund_id'		=> $fundID,
				':fund_type'	=> $fundType,
				':seq_id'		=> $seqID,
				':member_id'	=> $_SESSION['member_id'],
				':date'			=> date('Ymd'),
				':fund_name'	=> $fundName,
				':fund_symbol'	=> $fundSymbol,
				':description'	=> $fundDesc,
				':active'		=> $active,
				':fund_color'	=> $fundColor
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdateFund->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$aErrors['Insert-Fund-Error'] = $error;
		
		if($createLivePrice == true){
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
			
			$aErrors['Insert-LivePrice-Error'] = $error;
		}//end create live price
		
		if($createFundSettings	== true){
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
	
			$aErrors['Insert-Fund-Settings-Error'] = $error;
	
			/*$query = 'newFund|'.$_SESSION['username'].'|long|'.$fundName.'|'.$fundSymbol.'|'.$fundID.'';
			include('../../includes/data-query-legacy.php');*/
		}//end create fund settings
		
		if($createLegacy == true){
			$aMethodVars[] = array(
				'method'		=> 'newFund', #method name 
				'source'		=> 'User Settings | user-settings-processes.php | process: 5-2', #File and Code location of this instance 
				'api'			=> '1', #api switch, 1 = api1, 2 = api2 
				'username'		=> $_SESSION['username'], #username of member
				'fund_type'		=> 'long',
				'fund_name'		=> $fundName, 
				'fund_symbol'	=> $fundSymbol, #fund symbol 
				'fund_id' 		=> $fundID, #members fund ID 'group'	=> 'N/A' #use this field if there are multiple queries being placed at the same time (IE Fund Price)
			); 
			$mlaResults = legacy_api($mLink, $aMethodVars, true);
		
		$aErrors['MLA-Results'] = $mlaResults;
		}//end create legacy
        
		if($createFoF == true){
			
			$query = "
				INSERT INTO fof_funds (
					fund_id,
					active,
					fund_type,
					fund_symbol,
					seq_id,
					weight,
					member_id,
					unix_date,
					inception_datetime,
					fund_name,
					description,
					fund_color
				) VALUE (
					:fund_id,
					'1',
					:fund_type,
					:fund_symbol,
					:seq_id,
					:seq_id,
					:member_id,
					UNIX_TIMESTAMP(),
					CURRENT_TIMESTAMP(),
					:fund_name,
					:description,
					:fund_color
				)
				
			";
			try{
				$rsInsertFund = $mLink->prepare($query);
				$aValues = array(
					':fund_id'		=> $fundID,
					':fund_type'	=> $fundType,
					':fund_symbol'	=> $fundSymbol,
					':seq_id'		=> $seqID,
					':member_id'	=> $_SESSION['member_id'],
					':date'			=> date('Ymd'),
					':fund_name'	=> $fundName,
					':description'	=> $fundDesc,
					':fund_color'	=> $fundColor
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsInsertFund->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$aErrors['Insert-Fund-Error'] = $error;
			
		}//end create fund of funds
		
		if($_SESSION['member_id'] == '2'){
			echo '<div class="note note-danger"><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';	
			}
			echo '</ul></div>';
		}
		echo 'FUND:'.$fundID;

	}else{
		
		echo '<div class="note note-danger"><ul>';
		foreach($aErrors as $key=>$error){
			echo '<li>'.$error.'</li>';
		}
		echo '</ul></div>';
		
	}

    sleep(1);
	
}

//+---------------------------------------------------------------------------------------------------------+
//|									DELETE Fund - PROCESS 5-3												|
//+---------------------------------------------------------------------------------------------------------+
if($process == '5-3'){

    $fundID = $_REQUEST['fund'];

    $aErrors = array();
	
	$fundSymbol = get_funds($mLink, $fundID, 'fundSymbol');
	
    if($fundID == ''){
        $aErrors[] = "No fund ID passed. Please try again or submit a support ticket.";
    }

    if(empty($aErrors)){

        $query = "
            UPDATE ".$_SESSION['fund_table']."
            SET active='0'
            WHERE fund_id=:fund_id
        ";
        try{
            $rsUpdateFund = $mLink->prepare($query);
            $aValues = array(
                ':fund_id'		=> $fundID
            );
            $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
            $rsUpdateFund->execute($aValues);
        }
        catch(PDOException $error){
            // Log any error
            file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
        }

        if(!empty($error)){
            echo $error;
        }
		
		//tell the old system that the fund has been deactivated
		/*$query = 'deactivateFund|'.$_SESSION['username'].'|'.$fundSymbol.'|'.$fundID;
		include('../../includes/data-query-legacy.php');*/
		
		$aMethodVars[] = array(
			'method'		=> 'deactivateFund', #method name 
			'source'		=> 'User Settings Script | user-settings-processes.php | process: 5-3 | Line: '.__LINE__, #File and Code location of this instance 
			'api'			=> '1', #api switch, 1 = api1, 2 = api2 
			'username'		=> $_SESSION['username'], #username of member 
			'fund_symbol'	=> $fundSymbol, #fund symbol 
			'fund_id' 		=> $fundID #members fund ID 
		); 
		$mlaResults = legacy_api($mLink, $aMethodVars, true);
		
		/*$event = 'DELETE FUND';
        $detail = 'Member has deleted a fund: '.$_SESSION['username'].':'.$fundSymbol.'. | '.$query;
        addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);*/
		
    }else{
        echo '<ul>';
        foreach($aErrors as $key=>$error){
            echo '<li>'.$error.'</li>';
        }
        echo '</ul>';
    }

}

//+---------------------------------------------------------------------------------------------------------+
//|										Load Group - PROCESS 6												|
//+---------------------------------------------------------------------------------------------------------+
if($process == '6'){

	$groupID = $_REQUEST['group'];
	
	$query = "
		SELECT *
		FROM ".$_SESSION['connections_group_table']."
		WHERE group_owner=:member_id AND group_id=:group_id AND active='1'
	";
	try{
		$rsGetGroups = $mLink->prepare($query);
		$aValues = array(
			':member_id'	=> $_SESSION['member_id'],
			':group_id'		=> $groupID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetGroups->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$cnt = 0;
	
	$group = $rsGetGroups->fetch(PDO::FETCH_ASSOC);
		
	$groupName 					= $group['group_name'];
	$aGroupMembers[$groupID]	= explode('|',$group['members']);
		
	
	?>
    <div class="row">
    	<div class="col-md-10">
        	<h4><?php echo $groupName;?> </h4>
        </div>
        <div class="col-md-2" style="text-align:right;">
        	<button type="button" class="btn btn-xs btn-danger" onclick="deleteGroup('<?php echo $groupID;?>', '<?php echo $groupName;?>');">Delete Group</button>
        </div>
    
    </div><!--row-->
    <hr style="margin:0px 0px 10px 0px;" />
    <form action="" method="post" class="form-horizontal" name="update-group" id="update-group">
        
        <div class="form-group" >
            <div class="col-md-10">
                <select name="select_connections[]" class="multi-select" multiple="" id="select_connections" >
                    <?php
                    
                    $query = "
                        SELECT *
                        FROM ".$_SESSION['connections_table']."
                        WHERE member_id=:member_id AND status='active'
                    ";
                    
                    try{
                        $rsConnect = $mLink->prepare($query);
                        $aValues = array(
                            ':member_id'	=> $_SESSION['member_id']
                        );
                        $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                        $rsConnect->execute($aValues);
                    }
                    catch(PDOException $error){
                        // Log any error
                        file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                    }
                    
                    while($connect = $rsConnect->fetch(PDO::FETCH_ASSOC)) {
                        
                        $fullName = get_member($mLink, $connect['connection'], 'full name');
                        $username = get_member($mLink, $connect['connection'], 'username');
                        
                        $connectMemberID = $connect['connection'];
                        
                        if(in_array($connectMemberID, $aGroupMembers[$groupID])){
                            echo '<option value="'.$connectMemberID.'" selected>'.$fullName.' ('.$username.')</option>';
                        }else{
                            echo '<option value="'.$connectMemberID.'">'.$fullName.' ('.$username.')</option>';
                        }
                        
                    }																						
                    ?>
                </select>
                <span class="help-block">Move connections to the right box to add to group. Simply click on the connection's name to move into or out of the group.</span>
                
                <input type="hidden" name="group_id" value="<?php echo $groupID;?>" />
                <input type="hidden" name="group_name" value="<?php echo $groupName;?>"  />
                
            </div><!--col-md-10-->
    	</div><!--form-group-->
    </form>
    <?php
	
}

//+---------------------------------------------------------------------------------------------------------+
//|										Save Group Settings - PROCESS 7										|
//+---------------------------------------------------------------------------------------------------------+
if($process == '7'){
	
	$groupID 			= $_REQUEST['group_id'];
	$groupConnections	= implode('|',$_REQUEST['select_connections']);
	$groupName			= $_REQUEST['group_name'];
	
	
	$query = '
		UPDATE '.$_SESSION['connections_group_table'].'
		SET members=:members
		WHERE group_id=:group_id
	';
	try{
		$rsUpdateGroup = $mLink->prepare($query);
		$aValues = array(
			':group_id'	=> $groupID,
			':members'	=> $groupConnections
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsUpdateGroup->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$event = 'Account Update';
	$detail = 'Member has updated their group settings for group: '.$groupName.'('.$groupID.').';
	addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
}

//+---------------------------------------------------------------------------------------------------------+
//|										Create New Group - PROCESS 8										|
//+---------------------------------------------------------------------------------------------------------+
if($process == '8'){
	
	$aGroups = $_SESSION['my_groups'];
	$groupName = $_REQUEST['group_name'];
	
	if($groupName != ''){
	
		$query = "
			INSERT INTO ".$_SESSION['connections_group_table']." (
				group_name,
				group_owner,
				active,
				timestamp
			) VALUE (
				:group_name,
				:group_owner,
				'1',
				UNIX_TIMESTAMP()
			)
		";
		try{
			$rsCreateGroup = $mLink->prepare($query);
			$aValues = array(
				':group_name'	=> $groupName,
				':group_owner'	=> $_SESSION['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsCreateGroup->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$groupID = $mLink->lastInsertId();
		
		$aGroups[$groupID] = array(
			'group_name'	=> $groupName,
			'group_members'	=> ''
		); 
		
		$_SESSION['my_groups'] = $aGroups;
	}
	
	$cnt = 0;
	
	foreach($aGroups as $groupID=>$aGroup){
                                            
		$groupName 	= $aGroup['group_name'];
		
		$cnt++;
		
		?>
		<tr>
			<td><?php echo $cnt;?></td>
			<td><?php echo $groupName;?></td>
			<td><button type="button" class="btn btn-xs btn-info" onclick="loadGroup('<?php echo $groupID;?>');">Manage</button></td>
		</tr>
		<?php	
	}
	
	
	?>
    
	<tr>
		
		<td><?php $cnt++; echo $cnt;?></td>
		<td>
			<input type="text" class="form-control" placeholder="Enter new group name and press enter." name="group_name" id="group_name">
		</td>
		<td><button type="submit" style="margin-top:5px;" type="button" class="btn btn-xs btn-default">Create Group</button></td>
		
	</tr>
    <?php
	
}


//+---------------------------------------------------------------------------------------------------------+
//|										Reload Group - PROCESS 9											|
//+---------------------------------------------------------------------------------------------------------+
if($process == '9'){
	?>
    <select name="select_groups[]" class="multi-select" multiple="" id="select_groups" >
        <option value="my_connections">My Connections</option>
        <?php
		$aGroups = $_SESSION['my_groups'];
		
        foreach($aGroups as $groupID=>$aGroup){
            echo '<option value="'.$groupID.'">'.$aGroup['group_name'].'</option>';
        }
        ?>
    </select>
	<?php
}

//+---------------------------------------------------------------------------------------------------------+
//|										Delete Group - PROCESS 10											|
//+---------------------------------------------------------------------------------------------------------+
if($process == '10'){
	
	//Get vars
	$groupID = $_REQUEST['group'];
	$aGroups = $_SESSION['my_groups'];
	
	//Remove Group Id from Group Array
	unset($aGroups[$groupID]);
	
	//Reset the my groups session variable 
	$_SESSION['my_groups'] = $aGroups;
	
	//START | update group table
	$query = "
		UPDATE ".$_SESSION['connections_group_table']."
		SET active='0'
		WHERE group_id=:group_id
	";
	try{
		$rsCreateGroup = $mLink->prepare($query);
		$aValues = array(
			':group_id'	=> $groupID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsCreateGroup->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	//END | Update group table
	
	//START | Reload My Groups Table
	$cnt = 0; //set counter
	
	//loop through the my groups array to display to screen
	foreach($aGroups as $groupID=>$aGroup){
                                            
		$groupName 	= $aGroup['group_name'];
		
		$cnt++;
		
		?>
		<tr>
			<td><?php echo $cnt;?></td>
			<td><?php echo $groupName;?></td>
			<td><button type="button" class="btn btn-xs btn-info" onclick="loadGroup('<?php echo $groupID;?>');">Manage</button></td>
		</tr>
		<?php	
	}
	?>
	<tr>
		
		<td><?php $cnt++; echo $cnt;?></td>
		<td>
			<input type="text" class="form-control" placeholder="Enter new group name and press enter." name="group_name" id="group_name">
		</td>
		<td><button type="submit" style="margin-top:5px;" type="button" class="btn btn-xs btn-default">Create Group</button></td>
		
	</tr>
    <?php
	//END | Reload My groups Table 
}

//+---------------------------------------------------------------------------------------------------------+
//|									Save Advanced Settings - PROCESS 11										|
//+---------------------------------------------------------------------------------------------------------+
if($process == '11'){
	
	$private		= $_REQUEST['private'];
	$selectGroups	= implode('|',$_REQUEST['select_groups']);
	$selectConnects	= implode('|',$_REQUEST['select_connects']);
	$fundID			= $_REQUEST['fund_id'];
	
	if($private != ''){
		$query = "
			UPDATE ".$_SESSION['fund_settings_table']."
			SET private='1', allowed_connect_groups=:groups, allowed_connect_members=:connects, timestamp=UNIX_TIMESTAMP()
			WHERE fund_id=:fund_id
		";
		try{
			$rsCreateGroup = $mLink->prepare($query);
			$aValues = array(
				':fund_id'	=> $fundID,
				':groups'	=> $selectGroups,
				':connects'	=> $selectConnects
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsCreateGroup->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$query = "
			UPDATE ".$_SESSION['fund_table']."
			SET private='1', timestamp=UNIX_TIMESTAMP()
			WHERE fund_id=:fund_id
		";
		try{
			$rsCreateGroup = $mLink->prepare($query);
			$aValues = array(
				':fund_id'	=> $fundID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsCreateGroup->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}else{
		$query = "
			UPDATE ".$_SESSION['fund_settings_table']."
			SET private='0', allowed_connect_groups=:groups, allowed_connect_members=:connects, timestamp=UNIX_TIMESTAMP()
			WHERE fund_id=:fund_id
		";
		try{
			$rsCreateGroup = $mLink->prepare($query);
			$aValues = array(
				':fund_id'	=> $fundID,
				':groups'	=> NULL,
				':connects'	=> NULL
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsCreateGroup->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}	
		
		$query = "
			UPDATE ".$_SESSION['fund_table']."
			SET private='0', timestamp=UNIX_TIMESTAMP()
			WHERE fund_id=:fund_id
		";
		try{
			$rsCreateGroup = $mLink->prepare($query);
			$aValues = array(
				':fund_id'	=> $fundID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsCreateGroup->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}	
	}

	echo $fundID.' ~ '.$private.' ~ '.$selectGroups.' ~ '.$selectConnects.' ~ '.$error;
	sleep(1);
	
}

//+---------------------------------------------------------------------------------------------------------+
//|								Accept Connection Invite - PROCESS 12										|
//+---------------------------------------------------------------------------------------------------------+
if($process == '12'){
	
	$uid		= $_REQUEST['uid'];
	$connection	= $_REQUEST['connect'];
	
	echo $uid.' | '.$connection;
	
	//update request record and set to active
	$query = "
		UPDATE ".$_SESSION['connections_table']."
		SET status='active'
		WHERE uid=:uid
	";
	try{
		$rsInvite = $mLink->prepare($query);
		$aValues = array(
			':uid'	=> $uid
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsInvite->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	//create new record to complete the connection
	$query = "
		INSERT INTO ".$_SESSION['connections_table']." (
			member_id,
			connection,
			status,
			timestamp
		) VALUE (
			:member_id,
			:connection,
			'active',
			UNIX_TIMESTAMP()
		)
	";
	
	try{
		$rsInvite2 = $mLink->prepare($query);
		$aValues = array(
			':member_id'	=> $_SESSION['member_id'],
			':connection'	=> $connection
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsInvite2->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	//START NOTIFICATION
	$notificationID = "04-007";
	
	//Custom notification
	$notification	= "".get_member($mLink, $_SESSION['member_id'], "full name")." has accepted your connection invite! Click here to view their profile.";
	$link			= '?page=04-00-00-001&member='.$_SESSION['member_id'].'';
	$report = add_notification($mLink, $notificationID, $connection, $notification, $link);
	
	echo $report;
	//END NOTIFICATION	
	
	//Update Event Log
	$event = 'Connection Invite';
	$detail = 'Member has accepted connection invite.';
	addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
}

//+---------------------------------------------------------------------------------------------------------+
//|									Reload Connections - PROCESS 13											|
//+---------------------------------------------------------------------------------------------------------+
if($process == '13'){
	
	$query = "
		SELECT *
		FROM ".$_SESSION['connections_table']."
		WHERE member_id=:member_id AND status='active' 
	";
	try{
		$rsConnect = $mLink->prepare($query);
		$aValues = array(
			':member_id' 	=> $_SESSION['member_id']
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsConnect->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$cnt = 0;
	
	while($connect = $rsConnect->fetch(PDO::FETCH_ASSOC)){
		
		$cnt++;
				
		$aMember = get_member($mLink, $connect['connection'], 'all');
		
		?>
		<div class="col-md-4 connection">
			<div class="row">
				<div class="col-md-4">
					<a href="?page=04-00-00-001&member=<?php echo $connect['connection'];?>"><img class="img-responsive" alt="" src="<?php echo $aMember['profileImageURL'];?>"></a>
				</div><!--col-md-5-->
				
				<div class="col-md-8">
					<h5><a href="?page=04-00-00-001&member=<?php echo $connect['connection'];?>"><strong><?php echo $aMember['fullName'];?></strong></a></h5>
					<ul class="list-inline" style="margin:0px;padding:0px;">
						<?php if(!empty($aMember['state'])){?>
						<li title="Location" style="padding-left:0px;"><i class="icon-map-marker"></i> <?php echo $aMember['city'];?>, <?php echo $aMember['state'];?></li>
						<?php } ?>
						<li title="Member Since" style="padding-left:0px;"><i class="icon-calendar" ></i> <?php echo date('M d, Y', $aMember['joinDate']);?></li>
					</ul>
				</div><!--col-md-7-->
			</div><!--row--> 
		</div><!--col-md-4-->
		<?php
	}
}

//+---------------------------------------------------------------------------------------------------------+
//|									Reload Invites - PROCESS 14											|
//+---------------------------------------------------------------------------------------------------------+
if($process == '14'){
	$query = "
		SELECT *
		FROM ".$_SESSION['connections_table']."
		WHERE connection=:member_id AND status='pending' 
	";
	try{
		$rsConnect = $mLink->prepare($query);
		$aValues = array(
			':member_id' 	=> $_SESSION['member_id']
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsConnect->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$cnt = 0;
	
	while($connect = $rsConnect->fetch(PDO::FETCH_ASSOC)){
		
		$cnt++;
		
		$aMember = get_member($mLink, $connect['member_id'], 'all');
		
		?>
		<li>
			<div class="rl-col-1">
				<img border="0" width="40" height="40" src="<?php echo $aMember['profileImageURL'];?>">
			</div>
			
			<div class="rl-col-2">
				<strong><?php echo $aMember['fullName'];?></strong> <br>
				<span><?php echo $aMember['occupation'];?></span>
			</div>
			
			<div class="rl-col-3">
				<a class="accordion-toggle btn btn-info btn-sm" data-toggle="collapse" data-parent="#accordion1" href="#collapse_<?php echo $cnt;?>"><i class="icon-comment"></i></a>
				<button type="button" class="btn btn-info btn-sm" onclick="acceptInvite('<?php echo $connect['uid'];?>','<?php echo $connect['member_id'];?>','<?php echo $aMember['fullName'];?>');">Accept</button>
				<button type="button" class="btn btn-sm">Ignore</button>
			</div>
			<div class="clear"></div><!--clear-->
			
			<div class="collapse request-info" id="collapse_<?php echo $cnt;?>">
				<div class="row">
					<div class="col-md-8">
						<?php echo $connect['request_message'];?>
					</div><!--col-md-8-->
					
					<div class="col-md-4" style="text-align:right;">
						<?php echo time_past($connect['timestamp']);?>
					</div><!--col-md-4-->
				</div><!--row-->
			</div><!--collapse-->
				
		</li>
		<?php
		
	}
	
	if($cnt == 0){
		echo '<li>No Invites Pending</li>';	
	}
}

//+---------------------------------------------------------------------------------------------------------+
//|									Save Account Settings - PROCESS 15										|
//+---------------------------------------------------------------------------------------------------------+
if($process == '15'){
	
	$firstName		= $_REQUEST['name_first'];
	$lastName		= $_REQUEST['name_last'];
	$emailOrig		= $_REQUEST['email_orig'];
	$newEmail		= $_REQUEST['email'];
	$confirmEmail	= $_REQUEST['email2'];
	$phoneDay		= $_REQUEST['phone_day'];
	$phoneEvening	= $_REQUEST['phone_evening'];
	$phoneMobile	= $_REQUEST['phone_mobile'];
	
	$error_list = array();
	
	if(empty($firstName)){
		$error_list[] = 'Please provide your First Name.';	
	}
	
	if(empty($lastName)){
		$error_list[] = 'Please provide your Last Name.';	
	}
	
	if(empty($phoneDay)){
		$error_list[] = 'Please provide a phone number.';	
	}
	
	//if no errors continue
	if(empty($error_list)){
		
		//START | Email Validation
		//Check to see if new email address fields are filled in and valid
		if($newEmail != ''){
			//Field in use, check to see if they match
			if($newEmail == $confirmEmail){
				//Email addresses match
				
				if(filter_var($confirmEmail, FILTER_VALIDATE_EMAIL)){
					//Address is considered valid
					
					//include encrypt functions
					include('../../../secure/crypto.php');
					
					//encrypt the new email address
					$encryptEmail = encrypt($confirmEmail);
					
					//set universal var for email to save
					$saveEmail = $confirmEmail;
					
					//Store newly encrypted email in the system_authentication table
					
					//Get current auth values
					$query = "
						SELECT username, password
						FROM ".$_SESSION['auth_table']."
						WHERE member_id=:member_id
						ORDER BY timestamp DESC
						LIMIT 1
					";
					
					try{
						$rsGetAuth = $mLink->prepare($query);
						$aValues = array(
							':member_id' 	=> $_SESSION['member_id']
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsGetAuth->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
					$auth = $rsGetAuth->fetch(PDO::FETCH_ASSOC);
					
					//Create new auth record
					$query = "
						INSERT INTO ".$_SESSION['auth_table']." (
							member_id,
							timestamp,
							username,
							password,
							password_score,
							password_score_ack_timestamp,
							email,
							reset_request_timestamp,
							reset_request_ip,
							email_validated_timestamp,
							imported
						) VALUE (
							:member_id,
							UNIX_TIMESTAMP(),
							:username,
							:password,
							'0',
							'0',
							:email,
							'0',
							'',
							UNIX_TIMESTAMP(),
							'0'
						)		
					";
					
					try{
						$rsInsertAuth = $mLink->prepare($query);
						$aValues = array(
							':member_id' 	=> $_SESSION['member_id'],
							':username'		=> $auth['username'],
							':password'		=> $auth['password'],
							':email'		=> $encryptEmail
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsInsertAuth->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
					//Reset the User Email session so they don't have to logout and back in
					$_SESSION['email'] = $confirmEmail;
					
				}else{
					//Address is not conisdered valid	
					$error_list[] = 'New email addresss is not valid.';
					$saveEmail = $emailOrig;
				}
				
				
			}else{
				//Email adresseses do not match
				$error_list[] = 'Email addresses do not match.';
				$saveEmail = $emailOrig;
			}
		}else{
			$saveEmail = $emailOrig;	
		}
		//END | Email Validation
		
		//Check for any new errors
		if(empty($error_list)){
			//START | UPDATE MEMBERS TABLE
			$query = "
				UPDATE ".$_SESSION['members_table']."
				SET name_first=:name_first, name_last=:name_last, email=:email, phone_day=:phone_day, phone_evening=:phone_evening, phone_mobile=:phone_mobile, timestamp=UNIX_TIMESTAMP()
				WHERE member_id=:member_id
			";
			try{
				$rsInsertAuth = $mLink->prepare($query);
				$aValues = array(
					':member_id' 		=> $_SESSION['member_id'],
					':email'			=> $saveEmail,
					':name_first'		=> $firstName,
					':name_last'		=> $lastName,
					':phone_day'		=> $phoneDay,
					':phone_evening'	=> $phoneEvening,
					':phone_mobile'		=> $phoneMobile
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsInsertAuth->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			//END | UPDATE MEMBERS TABLE
		}else{
			//Print errors
			echo '<ul>';
			
			foreach($error_list as $key=>$errors){
				echo '<li>'.$errors.'</li>';	
			}
			
			echo '</ul>';
				
		}//end error check 2
		
	}else{
		//Print errors
		echo '<ul>';
		
		foreach($error_list as $key=>$errors){
			echo '<li>'.$errors.'</li>';	
		}
		
		echo '</ul>';
				
	}//end error check 1
	
	//echo $firstName.' | '.$lastName.' | '.$emailOrig.' | '.$newEmail.' | '.$confirmEmail.' | '.$phoneDay.' | '.$phoneEvening.' | '.$phoneMobile.' | '.$encryptEmail.' | '.$emailOrig.' | '.$error;
	
	//print_r($error_list);
	
	
	sleep(1);
}

//+---------------------------------------------------------------------------------------------------------+
//|									Save Address Settings - PROCESS 16										|
//+---------------------------------------------------------------------------------------------------------+
if($process == '16'){
	
	$country 	= $_REQUEST['country'];
	$address1	= $_REQUEST['address1'];
	$address2	= $_REQUEST['address2'];
	$city		= $_REQUEST['city'];
	$state		= $_REQUEST['state'];
	$zip		= $_REQUEST['zip'];
	
	$error_list = array();
	
	if(empty($country)){
		$error_list[] = 'Please provide your country.';	
	}
	
	if(empty($address1)){
		$error_list[] = 'Please provide your address.';	
	}
	
	if(empty($city)){
		$error_list[] = 'Please provide your city.';
	}
	
	//Update Vars
	$aCountry = explode('|', $country);
	$countryCode	= $aCountry[0];
	$countryName	= $aCountry[1];
	
	
	if($countryCode == 'US' || $countryCode == 'CA'){
		
		if(empty($state)){
			$error_list[] = 'Please provide your state/province.';	
		}
		
		if(empty($zip)){
			$error_list[] = 'Please provide your zip/postal code.';	
		}
		
		$aState	= explode('|', $state);
		$state	= $aState[0];
			
	}
	
	//If no errors, update members table
	if(empty($error_list)){
		
		$query = "
			UPDATE ".$_SESSION['members_table']."
			SET address=:address1, address2=:address2, city=:city, state=:state, zip_code=:zip, country=:country, country_code=:country_code
			WHERE member_id=:member_id
		";
		
		try{
			$rsInsertAuth = $mLink->prepare($query);
			$aValues = array(
				':member_id' 		=> $_SESSION['member_id'],
				':address1'			=> $address1,
				':address2'			=> $address2,
				':city'				=> $city,
				':state'			=> $state,
				':zip'				=> $zip,
				':country'			=> $countryName,
				':country_code'		=> $countryCode
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsInsertAuth->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
	}else{
		//Print errors
		echo '<ul>';
		
		foreach($error_list as $key=>$errors){
			echo '<li>'.$errors.'</li>';	
		}
		
		echo '</ul>';
	}
	
	//echo $country.' | '.$address1.' | '.$address2.' | '.$city.' | '.$state.' | '.$zip;
	
	echo $error;
	
	sleep(1);
}

//+---------------------------------------------------------------------------------------------------------+
//|									Save Profile Questions - PROCESS 17										|
//+---------------------------------------------------------------------------------------------------------+
if($process == '17'){

	$aQuestionIDs = explode('|',$_REQUEST['questions']);
	
	foreach($aQuestionIDs as $key=>$QID){
	
		$answer = $_REQUEST[''.$QID.''];
	
			//check to see if answer is an array
			if(is_array($answer) == true){
				//answer is an array
				$answer = implode('|', $answer);
			}else{
				//answer is not an array
				$answer = $answer;
			}
			
			$query = "
				INSERT INTO ".$_SESSION['profile_answers_table']." (
					qid,
					member_id,
					answer,
					timestamp
				) VALUES (
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

	}//end for each question
	
	print_r($error);
	
}

//+---------------------------------------------------------------------------------------------------------+
//|									Save Profile Info - PROCESS 18											|
//+---------------------------------------------------------------------------------------------------------+
if($process == '18'){
	
	$interests 	= explode(',',$_REQUEST['interests']);
	$occupation	= $_REQUEST['occupation'];
	$day		= $_REQUEST['day'];
	$month		= $_REQUEST['month'];
	$year		= $_REQUEST['year'];
	$dob		= $year.'-'.$month.'-'.$day;	
	$about		= $_REQUEST['about_me'];
	$website	= $_REQUEST['personal_site'];
	$linkedin	= $_REQUEST['linkedin_url'];
	
	$query = "
		UPDATE ".$_SESSION['members_profile_table']."
		SET about_me=:about, occupation=:occupation, interests=:interests, DOB=:dob, personal_site=:website, linkedin=:linkedin
		WHERE member_id=:member_id
	";
	try{
		$rsUpdateProfile = $mLink->prepare($query);
		$aValues = array(
			':member_id' 		=> $_SESSION['member_id'],
			':about'			=> $about,
			':occupation'		=> $occupation,
			':interests'		=> implode('|', $interests),
			':dob'				=> $dob,
			':website'			=> $website,
			':linkedin'			=> $linkedin
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsUpdateProfile->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	echo $error;
	
}

//+---------------------------------------------------------------------------------------------------------+
//|									Change Password - PROCESS 19											|
//+---------------------------------------------------------------------------------------------------------+
if($process == '19'){
	
	$currentPW 	= $_REQUEST['current_pw'];
	$newPW		= $_REQUEST['new_pw'];
	$newPW2		= $_REQUEST['new_pw2'];
	$pwScore	= $_REQUEST['password_score'];
	
		
	$aErrors = array();

	if(empty($currentPW) || $currentPW == ''){
		$aErrors[] = 'Please provide your current password.';	
	}
	
	if(empty($newPW) || $newPW == ''){
		$aErrors[] = 'Please provide your new password.';	
	}
	
	if(empty($newPW2) || $newPW2 == ''){
		$aErrors[] = 'Please re-type your new password.';	
	}
	
	if($newPW != $newPW2){
		$aErrors[] = 'Passwords do not match, please re-type your new password.';	
	}
	
	if($pwScore < 35){
		$aErrors[] = 'Password is too weak. Please make a stronger password.';
	}
	
	if(empty($aErrors)){
		
		$query = "
			SELECT *
			FROM ".$_SESSION['auth_table']."
			WHERE member_id=:memberID
			ORDER BY timestamp DESC
			LIMIT 1
		";
		
		try{
			$rsGetAuth = $mLink->prepare($query);
			$aValues = array(
				':memberID' 	=> $_SESSION['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetAuth->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$auth = $rsGetAuth->fetch(PDO::FETCH_ASSOC);
		
		//include encryptions
		require_once('../../../secure/crypto.php');
		
		$encryptCurrentPW = encrypt($currentPW);
		
		if($encryptCurrentPW != $auth['password']){
			
			$aErrors[] = 'Warning: The current password you entered is NOT correct.';
				
		}
		
		$encryptNewPW = encrypt($newPW);
		
		if($auth['password'] == $encryptNewPW){
			
			$aErrors[] = 'Warning: New password can NOT be the same as previous password.';
				
		}
		

		if(empty($aErrors)){
		
			//ADD new auth record
			$query = "
				INSERT INTO ".$_SESSION['auth_table']." (
					member_id,
					timestamp,
					username,
					password,
					password_score,
					email,
					email_validated_timestamp
				)VALUES(
					:member_id,
					UNIX_TIMESTAMP(),
					:username,
					:password,
					:password_score,
					:email,
					:email_val_time
				)
			";
			
			try{
				$rsAddAuth = $mLink->prepare($query);
				$aValues = array(
					':password_score'	=> $pwScore,
					':email_val_time'	=> $auth['email_validated_timestamp'],
					':member_id' 		=> $_SESSION['member_id'],
					':username'			=> $auth['username'],
					':password'			=> $encryptNewPW,
					':email'			=> $auth['email']
					
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsAddAuth->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			//echo $preparedQuery;
			
			$aMember = get_member($mLink, $_SESSION['member_id'], 'all');
			
			//START Send Confirmation Email
			$aEmailVars = array(
				'first_name'	=> $aMember['firstName'],
				'change_link'	=> 'http://beta.marketocracy.com/?page=10-00-00-002&account=1&tab=password'
			);
			$aEmail = array(
				'email_id'		=> '7',
				'to'			=> $aMember['email'],
				'vars'			=> $aEmailVars
			);
			include('../../includes/email/system-email.php');
			//END Send Confirmation Email
			
			//START add event record
			$event = 'Account Update';
			$detail = $aMember['username'].' has updated their password.';
			addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
			//END add event record
			
			sleep(1);
			echo 'success';
		}
		
	}
	
	if(!empty($aErrors)){
		
		echo '<ul style="color:#FF0000;">';
		
		foreach($aErrors as $key=>$errors){
			echo '<li>'.$errors.'</li>';	
		}
		
		echo '</ul>';
	}
	
	//echo $currentPW.' | '.$newPW.' | '.$newPW2;
}
?>