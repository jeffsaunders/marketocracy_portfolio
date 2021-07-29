<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
// This PHP script is run via an AJAX call to score the password as it's being entered
// Results are returned as HTML to be displayed on the form, after which the script is discontinued - no need to proceed

// Start me up...
session_start();

// Load default functions
require("../../includes/system-debug-functions.php");

require("../../includes/system-functions.php");echo getPasswordStrength(urldecode($_REQUEST['username']), urldecode($_REQUEST['password']));



function scorePassword($un,$pw){
	$score = 0;

	// Can't do much if I get blanks
	if (!$un){
		return "-2|Username cannot be blank.";
	}
//	if (!$pw){
//		return "X|Password cannot be blank.";
////		return "X";
//	}

	// Make sure password doesn't contain any spaces
	if (stripos($pw, " ") !== false){
		return "-1|Password cannot contain spaces.";
	}

	// Now add points to create a score -

	// Award every unique letter until 5 repetitions
	$letters = str_split($pw);
	$scores = array();
	foreach ($letters as $letter){
		$scores[$letter] = (isset($scores[$letter])) ? $scores[$letter] + 1 : 1;
		$score += 5 / $scores[$letter];
	}

	// Bonus points for mixing it up
	// d = numeric digit (numeral)
	// a-z = lower case letter
	// A-Z = upper case letter
	// W = cpecial character (punctuation, etc.)
	$variations = array(
		preg_match('/\d/', $pw),
		preg_match('/[a-z]/', $pw),
		preg_match('/[A-Z]/', $pw),
		preg_match('/\W/', $pw),
	);

	$variationCount = 0;
	foreach($variations as $check){
		$variationCount += ($check) ? 1 : 0;
	}
	$score += ($variationCount - 1) * 10;

	// Now take points away for bad behavior

	// Compare everything in lower case
    $lower_pw = strtolower($pw);
    $lower_un = strtolower($un);
    // 1337 5P34K (leet speak) check to spank the kiddies
    $leet_pw = strtr($lower_pw, '754301!', 'tsaeoll');
    $leet_un = strtr($lower_un, '754301!', 'tsaeoll');

    // the password must be at least {$_REQUEST['password-minimum']} characters
    if (strlen($pw) < $_SESSION['password_minimum_length']){
		$score -= 25; //Very weak - unacceptable
		return max(0, round($score))."|The password must contain at least ".$_SESSION['password_minimum_length']." characters.";
	    }

    // The password can't contain the username (or reversed username)
	if (
		(stripos($lower_pw, $lower_un) !== false) ||
		(stripos($lower_pw, strrev($lower_un)) !== false) ||
		(stripos($leet_pw, $lower_un) !== false) ||
		(stripos($leet_pw, strrev($lower_un)) !== false)
	){
		$score = -1; //Invalid
		return round($score)."|The password cannot contain the username or reversed username.";
    }

    // Count how many lowercase, uppercase, digits, and special characters are in the password
    $uc		= 0;
	$lc		= 0;
	$num	= 0;
	$other	= 0;
	$pwLen	= strlen($pw);

	for ($cnt = 0; $cnt < $pwLen; $cnt++){
		$char = substr($pw, $cnt, 1);
		if (preg_match('/^[[:upper:]]$/', $char)) {
			$uc++;
		}elseif (preg_match('/^[[:lower:]]$/', $char)){
			$lc++;
		}elseif (preg_match('/^[[:digit:]]$/', $char)){
			$num++;
		}else{
			$other++;
		}
	}

	// The password must have at least two different kinds (upper, lower, number, punctuation)
	$max = $pwLen - 1;
	if ($uc > $max){
		$score -= 25; //Very weak - unacceptable
		return max(0, round($score))."|The password cannot contains all upper case characters.";
	}
	if ($lc > $max){
		$score -= 25; //Very weak - unacceptable
		return max(0, round($score))."|The password cannot contains all lower case characters.";
	}
	if ($num > $max){
		$score -= 25; //Very weak - unacceptable
		return max(0, round($score))."|The password cannot contains all numbers.";
 	}
	if ($other > $max){
		$score -= 25; //Very weak - unacceptable
		return max(0, round($score))."|The password cannot contains all special characters.";
	}

	// The password must not contain a dictionary word
/* Code that uses the built-in (Linux) words file as a dictionary - too slow, moved to DB
	if (is_readable('/usr/share/dict/words')){
		if ($fh = fopen('/usr/share/dict/words','r')){
			$found = false;
			while (!($found || feof($fh))){
				$word = preg_quote(trim(strtolower(fgets($fh, 1024))), '/');
				if (preg_match("/$word/", $lower_pw) || preg_match("/$word/", $leet_pw)){
					if (strlen($word) > 3){
						$found = true;
					}
				}
			}
			fclose($fh);
			if ($found){
				$score = 25;  //Weak
				return round($score)."|The password contains a dictionary word";
			}
		}
	}
*/
	//Connect to DB
	require_once("../../../secure/dbconnect.php");
/*
	// Build a string of every possible substring of the password that is 4 or more characters long, delimited with ","
	$sSearch = "";
	for ($char = 0; $char < strlen($lower_pw); $char++){
		for ($cnt = 4; $cnt <= strlen($lower_pw) - $char; $cnt++){
			$sSearch .= "'".addslashes(substr($lower_pw, $char, $cnt))."',";
		}
	}
	for ($char = 0; $char < strlen($leet_pw); $char++){
		for ($cnt = 4; $cnt <= strlen($leet_pw) - $char; $cnt++){
			$sSearch .= "'".addslashes(substr($leet_pw, $char, $cnt))."',";
		}
	}
	$sSearch = substr($sSearch, 0, -1);
	//echo $sSearch;

	//Look up all those substrings for a word match
	// Cannot use PDO value substitution as it turns $sSearch into an array, then fails
	$query = "
		SELECT *
		FROM words
		WHERE word IN (".$sSearch.")
		AND language = 'en'
		AND allow = false
	";
	//echo $query;
	try {
		$rsPassword = $mLink->prepare($query);
		$rsPassword->execute();
	}
*/
	// Build an array of every possible substring of the password that is 4 or more characters long (proper PDO method)
	$aSearch = array();
	for ($char = 0; $char < strlen($lower_pw); $char++){
		for ($cnt = 4; $cnt <= strlen($lower_pw) - $char; $cnt++){
			array_push($aSearch, addslashes(substr($lower_pw, $char, $cnt)));
		}
	}
	for ($char = 0; $char < strlen($leet_pw); $char++){
		for ($cnt = 4; $cnt <= strlen($leet_pw) - $char; $cnt++){
			array_push($aSearch, addslashes(substr($lower_pw, $char, $cnt)));
		}
	}

	// Create a string of PDO placeholders (?s), comma delimited, for use in IN () clause
	$placeholders = implode(',',str_split(str_repeat('?',count($aSearch))));
	//Look up all those substrings for a word match
	$query = "
		SELECT *
		FROM ".$_SESSION['words_table']."
		WHERE word IN ($placeholders)
		AND language = 'en'
		AND allow = false
	";
	try {
		$rsPassword = $mLink->prepare($query);
		// Assign the array of calculated values as the $aValues substitution array that fills the placholders
		$aValues = $aSearch;
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery); // This really just shows the question marks, unfortunately...needs looping code to build properly (maybe do this later)
		$rsPassword->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}

	// Found a substring that is a dictionary word (4 characters or longer)
	if ($rsPassword->rowCount() > 0){
		$score -= 20;  // Weaken it
		return max(0, round($score))."|The password contains a dictionary word.|#FCB322";
		
	}

	return max(0, round($score));
}

