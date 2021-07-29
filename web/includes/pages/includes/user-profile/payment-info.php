<?php
/*
Marketocracy Inc. | Beta Development
User Profile/Settings - Payment Info - Include File

Supporting files:
	AJAX			- process/ajax/user-settings-processes.php
	JAVASCRIPT		- JAVASCRIPT includes/scripts/user-settings.js.php
	HTML			- includes/pages/user-profile.php
	PHP Includes	- THIS DOCUMENT includes/pages/includes/user-profile/payment-info.php
*/
?>

<!-- BEGIN EDIT PATH MODAL-->
<div class="modal fade" id="edit-method" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
     <div class="modal-content" id="load-edit-method-area">
        
     </div>
     <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<!-- END EDIT Method MODAL-->

<!-- BEGIN Delete Payment Method MODAL-->
<div class="modal fade" id="delete-method" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
     <div class="modal-content" id="load-delete-payment-method">
        
     </div>
     <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<!-- END Delete Payment Method MODAL-->

<!-- BEGIN NEW Payment Method MODAL-->
<div class="modal fade" id="new-card" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
     <div class="modal-content" id="load-new-payment-method">
        
     </div>
     <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<!-- END New Payment Method MODAL-->

<!-- BEGIN Invoice MODAL-->
<div class="modal fade" id="view-invoice" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
     <div class="modal-content" id="load-invoice">
        
     </div>
     <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<!-- END Invoice MODAL-->



<ul  class="nav nav-tabs">
    <li <?php if($subTab == '' || $subTab == 'methods'){ echo 'class="active"';}?>><a href="#payment_methods" data-toggle="tab">Payment Methods</a></li>
    <li <?php if($subTab == 'billing_history'){ echo 'class="active"';}?>><a href="#billing_history" data-toggle="tab">Billing History</a></li>
    
</ul>

