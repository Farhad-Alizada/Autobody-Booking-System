<?php
// login.php
session_start();
require_once 'db_connect.php';  // your PDO $pdo

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: login.html');
  exit;
}

$email    = trim($_POST['email']    ?? '');
$password =            $_POST['password'] ?? '';

// 1) missing?
if ($email === '' || $password === '') {
  header('Location: login.html?error=missing');
  exit;
}

// 2) lookup user
$stmt = $pdo->prepare("SELECT * FROM Users WHERE Email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (! $user) {
  header('Location: login.html?error=notfound');
  exit;
}

$stored = $user['Password'];
$ok     = false;

// 3) check bcrypt
if (password_verify($password, $stored)) {
  $ok = true;
}
// 4) fallback plain‑text for legacy accounts
elseif ($password === $stored) {
  $ok = true;
  // upgrade to bcrypt
  $newHash = password_hash($password, PASSWORD_DEFAULT);
  $update  = $pdo->prepare("UPDATE Users SET Password = ? WHERE UserID = ?");
  $update->execute([$newHash, $user['UserID']]);
}

if (! $ok) {
  header('Location: login.html?error=invalid');
  exit;
}

// 5) success!
unset($user['Password']);
$_SESSION['user'] = $user;

switch ($user['AccessLevel']) {
  case 'Admin':
    header('Location: admin.php');
    break;
  case 'Employee':
    header('Location: employee.php');
    break;
  case 'Customer':
    header('Location: customer.php');
    break;
  default:
    header('Location: login.html?error=invalid');
    break;
}
exit;
