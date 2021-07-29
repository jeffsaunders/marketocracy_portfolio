<?php
/*
Marketocracy Inc. | Beta Development
User Profile/Settings - Membership - Include File

Supporting files:
	AJAX			- process/ajax/user-membership-processes.php
	JAVASCRIPT		- JAVASCRIPT includes/scripts/user-settings.js.php
	HTML			- includes/pages/user-profile.php
	PHP Includes	- THIS DOCUMENT includes/pages/includes/user-profile/membership.php
*/

$enableTrialOffer		= true;
$first15promo			= .8; #20 percent
$second15promo			= .9; #10 percent
$memberFirstName 		= get_member($mLink, $_SESSION['member_id'], 'first name');

//GET THE CURRENT SUBSCRIPTION RECORD
subscription($mLink, $_SESSION['member_id'], true);

$subData 				= $_SESSION['subscription']['subData'];

$subProduct				= $subData['product_id'];
$availableProducts		= explode(',',$subData['available_products']);

$trial30 				= false;
$trial15				= false;
$inTrial				= false;
$subChange				= false;

//Get Days till end of subscription
$endOfSub				= $_SESSION['subscription']['subEndDate'];
$daysLeft				= $_SESSION['subscription']['daysLeft'];
$daysLeftRound			= $_SESSION['subscription']['daysLeftRound'];

$newProductID			= $_SESSION['subscription']['newProductID'];

//Check to see if subscription is a Trial Period
if($subProduct == '99' || $subProduct == '0'){
	
	$inTrial = true;
	
	if($daysLeft > 15){
		$trial30 = true;	
	}else{
		$trial15 = true;
	}
	
	$alertHeader		= 'Hello '.get_member($mLink, $_SESSION['member_id'], 'full name').',';
	
	
	if($newProductID != NULL){
		
		$subChange = true;
		
		$newProductInfo = get_product($mLink, $newProductID);
		
		$aDebug['newProduct'] = $newProductInfo;
		
		if($newProductInfo['product_type'] == 'membership'){
			//paid member
			
			$alertType 		= 'success';
			$alertMessage	= '<h3>Thank you for being a '.$newProductInfo['alt_product_name'].' member!</h3><p>You currently have <strong>'.$daysLeftRound.'</strong> days left in your <strong>Free Pro Membership Trial</strong>. Your new membership will automatically go into effect after the trial period has concluded. You will be billed <strong>'.$_SESSION['subscription']['term'].'</strong> starting on <strong>'.date('m/d/Y',$_SESSION['subscription']['nextBillDate']).'</strong>.</p>';
			
		}else{
			//basic member
			$alertType 		= 'success';
			$alertMessage	= '<h3>Thank you for purchasing '.$newProductInfo['alt_product_name'].'!</h3><p>You currently have <strong>'.$daysLeftRound.'</strong> days left in your <strong>Free Pro Membership Trial</strong>. You will automatically be converted to a Basic Membership after the trial period has concluded. At that time you will be able to maintain <strong>'.$newProductInfo['track_records'].'</strong> Unlimited Track Records. Your next billing date is <strong>'.date('m/d/Y',$_SESSION['subscription']['nextBillDate']).'</strong>.</p>';
		}
	}else{
		
		if($daysLeft > 1 && $daysLeft < 3){
			
			$alertType 		= 'warning';
			$alertMessage	= 'Tomorrow is the last day of your <strong>'.$subData['alt_product_name'].'</strong>. Upgrade now to take advantage of promotional pricing!';
								
		}elseif($daysLeft < 1){
			$alertType 		= 'warning';
			$alertMessage	= 'Today is the last day of your <strong>'.$subData['alt_product_name'].'</strong>. Upgrade now to take advantage of promotional pricing!';
		}else{
			$alertType 		= 'info';
			$alertMessage	= 'You currently have <strong>'.$daysLeftRound.'</strong> days left in your <strong>'.$subData['alt_product_name'].'</strong>. Upgrade now to take advantage of promotional pricing!';
		}
	}
	
	$details		= "
	<h3>Details</h3>
	<h5>Member Since: <strong>".date("M. j, Y", $_SESSION['joined_timestamp'])."</strong></h5>
	<h5>Membership: <strong>".$subData['product_name']."</strong></h5>
	<h5>Expiration Date: <strong>".date('m/d/Y',$endOfSub)."</strong></h5>
	<!--<h5>Account Balance: <strong>$0.00</strong></h5>-->";
		
}elseif($subProduct >= 2 && $subProduct <= 4){
	#in membership
	$details		= "
	<h3>Details</h3>
	<h5>Member Since: <strong>".date("M. j, Y", $_SESSION['joined_timestamp'])."</strong></h5>
	<h5>Membership: <strong>".$subData['product_name']."</strong></h5>
	<h5>Expiration Date: <strong>".date('m/d/Y',$endOfSub)."</strong></h5>
	<!--<h5>Account Balance: <strong>$0.00</strong></h5>-->";
		
}else{
	
	#basic
	$details		= "
	<h3>Details</h3>
	<h5>Member Since: <strong>".date("M. j, Y", $_SESSION['joined_timestamp'])."</strong></h5>
	<h5>Membership: <strong>".$subData['product_name']."</strong></h5>
	<!--<h5>Balance: <strong>$0.00</strong></h5>-->";
	
}

