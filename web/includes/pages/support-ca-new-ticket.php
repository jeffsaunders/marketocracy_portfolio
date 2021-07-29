        <!-- BEGIN PAGE CONTENT-->          
        <div class="row">
            <div class="col-md-12">
            
                <!-- BEGIN New Support Ticket-->
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption"><i class="icon-reorder"></i>Open Corporate Action Ticket</div>
                        <?php /*?><div class="tools">
                           <a href="javascript:;" class="collapse"></a>
                           <a href="#portlet-config" data-toggle="modal" class="config"></a>
                           <a href="javascript:;" class="reload"></a>
                           <a href="javascript:;" class="remove"></a>
                        </div><?php */?>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <form action="process/ajax/support-processes.php?process=2" method="post" class="ajax" name="support">
                                <div class="form-body">
                                  	<div id="userError">
                                    </div>
                                    	<input type="hidden" value="2" name="ticket" /><?php //Ticket Type = Corporate Action ?>
                                        
                                        
                                        <div class="form-group">
                                        	<label  class="control-label">Corporate Action Type *</label>
                                        	<select name="problem_type" id="problem_type" class="form-control" >
                                            	<?php
												$query = "
													SELECT *
													FROM ".$_SESSION['lists_options_table'] ."
													WHERE list_id=:list_id
													ORDER BY sequence ASC
												";
												
												try{
													$rsListOptions = $mLink->prepare($query);
													$aValues = array(
														':list_id' 	=> "2"
													);
													$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
													$rsListOptions->execute($aValues);
												}
												catch(PDOException $error){
													// Log any error
													file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
												}
												
												while($options = $rsListOptions->fetch(PDO::FETCH_ASSOC)) {
													if($options['disabled'] == "1"){
														$disabled = "disabled";	
													}else{
														$disabled = "";	
													}
													echo '<option value="'.$options['value'].'" '.$disabled.'>'.$options['label'].'</option>';
												
												}
												?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label  class="control-label">Stock Ticker *</label>
                                            <input type="text" name="stock_ticker" id="stock_ticker" class="form-control" placeholder="AAPL" style="width:100px;">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label  class="control-label">Ticket Subject *</label>
                                            <input type="text" class="form-control" id="subject" name="subject"> 
                                        </div>
                                        
                                        <div class="form-group">
                                        	<label class="control-label">Description / Resources *</label>
                                            <textarea name="description" id="description" class="form-control" rows="6"></textarea>
                                            <span id="desc-help" class="help-block">Please provide as much detail as possible relating to your Corporate Action request. Please note that information you provide here will be visible to the community.</span>
                                        </div>
                                        
                                        <div class="form-group">
                                        	<label class="control-label">Affected Funds</label><br  />
                                            <?php
											$query = " 
												SELECT fund_symbol, fund_id
												FROM ".$_SESSION['fund_table']."
												WHERE member_id=:member_id AND active='1'
												ORDER BY seq_id ASC
											";
											
											//Fund Symbols
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
												echo '<input type="checkbox" name="fund_symbols[]" class="form-control" value="'.$fund['fund_id'].'" /> '.$fund['fund_symbol'].'&nbsp;';
											
											}
											?>
                                          
                                        </div>
                                        
                                        <div class="form-group">
                                            <label  class="control-label">Priority</label>
                                            <select name="priority" class="form-control" >
                                            	<?php
												$query = "
													SELECT *
													FROM ".$_SESSION['lists_options_table'] ." 
													WHERE list_id=:list_id
													ORDER BY sequence ASC
												";
												
												//Fund Symbols
												try{
													$rsListOptions = $mLink->prepare($query);
													$aValues = array(
														':list_id' 	=> "4" //Priority List ID
													);
													$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
													$rsListOptions->execute($aValues);
												}
												catch(PDOException $error){
													// Log any error
													file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
												}
												
												while($options = $rsListOptions->fetch(PDO::FETCH_ASSOC)) {
													if($options['disabled'] == "1"){
														$disabled = "disabled";	
													}else{
														$disabled = "";	
													}
													echo '<option value="'.$options['value'].'" '.$disabled.' class="checkbox">'.$options['label'].'</option>';
												
												}
												?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                        	<label class="control-label">Additional Comments</label>
                                            <textarea name="comments" id="comments" class="form-control" rows="6"></textarea>
                                            <span class="help-block">If you have additional comments you would like to keep private, please provide them here.</span>
                                        </div>
                                        
                                        <div class="form-group">
                                        	<label>
                                        	<input type="checkbox" name="anonymous" class="form-control" /> Submit To Community Anonymously
                                            </label>
                                            <span class="help-block">If checked your Corporate Action will not show your name on the community boards.</span>
                                        </div>
                                        
                                        
                                </div><!--form-body-->
                                
                                <div class="form-actions">
                                        <input type="submit" value="Submit Ticket" class="btn btn-success">
                                        <?php //Form Processed by ajax | call file: includes/scripts/support-scripts.php | process file: process/ajax/support-processes.php ?>
                                </div>
                        </form>
                        <!-- END FORM--> 
                    </div>
                </div>
                <!-- END SAMPLE TABLE PORTLET-->
               
            </div><!--END COLUMN-->
        </div><!--END ROW-->
       <!-- END PAGE CONTENT-->
        