<?php
//+---------------------------------------------------------------+
//					MARKETOCRACY - PROJECT NOVA					  |
//					  Single Point Index Page					  |
//+---------------------------------------------------------------+

$membershipLive = 1; #1 = live

// Redirect if javascript is not enabled
echo "
<!DOCTYPE html>
<!--<div style='top:-100px; display:none;'><noscript><meta http-equiv='refresh' content='0;URL=http://www.enable-javascript.com'></noscript></div>-->
";

// Redirect if cookies are not enabled
// Most modern browsers understand navigator.cookieEnabled but if they don't or it returns false, set a cookie and see if it's there just to be sure
echo "
<script>
if (!navigator.cookieEnabled){
	// set and read cookie
	document.cookie = 'cookietest=1';
	if (document.cookie.indexOf('cookietest=') == -1){ // Not found, wasn't set
		// delete cookie (for good measure)
		document.cookie = 'cookietest=1; expires=Thu, 01-Jan-1970 00:00:01 GMT';
		// redirect
//		window.location.href = 'http://www.wikihow.com/Enable-Cookies-in-Your-Internet-Web-Browser';
	}
}
</script>
";

// Redirect to the proper site URL
// Handles the misspelled domain names that point to the site.
$host = array_reverse(explode(".",$_SERVER['HTTP_HOST']));
if (strtolower($host[1]).".".strtolower($host[0]) != "marketocracy.com"){
	header("Location: ".$_SESSION['protocol']."://portfolio.marketocracy.com");
}

// Redirect all alphas - temporary!!
if (strtolower($host[2]) == "alpha"){
	header("Location: ".$_SESSION['protocol']."//portfolio.marketocracy.com");
}
// Redirect all betas - temporary!!
if (strtolower($host[2]) == "beta"){
	header("Location: ".$_SESSION['protocol']."//portfolio.marketocracy.com");
}

// Disable this for production
//error_reporting(E_ALL);  // Show ALL, including warnings and notices
//error_reporting(E_ERROR);  // Just show hard errors
//ini_set('display_errors', '1');  // Show 'em

//Start User Session
session_start();


// Not logged in and not resetting password
if (isset($_SESSION['member_id']) == false
	&& $_REQUEST['task'] != "login"
	&& $_REQUEST['page'] != "11-00-00-002" //Change Password
){
	//Get Redirect Link from URI and append to end of header string
	$redirect = $_SERVER['REQUEST_URI'];

	header('Location: /login'.$redirect.'');
}

if(!isset($_SESSION['admin_override'])){
	// Lock member into the setup wizard. period, if they have no membership level flag set
	if (isset($_SESSION['member_id'])){  // Logged in
	
			
//		if ($_REQUEST['page'] != "10-00-00-001" && // Only if not trying to go to the setup-wizard (or we loop)
                if (isset($_REQUEST['page']) && $_REQUEST['page'] != "10-00-00-001" && // Only if not trying to go to the setup-wizard (or we loop)
			$_REQUEST['page'] != "11-00-00-001" && // or login/logout
			$_SESSION['flag_free'] == 0 &&
			$_SESSION['flag_standard'] == 0 &&
			$_SESSION['flag_student'] == 0 &&
			$_SESSION['flag_premium'] == 0 &&
			$_SESSION['flag_master'] == 0){
			header('Location: /setup-wizard');
		}
	}
}


// Load debug & error logging functions
require("includes/system-debug-functions.php");

// Connect to DB
require("../secure/dbconnect.php");

//echo $_SESSION['rank_date'].'hello-main';
// Load any global functions
require("includes/system-functions.php");



// Get global settings and functions
require("includes/system-global.php");
//dump_vars(get_defined_vars());//die();

//MAX FUNDS CHECK
subscription($mLink, $_SESSION['member_id'], true, false);



if(!isset($_SESSION['admin_override'])){
	if($_SESSION['subscription']['maxFundValid'] == false){
		
		$allowedPages = array(
			//'01-00-00-001', //dashboard
			'10-00-00-004', //transition wizard
			'10-00-00-002', //account settings
			'08-01-00-001', //support ticket
			'08-01-00-002', //support ticket
			'08-01-00-004',	//support ticket
			'08-01-cc-003'
		);
		
		# if a page outside of the allowed pages is accessed while in the trans wiz phase, redirect back to the transition wizard
		if(!in_array($_REQUEST['page'], $allowedPages)){
			
			if($_SESSION['admin'] != 1){
			header('Location: /?page=10-00-00-004');
			}
			//echo $_REQUEST['page'];
		}
		
	}
}


#check for expired fund
$memberData				= new member($dbPortfolio, $dbFeed, $_SESSION['member_id']);
				
$memberLevel = $memberData->getMembershipLevel();

if($memberLevel == 1){
	
	$aFundIDs = $memberData->getFundIDs();
	
	$isExpired = false;
	
	foreach($aFundIDs as $key=>$fundID){
					
		$getFund = new fund($dbPortfolio, $dbFeed, $fundID);
	
		echo $getFund->memberID;
		
		
		$fundExpired = $getFund->isFundExpired();
		
		if($fundExpired == true){
			$isExpired = true;	
		}
			
	}
		
}

if($_SESSION['admin_id'] == 1){
	$isExpired = false;	
}

if($isExpired == true){
	
	$allowedPages = array(
		//'01-00-00-001', //dashboard
		'10-00-00-004',
		'10-00-00-005', //transition wizard
		'10-00-00-002', //account settings
		'08-01-00-001', //support ticket
		'08-01-00-002', //support ticket
		'08-01-00-004',	//support ticket
		'08-01-cc-003'
	);
	
	# if a page outside of the allowed pages is accessed while in the trans wiz phase, redirect back to the transition wizard
	if(!in_array($_REQUEST['page'], $allowedPages)){
		header('Location: /?page=10-00-00-005');
		//echo $_REQUEST['page'];
	}
	
}


