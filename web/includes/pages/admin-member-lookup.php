<?php
/*
Marketocracy Inc. | Beta Development
Admin Member Lookup

Supporting files:
	AJAX		- process/ajax/admin-member-lookup-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/admin-member-lookup.js.php
	HTML		- THIS DOCUMENT includes/pages/admin-member-lookup.php
*/


$lookupMemberID	= $_REQUEST['member'];
$lookupUsername	= $_REQUEST['username'];
$lookupEmail	= $_REQUEST['email'];

?>
         	
            
            <!-- BEGIN Modal Changes-->
            <div class="modal fade" id="promote-manager" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                 <div class="modal-content">
                 	<div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Promote Manager</h4>
                    </div>
                    
                    <div class="modal-body">
                            
                        <img src="/images/ajax-loading.gif" alt="Loading" />
                        
                    </div><!--modal-body-->
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                 </div>
                 <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- END Modal Changes-->
          	
            <!-- BEGIN Member Info Modal-->
            <div class="modal fade" style="overflow:hidden;" id="view-member-info" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-full">
                 <div class="modal-content" id="load-member-data">
                 	
                    
                    
                 </div>
                 <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- END Modal Changes--
            
            <!-- BEGIN Modal Changes-->
            <div class="modal fade" id="load-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                 <div class="modal-content" id="load-modal-content">
                 	<div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Loading...</h4>
                    </div>
                    
                    <div class="modal-body">
                            
                        <img src="/images/ajax-loading.gif" alt="Loading" />
                        
                    </div><!--modal-body-->
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                 </div>
                 <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- END Modal Changes-->
            
            <!-- BEGIN CHANGE EMAIL-->
            <div class="modal fade" id="change-email" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                 <div class="modal-content">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Change Email</h4>
                    </div>
                    
                    <form action="" method="post" name="email-form" class="email-form">
                        <div class="modal-body" id="load-email">
                                
                            
                        </div><!--modal-body-->
                        
                        <div class="modal-footer">
                            <input type="submit" value="Change Email" id="submit-email" class="btn btn-info" />
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    
                    </form><!--email-form-->
                 </div>
                 <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- END CHANGE EMAIL-->
            
            <!-- BEGIN CHANGE PASSWORD-->
            <div class="modal fade" id="change-password" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                 <div class="modal-content">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Change Password</h4>
                    </div>
                    
                    <form action="" method="post" name="password-form" class="password-form">
                        <div class="modal-body" id="load-password">
                                
                            
                        </div><!--modal-body-->
                        
                        <div class="modal-footer">
                            <input type="submit" value="Change Password" id="submit-password" class="btn btn-info" />
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    
                    </form><!--email-form-->
                 </div>
                 <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- END CHANGE PASSWORD-->
            
            <!-- BEGIN CHANGE FUND SYMBOL-->
            <div class="modal fade" id="change-fund-symbol" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                 <div class="modal-content">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Change Fund Symbol</h4>
                    </div>
                    
                    <form action="" method="post" name="fund-symbol-form" class="fund-symbol-form">
                        <div class="modal-body" id="load-fund-symbol">
                                
                            
                        </div><!--modal-body-->
                        
                        <div class="modal-footer">
                            <input type="submit" value="Change Fund Symbol" id="submit-fund-symbol" class="btn btn-info" />
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    
                    </form><!--fund-symbol-form-->
                 </div>
                 <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- END CHANGE FUND SYMBOL-->
            
            <!-- BEGIN CHANGE FUND NAME-->
            <div class="modal fade" id="change-fund-name" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                 <div class="modal-content">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Change Fund Name</h4>
                    </div>
                    
                    <form action="" method="post" name="fund-name-form" class="fund-name-form">
                        <div class="modal-body" id="load-fund-name">
                                
                            
                        </div><!--modal-body-->
                        
                        <div class="modal-footer">
                            <input type="submit" value="Change Fund Name" id="submit-fund-name" class="btn btn-info" />
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    
                    </form><!--fund-name-form-->
                 </div>
                 <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- END CHANGE FUND NAME-->
            
            <!-- BEGIN RUN LIVE PRICE-->
            <div class="modal fade" id="update-live-price" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                 <div class="modal-content">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Update Live Price</h4>
                    </div>
                    
                    <form action="" method="post" name="live-price-form" class="live-price-form">
                        <div class="modal-body" id="load-live-price">
                                
                            
                        </div><!--modal-body-->
                        
                        <div class="modal-footer">
                            <input type="submit" value="Update Live Price" id="submit-live-price" class="btn btn-info" />
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    
                    </form><!--live-price-form-->
                 </div>
                 <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- END RUN LIVE PRICE-->
            
         
            <!-- BEGIN PAGE CONTENT-->
            
            <?php include('includes/pages/includes/admin-api-que.php');?>
                
            <div class="row">
                <div class="col-md-12">
                    
                    <div class="tabbable tabbable-custom">
                        
                        <?php include('includes/nav-admin-tabs.php');?>

                        <div class="tab-content">
                        
                            
                            
                            <div class="tab-pane active" id="tab_1">
                                <div class="portlet">
                                    <div class="portlet-title">
                                    	<div class="caption"><i class="icon-reorder"></i>Member Lookup</div>
                                      	<div class="tools" id="collapse-btn">
                                        	<a href="javascript:;" class="collapse"></a>
                                        	<!--<a href="javascript:;" class="reload"></a>-->
                                      	</div><!--tools-->
                                    </div><!--portlet-title-->
                                    <div class="portlet-body" id="collapse-portlet">
                                    	<form action="" method="post" class="member_lookup">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    
                                                    
                                                        
                                                        <div class="form-group">
                                                            <label class="input-label">Member ID</label>
                                                            <input type="text" name="member_id" class="form-control clear-input" value="<?php echo $lookupMemberID;?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="input-label">Username</label>
                                                            <input type="text" name="username" class="form-control clear-input" value="<?php echo $lookupUsername;?>" />
                                                        </div>
                                                        
                                                        
                                                        
                                                    
                                                    
                                                </div><!--col-->
                                                <div class="col-md-6">
                                                    
                                                    
                                                        
                                                        
                                                        
                                                        <div class="form-group">
                                                            <label class="input-label">First Name</label>
                                                            <input type="text" name="name_first" class="form-control clear-input" value="<?php echo $lookupFirstName;?>" />
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="input-label">Last Name</label>
                                                            <input type="text" name="name_last" class="form-control clear-input" value="<?php echo $lookupLastName;?>" />
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label class="input-label">Email</label>
                                                            <input type="text" name="email" class="form-control clear-input" value="<?php echo $lookupEmail;?>" />
                                                        </div>
                                                        
                                                        
                                                    
                                                    
                                                </div><!--col-->
                                            </div><!--row-->
                                            <div class="row">
                                            	<div class="col-md-12">
                                                	<input type="submit" value="Look Up Member" class="btn btn-info" id="submit-lookup"/>
                                                        
                                                        <button type="button" class="btn btn-success btn-sm" id="new-lookup">Old Search</button>
                                                </div><!--col-md-12-->
                                            </div><!--row-->
                                        </form>
                                        
                                        
                                        
                                    </div><!--END PORTLET BODY-->
                                </div><!--END PORTLET-->
                                
                                <div class="tab-pane active" id="tab_1">
                                
                                <div class="portlet">
                                    <div class="portlet-title">
                                    	<div class="caption"><i class="icon-reorder"></i>Member Info</div>
                                      	<div class="tools">
                                        	<a href="javascript:;" class="collapse"></a>
                                        	<!--<a href="javascript:;" class="reload"></a>-->
                                      	</div><!--tools-->
                                    </div><!--portlet-title-->
                                    <div class="portlet-body">
                                    
                                    	<div class="row">
                                        	<div class="col-md-12">
                                    			
                                    			<div id="load-member-list"></div>
                                                	
                                        	</div><!--col-->
                                        </div><!--row-->
                                        
                                    </div><!--END PORTLET BODY-->
                                </div><!--END PORTLET-->
                                
                                <?php /*?><div class="portlet">
                                    <div class="portlet-title">
                                    	<div class="caption"><i class="icon-reorder"></i>Member Info</div>
                                      	<div class="tools">
                                        	<a href="javascript:;" class="collapse"></a>
                                        	<!--<a href="javascript:;" class="reload"></a>-->
                                      	</div><!--tools-->
                                    </div><!--portlet-title-->
                                    <div class="portlet-body">
                                    
                                    	<div class="row">
                                        	<div class="col-md-12">
                                    			
                                    			<div id="load-member-info"></div>
                                                	
                                        	</div><!--col-->
                                        </div><!--row-->
                                        
                                    </div><!--END PORTLET BODY-->
                                </div><!--END PORTLET--><?php */?>
                            
                            </div><!--END TAB 2-->
                            
                            
                        
                        
                        </div><!--tab-content-->
                    </div><!--tabbable tabbable-custom-->
                    
                </div><!--col-md-12-->
            </div><!--row-->
            <!-- END PAGE CONTENT-->    
   