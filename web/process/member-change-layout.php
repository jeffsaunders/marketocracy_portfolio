<?php
session_start();

$_SESSION['layout'] = $_REQUEST['layout'];

header('Location: /');


?>