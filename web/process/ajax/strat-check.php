<?php
/*
Marketocracy Inc. | Beta Development
WelcomeMessage


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




//+---------------------------------------------------------------------------------------------------------+
//|								LOAD Edit Payment Modal - PROCESS 2											|
//+---------------------------------------------------------------------------------------------------------+
if($process == 'strat-check'){
	
	$listFundID = $_REQUEST['fund'];
	$wizType	= $_REQUEST['wizType'];
	
	$listSymbols 	= implode(',', get_funds($mLink, $listFundID, 'stockSymbols', 'stocks'));
	$aLivePrice		= get_funds($mLink, $listFundID, 'lp', 'livePrice');
	
	$stratProcess	= get_funds($mLink, $listFundID, 'processing');
	
	$cashValue = $aLivePrice['cashValue'];
	if($cashValue < 0){
		$cashValue = '($'.number_format(str_replace('-','',$cashValue), 2, '.',',').')';	
	}else{
		$cashValue = '$'.number_format($cashValue,2,'.',',');	
	}
	
	//$aLivePrice['cashValue'] = '-40.0';
	//$listSymbols = '';
	//$stratProcess = '1';
	
	switch($wizType){
		
		case 'sell':
			if(!empty($listSymbols)){
				//check to see if stratification is updating
				if($stratProcess == '1'){
					$sellWizLink 	= '#strat-updating';
					$wizModal 		= 'data-toggle="modal"';
					
					
				}else{
					$sellWizLink 	= '?page=02-00-00-001&fund='.$listFundID.'&trade=sell&symbols='.$listSymbols.'&wiz=sell';
					$wizModal		= '';
				}
			}else{
				$sellWizLink 	= '#sell-error';
				$wizModal		= 'data-toggle="modal"';
			}
			
			echo $sellWizLink;
		break;
		
		case 'buy':
	
			if($aLivePrice['cashValue'] < 0){
				$buyWizLink		= '#buy-error';
				$buyWizModal	= 'data-toggle="modal"';
			}else{
				//check to see if stratification is updating
				if($stratProcess == '1'){
					$buyWizLink		= '#strat-updating';
					$buyWizModal	= 'data-toggle="modal"';
				}else{
					$buyWizLink		= '?page=02-00-00-001&fund='.$listFundID.'&trade=buy&wiz=buy&buySym='.$listSymbols.'';
					$buyWizModal	= '';
				}
			}
			
			echo $buyWizLink;
		break;
		
	}
	
	
					
}

