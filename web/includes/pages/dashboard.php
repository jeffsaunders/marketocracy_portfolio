
        <?php 
		if($_SESSION['admin'] == '1'){
        	if($_SESSION['username'] == 'bmccarthy'){
				echo '<pre>';
				
				//$memberData				= new member($dbPortfolio, $dbFeed, $_SESSION['member_id']);
				
				
				
				
				
				
				//$fundStuff = new fund($dbPortfolio, $dbFeed, '2-1');
				
				//echo $fundStuff->memberID;
						
				/*foreach($aFundIDs as $key=>$fundID){
					
					$getFund = new fund($dbPortfolio, $dbFeed, $fundID);
				
					echo $getFund->memberID;
					
					
					$fundExpired = $getFund->isFundExpired();
					
					if($fundExpired == true){
						$isExpired = true;	
					}
						
				}
				
				//print_r($memberData);
				if($isExpired == true){
					echo 'is expired';
				}else{
					echo 'is not expired';
				}*/
				
				
				//if($_SESSION['subscription']['maxFundValid'] == true){
				//	echo $pageID;	
				//}
				//print_r($_SESSION['subscription']);
				
				//print_r(subscription($mLink,'2',true,false));
        		//print_r($_SESSION['show-login-error']);
        		echo '</pre>';
			}
         }
		 ?>
        <!-- BEGIN MARKET STATUS -->
		<div class="row">
			<div class="col-md-12 col-sm-12">
				<div class="portlet">
					<div class="portlet-title">
						<div class="caption">
							<div style="float:left;" id="market-status"><?php // Populated by market-status-ajax.php ?></div>
							<!--<div style="float:right; position:absolute; right:30px;">15 Minute Delay</div>-->
							<div class="clearfix"></div>
						</div><!--caption-->
					</div><!--portlet-title-->
				</div>
			</div>
		</div>
		<!-- END MARKET STATUS -->

		 <!-- BEGIN OVERVIEW STATISTIC BARS-->
         <div class="row stats-overview-cont">
         	<div class="col-sm-4 hidden-lg hidden-md hidden-sm visible-xs" id="details-mobile"></div>
         	<div class="col-md-2 col-sm-4 hidden-xs" id="details-sp500">
                <?php // SP500 INDEX FEED: AJAX ?>
            </div>
            <div class="col-md-2 col-sm-4 hidden-xs" id="details-nasdaq">
                <?php // NASDAQ INDEX FEED: AJAX ?>
            </div>
            <div class="col-md-2 col-sm-4 hidden-xs" id="details-djia">
                <?php // DJIA INDEX FEED: AJAX ?>
            </div>
            <div class="col-md-2 col-sm-4 hidden-xs" id="details-nyse">
                <?php // NYSE INDEX FEED: AJAX ?>
            </div>
            <div class="col-md-2 col-sm-4 hidden-xs" id="details-rut">
                <?php // RUSSELL 2000 INDEX FEED: AJAX ?>
            </div>
<!--
            <div class="col-md-2 col-sm-4 hidden-xs" id="details-w5000">
                <?php // WILSHIRE 5000 INDEX FEED: AJAX ?>
            </div>
