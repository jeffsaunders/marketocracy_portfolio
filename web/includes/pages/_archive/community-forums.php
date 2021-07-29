        <!-- BEGIN PAGE CONTENT-->
        <div class="row">
            <div class="col-md-12">
             
                <!-- BEGIN RECENT ORDERS TABLE-->
                <div class="portlet" id="ledger-entries">
                <div class="portlet-title">
                   <div class="caption"><i class="icon-comments"></i><?php echo $forumMainTitle; ?></div>
                   <div class="actions">
                      
                   </div>
                </div>
                <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover table-full-width" id="forum-cat">
                        <thead>
                            <tr>
                                
                                <th>Categories</th>
                                
                                <th width="10%">Last Post</th>
                                <th width="2%">Topics</th>
                                <th style="display:none;"></th>
                                
                            </tr>
                        </thead>
                        <tbody>
                        	<?php 
							
							 $query = "
								SELECT *
								FROM ".$_SESSION['forum_categories_table']." AS st
								WHERE forum_id=:forum_id
								ORDER BY sequence ASC
							";
							
							//Fund Symbols
							try{
								$rsCat = $mLink->prepare($query);
								$aValues = array(
									':forum_id' 	=> $forumID
								);
								$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
								$rsCat->execute($aValues);
							}
							catch(PDOException $error){
								// Log any error
								file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
							}
							
							while($cat = $rsCat->fetch(PDO::FETCH_ASSOC)) {
								
								
																
								?>
                                <tr>
                                    
                                	<td><a href="?page=04-01-00-003&forum=<?php echo $cat['forum_id'];?>&cat=<?php echo $cat['cat_id']; ?>" style="display:block;"><strong><?php echo $cat['cat_title'];?></strong><br /><small><?php echo substr($cat['cat_description'], 0, 100); ?></small></a></td>
                                    <td><span class="label label-info"><?php echo get_member($mLink, $cat['last_user_posted'], 'full name');?></span><br /><?php echo time_past($cat['last_post_date']);?></td>
                                    <td>0</td>
                                    <td style="display:none;"><?php echo $cat['sequence']; ?></td>
                            	</tr>
								<?php
							}
							?>
                            
                        </tbody>
                        </table>
                </div>
                </div>
                <!-- END RECENT ORDER TABLE-->
            </div>
        </div><!--row-->
        