<?php
// Debug related functions - load right up front

// Define some global system settings
date_default_timezone_set('America/New_York');
//error_reporting(E_ALL);  // Show ALL, including warnings and notices
error_reporting(E_ERROR);  // Just show hard errors
ini_set('display_errors', '1');  // Show 'em

//-----
// Dump ALL existing variables and their values
function dump_vars($mixed=null){
	ob_start();
//	var_dump($mixed);
	print_r($mixed);
	$sVars = ob_get_contents();
	ob_end_clean();
	return $sVars;
}

//-----
// Dump array elements variables and their values
function dump_array($mixed=null){
	ob_start();
	$sElem = "<pre>";
	print_r($mixed);
	$sElem .= htmlentities(ob_get_contents());
	$sElem .= "</pre>";
	ob_end_clean();
	return $sElem;
}

//-----
// Dump PDO result set.  *Note - Result Set can only be iterated once as the pointer moves.
// Set $html=1 if web page

function dump_rs($rs=null,$html=0){
        while($row = $rs->fetch(PDO::FETCH_ASSOC)){
                foreach($row as $cName => $cValue){
                        echo "$cName: $cValue".($html ? "&emsp;" : "\t");
                }
                echo ($html ? "<br><br>" : "\r\n");
        }
}

?>
