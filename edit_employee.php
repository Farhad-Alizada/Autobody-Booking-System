<?php
session_start();
require_once 'db_connect.php';

// only Admins
if (!isset($_SESSION['user']) || $_SESSION['user']['AccessLevel']!=='Admin') {
  header('Location: login.html');
  exit();
}

// handle POST → save updates
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $id   = intval($_POST['user_id']);
  $first= trim($_POST['first_name']);
  $last = trim($_POST['last_name']);
  $addr = trim($_POST['address']);
  $job  = trim($_POST['job_title']);
  $spec = trim($_POST['specialization']);

  // regenerate email: first.last@wraplab.com, with numeric suffix if needed
  $base = strtolower($first) .'.'. strtolower($last);
  $sql  = "SELECT COUNT(*) FROM Users WHERE Email LIKE :pattern";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(['pattern'=> $base .'%@wraplab.com']);
  $cnt = $stmt->fetchColumn();
  $email = $base 
         . ($cnt>0 ? $cnt : '')
         . '@wraplab.com';

  // update Users
  $updU = $pdo->prepare("
    UPDATE Users
      SET FirstName=?, LastName=?, Email=?
    WHERE UserID=?
  ");
  $updU->execute([$first,$last,$email,$id]);

  // update Employee (including Address)
  $updE = $pdo->prepare("
    UPDATE Employee
      SET JobTitle=?, Specialization=?, Address=?
    WHERE UserID=?
  ");
  $updE->execute([$job,$spec,$addr,$id]);

  header('Location: admin.php?msg=emp_updated');
  exit();
}

// GET → show form
$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: admin.php'); exit(); }

$stmt = $pdo->prepare("
  SELECT U.FirstName, U.LastName, U.Email,
         E.JobTitle, E.Specialization, E.Address
    FROM Users U
    JOIN Employee E ON U.UserID=E.UserID
   WHERE U.UserID=?
");
$stmt->execute([$id]);
$e = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$e) { header('Location: admin.php'); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Edit Employee</title>
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
  <h3 class="mb-4">Edit Employee #<?=$id?></h3>
  <form method="POST" class="row g-3" style="max-width:600px">
    <input type="hidden" name="user_id" value="<?=$id?>">

    <div class="col-md-6">
      <label class="form-label">First Name</label>
      <input name="first_name" class="form-control" required
             value="<?=htmlspecialchars($e['FirstName'])?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Last Name</label>
      <input name="last_name" class="form-control" required
             value="<?=htmlspecialchars($e['LastName'])?>">
    </div>
    <div class="col-12">
      <label class="form-label">Address</label>
      <input name="address" class="form-control"
             value="<?=htmlspecialchars($e['Address'])?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Job Title</label>
      <input name="job_title" class="form-control" required
             value="<?=htmlspecialchars($e['JobTitle'])?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Specialization</label>
      <input name="specialization" class="form-control"
             value="<?=htmlspecialchars($e['Specialization'])?>">
    </div>

    <div class="col-12 text-end">
      <a href="admin.php" class="btn btn-outline-secondary">Cancel</a>
      <button class="btn btn-primary">Save Changes</button>
    </div>
  </form>
</body>
</html>
