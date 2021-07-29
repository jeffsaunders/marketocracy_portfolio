<?php

require_once("system-functions.php");

// If this is a new session, set site config
if (isset($_SESSION['site_name']) == false){

	//Get newest config values
	$query = "
		SELECT *
		FROM config
		WHERE timestamp = (SELECT MAX(timestamp) FROM config)
	";
	$rsConfig = $mLink->prepare($query);
	$rsConfig->execute();
	$config = $rsConfig->fetch(PDO::FETCH_ASSOC);

	//Assign session varibles
	$_SESSION = array();
	$_SESSION['site_name']		= $config['site_name'];
	$_SESSION['doctype']		= $config['doctype'];
	$_SESSION['content_type']	= $config['content_type'];
	$_SESSION['x_ua_compatible']= $config['x_ua_compatible'];
	$_SESSION['title']			= $config['title'];
	$_SESSION['reply_to']		= $config['reply_to'];
	$_SESSION['author']			= $config['author'];
	$_SESSION['resource_type']	= $config['resource_type'];
	$_SESSION['revisit_after']	= $config['revisit_after'];
	$_SESSION['classification']	= $config['classification'];
	$_SESSION['distribution']	= $config['distribution'];
	$_SESSION['rating']			= $config['rating'];
	$_SESSION['copyright']		= $config['copyright'];
	$_SESSION['language']		= $config['language'];
	$_SESSION['description']	= $config['description'];
	$_SESSION['keywords']		= $config['keywords'];
	$_SESSION['robots']			= $config['robots'];
	$_SESSION['cache']			= $config['cache'];
	$_SESSION['site_root']		= $config['protocol']."://".$config['base_url'];
	$_SESSION['site_protocol']	= $config['protocol'];
	$_SESSION['base_url']		= $config['base_url'];
	$_SESSION['home_page']		= $config['home_page'];
	$_SESSION['auth_table']		= $config['auth_table'];
	$_SESSION['fund_table']		= $config['fund_table'];

	// Build array of misc config (meta) tags
	$_SESSION['aTags']			= explode("|",$config['other_meta']);

	// Operations config settings
	$_SESSION['pdo_log']			= $config['pdo_log'];

	// Feature/service on/off switches
	$_SESSION['tour_status']					= ($config['tour_status'] == '1' ? "on" : "off");
	$_SESSION['demo_note']						= ($config['demo_note'] == '1' ? "on" : "off");
	$_SESSION['username_obscenity_checking']	= ($config['username_obscenity_checking'] == '1' ? "on" : "off");
	$_SESSION['username_minimum_length']		= $config['username_minimum_length'];
	$_SESSION['password_minimum_length']		= $config['password_minimum_length'];
	$_SESSION['test_var']						= $config['test_var']; //Shows misc variables across site
}

//Assign misc needed config varibles
$fundTable	= $_SESSION['fund_table'];
$siteRoot	= $_SESSION['site_root'];
//$homePage	= $_SESSION['home_page']; //Dashboard/Home
$tourStatus	= $_SESSION['tour_status']; // turn off or on the site tour
$demoNote	= $_SESSION['demo_note']; // turn off or on demo notifications

?>