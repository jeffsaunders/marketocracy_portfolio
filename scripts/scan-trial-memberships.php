<?php
/*
The purpose of this script is scan through the current trial memberships and perform tasks based on the position (date) within the trial period.
Currently it sends emails on certain days and deactivates the trial subscription, if the trial has ended, and creates their new subscription record, whether they upgraded or not (switches them to Basic if not).
*Note - this will not run within a web browser.
*/

// Tell me when things go sideways
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Run long enough
set_time_limit(900); // 15 minutes

//Get debug functions
require("/var/www/html/portfolio.marketocracy.com/web/includes/system-debug-functions.php");

// Connect to database
//require("/var/www/html/portfolio.marketocracy.com/secure/dbconnect.php");
require("/var/www/html/portfolio.marketocracy.com/scripts/dbConnect.php"); // Need to use version without Brandon's database object code

//Get site functions
require("/var/www/html/portfolio.marketocracy.com/web/includes/system-functions.php");

//Get global settings
require("/var/www/html/portfolio.marketocracy.com/web/includes/system-global.php");

// Parse passed arguments string to $_REQUEST array (i.e. "first=1&second=2&third=3" -> $_REQUEST['first'] = 1, etc.)
parse_str($argv[1], $_REQUEST);

// Test passed run date is all numeric
if (isset($_REQUEST['date']) && !is_numeric($_REQUEST['date'])){
	echo "You must specify a run date as \"date=YYYYMMDD\" or leave blank for \"Today\". (e.g. php scan-trial-membership.php \"date=20161015\")\n";
	die();
}

// Test passed run date is 8 characters long
if (isset($_REQUEST['date']) && strlen($_REQUEST['date']) != 8){
	echo "You must specify a run date as \"date=YYYYMMDD\" or leave blank for \"Today\". (e.g. php scan-trial-membership.php \"date=20161015\")\n";
	die();
}

// Set run date
if (isset($_REQUEST['date'])){
//	$run_date = $_REQUEST['date']; // Just have to trust that the passed date is properly formatted...
	$run_date = new DateTime($_REQUEST['date']); // Just have to trust that the passed date is properly formatted...
}else{
//	$run_date = date('Ymd');
	$run_date = new DateTime(date('Y-m-d'));  // Objectify the run date
}


// Check for process actions
if (isset($_REQUEST['stopAction'])){
	$cntOnly = true;	
}else{
	$cntOnly = false;
}

// Check for single user
if (isset($_REQUEST['member_id'])){
	$allSubs = false;	
}else{
	$allSubs = true;
}

// Get the product details of each membership level
/* Don't need this anymore (leaving it here in case I do in the future)
$query = "
	SELECT *
	FROM ".$_SESSION['products_table']."
	WHERE product_id < 100
	AND active = 1
";
try{
	$rsProds = $mLink->prepare($query);
	$rsProds->execute();
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

// Assign values to an array (key = product_id)
$aProds = array();
while($product = $rsProds->fetch(PDO::FETCH_ASSOC)){
	$aProds[$product['product_id']] = $product;
}

//print_r($aProds);die();
*/
//echo "Processing Trial Memberships for ".date('Ymd', $run_date)."\n";
echo "Processing Trial Memberships for ".$run_date->format('Ymd')."\n";

// Back up the members_subscriptions table, just in case
// Out with the old....
$query = "
	DROP TABLE members_subscriptions_backup
