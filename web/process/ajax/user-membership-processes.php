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

//build a recursive in array function
function in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}


switch($process){
	
	//+-----------------------------------------------------------------------------------------+
	//|							VALIDATE EDU EMAIL on keyup
	//+-----------------------------------------------------------------------------------------+
	case 'validate-email':
		
		$emailAddress 	= $_REQUEST['email'];
		$emailError		= false;
		
		if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
			
			$emailError 	= true;
			$emailReason	= "Please enter a valid .edu email address."; 
		}else{
			
			if(strpos($emailAddress, '.edu') !== false){
			
				$emailError		= false;	
			}else{
				
				$splitEmail			= explode('@', $emailAddress);
				$emailDomain		= $splitEmail[1];
				$emailSplitDomain	= explode('.',$emailDomain);
				$emailSplitDomain	= array_reverse($emailSplitDomain);
				$emailHighDomain	= strtoupper($emailSplitDomain[0]);
				
				$emailError		= true;
				$emailReason	= 'You have entered a .'.$emailHighDomain.' email address. You must use a valid .EDU email address to continue.';
			}
				
		}
		
		
		
		if($emailError == true){
			
			echo '
				<div class="alert alert-danger"><p><small>'.$emailReason.'</small></p>
				
				</div>
			';
				
		}else{
			
			echo '
				<div class="alert alert-success"><p><small>Email Address is valid.</small></p></div>
			';
			
		}
		
	break;
	
	//+-----------------------------------------------------------------------------------------+
	//|								SEND EDU VALIDATION EMAIL
	//+-----------------------------------------------------------------------------------------+
	case 'send-edu-validation-email':
	
		$email			= $_REQUEST['email'];
		
		$emailDomain 	= substr(strrchr($email, "@"), 1);
		
		$aErrors		= array();
		
		if(strpos($emailDomain, '.edu') !== false){
			$eduValid = true;	
		}else{
			$eduValid = false;
			
			$aErrors[] = 'You must provide a .EDU email address to continue. Otherwise select "No, I am NOT a student".';	
		}
		
		if(empty($aErrors)){
			
			//Generate code to use in validation
			$code 	= gen_randomString(10,'numUpperSymbol');
			
			$query 	= "
				UPDATE ".$_SESSION['members_table']."
				SET edu_email=:edu_email, edu_verified_code=:code
				WHERE member_id=:member_id
			";
			try {
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':member_id'	=> $_SESSION['member_id'],
					':edu_email'	=> $email,
					':code'			=> $code
				);
				// Prepared query - for error logging and debugging
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdate->execute($aValues);
			}
			catch(PDOException $error){
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			echo $error;
			
			$aEmailVars = array(
				'edu_code'		=> $code,
				'support_email'	=> 'help@marketocracy.com'
			);
			$aEmail = array(
				'email_id'		=> '10',
				'to'			=> $email,
				'vars'			=> $aEmailVars,
				'headers_bcc'	=> 'brandon.mccarthy@marketocracy.com'
			);
			include('../../includes/email/system-email.php');
			?>
			
			
			<div class="show-code-box"></div>
			<?php
			
		}else{
			
			echo '<div class="alert alert-danger" style="margin:5px 0 0 0;"><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';
			}
			echo '</ul></div>';
		}
	
	break;
	
	//+-----------------------------------------------------------------------------------------+
	//|								EDU VERIFY CODE
	//+-----------------------------------------------------------------------------------------+
	case 'edu-verify-code':
		
		$code = $_REQUEST['code'];
	
		$query = "
			SELECT *
			FROM ".$_SESSION['members_table']."
			WHERE member_id=:member_id AND edu_verified_code=:code
		";
		try {
			$rsCheckCode = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $_SESSION['member_id'],
				':code'			=> $code
			);
			// Prepared query - for error logging and debugging
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsCheckCode->execute($aValues);
		}
		catch(PDOException $error){
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$rowCount = $rsCheckCode->rowCount();
		
		if($rowCount > 0){
			//email is valid
			
			//update most recent Auth record
			$query = "
				UPDATE ".$_SESSION['auth_table']."
				SET edu_email_validated_timestamp=UNIX_TIMESTAMP()
				WHERE member_id=:member_id
			";
			try {
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':member_id'	=> $_SESSION['member_id']
				);
				// Prepared query - for error logging and debugging
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdate->execute($aValues);
			}
			catch(PDOException $error){
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$query = "
				UPDATE ".$_SESSION['members_flags_table']."
				SET student='1'
				WHERE member_id=:member_id
			";
			try {
				$rsUpdateFlags = $mLink->prepare($query);
				$aValues = array(
					':member_id'	=> $_SESSION['member_id']
				);
				// Prepared query - for error logging and debugging
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdateFlags->execute($aValues);
			}
			catch(PDOException $error){
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$_SESSION['flag_student'] = '1';
			
			?>
			<div class="alert alert-success">
				<p>Your email has been successfully verified as a valid .edu address!<p>
				<input type="hidden" name="edu_email_passed" value="passed" />
			</div>
			<?php
			
			echo '<div class="email-is-valid"></div>';
		}else{
			
			echo '<div class="alert alert-danger" style="margin-top:5px;"><p>Invalid Code!</p></div>';
		}
		
	break;	
}


//+---------------------------------------------------------------------------------------------------------+
//|							SUBMIT ORDER | This is where you go when you click "PLACE ORDER"
//+---------------------------------------------------------------------------------------------------------+
if($process == 'submit-cart-order'){

	$cartID			= $_REQUEST['cart_id'];
	$payMethodID 	= $_REQUEST['payment_method_id'];
	$aProductIDs	= explode('|',$_REQUEST['product_ids']);
	$cartTotal		= $_REQUEST['cart_total'];
	$renewalDate	= $_REQUEST['renewal_date'];
	$term			= $_REQUEST['term'];

	// Added to cover for the occasional the renewal date is not properly set, for paid subscriptions, in the checkout process - JSS
	if ($renewalDate == NULL || $renewalDate < time()){
		if ($term == "Monthly"){
			$renewalDate = strtotime('+1 month', time());
		}elseif ($term == "Annually"){
                        $renewalDate = strtotime('+1 year', time());
		} 
	}

	$aErrors		= array();

	//AUTHORIZE AREA

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

	// Get the customerProfileID from merchant_customer_profiles
	$query = "
		SELECT customerProfileID
		FROM ".$_SESSION['customer_profiles_table']."
		WHERE member_id = :member_id
	";
	try{
		$rsGetCustomerInfo = $mLink->prepare($query);
		$aValues = array(
			':member_id'	=> $_SESSION['member_id']
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetCustomerInfo->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$customerInfo		= $rsGetCustomerInfo->fetch(PDO::FETCH_ASSOC);
	$customerProfileID	= $customerInfo['customerProfileID'];

	// Get the customerPaymentProfileID & CCV from merchant_payment_profiles
	$query = "
		SELECT customerPaymentProfileID, CCV
		FROM ".$_SESSION['payment_profiles_table']."
		WHERE uid = :uid
		AND default_method = 1
	";
	try{
		$rsGetPaymentInfo = $mLink->prepare($query);
		$aValues = array(
			':uid'	=> $payMethodID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetPaymentInfo->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$paymentInfo				= $rsGetPaymentInfo->fetch(PDO::FETCH_ASSOC);
	$customerPaymentProfileID	= $paymentInfo['customerPaymentProfileID'];
	$transactionCardCode		= double_decrypt($paymentInfo['CCV']) + 0; // Force numeric

	// Use the cart ID as the invoice number
	$order_invoiceNumber = $cartID;

	// Get the rest from merchant_cart
	$query = "
		SELECT *
		FROM ".$_SESSION['cart_table']."
		WHERE cart_id = :cart_id
	";
	try{
		$rsGetCartInfo = $mLink->prepare($query);
		$aValues = array(
			':cart_id'	=> $cartID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		//echo $preparedQuery;
		$rsGetCartInfo->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}

	// Static values
	$tax_name			= "Non Taxable";  // Cannot be blank (lame)
	$tax_amount			= "0.00"; // Must contain a decimal point (lamer)
	$orderDescription	= "Marketocracy Subscription";
	$transactionType	= "profileTransAuthCapture";
	$itemID				= "1";
	//$itemName			= $orderDescription;

	// Loop through cart rows and calculate totals
	$items		= "";
	$subtotal	= 0;
	while ($cartInfo = $rsGetCartInfo->fetch(PDO::FETCH_ASSOC)){

		$items		.= $cartInfo['product_id']."|";
		$altItemName = $cartInfo['alt_product_name']."|";
		$subtotal	+= $cartInfo['sale_price'];
		$taxable	 = $cartInfo['taxable'];
		$tax_rate	 = $cartInfo['tax_rate'];

	}

	// Pop the trailing pipe off the $items list
	$items = substr($items, 0, -1);

	// Assign item name
	$itemName = explode("|", $altItemName)[0];

	// Tack on the word "Membership" if that's the item type
	if (explode("|", $items)[0] < 100){
		$itemName .= " Membership";
	}
//echo $itemName;die();

	// Apply sales tax
	if ($taxable == true){
		$tax_amount	= round(($subtotal * $tax_rate), 2);
		$tax_name	= "Texas Sales Tax";
	}

	$total				= round(($subtotal + $tax_amount), 2);

	// Format the values to be sent - Authorize requires all dollar amounts to include the decimal and the cents...this code turns 5 into 5.00, for example
	$unitPrice			= number_format((float)$subtotal, 2, '.', '');
	$transaction_amount	= number_format((float)$total, 2, '.', '');

	// Charge the card!
	$cim = authorizeCreateTransactionRequest(
		$apiLogin,
		$transKey,
		$testMode,
		$customerProfileID,
		$customerPaymentProfileID,
		$order_invoiceNumber,
		$orderDescription,
		$itemID,
		$itemName,
		$transactionCardCode,
		$transactionType,
		$unitPrice,
		$tax_amount,
		$tax_name,
		$transaction_amount
	);

// Debug
if ($_SESSION["member_id"] == 1){
	echo $apiLogin."<br>";
	echo $transKey."<br>";
	echo $testMode."<br>";
	echo $customerProfileID."<br>";
	echo $customerPaymentProfileID."<br>";
	echo $order_invoiceNumber."<br>";
	echo $orderDescription."<br>";
	echo $itemID."<br>";
	echo $itemName."<br>";
	echo $transactionCardCode."<br>";
	echo $transactionType."<br>";
	echo $unitPrice."<br>";
	echo $tax_amount."<br>";
	echo $tax_name."<br>";
	echo $transaction_amount."<br>";
	die();
}

	$CIMresultCode	= $cim->resultCode;  //"Ok" if all is well
	$CIMcode		= $cim->code;  //The Authorize response code
	$CIMtext		= $cim->text;  //"Successful." if all goes well
	$CIMapproval	= $cim->approval;
	$CIMtransID		= $cim->transID;
	$CIMauthCode	= $cim->authCode;
	$CIMresponse	= $cim->response;

	// Test for resultCode = "Error"
	if ($CIMresultCode == "Error"){
//	if (is_null($CIMresultCode) || $CIMresultCode == "Error"){

		$aErrors[] = $CIMtext;
//		$aErrors[] = $CIMresultCode;

	}else{ // Card approved!

//$aErrors[] = print_r($cim);
		// Build discount code(s) string
		$discounts = "";
		foreach($_SESSION['merchant-discounts'] as $key=>$duration){
			$discounts .= $key."~".$duration."|";

			// Insert each into the applied discounts table
			$query = "
				INSERT INTO ".$_SESSION['applied_discounts_table']."(
					member_id,
					discount_id,
					timestamp
				) VALUES (
					:member_id,
					:discountID,
					UNIX_TIMESTAMP()
				)
			";
			try{
				$rsInsert = $mLink->prepare($query);
				$aValues = array(
					':member_id' 	=> $_SESSION["member_id"],
					':discountID'	=> $key
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				//$aErrors[] = $preparedQuery;
				//echo $preparedQuery;
				$rsInsert->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				$aErrors[] = $error;
			}

		}

		// Pop the trailing pipe off the discounts string
		$_SESSION['discounts'] = substr($discounts, 0, -1);

		// Now
		$billTimestamp	= time();

		// Insert transaction into merchant_transaction_history
		$query = "
			INSERT INTO ".$_SESSION['transaction_history_table']."(
				member_id,
				customerProfileID,
				customerPaymentProfileID,
				order_invoiceNumber,
				bill_date,
				bill_timestamp,
				order_description,
				itemIDs,
				bill_frequency,
				unitPrice,
				taxable,
				tax_name,
				transaction_amount,
				discounts,
				transactionCardCode,
				payment_profile_uid,
				CIMResultCode,
				CIMCode,
				CIMText,
				CIMApproval,
				CIMTransID,
				CIMAuthCode,
				CIMResponse
			) VALUES (
				:member_id,
				:customerProfileID,
				:customerPaymentProfileID,
				:order_invoiceNumber,
				:bill_date,
				:bill_timestamp,
				:order_description,
				:itemIDs,
				:bill_frequency,
				:unitPrice,
				:taxable,
				:tax_name,
				:transaction_amount,
				:discounts,
				:transactionCardCode,
				:payment_profile_uid,
				:CIMResultCode,
				:CIMCode,
				:CIMText,
				:CIMApproval,
				:CIMTransID,
				:CIMAuthCode,
				:CIMResponse
			)
		";
		try{
			$rsInsert = $mLink->prepare($query);
			$aValues = array(
				':member_id'				=> $_SESSION["member_id"],
				':customerProfileID'		=> $customerProfileID,
				':customerPaymentProfileID'	=> $customerPaymentProfileID,
				':order_invoiceNumber'		=> $order_invoiceNumber,
				':bill_date'				=> date("Ymd"),
				':bill_timestamp'			=> $billTimestamp,
				':order_description'		=> $orderDescription,
				':itemIDs'					=> $items,
				':bill_frequency'			=> $term,
				':unitPrice'				=> $unitPrice,
				':taxable'					=> $taxable,
				':tax_name'					=> $tax_name,
				':transaction_amount'		=> $transaction_amount,
				':discounts'				=> $_SESSION['discounts'],
				':transactionCardCode'		=> $paymentInfo['CCV'],
				':payment_profile_uid'		=> $payMethodID,
				':CIMResultCode'			=> $CIMresultCode,
				':CIMCode'					=> $CIMcode,
				':CIMText'					=> $CIMtext,
				':CIMApproval'				=> $CIMapproval,
				':CIMTransID'				=> $CIMtransID,
				':CIMAuthCode'				=> $CIMauthCode,
				':CIMResponse'				=> addslashes($CIMresponse)
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			//$aErrors[] = $preparedQuery;
			//echo $preparedQuery;
			$rsInsert->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			$aErrors[] = $error;
		}

		// If in trial, update trial record
		if ($_SESSION['subscription']['inTrial'] == 1){

			// Grab the merchant_cart again
			$query = "
				SELECT *
				FROM ".$_SESSION['cart_table']."
				WHERE cart_id = :cart_id
			";
			try{
				$rsGetCartInfo = $mLink->prepare($query);
				$aValues = array(
					':cart_id'	=> $cartID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetCartInfo->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}

			// Loop through cart rows and insert subscription records for each item
			$cartInfo = $rsGetCartInfo->fetch(PDO::FETCH_ASSOC);

			// Update the trial subscription record
			$query = "
				UPDATE ".$_SESSION['subscriptions_table']."
				SET new_product_id		= :new_product_id,
					new_bill_frequency	= :new_bill_frequency,
					last_invoice_id		= :last_invoice_id,
					discounts			= :discounts
				WHERE active = 1
				AND member_id = :member_id
				AND (product_id = 0 || product_id = 99)
			";
			try{
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':new_product_id'		=> $items,
					':new_bill_frequency'	=> $cartInfo['term'],
					':last_invoice_id'		=> $cartInfo['cart_id'],
					':discounts'			=> $_SESSION['discounts'],
					':member_id'			=> $_SESSION["member_id"]
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				//$aErrors[] = $preparedQuery;
				//echo $preparedQuery;die();
				$rsUpdate->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				$aErrors[] = $error;
			}

	  	}else{ // Not in trial

			// deactivate their old membership level if they are upgrading membership
			if ($cartInfo['product_id'] > 0 && $cartInfo['product_id'] < 11){ // Upgrading

				$query = "
					UPDATE ".$_SESSION['subscriptions_table']."
					SET active				= :active,
						cancel_timestamp	= UNIX_TIMESTAMP(),
						cancel_reason		= :cancel_reason
					WHERE active = 1
					AND member_id = :member_id
					AND product_id > 0
					AND product_id < 11
				";
				try{
					$rsUpdate = $mLink->prepare($query);
					$aValues = array(
						':active'		  	=> 0,
						':cancel_reason'	=> "Upgrade",
						':member_id'		=> $_SESSION["member_id"]
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					//$aErrors[] = $preparedQuery;
					//echo $preparedQuery;die();
					$rsUpdate->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					$aErrors[] = $error;
				}

			}

#basically if someone pays, just need to flag all current "active" funds with a null in fund_experation
			// remove the expiration date from their funds - they are now unlimited in duration
			$query = "
				UPDATE ".$_SESSION['fund_table']."
				SET fund_experation	= :fund_expiration
				WHERE active = 1
				AND member_id = :member_id
			";
			try{
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':fund_expiration'	=> NULL,
					':member_id'		=> $_SESSION["member_id"]
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				//$aErrors[] = $preparedQuery;
				//echo $preparedQuery;die();
				$rsUpdate->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				$aErrors[] = $error;
			}

			// Create a billing record for the next billing cycle

// Synchronize the next billing date with the the membership billing date...

			$query = "
				INSERT INTO ".$_SESSION['billing_table']."(
					member_id,
					customerProfileID,
					customerPaymentProfileID,
					next_bill_date,
					next_bill_timestamp,
					order_description,
					itemIDs,
					bill_frequency,
					unitPrice,
					taxable,
					tax_name,
					transaction_amount,
					discounts,
					renewal_timestamp,
					created_timestamp,
					updated_timestamp,
					last_bill_date,
					last_bill_timestamp
				) VALUES (
					:member_id,
					:customerProfileID,
					:customerPaymentProfileID,
					:next_bill_date,
					:next_bill_timestamp,
					:order_description,
					:itemIDs,
					:bill_frequency,
					:unitPrice,
					:taxable,
					:tax_name,
					:transaction_amount,
					:discounts,
					:renewal_timestamp,
					UNIX_TIMESTAMP(),
					UNIX_TIMESTAMP(),
					:last_bill_date,
					UNIX_TIMESTAMP()
				)
			";
			try{
				$rsInsert = $mLink->prepare($query);
				$aValues = array(
					':member_id'				=> $_SESSION["member_id"],
					':customerProfileID'		=> $customerProfileID,
					':customerPaymentProfileID'	=> $customerPaymentProfileID,
					':next_bill_date'			=> date("Ymd", $renewalDate),
					':next_bill_timestamp'		=> $renewalDate,
					':order_description'		=> $order_description,
					':itemIDs'					=> $items,
					':bill_frequency'			=> $term,
					':unitPrice'				=> $unitPrice,
					':taxable'					=> $taxable,
					':tax_name'					=> $tax_name,
					':transaction_amount'		=> $transaction_amount,
					':discounts'				=> $_SESSION['discounts'],
					':renewal_timestamp'		=> strtotime('+1 year', time()),
					':last_bill_date'			=> date("Ymd")
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				//$aErrors[] = $preparedQuery;
				//echo $preparedQuery;
				$rsInsert->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				$aErrors[] = $error;
			}

			// Grab the merchant_cart again
			$query = "
				SELECT *
				FROM ".$_SESSION['cart_table']."
				WHERE cart_id = :cart_id
			";
			try{
				$rsGetCartInfo = $mLink->prepare($query);
				$aValues = array(
					':cart_id'	=> $cartID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetCartInfo->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}

			// Loop through cart rows and insert subscription records for each item
			while ($cartInfo = $rsGetCartInfo->fetch(PDO::FETCH_ASSOC)){

	  			$product_id = $cartInfo['product_id'];

				// Set the next billing date
				if($cartInfo['term'] == 'Annually'){
					$nextBillDate = strtotime('+1 year', $subs['next_bill_timestamp']);
				}elseif($cartInfo['term'] == 'Monthly'){
					$nextBillDate = strtotime('+1 month', $subs['next_bill_timestamp']);
				}

				// Create a subscription record for each
				$query = "
					INSERT INTO ".$_SESSION['subscriptions_table']."(
						member_id,
						product_id,
						start_timestamp,
						bill_frequency,
						next_bill_timestamp,
						last_invoice_id,
						fund_id,
						email_campaign_id,
						discounts,
						product_expiration,
						cancel_timestamp,
						cancel_reason,
						active
					) VALUES (
						:member_id,
						:product_id,
						UNIX_TIMESTAMP(),
						:bill_frequency,
						:next_bill_timestamp,
						:last_invoice_id,
						:fund_id,
						:email_campaign_id,
						:discounts,
						:product_expiration,
						:cancel_timestamp,
						:cancel_reason,
						:active
					)
				";
				try{
					$rsInsert = $mLink->prepare($query);
					$aValues = array(
						':member_id'			=> $_SESSION["member_id"],
						':product_id'			=> $cartInfo['product_id'],
						':bill_frequency'		=> $cartInfo['term'],
						':next_bill_timestamp'	=> $nextBillDate,
						':last_invoice_id'		=> $cartInfo['cart_id'],
						':fund_id'				=> $cartInfo['fund_id'],
						':email_campaign_id'	=> NULL,
						':discounts'			=> $_SESSION['discounts'],
						':product_expiration'	=> NULL,
						':cancel_timestamp'		=> NULL,
						':cancel_reason'		=> NULL,
						':active'				=> 1
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					//$aErrors[] = $preparedQuery;
					//echo $preparedQuery."<br>";
					$rsInsert->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					$aErrors[] = $error;
				}

			}

			// Recreate array of product IDs for searching
			$aItems = explode("|", $items);

			// Update flags for their new membership level
			$query = "
				UPDATE ".$_SESSION['members_flags_table']."
				SET trial	   		= 0,
					free			= :free,
					standard   		= :standard,
					premium		   	= :premium,
					forums		   	= :forums,
					mytrackrecord	= :mytrackrecord,
					last_chance		= 0
				WHERE member_id	= :member_id
			";
			try{
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':free'			=> (array_search('2', $aItems) === false && array_search('3', $aItems) === false ? '1' : '0'),
					':standard'		=> (array_search('2', $aItems) !== false ? '1' : '0'),
					':premium'		=> (array_search('3', $aItems) !== false ? '1' : '0'),
					':forums'		=> (array_search('6', $aItems) !== false ? '1' : '0'),
					':mytrackrecord'=> (array_search('7', $aItems) !== false ? '1' : '0'),
					':member_id'	=> $_SESSION["member_id"]
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				//$aErrors[] = $preparedQuery;
				//echo $preparedQuery;
				$rsUpdate->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				$aErrors[] = $error;
			}

			// Get the number of track records for the product ID
			$query = "
				SELECT track_records
				FROM ".$_SESSION['products_table']."
				WHERE product_id = :product_id
			";
			try{
				$rsTrackRecords = $mLink->prepare($query);
				$aValues = array(
					':product_id'	=> $product_id
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				//$aErrors[] = $preparedQuery;
				$rsTrackRecords->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$trackRecords	= $rsTrackRecords->fetch(PDO::FETCH_ASSOC);
			$max_funds		= $trackRecords['track_records'];

			// Determine if this is the total max track records or if it's cumulative
			if ($cartInfo['product_id'] > 99) {
				$max_funds = $_SESSION['max_funds'] + $max_funds;
	 		}

			// Update member record to set the right number of funds allowed
			$query = "
				UPDATE ".$_SESSION['members_table']."
				SET max_funds	= :max_funds
				WHERE member_id	= :member_id
			";
			try{
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':max_funds'	=> $max_funds,
					':member_id'	=> $_SESSION["member_id"]
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				//$aErrors[] = $preparedQuery;
				//echo $preparedQuery;
				$rsUpdate->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				$aErrors[] = $error;
			}

		// Finally, set the live session vars to reflect the new membership level
			$_SESSION['flag_trial']			= 0;
			$_SESSION['flag_free']			= (array_search('2', $aItems) === false && array_search('3', $aItems) === false ? '1' : '0');
			$_SESSION['flag_standard']		= (array_search('2', $aItems) !== false ? '1' : '0');
			$_SESSION['flag_premium']		= (array_search('3', $aItems) !== false ? '1' : '0');
			$_SESSION['flag_forums']		= (array_search('6', $aItems) !== false ? '1' : '0');
			$_SESSION['flag_mytrackrecord']	= (array_search('7', $aItems) !== false ? '1' : '0');
			$_SESSION['max_funds']			= $max_funds;

	    // ...and reload the subscription session vars

			subscription($mLink, $_SESSION['member_id'], true);

		}

		//+-------------------------------------------------------------------------
		//|							SEND EMAIL RECEIPT
		//+-------------------------------------------------------------------------

	}


	if(empty($aErrors)){ // All is well....

		//echo "Right here";

		// Clear the cart
		$_SESSION['cart'] = '';
		$_SESSION['cart_id'] = '';
		$_SESSION['cart-discounts'] = '';
		$_SESSION['merchant-discounts'] = '';
		$_SESSION['cart-total'] = '';
		$_SESSION['products'] = '';
		$_SESSION['reload_products'] = '';

		//This will trap any errors in the $aErrors array  in this section ONLY
		if($aErrors[0] != ''){
			echo '<div class="alert alert-danger"><h4>Submission Error</h4><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';
			}
			echo '</ul></div>';
		}

		//ANTHING ECHOED OR PRINTED IN THIS SECTION WILL SHOW UP ON THE CONFIRMATION PAGE

		#DISPLAY CONFIRMATION

		//echo "I'm right here!";
		//echo transactionDetails($mLink, $order_invoiceNumber, 'invoiceNumber', 'html');

		$transInfo = transactionDetails($mLink,$order_invoiceNumber,'invoiceNumber','html');
		//echo '<pre>';
		//print_r($transInfo);
		//echo '</pre>';

		echo $transInfo;




/*
		echo '<pre>';
		echo $customerProfileID.'<br /><br />';
		echo $transactionCardCode.'<br /><br />';
		echo $payMethodID.'<br /><br />';
		print_r($aProductIDs);
		echo $cartTotal;
		echo '</pre>';
*/


// Tell them it was successful and that they will receive a receipt in their email here.
// Build and send that email receipt.
// Force a refresh via JS upon close so they see their new membership level.


	}else{

		//Display errors
		echo '<div class="alert alert-danger"><h4>Submission Error</h4><ul>';
		foreach($aErrors as $key=>$error){
			echo '<li>'.$error.'</li>';
		}
		echo '</ul></div>';

	}
}


//+---------------------------------------------------------------------------------------------------------+
//|								CART PROCESS - This handles adding/removing/viewing cart entries			|
//+---------------------------------------------------------------------------------------------------------+
if($process == 'cart'){

	//START || Get and store products, if the session is older than 10 min at the time this process runs, get them anyways
	if($_SESSION['products'] == '' || $_SESSION['reload_products'] >= time()){

		//echo 'hello';

		#reset the timer
		$_SESSION['reload_products'] = (time() + 600);

		$query = "
			SELECT *
			FROM ".$_SESSION['products_table']."
			WHERE active='1'
		";
		try{
			$rsGetProducts = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $_SESSION['member_id'],
				':group_id'		=> $groupID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetProducts->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}

		while($product = $rsGetProducts->fetch(PDO::FETCH_ASSOC)){

			$_SESSION['products'][$product['product_id']] = array(
				'product_id'		=> $product['product_id'],
				'product_name'		=> $product['product_name'],
				'alt_product_name'	=> $product['alt_product_name'],
				'product_type'		=> $product['product_type'],
				'level_flag'		=> $product['level_flag'],
				'monthly_price'		=> $product['monthly_price'],
				'annual_price'		=> $product['annual_price'],
				'product_desc'		=> $product['product_desc'],
				'icon'				=> $product['icon'],
				'max_quantity'		=> $product['max_quantity']
			);
		}
	}
	//END || Get and store products

	//Get variables from selected Product
	$action 		= $_REQUEST['action'];
	$productID		= $_REQUEST['productID'];
	$term			= $_REQUEST['term'];
	$price			= $_REQUEST['price'];
	$funds			= $_REQUEST['funds'];

	//If cartID session is not set, create one
//	if(!isset($_SESSION['cart_id'])){
//		$_SESSION['cart_id'] = $_SESSION['member_id'].'-'.time();
//	}

	//Switch on the action
	switch($action){

		//+------------------------------------------------+
		//|					Add Item To Cart
		//+------------------------------------------------+
		case 'add':

			//clear discounts if any exist
			$_SESSION['cart-discounts'] = '';

			//Create DB insert function
			function cartDbInsert($mLink,$aInsertValues){
				//add item to cart DB
				$query = "
					INSERT INTO ".$_SESSION['cart_table']." (
						cart_id,
						member_id,
						product_id,
						term,
						renewal,
						sale_price,

						orig_price,
						product_name,
						alt_product_name,
						product_desc,
						fund_id,
						timestamp
					) VALUES (
						:cart_id,
						:member_id,
						:product_id,
						:term,
						:renewal,
						:sale_price,
						:orig_price,
						:product_name,
						:alt_product_name,
						:product_desc,
						:fund_id,
						UNIX_TIMESTAMP()
					)
				";
				try {
					$rsInsert = $mLink->prepare($query);
					$aValues = array(
						':cart_id'		=> $_SESSION['cart_id'],
						':member_id'	=> $_SESSION['member_id'],
						':product_id'	=> $aInsertValues['productID'],
						':term'			=> $aInsertValues['term'],
						':renewal'		=> $aInsertValues['renewal'],
						':sale_price'	=> $aInsertValues['salePrice'],
						':orig_price'	=> $aInsertValues['origPrice'],
						':product_name'	=> $aInsertValues['productName'],
						':alt_product_name'	=> $aInsertValues['alt_product_name'],
						':product_desc'	=> $aInsertValues['productDesc'],
						':fund_id'		=> $aInsertValues['fundID']
					);
					// Prepared query - for error logging and debugging
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					//echo $preparedQuery;
					$rsInsert->execute($aValues);
				}
				catch(PDOException $error){
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
			}

			//get product info
			$aProduct 			= $_SESSION['products'][$productID];
			$productType 		= $aProduct['product_type'];
			$maxQuantity		= $aProduct['max_quantity'];

			if($term == 'year'){

				$origPrice 		= $aProduct['annual_price'];
				//$renewalDate 	= strtotime('+1 year',time());
				$termDisplay	= 'Per Year';

			}elseif($term == 'month'){

				$origPrice 		= $aProduct['monthly_price'];
				//$renewalDate 	= strtotime('+1 month',time());
				$termDisplay	= 'Per Month';

			}


			if($productType == 'membership'){
				
				#get renewal date
				$renewalDate = strtotime('+12 month');
				
				switch($term){
					case 'year':
						$nextBillDate = $renewalDate;
					break;
					
					case 'month':
						$nextBillDate = strtotime('+1 month');
					break;	
				}
				
				//Check to see if cart is not empty. If it is not, clear the cart and display message to let them know upgrade items are include in memberships.

				if(in_array_r('product',$_SESSION['cart']) !== false){
					$alertType 		= 'warning';
					$alertTitle		= 'Cart Updated';
					$alertMessage	= 'Our Standard and Premium Membership come with all upgrades included. For this reason we have removed the upgrades from your cart. No need to pay for something twice!';
				}

				//clear out any other memberships or produc
				$_SESSION['cart'] = '';

				if($price < $origPrice){

					$savePrice = $origPrice - $price;

					$save = '<h5>You Save:<br /><span style="color:#47A447;">($'.number_format($savePrice,2,'.',',').')</span></h5>';

				}else{
					$save = '';
				}

				#this is an array of values to pass to the cart session and the DB insert function
				$aInsertValues = array(
					'product_id'	=> $productID,
					'type'			=> $productType,
					'term'			=> $term,
					'renewal'		=> $renewalDate,
					'sale_price'	=> $price,
					'orig_price'	=> $origPrice,
					'product_name'	=> $aProduct['product_name'].' Membership Subscription',
					'alt_product_name'	=> $aProduct['alt_product_name'],
					'product_desc'	=> $aProduct['product_desc'],
					'monthly_price'	=> $aProduct['monthly_price'],
					'save'			=> $save,
					'display_term'	=> $termDisplay,
					'nextBillDate'	=> $nextBillDate
				);
				$_SESSION['cart'][] = $aInsertValues;


			}elseif($productType == 'product'){
				
				$renewalDate = $_SESSION['subscription']['monthlyRenewalDate'];
				$nextBillDate = $_SESSION['subscription']['monthlyRenewalDate'];
				
				#Check to see if a membership is in the cart already
				if(in_array_r('membership',$_SESSION['cart']) !== true){


					if($productID == '8'/*Track Record Extension*/){

						//check the array to see if any product 8's exist, if so remove them
						foreach($_SESSION['cart'] as $key=>$aItemInfo){
							if($aItemInfo['product_id'] == '8'){
								unset($_SESSION['cart'][$key]);
							}
						}

						#check to see if any funds are selected
						if($funds == ''){
							#no funds slected, send message
							$alertType 		= 'warning';
							$alertTitle		= 'No Funds Selected';
							$alertMessage	= 'You did not select a fund to add <strong>'.$aProduct['product_name'].'</strong>. Please <a href="javascript:void(0);" data-dismiss="modal">Click Here</a> to close this window and select a fund. ';
						}else{
							#funds selected, add to cart
							foreach($funds as $key=>$fund){
								$fundInfo = explode('_', $fund);

								$_SESSION['cart'][] = array(
									'product_id'	=> $productID,
									'type'			=> $productType,
									'term'			=> $term,
									'renewal'		=> $renewalDate,
									'sale_price'	=> $price,
									'orig_price'	=> $origPrice,
									'alt_product_name'	=> $aProduct['alt_product_name'],
									'product_name'	=> $aProduct['product_name'].' for: '.$fundInfo[1],
									'product_desc'	=> $aProduct['product_desc'],
									'fund_id'		=> $fundInfo[0],
									'display_term'	=> $termDisplay,
									'nextBillDate'	=> $nextBillDate
								);
							}
						}

					}else{

						//check cart to see if item already exists
						$skipItem = false;

						foreach($_SESSION['cart'] as $key=>$aItemInfo){
							if($productID == $aItemInfo['product_id']){
								$skipItem = true;
							}
						}

						if($skipItem == false){
							#no membership add the product
							$_SESSION['cart'][] = array(
								'product_id'	=> $productID,
								'type'			=> $productType,
								'term'			=> $term,
								'renewal'		=> $renewalDate,
								'sale_price'	=> $price,
								'orig_price'	=> $origPrice,
								'alt_product_name'	=> $aProduct['alt_product_name'],
								'product_name'	=> $aProduct['product_name'],
								'product_desc'	=> $aProduct['product_desc'],
								'display_term'	=> $termDisplay
							);
						}
					}

				}else{

					#there is a membership, do not add the product, instead send notification
					$alertType 		= 'warning';
					$alertTitle		= 'Membership Detected';
					$alertMessage	= 'We noticed that you have a membership in your cart already. All upgrades are included in our Standard and Premium Memberships. If you would rather use upgrades, please remove the membership from your cart.';
				}




			}



		break;


		//+------------------------------------------------+
		//|				Remove Item To Cart
		//+------------------------------------------------+
		case 'remove':

			//In the case of REMOVE: the following variables are repurposed:
			/*
			ProductID 	= Cart Array Key
			Term		= The Item Name
			*/

			$cartKey = $productID;
			$itemName	= $term;

			#remove item from cart session
			unset($_SESSION['cart'][$cartKey]);

			$noteType 		= 'info';
			$noteMessage	= 'You have successfully removed <strong>'.$itemName.'</strong> from your cart.';

		break;


		//+------------------------------------------------+
		//|					Clear Cart
		//+------------------------------------------------+
		case 'clear':
			//echo 'hello';
			#clear seesion
			$_SESSION['cart'] = '';
			$_SESSION['cart-discounts'] = '';
			$_SESSION['merchant-discounts'] = '';
			$_SESSION['cart-total'] = '';
			$_SESSION['products'] = '';
			$_SESSION['reload_products'] = '';

		break;
	}

	//Start || calculate subtotal
	$subTotal = 0;

	foreach($_SESSION['cart'] as $key=>$aCartItems){
		$subTotal = $subTotal + $aCartItems['sale_price'];
	}

	if($subTotal == 0){
		$numItems = 0;
	}else{
		$numItems = count($_SESSION['cart']);
	}

	//GET monthly price of standard membership
	/*need to do a db query | DONT FORGET WHEN YOU COME BACK AND READ THIS AGAIN | replace the 49 with a variable*/
	if(in_array_r('product',$_SESSION['cart']) !== false){
		if($subTotal > 49){

			$noteType = 'info';
			$noteMessage = 'Note: You could save $'.($subTotal - 49).' a month by signing up for a Standard Membership! It includes all of the available upgrdaes and more!';
		}
	}
	//END || Calculate Subtotal

	//START || DISPLAY CART
	?>
    <div class="cart-container">

        <?php if($alertType != ''){?>
        <div class="alert alert-<?php echo $alertType;?>">
        	<h4><?php echo $alertTitle;?></h4>
            <p><?php echo $alertMessage;?></p>
        </div><!--alert-->
        <?php } ?>

        <?php if($noteType != ''){?>
        <div class="note note-<?php echo $noteType;?>">
        	<p><?php echo $noteMessage;?></p>
        </div><!--note-->
        <?php } ?>

        <div class="cart-header">
            <div class="row">

                <div class="col-md-8">
                    <h3>Items</h3>
                </div><!--col-md-8-->

                <div class="col-md-2" style="text-align:right;">
                    <h4>Price</h4>
                </div><!--col-md-4-->
				
                <div class="col-md-2 hidden-xs hidden-sm">
                </div>
                
            </div><!--row-->
        </div><!--cart-header-->

        <?php
		foreach($_SESSION['cart'] as $key=>$aCartItem){

			?>
            <!--START ITEM-->
            <div class="cart-item">
                <div class="row">

                    <div class="col-md-8">
                        <div class="cart-item-info">
                            <h4><?php echo $aCartItem['product_name'];?></h4>
                            <h5><?php echo ucfirst($aCartItem['type']);?> Renewal Date: <?php echo date('m/d/Y', $aCartItem['renewal']);?></h5>
                            <h5>Next Bill Date: <?php echo date('m/d/Y', $aCartItem['nextBillDate']);?></h5>
                            <p><?php echo $aCartItem['product_desc'];?><p>
                        </div><!--cart-item-info-->
                    </div><!--col-md-8-->

                    <div class="col-md-2"  style="text-align:right;">
                        <div class="cart-price" style="text-align:right;">
                            <h4>$<?php echo number_format($aCartItem['sale_price'],2,'.',',');?></h4>
                            <?php echo $aCartItem['display_term'];?><br />
							<?php echo $aCartItem['save'];?>
                        </div><!--cart-price-->
                    </div><!--col-md-1-->

                    <div class="col-md-2">
                        <div class="cart-remove">
                            <a href="javascript:void(0);" onclick="cart('remove','<?php echo $key;?>','<?php echo $aCartItem['product_name'];?>','');"><i class="icon-remove-sign"></i> Remove</a>
                        </div><!--cart-remove-->
                    </div><!--col-md-1-->

                </div><!--row-->
            </div><!--cart-item-->
            <!--END ITEM-->
            <?php


		}//end for each cart

		if($numItems == 0){
			?>
            <!--START ITEM-->
            <div class="cart-item">
                <div class="row">

                    <div class="col-md-8">
                        <div class="cart-item-info">
                            <h4>You have no items in your cart.</h4>

                        </div><!--cart-item-info-->
                    </div><!--col-md-8-->

                </div><!--row-->
            </div><!--cart-item-->
            <!--END ITEM-->
            <?php
		}
		?>

        <div class="cart-subtotal">
            <div class="row">
                <div class="col-md-8">
                    <h3>Subtotal (<?php echo $numItems;?> item<?php if($numItems > 1 || $numItmes == 0){echo 's';}?>):</h3>
                </div><!--col-md-8-->
                <div class="col-md-2" style="text-align:right;">
                    <h4>$<?php echo number_format($subTotal,2,'.',',');?></h4>
                </div><!--col-md-2-->
            </div><!--row-->
       </div><!--cart-subtotal-->

    </div><!--cart-container-->
    <?php
	//END || DISPLAY CART

}


//+---------------------------------------------------------------------------------------------------------+
//|		CART ITEM COUNT PROCESS - used to update cart quanties in various places throughout the site		|
//+---------------------------------------------------------------------------------------------------------+
if($process == 'cart-items'){

	$subTotal = 0;

	foreach($_SESSION['cart'] as $key=>$aCartItems){
		$subTotal = $subTotal + $aCartItems['sale_price'];
	}

	if($subTotal == 0){
		$numItems = 0;
	}else{
		$numItems = count($_SESSION['cart']);
	}

	echo $numItems.'|'.$subTotal;

}


//+---------------------------------------------------------------------------------------------------------+
//|								CHECKOUT STEP THROUGH PROCESS												|
//+---------------------------------------------------------------------------------------------------------+
if($process == 'checkout-step'){

	$setStep = $_REQUEST['setStep'];

	if($setStep != ''){
		$_SESSION['checkout-step'] = $setStep;
	}

	$step = $_SESSION['checkout-step'];

	$numItems = 0;
	foreach($_SESSION['cart'] as $key=>$item){
		$numItems++;
	}

	if($numItems == 0){
		$display = 'hide';
	}

	switch($step){
		#Cart
		case 0:
			$prevButton = '<button type="button" class="btn btn-default" style="float:left;" data-dismiss="modal">Return To Site</button><button id="clear-cart" type="button" class="btn btn-warning" onclick="cart(\'clear\',\'\',\'\',\'\');" style="float:left;" data-dismiss="modal">Empty Cart</button> ';
			$nextButton = '<button type="button" class="btn btn-info '.$display.'" id="checkout-btn" onclick="checkout();">Checkout</button>';
		break;

		#Order Summary
		case 1:
			$prevButton = '';
			$nextButton = '<a onclick="checkoutStep(\'2\');" class="accordion-toggle btn btn-info" data-toggle="collapse" data-parent="#checkout_parent" href="#checkout-discounts">Continue To Discounts</a>';

		break;

		#Discounts
		case 2:
			$prevButton = '<a onclick="checkoutStep(\'1\');" class="accordion-toggle btn btn-default" style="float:left;" data-toggle="collapse" data-parent="#checkout_parent" href="#checkout-summary">Back</a>';
			$nextButton = '<a onclick="checkoutStep(\'3\');" class="accordion-toggle btn btn-info" data-toggle="collapse" data-parent="#checkout_parent" href="#checkout-payment">Continue To Payment Methods</a>';
		break;

		#Payment Method
		case 3:
			$prevButton = '<a onclick="checkoutStep(\'2\');" class="accordion-toggle btn btn-default" style="float:left;" data-toggle="collapse" data-parent="#checkout_parent" href="#checkout-discounts">Back</a>';
			$nextButton = '<a onclick="checkoutStep(\'4\');" class="accordion-toggle btn btn-info" data-toggle="collapse" data-parent="#checkout_parent" href="#checkout-review">Review Order</a>';
		break;

		#Review Order
		case 4:
			$prevButton = '<a onclick="checkoutStep(\'3\');" class="accordion-toggle btn btn-default" style="float:left;" data-toggle="collapse" data-parent="#checkout_parent" href="#checkout-payment">Back</a>';
			$nextButton = '<button onclick="placeOrder();" class="btn btn-success">Place Order</button>';
		break;
	}

	echo $prevButton.$nextButton;
}



//+---------------------------------------------------------------------------------------------------------+
//|										CHECKOUT Process													|
//+---------------------------------------------------------------------------------------------------------+
if($process == 'checkout'){

	$subTotal = 0;

	foreach($_SESSION['cart'] as $key=>$aCartItems){
		$subTotal = $subTotal + $aCartItems['sale_price'];
		$_SESSION['cart-subtotal'] = $subTotal;
	}

	if($subTotal == 0){
		$numItems = 0;
	}else{
		$numItems = count($_SESSION['cart']);
	}

	if(!isset($_SESSION['checkout-step'])){
		$_SESSION['checkout-step'] = 2;
	}



	?>
    <div class="panel-group accordion" id="checkout_parent">

        <div class="panel panel-default">

            <div class="panel-heading accordion-fix">
                <h4 class="panel-title">
                    <a class="accordion-toggle" onclick="checkoutStep('1');" data-toggle="collapse" data-parent="#checkout_parent" href="#checkout-summary" style="height:40px;<?php echo $barColor;?>">

                        <strong>1&nbsp;&nbsp;&nbsp;Order Summary</strong>
                        <span class="accordion-icons accordion-expand"></span><span style="float:right;margin-top:5px;margin-right:5px;"><?php echo $numItems;?> Item<?php echo ($numItems == 1 ? "" : "s");?> | Subtotal: <strong>$<?php echo money_format("%n", $subTotal);?></strong></span><span style="float:right;display:inline;padding-right:10px;"> <?php echo $barButton;?></span>
                    </a>
                </h4>
            </div>
            <div id="checkout-summary" class="panel-collapse in">
                <div class="panel-body">

                	<div class="cart-header">
                        <div class="row">

                            <div class="col-md-7">
                                <h3>Items</h3>
                            </div><!--col-md-8-->

                            <div class="col-md-3" style="text-align:right;">
                                <h4>Price</h4>
                            </div><!--col-md-4-->
							
                            <div class="col-md-2 hidden-xs hidden-sm">
                            </div>
                            
                        </div><!--row-->
                    </div><!--cart-header-->

                    <?php
                    foreach($_SESSION['cart'] as $key=>$aCartItem){

                        ?>
                        <!--START ITEM-->
                        <div class="cart-item">
                            <div class="row">

                                <div class="col-md-7">
                                    <div class="cart-item-info">
                                        <h4><?php echo $aCartItem['product_name'];?></h4>
                                        <h5>Renewal Date: <?php echo date('m/d/Y', $aCartItem['renewal']);?></h5>
                                        <p><?php echo $aCartItem['product_desc'];?><p>
                                    </div><!--cart-item-info-->
                                </div><!--col-md-8-->

                                <div class="col-md-3">
                                    <div class="cart-price" style="text-align:right;">
                                        <h4>$<?php echo money_format("%n", $aCartItem['sale_price']);?></h4>
                                        <?php echo $aCartItem['display_term'];?><br />
                                        <?php echo $aCartItem['save'];?>
                                    </div><!--cart-price-->
                                </div><!--col-md-1-->

                                <div class="col-md-2">
                                    <div class="cart-remove">
                                        <a href="javascript:void(0);" onclick="cart('remove','<?php echo $key;?>','<?php echo $aCartItem['product_name'];?>','');"><i class="icon-remove-sign"></i> Remove</a>
                                    </div><!--cart-remove-->
                                </div><!--col-md-1-->

                            </div><!--row-->
                        </div><!--cart-item-->
                        <!--END ITEM-->
                        <?php


                    }//end for each cart

					?>

                    <div class="cart-subtotal">
                        <div class="row">
                            <div class="col-md-8">
                            	<h3>Subtotal (<?php echo $numItems;?> item<?php if($numItems > 1 || $numItmes == 0){echo 's';}?>):</h3>
                            </div><!--col-md-8-->
                            <div class="col-md-2" style="text-align:right;">
                            	<h4>$<?php echo number_format($subTotal,2,'.',',');?></h4>
                            </div><!--col-md-2-->
                        </div><!--row-->
                    </div><!--cart-subtotal-->

                </div><!--panel-body-->
            </div><!--panel-collapse-->

    	</div><!--panel-->

        <div class="panel panel-default">

            <div class="panel-heading accordion-fix">
                <h4 class="panel-title">
                    <a class="accordion-toggle" onclick="checkoutStep('2');" data-toggle="collapse" data-parent="#checkout_parent" href="#checkout-discounts" style="height:40px;<?php echo $barColor;?>">

                        <strong>2&nbsp;&nbsp;&nbsp;Discounts</strong>
                        <span class="accordion-icons accordion-expand"></span><span style="float:right;display:inline;padding-right:10px;"> <?php echo $barButton;?></span>
                    </a>
                </h4>
            </div>
            <div id="checkout-discounts" class="panel-collapse collapse">
                <div class="panel-body">

                	<h4>Apply Discount Code</h4>
                    <p>Do you have a discount code? Enter it below and click apply. If you do not have a discount code continue on to payment methods.</p>

                    <form action="process/ajax/user-membership-processes.php?process=apply-discount" method="post" class="discount-form">
                    	<div class="input-group">
                        	<input type="text" class="form-control" name="discount-code" placeholder="Enter code" id="discount-code"/>
                            <span class="input-group-btn">
                            	<input type="submit" class="btn btn-info" value="Apply" />
                            </span>
                        </div>
                    </form>

                    <div id="discount-area"></div>



                </div><!--panel-body-->
            </div><!--panel-collapse-->

    	</div><!--panel-->

        <div class="panel panel-default">

            <div class="panel-heading accordion-fix">
                <h4 class="panel-title">
                    <a class="accordion-toggle" onclick="checkoutStep('3');" data-toggle="collapse" data-parent="#checkout_parent" href="#checkout-payment" style="height:40px;<?php echo $barColor;?>">

                        <strong>3&nbsp;&nbsp;&nbsp;Payment Method</strong>
                        <span class="accordion-icons accordion-expand"></span><span style="float:right;display:inline;padding-right:10px;"> <?php echo $barButton;?></span>
                    </a>
                </h4>
            </div>
            <div id="checkout-payment" class="panel-collapse collapse">
                <div class="panel-body">

					<h4 style="float:left;">Select Payment Method</h4>
                    <h5 style="float:right;padding-right:20px;">Expires on</h5>
                    <div class="clearfix"></div>

                    <div id="load-checkout-payments">

                    </div><!--load-checkout-payments-->
                	<a class="btn btn-info"  data-toggle="modal" onclick="newPayment('payment2')" href="#new-card2" style="margin-top:10px;">Add New Payment Method</a>
                </div><!--panel-body-->
            </div><!--panel-collapse-->

    	</div><!--panel-->

        <div class="panel panel-default">

            <div class="panel-heading accordion-fix">
                <h4 class="panel-title">
                    <a class="accordion-toggle" onclick="checkoutStep('4');" data-toggle="collapse" data-parent="#checkout_parent" href="#checkout-review" style="height:40px;<?php echo $barColor;?>">

                        <strong>4&nbsp;&nbsp;&nbsp;Review Order</strong>
                        <span class="accordion-icons accordion-expand"></span><span style="float:right;display:inline;padding-right:10px;"> <?php echo $barButton;?></span>
                    </a>
                </h4>
            </div>
            <div id="checkout-review" class="panel-collapse collapse">
                <div class="panel-body">

                	<h3>Review Your Order Information</h3>
                	<div id="load-review-order"></div>

                </div><!--panel-body-->
            </div><!--panel-collapse-->

    	</div><!--panel-->


    </div><!--panel-group-->
    <?php

}


//+---------------------------------------------------------------------------------------------------------+
//|			REVIEW ORDER PROCESS	 - this is loaded when you click the review order step					|
//+---------------------------------------------------------------------------------------------------------+
if($process == 'load-review-order'){

	$payMethodID 		= $_REQUEST['payMethodID'];
	$aDiscounts			= $_SESSION['cart-discounts'];
	$aCart				= $_SESSION['cart'];
	
	//print_r($aCart);

	//Check to see if there is a valid payment method ID
	if($payMethodID != ''){
		#valid payment id continue

		#get payment method details
		$query = "
			SELECT *
			FROM ".$_SESSION['payment_profiles_table']."
			WHERE uid=:uid
		";
		try{
			$rsGetPaymentInfo = $mLink->prepare($query);
			$aValues = array(
				':uid'	=> $payMethodID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetPaymentInfo->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$paymentInfo = $rsGetPaymentInfo->fetch(PDO::FETCH_ASSOC);

		/*PLEASE FINISH ADDING WHICH PAYMENT METHOD AND TERM IS GOING TO BE APPLIED TO THIS ORDER UNDER ORDER TOTAL*/

		$state 			= $paymentInfo['billTo_state'];
		$cardType		= $paymentInfo['cardType'];
		$cardLastFour	= $paymentInfo['cardLastFour'];
		$applyTax		= false;

		if(strtolower($state) == 'tx' || strtolower($state) == 'texas'){
			$applyTax = true;
			$taxRate = .0825;
			
		}

		?>

        <div id="load-order-processing-errors"></div>

        <div class="cart-header">
            <div class="row">

                <div class="col-md-9">
                    <h4>Items</h4>
                </div><!--col-md-8-->

                <div class="col-md-3 hidden-xs hidden-sm" style="text-align:right;">
                    <h4>Price</h4>
                </div><!--col-md-4-->

            </div><!--row-->
        </div><!--cart-header-->
        <?php
		$subTotal		= 0;
		$numItems 		= 0;
		$aProductIDs	= array();

		// Is there already a cart?
		if (isset($_SESSION['cart_id'])){

			// Delete old cart
			$query = "
				DELETE FROM ".$_SESSION['cart_table']."
				WHERE cart_id = :cart_id
			";
			try{
				$rsDelete = $mLink->prepare($query);
				$aValues = array(
					':cart_id'	=> $_SESSION['cart_id']
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsDelete->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}

		}

		// Create a cart_id that will eventually become the invoice number
		$_SESSION['cart_id'] = $_SESSION["member_id"]."-".time();

		//step through each cart item and determine if there are any applied discounts for it
		foreach($aCart as $key=>$aProductInfo){

			#set vars
			$productID 		= $aProductInfo['product_id'];
			$aProductIDs[]	= $productID;
			$productName	= $aProductInfo['product_name'];
			$altProductName	= $aProductInfo['alt_product_name'];
			$salePrice		= $aProductInfo['sale_price'];
			$origPrice		= $aProductInfo['orig_price'];
			$renewalDate	= $aProductInfo['renewal'];
			$term			= $aProductInfo['term'];
			$productDesc	= $aProductInfo['product_desc'];
			$fundID			= $aProductInfo['fund_id'];
			
			//echo 'hello'.$aProductInfo['alt_product_name'];
			
			switch($term){
				case 'year': $term = 'Annually';break;
				case 'month': $term = 'Monthly';break;
			}

			$newPrice 		= $salePrice;
			$discountCheck	= false;
			$aDiscountIDs	= array();

			$numItems++;

			#look for discounts that are applied to this product only
			foreach($aDiscounts['product'] as $key=>$aDiscountInfo){

				//Check to see if product ids match, if not skip to the next
				if($productID != $aDiscountInfo['productID']){
					#go to next item
					continue;
				}
				$discountCheck		= true;
				$discountID			= $aDiscountInfo['discountID'];
				$discountDuration	= $aDiscountInfo['duration'];
				$opperator 			= $aDiscountInfo['opperator'];
				$amount				= $aDiscountInfo['amount'];
				$discountName		= $aDiscountInfo['name'];

				$aDiscountIDs[$discountID] 	= $discountDuration;

				switch($opperator){
					case 'percent':
						$discount 		= (1 - ($amount /100));
						$newPrice 		= $salePrice * $discount;
						//$discountTxt	= $amount.'% Off - $'.$salePrice;
					break;

					case 'dollar':
						$newPrice 		= $salePrice - $amount;
						//$discountTxt	= '$'.$amount.' Off - $'.$salePrice;
					break;
				}
			}

			if($discountCheck == true){
				$showDiscountTxt 	= '<p style="color:#5CB85C;margin:0px;padding:0px;">'.$discountName.' Discount Applied</p>';
				$showCostSavings	= '<h4 style="color:#5CB85C;margin:0px;padding:0px;">$'.number_format($newPrice,2,'.',',').'</h4><p>('.$term.')</p>';
				$oldPrice			= '<h5><strike>$'.number_format($salePrice,2,'.',',').'</strike></h5>';
			}else{
				$oldPrice			= '<h4>$'.number_format($salePrice,2,'.',',').'</h4><p>('.$term.')</p>';
				$showDiscountTxt 	= '';
				$showCostSavings	= '';
			}

			$subTotal 				= $subTotal + $newPrice;


			?>
            <!--START ITEM-->
            <div class="cart-item" style="padding: 10px 0px;">
                <div class="row">

                    <div class="col-md-9">
                        <div class="cart-item-info">
                            <h5 style="color:#5BC0DE;margin-bottom:5px;"><strong><?php echo $productName;?></strong> </h5>
                            <p style="margin:0px;">Renewal Date: <?php echo date('m/d/Y', $renewalDate);?></p>
                            <?php echo $showDiscountTxt;?>
                        </div><!--cart-item-info-->
                    </div><!--col-md-8-->

                    <div class="col-md-3">
                        <div class="cart-price"  style="text-align:right;">
                            <?php echo $oldPrice;?>
                            <?php echo $showCostSavings;?>
                        </div><!--cart-price-->
                    </div><!--col-md-1-->

                </div><!--row-->
            </div><!--cart-item-->
            <!--END ITEM-->
            <?php
			
			if($applyTax == false){
				$applyTax = 0;
				$taxRate = 0;	
			}
			
			// Insert new cart record
			$query = "
				INSERT INTO ".$_SESSION['cart_table']."(
					cart_id,
					member_id,
					product_id,
					term,
					renewal,
					sale_price,
					orig_price,
					taxable,
					tax_rate,
					product_name,
					alt_product_name,
					product_desc,
					fund_id,
					timestamp
				) VALUES (
					:cart_id,
					:member_id,
					:product_id,
					:term,
					:renewal,
					:sale_price,
					:orig_price,
					:taxable,
					:tax_rate,
					:product_name,
					:alt_product_name,
					:product_desc,
					:fund_id,
					UNIX_TIMESTAMP()
				)
			";
			try{
				$rsInsert = $mLink->prepare($query);
				$aValues = array(
					':cart_id'		=> $_SESSION["cart_id"],
					':member_id'	=> $_SESSION["member_id"],
					':product_id'	=> $productID,
					':term'			=> $term,
					':renewal'		=> $renewalDate,
					':sale_price'	=> $newPrice,
					':orig_price'	=> $salePrice,
					':taxable'		=> $applyTax,
					':tax_rate'		=> $taxRate,
					':product_name'	=> $productName,
					':alt_product_name'	=> $altProductName,
					':product_desc'	=> $productDesc,
					':fund_id'		=> $fundID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				//$aErrors[] = $preparedQuery;
				//echo $preparedQuery;
				$rsInsert->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				$aErrors[] = $error;
			}

		}#end foreach product

		$discountAdjustedPrice 	= $subTotal;
		$subTotalDiscount		= false;

		#Loop through subtotal discounts
		foreach($aDiscounts['subtotal'] as $key=>$aDiscountInfo){

			$subTotalDiscount 	= true;

			$discountID			= $aDiscountInfo['discountID'];
			$discountDuration	= $aDiscountInfo['duration'];
			$opperator 			= $aDiscountInfo['opperator'];
			$amount				= $aDiscountInfo['amount'];
			$discountName		= $aDiscountInfo['name'];

			$aDiscountIDs[$discountID] 	= $discountDuration;

			switch($opperator){
				case 'percent':
					$discount 					= (1 -($amount /100));
					$discountAdjustedPrice		= ($discountAdjustedPrice * $discount);

				break;

				case 'dollar':
					$discountAdjustedPrice 		= $discountAdjustedPrice - $amount;
				break;
			}
		}

		$discountTotal 	= $subTotal - $discountAdjustedPrice;

		//Calculate TEXAS state/county sales tax (8.25%)
		$salesTax 		= ($discountAdjustedPrice * .0825);

		if($applyTax == true){
			$orderTotal 	= $discountAdjustedPrice + $salesTax;
		}else{
			$orderTotal 	= $discountAdjustedPrice;
		}
		?>
		
        <?php if($_REQUEST['debug'] == '1' || $_SESSION['debug'] == '1'){?>
        <div class="alert alert-danger">
        	<pre>
            	<?php print_r($aErrors);?>
            </pre>
        </div>
		<?php }?>
		
        
        
		<div class="cart-subtotal">
			<div class="row">
				<div class="col-md-9" style="text-align:right;">
					<h4>Subtotal (<?php echo $numItems;?> item<?php if($numItems > 1 || $numItems == 0){echo 's';}?>):</h4>
				</div><!--col-md-8-->
				<div class="col-md-3" style="text-align:right;">
					<h4>$<?php echo number_format($subTotal,2,'.',',');?></h4>
				</div><!--col-md-2-->
			</div><!--row-->

            <?php if($subTotalDiscount == true){?>
            <div class="row">
				<div class="col-md-9" style="text-align:right;">
					<h4 style="padding:0px;margin:0px 0px 10px 0px;font-size:16px;">Discounts:</h4>
				</div><!--col-md-8-->
				<div class="col-md-3" style="text-align:right;">
					<h4 style="padding:0px;margin:0px;font-size:16px;color:#5CB85C;">- $<?php echo number_format($discountTotal,2,'.',',');?></h4>
				</div><!--col-md-2-->
			</div><!--row-->
            <?php } ?>

            <?php if($applyTax == true){?>
            <div class="row">
				<div class="col-md-9" style="text-align:right;">
					<h4 style="padding:0px;margin:0px;">Texas Sales Tax:</h4>
				</div><!--col-md-8-->
				<div class="col-md-3" style="text-align:right;">
					<h4 style="padding:0px;margin:0px;">$<?php echo number_format($salesTax,2,'.',',');?></h4>
				</div><!--col-md-2-->
			</div><!--row-->
            <?php }?>

            <div class="row" style="border-top:1px solid #999999;margin-top:10px;">
				<div class="col-md-9" style="text-align:right;">
					<h4 style="padding:0px;margin:10px 0px 0px 0px;">Order Total:</h4>
				</div><!--col-md-8-->
				<div class="col-md-3" style="text-align:right;">
					<h4 style="padding:0px;margin:10px 0px 0px 0px;">$<?php echo number_format($orderTotal,2,'.',',');?></h4>
				</div><!--col-md-2-->
			</div><!--row-->
		</div><!--cart-subtotal-->

        <form action="" method="post" id="submit-cart-order">
        	<input type="hidden" name="payment_method_id" value="<?php echo $payMethodID;?>" />
            <input type="hidden" name="product_ids" value="<?php echo implode('|',$aProductIDs);?>" />
            <input type="hidden" name="cart_total" value="<?php echo $orderTotal;?>" />
            <input type="hidden" name="renewal_date" value="<?php echo $renewalDate;?>" />
            <input type="hidden" name="term" value="<?php echo $term;?>" />
        	<input type="hidden" name="cart_id" value="<?php echo $_SESSION["cart_id"];?>" />
        </form>
		<?php

	}else{
		#no valid payment id
		?>
        <div class="alert alert-danger">
        	<h4>You did not select a method of payment!</h4>
            <p>Please go back and select a method of payment.</p>
        </div>
        <?php

	}


	$_SESSION['merchant-discounts'] = $aDiscountIDs;
	//echo '<pre>';
	//print_r($aCart);
	//print_r($_SESSION['merchant-discounts']);
	//echo '</pre>';
}


//+---------------------------------------------------------------------------------------------------------+
//|			Load payment methods Process	 - this loads the payment methods in the checkout process		|
//+---------------------------------------------------------------------------------------------------------+
if($process == 'load-checkout-payment-methods'){

	$selectCard = $_REQUEST['selectCard'];

	if($selectCard == 'new'){
		$orderBy = 'ORDER BY created_timestamp DESC';	
	}else{
		$orderBy = '';	
	}
	
	$query = "
		SELECT *
		FROM ".$_SESSION['payment_profiles_table']."
		WHERE member_id=:member_id
		".$orderBy."
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
	
	$cntPaymentMethods = $rsGetPayments->rowCount();
	
	$loopCnt = 0;								
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
		$defaultMethod		= $pay['default_method'];
		$expYear			= date('Y', $cardExpiration);
		$expMonth			= date('m', $cardExpiration);
		$cardExpired		= false;
		
		if($expYear < date('Y')){
			$cardExpired = true;
		}elseif($expYear == date('Y')){
			if($expMonth < date('m')){
				$cardExpired = true;
			}
		}
		
		if($cardExpired == false){
		
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
					'country'		=> $country
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
			
			$loopCnt++;
			
			if($selectCard == 'new'){
				if($loopCnt == 1){
					$checked = 'checked="checked"';	
				}else{
					$checked = '';	
				}
			}else{
				if($cntPaymentMethods == 1){
					$checked = 'checked="checked"';	
				}else{
					if($defaultMethod == '1'){
						$checked = 'checked="checked"';	
					}else{
						$checked = '';
					}
				}
			}
			
			//store billing info into a session for use in the processing files
			$_SESSION['aBillingAddresses'] = $aBillingAddress;
			?>
			<div class="panel panel-default payment-container">
				<div class="panel-heading accordion-fix">
					<h4 class="panel-title">
						<a class="accordion-toggle">
							<span><input type="radio" name="payment-method" value="<?php echo $pay['uid'];?>" class="payment-radio" <?php echo $checked;?> /></span>
							<span><img src="images/payments/<?php echo $cardImg;?>" alt="<?php echo $cardType;?>" width="40"/></span>
							<span style="position:relative;top:2px;padding-left:5px;"><?php echo $cardType;?> ending in <?php echo $cardLast4;?> </span>
							<span style="float:right;position:relative;top:5px;right:10px;"><strong><?php echo $cardExpire;?></strong></span>
						</a>
					</h4>
					
				</div>
				
			</div><!--panel-->
			<?php
			
		}#card expired check		
	}#while loop	
}#process check


//+---------------------------------------------------------------------------------------------------------+
//|	Apply Discounts Process - This process takes the code entered in the form and applies it if it is valid		
//+---------------------------------------------------------------------------------------------------------+

if($process == 'apply-discount'){
	
	$code 				= $_REQUEST['discount-code'];
	$aCartProductIDs	= array();
	$loadDiscounts		= $_REQUEST['loadDiscounts']; /*this is used to trigger the loading of the discounts*/
	
	$subTotal			= 0;
	foreach($_SESSION['cart'] as $key=>$aCartItems){
		$subTotal = $subTotal + $aCartItems['sale_price'];
		$aCartProductIDs[] = $aCartItems['product_id'];	
	}
	
	$_SESSION['cart-total'] = $subTotal; /* I dont think I use this anymore, come back and do research to see if this session is used*/
	
	//check to see if we just need to load the discounts
	if($loadDiscounts != '1'){
	
		//START | Check for valid discount
		$query = "
			SELECT *
			FROM ".$_SESSION['discounts_table']."
			WHERE discount_code=:code AND active=1
		";
		try{
			$rsDiscount = $mLink->prepare($query);
			$aValues = array(
				':code'	=> $code
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsDiscount->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$discount = $rsDiscount->fetch(PDO::FETCH_ASSOC);
		$cntDiscount = $rsDiscount->rowCount();	
	
		//Determin if discount is valid
		$validDiscount = false;
		
		//first check to see if there is even a result in the result set
		if($cntDiscount > 0){
									
			#get the experation date of the discount
			$experation 	= $discount['endDate'];
			$startDate		= $discount['startDate'];
					
			#check to see if today is before the experation date, or if there is no experation date
			if(time() < $experation && time() > $startDate || $experation == '0' && time() > $startDate){
				
				#check to see if this discount is only for a particular member
				if($discount['member_id'] != NULL){
					#this discount is for a member
					
					#if there is a particular member make sure this is the right member
					if($_SESSION['member_id'] == $discount['member_id']){
						
						#this discount is for the member, mark as valid
						$validDiscount = true;	
					}
					
				}else{
					#discount is for anyone that knows it, mark as valid
					$validDiscount = true;	
				}
			}
		}
	
		if($validDiscount == true){
			#set vars for discount
			
			$multiple			= $discount['multiple']; 
			$allowOnce			= $discount['allow_once'];
			$discountID			= $discount['discount_id'];
			$discountProductID	= $discount['product_id'];
			$discountCode		= $discount['discount_code'];
			$discountName		= $discount['name'];
			$discountDesc		= $discount['desc'];
			$discountAmount		= $discount['amount'];
			$discountOpperator	= $discount['discount_type'];
			$discountDuration	= $discount['duration'];
			
			//Check to see if member has applied discount before, if it even matters
			$applyDiscount = false; //assume it wont pass
			
			if($allowOnce == 1){
				
				#check the applied discounts table to see if the member has used this discount before
				$query = "
					SELECT *
					FROM ".$_SESSION['applied_discounts_table']."
					WHERE member_id=:member_id AND discount_id=:discount_id
				";
				try{
					$rsAppliedDiscount = $mLink->prepare($query);
					$aValues = array(
						':member_id'	=> $_SESSION['member_id'],
						':discount_id'	=> $discountID
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsAppliedDiscount->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				$numDiscounts = $rsAppliedDiscount->rowCount();	
				
				//if there are zero results allow the discount to be applied
				if($numDiscounts == 0){
					$applyDiscount = true;	
				}else{
					$noteType 		= 'warning';
					$noteMessage	= 'You have already used the Discount Code: <strong>'.$discountCode.'</strong>. This discount may only be used once.';
				}
				
			}else{
				#discount can be used unlimited amount of times, apply it
				$applyDiscount = true;
			}
			
			if($applyDiscount == true){
				
				#check to see if this discount is allowed to work with others, if not clear all discounts
				if($multiple == '0'){
					#multiple discounts not allowed, clear discounts
					$_SESSION['cart-discounts'] = '';
				}else{
					#multiple discounts allowed, check to see if there are any discounts that do not allow multiple discounts
					foreach($_SESSION['cart-discounts'] as $discountType=>$aDiscounts){
						#loop through each discount type
						
						foreach($aDiscounts as $key=> $aDiscountInfo){
							
							#check to see if it allows multiples
							if($aDiscountInfo['multiple'] == '0'){
								
								#doesnt allow multiples, unset from the array
								unset($_SESSION['cart-discounts'][$discountType][$key]);
								
								$noteType 		= 'warning';
								$noteMessage	= 'The Discount Code: <strong>'.$aDiscountInfo['code'].'</strong> has been removed as it can not be used with other discounts.';	
							}
						}
					}
				}
				
				//Check to see if discount can only be applied to a specific product
				if($discountProductID == NULL){
					#apply to subtotal
					$_SESSION['cart-discounts']['subtotal'][$discountID] = array(
						'discountID'	=> $discountID,
						'productID'		=> $discountProductID,
						'code'			=> $discountCode,
						'name'			=> $discountName,
						'desc'			=> $discountDesc,
						'opperator'		=> $discountOpperator,
						'amount'		=> $discountAmount,
						'multiple'		=> $multiple,
						'duration'		=> $discountDuration
					);
				}else{
					#apply to individual product
					if(in_array($discountProductID, $aCartProductIDs)){
						
						if(array_key_exists($discountProductID, $_SESSION['cart-discounts']['product'])){
							$noteType 		= 'warning';
							$noteMessage	= 'Product Discount Code: <strong>'.$_SESSION['cart-discounts']['product'][$discountProductID]['code'].'</strong> has been removed. Only one Product Discount Code can be used with any one product at a time. ';
						}
						
						$_SESSION['cart-discounts']['product'][$discountProductID] = array(
							'discountID'	=> $discountID,
							'productID'		=> $discountProductID,
							'code'			=> $discountCode,
							'name'			=> $discountName,
							'desc'			=> $discountDesc,
							'opperator'		=> $discountOpperator,
							'amount'		=> $discountAmount,
							'multiple'		=> $multiple,
							'duration'		=> $discountDuration
						);
					}else{
						
						$noteType 	= 'warning';
						$noteMessage	= 'Discount Code: <strong>'.$code.'</strong> can not be used with any of the items in your cart.';
							
					}
				}
			}
				
		}else{
			#invalid code
			$noteType 		= 'warning';
			$noteMessage	= 'Discount Code: <strong>'.$code.'</strong> is not valid.';	
		}
		//END  | Check for valid discount
	}
	
	
	if($noteType != ''){
		?>
		<div class="note note-<?php echo $noteType;?>" style="margin-top:10px;">
			<p><?php echo $noteMessage;?></p>
		</div>
		<?php 
	}
	
	$numDiscounts = 0;
	foreach($_SESSION['cart-discounts'] as $discountType=>$aDiscounts){
		foreach($aDiscounts as $key=>$aDiscountInfo){
			$numDiscounts++;	
		}
	}
	
	if($numDiscounts > 0){
		?>
		<h4>Applied Discounts</h4>
		<ul>
			<?php
			foreach($_SESSION['cart-discounts'] as $discountType=>$aDiscounts){
				foreach($aDiscounts as $key=>$aDiscountInfo){
				
					$discountName 	= $aDiscountInfo['name'];
					$discountDesc	= $aDiscountInfo['desc'];
					$discountCode	= $aDiscountInfo['code'];
					
					?>
					<li>
						<h5><strong><?php echo $discountName;?></strong></h5>
						<p><strong>Code: <?php echo $discountCode;?></strong><br /><?php echo $discountDesc;?></p>
					</li>
					<?php
				}	
			}
			?>
		</ul>
        <a href="javascript:void(0);" onclick="clearDiscounts();">Remove applied discounts</a>
        
        <div class="note note-info" style="margin:10px 0px 0px 0px;">
            <p>Note: Discounts will be reflected in the final order review.</p>
        </div>
		<?php
	}


	/*echo '<pre>';

	echo $discountCode;
	print_r($_SESSION['cart-discounts']);
	echo '</pre>';	*/
}

if($process == 'clear-discounts'){
	$_SESSION['cart-discounts'] = '';

	?>
	<div class="note note-info" style="margin-top:10px;">
		<p>Applied discounts have been removed.</p>
	</div>
    <?php
}

?>
