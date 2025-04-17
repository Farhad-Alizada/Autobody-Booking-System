<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php'); exit();
}

$name   = trim($_POST['serviceName'] ?? '');
$desc   = trim($_POST['serviceDesc'] ?? '');
$min    = floatval($_POST['minPrice'] ?? -1);
$max    = floatval($_POST['maxPrice'] ?? -1);

// basic validation
if ($name === '' || $min < 0 || $max < $min) {
    header('Location: admin.php?error=invalid'); exit();
}

// handle optional image
$imgPath = null;
if (!empty($_FILES['serviceImage']['tmp_name'])) {
    $file  = $_FILES['serviceImage'];
    $ext   = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $ok    = ['jpg','jpeg','png','gif','webp'];
    if (in_array($ext,$ok) && $file['size'] <= 2*1024*1024) {
        $dir = 'uploads/services';
        if (!is_dir($dir)) mkdir($dir,0775,true);
        $fn   = uniqid('svc_',true).'.'.$ext;
        $dest = "$dir/$fn";
        if (move_uploaded_file($file['tmp_name'],$dest)) {
            $imgPath = $dest;
        }
    }
}

$stmt = $pdo->prepare("
    INSERT INTO ServiceOffering
      (OfferingName, ServiceDescription, MinPrice, MaxPrice, ImagePath)
    VALUES (?,?,?,?,?)
");
$stmt->execute([$name,$desc,$min,$max,$imgPath]);

header('Location: admin.php?msg=added');
exit();
