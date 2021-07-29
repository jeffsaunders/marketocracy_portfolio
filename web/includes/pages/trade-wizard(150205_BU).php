<?php
/*
TRADE WIZARD - HTML SOURCE FILE

SUPPORTING FILES:
_______________________________________________________________

AJAX 			- process/ajax/trade-wizard-processes.php
PHP JAVASCRIPT	- includes/scripts/trade-wizard.js.php
HTML 			- THIS FILE includes/pages/trade-wizard.php
_______________________________________________________________

*/

$symbols = strtoupper($_REQUEST['symbols']);
$tradeType = $_REQUEST['trade'];

switch($tradeType){
	case 'buy':$tradeTypeLabel = 'Buy';break;
	case 'sell':$tradeTypeLabel = 'Sell';break;
}

?>
         <!--START PAGE MODALS-->
         <!-- BEGIN DETAILS MODAL-->         
            <div class="modal fade" id="submitting-trade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                            <h4 class="modal-title">Submitting Trades</h4>
                        </div>
                        <div class="modal-body" id="submit-trades">
                            <img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" /> Submitting Trades...
                        </div><!--modal-body-->
                        <div class="modal-footer">
                            <!--<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>-->
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- END DETAILS MODAL-->
         <!--END PAGE MODALS-->
         
         <!-- BEGIN PAGE CONTENT-->
         <div class="row">
            <div class="col-md-12">
               <div class="portlet" id="trade_wizard">
                  <div class="portlet-title">
                     <div class="caption">
                        <i class="icon-random"></i> Trade Wizard - <span class="step-title">Step 1 of 4</span>
                     </div>
                     <div class="tools hidden-xs">
                        <a href="javascript:;" class="reload"></a>
                     </div>
                  </div>
                  <div class="portlet-body form">
                     <form action="process/ajax/trade-wizard-process.php?process=9" method="post" class="form-horizontal submit-trade" id="submit_form">
                        <div class="form-wizard">
                           <div class="form-body">
                              <ul class="nav nav-pills nav-justified steps">
                                 <li>
                                    <a href="#tab1" data-toggle="tab" class="step">
                                    <span class="number">1</span>
                                    <span class="desc"><i class="icon-ok"></i> Symbols</span>   
                                    </a>
                                 </li>
                                 <li>
                                    <a href="#tab2" data-toggle="tab" class="step">
                                    <span class="number">2</span>
                                    <span class="desc"><i class="icon-ok"></i> Trades</span>   
                                    </a>
                                 </li>
                                 <li>
                                    <a href="#tab3" data-toggle="tab" class="step active">
                                    <span class="number">3</span>
                                    <span class="desc"><i class="icon-ok"></i> Analyze</span>   
                                    </a>
                                 </li>
                                 <li>
                                    <a href="#tab4" data-toggle="tab" class="step">
                                    <span class="number">4</span>


                                    <span class="desc"><i class="icon-ok"></i> Confirm</span>   
                                    </a> 
                                 </li>
                              </ul>
                              <div id="bar" class="progress progress-striped" role="progressbar">
                                 <div class="progress-bar progress-bar-success"></div>
                              </div>
                              <div class="tab-content">
                                 <div class="alert alert-danger display-none">
                                    <button class="close" data-dismiss="alert"></button>
                                    You have some form errors. Please check below.
                                 </div>
                                 <div class="alert alert-success display-none">
                                    <button class="close" data-dismiss="alert"></button>
                                    Your form validation is successful!
                                 </div>
                                 
                                 <div class="tab-pane active" id="tab1">
                                 	<div class="trade-errors"></div>
                                    <h3 class="block">Enter stock symbols</h3>
                                    
                                    <div class="form-group">
                                    	<label class="control-label col-md-3">Trade Type</label>
                                        <div class="col-md-4">
                                        	<select class="form-control" name="sell-type-front" id="sell-type-front" onchange="getSymbols();">
                                            	<?php
												if(isset($tradeType)){
													echo '<option value="'.$tradeType.'">'.$tradeTypeLabel.'</option><option disabled="disabled">-----</option>';	
												}
												?>
                                                <!--<option value="both">Buy &amp; Sell</option>-->
                                                <option value="buy">Buy</option>
                                                <option value="sell">Sell</option>
                                            </select>                                         
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                    	<label class="control-label col-md-3">Fund <span class="required">*</span></label>
                                        <div class="col-md-4" id="fund-selection">
                                        	<select class="form-control" name="funds_front" id="funds-front" onchange="getSymbols(); checkFund();">
												<option  value="">Select Fund</option>
                                                <option disabled="disabled">-----</option>
												<?php
												if($fundID != ""){
													echo '<option value="'.$fundID.'" selected="selected">'.$fundSymbol.'</option><option disabled="disabled">-----</option>';
												}
												?>
                                                
                                                <option value="all">All</option>
                                                <option disabled="disabled">-----</option>
                                                <?php
												$query = "
													SELECT fund_symbol, fund_id
													FROM ".$_SESSION['fund_table']."
													WHERE member_id=:member_id AND active='1' AND short_fund='0'
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
												
												while($fund = $rsGetFunds->fetch(PDO::FETCH_ASSOC)){
													
													if($fund['fund_id'] == $fundID){
														$disableOption = 'disabled';
													}else{
														$disableOption = '';
													}
													echo '<option value="'.$fund['fund_id'].'" '.$disableOption.'>'.$fund['fund_symbol'].'</option>';
													
												}
												?>
                                            </select>
                                            
                                                                  
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                    	<div class="col-md-3"></div>
                                    	<div class="col-md-4">
                                    		<div class="checkbox-list" id="show-funds" style="display:none;">
                                            	<?php
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
												while($fund = $rsGetFunds->fetch(PDO::FETCH_ASSOC)){
													
													?>
                                                    <label class="checkbox-inline" style="padding-left:0px;margin-left:0px;">
                                                		<input type="checkbox" name="include_funds[]" value="<?php echo $fund['fund_id'];?>" checked> <?php echo $fund['fund_symbol'];?>
                                                	</label>
                                                    <?php
												}
												?>
                                                <span class="help-block">Select/Unselect funds you want to trade.</span>
                                                <div id="form_select_funds_error"></div>
                                            </div><!--checkbox-list-->
                                    	</div>             
                                    </div>
                                    
                                    <div class="form-group">
                                       <label class="control-label col-md-3">Symbols<span class="required">*</span></label>
                                       <div class="col-md-4">
                                          <input type="text" class="form-control" name="symbols" id="symbols" value="<?php echo $symbols;?>" />
                                          <?php /*?><span class="help-block">Enter multiple stock symbols by pressing the <span class="label label-info">Tab Key</span> after each symbol.</span><?php */?>
                                          <span class="help-block">Enter multiple stock symbols seperated by a comma(,).</span>
                                       </div>
                                    </div>
                                   
                                    
                                    
                                   
                                 </div>
                                 <div class="tab-pane " id="tab2">
                                    <?php /*?><h3 class="block">Provide your profile details</h3><?php */?>
                                    <!-- BEGIN TRADE TABLE-->
                                     <div class="portlet">
                                        <div class="portlet-title">
                                           <div class="caption">
                                           	<i class="icon-random"></i> Bulk Trade</div><!--caption-->
                                           <div class="actions">
                                              
                                           </div>
                                        </div>
                                        <div class="portlet-body">
                                        	<div class="trade-errors">
                                            </div>
                                            <a href="javascript:void(0);" onclick="toggleHide('hide-cash','cash-table');" title="hide" id="hide-cash">Hide Cash Values</a>
                                        	<table class="table table-striped table-bordered table-hover table-full-width" id="cash-table">
                                            	
                                                <tr>
												<?php
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
												
												$fundCnt = 0;
												
												while($fund2 = $rsGetFunds2->fetch(PDO::FETCH_ASSOC)){
													//$totalValue = $fund2['totalValue'];
													$cashValue 	= number_format($fund2['cashValue'], 2, '.', ',');
													$fundSymbol	= $fund2['fund_symbol'];
													$fundID2	= $fund2['fund_id'];
													
													$fundCnt++;
													
													/*echo '
														<td>
																<label class="form-label"><strong>'.$fundSymbol.'</strong> Avaliable Cash:</label>
																<div class="input-icon">
																	<i class="icon-dollar"></i>
																	<input type="text" class="form-control" name="'.$fundSymbol.'-cash" id="'.$fundSymbol.'-cash" value="'.$cashValue.'" readonly/>
																</div>
																<input type="hidden" id="'.$fundSymbol.'-calc-cash" name="'.$fundSymbol.'-calc-cash" value="'.$fund2['cashValue'].'" />
																
														</td>
													';*/
													
													echo '
														<td>
																<label class="form-label"><strong>'.$fundSymbol.'</strong> Avaliable Cash:</label>
																<div class="input-icon">
																	<i class="icon-dollar"></i>
																	<input type="text" class="form-control" name="'.$fundID2.'-cash" id="'.$fundID2.'-cash" value="'.$cashValue.'" readonly/>
																</div>
																<input type="hidden" id="'.$fundID2.'-calc-cash" name="'.$fundID2.'-calc-cash" value="'.$fund2['cashValue'].'" />
																
														</td>
													';
													
													if($fundCnt > 3){
														echo '</tr><tr>';
														$fundCnt = 0;	
													}
												}
												?>
                                                <?php /*?><td><label class="form-label"><strong>BMF</strong> Avaliable Cash:</label> <input type="text" class="form-control" name="BMF-cash" id="BMF-cash" value="$9,538.11" readonly/></td>
                                                <td><label class="form-label"><strong>BMF2</strong> Avaliable Cash:</label> <input type="text" class="form-control" name="BMF-cash" id="BMF-cash" value="$237,322.00" readonly/></td><?php */?>						
                                                </tr>
                                            </table>
                                            <div class="actions" style="margin-bottom:10px;">
                                            <div class="btn-group">
                                                <a class="btn btn-info dropdown-toggle" href="#" data-toggle="dropdown">
                                                    Show Columns
                                                    <i class="icon-angle-down"></i>
                                                </a>
                                                <div class="dropdown-menu hold-on-click dropdown-checkboxes pull-right">
													<?php /*?><label><input type="checkbox" checked data-column="0">Trade Type</label><?php */?>
                                                    <label><input type="checkbox" checked id="fund-check" title="fund-col" class="col-select" onclick="showHideCol('fund-check','fund-col');">Fund</label>
                                                    <?php /*?><label><input type="checkbox" checked data-column="2">Symbol</label><?php */?>
                                                    <label><input type="checkbox" checked id="name-check" title="name-col" class="col-select" onclick="showHideCol('name-check','name-col');">Name</label>
                                                    <label><input type="checkbox" checked id="cprice-check" title="cprice-col" class="col-select" onclick="showHideCol('cprice-check','cprice-col');">Current Price</label>
                                                    <label><input type="checkbox" checked id="cshares-check" title="cshares-col" class="col-select" onclick="showHideCol('cshares-check','cshares-col');">Current Shares</label>
                                                    <label><input type="checkbox" checked id="cpos-check" title="cpos-col" class="col-select" onclick="showHideCol('cpos-check','cpos-col');">Current %</label>
                                                    <label><input type="checkbox" checked id="value-check" title="value-col" class="col-select" onclick="showHideCol('value-check','value-col');">Current Value</label>
                                                    <?php /*?><label><input type="checkbox" checked data-column="8">Shares</label><?php */?>
                                                    <label><input type="checkbox" checked id="npos-check" title="npos-col" class="col-select" onclick="showHideCol('npos-check','npos-col');">New Position</label>
                                                    <label><input type="checkbox" checked id="buy-check" title="buy-col" class="col-select" onclick="showHideCol('buy-check','buy-col');">Buy/Sell$</label>
                                                    <label><input type="checkbox" checked id="limit-check" title="limit-col" class="col-select" onclick="showHideCol('limit-check','limit-col');">Limit Price</label>
                                                </div>
                                            </div>
                                            </div>
                                            <table class="table table-striped table-bordered table-hover table-full-width load-trades" id="trade_table_1">
                                                <thead>
                                                    <tr>
                                                    	<th>Row</th>
                                                        <th><label class="control-label col-md-3">Trade Type<span class="required">*</span></label></th>
                                                        <th>Fund</th>
                                                        <th class="hidden-xs">Symbol</th>
                                                        <th class="hidden-xs" style="display:none;">Name</th>
                                                        <th class="hidden-xs">Current Price</th>
                                                        <th class="hidden-xs" style="display:none;">Current Shares</th>
                                                        <th class="hidden-xs" style="display:none;">Current %</th>
                                                        <th class="hidden-xs" style="display:none;">Current Value</th>
                                                        <th class="hidden-xs">Shares</th>
                                                        <th style="display:none;" class="hidden-xs">New Position Size</th>
                                                        <th class="hidden-xs">Buy $</th>
                                                        <th class="hidden-xs">Limit Price</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                     </div>
                                     <!-- END TRADE TABLE-->
                                    
                                    
                                    <div class="form-group">
                                       <label class="control-label col-md-3">Order Type<span class="required">*</span></label>
                                       <div class="col-md-4">
                                          <div class="radio-list">
                                             <label>
                                             <input type="radio" name="order_type[]" value="Day" data-title="Day Order" checked/>
                                             Day Order                                             </label>
                                             <label>
                                             <input type="radio" name="order_type[]" value="GTC" data-title="Good Until Canceled"/>
                                             Good Until Canceled
                                             </label> 
                                          </div>
                                          <div id="form_order_type_error"></div>
                                       </div>
                                       
                                       
                                    </div>
                                    
                                    
                                 </div><!--end tab2-->
                                 
                                 <div class="tab-pane" id="tab3">
                                 	<div class="trade-errors">
                                    </div>
                                 
                                    <h3 class="block">Analyze your buy/sell decisions by selecting any &amp; all reasons that support this trade.<br /><small><span class="label label-warning">Please note: these fields are NOT required!</span></small></h3>
                                    <div class="form-group">
                                    	<div class="col-md-2">
                                        	<div class="checkbox-list">
                                        	<label>
                                       			<input type="checkbox" name="reasons[]" value="Good News"> Good News
                                       		</label>
                                       		<label>
                                       			<input type="checkbox" name="reasons[]" value="Bad News"> Bad News
                                      		</label>
                                       		<label>
                                       			<input type="checkbox" name="reasons[]" value="Good Price"> Good Price
                                       		</label>
                                            <label>
                                       			<input type="checkbox" name="reasons[]" value="Shift in Company Focus"> Shift in Company Focus
                                       		</label>
                                            </div><!--checkbox-list-->
                                        </div><!--col-md-4-->
                                        <div class="col-md-2">
                                        	<div class="checkbox-list">
                                        	<label>
                                       			<input type="checkbox" name="reasons[]" value="Earnings Announcement"> Earnings Announcement
                                       		</label>
                                       		<label>
                                       			<input type="checkbox" name="reasons[]" value="Management Change"> Management Change
                                      		</label>
                                       		<label>
                                       			<input type="checkbox" name="reasons[]" value="Merger/Acquistion"> Merger/Acquistion
                                       		</label>
                                            <label>
                                       			<input type="checkbox" name="reasons[]" value="Analyst Recommendation"> Analyst Recommendation
                                       		</label>
                                            </div><!--checkbox-list-->
                                        </div><!--col-md-4-->
                                        
                                        <div class="col-md-2">
                                        	<div class="checkbox-list">
                                        	<label>
                                       			<input type="checkbox" name="reasons[]" value="New Product"> New Product
                                       		</label>
                                       		<label>
                                       			<input type="checkbox" name="reasons[]" value="Asset Allocation"> Asset Allocation
                                      		</label>
                                       		<label>
                                       			<input type="checkbox" name="reasons[]" value="Rule Compliance"> Rule Compliance
                                       		</label>
                                            <label>
                                       			<input type="checkbox" name="reasons[]" value="Tax Planning"> Tax Planning
                                       		</label>
                                            </div><!--checkbox-list-->
                                        </div><!--col-md-4-->
                                        
                                        <div class="col-md-2">
                                        	<div class="checkbox-list">
                                        	<label>
                                       			<input type="checkbox" name="reasons[]" value="Sector Looks Better"> Sector Looks Better
                                       		</label>
                                       		<label>
                                       			<input type="checkbox" name="reasons[]" value="Sector Looks Worse"> Sector Looks Worse
                                      		</label>
                                       		<label>
                                       			<input type="checkbox" name="reasons[]" value="Market Indicators"> Market Indicators
                                       		</label>
                                            <label>
                                       			<input type="checkbox" name="reasons[]" value="Technical Analysis"> Technical Analysis
                                       		</label>
                                            </div><!--checkbox-list-->
                                        </div><!--col-md-4-->
                                    </div>
                                    <div class="form-group">
                                    	<div class="col-md-8">
                              			<label >Detailed Reason</label>
                             			<textarea class="form-control" name="trade-desc" rows="3"></textarea>
                                        </div>
                           			</div>
                                    
                                 </div>
                                 <div class="tab-pane" id="tab4">
                                 	<div class="trade-errors">
                                    </div>
                                    <h3 class="block">Confirm Trades</h3>
                                    
                                    	<div class="load-confirm">
                                    	</div>
                                    
                                        <?php /*?><h4 class="form-section">BMF Trades</h4>
                                        
                                        <table  class="table table-striped table-bordered table-hover table-full-width">
                                            <thead>
                                                <tr>
                                                    <th>Symbol</th>
                                                    <th>Trade Type</th>
                                                    <th>Shares</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><p class="form-control-static" data-display="symbol-1"></p></td>
                                                    <td><p class="form-control-static" data-display="trade-type-1[]"></p></td>
                                                    <td><p class="form-control-static" data-display="shares-1"></p></td>
                                                    <td><p style="display:inline;">$</p><p style="display:inline;" class="form-control-static" data-display="show-buy-ammount-1"></p></td>	
                                                </tr>
                                            </tbody>
                                        </table>
                                    <?php */?>
                                    
                                    
                                    
                              </div>
                           </div>
                           <div class="form-actions fluid">
                              <div class="row">
                                 <div class="col-md-12">
                                 	<input type="hidden" name="username" value="<?php echo $_SESSION['username'];?>" />
                                    <div class="col-md-offset-3 col-md-9" id="change_button">
                                       <a href="javascript:;" class="btn btn-default button-previous">
                                       <i class="m-icon-swapleft"></i> Back 
                                       </a>
                                       <a href="javascript:;" onclick="nextButton('0')" class="btn btn-info button-next">
                                       Continue <i class="m-icon-swapright m-icon-white"></i>
                                       </a>
                                       <a href="index.php" class="btn btn-success button-submit">
                                       Submit Trade<i class="m-icon-swapright m-icon-white"></i>
                                       </a>                            
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </form>
                  </div>
               </div>
            </div>
         </div>
         <!-- END PAGE CONTENT-->    