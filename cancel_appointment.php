<?php
// cancel_appointment.php
session_start();
require_once 'db_connect.php';

// only customers
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Customer') {
    header('Location: login.html');
    exit();
}

$customerID = $_SESSION['user']['UserID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_id'])) 
    $sid = intval($_POST['schedule_id']);

    // ensure this appointment belongs to the customer
    $check = $pdo->prepare("
      SELECT 1 FROM Schedule
       WHERE ScheduleID = :sid
         AND CustomerUserID = :cust
    ");
    $check->execute([':sid'=>$sid, ':cust'=>$customerID]);
    // 1) Pull the composite key fields for this ScheduleID
    $appt = $pdo->prepare("
    SELECT CustomerUserID, OfferingID, StartDate, EndDate
        FROM Schedule
    WHERE ScheduleID     = :sid
        AND CustomerUserID = :cust
    ");
    $appt->execute([':sid'=>$sid, ':cust'=>$customerID]);
    $row = $appt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // 2) Delete from ScheduleEmployee using the real columns
        $pdo->prepare("
        DELETE FROM ScheduleEmployee
        WHERE CustomerUserID = :cust
            AND OfferingID      = :off
            AND StartDate       = :start
            AND EndDate         = :end
        ")->execute([
        ':cust'  => $row['CustomerUserID'],
        ':off'   => $row['OfferingID'],
        ':start' => $row['StartDate'],
        ':end'   => $row['EndDate']
        ]);

        // 3) Now delete the Schedule itself
        $pdo->prepare("
        DELETE FROM Schedule
        WHERE ScheduleID     = :sid
            AND CustomerUserID = :cust
        ")->execute([
        ':sid'  => $sid,
        ':cust' => $customerID
        ]);
    }
// go back to dashboard
header('Location: customer.php');
exit();
