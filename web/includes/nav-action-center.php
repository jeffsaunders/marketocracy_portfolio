<ul class="nav nav-tabs">
    <li <?php if($pageID == '01-00-00-003'){?>class="active"<?php }?>><a href="?page=01-00-00-003">MyTrackRecord.com</a></li>
    <li <?php if($pageID == '01-00-00-007'){?>class="active"<?php }?>><a href="?page=01-00-00-007">Articles</a></li>
    
    <?php if($_SESSION['admin'] == '1'){?>
    
    
    
    <?php } ?>
    
    <?php if($_SESSION['subscription']['mtrAccess'] == '2' || $_SESSION['admin'] == '1'){?>
    <li <?php if($pageID == '01-00-00-006'){?>class="active"<?php }?>><a href="?page=01-00-00-006">Campaigns</a></li>
    <li <?php if($pageID == '01-00-00-005'){?>class="active"<?php }?>><a href="?page=01-00-00-005">Trackers</a></li>
    <?php } ?>
    <li <?php if($pageID == '01-00-00-008'){?>class="active"<?php }?>><a href="?page=01-00-00-008">Funds</a></li>
    <li <?php if($pageID == '01-00-00-004'){?>class="active"<?php }?>><a href="?page=01-00-00-004">Research</a></li>
    <?php /*?><li><a href="?page=10-00-00-002&account=1&tab=profile">Edit Profile</a></li>
    <li><a href="?page=10-00-00-002&account=2">Fund Settings</a></li><?php */?>
    
    <li style="float: right;margin-right:10px;"><a href="http://<?php echo $_SESSION['sub_domain'];?>.mytrackrecord.com" target="_blank" style="background:#5CB85C;color:#ffffff;">Public Profile <i class="icon-external-link"></i></a>
</ul>