//Get User IP
$_SESSION['user_ip'] = get_ip();

//set common objects
$optionList = new optionList($dbPortfolio);


// See if Target Link is set
if($_REQUEST['target'] != ""){
	header("Location: ".$_REQUEST['target']."");
}

trackLink($mLink, $_REQUEST['trackID'], $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);

//FORUM POST PERMALINK REDIRECT
if(isset($_REQUEST['postid'])){
	$lastPostURL = get_post_url($mLink, $_REQUEST['postid']);

	header("Location: ".$lastPostURL."");
}

//echo $_SERVER['HTTP_HOST']; echo $_SERVER['REQUEST_URI'];

//Grab Page ID from URL
$pageID = $_GET['page'];
	//Set Default Page
	if(empty($pageID)) {
		$pageID = $_SESSION['home_page']; //Set in Global
	}
$_SESSION['current_page'] = $pageID;

//Grab Forum ID from URL
$forumID = $_REQUEST['forum'];
$forumMainTitle 	= get_forum($mLink, $forumID, 'title');

//Grab Forum Category ID from URL
$catID 				= $_REQUEST['cat'];
$forumCatTitle 		= get_category($mLink, $catID, 'title');

//Grab Forum Topic ID from URL
$topicID 			= $_REQUEST['topic'];
$forumTopicTitle	= get_topic($mLink, $topicID, 'title');

//Grab Ticket ID from URL
$ticketID 			= $_REQUEST['ticket'];

//Get Class ID from URL
$classID 			= $_REQUEST['class'];

