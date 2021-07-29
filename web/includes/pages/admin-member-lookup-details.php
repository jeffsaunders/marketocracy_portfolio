<?php
/*
Marketocracy Inc. | Beta Development
Admin Member Lookup

Supporting files:
	AJAX		- process/ajax/admin-member-lookup-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/admin-member-lookup.js.php
	HTML		- THIS DOCUMENT includes/pages/admin-member-lookup.php
*/


$lookupMemberID	= $_REQUEST['member'];
$lookupUsername	= $_REQUEST['username'];
$lookupEmail	= $_REQUEST['email'];

#includes
require_once('/var/www/html/portfolio.marketocracy.com/secure/crypto.php');

#vars
$memberID = $_REQUEST['memberID'];

#get basic user info
$memberData	= memberData($mLink, $memberID);

$aMember = $memberData['data'];	
	
#get subscription data
$aSubData 	= subscription($mLink, $memberID, true, true);

$fullName = $memberData['data']['name_title'].' '.$memberData['data']['name_first'].' '.$memberData['data']['name_middle'].' '.$memberData['data']['name_last'].' '.$memberData['data']['name_suffix'];

$emailList = '<ul><li><strong>Main:</strong> '.$memberData['data']['email'].'</li><li><strong>Alt:</strong> '.$memberData['data']['alt_email'].'</li><li><strong>EDU:</strong> '.$memberData['data']['edu_email'].'</li></ul>';

$password = decrypt($memberData['auth']['password']);

?>
<style type="text/css">

