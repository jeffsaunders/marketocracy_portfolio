<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

require_once("../../includes/system-functions.php");

$process = $_REQUEST['process'];


if($process == "1"){
	
	$isFundActive = $_REQUEST['active_only'];
	
	$masterMember = $_REQUEST['member'];
	
	$testDate = $_REQUEST['test'];

	if($testDate != ""){
		$today = $testDate;
		$yesterday = date('Ymd', strtotime('-1 day', strtotime($today)));
	}else{
		$today = date('Ymd');
 		$yesterday = date('Ymd', strtotime('-1 day'));
	}
	
	$todayHR = ''.substr($today, 4, 2).'/'.substr($today, 6, 2).'/'.substr($today, 0, 4).'';
	
	//Initialize Arrays for operational use
	$error_list = array();
	$query_list = array(); 
 	
	//echo $today; echo "|"; echo $yesterday;
	
	//START: Build Aggregate Array
	$query = '
		SELECT asOfDate, fund_id
		FROM '.$_SESSION['fund_aggregate_table'].'
		WHERE asOfDate=:asOfDate 
		GROUP BY fund_id
		ORDER BY fund_id
	';
	
	try{
		$rsAgg = $mLink->prepare($query);
		$aValues = array(
			':asOfDate' 	=> $yesterday
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsAgg->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	while($agg = $rsAgg->fetch(PDO::FETCH_ASSOC)){
		$aAggregate[$agg['fund_id']] = $agg['asOfDate'];	
	}
	
		//echo '<pre>';print_r($aAggregate);echo '</pre>';
	//END: Build Aggregate Array
	
	
	//START: Build AlphaBeta Array
	$query = '
		SELECT asOfDate, fund_id
		FROM '.$_SESSION['fund_alphabeta_table'].'
		WHERE asOfDate=:asOfDate
		GROUP BY fund_id
		ORDER BY fund_id
	';
	
	try{
		$rsAlpha = $mLink->prepare($query);
		$aValues = array(
			':asOfDate' 	=> $yesterday
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsAlpha->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	while($alpha = $rsAlpha->fetch(PDO::FETCH_ASSOC)){
		$aAlphaBeta[$alpha['fund_id']] = $alpha['asOfDate'];
	}
	
		//echo '<pre>';print_r($aAlphaBeta);echo '</pre>';
	
	//END: Build AlphaBeta Array
	
	
	//START: Build LivePrice Array
	$query = '
		SELECT timestamp, fund_id
		FROM '.$_SESSION['fund_liveprice_table'].'
		ORDER BY fund_id ASC
	';
	
	try{
		$rsLivePrice = $mLink->prepare($query);
		$aValues = array(
			':fund_id'		=> $fundID
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsLivePrice->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	while($live = $rsLivePrice->fetch(PDO::FETCH_ASSOC)){
		
		$livePrice = date('Ymd', $live['timestamp']);
		
		if($livePrice == $today){
			$aLivePrice[$live['fund_id']] = $livePrice;
		}
	}
	
		//echo '<pre>';print_r($aLivePrice);echo '</pre>';
	
	//END: Build LivePrice Array
	
	
	//START: Check priceRun table for most recent data
	$query = '
		SELECT date, fund_id
		FROM '.$_SESSION['fund_pricing_table'].'
		WHERE date=:date
		GROUP BY fund_id
		ORDER BY fund_id
	';
	
	try{
		$rsPriceRun = $mLink->prepare($query);
		$aValues = array(
			':date' 	=> $yesterday
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsPriceRun->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	echo $error;
	
	while($price = $rsPriceRun->fetch(PDO::FETCH_ASSOC)){
		
		$aPriceRun[$price['fund_id']] = $price['date'];
		
	}
		//echo '<pre>';	print_r($aPriceRun);echo '</pre>';
	//END: Check priceRun table for most recent data
	
	
	//START: Check positionStratification table for most recent data
	$query = '
		SELECT timestamp, fund_id
		FROM '.$_SESSION['fund_stratification_basic_table'].'
		GROUP BY fund_id
		ORDER BY fund_id ASC
		
	';
	
	try{
		$rsStrat = $mLink->prepare($query);
		$aValues = array(
			//':date' 	=> $yesterday
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsStrat->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	echo $error;
	
	while($strat = $rsStrat->fetch(PDO::FETCH_ASSOC)){
		
		$stratDate = date('Ymd', $strat['timestamp']);
		
		if($stratDate == $today){
			$aStratBasic[$strat['fund_id']] = $stratDate;
		}
		
	}
		//echo '<pre>';	print_r($aStratBasic);	echo '</pre>';
	//END: Check positionStratification table for most recent data
	
	//START: Check stylePositionStratification table for most recent data
	$query = '
		SELECT timestamp, fund_id
		FROM '.$_SESSION['fund_stratification_style_table'].'
		GROUP BY fund_id
		ORDER BY fund_id ASC
		
	';
	
	try{
		$rsStratStyle = $mLink->prepare($query);
		$aValues = array(
			//':date' 	=> $yesterday
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsStratStyle->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	echo $error;
	
	while($stratStyle = $rsStratStyle->fetch(PDO::FETCH_ASSOC)){
		
		$stratStyleDate = date('Ymd', $stratStyle['timestamp']);
		
		if($stratStyleDate == $today){
			$aStratStyle[$stratStyle['fund_id']] = $stratStyleDate;
		}
		
	}
		//echo '<pre>';	print_r($aStratBasic);	echo '</pre>';
	//END: Check stylePositionStratification table for most recent data
	
	//START: Check sectorPositionStratification table for most recent data
	$query = '
		SELECT timestamp, fund_id
		FROM '.$_SESSION['fund_stratification_sector_table'].'
		GROUP BY fund_id
		ORDER BY fund_id ASC
		
	';
	
	try{
		$rsStratSector = $mLink->prepare($query);
		$aValues = array(
			//':date' 	=> $yesterday
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsStratSector->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	echo $error;
	
	while($stratSector = $rsStratSector->fetch(PDO::FETCH_ASSOC)){
		
		$stratSectorDate = date('Ymd', $stratSector['timestamp']);
		
		if($stratSectorDate == $today){
			$aStratSector[$stratSector['fund_id']] = $stratSectorDate;
		}
		
	}
		//echo '<pre>';	print_r($aStratBasic);	echo '</pre>';
	//END: Check stylePositionStratification table for most recent data
	 
	if($masterMember != ''){
		//Get only master member
		$query = '
			SELECT *
			FROM '.$_SESSION['members_table'].'
			WHERE active=:active AND member_id=:member_id
		';
		
		try{
			$rsMembers = $mLink->prepare($query);
			$aValues = array(
				':active' 		=> '1',
				':member_id'	=> $masterMember
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsMembers->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}else{
		//Get all active member IDs
		$query = '
			SELECT *
			FROM '.$_SESSION['members_table'].'
			WHERE active=:active
			ORDER BY member_id ASC
		';
		
		try{
			$rsMembers = $mLink->prepare($query);
			$aValues = array(
				':active' 	=> '1'
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsMembers->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}
	$aMembers = array();
	
	//loop through results and assign values to array
	while($member = $rsMembers->fetch(PDO::FETCH_ASSOC)){
		$memberID 	= $member['member_id'];
		$username 	= $member['username'];
		$firstName	= $member['name_first'];
		$lastName	= $member['name_last'];
		
		$aMembers[] = array(
			'member_id'	=> $memberID,
			'username'	=> $username,
			'firstName'	=> $firstName,
			'lastName'	=> $lastName
		);
		
	}
	
	//START: add members funds to the array
	foreach($aMembers as $key => $aMember){
	
		$memberID = $aMember['member_id'];
		
		if($isFundActive == 'on'){
			$activeQuery = "AND active='1'";	
		}else{
			$activeQuery = '';	
		}
		
//		//Get all funds for member
		$query = "
			SELECT fund_id, fund_symbol
			FROM ".$_SESSION['fund_table']."
			WHERE member_id=:member_id ".$activeQuery."
			ORDER BY seq_id ASC
		";
		
		try{
			$rsFunds = $mLink->prepare($query);
			$aValues = array(
				':member_id' 	=> $memberID
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsFunds->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$aMembers[$key]['funds'] = array();
		
		while($fund = $rsFunds->fetch(PDO::FETCH_ASSOC)){
			
			$aMembers[$key]['funds'][$fund['fund_id']] = $fund['fund_symbol'];
		
		} // end while loop $fund
		
	} // End foreach $aMembers
	//END: add members funds to the array
	
	//START: Loop through members and check against table arrays
	foreach($aMembers as $key => $aMember){
		
		//Set array variables
		$username 	= $aMember['username'];
		$aFunds		= $aMember['funds'];
		$firstName	= $aMember['firstName'];
		$lastName	= $aMember['lastName'];
		
		foreach($aFunds as $fundID => $fundSymbol){
			
			//START: Check aggregate array to see if fund ID exists
			if(array_key_exists($fundID, $aAggregate)){
				//has record
				$aggregateDate = $aAggregate[$fundID];
			}else{
				$error_list[] = array(
					'fund_id' => $fundID,
					'fund_symbol'	=> $fundSymbol,
					'name_first'	=> $firstName,
					'name_last'		=> $lastName,
					'table'			=> $_SESSION['fund_aggregate_table'],
					'date'			=> $yesterday,
					'query'			=> 'aggregateStatistics|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$yesterday.'',
					'api'			=> 'aggregateStatistics',
					'order'			=> '1'
				);	
				$query_list[] = 'aggregateStatistics|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$yesterday.'';
			}
			//END: Check aggregate array to see if fund ID exists
			
			
			//START: Check alphaBeta array to see if fund ID exists
			if(array_key_exists($fundID, $aAlphaBeta)){
				//has record
				$alphaBetaDate = $aAlphaBeta[$fundID];
			}else{
				$error_list[] = array(
					'fund_id' => $fundID,
					'fund_symbol'	=> $fundSymbol,
					'name_first'	=> $firstName,
					'name_last'		=> $lastName,
					'table'			=> $_SESSION['fund_alphabeta_table'],
					'date'			=> $yesterday,
					'query'			=> 'alphaBetaStatistics|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$yesterday.'',
					'api'			=> 'alphaBetaStatistics',
					'order'			=> '2'
				);	
				
				$query_list[] = 'alphaBetaStatistics|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$yesterday.'';
			}
			//END: Check alphaBeta array to see if fund ID exists
			
			
			//START: Check LivePrice array to see if fund ID exists
			if(array_key_exists($fundID, $aLivePrice)){
				//has record
				$livePriceDate = $aLivePrice[$fundID];
			}else{
				$error_list[] = array(
					'fund_id' 		=> $fundID,
					'fund_symbol'	=> $fundSymbol,
					'name_first'	=> $firstName,
					'name_last'		=> $lastName,
					'table'			=> $_SESSION['fund_liveprice_table'],
					'date'			=> $today,
					'query'			=> 'livePrice|'.$username.'|'.$fundID.'|'.$fundSymbol.'',
					'api'			=> 'livePrice',
					'order'			=> '3'
					
				);
					
				$query_list[] = 'livePrice|'.$username.'|'.$fundID.'|'.$fundSymbol.'';
			}
			//END: Check LivePrice array to see if fund ID exists
			
			
			//START: Check priceRun array to see if fund ID exists
			if(array_key_exists($fundID, $aPriceRun)){
				//has record
				$priceRunDate = $aPriceRun[$fundID];
			}else{
				//does not have record
				$error_list[] = array(
					'fund_id' 		=> $fundID,
					'fund_symbol'	=> $fundSymbol,
					'name_first'	=> $firstName,
					'name_last'		=> $lastName,
					'table'			=> $_SESSION['fund_pricing_table'],
					'date'			=> $yesterday,
					'query'			=> 'priceRun|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$yesterday.'',
					'api'			=> 'priceRun',
					'order'			=> '4'
				);
				
				/*$query_list[] = array(
					'fund_id' 	=> $fundID,
					'query'		=> 'priceRun|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$yesterday.''
				);*/
				
				$query_list[] = 'priceRun|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$yesterday.'';
			}
			//END: Check priceRun array to see if fund ID exists
			
			
			//START: Check Stratification Basic array to see if fund ID exists
			if(array_key_exists($fundID, $aStratBasic)){
				//has record
				$stratBasicDate = $aStratBasic[$fundID];
			}else{
				//does not have record
				$error_list[] = array(
					'fund_id' 		=> $fundID,
					'fund_symbol'	=> $fundSymbol,
					'name_first'	=> $firstName,
					'name_last'		=> $lastName,
					'table'			=> $_SESSION['fund_stratification_basic_table'],
					'date'			=> $today,
					'query'			=> 'positionStratification|'.$username.'|'.$fundID.'|'.$fundSymbol.'',
					'api'			=> 'positionStratification',
					'order'			=> '5'
				);
				
				$query_list[] = 'positionStratification|'.$username.'|'.$fundID.'|'.$fundSymbol.'';
			}
			//END: Check Stratification Basic array to see if fund ID exists
			
			//START: Check Stratification Style array to see if fund ID exists
			if(array_key_exists($fundID, $aStratStyle)){
				//has record
				$stratStyleDate = $aStratStyle[$fundID];
			}else{
				//does not have record
				$error_list[] = array(
					'fund_id' 		=> $fundID,
					'fund_symbol'	=> $fundSymbol,
					'name_first'	=> $firstName,
					'name_last'		=> $lastName,
					'table'			=> $_SESSION['fund_stratification_style_table'],
					'date'			=> $today,
					'query'			=> 'stylePositionStratification|'.$username.'|'.$fundID.'|'.$fundSymbol.'',
					'api'			=> 'stylePositionStratification',
					'order'			=> '6'
				);
					
				$query_list[] = 'stylePositionStratification|'.$username.'|'.$fundID.'|'.$fundSymbol.'';
			}
			//END: Check Stratification Style array to see if fund ID exists
			
			//START: Check Stratification Sector array to see if fund ID exists
			if(array_key_exists($fundID, $aStratSector)){
				//has record
				$stratSectorDate = $aStratSector[$fundID];
			}else{
				//does not have record
				$error_list[] = array(
					'fund_id' 		=> $fundID,
					'fund_symbol'	=> $fundSymbol,
					'name_first'	=> $firstName,
					'name_last'		=> $lastName,
					'table'			=> $_SESSION['fund_stratification_sector_table'],
					'date'			=> $today,
					'query'			=> 'sectorPositionStratification|'.$username.'|'.$fundID.'|'.$fundSymbol.'',
					'api'			=> 'sectorPositionStratification',
					'order'			=> '7'
				);
					
				$query_list[] = 'sectorPositionStratification|'.$username.'|'.$fundID.'|'.$fundSymbol.'';
			}
			//END: Check Stratification Style array to see if fund ID exists
						
			$aMembers[$key]['funds'][$fundID] = array(
				'fund_symbol' 	=> $fundSymbol,
				'aggregate'		=> $aggregateDate,
				'alphabeta'		=> $alphaBetaDate,
				'livePrice'		=> $livePriceDate,
				'pricing'		=> $priceRunDate,
				'strat_basic'	=> $stratBasicDate,
				'strat_style'	=> $stratStyleDate,
				'strat_sector'	=> $stratSectorDate
			);
			
			//RESET Vars
			$aggregateDate 		= '';
			$alphaBetaDate 		= '';
			$livePriceDate 		= '';
			$priceRunDate 		= '';
			$stratBasicDate		= '';
			$stratSectorDate	= '';
			$stratStyleDate		= '';
			
		} // end foreach $aFudns
		
	} // end foreach $aMembers
	
	//END: Loop through members and check against table arrays
	
	
	
	//START: Build Display Table
	?>
	<tr>
    	<td colspan="8">Data for: <strong><?php echo $todayHR;?></strong> <?php echo $isFundActive;?></td>
    </tr>
	<?php
	foreach($aMembers as $aMember){
		
		//Set array variables
		$username 	= $aMember['username'];
		$aFunds		= $aMember['funds'];
		$firstName	= $aMember['firstName'];
		$lastName	= $aMember['lastName'];
		$memberID	= $aMember['member_id'];
		$name		= ''.$firstName.' '.$lastName.'';
		
		?>
        <tr>
        	<td style="background-color: #5bc0de;color:#fff;"><strong><?php echo $name; ?> - MemberID: <?php echo $memberID;?></strong><br /><span style="border:1px solid #999999; background: #fff; text-align:left;font-size:14px;display:block;padding:5px;color:#000;">Username: <?php echo get_member($mLink, $memberID, 'username');?></span></td>
            <td style="background-color: #5bc0de;color:#fff;">Aggregate</td>
            <td style="background-color: #5bc0de;color:#fff;">Alpha/Beta</td>
            <td style="background-color: #5bc0de;color:#fff;">Live Price</td>
            <td style="background-color: #5bc0de;color:#fff;">Price Run</td>
            <td style="background-color: #5bc0de;color:#fff;">Strat. Basic</td>
            <td style="background-color: #5bc0de;color:#fff;">Strat. Style</td>
            <td style="background-color: #5bc0de;color:#fff;">Strat. Sector</td>
            
        </tr>
        <?php
		foreach($aFunds as $fundID => $fund){
			$symbol = $fund['fund_symbol'];
			
			$aggregate 		= $fund['aggregate'];
			$alphaBeta		= $fund['alphabeta'];
			$livePrice		= $fund['livePrice'];
			$priceRun 		= $fund['pricing'];
			$stratBasic		= $fund['strat_basic'];
			$stratStyle		= $fund['strat_style'];
			$stratSector	= $fund['strat_sector'];
			
			if($aggregate == ''){
				$aggTable = '<span class="label label-danger">No Record</span> <a href="javascript:void(0);" onclick="runQuery(\'aggregateStatistics|'.$username.'|'.$fundID.'|'.$symbol.'|'.$yesterday.'\', \''.$fundID.'-1\')" class="btn btn-default btn-xs"><i class="icon-download"></i> Get Record</a>';	
			}else{
				$aggTable = '<span class="label label-success">Up To Date</span>';
			}
			
			if($alphaBeta == ''){
				$alphaTable = '<span class="label label-danger">No Record</span> <a href="javascript:void(0);" onclick="runQuery(\'alphaBetaStatistics|'.$username.'|'.$fundID.'|'.$symbol.'|'.$yesterday.'\', \''.$fundID.'-2\')" class="btn btn-default btn-xs"><i class="icon-download"></i> Get Record</a>';	
			}else{
				$alphaTable = '<span class="label label-success">Up To Date</span>';
			}
			
			if($livePrice == ''){
				$livePriceTable = '<span class="label label-danger">No Record</span> <a href="javascript:void(0);" onclick="runQuery(\'livePrice|'.$username.'|'.$fundID.'|'.$symbol.'\', \''.$fundID.'-3\')" class="btn btn-default btn-xs"><i class="icon-download"></i> Get Record</a>';	
			}else{
				$livePriceTable = '<span class="label label-success">Up To Date</span>';
			}
			
			if($priceRun == ''){
				$priceRunTable = '<span class="label label-danger">No Record</span> <a href="javascript:void(0);" onclick="runQuery(\'priceRun|'.$username.'|'.$fundID.'|'.$symbol.'|'.$yesterday.'\', \''.$fundID.'-4\')" class="btn btn-default btn-xs"><i class="icon-download"></i> Get Record</a>';	
			}else{
				$priceRunTable = '<span class="label label-success">Up To Date</span>';
			}
			
			if($stratBasic == ''){
				$stratBasicTable = '<span class="label label-danger">No Record</span> <a href="javascript:void(0);" onclick="runQuery(\'positionStratification|'.$username.'|'.$fundID.'|'.$symbol.'\', \''.$fundID.'-5\')" class="btn btn-default btn-xs"><i class="icon-download"></i> Get Record</a>';	
			}else{
				$stratBasicTable = '<span class="label label-success">Up To Date</span>';
			}
			
			if($stratStyle == ''){
				$stratStyleTable = '<span class="label label-danger">No Record</span> <a href="javascript:void(0);" onclick="runQuery(\'stylePositionStratification|'.$username.'|'.$fundID.'|'.$symbol.'\', \''.$fundID.'-6\')" class="btn btn-default btn-xs"><i class="icon-download"></i> Get Record</a>';	
			}else{
				$stratStyleTable = '<span class="label label-success">Up To Date</span>';
			}
			
			if($stratSector == ''){
				$stratSectorTable = '<span class="label label-danger">No Record</span> <a href="javascript:void(0);" onclick="runQuery(\'sectorPositionStratification|'.$username.'|'.$fundID.'|'.$symbol.'\', \''.$fundID.'-7\')" class="btn btn-default btn-xs"><i class="icon-download"></i> Get Record</a>';	
			}else{
				$stratSectorTable = '<span class="label label-success">Up To Date</span>';
			}
		?>
        <tr>
        	<td style="vertical-align:middle;"><?php echo $symbol;?>: <?php echo $fundID;?></td>
        	<td style="vertical-align:middle;" id="<?php echo $fundID;?>-1"><?php echo $aggTable; ?></td>
            <td style="vertical-align:middle;" id="<?php echo $fundID;?>-2"><?php echo $alphaTable; ?></td>
            <td style="vertical-align:middle;" id="<?php echo $fundID;?>-3"><?php echo $livePriceTable; ?></td>
            <td style="vertical-align:middle;" id="<?php echo $fundID;?>-4"><?php echo $priceRunTable; ?></td>
            <td style="vertical-align:middle;" id="<?php echo $fundID;?>-5"><?php echo $stratBasicTable; ?></td>
            <td style="vertical-align:middle;" id="<?php echo $fundID;?>-6"><?php echo $stratStyleTable; ?></td>
            <td style="vertical-align:middle;" id="<?php echo $fundID;?>-7"><?php echo $stratSectorTable; ?></td>
        </tr>
        <?php
		}
	}
	//END: Build Display Table
	?>
    <input type="hidden" value="<?php echo count($query_list);?>" id="query-count" />
    
    <?php /*?><tr style="border-top:10px solid #F90;">
    	<td colspan="6">Query List (<?php echo count($query_list);?>):  <pre><?php print_r($query_list);?></pre></td>
    </tr>
    
    <tr style="border-top:10px solid #F90;">
    	<td colspan="6">Members Array (<?php echo count($aMembers);?>): <pre><?php print_r($aMembers);?></pre></td>
    </tr><?php */?>
    
    <form action="" method="post" class="run-queries">
    	<input type="text" value="<?php echo $masterMember;?>" name="member"/>
		<input type="hidden" value="<?php echo $testDate;?>" name="test" />
    	<input type="hidden" name="queries" value="<?php echo implode("~", $query_list);?>" />
    </form>
	<?php
	//echo '<h2>Number of Active Members: '.count($aMembers).'</h2>';
	
	//show missing records
	/*echo '<h3>Missing Records: '.count($error_list).'</h3>';
	
	usort($error_list, function($a, $b) {
		return $a['order'] - $b['order'];
	});
	
	echo '<ol>';
	foreach($error_list as $aError){
		echo '<li style="border-bottom: 1px solid #000;padding: 5px 0px;"><strong>'.$aError['api'].'</strong><br /><strong>Fund</strong>: '.$aError['fund_id'].' - '.$aError['fund_symbol'].' : '.$aError['name_first'].' '.$aError['name_last'].'<br /><strong>Record</strong>: No "<strong>'.$aError['table'].'</strong>" record found for <strong>'.$aError['date'].'</strong><br /><strong>Query</strong>: '.$aError['query'].'</li>';	
	}
	echo '</ol>';*/
	
	/*echo '<pre>';
	print_r($aMembers);
	//print_r($aPriceRun);
	echo '</pre>';*/
	
}

if($process == "2"){
	$query = $_REQUEST['query'];
	
	$aQuery = explode("|", $query);
	
	//Get Vars from query array
	$api 		= $aQuery[0];
	$username 	= $aQuery[1];
	$fundID		= $aQuery[2];
	$fundSymbol	= $aQuery[3];
	$date		= $aQuery[4];
$activeOnly = $aQuery[5];

	//If there is no date part of the query, assume today
	if($date == ""){
		$date = date('Ymd');
	}

	switch($api){
		case 'aggregateStatistics'			: $dbQuery = 1; $dbTable = $_SESSION['fund_aggregate_table']; $whereClause = 'fund_id=:fund_id AND asOfDate=:date';break;
		case 'alphaBetaStatistics'			: $dbQuery = 1; $dbTable = $_SESSION['fund_alphabeta_table']; $whereClause = 'fund_id=:fund_id AND asOfDate=:date';break;
		case 'livePrice'					: $dbQuery = 2; $dbTable = $_SESSION['fund_liveprice_table']; $whereClause = 'fund_id=:fund_id';break;
		case 'priceRun'						: $dbQuery = 1; $dbTable = $_SESSION['fund_pricing_table']; $whereClasuse = 'fund_id=:fund_id AND date=:date';break;
		case 'positionStratification'		: $dbQuery = 2; $dbTable = $_SESSION['fund_stratification_basic_table']; $whereClause = 'fund_id=:fund_id';break;
		case 'stylePositionStratification'	: $dbQuery = 2; $dbTable = $_SESSION['fund_stratification_style_table']; $whereClause = 'fund_id=:fund_id';break;
		case 'sectorPositionStratification'	: $dbQuery = 2; $dbTable = $_SESSION['fund_stratification_sector_table']; $whereClause = 'fund_id=:fund_id';break;
	}

	//Execute Query
	//$port = '22000';
	include ('../../includes/data-query-legacy.php');
	
	

	/*echo "<pre>";
	print_r($aQuery);
	echo "</pre>";*/
	
	//Start function to check to see if EXTERNAL API has finished
	function get_results($mLink, $dbTable, $whereClause, $dbQuery, $fundID, $date, $timeout){
		
		//Increment timeout counter by one
		$timeout = $timeout + 1;
		
		//Query DB to see if there are results for the provided date
		if($dbQuery == 1){
			$query = '
				SELECT *
				FROM '.$dbTable.'
				WHERE '.$whereClause.'
				ORDER BY timestamp DESC
				LIMIT 1
			';
			
			try{
				$rsGetData = $mLink->prepare($query);
				$aValues = array(
					':fund_id' 	=> $fundID,
					':date'		=> $date
					
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetData->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
		
			$rowCount = $rsGetData->rowCount();
			
		}elseif($dbQuery == 2){
			$query = '
				SELECT *
				FROM '.$dbTable.'
				WHERE '.$whereClause.'
				ORDER BY timestamp DESC
				LIMIT 1
			';
			try{
				$rsGetData = $mLink->prepare($query);
				$aValues = array(
					':fund_id' 	=> $fundID
					
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetData->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
		
			$time = $rsGetData->fetch(PDO::FETCH_ASSOC);
			
			$recordDate = date('Ymd', $time['timestamp']);
			
			if($recordDate == $date){
				$rowCount = "1";
			}else{
				$rowCount = "0";	
			}
		}
		
		//If the rowCount is zero, loop through the function until it gets a result or the timeout is reached.		
		if($rowCount == "0" && $timeout < 60){
			sleep(1);
			$results .= get_results($mLink, $dbTable, $whereClause, $dbQuery, $fundID, $date, $timeout);
		}else{
			$results .= $rowCount;
		}
		
		return $results;
		
	}
	
	//Set starting time out to zero
	$timeout = 0;
	
	// Run Loop function to see if Details have been written to Database
	$results = get_results($mLink, $dbTable, $whereClause, $dbQuery, $fundID, $date, $timeout);
	
	if($results != "0"){
		echo '<span class="label label-success">Up To Date</span> <span class="label label-info">'.$port.'</span>';	
	}else{
		switch($api){
			case 'aggregateStatistics'		: $queryBtn = '<span class="label label-danger">No Record</span> <a href="javascript:void(0);" onclick="runQuery(\''.$query.'\', \''.$fundID.'-1\')" class="btn btn-default btn-xs"><i class="icon-download"></i> Get Record</a> <span class="label label-warning">TO</span> <span class="label label-info">'.$port.'</span>';break;
			case 'alphaBetaStatistics'		: $queryBtn = '<span class="label label-danger">No Record</span> <a href="javascript:void(0);" onclick="runQuery(\''.$query.'\', \''.$fundID.'-2\')" class="btn btn-default btn-xs"><i class="icon-download"></i> Get Record</a> <span class="label label-warning">TO</span> <span class="label label-info">'.$port.'</span>';break;
			case 'livePrice'				: $queryBtn = '<span class="label label-danger">No Record</span> <a href="javascript:void(0);" onclick="runQuery(\''.$query.'\', \''.$fundID.'-3\')" class="btn btn-default btn-xs"><i class="icon-download"></i> Get Record</a> <span class="label label-warning">TO</span> <span class="label label-info">'.$port.'</span>';break;
			case 'priceRun'					: $queryBtn = '<span class="label label-danger">No Record</span> <a href="javascript:void(0);" onclick="runQuery(\''.$query.'\', \''.$fundID.'-4\')" class="btn btn-default btn-xs"><i class="icon-download"></i> Get Record</a> <span class="label label-warning">TO</span> <span class="label label-info">'.$port.'</span>';break;
			case 'positionStratification'	: $queryBtn = '<span class="label label-danger">No Record</span> <a href="javascript:void(0);" onclick="runQuery(\''.$query.'\', \''.$fundID.'-5\')" class="btn btn-default btn-xs"><i class="icon-download"></i> Get Record</a> <span class="label label-warning">TO</span> <span class="label label-info">'.$port.'</span>';break;
			case 'stylePositionStratification'	: $queryBtn = '<span class="label label-danger">No Record</span> <a href="javascript:void(0);" onclick="runQuery(\''.$query.'\', \''.$fundID.'-6\')" class="btn btn-default btn-xs"><i class="icon-download"></i> Get Record</a> <span class="label label-warning">TO</span> <span class="label label-info">'.$port.'</span>';break;
			case 'sectorPositionStratification'	: $queryBtn = '<span class="label label-danger">No Record</span> <a href="javascript:void(0);" onclick="runQuery(\''.$query.'\', \''.$fundID.'-7\')" class="btn btn-default btn-xs"><i class="icon-download"></i> Get Record</a> <span class="label label-warning">TO</span> <span class="label label-info">'.$port.'</span>';break;	
		}
		echo $queryBtn;	
	}
}


if($process == "3"){
	
	$aQueries = explode('~', $_REQUEST['queries']);
	
	foreach($aQueries as $query){
		include ('../../includes/data-query-legacy.php');
		//sleep(2);	
	}
	
	
	$masterMember = $_REQUEST['member'];
	
	$testDate = $_REQUEST['test'];
	
	if($testDate != ""){
		$today = $testDate;
		$yesterday = date('Ymd', strtotime('-1 day', strtotime($today)));
	}else{
		$today = date('Ymd');
 		$yesterday = date('Ymd', strtotime('-1 day'));
	}
	
	$todayHR = ''.substr($today, 4, 2).'/'.substr($today, 6, 2).'/'.substr($today, 0, 4).'';
	
	//Initialize Arrays for operational use
	$error_list = array();
	$query_list = array(); 
 	
	//echo $today; echo "|"; echo $yesterday;
	
	//START: Build Aggregate Array
	$query = '
		SELECT asOfDate, fund_id
		FROM '.$_SESSION['fund_aggregate_table'].'
		WHERE asOfDate=:asOfDate 
		GROUP BY fund_id
		ORDER BY fund_id
	';
	
	try{
		$rsAgg = $mLink->prepare($query);
		$aValues = array(
			':asOfDate' 	=> $yesterday
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsAgg->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	while($agg = $rsAgg->fetch(PDO::FETCH_ASSOC)){
		$aAggregate[$agg['fund_id']] = $agg['asOfDate'];	
	}
	
		//echo '<pre>';print_r($aAggregate);echo '</pre>';
	//END: Build Aggregate Array
	
	
	//START: Build AlphaBeta Array
	$query = '
		SELECT asOfDate, fund_id
		FROM '.$_SESSION['fund_alphabeta_table'].'
		WHERE asOfDate=:asOfDate
		GROUP BY fund_id
		ORDER BY fund_id
	';
	
	try{
		$rsAlpha = $mLink->prepare($query);
		$aValues = array(
			':asOfDate' 	=> $yesterday
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsAlpha->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	while($alpha = $rsAlpha->fetch(PDO::FETCH_ASSOC)){
		$aAlphaBeta[$alpha['fund_id']] = $alpha['asOfDate'];
	}
	
		//echo '<pre>';print_r($aAlphaBeta);echo '</pre>';
	
	//END: Build AlphaBeta Array
	
	
	//START: Build LivePrice Array
	$query = '
		SELECT timestamp, fund_id
		FROM '.$_SESSION['fund_liveprice_table'].'
		ORDER BY fund_id ASC
	';
	
	try{
		$rsLivePrice = $mLink->prepare($query);
		$aValues = array(
			':fund_id'		=> $fundID
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsLivePrice->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	while($live = $rsLivePrice->fetch(PDO::FETCH_ASSOC)){
		
		$livePrice = date('Ymd', $live['timestamp']);
		
		if($livePrice == $today){
			$aLivePrice[$live['fund_id']] = $livePrice;
		}
	}
	
		//echo '<pre>';print_r($aLivePrice);echo '</pre>';
	
	//END: Build LivePrice Array
	
	
	//START: Check priceRun table for most recent data
	$query = '
		SELECT date, fund_id
		FROM '.$_SESSION['fund_pricing_table'].'
		WHERE date=:date
		GROUP BY fund_id
		ORDER BY fund_id
	';
	
	try{
		$rsPriceRun = $mLink->prepare($query);
		$aValues = array(
			':date' 	=> $yesterday
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsPriceRun->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	echo $error;
	
	while($price = $rsPriceRun->fetch(PDO::FETCH_ASSOC)){
		
		$aPriceRun[$price['fund_id']] = $price['date'];
		
	}
		//echo '<pre>';	print_r($aPriceRun);echo '</pre>';
	//END: Check priceRun table for most recent data
	
	
	//START: Check positionStratification table for most recent data
	$query = '
		SELECT timestamp, fund_id
		FROM '.$_SESSION['fund_stratification_basic_table'].'
		GROUP BY fund_id
		ORDER BY fund_id ASC
		
	';
	
	try{
		$rsStrat = $mLink->prepare($query);
		$aValues = array(
			//':date' 	=> $yesterday
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsStrat->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	echo $error;
	
	while($strat = $rsStrat->fetch(PDO::FETCH_ASSOC)){
		
		$stratDate = date('Ymd', $strat['timestamp']);
		
		if($stratDate == $today){
			$aStratBasic[$strat['fund_id']] = $stratDate;
		}
		
	}
		//echo '<pre>';	print_r($aStratBasic);	echo '</pre>';
	//END: Check positionStratification table for most recent data
	
	//START: Check stylePositionStratification table for most recent data
	$query = '
		SELECT timestamp, fund_id
		FROM '.$_SESSION['fund_stratification_style_table'].'
		GROUP BY fund_id
		ORDER BY fund_id ASC
		
	';
	
	try{
		$rsStratStyle = $mLink->prepare($query);
		$aValues = array(
			//':date' 	=> $yesterday
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsStratStyle->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	echo $error;
	
	while($stratStyle = $rsStratStyle->fetch(PDO::FETCH_ASSOC)){
		
		$stratStyleDate = date('Ymd', $stratStyle['timestamp']);
		
		if($stratStyleDate == $today){
			$aStratStyle[$stratStyle['fund_id']] = $stratStyleDate;
		}
		
	}
		//echo '<pre>';	print_r($aStratBasic);	echo '</pre>';
	//END: Check stylePositionStratification table for most recent data
	
	//START: Check sectorPositionStratification table for most recent data
	$query = '
		SELECT timestamp, fund_id
		FROM '.$_SESSION['fund_stratification_sector_table'].'
		GROUP BY fund_id
		ORDER BY fund_id ASC
		
	';
	
	try{
		$rsStratSector = $mLink->prepare($query);
		$aValues = array(
			//':date' 	=> $yesterday
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsStratSector->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	echo $error;
	
	while($stratSector = $rsStratSector->fetch(PDO::FETCH_ASSOC)){
		
		$stratSectorDate = date('Ymd', $stratSector['timestamp']);
		
		if($stratSectorDate == $today){
			$aStratSector[$stratSector['fund_id']] = $stratSectorDate;
		}
		
	}
		//echo '<pre>';	print_r($aStratBasic);	echo '</pre>';
	//END: Check stylePositionStratification table for most recent data
	 
	if($masterMember != ''){
		//Get only master member
		$query = '
			SELECT *
			FROM '.$_SESSION['members_table'].'
			WHERE active=:active AND member_id=:member_id
		';
		
		try{
			$rsMembers = $mLink->prepare($query);
			$aValues = array(
				':active' 		=> '1',
				':member_id'	=> $masterMember
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsMembers->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}else{
		//Get all active member IDs
		$query = '
			SELECT *
			FROM '.$_SESSION['members_table'].'
			WHERE active=:active
			ORDER BY member_id ASC
		';
		
		try{
			$rsMembers = $mLink->prepare($query);
			$aValues = array(
				':active' 	=> '1'
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsMembers->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}
	$aMembers = array();
	
	//loop through results and assign values to array
	while($member = $rsMembers->fetch(PDO::FETCH_ASSOC)){
		$memberID 	= $member['member_id'];
		$username 	= $member['username'];
		$firstName	= $member['name_first'];
		$lastName	= $member['name_last'];
		
		$aMembers[] = array(
			'member_id'	=> $memberID,
			'username'	=> $username,
			'firstName'	=> $firstName,
			'lastName'	=> $lastName
		);
		
	}
	
	//START: add members funds to the array
	foreach($aMembers as $key => $aMember){
	
		$memberID = $aMember['member_id'];
		
		//Get all funds for member
		$query = '
			SELECT fund_id, fund_symbol
			FROM '.$_SESSION['fund_table'].'
			WHERE member_id=:member_id
			ORDER BY fund_id ASC
		';
		
		try{
			$rsFunds = $mLink->prepare($query);
			$aValues = array(
				':member_id' 	=> $memberID
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsFunds->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$aMembers[$key]['funds'] = array();
		
		while($fund = $rsFunds->fetch(PDO::FETCH_ASSOC)){
			
			$aMembers[$key]['funds'][$fund['fund_id']] = $fund['fund_symbol'];
		
		} // end while loop $fund
		
	} // End foreach $aMembers
	//END: add members funds to the array
	
	//START: Loop through members and check against table arrays
	foreach($aMembers as $key => $aMember){
		
		//Set array variables
		$username 	= $aMember['username'];
		$aFunds		= $aMember['funds'];
		$firstName	= $aMember['firstName'];
		$lastName	= $aMember['lastName'];
		
		foreach($aFunds as $fundID => $fundSymbol){
			
			//START: Check aggregate array to see if fund ID exists
			if(array_key_exists($fundID, $aAggregate)){
				//has record
				$aggregateDate = $aAggregate[$fundID];
			}else{
				$error_list[] = array(
					'fund_id' => $fundID,
					'fund_symbol'	=> $fundSymbol,
					'name_first'	=> $firstName,
					'name_last'		=> $lastName,
					'table'			=> $_SESSION['fund_aggregate_table'],
					'date'			=> $yesterday,
					'query'			=> 'aggregateStatistics|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$yesterday.'',
					'api'			=> 'aggregateStatistics',
					'order'			=> '1'
				);	
				$query_list[] = 'aggregateStatistics|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$yesterday.'';
			}
			//END: Check aggregate array to see if fund ID exists
			
			
			//START: Check alphaBeta array to see if fund ID exists
			if(array_key_exists($fundID, $aAlphaBeta)){
				//has record
				$alphaBetaDate = $aAlphaBeta[$fundID];
			}else{
				$error_list[] = array(
					'fund_id' => $fundID,
					'fund_symbol'	=> $fundSymbol,
					'name_first'	=> $firstName,
					'name_last'		=> $lastName,
					'table'			=> $_SESSION['fund_alphabeta_table'],
					'date'			=> $yesterday,
					'query'			=> 'alphaBetaStatistics|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$yesterday.'',
					'api'			=> 'alphaBetaStatistics',
					'order'			=> '2'
				);	
				
				$query_list[] = 'alphaBetaStatistics|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$yesterday.'';
			}
			//END: Check alphaBeta array to see if fund ID exists
			
			
			//START: Check LivePrice array to see if fund ID exists
			if(array_key_exists($fundID, $aLivePrice)){
				//has record
				$livePriceDate = $aLivePrice[$fundID];
			}else{
				$error_list[] = array(
					'fund_id' 		=> $fundID,
					'fund_symbol'	=> $fundSymbol,
					'name_first'	=> $firstName,
					'name_last'		=> $lastName,
					'table'			=> $_SESSION['fund_liveprice_table'],
					'date'			=> $today,
					'query'			=> 'livePrice|'.$username.'|'.$fundID.'|'.$fundSymbol.'',
					'api'			=> 'livePrice',
					'order'			=> '3'
					
				);
					
				$query_list[] = 'livePrice|'.$username.'|'.$fundID.'|'.$fundSymbol.'';
			}
			//END: Check LivePrice array to see if fund ID exists
			
			
			//START: Check priceRun array to see if fund ID exists
			if(array_key_exists($fundID, $aPriceRun)){
				//has record
				$priceRunDate = $aPriceRun[$fundID];
			}else{
				//does not have record
				$error_list[] = array(
					'fund_id' 		=> $fundID,
					'fund_symbol'	=> $fundSymbol,
					'name_first'	=> $firstName,
					'name_last'		=> $lastName,
					'table'			=> $_SESSION['fund_pricing_table'],
					'date'			=> $yesterday,
					'query'			=> 'priceRun|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$yesterday.'',
					'api'			=> 'priceRun',
					'order'			=> '4'
				);
				
				/*$query_list[] = array(
					'fund_id' 	=> $fundID,
					'query'		=> 'priceRun|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$yesterday.''
				);*/
				
				$query_list[] = 'priceRun|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$yesterday.'';
			}
			//END: Check priceRun array to see if fund ID exists
			
			
			//START: Check Stratification Basic array to see if fund ID exists
			if(array_key_exists($fundID, $aStratBasic)){
				//has record
				$stratBasicDate = $aStratBasic[$fundID];
			}else{
				//does not have record
				$error_list[] = array(
					'fund_id' 		=> $fundID,
					'fund_symbol'	=> $fundSymbol,
					'name_first'	=> $firstName,
					'name_last'		=> $lastName,
					'table'			=> $_SESSION['fund_stratification_basic_table'],
					'date'			=> $today,
					'query'			=> 'positionStratification|'.$username.'|'.$fundID.'|'.$fundSymbol.'',
					'api'			=> 'positionStratification',
					'order'			=> '5'
				);
				
				$query_list[] = 'positionStratification|'.$username.'|'.$fundID.'|'.$fundSymbol.'';
			}
			//END: Check Stratification Basic array to see if fund ID exists
			
			//START: Check Stratification Style array to see if fund ID exists
			if(array_key_exists($fundID, $aStratStyle)){
				//has record
				$stratStyleDate = $aStratStyle[$fundID];
			}else{
				//does not have record
				$error_list[] = array(
					'fund_id' 		=> $fundID,
					'fund_symbol'	=> $fundSymbol,
					'name_first'	=> $firstName,
					'name_last'		=> $lastName,
					'table'			=> $_SESSION['fund_stratification_style_table'],
					'date'			=> $today,
					'query'			=> 'stylePositionStratification|'.$username.'|'.$fundID.'|'.$fundSymbol.'',
					'api'			=> 'stylePositionStratification',
					'order'			=> '6'
				);
					
				$query_list[] = 'stylePositionStratification|'.$username.'|'.$fundID.'|'.$fundSymbol.'';
			}
			//END: Check Stratification Style array to see if fund ID exists
			
			//START: Check Stratification Sector array to see if fund ID exists
			if(array_key_exists($fundID, $aStratSector)){
				//has record
				$stratSectorDate = $aStratSector[$fundID];
			}else{
				//does not have record
				$error_list[] = array(
					'fund_id' 		=> $fundID,
					'fund_symbol'	=> $fundSymbol,
					'name_first'	=> $firstName,
					'name_last'		=> $lastName,
					'table'			=> $_SESSION['fund_stratification_sector_table'],
					'date'			=> $today,
					'query'			=> 'sectorPositionStratification|'.$username.'|'.$fundID.'|'.$fundSymbol.'',
					'api'			=> 'sectorPositionStratification',
					'order'			=> '7'
				);
					
				$query_list[] = 'sectorPositionStratification|'.$username.'|'.$fundID.'|'.$fundSymbol.'';
			}
			//END: Check Stratification Style array to see if fund ID exists
						
			$aMembers[$key]['funds'][$fundID] = array(
				'fund_symbol' 	=> $fundSymbol,
				'aggregate'		=> $aggregateDate,
				'alphabeta'		=> $alphaBetaDate,
				'livePrice'		=> $livePriceDate,
				'pricing'		=> $priceRunDate,
				'strat_basic'	=> $stratBasicDate,
				'strat_style'	=> $stratStyleDate,
				'strat_sector'	=> $stratSectorDate
			);
			
			//RESET Vars
			$aggregateDate 		= '';
			$alphaBetaDate 		= '';
			$livePriceDate 		= '';
			$priceRunDate 		= '';
			$stratBasicDate		= '';
			$stratSectorDate	= '';
			$stratStyleDate		= '';
			
		} // end foreach $aFudns
		
	} // end foreach $aMembers
	
	//END: Loop through members and check against table arrays
	
	
	
	//START: Build Display Table
	?>
	<tr>
    	<td colspan="8">Data for: <strong><?php echo $todayHR;?></strong></td>
    </tr>
	<?php
	foreach($aMembers as $aMember){
		
		//Set array variables
		$username 	= $aMember['username'];
		$aFunds		= $aMember['funds'];
		$firstName	= $aMember['firstName'];
		$lastName	= $aMember['lastName'];
		$memberID	= $aMember['member_id'];
		$name		= ''.$firstName.' '.$lastName.'';
		
		?>
        <tr>
        	<td style="background-color: #5bc0de;color:#fff;"><strong><?php echo $name; ?> - MemberID: <?php echo $memberID;?></strong></td>
            <td style="background-color: #5bc0de;color:#fff;">Aggregate</td>
            <td style="background-color: #5bc0de;color:#fff;">Alpha/Beta</td>
            <td style="background-color: #5bc0de;color:#fff;">Live Price</td>
            <td style="background-color: #5bc0de;color:#fff;">Price Run</td>
            <td style="background-color: #5bc0de;color:#fff;">Strat. Basic</td>
            <td style="background-color: #5bc0de;color:#fff;">Strat. Style</td>
            <td style="background-color: #5bc0de;color:#fff;">Strat. Sector</td>
            
        </tr>
        <?php
		foreach($aFunds as $fundID => $fund){
			$symbol = $fund['fund_symbol'];
			
			$aggregate 		= $fund['aggregate'];
			$alphaBeta		= $fund['alphabeta'];
			$livePrice		= $fund['livePrice'];
			$priceRun 		= $fund['pricing'];
			$stratBasic		= $fund['strat_basic'];
			$stratStyle		= $fund['strat_style'];
			$stratSector	= $fund['strat_sector'];
			
			if($aggregate == ''){
				$aggTable = '<span class="label label-danger">No Record</span> <a href="javascript:void(0);" onclick="runQuery(\'aggregateStatistics|'.$username.'|'.$fundID.'|'.$symbol.'|'.$yesterday.'\', \''.$fundID.'-1\')" class="btn btn-default btn-xs"><i class="icon-download"></i> Get Record</a>';	
			}else{
				$aggTable = '<span class="label label-success">Up To Date</span>';
			}
			
			if($alphaBeta == ''){
				$alphaTable = '<span class="label label-danger">No Record</span> <a href="javascript:void(0);" onclick="runQuery(\'alphaBetaStatistics|'.$username.'|'.$fundID.'|'.$symbol.'|'.$yesterday.'\', \''.$fundID.'-2\')" class="btn btn-default btn-xs"><i class="icon-download"></i> Get Record</a>';	
			}else{
				$alphaTable = '<span class="label label-success">Up To Date</span>';
			}
			
			if($livePrice == ''){
				$livePriceTable = '<span class="label label-danger">No Record</span> <a href="javascript:void(0);" onclick="runQuery(\'livePrice|'.$username.'|'.$fundID.'|'.$symbol.'\', \''.$fundID.'-3\')" class="btn btn-default btn-xs"><i class="icon-download"></i> Get Record</a>';	
			}else{
				$livePriceTable = '<span class="label label-success">Up To Date</span>';
			}
			
			if($priceRun == ''){
				$priceRunTable = '<span class="label label-danger">No Record</span> <a href="javascript:void(0);" onclick="runQuery(\'priceRun|'.$username.'|'.$fundID.'|'.$symbol.'|'.$yesterday.'\', \''.$fundID.'-4\')" class="btn btn-default btn-xs"><i class="icon-download"></i> Get Record</a>';	
			}else{
				$priceRunTable = '<span class="label label-success">Up To Date</span>';
			}
			
			if($stratBasic == ''){
				$stratBasicTable = '<span class="label label-danger">No Record</span> <a href="javascript:void(0);" onclick="runQuery(\'positionStratification|'.$username.'|'.$fundID.'|'.$symbol.'\', \''.$fundID.'-5\')" class="btn btn-default btn-xs"><i class="icon-download"></i> Get Record</a>';	
			}else{
				$stratBasicTable = '<span class="label label-success">Up To Date</span>';
			}
			
			if($stratStyle == ''){
				$stratStyleTable = '<span class="label label-danger">No Record</span> <a href="javascript:void(0);" onclick="runQuery(\'stylePositionStratification|'.$username.'|'.$fundID.'|'.$symbol.'\', \''.$fundID.'-6\')" class="btn btn-default btn-xs"><i class="icon-download"></i> Get Record</a>';	
			}else{
				$stratStyleTable = '<span class="label label-success">Up To Date</span>';
			}
			
			if($stratSector == ''){
				$stratSectorTable = '<span class="label label-danger">No Record</span> <a href="javascript:void(0);" onclick="runQuery(\'sectorPositionStratification|'.$username.'|'.$fundID.'|'.$symbol.'\', \''.$fundID.'-7\')" class="btn btn-default btn-xs"><i class="icon-download"></i> Get Record</a>';	
			}else{
				$stratSectorTable = '<span class="label label-success">Up To Date</span>';
			}
		?>
        <tr>
        	<td style="vertical-align:middle;"><?php echo $symbol;?>: <?php echo $fundID;?></td>
        	<td style="vertical-align:middle;" id="<?php echo $fundID;?>-1"><?php echo $aggTable; ?></td>
            <td style="vertical-align:middle;" id="<?php echo $fundID;?>-2"><?php echo $alphaTable; ?></td>
            <td style="vertical-align:middle;" id="<?php echo $fundID;?>-3"><?php echo $livePriceTable; ?></td>
            <td style="vertical-align:middle;" id="<?php echo $fundID;?>-4"><?php echo $priceRunTable; ?></td>
            <td style="vertical-align:middle;" id="<?php echo $fundID;?>-5"><?php echo $stratBasicTable; ?></td>
            <td style="vertical-align:middle;" id="<?php echo $fundID;?>-6"><?php echo $stratStyleTable; ?></td>
            <td style="vertical-align:middle;" id="<?php echo $fundID;?>-7"><?php echo $stratSectorTable; ?></td>
        </tr>
        <?php
		}
	}
	//END: Build Display Table
	?>
    <input type="hidden" value="<?php echo count($query_list);?>" id="query-count" />
    
    <?php /*?><tr style="border-top:10px solid #F90;">
    	<td colspan="6">Query List (<?php echo count($query_list);?>):  <pre><?php print_r($query_list);?></pre></td>
    </tr>
    
    <tr style="border-top:10px solid #F90;">
    	<td colspan="6">Members Array (<?php echo count($aMembers);?>): <pre><?php print_r($aMembers);?></pre></td>
    </tr><?php */?>
    
    <form action="" method="post" class="run-queries">
    	<input type="text" value="<?php echo $masterMember;?>" name="member"/>
		<input type="hidden" value="<?php echo $testDate;?>" name="test" />
    	<input type="hidden" name="queries" value="<?php echo implode("~", $query_list);?>" />
    </form>
	<?php
	//echo '<h2>Number of Active Members: '.count($aMembers).'</h2>';
	
	//show missing records
	/*echo '<h3>Missing Records: '.count($error_list).'</h3>';
	
	usort($error_list, function($a, $b) {
		return $a['order'] - $b['order'];
	});
	
	echo '<ol>';
	foreach($error_list as $aError){
		echo '<li style="border-bottom: 1px solid #000;padding: 5px 0px;"><strong>'.$aError['api'].'</strong><br /><strong>Fund</strong>: '.$aError['fund_id'].' - '.$aError['fund_symbol'].' : '.$aError['name_first'].' '.$aError['name_last'].'<br /><strong>Record</strong>: No "<strong>'.$aError['table'].'</strong>" record found for <strong>'.$aError['date'].'</strong><br /><strong>Query</strong>: '.$aError['query'].'</li>';	
	}
	echo '</ol>';*/
	
	/*echo '<pre>';
	print_r($aMembers);
	//print_r($aPriceRun);
	echo '</pre>';*/
	
}

?>