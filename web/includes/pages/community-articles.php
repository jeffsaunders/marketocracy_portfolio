<?php
/*
Marketocracy Inc. 
Community Articles Scripts

Supporting files:
	AJAX		- process/ajax/community-article-processes.php
	JAVASCRIPT	- THIS DOCUMENT includes/scripts/community-article.js.php
	HTML		- includes/pages/community-article.php
*/



?>      
		
   
        <!-- BEGIN PAGE CONTENT-->
        <div class="row profile">
            <div class="col-md-8">
                
                <div class="portlet article-portlet">
                    <div class="portlet-title">
                        <div class="caption"><span class="article-date">NOV 21, 2014</span></div>
                        <?php /*?><div class="actions">
                            <a class="btn btn-info btn-xs" href="javascript:;">New Campaign</a>
                        </div><!--tools--><?php */?>
                    </div><!--portlet-title-->
                    <div class="portlet-body">
                    	
                        <div class="row">
                        	
                            <div class="col-md-8">
                            	<h4><a href="?page=04-00-00-003">Time To Sell Solazyme</a></h4>
                        		<p>At the end of August, I wrote about Solazyme (NASDAQ:SZYM) as a company with the potential to double in two years. Since then, the stock has dropped 68%. In this article, I’ll explain what happened, and tell you what I think shareholders should do now.</p>
                                <p><a href="?page=04-00-00-003" class="btn btn-info btn-xm" style="color:#ffffff;">Read More</a></p>
                            </div><!--col-md-8-->
                            
                            <div class="col-md-4">
                            	<img src="http://blogs-images.forbes.com/kenkam/files/2014/06/American-Airlines-Part-2.jpg"  alt="forbes" />
                            </div><!--col-md-4-->
                            
                        </div><!--row-->
                        
                    	<div class="clearfix"></div>
                    
                    </div><!--portlet-body-->
                </div><!--end-portlet-->
                
                <div class="portlet article-portlet">
                    <div class="portlet-title">
                        <div class="caption"><span class="article-date">NOV 21, 2014</span></div>
                        <?php /*?><div class="actions">
                            <a class="btn btn-info btn-xs" href="javascript:;">New Campaign</a>
                        </div><!--tools--><?php */?>
                    </div><!--portlet-title-->
                    <div class="portlet-body">
                    	
                        <div class="row">
                        	
                            <div class="col-md-8">
                            	<h4><a href="?page=04-00-00-003">Time To Sell Solazyme</a></h4>
                        		<p>At the end of August, I wrote about Solazyme (NASDAQ:SZYM) as a company with the potential to double in two years. Since then, the stock has dropped 68%. In this article, I’ll explain what happened, and tell you what I think shareholders should do now.</p>
                                <p><a href="?page=04-00-00-003" class="btn btn-info btn-xm" style="color:#ffffff;">Read More</a></p>
                            </div><!--col-md-8-->
                            
                            <div class="col-md-4">
                            	<img src="http://blogs-images.forbes.com/kenkam/files/2014/06/American-Airlines-Part-2.jpg"  alt="forbes" />
                            </div><!--col-md-4-->
                            
                        </div><!--row-->
                        
                    	<div class="clearfix"></div>
                    
                    </div><!--portlet-body-->
                </div><!--end-portlet-->
                
            
            </div><!--col-md-8-->
            
            <div class="col-md-4">
            
            </div><!--col-md-4-->
            
        </div><!--row profile-->
        <!-- END PAGE CONTENT-->
        
        <?php if($_SESSION['admin'] == 1){?>
        <pre>
        <?php echo print_r($member); ?>
        </pre>
        <?php } ?>
      