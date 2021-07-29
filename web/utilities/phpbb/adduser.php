<?php

// Allow external access to phpBB functions
define('IN_PHPBB', true);

// Root to phpBB 
$phpbb_root_path = '../../forum/';  // Path to phpBB root

// Set cargo. This var is used by phpBB
$phpEx = substr(strrchr(__FILE__, '.'), 1); 

// Include common library from phpBB
include($phpbb_root_path . 'common.' . $phpEx);


//+-----------------------------------------------------+
//|  				Connect to our DB 					|
//+-----------------------------------------------------+
// Load debug & error logging functions
require("../../includes/system-debug-functions.php");

// Connect to DB
require("dbconnect.php");

//Start session management 
$user->session_begin(); 
$auth->acl($user->data); 
$user->setup(); 

//Require phpBB user functions
require($phpbb_root_path .'includes/functions_user.php'); 

$message = "Please fill out the form. Then click A";

if($_POST['add_user']){
	//Get Form Data
	$username 	= strip_tags(strtolower($_POST['username'])); 
	$password 	= strip_tags($_POST['password']); //Do NOT encrypt! phpBB functions will do this.  
	$email  	= strip_tags($_POST['email']); //Phpbb will accept non-valid emails. 
	
	//Validate Form | Check to see if any fields are empty
	if (!$username||!$password||!$email){
		// Empty Fields, display error.
		$message =  "Please provide a fill out entire form";	
	}else{
		// If form is not empty query phpBB DB for username and email
		$query = "
			SELECT * FROM phpbb_users WHERE username_clean=:username OR user_email=:user_email
		";
		try{
			$rsUser = $mLink->prepare($query);
			$aValues = array(
				':username'		=> $username,
				':user_email' 	=> $email,
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUser->execute($aValues);
		}// End Try
		catch(PDOException $error){
			// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}// End Catch
		
		
		// Count rows of result set
		if($rsUser->rowCount() > 0){
			// If result set is greater than 0 stop script
			$message = "Username or Email already exists. Can not continue.";	
		
		}else{	
			// If result set is 0 continue with phpBB DB insert
			//echo "Username and Email Not Found";
			
			//Build array for phpBB add_user function
			$user_row = array( 
				'username' => $username, 
				'user_password' => md5($password), 
				'user_email' => $email, 
				'group_id' => 2, #Registered users group 
				'user_timezone' => '1.00', 
				'user_dst' => 0, 
				'user_lang' => 'en', 
				'user_type' => '0', 
				'user_actkey' => '', 
				'user_dateformat' => 'd M Y H:i', 
				'user_style' => 1, 
				'user_regdate' => time(), 
			); 
			
			$phpbb_user_id = user_add($user_row); 
			echo "New user id = ".$phpbb_user_id;
		}
		
	}// End Check Empty Fields
}//End IF POST


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Add User to PHPBB</title>
</head>

<body>
<h1>Create PHPBB user</h1>

<h4><?php echo $message; ?></h4>
<form action="adduser.php" method="POST">

<label>Username:</label><br />
<input type="text" name="username" value="<?php echo $username;?>" /><p />

<label>Password:</label><br  />
<input type="password" name="password" value="<?php echo $password;?>" /><p />

<label>Email:</label><br />
<input type="text" name="email" value="<?php echo $email;?>" /><p />

<input type="submit" name="add_user" value="Add User" />

</form>

</body>
</html>