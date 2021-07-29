<?php
/*
The purpose of this script is to populate the authentication table with encrypted values for each imported member from the old system
This must be run on one of the new servers as the version of the MCRYPT library on the Fetch server is too old to support our encryption settings,
thus it must be run separately from, and after, the import process.
*/

// OK, let's get going...

// Tell me when things go sideways
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Run forever
set_time_limit(30);

// Load encryption functions
require("../secure/crypto.php");

// Connect to MySQL
require("../secure/dbconnect.php");

$query = "
	SELECT username
	FROM system_imported_usernames
	WHERE imported = 0
";

//die($query);
//echo $query."<br><br>";
$rsUsernames = $mLink->prepare($query);
$rsUsernames->execute();

while ($uname = $rsUsernames->fetch(PDO::FETCH_ASSOC)){
	$username = trim($uname['username']);
	$encrypted_username = encrypt($username);

	// Get the password for the member
	$query = "
		SELECT password
		FROM clear_passwords
		WHERE username = :username
	";
	$rsPassword = $mLink->prepare($query);
	$aValues = array(
		':username'	=> $username
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
	$rsPassword->execute($aValues);
	$pword = $rsPassword->fetch(PDO::FETCH_ASSOC);
	$password = trim($pword['password']);
	$encrypted_password = encrypt($password);

	// Get the email address for the member
	$query = "
		SELECT member_id, email
		FROM members
		WHERE username = :username
	";
	$rsEmail = $mLink->prepare($query);
	$aValues = array(
		':username'	=> $username
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
	$rsEmail->execute($aValues);
	$mail = $rsEmail->fetch(PDO::FETCH_ASSOC);
	$member_id = $mail['member_id'];
	$email = trim($mail['email']);
	if ($email == ""){
		$email = NULL;
		$encrypted_email = NULL;
	}else{
		$encrypted_email = encrypt($email);
	}

echo $member_id."|".$username."|".$encrypted_username."|".$password."|".$encrypted_password."|".$email."|".$encrypted_email."\n";

	// Insert the encrypted values into the auth table
	$query =
		"INSERT INTO system_authentication (
				member_id,
				timestamp,
				username,
				password,
				email,
				email_validated_timestamp,
				imported
			) VALUES (
				:member_id,
				UNIX_TIMESTAMP(),
				:username,
				:password,
				:email,
				UNIX_TIMESTAMP(),
				1
			)";
		$rsInsert = $mLink->prepare($query);
		$aValues = array(
			':member_id'	=> $member_id,
			':username'		=> $encrypted_username,
			':password'		=> $encrypted_password,
			':email'		=> $encrypted_email
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		//die($preparedQuery);
		$rsInsert->execute($aValues);

//echo $query."\n\n";


	// Flag the username as imported.
	// Insert the encrypted values into the auth table
	$query = "
		UPDATE	system_imported_usernames
		SET		imported = 1
		WHERE	member_id = :member_id
	";
	$rsUpdate = $mLink->prepare($query);
	$aValues = array(
	 	':member_id'	=> $member_id
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	//die($preparedQuery);
	$rsUpdate->execute($aValues);
}

?>