<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Customer') {
    header('Location: login.html');
    exit();
}

if (!empty($_POST['service_id']) && !empty($_POST['service_date']) && !empty($_POST['service_time'])) {
    $serviceID = $_POST['service_id'];
    $date = $_POST['service_date'];
    $time = $_POST['service_time'];

    $startDate = $date . ' ' . $time . ':00';
    $endDate = date('Y-m-d H:i:s', strtotime($startDate . ' +1 hour'));
    $adminID = 1; // temporary default
    $customerID = $_SESSION['user']['UserID'];

    // ✅ STEP 1: Check for duplicate before inserting
    $check = $pdo->prepare("SELECT * FROM Schedule 
        WHERE CustomerUserID = :custID 
        AND OfferingID = :offID 
        AND StartDate = :start 
        AND EndDate = :end");
    $check->execute([
        ':custID' => $customerID,
        ':offID' => $serviceID,
        ':start' => $startDate,
        ':end' => $endDate
    ]);

    if ($check->rowCount() > 0) {
        // Duplicate found
        header('Location: customer.php?error=duplicate_booking');
        exit();
    }

    // ✅ STEP 2: If no duplicate, insert into Schedule
    $stmt = $pdo->prepare("INSERT INTO Schedule 
        (CustomerUserID, OfferingID, StartDate, EndDate, TotalPrice, AdminUserID) 
        VALUES (:custID, :offID, :start, :end, 0, :adminID)");

    $stmt->execute([
        ':custID' => $customerID,
        ':offID' => $serviceID,
        ':start' => $startDate,
        ':end' => $endDate,
        ':adminID' => $adminID
    ]);

    header('Location: customer.php?message=booking_success');
    exit();
} else {
    header('Location: customer.php?error=invalid_input');
    exit();
}
