<?php
session_start();
require_once 'db_connect.php';

// only admins can do this
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel'] !== 'Admin') {
    header('Location: login.html');
    exit();
}

// 1) grab & sanitize
$fnRaw   = trim($_POST['first_name']       ?? '');
$lnRaw   = trim($_POST['last_name']        ?? '');
$addr    = trim($_POST['address']          ?? '');
$job     = trim($_POST['job_title']        ?? '');
$spec    = trim($_POST['specialization']   ?? '');
$phoneRaw= trim($_POST['phone_number']     ?? '');

if (!$fnRaw || !$lnRaw || !$addr || !$job || !$spec || !$phoneRaw) {
    die("All fields are required, including phone number.");
}

// 1a) phone must be exactly 10 digits
if (!$phoneRaw) {
  die("All fields are required, including phone number.");
}

// strip any non-digits
$digits = preg_replace('/\D+/', '', $phoneRaw);

if (strlen($digits) !== 10) {
  die("Phone number must contain exactly 10 digits (e.g. xxx-xxx-xxxx or xxxxxxxxxx).");
}

// and later, when you update:
$updU = $pdo->prepare("
UPDATE Users
   SET FirstName  = ?,
       LastName   = ?,
       Email      = ?,
       PhoneNumber= ?
 WHERE UserID    = ?
");
$updU->execute([
  $first,
  $last,
  $email,
  $digits,  // store the clean 10-digit string
  $id
]);

// 2) normalize to Title Case
$fn = mb_convert_case($fnRaw, MB_CASE_TITLE, "UTF-8");
$ln = mb_convert_case($lnRaw, MB_CASE_TITLE, "UTF-8");

// 3) slugify (keep only a–z letters, then lowercase)
$fnSlug = strtolower(preg_replace('/[^a-z]/i', '', $fn));
$lnSlug = strtolower(preg_replace('/[^a-z]/i', '', $ln));

// local‑part & base email
$local     = $fnSlug . '.' . $lnSlug;
$baseEmail = $local . '@wraplab.com';

// 4) find existing emails like “local%” → pick next suffix
$stmt = $pdo->prepare("SELECT Email FROM Users WHERE Email LIKE ?");
$stmt->execute([ $local . '%@wraplab.com' ]);
$all = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!in_array($baseEmail, $all, true)) {
    $email = $baseEmail;
} else {
    $max = 0;
    foreach ($all as $e) {
        if (preg_match('/^' . preg_quote($local, '/') . '(\d+)@wraplab\.com$/', $e, $m)) {
            $max = max($max, (int)$m[1]);
        }
    }
    $email = $local . ($max + 1) . '@wraplab.com';
}

// 5) default password
$pwd = 'changeme123';

// 6) insert into Users (now including PhoneNumber)
$insU = $pdo->prepare("
    INSERT INTO Users
      (FirstName, LastName, Email, Password, AccessLevel, PhoneNumber)
    VALUES (?, ?, ?, ?, 'Employee', ?)
");
$insU->execute([
    $fn,
    $ln,
    $email,
    password_hash($pwd, PASSWORD_DEFAULT),
    $phoneRaw
]);
$uid = $pdo->lastInsertId();

// 7) insert into Employee
$insE = $pdo->prepare("
    INSERT INTO Employee
      (UserID, JobTitle, Specialization, Address)
    VALUES (?, ?, ?, ?)
");
$insE->execute([$uid, $job, $spec, $addr]);

// all done
header('Location: admin.php?msg=employee_added');
exit();
