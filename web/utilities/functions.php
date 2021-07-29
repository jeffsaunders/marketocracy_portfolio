<?php
//Start User Session
session_start();



// Load debug & error logging functions
require("../includes/system-debug-functions.php");

// Connect to DB
require("../../secure/dbconnect.php");

// Load any global functions
require("../includes/system-functions.php");

// Get global settings and functions
require("../includes/system-global.php");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>System Functions</title>
</head>

<body>



<h1>Tools</h1>

<div class="get-session-vars">
	<form action="process.php?process=6" method="post" class="dump-sessions" >
        <h2>Dump Session Vars</h2>
        
        <input type="hidden" name="session" />
        <input type="submit" value="submit"  />
    </form>
    <p id="dump-sessions"></p>
</div>

<div class="url-encode">
	<form action="process.php?process=7" method="post" class="url" >
        <h2>URL ENCODE/DECODE</h2>
        <label>URL</label>
        <input type="text" name="url" style="width:400px;"/><br />
        <label>Type</label>
        <select name="type">
        	<option value="encode">Encode</option>
            <option value="decode">Decode</option>
        </select><br />
        <input type="submit" value="submit"  />
    </form>
    <p id="url"></p>
</div>

<h1>Functions</h1>

<div class="get-unix-time">
	<form action="process.php?process=5" method="post" class="unix-time" >
        <h2>Get Unixtime</h2>
        <label>Date (YYYYMMDD):</label>
        <input type="text" name="date" />
        <input type="submit" value="submit"  />
    </form>
    <p id="unix-time"></p>
</div>

<div class="get-tickets">
    <form action="process.php?process=1" method="post" class="ticket-info" >
        <h2>Get Ticket Info</h2>
        <label>Ticket ID:</label>
        <input type="text" name="ticket" />
        <label>Ticket Item:</label>
        <input type="text" name="info" />
        <input type="submit" value="submit"  />
    </form>
    <p id="ticket-info"></p>
</div><!--get-tickets-->

<div class="get-member">
    <form action="process.php?process=2" method="post" class="member-info" >
        <h2>Get Member Info</h2>
        <label>Member ID:</label>
        <input type="text" name="member" />
        <label>Member Item:</label>
        <input type="text" name="info" />
        <input type="submit" value="submit"  />
    </form>
    <p id="member-info"></p>
</div><!--get-tickets-->

<div class="get-post-url">
    <form action="process.php?process=3" method="post" class="post-url" >
        <h2>Get Post URL</h2>
        <label>POST ID:</label>
        <input type="text" name="post_id" />
        
        <input type="submit" value="submit"  />
    </form>
    <p id="post-url"></p>
</div><!--get-tickets-->

<div class="get-funds">
    <form action="process.php?process=4" method="post" class="fund-info" >
        <h2>Get Fund Info</h2>
        <label>FUND ID:</label>
        <input type="text" name="fund_id" />
        <label>Item:</label>
        <input type="text" name="item" />
        <label>Switch:</label>
        <input type="text" name="switch" />
        
        <input type="submit" value="submit"  />
    </form>
    <p id="fund-info"></p>
</div><!--get-tickets-->




<script src="../plugins/jquery-1.10.2.min.js" type="text/javascript"></script>
<script src="../plugins/jquery-migrate-1.2.1.min.js" type="text/javascript"></script>
<script>
//Process Reply Form via AJAX
$('form.ticket-info').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $(".ticket-info").serialize(),
        url: "process.php?process=1",
        success: function(data)
        {
			//alert(data);
			$('#ticket-info').html(data);
			if(data == ""){
				
			}
		}
    });
	
	return false;
});

//Process Reply Form via AJAX
$('form.member-info').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $(".member-info").serialize(),
        url: "process.php?process=2",
        success: function(data)
        {
			//alert(data);
			$('#member-info').html(data);
			if(data == ""){
				
			}
		}
    });
	
	return false;
});

//Process POST URL Form via AJAX
$('form.post-url').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $(".post-url").serialize(),
        url: "process.php?process=3",
        success: function(data)
        {
			//alert(data);
			$('#post-url').html(data);
			if(data == ""){
				
			}
		}
    });
	
	return false;
});
//Process POST URL Form via AJAX
$('form.fund-info').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $(".fund-info").serialize(),
        url: "process.php?process=4",
        success: function(data)
        {
			//alert(data);
			$('#fund-info').html(data);
			if(data == ""){
				
			}
		}
    });
	
	return false;
});

//Process POST URL Form via AJAX
$('form.unix-time').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $(".unix-time").serialize(),
        url: "process.php?process=5",
        success: function(data)
        {
			//alert(data);
			$('#unix-time').html(data);
			if(data == ""){
				
			}
		}
    });
	
	return false;
});

//Process POST URL Form via AJAX
$('form.dump-sessions').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $(".dump-sessions").serialize(),
        url: "process.php?process=6",
        success: function(data)
        {
			//alert(data);
			$('#dump-sessions').html(data);
			if(data == ""){
				
			}
		}
    });
	
	return false;
});

//Process POST URL Form via AJAX
$('form.url').on('submit', function() {
	$.ajax({
        type: "POST",
        data: $(".url").serialize(),
        url: "process.php?process=7",
        success: function(data)
        {
			//alert(data);
			$('#url').html(data);
			if(data == ""){
				
			}
		}
    });
	
	return false;
});
</script>
</body>
</html>