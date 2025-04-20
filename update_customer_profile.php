<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Customer') {
    header('Location: login.html');
    exit();
}

$uid  = $_SESSION['user']['UserID'];
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$pref  = $_POST['preferred_contact'] ?? '';
$addr  = trim($_POST['address'] ?? '');

// simple validation (you can expand)
if ($email && $phone && in_array($pref, ['Email','Phone'], true) && $addr) {
    $pdo->beginTransaction();
    try {
        // 1) Update Users table
        $u = $pdo->prepare("UPDATE Users SET Email = ?, PhoneNumber = ? WHERE UserID = ?");
        $u->execute([$email, $phone, $uid]);

        // 2) Update Customer table
        $c = $pdo->prepare("UPDATE Customer SET PreferredContact = ?, Address = ? WHERE UserID = ?");
        $c->execute([$pref, $addr, $uid]);

        $pdo->commit();
    } catch (\Throwable $e) {
        $pdo->rollBack();
        // optionally log error
    }
}

header('Location: customer.php?msg=profile_updated');
exit;
