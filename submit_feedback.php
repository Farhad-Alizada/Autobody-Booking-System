<?php
// show all errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db_connect.php';

// only customers can submit feedback
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Customer') {
    header('Location: login.html');
    exit();
}

// validate inputs
if (
    !empty($_POST['feedbackName']) &&
    !empty($_POST['feedback']) &&
    !empty($_POST['rating'])
) {
    $name       = trim($_POST['feedbackName']);
    $comments   = trim($_POST['feedback']);
    $rating     = (int)$_POST['rating'];
    $custID     = $_SESSION['user']['UserID'];
    $now        = date('Y-m-d H:i:s');

    try {
        $stmt = $pdo->prepare("
            INSERT INTO feedback
              (CustomerUserID, FeedbackDate, FeedbackName, Comments, Rating)
            VALUES
              (:custID, :date, :name, :comments, :rating)
        ");
        $stmt->execute([
            ':custID'   => $custID,
            ':date'     => $now,
            ':name'     => $name,
            ':comments' => $comments,
            ':rating'   => $rating,
        ]);

        header('Location: customer.php?feedback=success');
        exit();

    } catch (PDOException $e) {
        echo "Database error: " . htmlspecialchars($e->getMessage());
        exit();
    }

} else {
    header('Location: customer.php?feedback=error');
    exit();
}
