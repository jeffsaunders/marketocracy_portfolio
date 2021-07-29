<?php

if(isset($_GET['tag'])){
	$tag = preg_replace('#[^a-z0-9_]#i', '', $_GET['tag']);
	
	$fulltag = "#".$tag;	
	echo $fulltag;
}

?>