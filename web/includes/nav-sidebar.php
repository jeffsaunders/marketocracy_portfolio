<?php 
$allowedSubscriptions = array(0,99,1,2,3,4,5,10,11);
?>

         	<!-- BEGIN SIDEBAR MENU -->
         	<ul class="page-sidebar-menu" style="border-bottom:5px solid #414247;">
            	<li>
              		<form class="search-form search-form-sidebar" role="form">
                  	<div class="input-icon right" style="margin-left:15px !important;">
                    	<i class="icon-search"></i>
                    	<input type="text" class="form-control input-medium input-sm" placeholder="Search...">
                  	</div>
               		</form>
            	</li>
            	<li>
               		<!-- BEGIN SIDEBAR TOGGLER BUTTON -->
               		<div class="sidebar-toggler" id="sidebar-toggle"></div>
               		<div class="clearfix"></div>
               		<!-- BEGIN SIDEBAR TOGGLER BUTTON -->
            	</li>
            
            	<?php
				//NAV SECTION 01 (DASHBOARD SECTION)
            	$query = "
					SELECT *
					FROM ".$_SESSION['sections_table']."
					WHERE substr(sequence, 1, 2)='01' AND main_nav='1' AND active='1'
					ORDER BY sequence ASC
				";
			
				try{
					$rsMainNav = $mLink->prepare($query);
					$aValues = array(
						':section_id' => $fundSec
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsMainNav->execute($aValues);
				}
			
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
			
				while($navVar = $rsMainNav->fetch(PDO::FETCH_ASSOC)) {
					$listNavItem	= $navVar['section_name'];
					$listNavItemID	= $navVar['section_id'];
					$listNavURL		= $navVar['section_url'];
					$navIcon		= $navVar['icon'];
					$navOrder		= $navVar['sequence'];
					$navSection		= explode("-",$navOrder);
					?>
                
                	<li id="nav-<?php echo $listNavItemID; ?>">
                			<a href="/">
                   			<i class="icon-<?php echo $navIcon; ?>"></i> 
                   			<span class="title"><?php echo $listNavItem;?></span>
                   			
                   		</a>
                	</li>
                	<?php
				}
				//END NAV SECTION 01
				?>
            
            	<?php
				//NAV REMAINING SECTIONS
				
				//QUERY:01 | DB and loop code for every section not equal to section:01
				$query = "
					SELECT DISTINCT SUBSTR( sequence, 1, 2 ) AS sequence
					FROM ".$_SESSION['sections_table']."
					WHERE main_nav='1' AND active='1' 
					AND SUBSTR( sequence, 1, 2 ) <>  '01'
					ORDER BY sequence ASC
				";
				//START PDO:01
				try{
					$rsUniqueSec = $mLink->prepare($query);
					$aValues = array(
						//':section_id' => $fundSec
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsUniqueSec->execute($aValues);
				}
			
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				//END PDO:01
				
				//START QUERY:01 LOOP | For each result of QUERY:01(sections) loop through nav logic
				while($uniqueSec = $rsUniqueSec->fetch(PDO::FETCH_ASSOC)) {
					
					//skip class section if the member is not a teacher or stu
					/*if($uniqueSec['sequence'] == '13' && $_SESSION['flag_teacher'] == '0' && $_SESSION['flag_student'] == '0'){
						continue;	
					}*/
					
					
					//QUERY:02 Print each section that matches results of QUERY:01
					$query = "
						SELECT *
						FROM ".$_SESSION['sections_table']."
						WHERE substr(sequence, 1, 2)=:unique_sec AND main_nav=1 AND active=1
						ORDER BY sequence ASC
					";
					//START PDO:02
					try{
						$rsMainNav = $mLink->prepare($query);
						$aValues = array(
							':unique_sec' => $uniqueSec['sequence']
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsMainNav->execute($aValues);
					}
				
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					//END PDO:02
					
					//START QUERY:02 LOOP | PRINT TOP LEVEL NAV ELEMENTS
					while($navVar = $rsMainNav->fetch(PDO::FETCH_ASSOC)) {
						$listNavItem		= $navVar['section_name'];
						$listNavItemID		= $navVar['section_id'];
						$listNavURL			= $navVar['section_url'];
						$navIcon			= $navVar['icon'];
						$navOrder			= $navVar['sequence'];
						$navSection			= explode("-",$navOrder);
						$secAllowAccess		= $navVar['allow_access'];
						$aSecAllowAccess	= explode('|',$secAllowAccess);
						
						$showSecLink		= true;
						
						if($secAllowAccess != NULL){
							if(!in_array($_SESSION['subscription']['membershipLevelID'], $aSecAllowAccess)){
								$showSecLink = false;	
							}
						}
						
						//For each result of QUERY:02, print nav TOP LEVEL LIST ITEMS		
						
						if($showSecLink == true){
							?>
							
							<li <?php if($navSection[1] == "001") {?>style="border-top: 5px solid #414247;"<?php } ?>  id="sec-<?php echo $listNavItemID; ?>" class="<?php if($section == $listNavItemID){echo "active";}?>">
								<a href="javascript:;">
									<i class="icon-<?php echo $navIcon; ?>"></i> 
									<span class="title"><?php echo $listNavItem; ?></span>
									<span class="arrow <?php if($section == $listNavItemID){echo "open";}?>"></span>
								</a>
							<?php
							
							//START CHECK FUND: TRUE | Check Section to see if it is Funds
							if($listNavItemID == "03"/*My Funds*/) {
								
								//QUERY:03 If section is equal to: 03 "My Funds", Get Funds for Menu
								/*$query = "
									SELECT fund_id, fund_symbol, fund_name, fund_color
									FROM ".$_SESSION['fund_table']."
									WHERE member_id=:member_id AND active=1
									ORDER BY seq_id ASC
								";*/
								
								/*$query = "
									SELECT f.fund_id, f.fund_symbol, f.fund_name, s.fund_color
									FROM ".$_SESSION['fund_table']." AS f
									INNER JOIN ".$_SESSION['fund_settings_table']."  as s ON f.fund_id=s.fund_id
									WHERE member_id=:member_id AND active='1'
									ORDER BY seq_id ASC
								";
								*/
								$query = "
									SELECT f.fund_id, f.fund_symbol, f.fund_name
									FROM ".$_SESSION['fund_table']." AS f
									WHERE member_id=:member_id AND active='1'
									ORDER BY weight ASC
								";
								
								//START PDO:03
								try{
									$rsGetFunds = $mLink->prepare($query);
									$aValues = array(
										':member_id' => $_SESSION['member_id']
									);
								$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
								$rsGetFunds->execute($aValues);
								}
								
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}
								//END PDO:03
								
								//Start HTML <ul> element to create Second Level Menu
								echo "<ul class=\"sub-menu\" id=\"fund-ul\">";
								
								//START QUERY:03 LOOP | PRINT FUND NAMES AS SECOND LEVEL MENUES 
								while($rowFunds = $rsGetFunds->fetch(PDO::FETCH_ASSOC)) {
									$listFundID		= $rowFunds['fund_id'];
									$listFundSymbol	= $rowFunds['fund_symbol'];
									$listFundName	= $rowFunds['fund_name'];
									$listFundColor 	= get_funds($mLink, $listFundID, 'fundColor', 'fundSettings');
									
									
									//Check to see if fund color has been set if not, Cycle through to assign a color to a fund
									if($listFundColor == "") {
										if(!isset($fundColor)) {
											$fundColor = 1;	
										}else{
											$fundColor = $fundColor + 1;	
										}
										//If funds exceed 3 start over at 1
										if($fundColor > 3) {
											$fundColor = 1;	
										}
										//Switch the fund color depending on its value							
										switch($fundColor) {
											case 1: $setFundColor = "#57b5e3";break;
											case 2: $setFundColor = "#ed4e2a";break;
											case 3: $setFundColor = "#3cc051";break;
										}
									}else{
										$setFundColor = $listFundColor;
									}
								
									//For each result of QUERY:03 PRINT HTML of Second Level Nav Items (symbols)
									?>
									<li <?php if($fundID == $listFundID){?>class="open active"<?php } ?> style="border-left:5px solid <?php echo $setFundColor;?>;">
										<a href="javascript:;" style="color:#ffffff;">
										<!--<i class="icon-tag"></i>--> 
										<?php echo $listFundSymbol; ?><?php /* FOR TESTING: <?php echo $listFundID; ?> */?>
										<span class="arrow <?php if($fundID == $listFundID){?>open<?php } ?>"></span>
										</a>
										<ul class="sub-menu" id="fund-subpages-ul">
										
										<?php
										//QUERY:04 Get all published Thrid Level Fund Pages
										$query = "
											SELECT page_id, page_title, nav_custom, allow_access
											FROM ".$_SESSION['pages_table']."
											WHERE substr(page_id, 1, 8)='03-01-00' AND active='1' AND hide_nav='0'
										";
										
										//START PDO:04
										try{
											$rsGetPages = $mLink->prepare($query);
											$fundSec = substr($pageID, 0 , 5);
											$aValues = array(
												':section_id' => $fundSec
											);
											
											$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
											$rsGetPages->execute($aValues);
										}
										catch(PDOException $error){
											// Log any error
											file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
										}
										//END PDO:04
										
										//START BUY AND SELL WIZARD LINKS
										?>
										
										 <?php
										
										
										//if($_SESSION['admin'] == '1'){
											$listSymbols 	= implode(',', get_funds($mLink, $listFundID, 'stockSymbols', 'stocks'));
											$aLivePrice		= get_funds($mLink, $listFundID, 'lp', 'livePrice');
											
											$stratProcess	= get_funds($mLink, $listFundID, 'processing');
											
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
											
											$cashValue = $aLivePrice['cashValue'];
											if($cashValue < 0){
												$cashValue = '($'.number_format(str_replace('-','',$cashValue), 2, '.',',').')';	
											}else{
												$cashValue = '$'.number_format($cashValue,2,'.',',');	
											}
											
											//if($_SESSION['admin'] == '1'){
											if($stratProcess == 1){
												echo '<li><a href="#strat-updating" data-toggle="modal" title="Available Cash: '.$cashValue.'"><i class="icon-random"></i> Buy Wizard</a></li>';
												echo '<li style="border-bottom:3px solid #414247;"><a href="#strat-updating" data-toggle="modal" title="Available Cash: '.$cashValue.'" ><i class="icon-random"></i> Sell Wizard</a></li>';	
											}else{
												echo '<li><a href="javascript:void(0);" title="Available Cash: '.$cashValue.'" onclick="goToTradeWiz(\'buy\',\''.$listFundID.'\');"><i class="icon-random"></i> Buy Wizard</a></li>';
												echo '<li style="border-bottom:3px solid #414247;"><a href="javascript:void(0);" title="Available Cash: '.$cashValue.'" onclick="goToTradeWiz(\'sell\',\''.$listFundID.'\');"><i class="icon-random"></i> Sell Wizard '.$listFundProcessing.'</a></li>';	
												
													
											}
											
												echo '<li style="border-bottom:3px solid #414247;"><a href="?page=03-01-03-001&fund='.$listFundID.'">Holdings</a></li>';
											//}
											
											//echo '<li><a href="'.$buyWizLink.'" '.$buyWizModal.' title="Available Cash: '.$cashValue.'"><i class="icon-random"></i> Buy Wizard</a></li>';
											//echo '<li style="border-bottom:3px solid #414247;"><a href="'.$sellWizLink.'" '.$wizModal.' title="Available Cash: '.$cashValue.'"><i class="icon-random"></i> Sell Wizard</a></li>';
											
											
										//}
										
										
										//END BUY AND SELL WIZARD LINKS
										
										//START QUERY:04 LOOP | Third Level Fund Pages
										while($fundPages2 = $rsGetPages->fetch(PDO::FETCH_ASSOC)) {
											$listPageTitle2	= $fundPages2['page_title'];
											$listPageID2 	= $fundPages2['page_id'];
											$listNavCustom	= $fundPages['nav_custom'];
											
											//Check to see if page has a custom Nav Name			
											if($listNavCustom == "") {
												//If Custom Nav Name = FALSE, display Page Title
												$navTitle = $listPageTitle2;	
											}else{
												//If Custom Nav Name = TRUE, display Custom Nav Name
												$navTitle = $listNavCustom;	
											}
											
											//PRINT nav page element for QUERY:04
											?>
											<li class="<?php if($pageID == $listPageID2){?>active<?php } ?>"><a href="?page=<?php echo $listPageID2; ?>&fund=<?php echo $listFundID; ?>"><?php echo $navTitle; ?></a></li>
											<?php	
										}
										//END QUERY:04 LOOP
										?>
										<li class=""><a href="?page=04-00-00-001&member=<?php echo $_SESSION['member_id'];?>&tab=<?php echo $listFundID;?>">Public</a></li>
										
										<?php
										//QUERY:05 | GET FUND SUBSECTIONS
										$query = "
											SELECT section_name, section_id
											FROM ".$_SESSION['sections_table']."
											WHERE substr(section_id, 1, 5)='03-01' AND active='1'
										";
										
										//START PDO
										try{
											$rsGetSections = $mLink->prepare($query);
											$fundSec = substr($pageID, 0 , 5);
											$aValues = array(
												':section_id' => $fundSec
											);
											
											$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
											$rsGetSections->execute($aValues);
										}
										catch(PDOException $error){
											// Log any error
											file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
										}
										//END PDO
										
										//START QUERY:05 LOOP
										while($fundSubSec = $rsGetSections->fetch(PDO::FETCH_ASSOC)) {
											$listSecName = $fundSubSec['section_name'];
											$listSecID	= $fundSubSec['section_id'];
											$fundSubSecID = substr($pageID, 0 , 5);
											
											//Don't show empty sub sections
											if($listSecID != $fundSec) {
												//For each result of QUERY:05 PRINT each subsection
												?>
												<li <?php if($pageID == $fundSubSecID) {?>class="open"<?php } ?>>
													<a href="javascript:;"><i class="icon-folder-close"></i> <?php echo $listSecName; ?><span class="arrow"></span></a>
												   
													<?php
													//QUERY:06 | GET PAGES FOR EACH SECTION
													$query = "
														SELECT page_title, page_id, nav_custom, allow_access
														FROM ".$_SESSION['pages_table']."
														WHERE substr(page_id, 1, 8)=:section_id AND active='1' AND hide_nav='0'
													";
	
													//START PDO
													try{
														$rsGetPages2 = $mLink->prepare($query);
														$aValues = array(
															':section_id' => $listSecID
														);
	
														$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
														$rsGetPages2->execute($aValues);
													}
													catch(PDOException $error){
														// Log any error
														file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
													}
													//END PDO
	
													//START <ul> for submenu
													echo "<ul class=\"sub-menu\">";
													
													$aAllowAccess = array();
													
													//START QUERY:06 LOOP | PRINT all published pages for each section
													while($fundPages = $rsGetPages2->fetch(PDO::FETCH_ASSOC)) {
														$listPageTitle2	= $fundPages['page_title'];
														$listPageID2 	= $fundPages['page_id'];
														$listNavCustom	= $fundPages['nav_custom'];
														
														$allowAccess	= $fundPages['allow_access'];
														$aAllowAccess	= explode('|',$allowAccess);
														$showLink		= true;
													
														if($allowAccess != NULL){
															if(!in_array($_SESSION['subscription']['membershipLevelID'], $aAllowAccess)){
																$showLink = false;	
															}
														}
														
														if($_SESSION['admin'] == '1'){
															$showLink = true;	
														}
														
														if($showLink == true){
															//Check to see if page has a custom Nav Name
															if($listNavCustom == "") {
																//If Custom Nav Name = FALSE, display Page Title
																$navTitle = $listPageTitle2;
															}else{
																//If Custom Nav Name = TRUE, display Custom Nav Name
																$navTitle = $listNavCustom;
															}
															?>
															<li class="<?php if($pageID == $listPageID2){?>active<?php } ?>"><a href="?page=<?php echo $listPageID2; ?>&fund=<?php echo $listFundID; ?>"><?php echo $navTitle; ?></a></li>
															<?php
														}//end show link
	
													}//END QUERY:06 LOOP
													echo "</ul>";
												echo "</li>";
											}//END OF (Don't show empty sub sections)
											
										}//END QUERY:05 LOOP
										?>
										<li class=""><a  style="border-bottom:3px solid #414247;border-top:3px solid #414247;" href="?page=10-00-00-002&account=2&myfund=<?php echo $listFundID;?>"><i class="icon-gear"></i> Fund Settings</a></li>
										</ul>
									</li>
									
									<?php
								}//END QUERY:03 LOOP
								?>
								<?php //if($_SESSION['admin'] == '1'){?>
									
									<li><a href="?page=10-00-00-002&account=2&myfund=new"><i class="icon-plus"></i> Create New Fund</a></li>
								<?php //}?>
	
							<?php	
							}//END CHECK FUND: TRUE
							else{
							//START CHECK FUND: FALSE
							
								//QUERY:07
								$query = "
									SELECT page_id, page_title, nav_custom, allow_access, forward, target
									FROM ".$_SESSION['pages_table']."
									WHERE substr(page_id, 1, 2)=:section_id AND active=1 AND substr(page_id, 4, 2)='00' AND hide_nav='0'
									ORDER BY sequence ASC
								";
								//START PDO
								try{
									$rsNavPage = $mLink->prepare($query);
									$aValues = array(
										':section_id' => $listNavItemID
									);
								$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
								$rsNavPage->execute($aValues);
								}
								
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}
								//END PDO
								
								echo "	<ul class=\"sub-menu\">";
								$aAllowAccess = array();
								
								//START QUERY:07 LOOP
								while($rowPage = $rsNavPage->fetch(PDO::FETCH_ASSOC)) {
									$listPageTitle	= $rowPage['page_title'];
									$listPageID		= $rowPage['page_id'];
									$listNavCustom	= $rowPage['nav_custom'];
									$listNavForward	= $rowPage['forward'];
									$listNavTarget	= $rowPage['target'];
									
									$allowAccess	= $rowPage['allow_access'];
									$aAllowAccess	= explode('|',$allowAccess);
									$showLink		= true;
								
									if($allowAccess != NULL){
										if(!in_array($_SESSION['subscription']['membershipLevelID'], $aAllowAccess)){
											$showLink = false;	
										}
									}
									
									if($_SESSION['admin'] == '1'){
										$showLink = true;	
									}
									
									if($showLink == true){
									
										if($listNavCustom == "") {
											$navTitle = $listPageTitle;	
										}else{
											$navTitle = $listNavCustom;	
										}
									
										//Level 2 HTML
										?>
										
										<?php
										//Check to see if page has a FORWARD defined
										if($listNavForward == "") {
										?>
											<li <?php if($pageID == $listPageID) {?>class="active"<?php }?>><a href="?page=<?php echo $listPageID; ?>"><?php echo $navTitle; ?> </a></li>
										<?php
										}else{
											if($listNavTarget != ""){
												$showTarget = 'target="'.$listNavTarget.'"';	
											}else{
												$showTarget = '';	
											}
										?>
											<li class="<?php if($pageID == $listSubPageID){?>active<?php } ?>"><a href="<?php echo $listNavForward; ?>" <?php echo $showTarget;?>><?php echo $navTitle; ?></a></li>
										<?php
										}
										
									}//end show link
									
								}//END QUERY:07 LOOP
								?>
								
								<?php
								//QUERY:08 | GET FUND SUBSECTIONS
								$query = "
									SELECT section_id, section_name
									FROM ".$_SESSION['sections_table']."
									WHERE SUBSTR( section_id, 1, 2 ) =  :section_id AND LENGTH( section_id ) =5 AND active='1'
									ORDER BY sequence ASC
								";
								
								//START PDO
								try{
									$rsSubSec = $mLink->prepare($query);
									$aValues = array(
										':section_id' => $listNavItemID
									);
									
									$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
									$rsSubSec->execute($aValues);
								}
								catch(PDOException $error){
									// Log any error
									file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
								}
								//END PDO
								
								//START QUERY:08 LOOP
								while($rowSubSec = $rsSubSec->fetch(PDO::FETCH_ASSOC)) {
									$listSubSecName = $rowSubSec['section_name'];
									$listSubSecID	= $rowSubSec['section_id'];
									
									$checkSec		= $section;
									$checkSec		.= "-";
									$checkSec		.= $subSec1;
									
									//For each result of QUERY:08 PRINT each subsection
									?>
									<li <?php if($checkSec == $listSubSecID) {?>class="active"<?php } ?>>
										<a href="javascript:;"><i class="icon-folder-close"></i> <?php echo $listSubSecName; //echo $listSubSecID; ?><span class="arrow <?php if($checkSec == $listSubSecID) {?>open<?php } ?>"></span></a>
									   
										<?php
										
										
										//START <ul> for submenu
										echo "<ul class=\"sub-menu\">";
										
										//START if section is FORUMS
										if($listSubSecID == "04-01-"){
											
											//QUERY:FORUM | GET FORUMS
											$query = "
												SELECT forum_title, forum_description, forum_id
												FROM ".$_SESSION['forums_table']."
												WHERE active=1
												ORDER BY sequence ASC
											";
											
											//START PDO
											try{
												$rsForums = $mLink->prepare($query);
												$aValues = array(
													//':section_id' => $listSubSecID
												);
												
												$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
												$rsForums->execute($aValues);
											}
											catch(PDOException $error){
												// Log any error
												file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
											}
											//END PDO
											
											//START QUERY:FORUM LOOP | PRINT all active forums
											while($forum = $rsForums->fetch(PDO::FETCH_ASSOC)) {
												
												
												//Check to see if page has a custom Nav Name
												if($forum['nav_title'] == NULL) {
													//If Custom Nav Name = FALSE, display Page Title
													$forumTitle = $forum['forum_title'];	
												}else{
													//If Custom Nav Name = TRUE, display Custom Nav Name
													$forumTitle = $forum['nav_title'];	
												}
												
												$forumID = $_REQUEST['forum'];
												
												?>
												<li class="<?php if($pageID == "04-01-00-002" && $forum['forum_id']==$forumID){?>active<?php } ?>"><a href="?page=04-01-00-002&forum=<?php echo $forum['forum_id']; ?>"><?php echo $forumTitle; ?></a></li>
												<?php	
											}//END QUERY:09 LOOP
											
										}else{
											//QUERY:09 | GET PAGES FOR EACH SECTION
											$query = "
												SELECT page_title, page_id, nav_custom, forward, allow_access
												FROM ".$_SESSION['pages_table']."
												WHERE substr(page_id, 1, 5)=:section_id AND active='1' AND hide_nav='0'
											";
											
											//START PDO
											try{
												$rsSubPages = $mLink->prepare($query);
												$aValues = array(
													':section_id' => $listSubSecID
												);
												
												$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
												$rsSubPages->execute($aValues);
											}
											catch(PDOException $error){
												// Log any error
												file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
											}
											//END PDO
											
											$aAllowAccess = array();
											
											//START QUERY:09 LOOP | PRINT all published pages for each section
											while($subPages = $rsSubPages->fetch(PDO::FETCH_ASSOC)) {
												$listSubPageTitle	= $subPages['page_title'];
												$listSubPageID 		= $subPages['page_id'];
												$listNavCustom		= $subPages['nav_custom'];
												$listNavForward		= $subPages['forward'];
												
												$allowAccess	= $subPages['allow_access'];
												$aAllowAccess	= explode('|',$allowAccess);
												$showLink		= true;
											
												if($allowAccess != NULL){
													if(!in_array($_SESSION['subscription']['membershipLevelID'], $aAllowAccess)){
														$showLink = false;	
													}
												}
												
												if($_SESSION['admin'] == '1'){
													$showLink = true;	
												}
												
												if($showLink == true){
	
													//Check to see if page has a custom Nav Name
													if($listNavCustom == "") {
														//If Custom Nav Name = FALSE, display Page Title
														$navTitle = $listSubPageTitle;	
													}else{
														//If Custom Nav Name = TRUE, display Custom Nav Name
														$navTitle = $listNavCustom;	
													}
													//Check to see if page has a FORWARD defined
													if($listNavForward == "") {
													?>
														<li class="<?php if($pageID == $listSubPageID){?>active<?php } ?>"><a href="?page=<?php echo $listSubPageID; ?>"><?php echo $navTitle; ?> <?php echo $listNavForward;?> </a></li>
													<?php
													}else{
													?>
														<li class="<?php if($pageID == $listSubPageID){?>active<?php } ?>"><a href="<?php echo $listNavForward; ?>"><?php echo $navTitle; ?></a></li>
													<?php
													}
												}//end show link
	
											}//END QUERY:09 LOOP
											
										}
										//END IF SECTION IS FORUMS
										
										echo "</ul>";
									echo "</li>";
								}//END QUERY:08 LOOP
									
							}//END CHECK FUND: FALSE
							?>
								</ul>
							</li>
							<?php
						}//end if showSecLink
					}//END QUERY:02 LOOP
				}//END QUERY:01 LOOP
				
				if($_SESSION['admin'] == '1' || in_array($_SESSION['subscription']['membershipLevelID'],$allowedSubscriptions)){
					?>
                    <li style="border-top: 5px solid #414247;" id="sec-13" class="<?php if($section == '13'){echo "active";}?>">
                        <a href="javascript:;">
                            <i class="icon-retweet"></i>
                            <span class="title">Action Center</span>
                            <span class="arrow <?php if($section == '01'){echo "open";}?>"></span>
                        </a>
                        
                        <ul class="sub-menu">
                        	<?php /*?><li <?php if($checkSec == '13-01') {?>class="active"<?php } ?>>
								<a href="https://portfolio.marketocracy.com/?page=01-00-00-003">Overview</span></a>
                            </li><?php */?>
                            <li <?php if($checkSec == '13-01') {?>class="active"<?php } ?>>
								<a href="/moodle.php" target="_blank">Roundtable</span></a>
                            </li>
                        </ul>    
                    </li>
                    <?php
				}
				
				//START CLASS SECTION
				$showOldClass = false;
				if($_SESSION['flag_teacher'] == '1' && $showOldClass == true || $_SESSION['flag_student'] == '1' && $showOldClass == true/*OLD CLASS*/){
					?>
					<li style="border-top: 5px solid #414247;" id="sec-13" class="<?php if($section == '13'){echo "active";}?>">
						<a href="javascript:;">
							<i class="icon-book"></i> 
							<span class="title">My Classes</span>
							<span class="arrow <?php if($section == '13'){echo "open";}?>"></span>
						</a>
                        
                        <ul class="sub-menu">
                        	<li <?php if($checkSec == '13-01') {?>class="active"<?php } ?>>
								<a href="javascript:;"><i class="icon-folder-close"></i> Classes<span class="arrow <?php if($checkSec == '13-01') {?>open<?php } ?>"></span></a>
                                
                                <?php if($_SESSION['flag_teacher'] == '1' /*&& $_SESSION['admin'] != '1'*/){?>
                                
                                    <ul class="sub-menu">
                                        <li class="<?php if($pageID == '13-01-00-001'){?>active<?php } ?>"><a href="?page=13-01-00-001">Overview </a></li>
                                        <?php
                                        if($_SESSION['flag_teacher'] == '1'){
                                            
                                            $query = "
                                                SELECT *
                                                FROM ".$_SESSION['class_table']."
                                                WHERE teacher_id=:member_id
                                                ORDER BY start_date ASC 
                                            ";
                                            try{
                                                $rsClasses = $mLink->prepare($query);
                                                $aValues = array(
                                                    ':member_id' => $_SESSION['member_id']
                                                );
                                                
                                                $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                                $rsClasses->execute($aValues);
                                            }
                                            catch(PDOException $error){
                                                // Log any error
                                                file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                            }
                                            while($class = $rsClasses->fetch(PDO::FETCH_ASSOC)) {
                                                ?>
                                                <li class="<?php if($pageID == '13-01-00-002' && $classID == $class['class_id']){?>active<?php } ?>"><a href="?page=13-01-00-002&class=<?php echo $class['class_id'];?>"><?php echo $class['class_title'];?></a></li>
                                                <?php	
                                            }
                                        }
                                        ?>
                                    </ul>
                                <?php
								}else{
									
									?>
									<ul class="sub-menu">
                                        <li class="<?php if($pageID == '13-01-00-011'){?>active<?php } ?>"><a href="?page=13-01-00-011">Overview</a></li>
                                        <?php
                                        if($_SESSION['flag_student'] == '1'){
                                            
                                            $query = "
                                                SELECT *
                                                FROM ".$_SESSION['class_table']."
                                                WHERE teacher_id=:member_id
                                                ORDER BY start_date ASC 
                                            ";
                                            try{
                                                $rsClasses = $mLink->prepare($query);
                                                $aValues = array(
                                                    ':member_id' => $_SESSION['member_id']
                                                );
                                                
                                                $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                                $rsClasses->execute($aValues);
                                            }
                                            catch(PDOException $error){
                                                // Log any error
                                                file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                            }
                                            while($class = $rsClasses->fetch(PDO::FETCH_ASSOC)) {
                                                ?>
                                                <li class="<?php if($pageID == '13-01-00-002' && $classID == $class['class_id']){?>active<?php } ?>"><a href="?page=13-01-00-002&class=<?php echo $class['class_id'];?>"><?php echo $class['class_title'];?></a></li>
                                                <?php	
                                            }
                                        }
                                        ?>
                                    </ul>
                                    <?php
									
								}//end student/teacher check
								?>
                                
                            </li>
                        </ul>
					</li>
                <?php
				}
				//END CLASS SECTION
				?>
                
                <li <?php if($pageID == "10-00-00-002" && $_REQUEST['tab'] != 'account' && $_REQUEST['account'] != '2') {?>class="active"<?php } ?>style="border-top: 5px solid #414247;" id="nav-profile">
                    <a href="?page=10-00-00-002">
                    <i class="icon-user"></i> 
                    <span class="title">Profile</span>
                    </a>
                </li>
                <li <?php if($pageID == "10-00-00-002" && $_REQUEST['tab'] == 'account' || $pageID == '10-00-00-002' && $_REQUEST['account'] == '2' ) {?>class="active" style="border-top:1px solid #414247;"<?php } ?> id="nav-settings">
                    <a href="?page=10-00-00-002&account=1&tab=account">
                    <i class="icon-gear"></i> 
                    <span class="title">Settings</span>
                    </a>
                </li>
                
                <?php
				//ADMIN NAVIGATION MENU
				if($_SESSION['admin'] == "1"){
				?>
                <li style="border-top: 5px solid #414247;" id="sec-13" class="<?php if($section == '20'){echo "active";}?>">
                        <a href="javascript:;">
                            <i class="icon-retweet"></i>
                            <span class="title">Admin Center</span>
                            <span class="arrow <?php if($section == '20'){echo "open";}?>"></span>
                        </a>
                        
                        <ul class="sub-menu">
                        	<li <?php if($checkSec == '20-01') {?>class="active"<?php } ?>>
								<a href="javascript:;"><i class="icon-folder-close"></i> Email<span class="arrow <?php if($checkSec == '20-01') {?>open<?php } ?>"></span></a>
                                
                                <ul class="sub-menu">
                                        <li class="<?php if($pageID == '20-00-00-009'){?>active<?php } ?>"><a href="?page=20-00-00-009">Campaigns </a></li>
                                        <li class="<?php if($pageID == '20-00-00-010'){?>active<?php } ?>"><a href="?page=20-00-00-010">Auto Campaigns </a></li>
                                        <li class="<?php if($pageID == '20-01-00-012'){?>active<?php } ?>"><a href="?page=20-01-00-012">Montly Performance </a></li>
                                </ul>
                            </li>
                       	</ul>
                           
                    </li>
                    
                
                
                <?php					
				}
				?>
                
                <li class="last " style="border-bottom:1px solid #292a2d;border-top: 5px solid #414247;" id="nav-logout">
                    <a href="logout">
                    <i class="icon-signout"></i> 
                    <span class="title">Logout</span>
                    </a>
                </li>
                
                
                
         	</ul>
         	<!-- END SIDEBAR MENU -->
         
