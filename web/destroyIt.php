<?php
// Destroy session with extreme prejudice
if (isset($_COOKIE[session_name()])) setcookie(session_name(), '', time()-42000, '/');
session_destroy();
?>
