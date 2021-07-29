<?php 
$pageID = "bench-trailing";
$sectionID = "funds";
$fundID = "JDM";
$pageTitle = "JDM Alpha Trailing";
include('includes/html-header.php');

?>
<!-- BEGIN BODY -->
<body class="page-header-fixed">
   <?php include('includes/top-header.php'); ?>
   
   
   <!-- BEGIN CONTAINER -->
   <div class="page-container">
      <?php include('includes/sidebar.php'); ?>
      <!-- BEGIN PAGE -->  
      <div class="page-content">
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
         
         <div class="row">
            <div class="col-md-12">
               <div class="tabbable tabbable-custom">
                  <ul class="nav nav-tabs">
                     <li class="active"><a href="#tab_0" data-toggle="tab">S&amp;P 500</a></li>
                     <li><a href="#tab_1" data-toggle="tab">Total Market</a></li>
                     <li><a href="#tab_2" data-toggle="tab">Customized by Style</a></li>
                     <li><a href="#tab_3" data-toggle="tab">Customized by Sector</a></li>
                  </ul>
                  <div class="tab-content">
                     <div class="tab-pane active" id="tab_0">
                     
                        <div class="portlet">
                           <div class="portlet-title">
                              <div class="caption"><i class="icon-reorder"></i>Alpha Trailing</div>
                              <div class="tools">
                                 <a href="javascript:;" class="collapse"></a>
                                 <a href="javascript:;" class="reload"></a>
                              </div>
                           </div>
                           <div class="portlet-body">
                              <h3>S&amp;P 500</h3>
                              <div class="divider"></div>
                              
                              <img src="/images/graphs/alpha-trailing/sp500-graph.jpg" alt="Alpha Trailing - S&amp;P 500" />
                              
                           </div><!--END PORTLET BODY-->
                        </div><!--END PORTLET-->
                        
                     </div><!--END TAB 1-->
                     
                     <div class="tab-pane" id="tab_1">
                     
                        <div class="portlet">
                           <div class="portlet-title">
                              <div class="caption"><i class="icon-reorder"></i>Alpha Trailing</div>
                              <div class="tools">
                                 <a href="javascript:;" class="collapse"></a>
                                 <a href="javascript:;" class="reload"></a>
                              </div>
                           </div>
                           <div class="portlet-body">
                           		
                              <h3>Total Market</h3>
                              <div class="divider"></div>
                              
                              <img src="/images/graphs/alpha-trailing/total-market-graph.jpg" alt="Alpha Trailing - Total Market" />
                              
                           </div><!--END PORTLET BODY-->
                        </div><!--END PORTLET-->
                        
                     </div><!--END TAB 2-->
                     
                     <div class="tab-pane" id="tab_2">
                     
                        <div class="portlet">
                           <div class="portlet-title">
                              <div class="caption"><i class="icon-reorder"></i>Alpha Trailing</div>
                              <div class="tools">
                                 <a href="javascript:;" class="collapse"></a>
                                 <a href="javascript:;" class="reload"></a>
                              </div>
                           </div>
                           <div class="portlet-body">
                           		
                              <h3>Customized by Style</h3>
                              <div class="divider"></div>
                              
                              <img src="/images/graphs/alpha-trailing/custom-style-graph.jpg" alt="Alpha Trailing - Customized by Style" />
                              
                           </div><!--END PORTLET BODY-->
                        </div><!--END PORTLET-->
                        
                     </div><!--END TAB 3-->
                     
                     <div class="tab-pane" id="tab_3">
                     
                        <div class="portlet">
                           <div class="portlet-title">
                              <div class="caption"><i class="icon-reorder"></i>Alpha Trailing</div>
                              <div class="tools">
                                 <a href="javascript:;" class="collapse"></a>
                                 <a href="javascript:;" class="reload"></a>
                              </div>
                           </div>
                           <div class="portlet-body">
                           		
                              <h3>Customized by Sector</h3>
                              <div class="divider"></div>
                              
                              <img src="/images/graphs/alpha-trailing/custom-sector-graph.jpg" alt="Alpha Trailing - Customized by Sector" />
                              
                           </div><!--END PORTLET BODY-->
                        </div><!--END PORTLET-->
                        
                     </div><!--END TAB 4-->
                     
                     
                  </div><!--tab content-->
               </div><!--tab container-->
            </div><!--col-->
         </div><!--row-->
         <!-- END PAGE CONTENT-->
         
          <div class="portlet">
             <div class="portlet-title">
                <div class="caption"><i class="icon-book"></i>Trade School</div>
                <div class="tools">
                   <a href="javascript:;" class="collapse"></a>
                   <a href="javascript:;" class="reload"></a>
                </div>
             </div>
             <div class="portlet-body">
                
                <h3>How does this make me a better investor?</h3>
                <div class="divider"></div>
                
                <div class="row">
                      <div class="col-md-4">
                          <p>This chart shows your performance versus an index over all 30 day time periods since inception. By looking at the depth and breadth of periods of underperformance, you can get a sense of your funds volatility versus the benchmark.</p>
                          
                          <p>While comparison versus the S&amp;P 500 is standard, the S&amp;P is not style neutral because it is all large and a few mid-cap stocks. For a bit more challenge you can also compare to a total market benchmark which has lately outperformed the S&amp;P 500.</p>
                          
                      </div><!--col-->
                      
                      <div class="col-md-4">
                          
                          <p>Similarly, your fund is probably not style-neutral, you most likely have different weightings than the market by either sector or style then the market as a whole. So you can also measure your performance compared to a custom benchmark calculated by taking your weightings by style or sector every 30 days and producing a custom index blended from all the stocks in that sector. For instance, if you invested 50% of your fund in Health Care stocks and 50% in Infotech stocks, then your custom sector benchmark will be a blend of 50% of the Energy stocks in the market and 50% of the Infotech stocks in the market.</p>
                          
                      </div><!--col-->
                      
                      <div class="col-md-4">
                
                          
                      </div><!--col-->
                      
                      
                </div><!--row-->
             </div><!--END PORTLET BODY-->
          </div><!--END PORTLET-->
             
      </div>
      <!-- END PAGE -->  
   </div>
   <!-- END CONTAINER -->
   
   <?php include('includes/footer.php'); ?>
   
   <!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
   <!-- BEGIN CORE PLUGINS -->   
   <!--[if lt IE 9]>
   <script src="plugins/respond.min.js"></script>
   <script src="plugins/excanvas.min.js"></script> 
   <![endif]-->   
   <script src="plugins/jquery-1.10.2.min.js" type="text/javascript"></script>
   <script src="plugins/jquery-migrate-1.2.1.min.js" type="text/javascript"></script>
   <!-- IMPORTANT! Load jquery-ui-1.10.3.custom.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
   <script src="plugins/jquery-ui/jquery-ui-1.10.3.custom.min.js" type="text/javascript"></script>      
   <script src="plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
   <script src="plugins/bootstrap-hover-dropdown/twitter-bootstrap-hover-dropdown.min.js" type="text/javascript" ></script>
   <script src="plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
   <script src="plugins/jquery.blockui.min.js" type="text/javascript"></script>  
   <script src="plugins/jquery.cookie.min.js" type="text/javascript"></script>
   <script src="plugins/uniform/jquery.uniform.min.js" type="text/javascript" ></script>
   <!-- END CORE PLUGINS -->
   
   <!-- BEGIN PAGE LEVEL PLUGINS -->
   <script type="text/javascript" src="plugins/select2/select2.min.js"></script>
   <!-- END PAGE LEVEL PLUGINS -->
   
   <!-- BEGIN PAGE LEVEL SCRIPTS -->
   <script src="js/app.js"></script>
   <script src="js/form-samples.js"></script>   
   <!-- END PAGE LEVEL SCRIPTS -->
   
   <script>
      jQuery(document).ready(function() {    
         // initiate layout and plugins
         App.init();
         FormSamples.init();
      });
   </script>
   <!-- END JAVASCRIPTS -->   
</body>
<!-- END BODY -->
</html>