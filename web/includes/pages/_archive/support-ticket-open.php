        <!-- BEGIN PAGE CONTENT-->          
        <div class="row">
            <div class="col-md-12">
            
                <!-- BEGIN New Support Ticket-->
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption"><i class="icon-reorder"></i>Open Ticket</div>
                        <?php /*?><div class="tools">
                           <a href="javascript:;" class="collapse"></a>
                           <a href="#portlet-config" data-toggle="modal" class="config"></a>
                           <a href="javascript:;" class="reload"></a>
                           <a href="javascript:;" class="remove"></a>
                        </div><?php */?>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <form action="process/ajax/system-support-processes.php?process=2" method="post" class="ajax" name="support">
                                <div class="form-body">
                                  	<div id="userError">
                                    </div>
                                        <div class="form-group">
                                            <label  class="control-label">Ticket Type</label>
                                            <select name="ticket" id="ticket_type" class="form-control" >
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
														':list_id' 	=> "1"
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
                                            <?php /*?><span class="help-block">A block of help text.</span><?php */?>
                                        </div>
                                        
                                        <div id="ticket_list">
                                        	<?php //loaded by ajax | call file: includes/scripts/system-support-scripts.php | process file: process/ajax/system-support-processes.php ?>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label  class="control-label">Ticket Subject *</label>
                                            <input type="text" class="form-control" id="subject" name="subject"> 
                                        </div>
                                        
                                        <div class="form-group">
                                        	<label class="control-label">Description / Resources *</label>
                                            <textarea name="description" id="description" class="form-control" rows="6"></textarea>
                                        </div>
                                        
                                        <div class="form-group">
                                        	<label class="control-label">Affected Funds</label><br  />
                                            <?php
											$query = " 
												SELECT fund_symbol, fund_id
												FROM ".$_SESSION['fund_table']."
												WHERE member_id=:member_id
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
                                        
                                        
                                </div><!--form-body-->
                                
                                <div class="form-actions">
                                        <input type="submit" value="Submit Ticket" class="btn btn-success">
                                        <?php //Form Processed by ajax | call file: includes/scripts/system-support-scripts.php | process file: process/ajax/system-support-processes.php ?>
                                </div>
                        </form>
                        <!-- END FORM--> 
                    </div>
                </div>
                <!-- END SAMPLE TABLE PORTLET-->
               
            </div><!--END COLUMN-->
        </div><!--END ROW-->
       <!-- END PAGE CONTENT-->
        