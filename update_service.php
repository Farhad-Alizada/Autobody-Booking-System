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
    // Enhanced query to properly get assigned employee
    $stmt = $pdo->prepare("
        SELECT 
            SO.OfferingName,
            SO.ServiceDescription,
            SO.ServicePrice,
            S.StartDate,
            S.EndDate,
            S.Status,
            S.ScheduleID,
            V.Make,
            V.Model,
            V.Year,
            V.VINNumber,
            V.Color,
            CONCAT(U_emp.FirstName, ' ', U_emp.LastName) AS EmployeeName,
            E.JobTitle AS EmployeeJobTitle,
            E.Specialization AS EmployeeSpecialization
        FROM Schedule S
        JOIN ServiceOffering SO ON S.OfferingID = SO.OfferingID
        JOIN Vehicle V ON S.VehicleID = V.VehicleID
        LEFT JOIN ScheduleEmployee SE ON 
            S.CustomerUserID = SE.CustomerUserID AND
            S.OfferingID = SE.OfferingID AND
            S.StartDate = SE.StartDate AND
            S.EndDate = SE.EndDate
        LEFT JOIN Employee E ON SE.EmployeeUserID = E.UserID
        LEFT JOIN Users U_emp ON E.UserID = U_emp.UserID
        WHERE S.CustomerUserID = :custID
        ORDER BY S.StartDate DESC
    ");
    $stmt->bindValue(':custID', $customerID, PDO::PARAM_INT);
    $stmt->execute();

    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Output as HTML cards
    foreach ($appointments as $appt) {
        $date = date('Y-m-d', strtotime($appt['StartDate']));
        $startTime = date('H:i', strtotime($appt['StartDate']));
        $endTime = date('H:i', strtotime($appt['EndDate']));
        $status = $appt['Status'];
        $badgeClass = match ($status) {
            'Scheduled' => 'bg-info',
            'In Progress' => 'bg-warning',
            'Completed' => 'bg-success',
            default => 'bg-secondary',
        };

        // Generate a unique ID for each modal
        $modalId = 'apptDetails-' . $appt['ScheduleID'];

        // Employee information display
        $employeeInfo = 'Not assigned yet';
        if (!empty($appt['EmployeeName'])) {
            $employeeInfo = "{$appt['EmployeeName']} ({$appt['EmployeeJobTitle']})";
            if (!empty($appt['EmployeeSpecialization'])) {
                $employeeInfo .= "<br><small>Specializes in: {$appt['EmployeeSpecialization']}</small>";
            }
        }

        echo "
        <div class='col-md-4 mb-4'>
            <div class='card p-3'>
                <h5>Service: {$appt['OfferingName']}</h5>
                <p>Date: {$date}</p>
                <p>Status: <span class='badge {$badgeClass}'>{$status}</span></p>
                <div class='d-grid gap-2'>
                    <button class='btn btn-secondary btn-sm' data-bs-toggle='modal' data-bs-target='#{$modalId}'>
                        View Details
                    </button>
                    <a href='manage_appointment.php?schedule_id={$appt['ScheduleID']}' class='btn btn-primary btn-sm'>
                        Manage Appointment
                    </a>
                </div>
            </div>
        </div>

        <!-- Modal for this appointment -->
        <div class='modal fade' id='{$modalId}' tabindex='-1' aria-labelledby='{$modalId}Label' aria-hidden='true'>
            <div class='modal-dialog'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title' id='{$modalId}Label'>{$appt['OfferingName']} Details</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                    <div class='modal-body'>
                        <div class='row'>
                            <div class='col-md-6'>
                                <h6>Appointment Details</h6>
                                <p><strong>Date:</strong> {$date}</p>
                                <p><strong>Time:</strong> {$startTime} - {$endTime}</p>
                                <p><strong>Status:</strong> <span class='badge {$badgeClass}'>{$status}</span></p>
                                <p><strong>Service Price:</strong> \$" . number_format($appt['ServicePrice'], 2) . "</p>
                                <p><strong>Description:</strong> {$appt['ServiceDescription']}</p>
                                <p><strong>Assigned Employee:</strong> {$employeeInfo}</p>
                            </div>
                            <div class='col-md-6'>
                                <h6>Vehicle Details</h6>
                                <p><strong>Make:</strong> {$appt['Make']}</p>
                                <p><strong>Model:</strong> {$appt['Model']}</p>
                                <p><strong>Year:</strong> {$appt['Year']}</p>
                                <p><strong>VIN:</strong> {$appt['VINNumber']}</p>
                                <p><strong>Color:</strong> {$appt['Color']}</p>
                            </div>
                        </div>
                    </div>
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                    </div>
                </div>
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