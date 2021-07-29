<?php
/*
Marketocracy Inc. | Beta Development
User Payment Processes

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/user-settings-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/user-settings.js.php
	HTML		- includes/pages/user-profile.php


//-------------------------------Code Examples---------------------------

//alert boxes | Note: you do NOT have to use the <h3> and <p> tags
#danger = red
<div class="alert alert-danger">
	<h3>Error!</h3>
	<p>-content-</p>
</div>

#success = green
<div class="alert alert-success">
	<h3>Success!</h3>
	<p>-content-</p>
</div>

#info = blue
<div class="alert alert-info">
	<h3>Info!</h3>
	<p>-content-</p>
</div>

#warning = yellow
<div class="alert alert-warning">
	<h3>Warning!</h3>
	<p>-content-</p>
</div>


*/

//Start Session
session_start();

// Load debug & error logging functions
//require_once("../../includes/system-debug-functions.php");

// Connect to DB
//require_once("../../../secure/dbconnect.php");

// Load Crypto Functions
//require_once("../../../secure/crypto.php");

// Load Authorize Functions
//require_once("../../../secure/authorize/authorize_cim_lib.php");
//require_once("../../../secure/authorize/authorizenet.cim.class.php");

// Load System Wide Functions
//require_once("../../includes/system-functions.php");


// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

// Load Crypto Functions
require_once("../../../secure/crypto.php");

// Load Authorize Functions
require_once('../../../secure/authorize/AuthorizeNetRequest.php');
require_once('../../../secure/authorize/AuthorizeNetTypes.php');
require_once('../../../secure/authorize/AuthorizeNetXMLResponse.php');
require_once('../../../secure/authorize/AuthorizeNetResponse.php');

require_once('../../../secure/authorize/authorizenet.aim.class.php');
require_once('../../../secure/authorize/authorize_aim_lib.php');

require_once("../../../secure/authorize/authorizenet.cim.class.php");
require_once("../../../secure/authorize/authorize_cim_lib.php");

// Load System Wide Functions
require_once("../../includes/system-functions.php");

//Get Process from URL
$process = $_REQUEST['process'];


