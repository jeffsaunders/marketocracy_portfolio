<?php

$classEndDate = '1430492400';

$trialStart = time();
		
if($makeStudent == true){

	if($classEndDate != ''){
		$trialEnd = strtotime("+7 days", $classEndDate);
		
		
	}else{
		$trialEnd = strtotime("+30 days");	
		
			
	}
	
}else{
	$trialEnd = strtotime("+30 days");
	
	
}	

$trialEnd1 = strtotime("+7 days", $classEndDate);
$trialEnd2 = strtotime("+30 days");
echo date('m/d/Y',$classEndDate).'|'.date('m/d/Y',$trialStart).'|'.$trialEnd.'|End of Class + 7: '.date('m/d/Y',$trialEnd1).' | today + 30: '.date('m/d/Y',$trialEnd2);
?>