// Function to display strength to the user as it's being entered
// Get the possible values first
//require("../../../secure/dbconnect.php");
//$query = "
//	SELECT *
//	FROM ".$_SESSION['password_scores_table']."
//	WHERE 1
//	ORDER BY score DESC
//";
//try {
//	$rsPWScores = $mLink->prepare($query);
//	$rsPWScores->execute();
//}
//catch(PDOException $error){
//	// Log any error
//	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
//}

function getPasswordStrength($un, $pw){
	$pwScore		= scorePassword($un, $pw);
	$aScore			= explode("|", $pwScore);
	$score			= $aScore[0];
	$message 		= (sizeof($aScore) > 1 ? $aScore[1] : "");
	$messageColor	= $aScore[2];
	
	if($messageColor == ''){
		$messageColor = '#b94a48';	
	}
	
	switch(true){
//	while ($row = $rsPWScores->fetch(PDO::FETCH_ASSOC)){
//		case ($score $$row['operator'] $$row['score']):
//			$strength="Score: ".$score." - ".$row['label'];
//			$color=$row['color'];
//			break;
//	}

//		case ($score == "X"):
//			$strength="";
//			$color="";
//			break;
		case ($score > 80):
			$strength	= "Score: ".$score." - Very Secure";
			$strength2	= 'Very Secure';
			$color		= "#006400";
			$bar 		= '
				<div class="progress progress-striped" style="margin:0px; padding:0px;">
					<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
					   <span class="sr-only">100%</span>
					</div>
				 </div>
			';
			break;
			//green 100%
		case ($score > 70):
			$strength="Score: ".$score." - Secure";
			$strength2	= 'Secure';
			$color="#008000";
			$bar = '
				<div class="progress progress-striped" style="margin:0px; padding:0px;">
					<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100" style="width: 90%">
					   <span class="sr-only">90%</span>
					</div>
				 </div>
			';
			break;
			//green 90%
		case ($score > 60):
			$strength	= "Score: ".$score." - Very Strong";
			$strength2	= 'Very Strong';
			$color		= "#228B22";
			$bar 		= '
				<div class="progress progress-striped" style="margin:0px; padding:0px;">
					<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width: 80%">
					   <span class="sr-only">80%</span>
					</div>
				 </div>
			';
			break;
			//green 80%
		case ($score > 50):
			$strength	= "Score: ".$score." - Strong";
			$strength2	= 'Strong';
			$color		= "#3CB371";
			$bar		= '
				<div class="progress progress-striped" style="margin:0px; padding:0px;">
					<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" style="width: 70%">
					   <span class="sr-only">70%</span>
					</div>
				 </div>
			';
			break;
			//green 70%
		case ($score > 45):
			$strength	= "Score: ".$score." - Above Average";
			$strength2	= 'Above Average';
			$color		= "#FCB322";
			$bar 		= '
				<div class="progress progress-striped" style="margin:0px; padding:0px;">
					<div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%">
					   <span class="sr-only">60%</span>
					</div>
				 </div>
			';
			break;
			//yellow 60%
		case ($score > 40):
			$strength	= "Score: ".$score." - Average";
			$strength2	= 'Average';
			$color		= "#FCB322";
			$bar 		= '
				<div class="progress progress-striped" style="margin:0px; padding:0px;">
					<div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100" style="width: 50%">
					   <span class="sr-only">50%</span>
					</div>
				 </div>
			';
			break;
			//yellow 50%
		case ($score > 35):
			$strength	= "Score: ".$score." - Weak";
			$strength2	= 'Weak';
			$color		= "#FF7F50";
			$bar		= '
				<div class="progress progress-striped" style="margin:0px; padding:0px;">
					<div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 40%">
					   <span class="sr-only">40%</span>
					</div>
				 </div>
			';
			break;
			//red 40%
		case ($score >= 25):
			$strength	= "Score: ".$score." - Very Weak";
			$strength2	= 'Very Weak';
			$color		= "#DC143C";
			$bar 		= '
				<div class="progress progress-striped" style="margin:0px; padding:0px;">
					<div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100" style="width: 30%">
					   <span class="sr-only">30%</span>
					</div>
				 </div>
			';
			break;
			//red 30%
		case ($score >= 0):
			$strength	= "Score: ".$score." - Too Weak";
			$strength2	= 'Too Weak';
			$color		= "#FF0000";
			$bar 		= '
				<div class="progress progress-striped" style="margin:0px; padding:0px;">
					<div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 20%">
					   <span class="sr-only">20%</span>
					</div>
				 </div>
			';
			break;
			//red 20%
		case ($score == -1):
			$strength	= '<span style="color:#FFFFFF;">Invalid</span>';
			$strength2	= 'Invalid';
			$color		= "#57B5E3";
			$bar 		= '
				<div class="progress progress-striped" style="margin:0px; padding:0px;">
					<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100" style="width: 10%">
					   <span class="sr-only">10%</span>
					</div>
				 </div>
			';
			break;
			//blue 10%
		case ($score == -2):
			$strength	= "No Username";
			$strength2	= 'No Username';
			$color		= "#57B5E3";
			$bar 		= '
				<div class="progress progress-striped" style="margin:0px; padding:0px;">
					<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100" style="width: 10%">
					   <span class="sr-only">10%</span>
					</div>
				 </div>
			';
			break;
		default:
			$strength="";
			$color="";
			break;
	}
	if ($color == ""){
		return '<span style="color:#b94a48; font-size:13px; padding:5px 0 0 0;">'.$message.'</span><input type="hidden" id="password_score" name="password_score" value="'.$score.'">';
	}else{
		$option1 = '
			<table width="95%">
				<tr>
					<td height="5"></td>
				</tr>
				<tr>
					<td>'.$bar.'</td>
				</tr>
				<tr>
					<td style="background-color:'.$color.'; border-radius:4px; padding-bottom:2px; text-align:center;"><strong>'.$strength.'</strong></td>
				</tr>
				<tr>
					<td style="color:#b94a48; font-size:13px; padding:5px 0 0 0;">'.$message.'</td>
				</tr>
			</table>
			<div class="form-group"><input type="hidden" id="password_score" name="password_score" value="'.$score.'"></div>
		';
		$option2 = '
			<table width="95%">
				<tr>
					<td height="5"></td>
				</tr>
				<tr>
					<td>
						
						<p style="text-align:left;float:left;"><a href="javascript:void(0);" id="view-password" style="color:#57B5E3;text-decoration:none;"><i class="icon-eye-open"></i> View Password</a></p>
						<p style="text-align:right;float:right;"> Strength: <span style="color:'.$color.' !important;">'.$strength2.'</span></p>
						<div class="clearfix"></div>
						<p style="display:none;background:#E8F6FC;border:1px solid #57B5E3;padding:5px;border-radius:3px;" id="show-hide-pw"></p>
						'.$bar.'
					</td>
				</tr>
				<tr>
					<td style="color:'.$messageColor.'; font-size:13px; padding:5px 0 0 0;">'.$message.'</td>
				</tr>
			</table>
			<div class="form-group"><input type="hidden" id="password_score" name="password_score" value="'.$score.'"></div>
		';
		$option3 = '
			<table width="95%">
				<tr>
					<td height="5"></td>
				</tr>
				<tr>
					<td>
						
						<p style="text-align:left;float:left;"><a href="javascript:void(0);" id="view-password" style="color:#57B5E3;text-decoration:none;"><i class="icon-eye-open"></i> View Password</a></p>
						<p style="text-align:right;float:right;"> Strength: <span style="color:'.$color.' !important;">'.$strength2.'</span></p>
						<div class="clearfix"></div>
						<p style="display:none;background:#E8F6FC;border:1px solid #57B5E3;padding:5px;border-radius:3px;" id="show-hide-pw"></p>
						'.$bar.'
						<p style="color:'.$messageColor.'; font-size:13px; padding:5px 0 0 0;margin:0px;">'.$message.'</p>
					</td>
				</tr>
				
			</table>
			<input type="hidden" id="password_score" name="password_score" value="'.$score.'">
		';
		return $option3;
	}
}
?>
