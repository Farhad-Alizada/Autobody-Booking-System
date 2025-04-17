<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Admin') {
  header('Location: login.html');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
  $userID = $_POST['user_id'];

  try {
    $pdo->beginTransaction();

    $pdo->prepare("DELETE FROM Employee WHERE UserID = ?")->execute([$userID]);
    $pdo->prepare("DELETE FROM Users WHERE UserID = ?")->execute([$userID]);

    $pdo->commit();
    header('Location: admin.php?success=1');
    exit();
  } catch (PDOException $e) {
    $pdo->rollBack();
    echo "Failed to delete employee: " . $e->getMessage();
  }
}
?>
