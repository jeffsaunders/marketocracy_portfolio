<?php 
$pageID = "bench-success";
$sectionID = "funds";
$fundID = "JDM";
$pageTitle = "JDM Alpha Success";
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
                              <div class="caption"><i class="icon-reorder"></i>Alpha Success</div>
                              <div class="tools">
                                 <a href="javascript:;" class="collapse"></a>
                                 <a href="javascript:;" class="reload"></a>
                              </div>
                           </div>
                           <div class="portlet-body">
                              <h3>S&amp;P 500</h3>
                              <div class="divider"></div>
                              
                              <img src="/images/graphs/alpha-success/sp500-graph.jpg" alt="Alpha Success - S&amp;P 500" />
                              
                           </div><!--END PORTLET BODY-->
                        </div><!--END PORTLET-->
                        
                     </div><!--END TAB 1-->
                     
                     <div class="tab-pane" id="tab_1">
                     
                        <div class="portlet">
                           <div class="portlet-title">
                              <div class="caption"><i class="icon-reorder"></i>Alpha Success</div>
                              <div class="tools">
                                 <a href="javascript:;" class="collapse"></a>
                                 <a href="javascript:;" class="reload"></a>
                              </div>
                           </div>
                           <div class="portlet-body">
                           		
                              <h3>Total Market</h3>
                              <div class="divider"></div>
                              
                              <img src="/images/graphs/alpha-success/total-market-graph.jpg" alt="Alpha Success - Total Market" />
                              
                           </div><!--END PORTLET BODY-->
                        </div><!--END PORTLET-->
                        
                     </div><!--END TAB 2-->
                     
                     <div class="tab-pane" id="tab_2">
                     
                        <div class="portlet">
                           <div class="portlet-title">
                              <div class="caption"><i class="icon-reorder"></i>Alpha Success</div>
                              <div class="tools">
                                 <a href="javascript:;" class="collapse"></a>
                                 <a href="javascript:;" class="reload"></a>
                              </div>
                           </div>
                           <div class="portlet-body">
                              <div class="row">
                                	<div class="col-md-6">
                              			<h3>Your Fund</h3>
                              			<div class="divider"></div>
                              
                              			<img src="/images/graphs/alpha-success/custom-style-graph1.jpg" alt="Alpha Success - Customized by Style" />
                                	</div><!--col-md-6-->
                                    
                                    <div class="col-md-6">
                              			<h3>Benchmark</h3>
                              			<div class="divider"></div>
                              
                              			<img src="/images/graphs/alpha-success/custom-style-graph2.jpg" alt="Alpha Success - Customized by Style" />
                                	</div><!--col-md-6-->
                                </div><!--row-->
                              
                           </div><!--END PORTLET BODY-->
                        </div><!--END PORTLET-->
                        
                     </div><!--END TAB 3-->
                     
                     <div class="tab-pane" id="tab_3">
                     
                        <div class="portlet">
                           <div class="portlet-title">
                              <div class="caption"><i class="icon-reorder"></i>Alpha Success</div>
                              <div class="tools">
                                 <a href="javascript:;" class="collapse"></a>
                                 <a href="javascript:;" class="reload"></a>
                              </div>
                           </div>
                           <div class="portlet-body">
                              <div class="row">
                                	<div class="col-md-6">
                              			<h3>Your Fund</h3>
                              			<div class="divider"></div>
                              
                              			<img src="/images/graphs/alpha-success/custom-sector-graph1.jpg" alt="Alpha Success - Customized by Sector" />
                                	</div><!--col-md-6-->
                                    
                                    <div class="col-md-6">
                              			<h3>Benchmark</h3>
                              			<div class="divider"></div>
                              
                              			<img src="/images/graphs/alpha-success/custom-sector-graph2.jpg" alt="Alpha Success - Customized by Sector" />
                                	</div><!--col-md-6-->
                                </div><!--row-->
                              
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
                          <p>This chart shows your performance versus an index over various time periods. The graph measure the percentage chance a person buying your fund on an arbitrary date since inception and holding it for that number of months would have had of beating the market. While comparison versus the S&amp;P 500 is standard, the S&amp;P is not style neutral because it is all large and a few mid-cap stocks. For a bit more challenge you can also compare to a total market benchmark which has lately outperformed the S&amp;P 500.</p>
                          
                      </div><!--col-->
                      
                      <div class="col-md-4">
                          
                          <p>Similarly, your fund is probably not style-neutral, you most likely have different weightings than the market by either sector or style then the market as a whole. So you can also measure your performance compared to a custom benchmark calculated by taking your weightings by style or sector every 30 days and producing a custom index blended from all the stocks in that sector. For instance, if you invested 50% of your fund in Health Care stocks and 50% in Infotech stocks, then your custom sector benchmark will be a blend of 50% of the Energy stocks in the market and 50% of the Infotech stocks in the market.</p>
                          
                      </div><!--col-->
                      
                      <div class="col-md-4">
                
                          <p>If you choose the custom benchmark, the performance of your custom benchmark versus the total market is also shown, which indicates how much of your performance over those time periods is due to allocation by style or sector.</p>
                          
                          <p>Ideally, you want to pick the best stocks in the best sectors at the best time. In that case, both success contours will slope upwards. If the top chart of your custom benchmark slopes upwards while the bottom one slopes downwards, that means that you're picking better-then-average stocks in the out-of-favor styles or sectors. Conversely if the top chart slopes downwards while the bottom slopes upwards, that means you're picking worse stocks in styles or sectors that are doing well.</p>
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