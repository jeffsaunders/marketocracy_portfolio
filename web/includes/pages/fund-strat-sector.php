<?php
//Pull all sector Stratification Data and place into an array
$query = '
	SELECT * 
	FROM '.$_SESSION['fund_stratification_sector_table'].' 
	WHERE fund_id=:fund_id
	ORDER BY sectorALlocation DESC
	
';
try{
	$rsSectors = $mLink->prepare($query);
	$aValues = array(
		':fund_id'	=> $fundID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsSectors->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

//loop through result
while($sector = $rsSectors->fetch(PDO::FETCH_ASSOC)){
	
	if($sector['sectorAllocation'] > 0){
		$aSectors[$sector['sector_id']] = array(
			//'sector_id'			=> $sector['sector_id'],
			'sectorName'			=> $sector['sectorName'],
			'sectorValue'			=> $sector['sectorValue'],
			'sectorAllocation'		=> $sector['sectorAllocation'],
			'sectorTodayReturn'		=> $sector['sectorTodayReturn'],
			'sectorTotalReturn'		=> $sector['sectorTotalReturn']
			
		);
	}
	
}

$sort = $_REQUEST['sort'];
								
switch($sort){
	case 'symbol'	: 
		$setSort = 'stockSymbol';
		$symbolSort = 'set';
		$symbolColor = 'style="background:#5BC0DE !important;"';
		$sortType='ASC';
	break;
	case 'price'	: 
		$setSort = 'currentPrice';
		$priceSort = 'set';
		$priceColor = 'style="background:#5BC0DE !important;"';
		$sortType='DESC';
		
	break;
	case 'shares'	: 
		$setSort = 'totalShares';
		$sharesSort = 'set';
		$sharesColor = 'style="background:#5BC0DE !important;"';
		$sortType='DESC';
		
	break;
	case 'value'	: $setSort = 'currentValue';$valueSort = 'set';$valueColor = 'style="background:#5BC0DE !important;"';$sortType='DESC';break;
	case 'percent'	: $setSort = 'fundRatio';$percentSort = 'set';$percentColor = 'style="background:#5BC0DE !important;"';$sortType='DESC';break;
	case 'gains'	: $setSort = 'gains';$gainsSort = 'set';$gainsColor = 'style="background:#5BC0DE !important;"';$sortType='DESC';break;
	case 'today'	: $setSort = 'todayReturn';$todaySort = 'set';$todayColor = 'style="background:#5BC0DE !important;"';$sortType='DESC';break;
	case 'incept'	: $setSort = 'totalReturn';$inceptSort = 'set';$inceptColor = 'style="background:#5BC0DE !important;"';$sortType='DESC';break;
	case 'current'	: $setSort = 'recentReturn';$currentSort = 'set';$currentColor = 'style="background:#5BC0DE !important;"';$sortType='DESC';break;
	default			: $setSort = 'recentReturn';$currentSort = 'set';$currentColor = 'style="background:#5BC0DE !important;"';$sortType='DESC';break;
}

$aNoSplitCols = array('price','shares','value','percent','symbol');

$getAll = $_REQUEST['getAll'];
if($getAll == '1'){
	$addGetAll = '&getAll=1';	
}else{
	$addGetAll = '';	
}

//START: loop through each sector and get the positions relating to that sector and stuff in the array
foreach($aSectors as $sectorID => $sector){
	
	//query db and grab all positions for given sectorID
	$query = '
		SELECT * 
		FROM '.$_SESSION['fund_stratification_sector_positions_table'].' 
		WHERE fund_id=:fund_id AND sector_id=:sector_id
		ORDER BY '.$setSort.' '.$sortType.'
	';
	try{
		$rsSectorsPos = $mLink->prepare($query);
		$aValues = array(
			':fund_id'	=> $fundID,
			':sector_id'	=> $sectorID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsSectorsPos->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	//Initialize array
	$asectors[$key]['positions'] = array();
	
	//Start Position Counter, used for sorting later
	$posCnt = 0;
	
	
	
	//loop through and assign position values to the appropriate array
	while($sectorPos = $rsSectorsPos->fetch(PDO::FETCH_ASSOC)){
		
		if($getAll != '1'){
			
			if($sectorPos['totalShares'] != "0"){
			
				$posCnt = $posCnt + 1;
				
				 if($sectorPos['gains'] < 0){
					$gains = '<span class="label label-danger">-$'.number_format(str_replace('-', '', $sectorPos['gains']), 2, '.', ',').'</span>';
				 }else{
					$gains = '$'.number_format($sectorPos['gains'], 2, '.', ',').''; 
				 }
				
/*edit*/			$aSectors[$sectorID]['positions'][$sectorPos['stockSymbol']] = array(
					'stockSymbol' 	=> $sectorPos['stockSymbol'],
					'label'			=> $sectorPos['label'],
					'currentPrice'	=> '$'.number_format($sectorPos['currentPrice'], 2, '.', ',').'',
					'totalShares'	=> $sectorPos['totalShares'],
					'currentValue'	=> '$'.number_format($sectorPos['currentValue'], 2, '.', ',').'',
					'fundRatio'		=> $sectorPos['fundRatio'],
					'gains'			=> $gains,
					'todayReturn'	=> ''.number_format($sectorPos['todayReturn'], 2, '.', '').'%',
					'totalReturn'	=> ''.number_format($sectorPos['totalReturn'], 2, '.', '').'%',
					'currentReturn'	=> ''.number_format($sectorPos['recentReturn'], 2, '.', '').'%',
					'cnt'			=> $posCnt
				);
				
			}// end if(totalShares not equal to zero)
		}else{
			
			$posCnt = $posCnt + 1;
				
			if($sectorPos['gains'] < 0){
				$gains = '<span class="label label-danger">-$'.number_format(str_replace('-', '', $sectorPos['gains']), 2, '.', ',').'</span>';
			}else{
				$gains = '$'.number_format($sectorPos['gains'], 2, '.', ',').''; 
			}
			
			if($sectorPos['totalShares'] == '0'){
				$currentReturn = '0.00';	
			}else{
				$currentReturn = ''.number_format($sectorPos['recentReturn'], 2, '.', '').'%';	
			}
			
/*edit*/		$aSectors[$sectorID]['positions'][$sectorPos['stockSymbol']] = array(
				'stockSymbol' 	=> $sectorPos['stockSymbol'],
				'label'			=> $sectorPos['label'],
				'currentPrice'	=> '$'.number_format($sectorPos['currentPrice'], 2, '.', ',').'',
				'totalShares'	=> $sectorPos['totalShares'],
				'currentValue'	=> '$'.number_format($sectorPos['currentValue'], 2, '.', ',').'',
				'fundRatio'		=> $sectorPos['fundRatio'],
				'gains'			=> $gains,
				'todayReturn'	=> ''.number_format($sectorPos['todayReturn'], 2, '.', '').'%',
				'totalReturn'	=> ''.number_format($sectorPos['totalReturn'], 2, '.', '').'%',
				'currentReturn'	=> $currentReturn,
				'cnt'			=> $posCnt
			);
			
		}// end get all check
		
	}// end while loop
		
}// end foreach $aSytls

//Clean up array (get rid of empties)
foreach($aSectors as $key => $sector){
	if($key == ''){
		unset($aSectors[$key]);	
	}
}
//END: loop through each sector and get the positions relating to that sector and stuff in the array


//START: Create function to print each row within each section
function printRows($aSort){
	
	//Check whether the section Counter is odd or even to determin row shade color
	if(isOdd($aSort['secCnt']) == true){
		// If odd, make shade dark
		$trClass = 'trow-'.$aSort['trColor'].'';
	}else{
		// If even, make shade light
		$trClass = 'trow-light-'.$aSort['trColor'].'';	
	}
	
	//Determin if the row is the first row of the section to dispaly rowspan <td>
	if($aSort['secCnt'] == 1){
		//add rowspan <td>, row is the first of the section
		$addRowSpan = '<td rowspan="'.$aSort['secRows'].'" align="middle" valign="middle" style="vertical-align: middle;"><strong>'.$aSort['section'].'</strong></td>';
		
		//check to see if section is middle or bottom
		if($aSort['section'] == "MIDDLE" || $aSort['section'] == "BOTTOM"){
			//section is middle or bottom, add top border
			$addStyle = 'style="border-top:2px solid #5bc0de;"';	
		}else{
			//section is not middle or bottom, do nothing
			$addStyle = '';	
		}
	}else{
		//row is not the first row of the section, do nothing
		$addRowSpan = '';
		$addStyle = '';	
	}
	
	$label = $aSort['label'];
										
	if($label == '' || $label == NULL){
		$label = 'Add Label';	
	}
	
	//Create var to hold HTML
	$printRow = '
		<tr class="'.$trClass.'" '.$addStyle.'>
			<td><a href="javascript:void(0);" class="btn btn-default btn-xs">'.$aSort['symbol'].'</a></td>
			<td><a href="#global-label" class="btn btn-default btn-xs" data-toggle="modal" onclick="getLabel(\''.$aSort['fund_id'].'\',\''.$aSort['symbol'].'\');">'.$label.'</a></td>
			<td>'.$aSort['price'].'</td>
			<td>'.$aSort['shares'].'</td>
			<td>'.$aSort['value'].'</td>
			<td style="display:none">'.$aSort['ratio'].'</td>
			<td>'.$aSort['gains'].'</td>
			<td>'.$aSort['today'].'</td>
			<td>'.$aSort['return'].'</td>
			<td>'.$aSort['currentReturn'].'</td>
			<td class="center"><a href="#stock-details" class="btn btn-default btn-xs" data-toggle="modal">Details</a></td>
			'.$addRowSpan.'
		</tr>
	';
	
	//Return data out of function
	return $printRow;
			
}
//END: printRows Function

$aColors = graphColors();

?>
         <!-- BEGIN SAMPLE PORTLET CONFIGURATION MODAL FORM-->         
         <div class="modal fade" id="stock-label" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
               <div class="modal-content">
                  <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                     <h4 class="modal-title">Set Label</h4>
                  </div>
                  <div class="modal-body">
                  	  <p>You can use label to enter a very small reminder about this position for use other pages. How you use this is up to you, but one suggested use is a short reminder for why you chose this position for use with the strategy report. By comparing this label to the performance of a position, you can see which strategies work best for you.</p>
                      <p>You can use the notes field for more extensive notes on a position.</p>
                     <form role="form">
                     <div class="form-group">
                     	<label>Position</label>
                        <select class="form-control">
                        	<option>AAL</option>
                            <option>PRAN</option>
                            <option>PEIX</option>
                            <option>NKTR</option>
                            <option>C</option>
                            <option>GCA</option>
                            <option>GLL</option>
                            <option>DSX</option>
                            <option>BHI</option>
                            <option>DTLK</option>
                            <option>RT</option>
                            <option>XLB</option>
                            <option>WFT</option>
                            <option>SZYM</option>
                            <option>PWRD</option>
                        </select>
                     </div>
                     <div class="form-group">
                     	<label>Label</label>
                        <input type="text" class="form-control" placeholder="Set Label">
                     </div>
                     <div class="form-group">
                     	<label>Rationale</label>
                        <textarea class="form-control" row="5"></textarea>
                     </div>
                     </form>
                  </div>
                  <div class="modal-footer">
                     <button type="button" class="btn btn-success">Save</button>
                     <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  </div>
               </div>
               <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
         </div>
         <!-- END SAMPLE PORTLET CONFIGURATION MODAL FORM-->
              
        <!-- BEGIN PAGE CONTENT-->          
        <div class="row">
            <div class="col-md-12">
                
               <?php /*?> <div class="note note-warning">
                    <h4 class="block">Stratification Sector Warning</h4>
                    <p>The current sector allocations are currently incorrect. We are working on fixing this issue as quickly as possible. Please check back later.</p>
                </div><!--note--><?php */?>
                
                <!-- BEGIN FUND INFO -->
                <?php include('includes/portlets/fund-live-info.php');?>
                <!-- END FUND INFO -->
                
                        
                
                <!--START FUND POSITIONS BY SECTORS-->
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption"><i class="icon-bell"></i>Sector Allocation</div><!--caption-->
                        <div class="tools">
                        <a href="" class="collapse"></a>
                        <a href="" class="reload"></a>
                        </div>
                    </div><!--portlet-title-->
                    <div class="portlet-body">
                    	<div class="row">
                        	<div class="col-md-6">
                            	<div id="pos-sector-chart"></div>
                            </div><!--col-md-4-->
                            <div class="col-md-6">
                            	<table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Color</th>
                                            <th>Name</th>
                                            <th>Allocation</th>
                                            <th>Today</th>
                                            <?php /*?><th>Inception Return</th><?php */?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    	<?php
                                        $colorCnt = 0;

                                        foreach($aSectors as $sector){
                                                //SET VARS
                                                $sectorName 		= $sector['sectorName'];
                                                $sectorAllocation	= $sector['sectorAllocation'];
                                                $sectorTodayReturn	= $sector['sectorTodayReturn'];
                                                $sectorTotalReturn	= $sector['sectorTotalReturn'];

                                                //Format Vars
                                                $sectorAllocation	= ''.number_format($sectorAllocation, 2, '.', '').'%';
                                                $sectorTodayReturn	= ''.number_format($sectorTodayReturn, 2, '.', '').'%';
                                                $sectorTotalReturn	= ''.number_format($sectorTotalReturn, 2, '.', '').'%';

                                                // Grab a color from the color array then increment by one until you get to 10 then start over at 0
                                                if($colorCnt >= 10){
                                                        $colorCnt = 0;	
                                                }
                                                $color = $aColors[$colorCnt];
                                                $colorCnt = $colorCnt + 1;

                                                ?>
                                                <tr>
                                                    <td style="background-color:<?php echo $color; ?>;"></td>
                                                    <td><?php echo $sectorName; ?></td>
                                                    <td><?php echo $sectorAllocation; ?></td>
                                                    <td><?php echo $sectorTodayReturn; ?></td>
                                                    <?php /*?><td><?php echo $sectorTotalReturn; ?></td><?php */?>
                                                </tr>
                                                <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div><!--col-md-9-->
                        </div><!--row-->
                        
                        
                       
                    
                    </div><!--end portlet body-->
                </div><!--end portlet-->
                <!--END FUND POSITIONS BY SECTOR-->
                
                 <!--STRATIFICATION MENU-->
                <?php include("includes/nav-stratification.php"); ?>     
                    
                <!-- BEGIN Strat by Sector PORTLET-->
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption"><i class="icon-cogs"></i>Sector Stratification</div>
                        <div class="tools">
                            <a href="javascript:;" class="collapse"></a>
                            <a href="javascript:;" class="reload"></a>
                        </div><!--tools-->
                    </div><!--portlet-title-->
                    
                    <div class="portlet-body flip-scroll">
                    
                    <?php
					
					
					
					//START: Print each SECTOR Section
					foreach($aSectors as $sectorID => $sector){
						
						//SET VARS
						$sectorName 		= $sector['sectorName'];
						$sectorValue		= $sector['sectorValue'];
						$sectorAllocation	= $sector['sectorAllocation'];
						$sectorTodayReturn	= $sector['sectorTodayReturn'];
						$aPositions			= $sector['positions'];
						
						//Format Vars
						$sectorValue 		= '$'.number_format($sectorValue, 2, '.', ',').'';
						$sectorAllocation	= ''.number_format($sectorAllocation, 2, '.', '').'%';
						$sectorTodayReturn	= ''.number_format($sectorTodayReturn, 2, '.', '').'%';
						
						
						//Get postion symbols
						$aBuySell = array();
						foreach($aPositions as $position){
							$aBuySell[] = $position['stockSymbol'];
						}
						$buySell = implode(",", $aBuySell);
												
						//Loop through and print HTML
						?>
						<!--START sector: <?php echo $sectorName;?>-->
						<table class="table table-bordered table-striped table-condensed flip-content strat-style-table">
							<thead class="flip-content">
								<tr>
									<th colspan="11" class="strat-table-header"><?php echo $sectorName;?></th>
								</tr>
								<tr>
									<th colspan="3"><strong>Value:</strong> <?php echo $sectorValue;?></th>
									<th colspan="3"><strong>Allocation:</strong> <?php echo $sectorAllocation;?></th>
									<th colspan="3"><strong>Today:</strong> <?php echo $sectorTodayReturn;?></th>
									<th colspan="2" style="text-align:right;"><a href="?page=02-00-00-001&symbols=<?php echo $buySell;?>&fund=<?php echo $fundID;?>&trade=buy&wiz=buy" class="btn btn-success btn-xs" target="_blank"><i class="icon-arrow-up"></i> Buy</a> <a href="?page=02-00-00-001&symbols=<?php echo $buySell;?>&fund=<?php echo $fundID;?>&trade=sell&wiz=sell" class="btn btn-danger btn-xs" target="_blank"><i class="icon-arrow-down"></i> Sell</a></th>
								</tr>
								<tr>
										<th <?php echo $symbolColor;?>>
                                            <a href="?page=03-01-03-002&fund=<?php echo $fundID;?>&sort=symbol<?php echo $addGetAll;?>" style=" <?php if($symbolSort != 'set'){echo 'color:#000000;';}else{echo 'color:#ffffff !important;';}?>">SYMBOL <i class="icon-sort<?php if($symbolSort == 'set'){echo'-down';}?>"></i></a>
                                        </th>
										<th>LABEL</th>
										<th <?php echo $priceColor;?>>
                                    		<a href="?page=03-01-03-002&fund=<?php echo $fundID;?>&sort=price<?php echo $addGetAll;?>" style=" <?php if($priceSort != 'set'){echo 'color:#000000;';}else{echo 'color:#ffffff !important;';}?>">PRICE <i class="icon-sort<?php if($priceSort == 'set'){echo'-down';}?>"></i></a>
                                    	</th>
										<th <?php echo $sharesColor;?>>
                                    		<a href="?page=03-01-03-002&fund=<?php echo $fundID;?>&sort=shares<?php echo $addGetAll;?>" style=" <?php if($sharesSort != 'set'){echo 'color:#000000;';}else{echo 'color:#ffffff !important;';}?>">SHARES <i class="icon-sort<?php if($sharesSort == 'set'){echo'-down';}?>"></i></a>
                                    	</th>
										<th <?php echo $valueColor;?>>
                                            <a href="?page=03-01-03-002&fund=<?php echo $fundID;?>&sort=value<?php echo $addGetAll;?>" style=" <?php if($valueSort != 'set'){echo 'color:#000000;';}else{echo 'color:#ffffff !important;';}?>">VALUE <i class="icon-sort<?php if($valueSort == 'set'){echo'-down';}?>"></i></a>
                                        </th>
										<th style="display:none;">Ratio</th>
										<th <?php echo $gainsColor;?>>
                                            <a href="?page=03-01-03-002&fund=<?php echo $fundID;?>&sort=gains<?php echo $addGetAll;?>" style=" <?php if($gainsSort != 'set'){echo 'color:#000000;';}else{echo 'color:#ffffff !important;';}?>">GAINS <i class="icon-sort<?php if($gainsSort == 'set'){echo'-down';}?>"></i></a>
                                        </th>
                                        <th <?php echo $todayColor;?>>
                                            <a href="?page=03-01-03-002&fund=<?php echo $fundID;?>&sort=today<?php echo $addGetAll;?>" style=" <?php if($todaySort != 'set'){echo 'color:#000000;';}else{echo 'color:#ffffff !important;';}?>">TODAY <i class="icon-sort<?php if($todaySort == 'set'){echo'-down';}?>"></i></a>
                                        </th>
                                        <th <?php echo $inceptColor;?>>
                                            <a href="?page=03-01-03-002&fund=<?php echo $fundID;?>&sort=incept<?php echo $addGetAll;?>" style=" <?php if($inceptSort != 'set'){echo 'color:#000000;';}else{echo 'color:#ffffff !important;';}?>">INCEPTION RETURN <i class="icon-sort<?php if($inceptSort == 'set'){echo'-down';}?>"></i></a>
                                        </th>
                                        <th <?php echo $currentColor;?>>
                                            <a href="?page=03-01-03-002&fund=<?php echo $fundID;?>&sort=current<?php echo $addGetAll;?>" style=" <?php if($currentSort != 'set'){echo 'color:#000000;';}else{echo 'color:#ffffff !important;';}?>">CURRENT RETURN <i class="icon-sort<?php if($currentSort == 'set'){echo'-down';}?>"></i></a>
                                        </th>
										<th>ACTION</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
							<?php    
							
							//Count array
							$cntPos = count($aPositions);
							
							if(!in_array($sort, $aNoSplitCols)){
								//Get row counts for each section, evenly divided
								$topRows 	= ceil($cntPos / 3);
								$midRows 	= round($cntPos / 3);
								$bottomRows = ($cntPos - ($topRows + $midRows));
							}else{
								$topRows = 0;
								$midRows = 0;
								$bottomRows = 0;
							}
							
							//Set up counters for each section
							$topCnt = 0;
							$midCnt = 0;
							$btmCnt = 0;
							
                                                        $newCnt = 0;
							foreach($aPositions as $pos){
								$newCnt++;
								
								$aLabelInfo = getPosLabel($mLink, $fundID, $pos['stockSymbol']);
								$label		= $aLabelInfo['label'];
								//Build new array of values to be used in function (makes it easier to pass to the function, one var instead of a bunch)
								$aSort = array(
									'symbol'		=> $pos['stockSymbol'],
									'price'			=> $pos['currentPrice'],
									'shares'		=> $pos['totalShares'],
									'value'			=> $pos['currentValue'],
									'ratio'			=> $pos['fundRatio'],
									'gains'			=> $pos['gains'],
									'today'			=> $pos['todayReturn'],
									'return'		=> $pos['totalReturn'],
									'currentReturn' => $pos['currentReturn'],
									'cnt'			=> $newCnt,
									'label'			=> $label,
									'fund_id'		=> $fundID
								);
								
								if($topRows == 0){
										
									//Increment section coutner by 1
										$topCnt = $topCnt + 1;
										
										//Add section vars to array (these determin color and position)
										$aSort['secCnt']	= $topCnt;
										$aSort['trColor'] 	= 'green';
										$aSort['section'] 	= 'TOP';
										$aSort['secRows'] 	= $cntPos;
										$aSort['noTop']		= 1;
										
										//Print the row
										echo printRows($aSort);
									
								}else{
								
									//START: TOP SECTION
									if($newCnt <= $topRows){
										
										//Increment section coutner by 1
										$topCnt = $topCnt + 1;
										
										//Add section vars to array (these determin color and position)
										$aSort['secCnt'] = $topCnt;
										$aSort['trColor'] = 'green';
										$aSort['section'] = 'TOP';
										$aSort['secRows'] = $topRows;
										
										//Print the row
										echo printRows($aSort);
									}
									//END: TOP SECTION
									
									//START: MIDDLE SECTION
									if($newCnt <= ($topRows + $midRows) && $newCnt > $topRows){
										
										//Increment section counter by 1
										$midCnt = $midCnt + 1;
										
										//Add section vars to array (these determin color and position)
										$aSort['secCnt'] = $midCnt;
										$aSort['trColor'] = 'yellow';
										$aSort['section'] = 'MIDDLE';
										$aSort['secRows'] = $midRows;
										
										//Print the row
										echo printRows($aSort);
									}
									//END: MIDDLE SECTION
									
									//START: BOTTOM SECTION
									if($newCnt <= ($topRows + $midRows + $bottomRows) && $newCnt > ($topRows + $midRows)){
										
										//Increment section counter by 1
										$btmCnt = $btmCnt + 1;
										
										//Add section vars to array (these determin color and position)
										$aSort['secCnt'] = $btmCnt;
										$aSort['trColor'] = 'red1';
										$aSort['section'] = 'BOTTOM';
										$aSort['secRows'] = $bottomRows;
										
										//Print the row
										echo printRows($aSort);
									}
									//END: BOTTOM SECTION
								}
								
							} // foreach $aStrat
							
							?>
							</tbody>
						</table>
						<!--END sector: <?php echo $sectorName;?>-->
						<?php
						
					} //end foreach $asectors loop
					//START: Print each sector Section
					
					// debug info
					/*echo '<pre>';
					print_r($aSectors);
					echo '</pre>';*/
					?>

                    </div><!--portlet-body-->
                </div>
                <!-- END Strat by Sectors PORTLET-->
            
            </div><!--END COLUMN-->
        </div><!--END ROW-->
        <!-- END PAGE CONTENT-->
   
