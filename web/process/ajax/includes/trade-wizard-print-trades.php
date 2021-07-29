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
			//END STEP 1 | Get Members Fund Symbols
			
			//START STEP 3 | Get Current Shares
			
			// Get Fund ID
			
			//Check to see if fund ID is set
			/*if($fundFront == "" || $fundFront == "all"){
			
				$query = "
					SELECT mf.fund_symbol, mf.fund_id, lp.cashValue, lp.totalValue
					FROM ".$_SESSION['fund_table']." AS mf
					INNER JOIN ".$_SESSION['fund_liveprice_table']." as lp ON lp.fund_id=mf.fund_id
					WHERE mf.member_id=:member_id
					ORDER BY mf.seq_id ASC
				";
				
				try{
					$rsGetFunds2 = $mLink->prepare($query);
					$aValues = array(
						':member_id' 	=> $_SESSION['member_id']
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsGetFunds2->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				//multi fund here
				
				$fund2 = $rsGetFunds2->fetch(PDO::FETCH_ASSOC);
				$totalValue = $fund2['totalValue'];
				$cashValue = $fund2['cashValue'];
				
				$fundID = $fund2['fund_id'];
				$fundSymbol = $fund2['fund_symbol'];
			
			}else{
				
				$query = "
					SELECT mf.fund_symbol, mf.fund_id, lp.cashValue, lp.totalValue
					FROM ".$_SESSION['fund_table']." AS mf
					INNER JOIN ".$_SESSION['fund_liveprice_table']." as lp ON lp.fund_id=mf.fund_id
					WHERE mf.member_id=:member_id AND mf.fund_id=:fund_id
					ORDER BY mf.seq_id ASC
				";
				
				try{
					$rsGetFunds2 = $mLink->prepare($query);
					$aValues = array(
						':member_id' 	=> $_SESSION['member_id'],
						':fund_id'		=> $fundFront
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsGetFunds2->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				$fund2 = $rsGetFunds2->fetch(PDO::FETCH_ASSOC);
				$totalValue = $fund2['totalValue'];
				$cashValue = $fund2['cashValue'];
				
				$fundID = $fundFront;
				$fundSymbol = $fund2['fund_symbol'];
				
			}*/
			
			
			
			// Get Shares
			/*$query = "
				SELECT shares, stockSymbol, unix_date
				FROM ".$_SESSION['fund_positions_table']."
				WHERE fund_id=:fund_id AND stockSymbol=:stock_symbol
				GROUP BY unix_date
				ORDER BY unix_date DESC
				LIMIT 1
			";*/
			
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
			
			if(isset($fixMessage)){
				echo '
					<tr style="background:#F2DEDE !important;color:#B94A48 !important;">
						<td colspan="11">'.$fixMessage.'</td>
					</tr>
				';	
			}
			?>
			
			<tr id="update-row-<?php echo $cnt;?>">
				<td><?php echo $cnt;?></td>
                <td>
					<div class="radio-list">
					   <label>
					   <input type="radio" name="trade-type-<?php echo $cnt;?>[]" value="buy" id="trade-type-buy-<?php echo $cnt;?>" data-title="Buy" <?php echo $setBuy;?> />
					   Buy
					   </label>
					   <label>
					   <input type="radio" name="trade-type-<?php echo $cnt;?>[]" value="sell" id="trade-type-sell-<?php echo $cnt;?>" data-title="Sell" <?php echo $setSell;?> />
					   Sell
					   </label>  
                       
					</div>
				</td>
				<td>
					<select name="fund-<?php echo $cnt;?>" id="fund-symbol-<?php echo $cnt;?>" onchange="changeFund('<?php echo $cnt;?>');">
						<?php
						if($fundFront != "" && $fundFront != "all"){
							$fundSymbolFront = get_funds($mLink, $fundFront, 'fundSymbol');
							echo '<option value="'.$fundFront.'" data-title="'.$fundFront.'" class="fundsymbol">'.$fundSymbolFront.'</option>';	
						}
						
						while($fund = $rsGetFunds->fetch(PDO::FETCH_ASSOC)) {
							if($fund['fund_id'] != $fundFront){
								echo '<option value="'.$fund['fund_id'].'" data-title="'.$fund['fund_symbol'].'" class="fundsymbol">'.$fund['fund_symbol'].'</option>';
							}
						}
						?>
					</select>
				</td>
				<td>
					<input type="text" class="form-control" id="symbol-<?php echo $cnt;?>" name="symbol-<?php echo $cnt;?>" value="<?php echo $symbol;?>" readonly/>
				</td>
				<td><?php echo $companyName;?> <input type="hidden" value="<?php echo $companyName;?>" id="company-name-<?php echo $cnt;?>" /></td>
				<td>
                    $<?php echo $currentPrice;?> <input type="hidden" value="<?php echo $currentPrice;?>" id="current-price-<?php echo $cnt;?>" name="current-price-<?php echo $cnt;?>" /><br />
                    <span class="label label-info"><?php echo $change;?></span> <input type="hidden" value="<?php echo $change;?>" id="change-<?php echo $cnt;?>" name="change-<?php echo $cnt;?>" />
                </td>
				<td><?php if($totalShares < 0){echo '<span class="label label-danger">'.$totalShares.'</span>';}else{ echo $totalShares;}?> <input type="hidden" value="<?php echo $totalShares;?>" name="current-shares-<?php echo $cnt;?>" id="current-shares-<?php echo $cnt;?>"/></td>
				<td><?php echo $currentPercent; ?>%</td>
				<td><span class="label label-success">$<?php echo $currentValue; ?></span><input type="hidden" value="234947.70" id="current-value" /></td>
				<td><input type="text" class="form-control" id="share-qty-<?php echo $cnt;?>" onblur="calculateTotal('<?php echo $cnt;?>','<?php echo $fundSymbol;?>');" name="shares-<?php echo $cnt;?>" value="<?php echo $fixShareCnt;?>" /></td>
				<td style="display:none;"><input type="text" class="form-control" id="pos-size" name="pos-size" value="<?php echo $now;?>" /></td>
				<td>
                	<div class="input-icon">
						<i class="icon-dollar"></i>
                		<input type="text" class="form-control" id="show-share-total-<?php echo $cnt;?>" data-currency="USD" name="show-buy-ammount-<?php echo $cnt; ?>" value="" readonly/>
                    </div>
                    <input type="hidden" class="form-control" id="share-total-<?php echo $cnt;?>" data-currency="USD" name="buy-ammount-<?php echo $cnt;?>" value="" />
                	<input type="hidden" id="<?php echo $fundSymbol; ?>-cash-change-<?php echo $cnt;?>" name="<?php echo $fundSymbol; ?>-cash-change-<?php echo $cnt;?>" value="0" />
                    <input type="hidden" id="<?php echo $fundSymbol; ?>-cash-previous-<?php echo $cnt;?>" name="<?php echo $fundSymbol; ?>-cash-previous-<?php echo $cnt;?>" value="0" />
                </td>
				<td style="display:none;"><input type="text" class="form-control" name="limit-price" value="<?php echo $cashValue;?>"/></td>
			</tr>
			<?php
			
			?>