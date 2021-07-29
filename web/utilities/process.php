<?php
session_start();

// Load debug & error logging functions
require_once("../includes/system-debug-functions.php");

//Connect to DB
require_once("../../secure/dbconnect.php");

require_once("../includes/system-functions.php");

$process = $_REQUEST['process'];

//Get Ticket Info
if($process == "1"){

print_r( get_ticket($mLink, $_REQUEST['ticket'], $_REQUEST['info']));
	
}

//Get Member Info
if($process == "2"){

print_r( get_member($mLink, $_REQUEST['member'], $_REQUEST['info']));
	
}

//Get Post URL
if($process == "3"){

	print_r(get_post_url($mLink, $_REQUEST['post_id']));
	
}

//Get Fund Info
if($process == "4"){
	
	print_r(get_global_funds($mLink, $_REQUEST['fund_id'], $_REQUEST['item'], $_REQUEST['switch']));
	
}

//Get Unixtime
if($process == "5"){
	
	$date = $_REQUEST['date'];
	
	$year = substr($date, 0, 4);
	
	$month = substr($date, 4, 2);
	
	$day = substr($date, 6, 2);
	
	$unixtime = mktime('0','0','0',$month,$day,$year);
	
	//echo $day;
	
	echo $unixtime;
}

if($process == "6"){
	echo "help";
	var_dump($_SESSION);	
}

if($process == "7"){
	$type = $_REQUEST['type'];
	$url = $_REQUEST['url'];
	
	if($type == 'decode'){
		$url = rawurldecode($url);	
	}elseif($type == 'encode'){
		$url = urlencode($url);	
	}
	
	print_r($url);
}

?>