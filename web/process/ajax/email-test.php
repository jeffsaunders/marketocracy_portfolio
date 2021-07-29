<?php

session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

// Load System Wide Functions
require_once("../../includes/system-functions.php");

$to = $_REQUEST['email'];

//START Send Confirmation Email
$aEmailVars = array(
	'verify_link'	=> 'test.php'
);
$aEmail = array(
	'email_id'		=> '9',
	'to'			=> $to,
	'vars'			=> $aEmailVars
);
include('../../includes/email/system-email.php');
//END Send Confirmation Email

echo 'sent';

?>