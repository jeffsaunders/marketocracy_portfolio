						
<ul class="nav nav-tabs">
    <li><a href="?page=20-00-00-001">Events Log</a></li>
    <li><a href="?page=20-00-00-001">Currently Logged In (<?php echo $numLogged;?>)</a></li>
    <li><a href="?page=20-00-00-001">Unique Logins (<?php echo count($aLogins);?>)</a></li>
    <li <?php if($pageID == '20-00-00-003' || $pageID == '20-00-00-011'){echo 'class="active"';}?>>
    	<a <?php if($pageID == '20-00-00-003'){echo 'href="tab_1" data-toggle="tab"';}else{ echo'href="?page=20-00-00-003"';}?>>Member Lookup</a>    
    </li>
    
    <li <?php if($pageID == '20-00-00-004'){echo 'class="active"';}?>>
    	<a <?php if($pageID == '20-00-00-004'){echo 'href="tab_1" data-toggle="tab"';}else{ echo'href="?page=20-00-00-004"';}?>>Member Reprice</a>
    </li>
    
    <li <?php if($pageID == '20-00-00-005'){echo 'class="active"';}?>>
    	<a <?php if($pageID == '20-00-00-005'){echo 'href="tab_1" data-toggle="tab"';}else{ echo'href="?page=20-00-00-005"';}?>>CA Admin</a>
    </li>
    
    <li <?php if($pageID == '20-00-00-006'){echo 'class="active"';}?>>
    	<a <?php if($pageID == '20-00-00-006'){echo 'href="tab_1" data-toggle="tab"';}else{ echo'href="?page=20-00-00-006"';}?>>Rankings</a>
    </li>
    <li <?php if($pageID == '20-00-00-007'){echo 'class="active"';}?>>
    	<a <?php if($pageID == '20-00-00-007'){echo 'href="tab_1" data-toggle="tab"';}else{ echo'href="?page=20-00-00-007"';}?>>Managers</a>
    </li>
    <li <?php if($pageID == '20-00-00-008'){echo 'class="active"';}?>>
    	<a <?php if($pageID == '20-00-00-008'){echo 'href="tab_1" data-toggle="tab"';}else{ echo'href="?page=20-00-00-008"';}?>>Membership</a>
    </li>
    <li <?php if($pageID == '20-00-00-009'){echo 'class="active"';}?>>
    	<a <?php if($pageID == '20-00-00-009'){echo 'href="tab_1" data-toggle="tab"';}else{ echo'href="?page=20-00-00-009"';}?>>Campaigns</a>
    </li>
    <li <?php if($pageID == '20-00-00-010'){echo 'class="active"';}?>>
    	<a <?php if($pageID == '20-00-00-010'){echo 'href="tab_1" data-toggle="tab"';}else{ echo'href="?page=20-00-00-010"';}?>>Auto Campaigns</a>
    </li>
</ul>