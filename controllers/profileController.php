<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$connection = getDbConnection();
$userId = $_SESSION['user_id'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/profile.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$nationality = trim($_POST['nationality'] ?? '');
$preferredRoomTypeId = trim($_POST['preferred_room_type_id'] ?? '');
$specialRequests = trim($_POST['special_requests'] ?? '');
$subscribeOffers = isset($_POST['subscribe_offers']);

if ($name === '') {
    $errors[] = 'Name is required.';
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required.';
} elseif (emailExists($connection, $email, $userId)) {
    $errors[] = 'This email is already used by another account.';
}

if ($phone === '') {
    $errors[] = 'Phone is required.';
}

if ($nationality === '') {
    $errors[] = 'Nationality is required.';
}

if (!empty($errors)) {
    $_SESSION['profile_errors'] = $errors;
    header('Location: ../views/profile.php');
    exit;
}

$updated = updateProfile($connection, $userId, $name, $email, $phone, $nationality, $preferredRoomTypeId, $specialRequests);

if ($updated) {
    $_SESSION['name'] = $name;
    $_SESSION['profile_success'] = 'Profile updated successfully.';
} else {
    $_SESSION['profile_errors'] = ['Profile update failed. Please check database columns.'];
}

setcookie('subscribe_offers', $subscribeOffers ? '1' : '0', time() + (86400 * 365), '/');

header('Location: ../views/profile.php');
exit;
?>
