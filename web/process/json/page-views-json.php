<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

require_once("../../includes/system-functions.php");

$pageID 	= $_REQUEST['page']; 
$memberID	= $_REQUEST['member'];
$type		= $_REQUEST['type'];
$viewType	= $_REQUEST['viewType'];
$today		= time();

//echo $pageID.' | '.$memberID.' | '.$type.' | '.$viewType;

switch($type){
	
	case 'views':
		
		
		switch($viewType){
			
			case 'Unique Views':
				$query = "
					SELECT *
					FROM ".$_SESSION['page_tracking_table']."
					WHERE page_id=:page_id AND member_id=:member_id
					ORDER BY timestamp ASC
				";
				try{
					$rsGetViews = $mtrLink->prepare($query);
					$aValues = array(
						':page_id'		=> $pageID,
						':member_id'	=> $memberID		
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsGetViews->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				
				$aViews 		= array();
				$cntTotalViews 	= 0;
				
				while($views = $rsGetViews->fetch(PDO::FETCH_ASSOC)){
					
					$cntTotalViews++;
					
					$now = $views['timestamp'];
						
					$day = mktime(0,0,0,date('m',$now),date('d',$now),date('Y',$now));
					
					if($cntTotalViews == 1){
						$startDate = $day;
						$_SESSION['viewsStartDate'] = $day;	
					}
					
					$aViews[$day][$views['ip_address']] =  $views['timestamp'];
							
				}
				
				
				$loop 		= true;
				$loopCnt 	= 0;
				
				while($loop){
						
					if($loopCnt == 0){
						
						$date = $startDate;
						
					}
						
					$dataArray[$date] = 0;
					
					if($date > $today){
						$loop = false;
					}else{
						$date = strtotime('+1 day', $date);
						$loopCnt++;
					}
				}
				
				foreach($aViews as $timestamp=>$numIPs){
					
					$numIPs = count($numIPs);
					
					$dataArray[$timestamp] = $numIPs;
						
				}
				
				
				foreach($dataArray as $date=>$numViews){
					
					$date = $date * 1000;
					
					$array[] = array($date, $numViews);
						
				}
				
				/*echo '<pre>';
				
				print_r($dataArray);
				
				echo '</pre>';
				
				$array[] = array(x=>$date, y=>$price, compliant=>$compliant, fund=>$fundLabel);
				*/
				
				//echo $cnt;
				$json = json_encode($array);
				
				/*$json = str_replace('{','[',$json);
				$json = str_replace('}',']', $json);*/
				
				echo $json;
				
			break;
			
			case 'Trackers':
			
				$query = "
					SELECT *
					FROM ".$_SESSION['fund_tracking_table']."
					WHERE member_id=:member_id
					ORDER BY timestamp ASC
				";
				try{
					$rsGetTracking = $mLink->prepare($query);
					$aValues = array(
						':member_id'	=> $memberID			
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsGetTracking->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				echo $error;
				
				$aTracks 		= array();
				$cntTotalTracks	= 0;
				
				while($tracking = $rsGetTracking->fetch(PDO::FETCH_ASSOC)){
					
					$validTracker	= true;
					$updates		= $tracking['manager_updates'];
					$articles		= $tracking['manager_articles'];
					$spam			= $tracking['spam'];
					$unsub			= $tracking['unsubscribe'];
					$subscribed		= $tracking['timestamp'];
					$email			= $tracking['email'];
					
					if($spam != '0' || $unsub != '0'){
						$validTracker = false;	
					}
					
					//$aTest[$email] = date('Y-m-d h:i',$subscribed);
					
					if($spam == '1'){
						$aSpam[$email] = 1;	
					}
					
										
					if($validTracker == true){
						
						$cntTotalTracks++;
					
						$now = $subscribed;
							
						$day = mktime(0,0,0,date('m',$now),date('d',$now),date('Y',$now));
						
						if($cntTotalTracks == 1){
							$startDate = $day;	
						}
						
						$aTracks[$day][] =  $email;
							
					}
					
				}
				
				$loop 		= true;
				$loopCnt 	= 0;
				
				while($loop){
						
					if($loopCnt == 0){
						
						$date = $_SESSION['viewsStartDate'];
						
					}
						
					$dataArray[$date] = 0;
					
					if($date > $today){
						$loop = false;
					}else{
						$date = strtotime('+1 day', $date);
						$loopCnt++;
					}
				}
				
				foreach($aTracks as $timestamp=>$aEmails){
					
					$numTracks = count($aEmails);
					
					$dataArray[$timestamp] = $numTracks;
						
				}
				
				
				foreach($dataArray as $date=>$numTracks){
					
					$date = $date * 1000;
					
					$array[] = array($date, $numTracks);
						
				}
				
				$json = json_encode($array);
				
				echo $json;
			break;
		}
	break;
	
	case 'flags':
		
		$query = "
			SELECT *
			FROM members_profile_articles
			WHERE member_id=:member_id AND article_type='forbes' AND deleted='0' AND published='1'
			ORDER BY timestamp ASC
		";
		try{
			$rsGetForbes = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $memberID			
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetForbes->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		while($forbes = $rsGetForbes->fetch(PDO::FETCH_ASSOC)){
			
			$now = $forbes['publish_date'];
			
			$date = mktime(0,0,0,date('m',$now),date('d',$now),date('Y',$now));
			
			$date = $date * 1000;
			
			$array[] = array(x=>$date, title=>'FA', text=>'Forbes Article Published | '.$forbes['article_title']);
			
		}
		
		
		
		$json = json_encode($array);
		
		/*$json = str_replace('{','[',$json);
		$json = str_replace('}',']', $json);*/
		
		echo $json;
		/*echo "[{
                    x : 1461297600000,
                    title : 'F',
                    text : 'Forbes Article Published'
                }]";
*/		
	break;
}



?>