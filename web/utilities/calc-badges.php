<?php 
session_start();

// Load debug & error logging functions
require_once("../includes/system-debug-functions.php");

//Connect to DB
require_once("../../secure/dbconnect.php");

require_once("../includes/system-functions.php");

$memberID = $_REQUEST['member'];


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Badges</title>
</head>

<body>

<div>
    <h2>Reprice Funds For New Date</h2>
    <form action="process/calc-badges-process.php?process=5" method="post" class="reprice">
        <label>Member ID:</label>
        <input type="text" value="<?php echo $memberID;?>" name="member"/><br />
        <small>Leave blank for all members</small><br /><br />
        
        
        <input type="submit" value="Reprice Funds" /><!--&nbsp;&nbsp;<input id="active_only" name="active_only" type="checkbox" value="1" checked="checked" /> Active Funds Only<br />-->
        
    
    </form>
</div>

<div id="show-results3"></div>

<div>
    <h2>Calculate/Process Badges</h2>
    <form action="process/calc-badges-process.php?process=1" method="post" class="calc-badges">
        <label>Member ID:</label>
        <input type="text" value="<?php echo $memberID;?>" name="member"/><br />
        <small>Leave blank for all members</small><br /><br />
        
        
        <input type="submit" value="Process Badges" /><!--&nbsp;&nbsp;<input id="active_only" name="active_only" type="checkbox" value="1" checked="checked" /> Active Funds Only<br />-->
        
    
    </form>
</div>

<div id="show-results">

</div>

<div>
    <h2>Badge Report</h2>
    <form action="process/calc-badges-process.php?process=badge-report" method="post" class="badge-report">
        <label>Member ID:</label>
        <input type="text" value="<?php echo $memberID;?>" name="member"/><br />
        <label>Badge</label>
        <select name="badge">
        	<option value="all">All</option>
        	<?php
			//START build badge array
			$query = "
				SELECT * 
				FROM ".$_SESSION['badges_table']."
				WHERE active='1'
			";
			
			try{
				$rsBadge = $mLink->prepare($query);
				$aValues = array();
				$preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
				$rsBadge->execute($aValues);
			}
			
			catch(PDOException $error){
				// Log any error
				file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
			}
			
			$aBadge = array();
				
			while($badge = $rsBadge->fetch(PDO::FETCH_ASSOC)){
				$aBadges[$badge['badge_id']] = array(
					'badge_id'		=> $badge['badge_id'],
					'badge_name'	=> $badge['badge_name'],
					'type'			=> $badge['badge_type']	
				);
				
				echo '<option value="'.$badge['badge_id'].'">'.$badge['badge_name'].'</option>';
				
			}
			//END build badge array
			?>
        </select><br />
        <small>Leave blank for all members</small><br /><br />
        
        
        <input type="submit" value="Get Badge Report" /><!--&nbsp;&nbsp;<input id="active_only" name="active_only" type="checkbox" value="1" checked="checked" /> Active Funds Only<br />-->
        
    
    </form>
</div>

<div id="show-results2">

</div>

