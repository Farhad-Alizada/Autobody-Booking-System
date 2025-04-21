<?php
session_start();
require_once 'db_connect.php';

// only admins
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Admin') {
  header('Location: login.html');
  exit();
}

// must be POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: admin.php');
  exit();
}

$new     = trim($_POST['new_password']     ?? '');
$confirm = trim($_POST['confirm_password'] ?? '');

// 1) both fields required
if ($new === '' || $confirm === '') {
  header('Location: admin.php?error=empty_password');
  exit();
}

// 2) passwords must match
if ($new !== $confirm) {
  header('Location: admin.php?error=nomatch_password');
  exit();
}

// 3) enforce minimum length of 6
if (strlen($new) < 6) {
  header('Location: admin.php?error=short_password');
  exit();
}

// 4) hash & save
$hash = password_hash($new, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("
  UPDATE Users
     SET Password = ?
   WHERE UserID = ?
");
$stmt->execute([
  $hash,
  $_SESSION['user']['UserID']
]);

header('Location: admin.php?msg=password_updated');
exit();
