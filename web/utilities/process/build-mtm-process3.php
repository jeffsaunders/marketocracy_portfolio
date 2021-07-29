<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

require_once("../../includes/system-functions.php");

$process = $_REQUEST['process'];

set_time_limit(3600);



$startDate = '946702800'; //2000 - 01 - 01
$endDate = strtotime('today');

//START GET DATES FOR RETURN CALCULATION
function get_months($time1, $time2) {
	
    $my = date('mY', $time2);
    $months = array(/*date('Y-m-t', $time1)*/);
    $f = '';

    while($time1 < $time2) {
        $time1 = strtotime((date('Y-m-d', $time1).' +15days'));

        if(date('F', $time1) != $f) {
            $f = date('F', $time1);

            if(date('mY', $time1) != $my && ($time1 < $time2))
                $months[] = date('Y-m-t', $time1);
				//$months2[] = $time1;
				//$months[] = $time1;
        }

    }

    $months[] = date('Y-m-d', $time2);
	//$months[] = $time2;
	//$months2[] = $time2;
    return $months;
}
$aDates2 = get_months($startDate, $endDate);
$aDates = get_months($startDate, $endDate);

foreach($aDates as $key=>$timestamp){
	
	$aTimestamp = explode('-', $timestamp);
	
	$timestamp = mktime(6, 0, 0, $aTimestamp[1], $aTimestamp[2], $aTimestamp[0]);
	
	$origTimestamp = $timestamp;
	
	//check to see if its a weekend, if it is get the timestamp of the friday before it
	if(date('N', $timestamp) == '6'){
		$timestamp = strtotime('-1 day', $timestamp);
	}elseif(date('N', $timestamp) == '7'){
		$timestamp = strtotime('-2 day', $timestamp);	
	}
	
	if(date('N', $timestamp) == '1' && isMarketHoliday($timestamp, $mLink, true) == 'Y'){
		$timestamp = strtotime('-3 day', $timestamp);	
	}
	
	$test = isMarketHoliday($timestamp, $mLink, true);
	
	//Check to see if the timestamp is a Market Holiday
	if(isMarketHoliday($timestamp, $mLink, true) == 'Y'){
		
		$timestamp = strtotime('-1 day', $timestamp);
		
	}
	
	
	
	//$aDates[$key] = date('Y-m-d', $timestamp);
	$aDates[$key] = date('Y/m/d', $timestamp).'|'.$origTimestamp.' | '.$test;	
}


echo '<pre>';
print_r($aDates2);
print_r($aDates);
echo '</pre>';
?>