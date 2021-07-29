<?php
	$query = "
		SELECT dash_col1, dash_col2 
		FROM ".$_SESSION['members_settings_table']."
		WHERE member_id=:member_id 
	";
	//START PDO
	try{
		$rsGetColumns = $mLink->prepare($query);
		$aValues = array(
			':member_id' 	=> $_SESSION['member_id']
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetColumns->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$columns = $rsGetColumns->fetch(PDO::FETCH_ASSOC);
	//END PDO
	
	//Convert DB results into arrays
	$col1 = explode("|", $columns['dash_col1']);
	$col2 = explode("|", $columns['dash_col2']);
	
	//Set Portlet Counter to 0
	$cnt=0;
	
	//Loop through array to try and find a mactch, If found increment counter +1
	foreach($col1 as $key => $value) {
		if($value == "".$portletType."~".$fundID."~0~0"){
			$cnt = $cnt + 1;	
		}
	}
	
	foreach($col2 as $key => $value) {
		if($value == "".$portletType."~".$fundID."~0~0"){
			$cnt = $cnt + 1;
		}
	}
?>