<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in and is a Customer
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Customer') {
    header("Location: login.php");
    exit();
}

$customerID = $_SESSION['user']['UserID'];
$scheduleID = $_GET['schedule_id'] ?? null;

// Fetch appointment details
$appointment = null;
$vehicles = [];
$services = [];

try {
    // Get appointment details
    if ($scheduleID) {
        $stmt = $pdo->prepare("
            SELECT S.*, SO.OfferingName, SO.ServiceDescription, SO.ServicePrice, 
                   V.VehicleID, V.Make, V.Model, V.Year
            FROM Schedule S
            JOIN ServiceOffering SO ON S.OfferingID = SO.OfferingID
            JOIN Vehicle V ON S.VehicleID = V.VehicleID
            WHERE S.ScheduleID = :scheduleID AND S.CustomerUserID = :customerID
        ");
        $stmt->execute([':scheduleID' => $scheduleID, ':customerID' => $customerID]);
        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$appointment) {
            throw new Exception("Appointment not found or you don't have permission to access it.");
        }
    }

    // Get customer's vehicles for the update form
    $stmt = $pdo->prepare("SELECT VehicleID, Make, Model, Year FROM Vehicle WHERE UserID = :customerID");
    $stmt->execute([':customerID' => $customerID]);
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get available services
    $stmt = $pdo->prepare("SELECT OfferingID, OfferingName, ServiceDescription, ServicePrice FROM ServiceOffering");
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error = $e->getMessage();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['delete'])) {
            // Delete appointment
            $stmt = $pdo->prepare("DELETE FROM Schedule WHERE ScheduleID = :scheduleID AND CustomerUserID = :customerID");
            $stmt->execute([':scheduleID' => $scheduleID, ':customerID' => $customerID]);

            $_SESSION['message'] = "Appointment deleted successfully.";
            header("Location: appointments.php");
            exit();

        } elseif (isset($_POST['update'])) {
            // Update appointment
            $vehicleID = $_POST['vehicle_id'];
            $offeringID = $_POST['service_id'];
            $date = $_POST['appointment_date'];
            $startTime = $_POST['start_time'];
            $endTime = $_POST['end_time'];

            // Combine date and time
            $startDateTime = date('Y-m-d H:i:s', strtotime("$date $startTime"));
            $endDateTime = date('Y-m-d H:i:s', strtotime("$date $endTime"));

            $stmt = $pdo->prepare("
                UPDATE Schedule 
                SET VehicleID = :vehicleID, 
                    OfferingID = :offeringID, 
                    StartDate = :startDate, 
                    EndDate = :endDate
                WHERE ScheduleID = :scheduleID AND CustomerUserID = :customerID
            ");
            $stmt->execute([
                ':vehicleID' => $vehicleID,
                ':offeringID' => $offeringID,
                ':startDate' => $startDateTime,
                ':endDate' => $endDateTime,
                ':scheduleID' => $scheduleID,
                ':customerID' => $customerID
            ]);

            $_SESSION['message'] = "Appointment updated successfully.";
            header("Location: appointments.php");
            exit();
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Manage Appointment</h3>
                </div>
                <div class="card-body">
                    <?php if ($appointment): ?>
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Service</label>
                                <select class="form-select" name="service_id" required>
                                    <?php foreach ($services as $service): ?>
                                        <option value="<?= $service['OfferingID'] ?>"
                                            <?= $service['OfferingID'] == $appointment['OfferingID'] ? 'selected' : '' ?>>
                                            <?= $service['OfferingName'] ?> - $<?= number_format($service['ServicePrice'], 2) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Vehicle</label>
                                <select class="form-select" name="vehicle_id" required>
                                    <?php foreach ($vehicles as $vehicle): ?>
                                        <option value="<?= $vehicle['VehicleID'] ?>"
                                            <?= $vehicle['VehicleID'] == $appointment['VehicleID'] ? 'selected' : '' ?>>
                                            <?= "{$vehicle['Make']} {$vehicle['Model']} ({$vehicle['Year']})" ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date</label>
                                    <input type="date" class="form-control" name="appointment_date"
                                           value="<?= date('Y-m-d', strtotime($appointment['StartDate'])) ?>" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Start Time</label>
                                    <input type="time" class="form-control" name="start_time"
                                           value="<?= date('H:i', strtotime($appointment['StartDate'])) ?>" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">End Time</label>
                                    <input type="time" class="form-control" name="end_time"
                                           value="<?= date('H:i', strtotime($appointment['EndDate'])) ?>" required>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="submit" name="delete" class="btn btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this appointment?')">
                                    Delete Appointment
                                </button>
                                <button type="submit" name="update" class="btn btn-primary">
                                    Update Appointment
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning">No appointment found.</div>
                        <a href="appointments.php" class="btn btn-secondary">Back to Appointments</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
