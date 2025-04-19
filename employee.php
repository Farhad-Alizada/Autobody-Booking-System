<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Employee') {
    header('Location: login.html');
    exit();
}

$employeeID = $_SESSION['user']['UserID'];

// Fetch tasks assigned to this employee
$stmt = $pdo->prepare("
    SELECT 
        S.ScheduleID,
        S.CustomerUserID,
        S.OfferingID,
        S.StartDate,
        S.EndDate,
        S.Status,
        U.FirstName AS CustomerFirstName,
        U.LastName AS CustomerLastName,
        SO.OfferingName
    FROM Schedule S
    JOIN ScheduleEmployee SE 
        ON S.CustomerUserID = SE.CustomerUserID
        AND S.OfferingID = SE.OfferingID
        AND S.StartDate = SE.StartDate
        AND S.EndDate = SE.EndDate
    JOIN Users U ON S.CustomerUserID = U.UserID
    JOIN ServiceOffering SO ON S.OfferingID = SO.OfferingID
    WHERE SE.EmployeeUserID = :emp
    ORDER BY S.StartDate ASC
");
$stmt->execute([':emp' => $employeeID]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="styles.css">
</head>
<body class="d-flex">
  <nav id="sidebar" class="bg-black text-white p-3">
    <h3 class="text-purple mb-4">Employee Dashboard</h3>
    <ul class="nav flex-column">
      <li class="nav-item mb-2"><a href="#" class="nav-link text-white">My Tasks</a></li>
      <li class="nav-item mb-2"><a href="#availability" class="nav-link text-white">Set Availability</a></li>
      <li class="nav-item mb-2"><a href="#history" class="nav-link text-white">Appointment History</a></li>
      <li class="nav-item mt-4"><a href="login.php" class="nav-link text-purple">Log out</a></li>
    </ul>
  </nav>

  <div class="flex-grow-1 border-start border-4 border-black p-4">
    <h2 class="mb-4">My Tasks</h2>
    <?php foreach ($appointments as $appt): ?>
      <?php if ($appt['Status'] !== 'Completed'): ?>
        <div class="card mb-3 p-3">
          <strong>Customer:</strong> <?= htmlspecialchars($appt['CustomerFirstName'] . ' ' . $appt['CustomerLastName']) ?><br>
          <strong>Service:</strong> <?= htmlspecialchars($appt['OfferingName']) ?><br>
          <strong>Date:</strong> <?= date('Y-m-d H:i', strtotime($appt['StartDate'])) ?><br>

          <form method="POST" action="update_status.php" class="mt-2">
            <input type="hidden" name="customer_id" value="<?= $appt['CustomerUserID'] ?>">
            <input type="hidden" name="offering_id" value="<?= $appt['OfferingID'] ?>">
            <input type="hidden" name="start_date" value="<?= $appt['StartDate'] ?>">
            <input type="hidden" name="end_date" value="<?= $appt['EndDate'] ?>">
            <select name="new_status" class="form-select w-auto d-inline">
              <option <?= $appt['Status'] == 'Scheduled' ? 'selected' : '' ?>>Scheduled</option>
              <option <?= $appt['Status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
              <option <?= $appt['Status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
            </select>
            <button type="submit" class="btn btn-dark btn-sm">Update</button>
          </form>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>

    <h2 class="mt-5" id="availability">Set Your Daily Availability</h2>
<form action="set_availability.php" method="POST">
  <input type="date" name="availability_date" required value="<?=date('Y-m-d')?>">
  <br>Select 1-hour blocks you can work:<br>
  <?php foreach (range(8, 17) as $hour): ?>
    <input type="checkbox" name="time_slots[]" value="<?=sprintf('%02d:00:00', $hour)?>">
    <?=sprintf('%02d:00–%02d:00', $hour, $hour+1)?><br>
  <?php endforeach; ?>
  <button type="submit" class="btn btn-primary mt-2">Save Availability</button>
</form>

    <h3 class="mt-4">Your Upcoming Availabilities</h3>
    <?php
    // Correctly display availability
    $stmt = $pdo->prepare("
      SELECT AvailabilityDate, StartTime, EndTime
      FROM EmployeeAvailability
      WHERE EmployeeUserID = :eid
      AND AvailabilityDate >= CURDATE()
      ORDER BY AvailabilityDate, StartTime
    ");
    $stmt->execute([':eid' => $employeeID]);
    $availabilities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($availabilities)) {
      echo '<p>No availabilities set.</p>';
    } else {
      foreach ($availabilities as $av) {
        echo '<div class="alert alert-secondary">';
        echo '📅 <strong>' . htmlspecialchars($av['AvailabilityDate']) . '</strong> | ';
        echo '⏰ ' . htmlspecialchars(substr($av['StartTime'],0,5)) . '–' . htmlspecialchars(substr($av['EndTime'],0,5));
        echo '</div>';
      }
    }
    ?>

    <h2 class="mt-5" id="history">Appointment History</h2>
    <?php foreach ($appointments as $appt): ?>
      <?php if ($appt['Status'] === 'Completed'): ?>
        <div class="card mb-3 p-3">
          <strong>Customer:</strong> <?= htmlspecialchars($appt['CustomerFirstName'] . ' ' . $appt['CustomerLastName']) ?><br>
          <strong>Service:</strong> <?= htmlspecialchars($appt['OfferingName']) ?><br>
          <strong>Date:</strong> <?= date('Y-m-d H:i', strtotime($appt['StartDate'])) ?><br>
          <span class="badge bg-success">Completed</span>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
