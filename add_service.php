<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['serviceName'];
    $desc = $_POST['serviceDesc'];
    $price = $_POST['priceRange'];

    // Convert price to float just in case (TotalPrice = ServicePrice for now)
    $price = floatval($price);

    $stmt = $pdo->prepare("INSERT INTO ServiceOffering (OfferingName, ServiceDescription, ServicePrice, TotalPrice) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $desc, $price, $price]);

    header("Location: admin.php?service_success=1");
    exit();
}
?>
