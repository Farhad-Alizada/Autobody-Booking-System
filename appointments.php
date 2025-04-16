<?php
session_start();
require_once 'db_connect.php';

// Ensure the user is logged in and is a Customer
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Customer') {
    http_response_code(401); // Unauthorized
    echo "Unauthorized access";
    exit();
}

$customerID = $_SESSION['user']['UserID'];

try {
    // Fetch appointments with service name and status
    $stmt = $pdo->prepare("
        SELECT 
            SO.OfferingName,
            S.StartDate,
            S.EndDate,
            S.Status
        FROM Schedule S
        JOIN ServiceOffering SO ON S.OfferingID = SO.OfferingID
        WHERE S.CustomerUserID = :custID
        ORDER BY S.StartDate DESC
    ");
    $stmt->bindValue(':custID', $customerID, PDO::PARAM_INT);
    $stmt->execute();

    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Output as HTML cards
    foreach ($appointments as $appt) {
        $date = date('Y-m-d', strtotime($appt['StartDate']));
        $status = $appt['Status'];
        $badgeClass = match ($status) {
            'Scheduled' => 'bg-info',
            'In Progress' => 'bg-warning',
            'Completed' => 'bg-success',
            default => 'bg-secondary',
        };

        echo "
        <div class='col-md-4 mb-4'>
            <div class='card p-3'>
                <h5>Service: {$appt['OfferingName']}</h5>
                <p>Date: {$date}</p>
                <p>Status: <span class='badge {$badgeClass}'>{$status}</span></p>
                <button class='btn btn-secondary btn-sm'>View Details</button>
            </div>
        </div>
        ";
    }

    if (empty($appointments)) {
        echo "<p class='text-muted'>You have no appointments yet.</p>";
    }

} catch (PDOException $e) {
    echo "Error retrieving appointments: " . $e->getMessage();
}
?>
