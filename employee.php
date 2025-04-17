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

// Fetch Current Tasks (Scheduled or In Progress)
$stmtTasks = $pdo->prepare("SELECT 
    s.CustomerUserID, s.OfferingID, s.StartDate, s.EndDate, s.Status,
    u.FirstName, u.LastName,
    o.OfferingName
FROM Schedule s
JOIN ScheduleEmployee se ON 
    s.CustomerUserID = se.CustomerUserID AND 
    s.OfferingID = se.OfferingID AND 
    s.StartDate = se.StartDate AND 
    s.EndDate = se.EndDate
JOIN Customer c ON s.CustomerUserID = c.UserID
JOIN Users u ON c.UserID = u.UserID
JOIN ServiceOffering o ON s.OfferingID = o.OfferingID
WHERE se.EmployeeUserID = :empID AND s.Status IN ('Scheduled', 'In Progress')");
$stmtTasks->execute(['empID' => $employeeID]);
$tasks = $stmtTasks->fetchAll(PDO::FETCH_ASSOC);

// Fetch Completed Appointment History
$stmtHistory = $pdo->prepare("SELECT 
    s.CustomerUserID, s.OfferingID, s.StartDate, s.EndDate, s.Status,
    o.OfferingName
FROM Schedule s
JOIN ScheduleEmployee se ON 
    s.CustomerUserID = se.CustomerUserID AND 
    s.OfferingID = se.OfferingID AND 
    s.StartDate = se.StartDate AND 
    s.EndDate = se.EndDate
JOIN ServiceOffering o ON s.OfferingID = o.OfferingID
WHERE se.EmployeeUserID = :empID AND s.Status = 'Completed'");
$stmtHistory->execute(['empID' => $employeeID]);
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
              <th>Customer</th>
              <th>Service</th>
              <th>Date</th>
              <th>Status</th>
              <th>Update</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tasks as $t): ?>
              <tr>
                <td><?= htmlspecialchars($t['FirstName'] . ' ' . $t['LastName']) ?></td>
                <td><?= htmlspecialchars($t['OfferingName']) ?></td>
                <td><?= htmlspecialchars(date('Y-m-d', strtotime($t['StartDate']))) ?></td>
                <td><span class="badge bg-<?= $t['Status'] === 'Scheduled' ? 'primary' : 'warning' ?>"><?= htmlspecialchars($t['Status']) ?></span></td>
                <td>
                  <form action="update_status.php" method="POST" class="d-flex align-items-center">
                    <input type="hidden" name="customer_id" value="<?= $t['CustomerUserID'] ?>">
                    <input type="hidden" name="offering_id" value="<?= $t['OfferingID'] ?>">
                    <input type="hidden" name="start_date" value="<?= $t['StartDate'] ?>">
                    <input type="hidden" name="end_date" value="<?= $t['EndDate'] ?>">
                    <select name="new_status" class="form-select form-select-sm me-2" style="width: 120px;">
                      <option value="Scheduled" <?= $t['Status'] === 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
                      <option value="In Progress" <?= $t['Status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                      <option value="Completed" <?= $t['Status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                    <button type="submit" class="btn btn-secondary btn-sm">Update</button>
                  </form>
                </td>
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
<!-- DISPLAY EXISTING AVAILABILITY WITH DELETE OPTION -->
<section class="mb-5">
  <h3 class="mb-3">Your Upcoming Availabilities</h3>
  <ul class="list-group">
    <?php
    $stmtAvail = $pdo->prepare("
      SELECT AvailabilityID, AvailabilityDate, StartTime, EndTime
      FROM EmployeeAvailability
      WHERE EmployeeUserID = :empID
      ORDER BY AvailabilityDate, StartTime
    ");
    $stmtAvail->execute([':empID' => $employeeID]);
    $availabilities = $stmtAvail->fetchAll(PDO::FETCH_ASSOC);

    if (count($availabilities) > 0):
      foreach ($availabilities as $slot): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          📅 <?= htmlspecialchars($slot['AvailabilityDate']) ?> | ⏰ 
          <?= htmlspecialchars(substr($slot['StartTime'], 0, 5)) ?> - 
          <?= htmlspecialchars(substr($slot['EndTime'], 0, 5)) ?>
          
          <form method="POST" action="delete_availability.php" class="ms-3 mb-0">
            <input type="hidden" name="availability_id" value="<?= $slot['AvailabilityID'] ?>">
            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
          </form>
        </li>
    <?php 
      endforeach;
    else: ?>
      <li class="list-group-item">No availability set yet.</li>
    <?php endif; ?>
  </ul>
</section>


      <section id="appointment-history" class="mb-5">
        <h3 class="mb-4">Appointment History</h3>
        <div class="row">
          <?php foreach ($history as $h): ?>
            <div class="col-md-4 mb-4">
              <div class="card p-3">
                <h5>Service: <?= htmlspecialchars($h['OfferingName']) ?></h5>
                <p>Date: <?= htmlspecialchars(date('Y-m-d', strtotime($h['StartDate']))) ?></p>
                <p>Status: <span class="badge bg-success">Completed</span></p>
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
