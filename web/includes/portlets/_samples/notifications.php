<?php
//NOTIFICATIONS PORTLET

//portVar1 = NOT SET
//portVar2 = NOT SET
//portVar3 = NOT SET

/*$query = "
	SELECT fund_symbol, fund_id 
	FROM :table
	WHERE member_id=:member_id AND fund_id=:fund_id AND active=1
";

//Fund Symbols
try{
	$rsStocksFund = $mLink->prepare($query);
	$aValues = array(
		':member_id' 	=> $_SESSION['member_id'],
		':fund_id'		=> $portVar1,
		':table'		=> $_SESSION['fund_table']
	);
	$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
	$rsStocksFund->execute($aValues);
}
catch(PDOException $error){
	// Log any error
	file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
}

$stockFund = $rsStocksFund->fetch(PDO::FETCH_ASSOC)
*/

?>
<!--START PORTLET NOTIFICATIONS-->
<div class="portlet" id="<?php echo $portletID; ?>">
    <div class="portlet-title handle">
       <div class="caption"><i class="icon-bell"></i>Notifications</div>
       <div class="tools">
          <?php /*?><a href="#portlet-config" data-toggle="modal" class="config"></a>
          <a href="" class="remove"></a><?php */?>
          <a href="" class="reload"></a>
          <a href="" class="collapse"></a>
       </div>
    </div>
    <div class="portlet-body">
    	<!--BEGIN TABS-->
        <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_1_1" data-toggle="tab">All</a></li>
            <li><a href="#tab_1_2" data-toggle="tab">Corporate Actions</a></li>
            <li><a href="#tab_1_3" data-toggle="tab">System</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="tab_1_1">
                <div class="scroller" style="height: 250px;" data-always-visible="1" data-rail-visible="0">
                    <ul class="feeds">
                    	<?php
                    	$query = "
							SELECT *
							FROM ".$_SESSION['members_notification_table']."
							WHERE member_id=:member_id AND deleted='0'
							ORDER BY timestamp DESC
						";
						
						//Fund Symbols
						try{
							$rsSystemNote = $mLink->prepare($query);
							$aValues = array(
								':member_id' 	=> $_SESSION['member_id']
							);
							$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
							$rsSystemNote->execute($aValues);
						}
						catch(PDOException $error){
							// Log any error
							file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
						}
						
						while($systemNote = $rsSystemNote->fetch(PDO::FETCH_ASSOC)) {
						
						?>
                        <li>
                        	<?php
							if(!empty($systemNote['link'])){?><a href="<?php echo $systemNote['link'];?>"><?php } ?>
                            <div class="col1">
                              <div class="cont">
                                 <div class="cont-col1">
                                    <div class="label label-sm label-<?php echo $systemNote['status'];?>">                        
                                       <i class="icon-<?php echo $systemNote['icon'];?>"></i>
                                    </div>
                                 </div>
                                 <div class="cont-col2">
                                    <div class="desc">
                                       <?php if(!empty($systemNote['title'])){ echo "<strong>".$systemNote['title']."</strong> - ";} echo $systemNote['notification'];?>
                                       <?php if(!empty($systemNote['link'])){?>
                                       <span class="label label-sm label-<?php echo $systemNote['status'];?>">
                                       <i class="icon-share-alt"></i>
                                       </span>
                                       <?php } ?>   
                                    </div>
                                 </div>
                              </div>
                            </div>
                            <?php if(!empty($systemNote['link'])){?></a><?php }?>
                            <div class="col2">
                              <div class="date">
                                 <?php
								 echo time_past($systemNote['timestamp']);
								 ?>
                              </div>
                            </div>
                            
                        </li>
                        <?php
						}
						?>
                        <li>
                          <a href="#">
                             <div class="col1">
                                <div class="cont">
                                   <div class="cont-col1">
                                      <div class="label label-sm label-success">                        
                                         <i class="icon-bell"></i>
                                      </div>
                                   </div>
                                   <div class="cont-col2">
                                      <div class="desc">
                                         Place Holder Notification
                                         <span class="label label-sm label-success ">
                                         Success 
                                         <i class="icon-share-alt"></i>
                                         </span>   
                                      </div>
                                   </div>
                                </div>
                             </div>
                             <div class="col2">
                                <div class="date">
                                   20 mins
                                </div>
                             </div>
                          </a>
                        </li>
                        <li>
                           <div class="col1">
                              <div class="cont">
                                 <div class="cont-col1">
                                    <div class="label label-sm label-danger">                        
                                       <i class="icon-bell"></i>
                                    </div>
                                 </div>
                                 <div class="cont-col2">
                                    <div class="desc">
                                       Place Holder Notification - NO LINK
                                       <span class="label label-sm label-danger ">
                                       Danger 
                                       <i class="icon-share-alt"></i>
                                       </span>   
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="col2">
                              <div class="date">
                                 20 mins
                              </div>
                           </div>
                        </li>
                        <li>
                           <div class="col1">
                              <div class="cont">
                                 <div class="cont-col1">
                                    <div class="label label-sm label-warning">                        
                                       <i class="icon-bell"></i>
                                    </div>
                                 </div>
                                 <div class="cont-col2">
                                    <div class="desc">
                                       Place Holder Notification - NO LINK
                                       <span class="label label-sm label-warning ">
                                       Warning 
                                       <i class="icon-share-alt"></i>
                                       </span>   
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="col2">
                              <div class="date">
                                 20 mins
                              </div>
                           </div>
                        </li>
                        <li>
                           <div class="col1">
                              <div class="cont">
                                 <div class="cont-col1">
                                    <div class="label label-sm label-info">                        
                                       <i class="icon-bell"></i>
                                    </div>
                                 </div>
                                 <div class="cont-col2">
                                    <div class="desc">
                                       Place Holder Notification - NO LINK
                                       <span class="label label-sm label-info ">
                                       Info 
                                       <i class="icon-share-alt"></i>
                                       </span>   
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="col2">
                              <div class="date">
                                 20 mins
                              </div>
                           </div>
                        </li>
                        <li>
                           <div class="col1">
                              <div class="cont">
                                 <div class="cont-col1">
                                    <div class="label label-sm label-default">                        
                                       <i class="icon-bell"></i>
                                    </div>
                                 </div>
                                 <div class="cont-col2">
                                    <div class="desc">
                                       Place Holder Notification - NO LINK
                                       <span class="label label-sm label-default ">
                                       Default
                                       <i class="icon-share-alt"></i>
                                       </span>   
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="col2">
                              <div class="date">
                                 20 mins
                              </div>
                           </div>
                        </li>
                       
                    </ul>
                </div><!--scroller-->
            </div><!--tab-pane tab_1_1-->
            
            <div class="tab-pane" id="tab_1_2">
                <div class="scroller" style="height: 250px;" data-always-visible="1" data-rail-visible1="1">
                    <ul class="feeds">
                        <?php
                    	$query = "
							SELECT *
							FROM ".$_SESSION['members_notification_table']."
							WHERE member_id=:member_id AND deleted='0' AND type='corporate-action'
							ORDER BY timestamp DESC
						";
						
						//Fund Symbols
						try{
							$rsSystemNoteCA = $mLink->prepare($query);
							$aValues = array(
								':member_id' 	=> $_SESSION['member_id']
							);
							$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
							$rsSystemNoteCA->execute($aValues);
						}
						catch(PDOException $error){
							// Log any error
							file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
						}
						
						while($systemNote = $rsSystemNoteCA->fetch(PDO::FETCH_ASSOC)) {
						
						?>
                        <li>
                        	<?php
							if(!empty($systemNote['link'])){?><a href="<?php echo $systemNote['link'];?>"><?php } ?>
                            <div class="col1">
                              <div class="cont">
                                 <div class="cont-col1">
                                    <div class="label label-sm label-<?php echo $systemNote['status'];?>">                        
                                       <i class="icon-<?php echo $systemNote['icon'];?>"></i>
                                    </div>
                                 </div>
                                 <div class="cont-col2">
                                    <div class="desc">
                                       <?php if(!empty($systemNote['title'])){ echo "<strong>".$systemNote['title']."</strong> - ";} echo $systemNote['notification'];?>
                                       <?php if(!empty($systemNote['link'])){?>
                                       <span class="label label-sm label-<?php echo $systemNote['status'];?>">
                                       <i class="icon-share-alt"></i>
                                       </span>
                                       <?php } ?>   
                                    </div>
                                 </div>
                              </div>
                            </div>
                            <?php if(!empty($systemNote['link'])){?></a><?php }?>
                            <div class="col2">
                              <div class="date">
                                 <?php
								 echo time_past($systemNote['timestamp']);
								 ?>
                              </div>
                            </div>
                            
                        </li>
                        <?php
						}
						if(empty($systemNote)){
							echo '<li>
							<div class="col1">
                              <div class="cont">
                                 <div class="cont-col1">
                                    <div class="label label-sm label-default">                        
                                       <i class="icon-bell"></i>
                                    </div>
                                 </div>
                                 <div class="cont-col2">
                                    <div class="desc">
                                       No Corporate Action Notifications
                                    </div>
                                 </div>
                              </div>
                            </div></li>';
						}
						?>
                    </ul><!--feeds-->
                </div><!--scroller-->
            </div><!--tab-pane tab_1_2-->
            
            <div class="tab-pane" id="tab_1_3">
                <div class="scroller" style="height: 250px;" data-always-visible="1" data-rail-visible1="1">
                    <ul class="feeds">
                    	<?php
                    	$query = "
							SELECT *
							FROM ".$_SESSION['members_notification_table']."
							WHERE member_id=:member_id AND type='system'
						";
						
						//Fund Symbols
						try{
							$rsSystemNote = $mLink->prepare($query);
							$aValues = array(
								':member_id' 	=> $_SESSION['member_id']
							);
							$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
							$rsSystemNote->execute($aValues);
						}
						catch(PDOException $error){
							// Log any error
							file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
						}
						
						while($systemNote = $rsSystemNote->fetch(PDO::FETCH_ASSOC)) {
						
						?>
                        <li>
                        	<?php
							if(!empty($systemNote['link'])){?><a href="<?php echo $systemNote['link'];?>"><?php } ?>
                            <div class="col1">
                              <div class="cont">
                                 <div class="cont-col1">
                                    <div class="label label-sm label-<?php echo $systemNote['status'];?>">                        
                                       <i class="icon-bell"></i>
                                    </div>
                                 </div>
                                 <div class="cont-col2">
                                    <div class="desc">
                                       <?php if(!empty($systemNote['title'])){ echo "<strong>".$systemNote['title']."</strong> - ";} echo $systemNote['notification'];?>
                                       <?php if(!empty($systemNote['link'])){?>
                                       <span class="label label-sm label-<?php echo $systemNote['status'];?>">
                                       <i class="icon-share-alt"></i>
                                       </span>
                                       <?php } ?>   
                                    </div>
                                 </div>
                              </div>
                            </div>
                            <?php if(!empty($systemNote['link'])){?></a><?php }?>
                            <div class="col2">
                              <div class="date">
                                 <?php
								 echo time_past($systemNote['timestamp']);
								 ?>
                              </div>
                            </div>
                            
                        </li>
                        <?php
						}
						?>
                    </ul><!--feeds-->
                </div><!--scroller-->
                </div><!--tab-pane tab_1_3-->
        </div><!--END TABS-->
       
    </div><!--END Portlet Body-->
</div><!--END Portlet-->
<!--END PORTLET NOTIFICATIONS-->