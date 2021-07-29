<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

//Start User Session
session_start();

//Connect to DB
require_once("../../secure/dbconnect.php");

//Get global settings and functions
require("../includes/system-global.php");

$task = (isset($_REQUEST['task']) ? $_REQUEST['task'] : "logout");
//echo $task;die();
switch($task) {

	case "logout":
//die("x");
		// Make sure they are actually logged in
		if (isset($_SESSION['member_id']) == true){

			// log the event
			$query = "
				INSERT INTO ".$_SESSION['eventslog_table']." (
					member_id,
					timestamp,
					event,
					detail
				) VALUES (
					:member_id,
					UNIX_TIMESTAMP(),
					:event,
					:detail
				)
			";
			try {
				$rsInsert = $mLink->prepare($query);
				$aValues = array(
					':member_id'	=> $_SESSION['member_id'],
					':event'		=> "Logout",
					':detail'		=> (isset($_REQUEST['status']) ? $_REQUEST['status'] : "")
				);
				// Prepared query - for error logging and debugging
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
				$rsInsert->execute($aValues);
			}
			catch(PDOException $error){
// Must write log here - if included or called as a function it loses the filename and line number values of where the error occurred
//				require("../includes/log-pdo-errors.php"); // Log any errors
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}

			// Destroy session cookies, server AND client side
			if (isset($_COOKIE[session_name()])) setcookie(session_name(), '', time()-42000, '/');
			session_destroy();
		}

		// Send them to the login screen
		header('Location: /login'.($_REQUEST['status'] == "session-timeout" ? "-timeout" : ""));
 		break;


	case "login":
		
		
		
		//Load encryption functions
		require_once("../../secure/crypto.php");

		// Encrypt the passed username and password for DB lookup
		$encrypted_username = encrypt($_REQUEST['username']);
		$encrypted_password = encrypt($_REQUEST['password']);
		//Look up the encrypted passed values
		$query = "
			SELECT *
			FROM ".$_SESSION['auth_table']."
			WHERE :encrypted_username IN (username, email)
			AND email_validated_timestamp > 0
			ORDER BY timestamp DESC
			LIMIT 1
		";
		try {
			$rsAuth = $mLink->prepare($query);
			$aValues = array(
				':encrypted_username'	=> $encrypted_username
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
			$rsAuth->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$auth = $rsAuth->fetch(PDO::FETCH_ASSOC);

		// Authentication failed
		if ($rsAuth->rowCount() < 1 || $auth['password'] !== $encrypted_password){
			header('Location: /login-failed');
			break;
		}

		// They're in!
		// Assign session varibles
		$_SESSION['member_id']		= $auth['member_id'];
		$_SESSION['username']		= trim(decrypt($auth['username']));
		$_SESSION['email']			= trim(decrypt($auth['email']));
		
		// Assign Admin Session Variable <-- BM
		$query = "
			SELECT admin
			FROM ".$_SESSION['members_table']."
			WHERE member_id=".$_SESSION['member_id']."
		";
		$rsIsAdmin = $mLink->prepare($query);
		$rsIsAdmin->execute();
		$siteAdmin = $rsIsAdmin->fetch(PDO::FETCH_ASSOC);
		
		$_SESSION['admin'] = $siteAdmin['admin'];

		// If they checked "Remember Me", set a cookie for 1 year, otherwise delete it
		if (isset($_REQUEST['remember'])){
			setcookie("marketocracy[username]", $_REQUEST['username'], time()+60*60*24*365, "/", "marketocracy.com", false, true);
//			setcookie("marketocracy[password]", $_REQUEST['password'], time()+60*60*24*365, "/", "marketocracy.com", false, true);
		}else{
			setcookie("marketocracy[username]", "", time()-42000, "/", "marketocracy.com", false, true);
//			setcookie("marketocracy[password]", "", time()-42000, "/", "marketocracy.com", false, true);
		}

		// log the event
		$query = "
			INSERT INTO ".$_SESSION['eventslog_table']." (
				member_id,
				timestamp,
				event,
				detail
			) VALUES (
				:member_id,
				UNIX_TIMESTAMP(),
				:event,
				:detail
			)
		";
		try {
			$rsInsert = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $_SESSION['member_id'],
				':event'		=> "Login",
				':detail'		=> ""
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
			$rsInsert->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}

		//Look up their membership information
		$query = "
			SELECT name_first, name_last, joined_timestamp
			FROM ".$_SESSION['members_table']."
			WHERE member_id = :member_id
			ORDER BY timestamp DESC
			LIMIT 1
		";
		//echo $query;
		try {
			$rsMember = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $_SESSION['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
			$rsMember->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}

		$member = $rsMember->fetch(PDO::FETCH_ASSOC);
		//Assign session varibles
		$_SESSION['first_name']			= $member['name_first'];
		$_SESSION['last_name']			= $member['name_last'];
		$_SESSION['joined_timestamp']	= $member['joined_timestamp'];

		//Get hidden field from login form that contains URI Redirect
		if($_REQUEST['redirect'] != ""){
			$redirect = $_REQUEST['redirect'];
		}
		
		// Send them to the dashboard
		header('Location: /'.$redirect.'');
 		break;


	case "reset-password":

		//Load encryption functions
		require_once("../../secure/crypto.php");

		// Encrypt the passed username and email for DB lookup
		$encrypted_username = encrypt($_REQUEST['username']);
		$encrypted_email = encrypt($_REQUEST['email']);

		//Look up the encrypted passed values
		$query = "
			SELECT *
			FROM ".$_SESSION['auth_table']."
			WHERE username = :encrypted_username
			AND email = :encrypted_email
			ORDER BY timestamp DESC
			LIMIT 1
		";
		try {
			$rsAuth = $mLink->prepare($query);
			$aValues = array(
				':encrypted_username'	=> $encrypted_username,
				':encrypted_email'		=> $encrypted_email
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
			$rsAuth->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$auth = $rsAuth->fetch(PDO::FETCH_ASSOC);

		// Authentication succeeded (fall through if failed)
		// ...act like all is well but don't do anything more - don't clue any hackers in on the failure
		if ($rsAuth->rowCount() == 1){

			// Update the auth record with a unixtime stamp for later comparison (basis for time limit)
			$query = "
				UPDATE ".$_SESSION['auth_table']."
				SET reset_request_timestamp = UNIX_TIMESTAMP(),
					reset_request_ip = :ip
				WHERE uid = :uid
			";
			try {
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':ip'	=> $_SERVER['REMOTE_ADDR'],
					':uid'	=> $auth['uid']
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
				$rsUpdate->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}

			//Look up the member's first name for a more "friendly" email.
			$query = "
				SELECT name_first
				FROM ".$_SESSION['members_table']."
				WHERE member_id = :member_id
				ORDER BY timestamp DESC
				LIMIT 1
			";
			//echo $query;
			try {
				$rsMember = $mLink->prepare($query);
				$aValues = array(
					':member_id'	=> $auth['member_id']
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
				$rsMember->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$member = $rsMember->fetch(PDO::FETCH_ASSOC);

			// Build auth token for email
			$auth_string = encrypt(decrypt($auth['email'])."|".trim($auth['member_id']));
 			$token = json_encode($auth_string);

//echo $auth_string."<br>";
//echo $token."<br>";
//echo decrypt($token)."<br>";
//die();
			//Build email and send it
			require("../includes/email/password-reset-email.php");
		}
//die();
		// tell 'em to watch for it (even if none was sent)
		header('Location: /password-email-sent');
		break;



	case "change-password":

		//Load encryption functions
		require_once("../../secure/crypto.php");

		// Get member's current credentials
		$query = "
			SELECT *
			FROM ".$_SESSION['auth_table']."
			WHERE member_id = :member_id
			ORDER BY timestamp DESC
			LIMIT 1
		";
		//echo $query;
		try {
			$rsAuth = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $_REQUEST['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
			$rsAuth->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$auth = $rsAuth->fetch(PDO::FETCH_ASSOC);

		// Encrypt the new password for DB insert
		$encrypted_password = encrypt($_REQUEST['password']);

		// Insert new authentication record
		$query = "
			INSERT INTO ".$_SESSION['auth_table']." (
				member_id,
				timestamp,
				username,
				password,
				password_score,
				password_score_ack_timestamp,
				email,
				email_validated_timestamp
			) VALUES (
				:member_id,
				UNIX_TIMESTAMP(),
				:username,
				:password,
				:password_score,
				:password_score_ack,
				:email,
				:email_validated_timestamp
			)
		";
		try {
			$rsInsert = $mLink->prepare($query);
			$aValues = array(
				':member_id'					=> $_REQUEST['member_id'],
				':username'						=> $auth['username'],
				':password'						=> $encrypted_password,
				':password_score'				=> $_REQUEST['password_score'],
				':password_score_ack'			=> ($_REQUEST['password_score'] < 41 ? time() : 0),
				':email'						=> $auth['email'],
				':email_validated_timestamp'	=> $auth['email_validated_timestamp']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
			$rsInsert->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
 			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}

		// log the event
		$query = "
			INSERT INTO ".$_SESSION['eventslog_table']." (
				member_id,
				timestamp,
				event,
				detail
			) VALUES (
				:member_id,
				UNIX_TIMESTAMP(),
				:event,
				:detail
			)
		";
		try {
			$rsInsert = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $auth['member_id'],
				':event'		=> "Password Change",
				':detail'		=> ""
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
			$rsInsert->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
 			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}

		//Look up the member's first name for a more "friendly" email.
		$query = "
			SELECT name_first
			FROM ".$_SESSION['members_table']."
			WHERE member_id = :member_id
			ORDER BY timestamp DESC
			LIMIT 1
		";
		try {
			$rsMember = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $auth['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
			$rsMember->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$member = $rsMember->fetch(PDO::FETCH_ASSOC);
//die();
		// Send them a confirmation email
		require("../includes/email/password-reset-success-email.php");

		// Tell 'em the reset succeeded
		header('Location: /reset-succeeded');
		break;

} //End switch

?>