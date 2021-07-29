<?php
/*
Marketocracy Inc. | Beta Development
Fund ADMIN HTML

Supporting files:
	AJAX		- process/ajax/fund-admin-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/fund-admin.js.php
	HTML		- THIS DOCUMENT includes/pages/fund-admin.php
*/
?>
         <!-- BEGIN SAMPLE PORTLET CONFIGURATION MODAL FORM-->         
         <div class="modal fade" id="portlet-config" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
               <div class="modal-content">
                  <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                     <h4 class="modal-title">Modal title</h4>
                  </div>
                  <div class="modal-body">
                     Widget settings form goes here
                  </div>
                  <div class="modal-footer">
                     <button type="button" class="btn btn-success">Save changes</button>
                     <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  </div>
               </div>
               <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
         </div>
         <!-- END SAMPLE PORTLET CONFIGURATION MODAL FORM-->
          
         <?php include("includes/head-bread.php"); ?>
         
         <!-- BEGIN PAGE CONTENT-->
         
         <!-- BEGIN FUND INFO -->
         <div class="row">
         	<div class="col-md-12 col-sm-12">
                <!-- BEGIN FUND INFO -->
                <?php include('includes/portlets/fund-live-info.php');?>
                <!-- END FUND INFO -->
            
                
                <!--START FUND INFORMATION-->
                 <div class="portlet">
                    <div class="portlet-title">
                       <div class="caption"><i class="icon-bell"></i>Fund Information</div>
                       <div class="tools">
                          <a href="" class="collapse"></a>
                          <!--<a href="" class="reload"></a>-->
                       </div>
                    </div><!--portlet-title-->
                    <div class="portlet-body">
                      
                      	<?php
						$query = '
							SELECT mf.*, lp.*
							FROM `members_fund` AS mf
							INNER JOIN members_fund_liveprice AS lp ON mf.fund_id=lp.fund_id
							WHERE mf.fund_id=:fund_id
						';
						
						try{
							$rsGetFund = $mLink->prepare($query);
							$aValues = array(
								':fund_id' 	=> $fundID
								
							);
							$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
							$rsGetFund->execute($aValues);
						}
						catch(PDOException $error){
							// Log any error
							file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
						}
						
						$fund = $rsGetFund->fetch(PDO::FETCH_ASSOC);
						
						$totalValue = number_format($fund['totalValue'], 2, '.', ',');
						$inception = date('F d, Y', $fund['unix_date']);
						
						$query = '
							SELECT * 
							FROM `members_fund_details` 
							WHERE fund_id=:fund_id AND date=(SELECT MAX(date) FROM members_fund_details WHERE fund_id=:fund_id)
							GROUP BY stockSymbol
						';
						
						try{
							$rsGetPos = $mLink->prepare($query);
							$aValues = array(
								':fund_id' 	=> $fundID
								
							);
							$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
							$rsGetPos->execute($aValues);
						}
						catch(PDOException $error){
							// Log any error
							file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
						}
						
						$securities = $rsGetPos->rowCount();
						
						?>
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Fund Manager:</th>
                                    <th>Total Net Assets:</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="success"><?php echo $_SESSION['first_name']; ?> <?php echo $_SESSION['last_name'];?></td>
                                    <td class="success">$<?php echo $totalValue;?></td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Inception:</th>
                                    <th>Fund Symbol:</th>
                                    <th># of Securities:</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo $inception;?></td>
                                    <td><?php echo $fund['fund_symbol']; ?></td>
                                    <td><?php echo $securities;?></td>
                                </tr>
                            </tbody>
                        </table>
                        
                      
                     
                      
                      
                      
                    </div><!--portlet-body-->
                 </div><!--portlet-->
                 <!--END FUND INFORMATION-->

            </div>
         </div>
         <!-- END FUND INFO -->
         
         <div class="row">
            <div class="col-md-12">
               <div class="tabbable tabbable-custom">
                  <ul class="nav nav-tabs">
                     <li class="active"><a href="#tab_0" data-toggle="tab">Fund Administration</a></li>
                     <li><a href="?page=10-00-00-002&account=2&myfund=<?php echo $fundID;?>">Advanced Settings</a>
                     <li><a href="?page=10-00-00-002&account=2&myfund=<?php echo $fundID;?>#go-to-advanced">Delete Fund</a></li>
                  </ul>
                  <div class="tab-content">
                     <div class="tab-pane active" id="tab_0">
                     
                        <div class="portlet">
                           <div class="portlet-title">
                              <div class="caption"><i class="icon-reorder"></i>Basic Settings</div>
                              <div class="tools">
                                 <a href="javascript:;" class="collapse"></a>
                                 <!--<a href="javascript:;" class="reload"></a>-->
                              </div>
                           </div>
                           <div class="portlet-body form">
                           	 
                              <!-- BEGIN FORM-->
                              <form action="process/ajax/fund-admin-processess.php?process=1" method="post" class="form-horizontal fund_details" name="fund_details" id="fund_details">
                                 <div class="form-body" class="update-admin">
                                  <div id="adminFormError"></div>
                                  
                                    <div class="form-group">
                                       <label  class="col-md-3 control-label">Fund Name</label>
                                       <div class="col-md-4">
                                          <input type="text" class="form-control" placeholder="Example: John Doe Fund" name="fund_name" id="fund_name" value="<?php echo $fund['fund_name'];?>">
                                          <span class="help-block">Enter the name of your fund.</span>
                                       </div>
                                    </div>
                                    
                                    <div class="form-group">
                                       <label  class="col-md-3 control-label">Fund Symbol</label>
                                       <div class="col-md-4">
                                          <input type="text" class="form-control" name="fund_symbol" placeholder="Example: JDF" value="<?php echo $fund['fund_symbol'];?>" disabled>
                                          <span class="help-block">Currently you can not change your fund symbol.</span>
                                       </div>
                                    </div>
                                    
                                    <div class="form-group last">
                                       <label  class="col-md-3 control-label">Fund Description</label>
                                       <div class="col-md-4">
                                       	  <textarea class="form-control" rows="10" name="description" id="description"><?php echo $fund['description'];?></textarea>
                                          <span class="help-block">- What is your investment objective (aggressive growth, income, long term gain, short term gains, stable growth, etc.)?<br>