//Build an array of available products


$allProducts = array();
					
$query = "
	SELECT *
	FROM ".$_SESSION['products_table']."
	WHERE active='1'
";
try {
	$rsGetMembership = $mLink->prepare($query);
	$aValues = array();
	// Prepared query - for error logging and debugging
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsGetMembership->execute($aValues);
}
catch(PDOException $error){
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}
while($product = $rsGetMembership->fetch(PDO::FETCH_ASSOC)){
	
	$allProducts[$product['product_type']][$product['product_id']] = array(
		'product_name'			=> $product['product_name'],
		'product_parent_id'		=> $product['product_parent_id'],
		'max_quantity'			=> $product['max_quantity'],
		'level_flag'			=> $product['level_flag'],
		'monthly_price'			=> $product['monthly_price'],
		'annual_price'			=> $product['annual_price'],
		'product_desc'			=> $product['product_desc'],
		'commitment'			=> $product['commitment'],
		'track_records'			=> $product['track_records'],
		'track_records_history'	=> $product['track_record_history'],
		'forum_access'			=> $product['forum_access'],
		'icon'					=> $product['icon'],
		'display'				=> $product['display'],
		'support_level'			=> $product['support_level']
	);
		
}


$aDebug['transData'] = transactionDetails($mLink, '8-1479926744', 'invoiceNumber', 'raw');

/*echo '<pre>';

print_r($availableProducts);
print_r($allProducts['membership']);
echo '</pre>';*/

/*$query = "
	SELECT trial_start, trial_end
	FROM ".$_SESSION['auth_table']."
	WHERE member_id=:member_id
	ORDER BY timestamp DESC
	LIMIT 1
";
try {
	$rsAuth = $mLink->prepare($query);
	$aValues = array(
		':member_id'	=> $_SESSION['member_id']
	);
	// Prepared query - for error logging and debugging
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsAuth->execute($aValues);
}
catch(PDOException $error){
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}


$auth = $rsAuth->fetch(PDO::FETCH_ASSOC);

$today		= time();
$trialStart = $auth['trial_start'];
$trialEnd	= $auth['trial_end'];
$trial15	= strtotime('+15 day',$trialStart);*/

//Get membership flags
/*if($_SESSION['flag_trial'] == '1'){
	
	$membership = "Premium Trial";
	$flag		= 'trial,premium';	
	
}elseif($_SESSION['flag_free'] == '1'){
	$membership = 'Basic';	
	$flag		= 'free';
	
}elseif($_SESSION['flag_standard'] == '1'){
	
	$membership = "Standard";
	$flag		= 'standard';
	
	if($_SESSION['flag_student'] == '1'){
		$membership = "Student Standard";
		$flag		= 'student,standard';
	}
		
}elseif($_SESSION['flag_premium'] == '1'){

	$membership = "Premium";
	$flag		= 'premium';
	
	if($_SESSION['flag_student'] == '1'){
		$membership = "Student Premium";
		$flag		= 'student,premium';
	}
		
}
$validStudent = false;
if($_SESSION['flag_student'] == '1'){
	$validStudent = true;
}

if($today <= $trial15){
	$promo15 = 1;	
}else{
	$promo15 = 0;	
}
*/


