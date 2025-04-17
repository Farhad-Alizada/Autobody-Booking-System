<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Employee') {
    header('Location: login.html');
    exit();
}

$employeeID = $_SESSION['user']['UserID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['availability_id'])) {
    $availabilityID = $_POST['availability_id'];

    $stmt = $pdo->prepare("DELETE FROM EmployeeAvailability WHERE AvailabilityID = :id AND EmployeeUserID = :empID");
    $stmt->execute([
        ':id' => $availabilityID,
        ':empID' => $employeeID
    ]);
}

// 👇 This ensures the user is sent *back* to employee.php
header("Location: employee.php");
exit();
