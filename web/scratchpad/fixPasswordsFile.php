<?php
$fileIn = fopen("passwordsAll.txt", "r");
$fileOut = fopen("./passwordsFixed.txt", "w");

while ($row = fgets($fileIn)){
//	echo $row."<br>";
//	echo '"'.addslashes(strstr($row, ",", true)).'","'.addslashes(trim(substr(strstr($row, ","), 1))).'"<br>';
	$newRow = '"'.addslashes(strstr($row, ",", true)).'","'.addslashes(trim(substr(strstr($row, ","), 1))).'"'.PHP_EOL;
	fwrite($fileOut, $newRow);
}

fclose($fileIn);
fclose($fileOut);

?>
