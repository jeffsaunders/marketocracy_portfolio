
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
         <div class="row profile">
            <div class="col-md-12">
               <!--BEGIN TABS-->
               <div class="tabbable tabbable-custom" id="profile-overview">
                  <ul class="nav nav-tabs">
                     <li class="active"><a href="#tab_1_1" data-toggle="tab">Overview</a></li>
                  </ul>
                  <div class="tab-content">
                     <div class="tab-pane active" id="tab_1_1">
                        <div class="row">
                           <div class="col-md-3">
                              <ul class="list-unstyled profile-nav">
                                 <li><img src="images/person-icon.png" class="img-responsive" alt="" /> 
                                 </li>
                                 <li><a href="#">Projects</a></li>
                                 <li><a href="#">Message</a></li>
                                 <li><a href="#">Connections<span>102</span></a></li>
                              </ul>
                           </div>
                           <div class="col-md-9">
                              <div class="row">
                                 <div class="col-md-8 profile-info">
                                    <h1>John Doe</h1>
                                    <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt laoreet dolore magna aliquam tincidunt erat volutpat laoreet dolore magna aliquam tincidunt erat volutpat.</p>
                                    <p><a href="#">www.mywebsite.com</a></p>
                                    <ul class="list-inline">
                                       <li><i class="icon-map-marker"></i> Fort Worth, Texas</li>
                                       <li><i class="icon-calendar"></i> 12 March 1987</li>
                                       <li><i class="icon-briefcase"></i> Marketing</li>
                                       <li><i class="icon-star"></i> Top Trader</li>
                                       <li><i class="icon-heart"></i> Stocks</li>
                                    </ul>
                                 </div>
                                 <!--end col-md-8-->
                                 <div class="col-md-4">
                                    <div class="portlet sale-summary">
                                       <div class="portlet-title">
                                          <div class="caption">Funds Summary</div>
                                          <div class="tools">
                                             <a class="reload" href="javascript:;"></a>
                                          </div>
                                       </div>
                                       <div class="portlet-body">
                                          <ul class="list-unstyled">
                                             <li>
                                                <span class="sale-info">TODAY SOLD <i class="icon-img-up"></i></span> 
                                                <span class="sale-num">23</span>
                                             </li>
                                             <li>
                                                <span class="sale-info">WEEKLY SALES <i class="icon-img-down"></i></span> 
                                                <span class="sale-num">87</span>
                                             </li>
                                             <li>
                                                <span class="sale-info">TOTAL SOLD</span> 
                                                <span class="sale-num">2377</span>
                                             </li>
                                             <li>
                                                <span class="sale-info">CASH</span> 
                                                <span class="sale-num">$37,990</span>
                                             </li>
                                          </ul>
                                       </div>
                                    </div>
                                 </div>
                                 <!--end col-md-4-->
                              </div>
                              <!--end row-->
                              
                              <div class="tabbable tabbable-custom tabbable-custom-profile" id="profile-sections">
                                 <ul class="nav nav-tabs">
                                    <li class="active"><a href="#tab_1_11" data-toggle="tab">Price History</a></li>
                                    <li ><a href="#tab_1_22" data-toggle="tab">Fund Info</a></li>
                                    <li ><a href="#tab_1_33" data-toggle="tab">Sector</a></li>
                                    <li ><a href="#tab_1_44" data-toggle="tab">Style</a></li>
                                    <li ><a href="#tab_1_55" data-toggle="tab">Recent Returns</a></li>
                                    <li ><a href="#tab_1_66" data-toggle="tab">Turnover</a></li>
                                 </ul>
                                 <div class="tab-content">
                                    
                                    <div class="tab-pane active" id="tab_1_11">
                                       
                                       <!-- START PRICE HISTORY-->
                                       <div class="portlet">
                                          <div class="portlet-title">
                                             <div class="caption"><i class="icon-bar-chart"></i>Price History</div>
                                             <div class="tools">
                                                <a href="" class="collapse"></a>
                                                <a href="" class="reload"></a>
                                             </div><!--tools-->
                                          </div><!--portlet-title-->
                                          <div class="portlet-body">
                                             <div id="site_statistics_loading">
                                                <img src="images/loading.gif" alt="loading"/>
                                             </div>
                                             <div id="site_statistics_content" class="display-none">
                                                <div id="site_statistics" class="chart"></div>
                                             </div>
                                          </div><!--portlet-body-->
                                       </div><!--portlet-->
                                       <!-- END PRICE HISTORY-->
                                       
                                    </div><!--tab-pane-->
                                    
                                    <div class="tab-pane" id="tab_1_22">
                                       <!--START FUND INFORMATION-->
                                       <div class="portlet">
                                          <div class="portlet-title">
                                             <div class="caption"><i class="icon-bell"></i>Fund Information</div>
                                             <div class="tools">
                                                <a href="" class="collapse"></a>
                                                <a href="" class="reload"></a>
                                             </div>
                                          </div><!--portlet-title-->
                                          <div class="portlet-body">
                                            
                                            
                                            <table class="table table-striped table-bordered table-hover">
                                               <thead>
                                                  <tr>
                                                     <th>Fund Manager:</th>
                                                     <th>Total Net Assets:</th>
                                                  </tr>
                                               </thead>
                                               <tbody>
                                                  <tr>
                                                     <td class="success">John Doe</td>
                                                     <td class="success">$24,934,943.59</td>
                                                  </tr>
                                               </tbody>
                                            </table>
                                            
                                            <table class="table table-striped table-bordered table-hover">
                                                <thead>
                                                  <tr>
                                                     <th>Inception:</th>
                                                     <th>Ticker Symbol:</th>
                                                     <th># of Securities:</th>
                                                  </tr>
                                               </thead>
                                               <tbody>
                                                  <tr>
                                                     <td>January 29, 2003</td>
                                                     <td>JDM</td>
                                                     <td>32</td>
                                                  </tr>
                                               </tbody>
                                            </table>
                                            
                                            <table class="table table-bordered">
                                               <thead>
                                                  <tr>
                                                     <th>Description:</th>
                                                  </tr>
                                               </thead>
                                               <tbody>
                                                  <tr>
                                                     <td><p>This fund primarily takes advantage of short term fluctuation in the market by swing trading long and short positions. Long term positions will be established in companies that are resistant to recession or exhibit strong growth. Short positions will be established with the use of short ETF.</p></td>
                                                  </tr>
                                               </tbody>
                                            </table>
                                            
                                          </div><!--portlet-body-->
                                       </div><!--portlet-->
                                       <!--END FUND INFORMATION-->
                                    </div><!--tab-pane-->
                                    
                                    <div class="tab-pane" id="tab_1_33">
                                       <!--START FUND POSITIONS BY SECTORS-->
                                       <div class="portlet">
                                          <div class="portlet-title">
                                             <div class="caption"><i class="icon-bell"></i>Fund Positions by Sectors and Industries</div><!--caption-->
                                             <div class="tools">
                                                <a href="" class="collapse"></a>
                                                <a href="" class="reload"></a>
                                             </div>
                                          </div><!--portlet-title-->
                                          <div class="portlet-body">
                                          
                                             <table class="table">
                                                <tbody>
                                                <tr>
                                                    <td>
                                                    <canvas id="canvas" width="150"></canvas>
                                                    <script>
                                                       var pieData = [
                                                              {
                                                                  value: 28.68,
                                                                  color:"#306"
                                                              },
                                                              {
                                                                  value : 20.23,
                                                                  color : "#093"
                                                              },
                                                              {
                                                                  value : 13.25,
                                                                  color : "#660"
                                                              },
                                                              {
                                                                  value : 13.23,
                                                                  color : "#00C"
                                                              },
                                                              {
                                                                  value : 9.51,
                                                                  color : "#F90"
                                                              },
                                                              {
                                                                  value : 4.43,
                                                                  color : "#F69"
                                                              },
                                                              {
                                                                  value : 4.34,
                                                                  color : "#93C"
                                                              },
                                                              {
                                                                  value : .34,
                                                                  color : "#FF6"
                                                              }
                                                          
                                                          ];
                                                       
                                                       var myPie = new Chart(document.getElementById("canvas").getContext("2d")).Pie(pieData);
                                                       
                                                    </script>
                                                    </td>
                                                    <td>
                                                
                                                    <table class="table table-striped table-bordered table-hover">
                                                       <thead>
                                                          <tr>
                                                             <th>Color</th>
                                                             <th>Name</th>
                                                             <th>Allocation</th>
                                                             <th>Today</th>
                                                             <th>Inception Return</th>
                                                          </tr>
                                                       </thead>
                                                       <tbody>
                                                          <tr>
                                                             <td style="background-color:#306;"></td>
                                                             <td>Financials</td>
                                                             <td>28.68%</td>
                                                             <td>-0.10%</td>
                                                             <td>0.90%</td>
                                                          </tr>
                                                          <tr>
                                                             <td style="background-color:#093;"></td>
                                                             <td>Energy (Energy)</td>
                                                             <td>20.23%</td>
                                                             <td>-0.27%</td>
                                                             <td>0.94%</td>
                                                          </tr>
                                                          <tr>
                                                             <td style="background-color:#660;"></td>
                                                             <td>Industrials (Transportation)</td>
                                                             <td>13.25%</td>
                                                             <td>0.18%</td>
                                                             <td>6.72%</td>
                                                          </tr>
                                                          <tr>
                                                             <td style="background-color:#00C;"></td>
                                                             <td>Health Care (Pharmaceuticals &amp; Biotechnology)</td>
                                                             <td>13.23%</td>
                                                             <td>-3.03%</td>
                                                             <td>4.30%</td>
                                                          </tr>
                                                          <tr>
                                                             <td style="background-color:#F90;"></td>
                                                             <td>Information Technology</td>
                                                             <td>9.51%</td>
                                                             <td>0.95%</td>
                                                             <td>1.37%</td>
                                                          </tr>
                                                          <tr>
                                                             <td style="background-color:#F69;"></td>
                                                             <td>Materials (Diversified Metals &amp; Mining)</td>
                                                             <td>4.43%%</td>
                                                             <td>-0.77%</td>
                                                             <td>1.32%</td>
                                                          </tr>
                                                          <tr>
                                                             <td style="background-color:#93C;"></td>
                                                             <td>Consumer Discretionary</td>
                                                             <td>4.34%</td>
                                                             <td>-0.52%</td>
                                                             <td>2.12%</td>
                                                          </tr>
                                                          <tr>
                                                             <td style="background-color:#FF6;"></td>
                                                             <td>Consumer Staples</td>
                                                             <td>0.34%</td>
                                                             <td>-0.30%</td>
                                                             <td>-0.41%</td>
                                                          </tr>
                                                          
                                                       </tbody>
                                                    </table>
                                                    </td>
                                                    </tr>
                                                    </tbody>
                                                    </table>
                                              
                                          </div><!--end portlet body-->
                                       </div><!--end portlet-->
                                       <!--END FUND POSITIONS BY SECTOR-->
                                    </div><!--tab-pane-->
                                    
                                    <div class="tab-pane" id="tab_1_44">
                                       <!--START FUND POSITIONS BY STYLE-->
                                       <div class="portlet">
                                          <div class="portlet-title">
                                             <div class="caption"><i class="icon-bell"></i>Fund Positions by Style</div><!--caption-->
                                             <div class="tools">
                                                <a href="" class="collapse"></a>
                                                <a href="" class="reload"></a>
                                             </div>
                                          </div><!--portlet-title-->
                                          <div class="portlet-body">
                                          
                                             <table class="table">
                                                <tbody>
                                                <tr>
                                                    <td>
                                                    <canvas id="canvas2" width="150"></canvas>
                                                    <script>
                                                       var pieData = [
                                                              {
                                                                  value: 16.59,
                                                                  color:"#306"
                                                              },
                                                              {
                                                                  value : 12.56,
                                                                  color : "#093"
                                                              },
                                                              {
                                                                  value : 11.94,
                                                                  color : "#660"
                                                              },
                                                              {
                                                                  value : 11.21,
                                                                  color : "#00C"
                                                              },
                                                              {
                                                                  value : 9.68,
                                                                  color : "#F90"
                                                              },
                                                              {
                                                                  value : 8.31,
                                                                  color : "#F69"
                                                              },
                                                              {
                                                                  value : 6.70,
                                                                  color : "#93C"
                                                              },
                                                              {
                                                                  value : 5.90,
                                                                  color : "#FF6"
                                                              },
                                                              {
                                                                  value : 5.47,
                                                                  color : "#0F0"
                                                              },
                                                              {
                                                                  value : 4.55,
                                                                  color : "#0FF"
                                                              },
                                                              {
                                                                  value : 3.75,
                                                                  color : "#09F"
                                                              },
                                                              {
                                                                  value : 3.33,
                                                                  color : "#C03"
                                                              }
                                                          
                                                          ];
                                                       
                                                       var myPie = new Chart(document.getElementById("canvas2").getContext("2d")).Pie(pieData);
                                                       
                                                    </script>
                                                    </td>
                                                    <td>
                                                
                                                    <table class="table table-striped table-bordered table-hover">
                                                       <thead>
                                                          <tr>
                                                             <th>Color</th>
                                                             <th>Name</th>
                                                             <th>Allocation</th>
                                                             <th>Today</th>
                                                             <th>Inception Return</th>
                                                          </tr>
                                                       </thead>
                                                       <tbody>
                                                          <tr>
                                                             <td style="background-color:#306;"></td>
                                                             <td>Micro Cap : Growth</td>
                                                             <td>16.59%</td>
                                                             <td>-0.15%</td>
                                                             <td>0.62%</td>
                                                          </tr>
                                                          <tr>
                                                             <td style="background-color:#093;"></td>
                                                             <td>Small Cap : Growth</td>
                                                             <td>12.56%</td>
                                                             <td>-0.68%</td>
                                                             <td>0.10%</td>
                                                          </tr>
                                                          <tr>
                                                             <td style="background-color:#660;"></td>
                                                             <td>Large Cap : Growth</td>
                                                             <td>11.94%</td>
                                                             <td>0.00%</td>
                                                             <td>0.81%</td>
                                                          </tr>
                                                          <tr>
                                                             <td style="background-color:#00C;"></td>
                                                             <td>Micro Cap : Value</td>
                                                             <td>11.21%</td>
                                                             <td>-4.49%</td>
                                                             <td>4.00%</td>
                                                          </tr>
                                                          <tr>
                                                             <td style="background-color:#F90;"></td>
                                                             <td>Unclassified Market Cap : Unclassified Style</td>
                                                             <td>9.68%</td>
                                                             <td>0.88%</td>
                                                             <td>0.90%</td>
                                                          </tr>
                                                          <tr>
                                                             <td style="background-color:#F69;"></td>
                                                             <td>Large Cap : Blend</td>
                                                             <td>8.31%</td>
                                                             <td>0.81%</td>
                                                             <td>0.57%</td>
                                                          </tr>
                                                          <tr>
                                                             <td style="background-color:#93C;"></td>
                                                             <td>Mid Cap : Growth</td>
                                                             <td>6.70%</td>
                                                             <td>-0.27%</td>
                                                             <td>0.50%</td>
                                                          </tr>
                                                          <tr>
                                                             <td style="background-color:#FF6;"></td>
                                                             <td>Micro Cap : Blend</td>
                                                             <td>5.90%</td>
                                                             <td>1.43%</td>
                                                             <td>0.68%</td>
                                                          </tr>
                                                          <tr>
                                                             <td style="background-color:#0F0;"></td>
                                                             <td>Small Cap : Value</td>
                                                             <td>5.47%</td>
                                                             <td>0.32%</td>
                                                             <td>4.40%</td>
                                                          </tr>
                                                          <tr>
                                                             <td style="background-color:#0FF;"></td>
                                                             <td>Mid Cap : Value</td>
                                                             <td>4.55%</td>
                                                             <td>-1.77%</td>
                                                             <td>1.26%</td>
                                                          </tr>
                                                          <tr>
                                                             <td style="background-color:#09F;"></td>
                                                             <td>Large Cap : Value</td>
                                                             <td>3.75%</td>
                                                             <td>-0.65%</td>
                                                             <td>0.94%</td>
                                                          </tr>
                                                          <tr>
                                                             <td style="background-color:#C03;"></td>
                                                             <td>Large Cap : Unclassified Style</td>
                                                             <td>3.33%</td>
                                                             <td>0.22%</td>
                                                             <td>6.94%</td>
                                                          </tr>
                                                          
                                                       </tbody>
                                                    </table>
                                                    </td>
                                                    </tr>
                                                    </tbody>
                                                    </table>
                                              
                                          </div><!--end portlet body-->
                                       </div><!--end portlet-->
                                       <!--END FUND POSITIONS BY STYLE-->
                                    </div><!--tab-pane-->
                                    
                                    <div class="tab-pane" id="tab_1_55">
                                       <!-- START RECENT RETURNS-->
                                       <div class="portlet">
                                          <div class="portlet-title">
                                             <div class="caption"><i class="icon-comments"></i>Recent Returns</div>
                                             <div class="tools">
                                                <a href="" class="collapse"></a>
                                                <a href="" class="reload"></a>
                                             </div>
                                          </div>
                                          <div class="portlet-body" id="chats">
                                             <table class="table table-striped table-bordered table-hover">
                                               <thead>
                                                  <tr>
                                                     <th>Period</th>
                                                     <th class="success">Returns</th>
                                                     <th>S&amp;P 500 Returns</th>
                                                     <th class="warning">Returns VS S&amp;P 500</th>
                                                  </tr>
                                               </thead>
                                               <tbody>
                                                  <tr>
                                                    <th>Last Week</th>
                                                    <td class="success">-3.49%</td>
                                                    <td>-2.62%</td>
                                                    <td class="warning">-0.87%</td>
                                                  </tr>
                                                  <tr>
                                                    <th>Last Month</th>
                                                    <td class="success">-1.00%</td>
                                                    <td>-2.23%</td>
                                                    <td class="warning">1.23%</td>
                                                  </tr>
                                                  <tr>
                                                    <th>Last 3 Months</th>
                                                    <td class="success">6.57%</td>
                                                    <td>2.26%</td>
                                                    <td class="warning">4.31%</td>
                                                  </tr>
                                                  <tr>
                                                    <th>Last 6 Months</th>
                                                    <td class="success">17.36%</td>
                                                    <td>6.94%</td>
                                                    <td class="warning">10.42%</td>
                                                  </tr>
                                                  <tr>
                                                    <th>Last 12 Months</th>
                                                    <td class="success">51.31%</td>
                                                    <td>22.33%</td>
                                                    <td class="warning">28.97%</td>
                                                  </tr>
                                                  <tr>
                                                    <th>Last 2 Years</th>
                                                    <td class="success">68.06%</td>
                                                    <td>42.27%</td>
                                                    <td class="warning">25.79%</td>
                                                  </tr>
                                                  <tr>
                                                    <th>Last 3 Years</th>
                                                    <td class="success">119.56%</td>
                                                    <td>47.93%</td>
                                                    <td class="warning">71.63%</td>
                                                  </tr>
                                                  <tr>
                                                    <th>Last 5 Years</th>
                                                    <td class="success">408.20%</td>
                                                    <td>139.86%</td>
                                                    <td class="warning">268.35%</td>
                                                  </tr>
                                                  <tr>
                                                    <th>Since Inception</th>
                                                    <td class="success">1906.95%</td>
                                                    <td>159.14%</td>
                                                    <td class="warning">1747.81%</td>
                                                  </tr>
                                                  <tr>
                                                    <th>(Annualized)</th>
                                                    <td class="success">31.35%</td>
                                                    <td>9.04%</td>
                                                    <td class="warning">22.31%</td>
                                                  </tr>
                                               </tbody>
                                               
                                            </table>
                                          </div><!--portlet-body-->
                                       </div><!--portlet-->
                                       <!-- END RECENT RETURNS-->
                                    </div><!--tab-pane-->
                                    
                                    <div class="tab-pane" id="tab_1_66">
                                       <!--START TURNOVER-->
                                       <div class="portlet">
                                          <div class="portlet-title">
                                             <div class="caption"><i class="icon-bell"></i>Turnover</div>
                                             <div class="tools">
                                                <a href="" class="collapse"></a>
                                                <a href="" class="reload"></a>
                                             </div>
                                          </div><!--portlet-title-->
                                          <div class="portlet-body">
                                            
                                            <table class="table table-striped table-bordered table-hover">
                                               <tbody>
                                                  <tr>
                                                     <th>Last Month</th>
                                                     <td>53.26%</td>
                                                  </tr>
                                                  <tr>
                                                     <th>Last 3 Months</th>
                                                     <td>244.27%</td>
                                                  </tr>
                                                  <tr>
                                                     <th>Last 6 Months</th>
                                                     <td>352.56%</td>
                                                  </tr>
                                                  <tr>
                                                     <th>Last 12 Months</th>
                                                     <td>868.49%</td>
                                                  </tr>
                                               </tbody>
                                            </table>
                                            
                                          </div><!--portlet-body-->
                                       </div><!--portlet-->
                                       <!--END TURNOVER-->
                                    </div><!--tab-pane-->
                                    
                                    
                                 </div><!--tab-content-->
                              </div><!--END TAB SECTION-->
                              
                           </div><!--END MAIN RIGHT COL-->
                        </div><!--END ROW-->
                        
                     </div><!--END TAB PANE-->
                     <!--tab_1_2-->
                     
                     
                  </div>
               </div>
               <!--END TABS-->
            </div>
         </div>
        