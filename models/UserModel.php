<?php
include_once __DIR__ . "/db.php";

function createUser($connection, $name, $email, $password, $phone, $nationality, $role) {
    $sql = "INSERT INTO users (name, email, password_hash, phone, nationality, role) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("ssssss", $name, $email, $password, $phone, $nationality, $role);
    return $stmt->execute();
}

function emailExists($connection, $email, $excludeUserId = null) {
    if ($excludeUserId) {
        $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $connection->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("si", $email, $excludeUserId);
    } else {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $connection->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("s", $email);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function loginUser($connection, $email) {
    $sql = "SELECT id, name, email, role, password_hash FROM users WHERE email = ?";
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        return $result->fetch_assoc();
    }

    return false;
}

function saveRememberToken($connection, $userId, $token) {
    $hashedToken = password_hash($token, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET remember_token = ? WHERE id = ?";
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("si", $hashedToken, $userId);
    return $stmt->execute();
}

function findUserByRememberToken($connection, $plainToken) {
    $sql = "SELECT id, name, role, remember_token FROM users WHERE remember_token IS NOT NULL AND remember_token != ''";
    $result = $connection->query($sql);

    if (!$result) {
        return false;
    }

    while ($user = $result->fetch_assoc()) {
        if (password_verify($plainToken, $user['remember_token'])) {
            return $user;
        }
    }

    return false;
}

function getUserById($connection, $userId) {
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $connection->prepare($sql);
    if (!$stmt) return false;

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        return $result->fetch_assoc();
    }

    return false;
}

function columnExists($connection, $table, $column)
{
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $column = $connection->real_escape_string($column);

    $sql = "SHOW COLUMNS FROM `$table` LIKE '$column'";
    $result = $connection->query($sql);

    return $result && $result->num_rows > 0;
}
function getSpecialRequestsColumn($connection) {
    if (columnExists($connection, 'users', 'special_requests')) {
        return 'special_requests';
    }
    if (columnExists($connection, 'users', 'special_request')) {
        return 'special_request';
    }
    return null;
}

function updateProfile($connection, $userId, $name, $email, $phone, $nationality, $preferredRoomTypeId, $specialRequests) {
    $specialColumn = getSpecialRequestsColumn($connection);
    $preferredRoomTypeId = $preferredRoomTypeId === '' ? null : (int)$preferredRoomTypeId;

    if ($specialColumn) {
        $sql = "UPDATE users SET name = ?, email = ?, phone = ?, nationality = ?, preferred_room_type_id = ?, `$specialColumn` = ? WHERE id = ?";
        $stmt = $connection->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("ssssisi", $name, $email, $phone, $nationality, $preferredRoomTypeId, $specialRequests, $userId);
    } else {
        $sql = "UPDATE users SET name = ?, email = ?, phone = ?, nationality = ?, preferred_room_type_id = ? WHERE id = ?";
        $stmt = $connection->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("ssssii", $name, $email, $phone, $nationality, $preferredRoomTypeId, $userId);
    }

    return $stmt->execute();
}

function getRoomTypes($connection) {
    $result = $connection->query("SHOW TABLES LIKE 'room_types'");
    if (!$result || $result->num_rows === 0) {
        return [];
    }

    $roomTypes = [];
    $query = $connection->query("SELECT id, name FROM room_types ORDER BY name ASC");
    if ($query) {
        while ($row = $query->fetch_assoc()) {
            $roomTypes[] = $row;
        }
    }
    return $roomTypes;
}

function getUpcomingBooking($connection, $userId) {
    $result = $connection->query("SHOW TABLES LIKE 'bookings'");
    if (!$result || $result->num_rows === 0) {
        return null;
    }

    $sql = "SELECT b.id, b.checkin_date, b.checkout_date, b.status, rt.name AS room_type_name
            FROM bookings b
            JOIN rooms r ON b.room_id = r.id
            JOIN room_types rt ON r.room_type_id = rt.id
            WHERE b.user_id = ?
              AND b.checkin_date >= CURDATE()
              AND b.status NOT IN ('Cancelled', 'Checked-Out')
            ORDER BY b.checkin_date ASC
            LIMIT 1";

    $stmt = $connection->prepare($sql);
    if (!$stmt) return null;

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        return $result->fetch_assoc();
    }

    return null;
}
?>
