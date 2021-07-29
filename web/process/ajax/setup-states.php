<?php
// This PHP script is run via an AJAX call to validate a desired username during account creation
// Results of any failed check are returned as HTML to be displayed on the form, after which the script is discontinued - no need to proceed

// Start me up...
session_start();

// Load default functions
require("../../includes/system-debug-functions.php");
require("../../includes/system-functions.php");
//echo $_SERVER['QUERY_STRING'];
//echo "<br>";
//echo "x";die();
//Connect to DB
require_once("../../../secure/dbconnect.php");
//echo $_REQUEST['country'];
$setState = strtoupper($_REQUEST['setState']);
$aCountry = explode("|", $_REQUEST['country']);
$country = $aCountry[1];
//echo $country;die();

												//Get States/Provinces
												if ($country == "United States" || $country == "Canada"){
													if ($country == "United States"){
														$query = "
															SELECT *
															FROM ".$_SESSION['states_table']."
															WHERE state_entity = 'State'
															OR state_entity = 'Territory'
															ORDER BY state_entity ASC, state_name ASC
														";
													}else{
														$query = "
															SELECT *
															FROM ".$_SESSION['states_table']."
															WHERE state_entity = 'Province'
															ORDER BY state_name
														";
													}
													try {
														$rsStates = $mLink->prepare($query);
														$rsStates->execute();
													}
													catch(PDOException $error){
														// Log any error
														file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
													}

												}





  															if ($country == "United States"){
																echo '<optgroup label="States">';
  															}else if ($country == "Canada"){
																echo '<optgroup label="Provinces">';
	 														}else{
																break;
															}


															$states = $rsStates->fetch(PDO::FETCH_ASSOC);
															$entity = $states['state_entity'];
//															mysql_data_seek($rsStates, 0);
//															$rsStates->rewind();
															// PDO for MySQL does not have a way to reset the pointer in the result set
															// Therefore we must run the query again to get a fresh set with the pointer at the top
															// Commonly mentioned workarounds do NOT work.
															// Please fix this Zend or Oracle!
															$rsStates->execute();
															// Loop through states, building option list
															while ($states = $rsStates->fetch(PDO::FETCH_ASSOC)){
																if ($states['state_entity'] != $entity){
																	echo '
															</optgroup>
															<optgroup label = "Territories">
																	';
																	$entity = $states['state_entity'];
																}
																
																//Start select state
																//added by Brandon, will only affect the form on account settings
																if($setState == $states['state_abbrev']){
																	$selected = 'selected';
																}else{
																	$selected = '';
																}
																//END select state
																
																echo "<option value=\"".$states['state_abbrev']."|".$states['state_name']."\"".(trim($member['state']) == trim($states['state_name']) ? ' selected' : '')." ".$selected.">".$states['state_name']."</option>\r";
															}
															echo '
															</optgroup>
															';

?>
