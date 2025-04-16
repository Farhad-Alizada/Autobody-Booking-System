<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Customer') {
    // redirect or handle error
    header('Location: login.html');
    exit();
}

require_once 'db_connect.php';

// Validate form data
if (!empty($_POST['service_id']) && !empty($_POST['service_date']) && !empty($_POST['service_time'])) {
    $serviceID = $_POST['service_id'];
    $date = $_POST['service_date'];
    $time = $_POST['service_time'];
    
    // Combine date and time for StartDate (example)
    $startDate = $date . ' ' . $time . ':00';
    // For simplicity, assume EndDate is 1 hour later. Adjust logic as needed.
    $endDate = date('Y-m-d H:i:s', strtotime($startDate . ' +1 hour'));

    // Insert into Schedule table
    // Suppose the AdminUserID is always 1 for now, or dynamically found in your system
    $adminID = 1;
    $customerID = $_SESSION['user']['UserID'];
    
    $stmt = $pdo->prepare("INSERT INTO Schedule 
        (CustomerUserID, OfferingID, StartDate, EndDate, TotalPrice, AdminUserID) 
        VALUES (:custID, :offID, :start, :end, 0, :adminID)");
    $stmt->bindValue(':custID', $customerID);
    $stmt->bindValue(':offID', $serviceID);
    $stmt->bindValue(':start', $startDate);
    $stmt->bindValue(':end', $endDate);
    $stmt->bindValue(':adminID', $adminID);
    $stmt->execute();
    
    header('Location: customer.html?message=booking_success');
    exit();
} else {
    header('Location: customer.html?error=invalid_input');
    exit();
}
