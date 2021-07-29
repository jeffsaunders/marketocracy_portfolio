<?php
/*
+----------------------------------------------------------------------------------------+
|						STRATIFICATION CALCULATION ROUTINE
+----------------------------------------------------------------------------------------+
*/
session_start();

// Parse passed arguments string to $_REQUEST array (i.e. "first=1&second=2&third=3" -> $_REQUEST['first'] = 1, etc.)
//parse_str($argv[1], $_REQUEST);

//get dependencies
require('/var/www/html/portfolio.marketocracy.com/secure/dbconnect.php');
require('/var/www/html/portfolio.marketocracy.com/web/includes/system-functions.php');
require('/var/www/html/portfolio.marketocracy.com/web/includes/system-debug-functions.php');


//echo 'start\n';

$targetMemberID = $_REQUEST['memberID'];
$sendEmail 		= $_REQUEST['sendEmail'];

if($sendEmail == ''){
	$sendEmail = false;	
}

//tables
$emailTemplates			= 'email_templates';
$emailCampaigns			= 'email_campaigns';
$memberTracking			= 'members_fund_tracking';

$aCampaigns				= array();

if($sendEmail == true){
	echo 'true';
}else{
	echo 'false';	
}

#build array of campaigns
if($targetMemberID != ''){
	#get specific member campaigns
	$query = "
		SELECT *
		FROM ".$emailCampaigns."
		WHERE member_id=".$targetMemberID." AND camp_type='monthly_perf' AND camp_status='draft'
	";
}else{
	#get all member campaigns
	$query = "
		SELECT *
		FROM ".$emailCampaigns."
		WHERE camp_type='monthly_perf' AND camp_status='draft' 
	";
}
try{
	$rsCamps = $mLink->prepare($query);
	$aValues = array();
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsCamps->execute($aValues);
}

catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}
			
				
while($campInfo = $rsCamps->fetch(PDO::FETCH_ASSOC)){	
	
	//get template data
	$query = "
		SELECT *
		FROM ".$emailTemplates."
		WHERE temp_id=:temp_id
	";
	try{
		$rsTemp = $mLink->prepare($query);
		$aValues = array(':temp_id'=>$campInfo['temp_id']);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsTemp->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$tempInfo = $rsTemp->fetch(PDO::FETCH_ASSOC);
	
	if($campInfo['email_headline'] == '' || $campInfo['email_headline'] == NULL){
		$emailHeadline = $tempInfo['temp_headline'];
	}else{
		$emailHeadline = $campInfo['email_headline'];
	}
	if($campInfo['email_body'] == '' || $campInfo['email_body'] == NULL){
		$emailBody = $tempInfo['temp_body'];
	}else{
		$emailBody = $campInfo['email_body'];
	}
	
	$aCampaigns[$campInfo['member_id']][$campInfo['camp_id']] = array(
		'camp_name'				=> $campInfo['camp_name'],
		'temp_id'				=> $campInfo['temp_id'],
		'email_title'			=> $campInfo['email_title'],
		'email_subject'			=> $campInfo['email_subject'],
		'email_headline'		=> $emailHeadline,
		'email_body'			=> $emailBody,
		'email_headers_from'	=> $tempInfo['headers_from'],
		'email_headers_bcc'		=> $tempInfo['headers_bcc'],
		'asOfDate'				=> $campInfo['asOfDate'],
		'bcc'					=> $tempInfo['headers_bcc']
	);
	
}
//END build array of campaigns


//echo '<pre>';
/*echo '<pre>';

print_r($aCampaigns);
echo '</pre>';
*/
#START | Loop through each campaign
foreach($aCampaigns as $memberID=>$aCampaign){
	
	foreach($aCampaign as $campID=>$campInfo){
		
		#set vars
		//echo $memberID;
		$asOfDate		= $campInfo['asOfDate'];
		$unixAsOfDate	= mktime(8,0,0,substr($asOfDate,4,2),substr($asOfDate,6,2),substr($asOfDate,0,4));
		$lastMonth = strtotime('-1 month', $unixAsOfDate);
		$unixMonthStart	= mktime(8,0,0,date('m',$lastMonth),date('t',$lastMonth),date('Y',$lastMonth));
		
		//echo $unixMonthStart.'|'.date('Y/m/d', $unixMonthStart).'~';
		//echo $unixAsOfDate.'|'.date('Y/m/d', $unixAsOfDate);
		
		#get articles
		$aArticles = array();
		
		$query = "
			SELECT *
			FROM members_profile_articles
			WHERE member_id=:member_id AND published='1' AND deleted='0' AND publish_date>:publish_date
		";
		try{
			$rsArticles = $mLink->prepare($query);
			$aValues = array(
				'member_id'		=> $memberID,
				'publish_date'	=> $unixMonthStart
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsArticles->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$articleCnt = 0;				
		while($article = $rsArticles->fetch(PDO::FETCH_ASSOC)){
			
			$articleCnt++;
			
			$aArticles[$article['article_id']] = array(
				'article_type'	=> $article['article_type'],
				'article_title'	=> $article['article_title'],
				'publish_date'	=> $article['publish_date'],
				'article_link'	=> $article['article_link']
			);
			
		}
		
		//print_r($aArticles);
		
		if($articleCnt > 0){
			
			$showArticle = '<hr style="width:560px; height:1px; background-color:#EEEEEE; border:none">
			<h3>Articles</h3><table width="100%" cellspacing="0" cellpadding="10" style="border-radius:5px;text-align:center;">
							<thead>
								<tr>
									<th bgcolor="#FCB322" style="text-align:left;color:#ffffff;border:1px solid #999999;border-left:2px solid #999999;border-top:2px solid #999999;">Publish Date</th>
									<th bgcolor="#5BC0DE" style="color:#ffffff;border:1px solid #999999;border-top:2px solid #999999;">Article</th>
									
								</tr>
							</thead>
							<tbody>';
							
			foreach($aArticles as $articleID=>$article){
				$showArticle .= '
				<tr>
					<td style="text-align:left;border:1px solid #999999;border-left:2px solid #999999;">'.date('m/d/Y', $article['publish_date']).'</td>
					<td style="text-align:left;border:1px solid #999999;border-left:2px solid #999999;"><a href="'.$article['article_link'].'" target="_blank" style="display:block;color:#333333;width:100%;" title="'.$article['article_title'].'"><strong>'.$article['article_title'].'</strong></a></td>
					
				</tr>
				';	
			}
			$showArticle .= '
					</tbody>
				</table>';
			
				
		}else{
			$showArticle = '';	
		}
		
		#build array of trackers and funds being tracked
		$aTrackers 	= array();
		$aFunds		= array();
		
		$query = "
			SELECT *
			FROM ".$memberTracking."
			WHERE member_id=:member_id AND spam='0' AND unsubscribe='0'
		";
		try{
			$rsTracking = $mLink->prepare($query);
			$aValues = array(
				'member_id'	=> $memberID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsTracking->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
						
		while($trackInfo = $rsTracking->fetch(PDO::FETCH_ASSOC)){	
			
			$aTrackFunds	= explode('|', $trackInfo['track_funds']);
			
			if($trackInfo['first_name'] == ''){
				$nameFirst = 'Here';	
			}else{
				$nameFirst = ucfirst(trim($trackInfo['first_name'])).', here';
			}
			
			$aTrackers[$trackInfo['email']] = array(
				'code'			=> $trackInfo['code'],
				'name_first'	=> $nameFirst,
				'name_last'		=> $trackInfo['last_name'],
				'track_funds'	=> $aTrackFunds
			);
			
			foreach($aTrackFunds as $key=>$fundID){
				$aFunds[$fundID] = $fundID;	
			}
			
		}//END While $trackInfo
		
		/*echo '<pre>';
		
		print_r($aTrackers);
		echo '</pre>';*/
		
		//START | Get fund Information
		foreach($aFunds as $fundID=>$same){
			
			$query 			= "
				SELECT m.username, m.name_first, m.name_last, mf.composite_fund, mf.unix_date, mf.public, mf.fund_symbol, mf.fund_name, mfa.MTDReturn, mfa.QTDReturn, mfa.YTDReturn, mfa.sp500MTDReturn, mfa.sp500QTDReturn, mfa.sp500YTDReturn, mfa.unix_date as asOfDate
				FROM members AS m
				INNER JOIN members_fund AS mf ON mf.member_id=m.member_id
				INNER JOIN members_fund_aggregate_history AS mfa ON mfa.fund_id=mf.fund_id
				WHERE m.member_id=:member_id AND mf.fund_id=:fund_id
				ORDER BY mfa.unix_date DESC
				LIMIT 1
			";
			try{
				$rsFundInfo 	= $mLink->prepare($query);
				$aValues 		= array(
					':member_id' 	=> $memberID,
					':fund_id'		=> $fundID
				);
				$preparedQuery 	= str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsFundInfo	->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			//echo $preparedQuery;
			$fundInfo 	= $rsFundInfo->fetch(PDO::FETCH_ASSOC);
			
			$managerName			= $fundInfo['name_first'].' '.$fundInfo['name_last'];
			$managerUsername		= $fundInfo['username'];
			
			$aFunds[$fundID] = array(
				'composite_fund'	=> $fundInfo['composite_fund'],
				'fund_symbol'		=> $fundInfo['fund_symbol'],
				'fund_name'			=> $fundInfo['fund_name'],
				'MTDReturn'			=> $fundInfo['MTDReturn'],
				'QTDReturn'			=> $fundInfo['QTDReturn'],
				'YTDReturn'			=> $fundInfo['YTDReturn'],
				'sp500MTDReturn'	=> $fundInfo['sp500MTDReturn'],
				'sp500QTDReturn'	=> $fundInfo['sp500QTDReturn'],
				'sp500YTDReturn'	=> $fundInfo['sp500YTDReturn'],
				'public'			=> $fundInfo['public']
			);
				
		}//END foreach $aFunds
		//END | Get fund Info
		
		$fundPerfTable = '[FUND_INFO]';
		
		#START | Build fund Info
		/*$fundPerfTable = '<table width="100%" cellspacing="0" cellpadding="10" style="border-radius:5px;text-align:center;">
							<thead>
								<tr>
									<th bgcolor="#FCB322" style="text-align:left;color:#ffffff;border:1px solid #999999;border-left:2px solid #999999;border-top:2px solid #999999;">Fund</th>
									<th bgcolor="#5BC0DE" style="color:#ffffff;border:1px solid #999999;border-top:2px solid #999999;">MTD</th>
									<th bgcolor="#5BC0DE" style="color:#ffffff;border:1px solid #999999;border-top:2px solid #999999;">QTD</th>
									<th bgcolor="#422A88" style="color:#ffffff;border: 2px solid #422A88;border-bottom:2px solid #311f66;">YTD</th>
									
								</tr>
							</thead>
							<tbody>';
		
		foreach($aFunds as $fundID=>$fundInfo){
			
			$mtrLink		= 'https://'.str_replace('_','-',$managerUsername).'.mytrackrecord.com/'.$fundInfo['fund_symbol'];
			
			$mMTD			= number_format($fundInfo['MTDReturn'],2,'.',',');
			$mQTD			= number_format($fundInfo['QTDReturn'],2,'.',',');
			$mYTD			= number_format($fundInfo['YTDReturn'],2,'.',',');
			$spMTD			= number_format($fundInfo['sp500MTDReturn'],2,'.',',');
			$spQTD			= number_format($fundInfo['sp500QTDReturn'],2,'.',',');
			$spYTD			= number_format($fundInfo['sp500YTDReturn'],2,'.',',');
			
			if($mMTD < 0){
				$mtdNegColor = 'color:#ef1b1b;';
			}else{
				$mtdNegColor = '';
			}
			if($mQTD < 0){
				$qtdNegColor = 'color:#ef1b1b;';
			}else{
				$qtdNegColor = '';
			}
			if($mYTD < 0){
				$ytdNegColor = 'color:#ef1b1b;font-weight:bold;';
			}else{
				$ytdNegColor = '';
			}
			
			$fundPerfTable .= '<tr bgcolor="'.$rowColor.'">
									<td style="text-align:left;border:1px solid #999999;border-left:2px solid #999999;"><a href="'.$mtrLink.'" target="_blank" style="display:block;color:#333333;width:100%;" title="Full Profile"><strong>'.$fundInfo['fund_name'].' ('.$fundInfo['fund_symbol'].')</strong></a></td>
									<td style="border:1px solid #999999;'.$mtdNegColor.'">'.number_format($fundInfo['MTDReturn'],2,'.',',').'</td>
									<td style="border:1px solid #999999;'.$qtdNegColor.'">'.number_format($fundInfo['QTDReturn'],2,'.',',').'%</td>
									<td bgcolor="'.$lastCellColor.'" style="border:1px solid #422A88;border-left: 2px solid #422A88;border-right: 2px solid #422A88;'.$ytdNegColor.'">'.number_format($fundInfo['YTDReturn'],2,'.',',').'%</td>
								</tr>';
				
		}
		
		$fundPerfTable .= '<tr bgcolor="#DBE6F2">
							<td bgcolor="#006CA3" style="color:#ffffff; text-align:left;border:2px solid #006CA3;"><strong>S&P 500</strong></td>
							<td style="border:1px solid #999999;border-top:2px solid #006CA3;border-bottom:2px solid #006CA3;'.$spMTDcolor.'">'.$spMTD.'%</td>
							<td style="border:1px solid #999999;border-top:2px solid #006CA3;border-bottom:2px solid #006CA3;'.$spQTDcolor.'">'.$spQTD.'%</td>
							<td bgcolor="#97B9D7" style="border:2px solid #006CA3;'.$spYTDcolor.'">'.$spYTD.'%</td>
						</tr>
					</tbody>
				</table>';*/
		
		//print_r($aTrackers);
		//print_r($aFunds);
		
		
		
		#LOAD EMAIL
		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= 'From: '.$campInfo['email_headers_from'].'' . "\r\n";	
		$headers .= 'Bcc: '.$campInfo['bcc'].'' . "\r\n";
		
		$emailHeadline		= $campInfo['email_headline'];
		$emailTitle			= $campInfo['email_title'];
		$emailSubject		= $campInfo['email_subject'];
		$emailBody			= $campInfo['email_body'];
		
		$aEmailVars			= array(
			'manager'		=> $managerName,
			'as_of_date'	=> date('m/d/Y', $unixAsOfDate)
		);
				
		include('../includes/email/templates/monthly-performance.php');
		
		//Fill in email message variables
		foreach($aEmailVars as $key => $value){
			$message = str_replace('['.strtoupper($key).']', $value, $message);
		}
		
		//Fill in email subject variables
		foreach($aEmailVars as $key => $value){
			$emailSubject = str_replace('['.strtoupper($key).']', $value, $emailSubject);
		}
		
		#START | FOREACH TRACKER
		foreach($aTrackers as $emailAdress=>$trackerInfo){
			
			$aTackedFunds = $trackerInfo['track_funds'];
			
			//print_r($aTackedFunds).'|';
			
			$fundRow = '<table width="100%" cellspacing="0" cellpadding="10" style="border-radius:5px;text-align:center;">
							<thead>
								<tr>
									<th bgcolor="#FCB322" style="text-align:left;color:#ffffff;border:1px solid #999999;border-left:2px solid #999999;border-top:2px solid #999999;">Fund</th>
									<th bgcolor="#5BC0DE" style="color:#ffffff;border:1px solid #999999;border-top:2px solid #999999;">MTD</th>
									<th bgcolor="#5BC0DE" style="color:#ffffff;border:1px solid #999999;border-top:2px solid #999999;">QTD</th>
									<th bgcolor="#422A88" style="color:#ffffff;border: 2px solid #422A88;border-bottom:2px solid #311f66;">YTD</th>
									
								</tr>
							</thead>
							<tbody>';
			foreach($aTackedFunds as $key=>$trackFundID){
				
				
				
				$fundInfo = $aFunds[$trackFundID];
				
				if($fundInfo['public'] == '0'){
					continue;	
				}
				
				$mtrLink		= 'https://'.str_replace('_','-',$managerUsername).'.mytrackrecord.com/'.$fundInfo['fund_symbol'];
			
				$mMTD			= number_format($fundInfo['MTDReturn'],2,'.',',');
				$mQTD			= number_format($fundInfo['QTDReturn'],2,'.',',');
				$mYTD			= number_format($fundInfo['YTDReturn'],2,'.',',');
				$spMTD			= number_format($fundInfo['sp500MTDReturn'],2,'.',',');
				$spQTD			= number_format($fundInfo['sp500QTDReturn'],2,'.',',');
				$spYTD			= number_format($fundInfo['sp500YTDReturn'],2,'.',',');
				
				if($mMTD < 0){
					$mtdNegColor = 'color:#ef1b1b;';
				}else{
					$mtdNegColor = '';
				}
				if($mQTD < 0){
					$qtdNegColor = 'color:#ef1b1b;';
				}else{
					$qtdNegColor = '';
				}
				if($mYTD < 0){
					$ytdNegColor = 'color:#ef1b1b;font-weight:bold;';
				}else{
					$ytdNegColor = '';
				}
				
				$fundRow .= '<tr bgcolor="'.$rowColor.'">
										<td style="text-align:left;border:1px solid #999999;border-left:2px solid #999999;"><a href="'.$mtrLink.'" target="_blank" style="display:block;color:#333333;width:100%;" title="Full Profile"><strong>'.$fundInfo['fund_name'].' ('.$fundInfo['fund_symbol'].')</strong></a></td>
										<td style="border:1px solid #999999;'.$mtdNegColor.'">'.number_format($fundInfo['MTDReturn'],2,'.',',').'</td>
										<td style="border:1px solid #999999;'.$qtdNegColor.'">'.number_format($fundInfo['QTDReturn'],2,'.',',').'%</td>
										<td bgcolor="'.$lastCellColor.'" style="border:1px solid #422A88;border-left: 2px solid #422A88;border-right: 2px solid #422A88;'.$ytdNegColor.'">'.number_format($fundInfo['YTDReturn'],2,'.',',').'%</td>
									</tr>';
				
			}
			
			$fundRow .= '<tr bgcolor="#DBE6F2">
							<td bgcolor="#006CA3" style="color:#ffffff; text-align:left;border:2px solid #006CA3;"><strong>S&P 500</strong></td>
							<td style="border:1px solid #999999;border-top:2px solid #006CA3;border-bottom:2px solid #006CA3;'.$spMTDcolor.'">'.$spMTD.'%</td>
							<td style="border:1px solid #999999;border-top:2px solid #006CA3;border-bottom:2px solid #006CA3;'.$spQTDcolor.'">'.$spQTD.'%</td>
							<td bgcolor="#97B9D7" style="border:2px solid #006CA3;'.$spYTDcolor.'">'.$spYTD.'%</td>
						</tr>
					</tbody>
				</table>';
			
			$to	= $emailAdress;
			
			$aRecipientVars = array(
				'name_first'		=> $trackerInfo['name_first'],
				'name_last'			=> $trackerInfo['name_last'],
				'email_code'		=> $trackerInfo['code']
			);
		
			
			if($sendEmail == true){
						
				//Insert Tracking Record
				$query = "
					INSERT INTO ".$_SESSION['email_tracking_table']." (
						member_id,
						manager_id,
						email,
						name_first,
						name_last,
						camp_id,
						sent,
						timestamp
					)VALUES(
						:member_id,
						:manager_id,
						:email,
						:name_first,
						:name_last,
						:camp_id,
						UNIX_TIMESTAMP(),
						UNIX_TIMESTAMP()
					)
				";
				try{
					$rsInsertTrack = $mLink->prepare($query);
					$aValues = array(
						':member_id'		=> $to,
						':manager_id'		=> $memberID,
						':email'			=> $to,
						':name_first'		=> $aRecipientVars['name_first'],
						':name_last'		=> $aRecipientVars['name_last'],
						':camp_id'			=> $campID
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsInsertTrack->execute($aValues);
				}
				
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				$trackID	= $mLink->lastInsertId();
			}
			
			
				//Generate Tracking Codes
				$openLink	= 'track=open~'.$trackID;
				$clickCode 	= 'click-'.$trackID;
				$openCode 	= 'open-'.$trackID;
				$bounceCode	= '<track_id>'.$trackID.'</track_id>';
				$subscriptionLink = 'track=subscription~'.$trackID.'~'.$managerUsername.'~'.$aRecipientVars['email_code'].'~'.$to;
				
				$sendMessage	= str_replace('[OPEN_LINK]', $openLink, $message);
				$sendMessage 	= str_replace('[CLICK_CODE]', '&trackID='.$clickCode, $sendMessage);
				$sendMessage 	= str_replace('[OPEN_CODE]', '&trackID='.$openCode, $sendMessage);
				$sendMessage	= str_replace('[TRACK_ID]', $bounceCode, $sendMessage);
				$sendMessage 	= str_replace('[SUB_LINK]', $subscriptionLink, $sendMessage);
				
				$sendMessage	= str_replace('[FUND_INFO]', $fundRow, $sendMessage);
				
				//Go through the message and replace recipient based variables
				foreach($aRecipientVars as $key => $value){
					$sendMessage = str_replace('['.strtoupper($key).']', $value, $sendMessage);
				}
				
			if($sendEmail == true){
				mail($to, $emailSubject, $sendMessage, $headers);
				
				echo $to.'<br />';	
			}else{//end if send email = true
				echo $sendMessage;
			}
				
			
			
			
				
		}//END foreach tracker
		
		if($sendEmail == true){
			//update campaign to sent
			$query = "
				UPDATE email_campaigns
				SET camp_status='sent', sent_timestamp=UNIX_TIMESTAMP()
				WHERE camp_id=:camp_id
			";
			try{
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':camp_id'			=> $campID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdate->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
		}
	}//END Foreach $aCampaign
	
}//END Foreach $aCampaigns



//echo '</pre>';


//echo $message;
?>