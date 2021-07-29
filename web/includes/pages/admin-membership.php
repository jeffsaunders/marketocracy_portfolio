<?php
/*
Marketocracy Inc. | Beta Development
Admin Manager Composite Data

Supporting files:
	AJAX		- process/ajax/admin-managers-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/admin-managers.js.php
	HTML		- THIS DOCUMENT includes/pages/admin-managers.php
*/

#get product info and build structure array
$aProducts			= get_products($mLink);
$aSubs 				= array();
$totalSubs			= array();
$totalTrialSubs		= array();
$totalNonPaidSubs	= array();
$totalPaidSubs		= array();
$totalGIPSsubs		= array();
$totalMembershipRev	= 0;

//$aDebug['products'] = $aProducts;
$profTD = 0;
$annualRev = 0;

$query = "
	SELECT *
	FROM merchant_transaction_history
";
try{
	$rsTrans = $mLink->prepare($query);
	$aValues = array();
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsTrans->execute($aValues);
}

catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

while($trans = $rsTrans->fetch(PDO::FETCH_ASSOC)){
	
	
	
	$aTrans[$trans['member_id']][$trans['uid']] = $trans;
	
	$profTD += $trans['transaction_amount'];

	
}

foreach($aTrans as $memberID=>$trans){
	
	$transCnt = 0;
	
	foreach($trans as $transID=>$transInfo){
		//echo $transID;
		if($transCnt>1){
			continue;	
		}
	
		$billFreq = $transInfo['bill_frequency'];
		switch($billFreq){
			
			case 'Monthly':
				
				$annualCost = $transInfo['transaction_amount'] * 12;
				
			break;
			
			case 'Annually':
				
				$annualCost = $transInfo['transaction_amount'];
				
			break;
				
		}
		
		//echo $annualCost;
		
		$annualRev += $annualCost;
		
	}
	
}


foreach($aProducts as $productID=>$productInfo){
	
	$productType = $productInfo['product_type'];
	
	if($productType != 'product-group'){
		#build current structure
		$aSubs[$productType][$productID]['current']['count'] = 0;
		
		$aSubs[$productType][$productID]['current']['term']['Never']['count'] = 0;
		$aSubs[$productType][$productID]['current']['term']['Monthly']['count'] = 0;
		$aSubs[$productType][$productID]['current']['term']['Annually']['count'] = 0;
		
		
		
		#build future structure
		$aSubs[$productType][$productID]['future']['count'] = 0;
		
		$aSubs[$productType][$productID]['future']['term']['Never']['count'] = 0;
		$aSubs[$productType][$productID]['future']['term']['Monthly']['count'] = 0;
		$aSubs[$productType][$productID]['future']['term']['Annually']['count'] = 0;
	}
	
	
}

/*echo '<pre>';
print_r($aProducts);
print_r($aSubs);
echo '</pre>';*/







#counters
$TotalMembershipCnt			= 0;
$trialMemberCnt				= 0;
$trialPaidMemberCnt			= 0;
$nonPaidMemberCnt			= 0;
$paidMemberCnt				= 0;
$gipsMemberCnt				= 0;
$nonTrialMemberCnt  		= 0;

#trial counters
$inactiveTrialCnt			= 0;
$legacyTrialCnt				= 0;
$standardTrialCnt			= 0;
$trialConversionCnt 		= 0;
$trialConvertMembershipCnt	= 0;
$trialConvertUpgradeCnt		= 0;
$trialConvertPlus			= 0;
$trialConvertPro			= 0;
$trialConvertLegacyPro		= 0;
$trialConvertOneTR			= 0;
$trialConvertTwoTR			= 0;
$trialConvertThreeTR		= 0;
$trialConvertMTR			= 0;

$trialConvertPlusMonthCnt		= 0;
$trialConvertPlusYearCnt		= 0;
$trialConvertProMonthCnt		= 0;
$trialConvertProYearCnt			= 0;
$trialConvertLegacyProMonthCnt	= 0;
$trialConvertLegacyProYearCnt	= 0;


#membership counters
$basicMemberCnt				= 0;
$basicUpgradeCnt			= 0;
$plusMemberCnt				= 0;
$proMemberCnt				= 0;
$legacyProMemberCnt			= 0;
$gipsMemberCnt				= 0;
$upgradeOneTrCnt			= 0;
$upgradeTwoTrCnt			= 0;
$upgradeThreeTrCnt			= 0;
$upgradeMtrCnt				= 0;

$plusMonthlyCnt				= 0;
$plusYearlyCnt				= 0;
$proMonthlyCnt				= 0;
$proYearlyCnt				= 0;
$legacyProMonthlyCnt		= 0;
$legacyProYearlyCnt			= 0;

$noGoThruTrans				= 0;
$wentThruTrans				= 0;
$basicNotLoggedIn			= 0;
$basicOther					= 0;
$basicNoUpgrades			= 0;
$basicLoggedIn				= 0;
$basicLoggedInNoAction		= 0;

$notLoggedInMultiFund		= 0;
$notLoggedInSingleFund		= 0;

$aMembers					= array();



$query = "
	SELECT ms.*, m.last_login
	FROM members_subscriptions ms, members m
	WHERE ms.active='1' AND m.member_id=ms.member_id
";
try{
	$rsSubs = $mLink->prepare($query);
	$aValues = array();
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsSubs->execute($aValues);
}

catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

while($sub = $rsSubs->fetch(PDO::FETCH_ASSOC)){
	
	$aMembers[$sub['member_id']][$sub['uid']] = $sub;
	
	
	
}

