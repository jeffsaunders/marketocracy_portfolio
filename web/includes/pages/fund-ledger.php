<?php
/*
Marketocracy Inc. | Beta Development
Fund Ledger 

Supporting files:
	AJAX		- process/ajax/fund-ledger-processes.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/fund-ledger.js.php
	Javascript  - js/fund-ledger.js <- table scripts
	HTML		- THIS DOCUMENT includes/pages/fund-ledger.php
*/

$debug = $_REQUEST['debug'];



//START CUSTOM DATE RANGE
if(isset($_POST['submit'])){
	$startDay 	= $_REQUEST['start_day'];
	$startMonth = $_REQUEST['start_month'];
	$startYear 	= $_REQUEST['start_year'];
	
	$endDay 	= $_REQUEST['end_day'];
	$endMonth 	= $_REQUEST['end_month'];
	$endYear 	= $_REQUEST['end_year'];
	
	$startDate = ''.$startYear.'-'.$startMonth.'-'.$startDay.'';
	$endDate = ''.$endYear.'-'.$endMonth.'-'.$endDay.'';
}

//END CUSTOM DATE RANGE


//START | Get Inception Date /OR earliest Record
$query = "
	SELECT date
	FROM members_fund_pricing as one
	WHERE fund_id=:fund_id AND date=(
		SELECT MIN(two.date)
		FROM members_fund_pricing AS two
		WHERE two.fund_id=:fund_id)
	ORDER BY one.date
";

