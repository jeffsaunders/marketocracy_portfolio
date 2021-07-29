<?php

// If this is a new session get and set config vars
if (isset($_SESSION['member_id']) == false){

	//Create session variable array
	$_SESSION = array();

	//+---------------------------------------------------------------------------------------------+
	//									SYSTEM CONFIG SETTINGS										|
	//+---------------------------------------------------------------------------------------------+

	// Get newest system config values
// This query grabs the newest version of each setting, but since we are just updating the values there will only be one
/*	$query = "
		SELECT *
		FROM system_config conf
		INNER JOIN (
			SELECT max(uid) AS uid, setting
			FROM system_config
			GROUP BY setting
		) dup ON dup.setting = conf.setting
		WHERE conf.uid = dup.uid
	";
*/
	$query = "
		SELECT *
		FROM system_config
	";
	$rsSystemConfig = $mLink->prepare($query);
	$rsSystemConfig->execute();

	// Create variables from the stored settings and assign the stored values to them
	while ($systemConfig = $rsSystemConfig->fetch(PDO::FETCH_ASSOC)){
		$var = trim($systemConfig['setting']); // Just in case there are spaces before or after the value
		$$var = trim($systemConfig['value']);  // Create the var based on the "setting" value, and assign the actual "value" value to it
		
		$_SESSION[$var] = $$var;
	}

	// Assign system variables from the constructed variable's value
	// Switches
	$_SESSION['login_fundprice_checking']			= ($login_fundprice_checking == '1' ? "on" : "off");
	$_SESSION['username_obscenity_checking']		= ($username_obscenity_checking == '1' ? "on" : "off");

	// Limits
	$_SESSION['username_minimum_length']			= $username_minimum_length;
	$_SESSION['password_minimum_length']			= $password_minimum_length;

	// Timers & Timeouts
	$_SESSION['indices_refresh']					= $system_indices_refresh * 1000;
	$_SESSION['funds_refresh']						= $system_funds_refresh * 1000;
	$_SESSION['market_status_refresh']				= $system_market_status_refresh * 1000;
	$_SESSION['inactivity_timeout']					= $inactivity_timeout;
	$_SESSION['admin_inactivity_timeout']			= $admin_inactivity_timeout;
	$_SESSION['logout_warning_timeout']				= $logout_warning_timeout;
	$_SESSION['support_ticket_refresh']				= $support_ticket_refresh * 1000;
	$_SESSION['portlet_refresh']					= $portlet_refresh * 1000;
	$_SESSION['trade_popup_timeout']				= $trade_popup_timeout;
	$_SESSION['heartbeat_interval']					= $system_heartbeat_interval * 1000;

	// Operations config settings
	$_SESSION['pdo_log'] 							= $pdo_log;
	$_SESSION['max_reply_level_ca']					= $max_reply_level_ca;

	// External server settings
	$_SESSION['fetch_server']						= $fetch_server;
	$_SESSION['query_legacy_socket']				= $query_legacy_socket;
	$_SESSION['query_ecn_socket']					= $query_ecn_socket;
	$_SESSION['api_mount']							= $api_mount;

	// Email
	$_SESSION['system_email_from']					= $system_email_from;
	$_SESSION['system_email_bcc']					= $system_email_bcc;

	//Other
	$_SESSION['user_ident']							= $user_ident;
	


	//+---------------------------------------------------------------------------------------------+
	//									DATABASE CONFIG SETTINGS									|
	//+---------------------------------------------------------------------------------------------+

	// Get newest system config values
// This query grabs the newest version of each setting, but since we are just updating the values there will only be one
/*	$query = "
		SELECT *
		FROM system_config_database conf
		INNER JOIN (
			SELECT max(uid) AS uid, setting
			FROM system_config_database
			GROUP BY setting
		) dup ON dup.setting = conf.setting
		WHERE conf.uid = dup.uid
	";
*/
	$query = "
		SELECT *
		FROM system_config_database
	";
	$rsSystemConfigDB = $mLink->prepare($query);
	$rsSystemConfigDB->execute();

	// Create variables from the stored settings and assign the stored values to them
	while ($systemConfigDB = $rsSystemConfigDB->fetch(PDO::FETCH_ASSOC)){
		$var = trim($systemConfigDB['setting']); // Just in case there are spaces before or after the value
		$$var = trim($systemConfigDB['value']);  // Create the var based on the "setting" value, and assign the actual "value" value to it
		
		$_SESSION[$var] = $$var;
	}

	// Assign system variables from the constructed variable's value
	// Database table names
	$_SESSION['sections_table']						= $sections_table;
	$_SESSION['pages_table']						= $pages_table;
	$_SESSION['redirects_table']					= $redirects_table;
	
	//edu tables
	$_SESSION['school_table']						= $school_table;
	$_SESSION['teacher_table']						= $teacher_table;
	$_SESSION['class_table']						= $class_table;
	
	//system tables
	$_SESSION['system_feeds_table']					= $system_feeds_table;
	$_SESSION['system_holidays_table']				= $system_holidays_table;
	$_SESSION['system_lists_table']					= $system_lists_table;
	$_SESSION['lists_options_table']				= $lists_options_table;
	$_SESSION['fetch_errors_table']					= $fetch_errors_table;
	$_SESSION['system_benchmarks_table']			= $system_benchmarks_table;
	$_SESSION['system_email_table']					= $system_email_table;
	
	
	//support tables
	$_SESSION['support_ticket_table']				= $support_ticket_table;
	$_SESSION['support_email_table']				= $support_email_table;
	$_SESSION['email_fields_table']					= $email_fields_table;
	$_SESSION['support_feedback_table']				= $support_feedback_table;
	$_SESSION['community_feedback_table']			= $community_feedback_table;

	$_SESSION['auth_table']							= $auth_table;
	$_SESSION['password_scores_table']				= $password_scores_table;
	$_SESSION['eventslog_table']					= $eventslog_table;
	$_SESSION['logged_in_table']					= $logged_in_table;

	//fund tables
	$_SESSION['fund_table']							= $fund_table;
	$_SESSION['fund_settings_table']				= $fund_settings_table;
	$_SESSION['fund_pricing_table']					= $fund_pricing_table;
	$_SESSION['fund_aggregate_table']				= $fund_aggregate_table;
	$_SESSION['fund_aggregate_history_table']		= $fund_aggregate_history_table;
	$_SESSION['fund_alphabeta_table']				= $fund_alphabeta_table;
	$_SESSION['fund_alphabeta_history_table']		= $fund_alphabeta_history_table;
	$_SESSION['fund_positions_table']				= $fund_positions_table;
	$_SESSION['fund_positions_details_table']		= $fund_positions_details_table;
	$_SESSION['fund_stratification_basic_table']	= $fund_stratification_basic_table;
	$_SESSION['fund_stratification_style_table']	= $fund_stratification_style_table;
	$_SESSION['fund_stratification_style_positions_table']	= $fund_stratification_style_positions_table;
	$_SESSION['fund_stratification_sector_table']	= $fund_stratification_sector_table;
	$_SESSION['fund_stratification_sector_positions_table']	= $fund_stratification_sector_positions_table;
	$_SESSION['fund_maxdate_table']					= $fund_maxdate_table;
	$_SESSION['fund_pricing_table']					= $fund_pricing_table;
	$_SESSION['fund_liveprice_table']				= $fund_liveprice_table;
	$_SESSION['fund_trades_table']					= $fund_trades_table;
	$_SESSION['fund_mtm_table']						= $fund_mtm_table;

	//stock tables
	$_SESSION['stock_companies_table']				= $stock_companies_table;
	$_SESSION['stock_exchanges_table']				= $stock_exchanges_table;
	$_SESSION['stock_prices_table']					= $stock_prices_table;
	$_SESSION['stock_symbols_table']				= $stock_symbols_table;
	$_SESSION['stock_valid_symbols_table']			= $stock_valid_symbols_table;
	$_SESSION['stock_stratification_codes_table']	= $stock_stratification_codes_table;
	$_SESSION['index_history_table']				= $index_history_table;
	$_SESSION['changeactions_table']				= $changeactions_table;
	$_SESSION['dividends_table']					= $dividends_table;
	

	//members tables
	$_SESSION['members_table']						= $members_table;
	$_SESSION['members_flags_table']				= $members_flags_table;
	$_SESSION['members_flags_index_table']			= $members_flags_index_table;
	$_SESSION['members_settings_table']				= $members_settings_table;
	$_SESSION['members_notification_table']			= $members_notification_table;
	$_SESSION['members_notification_types_table']	= $members_notification_types_table;
	$_SESSION['members_profile_table']				= $members_profile_table;

	$_SESSION['obscenities_table']					= $obscenities_table;
	$_SESSION['words_table']						= $words_table;
	$_SESSION['countries_table']					= $countries_table;
	$_SESSION['states_table']						= $states_table;
	
	$_SESSION['connections_table']					= $connections_table;
	$_SESSION['connections_group_table']			= $connections_group_table;
	$_SESSION['profile_questions_table']			= $profile_questions_table;
	$_SESSION['profile_answers_table']				= $profile_answers_table;
	$_SESSION['badges_table']						= $badges_table;

	//community forum tables
	$_SESSION['forums_table']						= $forums_table;
	$_SESSION['forum_categories_table']				= $forum_categories_table;
	$_SESSION['forum_complaints_table']				= $forum_complaints_table;
	$_SESSION['forum_posts_table']					= $forum_posts_table;
	$_SESSION['forum_settings_table']				= $forum_settings_table;
	$_SESSION['forum_topics_table']					= $forum_topics_table;
	$_SESSION['member_forum_settings_table']		= $member_forum_settings_table;
	$_SESSION['forum_viewed_table']					= $forum_viewed_table;
	$_SESSION['forum_groups_table']					= $forum_groups_table;
	
	//email
	

	//+---------------------------------------------------------------------------------------------+
	//									SITE CONFIG SETTINGS										|
	//+---------------------------------------------------------------------------------------------+

	// Get newest site config values
	$query = "
		SELECT *
		FROM site_config conf
		INNER JOIN (
			SELECT max(uid) AS uid, setting
			FROM site_config
			GROUP BY setting
		) dup ON dup.setting = conf.setting
		WHERE conf.uid = dup.uid
	";
	$rsSiteConfig = $mLink->prepare($query);
	$rsSiteConfig->execute();

	// Create variables from the stored settings and assign the stored values to them
	while ($siteConfig = $rsSiteConfig->fetch(PDO::FETCH_ASSOC)){
		$var = trim($siteConfig['setting']); // Just in case there are spaces before or after the value
		$$var = trim($siteConfig['value']);  // Create the var based on the "setting" value, and assign the actual "value" value to it
		
		$_SESSION[$var] = $$var;
	}

	//Assign site variables from the constructed variable's value
	$_SESSION['site_name']				= $site_name;
	$_SESSION['doctype']				= $doctype;
	$_SESSION['content_type']			= $content_type;
	$_SESSION['x_ua_compatible']		= $x_ua_compatible;
	$_SESSION['title']					= $title;
	$_SESSION['reply_to']				= $reply_to;
	$_SESSION['author']					= $author;
	$_SESSION['resource_type']			= $resource_type;
	$_SESSION['revisit_after']			= $revisit_after;
	$_SESSION['classification']			= $classification;
	$_SESSION['distribution']			= $distribution;
	$_SESSION['rating']					= $rating;
	$_SESSION['copyright']				= $copyright;
	$_SESSION['language']				= $language;
	$_SESSION['description']			= $description;
	$_SESSION['keywords']				= $keywords;
	$_SESSION['robots']					= $robots;
	$_SESSION['cache']					= $cache;
	$_SESSION['protocol']				= $protocol;
	$_SESSION['site_root']				= $protocol."://".$base_url;
	$_SESSION['site_protocol']			= $protocol;
	$_SESSION['base_url']				= $base_url;
	$_SESSION['home_page']				= $home_page;
	$_SESSION['forum_posts_default']	= $forum_posts_default;
	$_SESSION['base_path']				= $base_path;
	$_SESSION['rank_date']				= $rank_date;
	$_SESSION['test_bcc_email']			= $test_bcc_email;
	
	// Build array of misc config (meta) tags
	$_SESSION['aTags']			= explode("|",$other_meta);

	// Feature/service on/off switches
	$_SESSION['tour_status']  	= ($tour_status == '1' ? "on" : "off");
	$_SESSION['demo_note']	  	= ($demo_note == '1' ? "on" : "off");
	$_SESSION['debug']	  		= $debug; //Shows misc variables across site

}

//Assign misc needed config varibles
$fundTable	= $_SESSION['fund_table'];
$siteRoot	= $_SESSION['site_root'];
//$homePage	= $_SESSION['home_page']; //Dashboard/Home
$tourStatus	= $_SESSION['tour_status']; // turn off or on the site tour
$demoNote	= $_SESSION['demo_note']; // turn off or on demo notifications


?>