<div>
    <h2>Build Ranks</h2>
    <form action="process/calc-badges-process.php?process=build-ranks" method="post" class="build-ranks">
        
        <div class="form-group">
            <label class="control-label">Select Price From Date</label><br />
            
            <select name="year">
                <?php
                $query = "
                    SELECT *
                    FROM system_lists_options
                    WHERE list_id='8'
                    ORDER BY sequence DESC
                ";
                try{
                    $rsList = $mLink->prepare($query);
                    $aValues = array(
                        ':fundkey' 	=> "X'".$fundKey."'"
                    );
                    $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                    $rsList->execute($aValues);
                }
                catch(PDOException $error){
                    // Log any error
                    file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                }
                while($list = $rsList->fetch(PDO::FETCH_ASSOC)){
                    echo '<option value="'.$list['value'].'">'.$list['label'].'</option>';
                }
                ?>
            </select>
            /<select  name="month">
                <?php
                $query = "
                    SELECT *
                    FROM system_lists_options
                    WHERE list_id='7'
                    ORDER BY sequence ASC
                ";
                try{
                    $rsList = $mLink->prepare($query);
                    $aValues = array(
                        ':fundkey' 	=> "X'".$fundKey."'"
                    );
                    $preparedQuery = str_replace(array_keys($aValues), array_values($aValues), $query); //Debug
                    $rsList->execute($aValues);
                }
                catch(PDOException $error){
                    // Log any error
                    file_put_contents($_SESSION['pdo_log'], "-----\rDate: ".date('Y-m-d H:i:s')."\rFile: ". __FILE__ ."\rLine Number: ". __LINE__ ."\rVars:\r".dump_vars(get_defined_vars())."\r", FILE_APPEND);
                }
                while($list = $rsList->fetch(PDO::FETCH_ASSOC)){
                    echo '<option value="'.$list['value'].'">'.$list['label'].'</option>';
                }
                ?>
            </select>/
            <select name="day">
                <option value="31">31</option>
                <option value="30">30</option>
                <option value="28">28</option>
            </select><br />
            <small>YYYY/MM/DD</small>
        </div>
        <br />
        <div class="form-group">
        	<label class="form-label">Period</label><br />
        	<select name="period">
            	<option value="10">10 Year</option>
                <option value="5">5 Year</option>
                <option value="3">3 Year</option>
                <option value="1">1 Year</option>
            </select>
        </div><br />
        <div class="form-group">
        	<label class="form-label">Rank Class</label><br />
            <select name="rank_class">
            	<option value="pro">Pro Ranks</option>
                <option value="community">Community Ranks</option>
            </select>
        </div><br />
        <div class="form-group">
        	<label class="form-label">Compliance Tolerance</label><br />
            <select name="tolerance">
            	<option value=".80">80%</option>
                <option value=".100">100%</option>
                <option value=".95">95%</option>
                <option value=".90">90%</option>
                <option value=".85">85%</option>
                
                <option value=".75">75%</option>
                <option value=".70">70%</option>
            </select>
        </div><br />

        <input type="submit" value="Build Ranks" /><!--&nbsp;&nbsp;<input id="active_only" name="active_only" type="checkbox" value="1" checked="checked" /> Active Funds Only<br />-->
        
    
    </form>
</div>

<div id="show-results4">

</div>


<script src="../plugins/jquery-1.10.2.min.js" type="text/javascript"></script>
<script src="../plugins/jquery-migrate-1.2.1.min.js" type="text/javascript"></script>
<script>
//Process Reply Form via AJAX
$('form.reprice').on('submit', function() {
	
	$('#show-results3').html('<img src="<?php echo $_SESSION['site_root'];?>images/loading.gif" />');
	
	$.ajax({
        type: "POST",
        data: $(".reprice").serialize(),
        url: "process/calc-badges-process.php?process=5",
        success: function(data)
        {
			//alert(data);
			$('#show-results3').html(data);
			
		}
    });
	
	return false;
});



$('form.build-ranks').on('submit', function() {
	
	$('#show-results4').html('<img src="<?php echo $_SESSION['site_root'];?>images/loading.gif" />');
	
	$.ajax({
        type: "POST",
        data: $(".build-ranks").serialize(),
        url: "process/calc-badges-process.php?process=build-ranks",
        success: function(data)
        {
			//alert(data);
			$('#show-results4').html(data);
			
		}
    });
	
	return false;
});


$('form.calc-badges').on('submit', function() {
	
	$('#show-results').html('<img src="<?php echo $_SESSION['site_root'];?>images/loading.gif" />');
	
	$.ajax({
        type: "POST",
        data: $(".calc-badges").serialize(),
        url: "process/calc-badges-process.php?process=1",
        success: function(data)
        {
			//alert(data);
			$('#show-results').html(data);
			
		}
    });
	
	return false;
});



</script>
</body>
</html>