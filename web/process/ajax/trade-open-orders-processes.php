<?php
/*
TRADE OPEN ORDERS - PROCESSING FILE FILE

SUPPORTING FILES:
_______________________________________________________________

AJAX 			-  THIS FILE process/ajax/trade-open-orders-processes.php
PHP JAVASCRIPT	- includes/scripts/trade-open-orders.js.php
HTML 			- includes/pages/trade-open-orders.php
_______________________________________________________________

*/

//Start Session
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

// Load System Wide Functions
require_once("../../includes/system-functions.php");

//Get Process from URL
$process = $_REQUEST['process'];


if($process == 'deleteOrder'){

	$uid	= $_REQUEST['uid'];

	//Cancel Ticket
	$query = "
		UPDATE members_fund_tickets
		SET status='cancelled', cancel_status=UNIX_TIMESTAMP(), closed=UNIX_TIMESTAMP()
		WHERE uid=:uid
	";
	try{
		$rsDelete = $mLink->prepare($query);
		$aValues = array(
			':uid' 	=> $uid
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsDelete->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	sleep(2);
}

if($process == 'resubmitOrder'){

	$uid	= $_REQUEST['uid'];
	
	
	$query = "
		SELECT *
		FROM members_fund_tickets
		WHERE uid=:uid
	";
	try{
		$rsTicket = $mLink->prepare($query);
		$aValues = array(
			':uid' 	=> $uid
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsTicket->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$order = $rsTicket->fetch(PDO::FETCH_ASSOC);
	
	$username 	= $_SESSION['username'];
	$fundID		= $order['fund_id'];
	$fundSymbol	= get_funds($mLink, $fundID, 'fundSymbol');
	$tradeType	= $order['action'];
	$orderType	= $order['type'];
	$symbol		= $order['symbol'];
	$shares		= $order['shares'];
	$limitPrice	= $order['limit'];
	$reason		= $order['reason'];
	$desc		= $order['description'];
	
	if($orderType == ''){
		$orderType = 'GTC';	
	}
	
	$location = trace();
	
	//Create query
	$query = 'create||'.$username.'|'.$fundSymbol.'|'.$fundID.'|'.$tradeType.'|'.$orderType.'|'.$symbol.'|'.$shares.'|'.$limitPrice.'|'.$reason.'|'.$desc.'|0';
	
	$port = $setPort;
	
	//Execute Query
	include ('../../includes/data-query-ecn.php');
	
	$showQueries[] = $query;
	
	$event = 'STOCK ORDER : resubmit';
	$detail = $_SESSION['username'].':'.$query;
	addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
	
	//Remove old record
	$query = "
		UPDATE members_fund_tickets
		SET status='closed', cancel_status=UNIX_TIMESTAMP(), closed=UNIX_TIMESTAMP()
		WHERE uid=:uid
	";
	try{
		$rsDelete = $mLink->prepare($query);
		$aValues = array(
			':uid' 	=> $uid
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsDelete->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	sleep(2);
}

//+---------------------------------------------------------------------------------------------------------+
//|										Get Trade Info - PROCESS 1											|
//+---------------------------------------------------------------------------------------------------------+

if($process == "1"){

	$uid = $_REQUEST['uid'];
	
	
	$query = "
		SELECT *
		FROM members_fund_tickets 
		WHERE uid=:uid
	";
	try{
		$rsTicket = $mLink->prepare($query);
		$aValues = array(
			':uid' 	=> $uid
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsTicket->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$order = $rsTicket->fetch(PDO::FETCH_ASSOC);
	
	$symbol = $order['symbol'];
	
	$yqlURL = "https://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.quote%20where%20symbol%20in%20%28%22".$symbol."%22%29&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
			
	// Make call with cURL  
	$session = curl_init($yqlURL);  
	curl_setopt($session, CURLOPT_RETURNTRANSFER,true);  
	$json = curl_exec($session); 
	
	// Convert JSON to PHP object   
	$phpObj =  json_decode($json);
	
	$name 	= $phpObj->query->results->quote->Name;
	$symbol	= $phpObj->query->results->quote->Symbol;
	
	
	?>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title">Confirm Cancel Order</h4>
        <h5><?php echo $name;?> (<?php echo $symbol;?>) | Status: <span class="label label-success"><?php echo $order['status'];?></span></h5>
    </div>
    <div class="modal-body cancel-order">
    	
        <h4>Please Confirm Your Cancel Order</h4>
        <p>Clicking the button below will send a cancel request to the exchange and take you back to the Open Orders page. The order will still appear in Open Orders for a few minutes until the exchange processes your request. After the exchange has canceled trading for that order, the order will move to the Recent Orders page. Any shares already traded will then be credited to your funds.</p>
        
    </div><!--modal-body-->
    <div class="modal-footer">
        <button type="button" class="btn btn-danger" onclick="cancelOrder('<?php echo $uid;?>');">Cancel the unfilled portion of my order</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    </div>
    <?php
	
}


//+---------------------------------------------------------------------------------------------------------+
//|										Cancell ORDER - PROCESS 2											|
//+---------------------------------------------------------------------------------------------------------+

if($process == "2"){
	
	$uid = $_REQUEST['uid'];
	
	$query = "
		SELECT *
		FROM members_fund_tickets 
		WHERE uid=:uid
	";
	try{
		$rsTicket = $mLink->prepare($query);
		$aValues = array(
			':uid' 	=> $uid
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsTicket->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$order = $rsTicket->fetch(PDO::FETCH_ASSOC);
	
	
	
	$query = 'cancel|'.$order['ticket_key'].'';
	//Execute Query
	include ('../../includes/data-query-ecn.php');
	
	sleep(1);
	//echo $query;
	
	$query = '
		UPDATE members_fund_tickets
		SET cancel_status=UNIX_TIMESTAMP()
		WHERE uid=:uid
		 
	';
	try{
		$rsUpdateTicket = $mLink->prepare($query);
		$aValues = array(
			':uid' 	=> $uid
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsUpdateTicket->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
}
?>
