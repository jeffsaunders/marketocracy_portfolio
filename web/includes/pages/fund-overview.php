
        <!-- BEGIN SAMPLE PORTLET CONFIGURATION MODAL FORM-->         
        <div class="modal fade" id="stock-info-pop" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog">
             <div class="modal-content">
                <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                   <h4 class="modal-title">Stock Info</h4>
                </div>
                <div class="modal-body">
                   Stock Info goes here.
                </div>
                <div class="modal-footer">
                   <a href="#quick-trade" data-toggle="modal" class="btn btn-success" data-dismiss="modal"><i class="icon-random"></i> Quick Buy</a>
                   <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
             </div>
             <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- END SAMPLE PORTLET CONFIGURATION MODAL FORM-->
         
        <!-- BEGIN FUND INFO -->
        <div class="row">
          <div class="col-md-12 col-sm-12">
              <!-- BEGIN FUND INFO -->
              <?php include('includes/portlets/fund-live-info.php');?>
              <!-- END FUND INFO -->
          </div>
        </div>
        <!-- END FUND INFO -->
         
		<div class="clearfix"></div>
		<div class="row" id="sortable_portlets">
        	<div class="col-md-6 col-sm-12 column sortable" id="col1">
            	<?php
               	$query = "
					SELECT overview_col1, overview_col2 
					FROM members_fund_settings
					WHERE fund_id=:fund_id
				";

				//Fund Symbols
				try{
					$rsDashOrder = $mLink->prepare($query);
					$aValues = array(
						':fund_id'		=> $fundID
					);
					$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
					$rsDashOrder->execute($aValues);
				}
				catch(PDOException $error){
					// Log any error
					file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
				}
				
				$rowOrder = $rsDashOrder->fetch(PDO::FETCH_ASSOC);
				
				
				$column1 = explode('|', $rowOrder['overview_col1']);
				
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
				?>
               
        	</div><!--END COLUMN-->
            
        	<div class="col-md-6 col-sm-12 column sortable" id="col2">
            	<?php
			   $column2 = explode('|', $rowOrder['overview_col2']);
				
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
			   ?> 

        	</div><!--END COLUMN-->
		</div><!--row-->
         
		<div class="clearfix"></div>
         
         
      