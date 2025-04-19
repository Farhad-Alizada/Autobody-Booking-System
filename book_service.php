<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Customer') {
    header('Location: login.html');
    exit();
}

if (empty($_POST['service_id']) || empty($_POST['service_date']) || empty($_POST['service_time'])) {
    header('Location: customer.php?error=invalid_input');
    exit();
}

$serviceID  = (int) $_POST['service_id'];
$date       = $_POST['service_date'];        // YYYY-MM-DD
$time       = $_POST['service_time'];        // HH:MM
$startDate  = "$date $time:00";
$endDate    = date('Y-m-d H:i:s', strtotime("$startDate +1 hour"));

$customerID = $_SESSION['user']['UserID'];
$adminID    = 1;

// Determine chosen employee
$chosenEmp = !empty($_POST['employee_id']) ? (int) $_POST['employee_id'] : null;

if (!$chosenEmp) {
    $r = $pdo->prepare("SELECT EmployeeUserID FROM DealsWith WHERE CustomerUserID = :cust LIMIT 1");
    $r->execute([':cust' => $customerID]);
    $row = $r->fetch(PDO::FETCH_ASSOC);
    $chosenEmp = (int) ($row['EmployeeUserID'] ?? 0);
}

if (!$chosenEmp) {
    header('Location: customer.php?error=no_employee');
    exit();
}

// Check employee availability
$avail = $pdo->prepare("
  SELECT AvailabilityID
    FROM EmployeeAvailability
   WHERE EmployeeUserID = :eid
     AND AvailabilityDate = :date
     AND TIME(StartTime) = :time
   LIMIT 1
");
$avail->execute([
    ':eid'   => $chosenEmp,
    ':date'  => $date,
    ':time'  => $time . ':00'
]);
$slot = $avail->fetch(PDO::FETCH_ASSOC);
if (!$slot) {
    header('Location: customer.php?error=no_availability');
    exit();
}
$slotID = $slot['AvailabilityID'];

// Check for duplicate booking
$dup = $pdo->prepare("
  SELECT 1
    FROM Schedule
   WHERE CustomerUserID = :cust
     AND OfferingID = :off
     AND StartDate = :start
     AND EndDate = :end
   LIMIT 1
");
$dup->execute([
    ':cust'  => $customerID,
    ':off'   => $serviceID,
    ':start' => $startDate,
    ':end'   => $endDate,
]);
if ($dup->fetch()) {
    header('Location: customer.php?error=duplicate_booking');
    exit();
}

// 💰 NEW: Get service price
$priceStmt = $pdo->prepare("SELECT MinPrice FROM ServiceOffering WHERE OfferingID = :off LIMIT 1");
$priceStmt->execute([':off' => $serviceID]);
$price = (float) $priceStmt->fetchColumn();

try {
    $pdo->beginTransaction();

    // Insert Schedule
    $ins1 = $pdo->prepare("
        INSERT INTO Schedule
          (CustomerUserID, OfferingID, StartDate, EndDate, TotalPrice, AdminUserID)
        VALUES
          (:cust, :off, :start, :end, :price, :admin)
    ");
    $ins1->execute([
        ':cust'  => $customerID,
        ':off'   => $serviceID,
        ':start' => $startDate,
        ':end'   => $endDate,
        ':price' => $price,     // ✅ using real price here
        ':admin' => $adminID,
    ]);

    // Insert ScheduleEmployee
    $ins2 = $pdo->prepare("
        INSERT INTO ScheduleEmployee
          (CustomerUserID, OfferingID, StartDate, EndDate, EmployeeUserID)
        VALUES
          (:cust, :off, :start, :end, :emp)
    ");
    $ins2->execute([
        ':cust'  => $customerID,
        ':off'   => $serviceID,
        ':start' => $startDate,
        ':end'   => $endDate,
        ':emp'   => $chosenEmp,
    ]);

    // Remove booked availability
    $del = $pdo->prepare("DELETE FROM EmployeeAvailability WHERE AvailabilityID = :slot");
    $del->execute([':slot' => $slotID]);

    $pdo->commit();
    header('Location: customer.php?message=booking_success');
    exit();

} catch (PDOException $e) {
    $pdo->rollBack();
    echo "Database error: " . htmlspecialchars($e->getMessage());
    exit();
}
?>
