<?php
/*
Marketocracy Inc. | Beta Development
Fund Month To Month Scripts

Supporting files:
	AJAX		- process/ajax/portlets/fund-month-ajax.php
	JAVASCRIPT	- includes/scripts/portlets/fund-month.js.php
	HTML		- THIS DOCUMENT includes/portlets/fund-month.php
*/


$fundSymbol = get_funds($mLink, $fundID, 'fundSymbol');

?>

            <!-- BEGIN PAGE CONTENT-->          
            <div class="row">
                <div class="col-md-12">
                
                    <?php /*?><div class="note note-warning">
                    <h4 class="block">PAGE AVAILABLE SOON - UNDER CONSTRUCTION</h4>
                    <p>The information reflected on this page is not accurate. Please check back later.</p>
                    </div><!--note--><?php */?>
                    
                    <!-- BEGIN FUND INFO -->
					<?php include('includes/portlets/fund-live-info.php');?>
                    <!-- END FUND INFO -->
                    
                    <!-- BEGIN Month To Month PORTLET-->
                    <div class="portlet">
                        <div class="portlet-title">
                        <div class="caption"><i class="icon-bar-chart"></i>Returns Month To Month</div>
                        <div class="tools">
                        <a href="javascript:;" class="collapse"></a>
                        <a href="javascript:;" class="reload"></a>
                        </div>
                        </div>
                        <div class="portlet-body flip-scroll">
                        
                        
                            <?php /*?><div class="alert alert-success alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
                            For the six month period ending December 31, 2013 your fund outperformed 98.2% of the other funds on our site.
                            </div>
                            
                            <div class="alert alert-info alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
                            Congratulations you have earned some mPoints with one or more of your funds. <a href="#" class="alert-link">Click to see the full report</a>.
                            </div><?php */?>
                            
                            
                            <table id="fund-zone-table" class="table table-bordered table-striped table-condensed flip-content" style="text-align:center;">
                                <thead class="flip-content">
                                   
                                    <tr style="background:#5BC0DE;color:#ffffff;">
                                        <th style="background:#FCB322;border-right:2px solid #FCB322;border-top:2px solid #FCB322;">Year</th>
                                        <th style="border-top:2px solid #5BC0DE;">Jan</th>
                                        <th style="border-top:2px solid #5BC0DE;">Feb</th>
                                        <th style="border-top:2px solid #5BC0DE;">Mar</th>
                                        <th style="border-top:2px solid #5BC0DE;">Apr</th>
                                        <th style="border-top:2px solid #5BC0DE;">May</th>
                                        <th style="border-top:2px solid #5BC0DE;">Jun</th>
                                        <th style="border-top:2px solid #5BC0DE;">Jul</th>
                                        <th style="border-top:2px solid #5BC0DE;">Aug</th>
                                        <th style="border-top:2px solid #5BC0DE;">Sep</th>
                                        <th style="border-top:2px solid #5BC0DE;">Oct</th>
                                        <th style="border-top:2px solid #5BC0DE;">Nov</th>
                                        <th style="border-top:2px solid #5BC0DE;">Dec</th>
                                        <th style="border-left: 2px solid #422A88;border-top: 2px solid #422A88;background:#422A88;"><?php echo $fundSymbol;?> YTD</th>
                                        <th style="background:#006ca3;border-left: 2px solid #006ca3;border-top: 2px solid #006ca3;background">S&amp;P YTD</th>
                                    </tr>
                                </thead>
                                <tbody>
                                	<?php
									
									$query = "
										SELECT *
										FROM ".$_SESSION['fund_mtm_table']."
										WHERE fund_id=:fund_id
										ORDER BY unix_date DESC
									";
									
									try{
										$rsValues = $mLink->prepare($query);
										$aValues = array(
											':fund_id' 	=> $fundID
											
										);
										$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
										$rsValues->execute($aValues);
									}
									
									catch(PDOException $error){
										// Log any error
										file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
									}
									
									$aMTM = array();
									
									while($months = $rsValues->fetch(PDO::FETCH_ASSOC)){
										
										$date = $months['unix_date'];
										
										$aMTM[] = array(
											'date'		=> $date,
											'rMonth'	=> date('F', $date),
											'value'		=> $months['value'],
											'year'		=> date('Y', $date),
											'YTD'		=> $months['YTD'],
											'spYTD'		=> $months['spYTD']
										);
										
										$aChartFund['['.($date*1000)] = number_format($months['value'], 2, '.', ',').']';
										$aChartSP['['.($date*1000)] = number_format(($months['spYTD']*100), 2, '.', ',').']';
										
										
									}
									
									$aProcess = array();
									
									foreach($aMTM as $key=>$aValues){
										
										$date = $aValues['date'];
										
										$day 	= date('d', $date);
										$month	= date('m', $date);
										$year	= date('Y', $date);
										
										if(in_array($year, $aProcess)){
											
										}else{
											$aProcess[$year] = array(
												'01' => NULL,
												'02' => NULL,
												'03' => NULL,
												'04' => NULL,
												'05' => NULL,
												'06' => NULL,
												'07' => NULL,
												'08' => NULL,
												'09' => NULL,
												'10' => NULL,
												'11' => NULL,
												'12' => NULL
											);	
										}
										
										
									}
									
									foreach($aMTM as $key=>$aValues){
										
										$date = $aValues['date'];
										
										
										$day 	= date('d', $date);
										$month	= date('m', $date);
										$year	= date('Y', $date);
										
										foreach($aProcess as $years=>$aMonths){
												
											if($year == $years){
												
												foreach($aMonths as $months=>$aMonthValues){
													
													if($month == $months){
														$aProcess[$year][$month]['value'] = $aValues['value'];
														$aProcess[$year][$month]['rMonth'] 	= $aValues['rMonth'];
														$aProcess[$year][$month]['YTD'] 	= $aValues['YTD'];
														$aProcess[$year][$month]['spYTD']	= $aValues['spYTD'];
														
														
													}
														
												}
													
											}
												
										}
									}
									
									//TIME TO PRINT
									
									$cnt = 1;
									
									foreach($aProcess as $year=>$aMonths){
										
										if(isEven($cnt) == 0){
											$addStyle1 = "background: #E2DFEE;";
											$addStyle2 = "background: #dbe6f2;";
											$addStyle3 = "";
										}else{
											$addStyle1 = "background: #AA9FCB;";
											$addStyle2 = "background: #97b9d7;";
											$addStyle3 = "background: #fad997;";
										}
										
										echo '<tr>';
										
										echo '<td>'.$year.'</td>';
										
										foreach($aMonths as $month=>$aValues){
											
											$mReturn = $aValues['value'];
											
											if($mReturn != NULL){
												
												if($mReturn < -5){
													//$monthReturn = '<span class="label label-danger">'.number_format($mReturn, 2, '.', ',').'%</span>';
													
													$addCellStyle = 'background: #F07154;color:#ffffff;';
												}elseif($mReturn > 5){
													//$monthReturn = '<span class="label label-success">'.number_format($mReturn, 2, '.', ',').'%</span>';
													
													$addCellStyle = 'background: #63CC73;color:#ffffff;';
												}else{
													//$monthReturn = number_format($mReturn, 2, '.', ',').'%';
													
													$addCellStyle = '';	
												}
												
												
												
												$monthReturn = number_format($mReturn, 2, '.', ',').'%';
											}else{
												$monthReturn = '-';	
												$addCellStyle = '';
											}
											
											
											if($aValues != NULL){
												$currentSpYTD 	= number_format(($aValues['spYTD']), 2, '.', ',');
												$currentYTD		= number_format(($aValues['YTD']*100), 2, '.', ',');	
											}
											
											$tdTitle = 'title="'.$aValues['rMonth'].' &#13;'.$fundSymbol.' YTD: '.$currentYTD.'% | S&P YTD: '.$currentSpYTD.'%"';
											
											echo '<td '.$tdTitle.' style="'.$addCellStyle.'">'.$monthReturn.'</td>';
											
											
										}
										
										
										
										echo '<td style="border-left: 2px solid #422A88 !important; '.$addStyle1.'"><strong>'.$currentYTD.'% </strong></td>';
										echo '<td style="border-left: 2px solid #006ca3; '.$addStyle2.'">'.$currentSpYTD.'%</td>';
										
										echo '</tr>';
										
										$cnt++;
											
									}
									
									
									
									
									
									?>
                                    
                                    
                                    
                                </tbody>
                            </table>
                            <?php /*?><span class="label label-success" style="opacity:.8;">TEST</span><span class="label label-danger" style="opacity:.8;">TEST</span><?php */?>
                            <div class="zone-key">
                                <div style="float:left;width: 20px;height: 20px; border:1px solid #666; background: #63CC73;-moz-border-radius: 10px;-webkit-border-radius: 10px;border-radius: 10px;"></div> 
                                <div style="float:left; margin-left:10px;">Returns greater than 5%</div>
                                <div style="clear:both;"></div>
                            </div><!--zone-key-->
                            
                            <div class="zone-key">
                                <div style="float:left;width: 20px;height: 20px; border:1px solid #666; background: #F07154;-moz-border-radius: 10px;-webkit-border-radius: 10px;border-radius: 10px;"></div> 
                                <div style="float:left; margin-left:10px;">Returns less than -5%</div>
                                <div style="clear:both;"></div>
                            </div><!--zone-key-->
                            <div class="clear"></div><!--clear-->
                            
							<?php 
                            /*echo '<pre>';
                            
							echo $error;
							print_r($aProcess);
							print_r($aMTM);
                            echo '</pre>';*/
							?>
                            
                        </div><!--portlet-body-->
                    </div><!--portlet-->
                    <!-- END Month To Month PORTLET-->
                    
                    
                    <!-- BEGIN SAMPLE TABLE PORTLET-->
                    <div class="portlet">
                        <div class="portlet-title">
                        <div class="caption"><i class="icon-bar-chart"></i>Chart</div>
                        <div class="tools">
                        <a href="javascript:;" class="collapse"></a>
                        <a href="javascript:;" class="reload"></a>
                        </div>
                        </div>
                        <div class="portlet-body flip-scroll">
                        
                        	<div id="monthToMonth"></div>
                            
                        </div><!--portlet-body-->
                    </div><!--portlet-->
                    <!-- END Month To Month PORTLET-->
                
                </div><!--END COLUMN-->
            </div><!--END ROW-->
            <!-- END PAGE CONTENT-->