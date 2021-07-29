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
					FROM sections
					WHERE substr(sequence, 1, 2)='01' AND main_nav=1
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
                   			<span class="arrow "></span>
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
					FROM sections
					WHERE main_nav =1
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
		
					//QUERY:02 Print each section that matches results of QUERY:01
					$query = "
						SELECT *
						FROM sections
						WHERE substr(sequence, 1, 2)=:unique_sec AND main_nav=1
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
						$listNavItem	= $navVar['section_name'];
						$listNavItemID	= $navVar['section_id'];
						$listNavURL		= $navVar['section_url'];
						$navIcon		= $navVar['icon'];
						$navOrder		= $navVar['sequence'];
						$navSection		= explode("-",$navOrder);
					
						//For each result of QUERY:02, print nav TOP LEVEL LIST ITEMS		
						?>
						<li <?php if($navSection[1] == "001") {?>style="border-top: 5px solid #414247;"<?php } ?>  id="sec-<?php echo $listNavItemID; ?>" class="<?php if($section == $listNavItemID){echo "active";}?>">
							<a href="javascript:;">
								<i class="icon-<?php echo $navIcon; ?>"></i> 
								<span class="title"><?php echo $listNavItem; ?></span>
								<span class="arrow"></span>
							</a>
						<?php
						
						//START CHECK FUND: TRUE | Check Section to see if it is Funds
						if($listNavItemID == "03"/*My Funds*/) {
							
							//QUERY:03 If section is equal to: 03 "My Funds", Get Funds for Menu
							$query = "
								SELECT fund_id, fund_symbol, fund_name, fund_color
								FROM funds_test
								WHERE member_id=:member_id AND active=1
								ORDER BY seq_id ASC
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
							echo "	<ul class=\"sub-menu\">";
							
							//START QUERY:03 LOOP | PRINT FUND NAMES AS SECOND LEVEL MENUES 
							while($rowFunds = $rsGetFunds->fetch(PDO::FETCH_ASSOC)) {
								$listFundID		= $rowFunds['fund_id'];
								$listFundSymbol	= $rowFunds['fund_symbol'];
								$listFundName	= $rowFunds['fund_name'];
								$listFundColor 	= $rowFunds['fund_color'];
								
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
								}
							
								//For each result of QUERY:03 PRINT HTML of Second Level Nav Items (symbols)
								?>
								<li <?php if($fundID == $listFundID){?>class="open"<?php } ?> style="border-left:5px solid <?php echo $setFundColor;?>;">
									<a href="javascript:;" style="color:#ffffff;">
									<!--<i class="icon-tag"></i>--> 
									<?php echo $listFundSymbol; ?>
									<span class="arrow"></span>
									</a>
									<ul class="sub-menu">
									
									<?php
									//QUERY:04 Get all published Thrid Level Fund Pages
									$query = "
										SELECT page_id, page_title, nav_custom
										FROM pages
										WHERE substr(page_id, 1, 8)='03-01-00'
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
									
									<?php
									//QUERY:05 | GET FUND SUBSECTIONS
									$query = "
										SELECT section_name, section_id
										FROM sections
										WHERE substr(section_id, 1, 5)='03-01'
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
										
										//Don't show empty sub sections
										if($listSecID != $fundSec) {
											//For each result of QUERY:05 PRINT each subsection
											?>
											<li <?php if(in_array($pageID, $fundSubNav1)) {?>class="open"<?php } ?>>
												<a href="javascript:;"><i class="icon-folder-close"></i> <?php echo $listSecName; ?><span class="arrow"></span></a>
											   
												<?php
												//QUERY:06 | GET PAGES FOR EACH SECTION
												$query = "
													SELECT page_title, page_id, nav_custom
													FROM pages
													WHERE substr(page_id, 1, 8)=:section_id
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
												
												//START QUERY:06 LOOP | PRINT all published pages for each section
												while($fundPages = $rsGetPages2->fetch(PDO::FETCH_ASSOC)) {
													$listPageTitle2	= $fundPages['page_title'];
													$listPageID2 	= $fundPages['page_id'];
													$listNavCustom	= $fundPages['nav_custom'];
													
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
												}//END QUERY:06 LOOP
												echo "</ul>";
											echo "</li>";
										}//END OF (Don't show empty sub sections)
									}//END QUERY:05 LOOP
									?>
									</ul>
								</li>
								<?php
							}//END QUERY:03 LOOP
							
						}//END CHECK FUND: TRUE
						else{
						//START CHECK FUND: FALSE
						
							//QUERY:07
							$query = "
								SELECT page_id, page_title, nav_custom
								FROM pages
								WHERE substr(page_id, 1, 2)=:section_id AND active=1 AND substr(page_id, 4, 2)='00'
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
							
							//START QUERY:07 LOOP
							while($rowPage = $rsNavPage->fetch(PDO::FETCH_ASSOC)) {
								$listPageTitle	= $rowPage['page_title'];
								$listPageID		= $rowPage['page_id'];
								$listNavCustom	= $rowPage['nav_custom'];
								
								if($listNavCustom == "") {
									$navTitle = $listPageTitle;	
								}else{
									$navTitle = $listNavCustom;	
								}
							
								//Level 2 HTML
								?>
								<li <?php if($pageID == $listPageID) {?>class="active"<?php }?>><a href="?page=<?php echo $listPageID; ?>"><?php echo $navTitle; ?></a></li>
								<?php
							}//END QUERY:07 LOOP
							?>
                            
                            <?php
							//QUERY:08 | GET FUND SUBSECTIONS
							$query = "
								SELECT section_id, section_name
								FROM sections
								WHERE SUBSTR( section_id, 1, 2 ) =  :section_id AND LENGTH( section_id ) =5
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
								
								
								//For each result of QUERY:08 PRINT each subsection
								?>
								<li <?php if(in_array($pageID, $fundSubNav1)) {?>class="open"<?php } ?>>
									<a href="javascript:;"><i class="icon-folder-close"></i> <?php echo $listSubSecName; ?><span class="arrow"></span></a>
								   
									<?php
									//QUERY:09 | GET PAGES FOR EACH SECTION
									$query = "
										SELECT page_title, page_id, nav_custom
										FROM pages
										WHERE substr(page_id, 1, 5)=:section_id AND active=1
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
									
									//START <ul> for submenu
									echo "<ul class=\"sub-menu\">";
									
									//START QUERY:09 LOOP | PRINT all published pages for each section
									while($subPages = $rsSubPages->fetch(PDO::FETCH_ASSOC)) {
										$listSubPageTitle	= $subPages['page_title'];
										$listSubPageID 		= $subPages['page_id'];
										$listNavCustom	= $subPages['nav_custom'];
										
										//Check to see if page has a custom Nav Name
										if($listNavCustom == "") {
											//If Custom Nav Name = FALSE, display Page Title
											$navTitle = $listPageTitle2;	
										}else{
											//If Custom Nav Name = TRUE, display Custom Nav Name
											$navTitle = $listNavCustom;	
										}
										
										?>
										<li class="<?php if($pageID == $listSubPageID){?>active<?php } ?>"><a href="?page=<?php echo $listSubPageID; ?>&fund=<?php echo $listFundID; ?>"><?php echo $navTitle; ?></a></li>
										<?php	
									}//END QUERY:09 LOOP
									echo "</ul>";
								echo "</li>";
							}//END QUERY:08 LOOP
								
						}//END CHECK FUND: FALSE
						?>
							</ul>
						</li>
						<?php
					}//END QUERY:02 LOOP
				}//END QUERY:01 LOOP
				?>
            
           
            
            
       
            
             <li <?php if($pageID == "user-profile") {?>class="active"<?php } ?>style="border-top: 5px solid #414247;" id="nav-profile">
               <a href="profile.php">
               <i class="icon-user"></i> 
               <span class="title">Profile</span>
               </a>
            </li>
            <li class="last " style="border-bottom:1px solid #292a2d;" id="nav-logout">
               <a href="logout">
               <i class="icon-signout"></i> 
               <span class="title">Logout</span>
               </a>
            </li>
            
            
            <?php if(1 == 2) {?>
            <li>&nbsp;</li>
            
            <li class="start <?php if($pageID == "home") {?>active<?php }?>" style="border-top: 1px solid #414247;" id="nav-dashboard">
               <a href="/">
               <i class="icon-dashboard"></i> 
               <span class="title">Dashboard</span>
               <span class="selected"></span>
               </a>
            </li>
            
            <?php 
            $navMark1 = array("02-00-00-001","02-00-00-002","02-00-00-003");
			?>
            <li style="border-top: 5px solid #414247;" class="<?php if (in_array($pageID, $navMark1)) {?>active<?php }?>" id="make-trade">
               <a href="javascript:;">
               <i class="icon-random"></i> 
               <span class="title">Make A Trade</span>
               <span class="arrow"></span>
               </a>
               <ul class="sub-menu">
               	  <li <?php if($pageID == "02-00-00-001") {?>class="active"<?php }?>><a href="?page=02-00-00-001"><!--<i class="icon-user"></i>--> Trade Wizard</a></li>
                  <?php /*?><li><a href="#"><!--<i class="icon-user"></i>-->  Overview</a></li><?php */?>
                  <li <?php if($pageID == "02-00-00-002") {?>class="active"<?php }?>><a href="?page=02-00-00-002"><!--<i class="icon-external-link"></i>-->  Open Orders</a></li>
                  <li <?php if($pageID == "02-00-00-003") {?>class="active"<?php }?>><a href="?page=02-00-00-003"><!--<i class="icon-bell"></i>-->  Recent Orders</a></li>
               </ul>
            </li>
            
            <!--START MY FUNDS-->
            <?php 
            $navMark2 = array("03");
			?>
            <li class="<?php if (in_array($section, $navMark2)) {?>active<?php }?>" id="my-funds">
               <a href="javascript:;">
               <i class="icon-money"></i> 
               <span class="title">My Funds</span>
               <span class="arrow"></span>
               </a>
               <ul class="sub-menu">
               
               	  <!--START FUND 1-->
                  
                  <li <?php if($fundID == "JDM"){?>class="open"<?php } ?> style="border-left:5px solid #57b5e3;">
                     <a href="javascript:;" style="color:#ffffff;">
                     <!--<i class="icon-tag"></i>--> 
                     JDM
                     <span class="arrow"></span>
                     </a>
                     <ul class="sub-menu">
                     	<li class="<?php if($pageID == "fund-overview"){?>active<?php } ?>"><a href="/fund-overview.php"><!--<i class="icon-user"></i>-->  Overview</a></li>
                        <li class="<?php if($pageID == "fund-admin"){?>active<?php } ?>"><a href="/fund-admin.php"><!--<i class="icon-user"></i>-->  Admin</a></li>
                        <?php
						$fundSubNav1 = array("fund-zones, fund-attr-stock");
						?>
                        <li <?php if(in_array($pageID, $fundSubNav1)) {?>class="open"<?php } ?>><a href="javascript:;"><i class="icon-folder-close"></i> Attribution <span class="arrow"></span></a>
                           <ul class="sub-menu">
                              <li><a href="#"><!--<i class="icon-remove"></i>--> By Sector</a></li>
                              <li class="<?php if($pageID == "fund-attr-stock"){?>active<?php } ?>"><a href="/fund-attr-stock.php"><!--<i class="icon-pencil"></i>--> By Stock</a></li>
                              <li class="<?php if($pageID == "fund-zones"){?>active<?php } ?>"><a href="/fund-zones.php"><!--<i class="icon-edit"></i>--> Zones</a></li>
                           </ul>
                        </li>
                        <?php
						$fundSubNav2 = array("bench-contour, bench-scatter, bench-success, bench-trailing");
						?>
                        <li <?php if(in_array($pageID, $fundSubNav2)) {?>class="open"<?php } ?>><a href="javascript:;"><i class="icon-folder-close"></i> Benchmarks <span class="arrow"></span></a>
                           <ul class="sub-menu">
                              <li class="<?php if($pageID == "bench-contour"){?>active<?php } ?>"><a href="/bench-contour.php"><!--<i class="icon-remove"></i>--> Alpha Contour</a></li>
                              <li class="<?php if($pageID == "bench-scatter"){?>active<?php } ?>"><a href="/bench-scatter.php"><!--<i class="icon-pencil"></i>--> Alpha Scatter</a></li>
                              <li class="<?php if($pageID == "bench-success"){?>active<?php } ?>"><a href="/bench-success.php"><!--<i class="icon-edit"></i>--> Alpha Success</a></li>
                              <li class="<?php if($pageID == "bench-trailing"){?>active<?php } ?>"><a href="/bench-trailing.php"><!--<i class="icon-edit"></i>--> Alpha Trailing</a></li>
                           </ul>
                        </li>
                        <li><a href="/trade-wizard.php"><i class="icon-random"></i>  Trade Wizard</a></li>
                        <li class="<?php if($pageID == "fund-compliance"){?>active<?php } ?>"><a href="/fund-compliace.php"><!--<i class="icon-external-link"></i>-->  Compliance</a></li>
                        <li class="<?php if($pageID == "fund-ledger"){?>active<?php } ?>"><a href="/fund-ledger.php"><!--<i class="icon-bell"></i>-->  Ledger</a></li>
                        <li class="<?php if($pageID == "fund-public"){?>active<?php } ?>"><a href="/fund-public.php"><!--<i class="icon-bell"></i>-->  Public</a></li>
                        <li class="<?php if($pageID == "fund-rank"){?>active<?php } ?>"><a href="/fund-rank.php"><!--<i class="icon-bell"></i>-->  Ranking</a></li>
                        <li><a href="javascript:;"><i class="icon-folder-close"></i> Stratification <span class="arrow"></span></a>
                           <ul class="sub-menu">
                              <li class="<?php if($pageID == "fund-strat-basic"){?>active<?php } ?>"><a href="/fund-strat-basic.php"><!--<i class="icon-remove"></i--> Basic</a></li>
                              <li class="<?php if($pageID == "fund-strat-sector"){?>active<?php } ?>"><a href="/fund-strat-sector.php"><!--<i class="icon-pencil"></i>--> Sector</a></li>
                              <li class="<?php if($pageID == "fund-strat-style"){?>active<?php } ?>"><a href="/fund-strat-style.php"><!--<i class="icon-edit"></i>--> Style</a></li>
                              <li class="<?php if($pageID == "fund-strat-activity"){?>active<?php } ?>"><a href="/fund-strat-activity.php"><!--<i class="icon-edit"></i>--> Activity</a></li>
                              <li class="<?php if($pageID == "fund-strat-fundamental"){?>active<?php } ?>"><a href="#"><!--<i class="icon-edit"></i>--> Fundamentals</a></li>
                              <li class="<?php if($pageID == "fund-strat-m100"){?>active<?php } ?>"><a href="#"><!--<i class="icon-edit"></i>--> m100 Trading</a></li>
                              <li class="<?php if($pageID == "fund-strat-performance"){?>active<?php } ?>"><a href="#"><!--<i class="icon-edit"></i>--> Performance</a></li>
                              <li class="<?php if($pageID == "fund-strat-rationale"){?>active<?php } ?>"><a href="#"><!--<i class="icon-edit"></i>--> Rationale</a></li>
                           </ul>
                        </li>
                        <li><a href="#"><!--<i class="icon-bell"></i>-->  Timing</a></li>
                        <li><a href="#"><!--<i class="icon-bell"></i> --> Top Ten</a></li>
                        <li><a href="#"><!--<i class="icon-bell"></i>-->  Trades</a></li>
                        <li><a href="#"><!--<i class="icon-bell"></i>-->  Volatility</a></li>
                        <li><a href="#"><!--<i class="icon-bell"></i> --> My Holdings</a></li>
                     </ul>
                  </li>
                  <!--END Fund 1-->
                  <!--START Fund 2-->
                  <li style="/*background:#ed4e2a;*/border-left:5px solid #ed4e2a;">
                     <a href="javascript:;" style="color:#ffffff;">
                     <!--<i class="icon-tag"></i>--> 
                     JDHMF
                     <span class="arrow"></span>
                     </a>
                     <ul class="sub-menu">
                     	<li><a href="#"><!--<i class="icon-user"></i>-->  Overview</a></li>
                        <li><a href="#"><!--<i class="icon-user"></i>-->  Admin</a></li>
                        <li><a href="javascript:;"><i class="icon-folder-close"></i> Attribution <span class="arrow"></span></a>
                           <ul class="sub-menu">
                              <li><a href="#"><!--<i class="icon-remove"></i>--> By Sector</a></li>
                              <li><a href="#"><!--<i class="icon-pencil"></i>--> By Stock</a></li>
                              <li><a href="#"><!--<i class="icon-edit"></i>--> Zones</a></li>
                           </ul>
                        </li>
                        <li><a href="javascript:;"><i class="icon-folder-close"></i> Benchmarks <span class="arrow"></span></a>
                           <ul class="sub-menu">
                              <li><a href="#"><!--<i class="icon-remove"></i>--> Alpha Contour</a></li>
                              <li><a href="#"><!--<i class="icon-pencil"></i>--> Alpha Scatter</a></li>
                              <li><a href="#"><!--<i class="icon-edit"></i>--> Alpha Success</a></li>
                              <li><a href="#"><!--<i class="icon-edit"></i>--> Alpha Trailing</a></li>
                           </ul>
                        </li>
                        <li><a href="#"><i class="icon-random"></i>  Trade Wizard</a></li>
                        <li><a href="#"><!--<i class="icon-external-link"></i>-->  Compliance</a></li>
                        <li><a href="#"><!--<i class="icon-bell"></i>-->  Ledger</a></li>
                        <li><a href="#"><!--<i class="icon-bell"></i>-->  Public</a></li>
                        <li><a href="#"><!--<i class="icon-bell"></i>-->  Ranking</a></li>
                        <li><a href="javascript:;"><i class="icon-folder-close"></i> Stratification <span class="arrow"></span></a>
                           <ul class="sub-menu">
                              <li><a href="#"><!--<i class="icon-remove"></i-->> Basic</a></li>
                              <li><a href="#"><!--<i class="icon-pencil"></i>--> Sector</a></li>
                              <li><a href="#"><!--<i class="icon-edit"></i>--> Style</a></li>
                              <li><a href="#"><!--<i class="icon-edit"></i>--> Activity</a></li>
                              <li><a href="#"><!--<i class="icon-edit"></i>--> Fundamentals</a></li>
                              <li><a href="#"><!--<i class="icon-edit"></i>--> m100 Trading</a></li>
                              <li><a href="#"><!--<i class="icon-edit"></i>--> Performance</a></li>
                              <li><a href="#"><!--<i class="icon-edit"></i>--> Rationale</a></li>
                           </ul>
                        </li>
                        <li><a href="#"><!--<i class="icon-bell"></i>-->  Timing</a></li>
                        <li><a href="#"><!--<i class="icon-bell"></i> --> Top Ten</a></li>
                        <li><a href="#"><!--<i class="icon-bell"></i>-->  Trades</a></li>
                        <li><a href="#"><!--<i class="icon-bell"></i>-->  Volatility</a></li>
                        <li><a href="#"><!--<i class="icon-bell"></i> --> My Holdings</a></li>
                     </ul>
                  </li>
                  <!--END Fund 2-->
                  <!--START Fund 3-->
                  <li style="/*background:#ed4e2a;*/border-left:5px solid #3cc051;">
                     <a href="javascript:;" style="color:#ffffff;">
                     <!--<i class="icon-tag"></i>--> 
                     JDJD
                     <span class="arrow"></span>
                     </a>
                     <ul class="sub-menu">
                     	<li><a href="#"><!--<i class="icon-user"></i>-->  Overview</a></li>
                        <li><a href="#"><!--<i class="icon-user"></i>-->  Admin</a></li>
                        <li><a href="javascript:;"><i class="icon-folder-close"></i> Attribution <span class="arrow"></span></a>
                           <ul class="sub-menu">
                              <li><a href="#"><!--<i class="icon-remove"></i>--> By Sector</a></li>
                              <li><a href="#"><!--<i class="icon-pencil"></i>--> By Stock</a></li>
                              <li><a href="#"><!--<i class="icon-edit"></i>--> Zones</a></li>
                           </ul>
                        </li>
                        <li><a href="javascript:;"><i class="icon-folder-close"></i> Benchmarks <span class="arrow"></span></a>
                           <ul class="sub-menu">
                              <li><a href="#"><!--<i class="icon-remove"></i>--> Alpha Contour</a></li>
                              <li><a href="#"><!--<i class="icon-pencil"></i>--> Alpha Scatter</a></li>
                              <li><a href="#"><!--<i class="icon-edit"></i>--> Alpha Success</a></li>
                              <li><a href="#"><!--<i class="icon-edit"></i>--> Alpha Trailing</a></li>
                           </ul>
                        </li>
                        <li><a href="#"><i class="icon-random"></i>  Trade Wizard</a></li>
                        <li><a href="#"><!--<i class="icon-external-link"></i>-->  Compliance</a></li>
                        <li><a href="#"><!--<i class="icon-bell"></i>-->  Ledger</a></li>
                        <li><a href="#"><!--<i class="icon-bell"></i>-->  Public</a></li>
                        <li><a href="#"><!--<i class="icon-bell"></i>-->  Ranking</a></li>
                        <li><a href="javascript:;"><i class="icon-folder-close"></i> Stratification <span class="arrow"></span></a>
                           <ul class="sub-menu">
                              <li><a href="#"><!--<i class="icon-remove"></i-->> Basic</a></li>
                              <li><a href="#"><!--<i class="icon-pencil"></i>--> Sector</a></li>
                              <li><a href="#"><!--<i class="icon-edit"></i>--> Style</a></li>
                              <li><a href="#"><!--<i class="icon-edit"></i>--> Activity</a></li>
                              <li><a href="#"><!--<i class="icon-edit"></i>--> Fundamentals</a></li>
                              <li><a href="#"><!--<i class="icon-edit"></i>--> m100 Trading</a></li>
                              <li><a href="#"><!--<i class="icon-edit"></i>--> Performance</a></li>
                              <li><a href="#"><!--<i class="icon-edit"></i>--> Rationale</a></li>
                           </ul>
                        </li>
                        <li><a href="#"><!--<i class="icon-bell"></i>-->  Timing</a></li>
                        <li><a href="#"><!--<i class="icon-bell"></i> --> Top Ten</a></li>
                        <li><a href="#"><!--<i class="icon-bell"></i>-->  Trades</a></li>
                        <li><a href="#"><!--<i class="icon-bell"></i>-->  Volatility</a></li>
                        <li><a href="#"><!--<i class="icon-bell"></i> --> My Holdings</a></li>
                     </ul>
                  </li>
                  <!--END Fund 3-->
                  
                  <li <?php /*?>style="border-top:1px solid #666666;border-bottom:1px solid #666666;"<?php */?>>
                     <a href="#">
                     <i class="icon-plus"></i>
                     Create New Fund
                     </a>
                  </li>
               </ul>
            </li>
            <!--END MY FUNDS-->
            
            <!--START COMMUNITY-->
            <?php 
            $navMark2 = array("profiles","forums","chat","blog");
			?>
            <li class="<?php if (in_array($pageID, $navMark2)) {?>active<?php }?>" id="nav-community">
               <a href="javascript:;">
               <i class="icon-group"></i> 
               <span class="title">Community</span>
               <span class="arrow "></span>
               </a>
               <ul class="sub-menu">
                  
                  <li <?php if($pageID == "profiles") {?>class="active"<?php }?>>
                     <a href="#">
                     <span class="badge badge-important">14</span>Profiles</a>
                  </li>
                  <li <?php if($pageID == "forums") {?>class="active"<?php }?>>
                     <a href="#">
                     Discussions(Forums)</a>
                  </li>
                  <li <?php if($pageID == "chat") {?>class="active"<?php }?>>
                     <a href="#">
                     <span class="badge badge-important">3</span>Chat Rooms</a>
                  </li>
                  <li <?php if($pageID == "blog") {?>class="active"<?php }?>>
                     <a href="#">
                     <span class="badge badge-important">3</span>Blog</a>
                  </li>
                 
               </ul>
            </li>
            <!--END COMMUNITY-->
            
            <!--START RESEARCH-->
            <li id="nav-research">
               <a href="javascript:;">
               <i class="icon-globe"></i> 
               <span class="title">Research</span>
               <span class="arrow "></span>
               </a>
               <ul class="sub-menu">
               	  <li>
                     <a href="#">
                     <!--<i class="icon-folder-open"></i>-->
                     Watch List
                     </a>
                  </li>
                  
                  <!--START Stock Info-->
                  <li>
                     <a href="javascript:;" style="color:#ffffff;">
                     <i class="icon-folder-close"></i> 
                     Stock Info
                     <span class="arrow"></span>
                     </a>
                     <ul class="sub-menu">
                     	<li><a href="#"><i class="icon-user"></i>  Top 10</a></li>
                        <li><a href="#"><i class="icon-user"></i>  Stock Info</a></li>
                        <li><a href="#"><i class="icon-user"></i>  Corporate Actions</a></li>
                        <li><a href="#"><i class="icon-user"></i>  Graphs</a></li>
                        <li><a href="#"><i class="icon-user"></i>  Related Stocks</a></li>
                        <li><a href="#"><i class="icon-user"></i>  Tickets</a></li>
                        <li><a href="#"><i class="icon-user"></i>  Trade Stock</a></li>
                        <li><a href="#"><i class="icon-user"></i>  Volatility</a></li>
                     </ul>
                  </li>
                  <!--END Stock Info-->
                  
               	  <!--START Weekly Insight-->
                  <li>
                     <a href="javascript:;" style="color:#ffffff;">
                     <i class="icon-folder-close"></i> 
                     Weekly Insight
                     <span class="arrow"></span>
                     </a>
                     <ul class="sub-menu">
                     	<li><a href="#"><i class="icon-user"></i>  More Info</a></li>
                        <li><a href="#"><i class="icon-user"></i>  Overview</a></li>
                        <li><a href="#"><i class="icon-user"></i>  Trades</a></li>
                        <li><a href="#"><i class="icon-user"></i>  Positions</a></li>
                        <li><a href="#"><i class="icon-user"></i>  m100 Index</a></li>
                        <li><a href="#"><i class="icon-user"></i>  Subscribe Now</a></li>
                     </ul>
                  </li>
                  <!--END Weekly Insight-->
                  
               </ul>
            </li>
            <!--END RESEARCH-->
            
            <!--START RANKINGS-->
            <li id="nav-rankings">
               <a href="javascript:;">
               <i class="icon-bar-chart"></i> 
               <span class="title">Rankings</span>
               <span class="arrow "></span>
               </a>
               <ul class="sub-menu">
                  
                  <!--START mRankings-->
                  <li>
                     <a href="javascript:;" style="color:#ffffff;">
                     <i class="icon-folder-close"></i> 
                     mRankings
                     <span class="arrow"></span>
                     </a>
                     <ul class="sub-menu">
                     	<li><a href="#"><i class="icon-user"></i>  m100 Index</a></li>
                        <li><a href="#"><i class="icon-user"></i>  m10 Index</a></li>
                     </ul>
                  </li>
                  <!--END mRankings-->
                  
               	  <!--START Top Rankings-->
                  <li>
                     <a href="javascript:;" style="color:#ffffff;">
                     <i class="icon-folder-close"></i> 
                     Top Rankings
                     <span class="arrow"></span>
                     </a>
                     <ul class="sub-menu">
                     	<li><a href="#"><i class="icon-user"></i>  Overview</a></li>
                        <li><a href="#"><i class="icon-user"></i>  5 Years</a></li>
                        <li><a href="#"><i class="icon-user"></i>  4 Years</a></li>
                        <li><a href="#"><i class="icon-user"></i>  3 Years</a></li>
                        <li><a href="#"><i class="icon-user"></i>  2 Years</a></li>
                        <li><a href="#"><i class="icon-user"></i>  1 Year</a></li>
                        <li><a href="#"><i class="icon-user"></i>  6 Months</a></li>
                        <li><a href="#"><i class="icon-user"></i>  3 Months</a></li>
                        <li><a href="#"><i class="icon-user"></i>  30 Days</a></li>
                     </ul>
                  </li>
                  <!--END Top Rankings-->
                  
                  <!--START Sector Rankings-->
                  <li>
                     <a href="javascript:;" style="color:#ffffff;">
                     <i class="icon-folder-close"></i> 
                     Sector Rankings
                     <span class="arrow"></span>
                     </a>
                     <ul class="sub-menu">
                        <li><a href="#"><i class="icon-user"></i>  3 Years</a></li>
                        <li><a href="#"><i class="icon-user"></i>  2 Years</a></li>
                        <li><a href="#"><i class="icon-user"></i>  1 Year</a></li>
                        <li><a href="#"><i class="icon-user"></i>  6 Months</a></li>
                        <li><a href="#"><i class="icon-user"></i>  3 Months</a></li>
                        <li><a href="#"><i class="icon-user"></i>  30 Days</a></li>
                     </ul>
                  </li>
                  <!--END Sector Rankings-->
                  
                  <!--START Style Rankings-->
                  <li>
                     <a href="javascript:;" style="color:#ffffff;">
                     <i class="icon-folder-close"></i> 
                     Style Rankings
                     <span class="arrow"></span>
                     </a>
                     <ul class="sub-menu">
                     	<li><a href="#"><i class="icon-user"></i>  4 Years</a></li>
                        <li><a href="#"><i class="icon-user"></i>  3 Years</a></li>
                        <li><a href="#"><i class="icon-user"></i>  2 Years</a></li>
                        <li><a href="#"><i class="icon-user"></i>  1 Year</a></li>
                        <li><a href="#"><i class="icon-user"></i>  6 Months</a></li>
                        <li><a href="#"><i class="icon-user"></i>  3 Months</a></li>
                        <li><a href="#"><i class="icon-user"></i>  30 Days</a></li>
                     </ul>
                  </li>
                  <!--END Style Rankings-->
                  
                  <li>
                     <a href="#"><!--<i class="icon-folder-open"></i>-->Short Rankings</a>
                  </li>
                  
               </ul>
            </li>
            <!--END RANKINGS-->
            
            <!--START LEARN (TRADE SCHOOL)-->
            <li style="border-top: 5px solid #414247;" id="nav-trade-school">
               <a href="javascript:;">
               <i class="icon-book"></i> 
               <span class="title">Trade School</span>
               <span class="arrow "></span>
               </a>
               <ul class="sub-menu">
                  
                  <!--START Investing Insights-->
                  <li>
                     <a href="javascript:;" style="color:#ffffff;">
                     <i class="icon-folder-close"></i> 
                     Investing Insights
                     <span class="arrow"></span>
                     </a>
                     <ul class="sub-menu">
                     	<li><a href="#"><i class="icon-user"></i>  Master Insights</a></li>
                        <li><a href="#"><i class="icon-user"></i>  Member Insights</a></li>
                        <li><a href="#"><i class="icon-user"></i>  Ken's Insights</a></li>
                     </ul>
                  </li>
                  <!--END Investing Insights-->
                  
                  <li>
                     <a href="#"><!--<i class="icon-folder-open"></i>-->Terminology</a>
                  </li>
                  
                  <li>
                     <a href="#"><!--<i class="icon-folder-open"></i>-->FAQ</a>
                  </li>
                  
               	  <!--START Tutorials-->
                  <li>
                     <a href="javascript:;" style="color:#ffffff;">
                     <i class="icon-folder-close"></i> 
                     Tutorials
                     <span class="arrow"></span>
                     </a>
                     <ul class="sub-menu">
                     	<li><a href="#"><i class="icon-user"></i>  System Walkthrough</a></li>
                        <li><a href="#"><i class="icon-user"></i>  Making A Trade</a></li>
                        <li><a href="#"><i class="icon-user"></i>  Start A Fund</a></li>
                        <li><a href="#"><i class="icon-user"></i>  Profile Creation</a></li>
                     </ul>
                  </li>
                  <!--END Tutorials-->
                  
               </ul>
            </li>
            <!--END LEARN (TRADE SCHOOL)-->
            
            <!--START SUPPORT-->
            <li style="border-top: 5px solid #414247;" id="nav-support">
               <a href="javascript:;">
               <i class="icon-briefcase"></i> 
               <span class="title">Support</span>
               <span class="arrow "></span>
               </a>
               <ul class="sub-menu">
                  
                  <!--START Investing Insights-->
                  <li>
                     <a href="javascript:;" style="color:#ffffff;">
                     <i class="icon-tags"></i> 
                     Support Tickets
                     <span class="arrow"></span>
                     </a>
                     <ul class="sub-menu">
                     	<li><a href="#"><!--<i class="icon-user"></i>-->  View Active</a></li>
                        <li><a href="#"><!--<i class="icon-user"></i>-->  Create New</a></li>
                        <li><a href="#"><!--<i class="icon-user"></i>-->  Resolved</a></li>
                     </ul>
                  </li>
                  <!--END Investing Insights-->
                  
                  <li>
                     <a href="#"><!--<i class="icon-folder-open"></i>-->Contact Us</a>
                  </li>
                  
                  <li>
                     <a href="#"><!--<i class="icon-folder-open"></i>-->Support Chat</a>
                  </li>
                  
                  <li>
                     <a href="#"><!--<i class="icon-folder-open"></i>-->Support Forum</a>
                  </li>
                                    
               </ul>
            </li>
            <!--END SUPPORT-->
            
            <!--START POLICIES-->
            <li id="nav-policies">
               <a href="javascript:;">
               <i class="icon-file-text"></i> 
               <span class="title">Policies</span>
               <span class="arrow "></span>
               </a>
               <ul class="sub-menu">
                  
                  <!--START Investing Insights-->
                  <li>
                     <a href="javascript:;" style="color:#ffffff;">
                     <i class="icon-folder-close"></i> 
                     Rules
                     <span class="arrow"></span>
                     </a>
                     <ul class="sub-menu">
                     	<li><a href="#"><!--<i class="icon-user"></i>  -->Compliance Rules</a></li>
                        <li><a href="#"><!--<i class="icon-user"></i> --> General Rules</a></li>
                        <li><a href="#"><!--<i class="icon-user"></i>  -->User Agreement</a></li>
                     </ul>
                  </li>
                  <!--END Investing Insights-->
                  
                  <li>
                     <a href="#"><!--<i class="icon-folder-open"></i>-->Terms Of Use</a>
                  </li>
                  
                  <li>
                     <a href="#"><!--<i class="icon-folder-open"></i>-->Privacy</a>
                  </li>
                  
                  <li>
                     <a href="#"><!--<i class="icon-folder-open"></i>-->Rules of Conduct</a>
                  </li>
                                    
               </ul>
            </li>
            <!--END POLICIES-->
            <li <?php if($pageID == "user-profile") {?>class="active"<?php } ?>style="border-top: 5px solid #414247;" id="nav-profile">
               <a href="profile.php">
               <i class="icon-user"></i> 
               <span class="title">Profile</span>
               </a>
            </li>
            <li class="last " style="border-bottom:1px solid #292a2d;" id="nav-logout">
               <a href="logout">
               <i class="icon-signout"></i> 
               <span class="title">Logout</span>
               </a>
            </li>
            <!--END MAIN LEVEL NAV-->
            <?php } ?>
            
         </ul>
         <!-- END SIDEBAR MENU -->
         
