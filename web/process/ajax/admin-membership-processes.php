<?php
/*
Marketocracy Inc. | Beta Development
Admin Managers

Supporting files:
	AJAX		- THIS DOCUMENT process/ajax/admin-managers-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/admin-managers.js.php
	HTML		- includes/pages/admin-managers.php
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
	
	//+-----------------------------------------------------------------------------------------------------------------------+
	//													Load subscription List
	//+-----------------------------------------------------------------------------------------------------------------------+
	case 'load-sub-list':
		
		$aMemberIDs = explode(',',$_REQUEST['ids']);
		$listName 	= $_REQUEST['list-name'];
		//echo $_REQUEST['ids'];
		$aProducts	= get_products($mLink);
		
		?>
        <div class="row" style="margin-bottom:10px;">
        	<div class="col-md-4">
            
            	<a href="process/ajax/admin-membership-processes.php?process=get-csv&list-name=<?php echo $listName;?>&ids=<?php echo $_REQUEST['ids'];?>"  class="btn btn-success"><i class="icon-download"></i> Export List to CSV</a>
            
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="manager-table">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Current Subscription</th>
                                <th>Future Subscription</th>
                                <th>Date</th>
                                <th>Invoice ID</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach($aMemberIDs as $key=>$memberID){
								
								$aMemberInfo 	= get_member($mLink, $memberID, 'all');
								$aSubInfo		= get_subscription($mLink, $memberID);
								
								$currentProduct	= $aProducts[$aSubInfo['product_id']]['alt_product_name_2'];
								$newProduct		= $aProducts[$aSubInfo['new_product_id']]['alt_product_name_2'];
								
								$lastInvoiceID	= $aSubInfo['last_invoice_id'];
								$aLastInvoiceID	= explode('-',$lastInvoiceID);
								$signUpDate		= $aLastInvoiceID[1];
								
								?>
                                <tr>
                                	<td><?php echo $aMemberInfo['firstName'].' '.$aMemberInfo['lastName'];?></td>
                                    <td><a href="/?page=20-00-00-003&member=<?php echo $memberID;?>" target="_blank"><?php echo $aMemberInfo['username'];?> | <?php echo $memberID;?></a></td>
                                    <td><?php echo $aMemberInfo['email'];?></td>
                                    <td><?php echo $currentProduct;?></td>
                                    <td><?php echo $newProduct;?></td>
                                    <td><?php echo date('Y-m-d h:i:s a', $signUpDate);?></td>
                                    <td><?php echo $lastInvoiceID;?></td>
                                    <td></td>
                                </tr>	
                                <?php
							}                         
                            ?>  
                            
                        </tbody>
                    </table>
                 
                    
                </div><!--table-responsive-->    
                
            </div><!--col-->
        </div><!--row-->
        
        
        <?php
		
		
		/*echo '<pre>';
		print_r($aSubInfo);
		print_r($aMemberIDs);
		echo '</pre>';*/
		
	break;
	
	case 'get-csv':
		
		$aCSV 		= array();
		$aMemberIDs = explode(',',$_REQUEST['ids']);
		$listName 	= $_REQUEST['list-name'];
		//echo $_REQUEST['ids'];
		$aProducts	= get_products($mLink);
		
		$aCSV[] = array(
			'Member ID',
			'First Name',
			'Last Name',
			'Username',
			'Email',
			'Current Subscription',
			'Future Subscription',
			'Date Subscribed',
			'Invoice ID',
			'Nickname',
			'Note'
		);
		
		foreach($aMemberIDs as $key=>$memberID){
								
			$aMemberInfo 	= get_member($mLink, $memberID, 'all');
			$aSubInfo		= get_subscription($mLink, $memberID);
			
			$currentProduct	= $aProducts[$aSubInfo['product_id']]['alt_product_name_2'];
			$newProduct		= $aProducts[$aSubInfo['new_product_id']]['alt_product_name_2'];
			
			$lastInvoiceID	= $aSubInfo['last_invoice_id'];
			$aLastInvoiceID	= explode('-',$lastInvoiceID);
			$signUpDate		= $aLastInvoiceID[1];
			
			$notes = "Current Subscription: ".$currentProduct." | Future Subscription: ".$newProduct;
			
			$aCSV[] = array(
				$memberID,
				$aMemberInfo['firstName'],
				$aMemberInfo['lastName'],
				$aMemberInfo['username'],
				$aMemberInfo['email'],
				$currentProduct,
				$newProduct,
				date('Y-m-d h:i:s a', $signUpDate),
				$lastInvoiceID,
				$aMemberInfo['username'],
				$notes
			);
			
		}
		
		//print_r($aCSv);
		
		$listName = str_replace(' ', '-', $listName);
		
		query_to_csv($aCSV, $listName."-List-".date('Y.m.d-h.i.s').".csv", true);
		
		
	break;
	
	
		
}

?>