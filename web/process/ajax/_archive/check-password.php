<?php
// This PHP script is run via an AJAX call to validate a desired password during account creation
// Since it is not included, nor run within the bounds of a user's session, session variables are not accessible and are therefore passed within the querystring
// Results of any failed check are returned as HTML to be displayed on the form, after which the script is discontinued - no need to proceed

// Make sure the password isn't blank
if ($_REQUEST['username'] == ""){
	echo '
		<span style="color:#b94a48; font-size:13px; padding:5px 0 0 0;">Password cannot be blank.</span>
		<input type="hidden" id="password-error" name="password-error" value="blank">
	';
	die();
}

// Make sure it doesn't contain any spaces
if (stripos($_REQUEST['password'], " ") !== false){
	echo '
		<span style="color:#b94a48; font-size:13px; padding:5px 0 0 0;">Password cannot contain spaces - Please try another.</span>
		<input type="hidden" id="password-error" name="password-error" value="spaces">
	';
	die();
}

// Make sure it doesn't contain fewer than the minimum defined number of characters
if (strlen($_REQUEST['password']) < $_REQUEST['password-minimum']){
	echo '
		<span style="color:#b94a48; font-size:13px; padding:5px 0 0 0;">Password must be at least '.$_REQUEST['password-minimum'].' characters - Please try another.</span>
		<input type="hidden" id="password-error" name="password-error" value="short">
	';
	die();
}

// Make sure it contains only letters and numbers (no punctuation, etc.)
//if (!ctype_alnum($_REQUEST['username'])){
//	echo '
//		<span style="color:#b94a48; font-size:13px; padding:5px 0 0 0;">Username must contain only letters and numbers - Please try another.</span>
//		<input type="hidden" id="username-error" name="username-error" value="punctuation">
//	';
//	die();
//}

// Make sure both passed values match
if ($_REQUEST['password'] !== $_REQUEST['password2']){
	echo '
		<span style="color:#b94a48; font-size:13px; padding:5px 0 0 0;">Passwords do not match - Please try again.</span>
		<input type="hidden" id="password-error" name="password-error" value="mismatch">
	';
	die();
}





function scorePassword($password){
	$score=0;
	if(!$password)
		return 0;

	// award every unique letter until 5 repetitions
	$letters=str_split($password);
	$scores=array();
	foreach($letters as $letter){
		$scores[$letter]=(isset($scores[$letter]))?$scores[$letter]+1:1;
		$score+=5/$scores[$letter];
	}

	// bonus points for mixing it up
	$variations = array(
		preg_match('/\d/', $this->password),
		preg_match('/[a-z]/', $this->password),
		preg_match('/[A-Z]/', $this->password),
		preg_match('/\W/', $this->password),
	);

	$variationCount=0;
	foreach($variations as $check){
		$variationCount+=($check)?1:0;
	}
	$score+=($variationCount-1)*10;
	return round($score);
}

		function checkPassStrength(pw){
			var score = scorePassword(pw);
			if (score > 80){
				return "Very Secure";
			}
			if (score > 70){
				return "Secure";
			}
			if (score > 60){
				return "Very Strong";
			}
			if (score > 50){
				return "Strong";
			}
			if (score > 45){
				return "Above Average";
			}
			if (score > 40){
				return "Average";
			}
			if (score > 35){
				return "Weak";
			}
			if (score >= 25){
				return "Very Weak";
			}
			if (score > 25){
				return "Too Weak";
			}
			return "";
		}
/*
		$(document).ready(function(){
			$("#register-password").on("keypress keyup keydown", function(){
//			$("#password").keypress(function(){
				var pw = $(this).val();
				$("#strength_human").text(checkPassStrength(pw));
				$("#strength_score").text(scorePassword(pw));
//				$("#strength_human").text("boo");
			});
		});
*/






/////// Add any other format restrictions here.....

//Connect to DB
require_once("../../secure/dbconnect.php");

//Load encryption functions
require_once("../../secure/crypto.php");

// Encrypt the passed username for DB lookup
$encrypted_username = encrypt($_REQUEST['username']);

//Look up the encrypted username
$query = "
	SELECT *
	FROM authentication
	WHERE username = '$encrypted_username'
";
//echo $query;
try {
	$rsUsername = $mLink->prepare($query);
	$rsUsername->execute();
}
catch(PDOException $error){
	// Log any error
		file_put_contents($_REQUEST['pdo-log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

// Username already taken
if ($rsUsername->rowCount() > 0){
	echo '
		<span style="color:#b94a48; font-size:13px; padding:5px 0 0 0;">Username unavailable - Please try another.</span>
		<input type="hidden" id="username-error" name="username-error" value="unavailable">
	';
	die();
}

if ($_REQUEST['obscenity-checking'] == "on"){
	// Get list of restricted phrases (obscenities)
	// *** Expand language support later ***
	$query = "
		SELECT *
		FROM obscenities
		WHERE language = 'en'
	";
	//echo $query;
	try {
		$rsObscenities = $mLink->prepare($query);
		$rsObscenities->execute();
	}
	catch(PDOException $error){
		// Log any error
			file_put_contents($_REQUEST['pdo-log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	while ($obscenity = $rsObscenities->fetch(PDO::FETCH_ASSOC)){
		$sBad = $obscenity['obscenity'];
		if (stripos($_REQUEST['username'], $sBad) !== false){
			echo '
				<span style="color:#b94a48; font-size:13px; padding:5px 0 0 0;">Username contains an obscene phrase - Please try another.</span>
				<input type="hidden" id="username-error" name="username-error" value="obscene">
			';
			die();
		}
		if (stripos($_REQUEST['username'], str_replace(' ', '', $sBad)) !== false){
			echo '
				<span style="color:#b94a48; font-size:13px; padding:5px 0 0 0;">Username contains an obscene phrase - Please try another.</span>
				<input type="hidden" id="username-error" name="username-error" value="obscene">
			';
			die();
		}
	}
}

// All's well
echo '<span style="color:#468847; font-size:13px; padding:5px 0 0 0;">Username available!</span>';
?>
