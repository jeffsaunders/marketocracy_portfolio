<?php
/*
Marketocracy Inc. | Beta Development
Admin General HTML

Supporting files:
	AJAX		- process/ajax/admin-general-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/admin-general.js.php
	HTML		- THIS DOCUMENT includes/pages/admin-general.php
*/

$date = $_REQUEST['date'];

$aDate = explode('/', $date);

$queryDate = mktime(0,0,0,$aDate[0],$aDate[1],$aDate[2]);

$today = strtotime('today midnight');


if($date == ''){
	$fromTime = $today;	
	
	$query = "
		SELECT * 
		FROM ".$_SESSION['eventslog_table']."
		WHERE timestamp>'".$fromTime."' AND event='Login'
		ORDER BY timestamp DESC
		
	";
}else{
	$fromTime = $queryDate;	
	
	$query = "
		SELECT * 
		FROM ".$_SESSION['eventslog_table']."
		WHERE timestamp>'".$fromTime."' AND timestamp<'".$today."'
		ORDER BY timestamp DESC
		
	";
}


try{
	$rsGetEvents = $mLink->prepare($query);
	$aValues = array(
		//':fund_id' 	=> $fundID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsGetEvents->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$cnt = 0;

$aEvents = array();
$aLogins = array();
$aLoggedIn = array();

$eventsCount = $rsGetEvents->rowCount();

while($event = $rsGetEvents->fetch(PDO::FETCH_ASSOC)) {
	
	$aMember	= get_member($mLink, $event['member_id'], 'all');
	
	$aEvents[$eventsCount] = array(
		'eventType'		=> $event['event'],
		'aMember'		=> $aMember,
		'timestamp'		=> $event['timestamp'],
		'member_id'		=> $event['member_id'],
		'detail'		=> $event['detail']		
	);
	
	if($event['event'] == 'Login'){
		
		$subData = subscription($mLink, $event['member_id'], true, true);
		
		$aLogins[$event['member_id']] = array(
			'eventType'		=> $event['event'],
			'aMember'		=> $aMember,
			'timestamp'		=> $event['timestamp'],
			'member_id'		=> $event['member_id'],
			'detail'		=> $event['detail'],
			'membership'	=> $subData['membershipLevel'],
			'maxFunds'		=> $subData['maxFunds'],
			'maxFundValid'	=> $subData['maxFundValid']
		);
		
		
	}
	
	
	
	$eventsCount = $eventsCount -1;
}
echo '<pre>';
echo $query;
//print_r($aLogins);
echo '</pre>';


$query = "
	SELECT *
	FROM ".$_SESSION['logged_in_table']."
";
try {
	$rsGet = $mLink->prepare($query);
	$aValues = array(
		//':fund_id' 	=> $fundID
	);
	// Prepared query - for error logging and debugging
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//die($preparedQuery);
	$rsGet->execute($aValues);
}
catch(PDOException $error){
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}
echo $error;

$numLogged 		= $rsGet->rowCount();
?>
         
          
         
            <!-- BEGIN PAGE CONTENT-->
            
            <?php include('includes/pages/includes/admin-api-que.php');?>
                
            <div class="row">
                <div class="col-md-12">
                    
                    <div class="tabbable tabbable-custom">
                        
                        <ul class="nav nav-tabs">
                       		<li class="active"><a href="#tab_0" data-toggle="tab">Events Log</a></li>
                            <li><a href="#loggedIn" data-toggle="tab">Currently Logged In (<?php echo $numLogged;?>)</a></li>
                            <li><a href="#logins" data-toggle="tab">Unique Logins (<?php echo count($aLogins);?>)</a></li>
                        	<li><a href="?page=20-00-00-003">Member Lookup</a></li>
                            <li><a href="?page=20-00-00-004">Member Reprice</a></li>
                            <li><a href="?page=20-00-00-005">CA Admin</a></li>
                        	
                        </ul>

                        <div class="tab-content">
                        
                            <div class="tab-pane active" id="tab_0">
                                <div class="portlet">
                                    <div class="portlet-title">
                                    	<div class="caption"><i class="icon-reorder"></i>Events Log</div>
                                      	<div class="tools">
                                        	<a href="javascript:;" class="collapse"></a>
                                         	<a href="javascript:;" class="reload"></a>
                                      	</div>
                                    </div><!--portlet-title-->
                                    <div class="portlet-body form">
                                    
                                    	<div class="table-responsive">
                                            <table class="table table-striped table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Member (username) (user ID)</th>
                                                        <th>Time</th>
                                                        <th>Event</th>
                                                        <th>Detail</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                	<?php
													
													
													
													foreach($aEvents as $events => $aEvent){
														
														$cnt++;
														
														$aMember = $aEvent['aMember'];
														$eventType = $aEvent['eventType'];
														
														switch($eventType){
															case 'Logout'	: $eventType = '<span class="label label-info"><i class="icon-signout"></i> '.$eventType.'</span>';	break;
															case 'Login'	: $eventType = '<span class="label label-success"><i class="icon-signin"></i> '.$eventType.'</span>';break;
															case 'Password Change': $eventType = '<span class="label label-warning"><i class="icon-lock"></i> '.$eventType.'</span>';break;
															case 'Verify Email': $eventType = '<span class="label label-warning"><i class="icon-envelope"></i> '.$eventType.'</span>';break;
															case 'Create Account': $eventType = '<span class="label label-warning"><i class="icon-user"></i> '.$eventType.'</span>';break;
															default: $eventType = '<span class="label label-default">'.$eventType.'</span>';break;
														}
														?>
                                                        <tr>
                                                        	<td><?php echo $events;?></td>
                                                            <td><a href="/?page=20-00-00-003&member=<?php echo $aEvent['member_id'];?>" target="_blank"><?php echo $aMember['fullName'];?> (<?php echo $aMember['username'];?>) (<?php echo $aEvent['member_id'];?>)</a></td>
                                                            <td><?php echo date('m/d/Y @ g:i A', $aEvent['timestamp']);?></td>
                                                            <td><?php echo $eventType;?></td>
                                                            <td><?php echo $aEvent['detail'];?></td>
                                                        </tr>
                                                        <?php
													}
													
													?>  
                                                    
                                                </tbody>
                                            </table>
										</div><!--table-responsive-->    
                                        
                                                               
                                    </div><!--END PORTLET BODY-->
                                </div><!--END PORTLET-->
                            
                            </div><!--END TAB 1-->
                            
                            
                            
                            <div class="tab-pane" id="logins">
                                <div class="portlet">
                                    <div class="portlet-title">
                                    	<div class="caption"><i class="icon-reorder"></i>Unique Logins</div>
                                      	<div class="tools">
                                        	<a href="javascript:;" class="collapse"></a>
                                        	<!--<a href="javascript:;" class="reload"></a>-->
                                      	</div><!--tools-->
                                    </div><!--portlet-title-->
                                    <div class="portlet-body form">
                                    	<div class="table-responsive">
                                            <table class="table table-striped table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Member (username) (user ID)</th>
                                                        <th>Membership</th>
                                                        <th># Funds</th>
                                                        <th>Time</th>
                                                        <th>Event</th>
                                                        <th>Detail</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
													
													$loginCount = count($aLogins);
                                                    foreach($aLogins as $events => $aEvent){
														
														$cnt++;
														
														$aMember = $aEvent['aMember'];
														$eventType = $aEvent['eventType'];
														
														switch($eventType){
															case 'Logout'	: $eventType = '<span class="label label-info"><i class="icon-signout"></i> '.$eventType.'</span>';	break;
															case 'Login'	: $eventType = '<span class="label label-success"><i class="icon-signin"></i> '.$eventType.'</span>';break;
															case 'Password Change': $eventType = '<span class="label label-warning"><i class="icon-lock"></i> '.$eventType.'</span>';break;
															case 'Verify Email': $eventType = '<span class="label label-warning"><i class="icon-envelope"></i> '.$eventType.'</span>';break;
															case 'Create Account': $eventType = '<span class="label label-warning"><i class="icon-user"></i> '.$eventType.'</span>';break;
															default: $eventType = '<span class="label label-default">'.$eventType.'</span>';break;
														}
														?>
                                                        <tr>
                                                        	<td><?php echo $loginCount;?></td>
                                                            <td><a href="?page=04-00-00-001&member=<?php echo $aEvent['member_id'];?>" target="_blank"><?php echo $aMember['fullName'];?> (<?php echo $aMember['username'];?>) (<?php echo $aEvent['member_id'];?>)</a></td>
                                                            <td><?php echo $aEvent['membership'];?></td>
                                                            <td><?php echo $aEvent['maxFunds'];?></td>
                                                            <td><?php echo date('m/d/Y @ g:i A', $aEvent['timestamp']);?></td>
                                                            <td><?php echo $eventType;?></td>
                                                            <td><?php echo $aEvent['detail'];?></td>
                                                        </tr>
                                                        <?php
														$loginCount = $loginCount -1;
													}
                                                    ?>
                                                </tbody>
                                            </table>
										</div><!--table-responsive-->
                                    </div><!--END PORTLET BODY-->
                                </div><!--END PORTLET-->
                            
                            </div><!--END TAB 2-->
                            
                            <div class="tab-pane" id="loggedIn">
                                <div class="portlet">
                                    <div class="portlet-title">
                                    	<div class="caption"><i class="icon-reorder"></i>Currently Logged In (<?php echo $numLogged;?>)</div>
                                      	<div class="tools">
                                        	<a href="javascript:;" class="collapse"></a>
                                        	<!--<a href="javascript:;" class="reload"></a>-->
                                      	</div><!--tools-->
                                    </div><!--portlet-title-->
                                    <div class="portlet-body form">
                                    	<div class="table-responsive">
                                            <table class="table table-striped table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Member (username) (user ID)</th>
                                                        <th>Session ID</th>
                                                        <th>Time Logged In</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
													
													$logCnt = 0;
													while($loggedIn = $rsGet->fetch(PDO::FETCH_ASSOC)){
														$logCnt++;
														?>
                                                        <tr>
                                                        	<td><?php echo $logCnt;?></td>
                                                            <td><a href="?page=04-00-00-001&member=<?php echo $loggedIn['member_id'];?>" target="_blank"><?php echo $loggedIn['username'];?> (<?php echo $loggedIn['member_id'];?>)</a></td>
                                                            <td><?php echo $loggedIn['session_id'];?></td>
                                                            <td><?php echo date('m/d/Y @ g:i A', $loggedIn['login_timestamp']);?></td>
                                                            
                                                        </tr>
                                                        <?php
													}
                                                    ?>
                                                </tbody>
                                            </table>
										</div><!--table-responsive-->
                                    </div><!--END PORTLET BODY-->
                                </div><!--END PORTLET-->
                            
                            </div><!--END TAB 2-->
                        
                        
                        </div><!--tab-content-->
                    </div><!--tabbable tabbable-custom-->
                    
                </div><!--col-md-12-->
            </div><!--row-->
            <!-- END PAGE CONTENT-->    
   