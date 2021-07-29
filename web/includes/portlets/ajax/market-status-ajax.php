<?php
// Determines if the markets are open
// Ajax call from /includes/pages/dashboard.php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

//Start User Session
session_start();

// Load debug & error logging functions
require_once("../../system-debug-functions.php");

//Connect to DB
require_once("../../../../secure/dbconnect.php");

//Get global settings and functions
require("../../system-functions.php");
require("../../system-global.php");

// Are we within normal market hours?
if (isMarketOpen(time(), $mLink, "none")){  // in system-functions.php
	$timeToClosing = strtotime('today '.(isMarketHoliday(time(), $mLink) == "E" ? "1:00 PM" : "4:00 PM"). ' America/New_York') - time();  // How long before closing?
	if ($timeToClosing > 1800){ // More than 30 minutes before closing
		$marketStatus = "US Markets Open<div class='hidden-xs' style='float:right; position:absolute; right:30px; top:12px'>15 Minute Delay</div>";
	}else{  // Last 30 minutes
		$minutes = floor(($timeToClosing + 59) / 60);  // How many minutes 'til closing?
		if ($minutes <= 0){  // Need this for that short period when market is actually closed while still counting down (rounding)
			$marketStatus = "US Markets Closed";
		}else{
			$marketStatus = "US Markets closing in less than ".$minutes." minute".($minutes == 1 ? "!" : "s")."<div class='hidden-xs' style='float:right; position:absolute; right:30px; top:12px'>15 Minute Delay</div>";
		}
	}
}else{
	$marketStatus = "US Markets Closed";
}

// See if it's a market holiday and change notice accordingly if it is
if ($_SESSION['market_holiday']){
	if ($_SESSION['market_holiday'] == "Y"){
		$marketStatus = "US Markets Closed for ".$_SESSION['market_holiday_occasion'];
	}else{  // Close early
		$marketStatus = "US Markets Close".(time() > strtotime('today 1:00 PM America/New_York') ? "d" : "")." Early Today for ".$_SESSION['market_holiday_occasion'];
	}
}
echo $marketStatus;
?>