<div  class="tab-content">
    
    <div class="tab-pane fade <?php if($subTab == '' || $subTab == 'methods'){ echo 'active';}?> in" id="payment_methods">
    	
        
        <div class="row">
        	
            <div class="col-md-9">
	            <h3>Credit &amp; Debit Cards <span style="float:right;font-size:16px;padding-right:40px;position:relative;top:7px;" class="hidden-xs"><strong>Expires</strong></span></h3>
                <div class="clearfix"></div>
                <div class="panel-group accordion" id="payment-options">
                    
                    
                    <?php 
					$query = "
					 SELECT *
					 FROM ".$_SESSION['payment_profiles_table']."
					 WHERE member_id=:member_id
					";
					try{
						$rsGetPayments = $mLink->prepare($query);
						$aValues = array(
							':member_id' => $_SESSION['member_id']
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsGetPayments->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
					$aBillingAddress = array();								
					while($pay = $rsGetPayments->fetch(PDO::FETCH_ASSOC)) {
						
						$billFirstName		= $pay['billTo_firstName'];
						$billLastName		= $pay['billTo_lastName'];
						$billAddress		= $pay['billTo_address'];
						$city				= $pay['billTo_city'];
						$state				= $pay['billTo_state'];
						$zip				= $pay['billTo_zip'];
						$country			= $pay['billTo_country'];
						$cardType			= $pay['cardType'];
						$cardLast4			= $pay['cardLastFour'];
						$cardName			= $pay['cardName'];
						$cardExpiration		= $pay['cardExpiration'];
						$billPhone			= $pay['billTo_phone'];
						$billEmail			= $pay['billTo_email'];

						if (!isset($_SESSION['customerProfileID'])){
							$_SESSION['customerProfileID'] = $pay['customerProfileID'];
						}

						$found= false;
						foreach($aBillingAddress as $aUID=>$aAddress){
							
							if($aAddress['address'] == trim($billAddress)){
								$found = true;
								break;	
							}
						}
						
						if($found === false){
							$aBillingAddress[$pay['uid']]	= array(
								'billName'		=> $billFirstName.' '.$billLastName,
								'firstName'		=> $billFirstName,
								'lastName'		=> $billLastName,
								'address'		=> $billAddress,
								'city'			=> $city,
								'state'			=> $state,
								'zip'			=> $zip,
								'country'		=> $country,
								'phone'			=> $billPhone,
								'email'			=> $billEmail
							);
						}
						
						$today = time();
						
						if($cardExpiration < $today){
							//card is expired
							$cardExpire = '<span style="color:#E02222;">Expired!</span>';
							
							$expireNotice = '
								<div class="alert alert-danger">
									<h4>This card exipired on '.date('m/Y',$cardExpiration).'! Please update or remove this card.</h4>
								</div>
							';	
						}else{
							$cardExpire = date('m/Y',$cardExpiration);	
							
							$expireNotice = '';
						}
						
						
						
						switch($cardType){
							
							case 'Visa':
								$cardImg = 'visa-straight-32px.png';
							break;
							
							case 'Mastercard':
								$cardImg = 'mastercard-straight-32px.png';
							break;
							
							case 'Discover':
								$cardImg = 'discover-straight-32px.png';
							break;
							
							case 'American Express':
								$cardImg = 'american-express-straight-32px.png';
							break;
								
						}
						
						//store billing info into a session for use in the processing files
						$_SESSION['aBillingAddresses'] = $aBillingAddress;
						?>
                        <div class="panel panel-default payment-container">
                            <div class="panel-heading accordion-fix">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle" data-toggle="collapse" data-parent="#payment-options" href="#payment_<?php echo $pay['uid'];?>">
                                        <span><img src="images/payments/<?php echo $cardImg;?>" alt="<?php echo $cardType;?>" width="40" height="25"/></span> 
                                        <span style="position:relative;top:2px;padding-left:5px;"><?php echo $cardType;?> ending in <?php echo $cardLast4;?> </span>
                                        <span class="accordion-icons accordion-expand"></span><span style="float:right;position:relative;top:5px;right:10px;"><strong><?php echo $cardExpire;?></strong></span>
                                    </a>
                                </h4>
                                
                            </div>
                            <div id="payment_<?php echo $pay['uid'];?>" class="panel-collapse collapse">
                                <div class="panel-body">
                                    
                                    <?php echo $expireNotice;?>
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <h5><strong>Name on Card</strong></h5>
                                            <p><?php echo $cardName;?></p>
                                        </div>

                                        <div class="col-md-8">
                                            <h5><strong>Billing Address</strong></h5>
                                            <p><?php echo $billFirstName.' '.$billLastName;?><br /><?php echo $billAddress;?><br /><?php echo $city;?>, <?php echo $state;?> <?php echo $zip;?>, <?php echo $country;?></p>
                                        </div>
                                    </div><!--row-->
                                    
                                    <div class="payment-btns">
                                        <a href="#delete-method" data-toggle="modal" class="btn btn-default" onclick="deletePayment('<?php echo $pay['uid'];?>');">Delete</a> <a href="#edit-method" data-toggle="modal" class="btn btn-default" onclick="editPayment('<?php echo $pay['uid'];?>');">Edit</a>
                                    </div>
                                    
                                    
                                </div>
                            </div>
                        </div><!--panel-->
                        <?php
						
					}
					?>
                    
                    
                    
                
                </div><!--end panel accordion-->
                
                
                
                     
            </div><!--col-md-9-->
            
            <div class="col-md-3">
            	<h3>Actions</h3>
            	<a href="#new-card" onclick="newPayment();" data-toggle="modal" class="btn btn-info" style="display:block;width:100%;margin-top:"><i class="icon-plus"></i> Add Card</a>
            </div><!--col-md-3-->
            
        </div><!--row-->
        
        
    </div><!--payment-methods-->
    
    <div class="tab-pane fade <?php if($subTab == 'billing_history'){ echo 'active in';}?>" id="billing_history">
    	
        <div class="portlet">
            <div class="portlet-title">
            <div class="caption"><i class="icon-reorder"></i>Billing History</div>
                <div class="actions">
                    <div class="btn-group">
                       <a class="btn btn-info dropdown-toggle" href="#" data-toggle="dropdown">
                       Columns
                       <i class="icon-angle-down"></i>
                       </a>
                       <div id="billing_history_table_column_toggler" class="dropdown-menu hold-on-click dropdown-checkboxes pull-right">
                          <label><input type="checkbox" checked data-column="0">Date</label>
                          <label><input type="checkbox" checked data-column="1">Invoice Number</label>
                          <label><input type="checkbox" checked data-column="2">Price</label>
                          <label><input type="checkbox" checked data-column="3">Description</label>
                          <label><input type="checkbox" checked data-column="4">Action</label>
                       </div>
                    </div><!--btn-group-->
                 </div><!--actions-->
            </div><!--portlet-title-->
            <div class="portlet-body">
            	<table class="table table-striped table-bordered table-hover table-full-width" id="billing_history_table">
                    <thead>
                       <tr>
                          <th>Date</th>
                          <th>Description</th>
                          <th>Price</th>
                          
                          <th>Invoice #</th>
                          <th>Action</th>
                       </tr>
                    </thead>
                    <tbody>
                       <?php
					   $query = "
					   		SELECT *
							FROM ".$_SESSION['transaction_history_table']."
							WHERE member_id=:member_id 
							ORDER BY bill_timestamp DESC
					   ";
					   try{
							$rsTranHistory = $mLink->prepare($query);
							$aValues = array(
								':member_id' 	=> $memberID
							);
							$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
							$rsTranHistory->execute($aValues);
						}
						catch(PDOException $error){
							// Log any error
							file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
						}
						while($transHistory = $rsTranHistory->fetch(PDO::FETCH_ASSOC)){
							
							?>
                            <tr >
                              <td><?php echo date('m/d/Y', $transHistory['bill_timestamp']);?></td>
                              <td><?php echo $transHistory['itemName'];?></td>
                              <td>$<?php echo number_format($transHistory['transaction_amount'], 2, '.',',');?></td>
                              <td><?php echo $transHistory['order_invoiceNumber'];?></td>
                              <td><?php /*?><a href="#" class="btn btn-xs btn-default">Download</a> <?php */?><a href="#view-invoice" onclick="loadInvoice('<?php echo $transHistory['order_invoiceNumber'];?>');" data-toggle="modal" class="btn btn-xs btn-info">View</a></td>
                            </tr>
                            <?php
							
						}
					   	?>
                        
                        
                       
                    </tbody>
                 </table>
            </div><!--portlet-body-->
    	</div><!--portlet-body-->
        
    </div><!--billing_history-->
    
</div><!--tab content-->