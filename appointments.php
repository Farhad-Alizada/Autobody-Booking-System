<?php
session_start();
require_once 'db_connect.php';

// 1) Auth check
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Customer') {
    http_response_code(401);
    echo "<p class='text-danger'>Unauthorized access</p>";
    exit();
}

$customerID = $_SESSION['user']['UserID'];

try {
    // 2) Fetch appointments + all the related info (no V.Color)
    $stmt = $pdo->prepare("
        SELECT
            S.ScheduleID,
            SO.OfferingName,
            SO.ServiceDescription,
            SO.Currency,
            COALESCE(S.TotalPrice, SO.MinPrice) AS Price,
            S.StartDate,
            S.EndDate,
            S.Status,
            V.Make,
            V.Model,
            V.Year,
            V.VINNumber,
            CONCAT(Uemp.FirstName, ' ', Uemp.LastName) AS EmployeeName,
            E.JobTitle,
            E.Specialization
        FROM Schedule S
        JOIN ServiceOffering SO 
          ON S.OfferingID = SO.OfferingID
        LEFT JOIN Vehicle V 
          ON S.VehicleID = V.VehicleID
        LEFT JOIN ScheduleEmployee SE 
          ON S.CustomerUserID = SE.CustomerUserID
         AND S.OfferingID    = SE.OfferingID
         AND S.StartDate     = SE.StartDate
         AND S.EndDate       = SE.EndDate
        LEFT JOIN Employee E 
          ON SE.EmployeeUserID = E.UserID
        LEFT JOIN Users Uemp 
          ON E.UserID = Uemp.UserID
        WHERE S.CustomerUserID = :custID
        ORDER BY S.StartDate DESC
    ");
    $stmt->execute([':custID' => $customerID]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3) If none, placeholder
    if (empty($appointments)) {
        echo "<p class='text-muted'>You have no appointments yet.</p>";
        exit();
    }

    // 4) Render each appointment + modal (no Color line)
    foreach ($appointments as $appt) {
        $id        = (int)$appt['ScheduleID'];
        $name      = htmlspecialchars($appt['OfferingName']);
        $desc      = nl2br(htmlspecialchars($appt['ServiceDescription']));
        $currency  = htmlspecialchars($appt['Currency']);
        $price     = number_format($appt['Price'], 2);
        $startDT   = strtotime($appt['StartDate']);
        $date      = date('Y-m-d', $startDT);
        $t0        = date('g:ia', $startDT);
        $t1        = date('g:ia', strtotime($appt['EndDate']));
        $status    = htmlspecialchars($appt['Status']);

        switch ($status) {
            case 'Scheduled':   $badge = 'bg-info';    break;
            case 'In Progress': $badge = 'bg-warning'; break;
            case 'Completed':   $badge = 'bg-success'; break;
            default:            $badge = 'bg-secondary';
        }

        // vehicle
        $make   = htmlspecialchars($appt['Make']);
        $model  = htmlspecialchars($appt['Model']);
        $year   = (int)$appt['Year'];
        $vin    = htmlspecialchars($appt['VINNumber']);

        // employee
        $empName       = $appt['EmployeeName'] ?: 'Not assigned';
        $jobTitle      = htmlspecialchars($appt['JobTitle'] ?? '');
        $specialization= htmlspecialchars($appt['Specialization'] ?? '');

        $modalId = "apptModal{$id}";
        echo "
        <div class='col-md-4 mb-4'>
          <div class='card p-3'>
            <h5 class='card-title'>Service: {$name}</h5>
            <p class='card-text'>Date: {$date}</p>
            <p class='card-text'>Status: <span class='badge {$badge}'>{$status}</span></p>
            <button class='btn btn-secondary btn-sm' data-bs-toggle='modal' data-bs-target='#{$modalId}'>
              View Details
            </button>
          </div>
        </div>

        <!-- Modal -->
        <div class='modal fade' id='{$modalId}' tabindex='-1' aria-labelledby='{$modalId}Label' aria-hidden='true'>
          <div class='modal-dialog modal-lg'>
            <div class='modal-content'>
              <div class='modal-header'>
                <h5 class='modal-title' id='{$modalId}Label'>{$name} Details</h5>
                <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
              </div>
              <div class='modal-body'>
                <div class='row'>
                  <!-- Left: Appointment/Service -->
                  <div class='col-md-6'>
                    <h6>Appointment Details</h6>
                    <p><strong>Date:</strong> {$date}</p>
                    <p><strong>Time:</strong> {$t0} – {$t1}</p>
                    <p><strong>Status:</strong> <span class='badge {$badge}'>{$status}</span></p>
                    <p><strong>Price:</strong> {$currency}{$price}</p>
                    <p><strong>Description:</strong><br>{$desc}</p>
                    <p><strong>Assigned Employee:</strong> {$empName}";
        if ($jobTitle)      echo "<br><small>Title: {$jobTitle}</small>";
        if ($specialization) echo "<br><small>Specializes in: {$specialization}</small>";
        echo "</p>
                  </div>
                  <!-- Right: Vehicle -->
                  <div class='col-md-6'>
                    <h6>Vehicle Details</h6>
                    <p><strong>Make:</strong> {$make}</p>
                    <p><strong>Model:</strong> {$model}</p>
                    <p><strong>Year:</strong> {$year}</p>
                    <p><strong>VIN:</strong> {$vin}</p>
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

} catch (PDOException $e) {
    echo "<p class='text-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
