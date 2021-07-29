<?php
$file = '../../images/1x1.png';
$type = 'image/png';
header('Content-Type:'.$type);
header('Content-Length: ' . filesize($file));
readfile($file);