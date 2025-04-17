<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['offering_id'])) {
    header('Location: admin.php'); exit();
}

$id = (int)$_POST['offering_id'];

// fetch image path
$path = $pdo->prepare("SELECT ImagePath FROM ServiceOffering WHERE OfferingID=?");
$path->execute([$id]);
$img = $path->fetchColumn();

// delete the row
$del = $pdo->prepare("DELETE FROM ServiceOffering WHERE OfferingID=?");
$del->execute([$id]);

// delete the file if local
if ($img && strpos($img,'uploads/')===0 && file_exists($img)) {
    @unlink($img);
}

header('Location: admin.php?msg=deleted');
exit();
