<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Employee') {
  header('Location: login.html');
  exit();
}

$empID = $_SESSION['user']['UserID'];
$date  = $_POST['availability_date'];
$slots = $_POST['time_slots'] ?? [];

// Remove old availabilities first
$del = $pdo->prepare("
  DELETE FROM EmployeeAvailability
  WHERE EmployeeUserID = :eid AND AvailabilityDate = :date
");
$del->execute([':eid' => $empID, ':date' => $date]);

// Insert selected availability slots
$ins = $pdo->prepare("
  INSERT INTO EmployeeAvailability (EmployeeUserID, AvailabilityDate, StartTime, EndTime)
  VALUES (:eid, :date, :start, :end)
");

foreach ($slots as $start) {
  $end = date('H:i:s', strtotime("$start +1 hour"));
  $ins->execute([
    ':eid'   => $empID,
    ':date'  => $date,
    ':start' => $start,
    ':end'   => $end,
  ]);
}

header('Location: employee.php');
exit();
?>
