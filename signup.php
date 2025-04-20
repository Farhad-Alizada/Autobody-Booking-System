<?php
session_start();
require_once 'db_connect.php';  // your PDO $pdo

// redirect back to signup.html with errors & old inputs
function redirect_with_errors(array $errs, array $old=[]) {
  $query = [];
  foreach ($errs as $e) {
    $query[] = 'errors[]=' . urlencode($e);
  }
  foreach ($old as $k=>$v) {
    $query[] = urlencode($k) . '=' . urlencode($v);
  }
  header('Location: signup.html?' . implode('&', $query));
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $errors = [];

  // 1) collect + trim
  $first = trim($_POST['first_name'] ?? '');
  $last  = trim($_POST['last_name']  ?? '');
  $email = trim($_POST['email']      ?? '');
  $pw    = $_POST['password']       ?? '';
  $phone = trim($_POST['phone']      ?? '');
  $pref  = $_POST['preferred_contact'] ?? '';
  $addr  = trim($_POST['address']    ?? '');

  // bundle old for redirect
  $old = compact('first','last','email','phone','pref','addr');

  // 2) required
  if (!$first||!$last||!$email||!$pw||!$phone||!$pref||!$addr) {
    $errors[] = "All fields are required.";
  }

  // 3) email format + block + unique
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
  } else {
    $low = strtolower($email);
    foreach (['@admin.com','@wraplab.com'] as $bad) {
      if (str_ends_with($low, $bad)) {
        $errors[] = "You may not register with “{$bad}” addresses.";
        break;
      }
    }
    if (empty($errors)) {
      $stmt = $pdo->prepare("SELECT 1 FROM Users WHERE Email = ?");
      $stmt->execute([$email]);
      if ($stmt->fetch()) {
        $errors[] = "Account already exists.";
      }
    }
  }

  // 4) phone: allow formats like 569-098-9999, (569)098-9999 etc.
  //    strip out everything except digits, then require exactly 10
  $cleanPhone = preg_replace('/\D+/', '', $phone);
  if (strlen($cleanPhone) !== 10) {
    $errors[] = "Phone must contain exactly 10 digits.";
  } else {
    // normalize to digits-only before storing
    $phone = $cleanPhone;
  }

  // 5) preferred contact
  if (!in_array($pref,['Email','Phone'], true)) {
    $errors[] = "Preferred contact must be Email or Phone.";
  }

  // 6) password length
  if (strlen($pw) < 6) {
    $errors[] = "Password must be at least 6 characters.";
  }

  if ($errors) {
    redirect_with_errors($errors, $old);
  }

  // 7) all good → insert both tables
  try {
    $pdo->beginTransaction();

    // a) Users
    $hash = password_hash($pw, PASSWORD_DEFAULT);
    $insU = $pdo->prepare("
      INSERT INTO Users
        (FirstName,LastName,Email,Password,PhoneNumber,AccessLevel)
      VALUES (?,?,?,?,?,'Customer')
    ");
    $insU->execute([$first,$last,$email,$hash,$phone]);
    $userID = $pdo->lastInsertId();

    // b) Customer
    $insC = $pdo->prepare("
      INSERT INTO Customer
        (UserID,PreferredContact,Address)
      VALUES (?,?,?)
    ");
    $insC->execute([$userID,$pref,$addr]);

    $pdo->commit();
    header('Location: login.html?msg=registered');
    exit;

  } catch (\Throwable $e) {
    $pdo->rollBack();
    redirect_with_errors(["Server error, please try again."], $old);
  }
}

// If someone GETs this PHP directly, just redirect to the form
header('Location: login.html');
exit;
