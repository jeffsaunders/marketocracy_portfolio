<?php
if($_SESSION['admin'] == '1'){
	
	$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	
	
	if($_REQUEST['convertBtn'] == 1){
		$convertLink = $actual_link.'&convertBtn=0';
		$showConvertBtn = '<a href="'.$convertLink.'" class="btn btn-xs btn-info">Convert To Button</a>';
	}else{
		$convertLink = $actual_link.'&convertBtn=1';
		$showConvertBtn = '<a href="'.$convertLink.'" class="btn btn-xs btn-info">Convert To Text</a>';
	}
	
}else{
	$showConvertBtn = '';	
}
?>



            <!-- BEGIN STOCK LABEL PORTLET-->         
            <div class="modal fade" id="stock-label" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                            <h4 class="modal-title">Set Label</h4>
                        </div><!--modal-header-->
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
                        </div><!--modal-body-->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success">Save</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div><!--modal-footer-->	
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- STOCK LABEL PORTLET-->
            
            
            
            
            
            
            <!-- BEGIN STOCK DETAILS PORTLET-->         
            <div class="modal fade in" id="stock-details" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                            <h4 style="float:left;" class="modal-title"><button title="Go Back To Previous Screen" type="button" class="btn btn-warning btn-sm" data-dismiss="modal"><i class="icon-arrow-left"></i></button> Position Details</h4>
    						<p style="float:right;margin:5px 10px 0px 0px;"><?php echo date('F d, Y');?></p>
    						<div class="clearfix"></div>
                        </div><!--modal-header-->
                        <div class="modal-body">
                            <?php /*?><div class="note note-warning">
                                <h4 class="block">Position Details Coming Soon!</h4>
                                <p>The information reflected on this page is not accurate. Please check back later.</p>
                            </div><!--note--><?php */?>
                            
                            <div id="position-detail-load"></div>
                            
                        </div><!--modal-body-->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div><!--modal-footer-->	
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- STOCK DETAILS PORTLET-->
            
            
              
            <!-- BEGIN STOCK INFO TEADE-->         
            <div class="modal fade in" id="stock-info-trade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content" style="background: #FAFAFA;">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                            <h4 class="modal-title trade-scroll-to"><button title="Go Back To Previous Screen" type="button" class="btn btn-warning btn-sm" data-dismiss="modal"><i class="icon-arrow-left"></i></button> Trade Stock </h4>
                        </div><!--modal-header-->
                        <div class="modal-body">
                            
                            <div id="trade-detail-load"></div>
                            
                        </div><!--modal-body-->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Back</button>
                        </div><!--modal-footer-->	
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- stock info trade PORTLET-->  
            
            <!-- BEGIN STOCK INFO TICKETS PORTLET-->         
            <div class="modal fade in" id="stock-info-tickets" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-full">
                    <div class="modal-content" style="background: #FAFAFA;">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                            <h4 class="modal-title"><button title="Go Back To Previous Screen" type="button" class="btn btn-warning btn-sm" data-dismiss="modal"><i class="icon-arrow-left"></i></button> Tickets</h4>
                        </div><!--modal-header-->
                        <div class="modal-body">
                            <div id="ticket-detail-load2"></div>
                            
                        </div><!--modal-body-->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div><!--modal-footer-->	
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- STOCK INFO TICKETS PORTLET-->
            
            <!-- BEGIN STOCK DETAILS PORTLET-->         
            <div class="modal fade in" id="ticket-details" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content" style="background: #FAFAFA;">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                            <h4 class="modal-title"><button title="Go Back To Previous Screen" type="button" class="btn btn-warning btn-sm" data-dismiss="modal"><i class="icon-arrow-left"></i></button> Ticket Details</h4>
                        </div><!--modal-header-->
                        <div class="modal-body">
                            
                            <div id="ticket-detail-load"></div>
                            
                        </div><!--modal-body-->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div><!--modal-footer-->	
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- STOCK DETAILS PORTLET-->
            
            <!-- BEGIN DETAILS MODAL-->         
            <div class="modal fade" id="fund-cancel" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content cancel-fund">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                            <h4 class="modal-title">Confirm Cancel Order</h4>
                            <h5><?php echo $name;?> (<?php echo $symbol;?>) | Status: <span class="label label-success"><?php echo $order['status'];?></span></h5>
                        </div>
                        <div class="modal-body cancel-fund">
                            
                            <h4>Please Confirm Your Cancel Order</h4>
                            <p>Clicking the button below will send a cancel request to the exchange and take you back to the Open Orders page. The order will still appear in Open Orders for a few minutes until the exchange processes your request. After the exchange has canceled trading for that order, the order will move to the Recent Orders page. Any shares already traded will then be credited to your funds.</p>
                            
                        </div><!--modal-body-->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel the unfilled portion of my order</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- END DETAILS MODAL-->
            <!--END MODALS-->
            
            <!-- BEGIN DETAILS MODAL-->         
            <div class="modal fade" id="strat-process-finished" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content cancel-fund">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="stopChecking();"></button>
                            <h4 class="modal-title">Stratification Updated</h4>
                            
                        </div>
                        <div class="modal-body cancel-fund">
                            
                            <p>Your stratification is now up to date. Would you like to refresh the page?</p>
                            
                        </div><!--modal-body-->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" data-dismiss="modal" onclick="stopChecking('1');">Refresh Page</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal" onclick="stopChecking('0');">Close</button>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- END DETAILS MODAL-->
            
            <!-- BEGIN DETAILS MODAL-->         
            <div class="modal fade" id="strat-process-reload" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content cancel-fund">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="stopChecking();"></button>
                            <h4 class="modal-title">Stratification Needs To Be Reloaded</h4>
                            
                        </div>
                        <div class="modal-body" id="refresh-symbol">
                            
                            
                            
                        </div><!--modal-body-->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" data-dismiss="modal" onclick="stopChecking2('2');">Refresh Page</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal" onclick="stopChecking2('0');">Close</button>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- END DETAILS MODAL-->
            <!--END MODALS-->
 
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
                    
                    
                    <!--STRATIFICATION MENU-->
                    <?php include("includes/nav-stratification.php"); ?>   
                    
                    <div id="load-process-note">
                    
                    	
                    
                    </div>
                    
                    <?php
					$getMessage = $_REQUEST['message'];
					$messageSymbol	= $_REQUEST['symbol'];
					
					
					if($getMessage == 'reload'){
						?>
						<div class="note note-warning">
                        	<p>We noticed a discrepancy in your trades for: <strong><?php echo $messageSymbol;?></strong>. Therefore, we are refreshing your stratification for this symbol. You will be given an option to refresh the page once the process is complete.</p> 
                        </div>                        
                        <?php
					}
					?>
                    
                    <!-- BEGIN Positions/Stratifications PORTLET-->
                    <div class="portlet">
                        <div class="portlet-title">
                            <div class="caption"><i class="icon-cogs"></i>Positions/Stratification <?php echo $showConvertBtn;?></div>
                            <div class="actions">
                            	<a href="process/ajax/fund-strat-basic-processes.php?process=refresh-strat&fund=<?php echo $fundID;?>" class="btn btn-info btn-sm"> Reload Stratification <i class="icon-refresh"></i></a>
                            </div>
                             <?php /*?><div class="tools">
                            	
                               <a href="javascript:;" class="collapse"></a>
                                <a href="javascript:;" class="reload"></a>
                            </div><!--tools--><?php */?>
                        </div><!--portlet-title-->
                        <div class="portlet-body flip-scroll">
                        <?php
						
						$debug = $_REQUEST['debug'];
						
						//check to see if its processing
						$query = "
							SELECT processing
							FROM ".$_SESSION['fund_table']."
							WHERE fund_id=:fund_id
						";
						try{
							$rsProcess = $mLink->prepare($query);
							$aValues = array(
								':fund_id'	=> $fundID
							);
							$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
							$rsProcess->execute($aValues);
						}
						catch(PDOException $error){
							// Log any error
							file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
						}
						$process = $rsProcess->fetch(PDO::FETCH_ASSOC);
						$processStatus = $process['processing'];
						
						
						$getAll = $_REQUEST['getAll'];
						if($getAll == '1'){
							$addGetAll = '&getAll=1';	
						}else{
							$addGetAll = '';	
						}
						
						$sort = $_REQUEST['sort'];
								
						switch($sort){
							case 'symbol'	: 
								$setSort 		= 'stockSymbol';
								$symbolSort 	= 'set';
								$symbolColor 	= 'style="background:#ffffff !important;"';
								$sortType		= 'ASC';
							break;
							case 'price'	: 
								$setSort 		= 'currentPrice';
								$priceSort 		= 'set';
								$priceColor 	= 'style="background:#ffffff !important;"';
								$sortType 		= "DESC";
								
							break;
							case 'shares'	: 
								$setSort 		= 'totalShares';
								$sharesSort		= 'set';
								$sharesColor 	= 'style="background:#ffffff !important;"';
								$sortType 		= "DESC";
								
								
							break;
							case 'value'	: $setSort = 'currentValue';$valueSort = 'set';$valueColor = 'style="background:#ffffff !important;"';$sortType='DESC';break;
							case 'percent'	: $setSort = 'fundRatio';$percentSort = 'set';$percentColor = 'style="background:#ffffff !important;"';$sortType='DESC';break;
							case 'gains'	: $setSort = 'gains';$gainsSort = 'set';$gainsColor = 'style="background:#ffffff !important;"';$sortType='DESC';break;
							case 'today'	: $setSort = 'todayReturn';$todaySort = 'set';$todayColor = 'style="background:#ffffff !important;"';$sortType='DESC';break;
							case 'incept'	: $setSort = 'totalReturn';$inceptSort = 'set';$inceptColor = 'style="background:#ffffff !important;"';$sortType='DESC';break;
							case 'current'	: $setSort = 'recentReturn';$currentSort = 'set';$currentColor = 'style="background:#ffffff !important;"';$sortType='DESC';break;
							
							default			: $setSort = 'recentReturn';$currentSort = 'set';$currentColor = 'style="background:#ffffff !important;"';$sortType='DESC';break;
						}
						
						$aNoSplitCols = array('price','shares','value','percent','symbol');
						?>
                        
                        <table id="fund-zone-table" class="table table-bordered table-striped table-condensed flip-content">
                            <thead class="flip-content">
                                <tr style="background-color:#5bc0de;color:#ffffff;">
                                    <th <?php echo $symbolColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=symbol<?php echo $addGetAll;?>" style=" <?php if($symbolSort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">SYMBOL <i class="icon-sort<?php if($symbolSort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    
									
                                    <th>LABEL</th>
                                    
                                    
                                    <th <?php echo $priceColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=price<?php echo $addGetAll;?>" style=" <?php if($priceSort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">PRICE <i class="icon-sort<?php if($priceSort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th <?php echo $sharesColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=shares<?php echo $addGetAll;?>" style=" <?php if($sharesSort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">SHARES <i class="icon-sort<?php if($sharesSort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th <?php echo $valueColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=value<?php echo $addGetAll;?>" style=" <?php if($valueSort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">VALUE <i class="icon-sort<?php if($valueSort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th <?php echo $percentColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=percent<?php echo $addGetAll;?>" style=" <?php if($percentSort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">PORTION OF FUND <i class="icon-sort<?php if($percentSort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th <?php echo $gainsColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=gains<?php echo $addGetAll;?>" style=" <?php if($gainsSort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">GAINS <i class="icon-sort<?php if($gainsSort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th <?php echo $todayColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=today<?php echo $addGetAll;?>" style=" <?php if($todaySort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">TODAY <i class="icon-sort<?php if($todaySort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th <?php echo $inceptColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=incept<?php echo $addGetAll;?>" style=" <?php if($inceptSort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">INCEPTION RETURN <i class="icon-sort<?php if($inceptSort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th <?php echo $currentColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=current<?php echo $addGetAll;?>" style=" <?php if($currentSort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">CURRENT RETURN <i class="icon-sort<?php if($currentSort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th>ACTION</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            	<?php
								//START: Query for displaying Position Stratification
								
								
								
								$query = '
									SELECT * 
									FROM '.$_SESSION['fund_stratification_basic_table'].' 
									WHERE fund_id=:fund_id
									
									ORDER BY '.$setSort.' '.$sortType.'
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
								//echo $preparedQuery;
								//Reset variables
								$cnt = 0;
								$rowCnt = 3; //delete me
								
								//Initialize Arrays
								$aStrat = array();
								$aStratNoPrice = array();
								
								
								//loop through result
								while($position = $rsPos->fetch(PDO::FETCH_ASSOC)){
									
									$lastProcessed = $position['timestamp'];
									
									//Set Vars
									$symbol	= $position['stockSymbol'];
									$price 	= $position['currentPrice'];
									$shares = $position['totalShares'];
									$value 	= $position['currentValue'];
									$ratio 	= $position['fundRatio'];
									$gains	= $position['gains'];
									$today	= $position['todayReturn'];
									$return = $position['totalReturn'];
									$recentReturn	= $position['recentReturn'];
									
									if($price == 0 && $shares != 0 && $value == 0){
										//stock is delisted or named has changed
										
										if($price == 0 && $shares > 0){
											
											$aStratNoPrice[$symbol] = array(
												'symbol'		=> $symbol,
												'price'			=> $price,
												'shares'		=> $shares,
												'value'			=> $value,
												'ratio'			=> $ratio,
												'gains'			=> $gains,
												'today'			=> $today,
												'return'		=> $return,
												'currentReturn'	=> $recentReturn,
												'cnt'			=> $cnt,
											);
												
										}
									}else{
									
										if($getAll != '1'){
											//Only work results that have shares
											if($shares != "0"){
												//Increase counter
												$cnt = $cnt + 1;
												
												//Format numbers
												$price 	= '$'.number_format($price, 2, '.', ',').'';
												$value 	= '$'.number_format($value, 2, '.', ',').'';
												$ratio 	= ''.number_format(($ratio * 100), 2, '.', '').'%';
												
												//$gains 	= '$'.number_format($gains, 2, '.', ',').'';
												$today 	= ''.number_format($today, 2, '.', '').'%';
												$return = ''.number_format($return, 2, '.', '').'%';
												
												/*if($gains < 0){
													$gains = str_replace('-','',$gains);
													$gains = '-$'.number_format($gains,2,'.',',');	
												}*/
												
												
												//Create Array to work with so we dont have to keep going back to the database
												if($shares >= '0'){
													//Create Array to work with so we dont have to keep going back to the database
													$aStrat[$symbol] = array(
														'symbol'		=> $symbol,
														'price'			=> $price,
														'shares'		=> $shares,
														'value'			=> $value,
														'ratio'			=> $ratio,
														'gains'			=> $gains,
														'today'			=> $today,
														'return'		=> $return,
														'currentReturn'	=> $recentReturn,
														'cnt'			=> $cnt,
													);
												}elseif($shares < 0){
													$aStratShort[$symbol] = array(
														'symbol'		=> $symbol,
														'price'			=> $price,
														'shares'		=> $shares,
														'value'			=> $value,
														'ratio'			=> $ratio,
														'gains'			=> $gains,
														'today'			=> $today,
														'return'		=> $return,
														'currentReturn'	=> $recentReturn,
														'cnt'			=> $cnt,
													);
												}
												
											} // if shares != 0
											
											
										}else{
											//get all
											//Increase counter
											$cnt = $cnt + 1;
											
											//Format numbers
											$price 	= '$'.number_format($price, 2, '.', ',').'';
											$value 	= '$'.number_format($value, 2, '.', ',').'';
											$ratio 	= ''.number_format(($ratio * 100), 2, '.', '').'%';
											//$gains 	= '$'.number_format($gains, 2, '.', ',').'';
											$today 	= ''.number_format($today, 2, '.', '').'%';
											$return = ''.number_format($return, 2, '.', '').'%';
											
											
											
											if($shares == '0'){
												$currentReturn = '0.00';	
											}else{
												$currentReturn = $recentReturn;	
											}
											
											//check for shorts
											if($shares >= '0'){
												//Create Array to work with so we dont have to keep going back to the database
												$aStrat[$symbol] = array(
													'symbol'		=> $symbol,
													'price'			=> $price,
													'shares'		=> $shares,
													'value'			=> $value,
													'ratio'			=> $ratio,
													'gains'			=> $gains,
													'today'			=> $today,
													'return'		=> $return,
													'currentReturn'	=> $currentReturn,
													'cnt'			=> $cnt,
												);
											}elseif($shares < 0){
												$aStratShort[$symbol] = array(
													'symbol'		=> $symbol,
													'price'			=> $price,
													'shares'		=> $shares,
													'value'			=> $value,
													'ratio'			=> $ratio,
													'gains'			=> $gains,
													'today'			=> $today,
													'return'		=> $return,
													'currentReturn'	=> $currentReturn,
													'cnt'			=> $cnt,
												);
											}
										}
										
									}//end price and share check
								
								$asOfDate = date('m/d/Y h:i:s', $position['timestamp']);
								
								} //end while loop
								
								
								
								//Get array of current holdings price and today values
								$aStockList = array();
								foreach($aStrat as $key=>$stock){
										
										$pullStockSymbol = str_replace('.','/', $stock['symbol']);
										
										$aStockList[] = "'".$pullStockSymbol."'";	
								}
								foreach($aStratShort as $key=>$stock){
										
										$pullStockSymbol = str_replace('.','/', $stock['symbol']);
										
										$aStockList[] = "'".$pullStockSymbol."'";	
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
										
										$getStockSymbol	= str_replace('/', '.',$foo['symbol']);
										
										$aStockInfo[$getStockSymbol] = array(
												'stockPrice'	=> $foo['CurrentPrice'],
												'today'			=> $foo['chang']
										);
								}
								
								
								
								
								//Count array
								$cntPos = count($aStrat);
								
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
								
								
								//START Create function to print each row
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
									
									$gains = $aSort['gains'];
									
									if($gains < 0){
										$gains = str_replace('-','',$gains);
										$gains = '-$'.number_format($gains,2,'.',',');	
									}else{
										$gains = '$'.number_format($gains,2,'.',',');	
									}
									
									
									$label = $aSort['label'];
										
									if($label == '' || $label == NULL){
										$label = 'Add Label';	
									}
									
									if($_REQUEST['convertBtn'] == 1){
										$btnClass = '';		
									}else{
										$btnClass = 'btn btn-default btn-xs';	
									}
									
									//Create var to hold HTML
									$printRow = '
										<tr class="'.$trClass.'" '.$addStyle.'>
											<td><a href="#stock-info" class="'.$btnClass.'" data-toggle="modal" onclick="loadStockInfo(\''.$aSort['symbol'].'\');" >'.$aSort['symbol'].'</a></td>
											<td><a href="#global-label" class="'.$btnClass.'" data-toggle="modal" onclick="getLabel(\''.$aSort['fund_id'].'\',\''.$aSort['symbol'].'\');">'.$label.'</a></td>
											<td>'.$aSort['price'].'</td>
											<td>'.number_format($aSort['shares'],0,'.',',').'</td>
											<td>'.$aSort['value'].'</td>
											<td>'.$aSort['ratio'].'</td>
											<td>'.$gains.'</td>
											<td>'.$aSort['today'].'</td>
											<td>'.$aSort['return'].'</td>
											<td>'.number_format($aSort['currentReturn'],2,'.',',').'%</td>
											<td><a href="#stock-details" class="'.$btnClass.'" data-toggle="modal" onclick="loadPD(\''.$aSort['fund_id'].'\',\''.$aSort['symbol'].'\');">Details</a></td>
											'.$addRowSpan.'
										</tr>
									';
									
									//Return data out of function
									return $printRow;
											
								}
								//END printRows Function
								
								$newCnt = 0;
								
								foreach($aStrat as $key=>$pos){
									
									$newCnt++;
									
									$aLabelInfo = getPosLabel($mLink, $fundID, $pos['symbol']);
									$label		= $aLabelInfo['label'];
									
									//Build new array of values to be used in function (makes it easier to pass to the function, one var instead of a bunch)
									$aSort = array(
										'symbol'		=> $pos['symbol'],
										'price'			=> '$'.number_format($aStockInfo[$pos['symbol']]['stockPrice'],2,'.',','),
										'shares'		=> $pos['shares'],
										'value'			=> '$'.number_format(($pos['shares'] * $aStockInfo[$pos['symbol']]['stockPrice']),2,'.',','),
										'ratio'			=> $pos['ratio'],
										'gains'			=> $pos['gains'],
										'today'			=> number_format($aStockInfo[$pos['symbol']]['today'],2,'.',',').'%',
										'return'		=> $pos['return'],
										'currentReturn'	=> $pos['currentReturn'],
										'cnt'			=> $newCnt,//$pos['cnt']
										'fund_id'		=> $fundID,
										'label'			=> $label,
										'debug'			=> $debug
										
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
										if($newCnt/*$pos['cnt']*/ <= $topRows){
											
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
										if($newCnt/*$pos['cnt']*/ <= ($topRows + $midRows) && $newCnt/*$pos['cnt']*/ > $topRows){
											
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
										if($newCnt/*$pos['cnt']*/ <= ($topRows + $midRows + $bottomRows) && $newCnt/*$pos['cnt']*/ > ($topRows + $midRows)){
											
											//Increment section counter by 1
											$btmCnt = $btmCnt + 1;
											
											//Add section vars to array (these determin color and position)
											$aSort['secCnt'] = $btmCnt;
											$aSort['trColor'] = 'red';
											$aSort['section'] = 'BOTTOM';
											$aSort['secRows'] = $bottomRows;
											
											//Print the row
											echo printRows($aSort);
										}
										//END: BOTTOM SECTION
									}
									
								} // foreach $aStrat
								
								//END: Query for displaying Position Stratification
								?>
                                
                            
                            </tbody>
                        </table>
                        <?php //if($_SESSION['admin'] == '1' || $_REQUEST['debug'] == '1'){?>
                        <small>As of: <strong><?php echo $asOfDate; ?> EST</strong></small>
                        <?php //} ?>
                        
                        <?php if($_REQUEST['debug']=='1'){?>
                        <pre>
                        <?php 
						// DEBUG AREA
						
						print_r($aStockInfo);
						
						print_r($aStrat);
						
						echo $cntPos;
						
						echo $topRows;
						echo "|";
						echo $midRows;
						echo "|";
						echo $bottomRows;
						
						
						?>
                        </pre>
                        <?php } ?>
                        
                    	</div><!--portlet-body-->
                    </div><!--portlet-->
                    <!-- END SAMPLE TABLE PORTLET-->
                	
                    <?php if(!empty($aStratNoPrice)){?>
                    <!-- BEGIN Positions/Stratifications PORTLET-->
                    <div class="portlet">
                        <div class="portlet-title">
                            <div class="caption"><span class="label label-warning"><i class="icon- icon-warning-sign"></i></span> Positions That Are Not Pricing</div>
                            
                        </div><!--portlet-title-->
                        <div class="portlet-body flip-scroll">
                        
                        <div class="note note-warning">
                        	<p>We are showing some positions in your account that are not pricing! If this error occurs during normal market hours, please try the following:
                            <br />(1) <a href="process/ajax/fund-strat-basic-processes.php?process=refresh-strat&fund=<?php echo $fundID;?>">Click Here</a> to refresh your stratification data, if that does not work,
                            <br />(2) <a href="?page=08-01-00-001&type=pp&subtype=fund_issue" target="_blank">Click Here</a> to submit a support ticket with the stock symbols below.</p>
                        </div>
                        
                        <table id="fund-zone-table" class="table table-bordered table-striped table-condensed flip-content">
                            <thead class="flip-content">
                                <tr style="background-color:#5bc0de;color:#ffffff;">
                                    <th <?php echo $symbolColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=symbol<?php echo $addGetAll;?>" style=" <?php if($symbolSort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">SYMBOL <i class="icon-sort<?php if($symbolSort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th>LABEL</th>
                                    <th <?php echo $priceColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=price<?php echo $addGetAll;?>" style=" <?php if($priceSort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">PRICE <i class="icon-sort<?php if($priceSort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th <?php echo $sharesColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=shares<?php echo $addGetAll;?>" style=" <?php if($sharesSort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">SHARES <i class="icon-sort<?php if($sharesSort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th <?php echo $valueColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=value<?php echo $addGetAll;?>" style=" <?php if($valueSort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">VALUE <i class="icon-sort<?php if($valueSort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th <?php echo $percentColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=percent<?php echo $addGetAll;?>" style=" <?php if($percentSort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">PORTION OF FUND <i class="icon-sort<?php if($percentSort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th <?php echo $gainsColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=gains<?php echo $addGetAll;?>" style=" <?php if($gainsSort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">GAINS <i class="icon-sort<?php if($gainsSort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th <?php echo $todayColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=today<?php echo $addGetAll;?>" style=" <?php if($todaySort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">TODAY <i class="icon-sort<?php if($todaySort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th <?php echo $inceptColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=incept<?php echo $addGetAll;?>" style=" <?php if($inceptSort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">INCEPTION RETURN <i class="icon-sort<?php if($inceptSort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th <?php echo $currentColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=current<?php echo $addGetAll;?>" style=" <?php if($currentSort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">CURRENT RETURN <i class="icon-sort<?php if($currentSort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            
                            <?php
							
							
							
							$newCnt = 0;
								
								foreach($aStratNoPrice as $key=>$pos){
									
									$newCnt++;
									
									//Build new array of values to be used in function (makes it easier to pass to the function, one var instead of a bunch)
									$aSort = array(
										'symbol'		=> $pos['symbol'],
										'price'			=> '$'.number_format($aStockInfo[$pos['symbol']]['stockPrice'],2,'.',','),
										'shares'		=> $pos['shares'],
										'value'			=> '$'.number_format(($pos['shares'] * $aStockInfo[$pos['symbol']]['stockPrice']),2,'.',','),
										'ratio'			=> $pos['ratio'],
										'gains'			=> $pos['gains'],
										'today'			=> number_format($aStockInfo[$pos['symbol']]['today'],2,'.',',').'%',
										'return'		=> $pos['return'],
										'currentReturn'	=> $pos['currentReturn'],
										'cnt'			=> $newCnt,//$pos['cnt']
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
										if($newCnt/*$pos['cnt']*/ <= $topRows){
											
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
										if($newCnt/*$pos['cnt']*/ <= ($topRows + $midRows) && $newCnt/*$pos['cnt']*/ > $topRows){
											
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
										if($newCnt/*$pos['cnt']*/ <= ($topRows + $midRows + $bottomRows) && $newCnt/*$pos['cnt']*/ > ($topRows + $midRows)){
											
											//Increment section counter by 1
											$btmCnt = $btmCnt + 1;
											
											//Add section vars to array (these determin color and position)
											$aSort['secCnt'] = $btmCnt;
											$aSort['trColor'] = 'red';
											$aSort['section'] = 'BOTTOM';
											$aSort['secRows'] = $bottomRows;
											
											//Print the row
											echo printRows($aSort);
										}
										//END: BOTTOM SECTION
									}
									
								} // foreach $aStrat
								
								//END: Query for displaying Position Stratification
								?>
                                
                            
                            </tbody>
                        </table>
                
                        
                    	</div><!--portlet-body-->
                    </div><!--portlet-->
                    <!-- END SAMPLE TABLE PORTLET-->
                    <?php } ?>
                    
                    
                    <?php if(!empty($aStratShort)){?>
                    <!-- BEGIN Positions/Stratifications PORTLET-->
                    <div class="portlet">
                        <div class="portlet-title">
                            <div class="caption"><span class="label label-warning"><i class="icon- icon-warning-sign"></i></span> SHORT Positions/Stratification</div>
                            
                        </div><!--portlet-title-->
                        <div class="portlet-body flip-scroll">
                        
                        <div class="note note-warning">
                        	<p>We are showing some short positions in your account! This most likely indicates some sort of error in our system, perhaps due to a corporate action. Please try the following:
                            <br />(1) <a href="process/ajax/fund-strat-basic-processes.php?process=refresh-strat&fund=<?php echo $fundID;?>">Click Here</a> to refresh your stratification data, if that does not work,
                            <br />(2) <a href="?page=08-01-00-001&type=pp&subtype=fund_issue" target="_blank">Click Here</a> to submit a support ticket with the stock symbols below.</p>
                        </div>
                        
                        <table id="fund-zone-table" class="table table-bordered table-striped table-condensed flip-content">
                            <thead class="flip-content">
                                <tr style="background-color:#5bc0de;color:#ffffff;">
                                    <th <?php echo $symbolColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=symbol<?php echo $addGetAll;?>" style=" <?php if($symbolSort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">SYMBOL <i class="icon-sort<?php if($symbolSort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th>LABEL</th>
                                    <th <?php echo $priceColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=price<?php echo $addGetAll;?>" style=" <?php if($priceSort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">PRICE <i class="icon-sort<?php if($priceSort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th <?php echo $sharesColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=shares<?php echo $addGetAll;?>" style=" <?php if($sharesSort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">SHARES <i class="icon-sort<?php if($sharesSort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th <?php echo $valueColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=value<?php echo $addGetAll;?>" style=" <?php if($valueSort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">VALUE <i class="icon-sort<?php if($valueSort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th <?php echo $percentColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=percent<?php echo $addGetAll;?>" style=" <?php if($percentSort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">PORTION OF FUND <i class="icon-sort<?php if($percentSort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th <?php echo $gainsColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=gains<?php echo $addGetAll;?>" style=" <?php if($gainsSort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">GAINS <i class="icon-sort<?php if($gainsSort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th <?php echo $todayColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=today<?php echo $addGetAll;?>" style=" <?php if($todaySort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">TODAY <i class="icon-sort<?php if($todaySort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th <?php echo $inceptColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=incept<?php echo $addGetAll;?>" style=" <?php if($inceptSort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">INCEPTION RETURN <i class="icon-sort<?php if($inceptSort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th <?php echo $currentColor;?>>
                                    	<a href="?page=03-01-03-001&fund=<?php echo $fundID;?>&sort=current<?php echo $addGetAll;?>" style=" <?php if($currentSort != 'set'){echo 'color:#ffffff;';}else{echo 'color:#5BC0DE !important;';}?>">CURRENT RETURN <i class="icon-sort<?php if($currentSort == 'set'){echo'-down';}?>"></i></a>
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            
                            <?php
							
							
							
							$newCnt = 0;
								
								foreach($aStratShort as $key=>$pos){
									
									$newCnt++;
									
									//Build new array of values to be used in function (makes it easier to pass to the function, one var instead of a bunch)
									$aSort = array(
										'symbol'		=> $pos['symbol'],
										'price'			=> '$'.number_format($aStockInfo[$pos['symbol']]['stockPrice'],2,'.',','),
										'shares'		=> $pos['shares'],
										'value'			=> '$'.number_format(($pos['shares'] * $aStockInfo[$pos['symbol']]['stockPrice']),2,'.',','),
										'ratio'			=> $pos['ratio'],
										'gains'			=> $pos['gains'],
										'today'			=> number_format($aStockInfo[$pos['symbol']]['today'],2,'.',',').'%',
										'return'		=> $pos['return'],
										'currentReturn'	=> $pos['currentReturn'],
										'cnt'			=> $newCnt,//$pos['cnt']
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
										if($newCnt/*$pos['cnt']*/ <= $topRows){
											
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
										if($newCnt/*$pos['cnt']*/ <= ($topRows + $midRows) && $newCnt/*$pos['cnt']*/ > $topRows){
											
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
										if($newCnt/*$pos['cnt']*/ <= ($topRows + $midRows + $bottomRows) && $newCnt/*$pos['cnt']*/ > ($topRows + $midRows)){
											
											//Increment section counter by 1
											$btmCnt = $btmCnt + 1;
											
											//Add section vars to array (these determin color and position)
											$aSort['secCnt'] = $btmCnt;
											$aSort['trColor'] = 'red';
											$aSort['section'] = 'BOTTOM';
											$aSort['secRows'] = $bottomRows;
											
											//Print the row
											echo printRows($aSort);
										}
										//END: BOTTOM SECTION
									}
									
								} // foreach $aStrat
								
								//END: Query for displaying Position Stratification
								?>
                                
                            
                            </tbody>
                        </table>
                
                        
                    	</div><!--portlet-body-->
                    </div><!--portlet-->
                    <!-- END SAMPLE TABLE PORTLET-->
                    <?php } ?>
                    
                    
                </div><!--END COLUMN-->
            </div><!--END ROW-->
            <!-- END PAGE CONTENT-->
         
            