/*// Get Pro level monthly rate to calculate "Early Bird" savings values.
$query = "
	SELECT monthly_price
	FROM ".$_SESSION['products_table']."
	WHERE product_id = 3
";
try {
	$rsProPrice = $mLink->prepare($query);
	$rsProPrice->execute();
}
catch(PDOException $error){
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}
$pro_price = $rsProPrice->fetch(PDO::FETCH_ASSOC);

$proPrice = $pro_price["monthly_price"];

if($_SESSION['flag_trial'] == '1'){

	$daysLeft 		= ceil(($trialEnd - $today) / (60*60*24));

	if($promo15 == 1){
		$alertType 		= 'info';
		$alertHeader	= 'Hello '.$memberFirstName.',';
		$alertMessage	= 'You are currently experiencing a <strong>Pro Membership Trial</strong>. You have <strong>'.$daysLeft.' Days</strong> left in your trial period and you only have <strong>'.($daysLeft - 15).' days left</strong> to qualify for the <em>"Quick Decision Maker"</em> <strong>Two (2) Months FREE</strong> special, so sign up now! That\'s up to <strong>$'.money_format("%n", $proPrice * 2).' in savings!</strong>';
	}else{
		$alertType 		= 'info';
		$alertHeader	= 'Hello '.$memberFirstName.',';
		$alertMessage	= 'You are currently experiencing a <strong>Pro Membership Trial</strong>. You have <strong>'.$daysLeft.' Days</strong> left in your trial period. Don\'t miss this opportunity to get <strong>One (1) Month FREE</strong> if you sign up now. That\'s up to <strong>$'.money_format("%n", $proPrice).' in savings!</strong> Offer ends when your trial concludes...don\'t let it get away, sign up today!';
	}
			
}else{
	$alertType 		= 'info';
	$alertHeader	= 'Hello '.get_member($mLink, $_SESSION['member_id'], 'full name').',';
	$alertMessage	= 'On <strong>March 30, 2016</strong>, you will be charged <strong>$415.00</strong> for a year of Pro Membership.';
}*/