- Do you focus on any one particular sector of the economy?<br>- Is the outlook favorable for that sector going forward?<br>- Do you have expertise in this area?<br>- How do you select companies to add to your fund?<br>- How do you select companies to sell from your fund?<br>- What are some of the more interesting companies in your portfolio?<br>- What firms are you adding now?</span>
                                       </div>
                                    </div>
                                    
                                   <input type="hidden" name="fund_id" value="<?php echo $fundID; ?>" />
                                 </div>
                                 <div class="form-actions fluid">
                                    <div class="col-md-offset-3 col-md-9">
                                       <button type="submit" class="btn btn-info">Update Fund</button>
                                       <a href="fund-overview.php" class="btn btn-default">Cancel</a>
                                    </div>
                                 </div>
                              </form>
                              <!-- END FORM--> 
                           </div><!--END PORTLET BODY-->
                        </div><!--END PORTLET-->
                        
                         <?php /*?><div class="portlet">
                           <div class="portlet-title">
                              <div class="caption"><i class="icon-reorder"></i>Advanced Settings</div>
                              <div class="tools">
                                 <a href="javascript:;" class="collapse"></a>
                                 <!--<a href="javascript:;" class="reload"></a>-->
                              </div>
                           </div>
                           <div class="portlet-body form">
                              <!-- BEGIN FORM-->
                              <form action="#" class="form-horizontal">
                                 <div class="form-body">
                                 	<h3 class="form-section">Make Fund Private</h3>
                                    <div class="form-group last">
                                       
                                       <div class="col-md-6">
                                          <span class="help-block">Marking this fund private will make it no longer show up on your "public" page, and it will be no longer ranked. However, it will be still available in your account, and you can still make trades to it. You might wish to do this if you are using this fund to track a private portfolio you do not wish to be ranked.
To reiterate, in order to be fair, private funds can no longer be ranked.</span>

											<label class="checkbox-inline">
                                       		<div class="checker" id="uniform-inlineCheckbox1">
                                            	<span class="un-checked">
                                                	<input type="checkbox" id="inlineCheckbox1" value="Make Private">
                                                </span>
                                            </div>
                                       I understand that this fund will no longer be ranked.
                                       </label>
                                       </div>
                                    </div>
                                    
                                 </div>
                                 <div class="form-actions fluid">
                                    <div class="col-md-6">
                                       <button type="submit" class="btn btn-info">Make Private</button>
                                    </div>
                                 </div>
                              </form>
                              <!-- END FORM--> 
                           </div><!--END PORTLET BODY-->
                        </div><!--END PORTLET--><?php */?>
                        
                        
                     </div><!--END TAB 1-->
                     <div class="tab-pane" id="tab_1">
                        <div class="portlet">
                           <div class="portlet-title">
                              <div class="caption"><i class="icon-reorder"></i>Delete Fund</div>
                              <div class="tools">
                                 <a href="javascript:;" class="collapse"></a>
                                 <a href="javascript:;" class="reload"></a>
                              </div>
                           </div>
                           <div class="portlet-body form">
                              <!-- BEGIN FORM-->
                              <p>Delete Fund is not avaliable at this time</p>
                              <?php /*?><form action="#" class="form-horizontal">
                                 <div class="form-body">
                                    <div class="form-group last">
                                       
                                       <div class="col-md-6">
                                       
                                          <p>While we encourage you to try many diverse investment strategies to find out what works best for you, you may only have 25 funds eligible for ranking at any one time. If this fund uses a strategy that has not worked for you, you can mark this fund so that it is no longer eligible for ranking by selecting the checkbox below, and clicking Delete. In general though, you should stick with a fund if you simply made a mistake as having a long track record is more important than having a perfect track record. Some of our top investors made mistakes early on at Marketocracy, but then went on to have a top fund.<br><br>Be aware that we still track and monitor your "deleted" funds, so in order to conserve site resources, you can mark no more then 25 funds total as inactive.</p>
                                          
                                        <label class="checkbox-inline">
                                       		<div class="checker" id="uniform-inlineCheckbox1">
                                            	<span class="un-checked">
                                                	<input type="checkbox" id="inlineCheckbox1" value="Make Private">
                                                </span>
                                            </div>
                                       I understand that I will no longer be able to access this fund.
                                       </label>  
                                       </div>
                                    </div>
                                    
                                 </div>
                                 <div class="form-actions fluid">
                                    <div class="col-md-6">
                                       <button type="submit" class="btn btn-danger">Delete Fund</button>
                                    </div>
                                 </div>
                              </form><?php */?>
                              <!-- END FORM--> 
                           </div><!--END PORTLET BODY-->
                        </div><!--END PORTLET-->
                        
                     </div><!--END TAB 2-->
                     
                  </div>
               </div>
            </div>
         </div>
         <!-- END PAGE CONTENT-->    
   