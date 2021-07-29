<?php
/*
Marketocracy Portfolio Account Creation

JS FILE: includes/scripts/login.js.php


*/

error_reporting(E_ALL);
ini_set('display_errors', '1');

//Start User Session
session_start();

//Connect to DB
require_once("../../secure/dbconnect.php");

//Get global settings and functions
require("../includes/system-debug-functions.php");
require("../includes/system-global.php");
require("../includes/system-functions.php");

// Not sure what I was planning for in the alternative here....
$task = (isset($_REQUEST['task']) ? $_REQUEST['task'] : "xxxxxxxxxx");


// Ended up being just one task but leaving as-is so it works.
switch($task) {

	case "create_acct":

		$aErrors = array();

		//$aErrors[] = 'You can not create an account at this time.';

		//Lets just double check whats being submitted, just in case someone got past the JQuery validation
		if(empty($_REQUEST['username'])){
			$aErrors[] = 'Please provide a username.';
		}
		if($_REQUEST['username_test'] != 'passed'){
			$aErrors[] = $_REQUEST['username_test'];
		}
		if(empty($_REQUEST['password'])){
			$aErrors[] = 'Please provide a password.';
		}
		if(empty($_REQUEST['email'])){
			$aErrors[] = 'Please provide your email.';
		}
		if($_REQUEST['email_test'] != 'passed'){
			$aErrors[] = $_REQUEST['email_test'];
		}


		if(empty($aErrors)){


			//+------------------------------------------------------------------------------------------+
			//|							START | CREATE MEMBER RECORD / GET MEMBER ID
			//+------------------------------------------------------------------------------------------+
			$query = "
				INSERT INTO ".$_SESSION['members_table']." (
					timestamp,
					username,
					email,
					signup_code_id,
					joined_timestamp
				)VALUES(
					UNIX_TIMESTAMP(),
					:username,
					:email,
					:signup_code_id,
					UNIX_TIMESTAMP()
				)
			";
			try {
				$rsInsert = $mLink->prepare($query);
				$aValues = array(
					':username'	   		=> $_REQUEST['username'],
					':email'	   		=> $_REQUEST['email'],
					':signup_code_id'	=> $_REQUEST['signup_code_id']
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsInsert->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$query = "
				SELECT member_id
				FROM ".$_SESSION['members_table']."
				WHERE username=:username
			";
			try {
				$rsGetMemberID = $mLink->prepare($query);
				$aValues = array(
					':username'	   		=> $_REQUEST['username']
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetMemberID->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$getMemberID = $rsGetMemberID->fetch(PDO::FETCH_ASSOC);
			$memberID = $getMemberID['member_id'];
			
			//$memberID = $mLink->lastInsertId();
			//+------------------------------------------------------------------------------------------+
			//|							END | CREATE MEMBER RECORD / GET MEMBER ID
			//+------------------------------------------------------------------------------------------+
			
			
			
			//+------------------------------------------------------------------------------------------+
			//|								START | INSERT INTO AUTH TABLE
			//+------------------------------------------------------------------------------------------+
			
			#Load encryption functions
			require_once("../../secure/crypto.php");
			
			#Encrypt the new password for DB insert
			$encrypted_username	= encrypt(strtolower($_REQUEST['username']));
			$encrypted_password = encrypt($_REQUEST['password']);
			$encrypted_email 	= encrypt($_REQUEST['email']);

			#Insert new authentication record and mark it for awaiting validation
			$query = "
				INSERT INTO ".$_SESSION['auth_table']." (
					member_id,
					timestamp,
					username,
					password,
					password_score,
					password_score_ack_timestamp,
					email
				)VALUES(
					:member_id,
					UNIX_TIMESTAMP(),
					:username,
					:password,
					:password_score,
					:password_score_ack,
					:email
				)
			";

			try {
				$rsInsert = $mLink->prepare($query);
				$aValues = array(
					':member_id'			=> $memberID,
					':username'				=> $encrypted_username,
					':password'				=> $encrypted_password,
					':password_score'		=> $_REQUEST['password_score'],
					':password_score_ack'	=> ($_REQUEST['password_score'] < 41 ? time() : 0),
					':email'				=> $encrypted_email
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsInsert->execute($aValues);
				//$lastInsertID = $mLink->lastInsertId();
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			//+------------------------------------------------------------------------------------------+
			//|								END | INSERT INTO AUTH TABLE
			//+------------------------------------------------------------------------------------------+



			//+------------------------------------------------------------------------------------------+
			//|								START | CREATE MEMBER FLAG RECORD
			//+------------------------------------------------------------------------------------------+
			$query = "
				INSERT INTO ".$_SESSION['members_flags_table']." (
					member_id
				)
				VALUES(
					:member_id
				)
			";
			try {
				$rsInsert = $mLink->prepare($query);
				$aValues = array(
					':member_id'	=> $memberID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsInsert->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			//+------------------------------------------------------------------------------------------+
			//|								END | CREATE FLAG RECORD
			//+------------------------------------------------------------------------------------------+
			
			
			
			//+------------------------------------------------------------------------------------------+
			//|							START | CREATE MEMBER SETTINGS RECORD
			//+------------------------------------------------------------------------------------------+
			$query = "
				INSERT INTO ".$_SESSION['members_settings_table']." (
					member_id,
					dash_col1,
					dash_col2,
					dash_4col1,
					dash_4col2,
					timestamp
				) VALUES (
					:member_id,
					:dash_col1,
					:dash_col2,
					:dash_4col1,
					:dash_4col2,
					UNIX_TIMESTAMP()
				)
			";
			try {
				$rsInsert = $mLink->prepare($query);
				$aValues = array(
					':member_id'	=> $memberID,
					':dash_col1'	=> "notifications~0~0~0",
					':dash_col2'    => "tickers~0~0~0",
					':dash_4col1'	=> "notifications~0~0~0",
					':dash_4col2'   => "tickers~0~0~0"
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			//die($preparedQuery);
				$rsInsert->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					echo $error;
			}
			//+------------------------------------------------------------------------------------------+
			//|							END | CREATE MEMBER SETTINGS RECORD
			//+------------------------------------------------------------------------------------------+
			
			
			
			//+------------------------------------------------------------------------------------------+
			//|							START | SEND NEW MANAGER METHOD
			//+------------------------------------------------------------------------------------------+
			$aMethodVars[] = array(
				'method'	=> 'newManager', #method name
				'source'    => 'User Sign Up | system-email-validate.php', #File and Code location of this instance
				'api'       => '1', #api switch, 1 = api1, 2 = api2
				'username'  => trim($_REQUEST['username']), #username of member
				'email'     => trim($_REQUEST['email']), #email of member
				'member_id' => $memberID #member id member
			);
			$mlaResults = legacy_api($mLink, $aMethodVars, true, 'results');
			//+------------------------------------------------------------------------------------------+
			//|							END | SEND NEW MANAGER METHOD
			//+------------------------------------------------------------------------------------------+
			
			
			
			//+------------------------------------------------------------------------------------------+
			//|								START | LOG EVENT
			//+------------------------------------------------------------------------------------------+
			$event = 'Create Account';
			$detail = 'Member: '.$_REQUEST['username'].' has created an account.';
			addEventRecord($mLink, $memberID, $event, $detail);
			//+------------------------------------------------------------------------------------------+
			//|								END | LOG EVENT
			//+------------------------------------------------------------------------------------------+
			
			
			
			//+------------------------------------------------------------------------------------------+
			//|								START | SEND VERIFICATION EMAIL
			//+------------------------------------------------------------------------------------------+
			
			// The following "dirty's up" the URL so email clients won't turn it into a clickable hyperlink by adding a zero-width non-breaking space in the protocol and around each dot.
			$bad_url = $_SESSION['site_protocol'].'&#65279;://'.str_replace(".", "&#65279;.&#65279;", $_SESSION['base_url']);
			
			#Create a token
			$auth_string = encrypt($_REQUEST['email']);
			$token = json_encode($auth_string);
			
			$tokenLink = $_SESSION['site_root'].'validate-email/'.$token.'';

			$aEmailVars = array(
				'verify_link'	=> $tokenLink,
				'username'		=> $_REQUEST['username']
			);
			$aEmail = array(
				'email_id'		=> '9',
				'to'			=> $_REQUEST['email'],
				'vars'			=> $aEmailVars
			);
			include('../includes/email/system-email.php');
			//+------------------------------------------------------------------------------------------+
			//|								END | SEND VERIFICATION EMAIL
			//+------------------------------------------------------------------------------------------+

			#SHOW EMAIL SENT MESSAGE
			echo '
				<h3>Sign Up</h3>
				<div class="alert alert-success">
					<p>A verification email has been sent to the email you provided. Please check your email to continue.</p>

					<p>Note: You may need to check your SPAM folder. If your verification email is in your SPAM folder, mark the email as "Not Spam" or move it to your inbox.</p>
				</div>
				<div class="form-actions">
					<button id="register-back-btn2" type="button" class="btn btn-default">
						<i class="m-icon-swapleft"></i>  Back
					</button>

				</div>
			';

			/*echo '<div class="alert alert-danger" id="show-error"><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';
			}
			echo '</ul></div>';*/
		}else{
			
			#IF THERE ARE ERRORS SHOW THEM TO THE SCREEN
			echo '<div class="alert alert-danger" id="show-error"><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';
			}
			echo '</ul></div>';

		}

		break;

} //End switch

?>