-->
            <div class="col-md-2 col-sm-4 hidden-xs" id="details-rua">
                <?php // RUSSELL 3000 INDEX FEED: AJAX ?>
            </div>
        	<?php //include('includes/portlets/indices-sp500.php');?>
            <?php //include('includes/portlets/indices-nasdaq.php');?>
            <?php //include('includes/portlets/indices-djia.php');?>
            <?php //include('includes/portlets/indices-nyse.php');?>


            
            
         </div>
         <!-- END OVERVIEW STATISTIC BARS-->
         <div class="clearfix"></div>
         <div class="row" id="sortable_portlets">
         
         	<?php
			//Check to see if layout session is set, and check against its value
			if(!isset($_SESSION['layout']) or $_SESSION['layout'] == "2"){
				//start 2 column layout
				?>
				<!--START COLUMN LEFT-->
				<div class="col-md-6 col-sm-12 column sortable" id="col1">
				   <?php
				   $query = "
						SELECT dash_col1, dash_col2, dash_4col1, dash_4col2, dash_4col3, dash_4col4
						FROM ".$_SESSION['members_settings_table']."
						WHERE member_id=:member_id
					";
	
					//Fund Symbols
					try{
						$rsDashOrder = $mLink->prepare($query);
						$aValues = array(
							':member_id' => $_SESSION['member_id']
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsDashOrder->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
					$rowOrder = $rsDashOrder->fetch(PDO::FETCH_ASSOC);

					//Set Var for Undo Function
					$undoColumns = "col1=".$rowOrder['dash_col1']."&col2=".$rowOrder['dash_col2']."&4col1=".$rowOrder['dash_4col1']."&4col2=".$rowOrder['dash_4col2']."&4col3=".$rowOrder['dash_4col3']."&4col4=".$rowOrder['dash_4col4']."";
					
					
					if(!empty($rowOrder['dash_col1'])){
						$column1 = explode('|', $rowOrder['dash_col1']);
						
						/*//If first value in array is empty/blank remove it from array
						if($column1[0] == ''){
							unset($column1[0]);
						}
						
						//If last value in array is empty/blank remove it from array
						if(end($column1) == ""){
							array_pop($column1);	
						}*/
					}
					
					foreach($column1 as $key => $value) {
						//Expand portlet ID and set variables
						$portletID 	= $value;
						$portlet 	= explode("~",$portletID);
						//Set Variables
						$portID		= $portlet[0];
						if($portlet[1] != "0"){
							$portVar1	= $portlet[1];
							
							
							$fundStatus = fund_status($mLink,$portVar1);
							
						}else{
							$fundStatus = '1';
						}
						
						if($portlet[2] != "0"){
							$portVar2	= $portlet[2];
						}
						if($portlet[3] != "0"){
							$portVar3	= $portlet[3];
						}
						
						if($fundStatus == '1'){
							include('includes/portlets/'.$portID.'.php');
						}
					}
					
					//If area is empty add portlet placeholder
					if($rowOrder['dash_col2'] == ""){?>
						<!-- BEGIN PORTLET CALENDAR-->
						<div class="portlet">
						  <div class="portlet-title handle">
							 <div class="caption"></div>
						  </div>
						</div>
						<!-- END PORTLET ALENDAR-->	
                        <?php
					}
					?>
				   
				</div><!--dash-col-left-->
				<!--END COLUMN LEFT-->
				
				<!--START COLUMN RIGHT-->
				<div class="col-md-6 col-sm-12 column sortable" id="col2">
				   <?php
				   if(!empty($rowOrder['dash_col2'])){
				   		$column2 = explode('|', $rowOrder['dash_col2']);
						
						//If last value in array is empty/blank remove it from array
						if(end($column2) == ""){
							array_pop($column2);	
						}
				   }
					
					foreach($column2 as $key => $value) {
						//Expand portlet ID and set variables
						$portletID 	= $value;
						
						$portlet 	= explode("~",$portletID);
						//Set Variables
						$portID		= $portlet[0];
						if($portlet[1] != "0"){
							$portVar1	= $portlet[1];
							
							
							$fundStatus = fund_status($mLink,$portVar1);
							
							
						}else{
							$fundStatus = '1';
						}
						if($portlet[2] != "0"){
							$portVar2	= $portlet[2];
						}
						if($portlet[3] != "0"){
							$portVar3	= $portlet[3];
						}
						
						if($fundStatus == '1'){
							include('includes/portlets/'.$portID.'.php');
						}
					}
					
					//If area is empty add portlet placeholder
					if($rowOrder['dash_col2'] == ""){?>
						<!-- BEGIN PORTLET CALENDAR-->
						<div class="portlet">
						  <div class="portlet-title handle">
							 <div class="caption"></div>
						  </div>
						</div>
						<!-- END PORTLET ALENDAR-->	
                        <?php
					}
				   ?>  
				</div><!--END Column-->
            <?php
				
			}elseif($_SESSION['layout'] == "4"){
            
				//start 4 column layout
				?>
				<!--START COLUMN ONE-->
				<div class="col-md-3 col-sm-12 column sortable" id="col1">
				   <?php
				   $query = "
						SELECT dash_4col1, dash_4col2, dash_4col3, dash_4col4, dash_col1, dash_col2
						FROM ".$_SESSION['members_settings_table']."
						WHERE member_id=:member_id
					";

					//Fund Symbols
					try{
						$rsDashOrder = $mLink->prepare($query);
						$aValues = array(
							':member_id' => $_SESSION['member_id']
						);
						$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
						$rsDashOrder->execute($aValues);
					}
					catch(PDOException $error){
						// Log any error
						file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
					}
					
					$rowOrder = $rsDashOrder->fetch(PDO::FETCH_ASSOC);
					
					//Set var for undo feature
					$undoColumns = "col1=".$rowOrder['dash_col1']."&col2=".$rowOrder['dash_col2']."&4col1=".$rowOrder['dash_4col1']."&4col2=".$rowOrder['dash_4col2']."&4col3=".$rowOrder['dash_4col3']."&4col4=".$rowOrder['dash_4col4']."";
					
					if(!empty($rowOrder['dash_4col1'])){
						$column1 = explode('|', $rowOrder['dash_4col1']);
						
						//If last value in array is empty/blank remove it from array
						if(end($column1) == ""){
							array_pop($column1);	
						}
					}
					
					foreach($column1 as $key => $value) {
						//Expand portlet ID and set variables
						$portletID 	= $value;
						$portlet 	= explode("~",$portletID);
						//Set Variables
						$portID		= $portlet[0];
						if($portlet[1] != "0"){
							$portVar1	= $portlet[1];
							
							$fundStatus = fund_status($mLink,$portVar1);
						}else{
							$fundStatus = '1';
						}
						
						if($portlet[2] != "0"){
							$portVar2	= $portlet[2];
						}
						if($portlet[3] != "0"){
							$portVar3	= $portlet[3];
						}
						
						if($fundStatus == '1'){
							include('includes/portlets/'.$portID.'.php');
						}
					}
					
					//If area is empty add portlet placeholder
					if($rowOrder['dash_4col1'] == ""){?>
						<!-- BEGIN PORTLET CALENDAR-->
						<div class="portlet">
						  <div class="portlet-title handle">
							 <div class="caption"></div>
						  </div>
						</div>
						<!-- END PORTLET ALENDAR-->	
                        <?php
					}
					?>
				   
				</div><!--dash-col-left-->
				<!--END COLUMN ONE-->
            
                <!--START COLUMN TWO-->
                <div class="col-md-3 col-sm-12 column sortable" id="col2">
                   	<?php
				   	if(!empty($rowOrder['dash_4col2'])){
                    	$column2 = explode('|', $rowOrder['dash_4col2']);
						
						//If last value in array is empty/blank remove it from array
						if(end($column2) == ""){
							array_pop($column2);	
						}
					}
                    
                    foreach($column2 as $key => $value) {
                        //Expand portlet ID and set variables
                        $portletID 	= $value;
                        $portlet 	= explode("~",$portletID);
                        //Set Variables
                        $portID		= $portlet[0];
                        if($portlet[1] != "0"){
                            $portVar1	= $portlet[1];
							
							
							$fundStatus = fund_status($mLink,$portVar1);
                        }else{
							$fundStatus = '1';
						}
						
                        if($portlet[2] != "0"){
                            $portVar2	= $portlet[2];
                        }
                        if($portlet[3] != "0"){
                            $portVar3	= $portlet[3];
                        }
                       
                        if($fundStatus == '1'){
							include('includes/portlets/'.$portID.'.php');
						}
                    }
					
					//If area is empty add portlet placeholder
					if($rowOrder['dash_4col2'] == ""){?>
						<!-- BEGIN PORTLET CALENDAR-->
						<div class="portlet">
						  <div class="portlet-title handle">
							 <div class="caption"></div>
						  </div>
						</div>
						<!-- END PORTLET ALENDAR-->	
                        <?php
					}
                   ?>  
                </div><!--END Column-->
                <!--END COLUMN TWO-->
            
                <!--START COLUMN THREE-->
                <div class="col-md-3 col-sm-12 column sortable" id="col3">
                    <?php
                    if(!empty($rowOrder['dash_4col3'])){
                    	$column3 = explode('|', $rowOrder['dash_4col3']);
						
						//If last value in array is empty/blank remove it from array
						if(end($column3) == ""){
							array_pop($column3);	
						}
					}
                    
                    foreach($column3 as $key => $value) {
                        //Expand portlet ID and set variables
                        $portletID 	= $value;
                        $portlet 	= explode("~",$portletID);
                        //Set Variables
                        $portID		= $portlet[0];
                        if($portlet[1] != "0"){
                            $portVar1	= $portlet[1];
							$fundStatus = fund_status($mLink,$portVar1);
                        }else{
							$fundStatus = '1';
						}
						
                        if($portlet[2] != "0"){
                            $portVar2	= $portlet[2];
                        }
                        if($portlet[3] != "0"){
                            $portVar3	= $portlet[3];
                        }
						
						
                        if($value != ""){
                        	if($fundStatus == '1'){
								include('includes/portlets/'.$portID.'.php');
							}
						}
                    }
					
					//If area is empty add portlet placeholder
					if($rowOrder['dash_4col3'] == ""){?>
						<!-- BEGIN PORTLET CALENDAR-->
						<div class="portlet">
						  <div class="portlet-title handle">
							 <div class="caption"></div>
						  </div>
						</div>
						<!-- END PORTLET ALENDAR-->	
                        <?php
					}
                    ?>
                   
                </div><!--dash-col-left-->
                <!--END COLUMN THREE-->
            
                <!--START COLUMN FOUR-->
                <div class="col-md-3 col-sm-12 column sortable" id="col4">
                   	<?php
				   	if(!empty($rowOrder['dash_4col4'])){
                    	$column4 = explode('|', $rowOrder['dash_4col4']);
						
						//If last value in array is empty/blank remove it from array
						if(end($column4) == ""){
							array_pop($column4);	
						}
					}
                    
                    foreach($column4 as $key => $value) {
                        //Expand portlet ID and set variables
                        $portletID 	= $value;
                        $portlet 	= explode("~",$portletID);
                        //Set Variables
                        $portID		= $portlet[0];
                        if($portlet[1] != "0"){
                            $portVar1	= $portlet[1];
							$fundStatus = fund_status($mLink,$portVar1);
                        }else{
							$fundStatus = '1';
						}
						
                        if($portlet[2] != "0"){
                            $portVar2	= $portlet[2];
                        }
                        if($portlet[3] != "0"){
                            $portVar3	= $portlet[3];
                        }
						
                        if($fundStatus == '1'){
							include('includes/portlets/'.$portID.'.php');
						}
                    }
					
					//If area is empty add portlet placeholder
					if($rowOrder['dash_4col4'] == ""){?>
						<!-- BEGIN PORTLET CALENDAR-->
						<div class="portlet">
						  <div class="portlet-title handle">
							 <div class="caption"></div>
						  </div>
						</div>
						<!-- END PORTLET ALENDAR-->	
                        <?php
					}
                   	?>
                   
                     
                </div><!--END Column-->
                <!--END COLUMN FOUR-->
            <?php
			}
			?>
         </div><!--END Row-->
         
         <div class="clearfix"></div>
         