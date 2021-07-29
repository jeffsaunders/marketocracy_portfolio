<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
// This PHP script is run via an AJAX call to score the password as it's being entered
// Results are returned as HTML to be displayed on the form, after which the script is discontinued - no need to proceed

// Start me up...
session_start();

// Load default functions
require("../../includes/system-debug-functions.php");
require("../../includes/system-functions.php");

$newPW	= $_REQUEST['newPW'];
$newPW2	= $_REQUEST['newPW2'];

//Check if both fields are empty
if($newPW == '' && $newPW2 == ''){
	//Both Fields empty
	$color		= "#FFD700";
	$message	= 'Please enter a password.';
	
}elseif($newPW == ''){
	//First Field is empty
	$color		= "#FFD700";
	$message	= 'Please provide a password in the "New Password" field.';	
	
}else{
	//check to see if passwords match
	if($newPW != $newPW2){
		
		//Passwords do not match
		$color		= "#DC143C";
		$message	= 'Passwords Do Not Match!';
		
		
	}else{
		//Passwords Match
		$color		= "#006400";
		$message	= "Passwords Match!";
		$textColor	= '#ffffff';
		
	}
}

echo '<table width="100%"><tr><td height="5"></td></tr><tr><td style="color:'.$textColor.'; background-color:'.$color.'; border-radius:4px; padding-bottom:2px; text-align:center;"><strong>'.$message.'</strong></td></tr></table>';	