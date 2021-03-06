<?php
/*
Marketocracy Inc. | Beta Development
User Profile/Settings HTML

Supporting files:
	AJAX			- process/ajax/user-settings-processes.php
	JAVASCRIPT		- JAVASCRIPT includes/scripts/user-settings.js.php
	HTML			- THIS DOCUMENT includes/pages/user-profile.php
	PHP Includes	- includes/pages/includes/user-profile/ 
	
	PHP Includes Note: because this page has many different settings, 
	each setting area has its own php include to help with production 
	so that multiple developers can be working on the user settings 
	page at the same time
*/



$tab = $_REQUEST['tab'];
$subTab = $_REQUEST['subtab'];
$getFund = $_REQUEST['myfund'];


$memberID = $_SESSION['member_id'];

$memberName = get_member($mLink, $memberID, 'full name');
$memberImgURL = get_member($mLink, $memberID, 'profileImage');

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

//START | Create Fund Settings Array
$query = "
	SELECT f.*, fs.*
	FROM ".$_SESSION['fund_table']." AS f
	INNER JOIN ".$_SESSION['fund_settings_table']." AS fs ON f.fund_id=fs.fund_id
	WHERE member_id=:member_id AND active='1'
	ORDER BY weight ASC
";

try{
	$rsFunds = $mLink->prepare($query);
	$aValues = array(
		':member_id'	=> $_SESSION['member_id']
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsFunds->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}
$cnt = 0;

while($funds = $rsFunds->fetch(PDO::FETCH_ASSOC)) {
	
	$cnt++;
	//echo $cnt;
	$aGetFunds[$cnt] = $funds['fund_id'];
	
	//echo '|'.$_REQUEST['myfund'].'~'.$cnt;
	
	if($_REQUEST['myfund'] == '' && $cnt == 1){
		//echo 'hello';
		$aStartFundID 		= $funds['fund_id'];
		$aStartFundSymbol	= $funds['fund_symbol'];
		
		//echo $funds['fund_id'];
	}
	
	$aFundsSettings[$funds['fund_id']] = array(
		'cnt'				=> $cnt,
		'fund_id'			=> $funds['fund_id'],
		'fund_symbol'		=> $funds['fund_symbol'],
		'fund_type'			=> $funds['fund_type'],
		'fund_sector'		=> $funds['fund_sector'],
		'fund_name'			=> $funds['fund_name'],
		'unix_date'			=> $funds['unix_date'],
		'fund_desc'			=> $funds['description'],
		'fund_color'		=> $funds['fund_color'],
		'private'			=> $funds['private'],
		'connect_groups'	=> $funds['allowed_connect_groups'],
		'connect_members'	=> $funds['allowed_connect_members'],
		'badges'			=> $funds['badges']
	);
	
}
//END | Create Fund Settings Array

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

//START | Create My Groups Array
$query = "
	SELECT *
	FROM ".$_SESSION['connections_group_table']."
	WHERE group_owner=:member_id AND active='1'
	ORDER BY group_id
";
try{
	$rsGetGroups = $mLink->prepare($query);
	$aValues = array(
		':member_id'	=> $_SESSION['member_id']
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsGetGroups->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

while($group = $rsGetGroups->fetch(PDO::FETCH_ASSOC)) {
	
	$aGroups[$group['group_id']] = array(
		'group_name'	=> $group['group_name'],
		'group_members'	=> $group['members']
	);
}

$_SESSION['my_groups'] = $aGroups;
//END | Create My Groups Array
?>       
		
        <!-- BEGIN MANGAGE GROUP MODAL-->
        <div class="modal fade" id="edit-groups" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-wide">
             <div class="modal-content">
                
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Group Management</h4>
                </div>
            
                <div class="modal-body">
                
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                            
                            	<form action="" method="post" name="new-group" id="new-group">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th width="1%"><strong>#</strong></th>
                                            <th><strong>My Groups</strong></th>
                                            <th width="2%"><strong>Actions</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody  id="update-new-group">
                                        <?php
                                        $cnt = 0;
                                        
                                        foreach($aGroups as $groupID=>$aGroup){
                                            
                                            $groupName 	= $aGroup['group_name'];
                                            
                                            $cnt++;
                                            
                                            ?>
                                            <tr>
                                                <td><?php echo $cnt;?></td>
                                                <td><?php echo $groupName;?></td>
                                                <td><button type="button" class="btn btn-xs btn-info" onclick="loadGroup('<?php echo $groupID;?>');">Manage</button></td>
                                            </tr>
                                            <?php	
                                        }
                                        ?>
                                        <tr>
                                        	
                                        	<td><?php echo ($cnt + 1);?></td>
                                            <td>
                                            	<input type="text" class="form-control" placeholder="Enter new group name and press enter." name="group_name" id="group_name">
                                            </td>
                                            <td><button type="submit" style="margin-top:5px;" type="button" class="btn btn-xs btn-default">Create Group</button></td>
                                            
                                        </tr>
                                    </tbody>
                                </table>
                                </form>
                            </div><!--table-responsive-->
                            
                            <div class="form-body" id="load-group-settings">
                            	
                            </div><!--form-body-->
                            
                        </div><!--col-md-12-->
                    </div><!--row-->
                    
                    
                </div><!--modal-body-->
                
                <div class="modal-footer">
                	<button type="button" class="btn btn-success" id="submit-group" style="display:none;" onclick="saveGroupSettings();">Save Group</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal" onclick="closeGroup();" id="exit-btn">Cancel</button>
                </div>
                
                
                
             </div>
             <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- END MANAGE GROUP MODAL-->

		<!-- BEGIN DELETE CONFIRM MODAL-->
        <div class="modal fade" id="delete-account-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title">Delete Account Confirmation</h4>
                    </div>

                    <div class="modal-body" id="delete-fund-text">
						
                        <div id="deleteError"></div>
                        
                        <h3>Are you sure you wish to delete your account?</h3>

                        <p>This is your final warning. By deleting your account you will no longer be able access it. You will also never be able to create a new account under the same username.</p>
                        
                        <p><strong>You will automatically be logged out and your account will no longer be accessible after completing this process!</strong></p>
						
                        <form class="account-delete-form" action="" method="post">
                        	
                            <div class="form-group">
                            	<label class="control-label">Reason For Account Deletion (Required)</label>
                                <textarea name="delete-reason" class="form-control" placeholder="Please tell us why you are deleting your account."></textarea>
                            </div>
                            
                            <div class="form-group">
                            	<label class="control-label"><input type="checkbox" id="check-delete-account" name="check-delete-account" value='1'> <strong>I ACCEPT and understand that I will no longer be able to access this account.</strong></label>
                            </div>
                            
                            <input type="hidden" name="member_id" value="<?php echo $_SESSION['member_id'];?>" />
                            
                        </form>
                        
                        <p></p>
                    </div><!--modal-body-->

                    <div class="modal-footer">
                        <span id="update-account-btn"><button type="button" class="btn btn-danger" id="delete-account-btn" onclick="deleteAccount('<?php echo $_SESSION['member_id'];?>');">DELETE Account</button></span>
                        <button type="button" class="btn btn-default" data-dismiss="modal" >Cancel</button>
                    </div>



                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- END DELETE CONFIRM MODAL-->

        <!-- BEGIN DELETE CONFIRM MODAL-->
        <div class="modal fade" id="delete-fund-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title">Delete Fund Confirmation</h4>
                    </div>

                    <div class="modal-body" id="delete-fund-text">

                        <h3>Are you sure you wish to delete fund: <?php echo $aStartFundSymbol;?><?php echo $aFundsSettings[$_REQUEST['myfund']]['fund_symbol'];?>?</h3>

                        <p>If this fund uses a strategy that has not worked for you, you can delete this fund. In general though, you should stick with a fund if you simply made a mistake as having a long track record is more important than having a perfect track record. Some of our top investors made mistakes early on at Marketocracy, but then went on to have a top fund.</p>

                        <p><input type="checkbox" id="check-delete-fund" name="check-delete-fund"> <strong>I ACCEPT and understand that I will no longer be able to access this fund.</strong></p>
                    </div><!--modal-body-->

                    <div class="modal-footer">
                        <span id="update-delete-btn"><button type="button" class="btn btn-danger" id="delete-fund-btn" onclick="deleteFund('<?php echo $aStartFundID;?><?php echo $_REQUEST['myfund'];?>');">DELETE FUND</button></span>
                        <button type="button" class="btn btn-default" data-dismiss="modal" >Cancel</button>
                    </div>



                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- END DELETE CONFIRM MODAL-->
          
        <!-- BEGIN PAGE CONTENT-->
        <div class="row profile">
            <div class="col-md-12">
               <!--BEGIN TABS-->
               <div class="tabbable tabbable-custom">
                  <ul class="nav nav-tabs">
                     <li <?php if($_REQUEST['account'] == ''){?>class="active"<?php }?>><a href="#overview" data-toggle="tab">Overview</a></li>
                     <li <?php if($_REQUEST['account'] == '3'){?>class="active"<?php }?>><a href="#connections" data-toggle="tab">My Connections</a></li>                     
                     <li <?php if($_REQUEST['account'] == '1'){?>class="active"<?php }?>><a href="#account-settings" data-toggle="tab">Account Settings</a></li>
                     
                     <li <?php if($_REQUEST['account'] == '2'){?>class="active"<?php }?>><a href="#fund-settings" data-toggle="tab">Fund Settings</a></li>
                     <?php if($_SESSION['flag_promote'] == '1'){?>
                     <li><a href="?page=01-00-00-003" style="background:#5BC0DE;color:#ffffff;">Action Center</a></li>
                     <?php } ?>
                     
                     <?php /*?><li><a href="#tab_1_6" data-toggle="tab">Help</a></li><?php */?>
                  </ul>
                  <div class="tab-content">
                     
                     <!--OVERVIEW TAB-->
                     <div class="tab-pane <?php if($_REQUEST['account'] == ''){?>active<?php } ?>" id="overview">
                        <div class="row">
                                <div class="col-md-2">
                                    <ul class="list-unstyled profile-nav">
                                    <li><img src="<?php echo $memberImgURL;?>" class="img-responsive" alt="" /> 
                                    <?php if($memberID == $_SESSION['member_id']){?>
                                    	<a href="?page=10-00-00-002&account=1&tab=picture" class="profile-edit"><i class="icon-cog"></i></a>
									<?php }?>
                                    </li>
                                    <?php /*?> <li><a href="#">Projects</a></li><?php */?>
                                   
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
                                                                <?php /*?><li><i class="icon-briefcase"></i> Marketing</li>
                                                                <li><i class="icon-star"></i> Top Trader</li>
                                                                <li><i class="icon-heart"></i> Stocks</li><?php */?>
                                                            </ul>
                                                        </div><!--col-md-8-->
                                                        <div class="col-md-4" style="text-align:right;">
															<a href="?page=04-00-00-001&member=<?php echo $memberID;?>" class="btn btn-default" style="background:#EBEBEB;">View Public Profile</a>
                                                        </div><!--col-md-4-->
                                                    </div><!--row-->
                                                    
                                                    <div class="row" style="background:#EFEFEF;margin:0px;padding:10px 0px 0px 0px;">
                                                        <div class="col-md-12">
                                                            <p><a href="javascript:void(0);"><?php echo $member['username'];?>.mytrackrecord.com</a> <i class="icon-arrow-left"></i> Coming Soon</p>
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
                                                    	<div class="col-md-2" >
                                                        	<h5 style="background:#39B3D7;margin:0px 0px 0px 0px;padding:10px;text-align:center;color:#ffffff;"><strong>About Member</strong></h5>
                                                        </div><!--col-md-2-->
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
                                                    	<div class="col-md-2" >
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
                     </div><!--tab-pane-->
                     <!--overview-->
                     
                     <!--CONNECTIONS TAB-->
                     <div class="tab-pane <?php if($_REQUEST['account'] == '3'){?>active<?php } ?>" id="connections">
                        <div class="row">
                                <div class="col-md-8">
                                    
                                    <div class="portlet">
                                        <div class="portlet-title">
                                        <div class="caption"><i class="icon-group"></i>My Connections</div>
                                            <div class="tools">
                                                <a href="javascript:;" class="collapse"></a>
                                            </div>
                                        </div><!--portlet-title-->
                                        <div class="portlet-body" style="background:#999999;">
         									
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
														':member_id' 	=> $_SESSION['member_id']
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
                                                    <div class="col-md-4 connection">
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
                                                    </div><!--col-md-4-->
                                                    <?php
												}
												?>
                                            	
                                                
                                               
                                                
                                            </div><!--row-->

                                        </div><!--END PORTLET BODY-->
                                	</div><!--END PORTLET-->
                                    
                                </div><!--col-md-2-->
                                
                                <div class="col-md-4">
                                    
									<div class="portlet">
                                        <div class="portlet-title">
                                        <div class="caption"><i class="icon-user"></i>Pending Invitations</div>
                                            <div class="tools">
                                                <a href="javascript:;" class="collapse"></a>
                                            </div>
                                        </div><!--portlet-title-->
                                        <div class="portlet-body">
                                                    
                                                    <ul class="request-list" id="invites-list">
                                                    	<?php
														
														$query = "
															SELECT *
															FROM ".$_SESSION['connections_table']."
															WHERE connection=:member_id AND status='pending' 
														";
														try{
															$rsConnect = $mLink->prepare($query);
															$aValues = array(
																':member_id' 	=> $_SESSION['member_id']
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
															
															$aMember = get_member($mLink, $connect['member_id'], 'all');
															
															?>
                                                            <li>
                                                                <div class="rl-col-1">
                                                                    <img border="0" width="40" height="40" src="<?php echo $aMember['profileImageURL'];?>">
                                                                </div>
                                                                
                                                                <div class="rl-col-2">
                                                                    <strong><?php echo $aMember['fullName'];?></strong> <br>
                                                                    <span><?php echo $aMember['occupation'];?></span>
                                                                </div>
                                                                
                                                                <div class="rl-col-3">
                                                                    <a class="accordion-toggle btn btn-info btn-sm" data-toggle="collapse" data-parent="#accordion1" href="#collapse_<?php echo $cnt;?>"><i class="icon-comment"></i></a>
                                                                    <button type="button" class="btn btn-info btn-sm" onclick="acceptInvite('<?php echo $connect['uid'];?>','<?php echo $connect['member_id'];?>','<?php echo $aMember['fullName'];?>');">Accept</button>
                                                                    <button type="button" class="btn btn-sm">Ignore</button>
                                                                </div>
                                                                <div class="clear"></div><!--clear-->
                                                                
                                                                <div class="collapse request-info" id="collapse_<?php echo $cnt;?>">
                                                                    <div class="row">
                                                                        <div class="col-md-8">
                                                                            <?php echo $connect['request_message'];?>
                                                                        </div><!--col-md-8-->
                                                                        
                                                                        <div class="col-md-4" style="text-align:right;">
                                                                            <?php echo time_past($connect['timestamp']);?>
                                                                        </div><!--col-md-4-->
                                                                    </div><!--row-->
                                                                </div><!--collapse-->
                                                                    
                                                            </li>
                                                            <?php
															
														}
														
														if($cnt == 0){
															echo '<li>No Invites Pending</li>';	
														}
														
														?>
                                                        
                                                        
                                                        
                                                    </ul>
                                                        
                                        </div><!--END PORTLET BODY-->
                                	</div><!--END PORTLET-->
                                    
                            	</div><!--col-md-4-->
                                
                            </div><!--row-->
                     </div><!--tab-pane-->
                     <!--CONNECTIONS TAB-->
                     
                     
                     <?php
                     //+---------------------------------------------------------------------------------------------+
                     //|										USER ACCOUNT AREA
                     //+---------------------------------------------------------------------------------------------+
                     /*
                     Link to this page: ?page=10-00-00-002&account=1
                     To link to this settings page, you must include the &account=1 to trigger the accout tab.
                     
                     The individual tabs on this page are accessable by the "Settings Links". The states of the 
                     different tabs are controlled by javascript, however to link to a tab specifically, pass the "tag" 
                     variable in the URL. The tabs are linked by their conatiner IDs and the href of the link in the "Settings Links"
                     
                     Link to settings tab: ?page=10-00-00-002&account=1&tab=note
                     
                     Current Tabs:
                     
                     Personal Info: 	?page=10-00-00-002&account=1&tab=personal
                     Membership:		?page=10-00-00-002$account=1&tab=membership
                     Profile Picture: 	?page=10-00-00-002&account=1&tab=picture
                     Change Password:	?page=10-00-00-002&account=1&tab=password
                     Privacy Settings:	?page=10-00-00-002&account=1&tab=privacy
                     Notifications: 	?page=10-00-00-002&account=1&tab=note
                     */
                     ?>
                     <div class="tab-pane <?php if($_REQUEST['account'] == '1'){?>active<?php } ?>"  id="account-settings">
                        
                        <div class="row profile-account">
                           <div class="col-md-3">
                              
                              <!--Start Settings Links-->
                              <ul class="ver-inline-menu tabbable margin-bottom-10">
                                 
                                 <?php if($_SESSION['admin'] == '1'){?>
                                 
                                 <?php } ?>
                                 <li><h3>User Profile</h3></li>
                                 <li <?php if($tab == "" || $tab == "account"){?>class="active"<?php }?>><a data-toggle="tab" href="#tab_account"><i class="icon-cog"></i> Member Info</a></li>
                                 <li <?php if($tab == 'profile'){?>class="active"<?php }?>>
                                    <a data-toggle="tab" href="#tab_profile">
                                    <i class="icon-user"></i> 
                                    Profile Info
                                    </a> 
                                    <span class="after"></span>
                                 </li>
                                 <li <?php if($tab == "picture"){?>class="active"<?php }?>><a data-toggle="tab" href="#tab_picture"><i class="icon-picture"></i> Change Profile Picture</a></li>
                                 
                                 
                                 <?php if($_SESSION['admin'] == '1' || $membershipLive == '1'){?>
                                 <li><h3>Membership</h3></li>
                                 <li <?php if($tab == "membership"){?>class="active"<?php }?>><a data-toggle="tab" href="#tab_membership"><i class="icon-dollar"></i> Membership</a></li>
                                 <li <?php if($tab == "payments"){?>class="active"<?php }?>><a data-toggle="tab" href="#tab_payment"><i class="icon-dollar"></i> Billing &amp; Payments</a></li>
                                 <?php } //End if admin?>
                                 
                                 <li><h3>Administration</h3></li>
                                 <li <?php if($tab == "password"){?>class="active"<?php }?>><a data-toggle="tab" href="#tab_password"><i class="icon-lock"></i> Change Password</a></li>
                                 <?php if($_SESSION['admin'] == '1'){?><li <?php if($tab == "privacy"){?>class="active"<?php }?>><a data-toggle="tab" href="#tab_privacy"><i class="icon-eye-open"></i> Privacy Settings</a></li><?php } ?>
                                 <li <?php if($tab == "email"){?>class="active"<?php }?>><a data-toggle="tab" href="#tab_email"><i class="icon-envelope"></i> Email Settings</a></li>
                                 <li <?php if($tab == "note"){?>class="active"<?php }?>><a data-toggle="tab" href="#tab_note"><i class="icon-exclamation-sign"></i> Notification Settings</a></li>
                                
                                 	
                                    <li <?php if($tab == "delete"){?>class="active"<?php }?>><a  style="background-color:#e07a7a !important;border-left: solid 2px #b94a48 !important; color:#ffffff !important;" data-toggle="tab" href="#tab_delete"><i class="icon-exclamation-sign" style="background-color:#b94a48 !important;"></i> Delete Account</a></li>
                                    
                               
                                 
								 

<!--                                 <li><a href="javascript:void(0);"><i class="icon-exclamation-sign"></i> More Coming Real Soon!</a></li>-->
                              </ul>
                              <!--END Settings Links-->
                              
                           </div>
                            <div class="col-md-9">
                                <div class="tab-content">
                                
									
                                    <!--START Account Settings Tab-->
                                    <div id="tab_account" class="tab-pane <?php if($tab == "" || $tab =='account'){?>active<?php } ?>">
                                    	<?php include('includes/user-profile/account.php'); ?>
                                    </div>
                                    <!--END Account Settings Tab-->
                                    
									<?php if($_SESSION['admin'] == '1' || $membershipLive == '1'){?>
                                    <!--START Membership Info Tab-->
                                    <div id="tab_membership" class="tab-pane <?php if($tab =='membership'){?>active<?php } ?>">
                                    	<?php include('includes/user-profile/membership.php'); ?>
                                    </div>
                                    <!--END Payment Info Tab-->
                                    <!--START Payment Info Tab-->
                                    <div id="tab_payment" class="tab-pane <?php if($tab =='payments'){?>active<?php } ?>">
                                    	<?php include('includes/user-profile/payment-info.php'); ?>
                                    </div>
                                    <!--END Payment Info Tab-->
                                    <?php } ?>

                                    <?php if($_SESSION['admin'] == '1'){?>
                                    <!--START PRIVACY TABL-->
                                    <div id="tab_privacy" class="tab-pane  <?php if($tab == 'privacy'){?>active<?php } ?>">
                                    	<?php include('includes/user-profile/privacy-settings.php'); ?>
                                    </div><!--tab4-4-->
                                    <!--END PRICACY TAB-->
                                    <?php } ?>

                                    
                                    
                                    <!--START PASSWORD TAB-->
                                    <div id="tab_password" class="tab-pane  <?php if($tab == 'password'){?>active<?php } ?>">
                                   		<?php include('includes/user-profile/change-password.php'); ?>
                                    </div>
                                    <!--END PASSWORD TAB-->
                                    
                                    <!--START Profile Info Tab-->
                                    <div id="tab_profile" class="tab-pane <?php if($tab =='profile'){?>active<?php } ?>">
                                    	<?php include('includes/user-profile/personal-info.php'); ?>
                                    </div>
                                    <!--END Profile Info Tab-->
                                    
                                    <!--START Change Profile Picture Tab-->
                                    <div id="tab_picture" class="tab-pane <?php if($tab == 'picture'){?>active<?php } ?>">
                                    	<?php include('includes/user-profile/profile-picture.php'); ?>
                                    </div>
                                    <!--END CHANGE PRFILE PICTURE TAB-->
                                    
                                    <!--START NOTIFICATIONS TAB-->
                                    <div id="tab_note" class="tab-pane <?php if($tab == "note"){?>active<?php } ?>">
                                    	<?php include('includes/user-profile/notification-settings.php'); ?>
                                    </div><!--tab_5-5-->
                                    <!--END Notifications Tab-->
                                    
                                    <!--START Email TAB-->
                                    <div id="tab_email" class="tab-pane <?php if($tab == "email"){?>active<?php } ?>">
                                    	<?php include('includes/user-profile/email-settings.php'); ?>
                                    </div><!--tab_5-5-->
                                    <!--END Email Tab-->
                                    
                                    <!--START Email TAB-->
                                    <div id="tab_delete" class="tab-pane <?php if($tab == "delete"){?>active<?php } ?>">
                                    	<?php include('includes/user-profile/delete-account.php'); ?>
                                    </div><!--tab_5-5-->
                                    <!--END Email Tab-->
                                
                                </div><!--tab-content-->
                            </div><!--end col-md-9-->                                   
                        </div><!--row profile-account-->
                        
                     </div><!--account-settings-->
                     <!--END ACCOUNTS TAB-->
                     
                    <div class="tab-pane <?php if($_REQUEST['account'] == '2'){?>active<?php } ?>"  id="fund-settings">
                    
                        <div class="row">
                            <div class="col-md-2">
                            
                                <!--Start Settings Links-->
                                <ul class="ver-inline-menu tabbable margin-bottom-10">
                                    <li><h3>Funds</h3></li>
									<?php
                                    
                                    
                                    foreach($aFundsSettings as $cnt=>$aSettings){
                                        
										$cnt		= $aSettings['cnt'];										
                                        $fundID 	= $aSettings['fund_id'];
                                        $fundName	= $aSettings['fund_name'];
                                        $fundSymbol	= $aSettings['fund_symbol'];
                                        
                                        
                                        ?>
                                         <li <?php if($cnt == "1" && $getFund == '' || $getFund == $fundID){?>class="active"<?php }?>>
                                            <a href="?page=10-00-00-002&account=2&myfund=<?php echo $fundID;?>">
                                            <i class="icon-cog"></i> 
                                            <?php echo $fundSymbol;?>
                                            </a> 
                                            <span class="after"></span>                                    
                                        </li>
                                        <?php
										
                                    }
                                    ?>
                                    <li><h3>Actions</h3></li>
<?php /* ?>
                                    <li <?php if($getFund == 'new'){?>class="active"<?php }?>>
                                        <a href="?page=10-00-00-002&account=2&myfund=new">
                                        <i class="icon-plus"></i> 
                                        Create New Fund
                                        </a> 
                                        <span class="after"></span>                                    
                                    </li>
<?php */ ?>
                                    <li <?php if($getFund == 'sort'){?>class="active"<?php }?>>
                                        <a data-toggle="tab" href="#tab_fund_sort">
                                        <i class="icon-plus"></i> 
                                        Sort Funds
                                        </a> 
                                        <span class="after"></span>                                    
                                    </li>
                                </ul>
                                <!--END Settings Links-->
                            
                            </div><!--col-md-2-->
                            <div class="col-md-10">
                                <div class="tab-content">
                                    
                                    <?php
                                    //foreach($aFundsSettings as $cnt=>$aSettings){
										if($getFund == ''){
											$getFund = 	$aGetFunds[1];
										}
										
                                        $aSettings = $aFundsSettings[$getFund];
										
                                        $fundID 	= $aSettings['fund_id'];
                                        $fundName	= $aSettings['fund_name'];
										$fundType	= $aSettings['fund_type'];
										$fundSector	= $aSettings['fund_sector'];
                                        $fundSymbol	= $aSettings['fund_symbol'];
                                        $fundDesc	= $aSettings['fund_desc'];
                                        $private 	= $aSettings['private'];
                                        $fundColor	= $aSettings['fund_color'];
										
										$aConnectGroups		= explode('|',$aSettings['connect_groups']);
										$aConnectMembers	= explode('|',$aSettings['connect_members']);
                                        
                                        ?>
                                        <!--START Personal Info Tab-->
                                        <div id="tab_<?php echo $fundID;?>" class="tab-pane active">
                                            <h3 class="form-section"><?php echo $fundName;?></h3>
                                            
                                            <div class="row">
                                                <div class="col-md-12">
                                                    
                                                    <?php if($getFund != 'new'){?>
                                                    <div class="portlet">
                                                        <div class="portlet-title">
                                                        <div class="caption"><i class="icon-reorder"></i>Basic Settings</div>
                                                            <div class="tools">
                                                                <a href="javascript:;" class="collapse"></a>
                                                            </div>
                                                        </div><!--portlet-title-->
                                                        <div class="portlet-body form" id="basic-settings">
                                                        	<div id="basic-settings-error"></div>
                                                            <!-- BEGIN FORM-->
                                                            <form action="" method="post" class="form-horizontal form-row-seperated" name="fund_basic" id="fund_basic">
                                                                <div class="form-body" class="update-admin">
                                                                    
                                                                    <div class="form-group">
                                                                        <label  class="col-md-2 control-label">Fund Name <span class="required">*</span></label>
                                                                        <div class="col-md-6">
                                                                            <input type="text" class="form-control" placeholder="Example: John Doe Fund" name="fund_name" id="fund_name" value="<?php echo $fundName;?>">
                                                                            <span class="help-block">Enter the name of your fund.</span>
                                                                            <span class="help-block" id="fund_name-help"></span>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label class="col-md-2 control-label">Fund Symbol <span class="required">*</span></label>
                                                                        <div class="col-md-6">
                                                                            <input type="text" class="form-control" name="fund_symbol" id="fund_symbol" placeholder="Example: JDF" value="<?php echo $fundSymbol;?>" disabled />
                                                                            <span class="help-block">Enter a Symbol for your fund.</span>
                                                                            <span class="help-block" id="fund_symbol-help"></span>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label class="col-md-2 control-label">Fund Type <span class="required">*</span></label>
                                                                        <div class="col-md-6">
                                                                            <select class="form-control" name="fund_type" id="fund_type">
                                                                            	
																				<?php echo $optionList->getList('Fund Type', $fundType);?>
                                                                            </select>
                                                                            <span class="help-block">Select a Fund Type.</span>
                                                                            <span class="help-block" id="fund_symbol-help"></span>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <?php
																	if($fundType == 'standard'){
																		$displaySector = 'style="display:none;"';	
																	}else{
																		$displaySector = '';
																	}
																	?>
                                                                    <div class="form-group" id="fund-sector" <?php echo $displaySector;?>>
                                                                        <label class="col-md-2 control-label">Fund Sector <span class="required">*</span></label>
                                                                        <div class="col-md-6">
                                                                            <select class="form-control" name="fund_sector" id="fund_sector">
                                                                            	<option value='xx'>Select a Sector</option>
                                                                                <?php echo $optionList->getList('Stock Sectors', $fundSector);?>
                                                                            </select>
                                                                            <span class="help-block">Select the Sector you want your fund to be apart of.</span>
                                                                            <span class="help-block" id="fund_symbol-help"></span>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label  class="col-md-2 control-label">Fund Description</label>
                                                                        <div class="col-md-10">
                                                                            <textarea class="form-control" rows="5" name="fund_desc" id="fund_desc"><?php echo $fundDesc;?></textarea>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="form-group last">
                                                                        <label class="col-md-2 control-label">Fund Color <span class="required">*</span></label>
                                                                        <div class="col-md-4">
                                                                            <div class="input-group color colorpicker-default" data-color="<?php echo $fundColor;?>" data-color-format="rgba">                                      					 						
                                                                                <input type="text" class="form-control colorpicker-default" onblur="updateColor();" name="fund_color" id="fund_color" value="<?php echo $fundColor;?>">
                                                                                <span class="input-group-btn">
                                                                                    <button class="btn btn-default" type="button"><i style="background-color: <?php echo $fundColor;?>;" id="update_color_btn"></i>&nbsp;</button>
                                                                                </span>
                                                                            </div>
                                                                            <span>
                                                                                <button type="button" class="btn" onclick="changeColor('#7CB5EC');" style="background-color:#7CB5EC;width:15px;height:15px;"></button>
                                                                                <button type="button" class="btn" onclick="changeColor('#434348');" style="background-color:#434348;width:15px;height:15px;"></button>
                                                                                <button type="button" class="btn" onclick="changeColor('#90ED7D');" style="background-color:#90ED7D;width:15px;height:15px;"></button>
                                                                                <button type="button" class="btn" onclick="changeColor('#F7A35C');" style="background-color:#F7A35C;width:15px;height:15px;"></button>
                                                                                <button type="button" class="btn" onclick="changeColor('#8085E9');" style="background-color:#8085E9;width:15px;height:15px;"></button>
                                                                                <br />
                                                                                <button type="button" class="btn" onclick="changeColor('#F15C80');" style="background-color:#F15C80;width:15px;height:15px;"></button>
                                                                                <button type="button" class="btn" onclick="changeColor('#E4D354');" style="background-color:#E4D354;width:15px;height:15px;"></button>
                                                                                <button type="button" class="btn" onclick="changeColor('#8085E8');" style="background-color:#8085E8;width:15px;height:15px;"></button>
                                                                                <button type="button" class="btn" onclick="changeColor('#8D4653');" style="background-color:#8D4653;width:15px;height:15px;"></button>
                                                                                <button type="button" class="btn" onclick="changeColor('#91E8E1');" style="background-color:#91E8E1;width:15px;height:15px;"></button>
                                                                                <br />
                                                                                <button type="button" class="btn" onclick="changeColor('#39B3D7');" style="background-color:#39B3D7;width:15px;height:15px;"></button>
                                                                                <button type="button" class="btn" onclick="changeColor('#D2322D');" style="background-color:#D2322D;width:15px;height:15px;"></button>
                                                                                <button type="button" class="btn" onclick="changeColor('#ED9C28');" style="background-color:#ED9C28;width:15px;height:15px;"></button>
                                                                                <button type="button" class="btn" onclick="changeColor('#428BCA');" style="background-color:#428BCA;width:15px;height:15px;"></button>
                                                                                <button type="button" class="btn" onclick="changeColor('#47A447');" style="background-color:#47A447;width:15px;height:15px;"></button>
                                                                            </span>
                                                                            
                                                                            <span class="help-block">Select a color or click the field above to get a custom color picker.</span>
                                                                            <span class="help-block" id="fund_color-help"></span>
                                                                            
                                                                        </div>
                                                                    </div>
                                                                    <input type="hidden" name="old_fund_name" value="<?php echo $fundName;?>" />
                                                                    <input type="hidden" name="old_fund_symbol" value="<?php echo $fundSymbol;?>" />
                                                                    <input type="hidden" name="fund_id" value="<?php echo $fundID; ?>" />
                                                                </div>
                                                                <div class="form-actions fluid">
                                                                    <div class="col-md-offset-2 col-md-10">
                                                                        <button type="submit" class="btn btn-success" id="submit-basic">Save Changes</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                            <!-- END FORM--> 
                                                        </div><!--END PORTLET BODY-->
                                                    </div><!--END PORTLET-->
                                                    
                                                
                                                    
                                                    <div class="portlet" id="go-to-advanced">
                                                        <div class="portlet-title">
                                                        <div class="caption"><i class="icon-reorder"></i>Advanced Settings</div>
                                                            <div class="tools">
                                                                <a href="javascript:;" class="collapse"></a>
                                                            </div>
                                                        </div><!--portlet-title-->
                                                        <div class="portlet-body form" id="advanced-settings">
                                                        
                                                            <!-- BEGIN FORM-->
                                                            <form action="" method="post" class="form-horizontal form-row-seperated" name="fund_advanced" id="fund_advanced">
                                                                <div class="form-body">
                                                                    <div id="adminFormError"></div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label  class="col-md-2 control-label" style="margin-top:0px !important">Fund is</label>
                                                                        <div class="col-md-10">
                                                                            <div class="make-switch" data-on-label="Private" data-on="danger" data-off-label="Public">
                                                                                <input type="checkbox"  <?php if($private == '1'){ echo 'checked';}?> id="allow_all" class="toggle" name="private"/>
                                                                            </div>
                                                                            <span class="help-block">If your fund is set to private, the community will not be able to view this fund.</span>
                                                                        </div><!--col-md-10-->
                                                                    </div><!--form-group-->
                                                                    
                                                                    <div class="collapse-group">
                                                                        <div class="collapse <?php if($private == '1'){ echo 'in';}?> access_types">
                                                                    
                                                                            <div class="form-group">
                                                                                <label class="col-md-2 control-label">Allowed Groups</label>
                                                                                <div class="col-md-10">
                                                                                    <span id="reload-groups">
                                                                                    <select name="select_groups[]" class="multi-select" multiple="" id="select_groups" >
                                                                                        <option value="my_connections">My Connections</option>
                                                                                        <?php
																						foreach($aGroups as $groupID=>$aGroup){
																							
																							if(in_array($groupID, $aConnectGroups)){
																								echo '<option value="'.$groupID.'" selected>'.$aGroup['group_name'].'</option>';
																							}else{
																								echo '<option value="'.$groupID.'">'.$aGroup['group_name'].'</option>';
																							}
																							
																						}
																						?>
                                                                                    </select>
                                                                                    </span>
                                                                                    <span class="help-block">Your fund is set to private, you can select groups to be able to view this fund.</span>
                                                                                    <br />
                                                                                    <a href="#edit-groups" data-toggle="modal" class="btn btn-info btn-xs">Create / Manage Groups</a>
                                                                                </div><!--col-md-10-->
                                                                            </div><!--form-group-->
                                                                            
                                                                            <div class="form-group">
                                                                                <label class="col-md-2 control-label">Allowed Connections</label>
                                                                                <div class="col-md-10">
                                                                                    <select name="select_connects[]" class="multi-select" multiple="" id="select_connects" >
                                                                                        <?php
                                                                                        
                                                                                        $query = "
                                                                                            SELECT *
                                                                                            FROM ".$_SESSION['connections_table']."
                                                                                            WHERE member_id=:member_id AND status='active'
                                                                                        ";
                                                                                        
                                                                                        try{
                                                                                            $rsConnect = $mLink->prepare($query);
                                                                                            $aValues = array(
                                                                                                ':member_id'	=> $_SESSION['member_id']
                                                                                            );
                                                                                            $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                                                                            $rsConnect->execute($aValues);
                                                                                        }
                                                                                        catch(PDOException $error){
                                                                                            // Log any error
                                                                                            file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                                                                        }
                                                                                        $cnt = 0;
                                                                                        
                                                                                        while($connect = $rsConnect->fetch(PDO::FETCH_ASSOC)) {
                                                                                            
                                                                                            $fullName = get_member($mLink, $connect['connection'], 'full name');
                                                                                            $username = get_member($mLink, $connect['connection'], 'username');
                                                                                            
																							if(in_array($connect['connection'], $aConnectMembers)){
																								echo '<option value="'.$connect['connection'].'" selected>'.$fullName.' ('.$username.')</option>';
																							}else{
																								echo '<option value="'.$connect['connection'].'">'.$fullName.' ('.$username.')</option>';
																							}
                                                                                            
                                                                                        }																						
                                                                                        ?>
                                                                                    </select>
                                                                                    <span class="help-block">You can select inividual members from your connections list, to be allowed to view this fund.</span>
                                                                                </div><!--col-md-10-->
                                                                            </div><!--form-group-->
                                                                        
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    
                                                                </div><!--form-body-->
                                                                <div class="form-actions fluid">
                                                                    <div class="col-md-offset-2 col-md-10">
                                                                    	<input type="hidden" value="<?php echo $fundID;?>" name="fund_id"  />
                                                                        <button type="submit" class="btn btn-success">Save Changes</button>
                                                                        <?php /*?><button type="button" class="btn btn-danger" data-toggle="modal" href="#delete-fund-modal">DELETE FUND</button><?php */?>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                            <!-- END FORM--> 
                                                        </div><!--END PORTLET BODY-->
                                                    </div><!--END PORTLET-->
                                                    
                                                    <div class="portlet" >
                                                        <div class="portlet-title">
                                                        <div class="caption"><i class="icon-reorder"></i>Delete Fund</div>
                                                            <div class="tools">
                                                                <a href="javascript:;" class="collapse"></a>
                                                            </div>
                                                        </div><!--portlet-title-->
                                                        <div class="portlet-body" >
                                                        	<button type="button" class="btn btn-danger" data-toggle="modal" href="#delete-fund-modal">DELETE FUND</button>
                                                        </div><!--END PORTLET BODY-->
                                                    </div><!--END PORTLET-->
                                                    <?php }else{ ?>
                                                    
                                                    <div class="portlet">
                                                        <div class="portlet-title">
                                                        <div class="caption"><i class="icon-reorder"></i>Create New Fund</div>
                                                            <div class="tools">
                                                                <a href="javascript:;" class="collapse"></a>
                                                            </div>
                                                        </div><!--portlet-title-->
                                                        <div class="portlet-body form" id="basic-settings">
                                                        	<div id="basic-settings-error"></div>
                                                            <!-- BEGIN FORM-->
                                                            <form action="" method="post" class="form-horizontal form-row-seperated" name="fund_new" id="fund_new">
                                                                <div class="form-body" class="update-admin">
                                                                    
                                                                    <div class="form-group">
                                                                        <label  class="col-md-2 control-label">Fund Name <span class="required">*</span></label>
                                                                        <div class="col-md-6">
                                                                            <input type="text" class="form-control" placeholder="Example: John Doe Fund" name="fund_name" id="fund_name" value="<?php echo $fundName;?>">
                                                                            <span class="help-block">Enter the name of your fund.</span>
                                                                            <span class="help-block" id="fund_name-help"></span>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label class="col-md-2 control-label">Fund Symbol <span class="required">*</span></label>
                                                                        <div class="col-md-6">
                                                                            <input type="text" class="form-control" name="fund_symbol" id="fund_symbol" placeholder="Example: JDF" value="<?php echo $fundSymbol;?>" >
                                                                            <span class="help-block">Enter a Symbol for your fund.</span>
                                                                            <span class="help-block" id="fund_symbol-help"></span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label class="col-md-2 control-label">Fund Type <span class="required">*</span></label>
                                                                        <div class="col-md-6">
                                                                            <select class="form-control" name="fund_type" id="fund_type">
                                                                            	
																				<?php echo $optionList->getList('Fund Type', $fundType);?>
                                                                            </select>
                                                                            <span class="help-block">Select a Fund Type.</span>
                                                                            <span class="help-block" id="fund_symbol-help"></span>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <?php
																	if($fundType == 'standard' || $fundType == ''){
																		$displaySector = 'style="display:none;"';	
																	}else{
																		$displaySector = '';
																	}
																	?>
                                                                    <div class="form-group" id="fund-sector" <?php echo $displaySector;?>>
                                                                        <label class="col-md-2 control-label">Fund Sector <span class="required">*</span></label>
                                                                        <div class="col-md-6">
                                                                            <select class="form-control" name="fund_sector" id="fund_sector">
                                                                            	<option value='xx'>Select a Sector</option>
                                                                                <?php echo $optionList->getList('Stock Sectors', $fundSector);?>
                                                                            </select>
                                                                            <span class="help-block">Select the Sector you want your fund to be apart of.</span>
                                                                            <span class="help-block" id="fund_symbol-help"></span>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label  class="col-md-2 control-label">Fund Description</label>
                                                                        <div class="col-md-10">
                                                                            <textarea class="form-control" rows="5" name="fund_desc" id="fund_desc"><?php echo $fundDesc;?></textarea>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="form-group last">
                                                                        <label class="col-md-2 control-label">Fund Color <span class="required">*</span></label>
                                                                        <div class="col-md-4">
                                                                            <div class="input-group color colorpicker-default" data-color="<?php echo $fundColor;?>" data-color-format="rgba">                                      					 						
                                                                                <input type="text" class="form-control colorpicker-default" onblur="updateColor();" name="fund_color" id="fund_color" value="<?php echo $fundColor;?>">
                                                                                <span class="input-group-btn">
                                                                                    <button class="btn btn-default" type="button"><i style="background-color: <?php echo $fundColor;?>;" id="update_color_btn"></i>&nbsp;</button>
                                                                                </span>
                                                                            </div>
                                                                            <span>
                                                                                <button type="button" class="btn" onclick="changeColor('#7CB5EC');" style="background-color:#7CB5EC;width:15px;height:15px;"></button>
                                                                                <button type="button" class="btn" onclick="changeColor('#434348');" style="background-color:#434348;width:15px;height:15px;"></button>
                                                                                <button type="button" class="btn" onclick="changeColor('#90ED7D');" style="background-color:#90ED7D;width:15px;height:15px;"></button>
                                                                                <button type="button" class="btn" onclick="changeColor('#F7A35C');" style="background-color:#F7A35C;width:15px;height:15px;"></button>
                                                                                <button type="button" class="btn" onclick="changeColor('#8085E9');" style="background-color:#8085E9;width:15px;height:15px;"></button>
                                                                                <br />
                                                                                <button type="button" class="btn" onclick="changeColor('#F15C80');" style="background-color:#F15C80;width:15px;height:15px;"></button>
                                                                                <button type="button" class="btn" onclick="changeColor('#E4D354');" style="background-color:#E4D354;width:15px;height:15px;"></button>
                                                                                <button type="button" class="btn" onclick="changeColor('#8085E8');" style="background-color:#8085E8;width:15px;height:15px;"></button>
                                                                                <button type="button" class="btn" onclick="changeColor('#8D4653');" style="background-color:#8D4653;width:15px;height:15px;"></button>
                                                                                <button type="button" class="btn" onclick="changeColor('#91E8E1');" style="background-color:#91E8E1;width:15px;height:15px;"></button>
                                                                                <br />
                                                                                <button type="button" class="btn" onclick="changeColor('#39B3D7');" style="background-color:#39B3D7;width:15px;height:15px;"></button>
                                                                                <button type="button" class="btn" onclick="changeColor('#D2322D');" style="background-color:#D2322D;width:15px;height:15px;"></button>
                                                                                <button type="button" class="btn" onclick="changeColor('#ED9C28');" style="background-color:#ED9C28;width:15px;height:15px;"></button>
                                                                                <button type="button" class="btn" onclick="changeColor('#428BCA');" style="background-color:#428BCA;width:15px;height:15px;"></button>
                                                                                <button type="button" class="btn" onclick="changeColor('#47A447');" style="background-color:#47A447;width:15px;height:15px;"></button>
                                                                            </span>
                                                                            
                                                                            <span class="help-block">Select a color or click the field above to get a custom color picker.</span>
                                                                            <span class="help-block" id="fund_color-help"></span>
                                                                            
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-actions fluid">
                                                                    <div class="col-md-offset-2 col-md-10">
                                                                        <button type="submit" class="btn btn-success" id="submit-basic">Create New Fund</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                            <!-- END FORM--> 
                                                        </div><!--END PORTLET BODY-->
                                                    </div><!--END PORTLET-->
                                                    <?php } ?>
                                                    
                                                </div><!--col-md-12-->
                                            </div><!--row-->
                                            
                                            <?php /*if($_SESSION['admin'] == '1'){?>
                                            	<pre>
                                            	<?php //print_r($_SESSION['my_groups']);?>
                                            	<?php //print_r($aSettings);?>
                                            	</pre>
                                            <?php }*/ ?>
                                        </div>
                                        <!--END Personal Info Tab-->
                                        
                                        
                                        <div id="tab_fund_sort" class="tab-pane">
                                            <h3 class="form-section"><?php echo $fundName;?></h3>
                                            
                                            <div class="row">
                                                <div class="col-md-12">
                                                
                                                	<div class="portlet">
                                                        <div class="portlet-title">
                                                        <div class="caption"><i class="icon-reorder"></i>Sort Funds</div>
                                                            
                                                        </div><!--portlet-title-->
                                                        <div class="portlet-body">
                                                        	<h3>Change the order in which your funds are displayed throughout the site.</h3>
                                                            <p>Simply click and drag your funds into your desired order. The position will automatically be saved once you release your mouse.</p>
                                                        	<ul id="sort-funds">
                                                            	<?php
																	
																foreach($aFundsSettings as $cnt=>$aSettings){
                                        
																	$cnt		= $aSettings['cnt'];										
																	$fundID 	= $aSettings['fund_id'];
																	$fundName	= $aSettings['fund_name'];
																	$fundSymbol	= $aSettings['fund_symbol'];
																	$fundColor	= $aSettings['fund_color'];
																	
																	?>
                                                                    <li id="<?php echo $fundID;?>" style="border-left: 10px solid <?php echo $fundColor;?>;" class="handle"><strong><?php echo $fundSymbol;?></strong> | <?php echo $fundName;?></li>
																	 
																	<?php
																	
																}
																	
																?>
                                                                
                                                            </ul>
                                                            
                                                            <div id="show-sort"></div>
                                                            
                                                        </div>
                                                	</div><!--portlet-->
                                                
                                                </div>
                                            </div>
                                        </div>
                                        <?php	
                                    //}
                                    ?>
                                    
                                
                                </div><!--tab-content-->
                            </div><!--end col-md-10-->                                   
                        </div><!--row-->
                    
                    </div><!--fund-settings-->
                    <!--END Funds Settings Tab TAB-->
                     
                     
                     
                  </div><!--tab-content-->
               </div><!--tabbable custom-->
               <!--END TABS-->
               
            </div><!--col-md-12-->
        </div><!--row-->
        <!-- END PAGE CONTENT-->
      
