<?php
/*
TRADE OPEN ORDERS - HTML FILE

SUPPORTING FILES:
_______________________________________________________________

AJAX 			- process/ajax/trade-open-orders-processes.php
PHP JAVASCRIPT	- includes/scripts/trade-open-orders.js.php
HTML 			- THIS FILE includes/pages/trade-open-orders.php
_______________________________________________________________

*/

if($_SESSION['admin'] == '1'){

$limit 		= $_REQUEST['limit'];
$period 	= $_REQUEST['period'];
$rankDate	= $_REQUEST['date'];
$benchOnly	= $_REQUEST['bench'];

if($rankDate == ''){
//	$rankDate = '20190131';	
//      echo "Add <strong><u>&date&YYYYMMDD</u></strong> to set period!";die();
      $rankDate = date('Ymd', strtotime('last day of previous month'));
}

if($limit == ''){
	$limit = 500;	
}

if($period == ''){
	$period = 10;	
}

if($benchOnly == 'all'){
	$benchOnly = '';	
}

$aFundIDs 	= get_fund_symbols($mLink, $_SESSION['member_id'], 'fundIDs');

$query = "
	SELECT *
	FROM rank_anomalies
	WHERE as_of_date=:asOfDate AND (`status` IS NULL OR `status`='unfixable' OR `status`='fixed' OR `status`='valid')
";
try{
	$rsAnomalies = $mLink->prepare($query);
	$aValues = array('asOfDate'=>$rankDate);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsAnomalies->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

//echo $error;
while($anomaly = $rsAnomalies->fetch(PDO::FETCH_ASSOC)){
	
	if($anomaly['status'] == NULL){
		$status = 'no-input';	
	}else{
		$status = $anomaly['status'];	
	}
	
	$aAnomalies[$anomaly['fund_id']][$anomaly['anomaly_timestamp']] = $status;
	
}


$rootLink 	= '?page=06-00-00-011&period='.$period.'';

$year 	= substr($rankDate, 0, 4);
$month	= substr($rankDate, 4, 2);
$day	= substr($rankDate, 6, 2);

$unixRankDate	= mktime(6,0,0,$month,$day,$year);

switch($period){
	
        case '15':
                $badgeGoldImg   = '<img width="20" height="20" title="Beat #1 Mutual Fund" src="/images/badges/Gold-15Year.png">';
                $badgeSilverImg = '<img width="20" height="20" title="Beat Top Quartile Mutal Fund" src="/images/badges/Silver-15Year.png">';
                $badgeBronzeImg = '<img width="20" height="20" title="Beat Top Half of Mutal Funds" alt="Top Percentile" src="/images/badges/Bronze-15Year.png">';
        break;

	case '10':
		$badgeGoldImg 	= '<img width="20" height="20" title="Beat #1 Mutual Fund" src="/images/badges/Gold-10Year.png">';
		$badgeSilverImg = '<img width="20" height="20" title="Beat Top Quartile Mutal Fund" src="/images/badges/Silver-10Year.png">';
		$badgeBronzeImg = '<img width="20" height="20" title="Beat Top Half of Mutal Funds" alt="Top Percentile" src="/images/badges/Bronze-10Year.png">';
	break;
	
	case '5':
		$badgeGoldImg 	= '<img width="20" height="20" title="Beat #1 Mutual Fund" src="/images/badges/Gold-5Year.png">';
		$badgeSilverImg = '<img width="20" height="20" title="Beat Top Quartile Mutal Fund" src="/images/badges/Silver-5Year.png">';
		$badgeBronzeImg = '<img width="20" height="20" title="Beat Top Half of Mutal Funds" alt="Top Percentile" src="/images/badges/Bronze-5Year.png">';
	break;
	
	case '3':
		$badgeGoldImg 	= '<img width="20" height="20" title="Beat #1 Mutual Fund" src="/images/badges/Gold-3Year.png">';
		$badgeSilverImg = '<img width="20" height="20" title="Beat Top Quartile Mutal Fund" src="/images/badges/Silver-3Year.png">';
		$badgeBronzeImg = '<img width="20" height="20" title="Beat Top Half of Mutal Funds" alt="Top Percentile" src="/images/badges/Bronze-3Year.png">';
	break;
	
	case '1':
		$badgeGoldImg 	= '<img width="20" height="20" title="Beat #1 Mutual Fund" src="/images/badges/Gold-1Year.png">';
		$badgeSilverImg = '<img width="20" height="20" title="Beat Top Quartile Mutal Fund" src="/images/badges/Silver-1Year.png">';
		$badgeBronzeImg = '<img width="20" height="20" title="Beat Top Half of Mutal Funds" alt="Top Percentile" src="/images/badges/Bronze-1Year.png">';
	break;
		
}

#Get benchmark values
$query = "
	SELECT * 
	FROM ".$_SESSION['system_benchmarks_table']."
	WHERE period=:period
";
try{
	$rsBench = $mLink->prepare($query);
	$aValues = array('period'=>$rankDate);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsBench->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$aBenchmarks = array();
while($bench = $rsBench->fetch(PDO::FETCH_ASSOC)){
	
	$aType = explode('-',$bench['type']);
	
	$periodYear = $aType[0];
	$periodLevel = $aType[2];
	
	$aBenchmarks[$periodYear][$periodLevel] = $bench['value'];
	
	
}

$aBadges = get_badge_info($mLink, $aBadges=NULL,'all', '40');

#get achievments
$query = "
	SELECT * 
	FROM rank_achievements
	WHERE as_of_date=:period
";
try{
	$rsAchieve = $mLink->prepare($query);
	$aValues = array('period'=>$rankDate);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsAchieve->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$aAchievements = array();

while($ach = $rsAchieve->fetch(PDO::FETCH_ASSOC)){
	$aAchievements[$ach['fund_id']][$ach['badge_id']] = $ach['badge_id'];	
}



/*echo '<pre>';
print_r($aBadges);
print_r($aBenchmarks);
echo '</pre>';*/

foreach($aBenchmarks[$period] as $type=>$value){
	
	//echo $type;
	
	switch($type){
		case 'gold':
			$name = '&uarr; Beat #1 Mutual Fund.';
			$username = '#1 Funds';
			$img = '<img width="40" height="40" title="Top Percentile" alt="Top Percentile" src="/images/badges/Gold-10Year.png">';
		break;
		
		case 'silver':
			$name = '&uarr; Beat Top Quartile Mutual Fund.';
			$username = 'Top Quartile';
			$img = '<img width="40" height="40" title="Top Percentile" alt="Top Percentile" src="/images/badges/Silver-10Year.png">';
		break;
		
		case 'bronze':
			$name = '&uarr; Beat Top Half Mutual Fund.';
			$username = '50th Percentile';
			$img = '<img width="40" height="40" title="Top Percentile" alt="Top Percentile" src="/images/badges/Bronze-10Year.png">';
		break;	
	}
	
	//echo $name;
	
	$aRanks[] = array(
		'type'			=> 'bench',
		'AAR'			=> $value,
		'fund_name'		=> $name,
		'rank'			=> 0,
		'username'		=> $username,
		'img'			=> $img	
	);
	 
}

$aProducts = get_products($mLink, true);


switch($period){
	
        case '15':

                $query = "
                        SELECT *
                        FROM rank_stage_pro
                        WHERE as_of_date=:asOfDate AND period=:period
                        ORDER BY rank ASC
                        LIMIT ".$limit."
                ";
                $aValues = array(
                        ':period'       => $period,
                        ':asOfDate'     => $rankDate
                );

        break;

	case '10':
		
		$query = "
			SELECT *
			FROM rank_stage_pro
			WHERE as_of_date=:asOfDate AND period=:period
			ORDER BY rank ASC
			LIMIT ".$limit."
		";
		$aValues = array(
			':period' 	=> $period,
			':asOfDate'	=> $rankDate
		);
		
	break;
	
	case '5':
		
		$query = "
			SELECT *
			FROM rank_stage_pro
			WHERE as_of_date=:asOfDate AND period=:period
			ORDER BY rank ASC
			LIMIT ".$limit."
		";
		$aValues = array(
			':period' 	=> $period,
			':asOfDate'	=> $rankDate
		);
		
	break;
	
	case '3':
		
		$query = "
			SELECT *
			FROM rank_stage_pro
			WHERE as_of_date=:asOfDate AND period=:period
			ORDER BY rank ASC
			LIMIT ".$limit."
		";
		$aValues = array(
			':period' 	=> $period,
			':asOfDate'	=> $rankDate
		);
		
	break;
	
	case '1':
		
		$query = "
			SELECT *
			FROM rank_stage_pro
			WHERE as_of_date=:asOfDate AND period=:period
			ORDER BY rank ASC
			LIMIT ".$limit."
		";
		$aValues = array(
			':period' 	=> $period,
			':asOfDate'	=> $rankDate
		);
		
	break;
	
	case 'anomalies':
		
		$query = "
		SELECT rsp.*
		FROM rank_stage_pro as rsp
		WHERE rsp.as_of_date=:asOfDate AND (SELECT ra.fund_id FROM rank_anomalies ra WHERE ra.fund_id=rsp.fund_id LIMIT 1)=rsp.fund_id
		GROUP BY fund_id
		ORDER BY rsp.rank ASC
		";
		$aValues = array(
			
			':asOfDate'	=> $rankDate
		);
		
	break;
}

// Get ranks table

//echo $query;
try{
	$rsRanks = $mLink->prepare($query);
	
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsRanks->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

echo $error;
//$aRanks = array();
while($ranks = $rsRanks->fetch(PDO::FETCH_ASSOC)){
	
	$fundID 	= $ranks['fund_id'];
	$memberID	= $ranks['member_id'];
	
	//skip if fund id is in invalid funds array
	if(in_array($fundID, $aInvalidFunds)){
		continue;	
	}
	
	$query = "
		SELECT mf.fund_name, mf.fund_symbol, m.name_first, m.name_last, m.username, ms.product_id
		FROM members_fund mf, members m, members_subscriptions ms
		WHERE mf.fund_id=:fund_id AND mf.member_id=m.member_id AND ms.member_id=mf.member_id AND ms.product_type='membership' AND ms.active='1'
	";
	try{
	$rsMember = $mLink->prepare($query);
		$aValues = array(
			':fund_id' 	=> $fundID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsMember->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	echo $error;
	$member = $rsMember->fetch(PDO::FETCH_ASSOC);
	
	$membershipLevel = $aProducts[$member['product_id']]['alt_product_name_2'];
	
	$fundName	= $member['fund_name'];
	$fundSymbol	= $member['fund_symbol'];
	$username	= $member['username'];
	$fullName	= $member['name_first'].' '.$member['name_last'];
	
	if($fullName == ''){ $fullName = $member['username'];}
	
	$tenYearAAR	= $ranks['AAR'];
	
	$asOfDate	= explode('/',$ranks['asOfDate']);
	
	//$startDate	= '1420092000';
	//$endDate	= mktime(6,0,0,$asOfDate[0],$asOfDate[1],$asOfDate[2]);
	
	$rank		= $ranks['rank'];
	
	$aRanks[] = array(
		'type'				=> 'fund',
		'fund_id'			=> $fundID,
		'rank'				=> $rank,
		/*'dateStart'		=> $startDate,
		'dateEnd'			=> $endDate,*/
		'AAR'				=> $tenYearAAR,
		//'period'			=> '10-Year',
		'member_id'			=> $memberID,
		'username'			=> $username,
		'full_name'			=> $fullName,
		'fund_symbol'		=> $fundSymbol,
		'fund_name'			=> $fundName,
		'exclude'			=> $ranks['exclude'],
		'membershipLevel'	=> $membershipLevel
	);
	
}

/*echo '<pre>';
print_r($aRanks);
echo '</pre>';*/

//foreach($aRanks as $key=>$aValues){
				
	usort($aRanks, function($a, $b) {
		return $b['AAR'] - $a['AAR'];
	});
	
	//$aRanks[$key] = $aValues;
		
//}

/*echo '<pre>';
print_r($aRanks);
echo '</pre>';*/

?>
        <!-- START EXCLUDE MODAL-->
        <div class="modal fade" id="exclude" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        	<div class="modal-dialog modal-wide">
            	<div class="modal-content">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Exclude Fund from Rankings</span></h4>
                    </div>
                    <div class="modal-body">
                    
                    	<form method="post" class="exclude-form">
                            
                            <h4>Are you sure you want to exclude <span id="exclude-fund-symbol"></span> from rankings?</h4>
                            
                            <div class="form-group">
                            <label class="control-label">As Of Date</label>
                            <input type="text" class="form-control" value="<?php echo $rankDate;?>" name="asOfDate" />
                            </div>
                            
                            
                            
                            
                            
                            <input type="hidden" name="as_of_timestamp" value="<?php echo $currentQuarter;?>" />
                            
                            <input type="hidden" name="fund_id" id="setFundID" value="" />
                            <input type="hidden" name="rank_table" value="rank_stage_pro" />
                            
                                                        
                            <div id="exclude-form-submit"><input type="submit" value="Exclude Fund" class="btn btn-success" /></div>
                            
                            <div id="exclude-form-errors"></div>
                            
                        </form>
                    
                    </div><!--modal-body-->
          		</div><!--modal-content -->
        	</div><!--modal-dialog -->
        </div><!--modal-->
        
        <!-- START Include MODAL-->
        <div class="modal fade" id="include" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        	<div class="modal-dialog modal-wide">
            	<div class="modal-content">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Exclude Fund from Rankings</span></h4>
                    </div>
                    <div class="modal-body">
                    
                    	<form method="post" class="include-form">
                            
                            <h4>Are you sure you want to include <span id="include-fund-symbol"></span> in rankings?</h4>
                            
                            <div class="form-group">
                            <label class="control-label">As Of Date</label>
                            <input type="text" class="form-control" value="<?php echo $rankDate;?>" name="asOfDate" />
                            </div>
                            
                            
                            
                            
                            
                            <input type="hidden" name="as_of_timestamp" value="<?php echo $currentQuarter;?>" />
                            
                            <input type="hidden" name="fund_id" id="setIncludeFundID" value="" />
                            <input type="hidden" name="rank_table" value="rank_stage_pro" />
                            
                                                        
                            <div id="include-form-submit"><input type="submit" value="Include Fund" class="btn btn-success" /></div>
                            
                            <div id="include-form-errors"></div>
                            
                        </form>
                    
                    </div><!--modal-body-->
          		</div><!--modal-content -->
        	</div><!--modal-dialog -->
        </div><!--modal-->
        
        <!-- START Include MODAL-->
        <div class="modal fade" id="notes-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        	<div class="modal-dialog modal-wide">
            	<div class="modal-content">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Fund Notes</span></h4>
                    </div>
                    <div class="modal-body" id="load-note-form">
                    
                    	
                    
                    </div><!--modal-body-->
          		</div><!--modal-content -->
        	</div><!--modal-dialog -->
        </div><!--modal-->
        
        <div class="row">
            <div class="col-md-12">
                
                <div class="tabbable tabbable-custom">
                
                	<div class="tab-content">
                    	
                        <form action="" method="post" class="period-form">
                            <div class="form-group">
                                <label class="input-label"><strong>Select Ranking Period</strong> </label>
                                <select class="form-control" name="period" id="period-select">
                                    <option value="<?php echo $rankDate;?>"><?php echo date('m/d/Y',$unixRankDate); ?></option>
                                    <option disabled>---------</option>
                                    <option value='20170430'>4/30/2017</option>
                                    <option value='20170331'>3/31/2017</option>
                                    <option value='20170228'>2/28/2017</option>
                                    <option value='20170131'>1/31/2017</option>
                                    
                                </select>
                            </div>
                        </form>
                        
                    	<div class="tab-pane active" id="rank-info">
                        	<h3>Your Fund Ranks</h3>
                            
                            <table class="table table-striped table-bordered table-hover table-full-width" id="ten_year_table2">
                                <thead>
                                    <tr>
                                        <th width="55px">Rank</th>
                                        <th>Period</th>
                                        <th>Fund Symbol</th>
                                        <th>Fund Name</th>
                                        <th>AAR</th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    
										
									$query = "
										SELECT rsp.*, mf.fund_symbol, mf.fund_name, ra.badge_id
										FROM rank_stage_pro rsp
										Left JOIN members_fund mf ON mf.fund_id=rsp.fund_id
										LEFT JOIN rank_achievements as ra ON ra.fund_id=rsp.fund_id AND ra.as_of_date=:asOfDate
										WHERE rsp.member_id=:member_id AND rsp.as_of_date=:asOfDate 
									";
									try{
									$rsMemberRanks = $mLink->prepare($query);
										$aValues = array(
											':member_id' 	=> $_SESSION['member_id'],
											':asOfDate'		=> $rankDate
										);
										$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
										$rsMemberRanks->execute($aValues);
									}
									catch(PDOException $error){
										// Log any error
										file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
									}
										
									//echo $preparedQuery;
									
									$aMyRanks = array();
									
									while($mRank = $rsMemberRanks->fetch(PDO::FETCH_ASSOC)){
										
										$rank 			= $mRank['rank'];
										$AAR			= $mRank['AAR'];
										$memberID		= $mRank['member_id'];
										$fundName		= $mRank['fund_name'];
										$fundSymbol		= $mRank['fund_symbol'];
										$rankPeriod		= $mRank['period'];
										$rankBadgeID	= $mRank['badge_id'];
										$fundID			= $mRank['fund_id'];
										
										
										$badgeImg		= '<img src="'.$aBadges[$rankBadgeID]['badge_img'].'" alt="'.$aBadges[$rankBadgeID]['badge_desc'].'" width="20" height="20" />';
										
										if(!in_array($fundID.'-'.$rankPeriod, $aMyRanks)){	
										?>
                                        <tr>
                                            <td><?php echo $rank;?> <?php echo $badgeImg;?></td>
                                            <td><?php echo date('m/d/Y', $unixRankDate);?> - <?php echo $rankPeriod;?> Year</td>
                                            <td><a href="?page=04-00-00-001&member=<?php echo $memberID;?>&tab=<?php echo $fundID;?>" <?php echo $linkStyle;?> target="_blank"><?php echo $fundSymbol;?></a></td>
                                            <td><?php echo $fundName;?> </td>
                                            <td><?php echo number_format($AAR,2,'.',',');?></td>
                                            
                                        </tr>
                                        <?php
										
										$aMyRanks[] = $fundID.'-'.$rankPeriod;
										}
												
									}
									
                                    
                                    ?>
                                    
                                   
                                </tbody>
                            </table>
                            
                        </div><!--tab-->
                    
                    </div><!--tab-content-->
                
                </div><!--tabbable-->
                
                <div class="tabbable tabbable-custom">
                    
                    <ul class="nav nav-tabs">
                        <li <?php if($period == 15){ echo 'class="active"';}?>><a href="?page=06-00-00-011&period=15">15 Year</a></li>
                        <li <?php if($period == 10){ echo 'class="active"';}?>><a href="?page=06-00-00-011&period=10">10 Year</a></li>
                        <li <?php if($period == 5){ echo 'class="active"';}?>><a href="?page=06-00-00-011&period=5">5 Year</a></li>
                        <li <?php if($period == 3){ echo 'class="active"';}?>><a href="?page=06-00-00-011&period=3">3 Year</a></li>
                        <li <?php if($period == 1){ echo 'class="active"';}?>><a href="?page=06-00-00-011&period=1">1 Year</a></li>
                        <li <?php if($period == 'anomalies'){ echo 'class="active"';}?>><a href="?page=06-00-00-011&period=anomalies">Anomalies</a></li>
                        
                    </ul>

                    <div class="tab-content">
                    
                        
                        <!--FIFTEEN YEAR RANKINGS TAB-->
                        <div class="tab-pane active" id="fifteenYear">
                            
                            <div class="btn-group" style="margin-bottom: 20px;float:left;">
                                    <a href="<?php echo $rootLink;?>&bench=all" class="btn <?php if($benchOnly == ''){echo 'btn-info';}else{ echo 'btn-default';}?>">Show All</a>
                                    <a href="<?php echo $rootLink;?>&bench=gold" class="btn <?php if($benchOnly == 'gold'){echo 'btn-info';}else{ echo 'btn-default';}?>">#1 Funds</a>
                                    <a href="<?php echo $rootLink;?>&bench=silver" class="btn <?php if($benchOnly == 'silver'){echo 'btn-info';}else{ echo 'btn-default';}?>">Top Quartile</a>
                                    <a href="<?php echo $rootLink;?>&bench=bronze" class="btn <?php if($benchOnly == 'bronze'){echo 'btn-info';}else{ echo 'btn-default';}?>">Top Half</a>
                            </div><!--btn-group-->
                            
                        	<table class="table table-striped table-bordered table-hover table-full-width" id="fifteen_year_table">
                                <thead>
                                    <tr>
                                        <th width="55px">Rank</th>
                                        <th>Manager</th>
                                        <th>Fund Symbol</th>
                                        <th>Fund Name</th>
                                        <th>AAR</th>
                                        <th>Membership</th>
                                        <th>Anomalies</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    
                                    foreach($aRanks as $key=>$aRank){
                                        
										$img		= $aRank['img'];
                                        $rank 		= $aRank['rank'];
                                        $type		= $aRank['type'];
										
                                        $fundID 	= $aRank['fund_id'];
                                        $memberID	= $aRank['member_id'];
                                        
                                        
                                        $fundName	= $aRank['fund_name'];
                                        $fundSymbol	= $aRank['fund_symbol'];
                                        
										$username	= $aRank['username'];
                                        $tenYearAAR	= $aRank['AAR'];
										
										$exclude	= $aRank['exclude'];
                                        
                                        if($type == 'bench' && $benchOnly == ''){
											$addStyle = 'style="background:#47A447;color:#ffffff;font-size:16px;"';
											?>
                                            <tr>
                                                <td <?php echo $addStyle;?>><?php echo $img;?></td>
                                                
                                                <td <?php echo $addStyle;?>><span style="display:block;padding-top:8px;"><?php echo $username;?></span></td>
                                                <td <?php echo $addStyle;?>></td>
                                                <td <?php echo $addStyle;?>><span style="display:block;padding-top:8px;"><?php echo $fundName;?></span></td>
                                                <td <?php echo $addStyle;?>><?php echo number_format($fifteenYearAAR,2,'.',',');?></td>
                                                <td <?php echo $addStyle;?>></td>
                                                <td <?php echo $addStyle;?>></td>
                                                <td <?php echo $addStyle;?>></td>
                                                
                                            </tr>
                                            <?php
										}elseif($type != 'bench'){
											
											$displayRecord = false;
											
											if($fifteenYearAAR >= $aBenchmarks[$period]['gold']){
												$addBadge = $badgeGoldImg;
												
												if($benchOnly != '' && $benchOnly == 'gold' || $benchOnly == ''){
													$displayRecord = true;	
												}
												
											}elseif($fifteenYearAAR >= $aBenchmarks[$period]['silver'] && $fifteenYearAAR < $aBenchmarks[$period]['gold']){
												$addBadge = $badgeSilverImg;
												
												if($benchOnly != '' && $benchOnly == 'silver' || $benchOnly == ''){
													$displayRecord = true;	
												}
												
											}elseif($fifteenYearAAR >= $aBenchmarks[$period]['bronze'] && $fifteenYearAAR < $aBenchmarks[$period]['silver']){
												$addBadge = $badgeBronzeImg;
												
												if($benchOnly != '' && $benchOnly == 'bronze' || $benchOnly == ''){
													$displayRecord = true;	
												}
											}else{
												$addBadge = '';	
												
												if($benchOnly == ''){
													$displayRecord = true;	
												}
											}
											
											$addStyle = '';
											$linkStyle = '';
											
											if($memberID == $_SESSION['member_id']){
												$addStyle = 'style="background:#5BC0DE;color:#ffffff;font-size:16px;"';
												$linkStyle = 'style="color:#ffffff;"';
											}
											
											if($exclude == '1'){
												$actionBtn = '<a href="#include" class="btn btn-xs btn-info" data-toggle="modal" onClick="includeFund(\''.$fundID.'\', \''.$fundSymbol.'\');">Include In Rankings</a>';
											}else{
												$actionBtn = '<a href="#exclude" class="btn btn-xs btn-danger" data-toggle="modal" onClick="excludeFund(\''.$fundID.'\', \''.$fundSymbol.'\');">Exclude From Rankings</a>';
											}
											
											#anomalies
											
											$anomalies 		= $aAnomalies[$fundID];
											$numAnomalies 	= count($anomalies);
											$aListAnom 		= array();
											
											foreach($anomalies as $anomTimestamp=>$anomStatus){
												$aListAnom[] = date('Y/m/d', $anomTimestamp);
												
												
												
												
											}
											
											
											
											if(in_array('no-input',$anomalies)){
												$anomLabel = 'warning';		
											}elseif(in_array('unfixable',$anomalies)){
												$anomLabel = 'danger';
											}elseif(in_array('fixed',$anomalies)){
												$anomLabel = 'info';
											}else{
												$anomLabel = 'success';	
											}
											
											
											
											/*if($numAnomalies > 0){
												$anomLabel = 'warning';	
											}else{
												$anomLabel = 'info';
											}*/
											
											$listAnom = implode(' | ',$aListAnom);
											
											#notes
											$lastNote = get_last_rank_note($mLink, $fundID);
											
											if($lastNote == ''){
												$notesBtn = '<a href="#notes-modal" data-toggle="modal" class="btn btn-info btn-xs" onclick="loadNotes(\''.$fundID.'\',\''.$fundSymbol.'\',\''.$username.'\',\''.$unixRankDate.'\',\''.$rankDate.'\');">Notes</a>';
											}else{
												$notesBtn = '<a href="#notes-modal" data-toggle="modal" class="btn btn-warning btn-xs" onclick="loadNotes(\''.$fundID.'\',\''.$fundSymbol.'\',\''.$username.'\',\''.$unixRankDate.'\',\''.$rankDate.'\');">Notes | '.date('Y/m/d', $lastNote).'</a>';
											}
											unset($anomBadges);
											#badges
											if($_REQUEST['period'] = 'anomalies'){
												
												$fundBadgeIDs = $aAchievements[$fundID];
												
												foreach($fundBadgeIDs as $key=>$badgeID){
													
													$anomBadges .= '<img src="'.$aBadges[$badgeID]['badge_img'].'" alt="'.$aBadges[$badgeID]['badge_desc'].'" width="25" height="25" /> ';
														
												}
													
											}
											
											
											
											if($displayRecord == true){	
												?>
												<tr>
													<td <?php echo $addStyle;?>><?php echo $rank;?> <?php echo $addBadge;?><?php echo $anomBadges;?></td>
													<td <?php echo $addStyle;?>><a href="?page=20-00-00-003&member=<?php echo $memberID;?>" <?php echo $linkStyle;?> target="_blank"><?php echo $username;?></a></td>
													<td <?php echo $addStyle;?>><a href="?page=04-00-00-001&member=<?php echo $memberID;?>&tab=<?php echo $fundID;?>" <?php echo $linkStyle;?> target="_blank"><?php echo $fundSymbol;?></a></td>
													<td <?php echo $addStyle;?>><?php echo $fundName;?> </td>
													<td <?php echo $addStyle;?>><?php echo number_format($tenYearAAR,2,'.',',');?></td>
                                                    <td <?php echo $addStyle;?>><?php echo $aRank['membershipLevel'];?> </td>
                                                    <td <?php echo $addStyle;?>><span class="label label-<?php echo $anomLabel;?>" title="<?php echo $listAnom;?>"><?php echo $numAnomalies;?></span></td>
                                                    <td id="rankAction-<?php echo $fundID;?>"><?php echo $actionBtn;?> <?php echo $notesBtn;?></td>
													
												</tr>
												<?php
											}
										}
										
                                        
                                    }
                                    ?>
                                    
                                   
                                </tbody>
                            </table>

                        
                        </div><!--fifteenYear-->
                        <!--END FIFTEEN YEAR RANKINGS TAB-->
                        
                        
                        <!--TENE YEAR RANKINGS TAB-->
                        <div class="tab-pane" id="tenYear">



                        </div><!--tenYear-->

                        <!--FIVE YEAR RANKINGS TAB-->
                        <div class="tab-pane" id="fiveYear">
                            
                            
                        
                        </div><!--fiveYear-->
                        <!--END FIVE YEAR RANKINGS TAB-->
                        
                        
                        <!--THREE YEAR RANKINGS TAB-->
                        <div class="tab-pane" id="threeYear">
                            
                        
                        </div><!--threeYear-->
                        <!--END THREE YEAR RANKINGS TAB-->
                        
                        
                        <!--ONE YEAR RANKINGS TAB-->
                        <div class="tab-pane" id="oneYear">
                            
                        
                        </div><!--oneYear-->
                        <!--END ONE YEAR RANKINGS TAB-->
                        
                        
                    
                    
                    </div><!--tab-content-->
                </div><!--tabbable tabbable-custom-->
                
            </div><!--col-md-12-->
        </div><!--row-->
        <!-- END PAGE CONTENT-->
<?php
        
}else{
	?>
    <div class="alert alert-info">
    	<h3>Site Rankings Coming Soon!</h3>
    </div>
    <?php
}   

?>     
        
