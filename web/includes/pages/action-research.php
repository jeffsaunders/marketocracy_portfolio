<?php
/*
Marketocracy Inc. |
Action Center

Supporting files:
	AJAX		- process/ajax/action-center-processes.php
	JAVASCRIPT	- includes/scripts/action-center.js.php
	HTML		- THIS DOCUMENT includes/pages/action-center.php
*/

$tab = $_REQUEST['tab'];


$aTrackInfo 	= memberTracker($mLink, $_SESSION['member_id']);

$aFundAssets	= fundAssets($mLink, $_SESSION['member_id']);

?>      
   
        <!-- BEGIN PAGE CONTENT-->
        <div class="row profile">
            <div class="col-md-12">
            	
                <?php /*?><div class="alert alert-warning">
                    <h4>Page Under Construction</h4>
                    <p>Note: This page is still under contruction, please check back later.</p>
                </div><?php */?>
                
                <?php 
				/*if($_SESSION['admin'] == '1'){ 
					echo '<pre>';
					print_r($aFundAssets);
					echo '</pre>';
				}*/
				?>
            
                <!--BEGIN TABS-->
                <div class="tabbable tabbable-custom">
                    <?php include('includes/nav-action-center.php');?>
                    <div class="tab-content">
                      	
                        <?php if($tab == ''){?>
                    		
                            <div class="tab-pane <?php if($tab == ""){?>active<?php } ?>" id="<?php echo $tab;?>" >
                            	<div class="row">
                                	
                                    <div class="col-md-8">
                                    	
                                        
                                        
                                        
                                        
                                        <div class="portlet" style="border-radius:0px !important;">
                                            <div class="portlet-title">
                                                <div class="caption">Managers You Are Tracking</div>
                                                <div class="tools">
                                                    <a href="" class="collapse ballon"></a>
                                                </div><!--tools-->
                                            </div><!--portlet-title-->
                                            <div class="portlet-body" style="background: #FAFAFA;">
                                            
                                            	<table class="table table-striped table-bordered table-hover table-full-width" id="tracking_table">
                                                    <thead>
                                                        <tr>
                                                            <th>Manager</th>
                                                            <th>Fund Symbol</th>
                                                            <th>Compliant</th>
                                                            <th>% Cash</th>
                                                            <th>NAV</th>
                                                            <th>AAR</th>
                                                            <th>Return Last Week</th>
                                                            <?php /*?><th>Return Today</th><?php */?>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="load-tracked-managers">
                                                    	<?php /*?><tr>
                                                        	<td><a href="javascript:void(0);">Justin Uyehara</a></td>
                                                            <td><a href="javascript:void(0);">HMF</a></td>
                                                            <td><span class="label label-success">Yes</span></td>
                                                            <td>4.39%</td>
                                                            <td>$213.15</td>
                                                            <td>30.82%</td>
                                                            <td>4.57%</td>
                                                            <td>2.11%</td>
                                                            <td><a href="javascript:void(0);"><i class="icon-ban-circle"></i> Stop Tracking</a></td>
                                                        </tr><?php */?>
                                                    </tbody>
                                            	</table>
                                                
                                            </div><!--portlet-body-->
                                        </div><!--end-portlet-->
                                        
                                        
                                        
                                    </div><!--col-md-8-->
                                    
                                    <div class="col-md-4">
                                    	
                                        
                                        <div class="portlet">
                                            <div class="portlet-title">
                                                <div class="caption">Top 10 Most Held Stocks</div>
                                                <div class="tools">
                                                    <a class="reload" href="javascript:;"></a>
                                                </div><!--tools-->
                                            </div><!--portlet-title-->
                                            <div class="portlet-body" style="background: #FAFAFA;" >
                                            	
                                                <table class="table table-striped table-bordered table-hover table-full-width" id="tracking_table">
                                                    <thead>
                                                        <tr>
                                                            <th>Stock</th>
                                                            <th># of Funds Held</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="load-top-ten-stocks">
                                                    	<tr>
                                                        	<td colspan="2">Loading <img src="images/loading.gif" alt="..." /></td>
                                                        </tr>
                                                    	<?php /*?><tr>
                                                        	<td><a href="javascript:void(0);">Justin Uyehara</a></td>
                                                            <td><a href="javascript:void(0);">HMF</a></td>
                                                            <td><span class="label label-success">Yes</span></td>
                                                            <td>4.39%</td>
                                                            <td>$213.15</td>
                                                            <td>30.82%</td>
                                                            <td>4.57%</td>
                                                            <td>2.11%</td>
                                                            <td><a href="javascript:void(0);"><i class="icon-ban-circle"></i> Stop Tracking</a></td>
                                                        </tr><?php */?>
                                                    </tbody>
                                            	</table>
                                                
                                                
                                                
                                            </div><!--portlet-body-->
                                        </div><!--end-portlet-->
                                        
                                        
                                            
                                    </div><!--col-md-4-->
                                    
                                </div><!--row-->
                            </div><!--tab-pane-->
                            
             			<?php } //end if tab = dashboard?>
                    </div><!--tab-content-->
                </div><!--tabbable
                <!--END TABS-->
                
                
            
            </div><!--col-md-12-->
        </div><!--row profile-->
        <!-- END PAGE CONTENT-->
        
        <?php if($_SESSION['admin'] == 1){?>
        <pre>
        <?php echo print_r($member); ?>
        </pre>
        <?php } ?>
      