<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['coupon_number'])) {
  header('Location: admin.php'); exit();
}

$couponNum = intval($_POST['coupon_number']);
$stmt = $pdo->prepare("DELETE FROM DiscountCoupon WHERE CouponNumber = ?");
$stmt->execute([$couponNum]);

header('Location: admin.php?msg=coupon_deleted');
exit();
