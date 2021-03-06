<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

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
//		header('Location: /login'.($_REQUEST['status'] == "session-timeout" ? "-timeout" : ""));
//		header('Location: '.($_REQUEST['status'] == "session-timeout" ? "/?page=11-00-00-001&task=login&status=session-timeout" : "/?page=11-00-00-001&task=login"));
		header('Location: '.($_REQUEST['status'] == "session-timeout" ? "/?page=11-00-00-001&task=login&status=session-timeout" : "/login"));
 		break;

	case "login":
//echo $_REQUEST['redirect']; die();

		// Assign passed values
//		$username = $_REQUEST['username'];
//		$password = $_REQUEST['password'];
		$username = urlencode($_REQUEST['username']);
		$password = urlencode($_REQUEST['password']);

		//Load encryption functions
		require_once("../../secure/crypto.php");

		// Encrypt the passed username and password for DB lookup
//		$encrypted_username = encrypt(strtolower($_REQUEST['username']));
//		$encrypted_password = encrypt($_REQUEST['password']);
		$encrypted_username = encrypt(strtolower(urldecode($_REQUEST['username'])));
		$encrypted_password = encrypt(urldecode($_REQUEST['password']));

		//Look up the encrypted passed username
		$query = "
			SELECT *
			FROM ".$_SESSION['auth_table']."
			WHERE :encrypted_username IN (username, email) AND member_id<>'0'
			ORDER BY timestamp DESC
			LIMIT 1
		";
		try {
			$rsAuth = $mLink->prepare($query);
			$aValues = array(
				':encrypted_username'	=> addslashes($encrypted_username)
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

// Moved test for validated email to just after this section
// Please DO NOT put anything here that tests any auth values to determine if they are eligible to log in - it defeats the import process below by aborting the login prematurely if the test fails, which it WILL if they are not yet imported!!

		// Username not found - check clear passwords and import them if found
		if ($rsAuth->rowCount() < 1){
			$query = "
				SELECT COUNT(*) AS found
				FROM clear_passwords
				WHERE LOWER(username) = :username
				LIMIT 1
			";
			try {
				$rsExists = $mLink->prepare($query);
				$aValues = array(
					':username'	=> strtolower($_REQUEST['username'])
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

//			if ($rsExists->rowCount() >= 1){
			if ($exists['found'] >= 1){

				// Go get 'em
				$url = "http://192.168.111.212/batch/importMember.php?username=".$_REQUEST['username'];
//				exec('/var/www/html/'.$_SESSION['base_url'].'web/process/scripts/process-member-import.sh "'.$url.'" > /dev/null &');
				// curl is better...
				system('curl '.$url.' > /dev/null &');

//				header('Location: /importing');
				header('Location: /?page=11-00-00-001&task=login&status=importing');
				break;

			}else{ // Not found - invalid username
//				header('Location: /login-failed');
				header('Location: /?page=11-00-00-001&task=login&status=login-failed');
				break;
			}
		}

// Brandon, not sure why you didn't just modify the section below (25 lines down) where I test for the exact same thing, but I'm leaving yours JIC
// Moved here from BEFORE import routine...
		#check to see if the email is validated, if not go to login screen and display proper record.
		if($auth['email_validated_timestamp'] == 0){

			header('Location: /?page=11-00-00-001&task=login&status=login-not-validated2&email='.rawurlencode(trim(decrypt($auth['email']))));

			break;

		}

		// Check to see if the auth record is active
		if ($auth['active'] == 0){  // Deactivated
//			header('Location: /login-failed');
//echo $encrypted_username;die();
			header('Location: /?page=11-00-00-001&task=login&status=account-closed');
			break;
		}

		// All clear - test for a password match
		if ($auth['password'] !== $encrypted_password){
//			header('Location: /login-failed');
			header('Location: /?page=11-00-00-001&task=login&status=login-failed');
			break;
		}

		// Make sure they validated their account
// We decided to just let them log in anyway...
//		if ($auth['email_validated_timestamp'] <= 0){
//			$_SESSION['email'] = trim(decrypt($auth['email']));
//			header('Location: /login-not-validated');
//			break;
//		}

		// Make sure their account is not closed (grab everything else that's needed while you're at it!)
		$query = "
			SELECT name_first, name_last, joined_timestamp, last_login, email, closed_acct
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

		if ($member['closed_acct'] == 1){  // CLOSED!
//			header('Location: /login-failed');
			header('Location: /?page=11-00-00-001&task=login&status=login-failed');
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
/* Already got this when checking for closed acct above...
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
*/
		//Assign more session variables
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

		//Look up their subscription
		/*$query = "
			SELECT *
			FROM ".$_SESSION['subscriptions_table']."
			WHERE member_id = :member_id
			AND active = 1
			ORDER BY product_id, start_timestamp DESC
		";
		try {
			$rsSubs = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $_SESSION['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			//die($preparedQuery);
			$rsSubs->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}

		// Loop through them and store off product id as array key, then if the next product id is the same, continue until you get a new product id
		$_SESSION['subscriptions'] = array();
		$aProductIDs = array();
		$lastProd = -1;
		$trackRecords = 0;

		while($subs = $rsSubs->fetch(PDO::FETCH_ASSOC)){

			$product_id = $subs["product_id"];

			// If we just did this product, skip it (results in getting only the latest record for this product)
			if($product_id == $lastProd){
				continue;
			}

			// Store for comparison
			$lastProd = $product_id;

			//Look up product information
			$query = "
				SELECT *
				FROM ".$_SESSION['products_table']."
				WHERE product_id = :product_id
			";
			//echo $query;
			try {
				$rsProduct = $mLink->prepare($query);
				$aValues = array(
					':product_id'	=> $product_id
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				//die($preparedQuery);
				$rsProduct->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$product = $rsProduct->fetch(PDO::FETCH_ASSOC);

			//Assign subscription session variables
			$_SESSION['subscriptions'][$product_id] = array( 'product_name'			=> $product['product_name'],
															 'product_type'			=> $product['product_name'],
															 'product_parent_id'	=> $product['product_parent_id'],
															 'max_quantity'			=> $product['max_quantity'],
															 'level_flag'			=> $product['level_flag'],
															 'monthly_price'		=> $product['monthly_price'],
															 'annual_price'			=> $product['annual_price'],
															 'track_records'		=> $product['track_records'],
															 'sub_begin_date'		=> $subs['start_timestamp'],
															 'bill_frequency'		=> $subs['bill_frequency'],
															 'next_bill_date'		=> $subs['next_bill_timestamp'],
															 'discounts'			=> $subs['discounts']
														);
			// Sum all their subscribed quantity of track records
			$trackRecords += $product['track_records'];
		}

		// Store off the maximum number of funds this member is allowed
		$_SESSION['max_funds'] = $trackRecords;*/

		subscription($mLink, $_SESSION['member_id'], true, false);

		if($_SESSION['subscription']['membershipLevelID'] == '99' && $_SESSION['subscription']['subStartDate'] == NULL){

			$query = "
				UPDATE ".$_SESSION['subscriptions_table']."
				SET start_timestamp=UNIX_TIMESTAMP()
				WHERE member_id=:member_id AND active='1'
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
		}

		#check to see if the basic member
		if($_SESSION['subscription']['membershipLevelID'] == '1' && $_SESSION['subscription']['maxFunds'] == '1'){

			$aFunds = $_SESSION['subscription']['funds'];

			$fundCount	= count($aFunds);

			if($fundCount == 1){

				foreach($aFunds as $fundID=>$fund_symbol){

					$aFundInfo = get_funds($mLink, $fundID, 'fundInfo');

					$inceptionDate	= $aFundInfo['unix_date'];
					$fundExpDate	= $aFundInfo['fund_experation'];

					if($fundExpDate == NULL){

						$timeDiff		= time() - $inceptionDate;

						#check to see if fund is over one year old
						if($timeDiff > 31536000){
							$experationDate	= strtotime('+30 day', time());
						}else{
							$experationDate	= strtotime('+395 day', $inceptionDate);
						}

						$query = "
							UPDATE ".$_SESSION['fund_table']."
							SET fund_experation=:fund_exp
							WHERE fund_id=:fund_id
						";
						try{
							$rsUpdate = $mLink->prepare($query);
							$aValues = array(
								':fund_id'	=> $fundID,
								':fund_exp'	=> $experationDate
							);
							$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
							$rsUpdate->execute($aValues);
						}
						catch(PDOException $error){
							// Log any error
							file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
						}

						$query = "
							UPDATE members_subscriptions
							SET fund_id=:fundID
							WHERE member_id=:member_id AND product_id='1' AND active='1'
						";


						try{
							$rsUpdate = $mLink->prepare($query);
							$aValues = array(
								':fundID'		=> $fundID,
								':member_id'	=> $_SESSION['member_id']
							);
							$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
							$rsUpdate->execute($aValues);
						}
						catch(PDOException $error){
							// Log any error
							file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
						}

						//$_SESSION['debug']['login']['erropr'] = $error;
						//$_SESSION['debug']['login']['query'] = $preparedQuery;
						//$_SESSION['debug']['test'] = 'test';

					}#fundExpDate NULL Check

				}#foreach $aFunds

			}#if fundCnt ==1

		}#if basic member with one fund

		//Make sure user has a members settings table
		if($_SESSION['flag_member'] == '1'){
			$query = "
				SELECT *
				FROM ".$_SESSION['members_settings_table']."
				WHERE member_id=:member_id
			";
			try {
				$rsSettings = $mLink->prepare($query);
				$aValues = array(
					':member_id' => $_SESSION['member_id']
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				//die($preparedQuery);
				$rsSettings->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$settingsRow = $rsSettings->rowCount();

			if($settingsRow <= 0){
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
						':member_id'	=> $auth['member_id'],
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
			}
		}


		// Get all the fund IDs for this member
		$query = "
			SELECT fund_id, fund_symbol, inception_date, unix_date
			FROM ".$_SESSION['fund_table']."
			WHERE member_id = :member_id
			AND active = 1
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

		// START | Calculate Stratification
		foreach ($aFunds as $fund_id => $fund_symbol){
			exec('/usr/bin/php /var/www/html/portfolio.marketocracy.com/scripts/strat-build.php "fundID='.$fund_id.'" > /dev/null &');
		}
		// END | Calculate Stratification

		// START | Get livePrice
//		if (isMarketOpen(time(), $mLink, "none")){ // Do this only if the markets are open
// Do it every time now...
			#get live price data
			foreach ($aFunds as $fund_id => $fund_symbol){
				/*$query = "livePrice|".$_SESSION['username']."|".$fund_id."|".$fund_symbol;

				#Set the port number for the API call
				if ($port == $endPort){
					$port = $startPort;
				}else{
					$port++;
				}

				#Include the API call
				include("../includes/data-query-legacy.php");*/

				$aMethodVars[] = array(
					'method'		=> 'livePrice',
					'source'		=> 'Login Process | process-auth.php.php | case: login',
					'api'			=> '1',
					'username'		=> $_SESSION['username'],
					'fund_id'		=> $fund_id,
					'fund_symbol'	=> $fund_symbol,
					'group'			=> $_SESSION['member_id'].' - Login Process - '.date('Y-m-d h:i')
				);



			}
//		}
		// END | Get livePrice


		$mlaResults = legacy_api($mLink, $aMethodVars, true);

		$_SESSION['show-login-error'][] = $mlaResults;

		if($_SESSION['username'] == 'bmccarthy'){

			$recordCheck = checkRecords($mLink, $_SESSION['member_id'], $method='all');

			$_SESSION['show-login-error']['recordCheck'] = $recordCheck;
		}



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

		// Write last login to member record
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

		// Now write last login to auth record
		$query = "
			UPDATE ".$_SESSION['auth_table']."
			SET last_login = UNIX_TIMESTAMP()
			WHERE member_id = :member_id
			AND timestamp = :auth_timestamp
		";
		try {
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':member_id'		=> $_SESSION['member_id'],
				':auth_timestamp'	=> $auth['timestamp']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			//die($preparedQuery);
			$rsUpdate->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}

		// And then write last login to subscription record (whew!) and remove the inactive timestamp if one was set (they're active again!).
		$query = "
			UPDATE ".$_SESSION['subscriptions_table']."
			SET last_login			= UNIX_TIMESTAMP(),
				inactive_timestamp	= NULL
			WHERE member_id = :member_id
			AND active = 1
		";
		try {
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':member_id'		=> $_SESSION['member_id']
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
//			$redirect = urlencode(htmlspecialchars($_REQUEST['redirect']));
//			$redirect = htmlentities($_REQUEST['redirect'], ENT_QUOTES | ENT_HTML5, "UTF-8");
//echo $_REQUEST['redirect']; die();
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
//			$redirect = "setup-wizard";
			$redirect = "?page=10-00-00-001";
		}
//echo "X"; die();

		// Send them to the dashboard, or wherever it says to
		header('Location: /'.$redirect.'');
 		break;


	case "resend-welcome-email":

		//Load encryption functions
		require_once("../../secure/crypto.php");

		$auth_string = encrypt($_REQUEST['email']);
		$token = json_encode($auth_string);


		//START SEND VERIFICATION EMAIL
//		$tokenLink = $_SESSION['site_root'].'validate-email/'.$token.'';
		$tokenLink = $_SESSION['site_root'].'process/system-email-validate.php?cargo='.$token.'';

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
//		header('Location: /welcome-email-resent');
		header('Location: /?page=11-00-00-001&task=login&status=welcome-email-resent');
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
			WHERE username = :encrypted_username AND email = :encrypted_email AND member_id<>'0'
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
//			$aEmailVars = array(
//				'password_link'	=> $_SESSION['site_root'].'reset-password/'.$token,
//				'ip_address'	=> $_SERVER['REMOTE_ADDR']
//			);
			$aEmailVars = array(
				'password_link'	=> $_SESSION['site_root'].'?page=11-00-00-002&cargo='.$token,
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
//			$alertMessage = 'password-email-sent';
			$alertMessage = '/?page=11-00-00-001&task=login&status=email-sent';
		}else{
//			$alertMessage = 'password-email-failed';
			$alertMessage = '/?page=11-00-00-001&task=login&status=password-email-failed';
		}
		header('Location: '.$alertMessage.'');
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
//		header('Location: /reset-succeeded');
		header('Location: /?page=11-00-00-001&task=login&status=reset-succeeded');
		break;

} //End switch

?>
