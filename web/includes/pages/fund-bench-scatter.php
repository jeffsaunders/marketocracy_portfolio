
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
                              <div class="caption"><i class="icon-reorder"></i>Alpha Scatter</div>
                              <div class="tools">
                                 <a href="javascript:;" class="collapse"></a>
                                 <a href="javascript:;" class="reload"></a>
                              </div>
                           </div>
                           <div class="portlet-body">
                              <h3>S&amp;P 500</h3>
                              <div class="divider"></div>
                              
                              <img src="/images/graphs/alpha-scatter/sp500-graph.jpg" alt="Alpha Scatter - S&P 500" />
                              
                           </div><!--END PORTLET BODY-->
                        </div><!--END PORTLET-->
                        
                     </div><!--END TAB 1-->
                     
                     <div class="tab-pane" id="tab_1">
                     
                        <div class="portlet">
                           <div class="portlet-title">
                              <div class="caption"><i class="icon-reorder"></i>Alpha Scatter</div>
                              <div class="tools">
                                 <a href="javascript:;" class="collapse"></a>
                                 <a href="javascript:;" class="reload"></a>
                              </div>
                           </div>
                           <div class="portlet-body">
                           		
                              <h3>Total Market</h3>
                              <div class="divider"></div>
                              
                              <img src="/images/graphs/alpha-scatter/total-market-graph.jpg" alt="Alpha Scatter - Total Market" />
                              
                           </div><!--END PORTLET BODY-->
                        </div><!--END PORTLET-->
                        
                     </div><!--END TAB 2-->
                     
                     <div class="tab-pane" id="tab_2">
                     
                        <div class="portlet">
                           <div class="portlet-title">
                              <div class="caption"><i class="icon-reorder"></i>Alpha Scatter</div>
                              <div class="tools">
                                 <a href="javascript:;" class="collapse"></a>
                                 <a href="javascript:;" class="reload"></a>
                              </div>
                           </div>
                           <div class="portlet-body">
                           		
                              <h3>Customized by Style</h3>
                              <div class="divider"></div>
                              
                              <img src="/images/graphs/alpha-scatter/custom-style-graph.jpg" alt="Alpha Scatter - Customized by Style" />
                              
                           </div><!--END PORTLET BODY-->
                        </div><!--END PORTLET-->
                        
                     </div><!--END TAB 3-->
                     
                     <div class="tab-pane" id="tab_3">
                     
                        <div class="portlet">
                           <div class="portlet-title">
                              <div class="caption"><i class="icon-reorder"></i>Alpha Scatter</div>
                              <div class="tools">
                                 <a href="javascript:;" class="collapse"></a>
                                 <a href="javascript:;" class="reload"></a>
                              </div>
                           </div>
                           <div class="portlet-body">
                           		
                              <h3>Customized by Sector</h3>
                              <div class="divider"></div>
                              
                              <img src="/images/graphs/alpha-scatter/custom-sector-graph.jpg" alt="Alpha Scatter - Customized by Sector" />
                              
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
                          <p>This chart goes beyond alpha and beta to show you your performance versus an index over all possible 30-day periods. Each point shows you the return of the benchmark vs. your fund over a 30-day period. You can use this to see how your fund performs with respect to the market. The red line one the graph is the "beta=1" line, or it is the line where upward and downward motions exactly equal that of the market.</p>
                          
                      </div><!--col-->
                      
                      <div class="col-md-4">
                          
                          <p>While comparison versus the S&amp;P 500 is standard, the S&amp;P is not style neutral because it is all large and a few mid-cap stocks. For a bit more challenge you can also compare to a total market benchmark which has lately outperformed the S&amp;P 500.</p>
                          
                      </div><!--col-->
                      
                      <div class="col-md-4">
                
                          <p>Similarly, your fund is probably not style-neutral, you most likely have different weightings than the market by either sector or style then the market as a whole. So you can also measure your performance compared to a custom benchmark calculated by taking your weightings by style or sector every 30 days and producing a custom index blended from all the stocks in that sector. For instance, if you invested 50% of your fund in Health Care stocks and 50% in Infotech stocks, then your custom sector benchmark will be a blend of 50% of the Energy stocks in the market and 50% of the Infotech stocks in the market.</p>
                      </div><!--col-->
                      
                      
                </div><!--row-->
             </div><!--END PORTLET BODY-->
          </div><!--END PORTLET-->