foreach($aMembers as $memberID => $subscriptions){
	
	
	
	$subMemberID	= $memberID;
	$hasUpgrades		= false;
	
	#check to see if the member purchased upgrades
	foreach($subscriptions as $uid=>$sub){
		
		if($sub['product_id'] > 100){
			
			#dont reset the variable if it is already set
			if($hasUpgrades == false){
				
				$hasUpgrades = true;
			}
		}
	}
	
	
	foreach($subscriptions as $uid=>$sub){
		
		$lastLogin 		= $sub['last_login'];
	
		//	$aDebug['lastLogin'][$memberID] = $lastLogin;
		
		$hasLoggedIn	= false;
		
		if($lastLogin > 1483246800){#jan 1
			$hasLoggedIn = true;
		}
		
		$productID		= $sub['product_id'];
		$productGroup	= $aProducts[$productID]['product_group'];
		$productType	= $aProducts[$productID]['product_type'];
		$subFundID		= $sub['fund_id'];
		$transWiz		= $sub['trans_wiz'];
		
		
		
		
		#count all membership records
		if($productType == 'membership'){
			$TotalMembershipCnt++;
		}
		
		switch($productGroup){
			
			#all trial product types
			case 'trial':
				
				
				
				
				
				#run counts on different product IDs		
				switch($productID){
					
					#standard trial
					case '0':
						$standardTrialCnt++;
						$trialMemberCnt++;
						
						$aStandardTrial[$subMemberID] = $subMemberID;
					break;
					
					#legacy Trial
					case '99':				
					
						if($sub['start_timestamp'] == NULL){
							$inactiveTrialCnt++;
							
							$aInactiveTrial[$subMemberID] = $subMemberID;	
						}else{
							$legacyTrialCnt++;
							$trialMemberCnt++;
							
							$aLegacyTrial[$subMemberID] = $subMemberID;
						}
						
					break;
						
				}#end switch productID
				
				
				#check for future products
				$productIDs = $sub['new_product_id'];
				
				if($productIDs != NULL){
					
					#converted Counter
					$trialConversionCnt++;
					
					$aProductIDs = explode('|', $productIDs);
					
					//print_r($aProductIDs);
					
					foreach($aProductIDs as $key=>$newProductID){
						
						$newProductGroup = $aProducts[$newProductID]['product_group'];
						
						#increment the paid members counter as they have already paid money
						$trialPaidMemberCnt++;
						
						switch($newProductGroup){
							
							case 'paid':
								
								$trialConvertMembershipCnt++;
								
								switch($newProductID){
									
									case '2': 
										$trialConvertPlus++; 
										$aTrialConvertPlus[] = $subMemberID; 
										
										switch($sub['new_bill_frequency']){
											case 'Monthly': $trialConvertPlusMonthCnt++;break;
											case 'Annually': $trialConvertPlusYearCnt++;break;
										}
										
									break;
									
									case '3': 
									
										$trialConvertPro++; 
										$aTrialConvertPro[$subMemberID] = $subMemberID;
										
										switch($sub['new_bill_frequency']){
											case 'Monthly': $trialConvertProMonthCnt++;break;
											case 'Annually': $trialConvertProYearCnt++;break;
										}
										
									break;
									
									case '4': 
									
										$trialConvertLegacyPro++; 
										$aTrialConvertLegacyPro[$subMemberID] = $subMemberID;
										
										switch($sub['new_bill_frequency']){
											case 'Monthly': $trialConvertLegacyProMonthCnt++;break;
											case 'Annually': $trialConvertLegacyProYearCnt++;break;
										}
										
									break;
									
									case '5': 
									
										$trialConvertLegacyPro++; 
										$aTrialConvertLegacyPro[$subMemberID] = $subMemberID;
										
										switch($sub['new_bill_frequency']){
											case 'Monthly': $trialConvertLegacyProMonthCnt++;break;
											case 'Annually': $trialConvertLegacyProYearCnt++;break;
										}
										
									break;
										
								}
								
							break;
							
							case 'upgrade':
								
								$trialConvertUpgradeCnt++;
								
								switch($newProductID){
									
									case '101': $trialConvertOneTR++; $aTrialConvertOneTR[] = $subMemberID;break;
									
									case '102': $trialConvertTwoTR++; $aTrialConvertTwoTR[] = $subMemberID;break;
									
									case '103': $trialConvertThreeTR++; $aTrialConvertThreeTR[] = $subMemberID;break;
									
									case '106': $trialConvertMTR++; $aTrialConvertMTR[] = $subMemberID;break;
										
								}
								
							break;
								
						}#edn switch $newProductGroup
							
					}#foreach new product id
						
				}#if new product id is not NULL		
				
			break;
			
			case 'free':
				
				$nonPaidMemberCnt++;
					
				$aNonPaidMembers[$subMemberID] = $subMemberID;
				
				if($hasUpgrades == false){
					
				}
				
				$nonTrialMemberCnt++;
				
				switch($productID){
					case '1': 
						
						$basicMemberCnt++;
						
						if($hasUpgrades == false){
							
							$basicNoUpgrades++;
						
							#count basic members who have gone through the trans wizard
							if($transWiz != NULL && $subFundID != NULL){
								$basicLoggedIn++;
								$wentThruTrans++;
								
								$aWentThruTrans[$subMemberID] = $subMemberID;
								$aHaveLoggedIn[$subMemberID] = $subMemberID;
							}elseif($subFundID != NULL && $transWiz == NULL){#count basic members who did not need to go through trans wizard
								$basicLoggedIn++;
								$noGoThruTrans++;
								
								$aNoGoThruTrans[$subMemberID] = $subMemberID;
								$aHaveLoggedIn[$subMemberID] = $subMemberID;
							}elseif($subFundID == NULL && $transWiz == NULL){#count basic members who have not yet logged in
								
								if($hasLoggedIn == true){
									$basicLoggedIn++;
									$basicLoggedInNoAction++;
									$abasicLoggedInNoAction[$subMemberID] = $subMemberID;
								}else{
									$basicNotLoggedIn++;
									$aBasicNotLoggedIn[$subMemberID] = $subMemberID;
									
									#check for number of funds
									$query = "
										SELECT COUNT(*) AS funds
										FROM members_fund
										WHERE member_id = :member_id
										AND active = 1
									";
									try{
										$rsFunds = $mLink->prepare($query);
										$aValues = array(
											':member_id'	=> $subMemberID
										);
										$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
										$rsFunds->execute($aValues);
									}
									
									catch(PDOException $error){
										// Log any error
										file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
									}
									$funds = $rsFunds->fetch(PDO::FETCH_ASSOC);
										
									$fundCnt = $funds['funds'];
									
									if($fundCnt > 1){
										$notLoggedInMultiFund++;	
										$aNotLoggedInMultiFund[$subMemberID] = $subMemberID;
									}else{
										$notLoggedInSingleFund++;
										$aNotLoggedInSingleFund[$subMemberID] = $subMemberID;
									}
									
								}
								
								
							}else{
								$aBasicOther[$subMemberID] = $subMemberID;	
							}
							
						}
					
						
					
					break;	
				}
				
			break;
			
			case 'paid':
				
				$paidMemberCnt++;
				$aPaidMembers[$subMemberID] = $subMemberID; 
				
				$nonTrialMemberCnt++;
				
				switch($productID){
					
					case '2':
						
						$plusMemberCnt++;
						
						switch($sub['bill_frequency']){
							case 'Monthly': $plusMonthlyCnt++;break;
							case 'Annually': $plusYearlyCnt++;break;
						}
						
					break;
					
					case '3':
					
						$proMemberCnt++;
						
						switch($sub['bill_frequency']){
							case 'Monthly': $proMonthlyCnt++;break;
							case 'Annually': $proYearlyCnt++;break;
						}
						
					break;
					
					case '4':
					
						$legacyProMemberCnt++;
						
						switch($sub['bill_frequency']){
							case 'Monthly': $legacyProMonthlyCnt++;break;
							case 'Annually': $legacyProYearlyCnt++;break;
						}
						
					break;
					
					case '5':
						
						$basicMemberCnt++;
						$paidBasicMemberCnt++;
						
						
					break;
					
				}
				
			break;
			
			case 'gips':
				
				$gipsMemberCnt++;
				$nonTrialMemberCnt++;
				
				$aGipsMembers[$subMemberID] = $subMemberID;
				
			break;
			
			case 'upgrade':
			
				$paidMemberCnt++;
				
				$aPaidMembers[$subMemberID] = $subMemberID;
				
				$basicUpgradeCnt++;
				
				switch($productID){
					
					case '101': $upgradeOneTrCnt++;break;
					case '102': $upgradeTwoTrCnt++;break;
					case '103': $upgradeThreeTrCnt++;break;
					case '106': $upgradeMtrCnt++;break;	
					
				}
			
			break;		
			
		}	
	}
		
}

