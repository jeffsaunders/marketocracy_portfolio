<?php
//Calculate Stratification for member
function run_strat($mLink, $fLink, $fundID){
	
	$effectiveDate = get_funds($mLink, $fundID, 'unixIncept', 'agg');
	
	$processStart = time();
	
	//Get all tickets for a fund
	$query = '
			SELECT * 
			FROM '.$_SESSION['fund_trades_table'].' 
			WHERE fund_id=:fund_id 
			ORDER BY unix_closed ASC
			';
	try{
			$rsPos = $mLink->prepare($query);
			$aValues = array(
					':fund_id'	=> $fundID
					//':sort'		=> $setSort
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsPos->execute($aValues);
	}
	catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}

	//Initialize Arrays
	$aTrades 	= array();
	$aHoldings 	= array();
	$aShares	= array();
	$aCAs		= array();

	//loop through result
	while($trade = $rsPos->fetch(PDO::FETCH_ASSOC)){
		
		$aTrades[$trade['stockSymbol']][strtolower($trade['buyOrSell'])][] = array(
				'fund_id'		=> $trade['fund_id'],
				'company_id'	=> $trade['company_id'],
				'unix_opened'	=> $trade['unix_opened'],
				'unix_closed'	=> $trade['unix_closed'],
				'closed'		=> date('m d, Y', $trade['unix_closed']),
				'shares'		=> $trade['shares'],
				'sharesOrdered'	=> $trade['sharesOrdered'],
				'sharesFilled'	=> $trade['sharesFilled'],
				'price'			=> $trade['price'],
				'ticketStatus'	=> $trade['ticketStatus'],
				'createdByCA'	=> $trade['createdByCA'],
				'buyOrSell'		=> $trade['buyOrSell']

		);

		//Set a var name to create dynamic variable to store ongoing share count for each trade date
		$shareTotal = $trade['stockSymbol'];


		if($trade['buyOrSell'] == 'buy'){
				//if the type is buy add shares to share total
				$$shareTotal = $$shareTotal + $trade['sharesFilled'];	
		}else{
				//if the type is sell, subtract shares from share total
				$$shareTotal = $$shareTotal - $trade['sharesFilled'];	
		}
		
		/*if($shareTotal == 'BLUE'){
			
			$showTrades[] = array(
				'fund_id'		=> $trade['fund_id'],
				'company_id'	=> $trade['company_id'],
				'unix_opened'	=> $trade['unix_opened'],
				'unix_closed'	=> $trade['unix_closed'],
				'closed'		=> date('m d, Y', $trade['unix_closed']),
				'shares'		=> $trade['shares'],
				'sharesOrdered'	=> $trade['sharesOrdered'],
				'sharesFilled'	=> $trade['sharesFilled'],
				'price'			=> $trade['price'],
				'ticketStatus'	=> $trade['ticketStatus'],
				'createdByCA'	=> $trade['createdByCA'],
				'buyOrSell'		=> $trade['buyOrSell']

			);
			
		
		}*/
		
		//stuff values into array for comparing later.
		$aShares[$shareTotal][$trade['unix_closed']] = $$shareTotal;
				
	}

	//Get array of current holdings price and today values
	$aStockList = array();
	foreach($aTrades as $stockSymbol=>$aTradeType){
			$aStockList[] = "'".$stockSymbol."'";	
	}
	
	
	
	$stockList = implode(',',$aStockList);

	$query = "
			SELECT Name AS companyName, Symbol AS symbol, Last AS CurrentPrice, PercentChangeFromPreviousClose AS chang
			FROM `stock_feed`
			WHERE `Symbol` IN (".$stockList.")
	";
	try{
			$rsSymbols = $fLink->prepare($query);
			$aValues = array();
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsSymbols->execute($aValues);
	}
	catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$aStockInfo = array();
	while($foo = $rsSymbols->fetch(PDO::FETCH_ASSOC)){
			$aStockInfo[$foo['symbol']] = array(
					'stockPrice'	=> $foo['CurrentPrice'],
					'today'			=> $foo['chang']
			);
	}
	
	//Loop through and figure out current holdings
	foreach($aTrades as $stockSymbol=>$aTradeType){

		$aBuys 	= $aTradeType['buy'];
		$aSells	= $aTradeType['sell'];

		//Set some vars
		$totalBuys 		= 0;
		$totalSells		= 0;
		$priceTotalBuys		= 0;
		$priceTotalSells	= 0;

		//Loop through buy tickets
		foreach($aBuys as $uid=>$aTicket){

				$totalBuys 		= $totalBuys + $aTicket['sharesFilled'];
				$priceTotalBuys	= $priceTotalBuys + ($aTicket['price']*$aTicket['sharesFilled']);	
		}

		//Loop through sell tickets
		foreach($aSells as $uid=>$aTicket){

				$totalSells 		= $totalSells + $aTicket['sharesFilled'];
				$priceTotalSells	= $priceTotalSells + ($aTicket['price']*$aTicket['sharesFilled']);	
		}

		#START CA YIELD CALCULATIONS
		/*$query = "
				SELECT *
				FROM ".$_SESSION['changeactions_table']."
				WHERE symbol=:symbol AND effectiveDate_timestamp>:fund_effectiveDate
		";
		try{
				$rsCA = $mLink->prepare($query);
				$aValues = array(
						':symbol'				=> $stockSymbol,
						':fund_effectiveDate'	=> $effectiveDate
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsCA->execute($aValues);
		}
		catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		//echo $preparedQuery;

		while($ca = $rsCA->fetch(PDO::FETCH_ASSOC)){

				$aCAs[$stockSymbol][$ca['action']][] = array(
						'date'				=> date('Y/m/d', $ca['effectiveDate_timestamp']),
						'effectiveDate'		=> $ca['effectiveDate_timestamp'],
						'gross'				=> $ca['gross']
				);

		}*/
		$query = "
				SELECT *
				FROM ".$_SESSION['fund_positions_details_table']."
				WHERE fund_id=:fund_id AND stockSymbol=:stockSymbol
				ORDER BY timestamp DESC
				LIMIT 1
		";
		try{
				$rsPosInfo = $mLink->prepare($query);
				$aValues = array(
						':fund_id'	=> $fundID,
						':stockSymbol'	=> $stockSymbol
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsPosInfo->execute($aValues);
		}
		catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$posInfo = $rsPosInfo->fetch(PDO::FETCH_ASSOC);

		//echo $error;

		#CA info
		$caYield 	= $posInfo['caYield'];
		$caCost		= $posInfo['caCost'];
		$recentCAYield	= $posInfo['recentCAYield'];
		$recentCACost	= $posInfo['recentCACost'];

		#return Info
		$recentReturn	= $posInfo['recentReturn']; //current
		$totalReturn	= $posInfo['totalReturn']; //incept

		#sector style info
		$sector         = $posInfo['sectorBaseName'];
		$style          = $posInfo['style'];
		$subIndustry    = $posInfo['subIndustryBaseName'];
		//END CA YIELD CALCULATIONS

		//Run Calculations
		$currentShares 	= $totalBuys - $totalSells;
		$currentValue	= $currentShares * $aStockInfo[$stockSymbol]['stockPrice'];

		$aLivePrice 	= get_funds($mLink, $fundID, 'lp', 'livePrice');
		$totalValue		= $aLivePrice['totalValue'];
		$stockValue         = $aLivePrice['stockValue'];

		$weight			= ($currentValue / $totalValue);
		$gains			= $currentValue + $priceTotalSells + $caYield - $priceTotalBuys - $caCost;

		#create an array of current holdings
		$aHoldings[$stockSymbol] = array(
				'price'         => $aStockInfo[$stockSymbol]['stockPrice'],
				'shares'        => $currentShares,
				'value'         => $currentValue,
				'weight'        => $weight,
				'gains'         => $gains,								
				'today'         => $aStockInfo[$stockSymbol]['today'],
				'recentReturn'  => $recentReturn,
				'totalReturn'   => $totalReturn,
				'sector'        => $sector,
				'subIndustry'   => $subIndustry,
				'style'         => $style
		);
		
		$query = "
			SELECT fund_id, stockSymbol
			FROM ".$_SESSION['fund_stratification_basic_table']."
			WHERE fund_id=:fund_id AND stockSymbol=:stockSymbol
		";
		try{
			$rsGetStrat = $mLink->prepare($query);
			$aValues = array(
					':fund_id'		=> $fundID,
					':stockSymbol'	=> $stockSymbol
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetStrat->execute($aValues);
		}
		catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$stratCnt = $rsGetStrat->rowCount();
		
		if($stratCnt > 0){
			//update
			$query = "
				UPDATE ".$_SESSION['fund_stratification_basic_table']."
				SET timestamp=UNIX_TIMESTAMP(), currentPrice=:price, totalShares=:shares, currentValue=:value, fundRatio=:weight, gains=:gains, todayReturn=:today, totalReturn=:totalReturn, sector=:sector, style=:style, recentReturn=:recentReturn
				WHERE fund_id=:fund_id AND stockSymbol=:stockSymbol
			";
			try{
				$rsUpdateStrat = $mLink->prepare($query);
				$aValues = array(
						':fund_id'		=> $fundID,
						':stockSymbol'	=> $stockSymbol,
						':price'		=> $aStockInfo[$stockSymbol]['stockPrice'],
						':shares'		=> $currentShares,
						':value'		=> $currentValue,
						':weight'		=> $weight,
						':gains'		=> $gains,
						':today'		=> $aStockInfo[$stockSymbol]['today'],
						':totalReturn'	=> $totalReturn,
						':recentReturn'	=> $recentReturn,
						':sector'		=> $sector,
						':style'		=> $style
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdateStrat->execute($aValues);
			}
			catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}	
			$aResults[] = $error;
		}else{
			//insert
			$query = "
				INSERT INTO ".$_SESSION['fund_stratification_basic_table']."  (
					fund_id,
					timestamp,
					stockSymbol,
					currentPrice,
					totalShares,
					currentValue,
					fundRatio,
					gains,
					todayReturn,
					totalReturn,
					recentReturn,
					sector,
					style
				)VALUES(
					:fund_id,
					UNIX_TIMESTAMP(),
					:stockSymbol,
					:price,
					:shares,
					:value,
					:weight,
					:gains,
					:today,
					:totalReturn,
					:recentReturn,
					:sector,
					:style
				)
			";
			try{
				$rsInsertStrat = $mLink->prepare($query);
				$aValues = array(
						':fund_id'		=> $fundID,
						':stockSymbol'	=> $stockSymbol,
						':price'		=> $aStockInfo[$stockSymbol]['stockPrice'],
						':shares'		=> $currentShares,
						':value'		=> $currentValue,
						':weight'		=> $weight,
						':gains'		=> $gains,
						':today'		=> $aStockInfo[$stockSymbol]['today'],
						':totalReturn'	=> $totalReturn,
						':recentReturn'	=> $recentReturn,
						':sector'		=> $sector,
						':style'		=> $style
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsInsertStrat->execute($aValues);
			}
			catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$aResults[] = $error;
		}//End update or insert check
		
	}//end for each trade
	
	$aSectors   = array();
	$aSyles     = array();
	
	//Group positions into sectors and styles
	foreach($aHoldings as $stockSymbol=>$aStockInfo2){
		
		$aSectors[$aStockInfo2['sector']]['positions'][$stockSymbol] = $aStockInfo2;
		$aStyles[$aStockInfo2['style']]['positions'][$stockSymbol] = $aStockInfo2;
		
	}
	
	$sectorCnt = 0;
	//get sector and style totals
	foreach($aSectors as $sector=>$aSectorItems){
		//For each sector level
		
		$sectorCnt++;
		
		//print_r($aSectorItems);
		//reset vars
		$sectorPosCnt       = 0;
		$sectorValue        = 0;
		$sectorToday        = 0;
		$aSectorToday       = array();
		
		//only loop through positions
		foreach($aSectors[$sector]['positions'] as $symbol=>$aStockInfo3){
			if($aStockInfo3['shares'] > 0){
				$sectorPosCnt++;
				$sectorValue = $sectorValue + $aStockInfo3['value'];
				$aSectorToday[$symbol]    = $aStockInfo3['today'];
			}
			
		}
		/*$aSectors[$sector]['sectorValue']       = $sectorValue;
		$aSectors[$sector]['totalValue']        = $totalValue;
		$aSectors[$sector]['stockValue']        = $stockValue;*/
		$aSectors[$sector]['allocation']        = ($sectorValue / $stockValue) *100;
		$aSectors[$sector]['value']				= $sectorValue;
		$aSectors[$sector]['today']             = (array_sum($aSectorToday) / $sectorPosCnt);
		$aSectors[$sector]['cnt']               = $sectorPosCnt;
		$aSectors[$sector]['todayArray']        = $aSectorToday;
		$aSectors[$sector]['sectorID']			= $fundID.'-'.$sectorCnt;
	}
	
	$styleCnt = 0;
	foreach($aStyles as $style=>$aStyleItems){
		//For each sector level
		
		$styleCnt++;
		
		//print_r($aSectorItems);
		//reset vars
		$stylePosCnt       = 0;
		$styleValue        = 0;
		$styleToday        = 0;
		$aStyleToday       = array();
		
		//only loop through positions
		foreach($aStyles[$style]['positions'] as $symbol=>$aStockInfo4){
			if($aStockInfo4['shares'] > 0){
				$stylePosCnt++;
				$styleValue = $styleValue + $aStockInfo4['value'];
				$aStyleToday[$symbol]    = $aStockInfo4['today'];
			}
			
		}
		/*$aStyles[$style]['sectorValue']       = $styleValue;
		$aStyles[$style]['totalValue']        	= $totalValue;
		$aStyles[$style]['stockValue']        	= $stockValue;*/
		$aStyles[$style]['allocation']        	= ($styleValue / $stockValue) *100;
		$aSytles[$style]['value']				= $styleValue;
		$aStyles[$style]['today']             	= (array_sum($aSectorToday) / $stylePosCnt);
		$aStyles[$style]['cnt']              	= $stylePosCnt;
		$aStyles[$style]['todayArray']        	= $aStyleToday;
		$aStyles[$style]['styleID']				= $fundID.'-'.$styleCnt;
	}
	
	//Insert Sector Strat Records
	//remove existing records
	$query = "
		DELETE FROM ".$_SESSION['fund_stratification_sector_table']."
		WHERE fund_id=:fund_id
	";
	try{
		$rsDeleteStrat = $mLink->prepare($query);
		$aValues = array(
				':fund_id'		=> $fundID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsDeleteStrat->execute($aValues);
	}
	catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$aResults[] = $error;
	//remove existing records
	$query = "
		DELETE FROM ".$_SESSION['fund_stratification_sector_positions_table']."
		WHERE fund_id=:fund_id
	";
	try{
		$rsDeleteStrat = $mLink->prepare($query);
		$aValues = array(
				':fund_id'		=> $fundID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsDeleteStrat->execute($aValues);
	}
	catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$aResults[] = $error;
	//insert new sector strats
	foreach($aSectors as $sector=>$aSecInfo){
		
		$aPositions			= $aSecInfo['positions'];
		$secAllocation		= $aSecInfo['allocation'];
		$secToday			= $aSecInfo['today'];
		$secValue			= $aSecInfo['value'];
		$sectorID			= $aSecInfo['sectorID'];
		
		//Insert Sector Info
		$query = "
			INSERT INTO ".$_SESSION['fund_stratification_sector_table']." (
				fund_id,
				sector_id,
				timestamp,
				sectorName,
				sectorAllocation,
				sectorTodayReturn,
				sectorValue
			)VALUES(
				:fund_id,
				:sector_id,
				UNIX_TIMESTAMP(),
				:sectorName,
				:sectorAllocation,
				:sectorTodayReturn,
				:value
			)
		";
		try{
			$rsInsertSector = $mLink->prepare($query);
			$aValues = array(
					':fund_id'				=> $fundID,
					':sector_id'			=> $sectorID,
					':sectorName'			=> $sector,
					':sectorAllocation'		=> $secAllocation,
					':sectorTodayReturn'	=> $secToday,
					':value'				=> $secValue
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsInsertSector->execute($aValues);
		}
		catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$aResults[] = $error;
		
		//loop through each position and insert them into the db
		foreach($aPositions as $stockSymbol=>$aStock){
			
			$price			= $aStock['price'];
			$shares			= $aStock['shares'];
			$value			= $aStock['value'];
			$weight			= $aStock['weight'];
			$gains			= $aStock['gains'];
			$today			= $aStock['today'];
			$recentReturn	= $aStock['recentReturn'];
			$totalReturn	= $aStock['totalReturn'];
			
			$query = "
				INSERT INTO ".$_SESSION['fund_stratification_sector_positions_table']." (
					fund_id,
					sector_id,
					timestamp,
					stockSymbol,
					currentPrice,
					totalShares,
					currentValue,
					fundRatio,
					gains,
					todayReturn,
					totalReturn,
					recentReturn
				)VALUES(
					:fund_id,
					:sector_id,
					UNIX_TIMESTAMP(),
					:stockSymbol,
					:price,
					:shares,
					:value,
					:weight,
					:gains,
					:today,
					:totalReturn,
					:recentReturn
				)
			";
			try{
				$rsInsertSectorPos = $mLink->prepare($query);
				$aValues = array(
						':fund_id'			=> $fundID,
						':sector_id'		=> $sectorID,
						':stockSymbol'		=> $stockSymbol,
						':price'			=> $price,
						':shares'			=> $shares,
						':value'			=> $value,
						':weight'			=> $weight,
						':gains'			=> $gains,
						':today'			=> $today,
						':totalReturn'		=> $totalReturn,
						':recentReturn'		=> $recentReturn
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsInsertSectorPos->execute($aValues);
			}
			catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$aResults[] = $error;
		}//end foreach position
	}//end foreach sector
	
	
	//INSERT STYLE RECORDS
	//remove existing records
	$query = "
		DELETE FROM ".$_SESSION['fund_stratification_style_table']."
		WHERE fund_id=:fund_id
	";
	try{
		$rsDeleteStrat = $mLink->prepare($query);
		$aValues = array(
				':fund_id'		=> $fundID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsDeleteStrat->execute($aValues);
	}
	catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$aResults[] = $error;
	//remove existing records
	$query = "
		DELETE FROM ".$_SESSION['fund_stratification_style_positions_table']."
		WHERE fund_id=:fund_id
	";
	try{
		$rsDeleteStrat = $mLink->prepare($query);
		$aValues = array(
				':fund_id'		=> $fundID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsDeleteStrat->execute($aValues);
	}
	catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$aResults[] = $error;
	//insert new style strats
	foreach($aStyles as $style=>$aStyleInfo){
		
		$aPositions			= $aStyleInfo['positions'];
		$styleAllocation	= $aStyleInfo['allocation'];
		$styleToday			= $aStyleInfo['today'];
		$styleID			= $aStyleInfo['styleID'];
		$styleValue			= $aStyleInfo['value'];
		
		//Insert Style Info
		$query = "
			INSERT INTO ".$_SESSION['fund_stratification_style_table']." (
				fund_id,
				style_id,
				timestamp,
				styleName,
				styleAllocation,
				styleTodayReturn,
				styleValue
			)VALUES(
				:fund_id,
				:style_id,
				UNIX_TIMESTAMP(),
				:styleName,
				:styleAllocation,
				:styleTodayReturn,
				:styleValue
			)
		";
		try{
			$rsInsertStyle = $mLink->prepare($query);
			$aValues = array(
					':fund_id'				=> $fundID,
					':style_id'				=> $styleID,
					':styleName'			=> $style,
					':styleAllocation'		=> $styleAllocation,
					':styleTodayReturn'		=> $styleToday,
					':styleValue'			=> $styleValue
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsInsertStyle->execute($aValues);
		}
		catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$aResults[] = $error;
		
		//loop through each position and insert them into the db
		foreach($aPositions as $stockSymbol=>$aStock){
			
			$price			= $aStock['price'];
			$shares			= $aStock['shares'];
			$value			= $aStock['value'];
			$weight			= $aStock['weight'];
			$gains			= $aStock['gains'];
			$today			= $aStock['today'];
			$recentReturn	= $aStock['recentReturn'];
			$totalReturn	= $aStock['totalReturn'];
			
			$query = "
				INSERT INTO ".$_SESSION['fund_stratification_style_positions_table']." (
					fund_id,
					style_id,
					timestamp,
					stockSymbol,
					currentPrice,
					totalShares,
					currentValue,
					fundRatio,
					gains,
					todayReturn,
					totalReturn,
					recentReturn
				)VALUES(
					:fund_id,
					:style_id,
					UNIX_TIMESTAMP(),
					:stockSymbol,
					:price,
					:shares,
					:value,
					:weight,
					:gains,
					:today,
					:totalReturn,
					:recentReturn
				)
			";
			try{
				$rsInsertStylePos = $mLink->prepare($query);
				$aValues = array(
						':fund_id'			=> $fundID,
						':style_id'			=> $styleID,
						':stockSymbol'		=> $stockSymbol,
						':price'			=> $price,
						':shares'			=> $shares,
						':value'			=> $value,
						':weight'			=> $weight,
						':gains'			=> $gains,
						':today'			=> $today,
						':totalReturn'		=> $totalReturn,
						':recentReturn'		=> $recentReturn
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsInsertStylePos->execute($aValues);
			}
			catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$aResults[] = $error;
		}//end foreach position
	}//end foreach style
	
	
	
	
	$processEnd = time();
	
	
	
	
	
	return($aResults);
	
}
