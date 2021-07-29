				<ul class="page-breadcrumb breadcrumb" id="nav-breadcrumb">
                  <li>
                     <i class="icon-dashboard"></i>
                     <a href="/">Dashboard</a>
                     <?php if($pageID != $_SESSION['home_page']){?><i class="icon-angle-right"></i><?php } ?>
                  </li>
                  
                  <?php 
				  if($section != "01"/*Dashboard*/) { ?>
				  <li><a href="<?php echo $secURL;?>"><?php echo $secName; ?></a><i class="icon-angle-right"></i></li>
				  <?php } ?>
                  
                  <?php
				  //Check if there is a sub-section 
				  if($subSec1 != "00") {
					  	//Check to see if sub-section is "My Funds" 
                  		if($section == "03"/*My Funds*/) { ?>
							<li>
							  <select onChange="location = this.options[this.selectedIndex].value;">
								  <option><?php echo $fundSymbol; ?></option>
								  <option disabled>----------</option>
                                  <?php 
								  $query = "
									  SELECT fund_symbol, fund_id 
									  FROM ".$_SESSION['fund_table']."
									  WHERE member_id=:member_id AND active=1
								  ";
		  
								  //Fund Symbols
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
								  
								  while($fundList = $rsGetFunds->fetch(PDO::FETCH_ASSOC)) {
									  $listFundSymbol = $fundList['fund_symbol'];
									  $listFundID = $fundList['fund_id'];
									  
									  if($listFundID != $fundID) {
										  $makeDisabled = '';
									  }else{
										  $makeDisabled = 'disabled';  
									  }
									  ?>
									  <option value="?page=<?php echo $pageID; ?>&fund=<?php echo $listFundID; ?>" <?php echo $makeDisabled;?>><?php echo $listFundSymbol; ?></option>
									  <?php
									  
								  }
								  
								  ?>
							  </select>
							<i class="icon-angle-right"></i>
							</li>
                            <?php
						}else{
						//If sub-section is not "My Funds" display normal	
				  		?>
				  		<li><a href="<?php echo $subSec1URL; ?>"><?php echo $subSec1Name; ?></a><i class="icon-angle-right"></i></li>
				  		<?php 
						}
				  } 
				  ?>
                  
                  <?php 
				  // Check to see if there is a sub-section-2
				  if($subSec2 != "00") { 
						// Check to see page is a child of a parent
						if($subSec2 == "cc") { 
							$query = "
								SELECT page_title, page_id, nav_custom
								FROM ".$_SESSION['pages_table'] ."
								WHERE page_child=:page_child AND hide_nav='0'
							";
							
							//Fund Symbols
							try{
								$rsParent = $mLink->prepare($query);
								$aValues = array(
									':page_child' 	=> $pageID
								);
								$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
								$rsParent->execute($aValues);
							}
							catch(PDOException $error){
								// Log any error
								file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
							}
							$parent = $rsParent->fetch(PDO::FETCH_ASSOC);
							
							if($parent['nav_custom'] == ""){
								$navListItem = $parent['page_title'];
							}else{
								$navListItem = $parent['nav_custom'];	
							}
							
							?>
							<li><a href="?page=<?php echo $parent['page_id']; ?>"><?php echo $navListItem; ?></a><i class="icon-angle-right"></i></li>
							<?php 
						}else{?>
						<li><a href="<?php echo $subSec2URL; ?>"><?php echo $subSec2Name; ?></a><i class="icon-angle-right"></i></li>
						<?php 
						}
				  }?>
                  
                  
                  
                  <?php 
				  if($subSec2 != "cc") {
				  	  //START SECTION DROP DOWN MENU
					  if($pageID != "01-00-00-001"/*dashboard*/ && substr($pageID, 0, 5) != "04-01") {?>
						  <li id="breadcrumb-drop">
							<select onChange="location = this.options[this.selectedIndex].value;" >
								<option value="?page=<?php echo $pageID; ?>"><?php echo $pageTitle; ?></option>
								<?php
								if(!isset($fundID)){
									?>	
									<option disabled>________________</option>
									<?php 
									$query = "
										SELECT page_title, page_id, hide_nav
										FROM ".$_SESSION['pages_table']."
										WHERE substr(page_id, 1, :stringCnt)=:section_id
									";
			
									//Section Name
									try{
										$rsGetPages = $mLink->prepare($query);
										
											$aValues = array(
												':section_id' => $fullSecID,
												':stringCnt'  => "8"
											);
										$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
										$rsGetPages->execute($aValues);
									}
									catch(PDOException $error){
										// Log any error
										file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
									}
									
									while($row4 = $rsGetPages->fetch(PDO::FETCH_ASSOC)) {
										$listPageTitle = $row4['page_title'];
										$listPageURL = $row4['page_id'];
										
										if($listPageURL != $pageID) {
											if($row4['hide_nav'] != "1"){
												?>
												<option value="?page=<?php echo $listPageURL;?>"><?php echo $listPageTitle; ?></option>
												<?php
											}
										}
									}//end while loop
								}
								?>
								
								<?php
								if(isset($fundID)){
									?>
									<option disabled>____________________</option>
									<option disabled style="color:#666666;">Fund Pages</option>
									<?php
									$query = "
										SELECT page_id, page_title
										FROM ".$_SESSION['pages_table']."
										WHERE substr(page_id, 1, 8)='03-01-00' AND active='1'
									";
									
									//Section Name
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
									
									while($fundPages2 = $rsGetPages->fetch(PDO::FETCH_ASSOC)) {
										$listPageTitle2	= $fundPages2['page_title'];
										$listPageID2 	= $fundPages2['page_id'];
										
										if($listPageID2 != $pageID) {
										?>
										<option value="?page=<?php echo $listPageID2;?>&fund=<?php echo $fundID; ?>"><?php echo $listPageTitle2; ?></option>
										<?php	
										}
									}
									
									
									$query = "
										SELECT section_name, section_id
										FROM ".$_SESSION['sections_table']."
										WHERE substr(section_id, 1, 5)='03-01' and active='1'
									";
									
									//Section Name
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
									
									while($fundSubSec = $rsGetSections->fetch(PDO::FETCH_ASSOC)) {
										$listSecName = $fundSubSec['section_name'];
										$listSecID	= $fundSubSec['section_id'];
										
										//Don't show empty sub sections
										if($listSecID != $fundSec) {
											?>
											<option disabled>____________________</option>
											<option disabled style="color:#666666;"><?php echo $listSecName; ?></option>
											
											<?php
											//GET PAGES FOR EACH SECTION
											$query = "
												SELECT page_title, page_id
												FROM ".$_SESSION['pages_table']."
												WHERE substr(page_id, 1, 8)=:section_id AND active='1'
											";
											
											//Section Name
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
											
											while($fundPages = $rsGetPages2->fetch(PDO::FETCH_ASSOC)) {
												$listPageTitle2	= $fundPages['page_title'];
												$listPageID2 	= $fundPages['page_id'];
												
												if($listPageID2 != $pageID) {
												?>
												<option value="?page=<?php echo $listPageID2;?>&fund=<?php echo $fundID; ?>"><?php echo $listPageTitle2; ?></option>
												<?php	
												}
											}
										}
									}//end while loop
								}
								?>
								
							</select>
						  </li>
						  <?php 
					  }//END SECTION DROP DOWN MENU
					  
					  if(substr($pageID, 0, 5) == "04-01"/*Forums*/){
						  
						  if(isset($topicID)){
							  ?>
                              <li><a href="?page=04-01-00-002&forum=<?php echo $forumID; ?>"><?php echo $forumMainTitle;?></a><i class="icon-angle-right"></i></li>
                              <li><a href="?page=04-01-00-003&forum=<?php echo $forumID; ?>&cat=<?php echo $catID; ?>"><?php echo $forumCatTitle;?></a><i class="icon-angle-right"></i></li>
                              <li><?php echo $forumTopicTitle;?></li>
                              <?php
						  }else{
						  
							  if(isset($catID)){
								  ?>
								  <li><a href="?page=04-01-00-002&forum=<?php echo $forumID; ?>"><?php echo $forumMainTitle;?></a><i class="icon-angle-right"></i></li>
								  <li id="breadcrumb-drop">
									<select onChange="location = this.options[this.selectedIndex].value;" >
										<option value="?page=04-01-00-003&forum=<?php echo $forumID; ?>&cat=<?php echo $catID; ?>"><?php echo $forumCatTitle; ?></option>
										<option disabled>____________________</option>
										<?php
										//QUERY:FORUM | GET FORUMS
										$query = "
											SELECT cat_title, cat_id, forum_id
											FROM ".$_SESSION['forum_categories_table']."
											WHERE forum_id=:forum_id
											ORDER BY sequence ASC
										";
										
										//START PDO
										try{
											$rsCat = $mLink->prepare($query);
											$aValues = array(
												':forum_id' => $forumID
											);
											
											$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
											$rsCat->execute($aValues);
										}
										catch(PDOException $error){
											// Log any error
											file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
										}
										//END PDO
										
										//START QUERY:CATEGORY LOOP 
										while($cat = $rsCat->fetch(PDO::FETCH_ASSOC)) {
											
											if($cat['cat_id'] != $catID){
												?>
												<option value="?page=04-01-00-003&forum=<?php echo $cat['forum_id']; ?>&cat=<?php echo $cat['cat_id'];?>"><?php echo $cat['cat_title']; ?></option>
												<?php
											}
											
										}//END QUERY
										
										?>
									</select>
								  </li>
								  <?php
								  
							  }else{
								  ?>
								  <li id="breadcrumb-drop">
									<select onChange="location = this.options[this.selectedIndex].value;" >
                                    	<?php
										if(!isset($forumID)){
											echo '<option>All</option>';	
										}else{
										?>
										<option value="?page=04-01-00-002&forum=<?php echo $forumID; ?>"><?php echo $forumMainTitle; ?></option>
                                        <?php } ?>
										<option disabled>____________________</option>
										<?php
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
											
											
											if($forum['forum_id'] != $forumID){
												?>
												<option value="?page=04-01-00-002&forum=<?php echo $forum['forum_id']; ?>"><?php echo $forumTitle; ?></option>
												<?php
											}
											
										}//END QUERY:09 LOOP
										?>
									</select>
								  </li>
								  <?php
							  }
						  }
					  }
				  }else{
					  if(isset($ticketID)){
					  	?>
                      	<li><?php echo $pageTitle; ?>: <?php echo $ticketID;?></li>
                      	<?php
					  }else{
						?>
                        <li><?php echo $pageTitle; ?></li>
                        <?php  
					  }
				  }
				  ?>
               </ul><!--nav-breadcrumb-->
               <!-- END PAGE TITLE & BREADCRUMB-->