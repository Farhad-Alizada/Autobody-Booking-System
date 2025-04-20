<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db_connect.php';

// 1) Auth check
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Customer') {
    header('Location: login.html');
    exit();
}

// 2) Validate inputs (including vehicle fields)
if (
    empty($_POST['service_id']) ||
    empty($_POST['service_date']) ||
    empty($_POST['service_time']) ||
    empty($_POST['vehicle_make']) ||
    empty($_POST['vehicle_model']) ||
    empty($_POST['vehicle_year'])
) {
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

// 3) Determine which employee to use (preference or DealsWith)
$chosenEmp = !empty($_POST['employee_id'])
    ? (int) $_POST['employee_id']
    : null;

if (!$chosenEmp) {
    $r = $pdo->prepare("
        SELECT EmployeeUserID
          FROM DealsWith
         WHERE CustomerUserID = :cust
         LIMIT 1
    ");
    $r->execute([':cust' => $customerID]);
    $row = $r->fetch(PDO::FETCH_ASSOC);
    $chosenEmp = $row['EmployeeUserID'] ?? null;
}

if (!$chosenEmp) {
    header('Location: customer.php?error=no_employee');
    exit();
}

// 4) Check that that employee actually has that slot free
$avail = $pdo->prepare("
    SELECT AvailabilityID
      FROM EmployeeAvailability
     WHERE EmployeeUserID  = :eid
       AND AvailabilityDate = :date
       AND TIME(StartTime)  = :time
     LIMIT 1
");
$avail->execute([
    ':eid'  => $chosenEmp,
    ':date' => $date,
    ':time' => $time . ':00'
]);
$slot = $avail->fetch(PDO::FETCH_ASSOC);
if (!$slot) {
    header('Location: customer.php?error=no_availability');
    exit();
}
$slotID = $slot['AvailabilityID'];

// 5) Prevent duplicate bookings
$dup = $pdo->prepare("
    SELECT 1
      FROM Schedule
     WHERE CustomerUserID = :cust
       AND OfferingID      = :off
       AND StartDate       = :start
       AND EndDate         = :end
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

// 6) Fetch the service’s base price
$priceStmt = $pdo->prepare("
    SELECT MinPrice
      FROM ServiceOffering
     WHERE OfferingID = :off
     LIMIT 1
");
$priceStmt->execute([':off' => $serviceID]);
$price = (float) $priceStmt->fetchColumn();

// 7) Pull the vehicle inputs
$vehicleMake  = $_POST['vehicle_make'];
$vehicleModel = $_POST['vehicle_model'];
$vehicleYear  = (int) $_POST['vehicle_year'];
$vinNumber    = !empty($_POST['vin_number']) ? $_POST['vin_number'] : null;

try {
    $pdo->beginTransaction();

    // 8) Insert the new Vehicle row
    $veh = $pdo->prepare("
        INSERT INTO Vehicle
          (CustomerUserID, Make, Model, Year, VINNumber)
        VALUES
          (:cust, :make, :model, :year, :vin)
    ");
    $veh->execute([
        ':cust'  => $customerID,
        ':make'  => $vehicleMake,
        ':model' => $vehicleModel,
        ':year'  => $vehicleYear,
        ':vin'   => $vinNumber
    ]);
    $vehicleID = $pdo->lastInsertId();

    // 9) Insert into Schedule (including that VehicleID)
    $ins1 = $pdo->prepare("
        INSERT INTO Schedule
          (CustomerUserID, OfferingID, StartDate, EndDate,
           TotalPrice, AdminUserID, VehicleID, Status)
        VALUES
          (:cust, :off, :start, :end,
           :price, :admin, :veh, 'Scheduled')
    ");
    $ins1->execute([
        ':cust'   => $customerID,
        ':off'    => $serviceID,
        ':start'  => $startDate,
        ':end'    => $endDate,
        ':price'  => $price,
        ':admin'  => $adminID,
        ':veh'    => $vehicleID
    ]);

    // 10) Insert into ScheduleEmployee
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
        ':emp'   => $chosenEmp
    ]);

    // 11) Remove that hour from availability
    $del = $pdo->prepare("
        DELETE FROM EmployeeAvailability 
         WHERE AvailabilityID = :slot
    ");
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
