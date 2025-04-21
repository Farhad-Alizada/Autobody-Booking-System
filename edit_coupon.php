<?php
session_start();
require_once 'db_connect.php';

// only admins can touch this
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Admin') {
  header('Location: login.html');
  exit();
}

// If we're saving the form…
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = intval($_POST['coupon_number'] ?? 0);
    $amount = floatval($_POST['amount'] ?? 0);

    // 1) Look up the MaxPrice for this coupon's service
    $stmt = $pdo->prepare("
      SELECT 
        DC.OfferingID    AS offering_id,
        SO.MaxPrice      AS max_price
      FROM DiscountCoupon DC
      JOIN ServiceOffering SO 
        ON DC.OfferingID = SO.OfferingID
      WHERE DC.CouponNumber = ?
    ");
    $stmt->execute([$id]);
    $svc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$svc) {
      // coupon not found
      header('Location: admin.php?error=coupon_not_found');
      exit();
    }

    // 2) Validate amount > 0 && <= max_price
    if ($amount <= 0 || $amount > (float)$svc['max_price']) {
      header('Location: admin.php?error=invalid_coupon');
      exit();
    }

    // 3) Update DiscountAmount
    $upd = $pdo->prepare("
      UPDATE DiscountCoupon
      SET DiscountAmount = ?
      WHERE CouponNumber = ?
    ");
    $upd->execute([$amount, $id]);

    header('Location: admin.php?msg=coupon_updated');
    exit();
}

// Otherwise it's a GET, show the edit form
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
  header('Location: admin.php');
  exit();
}

// fetch the coupon and its service’s min/max
$stmt = $pdo->prepare("
  SELECT
    DC.CouponNumber,
    DC.DiscountAmount,
    DC.OfferingID,
    SO.OfferingName,
    SO.MinPrice,
    SO.MaxPrice
  FROM DiscountCoupon DC
  JOIN ServiceOffering SO
    ON DC.OfferingID = SO.OfferingID
  WHERE DC.CouponNumber = ?
");
$stmt->execute([$id]);
$coupon = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$coupon) {
  header('Location: admin.php?error=coupon_not_found');
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Edit Coupon #<?= htmlspecialchars($coupon['CouponNumber']) ?></title>
  <link 
    rel="stylesheet" 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  >
</head>
<body class="p-4">
  <h3>Edit Coupon #<?= $coupon['CouponNumber'] ?></h3>
  <form method="POST" class="row g-3" style="max-width:500px">
    <input type="hidden" name="coupon_number" value="<?= $coupon['CouponNumber'] ?>">

    <div class="col-12">
      <label class="form-label">Service Offering</label>
      <input type="text" class="form-control" 
             value="<?= htmlspecialchars($coupon['OfferingName']) ?>" disabled>
    </div>

    <div class="col-md-6">
      <label class="form-label">Min Price</label>
      <input type="text" class="form-control" 
             value="$<?= number_format($coupon['MinPrice'],2) ?>" disabled>
    </div>
    <div class="col-md-6">
      <label class="form-label">Max Price</label>
      <input type="text" class="form-control" 
             value="$<?= number_format($coupon['MaxPrice'],2) ?>" disabled>
    </div>

    <div class="col-12">
      <label class="form-label">Discount Amount ($)</label>
      <input type="number" step="0.01" name="amount" class="form-control"
             value="<?= number_format($coupon['DiscountAmount'],2) ?>" required>
    </div>

    <div class="col-12 text-end">
      <a href="admin.php" class="btn btn-outline-secondary">Cancel</a>
      <button class="btn btn-primary">Save Changes</button>
    </div>
  </form>
</body>
</html>
