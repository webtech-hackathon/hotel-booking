
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$connection = getDbConnection();

$user = getUserById($connection, $_SESSION['user_id']);

if (!$user) {
    header("Location: auth/login.php");
    exit();
}

$roomTypes = getRoomTypes($connection);

$upcomingBooking = getUpcomingBooking($connection, $_SESSION['user_id']);

$specialColumn = getSpecialRequestsColumn($connection);

$specialRequests = "";

if ($specialColumn && isset($user[$specialColumn])) {
    $specialRequests = $user[$specialColumn];
}

$subscribeOffers = isset($_COOKIE['subscribe_offers']) && $_COOKIE['subscribe_offers'] === '1';
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <link rel="stylesheet" href="../assets/profile.css">
</head>
<body>
<div class="simple-dashboard">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
    <p>You are logged in successfully.</p>
    <a href="profile.php">Go to Profile & Preferences</a>
</div>
</body>
</html>
