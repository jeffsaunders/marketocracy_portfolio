<?php
/*	This utility reproduces a database table from FrontBase in MySQL and imports all the data.

	Parameters:	table={table name} *Required.  Table will be created as specified, including case (if applicable).
				task={replace} *Not required.  Any value other than "replace" indicates "append", except "test".
				A replace will drop the table and recreate it, otherwise the data is appended to the existing table.
				chunk={chunk size} *Not required.  Defaults to 100,000.
				live={true} *Not required.  "true" will access the live database, any other value will access the development database

	Example:	http://10.0.0.11/fbTableImporter.php?table=MMANAGER&task=replace&chunk=10000&live=true

	Notes:		If the destination table exists, and dropping it was not specified, it WILL attempt to import the data
				regardless of the schema of the existing destination.  If it does not match it will fail.
				This utility could, of course, be made more generic...when we all have more time.

	Created:	05/01/14 by Jeff Saunders
	Modified:	05/02/14 by Jeff Saunders	Switched to live database
				05/04/14 by Jeff Saunders	Added "chunk" functionality
				05/05/14 by Jeff Saunders	Enhanced "chunk" facility and general clean-up
				05/06/14 by Jeff Saunders	Added FrontBase CLOB support
				05/14/14 by Jeff Saunders	Changed FB database structure naming for MySQL, added "test" mode
*/

// Tell me when things go sideways
error_reporting(E_ALL);
ini_set('display_errors', '1');
/*
// Die if there's no table specified
if (!isset($_REQUEST['table']) || $_REQUEST['table'] == ""){
	die("ERROR - You Must Pass a Table Name (i.e. ?table=[name]) - Process Aborted!");
}
$table = $_REQUEST['table'];

// Determine if we should replace the destination or append to it
$dropTable = false;
if (isset($_REQUEST['task']) && $_REQUEST['task'] == "replace"){
	$dropTable = true;
}
*/
// Relax
//set_time_limit(3600); //60 minutes
set_time_limit(43200); //12 HOURS
$startingTime = time();

