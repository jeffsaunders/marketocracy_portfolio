			<?php
			$symbol = str_replace("-", ".", $symbol);
			
            $symbolString .= ''.$cnt.'|';
			//START STEP 1 | Get Members Fund Symbols
			$query = " 
				SELECT fund_symbol, fund_id
				FROM ".$_SESSION['fund_table']."
				WHERE member_id=:member_id
				ORDER BY seq_id ASC
			";
			
			try{
				$rsGetFunds = $mLink->prepare($query);
				$aValues = array(
					':member_id' 	=> $_SESSION['member_id']
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetFunds->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			while($fund = $rsGetFunds->fetch(PDO::FETCH_ASSOC)) {
				if($fund['fund_id'] != $fundFront){
					//echo '<option value="'.$fund['fund_id'].'" data-title="'.$fund['fund_symbol'].'" class="fundsymbol">'.$fund['fund_symbol'].'</option>';
					
					$aMemberFunds[$fund['fund_id']] = $fund['fund_symbol'];
				}
			}
			//END STEP 1 | Get Members Fund Symbols
			
			//START STEP 3 | Get Current Shares
			
			// Get Shares
			$query = "
				SELECT totalShares AS shares, stockSymbol
				FROM ".$_SESSION['fund_stratification_basic_table']."
				WHERE fund_id=:fund_id AND stockSymbol=:stock_symbol
			";
			
			try{
				$rsGetStock = $mLink->prepare($query);
				$aValues = array(
					':stock_symbol' 	=> $symbol,
					':fund_id'			=> $fundID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsGetStock->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			$stock = $rsGetStock->fetch(PDO::FETCH_ASSOC);
			
			$shares = $stock['shares'];
			if($shares == ''){
				$shares = 0;	
			}
			//END STEP 3 | Get Current Shares
			
			//START STEP 4 | Check Tickets table to see if there are any pending and closed symbols for today
			$query = "
				SELECT shares, status, openned, closed, action, symbol
				FROM members_fund_tickets
				WHERE fund_id=:fund_id AND symbol=:symbol AND action='sell' AND status='pending' OR fund_id=:fund_id AND symbol=:symbol AND action='sell' AND status='closed' AND closed>(SELECT process_timestamp FROM members_fund_maxdate)
				ORDER BY openned DESC
			";
			
			try{
				$rsTickets = $mLink->prepare($query);
				$aValues = array(
					':symbol' 	=> $symbol,
					':fund_id'	=> $fundID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsTickets->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$oldShares = 0;
			while($ticket = $rsTickets->fetch(PDO::FETCH_ASSOC)){
				$oldShares = $oldShares + $ticket['shares'];
			}
			//END STEP 4 | Check Tickets table to see if there are any pending and closed symbols for today
			
			//START CALCULATIONS
			$totalShares = $shares - $oldShares;
			$currentValue1 = ($totalShares * $currentPrice);
			$currentValue = number_format($currentValue1, 2, '.', ',');
			
			//Get Current Position %
			$currentPercent = round((($currentValue1/$totalValue)*100), 2);
			
			//add label to $change depending on what works? haha < - come back to me later
			if (strpos($change,'+') !== false) {
				$percentChange = '<span class="label label-success">'.$change.'</span>';
			}else{
				$percentChange = '<span class="label label-danger">'.$change.'</span>';
			}
			//END CALCULATIONS
			
			switch($sellType){
				case "both": $setBuy = ""; $setSell = "";break;
				case "buy": $setBuy = "checked"; $setSell = "";break;
				case "sell": $setBuy = ""; $setSell = "checked";break;
				default: $setBuy = ""; $setSell = "";break;
			}
			
			if($totalShares < 0){
				$setBuy = "checked";
				$setSell = "";
				//$fixShareCnt = abs($totalShares);
				
				$fixMessage = '<i class="icon-arrow-down"></i> <strong>This stock is shorted in your fund! Please fix immediately!</strong> <i class="icon-arrow-down"></i>';	
			}
			
			$aPrintRows[$cnt] = array(
				'cnt'				=> $cnt,
				'setBuy'			=> $setBuy,
				'setSell'			=> $setSell,
				'fundFront'			=> $fundFront,
				'memberFunds'		=> $aMemberFunds,
				'symbol'			=> $symbol,
				'companyName'		=> $companyName,
				'currentPrice'		=> $currentPrice,
				'totalShares'		=> $totalShares,
				'currentValue'		=> $currentValue,
				'currentPercent'	=> $currentPercent,
				'fixMessage'		=> $fixMessage
			);
			?>
			
			
            