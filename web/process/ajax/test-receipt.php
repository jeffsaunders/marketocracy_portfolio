<?php

//Start Session
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

// Load Crypto Functions
require_once("../../../secure/crypto.php");

// Load System Wide Functions
require_once("../../includes/system-functions.php");

$_SESSION['base_url'] = 'portfolio.marketocracy.com/';

$order_invoiceNumber 		= '7754-1449785875';
$customerProfileID			= '38448282';
$customerPaymentProfileID	= '34955119';
$cartInfo['product_id']		= '3';
$tax_amount					= '46.71';
$discounts					= '2~365|5~30';
$transaction_amount			= '612.90';
$unitPrice					= '566.19';
$billTimestamp				= time();
$CIMtransID					= '2246489776';
$CIMAuthCode				= 'VKZ7R4';
$term						= 'Monthly';

//Get Billing info
$query = "
	SELECT cp.*, pp.*
	FROM ".$_SESSION['payment_profiles_table']." AS pp
	INNER JOIN ".$_SESSION['customer_profiles_table']." AS cp ON cp.customerProfileID=pp.customerProfileID
	WHERE customerPaymentProfileID=:paymentID
";
try{
	$rsGetBilling = $mLink->prepare($query);
	$aValues = array(
		':paymentID'	=> $customerPaymentProfileID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	//$aErrors[] = $preparedQuery;
	//echo $preparedQuery;
	$rsGetBilling->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}
$billing	= $rsGetBilling->fetch(PDO::FETCH_ASSOC);

$billToEmail		= $billing['receiptEmail'];
$billToCardType		= $billing['cardType'];
$billToLast4		= $billing['cardLastFour'];
$billToFirstName	= $billing['billTo_firstName'];
$billToLastName		= $billing['billTo_lastName'];
$billToAddress		= $billing['billTo_address'];
$billToCity			= $billing['billTo_city'];
$billToState		= $billing['billTo_state'];
$billToZip			= $billing['billTo_zip'];
$billToCountry		= $billing['billTo_country'];
$billToPhone		= formatPhone($billing['billTo_phone']);

//Get Product Info

//Get Discount Info
$aDiscounts = explode('|', $discounts);

$showDiscountRows = '';

//print_r($aDiscounts);

foreach($aDiscounts as $key=>$discount){
	
	$aDiscount 			= explode('~', $discount);
	
	$discountID 		= $aDiscount[0];
	$discountDuration	= $aDiscount[1];
	
	//echo $discountID;
	
	$query = "
		SELECT *
		FROM ".$_SESSION['discounts_table']."
		WHERE discount_id=:discount_id
	";
	try{
		$rsDiscount = $mLink->prepare($query);
		$aValues = array(
			':discount_id'	=> $discountID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		//$aErrors[] = $preparedQuery;
		//echo $preparedQuery;
		$rsDiscount->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	echo $preparedQuery;
	
	$discountInfo	= $rsDiscount->fetch(PDO::FETCH_ASSOC);
	echo $error;
	$discountName	= $discountInfo['name'];
	$discountDesc	= $discountInfo['desc'];
	$discountCode	= $discountInfo['discount_code'];
	$discountAmount	= $discountInfo['amount'];
	$discountType	= $discountInfo['discount_type'];
	
	switch($discountType){
		case 'percent':
			$discountIcon = $discountAmount.'% OFF';	
		break;
		
		case 'dollar':
			$discountIcon = '$'.$discountAmount. 'OFF';
		break;	
	}
	
	$showDiscountRows .= '
		<tr>
			<td><h3 style="color:#9EB85C;font-size:16px;margin:0px;padding:0px;">'.$discountName.'<br /><small style="color:#666666;">'.$discountDesc.'<br />Discount: '.$discountCode.'</small></h3></td>
			<td valign="top"><strong>'.$discountIcon.'</strong></td>
		</tr>
		<tr>
			<td colspan="2" style="padding:0px 10px 0px 10px;;margin:0px;"><hr style="margin:0px;" /></td>
		</tr>
	';
		
}

?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Windows-1252">
<title>'.$emailTitle.'</title>

</head>

<body style="background-color:#4d4f4e; margin:0px; padding:0px;">

<table width="600" align="center" border="0" cellpadding="0" cellspacing="0" style="margin:0px auto;" bgcolor="#ffffff">
    <tr bgcolor="#00bdee">
		<td colspan="4" align="left" width="600" style="background-image:url('http://<?php echo $_SESSION['base_url'];?>includes/email/images/system-email/header-system.png'); background-repeat:no-repeat;">
		<!--[if gte mso 9]>
		<img src="http://'. $_SESSION['base_url'].'includes/email/images/system-email/header-system.png" width="600" height="45" border="0" alt="" />
		<![endif]-->
		<!--[if !mso]><!-- -->
		<img src="http://<?php echo $_SESSION['base_url'];?>includes/email/images/system-email/spacer.gif" width="600" height="45" border="0" alt="Marketocracy" />
		<!--<![endif]-->
		</td>
	</tr>
    <tr bgcolor="#00bdee">
    	<td width="15"><!--[if !mso]><!-- -->
		<img src="http://<?php echo $_SESSION['base_url'];?>includes/email/images/system-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
        
        <td><h2 style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; margin:0px 0px 3px 0px; padding:0px;">Portfolio Membership Receipt</h2></td>
        
        <td align="right"><h2 style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; margin:0px 0px 3px 0px; padding:0px;"><?php echo date("F j, Y");?></h2></td>
        
        <td width="15"><!--[if !mso]><!-- -->
		<img src="http://<?php echo $_SESSION['base_url'];?>includes/email/images/system-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
    </tr>
    <tr>
    	<td colspan="4">
        <!--[if !mso]><!-- -->
		<img src="http://<?php echo $_SESSION['base_url'];?>includes/email/images/system-email/spacer.gif" width="600" height="20" border="0" alt="Marketocracy" />
		<!--<![endif]-->
        </td>
    </tr>
    <tr>
    	<td width="15"><!--[if !mso]><!-- -->
		<img src="http://<?php echo $_SESSION['base_url'];?>includes/email/images/system-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
        <td valign="top" colspan="2" bgcolor="#ffffff" style="font-family:Arial, Helvetica, sans-serif; font-size:16px; color:#666666; line-height:20px;">
            
            <h2 style="font-family:Arial, Helvetica, sans-serif; font-size:18px; color:#242527; margin:0px 0px 10px 0px; padding:0px;">Invoice Number: <strong><?php echo $order_invoiceNumber;?></strong></h2>
            
            
            <table border="0" cellpadding="10" cellspacing="0" width="570" style="border:1px solid #DDDDDD;border-radius:3px;" align="left;">
                <tr bgcolor="#F5F5F5">
                    <td colspan="2"><h3 style="color:#222222;font-size:16px;margin:0px;padding:0px;">Order Information</h3></td>
                </tr>
               
                <tr>
                    <td width="420"><h3 style="margin:0px;padding:0px;">Items</h3></td>
                    <td width="150"><h3 style="margin:0px;padding:0px;">Price</h3></td>
                </tr>
                <tr>
                	<td colspan="2" style="padding:0px 10px 0px 10px;;margin:0px;"><hr style="margin:0px;" /></td>
                </tr>
                <tr style="border-bottom:1px solid #000000;">
                	<td><h3 style="color:#5BC0DE;font-size:16px;margin:0px;padding:0px;">MyTrackRecord.com Public Website<br /><small style="color:#666666;">Renewal Date: 01/11/2016</small></h3></td>
                    <td valign="top"><strong>$10.00</strong></td>
                </tr>
                <tr>
                	<td colspan="2" style="padding:0px 10px 0px 10px;;margin:0px;"><hr style="margin:0px;" /></td>
                </tr>
                <tr>
                	<td><h3 style="color:#5BC0DE;font-size:16px;margin:0px;padding:0px;">Add Fund for: MFF<br /><small style="color:#666666;">Renewal Date: 01/11/2016</small></h3></td>
                    <td valign="top"><strong>$15.00</strong></td>
                </tr>
                <tr>
                	<td colspan="2" style="padding:0px 10px 0px 10px;;margin:0px;"><hr style="margin:0px;" /></td>
                </tr>
                <tr>
                	<td><h3 style="color:#5BC0DE;font-size:16px;margin:0px;padding:0px;">Community Forums Access<br /><small style="color:#666666;">Renewal Date: 01/11/2016</small></h3></td>
                    <td valign="top"><strong>$10.00</strong></td>
                </tr>
                <tr>
                	<td colspan="2" style="padding:0px 10px 0px 10px;;margin:0px;"><hr style="margin:0px;" /></td>
                </tr>
                <?php echo $showDiscountRows;?>
                <tr>
                	<td align="right"><h3 style="font-size:16px;margin:0px;padding:0px;">Subtotal (3 items):</h3></td>
                    <td><strong>$35.00</strong></td>
                </tr>
                <tr>
                	<td align="right"><h3 style="font-size:14px;margin:0px;padding:0px;font-weight:300;">Discounts:</h3></td>
                    <td><span style="color:#9EB85C;">- $6.65</strong></td>
                </tr>
                <tr>
                	<td align="right"><h3 style="font-size:16px;margin:0px;padding:0px;">Texas Sales Tax:</h3></td>
                    <td><strong>$2.34</strong></td>
                </tr>
                <tr>
                	<td colspan="2" style="padding:0px 10px 0px 10px;;margin:0px;"><hr style="margin:0px;" /></td>
                </tr>
                <tr>
                	<td align="right"><h3 style="font-size:16px;margin:0px;padding:0px;">Order Total:</h3></td>
                    <td><strong>$30.69</strong></td>
                </tr>
            </table>
            <br />
            
            <table border="0" cellpadding="10" cellspacing="0" width="570" style="border:1px solid #DDDDDD;border-radius:3px;" align="left;">
                <tr bgcolor="#F5F5F5">
                    <td colspan="2"><h3 style="color:#222222;font-size:16px;margin:0px;padding:0px;">Billing Information</h3></td>
                </tr>
               
                <tr>
                    <td colspan="2">
                        <strong><?php echo $billToFirstName.' '.$billToLastName;?><br /></strong>
                        <?php echo $billToAddress;?><br />
                        <?php echo $billToCity.', '.$billToState.' '.$billToZip;?> | <?php echo $billToCountry;?><br />
                        <?php echo $billToEmail;?><br />
                        <?php echo $billToPhone;?>
                    
                    </td>
                </tr>
            </table>
            <br />
            
            <table border="0" cellpadding="10" cellspacing="0" width="570" style="border:1px solid #DDDDDD;border-radius:3px;" align="left;">
                <tr bgcolor="#F5F5F5">
                    <td colspan="2"><h3 style="color:#222222;font-size:16px;margin:0px;padding:0px;">Payment Information</h3></td>
                </tr>
               
                <tr>
                    <td><strong>Date/Time:</td>
                    <td><?php echo date('m/d/Y h:i:s A', $billTimestamp);?></td>
                </tr>
                <tr>
                    <td><strong>Transaction ID:</td>
                    <td><?php echo $CIMtransID;?></td>
                </tr>
                <tr>
                    <td><strong>Payment Method:</td>
                    <td><?php echo $billToCardType;?> xxxxx<?php echo $billToLast4;?></td>
                </tr>
                <tr>
                    <td><strong>Auth Code:</td>
                    <td><?php echo $CIMAuthCode;?></td>
                </tr>
            </table>
            
			
			
            <br />
			<img src="http://<?php echo $_SESSION['base_url'];?>includes/email/tracking.php?track=true[OPEN_CODE]" alt="" /><!--[TRACK_ID]-->
        </td>
        <td width="15"><!--[if !mso]><!-- -->
		<img src="http://<?php echo $_SESSION['base_url'];?>includes/email/images/system-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
		<!--<![endif]--></td>
    </tr>
    <tr bgcolor="#242527">
    	<td colspan="4">
        <!--[if !mso]><!-- -->
		<img src="http://<?php echo $_SESSION['base_url'];?>includes/email/images/system-email/spacer.gif" width="600" height="5" border="0" alt="Marketocracy" />
		<!--<![endif]-->
        </td>
    </tr>
    <tr bgcolor="#242527">
        <td width="15"><!--[if !mso]><!-- -->
            <img src="http://<?php echo $_SESSION['base_url'];?>includes/email/images/system-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
            <!--<![endif]--></td>
        <td width="570" colspan="2"  valign="top" colspan="3" style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; text-align:center;">
            &copy; 2001-<?php echo date("Y");?> <a href="http://marketocracy.com" target="_blank" style="font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#ffffff; text-decoration:none;">Marketocracy</a>, Inc. All rights reserved.<br>1208 West Magnolia Ave &middot; Suite 236 &middot; Fort Worth, TX 76104
<br><br>
        </td>
        <td width="15"><!--[if !mso]><!-- -->
            <img src="http://<?php echo $_SESSION['base_url'];?>includes/email/images/system-email/spacer.gif" width="15" border="0" alt="Marketocracy" />
            <!--<![endif]--></td>
    </tr>

</table><!--main-wrapper-->

</body>
</html>