// Connect to MySQL
$dbHost = "10.0.0.20";
$dbUser = "marketocracy";
$dbPass = "KfabyZcbE3";
$dbName = "marketocracy";
$linkID = mysql_connect($dbHost, $dbUser, $dbPass) or die("Could not connect to MySQL");
mysql_select_db($dbName, $linkID) or die("Could not select ".$dbName." DB in MySQL");
/*
// Connect to FrontBase
// Set which database server to pull from
$dbHost = "10.0.0.19"; // Dev (db3)
if (isset($_REQUEST['live']) && $_REQUEST['live'] == "true"){
	$dbHost = "192.168.111.141"; // Live (db1-new)
}
$dbUser = "eouser";
$dbPass = ""; // No password
$dbName = "MARKETOCRACY";
$dbMarketocracy = fbsql_connect($dbHost, $dbUser, $dbPass) or die("Could not connect to FrontBase");
fbsql_select_db($dbName, $dbMarketocracy) or die("Could not select ".$dbName." DB in FrontBase");

// Test to make sure the table specified exists in FrontBase - die here if it doesn't
// Also gives a row count for "chunking"
$query = "SELECT COUNT(*) FROM ".$dbUser.".".$table.";";
$rs_rows = fbsql_query($query, $dbMarketocracy) or die ("ERROR - Table ".$table." Does Not Exist in DB ".$dbName." in FrontBase - Process Aborted!");
*/
// So far, so good...is this a test run?
if (isset($_REQUEST['task']) && $_REQUEST['task'] !== "test"){
	// Drop the existing table if told to
	if ($dropTable){
		$query = "DROP TABLE ".$table;
	//die($query);
		echo "Dropping Existing Table ".$table." in MySQL.&nbsp;&nbsp;";
	//	$rs_drop = mysql_query($query, $linkID) or die ("ERROR - Query '".$query."' Failed for DB ".$dbName." in MySQL - Process Aborted!");
		// If it fails it's because it doesn't exist, so just pretend it was dropped and continue on
		$rs_drop = mysql_query($query, $linkID);
		echo "...Dropped!<br>";
	}

	// Create the destination table if it doesn't exist in MySQL (it won't if it was just dropped)
	if (mysql_query("DESCRIBE ".$table, $linkID) == false){ // If the query fails the table doesn't exist, so create it
		// Get a row from FrontBase so we have the column names
		$query = "SELECT TOP 1 * FROM ".$table.";";
		$result = fbsql_query($query, $dbMarketocracy) or die ("ERROR - Query '".$query."' Failed for DB ".$dbName." in FrontBase - Process Aborted!");
		// Build query based on the structure of the FrontBase table
		$query = "CREATE TABLE `".$table."` (";
		// Get the number of columns from FrontBase
		$columns = fbsql_num_fields($result);
		// Loop through them
		for ($cnt = 0; $cnt < $columns; $cnt++){
			// Handle CLOBs specially
			if (fbsql_field_type($result, $cnt) == "CLOB"){
				$query .= "`".fbsql_field_name($result, $cnt)."` TEXT, ";
			}else{
				// Set default destination column size (all VARCHAR except CLOBs)
				// FrontBase returns 0 for the size of non-text fields, so can't just make them all the same size as the source
				$size = 255;
				// If the source column size is larger than 255, get it's size and use that instead
				if (fbsql_field_len($result, $cnt) > 255){
					$size = fbsql_field_len($result, $cnt);
				}
				$query .= "`".fbsql_field_name($result, $cnt)."` VARCHAR(".$size."), ";
			}
		}
		// Strip off the trailing comma and space
		$query = substr($query, 0, -2);
		$query .= ")";
	//die($query);
		echo ($dropTable ? "Rec" : "C")."reating Table ".$table." in MySQL.&nbsp;&nbsp;";
		flush();
		$rs_create = mysql_query($query, $linkID) or die ("ERROR - Query '".$query."' Failed for DB ".$dbName." in MySQL - Process Aborted!");
		echo "...Created!<br>";
	}

	// Get the size of the source table (if it exists, of course) from the above query
	$row = fbsql_fetch_array($rs_rows);
	$records = $row[0];

	if (isset($_REQUEST['chunk']) && is_numeric($_REQUEST['chunk'])){
		$chunkSize = $_REQUEST['chunk'];
	}else{
		$chunkSize = 100000; // One hundred thousand
	}
	$chunks = floor($records/$chunkSize);

	// Start 'er up.
	echo "Importing Table ".$table." into MySQL.&nbsp;&nbsp;...Done!<br>";
	flush();
	$insertCounter = 0;
	for ($chunk = 0; $chunk <= $chunks; $chunk++){

		// Where to start retrieving
		$start = ($chunk * $chunkSize);

		// Determine number of rows to retrieve
		$quantity = $chunkSize;
		if ($chunks == 0){
			$quantity = $records;  // Only one chunk, so grab 'em all
		}else if ($chunk == $chunks){
			$quantity = ($records-($chunk * $chunkSize));  // Last chunk, so grab the remainder
		}

		// Get data from FrontBase
		echo "Getting Chunk ".($chunk+1)." of Table ".$table." from FrontBase.&nbsp;&nbsp;";
		flush();
		$query = "SELECT TOP(".$start.", ".$quantity.") * FROM ".$table.";";
	//$query = "SELECT TOP 1 * FROM ".$table.";";  // Testing
	//echo $query;//die();
		$rs_chunk = fbsql_query($query, $dbMarketocracy) or die ("ERROR - Query '".$query."' Failed for DB ".$dbName." in FrontBase - Process Aborted!");
		echo "...Got it!<br>";

	/*
	echo "<pre>";
	while ($row = fbsql_fetch_assoc($rs_chunk)) {
		echo print_r($row)."<br><br>"; flush();
	}
	echo "</pre>";
	die();
	*/

	//update `TABLE` set `PRIMARYKEY` = concat('X\'', `PRIMARYKEY`, '\'')

		// Insert the data
		while ($row = fbsql_fetch_assoc($rs_chunk)) {
			// Build query based on the structure of the FrontBase table
			$query = "INSERT INTO `".$table."` (";
			foreach (array_keys($row) as $key => $value){
				$query .= "`".$value."`, ";
			}
			$query = substr($query, 0, -2);
			$query .= ") VALUES (";
			$cnt = 0;
			foreach ($row as $key => $value){
				if (fbsql_field_type($rs_chunk, $cnt) == "CLOB"){
					$value = "";
				}
				// If this was a binary key in FrontBase (starts with X')...
				if (substr(trim($value), 0, 2) == "X'"){
					// ...convert the alpha to upper case
					$value = strtoupper($value);
				}
				$query .= "'".addslashes(trim($value))."', ";
				$cnt++;
			}
			// Trim the trailing comma and space
			$query = substr($query, 0, -2);
			$query = $query.");";
	//echo $query;//die();
	//print_r(array_keys($row));//die();
			$rs_insert = mysql_query($query, $linkID) or die ("ERROR - Query '".$query."' Failed for DB ".$dbName." in MySQL - Process Aborted!");
			$insertCounter++;
		}
		// Free the result set - FrontBase freaks if you don't explicitly do this before using the same RS var again
		fbsql_free_result($rs_chunk);
	}
	//echo "...Done!<br><br>";
	echo "<br>".number_format($insertCounter)." Row".($insertCounter != 1 ? "s" : "")." Inserted into ".$table." Table in ".(time()-$startingTime)." Seconds.<br><br>Import Complete!";
}else{
	echo "<br>Successfully connected to all required resources.";
}

// Close database connections
fbsql_close($dbMarketocracy);
mysql_close($linkID);

?>