?>
   		

        <!-- BEGIN Premium Membership MODAL-->
        <div class="modal fade" id="edu-check" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title">Validate .EDU Email Addresss</h4>
                    </div><!--modal-header-->
                    <div class="modal-body" id="load-edu-validate-form">
                    	
                    	<div class="no-valid-edu-email"></div>
         
                            <div class="form-group" id="edu-validate-field"  style="margin:0 0 10px 0 !important;">
                                <label class="control-label">Please enter a valid .EDU email address</label>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="icon-envelope"></i>
                                    </span>
                                    <input type="text" class="form-control" name="edu_email" id="edu_email" placeholder=".EDU Email Address" />
                                </div>
                                <!--<span class="help-block">Provide your email address</span>-->
                                <div id="error_edu_email"></div>
                                
                                <div id="validate-email-btn"></div>
                                
                            </div>
                            
                            <div class="form-group hide" id="edu-code-field"  style="margin:0 0 10px 0 !important;">
                                
                                <div class="alert alert-info">
                                    <p>An email with your Validation Code, has been sent to the email address you provided. Copy and paste the Validation Code into the field below, and click "Submit Code".<p>
                                    <p>Note: You may need to check your SPAM folder. If your verification email is in your SPAM folder, mark the email as "Not Spam" or move it to your inbox.</p>
                                </div>
                                
                                <label class="control-label">Enter Validation Code</label>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="icon-lock"></i>
                                    </span>
                                    <input type="text" class="form-control" name="validate_code" id="validate_code" placeholder="Validation Code" style="text-transform:uppercase;"/>
                                </div>
                                <div id="error_email_code2"></div>
                                <button type="button" class="btn btn-sm btn-info" style="margin-top:5px;" onclick="submitValidateCode('validate_code');">Submit Code</button>
                                <!--<span class="help-block">Provide your email address</span>-->
                                <div id="error_email_code"></div>
                            </div>
                    
                    </div><!--modal-body-->
                    <div class="modal-footer">
                    	
                	</div><!--modal--footer-->
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- END Premium Membership MODAL-->
        
        
        <!-- BEGIN Premium Membership MODAL-->
        <div class="modal fade" id="cart" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title" id="cart-header">Cart (<span class="cart-items"><?php echo $numItems;?></span> Items)</h4>
                    </div><!--modal-header-->
                    <div class="modal-body" id="load-cart">
                    	
                    
                    
                    </div><!--modal-body-->
                    <div class="modal-footer">
                    	<div id="load-cart-steps"></div>
                        <?php /*?><div id="prevStepBtn" style="float:left;"></div>
                    	<button id="clear-cart" type="button" class="btn btn-default" onclick="cart('clear','','','');" style="float:left;" data-dismiss="modal">Clear Cart</button>
                		<div id="nextStepBtn"></div>
                        <button type="button" class="btn btn-info <?php if($numItems == 0){echo 'hide';} ?>" id="checkout-btn" onclick="checkout();">Checkout</button><?php */?>
                        
                	</div><!--modal--footer-->
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- END Premium Membership MODAL-->
		
        <!-- BEGIN NEW Payment Method MODAL-->
        <div class="modal fade" id="new-card2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog">
             <div class="modal-content" id="load-new-payment-method2">
             		
             </div>
             <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- END New Payment Method MODAL-->
        
        
        <h3 class="form-section">
        	Membership 
            <span style="float:right;position:relative;top:-15px;"><a href="#cart" data-backdrop="static" data-toggle="modal" onclick="cart('view','','','');" class="btn btn-success"><i class="icon-shopping-cart"></i> Cart (<span class="cart-items"><?php echo $numItems;?></span> Items)</a></span>
            <span class="clearfix"></span>
        </h3>
        
        <?php if($_REQUEST['message'] == 'edu-success'){?>
        	<div class="alert alert-success">
            	<h5>You have successfully verified your .EDU email address.</h5>
            </div>
        <?php } ?>
        
        <?php if($alertType != ''){?>
        <div class="alert alert-<?php echo $alertType;?>">
            <h4><span style="color:#222"><?php echo $alertHeader;?></span></h4>
        	<h5><span style="color:#222"><?php echo $alertMessage;?></span></h5>
        </div><!--alert-->
        <?php } ?>
        
        <div class="row">
            <div class="col-md-4">
            	            
            	<div class="membership-info">
                	<?php echo $details;?>
                </div><!--membership-info-->
            </div><!--col-md-6-->
            
            <?php if($subChange == false){?>
            <div class="col-md-8">
                <h3>Memberships<!-- (<?php echo $flag;?>)--></h3>
                <div class="note note-info" style="margin-top:10px;">
                	<p>Click on a membership to upgrade or view more information.</p>

                    <?php if($validStudent == false){ ?>
<!--                    	<p>Are you a student? Do you have a valid .edu email address? <a href="#edu-check" data-toggle="modal">Click here</a> to validate your .edu email to take advantage of discounted membership pricing.</p>-->
                    <?php }	?>
                </div>

                <div class="clearfix"></div>

                <div class="panel-group accordion" id="memberships_parent">
                	
                    <?php
					
					//New Panels
					foreach($availableProducts as $key=>$productID){
						
						$product			= $allProducts['membership'][$productID];
//print_r($product);
//if ($product['product_name'] == "Basic"){continue;} // Don't display Basic level
//if ($product['product_name'] == "Pro"){continue;} // Don't display Pro level
if ($product['product_name'] != "FOLIOfn"){continue;} // Only display FOLIOfn level
 						#set variables
						$productName 		= $product['product_name'];
						$productFlags		= explode(',',$product['level_flag']);
						$monthlyPrice		= $product['monthly_price'];
						$annualPrice		= $product['annual_price'];
						$productDesc		= $product['product_desc'];
						$commitment			= $product['commitment'];
						$maxTrackRecords	= $product['track_records'];
						$trackRecordHistory	= $product['track_records_history'];
						$forumAccess		= $product['forum_access'];
						$productIcon		= $product['icon'];
						$display			= $product['display'];
						$supportLevel		= $product['support_level'];
						
						#pricing
						switch(true){
							
							#princing if within the first 15 days of trial
							case $trial30 == true:
								
								$monthlyRate	= number_format(($monthlyPrice * $first15promo),2,'.',',');
								$annualRate		= number_format(($annualPrice * $first15promo),2,'.',',');
								$lowestCost		= $monthlyRate;
								
							break;
							
							#princing if within the last 15 days of trial
							case $trial15 == true:
								
								$monthlyRate	= number_format(($monthlyPrice * $second15promo),2,'.',',');
								$annualRate		= number_format(($annualPrice * $second15promo),2,'.',',');
								$lowestCost		= $monthlyRate;
								
							break;
							
							#default db set pricing
							default:
								$monthlyRate	= number_format(($monthlyPrice),2,'.',',');
								$annualRate		= number_format(($annualPrice),2,'.',',');
								$lowestCost		= $monthlyRate;
							break;
								
						}
						
						#check to see if it is free
						if($monthlyPrice == '0'){
							#label as free
							$lowestCost = "Free";
						}else{
							#add currency
							$lowestCost = '$'.$lowestCost.' / Month';	
						}
						
						#set cart variables						
						$cartMonth			= $monthlyRate;
						$cartAnnual			= $annualRate;
						
						#product info bar
						if($subProduct == $productID){
							$barColor = 'background:#39B3D7 !important;color:#ffffff;';
							$barButton	= '<button class="btn btn-xs btn-default">Your Current Membership Level</button>';	
 						}else{
							$barColor = '';
                                                        //$barButton      = '<button class="btn btn-xs btn-info">'.$lowestCoste.'</button>';
 							$barButton	= '<button class="btn btn-xs btn-info">'.$annualRate.' / Year</button>';
						}
						
												
						?>
						<div class="panel panel-default">
                                                        <div class="panel-heading accordion-fix">
								<h4 class="panel-title">
									<a class="accordion-toggle active" data-toggle="collapse" data-parent="#memberships_parent" href="#product_<?php echo $productID;?>" style="height:40px;<?php echo $barColor;?>">
	
										<i class="<?php echo $productIcon;?>"></i> <?php echo $productName;?>
										<span class="accordion-icons accordion-expand"></span><span style="float:right;display:inline;padding-right:10px;"><?php //echo $barButton;?></span>
									</a>
								</h4>
	
                                                        </div>
                                                      <div class="panel-heading accordion-fix">
							<div id="product_<?php echo $productID;?>" class="panel-collapse collapse">
								<div class="panel-body">
	
	
	
									<div class="row">
										<div class="col-md-12">
											<h4><strong>Pricing</strong><?php echo (in_array("free", $productFlags) ? "&nbsp;&nbsp;<small>(Choose Monthly or Annual Billing)</small>" : ""); ?></h4>
											<?php
											//If free do not show buttons
											if($monthlyPrice != 0){
												
												//if($subProduct == '99' && $productID != '99'){
													
												//}else{
												?>
				
												<!--<h5><a href="#cart" data-backdrop="static" data-toggle="modal" class="btn btn-default" onclick="cart('add','<?php echo $productID;?>', 'month', '<?php echo $monthlyRate;?>');">$<?php echo $monthlyRate;?><?php echo ($inTrial == true ? "*" : ""); ?> / Month</a>&nbsp;&nbsp;-->
												<h5><a href="#cart" data-backdrop="static" data-toggle="modal" class="btn btn-info" onclick="cart('add','<?php echo $productID;?>', 'year', '<?php echo $annualRate;?>');">$<?php echo $annualRate;?><?php //echo ($inTrial == true ? "*" : ""); ?> / Year</a><?php //echo ($inTrial == true ? "<br /><br />&nbsp;&nbsp;<small>*For First Year, Regular Price: $".$monthlyPrice."/Month Or $".$annualPrice."/Year. View full promotional details below.</small>" : ""); ?></h5>
											<?php 
												//}
												
											}else{ ?>
												<h4>Free</h4>
											<?php } ?>
											<hr />
	
											<h5><strong>Description</strong></h5>
											<ul>
												<?php if($maxTrackRecords != NULL){?><li><?php echo $maxTrackRecords;?> <?php echo ($product['track_records'] > 1 ? "Concurrent Track Records" : "Track Record"); ?></li><?php } ?>
												<?php if($trackRecordHistory != NULL){?><li><?php echo $trackRecordHistory;?></li><?php } ?>
                                                <?php if($supportLevel != NULL){?><li><?php echo $supportLevel;?></li><?php } ?>
												<?php /*?><li><?php echo $product['forum_access'];?></li><?php */?>
												<?php if($product['commitment'] != NULL){?><li><?php echo $product['commitment'];?><?php } ?>
											</ul>
											
											<p><?php echo $product['product_desc'];?></p>
											<hr  />
										</div>
									</div><!--row-->
	
									<?php 
									//dont display this info if there is no cost
									if(!in_array("free",$productFlags) == true ){?>
									<div class="row">
										<div class="col-md-12">
											<!--
											<h5><strong>Monthly vs. Annual</strong></h5>
											<p>By subscribing to a Plus or Pro Membership, you are agreeing to a one year (1) subscription period.</p>
											<p>With your membership purchase you have the opportunity to pay for your full annual membership at a discounted price vs. monthly billing. If you pay the annual fee for your membership in full, you will save <?php echo $costSavings;?>!</p>
											<hr />-->
											
											<?php if($trial30 == true || $trial15 == true){?>
											<h5><strong>Trial Promos</strong></h5>
											<p>If you sign up within the first 15 days of your trial period, you will receive Two (2) Additional Months Free with your first year's membership! If you sign up within the last 15 days of your trial period, you will receive One (1) Month Free with your first year's membership! This is reflected differently depending whether you choose monthly payments or a single annual payment. To help, we have provided a table below to show you your cost savings!</p>
											
											
                                            <?php
											#calculate savings
											
											#regular
											$showAnnualSavings = (($monthlyPrice * 12) - $annualPrice);
											
											#first 15
											$showFirst15MonthlyRate 	= number_format(($monthlyPrice * $first15promo),2,'.',',');
											$showFirst15MonthlySavings 	= number_format((($monthlyPrice * 12) - ($showFirst15MonthlyRate * 12)),2,'.',',');
											$showFirst15AnnualRate 		= number_format(($annualPrice * $first15promo),2,'.',',');
											$showFirst15AnnualSavings 	= number_format((($monthlyPrice * 12) - $showFirst15AnnualRate),2,'.',',');
											
											#second 15
											$showLast15MonthlyRate 		= number_format(($monthlyPrice * $second15promo),2,'.',',');
											$showLast15MonthlySavings 	= number_format((($monthlyPrice * 12) - ($showLast15MonthlyRate * 12)),2,'.',',');
											$showLast15AnnualRate 		= number_format(($annualPrice * $second15promo),2,'.',',');
											$showLast15AnnualSavings 	= number_format((($monthlyPrice * 12) - $showLast15AnnualRate),2,'.',',');
											?>
                                            
											<table class="table table-hover table-striped ">
												<tr>
													<th style="border-right: 1px solid #333333;border-top:none;"></th>
													<th colspan="2" style="text-align:center;background:#D9EDF7;border:1px solid #333333;">Regular Pricing</th>
													<th colspan="2" style="text-align:center;background:#D9EDF7;border:1px solid #333333;border-left:none;border-right:none;">Signup First 15 Days of Trial</th>
													<th colspan="2" style="text-align:center;background:#D9EDF7;border:1px solid #333333;">Signup Last 15 Days of Trial</th>
												</tr>
												<tr>
													<th style="border-right: 1px solid #333333;background:#FFFFFF;border-top:none;"></th>
													<th style="border-left:1px solid #333333;border-right:1px solid #333333;border-bottom:1px solid #333333;">Rate</th>
													<th style="border-right:1px solid #333333;border-bottom:1px Solid #333333;">Annual Savings</th>
													<th style="border-right:1px solid #333333;border-bottom:1px solid #333333;">Rate</th>
													<th style="border-right:1px solid #333333;border-bottom:1px Solid #333333;">Annual Savings</th>
													<th style="border-right:1px solid #333333;border-bottom:1px solid #333333;">Rate</th>
													<th style="border-right:1px solid #333333;border-bottom:1px Solid #333333;">Annual Savings</th>
												</tr>
												<tr>
													<td style="border: 1px solid #333333;border-right:none;"><strong>Monthly Subscription</strong></td>
													<td style="border-left:1px solid #333333;border-bottom:1px solid #333333;border-right:1px solid #333333;">		$<?php echo $monthlyPrice;?></td>
													<td style="border-right:1px solid #333333;border-bottom:1px solid #333333;">	$0.00</td>
													<td style="border-bottom:1px solid #333333;border-right:1px solid #333333;">	$<?php echo $showFirst15MonthlyRate;?></td>
													<td style="border-right:1px solid #333333;border-bottom:1px solid #333333;">	$<?php echo $showFirst15MonthlySavings;?></td>
													<td style="border-bottom:1px solid #333333;border-right:1px solid #333333;">	$<?php echo $showLast15MonthlyRate;?></td>
													<td style="border-right:1px solid #333333;border-bottom:1px solid #333333;">	$<?php echo $showLast15MonthlySavings;?></td>
												</tr>
												<tr>
													<td style="border: 1px solid #333333;border-right:none;"><strong>Annual Subscription</strong></td>
													<td style="border-left:1px solid #333333;border-bottom:1px solid #333333;border-right:1px solid #333333;">		$<?php echo $annualPrice;?></td>
													<td style="border-right:1px solid #333333;border-bottom:1px solid #333333;">	$<?php echo $showAnnualSavings;?></td>
													<td style="border-bottom:1px solid #333333;border-right:1px solid #333333;">	$<?php echo $showFirst15AnnualRate;?></td>
													<td style="border-right:1px solid #333333;border-bottom:1px solid #333333;">	$<?php echo $showFirst15AnnualSavings;?></td>
													<td style="border-bottom:1px solid #333333;border-right:1px solid #333333;">	$<?php echo $showLast15AnnualRate;?></td>
													<td style="border-right:1px solid #333333;border-bottom:1px solid #333333;">	$<?php echo $showLast15AnnualSavings;?></td>
												</tr>
												
												
											</table>
											
											<hr  />
											<?php } ?>
	
										</div>
									</div><!--row-->
									<?php } ?>
									
									<?php 
									//check if in trial
									
									#product info bar
									if($subProduct == '99' && $productID == '1' || $subProduct == '0' && $productID == '1'){
										?>
                                        <div class="payment-btns" style="margin-top:20px;">
                                            <a href="javascript:void(0);" class="btn btn-success">You have <?php echo $daysLeftRound;?> Days Left in Your Trial</a>
                                        </div>
                                        <?php
									}elseif($productID == '1'){
										
										if($productID == $subProduct){
											?>
                                            <div class="payment-btns" style="margin-top:20px;">
                                                <a href="javascript:void(0);" class="btn btn-success">You are a <?php echo $productName;?> Member</a>
                                            </div>
                                            <?php
										}else{
											?>
                                            <div class="payment-btns" style="margin-top:20px;">
                                                 <a href="#cart" data-backdrop="static" data-toggle="modal" class="btn btn-warning" onclick="deletePayment('<?php echo $pay['uid'];?>');">Downgrade To Basic</a>
                                            </div>
                                            <?php	
										}
										
									}elseif($subProduct == $productID){
										?>
                                        <div class="payment-btns" style="margin-top:20px;">
                                            <a href="javascript:void(0);" class="btn btn-success">You are a <?php echo $productName;?> Member</a>
                                        </div>
                                        <?php
									}else{
										?>
                                        <div class="payment-btns" style="margin-top:20px;">
                                            <h4>Change Membership</h4>
                                            <!--<a href="#cart" data-backdrop="static" data-toggle="modal" class="btn btn-default" onclick="cart('add','<?php echo $productID;?>', 'month', '<?php echo $cartMonth;?>');">$<?php echo $monthlyRate;?> / Month</a>&nbsp;-->
                                            <a href="#cart" data-backdrop="static" data-toggle="modal" class="btn btn-info" onclick="cart('add','<?php echo $productID;?>', 'year', '<?php echo $cartAnnual;?>');">$<?php echo $annualRate;?> / Year</a><!--<br /><small>Choose Monthly or Annual Billing</small>-->
                                        </div>
                                        <?php
									}
										
									
									?>
									
									
									
								</div>
							</div>
                                                <?php


                                        }



                                        ?>
						</div><!--panel-->

                    <?php
					//only show upgrades if member has a free account
//					if($subProduct == '1' || $subProduct == '99' || $subProduct == '0'){
?>

<?php if(1 == 2){ // Never show it anymore...?>

                        <h3>Upgrades For Basic Membership</h3>
                        <div class="note note-info" style="margin-top:10px;">

                        <p>As a Basic Member you can choose to purchase a la carte upgrades. If you want to just have access to MyTrackRecord.com Public Page or perhaps add an additional fund to your portfolio, this is the most economical option for you. However, be sure and take a look at our paid memberships as all of these upgrades are included with a Plus Membership or Pro Membership.</p>
                        </div>
                        <?php
                        $query = "
                            SELECT *
                            FROM ".$_SESSION['products_table']."
                            WHERE active='1' AND product_type='product'
                        ";
                        try {
                            $rsGetProducts = $mLink->prepare($query);
                            $aValues = array();
                            // Prepared query - for error logging and debugging
                            $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                            $rsGetProducts->execute($aValues);
                        }
                        catch(PDOException $error){
                            file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                        }
                        while($product = $rsGetProducts->fetch(PDO::FETCH_ASSOC)){

                            if($product['monthly_price'] == '0.00'){
                                $productRate = 'Free';
                            }else{
                                $productRate = $product['monthly_price'];
                            }

                            ?>
                            <div class="panel panel-default">
                                <div class="panel-heading accordion-fix">
                                    <h4 class="panel-title">
                                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#memberships_parent" href="#product_<?php echo $product['product_id'];?>" style="height:40px;">

                                            <i class="<?php echo $product['icon'];?>"></i> <?php echo $product['product_name'];?>
                                            <span class="accordion-icons accordion-expand"></span><span style="float:right;display:inline;padding-right:10px;"><button class="btn btn-xs btn-info">$<?php echo $productRate;?> / Month</button></span>
                                        </a>
                                    </h4>

                                </div>
                                <div id="product_<?php echo $product['product_id'];?>" class="panel-collapse collapse">
                                    <div class="panel-body">

                                    	<div class="row">
                                            <div class="col-md-12">
                                                <h4><strong>Pricing</strong></h4>
                                                <?php
												if($product['product_id'] == '8'){
													//product is fund history extension
													?>
                                                    <div class="form-group">
                                                    	<label><strong>Select Funds To Extend</strong></label>
                                                        <div class="checkbox-list" id="upgradeFunds">
                                                        	<?php
															$aFunds = get_fund_symbols($mLink, $memberID, 'funds');
															foreach($aFunds as $listFundID=>$listFundSymbol){
																?>
                                                                <label><input type="checkbox" name="<?php echo $listFundID.'_'.$listFundSymbol;?>" class="getExtendFunds" value="<?php echo $listFundID.'|'.$listFundSymbol;?>"/> <?php echo $listFundSymbol;?></label>
                                                                <?php
															}
															?>

                                                        </div><!--checkbox-list-->
                                                    </div><!--form-group-->
                                                    <?php
												}
												?>
                                                <h5><a href="#cart" data-backdrop="static" data-toggle="modal" class="btn btn-info" onclick="cart('add','<?php echo $product['product_id'];?>', 'month', '<?php echo $productRate;?>');">$<?php echo $productRate;?> / Month</a><br /><small>Add To Cart</small></h5>

                                                <hr />

                                                <h5><strong>Description</strong></h5>

                                                <p><?php echo $product['product_desc'];?></p>
                                            </div><!--col-md-12-->
                                		</div><!--row-->


                                	</div><!--panel-body-->
                                </div><!--panel-collapse-->
                            </div><!--panel-->
                            <?php
                        }
						?>
                        <?php
					}//if flag = free
					?>


                    



                </div><!--panel-group accordion-->
                
            </div><!--col-md-6-->
            <?php } #end if($subChange == false)?>
            
        </div><!--row-->
        
        
        
        <?php /*?><div class="portlet" id="go-to-advanced">
            <div class="portlet-title">
            <div class="caption"><i class="icon-reorder"></i>Change Membership Level</div>
                <div class="tools">
                    <a href="javascript:;" class="collapse"></a>
                </div>
            </div><!--portlet-title-->
            <div class="portlet-body">
            
            	<div class="row">
                	<div class="col-md-3">
            			
                    </div><!--col-md-3-->
                    
                    <div class="col-md-3">
            			
                    </div><!--col-md-3-->
                    
                    <div class="col-md-3">
            			
                    </div><!--col-md-3-->
                    
                    <div class="col-md-3">
            			
                    </div><!--col-md-3-->
                </div><!--row-->
                
            </div><!--portlet-body-->
		</div><!--portlet--><?php */?>
        
</div>        
        
        
        
        
