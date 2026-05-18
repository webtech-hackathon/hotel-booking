<?php
include_once __DIR__ . "/../models/db.php";
include_once __DIR__ . "/../models/UserModel.php";

session_start();

$database = new db();
$connection = $database->connection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $uemail = trim($_POST["email"] ?? '');
    $upass = $_POST["password"] ?? '';
    $remember_me = isset($_POST["remember_me"]);

    $user = loginUser($connection, $uemail);

    if ($user && password_verify($upass, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        if ($remember_me) {
            $token = bin2hex(random_bytes(32));
            saveRememberToken($connection, $user['id'], $token);
            setcookie("remember_token", $token, time() + (86400 * 30), "/");
        }

        if ($user['role'] == "admin") {
            header('Location: ../views/admin.php');
            exit;
        }

        header('Location: ../views/profile.php');
        exit;
    }

    header('Location: ../views/auth/login.php?error=1');
    exit;
}

header('Location: ../views/auth/login.php');
exit;
?>
