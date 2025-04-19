<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Customer') {
    header('Location: login.html');
    exit();
}

if (!empty($_POST['service_id']) && !empty($_POST['service_date']) && !empty($_POST['service_time']) &&
    !empty($_POST['vehicle_make']) && !empty($_POST['vehicle_model']) && !empty($_POST['vehicle_year'])) {

    // Get form data
    $serviceID = $_POST['service_id'];
    $date = $_POST['service_date'];
    $time = $_POST['service_time'];

    // Vehicle details
    $vehicleMake = $_POST['vehicle_make'];
    $vehicleModel = $_POST['vehicle_model'];
    $vehicleYear = $_POST['vehicle_year'];
    $vinNumber = !empty($_POST['vin_number']) ? $_POST['vin_number'] : null;

    $startDate = $date . ' ' . $time . ':00';
    $endDate = date('Y-m-d H:i:s', strtotime($startDate . ' +1 hour'));
    $adminID = 1; // temporary default for admin
    $employeeID = 2; // Using ID 2 which exists in your sample data (Richard Tan)
    $customerID = $_SESSION['user']['UserID'];

    // Check for duplicate booking
    $check = $pdo->prepare("SELECT * FROM Schedule 
        WHERE CustomerUserID = :custID 
        AND OfferingID = :offID 
        AND StartDate = :start 
        AND EndDate = :end");
    $check->execute([
        ':custID' => $customerID,
        ':offID' => $serviceID,
        ':start' => $startDate,
        ':end' => $endDate
    ]);

    if ($check->rowCount() > 0) {
        header('Location: customer.php?error=duplicate_booking');
        exit();
    }

    // First insert vehicle details
    $stmt_vehicle = $pdo->prepare("INSERT INTO Vehicle 
        (CustomerUserID, Make, Model, Year, VINNumber) 
        VALUES (:custID, :make, :model, :year, :vinNumber)");
    $stmt_vehicle->execute([
        ':custID' => $customerID,
        ':make' => $vehicleMake,
        ':model' => $vehicleModel,
        ':year' => $vehicleYear,
        ':vinNumber' => $vinNumber
    ]);
    $vehicleID = $pdo->lastInsertId();

    // Then insert into Schedule (without EmployeeUserID)
    $stmt = $pdo->prepare("INSERT INTO Schedule 
        (CustomerUserID, OfferingID, StartDate, EndDate, TotalPrice, AdminUserID, VehicleID, Status) 
        VALUES (:custID, :offID, :start, :end, 0, :adminID, :vehicleID, 'Scheduled')");
    $stmt->execute([
        ':custID' => $customerID,
        ':offID' => $serviceID,
        ':start' => $startDate,
        ':end' => $endDate,
        ':adminID' => $adminID,
        ':vehicleID' => $vehicleID
    ]);

    // Get the schedule details for the ScheduleEmployee table
    $scheduleDetails = [
        'customerID' => $customerID,
        'offeringID' => $serviceID,
        'startDate' => $startDate,
        'endDate' => $endDate
    ];

    // Determine which employee to assign based on service type
    $stmt_service = $pdo->prepare("SELECT OfferingName FROM ServiceOffering WHERE OfferingID = ?");
    $service = $stmt_service->fetch(PDO::FETCH_ASSOC);

    // Define which services go to which employee
    $performanceServices = ['Performance Tuning'];
    $exteriorServices = ['Vinyl Wrap', 'Window Tint', 'PPF'];

    // Default to Sarah (exterior specialist) if service not found
    $employeeID = 3;

    if ($service && in_array($service['OfferingName'], $performanceServices)) {
        $employeeID = 2; // Alex (Performance Technician)
    }

    // Assign employee in ScheduleEmployee table
    $stmt_employee = $pdo->prepare("INSERT INTO ScheduleEmployee 
        (CustomerUserID, OfferingID, StartDate, EndDate, EmployeeUserID) 
        VALUES (:custID, :offID, :start, :end, :empID)");
    $stmt_employee->execute([
        ':custID' => $customerID,
        ':offID' => $serviceID,
        ':start' => $startDate,
        ':end' => $endDate,
        ':empID' => $employeeID
    ]);

    // Redirect to success page
    header('Location: customer.php?message=booking_success');
    exit();
} else {
    header('Location: customer.php?error=invalid_input');
    exit();
}
?>