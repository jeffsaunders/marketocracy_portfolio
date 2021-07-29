<?php
/*
Marketocracy Inc. | Beta Development
Admin Member Reprice HTML

Supporting files:
	AJAX		- process/ajax/admin-member-reprice-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/admin-member-reprice.js.php
	HTML		- THIS DOCUMENT includes/pages/admin-member-reprice.php
*/
?>
         
          
         
            <!-- BEGIN PAGE CONTENT-->
            
            <!-- BEGIN FUND INFO -->
            <div class="row">
                <div class="col-md-12 col-sm-6">
                
                    <!--START FUND INFORMATION-->
                    <div class="portlet">
                        <div class="portlet-title">
                            <div class="caption"><i class="icon-cogs"></i>Admin</div>
                            <div class="tools">
                            	<a href="" class="collapse"></a>
                            	<!--<a href="" class="reload"></a>-->
                            </div>
                        </div><!--portlet-title-->
                        <div class="portlet-body">
                        	
                        </div><!--portlet-body-->
                    </div><!--portlet-->
                    <!--END FUND INFORMATION-->
                	<pre>
                    	<?php 
						// Update their heartbeat timestamp
						
						?>
                    </pre>
                </div><!--col-md-6-->
			</div><!--row-->
            <!-- END FUND INFO -->
                
            <div class="row">
                <div class="col-md-12">
                    
                    <div class="tabbable tabbable-custom">
                        
                        <ul class="nav nav-tabs">
                       		<li><a href="?page=20-00-00-001">Events Log</a></li>
                            <li><a href="?page=20-00-00-001">Currently Logged In (<?php echo $numLogged;?>)</a></li>
                            <li><a href="?page=20-00-00-001">Unique Logins (<?php echo count($aLogins);?>)</a></li>
                        	<li><a href="?page=20-00-00-003">Member Lookup</a></li>
                            <li class="active"><a href="#tab_1" data-toggle="tab">Member Reprice</a></li>
                            <li class="active"><a href="#tab_1" data-toggle="tab">CA Admin</a></li>
                        	
                        </ul>

                        <div class="tab-content">
                        
                            
                            
                            <div class="tab-pane active" id="tab_1">
                                <div class="portlet">
                                    <div class="portlet-title">
                                    	<div class="caption"><i class="icon-reorder"></i>Stock Name Change</div>
                                      	<div class="tools">
                                        	<a href="javascript:;" class="collapse"></a>
                                        	<!--<a href="javascript:;" class="reload"></a>-->
                                      	</div><!--tools-->
                                    </div><!--portlet-title-->
                                    <div class="portlet-body form">
                                            
                                            <form action="" method="post" class="reprice">
                                                <div class="form-group">
                                                    <label class="control-label">Data Type:</label>
                                                    <select name="data-type" onchange="loadField();" id="data-type" class="form-control">
                                                        <option>Select Type</option>
                                                        <?php /*?><option value="csv-member-id">Member ID (CSV)</option><?php */?>
                                                        <option value="csv-fund-key">Fund Key (CSV)</option>
                                                        <option value="field-fund-id">Fund ID (Field Comma Seperated)</option>
                                                        <option value="field-fund-key">Fund Key (Field Comma Seperated)</option>
                                                    </select>
                                                </div><!--form-group-->
                                         
                                            </form>
                                        
                                        <div id="load-field">
                                        </div><!--load-field-->
                                        
                                    </div><!--END PORTLET BODY-->
                                </div><!--END PORTLET-->
                            
                            </div><!--END TAB 2-->
                            
                            
                        
                        
                        </div><!--tab-content-->
                    </div><!--tabbable tabbable-custom-->
                    
                </div><!--col-md-12-->
            </div><!--row-->
            <!-- END PAGE CONTENT-->    
   