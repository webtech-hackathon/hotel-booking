<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/profile.css">
</head>
<body>
<div class="simple-dashboard">
    <h1>Welcome Admin</h1>
    <p>Admin dashboard loaded successfully.</p>
    <a href="../controllers/logoutController.php">Logout</a>
</div>
</body>
</html>
