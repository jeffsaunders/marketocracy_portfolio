<?php
//NOTIFICATIONS PORTLET

//portVar1 = NOT SET
//portVar2 = NOT SET
//portVar3 = NOT SET

?>
<!--START PORTLET NOTIFICATIONS-->
<div class="portlet" id="<?php echo $portletID; ?>">
    <div class="portlet-title handle">
       <div class="caption"><i class="icon-exclamation-sign"></i>Notifications <span class="label label-info note-counter" id=""></span> <a href="javascript:void(0);" onclick="clearNotifications('<?php echo $_SESSION['member_id'];?>');" class="btn btn-xs btn-danger" title="This will clear all unflagged notifications.">Clear All</a></div>
       <div class="tools">
          <?php /*?><a href="#portlet-config" data-toggle="modal" class="config"></a>
          <a href="" class="remove"></a><?php */?>
          <a href="" class="reload" onClick="refreshNote()"></a>
          <a href="" class="collapse"></a>
       </div>
    </div>
    <div class="portlet-body">
    	<!--BEGIN TABS-->
        <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_1_1" data-toggle="tab" class="notifications">All <span class="label label-info note-counter"></span> <span class="label label-danger flag-counter"></span></a></li>
            <li><a href="#tab_1_2" data-toggle="tab" class="notifications">Corporate Actions <span class="label label-info note-counter-ca"></span> <span class="label label-danger flag-counter-ca"></span></a></li>
            <li><a href="#tab_1_3" data-toggle="tab" class="notifications">System <span class="label label-info note-counter-sys"></span> <span class="label label-danger flag-counter-sys"></span></a></li>
        </ul>
        <div class="tab-content" id="notification-ajax12">
            <?php //Loaded by ajax; file: includes/portlets/notification-ajax.php
			if($_SESSION['admin'] == '1'){ $pHeight = '700px';}else{$pHeight = '250px';}
			?>
            <div class="tab-pane active" id="tab_1_1">
                <div class="scroller" style="height: <?php echo $pHeight;?>;" data-always-visible="1" data-rail-visible="0">
                    <ul class="feeds notification-ajax-all">
                    	<?php //loaded by ajax ?>
                    </ul>
                </div><!--scroller-->
            </div><!--tab-pane tab_1_1-->
            
            <div class="tab-pane" id="tab_1_2">
                <div class="scroller" style="height: <?php echo $pHeight;?>;" data-always-visible="1" data-rail-visible1="1">
                    <ul class="feeds" id="notification-ajax-ca">
                        <?php //loaded by ajax ?>
                    </ul><!--feeds-->
                </div><!--scroller-->
            </div><!--tab-pane tab_1_2-->
            
            <div class="tab-pane" id="tab_1_3">
                <div class="scroller" style="height: <?php echo $pHeight;?>;" data-always-visible="1" data-rail-visible1="1">
                    <ul class="feeds" id="notification-ajax-system">
                    	<?php //loaded by ajax ?>
                	</ul><!--feeds-->
                </div><!--scroller-->
                </div><!--tab-pane tab_1_3-->
        </div><!--END TABS-->
       
    </div><!--END Portlet Body-->
</div><!--END Portlet-->
<!--END PORTLET NOTIFICATIONS-->