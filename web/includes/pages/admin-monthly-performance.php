<?php
/*
Marketocracy Inc. | Beta Development
Admin campaigns

Supporting files:
	AJAX		- process/ajax/admin-campaigns-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/admin-campaigns.js.php
	HTML		- THIS DOCUMENT includes/pages/admin-campaigns.php
*/

$allowedMembers = '10,11'; //3,4,10,11
$aManagers		= array();
$aMembers		= array();

$query = "
	SELECT ms.member_id, m.name_first, m.name_last, m.username, ms.product_id
	FROM `members_subscriptions` as ms
	JOIN members as m ON m.member_id=ms.member_id
	WHERE ms.product_id IN (".$allowedMembers.") and ms.active='1'
";
try{
	$rsMembers = $mLink->prepare($query);
	$aValues = array(
		
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsMembers->execute($aValues);
}

catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}
			
				
while($memberInfo = $rsMembers->fetch(PDO::FETCH_ASSOC)){	
	
	if($memberInfo['product_id'] == 10){
		$aManagers[$memberInfo['member_id']] = array(
			'name_first'	=> $memberInfo['name_first'],
			'name_last'		=> $memberInfo['name_last'],
			'username'		=> $memberInfo['username']
		);	
	}else{
		$aMembers[$memberInfo['member_id']] = array(
			'name_first'	=> $memberInfo['name_first'],
			'name_last'		=> $memberInfo['name_last'],
			'username'		=> $memberInfo['username']
		);		
	}
	
}

?>

         
            <!-- BEGIN PAGE CONTENT-->
            
                
            <div class="row">
                <div class="col-md-12">
                    
                    <div class="tabbable tabbable-custom">
                        
                        <?php include('includes/nav-admin-tabs.php');?>

                        <div class="tab-content">
                        
                            
                            
                            <div class="tab-pane active" id="tab_1">
                                
                                <div class="portlet">
                                    <div class="portlet-title">
                                        <div class="caption">Queue Monthly Performace Emails</div>
                                        <div class="actions">
                                            <?php /*?><a class="btn btn-info btn-xs" href="#email-campaign" data-toggle="modal">New Campaign</a><?php */?>
                                        </div><!--tools-->
                                    </div><!--portlet-title-->
                                    <div class="portlet-body" style="background: #FAFAFA;">
                                        
                                        
                                        
                                        <div id="gcf-form-errors"></div>
                                        
                                        <form action="" method="post" class="generate-campaigns-form">
                                                <div class="form-body">
                                                    
                                                     <div class="form-group">
                                                        <label class="control-label">Select Month</label><br />
                                                        
                                                        <?php
														$currentMonth = date('m');
														?>
                                                        
                                                        <select name="year">
                                                            <?php
                                                            echo date_list($mLink, 'year', NULL, NULL, false);
                                                            ?>
                                                        </select>/
                                                        <select  name="month">
                                                            <?php
                                                            echo date_list($mLink, 'month', NULL, $currentMonth, true);
                                                            ?>
                                                        </select>
                                                        
                                                        <small>YYYY/MM</small><br /><br />
                                                	</div><!--form-group-->
                                                    
                                                   	<div class="form-group">
                                                    	<label class="control-lable">Manager</label>
                                                    	<select name="managers" class="form-control">
                                                        	<option value="all">All</option>
                                                            <option value="XX">-----------------</option>
                                                            <option value="2">Brandon McCarthy</option>
                                                            <option value="XX">-----------------</option>
															<?php
															foreach($aManagers as $memberID=>$memberData){
																echo '<option value="'.$memberID.'">'.$memberData['name_first'].' '.$memberData['name_last'].' ('.$memberData['username'].')</option>';	
															}
															?>
                                                            <option value="XX">-----------------</option>
                                                            <?php
															foreach($aMembers as $memberID=>$memberData){
																echo '<option value="'.$memberID.'">'.$memberData['name_first'].' '.$memberData['name_last'].' ('.$memberData['username'].')</option>';	
															}
															?>
                                                        </select>
                                                    </div>
                                                    
                                                    <div id="generate-camp-submit-btn"><input type="submit" name="submit-btn" id="generate-camp-btn" value="Generate Campaigns" class="btn btn-success" /></div>
                                                    
                                            	</div><!--form-body-->
                                            </form>
                                            
                                            <div id="monthly-status"></div>
                                            
                                            
                                        
                                        
                                        
                                        <div id="load-results">
                                        
                                        </div>
                                        
                                    
                                    </div><!--portlet-body-->
                                </div><!--end-portlet-->
                                
                                <div class="portlet">
                                    <div class="portlet-title">
                                        <div class="caption">Monthly Performance History</div>
                                        <div class="actions">
                                            <a class="btn btn-info btn-xs" href="#email-campaign" data-toggle="modal">New Campaign</a>
                                        </div><!--tools-->
                                    </div><!--portlet-title-->
                                    <div class="portlet-body" style="background: #FAFAFA;">
                                        
                                        
                                        
                                    
                                    </div><!--portlet-body-->
                                </div><!--end-portlet-->
                                
                                
                            </div><!--END TAB 2-->
                            
                            
                        
                        
                        </div><!--tab-content-->
                    </div><!--tabbable tabbable-custom-->
                    
                </div><!--col-md-12-->
            </div><!--row-->
            <!-- END PAGE CONTENT-->    
   