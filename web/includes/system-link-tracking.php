<?php
//LINK TRACKING SYSTEM

//SET VARIABLES FOR VARIOUS SOURCES
$referralCode = $_SESSION['referral_source'];


//SWITCH/CASE FUNCTION FOR HUMAN READABLE

//FOR FEEDBACK FORM
switch($referralCode) {
	case "email": $referralSource = "Email";break;
	case "facebook": $referralSource = "Facebook";break;
	case "linkedin": $referralSource = "LinkedIn";break;
	case "twitter": $referralSource = "Twitter";break;
	case "forbes": $referralSource = "Forbes";break;	
}

?>