		 <!-- BEGIN OVERVIEW STATISTIC BARS-->
         <div class="row stats-overview-cont">
            <div class="col-md-2 col-sm-4">
               <div class="stats-overview stat-block">
                  <div class="display stat bad huge">
                     <span class="line-chart">5, 6, 7, 11, 14, 10, 15, 19, 15, 2</span>
                     <div class="percent">-0.25%</div>
                  </div>
                  <div class="details">
                     <div class="title">NASDAQ</div>
                     <div class="numbers">4,121.53</div>
                  </div>
                  <div class="progress">
                     <span style="width: 100%;" class="progress-bar progress-bar-danger" aria-valuenow="66" aria-valuemin="0" aria-valuemax="100">
                     <span class="sr-only">66% Complete</span>
                     </span>
                  </div>
               </div>
            </div>
            <div class="col-md-2 col-sm-4">
               <div class="stats-overview stat-block">
                  <div class="display stat bad huge">
                     <span class="line-chart">2,6,8,12, 11, 15, 16, 11, 16, 11, 10, 3, 7, 8, 12, 19</span>
                     <div class="percent">-0.05%</div>
                  </div>
                  <div class="details">
                     <div class="title">S&P 500</div>
                     <div class="numbers">1,830.44</div>
                     <div class="progress">
                        <span style="width: 100%;" class="progress-bar progress-bar-danger" aria-valuenow="0.05" aria-valuemin="0" aria-valuemax="100">
                        <span class="sr-only">0.05% Complete</span>
                        </span>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col-md-2 col-sm-4">
               <div class="stats-overview stat-block">
                  <div class="display stat bad huge">
                     <span class="line-chart">2,6,8,11, 14, 11, 12, 13, 15, 12, 9, 5, 11, 12, 15, 9,3</span>
                     <div class="percent">-0.01%</div>
                  </div>
                  <div class="details">
                     <div class="title">DJIA</div>
                     <div class="numbers">16,469.03</div>
                     <div class="progress">
                        <span style="width: 100%;" class="progress-bar progress-bar-danger" aria-valuenow="16" aria-valuemin="0" aria-valuemax="100">
                        <span class="sr-only">62% Complete</span>
                        </span>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col-md-2 col-sm-4">
               <div class="stats-overview stat-block">
                  <div class="display stat good huge">
                     <span class="bar-chart">1,4,9,12, 10, 11, 12, 15, 12, 11, 9, 12, 15, 19, 14, 13, 15</span>
                     <div class="percent">+86%</div>
                  </div>
                  <div class="details">
                     <div class="title">jdoe:JDM</div>
                     <div class="numbers">1550</div>
                     <div class="progress">
                        <span style="width: 100%;" class="progress-bar progress-bar-success" aria-valuenow="56" aria-valuemin="0" aria-valuemax="100">
                        <span class="sr-only">56% Complete</span>
                        </span>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col-md-2 col-sm-4">
               <div class="stats-overview stat-block">
                  <div class="display stat good huge">
                     <span class="line-chart">2,6,8,12, 11, 15, 16, 17, 14, 12, 10, 8, 10, 2, 4, 12, 19</span>
                     <div class="percent">+72%</div>
                  </div>
                  <div class="details">
                     <div class="title">jdoe:JDHMF</div>
                     <div class="numbers">9600</div>
                     <div class="progress">
                     	<?php /* progress-bar-: succes, warning, danger  */?>
                        <span style="width: 100%;" class="progress-bar progress-bar-warning" aria-valuenow="72" aria-valuemin="0" aria-valuemax="100">
                        <span class="sr-only">72% Complete</span>
                        </span>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col-md-2 col-sm-4">
               <div class="stats-overview stat-block">
                  <div class="display stat bad huge">
                     <span class="line-chart">1,7,9,11, 14, 12, 6, 7, 4, 2, 9, 8, 11, 12, 14, 12, 10</span>
                     <div class="percent">+15%</div>
                  </div>
                  <div class="details">
                     <div class="title">jdoe:JDJD</div>
                     <div class="numbers">2090</div>
                     <div class="progress">
                        <span style="width: 100%;" class="progress-bar progress-bar-danger" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100">
                        <span class="sr-only">15% Complete</span>
                        </span>
                     </div>
                  </div>
               </div>
            </div>
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
					
					$column1 = explode('|', $rowOrder['dash_col1']);
					
					foreach($column1 as $key => $value) {
						//Expand portlet ID and set variables
						$portletID 	= $value;
						$portlet 	= explode("~",$portletID);
						//Set Variables
						$portID		= $portlet[0];
						if($portlet[1] != "0"){
							$portVar1	= $portlet[1];
						}
						if($portlet[2] != "0"){
							$portVar2	= $portlet[2];
						}
						if($portlet[3] != "0"){
							$portVar3	= $portlet[3];
						}
						
						include('includes/portlets/'.$portID.'.php');
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
				   $column2 = explode('|', $rowOrder['dash_col2']);
					
					foreach($column2 as $key => $value) {
						//Expand portlet ID and set variables
						$portletID 	= $value;
						$portlet 	= explode("~",$portletID);
						//Set Variables
						$portID		= $portlet[0];
						if($portlet[1] != "0"){
							$portVar1	= $portlet[1];
						}
						if($portlet[2] != "0"){
							$portVar2	= $portlet[2];
						}
						if($portlet[3] != "0"){
							$portVar3	= $portlet[3];
						}
						
						include('includes/portlets/'.$portID.'.php');
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
					
					$column1 = explode('|', $rowOrder['dash_4col1']);
					
					foreach($column1 as $key => $value) {
						//Expand portlet ID and set variables
						$portletID 	= $value;
						$portlet 	= explode("~",$portletID);
						//Set Variables
						$portID		= $portlet[0];
						if($portlet[1] != "0"){
							$portVar1	= $portlet[1];
						}
						if($portlet[2] != "0"){
							$portVar2	= $portlet[2];
						}
						if($portlet[3] != "0"){
							$portVar3	= $portlet[3];
						}
						
						include('includes/portlets/'.$portID.'.php');
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
                   $column2 = explode('|', $rowOrder['dash_4col2']);
                    
                    foreach($column2 as $key => $value) {
                        //Expand portlet ID and set variables
                        $portletID 	= $value;
                        $portlet 	= explode("~",$portletID);
                        //Set Variables
                        $portID		= $portlet[0];
                        if($portlet[1] != "0"){
                            $portVar1	= $portlet[1];
                        }
                        if($portlet[2] != "0"){
                            $portVar2	= $portlet[2];
                        }
                        if($portlet[3] != "0"){
                            $portVar3	= $portlet[3];
                        }
                        
                        include('includes/portlets/'.$portID.'.php');
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
                    
                    $column3 = explode('|', $rowOrder['dash_4col3']);
                    
                    foreach($column3 as $key => $value) {
                        //Expand portlet ID and set variables
                        $portletID 	= $value;
                        $portlet 	= explode("~",$portletID);
                        //Set Variables
                        $portID		= $portlet[0];
                        if($portlet[1] != "0"){
                            $portVar1	= $portlet[1];
                        }
                        if($portlet[2] != "0"){
                            $portVar2	= $portlet[2];
                        }
                        if($portlet[3] != "0"){
                            $portVar3	= $portlet[3];
                        }
                        
                        include('includes/portlets/'.$portID.'.php');
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
                   $column4 = explode('|', $rowOrder['dash_4col4']);
                    
                    foreach($column4 as $key => $value) {
                        //Expand portlet ID and set variables
                        $portletID 	= $value;
                        $portlet 	= explode("~",$portletID);
                        //Set Variables
                        $portID		= $portlet[0];
                        if($portlet[1] != "0"){
                            $portVar1	= $portlet[1];
                        }
                        if($portlet[2] != "0"){
                            $portVar2	= $portlet[2];
                        }
                        if($portlet[3] != "0"){
                            $portVar3	= $portlet[3];
                        }
                        
                        include('includes/portlets/'.$portID.'.php');
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
         