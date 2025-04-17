<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // update form
    $id   = (int)$_POST['id'];
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['desc'] ?? '');
    $min  = floatval($_POST['min'] ?? 0);
    $max  = floatval($_POST['max'] ?? 0);

    if ($name === '' || $min < 0 || $max < $min) {
        header("Location: edit_service.php?id=$id&error=invalid");
        exit();
    }

    // optional new image
    $imgPath = null;
    if (!empty($_FILES['image']['tmp_name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $ok  = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext,$ok)) {
            $d = 'uploads/services';
            if (!is_dir($d)) mkdir($d,0775,true);
            $fn = uniqid('svc_',true).'.'.$ext;
            move_uploaded_file($_FILES['image']['tmp_name'], "$d/$fn");
            $imgPath = "$d/$fn";
        }
    }

    // build SQL
    $sql = "UPDATE ServiceOffering
            SET OfferingName=?, ServiceDescription=?, MinPrice=?, MaxPrice=?";
    if ($imgPath) {
        $sql .= ", ImagePath=".$pdo->quote($imgPath);
    }
    $sql .= " WHERE OfferingID=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name,$desc,$min,$max,$id]);

    header('Location: admin.php?msg=updated');
    exit();
}

// first‐visit: show form
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: admin.php'); exit(); }

$stmt = $pdo->prepare("SELECT * FROM ServiceOffering WHERE OfferingID=?");
$stmt->execute([$id]);
$svc = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$svc) { header('Location: admin.php'); exit(); }
?>
<!doctype html>
<html lang="en"><head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Edit Service</title>
  <link 
    rel="stylesheet" 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  >
</head><body class="p-4">
  <h3 class="mb-3">Edit “<?=htmlspecialchars($svc['OfferingName'])?>”</h3>

  <form method="POST" enctype="multipart/form-data" class="row g-3" style="max-width:600px">
    <input type="hidden" name="id" value="<?=$svc['OfferingID']?>">

    <div class="col-12">
      <label class="form-label">Name</label>
      <input name="name" class="form-control"
             value="<?=htmlspecialchars($svc['OfferingName'])?>" required>
    </div>

    <div class="col-12">
      <label class="form-label">Description</label>
      <input name="desc" class="form-control"
             value="<?=htmlspecialchars($svc['ServiceDescription'])?>" required>
    </div>

    <div class="col-md-6">
      <label class="form-label">Min Price ($)</label>
      <input name="min" type="number" step="0.01" class="form-control"
             value="<?=$svc['MinPrice']?>" required>
    </div>

    <div class="col-md-6">
      <label class="form-label">Max Price ($)</label>
      <input name="max" type="number" step="0.01" class="form-control"
             value="<?=$svc['MaxPrice']?>" required>
    </div>

    <div class="col-12">
      <label class="form-label">Replace Image (optional)</label>
      <input name="image" type="file" accept="image/*" class="form-control">
      <?php if($svc['ImagePath']): ?>
        <small class="text-muted">Current: <?=htmlspecialchars($svc['ImagePath'])?></small>
      <?php endif; ?>
    </div>

    <div class="col-12 text-end">
      <a href="admin.php" class="btn btn-outline-secondary">Cancel</a>
      <button class="btn btn-primary">Save Changes</button>
    </div>
  </form>
</body></html>
