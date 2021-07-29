
         <!-- BEGIN SAMPLE PORTLET CONFIGURATION MODAL FORM-->         
         <div class="modal fade" id="stock-label" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
               <div class="modal-content">
                  <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                     <h4 class="modal-title">Set Label</h4>
                  </div>
                  <div class="modal-body">
                  	  <p>You can use label to enter a very small reminder about this position for use other pages. How you use this is up to you, but one suggested use is a short reminder for why you chose this position for use with the strategy report. By comparing this label to the performance of a position, you can see which strategies work best for you.</p>
                      <p>You can use the notes field for more extensive notes on a position.</p>
                     <form role="form">
                     <div class="form-group">
                     	<label>Position</label>
                        <select class="form-control">
                        	<option>AAL</option>
                            <option>PRAN</option>
                            <option>PEIX</option>
                            <option>NKTR</option>
                            <option>C</option>
                            <option>GCA</option>
                            <option>GLL</option>
                            <option>DSX</option>
                            <option>BHI</option>
                            <option>DTLK</option>
                            <option>RT</option>
                            <option>XLB</option>
                            <option>WFT</option>
                            <option>SZYM</option>
                            <option>PWRD</option>
                        </select>
                     </div>
                     <div class="form-group">
                     	<label>Label</label>
                        <input type="text" class="form-control" placeholder="Set Label">
                     </div>
                     <div class="form-group">
                     	<label>Rationale</label>
                        <textarea class="form-control" row="5"></textarea>
                     </div>
                     </form>
                  </div>
                  <div class="modal-footer">
                     <button type="button" class="btn btn-success">Save</button>
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
            
               <div class="note note-warning">
                    <h4 class="block">PAGE AVAILABLE SOON - UNDER CONSTRUCTION</h4>
                    <p>The information reflected on this page is not accurate. Please check back later.</p>
                </div><!--note-->
                
                <!-- BEGIN FUND INFO -->
                <?php include('includes/portlets/fund-live-info.php');?>
                <!-- END FUND INFO -->
                
               <!-- BEGIN SAMPLE TABLE PORTLET-->
               <div class="portlet">
                  <div class="portlet-title">
                     <div class="caption"><i class="icon-cogs"></i>Attribution by Stock Detail</div>
                     <div class="tools">
                        <a href="javascript:;" class="collapse"></a>
                        <a href="javascript:;" class="reload"></a>
                     </div>
                  </div>
                  <div class="portlet-body flip-scroll">
                     <table id="fund-zone-table" class="table table-bordered table-striped table-condensed flip-content">
                        <thead class="flip-content">
                           <tr>
                              <th class="fzt-aleft fzt-blue">SYMBOL</th>
                              <th class="fzt-blue">LABEL</th>
                              <th class="fzt-blue">SECTOR</th>
                              <th class="fzt-blue">INDUSTRY</th>
                              <th class="fzt-blue">YIELD</th>
                              <th class="fzt-blue">SELECTION RETURN</th>
                              <th class="fzt-blue">ACTIVITY</th>
                              <th class="fzt-blue">INCEPTION RETURN</th>
                              <th class="fzt-blue"></th>
                              <th class="fzt-blue"></th>
                           </tr>
                        </thead>
                        <tbody>
                           <tr class="trow-green">
                              <td>AAL</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Add Label</a></td>
                              <td>Industrials</td>
                              <td>Airlines</td>
                              <td>0.00%</td>
                              <td>29.92%</td>
                              <td>130.54%</td>
                              <td>160.46%</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Details</a></td>
                              <td rowspan="6" valign="middle" align="center"><strong>TOP</strong></td>
                           </tr>
                           <tr class="trow-light-green">
                              <td>AAL</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Add Label</a></td>
                              <td>Industrials</td>
                              <td>Airlines</td>
                              <td>0.00%</td>
                              <td>29.92%</td>
                              <td>130.54%</td>
                              <td>160.46%</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Details</a></td>
                           </tr>
                           <tr class="trow-green">
                              <td>AAL</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Add Label</a></td>
                              <td>Industrials</td>
                              <td>Airlines</td>
                              <td>0.00%</td>
                              <td>29.92%</td>
                              <td>130.54%</td>
                              <td>160.46%</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Details</a></td>
                           </tr>
                           <tr class="trow-light-green">
                              <td>AAL</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Add Label</a></td>
                              <td>Industrials</td>
                              <td>Airlines</td>
                              <td>0.00%</td>
                              <td>29.92%</td>
                              <td>130.54%</td>
                              <td>160.46%</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Details</a></td>
                           </tr>
                           <tr class="trow-green">
                              <td>AAL</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Add Label</a></td>
                              <td>Industrials</td>
                              <td>Airlines</td>
                              <td>0.00%</td>
                              <td>29.92%</td>
                              <td>130.54%</td>
                              <td>160.46%</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Details</a></td>
                           </tr>
                           <tr class="trow-light-green">
                              <td>AAL</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Add Label</a></td>
                              <td>Industrials</td>
                              <td>Airlines</td>
                              <td>0.00%</td>
                              <td>29.92%</td>
                              <td>130.54%</td>
                              <td>160.46%</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Details</a></td>
                           </tr>
                           
                          
                           
                           <tr class="trow-yellow" style="border-top:5px solid #006da4;">
                              <td>GCA</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Add Label</a></td>
                              <td>Information Technology</td>
                              <td>Data Processing &amp; Outsourced Services</td>
                              <td>0.00%</td>
                              <td>16.18%</td>
                              <td>-12.28%</td>
                              <td>3.90%</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Details</a></td>
                              <td rowspan="6" align="center" valign="middle"><strong>MIDDLE</strong></td>
                           </tr>
                           <tr class="trow-light-yellow">
                              <td>GCA</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Add Label</a></td>
                              <td>Information Technology</td>
                              <td>Data Processing &amp; Outsourced Services</td>
                              <td>0.00%</td>
                              <td>16.18%</td>
                              <td>-12.28%</td>
                              <td>3.90%</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Details</a></td>
                           </tr>
                           <tr class="trow-yellow">
                              <td>GCA</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Add Label</a></td>
                              <td>Information Technology</td>
                              <td>Data Processing &amp; Outsourced Services</td>
                              <td>0.00%</td>
                              <td>16.18%</td>
                              <td>-12.28%</td>
                              <td>3.90%</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Details</a></td>
                           </tr>
                           <tr class="trow-light-yellow">
                              <td>GCA</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Add Label</a></td>
                              <td>Information Technology</td>
                              <td>Data Processing &amp; Outsourced Services</td>
                              <td>0.00%</td>
                              <td>16.18%</td>
                              <td>-12.28%</td>
                              <td>3.90%</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Details</a></td>
                           </tr>
                           <tr class="trow-yellow">
                              <td>GCA</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Add Label</a></td>
                              <td>Information Technology</td>
                              <td>Data Processing &amp; Outsourced Services</td>
                              <td>0.00%</td>
                              <td>16.18%</td>
                              <td>-12.28%</td>
                              <td>3.90%</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Details</a></td>
                           </tr>
                           <tr class="trow-light-yellow">
                              <td>GCA</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Add Label</a></td>
                              <td>Information Technology</td>
                              <td>Data Processing &amp; Outsourced Services</td>
                              <td>0.00%</td>
                              <td>16.18%</td>
                              <td>-12.28%</td>
                              <td>3.90%</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Details</a></td>
                           </tr>
                           
                           
                           
                           <tr class="trow-red"style="border-top:5px solid #006da4;">
                              <td>RT</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Add Label</a></td>
                              <td>Consumer Discretionary</td>
                              <td>Restaurants</td>
                              <td>0.00%</td>
                              <td>-26.55%</td>
                              <td>23.08%</td>
                              <td>-3.47%</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Details</a></td>
                              <td rowspan="6" align="center" valign="middle"><strong>BOTTOM</strong></td>
                           </tr>
                           <tr>
                              <td class="trow-light-red">RT</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Add Label</a></td>
                              <td>Consumer Discretionary</td>
                              <td>Restaurants</td>
                              <td>0.00%</td>
                              <td>-26.55%</td>
                              <td>23.08%</td>
                              <td>-3.47%</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Details</a></td>
                           </tr>
                           <tr class="trow-red">
                              <td>RT</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Add Label</a></td>
                              <td>Consumer Discretionary</td>
                              <td>Restaurants</td>
                              <td>0.00%</td>
                              <td>-26.55%</td>
                              <td>23.08%</td>
                              <td>-3.47%</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Details</a></td>
                           </tr>
                           <tr>
                              <td class="trow-light-red">RT</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Add Label</a></td>
                              <td>Consumer Discretionary</td>
                              <td>Restaurants</td>
                              <td>0.00%</td>
                              <td>-26.55%</td>
                              <td>23.08%</td>
                              <td>-3.47%</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Details</a></td>
                           </tr>
                           <tr class="trow-red">
                              <td>RT</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Add Label</a></td>
                              <td>Consumer Discretionary</td>
                              <td>Restaurants</td>
                              <td>0.00%</td>
                              <td>-26.55%</td>
                              <td>23.08%</td>
                              <td>-3.47%</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Details</a></td>
                           </tr>
                           <tr>
                              <td class="trow-light-red">RT</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Add Label</a></td>
                              <td>Consumer Discretionary</td>
                              <td>Restaurants</td>
                              <td>0.00%</td>
                              <td>-26.55%</td>
                              <td>23.08%</td>
                              <td>-3.47%</td>
                              <td><a href="#stock-label" data-toggle="modal" class="btn btn-default btn-xs">Details</a></td>
                           </tr>
                           
                        </tbody>
                     </table>
                    
                  </div>
               </div>
               <!-- END SAMPLE TABLE PORTLET-->
               
            </div><!--END COLUMN-->
         </div><!--END ROW-->
         <!-- END PAGE CONTENT-->
         
         <!--START TRADE SCHOOL-->
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
                
                <p>This report breaks down the return of each position into the portion attributable to dividends, stock selection, and trading. This is most useful if you trade a great deal in and out of the same stocks, you can see if all that trading is worth it overall. This report is also useful if you have a position in a stock with a large corporate action like a spinoff, so that you can see what portion of your return is from that.</p>
                     
                     
             </div><!--END PORTLET BODY-->
          </div><!--END PORTLET-->
          <!--END TRADE SCHOOL-->
  