<?php
session_start();
require_once 'db_connect.php';

// 1) Auth & input check
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Customer' ||
    empty($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: customer.php');
    exit();
}
$scheduleID = (int)$_GET['id'];
$customerID = $_SESSION['user']['UserID'];

// 2) Fetch appointment + full price range + coupon
$stmt = $pdo->prepare("
    SELECT 
        S.ScheduleID,
        SO.OfferingName,
        SO.Currency,

        -- full range
        SO.MinPrice      AS MinPrice,
        SO.MaxPrice      AS MaxPrice,

        -- coupon info
        DC.DiscountAmount,
        S.CouponNumber,
        S.Status,
        S.StartDate,
        S.EndDate,

        -- employee
        U.FirstName      AS EmpFirst,
        U.LastName       AS EmpLast
    FROM Schedule S
    JOIN ScheduleEmployee SE 
      ON S.CustomerUserID = SE.CustomerUserID
     AND S.OfferingID     = SE.OfferingID
     AND S.StartDate      = SE.StartDate
     AND S.EndDate        = SE.EndDate
    JOIN Users U 
      ON SE.EmployeeUserID = U.UserID
    JOIN ServiceOffering SO 
      ON S.OfferingID      = SO.OfferingID
    LEFT JOIN discountcoupon DC 
      ON S.CouponNumber   = DC.CouponNumber
    WHERE S.ScheduleID      = :sid
      AND S.CustomerUserID  = :cust
    LIMIT 1
");
$stmt->execute([':sid'=>$scheduleID, ':cust'=>$customerID]);
$appt = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$appt) {
    echo "Appointment not found.";
    exit();
}

// 3) Compute discounted min/max
$min    = (float)$appt['MinPrice'];
$max    = (float)$appt['MaxPrice'];
$disc   = isset($appt['DiscountAmount']) ? (float)$appt['DiscountAmount'] : 0.0;
$minDisc = max(0, $min - $disc);
$maxDisc = max(0, $max - $disc);

// 4) Badge color
switch ($appt['Status']) {
  case 'Scheduled':    $badge = 'bg-info';    break;
  case 'In Progress':  $badge = 'bg-warning'; break;
  case 'Completed':    $badge = 'bg-success'; break;
  default:             $badge = 'bg-secondary';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Appointment Details</title>
  <link 
    rel="stylesheet" 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  />
  <link 
    rel="stylesheet" 
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
  />
  <link 
    href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" 
    rel="stylesheet"
  >
  <link rel="stylesheet" href="styles.css" />
</head>
<body class="d-flex">

  <!-- SIDEBAR -->
  <nav id="sidebar" class="bg-black text-white p-3">
    <h3 class="text-purple mb-4">Customer Dashboard</h3>
    <ul class="nav flex-column">
      <li class="nav-item mb-2"><a href="customer.php#book-service" class="nav-link text-white">Book a Service</a></li>
      <li class="nav-item mb-2"><a href="customer.php#my-appointments" class="nav-link text-white">My Appointments</a></li>
      <li class="nav-item mb-2"><a href="customer.php#feedback" class="nav-link text-white">Leave Feedback</a></li>
      <li class="nav-item mt-4"><a href="login.php" class="nav-link text-purple">Log out</a></li>
    </ul>
  </nav>

  <!-- MAIN CONTENT -->
  <div class="flex-grow-1 border-start border-4 border-black">
    <div class="container-fluid p-4">
      <a href="customer.php" class="btn btn-primary mb-4">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
      </a>

      <h2 class="mb-4">Appointment Details</h2>

      <dl class="row">
        <dt class="col-sm-3">Service</dt>
        <dd class="col-sm-9"><?= htmlspecialchars($appt['OfferingName']) ?></dd>

        <dt class="col-sm-3">Date &amp; Time</dt>
        <dd class="col-sm-9">
          <?= date('M j, Y g:ia', strtotime($appt['StartDate'])) ?>
          &mdash;
          <?= date('g:ia', strtotime($appt['EndDate'])) ?>
        </dd>

        <dt class="col-sm-3">Assigned Employee</dt>
        <dd class="col-sm-9"><?= htmlspecialchars($appt['EmpFirst'].' '.$appt['EmpLast']) ?></dd>

        <dt class="col-sm-3">Status</dt>
        <dd class="col-sm-9">
          <span class="badge <?= $badge ?>">
            <?= htmlspecialchars($appt['Status']) ?>
          </span>
        </dd>

        <dt class="col-sm-3">Price Range</dt>
        <dd class="col-sm-9">
          <?= htmlspecialchars($appt['Currency']) . number_format($minDisc,2) ?>
          &nbsp;–&nbsp;
          <?= htmlspecialchars($appt['Currency']) . number_format($maxDisc,2) ?>
        </dd>
      </dl>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
