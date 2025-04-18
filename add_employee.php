<?php
session_start();
require_once 'db_connect.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel']!=='Admin') {
  header('Location: login.html');
  exit();
}

// grab & sanitize
$fn  = trim($_POST['first_name']);
$ln  = trim($_POST['last_name']);
$addr= trim($_POST['address']);
$job = trim($_POST['job_title']);
$spec= trim($_POST['specialization']);
if (!$fn||!$ln||!$addr||!$job||!$spec) {
  die("All fields are required.");
}

// 1) generate base email
$base = strtolower(preg_replace('/[^a-z]/','', $fn))
      .'.'
      .strtolower(preg_replace('/[^a-z]/','', $ln))
      .'@wraplab.com';

// 2) dedupe
$stmt = $pdo->prepare("SELECT COUNT(*) FROM Users WHERE Email LIKE ?");
$stmt->execute([ str_replace('@wraplab.com','',$base) .'%@wraplab.com' ]);
$count = (int)$stmt->fetchColumn();
$email = $count
  ? str_replace('@wraplab.com','',$base).$count.'@wraplab.com'
  : $base;

// 3) default password
$pwd = 'changeme123';

// 4) insert into Users
$insU = $pdo->prepare("
  INSERT INTO Users
    (FirstName, LastName, Email, Password, AccessLevel)
  VALUES (?,?,?,?, 'Employee')
");
$insU->execute([$fn,$ln,$email,$pwd]);
$uid = $pdo->lastInsertId();

// 5) insert into Employee (assumes Address column exists)
$insE = $pdo->prepare("
  INSERT INTO Employee
    (UserID, JobTitle, Specialization, Address)
  VALUES (?,?,?,?)
");
$insE->execute([$uid,$job,$spec,$addr]);

// redirect back
header('Location: admin.php?msg=employee_added');
exit();
