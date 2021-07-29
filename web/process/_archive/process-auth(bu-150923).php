<?php
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

$task = (isset($_REQUEST['task']) ? $_REQUEST['task'] : "logout");

$startPort 	= '52000';
$endPort	= '52099';

//echo $task;die();
switch($task) {

	case "logout":
//die("x");
		// Make sure they are actually logged in
		if (isset($_SESSION['member_id']) == true){

			// Delete their login record
			$query = "
				DELETE FROM ".$_SESSION['logged_in_table']."
				WHERE member_id = :member_id
				AND session_id = :session_id
			";
			try {
				$rsDelete = $mLink->prepare($query);
				$aValues = array(
					':member_id'	=> $_SESSION['member_id'],
					':session_id'	=> session_id()
				);
				// Prepared query - for error logging and debugging
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
				$rsDelete->execute($aValues);
			}
			catch(PDOException $error){
// Must write log here - if included or called as a function it loses the filename and line number values of where the error occurred
//				require("../includes/log-pdo-errors.php"); // Log any errors
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
		$encrypted_username = encrypt(strtolower($_REQUEST['username']));
		$encrypted_password = encrypt($_REQUEST['password']);

		//Look up the encrypted passed username
		$query = "
			SELECT *
			FROM ".$_SESSION['auth_table']."
			WHERE :encrypted_username IN (username, email)
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

		// Authentication failed - no match for username/email
		if ($rsAuth->rowCount() < 1){

			$query = "
				SELECT *
				FROM clear_passwords
				WHERE username = :username
				LIMIT 1
			";
			try {
				$rsExists = $mLink->prepare($query);
				$aValues = array(
					':username'	=> $_REQUEST['username']
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
				$rsExists->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$exists = $rsExists->fetch(PDO::FETCH_ASSOC);

			if ($rsExists->rowCount() >= 1){

				// Go get 'em
				$url = "http://192.168.111.212/batch/importMember.php?username=".$_REQUEST['username'];
				exec('/var/www/html/'.$_SESSION['base_url'].'web/process/scripts/process-member-import.sh "'.$url.'" > /dev/null &');

				header('Location: /importing');
				break;

			}else{
				header('Location: /login-failed');
				break;
			}
		}

		// Test for a password match
		if ($auth['password'] !== $encrypted_password){
			header('Location: /login-failed');
			break;
		}

		// Make sure they validated their account
		if ($auth['email_validated_timestamp'] <= 0){
			$_SESSION['email'] = trim(decrypt($auth['email']));
			header('Location: /login-not-validated');
			break;
		}

		// They're in!  Toss that old id so the credentials are vapor
		session_regenerate_id();
		$_SESSION['session_id'] = session_id();

		// Add them to the logged_in table
		$query = "
			INSERT INTO ".$_SESSION['logged_in_table']." (
				member_id,
				username,
				session_id,
				login_timestamp,
				heartbeat_timestamp
			) VALUES (
				:member_id,
				:username,
				:session_id,
				UNIX_TIMESTAMP(),
				UNIX_TIMESTAMP()
			)
		";
		try {
			$rsInsert = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $auth['member_id'],
				':username'		=> trim(decrypt($auth['username'])),
				':session_id'	=> $_SESSION['session_id']
			);
			// Prepared query - for error logging and debugging
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
			$rsInsert->execute($aValues);
		}
		catch(PDOException $error){
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}

		// Start assigning session variables
		$_SESSION['member_id']		= $auth['member_id'];
		$_SESSION['username']		= trim(decrypt($auth['username']));
		$_SESSION['email']			= trim(decrypt($auth['email']));

		//Look up their membership information
		$query = "
			SELECT name_first, name_last, joined_timestamp, last_login, email
			FROM ".$_SESSION['members_table']."
			WHERE member_id = :member_id
			ORDER BY timestamp DESC
			LIMIT 1
		";
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

		//Assign more session varibles
		$_SESSION['first_name']			= $member['name_first'];
		$_SESSION['last_name']			= $member['name_last'];
		$_SESSION['joined_timestamp']	= $member['joined_timestamp'];
		$_SESSION['last_login']			= $member['last_login'];
		$_SESSION['user_email']			= $member['email'];

		//Look up their membership flags
		$query = "
			SELECT *
			FROM ".$_SESSION['members_flags_table']."
			WHERE member_id = :member_id
			ORDER BY uid DESC
			LIMIT 1
		";
		//echo $query;
		try {
			$rsFlags = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $_SESSION['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
			$rsFlags->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$flags = $rsFlags->fetch(PDO::FETCH_ASSOC);

		// Save each flag value to a session variable based on it's column name
		foreach($flags as $key => $value){
			$_SESSION[$key] = $value; // Do this until all older references sans "flag_" are purged
			$_SESSION['flag_'.$key] = $value;
		}

		// Get rid of extraneous "uid" value
		unset($_SESSION['uid']); // Remove this when the non "flag_" assignment is removed.
		unset($_SESSION['flag_uid']);

		// Get all the fund IDs for this member
		$query = "
			SELECT fund_id, fund_symbol, inception_date, unix_date
			FROM ".$_SESSION['fund_table']."
			WHERE member_id = :member_id AND active='1'
		";
		try {
			$rsFunds = $mLink->prepare($query);
			$aValues = array(
				':member_id' => $_SESSION['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
			$rsFunds->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		// Step through them and assign them to an array ($key=fundID, $value=fundSymbol)
		while ($fund = $rsFunds->fetch(PDO::FETCH_ASSOC)){
			$aFunds[$fund['fund_id']] = $fund['fund_symbol'];
			// Store their inception date, JIC we need it
			$aFundDates[$fund['fund_id']] = $fund['inception_date'];
			
			$aFundIncept[$fund['fund_id']] = $fund['unix_date'];
			
			
		}
		
//print_r($aFunds);die();

		// Get the most recent date we have fund data for (maxDate)
		$query = "
			SELECT maxdate
			FROM ".$_SESSION['fund_maxdate_table']."
		";
		$rsMaxDate = $mLink->prepare($query);
		$rsMaxDate->execute();
		$maxDate = $rsMaxDate->fetch(PDO::FETCH_ASSOC);

		// Assign it to session vars, clear and unixdate converted
		$_SESSION['max_date'] = $maxDate['maxdate'];
		$_SESSION['unix_max_date'] = trim(mktime(0, 0, 0, substr($maxDate['maxdate'], 4, 2), substr($maxDate['maxdate'], 6, 2), substr($maxDate['maxdate'], 0, 4)));
		
		
		// Pull all their fund info only once per day
//		if (date('Ymd') != date('Ymd', $_SESSION['last_login'])){

		// Grab a port to start on randomly
//		$port = rand(22200, 22249);
		$port = $startPort;


		if ($_SESSION['login_fundprice_checking'] == 'on' && date('Ymd') != date('Ymd', $_SESSION['last_login'])){
			
			$_SESSION['show-login-error']['if-check'] = 'run';
			
			// Price their fund(s)
			$query = "priceManager|".$_SESSION['username'];
			// Include the API call
			include("../includes/data-query-legacy.php");
			
			// Wait half a tick
			//usleep(500000);

			// Step through the funds array and pull the data for each
			foreach ($aFunds as $fund_id => $fund_symbol){
				// Get the date of the most recent fund history we have for each fund for this member
				$query = "
					SELECT date, unix_date
					FROM ".$_SESSION['fund_pricing_table']."
					WHERE fund_id = :fund_id
					ORDER BY unix_date DESC
					LIMIT 1
				";
				try {
					$rsDate = $mLink->prepare($query);
					$aValues = array(
						':fund_id'	=> $fund_id
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
					$rsDate->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				// If result set is empty use the fund's inception date as the starting date
				if ($rsDate->rowCount() < 1){
					$lastDate = $aFundDates[$fund_id]; // Inception Date
				}else{
					$date = $rsDate->fetch(PDO::FETCH_ASSOC);
					$lastDate = $date['date'];
				}
				
				
				// Get all the fund history from the most recent forward to maxDate, breaking up the query into manageable chunks
				if ($_SESSION['max_date'] > $lastDate){
					if (($_SESSION['max_date'] - $lastDate) > 1){  // Get multiple days

						#Assign dates
						$startDate = substr($lastDate, 0, 4)."-".substr($lastDate, 4, 2)."-".substr($lastDate, 6, 2);
						$endDate = substr($_SESSION['max_date'], 0, 4)."-".substr($_SESSION['max_date'], 4, 2)."-".substr($_SESSION['max_date'], 6, 2);

						#Go get 'em
						include("../includes/get-fund-pricing.php");
						
						
						
					}else{

						#Get just the one missing day
						$query = "priceRun|".$_SESSION['username']."|".$fund_id."|".$fund_symbol."|".$_SESSION['max_date'];

						#Set the port number for the API call
						if ($port == $endPort){
							$port = $startPort;
						}else{
							$port++;
						}

						#Include the API call
						include("../includes/data-query-legacy.php");
						
					}
				}
				
				
			//START | Get Basic Stratification
			/*	$query = "positionStratification|".$_SESSION['username']."|".$fund_id."|".$fund_symbol."";
				
				#Set the port number for the API call
				if ($port == $endPort){
					$port = $startPort;
				}else{
					$port++;
				}

				#Include the API call
				include("../includes/data-query-legacy.php");*/
			//END | Get Basic Stratification
				
				
			// START | Get aggregate stats for maxdate
				$query = "aggregateStatistics|".$_SESSION['username']."|".$fund_id."|".$fund_symbol."|".$_SESSION['max_date'];

				#Set the port number for the API call
				if ($port == $endPort){
					$port = $startPort;
				}else{
					$port++;
				}

				#Include the API call
				include("../includes/data-query-legacy.php");
				
				
			// END | Get aggregate stats for maxdate
				//$_SESSION['show-login-error'] = 'hello: '.$aMethodVars;

			// START | Get alphabeta stats for maxDate
				$query = "alphaBetaStatistics|".$_SESSION['username']."|".$fund_id."|".$fund_symbol."|".$_SESSION['max_date'];

				#Set the port number for the API call
				if ($port == $endPort){
					$port = $startPort;
				}else{
					$port++;
				}

				#Include the API call
				include("../includes/data-query-legacy.php");
				
				
			// END | Get alphabeta stats for maxDate
			
			// START | Get fund content details for maxDate
				$query = "positionDetail|".$_SESSION['username']."|".$fund_id."|".$fund_symbol."|".$_SESSION['max_date'];
//echo $query."<br>";

				#Set the port number for the API call
				if ($port == $endPort){
					$port = $startPort;
				}else{
					$port++;
				}

				#Include the API call
				include("../includes/data-query-legacy.php");
			// END | Get fund content details for maxDate
			}
		}
		
		// START | Get livePrice
		if (isMarketOpen(time(), $mLink, "none")){ // Do this only if the markets are open
			#get live price data
			foreach ($aFunds as $fund_id => $fund_symbol){
				$query = "livePrice|".$_SESSION['username']."|".$fund_id."|".$fund_symbol;

				#Set the port number for the API call
				if ($port == $endPort){
					$port = $startPort;
				}else{
					$port++;
				}

				#Include the API call
				include("../includes/data-query-legacy.php");
				
				
				
				

			}
		}
		// END | Get livePrice
		
		
		// START | Calculate Stratification
		foreach ($aFunds as $fund_id => $fund_symbol){
			exec('/usr/bin/php /var/www/html/portfolio.marketocracy.com/scripts/strat-build.php "fundID='.$fund_id.'" > /dev/null &');
		}
		// END | Calculate Stratification
		
		//+---------------------------------------------------------------------------------------------+
		//								FORUM MEMBER CONFIG SETTINGS									|
		//+---------------------------------------------------------------------------------------------+

		// Get Row of settings
		$query = "
			SELECT *
			FROM members_forum_settings
			WHERE member_id='".$_SESSION['member_id']."'
		";
		$rsForumSettings = $mLink->prepare($query);
		$rsForumSettings->execute();
		$forumConfig = $rsForumSettings->fetch(PDO::FETCH_ASSOC);

		//Set Member Forum Session Vars
		$_SESSION['forum_signature']	= $forumConfig['signature'];
		$_SESSION['forum_rank']			= $forumConfig['ranking'];
		$_SESSION['forum_page_rows']	= $forumConfig['page_rows'];

		// Write last login to member's record
		$query = "
			UPDATE ".$_SESSION['members_table']."
			SET last_login = UNIX_TIMESTAMP()
			WHERE member_id = :member_id
		";
		try {
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':member_id' => $_SESSION['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
			$rsUpdate->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}

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

		//Get hidden field from login form that contains URI Redirect
		if($_REQUEST['redirect'] != ""){
			$redirect = $_REQUEST['redirect'];
		}

		// Force them into the setup wizard if they have no membership level flag set or the force setup flag is set
		if (($_SESSION['flag_staff'] == 0 &&
			$_SESSION['flag_free'] == 0 &&
			$_SESSION['flag_standard'] == 0 &&
			$_SESSION['flag_student'] == 0 &&
			$_SESSION['flag_premium'] == 0 &&
			$_SESSION['flag_master'] == 0) ||
			($_SESSION['flag_force_setup'] == 1)){
//			header('Location: /setup-wizard');
			$redirect = "setup-wizard";
		}

		// Send them to the dashboard, or wherever it says to
		header('Location: /'.$redirect.'');
 		break;


	case "resend-welcome-email":

		//Load encryption functions
		require_once("../../secure/crypto.php");

		$auth_string = encrypt($_REQUEST['email']);
		$token = json_encode($auth_string);


		//START SEND VERIFICATION EMAIL
		$tokenLink = $_SESSION['site_root'].'validate-email/'.$token.'';

		$aEmailVars = array(
			'verify_link'	=> $tokenLink
		);
		$aEmail = array(
			'email_id'		=> '11',
			'to'			=> $_REQUEST['email'],
			'vars'			=> $aEmailVars
		);
		include('../includes/email/system-email.php');
		//END SEND VERIFICATION EMAIL

		// Send them to the dashboard
		header('Location: /welcome-email-resent');
 		break;



	case "reset-password":
		//Load encryption functions
		require_once("../../secure/crypto.php");

		// Encrypt the passed username and email for DB lookup
		$encrypted_username = encrypt(strtolower($_REQUEST['username']));
		$encrypted_email = encrypt(strtolower($_REQUEST['email']));

		//Look up the encrypted passed values
//		$query = "
//			SELECT *
//			FROM ".$_SESSION['auth_table']."
//			WHERE username = :encrypted_username
//			AND email = :encrypted_email
//			ORDER BY timestamp DESC
//			LIMIT 1
//		";
		$query = "
			SELECT *
			FROM ".$_SESSION['auth_table']."
			WHERE username = :encrypted_username AND email = :encrypted_email
			ORDER BY timestamp DESC
			LIMIT 1
		";
		try {
			$rsAuth = $mLink->prepare($query);
//			$aValues = array(
//				':encrypted_username'	=> $encrypted_username,
//				':encrypted_email'		=> $encrypted_email
//			);
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
		
		$passValidation	= false;
		
		// Authentication succeeded (fall through if failed)
		// ...act like all is well but don't do anything more - don't clue any hackers in on the failure
		if ($rsAuth->rowCount() == 1){

			$passValidation = true;

//die($auth["email"]);
//if ($auth["email"] != $encrypted_email){
/*	echo "<script>alert('Email Address Mismatch');</script>";*/
//	header('Location: /password-email-sent');
//die("X");
//	break;
//}


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

			
			//Build email and send it
			$aEmailVars = array(
				'password_link'	=> $_SESSION['site_root'].'reset-password/'.$token,
				'ip_address'	=> $_SERVER['REMOTE_ADDR']
			);
			$aEmail = array(
				'email_id'		=> '12',
				'to'			=> decrypt($auth['email']),
				'vars'			=> $aEmailVars
			);
			include('../includes/email/system-email.php');
			
		}else{
			$passValidation = false;
			
			if(!isset($_SESSION['forget_pw_attempts'])){
				$_SESSION['forgot_pw_attempts'] = 1;	
			}else{
				$_SESSION['forgot_pw_attempts'] = $_SESSION['forgot_pw_attempts']++;	
			}
			
			if($_SESSION['forgot_pw_attempts'] == 5){
				$_SESSION['forgot_pw_lockout'] = time();	
			}
		}
//die();
		// tell 'em to watch for it (even if none was sent)
		if($passValidation == true){
			$alertMessage = 'password-email-sent';
		}else{
			$alertMessage = 'password-email-failed';	
		}
		header('Location: /'.$alertMessage.'');
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
				':member_id'	=> $_REQUEST['member_id'],
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
			SELECT name_first, username
			FROM ".$_SESSION['members_table']."
			WHERE member_id = :member_id
			ORDER BY timestamp DESC
			LIMIT 1
		";
		try {
			$rsMember = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $_REQUEST['member_id']
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

		if($member['name_first'] == ''){
			$showName = $member['username'];	
		}else{
			$showName = $member['name_first'];	
		}
		// Send them a confirmation email
		$aEmailVars = array(
			'name_first'	=> $showName
		);
		$aEmail = array(
			'email_id'		=> '13',
			'to'			=> decrypt($auth['email']),
			'vars'			=> $aEmailVars
		);
		include('../includes/email/system-email.php');

		// Tell 'em the reset succeeded
		header('Location: /reset-succeeded');
		break;

} //End switch

?>