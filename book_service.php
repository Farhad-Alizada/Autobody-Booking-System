<?php
// book_service.php  –  create a new booking for a customer
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db_connect.php';

// 1) Auth & basic input check
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Customer') {
    header('Location: login.html');
    exit();
}

$must = ['service_id','service_date','service_time',
         'vehicle_make','vehicle_model','vehicle_year'];
foreach ($must as $f) {
    if (empty($_POST[$f])) {
        header('Location: customer.php?error=invalid_input');
        exit();
    }
}

// 2) Normalise the incoming data
$serviceID   = (int) $_POST['service_id'];
$date        = $_POST['service_date'];            // YYYY‑MM‑DD
$time        = $_POST['service_time'];            // HH:MM
$startDate   = "$date $time:00";
$endDate     = date('Y-m-d H:i:s', strtotime("$startDate +1 hour"));

$customerID  = $_SESSION['user']['UserID'];
$adminID     = 1;                                 // hard‑coded for now

// 3) Resolve the employee (picker → DealsWith → error)
$chosenEmp = !empty($_POST['employee_id']) ? (int)$_POST['employee_id'] : null;

if (!$chosenEmp) {
    $r = $pdo->prepare("SELECT EmployeeUserID
                          FROM dealswith
                         WHERE CustomerUserID = ?
                         LIMIT 1");
    $r->execute([$customerID]);
    $chosenEmp = $r->fetchColumn();
}

if (!$chosenEmp) {
    header('Location: customer.php?error=no_employee');
    exit();
}

// 4) Make sure that employee really has that slot
$slotStmt = $pdo->prepare("
    SELECT AvailabilityID
      FROM employeeavailability
     WHERE EmployeeUserID  = ?
       AND AvailabilityDate = ?
       AND TIME(StartTime)  = ?
     LIMIT 1");
$slotStmt->execute([$chosenEmp, $date, "$time:00"]);
$slotID = $slotStmt->fetchColumn();

if (!$slotID) {
    header('Location: customer.php?error=no_availability');
    exit();
}

// 5) Stop duplicate bookings
$dup = $pdo->prepare("SELECT 1
                        FROM schedule
                       WHERE CustomerUserID = ?
                         AND OfferingID      = ?
                         AND StartDate       = ?
                         AND EndDate         = ?
                       LIMIT 1");
$dup->execute([$customerID, $serviceID, $startDate, $endDate]);
if ($dup->fetch()) {
    header('Location: customer.php?error=duplicate_booking');
    exit();
}

// 6) Pull base prices for the service
$row = $pdo->prepare("SELECT MinPrice, MaxPrice
                        FROM serviceoffering
                       WHERE OfferingID = ?
                       LIMIT 1");
$row->execute([$serviceID]);
$svc = $row->fetch(PDO::FETCH_ASSOC);
$minPrice = (float)$svc['MinPrice'];
$maxPrice = (float)$svc['MaxPrice'];

//7) Validate / apply coupon
$couponNum = trim($_POST['coupon_number'] ?? '');
$discount  = 0.0;          // default: no discount

if ($couponNum !== '') {
    $c = $pdo->prepare("SELECT DiscountAmount
                          FROM discountcoupon
                         WHERE CouponNumber = ?
                           AND OfferingID    = ?   
                         LIMIT 1");
    $c->execute([$couponNum, $serviceID]);
    $cpnRow = $c->fetch(PDO::FETCH_ASSOC);

    if (!$cpnRow) {        // coupon is not valid for this service
        header('Location: customer.php?error=bad_coupon');
        exit();
    }
    $discount = (float)$cpnRow['DiscountAmount'];
}

/* price after discount – never negative */
$finalPrice = max(0, $minPrice - $discount);

// 8) Vehicle record
$vehStmt = $pdo->prepare("
    INSERT INTO vehicle
      (CustomerUserID, Make, Model, Year, VINNumber)
    VALUES
      (:cust, :make, :model, :year, :vin)");
$vehStmt->execute([
    ':cust'  => $customerID,
    ':make'  => $_POST['vehicle_make'],
    ':model' => $_POST['vehicle_model'],
    ':year'  => (int)$_POST['vehicle_year'],
    ':vin'   => $_POST['vin_number'] ?: null
]);
$vehicleID = $pdo->lastInsertId();

// 9) Write everything in one transaction
try {
    $pdo->beginTransaction();

    /* schedule */
    $insSch = $pdo->prepare("
        INSERT INTO schedule
          (CustomerUserID, OfferingID, StartDate, EndDate,
           TotalPrice, AdminUserID, VehicleID, Status, CouponNumber)
        VALUES
          (:cust, :off, :start, :end,
           :price, :admin, :veh, 'Scheduled', :coupon)");
    $insSch->execute([
        ':cust'   => $customerID,
        ':off'    => $serviceID,
        ':start'  => $startDate,
        ':end'    => $endDate,
        ':price'  => $finalPrice,
        ':admin'  => $adminID,
        ':veh'    => $vehicleID,
        ':coupon' => $couponNum ?: null
    ]);

    /* linking table */
    $insSE = $pdo->prepare("
        INSERT INTO scheduleemployee
          (CustomerUserID, OfferingID, StartDate, EndDate, EmployeeUserID)
        VALUES
          (:cust, :off, :start, :end, :emp)");
    $insSE->execute([
        ':cust'  => $customerID,
        ':off'   => $serviceID,
        ':start' => $startDate,
        ':end'   => $endDate,
        ':emp'   => $chosenEmp
    ]);

    /* remove that availability slot */
    $pdo->prepare("DELETE FROM employeeavailability WHERE AvailabilityID = ?")
        ->execute([$slotID]);

    $pdo->commit();
    header('Location: customer.php?message=booking_success');
    exit();

} catch (PDOException $e) {
    $pdo->rollBack();
    echo "Database error: " . htmlspecialchars($e->getMessage());
    exit();
}
