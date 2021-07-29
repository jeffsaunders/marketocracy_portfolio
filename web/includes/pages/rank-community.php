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
	$rankDate = '20170331';	
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

$rootLink 	= '?page=06-00-00-002&period='.$period.'';

$year 	= substr($rankDate, 0, 4);
$month	= substr($rankDate, 4, 2);
$day	= substr($rankDate, 6, 2);

$unixRankDate	= mktime(6,0,0,$month,$day,$year);

switch($period){
	
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

/*echo '<pre>';
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
	
	$aTenYearRanks[] = array(
		'type'			=> 'bench',
		'AAR'			=> $value,
		'fund_name'		=> $name,
		'rank'			=> 0,
		'username'		=> $username,
		'img'			=> $img	
	);
	 
}
/*echo '<pre>';
print_r($aTenYearRanks);
echo '</pre>';*/

//INVALID FUNDS
$query = "
	SELECT fund_id
	FROM ".$_SESSION['invalid_funds_table']."
	WHERE fund_id<>''
";
try{
	$rsInvalidFunds = $mLink->prepare($query);
	$aValues = array();
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsInvalidFunds->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$invalidFunds = array();
while($invalid = $rsInvalidFunds->fetch(PDO::FETCH_ASSOC)){

	$invalidFunds[] = $invalid['fund_id'];

}

switch($benchOnly){
	case 'gold':
		$queryRank = "
			SELECT *
			FROM rank_stage_community
			WHERE period=:period AND beat='gold' AND date_end=:endDate
			ORDER BY rank ASC
		";
	break;
	
	case 'silver':
		$queryRank = "
			SELECT *
			FROM ".$_SESSION['community_rankings_table']."
			WHERE period=:period AND beat='silver' AND date_end=:endDate
			ORDER BY rank ASC
		";
	break;
	
	case 'bronze':
		$queryRank = "
			SELECT *
			FROM ".$_SESSION['community_rankings_table']."
			WHERE period=:period AND beat='bronze' AND date_end=:endDate
			ORDER BY rank ASC
		";
		
	break;
	
	default:
		$queryRank = "
			SELECT *
			FROM ".$_SESSION['community_rankings_table']."
			WHERE period=:period AND date_end=:endDate
			ORDER BY rank ASC
			LIMIT ".$limit."
		";
	break;	
}

// Get ranks table


try{
	$rsRanks = $mLink->prepare($queryRank);
	$aValues = array(
		':period' 	=> $period,
		':endDate'	=> $unixRankDate
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsRanks->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}


//$aTenYearRanks = array();
while($ranks = $rsRanks->fetch(PDO::FETCH_ASSOC)){
	
	$fundID 	= $ranks['fund_id'];
	$memberID	= $ranks['member_id'];
	
	//skip if fund id is in invalid funds array
	if(in_array($fundID, $aInvalidFunds)){
		continue;	
	}

	$aFundInfo	= get_funds($mLink, $fundID, 'fundBasic');
	
	$fundName	= $aFundInfo['fundName'];
	$fundSymbol	= $aFundInfo['fundSymbol'];
	$username	= get_member($mLink, $memberID, 'username');
	$tenYearAAR	= $ranks['AAR'];
	
	$asOfDate	= explode('/',$ranks['asOfDate']);
	
	$startDate	= '1420092000';
	$endDate	= mktime(6,0,0,$asOfDate[0],$asOfDate[1],$asOfDate[2]);
	
	$rank		= $ranks['rank'];
	
	$aTenYearRanks[] = array(
		'type'			=> 'fund',
		'fund_id'		=> $fundID,
		'rank'			=> $rank,
		/*'dateStart'		=> $startDate,
		'dateEnd'		=> $endDate,*/
		'AAR'			=> $tenYearAAR,
		//'period'		=> '10-Year',
		'member_id'		=> $memberID,
		'username'		=> $username,
		'fund_symbol'	=> $fundSymbol,
		'fund_name'		=> $fundName		
	);
	
}

/*echo '<pre>';
print_r($aTenYearRanks);
echo '</pre>';*/

//foreach($aTenYearRanks as $key=>$aValues){
				
	usort($aTenYearRanks, function($a, $b) {
		return $b['AAR'] - $a['AAR'];
	});
	
	//$aTenYearRanks[$key] = $aValues;
		
//}

/*echo '<pre>';
print_r($aTenYearRanks);
echo '</pre>';*/

?>
        
        
        <div class="row">
            <div class="col-md-12">
                
                <div class="tabbable tabbable-custom">
                
                	<div class="tab-content">
                    
                    	<div class="tab-pane active" id="rank-info">
                        	<h3>Your Fund Ranks</h3>
                            
                            <table class="table table-striped table-bordered table-hover table-full-width" id="ten_year_table2">
                                <thead>
                                    <tr>
                                        <th width="55px">Rank</th>
                                        <th>Manager</th>
                                        <th>Fund Symbol</th>
                                        <th>Fund Name</th>
                                        <th>AAR</th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    
                                    foreach($aTenYearRanks as $key=>$aRank){
                                        
										$img		= $aRank['img'];
                                        $rank 		= $aRank['rank'];
                                        $type		= $aRank['type'];
										
                                        $fundID 	= $aRank['fund_id'];
                                        $memberID	= $aRank['member_id'];
                                        
                                        
                                        $fundName	= $aRank['fund_name'];
                                        $fundSymbol	= $aRank['fund_symbol'];
                                        
										$username	= $aRank['username'];
                                        $tenYearAAR	= $aRank['AAR'];
										
										
                                        
                                        
											
											$displayRecord = false;
											
											if($tenYearAAR >= $aBenchmarks[$period]['gold']){
												$addBadge = $badgeGoldImg;
												
												if($benchOnly != '' && $benchOnly == 'gold' || $benchOnly == ''){
													$displayRecord = true;	
												}
												
											}elseif($tenYearAAR >= $aBenchmarks[$period]['silver'] && $tenYearAAR < $aBenchmarks[$period]['gold']){
												$addBadge = $badgeSilverImg;
												
												if($benchOnly != '' && $benchOnly == 'silver' || $benchOnly == ''){
													$displayRecord = true;	
												}
												
											}elseif($tenYearAAR >= $aBenchmarks[$period]['bronze'] && $tenYearAAR < $aBenchmarks[$period]['silver']){
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
											
											if(!in_array($fundID, $aFundIDs)){
												$displayRecord = false;
											}
											
											$addStyle = '';
											$linkStyle = '';
											
											if($memberID == $_SESSION['member_id']){
												$addStyle = 'style="background:#5BC0DE;color:#ffffff;font-size:16px;"';
												$linkStyle = 'style="color:#ffffff;"';
											}
											
											if($displayRecord == true){	
												?>
												<tr>
													<td <?php echo $addStyle;?>><?php echo $rank;?> <?php echo $addBadge;?></td>
													<td <?php echo $addStyle;?>><a href="?page=04-00-00-001&member=<?php echo $memberID;?>" <?php echo $linkStyle;?> target="_blank"><?php echo $username;?></a></td>
													<td <?php echo $addStyle;?>><a href="?page=04-00-00-001&member=<?php echo $memberID;?>&tab=<?php echo $fundID;?>" <?php echo $linkStyle;?> target="_blank"><?php echo $fundSymbol;?></a></td>
													<td <?php echo $addStyle;?>><?php echo $fundName;?> </td>
													<td <?php echo $addStyle;?>><?php echo number_format($tenYearAAR,2,'.',',');?></td>
													
												</tr>
												<?php
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
                        <li <?php if($period == 10){ echo 'class="active"';}?>><a href="?page=06-00-00-002&period=10">10 Year</a></li>
                        <li <?php if($period == 5){ echo 'class="active"';}?>><a href="?page=06-00-00-002&period=5">5 Year</a></li>
                        <li <?php if($period == 3){ echo 'class="active"';}?>><a href="?page=06-00-00-002&period=3">3 Year</a></li>
                        <li <?php if($period == 1){ echo 'class="active"';}?>><a href="?page=06-00-00-002&period=1">1 Year</a></li>
                        
                    </ul>

                    <div class="tab-content">
                    
                        
                        <!--TEN YEAR RANKINGS TAB-->
                        <div class="tab-pane active" id="tenYear">
                            
                            <div class="btn-group" style="margin-bottom: 20px;float:left;">
                                    <a href="<?php echo $rootLink;?>&bench=all" class="btn <?php if($benchOnly == ''){echo 'btn-info';}else{ echo 'btn-default';}?>">Show All</a>
                                    <a href="<?php echo $rootLink;?>&bench=gold" class="btn <?php if($benchOnly == 'gold'){echo 'btn-info';}else{ echo 'btn-default';}?>">#1 Funds</a>
                                    <a href="<?php echo $rootLink;?>&bench=silver" class="btn <?php if($benchOnly == 'silver'){echo 'btn-info';}else{ echo 'btn-default';}?>">Top Quartile</a>
                                    <a href="<?php echo $rootLink;?>&bench=bronze" class="btn <?php if($benchOnly == 'bronze'){echo 'btn-info';}else{ echo 'btn-default';}?>">Top Half</a>
                            </div><!--btn-group-->
                            
                        	<table class="table table-striped table-bordered table-hover table-full-width" id="ten_year_table">
                                <thead>
                                    <tr>
                                        <th width="55px">Rank</th>
                                        <th>Manager</th>
                                        <th>Fund Symbol</th>
                                        <th>Fund Name</th>
                                        <th>AAR</th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    
                                    foreach($aTenYearRanks as $key=>$aRank){
                                        
										$img		= $aRank['img'];
                                        $rank 		= $aRank['rank'];
                                        $type		= $aRank['type'];
										
                                        $fundID 	= $aRank['fund_id'];
                                        $memberID	= $aRank['member_id'];
                                        
                                        
                                        $fundName	= $aRank['fund_name'];
                                        $fundSymbol	= $aRank['fund_symbol'];
                                        
										$username	= $aRank['username'];
                                        $tenYearAAR	= $aRank['AAR'];
										
										
                                        
                                        if($type == 'bench' && $benchOnly == ''){
											$addStyle = 'style="background:#47A447;color:#ffffff;font-size:16px;"';
											?>
                                            <tr>
                                                <td <?php echo $addStyle;?>><?php echo $img;?></td>
                                                
                                                <td <?php echo $addStyle;?>><span style="display:block;padding-top:8px;"><?php echo $username;?></span></td>
                                                <td <?php echo $addStyle;?>></td>
                                                <td <?php echo $addStyle;?>><span style="display:block;padding-top:8px;"><?php echo $fundName;?></span></td>
                                                <td <?php echo $addStyle;?>><?php echo number_format($tenYearAAR,2,'.',',');?></td>
                                                
                                            </tr>
                                            <?php
										}elseif($type != 'bench'){
											
											$displayRecord = false;
											
											if($tenYearAAR >= $aBenchmarks[$period]['gold']){
												$addBadge = $badgeGoldImg;
												
												if($benchOnly != '' && $benchOnly == 'gold' || $benchOnly == ''){
													$displayRecord = true;	
												}
												
											}elseif($tenYearAAR >= $aBenchmarks[$period]['silver'] && $tenYearAAR < $aBenchmarks[$period]['gold']){
												$addBadge = $badgeSilverImg;
												
												if($benchOnly != '' && $benchOnly == 'silver' || $benchOnly == ''){
													$displayRecord = true;	
												}
												
											}elseif($tenYearAAR >= $aBenchmarks[$period]['bronze'] && $tenYearAAR < $aBenchmarks[$period]['silver']){
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
											
											if($displayRecord == true){	
												?>
												<tr>
													<td <?php echo $addStyle;?>><?php echo $rank;?> <?php echo $addBadge;?></td>
													<td <?php echo $addStyle;?>><a href="?page=04-00-00-001&member=<?php echo $memberID;?>" <?php echo $linkStyle;?> target="_blank"><?php echo $username;?></a></td>
													<td <?php echo $addStyle;?>><a href="?page=04-00-00-001&member=<?php echo $memberID;?>&tab=<?php echo $fundID;?>" <?php echo $linkStyle;?> target="_blank"><?php echo $fundSymbol;?></a></td>
													<td <?php echo $addStyle;?>><?php echo $fundName;?> </td>
													<td <?php echo $addStyle;?>><?php echo number_format($tenYearAAR,2,'.',',');?></td>
													
												</tr>
												<?php
											}
										}
										
                                        
                                    }
                                    ?>
                                    
                                   
                                </tbody>
                            </table>
                        
                        </div><!--tenYear-->
                        <!--END TEN YEAR RANKINGS TAB-->
                        
                        
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
        