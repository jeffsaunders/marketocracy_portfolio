<?php
$query = "
	SELECT *
	FROM ".$_SESSION['class_table']."
	WHERE active='1' AND class_id=:class_id
";

try{
	$rsClass = $mLink->prepare($query);
	$aValues = array(
		':class_id' 	=> $classID
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsClass->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$class = $rsClass->fetch(PDO::FETCH_ASSOC);
	
$className		= $class['class_name'];
$startDate		= $class['start_date'];
$endDate		= $class['end_date'];
$classDesc		= $class['class_desc'];
$classID		= $class['class_id'];
$aListStudents	= explode('|', $class['student_ids']);

$graphStartDate = date('Ymd', $startDate);

foreach($aListStudents as $key=>$sFundID){
	$aFundIDs[$key] = "'".$sFundID."'";
}

$studentFundIDs = implode(',',$aFundIDs);
$professor		= get_member($mLink, $class['teacher_id'], 'full name');

if($aListStudents[0] == ''){
	$aListStudents = '';
}


foreach($aListStudents as $key=>$sFundID){
	
	$aFundID = explode('-',$sFundID);
	
	$studentID = $aFundID[0];
	
	
	//get basic fund info from db
	$query = "
		SELECT *
		FROM ".$_SESSION['fund_table']."
		WHERE fund_id=:fund_id
	";
	try{
		$rsFund = $mLink->prepare($query);
		$aValues = array(
			':fund_id'	=> $sFundID
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsFund->execute($aValues);
	}
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	$fund = $rsFund->fetch(PDO::FETCH_ASSOC);
	
	//set vars
	//$sFundID 		= $fund['fund_id'];
	$fundName		= $fund['fund_name'];
	$fundSymbol		= $fund['fund_symbol'];
	$aMember 		= get_member($mLink, $studentID, 'all');
	$aLivePrice		= get_funds($mLink, $sFundID, 'lp', 'livePrice');
	$aAgg			= get_funds($mLink, $sFundID, 'aggAll', 'agg');
	
	if($aLivePrice['compCash'] == '0' && $aLivePrice['compDiv25'] == '0' && $aLivePrice['compDiv10'] == '0' && $aLivePrice['compMargin'] == '0'){
		$compliant 	= '<span class="label label-success">Yes</span>';	
	}else{
		$compliant 	= '<span class="label label-danger">No</span>';	
	}
	
	$cashValue 		= $aLivePrice['cashValue'];
	$totalValue		= $aLivePrice['totalValue'];
	$percentCash	= number_format((($cashValue / $totalValue) * 100), 2);
	
	//get gains
	$query = "
		SELECT totalShares, gains
		FROM ".$_SESSION['fund_stratification_basic_table']."
		WHERE fund_id=:fund_id
		ORDER BY totalShares ASC
	";
	try{
	$rsGains = $mLink->prepare($query);
		$aValues = array(
			':fund_id' 	=> $sFundID
			
		);
		$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
		$rsGains->execute($aValues);
	}
	
	catch(PDOException $error){
		// Log any error
		file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
	}
	
	$aGains = array();
	$gainCnt = 0;
	while($gains = $rsGains->fetch(PDO::FETCH_ASSOC)){
		$gainCnt++;
		$aGains[$sFundID][$gainCnt] = $gains['gains'];
	}
	
	$posGainCnt = 0;
	$negGainCnt = 0;
	
	$posGainTotal = 0;
	$negGainTotal = 0;
	
	$positionCnt = 0;
	
	foreach($aGains[$sFundID] as $key=>$gain){
		
		$positionCnt++;
		
		if($gain < 0){
			$negGainCnt++;	
			$negGainTotal = $negGainTotal + $gain;
		}elseif($gain > 0){
			$posGainCnt++;
			$posGainTotal = $posGainTotal + $gain;	
		}
	}
	
	$winningPercent = ($posGainCnt / $positionCnt) * 100;
	$gainLossRatio = $posGainTotal / abs($negGainTotal);
	
	$nGain = $posGainTotal / $posGainCnt;
	$dLoss = $negGainTotal / $negGainCnt;
	
	$avgGainLoss = $nGain / abs($dLoss);
	
	$aStudents[$studentID] = array(
		'firstName'			=> $aMember['firstName'],
		'lastName'			=> $aMember['lastName'],
		'fullName'			=> $aMember['fullName'],
		'username'			=> $aMember['username'],
		'fundID'			=> $sFundID,
		'fundSymbol'		=> $fundSymbol,
		'fundName'			=> $fundName,
		'compliant'			=> $compliant,
		'percentCash'		=> $percentCash,
		'cashValue'			=> $cashValue,
		'totalValue'		=> $totalValue,
		'nav'				=> $aLivePrice['nav'],
		'positionCnt'		=> $positionCnt,
		'posGains'			=> $posGainCnt,
		'winLossRatio'		=> $winningPercent,
		'avgGainLoss'		=> $avgGainLoss,
		'returnLastWeek'	=> $aAgg['returnLastWeek'],
		'currentReturn'		=> $aAgg['currentReturn'],
		'className'			=> $className,
		'professor'			=> $professor
	);
	
	$aTest[] = array(
		'name'	=>$aMember['fullName'],
		'c1'	=> $fundSymbol,
		'c2'	=> $fundName,
		'c3'	=> $compliant,
		'c4'	=> number_format($percentCash,2,'.',',').'%',
		'c5'	=> '$'.number_format($cashValue,2,'.',','),
		'c6'	=> '$'.number_format($aLivePrice['nav'],2,'.',','),
		'c7'	=> number_format($winningPercent,2,'.',',').'%',
		'c8'	=> number_format($avgGainLoss,2,'.',','),
		'c9'	=> (number_format($aAgg['returnLastWeek'],2,'.',',') * 1).'%',
		'c10'	=> (number_format($aAgg['currentReturn'],2,'.',',') * 1).'%',
		'use'	=> 1
	);
}
$json = json_encode($aTest);
$_SESSION['csvData'] = $aStudents;
?>
        <!-- BEGIN CREATE CATEGORY MODAL-->
        <div class="modal fade" id="create-forum" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog">
             <div class="modal-content">
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Create New Class</span></h4>
                </div>
                
                <form action="" method="post" name="create-forum" class="create-forum">
                    <div class="modal-body">
                        	<div id="createForumUserError"></div>
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="control-label">Class Name* <span id="forum_title-help"></span></label>
                                    <input type="text" class="form-control" name="forum_title" id="forum_title" />
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Menu Label* <span id="forum_title-help"></span></label>
                                    <input type="text" class="form-control" name="forum_title" id="forum_title" />
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Class Description* <span id="forum_desc-help"></span></label>
                                    <textarea class="form-control" name="forum_desc" id="forum_desc" row="5" style="resize:vertical;"></textarea>
                                </div>
                                
                            </div><!--form-body-->
                        
                    </div><!--modal-body-->
                    
                    <div class="modal-footer">
                    	<input type="submit" value="Create Forum" class="btn btn-info" id="submit-forum" style="display:none;"/>
                       	<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    </div>
                
                </form><!--create-topic-->
             </div>
             <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- END CREATE CATEGORY MODAL-->
        
        <!-- BEGIN PAGE CONTENT-->
        <div class="row">
            <div class="col-md-12">
             	
                
                
                <!-- BEGIN FORUMS TABLE-->
                <div class="portlet" id="ledger-entries">
                    <div class="portlet-title">
                        <div class="caption"><i class="icon-comments"></i>All Students</div>
                        <div class="actions">
                            <div class="btn-group" id="column-view">
                                <a class="btn btn-info dropdown-toggle" href="#" data-toggle="dropdown">
                                Columns
                                <i class="icon-angle-down"></i>
                                </a>
                                <div id="example1_column_toggler" class="dropdown-menu hold-on-click dropdown-checkboxes pull-right">
                                    <label><input type="checkbox" checked data-column="1">Fund Symbol</label>
                                    <label><input type="checkbox" checked data-column="2">Fund Name</label>
                                    <label><input type="checkbox" checked data-column="3">Compliant</label>
                                    <label><input type="checkbox" checked data-column="4">% Cash</label>
                                    <label><input type="checkbox" checked data-column="5">Cash Value</label>
                                    <label><input type="checkbox" checked data-column="6">NAV</label>
                                    <label><input type="checkbox" checked data-column="7">Win Loss Ratio</label>
                                    <label><input type="checkbox" checked data-column="8">Avg Gain Loss</label>
                                    <label><input type="checkbox" checked data-column="9">Return Last Week</label>
                                    <label><input type="checkbox" checked data-column="10">Current Return</label>
                                    
                                </div><!--ledger-table_colum_toggler-->
                       		</div><!--button-group-->
                            	
                                <button class="btn btn-success btn-md" onclick="getCSV();"><i class="icon-download"></i> Download CSV</button>
                        </div><!--actions-->
                    </div>
                    <div class="portlet-body">
                    		<table cellpadding="0" cellspacing="0" border="0"  class="table table-striped table-bordered table-hover table-full-width" id="example1">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                    	<th>Fund Symbol</th>
                                    	<th>Fund Name</th>
                                    	<th>Compliant</th>
                                    	<th>% Cash</th>
                                   	 	<th>Cash Value</th>
                                    	<th>NAV</th>
                                    	<th>Win Loss Ratio</th>
                                    	<th>Avg Gain Loss</th>
                                    	<th>Return Last Week</th>
                                    	<th>Current Return</th>
                                        <th>Include</th>
                                        <?php /*?><th>Remove</th><?php */?>
                                    </tr>
                                </thead>
                                <tfoot>
                                   <?php /*?><tr id='sum'>
                                        <th>Sum</th>
                                        <th class='c1'></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                    </tr><?php */?>
                                    <tr id='avg' style="border-top:5px solid #0CF;">
                                        <th>Avg</th>
                                        <th class='c1'></th>
                                        <th class='c2'></th>
                                        <th class='c3'></th>
                                        <th class='c4'></th>
                                        <th class='c5'></th>
                                        <th class='c6'></th>
                                        <th class='c7'></th>
                                        <th class='c8'></th>
                                        <th class='c9'></th>
                                        <th class='c10'></th>
                                        <th></th>
                                        <?php /*?><th></th><?php */?>
                                    </tr>
                                </tfoot>
                            </table>
                            
                            
                            
                            
                           
                            <?php
							//echo ' <pre>';
							//$aTest = array(array('name'=>"John",'c1'=>12,'c2'=>10,'c3'=>5, 'use'=>1),array('name'=>"John",'c1'=>12,'c2'=>10,'c3'=>5, 'use'=>1));
							
							//print_r($aTest);
							
							
							
							
							//print_r($json);
							/*print_r($aListStudents);
							print_r($aGains);
							print_r($aStudents);*/
							
							//echo '</pre>';
							?>
                            
                           <a href="?page=13-01-00-002&class=<?php echo $classID;?>">Production Table</a>
                           
                    </div><!--portlet-body-->
                </div><!--end portlet-->
                <!-- END FORUM TABLE-->
                
                <!-- BEGIN FORUMS TABLE-->
                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption"><i class="icon-comments"></i>Student Price History</div>
                        
                    </div>
                    <div class="portlet-body">
                    	<div class="load-class-data"></div>
                	</div><!--portlet-body-->
                </div><!--end portlet-->
                <!-- END FORUM TABLE-->
               
                
            </div><!--end column-->
        </div><!--row-->
        