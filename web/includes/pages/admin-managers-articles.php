<?php
/*
Marketocracy Inc. | Beta Development
Admin Manager Articles Composite Data

Supporting files:
	AJAX		- process/ajax/admin-managers-processes.php
	JAVASCRIPT	- JAVASCRIPT includes/scripts/admin-managers.js.php
	HTML		- THIS DOCUMENT includes/pages/admin-managers.php
*/



?>
         
          	
            
            
            
                
            <div class="row">
                <div class="col-md-12">
                    
                    <div class="tabbable tabbable-custom">
                        
                        <?php include('includes/nav-admin-tabs.php');?>

                        <div class="tab-content">
                        
                            
                            
                            <div class="tab-pane active" id="tab_1">
                                <div class="portlet">
                                    <div class="portlet-title">
                                    	<div class="caption"><i class="icon-reorder"></i>Manager Composite Data</div>
                                      	<div class="tools" id="collapse-btn">
                                        	<a href="javascript:;" class="collapse"></a>
                                        	<!--<a href="javascript:;" class="reload"></a>-->
                                      	</div><!--tools-->
                                    </div><!--portlet-title-->
                                    <div class="portlet-body" id="collapse-portlet">
                                    	
                                        <div class="row">
                                        	<div class="col-md-12">
                                                
                                                <div class="table-responsive">
                                                    <table class="table table-striped table-bordered table-hover" id="manager-table">
                                                        <thead>
                                                            <tr>
                                                                <th>Manager</th>
                                                                <th>Username</th>
                                                                <th>Fund Symbol</th>
                                                                <th>Fund Name</th>
                                                                <th>Composite</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            
                                                            $query = "
                                                                SELECT mf.*,m.*
                                                                FROM members_fund AS mf
                                                                INNER JOIN members AS m ON m.member_id=mf.member_id
                                                                WHERE mf.composite_fund='1'
                                                            ";
                                                            
                                                            try{
                                                                $rsGetFunds = $mLink->prepare($query);
                                                                $aValues = array();
                                                                $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                                                                $rsGetFunds->execute($aValues);
                                                            }
                                                            catch(PDOException $error){
                                                                // Log any error
                                                                file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                                                            }
                                                            
                                                            
                                                            
                                                            while($funds = $rsGetFunds->fetch(PDO::FETCH_ASSOC)){
                                                                
                                                                $memberID				= $funds['member_id'];
                                                                $compositeDisclosure	= $funds['composite_disclosure'];
                                                                
                                                                if($compositeDisclosure != NULL){
                                                                    $showComposite = '<a class="btn btn-xs btn-default" href="'.$_SESSION['site_root'].'/files/disclosures/'.$compositeDisclosure.'" target="_blank">View Composite <i class="icon-external-link"></i></a>';	
                                                                }else{
                                                                    $showComposite = 'N/A';	
                                                                }
                                                                
                                                                ?>
                                                                <tr>
                                                                    <td><a href="?page=20-00-00-003&member=<?php echo $memberID;?>" target="_blank"><?php echo $funds['name_first'];?> <?php echo $funds['name_last'];?></a></td>
                                                                    <td><?php echo $funds['username'];?>
                                                                    <td><?php echo $funds['fund_symbol'];?></td>
                                                                    <td><?php echo $funds['fund_name'];?></td>
                                                                    <td><?php echo $showComposite;?></td>
                                                                    <td>
                                                                        <a href="#composite-data" data-toggle="modal" class="btn btn-xs btn-info" onclick="loadCompositeData('<?php echo $funds['fund_id'];?>');">View Composite Data</a>
                                                                        <a href="https://portfolio.marketocracy.com/process/ajax/admin-switch-user.php?member=<?php echo $memberID;?>&admin=<?php echo $_SESSION['member_id'];?>&toggle=admin-to-user&gopage=01-00-00-003&gofund=<?php echo $funds['fund_id'];?>&returnPage=20-00-00-007" class="btn btn-xs btn-info">Action Center</a> 
                                                                        <a href="https://<?php echo $funds['username'];?>.mytrackrecord.com/" class="btn btn-xs btn-info" target="_blank">Public Page <i class="icon-external-link"></i></a>
                                                                        
                                                                    </td>
                                                                </tr>
                                                                <?php
                                                                
                                                            }
                                                            
                                                            ?>  
                                                            
                                                        </tbody>
                                                    </table>
                                                </div><!--table-responsive-->    
                                                
                                    		</div><!--col-->
                                        </div><!--row-->
                                        
                                        
                                        
                                    </div><!--END PORTLET BODY-->
                                </div><!--END PORTLET-->
                                
                                
                            </div><!--END TAB 2-->
                            
                            
                        
                        
                        </div><!--tab-content-->
                    </div><!--tabbable tabbable-custom-->
                    
                </div><!--col-md-12-->
            </div><!--row-->
            <!-- END PAGE CONTENT-->    
   