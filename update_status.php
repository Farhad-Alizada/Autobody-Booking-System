<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Employee') {
    header('Location: login.html');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerID = $_POST['customer_id'];
    $offeringID = $_POST['offering_id'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $newStatus = $_POST['new_status'];

    $stmt = $pdo->prepare("
        UPDATE Schedule
        SET Status = :status
        WHERE CustomerUserID = :custID AND OfferingID = :offID AND StartDate = :start AND EndDate = :end
    ");

    $stmt->execute([
        ':status' => $newStatus,
        ':custID' => $customerID,
        ':offID' => $offeringID,
        ':start' => $startDate,
        ':end' => $endDate,
    ]);

    header('Location: employee.php');
    exit();
}
?>