$aDebug['basic-other'] = $aBasicOther;

$aMembershipStats = array(
	'totalMembershipRecords'					=> array(
		'count'							=> $TotalMembershipCnt,				#Total Membership Record Count
		'membership-totals'						=> array(
			'count'						=> $TotalMembershipCnt,				#Total Membership Record Count
			'trial-members'						=> array(
				'count'					=> $trialMemberCnt,					#Trial Member Count
			),
			'non-paid-members'					=> array(
				'count'					=> $nonPaidMemberCnt,								#Non-Paid Member Count
			),
			'paid-members'						=> array(
				'count'					=> $paidMemberCnt,								#Paid Member Count
			),
			'trial-paid-members'				=> array(
				'count'					=> $trialPaidMemberCnt,								#Paid Member Count
			),
			'gips-members'						=> array(
				'count'					=> $gipsMemberCnt,								#GIPS Member Count
			),
		),
		'free-trial'							=> array(
			'count'						=> $trialMemberCnt,					#Free Trial Count
			'inactive'							=> array(
				'count'					=> $inactiveTrialCnt,				#Inactive Count
			),
			'standard'							=> array(
				'count'					=> $standardTrialCnt,				#In 30 Day Trial Count
			),
			'legacy'							=> array(
				'count'					=> $legacyTrialCnt,					#In 30 Day Trial Count
			),
			'converted'							=> array(
				'count'					=> $trialConversionCnt,				#Converted Count
				'membership'					=> array(
					'count'				=> $trialConvertMembershipCnt,		#Converted membership Count
					'plus'						=> array(
						'count'			=> $trialConvertPlus,				#Converted plus count
						'member_ids'			=> array(),
						'monthly'				=> array(
							'count'		=> $trialConvertPlusMonthCnt,
						),
						'yearly'				=> array(
							'count'		=> $trialConvertPlusYearCnt,
						),
					),
					'pro'						=> array(
						'count'			=> $trialConvertPro,				#Converted Pro Count
						'member_ids'			=> array(),
						'monthly'				=> array(
							'count'		=> $trialConvertProMonthCnt,
						),
						'yearly'				=> array(
							'count'		=> $trialConvertProYearCnt,
						),
					),
					'legacy-pro'				=> array(
						'count'			=> $trialConvertLegacyPro,			#Converted Legacy Pro Count
						'member_ids'			=> array(),
						'monthly'				=> array(
							'count'		=> $trialConvertLegacyProMonthCnt,
						),
						'yearly'				=> array(
							'count'		=> $trialConvertLegacyProYearCnt,
						),
					)
				),#membership
				'upgrades'						=> array(
					'count'				=> $trialConvertUpgradeCnt,			#upgrades count
					'one-extra-tr'				=> array(
						'count'			=> $trialConvertOneTR,				#one extra tr count
					),
					'two-extra-tr'				=> array(
						'count'			=> $trialConvertOneTR,				#two extra tr count
					),
					'three-extra-tr'			=> array(
						'count'			=> $trialConvertTwoTR,				#three extra tr count
					),
					'mtr-website'				=> array(
						'count'			=> $trialConvertMTR,				#mtr-website count
					),
				)#upgrades
				
			)#converted
			
		),#free trial
		'membership'							=> array(
			'count'						=> $nonTrialMemberCnt,				#membership count
			'basic'								=> array(
				'count'					=> $basicMemberCnt,					#basic count
				'no-upgrades'					=> array(
					'count'				=> 0,								#no upgrades count
					'member_ids'				=> array()
				),
				'one-extra-tr'					=> array(
					'count'				=> $upgradeOneTrCnt,				#one extra count
					'member_ids'				=> array()
				),
				'two-extra-tr'					=> array(
					'count'				=> $upgradeTwoTrCnt,				#two extra count
					'member_ids'				=> array()
				),
				'three-extra-tr'				=> array(
					'count'				=> $upgradeThreeTrCnt,				#three extra count
					'member_ids'				=> array()
				),
				'mtr-website'					=> array(
					'count'				=> $upgradeMtrCnt,					#mtr count
					'member_ids'				=> array()
				),
				'member_ids'					=> array()
			),
			'plus'								=> array(
				'count'					=> $plusMemberCnt,					#plus count
				'monthly'						=> array(
					'count'				=> $plusMonthlyCnt,					#plus monthly
					'member_ids'				=> array()
				),
				'yearly'						=> array(
					'count'				=> $plusYearlyCnt,					#plus yearly
					'member_ids'				=> array()
				),
				'member_ids'					=> array()
			),
			'pro'								=> array(
				'count'					=> $proMemberCnt,					#pro
				'monthly'						=> array(
					'count'				=> $proMonthlyCnt,					#pro monthly
					'member_ids'				=> array()
				),
				'yearly'						=> array(
					'count'				=> $proYearlyCnt,					#pro yearly
					'member_ids'				=> array()
				),
				'member_ids'					=> array()
			),
			'legacy-pro'						=> array(
				'count'					=> $legacyProMemberCnt,				#legacy pro
				'monthly'						=> array(
					'count'				=> $legacyProMonthlyCnt,			#legacy pro monthly
					'member_ids'				=> array()
				),
				'yearly'						=> array(
					'count'				=> $legacyProYearlyCnt,				#legacy pro yearly
					'member_ids'				=> array()
				),
				'member_ids'					=> array()
			),
			'gips-pro'							=> array(
				'count'					=> $gipsMemberCnt,					#gips pro
				'member_ids'					=> array()
			)
		)
	)#total membership
);

