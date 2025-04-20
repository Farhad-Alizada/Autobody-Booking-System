<?php
session_start();
require_once 'db_connect.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel']!=='Employee') {
  header('Location: login.html');
  exit();
}

$eid = $_SESSION['user']['UserID'];
$phone   = trim($_POST['phone']   ?? '');
$address = trim($_POST['address'] ?? '');

if ($phone && $address) {
  // update both tables
  $u = $pdo->prepare("UPDATE Users    SET PhoneNumber = ? WHERE UserID = ?");
  $e = $pdo->prepare("UPDATE Employee SET Address     = ? WHERE UserID = ?");
  $pdo->beginTransaction();
  try {
    $u->execute([$phone,   $eid]);
    $e->execute([$address, $eid]);
    $pdo->commit();
  } catch (\Throwable $ex) {
    $pdo->rollBack();
    // handle error or log...
  }
}
header('Location: employee.php');
exit;
