<?php
/*
GENERAL RULES - HTML FILE

SUPPORTING FILES:
_______________________________________________________________

AJAX 			- process/ajax/
PHP JAVASCRIPT	- includes/scripts/
HTML 			- THIS FILE includes/pages/policy-rules-general.php
_______________________________________________________________

*/

?>
        
        <!-- BEGIN PAGE CONTENT-->
        <div class="row">
            <div class="col-md-12">
				
                <?php
				include('includes/nav-rules.php');
				?>
                
            	<div class="note note-info">
                	<?php /*?><h4 class="block">PAGE AVAILABLE SOON - UNDER CONSTRUCTION</h4><?php */?>
                	<p style="font-size:14px;">By becoming a member of the site, all participants understand that the following rules will apply to each fund created.</p>
                </div><!--note-->
               	                
                <!-- BEGIN COMPLIANCE RULES-->
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption"><i class="icon-file-text"></i>General Rules</div>
                        <div class="tools">
                            <a href="" class="collapse"></a>
                            <a href="" class="reload"></a>
                        </div>
                    </div><!--portlet-title-->
                    <div class="portlet-body">
                    	
                    	<div class="row">
                        	<div class="col-md-4">
                            	<ol class="rules-list">
                                	<li> Each registered member of Marketocracy will be allocated $1,000,000 (US) in fictional Marketocracy dollars to manage per fund. Each registered member may build and track up to the number of funds allowed by their membership level, each with a limit of 200 stocks.</li>
                                    
                                    <li>Members may register and create funds at any time. All funds will be tracked by calendar quarter performance, year to date performance, and by performance since fund inception.</li>

                                    <li>Any non-extended (limited) fund offered via the free Basic membership will expire after one year, at which time you may create a new fund that will itself expire after one year.</li>

                                    <li>Each fund will be tracked and ranked based on the change in its net asset value (NAV) over a specific period of time. Fund performance at Marketocracy, and among professional mutual funds, is measured by calculating the change in NAV during a defined period of performance ranking. The NAV of an investment fund is calculated by dividing the current value of its holdings by the number of shares outstanding. All model funds at Marketocracy will begin with $1,000,000 in fantasy cash, with 100,000 shares outstanding and a $10.00 NAV.<!-- For a further explanation of the Net Asset Value methodology, <a href="http://legacy.marketocracy.com/cgi-bin/WebObjects/Portfolio.woa/wa/ArticleViewPage?source=HdHdGiNeDmJcGhJgMaKiAbLb">click here</a>.--></li>

                                    <li>Marketocracy has endeavored to create a real-world stock market simulation for our model fund managers. We believe that the best long-term fund performance comes from investing in good companies, not from day trading. While our trading system mirrors the real world as closely as possible, investors who prefer to capture quick gains may find day trading on our system more difficult than actual online day trading through an eBroker.</li>

                                    <li>Registered participants may select equities for their funds from those traded on the New York Stock Exchange (NYSE), New York Stock Exchange MKT Equities (NYSE MKT - formerly AMEX), New York Stock Exchange Archipelago Exchange (NYSEArca), National Association of Securities Dealers Automated Quotations (NASDAQ), and Over The Counter Bulletin Board (OTCBB) (herein the "Real Market"). The Real Market is open from 9:30 a.m. to 4:00 p.m. ET except on market holidays, during which the Real Market will close at 1:00 p.m. ET. After hours trading will not be permitted. Stock price quotations on the Marketocracy web site are delayed at least 15 minutes. All trades occur on the Marketocracy market rather than in the Real Market. Orders submitted for entry outside of normal trading hours will be entered into the appropriate order book for execution at the opening of the following trading day. These orders will be subject to communications delays and availability. Simulated order executions for fund trades are based on the actual reported trades of Real Market stocks, on a share for share basis, following a minimum 15-minute order delay. Marketocracy executes all fund orders with at least a 15-minute delay, subject to other orders in the order book, market conditions, system performance and other factors.</li>
                                    
                                    

									
                                    
                                </ol>
                            </div><!--col-md-6-->
                            
                            <div class="col-md-4">
                            	<ol start="7"  class="rules-list">
                            		
                            		<li>Market and limit orders are accepted, as are day and good 'til cancelled (GTC) orders. GTC orders will remain active for 14 days, unless filled, at which point the order will be closed.<br /><br />

Open orders will be adjusted for stock splits and stock dividends. Note that since splits and dividends are not credited until after the market close, it is possible that an order will execute before the split has been applied. The stock will be credited to your account when the split is applied. Other corporate actions (de-listing, mergers, etc.) will cause an open order to cancel.<br /><br />

Open orders may be cancelled, but you will receive any shares that have already filled by the time the cancellation has been processed. (Practically speaking, this means that while the Real Market is open, market orders for high volume stocks are likely to fill before they can be cancelled.)<br /><br />

To provide as realistic a simulation as possible, the Marketocracy trading engine uses live data feeds from the major stock exchanges, but fills open tickets at a fraction of the total real market volume. This means that orders for small, illiquid stocks can take days or even weeks to fill.</li>

									<li>Negative balances will be charged a virtual interest charge of Federal Funds rate plus 3%. The Federal Funds rate is updated monthly, or upon official change in rates, whichever is first. Interest is calculated and debited daily.</li>
                                    
                                    <li>Splits and acquisitions are processed after market close on their effective date. Account values may temporarily be misrepresented until reconciliation occurs. Un-reconciled stocks may be unavailable for trading.</li>
                                    
                            	</ol>
                            </div><!--col-md-6-->
                            
                            <div class="col-md-4">
                            	<ol start="10"  class="rules-list">

									<li>Cash dividends are paid on the pay date based on the number of shares owned on the record date.<br /><br />

Stock dividends are paid on the pay date. If the dividend ratio is greater than 25%, payout will be based on the share count prior to market open on the pay date. If the dividend ratio is less than 25%, the payout will be based on the share count on the day of record.<br /><br />

Note that there will be variation from the general rules for splits/acquisitions/dividends, as corporate actions are sometimes complex.</li>
                                    
                                    <li>All executed fund trades will be charged a commission of $0.05/share, with a maximum commission charge of 5%. All funds will be charged an annual management fee of 2% (200 basis points) of assets under management. The management fee will be calculated and debited daily. Sales will be charged an SEC fee on the proceeds of the sale divided by 30,000. End of day fantasy cash balances will earn annual interest based on the Federal Funds Rate, less 2%. The Federal Funds Rate is updated monthly, or upon official change in rates, whichever is first. Interest is calculated and credited daily. All such fees and charges, are, of course, in fictional dollars.</li>

                                    <li>No margin buying or short selling is allowed, as we want to be able to offer the investing public funds based on our members' performance, and SEC approval for funds that are allowed to short is much more difficult. Note that Marketocracy may expand to allow short selling in the future.</li>
                                    
                            	</ol>
                            </div><!--colum-md-4-->
                        </div><!--row-->
                   
                    </div><!--portlet-body-->
                </div><!--portlet-->
                <!--END VOLATILITY ANALYSIS-->
                
            </div>
        </div>
        <!-- END PAGE CONTENT-->