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
                        <div class="caption"><i class="icon-cogs"></i>My Fund Rankings</div>
                        <div class="tools">
                        <a href="javascript:;" class="collapse"></a>
                        <a href="javascript:;" class="reload"></a>
                        </div>
                        </div>
                        <div class="portlet-body flip-scroll">
                        
                        
                            <?php /*?><div class="alert alert-success alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
                            For the six month period ending December 31, 2013 your fund outperformed 98.2% of the other funds on our site.
                            </div>
                            
                            <div class="alert alert-info alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
                            Congratulations you have earned some mPoints with one or more of your funds. <a href="#" class="alert-link">Click to see the full report</a>.
                            </div><?php */?>
                            
                            
                            <table id="fund-zone-table" class="table table-bordered table-striped table-condensed flip-content">
                                <thead class="flip-content">
                                    <tr>
                                        <th class="fzt-organge" colspan="8">ALL RANKINGS FOR YOUR FUND</th>
                                    </tr>
                                    <tr class="fzt-blue">
                                        <th class="fzt-aleft">DATE</th>
                                        
                                        <th>1 YEAR</th>
                                        <th>3 YEAR</th>
                                        <th>5 YEAR</th>
                                        <th>10 YEAR</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                    <tr>
                                        <td>Jan 31, 2013</td>
                                        <td>91.7%</td>
                                        <td>82.35% <span class="label label-success"><i class="icon-certificate"></i></span></td>
                                        <td>89.24% <span class="label label-success"><i class="icon-certificate"></i></span></td>
                                        <td>54.36% <span class="label label-success"><i class="icon-certificate"></i></span></td>
                                        <td>69.78% <span class="label label-info"><i class="icon-certificate"></i></span></td>
                                        <td>98.25% <span class="label label-info"><i class="icon-certificate"></i></span></td>
                                        <td>84.25% <span class="label label-info"><i class="icon-certificate"></i></span></td>
                                    </tr>
                                    
                                    
                                </tbody>
                            </table>
                            
                            <p><span class="label label-danger">N/C</span> = "Not Compliant", and means you weren't compliant for that period.<br /><br /><span class="label label-info"><i class="icon-certificate"></i></span> A blue star means you were in the top 100.<br /><br /><span class="label label-success"><i class="icon-certificate"></i></span> A green star means you were in the top 25%.</p>
                            
                            <p>The numbers above are the percentage of compliant funds that your fund beat in the given time period, unless you were in the top 100 for that period, in which case the number displays your rank.</p>
                            
                        </div><!--portlet-body-->
                    </div><!--portlet-->
                    <!-- END SAMPLE TABLE PORTLET-->
                
                </div><!--END COLUMN-->
            </div><!--END ROW-->
            <!-- END PAGE CONTENT-->