<?php
session_start();
require_once 'db_connect.php';

// only employees
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Employee') {
    header('Location: login.html');
    exit();
}

$emp    = $_SESSION['user']['UserID'];
$start  = $_POST['start_date'];   // e.g. "2025-04-21 12:00:00"
$end    = $_POST['end_date'];     // e.g. "2025-04-21 13:00:00"
$status = $_POST['new_status'];   // "Scheduled" / "In Progress" / "Completed"

// update via the same 4‑col composite key you used in your SELECT
$stmt = $pdo->prepare("
  UPDATE Schedule S
  JOIN scheduleemployee SE
    ON S.CustomerUserID = SE.CustomerUserID
   AND S.OfferingID     = SE.OfferingID
   AND S.StartDate      = SE.StartDate
   AND S.EndDate        = SE.EndDate
  SET S.Status = :status
  WHERE SE.EmployeeUserID = :emp
    AND S.StartDate        = :start
    AND S.EndDate          = :end
");
$stmt->execute([
  ':status' => $status,
  ':emp'    => $emp,
  ':start'  => $start,
  ':end'    => $end,
]);

// send them back to employee.php
header('Location: employee.php');
exit();