//+---------------------------------------------------------------------------------------------------------+
//|								Add New Payment Method  - PROCESS 6											|
//+---------------------------------------------------------------------------------------------------------+
if($process == 'new-payment-method'){
	
	//get vars
	$cardNumber		= $_REQUEST['cc_number'];
	$cardName 		= $_REQUEST['cardName'];
	$CCV			= $_REQUEST['CCV'];
	$expMonth		= $_REQUEST['exp_month'];
	$expYear		= $_REQUEST['exp_year'];
		
	$firstName		= $_REQUEST['new_billTo_firstName'];
	$lastName		= $_REQUEST['new_billTo_lastName'];
	$address1		= $_REQUEST['new_billTo_address1'];
	$address2		= $_REQUEST['new_billTo_address2'];
	$city			= $_REQUEST['new_billTo_city'];
	$state			= $_REQUEST['new_billTo_state'];
	$zip			= $_REQUEST['new_billTo_zip'];
	$country		= $_REQUEST['new_billTo_country'];
	$phone			= $_REQUEST['new_billTo_phone'];
	$email			= $_REQUEST['new_billTo_email'];

	$defaultMethod	= $_REQUEST['default_method'];

	// Create expiration timestamp
	$cardExpiration = mktime(6,0,0,$expMonth,1,$expYear);

	if($defaultMethod != '1'){
		$defaultMethod = '0';
	}

	//initialize error array
	$aErrors		= array();
	
	/*if(empty($cardNumber)){
		$aErrors[] = 'You must provide a Card Number.';	
	}*/
	
	//Start error checking (to display an error simply add it to the $aErrors array (IE $aError[] = 'error message'))
	if($expYear < date('Y')){
		$aErrors[] = 'You have tried to enter an Expiration Date that is already expired. Please check the expiration date and try again.';
	}elseif($expYear == date('Y')){
		if($expMonth < date('m')){
			$aErrors[] = 'You have tried to enter an Expiration Date that is already expired. Please check the expiration date and try again.';
		}
	}

	if(empty($cardName)){
		$aErrors[] = 'Please provide the Name as it appears on your credit card.';
	}

	if(empty($CCV)){
		$aErrors[] = 'Please enter the CCV code. This can be found on the back of most cards. If you have an American Express, this code will be located on the front of the card above the Credit Card number.';
	}

	if(empty($firstName)){
		$aErrors[] = 'Please enter the First Name for the billing address of the card.';
	}

	if(empty($lastName)){
		$aErrors[] = 'Please enter the Last Name for the billing address of the card.';
	}

	if(empty($address1)){
		$aErrors[] = 'Please enter the billing Street Address.';
	}

	if(empty($city)){
		$aErrors[] = 'Please enter the City of the billing address.';
	}

	if(empty($state)){
		$aErrors[] = 'Please enter the State/Province/Region of the billing address.';
	}

	if(empty($zip)){
		$aErrors[] = 'Please enter the Zip/Postal Code of the billing address.';
	}
	
	if(empty($email)){
		$aErrors[] = 'Please provide an email that will be associated with this billing record. This email will be used to send receipts.';	
	}
	
	if(empty($phone)){
		$aErrors[] = 'Please provide the phone number associated with this billing record.';	
	}

	//START CC VALIDATION
	#Clean up the card number and get the last four, first number, and number length
	$cleanCardNumber	= preg_replace("/[^0-9]/","",$cardNumber);
	$cardLastFour 		= substr($cleanCardNumber, -4);
	$cardFirstOne 		= substr($cleanCardNumber, 0, 1);
	$numberLength		= strlen($cleanCardNumber);


	// Determine the card type
	switch($cardFirstOne){

		//AMERICAN EXPRESS
		case '3':
			if (substr($cleanCardNumber, 0, 2) == "34" || substr($cleanCardNumber, 0, 2) == "37"){

				#Validate number length
				if($numberLength == 15){

					#Checksum validation
					if(cc_checksum($cleanCardNumber) == true){

						#set card type
						$cardType = "American Express";
					}else{

						#card did not pass checksum, flag as an error
						$aErrors[] = 'Invalid Card Number, please check your card number and try again';
					}

				}else{

					#card length did not match required length for American Express, flag as an error
					$aErrors[] = 'Invalid Card Number Length';
				}

			}elseif (substr($cleanCardNumber, 0, 2) == "30" || substr($cleanCardNumber, 0, 2) == "36" || substr($cleanCardNumber, 0, 2) == "38"){

				#set card type
				$cardType = "Diner's Club/Carte Blanche";

				#flag as an error - we don't take those
				$aErrors[] = 'We do not accept the card type you entered. Please use another card.';
			}else{

				#flag as an error - invalid card (or JCB)
				$aErrors[] = 'Invalid Card, please try another.';
			}
		break;

		//VISA
		case '4':

			#Validate number length
			if($numberLength == 16 || $numberLength == 13){

				#Checksum validation
				if(cc_checksum($cleanCardNumber) == true){

					#card passed checksum, set card type
					$cardType = "Visa";
				}else{

					#card failed checksum, flag as an error
					$aErrors[] = 'Invalid Card Number, please check your card number and try again';
				}

			}else{

				#card length did not match required length for Visa, flag as an error
				$aErrors[] = 'Invalid Card Number Length';
			}

		break;

		//MASTERCARD
		case '5':
			if ((int)substr($cleanCardNumber, 0, 2) > 50 && (int)substr($cleanCardNumber, 0, 2) <= 55){

				#Validate number length
				if($numberLength == 16){

					#Checksum validation
					if(cc_checksum($cleanCardNumber) == true){

						#card passed checksum, set card type
						$cardType = "Mastercard";
					}else{

						#card failed checksum, flag as an error
						$aErrors[] = 'Invalid Card Number, please check your card number and try again';
					}

				}else{

					#card length did not match required length for Mastercard, flag as an error
					$aErrors[] = 'Invalid Card Number Length';
				}

			}else{
				#flag as an error - invalid card
				$aErrors[] = 'Invalid Card, please try another.';
			}
		break;

		//DISCOVER
		case '6':
			if (((int)substr($cleanCardNumber, 0, 8) >= 60110000 && (int)substr($cleanCardNumber, 0, 8) <= 60119999) ||
				((int)substr($cleanCardNumber, 0, 8) >= 65000000 && (int)substr($cleanCardNumber, 0, 8) <= 65999999) ||
				((int)substr($cleanCardNumber, 0, 8) >= 62212600 && (int)substr($cleanCardNumber, 0, 8) <= 62292599)){

				#Validate number length
				if($numberLength == 16){

					#Checksum validation
					if(cc_checksum($cleanCardNumber) == true){

						#card passed checksum, set card type
						$cardType = "Discover";
					}else{

						#card failed checksum, flag as an error
						$aErrors[] = 'Invalid Card Number, please check your card number and try again';
					}

				}else{

					#card length did not match required length for Discover, flag as an error
					$aErrors[] = 'Invalid Card Number Length';
				}

			}else{
				#flag as an error - invalid card
				$aErrors[] = 'Invalid Card, please try another.';
			}
		break;

		default:
			#card did not match any accepted profile, flag as an error - invalid card
			$aErrors[] = 'Invalid Card, please try another.';
		break;
	}
	

	
	//+-------------------------------------------------------------------------------------------------------------------------+
	//| ERROR CHECK ONE: make sure fields are submitted correctly																|
	//|																															|
	//| Wrap all this inside of a check for empty($aErrors) - no sense in going to authorize if we already have a problem..... 	|
	//+-------------------------------------------------------------------------------------------------------------------------+
	if (empty($aErrors)){

		//START AUTHORIZE INSERT AND VALIDATION

		// Are we in test mode?
		if ($_SESSION['authorize_test_mode'] == 1){ // In test mode
			$apiLogin = trim(decrypt($_SESSION['sandbox_api_login_id']));
			$transKey = trim(decrypt($_SESSION['sandbox_transaction_key']));
			$testMode = true;
		}else{  // Live mode
			$apiLogin = trim(decrypt($_SESSION['authorize_api_login_id']));
			$transKey = trim(decrypt($_SESSION['authorize_transaction_key']));
			$testMode = false;
		}

//$aErrors[] = $_SESSION['authorize_test_mode'];
//$aErrors[] = $apiLogin;
//$aErrors[] = $transKey;

// Test mode
//$apiLogin = "2cw2pN6NAe";			sandbox_api_login_id	authorize_api_login_id
//$transKey = "4w4w7xB3MvL35VCC";		sandbox_transaction_key	authorize_transaction_key
//$testMode = true;
		$amount = (rand(1, 100) / 100); // Random value between .01 and 1.00
		$expirationDate = $expMonth.substr($expYear, -2); // (e.g. 0116)

		// Check card for validity
		$approved = authorizeValidateCard(
			$apiLogin,
			$transKey,
			$testMode,
			$amount,
			$cardNumber,
			$expirationDate
		);

		// Card failed
		if ($approved == false){
			$aErrors[] = "Unable to Verify Card";
		}
		
		//END AUTHORIZE INSERT AND VALIDATION
		
		//+----------------------------------------------------------------+
		//| ERROR CHECK TWO: make sure authorize validation was successful
		//+----------------------------------------------------------------+
		if(empty($aErrors)){
			
			// See if the member has a merchant account record.  If they do, while there grab their profile id.
			$query = "
				SELECT customerProfileID
				FROM ".$_SESSION['customer_profiles_table']."
				WHERE member_id = :member_id
			";
			try {
				$rsCustomer = $mLink->prepare($query);
				$aValues = array(
					':member_id'	=> $_REQUEST['member_id']
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				//die($preparedQuery);
				$rsCustomer->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$aErrors[] = $error;

			// If no results then they don't have a merchant record, create it.
			if ($rsCustomer->rowCount() < 1){

				// Define values
				$authMemberID = $_SESSION['member_id'];
				$authFirstName = $firstName;
				$authLastName = $lastName;
				$authAddress = $address1." ".$address2;
				$authCity = $city;
				$authState = $state;
				$authZip = $zip;
				$authCountry = $country;
				$authPhone = $phone;
				$authEmail = $email;
				$authCardNumber = $cardNumber;
				$authExpirationDate = $expYear."-".$expMonth;

				// Crete the account at Authorize
				$cim = authorizeCreateAccount(
					$apiLogin,
					$transKey,
					$testMode,
					$authMemberID,
					$authFirstName,
					$authLastName,
					$authAddress,
					$authCity,
					$authState,
					$authZip,
					$authCountry,
					$authPhone,
					$authEmail,
					$authCardNumber,
					$authExpirationDate
				);

				// Parse results
				$resultCode = $cim->resultCode;  //"Ok" if all is well
				$code = $cim->code;  //The Authorize response code
				$text = $cim->text;  //"Successful." if all goes well
				$customerProfileId = $cim->customerProfileId;  //The customer ID for this new member
				$customerPaymentProfileId = $cim->customerPaymentProfileId;  // The payment profile ID for the credit card entered
				$customerAddressId = $cim->customerAddressId;  //The shipping address profile ID (required but unused by us (yet))

				// Did it fail?
				if (strtoupper($resultCode) != "OK"){

					// Add to error array
					$aErrors[] = "Transaction failed with the following error: ".$code." - ".$text;

					// Log the error
					$query = "
						INSERT INTO ".$_SESSION['transaction_errors_table']."(
							timestamp,
							member_id,
							task,
							result_code,
							response_code,
							response_text
						) VALUES (
							UNIX_TIMESTAMP(),
							:member_id,
							:task,
							:result_code,
							:response_code,
							:response_text
						)
					";
					try{
						$rsLogError = $mLink->prepare($query);
						$aValues = array(
							':member_id'	=> $_SESSION["member_id"],
							':task'			=> "authorizeCreateAccount",
							':result_code'	=> $resultCode,
							':response_code'=> $code,
							':response_text'=> $text
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsLogError->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}

				}else{ // Successful!

					// Write customer profile
					$query = "
						INSERT INTO ".$_SESSION['customer_profiles_table']."(
							member_id,
							customerProfileID,
							receiptEmail,
							created_timestamp
						) VALUES (
							:member_id,
							:customerProfileID,
							:receiptEmail,
							UNIX_TIMESTAMP()
						)
					";
					try{
						$rsInsert = $mLink->prepare($query);
						$aValues = array(
							':member_id'		=> $_SESSION["member_id"],
							':customerProfileID'=> $customerProfileId,
							':receiptEmail'		=> $authEmail						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						//$aErrors[] = $preparedQuery;
						$rsInsert->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					$aErrors[] = $error;

					// Write shipping profile (required by Authorize, even though we don't ship anything)
					$query = "
						INSERT INTO ".$_SESSION['shipping_profiles_table']."(
							member_id,
							customerProfileID,
							customerAddressID,
							shipTo_firstName,
							shipTo_lastName,
							shipTo_address,
							shipTo_city,
							shipTo_state,
							shipTo_zip,
							shipTo_country,
							shipTo_phoneNumber,
							created_timestamp,
							active
						) VALUES (
							:member_id,
							:customerProfileID,
							:customerAddressID,
							:shipTo_firstName,
							:shipTo_lastName,
							:shipTo_address,
							:shipTo_city,
							:shipTo_state,
							:shipTo_zip,
							:shipTo_country,
							:shipTo_phoneNumber,
							UNIX_TIMESTAMP(),
							:active
						)
					";
					try{
						$rsInsert = $mLink->prepare($query);
						$aValues = array(
							':member_id'		=> $_SESSION["member_id"],
							':customerProfileID'=> $customerProfileId,
							':customerAddressID'=> $customerAddressId,
							':shipTo_firstName'	=> $authFirstName,
							':shipTo_lastName'	=> $authLastName,
							':shipTo_address'	=> $authAddress,
							':shipTo_city'		=> $authCity,
							':shipTo_state'		=> $authState,
							':shipTo_zip'		=> $authZip,
							':shipTo_country'	=> $authCountry,
							':shipTo_phoneNumber'=> $authPhone,
							':active'			=> 1
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						//$aErrors[] = $preparedQuery;
						$rsInsert->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					$aErrors[] = $error;

					// Write initial payment profile
					$query = "
						INSERT INTO ".$_SESSION['payment_profiles_table']."(
							member_id,
							customerProfileID,
							customerPaymentProfileID,
							default_method,
							billTo_firstName,
							billTo_lastName,
							billTo_address,
							billTo_city,
							billTo_state,
							billTo_zip,
							billTo_country,
							billTo_phone,
							billTo_email,
							cardType,
							cardLastFour,
							cardExpiration,
							cardName,
							CCV,
							created_timestamp,
							active
						) VALUES (
							:member_id,
							:customerProfileID,
							:customerPaymentProfileID,
							:default_method,
							:billTo_firstName,
							:billTo_lastName,
							:billTo_address,
							:billTo_city,
							:billTo_state,
							:billTo_zip,
							:billTo_country,
							:billTo_phoneNumber,
							:billTo_email,
							:cardType,
							:cardLastFour,
							:cardExpiration,
							:cardName,
							:CCV,
							UNIX_TIMESTAMP(),
							:active
						)
					";
					try{
						$rsInsert = $mLink->prepare($query);
						$aValues = array(
							':member_id'				=> $_SESSION["member_id"],
							':customerProfileID'		=> $customerProfileId,
							':customerPaymentProfileID'	=> $customerAddressId,
							':default_method'			=> 1,
							':billTo_firstName'			=> $authFirstName,
							':billTo_lastName'			=> $authLastName,
							':billTo_address'			=> $authAddress,
							':billTo_city'				=> $authCity,
							':billTo_state'				=> $authState,
							':billTo_zip'				=> $authZip,
							':billTo_country'			=> $authCountry,
							':billTo_phoneNumber'		=> $authPhone,
							':billTo_email'				=> $authEmail,
							':cardType'	   				=> $cardType,
							':cardLastFour'				=> $cardLastFour,
							':cardExpiration'			=> $cardExpiration,
							':cardName'					=> $cardName,
							':CCV'	 					=> double_encrypt($CCV),
							':active'					=> 1
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						//$aErrors[] = $preparedQuery;
						$rsInsert->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					$aErrors[] = $error;

//$aErrors[] = "customerProfileId = ".$customerProfileId;
//$aErrors[] = "customerPaymentProfileId = ".$customerPaymentProfileId;
//$aErrors[] = "customerAddressId = ".$customerAddressId;

				}

			}else{ // Already a customer, just add payment method

				// Define values
				$customer = $rsCustomer->fetch(PDO::FETCH_ASSOC);
				$customerProfileID = $customer['customerProfileID'];

//			$authMemberID = $_SESSION['member_id'];
				$authFirstName = $firstName;
				$authLastName = $lastName;
				$authAddress = $address1." ".$address2;
				$authCity = $city;
				$authState = $state;
				$authZip = $zip;
				$authCountry = $country;
//			$authPhone = $phone;
//			$authEmail = $email;
				$authCardNumber = $cardNumber;
				$authExpirationDate = $expYear."-".$expMonth;

				// Crete the account at Authorize
				$cim = authorizeCreatePaymentProfile(
					$apiLogin,
					$transKey,
					$testMode,
					$customerProfileID,
					$authFirstName,
					$authLastName,
					$authAddress,
					$authCity,
					$authState,
					$authZip,
					$authCountry,
					$authCardNumber,
					$authExpirationDate
				);

				// Parse results
				$resultCode = $cim->resultCode;  //"Ok" if all is well
				$code = $cim->code;  //The Authorize response code
				$text = $cim->text;  //"Successful." if all goes well
//				$customerProfileId = $cim->customerProfileId;  //The customer ID for this new member
				$customerPaymentProfileId = $cim->customerPaymentProfileId;  // The payment profile ID for the credit card entered
//				$customerAddressId = $cim->customerAddressId;  //The shipping address profile ID (required but unused by us (yet))

				// Did it fail?
				if (strtoupper($resultCode) != "OK"){

					// Add to error array
					$aErrors[] = "Transaction failed with the following error: ".$code." - ".$text;

					// Log the error
					$query = "
						INSERT INTO ".$_SESSION['transaction_errors_table']."(
							timestamp,
							member_id,
							task,
							result_code,
							response_code,
							response_text
						) VALUES (
							UNIX_TIMESTAMP(),
							:member_id,
							:task,
							:result_code,
							:response_code,
							:response_text
						)
					";
					try{
						$rsLogError = $mLink->prepare($query);
						$aValues = array(
							':member_id'	=> $_SESSION["member_id"],
							':task'			=> "authorizeCreateAccount",
							':result_code'	=> $resultCode,
							':response_code'=> $code,
							':response_text'=> $text
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsLogError->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}

				}else{ // Successful!

					//Check to see if default method is checked, if so, zero out the rest of the payment profiles
					if($defaultMethod == '1'){
						$query = "
							UPDATE ".$_SESSION['payment_profiles_table']."
							SET default_method = '0'
							WHERE member_id = :member_id
						";
						try{
							$rsUpdateDefault = $mLink->prepare($query);
							$aValues = array(
								':member_id' => $_SESSION["member_id"]
							);
							$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
							$rsUpdateDefault->execute($aValues);
						}
						catch(PDOException $error){
							// Log any error
							file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
						}
						$aErrors[] = $error;
					}

					// Write new payment profile
					$query = "
						INSERT INTO ".$_SESSION['payment_profiles_table']."(
							member_id,
							customerProfileID,
							customerPaymentProfileID,
							default_method,
							billTo_firstName,
							billTo_lastName,
							billTo_address,
							billTo_city,
							billTo_state,
							billTo_zip,
							billTo_country,
							billTo_phone,
							billTo_email,
							cardType,
							cardLastFour,
							cardExpiration,
							cardName,
							CCV,
							created_timestamp,
							active
						) VALUES (
							:member_id,
							:customerProfileID,
							:customerPaymentProfileID,
							:default_method,
							:billTo_firstName,
							:billTo_lastName,
							:billTo_address,
							:billTo_city,
							:billTo_state,
							:billTo_zip,
							:billTo_country,
							:billTo_phoneNumber,
							:billTo_email,
							:cardType,
							:cardLastFour,
							:cardExpiration,
							:cardName,
							:CCV,
							UNIX_TIMESTAMP(),
							:active
						)
					";
					try{
						$rsInsert = $mLink->prepare($query);
						$aValues = array(
							':member_id'				=> $_SESSION["member_id"],
							':customerProfileID'		=> $customerProfileId,
							':customerPaymentProfileID'	=> $customerAddressId,
							':default_method'			=> 1,
							':billTo_firstName'			=> $authFirstName,
							':billTo_lastName'			=> $authLastName,
							':billTo_address'			=> $authAddress,
							':billTo_city'				=> $authCity,
							':billTo_state'				=> $authState,
							':billTo_zip'				=> $authZip,
							':billTo_country'			=> $authCountry,
							':billTo_phoneNumber'		=> $authPhone,
							':billTo_email'				=> $authEmail,
							':cardType'	   				=> $cardType,
							':cardLastFour'				=> $cardLastFour,
							':cardExpiration'			=> $cardExpiration,
							':cardName'					=> $cardName,
							':CCV'	 					=> double_encrypt($CCV),
							':active'					=> 1
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						//$aErrors[] = $preparedQuery;
						$rsInsert->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					$aErrors[] = $error;
				}
			}
			


		}else{
			//+----------------------------------------------------------------+
			//| ERROR CHECK TWO: Display
			//+----------------------------------------------------------------+
			echo '<div class="alert alert-danger"><h4>Add Payment Method Error</h4><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';
			}
			echo '</ul></div>';
		}

	}else{
		//+----------------------------------------------------------------+
		//| ERROR CHECK ONE: Display
		//+----------------------------------------------------------------+
		echo '<div class="alert alert-danger"><h4>Add Payment Method Error</h4><ul>';
		foreach($aErrors as $key=>$error){
			echo '<li>'.$error.'</li>';
		}
		echo '</ul></div>';
	}
}

//+---------------------------------------------------------------------------------------------------------+
//|								Edit Payment Method - PROCESS 1												|
//+---------------------------------------------------------------------------------------------------------+
if($process == 'edit-payment-method'){

	$uid			= $_REQUEST['uid'];

	$cardName 		= $_REQUEST['card_name'];
	$expMonth		= $_REQUEST['exp_month'];
	$expYear		= $_REQUEST['exp_year'];
	$newCCV			= $_REQUEST['new_ccv'];
	$changeAddress	= $_REQUEST['save_new_address'];

	$firstName		= $_REQUEST['new_billTo_firstName'];
	$lastName		= $_REQUEST['new_billTo_lastName'];
	$address1		= $_REQUEST['new_billTo_address1'];
	$address2		= $_REQUEST['new_billTo_address2'];
	$city			= $_REQUEST['new_billTo_city'];
	$state			= $_REQUEST['new_billTo_state'];
	$zip			= $_REQUEST['new_billTo_zip'];
	$country		= $_REQUEST['new_billTo_country'];
	$phone			= $_REQUEST['new_billTo_phone'];
	$email			= $_REQUEST['new_billTo_email'];
	
	$aErrors		= array();

	//comment this line out to allow processing
	//$aErrors[] = 'This is a test error, please remove me from the process file to continue processing. Line 84 user-payment-processes.php';

	$expirationDate = mktime(6,0,0,$expMonth,1,$expYear);

	//$aErrors[] = $expirationDate;

	//Check to see if user is updating their address, if so error check
	if($changeAddress == '1'){
		if(empty($firstName)){
			$aErrors[] = 'Please provide your Billing First Name';
		}

		if(empty($lastName)){
			$aErrors[] = 'Please provide your Billing Last Name';
		}

		if(empty($address1)){
			$aErrors[] = 'Please provide your Billing Address';
		}

		if(empty($city)){
			$aErrors[] = 'Please provide your Billing City';
		}

		if(empty($state)){
			$aErrors[] = 'Please provide your Billing State/Province/Region';
		}

		if(empty($zip)){
			$aErrors[] = 'Please provide your Billing Zip/Postal Code';
		}
		
		if(empty($email)){
			$aErrors[] = 'Please provide an email that will be associated with this billing record. This email will be used to send receipts.';	
		}
		
		if(empty($phone)){
			$aErrors[] = 'Please provide the phone number associated with this billing record.';	
		}
	}

	//Error check the rest of the fields
	if(empty($cardName)){
		$aErrors[] = 'Please provide the name that is on the front of your Credit/Debit card.';
	}
	
	//+------------------------------------------------------------------+
	//|						START UPDATE AUTHORIZE CIM
	//+------------------------------------------------------------------+

	//+------------------------------------------------------------------+
	//|						END UPDATE AUTHORIZE CIM
	//+------------------------------------------------------------------+
	
	
	if(empty($aErrors)){
		//Finish processing

		#Update Database Record
		if($changeAddress == '1'){
			//user wants to change address

			#check to see if the ccv is empty, if empty, do not update the record
			if($newCCV != ''){
				//encrypt ccv
				$encryptedCCV = double_encrypt($newCCV);
				$addCCV = ",CCV='".$encryptedCCV."'";
			}else{
				$addCCV = '';
			}

			$query = "
				UPDATE ".$_SESSION['payment_profiles_table']."
				SET billTo_firstName=:firstname,
					billTo_lastName=:lastname,
					billTo_address=:address,
					billTo_city=:city,
					billTo_state=:state,
					billTo_zip=:zip,
					billTo_country=:country,
					billTo_phone=:phone,
					billTo_email=:email,
					cardName=:cardName,
					cardExpiration=:cardExp,
					updated_timestamp=UNIX_TIMESTAMP()
					".$addCCV."
				WHERE uid=:uid
			";
			try{
				$rsUpdatePayment = $mLink->prepare($query);
				$aValues = array(
					':uid'				=> $uid,
					':firstname'		=> $firstName,
					':lastname'			=> $lastName,
					':address'			=> $address1.($address2 != "" ? ", ".$address2 : ""),
					':city'				=> $city,
					':state'			=> $state,
					':zip'				=> $zip,
					':country'			=> $country,
					':cardName'			=> $cardName,
					':cardExp'			=> $expirationDate,
					':phone'			=> $phone,
					':email'			=> $email
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdatePayment->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$aErrors[] = $error;
		}else{
			//User is not updating their billing address

			if($newCCV != ''){
				//encrypt ccv
				$encryptedCCV = double_encrypt($newCCV);
				$addCCV = ",CCV='".$encryptedCCV."'";
			}else{
				$addCCV = '';
			}

			$query = "
				UPDATE ".$_SESSION['payment_profiles_table']."
				SET cardName=:cardName,
					cardExpiration=:cardExp,
					updated_timestamp=UNIX_TIMESTAMP()
					".$addCCV."
				WHERE uid=:uid
			";
			try{
				$rsUpdatePayment = $mLink->prepare($query);
				$aValues = array(
					':uid'				=> $uid,
					':cardName'			=> $cardName,
					':cardExp'			=> $expirationDate
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdatePayment->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}

			$aErrors[] = $error;
		}


		

		#query troubleshooting
		/*if(!empty($aErrors)){
			//Display errors
			echo '<div class="alert alert-danger"><h4>Edit Payment Error</h4><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';
			}
			echo '</ul></div>';
		}*/

	}else{
		//Display errors
		echo '<div class="alert alert-danger"><h4>Edit Payment Error</h4><ul>';
		foreach($aErrors as $key=>$error){
			echo '<li>'.$error.'</li>';
		}
		echo '</ul></div>';
	}

}

//+---------------------------------------------------------------------------------------------------------+
//|								Delete Payment Method 														|
//+---------------------------------------------------------------------------------------------------------+
if($process == 'delete-payment-method'){
	
	$uid 		= $_REQUEST['uid'];
	
	// Initialize error array
	$aErrors 	= array();
	
	if(empty($uid)){
		$aErrors[] = 'UID Not Found, please contact customer service.';	
	}

	

	if(empty($aErrors)){
		
		$query = "
			DELETE FROM merchant_payment_profiles
			WHERE uid=:uid
		";
		try{
			$rsDelete = $mLink->prepare($query);
			$aValues = array(
				':uid'	=> $uid
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsDelete->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
			
		$aErrors[] = $error;
		
		
		//second error check	Jeff, if you want to display stuff to the modal, just pass something to the $aError[] array
		
		if($aErrors[0] != ''){
			echo '<div class="alert alert-danger"><h4>Add Payment Method Error</h4><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';
			}
			echo '</ul></div>';	
		}
		
	}else{
		//Display errors
		echo '<div class="alert alert-danger"><h4>Delete Payment Mehtod Error</h4><ul>';
		foreach($aErrors as $key=>$error){
			echo '<li>'.$error.'</li>';	
		}
		echo '</ul></div>';
	}
}

//+---------------------------------------------------------------------------------------------------------+
//|								LOAD Edit Payment Modal - PROCESS 2											|
//+---------------------------------------------------------------------------------------------------------+
if($process == 'load-edit-payment-modal'){
	
	$uid		= $_REQUEST['uid'];
	
	$query = "
	 SELECT *
	 FROM ".$_SESSION['payment_profiles_table']."
	 WHERE uid=:uid
	";
	try{
		$rsGetPayments = $mLink->prepare($query);
		$aValues = array(
			'uid' => $uid
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetPayments->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}

													
	$pay = $rsGetPayments->fetch(PDO::FETCH_ASSOC);
						
	$billFirstName		= $pay['billTo_firstName'];
	$billLastName		= $pay['billTo_lastName'];
	$billAddress		= $pay['billTo_address'];
	$city				= $pay['billTo_city'];
	$state				= $pay['billTo_state'];
	$zip				= $pay['billTo_zip'];
	$country			= $pay['billTo_country'];
	$cardType			= $pay['cardType'];
	$cardLast4			= $pay['cardLastFour'];
	$cardName			= $pay['cardName'];
	$cardExpiration		= $pay['cardExpiration'];
	
	$today = time();
	
	if($cardExpiration < $today){
		//card is expired
		$cardExpire = '<span style="color:#E02222;">Expired!</span>';
		
		$expireNotice = '
			<div class="alert alert-danger">
				<h4>This card exipired on '.date('m/Y',$cardExpiration).'! Please update or remove this card.</h4>
			</div>
		';	
	}else{
		$cardExpire = date('m/Y',$cardExpiration);	
		
		$expireNotice = '';
	}
	
	
	
	switch($cardType){
		
		case 'Visa':
			$cardImg = 'visa-straight-32px.png';
		break;
		
		case 'Mastercard':
			$cardImg = 'mastercard-straight-32px.png';
		break;
		
		case 'Discover':
			$cardImg = 'discover-straight-32px.png';
		break;
		
		case 'American Express':
			$cardImg = 'american-express-straight-32px.png';
		break;
			
	}
	?>
	<div class="modal-header">
       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
       <h4 class="modal-title">Edit Payment Method</span></h4>
    </div>
    
    <form action="process/ajax/user-payment-processes.php" method="post" name="edit-method-form" class="edit-method-form">
        <div class="modal-body">
            
            <div id="edit-payment-method-error"></div>
                
            <div class="row">
                <div class="col-md-4">
                    <h5><strong>Card</strong></h5>
                    <img src="images/payments/<?php echo $cardImg;?> " alt="<?php echo $cardType;?> " width="40" height="25"/> ending in <?php echo $cardLast4;?>
                    <br /><div id="ccv-link"><a href="javascript:void(0);" onclick="showHideCCV('show');" style="font-size:12px;">Edit CCV</a></div>
                    
                    <div id="ccv" class="hide">
                    	<h5><strong>New CCV</strong></h5>
                        <input type="text" name="new_ccv" class="form-control" id="new_ccv" /><br /> <button type="button" class="btn btn-default btn-sm" onclick="showHideCCV('hide');">Cancel Edit CCV</button>
                    </div><!--ccv-->
                </div>
                
                <div class="col-md-4">
                    <h5><strong>Name on Card</strong></h5>
                    <input type="text" class="form-control" name="card_name" value="<?php echo $cardName;?>"  />
                    
                </div>
                
                <div class="col-md-3">
                    <h5><strong>Expiration Date</strong></h5>
                    
                    <div class="form-inline">
                        <div class="form-group" style="margin-bottom:5px;">
                            <select name="exp_month" class="form-control input-small">
                                <?php echo date_list($mLink, 'month', NULL, date('m',$cardExpiration));?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <select name="exp_year" class="form-control input-small">
                                <?php
                                $expYear = date('Y',$cardExpiration);
                                $year = date('Y');
                                for($i = 1; $i < 21; $i++){
                                    
                                    if($year == $expYear){
                                        $selected = 'selected';	
                                    }else{
                                        $selected = '';	
                                    }
                                    
                                    echo '<option value="'.$year.'" '.$selected.'>'.$year.'</option>';
                                    
                                    $year++;
                                }
                                
                                ?>
                            </select>
                        </div><!--form-group-->
                    </div><!--form-inline-->
                    
                </div><!--col-md-4-->
            </div><!--row-->
            
            <div class="row">
                <div class="col-md-12">
                    <h5><strong>Billing Address</strong></h5>
                    <div id="current-billing-address">
                        <p><?php echo $billFirstName.' '.$billLastName;?><br /><?php echo $billAddress;?><br /><?php echo $city;?>, <?php echo $state;?> <?php echo $zip;?>, <?php echo $country;?></p>
                        <button type="button" class="btn btn-default btn-sm" onclick="changeAddress('change');">Change</button>
                    </div>
                    
                    <div id="billing-address-area" class="hide">
                        
                        <div class="new-address-btns" style="margin-bottom:20px;">
                            <button type="button" class="btn btn-info btn-sm" onclick="changeAddress('new-address');">New Address</button> <button type="button" class="btn btn-default btn-sm" onclick="changeAddress('cancel');">Cancel</button>
                        </div><!--new-address-btns-->
                        
                       
                        <div class="row">
                            
                            <?php
							foreach($_SESSION['aBillingAddresses'] as $listUID=>$aInfo){
								
								?>
								<div class="col-md-4">
									<p><?php echo $aInfo['billName'];?><br /><?php echo $aInfo['address'];?><br /><?php echo $aInfo['city'];?>, <?php echo $aInfo['state'];?> <?php echo $aInfo['zip'];?>, <?php echo $aInfo['country'];?></p>
									<button type="button" class="btn btn-info btn-sm" onclick="useThisAddress('<?php echo $listUID;?>');">Use This Address</button>
								</div><!--col-md-4-->
								<?php	
							}
							?>
                            
                        </div><!--row-->
                            
                    </div><!--billing-address-area-->
                    
                    <div id="new-address-fields" class="hide">
                        
                        <div class="new-address-errors"></div>
                        
                        
                        
                        <div class="form-group">
                            <label class="form-label">First Name</label>
                            <input type="text" name="new_billTo_firstName" class="form-control"  />
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="new_billTo_lastName" class="form-control"  />
                        </div>
                        
                        
                        
                        <div class="form-group">
                            <label class="form-label">Country</label>
                            <select name="new_billTo_country" class="form-control" onChange="loadStates(this.value);">
                                <option value="xx">Select Country</option>
                                
                                <?php
								$aCountries = array();
								$query = "
									SELECT country_name, country_code_isa
									FROM system_countries
									ORDER BY country_name ASC
								";
					
								try{
									$rsGetCounty = $mLink->prepare($query);
									$aValues = array();
									$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
									$rsGetCounty->execute($aValues);
								}
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}									
								while($country = $rsGetCounty->fetch(PDO::FETCH_ASSOC)) {
									
									
									
									if($aBillInfo['country'] == $country['country_code_isa']){
										$selected = 'selected';	
									}else{
										$selected = '';	
									}
									echo '<option value="'.$country['country_code_isa'].'" '.$selected.'>'.$country['country_name'].'</option>';
									/*if($country['coutry_code_isa'] != 'US'){
										
							
										
									}*/
									//save for later
									$aCountries[$country['country_code_isa']] = $country['country_name'];
								}
								//save for later use
								$_SESSION['countries'] = $aCountries;
								?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">State/Province/Region</label>
                            <div id="load-states">
                            <small>Please select a country.</small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Addres Line 1</label>
                            <input type="text" name="new_billTo_address1" class="form-control"  />
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Addres Line 2</label>
                            <input type="text" name="new_billTo_address2" class="form-control"  />
                            <span class="help-block">Apt, Suite, etc</span>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">City</label>
                            <input type="text" name="new_billTo_city" class="form-control"  />
                        </div>
                        
                        <?php /*?><div class="form-group">
                            <label class="form-label">State / Provice / Region</label>
                            <input type="text" name="new_billTo_state" class="form-control"  />
                        </div><?php */?>
                        
                        <div class="form-group">
                            <label class="form-label">Zip</label>
                            <input type="text" name="new_billTo_zip" class="form-control"  />
                        </div>
                        
                        <?php /*?><div class="form-group">
                            <label class="form-label">Country</label>
                            <select name="new_billTo_country" class="form-control">
                                <option value="US">United States</option>
                                <?php
								$query = "
									SELECT country_name, country_code_isa
									FROM system_countries
								";
					
								try{
									$rsGetCounty = $mLink->prepare($query);
									$aValues = array();
									$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
									$rsGetCounty->execute($aValues);
								}
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}									
								while($country = $rsGetCounty->fetch(PDO::FETCH_ASSOC)) {
									
									if($aBillInfo['country'] == $country['country_code_isa']){
										$selected = 'selected';	
									}else{
										$selected = '';	
									}
									
									if($country['coutry_code_isa'] != 'US'){
										echo '<option value="'.$country['country_code_isa'].'" '.$selected.'>'.$country['country_name'].'</option>';
							
										
									}
									//save for later
									$aCountries[$country['country_code_isa']] = $country['country_name'];
								}
								//save for later use
								$_SESSION['countries'] = $aCountries;
								?>
                            </select>
                        </div><?php */?>
                        
                        <button type="button" class="btn btn-default btn-sm" onclick="changeAddress('cancel');">Back</button>
                                                    
                    </div><!--new-address-fields-->
                    
                    
                    
                </div><!--col-md-12-->
            </div><!--row-->
            
        </div><!--modal-body-->
        
        <div class="modal-footer">
        	<input type="hidden" value='0' name="save_new_address" id="save_new_address"  />
            <input type="hidden" value="<?php echo $uid;?>" name="uid"  />
        
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <input type="submit" value="Save" id="submit-path" class="btn btn-info"/>
            
        </div>
    
    </form><!--create-topic-->
    <?php	
}

//+---------------------------------------------------------------------------------------------------------+
//|								Use This Address  - PROCESS 3												|
//+---------------------------------------------------------------------------------------------------------+
#This populates the new billing address form.

if($process == 'use-this-address'){

	$uid				= $_REQUEST['uid'];
	$aBillingAddresses 	= $_SESSION['aBillingAddresses'];
	$aBillInfo			= $aBillingAddresses[$uid];

	?>
    <div class="new-address-errors"></div>

    <div class="form-group">
        <label class="form-label">First Name</label>
        <input type="text" name="new_billTo_firstName" class="form-control" value="<?php echo $aBillInfo['firstName'];?>" />
    </div>

    <div class="form-group">
        <label class="form-label">Last Name</label>
        <input type="text" name="new_billTo_lastName" class="form-control" value="<?php echo $aBillInfo['lastName'];?>"  />
    </div>
	
    
    
    <div class="form-group">
        <label class="form-label">Address Line 1</label>
        <input type="text" name="new_billTo_address1" class="form-control" value="<?php echo $aBillInfo['address'];?>"  />
    </div>

    <div class="form-group">
        <label class="form-label">Address Line 2</label>
        <input type="text" name="new_billTo_address2" class="form-control" value="<?php echo $aBillInfo[''];?>"  />
        <span class="help-block">Apt, Suite, etc</span>
    </div>
	
    <div class="form-group">
        <label class="form-label">Country</label>
        <select name="new_billTo_country" class="form-control"  onChange="loadStates(this.value);">
            <?php /*?><option value="US">United States</option><?php */?>
            <?php
			$aCountries = array();
			$query = "
				SELECT country_name, country_code_isa
				FROM system_countries
				ORDER BY country_name ASC
			";

			try{
				$rsGetCounty = $mLink->prepare($query);
				$aValues = array();
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetCounty->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}									
			while($country = $rsGetCounty->fetch(PDO::FETCH_ASSOC)) {
				
				
				if($aBillInfo['country'] == $country['country_code_isa']){
					$selected = 'selected';	
				}else{
					$selected = '';	
				}
				
				/*if($country['coutry_code_isa'] != 'US'){
					echo '<option value="'.$country['country_code_isa'].'" '.$selected.'>'.$country['country_name'].'</option>';
		
					
				}*/
				//save for later
				$aCountries[$country['country_code_isa']] = $country['country_name'];
			}

			foreach($aCountries as $countryCode=>$countryName){

				if($countryCode == $aBillInfo['country']){
					$selected = 'selected';
					
					$setCountry = $aBillInfo['country'];
				}else{
					$selected = '';
				}

				echo '<option value="'.$countryCode.'" '.$selected.'>'.$countryName.'</option>';
			}
            ?>
        </select>
    </div>
    
    <div class="form-group">
        <label class="form-label">State/Province/Region</label>
        <div id="load-states">
        	<?php /*?><input type="text" name="new_billTo_state" class="form-control" value="<?php echo $aBillInfo['state'];?>"  />
<?php */?>            <?php
			$countryCode = $setCountry;
			switch($countryCode){
				case 'US'	:
					
					$stateLookup	= true;
					$stateQuery		= "
						SELECT *
						FROM ".$_SESSION['states_table']."
						WHERE state_entity = 'State'
						OR state_entity = 'Territory'
						ORDER BY state_entity ASC, state_name ASC
					";	
					
					$stateType		= 'dropdown';
					$stateLabel		= 'State<span class="required">*</span>';
					$stateDropDown	= 'Select your State*';
					$stateTextField	= '';
					
					$optGroupStart	= '<optgroup label="States">';
					$optGroupEnd	= '</optgroup>';
					
					$zipLabel		= 'ZIP Code<span class="required">*</span>';
					$zipFieldLabel	= 'ZIP Code*';
					
					
				break;
				
				case 'CA'	:
					
					$stateLookup	= true;
					$stateQuery		= "
						SELECT *
						FROM ".$_SESSION['states_table']."
						WHERE state_entity = 'Province'
						ORDER BY state_name
					";
					
					$stateType		= 'dropdown';
					$stateLabel		= 'Province<span class="required">*</span>';
					$stateDropDown	= 'Select your Province*';
					$stateTextField	= '';
					
					$optGroupStart	= '<optgroup label="Provinces">';
					$optGroupEnd	= '</optgroup>';
					
					$zipLabel		= 'Postal Code<span class="required">*</span>';
					$zipFieldLabel	= 'Postal Code*';
					
				break;
					
				default:
					
					$stateLookup	= false;
					$stateQuery 	= "";
					
					$stateType		= 'text-field';
					$stateLabel		= 'State/Province/Region<span class="required">*</span>';
					$stateDropDown	= '';
					$stateTextField = 'State/Province/Region*';
					
					$optGroupStart	= '<optgroup label="Provinces">';
					$optGroupEnd	= '</optgroup>';
					
					$zipLabel		= 'ZIP/Postal Code<span class="required">*</span>';
					$zipFieldLabel	= 'ZIP/Postal Code*';
				
				break;
			}
			
			if($stateLookup == true){
				try {
					$rsStates = $mLink->prepare($stateQuery);
					$rsStates->execute();
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
			}
			
			?>
			
			
			
			<div class="form-group"  style="margin:0 0 10px 0 !important;">
				<label class="control-label visible-ie8 visible-ie9" style="margin:0 0 10px 0 !important;"><?php echo $stateLabel;?><span class="required">*</span></label>
				<input type="hidden" name="state-field-type" value="<?php echo $stateType;?>" />
					
					<?php
					if($stateType == 'dropdown'){?>
						<select name="new_billTo_state" id="state-field" class="select2 form-control">
							<option value=""><?php echo $stateDropDown;?></option>
							<?php
							//Get States/Provinces
							
							echo $optGroupStart;
												
							$states = $rsStates->fetch(PDO::FETCH_ASSOC);
							$entity = $states['state_entity'];
							// PDO for MySQL does not have a way to reset the pointer in the result set
							// Therefore we must run the query again to get a fresh set with the pointer at the top
							// Commonly mentioned workarounds do NOT work.
							// Please fix this Zend or Oracle!
							$rsStates->execute();
							// Loop through states, building option list
							while ($states = $rsStates->fetch(PDO::FETCH_ASSOC)){
								if ($states['state_entity'] != $entity){
									echo '
										</optgroup>
										<optgroup label = "Territories">
									';
									$entity = $states['state_entity'];
								}
								
								
								echo "<option value=\"".$states['state_abbrev']."\"".(trim($aBillInfo['state']) == trim($states['state_abbrev']) ? ' selected' : '')." ".$selected.">".$states['state_name']."</option>\r";
							}
							echo $optGroupEnd;
							?>
						</select>
					<?php
					}elseif($stateType == 'text-field'){
						?>
						<input type="text" name="state" class="form-control" placeholder="<?php echo $stateTextField;?>" />
						<?php
					}
					?>
				<div id="error_state"></div>
				
			</div><!--form-group-->
            
        </div><!--load-states-->
    </div><!--form-group-->
    
    <div class="form-group">
        <label class="form-label">City</label>
        <input type="text" name="new_billTo_city" class="form-control" value="<?php echo $aBillInfo['city'];?>"  />
    </div>

    

    <div class="form-group">
        <label class="form-label">Zip/Postal Code</label>
        <input type="text" name="new_billTo_zip" class="form-control" value="<?php echo $aBillInfo['zip'];?>"  />
    </div>

    
    
    <div class="form-group">
        <label class="form-label">Phone Number</label>
        <input type="text" name="new_billTo_phone" class="form-control" value="<?php echo $aBillInfo['phone'];?>" />
    </div>
    
    <div class="form-group">
        <label class="form-label">Email</label>
        <input type="text" name="new_billTo_email" class="form-control" value="<?php echo $aBillInfo['email'];?>" />
        <small>Receipts will be sent to this email address.</small>
    </div>

<!--    <div class="form-group">-->
		<label style="padding:5px 0 0 0;"><input type="checkbox" name="default_method" value="1"/>&nbsp;&nbsp;Make this my default payment method</label>
<!--	</div>-->

<!--    <button type="button" class="btn btn-default btn-sm" onclick="changeAddress('cancel');">Back</button>-->
    <?php
	
}

//+---------------------------------------------------------------------------------------------------------+
//|								Load Payment Methods  - PROCESS 4											|
//+---------------------------------------------------------------------------------------------------------+

if($process == 'load-payment-methods'){
	
	$query = "
	 SELECT *
	 FROM ".$_SESSION['payment_profiles_table']."
	 WHERE member_id=:member_id
	";
	try{
		$rsGetPayments = $mLink->prepare($query);
		$aValues = array(
			':member_id' => $_SESSION['member_id']
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetPayments->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
									
	while($pay = $rsGetPayments->fetch(PDO::FETCH_ASSOC)) {
		
		$billFirstName		= $pay['billTo_firstName'];
		$billLastName		= $pay['billTo_lastName'];
		$billAddress		= $pay['billTo_address'];
		$city				= $pay['billTo_city'];
		$state				= $pay['billTo_state'];
		$zip				= $pay['billTo_zip'];
		$country			= $pay['billTo_country'];
		$cardType			= $pay['cardType'];
		$cardLast4			= $pay['cardLastFour'];
		$cardName			= $pay['cardName'];
		$cardExpiration		= $pay['cardExpiration'];
		$billPhone			= $pay['billTo_phone'];
		$billEmail			= $pay['billTo_email'];
		
		$found= false;
		foreach($aBillingAddress as $aUID=>$aAddress){
			
			if($aAddress['address'] == trim($billAddress)){
				$found = true;
				break;	
			}
		}
		
		if($found === false){
			$aBillingAddress[$pay['uid']]	= array(
				'billName'		=> $billFirstName.' '.$billLastName,
				'firstName'		=> $billFirstName,
				'lastName'		=> $billLastName,
				'address'		=> $billAddress,
				'city'			=> $city,
				'state'			=> $state,
				'zip'			=> $zip,
				'country'		=> $country,
				'phone'			=> $billPhone,
				'email'			=> $billEmail
			);
		}
		
		$today = time();
		
		if($cardExpiration < $today){
			//card is expired
			$cardExpire = '<span style="color:#E02222;">Expired!</span>';
			
			$expireNotice = '
				<div class="alert alert-danger">
					<h4>This card exipired on '.date('m/Y',$cardExpiration).'! Please update or remove this card.</h4>
				</div>
			';	
		}else{
			$cardExpire = date('m/Y',$cardExpiration);	
			
			$expireNotice = '';
		}
		
		
		
		switch($cardType){
			
			case 'Visa':
				$cardImg = 'visa-straight-32px.png';
			break;
			
			case 'Mastercard':
				$cardImg = 'mastercard-straight-32px.png';
			break;
			
			case 'Discover':
				$cardImg = 'discover-straight-32px.png';
			break;
			
			case 'American Express':
				$cardImg = 'american-express-straight-32px.png';
			break;
				
		}
		
		//store billing info into a session for use in the processing files
		$_SESSION['aBillingAddresses'] = $aBillingAddress;
		?>
        
        
		<div class="panel panel-default payment-container">
			<div class="panel-heading accordion-fix">
				<h4 class="panel-title">
					<a class="accordion-toggle" data-toggle="collapse" data-parent="#payment-options" href="#payment_<?php echo $pay['uid'];?>">
						<span><img src="images/payments/<?php echo $cardImg;?>" alt="<?php echo $cardType;?>" width="40"/></span>
						<span style="position:relative;top:2px;padding-left:5px;"><?php echo $cardType;?> ending in <?php echo $cardLast4;?> </span>
						<span class="accordion-icons accordion-expand"></span><span style="float:right;position:relative;top:5px;right:10px;"><strong><?php echo $cardExpire;?></strong></span>
					</a>
				</h4>
				
			</div>
			<div id="payment_<?php echo $pay['uid'];?>" class="panel-collapse collapse">
				<div class="panel-body">
					
					<?php echo $expireNotice;?>
					
					<div class="row">
						<div class="col-md-4">
							<h5><strong>Name on Card</strong></h5>
							<p><?php echo $cardName;?></p>
						</div>
						
						<div class="col-md-8">
							<h5><strong>Billing Address</strong></h5>
							<p><?php echo $billFirstName.' '.$billLastName;?><br /><?php echo $billAddress;?><br /><?php echo $city;?>, <?php echo $state;?> <?php echo $zip;?>, <?php echo $country;?></p>
						</div>
					</div><!--row-->
					
					<div class="payment-btns">
						<a href="#delete-method" data-toggle="modal" class="btn btn-default" onclick="deletePayment('<?php echo $pay['uid'];?>');">Delete</a> <a href="#edit-method" data-toggle="modal" class="btn btn-default" onclick="editPayment('<?php echo $pay['uid'];?>');">Edit</a>
					</div>
					
					
				</div>
			</div>
		</div><!--panel-->
		<?php
		
	}
		
}

if($process == 'load-states'){

	$country 		= $_REQUEST['country'];
	$aCountry 		= explode('|', $country);
	
	$country 		= $aCountry[1];
	$countryCode	= $aCountry[0];
	
	switch($countryCode){
		case 'US'	:
			
			$stateLookup	= true;
			$stateQuery		= "
				SELECT *
				FROM ".$_SESSION['states_table']."
				WHERE state_entity = 'State'
				OR state_entity = 'Territory'
				ORDER BY state_entity ASC, state_name ASC
			";	
			
			$stateType		= 'dropdown';
			$stateLabel		= 'State<span class="required">*</span>';
			$stateDropDown	= 'Select your State*';
			$stateTextField	= '';
			
			$optGroupStart	= '<optgroup label="States">';
			$optGroupEnd	= '</optgroup>';
			
			$zipLabel		= 'ZIP Code<span class="required">*</span>';
			$zipFieldLabel	= 'ZIP Code*';
			
			
		break;
		
		case 'CA'	:
			
			$stateLookup	= true;
			$stateQuery		= "
				SELECT *
				FROM ".$_SESSION['states_table']."
				WHERE state_entity = 'Province'
				ORDER BY state_name
			";
			
			$stateType		= 'dropdown';
			$stateLabel		= 'Province<span class="required">*</span>';
			$stateDropDown	= 'Select your Province*';
			$stateTextField	= '';
			
			$optGroupStart	= '<optgroup label="Provinces">';
			$optGroupEnd	= '</optgroup>';
			
			$zipLabel		= 'Postal Code<span class="required">*</span>';
			$zipFieldLabel	= 'Postal Code*';
			
		break;
			
		default:
			
			$stateLookup	= false;
			$stateQuery 	= "";
			
			$stateType		= 'text-field';
			$stateLabel		= 'State/Province/Region<span class="required">*</span>';
			$stateDropDown	= '';
			$stateTextField = 'State/Province/Region*';
			
			$optGroupStart	= '<optgroup label="Provinces">';
			$optGroupEnd	= '</optgroup>';
			
			$zipLabel		= 'ZIP/Postal Code<span class="required">*</span>';
			$zipFieldLabel	= 'ZIP/Postal Code*';
		
		break;
	}
	
	if($stateLookup == true){
		try {
			$rsStates = $mLink->prepare($stateQuery);
			$rsStates->execute();
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}
	
	?>
    
    
    
    <div class="form-group"  style="margin:0 0 10px 0 !important;">
        <label class="control-label visible-ie8 visible-ie9" style="margin:0 0 10px 0 !important;"><?php echo $stateLabel;?><span class="required">*</span></label>
        <input type="hidden" name="state-field-type" value="<?php echo $stateType;?>" />
        
            <?php
			if($stateType == 'dropdown'){?>
                <select name="new_billTo_state" id="state-field" class="select2 form-control">
                    <option value=""><?php echo $stateDropDown;?></option>
                    <?php
                    //Get States/Provinces
                    
					echo $optGroupStart;
					                    
                    $states = $rsStates->fetch(PDO::FETCH_ASSOC);
                    $entity = $states['state_entity'];
                    // PDO for MySQL does not have a way to reset the pointer in the result set
                    // Therefore we must run the query again to get a fresh set with the pointer at the top
                    // Commonly mentioned workarounds do NOT work.
                    // Please fix this Zend or Oracle!
                    $rsStates->execute();
                    // Loop through states, building option list
                    while ($states = $rsStates->fetch(PDO::FETCH_ASSOC)){
                        if ($states['state_entity'] != $entity){
                            echo '
                                </optgroup>
                                <optgroup label = "Territories">
                            ';
                            $entity = $states['state_entity'];
                        }
                        
                        echo "<option value=\"".$states['state_abbrev']."\"".(trim($member['state']) == trim($states['state_name']) ? ' selected' : '')." ".$selected.">".$states['state_name']."</option>\r";
                    }
                    echo $optGroupEnd;
                    ?>
                </select>
            <?php
			}elseif($stateType == 'text-field'){
				?>
                <input type="text" name="state" class="form-control" placeholder="<?php echo $stateTextField;?>" />
                <?php
			}
			?>
        <div id="error_state"></div>
        
    </div>


    
    <?php
	
}

//+---------------------------------------------------------------------------------------------------------+
//|								Add New Payment Method Form - PROCESS 5		   								|
//+---------------------------------------------------------------------------------------------------------+
if($process == 'load-new-payment-modal'){
	?>


    <div class="modal-header">
       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
       <h4 class="modal-title">Add New Payment Method</span></h4>
    </div>

    <form action="process/ajax/user-payment-processes.php" method="post" name="new-payment-form" class="new-payment-form">
        <div class="modal-body">

            <div id="new-payment-method-error"></div>

            <div class="row">
                <div class="col-md-8">
                    <h5><strong>Enter your credit card</strong></h5>

                    <div class="form-group">
                        <label class="form-label"><strong>Card Number</strong></label>
                        <input type="text" name="cc_number" class="form-control" id="card-number" autocomplete="off"/>
                    </div><!--form-group-->

                    <div class="form-group">
                        <label class="form-label"><strong>Name On Card</strong></label>
                        <input type="text" name="cardName" class="form-control" />
                    </div><!--form-group-->

                    <div class="form-group">
                        <label class="form-label"><strong>CCV</strong> <span style="font-size:11px; color:#737373;">(Security Code)</span></label>
                        <input type="text" name="CCV" class="form-control" autocomplete="off"/>
                    </div><!--form-group-->

                    <div class="form-inline">

                        <label class="form-label"><strong>Expiration Date</strong></label><br />
                        <div class="form-group">

                            <select name="exp_month" class="form-control input-small">
                                <?php echo date_list($mLink, 'month', NULL, NULL);?>
                            </select>
                        </div>

                        <div class="form-group">
                            <select name="exp_year" class="form-control input-small">
                                <?php
                                $year = date('Y');
                                for($i = 1; $i < 21; $i++){



                                    echo '<option value="'.$year.'" '.$selected.'>'.$year.'</option>';

                                    $year++;
                                }

                                ?>
                            </select>
                        </div><!--form-group-->
                    </div><!--form-inline-->


                </div><!--col-md-8-->

                <div class="col-md-4">
                    <h5><strong>We Accept</strong></h5>
                    <p>
                        <img src="images/payments/visa-straight-32px.png" alt="Visa" title="Visa" width="51" style="margin-top:5px;"/>
                        <img src="images/payments/mastercard-straight-32px.png" alt="MasterCard" title="MasterCard" width="51" style="margin-top:5px;"/>
                        <img src="images/payments/american-express-straight-32px.png" alt="American Express" title="American Express" width="51" style="margin-top:5px;"/>
                        <img src="images/payments/discover-straight-32px.png" alt="Discover" title="Discover" width="51" style="margin-top:5px;"/>
                    </p>


                    <div id="show-card-type"></div>
                </div><!--col-md-4-->

            </div><!--row-->



            <div class="row">
                <div class="col-md-12">

                    <br />
                    <label class="form-label"><strong>Billing Address</strong></label>
                    <div id="billing-address-area2">

                        <div class="new-address-btns" style="margin-bottom:20px;">
                            <button type="button" class="btn btn-info btn-sm" onclick="changeAddress2('new-address2');">New Address</button>
                        </div><!--new-address-btns-->


                        <div class="row">


                           	<?php
							foreach($_SESSION['aBillingAddresses'] as $listUID=>$aInfo){

								?>
								<div class="col-md-4">
									<p><?php echo $aInfo['billName'];?><br /><?php echo $aInfo['address'];?><br /><?php echo $aInfo['city'];?>, <?php echo $aInfo['state'];?> <?php echo $aInfo['zip'];?>, <?php echo $aInfo['country'];?></p>
									<button type="button" class="btn btn-info btn-sm" onclick="useThisAddress2('<?php echo $listUID;?>');">Use This Address</button>
								</div><!--col-md-4-->
								<?php
							}
							?>
                        </div><!--row-->

                    </div><!--billing-address-area-->

                    <div id="new-address-fields2" class="hide">

                        <div class="new-address-errors"></div>

                        <div class="form-group">
                            <label class="form-label">First Name</label>
                            <input type="text" name="new_billTo_firstName" class="form-control"  />
                        </div>

                        <div class="form-group">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="new_billTo_lastName" class="form-control"  />
                        </div>





                        <div class="form-group">
                            <label class="form-label">Address Line 1</label>
                            <input type="text" name="new_billTo_address1" class="form-control"  />
                        </div>

                        <div class="form-group">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" name="new_billTo_address2" class="form-control"  />
                            <span class="help-block">Apt, Suite, etc</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Country</label>
                            <select name="new_billTo_country" class="form-control" onChange="loadStates(this.value);">
                                <option value="xx">Select Country</option>
                                <option value="US">United States</option>
                                <?php
								$aCountries = array();
								$query = "
									SELECT country_name, country_code_isa
									FROM system_countries
									ORDER BY country_name ASC
								";

								try{
									$rsGetCounty = $mLink->prepare($query);
									$aValues = array();
									$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
									$rsGetCounty->execute($aValues);
								}
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}
								while($country = $rsGetCounty->fetch(PDO::FETCH_ASSOC)) {

									if($country['country_code_isa'] == "US"){
										continue;
									}

									if($aBillInfo['country'] == $country['country_code_isa']){
										$selected = 'selected';
									}else{
										$selected = '';
									}

									/*if($country['coutry_code_isa'] != 'US'){
										echo '<option value="'.$country['country_code_isa'].'" '.$selected.'>'.$country['country_name'].'</option>';


									}*/
									//save for later
									$aCountries[$country['country_code_isa']] = $country['country_name'];
								}

								foreach($aCountries as $countryCode=>$countryName){

									if($countryCode == $aBillInfo['country']){
										$selected = 'selected';
									}else{
										$selected = '';
									}

									echo '<option value="'.$countryCode.'" '.$selected.'>'.$countryName.'</option>';
								}
								?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">State/Province/Region</label>
                            <div id="load-states">
                            <small>Please select a country.</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">City</label>
                            <input type="text" name="new_billTo_city" class="form-control"  />
                        </div>



                        <div class="form-group">
                            <label class="form-label">Zip/Postal Code</label>
                            <input type="text" name="new_billTo_zip" class="form-control"  />
                        </div>



                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="new_billTo_phone" class="form-control"  />
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="text" name="new_billTo_email" class="form-control"  />
                            <small>Receipts will be sent to this email address.</small>
                        </div>

<!--
						<div class="clearfix"></div>
                        <div class="form-group">
	                        <div class="checkbox">
								<label style="float:left;"><input type="checkbox" name="default_method" class="form-control" />Make this my default payment method</label>
							</div>
                        </div>
-->
						<label style="padding:5px 0 0 0;"><input type="checkbox" name="default_method" value="1"/>&nbsp;&nbsp;Make this my default payment method</label>

                    </div><!--new-address-fields-->

                </div><!--col-md-12-->
            </div><!--row-->

        </div><!--modal-body-->

        <div class="modal-footer">

            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <input type="submit" value="Add Card" id="submit-page" class="btn btn-info"/>
        </div>

    </form><!--create-topic-->
    <?php
}




//+---------------------------------------------------------------------------------------------------------+
//|								VALIDATE CARD ON THE FLY													|
//+---------------------------------------------------------------------------------------------------------+
if($process == 'validate-card'){
	
	$cardNumber = $_REQUEST['ccNumber'];
	
	// Clean up the card number and get the last four
	$cleanCardNumber	= preg_replace("/[^0-9]/","",$cardNumber);
	$cardLastFour 		= substr($cleanCardNumber, -4);
	$cardFirstOne 		= substr($cleanCardNumber, 0, 1);
	$numberLength		= strlen($cleanCardNumber);
	
	
	// Determine the card type
	switch($cardFirstOne){
		//AMERICAN EXPRESS
		case '3':
			if (substr($cleanCardNumber, 0, 2) == "34" || substr($cleanCardNumber, 0, 2) == "37"){
				
				$cardType = "AmEx";
				
				//Validate number length
				if($numberLength == 15){
					
					//Checksum validation
					if(cc_checksum($cleanCardNumber) == true){
						$cardType = "AmEx";
					}else{
						$aErrors[] = 'Invalid Card Number, please check your card number and try again';		
					}
					
				}else{
					$aErrors[] = 'Invalid Card Number Length';		
				}

			}elseif (substr($cleanCardNumber, 0, 2) == "30" || substr($cleanCardNumber, 0, 2) == "36" || substr($cleanCardNumber, 0, 2) == "38"){
				$cardType = "Diner's Clube/Carte Blanche";
				#flag as an error - we don't take those
				$aErrors[] = 'We do not accept the card type you entered. Please use another card.';
			}else{
				#flag as an error - invalid card (or JCB)
				$aErrors[] = 'Invalid Card, please try another.';
			}
		break;

		//VISA
		case '4':
			$cardType = "Visa";
			//Validate number length
			if($numberLength == 16 || $numberLength == 13){
				
				//Checksum validation
				if(cc_checksum($cleanCardNumber) == true){
					$cardType = "Visa";
				}else{
					$aErrors[] = 'Invalid Card Number, please check your card number and try again';		
				}
				
			}else{
				$aErrors[] = 'Invalid Card Number Length';		
			}
			
		break;
		
		//MASTERCARD
		case '5':
			if ((int)substr($cleanCardNumber, 0, 2) > 50 && (int)substr($cleanCardNumber, 0, 2) <= 55){
				
				$cardType = "Mastercard";
				
				//Validate number length
				if($numberLength == 16){
					
					//Checksum validation
					if(cc_checksum($cleanCardNumber) == true){
						$cardType = "Mastercard";
					}else{
						$aErrors[] = 'Invalid Card Number, please check your card number and try again';		
					}
					
				}else{
					$aErrors[] = 'Invalid Card Number Length';	
				}
				
			}else{
				#flag as an error - invalid card
				$aErrors[] = 'Invalid Card, please try another.';
			}
		break;
		
		//DISCOVER
		case '6':
			if (((int)substr($cleanCardNumber, 0, 8) >= 60110000 && (int)substr($cleanCardNumber, 0, 8) <= 60119999) ||
				((int)substr($cleanCardNumber, 0, 8) >= 65000000 && (int)substr($cleanCardNumber, 0, 8) <= 65999999) ||
				((int)substr($cleanCardNumber, 0, 8) >= 62212600 && (int)substr($cleanCardNumber, 0, 8) <= 62292599)){
				
				$cardType = "Discover";
				
				//Validate number length
				if($numberLength == 16){
					
					//Checksum validation
					if(cc_checksum($cleanCardNumber) == true){
						$cardType = "Discover";
					}else{
						$aErrors[] = 'Invalid Card Number, please check your card number and try again';		
					}
					
				}else{
					$aErrors[] = 'Invalid Card Number Length';	
				}
				
			}else{
				#flag as an error - invalid card
				$aErrors[] = 'Invalid Card, please try another.';
			}
		break;

		default:
			#flag as an error - invalid card
			$aErrors[] = 'Invalid Card, please try another.';
		break;
	}
	
	
	switch($cardType){
		case 'Mastercard':
			echo 'mastercard-straight-32px.png|MasterCard|valid';
		break;
		
		case 'Visa':
			echo 'visa-straight-32px.png|Visa|valid';
		break;
		
		case 'AmEx':
			echo 'american-express-straight-32px.png|American Express|valid';
		break;
		
		case 'Discover':
			echo 'discover-straight-32px.png|Discover|valid';
		break;
		
		default:
			echo 'xx|invalid';
		break;
	}
	
}


//+---------------------------------------------------------------------------------------------------------+
//|								Load Delete Payment Method Modal  - PROCESS 7								|
//+---------------------------------------------------------------------------------------------------------+
if($process == 'load-delete-payment-modal'){

	$uid		= $_REQUEST['uid'];

	$query = "
	 SELECT *
	 FROM ".$_SESSION['payment_profiles_table']."
	 WHERE uid=:uid
	";
	try{
		$rsGetPayments = $mLink->prepare($query);
		$aValues = array(
			'uid' => $uid
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetPayments->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}


	$pay = $rsGetPayments->fetch(PDO::FETCH_ASSOC);

	$billFirstName		= $pay['billTo_firstName'];
	$billLastName		= $pay['billTo_lastName'];
	$billAddress		= $pay['billTo_address'];
	$city				= $pay['billTo_city'];
	$state				= $pay['billTo_state'];
	$zip				= $pay['billTo_zip'];
	$country			= $pay['billTo_country'];
	$cardType			= $pay['cardType'];
	$cardLast4			= $pay['cardLastFour'];
	$cardName			= $pay['cardName'];
	$cardExpiration		= $pay['cardExpiration'];

	$today = time();

	if($cardExpiration < $today){
		//card is expired
		$cardExpire = '<span style="color:#E02222;">Expired!</span>';

		$expireNotice = '
			<div class="alert alert-danger">
				<h4>This card exipired on '.date('m/Y',$cardExpiration).'! Please update or remove this card.</h4>
			</div>
		';
	}else{
		$cardExpire = date('m/Y',$cardExpiration);

		$expireNotice = '';
	}



	switch($cardType){

		case 'Visa':
			$cardImg = 'visa-straight-32px.png';
		break;

		case 'Mastercard':
			$cardImg = 'mastercard-straight-32px.png';
		break;

		case 'Discover':
			$cardImg = 'discover-straight-32px.png';
		break;

		case 'American Express':
			$cardImg = 'american-express-straight-32px.png';
		break;

	}
	?>
	<div class="modal-header">
       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
       <h4 class="modal-title">Delete Payment Method</span></h4>
    </div>

    <form action="process/ajax/user-payment-processes.php" method="post" name="delete-method-form" class="delete-method-form">
        <div class="modal-body">

            <div id="delete-method-error"></div>

            <div class="row">
                <div class="col-md-4">
                    <h5><strong>Card</strong></h5>
                    <img src="images/payments/<?php echo $cardImg;?>" alt="<?php echo $cardType;?>" width="40" height="25"/> ending in <?php echo $cardLast4;?>
                </div>
            </div>

            <p style="margin-top:20px;">By clicking "Delete", this card will be removed completely from your profile. This action can not be undone, you will have to re-add it.</p>

        </div><!--modal-body-->

        <div class="modal-footer">
            <input type="hidden" value="<?php echo $uid;?>" name="uid" />

            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <input type="submit" value="Delete Card" id="submit-page" class="btn btn-danger"/>
        </div>

    </form><!--create-topic-->
    <?php
}



//+---------------------------------------------------------------------------------------------------------+
//|								Load Invoice Modal  - PROCESS 8												|
//+---------------------------------------------------------------------------------------------------------+
if($process == 'load-invoice-modal'){
	
	?>
	<div class="modal-header">
       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
       <h4 class="modal-title">Invoice Number: xxxx</span></h4>
    </div>
    
    <div class="modal-body">
        
        <h5><strong>Billed Date:</strong> February 15, 2015</h5>  
        <h5><strong>Invoice Number:</strong> xxxx</h5>  
        <h5><strong>Bill Total:</strong> $34.75</h5>   
        
        <table class="table table-bordered">
        	<tr>
            	<td colspan="2"><strong>Order Information</strong></td>
           	</tr>
            <tr>
            	<td>
                    <p style="color:#222222;">Montly Premium Subscription</p>
                    
                </td>
                <td style="text-align:right;">
                	<h5><strong>Price:</strong></h5>
                	<p style="color:#222222;">$34.75<p>
                </td>
            </tr>
        </table>
            
        <table class="table table-bordered">
        	<tr>
            	<td colspan="2"><strong>Payment Information</strong></td>
           	</tr>
            <tr>
            	<td>
                	<h5><strong>Payment Method:</strong></h5>
                    <p>MasterCard | Last digits: 6788</p>
                    
                    <h5><strong>Billing Address:</strong></h5>
                    <p>Brandon McCarthy<br />10448 Bear Creek Trl<br />Fort Worth, Texas 76244<br />United States<p>
                </td>
                <td style="text-align:right;">
                	<p style="color:#222222;">
                    SubTotal: $34.75<br />
                    -----<br />
                    Tax: 2.87<br />
                    -----<br />
                    <strong>Total: 37.61</strong>
                    <p>
                </td>
            </tr>
        </table>
        
    </div><!--modal-body-->
    
    <div class="modal-footer">
        <input type="hidden" value="<?php echo $uid;?>" name="uid" />
        
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    </div>
    <?php
	
}
?>