try{
	$rsInception = $mLink->prepare($query);
	$aValues = array(
		':fund_id'	=> $fundID
		
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsInception->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$inception = $rsInception->fetch(PDO::FETCH_ASSOC);

$inceptionDate = $inception['date'];

$unixIncept 	= mktime(6,0,0,substr($inceptionDate, 4, 2),substr($inceptionDate, 6, 4),substr($inceptionDate, 0, 4));
$unixIncept30	= strtotime('+30 days', $unixIncept);

$inceptionDate = ''.substr($inceptionDate, 0, 4).'-'.substr($inceptionDate, 4, 2).'-'.substr($inceptionDate, 6, 4).'';
//END | Get Inception Date /OR earliest Record



//Get current month Date Range
//$firstDay =  date('Y-m-d', strtotime('first day of this month'));
$firstDay = date('Y-m-d', strtotime('-30 days'));
$yesterday =  date('Y-m-d', strtotime('yesterday'));

/*echo $firstDay;
echo '|';
echo $yesterday;*/



//Get Date Range from URL
if(!isset($startDay)){
	$startDate = $_REQUEST['startDate'];
}
if(!isset($endDate)){
	$endDate = $_REQUEST['endDate'];
}



// Check to see if date range is set in URL
if(isset($startDate) && isset($endDate)){
	$dateRange = (createDateRangeArray($startDate,$endDate));
	
	//date breakdown
	$startDay = substr($startDate, 8, 2);
	$startMonth = substr($startDate, 5, 2);
	$startYear = substr($startDate, 0, 4);
	
	$endDay = substr($endDate, 8, 2);
	$endMonth = substr($endDate, 5, 2);
	$endYear = substr($endDate, 0, 4);
	
	$previous = date('Y-m-d', strtotime('-30 days', strtotime($startDate)));
	$next = date('Y-m-d', strtotime('+30 days', strtotime($endDate)));
	
	//echo $endDate;
	// Start Pagination Controls
	$pageCntr .= '<div style="text-align:center;">';
	
	$pageCntr .= '
		<form action="?page=03-01-00-004&fund='.$fundID.'" method="post" name="incept-form" style="display:inline;">
			<input type="hidden" name="start_month" value="'.date('m',$unixIncept).'" />
			<input type="hidden" name="start_day" value="'.date('d',$unixIncept).'" />
			<input type="hidden" name="start_year" value="'.date('Y',$unixIncept).'" />
			
			<input type="hidden" name="end_month" value="'.date('m',$unixIncept30).'" />
			<input type="hidden" name="end_day" value="'.date('d',$unixIncept30).'" />
			<input type="hidden" name="end_year" value="'.date('Y',$unixIncept30).'" />
			<input type="submit" value="Inception" name="submit" class="btn btn-info btn-sm" />
		</form>
	';
	
	$pageCntr .= '<a href="?page=03-01-00-004&fund='.$fundID.'&startDate='.$previous.'&endDate='.$startDate.'" class="btn btn-sm btn-info"><i class="icon-arrow-left"></i> Previous 30 Days</a>
	<a href="?page=03-01-00-004&fund='.$fundID.'&startDate='.$endDate.'&endDate='.$next.'" class="btn btn-sm btn-info">Next 30 Days <i class="icon-arrow-right"></i></a>';
	
	$pageCntr .= '</div>';
	
}else{
	//Build array of date range
	$dateRange = (createDateRangeArray($firstDay,$yesterday));
	
	//date breakdown
	$startDay = substr($firstDay, 8, 2);
	$startMonth = substr($firstDay, 5, 2);
	$startYear = substr($firstDay, 0, 4);
	
	if($startMonth == "1"){
		$lastMonth = "12";
	}else{
		$lastMonth = $startMonth -1;	
	}
	
	$lastMonthDate = ''.$startYear.'-'.$lastMonth.'-'.$startDay.'';
	$lastMonthStart = date('Y-m-01', strtotime($lastMonthDate));
	
	
	
	$endDay = substr($yesterday, 8, 2);
	$endMonth = substr($yesterday, 5, 2);
	$endYear = substr($yesterday, 0, 4);
	
	$previous = date('Y-m-d', strtotime('-30 days', strtotime($firstDay)));
	$next = date('Y-m-d', strtotime('+30 days', strtotime($firstDay)));
	
	
	//echo $next;
	// Start Pagination Controls
	$pageCntr .= '<div style="text-align:center;">';
	
	$pageCntr .= '
		<form action="?page=03-01-00-004&fund='.$fundID.'" method="post" name="incept-form" style="display:inline;">
			<input type="hidden" name="start_month" value="'.date('m',$unixIncept).'" />
			<input type="hidden" name="start_day" value="'.date('d',$unixIncept).'" />
			<input type="hidden" name="start_year" value="'.date('Y',$unixIncept).'" />
			
			<input type="hidden" name="end_month" value="'.date('m',$unixIncept30).'" />
			<input type="hidden" name="end_day" value="'.date('d',$unixIncept30).'" />
			<input type="hidden" name="end_year" value="'.date('Y',$unixIncept30).'" />
			<input type="submit" value="Inception" name="submit" class="btn btn-info btn-sm" />
		</form>
	';
	
	$pageCntr .= '<a href="?page=03-01-00-004&fund='.$fundID.'&startDate='.$previous.'&endDate='.$firstDay.'" class="btn btn-sm btn-info"><i class="icon-arrow-left"></i> Previous 30 Days</a>';
	
	$pageCntr .= '</div>';
	
	
}

//echo $previous;
//echo $lastMonthStart;

$startDate = date('Ymd', mktime(0, 0, 0, $startMonth, $startDay, $startYear));
$endDate = date('Ymd', mktime(0, 0, 0, $endMonth, $endDay, $endYear));



//echo $startDate;echo '|';echo $endDate;
?>
		
        <div class="modal fade" id="ledger-details2" tabindex="-1" role="basic" aria-hidden="true">
            <div class="modal-dialog modal-full">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title"><button type="button" class="btn btn-warning" data-dismiss="modal"><i class="icon-arrow-left"></i></button> Ledger Detail</h4>
                    </div>
                    <div class="modal-body" id="load-ledger-details2">
                    	
                        
                    </div>
                    <div class="modal-footer">
                     <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div><!-- .modal-content -->
            </div><!-- .modal-dialog -->
        </div> <!-- .modal -->
        <!--END LEDGER DETAIL 2 MODAL-->
        
        <!-- BEGIN LEDGER DETAILS MODAL-->         
        <div class="modal fade" id="ledger-details" tabindex="-1" role="basic" aria-hidden="true">
            <div class="modal-dialog modal-wide">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title">Ledger Detail</h4>
                    </div>
                    <div class="modal-body">
                    	
                        <div class="load-ledger-info"></div>
                    	
                        
                        <table id="fund-zone-table" class="table table-bordered table-striped table-condensed flip-content load-ledger-positions">
                            <thead class="flip-content">
                                <tr>
                                	<th class="fzt-organge" colspan="8">POSITIONS ON JANUARAY 06, 2014</th>
                                </tr>
                                <tr class="fzt-blue">
                                    <th class="fzt-aleft">SYMBOL</th>
                                    <th class="hidden-xs">NAME</th>
                                    <th>PRICE</th>
                                    <th>PORTION OF FUND</th>
                                    <th>SHARES HELD</th>
                                    <th>DIVIDENDS PAID</th>
                                    <th>VALUE</th>
                                    <th>ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                            	
                              
                                
                            </tbody>
                        </table>
                            
                        <table id="fund-zone-table" class="table table-bordered table-striped table-condensed flip-content load-ledger-trades">
                            <thead class="flip-content">
                                <tr>
                                    <th class="fzt-organge" colspan="7">TRADES ON JANUARAY 06, 2014</th>
                                </tr>
                                <tr class="fzt-blue">
                                    <th>TYPE</th>
                                    <th class="fzt-aleft">SYMBOL</th>
                                    <th>QUANTITY</th>
                                    <th>PRICE</th>
                                    <th>NET</th>
                                    <th>COMMISSION</th>
                                    <th>SEC FEE</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                     <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div><!-- .modal-content -->
            </div><!-- .modal-dialog -->
        </div> <!-- .modal -->
        <!--END LEDGER DETAIL MODAL-->
        
        <!-- BEGIN PAGE CONTENT-->
        <div class="row">
            <div class="col-md-12">
            	
                <!-- BEGIN FUND INFO -->
                <?php include('includes/portlets/fund-live-info.php');?>
                <!-- END FUND INFO -->
                <?php //echo $inceptionDate.' | '.$unixIncept.' | '.$unixIncept30;?>
                <!-- BEGIN RECENT ORDERS TABLE-->
                <div class="portlet" id="ledger-entries">
                    <div class="portlet-title">
                        <div class="caption">
                        	<i class="icon-globe"></i>Ledger Entries:
                        	<form action="?page=03-01-00-004&fund=<?php echo $fundID;?>" method="post" style="display:inline;" name="dateRange">
                            	
                                <select name="start_month" class="custom-select">
                                	<option value="<?php echo $startMonth;?>"><?php echo $startMonth; ?></option>
                                    <option disabled>--</option>
                                	<?php
									echo date_list($mLink, 'month', $startMonth);
									?>
                                </select>
                                /
                                <select name="start_day" class="custom-select">
                                	<option value="<?php echo $startDay;?>"><?php echo $startDay; ?></option>
                                    <option disabled>--</option>
                                	<?php
									echo date_list($mLink, 'day', $startDay);
									?>
                                </select>
                                /
                                <select name="start_year" class="custom-select">
                                	<option value="<?php echo $startYear;?>"><?php echo $startYear; ?></option>
                                    <option disabled>----</option>
                                	<?php
									echo date_list($mLink, 'year', NULL,$startYear, false);
									?>
                                </select>
                                &nbsp;-&nbsp;
                                <select name="end_month" class="custom-select">
                                	<option value="<?php echo $endMonth;?>"><?php echo $endMonth; ?></option>
                                    <option disabled>--</option>
                                	<?php
									echo date_list($mLink, 'month', $endMonth);
									?>
                                </select>
                                /
                                <select name="end_day" class="custom-select">
                                	<option value="<?php echo $endDay;?>"><?php echo $endDay; ?></option>
                                    <option disabled>--</option>
                                	<?php
									echo date_list($mLink, 'day', $endDay);
									?>
                                </select>
                                /
                                <select name="end_year" class="custom-select">
                                	<option value="<?php echo $endYear;?>"><?php echo $endYear; ?></option>
                                    <option disabled>----</option>
                                	<?php
									echo date_list($mLink, 'year', NULL,$endYear, false);
									?>
                                </select>
                                
                                <input type="submit" value="Change Date Range" name="submit" class="btn btn-xs btn-info" style="margin-top:-4px;"/>
                            </form>
                        </div>
                        <div class="actions">
                            <div class="btn-group" id="column-view">
                            	
                                <a class="btn btn-info dropdown-toggle" href="#" data-toggle="dropdown">
                                Columns
                                <i class="icon-angle-down"></i>
                                </a>
                                <div id="ledger-table_column_toggler" class="dropdown-menu hold-on-click dropdown-checkboxes pull-right">
                                    <label><input type="checkbox" checked data-column="2">Start Cash</label>
                                    <label><input type="checkbox" checked data-column="3">In/Out Flow</label>
                                    <label><input type="checkbox" checked data-column="4">Interest</label>
                                    <label><input type="checkbox" checked data-column="5">Dividends</label>
                                    <label><input type="checkbox" checked data-column="6">Management Fees</label>
                                    <label><input type="checkbox" checked data-column="7">Trade Balance</label>
                                    <label><input type="checkbox" checked data-column="8">End Cash</label>
                                    <label><input type="checkbox" checked data-column="9">Stock Value</label>
                                    <label><input type="checkbox" checked data-column="10">Total Value</label>
                                    <label><input type="checkbox" checked data-column="11">Shares</label>
                                    <label><input type="checkbox" checked data-column="12">Price</label>
                                    <label><input type="checkbox" checked data-column="13">Compliance</label>
                                </div><!--ledger-table_colum_toggler-->
                       		</div><!--button-group-->
                            
                            <a href="process/ajax/fund-ledger-processes.php?process=csv&fund=<?php echo $fundID;?>" class="btn btn-success"><i class="icon-download"></i> Download CSV</a>
                        </div><!--actions-->
					</div><!--portlet-title-->
                    <div class="portlet-body">
                    	<?php echo $pageCntr; ?>
                        <table class="table table-striped table-bordered table-hover table-full-width" id="ledger-table">
                            <thead>
                                <tr bgcolor="#FFFFFF">
                                	
                                    <th class="fzt-aleft">DATE</th>
                                    <th>START CASH</th>
                                    <th>IN/OUT FLOW</th>
                                    <th id="column-sort">INTEREST</th>
                                    <th>DIVIDENDS</th>
                                    <th>MANAGEMENT FEES</th>
                                    <th>TRADE BALANCE</th>
                                    <th>END CASH</th>
                                    <th>STOCK VALUE</th>
                                    <th>TOTAL VALUE</th>
                                    <th>SHARES</th>
                                    <th>PRICE</th>
                                    <th>COMPLIANCE</th>
                                    <th>ACTION</th>
                                    <th style="display:none;"></th>
                                </tr>
                            </thead>
                            <tbody>
                            	<?php
																
								/*$query = "
									SELECT *
									FROM members_fund_pricing as one
									WHERE fund_id=:fund_id AND timestamp=(
										SELECT MAX(two.timestamp)
										FROM members_fund_pricing AS two
										WHERE two.fund_id=:fund_id AND two.date=one.date)
									ORDER BY one.date
								";*/
								
								$query = '
									SELECT *
									FROM '.$_SESSION['fund_pricing_table'].'
									WHERE fund_id=:fund_id AND date<=:end_date AND date>=:start_date
									GROUP BY date
									ORDER BY date DESC, timestamp DESC
								';
								
								try{
									$rsLedger = $mLink->prepare($query);
									$aValues = array(
										':fund_id'		=> $fundID,
										':start_date'	=> $startDate,
										':end_date'		=> $endDate,
										
									);
									$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
									$rsLedger->execute($aValues);
								}
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}
								
								//create CSV header
								$aLedgerCSV[] = array(
									'index','Date','In/Out Flow','Interest','Dividends','Fees','Buys','Sells','Cash Value','Stock Value','Total Value','Shares','Price','Compliance'
								);
								
								$ledgerCnt = 0;
								
								while($ledger = $rsLedger->fetch(PDO::FETCH_ASSOC)){
									
									$uid = $ledger['uid'];
									
									if(number_format($ledger['dividends'], 2, '.', ',') != "0.00"){
										$dividends = '<span class="label label-success">$'.number_format($ledger['dividends'], 2, '.', ',').'</span>';	
									}else{
										$dividends = '$'.number_format($ledger['dividends'], 2, '.', ',').'';	
									}
									
									if(number_format($ledger['tradeValue'], 2, '.',',') != "0.00"){
										$trades = '<span class="label label-info">$'.number_format($ledger['tradeValue'], 2, '.', ',').'</span>';	
									}else{
										$trades = '$'.number_format($ledger['tradeValue'], 2, '.', ',').'';	
									}
									
									$ledgerCnt++;
									
									//moved to process file
									/*$aLedgerCSV[] = array(
										$ledgerCnt,
										date('m/d/y',$ledger['unix_date']),
										'$'.number_format($ledger['netFlow'], 2, '.', ','),
										'$'.number_format($ledger['interest'], 2, '.', ','),
										'$'.number_format($ledger['dividends'], 2, '.', ','),
										'$'.number_format($ledger['fees'], 2, '.', ','),
										'$'.number_format($ledger['tradeBuys'], 2,'.',','),
										'$'.number_format($ledger['tradeSells'], 2,'.',','),
										'$'.number_format($ledger['cashValue'], 2, '.', ','),
										'$'.number_format($ledger['positionsValue'], 2, '.', ','),
										'$'.number_format($ledger['totalValue'], 2, '.', ','),
										number_format($ledger['shares'], 0, '.', ','),
										'$'.number_format($ledger['price'], 2, '.', ','),
										($ledger['secCompliance'] == '1' ? "Yes" : "No")
									);*/
									
									if($debug == '1'){
										$loadLedgerCall 	= "loadDetails('".$fundID."','".$ledger['unix_date']."','".$uid."');";	
										$loadLedgerModal	= '#ledger-details2';
									}else{
										$loadLedgerCall 	= "loadLedgerDetails('".$fundID."','".$ledger['unix_date']."');";
										$loadLedgerModal	= '#ledger-details';
									}
									
									?>
									<tr id="<?php echo $uid;?>">
										
										<td><a href="<?php echo $loadLedgerModal;?>" onClick="<?php echo $loadLedgerCall;?>" data-toggle="modal"><?php echo date('m/d/y',$ledger['unix_date']);?></a></td>
										<td><span class="label label-info">$<?php echo number_format($ledger['startCash'], 2, '.', ',');?></span></td>
										<td>$<?php echo number_format($ledger['netFlow'], 2, '.', ',');?></td>
										<td>$<?php echo number_format($ledger['interest'], 2, '.', ',');?></td>
										<td><?php echo $dividends;?></td>
										<td><span class="label label-danger">$<?php echo number_format($ledger['fees'], 2, '.', ',');?></span></td>
										<td><?php echo $trades;?></td>
										<td>$<?php echo number_format($ledger['cashValue'], 2, '.', ',');?></td>
										<td><span class="label label-default">$<?php echo number_format($ledger['positionsValue'], 2, '.', ',');?></span></td>
										<td><span class="label label-warning">$<?php echo number_format($ledger['totalValue'], 2, '.', ',');?></span></td>
										<td><?php echo number_format($ledger['shares'], 0, '.', ',');?></td>
										<td><span class="label label-success">$<?php echo number_format($ledger['price'], 2, '.', ',');?></span></td>
										<td><?php echo  ($ledger['secCompliance'] == '1' ? "Yes" : "No");?></td>
										<td><a href="<?php echo $loadLedgerModal;?>" class="btn btn-info btn-xs" onClick="<?php echo $loadLedgerCall;?>" data-toggle="modal">Details</a></td>
                                        <td style="display:none;"><?php echo $ledger['unix_date'];?></td>
									</tr>
									<?php
								}
								
								
								?>
								
                               
                            </tbody>
                        </table>
                        <?php echo $pageCntr; ?>
                    </div><!--portlet-boody-->
                </div><!--end portlet-->
               <!-- END RECENT ORDER TABLE-->
               
            </div><!--col-->
        </div><!--row-->
        