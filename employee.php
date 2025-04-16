<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Employee') {
    header('Location: login.html');
    exit();
}

$employeeID = $_SESSION['user']['UserID'];

// Get current tasks
$stmtTasks = $pdo->prepare("
    SELECT s.ScheduleID, u.Name AS CustomerName, o.OfferingName, s.StartDate, s.Status
    FROM Schedule s
    JOIN Users u ON s.CustomerUserID = u.UserID
    JOIN ServiceOffering o ON s.OfferingID = o.OfferingID
    JOIN ScheduleEmployee se ON s.ScheduleID = se.ScheduleID
    WHERE se.EmployeeUserID = :empID
    AND s.Status IN ('Scheduled', 'In Progress')
");
$stmtTasks->execute([':empID' => $employeeID]);
$tasks = $stmtTasks->fetchAll(PDO::FETCH_ASSOC);

// Get completed history
$stmtHistory = $pdo->prepare("
    SELECT s.ScheduleID, o.OfferingName, s.StartDate, s.Status, s.Details
    FROM Schedule s
    JOIN ServiceOffering o ON s.OfferingID = o.OfferingID
    JOIN ScheduleEmployee se ON s.ScheduleID = se.ScheduleID
    WHERE se.EmployeeUserID = :empID
    AND s.Status = 'Completed'
");
$stmtHistory->execute([':empID' => $employeeID]);
$history = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>WrapLab Employee Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css" />
</head>
<body class="d-flex">
  <nav id="sidebar" class="bg-black text-white p-3">
    <h3 class="text-purple mb-4">Employee Dashboard</h3>
    <ul class="nav flex-column">
      <li class="nav-item mb-2"><a href="#my-tasks" class="nav-link text-white">My Tasks</a></li>
      <li class="nav-item mb-2"><a href="#set-availability" class="nav-link text-white">Set Availability</a></li>
      <li class="nav-item mb-2"><a href="#appointment-history" class="nav-link text-white">Appointment History</a></li>
      <li class="nav-item mt-4"><a href="login.html" class="nav-link text-purple">Log out</a></li>
    </ul>
  </nav>

  <div class="flex-grow-1">
    <div class="container-fluid p-4">
      <section id="my-tasks" class="mb-5">
        <h3 class="mb-4">My Tasks</h3>
        <table class="table">
          <thead>
            <tr>
              <th>Appointment</th>
              <th>Customer</th>
              <th>Service</th>
              <th>Date</th>
              <th>Current Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tasks as $t): ?>
              <tr>
                <td>#A<?= htmlspecialchars($t['ScheduleID']) ?></td>
                <td><?= htmlspecialchars($t['CustomerName']) ?></td>
                <td><?= htmlspecialchars($t['OfferingName']) ?></td>
                <td><?= htmlspecialchars(date('Y-m-d', strtotime($t['StartDate']))) ?></td>
                <td><span class="badge bg-<?= $t['Status'] === 'Scheduled' ? 'primary' : 'warning' ?>"><?= htmlspecialchars($t['Status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>

      <section id="set-availability" class="mb-5">
        <h3 class="mb-4">Set Your Availability</h3>
        <form action="set_availability.php" method="POST" class="row g-3">
          <div class="col-md-4">
            <label for="availabilityDate" class="form-label">Select Date</label>
            <input type="date" name="availability_date" id="availabilityDate" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label for="startTime" class="form-label">Start Time</label>
            <input type="time" name="start_time" id="startTime" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label for="endTime" class="form-label">End Time</label>
            <input type="time" name="end_time" id="endTime" class="form-control" required>
          </div>
          <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary">Set Availability</button>
          </div>
        </form>
      </section>

      <section id="appointment-history" class="mb-5">
        <h3 class="mb-4">Appointment History</h3>
        <div class="row">
          <?php foreach ($history as $h): ?>
            <div class="col-md-4 mb-4">
              <div class="card p-3">
                <h5>Appointment: #A<?= htmlspecialchars($h['ScheduleID']) ?></h5>
                <p>Service: <?= htmlspecialchars($h['OfferingName']) ?></p>
                <p>Date: <?= htmlspecialchars(date('Y-m-d', strtotime($h['StartDate']))) ?></p>
                <p>Status: <span class="badge bg-success"><?= htmlspecialchars($h['Status']) ?></span></p>
                <p>Details: <?= htmlspecialchars($h['Details']) ?></p>
                <button class="btn btn-secondary btn-sm">View Full Details</button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