";
try{
	$rsDrop = $mLink->prepare($query);
	$rsDrop->execute();
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

// ...In with the new
$query = "
	CREATE TABLE members_subscriptions_backup LIKE members_subscriptions;
	INSERT members_subscriptions_backup SELECT * FROM members_subscriptions;
";
try{
	$rsCreate = $mLink->prepare($query);
	$rsCreate->execute();
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

// Reconnect to the DB - the CREATE TABLE seems to close the connection for some reason - need to investigate further
//require("/var/www/html/portfolio.marketocracy.com/secure/dbconnect.php");
require("/var/www/html/portfolio.marketocracy.com/scripts/dbConnect.php"); // Need to use version without Brandon's database object code

#get array of members who have been sent this campaign already
$query = "
	SELECT member_id, camp_id
	FROM email_auto_tracking
";
try{
	$rsTracking = $mLink->prepare($query);
	$aValues = array();
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsTracking->execute($aValues);
}

catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}
$aSent = array();

while($track = $rsTracking->fetch(PDO::FETCH_ASSOC)){

	$aSent[$track['camp_id']][] = $track['member_id'];

}

// Get all the active free trial members (product_id = 0 or 99)
if($allSubs == true){
	$query = "
		SELECT *
		FROM ".$_SESSION['subscriptions_table']."
		WHERE active = 1
		AND start_timestamp IS NOT NULL
		AND (product_id = 0 OR product_id = 99)
	";
}else{
	$query = "
		SELECT *
		FROM ".$_SESSION['subscriptions_table']."
		WHERE active = 1
		AND start_timestamp IS NOT NULL
		AND (product_id = 0 OR product_id = 99)
		AND member_id='".$_REQUEST['member_id']."'
	";
}

//echo $query;
/*$query = "
	SELECT *
	FROM ".$_SESSION['subscriptions_table']."
	WHERE active = 1
	AND start_timestamp IS NOT NULL
	AND (product_id = 0 OR product_id = 99)
";*/
try{
	$rsSubs = $mLink->prepare($query);
	$rsSubs->execute();
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

// Set up some counters
$emails1	= 0;
$emails7	= 0;
$emails15	= 0;
$emails23	= 0;
$emails27	= 0;
$emails30	= 0;
$expires	= 0;
$trials		= 0;
$trialsUpgrades = 0;

$emailsLegacy1 = 0;
$emailsStandard1 = 0;

while($subscription = $rsSubs->fetch(PDO::FETCH_ASSOC)){

	// Is this a Legacy trial?
	$legacy = false;
	if ($subscription['product_id'] == 99){
		$legacy = true;
	}

	// Calculate what day it is in their trial period
//	$day = $run_date - date('Ymd', $subscription['start_timestamp']);
	$start_date = new DateTime(date('Y-m-d', $subscription['start_timestamp'])); // Objectify the trial start date
	$interval = date_diff($run_date, $start_date);
	$day = $interval->format('%a');

/*echo $subscription["member_id"]." - ".$day." - ".date('Y-m-d',$subscription['start_timestamp'])."\n";
continue;
die();*/
	$memberID = $subscription['member_id'];

	$newProductID = $subscription['new_product_id'];

	if($newProductID != NULL){
		$trialsUpgrades++;
	}

	// Evaluate the day and act accordingly
	switch (true){ // Use this method so we can evaluate whether the value is == and >

		case $day == 1: // End of first week

			if($newProductID == NULL){



				if ($legacy){  // Legacy trial member
					// Build and send corresponding email here
					
					if(!in_array($memberID, $aSent[1])){
						if($cntOnly == false){
							sendAutoEmail($mLink, $memberID, '1', true);
						}
						$emails1++;
						$emailsLegacy1++;
					}

				}else{  // Regular trial member (new member)
					// Build and send corresponding email here
					
					
						if(!in_array($memberID, $aSent[2])){
							if($cntOnly == false){
								sendAutoEmail($mLink, $memberID, '2', true);
							}
							$emails1++;
							$emailsStandard1++;
						}
					
				}

			}

			break;
			
		case $day == 2: // End of first week

			if($newProductID == NULL){



				if ($legacy){  // Legacy trial member
					// Build and send corresponding email here
					
					
					if(!in_array($memberID, $aSent[1])){
						if($cntOnly == false){
							sendAutoEmail($mLink, $memberID, '1', true);
						}
						$emails1++;
						$emailsLegacy1++;
					}
					

				}else{  // Regular trial member (new member)
					// Build and send corresponding email here

					if(!in_array($memberID, $aSent[2])){
						if($cntOnly == false){
							sendAutoEmail($mLink, $memberID, '2', true);
						}
						$emails1++;
						$emailsStandard1++;
					}

				}

			}

			break;	

		case $day == 7: // End of first week

			if($newProductID == NULL){
				if ($legacy){  // Legacy trial member
					// Build and send corresponding email here
					if(!in_array($memberID, $aSent[3])){
						if($cntOnly == false){
							sendAutoEmail($mLink, $memberID, '3', true);
						}
					}
				}else{  // Regular trial member (new member)
					// Build and send corresponding email here
					if(!in_array($memberID, $aSent[4])){
						if($cntOnly == false){	
							sendAutoEmail($mLink, $memberID, '4', true);
						}
					}
				}
				$emails7++;
			}
			break;
			
		

		case $day == 15:  // After second week (early discount no longer offered)
			//echo $day.'-here';
			if($newProductID == NULL){
				if ($legacy){  // Legacy trial member
					// Build and send corresponding email here
					if($cntOnly == false){		
						$results = sendAutoEmail($mLink, $memberID, '5', true);
					}
				}else{  // Regular trial member (new member)
					// Build and send corresponding email here
					if($cntOnly == false){
						$results = sendAutoEmail($mLink, $memberID, '6', true);
					}
					//
				}
				$emails15++;
				//print_r($results);
			}
			break;

		case $day == 23:  // One week out

			if($newProductID == NULL){
				if ($legacy){  // Legacy trial member
					// Build and send corresponding email here
					if($cntOnly == false){
						sendAutoEmail($mLink, $memberID,'7');
					}
				}else{  // Regular trial member (new member)
					// Build and send corresponding email here
					if($cntOnly == false){
						sendAutoEmail($mLink, $memberID, '8');
					}
				}
				$emails23++;
			}
			break;

		case $day == 27:  // 3 days out

			if($newProductID == NULL){
				if ($legacy){  // Legacy trial member
					// Build and send corresponding email here
					if($cntOnly == false){
						sendAutoEmail($mLink, $memberID,'9');
					}
				}else{  // Regular trial member (new member)
					// Build and send corresponding email here
					
					if($cntOnly == false){
						sendAutoEmail($mLink, $memberID,'10');
					}
				}
				$emails27++;
			}
			break;


		case $day == 30:  // Last day

			if($newProductID == NULL){
				if ($legacy){  // Legacy trial member
					// Build and send corresponding email here
					if($cntOnly == false){
						sendAutoEmail($mLink, $memberID,'11');
					}
				}else{  // Regular trial member (new member)
					// Build and send corresponding email here
					
					if($cntOnly == false){
						sendAutoEmail($mLink, $memberID,'12');
					}
				}
				$emails30++;
			}
			break;

		case $day > 30:  //31, usually - trial over



			if ($legacy){  // Legacy trial member
				// Build and send corresponding email here
				if($cntOnly == false){
					sendAutoEmail($mLink, $memberID,'13');
				}
			}else{  // Regular trial member (new member)
				// Build and send corresponding email here
				
				if($cntOnly == false){
					sendAutoEmail($mLink, $memberID,'14');
				}
			}

			// Deactivate their trial membership
			$query = "
				UPDATE ".$_SESSION['subscriptions_table']."
				SET	cancel_timestamp= UNIX_TIMESTAMP(),
					cancel_reason	= 'Trial Expired',
					active			= 0
				WHERE member_id = :member_id
				AND (product_id = 0 OR product_id = 99)


			";
			try {
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':member_id' => $subscription['member_id']
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				//echo $preparedQuery;//die();
				$rsUpdate->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($pdo_log, "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}



			//echo $newProductID.'\n';

			// Grab the new_product_id to see what level to move them to (NULL = "Basic")
			$aNewProductID = array();


			if(is_null($newProductID)){
				$aNewProductID[] = 1; //basic member
			}else{
				$trialsUpgrades--;
				$aNewProductID = explode('|', $newProductID);
			}

			$isPaidBasic = false;

			#check for paid basic
			foreach($aNewProductID as $key=>$productID){

				if($productID > 100){
					$isPaidBasic = true;
				}

			}

			if($isPaidBasic == true){
				$aNewProductID[] = 5;
			}

			//print_r($aNewProductID).'/n';

			// Grab the new_bill_frequency (NULL = "Never" *Basic)
			$newBillFrequency = $subscription['new_bill_frequency'];

			// If it's unassigned, make it "Never"
			if(is_null($newBillFrequency)){
				$newBillFrequency = "Never"; // Basic
			}

			// Set the product expiration date (renewal) timestamp
			$productExpiration	= strtotime('+1 year', time());

			// Set the next billing date timestamp
			switch($newBillFrequency){

				case "Never":
					$nextBillTimestamp = NULL;
					$productExpiration = NULL;  // Never expires
					break;

				case "Monthly":
					$nextBillTimestamp = strtotime('+1 month', time());
					break;

				case "Quarterly":
					$nextBillTimestamp = strtotime('+3 months', time());
					break;

				case "Annually":
					$nextBillTimestamp = strtotime('+1 year', time());
					break;

		    	default:
					// Do Nothing

			}	// End switch

			foreach($aNewProductID as $key=>$setNewProductID){

				if($setNewProductID == 1 || $setNewProductID == 5){
					$nextBillTimestamp = NULL;
					$productExpiration = NULL;
				}


				// Now insert a new membership subscription
				$query = "
					INSERT INTO ".$_SESSION['subscriptions_table']." (
						member_id,
						product_id,
						start_timestamp,
						bill_frequency,
						next_bill_timestamp,
						product_expiration,
						active
					)VALUES(
						:member_id,
						:product_id,
						UNIX_TIMESTAMP(),
						:bill_frequency,
						:next_bill_timestamp,
						:product_expiration,
						1
					)
				";
				try{
					$rsInsert = $mLink->prepare($query);
					$aValues = array(
						':member_id'			=> $subscription['member_id'],
						':product_id'			=> $setNewProductID,
						':bill_frequency'		=> $newBillFrequency,
						':next_bill_timestamp'	=> $nextBillTimestamp,
						':product_expiration'	=> $productExpiration
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					//echo $preparedQuery."\n";die();
					$rsInsert->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($pdo_log, "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}

				//echo $error;
			}

 			// Finally, set their flags
			$query = "
				UPDATE ".$_SESSION['members_flags_table']."
				SET	member		= 1,
					trial		= 0,
					free		= ".($newProductID = 1 ? 1 : 0).",
					standard	= ".($newProductID = 2 ? 1 : 0).",
					premium		= ".(($newProductID = 3 || $newProductID = 4 || $newProductID = 10)  ? 1 : 0).",
					last_chance	= ".($newProductID = 1 ? 1 : 0)."
				WHERE member_id = :member_id
			";
//					student		= 0,

			try {
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':member_id' => $subscription['member_id']
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				//echo $preparedQuery;//die();
				$rsUpdate->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($pdo_log, "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}




			$expires++;
			$trials--;
			break;

    	default:
			// Do Nothing

	}  // End switch

	$trials++;
	echo ".";

}

echo "\n1 Day TOTAL Emails Sent:  ".$emails1."\n";
echo "1 Day Legacy Emails Sent:  ".$emailsLegacy1."\n";
echo "1 Day Standard Emails Sent:  ".$emailsStandard1."\n";
echo "7 Day Emails Sent:  ".$emails7."\n";
echo "15 Day Emails Sent: ".$emails15."\n";
echo "23 Day Emails Sent: ".$emails23."\n";
echo "27 Day Emails Sent: ".$emails27."\n";
echo "30 Day Emails Sent: ".$emails30."\n";
echo "Expired Trials:     ".$expires."\n";
echo "Trials with Upgrades: ".$trialsUpgrades."\n";
echo "Trials Remaining:   ".$trials."\n\n";


echo "\n\nLegacy Emails not sent: ".count($aSent[1])."\n";
echo "\nStandard Emails not sent: ".count($aSent[2])."\n";

echo "Done!\n";

?>
