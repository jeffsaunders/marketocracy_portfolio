<?php
session_start();
set_time_limit(600); //10 minutes

// Load debug & error logging functions
require_once("../../includes/system-debug-functions.php");

//Connect to DB
require_once("../../../secure/dbconnect.php");

require_once("../../includes/system-functions.php");

//global triggers
$process = $_REQUEST['process'];
$insertType = $_REQUEST['insertType'];
//$tradeTable = 'members_fund_trades_test';
//$tradeTable = 'members_fund_trades_J';
$tradeTable = 'members_fund_trades';

//runtime function
function rutime($ru, $rus, $index) {
	return ($ru["ru_$index.tv_sec"]*1000 + intval($ru["ru_$index.tv_usec"]/1000))
	 -  ($rus["ru_$index.tv_sec"]*1000 + intval($rus["ru_$index.tv_usec"]/1000));
}


//set default insert type
/*
Insert Type is a var to switch what type of query to run.

Single: creates a string of VALUES (value), (value), (value), to use in a single insert query (this method is faster, but is limited to 10,000 values)

Multiple: creates multiple insert queries for each trade individually (this method takes longer than single, but is not restricted to insert restrictions)
*/
if($insertType == ''){
	$insertType = 'multiple';
}



if($process == "1"){

	//start time tracking
	$rustart = getrusage();

	//select the directory
	$dir    = '../../xml/tradeHistory';
	$aFiles = scandir($dir);

	//remove first two elements (., ..)
	unset($aFiles[0]);
	unset($aFiles[1]);


	//loop through each file in the folder
	foreach($aFiles as $key=>$filename){

		//load the xml file into an xml object
		$xml = simplexml_load_file($dir.'/'.$filename);

		##Now, In this section we can call on the first level elements

		//Set main level vars
		$fundID			= $xml->fund_ID;
		$method			= $xml->method;
		$startDate		= $xml->startDate;
		$aPositionList	= $xml->positionsList;

		//INSERT TYPE CHECK
		if($insertType == 'multiple'){
			//Delete Existing Values on fundID
			$query = "
				DELETE FROM ".$tradeTable."
				WHERE fund_id=:fund_id
				AND opened >= :start_date
			";
			try{
				$rsDELETE = $mLink->prepare($query);
				$aValues = array(
					':fund_id'		=> $fundID,
					':start_date'	=> $startDate
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//echo $preparedQuery;die();
				$rsDELETE->execute($aValues);
			}
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
		}//end if insertType = multiple

		//loop through the positions
		foreach($aPositionList->position as $key=>$aPositions){
			##Now we are in the positions level


			//Set position level vars
			$stockSymbol	= $aPositions->stockSymbol;
			$aTrades 		= $aPositions->trade;
			
			/*echo '<pre>';
			echo $stockSymbol.' <br />';
			//print_r($aPositions);
			echo '</pre>';*/
			
			//loop through each trade
			foreach($aTrades as $key=>$trade){
				##We are now in the individual trade level
				
				/*echo '<pre>';
				print_r($trade).' <br />';
				print_r($trade->limit).' <br /><br />';
				echo '</pre>';*/
				
				//set trade level vars
				$opened			= $trade->opened;
				$unix_opened	= mktime(5,0,0,substr($opened,4,2),substr($opened,6,2),substr($opened,0,4));
				$closed			= $trade->closed;
				$unix_closed	= mktime(5,0,0,substr($closed,4,2),substr($closed,6,2),substr($closed,0,4));
				$sharesOrdered	= $trade->sharesOrdered;
				$sharesFilled	= $trade->sharesFilled;
				$price			= $trade->price;
				$limit			= $trade->limit;
				$createdByCA	= $trade->createdByCA;
				$net			= $trade->net;
				$secFee			= $trade->secFee;
				$commission		= $trade->commission;
				$buyOrSell		= $trade->buyOrSell;
				$dayOrGTC		= $trade->dayOrGTC;
				$ticketKey		= $trade->ticketKey;

				//Check insert type
				if($insertType == 'single'){
					$unixTimestamp	= time();
					$companyID		= '0';
					$ticketStatus	= 'closed';

					$aInsertValues[] = array($fundID,$unixTimestamp,$companyID,$stockSymbol,$opened,$unix_opened,$closed,$unix_closed,$sharesOrdered,$sharesFilled,$price,$limit,$dayOrGTC,$ticketKey,$ticketStatus,$createdByCA,$net,$secFee,$commission,$buyOrSell);
				}//end if insertType = single



				//check insert type
				if($insertType == 'multiple'){
					//insert records into db
					$query = "
						INSERT INTO ".$tradeTable." (
							fund_id,
							timestamp,
							company_id,
							stockSymbol,
							opened,
							unix_opened,
							closed,
							unix_closed,
							sharesOrdered,
							sharesFilled,
							price,
							`limit`,
							dayOrGTC,
							ticketKey,
							ticketStatus,
							createdByCA,
							net,
							secFee,
							commission,
							buyOrSell
						)VALUES(
							:fund_id,
							UNIX_TIMESTAMP(),
							'0',
							:stockSymbol,
							:opened,
							:unix_opened,
							:closed,
							:unix_closed,
							:sharesOrdered,
							:sharesFilled,
							:price,
							:limit,
							:dayOrGTC,
							:ticketKey,
							'closed',
							:createdByCA,
							:net,
							:secFee,
							:commission,
							:buyOrSell
						)
					";
					try{
						$rsMembers = $mLink->prepare($query);
						$aValues = array(
							':fund_id'			=> $fundID,
							':stockSymbol'		=> $stockSymbol,
							':opened'			=> $opened,
							':unix_opened'		=> $unix_opened,
							':closed'			=> $closed,
							':unix_closed'		=> $unix_closed,
							':sharesOrdered'	=> $sharesOrdered,
							':sharesFilled'		=> $sharesFilled,
							':price'			=> $price,
							':limit'			=> $limit,
							':dayOrGTC'			=> $dayOrGTC,
							':ticketKey'		=> $ticketKey,
							':createdByCA'		=> $createdByCA,
							':net'				=> $net,
							':secFee'			=> $secFee,
							':commission'		=> $commission,
							':buyOrSell'		=> $buyOrSell

						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
//echo $preparedQuery;die();
						$rsMembers->execute($aValues);
					}

					catch(PDOException $error){
//echo "XXX";die();
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					/*echo '<pre>';
					echo $preparedQuery;
					echo '</pre>';*/
				}//end if insert type = multiple


			}//end loop for $aTrades


			//echo '</pre>';

		}//end loop for $aPositionList

		if($insertType == 'single'){
			//INSERT VALUES INTO DB
			//insert records into db

			$valueString = '';

			$valueCnt = 0;

			foreach($aInsertValues as $key=>$aValues){

				$valueCnt++;

				if($valueCnt == 1){
					$values = "('".implode("','",$aValues)."')";
				}else{
					$values = ",('".implode("','",$aValues)."')";
				}

				$valueString .= $values;
			}
			echo $valueCnt;
			echo $valueString;

			//Delete Existing Values on fundID
			$query = "
				DELETE FROM ".$tradeTable."
				WHERE fund_id=:fund_id
			";
			try{
				$rsDELETE = $mLink->prepare($query);
				$aValues = array(
					':fund_id'			=> $fundID
				);
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsDELETE->execute($aValues);
			}

			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}

			//Insert values into db
			$query = "
				INSERT INTO ".$tradeTable." (
					fund_id,
					timestamp,
					company_id,
					stockSymbol,
					opened,
					unix_opened,
					closed,
					unix_closed,
					sharesOrdered,
					sharesFilled,
					price,
					`limit`,
					dayOrGTC,
					ticketKey,
					ticketStatus,
					createdByCA,
					net,
					secFee,
					commission,
					buyOrSell
				)VALUES ".$valueString."
			";
			try{
				$rsInsert = $mLink->prepare($query);
				$aValues = array();
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsInsert->execute($aValues);
			}

			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			//echo $error;
			echo $error;
		}//end if insert type = single

		//move file to the processed folder
//		rename('../../xml/tradeHistory/'.$filename, '../../xml/tradeHistoryProcessed/'.$filename);

		//or just delete it
		//unlink('../../xml/tradeHistory/'.$filename);

	}//end loop for $aFiles



	//end processing (used for exec time)
	$ru = getrusage();



	//show how much time passed
	echo '<pre>';
	echo "This process used " . rutime($ru, $rustart, "utime") .
		" ms for its computations.\n";
	echo "It spent " . rutime($ru, $rustart, "stime") .
		" ms in system calls\n<br /><br />";

	//display array area
	print_r($aFiles);
	echo '</pre>';

}

?>