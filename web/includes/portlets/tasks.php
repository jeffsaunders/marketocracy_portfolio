<?php
//TASKS PORTLET

//portVar1 = NOT SET
//portVar2 = NOT SET
//portVar3 = NOT SET

/*$query = "
	SELECT fund_symbol, fund_id 
	FROM table
	WHERE member_id=:member_id AND fund_id=:fund_id AND active=1
";

//Fund Symbols
try{
	$rsStocksFund = $mLink->prepare($query);
	$aValues = array(
		':member_id'	=> $_SESSION['member_id'],
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
<!--START PORTLET TASKS-->
<div class="portlet tasks-widget" id="<?php echo $portletID; ?>">
  <div class="portlet-title handle">
     <div class="caption"><i class="icon-check"></i>Tasks</div>
     <div class="tools">
        <a href="" class="reload"></a>
        <a href="" class="collapse"></a>
     </div>
     <div class="actions">
        <div class="btn-group">
           <a class="btn btn-info btn-sm dropdown-toggle" href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
           More
           <i class="icon-angle-down"></i>
           </a>
           <ul class="dropdown-menu pull-right">
              <li><a href="#"><i class="i"></i> All Project</a></li>
              <li class="divider"></li>
              <li><a href="#">AirAsia</a></li>
              <li><a href="#">Cruise</a></li>
              <li><a href="#">HSBC</a></li>
              <li class="divider"></li>
              <li><a href="#">Pending <span class="badge badge-important">4</span></a></li>
              <li><a href="#">Completed <span class="badge badge-success">12</span></a></li>
              <li><a href="#">Overdue <span class="badge badge-warning">9</span></a></li>
           </ul>
        </div>
     </div>
  </div>
  <div class="portlet-body">
     <div class="task-content">
        <div class="scroller" style="height: 305px;" data-always-visible="1" data-rail-visible1="1">
           <!-- START TASK LIST -->
           <ul class="task-list">
              <li>
                 <div class="task-checkbox">
                    <input type="checkbox" class="liChild" value=""  />                                       
                 </div>
                 <div class="task-title">
                    <span class="task-title-sp">Present 2013 Year IPO Statistics at Board Meeting</span>
                    <span class="label label-sm label-success">Company</span>
                    <span class="task-bell"><i class="icon-bell"></i></span>
                 </div>
                 <div class="task-config">
                    <div class="task-config-btn btn-group">
                       <a class="btn btn-xs btn-default dropdown-toggle" href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                       <i class="icon-cog"></i><i class="icon-angle-down"></i></a>
                       <ul class="dropdown-menu pull-right">
                          <li><a href="#"><i class="icon-ok"></i> Complete</a></li>
                          <li><a href="#"><i class="icon-pencil"></i> Edit</a></li>
                          <li><a href="#"><i class="icon-trash"></i> Cancel</a></li>
                       </ul>
                    </div>
                 </div>
              </li>
              <li>
                 <div class="task-checkbox">
                    <input type="checkbox" class="liChild" value=""/>                                       
                 </div>
                 <div class="task-title">
                    <span class="task-title-sp">Hold An Interview for Marketing Manager Position</span>
                    <span class="label label-sm label-danger">Marketing</span>
                 </div>
                 <div class="task-config">
                    <div class="task-config-btn btn-group">
                       <a class="btn btn-xs btn-default dropdown-toggle" href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                       <i class="icon-cog"></i><i class="icon-angle-down"></i></a>
                       <ul class="dropdown-menu pull-right">
                          <li><a href="#"><i class="icon-ok"></i> Complete</a></li>
                          <li><a href="#"><i class="icon-pencil"></i> Edit</a></li>
                          <li><a href="#"><i class="icon-trash"></i> Cancel</a></li>
                       </ul>
                    </div>
                 </div>
              </li>
              <li>
                 <div class="task-checkbox">
                    <input type="checkbox" class="liChild" value=""/>                                       
                 </div>
                 <div class="task-title">
                    <span class="task-title-sp">AirAsia Intranet System Project Internal Meeting</span>
                    <span class="label label-sm label-success">AirAsia</span>
                    <span class="task-bell"><i class="icon-bell"></i></span>
                 </div>
                 <div class="task-config">
                    <div class="task-config-btn btn-group">
                       <a class="btn btn-xs btn-default dropdown-toggle" href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                       <i class="icon-cog"></i><i class="icon-angle-down"></i></a>
                       <ul class="dropdown-menu pull-right">
                          <li><a href="#"><i class="icon-ok"></i> Complete</a></li>
                          <li><a href="#"><i class="icon-pencil"></i> Edit</a></li>
                          <li><a href="#"><i class="icon-trash"></i> Cancel</a></li>
                       </ul>
                    </div>
                 </div>
              </li>
              <li>
                 <div class="task-checkbox">
                    <input type="checkbox" class="liChild" value=""  />                                       
                 </div>
                 <div class="task-title">
                    <span class="task-title-sp">Technical Management Meeting</span>
                    <span class="label label-sm label-warning">Company</span>
                 </div>
                 <div class="task-config">
                    <div class="task-config-btn btn-group">
                       <a class="btn btn-xs btn-default dropdown-toggle" href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true"><i class="icon-cog"></i><i class="icon-angle-down"></i></a>
                       <ul class="dropdown-menu pull-right">
                          <li><a href="#"><i class="icon-ok"></i> Complete</a></li>
                          <li><a href="#"><i class="icon-pencil"></i> Edit</a></li>
                          <li><a href="#"><i class="icon-trash"></i> Cancel</a></li>
                       </ul>
                    </div>
                 </div>
              </li>
              <li>
                 <div class="task-checkbox">
                    <input type="checkbox" class="liChild" value=""  />                                       
                 </div>
                 <div class="task-title">
                    <span class="task-title-sp">Kick-off Company CRM Mobile App Development</span>
                    <span class="label label-sm label-info">Internal Products</span>
                 </div>
                 <div class="task-config">
                    <div class="task-config-btn btn-group">
                       <a class="btn btn-xs btn-default dropdown-toggle" href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true"><i class="icon-cog"></i><i class="icon-angle-down"></i></a>
                       <ul class="dropdown-menu pull-right">
                          <li><a href="#"><i class="icon-ok"></i> Complete</a></li>
                          <li><a href="#"><i class="icon-pencil"></i> Edit</a></li>
                          <li><a href="#"><i class="icon-trash"></i> Cancel</a></li>
                       </ul>
                    </div>
                 </div>
              </li>
              <li>
                 <div class="task-checkbox">
                    <input type="checkbox" class="liChild" value=""  />                                       
                 </div>
                 <div class="task-title">
                    <span class="task-title-sp">
                    Prepare Commercial Offer For SmartVision Website Rewamp 
                    </span>
                    <span class="label label-sm label-danger">SmartVision</span>
                 </div>
                 <div class="task-config">
                    <div class="task-config-btn btn-group">
                       <a class="btn btn-xs btn-default dropdown-toggle" href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true"><i class="icon-cog"></i><i class="icon-angle-down"></i></a>
                       <ul class="dropdown-menu pull-right">
                          <li><a href="#"><i class="icon-ok"></i> Complete</a></li>
                          <li><a href="#"><i class="icon-pencil"></i> Edit</a></li>
                          <li><a href="#"><i class="icon-trash"></i> Cancel</a></li>
                       </ul>
                    </div>
                 </div>
              </li>
              <li>
                 <div class="task-checkbox">
                    <input type="checkbox" class="liChild" value=""  />                                       
                 </div>
                 <div class="task-title">
                    <span class="task-title-sp">Sign-Off The Comercial Agreement With AutoSmart</span>
                    <span class="label label-sm label-default">AutoSmart</span>
                    <span class="task-bell"><i class="icon-bell"></i></span>
                 </div>
                 <div class="task-config">
                    <div class="task-config-btn btn-group">
                       <a class="btn btn-xs btn-default dropdown-toggle" href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true"><i class="icon-cog"></i><i class="icon-angle-down"></i></a>
                       <ul class="dropdown-menu pull-right">
                          <li><a href="#"><i class="icon-ok"></i> Complete</a></li>
                          <li><a href="#"><i class="icon-pencil"></i> Edit</a></li>
                          <li><a href="#"><i class="icon-trash"></i> Cancel</a></li>
                       </ul>
                    </div>
                 </div>
              </li>
              <li>
                 <div class="task-checkbox">
                    <input type="checkbox" class="liChild" value=""  />                                       
                 </div>
                 <div class="task-title">
                    <span class="task-title-sp">Company Staff Meeting</span>
                    <span class="label label-sm label-success">Cruise</span>
                    <span class="task-bell"><i class="icon-bell"></i></span>
                 </div>
                 <div class="task-config">
                    <div class="task-config-btn btn-group">
                       <a class="btn btn-xs btn-default dropdown-toggle" href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true"><i class="icon-cog"></i><i class="icon-angle-down"></i></a>
                       <ul class="dropdown-menu pull-right">
                          <li><a href="#"><i class="icon-ok"></i> Complete</a></li>
                          <li><a href="#"><i class="icon-pencil"></i> Edit</a></li>
                          <li><a href="#"><i class="icon-trash"></i> Cancel</a></li>
                       </ul>
                    </div>
                 </div>
              </li>
              <li class="last-line">
                 <div class="task-checkbox">
                    <input type="checkbox" class="liChild" value=""  />                                       
                 </div>
                 <div class="task-title">
                    <span class="task-title-sp">KeenThemes Investment Discussion</span>
                    <span class="label label-sm label-warning">KeenThemes</span>
                 </div>
                 <div class="task-config">
                    <div class="task-config-btn btn-group">
                       <a class="btn btn-xs btn-default dropdown-toggle" href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true"><i class="icon-cog"></i><i class="icon-angle-down"></i></a>
                       <ul class="dropdown-menu pull-right">
                          <li><a href="#"><i class="icon-ok"></i> Complete</a></li>
                          <li><a href="#"><i class="icon-pencil"></i> Edit</a></li>
                          <li><a href="#"><i class="icon-trash"></i> Cancel</a></li>
                       </ul>
                    </div>
                 </div>
              </li>
           </ul>
           <!-- END START TASK LIST -->
        </div>
     </div>
     <div class="task-footer">
        <span class="pull-right">
        <a href="#">See All Tasks <i class="m-icon-swapright m-icon-gray"></i></a> &nbsp;
        </span>
     </div>
  </div>
</div><!--PORETLET TASKS-WDGET-->
<!--END PORTLET TASKS-->