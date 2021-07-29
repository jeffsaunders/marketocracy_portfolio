<?php
/*
Marketocracy Inc. | Beta Development
Admin General HTML

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/admin-general-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/admin-general.js.php
	HTML		- includes/pages/admin-general.php
*/

//Start Session
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

// Load System Wide Functions
require_once("../../includes/system-functions.php");

//Get Process from URL
$process = $_REQUEST['process'];


//+-------------------------------------------------------------------------------+
//|						MEMBER LOOKUP
//+-------------------------------------------------------------------------------+
if($process == 'member-lookup'){
	
	$memberID 	= $_REQUEST['member_id'];
	$username	= $_REQUEST['username'];
	$email		= $_REQUEST['email'];
	
	if($memberID != ''){
		$action = 'memberID';	
	}elseif($memberID == '' && $username != ''){
		$action = 'username';	
	}elseif($memberID == '' && $username == '' && $email != ''){
		$action = 'email';	
	}else{
		echo 'No params passed';
		exit();	
	}
	
	switch($action){
		case 'memberID':
			$query = "
				SELECT *
				FROM ".$_SESSION['members_table']." 
				WHERE member_id=:member_id
			";
			try{
				$rsMember = $mLink->prepare($query);
				$aValues = array(
					':member_id'	=> $memberID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsMember->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
		break;
		
		case 'username':
			$query = "
				SELECT *
				FROM ".$_SESSION['members_table']." 
				WHERE username LIKE :username
			";
			try{
				$rsMember = $mLink->prepare($query);
				$aValues = array(
					':username'	=> $username
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsMember->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
		break;
		
		case 'email':
			$query = "
				SELECT *
				FROM ".$_SESSION['members_table']." 
				WHERE email LIKE :email
			";
			try{
				$rsMember = $mLink->prepare($query);
				$aValues = array(
					':email'	=> $email
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsMember->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
		break;	
	}
	
	$aMembers = array();
	
	while($member = $rsMember->fetch(PDO::FETCH_ASSOC)){
		
		$query = "
			SELECT password
			FROM ".$_SESSION['auth_table']."
			WHERE member_id=:member_id
			ORDER BY timestamp DESC
			LIMIT 1
		";
		try{
			$rsPW = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $member['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsPW->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$pw = $rsPW->fetch(PDO::FETCH_ASSOC);
		
		$encryptPW = $pw['password'];
		//echo $encryptPW;
		include('../../../secure/crypto.php');
		
		$decryptPW = decrypt($encryptPW);
		
		$aMembers[$member['member_id']] = array(
			'username'		=> $member['username'],
			'email'			=> $member['email'],
			'name'			=> $member['name_first'].' '.$member['name_last'],
			'pw'			=> $decryptPW
		);
		
	}
	
	?>
    <table class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Member ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>PW</th>
            </tr>
        </thead>
        <tbody>
			<?php
            
			$cnt = 0;
            foreach($aMembers as $memberID => $aMember){
                
				$cnt++;
				
                ?>
                <tr>
                	<td><?php echo $cnt;?></td>
                    <td><?php echo $memberID;?></td>
                    <td><?php echo $aMember['username'];?></td>
                    <td><?php echo $aMember['email'];?></td>
                    <td><?php echo $aMember['pw'];?></td>
                </tr>
                <?php
                    
            }
			?>
        </tbody>
	</table>
	<?php
		
}

?>