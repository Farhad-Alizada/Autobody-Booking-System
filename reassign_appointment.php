<?php
session_start();
require_once 'db_connect.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel']!=='Admin') {
  header('Location: login.html');
  exit();
}

// Inputs
$scheduleId     = intval($_POST['schedule_id'] ?? 0);
$newEmployeeId  = intval($_POST['new_employee_id'] ?? 0);

if ($scheduleId && $newEmployeeId) {
  // Fetch the appointment to get its composite key and service
  $stmt = $pdo->prepare("
    SELECT CustomerUserID, OfferingID, StartDate, EndDate
      FROM Schedule
     WHERE ScheduleID = ?
  ");
  $stmt->execute([$scheduleId]);
  $appt = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($appt) {
    // Replace the assignment
    $upd = $pdo->prepare("
      UPDATE ScheduleEmployee
         SET EmployeeUserID = ?
       WHERE CustomerUserID = ?
         AND OfferingID     = ?
         AND StartDate      = ?
         AND EndDate        = ?
    ");
    $upd->execute([
      $newEmployeeId,
      $appt['CustomerUserID'],
      $appt['OfferingID'],
      $appt['StartDate'],
      $appt['EndDate']
    ]);
  }
}

header('Location: admin.php?msg=reassigned');
exit();
