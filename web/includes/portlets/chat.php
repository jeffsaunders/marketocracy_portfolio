<?php
//TASKS PORTLET

//portVar1 = NOT SET
//portVar2 = NOT SET
//portVar3 = NOT SET

/*$query = "
	SELECT fund_symbol, fund_id 
	FROM funds_test
	WHERE member_id=:member_id AND fund_id=:fund_id AND active=1
";

//Fund Symbols
try{
	$rsStocksFund = $mLink->prepare($query);
	$aValues = array(
		':member_id' => $_SESSION['member_id'],
		':fund_id'	=> $portVar1
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
<!-- BEGIN CHAT PORTLET-->
<div class="portlet" id="<?php echo $portletID; ?>">
  <div class="portlet-title handle">
     <div class="caption"><i class="icon-comments"></i>Conversations</div>
     <div class="tools">
        <a href="" class="reload"></a>
        <a href="" class="collapse"></a>
     </div>
  </div>
  <div class="portlet-body" id="chats">
     <div class="scroller" style="height: 435px;" data-always-visible="1" data-rail-visible1="1">
        <ul class="chats">
           <li class="in">
              <img class="avatar img-responsive" alt="" src="images/avatar1.jpg" />
              <div class="message">
                 <span class="arrow"></span>
                 <a href="#" class="name">Bob Nilson</a>
                 <span class="datetime">at Jul 25, 2012 11:09</span>
                 <span class="body">
                 Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
                 </span>
              </div>
           </li>
           <li class="out">
              <img class="avatar img-responsive" alt="" src="images/avatar2.jpg" />
              <div class="message">
                 <span class="arrow"></span>
                 <a href="#" class="name">Lisa Wong</a>
                 <span class="datetime">at Jul 25, 2012 11:09</span>
                 <span class="body">
                 Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
                 </span>
              </div>
           </li>
           <li class="in">
              <img class="avatar img-responsive" alt="" src="images/avatar1.jpg" />
              <div class="message">
                 <span class="arrow"></span>
                 <a href="#" class="name">Bob Nilson</a>
                 <span class="datetime">at Jul 25, 2012 11:09</span>
                 <span class="body">
                 Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
                 </span>
              </div>
           </li>
           <li class="out">
              <img class="avatar img-responsive" alt="" src="images/avatar3.jpg" />
              <div class="message">
                 <span class="arrow"></span>
                 <a href="#" class="name">Richard Doe</a>
                 <span class="datetime">at Jul 25, 2012 11:09</span>
                 <span class="body">
                 Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
                 </span>
              </div>
           </li>
           <li class="in">
              <img class="avatar img-responsive" alt="" src="images/avatar3.jpg" />
              <div class="message">
                 <span class="arrow"></span>
                 <a href="#" class="name">Richard Doe</a>
                 <span class="datetime">at Jul 25, 2012 11:09</span>
                 <span class="body">
                 Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
                 </span>
              </div>
           </li>
           <li class="out">
              <img class="avatar img-responsive" alt="" src="images/avatar1.jpg" />
              <div class="message">
                 <span class="arrow"></span>
                 <a href="#" class="name">Bob Nilson</a>
                 <span class="datetime">at Jul 25, 2012 11:09</span>
                 <span class="body">
                 Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
                 </span>
              </div>
           </li>
           <li class="in">
              <img class="avatar img-responsive" alt="" src="images/avatar3.jpg" />
              <div class="message">
                 <span class="arrow"></span>
                 <a href="#" class="name">Richard Doe</a>
                 <span class="datetime">at Jul 25, 2012 11:09</span>
                 <span class="body">
                 Lorem ipsum dolor sit amet, consectetuer adipiscing elit, 
                 sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
                 </span>
              </div>
           </li>
           <li class="out">
              <img class="avatar img-responsive" alt="" src="images/avatar1.jpg" />
              <div class="message">
                 <span class="arrow"></span>
                 <a href="#" class="name">Bob Nilson</a>
                 <span class="datetime">at Jul 25, 2012 11:09</span>
                 <span class="body">
                 Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. sed diam nonummy nibh euismod tincidunt ut laoreet.
                 </span>
              </div>
           </li>
        </ul>
     </div>
     <div class="chat-form">
        <div class="input-cont">   
           <input class="form-control" type="text" placeholder="Type a message here..." />
        </div>
        <div class="btn-cont"> 
           <span class="arrow"></span>
           <a href="" class="btn btn-primary icn-only"><i class="icon-ok icon-white"></i></a>
        </div>
     </div>
  </div>
</div>
<!-- END CHAT PORTLET-->