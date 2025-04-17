<?php
// add_coupon.php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php');
    exit();
}

$amount     = floatval($_POST['amount'] ?? 0);
$offeringId = intval($_POST['offering_id'] ?? 0);
$adminId    = intval($_POST['admin_id']   ?? 0);

// basic sanity
if ($amount <= 0 || $offeringId <= 0 || $adminId <= 0) {
    header('Location: admin.php?error=invalid_coupon');
    exit();
}

// fetch that service's MaxPrice
$stmt = $pdo->prepare("SELECT MaxPrice FROM ServiceOffering WHERE OfferingID = ?");
$stmt->execute([$offeringId]);
$maxPrice = $stmt->fetchColumn();

if ($maxPrice === false) {
    // offering not found
    header('Location: admin.php?error=not_found');
    exit();
}

// enforce discount <= max price
if ($amount > (float)$maxPrice) {
    header('Location: admin.php?error=too_big');
    exit();
}

// now insert
$stmt = $pdo->prepare("
  INSERT INTO DiscountCoupon (DiscountAmount, OfferingID, AdminUserID)
  VALUES (?, ?, ?)
");
$stmt->execute([$amount, $offeringId, $adminId]);

header('Location: admin.php?msg=coupon_added');
exit();
