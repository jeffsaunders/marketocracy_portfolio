<?php
//Fund Stocks Owned Portlet
//portVar1 = Fund ID
//portVar2 = NOT SET
//portVar3 = NOT SET

include('fund-symbol-query.php');
$stocksFundID 	= $fundInfo['fund_id'];
$stocksFundSym	= $fundInfo['fund_symbol'];
?>
<!--START PORTLET STOCKS-->
<div class="portlet" id="<?php echo $portletID; ?>">
  <div class="portlet-title handle">
     <div class="caption">
     <i class="icon-bell"></i>
     <?php if(!isset($fundID)){?><a href="?page=03-01-00-001&fund=<?php echo $stocksFundID; ?>"><?php echo $stocksFundSym; ?></a><?php }else{ echo $stocksFundSym; } ?> | Stocks</div>
     	<div class="tools">
            <a href="" class="reload"></a>
            <a href="" class="collapse"></a>
        </div><!--tools-->
  </div>
  <div class="portlet-body">
     <div class="table-scrollable">
        <table class="table table-striped table-bordered table-hover">
           <thead>
              <tr>
                 <th>Stock</th>
                 <th>Price</th>
                 <th>% Change</th>
                 <th></th>
              </tr>
           </thead>
           <tbody>
              <tr>
                 <td>
                    <a href="#">DG</a>
                 </td>
                 <td>59.46</td>
                 <td>+5.30% 
                    <span class="label label-warning label-sm">UP</span>
                 </td>
                 <td><a href="#" class="btn btn-default btn-xs">View</a></td>
              </tr>
              <tr>
                 <td>
                    <a href="#">HSP</a>
                 </td>
                 <td>41.52</td>
                 <td>+3.26%</td>
                 <td><a href="#" class="btn btn-default btn-xs">View</a></td>
              </tr>
              <tr>
                 <td>
                    <a href="#">CELG</a>
                 </td>
                 <td>164.72</td>
                 <td>+2.93% 
                    <span class="label label-success label-sm">UP</span>
                 </td>
                 <td><a href="#" class="btn btn-default btn-xs">View</a></td>
              </tr>
              <tr>
                 <td>
                    <a href="#">BRCM</a>
                 </td>
                 <td>27.59</td>
                 <td>+2.66%
                    <span class="label label-success label-sm">UP</span>
                 </td>
                 <td><a href="#" class="btn btn-default btn-xs">View</a></td>
              </tr>
              <tr>
                 <td>
                    <a href="#">JNPR</a>
                 </td>
                 <td>21.58</td>
                 <td>2.06%
                    <span class="label label-warning label-sm">UP</span>
                 </td>
                 <td><a href="#" class="btn btn-default btn-xs">View</a></td>
              </tr>
              <tr>
                 <td>
                    <a href="#">APPL</a>
                 </td>
                 <td>546.10</td>
                 <td>-2.34% 
                    <span class="label label-success label-sm">Down</span>
                 </td>
                 <td><a href="#" class="btn btn-default btn-xs">View</a></td>
              </tr>
           </tbody>
        </table>
     </div>
  </div>
</div>
<!--END PORTLET STOCKS-->