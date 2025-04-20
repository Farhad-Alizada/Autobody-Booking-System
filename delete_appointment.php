<?php
session_start();
require_once 'db_connect.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel']!=='Admin') {
  header('Location: login.html');
  exit();
}

$id = intval($_POST['schedule_id'] ?? 0);
if ($id) {
  // Remove from ScheduleEmployee
  $pdo->prepare("
    DELETE SE
      FROM ScheduleEmployee SE
      JOIN Schedule S 
        ON SE.CustomerUserID = S.CustomerUserID
       AND SE.OfferingID     = S.OfferingID
       AND SE.StartDate      = S.StartDate
       AND SE.EndDate        = S.EndDate
    WHERE S.ScheduleID = ?
  ")->execute([$id]);

  // Remove from Schedule
  $pdo->prepare("DELETE FROM Schedule WHERE ScheduleID=?")
      ->execute([$id]);
}

header('Location: admin.php?msg=deleted');
exit();
