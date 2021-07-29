<?php
/*
Marketocracy Inc. | Beta Development
Fund Indices(Tickers)

Supporting files:
	AJAX		- process/ajax/portlets/fund-tickers-ajax.php
	JAVASCRIPT	- includes/scripts/portlets/tickers.js.php
	HTML		- THIS DOCUMENT includes/portlets/tickers.php
	
*/
?>
<!-- BEGIN Help Modal-->         
        <div class="modal fade in" id="nav-help" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title">NAV</h4>
                    </div><!--modal-header-->
                    <div class="modal-body">
                        
                        <p>The NAV is your fund's Net Asset Value.</p>
                        <p>This is your mutual fund's price per share value. The per-share dollar amount of the fund is calculated by dividing the total value of all the securities in your fund by the number of fund shares outstanding.</p>
                        
                        <p>The NAV is automatically updated periodically throughout the day and is displayed in two parts:</p>
                        
                        <ol>
                        	<li>The first part, not in parentheses, is your NAV value in dollars per share.</li>
                            <li>The second part, in parentheses, is the percent difference from the funds starting (inception) NAV of $10.00 per share.</li>
                        </ol>
                        
                    </div><!--modal-body-->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div><!--modal-footer-->	
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- Help Modal-->	
        
        <!-- BEGIN Help Modal-->         
        <div class="modal fade in" id="change-help" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title">Price Change</h4>
                    </div><!--modal-header-->
                    <div class="modal-body">
                        
                        <p>The Price Change is the difference in your funds current Total Value from the previous day's closing value and is automatically updated periodically throughout the day.</p>
                        
                        
                        <p>The Change is displayed in two parts:</p>
                        
                        <ol>
                        	<li>The first part, not in parentheses, is the total dollar amount difference from the previous day's closing value. Displayed in red when negative; displayed in green when positive.</li>
                            <li>The second part, in parentheses, is the percent difference from the previous day's closing value. Displayed in red when negative; displayed in green when positive.</li>
                        </ol>
                        
                    </div><!--modal-body-->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div><!--modal-footer-->	
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- Help Modal-->		

<!--START PORTLET TICKERS-->
<div class="portlet tickers-widget" id="<?php echo $portletID; ?>">
	<div class="portlet-title handle">
		<div class="caption">
			<i class="icon-signal"></i>Fund Tickers
		</div>
		<div class="tools">
			<a href="" class="reload" onclick="refreshTickers();" title="Refresh Fund Prices"></a>
			<a href="" class="collapse"></a>
		</div>
	</div>
	<div class="portlet-body">
		<div class="ticker-content">
			<div class="" style="min-height:75px;" data-always-visible="1" data-rail-visible1="1">
				<!-- START TICKER LIST -->
				<ul class="ticker-list">
					<div class="container-fund-tickers" id="details-funds">
						<img src="<?php echo $_SESSION['site_root']; ?>/images/loading.gif">
					</div>
				</ul>
				<!-- END START TASK LIST -->
			</div>
		</div>
	</div>
</div>
<!--END PORTLET tickers-->
