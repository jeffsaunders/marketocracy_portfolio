<?php

//Load encryption functions
require_once("../secure/crypto.php");

// get passed value
$id = $_REQUEST["cargo"];

// Now put back any stripped out plus signs (RFC 2396)
$id = str_replace(" ", "+", $id);

// ...and convert from URL safe format
//$id = json_decode($id);

// Decrypt passed string
$decryptedID = decrypt($id);

// Explode the decrypted ID string
$aID = explode("|", $decryptedID);
//print_r($aID);die();

// Encrypt just the extracted email address
$encrypted_email = encrypt($aID[0]);

// Look up encrypted email address
$query = "
	SELECT member_id, username, reset_request_timestamp
	FROM ".$_SESSION['auth_table']."
	WHERE email = :encrypted_email
	ORDER BY timestamp DESC
	LIMIT 1
";
//echo $query;
try {
	$rsAuth = $mLink->prepare($query);
	$aValues = array(
		':encrypted_email'	=> $encrypted_email
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
	$rsAuth->execute($aValues);
}
catch(PDOException $error){
	// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

// Authentication failed - no match for email address
if ($rsAuth->rowCount() < 1){
	echo "<script>window.location = '/reset-failed';</script>";
}

// So far, so good.  Let's test the data.
$auth = $rsAuth->fetch(PDO::FETCH_ASSOC);

// Compare the current unixtime to the stored value
if ((time() - $auth['reset_request_timestamp']) > 4000){  // Let's give 'em a little more than an hour
	// It's been more than an hour
	echo "<script>window.location = '/reset-failed';</script>";
}

// Now compare the passed member_id with the query result, for good measure
if (trim($aID[1]) !== trim($auth['member_id'])){
//die();
	// Don't match!  Hmmmm, fishy......
	echo "<script>window.location = '/reset-failed';</script>";
}

// All's well, let them change their password.
$username = decrypt($auth['username']);
?>
	<!-- BEGIN INCLUDE PASSWORD-RESET -->

	<!-- BEGIN LOGO -->
	<div class="logo">
		<img src="images/logo.png" alt=""/>
	</div>
	<!-- END LOGO -->

	<!-- BEGIN LOGIN -->
	<div class="content">
		<!-- BEGIN PASSWORD RESET FORM -->
		<form class="reset-form" id="reset-form" action="process/process-auth.php" method="post">
			<h3 class="form-title">Reset Your Password</h3>
			<div class="alert alert-error hide">
				<button class="close" data-dismiss="alert"></button>
				<span>Enter any password.</span>
			</div>
			<p>Enter a password below, twice, to set it.</p>
			<div class="form-group" id="password-div">
				<div class="input-icon">
					<i class="icon-lock"></i>
					<input class="form-control placeholder-no-fix" type="password" autocomplete="off" placeholder="Password" id="password" name="password" onInput="checkPassword('username=<?php echo $username;?>&password='+encodeURIComponent(this.value)+'&pdo-log=<?php echo $_SESSION['pdo_log'];?>&password-minimum=<?php echo $_SESSION['password_minimum_length'];?>');" onBlur="checkPassword('username=<?php echo $username;?>&password='+encodeURIComponent(this.value)+'&pdo-log=<?php echo $_SESSION['pdo_log'];?>&password-minimum=<?php echo $_SESSION['password_minimum_length'];?>');" />
				</div>
				<div id="password-strength-result"></div>
			</div>
			<div class="form-group" id="password2-div">
				<div class="input-icon">
					<i class="icon-lock"></i>
					<input class="form-control placeholder-no-fix" type="password" autocomplete="off" placeholder="Re-Enter Password" id="password2" name="password2" onBlur="matchPasswords();" />
				</div>
				<div id="password-check-result"></div>
			</div>
			<div id="weak-pw-div" class="form-group" style="visibility:hidden; display:none;">
				<label>
					<input type="checkbox" name="weak_pw" id="weak_pw"/> I acknowledge that I am willingly choosing a password that is considered weak by current standards.</a>
				</label>
				<div id="weak_pw_error"></div>
			</div>
			<div class="form-actions">
				<input type="hidden" name="member_id" value="<?php echo $auth['member_id'];?>"/>
				<input type="hidden" name="task" value="change-password"/>
				<button type="submit" class="btn btn-info pull-right">
				Submit
				</button>
			</div>
		</form>
	</div>
	<!-- END PASSWORD RESET  -->

	<!-- BEGIN COPYRIGHT -->
	<div class="copyright">
		<?php echo "&copy; 2000-".date("Y");?> Marketocracy, Inc. All rights reserved.
	</div>
	<!-- END COPYRIGHT -->

	<script>
		// Ajax script to check password validity
		function checkPassword(params){
//			document.getElementById("password-strength-result").innerHTML = '<span style="color:#3a87ad; font-size:13px;">Checking Password . . .<span>';
			if (window.XMLHttpRequest){ // Code for IE7+, Firefox, Opera, Webkit (Chrome, Safari, Rockmelt)
				xmlhttp=new XMLHttpRequest();
			}else{ // Code for IE6, IE5
				xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			xmlhttp.open("GET","/process/ajax/score-password-dev.php?"+params);
			xmlhttp.send();
			xmlhttp.onreadystatechange = function(){
				if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
					document.getElementById("password-strength-result").innerHTML = xmlhttp.responseText;
				}
			}
		}

		// Function to verify the two passwords match
		function matchPasswords(){
				// If they DON'T match
			if (document.getElementById('reset-form').elements['password'].value == ""){
				document.getElementById("password-div").className += " has-error";
				document.forms["reset-form"].elements["password"].focus();
			}else if (document.forms["reset-form"].elements["password_score"] && (document.forms["reset-form"].elements["password_score"].value < 25 || document.forms["reset-form"].elements["password_score"].value == "X")){
				document.forms["reset-form"].elements["password"].focus();
				document.getElementById("password-div").className += " has-error";
			}else if (document.getElementById('reset-form').elements['password'].value !== document.getElementById('reset-form').elements['password2'].value){
				// Turn them red
				document.getElementById("password-div").className += " has-error";
				document.getElementById("password2-div").className += " has-error";
				// Tell 'em what's wrong
				document.getElementById("password-check-result").innerHTML = '<span style="color:#b94a48; font-size:13px; padding:5px 0 0 0;">Passwords do not match.</span>';
				//Put the cursor back in the password2 field
				document.getElementById("reset-form").elements["password2"].focus();
			}else{
				// Turn them back to gray if they are red (does nothing if they aren't)
				document.getElementById("password-div").classList.remove("has-error");
				document.getElementById("password2-div").classList.remove("has-error");
				// clear any left-over error message
				document.getElementById("password-check-result").innerHTML = '';
			}
			if (document.forms["reset-form"].elements["password_score"].value > 24 && document.forms["reset-form"].elements["password_score"].value < 41){
				document.getElementById("weak-pw-div").style.visibility = "visible";
				document.getElementById("weak-pw-div").style.display = "block";
				
			}else{
				document.getElementById("weak-pw-div").style.visibility = "hidden";
				document.getElementById("weak-pw-div").style.display = "none";
				$('#weak_pw').attr('checked', true);
			}
		}

		// Place the cursor
	 	document.getElementById("reset-form").elements["password"].focus();
	</script>

	<!-- END INCLUDE PASSWORD-RESET -->