$aDebug['aMembershipStats'] = $aMembershipStats;

$GIPSrev = revenue($mLink, 'all');

$aDebug['gipsRev']				= $GIPSrev;
$aDebug['paid-subs'] 			= $totalPaidSubs;
$aDebug['subscriptions-array'] 	= $aSubs;
$aDebug['products-array'] 		= $aSrcProducts;



?>
   
 
<!-- BEGIN View members portlet-->
<div class="modal" id="sub-list" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-full">
     <div class="modal-content">
        <div class="modal-header">
           <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
           <h4 class="modal-title" id="update-title">Member Subscriptions</h4>
        </div>
        
        
            <div class="modal-body" id="load-sub-list">
                    
                
            </div><!--modal-body-->
            
            <div class="modal-footer">
                
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        
        
     </div>
     <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<!-- END RUN LIVE PRICE-->
         
<!-- BEGIN PAGE CONTENT-->

    
<div class="row">
    <div class="col-md-12">
        
        <div class="tabbable tabbable-custom">
            
            <?php include('includes/nav-admin-tabs.php');?>

            <div class="tab-content">
            
                <div class="tab-pane active" id="tab_1">
                	
                    <?php if($_REQUEST['debug'] == '1'){?>
                    <div class="portlet">
                        <div class="portlet-title">
                            <div class="caption">Debug
                            </div>
                            <div class="tools" id="collapse-btn">
                                <a href="javascript:;" class="collapse"></a>
                                <!--<a href="javascript:;" class="reload"></a>-->
                            </div><!--tools-->
                        </div><!--portlet-title-->
                        <div class="portlet-body">
                        
                        	<pre>
                            
                            <table>
                            	<tr>
                                	<td>Plus Members</td>
                                    <td><?php echo $plusMemberCnt;?></td>
                                    <td></td>
                                </tr>
                                <tr>
                                	<td>Pro Members</td>
                                    <td><?php echo $proMemberCnt;?></td>
                                </tr>
                                <tr>
                                	<td>Legacy Pro Members</td>
                                    <td><?php echo $legacyProMemberCnt;?></td>
                                </tr>
                                <tr>
                                	<td>One TR Members</td>
                                    <td><?php echo $upgradeOneTrCnt;?></td>
                                </tr>
                                <tr>
                                	<td>Two TR Members</td>
                                    <td><?php echo $upgradeTwoTrCnt;?></td>
                                </tr>
                                <tr>
                                	<td>Three TR Members</td>
                                    <td><?php echo $upgradeThreeTrCnt;?></td>
                                </tr>
                                <tr>
                                	<td>MTR TR Members</td>
                                    <td><?php echo $upgradeMtrCnt;?></td>
                                </tr>
                            </table>
                            
                            <?php
							print_r($aTrans);
							?>
                            </pre>
                        
                        </div><!--portlet-body-->
                    </div><!--Portlet-->
                    <?php } ?>
                    
                    <div class="portlet">
                        <div class="portlet-title">
                            <div class="caption">General Statistics
                            </div>
                            <div class="tools" id="collapse-btn">
                                <a href="javascript:;" class="collapse"></a>
                                <!--<a href="javascript:;" class="reload"></a>-->
                            </div><!--tools-->
                        </div><!--portlet-title-->
                        <div class="portlet-body" id="collapse-portlet">
                            <div class="row">
                            
                            	<div class="col-md-4">
                                	
                                    <div class="portlet">
                                        <div class="portlet-title">
                                            <div class="caption">By The Numbers
                                            </div>
                                            <div class="tools" id="collapse-btn">
                                                <a href="javascript:;" class="collapse"></a>
                                                <!--<a href="javascript:;" class="reload"></a>-->
                                            </div><!--tools-->
                                        </div><!--portlet-title-->
                                        <div class="portlet-body" id="collapse-portlet">
                                            <table class="table table-bordered table-hover">
                                               
                                               <thead>             
                                               		<tr>
                                                    	<th>Type</th>
                                                        <th>Subs</th>
                                                        <th>Action</th>
                                                    </tr>
                                               </thead>
                                                
                                                <tbody>
                                                	<tr>
                                                    	<th>Total Members</th>
                                                        <td><?php echo number_format($TotalMembershipCnt, 0, '.',',');?></td>
                                                        <td><button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#sub-list" onclick="loadSubList('<?php echo implode(',',$totalSubs);?>','Total-Membership');">View</button></td>
                                                    </tr>
                                                    <tr>
                                                    	<th>Inactive Members</th>
                                                        <td><?php echo number_format($inactiveTrialCnt, 0, '.',',');?></td>
                                                        <td><button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#sub-list" onclick="loadSubList('<?php echo implode(',',$aInactiveTrial);?>','Total-Membership');">View</button></td>
                                                    </tr>
                                                    <tr>
                                                    	<th>Active Members</th>
                                                        <td><?php echo number_format(($TotalMembershipCnt - $inactiveTrialCnt), 0, '.',',');?></td>
                                                        <td></td>
                                                    </tr>
                                                    <tr>
                                                    	<th>Trial Members</th>
                                                        <td><?php echo number_format($trialMemberCnt, 0, '.',',');?></td>
                                                        <td><button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#sub-list" onclick="loadSubList('<?php echo implode(',',$totalTrialSubs);?>','Trial-Subscriptions');">View</button></td>
                                                    </tr>
                                                    <tr>
                                                    	<th>Non-Paid Subscriptions</th>
                                                        <td><?php echo number_format($nonPaidMemberCnt, 0, '.',',');?></td>
                                                        <td><button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#sub-list" onclick="loadSubList('<?php echo implode(',',$aNonPaidMembers);?>','Non-Paid-Subscriptions');">View</button></td>
                                                    </tr>
                                                    	
                                                    <tr>
                                                    	<th>Paid Subscriptions</th>
                                                        <td><?php echo number_format(count($aPaidMembers), 0, '.',',');?></td>
                                                        <td><button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#sub-list" onclick="loadSubList('<?php echo implode(',',$aPaidMembers);?>','Paid-Subscriptions');">View</button></td>
                                                    </tr>
                                                    <tr>
                                                    	<th>Managers Pro</th>
                                                        <td><?php echo number_format($gipsMemberCnt, 0, '.',',');?></td>
                                                        <td><button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#sub-list" onclick="loadSubList('<?php echo implode(',',$totalGIPSsubs);?>','GIPS-Subscriptions');">View</button></td>
                                                    </tr>
                                                </tbody>
                                        	</table>
                                		</div><!--portlet-body-->
                    				</div><!--portlet-->
                                    
                                </div><!--col-md-4-->
                                
                                <div class="col-md-4">
                                	
                                    <div class="portlet">
                                        <div class="portlet-title">
                                            <div class="caption">By The Numbers
                                            </div>
                                            <div class="tools" id="collapse-btn">
                                                <a href="javascript:;" class="collapse"></a>
                                                <!--<a href="javascript:;" class="reload"></a>-->
                                            </div><!--tools-->
                                        </div><!--portlet-title-->
                                        <div class="portlet-body" id="collapse-portlet">
                                            <table class="table table-bordered table-hover">
                                               
                                               <thead>             
                                               		<tr>
                                                    	<th>Level</th>
                                                        <th>Revenue</th>
                                                    </tr>
                                               </thead>
                                                
                                                <tbody>
                                                	<tr>
                                                    	<th>Membership Revenue To Date</th>
                                                        <?php /*?><td></td><?php */?>
                                                        <td>$<?php echo number_format($profTD,2,'.',',');?></td>
                                                    </tr>
                                                	<tr>
                                                    	<th>Membership Annual Projected Revenue</th>
                                                        <?php /*?><td><?php echo number_format(count($totalPaidSubs), 0, '.',',');?></td><?php */?>
                                                        <td>$<?php echo number_format($annualRev,2,'.',',');?></td>
                                                    </tr>
                                                    
                                                    <tr>
                                                    	<th>Managers Revenue</th>
                                                        <?php /*?><td><?php echo number_format(count($totalGIPSsubs), 0, '.',',');?></td><?php */?>
                                                        <td>$<?php echo number_format($GIPSrev['gips-earned'],2,'.',',');?></td>
                                                    </tr>
                                                    
                                                    <?php 
														
													$totalRevMembers 	= (count($totalPaidSubs) + count($totalGIPSsubs));
													$totalSiteRev		= ($GIPSrev['gips-earned'] + $annualRev);
														
													?>
                                                    
                                                    <tr style="border-top:2px solid #000;">
                                                        <td><strong>Total Annual Revenue</strong></td>
                                                        <?php /*?><td><?php echo $totalRevMembers;?></td><?php */?>
                                                        <td>$<?php echo number_format($totalSiteRev,2,'.',',');?></td>
                                                    </tr>
                                                </tbody>
                                        	</table>
                                		</div><!--portlet-body-->
                    				</div><!--portlet-->
                                    
                                </div><!--col-md-4-->
                            
                            </div><!--row-->
                        </div><!--portlet-body-->
                    </div><!--portlet-->
                    
                    <div class="portlet">
                        <div class="portlet-title">
                            <div class="caption">Membership Statistics
                            </div>
                            <div class="tools" id="collapse-btn">
                                <a href="javascript:;" class="collapse"></a>
                                <!--<a href="javascript:;" class="reload"></a>-->
                            </div><!--tools-->
                        </div><!--portlet-title-->
                        <div class="portlet-body" id="collapse-portlet">
                            <div class="row">
                            
                            	<div class="col-md-12">
                                	
                                    <table class="tree table table-bordered table-hover">
                                    	<tr>
                                        	<th></th>
                                            <th>Desc</th>
                                            <th>Revenue</th>
                                            <th>Count</th>
                                            <th>Action</th>
                                        </tr>
                                       <?php /*?> <tr class="treegrid-root">
                                            <td style="font-size:16px;"><strong>Marketocracy Membership Records</strong></td>
                                            <td></td>
                                            <td></td>
                                            <td><?php echo number_format($TotalMembershipCnt,0,'.',',');?></td>
                                            <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                        </tr><?php */?>
                                        
                                            <tr class="treegrid-memTotal ">
                                                <td>Membership Totals</td>
                                                <td></td>
                                                <td></td>
                                                <td><?php echo number_format($TotalMembershipCnt,0,'.',',');?></td>
                                                <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                            </tr>
                                                <tr class="treegrid-trialMembers treegrid-parent-memTotal">
                                                    <td>Trial Members</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td><?php echo number_format($trialMemberCnt,0,'.',',');?></td>
                                                    <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                </tr>
                                                <tr class="treegrid-memTotal-non-paid treegrid-parent-memTotal treegrid-collapsed">
                                                    <td>Non-Paid Members (Basic)</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td><?php echo number_format($nonPaidMemberCnt,0,'.',',');?></td>
                                                    <td><button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#sub-list" onclick="loadSubList('<?php echo implode(',',$aNonPaidMembers);?>','Non-Paid Members');">View</button></td>
                                                </tr>
                                                	<tr class="treegrid-loggedin treegrid-parent-memTotal-non-paid">
                                                        <td>Have Logged In</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td><?php echo number_format($basicLoggedIn,0,'.',',');?></td>
                                                        <td><button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#sub-list" onclick="loadSubList('<?php echo implode(',',$aHaveLoggedIn);?>','Have Logged In');">View</button></td>
                                                    </tr>
                                                    	<tr class="treegrid-non-trans treegrid-parent-loggedin">
                                                            <td>Did Not Need To Go Through Trans Wizard (Single Fund)</td>
                                                            
                                                            <td>Members who have logged in, but only had one fund and did not need to go through trans wizard.</td>
                                                            <td></td>
                                                            <td><?php echo number_format($noGoThruTrans,0,'.',',');?></td>
                                                            <td><button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#sub-list" onclick="loadSubList('<?php echo implode(',',$aNoGoThruTrans);?>','Did Not Go Through Transition Wizard');">View</button></td>
                                                        </tr>
                                                        <tr class="treegrid-trans treegrid-parent-loggedin">
                                                            <td>Did Go Through Trans Wizard (Multiple Funds)</td>
                                                            
                                                            <td>Members who went through Trans Wizard to select their free fund.</td>
                                                            <td></td>
                                                            <td><?php echo number_format($wentThruTrans,0,'.',',');?></td>
                                                            <td><button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#sub-list" onclick="loadSubList('<?php echo implode(',',$aWentThruTrans);?>','Went Through Transition Wizard');">View</button></td>
                                                        </tr>
                                                        
                                                        <tr class="treegrid-logged-no-action treegrid-parent-loggedin">
                                                            <td>Logged In with No Action</td>
                                                            
                                                            <td>Members who have logged in and had more than one fund, but took no action in the transition wizard.</td>
                                                            <td></td>
                                                            <td><?php echo number_format($basicLoggedInNoAction,0,'.',',');?></td>
                                                            <td><button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#sub-list" onclick="loadSubList('<?php echo implode(',',$abasicLoggedInNoAction);?>','Did Not Go Through Transition Wizard');">View</button></td>
                                                        </tr>
                                                    <tr class="treegrid-memTotal-non-paid-not-loggedin treegrid-parent-memTotal-non-paid">
                                                        <td>Have Not Logged In</td>
                                                        <td>Have not logged into the site since Jan 1, 2017</td>
                                                        <td></td>
                                                        <td><?php echo number_format($basicNotLoggedIn,0,'.',',');?></td>
                                                        <td><button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#sub-list" onclick="loadSubList('<?php echo implode(',',$aBasicNotLoggedIn);?>','Have Not Logged In');">View</button></td>
                                                    </tr>
                                                    	<tr class="treegrid-not-logged-single treegrid-parent-memTotal-non-paid-not-loggedin">
                                                            <td>Single Fund</td>
                                                            
                                                            <td>Members who have NOT logged in and have only one fund.</td>
                                                            <td></td>
                                                            <td><?php echo number_format($notLoggedInSingleFund,0,'.',',');?></td>
                                                            <td><button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#sub-list" onclick="loadSubList('<?php echo implode(',',$aNotLoggedInSingleFund);?>','Not Logged In with Single Fund');">View</button></td>
                                                        </tr>
                                                        <tr class="treegrid-not-logged-mutli treegrid-parent-memTotal-non-paid-not-loggedin">
                                                            <td>Multiple Funds</td>
                                                            
                                                            <td>Members who have NOT logged in and have more than one fund.</td>
                                                            <td></td>
                                                            <td><?php echo number_format($notLoggedInMultiFund,0,'.',',');?></td>
                                                            <td><button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#sub-list" onclick="loadSubList('<?php echo implode(',',$aNotLoggedInMultiFund);?>','Not Logged In with Multiple Funds');">View</button></td>
                                                        </tr>
                                                <tr class="treegrid--memTotal-paid treegrid-parent-memTotal">
                                                    <td>Paid Members</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td><?php echo number_format(count($aPaidMembers),0,'.',',');?></td>
                                                    <td><button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#sub-list" onclick="loadSubList('<?php echo implode(',',$aPaidMembers);?>','PAID Members');">View</button></td>
                                                </tr>
                                                <tr class="treegrid-memTotal-GIPS treegrid-parent-memTotal">
                                                    <td>Managers Members</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td><?php echo number_format($gipsMemberCnt,0,'.',',');?></td>
                                                    <td><button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#sub-list" onclick="loadSubList('<?php echo implode(',',$aGipsMembers);?>','GIPS Members');">View</button></td>
                                                </tr>
                                        	
                                            <tr class="treegrid-Inactive ">
                                                <td>Inactive Membership</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td><?php echo number_format($inactiveTrialCnt,0,'.',',');?></td>
                                                    <td><button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#sub-list" onclick="loadSubList('<?php echo implode(',',$aInactiveTrial);?>','Inactive Free Trial');">View</button></td>
                                            </tr>
                                            	<tr class="treegrid-inactive-trial treegrid-parent-Inactive">
                                                    <td>Inactive Trial</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td><?php echo number_format($inactiveTrialCnt,0,'.',',');?></td>
                                                    <td><button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#sub-list" onclick="">View</button></td>
                                                </tr>
                                                <tr class="treegrid-inactive-basic treegrid-parent-Inactive">
                                                    <td>Inactive Basic</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td>0</td>
                                                    <td><button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#sub-list" onclick="">View</button></td>
                                                </tr>
                                                <tr class="treegrid-inactive-plus treegrid-parent-Inactive">
                                                    <td>Inactive Plus</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td>0</td>
                                                    <td><button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#sub-list" onclick="">View</button></td>
                                                </tr>
                                                <tr class="treegrid-inactive-pro treegrid-parent-Inactive">
                                                    <td>Inactive Pro</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td>0</td>
                                                    <td><button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#sub-list" onclick="">View</button></td>
                                                </tr>
                                            <tr class="treegrid-FreeTrial ">
                                                <td>Trial</td>
                                                <td></td>
                                                <td></td>
                                                <td><?php echo number_format($trialMemberCnt,0,'.',',');?></td>
                                                <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                            </tr>
                                            	
                                                <tr class="treegrid-30-day treegrid-parent-FreeTrial">
                                                    <td>Standard Trial</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td><?php echo number_format($standardTrialCnt,0,'.',',');?></td>
                                                    <td><button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#sub-list" onclick="loadSubList('<?php echo implode(',',$aStandardTrial);?>','Standard Free Trial');">View</button></td>
                                                </tr>
                                                <tr class="treegrid-30-day treegrid-parent-FreeTrial">
                                                    <td>Legacy Trial</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td><?php echo number_format($legacyTrialCnt,0,'.',',');?></td>
                                                    <td><button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#sub-list" onclick="loadSubList('<?php echo implode(',',$aLegacyTrial);?>','Legacy Free Trial');">View</button></td>
                                                </tr>
                                                <tr class="treegrid-converted treegrid-parent-FreeTrial">
                                                    <td>Converted</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td><?php echo number_format($trialConversionCnt,0,'.',',');?></td>
                                                    <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                </tr>
                                                	<tr class="treegrid-converted-membership treegrid-parent-converted">
                                                        <td>Membership</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td><?php echo number_format($trialConvertMembershipCnt,0,'.',',');?></td>
                                                        <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                    </tr>
                                                    	<tr class="treegrid-converted-membership-plus treegrid-parent-converted-membership">
                                                            <td>Plus</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td><?php echo number_format($trialConvertPlus,0,'.',',');?></td>
                                                            <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                        </tr>
                                                        	<tr class="treegrid-converted-membership-plus-month treegrid-parent-converted-membership-plus">
                                                                <td>Monthly</td>
                                                                <td></td>
                                                                <td></td>
                                                                <td><?php echo number_format($trialConvertPlusMonthCnt,0,'.',',');?></td>
                                                                <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                            </tr>
                                                            <tr class="treegrid-converted-membership-plus-year treegrid-parent-converted-membership-plus">
                                                                <td>Yearly</td>
                                                                <td></td>
                                                                <td></td>
                                                                <td><?php echo number_format($trialConvertPlusYearCnt,0,'.',',');?></td>
                                                                <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                            </tr>
                                                        <tr class="treegrid-converted-membership-pro treegrid-parent-converted-membership">
                                                            <td>Pro</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td><?php echo number_format($trialConvertPro,0,'.',',');?></td>
                                                            <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                        </tr>
                                                        <tr class="treegrid-converted-membership-legacy-pro treegrid-parent-converted-membership">
                                                            <td>Legacy Pro</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td><?php echo number_format($trialConvertLegacyPro,0,'.',',');?></td>
                                                            <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                        </tr>
                                             		<tr class="treegrid-converted-upgrades treegrid-parent-converted">
                                                        <td>Upgrades</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td><?php echo number_format($trialConvertUpgradeCnt,0,'.',',');?></td>
                                                        <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                    </tr>
                                                    	<tr class="treegrid-converted-upgrades-none treegrid-parent-converted-upgrades">
                                                            <td>No Upgrades</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td>N/A</td>
                                                            <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                        </tr>
                                                        <tr class="treegrid-converted-upgrades-one-extra treegrid-parent-converted-upgrades">
                                                            <td>One Extra TR</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td><?php echo number_format($trialConvertOneTR,0,'.',',');?></td>
                                                            <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                        </tr>
                                                        <tr class="treegrid-converted-upgrades-two-extra treegrid-parent-converted-upgrades">
                                                            <td>Two Extra TR</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td><?php echo number_format($trialConvertTwoTR,0,'.',',');?></td>
                                                            <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                        </tr>
                                                        <tr class="treegrid-converted-upgrades-three-extra treegrid-parent-converted-upgrades">
                                                            <td>Three Extra TR</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td><?php echo number_format($trialConvertThreeTR,0,'.',',');?></td>
                                                            <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                        </tr>
                                                        <tr class="treegrid-converted-upgrades-mtr-website treegrid-parent-converted-upgrades">
                                                            <td>MTR Website</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td><?php echo number_format($trialConvertMTR,0,'.',',');?></td>
                                                            <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                        </tr>
                                             <tr class="treegrid-Membership ">
                                                <td>Membership</td>
                                                <td></td>
                                                <td></td>
                                                <td><?php echo number_format($nonTrialMemberCnt,0,'.',',');?></td>
                                                <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                            </tr>
                                            	<tr class="treegrid-Membership-basic treegrid-parent-Membership">
                                                    <td>Basic</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td><?php echo number_format($basicMemberCnt,0,'.',',');?></td>
                                                    <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                </tr>
                                                	<tr class="treegrid-Membership-basic-no-upgrades treegrid-parent-Membership-basic">
                                                        <td>No Upgrades</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td><?php echo number_format($basicNoUpgrades,0,'.',',');?></td>
                                                        <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                    </tr>
                                                    <tr class="treegrid-Membership-basic-one-extra-tr treegrid-parent-Membership-basic">
                                                        <td>One Extra TR</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td><?php echo number_format($upgradeOneTrCnt,0,'.',',');?></td>
                                                        <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                    </tr>
                                                    <tr class="treegrid-Membership-basic-two-extra-tr treegrid-parent-Membership-basic">
                                                        <td>Two Extra TR</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td><?php echo number_format($upgradeTwoTrCnt,0,'.',',');?></td>
                                                        <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                    </tr>
                                                    <tr class="treegrid-Membership-basic-three-extra-tr treegrid-parent-Membership-basic">
                                                        <td>Three Extra TR</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td><?php echo number_format($upgradeThreeTrCnt,0,'.',',');?></td>
                                                        <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                    </tr>
                                                    <tr class="treegrid-Membership-basic-mtr treegrid-parent-Membership-basic">
                                                        <td>MTR Website</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td><?php echo number_format($upgradeMtrCnt,0,'.',',');?></td>
                                                        <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                    </tr>
                                                <tr class="treegrid-Membership-plus treegrid-parent-Membership">
                                                    <td>Plus</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td><?php echo number_format($plusMemberCnt,0,'.',',');?></td>
                                                    <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                </tr>
                                                	<tr class="treegrid-Membership-plus-month treegrid-parent-Membership-plus">
                                                        <td>Monthly</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td><?php echo number_format($plusMonthlyCnt,0,'.',',');?></td>
                                                        <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                    </tr>
                                                    <tr class="treegrid-Membership-plus-year treegrid-parent-Membership-plus">
                                                        <td>Yearly</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td><?php echo number_format($plusYearlyCnt,0,'.',',');?></td>
                                                        <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                    </tr>
                                                <tr class="treegrid-Membership-pro treegrid-parent-Membership">
                                                    <td>Pro</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td><?php echo number_format($proMemberCnt,0,'.',',');?></td>
                                                    <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                </tr>
                                                	<tr class="treegrid-Membership-pro-month treegrid-parent-Membership-pro">
                                                        <td>Monthly</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td><?php echo number_format($proMonthlyCnt,0,'.',',');?></td>
                                                        <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                    </tr>
                                                    <tr class="treegrid-Membership-pro-year treegrid-parent-Membership-pro">
                                                        <td>Yearly</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td><?php echo number_format($proYearlyCnt,0,'.',',');?></td>
                                                        <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                    </tr>
                                                <tr class="treegrid-Membership-legacy-pro treegrid-parent-Membership">
                                                    <td>Legacy Pro</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td><?php echo number_format($legacyProMemberCnt,0,'.',',');?></td>
                                                    <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                </tr>
                                                	<tr class="treegrid-Membership-legacy-pro-month treegrid-parent-Membership-legacy-pro">
                                                        <td>Monthly</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td><?php echo number_format($legacyProMonthlyCnt,0,'.',',');?></td>
                                                        <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                    </tr>
                                                    <tr class="treegrid-Membership-legacy-pro-year treegrid-parent-Membership-legacy-pro">
                                                        <td>Yearly</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td><?php echo number_format($legacyProYearlyCnt,0,'.',',');?></td>
                                                        <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                    </tr>
                                                <tr class="treegrid-Membership-gips-pro treegrid-parent-Membership">
                                                    <td>Managers Pro</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td><?php echo number_format($gipsMemberCnt,0,'.',',');?></td>
                                                    <td><a href="javascript:void(0);" class="btn btn-info btn-sm">View</a></td>
                                                </tr>
                                    </table>

                                    
                                </div><!--col-md-4-->
                            
                            </div><!--row-->
                        </div><!--portlet-body-->
                    </div><!--portlet-->
                
                    
                
                    
                </div><!--END TAB 1-->
                
            </div><!--tab-content-->
        </div><!--tabbable tabbable-custom-->
        
    </div><!--col-md-12-->
</div><!--row-->
<!-- END PAGE CONTENT-->    
   