<?php


#Get alerts

$query = "
	SELECT *
	FROM system_alerts
	WHERE active='1' AND expire_timestamp>UNIX_TIMESTAMP() OR active='1' AND expire_timestamp IS NULL
";
try{
	$rsAlert = $mLink->prepare($query);
	$aValues = array();
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsAlert->execute($aValues);
}

catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$aAlerts = array();
while($alert = $rsAlert->fetch(PDO::FETCH_ASSOC)){
	
	//if($alert['expire_timestamp'] == NULL || $alert['expire_timestamp'] > time()){
		$aAlerts[$alert['uid']] = array(
			'title'		=> $alert['alert_title'],
			'message'	=> $alert['alert_message'],
			'type'		=> $alert['alert_type'],
			'expire'	=> $alert['expire_timestamp'],
			'link'		=> $alert['link']
		);	
	//}
	
	$alertType = $alert['alert_type'];
	
}

$showAlert = false;
$numAlerts = count($aAlerts);
if($numAlerts > 0){
	$showAlert = true;	
}

?>


    <!-- BEGIN QUICK TRADE MODAL-->
    <div class="modal fade" id="quick-trade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form  method="post" class="quick-trade-form">
            <div class="modal-content">
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Quick Trade</h4>
                </div><!--modal-header-->

                <div class="modal-body">
                	<div class="quick-trade-errors"></div>
                    <p>Quick Trade makes a market priced trade against a single fund for a single reason.
                    For more options, use the <a href="/?page=02-00-00-001">Trade Wizard</a>.</p>

                    <table class="table table-striped table-bordered table-hover table-full-width" id="quick-trade-table1">
                        <thead>
                            <tr>
                                <th>Trade Type</th>
                                <th>Fund</th>
                                <th>Symbol</th>
                                <th>Shares</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                <select name="trade_type">
                                    <option value="buy" data-title="buy">Buy</option>
                                    <option value="sell" data-title="cell">Sell</option>
                                </select>
                                </td>
                                <td>
                                <select name="fund_id" class="">
                                	<?php
									$query = "
										SELECT fund_symbol, fund_id
										FROM ".$_SESSION['fund_table']."
										WHERE member_id=:member_id
										ORDER BY seq_id ASC
									";

									try{
										$rsGetFunds = $mLink->prepare($query);
										$aValues = array(
											':member_id' 	=> $_SESSION['member_id']
										);
										$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
										$rsGetFunds->execute($aValues);
									}
									catch(PDOException $error){
										// Log any error
										file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
									}

									while($fund = $rsGetFunds->fetch(PDO::FETCH_ASSOC)){

										?>
										<option value="<?php echo $fund['fund_id'];?>"><?php echo $fund['fund_symbol'];?></option>
										<?php
									}
									?>
                                </select>
                                <input type="hidden" name="username" value="<?php echo $_SESSION['username'];?>" />
                                <input type="hidden" name="order_type" value="Day" />
                                </td>
                                <td><input type="text" class="form-control" name="stock_symbol" /></td>
                                <td><input type="text" class="form-control" id="share-qty" name="shares" /></td>
                            </tr>
                        </tbody>
                    </table>

                    <table class="table table-striped table-bordered table-hover table-full-width" id="quick-trade-table2">
                        <thead>
                            <tr>
                            <th>Why</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                <select name="fund" class="">
                                    <option value="" data-title="Select A Reason">Select A Reason</option>
                                    <option value="Good News" data-title="Good News">Good News</option>
                                    <option Value="Earnings Announcement" data-title="Earnings Announcement">Earnings Announcement</option>
                                    <option value="New Product" data-title="New Product">New Product</option>
                                    <option value="Sector Looks Better" data-title="Sector Looks Better">Sector Looks Better</option>
                                    <option value="Bad News" data-title="Bad News">Bad News</option>
                                    <option value="Management Change" data-title="Management Change">Management Change</option>
                                    <option value="Asset Allocation" data-title="Assets Allocation">Asset Allocation</option>
                                    <option value="Sector Looks Worse" data-title="Sector Looks Worse">Sector Looks Worse</option>
                                    <option value="Good Price" data-title="Good Price">Good Price</option>
                                    <option value="Merger/Acquistion" data-title="Merger/Acquistion">Merger/Acquisition</option>
                                    <option value="Rule Compliance" data-title="Rule Compliance">Rule Compliance</option>
                                    <option value="Market Indicators" data-title="Market Indicators">Market Indicators</option>
                                    <option value="Shift in Company Focus" data-title="Shift in Company Focus">Shift in Company Focus</option>
                                    <option value="Analyst Recommendation" data-title="Analyst Recommendation">Analyst Recommendation</option>
                                    <option value="Tax Planning" data-title="Tax Planning">Tax Planning</option>
                                    <option value="Technical Analysis Indicators" data-title="Technical Analysis Indicators">Technical Analysis Indicators</option>
                                    <option value="Other" data-title="Other">Other</option>
                                </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div><!--modal-body-->
                
                <div class="modal-footer">
                    <a href="/?page=02-00-00-001" class="btn btn-default" style="float:left;">Use Trade Wizard</a>
                    <a href="javascript:void(0);" onclick="processQuickTrade();" class="btn btn-success">Submit Quick Trade</a>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div><!--modal-footer-->
            
            </div><!--modal-content -->
            </form>
        </div><!--modal-dialog -->
    </div>
    <!-- END QUICK TRADE MODAL-->
    
    <!-- BEGIN ALERTS MODAL-->
    <div class="modal fade" id="system-alerts" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">System Alerts</h4>
                </div><!--modal-header-->

                <div class="modal-body">
                	
					<ul>
                    <?php
					foreach($aAlerts as $uid=>$aAlert){
						
						$link = $aAlert['link'];
						
						if($link != ''){
							$showLink	= $link;
							$target		= 'target="_blank"';	
						}else{
							$showLink 	= 'javascript:void(0);';	
							$target 	= '';
						}
						
						
						?>
		
						<li><a href="<?php echo $showLink;?>" <?php echo $target;?>><h4><?php echo $aAlert['title'];?></h4><?php echo $aAlert['message'];?></a></li>
						<?php
					}
					?>
                    </ul>
                    
                </div><!--modal-body-->
                
                <div class="modal-footer">
                    
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div><!--modal-footer-->
            
            </div><!--modal-content -->
        </div><!--modal-dialog -->
    </div>
    <!-- END QUICK TRADE MODAL-->

   
   
   <!-- BEGIN HEADER -->   
   <div class="header navbar navbar-inverse navbar-fixed-top">
     
      <!-- BEGIN TOP NAVIGATION BAR -->
      <div class="header-inner">
         
         <!-- BEGIN LOGO -->  
         <!--FUTURE PLACEMENT OF MARKETOCRACY SITE DROPDOWN-->
         <a class="navbar-brand" href="index.php" id="escape-hatch">
         <img src="<?php echo $siteRoot;?>/images/logo.png" alt="logo" class="img-responsive" /> 
         </a>
         
         <?php 
		 //CHECK TO SEE IF USER IS LOGGED IN (IF YES) Display Toolbar
         if(isset($_SESSION['member_id'])){?>
         
         <form class="hidden-xs" role="form" action="?page=01-00-00-002" method="post">
         <div class="input-group input-medium right" style="padding-top:7px;float:left;">
           <span class="input-group-btn">
           
           <button class="btn btn-info dropdown-toggle btn-sm" id="search-icon" title="Search by Section" type="button" data-toggle="dropdown" tabindex="-1" style="border-color:#1d1d1d;border-radius-top-left: 4px !important;border-radius-bottom-left: 4px !important;"><i class="icon-globe"></i></button>
           
           <button id="advanced-search" title="Search by Section" class="btn btn-info dropdown-toggle btn-sm" type="button" data-toggle="dropdown" tabindex="-1" style="border-color:#1d1d1d;border-radius-top-left: 4px !important;border-radius-bottom-left: 4px !important;"><i class="icon-angle-down"></i></button>
           <ul class="dropdown-menu" role="menu">
           	   <li style="padding-left:10px;border-bottom: 1px solid #CCC;"><p><strong>Search by:</strong></p></li>
               <li><a href="javascript:void(0);" onclick="searchType('dollar','stock');"><i class="icon-dollar"></i> Stock Info</a></li>
               <li><a href="javascript:void(0);" onclick="searchType('group','community');"><i class="icon-group"></i> Community</a></li>
               <?php /*?><li><a href="javascript:void(0);" onclick="searchType('money','funds');"><i class="icon-money"></i> Funds</a></li>
               <li><a href="javascript:void(0);" onclick="searchType('bar-chart','rank');"><i class="icon-bar-chart"></i> Rankings</a></li><?php */?>
               <li><a href="javascript:void(0);" onclick="searchType('globe','all');"><i class="icon-globe"></i> Everything</a></li>
               <?php if($_SESSION['flag_support_admin'] == '1' || $_SESSION['flag_super_admin'] == '1' || $_SESSION['flag_admin'] == '1'){?>
			   <li><a href="javascript:void(0);" onclick="searchType('briefcase','support');"><i class="icon-briefcase"></i> Tickets</a></li>
			   <?php }?>
            </ul>
           </span>
           <input type="hidden" name="filter" id="search-filter" value="all" />
           <input type="text" id="search-bar" name="search" class="form-control input-medium input-sm" placeholder="Ticker, Forum, Search..." style="background-color:#EFEFEF;color:#212123 !important;border-color:#1d1d1d;border-right:none;border-left:none;">
           <span class="input-group-btn">
           
           <button class="btn btn-info btn-sm" title="Search" type="submit" style="border-color:#1d1d1d;border-radius-top-left: 4px !important;border-radius-bottom-left: 4px !important;"><i class="icon-search"></i></button>
           </span>
           
            
         </div>
         </form>
         <!-- END LOGO -->

<!--         <div style="display:block; font-size:12px !important;color:#CCC;padding:15px 0px 0px 10px;float:left;">&nbsp; <strong>BETA</strong></div>-->

         

             <!-- BEGIN RESPONSIVE MENU TOGGLER --> 
             <a href="javascript:;" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
             <img src="images/menu-toggler.png" alt="" />
             </a> 
             <!-- END RESPONSIVE MENU TOGGLER -->
             
             <!-- BEGIN STYLE CUSTOMIZER -->         
             <div class="theme-panel hidden-xs hidden-sm">
                <div class="toggler" id="theme-panel"><i class="icon-gear"></i></div>
                <div class="theme-options" id="theme-panel-1">
                   <div class="theme-option theme-colors clearfix">
                      <span>Interface Settings</span>
                     <?php /*?> <ul>
                         <li class="color-black current color-default tooltips" data-style="default" data-original-title="Default"></li>
                         <li class="color-grey tooltips" data-style="grey" data-original-title="Grey"></li>
                         <li class="color-blue tooltips" data-style="blue" data-original-title="Blue"></li>
                         <li class="color-red tooltips" data-style="red" data-original-title="Red"></li>
                         <li class="color-light tooltips" data-style="light" data-original-title="Light"></li>
                      </ul><?php */?>
                   </div>
                   <div class="theme-option">
                      <span>Layout</span>
                      <select class="layout-option form-control input-small">
                         <option value="fluid" selected="selected">Fluid</option>
                         <option value="boxed">Boxed</option>
                      </select>
                   </div>
                   <div class="theme-option">
                      <span>Header</span>
                      <select class="header-option form-control input-small">
                         <option value="fixed" selected="selected">Fixed</option>
                         <option value="default">Default</option>
                      </select>
                   </div>
                   <div class="theme-option">
                      <span>Sidebar</span>
                      <select class="sidebar-option form-control input-small">
                         <option value="fixed">Fixed</option>
                         <option value="default" selected="selected">Default</option>
                      </select>
                   </div>
                   <div class="theme-option">
                      <span>Footer</span>
                      <select class="footer-option form-control input-small">
                         <option value="fixed">Fixed</option>
                         <option value="default" selected="selected">Default</option>
                      </select>
                   </div>
                   <div class="theme-option">
                   		<?php 
						if(!isset($_SESSION['layout']) or $_SESSION['layout'] == "2"){
							$setLayout 		= "4";
							$columnCount 	= "Four";
						}elseif($_SESSION['layout'] == "4"){
							$setLayout 		= "2";
							$columnCount 	= "Two";
						}
						?>
                      	<a href="process/member-change-layout.php?layout=<?php echo $setLayout;?>" style="width:100%" class="btn btn-info">Switch To <?php echo $columnCount;?> Columns</a>
                        
                   </div>
                </div>
             </div>
             <!-- END BEGIN STYLE CUSTOMIZER -->
             
             <!-- BEGIN TOP NAVIGATION MENU -->
             <ul class="nav navbar-nav pull-right">
                
                <?php if(isset($_SESSION['admin_id'])){?>
                
                
                
                <li class="dropdown user" id="admin-menu">
                   <a href="/process/ajax/admin-switch-user.php?member=<?php echo $_SESSION['member_id'];?>&admin=<?php echo $_SESSION['admin_id'];?>&toggle=user-to-admin" class="dropdown-toggle"  data-close-others="true" style="padding:14px 10px 10px 15px;">
                   
                   <span class="username" style="color:#ffffff;">Return To Admin</span>
                   </a>
                </li>
                <li class="devider" style="margin-left: 0px !important;margin-right:0px !important;">&nbsp;</li>
                <?php } ?>
                
                <?php if($_SESSION['admin'] == "1"){?>
                <li class="dropdown user" id="admin-menu">
                   <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" style="padding:14px 10px 10px 15px;">
                   
                   <span class="username" style="color:#ffffff;">Admin</span>
                   <i class="icon-angle-down"></i>
                   </a>
                   <ul class="dropdown-menu">
                      <li style="background:#5BC0DE;color:#ffffff;padding:3px 3px 3px 10px;text-algin:center !important;"><i class="icon-tag"></i> Support Tickets</li>
                      <li><a href="?page=08-01-00-905">Assigned Tickets</a></li>
                      <li><a href="?page=08-01-00-902">All Open Tickets</a></li>
                      <li><a href="?page=08-01-00-904">Closed Tickets</a></li>
                      
                      <li class="divider"></li>
                      <li><a href="?page=20-00-00-008">Membership</a></li>
                      <li><a href="?page=20-00-00-003">Member Lookup</a></li>
                      <li><a href="?page=20-00-00-006">Rankings</a></li>
                      <li><a href="?page=20-00-00-004">Member Reprice</a></li>
                      <li><a href="?page=20-00-00-005">CA Admin</a></li>
                      <li><a href="?page=20-00-00-007">Manager List</a></li>
                      <?php if($_SESSION['super_admin'] == "1"){?>
                      <li><a href="?page=20-00-00-001">Tools</a></li>
                      <li><a href="?page=20-00-01-001">MDS CMS</a></li>
                      <li class="divider"></li>
                      <li><a href="moodle.php">Roundtable</a></li>
                      <?php }?>
                   </ul>
                </li><!-- END ADMIN DROPDOWN -->
                <li class="devider" style="margin-left: 0px !important;margin-right:0px !important;">&nbsp;</li>
                <?php } ?>
                
                <?php if($tourStatus == "on") {?>
                    <li class="hidden-xs user">
                        <button type="button" id="demo" style="margin:7px 10px 0px 0px;" class="btn btn-info btn-sm" data-demo><span class="icon icon-play"></span> Site Tour</button>
                    </li>
                   
                    <li class="devider" style="margin-left: 0px !important;margin-right:0px !important;">&nbsp;</li>
                <?php } //end tour button?>
                 
                <?php /*?><li class="dropdown hidden-xs user" id="feedback1">
                    <a href="#feedback-modal" id="tour-feedback" class="dropdown-toggle" data-toggle="modal" style="padding-right:10px;padding-left:10px;"><span class="username">Submit Feedback</span></a>
                </li>
                
                <li class="devider" style="margin-left:0px !important;">&nbsp;</li><?php */?>
                
                <?php if($_SESSION['admin'] == '1'){?>
<!--                <li class="dropdown hidden-xs" id="quick-trade" >
                    <a href="#quick-trade" id="tour-quick-trade" class="dropdown-toggle" data-toggle="modal" style="padding:14px 10px 10px 10px;" title="Make A Quick Trade"><i class="icon-random" style="color:#ffffff !important;"></i> </a>
                </li>
                <li class="devider" style="margin-right:0px !important;margin-left: 0px !important;">&nbsp;</li>
-->
                <?php }?>
                
                <li class="dropdown hidden-xs">
                	<a href="<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";?>" target="_blank" title="Open New Window" class="dropdown-toggle" style="padding:14px 10px 10px 10px;"><i style="color:#ffffff !important;" class="icon-external-link"></i></a>
                </li>
                
                <?php 
				
				
				
				
				if($showAlert == true){
					?>
                    <li class="devider" style="margin-right:0px !important;margin-left: 0px !important;">&nbsp;</li>
                    <!-- BEGIN Alert -->
                    <li class="dropdown disable-click" id="alert_bar" >
                       <a href="#system-alerts" id="system-alert-link" class="dropdown-toggle" data-toggle="modal" data-hover="dropdown" data-close-others="true" style="padding:13px 15px 10px 15px;color:#ffffff !important;">
                       <strong>ALERT</strong>&nbsp;&nbsp; <span class="badge badge-<?php echo $alertType;?>"><?php echo $numAlerts;?></span>
                       </a>
                       <ul class="dropdown-menu extended notification">
                          <li>
                             <p><?php echo $numAlerts;?> System Alert</p>
                          </li>
                          <li>
                             <ul class="dropdown-menu-list scroller " style="height: 250px;">
                                <?php
                                foreach($aAlerts as $uid=>$aAlert){
                                    
									$link = $aAlert['link'];
									
									if($link != ''){
										$showLink	= $link;
										$target		= 'target="_blank"';	
									}else{
										$showLink 	= 'javascript:void(0);';	
										$target 	= '';
									}
									
									
									?>
                    
                                    <li><a href="<?php echo $showLink;?>" <?php echo $target;?>><h4><?php echo $aAlert['title'];?></h4><?php echo $aAlert['message'];?></a></li>
                                    <?php
                                }
                                ?>
                             </ul>
                          </li>
                          
                       </ul>
                    </li>
                    
                    <?php
				}
				?>
               	
               
                 
                
                <li class="devider" style="margin-right:0px !important;margin-left: 0px !important;">&nbsp;</li>
                <!-- BEGIN NOTIFICATION DROPDOWN -->
                <li class="dropdown disable-click" id="header_notification_bar" >
                   <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" style="padding:13px 15px 10px 15px;color:#ffffff !important;">
                   <i class="icon-exclamation-sign" style="color:#ffffff !important;"></i>
                   <span class="badge badge-info note-counter"></span>
                   </a>
                   <ul class="dropdown-menu extended notification">
                      <li>
                         <p><span class="note-counter2"></span></p>
                      </li>
                      <li>
                         <ul class="dropdown-menu-list scroller notification-ajax-toolbar" style="height: 250px;">
                            <?php //Loaded by ajax - file: includes/portlets/ajax/notifications-ajax-toolbar.php ?>
                         </ul>
                      </li>
                      <li class="divider"></li>
                      <li class="external">   
                         <!--<a href="#">See all notifications <i class="icon-angle-right"></i></a>-->
                         <a href="?page=10-00-00-002&account=1&tab=note">Notification Settings <i class="icon-angle-right"></i></a>
                      </li>
                   </ul>
                </li>
                <!-- END NOTIFICATION DROPDOWN -->
               <?php /*?><!-- BEGIN INBOX DROPDOWN -->
                <li class="dropdown" id="header_inbox_bar">
                   <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown"
                      data-close-others="true">
                   <i class="icon-envelope"></i>
                   <span class="badge badge-info">1</span>
                   </a>
                   <ul class="dropdown-menu extended inbox">
                      <li>
                         <p>You have 1 new message(s)</p>
                      </li>
                      <li>
                         <ul class="dropdown-menu-list scroller" style="height: 250px;">
                            <li>  
                               <a href="inbox.php?a=view">
                               <span class="photo"><img src="./images/avatar2.jpg" alt=""/></span>
                               <span class="subject">
                               <span class="from">Name</span>
                               <span class="time">12:00 PM</span>
                               </span>
                               <span class="message">
                               Message Place Holder
                               </span>  
                               </a>
                            </li>
                            
                            
                         </ul>
                      </li>
                      <li class="divider"></li>
                      <li class="external">   
                         <a href="inbox.php">See all messages <i class="icon-angle-right"></i></a>
                      </li>
                   </ul>
                </li>
                <!-- END INBOX DROPDOWN --> <?php */?>
                <?php /*?><!-- BEGIN TODO DROPDOWN -->
                <li class="dropdown" id="header_task_bar">
                   <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                   <i class="icon-ok"></i>
                   <span class="badge badge-warning">1</span>
                   </a>
                   <ul class="dropdown-menu extended tasks">
                      <li>
                         <p>You have 1 pending tasks</p>
                      </li>
                      <li>
                         <ul class="dropdown-menu-list scroller" style="height: 250px;">
                            
                            <li>  
                               <a href="#">
                               <span class="task">
                               <span class="desc">Place Holder</span>
                               <span class="percent">65%</span>
                               </span>
                               <span class="progress progress-striped">
                               <span style="width: 65%;" class="progress-bar progress-bar-danger" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100">
                               <span class="sr-only">65% Complete</span>
                               </span>
                               </span>
                               </a>
                            </li>
                            
                            
                         </ul>
                      </li>
                      <li class="divider"></li>
                      <li class="external">   
                         <a href="#">See all tasks <i class="icon-angle-right"></i></a>
                      </li>
                   </ul>
                </li>
                <!-- END TODO DROPDOWN --><?php */?>
                
                <li class="devider" style="margin-right:0px !important;margin-left: 0px !important;">&nbsp;</li>
                
                <!--Help Menu-->
                <li class="dropdown user">
                   <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" style="padding:13px 15px 10px 15px;color:#ffffff !important;">
                   <i class="icon-question-sign" style="color:#ffffff !important;"></i>
                   </a>
                   <ul class="dropdown-menu">
                      	<li><a href="?page=08-01-00-001&type=pp&subtype=feedback">Submit Feedback</a></li>
                   		<li><a href="?page=08-01-00-001&type=pp&subtype=fund_issue">Report Fund Issue</a></li>
                      
                      	<li class="divider"></li>
                        <li><a href="?page=08-01-00-001&type=ca">Report Corporate Action</a></li>
                      
                   </ul>
                </li><!-- END HELP DROPDOWN -->
                
                <?php
				$subTotal = 0;
	
				foreach($_SESSION['cart'] as $key=>$aCartItems){
					$subTotal = $subTotal + $aCartItems['sale_price'];	
				}
				
				if($subTotal == 0){
					$numItems = 0;	
				}else{
					$numItems = count($_SESSION['cart']);	
				}
				
				if($numItems > 0){
					?>
					<li class="devider" style="margin-right:0px !important;margin-left: 0px !important;">&nbsp;</li>
					<!-- BEGIN NOTIFICATION DROPDOWN -->
					<li class="dropdown disable-click" id="header_cart" >
					   <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" style="padding:13px 15px 10px 15px;color:#ffffff !important;">
					   <i class="icon-shopping-cart" style="color:#ffffff !important;"></i>
					   <span class="badge badge-info  cart-items"><?php echo $numItems;?></span>
					   </a>
                        <ul class="dropdown-menu extended">
                            <li>
                            	<p>You have <span class="cart-items"><?php echo $numItems;?></span> items in your cart.</p>
                            </li>
                            <li>
                            	<ul class="dropdown-menu-list scroller" style="height: 250px;">
                            </ul>
                            </li>
                            <li class="divider"></li>
                            <li class="external">   
                            <!--<a href="#">See all notifications <i class="icon-angle-right"></i></a>-->
                            <a href="?page=10-00-00-002&account=1&tab=membership&toggle=1" style="text-align:center;">View Cart</a>
                            </li>
                        </ul>
					</li>
                    <?php
				}
				?>
                
                <li class="devider" style="margin-left: 0px !important;margin-right:0px !important;">&nbsp;</li>
                <!-- BEGIN USER LOGIN DROPDOWN -->
                <li class="dropdown user" id="user-menu">
                   <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" style="padding:14px 10px 10px 15px;">
                   
                   <?php
				   if($_SESSION['first_name'] == ''){
					   $displayName = $_SESSION['username'];
				   }else{
						$displayName = $_SESSION['first_name'].' '.$_SESSION['last_name'];  
				   }
				   ?>
                   
                   <span class="username"><?php echo $displayName;?></span>
                   <i class="icon-angle-down"></i>
                   </a>
                   <ul class="dropdown-menu">
                      
                      <?php if(/*$_SESSION['admin'] == '1' ||*/ $_SESSION['subscription']['mtrAccess'] != NULL){?>
                      <li><a href="?page=01-00-00-003"><i class="icon-retweet"></i> Action Center</a></li>
                      <li class="divider"></li>
                      <?php } ?>
                      
					  <?php /*?><li><a href="profile.php"><i class="icon-user"></i> My Profile</a>
                      </li>
                      <li><a href="calendar.php"><i class="icon-calendar"></i> My Calendar</a>
                      </li><?php */?>
                      <li><a href="#"><i class="icon-exclamation-sign"></i> My Notifications <span class="note-counter3"></span></a>
                      </li>
                     <?php /*?> <li><a href="inbox.php"><i class="icon-envelope"></i> My Inbox <span class="badge badge-danger">3</span></a>
                      </li>
                      <li><a href="#"><i class="icon-tasks"></i> My Tasks <span class="badge badge-success">7</span></a>
                      </li><?php */?>
                      <li class="divider"></li>
                      
                      <li><a href="?page=10-00-00-002&account=1&tab=account"><i class="icon-gear"></i> Account Settings</a></li>
                      <li><a href="?page=10-00-00-002&account=2"><i class="icon-gear"></i> Fund Settings</a></li>
                      <?php
					  if($membershipLive == '1' || $_SESSION['admin'] == '1'){
						?>
                        <li><a href="?page=10-00-00-002&account=1&tab=membership"><i class="icon-gear"></i> Membership</a></li>
                        <?php	  
					  }
					  ?>
                      
<!--                      <li><a href="form-wizard.php"><i class="icon-gear"></i> Account Setup</a>
                      </li>-->
                      <li><a href="/logout"><i class="icon-signout"></i> Log Out</a>
                      </li>
					<?php
					if (isset($_SESSION['joined_timestamp']) && is_numeric($_SESSION['joined_timestamp'])){
					?>
                      <li class="dropdown-bottom">Member Since <?php echo date("M. j, Y", $_SESSION['joined_timestamp']);?><br /><?php echo get_user_title();?></a>
<!--                      <li class="dropdown-bottom">Member Since <?php echo date("M. j, Y", $_SESSION['joined_timestamp']);?><br /><?php echo $_SESSION['subscription']['subData']['alt_product_name'];?> Member<br /><?php echo get_user_title();?></a>-->
					<?php
					}
					?>
                   </ul>
                </li><!-- END USER LOGIN DROPDOWN -->
                
                
             </ul><!-- END TOP NAVIGATION MENU -->
         
         <?php 
		 }else{
		 //If USER is not logged in, display LOGIN Links	  
		 ?>
		 	<!-- BEGIN TOP NAVIGATION MENU -->
             <ul class="nav navbar-nav pull-right navbar-logout">
             	<li><a href="#">Portfolio Login</a></li>
<!--
                <li class="devider" style="margin-left:0px !important;">&nbsp;</li>

                <li><a href="#">Sign Up</a></li>
-->             </ul><!-- END TOP NAVIGATION MENU -->
		 <?php
		 }
		 ?>
         
      </div><!-- END TOP NAVIGATION BAR -->
      
      
      
   </div><!-- END HEADER -->
   
   <div class="clearfix"></div>
