<?php
//SYSTEM Classes
#member class
class member {
	
	private $mLink;
	private $fLink;
	private $memberID;
	
	public function __construct(Database $mLink, Database $fLink, $memberID=NULL) {
		$this->_db		= $mLink;
		$this->_dbFeed	= $fLink;
		
		$aValues 	= array(':member_id'=>$memberID);
		$fundInfo	= $this->_db->query('SELECT * FROM members WHERE member_id=:member_id',$aValues);
		
		$this->memberID = $memberID;
	}
	
	public function getMembershipLevel(){
		
		$aValues 	= array(':member_id'=>$this->memberID);
		$subData	= $this->_db->query("SELECT s.*
			FROM ".$_SESSION['subscriptions_table']." AS s
			WHERE s.member_id=:member_id AND s.active='1' AND s.product_type='membership'
			ORDER BY s.uid DESC
			LIMIT 1",$aValues);	
			
		$productID = $subData[0]['product_id'];
		
		return($productID);	
	}
	
	
	
	public function getFundIDs(){
		
		$aValues 	= array(':member_id'=>$this->memberID);
		$fundData	= $this->_db->query("SELECT * FROM members_fund WHERE member_id=:member_id AND active='1'",$aValues);
		
		$aFunds = array();
		
		foreach($fundData as $key=>$aFundInfo){
			$aFunds[] = $aFundInfo['fund_id'];	
		}
		
		return($aFunds);
			
	}
	
	public function getFundSymbols(){
		
		$aFundSymbols = array();
		
		//check funds table
		$aValues 	= array(':member_id'=>$this->memberID);
		$fundData	= $this->_db->query("SELECT * FROM members_fund WHERE member_id=:member_id AND active='1'",$aValues);
		foreach($fundData as $key=>$aFundInfo){
			$aFundSymbols[$aFundInfo['fund_id']] = $aFundInfo['fund_symbol'];	
		}
		
		//check fund of funds table
		$fundData2	= $this->_db->query("SELECT * FROM fof_funds WHERE member_id=:member_id AND active='1'",$aValues);
		foreach($fundData2 as $key=>$aFundInfo){
			$aFundSymbols[$aFundInfo['fund_id']] = $aFundInfo['fund_symbol'];	
		}
		
		return($aFundSymbols);
		
	}
	
	/*public function newFund($aVars){
		
		
		
	}*/
		
}

#fund class
class fund {
	
	private $db;
	
	public $fundID;
	
	public function __construct(Database $mLink, Database $fLink, $fundID=NULL) {
		$this->_db		= $mLink;
		$this->_dbFeed	= $fLink;
		
		if($fundID != NULL){
			
			$aValues 	= array(':fund_id'=>$fundID);
			$fundInfo	= $this->_db->query('SELECT mf.*, ss.sector_name FROM members_fund mf INNER JOIN stocks_sectors ss ON ss.sector_id=mf.fund_sector WHERE fund_id=:fund_id',$aValues);
			
			$this->memberID			= $fundInfo[0]['member_id'];
			
			$this->fundActive		= $fundInfo[0]['active'];
			
			#basic fund data
			$this->fundData 		= $fundInfo;
			$this->fundID 			= $fundInfo[0]['fund_id'];
			$this->fundName			= $fundInfo[0]['fund_name'];
			$this->fundDesc			= $fundInfo[0]['description'];
			$this->fundSymbol 		= $fundInfo[0]['fund_symbol'];
			$this->fundType			= $fundInfo[0]['fund_type'];
			$this->fundSector		= $fundInfo[0]['fund_sector'];
			$this->fundSectorName	= $fundInfo[0]['sector_name'];
			$this->compositeFund	= $fundInfo[0]['composite_fund'];
			$this->rankEligible		= $fundInfo[0]['rank_eligible'];
			$this->inceptionDate	= $fundInfo[0]['inception_date'];
			$this->inceptionUnix	= $fundInfo[0]['unix_date'];
			$this->fundExperation	= $fundInfo[0]['fund_experation'];
			
			#legacy calls
			$this->shortFund		= $fundInfo[0]['short_fund'];
			$this->frontBaseKey		= $fundInfo[0]['fb_primarykey'];
			
			#composite data
			if($this->compositeFund == '1'){
				$this->compositeStart		= $fundInfo[0]['composite_start'];
				$this->compositeDisclosure	= $fundInfo[0]['composite_disclosure'];
			}
			
		}
			
	}
	
	public function isFundExpired(){
		
		$isExpired = false;
		
		if($this->fundExperation > time()){
			$isExpired = true;
		}
		
		return($isExpired);
	}
	
	public function getFundData(){
		return($this->fundData);
	}
	
	
	public function getCurrentCompliance(){
		
		$aValues 		= array(':fund_id'=>$this->fundID);
		$compInfo		= $this->_db->query('SELECT mfc.* FROM `members_fund_compliance` AS mfc WHERE mfc.fund_id=:fund_id ORDER BY unix_date DESC LIMIT 1',$aValues);
		return($compInfo[0]);	
	}
	
