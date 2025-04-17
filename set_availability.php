<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Employee') {
    header('Location: login.html');
    exit();
}

$employeeID = $_SESSION['user']['UserID'];
$date = $_POST['availability_date'];
$start = $_POST['start_time'];
$end = $_POST['end_time'];

// Insert availability
$stmt = $pdo->prepare("
    INSERT INTO EmployeeAvailability (EmployeeUserID, AvailabilityDate, StartTime, EndTime)
    VALUES (:eid, :date, :start, :end)
");
$stmt->execute([
    ':eid' => $employeeID,
    ':date' => $date,
    ':start' => $start,
    ':end' => $end
]);

header('Location: employee.php');
exit();
?>
