<?php
session_start();
require_once 'db_connect.php';

// 1) Must be a logged‑in Customer
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Customer') {
    http_response_code(401);
    echo "Unauthorized access";
    exit();
}

$customerID = $_SESSION['user']['UserID'];

try {
    // 2) Load this customer’s appointments
    $stmt = $pdo->prepare("\n        SELECT 
            S.ScheduleID,
            SO.OfferingName,
            S.StartDate,
            S.Status
        FROM Schedule S
        JOIN ServiceOffering SO 
          ON S.OfferingID = SO.OfferingID
        WHERE S.CustomerUserID = :custID
        ORDER BY S.StartDate DESC
    ");
    $stmt->bindValue(':custID', $customerID, PDO::PARAM_INT);
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3) If none, show a placeholder
    if (empty($appointments)) {
        echo "<p class='text-muted'>You have no appointments yet.</p>";
        exit();
    }

    // 4) Otherwise, render each as a Bootstrap card
    foreach ($appointments as $appt) {
        $scheduleID   = (int) $appt['ScheduleID'];
        $serviceName  = htmlspecialchars($appt['OfferingName']);
        $date         = date('Y-m-d', strtotime($appt['StartDate']));
        $status       = htmlspecialchars($appt['Status']);

        // map status → badge color
        switch ($status) {
            case 'Scheduled':   $badgeClass = 'bg-info';    break;
            case 'In Progress': $badgeClass = 'bg-warning'; break;
            case 'Completed':   $badgeClass = 'bg-success'; break;
            default:            $badgeClass = 'bg-secondary';
        }

        echo "
        <div class='col-md-4 mb-4'>
          <div class='card p-3'>
            <h5 class='card-title'>Service: {$serviceName}</h5>
            <p class='card-text'>Date: {$date}</p>
            <p class='card-text'>
              Status: <span class='badge {$badgeClass}'>{$status}</span>
            </p>
            <a 
              href='appointment_details.php?id={$scheduleID}' 
              class='btn btn-secondary btn-sm'
            >
              View Details
            </a>
          </div>
        </div>
        ";
    }

} catch (PDOException $e) {
    echo "<p class='text-danger'>Error retrieving appointments: "
         . htmlspecialchars($e->getMessage()) . "</p>";
}
