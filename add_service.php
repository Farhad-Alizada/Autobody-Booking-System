<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php');
    exit;
}

// 1) Gather & validate inputs
$name = trim($_POST['serviceName'] ?? '');
$desc = trim($_POST['serviceDesc'] ?? '');
$min  = floatval($_POST['minPrice']   ?? -1);
$max  = floatval($_POST['maxPrice']   ?? -1);

if ($name === '' || $min < 0 || $max < $min) {
    header('Location: admin.php?error=invalid');
    exit;
}

// 2) Handle optional image upload
$imgPath = null;
if (
    !empty($_FILES['serviceImage']['tmp_name']) &&
    $_FILES['serviceImage']['error'] === UPLOAD_ERR_OK
) {
    $file    = $_FILES['serviceImage'];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];

    if (in_array($ext, $allowed) && $file['size'] <= 2 * 1024 * 1024) {
        // Ensure upload directory exists
        $uploadDir = __DIR__ . '/uploads/services';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Move to final location
        $filename = uniqid('svc_', true) . '.' . $ext;
        $dest     = "$uploadDir/$filename";

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            // Store path relative to your web root
            $imgPath = "uploads/services/$filename";
        }
    }
}

// 3) Insert into database
$stmt = $pdo->prepare("
    INSERT INTO ServiceOffering
      (OfferingName, ServiceDescription, MinPrice, MaxPrice, ImagePath)
    VALUES
      (:name, :desc, :min, :max, :img)
");
$stmt->execute([
    ':name' => $name,
    ':desc' => $desc,
    ':min'  => $min,
    ':max'  => $max,
    ':img'  => $imgPath
]);

// 4) Redirect back to admin
header('Location: admin.php?msg=added');
exit;
