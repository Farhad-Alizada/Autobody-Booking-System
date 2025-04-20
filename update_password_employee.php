<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Employee') {
  header('Location: login.html');
  exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: employee.php');
  exit();
}

$new     = trim($_POST['new_password']     ?? '');
$confirm = trim($_POST['confirm_password'] ?? '');

if ($new === '' || $confirm === '') {
  header('Location: employee.php?error=empty');
  exit();
}
if (strlen($new) < 6) {
  header('Location: employee.php?error=short');
  exit();
}
if ($new !== $confirm) {
  header('Location: employee.php?error=nomatch');
  exit();
}

$hash = password_hash($new, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE Users SET Password = ? WHERE UserID = ?");
$stmt->execute([$hash, $_SESSION['user']['UserID']]);

header('Location: employee.php?msg=updated');
exit();