</style>     	
            
            <!-- BEGIN Modal Changes-->
            <div class="modal fade" id="promote-manager" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                 <div class="modal-content">
                 	<div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Promote To Manager</h4>
                    </div>
                    
                    <div class="modal-body" id="load-promote-info">
                            
                        <img src="/images/ajax-loading.gif" alt="Loading" />
                        
                    </div><!--modal-body-->
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                 </div>
                 <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- END Modal Changes-->
          	
            <!-- BEGIN Member Info Modal-->
            <div class="modal fade" style="overflow:hidden;" id="view-member-info" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-full">
                 <div class="modal-content" id="load-member-data">
                 	
                    
                    
                 </div>
                 <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- END Modal Changes--
            
            <!-- BEGIN Modal Changes-->
            <div class="modal fade" id="load-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                 <div class="modal-content" id="load-modal-content">
                 	<div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Loading...</h4>
                    </div>
                    
                    <div class="modal-body">
                            
                        <img src="/images/ajax-loading.gif" alt="Loading" />
                        
                    </div><!--modal-body-->
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                 </div>
                 <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- END Modal Changes-->
            
            <!-- BEGIN CHANGE EMAIL-->
            <div class="modal fade" id="change-email" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                 <div class="modal-content">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Change Email</h4>
                    </div>
                    
                    <form action="" method="post" name="email-form" class="email-form">
                        <div class="modal-body" id="load-email">
                                
                            
                        </div><!--modal-body-->
                        
                        <div class="modal-footer">
                            <input type="submit" value="Change Email" id="submit-email" class="btn btn-info" />
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    
                    </form><!--email-form-->
                 </div>
                 <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- END CHANGE EMAIL-->
            
            <!-- BEGIN CHANGE PASSWORD-->
            <div class="modal fade" id="change-password" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                 <div class="modal-content">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Change Password</h4>
                    </div>
                    
                    <form action="" method="post" name="password-form" class="password-form">
                        <div class="modal-body" id="load-password">
                                
                            
                        </div><!--modal-body-->
                        
                        <div class="modal-footer">
                            <input type="submit" value="Change Password" id="submit-password" class="btn btn-info" />
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    
                    </form><!--email-form-->
                 </div>
                 <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- END CHANGE PASSWORD-->
            
            <!-- BEGIN CHANGE FUND SYMBOL-->
            <div class="modal fade" id="change-fund-symbol" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                 <div class="modal-content">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Change Fund Symbol</h4>
                    </div>
                    
                    <form action="" method="post" name="fund-symbol-form" class="fund-symbol-form">
                        <div class="modal-body" id="load-fund-symbol">
                                
                            
                        </div><!--modal-body-->
                        
                        <div class="modal-footer">
                            <input type="submit" value="Change Fund Symbol" id="submit-fund-symbol" class="btn btn-info" />
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    
                    </form><!--fund-symbol-form-->
                 </div>
                 <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- END CHANGE FUND SYMBOL-->
            
            <!-- BEGIN CHANGE FUND NAME-->
            <div class="modal fade" id="change-fund-name" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                 <div class="modal-content">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Change Fund Name</h4>
                    </div>
                    
                    <form action="" method="post" name="fund-name-form" class="fund-name-form">
                        <div class="modal-body" id="load-fund-name">
                                
                            
                        </div><!--modal-body-->
                        
                        <div class="modal-footer">
                            <input type="submit" value="Change Fund Name" id="submit-fund-name" class="btn btn-info" />
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    
                    </form><!--fund-name-form-->
                 </div>
                 <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- END CHANGE FUND NAME-->
            
            <!-- BEGIN RUN LIVE PRICE-->
            <div class="modal fade" id="update-live-price" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                 <div class="modal-content">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                       <h4 class="modal-title">Update Live Price</h4>
                    </div>
                    
                    <form action="" method="post" name="live-price-form" class="live-price-form">
                        <div class="modal-body" id="load-live-price">
                                
                            
                        </div><!--modal-body-->
                        
                        <div class="modal-footer">
                            <input type="submit" value="Update Live Price" id="submit-live-price" class="btn btn-info" />
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    
                    </form><!--live-price-form-->
                 </div>
                 <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- END RUN LIVE PRICE-->
            
         
            <!-- BEGIN PAGE CONTENT-->
            
            <?php //include('includes/pages/includes/admin-api-que.php');?>
                
            <div class="row">
                <div class="col-md-12">
                    
                    <div class="tabbable tabbable-custom">
                        
                        <?php include('includes/nav-admin-tabs.php');?>

                        <div class="tab-content">
                        
                            
                            
                            <div class="tab-pane active" id="tab_1">
                               
                               <div class="row profile">
        
                                    <div class="col-md-2" style="position:fixed;" id="lookup-nav">
                                        
                                            <ul class="list-unstyled profile-nav">
                                                <?php /*?><?php */?>
                                                <li><img src="<?php echo $aMember['profile_image'];?>" class="img-responsive" alt="" /></li>
                                                <li><a class="goTo" href="basic-info">Basic Info</a></li>
                                                <li><a class="goTo" href="subscription">Subscription Info</a></li>
                                                <li><a class="goTo" href="funds">Fund Info</a></li>
                                                <li><a class="goTo" href="communication">Communications</a></li>
                                                <li><a class="goTo" href="support">Support Tickets</a></li>
                                                
                                            </ul>
                                    </div><!--col-md-2-->
                                    
                                    
                                    
                                    <div class="col-md-10 col-md-offset-2">
                                       <?php /*?> <div class="portlet">
                                            <div class="portlet-title">
                                                <div class="caption"><i class="icon-reorder"></i>Quick Access</div>
                                                
                                                <div class="tools">
                                                    
                                                    <a href="javascript:;" class="collapse"></a>
                                                    <!--<a href="javascript:;" class="reload"></a>-->
                                                </div><!--tools-->
                                                
                                            </div><!--portlet-title-->
                                            <div class="portlet-body">
                                                <a class="btn btn-sm btn-info goTo" href="subscription">Subscription Info</a>
                                                <a class="btn btn-sm btn-info goTo" href="funds">Fund Info</a>
                                                <a class="btn btn-sm btn-info goTo" href="communication">Communications</a>
                                            </div><!--portlet-body-->
                                        </div><!--portlet--><?php */?>
                                        
                                        <table class="table  table-bordered table-hover-alt">
                                            <thead>
                                                <tr>
                                                    <th>MID</th>
                                                    <th>Username</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>PW</th>
                                                    <th>Subscription</th>
                                                    <th>Last Login</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                              
                                                <tr>
                                                    <td><?php echo $memberID;?></td>
                                                    <td><?php echo $aMember['username'];?> <?php /*?><a href="#load-modal" data-toggle="modal" onclick="loadModal('username','<?php echo $memberID;?>');">(Change)</a><?php */?></td>
                                                    <td><?php echo $aMember['name_first'].' '.$aMember['name_last']?></td>
                                                    <td><?php echo $aMember['email'];?> <a href="#load-modal" data-toggle="modal" onclick="loadModal('email','<?php echo $memberID;?>');">(Change)</a></td>
                                                    <td><?php echo $password;?> <a href="#load-modal" data-toggle="modal" onclick="loadModal('pw','<?php echo $memberID;?>');">(Change)</a></td>
                                                    <td><?php echo $aSubData['membershipLevel'];?></td>
                                                    <td><?php echo date('m/d/Y h:i a',$memberData['auth']['last_login']);?></td>
                                                    <td><a href="process/ajax/admin-switch-user.php?member=<?php echo $memberID;?>&admin=<?php echo $_SESSION['member_id'];?>&toggle=admin-to-user&returnPage=20-00-00-011" class="btn-info btn btn-xs" onclick="switchUser('<?php echo $memberID;?>','<?php echo $_SESSION['member_id'];?>');">Login as Member</a></td>
                                                    
                                            </tr>
                                                        
                                            </tbody>
                                        </table>
                                        
                                        
                                        <div class="portlet" id="basic-info">
                                            <div class="portlet-title">
                                                <div class="caption"><i class="icon-reorder"></i>Basic Info</div>
                                                
                                                <div class="tools">
                                                    
                                                    <a href="javascript:;" class="collapse"></a>
                                                    <!--<a href="javascript:;" class="reload"></a>-->
                                                </div><!--tools-->
                                                <div class="actions">
                                                    <a href="javascript:;" class="btn btn-xs btn-info">Edit Basic Info</a>
                                                </div>
                                            </div><!--portlet-title-->
                                            <div class="portlet-body">
                                            
                                                <div class="row">
                                                
                                                   
                                                    
                                                    <div class="col-md-6">
                                                        
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th colspan="2" style="text-align:center;">Contact Info</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <th style="text-align:right;">Name:</th>
                                                                    <td><?php echo $fullName;?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">Username:</th>
                                                                    <td><?php echo $aMember['username'];?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">Last Login:</th>
                                                                    <td><?php echo date('m/d/Y h:i:s',$aMember['last_login']);?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">Joined Date:</th>
                                                                    <td><?php echo date('m/d/Y',$aMember['joined_timestamp']);?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">Email:</th>
                                                                    <td><?php echo $emailList;?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">Phone:</th>
                                                                    <td>
                                                                        <ul>
                                                                            <li><strong>Day:</strong> <?php echo $aMember['phone_day'];?></li>
                                                                            <li><strong>Evening:</strong> <?php echo $aMember['phone_evening'];?></li>
                                                                            <li><strong>Mobile:</strong> <?php echo $aMember['phone_mobile'];?></li>
                                                                        </ul>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">Address:</th>
                                                                    <td><?php if(!empty($aMember['address'])){ echo $aMember['address'].'<br />';} ?>
                                                                        <?php if(!empty($aMember['address2'])){ echo $aMember['address2'].'<br />';} ?>
                                                                        <?php echo $aMember['city'].', '.$aMember['state'].' '.$aMember['zip_code'].'<br />'; ?>
                                                                        <?php if(!empty($aMember['country'])){ echo $aMember['country'].'<br />';} ?>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th colspan="2" style="text-align:center;">Legacy Info</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                
                                                                
                                                                <tr>
                                                                    <th style="text-align:right;">FrontBase Primary Key:</th>
                                                                    <td><?php echo $aMember['fb_primarykey'];?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">FrontBase Portfolio Key:</th>
                                                                    <td><?php echo $aMember['fb_portfoliokey'];?></td>
                                                                </tr>
                                                                
                                                            </tbody>
                                                        </table>
                                                        
                                                    </div><!--col-md-5-->
                                                    
                                                    <div class="col-md-6">
                                                        
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th colspan="2" style="text-align:center;">Profile Info</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                
                                                                
                                                                <tr>
                                                                    <th style="text-align:right;">Occupation:</th>
                                                                    <td><?php echo $aMember['occupation'];?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">Industry:</th>
                                                                    <td><?php echo $aMember['industry'];?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">Interests:</th>
                                                                    <td><?php echo $aMember['interests'];?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">DOB:</th>
                                                                    <td><?php echo $aMember['DOB'];?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">Gender:</th>
                                                                    <td><?php echo $aMember['gender'];?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">Personal Website:</th>
                                                                    <td><?php echo $aMember['personal_site'];?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">LinkedIn:</th>
                                                                    <td><?php echo $aMember['linkedin'];?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">Facebook:</th>
                                                                    <td><?php echo $aMember['facebook'];?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">Twitter:</th>
                                                                    <td><?php echo $aMember['twitter'];?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">Google:</th>
                                                                    <td><?php echo $aMember['google'];?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">Profile Image Link:</th>
                                                                    <td><?php echo $_SESSION['site_root']?><?php echo $aMember['profile_image'];?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th colspan="2">About Me</th>
                                                                    
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="2"><?php echo $aMember['about_me'];?></td>
                                                                </tr>
                                                                
                                                            </tbody>
                                                        </table>
                                                        
                                                        
                                                        
                                                        
                                                        
                                                    </div><!--col-md-6-->
                                                    
                                                    <div class="col-md-12">
                                                        
                                                        <div class="portlet">
                                                            <div class="portlet-title">
                                                                <div class="caption"><i class="icon-reorder"></i>Debug</div>
                                                                
                                                                <div class="tools">
                                                                    
                                                                    <a href="javascript:;" class="expand"></a>
                                                                    <!--<a href="javascript:;" class="reload"></a>-->
                                                                </div><!--tools-->
                                                                
                                                            </div><!--portlet-title-->
                                                            <div class="portlet-body" style="display:none;">
                                                            
                                                                <pre>
                                                                <?php print_r($memberData);?>
                                                                </pre>
                                                        
                                                            </div><!--portlet-body-->
                                                        </div><!--portlet-->
                                                        
                                                        
                                                        
                                                       
                                                            
                                                    </div><!--col-->
                                                </div><!--row-->
                                                
                                            </div><!--END PORTLET BODY-->
                                        </div><!--END PORTLET-->
                                    
                                        
                                        <div class="portlet" id="subscription">
                                            <div class="portlet-title">
                                                <div class="caption"><i class="icon-reorder"></i>Subscription Info</div>
                                                <div class="tools">
                                                    <a href="javascript:;" class="collapse"></a>
                                                    <!--<a href="javascript:;" class="reload"></a>-->
                                                </div><!--tools-->
                                            </div><!--portlet-title-->
                                            <div class="portlet-body">
                                            
                                                <div class="row">
                                                   
                                                   <div class="col-md-6">
                                                        
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th colspan="2" style="text-align:center;">Sub Info</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                
                                                                <tr>
                                                                    <th style="text-align:right;">Legacy Member:</th>
                                                                    <td><?php if($aSubData['isLegacy'] == 1){echo "Yes";}else{echo "No";}?></td>
                                                                </tr>
                                                                <tr>
                                                                	<th style="text-align:right;">Product Name:</th>
                                                                    <td><?php echo $aSubData['subData']['product_name'];?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">Membership Level:</th>
                                                                    <td><?php echo $aSubData['membershipLevel'];?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">Subscription Start:</th>
                                                                    <td><?php echo date('m/d/Y', $aSubData['subStartDate']);?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">Subscription End:</th>
                                                                    <td><?php echo date('m/d/Y',$aSubData['subEndDate']);?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">Days Left in Subscription:</th>
                                                                    <td><?php echo $aSubData['daysLeftRound'];?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">Subscription Renewal:</th>
                                                                    <td><?php echo date('m/d/Y', $aSubData['annualRenewalDate']);?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">Next Bill Date:</th>
                                                                    <td><?php echo date('m/d/Y', $aSubData['nextBillDate']);?></td>
                                                                </tr>
                                                                
                                                                
                                                                
                                                                
                                                            </tbody>
                                                        </table>
                                                        
                                                        
                                                        
                                                        
                                                        
                                                    </div><!--col-md-6-->
                                                    
                                                    <div class="col-md-6">
                                                        
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th colspan="2" style="text-align:center;">Accesss and Levels</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                
                                                                <tr>
                                                                    <th style="text-align:right;">MTR Access:</th>
                                                                    <td><?php if($aSubData['mtrAccess'] == 2){echo "Yes Level: 2";}elseif($aSubData['mtrAccess'] == 1){echo "Yes Level: 1";}else{echo "No";}?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">Max Funds:</th>
                                                                    <td><?php echo $aSubData['maxFunds'];?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">Valid Max Funds:</th>
                                                                    <td><?php if($aSubData['maxFundValid'] == 1){echo "Yes";}else{echo "No";}?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">Valid Max Funds:</th>
                                                                    <td><?php if($aSubData['maxFundValid'] == 1){echo "Yes";}else{echo "No";}?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th style="text-align:right;">Valid Max Funds:</th>
                                                                    <td><?php if($aSubData['maxFundValid'] == 1){echo "Yes";}else{echo "No";}?></td>
                                                                </tr>
                                                                
                                                                <tr>
                                                                    <th style="text-align:right;">First 15 Trial:</th>
                                                                    <td><?php if($aSubData['trial30'] == 1){echo "Yes";}else{echo "No";}?></td>
                                                                </tr>
                                                                
                                                                <tr>
                                                                    <th style="text-align:right;">Second 15 Trial:</th>
                                                                    <td><?php if($aSubData['trial15'] == 1){echo "Yes";}else{echo "No";}?></td>
                                                                </tr>
                                                                
                                                                
                                                            </tbody>
                                                        </table>
                                                     
                                                    </div><!--col-md-6-->
                                                   
                                                   
                                                   <div class="col-md-12">
                                                   
                                                   		<div class="portlet">
                                                            <div class="portlet-title">
                                                                <div class="caption"><i class="icon-reorder"></i>Actions</div>
                                                                
                                                                <div class="tools">
                                                                    
                                                                    <a href="javascript:;" class="collapse"></a>
                                                                    <!--<a href="javascript:;" class="reload"></a>-->
                                                                </div><!--tools-->
                                                                
                                                            </div><!--portlet-title-->
                                                            <div class="portlet-body">
                                                            
                                                                <a href="#promote-manager" data-toggle="modal" onclick="promoteManager('<?php echo $memberID;?>');" class="btn btn-success">Promote To Manager</a>
                                                        
                                                            </div><!--portlet-body-->
                                                        </div><!--portlet-->
                                                        
                                                        <div class="portlet">
                                                            <div class="portlet-title">
                                                                <div class="caption"><i class="icon-reorder"></i>Debug</div>
                                                                
                                                                <div class="tools">
                                                                    
                                                                    <a href="javascript:;" class="expand"></a>
                                                                    <!--<a href="javascript:;" class="reload"></a>-->
                                                                </div><!--tools-->
                                                                
                                                            </div><!--portlet-title-->
                                                            <div class="portlet-body" style="display:none;">
                                                            
                                                                <pre>
                                                                <?php print_r($aSubData);?>
                                                                </pre>
                                                        
                                                            </div><!--portlet-body-->
                                                        </div><!--portlet-->
                                                        
                                                        
                                                    </div><!--col-->
                                                    
                                                    
                                                </div><!--row-->
                                                
                                            </div><!--END PORTLET BODY-->
                                        </div><!--END PORTLET-->
                                        
                                        
                                        <?php
                                        //|+---------------------------------------------------------------------------------------------------+
                                        //|									Fund Data
                                        //|+---------------------------------------------------------------------------------------------------+
                                        ?>
                                        <div class="portlet" id="funds">
                                            <div class="portlet-title">
                                                <div class="caption"><i class="icon-reorder"></i>Fund Information</div>
                                                <div class="tools">
                                                    <a href="javascript:;" class="collapse"></a>
                                                    <!--<a href="javascript:;" class="reload"></a>-->
                                                </div><!--tools-->
                                            </div><!--portlet-title-->
                                            <div class="portlet-body">
                                            
                                                <div class="row">
                                                   
                                                   <div class="col-md-12">
                                                        
                                                        
                                                        <div class="portlet" id="funds">
                                                            <div class="portlet-title">
                                                                <div class="caption"><i class="icon-reorder"></i>Active Funds</div>
                                                                <div class="tools">
                                                                    <a href="javascript:;" class="collapse"></a>
                                                                    <!--<a href="javascript:;" class="reload"></a>-->
                                                                </div><!--tools-->
                                                            </div><!--portlet-title-->
                                                            <div class="portlet-body">
                
                                                                <table class="table table-striped table-bordered table-hover">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>FID</th>
                                                                            <th>Fund Symbol</th>
                                                                            <th>Fund Name</th>
                                                                            <th>Status</th>
                                                                            <th>Actions</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    <?php
                                                                    $query = "
                                                                        SELECT *
                                                                        FROM ".$_SESSION['fund_table']."
                                                                        WHERE member_id=:member_id AND active='1'
                                                                    ";
                                                                    try{
                                                                        $rsFunds = $mLink->prepare($query);
                                                                        $aValues = array(
                                                                            ':member_id'	=> $memberID
                                                                        );
                                                                        $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                                                        $rsFunds->execute($aValues);
                                                                    }
                                                                    catch(PDOException $error){
                                                                        // Log any error
                                                                        file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                                                    }
                                                                    
                                                                    $cnt = 0;
                                                                    while($fund = $rsFunds->fetch(PDO::FETCH_ASSOC)){
                                                                        
                                                                        $fundID 	= $fund['fund_id'];
                                                                        $fundName	= $fund['fund_name'];
                                                                        $fundSymbol	= $fund['fund_symbol'];
                                                                        
                                                                        $cnt++;
                                                                        
                                                                        if($cnt == 2){
                                                                            $label = 'warning';
                                                                            $labelText = 'Processing';	
                                                                        }else{
                                                                            $label = 'success';	
                                                                            $labelText = 'Idle';
                                                                        }
                                                                        
                                                                        ?>
                                                                            <tr>
                                                                                <td><?php echo $fundID;?></td>
                                                                                <td><?php echo $fundSymbol;?> <?php /*?><a href="#change-fund-symbol" data-toggle="modal">(Change)</a><?php */?></td>
                                                                                <td><?php echo $fundName;?> <?php /*?><a href="#change-fund-name" data-toggle="modal">(Change)</a><?php */?></td>
                                                                                <td><span class="label label-<?php echo $label;?>"><?php echo $labelText;?></span></td>
                                                                                <td>
                                                                                    <ul class="btn-menu">
                                                                                        <li><a href="#load-modal" data-toggle="modal" class="btn btn-sm btn-default" onclick="loadModal('livePrice','<?php echo $memberID;?>','<?php echo $fundID;?>');">Update Live Price</a></li>
                                                                                        <li><a href="#load-modal" data-toggle="modal" class="btn btn-sm btn-default" onclick="loadModal('trades','<?php echo $memberID;?>','<?php echo $fundID;?>');">Get Trade History</a></li>
                                                                                        <li><a href="#load-modal" data-toggle="modal" class="btn btn-sm btn-default" onclick="loadModal('reprice','<?php echo $memberID;?>','<?php echo $fundID;?>');">Reprice Fund</a></li>
                                                                                        <li><a href="#load-modal" data-toggle="modal" class="btn btn-sm btn-default" onclick="loadModal('stratRebuild','<?php echo $memberID;?>','<?php echo $fundID;?>');">Reload Stratification</a></li>
                                                                                        <li class="divider"></li>
                                                                                        <li><a href="process/ajax/admin-switch-user.php?member=<?php echo $memberID;?>&admin=<?php echo $_SESSION['member_id'];?>&toggle=admin-to-user&gopage=03-01-03-001&gofund=<?php echo $fundID;?>&returnPage=20-00-00-011" class="btn btn-sm btn-info">Go To Stratification</a></li>
                                                                                        <li><a href="process/ajax/admin-switch-user.php?member=<?php echo $memberID;?>&admin=<?php echo $_SESSION['member_id'];?>&toggle=admin-to-user&gopage=03-01-00-004&gofund=<?php echo $fundID;?>&returnPage=20-00-00-011" class="btn btn-sm btn-info">Go To Ledger</a></li>
                                                                                        <li class="divider"></li>
                                                                                        <li><a href="javascript:void(0);" class="btn-danger btn btn-sm">Delete Fund</a></li>
                                                                                    </ul>
                                                                                </td>
                                                                            </tr>
                                                                        <?php
                                                        
                                                                    }
                                                                    ?>
                                                                    </tbody>
                                                                </table>
                                                        	</div><!--END PORTLET BODY-->
                                                        </div><!--END PORTLET-->
                                                        
                                                        <div class="portlet" id="funds">
                                                            <div class="portlet-title">
                                                                <div class="caption"><i class="icon-reorder"></i>Inactive Funds</div>
                                                                <div class="tools">
                                                                    <a href="javascript:;" class="expand"></a>
                                                                    <!--<a href="javascript:;" class="reload"></a>-->
                                                                </div><!--tools-->
                                                            </div><!--portlet-title-->
                                                            <div class="portlet-body" style="display:none;">
                                                                
                                                                <table class="table table-striped table-bordered table-hover">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>FID</th>
                                                                            <th>Fund Symbol</th>
                                                                            <th>Fund Name</th>
                                                                            <th>Status</th>
                                                                            <th>Actions</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    <?php
                                                                    $query = "
                                                                        SELECT *
                                                                        FROM ".$_SESSION['fund_table']."
                                                                        WHERE member_id=:member_id AND active='0'
                                                                    ";
                                                                    try{
                                                                        $rsFunds = $mLink->prepare($query);
                                                                        $aValues = array(
                                                                            ':member_id'	=> $memberID
                                                                        );
                                                                        $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                                                        $rsFunds->execute($aValues);
                                                                    }
                                                                    catch(PDOException $error){
                                                                        // Log any error
                                                                        file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                                                    }
                                                                    
                                                                    $cnt = 0;
                                                                    while($fund = $rsFunds->fetch(PDO::FETCH_ASSOC)){
                                                                        
                                                                        $fundID 	= $fund['fund_id'];
                                                                        $fundName	= $fund['fund_name'];
                                                                        $fundSymbol	= $fund['fund_symbol'];
                                                                        
                                                                        $cnt++;
                                                                        
                                                                        if($cnt == 2){
                                                                            $label = 'warning';
                                                                            $labelText = 'Processing';	
                                                                        }else{
                                                                            $label = 'success';	
                                                                            $labelText = 'Idle';
                                                                        }
                                                                        
                                                                        ?>
                                                                            <tr>
                                                                                <td><?php echo $fundID;?></td>
                                                                                <td><?php echo $fundSymbol;?> <?php /*?><a href="#change-fund-symbol" data-toggle="modal">(Change)</a><?php */?></td>
                                                                                <td><?php echo $fundName;?> <?php /*?><a href="#change-fund-name" data-toggle="modal">(Change)</a><?php */?></td>
                                                                                <td><span class="label label-<?php echo $label;?>"><?php echo $labelText;?></span></td>
                                                                                <td>
                                                                                    <ul class="btn-menu">
                                                                                        <?php /*?><li><a href="javascript:void(0);" class="btn btn-sm btn-default">Update Live Price</a></li>
                                                                                        <li><a href="javascript:void(0);" class="btn btn-sm btn-default">Reprice Fund</a></li>
                                                                                        <li><a href="javascript:void(0);" class="btn btn-sm btn-default">Reload Stratification</a></li>
                                                                                        <li class="divider"></li><?php */?>
                                                                                        <li><a href="javascript:void(0);" class="btn-warning btn btn-sm">Activate Fund</a></li>
                                                                                    </ul>
                                                                                </td>
                                                                            </tr>
                                                                        <?php
                                                        
                                                                    }
                                                                    ?>
                                                                    </tbody>
                                                                </table>
                                                        
                                                    		</div><!--END PORTLET BODY-->
                                                        </div><!--END PORTLET--    
                                                        
                                                        
                                                        
                                                    </div><!--col-md-12-->
                                                    
                                                    
                                                   
                                                   
                                                   
                                                   
                                                   <div class="col-md-12">
                                                        <div class="portlet">
                                                            <div class="portlet-title">
                                                                <div class="caption"><i class="icon-reorder"></i>Debug</div>
                                                                
                                                                <div class="tools">
                                                                    
                                                                    <a href="javascript:;" class="expand"></a>
                                                                    <!--<a href="javascript:;" class="reload"></a>-->
                                                                </div><!--tools-->
                                                                
                                                            </div><!--portlet-title-->
                                                            <div class="portlet-body" style="display:none;">
                                                            
                                                                <pre>
                                                                <?php print_r($aSubData);?>
                                                                </pre>
                                                        
                                                            </div><!--portlet-body-->
                                                        </div><!--portlet-->
                                                    </div><!--col-->
                                                    
                                                    
                                                </div><!--row-->
                                                
                                            </div><!--END PORTLET BODY-->
                                        </div><!--END PORTLET-->
                                        
                                        
                                        <?php
                                        //|+---------------------------------------------------------------------------------------------------+
                                        //|									MEMBER COMMUNICATION
                                        //|+---------------------------------------------------------------------------------------------------+
                                        ?>
                                        <div class="portlet" id="communication">
                                            <div class="portlet-title">
                                                <div class="caption"><i class="icon-reorder"></i>Communications</div>
                                                <div class="tools">
                                                    <a href="javascript:;" class="collapse"></a>
                                                    <!--<a href="javascript:;" class="reload"></a>-->
                                                </div><!--tools-->
                                            </div><!--portlet-title-->
                                            <div class="portlet-body">
                                            
                                                <div class="row">
                                                   
                                                   <div class="col-md-12">
                                                        <div class="portlet">
                                                            <div class="portlet-title">
                                                                <div class="caption"><i class="icon-reorder"></i>Email Communication</div>
                                                                
                                                                <div class="tools">
                                                                    
                                                                    <a href="javascript:;" class="collapse"></a>
                                                                    <!--<a href="javascript:;" class="reload"></a>-->
                                                                </div><!--tools-->
                                                                
                                                            </div><!--portlet-title-->
                                                            <div class="portlet-body">
                                                            	
                                                                <table class="table table-bordered table-hover-alt">
                                                                    <thead>
                                                                        <tr>
                                                                        	<th colspan="5">Campaigns</th>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Date Sent</th>
                                                                            <th>Campaign</th>
                                                                            <th>Subject</th>
                                                                            <th>Opened</th>
                                                                            <th>Actions</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
																	<?php
                                                                    $query = "
                                                                        SELECT et.*, ec.*
                                                                        FROM email_tracking et, email_campaigns ec
                                                                        WHERE et.member_id=:member_id AND ec.camp_id=et.camp_id
                                                                        ORDER BY et.sent DESC
                                                                    ";
                                                                    try{
                                                                        $rsEmails = $mLink->prepare($query);
                                                                        $aValues = array(
                                                                            ':member_id'	=> $memberID
                                                                        );
                                                                        $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                                                        $rsEmails->execute($aValues);
                                                                    }
                                                                    catch(PDOException $error){
                                                                        // Log any error
                                                                        file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                                                    }
                                                                    
                                                                    $cnt = 0;
                                                                    while($emails = $rsEmails->fetch(PDO::FETCH_ASSOC)){
                                                                        
																		$opened = $emails['opened'];
																		
																		if($opened == NULL){
																			$opened = "Did Not Open";	
																		}else{
																			$opened = date('m/d/Y h:i:s a',$opened);
																		}
																		
																		?>
                                                                        <tr>
                                                                        	<td><?php echo date('m/d/Y h:i:s a', $emails['sent']);?></td>
                                                                            <td><?php echo $emails['camp_name'];?></td>
                                                                            <td><?php echo $emails['email_subject'];?></td>
                                                                            <td><?php echo $opened;?></td>
                                                                            <td></td>
                                                                        </tr>
                                                                        <?php
                                                                        
                                                                    }
                                                                    
                                                                    ?>
                                                                    </tbody>
                                                            	</table>
                                                                
                                                                <table class="table table-bordered table-hover-alt">
                                                                    <thead>
                                                                        <tr>
                                                                        	<th colspan="5">Auto Campaigns</th>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Date Sent</th>
                                                                            <th>Campaign</th>
                                                                            <th>Subject</th>
                                                                            <th>Opened</th>
                                                                            <th>Actions</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
																	<?php
                                                                    $query = "
                                                                        SELECT et.*, ec.*
                                                                        FROM email_auto_tracking et, email_auto_campaigns ec
                                                                        WHERE et.member_id=:member_id AND ec.camp_id=et.camp_id
                                                                        ORDER BY et.sent DESC
                                                                    ";
                                                                    try{
                                                                        $rsEmails = $mLink->prepare($query);
                                                                        $aValues = array(
                                                                            ':member_id'	=> $memberID
                                                                        );
                                                                        $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                                                        $rsEmails->execute($aValues);
                                                                    }
                                                                    catch(PDOException $error){
                                                                        // Log any error
                                                                        file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                                                    }
                                                                    
                                                                    $cnt = 0;
                                                                    while($emails = $rsEmails->fetch(PDO::FETCH_ASSOC)){
                                                                        
																		$opened = $emails['opened'];
																		
																		if($opened == NULL){
																			$opened = "Did Not Open";	
																		}else{
																			$opened = date('m/d/Y h:i:s a',$opened);
																		}
																		
																		?>
                                                                        <tr>
                                                                        	<td><?php echo date('m/d/Y h:i:s a', $emails['sent']);?></td>
                                                                            <td><?php echo $emails['camp_name'];?></td>
                                                                            <td><?php echo $emails['email_subject'];?></td>
                                                                            <td><?php echo $opened;?></td>
                                                                            <td></td>
                                                                        </tr>
                                                                        <?php
                                                                        
                                                                    }
                                                                    
                                                                    ?>
                                                                    </tbody>
                                                            	</table>
                                                        
                                                            </div><!--portlet-body-->
                                                        </div><!--portlet-->
                                                    </div><!--col-->
                                                   
                                                   
                                                   <div class="col-md-12">
                                                        <div class="portlet" id="support">
                                                            <div class="portlet-title">
                                                                <div class="caption"><i class="icon-reorder"></i>Support Tickets</div>
                                                                
                                                                <div class="tools">
                                                                    
                                                                    <a href="javascript:;" class="collapse"></a>
                                                                    <!--<a href="javascript:;" class="reload"></a>-->
                                                                </div><!--tools-->
                                                                
                                                            </div><!--portlet-title-->
                                                            <div class="portlet-body">
                                                            
                                                               <div class="portlet" id="ledger-entries">
                                                                    <div class="portlet-title">
                                                                       <div class="caption"><i class="icon-tag"></i>Open Tickets</div>
                                                                       <div class="actions">
                                                                          <div class="btn-group" id="column-view">
                                                                             <a class="btn btn-info dropdown-toggle" href="#" data-toggle="dropdown">
                                                                             Columns
                                                                             <i class="icon-angle-down"></i>
                                                                             </a>
                                                                             <div id="open_tickets_column_toggler" class="dropdown-menu hold-on-click dropdown-checkboxes pull-right">
                                                                                <label><input type="checkbox" checked data-column="1" />Date</label>
                                                                                <label><input type="checkbox" checked data-column="2" />Ticket Number</label>
                                                                                <label><input type="checkbox" checked data-column="2" />Ticket Type</label>
                                                                                <label><input type="checkbox" checked data-column="3" />Subject</label>
                                                                                <label><input type="checkbox" checked data-column="4" />Replies</label>
                                                                                
                                                                             </div>
                                                                          </div>
                                                                       </div>
                                                                    </div>
                                                                    <div class="portlet-body">
                                                                            <table class="table table-striped table-bordered table-hover table-full-width" id="open_tickets">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th class="fzt-aleft" width="5%">DATE</th>
                                                                                    <th width="5%">Ticket Number</th>
                                                                                    <th width="10%">Ticket Type</th>
                                                                                    <th>Subject/Description</th>
                                                                                    <th width="5%">Status</th>
                                                                                    <th width="2%">Replies</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php 
                                                                                
                                                                                 $query = "
                                                                                    SELECT lo.label, st.ticket_status, st.ticket_id, st.ticket_type, st.ticket_subject, st.problem_type, st.description, st.timestamp
                                                                                    FROM ".$_SESSION['support_ticket_table']." AS st
                                                                                    INNER JOIN ".$_SESSION['lists_options_table']." AS lo ON lo.list_id='5' AND lo.value=st.ticket_status
                                                                                    WHERE st.member_id=:member_id AND st.ticket_status<>'0'
                                                                                    ORDER BY st.ticket_id
                                                                                ";
                                                                                
                                                                                //Fund Symbols
                                                                                try{
                                                                                    $rsTickets = $mLink->prepare($query);
                                                                                    $aValues = array(
                                                                                        ':member_id' 	=> $_SESSION['member_id']
                                                                                    );
                                                                                    $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                                                                    $rsTickets->execute($aValues);
                                                                                }
                                                                                catch(PDOException $error){
                                                                                    // Log any error
                                                                                    file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                                                                }
                                                                                
                                                                                $cnt = 0;
                                                                                while($ticket = $rsTickets->fetch(PDO::FETCH_ASSOC)) {
                                                                                    $cnt = $cnt + 1;
                                                                                    
                                                                                    if($ticket['ticket_status'] == "0"){
                                                                                        $showLabel = "danger";	
                                                                                    }elseif($ticket['ticket_status'] == "1"){
                                                                                        $showLabel = "success";	
                                                                                    }else{
                                                                                        $showLabel = "warning";	
                                                                                    }
                                                                                    
                                                                                    $query = "
                                                                                        SELECT ticket_id, COUNT(*) AS replys, viewed
                                                                                        FROM ".$_SESSION['support_feedback_table']."
                                                                                        WHERE ticket_id=:ticket_id
                                                                                    ";
                                                                                    
                                                                                    try{
                                                                                        $rsReplys = $mLink->prepare($query);
                                                                                        $aValues = array(
                                                                                            ':ticket_id' 	=> $ticket['ticket_id']
                                                                                        );
                                                                                        $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                                                                        $rsReplys->execute($aValues);
                                                                                    }
                                                                                    catch(PDOException $error){
                                                                                        // Log any error
                                                                                        file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                                                                    }
                                                                                    
                                                                                    $reply = $rsReplys->fetch(PDO::FETCH_ASSOC);
                                                                                    
                                                                                    $query = "
                                                                                        SELECT ticket_id, COUNT(*) AS replys, viewed
                                                                                        FROM ".$_SESSION['support_feedback_table']."
                                                                                        WHERE ticket_id=:ticket_id AND viewed='0'
                                                                                    ";
                                                                                    
                                                                                    //Fund Symbols
                                                                                    try{
                                                                                        $rsUnread = $mLink->prepare($query);
                                                                                        $aValues = array(
                                                                                            ':ticket_id' 	=> $ticket['ticket_id']
                                                                                        );
                                                                                        $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                                                                        $rsUnread->execute($aValues);
                                                                                    }
                                                                                    catch(PDOException $error){
                                                                                        // Log any error
                                                                                        file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                                                                    }
                                                                                    
                                                                                    $unread = $rsUnread->fetch(PDO::FETCH_ASSOC);
                                                                                    
                                                                                    if($unread['replys'] != "0"){
                                                                                        $ticketURL = "process/ajax/support-processes.php?process=7&ticket=".$ticket['ticket_id']."&user=member";	
                                                                                    }else{
                                                                                        $ticketURL = "?page=08-01-cc-003&ticket=".$ticket['ticket_id']."";	
                                                                                    }
                                                                                                                    
                                                                                    ?>
                                                                                    <tr>
                                                                                        <td><?php echo date('m/d/Y', $ticket['timestamp']); ?></td>
                                                                                        <td align="center"><a href="<?php echo $ticketURL; ?>" target="_blank" class="btn btn-<?php if($unread['replys'] != "0"){?>warning<?php }else{?>info<?php }?>"><?php echo $ticket['ticket_id']; ?></a></td>
                                                                                        <td><?php echo get_ticket_type($mLink, $ticket['ticket_type']);?></td>
                                                                                        <td><a href="<?php echo $ticketURL; ?>" target="_blank"><strong><?php echo $ticket['ticket_subject'];?></strong><br /><small><?php echo substr($ticket['description'], 0, 100); ?></small></a></td>
                                                                                        <td><span class="label label-lg label-<?php echo $showLabel; ?>"><?php echo $ticket['label']; ?></span></td>
                                                                                        <td><span class="label label-info"><?php echo $reply['replys'];?></span><?php if($unread['replys'] != "0"){?> <span class="label label-warning"><?php echo $unread['replys']; ?> Unread</span><?php } ?></td>
                                                                                    </tr>
                                                                                    <?php
                                                                                }
                                                                                ?>
                                                                                
                                                                            </tbody>
                                                                            </table>
                                                                    </div>
                                                                    </div>
                                                                    <!-- END RECENT ORDER TABLE-->
                                                                    
                                                                    <!-- BEGIN RECENT ORDERS TABLE-->
                                                                    <div class="portlet" id="ledger-entries">
                                                                    <div class="portlet-title">
                                                                       <div class="caption"><i class="icon-tag"></i>Closed Tickets</div>
                                                                       <div class="actions">
                                                                          <div class="btn-group" id="column-view">
                                                                             <a class="btn btn-info dropdown-toggle" href="#" data-toggle="dropdown">
                                                                             Columns
                                                                             <i class="icon-angle-down"></i>
                                                                             </a>
                                                                             <div id="open_tickets_column_toggler" class="dropdown-menu hold-on-click dropdown-checkboxes pull-right">
                                                                                <label><input type="checkbox" checked data-column="1" />Date</label>
                                                                                <label><input type="checkbox" checked data-column="2" />Ticket Number</label>
                                                                                <label><input type="checkbox" checked data-column="2" />Ticket Type</label>
                                                                                <label><input type="checkbox" checked data-column="3" />Subject</label>
                                                                                <label><input type="checkbox" checked data-column="4" />Replies</label>
                                                                                
                                                                             </div>
                                                                          </div>
                                                                       </div>
                                                                    </div>
                                                                    <div class="portlet-body">
                                                                            <table class="table table-striped table-bordered table-hover table-full-width" id="open_tickets">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th class="fzt-aleft" width="5%">DATE</th>
                                                                                    <th width="5%">Ticket Number</th>
                                                                                    <th width="10%">Ticket Type</th>
                                                                                    <th>Subject/Description</th>
                                                                                    <th width="5%">Status</th>
                                                                                    <th width="2%">Replies</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php 
                                                                                
                                                                                 $query = "
                                                                                    SELECT lo.label, st.ticket_status, st.ticket_id, st.ticket_type, st.ticket_subject, st.problem_type, st.description, st.timestamp
                                                                                    FROM ".$_SESSION['support_ticket_table']." AS st
                                                                                    INNER JOIN ".$_SESSION['lists_options_table']." AS lo ON lo.list_id='5' AND lo.value=st.ticket_status
                                                                                    WHERE st.member_id=:member_id AND st.ticket_status='0'
                                                                                    ORDER BY st.ticket_id
                                                                                ";
                                                                                
                                                                                //Fund Symbols
                                                                                try{
                                                                                    $rsTickets = $mLink->prepare($query);
                                                                                    $aValues = array(
                                                                                        ':member_id' 	=> $_SESSION['member_id']
                                                                                    );
                                                                                    $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                                                                    $rsTickets->execute($aValues);
                                                                                }
                                                                                catch(PDOException $error){
                                                                                    // Log any error
                                                                                    file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                                                                }
                                                                                
                                                                                $cnt = 0;
                                                                                while($ticket = $rsTickets->fetch(PDO::FETCH_ASSOC)) {
                                                                                    $cnt = $cnt + 1;
                                                                                    
                                                                                    if($ticket['ticket_status'] == "0"){
                                                                                        $showLabel = "danger";	
                                                                                    }elseif($ticket['ticket_status'] == "1"){
                                                                                        $showLabel = "success";	
                                                                                    }else{
                                                                                        $showLabel = "warning";	
                                                                                    }
                                                                                    
                                                                                    $query = "
                                                                                        SELECT ticket_id, COUNT(*) AS replys, viewed
                                                                                        FROM ".$_SESSION['support_feedback_table']."
                                                                                        WHERE ticket_id=:ticket_id
                                                                                    ";
                                                                                    
                                                                                    //Fund Symbols
                                                                                    try{
                                                                                        $rsReplys = $mLink->prepare($query);
                                                                                        $aValues = array(
                                                                                            ':ticket_id' 	=> $ticket['ticket_id']
                                                                                        );
                                                                                        $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                                                                        $rsReplys->execute($aValues);
                                                                                    }
                                                                                    catch(PDOException $error){
                                                                                        // Log any error
                                                                                        file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                                                                    }
                                                                                    
                                                                                    $reply = $rsReplys->fetch(PDO::FETCH_ASSOC);
                                                                                    
                                                                                    $query = "
                                                                                        SELECT ticket_id, COUNT(*) AS replys, viewed
                                                                                        FROM ".$_SESSION['support_feedback_table']."
                                                                                        WHERE ticket_id=:ticket_id AND viewed='0'
                                                                                    ";
                                                                                    
                                                                                    //Fund Symbols
                                                                                    try{
                                                                                        $rsUnread = $mLink->prepare($query);
                                                                                        $aValues = array(
                                                                                            ':ticket_id' 	=> $ticket['ticket_id']
                                                                                        );
                                                                                        $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                                                                        $rsUnread->execute($aValues);
                                                                                    }
                                                                                    catch(PDOException $error){
                                                                                        // Log any error
                                                                                        file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                                                                    }
                                                                                    
                                                                                    $unread = $rsUnread->fetch(PDO::FETCH_ASSOC);
                                                                                    
                                                                                    if($unread['replys'] != "0"){
                                                                                        $ticketURL = "process/ajax/support-processes.php?process=7&ticket=".$ticket['ticket_id']."&user=member&closed=1";	
                                                                                    }else{
                                                                                        $ticketURL = "?page=08-01-cc-003&ticket=".$ticket['ticket_id']."&closed=1";	
                                                                                    }
                                                                                                                    
                                                                                    ?>
                                                                                    <tr>
                                                                                        <td><?php echo date('m/d/Y', $ticket['timestamp']); ?></td>
                                                                                        <td align="center"><a href="<?php echo $ticketURL; ?>" class="btn btn-<?php if($unread['replys'] != "0"){?>warning<?php }else{?>info<?php }?>"><?php echo $ticket['ticket_id']; ?></a></td>
                                                                                        <td><?php echo get_ticket_type($mLink, $ticket['ticket_type']);?></td>
                                                                                        <td><a href="<?php echo $ticketURL; ?>"><strong><?php echo $ticket['ticket_subject'];?></strong><br /><small><?php echo substr($ticket['description'], 0, 100); ?></small></a></td>
                                                                                        <td><span class="label label-lg label-<?php echo $showLabel; ?>"><?php echo $ticket['label']; ?></span></td>
                                                                                        <td><span class="label label-info"><?php echo $reply['replys'];?></span><?php if($unread['replys'] != "0"){?> <span class="label label-warning"><?php echo $unread['replys']; ?> Unread</span><?php } ?></td>
                                                                                    </tr>
                                                                                    <?php
                                                                                }
                                                                                ?>
                                                                                
                                                                            </tbody>
                                                                            </table>
                                                                    </div>
                                                                    </div>
                                                                    <!-- END RECENT ORDER TABLE-->
                                                        
                                                            </div><!--portlet-body-->
                                                   		</div><!--portlet-->
                                                   </div><!--col-->
                                                   
                                                   
                                                   <div class="col-md-12">
                                                        <div class="portlet">
                                                            <div class="portlet-title">
                                                                <div class="caption"><i class="icon-reorder"></i>Debug</div>
                                                                
                                                                <div class="tools">
                                                                    
                                                                    <a href="javascript:;" class="expand"></a>
                                                                    <!--<a href="javascript:;" class="reload"></a>-->
                                                                </div><!--tools-->
                                                                
                                                            </div><!--portlet-title-->
                                                            <div class="portlet-body" style="display:none;">
                                                            
                                                                <pre>
                                                                <?php print_r($aSubData);?>
                                                                </pre>
                                                        
                                                            </div><!--portlet-body-->
                                                        </div><!--portlet-->
                                                    </div><!--col-->
                                                    
                                                    
                                                </div><!--row-->
                                                
                                            </div><!--END PORTLET BODY-->
                                        </div><!--END PORTLET-->
                                        
                                        
                                    </div><!--col-md-10-->
                                    
                                    
                                   
                                    
                                </div><!--row-->
                            
                            </div><!--END TAB 2-->
                            
                            
                        
                        
                        </div><!--tab-content-->
                    </div><!--tabbable tabbable-custom-->
                    
                </div><!--col-md-12-->
            </div><!--row-->
            <!-- END PAGE CONTENT-->    
   
