<?php
/*
Marketocracy Inc. | Beta Development
Admin General HTML

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/admin-general-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/admin-general.js.php
	HTML		- includes/pages/admin-general.php
*/

//Start Session
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

// Load System Wide Functions
require_once("../../includes/system-functions.php");

//Get Process from URL
$process = $_REQUEST['process'];


switch($process){
	
	case 'promote-manager':
		$aFunds = $_REQUEST['promoteFund'];
		
		//print_r($aFunds);
		
		$memberID	= $_REQUEST['member_id'];
		
		$username = get_member($mLink, $memberID, 'username');
		
		#update subscription table
		$query = "
			UPDATE members_subscriptions
			SET active='0', cancel_timestamp=UNIX_TIMESTAMP(), cancel_reason='Promoted to Manager'
			WHERE member_id=:member_id AND active=1
		";
		try{
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $memberID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		echo $error;
		#insert new subscription record
		$query = "
			INSERT INTO members_subscriptions (
				member_id,
				active,
				product_id,
				start_timestamp,
				bill_frequency
			)VALUES(
				:member_id,
				'1',
				'10',
				UNIX_TIMESTAMP(),
				'Never'
			)
		";
		try{
			$rsInsert = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $memberID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsInsert->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		echo $error;
		#update flgas table
		$query = "
			UPDATE members_flags
			SET premium='1', mytrackrecord='1', forums='1', promote='1', composite='1', trial='0', member='1'
			WHERE member_id=:member_id
		";
		try{
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $memberID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		echo $error;
		
		
		#update nessissary fund tables
		foreach($aFunds as $key=>$fundID){
			
			$startingAssets = $_REQUEST['startValue-'.$fundID];
			$fundSymbol		= get_funds($mLink, $fundID, 'fundSymbol');
			
			#update fund table
			$query = "
				UPDATE members_fund
				SET composite_fund='1', composite_start=UNIX_TIMESTAMP(), assets=:assets
				WHERE fund_id=:fund_id
			";
			try{
				$rsUpdate = $mLink->prepare($query);
				$aValues = array(
					':fund_id'		=> $fundID,
					':assets'		=> $startingAssets
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsUpdate->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			//echo $preparedQuery;
			//echo $error;
			
			#insert into casset table
			$query = "
				INSERT INTO composite_cassatt_list (
					member_id,
					username,
					fund_id,
					fund_symbol,
					added,
					active
				)VALUES(
					:member_id,
					:username,
					:fund_id,
					:fund_symbol,
					UNIX_TIMESTAMP(),
					'1'
				)
			";
			try{
				$rsInsert = $mLink->prepare($query);
				$aValues = array(
					':member_id'	=> $memberID,
					':username'		=> $username,
					':fund_id'		=> $fundID,
					':fund_symbol'	=> $fundSymbol
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsInsert->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
				echo $error;
		}#end foreach fund
		
		#notify admins
		$notificationID = '13-001';
		$notification	= $username.' as been promoted to member.';
		$link = '?page=20-00-00-011&memberID='.$memberID;
		add_notification($mLink, $notificationID, $memberID, $notification, $link);
		
		//print_r($aFunds);
	break;
	
	case 'load-promote-manager':
		
		$memberID = $_REQUEST['memberID'];
		
		//echo $memberID;
		
		
		
		?>
        <h3>Select Composite Funds</h3>
        
        <div id="promote-errors"></div>
        
        <form action="" method="post" name="promote-manager" class="promote-manager">
        <table class="table table-hover-alt">
        	<thead>
                <tr>
                    <th></th>
                    <th>Fund</th>
                    <th>Starting Assests</th>
                </tr>
        	</thead>
            <tbody>
            	
            	<?php
				$query = "
					SELECT fund_symbol, fund_id, fund_name
					FROM ".$_SESSION['fund_table']."
					WHERE member_id=:member_id AND active='1'
				";	
				try{
					$rsFund = $mLink->prepare($query);
					$aValues = array(
						':member_id'	=> $memberID
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsFund->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				while($fund = $rsFund->fetch(PDO::FETCH_ASSOC)){
				
					?>
                    <tr>
                   		<td><input type="checkbox" name="promoteFund[]" value="<?php echo $fund['fund_id'];?>" /></td>
                        <td><?php echo $fund['fund_symbol'];?> | <?php echo $fund['fund_name'];?></td>
                        <td><input type="text" name="startValue-<?php echo $fund['fund_id'];?>" /><input type="hidden" name="member_id" value="<?php echo $memberID;?>" /> </td>
                    </tr>
                    <?php
					
				}
				?>
                
            </tbody>
        </table>
        
        <input type="submit" value="Promote Manager" class="btn btn-success" />
        </form>
        
        
        <?php
		
	break;
	
	case 'member-lookup-new':
		
		$startTime = microtime();
		
		include('../../../secure/crypto.php');
		
		$aFields 	= array('member_id', 'username', 'name_first', 'name_last', 'email');
		$aParams 	= array();
		$aMembers	= array();
	
		#loop through the form fields
		foreach($aFields as $field){
			#only work with feilds that are not empty and add them to the conditions array
			if(isset($_REQUEST[$field]) && $_REQUEST[$field] != '') {
				
				if($field == 'member_id'){
					$aParams[] = "m.`$field` = ".$_REQUEST[$field]."";
				}else{
					$aParams[] = "m.`$field` LIKE '%" . $_REQUEST[$field] . "%'";
				}
			}
		}
		
		
		#check to see if any fields were used
		if(count($aParams) > 0) {
			
			$query = "
				SELECT m.*, a.password
				FROM ".$_SESSION['members_table']." m, ".$_SESSION['auth_table']." a
				WHERE " . implode (' AND ', $aParams)." AND a.member_id=m.member_id
				"; 
			
			try{
				$rsLookup 		= $mLink->prepare($query);
				$aValues 		= array();
				$preparedQuery 	= str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsLookup->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			//echo $query;
			//echo $error;
			
			while($result = $rsLookup->fetch(PDO::FETCH_ASSOC)){
				
				$aMembers[$result['member_id']] = $result;
				
				$aMembers[$result['member_id']]['password'] = decrypt($result['password']);
				
			}
		}
		
		$endTime = microtime();
		
		$timeDiff 	= $endTime - $startTime;
		$memberCnt	= count($aMembers);
		
		?>
        <h3>(<?php echo $memberCnt;?>) Search Results <small>(Response Time: <?php echo $timeDiff;?>ms )</small></h3>
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
        <?php
		
		foreach($aMembers as $memberID=>$aMember){
			
			if($aMember['last_login'] == NULL){
				$lastLogin = "Never Logged In";	
			}else{
				$lastLogin = date('m/d/Y h:i:s' ,$aMember['last_login']);	
			}
			
			$aSubData = subscription($mLink, $memberID, true, true);
			
			?>
			
                    
            <tr>
                <td><?php echo $memberID;?></td>
                <td><?php echo $aMember['username'];?> <?php /*?><a href="#load-modal" data-toggle="modal" onclick="loadModal('username','<?php echo $memberID;?>');">(Change)</a><?php */?></td>
                <td><?php echo $aMember['name_first'].' '.$aMember['name_last']?></td>
                <td><?php echo $aMember['email'];?> <a href="#load-modal" data-toggle="modal" onclick="loadModal('email','<?php echo $memberID;?>');">(Change)</a></td>
                <td><?php echo $aMember['password'];?> <a href="#load-modal" data-toggle="modal" onclick="loadModal('pw','<?php echo $memberID;?>');">(Change)</a></td>
                <td><?php echo $aSubData['membershipLevel'];?></td>
                <td><?php echo $lastLogin;?></td>
                <td><a href="process/ajax/admin-switch-user.php?member=<?php echo $memberID;?>&admin=<?php echo $_SESSION['member_id'];?>&toggle=admin-to-user" class="btn-info btn btn-xs" onclick="switchUser('<?php echo $memberID;?>','<?php echo $_SESSION['member_id'];?>');">Login as Member</a> <a href="/?page=20-00-00-011&memberID=<?php echo $memberID;?>" class="btn btn-xs btn-warning">View Info</a></td>
                
            </tr>
                        
                
			<?php
		}#end foreach member
		
		echo '</tbody></table>';
		
		
		
	break;
	
	
	case 'load-member-info':
		
		#vars
		$memberID = $_REQUEST['memberID'];
		
		#get basic user info
		$memberData	= memberData($mLink, $memberID);
		
		$aMember = $memberData['data'];	
			
		#get subscription data
		$aSubData 	= subscription($mLink, $memberID, true, true);
		
		$fullName = $memberData['data']['name_title'].' '.$memberData['data']['name_first'].' '.$memberData['data']['name_middle'].' '.$memberData['data']['name_last'].' '.$memberData['data']['name_suffix'];
		
		$emailList = '<ul><li><strong>Main:</strong> '.$memberData['data']['email'].'</li><li><strong>Alt:</strong> '.$memberData['data']['alt_email'].'</li><li><strong>EDU:</strong> '.$memberData['data']['edu_email'].'</li></ul>';
		
		?>
        
        <div class="modal-header">
           <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
           <h4 class="modal-title"><?php echo $fullName;?> <button class="btn btn-xs btn-warning"  onclick="loadMemberInfo('<?php echo $memberID;?>');">Reload</button></h4>
        </div>
        
        <div class="modal-body set-modal-height" style="height: 800px;overflow:auto;">
   
                        
            <div class="row profile">
        
                <div class="col-md-2">
                    <div class="modal-fixed" style="position:fixed;">
                        <ul class="list-unstyled profile-nav">
                            <li><img src="<?php echo $aMember['profile_image'];?>" class="img-responsive" alt="" /></li>
                            <?php /*?><li><a class="goTo" href="subscription">Subscription Info <span>1</span></a></li>
                            <li><a class="goTo" href="funds">Fund Info</a></li>
                            <li><a class="goTo" href="communication">Communications</a></li><?php */?>
                        </ul>
                    </div>
                </div><!--col-md-2-->
                
                
                
                <div class="col-md-10 scrollit">
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
                    
                    
                    <div class="portlet">
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
                                                <td colspan="2"><?php echo $aMember['about_me'];?>
                                            </tr>
                                            
                                        </tbody>
                                    </table>
                                    
                                    
                                    
                                    
                                    
                                </div><!--col-md-6-->
                                
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
                                            
                                            
                                        </tbody>
                                    </table>
                                    
                                    
                                    
                                    
                                    
                                </div><!--col-md-6-->
                                
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
                                            
                                            
                                        </tbody>
                                    </table>
                                    
                                    
                                    
                                    
                                    
                                </div><!--col-md-6-->
                                
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
                    
                  
               
            
            
        </div><!--modal-body-->
        
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
        <?php
		
	break;
	
	case 'fix-shorts':
		
		$type	= $_REQUEST['type'];
	
		$query = "
			SELECT DISTINCT(sb.fund_id)
			FROM ".$_SESSION['fund_stratification_basic_table']." AS sb
			INNER JOIN ".$_SESSION['fund_table']." AS mf ON mf.fund_id=sb.fund_id
			WHERE mf.active='1' AND sb.totalShares<'0'
		";
		try{
			$rsShortFunds = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $memberID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsShortFunds->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$aFunds = array();
		
		echo $error;
		
		while($shortFunds = $rsShortFunds->fetch(PDO::FETCH_ASSOC)){
			
			$query = "
				SELECT fund_id, fund_symbol, unix_date AS incept
				FROM ".$_SESSION['fund_table']."
				WHERE fund_id=:fund_id
			";
			try{
				$rsFunds = $mLink->prepare($query);
				$aValues = array(
					':fund_id' 	=> $shortFunds['fund_id']
					
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsFunds->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			while($funds = $rsFunds->fetch(PDO::FETCH_ASSOC)){
				
				$aFundID	= explode('-', $funds['fund_id']);
			
				$username	= get_member($mLink, $aFundID[0], 'username');
				
				$aFunds[$funds['fund_id']] = array(
					'fund_symbol'	=> $funds['fund_symbol'],
					'inception'		=> $funds['incept'],
					'username'		=> $username
				);
				
			}
			
			
		}
		
		
		switch($type){
		
			case 'get-trades':
				
				$aMethodVars 	= array();
		
				foreach($aFunds as $fundID=>$aFundInfo){
					
					
					$aMethodVars[] 	= array(
						'method'		=> 'tradesForFund',
						'source'		=> 'Member Lookup Script | admin-member-lookup-processes.php | process: fix-shorts',
						'api'			=> '2',
						'username'		=> $aFundInfo['username'],
						'fund_id'		=> $fundID,
						'fund_symbol'	=> $aFundInfo['fund_symbol'],
						'from_date'		=> date('Ymd', $aFundInfo['inception']), #gets all trades from the date past, forward
						'group'			=> date('Ymd', time()).'-trade-history'
					);
					
					
					
						
				}
				
				$mlaResults = legacy_api($mLink, $aMethodVars, true);
				
			break;
			
			case 'strat-build':
				
				foreach($aFunds as $fundID=>$aFundInfo){
					exec('/usr/bin/php /var/www/html/portfolio.marketocracy.com/scripts/strat-build.php "fundID='.$fundID.'" > /dev/null &');
					
					usleep(500000);
				}
				
			break;
			
		}
		
		
		echo '<pre>';
		echo count($aFunds);
		print_r($aFunds);
		echo '</pre>';
		
	break;
		
}



//+-------------------------------------------------------------------------------+
//|						MEMBER LOOKUP
//+-------------------------------------------------------------------------------+
if($process == 'member-lookup'){
	
	$memberID 		= trim($_REQUEST['member_id']);
	$username		= trim($_REQUEST['username']);
	$email			= trim($_REQUEST['email']);
	$firstName		= trim($_REQUEST['name_first']);
	$lastName		= trim($_REQUEST['name_last']);
	$aMembers 		= array();
	
	
	
	if($memberID != ''){
		$action = 'memberID';	
	}elseif($memberID == '' && $username != ''){
		$action = 'username';	
	}elseif($memberID == '' && $username == '' && $email != ''){
		$action = 'email';	
	}else{
		echo 'No params passed';
		exit();	
	}
	
	switch($action){
		case 'memberID':
			$query = "
				SELECT *
				FROM ".$_SESSION['members_table']." 
				WHERE member_id=:member_id
			";
			$aValues = array(
				':member_id'	=> $memberID
			);
			
		break;
		
		case 'username':
			$query = "
				SELECT *
				FROM ".$_SESSION['members_table']." 
				WHERE username LIKE :username
			";
			
			$aValues = array(
				':username'	=> $username
			);
			
		break;
		
		case 'email':
			$query = "
				SELECT *
				FROM ".$_SESSION['members_table']." 
				WHERE email LIKE :email
			";
			
			$aValues = array(
				':email'	=> $email
			);
				
		break;	
	}
	
	
	try{
		$rsMember = $mLink->prepare($query);
		
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsMember->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	
	while($member = $rsMember->fetch(PDO::FETCH_ASSOC)){
		
		
		
		$query = "
			SELECT password
			FROM ".$_SESSION['auth_table']."
			WHERE member_id=:member_id
			ORDER BY timestamp DESC
			LIMIT 1
		";
		try{
			$rsPW = $mLink->prepare($query);
			$aValues = array(
				':member_id'	=> $member['member_id']
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsPW->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$pw = $rsPW->fetch(PDO::FETCH_ASSOC);
		
		$encryptPW = $pw['password'];
		//echo $encryptPW;
		include('../../../secure/crypto.php');
		
		$decryptPW = decrypt($encryptPW);
		
		$aMembers[$member['member_id']] = array(
			'username'		=> strtolower($member['username']),
			'email'			=> $member['email'],
			'name'			=> $member['name_first'].' '.$member['name_last'],
			'pw'			=> $decryptPW,
			'lastLogin'		=> $member['last_login']
		);
		
	}
	
	foreach($aMembers as $memberID => $aMember){
		?>
		<h3>Basic Info</h3>
        <table class="table table-striped table-bordered table-hover">
			<thead>
				<tr>
					<th>MID</th>
					<th>Username</th>
					<th>Email</th>
					<th>PW</th>
					<th>Last Login</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				
					<tr>
						<td><?php echo $memberID;?></td>
						<td><?php echo $aMember['username'];?> <?php /*?><a href="#load-modal" data-toggle="modal" onclick="loadModal('username','<?php echo $memberID;?>');">(Change)</a><?php */?></td>
						<td><?php echo $aMember['email'];?> <a href="#load-modal" data-toggle="modal" onclick="loadModal('email','<?php echo $memberID;?>');">(Change)</a></td>
						<td><?php echo $aMember['pw'];?> <a href="#load-modal" data-toggle="modal" onclick="loadModal('pw','<?php echo $memberID;?>');">(Change)</a></td>
						<td><?php echo date('m/d/Y h:i:s' ,$aMember['lastLogin']);?></td>
                        <td><a href="process/ajax/admin-switch-user.php?member=<?php echo $memberID;?>&admin=<?php echo $_SESSION['member_id'];?>&toggle=admin-to-user" class="btn-info btn btn-xs" onclick="switchUser('<?php echo $memberID;?>','<?php echo $_SESSION['member_id'];?>');">Login as Member</a></td>
					</tr>
					
			</tbody>
		</table>
        
        <h3>Active Funds</h3>
		
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
                                <li><a href="process/ajax/admin-switch-user.php?member=<?php echo $memberID;?>&admin=<?php echo $_SESSION['member_id'];?>&toggle=admin-to-user&gopage=03-01-03-001&gofund=<?php echo $fundID;?>" class="btn btn-sm btn-info">Go To Stratification</a></li>
                                <li><a href="process/ajax/admin-switch-user.php?member=<?php echo $memberID;?>&admin=<?php echo $_SESSION['member_id'];?>&toggle=admin-to-user&gopage=03-01-00-004&gofund=<?php echo $fundID;?>" class="btn btn-sm btn-info">Go To Ledger</a></li>
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
        
        <h3>Inactive Funds</h3>
		
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
        <?php
		
		
	}
}


if($process == 'load-modal'){
	
	$modalType	= $_REQUEST['modalType'];
	$memberID	= $_REQUEST['memberID'];
	$fundID		= $_REQUEST['fundID'];
	
	switch($modalType){
		
		
		case 'livePrice':
			
			$aFundInfo 	= get_funds($mLink, $fundID, 'fundBasic');
			$fundSymbol	= $aFundInfo['fundSymbol'];
			$username	= strtolower(get_member($mLink, $memberID, 'username'));
			
			//$query = 'livePrice|'.$username.'|'.$fundID.'|'.$fundSymbol;
			$aMethodVars[] = array(
				'method'		=> 'livePrice',
				'source'		=> 'Admin LivePrice Script | admin-member-lookup-processes.php | process: load-modal',
				'api'			=> '2',
				'port'			=> '',
				'username'		=> $username,
				'fund_id'		=> $fundID,
				'fund_symbol'	=> $fundSymbol,
				'from_date'		=> '',
				'start_date'	=> '',
				'end_date'		=> '',
				'group'			=> ''
			);
			$mlaResults = legacy_api($mLink, $aMethodVars, true);
			
			//include ('../../includes/data-query-legacy.php');
			usleep(500000);
			
			?>
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
               <h4 class="modal-title">Update Live Price</h4>
            </div>
            
            <div class="modal-body">
                    
                <h3>Live Price query submitted</h3>
                <p>Please allow a short time for the process to complete.</p>
                
            </div><!--modal-body-->
            
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
            
            <?php
			
		break;
		
		case 'stratRebuild': 
		
			exec('/usr/bin/php /var/www/html/portfolio.marketocracy.com/scripts/strat-build.php "fundID='.$fundID.'" > /dev/null &');
		
			?>
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
               <h4 class="modal-title">Stratification Reload</h4>
            </div>
            
            <div class="modal-body">
                    
                <h3>Stratification Reload process has been started</h3>
                <p>Please allow a short time for the process to complete.</p>
                
            </div><!--modal-body-->
            
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
            
            <?php
		
		break;	
		
		case 'reprice':
			
			?>
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
               <h4 class="modal-title">Reprice Fund</h4>
            </div>
            
            <form action="" method="post" class="reprice-fund">
                <div class="modal-body">
                        
                    <div class="form-group">
                        <label class="control-label">Select Price From Date</label><br />
                        
                        
                        <select name="year">
                            <?php
                            echo date_list($mLink, 'year', NULL, date('Y'), false);
                            ?>
                        </select>/
                        <select  name="month">
                            <?php
                            echo date_list($mLink, 'month', NULL, date('m'));
                            ?>
                        </select>/
                        <select name="day">
                            <?php
                            echo date_list($mLink, 'day', NULL, date('d'));
                            ?>
                        </select><br />
                        <small>YYYY/MM/DD</small><br />
                        
                        <label class="control-label"><input type="checkbox" value="1" name="from-incpet" /> Reprice from Inception</label><br />
                        <small>If checked, it will ignore the date boxes above.</small>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Select API</label><br />
                        <select name="api-server">
                            <option value="api1">API1</option>
                            <option value="api2" selected>API2</option>
                            <option value="api3">API3</option>
                         </select>
                    </div>
                    <div class="form-group">
                        <label><input type="checkbox" value="1" name="process-queries" /> Show queries</label>
                    </div>
                    <input type="hidden" name="fund_id" value="<?php echo $fundID;?>" />
                    
                    <div id="display-reprice-info"></div>
                    
                </div><!--modal-body-->
                
                <div class="modal-footer">
                    <input type="submit" value="Reprice Fund" id="submit-reprice" class="btn btn-info" />
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            
            </form><!--fund reprice-->
            <?php			
			
		break;
		
		case 'email':
			
			?>
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
               <h4 class="modal-title">Change Email</h4>
            </div>
            
            <form action="" method="post" class="email-form">
                <div class="modal-body">
                    
                    <div id="email-warnings"></div>
                        
                    <div class="form-group">
                        <label class="control-label">Email*</label>
                        <input type="text" class="form-control" name="email" />
                    </div>
                    
                    <input type="hidden" name="member_id" value="<?php echo $memberID;?>" />
                </div><!--modal-body-->
                
                <div class="modal-footer">
                    <input type="submit" value="Change Email" id="submit-email" class="btn btn-info" />
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                </div>
            
            </form><!--update email-->
            <?php
			
		break;
		
		
		
		case 'fundSymbol':
		
		break;
		
		case 'fundName':
		
		break;
		
		case 'username':
			
			?>
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
               <h4 class="modal-title">Change Username</h4>
            </div>
            
            <form action="" method="post" name="username-form" class="username-form">
                <div class="modal-body">
                    
                    <div id="username-warnings"></div>
                    
                    <div class="form-group">
                        <label class="control-label">Username*</label>
                        <input type="text" class="form-control" name="username" />
                    </div>
                    <input type="hidden" name="member_id" value="<?php echo $memberID;?>" />
                </div><!--modal-body-->
                
                <div class="modal-footer">
                    <input type="submit" value="Change Username" id="submit-username" class="btn btn-info" />
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            
            </form><!--update-username-->
            <?php
			
		break;
		
		case 'pw':
			$username	= get_member($mLink, $memberID, 'username');
			?>
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
               <h4 class="modal-title">Change Password</h4>
            </div>
            
            <form action="" method="post" name="password-form" class="password-form">
                <div class="modal-body">
                    
                    <div id="password-warnings"></div>
                    
                    <div class="form-group">
                        <label class="control-label">New Password*</label>
                        <input type="text" class="form-control" name="password" id="new-pw"/>
                        <div id="password-score"></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Confirm Password*</label>
                        <input type="text" class="form-control" name="password2" />
                        
                    </div>
                    <input type="hidden" name="username" id="username" value="<?php echo $username;?>" />
                    <input type="hidden" name="member_id" value="<?php echo $memberID;?>" />
                </div><!--modal-body-->
                
                <div class="modal-footer">
                    <input type="submit" value="Change Password" id="submit-password" class="btn btn-info" />
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            
            </form><!--update-username-->
            <?php
		break;
		
		case 'trades':
			
			?>
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
               <h4 class="modal-title">Get Trade History</h4>
            </div>
            
            <form action="" method="post" class="trade-history-form">
                <div class="modal-body">
                        
                    <div class="form-group">
                        <label class="control-label">Select Date To get Trade History From</label><br />
                        
                       
                        
                        <select name="year">
                            <?php
                            echo date_list($mLink, 'year', NULL, date('Y'), false);
                            ?>
                        </select>/
                        <select  name="month">
                            <?php
                            echo date_list($mLink, 'month', NULL, date('m'));
                            ?>
                        </select>/
                        <select name="day">
                            <?php
                            echo date_list($mLink, 'month', NULL, date('d'));
                            ?>
                        </select><br />
                        <small>YYYY/MM/DD</small><br />
                        
                        <label class="control-label"><input type="checkbox" value="1" name="from-incpet" /> Get Trades from Inception</label><br />
                        <small>If checked, it will ignore the date boxes above.</small>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Select API</label><br />
                        <select name="api-type" class="form-control">
                            <option value="1">API1</option>
                            <option value="2" selected>API2</option>
                            <option value="3">API3</option>
                         </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Select Processes</label>
                        <select name="query" class="form-control">
                            <option value="all">All</option>
                            <option value="allPositionInfo">allPositionInfo</option>
                            <option value="tradesForFund">tradesForFund</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><input type="checkbox" value="1" name="process-queries" /> Show queries only</label>
                    </div>
                    <input type="hidden" name="fund_id" value="<?php echo $fundID;?>" />
                    <input type="hidden" name="member" value="<?php echo $memberID;?>" />
                    <input type="hidden" name="api" value="process" />
                     <div id="trade-history-warnings"></div>
                    
                </div><!--modal-body-->
                
                <div class="modal-footer">
                    <input type="submit" value="Get Trade Hisotry" id="submit-trade-history" class="btn btn-info" />
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            
            </form><!--fund reprice-->
            <?php			
			
		break;
	}
	?>
    
    <?php
		
}

if($process == 'trade-history'){
	
	$showQueries	= $_REQUEST['process-queries'];
	$member 		= $_REQUEST['member'];
	$fundID			= $_REQUEST['fund_id'];
		
	$runQuery		= $_REQUEST['query'];
	
	$api 			= $_REQUEST['api'];
	$apiType		= $_REQUEST['api-type'];
	
	//$tradeStart	= $_REQUEST['tradeStartDate'];
	$day			= $_REQUEST['day'];
	$month			= $_REQUEST['month'];
	$year			= $_REQUEST['year'];
	$fromDate		= mktime(6,0,0,$month,$day,$year);
	$tradeStart		= $year.$month.$day;
	$fromIncept		= $_REQUEST['from-incpet'];
	
	if($tradeStart == ''){
		$tradeDate = '';	
	}else{
		$tradeDate = '|'.$tradeStart;	
	}
	
	switch($apiType){
		case '1': $startPort = '52001';$endPort = '52050';break;
		case '2': $startPort = '52101';$endPort = '52150';break;	
                case '3': $startPort = '52501';$endPort = '52550';break;
	}
	
	//Get the passed member ID
	$query = '
		SELECT *
		FROM '.$_SESSION['members_table'].'
		WHERE active=:active AND member_id=:member_id
	';
	
	try{
		$rsMembers = $mLink->prepare($query);
		$aValues = array(
			':active' 		=> '1',
			':member_id'	=> $member
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsMembers->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aMembers = array();
	$cnt = 0;
	
	//loop through results and assign values to array
	while($member = $rsMembers->fetch(PDO::FETCH_ASSOC)){
		$memberID 	= $member['member_id'];
		$username 	= strtolower($member['username']);
		$firstName	= $member['name_first'];
		$lastName	= $member['name_last'];
		
		if($fundID == ''){
			$query = "
				SELECT fund_id, fund_symbol, unix_date AS incept
				FROM ".$_SESSION['fund_table']."
				WHERE member_id=:member_id AND active=:active
			";
			try{
				$rsFunds = $mLink->prepare($query);
				$aValues = array(
					':member_id' 	=> $memberID,
					':active'		=> '1'
					
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsFunds->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
		}else{
			$query = "
				SELECT fund_id, fund_symbol, unix_date AS incept
				FROM ".$_SESSION['fund_table']."
				WHERE fund_id=:fund_id
			";
			try{
				$rsFunds = $mLink->prepare($query);
				$aValues = array(
					':fund_id' 	=> $fundID
					
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsFunds->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
		}
		
		$aFunds = array();
		
		while($funds = $rsFunds->fetch(PDO::FETCH_ASSOC)){
			$aFunds[$funds['fund_id']] = array(
				'fund_symbol'=>$funds['fund_symbol'],
				'inception'=>$funds['incept']
			);
			
		}
		
		$aMembers[$memberID] = array(
			'member_id'	=> $memberID,
			'username'	=> $username,
			'firstName'	=> $firstName,
			'lastName'	=> $lastName,
			'funds'		=> $aFunds
		);
		
		$cnt++;
		
	}
	
	$aQueries = array();
	
	//get pricing and add to array
	foreach($aMembers as $memberID=>$aMember){
		
		$username = $aMember['username'];
		
		foreach($aMember['funds'] as $fundID=>$aFundInfo){		
			
			$fundSymbol 	= $aFundInfo['fund_symbol'];
			$inceptionDate	= $aFundInfo['inception'];
			
			if($fromIncept == '1'){
				$tradeStart = date('Ymd', $inceptionDate);
				$activeOnly	= '0';	
			}else{
				$activeOnly = '1';	
			}
			
			if($runQuery == 'all' || $runQuery == 'tradesForFund'){
				//generate trades query
				$port = rand($startPort, $endPort);
				//$query = 'tradesForFund|'.$username.'|'.$fundID.'|'.$fundSymbol.$tradeDate;
				
				//NEW LEGACY API FUNCTION
				$aMethodVars[] = array(
					'method'		=> 'tradesForFund',
					'source'		=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: trade-history',
					'api'			=> $apiType,
					'port'			=> '',
					'username'		=> $username,
					'fund_id'		=> $fundID,
					'fund_symbol'	=> $fundSymbol,
					'from_date'		=> $tradeStart,
					'start_date'	=> '',
					'end_date'		=> '',
					'group'			=> date('Ymd', time()).'-trade-history'
				);
				
				/*if($api == 'process'){
					include('../../includes/data-query-legacy.php');
				}*/
				
				//$aQueries[] = $query;
			}
			
			
			if($runQuery == 'all' || $runQuery == 'allPositionInfo'){
				//generate allPositionInfo query
				//$port = rand($startPort, $endPort);
				
				$aMethodVars[] = array(
					'method'		=> 'allPositionInfo',
					'source'		=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: trade-history',
					'api'			=> $apiType,
					'port'			=> '',
					'username'		=> $username,
					'fund_id'		=> $fundID,
					'fund_symbol'	=> $fundSymbol,
					'active_only'	=> $activeOnly,
					'from_date'		=> '',
					'start_date'	=> '',
					'end_date'		=> '',
					'group'			=> date('Ymd', time()).'-trade-history'
				);
				//$query	= 'allPositionInfo|'.$username.'|'.$fundID.'|'.$fundSymbol.'';
				
				/*if($api == 'process'){
					include('../../includes/data-query-legacy.php');
				}*/
				
				//$aQueries[] = $query;	
			}
			
		}
		
	}
	
	if($showQueries == '1'){
		?>
		<h4>Debug</h4>
        <?php
		echo '<pre>';
		print_r($aMethodVars);
		echo '</pre>';
	}else{
		//NEW LEGACY API FUNCTION
		$mlaResults = legacy_api($mLink, $aMethodVars, true);
		
		
		?>
		<h4>Trade history queries queued. Please allow some time for the queries to process.</h4>
		<?php
		echo count($aMethodVars).' Queries Submitted';
		?>
        <h4>Debug</h4>
        <?php
		echo $mlaResults.'<br /><br />';
		echo '<pre>';
		print_r($aMethodVars);
		echo '</pre>';
	}
	
}

if($process == 'change-password'){
	
	$newPW		= $_REQUEST['password'];
	$newPW2		= $_REQUEST['password2'];
	$pwScore	= $_REQUEST['password_score'];
	$memberID	= $_REQUEST['member_id'];
		
	$aErrors = array();

	/*if(empty($currentPW) || $currentPW == ''){
		$aErrors[] = 'Please provide your current password.';	
	}*/
	
	if(empty($newPW) || $newPW == ''){
		$aErrors[] = 'Please provide your new password.';	
	}
	
	if(empty($newPW2) || $newPW2 == ''){
		$aErrors[] = 'Please re-type your new password.';	
	}
	
	if($newPW != $newPW2){
		$aErrors[] = 'Passwords do not match, please re-type your new password.';	
	}
	
	/*if($pwScore < 35){
		$aErrors[] = 'Password is too weak. Please make a stronger password.';
	}*/
	
	if(empty($aErrors)){
		
		$query = "
			SELECT *
			FROM ".$_SESSION['auth_table']."
			WHERE member_id=:memberID
			ORDER BY timestamp DESC
			LIMIT 1
		";
		
		try{
			$rsGetAuth = $mLink->prepare($query);
			$aValues = array(
				':memberID' 	=> $memberID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsGetAuth->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		$auth = $rsGetAuth->fetch(PDO::FETCH_ASSOC);
		
		//include encryptions
		require_once('../../../secure/crypto.php');
		
		$encryptCurrentPW = encrypt($currentPW);
		
		/*if($encryptCurrentPW != $auth['password']){
			
			$aErrors[] = 'Warning: The current password you entered is NOT correct.';
				
		}*/
		
		$encryptNewPW = encrypt($newPW);
		
		if($auth['password'] == $encryptNewPW){
			
			$aErrors[] = 'Warning: New password can NOT be the same as previous password.';
				
		}
		

		if(empty($aErrors)){
		
			//ADD new auth record
			$query = "
				INSERT INTO ".$_SESSION['auth_table']." (
					member_id,
					timestamp,
					username,
					password,
					password_score,
					email,
					email_validated_timestamp
				)VALUES(
					:member_id,
					UNIX_TIMESTAMP(),
					:username,
					:password,
					:password_score,
					:email,
					:email_val_time
				)
			";
			
			try{
				$rsAddAuth = $mLink->prepare($query);
				$aValues = array(
					':password_score'	=> $pwScore,
					':email_val_time'	=> $auth['email_validated_timestamp'],
					':member_id' 		=> $memberID,
					':username'			=> $auth['username'],
					':password'			=> $encryptNewPW,
					':email'			=> $auth['email']
					
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsAddAuth->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			//echo $preparedQuery;
			
			$aMember = get_member($mLink, $_SESSION['member_id'], 'all');
			
			
			$memberUsername = decrypt($auth['username']);
			
			//START add event record
			$event = 'Account Update';
			$detail = $aMember['username'].' has changed a member\'s password: <a href="https://portfolio.marketocracy.com/?page=20-00-00-003&member='.$memberID.'>'.$memberUsername.'</a>.';
			addEventRecord($mLink, $_SESSION['member_id'], $event, $detail);
			//END add event record
			
			sleep(1);
			echo 'success';
		}
		
	}
	
	if(!empty($aErrors)){
		
		echo '<div class="alert alert-danger"><ul style="color:#FF0000;">';
		
		foreach($aErrors as $key=>$errors){
			echo '<li>'.$errors.'</li>';	
		}
		
		echo '</ul></div>';
	}
		
}

if($process == 'change-username'){
	
	include('../../../secure/crypto.php');
	
	$memberID			= $_REQUEST['member_id'];
	$newUsername 		= $_REQUEST['username'];
	$newEncryptUsername	= encrypt($newUsername);
	$aErrors			= array();
		
	//Get old username
	$query = "
		SELECT email, username, password
		FROM ".$_SESSION['auth_table']."
		WHERE member_id=:member_id
		ORDER BY timestamp DESC
		LIMIT 1
	";
	
	try{
		$rsGetAuth = $mLink->prepare($query);
		$aValues = array(
			':member_id' 	=> $_SESSION['member_id']
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetAuth->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$auth = $rsGetAuth->fetch(PDO::FETCH_ASSOC);
	
	$pwEncrypt			= $auth['password'];
	$oldEncryptUsername	= $auth['username'];
	$emailEncrypt		= $auth['email'];
	$oldUsername		= decrypt($oldEncryptUsername);
	
	//start error checking
	if(trim($oldUsername) == trim($newUsername)){
		$aErrors[] = 'You have entered the same username, please enter another or close this modal.';		
	}
	
	$query = "
		SELECT username
		FROM ".$_SESSION['auth_table']."
		WHERE username=:username AND member_id<>:member_id
	";
	
	try{
		$rsGetAuth = $mLink->prepare($query);
		$aValues = array(
			':username' 	=> $newEncryptUsername,
			':member_id'	=> $memberID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetAuth->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$usernameCnt = $rsGetAuth->rowCount();
	
	if($usernameCnt > 0){
		$aErrors[] = "Username already exists in the database. Please use another one.";	
	}
	
	if(empty($aErrors)){
		
		//Create new auth record
		$query = "
			INSERT INTO ".$_SESSION['auth_table']." (
				member_id,
				timestamp,
				username,
				password,
				password_score,
				password_score_ack_timestamp,
				email,
				reset_request_timestamp,
				reset_request_ip,
				email_validated_timestamp,
				imported
			) VALUE (
				:member_id,
				UNIX_TIMESTAMP(),
				:username,
				:password,
				'0',
				'0',
				:email,
				'0',
				'',
				UNIX_TIMESTAMP(),
				'0'
			)		
		";
		
		try{
			$rsInsertAuth = $mLink->prepare($query);
			$aValues = array(
				':member_id' 	=> $memberID,
				':username'		=> $newEncryptUsername,
				':password'		=> $pwEncrypt,
				':email'		=> $emailEncrypt
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			//$rsInsertAuth->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		//$aErrors[] = $preparedQuery;
		
		//START | UPDATE MEMBERS TABLE
		$query = "
			UPDATE ".$_SESSION['members_table']."
			SET username=:username, timestamp=UNIX_TIMESTAMP()
			WHERE member_id=:member_id
		";
		try{
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':member_id' 		=> $memberID,
				':username'			=> $newUsername
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			//$rsUpdate->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$aErrors[] = $preparedQuery;
		//END | UPDATE MEMBERS TABLE
		
		//START | Update legacy with new username
		$query = '';
		//END | Update legacy with new username
		
		if(!empty($aErrors)){
			echo '<div class="alert alert-danger"><ul>';
			foreach($aErrors as $key=>$error){
				echo '<li>'.$error.'</li>';	
			}
			echo '</ul></div>';
		}
		
	}else{
		
		echo '<div class="alert alert-danger"><ul>';
		foreach($aErrors as $key=>$error){
			echo '<li>'.$error.'</li>';	
		}
		echo '</ul></div>';
			
	}
	
		
}

if($process == 'change-email'){
	
	include('../../../secure/crypto.php');
	
	$newEmail 	= $_REQUEST['email'];
	$newEmailEncrypt	= encrypt($newEmail);
	
	$memberID	= $_REQUEST['member_id'];
	$aErrors	= array();
	
	/*if(filter_var($newEmail, FILTER_VALIDATE_EMAIL)){
		
	}else{
		$aErrors[] = 'Invalid email address';	
	}*/
	
	//Get auth record for member id
	$query = "
		SELECT email, username, password
		FROM ".$_SESSION['auth_table']."
		WHERE member_id=:member_id
		ORDER BY timestamp DESC
		LIMIT 1
	";
	
	try{
		$rsGetAuth = $mLink->prepare($query);
		$aValues = array(
			':member_id' 	=> $memberID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetAuth->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$auth = $rsGetAuth->fetch(PDO::FETCH_ASSOC);
	
	$pwEncrypt			= $auth['password'];
	$usernameEncrypt	= $auth['username'];
	$oldEmailEncrypt	= $auth['email'];
	$oldEmail			= decrypt($oldEmailEncrypt);
	
	//$aErrors[] = $oldEmail.' | '.$newEmail;
	
	if(trim($oldEmail) == trim($newEmail)){
		$aErrors[] = 'Email is the same, please enter a new email or close out of this modal.';	
	}
	
	//Search the auth table to make sure that email doesnt exist
	$query = "
		SELECT email
		FROM ".$_SESSION['auth_table']."
		WHERE email=:email AND member_id<>:member_id
		ORDER BY timestamp DESC
	";
	
	try{
		$rsGetAuth = $mLink->prepare($query);
		$aValues = array(
			':email' 		=> $newEmailEncrypt,
			':member_id'	=> $memberID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGetAuth->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$emailCnt = $rsGetAuth->rowCount();
	
	if($emailCnt > 0){
		$aErrors[] = "Email already exists in the database. Please use another one.";	
	}
	
	if(empty($newEmail)){
		$aErrors = 'Please provide an email address.';	
	}
	
	if(empty($aErrors)){
		
		//Create new auth record
		$query = "
			INSERT INTO ".$_SESSION['auth_table']." (
				member_id,
				timestamp,
				username,
				password,
				password_score,
				password_score_ack_timestamp,
				email,
				reset_request_timestamp,
				reset_request_ip,
				email_validated_timestamp,
				imported
			) VALUE (
				:member_id,
				UNIX_TIMESTAMP(),
				:username,
				:password,
				'0',
				'0',
				:email,
				'0',
				'',
				UNIX_TIMESTAMP(),
				'0'
			)		
		";
		
		try{
			$rsInsertAuth = $mLink->prepare($query);
			$aValues = array(
				':member_id' 	=> $memberID,
				':username'		=> $usernameEncrypt,
				':password'		=> $pwEncrypt,
				':email'		=> $newEmailEncrypt
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsInsertAuth->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		
		//START | UPDATE MEMBERS TABLE
		$query = "
			UPDATE ".$_SESSION['members_table']."
			SET email=:email, timestamp=UNIX_TIMESTAMP()
			WHERE member_id=:member_id
		";
		try{
			$rsUpdate = $mLink->prepare($query);
			$aValues = array(
				':member_id' 		=> $memberID,
				':email'			=> $newEmail
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsUpdate->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		//END | UPDATE MEMBERS TABLE
		
	}else{
		
		echo '<div class="alert alert-danger"><ul>';
		foreach($aErrors as $key=>$error){
			echo '<li>'.$error.'</li>';	
		}
		echo '</ul></div>';
			
	}
	
}

if($process == 'reprice-fund'){
	
	//$dataType		= $_REQUEST['data-type'];
	$aFundsValues	= explode(',',str_replace(' ','',$_REQUEST['fund_id']));
	
	//print_r($aFundsValues);
	//echo $dataType;
		
	$showQueries	= $_REQUEST['process-queries'];
	$apiServer		= $_REQUEST['api-server'];
	$day			= $_REQUEST['day'];
	$month			= $_REQUEST['month'];
	$year			= $_REQUEST['year'];
	$fromDate		= mktime(4,0,0,$month,$day,$year);

        // Reset minimum date to 3/27/18 - JS
        if ($fromDate < 1522108800){
                $day    = '27';
                $month  = '03';
                $year   = '2018';
                $fromDate = mktime(4,0,0,$month,$day,$year);

        }

	$fromDateStr	= $year.''.$month.''.$day;
	$today			= time();
	$newDate		= 0;
	$repriceIncept	= $_REQUEST['from-incpet'];
	$aPriceDates 	= array();
	
	
	
	switch($apiServer){
		case 'api1': $port = rand(52000, 52099);$apiType = '1';break;
		case 'api2': $port = rand(52100, 52199);$apiType = '2';break;
                case 'api3': $port = rand(52500, 52699);$apiType = '3';break;
	}
	
	foreach($aFundsValues as $key=>$fundID){
										
		$query = "
			SELECT mf.fund_id, mf.fund_symbol, m.username, mf.unix_date as incept, inception_date
			FROM members_fund as mf
			INNER JOIN members as m ON m.member_id=mf.member_id
			WHERE mf.fund_id=:fund_id AND mf.active='1'
		";
		try{
			$rsFundID = $mLink->prepare($query);
			$aValues = array(
				':fund_id' 	=> $fundID
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsFundID->execute($aValues);
		}
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
		$getFund = $rsFundID->fetch(PDO::FETCH_ASSOC);
		
		$fundID = $getFund['fund_id'];
		
		
		if($fundID != ''){
			$aFunds[$fundID] = array(
				'fundKey'		=> $fundKey,
				'fundSymbol'	=> $getFund['fund_symbol'],
				'username'		=> strtolower($getFund['username']),
				'inception'		=> $getFund['incept'],
				'inceptStr'		=> $getFund['inception_date']
			);
		}
	}
	
	$aQueries = array();
						
						
	$fundCnt = 0;
	//Loop through and create data legacy queries
	foreach($aFunds as $fundID=>$aFundInfo){
		
		if($repriceIncept == '1'){
		
			$fromDate 		= $aFundInfo['inception'];
			$fromDateStr	= $aFundInfo['inceptStr'];
				
		}
		
		if($showQueries != '1'){
			//Remove Price Data
			$query = "
				DELETE FROM members_fund_pricing
				WHERE fund_id=:fund_id AND unix_date>=:target_date
			";
			try{
				$rsDelete = $mLink->prepare($query);
				$aValues = array(
					':fund_id' 		=> $fundID,
					':target_date'	=> $fromDate
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsDelete->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			//Remove existing trade data
			$deleteTradesQuery = "
				DELETE FROM members_fund_trades
				WHERE fund_id=:fund_id AND unix_close>=:target_date
			";
			try{
				$rsDelete2 = $mLink->prepare($deleteTradesQuery);
				$aValues = array(
					':fund_id' 		=> $fundID,
					':target_date'	=> $fromDate
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsDelete2->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			//delete position detail datea (data that shows in the ledger
			$query = "
				DELETE FROM members_fund_positions WHERE fund_id=:fund_id AND `date`>:reprice_date
			";
			try{
				$rsDeletePD = $mLink->prepare($query);
				$aValues = array(
					':fund_id' 			=> $fundID,
					':reprice_date'		=> $fromDateStr
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsDeletePD->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			//echo $preparedQuery;
		}
		
		$fundCnt++;
		
		$username 	= strtolower($aFundInfo['username']);
		$fundSymbol	= $aFundInfo['fundSymbol'];
		//$aQueries[] = 'aggregateStatistics|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.date('Ymd', strtotime('yesterday'));
		//$aQueries[] = 'alphaBetaStatistics|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.date('Ymd', strtotime('yesterday'));
		//$aQueries[] = 'livePrice|'.$username.'|'.$fundID.'|'.$fundSymbol;
		
		//$aQueries[] = 'tradesForFund|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$fromDateStr;
		//$aQueries[] = 'allPositionInfo|'.$username.'|'.$fundID.'|'.$fundSymbol;
		
		//NEW LEGACY API FUNCTION
		$aMethodVars[] = array(
			'method'		=> 'aggregateStatistics',
			'source'		=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund',
			'api'			=> $apiType,
			'port'			=> '',
			'username'		=> $username,
			'fund_id'		=> $fundID,
			'fund_symbol'	=> $fundSymbol,
			'from_date'		=> date('Ymd', strtotime('yesterday')),
			'start_date'	=> '',
			'end_date'		=> '',
			'group'			=> date('Ymd-h.i', time()).'-reprice-fund'
		);
		
		$aMethodVars[] = array(
			'method'		=> 'alphaBetaStatistics',
			'source'		=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund',
			'api'			=> $apiType,
			'port'			=> '',
			'username'		=> $username,
			'fund_id'		=> $fundID,
			'fund_symbol'	=> $fundSymbol,
			'from_date'		=> date('Ymd', strtotime('yesterday')),
			'start_date'	=> '',
			'end_date'		=> '',
			'group'			=> date('Ymd-h.i', time()).'-reprice-fund'
		);
		
		$aMethodVars[] = array(
			'method'		=> 'livePrice',
			'source'		=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund',
			'api'			=> $apiType,
			'port'			=> '',
			'username'		=> $username,
			'fund_id'		=> $fundID,
			'fund_symbol'	=> $fundSymbol,
			'from_date'		=> '',
			'start_date'	=> '',
			'end_date'		=> '',
			'group'			=> date('Ymd-h.i', time()).'-reprice-fund'
		);
		
		$aMethodVars[] = array(
			'method'		=> 'allPositionInfo',
			'source'		=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund',
			'api'			=> $apiType,
			'port'			=> '',
			'username'		=> $username,
			'fund_id'		=> $fundID,
			'fund_symbol'	=> $fundSymbol,
			'from_date'		=> '',
			'start_date'	=> '',
			'end_date'		=> '',
			'active_only'	=> '1',
			'group'			=> date('Ymd-h.i', time()).'-reprice-fund'
		);
		
		$aMethodVars[] = array(
			'method'		=> 'tradesForFund',
			'source'		=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund',
			'api'			=> $apiType,
			'port'			=> '',
			'username'		=> $username,
			'fund_id'		=> $fundID,
			'fund_symbol'	=> $fundSymbol,
			'from_date'		=> $fromDateStr,
			'start_date'	=> '',
			'end_date'		=> '',
			'group'			=> date('Ymd-h.i', time()).'-reprice-fund'
		);
		
		$aPriceDates = priceRunDateArray($newDate, $today, $fromDate, $aFundInfo['inception']);
		
		foreach($aPriceDates as $key=>$aDate){
			
			$start 	= date('Ymd', $aDate['start']);
			$end	= date('Ymd', $aDate['end']);
			
			$aMethodVars[] = array(
				'method'		=> 'priceRun',
				'source'		=> 'Admin Reprice Script | admin-member-lookup-processes.php | process: reprice-fund',
				'api'			=> $apiType,
				'port'			=> '',
				'username'		=> $username,
				'fund_id'		=> $fundID,
				'fund_symbol'	=> $fundSymbol,
				'from_date'		=> '',
				'start_date'	=> $start,
				'end_date'		=> $end,
				'group'			=> date('Ymd', time()).'-reprice-fund'
			);

			//$aQueries[] = 'priceRun|'.$username.'|'.$fundID.'|'.$fundSymbol.'|'.$start.'|'.$end;
				
		}
			
	}
	
	if($showQueries == '1'){
		echo $fromDate.' | '.$fromDateStr.'<br />';
		echo 'Done!<br />'.$fundCnt.' Funds Queued<br />'.count($aMethodVars).' Queries Queued<br />';
		echo '<pre>';
		//echo 'Port:'.$port.'<br />';
		print_r($aMethodVars);
		//print_r($aPriceDates);
		//print_r($aFundsValues);
		echo '</pre>';
	}else{
		
		$queryCnt = 0;
		
		/*switch($apiServer){
			case 'api1': $portStart = 52000;$portStop = 52099;break;
			case 'api2': $portStart = 52100;$portStop = 52199;break;
                        case 'api3': $portStart = 52500;$portStop = 52699;break;
		}
		
		$port = $portStart;*/
		
		//Process All Queries
		/*foreach($aQueries as $key=>$query){
			
			if($port >= $portStop){
				$port = $portStart;	
			}
			
			$port++;
			
			$queryCnt++;
			include ('../../includes/data-query-legacy.php');
			usleep(500000);
		}*/
		
		//NEW LEGACY API FUNCTION
		$mlaResults = legacy_api($mLink, $aMethodVars, true);
		
		echo 'Done!<br />'.$fundCnt.' Funds Processed<br />'.count($aMethodVars).' Queries Processed<br />'.date('Ymd', strtotime('yesterday'));
		
		echo '<br /><br /><h4>Debug</h4><br /><br />'.$mlaResults;
		
	}
		
}

?>
