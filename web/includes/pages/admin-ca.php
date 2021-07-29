<?php
/*
Marketocracy Inc. | Beta Development
Admin Member Reprice HTML

Supporting files:
	AJAX		- process/ajax/admin-ca-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/admin-ca.js.php
	HTML		- THIS DOCUMENT includes/pages/admin-ca.php
*/
?>
         
          
         
            <!-- BEGIN PAGE CONTENT-->
            
            <?php include('includes/pages/includes/admin-api-que.php');?>
                
            <div class="row">
                <div class="col-md-12">
                    
                    <div class="tabbable tabbable-custom">
                        
                        <?php include('includes/nav-admin-tabs.php');?>

                        <div class="tab-content">
                        	
                            <?php if($_REQUEST['debug'] == 1){?>
                            <pre>
                            Function Testing<br />
                            
                            <?php
							$fundID = '2-1'; 
							print_r(checkRecords($mLink, $fundID));
							?>
                            </pre>
                            <?php }?>
                            
                            <div class="tab-pane active" id="tab_1">
                                <div class="portlet">
                                    <div class="portlet-title">
                                    	<div class="caption"><i class="icon-reorder"></i>Stock Symbol Change</div>
                                      	<div class="tools">
                                        	<a href="javascript:;" class="collapse"></a>
                                        	<!--<a href="javascript:;" class="reload"></a>-->
                                      	</div><!--tools-->
                                    </div><!--portlet-title-->
                                    <div class="portlet-body form">
                                            
                                            <form action="" method="post" class="ca-symbol-change">
                                                <div class="form-body">
                                                    
                                                    
                                                    <div class="form-group">
                                                        <label class="control-label">Old Symbol:</label>
                                                        <input type="text" name="old_symbol" class="form-control" />
                                                    </div><!--form-group-->
                                                    
                                                    <div class="form-group">
                                                        <label class="control-label">New Symbol:</label>
                                                        <input type="text" name="new_symbol" class="form-control" />
                                                    </div><!--form-group-->
                                                    
                                                    <div class="form-group">
                                                        <label class="control-label">Select API</label><br />
                                                        <select name="api-server">
                                                            <option value="api1">API1</option>
                                                            <option value="api2" selected>API2</option>
                                                            <option value="api3">API3</option>
                                                         </select>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                    	<label class="control-label"><input type="checkbox" name="all-members" /> Perform Error Recovery </label><br />
                                                        <small class="help-text">Only check this if there was an error processing a symbol change. This will check for trades for the new symbol for every member.</small>
                                                    </div>
                                                    
                                                    <input type="submit" name="ca-symbol-change" value="Run Symbol Change" class="btn btn-success" />
                                            	</div><!--form-body-->
                                                
                                            </form>
                                        
                                        <div id="symbol-change-results">
                                        
                                        
                                        </div><!--symbol-change-results-->
                                        
                                        
                                        
                                        
                                    </div><!--END PORTLET BODY-->
                                </div><!--END PORTLET-->
                                
                                <div class="portlet" id="processed-ca-area">
                                    <div class="portlet-title">
                                        <div class="caption"><i class="icon-reorder"></i>Processed</div>
                                        <div class="tools">
                                            <a href="javascript:;" class="collapse"></a>
                                            <!--<a href="javascript:;" class="reload"></a>-->
                                        </div><!--tools-->
                                    </div><!--portlet-title-->
                                    <div class="portlet-body form">
                                        
                                        <?php
										$numPerPage		=	20;
										
										if (isset($_GET["pag"])) { 
											$page  		= $_GET["pag"]; 
										}else{ 
											$page		=	1; 
										}; 
										
										$query = "
											SELECT COUNT(*) as numRows
											FROM ".$_SESSION['ca_affected_funds_table']."
											WHERE ca_type='ticker_change'
										";
										try{
											$rsGetCount = $mLink->prepare($query);
											$aValues = array();
											$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
											$rsGetCount->execute($aValues);
										}
										catch(PDOException $error){
											// Log any error
											file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
										}
										$getCount 	= $rsGetCount->fetch(PDO::FETCH_ASSOC);
										$numRows 	= $getCount['numRows'];
										
										$totalPages = ceil($numRows / $numPerPage);
										$maxPage 	= 8;
										$prevPage 	= $page - 1;
										$nextPage	= $page + 1;
										
										$loopPage = 1;
										if($page != 1){
											$loopPage = $page - 3;
											
											if($loopPage <= 0){
												$loopPage = 1;	
											}
											
											$showPag = '<a href="?page='.$pageID.'&pag=1#processed-ca-area" class="btn btn-info" style="margin-right:3px;"><i class="icon-step-backward"></i> First</a>';
											
											$showPag .= '<a href="?page='.$pageID.'&pag='.$prevPage.'#processed-ca-area" class="btn btn-info" style="margin-right:3px;">Prev</a>';	
										}
										
										$pageCnt = 0;
										for ($i=$loopPage; $i<=$totalPages; $i++) {
											$pageCnt++;
											if($pageCnt < $maxPage){
												 
												if($page == $i){
											 		$btnType = "warning";
												}else{
													$btnType= "default";
												}
												 
												$showPag .= '<a href="?page='.$pageID.'&pag='.$i.'#processed-ca-area" class="btn btn-'.$btnType.'" style="margin-right:3px;">'.$i.'</a>';
											 }
										};
										
										if($page < $totalPages){ 
											
											$showPag .= '<a href="?page='.$pageID.'&pag='.$nextPage.'#processed-ca-area" class="btn btn-info" style="margin-right:3px;">Next</a>';	
											$showPag .= '<a href="?page='.$pageID.'&pag='.$totalPages.'#processed-ca-area" class="btn btn-info">Last <i class="icon-step-forward"></i></a>';
										}
										?>
                                        
                                        <div class="row">
                                            <div class="col-md-12">
												<div class="pagination" style="float:right;margin:10px 10px 10px 0px;">
													<?php echo $showPag;?>
                                                </div>
                                            </div><!--col-md-4-->
                                        </div><!--row-->
                                            
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>PID</th>
                                                    <th>Date</th>
                                                    <th>Old Ticker</th>
                                                    <th>New Ticker</th>
                                                    <th>Funds</th>
                                                    <th>Actions</th>
                                                    
                                                </tr>
                                            </thead>
                                            <tbody>
                                                
                                                <?php
												$startFrom = ($page-1) * $numPerPage; 
												
                                                $query = "
                                                    SELECT *
                                                    FROM ".$_SESSION['ca_affected_funds_table']."
                                                    WHERE ca_type='ticker_change'
                                                    ORDER BY uid DESC
                                                    LIMIT ".$startFrom.", ".$numPerPage."
                                                ";
                                                try{
                                                    $rsGetProcess = $mLink->prepare($query);
                                                    $aValues = array(
														':start_from'	=> $startFrom,
														':num_per_page'	=> $numPerPage
													);
                                                    $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                                    $rsGetProcess->execute($aValues);
                                                }
                                                catch(PDOException $error){
                                                    // Log any error
                                                    file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                                }
                                                while($process = $rsGetProcess->fetch(PDO::FETCH_ASSOC)){
                                                    
                                                    $uid		= $process['uid'];
                                                    $aFundIDs	= explode('|', $process['fund_ids']);
                                                    $oldSymbol 	= $process['old_stock_symbol'];
                                                    $newSymbol	= $process['stock_symbol'];
                                                    $date		= date('m/d/Y h:i a', $process['timestamp']);
                                                    $stratRun	= $process['strat_run'];
													
                                                    $aListFunds	= array();
                                                    
                                                    $cnt = 0;
                                                    foreach($aFundIDs as $key=>$fundID){
                                                        
                                                        $fundSymbol = get_funds($mLink, $fundID, 'fundSymbol');
                                                        //$fundSymbol = $fundID;
                                                        $aFundID	= explode('-', $fundID);
                                                        $memberID	= $aFundID[0];
                                                        
                                                        $cnt++;
                                                        
                                                        if($cnt <= 200){
                                                            $aListFunds[] = '<a href="process/ajax/admin-switch-user.php?member='.$memberID.'&admin='.$_SESSION['member_id'].'&toggle=admin-to-user&gopage=03-01-03-001&gofund='.$fundID.'" onclick="switchUser(\''.$memberID.'\',\''.$_SESSION['member_id'].'\');">'.$fundSymbol.'</a>';	
                                                        }
                                                    }
                                                    
                                                    $listFunds = implode(', ',$aListFunds);
                                                    
                                                    $listFundIDs = implode('|', $aFundIDs);
													
													if($stratRun == NULL){
														$btnType 	= 'info';
														$btnText	= 'Run Stratification Rebuild';
													}else{
														$btnType 	= 'warning';
														$btnText	= 'Re-Run Stratification Rebuild';	
													}
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $uid;?></td>
                                                        <td><?php echo $date;?></td>
                                                        <td><?php echo $oldSymbol;?></td>
                                                        <td><?php echo $newSymbol;?></td>
                                                        <td><?php echo $listFunds;?></td>
                                                        <td>
                                                            <span id="process-btn-<?php echo $uid;?>"><a href="javascript:void(0);" class="btn btn-<?php echo $btnType;?> btn-sm" style="margin-bottom:5px;" onClick="runStrat('<?php echo $uid;?>','<?php echo $listFundIDs; ?>');"><?php echo $btnText;?></a></span>
                                                            <span id="rerun-btn-<?php echo $uid;?>"><a href="javascript:void(0);" onClick="rerunChange('<?php echo $uid;?>');" class="btn btn-default btn-sm">Re-Run Ticker Change</a></span>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                        
                                                }
                                                ?>
                                                    
                                            </tbody>
                                        </table>	
                                        
                                        <div class="row">
                                            <div class="col-md-12">
												<div class="pagination" style="float:right;margin:10px 10px 10px 0px;">
													<?php echo $showPag;?>
                                                </div>
                                            </div><!--col-md-4-->
                                        </div><!--row-->
                                        
                                    </div><!--END PORTLET BODY-->
                                </div><!--END PORTLET-->
                                
                            
                            </div><!--END TAB 2-->
                            
                            
                        
                        
                        </div><!--tab-content-->
                    </div><!--tabbable tabbable-custom-->
                    
                </div><!--col-md-12-->
            </div><!--row-->
            <!-- END PAGE CONTENT-->    
   