	public function calculateAllocation(){
		
		
		
		#start get sector allocation
		$aValues 		= array(':fund_id'=>$this->fundID);
		$query = 		"
			SELECT fsb.fund_id, fsb.fundRatio, fsb.stockSymbol, fsb.currentValue, fsb.totalShares, fsb.sector_id, fsb.sector, ss.sector_code_override
			FROM `members_fund_stratification_basic` fsb 
			INNER JOIN `stocks_symbols` AS ss ON ss.symbol=fsb.stockSymbol
			WHERE fsb.fund_id=:fund_id AND fsb.totalShares > 0 AND ss.active='1'
		";
		$stratData		= $this->_db->query($query,$aValues);
		
		$aSymbols		= array();
		$aStratData		= array();
	
		#build symbol list
		foreach($stratData as $key=>$strat){
			$aSymbols[] = $strat['stockSymbol'];
		}
		
		#get current pricing for stocks
		$stockData		= new stockData($this->_dbFeed);
		$aStockPrice 	= $stockData->getPricing($aSymbols);
		//end get current pricing
		
		
		#loop and calculate values
		foreach($stratData as $key=>$strat){
			$aSectorID 	= explode('-',$strat['sector_id']);
			$sectorID	= $aSectorID[0];
			
			$sectorOverride		= $strat['sector_code_override'];
			
			if($sectorOverride != NULL){ 
				$sectorID = $sectorOverride;
			}
			
			$sectorAllocation 	= 'sector'.$sectorID;
			$sectorValue		= 'sectorValue'.$sectorID;
			$posShares			= $strat['totalShares'];
			$posPrice			= $aStockPrice[$strat['stockSymbol']]['stockPrice'];
			$posValue			= ($posShares * $posPrice);
			
						
			if(!isset($totalValue)){
				$totalValue = 0;	#initialize variables
			}
			
			if(!isset($$sectorAllocation)){
				$$sectorAllocation = 0;	#initialize variables
			}

			if(!isset($$sectorValue)){
				$$sectorValue = 0;	#initialize variables
			}
			
			$totalValue			+= $posValue;
			$$sectorAllocation 	+= $strat['fundRatio'];
			$$sectorValue		+= $posValue;
			
			$aStratData[$sectorID] = array(
				'sector_name'	=> $strat['sector'],
				//'sector_allo'	=> $$sectorAllocation,
				'sector_value'	=> $$sectorValue,
				//'sector_perc'	=> number_format(($$sectorAllocation * 100),2,'.',',')
				
			);
			
			$aStratPosData[$sectorID][$strat['stockSymbol']] = array(
				'fundRatio'		=> $strat['fundRatio'],
				'totalShares'	=> $posShares,
				'price'			=> $posPrice,
				'value'			=> $posValue,
				'secCodeOver'	=> $strat['sector_code_override']
			);
			
		}//END foreach $stratData
		
		foreach($aStratData as $sectorID=>$aSecInfo){
			
			$fundAllocation = ($aSecInfo['sector_value'] / $totalValue);
			
			$aStratData[$sectorID]['fundAllocation'] = $fundAllocation * 100;
				
		}
		
		#debug
		foreach($aStratPosData as $sectorID=>$aPos){
			
			$aStratData[$sectorID]['stocks'] = $aPos;
			$aStratData[$sectorID]['totalValue'] = $totalValue;
				
		}
		//End debug
		
		
		return($aStratData);
		
			
	}
	
	public function getTodayCompliance(){
		
		$complianceRule	= 80; //80%
		$isCompliant	= false;
		
		#check for compliance
		$aStratData = $this->calculateAllocation();
		
		if($aStratData[$this->fundSector]['fundAllocation'] >= $complianceRule){
			$isCompliant = true;	
		}
	}
		
}

class stockData {
	
	private $db;
	
	public function __construct(Database $db){
		$this->_db = $db;	
	}
	
	public function getPricing($aSymbols){
		
		foreach($aSymbols as $key=>$stockSymbol){
			$aSymbols[$key] = "'".$stockSymbol."'";
		}
		$stockList	= implode(',',$aSymbols);
		
		$aValues = array();
		$results = $this->_db->query("SELECT Name AS companyName, Symbol AS symbol, Last AS CurrentPrice, PercentChangeFromPreviousClose AS chang FROM `stock_feed`	WHERE `Symbol` IN (".$stockList.")",$aValues);
		
		foreach($results as $key=>$aStockData){
			
			$getStockSymbol	= str_replace('/', '.',$aStockData['symbol']);
			
			$aStockInfo[$getStockSymbol] = array(
					'stockPrice'	=> $aStockData['CurrentPrice'],
					'today'			=> $aStockData['chang']
			);
				
		}
		
		return($aStockInfo);
		
	}
		
}

#System List Generator - Outputs list options for DB defined select instantces. 
class optionList {
	
	private $db;
	public $listName;
	public $select;
	public $displayType;
	
	public function __construct(Database $db) {
		
		$this->_db = $db;
	}
	
	public function getList($listName,$select=NULL,$displayType='html'){
		
		$aValues = array(':listName'=>$listName);
		$results = $this->_db->query('SELECT slo.* FROM `system_lists_options` AS slo WHERE slo.list_id=(SELECT sl.list_id FROM `system_lists` AS sl WHERE sl.list_name=:listName)',$aValues);
		
		switch($displayType){
			case 'result': return($results);break;
			
			case 'html':
				
				$listObject = NULL;
				foreach($results as $result=>$values){
					
					$showRow = true;
					
					if($values['admin_only'] == 1){
						$showRow = false;	
					}
					
					if($_SESSION['admin'] == 1){
						$showRow = true;	
					}
					
					
					if($showRow == true){
						if($select == $values['value']){
							$showSelect = 'selected';	
						}else{
							$showSelect = '';	
						}
						
						$listObject .= '<option value="'.$values['value'].'" '.$showSelect.'>'.$values['label'].'</option>';	
					}
				}
				
				return($listObject);
				
			break;	
		}
			
	}
	
}


#Globally called Objects
//

?>