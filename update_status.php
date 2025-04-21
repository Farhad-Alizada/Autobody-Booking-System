<?php
session_start();
require_once 'db_connect.php';

// only employees
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Employee') {
    header('Location: login.html');
    exit();
}

$emp    = $_SESSION['user']['UserID'];
$start  = $_POST['start_date'];   
$end    = $_POST['end_date'];     
$status = $_POST['new_status'];   

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
