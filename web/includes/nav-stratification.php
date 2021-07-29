                <!--START STRATIFICATION SUB NAV-->
                <div class="btn-group" style="margin-bottom: 20px;float:left;">
                    <a href="/?page=03-01-03-001&fund=<?php echo $fundID;?>" class="btn <?php if($pageID == "03-01-03-001"){?>btn-info<?php }else{?>btn-default<?php } ?>">Basic</a>
                    <a href="/?page=03-01-03-002&fund=<?php echo $fundID;?>" class="btn <?php if($pageID == "03-01-03-002"){?>btn-info<?php }else{?>btn-default<?php } ?>">Sector</a>
                    <a href="/?page=03-01-03-003&fund=<?php echo $fundID;?>" class="btn <?php if($pageID == "03-01-03-003"){?>btn-info<?php }else{?>btn-default<?php } ?>">Style</a>
                    <?php /*?><a href="/fund-strat-activity.php" class="btn <?php if($pageID == "fund-strat-activity"){?>btn-info<?php }else{?>btn-default<?php } ?>">Activity</a>
                    <a href="#" class="btn <?php if($pageID == "fund-strat-fundamentals"){?>btn-info<?php }else{?>btn-default<?php } ?>">Fundamentals</a>
                    <a href="#" class="btn <?php if($pageID == "fund-strat-m100"){?>btn-info<?php }else{?>btn-default<?php } ?>">m100 Trading</a>
                    <a href="#" class="btn <?php if($pageID == "fund-strat-performance"){?>btn-info<?php }else{?>btn-default<?php } ?>">Performance</a>
                    <a href="#" class="btn <?php if($pageID == "fund-strat-rationale"){?>btn-info<?php }else{?>btn-default<?php } ?>">Rationale</a><?php */?>
                </div><!--btn-group-->
                
                <div class="btn-group" style="margin-bottom: 20px;float:right;">
					<?php if($_REQUEST['getAll'] != '1'){?>
                    	<a href="<?php echo $_SERVER['REQUEST_URI'];?>&getAll=1" class="btn btn-default">Show All Positions</a>
                        <a href="process/ajax/fund-strat-basic-processes.php?process=csv&fund=<?php echo $fundID;?>&pos=0&sort=<?php echo $_REQUEST['sort'];?>" class="btn btn-success"><i class="icon-download"></i> Download CSV</a>
                    <?php }else{?>
                    	<a href="<?php echo str_replace('&getAll=1', '', $_SERVER['REQUEST_URI']);?>" class="btn btn-default">Show Current Positions</a>
                        <a href="process/ajax/fund-strat-basic-processes.php?process=csv&fund=<?php echo $fundID;?>&pos=1&sort=<?php echo $_REQUEST['sort'];?>" class="btn btn-success"><i class="icon-download"></i> Download CSV</a>
                    <?php }?>
                </div><!--btn-group-->
                
                <div class="clearfix"></div>
                <!--END STRATIFICATION SUB NAV--> 