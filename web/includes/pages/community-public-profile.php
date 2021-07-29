<?php
/*
Marketocracy Inc. | Beta Development
Community Public Profile Scripts

Supporting files:
	AJAX		- process/ajax/community-profile-processes.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/community-profile.js.php
	HTML		- includes/pages/community-public-profile.php
*/
//echo 'hello'.$_SESSION['rank_date'];
$tab = $_REQUEST['tab'];

$memberID = $_REQUEST['member'];

if(!isset($memberID)){
	$memberID = $_SESSION['member_id'];	
}

//Get member details from function
$member = get_member($mLink, $memberID, 'all');

$memberName = $member['fullName'];
$memberImgURL = $member['profileImageURL'];
$memberUsername =  $member['username'];

$query = "
	SELECT m.*, p.*
	FROM ".$_SESSION['members_table']." as m
	INNER JOIN ".$_SESSION['members_profile_table']." as p ON p.member_id=m.member_id 
	WHERE m.member_id=:member_id
";

try{
	$rsMember = $mLink->prepare($query);
	$aValues = array(
		':member_id' 	=> $memberID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsMember->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$member = $rsMember->fetch(PDO::FETCH_ASSOC);


$query = "
	SELECT f.fund_symbol, f.assets, f.unix_date, f.fund_name, f.description, f.fund_id, f.public, fs.private, fs.allowed_connect_groups as groups, fs.allowed_connect_members as connects, fs.*
	FROM ".$_SESSION['fund_table']." AS f
	INNER JOIN ".$_SESSION['fund_settings_table']." AS fs ON fs.fund_id=f.fund_id
	WHERE member_id=:member_id AND active='1'
	ORDER BY f.weight ASC
";

try{
	$rsFunds = $mLink->prepare($query);
	$aValues = array(
		':member_id' 	=> $memberID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsFunds->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

while($fund = $rsFunds->fetch(PDO::FETCH_ASSOC)){
	
	$fundID 	= $fund['fund_id'];
	$fundSymbol	= $fund['fund_symbol'];
	$private	= $fund['private'];
	$aGroups	= explode('|',$fund['groups']);
	$aConnects	= explode('|',$fund['connects']);
	
	//loop through each group id and grab its associated members
	foreach($aGroups as $key=>$groupID){
		
		$aGroupMembers = get_group($mLink, $groupID);
		
		//loop through each member and push them onto the Member Connections array
		foreach($aGroupMembers as $key=>$connects){
			array_push($aConnects, $connects);	
		}
	}
	
	//Remove duplicates from array
	$aAllowConnects = array_unique($aConnects);
	
	//Store all variable in array to use down page
	$aFunds[$fundID] = array(
		'fund_symbol' 	=> $fundSymbol,
		'fund_name'		=> $fund['fund_name'],
		'private'		=> $private,
		'public'		=> $fund['public'],
		'aGroups'		=> $aGroups,
		'aConnects'		=> $aAllowConnects,
		'assets'		=> $fund['assets']
	);
	
	
	if($fund['assets'] != NULL){
		//echo 'hello';echo $fund['assets'];
		$hasComposite = '1';	
	}
	
	//GET BASEBALL CARD DATA
	$hour = 12;
	$today              = strtotime("$hour:00:00");
	$yesterday          = strtotime("-1 day", $today);
	//Get seconds since inception, then years since inception
	$seconds = $yesterday - $fund['unix_date'];
	$years = $seconds / 31536000;
	$yearsRound = floor($years);
	
	//calaculate Annualized Return
	$query = "
		SELECT price, totalValue
		FROM ".$_SESSION['fund_pricing_table']."
		WHERE fund_id=:fund_id
		ORDER BY unix_date DESC
		LIMIT 1
	";
	try{
		$rsFundPrice = $mLink->prepare($query);
		$aValues = array(
			':fund_id' 	=> $fundID
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsFundPrice->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$fundPrice = $rsFundPrice->fetch(PDO::FETCH_ASSOC);
	//Get latest fund price
	$currentValue = $fundPrice['totalValue'];
	
	
	$base = $currentValue / $inceptionStart;
	$exp = 1 / $years;
	
	$AAR = ((pow($base, $exp))-1)*100;
	
	//echo $query;
	//Calculate S&P AAR same period
	$stockIndex = '^SP500TR';
	
	$query = "
	SELECT * 
	FROM `stock_index_history`
	WHERE `index`='^SP500TR' 
	AND date=(SELECT date FROM `stock_index_history` WHERE `index`='^SP500TR' AND date LIKE :date ORDER BY unix_date DESC LIMIT 1) 
	OR `index`='^SP500TR' 
	AND date=(SELECT date FROM `stock_index_history` WHERE `index`='^SP500TR' ORDER BY unix_date DESC LIMIT 1)
	ORDER BY unix_date ASC";
	try{
		$rsSP = $mLink->prepare($query);
		$aValues = array(
			':date' 	=> date('Y-m-', $fund['unix_date']).'%'
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsSP->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$cnt = 0;
	while($sp = $rsSP->fetch(PDO::FETCH_ASSOC)){
		
		$aSP[$cnt] = $sp['close'];
		
		$cnt++;
		
	}
	
	$base = $aSP[1] / $aSP[0];
	$exp = 1 / $years;
	
	$spAAR = ((pow($base, $exp))-1)*100;
	
	$query = "
		SELECT *
		FROM rank_achievements
		WHERE fund_id=:fund_id AND as_of_date=:rankDate
	";
	
	try{
		$rsBadges = $mLink->prepare($query);
		$aValues = array(
			':fund_id' 	=> $fundID,
			':rankDate'	=> $_SESSION['rank_date']
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsBadges->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	//echo $_SESSION['rank_date'].'here';
	$aFundBadges = array();
	$aFundBadges2 = array();
	
	while($badge = $rsBadges->fetch(PDO::FETCH_ASSOC)){
		
		$rankDate		= $badge['as_of_date'];
		$rankUnixDate	= $badge['as_of_timestamp'];
		
		$aFundBadges2[]	= $badge['badge_id'];
		
		$aFundBadges[$badge['fund_id']] = array(
			'rank_unix_date'		=> $rankDate,
			'rank_date'				=> $rankUnixDate,
			'badgeIds'				=> $aFundBadges2,
		);
		
	}
	
	$listBadgesNew = implode(',',$aFundBadges[$fundID]['badgeIds']);
	
	//Get Badges
	$query = "
		SELECT *
		FROM rankings_process_results
		WHERE fund_id=:fund_id
		ORDER BY rank_unix_date DESC
		LIMIT 1
	";
	try{
		$rsBadges = $mLink->prepare($query);
		$aValues = array(
			':fund_id' 	=> $fundID
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsBadges->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$getBadges = $rsBadges->fetch(PDO::FETCH_ASSOC);
	
	$listBadges = $getBadges['badge_ids'];
	
	$aFundsSettings[$fund['fund_id']] = array(
		'fund_id'			=> $fund['fund_id'],
		'fund_symbol'		=> $fund['fund_symbol'],
		'fund_name'			=> $fund['fund_name'],
		'unix_date'			=> $fund['unix_date'],
		'fund_desc'			=> $fund['description'],
		'fund_color'		=> $fund['fund_color'],
		'private'			=> $fund['private'],
		'connect_groups'	=> $fund['allowed_connect_groups'],
		'connect_members'	=> $fund['allowed_connect_members'],
		'badges'			=> $listBadgesNew/*$fund['badges']*/,
		'badgeDate'			=> $getBadges['rank_unix_date'],
		'public'			=> $fund['public'],
		'spAAR'				=> $spAAR,
		'AAR'				=> $AAR,
		'yearsRound'		=> $yearsRound,
		'currentValue'		=> $currentValue,
		'inceptionStart'	=> $inceptionStart,
		'years'				=> $years,
		'yesterday'			=> $yesterday,
		'inception'			=> $fund['unix_date'],
		'assets'			=> $fund['assets'],
		'tenYearAAR'		=> $getBadges['tenYearAAR'],
		'fiveYearAAR'		=> $getBadges['fiveYearAAR'],
		'threeYearAAR'		=> $getBadges['threeYearAAR'],
		'oneYearAAR'		=> $getBadges['oneYearAAR'],
		'tenYearNAV'		=> $getBadges['tenYearNAV'],
		'fiveYearNAV'		=> $getBadges['fiveYearNAV'],
		'threeYearNAV'		=> $getBadges['threeYearNAV'],
		'oneYearNAV'		=> $getBadges['oneYearNAV'],
		'tenYearAARsp'		=> $getBadges['tenYearAARsp'],
		'fiveYearAARsp'		=> $getBadges['fiveYearAARsp'],
		'threeYearAARsp'	=> $getBadges['threeYearAARsp'],
		'oneYearAARsp'		=> $getBadges['oneYearAARsp'],
		'rankUnixDate'		=> $getBadges['rank_unix_date']
	);
	
}

$aBadgeGroups = array(
	10 	=> array(1,5,9,13),
	5	=> array(2,6,10,14),
	3 	=> array(3,7,11,15),
	1 	=> array(4,8,12,16),
);

//START build badge array
$query = "
	SELECT * 
	FROM ".$_SESSION['badges_table']."
	WHERE active='1'
";

try{
	$rsBadge = $mLink->prepare($query);
	$aValues = array();
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsBadge->execute($aValues);
}

catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$aBadge = array();
	
while($badge = $rsBadge->fetch(PDO::FETCH_ASSOC)){
	$aBadges[$badge['badge_id']] = array(
		'badge_id'		=> $badge['badge_id'],
		'badge_name'	=> $badge['badge_name'],
		'type'			=> $badge['badge_type'],
		'badge_img'		=> '/images/badges/'.$badge['badge'],
		'badge_desc'	=> $badge['badge_desc']
	);
	
}
//END build badge array
//END build badge array
?>      
		<!-- BEGIN Confirm MODAL-->
        <div class="modal fade" id="sub-conf" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog">
             <div class="modal-content">
                
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Subscription Confirmation</h4>
                </div>
            	
                <div class="modal-body" id="subscription-confirm">
                	
                    <p>You have successfully subscribed to the manager. Please look out for the confirmation email sent to the address that you provided.</p>
                
        		</div><!--modal-body-->
                
                <div class="modal-footer">
                    <a href="?page=01-00-00-003" class="btn btn-info">Go To Action Center</a>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
                
                
             </div>
             <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- END Confirm MODAL-->

		<!-- BEGIN SEND REQUEST MODAL-->
        <div class="modal fade" id="track-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog">
             <div class="modal-content">
                
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Track <?php echo $memberName;?></h4>
                </div>
            	
                <form action="" method="post" name="track-manager" id="track-manager">
                <div class="modal-body">
                	
                    <div id="update-note">
                        <div class="note note-info">
                            <p>If you would like to track the funds of <?php echo $memberName;?>, please select the funds you would like to track. </p>
                        </div>
                    </div><!--update-note-->
                    
                    <div id="form-errors">
                    
                    </div>
                    
                   <?php /*?> <div class="form-group">
                    	<label class="control-label">First Name*</label>
                        <input type="text" name="name_first" class="form-control" value="<?php echo $_SESSION['first_name'];?>" />
                    </div>
                    
                    <div class="form-group">
                    	<label class="control-label">Last Name</label>
                        <input type="text" name="name_last" class="form-control" value="<?php echo $_SESSION['last_name'];?>" />
                    </div>
                    
                    <div class="form-group">
                    	<label class="control-label">Email*</label>
                        <input type="text" name="email" class="form-control" value="<?php echo $_SESSION['user_email'];?>" />
                    </div><?php */?>
                    
                    <input type="hidden" name="name_first" value="<?php echo $_SESSION['first_name'];?>" />
                    <input type="hidden" name="name_last" value="<?php echo $_SESSION['last_name'];?>" />
                    <input type="hidden" name="email" value="<?php echo $_SESSION['user_email'];?>" />
                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['member_id'];?>" />
                    
                    <div class="form-group">
                    	<label class="control-label">Email Frequency*</label>
                        <select name="frequency" class="form-control">
                        	<option value="unsubscribe">None</option>
							<?php /*?><option value="weekly">Weekly</option><?php */?>
                            <option value="monthly" selected>Monthly</option>
                            <option value="quarterly">Quarterly</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="fund-boxes">
                    	
                        <label class="control-label">Select Funds To Track*</label><br />
                        <?php
						//Check to see if member is already tracking funds
						$query = "
							SELECT track_funds
							FROM ".$_SESSION['fund_tracking_table']."
							WHERE member_id=:member_id AND user_id=:user_id
						";
						try{
							$rsGetTrack = $mLink->prepare($query);
							$aValues = array(
								':member_id'		=> $memberID,
								':user_id'			=> $_SESSION['member_id']
							);
							$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
							$rsGetTrack->execute($aValues);
						}
						catch(PDOException $error){
							// Log any error
							file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
						}	
						$tracking = $rsGetTrack->fetch(PDO::FETCH_ASSOC);
						
						$aTrackingFunds = explode('|', $tracking['track_funds']);
						
						foreach($aFunds as $fundID=>$aFundInfo){
							
							if(in_array($fundID, $aTrackingFunds)){
								$showTrack = '<span class="label label-success">Tracking</span>';	
							}else{
								$showTrack = '';	
							}
							
							if($aFundInfo['private'] == '0'){
								echo '<span style="display:block; margin-bottom:10px;"><input type="checkbox" id="fund-'.$fundID.'" name="funds[]" class="form-control" value="'.$fundID.'|'.$aFundInfo['fund_symbol'].'" /> '.$aFundInfo['fund_symbol'].' '.$showTrack.'</span>';
							}
						}
						?>
                        
                    </div>
                    
                    <hr />
                    
                    <div class="form-group">
                    	<label class="control-label"><input type="checkbox" name="manager-updates" class="form-control" value="1" checked /> I would like to receive updates from the manager.</label><br />
                        <small>If checked, the manager may choose to send email updates to your email address. Maximum of once a week.</small>
                    </div>
                    
                    <div class="form-group">
                    	<label class="control-label"><input type="checkbox" name="manager-articles" class="form-control" value="1" checked /> I would like to receive notifications when this manager publishes an article.</label><br />
                        <small>If checked, you will recieve an email notification with a link to the manager's article.</small>
                    </div>
                    
                    <div class="note note-warning">
                    	<p>The opinions expressed in articles and emails are those of the manager, and do not reflect in any way those of MyTrackRecord.com or Marketocracy Inc.</p>
                    </div>
                    
                    <input type="hidden" name="username" value="<?php echo $_SESSION['username'];?>" />
                    <input type="hidden" name="member_id" value="<?php echo $memberID;?>" />
                </div><!--modal-body-->
                
                <div class="modal-footer">
                	<button type="submit" class="btn btn-success" id="submit-request">Track Manager</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal" onclick="clearForm('track-manager');" id="exit-btn">No Thanks, Maybe Later</button>
                </div>
                </form>
                
                
             </div>
             <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- END SEND REQUEST MODAL-->

		<!-- BEGIN SEND REQUEST MODAL-->
        <div class="modal fade" id="connect" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog">
             <div class="modal-content">
                
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Connect with <?php echo $memberName;?></h4>
                </div>
            	
                <form action="" method="post" name="send-request" id="send-request">
                <div class="modal-body">
                
                    <div class="form-group">
                    	<label class="control-label">Add Message</label>
                        <textarea name="request_msg" class="form-control">Hi <?php echo $memberName;?>, I would like to connect with you! Please accept my Connection Request!</textarea>
                        <span class="help-block">Sometimes it helps to send a message with a connection request.</span>
                    </div>
                    
                    <input type="hidden" name="connection" value="<?php echo $memberID;?>" />
                </div><!--modal-body-->
                
                <div class="modal-footer">
                	<button type="submit" class="btn btn-success" id="submit-request">Send Request</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal" onclick="closeGroup();" id="exit-btn">Cancel</button>
                </div>
                </form>
                
                
             </div>
             <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- END SEND REQUEST MODAL-->
        
        <!-- BEGIN REMOVE CONNECTION MODAL-->
        <div class="modal fade" id="remove-connect" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog">
             <div class="modal-content">
                
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Remove connection with <?php echo $memberName;?></h4>
                </div>
            	
                <form action="" method="post" name="remove-connection" id="remove-connection">
                <div class="modal-body">
                	<p>Are you sure you want to remove this connection.</p>
                    
                    
                    <input type="hidden" name="connection" value="<?php echo $memberID;?>" />
                </div><!--modal-body-->
                
                <div class="modal-footer">
                	<button type="submit" class="btn btn-danger" id="submit-request">Remove Connection</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal" onclick="closeGroup();" id="exit-btn">Cancel</button>
                </div>
                </form>
                
                
             </div>
             <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- END REMOVE CONNECTION MODAL-->
   
        <!-- BEGIN PAGE CONTENT-->
        <div class="row profile">
            <div class="col-md-12">
                <!--BEGIN TABS-->
                <div class="tabbable tabbable-custom">
                    <ul class="nav nav-tabs">
                        <li <?php if($tab == ""){?>class="active"<?php }?>><a href="?page=04-00-00-001&member=<?php echo $memberID;?>">Overview</a></li>
                        <?php 
						if($_SESSION['member_id'] == $memberID){
                        if($_SESSION['flag_promote'] == '1'){
						?>
                        	<li <?php if($tab == "dashboard"){?>class="active"<?php }?>><a href="?page=01-00-00-003">Action Center</a></li>
                        <?php	
						}}
						?>
                        <?php
						
                        foreach($aFunds as $fundID=>$aFund){
                            
                            $fundSymbol	= $aFund['fund_symbol'];
							$aConnects	= $aFund['aConnects'];
							$private	= $aFund['private'];
							
							if(in_array($_SESSION['member_id'], $aConnects) || $_SESSION['member_id'] == $memberID || $_SESSION['admin'] == '1' || $private == '0'){
								?>
								<li <?php if($tab == $fundID){?>class="active"<?php }?>><a href="?page=04-00-00-001&member=<?php echo $memberID;?>&tab=<?php echo $fundID;?>" ><?php echo $fundSymbol; ?></a></li>
								<?php
							}
                        }
                        ?>
                     
                    
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane <?php if($tab == ""){?>active<?php } ?>" id="tab_1_1">
                            
                            <div class="row">
                                <div class="col-md-2">
                                    <ul class="list-unstyled profile-nav">
                                    <li><img src="<?php echo $memberImgURL;?>" class="img-responsive" alt="" /> 
                                    <?php if($memberID == $_SESSION['member_id']){?>
                                    	<a href="?page=10-00-00-002&account=1&tab=picture" class="profile-edit"><i class="icon-cog"></i></a>
									<?php }?>
                                    </li>
                                    <?php /*?> <li><a href="#">Projects</a></li><?php */?>
                                   	<?php 
									if($_SESSION['member_id'] == $memberID){
                        			if($_SESSION['flag_promote'] == '1'){
									?>
                                    <li><a href="?page=04-00-00-002">Articles <span>1</span></a></li>
                                    <?php }}?>
                                    <li><a href="javascript:void(0);">Connections <span><?php echo count(get_member_connections($mLink, $memberID));?></span></a></li>
                                    <?php /*?><li><a href="#">Settings</a></li><?php */?>
                                    </ul>
                                </div><!--col-md-2-->
                                
                                <div class="col-md-10">
                                    <div class="row">
                                        <div class="col-md-8 profile-info" >
                                            
                                            <div class="row profile-area" style="margin-bottom:10px;">
                                            	<div class="col-md-12" style="border: 1px solid #DDDDDD;padding:0px;border-radius:3px;">
                                                    
                                                    <div class="row" style="padding:10px;">
                                                    
                                                        <div class="col-md-8">
                                                        	
                                                            <h1><strong><?php echo $memberName;?></strong></h1>
                                                            
                                                            <h4><?php echo $member['occupation'];?></h4>
                                                            
                                                            <ul class="list-inline">
                                                                <?php if(!empty($member['state'])){?>
                                                                <li title="Location"><i class="icon-map-marker"></i> <?php echo $member['city'];?>, <?php echo $member['state'];?></li>
                                                                <?php } ?>
                                                                <li title="Member Since"><i class="icon-calendar" ></i> <?php echo date('M d, Y', $member['joined_timestamp']);?></li>
                                                                <?php 
																if($_SESSION['admin'] == '1'){
																	?>
                                                                    <li title="Member Email"><i class="icon-envelope"></i> <a href="mailto:<?php echo $member['email'];?>"><?php echo $member['email'];?></a></li>
                                                                    <?php	
																}
																?>
                                                                <?php /*?><li><i class="icon-briefcase"></i> Marketing</li>
                                                                <li><i class="icon-star"></i> Top Trader</li>
                                                                <li><i class="icon-heart"></i> Stocks</li><?php */?>
                                                            </ul>
                                                        </div><!--col-md-8-->
                                                        
                                                        <div class="col-md-4" style="text-align:right;">
                                                            <span id="connect-btn">
                                                            <?php 
															//Track Btn
															if($memberID != $_SESSION['member_id']){
																
																$query = "
																	SELECT *
																	FROM ".$_SESSION['fund_tracking_table']."
																	WHERE member_id=:member_id AND user_id=:user_id
																";
																try{
																	$rsTracking = $mLink->prepare($query);
																	$aValues = array(
																		':member_id' 	=> $memberID,
																		':user_id'		=> $_SESSION['member_id']
																	);
																	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
																	$rsTracking->execute($aValues);
																}
																catch(PDOException $error){
																	// Log any error
																	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
																}
																
																$track = $rsTracking->fetch(PDO::FETCH_ASSOC);
																
																$aTrackFunds = explode('|', $track['track_funds']);
																
																if($_SESSION['admin'] == '1'){
																	
																	echo '<a href="#track-modal" data-toggle="modal" class="btn btn-warning">Track Manager</a> ';
																		
																}
																	
															}#end check $memberID != $_SESSION['member_id']
															
															//Connection Button
															$query = "
																SELECT *
																FROM ".$_SESSION['connections_table']."
																WHERE member_id=:member_id AND connection=:connection AND status='pending'
															";
															try{
																$rsConnect2 = $mLink->prepare($query);
																$aValues = array(
																	':member_id' 	=> $memberID,
																	':connection'	=> $_SESSION['member_id']
																);
																$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
																$rsConnect2->execute($aValues);
															}
															catch(PDOException $error){
																// Log any error
																file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
															}
															
															$connect2 = $rsConnect2->fetch(PDO::FETCH_ASSOC);
															
															$query = "
																SELECT *
																FROM ".$_SESSION['connections_table']."
																WHERE member_id=:member_id AND connection=:connection 
															";
															try{
																$rsConnect = $mLink->prepare($query);
																$aValues = array(
																	':member_id' 	=> $_SESSION['member_id'],
																	':connection'	=> $memberID
																);
																$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
																$rsConnect->execute($aValues);
															}
															catch(PDOException $error){
																// Log any error
																file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
															}
															
															$connect = $rsConnect->fetch(PDO::FETCH_ASSOC);
															
															$status = $connect['status'];
															
															if($connect2['member_id'] == $memberID){
																$status = 'confirm';	
															}
															
															switch($status){
																case 'active'	: 
																	$connectBtn = '
																		<div class="btn-group">
																			<button type="button" class="btn btn-success">Connected</button>
																			<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown"><i class="icon-angle-down"></i></button>
																			<ul class="dropdown-menu" role="menu" id="connect-menu">
																				<li><a href="javascript:void(0);">More Soon!</a></li>
																				<li class="divider"></li>
																				<li><a href="#remove-connect" data-toggle="modal">Remove Connection</a></li>
																			</ul>
																		</div>
																	';
																break;
																
																case 'pending'	: $connectBtn = '<button type="button" class="btn btn-warning">Request Pending</button>';break;
																case 'blocked'	: $connectBtn = '';break;
																case 'confirm'	: $connectBtn = '<button type="button" class="btn btn-warning" title="This member has sent you a connection request. Click here to accept their request.">Accept Request</button>';break;
																default			: $connectBtn = '<a class="btn btn-info" href="#connect" data-toggle="modal">Connect</a>';break;
															}
															
															
															if($memberID == $_SESSION['member_id']){
                                                            	echo '<a href="?page=10-00-00-002&account=1" class="btn btn-default" style="background:#EBEBEB;">Edit Profile</a>';
															}else{
																echo $connectBtn;
															}
															
															/*if($_SESSION['admin'] == '1'){
																echo $preparedQuery;	
															}*/
															?>											
                                                            </span>
                                                            
                                                            
                                                            
                                                        </div><!--col-md-4-->
                                                    </div><!--row-->
                                                    
                                                    <div class="row" style="background:#EFEFEF;margin:0px;padding:10px 0px 0px 0px;">
                                                        <div class="col-md-12">
                                                        
                                                        <?php 
														if($_SESSION['flag_promote'] == '1'){
															$publicLink = 'https://'.$memberUsername.'.mytrackrecord.com';
														}else{
															$publicLink = 'javascript:void(0);';	
														}
														?>
                                                            <p><img src="images/logo-icon.png" width="20" height="20" alt="Marketocracy" title="Marketocracy" /> <a href="<?php echo $publicLink;?>" target="_blank"><?php echo $memberUsername;?>.mytrackrecord.com</a> <i class="icon-arrow-left"></i> Coming Soon</p>
                                                        </div><!--col-md-12-->
                                                    </div><!--row-->
                                        		
                                                </div><!--col-md-12-->
                                            </div><!--row profile-area-->
                                            
                                            <div class="row profile-area" style="margin-bottom:10px;">
                                            	<div class="col-md-12" style="border: 1px solid #DDDDDD;padding:0px;border-radius:3px;">
                                                	<?php if($memberID == $_SESSION['member_id']){?>
                                    					<a href="?page=10-00-00-002&account=1&tab=profile" class="profile-edit" style="opacity: 0.6;display:block;position:absolute;top:0;right:0;background:#000;color:#fff;padding:3px 9px;"><i class="icon-cog"></i></a>
													<?php }?>
                                                    <div class="row">
                                                    	<div class="col-md-3" >
                                                        	<h5 style="background:#39B3D7;margin:0px 0px 0px 0px;padding:10px;text-align:center;color:#ffffff;"><strong>About Member</strong></h5>
                                                        </div><!--col-md-4-->
                                                    </div><!--row-->
                                                    
                                                	<div class="row">
                                                        <div class="col-md-12" style="padding:10px 30px 30px 30px;">
                                                            <h4>Summary</h4>
                                                            <p><?php echo $member['about_me'];?></p>
                                                            <hr />
                                                        </div><!--col-md-12-->
                                                    </div><!--row-->
                                                    
                                                </div><!--col-md-12-->
                                            </div><!--row profile-area-->
                                            
                                            <div class="row profile-area" style="margin-bottom:10px;">
                                            	<div class="col-md-12" style="border: 1px solid #DDDDDD;padding:0px;border-radius:3px;">
                                                	<?php if($memberID == $_SESSION['member_id']){?>
                                    					<a href="?page=10-00-00-002&account=3" class="profile-edit" style="opacity: 0.6;display:block;position:absolute;top:0;right:0;background:#000;color:#fff;padding:3px 9px;"><i class="icon-cog"></i></a>
													<?php }?>
                                                    
                                                    <div class="row">
                                                    	<div class="col-md-3" >
                                                        	<h5 style="background:#39B3D7;margin:0px 0px 0px 0px;padding:10px;text-align:center;color:#ffffff;"><strong>Connections</strong></h5>
                                                        </div><!--col-md-4-->
                                                    </div><!--row-->
                                                    
                                                	<div class="row">
                                                        <div class="col-md-12" style="padding:10px 30px 30px 30px;">
                                                            <div class="row" style="margin:0px;" id="my-connections">
                                            
																<?php
                                                                
                                                                $query = "
                                                                    SELECT *
                                                                    FROM ".$_SESSION['connections_table']."
                                                                    WHERE member_id=:member_id AND status='active' 
                                                                ";
                                                                try{
                                                                    $rsConnect = $mLink->prepare($query);
                                                                    $aValues = array(
                                                                        ':member_id' 	=> $memberID
                                                                    );
                                                                    $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                                                    $rsConnect->execute($aValues);
                                                                }
                                                                catch(PDOException $error){
                                                                    // Log any error
                                                                    file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                                                }
                                                                
                                                                $cnt = 0;
                                                                
                                                                while($connect = $rsConnect->fetch(PDO::FETCH_ASSOC)){
                                                                    
                                                                    $cnt++;
                                                                            
                                                                    $aMember = get_member($mLink, $connect['connection'], 'all');
                                                                    
                                                                    ?>
                                                                    <div class="col-md-4 connection" style="border: none !important;padding-right:10px;">
                                                                        <div class="row">
                                                                        	<div class="col-md-12" style="border:1px solid #ccc !important;margin:0px 0px 0px 0px;padding:0px;">
                                                                            	
                                                                                <div class="row">
                                                                                    <div class="col-md-4">
                                                                                        <a href="?page=04-00-00-001&member=<?php echo $connect['connection'];?>"><img class="img-responsive" alt="" src="<?php echo $aMember['profileImageURL'];?>"></a>
                                                                                    </div><!--col-md-5-->
                                                                                    
                                                                                    <div class="col-md-8">
                                                                                        <h5><a href="?page=04-00-00-001&member=<?php echo $connect['connection'];?>"><strong><?php echo $aMember['fullName'];?></strong></a></h5>
                                                                                        <ul class="list-inline" style="margin:0px;padding:0px;">
                                                                                            <?php if(!empty($aMember['state'])){?>
                                                                                            <li title="Location" style="padding-left:0px;"><i class="icon-map-marker"></i> <?php echo $aMember['city'];?>, <?php echo $aMember['state'];?></li>
                                                                                            <?php } ?>
                                                                                            <li title="Member Since" style="padding-left:0px;"><i class="icon-calendar" ></i> <?php echo date('M d, Y', $aMember['joinDate']);?></li>
                                                                                        </ul>
                                                                                    </div><!--col-md-7-->
                                                                            	</div><!--row-->
                                                                                    
                                                                        	</div><!--col-md-12-->
                                                                        </div><!--row--> 
                                                                    </div><!--col-md-4-->
                                                                    <?php
                                                                }
																
																if($cnt == 0){
																	?>
                                                                    <div class="col-md-4 connection" style="border: none !important;padding-right:10px;">
                                                                        <div class="row">
                                                                        	<div class="col-md-12" style="border:1px solid #ccc !important;margin:0px 0px 0px 0px;padding:0px;">
                                                                            	
                                                                                <div class="row">
                                                                                    <div class="col-md-4">
                                                                                        <a href="javascript:void(0);"><img class="img-responsive" alt="" src="images/profile/default-profile.png"></a>
                                                                                    </div><!--col-md-5-->
                                                                                    
                                                                                    <div class="col-md-8">
                                                                                        <h5><a href="javascript:void(0);"><strong>No Connections</strong></a></h5>
                                                                                       	<p>Connect with members by visiting other members profiles and sending an invite.</p>
                                                                                    </div><!--col-md-7-->
                                                                            	</div><!--row-->
                                                                                    
                                                                        	</div><!--col-md-12-->
                                                                        </div><!--row--> 
                                                                    </div><!--col-md-4-->
                                                                    <?php	
																}
                                                                ?>
                                                                
                                                                
                                                               
                                                                
                                                            </div><!--row-->
                                                        </div><!--col-md-12-->
                                                    </div><!--row-->
                                                    
                                                </div><!--col-md-12-->
                                            </div><!--row profile-area-->
                                            
                                        </div>
                                        <!--end col-md-8-->
                                        
                                        <div class="col-md-4" <?php //if($_SESSION['admin'] != '1'){echo 'style="display:none;"';}?>>
                                            
                                            <?php if($_REQUEST['debug'] == 'badges'){
												echo '<pre>';
												print_r($aFundsSettings);
												echo '</pre>';	
											}
											?>
                                            
                                           
                                            
                                            <div class="portlet">
                                                <div class="portlet-title">
                                                    <div class="caption">Badges</div>
                                                    <div class="tools">
                                                    	<a class="reload" href="javascript:;"></a>
                                                    </div><!--tools-->
                                                </div><!--portlet-title-->
                                                <div class="portlet-body">
                                                
                                                <?php
												foreach($aFundsSettings as $fundID=>$aSetting){
													
													$fundSymbol2 	= get_funds($mLink, $fundID, 'fundSymbol');
													
													if($aSetting['badges'] != ''){
														
														echo '<h4>'.$fundSymbol2.'</h4>';
														
														$aFundBadges 	= explode(',',$aSetting['badges']);
														
														foreach($aFundBadges as $key=>$badgeID){
															 
															 if(array_key_exists($badgeID, $aBadges)){
																$badgeImg 	= $aBadges[$badgeID]['badge_img'];
																$badgeDesc	= $aBadges[$badgeID]['badge_desc'];
																
																//echo $badgeImg;
																
																echo '<img src="'.$badgeImg.'" alt="'.$badgeDesc.'" title="'.$fundSymbol2.': '.$badgeDesc.'" width="40" height="40" />&nbsp;&nbsp;'; 
															 }
															 
														}
														
														echo '<hr />';
													}
														
												}
												
												
												?>
                                              	
                                                </div><!--portlet-body-->
                                            </div><!--end-portlet-->
                                            
                                        </div><!--end col-md-4-->
                                    </div><!--end row-->
                                    
                                    
                                    
                                    
                                    
                            	</div><!--col-md-10-->
                            </div><!--row-->
                        </div><!--tab_1_1-->
                        <!--tab_1_2-->
                        
                        <?php
						if($tab != 'dashboard'){
							?>
                            <div class="tab-pane <?php if($tab != ""){?>active<?php } ?>" id="<?php echo $tab;?>">
                                
                                <?php if($_SESSION['admin'] == '1'){?>
                                <pre>
                                <?php //print_r($aFunds);?>
                                </pre>
                                <?php } ?>
                                
                                <?php
                                $aConnects = $aFunds[$tab]['aConnects'];
                                $private	= $aFunds[$tab]['private'];
                                
                                if(in_array($_SESSION['member_id'], $aConnects) || $_SESSION['member_id'] == $memberID || $_SESSION['admin'] == '1' || $private == '0'){
                                ?>
                                
                                <div class="row">
                                
                                    <div class="col-md-6">
                                        <h3><?php echo get_funds($mLink, $tab, 'fundName');?></h3>
                                    </div><!--col-md-6-->
                                    
                                    <?php if($_REQUEST['member'] == $_SESSION['member_id']){?>
                                    <div class="col-md-6" style="text-align:right;">
                                        <a href="?page=10-00-00-002&account=2&myfund=<?php echo $tab;?>" class="btn btn-sm btn-info" ><i class="icon-cog"></i> <span class="xs-hidden">Fund Settings</span></a>
                                    </div>
                                    <?php } ?>
                                    
                                    <div class="col-md-6" style="text-align:right;display:none;">
                                    <img src="/images/badges/Gold-10Year.png" width="50" height="50" title="BMF beat the top performing mutual funds over the past 10 years." />&nbsp;&nbsp;
                                            <img src="/images/badges/Bronze-5Year.png" width="50" height="50" title="BMF beat 50% of mutual funds over the past 5 years." />&nbsp;&nbsp;
                                                    <img src="/images/badges/Silver-3Year.png" width="50" height="50" title="BMF beat 75% of mutal funds over the past 3 years." />&nbsp;&nbsp;
                                                    <img src="/images/badges/Gold-1Year.png" width="50" height="50" title="BMF beat the top performing mutual funds over the past year." /><br /><br />
                                    </div><!--col-md-6-->
                                    
                                    
                                </div><!--row-->
                                
                                <div class="row">
                                    <div class="col-md-6 col-sm-12 column sortable" id="col1">
                                        <?php
                                        
                                        $fundID = $tab;
                                        
                                        $query = "
                                            SELECT overview_col1, overview_col2 
                                            FROM members_fund_settings
                                            WHERE fund_id=:fund_id
                                        ";
                            
                                        //Fund Symbols
                                        try{
                                            $rsDashOrder = $mLink->prepare($query);
                                            $aValues = array(
                                                ':fund_id'		=> $fundID
                                            );
                                            $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                            $rsDashOrder->execute($aValues);
                                        }
                                        catch(PDOException $error){
                                            // Log any error
                                            file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                        }
                                        
                                        $rowOrder = $rsDashOrder->fetch(PDO::FETCH_ASSOC);
                                        
                                        
                                        $column1 = explode('|', $rowOrder['overview_col1']);
                                        
                                        foreach($column1 as $key => $value) {
                                            //Expand portlet ID and set variables
                                            $portletID 	= $value;
                                            $portlet 	= explode("~",$portletID);
                                            //Set Variables
                                            $portID		= $portlet[0];
                                            if($portlet[1] != "0"){
                                                $portVar1	= $portlet[1];
                                            }
                                            if($portlet[2] != "0"){
                                                $portVar2	= $portlet[2];
                                            }
                                            if($portlet[3] != "0"){
                                                $portVar3	= $portlet[3];
                                            }
                                            
                                            include('includes/portlets/'.$portID.'.php');
                                        }
                                        ?>
                                       
                                    </div><!--END COLUMN-->
                                    
                                    <div class="col-md-6 col-sm-12 column sortable" id="col2">
                                        <?php
                                       $column2 = explode('|', $rowOrder['overview_col2']);
                                        
                                        $fundID = $tab;
                                        
                                        foreach($column2 as $key => $value) {
                                            //Expand portlet ID and set variables
                                            $portletID 	= $value;
                                            $portlet 	= explode("~",$portletID);
                                            //Set Variables
                                            $portID		= $portlet[0];
                                            if($portlet[1] != "0"){
                                                $portVar1	= $portlet[1];
                                            }
                                            if($portlet[2] != "0"){
                                                $portVar2	= $portlet[2];
                                            }
                                            if($portlet[3] != "0"){
                                                $portVar3	= $portlet[3];
                                            }
                                            
                                            include('includes/portlets/'.$portID.'.php');
                                        }
                                       ?> 
                            
                                    </div><!--END COLUMN-->
                                </div><!--row-->
                                <?php }else{  ?>
                                
                                    <div class="note note-danger">
                                        <h3>Access Denied</h3>
                                        <p>You do not have permission to view this fund. Please select the "Overview" tab, or select a fund tab above.</p>
                                    </div>
                                
                                <?php }//end access check ?>
                            </div><!--tab-pane-->
                    	<?php }else{ ?>
                    		
                            <div class="tab-pane <?php if($tab != ""){?>active<?php } ?>" id="<?php echo $tab;?>" >
                            	<div class="row">
                                	
                                    <div class="alert alert-warning">
                                    	<h4>Page Under Construction</h4>
                                        <p>Note: This page is still under contruction, please check back later.</p>
                                    </div>
                                    
                                    <div class="col-md-8">
                                    	
                                        <div class="portlet" style="border-radius:0px !important;">
                                            <div class="portlet-title">
                                                <div class="caption">Managers You Are Tracking</div>
                                                <div class="tools">
                                                    <a href="" class="collapse ballon"></a>
                                                </div><!--tools-->
                                            </div><!--portlet-title-->
                                            <div class="portlet-body" style="background: #FAFAFA;">
                                            
                                            	<table class="table table-striped table-bordered table-hover table-full-width" id="tracking_table">
                                                    <thead>
                                                        <tr>
                                                            <th>Manager</th>
                                                            <th>Fund Symbol</th>
                                                            <th>Compliant</th>
                                                            <th>% Cash</th>
                                                            <th>NAV</th>
                                                            <th>AAR</th>
                                                            <th>Return Last Week</th>
                                                            <th>Return Today</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    	<tr>
                                                        	<td><a href="javascript:void(0);">Justin Uyehara</a></td>
                                                            <td><a href="javascript:void(0);">HMF</a></td>
                                                            <td><span class="label label-success">Yes</span></td>
                                                            <td>4.39%</td>
                                                            <td>$213.15</td>
                                                            <td>30.82%</td>
                                                            <td>4.57%</td>
                                                            <td>2.11%</td>
                                                            <td><a href="javascript:void(0);"><i class="icon-ban-circle"></i> Stop Tracking</a></td>
                                                        </tr>
                                                    </tbody>
                                            	</table>
                                                
                                            </div><!--portlet-body-->
                                        </div><!--end-portlet-->
                                        
                                        <div class="portlet">
                                            <div class="portlet-title">
                                                <div class="caption">Articles</div>
                                                <div class="actions">
                                                    <a class="btn btn-info btn-xs" href="javascript:;">New Article</a>
                                                </div><!--tools-->
                                            </div><!--portlet-title-->
                                            <div class="portlet-body" style="background: #FAFAFA;">
                                            
                                            	<table class="table table-striped table-bordered table-hover table-full-width" id="articles_table">
                                                    <thead>
                                                        <tr>
                                                            <th width="1%">#</th>
                                                            <th >Article</th>
                                                            <th>Type</th>
                                                            <th>Last Modified</th>
                                                            <th>Status</th>
                                                            <th>Views</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    	<tr>
                                                        	<td>1</td>
                                                            <td><a href="javascript:void(0);">Apple Takes A Dive</a></td>
                                                            <td>Link</td>
                                                            <td>9/14/15 3:15 PM</td>
                                                            <td><span class="label label-success">Published</span></td>
                                                            <td>14</td>
                                                            <td><a href="javascript:void(0);"><i class="icon-edit"></i> Edit</a></td>
                                                        </tr>
                                                    </tbody>
                                            	</table>
                                                
                                            </div><!--portlet-body-->
                                        </div><!--end-portlet-->
                                        
                                        <div class="portlet">
                                            <div class="portlet-title">
                                                <div class="caption">Email Campaigns</div>
                                                <div class="actions">
                                                    <a class="btn btn-info btn-xs" href="javascript:;">New Campaign</a>
                                                </div><!--tools-->
                                            </div><!--portlet-title-->
                                            <div class="portlet-body" style="background: #FAFAFA;">
                                            
                                            	<table class="table table-striped table-bordered table-hover table-full-width" id="campaigns_table">
                                                    <thead>
                                                        <tr>
                                                            <th width="1%">#</th>
                                                            <th>Name</th>
                                                            <th>Subject</th>
                                                            <th>Scheduled</th>
                                                            <th>Last Modified</th>
                                                            <th>Status</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    	<tr>
                                                        	<td>1</td>
                                                            <td><a href="javascript:void(0);">Performance Update</a></td>
                                                            <td>August Update</td>
                                                            <td><span class="label label-warning">9/18/15 1:00 PM</span></td>
                                                            <td>9/14/15 3:15 PM</td>
                                                            <td><span class="label label-default">Draft</span></td>
                                                            <td><a href="javascript:void(0);"><i class="icon-edit"></i> Edit</a></td>
                                                        </tr>
                                                    </tbody>
                                            	</table>
                                            
                                            </div><!--portlet-body-->
                                        </div><!--end-portlet-->
                                        
                                    </div><!--col-md-8-->
                                    
                                    <div class="col-md-4">
                                    	
                                        <div class="portlet">
                                            <div class="portlet-title">
                                                <div class="caption">At A Glance</div>
                                                <div class="tools">
                                                    <a class="reload" href="javascript:;"></a>
                                                </div><!--tools-->
                                            </div><!--portlet-title-->
                                            <div class="portlet-body" style="background: #FAFAFA;">
                                            	
                                                
                                                
                                            	<div class="profile-section">
                                            		<h4>Profile Page Views</h4>
                                                    
                                                    <ul class="stat-list">
                                                    	<li>Unique: <span><?php echo pageViews($mtrLink, '04-00-00-001', $memberID, 'unique-member');?></span></li>
                                                        <li>Total: <span><?php echo pageViews($mtrLink, '04-00-00-001', $memberID, 'total-member');?></span></li>
                                                    </ul>
                                                    
                                                    <div class="clearfix"></div>
                                                </div><!--profile-section-->
                                                
                                                <div class="profile-section">
                                            		<h4>People Tracking You</h4>
                                                    
                                                    <ul class="stat-list">
                                                    	<li>Today: <span>0</span></li>
                                                        <li>Last Week: <span>0</span></li>
                                                        <li>Last Month: <span>0</span></li>
                                                        <li>Total: <span>0</span></li>
                                                    </ul>
                                                    
                                                    <div class="clearfix"></div>
                                              
                                                </div><!--profile-section-->
                                                
                                                <div class="profile-section">
                                            		<h4>Your Articles</h4>
                                                    
                                                    <ul class="stat-list">
                                                    	<li>Published: <span>0</span></li>
                                                        <li>Unpublished: <span>0</span></li>
                                                        <li>Views: <span>0</span></li>
                                                    </ul>
                                                    
                                                    <div class="clearfix"></div>
                                                    
                                                    
                                                </div><!--profile-section-->
                                                
                                                <div class="profile-section">
                                            		<h4>Your Email Campaigns</h4>
                                                    
                                                    <ul class="stat-list">
                                                    	<li>Sent: <span>0</span></li>
                                                        <li>Clicks: <span>0</span></li>
                                                    </ul>
                                                    
                                                    <div class="clearfix"></div>
                                                    
                                                    
                                                </div><!--profile-section-->
                                                
                                                
                                                    
                                            
                                            </div><!--portlet-body-->
                                        </div><!--end-portlet-->
                                        
                                        
                                            
                                    </div><!--col-md-4-->
                                    
                                </div><!--row-->
                            </div><!--tab-pane-->
                            
             			<?php } //end if tab = dashboard?>
                    </div><!--tab-content-->
                </div><!--tabbable
                <!--END TABS-->
                
                
            
            </div><!--col-md-12-->
        </div><!--row profile-->
        <!-- END PAGE CONTENT-->
        
        <?php if($_SESSION['admin'] == 1){?>
        <pre>
        <?php 
		print_r($aFundsSettings);
		print_r($member); ?>
        </pre>
        <?php } ?>
      