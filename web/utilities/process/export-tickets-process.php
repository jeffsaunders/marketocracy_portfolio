<?php
session_start();

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

require_once("../../includes/system-functions.php");

$process = $_REQUEST['process'];

set_time_limit(3600);

function array_to_csv_download($array, $filename = "export.csv", $delimiter=";") {
    header('Content-Type: application/csv');
    header('Content-Disposition: attachement; filename="'.$filename.'";');

    // open the "output" stream
    // see http://www.php.net/manual/en/wrappers.php.php#refsect2-wrappers.php-unknown-unknown-unknown-descriptioq
    $f = fopen('php://output', 'w');

    foreach ($array as $line) {
        fputcsv($f, $line, $delimiter);
    }
}  

if($process == "1"){
	
	$ticket = $_REQUEST['ticket'];
	
	
	if($ticket != ''){
		
		//Get only master member
		$query = '
			SELECT *
			FROM '.$_SESSION['support_ticket_table'].'
			WHERE ticket_id=:ticket_id
		';
		
		try{
			$rsTickets = $mLink->prepare($query);
			$aValues = array(
				//':active' 		=> '1',
				':ticket_id'	=> $ticket
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsTickets->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}else{
		
		//Get all active member IDs
		$query = "
			SELECT *
			FROM ".$_SESSION['support_ticket_table']."
			WHERE ticket_status<>'0'
			ORDER BY ticket_id
		";
		
		try{
			$rsTickets = $mLink->prepare($query);
			$aValues = array(
				//':active' 	=> '1'
				
			);
			$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
			$rsTickets->execute($aValues);
		}
		
		catch(PDOException $error){
			// Log any error
			file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
		}
	}
	$aTickets = array();
	$cnt = 0;
	
	//loop through results and assign values to array
	while($ticket = $rsTickets->fetch(PDO::FETCH_ASSOC)){
		
		switch($ticket['ticket_type']){
			case '2': $type = 'CA';break;
			case '3': $type = 'HELP';break;
			default	: $type = 'OTHER';break;	
		}
		
		$aTickets[$ticket['ticket_id']] = array(
			'ticket_id'		=> $ticket['ticket_id'],
			'type'			=> $type,
			'stock_ticker'	=> $ticket['stock_ticker'],
			'subject'		=> $ticket['ticket_subject'],
			'desc'			=> $ticket['description'],
			'cnt'			=> $cnt
		);
		
		$cnt++;
		
	}
	
	
	$aCA 	= array();
	$aHelp 	= array();
	$aOther	= array();
	
	
	?>
    <br /><br />
    <style type="text/css">
		table td {border:1px solid #333333;}
	</style>
    <form action="process/export-tickets-process.php?process=2" method="post" class="export-csv">
   	<input type="submit" name="submit-csv" value="Download CSV" />
    <h3>Corporate Actions</h3>
    <table cellpadding="10" cellspacing="0" style="text-align:left;border:1px solid;">
    <tr>
    	<td>Row</td>
        <td>Case/Ticket Number</td>
        <td>Case Type</td>
        <td>CA Type</td>
        <td>Ticker</td>
        <td>Subject</td>
        <td>Description</td>
        <td>Style Key</td>
        <?php /*?><td>Sector Key</td><?php */?>
        <td>Process</td>
    </tr>
    <?php
	//loop through each ticket
	$aTicketIDs = array();
	$caRowCnt = 0;
	foreach($aTickets as $ticketID=>$aTicket){
		
		$aTicketIDs[] = $aTicket['ticket_id'];
		
		//sort CA's
		if($aTicket['type'] == 'CA'){
			
			
			
			if(strpos($aTicket['stock_ticker'], ',')){
				$aStocks = explode(',', $aTicket['stock_ticker']);
			}else{
				$aStocks = '';	
			}
			
			$caRowCnt++;
		
			if(isOdd($caRowCnt) == true){
				//Row is ODD
				$rowColor 		= '#DFDFDF';
				$lastCellColor 	= '#AA9FCB';
			}else{
				//Row is EVEN
				$rowColor 		= '#FFFFFF';
				$lastCellColor 	= '#E2DFEE';
			}
			
			if($aStocks == ''){
			
				$aCA[$aTicket['ticket_id']] = array(
					'ticket_id' 	=> $aTicket['ticket_id'],
					'case_type'		=> 'CA',
					'stock_ticker'	=> $aTicket['stock_ticker'],
					'subject'		=> $aTicket['subject'],
					'desc'			=> $aTicket['desc'],
					'cnt'			=> $aTicket['cnt']
				);
				
				$ticketArray = implode('|', $aCA[$aTicket['ticket_id']]);
				
				
				?>
				<input type="hidden" name="ticket-<?php echo $aTicket['ticket_id'];?>" value="<?php echo $ticketArray;?>" />
				<tr style="background:<?php echo $rowColor;?>;border:1px solid #333333;">
					<td><?php echo $caRowCnt;?></td>
					<td><?php echo $aTicket['ticket_id'];?></td>
					<td>CA</td>
					<td>
						<select name="ca-type-<?php echo $aTicket['ticket_id'];?>" onclick="checkRadio('process-<?php echo $aTicket['ticket_id'];?>');">
							<option value="style">Style Change</option>
							<option value="sector">Sector Change</option>
							<option value="other">Other</option>
						</select>
					</td>
					<td><?php echo $aTicket['stock_ticker'];?></td>
					<td><?php echo $aTicket['subject'];?></td>
					<td style="width:200px;"><?php echo $aTicket['desc'];?></td>
					<td>
						<select name="style-<?php echo $aTicket['ticket_id'];?>" onchange="checkRadio('process-<?php echo $aTicket['ticket_id'];?>');">
							<option value=''>Select Style Key</option>
							<option value="LB">Large Cap Blend</option>
							<option value="LG">Large Cap Growth</option>
							<option value="LU">Large Cap Unclassified</option>
							<option value="LV">Large Cap Value</option>
							<option value="MB">Mid Cap Blend</option>
							<option value="MG">Mid Cap Growth</option>
							<option value="MV">Mid Cap Value</option>
							<option value="SB">Small Cap Blend</option>
							<option value="SG">Small Cap Growth</option>
							<option value="SU">Small Cap Unclassified</option>
							<option value="SV">Small Cap Value</option>
							<option value="UU">Unclassified Unclassified</option>
							<option value="XB">Micro Cap Blend</option>
							<option value="XG">Micro Cap Growth</option>
							<option value="XV">Micro Cap Value</option>
						</select>
					</td>
					<?php /*?><td>
					</td><?php */?>
					<td width="90px"><label><input type="radio" id="process-<?php echo $aTicket['ticket_id'];?>" name="process-<?php echo $aTicket['ticket_id'];?>" value="Yes" /> Yes</label><br /><label><input type="radio" name="process-<?php echo $aTicket['ticket_id'];?>" value="No" checked /> No</label></td>
				</tr>
				
				<?php
			}else{
				
			}
		}
		
		//end sort CA's
			
	}//end foreach ticket loop
	echo '</table>';
	
	$ticketIDs = implode('|', $aTicketIDs);
	echo '<input type="hidden" name="ticket_ids" value="'.$ticketIDs.'" />';
	echo '<input type="submit" name="submit-csv" value="Download CSV" />';
	echo '</form>';
	
	/*echo '<pre>';
	//print_r($aSP500);
	print_r($aCA);
	echo $error;
	echo $cnt;
	echo '</pre>';*/
	
	echo 'done';
	
}


if($process == '2'){
	//echo '<a href="http://beta.marketocracy.com/utilities/export-tickets.php">Go Back</a><br /><br /><br />';
	$aTicketIDs = explode('|',$_REQUEST['ticket_ids']);
	
	//echo $_REQUEST['ticket_ids'];
	
	$aCSV = array();
	
	$aCSV[] = array(
		'Ticket/Case Number', 'Ticket Type', 'Ticker', 'Subject', 'Description', 'stockkey check', 'SQL pre-check', 'SQL to do the update', 'SQL to check results'
	);
	
	foreach($aTicketIDs as $key=>$ticketID){
		
		$caType 	= $_REQUEST['ca-type-'.$ticketID];
		$styleKey	= $_REQUEST['style-'.$ticketID];
		$process	= $_REQUEST['process-'.$ticketID];
		$aTicket 	= explode('|',$_REQUEST['ticket-'.$ticketID]);
		
		if($caType == 'style'){
			//build queries
			$query1 = 'select distinct stockkey from mstockalias where symbol=\''.$aTicket[2].'\';';
			
			$query2 = 'select * from mstockalias where symbol=\''.$aTicket[2].'\';';
			
			$query3 = 'update mstockalias set stylekey=\''.$styleKey.'\'  where symbol=\''.$aTicket[2].'\';';
			
			$query4 = 'select * from mstockalias where symbol=\''.$aTicket[2].'\';';	
			
		}else{
			$query1 = '';
			
			$query2 = '';
			$query3 = '';
			$query4 = '';
		}
		
		if($process == "Yes"){
			$aCSV[] = array(
				'ticket_number'		=> $aTicket[0],
				'case_type'			=> $aTicket[1],
				'ticker'			=> addslashes($aTicket[2]),
				'subject'			=> addslashes($aTicket[3]),
				'desc'				=> addslashes($aTicket[4]),
				'query1'			=> $query1,
				'query2'			=> $query2,
				'query3'			=> $query3,
				'query4'			=> $query4
			);
		}
			
	}
	


	function query_to_csv($array, $filename, $attachment = false, $headers = true) {
        
        if($attachment) {
            // send response headers to the browser
            header( 'Content-Type: text/csv' );
            header( 'Content-Disposition: attachment;filename='.$filename);
            $fp = fopen('php://output', 'w');
        } else {
            $fp = fopen($filename, 'w');
        }
        
        		
		foreach($array as $key => $row){
			fputcsv($fp, $row, ',', '"');
		}
		
		
        
        fclose($fp);
    }

    // Using the function
    $sql = "SELECT * FROM table";
    // $db_conn should be a valid db handle

    // output as an attachment
    query_to_csv($aCSV, "tiket-export-".date('Y.m.d-h.i').".csv", true);

    
	
	/*echo '<pre>';
	print_r($aCSV);
	echo '</pre>';*/
	
}
?>