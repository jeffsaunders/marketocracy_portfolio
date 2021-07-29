            <!-- BEGIN PAGE CONTENT-->          
            <div class="row">
                <div class="col-md-12">
                <!-- BEGIN FUND INFO -->
                <?php include('includes/portlets/fund-live-info.php');?>
                <!-- END FUND INFO -->
                <?php /*?><div class="note note-warning">
                    <h4 class="block">PAGE AVAILABLE SOON - UNDER CONSTRUCTION</h4>
                    <p>The information reflected on this page is not accurate. Please check back later.</p>
                </div><!--note--><?php */?>
                
                <!-- BEGIN PERFORMANCE ZONE PORTLET-->
                <div class="portlet" id="performance">
                    
                    <div class="portlet-title">
                        <div class="caption"><i class="icon-cogs"></i>Performance Zones</div>
                        <div class="tools">
                            <a href="javascript:;" class="collapse"></a>
                            <a href="javascript:;" class="reload"></a>
                        </div>
                    </div><!--portlet-title-->
                    
                    <div class="portlet-body flip-scroll">
                    	<?php
						//Get Gain Total
						$query = '
							SELECT gains
							FROM '.$_SESSION['fund_stratification_basic_table'].' 
							WHERE fund_id=:fund_id 
						';
						try{
							$rsGainTotal = $mLink->prepare($query);
							$aValues = array(
								':fund_id'		=> $fundID
							);
							$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
							$rsGainTotal->execute($aValues);
						}
						catch(PDOException $error){
							// Log any error
							file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
						}
						
						$gainMasterTotal = 0;
						
						while($gain = $rsGainTotal->fetch(PDO::FETCH_ASSOC)){
							$gainMasterTotal = $gainMasterTotal + $gain['gains'];
						}
						
						
						
						
						$aZones = array(
							'1' 	=> array('sectorName' => 'Energy'),
							'2' 	=> array('sectorName' => 'Materials'),
							'3' 	=> array('sectorName' => 'Industrials'),
							'4' 	=> array('sectorName' => 'Consumer Discretionary'),
							'5' 	=> array('sectorName' => 'Consumer Staples'),
							'6' 	=> array('sectorName' => 'Health Care'),
							'7' 	=> array('sectorName' => 'Financials'),
							'8' 	=> array('sectorName' => 'Info. Tech.'),
							'9' 	=> array('sectorName' => 'Telecom'),
							'10'	=> array('sectorName' => 'Utilities'),
							'11' 	=> array('sectorName' => 'Unclassified')
						);
						
						foreach($aZones as $sectorCode => $aSector){
							
							$query = '
								SELECT *
								FROM '.$_SESSION['fund_stratification_basic_table'].' 
								WHERE fund_id=:fund_id AND sector_code=:sector_code
							';
							try{
								$rsStyles = $mLink->prepare($query);
								$aValues = array(
									':fund_id'		=> $fundID,
									':sector_code'	=> $sectorCode
								);
								$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
								$rsStyles->execute($aValues);
							}
							catch(PDOException $error){
								// Log any error
								file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
							}
							
							while($style = $rsStyles->fetch(PDO::FETCH_ASSOC)){
								
								$aZones[$sectorCode]['styles'][$style['style_code']] = array('styleName' => $style['style']);
								
							}
							
							$rowGainTotal = 0;
							$rowWeightTotal = 0;
							
							foreach($aZones[$sectorCode]['styles'] as $styleCode => $aStyles){
								
								$query = '
									SELECT *
									FROM '.$_SESSION['fund_stratification_basic_table'].' 
									WHERE fund_id=:fund_id AND sector_code=:sector_code AND style_code=:style_code
								';
								try{
									$rsStyle = $mLink->prepare($query);
									$aValues = array(
										':fund_id'		=> $fundID,
										':sector_code'	=> $sectorCode,
										':style_code'	=> $styleCode
									);
									$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
									$rsStyle->execute($aValues);
								}
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}
								
								$gainsTotal = 0;
								$weightTotal = 0;
								
								while($cap = $rsStyle->fetch(PDO::FETCH_ASSOC)){
									
									//if($cap['totalShares'] != "0"){
										$gainsTotal = $gainsTotal + $cap['gains'];
										$aZones[$sectorCode]['styles'][$styleCode][$cap['stockSymbol']] = $cap['gains'];
									//}
									$weightTotal = $weightTotal + $cap['fundRatio'];
									
								}
								
								$gainsTotal = round(($gainsTotal / abs($gainMasterTotal)) * 100);
								$weightTotal = round($weightTotal * 100);
								
								$aZones[$sectorCode]['styles'][$styleCode]['gains'] = ($gainsTotal);
								$aZones[$sectorCode]['styles'][$styleCode]['weight'] = ($weightTotal);
								
								$rowGainTotal = $rowGainTotal + $gainsTotal;
								$rowWeightTotal = $rowWeightTotal + $weightTotal;
								
							}
							
							$aZones[$sectorCode]['gainTotal'] = round($rowGainTotal);
							$aZones[$sectorCode]['weightTotal'] = round($rowWeightTotal);
								
						}
						
						
						?>
                        <?php /*?><pre>
                        <?php print_r($aZones);?>
                        <?php echo $gainMasterTotal;?>
                        </pre><?php */?>
                        <table id="fund-zone-table" class="table table-bordered table-striped table-condensed flip-content">
                            <thead class="flip-content">
                                <tr>
                                    <th class="fzt-organge"></th>
                                    <th colspan="2" class="fzt-organge">LARGE</th>
                                    <th colspan="2" class="fzt-organge">MID</th>
                                    <th colspan="2" class="fzt-organge">SMALL</th>
                                    <th colspan="2" class="fzt-organge">MICRO</th>
                                    <th colspan="2" class="fzt-organge">UNCL.</th>
                                    <th colspan="2" class="fzt-purple fzt-border-full">TOTAL</th>
                                </tr>
                                <tr>
                                    <th class="fzt-aleft strat-table-header">SECTOR</th>
                                    <th class="strat-table-header">GAIN</th>
                                    <th class="strat-table-header">WT</th>
                                    <th class="strat-table-header">GAIN</th>
                                    <th class="strat-table-header">WT</th>
                                    <th class="strat-table-header">GAIN</th>
                                    <th class="strat-table-header">WT</th>
                                    <th class="strat-table-header">GAIN</th>
                                    <th class="strat-table-header">WT</th>
                                    <th class="strat-table-header">GAIN</th>
                                    <th class="strat-table-header">WT</th>
                                    <th class="strat-table-header fzt-border-left">GAIN</th>
                                    <th class="strat-table-header">WT</th>
                                </tr>
                            </thead>
                            <tbody>
                            	<?php
								$posColor = 'style="background:#63CC73 !important;color:#ffffff;"';/*#00b8f1*/
								$negColor = 'style="background:#F07154 !important;color:#ffffff;"';/*#ed4e2a*/
								$weightColor = 'style="background:#ffffcc !important;color:#000000;"';
								
								$rowCnt = 0;
								
								//Build TD function
								function printTD($value, $type){
																		
									// GAIN COLUMN
									if($type == 'gain'){
										
										//IF there is no real value or value is = to 0(zero) use dash(-)
										if($value == '0' || $value == '-0' || $value == ''){
											
											$value = '-';
											
											$td = '<td class="numeric text-center">'.$value.'</td>';
										
										//IF there is a valid value evaluate it and assign the appropriate highlight	
										}else{
											
											$value = ''.$value.'%';
											
											if($value > 5){
												$td = '<td class="numeric text-center" style="background:#63CC73 !important;color:#ffffff;">'.$value.'</td>';/*#00b8f1*/
											}elseif($value <= -1){
												$td = '<td class="numeric text-center" style="background:#F07154 !important;color:#ffffff;">'.$value.'</td>';
											}else{
												$td = '<td class="numeric text-center">'.$value.'</td>';
											}
										}
									
									// WEIGHT COLUMN	
									}elseif($type == 'weight'){
										
										//IF there is no real value or value is = to 0(zero) use dash(-)
										if($value == '0' || $value == '-0' || $value == ''){
											
											$value = '-';
											
											$td = '<td class="numeric text-center">'.$value.'</td>';

										//IF there is a valid value evaluate it and assign the appropriate highlight	
										}else{
											
											$value = ''.$value.'%';
											
											if($value > 10){
												$td = '<td class="numeric text-center" style="background:#ffffcc !important;color:#000000;">'.$value.'</td>';
											}else{
												$td = '<td class="numeric text-center">'.$value.'</td>';
											}
										}
										
									}
									
									//Return row for print
									return $td;
								}
								//END Build TD function
								
								//Set Total Vars - used to calculate totals
								$largeGainTotal 	= 0;
								$largeWeightTotal 	= 0;
								$midGainTotal 		= 0;
								$midWeightTotal 	= 0;
								$smallGainTotal 	= 0;
								$smallWeightTotal 	= 0;
								$microGainTotal 	= 0;
								$microWeightTotal 	= 0;
								$unclassGainTotal 	= 0;
								$unclassWeightTotal	= 0;
								
								foreach($aZones as $sectorCode => $sector){
									
									//Set Vars
									$secName 		= $sector['sectorName'];
									$aStyles 		= $sector['styles'];
									$secGainTotal 	= $sector['gainTotal'];
									$secWeightTotal	= $sector['weightTotal'];
									
									// large cap vars
									$largeGain 		= $aStyles[1]['gains'];
									$largeWeight	= $aStyles[1]['weight'];
									
									$largeGainTotal		= $largeGainTotal + $largeGain;
									$largeWeightTotal	= $largeWeightTotal + $largeWeight;
									// mid cap vars
									$midGain 		= $aStyles[2]['gains'];
									$midWeight		= $aStyles[2]['weight'];
									
									$midGainTotal		= $midGainTotal + $midGain;
									$midWeightTotal		= $midWeightTotal + $midWeight;
									// small cap vars
									$smallGain 		= $aStyles[3]['gains'];
									$smallWeight	= $aStyles[3]['weight'];
									
									$smallGainTotal		= $smallGainTotal + $smallGain;
									$smallWeightTotal	= $smallWeightTotal + $smallWeight;
									// micro cap vars
									$microGain 		= $aStyles[4]['gains'];
									$microWeight	= $aStyles[4]['weight'];
									
									$microGainTotal 	= $microGainTotal + $microGain;
									$microWeightTotal	= $microWeightTotal + $microWeight;
									// unclassified cap vars
									$unclassGain 	= $aStyles[5]['gains'];
									$unclassWeight	= $aStyles[5]['weight'];
									
									$unclassGainTotal	= $unclassGainTotal + $unclassGain;
									$unclassWeightTotal	= $unclassWeightTotal + $unclassWeight;
									
									//Cell formating
									if($secGainTotal > 5){
										$gainTotalStyle = $posColor;	
									}elseif($secGainTotal < -1){
										$gainTotalStyle = $negColor;
									}else{
										$gainTotalStyle = '';	
									}
									
									if($secWeightTotal > 10){
										$weightTotalStyle = $weightColor;
									}else{
										$weightTotalStyle = '';	
									}
									
									
									//Text formating
									if($secGainTotal == "0" || $secGainTotal == "-0"){
										$secGainTotal = '-';	
									}else{
										$secGainTotal = ''.$secGainTotal.'%';	
									}
									
									if($secWeightTotal == "0" || $secWeightTotal == "-0"){
										$secWeightTotal = '-';	
									}else{
										$secWeightTotal = ''.$secWeightTotal.'%';	
									}
									
									
									//Check whether the section Counter is odd or even to determin row shade color
									$rowCnt = $rowCnt + 1;
									
									if(isOdd($rowCnt) == true){
										// If odd, make shade dark
										$totalClass = 'fzt-light-purple';
									}else{
										// If even, make shade light
										$totalClass = 'fzt-light-purple2';	
									}
								
									?>
                                    <tr>
                                        <td><?php echo $secName;?></td>
                                        <?php echo printTD($largeGain, 'gain');?>
                                        <?php echo printTD($largeWeight, 'weight');?>
                                        <?php echo printTD($midGain, 'gain');?>
                                        <?php echo printTD($midWeight, 'weight');?>
                                        <?php echo printTD($smallGain, 'gain');?>
                                        <?php echo printTD($smallWeight, 'weight');?>
                                        <?php echo printTD($microGain, 'gain');?>
                                        <?php echo printTD($microWeight, 'weight');?>
                                        <?php echo printTD($unclassGain, 'gain');?>
                                        <?php echo printTD($unclassWeight, 'weight');?>
                                        <td class="numeric text-center fzt-border-left <?php echo $totalClass;?>" <?php echo $gainTotalStyle;?>><?php echo $secGainTotal;?></td>
                                        <td class="numeric text-center <?php echo $totalClass;?>" <?php echo $weightTotalStyle;?>><?php echo $secWeightTotal;?></td>
                                    </tr>
                                    <?php	
									
								}
								
								//Get Live Price Data for CASH ROW
								$query = '
									SELECT * 
									FROM '.$_SESSION['fund_liveprice_table'].'
									WHERE fund_id=:fund_id
								';
								
								try{
									$rsLivePrice = $mLink->prepare($query);
									$aValues = array(
										':fund_id'	=> $fundID
									);
									$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
									$rsLivePrice->execute($aValues);
								}
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}
								
								$livePrice = $rsLivePrice->fetch(PDO::FETCH_ASSOC);
								
								//get Total Value
								$totalValue = $livePrice['totalValue'];
								$cashValue = $livePrice['cashValue'];
								$cashPercent = number_format((($cashValue / $totalValue)*100), 0);
								?>
                                
                                <tr>
                                    <td>Cash</td>
                                    <td class="numeric text-center">-</td>
                                    <td class="numeric text-center">-</td>
                                    <td class="numeric text-center">-</td>
                                    <td class="numeric text-center">-</td>
                                    <td class="numeric text-center">-</td>
                                    <td class="numeric text-center">-</td>
                                    <td class="numeric text-center">-</td>
                                    <td class="numeric text-center">-</td>
                                    <td class="numeric text-center">-</td>
                                    <td class="numeric text-center">-</td>
                                    <td class="numeric text-center fzt-border-left fzt-light-purple2">-</td>
                                    <td class="numeric text-center fzt-light-purple2"><?php echo $cashPercent;?>%</td>
                                </tr>
                                <tr class="fzt-border-top fzt-light-purple">
                                    <td class="fzt-border-full" style="background:#422a88 !important;color:#ffffff;">TOTAL</td>
                                    <?php echo printTD($largeGainTotal, 'gain');?>
                                    <?php echo printTD($largeWeightTotal, 'weight');?>
                                    <?php echo printTD($midGainTotal, 'gain');?>
                                    <?php echo printTD($midWeightTotal, 'weight');?>
									<?php echo printTD($smallGainTotal, 'gain');?>
                                    <?php echo printTD($smallWeightTotal, 'weight');?>
                                    <?php echo printTD($microGainTotal, 'gain');?>
                                    <?php echo printTD($microWeightTotal, 'weight');?>
                                    <?php echo printTD($unclassGainTotal, 'gain');?>
                                    <?php echo printTD($unclassWeightTotal, 'weight');?>
                                    <td class="numeric text-center fzt-border-left" style="background:#422a88 !important;color:#ffffff;">100%</td>
                                    <td class="numeric text-center" style="background:#422a88 !important;color:#ffffff;">100%</td>
                                </tr>
                            </tbody>
                        </table>
                    	
                        <div class="zone-key">
                            <div style="float:left;width: 20px;height: 20px; border:1px solid #666; background: #3cc051;-moz-border-radius: 10px;-webkit-border-radius: 10px;border-radius: 10px;"></div> 
                            <div style="float:left; margin-left:10px;">Gains greater than 5%</div>
                            <div style="clear:both;"></div>
                        </div><!--zone-key-->
                        
                        <div class="zone-key">
                            <div style="float:left;width: 20px;height: 20px; border:1px solid #666; background: #97004e;-moz-border-radius: 10px;-webkit-border-radius: 10px;border-radius: 10px;"></div> 
                            <div style="float:left; margin-left:10px;">Losses greater than 1%</div>
                            <div style="clear:both;"></div>
                        </div><!--zone-key-->
                        
                        <div class="zone-key">
                            <div style="float:left;width: 20px;height: 20px; border:1px solid #666; background: #ffffcc;-moz-border-radius: 10px;-webkit-border-radius: 10px;border-radius: 10px;"></div> 
                            <div style="float:left; margin-left:10px;">Weighted over 10%</div>
                            <div style="clear:both;"></div>
                        </div><!--zone-key-->
                        <div class="clear"></div>
                        
                    </div><!--portlet-body-->
                </div><!--portlet-->
                <!-- END PERFORMANCE ZONE PORTLET-->
                
                </div><!--END COLUMN-->
            </div><!--END ROW-->
            <!-- END PAGE CONTENT-->
         
            <!--START TRADE SCHOOL-->
            <div class="portlet">
                <div class="portlet-title">
                    <div class="caption"><i class="icon-book"></i>Trade School</div>
                    <div class="tools">
                    <a href="javascript:;" class="collapse"></a>
                    <a href="javascript:;" class="reload"></a>
                    </div><!--tools-->
                </div><!--portlet-title-->
                <div class="portlet-body">
                
                    <h3>How does this make me a better investor?</h3>
                    <div class="divider"></div>
                    
                    <p>This report splits the monetary gains made so far in your portfolio into zones by sector and market cap. Are you making more money in some areas than others? Do you have the most money in the areas where you make the most money?</p>
                    
                    <p>Note that if you have losses in areas or only small net gains overall, it is possible to have greater than 100% gains in some zones because your losses in some zones will balance the gains in other zones.</p>
                </div><!--END PORTLET BODY-->
            </div><!--END PORTLET-->
            <!--END TRADE SCHOOL-->
     