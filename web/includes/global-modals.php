<?php
/*
Global Include file for portlets that will be used site wide
Supporting Files:
HTML: includes/global-modals.php THIS FILE
Scripts: includes/scripts/global-modals.js.php
Process: process/ajax/global-modals-processes.php
*/

?>
<!-- BEGIN STOCK INFO PORTLET-->         
<div class="modal fade in" id="global-stock-info" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-full">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 style="float:left;" class="modal-title"><button title="Go Back To Previous Screen" type="button" class="btn btn-warning btn-sm" data-dismiss="modal"><i class="icon-arrow-left"></i></button> Stock Information (<strong><span id="global-stock-info-symbol"></span></strong>)</h4>
                <p style="float:right;margin:5px 10px 0px 0px;"><?php echo date('F d, Y');?></p>
                <div class="clearfix"></div>
            </div><!--modal-header-->
            <div class="modal-body">
                
                <div id="global-stock-info-load"></div>
                
            </div><!--modal-body-->
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div><!--modal-footer-->	
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- STOCK INFO PORTLET-->

<!-- BEGIN STOCK LABEL PORTLET-->         
<div class="modal fade" id="global-label" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" id="load-global-label">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">Set Label</h4>
            </div><!--modal-header-->
            <div class="modal-body">
            	<img src="<?php echo $_SESSION['site_root'];?>/images/loading.gif" /> Loading Label
            </div><!--modal-body-->
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div><!--modal-footer-->	
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- STOCK LABEL PORTLET-->

<!--Start Position Details Modal-->
<div class="modal fade in" id="global-pos-details" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 style="float:left;" class="modal-title scroll-to-top"><button title="Go Back To Previous Screen" type="button" class="btn btn-warning btn-sm" data-dismiss="modal"><i class="icon-arrow-left"></i></button> Position Details</h4>
                <p style="float:right;margin:5px 10px 0px 0px;"><?php echo date('F d, Y');?></p>
                <div class="clearfix"></div>
            </div><!--modal-header-->
            <div class="modal-body">
                
                
                <div id="load-global-pos-details"></div>
                
            </div><!--modal-body-->
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div><!--modal-footer-->	
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div><!--global-position-details-->
<!--End Position Details Modal-->

<!-- BEGIN STOCK DETAILS PORTLET-->         
<div class="modal fade in" id="global-ticket-details" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background: #FAFAFA;">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><button title="Go Back To Previous Screen" type="button" class="btn btn-warning btn-sm" data-dismiss="modal"><i class="icon-arrow-left"></i></button> Ticket Details</h4>
            </div><!--modal-header-->
            <div class="modal-body">
                
                <div id="load-global-trade-details"></div>
                
            </div><!--modal-body-->
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div><!--modal-footer-->	
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- STOCK DETAILS PORTLET-->
<!-- BEGIN STOCK DETAILS PORTLET-->         
            <div class="modal fade in" id="stock-info" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-full">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                            <h4 style="float:left;" class="modal-title"><button title="Go Back To Previous Screen" type="button" class="btn btn-warning btn-sm" data-dismiss="modal"><i class="icon-arrow-left"></i></button> Stock Information (<strong><span id="stock-info-symbol"></span></strong>)</h4>
    						<p style="float:right;margin:5px 10px 0px 0px;"><?php echo date('F d, Y');?></p>
    						<div class="clearfix"></div>
                        </div><!--modal-header-->
                        <div class="modal-body">
                            
                            <div id="stock-info-load"></div>
                            
                        </div><!--modal-body-->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div><!--modal-footer-->	
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- STOCK DETAILS PORTLET-->