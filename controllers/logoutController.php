<?php
session_start();
$_SESSION = [];
session_destroy();
setcookie('remember_token', '', time() - 3600, '/');
header('Location: ../views/auth/login.php');
exit;
?>
