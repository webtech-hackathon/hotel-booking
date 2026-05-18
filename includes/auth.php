<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/db.php';
require_once __DIR__ . '/../models/UserModel.php';

function getDbConnection() {
    static $connection = null;
    if ($connection === null) {
        $database = new db();
        $connection = $database->connection();
    }
    return $connection;
}

function restoreSessionFromRememberCookie() {
    if (isset($_SESSION['user_id'])) {
        return;
    }

    if (empty($_COOKIE['remember_token'])) {
        return;
    }

    $connection = getDbConnection();
    $user = findUserByRememberToken($connection, $_COOKIE['remember_token']);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
    } else {
        setcookie('remember_token', '', time() - 3600, '/');
    }
}
function requireLogin()
{
    restoreSessionFromRememberCookie();

    if (!isset($_SESSION['user_id'])) {
        header("Location: auth/login.php");
        exit();
    }
}
?>
