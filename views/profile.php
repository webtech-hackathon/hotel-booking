<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$connection = getDbConnection();
$user = getUserById($connection, $_SESSION['user_id']);
$roomTypes = getRoomTypes($connection);
$upcomingBooking = getUpcomingBooking($connection, $_SESSION['user_id']);
$specialColumn = getSpecialRequestsColumn($connection);
$specialRequests = $specialColumn && isset($user[$specialColumn]) ? $user[$specialColumn] : '';
$subscribeOffers = isset($_COOKIE['subscribe_offers']) && $_COOKIE['subscribe_offers'] === '1';
$errors = $_SESSION['profile_errors'] ?? [];
$success = $_SESSION['profile_success'] ?? '';
unset($_SESSION['profile_errors'], $_SESSION['profile_success']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile — Zyphora Suites</title>
    <link rel="stylesheet" href="../assets/profile.css">
</head>
<body>
<div class="page-wrap">
    <div class="profile-card">
        <div class="left-panel">
            <p class="panel-brand">Zyphora Suites</p>
            <h1>Guest Profile</h1>
            <p class="panel-sub">Manage your identity, preferences, and upcoming stay.</p>
            <a class="logout-link" href="../controllers/logoutController.php">Logout</a>
        </div>

        <div class="right-panel">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h2>
            <p class="muted">Update your profile and room preferences.</p>

            <?php if (!empty($success)): ?>
                <div class="success-box"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="error-box">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="../controllers/profileController.php" class="profile-form">
                <div class="grid-two">
                    <div>
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">
                    </div>
                    <div>
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                    </div>
                    <div>
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    <div>
                        <label for="nationality">Nationality</label>
                        <select id="nationality" name="nationality">
                            <?php
                            $nationalities = ['Bangladeshi', 'German', 'Pakistani', 'American', 'British', 'Canadian', 'Australian', 'Italian', 'Other'];
                            foreach ($nationalities as $nationality):
                            ?>
                                <option value="<?php echo $nationality; ?>" <?php echo (($user['nationality'] ?? '') === $nationality) ? 'selected' : ''; ?>>
                                    <?php echo $nationality; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <label for="preferred_room_type_id">Preferred Room Type</label>
                <select id="preferred_room_type_id" name="preferred_room_type_id">
                    <option value="">No preference</option>
                    <?php foreach ($roomTypes as $type): ?>
                        <option value="<?php echo (int)$type['id']; ?>" <?php echo ((string)($user['preferred_room_type_id'] ?? '') === (string)$type['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($roomTypes)): ?>
                    <p class="hint">No room types found yet. Task 2 will add room type data.</p>
                <?php endif; ?>

                <label for="special_requests">Special Requests</label>
                <textarea id="special_requests" name="special_requests" rows="4" placeholder="Late check-in, extra pillow, quiet room..."><?php echo htmlspecialchars($specialRequests); ?></textarea>

                <label class="checkbox-row">
                    <input type="checkbox" name="subscribe_offers" value="1" <?php echo $subscribeOffers ? 'checked' : ''; ?>>
                    Subscribe to offers
                </label>

                <button type="submit">Save Profile</button>
            </form>

            <div class="booking-card">
                <h3>Upcoming Booking Summary</h3>
                <?php if ($upcomingBooking): ?>
                    <p><strong>Room Type:</strong> <?php echo htmlspecialchars($upcomingBooking['room_type_name']); ?></p>
                    <p><strong>Check-in:</strong> <?php echo htmlspecialchars($upcomingBooking['checkin_date']); ?></p>
                    <p><strong>Check-out:</strong> <?php echo htmlspecialchars($upcomingBooking['checkout_date']); ?></p>
                    <span class="status-badge"><?php echo htmlspecialchars($upcomingBooking['status']); ?></span>
                <?php else: ?>
                    <p class="muted">No upcoming stays.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
