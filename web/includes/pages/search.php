<?php
/*
SEARCH - HTML FILE

SUPPORTING FILES:
_______________________________________________________________

AJAX 			- process/ajax/search-processes.php
PHP JAVASCRIPT	- includes/scripts/search.js.php
HTML 			- THIS FILE includes/pages/search.php
_______________________________________________________________

*/
?>
            <!--START MODALS-->
            <!-- BEGIN CREATE TOPIC MODAL-->
            <div class="modal fade" id="create-topic" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                 <div class="modal-content">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h3>Forum Disscussion</h3>
                       <h4 class="modal-title">Create New Topic For (<?php echo $stockSymbol; ?>) <?php echo $stockCompany;?></h4>
                    </div>
                    
                    <form action="process/ajax/community-forum-processes.php?process=15" method="post" name="create-topic" class="create-topic">
                        <div class="modal-body">
                                <div id="createTopicUserError"></div>
                                <div class="form-body">
                                    <div class="form-group">
                                        <label class="control-label">Topic Title * <span id="topic-help"></span></label>
                                        <input type="text" class="form-control" name="topic_title" id="topic_title" />
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Post * <span id="post-help"></span></label>
                                        <textarea class="form-control wysihtml5" name="topic_post" id="topic_post" row="5" style="resize:vertical;"></textarea>
                                    </div>
                                    <span>* Required Fields</span>
                                    <input type="hidden" name="symbol" value="<?php echo $stockSymbol; ?>" />
                                    <input type="hidden" name="company" value="<?php echo $stockCompany;?>" />
                                    <input type="hidden" name="members" value="<?php echo implode(',', $aMemberIds);?>" />
                                </div><!--form-body-->
                            
                        </div><!--modal-body-->
                        
                        <div class="modal-footer">
                            <input type="submit" value="Create Topic" class="btn btn-info" id="submit-topic" style="display:none;"/>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    
                    </form><!--create-topic-->
                 </div>
                 <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- END CREATE TOPIC MODAL-->
            
            <!--END MODALS-->
            
            <!-- BEGIN PAGE CONTENT-->
            <div class="row">
            <div class="col-md-12">
				
                <div class="note note-info">
                    <p><?php echo $aSearch['results'];?> results (<?php echo $aSearch['time']['total_time'];?> Seconds)</p>
                </div><!--note-->               
               	
                
                
				<?php
				
				//Make function to print entries when needed
				function printResults($aResults, $secCnt){
					
					$entryCnt = 0;
					
					//PRINT Entries as List ITEMS
					foreach($aResults as $key=>$entry){
						
						$entryCnt++;
						
						// IF there are 5 or less entries print
						if($entryCnt <= 5){
							
							if($entryCnt == 1){
								$topStyle = 'class="li-top"';	
							}else{
								$topStyle = '';	
							}
							
							if($entry['special'] != ''){
								$special = $entry['special'];	
							}else{
								$special = '';	
							}
							
							$results .= '
								<li '.$topStyle.'>
									<a href="'.$entry['url'].'"><strong>'.$entry['title'].'</strong></a><br />
									
									'.$entry['subtext'].'
									'.$special.'
								</li>
							';
						}else{
							//If there are more than 5 entries hide them
							$results .= '
								<li style="display:none;" class="hide-'.$secCnt.'">
									<a href="'.$entry['url'].'"><strong>'.$entry['title'].'</strong></a><br />
									
									'.$entry['subtext'].'
									'.$special.'
								</li>
							';
						}
						
					} // END foreach $aEntries
					
					// IF there are more than 5 entries print link to display remaining results
					if($entryCnt > 5){
						$results .= '<li class="no-border" id="btn-hide-'.$secCnt.'"><a href="javascript:void(0);" onclick="showResults(\'hide-'.$secCnt.'\');">[+] View More Results</a></li>';	
					}
					
					return $results;
				}
				
				$mainCnt = 0;
				
				//Start Search Array Parse
				foreach($aSearch['search'] as $siteSec => $aSubSec){
					
					//increment counter
					$mainCnt++;
					
					//PRINT Top Level Sections
					?>
                    <!-- BEGIN <?php echo $siteSec; ?> PORTLET-->
                    <div class="portlet">
                        <div class="portlet-title">
                            <div class="caption"><i class="icon-globe"></i><?php echo $siteSec; ?></div>
                            <div class="tools">
                                <a href="javascript:;" class="collapse"></a>
                                <!--<a href="javascript:;" class="reload"></a>-->
                            </div><!--tools-->
                            
                        </div><!--portlet-title-->
                        <div class="portlet-body">
                        
                        <?php
						if($siteSec == "Stocks"){
							echo '<div class="row"><div class="col-md-6">';
							echo '<div id="container" style="height: 600px; min-width: 310px;border: 1px solid #cccccc;"></div>';	
							echo '</div><!--col-md-6-->';
						}
						
						$subSecCnt = 0;
						
						//PRINT Sub Categories
						foreach($aSubSec as $subSection => $aSubSubSec){
							
							$subSecCnt++;
							
							// START Check to see if array reaches a BREAK
							if(substr($subSection, 0, 5) == "break"){
								//if array breaks print results
								
								echo '<div class="col-md-6">';
								echo '<ul class="search-results-list">';
								echo printResults($aSubSec);
								
								echo '</ul>';
								
								
								//+-------------------------------------------------------------------------+
								//|							START STOCK SECTION								|
								//+-------------------------------------------------------------------------+
								//If member has holding of the serached stock, display them here.
								if(!empty($aStockInfo)){
								
									?>
                                    <div class="table-responsive" style="border-top:1px solid #ccc;">
                                    	<h4 style="width:100%;display:block;"><span style="float:left;display:block;margin-bottom:10px;">Your Holdings of <?php echo $stockSymbol;?></span> <span style="float:right;display:block;"><a href="?page=02-00-00-001&trade=buy&symbols=<?php echo $stockSymbol;?>" class="btn btn-xs btn-success"><i class="icon-arrow-up"></i> Buy</a> <a href="?page=02-00-00-001&trade=sell&symbols=<?php echo $stockSymbol;?>" class="btn btn-xs btn-danger"><i class="icon-arrow-down"></i> Sell</a></span><span style="clear:both;"></span></h4>
                                        <table class="table table-bordered table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Fund</th>
                                                    <th>Shares</th>
                                                    <th>Value</th>
                                                    <th>Portion of Fund</th>
                                                    <th>History</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            	<?php
												foreach($aStockInfo as $fundID => $aFund){
													
													$heldPosition = $aFund['held_position'];
													
													if($heldPosition == true){
														$tradesBTN = '<a class="btn btn-xs btn-info" href="#global-pos-details" data-toggle="modal" onclick="loadPosDetails(\''.$aFund['fund_id'].'\',\''.$stockSymbol.'\');">View Trades</a>';
														$sharesLink	= '<a href="#global-pos-details" data-toggle="modal" onclick="loadPosDetails(\''.$aFund['fund_id'].'\',\''.$stockSymbol.'\');">'.number_format($aFund['totalShares'], 0, '', ',').'</a>';
													}else{
														$tradesBTN = '<a class="btn btn-xs btn-warning" href="javascript:void(0);">Never Held</a>';
														$sharesLink	= '0';
													}
													
													echo '
                                                    <tr>
                                                        <td><a href="?page=03-01-03-001&fund='.$aFund['fund_id'].'" target="_blank">'.$aFund['fund_symbol'].'</a></td>
                                                        <td>'.$sharesLink.'</td>
                                                        <td>'.$aFund['currentValue'].'</td>
                                                        <td>'.$aFund['fundRatio'].'</td>
														<td>'.$tradesBTN.'</td>
                                                    </tr>
                                                    ';
												}
												?>
                                                
                                                
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php
								} //End Display Members holdings of searched stock
								
								// If members hold the stock that is being serached, display here (Check to see if array is not empty before printing code)
								if(!empty($aCurHolders['members'])){
									
									$numMembers = number_format(count($aCurHolders['members']), 0, '', ',');
									
									?>
                                    <div class="table-responsive" style="border-top:1px solid #ccc;">
                                    	<h4 style="width:100%;display:block;"><span style="display:block;float:left;margin-bottom:10px;">Showing <?php echo $numMembers; ?> Members That Hold <?php echo $stockSymbol;?></span> <span style="display:block;float:right;"><a href="#create-topic" data-toggle="modal" class="btn btn-xs btn-info"><i class="icon-group"></i> &nbsp;&nbsp;Start A Discussion</a></span>	<span style="clear:both;"></span></h4>
                                        <table class="table table-bordered table-striped table-hover">
                                           <thead>
                                                <tr>
                                                	<th>Member</th>
                                                    <th>Funds <small>(% of Fund)</small></th>
                                                    <th>Shares</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            	<?php
												//Loop through current holdings array
												foreach($aCurHolders['members'] as $memberID => $aFunds){
													
													//If your member id matches the member id in the array, skip it
													if($memberID != $_SESSION['member_id']){
														
														//Get member info from function
														$memberInfo = get_member($mLink, $memberID, 'all');
														
														//Initialize Vars
														$aMemberFunds 	= array();
														$totalShares	= 0;
														
														//Loop through each of the funds per member
														foreach($aFunds as $fundID => $aFund){
															
															//Put funds into an array to implode later
															$aMemberFunds[] = '<a href="?page=04-00-00-001&member='.$memberID.'&tab='.$fundID.'" target="_blank">'.$aFund['fund_symbol'].' <small>('.$aFund['fundRatio'].')</small></a>';
															
															//Add up the total ammount of shares between funds
															$totalShares = $totalShares + $aFund['totalShares'];
														}
														
														//Implode the funds array and assign to variable
														$showFunds = implode(', ', $aMemberFunds);
														
														//Create new print array so that we can sort on the newly calculated variables										
														$aPrintMember[$memberID] = array(
															'member'		=> '<a class="btn btn-xs btn-info" style="width:100%" href="'.$memberInfo['profileLink'].'" target="_blank">'.$memberInfo['username'].'</a>',
															'funds'			=> $showFunds,
															'showShares'	=> number_format($totalShares, 0, '', ','),
															'shares'		=> $totalShares
														);
														
														//Sort the new array by highest amount of shares
														usort($aPrintMember, function($a, $b) {
															return $b['shares'] - $a['shares'];
														});
														
													} // End If memberID = memberID
													
												} // End foreach($aCurHolders
												
												
												$shortCnt = 0;
												
												//Loop through the print array
												foreach($aPrintMember as $memberID => $aMember){
												
													$shortCnt++;
													
													if($shortCnt <= 5){	
														
														// Echo values to screen
														echo '
														<tr>
															<td>'.$aMember['member'].'</th>
															<td>'.$aMember['funds'].'</td>
															<td>'.$aMember['showShares'].'</td>
														</tr>
														';	
														
													}else{
														
														echo '
														<tr style="display:none;" class="show-members-'.$subSecCnt.'">
															<td>'.$aMember['member'].'</th>
															<td>'.$aMember['funds'].'</td>
															<td>'.$aMember['showShares'].'</td>
														</tr>
														';	
															
													}
												}
												
												if($shortCnt > 5){
													echo '
														<tr id="btn-show-members-'.$subSecCnt.'">
															<td colspan="3"><a href="javascript:void(0);" onclick="showResults(\'show-members-'.$subSecCnt.'\');">[+] View More Results</a></td>
														<tr>
													';	
												}
												?>
                                                
                                                
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php
								} //End Display members that hold serached stock
								//+-------------------------------------------------------------------------+
								//|							END STOCK SECTION								|
								//+-------------------------------------------------------------------------+
								
								
								
								echo '</div><!--col-md-6-->';
								break;
								
							}else{
								//If array doesnt break, continue to parse
								?>
								<div class="search-sub-sec">							
									
									<h4 style="margin-bottom:3px;"><?php echo $subSection;?></h4><hr style="margin-top:3px;" />
									
									<div class="row">
									<?php
									$secCnt = 0;
									
									//PRINT Sub Sub Categories
									foreach($aSubSubSec as $subSubSec => $aEntries){
										
										$secCnt++;
										
										?>
										<div class="search-sub-sub-sec col-md-3">
											<h5><strong><?php echo $subSubSec;?></strong></h5>
											<ul class="search-results-list">
											<?php
											
											echo printResults($aEntries, $secCnt);
											
											?>
											</ul>
										</div><!--search-sub-sub-sec-->
										<?php
									} // END foreach $aSubSubSec
									?>
									</div><!--row-->
								</div><!--search-sub-sec-->
								<?php
							} // END Check for array BREAK
								
						} // END foreach $aSubSec
						
						if($siteSec == "Stocks"){
							echo '</div><!--end row-->';	
						}
						
						
						
						//+-------------------------------------------------------------------------+
						//|							START Corporate Actions								|
						//+-------------------------------------------------------------------------+
						if($_SESSION['admin']==1){
							
							$aCA = getCA($mLink, $stockSymbol);
							
							print_r($aCA);
							
							?>
                            <div class="portlet" style="margin-top:10px;">
                                <div class="portlet-title">
                                    <div class="caption"><i class="icon-globe"></i>Corporate Actions</div>
                                    <div class="tools">
                                        <a href="javascript:;" class="collapse"></a>
                                        <!--<a href="javascript:;" class="reload"></a>-->
                                    </div><!--tools-->
                                    
                                </div><!--portlet-title-->
                                <div class="portlet-body">
                            
                                    <div class="row">
                                        
                                        <div class="col-md-6">
                                            
                                        	<?php 
											#START CASH DIV
											if(!empty($aCA['cashDiv'])){?>    
                                            <div class="table-responsive" style="border-top:1px solid #ccc;">
                                            <h4 style="width:100%;display:block;"><span style="float:left;display:block;margin-bottom:10px;">Cash Dividends paid by <?php echo $stockSymbol;?></h4>
                                           
                                                <table class="table table-bordered table-striped table-hover-alt">
                                                    <thead>
                                                        
                                                        <tr>
                                                            <th>Effective Date</th>
                                                            <th>Frequency</th>
                                                            <th>Gross</th>
                                                            <th>Description</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        foreach($aCA['cashDiv'] as $key=>$aCashDiv){
                                                            
                                                            echo '
                                                            <tr>
                                                                <td>'.date('m/d/Y',$aCashDiv['effectiveDate']).'</td>
                                                                <td>'.$aCashDiv['frequency'].'</td>
                                                                <td>$'.number_format($aCashDiv['gross'],2,'.',',').'</td>
                                                                <td>'.$aCashDiv['desc'].'</td>
                                                            </tr>
                                                            ';
                                                        
                                                        }//END FOREACH CASH DIV
                                                        ?>
                                                    </tbody>
                                                </table>        
                                            </div>
                                            <?php }//END CASH DIV?>
               
                                        </div><!--col-md-6-->
                                        
                                        <div class="col-md-6">
                                        	
                                            <?php 
											#START STOCK SPLITS
											if(!empty($aCA['stockSplits'])){?>    
                                            <div class="table-responsive" style="border-top:1px solid #ccc;">
                                            <h4 style="width:100%;display:block;"><span style="float:left;display:block;margin-bottom:10px;"><?php echo $stockSymbol;?> Stock Splits</h4>
                                           
                                                <table class="table table-bordered table-striped table-hover-alt">
                                                    <thead>
                                                        
                                                        <tr>
                                                            <th>Effective Date</th>
                                                            <th>Terms</th>
                                                            <th>Description</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        foreach($aCA['stockSplits'] as $key=>$aInfo){
                                                            
                                                            echo '
                                                            <tr>
                                                                <td>'.date('m/d/Y',$aInfo['effectiveDate']).'</td>
                                                                <td>'.$aInfo['terms'].'</td>
                                                                <td>'.$aInfo['desc'].'</td>
                                                            </tr>
                                                            ';
                                                        
                                                        }//END FOREACH CASH DIV
                                                        ?>
                                                    </tbody>
                                                </table>        
                                            </div>
                                            <?php }//END STOCK SPLITS?>
                                            
                                            <?php 
											#START NAME CHANGE
											if(!empty($aCA['nameChange'])){?>    
                                            <div class="table-responsive" style="border-top:1px solid #ccc;">
                                            <h4 style="width:100%;display:block;"><span style="float:left;display:block;margin-bottom:10px;">Name Changes for <?php echo $stockSymbol;?></h4>
                                           
                                                <table class="table table-bordered table-striped table-hover-alt">
                                                    <thead>
                                                        
                                                        <tr>
                                                            <th>Effective Date</th>
                                                            <th>Old Name</th>
                                                            <th>New Name</th>
                                                            <th>Description</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        foreach($aCA['nameChange'] as $key=>$aInfo){
                                                            
                                                            echo '
                                                            <tr>
                                                                <td>'.date('m/d/Y',$aInfo['effectiveDate']).'</td>
                                                                <td>'.$aInfo['oldName'].'</td>
																<td>'.$aInfo['newName'].'</td>
                                                                <td>'.$aInfo['desc'].'</td>
                                                            </tr>
                                                            ';
                                                        
                                                        }//END FOREACH CASH DIV
                                                        ?>
                                                    </tbody>
                                                </table>        
                                            </div>
                                            <?php }//END NAME CHANGE?>
                                            
                                            <?php 
											#START NAME CHANGE
											if(!empty($aCA['listingChange'])){?>    
                                            <div class="table-responsive" style="border-top:1px solid #ccc;">
                                            <h4 style="width:100%;display:block;"><span style="float:left;display:block;margin-bottom:10px;">Listing Changes for <?php echo $stockSymbol;?></h4>
                                           
                                                <table class="table table-bordered table-striped table-hover-alt">
                                                    <thead>
                                                        
                                                        <tr>
                                                            <th>Effective Date</th>
                                                            <th>Old Exchange</th>
                                                            <th>Old Code</th>
                                                            <th>New Exchange</th>
                                                            <th>New Code</th>
                                                            <th>Description</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        foreach($aCA['listingChange'] as $key=>$aInfo){
                                                            
                                                            echo '
                                                            <tr>
                                                                <td>'.date('m/d/Y',$aInfo['effectiveDate']).'</td>
                                                                <td>'.$aInfo['oldExchange'].'</td>
																<td>'.$aInfo['oldCode'].'</td>
																<td>'.$aInfo['newExchange'].'</td>
																<td>'.$aInfo['newCode'].'</td>
                                                                <td>'.$aInfo['desc'].'</td>
                                                            </tr>
                                                            ';
                                                        
                                                        }//END FOREACH CASH DIV
                                                        ?>
                                                    </tbody>
                                                </table>        
                                            </div>
                                            <?php }//END NAME CHANGE?>
                                            
                                            <?php 
											#START NAME CHANGE
											if(!empty($aCa['symbolChange'])){?>    
                                            <div class="table-responsive" style="border-top:1px solid #ccc;">
                                            <h4 style="width:100%;display:block;"><span style="float:left;display:block;margin-bottom:10px;">Symbol Changes for <?php echo $stockSymbol;?></h4>
                                           
                                                <table class="table table-bordered table-striped table-hover-alt">
                                                    <thead>
                                                        
                                                        <tr>
                                                            <th>Effective Date</th>
                                                            <th>Old Symbol</th>
                                                            <th>New Symbol</th>
                                                            <th>Description</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        foreach($aCa['symbolChange'] as $key=>$aInfo){
                                                            
                                                            echo '
                                                            <tr>
                                                                <td>'.date('m/d/Y',$aInfo['effectiveDate']).'</td>
                                                                <td>'.$aInfo['oldSymbol'].'</td>
																<td>'.$aInfo['newSymbol'].'</td>
                                                                <td>'.$aInfo['desc'].'</td>
                                                            </tr>
                                                            ';
                                                        
                                                        }//END FOREACH CASH DIV
                                                        ?>
                                                    </tbody>
                                                </table>        
                                            </div>
                                            <?php }//END NAME CHANGE?>
                                            
                                        </div><!--col-md-6-->
                                        
                                	</div><!--row-->
                                </div><!--portlet-body-->
                                <?php
								} //End Corporate Actions
								?>
                                
                            </div><!--portlet-->
                        
                        </div><!--portlet-body-->
                        
                    </div><!--portlet-->
                    <!-- END <?php echo $siteSec;?> PORTLET-->
                    
                    <?php
				} // End foreach $aSearch['search']
				
				if($_SESSION['admin'] == 'xx'){
                    ?>
                    <pre>
                    <?php 
					//print_r($aMemberIds);
					//print_r($aCurHolders);
					//print_r($aHolders);
					
					//print_r($aStockInfo); print_r($aStocks);
					echo $sQuery;
					//echo $error;
					//print_r($aSearch);
					echo $searchString;?> | <?php echo $filter;?>
                    </pre>
                    <?php
				}
				?>   
             
            </div>
            </div>
            <!-- END PAGE CONTENT-->
