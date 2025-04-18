<?php
// login.php
session_start();
require_once 'db_connect.php'; // your PDO $pdo

// Only respond to POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html');
    exit;
}

$email    = trim($_POST['email']    ?? '');
$password = $_POST['password']      ?? '';

// 1) missing fields?
if ($email === '' || $password === '') {
    header('Location: login.html?error=missing_fields');
    exit;
}

// 2) lookup user
$stmt = $pdo->prepare('SELECT UserID, FirstName, LastName, Email, Password, AccessLevel 
                       FROM Users 
                       WHERE Email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: login.html?error=user_not_found');
    exit;
}

// 3) verify password
if (!password_verify($password, $user['Password'])) {
    header('Location: login.html?error=invalid_credentials');
    exit;
}

// 4) all good — store session (remove password)
unset($user['Password']);
$_SESSION['user'] = $user;

// 5) redirect by role
switch ($user['AccessLevel']) {
    case 'Admin':
        header('Location: admin.php');
        break;
    case 'Employee':
        header('Location: employee_dashboard.php');
        break;
    case 'Customer':
        header('Location: customer.php');
        break;
    default:
        header('Location: login.html?error=unknown_role');
        break;
}
exit;