if(isset($ticketID)){
	//Query Support Tickets to check to see if Member can access this Ticket ID
	$query = "
		SELECT member_id
		FROM ".$_SESSION['support_ticket_table']."
		WHERE ticket_id=:ticket_id
	";
	try{
		$rsTicket = $mLink->prepare($query);
		$aValues = array(
			':ticket_id'	=> $ticketID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	//die($preparedQuery);
		$rsTicket->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$ticket = $rsTicket->fetch(PDO::FETCH_ASSOC);
	
	//check to see if memeberID is set
	if (isset($_SESSION['member_id']) == true){
		//Check to see if member is admin
		if($_SESSION['admin'] != "1"){
			//Check to see if user is on appropriate page
			if($pageID != "04-02-cc-003"){//Community Tickets Page
				//check if member can access ticket id
				if($ticket['member_id'] != $_SESSION['member_id']){
					header('Location: /?page=08-01-00-002&note=08-01-00-002-1');
				}
			}
		}
	}
}


//Grab Fund ID from URL
$fundID = $_GET['fund'];



//Make sure users can not force secure pages
if(isset($fundID)){
	$checkFund = explode("-",$fundID);
	
	$checkMemberID = $checkFund[0];
	
	if($checkMemberID != $_SESSION['member_id']) {
		header('Location: /?bad-page=1');	
	}
	//Query Fund Variables
	$query = "
		SELECT fund_name, fund_symbol
		FROM ".$_SESSION['fund_table']."
		WHERE fund_id = :fund_id
		AND active=1
	";
	try{
		$rsFund = $mLink->prepare($query);
		$aValues = array(
			':fund_id'	=> $fundID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	//die($preparedQuery);
		$rsFund->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$fund = $rsFund->fetch(PDO::FETCH_ASSOC);
	
	//Set Page Variables
	$fundName 		= $fund['fund_name'];
	$fundSymbol		= $fund['fund_symbol'];
}





//Query Page Variables
$query = "
	SELECT *
	FROM ".$_SESSION['pages_table']."
	WHERE page_id = :page_id
	AND active=1
"; 
try{
	$rsPage = $mLink->prepare($query);
	$aValues = array(
		':page_id'	=> $pageID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
	$rsPage->execute($aValues);
}
catch(PDOException $error){
	// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$row = $rsPage->fetch(PDO::FETCH_ASSOC);

//Set Page Variables
$pageInclude	= $row['page_include'];
$pageTitle 		= $row['page_title'];
$pageXML 		= $row['xml'];
$pagePlugins	= $row['plugins'];
$pageScripts 	= $row['scripts'];
$pageInit 		= $row['init_scripts'];
$pageStyles		= $row['page_styles'];
$pagePlugStyles	= $row['plugin_styles'];
$jsIncludes		= $row['js_includes'];
$pageForward	= $row['forward'];

//If page forward is set, forward page now
if($pageForward != ''){
	
	header('Location: /'.$pageForward);	
}

//JSON Encode XML for use in array
$pageXML		= simplexml_load_string($pageXML);
$json			= json_encode($pageXML);
$page 			= json_decode($json, TRUE);


//Set Variables
//$pageURI 		= $page['uri'];
$pageSubtitle	= $page['subtitle'];

//----------------------------------------------------------+
//				START GET SECTION NAMES AND IDs				|
//----------------------------------------------------------+
//Explode PageID to get Section Variables
$sections	 	= explode("-",$pageID);

//Set variables for all sections
$section		= $sections[0];
$subSec1		= $sections[1];
$subSec2		= $sections[2];
$pageNumber		= $sections[3];

$fullSecID		= substr($pageID, 0, 8);

$secLevel2 = $sections[0].'-'.$sections[1];

//restrict admin pages
if($section == '20' && $_SESSION['admin'] != '1'){
	header('Location: /');	
}

if(substr($pageNumber,0,1) == '9' && $_SESSION['admin'] != '1'){
	header('Location: /');	
}

//Get Section Names from DB
$secQuery = "
	SELECT section_name, section_url
	FROM ".$_SESSION['sections_table']."
	WHERE section_id = :section_id
";

//Section Name
try{
	$rsSec = $mLink->prepare($secQuery);
	$aValues = array(
		':section_id'	=> $section
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $secQuery); //Debug
//die($preparedQuery);
	$rsSec->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$row1			= $rsSec->fetch(PDO::FETCH_ASSOC);
$secName		= $row1['section_name'];
$secURL			= $row1['section_url'];

//Sub-Section-1 Name
if($subSec1 != "00") {
	//Get Sub Secection 1 ID
	$subSec1Var = substr($pageID, 0 , 5);

	try{
		$rsSec1 = $mLink->prepare($secQuery);
		$aValues = array(
			':section_id'	=> $subSec1Var
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $secQuery); //Debug
//die($preparedQuery);
		$rsSec1->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$row2			= $rsSec1->fetch(PDO::FETCH_ASSOC);
	$subSec1Name	= $row2['section_name'];
	$subSec1URL		= $row2['section_url'];
}else{
	$subSec1Name	= "None";
	$subSec1URL 	= "";
}

//Sub-Section-2 Name
if($subSec2 != "00") {
	//Get Sub Section 2 ID
	$subSec2Var = substr($pageID, 0 , 8);

	try{
		$rsSec2 = $mLink->prepare($secQuery);
		$aValues = array(
			':section_id'	=> $subSec2Var
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $secQuery); //Debug
//die($preparedQuery);
		$rsSec2->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}

	$row3			= $rsSec2->fetch(PDO::FETCH_ASSOC);
	$subSec2Name	= $row3['section_name'];
	$subSec2URL		= $row3['section_url'];
}else{
	$subSec2Name	= "None";
	$subSec2URL		= "";
}
//----------------------------------------------------------+
//				END GET SECTION NAMES AND IDs				|
//----------------------------------------------------------+

//START FORUM VALIDATION
if($secLevel2 == '04-01'){ //Forums section
	$forumID = $_REQUEST['forum'];
	
	if($forumID != ''){
		
		//Check if user has access to the FORUM level
		$allowAccess = forumAccess($mLink, $forumID, 'forum');
		
		//Allow access to anyone on the View Forums page		
		if($pageID == '04-01-00-001'){
		 $allowAccess = 1;
		}
		
		//If the member does not have access to forum
		if($allowAccess == 0){
			//only forward if member is logged in, this is to make sure forward links work outside of the login
			if($_SESSION['member_id'] != ''){
				//redirect to forum home page and display an permissions error
				header('Location: /?page=04-01-00-001&forumError=1');
			}
		}
		
		//Check to see if member is allowed in a category
		if($pageID == '04-01-00-003'/*View Category*/ || $pageID == '04-01-00-004'/*View Topics*/){ 
			
			//first check to make sure they passed the forum access check
			if($allowAccess != 0){
				//Get category ID from the URL
				$catID = $_REQUEST['cat'];
				
				//temp link fix
				if($catID == ''){
					$catID = '81';	
				}
				
				//Now check against the category access tables and reassign the variable
				$allowAccess = forumAccess($mLink, $catID, 'category');
			}
			
			//if the member does not pass the above check, redirect them to the forum directory
			if($allowAccess == 0){
				//only forward if member is logged in
				if($_SESSION['member_id'] != ''){
					//redirect to forum directory
					header('Location: /?page=04-01-00-002&forum='.$forumID.'&forumError=2');
				}
			}
		}
	}
	
	if($pageID == '04-01-00-001'){
		$adminAccess = forumAdminAccess($mLink, $forumID, 'forum');	
	}
	if($pageID == '04-01-00-002' || $pageID == '04-01-00-003' || $pageID == '04-01-00-004'){
		
		$adminAccess = forumAdminAccess($mLink, $forumID, 'forum');
		
		/*if($adminAcess != 0){
			$adminAccess = forumAdminAccess($mLink, $forumID, 'cat');
		}*/
	}
}
//END FORUM VALIDATION


	
//Set site referal source as session variable
$linkSource = $_GET['source'];
	//Check to see if session is set
	if($linkSource == "") {}else{
		$_SESSION['referral_source'] = $linkSource;
	}

//Other Page Variables
$currentPage = $_SERVER["REQUEST_URI"];

//if($pageID == "11-00-00-001" /*Login*/){
if($pageID == "11-00-00-001" /*Login*/ || $pageID == "11-00-00-002" /*Change Password*/){
	$bodyClass = "login";
	
	
	
}else{
	$bodyClass = "page-header-fixed";
}

//Get Link Tracking Code
include('includes/link-tracking.php');

//create array vars for portlets
$phArray 		= array(); //Price History Portlet
$riArray 		= array(); //Returns Vs Index Portlet
$recentArray	= array(); //Recent Returns Portlet
$turnoverArray	= array(); //Turnover Array
$fundInfoArray	= array(); //Fund Info Array
$bwArray		= array(); //Best Worst Array
$profitArray	= array(); //Most and Least Profitable
$posSecArray	= array(); //Postions by Sectors Portlet
$posStyleArray	= array(); //Postions by Style Portlet


//+---------------------------------------------------------------+
//|								SEARCH						 	  |
//+---------------------------------------------------------------+
//echo $_REQUEST['search'];
if(isset($_REQUEST['search']) && $_REQUEST['search'] != '' && $pageID == '01-00-00-002'){
	//Valid search request
	
	
	$process = '1'; //Main search process
	include_once('process/ajax/search-processes.php');
		
}


//+---------------------------------------------------------------+
//						START BUILDING PAGE						  |
//+---------------------------------------------------------------+
//echo dump_array(get_defined_vars());die();

//START DOCTYPE (moved to the top of this file - JS)
//echo $_SESSION['doctype'];
$welcomeMessage = get_member($mLink, $_SESSION['member_id'], 'welcome');
?>

<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!--> <html lang="en" class="no-js"> <!--<![endif]-->

<!-- Comodo "Corner of Trust" Site Seal -->
<!--<script type="text/javascript">
//<![CDATA[
var cotJsHost = ((window.location.protocol == "https:") ? "https://secure.comodo.com/" : "http://www.trustlogo.com/");
document.write(unescape("%3Cscript src='" + cotJsHost + "trustlogo/javascript/cot.js' type='text/javascript'%3E%3C/script%3E"));
//]]>
</script>-->

<!-- BEGIN HEAD -->

<head>
	<title><?php echo $_SESSION['site_name'];?>&nbsp;|&nbsp;<?php echo $pageTitle; ?></title>

	<!-- START META tags -->
	<meta http-equiv="Content-Type" content="<?php echo $_SESSION['site_name'];?>">
	<?php
	// Only add these tags if you want to disable caching
	if ($_SESSION['cache'] != true){
	?>
		<meta http-equiv="Expires" content="Wed, 01 Jan 1990 00:00:01 GMT">
		<meta http-equiv="Pragma" content="no-cache">
	<?php
	}
	?>
	<meta http-equiv="X-UA-Compatible" content="<?php echo $_SESSION['x_ua_compatible'];?>">
	<meta http-equiv="title" content="<?php echo $_SESSION['title'];?>">
	<meta http-equiv="reply-to" content="<?php echo $_SESSION['reply_to'];?>">
	<meta name="Author" content="<?php echo $_SESSION['author'];?>">
	<meta name="resource-type" content="<?php echo $_SESSION['resource_type'];?>">
	<meta name="revisit-after" content="<?php echo $_SESSION['revisit_after'];?>">
	<meta name="classification" content="<?php echo $_SESSION['classification'];?>">
	<meta name="distribution" content="<?php echo $_SESSION['distribution'];?>">
	<meta name="rating" content="<?php echo $_SESSION['rating'];?>">
	<meta name="copyright" content="Copyright 2000-<?php echo date("Y");?>, <?php echo $_SESSION['copyright'];?>">
	<meta name="language" content="<?php echo $_SESSION['language'];?>">
	<meta name="Description" content="<?php echo $_SESSION['description'];?>">
	<meta name="Keywords" content="<?php echo $_SESSION['keywords'];?>">
	<meta name="robots" content="<?php echo $_SESSION['robots'];?>"> <!-- all, none, index, noindex, follow or nofollow-->
	<?php
	// Loop through the array of additional tags and add them
	for ($tagCnt = 0; $tagCnt < sizeof($_SESSION['aTags']); $tagCnt++){
		echo $_SESSION['aTags'][$tagCnt]."\n";
	}
	?>
	<!--END META TAGS-->

	<!-- Set the center of the universe -->
	<base href="/">

	<!-- BEGIN GLOBAL MANDATORY STYLES -->
	<link href="<?php echo $siteRoot;?>plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
	<link href="<?php echo $siteRoot;?>plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
	<link href="<?php echo $siteRoot;?>plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $siteRoot;?>plugins/bootstrap-toastr/toastr.css" rel="stylesheet" type="text/css"/>
	<!-- END GLOBAL MANDATORY STYLES -->

	<!-- BEGIN PAGE LEVEL PLUGIN STYLES -->
	<?php echo $pagePlugStyles; ?>
	<!-- END PAGE LEVEL PLUGIN STYLES -->

	<!-- BEGIN THEME STYLES -->
	<!--Start Custom-->
	<?php echo $pageStyles; ?>
	<!--End Custom-->

	<link href="<?php echo $siteRoot;?>css/style-minc.css" rel="stylesheet" type="text/css"/>
	<link href="<?php echo $siteRoot;?>css/style.css" rel="stylesheet" type="text/css"/>
	<link href="<?php echo $siteRoot;?>css/style-responsive.css" rel="stylesheet" type="text/css"/>
	<link href="<?php echo $siteRoot;?>css/plugins.css" rel="stylesheet" type="text/css"/>
	<link href="<?php echo $siteRoot;?>css/themes/default.css" rel="stylesheet" type="text/css" id="style_color"/>
	<link href="<?php echo $siteRoot;?>css/custom.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $siteRoot;?>plugins/jquery-ui/jquery-ui-1.10.3.custom.min.css" rel="stylesheet" type="text/css"/>
	<!-- END THEME STYLES -->

	<!--FAV ICON-->
	<link rel="shortcut icon" href="<?php echo $siteRoot;?>images/favicon.ico" />
	<!--END FAV ICON-->

</head>
<!-- END HEAD -->

<!-- Comodo "Corner of Trust" Site Seal -->
<!--<a href="http://www.instantssl.com" id="comodoTL">2048 Bit SSL Certificate</a>-->
<!--<script language="JavaScript" type="text/javascript">
COT("/images/comodo-ev-cornertrust.gif", "SC2", "none");
</script>-->

<!-- BEGIN BODY -->
<body class="<?php echo $bodyClass; ?> page-sidebar-fixed">
	
	<?php
	//echo $_SESSION['admin_id'];
    // Get Toolbar (Logo, Search, Top Navigation)
    include('includes/nav-toolbar.php');

    //If on the login page, hide sidebar and breadcrumb and load in login page content separately
	if($pageID != "11-00-00-001" /*Login*/ && $pageID != "11-00-00-002" /*Change Password*/){
   
   	
    ?>


    <!-- BEGIN DETAILS MODAL-->
    <div class="modal fade" id="welcome" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Welcome to the new Portfolio!</h4>
                </div>
                <div class="modal-body" id="submit-trades">
                    <p>Welcome to the soft launch of the new Portfolio. If you experience any problems, please use the new Support Ticket System to report an issue or give us feedback. To access this, click on "Support" > "Support Tickets" > "Create New", in the left hand menu.</p>
                    
                    <p>You can also access the Support Ticket System by choosing an option under the <i class="icon-question-sign"></i> Icon in the top toolbar.</p>
                </div><!--modal-body-->
                <div class="modal-footer">
                	<button type="button" class="btn btn-info" data-dismiss="modal" onClick="markWelcomeRead('<?php echo $_SESSION['member_id'];?>');">Don't show this message again.</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
    <!-- END DETAILS MODAL-->
    
    <!-- BEGIN DETAILS MODAL-->         
    <div class="modal fade" id="sell-error" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Can Not Open Sell Wizard</h4>
                </div>
                <div class="modal-body" id="submit-trades">
                    <div class="note note-warning">
                        <p>The fund you have selected does not have any positions to sell. Please click the Buy Wizard to buy positions, or select the Sell Wizard of a different fund.</p>
                    </div>
                </div><!--modal-body-->
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
    <!-- END DETAILS MODAL-->
    
    <!-- BEGIN DETAILS MODAL-->         
    <div class="modal fade" id="buy-error" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Can Not Open Buy Wizard</h4>
                </div>
                <div class="modal-body" id="submit-trades">
                    <div class="note note-warning">
                        <p>The fund you have selected has negative cash. Please click the Sell Wizard to sell positions to bring your fund to a positive cash amount.</p>
                    </div>
                </div><!--modal-body-->
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
    <!-- END DETAILS MODAL-->
    
    <!-- BEGIN DETAILS MODAL-->         
    <div class="modal fade" id="strat-updating" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Stratification Updating</h4>
                </div>
                <div class="modal-body" id="submit-trades">
                    
                    <div class="note note-info">
                        <p><strong><img src="images/ajax-refresh.gif" /> Stratification is currently being updated.</strong></p>
                        <p>You will automatically be redirected to the Trade Wizard once your stratification has been updated. This should only take a few moments. Or you can close this modal and try again later.</p>
                    </div>
                    
                </div><!--modal-body-->
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
    <!-- END DETAILS MODAL-->
    
	<!-- BEGIN CONTAINER -->
	<div class="page-container">
        <!-- BEGIN SIDEBAR -->
		
      	<div class="page-sidebar navbar-collapse collapse">	
			<?php include('includes/nav-sidebar.php'); ?>
        </div>
      	<!-- END SIDEBAR -->

    	
        
        <!-- BEGIN PAGE -->
        <div class="page-content">
        	<?php if($_SESSION['admin'] == '1'){
			/*echo '<pre>';
			echo $allowAccess;
			echo '|';
			
			echo '</pre>';*/
			} ?>
  			<?php /*?><div style="background: #EFEFEF; border-bottom:1px solid #e6e6e6; margin:-25px -20px 20px -20px; height:38px;padding:9px 15px 0px 15px;float:right; border-left:1px solid #e6e6e6;" id="nasdaq-feeds">
            	
            </div><?php */?>
            
           <?php //Portlet Section ?>
  
           <!-- BEGIN PAGE HEADER-->
           <?php
			if(isset($_SESSION['admin_id'])){
				
				if(!isset($_SESSION['admin_override'])){
					echo '
						<div class="row">
							<div class="col-md-12">
								<div class="alert alert-info">
									<button class="close" data-dismiss="alert"></button>
									<h4>Admin Override</h4>
									<p>If you need to view this members profile information please click the override button below.</p>
									<p><a href="/process/ajax/admin-override.php?process=override" class="btn btn-default">Override</a></p>
								</div>
							</div>
						</div>
					';	
				}else{
					echo '
						<div class="row">
							<div class="col-md-12">
								<div class="alert alert-info">
									<button class="close" data-dismiss="alert"></button>
									<h4>Admin Override</h4>
									<p>If you need to switch back to user mode, click the button below.</p>
									<p><a href="/process/ajax/admin-override.php?process=rtn-user-mode" class="btn btn-default">Return to User Mode</a></p>
								</div>
							</div>
						</div>
					';	
				}
					
			}
			?>
           
           
           <div class="row">
              <div class="col-md-12">
  
                 
  
                 <!-- BEGIN PAGE TITLE & BREADCRUMB-->
                 <h3 class="page-title">
                    <?php 
					
					
					
					if(isset($fundID)){ 
						echo $fundSymbol;
					}else{ 
						echo $pageTitle; 
					}?> 
                    <small>
					<?php 
					if(isset($forumID)){
						echo get_forum($mLink, $forumID, 'title');
					}else{
						if(isset($ticketID)){
							echo "Ticket:"; echo $ticketID; 
						}else{ 
							echo $pageSubtitle; 
						}
					}
					?>
                    </small>
                    
                    <?php if($_SESSION['debug'] == 1) {?>
                    	<br />
                    	<span><?php echo $section; ?> | <?php echo $subSec1; ?> | <?php echo $subSec2; ?> | <?php echo $pageNumber; ?></span> 
                    	: Member ID= <?php echo $_SESSION['member_id']; ?> : Fund ID= <?php echo $fundID; ?> : Section ID= <?php echo $fullSecID; ?> : <?php echo $subSec1Var; ?><br />
                    	<span><small><?php echo $secName; ?></small> | <small><?php echo $subSec1Name; ?></small> | <small><?php echo $subSec2Name; ?></small> | <small><?php echo $pageTitle; ?></small></span>
                        <div id="status-update" style="font-size:12px;"></div>
                        <span>Layout: <?php echo $_SESSION['layout'];?></span>
                   <?php } ?>
                   
                 </h3>
  				 
                 <!--START SYSTEM NOTES-->
                 <?php include("includes/system-notifications.php"); ?>
                 <!--END SYSTEM NOTES-->
                 
                 <?php include('includes/nav-breadcrumb.php'); ?>
                 
              </div><!-- END .col-md-12-->
           </div><!-- END .row-->
           <!-- END PAGE HEADER-->
  			
           	<?php
			$query = "
		   		SELECT *
				FROM ".$_SESSION['members_alerts_table']."
				WHERE member_id=:member_id AND active='1'
		   	";
		   	try{
				$rsAlert = $mLink->prepare($query);
				$aValues = array(
					':member_id'	=> $_SESSION['member_id']
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $secQuery); //Debug
		//die($preparedQuery);
				$rsAlert->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			while($mAlert	= $rsAlert->fetch(PDO::FETCH_ASSOC)){
				
				echo '

					<div class="alert alert-'.$mAlert['alert_type'].'">
						<h4>'.$mAlert['alert_title'].'</h4>
						<p>'.$mAlert['alert_message'].'</p>
					</div>
				';
				
			}
			
			
		   	//+--------------------------------------------------------+
           	//|				START PAGE BODY CONTENT					  |
           	//+--------------------------------------------------------+
  		   
		   	include('includes/pages/'.$pageInclude.'');
		   
		   	//include global modals here so they pop up in front of any page loaded modal
		   	include('includes/global-modals.php');
		   	?>
  			
            
        </div><!--END page-content-->
        <!-- END PAGE -->
        
        
        
	</div><!--END page-container-->
	<!-- END CONTAINER -->
	<?php
    //LOGIN IF STATEMENT
    }else{
        if($pageID == "11-00-00-002") {
            //Include password reset page
            include('includes/pages/password-reset.php');
        }else{
            //Include Login Page
            include('includes/pages/login.php');
        }
    }
    
    //Include footer on all pages but the login page
    if($pageID != "11-00-00-001" /*Login*/ && $pageID != "11-00-00-002" /*Change Password*/){?>
    
		<!-- BEGIN FOOTER -->
		<div class="footer">
        	
		  <div class="footer-inner">
			 &copy; <?php echo date('Y');?>, Marketocracy Membership Platform | All quotes are delayed 15 minutes.<?php //print_r($turnoverArray);?>
		  </div>
		  <div class="footer-tools" id="top-page">
			 <span class="go-top">
			 <i class="icon-angle-up"></i>
			 </span>
		  </div>
          
          
              <?php
				///////// Display SESSION VARS
				//if ($_SESSION['member_id'] == 1){  // Jeff (testing)
				if ($_SESSION['super_admin'] == "1" || $_REQUEST['pw'] == 'KfabyZcbE3'){
					if($_REQUEST['debug'] == '1'){

					//echo transactionDetails($mLink, '8-1479926744', 'invoiceNumber', 'html');

					echo "<div><h3>Page Debug</h3>";

					echo '<pre>';
					if(empty($aDebug)){
						echo '<p>No Debug array present on this page.</p>';
					}else{
						print_r($aDebug);

					}
					echo '</pre>';

					echo "<div><h3>Site Debug</h3>";

					echo '<pre>';
					
					print_r($_SESSION['debug']);
					
					if(empty($_SESSION['debug'])){
						echo '<p>Site Debug array is empty</p>';
					}else{
						print_r($_SESSION['debug']);

					}
					echo '</pre>';

					echo "<div><h3>Subscription Data</h3>";
					echo '<pre>';
						print_r($_SESSION['subscription']);
					echo '</pre>';

					echo '<h3>Sessions</h3>';
					echo '<pre><br>&nbsp;&nbsp;&nbsp;&nbsp;SESSION VARS:<br>';
					print_r($_SESSION);
					echo "</pre></div>";
					}
				}
				?>

		</div>
		<!-- END FOOTER -->
        
        
        
    <?php
    }
    ?>
	

    
    <?php include("includes/system-google-analytics.php"); ?>
    
    <!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
    <!-- BEGIN CORE PLUGINS -->
    <!--[if lt IE 9]>
    <script src="<?php echo $siteRoot;?>plugins/respond.min.js"></script>
    <script src="<?php echo $siteRoot;?>plugins/excanvas.min.js"></script>
    <![endif]-->
    <script src="<?php echo $siteRoot;?>plugins/jquery-1.10.2.min.js" type="text/javascript"></script>
    <!--<script src="<?php echo $siteRoot;?>plugins/jquery-1.11.1.min.js" type="text/javascript"></script>-->
    <script src="<?php echo $siteRoot;?>plugins/jquery-migrate-1.2.1.min.js" type="text/javascript"></script>
    <!-- IMPORTANT! Load jquery-ui-1.10.3.custom.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
    <script src="<?php echo $siteRoot;?>plugins/jquery-ui/jquery-ui-1.10.3.custom.min.js" type="text/javascript"></script>
    <script src="<?php echo $siteRoot;?>plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="<?php echo $siteRoot;?>plugins/bootstrap-hover-dropdown/twitter-bootstrap-hover-dropdown.min.js" type="text/javascript" ></script>
    <script src="<?php echo $siteRoot;?>plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
    <script src="<?php echo $siteRoot;?>plugins/jquery.blockui.min.js" type="text/javascript"></script>
    <script src="<?php echo $siteRoot;?>plugins/jquery.cookie.min.js" type="text/javascript"></script>
    <script src="<?php echo $siteRoot;?>plugins/uniform/jquery.uniform.min.js" type="text/javascript" ></script>
    <?php /*?><script src="plugins/jquery.balloon.min.js" type="text/javascript" ></script><?php */?>
    <script src="<?php echo $siteRoot;?>plugins/bootstrap-toastr/toastr.min.js"></script>
	<?php /*?><script src="plugins/jquery-session-timeout/jquery.sessionTimeout.1.0.min.js"></script><?php */?>
    <!-- END CORE PLUGINS -->
    
    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <?php	
    // Get Page Level Plugins (Source: DB: nova - pages)
    echo $pagePlugins; ?>
    <script src="<?php echo $siteRoot;?>js/system-add-remove-portlets.js" type="text/javascript"></script>
    <script src="<?php echo $siteRoot;?>js/quick-trade.js" type="text/javascript"></script>
    <!-- END PAGE LEVEL PLUGINS -->

    <!--START CHART PLUGIN-->
   	<?php /*?><script type="text/javascript" src="<?php echo $siteRoot;?>plugins/highcharts/highstock.js" ></script><?php */?>
    <script type="text/javascript" src="<?php echo $_SESSION['protocol'];?>://code.highcharts.com/stock/highstock.src.js" ></script>
	<script type="text/javascript" src="<?php echo $_SESSION['protocol'];?>://code.highcharts.com/stock/modules/exporting.src.js" ></script>
    <!--END CHART PLUGIN-->
    
    <!--STARTR SEARCH FUNCTION-->
    <script>
	function loadCheckStrat(fundID, wizType){
		var checkRate2 = 5000;

		$( document ).ready(checkStratProcess2 = function() {
			$.ajax({
				data: {fund:fundID, wizType:wizType},
				url: 'process/ajax/strat-check.php?process=strat-check',
				success: function(data) {
					
					if(data.indexOf('?page=') >= 0){
						window.location.href = data;	
					}
					
				}
			
			});
		});
		var refreshCheck2 = setInterval(checkStratProcess2, checkRate2);	
	}
	
	function goToTradeWiz(wizType, fundID){
		//alert('hello');
		
		$.ajax({
			data: {fund:fundID, wizType:wizType},
			url: 'process/ajax/strat-check.php?process=strat-check',
			success: function(data) {
				
				if(data == '#strat-updating'){
					//launch modal
					$(data).modal('show');
					
					loadCheckStrat(fundID, wizType);
						
				}else{
					if(data.indexOf('#') >= 0){
						$(data).modal('show');	
					}
					
					if(data.indexOf('?page=') >= 0){
						window.location.href = data;	
					}
				}
			}
		
		});
		
		
	}
	<?php 
	
	
	
	if($pageID == '01-00-00-001'){
	if($welcomeMessage == '0'){
			?>
			$(document).ready(function(e) {
				$('#welcome').modal('toggle');
			});
			<?php 
	}
	}
	?>
	function markWelcomeRead(memberID){
		 $.ajax({
				url : 'process/ajax/welcome-message.php?process=1',
				dataType: "POST",
				data: {memberID:memberID},
				success: function( data ) {
					
				}
			});
	 }
	 
	 
	
	$('.accordion-toggle').click(function(){
		
		if($(this).find('.accordion-icons').hasClass('accordion-expand')){
			//alert('expand');
			$(this).find('.accordion-icons').removeClass('accordion-expand');	
			$(this).find('.accordion-icons').addClass('accordion-collapse');	
		}else{
			//alert('collapse');
			$(this).find('.accordion-icons').removeClass('accordion-collapse');	
			$(this).find('.accordion-icons').addClass('accordion-expand');	
		}
		
	});
	
	$('.disable-click').click(function(event){
		event.stopPropagation();
	});
	
	function searchType(icon,filter){
		$('#search-icon').html('<i class="icon-'+icon+'"></i>');
		$('#search-filter').val(filter);	
	}
	
	$('#search-bar').autocomplete({
		source: function( request, response ) {
			$.ajax({
				url : '<?php echo $siteRoot;?>process/ajax/search-processes.php?process=3',
				dataType: "json",
				data: {
				   search_term: request.term,
				   type: 'country'
				},
				 success: function( data ) {
					 response( $.map( data, function( item ) {
						return {
							label: item,
							value: item
						}
					}));
				}
			});
		},
		autoFocus: true,
		minLength: 0      	
	});
	</script>
    <!--END SEARCH FUNCTION-->
    
    <!-- BEGIN PAGE LEVEL SCRIPTS -->
    <?php
    // Get Page Level Scripts (Source: DB: nova - pages)
    echo $pageScripts; 	
	
	// Get Scripts that require php
	$javascripts = explode('|', $jsIncludes);
		
	foreach($javascripts as $key => $value) {
		$scripts 	= $value;
		
		include('includes/scripts/'.$scripts.'.php');
	}

	//include global modal scripts
	include('includes/scripts/global-modals.js.php');
	
	echo '<!--';
		echo 'here I am';
		echo $pageID;
		echo '-->';
	
	//START PORTLET SCRIPTS
	// Get Portlet scripts when needed
	if($pageID == $_SESSION['home_page']){
		
		echo '<!--';
		echo 'here I am';
		echo $_SESSION['home_page'];
		echo '-->';
		
		if(!isset($_SESSION['layout']) or $_SESSION['layout'] == "2"){
			//start 2 column layout
			if(!empty($column1)){
				foreach($column1 as $key => $value) {
					
					//If the value is blank, skip it. Requires will break the page if they are empty
					if($value == ""){
						continue;	
					}
					
					//Expand portlet ID and set variables
					$portletID 	= $value;
					$portlet 	= explode("~",$portletID);
					//Set Variables
					$portID		= $portlet[0];
					if($portlet[1] != "0"){
						$portVar1	= $portlet[1];
					}
					if($portlet[2] != "0"){
						$portVar2	= $portlet[2];
					}
					if($portlet[3] != "0"){
						$portVar3	= $portlet[3];
					}
					
					//Require once so not to load multiple copies of the same script
					require_once('includes/scripts/portlets/'.$portID.'.js.php');
				}
			}
			
			if(!empty($column2)){		
				foreach($column2 as $key => $value) {
					
					//If the value is blank, skip it. Requires will break the page if they are empty
					if($value == ""){
						continue;	
					}
					
					//Expand portlet ID and set variables
					$portletID 	= $value;
					$portlet 	= explode("~",$portletID);
					//Set Variables
					$portID		= $portlet[0];
					if($portlet[1] != "0"){
						$portVar1	= $portlet[1];
					}
					if($portlet[2] != "0"){
						$portVar2	= $portlet[2];
					}
					if($portlet[3] != "0"){
						$portVar3	= $portlet[3];
					}
					require_once('includes/scripts/portlets/'.$portID.'.js.php');
				}
			}
			
		}elseif($_SESSION['layout'] == "4"){
			
			if(!empty($column1)){
				foreach($column1 as $key => $value) {
					
					//If the value is blank, skip it. Requires will break the page if they are empty
					if($value == ""){
						continue;	
					}
					
					//Expand portlet ID and set variables
					$portletID 	= $value;
					$portlet 	= explode("~",$portletID);
					//Set Variables
					$portID		= $portlet[0];
					if($portlet[1] != "0"){
						$portVar1	= $portlet[1];
					}
					if($portlet[2] != "0"){
						$portVar2	= $portlet[2];
					}
					if($portlet[3] != "0"){
						$portVar3	= $portlet[3];
					}
					require_once('includes/scripts/portlets/'.$portID.'.js.php');
				}
			}
			
			if(!empty($column2)){		
				foreach($column2 as $key => $value) {
					
					//If the value is blank, skip it. Requires will break the page if they are empty
					if($value == ""){
						continue;	
					}
					
					//Expand portlet ID and set variables
					$portletID 	= $value;
					$portlet 	= explode("~",$portletID);
					//Set Variables
					$portID		= $portlet[0];
					if($portlet[1] != "0"){
						$portVar1	= $portlet[1];
					}
					if($portlet[2] != "0"){
						$portVar2	= $portlet[2];
					}
					if($portlet[3] != "0"){
						$portVar3	= $portlet[3];
					}
					require_once('includes/scripts/portlets/'.$portID.'.js.php');
				}
			}
			
			if(!empty($column3)){
				foreach($column3 as $key => $value) {
					
					//If the value is blank, skip it. Requires will break the page if they are empty
					if($value == ""){
						continue;	
					}
					
					//Expand portlet ID and set variables
					$portletID 	= $value;
					$portlet 	= explode("~",$portletID);
					//Set Variables
					$portID		= $portlet[0];
					if($portlet[1] != "0"){
						$portVar1	= $portlet[1];
					}
					if($portlet[2] != "0"){
						$portVar2	= $portlet[2];
					}
					if($portlet[3] != "0"){
						$portVar3	= $portlet[3];
					}
					
					require_once('includes/scripts/portlets/'.$portID.'.js.php');
				}
			}
			
			if(!empty($column4)){
				foreach($column4 as $key => $value) {
					
					//If the value is blank, skip it. Requires will break the page if they are empty
					if($value == ""){
						continue;	
					}
					
					//Expand portlet ID and set variables
					$portletID 	= $value;
					$portlet 	= explode("~",$portletID);
					//Set Variables
					$portID		= $portlet[0];
					if($portlet[1] != "0"){
						$portVar1	= $portlet[1];
					}
					if($portlet[2] != "0"){
						$portVar2	= $portlet[2];
					}
					if($portlet[3] != "0"){
						$portVar3	= $portlet[3];
					}
					
					require_once('includes/scripts/portlets/'.$portID.'.js.php');
				}
			}
		}
		
	}
	if($pageID == "03-01-00-001"/*fund-overview*/ || $pageID == "04-00-00-001"){
		
		foreach($column1 as $key => $value) {
			
			//If the value is blank, skip it. Requires will break the page if they are empty
			if($value == ""){
				continue;	
			}
					
			//Expand portlet ID and set variables
			$portletID 	= $value;
			$portlet 	= explode("~",$portletID);
			//Set Variables
			$portID		= $portlet[0];
			if($portlet[1] != "0"){
				$portVar1	= $portlet[1];
			}
			if($portlet[2] != "0"){
				$portVar2	= $portlet[2];
			}
			if($portlet[3] != "0"){
				$portVar3	= $portlet[3];
			}
			require_once('includes/scripts/portlets/'.$portID.'.js.php');
		}
				
		foreach($column2 as $key => $value) {
			
			//If the value is blank, skip it. Requires will break the page if they are empty
			if($value == ""){
				continue;	
			}
			
			//Expand portlet ID and set variables
			$portletID 	= $value;
			$portlet 	= explode("~",$portletID);
			//Set Variables
			$portID		= $portlet[0];
			if($portlet[1] != "0"){
				$portVar1	= $portlet[1];
			}
			if($portlet[2] != "0"){
				$portVar2	= $portlet[2];
			}
			if($portlet[3] != "0"){
				$portVar3	= $portlet[3];
			}
			require_once('includes/scripts/portlets/'.$portID.'.js.php');
		}
		
	}
	//END PORTLET SCRIPTS
	
	//Include Notifications and notification counters for every page
	if($pageID != $_SESSION['home_page'] && $pageID != "11-00-00-001" /*Login*/ && $pageID != "11-00-00-002" /*Change Password*/){
		include('includes/scripts/system-ajax-counters.php');
	}

	//Include Heartbeat script for every page once logged in
//	if($pageID != "11-00-00-001" /*Login*/ && $pageID != "11-00-00-002" /*Change Password*/){
	if (isset($_SESSION['member_id'])){
		include('includes/scripts/system-heartbeat.php');
	}

	?>
    <!-- END PAGE LEVEL SCRIPTS -->
    
    <?php
    //Initialize Java Scripts (Source: DB: nova - pages)
    echo $pageInit; ?>

    <?php if($tourStatus == "on") {include("includes/system-tour-scripts.php");} ?>

	<!-- Session Timeout -->
	<?php //if (isset($_SESSION['member_id']) == true){ include('includes/scripts/system-session-timeout.php'); }	?>
	<?php
		if (isset($_SESSION['member_id']) == true){  // Logged in
			if ($_REQUEST['page'] != "10-00-00-001"){  // NOT in setup wizard
				include('includes/scripts/system-session-timeout.php');
			}
		}
	?>

	<!-- END JAVASCRIPTS -->



</body>
<!-- END BODY -->
</html>
