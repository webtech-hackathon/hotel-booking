<?php
header('Content-Type: application/json');

include_once __DIR__ . "/../models/db.php";
include_once __DIR__ . "/../models/UserModel.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'errors' => ['general' => 'Invalid request method.']]);
    exit;
}

$database = new db();
$connection = $database->connection();

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$nationality = trim($_POST['nationality'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

$errors = [];

if ($name === '') {
    $errors['name'] = 'Full name is required.';
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'A valid email address is required.';
} elseif (emailExists($connection, $email)) {
    $errors['email'] = 'This email is already registered.';
}

if ($phone === '') {
    $errors['phone'] = 'Phone number is required.';
}

if ($nationality === '') {
    $errors['nationality'] = 'Please select your nationality.';
}

if ($password === '' || strlen($password) < 8) {
    $errors['password'] = 'Password must be at least 8 characters.';
}

if ($password !== $confirm) {
    $errors['confirm'] = 'Passwords do not match.';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$result = createUser($connection, $name, $email, $hashedPassword, $phone, $nationality, 'guest');

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'errors' => ['general' => 'Registration failed. Check database/table columns.']]);
}
exit;
